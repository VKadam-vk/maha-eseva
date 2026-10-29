<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $application->application_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #0f172a;
            margin: 0;
            padding: 16px;
            background-color: #f1f5f9;
        }
        .receipt-container {
            max-width: 80mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 16px 14px;
            border: 1px dashed #94a3b8;
            font-size: 12px;
            line-height: 1.4;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .center {
            text-align: center;
        }
        .divider {
            border-top: 1px dashed #334155;
            margin: 8px 0;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 4px 0;
        }
        .bold {
            font-weight: 800;
        }
        .print-btn-bar {
            max-width: 80mm;
            margin: 0 auto 12px auto;
            display: flex;
            gap: 8px;
        }
        .print-btn {
            flex: 1;
            padding: 10px;
            background: #16a34a;
            color: #ffffff;
            border: none;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            border-radius: 6px;
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .close-btn {
            padding: 10px 14px;
            background: #64748b;
            color: #ffffff;
            border: none;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            border-radius: 6px;
            font-family: sans-serif;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-btn-bar {
                display: none !important;
            }
            .receipt-container {
                border: none;
                padding: 0;
                width: 80mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <button class="print-btn" onclick="window.print()">
            🖨️ Print Receipt (पावती प्रिंट करा)
        </button>
        <button class="close-btn" onclick="window.close()">Close</button>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="center">
            <h3 style="margin: 0; font-size: 15px; text-transform: uppercase;">
                {{ $application->branch->tenant->name ?? 'MAHA E-SEVA KENDRA' }}
            </h3>
            <div style="font-weight: 700; font-size: 11.5px; margin-top: 2px;">
                {{ $application->branch->name ?? 'Counter Service Center' }}
            </div>
            @if($application->branch->contact_mobile)
                <div style="font-size: 11px;">Ph: {{ $application->branch->contact_mobile }}</div>
            @endif
            @if(!empty($application->branch->tenant->gst_number))
                <div style="font-size: 10.5px;">GSTIN: {{ $application->branch->tenant->gst_number }}</div>
            @endif
        </div>

        <div class="divider"></div>
        <div class="center bold" style="font-size: 13px; letter-spacing: 0.5px;">
            SERVICE & PAYMENT RECEIPT<br>
            <span style="font-size: 11px; font-weight: normal;">(सेवा व पेमेंट पावती)</span>
        </div>
        <div class="divider"></div>

        <!-- Meta -->
        <div class="flex-between">
            <span>SR Number:</span>
            <span class="bold">{{ $application->application_number }}</span>
        </div>
        <div class="flex-between">
            <span>Date & Time:</span>
            <span>{{ $application->created_at ? $application->created_at->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</span>
        </div>
        <div class="flex-between">
            <span>Customer:</span>
            <span class="bold" style="text-transform: uppercase;">{{ $application->customer->name ?? '-' }}</span>
        </div>
        <div class="flex-between">
            <span>Mobile No:</span>
            <span class="bold">{{ $application->customer->mobile ?? '-' }}</span>
        </div>
        @if($application->customer?->customer_code)
            <div class="flex-between">
                <span>Customer Code:</span>
                <span>{{ $application->customer->customer_code }}</span>
            </div>
        @endif

        <div class="divider"></div>
        <!-- Service Details -->
        <div class="flex-between">
            <span>Service:</span>
            <span class="bold" style="text-align: right; max-width: 55%;">{{ $application->service?->main_service_name ?? 'Government Service' }}</span>
        </div>
        @if($application->sub_service)
            <div class="flex-between">
                <span>Sub-Service:</span>
                <span style="text-align: right; max-width: 55%; font-size: 11px;">{{ $application->sub_service }}</span>
            </div>
        @endif
        <div class="flex-between">
            <span>Status:</span>
            <span class="bold">{{ $application->work_status ?? 'PENDING' }}</span>
        </div>
        @if($application->service?->expected_processing_days)
            <div class="flex-between">
                <span>Exp. Time:</span>
                <span>{{ $application->service->expected_processing_days }} Working Days</span>
            </div>
        @endif

        <div class="divider"></div>
        <!-- Financials -->
        @php
            $rate = (float) $application->total_amount;
            $recv = (float) $application->received_amount;
            $rem = max(0, $rate - $recv);
        @endphp
        <div class="flex-between">
            <span>Total Fee (दर):</span>
            <span class="bold">₹ {{ number_format($rate, 2) }}</span>
        </div>
        <div class="flex-between" style="font-size: 13.5px; color: #166534;">
            <span class="bold">AMOUNT RECEIVED:</span>
            <span class="bold">₹ {{ number_format($recv, 2) }}</span>
        </div>
        @if(!empty($application->payment_mode))
            <div class="flex-between" style="font-size: 11px; color: #475569;">
                <span>Payment Mode (मोड):</span>
                <span class="bold">{{ strtoupper($application->payment_mode) }}</span>
            </div>
        @endif
        <div class="flex-between" style="color: {{ $rem > 0 ? '#b91c1c' : '#166534' }}; font-weight: bold;">
            <span>Remaining Bal:</span>
            <span>₹ {{ number_format($rem, 2) }}</span>
        </div>

        @if(!empty($application->external_acknowledgement_no))
            <div class="divider"></div>
            <div class="flex-between" style="font-size: 11px;">
                <span class="bold">Govt Ack / Token:</span>
                <span class="bold">{{ $application->external_acknowledgement_no }}</span>
            </div>
        @endif

        <div class="divider"></div>
        <!-- Footer -->
        <div class="center" style="font-size: 11px; margin-top: 6px;">
            Track your application status online at:<br>
            <strong style="font-size: 11.5px; text-decoration: underline;">{{ url('/track') }}</strong><br>
            <br>
            Operator: {{ $application->assignedEmployee?->user?->name ?? ($application->assignedEmployee?->name ?? 'Counter Desk') }}<br>
            <span style="font-size: 10px; color: #64748b;">(Computer Generated Receipt - No Signature Required)</span><br>
            <strong>* Thank You For Visiting *</strong>
        </div>
    </div>
</body>
</html>
