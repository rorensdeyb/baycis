<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · BayCIS — Bay Central Elementary School</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="BayCIS — Inventory Management System of Bay Central Elementary School. Sign in to manage assets, borrow requests and supplies.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Self-hosted vendor assets (same as the dashboard) --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') . '?v=' . filemtime(public_path('vendor/bootstrap/css/bootstrap.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') . '?v=' . filemtime(public_path('vendor/bootstrap-icons/bootstrap-icons.css')) }}">

    <!-- Theme Script (Prevents flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('css/welcome.css') . '?v=' . filemtime(public_path('css/welcome.css')) }}">
</head>
<body>

<div class="auth-shell">

    {{-- ══════════════════════════════════════
         LEFT — Auth card
         ══════════════════════════════════════ --}}
    <main class="auth-main">
        <header class="auth-brand">
            <img src="{{ asset('images/bces-logo.png') }}" alt="Bay Central Elementary School">
            <div class="auth-brand-divider"></div>
            <div>
                <div class="auth-brand-name">BayCIS</div>
                <div class="auth-brand-sub">Inventory Management System</div>
            </div>
        </header>

        <div class="auth-card">
            <div class="auth-tabs" role="tablist">
                <button type="button" id="authTabLogin" class="auth-tab active" onclick="showPanel('login')">Sign In</button>
                <button type="button" id="authTabRegister" class="auth-tab" onclick="showPanel('register')">Create Account</button>
            </div>

            {{-- ══════════════════════════════════════
                 PANEL: SIGN IN
                 ══════════════════════════════════════ --}}
            <section id="panel-login">
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Sign in with your school account to access your dashboard.</p>

                <div id="global-alert" class="auth-alert"></div>

                {{-- Password sign-in --}}
                <div id="login-container">
                    <input type="hidden" id="auth-mode-input" value="email">

                    <div class="mode-tabs">
                        <button type="button" id="tab-email" class="mode-tab active" onclick="switchTab('email')">Email</button>
                        <button type="button" id="tab-id" class="mode-tab" onclick="switchTab('id')">Teacher ID</button>
                    </div>

                    <div class="field-group">
                        <label class="field-label" id="id-label" for="identifier">Email address</label>
                        <div class="field-wrap">
                            <input class="field-input" id="identifier" type="text" placeholder="name@bces.edu.ph" autocomplete="username">
                        </div>
                        <div class="field-error" id="ident-err">Email address or Teacher ID is required.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="password">Password</label>
                        <div class="field-wrap">
                            <input class="field-input" id="password" type="password" placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="eye-toggle" aria-label="Toggle password visibility" onclick="toggleEye()">
                                <i class="bi bi-eye" id="eye-icon"></i>
                            </button>
                        </div>
                        <div class="field-error" id="pw-err">Password is required.</div>
                    </div>

                    <input type="hidden" id="use-pin-flag" value="0">
                    <button type="button" class="btn-primary" id="login-btn" onclick="doLogin()">Sign in</button>

                    <div class="auth-links">
                        <a href="#" class="link-action" onclick="toggleLoginMethod(event)">Use PIN instead</a>
                        <a href="#" class="link-danger" onclick="openForgotPinModal(event)">Forgot PIN?</a>
                    </div>
                </div>

                {{-- PIN sign-in --}}
                <div id="pin-login-container" style="display: none;">
                    <a href="#" class="pin-back" onclick="toggleLoginMethod(event)">
                        <i class="bi bi-arrow-left"></i> Back to password
                    </a>

                    <div class="field-group">
                        <label class="field-label" for="pin-identifier">Email or Teacher ID</label>
                        <div class="field-wrap">
                            <input class="field-input" id="pin-identifier" type="text" placeholder="name@bces.edu.ph" autocomplete="username">
                        </div>
                        <div class="field-error" id="pin-ident-err">Email address or Teacher ID is required.</div>
                    </div>

                    <label class="field-label" style="text-align:center;">4-digit PIN</label>
                    <div class="pin-dots">
                        <span class="pin-dot" id="lp-dot-0"></span>
                        <span class="pin-dot" id="lp-dot-1"></span>
                        <span class="pin-dot" id="lp-dot-2"></span>
                        <span class="pin-dot" id="lp-dot-3"></span>
                    </div>
                    <input type="hidden" id="pin-input" value="">

                    <div class="pin-numpad">
                        @foreach(['1','2','3','4','5','6','7','8','9'] as $n)
                            <button type="button" class="pin-key" onclick="numpadPress('{{ $n }}')">{{ $n }}</button>
                        @endforeach
                        <button type="button" class="pin-key is-placeholder" tabindex="-1"></button>
                        <button type="button" class="pin-key" onclick="numpadPress('0')">0</button>
                        <button type="button" class="pin-key" onclick="numpadDelete()" aria-label="Delete last digit"><i class="bi bi-backspace"></i></button>
                    </div>

                    <div class="pin-hint" id="pin-err">Use the keypad or your keyboard.</div>

                    <button type="button" class="btn-primary" id="login-pin-btn" onclick="doLogin()">Sign in</button>
                </div>
            </section>

            {{-- ══════════════════════════════════════
                 PANEL: CREATE ACCOUNT
                 ══════════════════════════════════════ --}}
            <section id="panel-register" style="display: none;">
                <h1 class="auth-title">Create your account</h1>
                <p class="auth-subtitle">Register as a borrower. You'll verify your email with a one-time code, then an administrator activates your account.</p>

                <div id="register-alert" class="auth-alert"></div>

                <form id="register-form" onsubmit="event.preventDefault(); doRegister();">
                    <div class="field-group">
                        <label class="field-label" for="reg-name">Full name</label>
                        <div class="field-wrap">
                            <input class="field-input" id="reg-name" type="text" placeholder="Juan A. Dela Cruz" autocomplete="name" maxlength="255">
                        </div>
                        <div class="field-error" id="reg-name-err">Full name is required.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="reg-email">Email address</label>
                        <div class="field-wrap">
                            <input class="field-input" id="reg-email" type="email" placeholder="name@bces.edu.ph" autocomplete="email">
                        </div>
                        <div class="field-error" id="reg-email-err">A valid email address is required.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="reg-teacher-id">Teacher ID</label>
                        <div class="field-wrap">
                            <input class="field-input" id="reg-teacher-id" type="text" placeholder="TCH-0000" autocomplete="off" maxlength="50">
                        </div>
                        <div class="field-error" id="reg-tid-err">Teacher ID is required.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="reg-password">Password</label>
                        <div class="field-wrap">
                            <input class="field-input" id="reg-password" type="password" placeholder="At least 8 characters" autocomplete="new-password">
                        </div>
                        <div class="field-error" id="reg-pw-err">Password must be at least 8 characters.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="reg-password-confirm">Confirm password</label>
                        <div class="field-wrap">
                            <input class="field-input" id="reg-password-confirm" type="password" placeholder="Re-enter your password" autocomplete="new-password">
                        </div>
                        <div class="field-error" id="reg-pwc-err">Passwords do not match.</div>
                    </div>

                    <button type="submit" class="btn-primary" id="register-btn">Create account</button>
                </form>
            </section>
        </div>

        <footer class="auth-foot">
            Department of Education · Bay Central Elementary School · School ID 108200<br>
            Authorized school personnel and registered borrowers only.
        </footer>
    </main>

    {{-- ══════════════════════════════════════
         RIGHT — Institutional panel
         ══════════════════════════════════════ --}}
    <aside class="auth-side">
        <div class="side-mark">
            <img src="{{ asset('images/ims-logo.png') }}" alt="" aria-hidden="true">
            <div>
                <div class="side-mark-name">BayCIS</div>
                <div class="side-mark-sub">DepEd · Division of Laguna</div>
            </div>
        </div>

        <div class="side-body">
            <h2 class="side-heading">School property,<br>accounted for at every step.</h2>
            <p class="side-copy">
                BayCIS keeps a single, auditable record of every asset at Bay Central Elementary School —
                from acquisition and property tagging to borrowing, returns and consumable supplies.
            </p>

            <ul class="side-list">
                <li class="side-item">
                    <span class="side-item-icon"><i class="bi bi-upc-scan"></i></span>
                    <div>
                        <div class="side-item-title">Asset registry &amp; property tags</div>
                        <div class="side-item-text">Every item carries a scannable tag with its full acquisition record.</div>
                    </div>
                </li>
                <li class="side-item">
                    <span class="side-item-icon"><i class="bi bi-arrow-left-right"></i></span>
                    <div>
                        <div class="side-item-title">Borrowing &amp; returns workflow</div>
                        <div class="side-item-text">Requests are reviewed and verified by the property custodian before items leave campus.</div>
                    </div>
                </li>
                <li class="side-item">
                    <span class="side-item-icon"><i class="bi bi-clipboard-check"></i></span>
                    <div>
                        <div class="side-item-title">Supplies issuance &amp; audit trail</div>
                        <div class="side-item-text">Consumables are issued against stock levels, with every action logged.</div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="side-foot">
            For account access concerns, contact the school's property custodian or system administrator.
        </div>
    </aside>
</div>

{{-- ══════════════════════════════════════════
     FORGOT PIN MODAL
     ══════════════════════════════════════════ --}}
<div class="modal fade" id="forgotPinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="font-size: 16px;">Reset your PIN</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">

                <div id="fp-step-1">
                    <p class="auth-subtitle mb-3">Enter your registered email address to receive a one-time verification code.</p>
                    <div class="field-group">
                        <label class="field-label" for="fp-email">Email address</label>
                        <div class="field-wrap">
                            <input class="field-input" id="fp-email" type="email" placeholder="name@bces.edu.ph">
                        </div>
                        <div class="field-error" id="fp-err-1">Failed to send code. Please try again.</div>
                    </div>
                    <button type="button" id="fp-send-otp-btn" class="btn-primary" onclick="sendForgotPinOtp()">Send verification code</button>
                </div>

                <div id="fp-step-2" style="display: none;">
                    <p class="auth-subtitle mb-3">Enter the 6-digit code sent to <strong id="fp-email-display"></strong>, then choose a new PIN.</p>
                    <div class="field-group">
                        <label class="field-label" for="fp-otp">Verification code</label>
                        <div class="field-wrap">
                            <input class="field-input" id="fp-otp" type="text" inputmode="numeric" maxlength="6" placeholder="000000" style="letter-spacing: 6px; font-weight: 700; text-align: center;">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="fp-new-pin">New PIN</label>
                        <div class="field-wrap">
                            <input class="field-input" id="fp-new-pin" type="password" inputmode="numeric" maxlength="4" placeholder="&bull;&bull;&bull;&bull;" style="letter-spacing: 6px; text-align: center;">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="fp-confirm-pin">Confirm new PIN</label>
                        <div class="field-wrap">
                            <input class="field-input" id="fp-confirm-pin" type="password" inputmode="numeric" maxlength="4" placeholder="&bull;&bull;&bull;&bull;" style="letter-spacing: 6px; text-align: center;">
                        </div>
                        <div class="field-error" id="fp-err-2">Failed to reset PIN. Please try again.</div>
                    </div>
                    <button type="button" id="fp-reset-btn" class="btn-primary" onclick="submitForgotPinReset()">Reset PIN</button>
                    <div class="text-center mt-3">
                        <a href="#" class="link-action" style="font-size: 12.5px; color: var(--muted);" onclick="goBackFpStep1(event)">Back</a>
                    </div>
                </div>

                <div id="fp-step-3" style="display: none;" class="text-center py-3">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--success-soft); color: var(--success); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 26px;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h6 class="fw-bold" style="color: var(--success);">PIN updated</h6>
                    <p class="auth-subtitle">You can now sign in using your new PIN.</p>
                    <button type="button" class="btn-ghost w-100" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') . '?v=' . filemtime(public_path('vendor/bootstrap/js/bootstrap.bundle.min.js')) }}"></script>
<script src="{{ asset('js/loader.js') . '?v=' . filemtime(public_path('js/loader.js')) }}"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>
</body>
</html>
