@extends('layouts.app')

@section('title', 'Compliance & Security Audit Trail')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Compliance & Security Audit Trail</h1>
        <p class="page-subtitle">Append-only security log of logins, data updates, status transitions, and document downloads</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="erp-card" style="margin-bottom: 20px;">
    <div class="erp-card-body" style="padding: 16px 20px;">
        <form action="{{ route('audit.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search IP, User, Entity</label>
                <input type="text" name="search" class="form-control" placeholder="IP address, entity type..." value="{{ request('search') }}">
            </div>

            <div style="min-width: 220px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Audit Event Type</label>
                <select name="event" class="form-select">
                    <option value="">All Security Events</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ request('event') == $ev ? 'selected' : '' }}>{{ $ev }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('audit.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Event</th>
                    <th>User</th>
                    <th>Entity Type</th>
                    <th>Entity ID</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <strong>{{ $log->created_at->format('d M Y') }}</strong>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $log->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td>
                            @php
                                $badge = match($log->event) {
                                    'LOGIN' => 'badge-paid',
                                    'FAILED_LOGIN', 'LOCKED_LOGIN_ATTEMPT', 'DOCUMENT_DELETED' => 'badge-pending',
                                    'DOCUMENT_DOWNLOADED', 'DOCUMENT_VIEWED' => 'badge-ready',
                                    default => 'badge-process'
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $log->event }}</span>
                        </td>
                        <td>
                            <strong>{{ $log->user?->name ?? 'Unauthenticated / System' }}</strong>
                            @if($log->user)
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ $log->user->email }}</div>
                            @endif
                        </td>
                        <td><span style="font-family: var(--font-mono); font-size: 12px;">{{ class_basename($log->entity_type) ?: '-' }}</span></td>
                        <td><span style="font-family: var(--font-mono); font-size: 12px; font-weight: 700;">{{ $log->entity_id ?? '-' }}</span></td>
                        <td><span style="font-family: var(--font-mono); font-size: 12px;">{{ $log->ip_address ?? '127.0.0.1' }}</span></td>
                        <td>
                            <div style="max-width: 250px; font-size: 11px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?: 'Local' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">No audit logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border);">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
