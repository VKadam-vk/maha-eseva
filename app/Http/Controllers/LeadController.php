<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\ApplicationService;
use App\Services\AuditService;
use App\Services\CustomerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected ApplicationService $applicationService
    ) {}

    /**
     * Leads / Website enquiries listing.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Lead::with(['service', 'branch', 'assignedEmployee.user', 'convertedCustomer', 'convertedApplication']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('followup')) {
            if ($request->followup === 'today') {
                $query->whereDate('next_follow_up_date', Carbon::today());
            } elseif ($request->followup === 'upcoming') {
                $query->whereDate('next_follow_up_date', '>', Carbon::today());
            } elseif ($request->followup === 'overdue') {
                $query->whereDate('next_follow_up_date', '<', Carbon::today());
            }
        }

        $leads = $query->latest()->paginate(15)->withQueryString();
        $services = Service::where('is_active', true)->get();
        $uniqueMainServices = $services->pluck('main_service_name')->filter()->unique()->sort()->values();
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        $statuses = [
            Lead::STATUS_NEW,
            Lead::STATUS_CONTACTED,
            Lead::STATUS_FOLLOW_UP,
            Lead::STATUS_QUALIFIED,
            Lead::STATUS_CONVERTED,
            Lead::STATUS_LOST,
        ];

        return view('leads.index', compact('leads', 'services', 'uniqueMainServices', 'employees', 'branches', 'statuses'));
    }

    /**
     * Store new lead (From internal staff or public website API).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_id' => ['nullable', 'exists:services,id'],
            'sub_service' => ['nullable', 'string', 'max:150'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'source' => ['required', 'in:WEBSITE,WALK_IN,PHONE,WHATSAPP,REFERRAL'],
            'message' => ['nullable', 'string'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'next_follow_up_date' => ['nullable', 'date'],
            'next_follow_up_time' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? Tenant::first()?->id;
        $branchId = $validated['branch_id'] ?? $user?->branch_id ?? Branch::where('tenant_id', $tenantId)->first()?->id;

        $subService = $validated['sub_service'] ?? null;
        if (!$subService && !empty($validated['service_id'])) {
            $selectedService = Service::find($validated['service_id']);
            $subService = $selectedService?->sub_service_name;
        }

        $lead = Lead::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'sub_service' => $subService,
            'source' => $validated['source'],
            'message' => $validated['message'] ?? null,
            'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'next_follow_up_time' => $validated['next_follow_up_time'] ?? null,
            'status' => Lead::STATUS_NEW,
        ]);

        AuditService::log('LEAD_CREATED', $lead);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your enquiry has been received. Our team will contact you shortly.',
                'lead_id' => $lead->id,
            ]);
        }

        return back()->with('success', 'Lead recorded successfully.');
    }

    /**
     * Update lead follow-up date and time inline.
     */
    public function updateFollowUp(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'next_follow_up_date' => ['nullable', 'date'],
            'next_follow_up_time' => ['nullable', 'string', 'max:20'],
        ]);

        $lead->update([
            'next_follow_up_date' => $validated['next_follow_up_date'] ?: null,
            'next_follow_up_time' => $validated['next_follow_up_time'] ?: null,
        ]);

        AuditService::log('LEAD_FOLLOWUP_UPDATED', $lead);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Next follow-up updated successfully.',
                'next_follow_up_date' => $lead->next_follow_up_date?->format('Y-m-d'),
                'next_follow_up_time' => $lead->next_follow_up_time,
            ]);
        }

        return back()->with('success', 'Next follow-up date and time updated.');
    }

    /**
     * Update lead status.
     */
    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:NEW,CONTACTED,FOLLOW_UP,QUALIFIED,CONVERTED,LOST'],
            'remarks' => ['nullable', 'string'],
        ]);

        $lead->update($validated);
        AuditService::log('LEAD_STATUS_UPDATED', $lead);

        return back()->with('success', 'Lead status updated.');
    }

    /**
     * 1-Click Convert Lead to Customer and Application, then load into Fast Daily Entry.
     */
    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $user = Auth::user();
        $targetBranchId = $user->branch_id ?? $lead->branch_id ?? Tenant::first()?->branches()->first()?->id;

        $result = DB::transaction(function () use ($lead, $user, $targetBranchId) {
            $cleanedMobile = preg_replace('/[^0-9]/', '', $lead->mobile);

            // 1. Check if customer already exists for this mobile across the tenant
            $customer = Customer::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id ?? Tenant::first()?->id)
                ->where(function ($q) use ($lead, $cleanedMobile) {
                    $q->where('mobile', $lead->mobile)
                      ->orWhere('mobile', $cleanedMobile);
                })
                ->first();

            if ($customer) {
                // Ensure customer belongs to active branch so the branch admin/operator can see and manage it
                if ($targetBranchId && (int)$customer->branch_id !== (int)$targetBranchId) {
                    $customer->update(['branch_id' => $targetBranchId]);
                }
            } else {
                $customer = $this->customerService->createCustomer([
                    'branch_id' => $targetBranchId,
                    'name' => $lead->name,
                    'mobile' => $lead->mobile,
                    'email' => $lead->email,
                    'gender' => 'MALE',
                    'notes' => 'Converted from Lead #' . $lead->id . ' (Source: ' . $lead->source . ')',
                ], $user);
            }

            // 2. If lead had requested service, create Application
            $application = null;
            if ($lead->service_id) {
                if ($lead->converted_application_id) {
                    $application = Application::withoutGlobalScopes()->find($lead->converted_application_id);
                    if ($application && $targetBranchId && (int)$application->branch_id !== (int)$targetBranchId) {
                        $application->update(['branch_id' => $targetBranchId]);
                    }
                }

                if (!$application) {
                    $application = $this->applicationService->createApplication([
                        'branch_id' => $targetBranchId,
                        'customer_id' => $customer->id,
                        'service_id' => $lead->service_id,
                        'assigned_employee_id' => $lead->assigned_employee_id ?? $user->employee?->id,
                        'work_status' => 'NEW',
                        'work_details' => $lead->message,
                    ], $user);
                }
            }

            // 3. Mark Lead as Converted
            $lead->update([
                'branch_id' => $targetBranchId,
                'status' => Lead::STATUS_CONVERTED,
                'converted_customer_id' => $customer->id,
                'converted_application_id' => $application?->id,
            ]);

            AuditService::log('LEAD_CONVERTED', $lead, null, [
                'customer_id' => $customer->id,
                'application_id' => $application?->id,
            ]);

            return [
                'customer' => $customer,
                'application' => $application,
            ];
        });

        return redirect()->route('fast_entry.index', [
            'mobile' => $result['customer']->mobile,
            'customer_name' => $result['customer']->name,
            'service_id' => $lead->service_id,
        ])->with('success', "Lead #{$lead->id} converted successfully! Loaded citizen {$result['customer']->name} in Fast Daily Entry.");
    }
}
