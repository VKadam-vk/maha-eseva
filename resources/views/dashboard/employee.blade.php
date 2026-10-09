@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Operator Workstation Dashboard</h1>
        <p class="page-subtitle">Your active assigned applications, document queues, and due tasks</p>
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
    <div class="metric-card indigo">
        <div>
            <div class="metric-label">Assigned Today</div>
            <div class="metric-value">{{ $assignedToday }}</div>
            <div class="metric-subtext"><span>Newly assigned to you</span></div>
        </div>
        <div class="metric-icon-box indigo"><i class="fa-solid fa-inbox fa-lg"></i></div>
    </div>

    <div class="metric-card amber">
        <div>
            <div class="metric-label">In Process</div>
            <div class="metric-value">{{ $inProcess }}</div>
            <div class="metric-subtext"><span>Processing on portal</span></div>
        </div>
        <div class="metric-icon-box amber"><i class="fa-solid fa-spinner fa-lg"></i></div>
    </div>

    <div class="metric-card rose">
        <div>
            <div class="metric-label">Due / Overdue</div>
            <div class="metric-value">{{ $dueToday + $overdue }}</div>
            <div class="metric-subtext"><span style="color: #ef4444;">{{ $overdue }} overdue items</span></div>
        </div>
        <div class="metric-icon-box rose"><i class="fa-solid fa-clock-rotate-left fa-lg"></i></div>
    </div>

    <div class="metric-card emerald">
        <div>
            <div class="metric-label">Completed</div>
            <div class="metric-value">{{ $completed }}</div>
            <div class="metric-subtext"><span>Approved / Delivered</span></div>
        </div>
        <div class="metric-icon-box emerald"><i class="fa-solid fa-circle-check fa-lg"></i></div>
    </div>
</div>

<!-- My Assigned Applications -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-list-check"></i> My Active Processing Queue</h3>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>SR Number</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Service</th>
                    <th>Due Date</th>
                    <th>Work Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $app)
                    <tr>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">
                                {{ $app->application_number }}
                            </a>
                        </td>
                        <td><strong>{{ $app->customer->name }}</strong></td>
                        <td>{{ $app->customer->mobile }}</td>
                        <td>{{ $app->service->main_service_name }}</td>
                        <td>
                            @if($app->due_date && $app->due_date->isPast())
                                <span style="color: #dc2626; font-weight: 700;"><i class="fa-solid fa-triangle-exclamation"></i> {{ $app->due_date->format('d M Y') }} (Overdue)</span>
                            @elseif($app->due_date)
                                <span>{{ $app->due_date->format('d M Y') }}</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td><span class="badge badge-process">{{ $app->work_status }}</span></td>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" class="btn btn-secondary btn-sm">Process Work <i class="fa-solid fa-arrow-right"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 24px; margin-bottom: 8px; display: block;"></i>
                            Your task queue is all clear! Great job.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
