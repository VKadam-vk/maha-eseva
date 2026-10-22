@extends('layouts.app')

@section('title', 'Follow-up Services Desk')

@section('content')
<style>
    /* ========================================================================= */
    /* FOLLOW-UP SERVICES SPREADSHEET DESK                                       */
    /* ========================================================================= */
    .followup-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 100%;
        margin: 0 auto;
    }

    /* Top Header & Date Navigation Bar */
    .followup-nav-bar {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        padding: 14px 18px;
        box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .followup-title-block h1 {
        margin: 0;
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.3px;
    }

    .followup-title-block p {
        margin: 3px 0 0 0;
        font-size: 12.5px;
        color: #64748b;
        font-weight: 500;
    }

    /* Quick Date Switcher Pills */
    .date-pills-group {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 10px;
        gap: 4px;
        border: 1px solid #e2e8f0;
    }

    .date-pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 7px;
        text-decoration: none;
        color: #475569;
        transition: all 0.18s ease;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .date-pill-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.7);
    }

    .date-pill-btn.active {
        background: #0284c7;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
    }

    .date-pill-badge {
        padding: 1px 6px;
        font-size: 10.5px;
        font-weight: 800;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.08);
        color: inherit;
    }

    .date-pill-btn.active .date-pill-badge {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    .date-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 4px 10px;
    }

    .date-picker-wrapper input[type="date"] {
        border: none;
        background: transparent;
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        outline: none;
        cursor: pointer;
        font-family: inherit;
    }

    /* Summary Metrics Strip */
    .metrics-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }

    .metric-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .metric-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .metric-info {
        display: flex;
        flex-direction: column;
    }

    .metric-label {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #64748b;
    }

    .metric-value {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        font-family: var(--font-mono, monospace);
        line-height: 1.2;
    }

    /* Fast Entry Style Spreadsheet Card */
    .sheet-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06);
    }

    .sheet-card-header {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1.5px solid #cbd5e1;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .sheet-table-wrap {
        overflow-x: auto;
        position: relative;
    }

    .sheet-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
        table-layout: auto;
    }

    .sheet-table thead th {
        background: #f8fafc;
        color: #334155;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 9px 8px;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .sheet-table tbody td {
        padding: 4px 5px;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #f1f5f9;
        vertical-align: middle;
        background: #ffffff;
    }

    .sheet-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Sticky Right Columns: Status & Actions */
    .sticky-status-th {
        position: sticky;
        right: 80px;
        background: #f8fafc !important;
        z-index: 5;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.05);
        min-width: 130px;
        width: 130px;
    }

    .sticky-status-td {
        position: sticky;
        right: 80px;
        background: #ffffff;
        z-index: 5;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.05);
        min-width: 130px;
        width: 130px;
    }

    .sheet-table tbody tr:hover .sticky-status-td {
        background-color: #f8fafc !important;
    }

    .sticky-action-th {
        position: sticky;
        right: 0;
        background: #f8fafc !important;
        z-index: 6;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.05);
        min-width: 80px;
        width: 80px;
        text-align: center;
    }

    .sticky-action-td {
        position: sticky;
        right: 0;
        background: #ffffff;
        z-index: 6;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.05);
        min-width: 80px;
        width: 80px;
        text-align: center;
    }

    .sheet-table tbody tr:hover .sticky-action-td {
        background-color: #f8fafc !important;
    }

    /* Fast Entry Input Styles */
    .excel-input, .excel-select {
        width: 100%;
        font-size: 12px;
        padding: 4px 6px;
        border: 1px solid transparent;
        border-radius: 4px;
        background: transparent;
        color: #0f172a;
        outline: none;
        transition: all 0.15s ease;
        box-sizing: border-box;
    }

    .excel-input:hover, .excel-select:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .excel-input:focus, .excel-select:focus {
        border-color: #0284c7;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    .excel-work-details {
        width: 100%;
        min-height: 28px;
        font-size: 11.5px;
        padding: 4px 6px;
        border: 1px solid transparent;
        border-radius: 4px;
        background: transparent;
        color: #0f172a;
        resize: none;
        overflow-y: hidden;
        outline: none;
        line-height: 1.35;
        font-family: inherit;
        box-sizing: border-box;
    }

    .excel-work-details:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .excel-work-details:focus {
        border-color: #0284c7;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    /* Status Combobox Style */
    .status-combobox {
        cursor: pointer;
        border-radius: 6px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 4px 6px !important;
        text-align: center;
        border: 1.5px solid transparent !important;
    }

    .status-combobox:hover, .status-combobox:focus {
        border-color: #0284c7 !important;
    }

    /* Button Doc Eye */
    .btn-doc-eye {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 5px;
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    .btn-doc-eye:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        transform: scale(1.05);
    }

    /* Empty State */
    .empty-state-box {
        padding: 48px 24px;
        text-align: center;
        background: #ffffff;
    }

    .empty-state-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin: 0 auto 14px auto;
    }

    /* Required Documents Popup Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .docs-modal-card {
        background: #ffffff;
        border-radius: 14px;
        max-width: 580px;
        width: 100%;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        border: 1.5px solid #cbd5e1;
        animation: modalScaleIn 0.18s ease-out;
    }

    @keyframes modalScaleIn {
        from { transform: scale(0.96); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .docs-modal-header {
        background: #0f172a;
        color: #ffffff;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .docs-modal-body {
        padding: 18px;
        overflow-y: auto;
    }

    .docs-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .modal-close-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 20px;
        cursor: pointer;
        line-height: 1;
        padding: 4px;
        transition: color 0.15s ease;
    }

    .modal-close-btn:hover {
        color: #ffffff;
    }
</style>

<div class="followup-container">

    <!-- Top Header & Date Navigation Bar -->
    <div class="followup-nav-bar">
        <div class="followup-title-block">
            <h1>
                <i class="fa-solid fa-calendar-check" style="color: #0284c7;"></i>
                Follow-up Services Desk
            </h1>
            <p>
                @if($mode === 'overdue')
                    <span style="color: #dc2626; font-weight: 700;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Overdue Follow-ups (Pending from past dates)
                    </span>
                @elseif($mode === 'all')
                    <span style="color: #0284c7; font-weight: 700;">
                        <i class="fa-solid fa-list-check"></i> All Scheduled Follow-ups
                    </span>
                @elseif($selectedDate === $today)
                    <span style="color: #16a34a; font-weight: 700;">
                        <i class="fa-solid fa-circle-check"></i> Today's Follow-up Services ({{ \Carbon\Carbon::parse($today)->format('d M Y') }})
                    </span>
                @elseif($selectedDate === $tomorrow)
                    <span style="color: #0284c7; font-weight: 700;">
                        <i class="fa-regular fa-clock"></i> Tomorrow's Follow-up Services ({{ \Carbon\Carbon::parse($tomorrow)->format('d M Y') }})
                    </span>
                @else
                    <span style="color: #475569; font-weight: 700;">
                        <i class="fa-solid fa-calendar-day"></i> Date: {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                    </span>
                @endif
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
            <!-- Quick Date Pills (Today, Tomorrow, Overdue, All) -->
            <div class="date-pills-group">
                <!-- Today Pill -->
                <a href="{{ route('followups.index', ['date' => $today]) }}"
                   class="date-pill-btn {{ (!$mode && $selectedDate === $today) ? 'active' : '' }}"
                   title="Today's Follow-ups">
                    <i class="fa-solid fa-bolt"></i> Today
                    <span class="date-pill-badge">{{ $todayCount }}</span>
                </a>

                <!-- Tomorrow Pill -->
                <a href="{{ route('followups.index', ['date' => $tomorrow]) }}"
                   class="date-pill-btn {{ (!$mode && $selectedDate === $tomorrow) ? 'active' : '' }}"
                   title="Tomorrow's Follow-ups">
                    <i class="fa-regular fa-calendar-plus"></i> Tomorrow
                    <span class="date-pill-badge">{{ $tomorrowCount }}</span>
                </a>

                <!-- Overdue Pill -->
                <a href="{{ route('followups.index', ['mode' => 'overdue']) }}"
                   class="date-pill-btn {{ $mode === 'overdue' ? 'active' : '' }}"
                   title="Overdue (Pending from past dates)">
                    <i class="fa-solid fa-triangle-exclamation" style="color: {{ $mode === 'overdue' ? '#fff' : '#e11d48' }};"></i> Overdue
                    <span class="date-pill-badge" style="background: {{ $mode === 'overdue' ? 'rgba(255,255,255,0.3)' : '#fee2e2' }}; color: {{ $mode === 'overdue' ? '#fff' : '#b91c1c' }};">{{ $overdueCount }}</span>
                </a>

                <!-- All Pill -->
                <a href="{{ route('followups.index', ['mode' => 'all']) }}"
                   class="date-pill-btn {{ $mode === 'all' ? 'active' : '' }}"
                   title="All Scheduled Follow-ups">
                    <i class="fa-solid fa-list-ul"></i> All
                    <span class="date-pill-badge">{{ $allCount }}</span>
                </a>
            </div>

            <!-- Custom Date Picker -->
            <div class="date-picker-wrapper" title="Pick a specific date to view follow-ups">
                <i class="fa-regular fa-calendar" style="color: #64748b; font-size: 13px;"></i>
                <input type="date" value="{{ $selectedDate ?? $today }}"
                       onchange="window.location.href = '{{ route('followups.index') }}?date=' + this.value;">
            </div>
        </div>
    </div>

    <!-- Summary Metrics Strip for the currently filtered items -->
    <div class="metrics-strip">
        <div class="metric-card">
            <div class="metric-icon" style="background: #e0f2fe; color: #0284c7;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">Scheduled Services</span>
                <span class="metric-value" id="card-total-count">{{ $totalCount }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: #f1f5f9; color: #475569;">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">Total Billed</span>
                <span class="metric-value font-mono" id="card-total-billed">₹ {{ number_format($totalBilled, 2) }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: #f0fdf4; color: #16a34a;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">Total Received</span>
                <span class="metric-value font-mono" id="card-total-received" style="color: #166534;">₹ {{ number_format($totalReceived, 2) }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: #fef2f2; color: #dc2626;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">Remaining Balance</span>
                <span class="metric-value font-mono" id="card-total-remaining" style="color: {{ $totalRemaining > 0 ? '#b91c1c' : '#16a34a' }};">
                    ₹ {{ number_format($totalRemaining, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Fast Entry Spreadsheet Table for Follow-ups -->
    <div class="sheet-card">
        <div class="sheet-card-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-table-list" style="color: #0284c7; font-size: 15px;"></i>
                <span style="font-size: 13.5px; font-weight: 800; color: #1e293b;">
                    @if($selectedDate === $today && !$mode)
                        Today's Follow-up Services
                    @elseif($selectedDate === $tomorrow && !$mode)
                        Tomorrow's Follow-up Services
                    @elseif($mode === 'overdue')
                        Overdue Follow-up Services
                    @elseif($mode === 'all')
                        All Scheduled Services
                    @else
                        Scheduled Services for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                    @endif
                </span>
                <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; border-radius: 12px; padding: 2px 8px;">
                    {{ $totalCount }} Records
                </span>
            </div>

            <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">
                <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Changes auto-save immediately
            </div>
        </div>

        <div class="sheet-table-wrap">
            <table class="sheet-table">
                <thead>
                    <tr>
                        <th style="min-width: 170px; width: 175px;">Customer (Name & Contact)</th>
                        <th style="min-width: 190px; width: 200px;">Service & Sub Service</th>
                        <th style="text-align: center; min-width: 75px; width: 80px;">Days</th>
                        <th style="text-align: right; min-width: 80px; width: 85px;">Rate (₹)</th>
                        <th style="text-align: right; min-width: 85px; width: 90px;">Received</th>
                        <th style="text-align: right; min-width: 85px; width: 90px;">Remaining</th>
                        <th style="min-width: 160px;">Work Details</th>
                        <th style="min-width: 125px; width: 130px;">Operator</th>
                        <th style="min-width: 115px; width: 120px;">Follow-Up</th>
                        <th class="sticky-status-th" style="text-align: center;">Status</th>
                        <th class="sticky-action-th">Action</th>
                    </tr>
                </thead>
                <tbody id="followup-sheet-tbody">
                    @forelse($applications as $app)
                        @php
                            $rate = (float) ($app->total_amount ?? $app->rate ?? 0);
                            $received = (float) ($app->received_amount ?? 0);
                            $rem = max(0, $rate - $received);
                            $cust = $app->customer;
                            $serv = $app->service;
                            $expDays = (int) ($serv?->expected_processing_days ?? 7);
                            $appDate = $app->follow_up_date ? \Carbon\Carbon::parse($app->follow_up_date)->format('Y-m-d') : '';
                        @endphp
                        <tr id="row-app-{{ $app->id }}" data-app-id="{{ $app->id }}">
                            <!-- 1. Customer Name & Contact -->
                            <td style="padding: 6px 8px;">
                                @if($cust)
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                        <a href="{{ route('customers.show', $cust->id) }}"
                                           style="font-weight: 800; color: #0f172a; text-decoration: none; font-size: 12.5px; line-height: 1.25;"
                                           title="Open customer profile in new tab" target="_blank">
                                            {{ $cust->name }}
                                        </a>
                                        @if($cust->mobile)
                                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $cust->mobile) }}" target="_blank"
                                               style="color: #16a34a; font-size: 13px; text-decoration: none;" title="Send WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-top: 2px;">
                                        <span style="font-family: var(--font-mono); font-weight: 600;">{{ $cust->customer_code }}</span>
                                        @if($cust->mobile)
                                            <span>&bull; {{ $cust->mobile }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">No Customer</span>
                                @endif
                            </td>

                            <!-- 2. Service & Sub Service -->
                            <td style="padding: 4px 6px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 12px; line-height: 1.25; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $serv?->main_service_name ?? 'Service' }}
                                        @if($app->sub_service)
                                            <div style="font-size: 11px; font-weight: 500; color: #0284c7;">
                                                {{ $app->sub_service }}
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn-doc-eye"
                                            onclick="openDocsModalForService({{ $serv?->id ?? 'null' }}, '{{ addslashes($serv?->main_service_name ?? '') }}', '{{ addslashes($app->sub_service ?? '') }}', {{ $expDays }}, {{ $rate }})"
                                            title="View Required Documents">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- 3. Expected Days Badge -->
                            <td style="text-align: center; padding: 4px 6px; white-space: nowrap;">
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 10.5px; padding: 2px 6px; border-radius: 4px;">
                                    ⏱️ {{ $expDays }}d
                                </span>
                            </td>

                            <!-- 4. Rate (₹) -->
                            <td>
                                <input type="number" step="0.01" class="excel-input font-mono font-bold"
                                       id="rate_{{ $app->id }}" value="{{ $rate }}"
                                       style="text-align: right;"
                                       oninput="calculateRowRemaining({{ $app->id }})"
                                       onchange="saveRowField({{ $app->id }}, 'rate', this.value)">
                            </td>

                            <!-- 5. Received (₹) -->
                            <td>
                                <input type="number" step="0.01" class="excel-input font-mono font-bold"
                                       id="received_{{ $app->id }}" value="{{ $received }}"
                                       style="text-align: right; color: #166534;"
                                       oninput="calculateRowRemaining({{ $app->id }})"
                                       onchange="saveRowField({{ $app->id }}, 'received', this.value)">
                            </td>

                            <!-- 6. Remaining (₹) -->
                            <td>
                                <input type="text" class="excel-input font-mono font-bold"
                                       id="remaining_{{ $app->id }}"
                                       value="₹ {{ number_format($rem, 2) }}" readonly tabindex="-1"
                                       style="text-align: right; background: #f8fafc; color: {{ $rem > 0 ? '#b91c1c' : '#166534' }};">
                            </td>

                            <!-- 7. Work Details -->
                            <td>
                                <textarea class="excel-work-details" id="work_details_{{ $app->id }}"
                                          placeholder="Notes / instructions..."
                                          rows="1" oninput="autoExpandWorkDetails(this)"
                                          onchange="saveRowField({{ $app->id }}, 'work_details', this.value)">{{ $app->work_details }}</textarea>
                            </td>

                            <!-- 8. Operator -->
                            <td>
                                <select class="excel-select font-semibold" id="operator_{{ $app->id }}"
                                        onchange="saveRowField({{ $app->id }}, 'assigned_employee_id', this.value)">
                                    <option value="">-- Staff --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $app->assigned_employee_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->user?->name ?? 'Staff #' . $emp->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- 9. Follow-Up Date -->
                            <td>
                                <input type="date" class="excel-input" id="followup_{{ $app->id }}"
                                       value="{{ $appDate }}"
                                       style="font-size: 11.5px; font-weight: 600; color: #475569;"
                                       onchange="saveRowField({{ $app->id }}, 'follow_up_date', this.value)">
                            </td>

                            <!-- 10. Status (Sticky Right) -->
                            <td class="sticky-status-td" style="padding: 4px 6px;">
                                <select class="excel-select font-bold status-select"
                                        id="status_{{ $app->id }}"
                                        data-prev="{{ $app->work_status ?? 'PENDING' }}"
                                        onchange="onStatusSelectChange(this, {{ $app->id }})">
                                    @php
                                        $curSt = strtoupper(trim($app->work_status ?? 'PENDING'));
                                        $knownStatuses = [
                                            'PENDING' => 'PENDING',
                                            'IN_PROGRESS' => 'IN_PROGRESS',
                                            'WAITING_FOR_DOCS' => 'WAITING FOR DOCS',
                                            'SUBMITTED' => 'SUBMITTED',
                                            'READY' => 'READY',
                                            'DELIVERED' => 'DELIVERED',
                                            'COMPLETED' => 'COMPLETED',
                                            'REJECTED' => 'REJECTED',
                                        ];
                                    @endphp
                                    @if(!array_key_exists($curSt, $knownStatuses) && !empty($curSt))
                                        <option value="{{ $curSt }}" selected>{{ $curSt }}</option>
                                    @endif
                                    @foreach($knownStatuses as $stKey => $stLabel)
                                        <option value="{{ $stKey }}" {{ $curSt === $stKey ? 'selected' : '' }}>
                                            {{ $stLabel }}
                                        </option>
                                    @endforeach
                                    <option value="__NEW_CUSTOM__">➕ Custom Status...</option>
                                </select>
                            </td>

                            <!-- 11. Actions (Sticky Right) -->
                            <td class="sticky-action-td" style="text-align: center; padding: 4px 6px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    @if($serv?->portal_link)
                                        <a href="{{ $serv->portal_link }}" target="_blank"
                                           class="btn btn-sm" style="padding: 3px 6px; font-size: 11px; background: #e0f2fe; color: #0369a1; border-radius: 4px; border: 1px solid #bae6fd;"
                                           title="Open Government Portal">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @endif

                                    @if($cust)
                                        <a href="{{ route('customers.show', $cust->id) }}" target="_blank"
                                           class="btn btn-sm" style="padding: 3px 6px; font-size: 11px; background: #f1f5f9; color: #334155; border-radius: 4px; border: 1px solid #cbd5e1;"
                                           title="View Full Profile">
                                            <i class="fa-solid fa-user"></i>
                                        </a>
                                    @endif

                                    <button type="button" class="btn btn-sm"
                                            style="padding: 3px 6px; font-size: 11px; background: #dcfce7; color: #166534; border-radius: 4px; border: 1px solid #bbf7d0;"
                                            onclick="markServiceComplete({{ $app->id }})"
                                            title="Mark as Completed / Delivered">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state-box">
                                    <div class="empty-state-icon">
                                        <i class="fa-regular fa-calendar-xmark"></i>
                                    </div>
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;">
                                        No Follow-up Scheduled For This Date
                                    </h4>
                                    <p style="margin: 6px 0 16px 0; font-size: 12.5px; color: #64748b;">
                                        @if($selectedDate === $today && !$mode)
                                            No pending follow-ups found for today ({{ \Carbon\Carbon::parse($today)->format('d M Y') }}).
                                        @else
                                            No follow-up services scheduled for the selected date ({{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}).
                                        @endif
                                    </p>
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <a href="{{ route('followups.index', ['date' => $today]) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-bolt"></i> View Today's Follow-ups
                                        </a>
                                        <a href="{{ route('followups.index', ['date' => $tomorrow]) }}" class="btn btn-sm btn-secondary">
                                            <i class="fa-regular fa-calendar-plus"></i> View Tomorrow's Follow-ups
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Datalist for Status Combobox -->
<datalist id="status-datalist">
    <option value="PENDING">Pending</option>
    <option value="IN_PROGRESS">In Progress</option>
    <option value="READY">Ready</option>
    <option value="DELIVERED">Delivered</option>
    <option value="COMPLETED">Completed</option>
    <option value="WAITING_FOR_DOCS">Waiting for Documents</option>
    <option value="REJECTED">Rejected</option>
</datalist>

<!-- REQUIRED DOCUMENTS POPUP MODAL -->
<div id="docs-modal-overlay" class="modal-overlay" style="display: none;" onclick="closeDocsModal(event)">
    <div class="docs-modal-card" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="docs-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(2, 132, 199, 0.25); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <div>
                    <h3 id="modal-service-title" style="margin: 0; font-size: 14.5px; font-weight: 700; color: #ffffff;">Required Documents</h3>
                    <p id="modal-sub-service-title" style="margin: 2px 0 0 0; font-size: 11px; color: #94a3b8; font-weight: 500;">Required Documents & Eligibility Details</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDocsModal()" title="Close">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div class="docs-modal-body">
            <!-- Service Switcher Selector -->
            <div style="margin-bottom: 12px; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1.5px solid #cbd5e1; display: flex; align-items: center; gap: 8px;">
                <label for="modal-service-switcher" style="font-size: 11.5px; font-weight: 700; color: #334155; white-space: nowrap; margin: 0;">
                    <i class="fa-solid fa-layer-group" style="color: #0284c7;"></i> Switch / Select Service:
                </label>
                <select id="modal-service-switcher" class="form-select form-select-sm" style="font-size: 12px; font-weight: 600; flex: 1; border-color: #cbd5e1; background: #ffffff;" onchange="onModalServiceSwitch(this.value)">
                </select>
            </div>

            <!-- Badges Bar (Expected Days & Fee) -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 12px; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #0369a1;">
                    <i class="fa-regular fa-clock"></i>
                    <span id="modal-service-days">⏱️ Expected: 7 Days</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 800; color: #166534;">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                    <span id="modal-service-rate">₹ 0</span>
                </div>
            </div>

            <!-- Required Documents List -->
            <div style="margin-bottom: 14px;">
                <h4 style="margin: 0 0 8px 0; font-size: 12.5px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-clipboard-check" style="color: #16a34a;"></i>
                    Required Documents Checklist:
                </h4>
                <div id="modal-docs-container" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; min-height: 80px;">
                    <!-- Injected via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="docs-modal-footer">
            <div style="display: flex; gap: 8px;">
                <a id="modal-btn-apply" href="#" target="_blank" class="btn btn-sm"
                   style="background: #0284c7; color: #ffffff; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Portal Apply
                </a>
                <a id="modal-btn-tutorial" href="#" target="_blank" class="btn btn-sm"
                   style="background: #dc2626; color: #ffffff; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa-brands fa-youtube"></i> Tutorial
                </a>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeDocsModal()" style="font-weight: 700; font-size: 11.5px;">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Master Services Bootstrap
    window.MASTER_SERVICES = @json($activeServices);

    document.addEventListener('DOMContentLoaded', function () {
        // Apply status colors to all status selectors
        document.querySelectorAll('.status-select, .status-combobox').forEach(el => applyStatusInputStyle(el));

        // Auto-expand all work details
        document.querySelectorAll('.excel-work-details').forEach(el => autoExpandWorkDetails(el));
    });

    // Auto-expand textarea
    function autoExpandWorkDetails(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = Math.max(28, el.scrollHeight) + 'px';
    }

    // Dynamic styling for status select/input
    function applyStatusInputStyle(el) {
        if (!el) return;
        const val = (el.value || '').toUpperCase().trim();
        let bg = '#f1f5f9', color = '#475569', border = '#cbd5e1';

        if (val.includes('PENDING') || val.includes('WAITING') || val.includes('NEW')) {
            bg = '#fef3c7'; color = '#92400e'; border = '#fcd34d';
        } else if (val.includes('PROGRESS') || val.includes('PROCESS') || val.includes('SUBMIT')) {
            bg = '#e0f2fe'; color = '#0369a1'; border = '#7dd3fc';
        } else if (val.includes('READY')) {
            bg = '#ede9fe'; color = '#6d28d9'; border = '#c4b5fd';
        } else if (val.includes('DELIVERED') || val.includes('COMPLETED') || val.includes('SUCCESS') || val.includes('APPROVED')) {
            bg = '#dcfce7'; color = '#166534'; border = '#86efac';
        } else if (val.includes('REJECT') || val.includes('CANCEL')) {
            bg = '#fee2e2'; color = '#991b1b'; border = '#fca5a5';
        } else {
            bg = '#f3e8ff'; color = '#6b21a8'; border = '#d8b4fe';
        }

        el.style.backgroundColor = bg;
        el.style.color = color;
        el.style.borderColor = border;
    }

    // Handle single-click status change and custom status addition
    function onStatusSelectChange(selectEl, appId) {
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
        saveRowField(appId, 'work_status', selectEl.value);
    }

    // Real-time calculation of remaining balance for a row
    function calculateRowRemaining(appId) {
        const rateEl = document.getElementById(`rate_${appId}`);
        const recvEl = document.getElementById(`received_${appId}`);
        const remEl = document.getElementById(`remaining_${appId}`);
        if (!rateEl || !recvEl || !remEl) return;

        const rate = parseFloat(rateEl.value) || 0;
        const recv = parseFloat(recvEl.value) || 0;
        const rem = Math.max(0, rate - recv);

        remEl.value = '₹ ' + rem.toFixed(2);
        remEl.style.color = rem > 0 ? '#b91c1c' : '#166534';

        recalculateFollowupMetrics();
    }

    // Save individual field on change (Auto-save)
    function saveRowField(appId, field, value) {
        const row = document.getElementById(`row-app-${appId}`);
        if (!row) return;

        const rate = parseFloat(document.getElementById(`rate_${appId}`)?.value || 0);
        const received = parseFloat(document.getElementById(`received_${appId}`)?.value || 0);
        const workDetails = document.getElementById(`work_details_${appId}`)?.value || null;
        const operatorId = document.getElementById(`operator_${appId}`)?.value || null;
        const followUpDate = document.getElementById(`followup_${appId}`)?.value || null;
        const statusVal = document.getElementById(`status_${appId}`)?.value || 'PENDING';

        const payload = {
            application_id: appId,
            rate: rate,
            received: received,
            work_details: workDetails,
            assigned_employee_id: operatorId,
            follow_up_date: followUpDate,
            work_status: statusVal
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
                // Visual subtle confirmation
                row.style.transition = 'background-color 0.3s ease';
                row.style.backgroundColor = '#ecfdf5';
                setTimeout(() => { row.style.backgroundColor = ''; }, 600);
                recalculateFollowupMetrics();
            } else {
                alert('Error updating row: ' + (res.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Save error:', err);
        });
    }

    // Mark as completed quick action
    function markServiceComplete(appId) {
        const statusInput = document.getElementById(`status_${appId}`);
        if (statusInput) {
            statusInput.value = 'COMPLETED';
            applyStatusInputStyle(statusInput);
            saveRowField(appId, 'work_status', 'COMPLETED');
        }
    }

    // Recalculate summary metrics across all visible rows
    function recalculateFollowupMetrics() {
        let totalBilled = 0;
        let totalReceived = 0;
        let count = 0;

        document.querySelectorAll('#followup-sheet-tbody tr[data-app-id]').forEach(row => {
            const appId = row.getAttribute('data-app-id');
            const rate = parseFloat(document.getElementById(`rate_${appId}`)?.value || 0);
            const recv = parseFloat(document.getElementById(`received_${appId}`)?.value || 0);
            totalBilled += rate;
            totalReceived += recv;
            count++;
        });

        const remaining = Math.max(0, totalBilled - totalReceived);

        const cardCount = document.getElementById('card-total-count');
        const cardBilled = document.getElementById('card-total-billed');
        const cardReceived = document.getElementById('card-total-received');
        const cardRemaining = document.getElementById('card-total-remaining');

        if (cardCount) cardCount.textContent = count;
        if (cardBilled) cardBilled.textContent = '₹ ' + totalBilled.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        if (cardReceived) cardReceived.textContent = '₹ ' + totalReceived.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        if (cardRemaining) {
            cardRemaining.textContent = '₹ ' + remaining.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            cardRemaining.style.color = remaining > 0 ? '#b91c1c' : '#166534';
        }
    }

    /* ========================================================================= */
    /* REQUIRED DOCUMENTS POPUP MODAL LOGIC                                      */
    /* ========================================================================= */
    function openDocsModalForService(serviceId, serviceName, subServiceName, expectedDays, rate) {
        const modal = document.getElementById('docs-modal-overlay');
        const titleEl = document.getElementById('modal-service-title');
        const subTitleEl = document.getElementById('modal-sub-service-title');
        const daysEl = document.getElementById('modal-service-days');
        const rateEl = document.getElementById('modal-service-rate');
        const docsContainer = document.getElementById('modal-docs-container');
        const switcher = document.getElementById('modal-service-switcher');
        const btnApply = document.getElementById('modal-btn-apply');
        const btnTutorial = document.getElementById('modal-btn-tutorial');

        if (!modal) return;

        // Populate service switcher dropdown
        if (switcher && switcher.options.length <= 1) {
            switcher.innerHTML = '';
            window.MASTER_SERVICES.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.main_service_name;
                switcher.appendChild(opt);
            });
        }
        if (switcher && serviceId) {
            switcher.value = serviceId;
        }

        const service = window.MASTER_SERVICES.find(s => s.id == serviceId);
        renderModalServiceDetails(service, subServiceName, expectedDays, rate);
        modal.style.display = 'flex';
    }

    function renderModalServiceDetails(service, subServiceName, fallbackDays, fallbackRate) {
        const titleEl = document.getElementById('modal-service-title');
        const subTitleEl = document.getElementById('modal-sub-service-title');
        const daysEl = document.getElementById('modal-service-days');
        const rateEl = document.getElementById('modal-service-rate');
        const docsContainer = document.getElementById('modal-docs-container');
        const btnApply = document.getElementById('modal-btn-apply');
        const btnTutorial = document.getElementById('modal-btn-tutorial');

        const sName = service ? service.main_service_name : 'Service Details';
        const days = service ? (service.expected_processing_days || 7) : (fallbackDays || 7);
        const rate = service ? (service.price || 0) : (fallbackRate || 0);

        if (titleEl) titleEl.textContent = sName;
        if (subTitleEl) subTitleEl.textContent = subServiceName ? `${sName} - ${subServiceName}` : 'Required Documents & Details';
        if (daysEl) daysEl.innerHTML = `<i class="fa-regular fa-clock"></i> ⏱️ Expected: ${days} Days`;
        if (rateEl) rateEl.innerHTML = `<i class="fa-solid fa-indian-rupee-sign"></i> ₹ ${parseFloat(rate).toFixed(2)}`;

        // Links
        if (btnApply) {
            if (service && service.portal_link) {
                btnApply.href = service.portal_link;
                btnApply.style.display = 'inline-flex';
            } else {
                btnApply.style.display = 'none';
            }
        }
        if (btnTutorial) {
            if (service && service.youtube_tutorial_link) {
                btnTutorial.href = service.youtube_tutorial_link;
                btnTutorial.style.display = 'inline-flex';
            } else {
                btnTutorial.style.display = 'none';
            }
        }

        // Render Docs
        if (docsContainer) {
            let docsHtml = '';
            let docs = [];

            if (service && service.required_documents) {
                if (Array.isArray(service.required_documents)) {
                    docs = service.required_documents;
                } else if (typeof service.required_documents === 'string') {
                    try {
                        const parsed = JSON.parse(service.required_documents);
                        if (Array.isArray(parsed)) docs = parsed;
                    } catch (e) {
                        docs = service.required_documents.split(/[\n,]+/).map(d => d.trim()).filter(Boolean);
                    }
                }
            }

            if (docs.length > 0) {
                docsHtml = '<ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 6px;">';
                docs.forEach(doc => {
                    const docText = typeof doc === 'object' ? (doc.name || JSON.stringify(doc)) : doc;
                    docsHtml += `<li style="font-size: 12.5px; font-weight: 600; color: #1e293b;">
                        <i class="fa-solid fa-check" style="color: #16a34a; margin-right: 6px;"></i> ${docText}
                    </li>`;
                });
                docsHtml += '</ul>';
            } else {
                docsHtml = `<div style="text-align: center; padding: 14px; color: #64748b; font-size: 12px;">
                    <i class="fa-regular fa-file-lines" style="font-size: 20px; color: #cbd5e1; display: block; margin-bottom: 6px;"></i>
                    No specific documents required for this service.
                </div>`;
            }

            docsContainer.innerHTML = docsHtml;
        }
    }

    function onModalServiceSwitch(serviceId) {
        const service = window.MASTER_SERVICES.find(s => s.id == serviceId);
        if (service) {
            renderModalServiceDetails(service, '', service.expected_processing_days, service.price);
        }
    }

    function closeDocsModal(e) {
        if (!e || e.target === document.getElementById('docs-modal-overlay') || e.target.closest('.modal-close-btn') || e.target.classList.contains('btn-secondary')) {
            const modal = document.getElementById('docs-modal-overlay');
            if (modal) modal.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
