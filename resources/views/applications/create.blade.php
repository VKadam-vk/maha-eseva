@extends('layouts.app')

@section('title', 'New Service Application')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Application / Service Request</h1>
        <p class="page-subtitle">Register a citizen service request with dynamic custom fields and fee calculation</p>
    </div>
    <a href="{{ route('applications.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Applications
    </a>
</div>

<form action="{{ route('applications.store') }}" method="POST">
    @csrf

    <div class="form-grid-2">
        <!-- Step 1: Customer Selection Card -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-user-check"></i> Step 1: Customer Selection</h3>
                <a href="{{ route('customers.create') }}" target="_blank" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-user-plus"></i> New Customer
                </a>
            </div>
            <div class="erp-card-body">
                <div class="form-group">
                    <label class="form-label required">Select Existing Customer</label>
                    <select name="customer_id" id="customer_select" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (old('customer_id') == $c->id || (isset($selectedCustomer) && $selectedCustomer->id == $c->id)) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->mobile }}) - {{ $c->customer_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Application Date</label>
                        <input type="date" name="application_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assign Staff / Operator</label>
                        <select name="assigned_employee_id" class="form-select">
                            <option value="">-- Assign Operator --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('assigned_employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->user->name }} ({{ $emp->designation }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Work Details / Instructions</label>
                    <textarea name="work_details" class="form-control" rows="2" placeholder="Citizen specific instructions or remarks...">{{ old('work_details') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Step 2: Service Selection & Dynamic Fields Card -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-file-signature"></i> Step 2: Service & Custom Fields</h3>
            </div>
            <div class="erp-card-body">
                <div class="form-group">
                    <label class="form-label required">Select Service</label>
                    <select name="service_id" id="service_select" class="form-select" required onchange="loadServiceSchema(this.value)">
                        <option value="">-- Choose Maha E-Seva Service --</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ (old('service_id') == $srv->id || (request('service_id') == $srv->id)) ? 'selected' : '' }}>
                                {{ $srv->main_service_name }} - {{ $srv->sub_service_name }} (₹{{ number_format($srv->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dynamic Form Fields Container -->
                <div id="dynamic-fields-container" style="background: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px; display: none;">
                    <h4 style="font-size: 13.5px; font-weight: 700; margin-bottom: 12px; color: #1e293b;">
                        <i class="fa-solid fa-sliders"></i> Service Specific Form Fields
                    </h4>
                    <div id="dynamic-fields-body" class="form-grid-2">
                        <!-- Injected dynamically via JS -->
                    </div>
                </div>

                <!-- Required Documents Checklist Display -->
                <div id="required-docs-box" style="display: none; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-md); padding: 14px; margin-bottom: 16px;">
                    <strong style="font-size: 13px; color: #92400e;"><i class="fa-solid fa-paperclip"></i> Mandatory Documents Checklist:</strong>
                    <ul id="required-docs-list" style="margin-left: 20px; margin-top: 6px; font-size: 12.5px; color: #78350f;">
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Financials & Counter Payment Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Step 3: Billing & Counter Payment</h3>
        </div>
        <div class="erp-card-body">
            <div class="form-grid-4">
                <div class="form-group">
                    <label class="form-label">Base Fee (₹)</label>
                    <input type="number" step="0.01" id="base_amount_input" name="base_amount" class="form-control" value="0.00" oninput="calculateTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label">Govt Fee (₹)</label>
                    <input type="number" step="0.01" id="govt_fee_input" name="govt_fee" class="form-control" value="0.00" oninput="calculateTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label">Service Charge (₹)</label>
                    <input type="number" step="0.01" id="service_charge_input" name="service_charge" class="form-control" value="0.00" oninput="calculateTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label">Additional Charges (₹)</label>
                    <input type="number" step="0.01" id="additional_charges_input" name="additional_charges" class="form-control" value="0.00" oninput="calculateTotal()">
                </div>
            </div>

            <div class="form-grid-4" style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 16px; margin-top: 10px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Discount Amount (₹)</label>
                    <input type="number" step="0.01" id="discount_amount_input" name="discount_amount" class="form-control" value="0.00" oninput="calculateTotal()">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b;">Total Amount (₹)</label>
                    <input type="text" id="total_amount_display" class="form-control" style="font-weight: 800; font-size: 16px; background-color: #ffffff; color: #1e293b;" readonly value="₹0.00">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="color: #166534; font-weight: 700;">Initial Received (₹)</label>
                    <input type="number" step="0.01" id="received_amount_input" name="received_amount" class="form-control" placeholder="0.00" value="0.00">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-select">
                        <option value="CASH">Cash</option>
                        <option value="UPI">UPI / QR Code</option>
                        <option value="BANK_TRANSFER">Bank Transfer</option>
                        <option value="CARD">Card</option>
                        <option value="CHEQUE">Cheque</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="{{ route('applications.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                    <i class="fa-solid fa-check"></i> Register Application
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function loadServiceSchema(serviceId) {
    if (!serviceId) {
        document.getElementById('dynamic-fields-container').style.display = 'none';
        document.getElementById('required-docs-box').style.display = 'none';
        return;
    }

    fetch(`/services/${serviceId}/schema`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('base_amount_input').value = data.price;
            document.getElementById('govt_fee_input').value = data.govt_fee;
            document.getElementById('service_charge_input').value = data.service_charge;
            document.getElementById('received_amount_input').value = data.price;
            calculateTotal();

            // Render Documents Checklist
            const docsBox = document.getElementById('required-docs-box');
            const docsList = document.getElementById('required-docs-list');
            docsList.innerHTML = '';
            if (data.required_documents && data.required_documents.length > 0) {
                data.required_documents.forEach(doc => {
                    const li = document.createElement('li');
                    li.textContent = doc;
                    docsList.appendChild(li);
                });
                docsBox.style.display = 'block';
            } else {
                docsBox.style.display = 'none';
            }

            // Render Dynamic Fields
            const fieldsContainer = document.getElementById('dynamic-fields-container');
            const fieldsBody = document.getElementById('dynamic-fields-body');
            fieldsBody.innerHTML = '';

            if (data.custom_fields && data.custom_fields.length > 0) {
                data.custom_fields.forEach(f => {
                    const group = document.createElement('div');
                    group.className = 'form-group';
                    if (f.type === 'textarea') group.style.gridColumn = 'span 2';

                    const label = document.createElement('label');
                    label.className = 'form-label' + (f.is_required ? ' required' : '');
                    label.textContent = f.label;
                    group.appendChild(label);

                    let input;
                    if (f.type === 'textarea') {
                        input = document.createElement('textarea');
                        input.rows = 2;
                    } else if (f.type === 'select') {
                        input = document.createElement('select');
                        input.className = 'form-select';
                        const defaultOpt = document.createElement('option');
                        defaultOpt.value = '';
                        defaultOpt.textContent = '-- Select --';
                        input.appendChild(defaultOpt);
                        if (f.options) {
                            f.options.forEach(opt => {
                                const o = document.createElement('option');
                                o.value = opt;
                                o.textContent = opt;
                                input.appendChild(o);
                            });
                        }
                    } else {
                        input = document.createElement('input');
                        input.type = f.type === 'mobile' ? 'tel' : (f.type === 'password' ? 'password' : (f.type === 'number' ? 'number' : (f.type === 'date' ? 'date' : 'text')));
                    }

                    input.name = `custom_fields[${f.key}]`;
                    input.className = input.className || 'form-control';
                    if (f.placeholder) input.placeholder = f.placeholder;
                    if (f.is_required) input.required = true;

                    group.appendChild(input);
                    fieldsBody.appendChild(group);
                });
                fieldsContainer.style.display = 'block';
            } else {
                fieldsContainer.style.display = 'none';
            }
        });
}

function calculateTotal() {
    const base = parseFloat(document.getElementById('base_amount_input').value) || 0;
    const additional = parseFloat(document.getElementById('additional_charges_input').value) || 0;
    const discount = parseFloat(document.getElementById('discount_amount_input').value) || 0;
    const total = Math.max(0, (base + additional) - discount);
    document.getElementById('total_amount_display').value = '₹' + total.toFixed(2);
}

// Auto-trigger if pre-selected
document.addEventListener('DOMContentLoaded', () => {
    const sSelect = document.getElementById('service_select');
    if (sSelect && sSelect.value) {
        loadServiceSchema(sSelect.value);
    }
});
</script>
@endpush
@endsection
