<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCustomField;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display listing of all services.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Service::with(['category', 'customFields'])->withCount('applications');

        // Restrict services if logged in employee has specific assigned services
        if ($user->isEmployee() && $user->employee && $user->employee->services()->exists()) {
            $allowedServiceIds = $user->employee->services()->pluck('services.id');
            $query->whereIn('id', $allowedServiceIds);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('main_service_name', 'like', "%{$s}%")
                  ->orWhere('sub_service_name', 'like', "%{$s}%")
                  ->orWhere('service_code', 'like', "%{$s}%");
            });
        }

        $services = $query->paginate(20)->withQueryString();
        $categories = ServiceCategory::all();

        return view('services.index', compact('services', 'categories'));
    }

    /**
     * Show create service form.
     */
    public function create(): View
    {
        $categories = ServiceCategory::all();
        return view('services.create', compact('categories'));
    }

    /**
     * Store newly created service.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:service_categories,id'],
            'service_code' => ['required', 'string', 'max:30'],
            'main_service_name' => ['required', 'string', 'max:255'],
            'sub_service_name' => ['required', 'string', 'max:255'],
            'service_variant' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'govt_fee' => ['required', 'numeric', 'min:0'],
            'service_charge' => ['required', 'numeric', 'min:0'],
            'expected_processing_days' => ['required', 'integer', 'min:1'],
            'required_documents' => ['nullable', 'string'], // newline separated
            'service_portal_link' => ['nullable', 'url'],
            'work_portal_link' => ['nullable', 'url'],
            'video_instruction_link' => ['nullable', 'url'],
            'portal_username' => ['nullable', 'string'],
            'portal_password' => ['nullable', 'string'],
        ]);

        $docsList = [];
        if (!empty($validated['required_documents'])) {
            $docsList = array_values(array_filter(array_map('trim', explode("\n", $validated['required_documents']))));
        }

        $service = Service::create([
            'tenant_id' => Auth::user()->tenant_id,
            'category_id' => $validated['category_id'],
            'service_code' => strtoupper($validated['service_code']),
            'main_service_name' => $validated['main_service_name'],
            'sub_service_name' => $validated['sub_service_name'],
            'service_variant' => $validated['service_variant'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'govt_fee' => $validated['govt_fee'],
            'service_charge' => $validated['service_charge'],
            'expected_processing_days' => $validated['expected_processing_days'],
            'required_documents_json' => $docsList,
            'service_portal_link' => $validated['service_portal_link'] ?? null,
            'work_portal_link' => $validated['work_portal_link'] ?? null,
            'video_instruction_link' => $validated['video_instruction_link'] ?? null,
            'portal_username_encrypted' => $validated['portal_username'] ?? null,
            'portal_password_encrypted' => $validated['portal_password'] ?? null,
            'is_active' => true,
        ]);

        AuditService::log('SERVICE_CREATED', $service, null, $service->toArray());

        return redirect()->route('services.show', $service->id)->with('success', 'Service created successfully.');
    }

    /**
     * Show service details & custom fields manager.
     */
    public function show(Service $service): View
    {
        $service->load(['category', 'customFields']);
        return view('services.show', compact('service'));
    }

    /**
     * Store dynamic custom field for a service.
     */
    public function storeCustomField(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:255'],
            'field_key' => ['required', 'string', 'max:50', 'alpha_dash'],
            'field_type' => ['required', 'in:text,textarea,number,date,datetime,email,mobile,select,multiselect,radio,checkbox,file,password,url'],
            'label' => ['required', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string'],
            'options' => ['nullable', 'string'], // Comma-separated options for select/radio
            'is_required' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $options = null;
        if (!empty($validated['options'])) {
            $options = array_values(array_filter(array_map('trim', explode(',', $validated['options']))));
        }

        ServiceCustomField::create([
            'tenant_id' => Auth::user()->tenant_id,
            'service_id' => $service->id,
            'field_name' => $validated['field_name'],
            'field_key' => strtolower($validated['field_key']),
            'field_type' => $validated['field_type'],
            'label' => $validated['label'],
            'placeholder' => $validated['placeholder'] ?? null,
            'options_json' => $options,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Custom field added to service form.');
    }

    /**
     * Delete custom field.
     */
    public function deleteCustomField(ServiceCustomField $field): RedirectResponse
    {
        $field->delete();
        return back()->with('success', 'Custom field removed.');
    }

    /**
     * Toggle service active status.
     */
    public function toggleStatus(Service $service): RedirectResponse
    {
        $service->update(['is_active' => !$service->is_active]);
        return back()->with('success', 'Service status updated.');
    }

    /**
     * Schema API returning service details, documents, and dynamic custom fields for dynamic form rendering.
     */
    public function schema(Service $service): JsonResponse
    {
        $service->load('customFields');

        return response()->json([
            'id' => $service->id,
            'service_code' => $service->service_code,
            'full_name' => $service->full_name,
            'price' => $service->price,
            'govt_fee' => $service->govt_fee,
            'service_charge' => $service->service_charge,
            'expected_processing_days' => $service->expected_processing_days,
            'required_documents' => $service->required_documents_json ?? [],
            'custom_fields' => $service->customFields->map(function ($f) {
                return [
                    'id' => $f->id,
                    'key' => $f->field_key,
                    'label' => $f->label,
                    'type' => $f->field_type,
                    'placeholder' => $f->placeholder,
                    'is_required' => $f->is_required,
                    'options' => $f->options_json,
                    'default_value' => $f->default_value,
                ];
            }),
        ]);
    }
}
