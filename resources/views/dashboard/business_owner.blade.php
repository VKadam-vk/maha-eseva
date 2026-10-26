@extends('layouts.app')

@section('title', 'Business Owner Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Maha E-Seva Executive Operations</h1>
        <p class="page-subtitle">Centralized multi-branch performance, collections and application tracking</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('fast_entry.index') }}" class="btn btn-indigo">
            <i class="fa-solid fa-table-cells"></i> Fast Daily Entry
        </a>
        <a href="{{ route('applications.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Application
        </a>
    </div>
</div>

<!-- Primary Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card saffron">
        <div>
            <div class="metric-label">Total Revenue Collected</div>
            <div class="metric-value">₹{{ number_format($totalRevenue, 2) }}</div>
            <div class="metric-subtext">
                <i class="fa-solid fa-arrow-trend-up" style="color: #10b981;"></i>
                <span>₹{{ number_format($todayRevenue, 2) }} collected today</span>
            </div>
        </div>
        <div class="metric-icon-box saffron">
            <i class="fa-solid fa-indian-rupee-sign fa-lg"></i>
        </div>
    </div>

    <div class="metric-card indigo">
        <div>
            <div class="metric-label">Total Applications</div>
            <div class="metric-value">{{ number_format($totalApplications) }}</div>
            <div class="metric-subtext">
                <i class="fa-solid fa-bolt" style="color: #4f46e5;"></i>
                <span>{{ $todayApplications }} registered today</span>
            </div>
        </div>
        <div class="metric-icon-box indigo">
            <i class="fa-solid fa-file-signature fa-lg"></i>
        </div>
    </div>

    <div class="metric-card amber">
        <div>
            <div class="metric-label">In-Process / Pending</div>
            <div class="metric-value">{{ number_format($pendingApplications) }}</div>
            <div class="metric-subtext">
                <i class="fa-solid fa-clock" style="color: #f59e0b;"></i>
                <span>Active work in queue</span>
            </div>
        </div>
        <div class="metric-icon-box amber">
            <i class="fa-solid fa-hourglass-half fa-lg"></i>
        </div>
    </div>

    <div class="metric-card emerald">
        <div>
            <div class="metric-label">Total Customers</div>
            <div class="metric-value">{{ number_format($totalCustomers) }}</div>
            <div class="metric-subtext">
                <i class="fa-solid fa-user-plus" style="color: #10b981;"></i>
                <span>{{ $todayCustomers }} new customers today</span>
            </div>
        </div>
        <div class="metric-icon-box emerald">
            <i class="fa-solid fa-users fa-lg"></i>
        </div>
    </div>
</div>

<!-- Top High-Demand Services Card -->
<div class="erp-card" style="margin-bottom: 24px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-ranking-star"></i> Top Demand Services</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">Services Master</a>
    </div>
    <div class="erp-card-body" style="padding: 16px 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px;">
            @forelse($topServices as $ts)
                <div style="display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--surface-border); border-radius: 8px; padding: 12px 16px; background: #f8fafc;">
                    <div>
                        <div style="font-size: 14px; font-weight: 700;">{{ $ts->main_service_name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $ts->sub_service_name }}</div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-process">{{ $ts->app_count }} Requests</span>
                        <div style="font-size: 12px; font-weight: 600; color: #166534; margin-top: 2px;">₹{{ number_format($ts->total_revenue, 2) }}</div>
                    </div>
                </div>
            @empty
                <p style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 20px 0;">No application data recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Branches Overview Card -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-code-branch"></i> Branch Network Performance</h3>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary btn-sm">Manage Branches</a>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Branch Code</th>
                    <th>Branch Name</th>
                    <th>City / Location</th>
                    <th>Contact Person</th>
                    <th>Total Customers</th>
                    <th>Total Applications</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchPerformance as $b)
                    <tr>
                        <td><span style="font-family: var(--font-mono); font-weight: 700; color: #4f46e5;">{{ $b->branch_code }}</span></td>
                        <td>
                            <strong>{{ $b->name }}</strong>
                            @if($b->is_main_branch)
                                <span class="badge badge-ready" style="font-size: 10px; margin-left: 6px;">HQ Main</span>
                            @endif
                        </td>
                        <td>{{ $b->city ?? 'Pune' }}</td>
                        <td>{{ $b->contact_person }} ({{ $b->contact_mobile }})</td>
                        <td><strong>{{ $b->customers_count }}</strong></td>
                        <td><strong>{{ $b->applications_count }}</strong></td>
                        <td><span class="badge badge-paid">{{ $b->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
