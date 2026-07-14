<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - BayCIS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Theme Script (Prevents flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <!-- Compact Mode Settings -->
    @php
        $settingsPath = storage_path('app/settings.json');
        $sysSettings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $isCompact = ($sysSettings['density'] ?? 'densityCozy') === 'densityCompact';
    @endphp
    @if($isCompact)
    <style>
        .admin-table th, .admin-table td {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            font-size: 13px !important;
        }
    </style>
    @endif
</head>
<body>
    <!-- NOTIFICATION ENGINE -->
    @php
        $unreadAdminNotifs = 0;
        $adminNotifs = collect();
        try {
            if (class_exists(\App\Models\Notification::class)) {
                $unreadAdminNotifs = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', 0)->count();
                $adminNotifs = \App\Models\Notification::where('user_id', auth()->id())->latest()->take(5)->get();
            }
        } catch (\Throwable $e) {}
    @endphp

    <div class="admin-layout d-flex">
        
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <aside class="sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="brand-logo"><i class="bi bi-box-seam-fill"></i></div>
                <span class="brand-text">BayCIS</span>
            </div>

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
                </a>
                
                <a href="{{ route('admin.requests') }}" class="nav-item {{ request()->routeIs('admin.requests') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> <span>Borrow Requests</span>
                </a>
                
                <a href="{{ route('admin.returns') }}" class="nav-item {{ request()->routeIs('admin.returns') ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-left"></i> <span>Return Assets</span>
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

                <div class="nav-category">System</div>
                
                <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> <span>User Management</span>
                </a>
                
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> <span>Settings</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="profile-main">
                        <div class="user-avatar"><i class="bi bi-person"></i></div>
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

        <main class="main-content flex-grow-1">
            <header class="top-navbar">
                <div class="d-flex align-items-center gap-3 d-lg-none">
                    <button class="icon-btn" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-2"></i>
                    </button>
                    <span class="fw-bold fs-5" style="color: var(--text-primary);">BayCIS</span>
                </div>

                <div class="search-bar desktop-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="globalSystemSearch" placeholder="Search assets, property tags..." autocomplete="off">
                </div>

                <div class="top-actions ms-auto d-flex align-items-center gap-2">
                    
                    <!-- NOTIFICATION BELL DROPDOWN -->
                    <div class="dropdown d-inline-block">
                        <button class="icon-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 8px;">
                            <i class="bi bi-bell"></i>
                            @if($unreadAdminNotifs > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                {{ $unreadAdminNotifs }}
                            </span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 340px; border-radius: 16px; border: 1px solid var(--border-color); background: var(--bg-surface); padding: 0; overflow: hidden; margin-top: 12px;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: var(--bg-main);">
                                <h6 class="m-0 fw-bold" style="color: var(--text-primary);">Notifications</h6>
                                @if($unreadAdminNotifs > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold" style="font-size: 11px; color: var(--accent-color); background: none; border: none; padding: 2px 6px;">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                @forelse($adminNotifs as $notif)
                                    <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item p-3 border-bottom text-wrap w-100 text-start" style="white-space: normal; background: {{ $notif->is_read ? 'transparent' : 'var(--accent-blue-bg)' }}; border: none; cursor: pointer;">
                                            <div class="d-flex align-items-start gap-2">
                                                @if(!$notif->is_read)
                                                    <span class="mt-1 flex-shrink-0" style="width:8px; height:8px; border-radius:50%; background: var(--accent-color); display:inline-block;"></span>
                                                @else
                                                    <span class="mt-1 flex-shrink-0" style="width:8px; height:8px; display:inline-block;"></span>
                                                @endif
                                                <div>
                                                    <div class="fw-bold mb-1" style="font-size: 13px; color: var(--text-primary);">{{ $notif->title }}</div>
                                                    <div class="small" style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">{{ $notif->message }}</div>
                                                    <div class="mt-2" style="font-size: 10px; color: var(--text-secondary);"><i class="bi bi-clock me-1"></i>{{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Just now' }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <div class="p-4 text-center text-secondary small">
                                        <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                        No recent notifications.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- THEME TOGGLE -->
                    <button class="icon-btn" id="theme-toggle" onclick="toggleTheme()">
                        <i id="theme-icon" class="bi bi-sun"></i>
                    </button>
                </div>
            </header>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = newTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon && savedTheme === 'dark') {
                themeIcon.className = 'bi bi-moon-stars';
            }
        });

        // Logout Execution
        document.getElementById('confirm-logout-btn')?.addEventListener('click', async function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>...';
            this.disabled = true;
            try {
                const response = await fetch('/logout', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    }
                });
                if (response.ok) window.location.replace('/');
            } catch (error) { 
                console.error('Logout error:', error); 
                this.disabled = false; 
                this.innerHTML = 'Yes, Sign Out';
            }
        });
    </script>
@if(auth()->check() && !auth()->user()->pin_setup_completed)
{{-- ══════════════════════════════════════════
     FIRST-LOGIN PIN SETUP OVERLAY
══════════════════════════════════════════ --}}
<div class="pin-overlay" id="pinSetupOverlay">
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
<style>
.pin-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.pin-card {
    background: var(--bg-surface, #ffffff);
    border-radius: 28px;
    padding: 44px 36px 36px;
    width: 350px;
    max-width: 95vw;
    text-align: center;
    box-shadow: 0 24px 70px rgba(20, 28, 46, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255,255,255,0.7);
    animation: pinSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes pinSlideUp {
    from { transform: translateY(40px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.pin-card-icon {
    width: 68px; height: 68px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    transition: transform 0.3s ease;
}
.pin-card-icon:hover {
    transform: translateY(-4px) rotate(5deg);
}
.pin-card-icon svg { width: 28px; height: 28px; fill: none; stroke: #fff; stroke-width: 2; }
.pin-card-title {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: var(--text-primary, #111827);
    margin-bottom: 8px;
}
.pin-card-sub {
    font-size: 13.5px;
    color: var(--text-secondary, #6b7280);
    line-height: 1.5;
    margin-bottom: 28px;
}
.pin-dots-row {
    display: flex;
    justify-content: center;
    gap: 22px;
    margin: 20px 0 28px;
}
.pin-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    background: transparent;
    border: 2px solid #d1d5db;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pin-dot.filled {
    background: #10b981;
    border-color: #10b981;
    transform: scale(1.3);
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.6);
}
.pin-dot.error {
    background: #ef4444;
    border-color: #ef4444;
    box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
    animation: pinShake 0.4s ease;
}
@keyframes pinShake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}
.pin-numpad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px 20px;
    margin: 24px 0 20px;
    justify-items: center;
}
.pin-key {
    background: #f3f4f6;
    border: 1px solid rgba(0, 0, 0, 0.02);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 22px;
    font-weight: 600;
    color: #1f2937;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    -webkit-tap-highlight-color: transparent;
}
.pin-key:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}
.pin-key:active {
    transform: scale(0.92) translateY(0);
    background: #d1d5db;
}
.pin-key.pin-key-del {
    font-size: 18px;
    color: #4b5563;
    background: transparent;
    box-shadow: none;
    border-color: transparent;
}
.pin-key.pin-key-del:hover {
    background: rgba(0,0,0,0.04);
}
.pin-key.pin-key-empty {
    background: transparent;
    box-shadow: none;
    border-color: transparent;
    cursor: default;
    pointer-events: none;
}
.pin-error-msg { font-size: 12px; color: #ef4444; min-height: 18px; margin-top: 6px; }

[data-theme="dark"] .pin-card {
    background: #141c2e;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.5);
}
[data-theme="dark"] .pin-card-title { color: #f9fafb; }
[data-theme="dark"] .pin-card-sub   { color: #9ca3af; }
[data-theme="dark"] .pin-key {
    background: #1f2937;
    color: #f3f4f6;
    border-color: rgba(255, 255, 255, 0.02);
}
[data-theme="dark"] .pin-key:hover {
    background: #374151;
}
[data-theme="dark"] .pin-key:active {
    background: #4b5563;
}
[data-theme="dark"] .pin-dot { border-color: #4b5563; }
[data-theme="dark"] .pin-key.pin-key-del {
    color: #9ca3af;
    background: transparent;
}
[data-theme="dark"] .pin-key.pin-key-del:hover {
    background: rgba(255,255,255,0.04);
}
</style>
<script>
(function(){
    window._pinPhase1 = '';
    window._pinPhase2 = '';
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
            const res=await fetch('{{ route("auth.set-pin") }}',{
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