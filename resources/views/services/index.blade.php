@extends('layouts.app')

@section('title', 'Services Master')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Services Master & Dynamic Catalogs</h1>
        <p class="page-subtitle">Configure Maharashtra citizen services, government fees, and dynamic form fields</p>
    </div>
    <a href="{{ route('services.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Service
    </a>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('services.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 250px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search Service Name</label>
                <input type="text" name="search" class="form-control" placeholder="e.g. PAN CARD, Rent Agreement, Domicile..." value="{{ request('search') }}">
            </div>

            @if(isset($mainServices) && count($mainServices) > 0)
            <div style="min-width: 220px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Main Service Filter</label>
                <select name="main_service" class="form-select">
                    <option value="">All Main Services</option>
                    @foreach($mainServices as $ms)
                        <option value="{{ $ms }}" {{ request('main_service') == $ms ? 'selected' : '' }}>{{ $ms }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Services Grid / Table -->
<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Main Service</th>
                    <th>Sub-Service / Variant</th>
                    <th>Govt Fee</th>
                    <th>Service Charge</th>
                    <th>Total Price</th>
                    <th>Processing Days</th>
                    <th>Operator Links</th>
                    <th>Custom Fields</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $s)
                    <tr>
                        <td>
                            <strong style="color: #0f172a; font-size: 14.5px;">{{ $s->main_service_name }}</strong>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #4338ca;">{{ $s->sub_service_name }}</span>
                            @if($s->service_variant)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $s->service_variant }}</div>
                            @endif
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
                            <span class="badge badge-process">{{ $s->customFields->count() }} Fields</span>
                        </td>
                        <td>
                            <form action="{{ route('services.toggle_status', $s->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="badge {{ $s->is_active ? 'badge-paid' : 'badge-pending' }}" style="cursor: pointer; border: none;">
                                    {{ $s->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <a href="{{ route('services.edit', $s->id) }}" class="btn btn-secondary btn-sm" title="Edit Sub-Service" style="padding: 4px 8px;">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <a href="{{ route('services.show', $s->id) }}" class="btn btn-secondary btn-sm" title="Configure Fields" style="padding: 4px 8px;">
                                    <i class="fa-solid fa-gear"></i>
                                </a>
                                <a href="{{ route('applications.create', ['service_id' => $s->id]) }}" class="btn btn-primary btn-sm" title="Apply Service" style="padding: 4px 8px;">
                                    <i class="fa-solid fa-plus"></i> Apply
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 24px; color: var(--text-muted);">No services found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($services->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $services->links() }}
        </div>
    @endif
</div>
@endsection
