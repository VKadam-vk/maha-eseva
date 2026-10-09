<?php

namespace App\Http\Controllers;

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
     * Display follow-ups with filtering by status and date.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = FollowUp::with(['customer', 'application.service', 'assignedEmployee.user']);

        if ($user->isEmployee()) {
            $query->where('assigned_employee_id', $user->employee?->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('follow_up_date', $request->date);
        }

        $followUps = $query->orderBy('follow_up_date', 'asc')->paginate(15)->withQueryString();
        $employees = $user->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();
        $customers = Customer::latest()->limit(50)->get();

        $dueCount = FollowUp::where('status', FollowUp::STATUS_PENDING)->whereDate('follow_up_date', '<=', now()->toDateString())->count();

        return view('followups.index', compact('followUps', 'employees', 'customers', 'dueCount'));
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

        $followUp->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ? $followUp->remarks . "\n[Update: " . $validated['remarks'] . "]" : $followUp->remarks,
        ]);

        return back()->with('success', "Follow-up marked as {$validated['status']}.");
    }
}
