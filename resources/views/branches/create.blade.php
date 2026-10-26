@extends('layouts.app')

@section('title', 'Add New Branch')

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px; font-size: 22px;">
            <i class="fa-solid fa-building" style="color: #4f46e5;"></i>
            Add New Digital Service Branch
        </h1>
        <p class="page-subtitle">Configure branch location, owner identity and legal shop registration</p>
    </div>
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Branches
    </a>
</div>

<div class="erp-card" style="max-width: 780px; margin: 0 auto; border: 1.5px solid #cbd5e1; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.05); overflow: hidden;">
    <div class="erp-card-header" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); padding: 14px 22px; color: white;">
        <h3 class="erp-card-title" style="color: white; margin: 0; font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-id-card"></i> Branch & Owner Details
        </h3>
    </div>
    
    <div class="erp-card-body" style="padding: 26px;">
        <form action="{{ route('branches.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- 1. Branch Name -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-building" style="color: #4f46e5;"></i> Branch Name
                </label>
                <input type="text" name="name" class="form-control" required 
                       placeholder="e.g. Kothrud Branch / Pune FC Road Center" 
                       value="{{ old('name') }}"
                       style="height: 44px; font-size: 14px; font-weight: 600; width: 100%;">
                <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">Official display name for this center/branch.</small>
            </div>

            <!-- 2. Branch Code -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-barcode" style="color: #4f46e5;"></i> Branch Code
                </label>
                <input type="text" name="branch_code" class="form-control" required 
                       placeholder="e.g. PUNE-KT-04" 
                       value="{{ old('branch_code') }}"
                       style="height: 44px; font-size: 14px; font-weight: 700; text-transform: uppercase; font-family: var(--font-mono); width: 100%;">
                <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">Unique identifier prefix used for receipts and records.</small>
            </div>

            <!-- Business Owner & Legal Section Divider -->
            <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; display: flex; flex-direction: column; gap: 16px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1e40af; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-user-shield"></i> Owner Identity & Shop Licence Details
                </div>

                <!-- 3. Owner Name -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-user-tie" style="color: #2563eb;"></i> Owner Name (मालकाचे नाव)
                    </label>
                    <input type="text" name="owner_name" class="form-control" 
                           placeholder="e.g. Vinod Kadam" 
                           value="{{ old('owner_name') }}"
                           style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
                </div>

                <!-- 4. PAN Number -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-credit-card" style="color: #d97706;"></i> PAN Number (पॅन कार्ड क्रमांक)
                    </label>
                    <input type="text" name="pan_number" class="form-control" maxlength="10"
                           placeholder="e.g. ABCDE1234F" 
                           value="{{ old('pan_number') }}"
                           style="height: 44px; font-size: 14px; font-weight: 700; text-transform: uppercase; font-family: var(--font-mono); width: 100%;">
                </div>

                <!-- 5. Aadhaar Number -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-fingerprint" style="color: #16a34a;"></i> Aadhaar Number (आधार क्रमांक)
                    </label>
                    <input type="text" name="aadhaar_number" class="form-control" maxlength="14"
                           placeholder="12 Digits (e.g. 1234 5678 9012)" 
                           value="{{ old('aadhaar_number') }}"
                           style="height: 44px; font-size: 14px; font-weight: 600; font-family: var(--font-mono); width: 100%;">
                </div>

                <!-- 6. Shop Licence Number -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-certificate" style="color: #7c3aed;"></i> Shop Licence / Gumasta Number (दुकान परवाना क्रमांक)
                    </label>
                    <input type="text" name="shop_licence_number" class="form-control" 
                           placeholder="e.g. SHOP/ACT/MH/2026/8945" 
                           value="{{ old('shop_licence_number') }}"
                           style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
                </div>
            </div>

            <!-- 7. Contact Person / Branch Head -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-user-gear" style="color: #475569;"></i> Contact Person / Branch Head
                </label>
                <input type="text" name="contact_person" class="form-control" 
                       placeholder="e.g. Sachin More" 
                       value="{{ old('contact_person') }}"
                       style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
            </div>

            <!-- 8. Contact Mobile -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-phone" style="color: #16a34a;"></i> Contact Mobile
                </label>
                <input type="tel" name="contact_mobile" class="form-control" 
                       placeholder="10 Digits (e.g. 98XXXXXXXX)" 
                       value="{{ old('contact_mobile') }}"
                       style="height: 44px; font-size: 14px; font-family: var(--font-mono); width: 100%;">
            </div>

            <!-- 9. Contact Email -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-envelope" style="color: #2563eb;"></i> Contact Email
                </label>
                <input type="email" name="contact_email" class="form-control" 
                       placeholder="kothrud@mahaeseva.com" 
                       value="{{ old('contact_email') }}"
                       style="height: 44px; font-size: 13.5px; width: 100%;">
            </div>

            <!-- 10. Physical Address -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-location-dot" style="color: #dc2626;"></i> Physical Address
                </label>
                <textarea name="address" class="form-control" rows="3" 
                          placeholder="Complete physical address of center / shop premises"
                          style="font-size: 13.5px; width: 100%; border-radius: 6px;">{{ old('address') }}</textarea>
            </div>

            <!-- 11. City -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-city" style="color: #475569;"></i> City
                </label>
                <input type="text" name="city" class="form-control" 
                       value="{{ old('city', 'Pune') }}"
                       style="height: 44px; font-size: 13.5px; width: 100%;">
            </div>

            <!-- 12. District -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-map-location-dot" style="color: #475569;"></i> District
                </label>
                <input type="text" name="district" class="form-control" 
                       value="{{ old('district', 'Pune') }}"
                       style="height: 44px; font-size: 13.5px; width: 100%;">
            </div>

            <!-- 13. Pincode -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-map-pin" style="color: #d97706;"></i> Pincode
                </label>
                <input type="text" name="pincode" class="form-control" 
                       placeholder="411038" 
                       value="{{ old('pincode') }}"
                       style="height: 44px; font-size: 14px; font-family: var(--font-mono); width: 100%;">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary" style="padding: 10px 22px;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 26px; font-weight: 700; background: #4f46e5; border-color: #3730a3; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                    <i class="fa-solid fa-check"></i> Register Branch
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
