@extends('layouts.app')

@section('title', 'Reports Center')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Executive Reports & Business Intelligence</h1>
        <p class="page-subtitle">Exportable analytics, customer registers, service metrics and collections reconciliation</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
    <div class="erp-card" style="margin-bottom: 0;">
        <div class="erp-card-body">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 6px;">Applications Report</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Detailed breakdown of application throughput, processing times, service charges and completion rates.</p>
            <a href="{{ route('reports.applications') }}" class="btn btn-primary btn-sm" style="width: 100%;">
                <i class="fa-solid fa-arrow-right"></i> Open Report
            </a>
        </div>
    </div>

    <div class="erp-card" style="margin-bottom: 0;">
        <div class="erp-card-body">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 6px;">Collections & Revenue</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Reconciliation of Cash, UPI, and Bank collections with date filters and cashier breakdowns.</p>
            <a href="{{ route('reports.collections') }}" class="btn btn-primary btn-sm" style="width: 100%;">
                <i class="fa-solid fa-arrow-right"></i> Open Report
            </a>
        </div>
    </div>

    <div class="erp-card" style="margin-bottom: 0;">
        <div class="erp-card-body">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 6px;">Customer Register</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Comprehensive citizen directory with demographics, category distribution and total service spending.</p>
            <a href="{{ route('reports.customers') }}" class="btn btn-primary btn-sm" style="width: 100%;">
                <i class="fa-solid fa-arrow-right"></i> Open Report
            </a>
        </div>
    </div>

    <div class="erp-card" style="margin-bottom: 0;">
        <div class="erp-card-body">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #fdf4ff; color: #c026d3; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 6px;">Staff Productivity</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Operator performance comparison, speed of service, pending queues and workload distribution.</p>
            <a href="{{ route('reports.employees') }}" class="btn btn-primary btn-sm" style="width: 100%;">
                <i class="fa-solid fa-arrow-right"></i> Open Report
            </a>
        </div>
    </div>
</div>
@endsection
