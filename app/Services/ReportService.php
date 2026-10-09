<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Aggregated metrics for Business Owner Dashboard.
     */
    public function getBusinessOwnerDashboard(int $tenantId): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $totalBranches = Branch::where('tenant_id', $tenantId)->count();
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();
        $totalApplications = Application::where('tenant_id', $tenantId)->count();
        $totalRevenue = (float) Payment::where('tenant_id', $tenantId)->where('payment_status', 'SUCCESS')->sum('amount');
        
        $todayCustomers = Customer::where('tenant_id', $tenantId)->whereDate('created_at', $today)->count();
        $todayApplications = Application::where('tenant_id', $tenantId)->whereDate('created_at', $today)->count();
        $todayRevenue = (float) Payment::where('tenant_id', $tenantId)->where('payment_status', 'SUCCESS')->whereDate('payment_date', $today)->sum('amount');
        $monthRevenue = (float) Payment::where('tenant_id', $tenantId)->where('payment_status', 'SUCCESS')->where('payment_date', 'like', "{$thisMonth}%")->sum('amount');

        $pendingApplications = Application::where('tenant_id', $tenantId)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED, Application::STATUS_REJECTED, Application::STATUS_CANCELLED])
            ->count();

        $completedApplications = Application::where('tenant_id', $tenantId)
            ->whereIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        // Status pipeline breakdown
        $statusCounts = Application::where('tenant_id', $tenantId)
            ->select('work_status', DB::raw('count(*) as count'))
            ->groupBy('work_status')
            ->pluck('count', 'work_status')
            ->toArray();

        // Top 5 Services by volume
        $topServices = Service::where('services.tenant_id', $tenantId)
            ->join('applications', 'applications.service_id', '=', 'services.id')
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
            ->with(['customer', 'service', 'assignedEmployee.user'])
            ->latest()
            ->limit(5)
            ->get();

        return compact(
            'totalBranches', 'totalCustomers', 'totalApplications', 'totalRevenue',
            'todayCustomers', 'todayApplications', 'todayRevenue', 'monthRevenue',
            'pendingApplications', 'completedApplications', 'statusCounts',
            'topServices', 'branchPerformance', 'recentApplications'
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

        return compact(
            'todayCustomers', 'todayApplications', 'todayCollection',
            'totalCustomers', 'totalApplications', 'pendingWork',
            'completedWork', 'dueFollowUps', 'recentApplications'
        );
    }

    /**
     * Aggregated metrics for Employee Dashboard.
     */
    public function getEmployeeDashboard(int $tenantId, int $employeeId): array
    {
        $today = now()->toDateString();

        $assignedToday = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereDate('created_at', $today)
            ->count();

        $inProcess = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereIn('work_status', [Application::STATUS_IN_PROCESS, Application::STATUS_SUBMITTED, Application::STATUS_UNDER_PROCESS])
            ->count();

        $pending = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereIn('work_status', [Application::STATUS_NEW, Application::STATUS_DOCUMENT_PENDING, Application::STATUS_RETURNED_CORRECTION])
            ->count();

        $dueToday = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereDate('due_date', $today)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        $overdue = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereDate('due_date', '<', $today)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        $completed = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->count();

        $tasks = Application::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->whereNotIn('work_status', [Application::STATUS_DELIVERED, Application::STATUS_CLOSED])
            ->with(['customer', 'service'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $followUps = FollowUp::where('tenant_id', $tenantId)
            ->where('assigned_employee_id', $employeeId)
            ->where('status', FollowUp::STATUS_PENDING)
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
