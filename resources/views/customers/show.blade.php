@extends('layouts.app')

@section('title', $customer->name . ' (360° Profile)')

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; gap: 16px;">
        <div class="user-avatar" style="width: 54px; height: 54px; font-size: 20px;">
            {{ strtoupper(substr($customer->name, 0, 1)) }}
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 class="page-title" style="font-size: 22px;">{{ $customer->name }}</h1>
                <span class="badge badge-ready" style="font-family: var(--font-mono);">{{ $customer->customer_code }}</span>
                <span class="badge badge-hold">{{ $customer->category?->name ?? 'Regular' }}</span>
            </div>
            <p class="page-subtitle">
                <i class="fa-solid fa-phone"></i> {{ $customer->mobile }} 
                @if($customer->email) | <i class="fa-solid fa-envelope"></i> {{ $customer->email }} @endif
                | <i class="fa-solid fa-location-dot"></i> {{ $customer->city ?? 'Pune' }}
            </p>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('applications.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Application
        </a>
        <button type="button" class="btn btn-secondary" onclick="openPaymentModal()">
            <i class="fa-solid fa-indian-rupee-sign"></i> Record Payment
        </button>
        <button type="button" class="btn btn-secondary" onclick="openDocModal()">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Doc
        </button>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
    </div>
</div>

<!-- Balance / Summary Strip -->
<div class="metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="metric-card indigo" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Billed</div>
            <div class="metric-value" style="font-size: 22px;">₹{{ number_format($customer->total_billed, 2) }}</div>
        </div>
    </div>
    <div class="metric-card emerald" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Paid</div>
            <div class="metric-value" style="font-size: 22px; color: #166534;">₹{{ number_format($customer->total_paid, 2) }}</div>
        </div>
    </div>
    <div class="metric-card saffron" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Outstanding Balance</div>
            <div class="metric-value" style="font-size: 22px; color: {{ $customer->total_outstanding > 0 ? '#dc2626' : '#166534' }};">
                ₹{{ number_format($customer->total_outstanding, 2) }}
            </div>
        </div>
    </div>
    <div class="metric-card amber" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Applications</div>
            <div class="metric-value" style="font-size: 22px;">{{ $customer->applications->count() }}</div>
        </div>
    </div>
</div>

<!-- Profile Tabs -->
<div class="erp-tabs">
    <button class="erp-tab-btn active" onclick="switchTab('overview')"><i class="fa-solid fa-id-card"></i> Overview</button>
    <button class="erp-tab-btn" onclick="switchTab('applications')"><i class="fa-solid fa-file-signature"></i> Applications ({{ $customer->applications->count() }})</button>
    <button class="erp-tab-btn" onclick="switchTab('documents')"><i class="fa-solid fa-folder-open"></i> Private Documents ({{ $customer->documents->count() }})</button>
    <button class="erp-tab-btn" onclick="switchTab('payments')"><i class="fa-solid fa-receipt"></i> Payments & Ledger ({{ $customer->payments->count() }})</button>
    <button class="erp-tab-btn" onclick="switchTab('followups')"><i class="fa-solid fa-calendar-check"></i> Follow-ups ({{ $customer->followUps->count() }})</button>
    <button class="erp-tab-btn" onclick="switchTab('timeline')"><i class="fa-solid fa-timeline"></i> Activity Timeline</button>
</div>

<!-- Tab 1: Overview -->
<div id="tab-overview" class="tab-pane">
    <div class="form-grid-2">
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-address-book"></i> Personal & Demographic Information</h3>
            </div>
            <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong>Full Name:</strong> {{ $customer->name }}</div>
                <div><strong>Mobile:</strong> <span style="font-family: var(--font-mono); font-weight: 700;">{{ $customer->mobile }}</span></div>
                <div><strong>Gender:</strong> {{ $customer->gender }}</div>
                <div><strong>Birth Date:</strong> {{ $customer->birth_date ? $customer->birth_date->format('d M Y') : 'Not provided' }}</div>
                <div><strong>Email:</strong> {{ $customer->email ?? 'Not provided' }}</div>
                <div><strong>Alternate Mobile:</strong> {{ $customer->alternate_mobile ?? '-' }}</div>
                <div><strong>Address:</strong> {{ $customer->address ?? '-' }}</div>
                <div><strong>City / District:</strong> {{ $customer->city ?? 'Pune' }}, {{ $customer->district ?? 'Pune' }} ({{ $customer->pincode ?? '-' }})</div>
                <div><strong>Registered At:</strong> {{ $customer->created_at->format('d M Y, h:i A') }} (by {{ $customer->creator?->name ?? 'Staff' }})</div>
            </div>
        </div>

        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-note-sticky"></i> Profile Notes & Instructions</h3>
            </div>
            <div class="erp-card-body">
                <div style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 14px; min-height: 120px; font-size: 13.5px; color: #334155;">
                    {{ $customer->notes ?: 'No special notes recorded for this customer profile.' }}
                </div>
                <div style="margin-top: 16px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openFollowupModal()">
                        <i class="fa-solid fa-calendar-plus"></i> Schedule Follow-up
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab 2: Applications -->
<div id="tab-applications" class="tab-pane" style="display: none;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-file-lines"></i> Customer Applications & Services History</h3>
            <a href="{{ route('applications.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> New Application
            </a>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>SR Number</th>
                        <th>Application Date</th>
                        <th>Service Name</th>
                        <th>Assigned Staff</th>
                        <th>Total Amount</th>
                        <th>Received</th>
                        <th>Work Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->applications as $app)
                        <tr>
                            <td>
                                <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">
                                    {{ $app->application_number }}
                                </a>
                            </td>
                            <td>{{ $app->application_date->format('d M Y') }}</td>
                            <td>{{ $app->service->main_service_name }} ({{ $app->service->sub_service_name }})</td>
                            <td>{{ $app->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                            <td>₹{{ number_format($app->total_amount, 2) }}</td>
                            <td style="color: #166534; font-weight: 700;">₹{{ number_format($app->received_amount, 2) }}</td>
                            <td><span class="badge badge-process">{{ $app->work_status }}</span></td>
                            <td><span class="badge badge-paid">{{ $app->payment_status }}</span></td>
                            <td>
                                <a href="{{ route('applications.show', $app->id) }}" class="btn btn-secondary btn-sm">View <i class="fa-solid fa-arrow-right"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 24px; color: var(--text-muted);">No applications registered for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab 3: Documents -->
<div id="tab-documents" class="tab-pane" style="display: none;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-shield-halved"></i> Private Document Vault</h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="openDocModal()">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Document
            </button>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Original Filename</th>
                        <th>Size</th>
                        <th>Uploaded On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->documents as $doc)
                        <tr>
                            <td><strong>{{ $doc->document_type_name }}</strong></td>
                            <td>{{ $doc->original_filename }}</td>
                            <td>{{ $doc->formatted_size }}</td>
                            <td>{{ $doc->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                @php
                                    $docBadge = match($doc->status) {
                                        'VERIFIED' => 'badge-paid',
                                        'REJECTED' => 'badge-pending',
                                        default => 'badge-process'
                                    };
                                @endphp
                                <span class="badge {{ $docBadge }}">{{ $doc->status }}</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('documents.preview', $doc->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Preview inline">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-secondary btn-sm" title="Download secure file">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Permanently delete this document from vault?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">No private documents uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab 4: Payments -->
<div id="tab-payments" class="tab-pane" style="display: none;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Receipts & Payments Ledger</h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="openPaymentModal()">
                <i class="fa-solid fa-plus"></i> Record Payment
            </button>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Reference</th>
                        <th>Received By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->payments as $pay)
                        <tr>
                            <td><strong style="font-family: var(--font-mono);">{{ $pay->receipt_number }}</strong></td>
                            <td>{{ $pay->payment_date->format('d M Y') }}</td>
                            <td><strong style="color: #166534; font-size: 14px;">₹{{ number_format($pay->amount, 2) }}</strong></td>
                            <td><span class="badge badge-ready">{{ $pay->payment_mode }}</span></td>
                            <td>{{ $pay->transaction_reference ?? '-' }}</td>
                            <td>{{ $pay->receiver?->name ?? 'Operator' }}</td>
                            <td>
                                <a href="{{ route('payments.receipt', $pay->id) }}" target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-print"></i> Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">No payment transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab 5: Follow-ups -->
<div id="tab-followups" class="tab-pane" style="display: none;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-calendar-check"></i> Customer Follow-ups & Reminders</h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="openFollowupModal()">
                <i class="fa-solid fa-calendar-plus"></i> Schedule Follow-up
            </button>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Follow-up Date</th>
                        <th>Reason</th>
                        <th>Remarks</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->followUps as $f)
                        <tr>
                            <td>{{ $f->follow_up_date->format('d M Y') }} {{ $f->follow_up_time }}</td>
                            <td><strong>{{ $f->reason }}</strong></td>
                            <td>{{ $f->remarks ?? '-' }}</td>
                            <td>{{ $f->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                            <td><span class="badge badge-process">{{ $f->status }}</span></td>
                            <td>
                                @if($f->status === 'PENDING')
                                    <form action="{{ route('followups.status', $f->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="COMPLETED">
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Done</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">No follow-ups recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab 6: Timeline -->
<div id="tab-timeline" class="tab-pane" style="display: none;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-timeline"></i> Complete Activity & Audit Trail</h3>
        </div>
        <div class="erp-card-body">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @forelse($activityTimeline as $log)
                    <div style="display: flex; gap: 14px; border-left: 2px solid var(--surface-border); padding-left: 16px; position: relative;">
                        <div style="position: absolute; left: -7px; top: 2px; width: 12px; height: 12px; border-radius: 50%; background: #ea580c;"></div>
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: var(--text-main);">{{ $log->event }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                {{ $log->created_at->format('d M Y, h:i A') }} • by {{ $log->user?->name ?? 'System' }} • IP: {{ $log->ip_address }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="font-size: 13px; color: var(--text-muted);">No historical audit events logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Record Payment Modal -->
<div id="paymentModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-indian-rupee-sign"></i> Record Payment for {{ $customer->name }}</h3>
            <button type="button" onclick="closeModal('paymentModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('payments.record') }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Payment Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00" value="{{ $customer->total_outstanding > 0 ? $customer->total_outstanding : '' }}">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Payment Mode</label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="CASH">Cash</option>
                            <option value="UPI">UPI / QR Code</option>
                            <option value="BANK_TRANSFER">Bank Transfer (NEFT/IMPS)</option>
                            <option value="CARD">Debit / Credit Card</option>
                            <option value="CHEQUE">Cheque</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Transaction Reference (UPI UTR / Cheque No)</label>
                    <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. 482910394819">
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('paymentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save & Generate Receipt</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="docModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-shield-halved"></i> Upload Document for {{ $customer->name }}</h3>
            <button type="button" onclick="closeModal('docModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Document Type</label>
                    <select name="document_type_name" class="form-select" required>
                        <option value="Aadhaar Card">Aadhaar Card</option>
                        <option value="PAN Card">PAN Card</option>
                        <option value="Ration Card">Ration Card</option>
                        <option value="Electricity Bill">Electricity Bill</option>
                        <option value="School Leaving Certificate">School Leaving Certificate (LC)</option>
                        <option value="Passport Photo">Passport Photo</option>
                        <option value="Caste Certificate">Caste Certificate</option>
                        <option value="Income Certificate">Income Certificate</option>
                        <option value="Rent Agreement">Rent Agreement</option>
                        <option value="Other Document">Other Document</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Select File (PDF, JPG, PNG, DOCX - Max 15MB)</label>
                    <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.webp,.docx,.zip">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('docModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Upload to Vault</button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Followup Modal -->
<div id="followupModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-calendar-plus"></i> Schedule Follow-up</h3>
            <button type="button" onclick="closeModal('followupModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('followups.store') }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Follow-up Time</label>
                        <input type="time" name="follow_up_time" class="form-control" value="11:00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Reason for Follow-up</label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Document Collection, Balance Payment Reminder">
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Specific notes for operator..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('followupModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Follow-up</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.erp-tab-btn').forEach(el => el.classList.remove('active'));

    document.getElementById('tab-' + tabName).style.display = 'block';
    event.currentTarget.classList.add('active');
}

function openPaymentModal() {
    document.getElementById('paymentModal').classList.add('open');
}

function openDocModal() {
    document.getElementById('docModal').classList.add('open');
}

function openFollowupModal() {
    document.getElementById('followupModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
</script>
@endpush
@endsection
