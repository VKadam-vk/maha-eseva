<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Services\CustomerService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $query = Customer::with(['branch', 'category', 'creator'])
            ->withCount(['applications', 'documents', 'payments']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('alternate_mobile', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Branch-level isolation: By default, users see only their own branch's customers
        $selectedBranchId = $user->branch_id;
        if ($request->has('branch_id') && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $selectedBranchId = $request->branch_id === 'all' ? null : (int) $request->branch_id;
        }

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        if ($request->filled('occupation')) {
            $query->whereRaw('UPPER(occupation) = ?', [strtoupper(trim($request->occupation))]);
        } elseif ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $isPrivilegedUser = $user->isSuperAdmin() 
            || $user->isBusinessOwner() 
            || $user->isBranchAdmin() 
            || $user->isAdmin()
            || str_contains(strtoupper($user->roles->pluck('name')->implode(' ')), 'OWNER')
            || str_contains(strtoupper($user->roles->pluck('name')->implode(' ')), 'ADMIN');

        // Restrict employee visibility: an employee sees customers they created, or customers with applications in their allowed services
        if (!$isPrivilegedUser) {
            $allowedServiceIds = $user->getAllowedServiceIds();
            $empId = $user->employee?->id;
            $query->where(function ($q) use ($user, $allowedServiceIds, $empId) {
                $q->where('created_by', $user->id);
                if (!empty($allowedServiceIds)) {
                    $q->orWhereHas('applications', function ($sub) use ($allowedServiceIds, $empId) {
                        $sub->whereIn('service_id', $allowedServiceIds);
                        if ($empId) {
                            $sub->orWhere('assigned_employee_id', $empId);
                        }
                    });
                }
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $categories = CustomerCategory::all();

        $tenantId = $user->tenant_id ?? 1;
        $dbOccupations = Customer::where('tenant_id', $tenantId)
            ->whereNotNull('occupation')
            ->where('occupation', '!=', '')
            ->distinct()
            ->pluck('occupation')
            ->toArray();

        $defaultOccupations = [
            'FARMER', 'BUSINESS', 'STUDENT', 'SERVICE', 'HOUSEWIFE',
            'PRIVATE JOB', 'GOVT EMPLOYEE', 'LABOUR', 'SELF EMPLOYED',
            'RETIRED', 'DRIVER', 'TAILOR', 'TEACHER', 'ADVOCATE', 'DOCTOR', 'SHOP', 'OTHER'
        ];

        $occupations = collect(array_unique(array_merge($defaultOccupations, array_map('trim', array_map('strtoupper', $dbOccupations)))))
            ->filter()
            ->sort()
            ->values();

        $branches = Branch::where('tenant_id', $tenantId)->where('status', 'ACTIVE')->get();

        $cities = Customer::where('tenant_id', $tenantId)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->filter()
            ->sort()
            ->values();

        return view('customers.index', compact('customers', 'categories', 'branches', 'occupations', 'cities', 'selectedBranchId'));
    }

    /**
     * Show create customer form.
     */
    public function create(): View
    {
        $user = Auth::user();
        $categories = CustomerCategory::all();

        $branchesQuery = $user->tenant?->branches()->where('status', 'ACTIVE');
        if ($user->branch_id) {
            $branchesQuery->where('id', $user->branch_id);
        }
        $branches = $branchesQuery->get() ?? collect();

        return view('customers.create', compact('categories', 'branches'));
    }

    /**
     * Store new customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Handle dynamic category creation
        if ($request->input('category_id') === '__NEW_CATEGORY__' || $request->filled('new_category_name')) {
            $catName = trim($request->input('new_category_name', ''));
            if (!empty($catName)) {
                $category = CustomerCategory::firstOrCreate(
                    ['tenant_id' => $user->tenant_id, 'name' => $catName],
                    ['code' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catName), 0, 4))]
                );
                $request->merge(['category_id' => $category->id]);
            } else {
                $request->merge(['category_id' => null]);
            }
        } elseif ($request->input('category_id') === '') {
            $request->merge(['category_id' => null]);
        }

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
            'occupation' => ['nullable', 'string', 'max:100'],
            'star_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'category_id' => ['nullable', 'exists:customer_categories,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($user->branch_id) {
            $validated['branch_id'] = $user->branch_id;
        }

        $customer = $this->customerService->createCustomer($validated, $user);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', "Customer {$customer->name} ({$customer->customer_code}) saved successfully.");
    }

    /**
     * Check duplicate customer by mobile number (JSON API for quick add / AJAX).
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string']);
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $existing = $this->customerService->findByMobile($tenantId, $request->mobile, $user->branch_id);

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
        $user = Auth::user();
        $this->authorize('view', $customer);

        // Restrict employee access to customer profile if no allowed services match
        if ($user->hasServiceRestrictions()) {
            $empId = $user->employee?->id;
            $hasAccess = $customer->applications()->where(function ($q) use ($user, $empId) {
                $q->whereIn('service_id', $user->getAllowedServiceIds());
                if ($empId) {
                    $q->orWhere('assigned_employee_id', $empId);
                }
            })->exists() || (int) ($customer->created_by ?? 0) === (int) $user->id;
            if (!$hasAccess && $customer->applications()->exists()) {
                abort(403, 'तुम्हाला या ग्राहकाची माहिती पाहण्याची परवानगी नाही.');
            }
        }

        $customer->load([
            'branch',
            'category',
            'creator',
            'familyMembers',
            'applications' => function ($q) use ($user) {
                if ($user->hasServiceRestrictions()) {
                    $empId = $user->employee?->id;
                    $q->where(function ($sub) use ($user, $empId) {
                        $sub->whereIn('service_id', $user->getAllowedServiceIds());
                        if ($empId) {
                            $sub->orWhere('assigned_employee_id', $empId);
                        }
                    });
                }
            },
            'applications.service',
            'applications.assignedEmployee.user',
            'applications.familyMember',
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

        $activeServices = $user->allowedServices()->with('category')->get();

        $employeesQuery = $user->tenant?->employees()->where('status', 'ACTIVE')->with(['user', 'services:id,main_service_name,sub_service_name']);
        if ($user->branch_id) {
            $employeesQuery->where('branch_id', $user->branch_id);
        }
        $employees = $employeesQuery->get() ?? collect();

        $formattedFamilyMembers = $customer->familyMembers->map(function ($fm) {
            return [
                'id' => $fm->id,
                'name' => $fm->name,
                'relation' => $fm->relation,
                'relation_label' => $fm->relation_label,
                'gender' => $fm->gender,
                'birth_date' => $fm->birth_date ? $fm->birth_date->format('Y-m-d') : '',
                'age' => $fm->age,
                'mobile' => $fm->mobile ?: '',
                'notes' => $fm->notes ?: '',
            ];
        })->values();

        $existingPaymentModes = \App\Models\Application::getAvailablePaymentModes($customer->tenant_id);

        return view('customers.show', compact('customer', 'activityTimeline', 'activeServices', 'employees', 'formattedFamilyMembers', 'existingPaymentModes'));
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
        $user = Auth::user();

        // Handle dynamic category creation
        if ($request->input('category_id') === '__NEW_CATEGORY__' || $request->filled('new_category_name')) {
            $catName = trim($request->input('new_category_name', ''));
            if (!empty($catName)) {
                $category = CustomerCategory::firstOrCreate(
                    ['tenant_id' => $user->tenant_id, 'name' => $catName],
                    ['code' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catName), 0, 4))]
                );
                $request->merge(['category_id' => $category->id]);
            } else {
                $request->merge(['category_id' => null]);
            }
        } elseif ($request->input('category_id') === '') {
            $request->merge(['category_id' => null]);
        }

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
            'occupation' => ['nullable', 'string', 'max:100'],
            'star_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
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

        DB::transaction(function () use ($customer) {
            foreach ($customer->applications as $app) {
                $app->payments()->delete();
                $app->documents()->delete();
                $app->customValues()->delete();
                $app->statusHistories()->delete();
                $app->followUps()->delete();
                $app->fieldHistories()->delete();
                $app->invoices()->delete();
                $app->delete();
            }
            $customer->familyMembers()->delete();
            $customer->followUps()->delete();
            $customer->delete();
        });

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    /**
     * Preview matching customers for broadcast with dynamic filters (Software Owner only).
     */
    public function previewBroadcast(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSoftwareOwner()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized: Only Software Owner can preview broadcast.'], 403);
        }

        $tenant = $user->tenant ?? \App\Models\Tenant::first();
        if (!$tenant) {
            return response()->json(['success' => false, 'error' => 'Tenant not found.'], 404);
        }

        $query = Customer::where('tenant_id', $tenant->id)
            ->where('is_blacklisted', false)
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '');

        // 1. Occupation filter
        if ($request->filled('occupation')) {
            $query->whereRaw('UPPER(occupation) = ?', [strtoupper(trim($request->occupation))]);
        }

        // 2. Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 3. Gender filter (Strict Case-Insensitive Matching)
        if ($request->filled('gender') && in_array(strtoupper(trim($request->gender)), ['MALE', 'FEMALE', 'OTHER'])) {
            $query->whereRaw('UPPER(gender) = ?', [strtoupper(trim($request->gender))]);
        }

        // 4. Star Rating filter
        if ($request->filled('min_star') && is_numeric($request->min_star) && (int) $request->min_star > 0) {
            $query->where('star_rating', '>=', (int) $request->min_star);
        }

        // 5. City / Village filter
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . trim($request->city) . '%');
        }

        // 6. Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // 7. Search keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $totalCount = (clone $query)->count();
        $customers = $query->with('category:id,name')
            ->select('id', 'name', 'mobile', 'city', 'occupation', 'star_rating', 'gender', 'category_id')
            ->orderBy('name')
            ->limit(250)
            ->get();

        $settings = $tenant->settings_json ?? [];

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'customers' => $customers->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'mobile' => $c->mobile,
                    'city' => $c->city ?: '—',
                    'occupation' => $c->occupation ?: '—',
                    'star_rating' => $c->star_rating ?? 5,
                    'gender' => $c->gender ?: '—',
                    'category' => $c->category?->name ?: 'Regular',
                ];
            }),
            'estimated_cost' => round($totalCount * 0.12, 2),
            'defaults' => [
                'service' => $settings['whatsapp_broadcast_default_service'] ?? 'शासकीय सेवा व योजना माहिती',
                'status' => $settings['whatsapp_broadcast_default_status'] ?? 'नवीन नोंदणी सुरू आहे',
                'app_no' => $settings['whatsapp_broadcast_default_app_no'] ?? 'MAHA-ALERT',
                'custom_template' => $settings['whatsapp_broadcast_custom_template'] ?? '',
                'template_name' => ($settings['whatsapp_default_template_lang'] ?? 'mr') === 'en' ? 'application_update' : 'application_status',
            ],
        ]);
    }

    /**
     * Broadcast WhatsApp message to customers (Software Owner only).
     */
    public function broadcastWhatsApp(Request $request, WhatsAppService $whatsAppService): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSoftwareOwner()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized: Only Software Owner can broadcast WhatsApp messages.'], 403);
        }

        $request->validate([
            'mode' => ['required', 'in:template,custom'],
            'message' => ['nullable', 'string', 'max:2000'],
            'template_name' => ['nullable', 'string'],
            'param_service' => ['nullable', 'string', 'max:100'],
            'param_status' => ['nullable', 'string', 'max:50'],
            'param_app_no' => ['nullable', 'string', 'max:50'],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['integer'],
        ]);

        $tenant = $user->tenant ?? \App\Models\Tenant::first();
        $settings = $tenant?->settings_json ?? [];
        if (empty($settings['whatsapp_enabled']) || !WhatsAppService::isConfigured($tenant)) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp Cloud API is not configured or disabled in Settings.'
            ], 422);
        }

        // Strictly target ONLY valid CRM customers belonging to this tenant, not blacklisted
        $customers = Customer::where('tenant_id', $tenant->id)
            ->where('is_blacklisted', false)
            ->whereIn('id', $request->customer_ids)
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->get(['id', 'name', 'mobile']);

        if ($customers->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No valid customers found in CRM for the selected IDs.'], 422);
        }

        $mode = $request->input('mode', 'template');
        $rawMessage = $request->input('message', '');
        $templateName = $request->input('template_name', 'application_status');
        $paramService = trim($request->input('param_service', 'शासकीय सेवा व योजना')) ?: 'शासकीय सेवा व योजना';
        $paramStatus = trim($request->input('param_status', 'AVAILABLE')) ?: 'AVAILABLE';
        $paramAppNo = trim($request->input('param_app_no', 'MAHA-ALERT')) ?: 'MAHA-ALERT';

        $centerName = $tenant->name ?? 'Maha E-Seva Kendra';
        $centerPhone = $tenant->contact_mobile ?? '';

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($customers as $customer) {
            if ($mode === 'template') {
                if ($templateName === 'application_update') {
                    $components = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => mb_substr($customer->name, 0, 60)],
                                ['type' => 'text', 'text' => mb_substr($paramService, 0, 60)],
                                ['type' => 'text', 'text' => mb_substr($paramStatus, 0, 40)],
                                ['type' => 'text', 'text' => mb_substr($paramAppNo, 0, 40)],
                            ],
                        ],
                    ];
                    $res = $whatsAppService->sendTemplate($customer->mobile, 'application_update', 'en', $components, $tenant);
                    if (!$res['success'] && str_contains(strtolower($res['error'] ?? ''), 'language')) {
                        $res = $whatsAppService->sendTemplate($customer->mobile, 'application_update', 'en_US', $components, $tenant);
                    }
                } else {
                    // Default to approved Marathi template: application_status (mr)
                    $components = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => mb_substr($customer->name, 0, 60)],
                                ['type' => 'text', 'text' => mb_substr($paramService, 0, 60)],
                                ['type' => 'text', 'text' => mb_substr($paramStatus, 0, 40)],
                                ['type' => 'text', 'text' => mb_substr($paramAppNo, 0, 40)],
                            ],
                        ],
                    ];
                    $res = $whatsAppService->sendTemplate($customer->mobile, 'application_status', 'mr', $components, $tenant);
                }
                $loggedMessage = "Template: {$templateName} | Service: {$paramService} | Status: {$paramStatus}";
            } else {
                $personalized = str_replace(
                    ['{name}', '{customer_name}', '{center_name}', '{center_mobile}'],
                    [$customer->name, $customer->name, $centerName, $centerPhone],
                    $rawMessage
                );
                $res = $whatsAppService->sendMessage($customer->mobile, $personalized, $tenant);
                $loggedMessage = $personalized;
            }

            if ($res['success']) {
                $sentCount++;
                NotificationLog::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'type' => 'BROADCAST',
                    'channel' => 'WHATSAPP',
                    'recipient' => $customer->mobile,
                    'title' => 'WhatsApp Broadcast',
                    'message' => $loggedMessage,
                    'status' => 'SENT',
                    'provider_response' => json_encode($res),
                    'sent_at' => now(),
                ]);
            } else {
                $failedCount++;
                $errors[] = "{$customer->name} ({$customer->mobile}): " . ($res['error'] ?? 'Delivery failed');
            }

            // Safe throttle (100ms) between outbound requests to respect Meta API rate limits
            usleep(100000);
        }

        // Log broadcast activity in AuditLog
        try {
            AuditLog::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'action' => 'WHATSAPP_BROADCAST',
                'entity_type' => Customer::class,
                'entity_id' => null,
                'new_values' => [
                    'mode' => $mode,
                    'template' => $templateName,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'total_targeted' => $customers->count(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::warning('[Broadcast AuditLog]: ' . $e->getMessage());
        }

        return response()->json([
            'success' => $sentCount > 0,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'total' => $customers->count(),
            'message' => $sentCount > 0
                ? "ब्रॉडकास्ट पूर्ण! एकूण {$sentCount} CRM ग्राहकांना WhatsApp मेसेज पाठवण्यात आले" . ($failedCount > 0 ? " ({$failedCount} अयशस्वी)." : ".")
                : "ब्रॉडकास्ट अयशस्वी: ० मेसेज पाठवले, {$failedCount} अयशस्वी.",
            'errors' => array_slice($errors, 0, 5),
            'error' => !empty($errors) ? implode('; ', array_slice($errors, 0, 2)) : null,
        ]);
    }
}
