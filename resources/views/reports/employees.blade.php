@extends('layouts.app')

@section('title', 'Operator Performance Report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Staff & Operator Productivity Report</h1>
        <p class="page-subtitle">Evaluation of service completion speed, resolution rates, and pending backlog</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Reports Hub
    </a>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Operator Name</th>
                    <th>Branch</th>
                    <th>Designation</th>
                    <th>Total Assigned</th>
                    <th>Completed</th>
                    <th>Pending</th>
                    <th>Resolution Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                    @php
                        $rate = $emp->applications_count > 0 ? round(($emp->completed_count / $emp->applications_count) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><span style="font-family: var(--font-mono); font-weight: 700; color: #4f46e5;">{{ $emp->employee_code }}</span></td>
                        <td><strong>{{ $emp->user->name }}</strong></td>
                        <td>{{ $emp->branch->name }}</td>
                        <td>{{ $emp->designation }}</td>
                        <td><strong>{{ $emp->applications_count }}</strong></td>
                        <td><span style="color: #166534; font-weight: 700;">{{ $emp->completed_count }}</span></td>
                        <td><span style="color: #dc2626; font-weight: 700;">{{ $emp->pending_count }}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex-grow: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: {{ $rate }}%; height: 100%; background: #10b981;"></div>
                                </div>
                                <span style="font-weight: 700; font-size: 12.5px;">{{ $rate }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
