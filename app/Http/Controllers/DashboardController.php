<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display role-appropriate ERP dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isBusinessOwner()) {
            // Default to owner's own branch (Main Head Office) so dashboard reflects their own center's operations
            $selectedBranchId = $user->branch_id ?? 1;
            if ($request->has('branch_id')) {
                $selectedBranchId = $request->branch_id === 'all' ? null : (int) $request->branch_id;
            }

            $data = $this->reportService->getBusinessOwnerDashboard($user->tenant_id ?? 1, $selectedBranchId);
            $branches = \App\Models\Branch::where('tenant_id', $user->tenant_id ?? 1)->where('status', 'ACTIVE')->get();
            $data['branches'] = $branches;
            $data['selectedBranchId'] = $selectedBranchId;
            return view('dashboard.business_owner', $data);
        }

        if ($user->isBranchAdmin()) {
            $data = $this->reportService->getBranchAdminDashboard($user->tenant_id, $user->branch_id);
            return view('dashboard.branch_admin', $data);
        }

        // Employee dashboard
        $employeeId = $user->employee?->id ?? 0;
        $data = $this->reportService->getEmployeeDashboard($user->tenant_id, $employeeId);
        return view('dashboard.employee', $data);
    }
}
