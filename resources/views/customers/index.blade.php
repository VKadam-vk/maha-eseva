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
        @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isBusinessOwner())
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

<!-- Broadcast WhatsApp Modal (Owner Only) -->
<div id="broadcastModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 620px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: popIn 0.2s ease;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-brands fa-whatsapp" style="font-size: 22px;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800;">WhatsApp Broadcast Campaign</h3>
                    <p style="margin: 2px 0 0 0; font-size: 11.5px; opacity: 0.9;">Send bulk festival greetings, offers, or government scheme alerts</p>
                </div>
            </div>
            <button type="button" onclick="closeBroadcastModal()" style="background: none; border: none; color: #ffffff; font-size: 20px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding: 20px; max-height: calc(85vh - 130px); overflow-y: auto;">
            <!-- Audience Selection -->
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Select Target Audience:</label>
                <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                        <input type="radio" name="broadcast_audience" value="all" checked onchange="toggleAudienceOptions()" style="accent-color: #16a34a;">
                        <strong>All Customers</strong> ({{ $customers->total() }} in Directory)
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                        <input type="radio" name="broadcast_audience" value="filtered" onchange="toggleAudienceOptions()" style="accent-color: #16a34a;">
                        Filter by Occupation
                    </label>
                </div>
                <div id="categoryFilterDiv" style="display: none; margin-top: 8px;">
                    <select id="broadcast_occupation" class="form-select" style="font-size: 12px;">
                        <option value="">-- All Occupations --</option>
                        @foreach($occupations as $occ)
                            <option value="{{ $occ }}">{{ $occ }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Quick Template Presets -->
            <div style="margin-bottom: 14px;">
                <label style="font-size: 11.5px; font-weight: 700; color: #475569; margin-bottom: 4px; display: block;">Quick Message Presets:</label>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm" onclick="setPresetMessage('diwali')" style="font-size: 11px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                        🪔 सण/उत्सव शुभेच्छा
                    </button>
                    <button type="button" class="btn btn-sm" onclick="setPresetMessage('scheme')" style="font-size: 11px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                        🏛️ सरकारी योजना / लाडकी बहीण
                    </button>
                    <button type="button" class="btn btn-sm" onclick="setPresetMessage('notice')" style="font-size: 11px; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                        📢 महत्त्वाची केंद्र सूचना
                    </button>
                </div>
            </div>

            <!-- Message Textarea -->
            <div style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155;">Message Content (Marathi / English):</label>
                    <div style="display: flex; gap: 4px;">
                        <span style="font-size: 10.5px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569; cursor: pointer;" onclick="insertTag('{name}')" title="Insert customer name">+ {name}</span>
                        <span style="font-size: 10.5px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569; cursor: pointer;" onclick="insertTag('{center_name}')" title="Insert center name">+ {center_name}</span>
                    </div>
                </div>
                <textarea id="broadcastMessageText" rows="6" class="form-control" style="font-size: 13px; line-height: 1.5; font-family: inherit;" placeholder="Type your WhatsApp message here... You can use {name} for personalized greeting."></textarea>
            </div>

            <!-- Progress / Status Box -->
            <div id="broadcastStatusBox" style="display: none; padding: 12px; border-radius: 6px; font-size: 12px; margin-top: 10px;"></div>
        </div>

        <!-- Footer -->
        <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-secondary" onclick="closeBroadcastModal()">Cancel</button>
            <button type="button" id="btnExecuteBroadcast" onclick="startBroadcast()" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-paper-plane"></i> <span id="broadcastBtnText">Send Broadcast Now</span>
            </button>
        </div>
    </div>
</div>
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
// BROADCAST WHATSAPP MODAL
// ==========================================
function openBroadcastModal() {
    const m = document.getElementById('broadcastModal');
    if (m) m.style.display = 'flex';
}

function closeBroadcastModal() {
    const m = document.getElementById('broadcastModal');
    if (m) m.style.display = 'none';
}

function toggleAudienceOptions() {
    const isFiltered = document.querySelector('input[name="broadcast_audience"]:checked').value === 'filtered';
    const div = document.getElementById('categoryFilterDiv');
    if (div) div.style.display = isFiltered ? 'block' : 'none';
}

function insertTag(tag) {
    const ta = document.getElementById('broadcastMessageText');
    if (!ta) return;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const text = ta.value;
    ta.value = text.substring(0, start) + tag + text.substring(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + tag.length;
}

function setPresetMessage(type) {
    const ta = document.getElementById('broadcastMessageText');
    if (!ta) return;

    if (type === 'diwali') {
        ta.value = "🪔 *सस्नेह नमस्कार {name},*\n\n"
                 + "दिवाळीच्या व नवीन वर्षाच्या आपणास व आपल्या परिवारास महा ई-सेवा केंद्रामार्फत मनःपूर्वक हार्दिक शुभेच्छा!\n\n"
                 + "आपल्या केंद्रावर सर्व शासकीय दाखले, पॅन कार्ड, आधार कामे नियमितपणे सुरू आहेत.\n\n"
                 + "धन्यवाद!\n"
                 + "_{center_name}_";
    } else if (type === 'scheme') {
        ta.value = "📢 *महत्त्वाची सूचना - {name}*\n\n"
                 + "शासकीय योजनांचे अर्ज (मुख्यमंत्री लाडकी बहीण योजना / रेशन कार्ड ई-केवायसी / शेतकरी सन्मान निधी) भरण्याची प्रक्रिया आपल्या केंद्रात सुरू झाली आहे.\n\n"
                 + "लागणारी कागदपत्रे घेऊन केंद्रावर त्वरित संपर्क साधावा.\n\n"
                 + "केंद्राचे नाव: *{center_name}*";
    } else if (type === 'notice') {
        ta.value = "📋 *महा ई-सेवा केंद्र सूचना - {name}*\n\n"
                 + "आपल्या केंद्रावर आता नवीन सेवा (पासपोर्ट अर्ज, मतदान कार्ड दुरुस्ती, इन्कम टॅक्स रिटर्न) उपलब्ध करून देण्यात आल्या आहेत.\n\n"
                 + "अधिक माहितीसाठी केंद्रास भेट द्या.\n"
                 + "_{center_name}_";
    }
}

function startBroadcast() {
    const msg = document.getElementById('broadcastMessageText').value.trim();
    const audience = document.querySelector('input[name="broadcast_audience"]:checked').value;
    const occupation = document.getElementById('broadcast_occupation') ? document.getElementById('broadcast_occupation').value : '';
    const statusBox = document.getElementById('broadcastStatusBox');
    const btn = document.getElementById('btnExecuteBroadcast');
    const btnText = document.getElementById('broadcastBtnText');

    if (!msg) {
        alert('Please enter a message to broadcast.');
        return;
    }

    if (!confirm('Are you sure you want to broadcast this WhatsApp message to customers?')) {
        return;
    }

    btn.disabled = true;
    btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Broadcasting...';
    statusBox.style.display = 'block';
    statusBox.style.background = '#e0f2fe';
    statusBox.style.border = '1px solid #bae6fd';
    statusBox.style.color = '#0369a1';
    statusBox.innerHTML = '<i class="fa-solid fa-paper-plane fa-bounce"></i> Broadcasting message in background via Meta WhatsApp Cloud API... Please wait.';

    fetch('{{ route("customers.broadcast_whatsapp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message: msg,
            audience: audience,
            occupation: occupation
        })
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        btnText.innerHTML = 'Send Broadcast Now';

        if (res.success && res.sent_count > 0) {
            statusBox.style.background = '#dcfce7';
            statusBox.style.border = '1.5px solid #86efac';
            statusBox.style.color = '#15803d';
            statusBox.innerHTML = `
                <div style="font-weight: 800; font-size: 13px;"><i class="fa-solid fa-circle-check"></i> ${res.message}</div>
                <div style="margin-top: 3px; font-size: 11.5px;">Total Processed: <strong>${res.total}</strong> | Successfully Delivered: <strong>${res.sent_count}</strong></div>
            `;
        } else {
            statusBox.style.background = '#fee2e2';
            statusBox.style.border = '1.5px solid #fca5a5';
            statusBox.style.color = '#991b1b';
            let errHtml = `<div style="font-weight: 800; font-size: 13px;"><i class="fa-solid fa-triangle-exclamation"></i> ${res.message || 'Broadcast Failed'}</div>`;
            if (res.errors && res.errors.length) {
                errHtml += `<ul style="margin: 6px 0 0 16px; padding: 0; font-size: 11.5px; font-family: var(--font-mono);">`;
                res.errors.forEach(e => errHtml += `<li>${e}</li>`);
                errHtml += `</ul>`;
            } else if (res.error) {
                errHtml += `<div style="margin-top: 4px; font-size: 11.5px;">${res.error}</div>`;
            }
            errHtml += `<div style="font-size: 11px; margin-top: 6px; color: #7f1d1d;">💡 <em>टीप: Meta चा Test Number फक्त Developer पोर्टलवर OTP ने verify केलेल्या नंबरवरच मेसेज पाठवू शकतो. इतर कोणत्याही नवीन नंबरसाठी किंवा साध्या टेक्स्टसाठी Meta कडून 24-hr विंडो किंवा Template आवश्यक असते.</em></div>`;
            statusBox.innerHTML = errHtml;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnText.innerHTML = 'Send Broadcast Now';
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
