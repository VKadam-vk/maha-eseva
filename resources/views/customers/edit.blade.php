@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: #0f172a;">Edit Customer: {{ $customer->name }}</h1>
        <p class="page-subtitle" style="font-size: 13px; color: #64748b; margin-top: 4px;">Customer Code: <span class="font-mono font-bold" style="color: #0284c7;">{{ $customer->customer_code }}</span></p>
    </div>
    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Profile
    </a>
</div>

<div class="erp-card" style="max-width: 760px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06); overflow: hidden;">
    <div class="erp-card-header" style="background: #0f172a; color: #f8fafc; padding: 16px 22px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-user-pen" style="color: #38bdf8; font-size: 17px;"></i>
        <h3 class="erp-card-title" style="font-size: 15px; font-weight: 800; margin: 0; color: #f8fafc; letter-spacing: 0.02em;">
            Update Profile Information (एका खाली एक मांडणी)
        </h3>
    </div>
    <div class="erp-card-body" style="padding: 24px 28px;">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- 1. Customer Full Name -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-user" style="color: #0284c7; width: 16px;"></i> Customer Full Name <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $customer->name) }}"
                       placeholder="Enter full name"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 2. Mobile Number -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-phone" style="color: #16a34a; width: 16px;"></i> Mobile Number <span style="color: #ef4444;">*</span>
                </label>
                <input type="tel" name="mobile" class="form-control" required value="{{ old('mobile', $customer->mobile) }}"
                       placeholder="10-digit mobile number" maxlength="15"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 3. Alternate Mobile -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-mobile-screen" style="color: #64748b; width: 16px;"></i> Alternate Mobile
                </label>
                <input type="tel" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $customer->alternate_mobile) }}"
                       placeholder="Optional secondary mobile number" maxlength="15"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 4. Gender -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label required" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-venus-mars" style="color: #8b5cf6; width: 16px;"></i> Gender <span style="color: #ef4444;">*</span>
                </label>
                <select name="gender" class="form-select" required
                        style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box; cursor: pointer;">
                    <option value="MALE" {{ old('gender', $customer->gender) == 'MALE' ? 'selected' : '' }}>Male</option>
                    <option value="FEMALE" {{ old('gender', $customer->gender) == 'FEMALE' ? 'selected' : '' }}>Female</option>
                    <option value="OTHER" {{ old('gender', $customer->gender) == 'OTHER' ? 'selected' : '' }}>Other</option>
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
                        <option value="{{ $cat->id }}" {{ old('category_id', $customer->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 6. Birth Date -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-regular fa-calendar" style="color: #06b6d4; width: 16px;"></i> Birth Date
                </label>
                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box; cursor: pointer;">
            </div>

            <!-- 7. Email Address -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-envelope" style="color: #6366f1; width: 16px;"></i> Email Address
                </label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}"
                       placeholder="customer@example.com"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 8. Address -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-location-dot" style="color: #ef4444; width: 16px;"></i> Address
                </label>
                <textarea name="address" class="form-control" rows="2" placeholder="House/Flat No, Street, Landmark"
                          style="width: 100%; min-height: 75px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500; color: #0f172a; background: #ffffff; box-sizing: border-box; resize: vertical;">{{ old('address', $customer->address) }}</textarea>
            </div>

            <!-- 9. City / Village -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-city" style="color: #0284c7; width: 16px;"></i> City / Village
                </label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}"
                       placeholder="Enter city or village name"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 10. District -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-map-location-dot" style="color: #10b981; width: 16px;"></i> District
                </label>
                <input type="text" name="district" class="form-control" value="{{ old('district', $customer->district) }}"
                       placeholder="Enter district name"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 11. Pincode -->
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-solid fa-map-pin" style="color: #f97316; width: 16px;"></i> Pincode
                </label>
                <input type="text" name="pincode" class="form-control font-mono" value="{{ old('pincode', $customer->pincode) }}"
                       placeholder="6-digit pincode e.g. 411001" maxlength="10"
                       style="width: 100%; height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; color: #0f172a; background: #ffffff; box-sizing: border-box;">
            </div>

            <!-- 12. Notes -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    <i class="fa-regular fa-note-sticky" style="color: #64748b; width: 16px;"></i> Notes / Citizen Remarks
                </label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Any special notes or reference..."
                          style="width: 100%; min-height: 75px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500; color: #0f172a; background: #ffffff; box-sizing: border-box; resize: vertical;">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <!-- Form Action Buttons -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding-top: 18px; border-top: 1.5px solid #f1f5f9;">
                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary" style="height: 44px; padding: 0 20px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 8px;">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" style="height: 44px; padding: 0 26px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 8px; gap: 8px; background: #0284c7; border-color: #0284c7;">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
