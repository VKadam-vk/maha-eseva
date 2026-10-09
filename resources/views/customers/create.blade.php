@extends('layouts.app')

@section('title', 'Quick Add Customer')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Quick Customer Registration</h1>
        <p class="page-subtitle">Create new customer profile or search existing customer by mobile</p>
    </div>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Directory
    </a>
</div>

<!-- Duplicate Detection Alert Box -->
<div id="duplicate-alert" class="alert alert-danger" style="display: none; border-left: 4px solid #dc2626;">
    <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
    <div style="flex-grow: 1;">
        <strong>Customer already exists!</strong> A customer with this mobile number is already registered as:
        <span id="dup-name" style="font-weight: 700;"></span> (<span id="dup-code" style="font-family: var(--font-mono);"></span>).
    </div>
    <a id="dup-link" href="#" class="btn btn-danger btn-sm">
        <i class="fa-solid fa-arrow-right"></i> Open Existing Profile
    </a>
</div>

<div class="erp-card" style="max-width: 800px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-user-plus"></i> Customer Details</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <!-- Minimum Required Fields -->
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required">Customer Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Ramesh Shankar Patil" value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Mobile Number (Primary)</label>
                    <input type="tel" id="mobile_input" name="mobile" class="form-control" required placeholder="10-digit mobile number" value="{{ old('mobile') }}" maxlength="15">
                    <span id="dup-status" style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Real-time duplicate check active</span>
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label required">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="MALE" {{ old('gender') == 'MALE' ? 'selected' : '' }}>Male</option>
                        <option value="FEMALE" {{ old('gender') == 'FEMALE' ? 'selected' : '' }}>Female</option>
                        <option value="OTHER" {{ old('gender') == 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Regular Citizen</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Alternate Mobile</label>
                    <input type="tel" name="alternate_mobile" class="form-control" placeholder="Optional" value="{{ old('alternate_mobile') }}">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="customer@example.com" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="House/Flat No, Street, Landmark">{{ old('address') }}</textarea>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">City / Village</label>
                    <input type="text" name="city" class="form-control" placeholder="Pune" value="{{ old('city', 'Pune') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" placeholder="Pune" value="{{ old('district', 'Pune') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" placeholder="411001" value="{{ old('pincode') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Internal Remarks / Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions or reference...">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Register Customer Profile
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let debounceTimer;
document.getElementById('mobile_input').addEventListener('input', function() {
    const mobile = this.value.trim();
    const alertBox = document.getElementById('duplicate-alert');
    const statusText = document.getElementById('dup-status');
    const submitBtn = document.getElementById('submit_btn');

    clearTimeout(debounceTimer);

    if (mobile.length < 10) {
        alertBox.style.display = 'none';
        statusText.innerHTML = 'Enter 10-digit mobile';
        statusText.style.color = 'var(--text-muted)';
        return;
    }

    statusText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking duplicate...';

    debounceTimer = setTimeout(() => {
        fetch(`{{ route('customers.check_duplicate') }}?mobile=${encodeURIComponent(mobile)}`)
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    alertBox.style.display = 'flex';
                    document.getElementById('dup-name').textContent = data.customer.name;
                    document.getElementById('dup-code').textContent = data.customer.customer_code;
                    document.getElementById('dup-link').href = data.customer.url;
                    statusText.innerHTML = '<span style="color: #dc2626; font-weight: 700;"><i class="fa-solid fa-circle-xmark"></i> Mobile number already registered!</span>';
                } else {
                    alertBox.style.display = 'none';
                    statusText.innerHTML = '<span style="color: #10b981; font-weight: 700;"><i class="fa-solid fa-circle-check"></i> Mobile number is available.</span>';
                }
            })
            .catch(() => {
                statusText.innerHTML = 'Duplicate check error';
            });
    }, 400);
});
</script>
@endpush
@endsection
