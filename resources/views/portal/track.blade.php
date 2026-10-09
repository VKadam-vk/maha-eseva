@extends('layouts.portal')

@section('title', 'Track Your Application')

@section('content')
<div style="max-width: 600px; margin: 40px auto;">
    <div class="erp-card" style="box-shadow: var(--shadow-lg); border-top: 4px solid #ea580c;">
        <div class="erp-card-body" style="padding: 36px 32px;">
            <div style="text-align: center; margin-bottom: 28px;">
                <div style="width: 56px; height: 56px; border-radius: var(--radius-md); background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 14px auto;">
                    <i class="fa-solid fa-magnifying-glass-location"></i>
                </div>
                <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Track Service Application</h1>
                <p style="font-size: 13.5px; color: var(--text-muted);">
                    Enter your Application / SR Number and registered mobile number to securely view status via OTP verification.
                </p>
            </div>

            <!-- Error Banner -->
            <div id="track-error" class="alert alert-danger" style="display: none;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span id="track-error-msg"></span>
            </div>

            <!-- Step 1: Enter SR & Mobile -->
            <div id="step-1">
                <form id="request-otp-form" onsubmit="handleRequestOtp(event)">
                    <div class="form-group">
                        <label class="form-label required">Application / SR Number</label>
                        <input type="text" id="sr_input" class="form-control" required placeholder="e.g. SR-2026-00001" style="font-family: var(--font-mono); font-size: 15px; font-weight: 700; text-transform: uppercase;">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Registered Mobile Number</label>
                        <input type="tel" id="mobile_input" class="form-control" required placeholder="10-digit mobile number" style="font-size: 15px;">
                    </div>

                    <button type="submit" id="btn-get-otp" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px;">
                        <i class="fa-solid fa-shield-halved"></i> Get Verification OTP
                    </button>
                </form>

                <!-- Demo helper -->
                <div style="margin-top: 20px; background: #f8fafc; border: 1px solid var(--surface-border); border-radius: 6px; padding: 12px; font-size: 12px; color: var(--text-muted);">
                    <strong>Quick Test Reference:</strong> Application Number: <code style="color: #ea580c; font-weight: bold;">SR-{{ date('Y') }}-00001</code> | Mobile: <code style="color: #ea580c; font-weight: bold;">9822012345</code>
                </div>
            </div>

            <!-- Step 2: Verify OTP Form -->
            <div id="step-2" style="display: none;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px; font-size: 13px; color: #166534; margin-bottom: 20px;">
                    <i class="fa-solid fa-check-circle"></i> OTP has been dispatched to your mobile.
                    <div id="debug-otp-display" style="margin-top: 4px; font-weight: 700; color: #ea580c;"></div>
                </div>

                <form id="verify-otp-form" onsubmit="handleVerifyOtp(event)">
                    <div class="form-group">
                        <label class="form-label required">Enter 6-Digit Verification Code</label>
                        <input type="text" id="otp_input" class="form-control" required maxlength="6" placeholder="• • • • • •" style="font-family: var(--font-mono); font-size: 22px; text-align: center; letter-spacing: 8px; font-weight: 800;">
                    </div>

                    <button type="submit" id="btn-verify-otp" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px;">
                        <i class="fa-solid fa-lock-open"></i> Verify & View Application Status
                    </button>

                    <div style="text-align: center; margin-top: 16px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="backToStep1()">
                            <i class="fa-solid fa-arrow-left"></i> Change Mobile / SR Number
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function handleRequestOtp(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-get-otp');
    const errBox = document.getElementById('track-error');
    errBox.style.display = 'none';

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking Application...';

    const sr = document.getElementById('sr_input').value.trim();
    const mobile = document.getElementById('mobile_input').value.trim();

    fetch('{{ route("portal.request_otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ application_number: sr, mobile: mobile })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Get Verification OTP';

        if (data.success) {
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
            if (data.debug_otp) {
                document.getElementById('debug-otp-display').innerHTML = `(Local Test Mode OTP: <strong>${data.debug_otp}</strong>)`;
            }
            document.getElementById('otp_input').focus();
        } else {
            errBox.style.display = 'flex';
            document.getElementById('track-error-msg').textContent = data.message;
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Get Verification OTP';
        errBox.style.display = 'flex';
        document.getElementById('track-error-msg').textContent = 'Unable to connect to service. Please try again.';
    });
}

function handleVerifyOtp(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-verify-otp');
    const errBox = document.getElementById('track-error');
    errBox.style.display = 'none';

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';

    const sr = document.getElementById('sr_input').value.trim();
    const mobile = document.getElementById('mobile_input').value.trim();
    const otp = document.getElementById('otp_input').value.trim();

    fetch('{{ route("portal.verify_otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ application_number: sr, mobile: mobile, otp: otp })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-lock-open"></i> Verify & View Application Status';

        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            errBox.style.display = 'flex';
            document.getElementById('track-error-msg').textContent = data.message;
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-lock-open"></i> Verify & View Application Status';
        errBox.style.display = 'flex';
        document.getElementById('track-error-msg').textContent = 'Verification error. Please retry.';
    });
}

function backToStep1() {
    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-1').style.display = 'block';
}
</script>
@endpush
@endsection
