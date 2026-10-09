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
            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Pipeline Status</label>
                <select name="status" class="form-select">
                    <option value="">All Pipeline Stages</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 160px;">
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
                    <th>Requested Service</th>
                    <th>Source</th>
                    <th>Enquiry Message</th>
                    <th>Assigned Staff</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td>
                            <strong>{{ $lead->name }}</strong>
                            @if($lead->email)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $lead->email }}</div>
                            @endif
                        </td>
                        <td><strong style="font-family: var(--font-mono);">{{ $lead->mobile }}</strong></td>
                        <td>{{ $lead->service?->full_name ?? 'General Inquiry' }}</td>
                        <td><span class="badge badge-hold">{{ $lead->source }}</span></td>
                        <td>
                            <div style="max-width: 250px; font-size: 12.5px; color: #334155; white-space: normal;">
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
                            @if($lead->status === 'CONVERTED' && $lead->convertedCustomer)
                                <a href="{{ route('customers.show', $lead->converted_customer_id) }}" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-check"></i> Profile
                                </a>
                            @else
                                <form action="{{ route('leads.convert', $lead->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" title="1-Click Convert to Customer Profile">
                                        <i class="fa-solid fa-user-check"></i> Convert
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">No enquiries recorded in pipeline.</td>
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
    <div class="modal-container">
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

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Interested Service</label>
                        <select name="service_id" class="form-select">
                            <option value="">-- General Inquiry --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->main_service_name }} ({{ $s->sub_service_name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assign Staff</label>
                        <select name="assigned_employee_id" class="form-select">
                            <option value="">-- Unassigned --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
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

@push('scripts')
<script>
function openLeadModal() { document.getElementById('leadModal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
@endpush
@endsection
