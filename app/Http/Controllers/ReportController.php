<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Service;
use App\Services\ImportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ImportExportService $importExportService
    ) {}

    /**
     * Reports Center landing.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Customer Report.
     */
    public function customers(Request $request): View|StreamedResponse
    {
        $user = Auth::user();

        if ($request->boolean('export')) {
            return $this->importExportService->exportCustomers($user, $request->all());
        }

        $query = Customer::with(['branch', 'category'])
            ->withCount(['applications', 'payments']);

        if ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('occupation')) {
            $query->whereRaw('UPPER(occupation) = ?', [strtoupper(trim($request->occupation))]);
        } elseif ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('mobile', 'like', "%{$term}%")
                  ->orWhere('customer_code', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $customers = $query->latest()->paginate(20)->withQueryString();
        
        $countQuery = Customer::query();
        if ($user->tenant_id) {
            $countQuery->where('tenant_id', $user->tenant_id);
        }
        if ($user->branch_id) {
            $countQuery->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $countQuery->where('branch_id', $request->branch_id);
        }
        $totalCustomers = $countQuery->count();

        return view('reports.customers', compact('customers', 'totalCustomers'));
    }

    /**
     * Applications Report.
     */
    public function applications(Request $request): View|StreamedResponse
    {
        $user = Auth::user();

        if ($request->boolean('export')) {
            return $this->importExportService->exportApplications($user, $request->all());
        }

        $query = Application::with(['customer', 'service', 'assignedEmployee.user', 'branch']);

        if ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('work_status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('application_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('application_date', '<=', $request->to_date);
        }

        $applications = $query->latest('application_date')->paginate(20)->withQueryString();
        $services = Service::where('is_active', true)->get();

        $summaryBase = Application::query();
        if ($user->tenant_id) {
            $summaryBase->where('tenant_id', $user->tenant_id);
        }
        if ($user->branch_id) {
            $summaryBase->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $summaryBase->where('branch_id', $request->branch_id);
        }

        $summary = [
            'total' => (clone $summaryBase)->count(),
            'total_amount' => (float) (clone $summaryBase)->sum('total_amount'),
            'total_received' => (float) (clone $summaryBase)->sum('received_amount'),
            'total_remaining' => (float) (clone $summaryBase)->sum('remaining_amount'),
        ];

        return view('reports.applications', compact('applications', 'services', 'summary'));
    }

    /**
     * Collections / Revenue Report.
     */
    public function collections(Request $request): View
    {
        $user = Auth::user();
        $query = Payment::with(['customer', 'application.service', 'receiver', 'branch'])
            ->where('payment_status', 'SUCCESS');

        $breakdownQuery = Payment::where('payment_status', 'SUCCESS');
        $appsBase = Application::query();

        if ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
            $breakdownQuery->where('tenant_id', $user->tenant_id);
            $appsBase->where('tenant_id', $user->tenant_id);
        }

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
            $breakdownQuery->where('branch_id', $user->branch_id);
            $appsBase->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $query->where('branch_id', $request->branch_id);
            $breakdownQuery->where('branch_id', $request->branch_id);
            $appsBase->where('branch_id', $request->branch_id);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
            $breakdownQuery->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
            $breakdownQuery->whereDate('payment_date', '>=', $request->from_date);
            $appsBase->whereDate('application_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
            $breakdownQuery->whereDate('payment_date', '<=', $request->to_date);
            $appsBase->whereDate('application_date', '<=', $request->to_date);
        }

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();

        $modeBreakdown = (clone $breakdownQuery)
            ->select('payment_mode', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
            ->groupBy('payment_mode')
            ->get();

        $totalRevenue = (float) (clone $breakdownQuery)->sum('amount');
        $totalBilled = (float) (clone $appsBase)->sum('total_amount');
        $totalPendingReceivable = (float) (clone $appsBase)->sum('remaining_amount');

        return view('reports.collections', compact('payments', 'modeBreakdown', 'totalRevenue', 'totalBilled', 'totalPendingReceivable'));
    }

    /**
     * Employee Performance Report.
     */
    public function employees(Request $request): View
    {
        $user = Auth::user();
        $employeesQuery = Employee::with(['user', 'branch']);

        if ($user->tenant_id) {
            $employeesQuery->where('tenant_id', $user->tenant_id);
        }

        if ($user->branch_id) {
            $employeesQuery->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $employeesQuery->where('branch_id', $request->branch_id);
        }

        $employees = $employeesQuery
            ->withCount([
                'applications',
                'applications as completed_count' => function ($q) {
                    $q->whereIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED]);
                },
                'applications as pending_count' => function ($q) {
                    $q->whereNotIn('work_status', [Application::STATUS_APPROVED, Application::STATUS_READY, Application::STATUS_DELIVERED, Application::STATUS_CLOSED, Application::STATUS_REJECTED, Application::STATUS_CANCELLED]);
                }
            ])
            ->get();

        return view('reports.employees', compact('employees'));
    }
}
