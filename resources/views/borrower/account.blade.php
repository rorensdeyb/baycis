@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Account Settings</h1>
            <p class="text-muted m-0">Manage your profile, password, and security preferences.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-green-bg); border: 1px solid var(--accent-green); color: var(--accent-green); padding: 16px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- ==========================================
             PROFILE INFORMATION CARD
             ========================================== --}}
        <div class="col-12 col-xl-6">
            <div class="activity-card h-100 p-4 shadow-sm" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px;">
                <h5 class="fw-bold mb-1" style="color: var(--text-primary);">Profile Information</h5>
                <p class="text-secondary small mb-4">Update your account's profile information and email address.</p>

                <form action="{{ route('borrower.account.profile') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="form-control @error('name', 'profileUpdate') is-invalid @enderror" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                        @error('name', 'profileUpdate')
                            <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="form-control @error('email', 'profileUpdate') is-invalid @enderror" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                        @error('email', 'profileUpdate')
                            <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary-action fw-bold w-100 py-2" style="border-radius: 10px;">Save Profile Changes</button>
                </form>
            </div>
        </div>

        {{-- ==========================================
             UPDATE PASSWORD CARD
             ========================================== --}}
        <div class="col-12 col-xl-6">
            <div class="activity-card h-100 p-4 shadow-sm" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px;">
                <h5 class="fw-bold mb-1" style="color: var(--text-primary);">Update Password</h5>
                <p class="text-secondary small mb-4">Ensure your account is using a long, random password to stay secure.</p>

                <form action="{{ route('borrower.account.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Current Password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password', 'passwordUpdate') is-invalid @enderror" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                        @error('current_password', 'passwordUpdate')
                            <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">New Password</label>
                        <input type="password" name="new_password" class="form-control @error('new_password', 'passwordUpdate') is-invalid @enderror" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                        @error('new_password', 'passwordUpdate')
                            <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                    </div>

                    <button type="submit" class="btn btn-dark fw-bold w-100 py-2" style="background: var(--text-primary); color: var(--bg-surface); border: none; border-radius: 10px;">Change Password</button>
                </form>
            </div>
        </div>

        {{-- ==========================================
             MANAGE SECURITY PIN CARD
             ========================================== --}}
        <div class="col-12">
            <div class="activity-card p-4 shadow-sm" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px;">
                <div class="d-flex align-items-center gap-3 mb-1">
                    <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: var(--accent-green-bg); border-radius: 10px;">
                        <i class="bi bi-shield-lock-fill text-success fs-5"></i>
                    </div>
                    <h5 class="fw-bold m-0" style="color: var(--text-primary);">Security PIN</h5>
                </div>
                <p class="text-secondary small mb-4 mt-2">Update the 4-digit PIN used to authorize asset borrow requests and secure logins.</p>

                <form action="{{ route('borrower.account.pin') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Current PIN</label>
                            <input type="password" name="current_pin" maxlength="4" pattern="\d{4}" inputmode="numeric" class="form-control text-center tracking-widest @error('current_pin', 'pinUpdate') is-invalid @enderror" placeholder="••••" style="font-size: 20px; letter-spacing: 4px; background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                            @error('current_pin', 'pinUpdate')
                                <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">New PIN</label>
                            <input type="password" name="new_pin" maxlength="4" pattern="\d{4}" inputmode="numeric" class="form-control text-center tracking-widest @error('new_pin', 'pinUpdate') is-invalid @enderror" placeholder="••••" style="font-size: 20px; letter-spacing: 4px; background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                            @error('new_pin', 'pinUpdate')
                                <div class="invalid-feedback fw-semibold mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Confirm New PIN</label>
                            <input type="password" name="new_pin_confirmation" maxlength="4" pattern="\d{4}" inputmode="numeric" class="form-control text-center tracking-widest" placeholder="••••" style="font-size: 20px; letter-spacing: 4px; background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;" required>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-link text-decoration-none text-danger fw-bold px-0" data-bs-toggle="modal" data-bs-target="#borrowerPinResetOtpModal" style="font-size: 13px;">
                            <i class="bi bi-envelope me-1"></i> Forgot PIN? Reset via Email OTP
                        </button>
                        <button type="submit" class="btn btn-success fw-bold px-4 py-2" style="border-radius: 10px;">Update PIN</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ==========================================
             MOBILE SIGN OUT CARD (At top of Danger Zone on mobile)
             ========================================== --}}
        <div class="col-12 d-md-none">
            <button class="w-100 border-0 p-0" data-bs-toggle="modal" data-bs-target="#logoutModal" style="background: transparent; cursor: pointer;">
                <div class="activity-card d-flex align-items-center justify-content-between p-3" style="background: var(--bg-surface); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--accent-red-bg); border-radius: 12px; flex-shrink: 0;">
                            <i class="bi bi-box-arrow-right text-danger" style="font-size: 20px;"></i>
                        </div>
                        <div class="text-start">
                            <div class="fw-bold text-danger" style="font-size: 15px; line-height: 1.2;">Sign Out</div>
                            <div class="text-muted" style="font-size: 12px; line-height: 1.4;">End your current session</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-danger" style="font-size: 16px; opacity: 0.6;"></i>
                </div>
            </button>
        </div>

        {{-- ==========================================
             DANGER ZONE — ACCOUNT LIFECYCLE REQUESTS
             ========================================== --}}
        <div class="col-12 mb-5 pb-4">
            <div class="activity-card p-4 shadow-sm" style="background: var(--bg-surface); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 16px;">
                <div class="d-flex align-items-center gap-3 mb-1">
                    <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: var(--accent-red-bg, rgba(220,53,69,0.1)); border-radius: 10px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    </div>
                    <h5 class="fw-bold m-0" style="color: var(--text-primary);">Danger Zone</h5>
                </div>

                @if(Auth::user()->isScheduledForDeletion())
                    <div class="alert alert-danger mt-3 mb-3" style="border-radius: 10px;">
                        <i class="bi bi-trash3-fill me-2"></i>
                        <strong>Your account is scheduled for permanent deletion on {{ Auth::user()->deletion_effective_at->format('M d, Y') }}.</strong>
                        Your transaction history will be preserved. Cancel below to keep your account.
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

                <p class="text-secondary small mb-4">You may request deactivation (temporary) or deletion (permanent, with a {{ \App\Models\User::DELETION_BUFFER_DAYS }}-day grace period). Both require administrator approval. Your ongoing and past transactions are always preserved.</p>

                <div class="d-flex flex-wrap gap-2">
                    @if(Auth::user()->isScheduledForDeletion() || Auth::user()->hasPendingDeletionRequest() || Auth::user()->hasPendingDeactivationRequest())
                        <button type="button" class="btn btn-success fw-bold px-4 py-2" onclick="cancelLifecycleRequest()" style="border-radius: 10px;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Cancel My Request
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-secondary fw-semibold px-4 py-2" data-bs-toggle="modal" data-bs-target="#requestDeactivationModal" style="border-radius: 10px;">
                            <i class="bi bi-pause-circle me-1"></i> Request Account Deactivation
                        </button>
                        <button type="button" class="btn btn-outline-danger fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#requestDeletionModal" style="border-radius: 10px;">
                            <i class="bi bi-trash3 me-1"></i> Request Account Deletion
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     BORROWER PIN RESET VIA OTP MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="borrowerPinResetOtpModal" tabindex="-1" aria-hidden="true">
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
                <div id="borrFpStep1">
                    <p class="text-secondary small mb-3">A verification code will be sent to your registered email.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Registered Email</label>
                        <input type="email" id="borrFpEmail" class="form-control theme-dynamic-input" value="{{ Auth::user()->email }}" readonly style="font-weight: 600;" />
                    </div>
                    <div id="borrFpErr1" class="text-danger small mb-2 d-none"></div>
                    <button type="button" id="borrFpSendOtpBtn" class="btn btn-primary fw-bold w-100 py-2" onclick="sendBorrowerPinResetOtp()" style="border-radius: 8px;">
                        Send Verification Code
                    </button>
                </div>
                <div id="borrFpStep2" style="display: none;">
                    <p class="text-secondary small mb-3">Enter the 6-digit code sent to <strong>{{ Auth::user()->email }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Verification Code</label>
                        <input type="text" id="borrFpOtp" class="form-control theme-dynamic-input text-center" maxlength="6" placeholder="000000" style="font-size: 20px; letter-spacing: 8px; font-weight: 700;">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">New PIN</label>
                            <input type="password" id="borrFpNewPin" class="form-control theme-dynamic-input text-center" maxlength="4" pattern="\d{4}" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Confirm PIN</label>
                            <input type="password" id="borrFpConfirmPin" class="form-control theme-dynamic-input text-center" maxlength="4" pattern="\d{4}" placeholder="••••" style="font-size: 20px; letter-spacing: 4px;">
                        </div>
                    </div>
                    <div id="borrFpErr2" class="text-danger small mb-2 d-none"></div>
                    <button type="button" id="borrFpResetBtn" class="btn btn-success fw-bold w-100 py-2" onclick="submitBorrowerPinReset()" style="border-radius: 8px;">
                        Reset PIN
                    </button>
                    <button type="button" class="btn w-100 mt-2 py-1 fw-semibold" onclick="goBackBorrFpStep1()" style="background: transparent; color: var(--text-secondary); border: none; font-size: 13px;">← Back</button>
                </div>
                <div id="borrFpStep3" style="display: none;">
                    <div class="text-center py-3">
                        <div class="d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: var(--accent-green-bg); border-radius: 50%; margin: 0 auto 16px;">
                            <i class="bi bi-check-circle-fill text-success fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-success">PIN Reset Successful!</h5>
                        <p class="text-secondary small">Your security PIN has been updated.</p>
                        <button type="button" class="btn btn-primary fw-bold px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let _borrFpCooldownTimer = null;
    let _borrFpCooldownEnd = 0;

    function startBorrFpCooldown() {
        const btn = document.getElementById('borrFpSendOtpBtn');
        _borrFpCooldownEnd = Date.now() + 30000;
        tickBorrFpCooldown();
        if (_borrFpCooldownTimer) clearInterval(_borrFpCooldownTimer);
        _borrFpCooldownTimer = setInterval(tickBorrFpCooldown, 1000);
    }

    function tickBorrFpCooldown() {
        const btn = document.getElementById('borrFpSendOtpBtn');
        const remaining = Math.max(0, Math.ceil((_borrFpCooldownEnd - Date.now()) / 1000));
        if (remaining <= 0) {
            clearInterval(_borrFpCooldownTimer);
            _borrFpCooldownTimer = null;
            if (document.getElementById('borrFpStep1').style.display !== 'none') {
                btn.disabled = false;
                btn.innerHTML = 'Send Verification Code';
            }
            return;
        }
        btn.disabled = true;
        btn.innerHTML = `Resend in ${remaining}s`;
    }

    // ── Borrower PIN Reset OTP Modal ───────────────────────────────────────▸
    function sendBorrowerPinResetOtp() {
        const btn = document.getElementById('borrFpSendOtpBtn');
        const errDiv = document.getElementById('borrFpErr1');
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
                document.getElementById('borrFpStep1').style.display = 'none';
                document.getElementById('borrFpStep2').style.display = 'block';
            } else {
                errDiv.textContent = data.message;
                errDiv.classList.remove('d-none');
            }
            startBorrFpCooldown();
        })
        .catch(() => {
            errDiv.textContent = 'Network error.';
            errDiv.classList.remove('d-none');
            startBorrFpCooldown();
        });
    }

    function goBackBorrFpStep1() {
        document.getElementById('borrFpStep2').style.display = 'none';
        document.getElementById('borrFpStep1').style.display = 'block';
        const remaining = Math.max(0, Math.ceil((_borrFpCooldownEnd - Date.now()) / 1000));
        if (remaining > 0) {
            tickBorrFpCooldown();
        } else {
            document.getElementById('borrFpSendOtpBtn').disabled = false;
            document.getElementById('borrFpSendOtpBtn').innerHTML = 'Send Verification Code';
        }
    }

    function submitBorrowerPinReset() {
        const otp = document.getElementById('borrFpOtp').value.trim();
        const newPin = document.getElementById('borrFpNewPin').value.trim();
        const confirmPin = document.getElementById('borrFpConfirmPin').value.trim();
        const errDiv = document.getElementById('borrFpErr2');
        const btn = document.getElementById('borrFpResetBtn');

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
                document.getElementById('borrFpStep2').style.display = 'none';
                document.getElementById('borrFpStep3').style.display = 'block';
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
{{-- ══════════════════════════════════════════
     ACCOUNT LIFECYCLE REQUEST MODALS
     ══════════════════════════════════════════ --}}
<div class="modal fade" id="requestDeactivationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold">Request Account Deactivation</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="text-secondary small">Your account will be deactivated once an administrator approves. You will not be able to sign in, but your history is preserved and access can be restored later.</p>
                <label class="form-label fw-bold text-secondary small">Reason (required)</label>
                <textarea id="deactReason" class="form-control theme-dynamic-input" rows="3" maxlength="500" placeholder="Tell the administrators why you want to deactivate your account..."></textarea>
                <div id="deactErr" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-warning fw-bold px-4" onclick="submitLifecycleRequest('request-deactivation', 'deactReason', 'deactErr')" style="border-radius: 8px;">Submit Request</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="requestDeletionModal" tabindex="-1" aria-hidden="true">
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
                    <strong>Permanent action.</strong> Once approved, your account enters a {{ \App\Models\User::DELETION_BUFFER_DAYS }}-day grace period, then it is permanently removed.
                    All your ongoing and closed transactions are preserved in school records.
                </div>
                <label class="form-label fw-bold text-secondary small">Reason (required)</label>
                <textarea id="delReqReason" class="form-control theme-dynamic-input" rows="3" maxlength="500" placeholder="Tell the administrators why you want to delete your account..."></textarea>
                <label class="form-label fw-bold text-secondary small mt-3">Confirm with your 4-digit PIN</label>
                <input type="password" id="delReqPin" class="form-control theme-dynamic-input text-center" maxlength="4" inputmode="numeric" placeholder="••••" style="font-size: 20px; letter-spacing: 6px; font-weight: 700; max-width: 160px;">
                <div id="delReqErr" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4" onclick="submitDeletionRequest()" style="border-radius: 8px;">Submit Deletion Request</button>
            </div>
        </div>
    </div>
</div>

<script>
    function lifecycleCsrf() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function lifecycleFetch(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': lifecycleCsrf() },
            body: JSON.stringify(body)
        }).then(res => res.json().then(data => ({ ok: res.ok, data })));
    }

    async function submitLifecycleRequest(kind, reasonId, errId) {
        const reason = document.getElementById(reasonId).value.trim();
        const errDiv = document.getElementById(errId);
        errDiv.classList.add('d-none');

        if (reason.length < 10) {
            errDiv.textContent = 'Please provide a reason of at least 10 characters.';
            errDiv.classList.remove('d-none');
            return;
        }

        const result = await lifecycleFetch('{{ route("account.request-deactivation") }}', { reason: reason });
        if (result.ok && result.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('requestDeactivationModal'))?.hide();
            location.reload();
        } else {
            errDiv.textContent = result.data.message || 'Request failed.';
            errDiv.classList.remove('d-none');
        }
    }

    async function submitDeletionRequest() {
        const reason = document.getElementById('delReqReason').value.trim();
        const pin = document.getElementById('delReqPin').value.trim();
        const errDiv = document.getElementById('delReqErr');
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

        const result = await lifecycleFetch('{{ route("account.request-deletion") }}', { reason: reason, pin: pin });
        if (result.ok && result.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('requestDeletionModal'))?.hide();
            location.reload();
        } else {
            errDiv.textContent = result.data.message || 'Request failed.';
            errDiv.classList.remove('d-none');
        }
    }

    async function cancelLifecycleRequest() {
        if (!confirm('Cancel your pending account request?')) return;
        const result = await lifecycleFetch('{{ route("account.cancel-request") }}', {});
        if (result.ok && result.data.status === 'success') {
            location.reload();
        } else {
            alert(result.data.message || 'Failed to cancel request.');
        }
    }
</script>
@endsection