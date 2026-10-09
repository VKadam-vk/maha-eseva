@extends('layouts.app')

@section('title', 'Edit Branch: ' . $branch->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Branch: {{ $branch->name }}</h1>
        <p class="page-subtitle">Branch Code: {{ $branch->branch_code }}</p>
    </div>
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Branches
    </a>
</div>

<div class="erp-card" style="max-width: 800px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-pen-to-square"></i> Branch Details</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('branches.update', $branch->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required">Branch Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $branch->name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Branch Code</label>
                    <input type="text" name="branch_code" class="form-control" required value="{{ old('branch_code', $branch->branch_code) }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $branch->contact_person) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Mobile</label>
                    <input type="tel" name="contact_mobile" class="form-control" value="{{ old('contact_mobile', $branch->contact_mobile) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $branch->contact_email) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $branch->address) }}</textarea>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $branch->city) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" value="{{ old('district', $branch->district) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="ACTIVE" {{ $branch->status === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ $branch->status === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
