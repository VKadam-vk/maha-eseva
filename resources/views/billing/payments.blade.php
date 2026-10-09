@extends('layouts.app')

@section('title', 'Collections & Receipts Ledger')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Collections & Payments Ledger</h1>
        <p class="page-subtitle">Complete transaction journal, cash/UPI reconciliation and printable receipts</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('reports.collections') }}" class="btn btn-secondary">
            <i class="fa-solid fa-chart-pie"></i> Revenue Analytics
        </a>
    </div>
</div>

<!-- Financial Summary KPIs -->
<div class="metrics-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 20px;">
    <div class="metric-card saffron">
        <div>
            <div class="metric-label">All-Time Collections</div>
            <div class="metric-value">₹{{ number_format($totalCollected, 2) }}</div>
            <div class="metric-subtext"><span>Verified successful receipts</span></div>
        </div>
        <div class="metric-icon-box saffron"><i class="fa-solid fa-indian-rupee-sign fa-lg"></i></div>
    </div>

    <div class="metric-card emerald">
        <div>
            <div class="metric-label">Today's Collections</div>
            <div class="metric-value">₹{{ number_format($todayCollected, 2) }}</div>
            <div class="metric-subtext"><span>Counter receipts for {{ date('d M Y') }}</span></div>
        </div>
        <div class="metric-icon-box emerald"><i class="fa-solid fa-calendar-day fa-lg"></i></div>
    </div>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('payments.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search Receipt, Customer, UTR</label>
                <input type="text" name="search" class="form-control" placeholder="Receipt no, customer, UTR..." value="{{ request('search') }}">
            </div>

            <div style="min-width: 150px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                    <option value="">All Modes</option>
                    <option value="CASH" {{ request('payment_mode') == 'CASH' ? 'selected' : '' }}>CASH</option>
                    <option value="UPI" {{ request('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI / QR</option>
                    <option value="BANK_TRANSFER" {{ request('payment_mode') == 'BANK_TRANSFER' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="CARD" {{ request('payment_mode') == 'CARD' ? 'selected' : '' }}>Card</option>
                    <option value="CHEQUE" {{ request('payment_mode') == 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <div style="min-width: 140px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Payments Ledger Table -->
<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Payment Date</th>
                    <th>Customer Name</th>
                    <th>Application SR</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Transaction Ref / UTR</th>
                    <th>Received By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>
                            <a href="{{ route('payments.receipt', $p->id) }}" target="_blank" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                {{ $p->receipt_number }}
                            </a>
                        </td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('customers.show', $p->customer_id) }}" style="font-weight: 700; color: var(--text-main);">
                                {{ $p->customer->name }}
                            </a>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $p->customer->mobile }}</div>
                        </td>
                        <td>
                            @if($p->application)
                                <a href="{{ route('applications.show', $p->application_id) }}" style="font-family: var(--font-mono); font-weight: 600; color: #4f46e5;">
                                    {{ $p->application->application_number }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">Direct Advance</span>
                            @endif
                        </td>
                        <td><strong style="color: #166534; font-size: 14px;">₹{{ number_format($p->amount, 2) }}</strong></td>
                        <td><span class="badge badge-ready">{{ $p->payment_mode }}</span></td>
                        <td><span style="font-family: var(--font-mono); font-size: 12px;">{{ $p->transaction_reference ?? '-' }}</span></td>
                        <td>{{ $p->receiver?->name ?? 'Staff' }}</td>
                        <td>
                            <span class="badge {{ $p->payment_status === 'SUCCESS' ? 'badge-paid' : 'badge-pending' }}">{{ $p->payment_status }}</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('payments.receipt', $p->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Print Receipt">
                                    <i class="fa-solid fa-print"></i> Receipt
                                </a>
                                @if($p->payment_status === 'SUCCESS' && (Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner()))
                                    <button type="button" class="btn btn-danger btn-sm" onclick="openRefundModal({{ $p->id }}, '{{ $p->receipt_number }}', {{ $p->amount }})" title="Refund">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 32px; color: var(--text-muted);">No payment records found.</td>
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

<!-- Refund Modal -->
<div id="refundModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-rotate-left"></i> Process Payment Refund</h3>
            <button type="button" onclick="closeModal('refundModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="refundForm" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size: 13.5px; margin-bottom: 16px;">
                    Refunding receipt: <strong id="refund-receipt-no"></strong> (Paid Amount: <strong id="refund-paid-amount"></strong>)
                </p>

                <div class="form-group">
                    <label class="form-label required">Refund Amount (₹)</label>
                    <input type="number" step="0.01" id="refund-amount-input" name="refund_amount" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Reason for Refund</label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Government service rejected / Duplicate payment made">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('refundModal')">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-rotate-left"></i> Confirm Refund</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openRefundModal(paymentId, receiptNo, amount) {
    document.getElementById('refundForm').action = `/billing/payments/${paymentId}/refund`;
    document.getElementById('refund-receipt-no').textContent = receiptNo;
    document.getElementById('refund-paid-amount').textContent = '₹' + amount.toFixed(2);
    document.getElementById('refund-amount-input').value = amount;
    document.getElementById('refund-amount-input').max = amount;
    document.getElementById('refundModal').classList.add('open');
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
@endpush
@endsection
