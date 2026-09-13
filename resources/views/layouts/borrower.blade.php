<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f9fafb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="BayCIS Borrower Portal — Request and manage borrowed assets, consumables, and view your transaction history.">
    <meta property="og:title" content="BayCIS - Borrower Portal">
    <meta property="og:description" content="Bay Central Elementary School Inventory Management System.">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <title>IMS - Borrower App</title>
    
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://quickchart.io">
    <link rel="dns-prefetch" href="https://quickchart.io">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') . '?v=' . filemtime(public_path('vendor/bootstrap/css/bootstrap.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') . '?v=' . filemtime(public_path('vendor/bootstrap-icons/bootstrap-icons.css')) }}">
    
    <link rel="stylesheet" href="{{ asset('css/borrower.css') . '?v=' . filemtime(public_path('css/borrower.css')) }}">
    <script src="{{ asset('js/borrower.js') . '?v=' . filemtime(public_path('js/borrower.js')) }}" defer></script>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const savedPalette = localStorage.getItem('palette') || 'default';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.setAttribute('data-palette', savedPalette);
        })();
    </script>
</head>
<body>
    @php
        $activeBorrows = 0;
        $pendingConfirmations = 0;
        $pendingBorrowRequests = 0;
        try {
            $activeBorrows = \App\Models\BorrowRequest::where('user_id', auth()->id())->whereIn('status', ['approved', 'active'])->count();
            $pendingConfirmations = \App\Models\ConsumableIssuance::where('user_id', auth()->id())->where('status', 'issued')->count();
            $pendingBorrowRequests = \App\Models\BorrowRequest::where('user_id', auth()->id())->where('status', 'pending')->count();
        } catch (\Throwable $e) {}
        
        $unreadBorrowerNotifs = 0;
        $borrowerNotifs = collect();
        try {
            if (class_exists(\App\Models\Notification::class)) {
                $unreadBorrowerNotifs = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', 0)->count();
                $borrowerNotifs = \App\Models\Notification::where('user_id', auth()->id())->latest()->take(20)->get();
            }
        } catch (\Throwable $e) {}
    @endphp

<div class="admin-layout d-flex">
    
    <!-- SYNCED SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand-logo"><i class="bi bi-box-seam-fill"></i></div>
            <span class="brand-text">BayCIS</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('borrower.dashboard') }}" class="nav-item {{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i> <span>Home</span>
            </a>
            <a href="{{ route('borrower.requests') }}" class="nav-item {{ request()->routeIs('borrower.requests') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> <span>Request Asset</span>
                <span class="badge rounded-pill ms-auto {{ $pendingBorrowRequests > 0 ? '' : 'd-none' }}" id="borrowerSidebarRequestsBadge" style="font-size: 0.6rem; background: var(--accent-color); color: #fff; padding: 0.25em 0.6em;">{{ $pendingBorrowRequests }}</span>
            </a>
            
            <a href="{{ route('borrower.returns') }}" class="nav-item {{ request()->routeIs('borrower.returns') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i> <span>Return Items</span>
                <span class="badge bg-danger rounded-pill ms-auto {{ $activeBorrows > 0 ? '' : 'd-none' }}" id="borrowerSidebarReturnsBadge">{{ $activeBorrows }}</span>
            </a>
            
            <a href="{{ route('borrower.issuance') }}" class="nav-item {{ request()->routeIs('borrower.issuance') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-right"></i> <span>My Issuances</span>
                <span class="badge rounded-pill ms-auto {{ $pendingConfirmations > 0 ? '' : 'd-none' }}" id="borrowerSidebarIssuanceBadge" style="font-size: 0.6rem; background: var(--accent-red); color: #fff; padding: 0.25em 0.6em;">{{ $pendingConfirmations }}</span>
            </a>
            
            <a href="{{ route('borrower.history') }}" class="nav-item {{ request()->routeIs('borrower.history') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> <span>History</span>
            </a>
            <a href="{{ route('borrower.account') }}" class="nav-item {{ request()->routeIs('borrower.account') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i> <span>Account</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="profile-main" onclick="window.location.href='{{ route('borrower.account') }}'" style="cursor: pointer;">
                    <div class="user-avatar" title="Account Settings"><i class="bi bi-person"></i></div>
                    <div class="user-info">
                        <span class="user-name text-truncate" style="max-width: 110px;">{{ Auth::user()->name }}</span>
                        <span class="user-role">{{ ucfirst(Auth::user()->role) }}</span>
                    </div>
                </div>
                <button type="button" class="btn-logout-inline" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Sign Out">
                    <i class="bi bi-power"></i>
                </button>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="page-title d-md-none fw-bold" style="font-size: 18px; color: var(--text-primary);">BayCIS</div>
            <div class="d-none d-md-block"></div>
            
            <div class="top-actions ms-auto d-flex align-items-center gap-1 gap-md-2">
                
                <!-- B-UI5: Connection Quality Indicator (moved BEFORE theme toggle) -->
                <div class="position-relative d-inline-block" id="connectionIndicator" title="Checking connection...">
                    <div style="width:8px;height:8px;border-radius:50%;background:#6b7280;display:inline-block;transition:all 0.3s ease;" id="connectionDot"></div>
                </div>

                <!-- THEME TOGGLE -->
                <button class="icon-btn" onclick="toggleTheme()" title="Toggle Theme">
                    <i id="theme-icon" class="bi bi-sun"></i>
                </button>

                <!-- NOTIFICATION BELL DROPDOWN -->
                <div class="dropdown d-inline-block">
                    <button class="icon-btn position-relative" id="borrowerBellBtn" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 6px;" data-poll-url="{{ route('notifications.poll') }}" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadBorrowerNotifs > 0 ? '' : 'd-none' }}" id="borrowerBellBadge" style="font-size: 0.6rem; padding: 0.2em 0.35em;">
                            {{ $unreadBorrowerNotifs }}
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu" id="borrowerNotifDropdown">
                        <div class="notif-header">
                            <div class="notif-header-top">
                                <h6 class="notif-header-title">
                                    <span>Notifications</span>
                                    <span class="notif-unread-pill {{ $unreadBorrowerNotifs > 0 ? '' : 'd-none' }}" id="headerUnreadPill">{{ $unreadBorrowerNotifs }} new</span>
                                </h6>
                                <div class="notif-header-actions">
                                    <button type="button" class="btn-notif-header-action {{ $unreadBorrowerNotifs > 0 ? '' : 'd-none' }}" id="btnMarkAllRead" title="Mark all notifications as read">
                                        <i class="bi bi-check2-all"></i> Mark all read
                                    </button>
                                    <button type="button" class="btn-notif-header-action text-danger {{ $borrowerNotifs->count() > 0 ? '' : 'd-none' }}" id="btnClearAllNotifs" title="Clear notifications">
                                        <i class="bi bi-trash3"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Segmented Filter Tabs -->
                            <div class="notif-tabs" role="tablist">
                                <button type="button" class="notif-tab active" data-notif-filter="all" id="notifTabAll">
                                    <span>All</span>
                                    <span class="notif-tab-badge" id="notifTabCountAll">{{ $borrowerNotifs->count() }}</span>
                                </button>
                                <button type="button" class="notif-tab" data-notif-filter="unread" id="notifTabUnread">
                                    <span>Unread</span>
                                    <span class="notif-tab-badge badge-unread" id="notifTabCountUnread">{{ $unreadBorrowerNotifs }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Scrollable Notification List -->
                        <div class="notif-list-container" data-notif-list>
                            @forelse($borrowerNotifs as $notif)
                                @php
                                    $meta = match ($notif->type) {
                                        'request'    => ['icon' => 'bi-arrow-left-right', 'class' => 'text-primary', 'bg' => 'var(--accent-blue-bg)'],
                                        'return'     => ['icon' => 'bi-arrow-return-left', 'class' => 'text-success', 'bg' => 'rgba(25,135,84,0.12)'],
                                        'approval'   => ['icon' => 'bi-check-circle-fill', 'class' => 'text-success', 'bg' => 'rgba(25,135,84,0.12)'],
                                        'consumable' => ['icon' => 'bi-box-seam-fill', 'class' => 'text-info', 'bg' => 'rgba(13,202,240,0.12)'],
                                        'alert'      => ['icon' => 'bi-exclamation-triangle-fill', 'class' => 'text-danger', 'bg' => 'rgba(220,53,69,0.12)'],
                                        default      => ['icon' => 'bi-bell-fill', 'class' => 'text-secondary', 'bg' => 'rgba(108,117,125,0.12)'],
                                    };
                                @endphp
                                <div class="notif-item {{ $notif->is_read ? 'is-read' : 'is-unread' }}" data-notif-id="{{ $notif->id }}" data-is-read="{{ $notif->is_read ? '1' : '0' }}">
                                    <a href="{{ route('notifications.read', $notif->id) }}" class="notif-item-link">
                                        <div class="notif-item-icon" style="background: {{ $meta['bg'] }};">
                                            <i class="bi {{ $meta['icon'] }} {{ $meta['class'] }}"></i>
                                        </div>
                                        <div class="notif-item-content">
                                            <div class="notif-item-header">
                                                <span class="notif-item-title">{{ $notif->title }}</span>
                                                <span class="notif-item-time"><i class="bi bi-clock me-1"></i>{{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Just now' }}</span>
                                            </div>
                                            <p class="notif-item-message">{{ $notif->message }}</p>
                                        </div>
                                    </a>
                                    <div class="notif-item-actions">
                                        <button type="button" class="btn-notif-action btn-notif-toggle" title="{{ $notif->is_read ? 'Mark as unread' : 'Mark as read' }}" data-action="toggle-read">
                                            <i class="bi {{ $notif->is_read ? 'bi-envelope' : 'bi-envelope-open' }}"></i>
                                        </button>
                                        <button type="button" class="btn-notif-action btn-notif-delete text-danger" title="Delete notification" data-action="delete">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="notif-empty-state">
                                    <div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>
                                    <div class="notif-empty-title">No notifications yet</div>
                                    <p class="notif-empty-subtitle">You're completely caught up!</p>
                                </div>
                            @endforelse
                        </div> 

                        <div class="notif-dropdown-footer">
                            <span><i class="bi bi-shield-check me-1"></i>Notifications Center</span>
                            <span class="text-secondary small">Realtime</span>
                        </div>
                    </div>
                </div>

                <!-- ACCOUNT SETTINGS (Avatar Circle with Initial) -->
                <a href="{{ route('borrower.account') }}" class="user-avatar-circle text-decoration-none d-flex align-items-center justify-content-center" title="Account Settings">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </a>

                <!-- <div class="d-none d-md-flex align-items-center justify-content-center user-avatar ms-1">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div> -->
            </div>
        </header>

        <div class="content-wrapper p-4">
            @yield('content')
            {{-- Spacer to push content above the fixed bottom nav on mobile --}}
            <div class="bottom-nav-safe-spacer"></div>
        </div>
    </main>

    <!-- MOBILE BOTTOM NAV -->
    <nav class="bottom-nav">
        <a href="{{ route('borrower.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('borrower.requests') }}" class="bottom-nav-item {{ request()->routeIs('borrower.requests') ? 'active' : '' }}">
            <span class="bn-icon-wrap position-relative">
                <i class="bi bi-box-seam"></i>
                <span class="bn-count {{ $pendingBorrowRequests > 0 ? '' : 'd-none' }}" id="bnRequestsCount">{{ $pendingBorrowRequests > 9 ? '9+' : $pendingBorrowRequests }}</span>
            </span>
            <span>Request</span>
        </a>
        <a href="{{ route('borrower.issuance') }}" class="bottom-nav-item {{ request()->routeIs('borrower.issuance') ? 'active' : '' }}" aria-label="Issuance">
            <span class="bn-icon-wrap position-relative">
                <i class="bi bi-box-arrow-in-right"></i>
                <span class="bn-count {{ $pendingConfirmations > 0 ? '' : 'd-none' }}" id="bnIssuanceCount">{{ $pendingConfirmations > 9 ? '9+' : $pendingConfirmations }}</span>
            </span>
            <span>Issuance</span>
        </a>
        <a href="{{ route('borrower.history') }}" class="bottom-nav-item {{ request()->routeIs('borrower.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>History</span>
        </a>
        
        <a href="{{ route('borrower.returns') }}" class="bottom-nav-item {{ request()->routeIs('borrower.returns') ? 'active' : '' }}" aria-label="Returns">
            <span class="bn-icon-wrap position-relative">
                <i class="bi bi-arrow-return-left"></i>
                <span class="bn-count {{ $activeBorrows > 0 ? '' : 'd-none' }}" id="bnReturnsCount">{{ $activeBorrows > 9 ? '9+' : $activeBorrows }}</span>
            </span>
            <span>Returns</span>
        </a>

    </nav>
</div>


<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; background: var(--accent-red-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent-red);"><i class="bi bi-box-arrow-right"></i></div>
                    Sign Out
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4 text-center">
                <p class="text-secondary mb-4">Are you sure you want to end your current session?</p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-light px-3 py-2 fw-semibold w-50" data-bs-dismiss="modal" style="border-radius: 10px; background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); font-size: 13px; white-space: nowrap;">Stay Logged In</button>
                    <button type="button" class="btn btn-danger px-3 py-2 fw-semibold w-50" id="confirm-logout-btn" style="border-radius: 10px; font-size: 13px; white-space: nowrap;">Yes, Sign Out</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') . '?v=' . filemtime(public_path('vendor/bootstrap/js/bootstrap.bundle.min.js')) }}"></script>

{{-- ══════════════════════════════════════════
     TOAST NOTIFICATION SYSTEM (CSS moved to borrower.css, JS moved to borrower.js)
     ══════════════════════════════════════════ --}}
<div id="toastContainer" class="toast-container position-fixed end-0 p-3" style="top: 78px; z-index: 99999; pointer-events: none;"></div>
<script>
// Flash session messages as toasts (Blade-dependent — must stay inline)
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    showToast('Success', '{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
    showToast('Error', '{{ session('error') }}', 'error');
    @endif
    @if(session('warning'))
    showToast('Warning', '{{ session('warning') }}', 'warning');
    @endif
    @if(session('info'))
    showToast('Info', '{{ session('info') }}', 'info');
    @endif
    @if(isset($errors) && $errors->any())
    showToast('Validation Error', '{{ $errors->first() }}', 'error');
    @endif
});
</script>
<!-- Theme toggle, pull-to-refresh, notification polling, and logout → moved to borrower.js -->

@if(auth()->check() && !auth()->user()->pin_setup_completed)
{{-- ══════════════════════════════════════════
     FIRST-LOGIN PIN SETUP OVERLAY
══════════════════════════════════════════ --}}
<div class="pin-overlay" id="pinSetupOverlay" data-pin-setup-url="{{ route('auth.set-pin') }}">
    <div class="pin-card" id="pinSetupCard">

        {{-- PHASE 1: Create PIN --}}
        <div id="pinPhase1">
            <div class="pin-card-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="pin-card-title">Create Your PIN</div>
            <div class="pin-card-sub">Set a 4-digit PIN to secure your account.<br>You'll use this to confirm requests.</div>
            <div class="pin-dots-row" id="p1-dots">
                <div class="pin-dot" id="p1d0"></div>
                <div class="pin-dot" id="p1d1"></div>
                <div class="pin-dot" id="p1d2"></div>
                <div class="pin-dot" id="p1d3"></div>
            </div>
            <div class="pin-numpad">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button class="pin-key" onclick="pinPad('p1','{{ $k }}')">{{ $k }}</button>
                @endforeach
                <button class="pin-key pin-key-empty"></button>
                <button class="pin-key" onclick="pinPad('p1','0')">0</button>
                <button class="pin-key pin-key-del" onclick="pinDel('p1')">⌫</button>
            </div>
            <div class="pin-error-msg" id="p1-err"></div>
        </div>

        {{-- PHASE 2: Confirm PIN --}}
        <div id="pinPhase2" style="display:none;">
            <div class="pin-card-icon" style="background: linear-gradient(135deg, var(--accent-blue), #072d5a);">
                <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="pin-card-title">Confirm Your PIN</div>
            <div class="pin-card-sub">Re-enter your 4-digit PIN to confirm.</div>
            <div class="pin-dots-row" id="p2-dots">
                <div class="pin-dot" id="p2d0"></div>
                <div class="pin-dot" id="p2d1"></div>
                <div class="pin-dot" id="p2d2"></div>
                <div class="pin-dot" id="p2d3"></div>
            </div>
            <div class="pin-numpad">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button class="pin-key" onclick="pinPad('p2','{{ $k }}')">{{ $k }}</button>
                @endforeach
                <button class="pin-key pin-key-empty"></button>
                <button class="pin-key" onclick="pinPad('p2','0')">0</button>
                <button class="pin-key pin-key-del" onclick="pinDel('p2')">⌫</button>
            </div>
            <div class="pin-error-msg" id="p2-err"></div>
            <button class="pin-key" onclick="document.getElementById('pinPhase2').style.display='none'; document.getElementById('pinPhase1').style.display='block'; window._pinPhase1='';" style="width:100%;background:none;color:var(--text-secondary);font-size:13px;height:auto;padding:8px 0;">← Back</button>
        </div>

    </div>
</div>
<!-- PIN overlay CSS moved to borrower.css -->
<script>
(function() {
    window._pinPhase1 = '';
    window._pinPhase2 = '';
    var pinSetupUrl = (document.getElementById('pinSetupOverlay')?.getAttribute('data-pin-setup-url')) || '/auth/set-pin';

    function updateDots(phase, val) {
        for (let i = 0; i < 4; i++) {
            const dot = document.getElementById(phase + 'd' + i);
            if (i < val.length) { dot.classList.add('filled'); dot.classList.remove('error'); }
            else                { dot.classList.remove('filled','error'); }
        }
    }

    function flashError(phase) {
        for (let i = 0; i < 4; i++) {
            const dot = document.getElementById(phase + 'd' + i);
            dot.classList.add('error');
            setTimeout(() => dot.classList.remove('error','filled'), 500);
        }
    }

    window.pinPad = function(phase, digit) {
        const key = phase === 'p1' ? '_pinPhase1' : '_pinPhase2';
        if (window[key].length >= 4) return;
        window[key] += digit;
        updateDots(phase, window[key]);

        if (window[key].length === 4) {
            if (phase === 'p1') {
                setTimeout(() => {
                    document.getElementById('pinPhase1').style.display = 'none';
                    document.getElementById('pinPhase2').style.display = 'block';
                }, 200);
            } else {
                setTimeout(() => submitPin(), 200);
            }
        }
    };

    window.pinDel = function(phase) {
        const key = phase === 'p1' ? '_pinPhase1' : '_pinPhase2';
        window[key] = window[key].slice(0, -1);
        updateDots(phase, window[key]);
    };

    async function submitPin() {
        const p2err = document.getElementById('p2-err');
        if (window._pinPhase1 !== window._pinPhase2) {
            flashError('p2');
            p2err.textContent = 'PINs do not match. Please try again.';
            setTimeout(() => { window._pinPhase2 = ''; updateDots('p2', ''); p2err.textContent = ''; }, 700);
            return;
        }
        try {
            const res = await fetch(pinSetupUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ pin: window._pinPhase1 })
            });
            const data = await res.json();
            if (res.ok) {
                const card = document.getElementById('pinSetupCard');
                card.style.transform = 'scale(1.05)';
                card.style.opacity = '0';
                card.style.transition = 'all 0.3s ease';
                setTimeout(() => document.getElementById('pinSetupOverlay').remove(), 350);
            } else {
                flashError('p2');
                p2err.textContent = data.message || 'Failed to set PIN.';
                window._pinPhase2 = '';
                updateDots('p2', '');
            }
        } catch(e) {
            p2err.textContent = 'Network error. Please try again.';
        }
    }
})();
</script>
@endif

</body>
</html>