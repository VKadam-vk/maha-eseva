<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\ApplicationService;
use App\Services\AuditService;
use App\Services\CustomerService;
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

        $leads = $query->latest()->paginate(15)->withQueryString();
        $services = Service::where('is_active', true)->get();
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

        return view('leads.index', compact('leads', 'services', 'employees', 'branches', 'statuses'));
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
            'branch_id' => ['nullable', 'exists:branches,id'],
            'source' => ['required', 'in:WEBSITE,WALK_IN,PHONE,WHATSAPP,REFERRAL'],
            'message' => ['nullable', 'string'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? Tenant::first()?->id;
        $branchId = $validated['branch_id'] ?? $user?->branch_id ?? Branch::where('tenant_id', $tenantId)->first()?->id;

        $lead = Lead::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'source' => $validated['source'],
            'message' => $validated['message'] ?? null,
            'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
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
     * 1-Click Convert Lead to Customer and Application.
     */
    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $user = Auth::user();

        $result = DB::transaction(function () use ($lead, $user) {
            // 1. Create or fetch existing Customer
            $customer = $this->customerService->createCustomer([
                'branch_id' => $lead->branch_id,
                'name' => $lead->name,
                'mobile' => $lead->mobile,
                'email' => $lead->email,
                'gender' => 'MALE',
                'notes' => 'Converted from Lead #' . $lead->id . ' (Source: ' . $lead->source . ')',
            ], $user);

            // 2. If lead had requested service, create Application
            $application = null;
            if ($lead->service_id) {
                $application = $this->applicationService->createApplication([
                    'branch_id' => $lead->branch_id,
                    'customer_id' => $customer->id,
                    'service_id' => $lead->service_id,
                    'assigned_employee_id' => $lead->assigned_employee_id,
                    'work_status' => 'NEW',
                    'work_details' => $lead->message,
                ], $user);
            }

            // 3. Mark Lead as Converted
            $lead->update([
                'status' => Lead::STATUS_CONVERTED,
                'converted_customer_id' => $customer->id,
                'converted_application_id' => $application?->id,
            ]);

            AuditService::log('LEAD_CONVERTED', $lead, null, [
                'customer_id' => $customer->id,
                'application_id' => $application?->id,
            ]);

            return $customer;
        });

        return redirect()->route('customers.show', $result->id)
            ->with('success', 'Lead successfully converted into Customer profile.');
    }
}
