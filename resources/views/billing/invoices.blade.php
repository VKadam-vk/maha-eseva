@extends('layouts.app')

@section('title', 'Invoices Master')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tax Invoices & Billing</h1>
        <p class="page-subtitle">Manage itemized customer bills, GST invoices and balance records</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Create Invoice
    </a>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('invoices.show', $inv->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td>
                            <strong>{{ $inv->customer->name }}</strong>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $inv->customer->mobile }}</div>
                        </td>
                        <td>₹{{ number_format($inv->subtotal, 2) }}</td>
                        <td>₹{{ number_format($inv->tax_amount, 2) }}</td>
                        <td><strong>₹{{ number_format($inv->total_amount, 2) }}</strong></td>
                        <td style="color: #166534; font-weight: 700;">₹{{ number_format($inv->paid_amount, 2) }}</td>
                        <td style="color: {{ $inv->balance_amount > 0 ? '#dc2626' : '#166534' }};">
                            ₹{{ number_format($inv->balance_amount, 2) }}
                        </td>
                        <td>
                            @php
                                $invBadge = match($inv->status) {
                                    'PAID' => 'badge-paid',
                                    'PARTIAL' => 'badge-partial',
                                    default => 'badge-pending'
                                };
                            @endphp
                            <span class="badge {{ $invBadge }}">{{ $inv->status }}</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-secondary btn-sm" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Print Invoice">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 32px; color: var(--text-muted);">No invoices created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
