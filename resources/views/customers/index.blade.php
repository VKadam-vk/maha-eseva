@extends('layouts.app')

@section('title', 'Customers CRM')

@section('content')
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
                           placeholder="Type customer name or mobile (auto-searches)..." 
                           value="{{ request('search') }}"
                           oninput="autoFilterCustomers(this.value)"
                           autocomplete="off"
                           style="padding-right: 32px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 13px;"></i>
                </div>
            </div>

            @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
                <div style="min-width: 180px;">
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Branch</label>
                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Category</label>
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
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
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Customer (Name, Mobile, Code)</th>
                    <th>Gender</th>
                    <th>Category</th>
                    <th>Applications</th>
                    <th>Total Spent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td style="padding: 10px 14px;">
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
                                @if($c->occupation)
                                    <span style="background: #f1f5f9; padding: 1px 6px; border-radius: 3px; font-weight: 600; color: #475569; font-size: 10.5px;">
                                        {{ strtoupper($c->occupation) }}
                                    </span>
                                @endif
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
                        <td><span class="badge badge-hold" style="font-size: 10px;">{{ $c->gender }}</span></td>
                        <td>{{ $c->category?->name ?? 'Regular' }}</td>
                        <td><span class="badge badge-process">{{ $c->applications_count }} Apps</span></td>
                        <td><strong>₹{{ number_format($c->total_paid, 2) }}</strong></td>
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
                        Filter by Category
                    </label>
                </div>
                <div id="categoryFilterDiv" style="display: none; margin-top: 8px;">
                    <select id="broadcast_category_id" class="form-select" style="font-size: 12px;">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
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
let custSearchTimer = null;

function autoFilterCustomers(query) {
    const q = (query || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.erp-table tbody tr');
    const indicator = document.getElementById('custSearchIndicator');

    if (indicator) indicator.style.display = 'inline-block';

    let visibleCount = 0;
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const text = row.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    clearTimeout(custSearchTimer);
    custSearchTimer = setTimeout(() => {
        const form = document.getElementById('customerFilterForm');
        if (form) {
            form.submit();
        }
    }, 600);
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
    const categoryId = document.getElementById('broadcast_category_id') ? document.getElementById('broadcast_category_id').value : '';
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
            category_id: categoryId
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
</script>
@endpush
