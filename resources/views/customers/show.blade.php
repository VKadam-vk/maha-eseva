@extends('layouts.app')

@section('title', $customer->name . ' (Customer Profile & Services)')

@section('content')
<style>
    /* ========================================================================= */
    /* CUSTOMER SHOW & SERVICES SPREADSHEET                                      */
    /* ========================================================================= */
    .cust-profile-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 100%;
        margin: 0 auto;
    }

    /* Top Compact Profile Card */
    .cust-top-banner {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 4px 15px -2px rgba(15, 23, 42, 0.05);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cust-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .cust-avatar-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .cust-avatar-badge {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        font-size: 20px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
    }

    .cust-name-title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
        text-transform: uppercase;
    }

    /* Details Grid Strip */
    .cust-details-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 12.5px;
    }

    .cust-info-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cust-info-item strong {
        color: #0f172a;
    }

    .cust-info-item i {
        color: #0284c7;
        width: 14px;
        text-align: center;
    }

    /* 4 Summary Metric Cards */
    .summary-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    @media (max-width: 900px) {
        .summary-metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .metric-card-box {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 12px 16px;
        box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .metric-card-box .lbl {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .metric-card-box .val {
        font-size: 21px;
        font-weight: 800;
        font-family: var(--font-mono);
        color: #0f172a;
    }

    /* UNIFIED SERVICES EXCEL SPREADSHEET TABLE */
    .services-spreadsheet-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px -2px rgba(15, 23, 42, 0.05);
    }

    .services-card-header {
        background: #0f172a;
        color: #f8fafc;
        padding: 10px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .excel-table-scroll-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
    }

    .pocket-excel-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12.5px;
    }

    .pocket-excel-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    .pocket-excel-table th {
        padding: 8px 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 2px solid #334155;
        border-right: 1px solid #334155;
        white-space: nowrap;
        text-align: left;
    }

    .pocket-excel-table th:last-child {
        border-right: none;
    }

    .pocket-excel-table td {
        padding: 4px 5px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }

    .pocket-excel-table td:last-child {
        border-right: none;
    }

    .pocket-excel-table tr.active-entry-row {
        background: #fffbeb !important;
        border: 2px solid #f59e0b;
    }

    .pocket-excel-table tr.active-entry-row td {
        background: #fffbeb;
        border-top: 2px solid #f59e0b;
        border-bottom: 2px solid #f59e0b;
        padding: 5px 5px;
    }

    .pocket-excel-table tbody tr.committed-row:hover {
        background: #f8fafc;
    }

    .pocket-excel-table .excel-input,
    .pocket-excel-table .excel-select {
        width: 100%;
        height: 34px;
        font-size: 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 5px;
        padding: 4px 6px;
        background: #ffffff;
        transition: all 0.15s ease;
    }

    .pocket-excel-table .excel-cell-input {
        width: 100%;
        height: 30px;
        font-size: 12px;
        border: 1px solid transparent;
        border-radius: 5px;
        background: transparent;
        padding: 3px 6px;
        transition: all 0.15s ease;
    }

    .pocket-excel-table .excel-cell-input:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .pocket-excel-table .excel-cell-input:focus {
        border-color: #0284c7;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    .pocket-excel-table .excel-status-input {
        width: 100%;
        height: 30px;
        font-size: 11.5px;
        font-weight: 800;
        border: 1.5px solid #cbd5e1;
        border-radius: 5px;
        padding: 3px 6px;
        text-align: center;
        background: #ffffff;
        text-transform: uppercase;
        cursor: pointer;
    }

    .pocket-excel-table .excel-work-details {
        width: 100%;
        min-height: 30px;
        max-height: 120px;
        font-size: 12px;
        border: 1px solid transparent;
        border-radius: 5px;
        background: transparent;
        padding: 3px 6px;
        resize: vertical;
        line-height: 1.3;
        transition: all 0.15s ease;
    }

    .pocket-excel-table .excel-work-details:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .pocket-excel-table .excel-work-details:focus {
        border-color: #0284c7;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    .pocket-excel-table .excel-operator-select {
        width: 100%;
        height: 30px;
        font-size: 11.5px;
        font-weight: 600;
        border: 1px solid transparent;
        border-radius: 5px;
        background: transparent;
        padding: 3px 6px;
        color: #334155;
        cursor: pointer;
    }

    .pocket-excel-table .excel-operator-select:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .pocket-excel-table .excel-operator-select:focus {
        border-color: #0284c7;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    /* Fixed / Sticky Right Status Column for No-Scroll Experience */
    .sticky-status-th,
    .sticky-status-td {
        position: sticky;
        right: 120px;
        background: #ffffff;
        z-index: 2;
    }
    .pocket-excel-table thead th.sticky-status-th {
        background: #0f172a;
        z-index: 3;
    }
    .pocket-excel-table tr.active-entry-row td.sticky-status-td {
        background: #fffbeb;
    }

    .sticky-action-th,
    .sticky-action-td {
        position: sticky;
        right: 0;
        background: #ffffff;
        z-index: 2;
    }
    .pocket-excel-table thead th.sticky-action-th {
        background: #0f172a;
        z-index: 3;
    }
    .pocket-excel-table tr.active-entry-row td.sticky-action-td {
        background: #fffbeb;
    }

    /* Sub-Service Eye Icon Button */
    .btn-doc-eye {
        width: 32px;
        height: 34px;
        flex-shrink: 0;
        background: #e0f2fe;
        color: #0284c7;
        border: 1.5px solid #bae6fd;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .btn-doc-eye:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }

    .btn-doc-eye i,
    .btn-doc-eye-small i {
        pointer-events: none;
    }

    .btn-doc-eye-small {
        width: 24px;
        height: 26px;
        flex-shrink: 0;
        background: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 11px;
    }

    .btn-doc-eye-small:hover {
        background: #0284c7;
        color: #ffffff;
    }

    .btn-row-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        line-height: 1.2;
    }

    .btn-row-apply { background: #0284c7; color: #ffffff !important; }
    .btn-row-apply:hover { background: #0369a1; }
    .btn-row-tutorial { background: #dc2626; color: #ffffff !important; }
    .btn-row-tutorial:hover { background: #b91c1c; }
    .btn-row-view { background: #f1f5f9; color: #334155 !important; border: 1px solid #cbd5e1; }
    .btn-row-view:hover { background: #e2e8f0; }

    .saved-pulse-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        background: #dcfce7;
        color: #166534;
        font-size: 10.5px;
        font-weight: 800;
        border-radius: 4px;
        animation: fadeInOut 1.5s forwards;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; transform: scale(0.9); }
        15% { opacity: 1; transform: scale(1); }
        80% { opacity: 1; }
        100% { opacity: 0; }
    }

    /* Modal Overlay & Card for Required Docs */
    #docs-modal-overlay,
    .modal-overlay#docs-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.82) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        z-index: 99999999 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    #docs-modal-overlay.open {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .docs-modal-card {
        position: relative !important;
        z-index: 100000000 !important;
        background: #ffffff !important;
        width: 100% !important;
        max-width: 540px !important;
        border-radius: 12px !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6) !important;
        border: 1.5px solid #cbd5e1 !important;
        overflow: hidden !important;
    }

    .docs-modal-header {
        background: #0f172a;
        color: #f8fafc;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .docs-modal-body {
        padding: 16px;
    }

    .docs-checklist-container {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        max-height: 220px;
        overflow-y: auto;
    }

    .doc-check-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 6px 0;
        font-size: 12.5px;
        font-weight: 600;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .doc-check-item:last-child {
        border-bottom: none;
    }
</style>

<div class="cust-profile-container">

    <!-- 1. COMPACT CUSTOMER PROFILE BANNER (Above Total Billed) -->
    <div class="cust-top-banner">
        <div class="cust-header-row">
            <div class="cust-avatar-info">
                <div class="cust-avatar-badge">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h1 class="cust-name-title">{{ $customer->name }}</h1>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-family: var(--font-mono); font-weight: 800;">
                            {{ $customer->customer_code }}
                        </span>
                        <span class="badge" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 700;">
                            {{ $customer->category?->name ?? 'Regular Citizen' }}
                        </span>
                        @if($customer->is_blacklisted)
                            <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #f87171; font-weight: 800;">
                                <i class="fa-solid fa-ban"></i> BLACKLISTED
                            </span>
                        @endif
                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800;">
                            <i class="fa-solid fa-star" style="color: #f59e0b;"></i> {{ $customer->star_rating ?? 5 }} ★
                        </span>
                    </div>
                </div>
            </div>

            <!-- Header Action Button -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-secondary btn-sm"
                   style="font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Details Strip (Clean Row Grid) -->
        <div class="cust-details-strip">
            <div class="cust-info-item" title="Mobile Number">
                <i class="fa-solid fa-phone"></i>
                <span><strong>Phone:</strong> <a href="tel:{{ $customer->mobile }}" style="color: #0284c7; text-decoration: none; font-family: var(--font-mono); font-weight: 700;">{{ $customer->mobile }}</a></span>
            </div>
            <div class="cust-info-item" title="Birth Date / Age">
                <i class="fa-solid fa-cake-candles"></i>
                <span><strong>DOB:</strong> {{ $customer->birth_date ? $customer->birth_date->format('d M Y') : 'Not set' }}</span>
            </div>
            <div class="cust-info-item" title="Occupation">
                <i class="fa-solid fa-briefcase"></i>
                <span><strong>Work:</strong> {{ $customer->occupation ? strtoupper($customer->occupation) : 'General' }}</span>
            </div>
            <div class="cust-info-item" title="Full Address">
                <i class="fa-solid fa-location-dot"></i>
                <span><strong>Location:</strong> {{ $customer->address ?? ($customer->city ?? 'Pune') }}</span>
            </div>
            @if($customer->email)
                <div class="cust-info-item" title="Email">
                    <i class="fa-solid fa-envelope"></i>
                    <span><strong>Email:</strong> {{ $customer->email }}</span>
                </div>
            @endif
            <div class="cust-info-item" title="Registration Date">
                <i class="fa-regular fa-calendar-check"></i>
                <span><strong>Registered:</strong> {{ $customer->created_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>

    <!-- 2. TOTAL BILLED & SUMMARY METRIC CARDS -->
    <div class="summary-metrics-grid">
        <div class="metric-card-box" style="border-left: 4px solid #0284c7;">
            <div class="lbl"><i class="fa-solid fa-file-invoice" style="color: #0284c7;"></i> Total Billed</div>
            <div id="metric-total-billed" class="val">₹{{ number_format($customer->total_billed, 2) }}</div>
        </div>
        <div class="metric-card-box" style="border-left: 4px solid #16a34a;">
            <div class="lbl"><i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Total Paid</div>
            <div id="metric-total-paid" class="val" style="color: #166534;">₹{{ number_format($customer->total_paid, 2) }}</div>
        </div>
        <div class="metric-card-box" style="border-left: 4px solid {{ $customer->total_outstanding > 0 ? '#dc2626' : '#16a34a' }};">
            <div class="lbl"><i class="fa-solid fa-clock-rotate-left" style="color: {{ $customer->total_outstanding > 0 ? '#dc2626' : '#16a34a' }};"></i> Outstanding Balance</div>
            <div id="metric-total-outstanding" class="val" style="color: {{ $customer->total_outstanding > 0 ? '#dc2626' : '#166534' }};">
                ₹{{ number_format($customer->total_outstanding, 2) }}
            </div>
        </div>
        <div class="metric-card-box" style="border-left: 4px solid #f59e0b;">
            <div class="lbl"><i class="fa-solid fa-folder-tree" style="color: #f59e0b;"></i> Total Services</div>
            <div id="metric-total-apps" class="val">{{ $customer->applications->count() }}</div>
        </div>
    </div>

    <!-- 3. UNIFIED CUSTOMER SERVICES SPREADSHEET (Fast Entry Style: Live Row on Top + Old Services Below) -->
    @php
        $uniqueMainServices = $activeServices->pluck('main_service_name')->filter()->unique()->sort()->values();
        $formattedApps = $customer->applications->map(function ($app) use ($customer) {
            return [
                'id' => $app->id,
                'application_number' => $app->application_number,
                'service_id' => $app->service_id,
                'service_name' => $app->service?->main_service_name ?? 'Service',
                'sub_service' => $app->sub_service ?: ($app->service?->sub_service_name ?? '-'),
                'application_date' => $app->created_at ? $app->created_at->format('d-m-Y') : 'Today',
                'application_date_time' => $app->created_at ? $app->created_at->format('d-m-Y h:i A') : ($app->application_date ? $app->application_date->format('d-m-Y') : 'Today'),
                'rate' => (float) $app->total_amount,
                'received' => (float) $app->received_amount,
                'remaining' => (float) $app->remaining_amount,
                'status' => $app->work_status ?? 'PENDING',
                'expected_days' => (int) ($app->service?->expected_processing_days ?? 7),
                'main_service_name' => $app->service?->main_service_name ?? 'Service',
                'work_details' => $app->work_details ?? '',
                'payment_remarks' => $app->payment_remarks ?? '',
                'follow_up_date' => $app->follow_up_date ? $app->follow_up_date->format('Y-m-d') : '',
                'portal_link' => $app->service?->work_portal_link ?: $app->service?->service_portal_link,
                'tutorial_link' => $app->service?->video_instruction_link,
                'assigned_employee_id' => $app->assigned_employee_id,
                'operator' => $app->assignedEmployee?->user?->name ?? 'Unassigned',
                'customer_name' => $customer->name,
                'customer_mobile' => $customer->mobile,
                'show_url' => route('applications.show', $app->id),
                'receipt_url' => route('applications.receipt', $app->id),
            ];
        });
    @endphp

    <div class="services-spreadsheet-card">
        <div class="services-card-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-table-cells" style="color: #38bdf8;"></i>
                <span style="font-weight: 700; font-size: 13px; text-transform: uppercase;">
                    CUSTOMER SERVICES & WORK DESK (New Service Entry Row + All Customer Services Below)
                </span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div id="customer-show-filter-toolbar"></div>
                <span id="services-badge" class="badge" style="background: #1e293b; color: #38bdf8; border: 1px solid #334155; font-weight: 800; font-size: 11.5px;">
                    {{ $customer->applications->count() }} Services
                </span>
                <span style="font-size: 11.5px; color: #94a3b8; font-weight: 600;">
                    <i class="fa-solid fa-keyboard" style="color: #f59e0b;"></i> Tab across cells | Enter to save
                </span>
            </div>
        </div>

        <!-- STATUS COMBOBOX DATALIST -->
        <datalist id="status-datalist">
            <option value="IN_PROGRESS">IN_PROGRESS</option>
            <option value="PENDING">PENDING</option>
            <option value="READY">READY</option>
            <option value="DELIVERED">DELIVERED</option>
            <option value="SUBMITTED">SUBMITTED</option>
            <option value="APPROVED">APPROVED</option>
            <option value="REJECTED">REJECTED</option>
        </datalist>

        <!-- TABLE SCROLL WRAPPER (Compact, Fits Screen Without Horizontal Scroll) -->
        <div class="excel-table-scroll-wrapper">
            <table id="customer-services-table" class="pocket-excel-table">
                <thead>
                    <tr>
                        <th style="min-width: 80px;"># SR Number</th>
                        <th style="min-width: 150px;">
                            <div class="excel-th-inner">
                                <span>1. Main Service *</span>
                                <button type="button" class="excel-col-filter-btn" data-col="main_service" title="Filter by Main Service">
                                    <i class="fa-solid fa-filter"></i>
                                </button>
                            </div>
                        </th>
                        <th style="min-width: 190px;">
                            <div class="excel-th-inner">
                                <span>2. Sub Service *</span>
                                <button type="button" class="excel-col-filter-btn" data-col="sub_service" title="Filter by Sub Service">
                                    <i class="fa-solid fa-filter"></i>
                                </button>
                            </div>
                        </th>
                        <th style="min-width: 85px; color: #38bdf8; text-align: center;">Exp. Days</th>
                        <th style="min-width: 75px;">3. Rate (₹) *</th>
                        <th style="min-width: 75px; color: #86efac;">4. Received (₹)</th>
                        <th style="min-width: 75px;">5. Remaining (₹)</th>
                        <th style="min-width: 140px; color: #38bdf8;">6. Work Details</th>
                        <th style="min-width: 140px; color: #10b981;">7. Pending Remark</th>
                        <th style="min-width: 125px;">
                            <div class="excel-th-inner">
                                <span>8. Operator</span>
                                <button type="button" class="excel-col-filter-btn" data-col="operator" title="Filter by Operator">
                                    <i class="fa-solid fa-filter"></i>
                                </button>
                            </div>
                        </th>
                        <th style="min-width: 125px; color: #fbbf24;">
                            <div class="excel-th-inner">
                                <span>9. Follow-Up Date</span>
                                <button type="button" class="excel-col-filter-btn" data-col="followup_date" title="Filter by Follow-Up Date">
                                    <i class="fa-solid fa-filter"></i>
                                </button>
                            </div>
                        </th>
                        <th class="sticky-status-th" style="min-width: 130px; color: #f59e0b; text-align: center;">
                            <div class="excel-th-inner">
                                <span>10. Status</span>
                                <button type="button" class="excel-col-filter-btn" data-col="status" title="Filter by Status">
                                    <i class="fa-solid fa-filter"></i>
                                </button>
                            </div>
                        </th>
                        <th class="sticky-action-th" style="min-width: 120px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="services-tbody">
                    <!-- ACTIVE EXCEL LIVE INPUT ROW (Always on Top for instant 5-second entry) -->
                    <tr id="active-service-row" class="active-entry-row">
                        <!-- SR Number Column -->
                        <td style="padding: 4px 6px;">
                            <span class="badge" style="background: #f59e0b; color: white; font-weight: 800; font-size: 10px; padding: 4px 6px; border-radius: 4px; font-family: var(--font-mono);">
                                <i class="fa-solid fa-plus"></i> NEW
                            </span>
                            <div style="font-size: 10px; color: #64748b; font-family: var(--font-mono); font-weight: 600; margin-top: 2px;">
                                <i class="fa-regular fa-clock" style="font-size: 9px; color: #94a3b8; margin-right: 2px;"></i>Today
                            </div>
                        </td>

                        <!-- 1. Main Service -->
                        <td>
                            <select id="se_main_service" class="excel-select" onchange="onMainServiceChange(this.value)"
                                    style="font-weight: 700; text-transform: uppercase;">
                                <option value="">-- Main Service --</option>
                                @foreach($uniqueMainServices as $mainName)
                                    <option value="{{ $mainName }}">{{ strtoupper($mainName) }}</option>
                                @endforeach
                            </select>
                        </td>

                        <!-- 2. Sub Service -->
                        <td style="padding: 4px 6px;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <select id="se_sub_service_select" class="excel-select" onchange="onSubServiceChange(this.value)" disabled
                                        style="font-weight: 600; flex: 1;">
                                    <option value="">-- Choose Sub Service --</option>
                                </select>
                                <button type="button" id="btn-view-service-docs" class="btn-doc-eye" onclick="openDocsModal(event)"
                                        title="View Required Documents">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Expected Days Column -->
                        <td style="text-align: center; padding: 4px 6px; white-space: nowrap;">
                            <span id="se_expected_days_badge" class="badge"
                                  style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 700; font-size: 11px; padding: 3px 6px; border-radius: 4px;">
                                -
                            </span>
                        </td>

                        <!-- 3. Rate (₹) -->
                        <td>
                            <input type="number" step="0.01" id="se_rate" class="excel-input font-mono font-bold"
                                   placeholder="0.00" oninput="calculateRemaining()" onkeydown="handleActiveRowKey(event)">
                        </td>

                        <!-- 4. Received (₹) -->
                        <td>
                            <input type="number" step="0.01" id="se_received" class="excel-input font-mono font-bold"
                                   placeholder="0.00" style="color: #166534;" oninput="calculateRemaining()" onkeydown="handleActiveRowKey(event)">
                        </td>

                        <!-- 5. Remaining (₹) -->
                        <td>
                            <input type="text" id="se_remaining" class="excel-input font-mono font-bold"
                                   placeholder="₹ 0.00" readonly tabindex="-1" style="background: #f8fafc;">
                        </td>

                        <!-- 6. Work Details -->
                        <td>
                            <textarea id="se_work_details" class="excel-work-details"
                                      placeholder="Work details / notes..."
                                      rows="1" oninput="autoExpandWorkDetails(this)" onkeydown="handleActiveRowKey(event)"></textarea>
                        </td>

                        <!-- 7. Pending Remark -->
                        <td>
                            <textarea id="se_payment_remarks" class="excel-work-details"
                                      placeholder="Pending remark / notes..."
                                      rows="1" oninput="autoExpandWorkDetails(this)" onkeydown="handleActiveRowKey(event)"></textarea>
                        </td>

                        <!-- 8. Operator -->
                        <td>
                            <select id="se_operator" class="excel-select font-semibold" onkeydown="handleActiveRowKey(event)">
                                <option value="">-- Operator --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ Auth::user()?->employee?->id == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->user?->name ?? 'Staff #' . $emp->id }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- 8. Follow-Up Date -->
                        <td>
                            <input type="date" id="se_followup" class="excel-input"
                                   style="font-size: 11.5px; font-weight: 600; color: #475569;"
                                   title="Pick follow-up date for reminders and citizen tracking"
                                   onkeydown="handleActiveRowKey(event)">
                        </td>

                        <!-- 9. Status (Sticky Right) -->
                        <td class="sticky-status-td" style="padding: 4px 6px;">
                            <select id="se_status" class="excel-select font-bold status-select"
                                    data-prev="IN_PROGRESS"
                                    onchange="onActiveStatusSelectChange(this)"
                                    onkeydown="handleActiveRowKey(event)"
                                    style="text-align: center;">
                                <option value="IN_PROGRESS" selected>IN_PROGRESS</option>
                                <option value="PENDING">PENDING</option>
                                <option value="WAITING_FOR_DOCS">WAITING FOR DOCS</option>
                                <option value="SUBMITTED">SUBMITTED</option>
                                <option value="READY">READY</option>
                                <option value="DELIVERED">DELIVERED</option>
                                <option value="COMPLETED">COMPLETED</option>
                                <option value="REJECTED">REJECTED</option>
                                <option value="__NEW_CUSTOM__">➕ Custom Status...</option>
                            </select>
                        </td>

                        <!-- Actions (Sticky Right) -->
                        <td class="sticky-action-td" style="text-align: center; padding: 4px 6px;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <button type="button" id="btn-save-service" class="btn btn-sm btn-primary" onclick="saveActiveServiceRow()"
                                        style="font-weight: 800; font-size: 11px; padding: 5px 10px; background: #ea580c; border: none; border-radius: 5px;"
                                        title="Save service entry to customer profile (Enter)">
                                    <i class="fa-solid fa-check"></i> Save
                                </button>
                                <a id="row-btn-apply" href="#" target="_blank" class="btn-row-action btn-row-apply" style="display: none;" title="Apply on Portal">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <a id="row-btn-tutorial" href="#" target="_blank" class="btn-row-action btn-row-tutorial" style="display: none;" title="Watch Tutorial">
                                    <i class="fa-brands fa-youtube"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- COMMITTED ROWS WILL BE POPULATED DYNAMICALLY BELOW -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- REQUIRED DOCUMENTS POPUP MODAL -->
<div id="docs-modal-overlay" class="modal-overlay" style="display: none;" onclick="closeDocsModal(event)">
    <div class="docs-modal-card" style="max-width: 500px;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="docs-modal-header" style="background: #0f172a; color: #ffffff; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(2, 132, 199, 0.25); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <div>
                    <h3 id="modal-service-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">Required Documents</h3>
                    <p id="modal-sub-service-title" style="margin: 2px 0 0 0; font-size: 11.5px; color: #94a3b8; font-weight: 500;">Required Documents Checklist</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDocsModal()" title="Close" style="background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; line-height: 1;">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div class="docs-modal-body" style="padding: 16px 18px; max-height: 60vh; overflow-y: auto;">
            <!-- Documents Checklist Container -->
            <div id="modal-docs-container" class="docs-checklist-container">
                <!-- Populated dynamically via JS -->
            </div>
        </div>

        <!-- Footer -->
        <div class="docs-modal-footer" style="padding: 12px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" id="modal-btn-copy-docs" onclick="copyModalDocsToClipboard(this)" class="btn btn-sm"
                    style="background: #16a34a; color: #ffffff; font-size: 12.5px; font-weight: 700; border: none; border-radius: 6px; padding: 7px 16px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;"
                    title="Copy formatted checklist to paste on WhatsApp">
                <i class="fa-brands fa-whatsapp" style="font-size: 15px;"></i> Copy for WhatsApp
            </button>
            <button type="button" onclick="closeDocsModal()" class="btn btn-sm btn-secondary"
                    style="font-size: 12.5px; font-weight: 600; padding: 7px 16px; border-radius: 6px;">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ==========================================
// 1. DATA MASTER & STATE
// ==========================================
const currentCustomerId = {{ $customer->id }};
const allServices = @json($activeServices);
const allEmployees = @json($employees);
let loadedApplications = {};
const initialApps = @json($formattedApps);

// Status storage key
const STATUS_STORAGE_KEY = 'maha_eseva_service_statuses';
const DEFAULT_SERVICE_STATUSES = {
    'DEFAULT': ['IN_PROGRESS', 'PENDING', 'READY', 'DELIVERED', 'SUBMITTED', 'APPROVED', 'REJECTED'],
    'PAN CARD': ['IN_PROGRESS', 'PENDING', 'DOCS_RECEIVED', 'NSDL_SUBMITTED', 'ACK_GENERATED', 'UNDER_VERIFICATION', 'PAN_ALLOTTED', 'READY', 'DELIVERED', 'REJECTED'],
    'VOTER ID': ['IN_PROGRESS', 'PENDING', 'FORM_6_SUBMITTED', 'BLO_VERIFICATION', 'ACCEPTED', 'EPIC_GENERATED', 'READY', 'DELIVERED', 'REJECTED'],
    'SHOP ACT': ['IN_PROGRESS', 'PENDING', 'INTIMATION_FILED', 'APPLICATION_SUBMITTED', 'FEE_PAID', 'CERTIFICATE_GENERATED', 'READY', 'DELIVERED', 'REJECTED'],
    'RATION CARD': ['IN_PROGRESS', 'PENDING', 'DOCS_UPLOADED', 'TAHSIL_SUBMISSION', 'INSPECTION_PENDING', 'APPROVED', 'READY', 'DELIVERED', 'REJECTED'],
    'AADHAAR': ['IN_PROGRESS', 'PENDING', 'UPDATE_APPOINTMENT', 'ENROLLMENT_DONE', 'UNDER_PROCESS', 'GENERATED', 'READY', 'DELIVERED'],
    'DRIVING LICENSE': ['IN_PROGRESS', 'PENDING', 'LEARNER_APPLIED', 'SLOT_BOOKED', 'TEST_PASSED', 'DL_APPROVED', 'SMART_CARD_PRINTED', 'READY', 'DELIVERED'],
    'INCOME CERTIFICATE': ['IN_PROGRESS', 'PENDING', 'TALATHI_REPORT', 'CIRCLE_OFFICER_APPROVED', 'TAHSILDAR_SIGN_PENDING', 'APPROVED', 'READY', 'DELIVERED'],
    'CASTE CERTIFICATE': ['IN_PROGRESS', 'PENDING', 'DOCS_SCRUTINY', 'SDO_SUBMISSION', 'VERIFICATION_PENDING', 'APPROVED', 'READY', 'DELIVERED']
};

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ==========================================
// 2. STATUS COMBOBOX PERSISTENCE
// ==========================================
function getSavedStatusesForService(serviceName) {
    const rawKey = (serviceName || 'DEFAULT').trim().toUpperCase();
    let stored = {};
    try {
        stored = JSON.parse(localStorage.getItem(STATUS_STORAGE_KEY) || '{}');
    } catch(e) {
        stored = {};
    }

    const baseList = DEFAULT_SERVICE_STATUSES[rawKey] || DEFAULT_SERVICE_STATUSES['DEFAULT'];
    const customList = Array.isArray(stored[rawKey]) ? stored[rawKey] : [];
    return Array.from(new Set([...baseList, ...customList]));
}

function recordCustomStatusForService(serviceName, val) {
    if (!val) return;
    const clean = val.trim().toUpperCase();
    if (!clean) return;

    const rawKey = (serviceName || 'DEFAULT').trim().toUpperCase();
    let stored = {};
    try {
        stored = JSON.parse(localStorage.getItem(STATUS_STORAGE_KEY) || '{}');
    } catch(e) {
        stored = {};
    }

    if (!Array.isArray(stored[rawKey])) {
        stored[rawKey] = [];
    }

    const baseList = DEFAULT_SERVICE_STATUSES[rawKey] || DEFAULT_SERVICE_STATUSES['DEFAULT'];
    if (!baseList.includes(clean) && !stored[rawKey].includes(clean)) {
        stored[rawKey].push(clean);
        localStorage.setItem(STATUS_STORAGE_KEY, JSON.stringify(stored));
    }

    updateStatusDatalistForService(rawKey);
}

function updateStatusDatalistForService(serviceName) {
    const datalist = document.getElementById('status-datalist');
    if (!datalist) return;
    const list = getSavedStatusesForService(serviceName);
    datalist.innerHTML = list.map(st => `<option value="${escapeHtml(st)}"></option>`).join('');
}

function ensureServiceStatusDatalist(serviceName) {
    const safeId = 'status-datalist-' + encodeURIComponent(serviceName || 'DEFAULT').replace(/%/g, '_');
    let datalist = document.getElementById(safeId);
    if (!datalist) {
        datalist = document.createElement('datalist');
        datalist.id = safeId;
        document.body.appendChild(datalist);
    }
    const list = getSavedStatusesForService(serviceName);
    datalist.innerHTML = list.map(st => `<option value="${escapeHtml(st)}"></option>`).join('');
    return safeId;
}

function applyStatusInputStyle(el) {
    if (!el) return;
    const val = (el.value || '').trim().toUpperCase();

    if (!val) {
        el.style.background = '#ffffff';
        el.style.color = '#334155';
        el.style.borderColor = '#cbd5e1';
    } else if (val.includes('READY') || val.includes('DONE') || val.includes('APPROVED') || val.includes('DELIVERED')) {
        el.style.background = '#dcfce7';
        el.style.color = '#166534';
        el.style.borderColor = '#86efac';
    } else if (val.includes('PEND') || val.includes('REJECT') || val.includes('CANCEL')) {
        el.style.background = '#fee2e2';
        el.style.color = '#dc2626';
        el.style.borderColor = '#fca5a5';
    } else if (val.includes('PROG') || val.includes('HOLD') || val.includes('WAIT') || val.includes('SUBMIT')) {
        el.style.background = '#fef3c7';
        el.style.color = '#b45309';
        el.style.borderColor = '#fde68a';
    } else {
        el.style.background = '#f3e8ff';
        el.style.color = '#6b21a8';
        el.style.borderColor = '#d8b4fe';
    }
}

function renderStatusOptionsHtml(currentStatus, serviceName) {
    const cleanCur = (currentStatus || 'PENDING').trim().toUpperCase();
    const serviceStatuses = getSavedStatusesForService(serviceName) || [];
    const defaults = [
        'PENDING',
        'IN_PROGRESS',
        'WAITING_FOR_DOCS',
        'SUBMITTED',
        'READY',
        'DELIVERED',
        'COMPLETED',
        'REJECTED'
    ];
    const allStatuses = Array.from(new Set([cleanCur, ...serviceStatuses, ...defaults])).filter(Boolean);

    let html = '';
    allStatuses.forEach(st => {
        const isSel = (st === cleanCur) ? 'selected' : '';
        html += `<option value="${escapeHtml(st)}" ${isSel}>${escapeHtml(st)}</option>`;
    });
    html += `<option value="__NEW_CUSTOM__">➕ Custom Status...</option>`;
    return html;
}

function onCommittedStatusSelectChange(selectEl, appId) {
    if (!selectEl) return;
    if (selectEl.value === '__NEW_CUSTOM__') {
        const custom = prompt('Enter custom status name:');
        if (custom && custom.trim()) {
            const clean = custom.trim().toUpperCase();
            let opt = Array.from(selectEl.options).find(o => o.value === clean);
            if (!opt) {
                opt = document.createElement('option');
                opt.value = clean;
                opt.textContent = clean;
                selectEl.insertBefore(opt, selectEl.options[selectEl.options.length - 1]);
            }
            selectEl.value = clean;
        } else {
            selectEl.value = selectEl.dataset.prev || 'PENDING';
            applyStatusInputStyle(selectEl);
            return;
        }
    }
    selectEl.dataset.prev = selectEl.value;
    applyStatusInputStyle(selectEl);
    autoSaveCommittedRow(appId);
}

function onActiveStatusSelectChange(selectEl) {
    if (!selectEl) return;
    if (selectEl.value === '__NEW_CUSTOM__') {
        const custom = prompt('Enter custom status name:');
        if (custom && custom.trim()) {
            const clean = custom.trim().toUpperCase();
            let opt = Array.from(selectEl.options).find(o => o.value === clean);
            if (!opt) {
                opt = document.createElement('option');
                opt.value = clean;
                opt.textContent = clean;
                selectEl.insertBefore(opt, selectEl.options[selectEl.options.length - 1]);
            }
            selectEl.value = clean;
        } else {
            selectEl.value = selectEl.dataset.prev || 'IN_PROGRESS';
            applyStatusInputStyle(selectEl);
            return;
        }
    }
    selectEl.dataset.prev = selectEl.value;
    applyStatusInputStyle(selectEl);
}

// ==========================================
// 3. COMMITTED ROWS GENERATION & AUTO-SAVE
// ==========================================
function getEmployeeOptionsHtml(selectedEmployeeId) {
    let html = '<option value="">-- Unassigned --</option>';
    allEmployees.forEach(emp => {
        const isSel = selectedEmployeeId && (emp.id == selectedEmployeeId);
        html += `<option value="${emp.id}" ${isSel ? 'selected' : ''}>${escapeHtml(emp.user ? emp.user.name : 'Operator')}</option>`;
    });
    return html;
}

function getAppPortalLink(app) {
    if (app.portal_link && app.portal_link.trim() !== '') return app.portal_link.trim();
    return `https://www.google.com/search?q=${encodeURIComponent(app.service_name + ' ' + (app.sub_service || '') + ' official portal online apply')}`;
}

function getAppTutorialLink(app) {
    if (app.tutorial_link && app.tutorial_link.trim() !== '') return app.tutorial_link.trim();
    return `https://www.youtube.com/results?search_query=${encodeURIComponent(app.service_name + ' ' + (app.sub_service || '') + ' online apply tutorial')}`;
}

function getCommittedRowInnerHtml(app) {
    const rateNum = typeof app.rate === 'number' ? app.rate : (parseFloat(app.rate) || 0);
    const recNum = typeof app.received === 'number' ? app.received : (parseFloat(app.received) || 0);
    const remNum = Math.max(0, rateNum - recNum);
    const remColor = remNum > 0 ? '#dc2626' : '#166534';
    const currentStatus = (app.status || '').toUpperCase();
    const serviceName = (app.main_service_name || app.service_name || 'DEFAULT').trim().toUpperCase();
    const datalistId = ensureServiceStatusDatalist(serviceName);

    return `
        <td class="font-mono" style="white-space: nowrap; user-select: text; cursor: default; padding: 4px 6px;">
            <div style="font-weight: 800; color: #0f172a; font-size: 12px; line-height: 1.2;">
                ${escapeHtml(app.application_number)}
            </div>
            <div style="font-size: 10px; color: #64748b; font-weight: 600; margin-top: 2px;">
                <i class="fa-regular fa-clock" style="font-size: 9px; color: #94a3b8; margin-right: 2px;"></i>${escapeHtml(app.application_date_time || app.application_date || 'Today')}
            </div>
        </td>
        <td style="font-weight: 700; color: #0f172a;">
            ${escapeHtml(app.service_name)}
        </td>
        <td style="padding: 4px 6px;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <input type="text" id="crow_sub_${app.id}" class="excel-cell-input" style="flex: 1;"
                       value="${escapeHtml(app.sub_service || '')}"
                       placeholder="Sub service"
                       title="Click to edit sub-service"
                       onchange="autoSaveCommittedRow(${app.id})"
                       onkeydown="handleCommittedKey(event, ${app.id})">
                <button type="button" class="btn-doc-eye-small" onclick="openDocsModalForApp(${app.id})" title="View Required Documents">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'sub_service', event)" title="Sub Service Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
        </td>
        <td style="text-align: center; padding: 4px 6px; white-space: nowrap;">
            <span class="badge" style="background: #f1f5f9; color: #0369a1; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11px; padding: 3px 6px; border-radius: 4px;">
                ⏱️ ${app.expected_days ? app.expected_days + ' Days' : '-'}
            </span>
        </td>
        <td style="padding: 4px 6px;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <input type="number" step="0.01" id="crow_rate_${app.id}" class="excel-cell-input font-mono font-bold" style="flex: 1;"
                       value="${rateNum}"
                       title="Click to edit rate"
                       oninput="calcCommittedRemaining(${app.id})"
                       onchange="autoSaveCommittedRow(${app.id})"
                       onkeydown="handleCommittedKey(event, ${app.id})">
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'rate', event)" title="Rate Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
        </td>
        <td style="padding: 4px 6px;">
            <input type="number" step="0.01" id="crow_received_${app.id}" class="excel-cell-input font-mono font-bold"
                   value="${recNum}"
                   style="color: #166534;"
                   title="Click to edit received amount"
                   oninput="calcCommittedRemaining(${app.id})"
                   onchange="autoSaveCommittedRow(${app.id})"
                   onkeydown="handleCommittedKey(event, ${app.id})">
        </td>
        <td class="font-mono font-bold" style="padding: 4px 6px; white-space: nowrap;">
            <span id="crow_rem_${app.id}" style="color: ${remColor}; font-size: 12.5px;">
                ₹ ${remNum.toFixed(2)}
            </span>
        </td>
        <td style="padding: 4px 6px;">
            <textarea id="crow_work_details_${app.id}" class="excel-work-details"
                      placeholder="Work details..."
                      rows="1"
                      title="${escapeHtml(app.work_details || '')}"
                      oninput="autoExpandWorkDetails(this)"
                      onchange="autoSaveCommittedRow(${app.id})"
                      onkeydown="handleCommittedKey(event, ${app.id})">${escapeHtml(app.work_details || '')}</textarea>
        </td>
        <td style="padding: 4px 6px;">
            <textarea id="crow_payment_remarks_${app.id}" class="excel-work-details"
                      placeholder="Pending remark..."
                      rows="1"
                      title="${escapeHtml(app.payment_remarks || '')}"
                      oninput="autoExpandWorkDetails(this)"
                      onchange="autoSaveCommittedRow(${app.id})"
                      onkeydown="handleCommittedKey(event, ${app.id})">${escapeHtml(app.payment_remarks || '')}</textarea>
        </td>
        <td style="padding: 4px 6px;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <select id="crow_operator_${app.id}" class="excel-operator-select" style="flex: 1;"
                        title="Click to assign operator"
                        onchange="autoSaveCommittedRow(${app.id})">
                    ${getEmployeeOptionsHtml(app.assigned_employee_id)}
                </select>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'assigned_employee_id', event)" title="Operator Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
        </td>
        <td style="padding: 4px 6px;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <input type="date" id="crow_followup_${app.id}" class="excel-date-input" style="flex: 1;"
                       value="${escapeHtml(app.follow_up_date || '')}"
                       title="Expected Follow-up Date"
                       onchange="autoSaveCommittedRow(${app.id})"
                       onblur="autoSaveCommittedRow(${app.id})">
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'follow_up_date', event)" title="Follow-Up Date Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
        </td>
        <td class="sticky-status-td" style="padding: 4px 6px;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <select id="crow_status_${app.id}" class="excel-status-input font-bold status-select" style="flex: 1;"
                        data-prev="${escapeHtml(currentStatus)}"
                        onchange="onCommittedStatusSelectChange(this, ${app.id})">
                    ${renderStatusOptionsHtml(currentStatus, serviceName)}
                </select>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'work_status', event)" title="Status Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
        </td>
        <td class="sticky-action-td" style="text-align: center; white-space: nowrap; padding: 4px 6px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 4px; flex-wrap: nowrap;">
                <a href="${getAppPortalLink(app)}" target="_blank" class="btn-row-action btn-row-apply" title="Apply Online">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
                <a href="${getAppTutorialLink(app)}" target="_blank" class="btn-row-action btn-row-tutorial" title="Watch Video Tutorial">
                    <i class="fa-brands fa-youtube"></i>
                </a>
                <button type="button" class="btn-row-action btn-row-receipt" onclick="openReceiptModal(${app.id})" title="View Citizen Receipt (पावती)"
                   style="background: #16a34a; color: #ffffff; border: 1px solid #15803d; border-radius: 4px; padding: 4px 8px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fa-solid fa-receipt"></i>
                </button>
                <span id="saved-indicator-${app.id}" class="saved-pulse-badge" style="display: none;">
                    <i class="fa-solid fa-check"></i>
                </span>
            </div>
        </td>
    `;
}

function openReceiptModal(appId) {
    const app = loadedApplications[appId];
    if (app && window.showReceiptModal) {
        window.showReceiptModal(app);
    } else {
        window.open(`/applications/${appId}/receipt`, '_blank');
    }
}

function getEmployeeName(empId, fallback = '') {
    if (!empId) return (fallback || '').toUpperCase();
    const emp = (window.allEmployees || []).find(e => e.id == empId);
    if (!emp) return (fallback || '').toUpperCase();
    const name = (emp.user && emp.user.name) ? emp.user.name : (emp.name || fallback || '');
    return name.toUpperCase();
}

function createCommittedRowElement(app) {
    const tr = document.createElement('tr');
    tr.className = 'committed-row';
    tr.id = 'committed-row-' + app.id;
    tr.dataset.appId = app.id;
    tr.dataset.main_service = (app.service_name || app.main_service_name || '').trim();
    tr.dataset.sub_service = (app.sub_service || '').trim();
    tr.dataset.status = (app.status || '').trim().toUpperCase();
    tr.dataset.operator = getEmployeeName(app.assigned_employee_id, app.operator);
    tr.dataset.followup_date = (app.follow_up_date || '').trim();
    tr.innerHTML = getCommittedRowInnerHtml(app);

    setTimeout(() => {
        const input = tr.querySelector(`#crow_status_${app.id}`);
        if (input) applyStatusInputStyle(input);
        const wd = tr.querySelector(`#crow_work_details_${app.id}`);
        if (wd) autoExpandWorkDetails(wd);
    }, 0);

    return tr;
}

function handleCommittedKey(e, appId) {
    if (e.key === 'Enter') {
        e.preventDefault();
        e.target.blur();
    }
}

function calcCommittedRemaining(appId) {
    const rateEl = document.getElementById(`crow_rate_${appId}`);
    const recEl = document.getElementById(`crow_received_${appId}`);
    const remEl = document.getElementById(`crow_rem_${appId}`);

    const rate = parseFloat(rateEl ? rateEl.value : 0) || 0;
    const rec = parseFloat(recEl ? recEl.value : 0) || 0;
    const rem = Math.max(0, rate - rec);

    if (remEl) {
        remEl.innerText = `₹ ${rem.toFixed(2)}`;
        remEl.style.color = rem > 0 ? '#dc2626' : '#166534';
    }
}

function autoSaveCommittedRow(appId) {
    const rateInput = document.getElementById(`crow_rate_${appId}`);
    const receivedInput = document.getElementById(`crow_received_${appId}`);
    const statusInput = document.getElementById(`crow_status_${appId}`);
    const operatorSelect = document.getElementById(`crow_operator_${appId}`);
    const subInput = document.getElementById(`crow_sub_${appId}`);
    const workDetailsInput = document.getElementById(`crow_work_details_${appId}`);
    const paymentRemarksInput = document.getElementById(`crow_payment_remarks_${appId}`);
    const followupInput = document.getElementById(`crow_followup_${appId}`);

    if (!rateInput) return;

    const rate = rateInput.value;
    const received = receivedInput ? (receivedInput.value || 0) : 0;
    const status = statusInput ? (statusInput.value.trim().toUpperCase() || 'PENDING') : 'PENDING';
    const operator = operatorSelect ? operatorSelect.value : null;
    const subService = subInput ? subInput.value.trim() : '';
    const workDetails = workDetailsInput ? workDetailsInput.value.trim() : null;
    const paymentRemarks = paymentRemarksInput ? paymentRemarksInput.value.trim() : null;
    const followupDate = followupInput ? (followupInput.value || null) : null;

    const rowEl = document.getElementById(`committed-row-${appId}`);
    if (rowEl) {
        if (subService) rowEl.dataset.sub_service = subService;
        if (status) rowEl.dataset.status = status;
        if (operatorSelect) {
            const opText = operatorSelect.selectedOptions[0]?.textContent?.trim() || '';
            rowEl.dataset.operator = (opText.startsWith('--') ? '' : opText);
        }
        if (followupDate !== undefined) rowEl.dataset.followup_date = followupDate || '';
        if (window.customerShowFilter) window.customerShowFilter.refresh();
    }

    if (!rate || parseFloat(rate) < 0) {
        rateInput.style.borderColor = '#dc2626';
        alert('Please enter a valid rate!');
        rateInput.focus();
        return;
    }
    rateInput.style.borderColor = '';

    const payload = {
        _token: '{{ csrf_token() }}',
        application_id: appId,
        rate: rate,
        received: received,
        work_status: status,
        assigned_employee_id: operator || null,
        sub_service: subService || null,
        work_details: workDetails,
        payment_remarks: paymentRemarks,
        follow_up_date: followupDate,
    };

    fetch('{{ route("fast_entry.update_service_row") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            const d = res.data;
            if (loadedApplications[appId]) {
                loadedApplications[appId] = {
                    ...loadedApplications[appId],
                    rate: parseFloat(d.rate.replace(/,/g, '')),
                    received: parseFloat(d.received.replace(/,/g, '')),
                    remaining: parseFloat(d.remaining.replace(/,/g, '')),
                    status: d.status,
                    operator: d.operator,
                    sub_service: d.sub_service,
                    work_details: d.work_details,
                    payment_remarks: d.payment_remarks,
                    follow_up_date: d.follow_up_date,
                    assigned_employee_id: d.assigned_employee_id
                };
            }

            calcCommittedRemaining(appId);
            recalculateCustomerMetrics();

            if (statusInput) {
                recordCustomStatusForService(d.main_service_name || 'DEFAULT', statusInput.value);
                applyStatusInputStyle(statusInput);
            }

            // Pulse checkmark
            const indicator = document.getElementById(`saved-indicator-${appId}`);
            if (indicator) {
                indicator.style.display = 'inline-flex';
                setTimeout(() => { indicator.style.display = 'none'; }, 1500);
            }
        } else {
            alert('Update Error: ' + res.message);
        }
    })
    .catch(err => {
        console.error(err);
    });
}

// ==========================================
// 4. ACTIVE ROW SERVICE SELECTION & SAVE
// ==========================================
function onMainServiceChange(mainName) {
    const subSelect = document.getElementById('se_sub_service_select');
    subSelect.innerHTML = '<option value="">-- Choose Sub Service --</option>';

    updateStatusDatalistForService(mainName || 'DEFAULT');
    const seStatus = document.getElementById('se_status');
    if (seStatus) {
        seStatus.value = '';
        applyStatusInputStyle(seStatus);
    }

    if (!mainName) {
        subSelect.disabled = true;
        resetActiveRowFields();
        return;
    }

    const matched = allServices.filter(s => s.main_service_name === mainName);
    matched.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.text = s.sub_service_name + ' (₹' + parseFloat(s.price).toFixed(0) + ')';
        subSelect.appendChild(opt);
    });

    subSelect.disabled = false;

    if (matched.length === 1) {
        subSelect.value = matched[0].id;
        onSubServiceChange(matched[0].id);
    } else {
        resetActiveRowFields();
        subSelect.focus();
    }
}

function onSubServiceChange(serviceId) {
    if (!serviceId) {
        resetActiveRowFields();
        return;
    }

    const srv = allServices.find(s => s.id == serviceId);
    if (!srv) return;

    const price = parseFloat(srv.price) || 0;
    document.getElementById('se_rate').value = price;
    document.getElementById('se_received').value = price;
    calculateRemaining();

    const days = parseInt(srv.expected_processing_days) || 7;
    const expBadge = document.getElementById('se_expected_days_badge');
    if (expBadge) {
        expBadge.innerText = days + ' Days';
        expBadge.title = `Expected Processing: ${days} Days`;
    }

    const rowBtnApply = document.getElementById('row-btn-apply');
    const rowBtnTutorial = document.getElementById('row-btn-tutorial');
    const workUrl = srv.work_portal_link || srv.service_portal_link;
    const tutorialUrl = srv.video_instruction_link;

    if (rowBtnApply) {
        rowBtnApply.href = workUrl && workUrl.trim() !== '' ? workUrl.trim() : `https://www.google.com/search?q=${encodeURIComponent(srv.main_service_name + ' ' + (srv.sub_service_name || '') + ' official portal online apply')}`;
        rowBtnApply.style.display = 'inline-flex';
    }

    if (rowBtnTutorial) {
        rowBtnTutorial.href = tutorialUrl && tutorialUrl.trim() !== '' ? tutorialUrl.trim() : `https://www.youtube.com/results?search_query=${encodeURIComponent(srv.main_service_name + ' ' + (srv.sub_service_name || '') + ' online apply tutorial')}`;
        rowBtnTutorial.style.display = 'inline-flex';
    }

    document.getElementById('se_received').focus();
    document.getElementById('se_received').select();
}

function calculateRemaining() {
    const rate = parseFloat(document.getElementById('se_rate').value) || 0;
    const received = parseFloat(document.getElementById('se_received').value) || 0;
    const rem = Math.max(0, rate - received);

    const remEl = document.getElementById('se_remaining');
    remEl.value = '₹ ' + rem.toFixed(2);
    remEl.style.color = rem > 0 ? '#dc2626' : '#166534';
}

function resetActiveRowFields() {
    const expBadge = document.getElementById('se_expected_days_badge');
    if (expBadge) expBadge.innerText = '-';

    const rowBtnApply = document.getElementById('row-btn-apply');
    if (rowBtnApply) rowBtnApply.style.display = 'none';

    const rowBtnTutorial = document.getElementById('row-btn-tutorial');
    if (rowBtnTutorial) rowBtnTutorial.style.display = 'none';

    document.getElementById('se_rate').value = '';
    document.getElementById('se_received').value = '';
    document.getElementById('se_remaining').value = '';
    document.getElementById('se_work_details').value = '';
    document.getElementById('se_work_details').style.height = '30px';
    document.getElementById('se_payment_remarks').value = '';
    document.getElementById('se_payment_remarks').style.height = '30px';
    document.getElementById('se_followup').value = '';
}

function handleActiveRowKey(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        saveActiveServiceRow();
    }
}

function saveActiveServiceRow() {
    const subSel = document.getElementById('se_sub_service_select');
    const serviceId = subSel ? subSel.value : '';

    if (!serviceId) {
        alert('Please select a service first!');
        document.getElementById('se_main_service').focus();
        return;
    }

    const rate = document.getElementById('se_rate').value;
    if (!rate || parseFloat(rate) < 0) {
        alert('Please enter a valid rate!');
        document.getElementById('se_rate').focus();
        return;
    }

    const btnSave = document.getElementById('btn-save-service');
    btnSave.disabled = true;
    btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    const srv = allServices.find(s => s.id == serviceId);
    const mainServiceName = srv ? srv.main_service_name : '';
    const statusVal = document.getElementById('se_status').value.trim().toUpperCase() || 'IN_PROGRESS';

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_id: currentCustomerId,
        service_id: serviceId,
        sub_service: srv ? srv.sub_service_name : '',
        rate: rate,
        received: document.getElementById('se_received').value || 0,
        work_details: document.getElementById('se_work_details').value.trim() || null,
        payment_remarks: document.getElementById('se_payment_remarks').value.trim() || null,
        follow_up_date: document.getElementById('se_followup').value || null,
        work_status: statusVal,
        assigned_employee_id: document.getElementById('se_operator').value || null,
    };

    fetch('{{ route("fast_entry.store_service_entry") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        btnSave.disabled = false;
        btnSave.innerHTML = '<i class="fa-solid fa-check"></i> Save';

        if (res.success) {
            const d = res.data;
            recordCustomStatusForService(mainServiceName, statusVal);

            const newApp = {
                id: d.id,
                application_number: d.application_number,
                service_id: serviceId,
                service_name: d.service_name,
                sub_service: d.sub_service,
                rate: parseFloat(d.rate.replace(/,/g, '')),
                received: parseFloat(d.received.replace(/,/g, '')),
                remaining: parseFloat(d.remaining.replace(/,/g, '')),
                status: d.status,
                expected_days: d.expected_days,
                main_service_name: d.main_service_name || mainServiceName,
                work_details: d.work_details,
                payment_remarks: d.payment_remarks || payload.payment_remarks || '',
                follow_up_date: d.follow_up_date,
                portal_link: d.portal_link,
                tutorial_link: d.tutorial_link,
                operator: d.operator,
                assigned_employee_id: payload.assigned_employee_id,
                show_url: d.show_url,
                receipt_url: d.receipt_url || `/applications/${d.id}/receipt`,
                customer_name: '{{ addslashes($customer->name) }}',
                customer_mobile: '{{ addslashes($customer->mobile) }}',
                application_date: 'Today',
                application_date_time: d.formatted_date_time || (new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }))
            };

            loadedApplications[newApp.id] = newApp;

            // Insert committed row right below active input row
            const tr = createCommittedRowElement(newApp);
            const activeRow = document.getElementById('active-service-row');
            activeRow.parentNode.insertBefore(tr, activeRow.nextSibling);

            if (window.customerShowFilter) {
                window.customerShowFilter.refresh();
            }

            // Reset active row
            document.getElementById('se_main_service').value = '';
            subSel.innerHTML = '<option value="">-- Choose Sub Service --</option>';
            subSel.disabled = true;
            resetActiveRowFields();

            const stInput = document.getElementById('se_status');
            if (stInput) {
                stInput.value = '';
                applyStatusInputStyle(stInput);
            }

            recalculateCustomerMetrics();
            document.getElementById('se_main_service').focus();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => {
        btnSave.disabled = false;
        btnSave.innerHTML = '<i class="fa-solid fa-check"></i> Save';
        console.error(err);
        alert('An error occurred while saving the service. Please try again.');
    });
}

function recalculateCustomerMetrics() {
    let totalBilled = 0;
    let totalPaid = 0;
    let appCount = 0;

    Object.values(loadedApplications).forEach(app => {
        totalBilled += (parseFloat(app.rate) || 0);
        totalPaid += (parseFloat(app.received) || 0);
        appCount++;
    });

    const outstanding = Math.max(0, totalBilled - totalPaid);

    const bEl = document.getElementById('metric-total-billed');
    const pEl = document.getElementById('metric-total-paid');
    const oEl = document.getElementById('metric-total-outstanding');
    const aEl = document.getElementById('metric-total-apps');
    const sBadge = document.getElementById('services-badge');

    if (bEl) bEl.innerText = `₹${totalBilled.toFixed(2)}`;
    if (pEl) pEl.innerText = `₹${totalPaid.toFixed(2)}`;
    if (oEl) {
        oEl.innerText = `₹${outstanding.toFixed(2)}`;
        oEl.style.color = outstanding > 0 ? '#dc2626' : '#166534';
    }
    if (aEl) aEl.innerText = appCount;
    if (sBadge) sBadge.innerText = `${appCount} Services`;
}

function autoExpandWorkDetails(el) {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(Math.max(el.scrollHeight, 30), 120) + 'px';
    el.title = el.value;
}

// ==========================================
// 5. REQUIRED DOCUMENTS POPUP MODAL
// ==========================================
let currentModalDocs = [];
let currentSelectedModalService = null;

function openDocsModal(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const subSelect = document.getElementById('se_sub_service_select');
    const mainService = document.getElementById('se_main_service')?.value;
    const serviceId = subSelect ? subSelect.value : null;

    let srv = null;
    if (serviceId) {
        srv = allServices.find(s => s.id == serviceId);
    }
    if (!srv && mainService) {
        srv = allServices.find(s => s.main_service_name === mainService);
    }
    if (!srv && allServices && allServices.length > 0) {
        srv = allServices[0];
    }
    if (!srv) {
        alert('No services available.');
        return;
    }
    openDocsModalWithService(srv);
}

function openDocsModalForApp(appId) {
    const app = loadedApplications[appId];
    if (!app) return;
    let srv = allServices.find(s => s.id == app.service_id);
    if (!srv) {
        srv = allServices.find(s => s.main_service_name === app.service_name && s.sub_service_name === app.sub_service);
    }
    if (!srv) {
        srv = allServices.find(s => s.main_service_name === app.service_name);
    }
    if (!srv && allServices && allServices.length > 0) {
        srv = allServices[0];
    }
    if (!srv) {
        alert('Information not available for this service.');
        return;
    }
    openDocsModalWithService(srv);
}

function onModalServiceSwitch(serviceId) {
    const srv = allServices.find(s => s.id == serviceId);
    if (srv) {
        openDocsModalWithService(srv);
    }
}

function applyModalServiceToActiveRow() {
    if (!currentSelectedModalService) return;
    const srv = currentSelectedModalService;
    const mainSel = document.getElementById('se_main_service');
    if (mainSel) {
        mainSel.value = srv.main_service_name;
        onMainServiceChange(srv.main_service_name);
        setTimeout(() => {
            const subSel = document.getElementById('se_sub_service_select');
            if (subSel) {
                subSel.value = srv.id;
                onSubServiceChange(srv.id);
            }
        }, 60);
    }
    closeDocsModal();
}

function openDocsModalWithService(srv) {
    currentSelectedModalService = srv;

    const modalTitle = document.getElementById('modal-service-title');
    const modalSubTitle = document.getElementById('modal-sub-service-title');
    const modalDays = document.getElementById('modal-service-days');
    const modalRate = document.getElementById('modal-service-rate');
    const docsContainer = document.getElementById('modal-docs-container');
    const btnPortal = document.getElementById('modal-btn-portal');
    const btnTutorial = document.getElementById('modal-btn-tutorial');
    const switcher = document.getElementById('modal-service-switcher');

    const mainName = srv.main_service_name || 'Service';
    const subName = srv.sub_service_name || '';
    const days = parseInt(srv.expected_processing_days) || 7;
    const price = parseFloat(srv.price) || 0;

    if (modalTitle) modalTitle.innerText = subName ? `${mainName} - ${subName}` : mainName;
    if (modalSubTitle) modalSubTitle.innerText = 'Required Documents Checklist';

    let docsArr = [];
    const rawDocs = srv ? (srv.required_documents_json || srv.required_documents) : null;
    if (rawDocs) {
        if (Array.isArray(rawDocs)) {
            docsArr = rawDocs;
        } else if (typeof rawDocs === 'string') {
            try {
                const parsed = JSON.parse(rawDocs);
                if (Array.isArray(parsed)) docsArr = parsed;
            } catch(e) {
                docsArr = rawDocs.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);
            }
        }
    }
    currentModalDocs = docsArr;

    if (docsContainer) {
        if (docsArr.length > 0) {
            docsContainer.innerHTML = docsArr.map(doc => {
                const docText = typeof doc === 'object' ? (doc.name || JSON.stringify(doc)) : doc;
                return `
                    <div class="doc-check-item" style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 8px;">
                        <i class="fa-solid fa-circle-check" style="color: #16a34a; font-size: 16px; margin-top: 2px;"></i>
                        <span style="font-size: 13.5px; font-weight: 600; color: #1e293b; line-height: 1.4;">${escapeHtml(docText)}</span>
                    </div>
                `;
            }).join('');
        } else {
            docsContainer.innerHTML = `
                <div style="text-align: center; padding: 24px 10px; color: #64748b;">
                    <i class="fa-solid fa-file-circle-check" style="font-size: 32px; color: #94a3b8; margin-bottom: 8px; display: block;"></i>
                    <p style="margin: 0; font-size: 13.5px; font-weight: 600;">No specific documents required for this service.</p>
                </div>
            `;
        }
    }

    const overlay = document.getElementById('docs-modal-overlay');
    if (overlay) {
        overlay.classList.add('open');
        overlay.style.setProperty('display', 'flex', 'important');
        overlay.style.setProperty('opacity', '1', 'important');
        overlay.style.setProperty('visibility', 'visible', 'important');
        overlay.style.setProperty('z-index', '999999999', 'important');
    }
}

function closeDocsModal(e) {
    if (e && e.target && e.target.id !== 'docs-modal-overlay' && !e.target.classList.contains('modal-close-btn') && !e.target.closest('.modal-close-btn') && e.target.tagName !== 'BUTTON') {
        return;
    }
    const overlay = document.getElementById('docs-modal-overlay');
    if (overlay) {
        overlay.classList.remove('open');
        overlay.style.setProperty('display', 'none', 'important');
        overlay.style.setProperty('opacity', '0', 'important');
        overlay.style.setProperty('visibility', 'hidden', 'important');
    }
}

function copyModalDocsToClipboard(btn) {
    if (!currentModalDocs || currentModalDocs.length === 0) {
        alert('No documents available to copy.');
        return;
    }

    const srv = currentSelectedModalService;
    const mainName = srv ? (srv.main_service_name || 'Service') : 'Service';
    const subName = srv ? (srv.sub_service_name || '') : '';
    const title = subName ? `${mainName} - ${subName}` : mainName;

    let text = `📄 Required Documents Checklist\n`;
    text += `Service: ${title}\n\n`;
    currentModalDocs.forEach(doc => {
        const docName = typeof doc === 'object' ? (doc.name || JSON.stringify(doc)) : doc;
        text += `• ${docName}\n`;
    });
    text += `\nMaha E-Seva Kendra`;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(onSuccess).catch(() => fallbackCopy(text));
    } else {
        fallbackCopy(text);
    }

    function fallbackCopy(str) {
        const ta = document.createElement('textarea');
        ta.value = str;
        ta.style.position = 'fixed';
        ta.style.left = '-999999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            onSuccess();
        } catch (err) {
            alert('Failed to copy. Please copy manually.');
        }
        ta.remove();
    }

    function onSuccess() {
        if (btn) {
            const origHtml = btn.innerHTML;
            btn.innerHTML = `<i class="fa-solid fa-check"></i> Copied to Clipboard!`;
            btn.style.backgroundColor = '#059669';
            setTimeout(() => {
                btn.innerHTML = origHtml;
                btn.style.backgroundColor = '#16a34a';
            }, 2500);
        }
    }
}

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('docs-modal-overlay');
        if (overlay) {
            overlay.classList.remove('open');
            overlay.style.setProperty('display', 'none', 'important');
        }
    }
});

// ==========================================
// 6. INITIAL LOAD BOOTSTRAP
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('services-tbody');
    const activeRow = document.getElementById('active-service-row');

    if (initialApps && initialApps.length > 0) {
        initialApps.forEach(app => {
            loadedApplications[app.id] = app;
            const tr = createCommittedRowElement(app);
            tbody.appendChild(tr);
        });
    }

    updateStatusDatalistForService('DEFAULT');
    const seStatus = document.getElementById('se_status');
    if (seStatus) {
        seStatus.value = '';
        applyStatusInputStyle(seStatus);
    }

    if (window.ExcelTableFilter) {
        window.customerShowFilter = new ExcelTableFilter({
            table: '#customer-services-table',
            rowSelector: 'tbody tr.committed-row',
            ignoreRowSelector: '#active-service-row',
            clearBtnContainer: '#customer-show-filter-toolbar',
            columns: {
                'main_service': { label: 'Main Service', type: 'text' },
                'sub_service': { label: 'Sub Service', type: 'text' },
                'status': { label: 'Status', type: 'text' },
                'operator': { label: 'Operator', type: 'text' },
                'followup_date': { label: 'Follow-Up Date', type: 'date' }
            },
            onFilterChange: function({ visibleCount, totalCount, activeFilterCount }) {
                const badge = document.getElementById('services-badge');
                if (badge) {
                    if (activeFilterCount > 0) {
                        badge.innerText = `${visibleCount} of ${totalCount} Services`;
                    } else {
                        badge.innerText = `${totalCount} Services`;
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
