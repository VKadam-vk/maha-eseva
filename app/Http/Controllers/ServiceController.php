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

        if ($request->filled('main_service')) {
            $query->where('main_service_name', $request->main_service);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('main_service_name', 'like', "%{$s}%")
                  ->orWhere('sub_service_name', 'like', "%{$s}%")
                  ->orWhere('service_variant', 'like', "%{$s}%");
            });
        }

        $services = $query->orderBy('main_service_name')->orderBy('sub_service_name')->get();
        $groupedServices = $services->groupBy(function($item) {
            return trim($item->main_service_name ?: 'GENERAL SERVICES');
        });

        $mainServices = Service::distinct()->pluck('main_service_name')->filter()->values();

        return view('services.index', compact('services', 'groupedServices', 'mainServices'));
    }

    /**
     * Show create service form.
     */
    public function create(): View
    {
        $existingMainServices = Service::distinct()->pluck('main_service_name')->filter()->values();
        return view('services.create', compact('existingMainServices'));
    }

    /**
     * Store newly created main service and its multiple sub-services.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'main_service_name' => ['required', 'string', 'max:255'],
            'sub_services' => ['required', 'array', 'min:1'],
            'sub_services.*.sub_service_name' => ['required', 'string', 'max:255'],
            'sub_services.*.expected_processing_days' => ['required', 'integer', 'min:1'],
            'sub_services.*.price' => ['required', 'numeric', 'min:0'],
            'sub_services.*.govt_fee' => ['required', 'numeric', 'min:0'],
            'sub_services.*.service_charge' => ['required', 'numeric', 'min:0'],
            'sub_services.*.required_documents' => ['nullable', 'string'],
            'sub_services.*.work_portal_link' => ['nullable', 'string', 'max:500'],
            'sub_services.*.video_instruction_link' => ['nullable', 'string', 'max:500'],
        ]);

        $mainServiceName = strtoupper(trim($validated['main_service_name']));
        $tenantId = Auth::user()->tenant_id;
        $createdCount = 0;

        foreach ($validated['sub_services'] as $index => $subData) {
            $subName = trim($subData['sub_service_name']);

            // Normalize URLs
            $workLink = !empty($subData['work_portal_link']) ? trim($subData['work_portal_link']) : null;
            if ($workLink && !preg_match('#^https?://#i', $workLink)) {
                $workLink = 'https://' . $workLink;
            }

            $videoLink = !empty($subData['video_instruction_link']) ? trim($subData['video_instruction_link']) : null;
            if ($videoLink && !preg_match('#^https?://#i', $videoLink)) {
                $videoLink = 'https://' . $videoLink;
            }

            $docsList = [];
            if (!empty($subData['required_documents'])) {
                $docsList = array_values(array_filter(array_map('trim', explode("\n", $subData['required_documents']))));
            }

            // Auto-generate clean unique service code
            $codePrefix = substr(preg_replace('/[^A-Za-z0-9]/', '', $mainServiceName), 0, 4) ?: 'SRV';
            $codeSub = substr(preg_replace('/[^A-Za-z0-9]/', '', $subName), 0, 3) ?: 'SUB';
            $serviceCode = strtoupper($codePrefix . '-' . $codeSub . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT));

            $attempt = 1;
            $baseCode = $serviceCode;
            while (Service::where('tenant_id', $tenantId)->where('service_code', $serviceCode)->exists()) {
                $serviceCode = $baseCode . '-' . rand(10, 99);
                $attempt++;
                if ($attempt > 10) break;
            }

            $service = Service::create([
                'tenant_id' => $tenantId,
                'category_id' => null,
                'service_code' => $serviceCode,
                'main_service_name' => $mainServiceName,
                'sub_service_name' => $subName,
                'price' => $subData['price'],
                'govt_fee' => $subData['govt_fee'],
                'service_charge' => $subData['service_charge'],
                'expected_processing_days' => $subData['expected_processing_days'],
                'required_documents_json' => $docsList,
                'work_portal_link' => $workLink,
                'service_portal_link' => $workLink,
                'video_instruction_link' => $videoLink,
                'is_active' => true,
            ]);

            AuditService::log('SERVICE_CREATED', $service, null, $service->toArray());
            $createdCount++;
        }

        return redirect()->route('services.index')
            ->with('success', "Main service [{$mainServiceName}] with {$createdCount} sub-services saved successfully!");
    }

    /**
     * Show edit service form for a single sub-service.
     */
    public function edit(Service $service): View
    {
        $existingMainServices = Service::distinct()->pluck('main_service_name')->filter()->values();
        return view('services.edit', compact('service', 'existingMainServices'));
    }

    /**
     * Update an existing sub-service.
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'main_service_name' => ['required', 'string', 'max:255'],
            'sub_service_name' => ['required', 'string', 'max:255'],
            'expected_processing_days' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'govt_fee' => ['required', 'numeric', 'min:0'],
            'service_charge' => ['required', 'numeric', 'min:0'],
            'required_documents' => ['nullable', 'string'],
            'work_portal_link' => ['nullable', 'string', 'max:500'],
            'video_instruction_link' => ['nullable', 'string', 'max:500'],
        ]);

        $workLink = !empty($validated['work_portal_link']) ? trim($validated['work_portal_link']) : null;
        if ($workLink && !preg_match('#^https?://#i', $workLink)) {
            $workLink = 'https://' . $workLink;
        }

        $videoLink = !empty($validated['video_instruction_link']) ? trim($validated['video_instruction_link']) : null;
        if ($videoLink && !preg_match('#^https?://#i', $videoLink)) {
            $videoLink = 'https://' . $videoLink;
        }

        $docsList = [];
        if (!empty($validated['required_documents'])) {
            $docsList = array_values(array_filter(array_map('trim', explode("\n", $validated['required_documents']))));
        }

        $old = $service->toArray();

        $service->update([
            'main_service_name' => strtoupper(trim($validated['main_service_name'])),
            'sub_service_name' => trim($validated['sub_service_name']),
            'price' => $validated['price'],
            'govt_fee' => $validated['govt_fee'],
            'service_charge' => $validated['service_charge'],
            'expected_processing_days' => $validated['expected_processing_days'],
            'required_documents_json' => $docsList,
            'work_portal_link' => $workLink,
            'service_portal_link' => $workLink,
            'video_instruction_link' => $videoLink,
        ]);

        AuditService::log('SERVICE_UPDATED', $service, $old, $service->toArray());

        return redirect()->route('services.show', $service->id)->with('success', 'Sub-service updated successfully!');
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $old = $service->toArray();
        $service->delete();
        AuditService::log('SERVICE_DELETED', $service, $old, null);

        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
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
            'work_portal_link' => $service->work_portal_link ?: $service->service_portal_link,
            'video_instruction_link' => $service->video_instruction_link,
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
