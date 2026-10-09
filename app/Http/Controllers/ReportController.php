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

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();
        $totalCustomers = Customer::count();

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

        $summary = [
            'total' => Application::count(),
            'total_amount' => (float) Application::sum('total_amount'),
            'total_received' => (float) Application::sum('received_amount'),
            'total_remaining' => (float) Application::sum('remaining_amount'),
        ];

        return view('reports.applications', compact('applications', 'services', 'summary'));
    }

    /**
     * Collections / Revenue Report.
     */
    public function collections(Request $request): View
    {
        $query = Payment::with(['customer', 'application.service', 'receiver', 'branch'])
            ->where('payment_status', 'SUCCESS');

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();

        $modeBreakdown = Payment::where('payment_status', 'SUCCESS')
            ->select('payment_mode', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
            ->groupBy('payment_mode')
            ->get();

        $totalRevenue = (float) Payment::where('payment_status', 'SUCCESS')->sum('amount');

        return view('reports.collections', compact('payments', 'modeBreakdown', 'totalRevenue'));
    }

    /**
     * Employee Performance Report.
     */
    public function employees(Request $request): View
    {
        $employees = Employee::with(['user', 'branch'])
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
