@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="auth-brand">
    <div class="auth-brand-icon">
        <i class="fa-solid fa-landmark"></i>
    </div>
    <div>
        <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1.1;">Maha E-Seva</h2>
        <span style="font-size: 12px; font-weight: 600; color: #ea580c; text-transform: uppercase;">ERP Management System</span>
    </div>
</div>

<h3 style="font-size: 18px; font-weight: 700; margin-bottom: 6px;">Sign in to your account</h3>
<p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">Enter your authorized operator or administrator credentials.</p>

@if($errors->any())
    <div class="alert alert-danger" style="padding: 10px 14px; font-size: 12.5px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success" style="padding: 10px 14px; font-size: 12.5px;">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<form action="{{ route('login.post') }}" method="POST">
    @csrf
    <div class="form-group">
        <label class="form-label required">Email Address</label>
        <div style="position: relative;">
            <input type="email" id="login_email" name="email" class="form-control" required value="{{ old('email', app()->environment(['local', 'testing']) ? 'admin@mahaeseva.com' : '') }}" placeholder="operator@mahaeseva.com" style="padding-left: 36px;">
            <i class="fa-solid fa-envelope" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label required">Password</label>
        <div style="position: relative;">
            <input type="password" id="login_password" name="password" class="form-control" required value="{{ app()->environment(['local', 'testing']) ? 'Password@123' : '' }}" placeholder="••••••••" style="padding-left: 36px;">
            <i class="fa-solid fa-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
        </div>
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); cursor: pointer;">
            <input type="checkbox" name="remember" value="1" checked> Remember this device
        </label>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 14.5px;">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In to Dashboard
    </button>
</form>

<!-- Quick Role Demo Switcher for Evaluation -->
<div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--surface-border);">
    <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 10px;">Quick 1-Click Role Logins (Testing):</span>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('admin@mahaeseva.com', 'Password@123')" style="font-size: 11.5px; justify-content: flex-start;">
            <i class="fa-solid fa-crown" style="color: #f59e0b;"></i> Business Owner
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('branchadmin@mahaeseva.com', 'Password@123')" style="font-size: 11.5px; justify-content: flex-start;">
            <i class="fa-solid fa-building" style="color: #4f46e5;"></i> Branch Admin
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('employee@mahaeseva.com', 'Password@123')" style="font-size: 11.5px; justify-content: flex-start;">
            <i class="fa-solid fa-user-gear" style="color: #10b981;"></i> Operator 1
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('operator@mahaeseva.com', 'Password@123')" style="font-size: 11.5px; justify-content: flex-start;">
            <i class="fa-solid fa-user-check" style="color: #0284c7;"></i> Operator 2
        </button>
    </div>
</div>

<script>
function setCredentials(email, password) {
    document.getElementById('login_email').value = email;
    document.getElementById('login_password').value = password;
}
</script>
@endsection
