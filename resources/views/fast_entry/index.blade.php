@extends('layouts.app')

@section('title', 'Fast Daily Counter Entry')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">High-Speed Daily Counter Entry Grid</h1>
        <p class="page-subtitle">Rapid Excel-like row entry for counter operators with instant customer creation & receipting</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <span class="badge badge-ready" style="font-size: 13px; padding: 8px 14px;">
            <i class="fa-solid fa-bolt"></i> Shortcut: Ctrl + Enter to Save Row
        </span>
    </div>
</div>

<!-- Fast Quick Add Bar -->
<div class="erp-card" style="border: 2px solid #ea580c; background-color: #fffaf5;">
    <div class="erp-card-header" style="background-color: #fff7ed;">
        <h3 class="erp-card-title" style="color: #ea580c;"><i class="fa-solid fa-bolt"></i> Fast Counter Row Entry</h3>
        <span style="font-size: 12px; color: #9a3412;">Auto-detects existing customer by mobile</span>
    </div>
    <div class="erp-card-body">
        <form id="fast-entry-form" onsubmit="saveFastRow(event)">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr 2.5fr 1fr 1fr 1.2fr 1fr auto; gap: 10px; align-items: flex-end;">
                <div>
                    <label class="form-label required" style="font-size: 11px;">Customer Name</label>
                    <input type="text" id="fe_name" name="customer_name" class="form-control" required placeholder="Citizen Name" style="font-size: 13px; padding: 7px 10px;">
                </div>

                <div>
                    <label class="form-label required" style="font-size: 11px;">Mobile Number</label>
                    <input type="tel" id="fe_mobile" name="mobile" class="form-control" required placeholder="10 Digits" style="font-size: 13px; padding: 7px 10px;">
                </div>

                <div>
                    <label class="form-label" style="font-size: 11px;">Gender</label>
                    <select id="fe_gender" name="gender" class="form-select" style="font-size: 12.5px; padding: 7px 8px;">
                        <option value="MALE">Male</option>
                        <option value="FEMALE">Female</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label required" style="font-size: 11px;">Service</label>
                    <select id="fe_service" name="service_id" class="form-select" required onchange="onServiceChange(this)" style="font-size: 12.5px; padding: 7px 8px;">
                        <option value="">-- Choose Service --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" data-price="{{ $s->price }}">
                                {{ $s->main_service_name }} ({{ $s->sub_service_name }}) - ₹{{ number_format($s->price, 0) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 11px;">Total Fee (₹)</label>
                    <input type="number" step="0.01" id="fe_base_amount" name="base_amount" class="form-control" placeholder="0" style="font-size: 13px; padding: 7px 10px;">
                </div>

                <div>
                    <label class="form-label" style="font-size: 11px; color: #166534; font-weight: 700;">Received (₹)</label>
                    <input type="number" step="0.01" id="fe_received_amount" name="received_amount" class="form-control" placeholder="0" style="font-size: 13px; padding: 7px 10px; font-weight: 700; color: #166534;">
                </div>

                <div>
                    <label class="form-label" style="font-size: 11px;">Payment Mode</label>
                    <select id="fe_paymode" name="payment_mode" class="form-select" style="font-size: 12.5px; padding: 7px 8px;">
                        <option value="CASH">Cash</option>
                        <option value="UPI">UPI / QR</option>
                        <option value="BANK_TRANSFER">Bank</option>
                        <option value="CARD">Card</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 11px;">Operator</label>
                    <select id="fe_employee" name="assigned_employee_id" class="form-select" style="font-size: 12.5px; padding: 7px 8px;">
                        <option value="">Auto</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" id="fe_submit_btn" class="btn btn-primary" style="padding: 8px 16px;">
                        <i class="fa-solid fa-plus"></i> Save Row
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Realtime Entry Data Grid -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-table-list"></i> Today's Counter Transactions (Live Stream)</h3>
        <span id="grid-status" class="badge badge-paid"><i class="fa-solid fa-check"></i> Grid Active</span>
    </div>
    <div class="table-responsive">
        <table class="erp-table" id="counter-table">
            <thead>
                <tr>
                    <th>SR Number</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Service</th>
                    <th>Total</th>
                    <th>Received</th>
                    <th>Balance</th>
                    <th>Work Status</th>
                    <th>Payment</th>
                    <th>Operator</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="counter-tbody">
                @forelse($recentApplications as $app)
                    <tr>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">
                                {{ $app->application_number }}
                            </a>
                        </td>
                        <td><strong>{{ $app->customer->name }}</strong></td>
                        <td><span style="font-family: var(--font-mono);">{{ $app->customer->mobile }}</span></td>
                        <td>{{ $app->service->full_name }}</td>
                        <td>₹{{ number_format($app->total_amount, 2) }}</td>
                        <td style="color: #166534; font-weight: 700;">₹{{ number_format($app->received_amount, 2) }}</td>
                        <td style="color: {{ $app->remaining_amount > 0 ? '#dc2626' : '#166534' }};">₹{{ number_format($app->remaining_amount, 2) }}</td>
                        <td><span class="badge badge-process">{{ $app->work_status }}</span></td>
                        <td><span class="badge badge-paid">{{ $app->payment_status }}</span></td>
                        <td>{{ $app->assignedEmployee?->user?->name ?? 'Staff' }}</td>
                        <td>
                            <a href="{{ route('applications.show', $app->id) }}" class="btn btn-secondary btn-sm">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr id="empty-row">
                        <td colspan="11" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            No counter entries recorded today yet. Type in the row above to start fast logging!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function onServiceChange(selectEl) {
    const selected = selectEl.options[selectEl.selectedIndex];
    const price = selected.getAttribute('data-price') || 0;
    document.getElementById('fe_base_amount').value = price;
    document.getElementById('fe_received_amount').value = price;
}

function saveFastRow(e) {
    e.preventDefault();
    const btn = document.getElementById('fe_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    const payload = {
        _token: '{{ csrf_token() }}',
        customer_name: document.getElementById('fe_name').value,
        mobile: document.getElementById('fe_mobile').value,
        gender: document.getElementById('fe_gender').value,
        service_id: document.getElementById('fe_service').value,
        base_amount: document.getElementById('fe_base_amount').value,
        received_amount: document.getElementById('fe_received_amount').value,
        payment_mode: document.getElementById('fe_paymode').value,
        assigned_employee_id: document.getElementById('fe_employee').value || null,
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
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Save Row';

        if (res.success) {
            const tbody = document.getElementById('counter-tbody');
            const emptyRow = document.getElementById('empty-row');
            if (emptyRow) emptyRow.remove();

            const d = res.data;
            const tr = document.createElement('tr');
            tr.style.backgroundColor = '#ecfdf5';
            tr.innerHTML = `
                <td><a href="${d.url}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">${d.sr_number}</a></td>
                <td><strong>${d.customer_name}</strong></td>
                <td><span style="font-family: var(--font-mono);">${d.mobile}</span></td>
                <td>${d.service_name}</td>
                <td>₹${d.total_amount}</td>
                <td style="color: #166534; font-weight: 700;">₹${d.received_amount}</td>
                <td style="color: ${parseFloat(d.remaining_amount) > 0 ? '#dc2626' : '#166534'};">₹${d.remaining_amount}</td>
                <td><span class="badge badge-process">${d.work_status}</span></td>
                <td><span class="badge badge-paid">${d.payment_status}</span></td>
                <td>${d.employee_name}</td>
                <td><a href="${d.url}" class="btn btn-secondary btn-sm">Open</a></td>
            `;

            tbody.insertBefore(tr, tbody.firstChild);

            // Reset form for next customer
            document.getElementById('fe_name').value = '';
            document.getElementById('fe_mobile').value = '';
            document.getElementById('fe_service').value = '';
            document.getElementById('fe_base_amount').value = '';
            document.getElementById('fe_received_amount').value = '';
            document.getElementById('fe_name').focus();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Save Row';
        alert('Failed to save row. Please check connection.');
    });
}

// Global Keyboard Shortcut: Ctrl + Enter
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        document.getElementById('fast-entry-form').requestSubmit();
    }
});
</script>
@endpush
@endsection
