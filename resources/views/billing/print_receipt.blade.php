<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #000; margin: 0; padding: 10px; background-color: #f1f5f9; }
        .receipt-container { max-width: 80mm; margin: auto; background: #fff; padding: 15px; border: 1px dashed #94a3b8; font-size: 12px; }
        .center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .flex-between { display: flex; justify-content: space-between; margin: 4px 0; }
        .bold { font-weight: bold; }
        .print-btn { display: block; width: 100%; max-width: 80mm; margin: 10px auto; padding: 8px; background: #ea580c; color: #fff; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; font-family: sans-serif; }
        @media print {
            body { background: #fff; padding: 0; }
            .print-btn { display: none; }
            .receipt-container { border: none; padding: 0; width: 80mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Thermal Receipt (80mm)</button>
    <div class="receipt-container">
        <div class="center">
            <h3 style="margin: 0; font-size: 15px;">{{ $payment->branch->tenant->name ?? 'MAHA E-SEVA KENDRA' }}</h3>
            <div>{{ $payment->branch->name }}</div>
            <div>Ph: {{ $payment->branch->contact_mobile ?? '9876543210' }}</div>
            <div style="font-size: 11px;">GSTIN: {{ $payment->branch->tenant->gst_number ?? '27ABCDE1234F1Z5' }}</div>
        </div>

        <div class="divider"></div>
        <div class="center bold" style="font-size: 13px;">PAYMENT RECEIPT</div>
        <div class="divider"></div>

        <div class="flex-between">
            <span>Receipt No:</span>
            <span class="bold">{{ $payment->receipt_number }}</span>
        </div>
        <div class="flex-between">
            <span>Date:</span>
            <span>{{ $payment->payment_date->format('d/m/Y') }}</span>
        </div>
        <div class="flex-between">
            <span>Customer:</span>
            <span class="bold">{{ $payment->customer->name }}</span>
        </div>
        <div class="flex-between">
            <span>Mobile:</span>
            <span>{{ $payment->customer->mobile }}</span>
        </div>

        @if($payment->application)
            <div class="divider"></div>
            <div class="flex-between">
                <span>Application SR:</span>
                <span class="bold">{{ $payment->application->application_number }}</span>
            </div>
            <div class="flex-between">
                <span>Service:</span>
                <span>{{ $payment->application->service->main_service_name }}</span>
            </div>
            <div class="flex-between">
                <span>Total Fee:</span>
                <span>Rs. {{ number_format($payment->application->total_amount, 2) }}</span>
            </div>
        @endif

        <div class="divider"></div>
        <div class="flex-between" style="font-size: 14px;">
            <span class="bold">AMOUNT PAID:</span>
            <span class="bold">Rs. {{ number_format($payment->amount, 2) }}</span>
        </div>
        <div class="flex-between">
            <span>Payment Mode:</span>
            <span class="bold">{{ $payment->payment_mode }}</span>
        </div>
        @if($payment->transaction_reference)
            <div class="flex-between">
                <span>Ref / UTR:</span>
                <span>{{ $payment->transaction_reference }}</span>
            </div>
        @endif

        @if($payment->application)
            <div class="flex-between">
                <span>Remaining Bal:</span>
                <span class="bold">Rs. {{ number_format($payment->application->remaining_amount, 2) }}</span>
            </div>
        @endif

        <div class="divider"></div>
        <div class="center" style="font-size: 11px;">
            Track status online at:<br>
            <strong>{{ url('/track') }}</strong><br>
            <br>
            Cashier: {{ $payment->receiver?->name ?? 'Staff' }}<br>
            * Thank You For Visiting *
        </div>
    </div>
</body>
</html>
