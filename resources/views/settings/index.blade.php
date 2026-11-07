@extends('layouts.app')

@section('title', 'Organization Settings')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Organization & Kendra Settings</h1>
        <p class="page-subtitle">Configure business branding, GST details, thermal receipt headers, and notification APIs</p>
    </div>
</div>

<div class="erp-card" style="max-width: 800px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-sliders"></i> Kendra Branding & Information</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label required">Business / Kendra Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $tenant->name) }}">
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label required">Owner / Primary Contact</label>
                    <input type="text" name="contact_name" class="form-control" required value="{{ old('contact_name', $tenant->contact_name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Contact Mobile</label>
                    <input type="tel" name="contact_mobile" class="form-control" required value="{{ old('contact_mobile', $tenant->contact_mobile) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" required value="{{ old('contact_email', $tenant->contact_email) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Headquarters Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $tenant->address) }}</textarea>
            </div>

            <div class="form-grid-4">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $tenant->city) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" value="{{ old('district', $tenant->district) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $tenant->pincode) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $tenant->gst_number) }}">
                </div>
            </div>

            <!-- WhatsApp Cloud API Configuration (Meta Developer & Production) -->
            <div style="background-color: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: var(--radius-md); padding: 20px; margin: 24px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h4 style="font-size: 15px; font-weight: 800; margin: 0; color: #166534; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-brands fa-whatsapp" style="font-size: 18px; color: #22c55e;"></i> WhatsApp Cloud API (Meta Official / Test Number)
                        </h4>
                        <p style="font-size: 12px; color: #15803d; margin: 4px 0 0 0;">
                            Configure direct 1-click background WhatsApp messaging using Meta Developer test number or live business account.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 18px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 700; color: #166534;">
                            <input type="checkbox" name="whatsapp_enabled" value="1" {{ !empty($tenant->settings_json['whatsapp_enabled']) ? 'checked' : '' }} style="width: 17px; height: 17px; accent-color: #16a34a;">
                            Enable WhatsApp
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 700; color: #166534;">
                            <input type="checkbox" name="whatsapp_auto_status_notify" value="1" {{ !empty($tenant->settings_json['whatsapp_auto_status_notify']) ? 'checked' : '' }} style="width: 17px; height: 17px; accent-color: #16a34a;">
                            Auto-Send on Status Change
                        </label>
                    </div>
                </div>

                <div class="form-grid-2" style="margin-bottom: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #14532d;">
                            Meta Phone Number ID <span style="font-weight: 400; font-size: 11px; color: #15803d;">(From developers.facebook.com -> WhatsApp -> API Setup)</span>
                        </label>
                        <input type="text" id="wa_phone_number_id" name="whatsapp_phone_number_id" class="form-control" placeholder="e.g. 10482910492810" value="{{ old('whatsapp_phone_number_id', $tenant->settings_json['whatsapp_phone_number_id'] ?? '') }}" style="border-color: #86efac; font-family: var(--font-mono);">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #14532d;">
                            Meta Access Token <span style="font-weight: 400; font-size: 11px; color: #15803d;">(Temporary 24h Token or System User Token)</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="wa_access_token" name="whatsapp_access_token" class="form-control" placeholder="EAABwz..." value="{{ old('whatsapp_access_token', $tenant->settings_json['whatsapp_access_token'] ?? '') }}" style="border-color: #86efac; padding-right: 40px; font-family: var(--font-mono);">
                            <button type="button" onclick="toggleWaTokenVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #15803d; cursor: pointer;">
                                <i class="fa-solid fa-eye" id="waTokenEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Instant Live Test Tool for Meta Test Number -->
                <div style="background: #ffffff; border: 1px dashed #86efac; border-radius: 8px; padding: 14px; margin-top: 14px;">
                    <div style="font-size: 12.5px; font-weight: 800; color: #166534; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="fa-solid fa-vial" style="color: #22c55e;"></i> Test Connection & Send Sample WhatsApp</span>
                        <span style="font-size: 11px; font-weight: 600; color: #64748b;">(Works with Meta Test Number & Personal Verified WhatsApp)</span>
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 3px; display: block;">Recipient WhatsApp Number (with Country Code):</label>
                            <input type="text" id="wa_test_recipient" class="form-control" placeholder="e.g. 919876543210" value="{{ old('contact_mobile', $tenant->contact_mobile) }}" style="font-family: var(--font-mono); font-size: 13px;">
                        </div>
                        <div style="width: 220px;">
                            <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 3px; display: block;">Message Type:</label>
                            <select id="wa_test_type" class="form-select" style="font-size: 12.5px;">
                                <option value="template">Official Template ('hello_world')</option>
                                <option value="text">Custom Text Message (Maha E-Seva Alert)</option>
                            </select>
                        </div>
                        <div>
                            <button type="button" id="btnTestWhatsApp" onclick="runWhatsAppTest()" class="btn btn-sm" style="background: #16a34a; color: #ffffff; font-weight: 700; font-size: 12.5px; padding: 8px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: none; box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);">
                                <i class="fa-brands fa-whatsapp"></i> <span id="waTestBtnText">Send Test WhatsApp</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live Test Result Banner -->
                    <div id="waTestResultBox" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: 6px; font-size: 12px;"></div>
                </div>
            </div>

            <div style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin: 20px 0;">
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: #1e293b;">
                    <i class="fa-solid fa-comment-sms" style="color: #4f46e5;"></i> SMS Provider Configuration
                </h4>
                <div class="form-grid-2">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">SMS Sender Header (6 Chars)</label>
                        <input type="text" name="sms_sender_id" class="form-control" value="{{ old('sms_sender_id', $tenant->settings_json['sms_sender_id'] ?? 'MHSEVA') }}" maxlength="6">
                    </div>
                </div>
            </div>

            <!-- Fast Daily Entry Advertising & Announcement Banner Settings -->
            @php
                $adSettings = $tenant->settings_json ?? [];
                $isAdEnabled = !array_key_exists('advertising_banner_enabled', $adSettings) || !empty($adSettings['advertising_banner_enabled']);
                $adTag = old('advertising_banner_tag', $adSettings['advertising_banner_tag'] ?? 'ADVERTISING');
                $defaultAdText = '📢 महा ई-सेवा केंद्र विशेष सूचना / जाहिरাত: नवीन पॅन कार्ड (२ तासांत), आधार अपडेट, उत्पन्न दाखला, डोमिसाईल, नॉन-क्रिमिलेअर, लाडकी बहीण योजना, शेतकरी सन्मान निधी, रेशन कार्ड ई-केवायसी व सर्व शासकीय योजनांचे अर्ज आपल्या केंद्रावर जलद गतीने भरून मिळतील! त्वरित संपर्क साधा.';
                $adText = old('advertising_banner_text', $adSettings['advertising_banner_text'] ?? $defaultAdText);
                $adVisibility = old('advertising_banner_visibility', $adSettings['advertising_banner_visibility'] ?? 'ALL');
                $allowedEmpIds = array_map('intval', (array) old('advertising_banner_employee_ids', $adSettings['advertising_banner_employee_ids'] ?? []));
            @endphp
            <div style="background-color: #fffbeb; border: 1.5px solid #fde68a; border-radius: var(--radius-md); padding: 20px; margin: 24px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h4 style="font-size: 15px; font-weight: 800; margin: 0; color: #9a3412; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-bullhorn" style="font-size: 18px; color: #ea580c;"></i> Fast Daily Entry - Advertising & Announcement Banner
                        </h4>
                        <p style="font-size: 12px; color: #b45309; margin: 4px 0 0 0;">
                            Configure promotion announcements and scrolling ticker text displayed at the top of the Fast Counter desk.
                        </p>
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: 700; color: #9a3412; background: #ffffff; padding: 6px 14px; border-radius: 6px; border: 1px solid #fed7aa; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                            <input type="checkbox" name="advertising_banner_enabled" id="ad_banner_enabled" value="1" {{ $isAdEnabled ? 'checked' : '' }} onchange="updateAdLivePreview()" style="width: 17px; height: 17px; accent-color: #ea580c;">
                            Enable Banner on Fast Entry
                        </label>
                    </div>
                </div>

                <div class="form-grid-2" style="margin-bottom: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #9a3412;">
                            Badge Tag / Title <span style="font-weight: 400; font-size: 11px; color: #b45309;">(e.g. ADVERTISING, विशेष सूचना, OFFER)</span>
                        </label>
                        <input type="text" id="ad_banner_tag" name="advertising_banner_tag" class="form-control" value="{{ $adTag }}" placeholder="ADVERTISING" oninput="updateAdLivePreview()" style="border-color: #fcd34d; font-weight: 700; color: #c2410c;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #9a3412;">
                            Target Audience / Visibility <span style="font-weight: 400; font-size: 11px; color: #b45309;">(Who can see this banner)</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 14px; height: 38px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #7c2d12; cursor: pointer;">
                                <input type="radio" name="advertising_banner_visibility" value="ALL" {{ $adVisibility === 'ALL' ? 'checked' : '' }} onchange="toggleEmployeeVisibilityOptions(this.value)" style="accent-color: #ea580c;">
                                All Staff & Operators
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #7c2d12; cursor: pointer;">
                                <input type="radio" name="advertising_banner_visibility" value="SPECIFIC" {{ $adVisibility === 'SPECIFIC' ? 'checked' : '' }} onchange="toggleEmployeeVisibilityOptions(this.value)" style="accent-color: #ea580c;">
                                Specific Employees Only
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Specific Employees Checklist (Toggled when Specific is chosen) -->
                <div id="ad_employees_panel" style="display: {{ $adVisibility === 'SPECIFIC' ? 'block' : 'none' }}; background: #ffffff; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12.5px; font-weight: 700; color: #9a3412;">
                            <i class="fa-solid fa-user-check" style="color: #ea580c;"></i> Select Employees who will see the advertising banner:
                        </span>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleAllAdEmployees(true)" style="font-size: 11px; padding: 2px 8px;">Select All</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleAllAdEmployees(false)" style="font-size: 11px; padding: 2px 8px;">Deselect All</button>
                        </div>
                    </div>
                    @if(isset($employees) && $employees->count() > 0)
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto; padding-right: 4px;">
                            @foreach($employees as $emp)
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #334155; background: #f8fafc; padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" name="advertising_banner_employee_ids[]" value="{{ $emp->id }}" class="ad-emp-checkbox" {{ in_array($emp->id, $allowedEmpIds) ? 'checked' : '' }} style="accent-color: #ea580c;">
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $emp->user?->name ?? 'Staff #' . $emp->id }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size: 12px; color: #64748b; margin: 0; font-style: italic;">No active employees found in this Kendra.</p>
                    @endif
                </div>

                <!-- Scrolling Marquee Textarea -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 700; color: #9a3412;">
                        Announcement & Promotion Marquee Text <span style="font-weight: 400; font-size: 11px; color: #b45309;">(This message scrolls continuously across the counter)</span>
                    </label>
                    <textarea id="ad_banner_text" name="advertising_banner_text" class="form-control" rows="3" placeholder="Enter continuous scrolling advertisement or announcement message..." oninput="updateAdLivePreview()" style="border-color: #fcd34d; font-size: 13px; line-height: 1.5;">{{ $adText }}</textarea>
                </div>

                <!-- Real-time Live Preview -->
                <div style="background: #ffffff; border: 1px dashed #f59e0b; border-radius: 8px; padding: 12px; margin-top: 10px;">
                    <div style="font-size: 11px; font-weight: 800; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-eye"></i> Live Marquee Preview (How it will appear on Fast Daily Entry):
                    </div>
                    <div id="ad_live_preview_card" class="advertising-banner-card" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%); border: 1.5px solid #fcd34d; border-radius: 8px; padding: 7px 14px; box-shadow: 0 2px 5px rgba(245, 158, 11, 0.12); display: {{ $isAdEnabled ? 'flex' : 'none' }}; align-items: center; justify-content: space-between; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px; overflow: hidden;">
                            <span id="preview_ad_tag" class="badge" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase; box-shadow: 0 1px 3px rgba(194, 65, 12, 0.35); display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;">
                                <i class="fa-solid fa-bullhorn" style="font-size: 11px;"></i> <span>{{ $adTag }}</span>
                            </span>
                            <div style="flex: 1; overflow: hidden; white-space: nowrap;">
                                <marquee id="preview_ad_marquee" behavior="scroll" direction="left" scrollamount="6" style="font-size: 12.5px; font-weight: 700; color: #9a3412; vertical-align: middle;">
                                    {{ $adText }}
                                </marquee>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                            <span style="font-size: 11px; font-weight: 700; color: #9a3412; background: rgba(255,255,255,0.7); padding: 3px 8px; border-radius: 5px; border: 1px solid #fed7aa;">
                                <i class="fa-solid fa-bolt" style="font-size: 10.5px; color: #ea580c; margin-right: 3px;"></i> Fast Counter
                            </span>
                        </div>
                    </div>
                    <div id="ad_disabled_notice" style="display: {{ $isAdEnabled ? 'none' : 'block' }}; padding: 10px; text-align: center; color: #dc2626; font-size: 12px; font-weight: 700; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px;">
                        <i class="fa-solid fa-ban"></i> Banner is currently DISABLED. It will not be shown on Fast Daily Entry.
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Organization & WhatsApp Settings</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleWaTokenVisibility() {
    const input = document.getElementById('wa_access_token');
    const icon = document.getElementById('waTokenEyeIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

function runWhatsAppTest() {
    const recipient = document.getElementById('wa_test_recipient').value.trim();
    const type = document.getElementById('wa_test_type').value;
    const phoneId = document.getElementById('wa_phone_number_id').value.trim();
    const token = document.getElementById('wa_access_token').value.trim();
    const resultBox = document.getElementById('waTestResultBox');
    const btn = document.getElementById('btnTestWhatsApp');
    const btnText = document.getElementById('waTestBtnText');

    if (!recipient) {
        alert('Please enter a recipient phone number with country code (e.g. 919876543210).');
        return;
    }

    if (!phoneId || !token) {
        alert('Please fill in Meta Phone Number ID and Access Token before testing.');
        return;
    }

    btn.disabled = true;
    btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';
    resultBox.style.display = 'none';

    fetch('{{ route("settings.whatsapp.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            recipient: recipient,
            type: type,
            phone_number_id: phoneId,
            access_token: token
        })
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        btnText.innerHTML = 'Send Test WhatsApp';
        resultBox.style.display = 'block';

        if (res.success) {
            resultBox.style.background = '#dcfce7';
            resultBox.style.border = '1.5px solid #86efac';
            resultBox.style.color = '#15803d';
            resultBox.innerHTML = `
                <div style="font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 13px;">
                    <i class="fa-solid fa-circle-check"></i> Test WhatsApp Message Delivered!
                </div>
                <div style="margin-top: 4px;">
                    Meta Cloud API responded successfully. <strong>Message ID:</strong> <code>${res.message_id || 'OK'}</code>
                </div>
                <div style="font-size: 11px; margin-top: 3px; color: #166534;">
                    Check recipient WhatsApp: <strong>${recipient}</strong>. You should receive the message instantly!
                </div>
            `;
        } else {
            resultBox.style.background = '#fee2e2';
            resultBox.style.border = '1.5px solid #fca5a5';
            resultBox.style.color = '#991b1b';
            resultBox.innerHTML = `
                <div style="font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 13px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Meta Cloud API Error:
                </div>
                <div style="margin-top: 4px; font-family: var(--font-mono); font-size: 11.5px;">
                    ${res.error || 'Failed to dispatch WhatsApp message.'}
                </div>
                <div style="font-size: 11px; margin-top: 4px; color: #7f1d1d;">
                    💡 <strong>Tip for Test Number:</strong> Meta requires recipient phone numbers to be explicitly added in your Meta Developer portal under <em>"To (Add and verify phone number)"</em> before messages can be delivered.
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnText.innerHTML = 'Send Test WhatsApp';
        resultBox.style.display = 'block';
        resultBox.style.background = '#fee2e2';
        resultBox.style.border = '1.5px solid #fca5a5';
        resultBox.style.color = '#991b1b';
        resultBox.innerHTML = `<strong>Server error:</strong> ${err.message || 'Could not connect to ERP backend.'}`;
    });
}

function updateAdLivePreview() {
    const isEnabledEl = document.getElementById('ad_banner_enabled');
    const isEnabled = isEnabledEl ? isEnabledEl.checked : true;
    const tag = (document.getElementById('ad_banner_tag')?.value || '').trim() || 'ADVERTISING';
    const text = (document.getElementById('ad_banner_text')?.value || '').trim() || 'No announcement message specified.';

    const tagSpan = document.querySelector('#preview_ad_tag span');
    const marquee = document.getElementById('preview_ad_marquee');
    const card = document.getElementById('ad_live_preview_card');
    const disabledNotice = document.getElementById('ad_disabled_notice');

    if (tagSpan) tagSpan.textContent = tag;
    if (marquee) marquee.textContent = text;

    if (card && disabledNotice) {
        if (isEnabled) {
            card.style.display = 'flex';
            disabledNotice.style.display = 'none';
        } else {
            card.style.display = 'none';
            disabledNotice.style.display = 'block';
        }
    }
}

function toggleEmployeeVisibilityOptions(visibility) {
    const panel = document.getElementById('ad_employees_panel');
    if (panel) {
        panel.style.display = (visibility === 'SPECIFIC') ? 'block' : 'none';
    }
}

function toggleAllAdEmployees(check) {
    document.querySelectorAll('.ad-emp-checkbox').forEach(cb => {
        cb.checked = check;
    });
}
</script>
@endsection
