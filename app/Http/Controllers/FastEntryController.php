<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Employee;
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

        return view('fast_entry.index', compact('branches', 'services', 'employees'));
    }

    /**
     * Fast Quick-Go: Search by mobile/name. If customer does NOT exist, directly auto-save to CRM!
     */
    public function quickGo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'min:10', 'max:20'],
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

            $customer = $existing;
            $isNew = false;
        } else {
            // 2. Direct Auto-Save to Customers CRM
            $custName = trim($validated['customer_name'] ?? '');
            if (empty($custName)) {
                $custName = 'Citizen ' . substr($cleanedMobile, -4);
            }

            $customer = $this->customerService->createCustomer([
                'branch_id' => $branchId,
                'name' => $custName,
                'mobile' => $cleanedMobile,
                'gender' => 'MALE',
                'star_rating' => 5,
            ], $user);

            $isNew = true;
        }

        // Fetch customer's full applications & services history
        $applications = Application::where('customer_id', $customer->id)
            ->with(['service', 'assignedEmployee.user'])
            ->latest('id')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'application_number' => $app->application_number,
                    'service_name' => $app->service?->main_service_name ?? 'Government Service',
                    'sub_service' => $app->sub_service ?: ($app->service?->sub_service_name ?? '-'),
                    'application_date' => $app->application_date ? date('d-m-Y', strtotime($app->application_date)) : $app->created_at->format('d-m-Y'),
                    'rate' => (float) $app->total_amount,
                    'received' => (float) $app->received_amount,
                    'remaining' => (float) $app->remaining_amount,
                    'status' => $app->work_status,
                    'service_id' => $app->service_id,
                    'expected_days' => (int) ($app->service?->expected_processing_days ?? 7),
                    'main_service_name' => $app->service?->main_service_name ?? 'Service',
                    'work_details' => $app->work_details ?? '',
                    'follow_up_date' => $app->follow_up_date ? date('Y-m-d', strtotime($app->follow_up_date)) : '',
                    'portal_link' => $app->service?->work_portal_link ?: $app->service?->service_portal_link,
                    'tutorial_link' => $app->service?->video_instruction_link,
                    'assigned_employee_id' => $app->assigned_employee_id,
                    'operator' => $app->assignedEmployee?->user?->name ?? 'Counter Desk',
                    'show_url' => route('applications.show', $app->id),
                ];
            });

        return response()->json([
            'success' => true,
            'is_new' => $isNew,
            'message' => $isNew
                ? "New Customer Auto-Saved to CRM: {$customer->customer_code} ({$customer->name})"
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
                'total_services' => $applications->count(),
                'total_billed' => (float) $customer->total_billed,
                'total_paid' => (float) $customer->total_paid,
                'total_outstanding' => (float) $customer->total_outstanding,
                'profile_url' => route('customers.show', $customer->id),
            ],
            'applications' => $applications,
        ]);
    }

    /**
     * Update customer optional details (gender, DOB, rating, email, occupation) from right panel.
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
            ]
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
            'work_status' => ['nullable', 'string'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'sub_service' => ['nullable', 'string', 'max:255'],
            'work_details' => ['nullable', 'string'],
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

        $application->update([
            'total_amount' => $rate,
            'base_amount' => $rate,
            'received_amount' => $received,
            'remaining_amount' => $remaining,
            'payment_status' => $remaining <= 0 ? Application::PAYMENT_PAID : ($received > 0 ? Application::PAYMENT_PARTIAL : Application::PAYMENT_PENDING),
            'work_status' => $workStatus,
            'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
            'sub_service' => $validated['sub_service'] ?? $application->sub_service,
            'work_details' => array_key_exists('work_details', $validated) ? $validated['work_details'] : $application->work_details,
            'follow_up_date' => array_key_exists('follow_up_date', $validated) ? $validated['follow_up_date'] : $application->follow_up_date,
        ]);

        $customer = $application->customer;

        return response()->json([
            'success' => true,
            'message' => "Service Updated: {$application->application_number}",
            'data' => [
                'id' => $application->id,
                'application_number' => $application->application_number,
                'service_name' => $application->service?->main_service_name ?? 'Service',
                'sub_service' => $application->sub_service,
                'expected_days' => (int) ($application->service?->expected_processing_days ?? 7),
                'main_service_name' => $application->service?->main_service_name ?? 'Service',
                'rate' => number_format($application->total_amount, 2),
                'received' => number_format($application->received_amount, 2),
                'remaining' => number_format($application->remaining_amount, 2),
                'status' => $application->work_status,
                'assigned_employee_id' => $application->assigned_employee_id,
                'work_details' => $application->work_details ?? '',
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
                    $q->orWhere('mobile', 'like', "%{$cleanedMobile}%");
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
                'gender' => $c->gender,
                'star_rating' => $c->star_rating ?? 5,
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
            'service_id' => ['required', 'exists:services,id'],
            'sub_service' => ['nullable', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0'],
            'received' => ['nullable', 'numeric', 'min:0'],
            'work_details' => ['nullable', 'string'],
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

        try {
            $application = DB::transaction(function () use ($validated, $user, $customer, $service) {
                $rate = (float) $validated['rate'];
                $received = isset($validated['received']) ? (float) $validated['received'] : 0.00;
                $workStatus = $validated['work_status'] ?? Application::STATUS_NEW;
                if ($workStatus === 'DONE') {
                    $workStatus = Application::STATUS_READY;
                }

                $app = $this->applicationService->createApplication([
                    'customer_id' => $customer->id,
                    'service_id' => $service->id,
                    'sub_service' => $validated['sub_service'] ?? $service->sub_service_name,
                    'branch_id' => $customer->branch_id ?? $user->branch_id,
                    'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
                    'application_date' => now()->toDateString(),
                    'base_amount' => $rate,
                    'received_amount' => $received,
                    'work_status' => $workStatus,
                    'work_details' => $validated['work_details'] ?? null,
                    'documents_list' => $validated['documents_list'] ?? null,
                    'pending_remarks' => $validated['pending_remarks'] ?? null,
                    'follow_up_date' => $validated['follow_up_date'] ?? null,
                ], $user);

                return $app->load(['service', 'customer', 'assignedEmployee.user']);
            });

            return response()->json([
                'success' => true,
                'message' => "Service Entry Recorded: {$service->main_service_name} for {$customer->name} (SR: {$application->application_number})",
                'data' => [
                    'id' => $application->id,
                    'application_number' => $application->application_number,
                    'customer_name' => $customer->name,
                    'customer_url' => route('customers.show', $customer->id),
                    'service_name' => $service->main_service_name,
                    'sub_service' => $application->sub_service,
                    'rate' => number_format($application->total_amount, 2),
                    'received' => number_format($application->received_amount, 2),
                    'remaining' => number_format($application->remaining_amount, 2),
                    'status' => $application->work_status,
                    'expected_days' => (int) ($service->expected_processing_days ?? 7),
                    'main_service_name' => $service->main_service_name,
                    'work_details' => $application->work_details ?? '',
                    'follow_up_date' => $application->follow_up_date ? date('Y-m-d', strtotime($application->follow_up_date)) : '',
                    'portal_link' => $service->work_portal_link ?: $service->service_portal_link,
                    'tutorial_link' => $service->video_instruction_link,
                    'operator' => $application->assignedEmployee?->user?->name ?? 'Unassigned',
                    'show_url' => route('applications.show', $application->id),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
