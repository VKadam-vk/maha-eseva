@extends('layouts.app')

@section('title', 'Add New Employee')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Register Staff Operator</h1>
        <p class="page-subtitle">Register center staff operators, manage system permissions, and control service access.</p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Staff Directory
    </a>
</div>

<form action="{{ route('employees.store') }}" method="POST" id="employeeForm">
    @csrf

    <div class="access-control-wrapper">

        <!-- SECTION 1: Staff Information & Credentials -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div class="access-section-title">
                    <i class="fa-solid fa-user-plus"></i> Staff Information & Credentials
                </div>
                <span class="badge badge-ready"><i class="fa-solid fa-id-badge"></i> ERP Login Account</span>
            </div>
            <div style="padding: 24px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Employee Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Rahul Shinde" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Assigned Branch</label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->branch_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Login Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="rahul@mahaeseva.com" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Login Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label required">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" required placeholder="10-digit mobile number" value="{{ old('mobile') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Designation / Role</label>
                        <input type="text" name="designation" class="form-control" required placeholder="Service Operator" value="{{ old('designation', 'Service Operator') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label">Monthly Salary (₹)</label>
                        <input type="number" step="0.01" name="salary" class="form-control" placeholder="0.00" value="{{ old('salary', '0.00') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Proof Type</label>
                        <select name="id_proof_type" class="form-select">
                            <option value="AADHAAR">Aadhaar Card</option>
                            <option value="PAN">PAN Card</option>
                            <option value="VOTER">Voter ID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Proof Number</label>
                        <input type="text" name="id_proof_number" class="form-control" placeholder="XXXX-XXXX-XXXX" value="{{ old('id_proof_number') }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Residential address of employee">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Allowed E-Seva Services Access -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div>
                    <div class="access-section-title">
                        <i class="fa-solid fa-list-check"></i> Allowed E-Seva Services Access (ऑपरेटरसाठी सेवांची निवड)
                    </div>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; margin-bottom: 0;">
                        या कर्मचाऱ्याला (Employee) कोणत्या सेवांची कामे (उदा. फक्त PAN Card किंवा निवडक सेवा) करू द्यायची आहेत ते येथून ठरवा.
                    </p>
                </div>

                <div class="access-toolbar">
                    <label class="switch-control" title="Provide employee access to all center services">
                        <input type="checkbox" name="all_services" id="allServicesToggle" value="1" {{ old('all_services', '0') == '1' ? 'checked' : '' }}>
                        <span class="switch-slider"></span>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Grant All Services (सर्व सेवांचा ॲक्सेस)</span>
                    </label>

                    <span style="color: #cbd5e1;">|</span>

                    <button type="button" class="access-btn-xs" id="selectAllServicesBtn">
                        <i class="fa-solid fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="access-btn-xs" id="deselectAllServicesBtn">
                        <i class="fa-solid fa-xmark"></i> Deselect All
                    </button>

                    <span class="badge badge-process" id="servicesSelectedCountBadge">0 Services Selected</span>
                </div>
            </div>

            <!-- SEARCHABLE MULTI-SELECT FILTER BAR -->
            <div id="servicesDropdownPickerWrapper" style="padding: 14px 20px; background: #ffffff; border-bottom: 1.5px solid #e2e8f0;">
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 280px; position: relative;">
                            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8; font-size: 13px;"></i>
                            <input type="text" id="serviceSearchInput" placeholder="सेवा शोधा किंवा सिलेक्ट करा (उदा. PAN Card, Driving, उत्पन्न, आधार, जात प्रमाणपत्र)..." 
                                   style="width: 100%; height: 38px; padding-left: 36px; padding-right: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none;">
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="access-btn-xs btn-quick-filter" data-keyword="PAN" style="background: #eef2ff; color: #4338ca; border-color: #c7d2fe; font-weight: 700;">
                                <i class="fa-solid fa-id-card"></i> PAN Card All
                            </button>
                            <button type="button" class="access-btn-xs btn-quick-filter" data-keyword="CERTIFICATE" style="background: #ecfdf5; color: #065f46; border-color: #a7f3d0; font-weight: 700;">
                                <i class="fa-solid fa-stamp"></i> Certificates
                            </button>
                            <button type="button" class="access-btn-xs btn-quick-filter" data-keyword="AADHAAR" style="background: #fffbeb; color: #92400e; border-color: #fde68a; font-weight: 700;">
                                <i class="fa-solid fa-fingerprint"></i> Aadhaar
                            </button>
                            <button type="button" class="access-btn-xs" id="clearServiceSearchBtn" style="color: #64748b;">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- Selected Services Tags Cloud -->
                    <div id="selectedServicesTagsContainer" style="display: flex; flex-wrap: wrap; gap: 6px; min-height: 28px; padding: 6px 10px; background: #f8fafc; border-radius: 6px; border: 1px dashed #cbd5e1; align-items: center;">
                        <span style="font-size: 11.5px; color: #64748b; font-weight: 600;"><i class="fa-solid fa-tags"></i> Selected Services:</span>
                        <div id="selectedTagsList" style="display: flex; flex-wrap: wrap; gap: 6px;"></div>
                    </div>
                </div>
            </div>

            <div id="servicesContainer" style="background: #fafafa; border-top: 1px solid #f1f5f9; max-height: 480px; overflow-y: auto;">
                <div class="services-picker-grid">
                    @foreach($services as $categoryName => $catServices)
                        <div class="service-cat-divider" data-cat-name="{{ strtoupper($categoryName) }}">
                            <i class="fa-solid fa-folder-open" style="color: var(--primary-500);"></i>
                            <span>{{ $categoryName }}</span>
                            <span class="badge badge-process" style="font-size: 10px; margin-left: auto;">{{ $catServices->count() }} Services</span>
                        </div>

                        @foreach($catServices as $service)
                            <label class="service-pick-card" id="svc_card_{{ $service->id }}" 
                                   data-main="{{ strtoupper($service->main_service_name) }}"
                                   data-sub="{{ strtoupper($service->sub_service_name) }}"
                                   data-cat="{{ strtoupper($categoryName) }}">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="service-checkbox"
                                       data-title="{{ $service->main_service_name }} - {{ $service->sub_service_name }}"
                                       {{ old('all_services', '0') == '1' || (is_array(old('services')) && in_array($service->id, old('services'))) ? 'checked' : '' }}>
                                <div class="svc-icon">
                                    <i class="fa-solid fa-file-contract"></i>
                                </div>
                                <div class="service-pick-info">
                                    <div class="service-pick-title" title="{{ $service->main_service_name }}">
                                        {{ $service->main_service_name }}
                                    </div>
                                    <div class="service-pick-sub" title="{{ $service->sub_service_name }}">
                                        {{ $service->sub_service_name }}
                                    </div>
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
                                        <span style="font-size: 11px; color: var(--text-muted);"><i class="fa-regular fa-clock"></i> {{ $service->expected_processing_days }} Days</span>
                                        <span style="font-size: 11px; font-weight: 700; color: #059669;">₹{{ number_format($service->price > 0 ? $service->price : ($service->govt_fee + $service->service_charge), 0) }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- SECTION 3: System Module Permissions & Access Control -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div>
                    <div class="access-section-title">
                        <i class="fa-solid fa-shield-halved"></i> System Module Permissions (परवानग्या व अधिकार)
                    </div>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; margin-bottom: 0;">
                        या कर्मचाऱ्याला सिस्टीममधील कोणकोणते मॉड्यूल्स (Service Master, Branches, Billing, CRM) वापरायचे अधिकार द्यायचे आहेत ते निवडा.
                    </p>
                </div>

                <div class="access-toolbar">
                    <button type="button" class="access-btn-xs active" id="btnPresetOperator">
                        <i class="fa-solid fa-user-check"></i> Standard Operator Preset (शिफारस केलेला ऑपरेटर)
                    </button>
                    <button type="button" class="access-btn-xs" id="selectAllPermsBtn">
                        <i class="fa-solid fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="access-btn-xs" id="deselectAllPermsBtn">
                        <i class="fa-solid fa-xmark"></i> Deselect All
                    </button>
                </div>
            </div>

            <!-- QUICK ACCESS HIGH-LEVEL TOGGLE CARDS (YES / NO) -->
            <div style="padding: 16px 20px; background: #ffffff; border-bottom: 1.5px solid #e2e8f0;">
                <div style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">
                    ⚡ Quick Access Controls (होय / नाही जलद नियंत्रणे)
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                    <!-- 1. Service Master Control (Default NO for Operator) -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-list-check" style="color: #6366f1;"></i> Service Master Access
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                सेवा दर व मास्टर सूची पाहणे/बदलणे
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Service Master Access">
                            <input type="checkbox" id="toggleQuickServiceMaster">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 2. Branch Network Control (Default NO) -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-code-branch" style="color: #0ea5e9;"></i> Branch Network Access
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                इतर शाखा व ब्रँच नेटवर्क पाहणे
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Branch Access">
                            <input type="checkbox" id="toggleQuickBranches">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 3. Billing & Payments Control (Default YES) -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-indian-rupee-sign" style="color: #10b981;"></i> Collections & Invoices
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                पेमेंट कलेक्शन व पावत्या देणे
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Payments Access">
                            <input type="checkbox" id="toggleQuickPayments" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 4. Reports Center Control (Default NO) -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-chart-line" style="color: #f59e0b;"></i> Reports & Analytics
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                बिझनेस रिपोर्ट्स व हिशोब पाहणे
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Reports Access">
                            <input type="checkbox" id="toggleQuickReports">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 5. Customers CRM & Applications (Default YES) -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-users" style="color: #3b82f6;"></i> Customers CRM & Entries
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                ग्राहक नोंदणी व अर्ज प्रक्रिया
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle CRM Access">
                            <input type="checkbox" id="toggleQuickCRM" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 6. Follow-ups & Leads (Default YES) -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-headset" style="color: #ec4899;"></i> Follow-ups & Leads
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                ग्राहकांचे फॉलो-अप्स व नवीन लीड्स
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Follow-ups Access">
                            <input type="checkbox" id="toggleQuickFollowups" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Detailed Permissions Accordion Bar -->
            <div style="padding: 10px 20px; background: #f1f5f9; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; font-weight: 700; color: #475569;">
                    <i class="fa-solid fa-sliders"></i> तपशीलवार परवानग्या (Detailed Permissions Breakdown)
                </span>
                <button type="button" class="access-btn-xs" id="btnToggleDetailedPerms" style="background: #ffffff; border: 1px solid #cbd5e1;">
                    <i class="fa-solid fa-chevron-down" id="toggleDetailedPermsIcon"></i> <span id="toggleDetailedPermsText">विस्तृत यादी दाखवा (Show Details)</span>
                </button>
            </div>

            <div class="perm-groups-container" id="detailedPermsContainer" style="display: none;">
                @php
                    $moduleLabels = [
                        'customers' => ['icon' => 'fa-users', 'title' => 'Customers CRM'],
                        'applications' => ['icon' => 'fa-file-signature', 'title' => 'Applications Workflow'],
                        'documents' => ['icon' => 'fa-folder-closed', 'title' => 'Private Document Vault'],
                        'payments' => ['icon' => 'fa-indian-rupee-sign', 'title' => 'Billing & Payments'],
                        'services' => ['icon' => 'fa-list-check', 'title' => 'Services Master'],
                        'reports' => ['icon' => 'fa-chart-line', 'title' => 'Reports & Analytics'],
                        'employees' => ['icon' => 'fa-user-tie', 'title' => 'Staff & Tasks'],
                        'branches' => ['icon' => 'fa-code-branch', 'title' => 'Branches'],
                        'settings' => ['icon' => 'fa-gear', 'title' => 'System Settings'],
                    ];
                @endphp

                @foreach($permissions as $module => $modulePerms)
                    @php
                        $modInfo = $moduleLabels[$module] ?? ['icon' => 'fa-shield', 'title' => ucfirst($module)];
                    @endphp
                    <div class="perm-module-box">
                        <div class="perm-module-header">
                            <div class="perm-module-name">
                                <i class="fa-solid {{ $modInfo['icon'] }}" style="color: var(--primary-500);"></i>
                                <span>{{ $modInfo['title'] }}</span>
                            </div>
                            <button type="button" class="access-btn-xs btn-toggle-module" data-module="{{ $module }}">
                                Toggle All
                            </button>
                        </div>

                        <div class="perm-items-grid" id="module_grid_{{ $module }}">
                            @foreach($modulePerms as $perm)
                                @php
                                    $isDefault = in_array($perm->id, $defaultPermissionIds ?? []);
                                @endphp
                                <label class="perm-checkbox-label {{ $isDefault ? 'checked' : '' }}">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-checkbox" data-slug="{{ $perm->slug }}" data-module="{{ $module }}" {{ $isDefault ? 'checked' : '' }}>
                                    <div>
                                        <div>{{ $perm->name }}</div>
                                        <span class="perm-slug-tag">{{ $perm->slug }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; padding: 10px 0 30px;">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                <i class="fa-solid fa-check"></i> Register Staff Operator
            </button>
        </div>

    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allServicesToggle = document.getElementById('allServicesToggle');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const servicesCountBadge = document.getElementById('servicesSelectedCountBadge');
    const selectAllServicesBtn = document.getElementById('selectAllServicesBtn');
    const deselectAllServicesBtn = document.getElementById('deselectAllServicesBtn');
    const serviceSearchInput = document.getElementById('serviceSearchInput');
    const clearServiceSearchBtn = document.getElementById('clearServiceSearchBtn');
    const selectedTagsList = document.getElementById('selectedTagsList');

    // 1. SERVICES MANAGEMENT
    function renderSelectedTags() {
        if (!selectedTagsList) return;
        selectedTagsList.innerHTML = '';
        if (allServicesToggle.checked) {
            selectedTagsList.innerHTML = '<span class="badge badge-ready" style="font-size: 11px; padding: 2px 7px;">All Center Services Enabled</span>';
            return;
        }

        let count = 0;
        serviceCheckboxes.forEach(cb => {
            if (cb.checked) {
                count++;
                if (count <= 15) {
                    const tag = document.createElement('span');
                    tag.style.cssText = 'background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #c7d2fe;';
                    tag.innerHTML = `<span>${cb.dataset.title || 'Service'}</span> <i class="fa-solid fa-xmark" style="cursor: pointer; opacity: 0.7;" title="Remove"></i>`;
                    tag.querySelector('i').addEventListener('click', function () {
                        cb.checked = false;
                        updateServicesUI();
                    });
                    selectedTagsList.appendChild(tag);
                }
            }
        });

        if (count > 15) {
            const more = document.createElement('span');
            more.style.cssText = 'font-size: 11px; color: #6366f1; font-weight: 700;';
            more.textContent = `+ ${count - 15} more`;
            selectedTagsList.appendChild(more);
        }

        if (count === 0) {
            selectedTagsList.innerHTML = '<span style="font-size: 11px; color: #94a3b8; font-style: italic;">No specific service selected. Please search and select above.</span>';
        }
    }

    function updateServicesUI() {
        const isAll = allServicesToggle.checked;
        let selectedCount = 0;

        serviceCheckboxes.forEach(cb => {
            const card = cb.closest('.service-pick-card');
            if (isAll) {
                cb.checked = true;
                if (card) card.classList.add('selected');
            } else {
                if (cb.checked) {
                    if (card) card.classList.add('selected');
                    selectedCount++;
                } else {
                    if (card) card.classList.remove('selected');
                }
            }
        });

        if (isAll) {
            servicesCountBadge.textContent = 'All Services Allowed (Full Access)';
            servicesCountBadge.className = 'badge badge-ready';
        } else {
            servicesCountBadge.textContent = `${selectedCount} / ${serviceCheckboxes.length} Services Selected`;
            servicesCountBadge.className = selectedCount > 0 ? 'badge badge-process' : 'badge badge-danger';
        }

        renderSelectedTags();
    }

    allServicesToggle.addEventListener('change', updateServicesUI);

    serviceCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (allServicesToggle.checked) {
                allServicesToggle.checked = false;
            }
            updateServicesUI();
        });
    });

    selectAllServicesBtn.addEventListener('click', function () {
        allServicesToggle.checked = true;
        updateServicesUI();
    });

    deselectAllServicesBtn.addEventListener('click', function () {
        allServicesToggle.checked = false;
        serviceCheckboxes.forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.service-pick-card');
            if (card) card.classList.remove('selected');
        });
        updateServicesUI();
    });

    // Search filter in services grid
    function filterServicesGrid(query) {
        const q = (query || '').trim().toUpperCase();
        const cards = document.querySelectorAll('.service-pick-card');
        const dividers = document.querySelectorAll('.service-cat-divider');

        cards.forEach(card => {
            const main = card.dataset.main || '';
            const sub = card.dataset.sub || '';
            const cat = card.dataset.cat || '';
            const match = !q || main.includes(q) || sub.includes(q) || cat.includes(q);
            card.style.display = match ? 'flex' : 'none';
        });

        dividers.forEach(div => {
            const cat = div.dataset.catName || '';
            if (!q) {
                div.style.display = 'flex';
            } else {
                let hasVisibleCard = false;
                let next = div.nextElementSibling;
                while (next && next.classList.contains('service-pick-card')) {
                    if (next.style.display !== 'none') {
                        hasVisibleCard = true;
                        break;
                    }
                    next = next.nextElementSibling;
                }
                div.style.display = (cat.includes(q) || hasVisibleCard) ? 'flex' : 'none';
            }
        });
    }

    if (serviceSearchInput) {
        serviceSearchInput.addEventListener('input', function () {
            filterServicesGrid(this.value);
        });
    }

    if (clearServiceSearchBtn) {
        clearServiceSearchBtn.addEventListener('click', function () {
            if (serviceSearchInput) serviceSearchInput.value = '';
            filterServicesGrid('');
        });
    }

    document.querySelectorAll('.btn-quick-filter').forEach(btn => {
        btn.addEventListener('click', function () {
            const kw = this.dataset.keyword || '';
            if (serviceSearchInput) {
                serviceSearchInput.value = kw;
                filterServicesGrid(kw);
            }
            // Automatically select matching services if unselected
            allServicesToggle.checked = false;
            const cards = document.querySelectorAll('.service-pick-card');
            cards.forEach(card => {
                if (card.style.display !== 'none') {
                    const cb = card.querySelector('.service-checkbox');
                    if (cb) cb.checked = true;
                }
            });
            updateServicesUI();
        });
    });

    // 2. PERMISSIONS LOGIC & QUICK TOGGLES
    const permCheckboxes = document.querySelectorAll('.perm-checkbox');
    const selectAllPermsBtn = document.getElementById('selectAllPermsBtn');
    const deselectAllPermsBtn = document.getElementById('deselectAllPermsBtn');
    const btnPresetOperator = document.getElementById('btnPresetOperator');
    const btnToggleDetailedPerms = document.getElementById('btnToggleDetailedPerms');
    const detailedPermsContainer = document.getElementById('detailedPermsContainer');
    const toggleDetailedPermsIcon = document.getElementById('toggleDetailedPermsIcon');
    const toggleDetailedPermsText = document.getElementById('toggleDetailedPermsText');

    // Quick Toggles
    const toggleServiceMaster = document.getElementById('toggleQuickServiceMaster');
    const toggleBranches = document.getElementById('toggleQuickBranches');
    const togglePayments = document.getElementById('toggleQuickPayments');
    const toggleReports = document.getElementById('toggleQuickReports');
    const toggleCRM = document.getElementById('toggleQuickCRM');
    const toggleFollowups = document.getElementById('toggleQuickFollowups');

    const defaultOperatorSlugs = [
        'customers.view', 'customers.create', 'customers.edit',
        'applications.view', 'applications.create', 'applications.edit',
        'documents.view', 'documents.upload',
        'payments.view', 'payments.create'
    ]; // NO 'services.view'! Operator has NO service master by default!

    function updatePermCheckboxesUI() {
        permCheckboxes.forEach(cb => {
            const label = cb.closest('.perm-checkbox-label');
            if (label) {
                if (cb.checked) {
                    label.classList.add('checked');
                } else {
                    label.classList.remove('checked');
                }
            }
        });

        // Sync Quick Toggles state from checkboxes
        if (toggleServiceMaster) {
            const svcCheckboxes = document.querySelectorAll('.perm-checkbox[data-module="services"]');
            toggleServiceMaster.checked = Array.from(svcCheckboxes).some(cb => cb.checked);
        }
        if (toggleBranches) {
            const branchCheckboxes = document.querySelectorAll('.perm-checkbox[data-module="branches"]');
            toggleBranches.checked = Array.from(branchCheckboxes).some(cb => cb.checked);
        }
        if (togglePayments) {
            const payCheckboxes = document.querySelectorAll('.perm-checkbox[data-module="payments"]');
            togglePayments.checked = Array.from(payCheckboxes).some(cb => cb.checked);
        }
        if (toggleReports) {
            const repCheckboxes = document.querySelectorAll('.perm-checkbox[data-module="reports"]');
            toggleReports.checked = Array.from(repCheckboxes).some(cb => cb.checked);
        }
        if (toggleCRM) {
            const crmCheckboxes = document.querySelectorAll('.perm-checkbox[data-module="customers"], .perm-checkbox[data-module="applications"]');
            toggleCRM.checked = Array.from(crmCheckboxes).some(cb => cb.checked);
        }
    }

    permCheckboxes.forEach(cb => {
        cb.addEventListener('change', updatePermCheckboxesUI);
    });

    // Quick Toggle listeners
    if (toggleServiceMaster) {
        toggleServiceMaster.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox[data-module="services"]').forEach(cb => cb.checked = toggleServiceMaster.checked);
            updatePermCheckboxesUI();
        });
    }

    if (toggleBranches) {
        toggleBranches.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox[data-module="branches"]').forEach(cb => cb.checked = toggleBranches.checked);
            updatePermCheckboxesUI();
        });
    }

    if (togglePayments) {
        togglePayments.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox[data-module="payments"]').forEach(cb => cb.checked = togglePayments.checked);
            updatePermCheckboxesUI();
        });
    }

    if (toggleReports) {
        toggleReports.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox[data-module="reports"]').forEach(cb => cb.checked = toggleReports.checked);
            updatePermCheckboxesUI();
        });
    }

    if (toggleCRM) {
        toggleCRM.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox[data-module="customers"], .perm-checkbox[data-module="applications"], .perm-checkbox[data-module="documents"]').forEach(cb => cb.checked = toggleCRM.checked);
            updatePermCheckboxesUI();
        });
    }

    if (toggleFollowups) {
        toggleFollowups.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox[data-slug="leads.view"], .perm-checkbox[data-slug="applications.view"]').forEach(cb => cb.checked = toggleFollowups.checked);
            updatePermCheckboxesUI();
        });
    }

    if (btnToggleDetailedPerms) {
        btnToggleDetailedPerms.addEventListener('click', function () {
            const isHidden = detailedPermsContainer.style.display === 'none';
            detailedPermsContainer.style.display = isHidden ? 'grid' : 'none';
            toggleDetailedPermsIcon.className = isHidden ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down';
            toggleDetailedPermsText.textContent = isHidden ? 'विस्तृत यादी लपवा (Hide Details)' : 'विस्तृत यादी दाखवा (Show Details)';
        });
    }

    selectAllPermsBtn.addEventListener('click', function () {
        permCheckboxes.forEach(cb => cb.checked = true);
        updatePermCheckboxesUI();
    });

    deselectAllPermsBtn.addEventListener('click', function () {
        permCheckboxes.forEach(cb => cb.checked = false);
        updatePermCheckboxesUI();
    });

    btnPresetOperator.addEventListener('click', function () {
        permCheckboxes.forEach(cb => {
            const slug = cb.getAttribute('data-slug');
            cb.checked = defaultOperatorSlugs.includes(slug);
        });
        updatePermCheckboxesUI();
    });

    document.querySelectorAll('.btn-toggle-module').forEach(btn => {
        btn.addEventListener('click', function () {
            const module = this.getAttribute('data-module');
            const modCheckboxes = document.querySelectorAll(`.perm-checkbox[data-module="${module}"]`);
            const allChecked = Array.from(modCheckboxes).every(cb => cb.checked);
            modCheckboxes.forEach(cb => cb.checked = !allChecked);
            updatePermCheckboxesUI();
        });
    });

    updateServicesUI();
    updatePermCheckboxesUI();
});
</script>
@endpush
@endsection
