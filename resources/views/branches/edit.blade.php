@extends('layouts.app')

@section('title', 'Edit Branch: ' . $branch->name)

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px; font-size: 22px;">
            <i class="fa-solid fa-pen-to-square" style="color: #4f46e5;"></i>
            Edit Branch: {{ $branch->name }}
        </h1>
        <p class="page-subtitle">Branch Code: {{ $branch->branch_code }} &bull; Update location, owner identity and shop registration</p>
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
        <form action="{{ route('branches.update', $branch->id) }}" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf
            @method('PUT')

            <!-- 1. Branch Name -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-building" style="color: #4f46e5;"></i> Branch Name
                </label>
                <input type="text" name="name" class="form-control" required 
                       value="{{ old('name', $branch->name) }}"
                       style="height: 44px; font-size: 14px; font-weight: 600; width: 100%;">
            </div>

            <!-- 2. Branch Code -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-barcode" style="color: #4f46e5;"></i> Branch Code
                </label>
                <input type="text" name="branch_code" class="form-control" required 
                       value="{{ old('branch_code', $branch->branch_code) }}"
                       style="height: 44px; font-size: 14px; font-weight: 700; text-transform: uppercase; font-family: var(--font-mono); width: 100%;">
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
                           value="{{ old('owner_name', $branch->owner_name) }}"
                           style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
                </div>

                <!-- 4. PAN Number -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-credit-card" style="color: #d97706;"></i> PAN Number (पॅन कार्ड क्रमांक)
                    </label>
                    <input type="text" name="pan_number" class="form-control" maxlength="10"
                           placeholder="e.g. ABCDE1234F" 
                           value="{{ old('pan_number', $branch->pan_number) }}"
                           style="height: 44px; font-size: 14px; font-weight: 700; text-transform: uppercase; font-family: var(--font-mono); width: 100%;">
                </div>

                <!-- 5. Aadhaar Number -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-fingerprint" style="color: #16a34a;"></i> Aadhaar Number (आधार क्रमांक)
                    </label>
                    <input type="text" name="aadhaar_number" class="form-control" maxlength="14"
                           placeholder="12 Digits (e.g. 1234 5678 9012)" 
                           value="{{ old('aadhaar_number', $branch->aadhaar_number) }}"
                           style="height: 44px; font-size: 14px; font-weight: 600; font-family: var(--font-mono); width: 100%;">
                </div>

                <!-- 6. Shop Licence Number -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-certificate" style="color: #7c3aed;"></i> Shop Licence / Gumasta Number (दुकान परवाना क्रमांक)
                    </label>
                    <input type="text" name="shop_licence_number" class="form-control" 
                           placeholder="e.g. SHOP/ACT/MH/2026/8945" 
                           value="{{ old('shop_licence_number', $branch->shop_licence_number) }}"
                           style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
                </div>
            </div>

            <!-- 7. Contact Person -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-user-gear" style="color: #475569;"></i> Contact Person / Branch Head
                </label>
                <input type="text" name="contact_person" class="form-control" 
                       value="{{ old('contact_person', $branch->contact_person) }}"
                       style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
            </div>

            <!-- 8. Contact Mobile -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-phone" style="color: #16a34a;"></i> Contact Mobile
                </label>
                <input type="tel" name="contact_mobile" class="form-control" 
                       value="{{ old('contact_mobile', $branch->contact_mobile) }}"
                       style="height: 44px; font-size: 14px; font-family: var(--font-mono); width: 100%;">
            </div>

            <!-- 9. Contact Email -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-envelope" style="color: #2563eb;"></i> Contact Email
                </label>
                <input type="email" name="contact_email" class="form-control" 
                       value="{{ old('contact_email', $branch->contact_email) }}"
                       style="height: 44px; font-size: 13.5px; width: 100%;">
            </div>

            <!-- 10. Address -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-location-dot" style="color: #dc2626;"></i> Physical Address
                </label>
                <textarea name="address" class="form-control" rows="3" 
                          style="font-size: 13.5px; width: 100%; border-radius: 6px;">{{ old('address', $branch->address) }}</textarea>
            </div>

            <!-- 11. City -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-city" style="color: #475569;"></i> City
                </label>
                <input type="text" name="city" class="form-control" 
                       value="{{ old('city', $branch->city) }}"
                       style="height: 44px; font-size: 13.5px; width: 100%;">
            </div>

            <!-- 12. District -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-map-location-dot" style="color: #475569;"></i> District
                </label>
                <input type="text" name="district" class="form-control" 
                       value="{{ old('district', $branch->district) }}"
                       style="height: 44px; font-size: 13.5px; width: 100%;">
            </div>

            <!-- 13. Pincode -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-map-pin" style="color: #d97706;"></i> Pincode
                </label>
                <input type="text" name="pincode" class="form-control" 
                       value="{{ old('pincode', $branch->pincode) }}"
                       style="height: 44px; font-size: 14px; font-family: var(--font-mono); width: 100%;">
            </div>

            <!-- 14. Status -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-toggle-on" style="color: #16a34a;"></i> Branch Status
                </label>
                <select name="status" class="form-select" required style="height: 44px; font-size: 13.5px; font-weight: 600; width: 100%;">
                    <option value="ACTIVE" {{ $branch->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (कार्यान्वित)</option>
                    <option value="INACTIVE" {{ $branch->status === 'INACTIVE' ? 'selected' : '' }}>INACTIVE (बंद / स्थगित)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary" style="padding: 10px 22px;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 26px; font-weight: 700; background: #4f46e5; border-color: #3730a3; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
