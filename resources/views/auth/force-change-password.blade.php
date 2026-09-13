<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set New Password - BayCIS</title>
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
        .auth-card {
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
            .auth-card {
                padding: 28px 20px 24px;
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
        .subtitle { font-size: 14px; color: var(--muted); text-align: center; margin-bottom: 32px; line-height: 1.5; }
        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group-custom label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-secondary);
            margin-bottom: 6px;
        }
        .input-group-custom input {
            width: 100%;
            padding: 14px 16px;
            padding-right: 48px;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background: var(--field);
            transition: all 0.2s ease;
            outline: none;
        }
        .input-group-custom input:focus {
            border-color: var(--accent-color);
            background: var(--surface);
            box-shadow: 0 0 0 3px var(--focus-ring);
        }
        .input-group-custom input.error {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px var(--danger-soft);
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap .toggle-vis {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            font-size: 18px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .input-wrap .toggle-vis:hover { color: var(--accent-color); }
        .password-strength {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }
        .password-strength .bar {
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: var(--line);
            transition: all 0.3s ease;
        }
        .password-strength .bar.active.weak { background: var(--danger); }
        .password-strength .bar.active.medium { background: #f59e0b; }
        .password-strength .bar.active.strong { background: var(--success); }
        .password-strength-label {
            font-size: 11px;
            font-weight: 600;
            margin-top: 4px;
            min-height: 16px;
            color: var(--muted);
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
        .btn-primary-custom:disabled { opacity: 0.6; cursor: not-allowed; }
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
        .requirements {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 12px;
        }
        .requirements .req {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .requirements .req.met { background: var(--success-soft); color: var(--success); }
        .requirements .req.unmet { background: var(--canvas); color: var(--muted); border: 1px solid var(--line); }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>

        <h1>Set New Password</h1>
        <p class="subtitle">Create a strong password for your account. <br>Must be at least 8 characters.</p>

        <div id="alertBox" class="alert-box"></div>

        <form id="passwordForm">
            <div class="input-group-custom">
                <label for="password">New Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" class="form-control" placeholder="Enter new password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="toggle-vis" onclick="togglePass('password', this)" tabindex="-1" aria-label="Toggle password visibility">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="password-strength" id="strengthBars">
                    <div class="bar" data-idx="0"></div>
                    <div class="bar" data-idx="1"></div>
                    <div class="bar" data-idx="2"></div>
                    <div class="bar" data-idx="3"></div>
                </div>
                <div class="password-strength-label" id="strengthLabel"></div>
            </div>

            <div class="input-group-custom">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrap">
                    <input type="password" id="password_confirmation" class="form-control" placeholder="Re-enter password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="toggle-vis" onclick="togglePass('password_confirmation', this)" tabindex="-1" aria-label="Toggle password visibility">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div id="matchIndicator" style="font-size: 11px; font-weight: 600; margin-top: 6px; min-height: 16px;"></div>
            </div>

            <div class="requirements" id="requirements">
                <span class="req unmet" data-req="length">8+ characters</span>
                <span class="req unmet" data-req="upper">Uppercase</span>
                <span class="req unmet" data-req="number">Number</span>
                <span class="req unmet" data-req="match">Passwords match</span>
            </div>

            <button type="submit" id="submitBtn" class="btn-primary-custom mt-3" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12l5 5 9-9"/></svg>
                Set Password &amp; Continue
            </button>
        </form>

        <p class="text-center mt-4 mb-0" style="font-size: 12px; color: #94a3b8;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Secured with 256-bit encryption
        </p>
    </div>

    <script>
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submitBtn');
        const alertBox = document.getElementById('alertBox');
        const reqs = {
            length: document.querySelector('[data-req="length"]'),
            upper: document.querySelector('[data-req="upper"]'),
            number: document.querySelector('[data-req="number"]'),
            match: document.querySelector('[data-req="match"]'),
        };

        function togglePass(id, btn) {
            const inp = document.getElementById(id);
            const isPass = inp.type === 'password';
            inp.type = isPass ? 'text' : 'password';
            btn.innerHTML = isPass
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22"/></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        }

        function checkStrength() {
            const val = password.value;
            const bars = document.querySelectorAll('#strengthBars .bar');
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            const classes = ['', 'weak', 'medium', 'medium', 'strong'];
            const label = document.getElementById('strengthLabel');

            bars.forEach((bar, i) => {
                bar.className = 'bar';
                if (i < score) bar.classList.add('active', classes[score] || '');
            });

            label.textContent = score > 0 ? labels[score] : '';
            label.style.color = score >= 4 ? '#10b981' : score >= 2 ? '#f59e0b' : score > 0 ? '#ef4444' : 'transparent';
            
            return score;
        }

        function checkRequirements() {
            const val = password.value;
            const conf = confirm.value;

            reqs.length.className = 'req ' + (val.length >= 8 ? 'met' : 'unmet');
            reqs.upper.className = 'req ' + (/[A-Z]/.test(val) ? 'met' : 'unmet');
            reqs.number.className = 'req ' + (/[0-9]/.test(val) ? 'met' : 'unmet');
            reqs.match.className = 'req ' + (conf.length > 0 && val === conf ? 'met' : 'unmet');

            const allMet = val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val) && val === conf && val.length > 0;
            submitBtn.disabled = !allMet;

            const matchEl = document.getElementById('matchIndicator');
            if (conf.length > 0 && val !== conf) {
                matchEl.textContent = 'Passwords do not match';
                matchEl.style.color = '#ef4444';
                confirm.classList.add('error');
            } else if (conf.length > 0 && val === conf) {
                matchEl.textContent = 'Passwords match ✓';
                matchEl.style.color = '#16a34a';
                confirm.classList.remove('error');
            } else {
                matchEl.textContent = '';
                confirm.classList.remove('error');
            }

            if (val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val)) {
                password.classList.remove('error');
            } else if (val.length > 0) {
                password.classList.add('error');
            }
        }

        password.addEventListener('input', function() { checkStrength(); checkRequirements(); });
        confirm.addEventListener('input', checkRequirements);

        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (submitBtn.disabled) return;

            const btn = submitBtn;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Setting password...';
            alertBox.className = 'alert-box';
            alertBox.classList.remove('show');

            try {
                const res = await fetch('/force-change-password-process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        password: password.value,
                        password_confirmation: confirm.value
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    alertBox.className = 'alert-box success show';
                    alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> ' + data.message;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Redirecting...';
                    setTimeout(() => window.location.replace(data.redirect), 1200);
                } else {
                    const msg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'An error occurred.');
                    alertBox.className = 'alert-box error show';
                    alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> ' + msg;
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12l5 5 9-9"/></svg> Set Password &amp; Continue';
                }
            } catch (e) {
                alertBox.className = 'alert-box error show';
                alertBox.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Server error. Please try again.';
                btn.disabled = false;
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12l5 5 9-9"/></svg> Set Password &amp; Continue';
            }
        });
    </script>
</body>
</html>
