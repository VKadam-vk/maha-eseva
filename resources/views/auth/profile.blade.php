@extends('layouts.app')

@section('title', 'My Profile & Security')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">My Profile & Security Settings</h1>
        <p class="page-subtitle">Manage your account authentication and personal operator details</p>
    </div>
</div>

<div class="form-grid-2">
    <!-- User Info Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-user-shield"></i> Account Information</h3>
        </div>
        <div class="erp-card-body">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <div class="user-avatar" style="width: 64px; height: 64px; font-size: 24px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 700;">{{ $user->name }}</h2>
                    <p style="font-size: 13px; color: var(--text-muted);">{{ $user->email }} | {{ $user->mobile }}</p>
                    <span class="badge badge-approved" style="margin-top: 4px;">{{ $user->roles->first()?->name ?? 'User' }}</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; border-top: 1px solid var(--surface-border); padding-top: 16px;">
                <div><strong>Organization:</strong> {{ $user->tenant->name ?? 'Global Platform' }}</div>
                <div><strong>Branch Assignment:</strong> {{ $user->branch->name ?? 'All Branches (Full Access)' }}</div>
                <div><strong>Last Login:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : 'First session' }}</div>
                <div><strong>Last IP Address:</strong> {{ $user->last_login_ip ?? '127.0.0.1' }}</div>
                <div><strong>Account Status:</strong> <span class="badge badge-paid">{{ $user->status }}</span></div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-key"></i> Update Security Password</h3>
        </div>
        <div class="erp-card-body">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label required">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                </div>

                <div class="form-group">
                    <label class="form-label required">New Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
                </div>

                <div class="form-group">
                    <label class="form-label required">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Re-enter new password">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-lock"></i> Save New Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
