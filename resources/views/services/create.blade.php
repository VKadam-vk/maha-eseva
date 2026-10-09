@extends('layouts.app')

@section('title', 'Add New Service')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Service Master Entry</h1>
        <p class="page-subtitle">Configure pricing, government fees, and portal integration details</p>
    </div>
    <a href="{{ route('services.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Services
    </a>
</div>

<div class="erp-card" style="max-width: 850px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-list-check"></i> Service Specifications</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('services.store') }}" method="POST">
            @csrf

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label required">Service Category</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Service Code</label>
                    <input type="text" name="service_code" class="form-control" required placeholder="e.g. PAN-NEW-01" value="{{ old('service_code') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Expected Processing (Days)</label>
                    <input type="number" name="expected_processing_days" class="form-control" required value="{{ old('expected_processing_days', 7) }}">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required">Main Service Name</label>
                    <input type="text" name="main_service_name" class="form-control" required placeholder="e.g. PAN CARD, RENT AGREEMENT" value="{{ old('main_service_name') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Sub-Service Name</label>
                    <input type="text" name="sub_service_name" class="form-control" required placeholder="e.g. New PAN Application Form 49A" value="{{ old('sub_service_name') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Service Variant / Details</label>
                <input type="text" name="service_variant" class="form-control" placeholder="e.g. Physical + e-PAN, 1 Year Tahsildar" value="{{ old('service_variant') }}">
            </div>

            <!-- Financial Rules -->
            <div style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: #1e293b;">Fee Structure</h4>
                <div class="form-grid-3">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required">Total Customer Price (₹)</label>
                        <input type="number" step="0.01" id="price_input" name="price" class="form-control" required placeholder="0.00" value="{{ old('price', 0.00) }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required">Government Fee (₹)</label>
                        <input type="number" step="0.01" id="govt_fee_input" name="govt_fee" class="form-control" required placeholder="0.00" value="{{ old('govt_fee', 0.00) }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required">Service Charge / Margin (₹)</label>
                        <input type="number" step="0.01" id="service_charge_input" name="service_charge" class="form-control" required placeholder="0.00" value="{{ old('service_charge', 0.00) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Required Documents List (One document per line)</label>
                <textarea name="required_documents" class="form-control" rows="3" placeholder="Aadhaar Card&#10;Passport Size Photograph&#10;Signature on White Paper">{{ old('required_documents') }}</textarea>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Government Portal Link</label>
                    <input type="url" name="service_portal_link" class="form-control" placeholder="https://aaplesarkar.mahaonline.gov.in" value="{{ old('service_portal_link') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Operator Work Portal Link</label>
                    <input type="url" name="work_portal_link" class="form-control" placeholder="https://..." value="{{ old('work_portal_link') }}">
                </div>
            </div>

            <!-- Encrypted Portal Credentials -->
            <div style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 4px; color: #1e293b;">
                    <i class="fa-solid fa-lock" style="color: #ea580c;"></i> Encrypted Operator Portal Credentials
                </h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">Saved securely with Laravel application-level AES-256-GCM encryption.</p>
                <div class="form-grid-2">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Portal Username</label>
                        <input type="text" name="portal_username" class="form-control" placeholder="e.g. maha_kendra_pune">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Portal Password</label>
                        <input type="password" name="portal_password" class="form-control" placeholder="••••••••">
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Service</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('govt_fee_input').addEventListener('input', calcTotal);
document.getElementById('service_charge_input').addEventListener('input', calcTotal);

function calcTotal() {
    const govt = parseFloat(document.getElementById('govt_fee_input').value) || 0;
    const srv = parseFloat(document.getElementById('service_charge_input').value) || 0;
    document.getElementById('price_input').value = (govt + srv).toFixed(2);
}
</script>
@endpush
@endsection
