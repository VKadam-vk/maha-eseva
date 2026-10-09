@extends('layouts.app')

@section('title', 'Staff & Operators')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Staff & Operator Directory</h1>
        <p class="page-subtitle">Manage service counter operators, branch assignments and performance</p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-user-plus"></i> Add New Employee
    </a>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Name</th>
                    <th>Email / Login</th>
                    <th>Mobile</th>
                    <th>Branch</th>
                    <th>Allowed Services</th>
                    <th>Permissions</th>
                    <th>Active Apps</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td><span style="font-family: var(--font-mono); font-weight: 700; color: #4f46e5;">{{ $emp->employee_code }}</span></td>
                        <td>
                            <strong>{{ $emp->user->name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $emp->designation }}</div>
                        </td>
                        <td>{{ $emp->user->email }}</td>
                        <td>{{ $emp->user->mobile }}</td>
                        <td><span class="badge badge-ready">{{ $emp->branch->name }}</span></td>
                        <td>
                            @if($emp->services->count() > 0)
                                <span class="badge badge-process" title="{{ $emp->services->pluck('main_service_name')->join(', ') }}">
                                    <i class="fa-solid fa-list-check"></i> {{ $emp->services->count() }} Services
                                </span>
                            @else
                                <span class="badge badge-ready">
                                    <i class="fa-solid fa-circle-check"></i> All Services
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($emp->user->permissions->count() > 0)
                                <span class="badge badge-process">
                                    <i class="fa-solid fa-shield-halved"></i> {{ $emp->user->permissions->count() }} Custom
                                </span>
                            @else
                                <span class="badge badge-ready">
                                    <i class="fa-solid fa-shield"></i> Default Role
                                </span>
                            @endif
                        </td>
                        <td><span class="badge badge-process">{{ $emp->applications_count }} Apps</span></td>
                        <td><span class="badge {{ $emp->status === 'ACTIVE' ? 'badge-paid' : 'badge-danger' }}">{{ $emp->status }}</span></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('employees.edit', $emp->id) }}" class="btn btn-outline-primary btn-sm" title="Edit Permissions & Services">
                                    <i class="fa-solid fa-user-gear"></i> Edit Access
                                </a>
                                <a href="{{ route('employees.show', $emp->id) }}" class="btn btn-secondary btn-sm" title="Workload & Tasks">
                                    <i class="fa-solid fa-eye"></i> Workload
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 24px; color: var(--text-muted);">No employees registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
