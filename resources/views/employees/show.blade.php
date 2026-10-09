@extends('layouts.app')

@section('title', 'Staff: ' . $employee->user->name)

@section('content')
<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title">{{ $employee->user->name }}</h1>
            <span class="badge badge-ready" style="font-family: var(--font-mono);">{{ $employee->employee_code }}</span>
            <span class="badge badge-paid">{{ $employee->status }}</span>
        </div>
        <p class="page-subtitle">{{ $employee->designation }} • {{ $employee->branch->name }} • {{ $employee->user->email }}</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary" onclick="openTaskModal()">
            <i class="fa-solid fa-plus"></i> Assign Task
        </button>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Staff Directory
        </a>
    </div>
</div>

<div class="form-grid-2">
    <!-- Active Tasks Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-list-check"></i> Assigned Work Tasks</h3>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->tasks as $t)
                        <tr>
                            <td>
                                <strong>{{ $t->title }}</strong>
                                @if($t->description)
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $t->description }}</div>
                                @endif
                            </td>
                            <td><span class="badge badge-process">{{ $t->priority }}</span></td>
                            <td>{{ $t->due_date ? $t->due_date->format('d M Y') : '-' }}</td>
                            <td><span class="badge badge-paid">{{ $t->status }}</span></td>
                            <td>
                                @if($t->status !== 'COMPLETED')
                                    <form action="{{ route('employees.tasks.status', $t->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="COMPLETED">
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No tasks assigned currently.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Assigned Applications -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-file-signature"></i> Assigned Applications ({{ $employee->applications->count() }})</h3>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>SR Number</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->applications as $app)
                        <tr>
                            <td>
                                <a href="{{ route('applications.show', $app->id) }}" style="font-family: var(--font-mono); font-weight: 700; color: #ea580c;">
                                    {{ $app->application_number }}
                                </a>
                            </td>
                            <td>{{ $app->customer->name }}</td>
                            <td>{{ $app->service->main_service_name }}</td>
                            <td><span class="badge badge-process">{{ $app->work_status }}</span></td>
                            <td>
                                <a href="{{ route('applications.show', $app->id) }}" class="btn btn-secondary btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No applications assigned.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Task Modal -->
<div id="taskModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700;"><i class="fa-solid fa-list-check"></i> Assign Task to {{ $employee->user->name }}</h3>
            <button type="button" onclick="closeModal('taskModal')" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('employees.tasks.store', $employee->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Task Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Upload biometric acknowledgment for Rent Agreement">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required">Priority</label>
                        <select name="priority" class="form-select" required>
                            <option value="LOW">Low</option>
                            <option value="MEDIUM" selected>Medium</option>
                            <option value="HIGH">High</option>
                            <option value="URGENT">Urgent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Task Instructions / Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Details..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('taskModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Assign Task</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openTaskModal() { document.getElementById('taskModal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
@endpush
@endsection
