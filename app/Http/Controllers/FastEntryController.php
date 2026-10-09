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
     * Show fast spreadsheet-style daily data entry grid.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $services = $user->allowedServices()->orderBy('main_service_name')->get();
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        // Recent today's quick entries
        $recentApplications = Application::with(['customer', 'service', 'assignedEmployee.user'])
            ->whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->limit(20)
            ->get();

        return view('fast_entry.index', compact('services', 'employees', 'branches', 'recentApplications'));
    }

    /**
     * Fast Quick-Add row via AJAX for instant spreadsheet saving.
     */
    public function storeRow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
            'service_id' => ['required', 'exists:services,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'base_amount' => ['nullable', 'numeric', 'min:0'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'in:CASH,UPI,BANK_TRANSFER,CARD,CHEQUE,WALLET'],
            'remarks' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        if (!$user->canAccessService((int) $validated['service_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not have permission to process this service.',
            ], 403);
        }

        try {
            $application = DB::transaction(function () use ($validated, $user) {
                // 1. Create or fetch customer
                $customer = $this->customerService->createCustomer([
                    'branch_id' => $validated['branch_id'] ?? $user->branch_id,
                    'name' => $validated['customer_name'],
                    'mobile' => $validated['mobile'],
                    'gender' => $validated['gender'] ?? 'MALE',
                ], $user);

                // 2. Create application
                $app = $this->applicationService->createApplication([
                    'customer_id' => $customer->id,
                    'service_id' => $validated['service_id'],
                    'branch_id' => $validated['branch_id'] ?? $user->branch_id,
                    'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
                    'application_date' => now()->toDateString(),
                    'base_amount' => $validated['base_amount'] ?? null,
                    'received_amount' => $validated['received_amount'] ?? 0.00,
                    'payment_mode' => $validated['payment_mode'] ?? 'CASH',
                    'work_details' => $validated['remarks'] ?? 'Quick Daily Counter Entry',
                ], $user);

                return $app->load(['customer', 'service', 'assignedEmployee.user']);
            });

            return response()->json([
                'success' => true,
                'message' => "Saved: {$application->application_number} ({$application->customer->name})",
                'data' => [
                    'id' => $application->id,
                    'sr_number' => $application->application_number,
                    'customer_name' => $application->customer->name,
                    'mobile' => $application->customer->mobile,
                    'service_name' => $application->service->full_name,
                    'employee_name' => $application->assignedEmployee?->user?->name ?? 'Unassigned',
                    'total_amount' => number_format($application->total_amount, 2),
                    'received_amount' => number_format($application->received_amount, 2),
                    'remaining_amount' => number_format($application->remaining_amount, 2),
                    'work_status' => $application->work_status,
                    'payment_status' => $application->payment_status,
                    'url' => route('applications.show', $application->id),
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
