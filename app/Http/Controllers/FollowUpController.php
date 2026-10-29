<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FollowUp;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    /**
     * Display follow-ups with automatic daily filtering and Fast-Entry spreadsheet data.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        // 1. Determine active date / filter mode:
        // By DEFAULT, if no date and no mode is passed, automatically filter by TODAY's date!
        $mode = $request->get('mode'); // 'overdue', 'tomorrow', 'all', or null
        $selectedDate = $request->get('date');

        if (!$selectedDate && !$mode) {
            $selectedDate = $today; // Automatically today!
        }

        // 2. Query Applications with follow_up_date
        $query = Application::with(['customer', 'service.category', 'assignedEmployee.user', 'payments'])
            ->whereNotNull('follow_up_date');

        if ($user->isEmployee()) {
            $query->where('assigned_employee_id', $user->employee?->id);
        }

        if ($request->filled('status')) {
            $query->where('work_status', $request->status);
        }

        // Filter according to mode or selected date
        if ($mode === 'overdue') {
            $query->whereDate('follow_up_date', '<', $today)
                  ->whereNotIn('work_status', ['COMPLETED', 'DELIVERED', 'READY']);
        } elseif ($mode === 'tomorrow') {
            $query->whereDate('follow_up_date', $tomorrow);
            $selectedDate = $tomorrow;
        } elseif ($mode === 'all') {
            // All scheduled follow-ups
        } else {
            // Strict match for the selected date (defaults automatically to today!)
            $query->whereDate('follow_up_date', $selectedDate);
        }

        $applications = $query->orderBy('follow_up_date', 'asc')->get();

        // 3. Counts for quick switcher bar
        $todayCountQuery = Application::whereNotNull('follow_up_date')->whereDate('follow_up_date', $today);
        $tomorrowCountQuery = Application::whereNotNull('follow_up_date')->whereDate('follow_up_date', $tomorrow);
        $overdueCountQuery = Application::whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', $today)
            ->whereNotIn('work_status', ['COMPLETED', 'DELIVERED', 'READY']);
        $allCountQuery = Application::whereNotNull('follow_up_date');

        if ($user->isEmployee()) {
            $empId = $user->employee?->id;
            $todayCountQuery->where('assigned_employee_id', $empId);
            $tomorrowCountQuery->where('assigned_employee_id', $empId);
            $overdueCountQuery->where('assigned_employee_id', $empId);
            $allCountQuery->where('assigned_employee_id', $empId);
        }

        $todayCount = $todayCountQuery->count();
        $tomorrowCount = $tomorrowCountQuery->count();
        $overdueCount = $overdueCountQuery->count();
        $allCount = $allCountQuery->count();

        // 4. Auxiliary master data
        $activeServices = \App\Models\Service::where('is_active', true)->with('category')->get();
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();
        $customers = Customer::latest()->limit(50)->get();

        // 5. Aggregate metrics for the active view
        $totalBilled = $applications->sum(fn($a) => (float) ($a->total_amount ?? $a->rate ?? 0));
        $totalReceived = $applications->sum('received_amount');
        $totalRemaining = max(0, $totalBilled - $totalReceived);
        $totalCount = $applications->count();
        $existingPaymentModes = \App\Models\Application::getAvailablePaymentModes($user->tenant_id);

        return view('followups.index', compact(
            'applications',
            'activeServices',
            'employees',
            'customers',
            'selectedDate',
            'mode',
            'today',
            'tomorrow',
            'todayCount',
            'tomorrowCount',
            'overdueCount',
            'allCount',
            'totalBilled',
            'totalReceived',
            'totalRemaining',
            'totalCount',
            'existingPaymentModes'
        ));
    }

    /**
     * Store new follow-up.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'application_id' => ['nullable', 'exists:applications,id'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'follow_up_date' => ['required', 'date'],
            'follow_up_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        $followUp = FollowUp::create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id ?? $user->tenant->branches()->first()?->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'application_id' => $validated['application_id'] ?? null,
            'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
            'follow_up_date' => $validated['follow_up_date'],
            'follow_up_time' => $validated['follow_up_time'] ?? null,
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'] ?? null,
            'status' => FollowUp::STATUS_PENDING,
            'created_by' => $user->id,
        ]);

        AuditService::log('FOLLOWUP_CREATED', $followUp);

        return back()->with('success', 'Follow-up scheduled successfully.');
    }

    /**
     * Update follow-up status.
     */
    public function updateStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:PENDING,COMPLETED,MISSED,CANCELLED'],
            'remarks' => ['nullable', 'string'],
        ]);

        $newRemarks = !empty($validated['remarks'])
            ? ($followUp->remarks ? $followUp->remarks . "\n[Update: " . $validated['remarks'] . "]" : $validated['remarks'])
            : $followUp->remarks;

        $followUp->update([
            'status' => $validated['status'],
            'remarks' => $newRemarks,
        ]);

        // If follow-up was completed and it has an application, keep work_status in sync if needed
        if ($validated['status'] === FollowUp::STATUS_COMPLETED && $followUp->application) {
            if ($followUp->application->work_status === 'PENDING') {
                $followUp->application->update(['work_status' => 'IN_PROGRESS']);
            }
        }

        return back()->with('success', "Follow-up marked as {$validated['status']}.");
    }
}
