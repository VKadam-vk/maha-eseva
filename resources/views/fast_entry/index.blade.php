@extends('layouts.app')

@section('title', 'Fast Daily Entry & Service Work')

@section('content')
<style>
    .fast-entry-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        max-width: 1650px;
        margin: 0 auto;
    }

    /* Top Grid (Pocket Pay Inspired 2-Column Split) */
    .pocket-top-grid {
        display: grid;
        grid-template-columns: minmax(360px, 460px);
        gap: 20px;
        align-items: stretch;
        transition: all 0.25s ease;
    }

    .pocket-top-grid.has-customer {
        grid-template-columns: 370px 1fr;
    }

    @media (max-width: 1024px) {
        .pocket-top-grid.has-customer {
            grid-template-columns: 1fr;
        }
    }

    .pocket-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 4px 15px -2px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .pocket-card-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: #ffffff;
        padding: 13px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #1e3a8a;
    }

    .pocket-card-header .card-title {
        font-size: 14.5px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: 0.02em;
    }

    .pocket-card-body {
        padding: 18px 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Left Form Elements */
    .pocket-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
        display: block;
    }

    .pocket-label.required::after {
        content: " *";
        color: #dc2626;
        font-weight: 800;
    }

    .pocket-input {
        width: 100%;
        height: 42px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .pocket-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        outline: none;
    }

    .pocket-btn-group {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

    .btn-go {
        flex: 1;
        height: 42px;
        background: #0284c7;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 14.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
    }

    .btn-go:hover {
        background: #0369a1;
        transform: translateY(-1px);
    }

    .btn-reset {
        height: 42px;
        padding: 0 18px;
        background: #ffffff;
        color: #dc2626;
        border: 1.5px solid #f87171;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-reset:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    .pocket-hint-note {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 14px;
        line-height: 1.4;
        background: #f8fafc;
        border-left: 3px solid #0284c7;
        padding: 8px 12px;
        border-radius: 0 6px 6px 0;
    }

    /* Right Details Card Elements */
    .cust-empty-state {
        text-align: center;
        padding: 24px 20px;
        color: #94a3b8;
    }

    .cust-empty-state .empty-icon {
        font-size: 34px;
        color: #cbd5e1;
        margin-bottom: 8px;
    }

    .cust-empty-state .empty-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
    }

    .cust-empty-state .empty-desc {
        font-size: 12px;
        max-width: 360px;
        margin: 0 auto;
        line-height: 1.4;
    }

    .details-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    @media (max-width: 768px) {
        .details-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    .summary-item {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 9px;
        padding: 9px 13px;
        display: flex;
        flex-direction: column;
        gap: 3px;
        transition: all 0.2s ease;
        position: relative;
    }

    .summary-item:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }

    .summary-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-val {
        font-size: 14px;
        color: #0f172a;
        font-weight: 700;
        word-break: break-all;
    }

    /* Inline Edit Inputs for Profile Auto-Save */
    .inline-edit-input {
        width: 100%;
        border: 1px solid transparent;
        border-radius: 5px;
        background: transparent;
        padding: 3px 6px;
        margin-left: -6px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        transition: all 0.15s ease;
    }

    .inline-edit-input:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .inline-edit-input:focus {
        border-color: #0284c7;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        outline: none;
    }

    .inline-edit-select {
        width: 100%;
        border: 1px solid transparent;
        border-radius: 5px;
        background: transparent;
        padding: 2px 4px;
        margin-left: -4px;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
    }

    .inline-edit-select:hover, .inline-edit-select:focus {
        border-color: #cbd5e1;
        background: #ffffff;
        outline: none;
    }

    /* Star Clickable Picker in Card */
    .star-clickable-group {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 2px;
        cursor: pointer;
    }

    .star-icon {
        font-size: 17px;
        color: #f59e0b;
        cursor: pointer;
        transition: transform 0.15s ease;
    }

    .star-icon:hover {
        transform: scale(1.25);
    }

    .star-icon.inactive {
        color: #cbd5e1;
    }

    .rating-badge {
        font-size: 12px;
        font-weight: 800;
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
        padding: 1px 7px;
        border-radius: 5px;
        margin-left: 6px;
    }

    /* Auto-save notification tag */
    .save-indicator {
        position: absolute;
        right: 8px;
        top: 8px;
        font-size: 10.5px;
        font-weight: 700;
        color: #16a34a;
        display: none;
    }

    /* Pocket Badges */
    .pocket-badge-neutral {
        font-size: 11.5px;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    .pocket-badge-active {
        font-size: 11.5px;
        background: #22c55e;
        color: #ffffff;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 700;
    }

    /* ========================================================================= */
    /* UNIFIED EXCEL SPREADSHEET TABLE                                           */
    /* ========================================================================= */
    .excel-table-scroll-wrapper {
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        box-shadow: 0 4px 15px -2px rgba(15, 23, 42, 0.05);
    }

    .pocket-excel-table {
        width: 100%;
        min-width: 1450px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .pocket-excel-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    .pocket-excel-table th {
        padding: 10px 12px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 2px solid #334155;
        border-right: 1px solid #334155;
        white-space: nowrap;
    }

    .pocket-excel-table th:last-child {
        border-right: none;
    }

    .pocket-excel-table tbody tr {
        background: #ffffff;
        transition: background 0.15s ease;
    }

    .pocket-excel-table tbody tr.committed-row:hover {
        background: #f0f9ff;
    }

    .pocket-excel-table td {
        padding: 8px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }

    .pocket-excel-table td:last-child {
        border-right: none;
    }

    /* Active Excel Input Row */
    .pocket-excel-table tr.active-entry-row {
        background: #fffbeb !important;
        border: 2px solid #f59e0b;
    }

    .pocket-excel-table tr.active-entry-row td {
        background: #fffbeb;
        border-top: 2px solid #f59e0b;
        border-bottom: 2px solid #f59e0b;
        padding: 6px 8px;
    }

    .pocket-excel-table .excel-input,
    .pocket-excel-table .excel-select {
        width: 100%;
        height: 38px;
        font-size: 13px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 5px 9px;
        background: #ffffff;
        transition: all 0.15s ease;
    }

    .pocket-excel-table .excel-input:focus,
    .pocket-excel-table .excel-select:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        outline: none;
        background: #ffffff;
    }

    /* Direct Inline Excel Cell Inputs & Selects for Committed Rows */
    .pocket-excel-table tbody tr.committed-row td:has(.excel-cell-input) {
        cursor: text;
    }

    .pocket-excel-table .excel-cell-input {
        width: 100%;
        height: 34px;
        font-size: 13px;
        border: 1px solid transparent;
        border-radius: 6px;
        padding: 4px 8px;
        background: transparent;
        color: #0f172a;
        transition: all 0.15s ease;
    }

    .pocket-excel-table .excel-cell-input:hover {
        border-color: #cbd5e1;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .pocket-excel-table .excel-cell-input:focus {
        border-color: #0284c7;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        outline: none;
    }

    .pocket-excel-table .excel-status-select {
        height: 32px;
        font-size: 11.5px;
        font-weight: 800;
        border-radius: 6px;
        padding: 3px 8px;
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: all 0.15s ease;
        background: #f1f5f9;
    }

    .pocket-excel-table .excel-status-select:hover {
        filter: brightness(0.97);
        border-color: #94a3b8;
    }

    .pocket-excel-table .excel-status-select:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25);
    }

    .pocket-excel-table .excel-operator-select {
        width: 100%;
        height: 34px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        padding: 4px 8px;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
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

    .saved-pulse-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 7px;
        background: #dcfce7;
        color: #166534;
        font-size: 11px;
        font-weight: 800;
        border-radius: 5px;
        margin-left: 5px;
        animation: fadeInOut 1.5s forwards;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; transform: scale(0.9); }
        15% { opacity: 1; transform: scale(1); }
        80% { opacity: 1; }
        100% { opacity: 0; }
    }

    /* Sub-Service Eye Icon Button */
    .btn-doc-eye {
        width: 34px;
        height: 38px;
        flex-shrink: 0;
        background: #e0f2fe;
        color: #0284c7;
        border: 1.5px solid #bae6fd;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }

    .btn-doc-eye:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
    }

    /* Modal Overlay & Card for Required Docs */
    .modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(4px);
        z-index: 9999999 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .docs-modal-card {
        position: relative !important;
        z-index: 10000000 !important;
        background: #ffffff;
        width: 100%;
        max-width: 520px;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border: 1px solid #cbd5e1;
        overflow: hidden;
        animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleIn {
        from { opacity: 0; transform: scale(0.94); }
        to { opacity: 1; transform: scale(1); }
    }

    .docs-modal-header {
        background: #0f172a;
        color: #f8fafc;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-close-btn {
        background: transparent;
        border: none;
        font-size: 24px;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        border-radius: 6px;
        transition: all 0.15s ease;
    }

    .modal-close-btn:hover {
        color: #ffffff;
        background: #334155;
    }

    .docs-modal-body {
        padding: 18px 20px;
    }

    .docs-checklist-container {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        max-height: 240px;
        overflow-y: auto;
    }

    .doc-check-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 7px 0;
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .doc-check-item:last-child {
        border-bottom: none;
    }

    /* Status Input in Committed Rows */
    .pocket-excel-table .excel-status-input {
        width: 100%;
        height: 32px;
        font-size: 11.5px;
        font-weight: 800;
        border-radius: 6px;
        padding: 3px 8px;
        cursor: text;
        border: 1.5px solid transparent;
        transition: all 0.15s ease;
        text-transform: uppercase;
        background: #f1f5f9;
    }

    .pocket-excel-table .excel-status-input:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .pocket-excel-table .excel-status-input:focus {
        border-color: #0284c7;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25);
    }

    /* Work Details auto-expanding and resizable textarea */
    .pocket-excel-table .excel-work-details {
        width: 100%;
        min-width: 160px;
        max-width: 320px;
        min-height: 34px;
        height: 34px;
        resize: both;
        font-size: 12px;
        font-family: inherit;
        font-weight: 500;
        color: #1e293b;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 5px 8px;
        line-height: 1.35;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        overflow-y: auto;
        box-sizing: border-box;
    }

    .pocket-excel-table .excel-work-details:hover {
        border-color: #94a3b8;
    }

    .pocket-excel-table .excel-work-details:focus {
        border-color: #0284c7;
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    /* Date Input */
    .pocket-excel-table .excel-date-input {
        width: 100%;
        min-width: 120px;
        height: 34px;
        font-size: 11.5px;
        font-family: var(--font-mono, monospace);
        font-weight: 600;
        color: #334155;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .pocket-excel-table .excel-date-input:hover {
        border-color: #94a3b8;
    }

    .pocket-excel-table .excel-date-input:focus {
        border-color: #0284c7;
        outline: none;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }

    /* Row Action Buttons (Apply, Tutorial, View) */
    .btn-row-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 9px;
        border-radius: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        line-height: 1;
        border: 1px solid transparent;
    }

    .btn-row-apply {
        background: #0284c7;
        color: #ffffff;
        border-color: #0369a1;
    }

    .btn-row-apply:hover {
        background: #0369a1;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
    }

    .btn-row-tutorial {
        background: #dc2626;
        color: #ffffff;
        border-color: #b91c1c;
    }

    .btn-row-tutorial:hover {
        background: #b91c1c;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
    }

    .btn-row-view {
        background: #f1f5f9;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-row-view:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .btn-doc-eye-small {
        width: 26px;
        height: 28px;
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
        transition: all 0.15s ease;
    }

    .btn-doc-eye-small:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }

    .btn-save-excel-row {
        height: 38px;
        background: #ea580c;
        color: white;
        border: none;
        font-weight: 800;
        font-size: 13px;
        padding: 0 16px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(234, 88, 12, 0.3);
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn-save-excel-row:hover {
        background: #c2410c;
        transform: translateY(-1px);
    }

    /* Alert Banner */
    .save-alert-banner {
        display: none;
        background: #ecfdf5;
        border: 1.5px solid #86efac;
        color: #14532d;
        border-radius: 10px;
        padding: 12px 18px;
        align-items: center;
        justify-content: space-between;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="fast-entry-container">
    <!-- Top Header -->
    <div class="page-header" style="margin-bottom: 0;">
        <div>
            <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-bolt" style="font-size: 18px;"></i>
                </span>
                High-Speed Counter & Service Entry System
            </h1>
            <p class="page-subtitle" style="margin-top: 4px;">
                Fast Customer Lookup, Auto-CRM Registration & Continuous Excel Service Spreadsheet
            </p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary" style="font-weight: 600; padding: 8px 16px;">
                <i class="fa-solid fa-users" style="color: #0284c7;"></i> Open Customers CRM <i class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
            </a>
            <span class="badge" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12.5px; padding: 7px 14px; font-weight: 600;">
                <i class="fa-solid fa-keyboard" style="color: #16a34a; margin-right: 4px;"></i> <strong>Enter</strong> in Row to Save & Next
            </span>
        </div>
    </div>

    <!-- Alert Notification Banner -->
    <div id="save-alert" class="save-alert-banner">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="display: inline-flex; width: 32px; height: 32px; border-radius: 50%; background: #22c55e; color: white; align-items: center; justify-content: center; font-size: 14px;">
                <i class="fa-solid fa-check"></i>
            </span>
            <div>
                <strong id="alert-title" style="font-size: 14px;">Action Success</strong>
                <div id="alert-desc" style="font-size: 12.5px; color: #166534;">Customer details loaded.</div>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('save-alert').style.display='none'" style="font-size: 12px;">
                <i class="fa-solid fa-xmark"></i> Dismiss
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. POCKET PAY INSPIRED 2-COLUMN SECTION (LEFT: LOOKUP/ENTRY, RIGHT: INFO) -->
    <!-- ========================================================================= -->
    <div class="pocket-top-grid" id="pocket-top-grid">
        <!-- LEFT BOX: Customer Name & Mobile Number -->
        <div class="pocket-card">
            <div class="pocket-card-header">
                <div class="card-title">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Customer Quick Entry / Go</span>
                </div>
                <span style="font-size: 11px; opacity: 0.9;">Step 1</span>
            </div>

            <div class="pocket-card-body">
                <form id="quick-go-form" onsubmit="executeQuickGo(event)">
                    @csrf
                    <!-- 1. Customer Name -->
                    <div style="margin-bottom: 14px;">
                        <label class="pocket-label" for="fe_name">
                            Customer Name (नाव)
                        </label>
                        <input type="text" id="fe_name" name="customer_name" class="pocket-input" 
                               placeholder="e.g. VINOD KADAM" autocomplete="off"
                               style="text-transform: uppercase;" autofocus>
                    </div>

                    <!-- 2. Mobile Number -->
                    <div style="margin-bottom: 14px;">
                        <label class="pocket-label required" for="fe_mobile">
                            Mobile Number (मोबाईल नंबर)
                        </label>
                        <input type="tel" id="fe_mobile" name="mobile" class="pocket-input font-mono" required 
                               placeholder="10 Digits (e.g. 9527667881)" maxlength="10" pattern="[0-9]{10}" autocomplete="off">
                    </div>

                    <!-- Action Buttons: Go & Reset -->
                    <div class="pocket-btn-group">
                        <button type="submit" id="btn-go" class="btn-go">
                            <i class="fa-solid fa-arrow-right"></i> Go
                        </button>
                        <button type="button" id="btn-reset" class="btn-reset" onclick="resetEntireDesk()">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                    </div>

                    <div class="pocket-hint-note">
                        <i class="fa-solid fa-circle-check" style="color: #0284c7;"></i> 
                        नाव व मोबाईल टाकून <strong>Go</strong> दाबा. नसल्यास <strong>थेट CRM मध्ये Auto-Save</strong> होईल!
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT BOX: Customer Details (Click-To-Edit + Instant AutoSave) -->
        <div class="pocket-card" id="right-customer-card" style="display: none;">
            <div class="pocket-card-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="card-title">
                    <i class="fa-solid fa-address-card" style="color: #38bdf8;"></i>
                    <span>Customer Details (क्लिक करून लगेच एडिट करा - Auto-Saves)</span>
                </div>
                <span id="cd-status-badge" class="pocket-badge-neutral">
                    No Customer Selected
                </span>
            </div>

            <div class="pocket-card-body">
                <!-- Empty State -->
                <div id="cust-empty-state" class="cust-empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <div class="empty-title">कोणताही ग्राहक निवडलेला नाही</div>
                    <div class="empty-desc">
                        डाव्या बाजूला नाव व 10-अंकी मोबाईल नंबर टाकून <strong>Go</strong> बटण दाबा.
                    </div>
                </div>

                <!-- Loaded State (Sender Details Card with Direct Inline Editable Fields) -->
                <div id="cust-loaded-state" style="display: none;">
                    <input type="hidden" id="current_cust_id" value="">

                    <div class="details-summary-grid">
                        <!-- 1. NAME (Click to edit directly) -->
                        <div class="summary-item" title="Click to edit Name (Auto-saves on blur/Enter)">
                            <span class="summary-label">
                                <span>Name (क्लिक करून बदला)</span>
                                <i class="fa-solid fa-pencil" style="font-size: 10px; color: #0284c7;"></i>
                            </span>
                            <input type="text" id="cd-inline-name" class="inline-edit-input" placeholder="Customer Name"
                                   onblur="autoSaveCustomerField('name', this.value)"
                                   onkeydown="if(event.key==='Enter'){this.blur();}">
                            <span class="save-indicator" id="ind-name">✓ Saved</span>
                        </div>

                        <!-- 2. MOBILE -->
                        <div class="summary-item">
                            <span class="summary-label">Mobile No</span>
                            <span class="summary-val font-mono" id="cd-mobile">-</span>
                        </div>

                        <!-- 3. CODE -->
                        <div class="summary-item">
                            <span class="summary-label">Customer Code</span>
                            <span class="summary-val font-mono" id="cd-code" style="color: #0284c7;">-</span>
                        </div>

                        <!-- 4. SERVICES COUNT -->
                        <div class="summary-item">
                            <span class="summary-label">Services Taken</span>
                            <span class="summary-val" id="cd-services-count" style="color: #166534;">0 Services</span>
                        </div>

                        <!-- 5. OUTSTANDING BALANCE -->
                        <div class="summary-item">
                            <span class="summary-label">Outstanding Balance</span>
                            <span class="summary-val font-mono" id="cd-outstanding">₹ 0.00</span>
                        </div>

                        <!-- 6. RATING (Interactive 1-5 Keys or Click to AutoSave) -->
                        <div class="summary-item" id="rating-item-box" title="Click star or press key 1 to 5 to change rating" tabindex="0">
                            <span class="summary-label">
                                <span>Rating (Key 1-5 किंवा Click)</span>
                                <span class="save-indicator" id="ind-rating" style="display:none;">✓</span>
                            </span>
                            <div class="star-clickable-group">
                                <i class="fa-solid fa-star star-icon" data-val="1" onclick="setAndAutoSaveRating(1)"></i>
                                <i class="fa-solid fa-star star-icon" data-val="2" onclick="setAndAutoSaveRating(2)"></i>
                                <i class="fa-solid fa-star star-icon" data-val="3" onclick="setAndAutoSaveRating(3)"></i>
                                <i class="fa-solid fa-star star-icon" data-val="4" onclick="setAndAutoSaveRating(4)"></i>
                                <i class="fa-solid fa-star star-icon" data-val="5" onclick="setAndAutoSaveRating(5)"></i>
                                <span class="rating-badge font-mono" id="cd-rating-badge">5 ★</span>
                            </div>
                        </div>

                        <!-- 7. GENDER -->
                        <div class="summary-item">
                            <span class="summary-label">Gender (लिंग)</span>
                            <select id="cd-inline-gender" class="inline-edit-select" onchange="autoSaveCustomerField('gender', this.value)">
                                <option value="MALE">Male (पुरुष)</option>
                                <option value="FEMALE">Female (महिला)</option>
                                <option value="OTHER">Other</option>
                            </select>
                        </div>

                        <!-- 8. OCCUPATION -->
                        <div class="summary-item" title="Click to edit occupation">
                            <span class="summary-label">Occupation (व्यवसाय)</span>
                            <input type="text" id="cd-inline-occ" class="inline-edit-input" placeholder="e.g. FARMER / SHOP"
                                   onblur="autoSaveCustomerField('occupation', this.value)"
                                   onkeydown="if(event.key==='Enter'){this.blur();}">
                        </div>

                        <!-- 9. BIRTH DATE -->
                        <div class="summary-item" title="Click to edit birth date">
                            <span class="summary-label">Birth Date (जन्मतारीख)</span>
                            <input type="date" id="cd-inline-dob" class="inline-edit-input" style="font-size: 13px;"
                                   onchange="autoSaveCustomerField('birth_date', this.value)">
                        </div>
                    </div>

                    <!-- Footer Link -->
                    <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 10px;">
                        <a id="cd-full-profile-link" href="#" target="_blank" class="btn btn-sm" style="color: #0284c7; font-weight: 700; font-size: 12px; text-decoration: none;">
                            Open Full CRM Profile <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. SINGLE UNIFIED EXCEL SPREADSHEET TABLE                                 -->
    <!-- ========================================================================= -->
    @php
        $uniqueMainServices = $services->pluck('main_service_name')->filter()->unique()->sort()->values();
    @endphp

    <div class="pocket-card" id="bottom-services-card" style="border: 1.5px solid #cbd5e1; display: none;">
        <div class="pocket-card-header" style="background: #0f172a; display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <i class="fa-solid fa-table-cells" style="color: #38bdf8;"></i>
                <span>CUSTOMER SERVICES (सलग Excel शीट: सर्वात वर नवीन सेवा एन्ट्री रो + खाली सर्व सेवा - Newest on Top)</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span id="services-badge" class="pocket-badge-neutral" style="background: #1e293b; color: #38bdf8; border: 1px solid #334155;">
                    0 Services
                </span>
                <span style="font-size: 11.5px; color: #94a3b8; font-weight: 600;">
                    <i class="fa-solid fa-keyboard" style="color: #f59e0b;"></i> Tab across cells | Enter to save
                </span>
            </div>
        </div>

        <div class="pocket-card-body" style="padding: 0;">
            <!-- STATUS COMBOBOX DATALIST (Supports Typing + Service-Specific Options) -->
            <datalist id="status-datalist">
                <option value="IN_PROGRESS">IN_PROGRESS</option>
                <option value="PENDING">PENDING</option>
                <option value="READY">READY</option>
                <option value="DELIVERED">DELIVERED</option>
                <option value="SUBMITTED">SUBMITTED</option>
                <option value="APPROVED">APPROVED</option>
                <option value="REJECTED">REJECTED</option>
            </datalist>

            <!-- UNIFIED EXCEL TABLE (Commited Rows + Live Input Row) -->
            <div class="excel-table-scroll-wrapper" style="border: none; border-radius: 0;">
                <table class="pocket-excel-table">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;"># SR Number</th>
                            <th style="min-width: 150px;">1. Main Service *</th>
                            <th style="min-width: 200px;">2. Sub Service *</th>
                            <th style="min-width: 110px; color: #38bdf8; text-align: center;">Expected Days</th>
                            <th style="min-width: 90px;">Date</th>
                            <th style="min-width: 90px;">3. Rate (₹) *</th>
                            <th style="min-width: 95px; color: #86efac;">4. Received (₹)</th>
                            <th style="min-width: 95px;">5. Remaining (₹)</th>
                            <th style="min-width: 180px; color: #38bdf8;">6. Work Details</th>
                            <th style="min-width: 140px; color: #f59e0b;">7. Status</th>
                            <th style="min-width: 140px;">8. Operator</th>
                            <th style="min-width: 130px; color: #fbbf24;">9. Follow-Up Date</th>
                            <th style="min-width: 220px; text-align: center;">Actions & Quick Links</th>
                        </tr>
                    </thead>
                    <tbody id="services-tbody">
                        <!-- COMMITTED / EXISTING ROWS WILL BE INSERTED HERE DYNAMICALLY -->

                        <!-- ACTIVE SPREADSHEET INPUT ROW (Always present right below existing rows!) -->
                        <tr id="active-service-row" class="active-entry-row">
                            <!-- SR Number Column -->
                            <td>
                                <span class="badge" style="background: #f59e0b; color: white; font-weight: 800; font-size: 11px; padding: 5px 8px; border-radius: 5px; font-family: var(--font-mono);">
                                    <i class="fa-solid fa-plus"></i> NEW ROW
                                </span>
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
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <select id="se_sub_service_select" class="excel-select" onchange="onSubServiceChange(this.value)" disabled
                                            style="font-weight: 600; flex: 1;">
                                        <option value="">-- Choose Sub Service --</option>
                                    </select>
                                    <button type="button" id="btn-view-service-docs" class="btn-doc-eye" onclick="openDocsModal(event)"
                                            title="View Required Documents (आवश्यक कागदपत्रे पहा)">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Expected Days Column -->
                            <td style="text-align: center; padding: 4px 6px; white-space: nowrap;">
                                <span id="se_expected_days_badge" class="badge"
                                      style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 700; font-size: 11.5px; padding: 4px 8px; border-radius: 5px;">
                                    -
                                </span>
                            </td>

                            <!-- Date Column -->
                            <td>
                                <span style="font-size: 12px; color: #64748b; font-family: var(--font-mono); font-weight: 600;">Today</span>
                            </td>

                            <!-- 3. Rate (₹) -->
                            <td>
                                <input type="number" step="0.01" id="se_rate" class="excel-input font-mono" placeholder="0"
                                       oninput="calculateRemaining()" onkeydown="handleRowKeyDown(event)"
                                       style="font-weight: 700; font-size: 13.5px;">
                            </td>

                            <!-- 4. Received (₹) -->
                            <td>
                                <input type="number" step="0.01" id="se_received" class="excel-input font-mono" placeholder="0"
                                       oninput="calculateRemaining()" onkeydown="handleRowKeyDown(event)"
                                       style="font-weight: 700; font-size: 13.5px; color: #166534;">
                            </td>

                            <!-- 5. Remaining (₹) -->
                            <td>
                                <input type="text" id="se_remaining" class="excel-input font-mono" readonly placeholder="0.00"
                                       style="font-weight: 800; font-size: 13.5px; background: #f8fafc; color: #0f172a;">
                            </td>

                            <!-- 6. Work Details (Auto-expandable & Resizable) -->
                            <td style="padding: 4px 6px;">
                                <textarea id="se_work_details" class="excel-work-details"
                                          placeholder="Work details, notes, ack no..."
                                          rows="1"
                                          title="Work Details: Citizen notes, application numbers, or document status"
                                          oninput="autoExpandWorkDetails(this)"
                                          onkeydown="handleRowKeyDown(event)"></textarea>
                            </td>

                            <!-- 7. Status (Initially Blank + Service-Specific Combobox) -->
                            <td style="padding: 4px 6px;">
                                <input type="text" id="se_status" list="status-datalist" class="excel-input font-bold status-combobox"
                                       value=""
                                       placeholder="Type or pick status..."
                                       title="Pick from dropdown or type custom status for this service"
                                       oninput="applyStatusInputStyle(this)"
                                       onchange="recordCustomStatus(this.value); applyStatusInputStyle(this);"
                                       onkeydown="handleRowKeyDown(event)"
                                       style="height: 38px; font-size: 11.5px; font-weight: 800; text-transform: uppercase;">
                            </td>

                            <!-- 8. Operator -->
                            <td>
                                <select id="se_operator" class="excel-select" onkeydown="handleRowKeyDown(event)" style="font-weight: 600; font-size: 12.5px;">
                                    <option value="">-- Operator --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ strtoupper($emp->user?->name ?? $emp->name ?? 'Staff') }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- 9. Follow-Up Date -->
                            <td style="padding: 4px 6px;">
                                <input type="date" id="se_followup" class="excel-date-input"
                                       title="Expected Follow-up Date (फॉलो-अप तारीख)"
                                       onkeydown="handleRowKeyDown(event)">
                            </td>

                            <!-- 10. Actions & Quick Links: Save + Apply + Tutorial -->
                            <td style="text-align: center; white-space: nowrap; padding: 4px 8px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 5px; flex-wrap: nowrap;">
                                    <button type="button" id="btn-save-row" class="btn-save-excel-row" onclick="saveActiveServiceRow()" title="Save Service Entry (Enter)">
                                        <i class="fa-solid fa-check"></i> Save
                                    </button>
                                    <a id="row-btn-apply" href="#" target="_blank" class="btn-row-action btn-row-apply" title="Apply Now on Official Portal" style="display: none;">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Apply
                                    </a>
                                    <a id="row-btn-tutorial" href="#" target="_blank" class="btn-row-action btn-row-tutorial" title="Watch Tutorial Video" style="display: none;">
                                        <i class="fa-brands fa-youtube"></i> Tutorial
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Hidden inputs for service form -->
            <input type="hidden" id="se_customer_id" value="">
            <input type="hidden" id="se_service_id" value="">
            <input type="hidden" id="se_sub_service" value="">
            <input type="hidden" id="se_docs_list" value="">
        </div>
    </div>
</div>

<!-- REQUIRED DOCUMENTS POPUP MODAL -->
<div id="docs-modal-overlay" class="modal-overlay" style="display: none;" onclick="closeDocsModal(event)">
    <div class="docs-modal-card" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="docs-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(2, 132, 199, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <div>
                    <h3 id="modal-service-title" style="margin: 0; font-size: 14.5px; font-weight: 700; color: #ffffff;">Required Documents</h3>
                    <p id="modal-sub-service-title" style="margin: 2px 0 0 0; font-size: 11.5px; color: #94a3b8; font-weight: 500;">आवश्यक कागदपत्रे व पात्रता तपशील</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDocsModal()" title="Close (बंद करा)">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div class="docs-modal-body">
            <!-- Badges Bar (Expected Days & Fee) -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 14px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #0369a1;">
                    <i class="fa-regular fa-clock"></i>
                    <span id="modal-service-days">⏱️ Expected: 7 Days</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 800; color: #166534;">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                    <span id="modal-service-rate">₹ 0</span>
                </div>
            </div>

            <!-- Documents Checklist Container -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11.5px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px;">
                    <i class="fa-solid fa-clipboard-check" style="color: #0284c7; margin-right: 4px;"></i>
                    आवश्यक कागदपत्रे सूची (Required Documents Checklist)
                </label>
                <div id="modal-docs-container" class="docs-checklist-container">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" id="modal-btn-copy-docs" onclick="copyModalDocsToDetails()" class="btn btn-sm btn-secondary"
                        style="font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;"
                        title="Copy documents list to service work details">
                    <i class="fa-solid fa-copy"></i> Copy to Details
                </button>
                <a id="modal-btn-portal" href="#" target="_blank" class="btn btn-sm"
                   style="background: #0284c7; color: #ffffff; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; padding: 5px 10px;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Apply Online
                </a>
                <a id="modal-btn-tutorial" href="#" target="_blank" class="btn btn-sm"
                   style="background: #dc2626; color: #ffffff; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; padding: 5px 10px;">
                    <i class="fa-brands fa-youtube"></i> Watch Tutorial
                </a>
                <button type="button" onclick="closeDocsModal()" class="btn btn-sm btn-secondary"
                        style="font-size: 12px; font-weight: 600;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ==========================================
// 1. GLOBAL STATE & SERVICES MASTER
// ==========================================
let currentLoadedCustomer = null;
let loadedApplications = {};
const allServices = @json($services);
const allEmployees = @json($employees);

// ==========================================
// 2. QUICK GO: SEARCH / AUTO-SAVE TO CRM
// ==========================================
function executeQuickGo(e) {
    if (e) e.preventDefault();

    const mobileInput = document.getElementById('fe_mobile');
    const nameInput = document.getElementById('fe_name');
    const btnGo = document.getElementById('btn-go');

    const mobile = mobileInput.value.trim().replace(/[^0-9]/g, '');
    const name = nameInput.value.trim();

    if (mobile.length !== 10) {
        alert('कृपया वैध 10-अंकी मोबाईल नंबर टाका (Please enter valid 10-digit mobile number)');
        mobileInput.focus();
        return;
    }

    btnGo.disabled = true;
    btnGo.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    fetch('{{ route("fast_entry.quick_go") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            mobile: mobile,
            customer_name: name
        })
    })
    .then(res => res.json())
    .then(res => {
        btnGo.disabled = false;
        btnGo.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Go';

        if (res.success) {
            currentLoadedCustomer = res.customer;
            populateCustomerUI(res.customer, res.is_new);
            renderCommittedServices(res.applications || []);

            // Link customer to Service Entry row
            document.getElementById('se_customer_id').value = res.customer.id;

            // Show alert banner
            showAlert(res.is_new ? 'New Customer Registered!' : 'Customer Profile Loaded', res.message);

            // Focus on Main Service dropdown for immediate row typing!
            document.getElementById('se_main_service').focus();
        } else {
            alert('Error: ' + (res.message || 'Failed to process customer.'));
        }
    })
    .catch(err => {
        btnGo.disabled = false;
        btnGo.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Go';
        alert('Network error while processing customer.');
    });
}

// Auto-lookup when 10 digits are typed in mobile field
document.getElementById('fe_mobile').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// ==========================================
// 3. UI POPULATION (RIGHT DETAILS & SERVICES)
// ==========================================
function populateCustomerUI(cust, isNew) {
    const grid = document.getElementById('pocket-top-grid');
    if (grid) grid.classList.add('has-customer');

    const rightCard = document.getElementById('right-customer-card');
    if (rightCard) rightCard.style.display = 'flex';

    const bottomCard = document.getElementById('bottom-services-card');
    if (bottomCard) bottomCard.style.display = 'flex';

    document.getElementById('cust-empty-state').style.display = 'none';
    document.getElementById('cust-loaded-state').style.display = 'block';

    const statusBadge = document.getElementById('cd-status-badge');
    if (isNew) {
        statusBadge.className = 'pocket-badge-active';
        statusBadge.innerText = 'Auto-Saved to CRM';
    } else {
        statusBadge.className = 'pocket-badge-active';
        statusBadge.innerText = 'Existing Active Customer';
    }

    document.getElementById('current_cust_id').value = cust.id;
    document.getElementById('cd-inline-name').value = cust.name;
    document.getElementById('cd-mobile').innerText = cust.mobile;
    document.getElementById('cd-code').innerText = cust.customer_code;
    document.getElementById('cd-services-count').innerText = `${cust.total_services} Services`;
    
    const outEl = document.getElementById('cd-outstanding');
    outEl.innerText = `₹ ${parseFloat(cust.total_outstanding).toFixed(2)}`;
    outEl.style.color = cust.total_outstanding > 0 ? '#dc2626' : '#166534';

    // Rating & Gender
    renderStarRating(cust.star_rating || 5);
    document.getElementById('cd-inline-gender').value = cust.gender || 'MALE';
    document.getElementById('cd-inline-occ').value = cust.occupation || '';
    document.getElementById('cd-inline-dob').value = cust.birth_date || '';
    document.getElementById('cd-full-profile-link').href = cust.profile_url;

    // Sync back left name if blank
    if (!document.getElementById('fe_name').value) {
        document.getElementById('fe_name').value = cust.name;
    }
}

// ==========================================
// 4. INLINE CLICK-TO-EDIT & AUTOSAVE TO CRM
// ==========================================
function autoSaveCustomerField(fieldName, fieldValue) {
    const custId = document.getElementById('current_cust_id').value;
    if (!custId) return;

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_id: custId
    };
    payload[fieldName] = fieldValue;

    fetch('{{ route("fast_entry.update_customer") }}', {
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
            // Show subtle saved indicator
            if (fieldName === 'name') {
                const ind = document.getElementById('ind-name');
                ind.style.display = 'inline';
                setTimeout(() => ind.style.display = 'none', 1500);
                document.getElementById('fe_name').value = fieldValue;
            }
        }
    })
    .catch(() => {});
}

// Star Rating click or key press (1 to 5)
function renderStarRating(val) {
    val = parseInt(val, 10) || 5;
    document.getElementById('cd-rating-badge').innerText = `${val} ★`;
    const stars = document.querySelectorAll('#rating-item-box .star-icon');
    stars.forEach(star => {
        const starVal = parseInt(star.getAttribute('data-val'), 10);
        if (starVal <= val) {
            star.className = 'fa-solid fa-star star-icon';
        } else {
            star.className = 'fa-solid fa-star star-icon inactive';
        }
    });
}

function setAndAutoSaveRating(val) {
    val = parseInt(val, 10);
    renderStarRating(val);
    autoSaveCustomerField('star_rating', val);

    const ind = document.getElementById('ind-rating');
    if (ind) {
        ind.style.display = 'inline';
        setTimeout(() => ind.style.display = 'none', 1200);
    }
}

// Global Keyboard Handler for Star Rating (Keys 1 to 5)
document.addEventListener('keydown', function(e) {
    // If user is typing in a text field or select, do not trigger star change
    const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea' || tag === 'select') {
        return;
    }

    if (currentLoadedCustomer && ['1', '2', '3', '4', '5'].includes(e.key)) {
        e.preventDefault();
        setAndAutoSaveRating(parseInt(e.key, 10));
    }
});

// ==========================================
// 5. UNIFIED EXCEL TABLE: RENDER COMMITTED ROWS
// ==========================================
function getEmployeeOptionsHtml(selectedId) {
    let html = '<option value="">-- Unassigned --</option>';
    allEmployees.forEach(emp => {
        const name = (emp.user && emp.user.name) ? emp.user.name : (emp.name || 'Staff');
        const sel = (emp.id == selectedId) ? 'selected' : '';
        html += `<option value="${emp.id}" ${sel}>${name.toUpperCase()}</option>`;
    });
    return html;
}

function renderCommittedServices(apps) {
    const tbody = document.getElementById('services-tbody');
    const activeRow = document.getElementById('active-service-row');

    // Remove any previously rendered committed rows (keep only active row)
    const oldRows = tbody.querySelectorAll('tr.committed-row');
    oldRows.forEach(r => r.remove());

    loadedApplications = {};
    document.getElementById('services-badge').innerText = `${apps.length} Services`;

    if (!apps || apps.length === 0) {
        return;
    }

    apps.forEach(app => {
        loadedApplications[app.id] = app;
        const tr = createCommittedRowElement(app);
        tbody.appendChild(tr);
    });
}

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
// STATUS COMBOBOX & PER-SERVICE PERSISTENCE
// ==========================================
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

function getSavedStatusesForService(serviceName) {
    const key = (serviceName || 'DEFAULT').trim().toUpperCase();
    let storedMap = {};
    try {
        const stored = localStorage.getItem('maha_eseva_service_statuses');
        if (stored) storedMap = JSON.parse(stored);
    } catch(e) {}

    const defaultList = DEFAULT_SERVICE_STATUSES[key] || DEFAULT_SERVICE_STATUSES['DEFAULT'];
    const customList = storedMap[key] || [];

    return Array.from(new Set([...defaultList, ...customList]));
}

function initCustomStatuses() {
    updateStatusDatalistForService('DEFAULT');
    // Initially blank status input
    const seStatus = document.getElementById('se_status');
    if (seStatus) {
        seStatus.value = '';
        applyStatusInputStyle(seStatus);
    }
}

function recordCustomStatus(val) {
    const mainService = document.getElementById('se_main_service')?.value || 'DEFAULT';
    recordCustomStatusForService(mainService, val);
}

function recordCustomStatusForService(serviceName, val) {
    if (!val) return;
    const clean = val.trim().toUpperCase();
    if (!clean) return;

    const key = (serviceName || 'DEFAULT').trim().toUpperCase();
    let storedMap = {};
    try {
        const stored = localStorage.getItem('maha_eseva_service_statuses');
        if (stored) storedMap = JSON.parse(stored);
    } catch(e) {}

    if (!storedMap[key]) storedMap[key] = [];
    if (!storedMap[key].includes(clean)) {
        storedMap[key].push(clean);
        try {
            localStorage.setItem('maha_eseva_service_statuses', JSON.stringify(storedMap));
        } catch(e) {}
    }
    updateStatusDatalistForService(key);
}

function updateStatusDatalistForService(serviceName) {
    const key = (serviceName || 'DEFAULT').trim().toUpperCase();
    const list = getSavedStatusesForService(key);
    const datalist = document.getElementById('status-datalist');
    if (datalist) {
        datalist.innerHTML = list.map(st => `<option value="${escapeHtml(st)}"></option>`).join('');
    }
}

function ensureServiceStatusDatalist(serviceName) {
    const key = (serviceName || 'DEFAULT').trim().toUpperCase();
    const cleanId = 'status-datalist-' + key.replace(/[^A-Z0-9]/g, '_');
    let datalist = document.getElementById(cleanId);
    if (!datalist) {
        datalist = document.createElement('datalist');
        datalist.id = cleanId;
        document.body.appendChild(datalist);
    }
    const list = getSavedStatusesForService(key);
    datalist.innerHTML = list.map(st => `<option value="${escapeHtml(st)}"></option>`).join('');
    return cleanId;
}

function applyStatusInputStyle(el) {
    if (!el) return;
    const val = (el.value || '').trim().toUpperCase();
    el.setAttribute('data-status', val);

    if (val === '') {
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

// Backward compatibility alias
function applyStatusSelectStyle(el) {
    applyStatusInputStyle(el);
}

// ==========================================
// REQUIRED DOCUMENTS POPUP MODAL
// ==========================================
let currentModalDocs = [];

function openDocsModal(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const serviceId = document.getElementById('se_service_id')?.value;
    const mainService = document.getElementById('se_main_service')?.value;

    let srv = null;
    if (serviceId) {
        srv = allServices.find(s => s.id == serviceId);
    }
    if (!srv && mainService) {
        srv = allServices.find(s => s.main_service_name === mainService);
    }
    if (!srv) {
        alert('कृपया आधी Main Service किंवा Sub Service निवडा!');
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
    if (!srv) {
        alert('या सेवेची तपशीलवार माहिती उपलब्ध नाही.');
        return;
    }
    openDocsModalWithService(srv);
}

function openDocsModalWithService(srv) {
    const modalTitle = document.getElementById('modal-service-title');
    const modalSubTitle = document.getElementById('modal-sub-service-title');
    const modalDays = document.getElementById('modal-service-days');
    const modalRate = document.getElementById('modal-service-rate');
    const docsContainer = document.getElementById('modal-docs-container');
    const btnPortal = document.getElementById('modal-btn-portal');
    const btnTutorial = document.getElementById('modal-btn-tutorial');

    const mainName = srv.main_service_name || 'Service';
    const subName = srv.sub_service_name || '';
    const days = parseInt(srv.expected_processing_days) || 7;
    const price = parseFloat(srv.price) || 0;

    if (modalTitle) modalTitle.innerText = `${mainName} ${subName ? '➔ ' + subName : ''}`;
    if (modalSubTitle) modalSubTitle.innerText = 'आवश्यक कागदपत्रे व पात्रता तपशील (Required Documents)';
    if (modalDays) modalDays.innerText = `⏱️ Expected: ${days} Days (${days} दिवस)`;
    if (modalRate) modalRate.innerText = `₹ ${price.toFixed(0)}`;

    // Parse Required Docs
    let docsArr = [];
    if (Array.isArray(srv.required_documents_json)) {
        docsArr = srv.required_documents_json;
    } else if (typeof srv.required_documents_json === 'string') {
        try {
            const parsed = JSON.parse(srv.required_documents_json);
            if (Array.isArray(parsed)) docsArr = parsed;
        } catch(e) {
            if (srv.required_documents_json.trim()) {
                docsArr = srv.required_documents_json.split(',').map(s => s.trim());
            }
        }
    }
    currentModalDocs = docsArr;

    if (docsContainer) {
        if (docsArr.length > 0) {
            docsContainer.innerHTML = docsArr.map((doc, idx) => `
                <div class="doc-check-item">
                    <i class="fa-solid fa-circle-check" style="color: #16a34a; font-size: 15px; margin-top: 1px;"></i>
                    <span style="flex: 1; line-height: 1.4;">${escapeHtml(doc)}</span>
                </div>
            `).join('');
        } else {
            docsContainer.innerHTML = `
                <div style="text-align: center; padding: 20px 10px; color: #64748b;">
                    <i class="fa-solid fa-file-circle-check" style="font-size: 30px; color: #94a3b8; margin-bottom: 8px;"></i>
                    <p style="margin: 0; font-size: 13px; font-weight: 500;">या सेवेसाठी विशेष कागदपत्रांची यादी उपलब्ध नाही किंवा कागदपत्रे आवश्यक नाहीत.</p>
                </div>
            `;
        }
    }

    // Portal link
    const workUrl = srv.work_portal_link || srv.service_portal_link;
    if (btnPortal) {
        if (workUrl && workUrl.trim() !== '') {
            btnPortal.href = workUrl.trim();
        } else {
            btnPortal.href = `https://www.google.com/search?q=${encodeURIComponent(mainName + ' ' + subName + ' maharashtra official online portal apply')}`;
        }
    }

    // Video instruction tutorial link
    const tutorialUrl = srv.video_instruction_link;
    if (btnTutorial) {
        if (tutorialUrl && tutorialUrl.trim() !== '') {
            btnTutorial.href = tutorialUrl.trim();
        } else {
            btnTutorial.href = `https://www.youtube.com/results?search_query=${encodeURIComponent(mainName + ' ' + subName + ' form fill up online maharashtra tutorial')}`;
        }
    }

    const overlay = document.getElementById('docs-modal-overlay');
    if (overlay) overlay.style.display = 'flex';
}

function closeDocsModal(e) {
    if (e && e.target && e.target.id !== 'docs-modal-overlay' && !e.target.classList.contains('modal-close-btn') && !e.target.closest('.modal-close-btn') && e.target.tagName !== 'BUTTON') {
        return;
    }
    const overlay = document.getElementById('docs-modal-overlay');
    if (overlay) overlay.style.display = 'none';
}

function copyModalDocsToDetails() {
    if (currentModalDocs && currentModalDocs.length > 0) {
        const text = currentModalDocs.join(', ');
        const detailsInput = document.getElementById('se_work_details');
        if (detailsInput) {
            detailsInput.value = text;
            autoExpandWorkDetails(detailsInput);
        }
        showAlert('Docs Copied', 'आवश्यक कागदपत्रांची यादी सेवेच्या तपशीलात (Work Details) जोडली!');
    } else {
        alert('कॉपी करण्यासाठी कागदपत्रे उपलब्ध नाहीत.');
    }
}

function autoExpandWorkDetails(el) {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(Math.max(el.scrollHeight, 34), 180) + 'px';
    el.title = el.value;
}

function getAppPortalLink(app) {
    if (app.portal_link && app.portal_link.trim() !== '') {
        return app.portal_link.trim();
    }
    const srv = allServices.find(s => s.id == app.service_id || (s.main_service_name === app.service_name && s.sub_service_name === app.sub_service));
    if (srv && (srv.work_portal_link || srv.service_portal_link)) {
        return (srv.work_portal_link || srv.service_portal_link).trim();
    }
    const q = (app.service_name || '') + ' ' + (app.sub_service || '') + ' maharashtra official online portal apply';
    return `https://www.google.com/search?q=${encodeURIComponent(q.trim())}`;
}

function getAppTutorialLink(app) {
    if (app.tutorial_link && app.tutorial_link.trim() !== '') {
        return app.tutorial_link.trim();
    }
    const srv = allServices.find(s => s.id == app.service_id || (s.main_service_name === app.service_name && s.sub_service_name === app.sub_service));
    if (srv && srv.video_instruction_link) {
        return srv.video_instruction_link.trim();
    }
    const q = (app.service_name || '') + ' ' + (app.sub_service || '') + ' form fill up online maharashtra tutorial';
    return `https://www.youtube.com/results?search_query=${encodeURIComponent(q.trim())}`;
}

function createCommittedRowElement(app) {
    const tr = document.createElement('tr');
    tr.className = 'committed-row';
    tr.id = 'committed-row-' + app.id;
    tr.innerHTML = getCommittedRowInnerHtml(app);

    // Apply colored status combobox styling & auto-expand work details
    setTimeout(() => {
        const input = tr.querySelector(`#crow_status_${app.id}`);
        if (input) applyStatusInputStyle(input);

        const wd = tr.querySelector(`#crow_work_details_${app.id}`);
        if (wd) autoExpandWorkDetails(wd);
    }, 0);

    return tr;
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
        <td class="font-mono font-bold" style="color: #0284c7; white-space: nowrap;">
            <a href="${app.show_url || '#'}" target="_blank" style="color: #0284c7; text-decoration: none;" title="Open application details">
                ${escapeHtml(app.application_number)}
            </a>
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
                <button type="button" class="btn-doc-eye-small" onclick="openDocsModalForApp(${app.id})" title="View Required Documents (आवश्यक कागदपत्रे)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </td>
        <td style="text-align: center; padding: 4px 6px; white-space: nowrap;">
            <span class="badge" style="background: #f1f5f9; color: #0369a1; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11px; padding: 4px 7px; border-radius: 5px;">
                ⏱️ ${app.expected_days ? app.expected_days + ' Days' : '-'}
            </span>
        </td>
        <td class="font-mono" style="font-size: 12px; color: #64748b; white-space: nowrap;">
            ${escapeHtml(app.application_date || 'Today')}
        </td>
        <td style="padding: 4px 6px;">
            <div style="position: relative; display: flex; align-items: center;">
                <span style="position: absolute; left: 8px; font-weight: 700; color: #64748b; pointer-events: none; font-size: 12px;">₹</span>
                <input type="number" step="0.01" id="crow_rate_${app.id}" class="excel-cell-input font-mono font-bold"
                       value="${rateNum}"
                       style="padding-left: 20px; font-size: 13.5px;"
                       title="Click to edit rate"
                       oninput="calcCommittedRemaining(${app.id})"
                       onchange="autoSaveCommittedRow(${app.id})"
                       onkeydown="handleCommittedKey(event, ${app.id})">
            </div>
        </td>
        <td style="padding: 4px 6px;">
            <div style="position: relative; display: flex; align-items: center;">
                <span style="position: absolute; left: 8px; font-weight: 700; color: #166534; pointer-events: none; font-size: 12px;">₹</span>
                <input type="number" step="0.01" id="crow_received_${app.id}" class="excel-cell-input font-mono font-bold"
                       value="${recNum}"
                       style="padding-left: 20px; font-size: 13.5px; color: #166534;"
                       title="Click to edit received amount"
                       oninput="calcCommittedRemaining(${app.id})"
                       onchange="autoSaveCommittedRow(${app.id})"
                       onkeydown="handleCommittedKey(event, ${app.id})">
            </div>
        </td>
        <td class="font-mono font-bold" style="padding: 8px 10px; white-space: nowrap;">
            <span id="crow_rem_${app.id}" style="color: ${remColor}; font-size: 13.5px;">
                ₹ ${remNum.toFixed(2)}
            </span>
        </td>
        <td style="padding: 4px 6px;">
            <textarea id="crow_work_details_${app.id}" class="excel-work-details"
                      placeholder="Work details / citizen notes..."
                      rows="1"
                      title="${escapeHtml(app.work_details || '')}"
                      oninput="autoExpandWorkDetails(this)"
                      onchange="autoSaveCommittedRow(${app.id})"
                      onkeydown="handleCommittedKey(event, ${app.id})">${escapeHtml(app.work_details || '')}</textarea>
        </td>
        <td style="padding: 4px 6px;">
            <input type="text" id="crow_status_${app.id}" list="${datalistId}" class="excel-status-input font-bold"
                   value="${escapeHtml(currentStatus)}"
                   placeholder="Status..."
                   title="Pick from dropdown or type custom status for this service"
                   onfocus="ensureServiceStatusDatalist('${escapeHtml(serviceName)}')"
                   oninput="applyStatusInputStyle(this)"
                   onchange="recordCustomStatusForService('${escapeHtml(serviceName)}', this.value); applyStatusInputStyle(this); autoSaveCommittedRow(${app.id})"
                   onkeydown="handleCommittedKey(event, ${app.id})">
        </td>
        <td style="padding: 4px 6px;">
            <select id="crow_operator_${app.id}" class="excel-operator-select"
                    title="Click to assign operator"
                    onchange="autoSaveCommittedRow(${app.id})">
                ${getEmployeeOptionsHtml(app.assigned_employee_id)}
            </select>
        </td>
        <td style="padding: 4px 6px;">
            <input type="date" id="crow_followup_${app.id}" class="excel-date-input"
                   value="${escapeHtml(app.follow_up_date || '')}"
                   title="Expected Follow-up Date (फॉलो-अप तारीख)"
                   onchange="autoSaveCommittedRow(${app.id})">
        </td>
        <td style="text-align: center; white-space: nowrap; padding: 4px 8px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 5px; flex-wrap: nowrap;">
                <a href="${getAppPortalLink(app)}" target="_blank" class="btn-row-action btn-row-apply" title="Apply Now on Official Portal">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Apply
                </a>
                <a href="${getAppTutorialLink(app)}" target="_blank" class="btn-row-action btn-row-tutorial" title="Watch Tutorial Video">
                    <i class="fa-brands fa-youtube"></i> Tutorial
                </a>
                <a href="${app.show_url || '#'}" target="_blank" class="btn-row-action btn-row-view"
                   title="View application details">
                    <i class="fa-solid fa-receipt"></i> View
                </a>
                <span id="saved-indicator-${app.id}" class="saved-pulse-badge" style="display: none;">
                    <i class="fa-solid fa-check"></i>
                </span>
            </div>
        </td>
    `;
}

function handleCommittedKey(e, appId) {
    if (e.key === 'Enter') {
        e.preventDefault();
        e.target.blur(); // Triggers onchange to auto-save immediately
    }
}

function calcCommittedRemaining(appId) {
    const rateEl = document.getElementById(`crow_rate_${appId}`);
    const recEl = document.getElementById(`crow_received_${appId}`);
    const remEl = document.getElementById(`crow_rem_${appId}`);

    if (!rateEl || !remEl) return;

    const rate = parseFloat(rateEl.value) || 0;
    const rec = parseFloat(recEl ? recEl.value : 0) || 0;
    const rem = Math.max(0, rate - rec);

    remEl.innerText = `₹ ${rem.toFixed(2)}`;
    remEl.style.color = rem > 0 ? '#dc2626' : '#166534';
}

function autoSaveCommittedRow(appId) {
    const rateInput = document.getElementById(`crow_rate_${appId}`);
    const receivedInput = document.getElementById(`crow_received_${appId}`);
    const statusInput = document.getElementById(`crow_status_${appId}`);
    const operatorSelect = document.getElementById(`crow_operator_${appId}`);
    const subInput = document.getElementById(`crow_sub_${appId}`);
    const workDetailsInput = document.getElementById(`crow_work_details_${appId}`);
    const followupInput = document.getElementById(`crow_followup_${appId}`);

    if (!rateInput) return;

    const rate = rateInput.value;
    const received = receivedInput ? (receivedInput.value || 0) : 0;
    const status = statusInput ? (statusInput.value.trim().toUpperCase() || 'PENDING') : 'PENDING';
    const operator = operatorSelect ? operatorSelect.value : null;
    const subService = subInput ? subInput.value.trim() : '';
    const workDetails = workDetailsInput ? workDetailsInput.value.trim() : null;
    const followupDate = followupInput ? (followupInput.value || null) : null;

    if (!rate || parseFloat(rate) < 0) {
        rateInput.style.borderColor = '#dc2626';
        alert('कृपया वैध Rate टाका!');
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
                    rate: d.rate.replace(/,/g, ''),
                    received: d.received.replace(/,/g, ''),
                    remaining: d.remaining.replace(/,/g, ''),
                    status: d.status,
                    operator: d.operator,
                    sub_service: d.sub_service,
                    work_details: d.work_details,
                    follow_up_date: d.follow_up_date,
                    assigned_employee_id: d.assigned_employee_id
                };
            }

            // Recalculate remaining display
            calcCommittedRemaining(appId);

            // Update status styling and record status
            if (statusInput) {
                recordCustomStatus(statusInput.value);
                applyStatusInputStyle(statusInput);
            }

            // Row flash animation
            const tr = document.getElementById('committed-row-' + appId);
            if (tr) {
                tr.style.transition = 'background-color 0.3s ease';
                tr.style.backgroundColor = '#f0fdf4';
                setTimeout(() => {
                    tr.style.backgroundColor = '';
                }, 1000);
            }

            // Show subtle saved badge
            const indicator = document.getElementById(`saved-indicator-${appId}`);
            if (indicator) {
                indicator.style.display = 'inline-flex';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 1500);
            }

            // Update customer outstanding on right card
            if (currentLoadedCustomer && d.customer_total_outstanding !== undefined) {
                currentLoadedCustomer.total_outstanding = d.customer_total_outstanding;
                const outEl = document.getElementById('cd-outstanding');
                if (outEl) {
                    outEl.innerText = `₹ ${parseFloat(currentLoadedCustomer.total_outstanding).toFixed(2)}`;
                    outEl.style.color = currentLoadedCustomer.total_outstanding > 0 ? '#dc2626' : '#166534';
                }
            }
        } else {
            alert('Error: ' + (res.message || 'Failed to update service row.'));
        }
    })
    .catch(() => {
        // Network error handling
    });
}

// ==========================================
// 6. ACTIVE ROW SERVICE SELECTION
// ==========================================
function onMainServiceChange(mainName) {
    const subSelect = document.getElementById('se_sub_service_select');
    subSelect.innerHTML = '<option value="">-- Choose Sub Service --</option>';

    // Update status datalist specifically for this main service!
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

    // Show Eye Icon Button for Required Documents Popup
    const eyeBtn = document.getElementById('btn-view-service-docs');
    if (eyeBtn) eyeBtn.style.display = 'inline-flex';

    document.getElementById('se_service_id').value = srv.id;
    document.getElementById('se_sub_service').value = srv.sub_service_name;

    const price = parseFloat(srv.price) || 0;
    document.getElementById('se_rate').value = price;
    document.getElementById('se_received').value = price;
    calculateRemaining();

    const days = parseInt(srv.expected_processing_days) || 7;
    const expBadge = document.getElementById('se_expected_days_badge');
    if (expBadge) {
        expBadge.innerText = days + ' Days';
    }

    const docs = Array.isArray(srv.required_documents_json) ? srv.required_documents_json.join(', ') : '';
    document.getElementById('se_docs_list').value = docs;

    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + days);
    document.getElementById('se_followup').value = targetDate.toISOString().split('T')[0];

    // Update Row-level Apply Now & Watch Tutorial buttons
    const rowBtnApply = document.getElementById('row-btn-apply');
    const rowBtnTutorial = document.getElementById('row-btn-tutorial');

    const workUrl = srv.work_portal_link || srv.service_portal_link;
    const tutorialUrl = srv.video_instruction_link;

    if (rowBtnApply) {
        if (workUrl && workUrl.trim() !== '') {
            rowBtnApply.href = workUrl.trim();
        } else {
            rowBtnApply.href = `https://www.google.com/search?q=${encodeURIComponent(srv.main_service_name + ' ' + (srv.sub_service_name || '') + ' maharashtra official online portal apply')}`;
        }
        rowBtnApply.style.display = 'inline-flex';
    }

    if (rowBtnTutorial) {
        if (tutorialUrl && tutorialUrl.trim() !== '') {
            rowBtnTutorial.href = tutorialUrl.trim();
        } else {
            rowBtnTutorial.href = `https://www.youtube.com/results?search_query=${encodeURIComponent(srv.main_service_name + ' ' + (srv.sub_service_name || '') + ' form fill up online maharashtra tutorial')}`;
        }
        rowBtnTutorial.style.display = 'inline-flex';
    }

    // Auto-focus on Received amount so user can hit Enter or adjust!
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
    if (expBadge) {
        expBadge.innerText = '-';
    }

    const rowBtnApply = document.getElementById('row-btn-apply');
    if (rowBtnApply) rowBtnApply.style.display = 'none';

    const rowBtnTutorial = document.getElementById('row-btn-tutorial');
    if (rowBtnTutorial) rowBtnTutorial.style.display = 'none';

    document.getElementById('se_service_id').value = '';
    document.getElementById('se_sub_service').value = '';
    document.getElementById('se_rate').value = '';
    document.getElementById('se_received').value = '';
    document.getElementById('se_remaining').value = '';
    document.getElementById('se_work_details').value = '';
    document.getElementById('se_work_details').style.height = '34px';
    document.getElementById('se_docs_list').value = '';
    document.getElementById('se_followup').value = '';
}

function copyDocsToActiveRow() {
    const docsText = document.getElementById('ob-docs-text').innerText;
    if (docsText) {
        const detailsInput = document.getElementById('se_work_details');
        detailsInput.value = docsText;
        autoExpandWorkDetails(detailsInput);
        showAlert('Docs Copied', 'Documents checklist added to service work details.');
    }
}

// Enter Key handler inside row cells
function handleRowKeyDown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        saveActiveServiceRow();
    }
}

// ==========================================
// 7. SAVE ACTIVE ROW & CONTINUOUS EXCEL ENTRY
// ==========================================
function saveActiveServiceRow() {
    const custId = document.getElementById('se_customer_id').value;
    if (!custId) {
        alert('प्रथम डाव्या बाजूला ग्राहक नाव व मोबाईल नंबर टाकून Go दाबा! (Please select customer first)');
        document.getElementById('fe_mobile').focus();
        return;
    }

    const serviceId = document.getElementById('se_service_id').value;
    if (!serviceId) {
        alert('कृपया Main Service आणि Sub Service निवडा!');
        document.getElementById('se_main_service').focus();
        return;
    }

    const rate = document.getElementById('se_rate').value;
    if (!rate || parseFloat(rate) < 0) {
        alert('कृपया वैध Rate टाका!');
        document.getElementById('se_rate').focus();
        return;
    }

    const btn = document.getElementById('btn-save-row');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_id: custId,
        service_id: serviceId,
        sub_service: document.getElementById('se_sub_service').value.trim() || null,
        rate: rate,
        received: document.getElementById('se_received').value || 0,
        work_details: document.getElementById('se_work_details').value.trim() || null,
        documents_list: document.getElementById('se_docs_list').value.trim() || null,
        follow_up_date: document.getElementById('se_followup').value || null,
        work_status: (document.getElementById('se_status').value || 'IN_PROGRESS').trim().toUpperCase(),
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
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save (Enter)';

        if (res.success) {
            const d = res.data;
            const tbody = document.getElementById('services-tbody');
            const activeRow = document.getElementById('active-service-row');

            // Record custom status to datalist & local storage
            recordCustomStatus(payload.work_status);

            // 1. Create and Insert Committed Row immediately above Active Row
            const newApp = {
                id: d.id,
                application_number: d.application_number,
                service_id: serviceId,
                service_name: d.service_name,
                sub_service: d.sub_service,
                application_date: 'Today',
                rate: d.rate.replace(/,/g, ''),
                received: d.received.replace(/,/g, ''),
                remaining: d.remaining.replace(/,/g, ''),
                status: payload.work_status || 'READY',
                work_details: d.work_details || payload.work_details || '',
                follow_up_date: d.follow_up_date || payload.follow_up_date || '',
                portal_link: d.portal_link,
                tutorial_link: d.tutorial_link,
                assigned_employee_id: payload.assigned_employee_id,
                operator: d.operator,
                show_url: d.show_url || '#'
            };
            loadedApplications[d.id] = newApp;
            const committedRow = createCommittedRowElement(newApp);
            activeRow.after(committedRow);

            // 2. Update Customer Summary Badges in Real-time
            if (currentLoadedCustomer) {
                currentLoadedCustomer.total_services = (currentLoadedCustomer.total_services || 0) + 1;
                document.getElementById('cd-services-count').innerText = `${currentLoadedCustomer.total_services} Services`;
                document.getElementById('services-badge').innerText = `${tbody.querySelectorAll('tr.committed-row').length} Services`;

                const remNum = parseFloat(d.remaining.replace(/[^0-9.]/g, '')) || 0;
                if (remNum > 0) {
                    currentLoadedCustomer.total_outstanding = (currentLoadedCustomer.total_outstanding || 0) + remNum;
                    const outEl = document.getElementById('cd-outstanding');
                    outEl.innerText = `₹ ${currentLoadedCustomer.total_outstanding.toFixed(2)}`;
                    outEl.style.color = '#dc2626';
                }
            }

            showAlert('Service Recorded!', `${d.service_name} successfully saved. Next row is ready!`);

            // 3. Reset Active Row & Keep Ready for Next Service (Excel Feel!)
            document.getElementById('se_main_service').value = '';
            const subSel = document.getElementById('se_sub_service_select');
            subSel.innerHTML = '<option value="">-- Choose Sub Service --</option>';
            subSel.disabled = true;
            resetActiveRowFields();

            const stInput = document.getElementById('se_status');
            if (stInput) {
                stInput.value = 'IN_PROGRESS';
                applyStatusInputStyle(stInput);
            }

            // Auto-focus back on Main Service for next entry
            document.getElementById('se_main_service').focus();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save (Enter)';
        alert('Network error while recording service.');
    });
}

// ==========================================
// 8. RESET ENTIRE DESK
// ==========================================
function resetEntireDesk() {
    currentLoadedCustomer = null;

    // Reset Left Form
    document.getElementById('fe_name').value = '';
    document.getElementById('fe_mobile').value = '';

    // Hide Right Card & Bottom Services Card
    const grid = document.getElementById('pocket-top-grid');
    if (grid) grid.classList.remove('has-customer');

    const rightCard = document.getElementById('right-customer-card');
    if (rightCard) rightCard.style.display = 'none';

    const bottomCard = document.getElementById('bottom-services-card');
    if (bottomCard) bottomCard.style.display = 'none';

    document.getElementById('current_cust_id').value = '';
    document.getElementById('cd-status-badge').className = 'pocket-badge-neutral';
    document.getElementById('cd-status-badge').innerText = 'No Customer Selected';

    // Remove committed rows from Unified Table
    const tbody = document.getElementById('services-tbody');
    tbody.querySelectorAll('tr.committed-row').forEach(r => r.remove());
    document.getElementById('services-badge').innerText = '0 Services';

    // Reset active row
    document.getElementById('se_customer_id').value = '';
    document.getElementById('se_main_service').value = '';
    const subSelect = document.getElementById('se_sub_service_select');
    subSelect.innerHTML = '<option value="">-- Choose Sub Service --</option>';
    subSelect.disabled = true;
    resetActiveRowFields();

    const seStatus = document.getElementById('se_status');
    if (seStatus) {
        seStatus.value = 'IN_PROGRESS';
        applyStatusInputStyle(seStatus);
    }

    document.getElementById('fe_name').focus();
}

function showAlert(title, desc) {
    const alertBox = document.getElementById('save-alert');
    document.getElementById('alert-title').innerText = title;
    document.getElementById('alert-desc').innerText = desc;
    alertBox.style.display = 'flex';
}

// Click anywhere on cell to focus its input/select
document.addEventListener('click', function(e) {
    const td = e.target.closest('tr.committed-row td');
    if (td && e.target === td) {
        const input = td.querySelector('.excel-cell-input, .excel-status-input, .excel-operator-select');
        if (input) {
            input.focus();
            if (input.select && typeof input.select === 'function') {
                input.select();
            }
        }
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('docs-modal-overlay');
        if (overlay && overlay.style.display !== 'none') {
            overlay.style.display = 'none';
        }
    }
});

// Initialize Status Datalist & Colors on boot
document.addEventListener('DOMContentLoaded', function() {
    initCustomStatuses();
});
initCustomStatuses();
</script>
@endpush
@endsection
