<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Service;
use App\Services\ApplicationService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $query = Application::with(['customer', 'service', 'assignedEmployee.user', 'branch']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('application_number', 'like', "%{$s}%")
                  ->orWhere('external_acknowledgement_no', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($c) use ($s) {
                      $c->where('name', 'like', "%{$s}%")
                        ->orWhere('mobile', 'like', "%{$s}%")
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

        $applications = $query->latest('application_date')->latest('id')->paginate(15)->withQueryString();
        $services = $user->allowedServices()->get();
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

        return view('applications.index', compact('applications', 'services', 'employees', 'branches', 'statuses'));
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

        $application->update($validated);

        return back()->with('success', 'Application details updated.');
    }
}
