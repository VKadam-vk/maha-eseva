@extends('layouts.app')

@section('title', 'Branch Management')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Branch Network Management</h1>
        <p class="page-subtitle">Configure regional digital service centers, contact heads and counters</p>
    </div>
    @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
        <a href="{{ route('branches.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add New Branch
        </a>
    @endif
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Branch Code</th>
                    <th>Branch Name</th>
                    <th>Location / City</th>
                    <th>Contact Person</th>
                    <th>Mobile</th>
                    <th>Operators</th>
                    <th>Customers</th>
                    <th>Applications</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $b)
                    <tr>
                        <td><span style="font-family: var(--font-mono); font-weight: 700; color: #4f46e5;">{{ $b->branch_code }}</span></td>
                        <td>
                            <strong>{{ $b->name }}</strong>
                            @if($b->is_main_branch)
                                <span class="badge badge-ready" style="font-size: 10px; margin-left: 6px;">Headquarters</span>
                            @endif
                        </td>
                        <td>{{ $b->city ?? 'Pune' }}</td>
                        <td>{{ $b->contact_person ?? '-' }}</td>
                        <td>{{ $b->contact_mobile ?? '-' }}</td>
                        <td><span class="badge badge-process">{{ $b->employees_count }} Staff</span></td>
                        <td><strong>{{ $b->customers_count }}</strong></td>
                        <td><strong>{{ $b->applications_count }}</strong></td>
                        <td><span class="badge {{ $b->status === 'ACTIVE' ? 'badge-paid' : 'badge-pending' }}">{{ $b->status }}</span></td>
                        <td>
                            @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
                                <a href="{{ route('branches.edit', $b->id) }}" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                            @else
                                <span style="font-size: 12px; color: var(--text-muted);">View only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 24px; color: var(--text-muted);">No branches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
