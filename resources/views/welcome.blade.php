<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS - Bay Central Elementary School</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="bg-light">

    {{-- ══════════════════════════════════════════
         OUR CUSTOM GREEN LOADER SCREEN
    ══════════════════════════════════════════ --}}
    <div id="loader-wrapper">
        <div class="logos-container">
            <img src="{{ asset('images/bces-logo.png') }}" alt="BCES Logo" class="logo-img">
            <img src="{{ asset('images/ims-logo.png') }}" alt="IMS Logo" class="logo-img">
        </div>
        
        <div class="progress loader-progress-track">
            <div id="progress-bar" class="progress-bar loader-progress-fill" role="progressbar" style="transition: none;"></div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-2" style="width: 300px;">
            <p class="text-muted small fw-bold mb-0" id="loading-text">Initializing...</p>
            <span class="fw-bold" style="color: #1a3a4a; font-size: 13px;" id="loading-percent">0%</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         NEW SPLIT-PANEL LOGIN MODAL
    ══════════════════════════════════════════ --}}
    <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <div class="modal-content bg-transparent border-0 shadow-lg"> <div class="lp-wrap position-relative">
                    <div class="lp-left">
                        <div class="lp-logo">
                            <div class="lp-logo-icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <circle cx="7" cy="7" r="5" stroke="#fff" stroke-width="1.5"/>
                                    <path d="M4 7h6M7 4v6" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="lp-logo-text">IMS · BCES</span>
                        </div>

                        <div id="global-alert" class="alert d-none mb-3" style="font-size: 0.85rem; padding: 10px; border-radius: 8px; z-index: 10;"></div>

                        <!-- STANDARD LOGIN CONTAINER -->
                        <div id="standard-login-container" class="slide-up-fade">
                            <p class="lp-heading">Welcome back</p>
                            <p class="lp-sub">Sign in to access your inventory dashboard.</p>

                            <div class="lp-tab-row">
                                <button class="lp-tab active" id="tab-email" onclick="switchTab('email')">Email</button>
                                <button class="lp-tab" id="tab-id" onclick="switchTab('id')">Teacher ID</button>
                            </div>

                            <div id="login-container">
                                @csrf
                                <input type="hidden" name="auth_mode" id="auth-mode-input" value="email">

                                <label class="lp-label" id="id-label" for="identifier">Email address</label>
                                <div class="lp-input-wrap">
                                    <input class="lp-input" id="identifier" name="identifier" type="text" placeholder="Enter your DepEd email" autocomplete="username" />
                                </div>
                                <div class="lp-err-msg" id="ident-err">This field is required.</div>

                                <input type="hidden" id="use-pin-flag" name="use_pin" value="0">

                                <!-- Password field -->
                                <div id="password-section">
                                    <label class="lp-label" for="password">Password</label>
                                    <div class="lp-input-wrap">
                                        <input class="lp-input" id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" />
                                        <button class="lp-eye" id="eye-btn" type="button" aria-label="Toggle password visibility" onclick="toggleEye()">
                                            <svg id="eye-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="lp-err-msg" id="pw-err">Password is required.</div>
                                </div>

                                <button class="lp-btn-login" id="login-btn" type="button" onclick="doLogin()">Sign in</button>
                            </div>

                            <a class="lp-forgot" href="#">Forgot password?</a>
                            <a class="lp-forgot" href="#" id="toggle-pin-btn" onclick="toggleLoginMethod(event)" style="margin-top: 6px; color: #1a7a5e; font-weight: 600;">Use PIN Instead</a>
                            <a class="lp-need-account" href="#" data-bs-toggle="modal" data-bs-target="#registrationModal">Need an account? Contact the School Custodian.</a>
                        </div>

                        <!-- PIN LOGIN CONTAINER (SLIDES UPWARDS WITH ANIMATION) -->
                        <div id="pin-login-container" style="display: none; width: 100%;">
                            <a class="lp-back-btn" href="#" onclick="toggleLoginMethod(event)" style="text-decoration: none; color: #7a8299; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px; transition: color 0.15s ease;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                Back to Password Login
                            </a>

                            <p class="lp-heading" style="margin-top: 4px;">Sign in with PIN</p>
                            <p class="lp-sub">Enter your credentials and 4-digit PIN.</p>

                            <label class="lp-label" for="pin-identifier">Email or Teacher ID</label>
                            <div class="lp-input-wrap">
                                <input class="lp-input" id="pin-identifier" placeholder="Enter DepEd email or Teacher ID" autocomplete="username" />
                            </div>
                            <div class="lp-err-msg" id="pin-ident-err">Email or Teacher ID is required.</div>

                            <label class="lp-label mt-3">4-Digit PIN</label>
                            <div class="lp-pin-dots" style="margin: 8px 0 16px;">
                                <span class="lp-pin-dot" id="lp-dot-0"></span>
                                <span class="lp-pin-dot" id="lp-dot-1"></span>
                                <span class="lp-pin-dot" id="lp-dot-2"></span>
                                <span class="lp-pin-dot" id="lp-dot-3"></span>
                            </div>
                            <input type="hidden" id="pin-input" name="pin" value="" />

                            <div class="pin-numpad" style="margin: 8px 0 12px;">
                                @foreach(['1','2','3','4','5','6','7','8','9'] as $num)
                                    <button type="button" class="pin-key" onclick="numpadPress('{{ $num }}')">{{ $num }}</button>
                                @endforeach
                                <button type="button" class="pin-key pin-key-empty"></button>
                                <button type="button" class="pin-key" onclick="numpadPress('0')">0</button>
                                <button type="button" class="pin-key pin-key-del" onclick="numpadDelete()">⌫</button>
                            </div>
                            <div class="lp-err-msg text-center" id="pin-err" style="min-height: 18px; margin-top: 2px;">PIN is required.</div>

                            <button class="lp-btn-login" id="login-pin-btn" type="button" onclick="doLogin()">Sign in</button>
                        </div>

                        <div class="lp-footer-note">
                            <div>Unified access for all roles</div>
                            <div class="lp-role-chips">
                                <span class="lp-chip chip-b">Borrower</span>
                                <span class="lp-chip chip-s">Staff</span>
                                <span class="lp-chip chip-a">Admin</span>
                            </div>
                        </div>
                    </div>

                    <div class="lp-right">
                        <svg class="rp-deco" viewBox="0 0 160 160" fill="none">
                            <rect x="100" y="8" width="22" height="22" rx="4" fill="#fff"/>
                            <rect x="128" y="8" width="22" height="22" rx="4" fill="#fff"/>
                            <rect x="100" y="36" width="22" height="22" rx="4" fill="#fff"/>
                            <rect x="128" y="36" width="22" height="22" rx="4" fill="#fff"/>
                            <rect x="100" y="64" width="22" height="22" rx="4" fill="#fff"/>
                            <rect x="128" y="64" width="22" height="22" rx="4" fill="#fff"/>
                        </svg>

                        <div class="rp-analytics-card shadow-sm">
                            <div class="rp-card-header">
                                <span class="rp-card-title">Inventory analytics</span>
                                <div class="rp-period-btns">
                                    <button class="rp-period active">Weekly</button>
                                    <button class="rp-period">Monthly</button>
                                </div>
                            </div>
                            <div class="rp-chart-area">
                                <svg width="100%" height="68" viewBox="0 0 260 68" preserveAspectRatio="none">
                                    <polyline points="0,52 40,38 80,48 120,22 160,34 200,16 260,28" fill="none" stroke="#d0dde8" stroke-width="1.5"/>
                                    <polyline points="0,60 40,46 80,54 120,32 160,44 200,24 260,36" fill="none" stroke="#1a3a4a" stroke-width="2" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="rp-days">
                                <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                            </div>
                        </div>

                        <div class="rp-donut-card shadow-sm">
                            <div class="rp-donut-wrap">
                                <svg width="64" height="64" viewBox="0 0 64 64">
                                    <circle cx="32" cy="32" r="24" fill="none" stroke="#e4e9f2" stroke-width="8"/>
                                    <circle cx="32" cy="32" r="24" fill="none" stroke="#1a3a4a" stroke-width="8" stroke-dasharray="90 60" stroke-dashoffset="24" stroke-linecap="round"/>
                                </svg>
                                <div class="rp-donut-label">
                                    <span class="rp-donut-pct">42%</span>
                                    <span class="rp-donut-sub">Total</span>
                                </div>
                            </div>
                            <div class="rp-donut-stats">
                                <div class="rp-stat-row"><div class="rp-dot" style="background:#1a3a4a;"></div><span class="rp-stat-label">Borrowed</span><span class="rp-stat-val">284</span></div>
                                <div class="rp-stat-row"><div class="rp-dot" style="background:#5dcaa5;"></div><span class="rp-stat-label">Returned</span><span class="rp-stat-val">196</span></div>
                                <div class="rp-stat-row"><div class="rp-dot" style="background:#fac775;"></div><span class="rp-stat-label">Pending</span><span class="rp-stat-val">88</span></div>
                            </div>
                        </div>

                        <p class="rp-tagline">Manage inventory efficiently</p>
                        <p class="rp-tagline-sub">Bay Central Elementary School · IMS tracks and manages your school assets with ease.</p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ACCOUNT REGISTRATION MODAL
    ══════════════════════════════════════════ --}}
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content" style="border-radius: 16px; border: 1px solid #dde2ec; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background-color: #fff;">
                <div class="modal-header border-0 pt-4 px-4 pb-0 position-relative">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" style="color: #141c2e; font-size: 17px;">
                        <span style="color: #1a3a4a; font-size: 20px;">ⓘ</span> Account Registration
                    </h5>
                    <button type="button" class="btn-close shadow-none position-absolute" style="top: 24px; right: 24px; font-size: 12px;" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3" style="color: #5a6175; font-size: 14px; line-height: 1.6;">
                    <p class="mb-3 fw-medium" style="color: #141c2e;">This system does not support self-registration.</p>
                    <p class="m-0">To obtain an account, please contact the School Custodian or the designated system administrator. They will create your account and provide your login credentials.</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 pt-2">
                    <button type="button" class="btn w-100 fw-semibold" data-bs-dismiss="modal" style="background: #1a3a4a; color: #fff; border-radius: 8px; height: 40px; font-size: 14px; transition: 0.2s;">Got it</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/loader.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const regModal = document.getElementById('registrationModal');
            if (regModal) {
                regModal.addEventListener('hidden.bs.modal', function () {
                    const loginModalEl = document.getElementById('loginModal');
                    if (loginModalEl) {
                        bootstrap.Modal.getOrCreateInstance(loginModalEl).show();
                    }
                });
            }
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered successfully with scope: ', registration.scope);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
    </script>
</body>
</html>