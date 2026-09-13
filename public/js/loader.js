/* ============================================================
   BayCIS — Authentication screen behavior
   Sign in (password / PIN), registration, forgot-PIN flow.
   ============================================================ */

(function () {
    'use strict';

    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showAlert(id, message, kind) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'auth-alert ' + (kind === 'success' ? 'is-success' : 'is-danger');
        el.textContent = message;
    }

    function hideAlert(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'auth-alert';
    }

    function fieldError(id, message) {
        var el = document.getElementById(id);
        if (!el) return;
        if (message) {
            el.textContent = message;
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    // ── Panel switching (Sign In / Create Account) ─────────────

    window.showPanel = function (panel) {
        var loginTab = document.getElementById('authTabLogin');
        var registerTab = document.getElementById('authTabRegister');
        var loginPanel = document.getElementById('panel-login');
        var registerPanel = document.getElementById('panel-register');
        if (!loginPanel || !registerPanel) return;

        var showingRegister = panel === 'register';

        loginTab.classList.toggle('active', !showingRegister);
        registerTab.classList.toggle('active', showingRegister);
        loginPanel.style.display = showingRegister ? 'none' : 'block';
        registerPanel.style.display = showingRegister ? 'block' : 'none';

        hideAlert('global-alert');
        hideAlert('register-alert');

        var firstField = showingRegister
            ? document.getElementById('reg-name')
            : document.getElementById('identifier');
        if (firstField) firstField.focus();
    };

    // ── Identifier mode (Email / Teacher ID) ───────────────────

    window.switchTab = function (mode) {
        var emailTab = document.getElementById('tab-email');
        var idTab = document.getElementById('tab-id');
        var label = document.getElementById('id-label');
        var input = document.getElementById('identifier');
        var modeInput = document.getElementById('auth-mode-input');
        if (!emailTab || !label || !input) return;

        emailTab.classList.toggle('active', mode === 'email');
        idTab.classList.toggle('active', mode === 'id');
        if (modeInput) modeInput.value = mode;

        if (mode === 'email') {
            label.textContent = 'Email address';
            input.placeholder = 'name@bces.edu.ph';
        } else {
            label.textContent = 'Teacher ID';
            input.placeholder = 'TCH-0000';
        }
        fieldError('ident-err', null);
    };

    // ── Password visibility ────────────────────────────────────

    window.toggleEye = function () {
        var input = document.getElementById('password');
        var icon = document.getElementById('eye-icon');
        if (!input || !icon) return;

        var hidden = input.type === 'password';
        input.type = hidden ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !hidden);
        icon.classList.toggle('bi-eye-slash', hidden);
    };

    // ── Password ↔ PIN mode ────────────────────────────────────

    function syncPinDots(value) {
        for (var i = 0; i < 4; i++) {
            var dot = document.getElementById('lp-dot-' + i);
            if (!dot) continue;
            dot.classList.toggle('filled', i < value.length);
        }
    }

    window.toggleLoginMethod = function (e) {
        if (e) e.preventDefault();
        var passwordView = document.getElementById('login-container');
        var pinView = document.getElementById('pin-login-container');
        var flag = document.getElementById('use-pin-flag');
        if (!passwordView || !pinView) return;

        hideAlert('global-alert');

        var switchingToPin = passwordView.style.display !== 'none';

        if (switchingToPin) {
            var identifier = document.getElementById('identifier').value.trim();
            var pinIdent = document.getElementById('pin-identifier');
            if (pinIdent && identifier) pinIdent.value = identifier;

            passwordView.style.display = 'none';
            pinView.style.display = 'block';
            flag.value = '1';
            if (pinIdent) pinIdent.focus();
        } else {
            var pinIdentValue = document.getElementById('pin-identifier').value.trim();
            var standardIdent = document.getElementById('identifier');
            if (standardIdent && pinIdentValue) standardIdent.value = pinIdentValue;

            pinView.style.display = 'none';
            passwordView.style.display = 'block';
            flag.value = '0';
        }

        var pinInput = document.getElementById('pin-input');
        if (pinInput) pinInput.value = '';
        syncPinDots('');
    };

    window.numpadPress = function (digit) {
        var input = document.getElementById('pin-input');
        if (!input || input.value.length >= 4) return;
        input.value += digit;
        syncPinDots(input.value);
        fieldError('pin-err', null);
    };

    window.numpadDelete = function () {
        var input = document.getElementById('pin-input');
        if (!input) return;
        input.value = input.value.slice(0, -1);
        syncPinDots(input.value);
    };

    document.addEventListener('keydown', function (e) {
        var pinView = document.getElementById('pin-login-container');
        if (!pinView || pinView.style.display === 'none') return;

        if (/^[0-9]$/.test(e.key)) {
            numpadPress(e.key);
        } else if (e.key === 'Backspace') {
            numpadDelete();
        } else if (e.key === 'Enter') {
            doLogin();
        }
    });

    // Enter submits within the sign-in container
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('login-container');
        if (container) {
            container.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && document.activeElement && document.activeElement.tagName !== 'BUTTON') {
                    e.preventDefault();
                    window.doLogin();
                }
            });
        }
    });

    // ── Sign in ────────────────────────────────────────────────

    window.doLogin = async function () {
        var usePin = document.getElementById('use-pin-flag').value === '1';
        var btn = document.getElementById(usePin ? 'login-pin-btn' : 'login-btn');
        var payload;

        if (usePin) {
            var pinIdent = document.getElementById('pin-identifier').value.trim();
            var pin = document.getElementById('pin-input').value;
            if (!pinIdent) { fieldError('pin-ident-err', 'Email address or Teacher ID is required.'); return; }
            fieldError('pin-ident-err', null);
            if (pin.length !== 4) { showAlert('global-alert', 'Enter your 4-digit PIN.', 'danger'); return; }

            payload = { login_id: pinIdent, pin: pin, use_pin: true };
        } else {
            var identifier = document.getElementById('identifier').value.trim();
            var password = document.getElementById('password').value;
            if (!identifier) { fieldError('ident-err', 'Email address or Teacher ID is required.'); return; }
            fieldError('ident-err', null);
            if (!password) { fieldError('pw-err', 'Password is required.'); return; }
            fieldError('pw-err', null);

            payload = { login_id: identifier, password: password, use_pin: false };
        }

        btn.disabled = true;
        var originalLabel = btn.textContent;
        btn.textContent = 'Signing in…';
        hideAlert('global-alert');

        try {
            var response = await fetch('/login-process', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify(payload)
            });
            var data = await response.json();

            if (response.ok) {
                showAlert('global-alert', data.message || 'Signed in. Redirecting…', 'success');
                setTimeout(function () { window.location.replace(data.redirect); }, 500);
                return;
            }

            showAlert('global-alert', data.message || 'Invalid credentials. Please try again.', 'danger');
            btn.disabled = false;
            btn.textContent = originalLabel;
        } catch (error) {
            showAlert('global-alert', 'Network error. Please check your connection and try again.', 'danger');
            btn.disabled = false;
            btn.textContent = originalLabel;
        }
    };

    // ── Registration ───────────────────────────────────────────

    window.doRegister = async function () {
        var fields = {
            name: document.getElementById('reg-name').value.trim(),
            email: document.getElementById('reg-email').value.trim(),
            teacher_id: document.getElementById('reg-teacher-id').value.trim(),
            password: document.getElementById('reg-password').value,
            password_confirmation: document.getElementById('reg-password-confirm').value
        };

        var valid = true;
        if (!fields.name) { fieldError('reg-name-err', 'Full name is required.'); valid = false; } else { fieldError('reg-name-err', null); }
        if (!fields.email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(fields.email)) { fieldError('reg-email-err', 'A valid email address is required.'); valid = false; } else { fieldError('reg-email-err', null); }
        if (!fields.teacher_id) { fieldError('reg-tid-err', 'Teacher ID is required.'); valid = false; } else { fieldError('reg-tid-err', null); }
        if (fields.password.length < 8) { fieldError('reg-pw-err', 'Password must be at least 8 characters.'); valid = false; } else { fieldError('reg-pw-err', null); }
        if (fields.password !== fields.password_confirmation) { fieldError('reg-pwc-err', 'Passwords do not match.'); valid = false; } else { fieldError('reg-pwc-err', null); }
        if (!valid) return;

        var btn = document.getElementById('register-btn');
        btn.disabled = true;
        btn.textContent = 'Creating account…';
        hideAlert('register-alert');

        try {
            var response = await fetch('/auth/register', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify(fields)
            });
            var data = await response.json();

            if (response.ok) {
                if (data.redirect) {
                    window.location.replace(data.redirect);
                    return;
                }
                showAlert('register-alert', data.message || 'Registration received.', 'success');
            } else {
                showAlert('register-alert', data.message || 'Registration failed. Please review your details.', 'danger');
                btn.disabled = false;
                btn.textContent = 'Create account';
            }
        } catch (error) {
            showAlert('register-alert', 'Network error. Please try again.', 'danger');
            btn.disabled = false;
            btn.textContent = 'Create account';
        }
    };

    // ── Forgot PIN (OTP flow) ──────────────────────────────────

    var cooldownTimer = null;
    var cooldownEnd = 0;

    function tickCooldown() {
        var btn = document.getElementById('fp-send-otp-btn');
        if (!btn) return;
        var remaining = Math.max(0, Math.ceil((cooldownEnd - Date.now()) / 1000));
        if (remaining <= 0) {
            clearInterval(cooldownTimer);
            cooldownTimer = null;
            if (document.getElementById('fp-step-1').style.display !== 'none') {
                btn.disabled = false;
                btn.textContent = 'Send verification code';
            }
            return;
        }
        btn.disabled = true;
        btn.textContent = 'Resend available in ' + remaining + 's';
    }

    window.openForgotPinModal = function (e) {
        if (e) e.preventDefault();
        ['fp-step-1', 'fp-step-2', 'fp-step-3'].forEach(function (id, index) {
            document.getElementById(id).style.display = index === 0 ? 'block' : 'none';
        });
        ['fp-email', 'fp-otp', 'fp-new-pin', 'fp-confirm-pin'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        fieldError('fp-err-1', null);
        fieldError('fp-err-2', null);
        new bootstrap.Modal(document.getElementById('forgotPinModal')).show();
    };

    window.sendForgotPinOtp = async function () {
        var email = document.getElementById('fp-email').value.trim();
        var btn = document.getElementById('fp-send-otp-btn');
        if (!email) { fieldError('fp-err-1', 'Enter your registered email address.'); return; }
        fieldError('fp-err-1', null);

        btn.disabled = true;
        btn.textContent = 'Sending…';

        try {
            var response = await fetch('/auth/forgot-pin-send-otp', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({ email: email })
            });
            var data = await response.json();

            if (response.ok) {
                document.getElementById('fp-email-display').textContent = email;
                document.getElementById('fp-step-1').style.display = 'none';
                document.getElementById('fp-step-2').style.display = 'block';
            } else {
                fieldError('fp-err-1', data.message || 'Failed to send code.');
            }
        } catch (error) {
            fieldError('fp-err-1', 'Network error. Please try again.');
        }

        cooldownEnd = Date.now() + 30000;
        tickCooldown();
        if (cooldownTimer) clearInterval(cooldownTimer);
        cooldownTimer = setInterval(tickCooldown, 1000);
    };

    window.goBackFpStep1 = function (e) {
        if (e) e.preventDefault();
        document.getElementById('fp-step-2').style.display = 'none';
        document.getElementById('fp-step-1').style.display = 'block';
        tickCooldown();
        if (!cooldownTimer && Date.now() >= cooldownEnd) {
            var btn = document.getElementById('fp-send-otp-btn');
            if (btn) { btn.disabled = false; btn.textContent = 'Send verification code'; }
        }
    };

    window.submitForgotPinReset = async function () {
        var otp = document.getElementById('fp-otp').value.trim();
        var newPin = document.getElementById('fp-new-pin').value.trim();
        var confirmPin = document.getElementById('fp-confirm-pin').value.trim();
        var btn = document.getElementById('fp-reset-btn');

        if (otp.length !== 6) { fieldError('fp-err-2', 'Enter the 6-digit verification code.'); return; }
        if (!/^\d{4}$/.test(newPin)) { fieldError('fp-err-2', 'PIN must be exactly 4 digits.'); return; }
        if (newPin !== confirmPin) { fieldError('fp-err-2', 'PINs do not match.'); return; }
        fieldError('fp-err-2', null);

        btn.disabled = true;
        btn.textContent = 'Updating…';

        try {
            var response = await fetch('/auth/forgot-pin-reset', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({ otp: otp, new_pin: newPin, new_pin_confirmation: confirmPin })
            });
            var data = await response.json();

            if (response.ok) {
                document.getElementById('fp-step-2').style.display = 'none';
                document.getElementById('fp-step-3').style.display = 'block';
            } else {
                fieldError('fp-err-2', data.message || 'Failed to reset PIN.');
                btn.disabled = false;
                btn.textContent = 'Reset PIN';
            }
        } catch (error) {
            fieldError('fp-err-2', 'Network error. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Reset PIN';
        }
    };

    // ── Offline monitor ────────────────────────────────────────

    window.addEventListener('offline', function () {
        var currentUrl = encodeURIComponent(window.location.href);
        window.location.href = '/offline?returnTo=' + currentUrl;
    });
})();
