@extends('layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title">{{ $invoice->invoice_number }}</h1>
            <span class="badge {{ $invoice->status === 'PAID' ? 'badge-paid' : 'badge-pending' }}">{{ $invoice->status }}</span>
        </div>
        <p class="page-subtitle">Date: {{ $invoice->invoice_date->format('d M Y') }} • Customer: {{ $invoice->customer->name }}</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-secondary">
            <i class="fa-solid fa-print"></i> Print Invoice
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> All Invoices
        </a>
    </div>
</div>

<div class="form-grid-2">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-user"></i> Bill To</h3>
        </div>
        <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
            <div><strong>Name:</strong> {{ $invoice->customer->name }}</div>
            <div><strong>Mobile:</strong> {{ $invoice->customer->mobile }}</div>
            <div><strong>Email:</strong> {{ $invoice->customer->email ?? '-' }}</div>
            <div><strong>Address:</strong> {{ $invoice->customer->address ?? '-' }} ({{ $invoice->customer->city }})</div>
        </div>
    </div>

    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-building"></i> Issued By</h3>
        </div>
        <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
            <div><strong>Organization:</strong> {{ $invoice->branch->tenant->name ?? 'Maha E-Seva Kendra' }}</div>
            <div><strong>Branch:</strong> {{ $invoice->branch->name }}</div>
            <div><strong>GSTIN:</strong> {{ $invoice->branch->tenant->gst_number ?? '27ABCDE1234F1Z5' }}</div>
            <div><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Immediate' }}</div>
        </div>
    </div>
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-list-ol"></i> Itemized Services</h3>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>GST Tax</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td><strong>{{ $item->description }}</strong></td>
                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->tax_percentage }}%</td>
                        <td><strong>₹{{ number_format($item->total_price, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: 700;">Subtotal:</td>
                    <td>₹{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: 700;">Tax Amount:</td>
                    <td>₹{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700; color: #dc2626;">Discount:</td>
                        <td style="color: #dc2626;">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr style="background-color: #f8fafc; font-size: 15px;">
                    <td colspan="4" style="text-align: right; font-weight: 800;">Grand Total:</td>
                    <td><strong style="color: #1e293b;">₹{{ number_format($invoice->total_amount, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: 700; color: #166534;">Amount Paid:</td>
                    <td style="color: #166534; font-weight: 700;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: 700; color: #dc2626;">Balance Due:</td>
                    <td style="color: #dc2626; font-weight: 700;">₹{{ number_format($invoice->balance_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
