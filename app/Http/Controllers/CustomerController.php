<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\Service;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of customers with filters and pagination.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Customer::with(['branch', 'category'])
            ->withCount(['applications', 'documents', 'payments']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $categories = CustomerCategory::all();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        return view('customers.index', compact('customers', 'categories', 'branches'));
    }

    /**
     * Show create customer form.
     */
    public function create(): View
    {
        $user = Auth::user();
        $categories = CustomerCategory::all();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        return view('customers.create', compact('categories', 'branches'));
    }

    /**
     * Store new customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'in:MALE,FEMALE,OTHER'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'birth_date' => ['nullable', 'date'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],
            'category_id' => ['nullable', 'exists:customer_categories,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = $this->customerService->createCustomer($validated, Auth::user());

        return redirect()->route('customers.show', $customer->id)
            ->with('success', "Customer {$customer->name} ({$customer->customer_code}) saved successfully.");
    }

    /**
     * Check duplicate customer by mobile number (JSON API for quick add / AJAX).
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string']);
        $tenantId = Auth::user()->tenant_id;

        $existing = $this->customerService->findByMobile($tenantId, $request->mobile);

        if ($existing) {
            return response()->json([
                'exists' => true,
                'customer' => [
                    'id' => $existing->id,
                    'customer_code' => $existing->customer_code,
                    'name' => $existing->name,
                    'mobile' => $existing->mobile,
                    'email' => $existing->email,
                    'address' => $existing->address,
                    'gender' => $existing->gender,
                    'url' => route('customers.show', $existing->id),
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Display comprehensive customer profile with tabs.
     */
    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load([
            'branch',
            'category',
            'creator',
            'applications.service',
            'applications.assignedEmployee.user',
            'documents.verifier',
            'documents.uploader',
            'payments.receiver',
            'invoices.items',
            'followUps.assignedEmployee.user',
        ]);

        // Fetch activity timeline
        $activityTimeline = AuditLog::where('tenant_id', $customer->tenant_id)
            ->where(function ($q) use ($customer) {
                $q->where(function ($sub) use ($customer) {
                    $sub->where('entity_type', Customer::class)->where('entity_id', $customer->id);
                })->orWhere(function ($sub) use ($customer) {
                    $appIds = $customer->applications->pluck('id')->toArray();
                    $sub->where('entity_type', \App\Models\Application::class)->whereIn('entity_id', $appIds);
                });
            })
            ->latest()
            ->limit(25)
            ->get();

        $activeServices = Service::where('is_active', true)->with('category')->get();
        $employees = Auth::user()->tenant?->employees()->where('status', 'ACTIVE')->with('user')->get() ?? collect();

        return view('customers.show', compact('customer', 'activityTimeline', 'activeServices', 'employees'));
    }

    /**
     * Show edit customer form.
     */
    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        $user = Auth::user();
        $categories = CustomerCategory::all();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        return view('customers.edit', compact('customer', 'categories', 'branches'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'in:MALE,FEMALE,OTHER'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'birth_date' => ['nullable', 'date'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],
            'category_id' => ['nullable', 'exists:customer_categories,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->customerService->updateCustomer($customer, $validated, Auth::user());

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Customer profile updated successfully.');
    }

    /**
     * Delete customer.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
