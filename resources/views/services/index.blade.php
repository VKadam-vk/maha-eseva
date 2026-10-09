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

<!-- Category Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('services.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 250px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search Service Name or Code</label>
                <input type="text" name="search" class="form-control" placeholder="e.g. PAN CARD, Rent Agreement, Domicile..." value="{{ request('search') }}">
            </div>

            <div style="min-width: 220px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Service Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Service Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

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
                    <th>Service Code</th>
                    <th>Category</th>
                    <th>Main Service</th>
                    <th>Sub-Service / Variant</th>
                    <th>Govt Fee</th>
                    <th>Service Charge</th>
                    <th>Total Price</th>
                    <th>Processing Days</th>
                    <th>Custom Fields</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $s)
                    <tr>
                        <td><span style="font-family: var(--font-mono); font-weight: 700; color: #4f46e5;">{{ $s->service_code }}</span></td>
                        <td><span class="badge badge-ready">{{ $s->category->name }}</span></td>
                        <td><strong>{{ $s->main_service_name }}</strong></td>
                        <td>
                            {{ $s->sub_service_name }}
                            @if($s->service_variant)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $s->service_variant }}</div>
                            @endif
                        </td>
                        <td>₹{{ number_format($s->govt_fee, 2) }}</td>
                        <td>₹{{ number_format($s->service_charge, 2) }}</td>
                        <td><strong style="color: #166534; font-size: 14px;">₹{{ number_format($s->price, 2) }}</strong></td>
                        <td>{{ $s->expected_processing_days }} Days</td>
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
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('services.show', $s->id) }}" class="btn btn-secondary btn-sm" title="Configure Fields">
                                    <i class="fa-solid fa-gear"></i> Configure
                                </a>
                                <a href="{{ route('applications.create', ['service_id' => $s->id]) }}" class="btn btn-primary btn-sm" title="Apply Service">
                                    <i class="fa-solid fa-plus"></i> Apply
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 24px; color: var(--text-muted);">No services found.</td>
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
