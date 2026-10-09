@extends('layouts.app')

@section('title', 'Add New Branch')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add New Digital Service Branch</h1>
        <p class="page-subtitle">Configure branch location and regional head</p>
    </div>
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Branches
    </a>
</div>

<div class="erp-card" style="max-width: 800px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-building"></i> Branch Details</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('branches.store') }}" method="POST">
            @csrf

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required">Branch Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Kothrud Branch" value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Branch Code</label>
                    <input type="text" name="branch_code" class="form-control" required placeholder="e.g. PUNE-KT-04" value="{{ old('branch_code') }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">Contact Person / Branch Head</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. Sachin More" value="{{ old('contact_person') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Mobile</label>
                    <input type="tel" name="contact_mobile" class="form-control" placeholder="98XXXXXXXX" value="{{ old('contact_mobile') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" placeholder="kothrud@mahaeseva.com" value="{{ old('contact_email') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Physical Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Complete address of center">{{ old('address') }}</textarea>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', 'Pune') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" value="{{ old('district', 'Pune') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" placeholder="411038" value="{{ old('pincode') }}">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Register Branch</button>
            </div>
        </form>
    </div>
</div>
@endsection
