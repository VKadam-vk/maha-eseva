@extends('layouts.app')

@section('title', 'Customer Register Report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Citizen & Customer Register Report</h1>
        <p class="page-subtitle">Exportable customer directory with registration dates and branch distribution</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-primary">
            <i class="fa-solid fa-file-excel"></i> Export Customer CSV
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Reports Hub
        </a>
    </div>
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-users"></i> Registered Citizens ({{ $totalCustomers }} Total)</h3>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Customer Code</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Gender</th>
                    <th>City / District</th>
                    <th>Branch</th>
                    <th>Total Apps</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td><span style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">{{ $c->customer_code }}</span></td>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->mobile }}</td>
                        <td>{{ $c->gender }}</td>
                        <td>{{ $c->city ?? 'Pune' }}, {{ $c->district ?? 'Pune' }}</td>
                        <td>{{ $c->branch->name }}</td>
                        <td><span class="badge badge-process">{{ $c->applications_count }}</span></td>
                        <td>{{ $c->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
