@extends('layouts.app')

@section('title', 'Branch Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Branch Operations Dashboard</h1>
        <p class="page-subtitle">Daily counter metrics, pending tasks and customer footfall for your assigned branch</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn" onclick="openCashModal('RECEIVE')" style="background: #10b981; color: #ffffff; font-weight: 700; border: none; padding: 7px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 2px 4px rgba(16,185,129,0.25); cursor: pointer;">
            <i class="fa-solid fa-circle-plus"></i> Receive RS
        </button>
        <button type="button" class="btn" onclick="openCashModal('SPEND')" style="background: #ef4444; color: #ffffff; font-weight: 700; border: none; padding: 7px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 2px 4px rgba(239,68,68,0.25); cursor: pointer;">
            <i class="fa-solid fa-circle-minus"></i> Spend RS
        </button>
        <a href="{{ route('fast_entry.index') }}" class="btn btn-indigo">
            <i class="fa-solid fa-table-cells"></i> Fast Daily Entry
        </a>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card saffron">
        <div>
            <div class="metric-label">Today's Collections (Net)</div>
            <div class="metric-value">₹{{ number_format($todayNetCollection ?? $todayCollection, 2) }}</div>
            <div class="metric-subtext" style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; font-size: 11.5px;">
                <span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-receipt"></i> Srv: ₹{{ number_format($todayCollection, 2) }}</span>
                @if(($todayExtraReceived ?? 0) > 0)
                <span style="color: #10b981; font-weight: 700;">+ ₹{{ number_format($todayExtraReceived, 2) }}</span>
                @endif
                @if(($todaySpent ?? 0) > 0)
                <span style="color: #ef4444; font-weight: 700;">- ₹{{ number_format($todaySpent, 2) }}</span>
                @endif
            </div>
            <div class="metric-subtext" style="margin-top: 3px;"><span>All-time Net: ₹{{ number_format($netCollection ?? $totalCollection, 2) }}</span></div>
        </div>
        <div class="metric-icon-box saffron"><i class="fa-solid fa-calendar-day fa-lg"></i></div>
    </div>

    <div class="metric-card amber" style="border-left: 4px solid #f59e0b;">
        <div>
            <div class="metric-label" style="color: #92400e; font-weight: 700;">Receivable / Pending Bal</div>
            <div class="metric-value" style="color: #b45309;">₹{{ number_format($pendingAmount, 2) }}</div>
            <div class="metric-subtext"><span>Uncollected balance from applications</span></div>
        </div>
        <div class="metric-icon-box amber" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-hand-holding-dollar fa-lg"></i></div>
    </div>

    <div class="metric-card amber">
        <div>
            <div class="metric-label">Pending Work</div>
            <div class="metric-value">{{ $pendingWork }}</div>
            <div class="metric-subtext"><span>Requires operator processing</span></div>
        </div>
        <div class="metric-icon-box amber"><i class="fa-solid fa-clock-rotate-left fa-lg"></i></div>
    </div>

    <div class="metric-card emerald">
        <div>
            <div class="metric-label">Completed Applications</div>
            <div class="metric-value">{{ $completedWork }}</div>
            <div class="metric-subtext"><span>Delivered / Ready for pickup</span></div>
        </div>
        <div class="metric-icon-box emerald"><i class="fa-solid fa-circle-check fa-lg"></i></div>
    </div>
</div>

<div style="background: #ffffff; border: 1px solid var(--surface-border); border-radius: 10px; padding: 12px 18px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; font-size: 13.5px;">
        <div><span style="color: var(--text-muted); font-weight: 500;">All-Time Branch Collections (Net):</span> <strong style="color: #166534; font-weight: 700;">₹{{ number_format($netCollection ?? $totalCollection, 2) }}</strong></div>
        <div style="color: #cbd5e1;">|</div>
        <div><span style="color: var(--text-muted); font-weight: 500;">Total Work Billed:</span> <strong style="color: #4338ca; font-weight: 700;">₹{{ number_format($totalBilled, 2) }}</strong></div>
        <div style="color: #cbd5e1;">|</div>
        <div><span style="color: var(--text-muted); font-weight: 500;">Today's Counter Entries:</span> <strong style="color: #0f172a; font-weight: 700;">{{ $todayApplications }} Applications</strong></div>
    </div>
    <div>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm" style="font-size: 12px;">
            <i class="fa-solid fa-receipt"></i> Collections Ledger
        </a>
    </div>
</div>

@if($dueFollowUps->isNotEmpty())
    <div class="erp-card" style="border-left: 4px solid #f59e0b;">
        <div class="erp-card-header" style="background-color: #fffbeb;">
            <h3 class="erp-card-title" style="color: #92400e;">
                <i class="fa-solid fa-bell"></i> Pending Reminders Due Today ({{ $dueFollowUps->count() }})
            </h3>
            <a href="{{ route('followups.index') }}" class="btn btn-secondary btn-sm">All Reminders</a>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Reason</th>
                        <th>Follow-up Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dueFollowUps as $f)
                        <tr>
                            <td><strong>{{ $f->customer?->name ?? 'N/A' }}</strong></td>
                            <td>{{ $f->customer?->mobile }}</td>
                            <td>{{ $f->reason }}</td>
                            <td>{{ $f->follow_up_date->format('d M Y') }} {{ $f->follow_up_time }}</td>
                            <td>
                                <form action="{{ route('followups.status', $f->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="COMPLETED">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fa-solid fa-check"></i> Complete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-list-check"></i> Recent Applications at Branch</h3>
        <a href="{{ route('applications.index') }}" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>SR Number</th>
                    <th>Customer Name</th>
                    <th>Service</th>
                    <th>Assigned Operator</th>
                    <th>Work Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentApplications as $app)
                    <tr>
                        <td>
                            <span style="font-family: var(--font-mono); font-weight: 700; color: #1e293b;">
                                {{ $app->application_number }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $app->customer->name }}</strong>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $app->customer->mobile }}</div>
                        </td>
                        <td>{{ $app->service->main_service_name }} ({{ $app->service->sub_service_name }})</td>
                        <td>{{ $app->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                        <td><span class="badge badge-process">{{ $app->work_status }}</span></td>
                        <td><span class="badge badge-paid">{{ $app->payment_status }}</span></td>
                        <td>
                            <a href="{{ route('applications.receipt', $app->id) }}" target="_blank" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-print"></i> Receipt
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('dashboard.partials.cash_modal')
@endsection
