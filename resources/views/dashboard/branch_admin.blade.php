@extends('layouts.app')

@section('title', 'Branch Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Branch Operations Dashboard</h1>
        <p class="page-subtitle">Daily counter metrics, pending tasks and customer footfall for your assigned branch</p>
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

<div class="metrics-grid">
    <div class="metric-card saffron">
        <div>
            <div class="metric-label">Today's Collections</div>
            <div class="metric-value">₹{{ number_format($todayCollection, 2) }}</div>
            <div class="metric-subtext"><span>Cash & UPI receipts today</span></div>
        </div>
        <div class="metric-icon-box saffron"><i class="fa-solid fa-indian-rupee-sign fa-lg"></i></div>
    </div>

    <div class="metric-card indigo">
        <div>
            <div class="metric-label">Today's Applications</div>
            <div class="metric-value">{{ $todayApplications }}</div>
            <div class="metric-subtext"><span>Registered at counter</span></div>
        </div>
        <div class="metric-icon-box indigo"><i class="fa-solid fa-file-signature fa-lg"></i></div>
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

@if($dueFollowUps->isNotEmpty())
    <div class="erp-card" style="border-left: 4px solid #f59e0b;">
        <div class="erp-card-header" style="background-color: #fffbeb;">
            <h3 class="erp-card-title" style="color: #92400e;">
                <i class="fa-solid fa-bell"></i> Pending Follow-ups Due Today ({{ $dueFollowUps->count() }})
            </h3>
            <a href="{{ route('followups.index') }}" class="btn btn-secondary btn-sm">All Follow-ups</a>
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
                            <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                {{ $app->application_number }}
                            </a>
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
                            <a href="{{ route('applications.show', $app->id) }}" class="btn btn-secondary btn-sm">View</a>
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
@endsection
