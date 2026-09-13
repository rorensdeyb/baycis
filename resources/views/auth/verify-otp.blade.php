<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Your Account - BayCIS</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') . '?v=' . filemtime(public_path('vendor/bootstrap/css/bootstrap.min.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Theme Script (Prevents flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        :root {
            --navy-900: #14273a;
            --navy-800: #1b3550;
            --navy-700: #24476b;
            --ink: #182230;
            --ink-secondary: #3d4756;
            --muted: #66707f;
            --faint: #98a1ad;
            --line: #e3e8ee;
            --line-strong: #cfd6df;
            --surface: #ffffff;
            --canvas: #f6f8fa;
            --field: #fbfcfd;
            --focus-ring: rgba(30, 58, 84, 0.14);
            --accent-color: #1b3550;
            --accent-color-hover: #24476b;
            --accent-bg: #eaf2f9;
            --btn-text: #ffffff;
            --success: #177a50;
            --success-soft: #eaf5ef;
            --danger: #c0392b;
            --danger-soft: #fdf0ee;
        }
        [data-theme="dark"] {
            color-scheme: dark;
            --navy-900: #081018;
            --navy-800: #1b3550;
            --navy-700: #24476b;
            --ink: #f0f4f8;
            --ink-secondary: #cbd5e1;
            --muted: #94a3b8;
            --faint: #64748b;
            --line: #1e334a;
            --line-strong: #2b4766;
            --surface: #111d2e;
            --canvas: #0c1520;
            --field: #0e1826;
            --focus-ring: rgba(82, 148, 226, 0.25);
            --accent-color: #5294e2;
            --accent-color-hover: #6ea8ee;
            --accent-bg: rgba(82, 148, 226, 0.15);
            --btn-text: #ffffff;
            --success: #22c55e;
            --success-soft: rgba(34, 197, 94, 0.15);
            --danger: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.15);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--canvas);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .otp-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 44px 40px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 1px 2px rgba(24, 34, 48, 0.05), 0 8px 24px rgba(24, 34, 48, 0.06);
            animation: slideUp 0.4s ease both;
        }
        @media (max-width: 460px) {
            .otp-card {
                padding: 32px 20px 28px;
            }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .brand-icon {
            width: 56px; height: 56px;
            background: var(--accent-color);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        .brand-icon svg { width: 28px; height: 28px; fill: none; stroke: var(--btn-text, #fff); stroke-width: 2; }
        h1 { font-size: 22px; font-weight: 800; color: var(--ink); text-align: center; letter-spacing: -0.5px; margin-bottom: 4px; }
        .subtitle {
            font-size: 14px;
            color: var(--muted);
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .subtitle strong { color: var(--ink); }
        .otp-input-grid {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 28px;
        }
        .otp-digit {
            flex: 1;
            max-width: 52px;
            min-width: 32px;
            height: 56px;
            text-align: center;
            font-size: clamp(20px, 5vw, 28px);
            font-weight: 800;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            border: 2px solid var(--line);
            border-radius: 12px;
            background: var(--field);
            outline: none;
            transition: all 0.2s ease;
            caret-color: var(--accent-color);
        }
        .otp-digit:focus {
            border-color: var(--accent-color);
            background: var(--surface);
            box-shadow: 0 0 0 3px var(--focus-ring);
        }
        .otp-digit.filled {
            border-color: var(--accent-color);
            background: var(--accent-bg);
            color: var(--ink);
        }
        .btn-primary-custom {
            width: 100%;
            padding: 14px;
            background: var(--accent-color);
            border: none;
            border-radius: 10px;
            color: var(--btn-text);
            font-size: 15px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary-custom:hover:not(:disabled) {
            background: var(--accent-color-hover);
            transform: translateY(-1px);
        }
        .btn-primary-custom:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--focus-ring); }
        .btn-primary-custom:active:not(:disabled) { background: var(--accent-color-hover); }
        .btn-primary-custom:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-resend {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 1.5px dashed var(--line-strong);
            border-radius: 12px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 12px;
        }
        .btn-resend:hover:not(:disabled) {
            border-color: var(--accent-color);
            color: var(--accent-color);
            background: var(--accent-bg);
        }
        .btn-resend:disabled { opacity: 0.4; cursor: not-allowed; }
        .btn-resend .countdown {
            font-weight: 700;
            color: inherit;
        }
        .alert-box {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 8px;
        }
        .alert-box.show { display: flex; }
        .alert-box.error { background: var(--danger-soft); border: 1px solid var(--danger); color: var(--danger); }
        .alert-box.success { background: var(--success-soft); border: 1px solid var(--success); color: var(--success); }
        .alert-box.info { background: var(--accent-bg); border: 1px solid var(--line-strong); color: var(--accent-color); }
        .email-display {
            background: var(--field);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--ink-secondary);
        }
        .email-display i { font-size: 18px; color: var(--muted); }
        .email-display strong { color: var(--ink); font-weight: 600; }
    </style>
</head>
<body>

    <div class="otp-card">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>

        <h1>Verify Your Account</h1>
        <p class="subtitle">
            Enter the 6-digit code sent to<br>
            <strong>{{ session('pending_verification_email') }}</strong>
        </p>

        <div id="alertBox" class="alert-box"></div>

        <form id="otpForm" autocomplete="off">
            <div class="otp-input-grid" id="otpGrid">
                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" autofocus data-idx="0">
                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="1">
                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="2">
                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="3">
                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="4">
                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="5">
            </div>

            <button type="submit" id="verifyBtn" class="btn-primary-custom" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Verify Account
            </button>
        </form>

        <button type="button" id="resendBtn" class="btn-resend" disabled>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Resend code <span class="countdown" id="resendCountdown"></span>
        </button>
    </div>

    <script>
        const digits = document.querySelectorAll('.otp-digit');
        const verifyBtn = document.getElementById('verifyBtn');
        const alertBox = document.getElementById('alertBox');
        const resendBtn = document.getElementById('resendBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let resendTimer = null;
        let resendCooldown = 30;

        // ── OTP Input Auto-Advance ──────────────────────────────────
        digits.forEach((input, idx) => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value) {
                    this.classList.add('filled');
                    if (idx < 5) digits[idx + 1].focus();
                } else {
                    this.classList.remove('filled');
                }
                updateVerifyButton();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    digits[idx - 1].focus();
                    digits[idx - 1].classList.remove('filled');
                }
                if (e.key === 'ArrowLeft' && idx > 0) digits[idx - 1].focus();
                if (e.key === 'ArrowRight' && idx < 5) digits[idx + 1].focus();
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                for (let i = 0; i < Math.min(paste.length, 6); i++) {
                    digits[i].value = paste[i];
                    digits[i].classList.add('filled');
                }
                const nextIdx = Math.min(paste.length, 5);
                digits[nextIdx > 5 ? 5 : nextIdx === 6 ? 5 : nextIdx]?.focus();
                updateVerifyButton();
            });
        });

        function getOtp() {
            let code = '';
            digits.forEach(d => code += d.value);
            return code;
        }

        function setOtp(code) {
            for (let i = 0; i < 6; i++) {
                digits[i].value = code[i] || '';
                digits[i].classList.toggle('filled', !!code[i]);
            }
            updateVerifyButton();
        }

        function updateVerifyButton() {
            verifyBtn.disabled = getOtp().length < 6;
        }

        // ── Resend Cooldown Timer ───────────────────────────────────
        function startResendCooldown() {
            resendCooldown = 30;
            resendBtn.disabled = true;
            const countdown = document.getElementById('resendCountdown');

            function tick() {
                if (resendCooldown <= 0) {
                    resendBtn.disabled = false;
                    countdown.textContent = '';
                    resendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Resend code';
                    return;
                }
                resendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Resend code in <span class="countdown" id="resendCountdown">' + resendCooldown + 's</span>';
                resendCooldown--;
                resendTimer = setTimeout(tick, 1000);
            }
            tick();
        }

        // ── Submit OTP ──────────────────────────────────────────────
        document.getElementById('otpForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (verifyBtn.disabled) return;

            const otp = getOtp();
            if (otp.length < 6) return;

            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';
            alertBox.className = 'alert-box';
            alertBox.classList.remove('show');

            try {
                const res = await fetch('/verify-otp-process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ otp: otp })
                });

                const data = await res.json();

                if (res.ok) {
                    alertBox.className = 'alert-box success show';
                    alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> ' + data.message;
                    verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Redirecting...';
                    setTimeout(() => window.location.replace(data.redirect), 1500);
                } else {
                    alertBox.className = 'alert-box error show';
                    alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> ' + (data.message || 'Invalid code. Please try again.');
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Verify Account';
                    setOtp('');
                    digits[0].focus();
                }
            } catch (e) {
                alertBox.className = 'alert-box error show';
                alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Server error. Please try again.';
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Verify Account';
            }
        });

        // ── Resend OTP ──────────────────────────────────────────────
        resendBtn.addEventListener('click', async function() {
            if (resendBtn.disabled) return;

            resendBtn.disabled = true;
            resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';

            try {
                const res = await fetch('/verify-otp-resend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await res.json();

                if (res.ok) {
                    alertBox.className = 'alert-box info show';
                    alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> ' + data.message;
                    startResendCooldown();
                    setOtp('');
                    digits[0].focus();
                } else {
                    alertBox.className = 'alert-box error show';
                    alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> ' + (data.message || 'Failed to resend code.');
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Resend code';
                }
            } catch (e) {
                alertBox.className = 'alert-box error show';
                alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Server error. Please try again.';
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Resend code';
            }
        });

        // ── Start resend cooldown on page load ──────────────────────
        document.addEventListener('DOMContentLoaded', startResendCooldown);
    </script>
</body>
</html>
