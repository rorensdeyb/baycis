// =========================================================
// 1. OUR CUSTOM LOADER LOGIC (Runs when page loads)
// =========================================================
document.addEventListener("DOMContentLoaded", async function() {
    
    const progressBar = document.getElementById('progress-bar');
    const loaderWrapper = document.getElementById('loader-wrapper');
    const loadingText = document.getElementById('loading-text');
    const percentText = document.getElementById('loading-percent');
    
    if (!loaderWrapper) return;

    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    let currentProgress = 0;

    function animateProgress(target, text, duration) {
        return new Promise(resolve => {
            if (text) loadingText.innerText = text;
            const start = currentProgress;
            const change = target - start;
            const startTime = performance.now();

            function step(time) {
                const elapsed = time - startTime;
                const rawProgress = start + (change * (elapsed / duration));
                currentProgress = Math.min(rawProgress, target);
                progressBar.style.width = currentProgress + '%';
                percentText.innerText = Math.floor(currentProgress) + '%';
                if (elapsed < duration) {
                    requestAnimationFrame(step); 
                } else {
                    currentProgress = target; 
                    progressBar.style.width = currentProgress + '%';
                    percentText.innerText = Math.floor(currentProgress) + '%';
                    resolve(); 
                }
            }
            requestAnimationFrame(step);
        });
    }

    function waitForImages() {
        return new Promise(resolve => {
            const images = document.querySelectorAll('.logo-img');
            if (images.length === 0) resolve();
            let loaded = 0;
            images.forEach(img => {
                if (img.complete) {
                    loaded++;
                    if (loaded === images.length) resolve();
                } else {
                    img.addEventListener('load', () => { loaded++; if (loaded === images.length) resolve(); });
                    img.addEventListener('error', () => { loaded++; if (loaded === images.length) resolve(); });
                }
            });
        });
    }

    await Promise.all([animateProgress(10, "Loading visual assets...", 600), waitForImages()]);
    await animateProgress(80, "Loading IMS Environment...", 1500);
    await animateProgress(100, "Preparing Dashboard...", 400);
    loadingText.innerText = "Ready!";

    setTimeout(() => {
        loaderWrapper.style.opacity = '0';
        setTimeout(() => {
            loaderWrapper.style.display = 'none';
            loginModal.show();
        }, 500); 
    }, 400);

    // =========================================================
    // THE "FORM KILLER" & KEYBOARD LOGIC
    // =========================================================
    
    // 1. If the form still exists in some views, kill its default submit
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.doLogin();
        });
    }

    // 2. Handle the "Enter" key inside our new div container
    const loginContainer = document.getElementById('login-container');
    if (loginContainer) {
        loginContainer.addEventListener('keydown', function (e) {
            // Check if the pressed key is 'Enter'
            if (e.key === 'Enter' || e.keyCode === 13) {
                // Prevent the browser from doing anything else
                e.preventDefault(); 
                window.doLogin();
            }
        });
    }
});

// =========================================================
// 2. NETWORK CONNECTION MONITORING
// =========================================================
window.addEventListener('offline', () => {
    const currentUrl = encodeURIComponent(window.location.href);
    window.location.href = '/offline?returnTo=' + currentUrl;
});

// =========================================================
// 3. UI TOGGLES & TABS
// =========================================================
window.switchTab = function(mode) {
    document.getElementById('tab-email').classList.remove('active');
    document.getElementById('tab-id').classList.remove('active');
    document.getElementById('tab-' + mode).classList.add('active');
    document.getElementById('auth-mode-input').value = mode;
    const label = document.getElementById('id-label');
    const input = document.getElementById('identifier');
    
    if (mode === 'email') {
        label.textContent = 'Email address';
        input.placeholder = 'Enter your DepEd email';
    } else {
        label.textContent = 'Teacher ID';
        input.placeholder = 'Enter your Teacher ID';
    }
};

window.toggleEye = function() {
    const pwInput = document.getElementById('password');
    const eyeSvg = document.getElementById('eye-svg');
    if (pwInput.type === 'password') {
        pwInput.type = 'text';
        eyeSvg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        pwInput.type = 'password';
        eyeSvg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
};

window.toggleLoginMethod = function(e) {
    e.preventDefault();
    const standardView = document.getElementById('standard-login-container');
    const pinView      = document.getElementById('pin-login-container');
    const flag         = document.getElementById('use-pin-flag');
    const globalAlert  = document.getElementById('global-alert');

    // Hide any showing alerts on switch
    if (globalAlert) globalAlert.classList.add('d-none');

    const switchingToPin = standardView.style.display !== 'none';

    if (switchingToPin) {
        // Sync identifier value from standard email/teacher ID to PIN identifier input
        const standardIdent = document.getElementById('identifier').value.trim();
        const pinIdent = document.getElementById('pin-identifier');
        if (pinIdent) pinIdent.value = standardIdent;

        standardView.style.display = 'none';
        standardView.classList.remove('slide-up-fade');
        
        pinView.style.display = 'block';
        pinView.classList.add('slide-up-fade');
        
        flag.value = '1';
    } else {
        // Sync identifier back to standard input
        const pinIdentValue = document.getElementById('pin-identifier').value.trim();
        const standardIdent = document.getElementById('identifier');
        if (standardIdent) standardIdent.value = pinIdentValue;

        pinView.style.display = 'none';
        pinView.classList.remove('slide-up-fade');
        
        standardView.style.display = 'block';
        standardView.classList.add('slide-up-fade');
        
        flag.value = '0';
    }

    // Reset PIN inputs when switching
    document.getElementById('pin-input').value = '';
    syncPinDots('');
};

window.syncPinDots = function(val) {
    for (let i = 0; i < 4; i++) {
        const dot = document.getElementById('lp-dot-' + i);
        if (!dot) continue;
        if (i < val.length) {
            dot.style.background = '#1a7a5e';
            dot.style.transform  = 'scale(1.2)';
            dot.style.boxShadow  = '0 0 0 3px rgba(26, 122, 94, 0.15)';
        } else {
            dot.style.background = '#d0dde8';
            dot.style.transform  = 'scale(1)';
            dot.style.boxShadow  = 'none';
        }
    }
};

window.numpadPress = function(num) {
    const input = document.getElementById('pin-input');
    if (input.value.length >= 4) return;
    input.value += num;
    syncPinDots(input.value);
};

window.numpadDelete = function() {
    const input = document.getElementById('pin-input');
    input.value = input.value.slice(0, -1);
    syncPinDots(input.value);
};

// Add keyboard numeric listener when PIN panel is active
document.addEventListener('keydown', function(e) {
    const pinView = document.getElementById('pin-login-container');
    if (pinView && pinView.style.display !== 'none') {
        if (e.key >= '0' && e.key <= '9') {
            numpadPress(e.key);
        } else if (e.key === 'Backspace') {
            numpadDelete();
        } else if (e.key === 'Enter') {
            doLogin();
        }
    }
});

// =========================================================
// 4. SECURE API REQUESTS (Login & Register)
// =========================================================
window.doLogin = async function() {
    const usePinFlag   = document.getElementById('use-pin-flag');
    const isPinMode    = usePinFlag && usePinFlag.value === '1';
    const btn          = isPinMode ? document.getElementById('login-pin-btn') : document.getElementById('login-btn');
    const globalAlert  = document.getElementById('global-alert');

    let payload;

    if (isPinMode) {
        const identifier = document.getElementById('pin-identifier').value.trim();
        const pinInput   = document.getElementById('pin-input').value;
        const identErr   = document.getElementById('pin-ident-err');
        const pinErr     = document.getElementById('pin-err');

        if (!identifier) { identErr.style.display = 'block'; return; } else { identErr.style.display = 'none'; }
        if (!pinInput || pinInput.length !== 4) { pinErr.style.display = 'block'; return; } else { pinErr.style.display = 'none'; }
        
        payload = { login_id: identifier, pin: pinInput, use_pin: true };
    } else {
        const identifier = document.getElementById('identifier').value.trim();
        const password   = document.getElementById('password').value;
        const identErr   = document.getElementById('ident-err');
        const pwErr      = document.getElementById('pw-err');

        if (!identifier) { identErr.style.display = 'block'; return; } else { identErr.style.display = 'none'; }
        if (!password) { pwErr.style.display = 'block'; return; } else { pwErr.style.display = 'none'; }
        
        payload = { login_id: identifier, password: password, use_pin: false };
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Authenticating...';
    }
    if (globalAlert) globalAlert.classList.add('d-none');

    try {
        const response = await fetch('/login-process', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok) {
            if (globalAlert) {
                globalAlert.className = 'alert alert-success d-block';
                globalAlert.innerHTML = `<strong>Success!</strong> ${data.message}`;
            }
            setTimeout(() => { window.location.replace(data.redirect); }, 600);
        } else {
            if (globalAlert) {
                globalAlert.className = 'alert alert-danger d-block';
                globalAlert.textContent = data.message || 'Invalid credentials.';
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Sign in';
            }
        }
    } catch (error) {
        console.error("Login Error:", error);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Sign in';
        }
    }
};

window.doRegister = async function() {
    const btn = document.getElementById('register-btn');
    const alertBox = document.getElementById('register-alert');
    
    btn.disabled = true;
    btn.innerHTML = 'Processing...';

    try {
        const response = await fetch('/auth/register', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name: document.getElementById('reg-name').value,
                email: document.getElementById('reg-email').value,
                teacher_id: document.getElementById('reg-teacher-id').value,
                password: document.getElementById('reg-password').value
            })
        });

        const data = await response.json();
        if (response.ok) {
            alertBox.className = 'alert alert-success d-block';
            alertBox.textContent = data.message;
        } else {
            alertBox.className = 'alert alert-danger d-block';
            alertBox.textContent = data.message || 'Registration failed.';
            btn.disabled = false;
            btn.innerHTML = 'Create Account';
        }
    } catch (error) {
        btn.disabled = false;
        btn.innerHTML = 'Create Account';
    }
};

// =========================================================
// 5. SECURE LOGOUT LOGIC
// =========================================================

// This function just opens the modal
window.doLogout = function() {
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    logoutModal.show();

    // Attach a ONE-TIME listener to the confirmation button inside the modal
    const confirmBtn = document.getElementById('confirm-logout-btn');
    confirmBtn.onclick = async function() {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Signing out...';

        try {
            const response = await fetch('/auth/logout', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                window.location.replace('/');
            } else {
                // If something goes wrong, just force them out for safety
                window.location.replace('/');
            }
        } catch (error) {
            console.error("Logout error:", error);
            window.location.replace('/');
        }
    };
};

// =========================================================
// THEME TOGGLE LOGIC (Light/Dark Mode)
// =========================================================
window.toggleTheme = function() {
    const root = document.documentElement;
    const currentTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    
    // 1. Apply to the HTML tag
    root.setAttribute('data-theme', currentTheme);
    
    // 2. Save the choice to localStorage
    localStorage.setItem('theme', currentTheme);
    
    // 3. Optional: Update the icon if you have one
    updateThemeIcon(currentTheme);
};

// Run this on page load to sync icons/buttons
function updateThemeIcon(theme) {
    const icon = document.getElementById('theme-icon');
    if (!icon) return;

    if (theme === 'dark') {
        icon.classList.replace('bi-moon', 'bi-sun');
    } else {
        icon.classList.replace('bi-sun', 'bi-moon');
    }
}

// Ensure the icon is correct when the page finishes loading
document.addEventListener('DOMContentLoaded', () => {
    updateThemeIcon(localStorage.getItem('theme') || 'light');
});

// =========================================================
// THEME PERSISTENCE LOGIC
// =========================================================

window.toggleTheme = function() {
    const root = document.documentElement;
    // Switch between light and dark
    const newTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    
    // 1. Apply the theme to the root element
    root.setAttribute('data-theme', newTheme);
    
    // 2. Save to browser memory
    localStorage.setItem('theme', newTheme);
    
    // 3. Update the button icon
    updateThemeUI(newTheme);
};

function updateThemeUI(theme) {
    const themeIcon = document.getElementById('theme-icon');
    if (!themeIcon) return;

    if (theme === 'dark') {
        // In dark mode, show the Sun (to switch back to light)
        themeIcon.classList.replace('bi-moon', 'bi-sun');
    } else {
        // In light mode, show the Moon (to switch to dark)
        themeIcon.classList.replace('bi-sun', 'bi-moon');
    }
}

// Ensure the UI matches the saved theme when the page finishes loading
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    updateThemeUI(savedTheme);
});