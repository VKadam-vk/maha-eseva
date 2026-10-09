@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Tax / Service Invoice</h1>
        <p class="page-subtitle">Generate an official itemized bill with GST tax rules</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Invoices
    </a>
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-file-invoice-dollar"></i> Invoice Specifications</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label required">Customer</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->mobile }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                </div>
            </div>

            <!-- Items Table -->
            <div style="margin: 20px 0;">
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px;">Invoice Line Items</h4>
                <div class="table-responsive">
                    <table class="erp-table" id="items-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Service / Item Description</th>
                                <th style="width: 15%;">Unit Price (₹)</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 15%;">GST Tax (%)</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <tr>
                                <td>
                                    <input type="text" name="items[0][description]" class="form-control" required placeholder="Service description (e.g. PAN Card Processing)">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="form-control" required value="200.00">
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control" required value="1" min="1">
                                </td>
                                <td>
                                    <select name="items[0][tax_percentage]" class="form-select">
                                        <option value="0">0% (Nil)</option>
                                        <option value="5">5% GST</option>
                                        <option value="12">12% GST</option>
                                        <option value="18" selected>18% GST</option>
                                    </select>
                                </td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 10px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addItemRow()">
                        <i class="fa-solid fa-plus"></i> Add Another Line Item
                    </button>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Discount Amount (₹)</label>
                    <input type="number" step="0.01" name="discount_amount" class="form-control" value="0.00">
                </div>

                <div class="form-group">
                    <label class="form-label">Customer Notes / Payment Terms</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Thank you for using Maha E-Seva. Payment due within 7 days."></textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Generate Invoice</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;
function addItemRow() {
    const tbody = document.getElementById('items-tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="items[${itemIndex}][description]" class="form-control" required placeholder="Service description"></td>
        <td><input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control" required value="100.00"></td>
        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control" required value="1" min="1"></td>
        <td>
            <select name="items[${itemIndex}][tax_percentage]" class="form-select">
                <option value="0">0%</option>
                <option value="5">5%</option>
                <option value="12">12%</option>
                <option value="18" selected>18%</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    itemIndex++;
}
</script>
@endpush
@endsection
