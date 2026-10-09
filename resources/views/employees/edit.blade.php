@extends('layouts.app')

@section('title', 'Edit Employee & Access Permissions')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Staff: {{ $employee->user->name }}</h1>
        <p class="page-subtitle">कर्मचाऱ्याची माहिती, परवानग्या आणि सेवा प्रवेश नियंत्रण (Access & Services Permissions)</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">
            <i class="fa-solid fa-briefcase"></i> View Workload
        </a>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Staff Directory
        </a>
    </div>
</div>

<form action="{{ route('employees.update', $employee->id) }}" method="POST" id="employeeEditForm">
    @csrf
    @method('PUT')

    <div class="access-control-wrapper">

        <!-- SECTION 1: Staff Information & Credentials -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div class="access-section-title">
                    <i class="fa-solid fa-user-pen"></i> Staff Information & Credentials (कर्मचारी प्रोफाईल)
                </div>
                <div style="display: flex; gap: 8px;">
                    <span class="badge badge-ready">{{ $employee->employee_code }}</span>
                    <span class="badge {{ $employee->status === 'ACTIVE' ? 'badge-paid' : 'badge-danger' }}">{{ $employee->status }}</span>
                </div>
            </div>
            <div style="padding: 24px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Employee Full Name (कर्मचाऱ्याचे नाव)</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $employee->user->name) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Assigned Branch (नियुक्त शाखा)</label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $employee->branch_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->branch_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label required">Login Email Address (लॉगिन ईमेल)</label>
                        <input type="email" name="email" class="form-control" required value="{{ old('email', $employee->user->email) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password (नवीन पासवर्ड - बदल नको असल्यास रिकामा ठेवा)</label>
                        <input type="password" name="password" class="form-control" placeholder="किमान ८ अक्षरे (पर्यायी)">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Account Status (खाते स्थिती)</label>
                        <select name="status" class="form-select" required>
                            <option value="ACTIVE" {{ old('status', $employee->status) === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (सक्रिय)</option>
                            <option value="INACTIVE" {{ old('status', $employee->status) === 'INACTIVE' ? 'selected' : '' }}>INACTIVE (निष्क्रिय)</option>
                            <option value="ON_LEAVE" {{ old('status', $employee->status) === 'ON_LEAVE' ? 'selected' : '' }}>ON_LEAVE (रजेवर)</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label required">Mobile Number (मोबाईल)</label>
                        <input type="tel" name="mobile" class="form-control" required value="{{ old('mobile', $employee->user->mobile) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Designation / Role (हुद्दा)</label>
                        <input type="text" name="designation" class="form-control" required value="{{ old('designation', $employee->designation) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Joining Date (रुजू दिनांक)</label>
                        <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label">Monthly Salary (₹ मासिक वेतन)</label>
                        <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Proof Type (ओळख पुरावा)</label>
                        <select name="id_proof_type" class="form-select">
                            <option value="AADHAAR" {{ old('id_proof_type', $employee->id_proof_type) === 'AADHAAR' ? 'selected' : '' }}>Aadhaar Card</option>
                            <option value="PAN" {{ old('id_proof_type', $employee->id_proof_type) === 'PAN' ? 'selected' : '' }}>PAN Card</option>
                            <option value="VOTER" {{ old('id_proof_type', $employee->id_proof_type) === 'VOTER' ? 'selected' : '' }}>Voter ID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Proof Number (ओळख क्रमांक)</label>
                        <input type="text" name="id_proof_number" class="form-control" value="{{ old('id_proof_number', $employee->id_proof_number) }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Address (पत्ता)</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Allowed E-Seva Services (सेवा प्रवेश नियंत्रण) -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div>
                    <div class="access-section-title">
                        <i class="fa-solid fa-list-check"></i> ई-सेवा प्रवेश परवानगी (Allowed E-Seva Services Access)
                    </div>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; margin-bottom: 0;">
                        या ऑपरेटरला ज्या सेवांवर टिक (✓) असेल, तेवढ्याच सेवा त्याला फास्ट एन्ट्री, अर्ज नोंदणी आणि सेवा सूचीमध्ये दिसतील.
                    </p>
                </div>

                <div class="access-toolbar">
                    <label class="switch-control" title="कर्मचाऱ्याला केंद्रातील सर्व सेवा उपलब्ध करा">
                        <input type="checkbox" name="all_services" id="allServicesToggle" value="1" {{ $hasAllServices ? 'checked' : '' }}>
                        <span class="switch-slider"></span>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">सर्व सेवा उपलब्ध करा (Full Access)</span>
                    </label>

                    <span style="color: #cbd5e1;">|</span>

                    <button type="button" class="access-btn-xs" id="selectAllServicesBtn">
                        <i class="fa-solid fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="access-btn-xs" id="deselectAllServicesBtn">
                        <i class="fa-solid fa-xmark"></i> Deselect All
                    </button>

                    <span class="badge badge-ready" id="servicesSelectedCountBadge">सर्व सेवा ॲक्टिव्ह</span>
                </div>
            </div>

            <div id="servicesContainer" style="background: #fafafa; border-top: 1px solid #f1f5f9;">
                <div class="services-picker-grid">
                    @foreach($services as $categoryName => $catServices)
                        <div class="service-cat-divider">
                            <i class="fa-solid fa-folder-open" style="color: var(--primary-500);"></i>
                            <span>{{ $categoryName }}</span>
                            <span class="badge badge-process" style="font-size: 10px; margin-left: auto;">{{ $catServices->count() }} सेवा</span>
                        </div>

                        @foreach($catServices as $service)
                            @php
                                $isServiceChecked = $hasAllServices || in_array($service->id, $assignedServiceIds);
                            @endphp
                            <label class="service-pick-card {{ $isServiceChecked ? 'selected' : '' }}" id="svc_card_{{ $service->id }}">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="service-checkbox" {{ $isServiceChecked ? 'checked' : '' }}>
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
                                        <span class="service-pick-code">{{ $service->service_code }}</span>
                                        <span style="font-size: 11px; font-weight: 700; color: #059669;">₹{{ number_format($service->price > 0 ? $service->price : ($service->govt_fee + $service->service_charge), 0) }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- SECTION 3: System Module Permissions (सिस्टीम ॲक्सेस परवानग्या) -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div>
                    <div class="access-section-title">
                        <i class="fa-solid fa-shield-halved"></i> सिस्टीम ॲक्सेस परवानग्या (System Module Permissions)
                    </div>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; margin-bottom: 0;">
                        कर्मचाऱ्याला सिस्टीममधील कोणकोणती फीचर्स (CRM, अर्ज, पेमेंट्स, कागदपत्रे) वापरण्याची परवानगी द्यायची आहे ते निवडा.
                    </p>
                </div>

                <div class="access-toolbar">
                    <button type="button" class="access-btn-xs" id="btnPresetOperator">
                        <i class="fa-solid fa-user-check"></i> Standard Operator Preset
                    </button>
                    <button type="button" class="access-btn-xs" id="selectAllPermsBtn">
                        <i class="fa-solid fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="access-btn-xs" id="deselectAllPermsBtn">
                        <i class="fa-solid fa-xmark"></i> Deselect All
                    </button>
                </div>
            </div>

            <div class="perm-groups-container">
                @php
                    $moduleLabels = [
                        'customers' => ['icon' => 'fa-users', 'title' => 'ग्राहक व्यवस्थापन (Customers CRM)'],
                        'applications' => ['icon' => 'fa-file-signature', 'title' => 'अर्ज व प्रक्रिया (Applications Workflow)'],
                        'documents' => ['icon' => 'fa-folder-closed', 'title' => 'कागदपत्रे व्हॉल्ट (Private Document Vault)'],
                        'payments' => ['icon' => 'fa-indian-rupee-sign', 'title' => 'बिलिंग व पेमेंट्स (Billing & Payments)'],
                        'services' => ['icon' => 'fa-list-check', 'title' => 'सेवा व्यवस्थापन (Services Master)'],
                        'reports' => ['icon' => 'fa-chart-line', 'title' => 'रिपोर्ट्स सेंटर (Reports & Analytics)'],
                        'employees' => ['icon' => 'fa-user-tie', 'title' => 'कर्मचारी व्यवस्थापन (Staff & Tasks)'],
                        'branches' => ['icon' => 'fa-code-branch', 'title' => 'शाखा माहिती (Branches)'],
                        'settings' => ['icon' => 'fa-gear', 'title' => 'सिस्टीम सेटींग्ज (Settings)'],
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
                                    $isPermChecked = in_array($perm->id, $assignedPermissionIds);
                                @endphp
                                <label class="perm-checkbox-label {{ $isPermChecked ? 'checked' : '' }}">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-checkbox" data-slug="{{ $perm->slug }}" data-module="{{ $module }}" {{ $isPermChecked ? 'checked' : '' }}>
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
                <i class="fa-solid fa-xmark"></i> रद्द करा (Cancel)
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                <i class="fa-solid fa-save"></i> बदल सेव्ह करा (Save Changes)
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

    function updateServicesUI() {
        const isAll = allServicesToggle.checked;
        let selectedCount = 0;

        serviceCheckboxes.forEach(cb => {
            const card = cb.closest('.service-pick-card');
            if (isAll) {
                cb.checked = true;
                card.classList.add('selected');
            } else {
                if (cb.checked) {
                    card.classList.add('selected');
                    selectedCount++;
                } else {
                    card.classList.remove('selected');
                }
            }
        });

        if (isAll) {
            servicesCountBadge.textContent = 'सर्व सेवा उपलब्ध (All Allowed)';
            servicesCountBadge.className = 'badge badge-ready';
        } else {
            servicesCountBadge.textContent = `${selectedCount} / ${serviceCheckboxes.length} सेवा निवडल्या`;
            servicesCountBadge.className = selectedCount > 0 ? 'badge badge-process' : 'badge badge-danger';
        }
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
            cb.closest('.service-pick-card').classList.remove('selected');
        });
        updateServicesUI();
    });

    // Permissions logic
    const permCheckboxes = document.querySelectorAll('.perm-checkbox');
    const selectAllPermsBtn = document.getElementById('selectAllPermsBtn');
    const deselectAllPermsBtn = document.getElementById('deselectAllPermsBtn');
    const btnPresetOperator = document.getElementById('btnPresetOperator');

    const defaultOperatorSlugs = [
        'customers.view', 'customers.create', 'customers.edit',
        'applications.view', 'applications.create', 'applications.edit',
        'documents.view', 'documents.upload',
        'payments.view', 'payments.create',
        'services.view'
    ];

    function updatePermCheckboxesUI() {
        permCheckboxes.forEach(cb => {
            const label = cb.closest('.perm-checkbox-label');
            if (cb.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        });
    }

    permCheckboxes.forEach(cb => {
        cb.addEventListener('change', updatePermCheckboxesUI);
    });

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
