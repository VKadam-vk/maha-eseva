@extends('layouts.app')

@section('title', 'Application #' . $application->application_number)

@section('content')
<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <h1 class="page-title">{{ $application->application_number }}</h1>
            @php
                $statusBadge = match($application->work_status) {
                    'NEW' => 'badge-new',
                    'APPROVED', 'READY' => 'badge-ready',
                    'DELIVERED', 'CLOSED' => 'badge-delivered',
                    'DOCUMENT_PENDING' => 'badge-pending',
                    'REJECTED', 'CANCELLED' => 'badge-rejected',
                    default => 'badge-process'
                };
                $payBadge = match($application->payment_status) {
                    'PAID' => 'badge-paid',
                    'PARTIAL' => 'badge-partial',
                    default => 'badge-pending'
                };
            @endphp
            <span class="badge {{ $statusBadge }}" style="font-size: 13px; padding: 6px 14px;">{{ $application->work_status }}</span>
            <span class="badge {{ $payBadge }}" style="font-size: 13px; padding: 6px 14px;">{{ $application->payment_status }}</span>
        </div>
        <p class="page-subtitle">
            Customer: <strong>{{ $application->customer->name }}</strong> ({{ $application->customer->mobile }}) • Service: <strong>{{ $application->service->full_name }}</strong>
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary" onclick="openStatusModal()">
            <i class="fa-solid fa-arrows-rotate"></i> Change Status
        </button>
        <button type="button" class="btn btn-secondary" onclick="openPayModal()">
            <i class="fa-solid fa-indian-rupee-sign"></i> Add Payment
        </button>
        <button type="button" class="btn btn-secondary" onclick="openDocModal()">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Doc
        </button>
    </div>
</div>

<!-- Financial & Milestone KPI Strip -->
<div class="metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="metric-card indigo" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Total Service Fee</div>
            <div class="metric-value" style="font-size: 22px;">₹{{ number_format($application->total_amount, 2) }}</div>
        </div>
    </div>
    <div class="metric-card emerald" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Amount Received</div>
            <div class="metric-value" style="font-size: 22px; color: #166534;">₹{{ number_format($application->received_amount, 2) }}</div>
        </div>
    </div>
    <div class="metric-card saffron" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Remaining Balance</div>
            <div class="metric-value" style="font-size: 22px; color: {{ $application->remaining_amount > 0 ? '#dc2626' : '#166534' }};">
                ₹{{ number_format($application->remaining_amount, 2) }}
            </div>
        </div>
    </div>
    <div class="metric-card amber" style="padding: 16px 20px;">
        <div>
            <div class="metric-label" style="font-size: 11px;">Expected Completion</div>
            <div class="metric-value" style="font-size: 18px;">
                {{ $application->expected_completion_date ? $application->expected_completion_date->format('d M Y') : '7 Days' }}
            </div>
        </div>
    </div>
</div>

<div class="form-grid-2">
    <!-- Left Column: Application Details & Custom Values -->
    <div>
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-file-invoice"></i> Application Information</h3>
            </div>
            <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong>SR Number:</strong> <span style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">{{ $application->application_number }}</span></div>
                <div><strong>Customer Profile:</strong> <a href="{{ route('customers.show', $application->customer_id) }}" style="font-weight: 700; color: #4f46e5;">{{ $application->customer->name }}</a> ({{ $application->customer->customer_code }})</div>
                <div><strong>Mobile:</strong> {{ $application->customer->mobile }}</div>
                <div><strong>Application Date:</strong> {{ $application->application_date->format('d M Y') }}</div>
                <div><strong>Branch:</strong> {{ $application->branch->name }}</div>
                <div><strong>Assigned Operator:</strong> {{ $application->assignedEmployee?->user?->name ?? 'Unassigned' }}</div>
                <div><strong>External Ack / Token No:</strong> <span style="font-family: var(--font-mono); font-weight: 700;">{{ $application->external_acknowledgement_no ?? 'Not generated yet' }}</span></div>
                <div><strong>Delivery Status:</strong> <span class="badge badge-process">{{ $application->delivery_status }}</span></div>

                @if($application->work_details)
                    <div style="margin-top: 12px; background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 12px;">
                        <strong>Work Notes / Instructions:</strong>
                        <p style="margin-top: 4px; color: #334155;">{{ $application->work_details }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Dynamic Service Custom Fields Card -->
        @if($application->customValues->isNotEmpty())
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa-solid fa-sliders"></i> Service Specific Field Values</h3>
                </div>
                <div class="erp-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 13.5px;">
                        @foreach($application->customValues as $cv)
                            <div style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 10px 14px;">
                                <div style="font-size: 11.5px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
                                    {{ $cv->field?->label ?? $cv->field_key }}
                                </div>
                                <div style="font-weight: 700; margin-top: 2px; color: var(--text-main);">
                                    {{ $cv->field_value }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Update Assignment & External Ack Form -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-pen-to-square"></i> Update Operator Assignment & Ack No.</h3>
            </div>
            <div class="erp-card-body">
                <form action="{{ route('applications.details.update', $application->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Assigned Staff / Operator</label>
                            <select name="assigned_employee_id" class="form-select">
                                <option value="">-- Unassigned --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $application->assigned_employee_id == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->user->name }} ({{ $emp->designation }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Govt Acknowledgement No.</label>
                            <input type="text" name="external_acknowledgement_no" class="form-control" value="{{ $application->external_acknowledgement_no }}" placeholder="e.g. N-1294819284">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ $application->due_date?->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pending Remarks</label>
                            <input type="text" name="pending_remarks" class="form-control" value="{{ $application->pending_remarks }}" placeholder="e.g. Biometric verification pending">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-check"></i> Save Assignment & Ack
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Status Workflow History, Documents, Payments -->
    <div>
        <!-- Status History & State Machine Timeline -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-arrows-rotate"></i> Workflow Status History</h3>
                <button type="button" class="btn btn-primary btn-sm" onclick="openStatusModal()">
                    <i class="fa-solid fa-pen"></i> Update Status
                </button>
            </div>
            <div class="erp-card-body">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @forelse($application->statusHistories as $sh)
                        <div style="display: flex; gap: 12px; border-left: 2px solid #ea580c; padding-left: 16px; position: relative;">
                            <div style="position: absolute; left: -7px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: #ea580c;"></div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="badge badge-process" style="font-size: 11px;">{{ $sh->new_status }}</span>
                                    <span style="font-size: 12px; color: var(--text-muted);">{{ $sh->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <div style="font-size: 13px; font-weight: 500; margin-top: 4px; color: var(--text-main);">
                                    {{ $sh->remarks ?: 'Status transition recorded' }}
                                </div>
                                <div style="font-size: 11.5px; color: var(--text-light); margin-top: 2px;">
                                    by {{ $sh->changer?->name ?? 'System' }} • IP: {{ $sh->ip_address ?? '127.0.0.1' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="font-size: 13px; color: var(--text-muted);">No status transition history recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Attached Documents -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-folder-open"></i> Private Documents Vault</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="openDocModal()">
                    <i class="fa-solid fa-upload"></i> Upload
                </button>
            </div>
            <div class="table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Doc Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($application->documents as $doc)
                            <tr>
                                <td>
                                    <strong>{{ $doc->document_type_name }}</strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $doc->original_filename }} ({{ $doc->formatted_size }})</div>
                                </td>
                                <td>
                                    <form action="{{ route('documents.status', $doc->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <select name="status" class="form-select" style="font-size: 11.5px; padding: 3px 8px; width: auto;" onchange="this.form.submit()">
                                            <option value="RECEIVED" {{ $doc->status === 'RECEIVED' ? 'selected' : '' }}>RECEIVED</option>
                                            <option value="VERIFIED" {{ $doc->status === 'VERIFIED' ? 'selected' : '' }}>VERIFIED</option>
                                            <option value="REJECTED" {{ $doc->status === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <a href="{{ route('documents.preview', $doc->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Preview inline">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-secondary btn-sm" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 16px; color: var(--text-muted); font-size: 13px;">No documents attached.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payments for Application -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-receipt"></i> Payments Ledger</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="openPayModal()">
                    <i class="fa-solid fa-plus"></i> Record Payment
                </button>
            </div>
            <div class="table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Amount</th>
                            <th>Mode</th>
                            <th>Date</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($application->payments as $pay)
                            <tr>
                                <td><strong style="font-family: var(--font-mono);">{{ $pay->receipt_number }}</strong></td>
                                <td><strong style="color: #166534;">₹{{ number_format($pay->amount, 2) }}</strong></td>
                                <td><span class="badge badge-ready">{{ $pay->payment_mode }}</span></td>
                                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('payments.receipt', $pay->id) }}" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-print"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 16px; color: var(--text-muted); font-size: 13px;">No payments recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div id="statusModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-arrows-rotate"></i> Change Status: {{ $application->application_number }}</h3>
            <button type="button" onclick="closeModal('statusModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('applications.status.update', $application->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Select New Status</label>
                    <select name="work_status" class="form-select" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ $application->work_status == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Update Remarks / Customer Note</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="e.g. Approved by Tahsildar. Certificate generated and ready for print."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update Status & Notify</button>
            </div>
        </form>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="payModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-indian-rupee-sign"></i> Record Payment for {{ $application->application_number }}</h3>
            <button type="button" onclick="closeModal('payModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('payments.record') }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $application->customer_id }}">
            <input type="hidden" name="application_id" value="{{ $application->id }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Payment Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required value="{{ $application->remaining_amount > 0 ? $application->remaining_amount : '' }}" placeholder="0.00">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Payment Mode</label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="CASH">Cash</option>
                            <option value="UPI">UPI / QR Code</option>
                            <option value="BANK_TRANSFER">Bank Transfer</option>
                            <option value="CARD">Card</option>
                            <option value="CHEQUE">Cheque</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Transaction Reference (UPI UTR)</label>
                    <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. 482910394819">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('payModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save & Generate Receipt</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Doc Modal -->
<div id="docModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-shield-halved"></i> Upload Document for {{ $application->application_number }}</h3>
            <button type="button" onclick="closeModal('docModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $application->customer_id }}">
            <input type="hidden" name="application_id" value="{{ $application->id }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Document Type</label>
                    <select name="document_type_name" class="form-select" required>
                        @if(!empty($application->service->required_documents_json))
                            @foreach($application->service->required_documents_json as $doc)
                                <option value="{{ $doc }}">{{ $doc }}</option>
                            @endforeach
                        @endif
                        <option value="Aadhaar Card">Aadhaar Card</option>
                        <option value="PAN Card">PAN Card</option>
                        <option value="Photo">Passport Size Photo</option>
                        <option value="Signature">Signature</option>
                        <option value="Generated Certificate / Final PDF">Generated Certificate / Final PDF</option>
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

@push('scripts')
<script>
function openStatusModal() { document.getElementById('statusModal').classList.add('open'); }
function openPayModal() { document.getElementById('payModal').classList.add('open'); }
function openDocModal() { document.getElementById('docModal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
@endpush
@endsection
