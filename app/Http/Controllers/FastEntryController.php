<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationFieldHistory;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FamilyMember;
use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\Service;
use App\Services\ApplicationService;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FastEntryController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected ApplicationService $applicationService
    ) {}

    /**
     * Show fast spreadsheet-style daily customer profile creation grid and new service entry.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();
        
        // Active services master for counter entries
        $services = Service::where('is_active', true)->orderBy('main_service_name')->get();

        // Active operators/employees
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();

        // Distinct customer occupations in database
        $existingOccupations = Customer::where('tenant_id', $user->tenant_id)
            ->whereNotNull('occupation')
            ->where('occupation', '!=', '')
            ->distinct()
            ->pluck('occupation')
            ->map(fn($o) => strtoupper(trim($o)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Available payment modes (defaults + tenant custom entered)
        $existingPaymentModes = Application::getAvailablePaymentModes($user->tenant_id);

        return view('fast_entry.index', compact('branches', 'services', 'employees', 'existingOccupations', 'existingPaymentModes'));
    }

    /**
     * Fast Quick-Go: Search by mobile/name. If customer does NOT exist, directly auto-save to CRM!
     */
    public function quickGo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'min:10', 'max:20'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $tenantId = (int) ($user->tenant_id ?? session('current_tenant_id') ?? \App\Models\Tenant::first()?->id ?? 1);
        $branchId = $user->branch_id ?? \App\Models\Branch::where('tenant_id', $tenantId)->first()?->id ?? 1;
        $cleanedMobile = preg_replace('/[^0-9]/', '', $validated['mobile']);

        if (strlen($cleanedMobile) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 10-digit mobile number.',
            ], 422);
        }

        // 1. Search existing customer by mobile
        $existing = $this->customerService->findByMobile($tenantId, $cleanedMobile);

        if ($existing) {
            // If name is entered and different, update name
            $newName = trim($validated['customer_name'] ?? '');
            if (!empty($newName) && $existing->name !== $newName) {
                $existing->name = $newName;
                $existing->save();
            }

            if ($request->filled('alternate_mobile')) {
                $existing->alternate_mobile = trim($request->input('alternate_mobile'));
                $existing->save();
            }

            $customer = $existing;
            $isNew = false;
        } else {
            // If creation is not explicitly confirmed, return exists: false to prompt the user
            $confirmCreate = $request->boolean('confirm_create');

            if (!$confirmCreate) {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'is_new' => false,
                    'mobile' => $cleanedMobile,
                    'customer_name' => trim($validated['customer_name'] ?? ''),
                    'message' => "Customer with mobile {$cleanedMobile} is not registered.",
                ]);
            }

            // 2. User confirmed creation: Save to Customers CRM
            $custName = trim($validated['customer_name'] ?? '');
            if (empty($custName)) {
                $custName = 'Citizen ' . substr($cleanedMobile, -4);
            }

            $customer = $this->customerService->createCustomer([
                'branch_id' => $branchId,
                'name' => $custName,
                'mobile' => $cleanedMobile,
                'alternate_mobile' => $request->filled('alternate_mobile') ? trim($request->input('alternate_mobile')) : null,
                'gender' => 'MALE',
                'star_rating' => 5,
            ], $user);

            $isNew = true;
        }

        // Fetch customer's full applications & services history
        $applications = Application::where('customer_id', $customer->id)
            ->with(['service', 'assignedEmployee.user', 'familyMember'])
            ->latest('id')
            ->get()
            ->map(function ($app) use ($customer) {
                return [
                    'id' => $app->id,
                    'application_number' => $app->application_number,
                    'family_member_id' => $app->family_member_id,
                    'beneficiary_name' => $app->familyMember ? $app->familyMember->name : $customer->name,
                    'beneficiary_relation' => $app->familyMember ? $app->familyMember->relation_label : 'स्वतः (Head)',
                    'service_name' => $app->service?->main_service_name ?? 'Government Service',
                    'sub_service' => $app->sub_service ?: ($app->service?->sub_service_name ?? '-'),
                    'application_date' => $app->application_date ? date('d-m-Y', strtotime($app->application_date)) : ($app->created_at ? $app->created_at->format('d-m-Y') : date('d-m-Y')),
                    'formatted_date_time' => $app->created_at ? $app->created_at->format('d-m-Y h:i A') : ($app->application_date ? date('d-m-Y h:i A', strtotime($app->application_date)) : date('d-m-Y h:i A')),
                    'rate' => (float) $app->total_amount,
                    'received' => (float) $app->received_amount,
                    'remaining' => (float) $app->remaining_amount,
                    'payment_mode' => $app->payment_mode ?: 'Cash',
                    'status' => $app->work_status,
                    'service_id' => $app->service_id,
                    'expected_days' => (int) ($app->service?->expected_processing_days ?? 7),
                    'main_service_name' => $app->service?->main_service_name ?? 'Service',
                    'work_details' => $app->work_details ?? '',
                    'payment_remarks' => $app->payment_remarks ?? '',
                    'follow_up_date' => $app->follow_up_date ? date('Y-m-d', strtotime($app->follow_up_date)) : '',
                    'portal_link' => $app->service?->work_portal_link ?: $app->service?->service_portal_link,
                    'tutorial_link' => $app->service?->video_instruction_link,
                    'assigned_employee_id' => $app->assigned_employee_id,
                    'operator' => $app->assignedEmployee?->user?->name ?? 'Counter Desk',
                    'show_url' => route('applications.show', $app->id),
                    'receipt_url' => route('applications.receipt', $app->id),
                ];
            });

        $familyMembers = $customer->familyMembers()->get()->map(function ($fm) {
            return [
                'id' => $fm->id,
                'name' => $fm->name,
                'relation' => $fm->relation,
                'relation_label' => $fm->relation_label,
                'gender' => $fm->gender,
                'birth_date' => $fm->birth_date ? $fm->birth_date->format('Y-m-d') : '',
                'age' => $fm->age,
                'mobile' => $fm->mobile ?: '',
            ];
        });

        return response()->json([
            'success' => true,
            'exists' => true,
            'is_new' => $isNew,
            'message' => $isNew
                ? "New Customer Registered: {$customer->customer_code} ({$customer->name})"
                : "Existing Customer Loaded: {$customer->customer_code} ({$customer->name})",
            'customer' => [
                'id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'gender' => $customer->gender ?? 'MALE',
                'birth_date' => $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '',
                'alternate_mobile' => $customer->alternate_mobile ?: '',
                'occupation' => $customer->occupation ?: '',
                'email' => $customer->email ?: '',
                'star_rating' => (int) ($customer->star_rating ?? 5),
                'is_blacklisted' => (bool) $customer->is_blacklisted,
                'blacklist_reason' => $customer->blacklist_reason ?: '',
                'total_services' => $applications->count(),
                'total_billed' => (float) $customer->total_billed,
                'total_paid' => (float) $customer->total_paid,
                'total_outstanding' => (float) $customer->total_outstanding,
                'profile_url' => route('customers.show', $customer->id),
                'family_members' => $familyMembers,
            ],
            'applications' => $applications,
        ]);
    }

    /**
     * Update customer optional details (gender, DOB, rating, email, occupation, blacklist) from right panel.
     */
    public function updateCustomerDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
            'birth_date' => ['nullable', 'date'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'star_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_blacklisted' => ['nullable', 'boolean'],
            'blacklist_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $updates = [];
        if (!empty($validated['name'])) $updates['name'] = trim($validated['name']);
        if (isset($validated['gender'])) $updates['gender'] = $validated['gender'];
        if (isset($validated['star_rating'])) $updates['star_rating'] = (int) $validated['star_rating'];
        if (array_key_exists('birth_date', $validated)) $updates['birth_date'] = $validated['birth_date'];
        if (array_key_exists('alternate_mobile', $validated)) $updates['alternate_mobile'] = $validated['alternate_mobile'];
        if (array_key_exists('occupation', $validated)) $updates['occupation'] = $validated['occupation'];
        if (array_key_exists('email', $validated)) $updates['email'] = $validated['email'];
        if (array_key_exists('is_blacklisted', $validated)) $updates['is_blacklisted'] = (bool) $validated['is_blacklisted'];
        if (array_key_exists('blacklist_reason', $validated)) $updates['blacklist_reason'] = $validated['blacklist_reason'];

        $customer->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Customer profile updated successfully.',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'gender' => $customer->gender,
                'birth_date' => $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '',
                'alternate_mobile' => $customer->alternate_mobile ?: '',
                'occupation' => $customer->occupation ?: '',
                'email' => $customer->email ?: '',
                'star_rating' => (int) $customer->star_rating,
                'is_blacklisted' => (bool) $customer->is_blacklisted,
                'blacklist_reason' => $customer->blacklist_reason ?: '',
            ]
        ]);
    }

    /**
     * Toggle Customer Blacklist Status with optional reason.
     */
    public function toggleBlacklist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'is_blacklisted' => ['required', 'boolean'],
            'blacklist_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $isBlacklisted = (bool) $validated['is_blacklisted'];
        $customer->update([
            'is_blacklisted' => $isBlacklisted,
            'blacklist_reason' => $isBlacklisted ? ($validated['blacklist_reason'] ?? 'Flagged by operator') : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $customer->is_blacklisted ? "Customer marked as BLACKLISTED." : "Customer removed from Blacklist.",
            'is_blacklisted' => (bool) $customer->is_blacklisted,
            'blacklist_reason' => $customer->blacklist_reason ?: '',
        ]);
    }

    /**
     * Update existing service row (Rate, Received, Status, Operator, Sub Service) directly from table.
     */
    public function updateServiceRow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'rate' => ['required', 'numeric', 'min:0'],
            'received' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'work_status' => ['nullable', 'string'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'sub_service' => ['nullable', 'string', 'max:255'],
            'work_details' => ['nullable', 'string'],
            'payment_remarks' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $user = Auth::user();
        $application = Application::findOrFail($validated['application_id']);

        $rate = (float) $validated['rate'];
        $received = isset($validated['received']) ? (float) $validated['received'] : 0.00;
        $remaining = max(0, $rate - $received);

        $workStatus = array_key_exists('work_status', $validated) ? trim($validated['work_status'] ?? '') : ($application->work_status ?: Application::STATUS_NEW);
        if ($workStatus === 'DONE') {
            $workStatus = Application::STATUS_READY;
        }

        $oldStatus = $application->work_status;
        $oldAssigned = $application->assigned_employee_id;
        $oldRate = (float) $application->total_amount;
        $oldSubService = $application->sub_service;
        $oldServiceId = $application->service_id;
        $oldFollowup = $application->follow_up_date ? date('Y-m-d', strtotime($application->follow_up_date)) : null;
        $oldPaymentMode = $application->payment_mode ?: 'Cash';

        $newRate = $rate;
        $newStatus = $workStatus;
        $newAssigned = $validated['assigned_employee_id'] ?? null;
        $newServiceId = $application->service_id;
        $newSubService = $application->sub_service;
        $newPaymentMode = array_key_exists('payment_mode', $validated) ? ($validated['payment_mode'] ?: 'Cash') : $oldPaymentMode;

        if (!empty($validated['service_id'])) {
            $newService = Service::find($validated['service_id']);
            if ($newService) {
                $newServiceId = $newService->id;
                $newSubService = $newService->sub_service_name;
            }
        } elseif (array_key_exists('sub_service', $validated) && !empty($validated['sub_service'])) {
            $newSubService = trim($validated['sub_service']);
            $currentService = $application->service;
            if ($currentService) {
                $matched = Service::where('main_service_name', $currentService->main_service_name)
                    ->where('sub_service_name', $newSubService)
                    ->first();
                if ($matched) {
                    $newServiceId = $matched->id;
                }
            }
        }

        $newFollowup = array_key_exists('follow_up_date', $validated) && !empty($validated['follow_up_date']) ? date('Y-m-d', strtotime($validated['follow_up_date'])) : null;

        $application->update([
            'service_id' => $newServiceId,
            'sub_service' => $newSubService,
            'total_amount' => $rate,
            'base_amount' => $rate,
            'received_amount' => $received,
            'remaining_amount' => $remaining,
            'payment_mode' => $newPaymentMode,
            'payment_status' => $remaining <= 0 ? Application::PAYMENT_PAID : ($received > 0 ? Application::PAYMENT_PARTIAL : Application::PAYMENT_PENDING),
            'work_status' => $workStatus,
            'assigned_employee_id' => $newAssigned,
            'work_details' => array_key_exists('work_details', $validated) ? $validated['work_details'] : $application->work_details,
            'payment_remarks' => array_key_exists('payment_remarks', $validated) ? $validated['payment_remarks'] : $application->payment_remarks,
            'follow_up_date' => array_key_exists('follow_up_date', $validated) ? $validated['follow_up_date'] : $application->follow_up_date,
        ]);

        // Record field-specific history for changed attributes
        ApplicationFieldHistory::recordIfChanged($application, 'work_status', 'Status', $oldStatus, $newStatus, null, null, $user->id);
        ApplicationFieldHistory::recordIfChanged($application, 'assigned_employee_id', 'Operator', $oldAssigned, $newAssigned, null, null, $user->id);
        ApplicationFieldHistory::recordIfChanged($application, 'rate', 'Rate (₹)', $oldRate, $newRate, null, null, $user->id);
        ApplicationFieldHistory::recordIfChanged($application, 'sub_service', 'Sub Service', $oldSubService, $newSubService, null, null, $user->id);
        ApplicationFieldHistory::recordIfChanged($application, 'payment_mode', 'Payment Mode', $oldPaymentMode, $newPaymentMode, null, null, $user->id);
        ApplicationFieldHistory::recordIfChanged($application, 'follow_up_date', 'Follow-Up Date', $oldFollowup, $newFollowup, null, null, $user->id);

        $this->syncApplicationFollowUp(
            $application,
            $application->follow_up_date ? $application->follow_up_date->format('Y-m-d') : null,
            $application->work_details,
            $user->id
        );

        $customer = $application->customer;
        $application->load(['service', 'assignedEmployee.user']);

        return response()->json([
            'success' => true,
            'message' => "Service Updated: {$application->application_number}",
            'data' => [
                'id' => $application->id,
                'application_number' => $application->application_number,
                'service_id' => $application->service_id,
                'service_name' => $application->service?->main_service_name ?? 'Service',
                'sub_service' => $application->sub_service,
                'expected_days' => (int) ($application->service?->expected_processing_days ?? 7),
                'main_service_name' => $application->service?->main_service_name ?? 'Service',
                'rate' => number_format($application->total_amount, 2),
                'received' => number_format($application->received_amount, 2),
                'remaining' => number_format($application->remaining_amount, 2),
                'payment_mode' => $application->payment_mode ?: 'Cash',
                'status' => $application->work_status,
                'assigned_employee_id' => $application->assigned_employee_id,
                'work_details' => $application->work_details ?? '',
                'payment_remarks' => $application->payment_remarks ?? '',
                'follow_up_date' => $application->follow_up_date ? date('Y-m-d', strtotime($application->follow_up_date)) : '',
                'operator' => $application->assignedEmployee?->user?->name ?? 'Unassigned',
                'customer_total_outstanding' => (float) $customer?->total_outstanding,
            ],
        ]);
    }

    /**
     * Search customers by name, mobile, or customer_code for New Entry selection.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $cleanedMobile = preg_replace('/[^0-9]/', '', $query);

        $customers = Customer::where('tenant_id', $user->tenant_id)
            ->where(function ($q) use ($query, $cleanedMobile) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('customer_code', 'like', "%{$query}%");
                if ($cleanedMobile) {
                    $q->orWhere('mobile', 'like', "%{$cleanedMobile}%")
                      ->orWhere('alternate_mobile', 'like', "%{$cleanedMobile}%");
                }
            })
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json($customers->map(function ($c) {
            return [
                'id' => $c->id,
                'customer_code' => $c->customer_code,
                'name' => $c->name,
                'mobile' => $c->mobile,
                'alternate_mobile' => $c->alternate_mobile ?: '',
                'gender' => $c->gender,
                'star_rating' => $c->star_rating ?? 5,
                'is_blacklisted' => (bool) $c->is_blacklisted,
                'occupation' => $c->occupation ?: '-',
            ];
        }));
    }

    /**
     * Fast Quick-Add customer profile row via AJAX for instant Excel-style registration.
     */
    public function storeRow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:MALE,FEMALE,OTHER'],
            'mobile' => ['required', 'string', 'min:10', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'star_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ], [
            'customer_name.required' => 'Customer Name is required.',
            'gender.required' => 'Gender is compulsory.',
            'mobile.required' => 'Mobile Number is compulsory.',
            'mobile.min' => 'Mobile Number must be at least 10 digits.',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        try {
            $cleanedMobile = preg_replace('/[^0-9]/', '', $validated['mobile']);
            $existing = $this->customerService->findByMobile($tenantId, $cleanedMobile);

            if ($existing) {
                $customer = $this->customerService->updateCustomer($existing, [
                    'name' => $validated['customer_name'],
                    'gender' => $validated['gender'],
                    'mobile' => $cleanedMobile,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'alternate_mobile' => $validated['alternate_mobile'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'star_rating' => $validated['star_rating'] ?? 5,
                ], $user);
                $isNew = false;
            } else {
                $customer = $this->customerService->createCustomer([
                    'branch_id' => $validated['branch_id'] ?? $user->branch_id,
                    'name' => $validated['customer_name'],
                    'gender' => $validated['gender'],
                    'mobile' => $cleanedMobile,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'alternate_mobile' => $validated['alternate_mobile'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'star_rating' => $validated['star_rating'] ?? 5,
                ], $user);
                $isNew = true;
            }

            return response()->json([
                'success' => true,
                'is_new' => $isNew,
                'message' => $isNew
                    ? "Customer Profile Created: {$customer->customer_code} ({$customer->name})"
                    : "Existing Customer Profile Updated: {$customer->customer_code} ({$customer->name})",
                'data' => [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name,
                    'gender' => $customer->gender,
                    'mobile' => $customer->mobile,
                    'birth_date' => $customer->birth_date ? $customer->birth_date->format('d-m-Y') : '-',
                    'alternate_mobile' => $customer->alternate_mobile ?: '-',
                    'occupation' => $customer->occupation ?: '-',
                    'email' => $customer->email ?: '-',
                    'star_rating' => (int) ($customer->star_rating ?? 5),
                    'branch_name' => $customer->branch?->name ?? 'Main Branch',
                    'created_at' => $customer->created_at->format('d M, h:i A'),
                    'url' => route('customers.show', $customer->id),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store New Service Entry directly linked to chosen Customer & assigned to Operator.
     */
    public function storeServiceEntry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'family_member_id' => ['nullable', 'exists:family_members,id'],
            'service_id' => ['required', 'exists:services,id'],
            'sub_service' => ['nullable', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0'],
            'received' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'work_details' => ['nullable', 'string'],
            'payment_remarks' => ['nullable', 'string'],
            'documents_list' => ['nullable', 'string'],
            'pending_remarks' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
            'work_status' => ['nullable', 'string'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
        ], [
            'customer_id.required' => 'Please select a customer first.',
            'service_id.required' => 'Please choose a service.',
            'rate.required' => 'Rate / Fee is required.',
        ]);

        $user = Auth::user();
        $customer = Customer::findOrFail($validated['customer_id']);
        $service = Service::findOrFail($validated['service_id']);
        $rate = (float) $validated['rate'];
        $received = isset($validated['received']) ? (float) $validated['received'] : 0.00;
        $paymentMode = !empty($validated['payment_mode']) ? trim($validated['payment_mode']) : 'Cash';
        $workStatus = $validated['work_status'] ?? Application::STATUS_NEW;
        if ($workStatus === 'DONE') {
            $workStatus = Application::STATUS_READY;
        }

        try {
            $application = DB::transaction(function () use ($validated, $user, $customer, $service, $rate, $received, $paymentMode, $workStatus) {
                $app = $this->applicationService->createApplication([
                    'customer_id' => $customer->id,
                    'family_member_id' => $validated['family_member_id'] ?? null,
                    'service_id' => $service->id,
                    'sub_service' => $validated['sub_service'] ?? $service->sub_service_name,
                    'branch_id' => $customer->branch_id ?? $user->branch_id,
                    'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
                    'application_date' => now()->toDateString(),
                    'base_amount' => $rate,
                    'received_amount' => $received,
                    'payment_mode' => $paymentMode,
                    'work_status' => $workStatus,
                    'work_details' => $validated['work_details'] ?? null,
                    'payment_remarks' => $validated['payment_remarks'] ?? null,
                    'documents_list' => $validated['documents_list'] ?? null,
                    'pending_remarks' => $validated['pending_remarks'] ?? null,
                    'follow_up_date' => $validated['follow_up_date'] ?? null,
                ], $user);

                return $app->load(['service', 'customer', 'assignedEmployee.user', 'familyMember']);
            });

            // Automatically synchronize with CRM Follow-ups
            $this->syncApplicationFollowUp(
                $application,
                $validated['follow_up_date'] ?? null,
                $validated['work_details'] ?? null,
                $user->id
            );

            // Record initial service creation in field histories
            ApplicationFieldHistory::create([
                'tenant_id' => $application->tenant_id,
                'application_id' => $application->id,
                'user_id' => $user->id,
                'field_name' => 'created',
                'field_label' => 'Service Created',
                'old_value' => null,
                'new_value' => $application->application_number,
                'old_display' => 'New Service Registration',
                'new_display' => $service->main_service_name . ($application->sub_service ? " - {$application->sub_service}" : ""),
                'remarks' => "Initial Rate: ₹" . number_format($rate, 2) . ", Operator: " . ($application->assignedEmployee?->user?->name ?? 'Unassigned'),
                'created_at' => now(),
            ]);

            $applications = Application::where('customer_id', $customer->id)
                ->with(['service', 'assignedEmployee.user', 'familyMember'])
                ->latest('id')
                ->get()
                ->map(function ($app) use ($customer) {
                    return [
                        'id' => $app->id,
                        'application_number' => $app->application_number,
                        'family_member_id' => $app->family_member_id,
                        'beneficiary_name' => $app->familyMember ? $app->familyMember->name : $customer->name,
                        'beneficiary_relation' => $app->familyMember ? $app->familyMember->relation_label : 'स्वतः (Head)',
                        'service_name' => $app->service?->main_service_name ?? 'Government Service',
                        'sub_service' => $app->sub_service ?: ($app->service?->sub_service_name ?? '-'),
                        'application_date' => $app->application_date ? date('d-m-Y', strtotime($app->application_date)) : ($app->created_at ? $app->created_at->format('d-m-Y') : date('d-m-Y')),
                        'formatted_date_time' => $app->created_at ? $app->created_at->format('d-m-Y h:i A') : ($app->application_date ? date('d-m-Y h:i A', strtotime($app->application_date)) : date('d-m-Y h:i A')),
                        'rate' => (float) $app->total_amount,
                        'received' => (float) $app->received_amount,
                        'remaining' => (float) $app->remaining_amount,
                        'payment_mode' => $app->payment_mode ?: 'Cash',
                        'status' => $app->work_status,
                        'service_id' => $app->service_id,
                        'expected_days' => (int) ($app->service?->expected_processing_days ?? 7),
                        'main_service_name' => $app->service?->main_service_name ?? 'Service',
                        'work_details' => $app->work_details ?? '',
                        'payment_remarks' => $app->payment_remarks ?? '',
                        'follow_up_date' => $app->follow_up_date ? date('Y-m-d', strtotime($app->follow_up_date)) : '',
                        'portal_link' => $app->service?->work_portal_link ?: $app->service?->service_portal_link,
                        'tutorial_link' => $app->service?->video_instruction_link,
                        'assigned_employee_id' => $app->assigned_employee_id,
                        'operator' => $app->assignedEmployee?->user?->name ?? 'Counter Desk',
                        'show_url' => route('applications.show', $app->id),
                        'receipt_url' => route('applications.receipt', $app->id),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => "Service Entry Recorded: {$service->main_service_name} for " . ($application->familyMember ? "{$application->familyMember->name} ({$application->familyMember->relation_label})" : $customer->name) . " (SR: {$application->application_number})",
                'data' => [
                    'id' => $application->id,
                    'application_number' => $application->application_number,
                    'customer_name' => $customer->name,
                    'family_member_id' => $application->family_member_id,
                    'beneficiary_name' => $application->familyMember ? $application->familyMember->name : $customer->name,
                    'beneficiary_relation' => $application->familyMember ? $application->familyMember->relation_label : 'स्वतः (Head)',
                    'customer_url' => route('customers.show', $customer->id),
                    'service_id' => $application->service_id,
                    'service_name' => $service->main_service_name,
                    'sub_service' => $application->sub_service ?: ($service->sub_service_name ?? '-'),
                    'application_date' => $application->application_date ? date('d-m-Y', strtotime($application->application_date)) : now()->format('d-m-Y'),
                    'formatted_date_time' => $application->created_at ? $application->created_at->format('d-m-Y h:i A') : now()->format('d-m-Y h:i A'),
                    'rate' => number_format($application->total_amount, 2),
                    'received' => number_format($application->received_amount, 2),
                    'remaining' => number_format($application->remaining_amount, 2),
                    'payment_mode' => $application->payment_mode ?: $paymentMode,
                    'status' => $application->work_status,
                    'expected_days' => (int) ($service->expected_processing_days ?? 7),
                    'main_service_name' => $service->main_service_name,
                    'work_details' => $application->work_details ?? '',
                    'payment_remarks' => $application->payment_remarks ?? '',
                    'follow_up_date' => $application->follow_up_date ? date('Y-m-d', strtotime($application->follow_up_date)) : '',
                    'portal_link' => $service->work_portal_link ?: $service->service_portal_link,
                    'tutorial_link' => $service->video_instruction_link,
                    'assigned_employee_id' => $application->assigned_employee_id,
                    'operator' => $application->assignedEmployee?->user?->name ?? 'Counter Desk',
                    'show_url' => route('applications.show', $application->id),
                    'receipt_url' => route('applications.receipt', $application->id),
                    'customer_total_outstanding' => (float) $customer->fresh()->total_outstanding,
                    'total_services' => $applications->count(),
                ],
                'applications' => $applications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store new Family Member under Customer.
     */
    public function storeFamilyMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'string', 'max:50'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Member Name is required.',
            'relation.required' => 'Relationship is required.',
        ]);

        $user = Auth::user();
        $customer = Customer::findOrFail($validated['customer_id']);

        $member = FamilyMember::create([
            'tenant_id' => $customer->tenant_id ?? $user->tenant_id,
            'customer_id' => $customer->id,
            'name' => trim($validated['name']),
            'relation' => strtoupper(trim($validated['relation'])),
            'gender' => $validated['gender'] ?? 'MALE',
            'birth_date' => $validated['birth_date'] ?? null,
            'age' => $validated['age'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $allMembers = $customer->familyMembers()->get()->map(function ($fm) {
            return [
                'id' => $fm->id,
                'name' => $fm->name,
                'relation' => $fm->relation,
                'relation_label' => $fm->relation_label,
                'gender' => $fm->gender,
                'birth_date' => $fm->birth_date ? $fm->birth_date->format('Y-m-d') : '',
                'age' => $fm->age,
                'mobile' => $fm->mobile ?: '',
                'notes' => $fm->notes ?: '',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Family member added: {$member->name} ({$member->relation_label})",
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'relation' => $member->relation,
                'relation_label' => $member->relation_label,
                'gender' => $member->gender,
                'birth_date' => $member->birth_date ? $member->birth_date->format('Y-m-d') : '',
                'age' => $member->age,
                'mobile' => $member->mobile ?: '',
                'notes' => $member->notes ?: '',
            ],
            'family_members' => $allMembers,
        ]);
    }

    /**
     * Update an existing Family Member details.
     */
    public function updateFamilyMember(Request $request, FamilyMember $familyMember): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'string', 'max:50'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Member Name is required.',
            'relation.required' => 'Relationship is required.',
        ]);

        $familyMember->update([
            'name' => trim($validated['name']),
            'relation' => strtoupper(trim($validated['relation'])),
            'gender' => $validated['gender'] ?? $familyMember->gender,
            'birth_date' => $validated['birth_date'] ?? null,
            'age' => $validated['age'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $customer = $familyMember->customer;
        $allMembers = $customer ? $customer->familyMembers()->get()->map(function ($fm) {
            return [
                'id' => $fm->id,
                'name' => $fm->name,
                'relation' => $fm->relation,
                'relation_label' => $fm->relation_label,
                'gender' => $fm->gender,
                'birth_date' => $fm->birth_date ? $fm->birth_date->format('Y-m-d') : '',
                'age' => $fm->age,
                'mobile' => $fm->mobile ?: '',
                'notes' => $fm->notes ?: '',
            ];
        }) : [];

        return response()->json([
            'success' => true,
            'message' => "Family member {$familyMember->name} updated successfully.",
            'member' => [
                'id' => $familyMember->id,
                'name' => $familyMember->name,
                'relation' => $familyMember->relation,
                'relation_label' => $familyMember->relation_label,
                'gender' => $familyMember->gender,
                'birth_date' => $familyMember->birth_date ? $familyMember->birth_date->format('Y-m-d') : '',
                'age' => $familyMember->age,
                'mobile' => $familyMember->mobile ?: '',
                'notes' => $familyMember->notes ?: '',
            ],
            'family_members' => $allMembers,
        ]);
    }

    /**
     * Delete a Family Member.
     */
    public function destroyFamilyMember(FamilyMember $familyMember): JsonResponse
    {
        $customer = $familyMember->customer;
        $name = $familyMember->name;
        $familyMember->delete();

        $allMembers = $customer ? $customer->familyMembers()->get()->map(function ($fm) {
            return [
                'id' => $fm->id,
                'name' => $fm->name,
                'relation' => $fm->relation,
                'relation_label' => $fm->relation_label,
                'gender' => $fm->gender,
                'birth_date' => $fm->birth_date ? $fm->birth_date->format('Y-m-d') : '',
                'age' => $fm->age,
                'mobile' => $fm->mobile ?: '',
                'notes' => $fm->notes ?: '',
            ];
        }) : [];

        return response()->json([
            'success' => true,
            'message' => "Family member {$name} removed.",
            'family_members' => $allMembers,
        ]);
    }

    /**
     * Synchronize application follow-up date with the CRM Follow-ups table.
     */
    protected function syncApplicationFollowUp(Application $application, ?string $followUpDate, ?string $remarks = null, ?int $userId = null): void
    {
        $cleanDate = !empty($followUpDate) ? date('Y-m-d', strtotime($followUpDate)) : null;

        if ($cleanDate) {
            $serviceName = $application->service?->main_service_name ?? 'Service';
            $subServiceName = $application->sub_service ?? '';
            $reason = $subServiceName ? "{$serviceName} - {$subServiceName}" : "Service Follow-up ({$serviceName})";

            FollowUp::updateOrCreate(
                [
                    'application_id' => $application->id,
                ],
                [
                    'tenant_id' => $application->tenant_id,
                    'branch_id' => $application->branch_id,
                    'customer_id' => $application->customer_id,
                    'assigned_employee_id' => $application->assigned_employee_id,
                    'follow_up_date' => $cleanDate,
                    'reason' => $reason,
                    'remarks' => $remarks ?: ($application->work_details ?: 'Fast Entry Desk Schedule'),
                    'status' => in_array($application->work_status, ['READY', 'DELIVERED', 'COMPLETED']) ? FollowUp::STATUS_COMPLETED : FollowUp::STATUS_PENDING,
                    'created_by' => $userId ?? Auth::id(),
                ]
            );
        } else {
            // If follow-up date was cleared, remove any pending follow-up for this application
            FollowUp::where('application_id', $application->id)
                ->where('status', FollowUp::STATUS_PENDING)
                ->delete();
        }
    }
}
