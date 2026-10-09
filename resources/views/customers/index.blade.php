@extends('layouts.app')

@section('title', 'Customers CRM')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Customers CRM & Directory</h1>
        <p class="page-subtitle">Search, manage and create customer profiles with duplicate protection</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('import_export.import.customers') }}" class="btn btn-secondary">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </a>
        <a href="{{ route('reports.customers', ['export' => 1]) }}" class="btn btn-secondary">
            <i class="fa-solid fa-file-export"></i> Export CSV
        </a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Quick Add Customer
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('customers.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 250px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search Name, Mobile, Code or Email</label>
                <input type="text" name="search" class="form-control" placeholder="Type customer name or mobile..." value="{{ request('search') }}">
            </div>

            @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
                <div style="min-width: 180px;">
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customer Table -->
<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Customer Code</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Gender</th>
                    <th>City / Pincode</th>
                    <th>Category</th>
                    <th>Applications</th>
                    <th>Total Spent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $c->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                {{ $c->customer_code }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);">{{ $c->name }}</div>
                            @if($c->email)
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ $c->email }}</div>
                            @endif
                        </td>
                        <td>
                            <strong style="font-family: var(--font-mono);">{{ $c->mobile }}</strong>
                            @if($c->alternate_mobile)
                                <div style="font-size: 11.5px; color: var(--text-muted);">Alt: {{ $c->alternate_mobile }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-hold" style="font-size: 10px;">{{ $c->gender }}</span></td>
                        <td>{{ $c->city ?? '-' }} {{ $c->pincode ? "({$c->pincode})" : '' }}</td>
                        <td>{{ $c->category?->name ?? 'Regular' }}</td>
                        <td><span class="badge badge-process">{{ $c->applications_count }} Apps</span></td>
                        <td><strong>₹{{ number_format($c->total_paid, 2) }}</strong></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('customers.show', $c->id) }}" class="btn btn-secondary btn-sm" title="View 360 Profile">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                <a href="{{ route('applications.create', ['customer_id' => $c->id]) }}" class="btn btn-primary btn-sm" title="Add Service">
                                    <i class="fa-solid fa-plus"></i> Service
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                            No customers found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
