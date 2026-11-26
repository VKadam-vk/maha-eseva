@extends('layouts.app')

@section('title', 'Customers CRM')

@section('content')
<style>
    /* Horizontal Scrollbars - High Visibility & Modern Styling */
    .table-responsive,
    #customerTopScrollbar,
    #customerFloatingScrollbar {
        scrollbar-width: auto;
        scrollbar-color: #0284c7 #e2e8f0;
    }

    .table-responsive::-webkit-scrollbar,
    #customerTopScrollbar::-webkit-scrollbar,
    #customerFloatingScrollbar::-webkit-scrollbar {
        height: 12px !important;
    }

    .table-responsive::-webkit-scrollbar-track,
    #customerTopScrollbar::-webkit-scrollbar-track,
    #customerFloatingScrollbar::-webkit-scrollbar-track {
        background: #e2e8f0 !important;
        border-radius: 6px;
    }

    .table-responsive::-webkit-scrollbar-thumb,
    #customerTopScrollbar::-webkit-scrollbar-thumb,
    #customerFloatingScrollbar::-webkit-scrollbar-thumb {
        background: #0284c7 !important;
        border-radius: 6px;
        border: 2px solid #e2e8f0;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover,
    #customerTopScrollbar::-webkit-scrollbar-thumb:hover,
    #customerFloatingScrollbar::-webkit-scrollbar-thumb:hover {
        background: #0369a1 !important;
    }

    /* Compressed Table Cells & Instant Hover Preview */
    .cell-hover-preview {
        cursor: pointer;
        position: relative;
        transition: background-color 0.15s ease;
    }
    .cell-hover-preview:hover {
        background-color: #e0f2fe !important;
        border-radius: 4px;
    }

    #erpGlobalHoverTooltip {
        position: fixed;
        z-index: 9999999;
        background: #0f172a;
        color: #f8fafc;
        border-radius: 8px;
        padding: 9px 13px;
        font-size: 12.5px;
        line-height: 1.5;
        max-width: 420px;
        min-width: 180px;
        word-break: break-word;
        box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.45), 0 4px 6px -2px rgba(0, 0, 0, 0.25);
        border: 1px solid #334155;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.12s ease, transform 0.12s ease;
        transform: translateY(4px);
    }
    #erpGlobalHoverTooltip.visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    #erpGlobalHoverTooltip .tooltip-title {
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #38bdf8;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    #erpGlobalHoverTooltip .tooltip-body {
        color: #f8fafc;
        font-weight: 500;
        white-space: pre-wrap;
    }
</style>
<div class="page-header">
    <div>
        <h1 class="page-title">Customers CRM & Directory</h1>
        <p class="page-subtitle">Search, manage and create customer profiles with duplicate protection</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        @if(auth()->user()?->isSoftwareOwner())
            <button type="button" class="btn" onclick="openBroadcastModal()" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; font-weight: 700; border: none; box-shadow: 0 2px 6px rgba(22, 163, 74, 0.35); display: inline-flex; align-items: center; gap: 7px;">
                <i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i> Broadcast Message
            </button>
        @endif
        <a href="{{ route('import_export.import.customers') }}" class="btn btn-secondary">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </a>
        <a href="{{ route('reports.customers', array_merge(request()->query(), ['export' => 1])) }}" class="btn btn-secondary" onclick="exportFilteredCustomers(event)" title="Export customers with Category to CSV">
            <i class="fa-solid fa-file-export"></i> Export CSV
        </a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Quick Add Customer
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form id="customerFilterForm" action="{{ route('customers.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 250px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                    <span>Search Name, Mobile, Code or Email</span>
                    <span id="custSearchIndicator" style="display: none; font-size: 11px; color: #4f46e5; font-weight: 700;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i> Filtering...
                    </span>
                </label>
                <div style="position: relative;">
                    <input type="text" name="search" id="customerSearchInput" class="form-control" 
                           placeholder="Search customer name, mobile or code..." 
                           value="{{ request('search') }}"
                           oninput="autoFilterCustomers(this.value)"
                           autocomplete="off"
                           style="padding-right: 32px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 13px;"></i>
                </div>
            </div>

            @if((Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner()) && isset($branches) && $branches->count() > 1)
            <div style="min-width: 210px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px; font-weight: 700; color: #475569;">Filter by Branch / Center</label>
                <select name="branch_id" class="form-select" onchange="this.form.submit()">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (isset($selectedBranchId) && $selectedBranchId == $b->id) ? 'selected' : '' }}>
                            📍 {{ $b->name }} {{ $b->is_main_branch ? '(Head Office)' : '' }}
                        </option>
                    @endforeach
                    <option value="all" {{ (isset($selectedBranchId) && is_null($selectedBranchId)) ? 'selected' : '' }}>
                        🌐 All Branches (Overview)
                    </option>
                </select>
            </div>
            @endif

            <div style="min-width: 190px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Occupation</label>
                <select name="occupation" class="form-select" onchange="this.form.submit()">
                    <option value="">All Occupations</option>
                    @foreach($occupations as $occ)
                        <option value="{{ $occ }}" {{ strtoupper(request('occupation')) == strtoupper($occ) ? 'selected' : '' }}>{{ $occ }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customer Table -->
<div class="erp-card">
    <div style="padding: 12px 18px; border-bottom: 1.5px solid #cbd5e1; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: #ffffff;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-users" style="color: #0284c7; font-size: 15px;"></i>
            <span style="font-size: 13.5px; font-weight: 800; color: #1e293b;">
                Customer Directory (एकूण ग्राहक)
            </span>
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; border-radius: 12px; padding: 2px 8px;">
                {{ $customers->total() }} Customers
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 5px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 6px;">
            <span style="font-size: 11px; font-weight: 700; color: #0284c7;"><i class="fa-solid fa-arrows-left-right"></i> Scroll:</span>
            <button type="button" class="btn btn-sm btn-secondary" onclick="scrollCustomersTable(-260)" style="padding: 2px 7px; font-size: 11px; height: 24px; line-height: 1; border-radius: 4px;" title="Scroll Left">
                <i class="fa-solid fa-chevron-left"></i> Left
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="scrollCustomersTable(260)" style="padding: 2px 7px; font-size: 11px; height: 24px; line-height: 1; border-radius: 4px;" title="Scroll Right">
                Right <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Top Synced Horizontal Scrollbar -->
    <div id="customerTopScrollbar" style="overflow-x: auto; overflow-y: hidden; height: 13px; background: #e2e8f0; border-bottom: 1.5px solid #cbd5e1; display: none;">
        <div id="customerTopScrollbarInner" style="height: 1px;"></div>
    </div>

    <div class="table-responsive">
        <table id="customer-table" class="erp-table">
            <thead>
                <tr>
                    <th style="min-width: 260px;">Customer (Name, Mobile, Code)</th>
                    <th style="min-width: 90px;">Gender</th>
                    <th style="min-width: 140px;">Occupation</th>
                    <th style="min-width: 120px;">Applications</th>
                    <th style="min-width: 120px;">Total Spent</th>
                    <th style="min-width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td style="padding: 10px 14px;" class="cell-hover-preview" data-hover-title="Customer & Contact Details" data-hover-text="{{ $c->name }} | Mobile: {{ $c->mobile }}{{ $c->alternate_mobile ? ' (Alt: ' . $c->alternate_mobile . ')' : '' }} | Code: {{ $c->customer_code }}{{ $c->email ? ' | Email: ' . $c->email : '' }}{{ $c->address ? ' | Address: ' . $c->address : '' }}{{ $c->creator ? ' | Registered By: ' . $c->creator->name : '' }}">
                            <!-- 1. Customer Name + Badges -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('customers.show', $c->id) }}" style="font-weight: 800; color: var(--text-main); text-transform: uppercase; text-decoration: none; font-size: 13.5px; line-height: 1.25;">
                                    {{ $c->name }}
                                </a>
                                @if($c->is_blacklisted)
                                    <span style="font-size: 10px; color: #dc2626; background: #fee2e2; border: 1px solid #f87171; padding: 1px 6px; border-radius: 4px; font-weight: 800;">
                                        <i class="fa-solid fa-ban"></i> BLACKLISTED
                                    </span>
                                @endif
                                @if($c->star_rating)
                                    <span style="font-size: 11px; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 1px 5px; border-radius: 4px; font-weight: 700;">
                                        <i class="fa-solid fa-star" style="color: #f59e0b;"></i> {{ $c->star_rating }}★
                                    </span>
                                @endif
                            </div>

                            <!-- 2. Mobile Name / Number + Alt -->
                            <div style="margin-top: 4px; font-size: 12.5px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <strong style="font-family: var(--font-mono); color: #0f172a;">
                                    <i class="fa-solid fa-phone" style="font-size: 10.5px; color: #64748b; margin-right: 3px;"></i>{{ $c->mobile }}
                                </strong>
                                @if($c->alternate_mobile)
                                    <span style="font-size: 11px; color: var(--text-muted);">(Alt: {{ $c->alternate_mobile }})</span>
                                @endif
                            </div>

                            <!-- 3. Customer Code + Occupation / Email / Creator -->
                            <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11.5px;">
                                <a href="{{ route('customers.show', $c->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c; text-decoration: none;">
                                    {{ $c->customer_code }}
                                </a>
                                @if($c->email)
                                    <span style="color: var(--text-muted); font-size: 11px;">&bull; {{ $c->email }}</span>
                                @endif
                                @if(Auth::user()->isAdmin())
                                    <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 1px 7px; border-radius: 4px; font-weight: 700; font-size: 10.5px; display: inline-flex; align-items: center; gap: 4px;" title="Registered by {{ $c->creator?->name ?? 'Admin' }}">
                                        <i class="fa-solid fa-user-pen" style="font-size: 10px;"></i> By: {{ $c->creator?->name ?? 'Admin' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="cell-hover-preview" data-hover-title="Gender" data-hover-text="{{ $c->gender }}"><span class="badge badge-hold" style="font-size: 10px;">{{ $c->gender }}</span></td>
                        <td class="cell-hover-preview" data-hover-title="Customer Occupation" data-hover-text="{{ $c->occupation ?: 'Not Specified' }}">
                            @if(!empty($c->occupation))
                                <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 700; font-size: 11px;">
                                    <i class="fa-solid fa-briefcase" style="font-size: 10px; margin-right: 3px;"></i> {{ strtoupper($c->occupation) }}
                                </span>
                            @else
                                <span style="color: #94a3b8; font-size: 11.5px; font-style: italic;">-</span>
                            @endif
                        </td>
                        <td class="cell-hover-preview" data-hover-title="Total Applications" data-hover-text="{{ $c->applications_count }} Applications Processed"><span class="badge badge-process">{{ $c->applications_count }} Apps</span></td>
                        <td class="cell-hover-preview" data-hover-title="Total Payments" data-hover-text="Total counter receipts: ₹{{ number_format($c->total_paid, 2) }}"><strong>₹{{ number_format($c->total_paid, 2) }}</strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <a href="{{ route('customers.show', $c->id) }}" class="btn btn-secondary btn-sm" title="View 360 Profile">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                @if(auth()->user()?->isAdmin())
                                    <form action="{{ route('customers.destroy', $c->id) }}" method="POST" style="display: inline; margin: 0; padding: 0;"
                                          onsubmit="return confirm('Are you sure you want to delete customer {{ $c->name }}? All records and applications will be deleted permanently.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 5px 8px; font-size: 11px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;" title="Delete Customer (Admin Only)">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                            No customers found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Sticky Floating Bottom Horizontal Scrollbar -->
    <div id="customerFloatingScrollbar" style="position: fixed; bottom: 0; left: 0; right: 0; background: #ffffff; border-top: 2px solid #0284c7; box-shadow: 0 -4px 12px rgba(0,0,0,0.15); z-index: 998; overflow-x: auto; overflow-y: hidden; height: 15px; display: none;">
        <div id="customerFloatingScrollbarInner" style="height: 1px;"></div>
    </div>
    @if($customers->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $customers->links() }}
        </div>
    @endif
</div>

@if(auth()->user()?->isSoftwareOwner())
<!-- Broadcast WhatsApp Modal (Software Owner Only) -->
<div id="broadcastModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 920px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: popIn 0.2s ease; display: flex; flex-direction: column; max-height: 94vh;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255, 255, 255, 0.2); width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 22px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        WhatsApp Smart Customer Broadcast <span style="font-size: 11px; background: rgba(255, 255, 255, 0.25); padding: 2px 8px; border-radius: 20px; font-weight: 600;">CRM Exclusive</span>
                    </h3>
                    <p style="margin: 2px 0 0 0; font-size: 11.5px; opacity: 0.95;">
                        केवळ Customer CRM मधील पडताळणी केलेल्या ग्राहकांनाच थेट अधिकृत WhatsApp पाठवा
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeBroadcastModal()" style="background: none; border: none; color: #ffffff; font-size: 24px; cursor: pointer; line-height: 1; padding: 0 4px; opacity: 0.8;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">&times;</button>
        </div>

        <!-- 2-Step Workflow Tabs -->
        <div style="display: flex; background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 0 16px;">
            <button type="button" id="bcTabBtn_1" onclick="switchBcTab(1)" style="flex: 1; padding: 11px 14px; border: none; background: none; font-size: 13px; font-weight: 800; color: #16a34a; border-bottom: 3px solid #16a34a; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease;">
                <i class="fa-solid fa-users"></i> १. संपर्क व ग्राहक निवडा <span id="bcTabBadge" style="background: #dcfce7; color: #15803d; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 700; border: 1px solid #86efac;">0 ग्राहक</span>
            </button>
            <button type="button" id="bcTabBtn_2" onclick="switchBcTab(2)" style="flex: 1; padding: 11px 14px; border: none; background: none; font-size: 13px; font-weight: 700; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease;">
                <i class="fa-solid fa-gift" style="color: #ea580c;"></i> २. मेसेज, ऑफर्स व मजकूर <span style="font-size: 10.5px; background: #ffedd5; color: #c2410c; padding: 2px 7px; border-radius: 10px; font-weight: 700;">६ रेडीमेड ऑफर्स</span>
            </button>
        </div>

        <!-- Modal Body Container -->
        <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">

            <!-- ============================================== -->
            <!-- TAB 1: CONTACT SELECTION & FILTERS -->
            <!-- ============================================== -->
            <div id="bcTabContent_1">
                <!-- Quick Offer Teaser Banner -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                    <span style="font-size: 11.5px; color: #92400e; font-weight: 700;">
                        🎁 <strong>थेट ऑफर मेसेज पाठवायचा आहे?</strong> खालील १ क्लिकने ऑफर निवडा:
                    </span>
                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-xs" onclick="applyBcOfferPreset('offer'); switchBcTab(2);" style="background: #ffffff; border: 1px solid #f59e0b; color: #b45309; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 4px;">🎁 पॅन/दाखले ऑफर</button>
                        <button type="button" class="btn btn-xs" onclick="applyBcOfferPreset('ladki_bahin'); switchBcTab(2);" style="background: #ffffff; border: 1px solid #db2777; color: #be185d; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 4px;">👩 लाडकी बहीण</button>
                        <button type="button" class="btn btn-xs" onclick="applyBcOfferPreset('farmer'); switchBcTab(2);" style="background: #ffffff; border: 1px solid #16a34a; color: #15803d; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 4px;">🌾 शेतकरी योजना</button>
                        <button type="button" class="btn btn-xs" onclick="switchBcTab(2);" style="background: #fef3c7; border: 1px solid #fde68a; color: #78350f; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 4px;">सर्व ६ ऑफर्स ➜</button>
                    </div>
                </div>

                <!-- STEP 1: SMART CRM FILTERS -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-filter" style="color: #16a34a;"></i> १. अचूक ग्राहक फिल्टर (Specific CRM Filters)
                        </span>
                        <span style="font-size: 10.5px; color: #64748b; font-style: italic;">
                            फिल्टर बदलताच खालील यादी आपोआप अपडेट होते
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 8px;">
                        <!-- Category Filter -->
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #475569; margin-bottom: 2px; display: block;">ग्राहक वर्ग (Category):</label>
                            <select id="bc_filter_category" class="form-select form-select-sm" onchange="fetchBroadcastPreview()" style="font-size: 11.5px;">
                                <option value="">-- All Categories (सर्व वर्ग) --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Gender Filter (Crucial for Ladki Bahin Scheme!) -->
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #475569; margin-bottom: 2px; display: block;">लिंग (Gender Filter):</label>
                            <select id="bc_filter_gender" class="form-select form-select-sm" onchange="fetchBroadcastPreview()" style="font-size: 11.5px; font-weight: 600;">
                                <option value="">-- All Genders (सर्व लिंग) --</option>
                                <option value="FEMALE">👩 फक्त महिला (लाडकी बहीण / Women Only)</option>
                                <option value="MALE">👨 फक्त पुरुष (Men Only)</option>
                                <option value="OTHER">⚧️ इतर (Other)</option>
                            </select>
                        </div>

                        <!-- Occupation Filter -->
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #475569; margin-bottom: 2px; display: block;">व्यवसाय (Occupation):</label>
                            <select id="bc_filter_occupation" class="form-select form-select-sm" onchange="fetchBroadcastPreview()" style="font-size: 11.5px;">
                                <option value="">-- All Occupations (सर्व व्यवसाय) --</option>
                                @foreach($occupations as $occ)
                                    <option value="{{ $occ }}">{{ $occ }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Star Rating Filter -->
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #475569; margin-bottom: 2px; display: block;">लॉयल्टी रेटिंग (Star):</label>
                            <select id="bc_filter_star" class="form-select form-select-sm" onchange="fetchBroadcastPreview()" style="font-size: 11.5px;">
                                <option value="">-- All Ratings (सर्व) --</option>
                                <option value="5">⭐⭐⭐⭐⭐ 5-Star VIP Only</option>
                                <option value="4">⭐⭐⭐⭐ 4+ Star Frequent</option>
                                <option value="3">⭐⭐⭐ 3+ Star Regular</option>
                            </select>
                        </div>

                        <!-- Village / City Filter -->
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #475569; margin-bottom: 2px; display: block;">गाव / शहर (City / Village):</label>
                            <select id="bc_filter_city" class="form-select form-select-sm" onchange="fetchBroadcastPreview()" style="font-size: 11.5px;">
                                <option value="">-- All Cities (सर्व गावे) --</option>
                                @if(isset($cities))
                                    @foreach($cities as $ct)
                                        <option value="{{ $ct }}">{{ $ct }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Quick Search by Name / Mobile -->
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #475569; margin-bottom: 2px; display: block;">थेट नाव/नंबर शोधा:</label>
                            <input type="text" id="bc_filter_search" class="form-control form-control-sm" placeholder="उदा. राहुल किंवा 9822..." oninput="debounceBroadcastPreview()" style="font-size: 11.5px;">
                        </div>
                    </div>
                </div>

                <!-- STEP 2: RECIPIENT DIRECTORY & CHERRY-PICK CHECKLIST -->
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span style="font-size: 12px; font-weight: 800; color: #0f172a;">
                                २. संपर्क यादी (Select Contacts):
                            </span>
                            <span style="font-size: 11px; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 12px; font-weight: 800; border: 1px solid #86efac;">
                                🎯 <span id="bcSelectedCount">0</span> ग्राहक निवडले (एकूण <span id="bcTotalCount">0</span> पैकी)
                            </span>
                            <span style="font-size: 11px; color: #64748b;">
                                Meta खर्च: <strong>₹ <span id="bcEstimatedCost">0.00</span></strong>
                            </span>
                        </div>

                        <!-- Action and Search Toolbar -->
                        <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="bcTableSearch" class="form-control form-control-sm" placeholder="🔍 यादीत त्वरित शोधा..." oninput="filterBcTableClientSide()" style="font-size: 11px; width: 170px; padding: 2px 7px; border-color: #86efac;">
                            <button type="button" class="btn btn-xs btn-outline-success" onclick="toggleSelectAllBc(true)" style="font-size: 10.5px; padding: 2px 7px; font-weight: 700;">
                                <i class="fa-solid fa-check-double"></i> सर्व निवडा
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="toggleSelectAllBc(false)" style="font-size: 10.5px; padding: 2px 7px; font-weight: 700;">
                                <i class="fa-solid fa-xmark"></i> सर्व काढा
                            </button>
                            <button type="button" class="btn btn-xs btn-warning text-dark" onclick="prepareManualPick()" style="font-size: 10.5px; padding: 2px 7px; font-weight: 700;" title="सर्व अनचेक करून १-२ ग्राहक स्वतः टिक करा">
                                <i class="fa-solid fa-hand-pointer"></i> १-२ स्वतः निवडा
                            </button>
                        </div>
                    </div>

                    <div style="font-size: 10.5px; color: #475569; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                        <span>💡 <strong>मॅन्युअल निवड टीप:</strong> जर तुम्हाला फक्त १ किंवा २ ग्राहक निवडायचे असतील, तर वर <strong>'१-२ स्वतः निवडा'</strong> दाबा किंवा समोर <strong>'फक्त हा १'</strong> वर क्लिक करा.</span>
                        <span id="bcFilteredCountNote" style="font-weight: 600; color: #166534;"></span>
                    </div>

                    <!-- Scrollable Checklist Table -->
                    <div style="max-height: 240px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <table class="table table-sm table-hover" style="margin-bottom: 0; font-size: 11.5px;">
                            <thead style="background: #f1f5f9; position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 34px; text-align: center;">
                                        <input type="checkbox" id="bcSelectAllCheckbox" onchange="toggleSelectAllBc(this.checked)" checked style="accent-color: #16a34a; cursor: pointer;">
                                    </th>
                                    <th>नाव (Name)</th>
                                    <th>WhatsApp मोबाईल</th>
                                    <th>लिंग व गाव</th>
                                    <th>वर्ग व व्यवसाय</th>
                                    <th style="text-align: center;">रेटिंग</th>
                                    <th style="text-align: center; width: 75px;">ॲक्शन</th>
                                </tr>
                            </thead>
                            <tbody id="bcCustomerTableBody">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 18px; color: #64748b;">
                                        <i class="fa-solid fa-circle-notch fa-spin"></i> CRM मधील ग्राहक लोड होत आहेत...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Next Step Button on Tab 1 -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 8px;">
                    <span style="font-size: 11.5px; color: #64748b;">
                        ग्राहक निवडले? आता पुढील पायरीवर जाऊन मेसेज किंवा ऑफर निवडा ➜
                    </span>
                    <button type="button" onclick="switchBcTab(2)" class="btn btn-success" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; font-weight: 800; font-size: 13px; padding: 9px 22px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);">
                        पुढील पायरी: मेसेज व ऑफर निवडा (Next) <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: OFFERS, TEMPLATES & MESSAGE CUSTOMIZATION -->
            <!-- ============================================== -->
            <div id="bcTabContent_2" style="display: none;">

                <!-- 1. READY-MADE OFFER & SCHEME PRESETS (6 CLICKABLE CARDS) -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 12.5px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-wand-magic-sparkles" style="color: #ea580c;"></i> १-क्लिक रेडीमेड ऑफर्स व योजना मेसेज (Click any Offer to apply):
                        </span>
                        <span style="font-size: 11px; color: #64748b;">
                            कोणत्याही कार्डवर क्लिक करताच मेसेज व टेम्पलेट आपोआप भरले जाईल
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                        <!-- Offer 1: Pan & Certificates Special Discount -->
                        <div class="bc-preset-card" id="bcPreset_offer" onclick="applyBcOfferPreset('offer')" style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 12.5px; font-weight: 800; color: #9a3412;">🎁 पॅन व दाखले सवलत ऑफर</span>
                                <span style="font-size: 9.5px; background: #ffedd5; color: #c2410c; padding: 1px 6px; border-radius: 10px; font-weight: 700;">सवलत दर</span>
                            </div>
                            <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.35;">
                                नवीन पॅन कार्ड (२ तासांत), उत्पन्न, डोमिसाईल व सर्व दाखल्यांवर विशेष सवलत ऑफर.
                            </p>
                        </div>

                        <!-- Offer 2: Ladki Bahin Yojana Alert -->
                        <div class="bc-preset-card" id="bcPreset_ladki_bahin" onclick="applyBcOfferPreset('ladki_bahin')" style="background: #ffffff; border: 1.5px solid #fbcfe8; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 12.5px; font-weight: 800; color: #9d174d;">👩 लाडकी बहीण योजना अपडेट</span>
                                <span style="font-size: 9.5px; background: #fce7f3; color: #be185d; padding: 1px 6px; border-radius: 10px; font-weight: 700;">योजना अलर्ट</span>
                            </div>
                            <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.35;">
                                ई-केवायसी, बँक आधार सीडिंग व हप्ता तपासणी त्वरित केंद्रात येऊन करून घ्यावी.
                            </p>
                        </div>

                        <!-- Offer 3: Farmer Schemes (PM Kisan & Namo Shetkari) -->
                        <div class="bc-preset-card" id="bcPreset_farmer" onclick="applyBcOfferPreset('farmer')" style="background: #ffffff; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 12.5px; font-weight: 800; color: #166534;">🌾 शेतकरी सन्मान निधी योजना</span>
                                <span style="font-size: 9.5px; background: #dcfce7; color: #15803d; padding: 1px 6px; border-radius: 10px; font-weight: 700;">शेतकरी</span>
                            </div>
                            <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.35;">
                                PM किसान व नमो शेतकरी योजना ई-केवायसी, जमीन नोंदणी व नवीन अर्ज सुरू.
                            </p>
                        </div>

                        <!-- Offer 4: Ration Card KYC Urgent Notice -->
                        <div class="bc-preset-card" id="bcPreset_ration" onclick="applyBcOfferPreset('ration')" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 12.5px; font-weight: 800; color: #334155;">📋 रेशन कार्ड ई-केवायसी अलर्ट</span>
                                <span style="font-size: 9.5px; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 10px; font-weight: 700;">अंतिम मुदत</span>
                            </div>
                            <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.35;">
                                धान्य बंद होऊ नये म्हणून कुटुंबातील सर्व सदस्यांची ई-केवायसी त्वरित पूर्ण करा.
                            </p>
                        </div>

                        <!-- Offer 5: Student Scholarships & Admissions -->
                        <div class="bc-preset-card" id="bcPreset_student" onclick="applyBcOfferPreset('student')" style="background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 12.5px; font-weight: 800; color: #1e40af;">🎓 विद्यार्थी स्कॉलरशिप व दाखले</span>
                                <span style="font-size: 9.5px; background: #dbeafe; color: #1d4ed8; padding: 1px 6px; border-radius: 10px; font-weight: 700;">विद्यार्थी</span>
                            </div>
                            <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.35;">
                                महाडीबीटी स्कॉलरशिप, जात पडताळणी, उत्पन्न दाखला व प्रवेश अर्ज भरून मिळतील.
                            </p>
                        </div>

                        <!-- Offer 6: Festival Greetings & Kendra Services -->
                        <div class="bc-preset-card" id="bcPreset_festival" onclick="applyBcOfferPreset('festival')" style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 12.5px; font-weight: 800; color: #854d0e;">🪔 सण-उत्सव हार्दिक शुभेच्छा</span>
                                <span style="font-size: 9.5px; background: #fef3c7; color: #b45309; padding: 1px 6px; border-radius: 10px; font-weight: 700;">सण शुभेच्छा</span>
                            </div>
                            <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.35;">
                                आगामी सणाच्या हार्दिक शुभेच्छा! केंद्र सुरू असून सर्व दाखले व सेवा नियमित चालू आहेत.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. MESSAGE MODE & CONTENT CUSTOMIZATION -->
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12.5px; font-weight: 800; color: #0f172a;">
                            २. संदेश प्रकार निवडा (Choose Mode):
                        </span>
                        <span style="font-size: 11px; color: #166534; font-weight: 700;">
                            <i class="fa-solid fa-shield-check"></i> Meta Verified Delivery
                        </span>
                    </div>

                    <!-- Mode Radio Selector -->
                    <div style="display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                        <label style="flex: 1; min-width: 250px; display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1.5px solid #16a34a; border-radius: 8px; background: #f0fdf4; cursor: pointer;">
                            <input type="radio" name="bc_mode" value="template" checked onchange="toggleBcMode('template')" style="margin-top: 3px; accent-color: #16a34a;">
                            <div>
                                <div style="font-weight: 800; font-size: 12.5px; color: #166534;">
                                    ✅ Approved Meta Template (१००% हमखास डिलिव्हरी)
                                </div>
                                <div style="font-size: 11px; color: #15803d; margin-top: 2px;">
                                    २४ तासांची कसलीही अट नाही! कोणत्याही CRM ग्राहकाला थेट मेसेज पोहोचतो. (₹ ०.१२ / मेसेज)
                                </div>
                            </div>
                        </label>

                        <label style="flex: 1; min-width: 250px; display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer;">
                            <input type="radio" name="bc_mode" value="custom" onchange="toggleBcMode('custom')" style="margin-top: 3px; accent-color: #16a34a;">
                            <div>
                                <div style="font-weight: 800; font-size: 12.5px; color: #334155;">
                                    ✍️ Custom Text Message (सानुकूल मोकळा मजकूर)
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    सण शुभेच्छा किंवा थेट मेसेज (फक्त ज्या ग्राहकांशी २४ तासांत व्हॉट्सअॅपवर बोलणे झाले आहे).
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Template Mode Parameters -->
                    <div id="bcTemplateControls">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; margin-bottom: 10px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 3px; display: block;">टेम्पलेट निवडा (Meta Approved):</label>
                                <select id="bc_template_select" class="form-select form-select-sm" onchange="updateBcTemplatePreview()" style="font-size: 12px; font-weight: 700;">
                                    <option value="application_status" selected>मराठी टेम्पलेट ('application_status')</option>
                                    <option value="application_update">इंग्रजी टेम्पलेट ('application_update')</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 3px; display: block;">१. सेवा / योजना / ऑफर शीर्षक:</label>
                                <input type="text" id="bc_param_service" class="form-control form-control-sm" value="पॅन कार्ड व सर्व दाखले विशेष ऑफर" oninput="this.dataset.userEdited='1'; updateBcTemplatePreview();" placeholder="उदा. पॅन कार्ड ऑफर / लाडकी बहीण..." style="font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 3px; display: block;">२. स्थिती / मुख्य संदेश:</label>
                                <input type="text" id="bc_param_status" class="form-control form-control-sm" value="सवलत दर व २ तासांत उपलब्ध" oninput="this.dataset.userEdited='1'; updateBcTemplatePreview();" placeholder="उदा. सवलत दर सुरू आहेत / त्वरित या..." style="font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 3px; display: block;">३. संदर्भ कोड / क्रमांक:</label>
                                <input type="text" id="bc_param_app_no" class="form-control form-control-sm" value="OFFER-50" oninput="this.dataset.userEdited='1'; updateBcTemplatePreview();" placeholder="उदा. OFFER-2026 किंवा केंद्रात या..." style="font-size: 12px;">
                            </div>
                        </div>

                        <!-- Live WhatsApp Bubble Preview -->
                        <div style="background: #f8fafc; border: 1.5px solid #86efac; border-radius: 8px; padding: 12px 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 11px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-mobile-screen"></i> ग्राहकाच्या WhatsApp वर मेसेज असा दिसेल (Live WhatsApp Bubble):
                                </span>
                                <span style="font-size: 10.5px; background: #dcfce7; color: #166534; padding: 1px 7px; border-radius: 4px; font-weight: 700;">
                                    ✓✓ Delivered
                                </span>
                            </div>
                            <div style="background: #ffffff; border-radius: 8px; padding: 10px 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-left: 4px solid #16a34a; font-family: inherit; font-size: 12.5px; line-height: 1.5; color: #0f172a; white-space: pre-wrap;" id="bcTemplateLivePreview"></div>
                        </div>
                    </div>

                    <!-- Custom Text Mode Controls -->
                    <div id="bcCustomControls" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                            <span style="font-size: 11px; font-weight: 700; color: #475569;">
                                सानुकूल मेसेज टाईप करा (किंवा वरील ६ ऑफर्समधून निवडा):
                            </span>
                            <div style="display: flex; gap: 4px;">
                                <span style="font-size: 10.5px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; color: #334155; cursor: pointer; font-weight: 600;" onclick="insertBcTag('{name}')" title="Insert customer name">+ {name}</span>
                                <span style="font-size: 10.5px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; color: #334155; cursor: pointer; font-weight: 600;" onclick="insertBcTag('{center_name}')" title="Insert center name">+ {center_name}</span>
                                <span style="font-size: 10.5px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; color: #334155; cursor: pointer; font-weight: 600;" onclick="insertBcTag('{center_mobile}')" title="Insert phone">+ {center_mobile}</span>
                            </div>
                        </div>
                        <textarea id="bcCustomMessageText" rows="5" class="form-control" style="font-size: 12.5px; line-height: 1.5; font-family: inherit;" placeholder="तुमचा सानुकूल मेसेज येथे टाईप करा... उदा. नमस्कार {name}, आपल्या केंद्रात विशेष सवलत ऑफर सुरू आहे..."></textarea>
                    </div>
                </div>

                <!-- Progress / Status Box -->
                <div id="broadcastStatusBox" style="display: none; padding: 12px 14px; border-radius: 8px; font-size: 12.5px; margin-top: 10px;"></div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
            <button type="button" id="bcFooterBackBtn" class="btn btn-secondary" onclick="handleBcFooterCancelOrBack()" style="font-weight: 700; font-size: 12.5px;">
                रद्द करा (Cancel)
            </button>
            <div style="font-size: 12px; font-weight: 700; color: #1e293b;">
                🎯 <span id="bcFooterSelectedCount">0</span> ग्राहक निवडले | खर्च: ₹ <span id="bcFooterEstimatedCost">0.00</span>
            </div>
            <button type="button" id="btnExecuteBroadcast" onclick="startBroadcast()" class="btn btn-success" style="background: #16a34a; border-color: #16a34a; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; padding: 8px 22px; font-size: 13px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);">
                <i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i>
                <span id="broadcastBtnText">Send Broadcast to 0 CRM Customers</span>
            </button>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function autoFilterCustomers(query) {
    const q = (query || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.erp-table tbody tr');
    const indicator = document.getElementById('custSearchIndicator');

    if (indicator) indicator.style.display = q ? 'inline-block' : 'none';

    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const text = row.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    if (indicator) {
        setTimeout(() => { indicator.style.display = 'none'; }, 300);
    }
}

// ==========================================
// BROADCAST WHATSAPP SUITE (CRM EXCLUSIVE)
// ==========================================
let bcLoadedCustomers = [];
let bcSearchTimeout = null;
let bcActiveTab = 1;

function switchBcTab(tabIndex) {
    bcActiveTab = tabIndex;
    const tab1 = document.getElementById('bcTabContent_1');
    const tab2 = document.getElementById('bcTabContent_2');
    const btn1 = document.getElementById('bcTabBtn_1');
    const btn2 = document.getElementById('bcTabBtn_2');
    const footerBackBtn = document.getElementById('bcFooterBackBtn');

    if (tabIndex === 1) {
        if (tab1) tab1.style.display = 'block';
        if (tab2) tab2.style.display = 'none';
        if (btn1) {
            btn1.style.color = '#16a34a';
            btn1.style.borderBottom = '3px solid #16a34a';
            btn1.style.fontWeight = '800';
        }
        if (btn2) {
            btn2.style.color = '#64748b';
            btn2.style.borderBottom = '3px solid transparent';
            btn2.style.fontWeight = '700';
        }
        if (footerBackBtn) footerBackBtn.textContent = 'रद्द करा (Cancel)';
    } else {
        if (tab1) tab1.style.display = 'none';
        if (tab2) tab2.style.display = 'block';
        if (btn2) {
            btn2.style.color = '#ea580c';
            btn2.style.borderBottom = '3px solid #ea580c';
            btn2.style.fontWeight = '800';
        }
        if (btn1) {
            btn1.style.color = '#64748b';
            btn1.style.borderBottom = '3px solid transparent';
            btn1.style.fontWeight = '700';
        }
        if (footerBackBtn) footerBackBtn.textContent = '⬅ मागे (ग्राहक यादी)';
        updateBcTemplatePreview();
    }
}

function handleBcFooterCancelOrBack() {
    if (bcActiveTab === 2) {
        switchBcTab(1);
    } else {
        closeBroadcastModal();
    }
}

function openBroadcastModal() {
    const m = document.getElementById('broadcastModal');
    if (m) {
        m.style.display = 'flex';
        switchBcTab(1);
        updateBcTemplatePreview();
        fetchBroadcastPreview();
    }
}

function closeBroadcastModal() {
    const m = document.getElementById('broadcastModal');
    if (m) m.style.display = 'none';
}

function debounceBroadcastPreview() {
    clearTimeout(bcSearchTimeout);
    bcSearchTimeout = setTimeout(() => {
        fetchBroadcastPreview();
    }, 350);
}

function toggleBcMode(mode) {
    const tmplDiv = document.getElementById('bcTemplateControls');
    const customDiv = document.getElementById('bcCustomControls');
    if (mode === 'template') {
        if (tmplDiv) tmplDiv.style.display = 'block';
        if (customDiv) customDiv.style.display = 'none';
    } else {
        if (tmplDiv) tmplDiv.style.display = 'none';
        if (customDiv) customDiv.style.display = 'block';
        if (!document.getElementById('bcCustomMessageText').value.trim()) {
            setPresetBcMessage('scheme');
        }
    }
}

function updateBcTemplatePreview() {
    const tmpl = document.getElementById('bc_template_select')?.value || 'application_status';
    const srv = document.getElementById('bc_param_service')?.value.trim() || 'शासकीय सेवा व योजना';
    const st = document.getElementById('bc_param_status')?.value.trim() || 'अर्ज सुरू आहेत';
    const appNo = document.getElementById('bc_param_app_no')?.value.trim() || 'MAHA-ALERT';
    const previewEl = document.getElementById('bcTemplateLivePreview');

    if (previewEl) {
        if (tmpl === 'application_update') {
            previewEl.textContent = `Hello [Customer Name],\nYour application for ${srv} is now ${st}.\nApplication No. : ${appNo}\nThank you!`;
        } else {
            previewEl.textContent = `नमस्कार [ग्राहकाचे नाव],\nतुमच्या ${srv} अर्जाची स्थिती आता ${st} झाली आहे.\nअर्ज क्र. : ${appNo}\nधन्यवाद!`;
        }
    }
}

function fetchBroadcastPreview() {
    const tbody = document.getElementById('bcCustomerTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 16px; color: #64748b;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> CRM मधील ग्राहक फिल्टर होत आहेत...
                </td>
            </tr>
        `;
    }

    const payload = {
        category_id: document.getElementById('bc_filter_category')?.value || '',
        gender: document.getElementById('bc_filter_gender')?.value || '',
        occupation: document.getElementById('bc_filter_occupation')?.value || '',
        min_star: document.getElementById('bc_filter_star')?.value || '',
        city: document.getElementById('bc_filter_city')?.value || '',
        search: document.getElementById('bc_filter_search')?.value || '',
    };

    fetch('{{ route("customers.broadcast_preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            bcLoadedCustomers = res.customers || [];
            
            // Auto-populate tenant defaults if first time or available
            if (res.defaults) {
                const sEl = document.getElementById('bc_param_service');
                const stEl = document.getElementById('bc_param_status');
                const appEl = document.getElementById('bc_param_app_no');
                const customTa = document.getElementById('bcCustomMessageText');
                if (sEl && (!sEl.dataset.userEdited || sEl.value === '')) sEl.value = res.defaults.service;
                if (stEl && (!stEl.dataset.userEdited || stEl.value === '')) stEl.value = res.defaults.status;
                if (appEl && (!appEl.dataset.userEdited || appEl.value === '')) appEl.value = res.defaults.app_no;
                if (customTa && customTa.value === '' && res.defaults.custom_template) customTa.value = res.defaults.custom_template;
                updateBcTemplatePreview();
            }

            renderBcCustomerList(bcLoadedCustomers, res.total_count, res.estimated_cost);
        } else {
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #dc2626; padding: 14px;">${res.error || 'ग्राहकांची यादी आणता आली नाही.'}</td></tr>`;
            }
        }
    })
    .catch(err => {
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #dc2626; padding: 14px;">Server Error: ${err.message}</td></tr>`;
        }
    });
}

function renderBcCustomerList(customers, totalCount, cost) {
    const tbody = document.getElementById('bcCustomerTableBody');
    const totalEl = document.getElementById('bcTotalCount');
    const selectedEl = document.getElementById('bcSelectedCount');
    const costEl = document.getElementById('bcEstimatedCost');
    const btnText = document.getElementById('broadcastBtnText');

    if (totalEl) totalEl.textContent = totalCount;
    if (costEl) costEl.textContent = cost;

    if (!customers || customers.length === 0) {
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 18px; color: #94a3b8; font-style: italic;">
                        ⚠️ निवडलेल्या फिल्टरनुसार CRM मध्ये कोणताही सक्रिय ग्राहक सापडला नाही. कृपया फिल्टर बदला.
                    </td>
                </tr>
            `;
        }
        if (selectedEl) selectedEl.textContent = 0;
        if (btnText) btnText.textContent = 'Send Broadcast (0 Customers)';
        return;
    }

    let rowsHtml = '';
    customers.forEach(c => {
        let stars = '';
        for (let i = 0; i < (c.star_rating || 5); i++) {
            stars += '⭐';
        }

        const isFemale = (c.gender || '').toUpperCase() === 'FEMALE';
        const isMale = (c.gender || '').toUpperCase() === 'MALE';
        const genderBadge = isFemale 
            ? `<span style="font-size: 10px; font-weight: 700; color: #be185d; background: #fce7f3; padding: 1px 6px; border-radius: 4px; border: 1px solid #fbcfe8;">👩 महिला</span>`
            : (isMale ? `<span style="font-size: 10px; font-weight: 700; color: #1d4ed8; background: #dbeafe; padding: 1px 6px; border-radius: 4px; border: 1px solid #bfdbfe;">👨 पुरुष</span>` : `<span style="font-size: 10px; color: #64748b;">${c.gender}</span>`);

        rowsHtml += `
            <tr id="bcRow_${c.id}" class="bc-cust-row" style="cursor: pointer; background: #f0fdf4; transition: background 0.15s ease;" onclick="toggleBcCustomerRow(event, ${c.id})">
                <td style="text-align: center;" onclick="event.stopPropagation()">
                    <input type="checkbox" value="${c.id}" class="bc-cust-checkbox" checked onchange="handleCheckboxChange(${c.id})" style="accent-color: #16a34a; cursor: pointer; width: 15px; height: 15px;">
                </td>
                <td style="font-weight: 700; color: #1e293b;" class="bc-search-target-name">
                    ${c.name}
                </td>
                <td style="font-family: var(--font-mono); color: #166534; font-weight: 700;" class="bc-search-target-mobile">
                    <i class="fa-brands fa-whatsapp"></i> ${c.mobile}
                </td>
                <td style="color: #475569;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        ${genderBadge}
                        <span style="font-size: 11px;">${c.city}</span>
                    </div>
                </td>
                <td style="color: #475569;">
                    <span style="font-size: 10.5px; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-weight: 600;">${c.category}</span>
                    <span style="font-size: 10.5px; color: #64748b;">${c.occupation}</span>
                </td>
                <td style="text-align: center; font-size: 10.5px;">
                    ${stars}
                </td>
                <td style="text-align: center;" onclick="event.stopPropagation()">
                    <button type="button" class="btn btn-xs btn-outline-success" onclick="selectOnlyThisBcCustomer(event, ${c.id})" style="font-size: 10px; padding: 1px 6px; font-weight: 700; border-radius: 4px;" title="फक्त हा १ ग्राहक निवडा">
                        फक्त हा १
                    </button>
                </td>
            </tr>
        `;
    });

    if (tbody) tbody.innerHTML = rowsHtml;
    updateBcSelectedCount();
}

function handleCheckboxChange(id) {
    const cb = document.querySelector(`.bc-cust-checkbox[value="${id}"]`);
    const row = document.getElementById(`bcRow_${id}`);
    if (cb && row) {
        row.style.background = cb.checked ? '#f0fdf4' : 'transparent';
    }
    updateBcSelectedCount();
}

function toggleBcCustomerRow(e, id) {
    const cb = document.querySelector(`.bc-cust-checkbox[value="${id}"]`);
    if (cb && e.target !== cb) {
        cb.checked = !cb.checked;
        handleCheckboxChange(id);
    }
}

function selectOnlyThisBcCustomer(e, id) {
    if (e) e.stopPropagation();
    // Uncheck all
    document.querySelectorAll('.bc-cust-checkbox').forEach(cb => {
        cb.checked = false;
        const row = document.getElementById(`bcRow_${cb.value}`);
        if (row) row.style.background = 'transparent';
    });
    // Check only this one
    const targetCb = document.querySelector(`.bc-cust-checkbox[value="${id}"]`);
    const targetRow = document.getElementById(`bcRow_${id}`);
    if (targetCb) targetCb.checked = true;
    if (targetRow) targetRow.style.background = '#f0fdf4';

    const mainCb = document.getElementById('bcSelectAllCheckbox');
    if (mainCb) mainCb.checked = false;

    updateBcSelectedCount();
}

function prepareManualPick() {
    toggleSelectAllBc(false);
    const note = document.getElementById('bcFilteredCountNote');
    if (note) {
        note.innerHTML = `<span style="color: #b45309; background: #fef3c7; padding: 2px 8px; border-radius: 4px;">🎯 सर्व अनचेक केले! आता खाली तुम्हाला हवे ते १ किंवा २ ग्राहक टिक करा.</span>`;
    }
    const searchInput = document.getElementById('bcTableSearch');
    if (searchInput) searchInput.focus();
}

function filterBcTableClientSide() {
    const term = (document.getElementById('bcTableSearch')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.bc-cust-row');
    let visibleCount = 0;
    rows.forEach(r => {
        const name = (r.querySelector('.bc-search-target-name')?.textContent || '').toLowerCase();
        const mobile = (r.querySelector('.bc-search-target-mobile')?.textContent || '').toLowerCase();
        if (!term || name.includes(term) || mobile.includes(term)) {
            r.style.display = '';
            visibleCount++;
        } else {
            r.style.display = 'none';
        }
    });

    const note = document.getElementById('bcFilteredCountNote');
    if (note) {
        note.textContent = term ? `शोध निकाल: ${visibleCount} ग्राहक आढळले` : '';
    }
}

function toggleSelectAllBc(select) {
    const cbs = document.querySelectorAll('.bc-cust-checkbox');
    cbs.forEach(cb => {
        cb.checked = select;
        const row = document.getElementById(`bcRow_${cb.value}`);
        if (row) row.style.background = select ? '#f0fdf4' : 'transparent';
    });
    const mainCb = document.getElementById('bcSelectAllCheckbox');
    if (mainCb) mainCb.checked = select;
    updateBcSelectedCount();
}

function updateBcSelectedCount() {
    const checked = document.querySelectorAll('.bc-cust-checkbox:checked');
    const count = checked.length;
    const selectedEl = document.getElementById('bcSelectedCount');
    const costEl = document.getElementById('bcEstimatedCost');
    const btnText = document.getElementById('broadcastBtnText');
    const tabBadge = document.getElementById('bcTabBadge');
    const footerCount = document.getElementById('bcFooterSelectedCount');
    const footerCost = document.getElementById('bcFooterEstimatedCost');

    const formattedCost = (count * 0.12).toFixed(2);
    if (selectedEl) selectedEl.textContent = count;
    if (costEl) costEl.textContent = formattedCost;
    if (tabBadge) tabBadge.textContent = `${count} ग्राहक`;
    if (footerCount) footerCount.textContent = count;
    if (footerCost) footerCost.textContent = formattedCost;
    if (btnText) btnText.textContent = `Send Broadcast to ${count} CRM Customers`;
}

function insertBcTag(tag) {
    const ta = document.getElementById('bcCustomMessageText');
    if (!ta) return;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const text = ta.value;
    ta.value = text.substring(0, start) + tag + text.substring(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + tag.length;
}

function applyBcOfferPreset(key) {
    // 1. Highlight clicked card
    document.querySelectorAll('.bc-preset-card').forEach(c => {
        c.style.border = '1.5px solid #fed7aa';
        c.style.background = '#ffffff';
        c.style.boxShadow = 'none';
    });
    const targetCard = document.getElementById(`bcPreset_${key}`);
    if (targetCard) {
        targetCard.style.border = '2px solid #16a34a';
        targetCard.style.background = '#f0fdf4';
        targetCard.style.boxShadow = '0 4px 12px rgba(22, 163, 74, 0.25)';
    }

    const sEl = document.getElementById('bc_param_service');
    const stEl = document.getElementById('bc_param_status');
    const appEl = document.getElementById('bc_param_app_no');
    const ta = document.getElementById('bcCustomMessageText');

    if (key === 'offer') {
        if (sEl) sEl.value = 'पॅन कार्ड व सर्व दाखले विशेष ऑफर';
        if (stEl) stEl.value = 'सवलत दर व २ तासांत उपलब्ध';
        if (appEl) appEl.value = 'OFFER-50';
        if (ta) ta.value = "🎉 *{center_name} - विशेष सवलत ऑफर!*\n\nनमस्कार *{name}*,\nआमच्या केंद्रावर नवीन पॅन कार्ड (२ तासांत), उत्पन्न दाखला, डोमिसाईल व सर्व शासकीय दाखल्यांवर विशेष सवलत ऑफर सुरू आहे! मर्यादित काळासाठी लाभ घ्या. आजच केंद्रास भेट द्या किंवा संपर्क करा.\n\n📞 संपर्क: {center_mobile}\n_{center_name}_";
    } else if (key === 'ladki_bahin') {
        if (sEl) sEl.value = 'मुख्यमंत्री माझी लाडकी बहीण योजना';
        if (stEl) stEl.value = 'ई-केवायसी व हप्ता तपासणी सुरू';
        if (appEl) appEl.value = 'LADKI-BAHIN';
        if (ta) ta.value = "👩 *{center_name} - लाडकी बहीण योजना महत्त्वाची सूचना*\n\nनमस्कार *{name}* ताई,\nमाझी लाडकी बहीण योजनेची आधार ई-केवायसी, बँक खाते आधार सीडिंग व हप्ता तपासणी आपल्या केंद्रावर सुरू आहे. लाभ सुरळीत राहण्यासाठी त्वरित येऊन पडताळणी करून घ्यावी.\n\n📞 संपर्क: {center_mobile}\n_{center_name}_";
    } else if (key === 'farmer') {
        if (sEl) sEl.value = 'PM किसान व नमो शेतकरी योजना';
        if (stEl) stEl.value = 'ई-केवायसी व नवीन नोंदणी सुरू';
        if (appEl) appEl.value = 'KISAN-2026';
        if (ta) ta.value = "🌾 *{center_name} - शेतकरी बंधूंसाठी महत्त्वाची सूचना*\n\nनमस्कार *{name}*,\nPM किसान सन्मान निधी व नमो शेतकरी महासन्मान निधीची ई-केवायसी, जमीन नोंदणी व नवीन अर्ज आपल्या केंद्रावर भरून मिळतील. त्वरित भेट द्या.\n\n📞 संपर्क: {center_mobile}\n_{center_name}_";
    } else if (key === 'ration') {
        if (sEl) sEl.value = 'रेशन कार्ड ई-केवायसी व नाव बदल';
        if (stEl) stEl.value = 'अंतिम मुदत सुरू आहे';
        if (appEl) appEl.value = 'RATION-KYC';
        if (ta) ta.value = "📋 *{center_name} - रेशन कार्ड ई-केवायसी अलर्ट*\n\nनमस्कार *{name}*,\nसर्व रेशन कार्ड धारकांसाठी कुटुंबातील सर्व सदस्यांची ई-केवायसी करणे बंधनकारक आहे. धान्य बंद होऊ नये म्हणून आजच केंद्रात येऊन प्रक्रिया पूर्ण करा.\n\n📞 संपर्क: {center_mobile}\n_{center_name}_";
    } else if (key === 'student') {
        if (sEl) sEl.value = 'महाडीबीटी स्कॉलरशिप व दाखले';
        if (stEl) stEl.value = 'नवीन नोंदणी व अर्ज सुरू';
        if (appEl) appEl.value = 'SCHOLARSHIP';
        if (ta) ta.value = "🎓 *{center_name} - विद्यार्थी शिष्यवृत्ती सूचना*\n\nनमस्कार *{name}*,\nमहाडीबीटी स्कॉलरशिप, जात पडताळणी, उत्पन्न दाखला व विविध प्रवेश अर्ज आपल्या केंद्रावर भरून मिळतील. आजच अर्ज करा.\n\n📞 संपर्क: {center_mobile}\n_{center_name}_";
    } else if (key === 'festival') {
        if (sEl) sEl.value = 'सण-उत्सव हार्दिक शुभेच्छा';
        if (stEl) stEl.value = 'सर्व शासकीय सेवा चालू आहेत';
        if (appEl) appEl.value = 'GREETINGS';
        if (ta) ta.value = "🪔 *{center_name} कडून हार्दिक शुभेच्छा!*\n\nनमस्कार *{name}*,\nतुम्हाला व तुमच्या परिवाराला आगामी सणाच्या मनःपूर्वक हार्दिक शुभेच्छा! आपले महा ई-सेवा केंद्र नेहमी आपल्या सेवेसाठी तत्पर आहे.\n\n📞 संपर्क: {center_mobile}\n_{center_name}_";
    }

    updateBcTemplatePreview();
}

function setPresetBcMessage(type) {
    applyBcOfferPreset(type === 'scheme' ? 'ladki_bahin' : (type === 'diwali' ? 'festival' : 'offer'));
}

function startBroadcast() {
    const checked = document.querySelectorAll('.bc-cust-checkbox:checked');
    const customerIds = Array.from(checked).map(cb => parseInt(cb.value)).filter(id => !isNaN(id));

    if (customerIds.length === 0) {
        alert('कृपया ब्रॉडकास्ट करण्यासाठी यादीतील किमान एक ग्राहक निवडा!');
        return;
    }

    const mode = document.querySelector('input[name="bc_mode"]:checked')?.value || 'template';
    const statusBox = document.getElementById('broadcastStatusBox');
    const btn = document.getElementById('btnExecuteBroadcast');
    const btnText = document.getElementById('broadcastBtnText');

    let payload = {
        mode: mode,
        customer_ids: customerIds,
    };

    if (mode === 'template') {
        payload.template_name = document.getElementById('bc_template_select')?.value || 'application_status';
        payload.param_service = document.getElementById('bc_param_service')?.value.trim() || 'शासकीय सेवा व योजना';
        payload.param_status = document.getElementById('bc_param_status')?.value.trim() || 'अर्ज सुरू आहेत';
        payload.param_app_no = document.getElementById('bc_param_app_no')?.value.trim() || 'MAHA-ALERT';
    } else {
        const customMsg = document.getElementById('bcCustomMessageText')?.value.trim();
        if (!customMsg) {
            alert('कृपया सानुकूल मेसेज मजकूर टाईप करा.');
            return;
        }
        payload.message = customMsg;
    }

    if (!confirm(`तुम्ही निवडलेल्या ${customerIds.length} CRM ग्राहकांना थेट WhatsApp मेसेज पाठवणार आहात.\n\nखात्री आहे का? सुरु करायचे?`)) {
        return;
    }

    btn.disabled = true;
    btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ब्रॉडकास्टिंग सुरू आहे...';
    statusBox.style.display = 'block';
    statusBox.style.background = '#e0f2fe';
    statusBox.style.border = '1.5px solid #bae6fd';
    statusBox.style.color = '#0369a1';
    statusBox.innerHTML = `
        <div style="font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-paper-plane fa-bounce"></i>
            <span>Meta WhatsApp Cloud API द्वारे ${customerIds.length} ग्राहकांना संदेश पाठवले जात आहेत... कृपया थांबा.</span>
        </div>
    `;

    fetch('{{ route("customers.broadcast_whatsapp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btnText.innerHTML = `<i class="fa-brands fa-whatsapp"></i> Send Broadcast to ${customerIds.length} CRM Customers`;

        if (res.success && res.sent_count > 0) {
            statusBox.style.background = '#dcfce7';
            statusBox.style.border = '1.5px solid #86efac';
            statusBox.style.color = '#15803d';
            statusBox.innerHTML = `
                <div style="font-weight: 800; font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-check"></i> ${res.message}
                </div>
                <div style="margin-top: 5px; font-size: 12px;">
                    एकूण निवडलेले: <strong>${res.total}</strong> | यशस्वीरीत्या पाठवले: <strong>${res.sent_count}</strong> | अयशस्वी: <strong>${res.failed_count}</strong>
                </div>
            `;
        } else {
            statusBox.style.background = '#fee2e2';
            statusBox.style.border = '1.5px solid #fca5a5';
            statusBox.style.color = '#991b1b';
            let errHtml = `
                <div style="font-weight: 800; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> ${res.message || 'ब्रॉडकास्ट पाठवण्यात अडचण आली.'}
                </div>
            `;
            if (res.errors && res.errors.length) {
                errHtml += `<ul style="margin: 6px 0 0 16px; padding: 0; font-size: 11.5px; font-family: var(--font-mono);">`;
                res.errors.forEach(e => errHtml += `<li>${e}</li>`);
                errHtml += `</ul>`;
            } else if (res.error) {
                errHtml += `<div style="margin-top: 4px; font-size: 12px;">${res.error}</div>`;
            }
            statusBox.innerHTML = errHtml;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnText.innerHTML = `<i class="fa-brands fa-whatsapp"></i> Send Broadcast to ${customerIds.length} CRM Customers`;
        statusBox.style.background = '#fee2e2';
        statusBox.style.border = '1.5px solid #fca5a5';
        statusBox.style.color = '#991b1b';
        statusBox.innerHTML = `<strong>Server error:</strong> ${err.message || 'Could not connect.'}`;
    });
}
function exportFilteredCustomers(e) {
    if (e) e.preventDefault();
    const form = document.getElementById('customerFilterForm');
    const params = new URLSearchParams(form ? new FormData(form) : {});
    params.set('export', '1');
    window.location.href = "{{ route('reports.customers') }}?" + params.toString();
}

// Scroll table left / right via buttons
function scrollCustomersTable(px) {
    const wrap = document.querySelector('.table-responsive');
    if (wrap) {
        wrap.scrollBy({ left: px, behavior: 'smooth' });
    }
}

// =========================================================================
// SYNCHRONIZED TOP & FLOATING HORIZONTAL SCROLLBARS FOR CUSTOMERS CRM
// =========================================================================
(function initCustomerHorizontalScrollbars() {
    const tableWrap = document.querySelector('.table-responsive');
    const table = document.getElementById('customer-table');
    const topScroll = document.getElementById('customerTopScrollbar');
    const topInner = document.getElementById('customerTopScrollbarInner');
    const floatScroll = document.getElementById('customerFloatingScrollbar');
    const floatInner = document.getElementById('customerFloatingScrollbarInner');

    if (!tableWrap || !table) return;

    function syncScrollWidths() {
        const scrollW = table.scrollWidth;
        const clientW = tableWrap.clientWidth;
        const needsScroll = scrollW > clientW + 5;

        if (topScroll && topInner) {
            topInner.style.width = scrollW + 'px';
            topScroll.style.display = needsScroll ? 'block' : 'none';
        }
        if (floatScroll && floatInner) {
            floatInner.style.width = scrollW + 'px';
        }
        checkFloatVisibility();
    }

    function checkFloatVisibility() {
        if (!floatScroll || !tableWrap) return;
        const scrollW = table.scrollWidth;
        const clientW = tableWrap.clientWidth;
        const needsScroll = scrollW > clientW + 5;

        if (!needsScroll) {
            floatScroll.style.display = 'none';
            return;
        }

        const rect = tableWrap.getBoundingClientRect();
        const isBottomVisible = (rect.bottom <= window.innerHeight);
        const isTopAboveScreen = (rect.top < window.innerHeight);

        if (isTopAboveScreen && !isBottomVisible) {
            floatScroll.style.display = 'block';
            const sidebar = document.querySelector('.erp-sidebar');
            const sidebarW = sidebar ? sidebar.offsetWidth : 240;
            floatScroll.style.left = sidebarW + 'px';
        } else {
            floatScroll.style.display = 'none';
        }
    }

    let isSyncing = false;
    function syncScroll(source, targets) {
        if (isSyncing) return;
        isSyncing = true;
        targets.forEach(t => {
            if (t && t !== source) t.scrollLeft = source.scrollLeft;
        });
        setTimeout(() => { isSyncing = false; }, 10);
    }

    tableWrap.addEventListener('scroll', () => {
        syncScroll(tableWrap, [topScroll, floatScroll]);
    });
    if (topScroll) {
        topScroll.addEventListener('scroll', () => {
            syncScroll(topScroll, [tableWrap, floatScroll]);
        });
    }
    if (floatScroll) {
        floatScroll.addEventListener('scroll', () => {
            syncScroll(floatScroll, [tableWrap, topScroll]);
        });
    }

    window.addEventListener('scroll', checkFloatVisibility);
    window.addEventListener('resize', syncScrollWidths);
    setTimeout(syncScrollWidths, 200);
    setTimeout(syncScrollWidths, 800);
})();

// =========================================================================
// INSTANT HOVER PREVIEW TOOLTIP FOR COMPRESSED TABLE COLUMNS
// =========================================================================
(function initErpHoverTooltip() {
    let tooltip = document.getElementById('erpGlobalHoverTooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'erpGlobalHoverTooltip';
        tooltip.innerHTML = '<div class="tooltip-title"></div><div class="tooltip-body"></div>';
        document.body.appendChild(tooltip);
    }

    const titleEl = tooltip.querySelector('.tooltip-title');
    const bodyEl = tooltip.querySelector('.tooltip-body');

    document.addEventListener('mouseover', function(e) {
        const target = e.target.closest('.cell-hover-preview, [data-hover-text]');
        if (!target) return;

        const text = (target.getAttribute('data-hover-text') || target.innerText || '').trim();
        if (!text || text === '-' || text === 'None' || text === 'No Customer') return;

        const title = target.getAttribute('data-hover-title') || 'Information';
        titleEl.innerHTML = `<i class="fa-solid fa-circle-info"></i> ${title}`;
        bodyEl.textContent = text;

        tooltip.classList.add('visible');

        const rect = target.getBoundingClientRect();
        let top = rect.bottom + 8;
        let left = rect.left;

        const tipRect = tooltip.getBoundingClientRect();
        if (left + tipRect.width > window.innerWidth - 16) {
            left = window.innerWidth - tipRect.width - 16;
        }
        if (left < 16) left = 16;

        if (top + tipRect.height > window.innerHeight - 16) {
            top = Math.max(16, rect.top - tipRect.height - 8);
        }

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
    });

    document.addEventListener('mouseout', function(e) {
        const target = e.target.closest('.cell-hover-preview, [data-hover-text]');
        if (target) {
            tooltip.classList.remove('visible');
        }
    });
})();
</script>
@endpush
