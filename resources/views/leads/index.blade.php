@extends('layouts.app')

@section('title', 'Website Enquiries & Leads CRM')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Website Enquiries & Lead Pipeline</h1>
        <p class="page-subtitle">Capture online citizen inquiries, manage qualification stages and convert into live applications</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openLeadModal()">
        <i class="fa-solid fa-plus"></i> Record New Lead
    </button>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('leads.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="min-width: 170px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Pipeline Status</label>
                <select name="status" class="form-select">
                    <option value="">All Pipeline Stages</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 150px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Lead Source</label>
                <select name="source" class="form-select">
                    <option value="">All Sources</option>
                    <option value="WEBSITE" {{ request('source') == 'WEBSITE' ? 'selected' : '' }}>WEBSITE</option>
                    <option value="WALK_IN" {{ request('source') == 'WALK_IN' ? 'selected' : '' }}>WALK_IN</option>
                    <option value="PHONE" {{ request('source') == 'PHONE' ? 'selected' : '' }}>PHONE</option>
                    <option value="WHATSAPP" {{ request('source') == 'WHATSAPP' ? 'selected' : '' }}>WHATSAPP</option>
                    <option value="REFERRAL" {{ request('source') == 'REFERRAL' ? 'selected' : '' }}>REFERRAL</option>
                </select>
            </div>

            <div style="min-width: 160px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Next Follow-up</label>
                <select name="followup" class="form-select">
                    <option value="">All Follow-ups</option>
                    <option value="today" {{ request('followup') == 'today' ? 'selected' : '' }}>Due Today</option>
                    <option value="upcoming" {{ request('followup') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="overdue" {{ request('followup') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('leads.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Lead Name</th>
                    <th>Mobile</th>
                    <th style="min-width: 220px;">Requested Service</th>
                    <th style="min-width: 215px;">Next Follow-up</th>
                    <th>Source</th>
                    <th>Enquiry Message</th>
                    <th>Assigned Staff</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    @php
                        $mainName = $lead->service?->main_service_name ?? ($lead->sub_service ? 'Service' : 'General Inquiry');
                        $subName = $lead->sub_service ?: ($lead->service?->sub_service_name ?? null);
                        $serviceDocs = $lead->service?->required_documents_json ?? [];
                        $serviceTitle = $mainName . ($subName ? ' - ' . $subName : '');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $lead->name }}</strong>
                            @if($lead->email)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $lead->email }}</div>
                            @endif
                        </td>
                        <td><strong style="font-family: var(--font-mono);">{{ $lead->mobile }}</strong></td>
                        <td>
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
                                <div>
                                    @if($lead->service || $lead->sub_service)
                                        <div style="font-weight: 700; color: #1e293b; font-size: 13px;">
                                            {{ $mainName }}
                                        </div>
                                        @if($subName)
                                            <div style="font-size: 11px; color: #2563eb; font-weight: 600; text-transform: uppercase; margin-top: 2px;">
                                                <i class="fa-solid fa-angle-right" style="font-size: 9px; opacity: 0.7;"></i> {{ $subName }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: #64748b; font-style: italic; font-size: 12.5px;">General Inquiry</span>
                                    @endif
                                </div>

                                @if($lead->service_id || count($serviceDocs) > 0)
                                    <button type="button" 
                                            class="btn btn-sm" 
                                            style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 6px; padding: 2px 7px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; flex-shrink: 0;"
                                            title="View Required Documents"
                                            onclick='openDocListModal({{ json_encode($serviceTitle) }}, {{ json_encode($serviceDocs) }})'>
                                        <i class="fa-solid fa-eye" style="color: #4f46e5;"></i> Docs
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="lead-followup-box" style="min-width: 205px;">
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <input type="date" 
                                           id="followup-date-{{ $lead->id }}"
                                           class="form-control form-control-sm" 
                                           value="{{ $lead->next_follow_up_date ? $lead->next_follow_up_date->format('Y-m-d') : '' }}"
                                           style="font-size: 11px; padding: 2px 5px; height: 28px; width: 112px; border-radius: 4px; border: 1px solid #cbd5e1;"
                                           title="Change Next Follow-up Date"
                                           onchange="saveFollowUp({{ $lead->id }})">
                                    <input type="time" 
                                           id="followup-time-{{ $lead->id }}"
                                           class="form-control form-control-sm" 
                                           value="{{ $lead->next_follow_up_time ?? '' }}"
                                           style="font-size: 11px; padding: 2px 5px; height: 28px; width: 85px; border-radius: 4px; border: 1px solid #cbd5e1;"
                                           title="Change Follow-up Time"
                                           onchange="saveFollowUp({{ $lead->id }})">
                                </div>
                                <div id="followup-badge-{{ $lead->id }}" style="margin-top: 3px; font-size: 10.5px; min-height: 16px;">
                                    @if($lead->next_follow_up_date)
                                        @php
                                            $isToday = $lead->next_follow_up_date->isToday();
                                            $isPast = $lead->next_follow_up_date->isPast() && !$isToday;
                                            $badgeClass = $isToday ? 'badge-warning' : ($isPast ? 'badge-danger' : 'badge-info');
                                            $formattedTime = '';
                                            if ($lead->next_follow_up_time) {
                                                try {
                                                    $formattedTime = \Carbon\Carbon::createFromFormat('H:i', substr($lead->next_follow_up_time, 0, 5))->format('h:i A');
                                                } catch (\Exception $e) {
                                                    $formattedTime = $lead->next_follow_up_time;
                                                }
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-regular fa-clock"></i> {{ $lead->next_follow_up_date->format('d M Y') }} {{ $formattedTime }}
                                        </span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Not scheduled</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-hold">{{ $lead->source }}</span></td>
                        <td>
                            <div style="max-width: 200px; font-size: 12.5px; color: #334155; white-space: normal;">
                                {{ $lead->message ?: '-' }}
                            </div>
                        </td>
                        <td>{{ $lead->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                        <td>
                            <form action="{{ route('leads.status', $lead->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <select name="status" class="form-select" style="font-size: 11.5px; padding: 3px 8px; width: auto;" onchange="this.form.submit()">
                                    @foreach($statuses as $st)
                                        <option value="{{ $st }}" {{ $lead->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>
                            @if($lead->status === 'CONVERTED' && $lead->converted_customer_id)
                                <div style="display: flex; gap: 5px; align-items: center; white-space: nowrap;">
                                    <a href="{{ route('fast_entry.index', ['mobile' => $lead->convertedCustomer?->mobile ?? $lead->mobile, 'customer_name' => $lead->convertedCustomer?->name ?? $lead->name, 'service_id' => $lead->service_id]) }}" 
                                       class="btn btn-primary btn-sm" 
                                       style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;"
                                       title="Open in Fast Daily Entry">
                                        <i class="fa-solid fa-bolt"></i> Fast Entry
                                    </a>
                                    <a href="{{ route('customers.show', $lead->converted_customer_id) }}" 
                                       class="btn btn-secondary btn-sm" 
                                       style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;"
                                       title="View Full Profile">
                                        <i class="fa-solid fa-user"></i> Profile
                                    </a>
                                </div>
                            @else
                                <form action="{{ route('leads.convert', $lead->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" title="Convert to Customer & Load in Fast Daily Entry" style="display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;">
                                        <i class="fa-solid fa-user-check"></i> Convert
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 24px; color: var(--text-muted);">No enquiries recorded in pipeline.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $leads->links() }}
        </div>
    @endif
</div>

<!-- Record Lead Modal -->
<div id="leadModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 620px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-headset"></i> Record New Lead / Citizen Enquiry</h3>
            <button type="button" onclick="closeModal('leadModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('leads.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Citizen / Prospect Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Nilesh Joshi">
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" required placeholder="98XXXXXXXX">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Lead Source</label>
                        <select name="source" class="form-select" required>
                            <option value="WALK_IN">Walk-in Counter</option>
                            <option value="PHONE">Phone Inquiry</option>
                            <option value="WHATSAPP">WhatsApp</option>
                            <option value="WEBSITE">Website Portal</option>
                            <option value="REFERRAL">Referral</option>
                        </select>
                    </div>
                </div>

                <!-- Service & Sub Service Separate -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Main Service (मुख्य सेवा)</label>
                        <select id="newLeadMainService" class="form-select" onchange="onLeadMainServiceChange(this.value)">
                            <option value="">-- General Inquiry / Select Service --</option>
                            @foreach($uniqueMainServices as $mainName)
                                <option value="{{ $mainName }}">{{ $mainName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sub Service (उप-सेवा)</label>
                        <select name="service_id" id="newLeadSubService" class="form-select" onchange="onLeadSubServiceChange(this)">
                            <option value="">-- Select Main Service First --</option>
                        </select>
                        <input type="hidden" name="sub_service" id="newLeadSubServiceText">
                    </div>
                </div>

                <!-- Next Follow-up Date and Time in Modal -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="date" name="next_follow_up_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Next Follow-up Time</label>
                        <input type="time" name="next_follow_up_time" class="form-control" value="11:00">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Assign Staff</label>
                        <select name="assigned_employee_id" class="form-select">
                            <option value="">-- Unassigned --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Enquiry Message / Requirements</label>
                    <textarea name="message" class="form-control" rows="2" placeholder="Describe customer query..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('leadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Lead</button>
            </div>
        </form>
    </div>
</div>

<!-- Required Documents List Modal -->
<div id="docListModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 520px;">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div>
                    <h3 id="docModalTitle" style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">Required Documents</h3>
                    <div id="docModalSubtitle" style="font-size: 11px; color: #64748b; margin-top: 2px;">Service Checklist for Citizen</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('docListModal')" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 18px 20px;">
            <div id="docModalBody">
                <!-- Dynamically loaded -->
            </div>
        </div>
        <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="copyDocChecklist()" style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-regular fa-copy"></i> Copy Checklist
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeModal('docListModal')">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const allServicesList = @json($services);
let currentDocList = [];
let currentDocTitle = '';

function openLeadModal() { 
    document.getElementById('leadModal').classList.add('open'); 
}

function closeModal(id) { 
    document.getElementById(id).classList.remove('open'); 
}

// Dynamic Sub-Service dropdown based on Main Service selection
function onLeadMainServiceChange(mainName) {
    const subSelect = document.getElementById('newLeadSubService');
    const subText = document.getElementById('newLeadSubServiceText');
    if (!subSelect) return;

    subSelect.innerHTML = '';

    if (!mainName) {
        subSelect.innerHTML = '<option value="">-- General Inquiry --</option>';
        if (subText) subText.value = '';
        return;
    }

    const matched = allServicesList.filter(s => (s.main_service_name || '').trim().toUpperCase() === mainName.trim().toUpperCase());

    if (matched.length === 0) {
        subSelect.innerHTML = '<option value="">-- No Sub-Services Found --</option>';
        if (subText) subText.value = '';
        return;
    }

    subSelect.innerHTML = '<option value="">-- Select Sub Service --</option>';
    matched.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.sub_service_name || s.main_service_name;
        opt.setAttribute('data-sub-name', s.sub_service_name || '');
        subSelect.appendChild(opt);
    });

    if (matched.length === 1) {
        subSelect.selectedIndex = 1;
        if (subText) subText.value = matched[0].sub_service_name || '';
    }
}

function onLeadSubServiceChange(selectEl) {
    const subText = document.getElementById('newLeadSubServiceText');
    if (!subText) return;
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    if (selectedOption && selectedOption.hasAttribute('data-sub-name')) {
        subText.value = selectedOption.getAttribute('data-sub-name');
    } else {
        subText.value = '';
    }
}

// Inline AJAX Follow-up Save
function saveFollowUp(leadId) {
    const dateInput = document.getElementById(`followup-date-${leadId}`);
    const timeInput = document.getElementById(`followup-time-${leadId}`);
    const badgeEl = document.getElementById(`followup-badge-${leadId}`);
    
    const dateVal = dateInput ? dateInput.value : '';
    const timeVal = timeInput ? timeInput.value : '';
    
    if (badgeEl) {
        badgeEl.innerHTML = `<span style="color: #64748b; font-size: 10px;"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span>`;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]') 
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
        : '{{ csrf_token() }}';
    
    fetch(`/leads/${leadId}/followup`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            next_follow_up_date: dateVal,
            next_follow_up_time: timeVal
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (badgeEl) {
                if (dateVal) {
                    const formattedDisplay = `${dateVal} ${timeVal || ''}`.trim();
                    badgeEl.innerHTML = `<span class="badge badge-success" style="font-size: 10px; padding: 2px 6px;"><i class="fa-solid fa-check"></i> Saved: ${formattedDisplay}</span>`;
                    setTimeout(() => {
                        badgeEl.innerHTML = `<span class="badge badge-info" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-regular fa-clock"></i> ${formattedDisplay}</span>`;
                    }, 1800);
                } else {
                    badgeEl.innerHTML = `<span style="color: #94a3b8; font-style: italic;">Not scheduled</span>`;
                }
            }
        } else {
            alert(data.message || 'Error updating follow-up');
        }
    })
    .catch(err => {
        console.error(err);
        if (badgeEl) {
            badgeEl.innerHTML = `<span style="color: #dc2626; font-size: 10px;"><i class="fa-solid fa-circle-exclamation"></i> Save failed</span>`;
        }
    });
}

// Document List Modal
function openDocListModal(serviceTitle, docs) {
    currentDocTitle = serviceTitle || 'Service Documents';
    currentDocList = Array.isArray(docs) ? docs : [];
    
    document.getElementById('docModalTitle').innerText = currentDocTitle;
    const subTitle = document.getElementById('docModalSubtitle');
    if (subTitle) {
        subTitle.innerText = `${currentDocList.length} Required Document(s) Checklist`;
    }
    
    const bodyEl = document.getElementById('docModalBody');
    if (!bodyEl) return;
    
    if (currentDocList.length === 0) {
        bodyEl.innerHTML = `
            <div style="text-align: center; padding: 28px 15px; color: #64748b;">
                <i class="fa-regular fa-folder-open" style="font-size: 32px; color: #94a3b8; margin-bottom: 8px; display: block;"></i>
                <div style="font-weight: 600; font-size: 13.5px; color: #334155;">No Specific Documents Registered</div>
                <div style="font-size: 11.5px; margin-top: 3px; color: #94a3b8;">Standard citizen identity proofs (Aadhaar / PAN Card) recommended.</div>
            </div>
        `;
    } else {
        let html = `
            <div style="margin-bottom: 12px; font-size: 12px; color: #475569; font-weight: 500;">
                <i class="fa-solid fa-circle-info" style="color: #4f46e5;"></i> Required documents checklist for citizen processing:
            </div>
            <div style="display: flex; flex-direction: column; gap: 7px; max-height: 320px; overflow-y: auto; padding-right: 4px;">
        `;
        
        currentDocList.forEach((doc, idx) => {
            html += `
                <div style="display: flex; align-items: center; gap: 10px; padding: 9px 13px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <div style="width: 22px; height: 22px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; font-weight: 700;">
                        ${idx + 1}
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.3;">
                        ${escapeHtml(doc)}
                    </div>
                </div>
            `;
        });
        
        html += `</div>`;
        bodyEl.innerHTML = html;
    }
    
    document.getElementById('docListModal').classList.add('open');
}

function copyDocChecklist() {
    if (!currentDocList || currentDocList.length === 0) {
        alert('No documents in list to copy.');
        return;
    }
    const text = `📋 Required Documents for ${currentDocTitle}:\n` + currentDocList.map((d, i) => `${i+1}. ${d}`).join('\n');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Checklist copied to clipboard! You can paste and share via WhatsApp or SMS.');
        }).catch(() => {
            prompt('Copy checklist below:', text);
        });
    } else {
        prompt('Copy checklist below:', text);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
