@extends('layouts.app')

@section('title', 'Organization Settings')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Organization & Kendra Settings</h1>
        <p class="page-subtitle">Configure business branding, GST details, thermal receipt headers, and notification APIs</p>
    </div>
</div>

<div class="erp-card" style="max-width: 800px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-sliders"></i> Kendra Branding & Information</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label required">Business / Kendra Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $tenant->name) }}">
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label required">Owner / Primary Contact</label>
                    <input type="text" name="contact_name" class="form-control" required value="{{ old('contact_name', $tenant->contact_name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Contact Mobile</label>
                    <input type="tel" name="contact_mobile" class="form-control" required value="{{ old('contact_mobile', $tenant->contact_mobile) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" required value="{{ old('contact_email', $tenant->contact_email) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Headquarters Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $tenant->address) }}</textarea>
            </div>

            <div class="form-grid-4">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $tenant->city) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" value="{{ old('district', $tenant->district) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $tenant->pincode) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $tenant->gst_number) }}">
                </div>
            </div>

            <div style="background-color: #f8fafc; border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin: 20px 0;">
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: #1e293b;">
                    <i class="fa-solid fa-comment-sms" style="color: #4f46e5;"></i> Notification & SMS Provider Configuration
                </h4>
                <div class="form-grid-2">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">SMS Sender Header (6 Chars)</label>
                        <input type="text" name="sms_sender_id" class="form-control" value="{{ old('sms_sender_id', $tenant->settings_json['sms_sender_id'] ?? 'MHSEVA') }}" maxlength="6">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">WhatsApp Cloud API Key</label>
                        <input type="password" name="whatsapp_api_key" class="form-control" placeholder="••••••••••••••••" value="{{ old('whatsapp_api_key', $tenant->settings_json['whatsapp_api_key'] ?? '') }}">
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Organization Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
