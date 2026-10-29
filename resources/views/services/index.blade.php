@extends('layouts.app')

@section('title', 'Services Master')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Services Master & Dynamic Catalogs</h1>
        <p class="page-subtitle">Configure Maharashtra citizen services, government fees, and dynamic form fields</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('services.create') }}" class="btn btn-primary" style="box-shadow: 0 2px 6px rgba(79,70,229,0.3);">
            <i class="fa-solid fa-plus"></i> Add New Service
        </a>
    </div>
</div>

<!-- Filters & Summary Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('services.index') }}" method="GET" id="servicesFilterForm" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 250px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px; font-weight: 700; color: #475569;">Search Service or Sub-Service</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" id="serviceSearchInput" class="form-control"
                           placeholder="Type main service or sub-service name (e.g. Adhar, Mobile, Pan)..."
                           value="{{ request('search') }}"
                           oninput="autoFilterServices(this.value)" autocomplete="off"
                           style="padding-left: 34px;">
                </div>
            </div>

            @if(isset($mainServices) && count($mainServices) > 0)
            <div style="min-width: 220px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px; font-weight: 700; color: #475569;">Filter by Main Service</label>
                <select name="main_service" id="mainServiceSelect" class="form-select" onchange="this.form.submit()">
                    <option value="">All Main Services ({{ $mainServices->count() }})</option>
                    @foreach($mainServices as $ms)
                        <option value="{{ $ms }}" {{ request('main_service') == $ms ? 'selected' : '' }}>{{ $ms }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('services.index') }}" class="btn btn-secondary" title="Clear Filters">Reset</a>
            </div>

            <div style="margin-left: auto; display: flex; align-items: center; gap: 12px; font-size: 13px; color: #64748b;">
                <span style="background: #f1f5f9; padding: 6px 12px; border-radius: 6px; font-weight: 600; border: 1px solid #e2e8f0;">
                    <i class="fa-solid fa-layer-group" style="color: #4f46e5; margin-right: 4px;"></i>
                    {{ $groupedServices->count() }} Main Services
                </span>
                <span style="background: #ecfdf5; color: #065f46; padding: 6px 12px; border-radius: 6px; font-weight: 600; border: 1px solid #a7f3d0;">
                    <i class="fa-solid fa-sitemap" style="color: #059669; margin-right: 4px;"></i>
                    {{ $services->count() }} Total Sub-Services
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Grouped Services Table -->
<div class="erp-card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
    <div class="table-responsive">
        <table class="erp-table" id="servicesTable" style="margin-bottom: 0;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding-left: 20px; width: 300px;">Sub-Service / Variant</th>
                    <th style="width: 110px;">Govt Fee</th>
                    <th style="width: 120px;">Service Charge</th>
                    <th style="width: 120px;">Total Price</th>
                    <th style="width: 120px;">Processing Days</th>
                    <th>Operator Links</th>
                    <th style="width: 120px;">Custom Fields</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 110px; text-align: right; padding-right: 20px;">Action</th>
                </tr>
            </thead>
            <tbody id="servicesTableBody">
                @forelse($groupedServices as $mainServiceName => $subList)
                    @php
                        $groupKey = \Illuminate\Support\Str::slug($mainServiceName ?: 'general');
                    @endphp

                    <!-- Main Service Group Header Row -->
                    <tr class="main-service-group-row" 
                        data-main-key="{{ $groupKey }}" 
                        data-main-service="{{ strtolower(trim($mainServiceName)) }}"
                        onclick="toggleGroup('{{ $groupKey }}')"
                        style="cursor: pointer; user-select: none;">
                        <td colspan="9" style="background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 100%); padding: 10px 18px; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="background: #4f46e5; color: #ffffff; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; box-shadow: 0 2px 4px rgba(79,70,229,0.25);">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </span>
                                    <span style="font-weight: 800; font-size: 14.5px; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase;">
                                        {{ $mainServiceName }}
                                    </span>
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 11px; padding: 3px 9px; border-radius: 20px; border: 1px solid #c7d2fe;">
                                        {{ $subList->count() }} {{ $subList->count() === 1 ? 'Sub-Service' : 'Sub-Services' }}
                                    </span>
                                    <i class="fa-solid fa-chevron-down" id="toggle-icon-{{ $groupKey }}" 
                                       style="color: #64748b; font-size: 12px; margin-left: 6px; transition: transform 0.2s;" title="Click to collapse / expand"></i>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;" onclick="event.stopPropagation();">
                                    <a href="{{ route('services.create', ['main_service' => $mainServiceName]) }}" 
                                       class="btn btn-sm btn-secondary" 
                                       style="font-size: 11.5px; padding: 4px 10px; background: #ffffff; color: #4338ca; border: 1px solid #c7d2fe; font-weight: 600;" 
                                       title="Add new sub-service directly under {{ $mainServiceName }}">
                                        <i class="fa-solid fa-plus"></i> Add Sub-Service
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Nested Sub-Services Rows for this Main Service -->
                    @foreach($subList as $s)
                        <tr class="service-table-row sub-row-{{ $groupKey }}" 
                            data-main-key="{{ $groupKey }}"
                            data-main-service="{{ strtolower(trim($mainServiceName)) }}"
                            data-search="{{ strtolower(trim($mainServiceName . ' ' . $s->sub_service_name . ' ' . $s->service_variant)) }}">
                            <td style="padding-left: 28px;">
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <span style="color: #6366f1; font-size: 14px; font-weight: 800; margin-top: 1px;">↳</span>
                                    <div>
                                        <span style="font-weight: 700; color: #1e293b; font-size: 13.5px;">{{ $s->sub_service_name }}</span>
                                        @if($s->service_variant)
                                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $s->service_variant }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>₹{{ number_format($s->govt_fee, 2) }}</td>
                            <td>₹{{ number_format($s->service_charge, 2) }}</td>
                            <td><strong style="color: #166534; font-size: 14px;">₹{{ number_format($s->price, 2) }}</strong></td>
                            <td>{{ $s->expected_processing_days }} Days</td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                    @if($s->work_portal_link || $s->service_portal_link)
                                        <a href="{{ $s->work_portal_link ?: $s->service_portal_link }}" target="_blank" 
                                           class="btn btn-sm btn-secondary" style="font-size: 11px; padding: 4px 8px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;"
                                           title="Open Government Work Portal">
                                            <i class="fa-solid fa-globe"></i> Work Portal
                                        </a>
                                    @endif
                                    @if($s->video_instruction_link)
                                        <a href="{{ $s->video_instruction_link }}" target="_blank" 
                                           class="btn btn-sm btn-secondary" style="font-size: 11px; padding: 4px 8px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;"
                                           title="Watch Tutorial Video">
                                            <i class="fa-brands fa-youtube"></i> Tutorial
                                        </a>
                                    @endif
                                    @if(!$s->work_portal_link && !$s->service_portal_link && !$s->video_instruction_link)
                                        <span style="color: #94a3b8; font-size: 11px;">No links</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-process" style="font-size: 11px; padding: 3px 8px;">{{ $s->customFields->count() }} Fields</span>
                            </td>
                            <td>
                                <form action="{{ route('services.toggle_status', $s->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="badge {{ $s->is_active ? 'badge-paid' : 'badge-pending' }}" style="cursor: pointer; border: none; font-size: 11px; padding: 4px 8px;">
                                        {{ $s->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: right; padding-right: 20px;">
                                <a href="{{ route('services.edit', $s->id) }}" class="btn btn-secondary btn-sm" title="Edit Sub-Service" style="padding: 4px 10px; font-size: 12px;">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr id="emptyServicesRow">
                        <td colspan="9" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            <i class="fa-solid fa-folder-open" style="font-size: 28px; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                            No services found. Click "Add New Service" above to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
let searchDebounceTimer = null;

// Real-time client side filter
function autoFilterServices(val) {
    const query = (val || '').toLowerCase().trim();
    const groupRows = document.querySelectorAll('tr.main-service-group-row');
    const subRows = document.querySelectorAll('tr.service-table-row');
    
    if (!query) {
        groupRows.forEach(gr => gr.style.display = '');
        subRows.forEach(sr => sr.style.display = '');
        return;
    }

    const groupMatches = {};

    // Filter sub-service rows
    subRows.forEach(row => {
        const searchText = row.dataset.search || '';
        const groupKey = row.dataset.mainKey || '';
        const isMatch = searchText.includes(query);

        if (isMatch) {
            row.style.display = '';
            groupMatches[groupKey] = true;
        } else {
            row.style.display = 'none';
        }
    });

    // Control visibility of group headers
    groupRows.forEach(header => {
        const groupKey = header.dataset.mainKey || '';
        const mainServiceName = (header.dataset.mainService || '').toLowerCase();
        
        // If the main service title matches query, reveal the header and all its sub-services
        if (mainServiceName.includes(query)) {
            header.style.display = '';
            document.querySelectorAll(`.sub-row-${groupKey}`).forEach(sr => sr.style.display = '');
        } else if (groupMatches[groupKey]) {
            header.style.display = '';
        } else {
            header.style.display = 'none';
        }
    });

    // Debounced server filter for exact DB searches if needed
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        const form = document.getElementById('servicesFilterForm');
        if (form && query.length >= 3) {
            form.submit();
        } else if (form && query.length === 0 && '{{ request("search") }}'.length > 0) {
            form.submit();
        }
    }, 800);
}

// Expand / Collapse group
function toggleGroup(groupKey) {
    const subRows = document.querySelectorAll(`.sub-row-${groupKey}`);
    const icon = document.getElementById(`toggle-icon-${groupKey}`);
    if (!subRows.length) return;

    const isCurrentlyHidden = (subRows[0].style.display === 'none');
    
    subRows.forEach(r => {
        r.style.display = isCurrentlyHidden ? '' : 'none';
    });

    if (icon) {
        icon.style.transform = isCurrentlyHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
    }
}
</script>
@endpush
@endsection
