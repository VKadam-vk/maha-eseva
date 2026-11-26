<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Branch;
use App\Models\CashAdjustment;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Aggregated metrics for Business Owner Dashboard.
     * When $branchId is provided, scopes all performance and financial metrics strictly to that branch.
     */
    public function getBusinessOwnerDashboard(int $tenantId, ?int $branchId = null): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $totalBranches = Branch::where('tenant_id', $tenantId)->count();
        $totalCustomers = Customer::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $totalApplications = Application::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $totalRevenue = (float) Payment::where('tenant_id', $tenantId)->where('payment_status', 'SUCCESS')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('amount');
        
        $todayCustomers = Customer::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->whereDate('created_at', $today)->count();
        $todayApplications = Application::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->whereDate('created_at', $today)->count();
        $todayRevenue = (float) Payment::where('tenant_id', $tenantId)->where('payment_status', 'SUCCESS')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->whereDate('payment_date', $today)->sum('amount');
        $monthRevenue = (float) Payment::where('tenant_id', $tenantId)->where('payment_status', 'SUCCESS')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->where('payment_date', 'like', "{$thisMonth}%")->sum('amount');

        $pendingApplications = Application::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED, Application::STATUS_REJECTED, Application::STATUS_CANCELLED])
            ->count();

        $completedApplications = Application::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        // Quick Cash In (Receive) and Cash Out (Spend) calculations
        $totalExtraReceived = 0.0;
        $totalSpent = 0.0;
        $todayExtraReceived = 0.0;
        $todaySpent = 0.0;

        if (\Illuminate\Support\Facades\Schema::hasTable('cash_adjustments')) {
            $adjQuery = CashAdjustment::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId));
            $totalExtraReceived = (float) (clone $adjQuery)->where('type', 'RECEIVE')->sum('amount');
            $totalSpent = (float) (clone $adjQuery)->where('type', 'SPEND')->sum('amount');
            $todayExtraReceived = (float) (clone $adjQuery)->where('type', 'RECEIVE')->whereDate('entry_date', $today)->sum('amount');
            $todaySpent = (float) (clone $adjQuery)->where('type', 'SPEND')->whereDate('entry_date', $today)->sum('amount');
        }

        $netRevenue = ($totalRevenue + $totalExtraReceived) - $totalSpent;
        $todayNetRevenue = ($todayRevenue + $todayExtraReceived) - $todaySpent;

        // Status pipeline breakdown
        $statusCounts = Application::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('work_status', DB::raw('count(*) as count'))
            ->groupBy('work_status')
            ->pluck('count', 'work_status')
            ->toArray();

        // Top 5 Services by volume
        $topServices = Service::where('services.tenant_id', $tenantId)
            ->join('applications', 'applications.service_id', '=', 'services.id')
            ->when($branchId, fn($q) => $q->where('applications.branch_id', $branchId))
            ->select('services.id', 'services.main_service_name', 'services.sub_service_name', DB::raw('count(applications.id) as app_count'), DB::raw('sum(applications.total_amount) as total_revenue'))
            ->groupBy('services.id', 'services.main_service_name', 'services.sub_service_name')
            ->orderByDesc('app_count')
            ->limit(5)
            ->get();

        // Branch summary
        $branchPerformance = Branch::where('tenant_id', $tenantId)
            ->withCount(['customers', 'applications'])
            ->get();

        // Recent 5 applications
        $recentApplications = Application::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['customer', 'service', 'assignedEmployee.user'])
            ->latest()
            ->limit(5)
            ->get();

        return compact(
            'totalBranches', 'totalCustomers', 'totalApplications', 'totalRevenue',
            'todayCustomers', 'todayApplications', 'todayRevenue', 'monthRevenue',
            'pendingApplications', 'completedApplications', 'statusCounts',
            'topServices', 'branchPerformance', 'recentApplications',
            'totalExtraReceived', 'totalSpent', 'netRevenue', 'todayExtraReceived', 'todaySpent', 'todayNetRevenue'
        );
    }

    /**
     * Aggregated metrics for Branch Admin Dashboard.
     */
    public function getBranchAdminDashboard(int $tenantId, int $branchId): array
    {
        $today = now()->toDateString();

        $todayCustomers = Customer::where('tenant_id', $tenantId)->where('branch_id', $branchId)->whereDate('created_at', $today)->count();
        $todayApplications = Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)->whereDate('created_at', $today)->count();
        $todayCollection = (float) Payment::where('tenant_id', $tenantId)->where('branch_id', $branchId)->where('payment_status', 'SUCCESS')->whereDate('payment_date', $today)->sum('amount');
        $totalCollection = (float) Payment::where('tenant_id', $tenantId)->where('branch_id', $branchId)->where('payment_status', 'SUCCESS')->sum('amount');
        $totalBilled = (float) Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)->sum('total_amount');
        $pendingAmount = (float) Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)->sum('remaining_amount');
        $totalCustomers = Customer::where('tenant_id', $tenantId)->where('branch_id', $branchId)->count();
        $totalApplications = Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)->count();

        $pendingWork = Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED, Application::STATUS_REJECTED, Application::STATUS_CANCELLED])
            ->count();

        $completedWork = Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)
            ->whereIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        $dueFollowUps = FollowUp::where('tenant_id', $tenantId)->where('branch_id', $branchId)
            ->where('status', FollowUp::STATUS_PENDING)
            ->whereDate('follow_up_date', '<=', $today)
            ->with(['customer', 'application'])
            ->get();

        $recentApplications = Application::where('tenant_id', $tenantId)->where('branch_id', $branchId)
            ->with(['customer', 'service', 'assignedEmployee.user'])
            ->latest()
            ->limit(5)
            ->get();

        // Quick Cash In (Receive) and Cash Out (Spend) calculations
        $totalExtraReceived = 0.0;
        $totalSpent = 0.0;
        $todayExtraReceived = 0.0;
        $todaySpent = 0.0;

        if (\Illuminate\Support\Facades\Schema::hasTable('cash_adjustments')) {
            $adjQuery = CashAdjustment::where('tenant_id', $tenantId)->where('branch_id', $branchId);
            $totalExtraReceived = (float) (clone $adjQuery)->where('type', 'RECEIVE')->sum('amount');
            $totalSpent = (float) (clone $adjQuery)->where('type', 'SPEND')->sum('amount');
            $todayExtraReceived = (float) (clone $adjQuery)->where('type', 'RECEIVE')->whereDate('entry_date', $today)->sum('amount');
            $todaySpent = (float) (clone $adjQuery)->where('type', 'SPEND')->whereDate('entry_date', $today)->sum('amount');
        }

        $netCollection = ($totalCollection + $totalExtraReceived) - $totalSpent;
        $todayNetCollection = ($todayCollection + $todayExtraReceived) - $todaySpent;

        return compact(
            'todayCustomers', 'todayApplications', 'todayCollection', 'totalCollection',
            'totalBilled', 'pendingAmount',
            'totalCustomers', 'totalApplications', 'pendingWork',
            'completedWork', 'dueFollowUps', 'recentApplications',
            'totalExtraReceived', 'totalSpent', 'netCollection', 'todayExtraReceived', 'todaySpent', 'todayNetCollection'
        );
    }

    /**
     * Aggregated metrics for Employee Dashboard.
     */
    public function getEmployeeDashboard(int $tenantId, int $employeeId): array
    {
        $today = now()->toDateString();
        $user = Auth::user();
        $hasRestrictions = $user && $user->hasServiceRestrictions();
        $allowedIds = $hasRestrictions ? $user->getAllowedServiceIds() : [];

        $buildAppQuery = function() use ($tenantId, $employeeId, $hasRestrictions, $allowedIds) {
            $q = Application::where('tenant_id', $tenantId);
            if ($hasRestrictions) {
                $q->whereIn('service_id', $allowedIds);
            } else {
                $q->where('assigned_employee_id', $employeeId);
            }
            return $q;
        };

        $assignedToday = $buildAppQuery()
            ->whereDate('created_at', $today)
            ->count();

        $inProcess = $buildAppQuery()
            ->whereIn('work_status', [Application::STATUS_IN_PROCESS, Application::STATUS_SUBMITTED, Application::STATUS_UNDER_PROCESS])
            ->count();

        $pending = $buildAppQuery()
            ->whereIn('work_status', [Application::STATUS_NEW, Application::STATUS_DOCUMENT_PENDING, Application::STATUS_RETURNED_CORRECTION])
            ->count();

        $dueToday = $buildAppQuery()
            ->whereDate('due_date', $today)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        $overdue = $buildAppQuery()
            ->whereDate('due_date', '<', $today)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        $completed = $buildAppQuery()
            ->whereIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        // Pending Applications Only for Dashboard
        $tasks = $buildAppQuery()
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED, Application::STATUS_APPROVED])
            ->with(['customer', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $followUps = FollowUp::where('tenant_id', $tenantId);
        if ($hasRestrictions) {
            $followUps->where(function($q) use ($allowedIds) {
                $q->whereNull('application_id')
                  ->orWhereHas('application', function($sub) use ($allowedIds) {
                      $sub->whereIn('service_id', $allowedIds);
                  });
            });
        } else {
            $followUps->where('assigned_employee_id', $employeeId);
        }
        $followUps = $followUps->where('status', FollowUp::STATUS_PENDING)
            ->with(['customer', 'application'])
            ->orderBy('follow_up_date', 'asc')
            ->limit(10)
            ->get();

        return compact(
            'assignedToday', 'inProcess', 'pending', 'dueToday',
            'overdue', 'completed', 'tasks', 'followUps'
        );
    }
}
