@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Customer: {{ $customer->name }}</h1>
        <p class="page-subtitle">Customer Code: {{ $customer->customer_code }}</p>
    </div>
    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Profile
    </a>
</div>

<div class="erp-card" style="max-width: 800px;">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-user-pen"></i> Update Profile Information</h3>
    </div>
    <div class="erp-card-body">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required">Customer Full Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $customer->name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label required">Mobile Number</label>
                    <input type="tel" name="mobile" class="form-control" required value="{{ old('mobile', $customer->mobile) }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label required">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="MALE" {{ old('gender', $customer->gender) == 'MALE' ? 'selected' : '' }}>Male</option>
                        <option value="FEMALE" {{ old('gender', $customer->gender) == 'FEMALE' ? 'selected' : '' }}>Female</option>
                        <option value="OTHER" {{ old('gender', $customer->gender) == 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Regular Citizen</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $customer->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Alternate Mobile</label>
                    <input type="tel" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $customer->alternate_mobile) }}">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" value="{{ old('district', $customer->district) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $customer->pincode) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
