@extends('layouts.app')

@section('title', $service->full_name . ' - Fields Configuration')

@section('content')
<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title">{{ $service->main_service_name }}</h1>
            <span class="badge badge-ready" style="font-family: var(--font-mono);">{{ $service->service_code }}</span>
            <span class="badge {{ $service->is_active ? 'badge-paid' : 'badge-pending' }}">{{ $service->is_active ? 'ACTIVE' : 'INACTIVE' }}</span>
        </div>
        <p class="page-subtitle">{{ $service->sub_service_name }} ({{ $service->category->name }})</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('applications.create', ['service_id' => $service->id]) }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Application for Service
        </a>
        <a href="{{ route('services.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> All Services
        </a>
    </div>
</div>

<div class="form-grid-2">
    <!-- Service Summary Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-circle-info"></i> Service Overview & Fees</h3>
        </div>
        <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
            <div><strong>Main Service:</strong> {{ $service->main_service_name }}</div>
            <div><strong>Sub-Service:</strong> {{ $service->sub_service_name }}</div>
            <div><strong>Variant:</strong> {{ $service->service_variant ?? 'Standard' }}</div>
            <div><strong>Category:</strong> {{ $service->category->name }}</div>
            <div><strong>Expected Processing Time:</strong> {{ $service->expected_processing_days }} Days</div>
            <div style="border-top: 1px solid var(--surface-border); margin: 10px 0; padding-top: 10px;">
                <div><strong>Government Fee:</strong> ₹{{ number_format($service->govt_fee, 2) }}</div>
                <div><strong>Service Charge:</strong> ₹{{ number_format($service->service_charge, 2) }}</div>
                <div><strong>Total Customer Price:</strong> <strong style="color: #166534; font-size: 15px;">₹{{ number_format($service->price, 2) }}</strong></div>
            </div>

            @if($service->service_portal_link)
                <div style="margin-top: 10px;">
                    <a href="{{ $service->service_portal_link }}" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-external-link"></i> Official Portal
                    </a>
                </div>
            @endif

            @if(!empty($service->required_documents_json))
                <div style="margin-top: 16px;">
                    <strong>Required Documents Checklist:</strong>
                    <ul style="margin-left: 20px; margin-top: 6px;">
                        @foreach($service->required_documents_json as $docName)
                            <li>{{ $docName }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Dynamic Custom Field Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-plus"></i> Add Dynamic Custom Field</h3>
        </div>
        <div class="erp-card-body">
            <form action="{{ route('services.custom_fields.store', $service->id) }}" method="POST">
                @csrf
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Field Name / Label</label>
                        <input type="text" name="label" class="form-control" required placeholder="e.g. Aadhaar Number, Flat No" oninput="document.getElementById('field_key_input').value = this.value.toLowerCase().replace(/[^a-z0-9]/g, '_')">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Field Key (Unique identifier)</label>
                        <input type="text" id="field_key_input" name="field_key" class="form-control" required placeholder="e.g. aadhaar_number">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Field Type</label>
                        <select name="field_type" class="form-select" required>
                            <option value="text">Text (Single Line)</option>
                            <option value="textarea">Textarea (Multi-line)</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="datetime">Date & Time</option>
                            <option value="email">Email</option>
                            <option value="mobile">Mobile Number</option>
                            <option value="select">Dropdown Select</option>
                            <option value="radio">Radio Buttons</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="password">Password (Encrypted)</option>
                            <option value="url">Website URL</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $service->customFields->count() + 1 }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Options (For Select / Radio - Comma Separated)</label>
                    <input type="text" name="options" class="form-control" placeholder="Option 1, Option 2, Option 3">
                </div>

                <div class="form-group">
                    <label class="form-label">Placeholder Text</label>
                    <input type="text" name="placeholder" class="form-control" placeholder="Enter placeholder hint...">
                </div>

                <input type="hidden" name="field_name" value="Custom Field">

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;">
                        <input type="checkbox" name="is_required" value="1" checked> Mandatory / Required Field
                    </label>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Add Field to Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Configured Custom Fields Table -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fa-solid fa-sliders"></i> Active Configured Dynamic Form Fields ({{ $service->customFields->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Sort</th>
                    <th>Field Label</th>
                    <th>Field Key</th>
                    <th>Type</th>
                    <th>Options</th>
                    <th>Required?</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($service->customFields as $field)
                    <tr>
                        <td>{{ $field->sort_order }}</td>
                        <td><strong>{{ $field->label }}</strong></td>
                        <td><span style="font-family: var(--font-mono); font-size: 12px; color: #4f46e5;">{{ $field->field_key }}</span></td>
                        <td><span class="badge badge-process">{{ strtoupper($field->field_type) }}</span></td>
                        <td>
                            @if($field->options_json)
                                <span style="font-size: 12px;">{{ implode(', ', $field->options_json) }}</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            @if($field->is_required)
                                <span class="badge badge-pending">Required</span>
                            @else
                                <span class="badge badge-hold">Optional</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('services.custom_fields.destroy', $field->id) }}" method="POST" onsubmit="return confirm('Remove this dynamic field from service form?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            No dynamic fields configured. Standard service fields will apply.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
