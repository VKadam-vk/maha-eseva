@extends('layouts.app')

@section('title', 'Add Main Service & Sub-Services')

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px; font-size: 22px;">
            <i class="fa-solid fa-layer-group" style="color: #4f46e5;"></i> 
            Add New Service Master
        </h1>
        <p class="page-subtitle">
            Register multiple sub-services under a main service with independent rates, processing days, documents, and portal links.
        </p>
    </div>
    <a href="{{ route('services.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> All Services
    </a>
</div>

<form action="{{ route('services.store') }}" method="POST" id="servicesForm">
    @csrf

    <!-- Main Service Card -->
    <div class="erp-card" style="margin-bottom: 24px; border: 2px solid #4f46e5; border-radius: 12px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.08); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); padding: 14px 22px; color: white; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-crown" style="color: #fbbf24;"></i>
                MAIN SERVICE
            </div>
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 11px; padding: 4px 10px;">
                Parent Service
            </span>
        </div>
        <div class="erp-card-body" style="padding: 22px 24px; background: #ffffff;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-size: 14px; font-weight: 700; color: #1e293b;">
                    Main Service Name
                </label>
                <input type="text" name="main_service_name" list="main_services_list" class="form-control" required 
                       placeholder="e.g. PAN CARD, INCOME CERTIFICATE, RENT AGREEMENT, DOMICILE CERTIFICATE..." 
                       value="{{ old('main_service_name', request('main_service')) }}"
                       style="height: 48px; font-size: 15px; font-weight: 700; text-transform: uppercase; border: 2px solid #cbd5e1; border-radius: 8px;">
                
                <datalist id="main_services_list">
                    @foreach($existingMainServices as $ms)
                        <option value="{{ $ms }}"></option>
                    @endforeach
                </datalist>
                <small style="color: #64748b; font-size: 12px; margin-top: 6px; display: block;">
                    <i class="fa-solid fa-circle-info" style="color: #4f46e5;"></i> Select an existing main service or enter a new name. All sub-services added below will be registered under this main service.
                </small>
            </div>
        </div>
    </div>

    <!-- Section Header for Sub-Services -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 17px; font-weight: 800; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-sitemap" style="color: #2563eb;"></i>
                Sub-Services List
            </h3>
            <p style="font-size: 12px; color: #64748b; margin: 3px 0 0 0;">
                Enter name, expected days, fee structure, required documents, and links for each sub-service.
            </p>
        </div>
        <button type="button" class="btn btn-primary" id="addSubServiceBtn" style="background: #2563eb; border-color: #1d4ed8; font-weight: 700; box-shadow: 0 2px 6px rgba(37,99,235,0.25);">
            <i class="fa-solid fa-plus"></i> + Add Another Sub-Service
        </button>
    </div>

    <!-- Sub Services Container -->
    <div id="subServicesContainer" style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Default Initial Sub-Service Card -->
        <div class="sub-service-card erp-card" style="border: 1.5px solid #cbd5e1; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
            <div class="sub-service-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="sub-service-badge" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 12px; padding: 4px 12px; border-radius: 6px; border: 1px solid #c7d2fe;">
                        <i class="fa-solid fa-folder-tree"></i> Sub-Service #1
                    </span>
                    <span style="font-size: 13px; font-weight: 600; color: #475569;" class="sub-service-title-preview">New Sub-Service Details</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-sub-service-btn" style="display: none; padding: 4px 10px; font-size: 12px; border-radius: 6px;" title="Remove this sub-service">
                    <i class="fa-solid fa-trash-can"></i> Remove
                </button>
            </div>

            <div class="erp-card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                <!-- 1. Sub-Service Name -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-tag" style="color: #4f46e5;"></i> Sub-Service Name
                    </label>
                    <input type="text" name="sub_services[0][sub_service_name]" class="form-control sub-service-name-input" required 
                           placeholder="e.g. New PAN Application Form 49A / Correction / Duplicate" 
                           style="height: 44px; font-weight: 600; width: 100%;">
                    <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                        Specific application or form type under this main service.
                    </small>
                </div>

                <!-- 2. Expected Processing Days -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> Expected Processing Time (Days)
                    </label>
                    <input type="number" name="sub_services[0][expected_processing_days]" class="form-control" required min="1" value="7"
                           placeholder="7" style="height: 44px; font-weight: 700; width: 100%;">
                    <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                        Estimated processing time required for this sub-service (in days).
                    </small>
                </div>

                <!-- 3. Fee Structure & Margin (Vertical Layout) -->
                <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 18px 20px;">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #166534; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-indian-rupee-sign"></i> Fee Structure & Margin
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #334155;">Government Fee (₹)</label>
                            <input type="number" step="0.01" name="sub_services[0][govt_fee]" class="form-control govt-fee-input" required min="0" value="0.00"
                                   placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700; width: 100%;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #334155;">Service Charge / Kendra Margin (₹)</label>
                            <input type="number" step="0.01" name="sub_services[0][service_charge]" class="form-control service-charge-input" required min="0" value="0.00"
                                   placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700; width: 100%;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-size: 12px; font-weight: 800; color: #166534;">Total Customer Price (₹)</label>
                            <input type="number" step="0.01" name="sub_services[0][price]" class="form-control total-price-input" required min="0" value="0.00"
                                   placeholder="0.00" style="height: 44px; font-family: var(--font-mono); font-weight: 800; font-size: 16px; color: #166534; background: #f0fdf4; border: 1.5px solid #86efac; width: 100%;">
                        </div>
                    </div>
                </div>

                <!-- 4. Required Documents -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-file-circle-check" style="color: #4f46e5;"></i> 
                        Required Documents (One per line)
                    </label>
                    <textarea name="sub_services[0][required_documents]" class="form-control" rows="3" 
                              placeholder="Aadhaar Card&#10;Passport Photo&#10;Signature / Thumb Impression" style="font-size: 13px; border-radius: 6px; width: 100%;"></textarea>
                    <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                        These documents will automatically appear when an operator selects this sub-service at the desk.
                    </small>
                </div>

                <!-- 5. Operator Links (Vertical Layout) -->
                <div style="background-color: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 18px 20px;">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1e40af; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-link"></i> Operator Links (Portal & Tutorial Links)
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <!-- Work Portal Link -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-globe" style="color: #2563eb;"></i> Operator Work Portal Link
                            </label>
                            <input type="text" name="sub_services[0][work_portal_link]" class="form-control" 
                                   placeholder="https://aaplesarkar.mahaonline.gov.in or relevant portal link" 
                                   style="height: 42px; font-size: 13px; background: white; width: 100%;">
                        </div>

                        <!-- Video Tutorial Link -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-brands fa-youtube" style="color: #dc2626;"></i> Watch Tutorial Video Link
                            </label>
                            <input type="text" name="sub_services[0][video_instruction_link]" class="form-control" 
                                   placeholder="https://www.youtube.com/watch?v=... or training link" 
                                   style="height: 42px; font-size: 13px; background: white; width: 100%;">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Sub-Service Button Bar -->
    <div style="margin: 20px 0; text-align: center;">
        <button type="button" class="btn btn-secondary" id="addSubServiceBottomBtn" style="padding: 12px 28px; font-weight: 700; border: 2px dashed #94a3b8; background: #f8fafc; color: #334155; border-radius: 8px;">
            <i class="fa-solid fa-plus" style="color: #2563eb;"></i> + Add Another Sub-Service
        </button>
    </div>

    <!-- Final Submission Bar -->
    <div class="erp-card" style="padding: 16px 24px; border: 1.5px solid #cbd5e1; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-top: 24px; background: white;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">
            Total Sub-Services to Register: <span id="totalSubServicesCount" style="font-weight: 800; color: #4f46e5; font-size: 15px;">1</span>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 800; background: #4f46e5; border-color: #3730a3; font-size: 14px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                <i class="fa-solid fa-check"></i> Save All Services
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let subServiceIndex = 1;

function attachPriceListeners(card) {
    const govtInput = card.querySelector('.govt-fee-input');
    const serviceInput = card.querySelector('.service-charge-input');
    const totalInput = card.querySelector('.total-price-input');
    const nameInput = card.querySelector('.sub-service-name-input');
    const previewSpan = card.querySelector('.sub-service-title-preview');

    function calculateTotal() {
        const govt = parseFloat(govtInput.value) || 0;
        const srv = parseFloat(serviceInput.value) || 0;
        totalInput.value = (govt + srv).toFixed(2);
    }

    govtInput.addEventListener('input', calculateTotal);
    serviceInput.addEventListener('input', calculateTotal);

    if (nameInput && previewSpan) {
        nameInput.addEventListener('input', function() {
            previewSpan.textContent = this.value.trim() ? this.value.trim() : 'New Sub-Service Details';
        });
    }
}

function updateCardNumbers() {
    const cards = document.querySelectorAll('.sub-service-card');
    cards.forEach((card, idx) => {
        const num = idx + 1;
        const badge = card.querySelector('.sub-service-badge');
        if (badge) {
            badge.innerHTML = `<i class="fa-solid fa-folder-tree"></i> Sub-Service #${num}`;
        }
        const removeBtn = card.querySelector('.remove-sub-service-btn');
        if (removeBtn) {
            removeBtn.style.display = cards.length > 1 ? 'inline-block' : 'none';
        }
    });
    document.getElementById('totalSubServicesCount').textContent = cards.length;
}

function createSubServiceCard(index) {
    const card = document.createElement('div');
    card.className = 'sub-service-card erp-card';
    card.style.cssText = 'border: 1.5px solid #cbd5e1; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);';
    card.innerHTML = `
        <div class="sub-service-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="sub-service-badge" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 12px; padding: 4px 12px; border-radius: 6px; border: 1px solid #c7d2fe;">
                    <i class="fa-solid fa-folder-tree"></i> Sub-Service #${index + 1}
                </span>
                <span style="font-size: 13px; font-weight: 600; color: #475569;" class="sub-service-title-preview">New Sub-Service Details</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-sub-service-btn" style="padding: 4px 10px; font-size: 12px; border-radius: 6px;" title="Remove this sub-service">
                <i class="fa-solid fa-trash-can"></i> Remove
            </button>
        </div>

        <div class="erp-card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
            <!-- 1. Sub-Service Name -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-tag" style="color: #4f46e5;"></i> Sub-Service Name
                </label>
                <input type="text" name="sub_services[${index}][sub_service_name]" class="form-control sub-service-name-input" required 
                       placeholder="e.g. New PAN Application Form 49A / Correction / Duplicate" 
                       style="height: 44px; font-weight: 600; width: 100%;">
                <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                    Specific application or form type under this main service.
                </small>
            </div>

            <!-- 2. Expected Processing Days -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> Expected Processing Time (Days)
                </label>
                <input type="number" name="sub_services[${index}][expected_processing_days]" class="form-control" required min="1" value="7"
                       placeholder="7" style="height: 44px; font-weight: 700; width: 100%;">
                <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                    Estimated processing time required for this sub-service (in days).
                </small>
            </div>

            <!-- 3. Fee Structure & Margin (Vertical Layout) -->
            <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 18px 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #166534; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-indian-rupee-sign"></i> Fee Structure & Margin
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #334155;">Government Fee (₹)</label>
                        <input type="number" step="0.01" name="sub_services[${index}][govt_fee]" class="form-control govt-fee-input" required min="0" value="0.00"
                               placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700; width: 100%;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #334155;">Service Charge / Kendra Margin (₹)</label>
                        <input type="number" step="0.01" name="sub_services[${index}][service_charge]" class="form-control service-charge-input" required min="0" value="0.00"
                               placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700; width: 100%;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 800; color: #166534;">Total Customer Price (₹)</label>
                        <input type="number" step="0.01" name="sub_services[${index}][price]" class="form-control total-price-input" required min="0" value="0.00"
                               placeholder="0.00" style="height: 44px; font-family: var(--font-mono); font-weight: 800; font-size: 16px; color: #166534; background: #f0fdf4; border: 1.5px solid #86efac; width: 100%;">
                    </div>
                </div>
            </div>

            <!-- 4. Required Documents -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-circle-check" style="color: #4f46e5;"></i> 
                    Required Documents (One per line)
                </label>
                <textarea name="sub_services[${index}][required_documents]" class="form-control" rows="3" 
                          placeholder="Aadhaar Card&#10;Passport Photo&#10;Signature / Thumb Impression" style="font-size: 13px; border-radius: 6px; width: 100%;"></textarea>
                <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                    These documents will automatically appear when an operator selects this sub-service at the desk.
                </small>
            </div>

            <!-- 5. Operator Links (Vertical Layout) -->
            <div style="background-color: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 18px 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1e40af; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-link"></i> Operator Links (Portal & Tutorial Links)
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <!-- Work Portal Link -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-globe" style="color: #2563eb;"></i> Operator Work Portal Link
                        </label>
                        <input type="text" name="sub_services[${index}][work_portal_link]" class="form-control" 
                               placeholder="https://aaplesarkar.mahaonline.gov.in or relevant portal link" 
                               style="height: 42px; font-size: 13px; background: white; width: 100%;">
                    </div>

                    <!-- Video Tutorial Link -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-youtube" style="color: #dc2626;"></i> Watch Tutorial Video Link
                        </label>
                        <input type="text" name="sub_services[${index}][video_instruction_link]" class="form-control" 
                               placeholder="https://www.youtube.com/watch?v=... or training link" 
                               style="height: 42px; font-size: 13px; background: white; width: 100%;">
                    </div>
                </div>
            </div>
        </div>
    `;

    attachPriceListeners(card);

    const removeBtn = card.querySelector('.remove-sub-service-btn');
    removeBtn.addEventListener('click', function() {
        card.remove();
        updateCardNumbers();
    });

    return card;
}

function addSubService() {
    const container = document.getElementById('subServicesContainer');
    const newCard = createSubServiceCard(subServiceIndex);
    subServiceIndex++;
    container.appendChild(newCard);
    updateCardNumbers();
    newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

document.addEventListener('DOMContentLoaded', function() {
    const initialCard = document.querySelector('.sub-service-card');
    if (initialCard) {
        attachPriceListeners(initialCard);
        const removeBtn = initialCard.querySelector('.remove-sub-service-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                initialCard.remove();
                updateCardNumbers();
            });
        }
    }

    document.getElementById('addSubServiceBtn').addEventListener('click', addSubService);
    document.getElementById('addSubServiceBottomBtn').addEventListener('click', addSubService);
    updateCardNumbers();
});
</script>
@endpush
@endsection
