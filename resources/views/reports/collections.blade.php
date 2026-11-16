@extends('layouts.app')

@section('title', 'Collections Report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Collections & Revenue Reconciliation Report</h1>
        <p class="page-subtitle">Reconciliation of Cash, UPI, and Bank collections by payment mode</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Reports Hub
    </a>
</div>

<!-- Mode Breakdown Cards -->
<div class="metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 20px;">
    <div class="metric-card emerald">
        <div>
            <div class="metric-label">Total Realized Revenue (जमा)</div>
            <div class="metric-value">₹{{ number_format($totalRevenue, 2) }}</div>
            <div class="metric-subtext"><span>Verified collections for branch</span></div>
        </div>
        <div class="metric-icon-box emerald"><i class="fa-solid fa-circle-check fa-lg"></i></div>
    </div>

    <div class="metric-card amber" style="border-left: 4px solid #f59e0b;">
        <div>
            <div class="metric-label" style="color: #92400e; font-weight: 700;">Receivable / Pending (येणे बाकी)</div>
            <div class="metric-value" style="color: #b45309;">₹{{ number_format($totalPendingReceivable, 2) }}</div>
            <div class="metric-subtext"><span>Uncollected balance from applications</span></div>
        </div>
        <div class="metric-icon-box amber" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-hand-holding-dollar fa-lg"></i></div>
    </div>

    <div class="metric-card indigo">
        <div>
            <div class="metric-label">Total Work Billed (एकूण दर)</div>
            <div class="metric-value">₹{{ number_format($totalBilled, 2) }}</div>
            <div class="metric-subtext"><span>Gross application fee value</span></div>
        </div>
        <div class="metric-icon-box indigo"><i class="fa-solid fa-file-invoice-dollar fa-lg"></i></div>
    </div>

    @foreach($modeBreakdown as $mb)
        <div class="metric-card saffron">
            <div>
                <div class="metric-label">{{ $mb->payment_mode }} Collections</div>
                <div class="metric-value">₹{{ number_format($mb->total, 2) }}</div>
                <div class="metric-subtext"><span>{{ $mb->count }} Receipts</span></div>
            </div>
            <div class="metric-icon-box saffron"><i class="fa-solid fa-money-bill-wave fa-lg"></i></div>
        </div>
    @endforeach
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-receipt"></i> Receipts Log</h3>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Service / Reason</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>UTR Reference</th>
                    <th>Cashier</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>
                            <a href="{{ route('payments.receipt', $p->id) }}" target="_blank" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">
                                {{ $p->receipt_number }}
                            </a>
                        </td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td>{{ $p->customer->name }}</td>
                        <td>{{ $p->customer->mobile }}</td>
                        <td>{{ $p->application?->service?->main_service_name ?? 'Counter Advance' }}</td>
                        <td>
                            <strong style="color: #166534;">₹{{ number_format($p->amount, 2) }}</strong>
                            @if($p->application && $p->application->remaining_amount > 0)
                                <div style="font-size: 11px; color: #b45309; font-weight: 700; margin-top: 2px;"><i class="fa-solid fa-clock-rotate-left"></i> Bal: ₹{{ number_format($p->application->remaining_amount, 2) }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-ready">{{ $p->payment_mode }}</span></td>
                        <td><span style="font-family: var(--font-mono); font-size: 11.5px;">{{ $p->transaction_reference ?? '-' }}</span></td>
                        <td>{{ $p->receiver?->name ?? 'Staff' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 24px; color: var(--text-muted);">No payment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
