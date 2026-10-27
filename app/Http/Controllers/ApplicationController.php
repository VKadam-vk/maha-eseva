<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationFieldHistory;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Service;
use App\Services\ApplicationService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display list of applications with filter filters and server-side pagination.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Application::with(['customer', 'service', 'assignedEmployee.user', 'branch', 'familyMember']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('application_number', 'like', "%{$s}%")
                  ->orWhere('external_acknowledgement_no', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($c) use ($s) {
                      $c->where('name', 'like', "%{$s}%")
                        ->orWhere('mobile', 'like', "%{$s}%")
                        ->orWhere('alternate_mobile', 'like', "%{$s}%")
                        ->orWhere('customer_code', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('work_status')) {
            $query->where('work_status', $request->work_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('assigned_employee_id', $request->employee_id);
        }

        if ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('application_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('application_date', '<=', $request->to_date);
        }

        // Restrict employee visibility to only their allowed services
        if ($user->isEmployee() && $user->employee && $user->employee->services()->exists()) {
            $allowedServiceIds = $user->employee->services()->pluck('services.id');
            $query->whereIn('service_id', $allowedServiceIds);
        }

        $totalCount = (clone $query)->count();
        $totalBilled = (clone $query)->sum('total_amount');
        $totalReceived = (clone $query)->sum('received_amount');
        $totalRemaining = max(0, $totalBilled - $totalReceived);

        $applications = $query->latest('id')->paginate(30)->withQueryString();
        $services = $user->allowedServices()->get();
        $activeServices = Service::where('is_active', true)->with('category')->get();
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        $statuses = [
            Application::STATUS_NEW,
            Application::STATUS_DOCUMENT_PENDING,
            Application::STATUS_DOCUMENT_VERIFIED,
            Application::STATUS_IN_PROCESS,
            Application::STATUS_SUBMITTED,
            Application::STATUS_UNDER_PROCESS,
            Application::STATUS_RETURNED_CORRECTION,
            Application::STATUS_APPROVED,
            Application::STATUS_READY,
            Application::STATUS_DELIVERED,
            Application::STATUS_CLOSED,
            Application::STATUS_ON_HOLD,
            Application::STATUS_REJECTED,
            Application::STATUS_CANCELLED,
        ];

        return view('applications.index', compact(
            'applications',
            'services',
            'activeServices',
            'employees',
            'branches',
            'statuses',
            'totalCount',
            'totalBilled',
            'totalReceived',
            'totalRemaining'
        ));
    }

    /**
     * Show create application form.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $customerId = $request->query('customer_id');
        $selectedCustomer = $customerId ? Customer::find($customerId) : null;
        
        $services = $user->allowedServices()->with('customFields')->get();
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();
        $customers = Customer::latest()->limit(50)->get();

        return view('applications.create', compact('services', 'employees', 'branches', 'customers', 'selectedCustomer'));
    }

    /**
     * Store new application with dynamic custom fields.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'application_date' => ['required', 'date'],
            'base_amount' => ['nullable', 'numeric', 'min:0'],
            'govt_fee' => ['nullable', 'numeric', 'min:0'],
            'service_charge' => ['nullable', 'numeric', 'min:0'],
            'additional_charges' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'in:CASH,UPI,BANK_TRANSFER,CARD,CHEQUE,WALLET'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'external_acknowledgement_no' => ['nullable', 'string', 'max:100'],
            'work_details' => ['nullable', 'string'],
            'pending_remarks' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $user = Auth::user();
        if (!$user->canAccessService((int) $validated['service_id'])) {
            return back()->withErrors(['service_id' => 'Unauthorized: You do not have permission to access or create applications for this service.'])->withInput();
        }

        $application = $this->applicationService->createApplication($validated, $user);
        
        // Notify customer via SMS / WhatsApp simulation
        $this->notificationService->notifyApplicationCreated($application);

        return redirect()->route('applications.show', $application->id)
            ->with('success', "Application {$application->application_number} created successfully.");
    }

    /**
     * Display application details, dynamic values, workflow actions, documents, payments.
     */
    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'customer',
            'service.category',
            'service.customFields',
            'assignedEmployee.user',
            'branch',
            'customValues.field',
            'statusHistories.changer',
            'documents.verifier',
            'payments.receiver',
            'followUps.assignedEmployee.user',
            'tasks.employee.user',
        ]);

        $employees = Auth::user()->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();

        $statuses = [
            Application::STATUS_NEW,
            Application::STATUS_DOCUMENT_PENDING,
            Application::STATUS_DOCUMENT_VERIFIED,
            Application::STATUS_IN_PROCESS,
            Application::STATUS_SUBMITTED,
            Application::STATUS_UNDER_PROCESS,
            Application::STATUS_RETURNED_CORRECTION,
            Application::STATUS_APPROVED,
            Application::STATUS_READY,
            Application::STATUS_DELIVERED,
            Application::STATUS_CLOSED,
            Application::STATUS_ON_HOLD,
            Application::STATUS_REJECTED,
            Application::STATUS_CANCELLED,
        ];

        return view('applications.show', compact('application', 'employees', 'statuses'));
    }

    /**
     * Print official citizen service acknowledgement & payment receipt.
     */
    public function printReceipt(Application $application): View
    {
        $application->load(['customer', 'service', 'assignedEmployee.user', 'branch.tenant', 'payments.receiver']);
        return view('applications.print_receipt', compact('application'));
    }

    /**
     * Update application workflow status.
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'work_status' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->applicationService->updateStatus($application, $validated['work_status'], $validated['remarks'] ?? null, Auth::user());
        
        // Trigger status notification
        $this->notificationService->notifyStatusChanged($application->fresh(), $validated['work_status']);

        return back()->with('success', "Application status updated to {$validated['work_status']}.");
    }

    /**
     * Update employee assignment or acknowledgement number.
     */
    public function updateDetails(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'external_acknowledgement_no' => ['nullable', 'string', 'max:100'],
            'external_portal_login_user' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'work_details' => ['nullable', 'string'],
            'pending_remarks' => ['nullable', 'string'],
        ]);

        $oldAssigned = $application->assigned_employee_id;
        $application->update($validated);

        if (array_key_exists('assigned_employee_id', $validated)) {
            ApplicationFieldHistory::recordIfChanged($application, 'assigned_employee_id', 'Operator', $oldAssigned, $validated['assigned_employee_id'], null, null, Auth::id());
        }

        return back()->with('success', 'Application details updated.');
    }

    /**
     * Retrieve audit change history for application fields.
     */
    public function fieldHistory(Request $request, Application $application): JsonResponse
    {
        $field = $request->query('field');

        $query = ApplicationFieldHistory::where('application_id', $application->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $aliases = [
            'status' => 'work_status',
            'operator' => 'assigned_employee_id',
            'followup' => 'follow_up_date',
            'followup_date' => 'follow_up_date',
            'service' => 'sub_service',
            'subservice' => 'sub_service',
        ];
        $targetField = ($field && isset($aliases[$field])) ? $aliases[$field] : $field;

        if ($targetField && $targetField !== 'all') {
            $query->where('field_name', $targetField);
        }

        $histories = $query->limit(60)->get();

        $counts = ApplicationFieldHistory::where('application_id', $application->id)
            ->select('field_name', DB::raw('count(*) as aggregate'))
            ->groupBy('field_name')
            ->pluck('aggregate', 'field_name');

        return response()->json([
            'success' => true,
            'application' => [
                'id' => $application->id,
                'application_number' => $application->application_number,
                'customer_name' => $application->customer?->name ?? 'Customer',
                'service_name' => $application->service?->main_service_name ?? 'Service',
                'sub_service' => $application->sub_service ?? '',
                'current_status' => $application->work_status,
                'current_operator' => $application->assignedEmployee?->user?->name ?? 'Unassigned',
                'current_rate' => number_format($application->total_amount, 2),
                'current_followup' => $application->follow_up_date ? date('d M Y', strtotime($application->follow_up_date)) : 'Not Set',
                'created_at' => $application->created_at ? $application->created_at->format('d M Y, h:i A') : '',
            ],
            'selected_field' => $targetField ?: 'all',
            'counts' => $counts,
            'histories' => $histories->map(function ($h) {
                return [
                    'id' => $h->id,
                    'field_name' => $h->field_name,
                    'field_label' => $h->field_label,
                    'old_value' => $h->old_value,
                    'new_value' => $h->new_value,
                    'old_display' => $h->old_display ?? '-',
                    'new_display' => $h->new_display ?? '-',
                    'remarks' => $h->remarks,
                    'user_name' => $h->user?->name ?? 'System',
                    'created_at' => $h->created_at ? $h->created_at->format('d M Y, h:i A') : '',
                    'time_ago' => $h->created_at ? $h->created_at->diffForHumans() : '',
                ];
            }),
        ]);
    }
}
