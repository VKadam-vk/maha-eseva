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

<div class="erp-card" style="max-width: 760px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06); overflow: hidden;">
    <div class="erp-card-header" style="background: #0f172a; color: #f8fafc; padding: 16px 22px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-user-plus" style="color: #38bdf8; font-size: 17px;"></i>
        <h3 class="erp-card-title" style="font-size: 15px; font-weight: 800; margin: 0; color: #f8fafc; letter-spacing: 0.02em;">
            Customer Details (एका खाली एक मांडणी)
        </h3>
    </div>
    <div class="erp-card-body" style="padding: 24px 28px;">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <!-- 1. Customer Full Name -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-user" style="color: #0284c7; width: 16px;"></i> Customer Full Name <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Ramesh Shankar Patil" value="{{ old('name') }}"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 2. Mobile Number (Primary) -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-phone" style="color: #16a34a; width: 16px;"></i> Mobile Number (Primary) <span style="color: #ef4444;">*</span>
                </label>
                <input type="tel" id="mobile_input" name="mobile" class="form-control" required placeholder="10-digit mobile number" value="{{ old('mobile') }}" maxlength="15"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
                <span id="dup-status" style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Real-time duplicate check active</span>
            </div>

            <!-- 3. Alternate Mobile -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-mobile-screen" style="color: #64748b; width: 16px;"></i> Alternate Mobile
                </label>
                <input type="tel" name="alternate_mobile" class="form-control" placeholder="Optional secondary mobile" value="{{ old('alternate_mobile') }}" maxlength="15"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 4. Gender -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-venus-mars" style="color: #8b5cf6; width: 16px;"></i> Gender <span style="color: #ef4444;">*</span>
                </label>
                <select name="gender" class="form-select" required
                        style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box; cursor: pointer;">
                    <option value="MALE" {{ old('gender') == 'MALE' ? 'selected' : '' }}>Male</option>
                    <option value="FEMALE" {{ old('gender') == 'FEMALE' ? 'selected' : '' }}>Female</option>
                    <option value="OTHER" {{ old('gender') == 'OTHER' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <!-- 5. Category -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-layer-group" style="color: #f59e0b; width: 16px;"></i> Customer Category
                </label>
                <select name="category_id" class="form-select"
                        style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box; cursor: pointer;">
                    <option value="">Regular Citizen</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 6. Birth Date -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-regular fa-calendar" style="color: #06b6d4; width: 16px;"></i> Birth Date
                </label>
                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box; cursor: pointer;">
            </div>

            <!-- 7. Email Address -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-envelope" style="color: #6366f1; width: 16px;"></i> Email Address
                </label>
                <input type="email" name="email" class="form-control" placeholder="customer@example.com" value="{{ old('email') }}"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 8. Address -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-location-dot" style="color: #ef4444; width: 16px;"></i> Address
                </label>
                <textarea name="address" class="form-control" rows="2" placeholder="House/Flat No, Street, Landmark"
                          style="width: 100%; min-height: 75px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500; color: #0f172a; background: #ffffff; box-sizing: border-box; resize: vertical;">{{ old('address') }}</textarea>
            </div>

            <!-- 9. City / Village -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-city" style="color: #0284c7; width: 16px;"></i> City / Village
                </label>
                <input type="text" name="city" class="form-control" placeholder="Pune" value="{{ old('city', 'Pune') }}"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 10. District -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-map-location-dot" style="color: #10b981; width: 16px;"></i> District
                </label>
                <input type="text" name="district" class="form-control" placeholder="Pune" value="{{ old('district', 'Pune') }}"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 11. Pincode -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-map-pin" style="color: #f97316; width: 16px;"></i> Pincode
                </label>
                <input type="text" name="pincode" class="form-control font-mono" placeholder="411001" value="{{ old('pincode') }}" maxlength="10"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 12. Notes -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-regular fa-note-sticky" style="color: #64748b; width: 16px;"></i> Internal Remarks / Notes
                </label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions or reference..."
                          style="width: 100%; min-height: 75px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500; color: #0f172a; background: #ffffff; box-sizing: border-box; resize: vertical;">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding-top: 18px; border-top: 1.5px solid #f1f5f9;">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary" style="height: 44px; padding: 0 20px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 8px;">Cancel</a>
                <button type="submit" id="submit_btn" class="btn btn-primary" style="height: 44px; padding: 0 26px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 8px; gap: 8px; background: #0284c7; border-color: #0284c7;">
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
