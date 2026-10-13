@extends('layouts.app')

@section('title', 'Add Main Service & Sub-Services')

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px; font-size: 22px;">
            <i class="fa-solid fa-layer-group" style="color: #4f46e5;"></i> 
            Add New Service Master (मुख्य सेवा व उप-सेवा)
        </h1>
        <p class="page-subtitle">
            एका मुख्य सेवेअंतर्गत एकाच वेळी अनेक उप-सेवा (Sub-Services), त्यांचे स्वतंत्र दर, दिवस, कागदपत्रे आणि पोर्टल्स नोंदवा.
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
                MAIN SERVICE (मुख्य सेवा)
            </div>
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 11px; padding: 4px 10px;">
                Parent Service
            </span>
        </div>
        <div class="erp-card-body" style="padding: 22px 24px; background: #ffffff;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-size: 14px; font-weight: 700; color: #1e293b;">
                    Main Service Name (मुख्य सेवेचे नाव)
                </label>
                <input type="text" name="main_service_name" list="main_services_list" class="form-control" required 
                       placeholder="उदा. PAN CARD, उत्पन्नाचा दाखला, RENT AGREEMENT, DOMICILE CERTIFICATE..." 
                       value="{{ old('main_service_name') }}"
                       style="height: 48px; font-size: 15px; font-weight: 700; text-transform: uppercase; border: 2px solid #cbd5e1; border-radius: 8px;">
                
                <datalist id="main_services_list">
                    @foreach($existingMainServices as $ms)
                        <option value="{{ $ms }}"></option>
                    @endforeach
                </datalist>
                <small style="color: #64748b; font-size: 12px; margin-top: 6px; display: block;">
                    <i class="fa-solid fa-circle-info" style="color: #4f46e5;"></i> जुनी मुख्य सेवा निवडू शकता किंवा नवीन नाव टाकू शकता. खाली जोडल्या जाणाऱ्या सर्व उप-सेवा या मुख्य सेवेअंतर्गत नोंदवल्या जातील.
                </small>
            </div>
        </div>
    </div>

    <!-- Section Header for Sub-Services -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 17px; font-weight: 800; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-sitemap" style="color: #2563eb;"></i>
                Sub-Services List (उप-सेवांची यादी)
            </h3>
            <p style="font-size: 12px; color: #64748b; margin: 3px 0 0 0;">
                प्रत्येक उप-सेवेचे नाव, अपेक्षित दिवस, फी रचना, कागदपत्रे व लिंक्स स्वतंत्रपणे टाका.
            </p>
        </div>
        <button type="button" class="btn btn-primary" id="addSubServiceBtn" style="background: #2563eb; border-color: #1d4ed8; font-weight: 700; box-shadow: 0 2px 6px rgba(37,99,235,0.25);">
            <i class="fa-solid fa-plus"></i> + Add Another Sub-Service (आणखी उप-सेवा जोडा)
        </button>
    </div>

    <!-- Sub Services Container -->
    <div id="subServicesContainer" style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Default Initial Sub-Service Card -->
        <div class="sub-service-card erp-card" style="border: 1.5px solid #cbd5e1; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
            <div class="sub-service-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="sub-service-badge" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 12px; padding: 4px 12px; border-radius: 6px; border: 1px solid #c7d2fe;">
                        <i class="fa-solid fa-folder-tree"></i> उप-सेवा #1
                    </span>
                    <span style="font-size: 13px; font-weight: 600; color: #475569;" class="sub-service-title-preview">नवीन उप-सेवा तपशील</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-sub-service-btn" style="display: none; padding: 4px 10px; font-size: 12px; border-radius: 6px;" title="ही उप-सेवा काढा">
                    <i class="fa-solid fa-trash-can"></i> Remove
                </button>
            </div>

            <div class="erp-card-body" style="padding: 22px;">
                <!-- Row 1: Sub-Service Name & Expected Days -->
                <div class="form-grid-2" style="margin-bottom: 18px; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-weight: 700; color: #1e293b;">
                            Sub-Service Name (उप-सेवेचे नाव)
                        </label>
                        <input type="text" name="sub_services[0][sub_service_name]" class="form-control sub-service-name-input" required 
                               placeholder="उदा. New PAN Application Form 49A / Correction / Duplicate" 
                               style="height: 44px; font-weight: 600;">
                        <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                            या मुख्य सेवेअंतर्गत येणाऱ्या विशिष्ट अर्जाचा किंवा फॉर्मचा प्रकार.
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-weight: 700; color: #1e293b;">
                            <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> Expected Processing Time (अपेक्षित दिवस)
                        </label>
                        <input type="number" name="sub_services[0][expected_processing_days]" class="form-control" required min="1" value="7"
                               placeholder="7" style="height: 44px; font-weight: 700;">
                        <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                            या उप-सेवेसाठी लागणारा अंदाजे कालावधी (दिवसांमध्ये).
                        </small>
                    </div>
                </div>

                <!-- Row 2: Fee Structure -->
                <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px 18px; margin-bottom: 18px;">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #166534; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-indian-rupee-sign"></i> Fee Structure (या उप-सेवेचे दर व कमिशन)
                    </div>
                    <div class="form-grid-3" style="gap: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-size: 12px; font-weight: 700;">Government Fee (सरकारी फी ₹)</label>
                            <input type="number" step="0.01" name="sub_services[0][govt_fee]" class="form-control govt-fee-input" required min="0" value="0.00"
                                   placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-size: 12px; font-weight: 700;">Service Charge / Kendra Margin (नफा ₹)</label>
                            <input type="number" step="0.01" name="sub_services[0][service_charge]" class="form-control service-charge-input" required min="0" value="0.00"
                                   placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #166534;">Total Customer Price (एकूण ग्राहक फी ₹)</label>
                            <input type="number" step="0.01" name="sub_services[0][price]" class="form-control total-price-input" required min="0" value="0.00"
                                   placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 800; font-size: 15px; color: #166534; background: #f0fdf4;">
                        </div>
                    </div>
                </div>

                <!-- Row 3: Required Documents -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-file-circle-check" style="color: #4f46e5;"></i> 
                        Required Documents (या उप-सेवेसाठी लागणारी कागदपत्रे - एका ओळीत एक)
                    </label>
                    <textarea name="sub_services[0][required_documents]" class="form-control" rows="3" 
                              placeholder="आधार कार्ड&#10;पासपोर्ट फोटो&#10;स्वाक्षरी / अंगठा" style="font-size: 13px; border-radius: 6px;"></textarea>
                    <small style="color: #64748b; font-size: 11px;">
                        काऊंटरवर जेव्हा ऑपरेटर ही उप-सेवा निवडेल, तेव्हा ही कागदपत्रे आपोआप समोर दिसतील.
                    </small>
                </div>

                <!-- Row 4: Operator Links (Work Portal & Tutorial Video) -->
                <div style="background-color: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; padding: 16px 18px;">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1e40af; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-link"></i> Links For Operator (पोर्टल व ट्युटोरियल लिंक्स)
                    </div>
                    <div class="form-grid-2" style="gap: 16px;">
                        <!-- Work Portal Link -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-globe" style="color: #2563eb;"></i> Operator Work Portal Link (सरकारी कामाचे पोर्टल)
                            </label>
                            <input type="text" name="sub_services[0][work_portal_link]" class="form-control" 
                                   placeholder="https://aaplesarkar.mahaonline.gov.in किंवा इतर कामाची लिंक" 
                                   style="height: 40px; font-size: 12.5px; background: white;">
                        </div>

                        <!-- Video Tutorial Link -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-brands fa-youtube" style="color: #dc2626;"></i> Watch Tutorial Video Link (व्हिडिओ ट्युटोरियल)
                            </label>
                            <input type="text" name="sub_services[0][video_instruction_link]" class="form-control" 
                                   placeholder="https://www.youtube.com/watch?v=... किंवा ट्रेनिंग लिंक" 
                                   style="height: 40px; font-size: 12.5px; background: white;">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Sub-Service Button Bar -->
    <div style="margin: 20px 0; text-align: center;">
        <button type="button" class="btn btn-secondary" id="addSubServiceBottomBtn" style="padding: 12px 28px; font-weight: 700; border: 2px dashed #94a3b8; background: #f8fafc; color: #334155; border-radius: 8px;">
            <i class="fa-solid fa-plus" style="color: #2563eb;"></i> + Add Another Sub-Service (आणखी उप-सेवा जोडा)
        </button>
    </div>

    <!-- Final Submission Bar -->
    <div class="erp-card" style="padding: 16px 24px; border: 1.5px solid #cbd5e1; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-top: 24px; background: white;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">
            एकूण नोंदवल्या जाणाऱ्या उप-सेवा: <span id="totalSubServicesCount" style="font-weight: 800; color: #4f46e5; font-size: 15px;">1</span>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 800; background: #4f46e5; border-color: #3730a3; font-size: 14px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                <i class="fa-solid fa-check"></i> Save All Services (सर्व उप-सेवा सेव्ह करा)
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
            previewSpan.textContent = this.value.trim() ? this.value.trim() : 'नवीन उप-सेवा तपशील';
        });
    }
}

function updateCardNumbers() {
    const cards = document.querySelectorAll('.sub-service-card');
    cards.forEach((card, idx) => {
        const num = idx + 1;
        const badge = card.querySelector('.sub-service-badge');
        if (badge) {
            badge.innerHTML = `<i class="fa-solid fa-folder-tree"></i> उप-सेवा #${num}`;
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
                    <i class="fa-solid fa-folder-tree"></i> उप-सेवा #${index + 1}
                </span>
                <span style="font-size: 13px; font-weight: 600; color: #475569;" class="sub-service-title-preview">नवीन उप-सेवा तपशील</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-sub-service-btn" style="padding: 4px 10px; font-size: 12px; border-radius: 6px;" title="ही उप-सेवा काढा">
                <i class="fa-solid fa-trash-can"></i> Remove
            </button>
        </div>

        <div class="erp-card-body" style="padding: 22px;">
            <!-- Row 1: Sub-Service Name & Expected Days -->
            <div class="form-grid-2" style="margin-bottom: 18px; gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label required" style="font-weight: 700; color: #1e293b;">
                        Sub-Service Name (उप-सेवेचे नाव)
                    </label>
                    <input type="text" name="sub_services[${index}][sub_service_name]" class="form-control sub-service-name-input" required 
                           placeholder="उदा. New PAN Application Form 49A / Correction / Duplicate" 
                           style="height: 44px; font-weight: 600;">
                    <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                        या मुख्य सेवेअंतर्गत येणाऱ्या विशिष्ट अर्जाचा किंवा फॉर्मचा प्रकार.
                    </small>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label required" style="font-weight: 700; color: #1e293b;">
                        <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> Expected Processing Time (अपेक्षित दिवस)
                    </label>
                    <input type="number" name="sub_services[${index}][expected_processing_days]" class="form-control" required min="1" value="7"
                           placeholder="7" style="height: 44px; font-weight: 700;">
                    <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">
                        या उप-सेवेसाठी लागणारा अंदाजे कालावधी (दिवसांमध्ये).
                    </small>
                </div>
            </div>

            <!-- Row 2: Fee Structure -->
            <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px 18px; margin-bottom: 18px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #166534; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-indian-rupee-sign"></i> Fee Structure (या उप-सेवेचे दर व कमिशन)
                </div>
                <div class="form-grid-3" style="gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700;">Government Fee (सरकारी फी ₹)</label>
                        <input type="number" step="0.01" name="sub_services[${index}][govt_fee]" class="form-control govt-fee-input" required min="0" value="0.00"
                               placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700;">Service Charge / Kendra Margin (नफा ₹)</label>
                        <input type="number" step="0.01" name="sub_services[${index}][service_charge]" class="form-control service-charge-input" required min="0" value="0.00"
                               placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 700;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #166534;">Total Customer Price (एकूण ग्राहक फी ₹)</label>
                        <input type="number" step="0.01" name="sub_services[${index}][price]" class="form-control total-price-input" required min="0" value="0.00"
                               placeholder="0.00" style="height: 42px; font-family: var(--font-mono); font-weight: 800; font-size: 15px; color: #166534; background: #f0fdf4;">
                    </div>
                </div>
            </div>

            <!-- Row 3: Required Documents -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-circle-check" style="color: #4f46e5;"></i> 
                    Required Documents (या उप-सेवेसाठी लागणारी कागदपत्रे - एका ओळीत एक)
                </label>
                <textarea name="sub_services[${index}][required_documents]" class="form-control" rows="3" 
                          placeholder="आधार कार्ड&#10;पासपोर्ट फोटो&#10;स्वाक्षरी / अंगठा" style="font-size: 13px; border-radius: 6px;"></textarea>
                <small style="color: #64748b; font-size: 11px;">
                    काऊंटरवर जेव्हा ऑपरेटर ही उप-सेवा निवडेल, तेव्हा ही कागदपत्रे आपोआप समोर दिसतील.
                </small>
            </div>

            <!-- Row 4: Operator Links -->
            <div style="background-color: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; padding: 16px 18px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1e40af; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-link"></i> Links For Operator (पोर्टल व ट्युटोरियल लिंक्स)
                </div>
                <div class="form-grid-2" style="gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-globe" style="color: #2563eb;"></i> Operator Work Portal Link (सरकारी कामाचे पोर्टल)
                        </label>
                        <input type="text" name="sub_services[${index}][work_portal_link]" class="form-control" 
                               placeholder="https://aaplesarkar.mahaonline.gov.in किंवा इतर कामाची लिंक" 
                               style="height: 40px; font-size: 12.5px; background: white;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-youtube" style="color: #dc2626;"></i> Watch Tutorial Video Link (व्हिडिओ ट्युटोरियल)
                        </label>
                        <input type="text" name="sub_services[${index}][video_instruction_link]" class="form-control" 
                               placeholder="https://www.youtube.com/watch?v=... किंवा ट्रेनिंग लिंक" 
                               style="height: 40px; font-size: 12.5px; background: white;">
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
