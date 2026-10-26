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
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    @media (min-width: 1400px) {
        .details-summary-grid {
            grid-template-columns: repeat(5, 1fr);
        }
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
        position: relative;
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

    /* ========================================================================= */
    /* STICKY RIGHT STATUS COLUMN (FREEZE PANE ON RIGHT SIDE)                     */
    /* ========================================================================= */
    .pocket-excel-table .sticky-col-status {
        position: -webkit-sticky !important;
        position: sticky !important;
        right: 0 !important;
        min-width: 185px !important;
        width: 185px !important;
        box-sizing: border-box !important;
    }

    .pocket-excel-table th.sticky-col-status {
        z-index: 35 !important;
        background: #0f172a !important;
        color: #f59e0b !important;
        border-left: 2px solid #334155 !important;
        box-shadow: -5px 0 12px -2px rgba(0, 0, 0, 0.5) !important;
    }

    .pocket-excel-table tr.active-entry-row td.sticky-col-status {
        z-index: 25 !important;
        background: #fffbeb !important;
        border-left: 2px solid #f59e0b !important;
        border-top: 2px solid #f59e0b !important;
        border-bottom: 2px solid #f59e0b !important;
        box-shadow: -5px 0 12px -2px rgba(245, 158, 11, 0.35) !important;
    }

    .pocket-excel-table tbody tr.committed-row td.sticky-col-status {
        z-index: 20 !important;
        background: #ffffff !important;
        border-left: 2px solid #cbd5e1 !important;
        box-shadow: -5px 0 10px -2px rgba(15, 23, 42, 0.12) !important;
    }

    .pocket-excel-table tbody tr.committed-row:hover td.sticky-col-status {
        background: #f0f9ff !important;
    }

    .pocket-excel-table tbody tr.newly-saved-row-pulse td.sticky-col-status {
        background: #dcfce7 !important;
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

    /* Committed Row Locked vs Unlocked Mode */
    .pocket-excel-table tr.committed-row.locked-row {
        background: #ffffff;
    }
    .pocket-excel-table tr.committed-row.locked-row:hover {
        background: #f8fafc;
    }
    .pocket-excel-table tr.committed-row.unlocked-row {
        background: #f0fdf4 !important;
        box-shadow: inset 0 0 0 2px #22c55e !important;
    }

    .crow-view-mode {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .crow-edit-mode {
        display: none !important;
        align-items: center;
        gap: 4px;
    }
    .unlocked-row .crow-view-mode {
        display: none !important;
    }
    .unlocked-row .crow-edit-mode {
        display: flex !important;
    }

    .crow-view-text {
        font-size: 13px;
        color: #0f172a;
        font-weight: 600;
        user-select: text;
    }
    .crow-view-rate {
        font-size: 13.5px;
        color: #0f172a;
        font-weight: 800;
        font-family: var(--font-mono, monospace);
    }
    .crow-view-notes {
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 12px;
        color: #334155;
        font-weight: 500;
        cursor: help;
    }
    .crow-view-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .btn-row-action.btn-row-edit {
        background: #eff6ff;
        color: #0284c7;
        border: 1.5px solid #bae6fd;
        font-weight: 700;
    }
    .btn-row-action.btn-row-edit:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }
    .btn-row-action.btn-row-done {
        background: #16a34a !important;
        color: #ffffff !important;
        border: 1.5px solid #15803d !important;
        font-weight: 800;
    }
    .btn-row-action.btn-row-done:hover {
        background: #15803d !important;
    }

    .pocket-badge-danger {
        background: #fee2e2 !important;
        color: #dc2626 !important;
        border: 1px solid #f87171 !important;
    }

    @keyframes pulseNewSavedRow {
        0% { background-color: #86efac; }
        50% { background-color: #bbf7d0; }
        100% { background-color: transparent; }
    }
    .newly-saved-row-pulse {
        animation: pulseNewSavedRow 2s ease-out;
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

    /* Modal Overlay & Card for Required Docs & New Customer Confirmation */
    #docs-modal-overlay,
    .modal-overlay#docs-modal-overlay,
    #new-customer-confirm-modal,
    .modal-overlay#new-customer-confirm-modal {
        position: fixed !important;
        inset: 0 !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.82) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        z-index: 999999999 !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px !important;
        opacity: 0 !important;
        visibility: hidden !important;
        transition: opacity 0.2s ease, visibility 0.2s ease !important;
    }

    #docs-modal-overlay.open,
    .modal-overlay#docs-modal-overlay.open,
    #new-customer-confirm-modal.open,
    .modal-overlay#new-customer-confirm-modal.open {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
    }

    .docs-modal-card {
        position: relative !important;
        z-index: 100000000 !important;
        background: #ffffff !important;
        width: 100% !important;
        max-width: 560px !important;
        border-radius: 12px !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6) !important;
        border: 1.5px solid #cbd5e1 !important;
        overflow: hidden !important;
        animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .btn-doc-eye i,
    .btn-doc-eye-small i {
        pointer-events: none;
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
    <!-- SERVICES QUICK CATALOG & DOCUMENT CHECKLIST (Above Customer Quick Entry)  -->
    <!-- ========================================================================= -->
    @php
        $uniqueMainServices = $services->pluck('main_service_name')->filter()->unique()->sort()->values();
    @endphp
    <div class="pocket-card" style="border: 1.5px solid #cbd5e1; margin-bottom: 8px; background: #ffffff;">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-radius: 8px 8px 0 0;">
            <div style="font-size: 13px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-layer-group" style="color: #38bdf8; font-size: 14px;"></i>
                <span>SERVICES MASTER CATALOG & RATES</span>
                <span class="badge" style="background: #334155; color: #38bdf8; font-size: 10px; font-weight: 700; border-radius: 8px; padding: 2px 7px;">
                    {{ $services->count() }} Services
                </span>
            </div>

            <!-- Dropdown Selectors -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- 1. Main Service Dropdown -->
                <div style="display: flex; align-items: center; gap: 5px;">
                    <label style="font-size: 11px; font-weight: 700; color: #94a3b8; margin: 0; white-space: nowrap;">Main:</label>
                    <select id="catalog_main_service_select" onchange="onCatalogMainSelectChange(this.value)"
                            style="height: 30px; font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #ffffff; min-width: 170px; outline: none; cursor: pointer;">
                        <option value="">-- Choose Main Service --</option>
                        @foreach($uniqueMainServices as $mainName)
                            <option value="{{ $mainName }}">{{ strtoupper($mainName) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Sub Service Dropdown -->
                <div style="display: flex; align-items: center; gap: 5px;">
                    <label style="font-size: 11px; font-weight: 700; color: #94a3b8; margin: 0; white-space: nowrap;">Sub:</label>
                    <select id="catalog_sub_service_select" onchange="onCatalogSubSelectChange(this.value)" disabled
                            style="height: 30px; font-size: 11.5px; font-weight: 600; padding: 2px 8px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #ffffff; min-width: 210px; max-width: 340px; outline: none; cursor: pointer;">
                        <option value="">-- Choose Sub Service --</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button type="button" onclick="resetCatalogSelection()" title="Reset Selection"
                        style="height: 30px; padding: 2px 10px; background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>

        <!-- Particular Service Details Display Strip -->
        <div id="catalog-card-body" style="padding: 10px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
            <!-- Placeholder (when nothing selected) -->
            <div id="catalog-empty-placeholder" style="display: flex; align-items: center; justify-content: space-between; color: #64748b; font-size: 12px; padding: 3px 4px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-info" style="color: #0284c7; font-size: 13px;"></i>
                    <span>वरून <strong>Main Service</strong> व <strong>Sub Service</strong> निवडा — त्या विशिष्ट सेवेचे दर, अपेक्षित दिवस आणि 👁️ कागदपत्रे बटण येथे दिसेल.</span>
                </div>
                <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">Fast Counter Catalog</span>
            </div>

            <!-- Particular Service Details (Visible when selected) -->
            <div id="catalog-selected-view" style="display: none; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <!-- Left: Service Identity -->
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span id="cat-view-main" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 6px; border: 1px solid #c7d2fe; text-transform: uppercase;">
                        MAIN SERVICE
                    </span>
                    <span id="cat-view-sub" style="font-size: 13px; font-weight: 800; color: #0f172a;">
                        Sub Service Name
                    </span>
                </div>

                <!-- Right: Price, Expected Days, Document Eye Icon, and Use Button -->
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <!-- Price -->
                    <div style="display: flex; align-items: baseline; gap: 4px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 3px 10px; border-radius: 6px;">
                        <span style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase;">दर (Price):</span>
                        <span id="cat-view-price" style="font-size: 14.5px; font-weight: 800; font-family: var(--font-mono); color: #166534;">
                            ₹ 0.00
                        </span>
                    </div>

                    <!-- Expected Days -->
                    <div style="background: #e0f2fe; border: 1px solid #bae6fd; padding: 3px 9px; border-radius: 6px; font-size: 11.5px; font-weight: 700; color: #0369a1; display: flex; align-items: center; gap: 4px;">
                        <i class="fa-regular fa-clock" style="color: #0284c7;"></i>
                        <span id="cat-view-days">7 Days</span>
                    </div>

                    <!-- Eye Icon for Documents -->
                    <button type="button" id="cat-view-doc-btn" onclick="openCatalogServiceDocs()" class="btn-doc-eye"
                            title="View Required Documents" style="width: auto; height: 30px; padding: 0 12px; gap: 6px; font-size: 11.5px; font-weight: 700; background: #eff6ff; border: 1.5px solid #3b82f6; color: #1d4ed8;">
                        <i class="fa-solid fa-eye" style="font-size: 13px; color: #2563eb;"></i>
                        <span>लागणारी कागदपत्रे (Documents)</span>
                    </button>
                </div>
            </div>
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
                            Customer Name
                        </label>
                        <input type="text" id="fe_name" name="customer_name" class="pocket-input" 
                               placeholder="e.g. VINOD KADAM" autocomplete="off"
                               style="text-transform: uppercase;" autofocus>
                    </div>

                    <!-- 2. Mobile Number -->
                    <div style="margin-bottom: 14px;">
                        <label class="pocket-label required" for="fe_mobile">
                            Mobile Number
                        </label>
                        <input type="tel" id="fe_mobile" name="mobile" class="pocket-input font-mono" required 
                               placeholder="10 Digits (e.g. 9527667881)" maxlength="10" pattern="[0-9]{10}" autocomplete="off">
                    </div>

                    <!-- Action Buttons: Go & Reset -->
                    <div class="pocket-btn-group">
                        <button type="submit" id="btn-go" class="btn-go" onclick="executeQuickGo(event)">
                            <i class="fa-solid fa-arrow-right"></i> Go
                        </button>
                        <button type="button" id="btn-reset" class="btn-reset" onclick="resetEntireDesk()">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                    </div>

                    <div class="pocket-hint-note">
                        <i class="fa-solid fa-circle-check" style="color: #0284c7;"></i> 
                        Enter Mobile & Name, then click <strong>Go</strong>. Loads existing or prompts to create new!
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT BOX: Customer Details (Click-To-Edit + Instant AutoSave) -->
        <div class="pocket-card" id="right-customer-card" style="display: none;">
            <div class="pocket-card-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="card-title">
                    <i class="fa-solid fa-address-card" style="color: #38bdf8;"></i>
                    <span>Customer Details (Click to edit - Auto-Saves)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="cd-status-badge" class="pocket-badge-neutral">
                        No Customer Selected
                    </span>
                    <span id="cd-blacklist-badge" class="badge" style="display: none; background: #dc2626; color: white; font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 5px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-ban"></i> BLACKLISTED
                    </span>
                </div>
            </div>

            <div class="pocket-card-body">
                <!-- Empty State -->
                <div id="cust-empty-state" class="cust-empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <div class="empty-title">No Customer Selected</div>
                    <div class="empty-desc">
                        Enter customer name and 10-digit mobile number on the left, then click <strong>Go</strong>.
                    </div>
                </div>

                <!-- Loaded State (Sender Details Card with Direct Inline Editable Fields) -->
                <div id="cust-loaded-state" style="display: none;">
                    <input type="hidden" id="current_cust_id" value="">

                    <!-- Blacklisted Customer Alert Warning Banner -->
                    <div id="cd-blacklist-banner" style="display: none; background: #fef2f2; border: 1.5px solid #ef4444; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; font-size: 18px;"></i>
                            <div>
                                <div style="font-size: 13px; font-weight: 800; color: #b91c1c;">⚠️ BLACKLISTED CUSTOMER</div>
                                <div id="cd-blacklist-reason-text" style="font-size: 11.5px; color: #7f1d1d; font-weight: 600;"></div>
                            </div>
                        </div>
                        <button type="button" onclick="toggleCustomerBlacklist(false)" class="btn btn-sm" style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; background: #ffffff; color: #dc2626; border: 1px solid #ef4444; cursor: pointer;">
                            <i class="fa-solid fa-unlock"></i> Remove Blacklist
                        </button>
                    </div>

                    <div class="details-summary-grid">
                        <!-- 1. NAME (Click to edit directly) -->
                        <div class="summary-item" title="Click to edit Name (Auto-saves on blur/Enter)">
                            <span class="summary-label">
                                <span>Name (Click to edit)</span>
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

                        <!-- 2b. ALTERNATE MOBILE (Click to edit) -->
                        <div class="summary-item" title="Click to edit Alternate Mobile (Auto-saves on blur/Enter)">
                            <span class="summary-label">
                                <span>Alt Mobile (Click to edit)</span>
                                <i class="fa-solid fa-pencil" style="font-size: 10px; color: #0284c7;"></i>
                            </span>
                            <input type="tel" id="cd-inline-alt-mobile" class="inline-edit-input font-mono" placeholder="Alternate Mobile" maxlength="20"
                                   onblur="autoSaveCustomerField('alternate_mobile', this.value)"
                                   onkeydown="if(event.key==='Enter'){this.blur();}">
                            <span class="save-indicator" id="ind-alt-mobile">✓ Saved</span>
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
                                <span>Rating (Keys 1-5 or Click)</span>
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
                            <span class="summary-label">Gender</span>
                            <select id="cd-inline-gender" class="inline-edit-select" onchange="autoSaveCustomerField('gender', this.value)">
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                                <option value="OTHER">Other</option>
                            </select>
                        </div>

                        <!-- 8. OCCUPATION (Click to choose or Add New) -->
                        <div class="summary-item" title="Click to choose or add occupation (Auto-saves)">
                            <span class="summary-label">
                                <span>Occupation</span>
                                <span class="save-indicator" id="ind-occ" style="display: none;">✓ Saved</span>
                            </span>
                            <select id="cd-inline-occ" class="inline-edit-select" onchange="onOccupationSelectChange(this)">
                                <option value="">-- Select Occupation --</option>
                            </select>
                        </div>

                        <!-- 9. BIRTH DATE -->
                        <div class="summary-item" title="Click to edit birth date">
                            <span class="summary-label">Birth Date</span>
                            <input type="date" id="cd-inline-dob" class="inline-edit-input" style="font-size: 13px;"
                                   onchange="autoSaveCustomerField('birth_date', this.value)">
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 8px;">
                        <button type="button" id="btn-toggle-blacklist" onclick="promptCustomerBlacklist()" class="btn btn-sm" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: 700; font-size: 11.5px; border-radius: 6px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                            <i class="fa-solid fa-ban"></i> <span id="btn-blacklist-label">Blacklist Customer</span>
                        </button>
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
                <span>CUSTOMER SERVICES (New Service Entry Row on Top + All Services Below)</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div id="fast-entry-filter-toolbar"></div>
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
                <table id="fast-entry-table" class="pocket-excel-table">
                    <thead>
                        <tr>
                            <th style="min-width: 130px;"># SR Number</th>
                            <th style="min-width: 160px;">
                                <div class="excel-th-inner">
                                    <span>1. Main Service *</span>
                                    <button type="button" class="excel-col-filter-btn" data-col="main_service" title="Filter by Main Service">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                </div>
                            </th>
                            <th style="min-width: 210px;">
                                <div class="excel-th-inner">
                                    <span>2. Sub Service *</span>
                                    <button type="button" class="excel-col-filter-btn" data-col="sub_service" title="Filter by Sub Service">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                </div>
                            </th>
                            <th style="min-width: 110px; color: #38bdf8; text-align: center;">Expected Days</th>
                            <th style="min-width: 90px;">3. Rate (₹) *</th>
                            <th style="min-width: 95px; color: #86efac;">4. Received (₹)</th>
                            <th style="min-width: 95px;">5. Remaining (₹)</th>
                            <th style="min-width: 180px; color: #38bdf8;">6. Work Details</th>
                            <th style="min-width: 160px; color: #10b981;">7. Pending Remark</th>
                            <th style="min-width: 145px;">
                                <div class="excel-th-inner">
                                    <span>8. Operator</span>
                                    <button type="button" class="excel-col-filter-btn" data-col="operator" title="Filter by Operator">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                </div>
                            </th>
                            <th style="min-width: 145px; color: #fbbf24;">
                                <div class="excel-th-inner">
                                    <span>9. Follow-Up Date</span>
                                    <button type="button" class="excel-col-filter-btn" data-col="followup_date" title="Filter by Follow-Up Date">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                </div>
                            </th>
                            <th style="min-width: 240px; text-align: center;">Actions & Quick Links</th>
                            <!-- 10. Status (Fixed Sticky Column on Right Side) -->
                            <th class="sticky-col-status" style="min-width: 185px; text-align: center;">
                                <div class="excel-th-inner" style="justify-content: center; gap: 6px;">
                                    <span><i class="fa-solid fa-thumbtack" style="font-size: 11px; transform: rotate(45deg); color: #f59e0b;"></i> 10. Status</span>
                                    <button type="button" class="excel-col-filter-btn" data-col="status" title="Filter by Status">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="services-tbody">
                        <!-- COMMITTED / EXISTING ROWS WILL BE INSERTED HERE DYNAMICALLY -->

                        <!-- ACTIVE SPREADSHEET INPUT ROW (Always present right below existing rows!) -->
                        <tr id="active-service-row" class="active-entry-row">
                            <!-- SR Number Column -->
                            <td style="padding: 6px 8px;">
                                <span class="badge" style="background: #f59e0b; color: white; font-weight: 800; font-size: 11px; padding: 4px 7px; border-radius: 5px; font-family: var(--font-mono);">
                                    <i class="fa-solid fa-plus"></i> NEW ROW
                                </span>
                                <div style="font-size: 10.5px; color: #64748b; font-family: var(--font-mono); font-weight: 600; margin-top: 3px;">
                                    {{ now()->format('d-m-Y h:i A') }}
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
                                <div style="display: flex; align-items: center; gap: 6px;">
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
                                      style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 700; font-size: 11.5px; padding: 4px 8px; border-radius: 5px;">
                                    -
                                </span>
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

                            <!-- 7. Pending Remark (Auto-expandable & Resizable) -->
                            <td style="padding: 4px 6px;">
                                <textarea id="se_payment_remarks" class="excel-work-details"
                                          placeholder="Pending remark / notes..."
                                          rows="1"
                                          title="Pending Remark: Pending reason, documents needed, notes, etc."
                                          oninput="autoExpandWorkDetails(this)"
                                          onkeydown="handleRowKeyDown(event)"></textarea>
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
                                       title="Expected Follow-up Date"
                                       onkeydown="handleRowKeyDown(event)">
                            </td>

                            <!-- Actions & Quick Links: Save + Apply + Tutorial -->
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

                            <!-- 10. Status (Fixed Sticky Column on Right Side) -->
                            <td class="sticky-col-status" style="padding: 4px 6px;">
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <select id="se_status" class="excel-select font-bold status-select"
                                            data-prev="IN_PROGRESS"
                                            onchange="onActiveStatusSelectChange(this)"
                                            onkeydown="handleRowKeyDown(event)"
                                            style="height: 38px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; flex: 1;">
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
                                    <button type="button" id="btn-save-sticky" class="btn-save-excel-row" onclick="saveActiveServiceRow()" title="Save Service Entry (Enter)"
                                            style="height: 38px; padding: 0 10px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-weight: 800; font-size: 12px; gap: 4px; white-space: nowrap; flex-shrink: 0;">
                                        <i class="fa-solid fa-check"></i> Save
                                    </button>
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

<!-- Modal: New Customer Confirmation Popup -->
<div id="new-customer-confirm-modal" class="modal-overlay">
    <div class="confirm-modal-card" style="position: relative; background: #ffffff; width: 100%; max-width: 490px; border-radius: 14px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5); border: 1.5px solid #cbd5e1; overflow: hidden; animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #f8fafc; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(2, 132, 199, 0.25); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff;">Customer Not Found</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: #94a3b8; font-weight: 500;">Register New Customer Confirmation</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeNewCustomerModal()" title="Close" style="background: transparent; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; line-height: 1; padding: 2px 6px; border-radius: 6px;">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div style="padding: 22px 24px;">
            <!-- Notice Box with Mobile Number -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <div>
                    <div style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Mobile Number</div>
                    <div style="font-size: 17px; font-weight: 800; color: #0f172a; font-family: monospace;" id="confirm-modal-mobile-text">
                        9876543210
                    </div>
                </div>
            </div>

            <p style="font-size: 13.5px; color: #334155; margin: 0 0 16px 0; line-height: 1.5;">
                This mobile number is <strong>not registered</strong> in the customer database. Would you like to create a new customer profile?
            </p>

            <!-- Customer Name Input -->
            <div style="margin-bottom: 14px;">
                <label for="confirm-modal-name-input" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    Customer Full Name
                </label>
                <input type="text" id="confirm-modal-name-input" class="form-control"
                       placeholder="e.g. VINOD KADAM"
                       style="height: 44px; font-size: 14px; font-weight: 700; text-transform: uppercase; border: 2px solid #cbd5e1; border-radius: 8px;"
                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); confirmAndCreateCustomer(); }">
                <small style="color: #64748b; font-size: 11.5px; margin-top: 4px; display: block;">
                    Verify or type the customer's full name before registering.
                </small>
            </div>

            <!-- Alternate Mobile (Optional) -->
            <div style="margin-bottom: 20px;">
                <label for="confirm-modal-alt-mobile-input" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    Alternate Mobile Number (Optional)
                </label>
                <input type="tel" id="confirm-modal-alt-mobile-input" class="form-control font-mono"
                       placeholder="e.g. 9822012345" maxlength="20"
                       style="height: 42px; font-size: 14px; font-weight: 600; border: 1.5px solid #cbd5e1; border-radius: 8px;"
                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); confirmAndCreateCustomer(); }">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <button type="button" class="btn btn-secondary" onclick="closeNewCustomerModal()" style="font-weight: 600; padding: 10px 18px; font-size: 13px; border-radius: 8px;">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button type="button" id="btn-confirm-create-customer" onclick="confirmAndCreateCustomer()" class="btn btn-primary"
                        style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-color: #0284c7; font-weight: 700; padding: 10px 22px; font-size: 13.5px; border-radius: 8px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
                    <i class="fa-solid fa-user-plus"></i> Create Customer Profile
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
// 2. QUICK GO: SEARCH / PROMPT TO CREATE IN CRM
// ==========================================
function executeQuickGo(e, confirmCreate = false) {
    if (e) e.preventDefault();

    const mobileInput = document.getElementById('fe_mobile');
    const nameInput = document.getElementById('fe_name');
    const btnGo = document.getElementById('btn-go');

    const mobile = mobileInput.value.trim().replace(/[^0-9]/g, '');
    const name = nameInput.value.trim();

    if (mobile.length !== 10) {
        alert('Please enter a valid 10-digit mobile number');
        mobileInput.focus();
        return;
    }

    btnGo.disabled = true;
    btnGo.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    fetch('{{ route("fast_entry.quick_go") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            mobile: mobile,
            customer_name: name,
            confirm_create: confirmCreate,
            alternate_mobile: document.getElementById('confirm-modal-alt-mobile-input')?.value?.trim() || null
        })
    })
    .then(async res => {
        if (!res) return null;
        if (res.status === 419) {
            alert('Your session was refreshed. Reloading page...');
            window.location.reload();
            return null;
        }
        return res.json();
    })
    .then(res => {
        if (!res) return;
        btnGo.disabled = false;
        btnGo.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Go';

        if (res.success) {
            // Case 1: Customer DOES NOT exist and creation was not yet confirmed
            if (res.exists === false) {
                showNewCustomerModal(res.mobile, res.customer_name);
                return;
            }

            // Case 2: Customer exists or has just been confirmed and created
            closeNewCustomerModal();
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
        const createBtn = document.getElementById('btn-confirm-create-customer');
        if (createBtn) {
            createBtn.disabled = false;
            createBtn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Create Customer Profile';
        }
        alert('Network error while processing customer.');
    });
}

function showNewCustomerModal(mobile, name) {
    const modal = document.getElementById('new-customer-confirm-modal');
    const mobileText = document.getElementById('confirm-modal-mobile-text');
    const nameInput = document.getElementById('confirm-modal-name-input');
    const createBtn = document.getElementById('btn-confirm-create-customer');

    if (mobileText) mobileText.textContent = mobile;
    if (nameInput) nameInput.value = name || document.getElementById('fe_name').value.trim() || '';
    if (createBtn) {
        createBtn.disabled = false;
        createBtn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Create Customer Profile';
    }

    if (modal) {
        modal.classList.add('open');
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.setProperty('opacity', '1', 'important');
        modal.style.setProperty('visibility', 'visible', 'important');
        modal.style.setProperty('z-index', '999999999', 'important');
        setTimeout(() => {
            if (nameInput) {
                nameInput.focus();
                nameInput.select();
            }
        }, 100);
    }
}

function confirmAndCreateCustomer() {
    const btn = document.getElementById('btn-confirm-create-customer');
    const nameInput = document.getElementById('confirm-modal-name-input');
    const custName = (nameInput ? nameInput.value.trim() : '');

    // Sync name back to the main fast-entry name input
    const feName = document.getElementById('fe_name');
    if (feName && custName) {
        feName.value = custName;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registering...';
    }

    executeQuickGo(null, true);
}

function closeNewCustomerModal() {
    const modal = document.getElementById('new-customer-confirm-modal');
    if (modal) {
        modal.classList.remove('open');
        modal.style.setProperty('display', 'none', 'important');
        modal.style.setProperty('opacity', '0', 'important');
        modal.style.setProperty('visibility', 'hidden', 'important');
    }

    const btn = document.getElementById('btn-confirm-create-customer');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Create Customer Profile';
    }

    const feMobile = document.getElementById('fe_mobile');
    if (feMobile) feMobile.focus();
}

// Close modal on backdrop click or Escape key
document.addEventListener('DOMContentLoaded', function() {
    const newCustModal = document.getElementById('new-customer-confirm-modal');
    if (newCustModal) {
        newCustModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeNewCustomerModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('new-customer-confirm-modal');
            if (modal && modal.style.display === 'flex') {
                closeNewCustomerModal();
            }
        }
    });
});

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
    if (cust.is_blacklisted) {
        statusBadge.className = 'pocket-badge-danger';
        statusBadge.innerText = '⛔ BLACKLISTED';
    } else if (isNew) {
        statusBadge.className = 'pocket-badge-active';
        statusBadge.innerText = 'Auto-Saved to CRM';
    } else {
        statusBadge.className = 'pocket-badge-active';
        statusBadge.innerText = 'Existing Active Customer';
    }

    document.getElementById('current_cust_id').value = cust.id;
    document.getElementById('cd-inline-name').value = cust.name;
    document.getElementById('cd-mobile').innerText = cust.mobile;
    const altMobInput = document.getElementById('cd-inline-alt-mobile');
    if (altMobInput) altMobInput.value = cust.alternate_mobile || '';
    document.getElementById('cd-code').innerText = cust.customer_code;
    document.getElementById('cd-services-count').innerText = `${cust.total_services} Services`;
    
    const outEl = document.getElementById('cd-outstanding');
    outEl.innerText = `₹ ${parseFloat(cust.total_outstanding).toFixed(2)}`;
    outEl.style.color = cust.total_outstanding > 0 ? '#dc2626' : '#166534';

    // Rating & Gender
    renderStarRating(cust.star_rating || 5);
    document.getElementById('cd-inline-gender').value = cust.gender || 'MALE';
    renderOccupationOptions(cust.occupation || '');
    document.getElementById('cd-inline-dob').value = cust.birth_date || '';
    document.getElementById('cd-full-profile-link').href = cust.profile_url;

    // Update Blacklist Status UI
    updateBlacklistUI(cust);

    // Sync back left name if blank
    if (!document.getElementById('fe_name').value) {
        document.getElementById('fe_name').value = cust.name;
    }

    if (cust.is_blacklisted) {
        showAlert('⛔ BLACKLISTED CUSTOMER', `Caution: Customer is marked as BLACKLISTED! Reason: ${cust.blacklist_reason || 'Flagged by desk operator'}`);
    }
}

function updateBlacklistUI(cust) {
    const isBl = !!(cust && cust.is_blacklisted);
    const badge = document.getElementById('cd-blacklist-badge');
    const banner = document.getElementById('cd-blacklist-banner');
    const reasonText = document.getElementById('cd-blacklist-reason-text');
    const btnLabel = document.getElementById('btn-blacklist-label');
    const btn = document.getElementById('btn-toggle-blacklist');
    const statusBadge = document.getElementById('cd-status-badge');

    if (badge) badge.style.display = isBl ? 'inline-flex' : 'none';
    if (banner) {
        banner.style.display = isBl ? 'flex' : 'none';
        if (reasonText) reasonText.textContent = cust && cust.blacklist_reason ? `Reason: ${cust.blacklist_reason}` : 'No specific reason provided';
    }
    if (btnLabel) btnLabel.textContent = isBl ? 'Remove from Blacklist' : 'Blacklist Customer';
    if (btn) {
        btn.style.background = isBl ? '#dcfce7' : '#fee2e2';
        btn.style.color = isBl ? '#166534' : '#dc2626';
        btn.style.borderColor = isBl ? '#86efac' : '#fca5a5';
    }
    if (statusBadge && cust) {
        if (isBl) {
            statusBadge.className = 'pocket-badge-danger';
            statusBadge.innerText = '⛔ BLACKLISTED';
        } else {
            statusBadge.className = 'pocket-badge-active';
            statusBadge.innerText = 'Existing Active Customer';
        }
    }
}

function promptCustomerBlacklist() {
    if (!currentLoadedCustomer) {
        alert('Please select or search a customer first.');
        return;
    }
    const isCurBlacklisted = !!currentLoadedCustomer.is_blacklisted;

    if (isCurBlacklisted) {
        if (confirm(`Remove ${currentLoadedCustomer.name} (${currentLoadedCustomer.customer_code}) from Blacklist?`)) {
            toggleCustomerBlacklist(false);
        }
    } else {
        const reason = prompt(`Enter reason for Blacklisting ${currentLoadedCustomer.name}:`, 'Payment pending / non-cooperative citizen');
        if (reason !== null && reason.trim() !== '') {
            toggleCustomerBlacklist(true, reason.trim());
        }
    }
}

function toggleCustomerBlacklist(setBlacklist, reason = '') {
    if (!currentLoadedCustomer) return;
    const custId = currentLoadedCustomer.id;

    fetch('{{ route("fast_entry.toggle_blacklist") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            customer_id: custId,
            is_blacklisted: setBlacklist,
            blacklist_reason: reason
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            currentLoadedCustomer.is_blacklisted = res.is_blacklisted;
            currentLoadedCustomer.blacklist_reason = res.blacklist_reason || '';
            updateBlacklistUI(currentLoadedCustomer);
            showAlert(res.is_blacklisted ? 'Customer Blacklisted' : 'Blacklist Removed', res.message);
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(() => alert('Network error updating blacklist status.'));
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
                if (ind) {
                    ind.style.display = 'inline';
                    setTimeout(() => ind.style.display = 'none', 1500);
                }
                document.getElementById('fe_name').value = fieldValue;
            } else if (fieldName === 'alternate_mobile') {
                const ind = document.getElementById('ind-alt-mobile');
                if (ind) {
                    ind.style.display = 'inline';
                    setTimeout(() => ind.style.display = 'none', 1500);
                }
            } else if (fieldName === 'occupation') {
                const ind = document.getElementById('ind-occ');
                if (ind) {
                    ind.style.display = 'inline';
                    setTimeout(() => ind.style.display = 'none', 1500);
                }
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
// 4b. OCCUPATION DROPDOWN & CUSTOM PERSISTENCE
// ==========================================
const DEFAULT_OCCUPATIONS = [
    'FARMER',
    'BUSINESS / SHOP',
    'PRIVATE SERVICE',
    'GOVERNMENT SERVICE',
    'STUDENT',
    'HOUSEWIFE',
    'SELF EMPLOYED',
    'DAILY WAGER / LABOUR',
    'DRIVER',
    'RETIRED',
    'TEACHER',
    'ADVOCATE',
    'DOCTOR',
    'OTHER'
];
const SERVER_OCCUPATIONS = @json($existingOccupations ?? []);

function getSavedOccupations() {
    let customList = [];
    try {
        const stored = localStorage.getItem('maha_eseva_custom_occupations');
        if (stored) customList = JSON.parse(stored);
    } catch(e) {}
    if (!Array.isArray(customList)) customList = [];

    return Array.from(new Set([
        ...DEFAULT_OCCUPATIONS,
        ...(Array.isArray(SERVER_OCCUPATIONS) ? SERVER_OCCUPATIONS : []),
        ...customList
    ])).map(s => (s || '').trim().toUpperCase()).filter(Boolean);
}

function recordCustomOccupation(val) {
    if (!val) return;
    const clean = val.trim().toUpperCase();
    if (!clean || clean === '__NEW_OCCUPATION__') return;

    let customList = [];
    try {
        const stored = localStorage.getItem('maha_eseva_custom_occupations');
        if (stored) customList = JSON.parse(stored);
    } catch(e) {}
    if (!Array.isArray(customList)) customList = [];

    if (!customList.includes(clean)) {
        customList.push(clean);
        try {
            localStorage.setItem('maha_eseva_custom_occupations', JSON.stringify(customList));
        } catch(e) {}
    }
}

function renderOccupationOptions(selectedVal) {
    const occSelect = document.getElementById('cd-inline-occ');
    if (!occSelect) return;

    const cleanSel = (selectedVal || '').trim().toUpperCase();
    const list = getSavedOccupations();

    if (cleanSel && !list.includes(cleanSel)) {
        list.push(cleanSel);
    }

    let html = '<option value="">-- Select Occupation --</option>';
    list.forEach(occ => {
        const isSel = (occ === cleanSel) ? 'selected' : '';
        html += `<option value="${escapeHtml(occ)}" ${isSel}>${escapeHtml(occ)}</option>`;
    });
    html += '<option value="__NEW_OCCUPATION__" style="color: #0284c7; font-weight: 700;">➕ Add New Occupation...</option>';

    occSelect.innerHTML = html;
    occSelect.dataset.prev = cleanSel;
}

function onOccupationSelectChange(selectEl) {
    if (!selectEl) return;
    if (selectEl.value === '__NEW_OCCUPATION__') {
        const custom = prompt('Enter new occupation (e.g. ADVOCATE, POLICE, ENGINEER):');
        if (custom && custom.trim()) {
            const clean = custom.trim().toUpperCase();
            recordCustomOccupation(clean);
            renderOccupationOptions(clean);
            selectEl.value = clean;
            selectEl.dataset.prev = clean;
            autoSaveCustomerField('occupation', clean);
        } else {
            selectEl.value = selectEl.dataset.prev || '';
            return;
        }
    } else {
        const val = selectEl.value;
        selectEl.dataset.prev = val;
        autoSaveCustomerField('occupation', val);
    }
}

// Initial populate of occupation options
renderOccupationOptions('');

// ==========================================
// 5. UNIFIED EXCEL TABLE: RENDER COMMITTED ROWS
// ==========================================
function getEmployeeName(empId, fallback = '') {
    if (!empId) return (fallback || '').toUpperCase();
    const emp = (window.allEmployees || []).find(e => e.id == empId);
    if (!emp) return (fallback || '').toUpperCase();
    const name = (emp.user && emp.user.name) ? emp.user.name : (emp.name || fallback || '');
    return name.toUpperCase();
}

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
        if (window.fastEntryFilter) {
            window.fastEntryFilter.refresh();
        }
        return;
    }

    apps.forEach(app => {
        loadedApplications[app.id] = app;
        const tr = createCommittedRowElement(app);
        tbody.appendChild(tr);
    });

    if (window.fastEntryFilter) {
        window.fastEntryFilter.refresh();
    }
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

function onCommittedStatusSelectChange(selectEl, appId, serviceName) {
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
            recordCustomStatusForService(serviceName, clean);
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
            recordCustomStatus(clean);
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
// REQUIRED DOCUMENTS POPUP MODAL
// ==========================================
let currentModalDocs = [];
let currentSelectedModalService = null;

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
    // If no service chosen yet, default to first available service so modal always opens!
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
    showAlert('Service Selected', `${srv.main_service_name} ➔ ${srv.sub_service_name} selected!`);
}

function openDocsModalWithService(srv) {
    currentSelectedModalService = srv;

    const modalTitle = document.getElementById('modal-service-title');
    const modalSubTitle = document.getElementById('modal-sub-service-title');
    const docsContainer = document.getElementById('modal-docs-container');

    const mainName = srv ? (srv.main_service_name || 'Service') : 'Service';
    const subName = srv ? (srv.sub_service_name || '') : '';

    if (modalTitle) modalTitle.innerText = subName ? `${mainName} - ${subName}` : mainName;
    if (modalSubTitle) modalSubTitle.innerText = 'Required Documents Checklist';

    // Parse Required Docs
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
    currentModalDocs.forEach((doc, idx) => {
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
        if (typeof showAlert === 'function') {
            showAlert('Copied to Clipboard', 'Checklist copied! You can now paste and share on WhatsApp.');
        }
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

function applyStatusBadgeStyle(el, statusVal) {
    if (!el) return;
    const val = (statusVal || '').trim().toUpperCase();
    if (val.includes('READY') || val.includes('DONE') || val.includes('APPROVED') || val.includes('DELIVERED') || val.includes('COMPLETED')) {
        el.style.background = '#dcfce7';
        el.style.color = '#166534';
        el.style.border = '1px solid #86efac';
    } else if (val.includes('PEND') || val.includes('REJECT') || val.includes('CANCEL')) {
        el.style.background = '#fee2e2';
        el.style.color = '#dc2626';
        el.style.border = '1px solid #fca5a5';
    } else if (val.includes('PROG') || val.includes('HOLD') || val.includes('WAIT') || val.includes('SUBMIT')) {
        el.style.background = '#fef3c7';
        el.style.color = '#b45309';
        el.style.border = '1px solid #fde68a';
    } else {
        el.style.background = '#f3e8ff';
        el.style.color = '#6b21a8';
        el.style.border = '1px solid #d8b4fe';
    }
}

function toggleEditCommittedRow(appId) {
    const tr = document.getElementById('committed-row-' + appId);
    if (!tr) return;

    const isLocked = tr.classList.contains('locked-row');
    if (isLocked) {
        tr.classList.remove('locked-row');
        tr.classList.add('unlocked-row');
        const btn = document.getElementById('btn-edit-row-' + appId);
        if (btn) {
            btn.className = 'btn-row-action btn-row-done';
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Done';
            btn.title = 'Save and Lock this service row';
        }
        const rateInput = document.getElementById(`crow_rate_${appId}`);
        if (rateInput) {
            rateInput.focus();
            rateInput.select();
        }
    } else {
        saveAndLockCommittedRow(appId);
    }
}

function saveAndLockCommittedRow(appId) {
    autoSaveCommittedRow(appId);
    const tr = document.getElementById('committed-row-' + appId);
    if (!tr) return;

    tr.classList.remove('unlocked-row');
    tr.classList.add('locked-row');
    const btn = document.getElementById('btn-edit-row-' + appId);
    if (btn) {
        btn.className = 'btn-row-action btn-row-edit';
        btn.innerHTML = '<i class="fa-solid fa-pencil"></i> Edit';
        btn.title = 'Click to edit service details';
    }
    updateCommittedRowViewTexts(appId);
}

function updateCommittedRowViewTexts(appId) {
    const app = loadedApplications[appId];
    if (!app) return;

    const subEl = document.getElementById(`crow_view_sub_${appId}`);
    if (subEl) subEl.textContent = app.sub_service || '-';

    const statusEl = document.getElementById(`crow_view_status_${appId}`);
    if (statusEl) {
        statusEl.textContent = app.status || 'PENDING';
        applyStatusBadgeStyle(statusEl, app.status);
    }

    const rateNum = typeof app.rate === 'number' ? app.rate : (parseFloat(app.rate) || 0);
    const rateEl = document.getElementById(`crow_view_rate_${appId}`);
    if (rateEl) rateEl.textContent = '₹ ' + rateNum.toFixed(2);

    const recNum = typeof app.received === 'number' ? app.received : (parseFloat(app.received) || 0);
    const recEl = document.getElementById(`crow_view_rec_${appId}`);
    if (recEl) recEl.textContent = '₹ ' + recNum.toFixed(2);

    const wdEl = document.getElementById(`crow_view_wd_${appId}`);
    if (wdEl) {
        wdEl.textContent = app.work_details || '-';
        wdEl.title = app.work_details || '';
    }

    const prEl = document.getElementById(`crow_view_pr_${appId}`);
    if (prEl) {
        prEl.textContent = app.payment_remarks || '-';
        prEl.title = app.payment_remarks || '';
    }

    const opEl = document.getElementById(`crow_view_op_${appId}`);
    if (opEl) opEl.textContent = getEmployeeName(app.assigned_employee_id, app.operator) || 'Unassigned';

    const fuEl = document.getElementById(`crow_view_fu_${appId}`);
    if (fuEl) fuEl.textContent = app.follow_up_date || '-';
}

function createCommittedRowElement(app) {
    const tr = document.createElement('tr');
    tr.className = 'committed-row locked-row';
    tr.id = 'committed-row-' + app.id;
    tr.dataset.appId = app.id;
    tr.dataset.main_service = (app.service_name || app.main_service_name || '').trim();
    tr.dataset.sub_service = (app.sub_service || '').trim();
    tr.dataset.status = (app.status || '').trim().toUpperCase();
    tr.dataset.operator = getEmployeeName(app.assigned_employee_id, app.operator);
    tr.dataset.followup_date = (app.follow_up_date || '').trim();
    tr.innerHTML = getCommittedRowInnerHtml(app);

    // Apply colored status combobox styling & auto-expand work details
    setTimeout(() => {
        const input = tr.querySelector(`#crow_status_${app.id}`);
        if (input) applyStatusInputStyle(input);

        const vBadge = tr.querySelector(`#crow_view_status_${app.id}`);
        if (vBadge) applyStatusBadgeStyle(vBadge, app.status);

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
        <td class="font-mono" style="white-space: nowrap; user-select: text; cursor: default; padding: 6px 8px;">
            <div style="font-weight: 800; color: #0f172a; font-size: 12.5px; line-height: 1.2;">
                ${escapeHtml(app.application_number)}
            </div>
            <div style="font-size: 11px; color: #64748b; font-weight: 600; margin-top: 2px;">
                ${escapeHtml(app.formatted_date_time || (app.application_date ? app.application_date : 'Today'))}
            </div>
        </td>
        <td style="font-weight: 700; color: #0f172a;">
            ${escapeHtml(app.service_name)}
        </td>
        <td style="padding: 4px 6px;">
            <div class="crow-view-mode">
                <span class="crow-view-text font-bold" id="crow_view_sub_${app.id}">${escapeHtml(app.sub_service || '-')}</span>
                <button type="button" class="btn-doc-eye-small" onclick="openDocsModalForApp(${app.id})" title="View Required Documents">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'sub_service', event)" title="Sub Service Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
            <div class="crow-edit-mode">
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
            <span class="badge" style="background: #f1f5f9; color: #0369a1; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11px; padding: 4px 7px; border-radius: 5px;">
                ⏱️ ${app.expected_days ? app.expected_days + ' Days' : '-'}
            </span>
        </td>
        <td style="padding: 4px 6px;">
            <div class="crow-view-mode">
                <span id="crow_view_rate_${app.id}" class="crow-view-rate">₹ ${rateNum.toFixed(2)}</span>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'rate', event)" title="Rate Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
            <div class="crow-edit-mode">
                <div style="position: relative; display: flex; align-items: center; flex: 1;">
                    <span style="position: absolute; left: 8px; font-weight: 700; color: #64748b; pointer-events: none; font-size: 12px;">₹</span>
                    <input type="number" step="0.01" id="crow_rate_${app.id}" class="excel-cell-input font-mono font-bold"
                           value="${rateNum}"
                           style="padding-left: 20px; font-size: 13.5px;"
                           title="Rate"
                           oninput="calcCommittedRemaining(${app.id})"
                           onchange="autoSaveCommittedRow(${app.id})"
                           onkeydown="handleCommittedKey(event, ${app.id})">
                </div>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'rate', event)" title="Rate Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
        </td>
        <td style="padding: 4px 6px;">
            <div class="crow-view-mode">
                <span id="crow_view_rec_${app.id}" class="crow-view-rate" style="color: #166534;">₹ ${recNum.toFixed(2)}</span>
            </div>
            <div class="crow-edit-mode" style="position: relative; display: flex; align-items: center;">
                <span style="position: absolute; left: 8px; font-weight: 700; color: #166534; pointer-events: none; font-size: 12px;">₹</span>
                <input type="number" step="0.01" id="crow_received_${app.id}" class="excel-cell-input font-mono font-bold"
                       value="${recNum}"
                       style="padding-left: 20px; font-size: 13.5px; color: #166534;"
                       title="Received amount"
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
            <div class="crow-view-mode">
                <div id="crow_view_wd_${app.id}" class="crow-view-notes" title="${escapeHtml(app.work_details || '')}">${escapeHtml(app.work_details || '-')}</div>
            </div>
            <div class="crow-edit-mode">
                <textarea id="crow_work_details_${app.id}" class="excel-work-details"
                          placeholder="Work details / citizen notes..."
                          rows="1"
                          title="${escapeHtml(app.work_details || '')}"
                          oninput="autoExpandWorkDetails(this)"
                          onchange="autoSaveCommittedRow(${app.id})"
                          onkeydown="handleCommittedKey(event, ${app.id})">${escapeHtml(app.work_details || '')}</textarea>
            </div>
        </td>
        <td style="padding: 4px 6px;">
            <div class="crow-view-mode">
                <div id="crow_view_pr_${app.id}" class="crow-view-notes" title="${escapeHtml(app.payment_remarks || '')}">${escapeHtml(app.payment_remarks || '-')}</div>
            </div>
            <div class="crow-edit-mode">
                <textarea id="crow_payment_remarks_${app.id}" class="excel-work-details"
                          placeholder="Pending remark / notes..."
                          rows="1"
                          title="${escapeHtml(app.payment_remarks || '')}"
                          oninput="autoExpandWorkDetails(this)"
                          onchange="autoSaveCommittedRow(${app.id})"
                          onkeydown="handleCommittedKey(event, ${app.id})">${escapeHtml(app.payment_remarks || '')}</textarea>
            </div>
        </td>
        <td style="padding: 4px 6px;">
            <div class="crow-view-mode">
                <span id="crow_view_op_${app.id}" class="crow-view-text font-bold" style="font-size: 12px; color: #475569;">${escapeHtml(getEmployeeName(app.assigned_employee_id, app.operator) || 'Unassigned')}</span>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'assigned_employee_id', event)" title="Operator Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
            <div class="crow-edit-mode">
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
            <div class="crow-view-mode">
                <span id="crow_view_fu_${app.id}" class="crow-view-text font-mono" style="font-size: 12px;">${escapeHtml(app.follow_up_date || '-')}</span>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'follow_up_date', event)" title="Follow-Up Date Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
            <div class="crow-edit-mode">
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
        <td style="text-align: center; white-space: nowrap; padding: 4px 8px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 5px; flex-wrap: nowrap;">
                <button type="button" id="btn-edit-row-${app.id}" class="btn-row-action btn-row-edit" onclick="toggleEditCommittedRow(${app.id})" title="Click to edit service details">
                    <i class="fa-solid fa-pencil"></i> Edit
                </button>
                <a href="${getAppPortalLink(app)}" target="_blank" class="btn-row-action btn-row-apply" title="Apply Now on Official Portal">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Apply
                </a>
                <a href="${getAppTutorialLink(app)}" target="_blank" class="btn-row-action btn-row-tutorial" title="Watch Tutorial Video">
                    <i class="fa-brands fa-youtube"></i> Tutorial
                </a>
                <a href="${app.receipt_url || `/applications/${app.id}/receipt`}" target="_blank" class="btn-row-action btn-row-receipt"
                   style="background: #16a34a; color: #ffffff; border: 1px solid #15803d; font-weight: 700; text-decoration: none; padding: 4px 9px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;"
                   title="Print Official Citizen Receipt (पावती)">
                    <i class="fa-solid fa-receipt"></i> Receipt
                </a>
                <span id="saved-indicator-${app.id}" class="saved-pulse-badge" style="display: none;">
                    <i class="fa-solid fa-check"></i>
                </span>
            </div>
        </td>
        <!-- 10. STATUS (Fixed Sticky Column on Right Side) -->
        <td class="sticky-col-status" style="padding: 4px 6px;">
            <div class="crow-view-mode">
                <span id="crow_view_status_${app.id}" class="crow-view-badge">${escapeHtml(currentStatus || 'PENDING')}</span>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'work_status', event)" title="Status Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
            </div>
            <div class="crow-edit-mode">
                <select id="crow_status_${app.id}" class="excel-status-input font-bold status-select" style="flex: 1;"
                        data-prev="${escapeHtml(currentStatus)}"
                        onchange="onCommittedStatusSelectChange(this, ${app.id}, '${escapeHtml(serviceName)}')">
                    ${renderStatusOptionsHtml(currentStatus, serviceName)}
                </select>
                <button type="button" class="btn-cell-history" onclick="openFieldHistory(${app.id}, 'work_status', event)" title="Status Change History">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
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
                    rate: d.rate.replace(/,/g, ''),
                    received: d.received.replace(/,/g, ''),
                    remaining: d.remaining.replace(/,/g, ''),
                    status: d.status,
                    operator: d.operator,
                    sub_service: d.sub_service,
                    work_details: d.work_details,
                    payment_remarks: d.payment_remarks,
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
    document.getElementById('se_payment_remarks').value = '';
    document.getElementById('se_payment_remarks').style.height = '34px';
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
        alert('Please enter customer name & mobile number on the left and click Go first!');
        document.getElementById('fe_mobile').focus();
        return;
    }

    const serviceId = document.getElementById('se_service_id').value;
    if (!serviceId) {
        alert('Please choose Main Service and Sub Service!');
        document.getElementById('se_main_service').focus();
        return;
    }

    const rate = document.getElementById('se_rate').value;
    if (!rate || parseFloat(rate) < 0) {
        alert('Please enter a valid rate!');
        document.getElementById('se_rate').focus();
        return;
    }

    const saveBtns = document.querySelectorAll('.btn-save-excel-row');
    saveBtns.forEach(b => {
        b.disabled = true;
        b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    });

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_id: custId,
        service_id: serviceId,
        sub_service: document.getElementById('se_sub_service').value.trim() || null,
        rate: rate,
        received: document.getElementById('se_received').value || 0,
        work_details: document.getElementById('se_work_details').value.trim() || null,
        payment_remarks: document.getElementById('se_payment_remarks').value.trim() || null,
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
        saveBtns.forEach(b => {
            b.disabled = false;
            b.innerHTML = '<i class="fa-solid fa-check"></i> Save (Enter)';
        });

        if (res.success) {
            const d = res.data;
            const tbody = document.getElementById('services-tbody');
            const activeRow = document.getElementById('active-service-row');

            // Record custom status to datalist & local storage
            recordCustomStatus(payload.work_status);

            // 1. If backend returned the full applications array, render all committed services!
            if (res.applications && Array.isArray(res.applications)) {
                renderCommittedServices(res.applications);
                const newRow = document.getElementById('committed-row-' + d.id);
                if (newRow) {
                    newRow.classList.add('newly-saved-row-pulse');
                    setTimeout(() => newRow.classList.remove('newly-saved-row-pulse'), 2500);
                }
            } else {
                // Fallback: create single committed row and place right after activeRow
                const newApp = {
                    id: d.id,
                    application_number: d.application_number,
                    service_id: serviceId,
                    service_name: d.service_name,
                    sub_service: d.sub_service,
                    application_date: d.application_date || 'Today',
                    rate: typeof d.rate === 'string' ? d.rate.replace(/,/g, '') : d.rate,
                    received: typeof d.received === 'string' ? d.received.replace(/,/g, '') : d.received,
                    remaining: typeof d.remaining === 'string' ? d.remaining.replace(/,/g, '') : d.remaining,
                    status: payload.work_status || 'READY',
                    expected_days: d.expected_days || 7,
                    main_service_name: d.main_service_name || d.service_name,
                    work_details: d.work_details || payload.work_details || '',
                    payment_remarks: d.payment_remarks || payload.payment_remarks || '',
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
                committedRow.classList.add('newly-saved-row-pulse');
                setTimeout(() => committedRow.classList.remove('newly-saved-row-pulse'), 2500);
            }

            if (window.fastEntryFilter) {
                window.fastEntryFilter.refresh();
            }

            // 2. Update Customer Summary Badges in Real-time
            if (currentLoadedCustomer) {
                currentLoadedCustomer.total_services = d.total_services !== undefined ? d.total_services : (currentLoadedCustomer.total_services || 0) + 1;
                document.getElementById('cd-services-count').innerText = `${currentLoadedCustomer.total_services} Services`;
                document.getElementById('services-badge').innerText = `${tbody.querySelectorAll('tr.committed-row').length} Services`;

                if (d.customer_total_outstanding !== undefined) {
                    currentLoadedCustomer.total_outstanding = d.customer_total_outstanding;
                } else {
                    const remNum = parseFloat(String(d.remaining).replace(/[^0-9.]/g, '')) || 0;
                    currentLoadedCustomer.total_outstanding = (currentLoadedCustomer.total_outstanding || 0) + remNum;
                }
                const outEl = document.getElementById('cd-outstanding');
                if (outEl) {
                    outEl.innerText = `₹ ${parseFloat(currentLoadedCustomer.total_outstanding).toFixed(2)}`;
                    outEl.style.color = currentLoadedCustomer.total_outstanding > 0 ? '#dc2626' : '#166534';
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
                stInput.value = '';
                applyStatusInputStyle(stInput);
            }

            // Auto-focus back on Main Service for next entry
            document.getElementById('se_main_service').focus();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(() => {
        saveBtns.forEach(b => {
            b.disabled = false;
            b.innerHTML = '<i class="fa-solid fa-check"></i> Save (Enter)';
        });
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
    updateBlacklistUI(null);

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
        seStatus.value = '';
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

// Click anywhere on cell to focus its input/select (only when row is unlocked/editing)
document.addEventListener('click', function(e) {
    const row = e.target.closest('tr.committed-row.unlocked-row');
    if (!row) return;
    const td = e.target.closest('td');
    if (td && e.target === td) {
        const input = td.querySelector('.excel-cell-input, .excel-status-input, .excel-operator-select, .excel-work-details');
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
        if (overlay) {
            overlay.classList.remove('open');
            overlay.style.setProperty('display', 'none', 'important');
        }
    }
});

// Initialize Status Datalist & Excel Table Column Filter on boot
document.addEventListener('DOMContentLoaded', function() {
    initCustomStatuses();

    if (window.ExcelTableFilter) {
        window.fastEntryFilter = new ExcelTableFilter({
            table: '#fast-entry-table',
            rowSelector: 'tbody tr.committed-row',
            ignoreRowSelector: '#active-service-row',
            clearBtnContainer: '#fast-entry-filter-toolbar',
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

// ========================================================
// Dropdown Services Catalog & Particular Viewer with Eye Icon
// ========================================================
let currentCatalogSelectedService = null;

function onCatalogMainSelectChange(mainName) {
    const subSel = document.getElementById('catalog_sub_service_select');
    if (!subSel) return;

    subSel.innerHTML = '<option value="">-- Choose Sub Service --</option>';
    currentCatalogSelectedService = null;

    if (!mainName) {
        subSel.disabled = true;
        renderCatalogParticularView(null);
        return;
    }

    const matched = allServices.filter(s => s.main_service_name === mainName);
    matched.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.sub_service_name;
        subSel.appendChild(opt);
    });

    subSel.disabled = false;

    // If there is only 1 sub-service under this main service, auto-select it!
    if (matched.length === 1) {
        subSel.value = matched[0].id;
        onCatalogSubSelectChange(matched[0].id);
    } else {
        renderCatalogParticularView(null);
    }
}

function onCatalogSubSelectChange(serviceId) {
    if (!serviceId) {
        currentCatalogSelectedService = null;
        renderCatalogParticularView(null);
        return;
    }
    const srv = allServices.find(s => s.id == serviceId);
    currentCatalogSelectedService = srv || null;
    renderCatalogParticularView(srv);
}

function renderCatalogParticularView(srv) {
    const placeholder = document.getElementById('catalog-empty-placeholder');
    const selectedView = document.getElementById('catalog-selected-view');
    if (!placeholder || !selectedView) return;

    if (!srv) {
        placeholder.style.display = 'flex';
        selectedView.style.display = 'none';
        return;
    }

    placeholder.style.display = 'none';
    selectedView.style.display = 'flex';

    const mainEl = document.getElementById('cat-view-main');
    const subEl = document.getElementById('cat-view-sub');
    const priceEl = document.getElementById('cat-view-price');
    const daysEl = document.getElementById('cat-view-days');

    if (mainEl) mainEl.textContent = (srv.main_service_name || 'SERVICE').toUpperCase();
    if (subEl) subEl.textContent = srv.sub_service_name || '';

    const price = parseFloat(srv.price > 0 ? srv.price : ((parseFloat(srv.govt_fee) || 0) + (parseFloat(srv.service_charge) || 0))) || 0;
    if (priceEl) priceEl.textContent = '₹ ' + price.toFixed(2);

    const days = srv.expected_processing_days || 7;
    if (daysEl) daysEl.textContent = days + ' Days';
}

function openCatalogServiceDocs() {
    if (!currentCatalogSelectedService) {
        alert('Please select a service first.');
        return;
    }
    openDocsModalWithService(currentCatalogSelectedService);
}

function applyCatalogServiceToDesk() {
    if (!currentCatalogSelectedService) return;
    const srv = currentCatalogSelectedService;
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
    showAlert('Service Selected', `${srv.main_service_name} ➔ ${srv.sub_service_name} active in entry row!`);
}

function resetCatalogSelection() {
    const mainSel = document.getElementById('catalog_main_service_select');
    const subSel = document.getElementById('catalog_sub_service_select');
    if (mainSel) mainSel.value = '';
    if (subSel) {
        subSel.innerHTML = '<option value="">-- Choose Sub Service --</option>';
        subSel.disabled = true;
    }
    currentCatalogSelectedService = null;
    renderCatalogParticularView(null);
}

initCustomStatuses();
</script>
@endpush
@endsection
