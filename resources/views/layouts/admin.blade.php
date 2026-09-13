<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? 'Admin' }}">
    <meta name="description" content="BayCIS Admin Dashboard — Inventory Management System for Bay Central Elementary School. Manage assets, borrow requests, returns, and issuances.">
    <meta property="og:title" content="Admin Dashboard - BayCIS">
    <meta property="og:description" content="Bay Central Elementary School Inventory Management System.">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <title>Admin Dashboard - BayCIS</title>
    
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://quickchart.io">
    <link rel="dns-prefetch" href="https://quickchart.io">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') . '?v=' . filemtime(public_path('vendor/bootstrap/css/bootstrap.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') . '?v=' . filemtime(public_path('vendor/bootstrap-icons/bootstrap-icons.css')) }}">
    
    <!-- Theme Script (Prevents flash) + Palette -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const savedPalette = localStorage.getItem('palette') || 'default';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.setAttribute('data-palette', savedPalette);
        })();
    </script>
    
    <link rel="stylesheet" href="{{ asset('css/admin.css') . '?v=' . filemtime(public_path('css/admin.css')) }}">
    <script src="{{ asset('js/admin.js') . '?v=' . filemtime(public_path('js/admin.js')) }}" defer></script>

    <!-- Compact Mode Settings -->
    @php
        $settingsPath = storage_path('app/settings.json');
        $sysSettings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $isCompact = ($sysSettings['density'] ?? 'densityCozy') === 'densityCompact';
    @endphp
    @if($isCompact)
    <style>
        .admin-table th, .admin-table td,
        .issuance-table th, .issuance-table td,
        .table-responsive table th, .table-responsive table td {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            font-size: 13px !important;
        }
    </style>
    @endif
    <style>
        /* Fix sidebar scroll/float issue: use fixed positioning instead of sticky */
        @media (min-width: 992px) {
            .sidebar {
                position: fixed !important;
                top: 72px !important;
                left: 0;
                height: calc(100vh - 72px) !important;
                overflow-y: hidden !important;
            }
            .main-content {
                margin-left: 260px;
            }
            .admin-body {
                padding-left: 0;
            }
        }
    </style>
</head>
<body>


    @php
        $unreadAdminNotifs = 0;
        $adminNotifs = collect();
        $pendingBorrowRequests = 0;
        $pendingReturns = 0;
        $pendingConsumableIssuances = 0;
        $pendingAccountRequests = 0;
        try {
            if (auth()->check() && class_exists(\App\Models\Notification::class)) {
                $adminId = auth()->id();
                $unreadAdminNotifs = \App\Models\Notification::where('user_id', $adminId)->where('is_read', 0)->count();
                $adminNotifs = \App\Models\Notification::where('user_id', $adminId)->latest()->take(20)->get();
            }
            if (auth()->check() && class_exists(\App\Models\BorrowRequest::class)) {
                $pendingBorrowRequests = \App\Models\BorrowRequest::where('status', 'pending')->count();
                $pendingReturns = \App\Models\BorrowRequest::where('status', 'return_pending')->count();
            }
            if (auth()->check() && class_exists(\App\Models\ConsumableIssuance::class)) {
                $pendingConsumableIssuances = \App\Models\ConsumableIssuance::whereIn('status', ['pending_issue', 'issued'])->count();
            }
            // Account Lifecycle: activation/deactivation/deletion requests awaiting admin review
            if (auth()->check() && auth()->user()->role === 'admin' && class_exists(\App\Models\User::class)) {
                $pendingAccountRequests = \App\Models\User::where(function ($q) {
                        $q->whereNotNull('deactivation_requested_at')
                          ->orWhere(function ($qq) {
                              $qq->whereNotNull('deletion_requested_at')->whereNull('deletion_effective_at');
                          });
                    })
                    ->orWhere(function ($q) {
                        $q->where('registration_source', 'self_registered')
                          ->where('is_active', false)
                          ->whereNotNull('email_verified_at');
                    })
                    ->count();
            }
        } catch (\Throwable $e) {}
    @endphp

    {{-- ══════════════════════════════════════════
         UNIFIED HEADER — Logo + Search + Actions
         ══════════════════════════════════════════ --}}
    <header class="unified-header">
        <div class="header-left">
            <button class="icon-btn d-lg-none me-1 position-relative" onclick="toggleSidebar()" aria-label="Toggle menu" id="mobileNavToggle">
                <i class="bi bi-list fs-2"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle {{ ($pendingBorrowRequests + $pendingReturns + $pendingConsumableIssuances + $pendingAccountRequests) > 0 ? '' : 'd-none' }}" id="mobileNavDot">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </button>
            <div class="brand-logo"><i class="bi bi-box-seam-fill"></i></div>
            <span class="brand-text">BayCIS</span>

            {{-- VERTICAL DIVIDER --}}
            <div class="header-divider"></div>

            {{-- FAKE HIDDEN INPUTS to prevent Chrome autofill --}}
            <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none; height: 0; overflow: hidden;" aria-hidden="true">
                <input type="text" name="fakeusername" value="" autocomplete="off" tabindex="-1">
                <input type="password" name="fakepassword" value="" autocomplete="new-password" tabindex="-1">
            </div>

            <div class="search-bar">
                <i class="bi bi-search"></i>
                <input type="search" id="globalSystemSearch" name="navsearch" placeholder="Search pages, assets, property tags..." autocomplete="off" aria-label="Search">
                <div id="globalSearchDropdown" class="search-dropdown shadow-lg" style="display: none;">
                    <div class="search-dropdown-header">Quick Navigation</div>
                    <a href="{{ route('admin.dashboard') }}" class="search-dropdown-item" data-keywords="dashboard command center home"><i class="bi bi-grid-1x2"></i> Command Center</a>
                    <a href="{{ route('items.index') }}" class="search-dropdown-item" data-keywords="inventory assets items list stock"><i class="bi bi-box-fill"></i> Inventory</a>
                    <a href="{{ route('items.issuance') }}" class="search-dropdown-item" data-keywords="issuance issue assign asset"><i class="bi bi-box-arrow-right"></i> Issuance</a>
                    <a href="{{ route('admin.requests') }}" class="search-dropdown-item" data-keywords="borrow requests pending approve"><i class="bi bi-arrow-left-right"></i> Borrow Requests</a>
                    <a href="{{ route('admin.returns') }}" class="search-dropdown-item" data-keywords="return assets verify condition"><i class="bi bi-arrow-return-left"></i> Return Assets</a>
                    <a href="{{ route('items.history') }}" class="search-dropdown-item" data-keywords="history transaction log audit"><i class="bi bi-clock-history"></i> Transaction History</a>
                    <a href="{{ route('admin.reports') }}" class="search-dropdown-item" data-keywords="reports analytics statistics data"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
                    <a href="{{ route('items.archive') }}" class="search-dropdown-item" data-keywords="archive deleted trashed items"><i class="bi bi-archive"></i> Archived Assets</a>
                    @if(auth()->user()->role === 'admin')   
                    <a href="{{ route('admin.users') }}" class="search-dropdown-item" data-keywords="users management accounts people"><i class="bi bi-people"></i> User Management</a>
                    <a href="{{ route('admin.settings') }}" class="search-dropdown-item" data-keywords="settings configuration preferences"><i class="bi bi-gear"></i> Settings</a>
                    @endif
                    <a href="{{ route('admin.account-settings') }}" class="search-dropdown-item" data-keywords="account profile password security pin"><i class="bi bi-person-fill-lock"></i> Account Settings</a>
                    <div class="search-dropdown-empty" style="display: none;"><i class="bi bi-search"></i> No pages matched your search</div>
                </div>
            </div>
        </div>

        <div class="header-right">
            <!-- NOTIFICATION BELL DROPDOWN -->
            <div class="dropdown d-inline-block">
                <button class="icon-btn position-relative" id="adminBellBtn" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 8px;" data-poll-url="{{ route('notifications.poll') }}" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadAdminNotifs > 0 ? '' : 'd-none' }}" id="adminBellBadge" style="font-size: 0.65rem; padding: 0.25em 0.4em;">{{ $unreadAdminNotifs }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu" id="adminNotifDropdown">
                    <div class="notif-header">
                        <div class="notif-header-top">
                            <h6 class="notif-header-title">
                                <span>Notifications</span>
                                <span class="notif-unread-pill {{ $unreadAdminNotifs > 0 ? '' : 'd-none' }}" id="headerUnreadPill">{{ $unreadAdminNotifs }} new</span>
                            </h6>
                            <div class="notif-header-actions">
                                <button type="button" class="btn-notif-header-action {{ $unreadAdminNotifs > 0 ? '' : 'd-none' }}" id="btnMarkAllRead" title="Mark all notifications as read">
                                    <i class="bi bi-check2-all"></i> Mark all read
                                </button>
                                <button type="button" class="btn-notif-header-action text-danger {{ $adminNotifs->count() > 0 ? '' : 'd-none' }}" id="btnClearAllNotifs" title="Clear notifications">
                                    <i class="bi bi-trash3"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Segmented Filter Tabs -->
                        <div class="notif-tabs" role="tablist">
                            <button type="button" class="notif-tab active" data-notif-filter="all" id="notifTabAll">
                                <span>All</span>
                                <span class="notif-tab-badge" id="notifTabCountAll">{{ $adminNotifs->count() }}</span>
                            </button>
                            <button type="button" class="notif-tab" data-notif-filter="unread" id="notifTabUnread">
                                <span>Unread</span>
                                <span class="notif-tab-badge badge-unread" id="notifTabCountUnread">{{ $unreadAdminNotifs }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Notification List -->
                    <div class="notif-list-container" data-notif-list>
                        @forelse($adminNotifs as $notif)
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
                        <span><i class="bi bi-shield-check me-1"></i>System Updates</span>
                        <span class="text-secondary small">Realtime</span>
                    </div>
                </div>
            </div>

            <!-- THEME TOGGLE (Dark/Light Mode) -->
            <button class="icon-btn" id="headerThemeToggle" onclick="toggleTheme()" aria-label="Toggle dark/light mode" title="Toggle Dark/Light Mode">
                <i class="bi bi-sun" id="headerThemeIcon"></i>
            </button>
        </div>
    </header>

    {{-- ══════════════════════════════════════════
         BODY — Sidebar + Main Content
         ══════════════════════════════════════════ --}}
    <div class="admin-body d-flex">
        
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <aside class="sidebar" id="adminSidebar">
            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> <span>Command Center</span>
                </a>

                <div class="nav-category">Transactions</div>
                
                <a href="{{ route('items.index') }}" class="nav-item {{ request()->routeIs('items.index', 'items.create', 'items.edit') ? 'active' : '' }}">
                    <i class="bi bi-box-fill"></i> <span>Inventory</span>
                </a>

                <a href="{{ route('items.issuance') }}" class="nav-item {{ request()->routeIs('items.issuance') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-right"></i> <span>Issuance</span>
                    <span class="badge rounded-pill ms-auto {{ $pendingConsumableIssuances > 0 ? '' : 'd-none' }}" id="sidebarIssuanceBadge" style="font-size: 0.6rem; background: #dc3545; color: white; padding: 0.25em 0.6em;">{{ $pendingConsumableIssuances }}</span>
                </a>
                
                <a href="{{ route('admin.requests') }}" class="nav-item {{ request()->routeIs('admin.requests') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> <span>Borrow Requests</span>
                    <span class="badge rounded-pill ms-auto {{ $pendingBorrowRequests > 0 ? '' : 'd-none' }}" id="sidebarRequestsBadge" style="font-size: 0.6rem; background: #dc3545; color: white; padding: 0.25em 0.6em;">{{ $pendingBorrowRequests }}</span>
                </a>
                
                <a href="{{ route('admin.returns') }}" class="nav-item {{ request()->routeIs('admin.returns') ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-left"></i> <span>Return Assets</span>
                    <span class="badge rounded-pill ms-auto {{ $pendingReturns > 0 ? '' : 'd-none' }}" id="sidebarReturnsBadge" style="font-size: 0.6rem; background: #dc3545; color: white; padding: 0.25em 0.6em;">{{ $pendingReturns }}</span>
                </a>

                <a href="{{ route('items.history') }}" class="nav-item {{ request()->routeIs('items.history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> <span>Transaction History</span>
                </a>
                
                <a href="{{ route('admin.reports') }}" class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Reports</span>
                </a>

                <a href="{{ route('items.archive') }}" class="nav-item {{ request()->routeIs('items.archive') ? 'active' : '' }}">
                    <i class="bi bi-archive"></i> <span>Archived Assets</span>
                </a>

                @if(auth()->user()->role === 'admin')
                <div class="nav-category">System</div>
                {{-- @if(auth()->user()->role === 'admin') --}}
                <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> <span>User Management</span>
                    <span class="badge rounded-pill ms-auto {{ $pendingAccountRequests > 0 ? '' : 'd-none' }}" id="sidebarUsersBadge" style="font-size: 0.6rem; background: #f59e0b; color: white; padding: 0.25em 0.6em;">{{ $pendingAccountRequests }}</span>
                </a>
                
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> <span>Settings</span>
                </a>
                @endif

                <!-- <div class="nav-category">Personal</div>

                <a href="{{ route('admin.account-settings') }}" class="nav-item {{ request()->routeIs('admin.account-settings') ? 'active' : '' }}">
                    <i class="bi bi-person-fill-lock"></i> <span>Account Settings</span>
                </a> -->
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="profile-main">
                        <div class="user-avatar" data-account-url="{{ route('admin.account-settings') }}"><i class="bi bi-person"></i></div>
                        <div class="user-info">
                            <span class="user-name text-truncate" style="max-width: 110px;">{{ Auth::user()->name }}</span>
                            <span class="user-role">{{ Auth::user()->role === 'custodian' ? 'Property Custodian' : ucfirst(Auth::user()->role) }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-logout-inline" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Sign Out">
                        <i class="bi bi-power"></i>
                    </button>
                </div>
            </div>
        </aside>

        <main class="main-content flex-grow-1">
            <div class="p-4 p-md-5">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background: var(--bg-surface);">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-box-arrow-right text-danger" style="font-size: 3.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="color: var(--text-primary);">Wait! Signing Out?</h4>
                    <p class="text-secondary mb-4">Are you sure you want to end your current session? You'll need to log in again to access the dashboard.</p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-light px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">
                            Stay Logged In
                        </button>
                        <button type="button" class="btn btn-danger px-4 py-2 fw-semibold" id="confirm-logout-btn" style="border-radius: 10px;">
                            Yes, Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') . '?v=' . filemtime(public_path('vendor/bootstrap/js/bootstrap.bundle.min.js')) }}"></script>

    {{-- AppDrawer: uniform right slide-over for system modals (Inventory, Issuance, Requests, Returns, History, Archive…) --}}
    @include('admin.partials.app-drawer')

    {{-- ══════════════════════════════════════════
         TOAST NOTIFICATION SYSTEM
         ══════════════════════════════════════════ --}}
    <div id="toastContainer" class="toast-container position-fixed end-0 p-3" style="top: 78px; z-index: 99999; pointer-events: none;"></div>
    <script>
    // Flash session messages as toasts
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
    <script>
        // ── Burger / Sidebar Toggle ──────────────────────────────────────────
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isOpen  = sidebar.classList.contains('show');

            if (isOpen) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.add('show');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden'; // prevent background scroll
            }
        }

        // Close sidebar when any nav link is tapped on mobile
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('#adminSidebar .nav-item').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        const sidebar = document.getElementById('adminSidebar');
                        const overlay = document.getElementById('sidebarOverlay');
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                });
            });
        });

        // Theme Toggle Logic
        function toggleTheme() {
            const html = document.documentElement;
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Sync header theme icon
            const icon = document.getElementById('headerThemeIcon');
            if (icon) {
                icon.className = newTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
            }
            // Sync settings dark mode checkbox if present
            const toggle = document.getElementById('settingsDarkToggle');
            if (toggle) toggle.checked = newTheme === 'dark';
        }

        // Palette setter
        function setPalette(name) {
            localStorage.setItem('palette', name);
            document.documentElement.setAttribute('data-palette', name);
            // Update header checkmarks (if paletteOptions exists)
            document.querySelectorAll('#paletteOptions .palette-option').forEach(function(opt) {
                var check = opt.querySelector('[id^="palCheck-"]');
                if (check) check.style.display = 'none';
            });
            var activeCheck = document.getElementById('palCheck-' + name);
            if (activeCheck) activeCheck.style.display = 'inline';
            // Update settings page checkmarks
            document.querySelectorAll('#settingsPaletteOptions .settings-palette-opt').forEach(function(opt) {
                var check = opt.querySelector('[id^="sttPalCheck-"]');
                if (check) check.style.display = 'none';
            });
            var sttCheck = document.getElementById('sttPalCheck-' + name);
            if (sttCheck) sttCheck.style.display = 'inline';
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Sync theme icon on page load
            const savedTheme = localStorage.getItem('theme') || 'light';
            const savedPalette = localStorage.getItem('palette') || 'default';
            const icon = document.getElementById('headerThemeIcon');
            if (icon) {
                icon.className = savedTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
            }
        });

        // ── Global Search Bar (Page Navigation) ────────────────────────────
        const searchInput = document.getElementById('globalSystemSearch');
        const searchDropdown = document.getElementById('globalSearchDropdown');
        
        if (searchInput && searchDropdown) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                
                if (query.length === 0) {
                    searchDropdown.style.display = 'none';
                    return;
                }
                
                let hasVisibleItems = false;
                const items = searchDropdown.querySelectorAll('.search-dropdown-item');
                const emptyMsg = searchDropdown.querySelector('.search-dropdown-empty');
                
                items.forEach(function(item) {
                    const keywords = item.getAttribute('data-keywords') || '';
                    const text = item.textContent.toLowerCase().trim();
                    
                    if (text.includes(query) || keywords.includes(query)) {
                        item.style.display = 'flex';
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                if (emptyMsg) {
                    emptyMsg.style.display = hasVisibleItems ? 'none' : 'block';
                }
                
                searchDropdown.style.display = 'block';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-bar')) {
                    searchDropdown.style.display = 'none';
                }
            });
            
            // Prevent form submission on Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Click the first visible result
                    const visibleItem = searchDropdown.querySelector('.search-dropdown-item[style*="display: flex"]');
                    if (visibleItem) {
                        visibleItem.click();
                    }
                }
            });
        }

        // ── Profile Picture → Dedicated Account Settings Page ─────────────
        // Profile avatar click — handled in admin.js via data-account-url

        // ── N2.2 & N2.5: Keyboard Shortcuts + Recent Searches ─────────────
        (function() {
            // Recent searches in localStorage
            var recentSearches = JSON.parse(localStorage.getItem('global-recent-searches') || '[]');
            function saveRecentSearch(query) {
                if (!query.trim()) return;
                recentSearches = recentSearches.filter(function(s) { return s !== query; });
                recentSearches.unshift(query);
                if (recentSearches.length > 10) recentSearches = recentSearches.slice(0, 10);
                localStorage.setItem('global-recent-searches', JSON.stringify(recentSearches));
            }
            var searchInput = document.getElementById('globalSystemSearch');
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && this.value.trim()) {
                        saveRecentSearch(this.value.trim());
                    }
                });
                // Show recent searches when focusing on empty input
                searchInput.addEventListener('focus', function() {
                    if (!this.value && recentSearches.length > 0) {
                        var dd = document.getElementById('globalSearchDropdown');
                        if (dd) {
                            // Remove existing recent section
                            var existingRecent = dd.querySelector('.recent-searches-section');
                            if (existingRecent) existingRecent.remove();
                            // Add sub-header + recent items below the existing nav items
                            var recentDiv = document.createElement('div');
                            recentDiv.className = 'recent-searches-section';
                            recentDiv.innerHTML = '<div class="search-dropdown-header" style="border-top:1px solid var(--border-color);margin-top:0;"><i class="bi bi-clock-history me-1"></i>Recent Searches</div>'
                                + recentSearches.map(function(s) {
                                    return '<a href="#" class="search-dropdown-item recent-search-item" onclick="document.getElementById(\'globalSystemSearch\').value=\'' + s.replace(/'/g, "\\'") + '\';document.getElementById(\'globalSystemSearch\').dispatchEvent(new Event(\'input\'));return false;"><i class="bi bi-clock-history"></i>' + s + '</a>';
                                }).join('') 
                                + '<div class="search-dropdown-item" style="cursor:pointer;color:var(--accent-red);font-size:11px;justify-content:center;" onclick="localStorage.removeItem(\'global-recent-searches\');this.closest(\'.recent-searches-section\').remove();">Clear history</div>';
                            dd.appendChild(recentDiv);
                            dd.style.display = 'block';
                        }
                    }
                });
            }

            // N2.2: Keyboard Shortcuts Cheat Sheet
            // Shortcuts modal HTML (injected on ? keypress — handled in admin.js)

            // Keyboard shortcut listener — handled in admin.js

            // C1.2: Skeleton loading transition
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[data-skeleton]').forEach(function(el) {
                    var targetId = el.getAttribute('data-skeleton-target');
                    if (targetId) {
                        var target = document.getElementById(targetId);
                        if (target) target.style.display = 'none';
                    }
                    setTimeout(function() {
                        el.style.opacity = '0';
                        if (targetId) {
                            var t = document.getElementById(targetId);
                            if (t) t.style.display = '';
                        }
                        setTimeout(function() { el.style.display = 'none'; }, 300);
                    }, 400);
                });
            });
        })();
    </script>
    <!-- Notification polling handled in admin.js via data-poll-url -->

@if(auth()->check() && !auth()->user()->pin_setup_completed)
{{-- ══════════════════════════════════════════
     FIRST-LOGIN PIN SETUP OVERLAY
══════════════════════════════════════════ --}}
<div class="pin-overlay" id="pinSetupOverlay" data-pin-setup-url="{{ route('auth.set-pin') }}">
    <div class="pin-card" id="pinSetupCard">
        <div id="pinPhase1">
            <div class="pin-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="pin-card-title">Create Your PIN</div>
            <div class="pin-card-sub">Set a 4-digit PIN to secure your account.<br>You'll use this to confirm sensitive actions.</div>
            <div class="pin-dots-row">
                <div class="pin-dot" id="p1d0"></div><div class="pin-dot" id="p1d1"></div>
                <div class="pin-dot" id="p1d2"></div><div class="pin-dot" id="p1d3"></div>
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
        <div id="pinPhase2" style="display:none;">
            <div class="pin-card-icon" style="background: linear-gradient(135deg,#0f4c8a,#072d5a);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M9 12l2 2 4-4"/><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="pin-card-title">Confirm Your PIN</div>
            <div class="pin-card-sub">Re-enter your 4-digit PIN to confirm.</div>
            <div class="pin-dots-row">
                <div class="pin-dot" id="p2d0"></div><div class="pin-dot" id="p2d1"></div>
                <div class="pin-dot" id="p2d2"></div><div class="pin-dot" id="p2d3"></div>
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
            <button class="pin-key" onclick="document.getElementById('pinPhase2').style.display='none';document.getElementById('pinPhase1').style.display='block';window._pinPhase1='';" style="width:100%;background:none;color:#7a8299;font-size:13px;height:auto;padding:8px 0;">← Back</button>
        </div>
    </div>
</div>
<!-- PIN overlay CSS moved to admin.css; PIN setup JS uses data-pin-setup-url -->
<script>
(function(){
    window._pinPhase1 = '';
    window._pinPhase2 = '';
    var pinSetupUrl = (document.getElementById('pinSetupOverlay')?.getAttribute('data-pin-setup-url')) || '/auth/set-pin';
    function updateDots(phase, val) {
        for(let i=0;i<4;i++){
            const d=document.getElementById(phase+'d'+i);
            if(i<val.length){d.classList.add('filled');d.classList.remove('error');}
            else{d.classList.remove('filled','error');}
        }
    }
    function flashError(phase){
        for(let i=0;i<4;i++){
            const d=document.getElementById(phase+'d'+i);
            d.classList.add('error');
            setTimeout(()=>d.classList.remove('error','filled'),500);
        }
    }
    window.pinPad=function(phase,digit){
        const key=phase==='p1'?'_pinPhase1':'_pinPhase2';
        if(window[key].length>=4)return;
        window[key]+=digit;
        updateDots(phase,window[key]);
        if(window[key].length===4){
            if(phase==='p1'){
                setTimeout(()=>{document.getElementById('pinPhase1').style.display='none';document.getElementById('pinPhase2').style.display='block';},200);
            } else { setTimeout(()=>submitPin(),200); }
        }
    };
    window.pinDel=function(phase){
        const key=phase==='p1'?'_pinPhase1':'_pinPhase2';
        window[key]=window[key].slice(0,-1);
        updateDots(phase,window[key]);
    };
    async function submitPin(){
        const p2err=document.getElementById('p2-err');
        if(window._pinPhase1!==window._pinPhase2){
            flashError('p2');
            p2err.textContent='PINs do not match. Please try again.';
            setTimeout(()=>{window._pinPhase2='';updateDots('p2','');p2err.textContent='';},700);
            return;
        }
        try{
            const res=await fetch(pinSetupUrl,{
                method:'POST',credentials:'include',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                body:JSON.stringify({pin:window._pinPhase1})
            });
            const data=await res.json();
            if(res.ok){
                const card=document.getElementById('pinSetupCard');
                card.style.transform='scale(1.05)';card.style.opacity='0';card.style.transition='all 0.3s ease';
                setTimeout(()=>document.getElementById('pinSetupOverlay').remove(),350);
            } else {
                flashError('p2');p2err.textContent=data.message||'Failed to set PIN.';
                window._pinPhase2='';updateDots('p2','');
            }
        }catch(e){p2err.textContent='Network error. Please try again.';}
    }
})();
</script>
@endif
</body>
</html>