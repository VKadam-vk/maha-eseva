@extends('layouts.app')

@section('title', 'Edit Employee & Access Permissions')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Staff: {{ $employee->user->name }}</h1>
        <p class="page-subtitle">Staff profile information, system permissions, and services access control.</p>
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
                    <i class="fa-solid fa-user-pen"></i> Staff Information & Credentials
                </div>
                <div style="display: flex; gap: 8px;">
                    <span class="badge badge-ready">{{ $employee->employee_code }}</span>
                    <span class="badge {{ $employee->status === 'ACTIVE' ? 'badge-paid' : 'badge-danger' }}">{{ $employee->status }}</span>
                </div>
            </div>
            <div style="padding: 24px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Employee Full Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $employee->user->name) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Assigned Branch</label>
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
                        <label class="form-label required">Login Email Address</label>
                        <input type="email" name="email" class="form-control" required value="{{ old('email', $employee->user->email) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters (optional)">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Account Status</label>
                        <select name="status" class="form-select" required>
                            <option value="ACTIVE" {{ old('status', $employee->status) === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="INACTIVE" {{ old('status', $employee->status) === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                            <option value="ON_LEAVE" {{ old('status', $employee->status) === 'ON_LEAVE' ? 'selected' : '' }}>ON_LEAVE</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label required">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" required value="{{ old('mobile', $employee->user->mobile) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Designation / Role</label>
                        <input type="text" name="designation" class="form-control" required value="{{ old('designation', $employee->designation) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label">Monthly Salary (₹)</label>
                        <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Proof Type</label>
                        <select name="id_proof_type" class="form-select">
                            <option value="AADHAAR" {{ old('id_proof_type', $employee->id_proof_type) === 'AADHAAR' ? 'selected' : '' }}>Aadhaar Card</option>
                            <option value="PAN" {{ old('id_proof_type', $employee->id_proof_type) === 'PAN' ? 'selected' : '' }}>PAN Card</option>
                            <option value="VOTER" {{ old('id_proof_type', $employee->id_proof_type) === 'VOTER' ? 'selected' : '' }}>Voter ID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ID Proof Number</label>
                        <input type="text" name="id_proof_number" class="form-control" value="{{ old('id_proof_number', $employee->id_proof_number) }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Allowed Services -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div>
                    <div class="access-section-title">
                        <i class="fa-solid fa-list-check"></i> Allowed Services
                    </div>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; margin-bottom: 0;">
                        Assign specific services this employee is permitted to handle (e.g. PAN Card, Certificates).
                    </p>
                </div>

                <div class="access-toolbar">
                    <label class="switch-control" title="Provide employee access to all center services">
                        <input type="checkbox" name="all_services" id="allServicesToggle" value="1" {{ $hasAllServices ? 'checked' : '' }}>
                        <span class="switch-slider"></span>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Grant All Services (Full Access)</span>
                    </label>

                    <span class="badge {{ $hasAllServices ? 'badge-ready' : (count($assignedServiceIds) > 0 ? 'badge-process' : 'badge-danger') }}" id="servicesSelectedCountBadge">
                        {{ $hasAllServices ? 'All Services Allowed (Full Access)' : count($assignedServiceIds) . ' Services Selected' }}
                    </span>
                </div>
            </div>

            <div id="serviceDropdownWrapper" style="padding: 20px; background: #ffffff;">
                <div style="max-width: 680px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        Select Service to Assign
                    </label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <select id="serviceSelectDropdown" class="form-control" style="flex: 1; height: 42px; font-size: 13.5px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 6px 12px; background: #ffffff;">
                            <option value="">-- Choose Service from List --</option>
                            @foreach($services as $categoryName => $catServices)
                                <optgroup label="{{ $categoryName }}">
                                    @foreach($catServices as $service)
                                        <option value="{{ $service->id }}" 
                                                data-label="{{ $service->main_service_name }}{{ $service->sub_service_name ? ' - ' . $service->sub_service_name : '' }}">
                                            {{ $service->main_service_name }}{{ $service->sub_service_name ? ' - ' . $service->sub_service_name : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary" id="btnAddServiceFromDropdown" style="height: 42px; padding: 0 18px; font-weight: 700; white-space: nowrap;">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </div>

                    <!-- Selected Services Tags / List -->
                    <div style="margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                                Assigned Services (<span id="selectedServiceCounter">0</span>)
                            </div>
                            <button type="button" class="access-btn-xs" id="btnClearAllAssignedServices" style="color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                                <i class="fa-solid fa-trash-can"></i> Clear All
                            </button>
                        </div>
                        <div id="selectedServicesTagsBox" style="min-height: 52px; padding: 10px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs container for services[] -->
                <div id="hiddenServicesContainer">
                    @if(!$hasAllServices && !empty($assignedServiceIds))
                        @foreach($assignedServiceIds as $sId)
                            <input type="hidden" name="services[]" value="{{ $sId }}" id="hidden_svc_{{ $sId }}">
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- SECTION 3: System Module Permissions -->
        <div class="access-section-card">
            <div class="access-section-header">
                <div>
                    <div class="access-section-title">
                        <i class="fa-solid fa-shield-halved"></i> System Module Permissions
                    </div>
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; margin-bottom: 0;">
                        Configure system module access and operational privileges for this employee.
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

            <!-- QUICK ACCESS HIGH-LEVEL TOGGLE CARDS (YES / NO) -->
            <div style="padding: 16px 20px; background: #ffffff; border-bottom: 1.5px solid #e2e8f0;">
                <div style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">
                    ⚡ Quick Access Controls
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                    <!-- 1. Service Master Control -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-list-check" style="color: #6366f1;"></i> Service Master Access
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                View and edit master services and rates
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Service Master Access">
                            <input type="checkbox" id="toggleQuickServiceMaster">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 2. Branch Network Control -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-code-branch" style="color: #0ea5e9;"></i> Branch Network Access
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                View and switch between branches
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Branch Access">
                            <input type="checkbox" id="toggleQuickBranches">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 3. Billing & Payments Control -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-indian-rupee-sign" style="color: #10b981;"></i> Collections & Invoices
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                Record customer payments and receipts
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Payments Access">
                            <input type="checkbox" id="toggleQuickPayments">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 4. Reports Center Control -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-chart-line" style="color: #f59e0b;"></i> Reports & Analytics
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                View business analytics and financial reports
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Reports Access">
                            <input type="checkbox" id="toggleQuickReports">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 5. Customers CRM & Applications -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-users" style="color: #3b82f6;"></i> Customers CRM & Entries
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                Register customers and manage entries
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle CRM Access">
                            <input type="checkbox" id="toggleQuickCRM">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <!-- 6. Follow-ups & Leads -->
                    <div class="quick-toggle-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-headset" style="color: #ec4899;"></i> Follow-ups & Leads
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                Manage customer follow-ups and inquiries
                            </div>
                        </div>
                        <label class="switch-control" title="Toggle Follow-ups Access">
                            <input type="checkbox" id="toggleQuickFollowups">
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Detailed Permissions Accordion Bar -->
            <div style="padding: 10px 20px; background: #f1f5f9; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; font-weight: 700; color: #475569;">
                    <i class="fa-solid fa-sliders"></i> Detailed Permissions Breakdown
                </span>
                <button type="button" class="access-btn-xs" id="btnToggleDetailedPerms" style="background: #ffffff; border: 1px solid #cbd5e1;">
                    <i class="fa-solid fa-chevron-down" id="toggleDetailedPermsIcon"></i> <span id="toggleDetailedPermsText">Show Detailed Permissions</span>
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
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>
        </div>

    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allServicesToggle = document.getElementById('allServicesToggle');
    const serviceSelectDropdown = document.getElementById('serviceSelectDropdown');
    const btnAddService = document.getElementById('btnAddServiceFromDropdown');
    const tagsBox = document.getElementById('selectedServicesTagsBox');
    const counterSpan = document.getElementById('selectedServiceCounter');
    const badgeSpan = document.getElementById('servicesSelectedCountBadge');
    const hiddenContainer = document.getElementById('hiddenServicesContainer');
    const btnClearAll = document.getElementById('btnClearAllAssignedServices');

    // Key-value store for selected services: { id: label }
    const selectedServices = new Map();

    // Initialize from any existing hidden inputs
    if (hiddenContainer) {
        hiddenContainer.querySelectorAll('input[name="services[]"]').forEach(inp => {
            const opt = serviceSelectDropdown ? serviceSelectDropdown.querySelector(`option[value="${inp.value}"]`) : null;
            const label = opt ? opt.dataset.label : `Service #${inp.value}`;
            selectedServices.set(inp.value, label);
        });
    }

    function renderServicesUI() {
        if (!tagsBox) return;
        tagsBox.innerHTML = '';

        const isAll = allServicesToggle && allServicesToggle.checked;
        if (serviceSelectDropdown) serviceSelectDropdown.disabled = isAll;
        if (btnAddService) btnAddService.disabled = isAll;
        if (btnClearAll) btnClearAll.disabled = isAll;

        if (isAll) {
            badgeSpan.textContent = 'All Services Allowed (Full Access)';
            badgeSpan.className = 'badge badge-ready';
            counterSpan.textContent = 'All';
            tagsBox.innerHTML = '<span class="badge badge-ready" style="font-size: 11.5px; padding: 4px 10px;">All Center Services Enabled (Full Access)</span>';
            // Clear hidden inputs when full access is granted
            if (hiddenContainer) hiddenContainer.innerHTML = '';
            return;
        }

        const count = selectedServices.size;
        counterSpan.textContent = count;
        badgeSpan.textContent = `${count} Services Selected`;
        badgeSpan.className = count > 0 ? 'badge badge-process' : 'badge badge-danger';

        // Re-generate hidden inputs
        if (hiddenContainer) {
            hiddenContainer.innerHTML = '';
            selectedServices.forEach((label, id) => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'services[]';
                inp.value = id;
                hiddenContainer.appendChild(inp);
            });
        }

        if (count === 0) {
            tagsBox.innerHTML = '<span style="font-size: 12px; color: #94a3b8; font-style: italic;">No specific service selected. Choose a service from the dropdown above and click "Add".</span>';
            return;
        }

        selectedServices.forEach((label, id) => {
            const pill = document.createElement('span');
            pill.style.cssText = 'background: #eef2ff; color: #4338ca; border: 1.5px solid #c7d2fe; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;';
            pill.innerHTML = `<span>${label}</span> <i class="fa-solid fa-xmark" style="cursor: pointer; color: #6366f1; font-size: 13px;" title="Remove ${label}"></i>`;
            pill.querySelector('i').addEventListener('click', function () {
                selectedServices.delete(id);
                renderServicesUI();
            });
            tagsBox.appendChild(pill);
        });
    }

    function addSelectedService() {
        if (!serviceSelectDropdown) return;
        const val = serviceSelectDropdown.value;
        if (!val) return;

        const opt = serviceSelectDropdown.selectedOptions[0];
        const label = opt ? (opt.dataset.label || opt.text) : val;

        if (allServicesToggle && allServicesToggle.checked) {
            allServicesToggle.checked = false;
        }

        selectedServices.set(val, label);
        serviceSelectDropdown.value = '';
        renderServicesUI();
    }

    if (btnAddService) {
        btnAddService.addEventListener('click', addSelectedService);
    }

    if (serviceSelectDropdown) {
        serviceSelectDropdown.addEventListener('change', function () {
            if (this.value) {
                addSelectedService();
            }
        });
    }

    if (allServicesToggle) {
        allServicesToggle.addEventListener('change', renderServicesUI);
    }

    if (btnClearAll) {
        btnClearAll.addEventListener('click', function () {
            selectedServices.clear();
            renderServicesUI();
        });
    }

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
            toggleDetailedPermsText.textContent = isHidden ? 'Hide Detailed Permissions' : 'Show Detailed Permissions';
        });
    }

    if (selectAllPermsBtn) {
        selectAllPermsBtn.addEventListener('click', function () {
            permCheckboxes.forEach(cb => cb.checked = true);
            updatePermCheckboxesUI();
        });
    }

    if (deselectAllPermsBtn) {
        deselectAllPermsBtn.addEventListener('click', function () {
            permCheckboxes.forEach(cb => cb.checked = false);
            updatePermCheckboxesUI();
        });
    }

    if (btnPresetOperator) {
        btnPresetOperator.addEventListener('click', function () {
            permCheckboxes.forEach(cb => {
                const slug = cb.getAttribute('data-slug');
                cb.checked = defaultOperatorSlugs.includes(slug);
            });
            updatePermCheckboxesUI();
        });
    }

    document.querySelectorAll('.btn-toggle-module').forEach(btn => {
        btn.addEventListener('click', function () {
            const module = this.getAttribute('data-module');
            const modCheckboxes = document.querySelectorAll(`.perm-checkbox[data-module="${module}"]`);
            const allChecked = Array.from(modCheckboxes).every(cb => cb.checked);
            modCheckboxes.forEach(cb => cb.checked = !allChecked);
            updatePermCheckboxesUI();
        });
    });

    renderServicesUI();
    updatePermCheckboxesUI();
});
</script>
@endpush
@endsection
