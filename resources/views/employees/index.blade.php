@extends('layouts.app')

@section('title', 'Staff & Operators')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Staff & Operator Directory</h1>
        <p class="page-subtitle">Manage service counter operators, credentials, branch assignments and login status</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">

        <a href="{{ route('employees.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-user-plus"></i> Add New Operator
        </a>
    </div>
</div>

<div class="erp-card">
    <div class="table-responsive">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Operator Name</th>
                    <th>Login Email</th>
                    <th>Mobile</th>
                    <th>Branch</th>
                    <th>Allowed Services</th>
                    <th>Permissions</th>
                    <th>Active Apps</th>
                    <th style="text-align: center;">Login Status</th>
                    <th style="text-align: right; padding-right: 20px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td>
                            <span style="font-family: var(--font-mono); font-weight: 700; color: #4f46e5;">{{ $emp->employee_code }}</span>
                        </td>
                        <td>
                            <strong>{{ $emp->user->name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $emp->designation }}</div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-family: var(--font-mono); font-weight: 600; color: #0f172a;">{{ $emp->user->email }}</span>
                                <button type="button" class="btn btn-sm" onclick="navigator.clipboard.writeText('{{ $emp->user->email }}'); alert('Login email copied!');" title="Copy Login Email" style="padding: 1px 6px; font-size: 10px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 3px; cursor: pointer;">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td>{{ $emp->user->mobile }}</td>
                        <td><span class="badge badge-ready">{{ $emp->branch->name }}</span></td>
                        <td>
                            @if($emp->services->count() > 0)
                                <span class="badge badge-process" title="{{ $emp->services->pluck('main_service_name')->join(', ') }}">
                                    <i class="fa-solid fa-list-check"></i> {{ $emp->services->count() }} Services
                                </span>
                            @else
                                <span class="badge badge-ready">
                                    <i class="fa-solid fa-circle-check"></i> All Services
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($emp->user->permissions->count() > 0)
                                <span class="badge badge-process">
                                    <i class="fa-solid fa-shield-halved"></i> {{ $emp->user->permissions->count() }} Custom
                                </span>
                            @else
                                <span class="badge badge-ready">
                                    <i class="fa-solid fa-shield"></i> Default Role
                                </span>
                            @endif
                        </td>
                        <td><span class="badge badge-process">{{ $emp->applications_count }} Apps</span></td>
                        <td style="text-align: center;">
                            <form action="{{ route('employees.toggle_status', $emp->id) }}" method="POST" style="margin: 0; display: inline;"
                                  onsubmit="return confirm('⚠️ खात्री करा: तुम्ही {{ addslashes($emp->user->name) }} यांना {{ $emp->status === 'ACTIVE' ? 'DEACTIVATE (लॉगिन बंद)' : 'ACTIVATE (लॉगिन सुरू)' }} करू इच्छिता का?')">
                                @csrf
                                @if($emp->status === 'ACTIVE')
                                    <button type="submit" class="badge badge-paid" style="cursor: pointer; border: none; font-size: 11px; padding: 4px 10px;" title="Active (Click to Deactivate operator)">
                                        <i class="fa-solid fa-circle-check"></i> Active
                                    </button>
                                @else
                                    <button type="submit" class="badge badge-danger" style="cursor: pointer; border: none; font-size: 11px; padding: 4px 10px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;" title="Inactive (Click to Activate operator)">
                                        <i class="fa-solid fa-circle-xmark"></i> Inactive
                                    </button>
                                @endif
                            </form>
                        </td>
                        <td style="text-align: right; padding-right: 20px;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <!-- 1. View / Reset Password & Credentials -->
                                <button type="button" class="btn btn-sm"
                                        onclick="openCredentialsModal({{ $emp->id }}, '{{ addslashes($emp->user->name) }}', '{{ addslashes($emp->user->email) }}', '{{ addslashes($emp->user->mobile) }}', '{{ addslashes($emp->designation) }}', '{{ addslashes($emp->branch->name) }}')"
                                        style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600; padding: 4px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"
                                        title="View Credentials & Reset Password (लॉगिन माहिती व पासवर्ड बदला)">
                                    <i class="fa-solid fa-key"></i> Credentials
                                </button>

                                <!-- 2. Edit Access & Permissions -->
                                <a href="{{ route('employees.edit', $emp->id) }}" class="btn btn-outline-primary btn-sm" title="Edit Permissions & Services" style="padding: 4px 10px; font-size: 12px;">
                                    <i class="fa-solid fa-user-gear"></i> Edit
                                </a>

                                <!-- 3. Delete Operator -->
                                <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" style="margin: 0; display: inline;"
                                      onsubmit="return confirm('⚠️ खात्री करा: तुम्ही {{ addslashes($emp->user->name) }} या ऑपरेटरला कायमस्वरूपी DELETE (काढून) टाकू इच्छिता का?\n\nटीप: जर ऑपरेटरला तात्पुरते थांबवायचे असेल तर Status बटनावरून Deactivate करा.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" title="Delete Operator (ऑपरेटर काढा)"
                                            style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 600; padding: 4px 8px; font-size: 12px; border-radius: 4px; cursor: pointer;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>

                                <!-- 4. Workload -->
                                <a href="{{ route('employees.show', $emp->id) }}" class="btn btn-secondary btn-sm" title="Workload & Tasks" style="padding: 4px 8px; font-size: 12px;">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 28px; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                            No employees or operators registered yet. Click "Add New Operator" above to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: NEWLY CREATED OR RESET OPERATOR CREDENTIALS POPUP (AUTO SHOW)    -->
<!-- ========================================================================= -->
@if(session('new_operator_credentials'))
<div id="newCredentialsModalOverlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(5px); z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 25px 60px -15px rgba(0,0,0,0.5); overflow: hidden; border: 1.5px solid #cbd5e1;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff;">Operator Login Credentials</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: #d1fae5;">ऑपरेटर लॉगिन माहिती यशस्वीरीत्या तयार झाली</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('newCredentialsModalOverlay').style.display = 'none';" style="background: none; border: none; color: #ffffff; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding: 22px 24px;">
            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px;">
                <div style="font-size: 12px; color: #166534; font-weight: 700; margin-bottom: 4px;">
                    <i class="fa-solid fa-circle-check"></i> हा लॉगिन आयडी आणि पासवर्ड ऑपरेटरला द्या:
                </div>
                <div style="font-size: 14px; font-weight: 800; color: #0f172a;">
                    {{ session('new_operator_credentials.name') }} ({{ session('new_operator_credentials.designation') }})
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                    <span style="color: #64748b; font-weight: 600;">📧 Login Email:</span>
                    <strong style="font-family: var(--font-mono); color: #0f172a; font-size: 14px;">{{ session('new_operator_credentials.email') }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                    <span style="color: #64748b; font-weight: 600;">🔑 Password:</span>
                    <span style="font-family: var(--font-mono); font-weight: 800; font-size: 16px; color: #0284c7; background: #e0f2fe; padding: 2px 10px; border-radius: 4px; letter-spacing: 0.5px;">
                        {{ session('new_operator_credentials.password') }}
                    </span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                    <span style="color: #64748b; font-weight: 600;">📱 Mobile:</span>
                    <strong style="color: #0f172a;">{{ session('new_operator_credentials.mobile') }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 4px;">
                    <span style="color: #64748b; font-weight: 600;">🌐 Login URL:</span>
                    <a href="{{ session('new_operator_credentials.login_url') }}" target="_blank" style="color: #4f46e5; font-size: 12.5px; font-weight: 700; text-decoration: none;">
                        {{ session('new_operator_credentials.login_url') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" onclick="copyAutoCredentialsMessage()" class="btn btn-sm"
                    style="background: #16a34a; color: #ffffff; font-size: 13px; font-weight: 700; border: none; border-radius: 6px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="fa-brands fa-whatsapp" style="font-size: 15px;"></i>
                <span id="btnCopyAutoText">Copy for WhatsApp (कॉपी करा)</span>
            </button>
            <button type="button" onclick="document.getElementById('newCredentialsModalOverlay').style.display = 'none';" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                Close
            </button>
        </div>
    </div>
</div>
@endif

<!-- ========================================================================= -->
<!-- MODAL 2: VIEW CREDENTIALS & RESET PASSWORD MODAL (ON-DEMAND)              -->
<!-- ========================================================================= -->
<div id="empCredentialsModalOverlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(5px); z-index: 999998; display: none; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 25px 60px -15px rgba(0,0,0,0.5); overflow: hidden; border: 1.5px solid #cbd5e1;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff;" id="modal_emp_name">Operator Credentials</h3>
                    <p style="margin: 2px 0 0 0; font-size: 11.5px; color: #94a3b8;" id="modal_emp_subtitle">Login ID & Password Management</p>
                </div>
            </div>
            <button type="button" onclick="closeCredentialsModal()" style="background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding: 20px 22px;">
            <!-- Current Credentials Info Box -->
            <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #64748b;">लॉगिन ईमेल (Login ID):</span>
                    <strong style="color: #0f172a; font-family: var(--font-mono);" id="modal_emp_email">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #64748b;">मोबाईल नंबर:</span>
                    <strong style="color: #0f172a;" id="modal_emp_mobile">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">शाखा (Branch):</span>
                    <span class="badge badge-ready" id="modal_emp_branch">-</span>
                </div>
            </div>

            <!-- Reset Password Form -->
            <form id="resetPasswordForm" method="POST" action="">
                @csrf
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label required" style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; justify-content: space-between; align-items: center;">
                        <span>Set New Password (नवीन पासवर्ड सेट करा)</span>
                        <span style="font-size: 11px; color: #64748b; font-weight: 500;">Minimum 6 characters</span>
                    </label>
                    <div style="position: relative;">
                        <input type="text" id="modal_new_password_input" name="password" class="form-control" required
                               placeholder="Enter new password"
                               style="font-family: var(--font-mono); font-weight: 700; font-size: 14px; padding-right: 42px; letter-spacing: 0.5px;">
                        <button type="button" onclick="toggleModalPasswordVisibility()"
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 6px; font-size: 14px;">
                            <i id="modal_password_eye" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick Password Generator Chips -->
                <div style="display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap;">
                    <button type="button" onclick="setQuickPassword('12345678')" class="btn btn-sm"
                            style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 11px; padding: 3px 8px; border-radius: 4px; cursor: pointer;">
                        <i class="fa-solid fa-key"></i> Set: <code>12345678</code>
                    </button>
                    <button type="button" onclick="generateRandomPassword()" class="btn btn-sm"
                            style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 11px; padding: 3px 8px; border-radius: 4px; cursor: pointer;">
                        <i class="fa-solid fa-shuffle"></i> 🎲 Generate Random
                    </button>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <button type="button" onclick="copyModalCredentialsToClipboard()" class="btn btn-sm"
                            style="background: #16a34a; color: #ffffff; font-size: 12.5px; font-weight: 700; border: none; border-radius: 6px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                        <i class="fa-brands fa-whatsapp"></i> <span id="btnModalCopyText">Copy for WhatsApp</span>
                    </button>

                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="closeCredentialsModal()" class="btn btn-secondary btn-sm" style="font-weight: 600;">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" style="background: #4f46e5; font-weight: 700; padding: 8px 18px;">
                            <i class="fa-solid fa-check"></i> Save Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentModalEmp = {};

    function openCredentialsModal(empId, name, email, mobile, designation, branch) {
        currentModalEmp = { id: empId, name, email, mobile, designation, branch };

        document.getElementById('modal_emp_name').textContent = name + ' (' + designation + ')';
        document.getElementById('modal_emp_subtitle').textContent = 'Branch: ' + branch + ' • Login ID: ' + email;
        document.getElementById('modal_emp_email').textContent = email;
        document.getElementById('modal_emp_mobile').textContent = mobile;
        document.getElementById('modal_emp_branch').textContent = branch;

        const form = document.getElementById('resetPasswordForm');
        form.action = `/employees/${empId}/reset-password`;

        // Default suggestion
        document.getElementById('modal_new_password_input').value = '12345678';

        const overlay = document.getElementById('empCredentialsModalOverlay');
        overlay.style.display = 'flex';
    }

    function closeCredentialsModal() {
        document.getElementById('empCredentialsModalOverlay').style.display = 'none';
    }

    function setQuickPassword(pwd) {
        document.getElementById('modal_new_password_input').value = pwd;
    }

    function generateRandomPassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#';
        let res = '';
        for (let i = 0; i < 8; i++) {
            res += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('modal_new_password_input').value = res;
    }

    function toggleModalPasswordVisibility() {
        const input = document.getElementById('modal_new_password_input');
        const eye = document.getElementById('modal_password_eye');
        if (input.type === 'password') {
            input.type = 'text';
            eye.className = 'fa-solid fa-eye-slash';
        } else {
            input.type = 'password';
            eye.className = 'fa-solid fa-eye';
        }
    }

    function copyModalCredentialsToClipboard() {
        const pwd = document.getElementById('modal_new_password_input').value.trim() || '12345678';
        const loginUrl = window.location.origin + '/login';
        const text = `*Maha E-Seva Kendra - Operator Login Details*\n\n` +
                     `👤 *Operator:* ${currentModalEmp.name}\n` +
                     `🏢 *Branch:* ${currentModalEmp.branch}\n` +
                     `📧 *Login Email:* ${currentModalEmp.email}\n` +
                     `🔑 *Password:* ${pwd}\n` +
                     `🌐 *Login Link:* ${loginUrl}\n\n` +
                     `_कृपया वरील लिंकवर जाऊन लॉगिन करा._`;

        navigator.clipboard.writeText(text).then(() => {
            const btnText = document.getElementById('btnModalCopyText');
            btnText.textContent = 'Copied! (कॉपी झाले)';
            setTimeout(() => { btnText.textContent = 'Copy for WhatsApp'; }, 2200);
        });
    }

    function copyAutoCredentialsMessage() {
        @if(session('new_operator_credentials'))
            const text = `*Maha E-Seva Kendra - Operator Login Details*\n\n` +
                         `👤 *Operator:* {{ session('new_operator_credentials.name') }}\n` +
                         `📧 *Login Email:* {{ session('new_operator_credentials.email') }}\n` +
                         `🔑 *Password:* {{ session('new_operator_credentials.password') }}\n` +
                         `📱 *Mobile:* {{ session('new_operator_credentials.mobile') }}\n` +
                         `🌐 *Login Link:* {{ session('new_operator_credentials.login_url') }}\n\n` +
                         `_कृपया वरील लिंकवर जाऊन लॉगिन करा._`;

            navigator.clipboard.writeText(text).then(() => {
                const btnText = document.getElementById('btnCopyAutoText');
                if (btnText) {
                    btnText.textContent = 'Copied! (कॉपी झाले)';
                    setTimeout(() => { btnText.textContent = 'Copy for WhatsApp (कॉपी करा)'; }, 2200);
                }
            });
        @endif
    }
</script>
@endpush
@endsection
