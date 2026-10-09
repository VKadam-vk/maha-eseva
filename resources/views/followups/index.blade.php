@extends('layouts.app')

@section('title', 'Follow-ups & Reminders')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Follow-ups & Citizen Reminders</h1>
        <p class="page-subtitle">Track pending document submissions, payment reminders and citizen callback schedules</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openFollowupModal()">
        <i class="fa-solid fa-plus"></i> Schedule Follow-up
    </button>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('followups.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
                    <option value="MISSED" {{ request('status') == 'MISSED' ? 'selected' : '' }}>MISSED</option>
                    <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                </select>
            </div>

            <div style="min-width: 160px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Follow-up Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>

            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('followups.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Scheduled Date</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Application SR</th>
                    <th>Reason</th>
                    <th>Remarks</th>
                    <th>Assigned Staff</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($followUps as $f)
                    <tr>
                        <td>
                            <strong>{{ $f->follow_up_date->format('d M Y') }}</strong>
                            @if($f->follow_up_time)
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ $f->follow_up_time }}</div>
                            @endif
                        </td>
                        <td>
                            @if($f->customer)
                                <a href="{{ route('customers.show', $f->customer_id) }}" style="font-weight: 700; color: var(--text-main);">
                                    {{ $f->customer->name }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>{{ $f->customer?->mobile ?? '-' }}</td>
                        <td>
                            @if($f->application)
                                <a href="{{ route('applications.show', $f->application_id) }}" style="font-family: var(--font-mono); font-weight: 600; color: #ea580c;">
                                    {{ $f->application->application_number }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td><strong>{{ $f->reason }}</strong></td>
                        <td>{{ $f->remarks ?? '-' }}</td>
                        <td>{{ $f->assignedEmployee?->user?->name ?? 'Unassigned' }}</td>
                        <td>
                            @php
                                $fBadge = match($f->status) {
                                    'COMPLETED' => 'badge-paid',
                                    'MISSED', 'CANCELLED' => 'badge-pending',
                                    default => 'badge-process'
                                };
                            @endphp
                            <span class="badge {{ $fBadge }}">{{ $f->status }}</span>
                        </td>
                        <td>
                            @if($f->status === 'PENDING')
                                <form action="{{ route('followups.status', $f->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="COMPLETED">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Complete</button>
                                </form>
                            @else
                                <span style="font-size: 12px; color: var(--text-muted);">Resolved</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 24px; color: var(--text-muted);">No follow-ups recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($followUps->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $followUps->links() }}
        </div>
    @endif
</div>

<!-- Schedule Modal -->
<div id="newFollowupModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-calendar-plus"></i> Schedule Follow-up</h3>
            <button type="button" onclick="closeModal('newFollowupModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('followups.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->mobile }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time</label>
                        <input type="time" name="follow_up_time" class="form-control" value="11:00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Reason</label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Document Collection, Balance Due, Acknowledgement Handover">
                </div>

                <div class="form-group">
                    <label class="form-label">Assigned Staff</label>
                    <select name="assigned_employee_id" class="form-select">
                        <option value="">-- Staff --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Instructions..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newFollowupModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Follow-up</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openFollowupModal() { document.getElementById('newFollowupModal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
@endpush
@endsection
