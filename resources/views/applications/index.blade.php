@extends('layouts.app')

@section('title', 'Applications Master')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Applications & Service Requests</h1>
        <p class="page-subtitle">Track, assign, process and deliver citizen service requests across all branches</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('reports.applications', ['export' => 1]) }}" class="btn btn-secondary">
            <i class="fa-solid fa-file-export"></i> Export CSV
        </a>
        <a href="{{ route('fast_entry.index') }}" class="btn btn-indigo">
            <i class="fa-solid fa-table-cells"></i> Fast Daily Entry
        </a>
        <a href="{{ route('applications.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Application
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('applications.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search SR / Customer / Ack</label>
                <input type="text" name="search" class="form-control" placeholder="SR number, mobile, name..." value="{{ request('search') }}">
            </div>

            <div style="min-width: 160px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Work Status</label>
                <select name="work_status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('work_status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 150px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="">All Payment</option>
                    <option value="PAID" {{ request('payment_status') == 'PAID' ? 'selected' : '' }}>PAID</option>
                    <option value="PARTIAL" {{ request('payment_status') == 'PARTIAL' ? 'selected' : '' }}>PARTIAL</option>
                    <option value="PENDING" {{ request('payment_status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                </select>
            </div>

            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Service</label>
                <select name="service_id" class="form-select">
                    <option value="">All Services</option>
                    @foreach($services as $srv)
                        <option value="{{ $srv->id }}" {{ request('service_id') == $srv->id ? 'selected' : '' }}>{{ $srv->main_service_name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 140px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <div style="min-width: 140px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('applications.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>SR Number</th>
                    <th>Application Date</th>
                    <th>Customer Name</th>
                    <th>Service</th>
                    <th>Branch</th>
                    <th>Operator</th>
                    <th>Total</th>
                    <th>Received</th>
                    <th>Work Status</th>
                    <th>Payment</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                {{ $app->application_number }}
                            </a>
                            @if($app->external_acknowledgement_no)
                                <div style="font-size: 11px; font-family: var(--font-mono); color: var(--text-muted);">Ack: {{ $app->external_acknowledgement_no }}</div>
                            @endif
                        </td>
                        <td>{{ $app->application_date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('customers.show', $app->customer_id) }}" style="font-weight: 700; color: var(--text-main); text-decoration: none;">
                                {{ $app->customer->name }}
                            </a>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $app->customer->mobile }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $app->service->main_service_name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $app->service->sub_service_name }}</div>
                        </td>
                        <td><span style="font-size: 12px;">{{ $app->branch->name }}</span></td>
                        <td>{{ $app->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                        <td><strong>₹{{ number_format($app->total_amount, 2) }}</strong></td>
                        <td>
                            <span style="color: #166534; font-weight: 700;">₹{{ number_format($app->received_amount, 2) }}</span>
                            @if($app->remaining_amount > 0)
                                <div style="font-size: 10.5px; color: #dc2626;">Bal: ₹{{ number_format($app->remaining_amount, 2) }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($app->work_status) {
                                    'NEW' => 'badge-new',
                                    'APPROVED', 'READY' => 'badge-ready',
                                    'DELIVERED', 'CLOSED' => 'badge-delivered',
                                    'DOCUMENT_PENDING' => 'badge-pending',
                                    'REJECTED', 'CANCELLED' => 'badge-rejected',
                                    default => 'badge-process'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $app->work_status }}</span>
                        </td>
                        <td>
                            @php
                                $payClass = match($app->payment_status) {
                                    'PAID' => 'badge-paid',
                                    'PARTIAL' => 'badge-partial',
                                    default => 'badge-pending'
                                };
                            @endphp
                            <span class="badge {{ $payClass }}">{{ $app->payment_status }}</span>
                        </td>
                        <td>
                            @if($app->due_date)
                                <span style="font-size: 12px; {{ $app->due_date->isPast() && !in_array($app->work_status, ['DELIVERED', 'CLOSED', 'APPROVED']) ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                    {{ $app->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" class="btn btn-secondary btn-sm">
                                View <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            <i class="fa-solid fa-folder-open" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                            No applications found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($applications->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
