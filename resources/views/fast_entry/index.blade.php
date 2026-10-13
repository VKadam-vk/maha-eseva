@extends('layouts.app')

@section('title', 'Fast Daily Entry & Service Work')

@section('content')
<style>
    .fast-entry-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .fe-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 15px -2px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .fe-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .fe-card-body {
        padding: 26px;
    }

    .fe-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    /* Grids */
    .fe-grid-row-1 {
        display: grid;
        grid-template-columns: 2.2fr 1.2fr 1.8fr 2fr;
        gap: 20px;
        margin-bottom: 22px;
    }

    .fe-grid-row-2 {
        display: grid;
        grid-template-columns: 1.5fr 1.5fr 1.6fr 2fr 1.4fr;
        gap: 20px;
        align-items: flex-end;
    }

    /* Excel-style Horizontal Table Styling */
    .excel-row-wrapper {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        margin-top: 8px;
    }

    .excel-table {
        width: 100%;
        min-width: 1560px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .excel-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    .excel-table th {
        padding: 9px 10px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 2px solid #334155;
        border-right: 1px solid #334155;
        white-space: nowrap;
    }

    .excel-table th:last-child {
        border-right: none;
    }

    .excel-table tbody tr {
        background: #f8fafc;
        transition: background 0.15s ease;
    }

    .excel-table tbody tr:hover {
        background: #f1f5f9;
    }

    .excel-table td {
        padding: 8px 8px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }

    .excel-table td:last-child {
        border-right: none;
    }

    .excel-table .form-control,
    .excel-table .form-select {
        height: 38px;
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 10px;
        background: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .excel-table .form-control:focus,
    .excel-table .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        outline: none;
    }

    /* Star Rating Box */
    .star-rating-box {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        border-radius: 10px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
        height: 42px;
    }

    .star-rating-box:focus-within,
    .star-rating-box:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        background: #ffffff;
    }

    .star-btn-group {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .star-btn {
        font-size: 18px;
        color: #f59e0b;
        cursor: pointer;
        transition: transform 0.15s ease, color 0.15s ease;
    }

    .star-btn:hover {
        transform: scale(1.2);
    }

    .rating-pill {
        background: #ffffff;
        border: 1px solid #f59e0b;
        color: #b45309;
        font-weight: 800;
        font-size: 12.5px;
        padding: 2px 8px;
        border-radius: 6px;
        font-family: var(--font-mono);
    }

    /* Customer Live Search Results Dropdown */
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        z-index: 100;
        max-height: 280px;
        overflow-y: auto;
        display: none;
        margin-top: 4px;
    }

    .search-item {
        padding: 10px 16px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.15s ease;
    }

    .search-item:hover, .search-item.active {
        background: #fff7ed;
    }

    .search-item:last-child {
        border-bottom: none;
    }

    /* Selected Customer Chip */
    .selected-customer-chip {
        display: none;
        background: #ecfdf5;
        border: 1.5px solid #86efac;
        padding: 10px 16px;
        border-radius: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    /* Alert Banner */
    .save-alert-banner {
        display: none;
        background: #ecfdf5;
        border: 1.5px solid #86efac;
        color: #14532d;
        border-radius: 10px;
        padding: 14px 20px;
        margin-bottom: 20px;
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
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: #f0fdf4; color: #16a34a;">
                    <i class="fa-solid fa-bolt" style="font-size: 18px;"></i>
                </span>
                High-Speed Counter & Service Entry System
            </h1>
            <p class="page-subtitle" style="margin-top: 4px;">
                Register customer profiles & record service entries seamlessly connected to <strong>Customers CRM</strong>
            </p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary" style="font-weight: 600; padding: 8px 16px;">
                <i class="fa-solid fa-users" style="color: #ea580c;"></i> Open Customers CRM <i class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
            </a>
            <span class="badge" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12.5px; padding: 7px 14px; font-weight: 600;">
                <i class="fa-solid fa-keyboard" style="color: #16a34a; margin-right: 4px;"></i> <strong>Ctrl + Enter</strong> to Save
            </span>
        </div>
    </div>

    <!-- Alert Notification Banner -->
    <div id="save-alert" class="save-alert-banner">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="display: inline-flex; width: 32px; height: 32px; border-radius: 50%; background: #22c55e; color: white; align-items: center; justify-content: center;">
                <i class="fa-solid fa-check"></i>
            </span>
            <div>
                <strong id="alert-title" style="font-size: 14px;">Entry Recorded Successfully!</strong>
                <div id="alert-desc" style="font-size: 12.5px; color: #166534;">Saved directly to Customers CRM.</div>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a id="alert-profile-btn" href="#" class="btn btn-sm btn-secondary" style="background: white; border-color: #86efac; color: #15803d; font-weight: 700;">
                <i class="fa-solid fa-eye"></i> View Customer Profile
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary" style="background: #16a34a; border-color: #15803d; font-weight: 700;">
                <i class="fa-solid fa-users"></i> View in Customers CRM
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. FAST CUSTOMER PROFILE REGISTRATION CARD                                -->
    <!-- ========================================================================= -->
    <div class="fe-card" style="border: 2px solid #22c55e;">
        <div class="fe-card-header" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-user-plus" style="font-size: 18px;"></i>
                <h3 style="color: white; margin: 0; font-size: 15px; font-weight: 700; letter-spacing: 0.02em;">
                    FAST CUSTOMER PROFILE ENTRY GRID
                </h3>
            </div>
            <span style="font-size: 12px; color: #dcfce7; background: rgba(255, 255, 255, 0.18); padding: 5px 12px; border-radius: 20px; font-weight: 500;">
                <i class="fa-solid fa-shield-halved"></i> Auto-detects & saves to Customers CRM
            </span>
        </div>

        <div class="fe-card-body">
            <form id="fast-customer-form" onsubmit="saveFastCustomerRow(event)">
                @csrf

                <!-- Section 1: Compulsory / Primary Fields -->
                <div class="fe-section-title" style="color: #15803d;">
                    <i class="fa-solid fa-circle-check"></i> Mandatory & Priority Details
                </div>

                <div class="fe-grid-row-1">
                    <!-- 1. Customer Name -->
                    <div>
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #1e293b;">
                            Customer Name
                        </label>
                        <input type="text" id="fe_name" name="customer_name" class="form-control" required 
                               placeholder="e.g. VINOD KADAM" autocomplete="off"
                               style="height: 42px; font-size: 13.5px; padding: 9px 14px; font-weight: 600; text-transform: uppercase;" autofocus>
                    </div>

                    <!-- 2. Gender -->
                    <div>
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #1e293b;">
                            Gender
                        </label>
                        <select id="fe_gender" name="gender" class="form-select" required 
                                style="height: 42px; font-size: 13.5px; padding: 9px 12px; font-weight: 600;">
                            <option value="MALE" selected>Male</option>
                            <option value="FEMALE">Female</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>

                    <!-- 3. Mobile Number -->
                    <div>
                        <label class="form-label required" style="font-size: 12px; font-weight: 700; color: #1e293b;">
                            Mobile Number
                        </label>
                        <input type="tel" id="fe_mobile" name="mobile" class="form-control" required 
                               placeholder="10 Digits (e.g. 8888888888)" maxlength="10" pattern="[0-9]{10}" autocomplete="off"
                               style="height: 42px; font-size: 14px; padding: 9px 14px; font-family: var(--font-mono); font-weight: 700; letter-spacing: 0.5px;">
                    </div>

                    <!-- 4. Star Rating (1 to 5 Stars) -->
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #b45309; display: flex; justify-content: space-between;">
                            <span>Customer Rating</span>
                            <span style="font-size: 11px; font-weight: 600; color: #92400e;">(Click star or key 1-5)</span>
                        </label>
                        
                        <div class="star-rating-box" id="star-rating-picker" tabindex="0" title="Click stars or press key 1 to 5">
                            <div class="star-btn-group" id="star-icons-container">
                                <i class="fa-solid fa-star star-btn" data-val="1"></i>
                                <i class="fa-solid fa-star star-btn" data-val="2"></i>
                                <i class="fa-solid fa-star star-btn" data-val="3"></i>
                                <i class="fa-solid fa-star star-btn" data-val="4"></i>
                                <i class="fa-solid fa-star star-btn" data-val="5"></i>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="rating-pill" id="star-badge">5 ★</span>
                                <input type="hidden" id="fe_star_rating" name="star_rating" value="5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Demographic & Contact Details -->
                <div class="fe-section-title" style="color: #475569;">
                    <i class="fa-solid fa-address-card"></i> Demographic & Contact Details (Optional)
                </div>

                <div class="fe-grid-row-2">
                    <!-- 5. Birth Date -->
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569;">
                            Birth Date
                        </label>
                        <input type="date" id="fe_birth_date" name="birth_date" class="form-control"
                               style="height: 42px; font-size: 13px; padding: 8px 12px;">
                    </div>

                    <!-- 6. Alternate Mobile -->
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569;">
                            Alternate Mobile
                        </label>
                        <input type="tel" id="fe_alt_mobile" name="alternate_mobile" class="form-control" 
                               placeholder="e.g. 9999999999" maxlength="10" autocomplete="off"
                               style="height: 42px; font-size: 13.5px; padding: 9px 12px; font-family: var(--font-mono);">
                    </div>

                    <!-- 7. Occupation -->
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569;">
                            Occupation
                        </label>
                        <input type="text" id="fe_occupation" name="occupation" class="form-control" 
                               placeholder="e.g. EMPLOYEE / FARMER" autocomplete="off"
                               style="height: 42px; font-size: 13.5px; padding: 9px 12px; text-transform: uppercase;">
                    </div>

                    <!-- 8. Email ID -->
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569;">
                            Email Address
                        </label>
                        <input type="email" id="fe_email" name="email" class="form-control" 
                               placeholder="customer@gmail.com" autocomplete="off"
                               style="height: 42px; font-size: 13.5px; padding: 9px 12px;">
                    </div>

                    <!-- 9. Submit Button -->
                    <div>
                        <button type="submit" id="fe_submit_btn" class="btn" 
                                style="width: 100%; height: 42px; background: #16a34a; color: white; border: none; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 8px; box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3); cursor: pointer; transition: all 0.2s;">
                            <i class="fa-solid fa-user-plus"></i> Save to CRM
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. "NEW ENTRY" ACTION BUTTON & WORK ALLOCATION SECTION                     -->
    <!-- ========================================================================= -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
        <button type="button" id="btn-toggle-new-entry" class="btn" onclick="scrollToNewEntry()"
                style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: white; border: none; padding: 13px 28px; font-size: 15px; font-weight: 700; border-radius: 10px; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3); cursor: pointer;">
            <i class="fa-solid fa-file-circle-plus" style="font-size: 18px;"></i> + NEW ENTRY (SERVICE & WORK ALLOCATION)
        </button>
        <span style="font-size: 13px; color: #64748b; font-weight: 500;">
            <i class="fa-solid fa-info-circle" style="color: #ea580c;"></i> Search customer & assign work (PAN, Aadhaar, Rent Agreement...)
        </span>
    </div>    @php
        $uniqueMainServices = $services->pluck('main_service_name')->filter()->unique()->sort()->values();
    @endphp

    <!-- NEW ENTRY CARD (Excel Format: Image 2) -->
    <div class="fe-card" id="new-entry-card" style="border: 2px solid #ea580c;">
        <div class="fe-card-header" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-table-cells" style="font-size: 18px;"></i>
                <h3 style="color: white; margin: 0; font-size: 15px; font-weight: 700; letter-spacing: 0.02em;">
                    NEW ENTRY TAB - EXCEL COUNTER WORK ROW (HORIZONTAL SPREADSHEET)
                </h3>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span style="font-size: 11.5px; color: #ffedd5; background: rgba(255, 255, 255, 0.2); padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                    <i class="fa-solid fa-keyboard"></i> Tab across cells | Ctrl+Enter to Save
                </span>
                <span style="font-size: 11.5px; color: #ffedd5; background: rgba(0, 0, 0, 0.2); padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                    <i class="fa-solid fa-link"></i> Syncs to Customer CRM
                </span>
            </div>
        </div>

        <div class="fe-card-body" style="padding: 18px 20px;">
            <!-- Step 1: Customer Search / Selection Bar -->
            <div style="background: #fff7ed; border: 1.5px dashed #fdba74; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <label style="font-size: 12px; font-weight: 800; color: #9a3412; margin: 0; display: flex; align-items: center; gap: 6px; text-transform: uppercase;">
                        <i class="fa-solid fa-magnifying-glass"></i> Customer Selection (Type Name or Mobile No.) *
                    </label>
                    <span style="font-size: 11px; color: #c2410c; font-weight: 600;">Search or select to link this counter entry</span>
                </div>
                
                <div style="position: relative;">
                    <input type="text" id="se_cust_search" class="form-control" autocomplete="off"
                           placeholder="Search customer name or mobile number (e.g. Vinod or 8888888888)..."
                           style="height: 40px; font-size: 13.5px; padding: 8px 14px; border: 1.5px solid #f97316; border-radius: 8px; background: white;"
                           oninput="onCustomerSearchInput(this.value)" onfocus="onCustomerSearchFocus()">

                    <div id="cust-search-spinner" style="position: absolute; right: 14px; top: 11px; display: none; color: #ea580c;">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </div>

                    <!-- Autocomplete Results Dropdown -->
                    <div id="cust-search-dropdown" class="search-results-dropdown"></div>
                </div>

                <!-- Selected Customer Chip Banner -->
                <div id="selected-customer-chip" class="selected-customer-chip" style="margin-top: 0;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="display: inline-flex; width: 32px; height: 32px; border-radius: 50%; background: #16a34a; color: white; align-items: center; justify-content: center; font-weight: 800; font-size: 13px;">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase;" id="sc-name">
                                -
                            </div>
                            <div style="font-size: 12px; color: #166534; font-family: var(--font-mono);">
                                <span id="sc-code" style="font-weight: 700;">-</span> | Mobile: <span id="sc-mobile" style="font-weight: 700;">-</span> | Rating: <span id="sc-rating" style="color: #b45309; font-weight: 700;">5★</span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelectedCustomer()" style="font-size: 11.5px; padding: 4px 10px;">
                        <i class="fa-solid fa-rotate-left"></i> Change Customer
                    </button>
                </div>
            </div>

            <!-- Operator Guidance Live Bar (Service, Expected Days, Required Docs, Apply Now, Watch Tutorial) -->
            <div id="service-operator-bar" style="display: none; background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 10px; padding: 12px 18px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <span style="font-weight: 800; color: #3730a3; font-size: 13.5px;" id="ob-title">
                            Service Details
                        </span>
                        <span class="badge" style="background: white; color: #4338ca; border: 1px solid #c7d2fe; font-size: 12px; font-weight: 700;" id="ob-days">
                            ⏱️ Expected: 7 Days
                        </span>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <a id="btn-work-link" href="#" target="_blank" class="btn btn-sm"
                           style="background: #2563eb; color: white; border: none; font-weight: 700; padding: 6px 14px; border-radius: 7px; display: none; font-size: 12px; box-shadow: 0 2px 4px rgba(37,99,235,0.25); text-decoration: none;"
                           title="Open official government portal to work">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Apply Now &rarr;
                        </a>

                        <a id="btn-tutorial-link" href="#" target="_blank" class="btn btn-sm"
                           style="background: #dc2626; color: white; border: none; font-weight: 700; padding: 6px 14px; border-radius: 7px; display: none; font-size: 12px; box-shadow: 0 2px 4px rgba(220,38,38,0.25); text-decoration: none;"
                           title="Watch step-by-step video training tutorial">
                            <i class="fa-brands fa-youtube"></i> Watch Tutorial &rarr;
                        </a>
                    </div>
                </div>

                <div id="ob-docs" style="font-size: 12px; color: #4338ca; margin-top: 8px; border-top: 1px dashed #c7d2fe; padding-top: 6px; display: none; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <strong>📄 Required Documents Checklist:</strong> <span id="ob-docs-text" style="color: #1e293b; font-weight: 600;"></span>
                    </div>
                    <button type="button" class="btn btn-sm" onclick="copyDocsToEntry()" style="background: white; border: 1px solid #c7d2fe; color: #3730a3; font-size: 11px; padding: 2px 8px; font-weight: 600;">
                        <i class="fa-solid fa-copy"></i> Re-copy to Row
                    </button>
                </div>
            </div>

            <!-- Step 2: Excel-Style Horizontal Row Table -->
            <form id="new-service-entry-form" onsubmit="saveServiceEntryRow(event)">
                @csrf
                <input type="hidden" id="se_customer_id" name="customer_id" value="">
                <input type="hidden" id="se_service_id" name="service_id" value="">
                <input type="hidden" id="se_sub_service" name="sub_service" value="">

                <div class="excel-row-wrapper">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th style="min-width: 175px;">1. Main Service *</th>
                                <th style="min-width: 220px;">2. Sub Service *</th>
                                <th style="min-width: 95px;">3. Rate (₹) *</th>
                                <th style="min-width: 100px; color: #86efac;">4. Received (₹)</th>
                                <th style="min-width: 105px;">5. Remaining (₹)</th>
                                <th style="min-width: 160px;">6. Work Details</th>
                                <th style="min-width: 180px;">7. Documents List</th>
                                <th style="min-width: 165px; color: #fca5a5;"><i class="fa-solid fa-triangle-exclamation"></i> 8. Pending Remark</th>
                                <th style="min-width: 130px;">9. Followup Date</th>
                                <th style="min-width: 120px;">10. Status</th>
                                <th style="min-width: 160px;">11. Operator *</th>
                                <th style="min-width: 230px; text-align: center;">12. Apply & Tutorial Links</th>
                                <th style="min-width: 120px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- 1. Main Service -->
                                <td>
                                    <select id="se_main_service" class="form-select form-select-sm" onchange="onMainServiceChange(this.value)" required
                                            style="height: 38px; font-size: 13px; font-weight: 700; text-transform: uppercase;">
                                        <option value="">-- Main Service --</option>
                                        @foreach($uniqueMainServices as $mainName)
                                            <option value="{{ $mainName }}">{{ strtoupper($mainName) }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- 2. Sub Service -->
                                <td>
                                    <select id="se_sub_service_select" class="form-select form-select-sm" onchange="onSubServiceChange(this.value)" required disabled
                                            style="height: 38px; font-size: 12.5px; font-weight: 600;">
                                        <option value="">-- Select Main First --</option>
                                    </select>
                                </td>

                                <!-- 3. Rate (₹) -->
                                <td>
                                    <input type="number" step="0.01" id="se_rate" name="rate" class="form-control form-control-sm" required placeholder="0"
                                           oninput="calculateRemaining()"
                                           style="height: 38px; font-family: var(--font-mono); font-weight: 700; font-size: 13.5px;">
                                </td>

                                <!-- 4. Received (₹) -->
                                <td>
                                    <input type="number" step="0.01" id="se_received" name="received" class="form-control form-control-sm" placeholder="0"
                                           oninput="calculateRemaining()"
                                           style="height: 38px; font-family: var(--font-mono); font-weight: 700; font-size: 13.5px; color: #166534;">
                                </td>

                                <!-- 5. Remaining (₹) -->
                                <td>
                                    <input type="text" id="se_remaining" class="form-control form-control-sm" readonly placeholder="0.00"
                                           style="height: 38px; font-family: var(--font-mono); font-weight: 800; font-size: 13.5px; background: #f1f5f9; color: #0f172a;">
                                </td>

                                <!-- 6. Work Details -->
                                <td>
                                    <input type="text" id="se_work_details" name="work_details" class="form-control form-control-sm" 
                                           placeholder="e.g. ONLY PHOTO & Sig"
                                           style="height: 38px; font-size: 12.5px;">
                                </td>

                                <!-- 7. Documents List -->
                                <td>
                                    <input type="text" id="se_docs_list" name="documents_list" class="form-control form-control-sm" 
                                           placeholder="e.g. ADHAR + OLD PAN"
                                           style="height: 38px; font-size: 12.5px;">
                                </td>

                                <!-- 8. Pending Remark -->
                                <td>
                                    <input type="text" id="se_pending_remark" name="pending_remarks" class="form-control form-control-sm" 
                                           placeholder="e.g. PHOTO PENDING"
                                           style="height: 38px; font-size: 12.5px; border: 1.5px solid #f87171; background: #fff5f5; color: #991b1b; font-weight: 600;">
                                </td>

                                <!-- 9. Followup Date -->
                                <td>
                                    <input type="date" id="se_followup" name="follow_up_date" class="form-control form-control-sm"
                                           style="height: 38px; font-size: 12.5px;">
                                </td>

                                <!-- 10. Status -->
                                <td>
                                    <select id="se_status" name="work_status" class="form-select form-select-sm"
                                            style="height: 38px; font-size: 12px; font-weight: 700;">
                                        <option value="PENDING" style="color: #dc2626;">PENDING</option>
                                        <option value="IN_PROGRESS" style="color: #d97706;">IN PROGRESS</option>
                                        <option value="DONE" style="color: #166534;">DONE</option>
                                    </select>
                                </td>

                                <!-- 11. Operator -->
                                <td>
                                    <select id="se_operator" name="assigned_employee_id" class="form-select form-select-sm" required
                                            style="height: 38px; font-size: 12.5px; font-weight: 600;">
                                        <option value="">-- Operator --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ strtoupper($emp->user?->name ?? $emp->name ?? 'Staff') }} ({{ $emp->designation ?: 'Staff' }})</option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- 12. Operator Links (Apply Now & Watch Tutorial) -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                        <a id="row-btn-apply" href="#" target="_blank" class="btn btn-sm"
                                           style="display: none; background: #2563eb; color: white; border: none; font-weight: 700; font-size: 11.5px; padding: 6px 11px; border-radius: 6px; white-space: nowrap; text-decoration: none; box-shadow: 0 1px 3px rgba(37,99,235,0.25);"
                                           title="Open official government work portal">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Apply Now
                                        </a>

                                        <a id="row-btn-tutorial" href="#" target="_blank" class="btn btn-sm"
                                           style="display: none; background: #dc2626; color: white; border: none; font-weight: 700; font-size: 11.5px; padding: 6px 11px; border-radius: 6px; white-space: nowrap; text-decoration: none; box-shadow: 0 2px 4px rgba(220,38,38,0.25);"
                                           title="Watch step-by-step video training tutorial">
                                            <i class="fa-brands fa-youtube"></i> Watch Tutorial
                                        </a>

                                        <span id="row-no-links" style="color: #94a3b8; font-size: 11.5px; font-weight: 500;">
                                            Select service
                                        </span>
                                    </div>
                                </td>

                                <!-- 13. Save Button -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <button type="submit" id="se_submit_btn" class="btn btn-sm"
                                            style="height: 38px; background: #ea580c; color: white; border: none; font-weight: 800; font-size: 13px; padding: 0 16px; border-radius: 6px; box-shadow: 0 2px 6px rgba(234, 88, 12, 0.3); cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-check"></i> Save Row
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ==========================================
// 1. STAR RATING LOGIC
// ==========================================
let currentRating = 5;

function setStarRating(val) {
    val = parseInt(val, 10);
    if (isNaN(val) || val < 1) val = 1;
    if (val > 5) val = 5;
    currentRating = val;

    document.getElementById('fe_star_rating').value = val;
    document.getElementById('star-badge').innerText = val + ' ★';

    const stars = document.querySelectorAll('#star-icons-container .star-btn');
    stars.forEach(star => {
        const starVal = parseInt(star.getAttribute('data-val'), 10);
        if (starVal <= val) {
            star.style.color = '#f59e0b';
            star.classList.remove('fa-regular');
            star.classList.add('fa-solid');
        } else {
            star.style.color = '#cbd5e1';
            star.classList.remove('fa-solid');
            star.classList.add('fa-regular');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const starPicker = document.getElementById('star-rating-picker');
    const stars = document.querySelectorAll('#star-icons-container .star-btn');
    
    stars.forEach(star => {
        star.addEventListener('click', function(e) {
            e.stopPropagation();
            setStarRating(this.getAttribute('data-val'));
        });
    });

    if (starPicker) {
        starPicker.addEventListener('keydown', function(e) {
            if (['1', '2', '3', '4', '5'].includes(e.key)) {
                e.preventDefault();
                setStarRating(parseInt(e.key, 10));
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.altKey && ['1', '2', '3', '4', '5'].includes(e.key)) {
            e.preventDefault();
            setStarRating(parseInt(e.key, 10));
        }
    });

    // Close search dropdown on click outside
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('cust-search-dropdown');
        const input = document.getElementById('se_cust_search');
        if (dd && !dd.contains(e.target) && e.target !== input) {
            dd.style.display = 'none';
        }
    });
});

// ==========================================
// 2. FAST CUSTOMER PROFILE SUBMISSION
// ==========================================
function saveFastCustomerRow(e) {
    e.preventDefault();
    const btn = document.getElementById('fe_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving to CRM...';

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_name: document.getElementById('fe_name').value.trim(),
        gender: document.getElementById('fe_gender').value,
        birth_date: document.getElementById('fe_birth_date').value || null,
        mobile: document.getElementById('fe_mobile').value.trim(),
        alternate_mobile: document.getElementById('fe_alt_mobile').value.trim() || null,
        occupation: document.getElementById('fe_occupation').value.trim() || null,
        email: document.getElementById('fe_email').value.trim() || null,
        star_rating: parseInt(document.getElementById('fe_star_rating').value, 10) || 5,
    };

    fetch('{{ route("fast_entry.store_row") }}', {
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
        btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Save to CRM';

        if (res.success) {
            const d = res.data;

            // Show clean success alert banner
            const alertBox = document.getElementById('save-alert');
            const alertTitle = document.getElementById('alert-title');
            const alertDesc = document.getElementById('alert-desc');
            const alertProfileBtn = document.getElementById('alert-profile-btn');

            alertTitle.innerText = `Customer Registered: ${d.customer_code} - ${d.name}`;
            alertDesc.innerText = `Profile successfully saved in Customers CRM (${d.mobile} | Rating: ${d.star_rating}★)`;
            alertProfileBtn.href = d.url;
            alertBox.style.display = 'flex';

            // Auto-select this newly registered customer for New Entry below!
            selectCustomerForNewEntry({
                id: d.id,
                customer_code: d.customer_code,
                name: d.name,
                mobile: d.mobile,
                star_rating: d.star_rating
            });

            // Reset customer form
            document.getElementById('fe_name').value = '';
            document.getElementById('fe_mobile').value = '';
            document.getElementById('fe_birth_date').value = '';
            document.getElementById('fe_alt_mobile').value = '';
            document.getElementById('fe_occupation').value = '';
            document.getElementById('fe_email').value = '';
            setStarRating(5);
            document.getElementById('fe_name').focus();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Save to CRM';
        alert('Failed to save customer. Please check network connection.');
    });
}

// ==========================================
// 3. NEW SERVICE ENTRY LOGIC (EXCEL IMAGE 2)
// ==========================================
function scrollToNewEntry() {
    const card = document.getElementById('new-entry-card');
    card.scrollIntoView({ behavior: 'smooth' });
    const searchInput = document.getElementById('se_cust_search');
    setTimeout(() => searchInput.focus(), 400);
}

let searchDebounceTimeout = null;

function onCustomerSearchInput(query) {
    clearTimeout(searchDebounceTimeout);
    query = query.trim();

    const dd = document.getElementById('cust-search-dropdown');
    const spinner = document.getElementById('cust-search-spinner');

    if (query.length < 2) {
        dd.style.display = 'none';
        return;
    }

    spinner.style.display = 'block';

    searchDebounceTimeout = setTimeout(() => {
        fetch(`{{ route('fast_entry.search_customers') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(customers => {
                spinner.style.display = 'none';
                if (!customers || customers.length === 0) {
                    dd.innerHTML = `<div style="padding: 12px 16px; color: #94a3b8; font-size: 13px;">No customer found matching "${query}". Register them in the form above!</div>`;
                    dd.style.display = 'block';
                    return;
                }

                let html = '';
                customers.forEach(c => {
                    html += `
                        <div class="search-item" onclick='selectCustomerForNewEntry(${JSON.stringify(c)})'>
                            <div>
                                <div style="font-weight: 700; color: #0f172a; text-transform: uppercase;">
                                    ${c.name}
                                    <span style="font-size: 11px; color: #b45309; margin-left: 6px;">${c.star_rating}★</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; font-family: var(--font-mono);">
                                    <span style="color: #ea580c; font-weight: 700;">${c.customer_code}</span> | Mobile: ${c.mobile}
                                </div>
                            </div>
                            <span class="badge badge-paid" style="font-size: 11px;">Select</span>
                        </div>
                    `;
                });

                dd.innerHTML = html;
                dd.style.display = 'block';
            })
            .catch(() => {
                spinner.style.display = 'none';
            });
    }, 250);
}

function onCustomerSearchFocus() {
    const val = document.getElementById('se_cust_search').value.trim();
    if (val.length >= 2) {
        onCustomerSearchInput(val);
    }
}

function selectCustomerForNewEntry(cust) {
    document.getElementById('se_customer_id').value = cust.id;
    document.getElementById('sc-name').innerText = cust.name;
    document.getElementById('sc-code').innerText = cust.customer_code;
    document.getElementById('sc-mobile').innerText = cust.mobile;
    document.getElementById('sc-rating').innerText = (cust.star_rating || 5) + '★';

    document.getElementById('selected-customer-chip').style.display = 'flex';
    document.getElementById('se_cust_search').style.display = 'none';
    document.getElementById('cust-search-dropdown').style.display = 'none';

    // Focus on Main Service Dropdown
    document.getElementById('se_main_service').focus();
}

function clearSelectedCustomer() {
    document.getElementById('se_customer_id').value = '';
    document.getElementById('selected-customer-chip').style.display = 'none';
    const searchInput = document.getElementById('se_cust_search');
    searchInput.style.display = 'block';
    searchInput.value = '';
    searchInput.focus();
}

// All services catalog from backend
const allServices = @json($services);

function onMainServiceChange(mainName) {
    const subSelect = document.getElementById('se_sub_service_select');
    subSelect.innerHTML = '<option value="">-- Choose Sub Service --</option>';

    if (!mainName) {
        subSelect.disabled = true;
        resetServiceFields();
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

    // If only 1 sub-service, auto-select it immediately!
    if (matched.length === 1) {
        subSelect.value = matched[0].id;
        onSubServiceChange(matched[0].id);
    } else {
        resetServiceFields();
        subSelect.focus();
    }
}

function onSubServiceChange(serviceId) {
    if (!serviceId) {
        resetServiceFields();
        return;
    }

    const srv = allServices.find(s => s.id == serviceId);
    if (!srv) return;

    // Set hidden fields
    document.getElementById('se_service_id').value = srv.id;
    document.getElementById('se_sub_service').value = srv.sub_service_name;

    // Auto-fill Rate, Received, Remaining
    const price = parseFloat(srv.price) || 0;
    document.getElementById('se_rate').value = price;
    document.getElementById('se_received').value = price;
    calculateRemaining();

    // Auto-fill Documents List
    const docs = Array.isArray(srv.required_documents_json) ? srv.required_documents_json.join(', ') : '';
    document.getElementById('se_docs_list').value = docs;

    // Auto-calculate Follow-up Date (Today + expected days)
    const days = parseInt(srv.expected_processing_days) || 7;
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + days);
    document.getElementById('se_followup').value = targetDate.toISOString().split('T')[0];

    // Configure Operator Guidance Bar & Row Links
    const workUrl = srv.work_portal_link || srv.service_portal_link;
    const tutorialUrl = srv.video_instruction_link;

    // Row Buttons
    const btnRowApply = document.getElementById('row-btn-apply');
    const btnRowTutorial = document.getElementById('row-btn-tutorial');
    const noLinksSpan = document.getElementById('row-no-links');

    let hasAnyLink = false;
    if (workUrl && workUrl.trim() !== '') {
        btnRowApply.href = workUrl.trim();
        btnRowApply.style.display = 'inline-flex';
        hasAnyLink = true;
    } else {
        btnRowApply.style.display = 'none';
    }

    if (tutorialUrl && tutorialUrl.trim() !== '') {
        btnRowTutorial.href = tutorialUrl.trim();
        btnRowTutorial.style.display = 'inline-flex';
        hasAnyLink = true;
    } else {
        btnRowTutorial.style.display = 'none';
    }

    if (noLinksSpan) {
        noLinksSpan.style.display = hasAnyLink ? 'none' : 'inline';
    }

    // Top Operator Guidance Bar
    const opBar = document.getElementById('service-operator-bar');
    const obTitle = document.getElementById('ob-title');
    const obDays = document.getElementById('ob-days');
    const obDocs = document.getElementById('ob-docs');
    const obDocsText = document.getElementById('ob-docs-text');
    const topBtnWork = document.getElementById('btn-work-link');
    const topBtnTutorial = document.getElementById('btn-tutorial-link');

    if (opBar) {
        obTitle.innerText = `${srv.main_service_name} ➔ ${srv.sub_service_name}`;
        obDays.innerText = `⏱️ Expected: ${days} Days`;

        if (workUrl && workUrl.trim() !== '') {
            topBtnWork.href = workUrl.trim();
            topBtnWork.style.display = 'inline-flex';
        } else {
            topBtnWork.style.display = 'none';
        }

        if (tutorialUrl && tutorialUrl.trim() !== '') {
            topBtnTutorial.href = tutorialUrl.trim();
            topBtnTutorial.style.display = 'inline-flex';
        } else {
            topBtnTutorial.style.display = 'none';
        }

        if (docs && docs.trim() !== '') {
            obDocsText.innerText = docs;
            obDocs.style.display = 'flex';
        } else {
            obDocs.style.display = 'none';
        }

        opBar.style.display = 'block';
    }
}

function resetServiceFields() {
    document.getElementById('se_service_id').value = '';
    document.getElementById('se_sub_service').value = '';
    document.getElementById('se_rate').value = '';
    document.getElementById('se_received').value = '';
    document.getElementById('se_remaining').value = '';
    document.getElementById('se_work_details').value = '';
    document.getElementById('se_docs_list').value = '';
    document.getElementById('se_pending_remark').value = '';
    document.getElementById('se_followup').value = '';

    const btnRowApply = document.getElementById('row-btn-apply');
    const btnRowTutorial = document.getElementById('row-btn-tutorial');
    const noLinksSpan = document.getElementById('row-no-links');
    if (btnRowApply) btnRowApply.style.display = 'none';
    if (btnRowTutorial) btnRowTutorial.style.display = 'none';
    if (noLinksSpan) noLinksSpan.style.display = 'inline';

    const opBar = document.getElementById('service-operator-bar');
    if (opBar) opBar.style.display = 'none';
}

function copyDocsToEntry() {
    const docsText = document.getElementById('ob-docs-text').innerText;
    if (docsText) {
        document.getElementById('se_docs_list').value = docsText;
    }
}

function calculateRemaining() {
    const rate = parseFloat(document.getElementById('se_rate').value) || 0;
    const received = parseFloat(document.getElementById('se_received').value) || 0;
    const rem = Math.max(0, rate - received);

    const remEl = document.getElementById('se_remaining');
    remEl.value = '₹ ' + rem.toFixed(2);
    if (rem > 0) {
        remEl.style.color = '#dc2626';
        remEl.style.fontWeight = '800';
    } else {
        remEl.style.color = '#166534';
        remEl.style.fontWeight = '800';
    }
}

function saveServiceEntryRow(e) {
    e.preventDefault();
    const custId = document.getElementById('se_customer_id').value;
    if (!custId) {
        alert('Please search and select a customer first!');
        document.getElementById('se_cust_search').focus();
        return;
    }

    const btn = document.getElementById('se_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Entry...';

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_id: custId,
        service_id: document.getElementById('se_service_id').value,
        sub_service: document.getElementById('se_sub_service').value.trim() || null,
        rate: document.getElementById('se_rate').value,
        received: document.getElementById('se_received').value || 0,
        work_details: document.getElementById('se_work_details').value.trim() || null,
        documents_list: document.getElementById('se_docs_list').value.trim() || null,
        pending_remarks: document.getElementById('se_pending_remark').value.trim() || null,
        follow_up_date: document.getElementById('se_followup').value || null,
        work_status: document.getElementById('se_status').value,
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
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save Service Entry';

        if (res.success) {
            const d = res.data;

            // Show alert
            const alertBox = document.getElementById('save-alert');
            const alertTitle = document.getElementById('alert-title');
            const alertDesc = document.getElementById('alert-desc');
            const alertProfileBtn = document.getElementById('alert-profile-btn');

            alertTitle.innerText = `Service Recorded: ${d.service_name} (${d.sub_service || 'Standard'})`;
            alertDesc.innerText = `Customer: ${d.customer_name} | Total: ₹${d.rate} | Operator: ${d.operator} (Saved to Customer CRM)`;
            alertProfileBtn.href = d.customer_url;
            alertBox.style.display = 'flex';

            // Reset service form fields
            document.getElementById('se_main_service').value = '';
            const subSel = document.getElementById('se_sub_service_select');
            subSel.innerHTML = '<option value="">-- Select Main First --</option>';
            subSel.disabled = true;
            resetServiceFields();
            clearSelectedCustomer();

            alertBox.scrollIntoView({ behavior: 'smooth' });
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save Service Entry';
        alert('Failed to save service entry. Please verify details.');
    });
}

// Shortcut: Ctrl + Enter to quickly save row
document.getElementById('new-service-entry-form').addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        saveServiceEntryRow(e);
    }
});
</script>
@endpush
@endsection
