<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; margin: 0; padding: 30px; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; }
        .header-table, .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { background: #f1f5f9; text-align: left; padding: 10px; font-size: 12px; border-bottom: 2px solid #cbd5e1; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .brand-title { font-size: 22px; font-weight: 800; color: #ea580c; }
        .total-row td { font-weight: bold; }
        .print-btn { background: #ea580c; color: #fff; border: none; padding: 10px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; margin-bottom: 20px; }
        @media print { .print-btn { display: none; } .invoice-box { border: none; padding: 0; } }
    </style>
</head>
<body>
    <div style="text-align: right; max-width: 800px; margin: auto;">
        <button class="print-btn" onclick="window.print()">Print Invoice (A4)</button>
    </div>
    <div class="invoice-box">
        <table class="header-table" style="margin-bottom: 24px;">
            <tr>
                <td>
                    <div class="brand-title">{{ $invoice->branch->tenant->name ?? 'MAHA E-SEVA KENDRA' }}</div>
                    <div style="font-size: 13px; color: #64748b;">
                        {{ $invoice->branch->address ?? 'Shivaji Commercial Complex, FC Road, Pune' }}<br>
                        Phone: {{ $invoice->branch->contact_mobile ?? '9876543210' }} | Email: {{ $invoice->branch->contact_email ?? 'admin@mahaeseva.com' }}<br>
                        GSTIN: <strong>{{ $invoice->branch->tenant->gst_number ?? '27ABCDE1234F1Z5' }}</strong>
                    </div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <h2 style="margin: 0; color: #0f172a;">TAX INVOICE</h2>
                    <div style="font-size: 13px; margin-top: 6px;">
                        Invoice No: <strong>{{ $invoice->invoice_number }}</strong><br>
                        Date: <strong>{{ $invoice->invoice_date->format('d M Y') }}</strong><br>
                        Status: <strong>{{ $invoice->status }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        <div style="background: #f8fafc; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 13px;">
            <strong>Billed To:</strong><br>
            {{ $invoice->customer->name }} (Customer Code: {{ $invoice->customer->customer_code }})<br>
            Mobile: {{ $invoice->customer->mobile }} | City: {{ $invoice->customer->city ?? 'Pune' }}
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Service Description</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">GST Rate</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $item->description }}</strong></td>
                        <td style="text-align: right;">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ $item->tax_percentage }}%</td>
                        <td style="text-align: right;"><strong>₹{{ number_format($item->total_price, 2) }}</strong></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">Subtotal:</td>
                    <td style="text-align: right;">₹{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">Total GST Tax:</td>
                    <td style="text-align: right;">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right; color: #dc2626;">Discount:</td>
                        <td style="text-align: right; color: #dc2626;">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row" style="font-size: 15px; background: #f8fafc;">
                    <td colspan="5" style="text-align: right;">Grand Total:</td>
                    <td style="text-align: right;">₹{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr class="total-row" style="color: #166534;">
                    <td colspan="5" style="text-align: right;">Paid Amount:</td>
                    <td style="text-align: right;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr class="total-row" style="color: #dc2626;">
                    <td colspan="5" style="text-align: right;">Balance Due:</td>
                    <td style="text-align: right;">₹{{ number_format($invoice->balance_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 36px; display: flex; justify-content: space-between; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 16px;">
            <div>
                <strong>Terms & Conditions:</strong><br>
                1. Computer generated tax invoice issued by authorized Maha E-Seva Kendra.<br>
                2. Government statutory fees are non-refundable once processed on portal.
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <br><br>
                _______________________<br>
                <strong>Authorized Signatory</strong>
            </div>
        </div>
    </div>
</body>
</html>
