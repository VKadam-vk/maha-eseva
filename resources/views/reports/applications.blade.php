@extends('layouts.app')

@section('title', 'Applications Report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Applications & Service Metrics Report</h1>
        <p class="page-subtitle">Detailed service request log with financial aggregates and CSV export</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-primary">
            <i class="fa-solid fa-file-excel"></i> Export Applications CSV
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Reports Hub
        </a>
    </div>
</div>

<!-- Summary Strip -->
<div class="metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="metric-card indigo" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Volume</div>
            <div class="metric-value" style="font-size: 22px;">{{ $summary['total'] }}</div>
        </div>
    </div>
    <div class="metric-card saffron" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Billing</div>
            <div class="metric-value" style="font-size: 22px;">₹{{ number_format($summary['total_amount'], 2) }}</div>
        </div>
    </div>
    <div class="metric-card emerald" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Realized</div>
            <div class="metric-value" style="font-size: 22px; color: #166534;">₹{{ number_format($summary['total_received'], 2) }}</div>
        </div>
    </div>
    <div class="metric-card amber" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Remaining</div>
            <div class="metric-value" style="font-size: 22px; color: #dc2626;">₹{{ number_format($summary['total_remaining'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('reports.applications') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Service</label>
                <select name="service_id" class="form-select">
                    <option value="">All Services</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>{{ $s->main_service_name }}</option>
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
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
                <a href="{{ route('reports.applications') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>SR Number</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Service</th>
                    <th>Branch</th>
                    <th>Operator</th>
                    <th>Total</th>
                    <th>Received</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">
                                {{ $app->application_number }}
                            </a>
                        </td>
                        <td>{{ $app->application_date->format('d M Y') }}</td>
                        <td><strong>{{ $app->customer->name }}</strong></td>
                        <td>{{ $app->customer->mobile }}</td>
                        <td>{{ $app->service->main_service_name }}</td>
                        <td>{{ $app->branch->name }}</td>
                        <td>{{ $app->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                        <td>₹{{ number_format($app->total_amount, 2) }}</td>
                        <td style="color: #166534; font-weight: 700;">₹{{ number_format($app->received_amount, 2) }}</td>
                        <td><span class="badge badge-process">{{ $app->work_status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 24px; color: var(--text-muted);">No records found.</td>
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
