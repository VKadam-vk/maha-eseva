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
        <form id="customerFilterForm" action="{{ route('customers.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 250px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                    <span>Search Name, Mobile, Code or Email</span>
                    <span id="custSearchIndicator" style="display: none; font-size: 11px; color: #4f46e5; font-weight: 700;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i> Filtering...
                    </span>
                </label>
                <div style="position: relative;">
                    <input type="text" name="search" id="customerSearchInput" class="form-control" 
                           placeholder="Type customer name or mobile (auto-searches)..." 
                           value="{{ request('search') }}"
                           oninput="autoFilterCustomers(this.value)"
                           autocomplete="off"
                           style="padding-right: 32px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 13px;"></i>
                </div>
            </div>

            @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
                <div style="min-width: 180px;">
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Branch</label>
                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Category</label>
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
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
                    <th>Customer (Name, Mobile, Code)</th>
                    <th>Gender</th>
                    <th>Category</th>
                    <th>Applications</th>
                    <th>Total Spent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td style="padding: 10px 14px;">
                            <!-- 1. Customer Name + Badges -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('customers.show', $c->id) }}" style="font-weight: 800; color: var(--text-main); text-transform: uppercase; text-decoration: none; font-size: 13.5px; line-height: 1.25;">
                                    {{ $c->name }}
                                </a>
                                @if($c->is_blacklisted)
                                    <span style="font-size: 10px; color: #dc2626; background: #fee2e2; border: 1px solid #f87171; padding: 1px 6px; border-radius: 4px; font-weight: 800;">
                                        <i class="fa-solid fa-ban"></i> BLACKLISTED
                                    </span>
                                @endif
                                @if($c->star_rating)
                                    <span style="font-size: 11px; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 1px 5px; border-radius: 4px; font-weight: 700;">
                                        <i class="fa-solid fa-star" style="color: #f59e0b;"></i> {{ $c->star_rating }}★
                                    </span>
                                @endif
                            </div>

                            <!-- 2. Mobile Name / Number + Alt -->
                            <div style="margin-top: 4px; font-size: 12.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <strong style="font-family: var(--font-mono); color: #0f172a;">
                                    <i class="fa-solid fa-phone" style="font-size: 10.5px; color: #64748b; margin-right: 3px;"></i>{{ $c->mobile }}
                                </strong>
                                @if($c->alternate_mobile)
                                    <span style="font-size: 11px; color: var(--text-muted);">(Alt: {{ $c->alternate_mobile }})</span>
                                @endif
                            </div>

                            <!-- 3. Customer Code + Occupation / Email -->
                            <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px; font-size: 11.5px;">
                                <a href="{{ route('customers.show', $c->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                    {{ $c->customer_code }}
                                </a>
                                @if($c->occupation)
                                    <span style="background: #f1f5f9; padding: 1px 6px; border-radius: 3px; font-weight: 600; color: #475569; font-size: 10.5px;">
                                        {{ strtoupper($c->occupation) }}
                                    </span>
                                @endif
                                @if($c->email)
                                    <span style="color: var(--text-muted); font-size: 11px;">&bull; {{ $c->email }}</span>
                                @endif
                            </div>
                        </td>
                        <td><span class="badge badge-hold" style="font-size: 10px;">{{ $c->gender }}</span></td>
                        <td>{{ $c->category?->name ?? 'Regular' }}</td>
                        <td><span class="badge badge-process">{{ $c->applications_count }} Apps</span></td>
                        <td><strong>₹{{ number_format($c->total_paid, 2) }}</strong></td>
                        <td>
                            <a href="{{ route('customers.show', $c->id) }}" class="btn btn-secondary btn-sm" title="View 360 Profile">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
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

@push('scripts')
<script>
let custSearchTimer = null;

function autoFilterCustomers(query) {
    const q = (query || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.erp-table tbody tr');
    const indicator = document.getElementById('custSearchIndicator');

    if (indicator) indicator.style.display = 'inline-block';

    // 1. Instant client-side DOM filter on current rows
    let visibleCount = 0;
    rows.forEach(row => {
        // Skip empty state row if present
        if (row.querySelector('td[colspan]')) return;
        const text = row.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // 2. Debounced auto-submit to query server/pagination across full database
    clearTimeout(custSearchTimer);
    custSearchTimer = setTimeout(() => {
        const form = document.getElementById('customerFilterForm');
        if (form) {
            form.submit();
        }
    }, 600);
}
</script>
@endpush
