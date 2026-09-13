@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">Account Settings</h1>
            <p class="form-label text-secondary mb-0">Manage your profile, password, and security preferences.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div class="ms-2">{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="ms-2">{{ session('error') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- ==========================================
             PROFILE INFORMATION CARD
             ========================================== --}}
        <div class="col-12 col-xl-6">
            <div class="panel-card p-4 h-100" style="border-radius: 12px;">
                <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                    <i class="bi bi-person-vcard me-2 text-primary"></i> Profile Information
                </h5>
                <form action="{{ route('admin.settings.profile') }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="form-control theme-dynamic-input @error('name', 'profileUpdate') is-invalid @enderror" required>
                        @error('name', 'profileUpdate') <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" autocomplete="email" class="form-control theme-dynamic-input @error('email', 'profileUpdate') is-invalid @enderror" required>
                        @error('email', 'profileUpdate') <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div> @enderror
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==========================================
             UPDATE PASSWORD CARD
             ========================================== --}}
        <div class="col-12 col-xl-6">
            <div class="panel-card p-4 h-100" style="border-radius: 12px;">
                <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                    <i class="bi bi-key me-2 text-primary"></i> Update Password
                </h5>
                <form action="{{ route('admin.settings.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Current Password</label>
                        <input type="password" name="current_password" class="form-control theme-dynamic-input @error('current_password', 'passwordUpdate') is-invalid @enderror" required>
                        @error('current_password', 'passwordUpdate') <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">New Password</label>
                        <input type="password" name="new_password" class="form-control theme-dynamic-input @error('new_password', 'passwordUpdate') is-invalid @enderror" required>
                        @error('new_password', 'passwordUpdate') <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control theme-dynamic-input" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-secondary fw-bold px-4 py-2" style="border-radius: 8px;">Change Password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==========================================
             SECURITY PIN CARD
             ========================================== --}}
        <div class="col-12">
            <div class="panel-card p-4" style="border-radius: 12px;">
                <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                    <i class="bi bi-shield-lock-fill me-2 text-success"></i> Administrative Security PIN
                </h5>
                <form action="{{ route('admin.settings.pin') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">Current PIN</label>
                            <input type="password" name="current_pin" maxlength="4" pattern="\d{4}" class="form-control theme-dynamic-input text-center tracking-widest @error('current_pin', 'pinUpdate') is-invalid @enderror" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;" required>
                            @error('current_pin', 'pinUpdate') <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">New PIN</label>
                            <input type="password" name="new_pin" maxlength="4" pattern="\d{4}" class="form-control theme-dynamic-input text-center tracking-widest @error('new_pin', 'pinUpdate') is-invalid @enderror" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;" required>
                            @error('new_pin', 'pinUpdate') <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">Confirm New PIN</label>
                            <input type="password" name="new_pin_confirmation" maxlength="4" pattern="\d{4}" class="form-control theme-dynamic-input text-center tracking-widest" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;" required>
                        </div>
                    </div>
                    <div class="text-end mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-link text-decoration-none text-danger fw-bold px-0" data-bs-toggle="modal" data-bs-target="#adminPinResetOtpModal" style="font-size: 13px;">
                            <i class="bi bi-envelope me-1"></i> Forgot PIN? Reset via Email OTP
                        </button>
                        <button type="submit" class="btn btn-success fw-bold px-4 py-2" style="border-radius: 8px;">Update Security PIN</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==========================================
             DANGER ZONE — ACCOUNT LIFECYCLE REQUESTS
             ========================================== --}}
        <div class="col-12">
            <div class="panel-card p-4" style="border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.35);">
                <h5 class="fw-bold mb-1 border-bottom pb-3" style="color: var(--text-primary);">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> Danger Zone
                </h5>

                @if(Auth::user()->isScheduledForDeletion())
                    <div class="alert alert-danger mt-3 mb-3" style="border-radius: 10px;">
                        <i class="bi bi-trash3-fill me-2"></i>
                        <strong>Your account is scheduled for permanent deletion on {{ Auth::user()->deletion_effective_at->format('M d, Y') }}.</strong>
                        Cancel below to keep your account.
                    </div>
                @elseif(Auth::user()->hasPendingDeletionRequest())
                    <div class="alert alert-warning mt-3 mb-3" style="border-radius: 10px;">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Your <strong>deletion request</strong> is awaiting administrator review.
                    </div>
                @elseif(Auth::user()->hasPendingDeactivationRequest())
                    <div class="alert alert-warning mt-3 mb-3" style="border-radius: 10px;">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Your <strong>deactivation request</strong> is awaiting administrator review.
                    </div>
                @endif

                <p class="text-secondary small">Request deactivation (temporary) or deletion (permanent after a {{ \App\Models\User::DELETION_BUFFER_DAYS }}-day grace period). Both require administrator approval. Transaction history is always preserved.</p>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if(Auth::user()->isScheduledForDeletion() || Auth::user()->hasPendingDeletionRequest() || Auth::user()->hasPendingDeactivationRequest())
                        <button type="button" class="btn btn-success fw-bold px-4 py-2" onclick="cancelStaffLifecycleRequest()" style="border-radius: 8px;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Cancel My Request
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-secondary fw-semibold px-4 py-2" data-bs-toggle="modal" data-bs-target="#staffDeactivationModal" style="border-radius: 8px;">
                            <i class="bi bi-pause-circle me-1"></i> Request Account Deactivation
                        </button>
                        <button type="button" class="btn btn-outline-danger fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#staffDeletionModal" style="border-radius: 8px;">
                            <i class="bi bi-trash3 me-1"></i> Request Account Deletion
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     STAFF ACCOUNT LIFECYCLE REQUEST MODALS
     ══════════════════════════════════════════ --}}
<div class="modal fade" id="staffDeactivationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold">Request Account Deactivation</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="text-secondary small">Your account will be deactivated once an administrator approves. Access can be restored later; your history is preserved.</p>
                <label class="form-label fw-bold text-secondary small">Reason (required)</label>
                <textarea id="staffDeactReason" class="form-control theme-dynamic-input" rows="3" maxlength="500" placeholder="Why do you want to deactivate this account?"></textarea>
                <div id="staffDeactErr" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-warning fw-bold px-4" onclick="submitStaffLifecycleRequest()" style="border-radius: 8px;">Submit Request</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="staffDeletionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid rgba(220,53,69,0.4);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-trash3-fill text-danger"></i> Request Account Deletion
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="alert alert-danger py-2 small" style="border-radius: 10px;">
                    <strong>Permanent action.</strong> Once approved, a {{ \App\Models\User::DELETION_BUFFER_DAYS }}-day grace period begins before permanent removal. Transaction records are preserved.
                </div>
                <label class="form-label fw-bold text-secondary small">Reason (required)</label>
                <textarea id="staffDelReason" class="form-control theme-dynamic-input" rows="3" maxlength="500" placeholder="Why do you want to delete this account?"></textarea>
                <label class="form-label fw-bold text-secondary small mt-3">Confirm with your 4-digit PIN</label>
                <input type="password" id="staffDelPin" class="form-control theme-dynamic-input text-center" maxlength="4" inputmode="numeric" placeholder="••••" style="font-size: 20px; letter-spacing: 6px; font-weight: 700; max-width: 160px;">
                <div id="staffDelErr" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4" onclick="submitStaffDeletionRequest()" style="border-radius: 8px;">Submit Deletion Request</button>
            </div>
        </div>
    </div>
</div>

<script>
    function staffLifecycleFetch(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(body)
        }).then(res => res.json().then(data => ({ ok: res.ok, data })));
    }

    async function submitStaffLifecycleRequest() {
        const reason = document.getElementById('staffDeactReason').value.trim();
        const errDiv = document.getElementById('staffDeactErr');
        errDiv.classList.add('d-none');
        if (reason.length < 10) {
            errDiv.textContent = 'Please provide a reason of at least 10 characters.';
            errDiv.classList.remove('d-none');
            return;
        }
        const result = await staffLifecycleFetch('{{ route("account.request-deactivation") }}', { reason: reason });
        if (result.ok && result.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('staffDeactivationModal'))?.hide();
            location.reload();
        } else {
            errDiv.textContent = result.data.message || 'Request failed.';
            errDiv.classList.remove('d-none');
        }
    }

    async function submitStaffDeletionRequest() {
        const reason = document.getElementById('staffDelReason').value.trim();
        const pin = document.getElementById('staffDelPin').value.trim();
        const errDiv = document.getElementById('staffDelErr');
        errDiv.classList.add('d-none');
        if (reason.length < 10) {
            errDiv.textContent = 'Please provide a reason of at least 10 characters.';
            errDiv.classList.remove('d-none');
            return;
        }
        if (!/^\d{4}$/.test(pin)) {
            errDiv.textContent = 'Please enter your 4-digit PIN to confirm.';
            errDiv.classList.remove('d-none');
            return;
        }
        const result = await staffLifecycleFetch('{{ route("account.request-deletion") }}', { reason: reason, pin: pin });
        if (result.ok && result.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('staffDeletionModal'))?.hide();
            location.reload();
        } else {
            errDiv.textContent = result.data.message || 'Request failed.';
            errDiv.classList.remove('d-none');
        }
    }

    async function cancelStaffLifecycleRequest() {
        if (!confirm('Cancel your pending account request?')) return;
        const result = await staffLifecycleFetch('{{ route("account.cancel-request") }}', {});
        if (result.ok && result.data.status === 'success') {
            location.reload();
        } else {
            alert(result.data.message || 'Failed to cancel request.');
        }
    }
</script>

{{-- ══════════════════════════════════════════
     ADMIN PIN RESET VIA OTP MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="adminPinResetOtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <div class="icon-circle icon-circle-red">
                        <i class="bi bi-shield-lock text-danger"></i>
                    </div>
                    Reset Your PIN
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div id="adminFpStep1">
                    <p class="text-secondary small mb-3">A verification code will be sent to your registered email.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Registered Email</label>
                        <input type="text" id="adminFpEmail" class="form-control theme-dynamic-input" value="{{ Auth::user()->email }}" readonly style="font-weight: 600;" autocomplete="off" />
                    </div>
                    <div id="adminFpErr1" class="text-danger small mb-2 d-none"></div>
                    <button type="button" id="adminFpSendOtpBtn" class="btn btn-primary fw-bold w-100 py-2" onclick="sendAdminPinResetOtp()" style="border-radius: 8px;">
                        Send Verification Code
                    </button>
                </div>
                <div id="adminFpStep2" style="display: none;">
                    <p class="text-secondary small mb-3">Enter the 6-digit code sent to <strong>{{ Auth::user()->email }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Verification Code</label>
                        <input type="text" id="adminFpOtp" class="form-control theme-dynamic-input text-center" maxlength="6" placeholder="000000" style="font-size: 20px; letter-spacing: 8px; font-weight: 700;">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">New PIN</label>
                            <input type="password" id="adminFpNewPin" class="form-control theme-dynamic-input text-center" maxlength="4" pattern="\d{4}" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Confirm PIN</label>
                            <input type="password" id="adminFpConfirmPin" class="form-control theme-dynamic-input text-center" maxlength="4" pattern="\d{4}" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;">
                        </div>
                    </div>
                    <div id="adminFpErr2" class="text-danger small mb-2 d-none"></div>
                    <button type="button" id="adminFpResetBtn" class="btn btn-success fw-bold w-100 py-2" onclick="submitAdminPinReset()" style="border-radius: 8px;">
                        Reset PIN
                    </button>
                    <button type="button" class="btn w-100 mt-2 py-1 fw-semibold" onclick="goBackAdminFpStep1()" style="background: transparent; color: var(--text-secondary); border: none; font-size: 13px;">← Back</button>
                </div>
                <div id="adminFpStep3" style="display: none;">
                    <div class="text-center py-3">
                        <div class="d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: var(--accent-green-bg); border-radius: 50%; margin: 0 auto 16px;">
                            <i class="bi bi-check-circle-fill text-success fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-success">PIN Reset Successful!</h5>
                        <p class="text-secondary small">Your administrative PIN has been updated.</p>
                        <button type="button" class="btn btn-primary fw-bold px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.theme-dynamic-input {
    background: transparent !important;
    border: 1px solid var(--text-secondary) !important;
    color: var(--text-primary) !important;
}
.theme-dynamic-input:focus {
    box-shadow: none !important;
    border-color: var(--text-primary) !important;
}
.theme-dynamic-input option {
    background-color: var(--bg-surface) !important;
    color: var(--text-primary) !important;
}
</style>

<script>
    let _adminFpCooldownTimer = null;
    let _adminFpCooldownEnd = 0;

    function startAdminFpCooldown() {
        const btn = document.getElementById('adminFpSendOtpBtn');
        _adminFpCooldownEnd = Date.now() + 30000;
        tickAdminFpCooldown();
        if (_adminFpCooldownTimer) clearInterval(_adminFpCooldownTimer);
        _adminFpCooldownTimer = setInterval(tickAdminFpCooldown, 1000);
    }

    function tickAdminFpCooldown() {
        const btn = document.getElementById('adminFpSendOtpBtn');
        const remaining = Math.max(0, Math.ceil((_adminFpCooldownEnd - Date.now()) / 1000));
        if (remaining <= 0) {
            clearInterval(_adminFpCooldownTimer);
            _adminFpCooldownTimer = null;
            if (document.getElementById('adminFpStep1').style.display !== 'none') {
                btn.disabled = false;
                btn.innerHTML = 'Send Verification Code';
            }
            return;
        }
        btn.disabled = true;
        btn.innerHTML = `Resend in ${remaining}s`;
    }

    // ── Admin PIN Reset OTP Modal ──────────────────────────────────────────▸
    function sendAdminPinResetOtp() {
        const btn = document.getElementById('adminFpSendOtpBtn');
        const errDiv = document.getElementById('adminFpErr1');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';
        errDiv.classList.add('d-none');

        fetch('{{ route("auth.send-pin-reset-otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('adminFpStep1').style.display = 'none';
                document.getElementById('adminFpStep2').style.display = 'block';
            } else {
                errDiv.textContent = data.message;
                errDiv.classList.remove('d-none');
            }
            startAdminFpCooldown();
        })
        .catch(() => {
            errDiv.textContent = 'Network error.';
            errDiv.classList.remove('d-none');
            startAdminFpCooldown();
        });
    }

    function goBackAdminFpStep1() {
        document.getElementById('adminFpStep2').style.display = 'none';
        document.getElementById('adminFpStep1').style.display = 'block';
        const remaining = Math.max(0, Math.ceil((_adminFpCooldownEnd - Date.now()) / 1000));
        if (remaining > 0) {
            tickAdminFpCooldown();
        } else {
            document.getElementById('adminFpSendOtpBtn').disabled = false;
            document.getElementById('adminFpSendOtpBtn').innerHTML = 'Send Verification Code';
        }
    }

    function submitAdminPinReset() {
        const otp = document.getElementById('adminFpOtp').value.trim();
        const newPin = document.getElementById('adminFpNewPin').value.trim();
        const confirmPin = document.getElementById('adminFpConfirmPin').value.trim();
        const errDiv = document.getElementById('adminFpErr2');
        const btn = document.getElementById('adminFpResetBtn');

        if (!otp || otp.length !== 6) {
            errDiv.textContent = 'Enter the 6-digit verification code.';
            errDiv.classList.remove('d-none'); return;
        }
        if (!newPin || !/^\d{4}$/.test(newPin)) {
            errDiv.textContent = 'PIN must be exactly 4 digits.';
            errDiv.classList.remove('d-none'); return;
        }
        if (newPin !== confirmPin) {
            errDiv.textContent = 'PINs do not match.';
            errDiv.classList.remove('d-none'); return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Resetting...';
        errDiv.classList.add('d-none');

        fetch('{{ route("auth.verify-pin-reset-otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ otp: otp, new_pin: newPin, new_pin_confirmation: confirmPin })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('adminFpStep2').style.display = 'none';
                document.getElementById('adminFpStep3').style.display = 'block';
            } else {
                errDiv.textContent = data.message;
                errDiv.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = 'Reset PIN';
            }
        })
        .catch(() => {
            errDiv.textContent = 'Network error.';
            errDiv.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = 'Reset PIN';
        });
    }
</script>
@endsection
