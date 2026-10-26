@extends('layouts.app')

@section('title', 'Edit Service - ' . $service->full_name)

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px; font-size: 22px;">
            <i class="fa-solid fa-pen-to-square" style="color: #4f46e5;"></i> 
            Edit Service / Sub-Service
        </h1>
        <p class="page-subtitle">
            Update service name, rates, expected days, required documents and portals.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('services.show', $service->id) }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> View Service
        </a>
        <a href="{{ route('services.index') }}" class="btn btn-secondary">
            All Services
        </a>
    </div>
</div>

<div class="erp-card" style="max-width: 900px; margin: 0 auto; border: 1.5px solid #cbd5e1; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color: white; margin: 0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-sliders"></i> {{ $service->main_service_name }} &mdash; {{ $service->sub_service_name }}
        </h3>
    </div>

    <div class="erp-card-body" style="padding: 28px;">
        <form action="{{ route('services.update', $service->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- 1. Main Service Name -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-crown" style="color: #4f46e5;"></i> Main Service Name
                </label>
                <input type="text" name="main_service_name" list="main_services_list" class="form-control" required 
                       value="{{ old('main_service_name', $service->main_service_name) }}"
                       style="height: 44px; font-weight: 700; text-transform: uppercase; width: 100%;">
                
                <datalist id="main_services_list">
                    @foreach($existingMainServices as $ms)
                        <option value="{{ $ms }}"></option>
                    @endforeach
                </datalist>
            </div>

            <!-- 2. Sub-Service Name -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-tag" style="color: #4f46e5;"></i> Sub-Service Name
                </label>
                <input type="text" name="sub_service_name" class="form-control" required 
                       value="{{ old('sub_service_name', $service->sub_service_name) }}"
                       style="height: 44px; font-weight: 600; width: 100%;">
            </div>

            <!-- 3. Expected Processing Days -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> Expected Processing Time (Days)
                </label>
                <input type="number" name="expected_processing_days" class="form-control" required min="1" 
                       value="{{ old('expected_processing_days', $service->expected_processing_days) }}"
                       style="height: 44px; font-weight: 700; width: 100%;">
            </div>

            <!-- 4. Fee Structure (Vertical Layout) -->
            <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #166534; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-indian-rupee-sign"></i> Fee Structure & Rates
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #334155;">Government Fee (₹)</label>
                        <input type="number" step="0.01" id="govt_fee_input" name="govt_fee" class="form-control" required min="0" 
                               value="{{ old('govt_fee', $service->govt_fee) }}"
                               style="height: 42px; font-family: var(--font-mono); font-weight: 700; width: 100%;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #334155;">Service Charge / Kendra Margin (₹)</label>
                        <input type="number" step="0.01" id="service_charge_input" name="service_charge" class="form-control" required min="0" 
                               value="{{ old('service_charge', $service->service_charge) }}"
                               style="height: 42px; font-family: var(--font-mono); font-weight: 700; width: 100%;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 800; color: #166534;">Total Customer Price (₹)</label>
                        <input type="number" step="0.01" id="price_input" name="price" class="form-control" required min="0" 
                               value="{{ old('price', $service->price) }}"
                               style="height: 44px; font-family: var(--font-mono); font-weight: 800; font-size: 16px; color: #166534; background: #f0fdf4; border: 1.5px solid #86efac; width: 100%;">
                    </div>
                </div>
            </div>

            <!-- 5. Required Documents -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-circle-check" style="color: #4f46e5;"></i> 
                    Required Documents (One per line)
                </label>
                @php
                    $docsText = !empty($service->required_documents_json) ? implode("\n", $service->required_documents_json) : '';
                @endphp
                <textarea name="required_documents" class="form-control" rows="4" 
                          placeholder="Aadhaar Card&#10;Passport Photo&#10;Signature" style="font-size: 13px; border-radius: 6px; width: 100%;">{{ old('required_documents', $docsText) }}</textarea>
            </div>

            <!-- 6. Operator Links (Vertical Layout) -->
            <div style="background-color: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1e40af; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-link"></i> Essential Operator Links (Work Portal & Tutorial Video)
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-globe" style="color: #2563eb;"></i> Operator Work Portal Link
                        </label>
                        <input type="text" name="work_portal_link" class="form-control" 
                               value="{{ old('work_portal_link', $service->work_portal_link ?: $service->service_portal_link) }}"
                               placeholder="https://aaplesarkar.mahaonline.gov.in" 
                               style="height: 42px; font-size: 13px; background: white; width: 100%;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-youtube" style="color: #dc2626;"></i> Watch Tutorial Video Link
                        </label>
                        <input type="text" name="video_instruction_link" class="form-control" 
                               value="{{ old('video_instruction_link', $service->video_instruction_link) }}"
                               placeholder="https://www.youtube.com/watch?v=..." 
                               style="height: 42px; font-size: 13px; background: white; width: 100%;">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; align-items: center; padding-top: 14px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('services.show', $service->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 26px; font-weight: 700; background: #4f46e5; border-color: #3730a3;">
                    <i class="fa-solid fa-check"></i> Update Service
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('govt_fee_input').addEventListener('input', calcTotal);
document.getElementById('service_charge_input').addEventListener('input', calcTotal);

function calcTotal() {
    const govt = parseFloat(document.getElementById('govt_fee_input').value) || 0;
    const srv = parseFloat(document.getElementById('service_charge_input').value) || 0;
    document.getElementById('price_input').value = (govt + srv).toFixed(2);
}
</script>
@endpush
@endsection
