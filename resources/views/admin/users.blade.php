@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="page-header mb-0">
            <h1>User Management</h1>
            <p>Manage system access, roles, and view user statuses.</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 py-2 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createUserModal" style="border-radius: 10px; background-color: var(--accent-blue); border: none;">
            <i class="bi bi-person-plus"></i> Add New User
        </button>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         TOOLBAR â€” search Â· filters Â· clear
         (Google Admin console style)
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @php
        $roleOptions = [
            ['v' => 'all',        'l' => 'All roles'],
            ['v' => 'admin',      'l' => 'Admin'],
            ['v' => 'custodian',  'l' => 'Property Custodian'],
            ['v' => 'borrower',   'l' => 'Borrower'],
        ];
        $statusOptions = [
            ['v' => 'all',                    'l' => 'All statuses'],
            ['v' => 'active',                 'l' => 'Active'],
            ['v' => 'pending_otp',            'l' => 'Pending OTP'],
            ['v' => 'inactive',               'l' => 'Deactivated'],
            ['v' => 'pending_approval',       'l' => 'Pending activation'],
            ['v' => 'deactivation_requested', 'l' => 'Deactivation requested'],
            ['v' => 'deletion_requested',     'l' => 'Deletion requested'],
            ['v' => 'deletion_scheduled',     'l' => 'In deletion grace period'],
        ];
        $activeRole = request('role') ?: 'all';
        $activeStatus = request('status') ?: 'all';
        $activeSearch = request('search');
        $hasFilters = ($activeRole !== 'all') || ($activeStatus !== 'all') || $activeSearch;

        // Build clean URLs that merge/replace query params (and reset paging)
        $buildUrl = function (array $overrides = []) use ($activeSearch, $activeRole, $activeStatus) {
            $params = array_filter([
                'search' => $activeSearch ?: null,
                'role'   => $activeRole !== 'all' ? $activeRole : null,
                'status' => $activeStatus !== 'all' ? $activeStatus : null,
                'per_page' => request('per_page'),
            ]);
            foreach ($overrides as $k => $v) {
                if ($v === null || $v === '') { unset($params[$k]); } else { $params[$k] = $v; }
            }
            return request()->url() . (count($params) ? '?' . http_build_query($params) : '');
        };
        $roleLabel = collect($roleOptions)->firstWhere('v', $activeRole)['l'] ?? 'Role';
        $statusLabel = collect($statusOptions)->firstWhere('v', $activeStatus)['l'] ?? 'Status';
    @endphp

    <div class="panel-card px-3 py-2 mb-2" style="border-radius: 12px;">
        <form method="GET" action="{{ route('admin.users') }}" id="userToolbarForm" class="d-flex flex-wrap align-items-center gap-2 m-0">
            <i class="bi bi-search" style="color: var(--text-secondary); font-size: 15px;"></i>
            <input type="search" id="userSearchInput" name="search" value="{{ $activeSearch }}"
                   placeholder="Search name, email, or Teacher ID" autocomplete="off"
                   class="border-0 flex-grow-1"
                   style="background: transparent; outline: none; box-shadow: none; font-size: 13.5px; color: var(--text-primary); height: 42px; min-width: 160px;">
            <input type="hidden" name="role"   id="toolbarRole"   value="{{ $activeRole !== 'all' ? $activeRole : '' }}">
            <input type="hidden" name="status" id="toolbarStatus" value="{{ $activeStatus !== 'all' ? $activeStatus : '' }}">

            {{-- Role filter button --}}
            <div class="dropdown">
                <button type="button" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-1"
                        data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false"
                        style="border-radius: 18px; padding: 6px 14px; font-size: 12.5px;
                               border: 1px solid {{ $activeRole !== 'all' ? 'var(--accent-blue)' : 'var(--border-color)' }};
                               color: {{ $activeRole !== 'all' ? 'var(--accent-blue)' : 'var(--text-secondary)' }};
                               background: {{ $activeRole !== 'all' ? 'rgba(59, 130, 246, 0.08)' : 'transparent' }};">
                    <i class="bi bi-person-badge" style="font-size: 13px;"></i>
                    {{ $activeRole !== 'all' ? $roleLabel : 'Role' }}
                    <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 12px; border: 1px solid var(--border-color); min-width: 200px; padding: 6px;">
                    @foreach($roleOptions as $opt)
                    <li>
                        <button type="button" class="dropdown-item d-flex justify-content-between align-items-center rounded"
                                style="font-size: 13px; padding: 8px 12px;"
                                onclick="applyToolbarFilter('role', '{{ $opt['v'] === 'all' ? '' : $opt['v'] }}')">
                            <span>{{ $opt['l'] }}</span>
                            @if($activeRole === $opt['v'])<i class="bi bi-check2" style="color: var(--accent-blue);"></i>@endif
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Status filter button --}}
            <div class="dropdown">
                <button type="button" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-1"
                        data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false"
                        style="border-radius: 18px; padding: 6px 14px; font-size: 12.5px;
                               border: 1px solid {{ $activeStatus !== 'all' ? 'var(--accent-blue)' : 'var(--border-color)' }};
                               color: {{ $activeStatus !== 'all' ? 'var(--accent-blue)' : 'var(--text-secondary)' }};
                               background: {{ $activeStatus !== 'all' ? 'rgba(59, 130, 246, 0.08)' : 'transparent' }};">
                    <i class="bi bi-funnel" style="font-size: 13px;"></i>
                    {{ $activeStatus !== 'all' ? $statusLabel : 'Status' }}
                    <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 12px; border: 1px solid var(--border-color); min-width: 230px; max-height: 340px; overflow-y: auto; padding: 6px;">
                    @foreach($statusOptions as $opt)
                    <li>
                        <button type="button" class="dropdown-item d-flex justify-content-between align-items-center rounded"
                                style="font-size: 13px; padding: 8px 12px;"
                                onclick="applyToolbarFilter('status', '{{ $opt['v'] === 'all' ? '' : $opt['v'] }}')">
                            <span>{{ $opt['l'] }}</span>
                            @if($activeStatus === $opt['v'])<i class="bi bi-check2" style="color: var(--accent-blue);"></i>@endif
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>

            @if($hasFilters)
            <a href="{{ $buildUrl(['search' => null, 'role' => null, 'status' => null]) }}"
               data-users-nav data-clear-filters
               class="btn btn-sm d-inline-flex align-items-center gap-1"
               title="Clear all filters"
               style="border-radius: 18px; padding: 6px 12px; font-size: 12.5px; border: 1px solid var(--border-color); color: var(--text-secondary);">
                <i class="bi bi-x-lg" style="font-size: 12px;"></i> Clear all
            </a>
            @endif
        </form>
    </div>

    {{-- Applied filter chips --}}
    @if($hasFilters)
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 mt-1">
        <span style="font-size: 11.5px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Filters:</span>
        @if($activeSearch)
        <a href="{{ $buildUrl(['search' => null]) }}" data-users-nav data-remove-param="search"
           class="d-inline-flex align-items-center gap-2 text-decoration-none"
           style="border-radius: 16px; font-size: 12px; font-weight: 600; padding: 4px 10px; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.35); color: var(--accent-blue);">
            Search: &ldquo;{{ Str::limit($activeSearch, 24) }}&rdquo; <i class="bi bi-x-lg" style="font-size: 11px;"></i>
        </a>
        @endif
        @if($activeRole !== 'all')
        <a href="{{ $buildUrl(['role' => null]) }}" data-users-nav data-remove-param="role"
           class="d-inline-flex align-items-center gap-2 text-decoration-none"
           style="border-radius: 16px; font-size: 12px; font-weight: 600; padding: 4px 10px; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.35); color: var(--accent-blue);">
            {{ $roleLabel }} <i class="bi bi-x-lg" style="font-size: 11px;"></i>
        </a>
        @endif
        @if($activeStatus !== 'all')
        <a href="{{ $buildUrl(['status' => null]) }}" data-users-nav data-remove-param="status"
           class="d-inline-flex align-items-center gap-2 text-decoration-none"
           style="border-radius: 16px; font-size: 12px; font-weight: 600; padding: 4px 10px; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.35); color: var(--accent-blue);">
            {{ $statusLabel }} <i class="bi bi-x-lg" style="font-size: 11px;"></i>
        </a>
        @endif
    </div>
    @endif

    {{-- Live search / filter behavior is handled by the AJAX engine at the
         bottom of this page — no full-page reloads while typing. --}}

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         ACCOUNT REQUESTS PANEL
         (activation, deactivation & deletion requests)
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @php
        $lifecycleChips = [
            'pending_approval' => [
                'label' => 'Activation Requests', 'icon' => 'bi-person-plus',
                'count' => $lifecycleCounts['activation'] ?? 0,
                'desc'  => 'Self-registered users waiting for approval',
            ],
            'deactivation_requested' => [
                'label' => 'Deactivation Requests', 'icon' => 'bi-pause-circle',
                'count' => $lifecycleCounts['deactivation'] ?? 0,
                'desc'  => 'Users asking to deactivate their account',
            ],
            'deletion_requested' => [
                'label' => 'Deletion Requests', 'icon' => 'bi-trash3',
                'count' => $lifecycleCounts['deletion'] ?? 0,
                'desc'  => 'Users asking for permanent deletion',
            ],
            'deletion_scheduled' => [
                'label' => 'In Deletion Grace Period', 'icon' => 'bi-hourglass-split',
                'count' => $lifecycleCounts['scheduled'] ?? 0,
                'desc'  => 'Approved deletions inside the 60-day buffer',
            ],
        ];
    @endphp
    <div class="panel-card p-3 mb-3 {{ ($lifecycleCounts['total'] ?? 0) > 0 ? '' : 'opacity-75' }}" style="border-radius: 12px; border-left: 4px solid {{ ($lifecycleCounts['total'] ?? 0) > 0 ? '#f59e0b' : 'var(--border-color)' }};">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-bold me-2" style="font-size: 13px; color: var(--text-primary);">
                <i class="bi bi-inboxes me-1"></i> Account Requests
            </span>
            @foreach($lifecycleChips as $statusKey => $chip)
                <a href="{{ $buildUrl(['status' => $statusKey]) }}"
                   data-users-nav data-set-status="{{ $statusKey }}"
                   class="d-inline-flex align-items-center gap-2 px-3 py-2 text-decoration-none"
                   title="{{ $chip['desc'] }}"
                   style="border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid {{ $chip['count'] > 0 ? 'rgba(220, 53, 69, 0.35)' : 'var(--border-color)' }}; background: {{ $chip['count'] > 0 ? 'rgba(220, 53, 69, 0.07)' : 'transparent' }}; color: var(--text-primary);">
                    <i class="bi {{ $chip['icon'] }}" style="{{ $chip['count'] > 0 ? 'color: #dc3545;' : 'color: var(--text-secondary);' }}"></i>
                    {{ $chip['label'] }}
                    <span class="badge rounded-pill {{ $chip['count'] > 0 ? 'bg-danger' : 'bg-secondary' }}" data-lc-count="{{ $statusKey }}">{{ $chip['count'] }}</span>
                </a>
            @endforeach
        </div>
        <div class="mt-2" style="font-size: 11.5px; color: var(--text-secondary);">
            <i class="bi bi-info-circle me-1"></i>
            Use the inline approve / reject buttons on any pending row, or open its <strong>â‹® actions menu</strong>. Users can no longer be deleted directly â€” deletion always starts as a user request.
        </div>
    </div>

    {{-- UM-5: Bulk Action Bar --}}
    <div id="bulkActionBar" class="d-none align-items-center gap-2 px-3 py-2 mb-3" style="border-radius: 12px; background: var(--accent-color); color: var(--btn-text, #ffffff);">
        <i class="bi bi-person-check-fill ms-1"></i>
        <span class="fw-bold" id="bulkCount">0</span>
        <span class="opacity-75">selected</span>
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-light fw-semibold px-3" onclick="bulkAction('activate')" style="border-radius: 6px; font-size: 12px;">
                <i class="bi bi-check-circle me-1"></i> Activate
            </button>
            <button class="btn btn-sm btn-light fw-semibold px-3" onclick="bulkAction('deactivate')" style="border-radius: 6px; font-size: 12px;">
                <i class="bi bi-pause-circle me-1"></i> Deactivate
            </button>
            <div class="dropdown d-inline-block">
                <button class="btn btn-sm btn-light fw-semibold px-3 dropdown-toggle" data-bs-toggle="dropdown" style="border-radius: 6px; font-size: 12px;">
                    <i class="bi bi-shuffle me-1"></i> Change Role
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 10px;">
                    <li><button class="dropdown-item" onclick="bulkAction('set_admin')" style="font-size: 13px;">Set as Admin</button></li>
                    <li><button class="dropdown-item" onclick="bulkAction('set_custodian')" style="font-size: 13px;">Set as Property Custodian</button></li>
                    <li><button class="dropdown-item" onclick="bulkAction('set_borrower')" style="font-size: 13px;">Set as Borrower</button></li>
                </ul>
            </div>
            <button class="btn btn-sm btn-light fw-semibold px-3" onclick="clearBulkSelection()" style="border-radius: 6px; font-size: 12px;">
                <i class="bi bi-x-lg me-1"></i> Clear
            </button>
        </div>
    </div>

    <div id="users-table" class="panel-card p-0 overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 36px;" class="ps-3">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="cursor: pointer;">
                        </th>
                        <th class="ps-1">Name & Email</th>
                        <th>Teacher ID</th>
                        <th>Role {!! ' <span class="role-info-trigger" onclick="showRoleInfo(event)" style="cursor:pointer;color:var(--text-secondary);font-size:11px;" title="Click for role descriptions">&#9432;</span>' !!}</th>
                        <th>Status</th>
                        <th class="d-none d-md-table-cell">Last Login</th>
                        <th class="text-center" style="min-width: 60px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTbody">
                    @include('admin.users._rows')
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div id="usersFooter" class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border-color) !important;">
            <span id="usersRangeText" style="font-size: 12.5px; color: var(--text-secondary);">
                {{ $users->firstItem() ?? 0 }}&ndash;{{ $users->lastItem() ?? 0 }} of <strong style="color: var(--text-primary);">{{ $users->total() }}</strong>
            </span>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label style="font-size: 12px; color: var(--text-secondary); m-0;">Rows per page</label>
                    <select id="perPageSelect" class="form-select form-select-sm" style="width: auto; border-radius: 8px; font-size: 12px; background: var(--bg-surface); border-color: var(--border-color); color: var(--text-primary);">
                        @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a href="{{ $users->previousPageUrl() }}" data-users-nav id="usersPrevLink" tabindex="{{ $users->onFirstPage() ? -1 : 0 }}"
                       class="btn btn-sm d-inline-flex align-items-center justify-content-center {{ $users->onFirstPage() ? 'disabled' : '' }}"
                       title="Previous page" aria-label="Previous page"
                       style="border-radius: 8px; border: none; color: {{ $users->onFirstPage() ? 'var(--border-color)' : 'var(--text-secondary)' }}; pointer-events: {{ $users->onFirstPage() ? 'none' : 'auto' }};">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <span id="usersPageText" style="font-size: 12.5px; color: var(--text-secondary); padding: 0 4px;">
                        Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                    </span>
                    <a href="{{ $users->nextPageUrl() }}" data-users-nav id="usersNextLink" tabindex="{{ $users->hasMorePages() ? 0 : -1 }}"
                       class="btn btn-sm d-inline-flex align-items-center justify-content-center {{ $users->hasMorePages() ? '' : 'disabled' }}"
                       title="Next page" aria-label="Next page"
                       style="border-radius: 8px; border: none; color: {{ $users->hasMorePages() ? 'var(--text-secondary)' : 'var(--border-color)' }}; pointer-events: {{ $users->hasMorePages() ? 'auto' : 'none' }};">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     CREATE USER MODAL
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; background-color: var(--bg-surface); border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary);">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Full Name</label>
                        <input type="text" class="form-control custom-input" name="name" placeholder="e.g. Jane Smith" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Email Address</label>
                        <input type="email" class="form-control custom-input" name="email" placeholder="name@bces.edu.ph" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Teacher ID</label>
                            <input type="text" class="form-control custom-input" name="teacher_id" placeholder="TCH-XXX" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Role</label>
                            <select class="form-select custom-input" name="role" required>
                                <option value="borrower" selected>Borrower</option>
                                <option value="custodian">Property Custodian</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert mt-4 mb-3" style="background-color: var(--accent-blue-bg); border: 1px solid var(--accent-blue); color: var(--accent-blue); border-radius: 8px; font-size: 13px;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        The user's temporary password will be <strong>BayCIS2026!</strong> They will be required to verify via OTP on their first login.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                        <button type="button" class="btn btn-primary fw-bold px-4 btn-save-user" id="saveUserBtn" style="border-radius: 8px; background-color: var(--accent-blue); border: none; color: #ffffff !important;">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     UM-1: EDIT USER MODAL
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; background-color: var(--bg-surface); border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary);">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editUserForm">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Full Name</label>
                        <input type="text" class="form-control custom-input" name="name" id="editName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Email Address</label>
                        <input type="email" class="form-control custom-input" name="email" id="editEmail" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Teacher ID</label>
                            <input type="text" class="form-control custom-input" name="teacher_id" id="editTeacherId" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: var(--text-secondary); font-size: 13px;">Role</label>
                            <select class="form-select custom-input" name="role" id="editRole" required>
                                <option value="borrower">Borrower</option>
                                <option value="custodian">Property Custodian</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                            <label class="form-check-label fw-semibold" for="editIsActive" style="color: var(--text-secondary); font-size: 13px;">Account Active</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                        <button type="button" class="btn btn-primary fw-bold px-4" id="updateUserBtn" style="border-radius: 8px; background-color: var(--accent-blue); border: none; color: #ffffff !important;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     UM-6: USER DETAIL MODAL
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
{{-- ══════════════════════════════════════════
     UM-6: FULL-PAGE ACCOUNT DRAWER
     (Google Admin style — everything about one account)
     ══════════════════════════════════════════ --}}
<div id="userDrawerBackdrop" class="d-none" style="position:fixed; inset:0; background:rgba(16,24,40,.5); z-index:1050;" onclick="closeUserDrawer()"></div>
<aside id="userDrawer" aria-hidden="true"
       style="position:fixed; top:0; right:0; height:100vh; width:min(980px,100vw); background:var(--bg-surface); z-index:1051;
              box-shadow:-16px 0 48px rgba(0,0,0,.22); transform:translateX(102%); transition:transform .28s cubic-bezier(.4,0,.2,1);
              display:flex; flex-direction:column;">
    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom" style="border-color: var(--border-color) !important;">
        <button type="button" class="btn btn-sm" onclick="closeUserDrawer()" title="Back to list (Esc)"
                style="border-radius:8px; border:1px solid var(--border-color); color:var(--text-secondary); background:transparent;">
            <i class="bi bi-arrow-left"></i>
        </button>
        <div id="udAvatar" class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
             style="width:50px; height:50px; font-size:19px; background:var(--accent-blue-bg); color:var(--accent-blue);">?</div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 id="udName" class="fw-bold m-0 text-truncate" style="color:var(--text-primary);">Loading…</h5>
                <span id="udRoleBadge"></span>
                <span id="udStatusBadge"></span>
            </div>
            <div id="udEmail" class="text-truncate" style="font-size:13px; color:var(--text-secondary);">&nbsp;</div>
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <button type="button" id="udResetPwBtn" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-1"
                    style="border-radius:8px; border:1px solid var(--border-color); color:var(--text-primary); background:transparent;">
                <i class="bi bi-key" style="color:#f59e0b;"></i> Reset password
            </button>
            <button type="button" class="btn btn-sm btn-light" onclick="closeUserDrawer()" aria-label="Close"
                    style="border-radius:8px; border:1px solid var(--border-color); color:var(--text-secondary);">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    {{-- Contextual banner (pending request / scheduled deletion) --}}
    <div id="udBanner" class="px-4 py-2 border-bottom d-none" style="border-color: var(--border-color) !important;"></div>

    {{-- Body --}}
    <div class="flex-grow-1 overflow-auto p-4" style="background:var(--bg-main);">
        <div class="row g-3">
            {{-- Left: information --}}
            <div class="col-12 col-lg-7 d-flex flex-column gap-3">
                <div class="panel-card p-4" style="border-radius:12px;">
                    <h6 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-info-circle me-2 text-primary"></i>Account information</h6>
                    <div class="row g-3" style="font-size:13px;">
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">User ID</div><div class="fw-semibold" style="color:var(--text-primary);" id="udUserId">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Teacher ID</div><div class="fw-semibold" style="color:var(--text-primary);" id="udTeacherId">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Registered</div><div class="fw-semibold" style="color:var(--text-primary);" id="udRegistered">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Email verified</div><div class="fw-semibold" style="color:var(--text-primary);" id="udVerified">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Last sign-in</div><div class="fw-semibold" style="color:var(--text-primary);" id="udLastLogin">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Total sign-ins</div><div class="fw-semibold" style="color:var(--text-primary);" id="udLoginCount">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Security PIN</div><div class="fw-semibold" style="color:var(--text-primary);" id="udPinStatus">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Registration</div><div class="fw-semibold" style="color:var(--text-primary);" id="udRegSource">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Active borrows</div><div class="fw-semibold" style="color:var(--text-primary);" id="udActiveBorrows">—</div></div>
                        <div class="col-6"><div style="color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Lifetime transactions</div><div class="fw-semibold" style="color:var(--text-primary);" id="udTxCount">—</div></div>
                    </div>
                </div>

                <div class="panel-card p-4" style="border-radius:12px;">
                    <h6 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-clock-history me-2 text-primary"></i>Recent activity</h6>
                    <div id="udActivity" style="font-size:13px;"><span style="color:var(--text-secondary);">No recent activity.</span></div>
                </div>
            </div>

            {{-- Right: configuration & actions --}}
            <div class="col-12 col-lg-5 d-flex flex-column gap-3">
                <div class="panel-card p-4" style="border-radius:12px;">
                    <h6 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-sliders me-2 text-primary"></i>Configuration</h6>

                    <label class="form-label fw-semibold mb-1" style="font-size:12px; color:var(--text-secondary);">Full name</label>
                    <input type="text" id="udNameInput" class="form-control form-control-sm mb-2 theme-dynamic-input">

                    <label class="form-label fw-semibold mb-1" style="font-size:12px; color:var(--text-secondary);">Email address</label>
                    <input type="email" id="udEmailInput" class="form-control form-control-sm mb-2 theme-dynamic-input">

                    <label class="form-label fw-semibold mb-1" style="font-size:12px; color:var(--text-secondary);">Teacher ID</label>
                    <input type="text" id="udTeacherInput" class="form-control form-control-sm mb-3 theme-dynamic-input">

                    <label class="form-label fw-semibold mb-1" style="font-size:12px; color:var(--text-secondary);">Role</label>
                    <select id="udRoleSelect" class="form-select form-select-sm mb-3 theme-dynamic-input">
                        <option value="borrower">Borrower</option>
                        <option value="custodian">Property Custodian</option>
                        <option value="admin">Admin</option>
                    </select>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="udActiveSwitch">
                        <label class="form-check-label fw-semibold" for="udActiveSwitch" style="font-size:13px; color:var(--text-primary);">Account active (can sign in)</label>
                    </div>

                    <div id="udPinWrap" class="d-none mb-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:12px; color:#f59e0b;">
                            <i class="bi bi-shield-lock me-1"></i>Your admin PIN is required for this privilege change
                        </label>
                        <input type="password" id="udPrivilegePin" class="form-control form-control-sm" maxlength="4" inputmode="numeric" placeholder="••••" style="letter-spacing:4px; max-width:140px;">
                    </div>

                    <div id="udSaveError" class="text-danger small mb-2 d-none"></div>
                    <button type="button" id="udSaveBtn" class="btn btn-primary btn-sm fw-bold w-100" style="background:var(--accent-blue); border:none; border-radius:8px;">Save changes</button>
                </div>

                <div class="panel-card p-4" style="border-radius:12px; border-left:4px solid #dc3545;">
                    <h6 class="fw-bold mb-2" style="color:var(--text-primary);"><i class="bi bi-shield-exclamation me-2 text-danger"></i>Account requests</h6>
                    <div id="udLifecycle" style="font-size:13px; color:var(--text-secondary);">—</div>
                </div>

                <div class="panel-card p-4" style="border-radius:12px;">
                    <h6 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-tools me-2 text-primary"></i>Security tools</h6>

                    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <div class="fw-semibold" style="font-size:13px; color:var(--text-primary);">Password</div>
                            <div style="font-size:11.5px; color:var(--text-secondary);">Generate a temporary one or assign your own.</div>
                        </div>
                        <button type="button" id="udResetPwBtn2" class="btn btn-sm fw-semibold flex-shrink-0 d-inline-flex align-items-center gap-1"
                                style="border-radius:8px; border:1px solid var(--border-color); color:var(--text-primary); background:transparent;">
                            <i class="bi bi-key" style="color:#f59e0b;"></i> Reset
                        </button>
                    </div>

                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold" style="font-size:13px; color:var(--text-primary);">Security PIN</div>
                            <div style="font-size:11.5px; color:var(--text-secondary);" id="udPinHint">Clear their PIN if it was forgotten — they create a new one at next sign-in.</div>
                        </div>
                        <button type="button" id="udResetPinBtn" class="btn btn-sm fw-semibold flex-shrink-0 d-inline-flex align-items-center gap-1"
                                style="border-radius:8px; border:1px solid rgba(220,53,69,.4); color:#dc3545; background:transparent;">
                            <i class="bi bi-shield-lock"></i> Reset PIN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     UM-2: PASSWORD RESET MODAL
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; background-color: var(--bg-surface); border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary);">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="passwordResetBody">

                {{-- Step 1: choose mode --}}
                <div id="prStep1">
                    <p class="text-secondary mb-3" style="font-size: 13px;">
                        Choose how to set the new password for <strong id="prUserName" style="color: var(--text-primary);"></strong>.
                    </p>

                    <label class="d-flex align-items-start gap-2 p-3 mb-2" style="border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                        <input type="radio" name="prMode" value="generate" checked class="form-check-input mt-1" onchange="prModeChanged()">
                        <span>
                            <span class="fw-semibold d-block" style="font-size: 13.5px; color: var(--text-primary);">Auto-generate a temporary password</span>
                            <span class="d-block" style="font-size: 12px; color: var(--text-secondary);">System creates a random password you share with the user.</span>
                        </span>
                    </label>

                    <label class="d-flex align-items-start gap-2 p-3 mb-3" style="border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                        <input type="radio" name="prMode" value="assign" class="form-check-input mt-1" onchange="prModeChanged()">
                        <span>
                            <span class="fw-semibold d-block" style="font-size: 13.5px; color: var(--text-primary);">Assign a specific password</span>
                            <span class="d-block" style="font-size: 12px; color: var(--text-secondary);">You set the password yourself.</span>
                        </span>
                    </label>

                    <div id="prAssignFields" class="d-none mb-3 ps-1">
                        <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: var(--text-secondary);">New password</label>
                        <input type="password" id="prPassword" class="form-control form-control-sm mb-2 theme-dynamic-input" autocomplete="new-password" placeholder="At least 8 characters">
                        <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: var(--text-secondary);">Confirm password</label>
                        <input type="password" id="prPasswordConfirm" class="form-control form-control-sm theme-dynamic-input" autocomplete="new-password" placeholder="Re-enter password">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="prRequireChange" checked>
                        <label class="form-check-label" for="prRequireChange" style="font-size: 12.5px; color: var(--text-secondary);">
                            Require password change at first sign-in
                            <span class="d-block" style="font-size: 11px;">The account is deactivated until they complete verification with the new credentials.</span>
                        </label>
                    </div>

                    <div id="prError" class="text-danger small mt-3 d-none"></div>
                </div>

                {{-- Step 2: result --}}
                <div id="prStep2" class="d-none text-center">
                    <div style="width: 56px; height: 56px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="bi bi-check-circle" style="font-size: 28px; color: #10b981;"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--text-primary);" id="prResultTitle">Password Reset Successful</h5>
                    <p class="text-secondary mb-2" style="font-size: 13px;" id="prResultMsg"></p>
                    <div id="prGeneratedWrap" class="mb-3 d-flex align-items-center justify-content-center gap-2">
                        <code id="tempPasswordDisplay" style="font-size: 18px; padding: 8px 16px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; color: var(--accent-blue); font-weight: 700;"></code>
                        <button class="btn btn-sm btn-light" onclick="copyTempPassword()" title="Copy to clipboard" style="border: 1px solid var(--border-color); border-radius: 6px;">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <p class="text-muted" id="prResultNote" style="font-size: 11px;"></p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);" id="prCancelBtn">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="prSubmitBtn" style="border-radius: 8px;">Reset Password</button>
            </div>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     ðŸ”’ PRIVILEGE CHANGE PIN CONFIRMATION MODAL
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="modal fade" id="privilegePinModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content" style="border-radius: 16px; background-color: var(--bg-surface); border: 1px solid var(--border-color); box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" style="color: var(--text-primary);">
                    <div style="width: 32px; height: 32px; background: rgba(220, 53, 69, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-lock text-danger"></i>
                    </div>
                    Security Verification
                </h5>
            </div>
            <div class="modal-body px-4 py-3 text-center">
                <div class="mb-3 p-3" style="background: rgba(220, 53, 69, 0.05); border: 1px solid rgba(220, 53, 69, 0.2); border-radius: 10px;">
                    <i class="bi bi-exclamation-triangle-fill text-danger d-block mb-2" style="font-size: 24px;"></i>
                    <p class="fw-bold mb-1" style="font-size: 14px; color: var(--text-primary);" id="privilegeActionTitle">Change Privileges?</p>
                    <p class="text-secondary mb-0" style="font-size: 12px;" id="privilegeActionDesc">This user's access level will change.</p>
                </div>
                <p class="text-secondary mb-2" style="font-size: 12px;">Enter your <strong>4-digit PIN</strong> to authorize this change:</p>
                
                {{-- Desktop: Hidden input for keyboard entry --}}
                <div class="d-none d-md-block mb-3">
                    <input type="password" 
                           id="privilegeKeyboardInput" 
                           class="form-control text-center" 
                           maxlength="4" 
                           inputmode="numeric" 
                           pattern="[0-9]{4}"
                           autocomplete="off"
                           placeholder="&bull;&bull;&bull;&bull;"
                           style="font-size: 24px; letter-spacing: 8px; font-weight: 700; max-width: 180px; margin: 0 auto; border-radius: 10px; background: var(--bg-main); border: 2px solid var(--border-color); color: var(--text-primary); padding: 12px; box-shadow: none;">
                </div>

                {{-- PIN Dots --}}
                <div class="d-flex justify-content-center gap-3 mb-3" id="privilegePinDots">
                    <div class="privilege-dot" id="privDot0"></div>
                    <div class="privilege-dot" id="privDot1"></div>
                    <div class="privilege-dot" id="privDot2"></div>
                    <div class="privilege-dot" id="privDot3"></div>
                </div>

                <div class="privilege-error text-danger mb-2" id="privilegePinError" style="font-size: 12px; min-height: 20px;"></div>

                <input type="hidden" id="privilegePinValue" value="">

                {{-- Numpad (hidden on desktop) --}}
                <div class="d-flex d-md-none flex-wrap justify-content-center gap-2" style="max-width: 220px; margin: 0 auto;">
                    @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                        <button type="button" class="btn btn-lg fw-bold privilege-key" onclick="privilegePinPad('{{ $k }}')" style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 22px; transition: all 0.1s ease;">{{ $k }}</button>
                    @endforeach
                    <button type="button" class="btn btn-lg privilege-key" style="width: 64px; height: 64px; border-radius: 50%; background: transparent; border: none; cursor: default;" disabled></button>
                    <button type="button" class="btn btn-lg fw-bold privilege-key" onclick="privilegePinPad('0')" style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 22px;">0</button>
                    <button type="button" class="btn btn-lg privilege-key" onclick="privilegePinDel()" style="width: 64px; height: 64px; border-radius: 50%; background: transparent; border: none; color: var(--text-secondary); font-size: 18px;">&#9003;</button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" onclick="cancelPrivilegePin()" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="confirmPrivilegeBtn" disabled style="border-radius: 8px;">
                    <i class="bi bi-shield-check me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     ACCOUNT LIFECYCLE CONFIRMATION MODAL
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="modal fade" id="lifecycleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content" style="border-radius: 16px; background-color: var(--bg-surface); border: 1px solid var(--border-color); box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" style="color: var(--text-primary);">
                    <div id="lifecycleIconWrap" style="width: 32px; height: 32px; background: rgba(220, 53, 69, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-exclamation text-danger"></i>
                    </div>
                    <span id="lifecycleModalTitle">Confirm Action</span>
                </h5>
            </div>
            <div class="modal-body px-4 py-3">
                <div id="lifecycleDescWrap" class="mb-3 p-3" style="background: rgba(220, 53, 69, 0.05); border: 1px solid rgba(220, 53, 69, 0.2); border-radius: 10px; text-align: center;">
                    <i class="bi bi-info-circle-fill text-danger d-block mb-2" style="font-size: 24px;"></i>
                    <p class="fw-bold mb-1" style="font-size: 14px; color: var(--text-primary);" id="lifecycleUserName"></p>
                    <p class="text-secondary mb-0" style="font-size: 12px;" id="lifecycleUserDesc"></p>
                </div>
                <div id="lifecycleReasonWrap" class="d-none mb-2 p-2" style="background: var(--bg-main); border-radius: 8px;">
                    <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">User's Reason</div>
                    <div style="font-size: 13px; color: var(--text-primary);" id="lifecycleReasonText"></div>
                </div>
                <input type="hidden" id="lifecycleUserId" value="">
                <input type="hidden" id="lifecycleActionName" value="">
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                <button type="button" class="btn fw-bold px-4" id="confirmLifecycleBtn" style="border-radius: 8px;">Confirm</button>
            </div>
        </div>
    </div>
</div>

<style>
.privilege-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    background: transparent;
    border: 2px solid #d1d5db;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.privilege-dot.filled {
    background: #dc3545;
    border-color: #dc3545;
    transform: scale(1.3);
    box-shadow: 0 0 12px rgba(220, 53, 69, 0.6);
}
.privilege-dot.error {
    background: #ef4444;
    border-color: #ef4444;
    box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
    animation: pinShake 0.4s ease;
}
.privilege-key:active {
    transform: scale(0.92) !important;
    background: #d1d5db !important;
}
[data-theme="dark"] .privilege-key {
    background: #1f2937 !important;
    color: #f3f4f6 !important;
    border-color: rgba(255,255,255,0.08) !important;
}
[data-theme="dark"] .privilege-key:active {
    background: #374151 !important;
}
[data-theme="dark"] .privilege-dot {
    border-color: #4b5563;
}
@keyframes pinShake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}
</style>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     UM-7: ROLE INFO POPOVER
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="roleInfoPopover" class="d-none position-fixed" style="z-index: 99999; width: 280px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,0.15); padding: 16px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold m-0" style="color: var(--text-primary); font-size: 14px;"><i class="bi bi-info-circle me-1"></i> Role Guide</h6>
        <button class="btn btn-sm p-0" onclick="document.getElementById('roleInfoPopover').classList.add('d-none')" style="border: none; background: none; color: var(--text-secondary); font-size: 18px; line-height: 1;">&times;</button>
    </div>
    <div class="mb-2 p-2" style="background: var(--accent-purple-bg, rgba(139, 92, 246, 0.08)); border-radius: 8px;">
        <div class="fw-bold" style="font-size: 12px; color: var(--accent-purple, #8b5cf6);"><i class="bi bi-shield-lock me-1"></i> Admin</div>
        <div style="font-size: 11px; color: var(--text-secondary); line-height: 1.4;">Full system access â€” manage inventory, approve requests, create users, generate reports, configure settings</div>
    </div>
    <div class="mb-2 p-2" style="background: var(--accent-green-bg, rgba(25, 135, 84, 0.08)); border-radius: 8px;">
        <div class="fw-bold" style="font-size: 12px; color: var(--accent-green, #198754);"><i class="bi bi-key me-1"></i> Property Custodian</div>
        <div style="font-size: 11px; color: var(--text-secondary); line-height: 1.4;">Day-to-day operations â€” manage inventory, approve requests, verify returns, issue supplies, view reports and audit logs (read-only)</div>
    </div>
    <div class="p-2" style="background: var(--accent-blue-bg, rgba(59, 130, 246, 0.08)); border-radius: 8px;">
        <div class="fw-bold" style="font-size: 12px; color: var(--accent-blue);"><i class="bi bi-person me-1"></i> Borrower</div>
        <div style="font-size: 11px; color: var(--text-secondary); line-height: 1.4;">Self-service â€” request assets, return items, view history, manage own account and PIN</div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     TOAST (for inline notifications)
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;">
    <div id="liveToast" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px; background-color: var(--bg-surface);">
        <div class="d-flex align-items-center p-3 rounded-top" id="toastHeader">
            <i id="toastIcon" class="bi bi-check-circle-fill text-white me-2 fs-5"></i>
            <div class="toast-body flex-grow-1 fw-bold text-white p-0 ms-2" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// â”€â”€ Toast Engine â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showNotify(message, type = 'success') {
    const toastElement = document.getElementById('liveToast');
    const toastHeader = document.getElementById('toastHeader');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    
    if (type === 'success') {
        toastHeader.style.backgroundColor = 'var(--accent-green)';
        toastIcon.className = 'bi bi-check-circle-fill text-white fs-5';
    } else {
        toastHeader.style.backgroundColor = 'var(--accent-red)';
        toastIcon.className = 'bi bi-exclamation-circle-fill text-white fs-5';
    }
    toastMessage.innerText = message;
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
}

// â”€â”€ CSRF Token â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// â”€â”€ CREATE USER LOGIC â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createUserForm');

    async function submitCreateUser() {
        if (!form) return;
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const saveBtns = document.querySelectorAll('#saveUserBtn, .btn-save-user');
        saveBtns.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';
        });

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        try {
            const response = await fetch('/admin/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (response.ok) {
                const modalEl = document.getElementById('createUserModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                if (window.AppDrawer && window.AppDrawer.isOpen()) {
                    window.AppDrawer.close();
                }
                showNotify("Account created successfully!", "success");
                form.reset();
                saveBtns.forEach(btn => {
                    btn.disabled = false;
                    btn.innerHTML = 'Create Account';
                });
                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                let errorMessage = 'Failed to create user.\n';
                if (result.errors) { for (const [field, errors] of Object.entries(result.errors)) { errorMessage += `${errors[0]}\n`; } }
                showNotify(errorMessage, "error");
                saveBtns.forEach(btn => {
                    btn.disabled = false;
                    btn.innerHTML = 'Create Account';
                });
            }
        } catch (error) {
            showNotify("A network error occurred. Please try again.", "error");
            saveBtns.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = 'Create Account';
            });
        }
    }

    // Delegated click handler: works when button is in modal dialog or inside AppDrawer
    document.addEventListener('click', function (e) {
        if (e.target.closest('#saveUserBtn, .btn-save-user')) {
            e.preventDefault();
            submitCreateUser();
        }
    });

    // Support keyboard Enter or explicit form submit
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitCreateUser();
        });
    }

    // â”€â”€ Fix pagination scroll â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#users-table');
        }
    });
});

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// UM-1: EDIT USER (with Promotion/Demotion PIN Security)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
async function editUser(userId) {
    try {
        const res = await fetch('/admin/users/' + userId + '/edit', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (!res.ok) { showNotify('Failed to load user data.', 'error'); return; }
        
        document.getElementById('editUserId').value = data.id;
        document.getElementById('editName').value = data.name;
        document.getElementById('editEmail').value = data.email;
        document.getElementById('editTeacherId').value = data.teacher_id || '';
        document.getElementById('editRole').value = data.role;
        document.getElementById('editRole').setAttribute('data-original-role', data.role);
        document.getElementById('editIsActive').checked = data.is_active;
        
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    } catch (e) {
        showNotify('Network error loading user data.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const updateBtn = document.getElementById('updateUserBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', async function () {
            const userId = document.getElementById('editUserId').value;
            const oldRole = document.getElementById('editRole').getAttribute('data-original-role');
            const newRole = document.getElementById('editRole').value;
            
            const data = {
                name: document.getElementById('editName').value,
                email: document.getElementById('editEmail').value,
                teacher_id: document.getElementById('editTeacherId').value,
                role: newRole,
                is_active: document.getElementById('editIsActive').checked ? 1 : 0,
            };

            // ðŸ”’ If promoting to admin OR demoting from admin, require PIN verification
            const isPromotion = (oldRole !== 'admin' && newRole === 'admin');
            const isDemotion = (oldRole === 'admin' && newRole !== 'admin');
            
            if (isPromotion || isDemotion) {
                const actionMsg = isPromotion ? 'Promote "' + data.name + '" to Admin' : 'Demote "' + data.name + '" to Borrower';
                const pin = await showPrivilegePinPrompt(actionMsg, isPromotion ? 'Full system access will be granted.' : 'Admin privileges will be revoked.');
                if (!pin) return; // User cancelled
                data.promotion_pin = pin;
            }
            
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            
            try {
                const res = await fetch('/admin/users/' + userId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (res.ok) {
                    const modalEl = document.getElementById('editUserModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                    if (window.AppDrawer && window.AppDrawer.isOpen()) {
                        window.AppDrawer.close();
                    }
                    showNotify(result.message || 'User updated successfully!', 'success');
                    updateBtn.disabled = false;
                    updateBtn.innerHTML = 'Save Changes';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    let msg = 'Failed to update user.\n';
                    if (result.require_pin) {
                        msg = result.message || 'PIN verification required.';
                        showNotify(msg, 'error');
                    } else if (result.errors) {
                        for (const [f, errs] of Object.entries(result.errors)) { msg += errs[0] + '\n'; }
                        showNotify(msg, 'error');
                    } else {
                        showNotify(result.message || 'Failed to update user.', 'error');
                    }
                    updateBtn.disabled = false;
                    updateBtn.innerHTML = 'Save Changes';
                }
            } catch (e) {
                showNotify('Network error.', 'error');
                updateBtn.disabled = false;
                updateBtn.innerHTML = 'Save Changes';
            }
        });
    }
});

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// UM-2: PASSWORD RESET
let _prUserId = null;
window.resetPassword = function (userId, userName) {
    _prUserId = userId;
    document.getElementById('prUserName').textContent = userName;
    document.getElementById('prStep1').classList.remove('d-none');
    document.getElementById('prStep2').classList.add('d-none');
    document.querySelector('input[name="prMode"][value="generate"]').checked = true;
    prModeChanged();
    document.getElementById('prPassword').value = '';
    document.getElementById('prPasswordConfirm').value = '';
    const err = document.getElementById('prError');
    err.classList.add('d-none'); err.textContent = '';
    const submit = document.getElementById('prSubmitBtn');
    submit.disabled = false;
    submit.textContent = 'Reset Password';
    new bootstrap.Modal(document.getElementById('passwordResetModal')).show();
};

window.prModeChanged = function () {
    const mode = document.querySelector('input[name="prMode"]:checked').value;
    const assignFields = document.getElementById('prAssignFields');
    const requireChange = document.getElementById('prRequireChange');
    assignFields.classList.toggle('d-none', mode !== 'assign');
    if (mode === 'generate') {
        requireChange.checked = true;
        requireChange.disabled = true;
    } else {
        requireChange.disabled = false;
    }
};

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('prSubmitBtn')?.addEventListener('click', async function () {
        if (!_prUserId) return;
        const btn = this;
        const err = document.getElementById('prError');
        err.classList.add('d-none'); err.textContent = '';

        const mode = document.querySelector('input[name="prMode"]:checked').value;
        const payload = { mode: mode };

        if (mode === 'assign') {
            const pw = document.getElementById('prPassword').value;
            const pwc = document.getElementById('prPasswordConfirm').value;
            if (pw.length < 8) { err.textContent = 'Password must be at least 8 characters.'; err.classList.remove('d-none'); return; }
            if (pw !== pwc) { err.textContent = 'Passwords do not match.'; err.classList.remove('d-none'); return; }
            payload.password = pw;
            payload.password_confirmation = pwc;
        }

        if (mode === 'generate') {
            document.getElementById('prRequireChange').checked = true;
        }
        payload.require_password_change = document.getElementById('prRequireChange').checked ? 1 : 0;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Working…';

        try {
            const res = await fetch('/admin/users/' + _prUserId + '/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (res.ok) {
                document.getElementById('prStep1').classList.add('d-none');
                document.getElementById('prStep2').classList.remove('d-none');
                document.getElementById('prCancelBtn').textContent = 'Done';

                if (data.mode === 'generate' && data.temp_password) {
                    document.getElementById('prResultTitle').textContent = 'Temporary password created';
                    document.getElementById('prResultMsg').innerHTML = 'Share this one-time password with <strong>' + escapeHtml(data.user_name) + '</strong>:';
                    document.getElementById('tempPasswordDisplay').textContent = data.temp_password;
                    document.getElementById('prGeneratedWrap').classList.remove('d-none');
                    document.getElementById('prResultNote').textContent = 'They will set a new password (and re-verify) at next sign-in.';
                } else {
                    document.getElementById('prResultTitle').textContent = 'Password assigned';
                    document.getElementById('prResultMsg').innerHTML = 'The new password was saved for <strong>' + escapeHtml(data.user_name) + '</strong>.';
                    document.getElementById('prGeneratedWrap').classList.add('d-none');
                    document.getElementById('prResultNote').textContent = data.requires_password_change
                        ? 'They must change it at first sign-in.'
                        : 'No forced change — they can sign in with it right away.';
                }
                _prUserId = null;
            } else {
                err.textContent = data.message || 'Failed to reset password.';
                err.classList.remove('d-none');
                btn.disabled = false;
                btn.textContent = 'Reset Password';
            }
        } catch (e) {
            err.textContent = 'Network error occurred.';
            err.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = 'Reset Password';
        }
    });
});

function copyTempPassword() {
    const pw = document.getElementById('tempPasswordDisplay');
    if (pw) {
        navigator.clipboard.writeText(pw.textContent).then(() => {
            showNotify('Password copied to clipboard!', 'success');
        }).catch(() => {});
    }
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const bar = document.getElementById('bulkActionBar');
    const count = document.getElementById('bulkCount');
    if (checked.length > 0) {
        bar.classList.remove('d-none');
        bar.classList.add('d-flex');
        count.textContent = checked.length;
    } else {
        bar.classList.add('d-none');
        bar.classList.remove('d-flex');
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkBar();
}

async function bulkAction(action) {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checked).map(cb => parseInt(cb.value));
    if (userIds.length === 0) return;

    // ðŸ”’ PIN required for role changes (both set_admin and set_borrower)
    if (action === 'set_admin' || action === 'set_borrower') {
        const isPromotion = (action === 'set_admin');
        const actionMsg = isPromotion ? 'Bulk promote to Admin (' + userIds.length + ' users)' : 'Bulk demote to Borrower (' + userIds.length + ' users)';
        const actionDesc = isPromotion ? 'Full system access will be granted.' : 'Admin privileges will be revoked.';
        const pin = await showPrivilegePinPrompt(actionMsg, actionDesc);
        if (!pin) return;
        _executeBulkWithPin(action, pin);
        return;
    }
    
    const bar = document.getElementById('bulkActionBar');
    bar.style.opacity = '0.6';
    
    try {
        const res = await fetch('/admin/users/bulk-update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ user_ids: userIds, action: action })
        });
        const data = await res.json();
        if (res.ok) {
            showNotify(data.message || 'Bulk action completed!', 'success');
            clearBulkSelection();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotify(data.message || 'Bulk action failed.', 'error');
            bar.style.opacity = '1';
        }
    } catch (e) {
        showNotify('Network error.', 'error');
        bar.style.opacity = '1';
    }
}

async function _executeBulkWithPin(action, pin) {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checked).map(cb => parseInt(cb.value));
    if (userIds.length === 0) return;
    
    const bar = document.getElementById('bulkActionBar');
    bar.style.opacity = '0.6';
    
    try {
        const res = await fetch('/admin/users/bulk-update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ user_ids: userIds, action: action, promotion_pin: pin })
        });
        const data = await res.json();
        if (res.ok) {
            showNotify(data.message || 'Bulk action completed!', 'success');
            clearBulkSelection();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotify(data.message || 'Bulk action failed.', 'error');
            bar.style.opacity = '1';
        }
    } catch (e) {
        showNotify('Network error.', 'error');
        bar.style.opacity = '1';
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// UM-6: USER DETAIL MODAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// UM-7: ROLE INFO POPOVER
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function showRoleInfo(event) {
    const popover = document.getElementById('roleInfoPopover');
    const rect = event.target.getBoundingClientRect();
    popover.style.top = (rect.bottom + 8) + 'px';
    popover.style.left = Math.max(8, Math.min(rect.left - 100, window.innerWidth - 290)) + 'px';
    popover.classList.remove('d-none');
    
    function closePopover(e) {
        if (!popover.contains(e.target) && !e.target.closest('.role-info-trigger')) {
            popover.classList.add('d-none');
            document.removeEventListener('click', closePopover);
        }
    }
    setTimeout(() => document.addEventListener('click', closePopover), 10);
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ðŸ”’ PRIVILEGE CHANGE PIN CONFIRMATION (Promotion OR Demotion)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let _privilegeResolve = null;
let _privilegePin = '';

function showPrivilegePinPrompt(actionTitle, actionDesc) {
    return new Promise(function(resolve) {
        _privilegeResolve = resolve;
        _privilegePin = '';
        
        document.getElementById('privilegeActionTitle').textContent = actionTitle + '?';
        document.getElementById('privilegeActionDesc').textContent = actionDesc;
        
        // Reset dots
        document.querySelectorAll('.privilege-dot').forEach(function(d) {
            d.classList.remove('filled', 'error');
        });
        document.getElementById('privilegePinValue').value = '';
        document.getElementById('privilegePinError').textContent = '';
        document.getElementById('confirmPrivilegeBtn').disabled = true;
        
        const modal = new bootstrap.Modal(document.getElementById('privilegePinModal'));
        modal.show();
    });
}

function privilegePinPad(digit) {
    if (_privilegePin.length >= 4) return;
    _privilegePin += digit;
    document.getElementById('privilegePinValue').value = _privilegePin;
    
    for (var i = 0; i < 4; i++) {
        var dot = document.getElementById('privDot' + i);
        if (i < _privilegePin.length) {
            dot.classList.add('filled');
            dot.classList.remove('error');
        } else {
            dot.classList.remove('filled', 'error');
        }
    }
    
    document.getElementById('privilegePinError').textContent = '';
    document.getElementById('confirmPrivilegeBtn').disabled = _privilegePin.length !== 4;
}

function privilegePinDel() {
    _privilegePin = _privilegePin.slice(0, -1);
    document.getElementById('privilegePinValue').value = _privilegePin;
    
    for (var i = 0; i < 4; i++) {
        var dot = document.getElementById('privDot' + i);
        if (i < _privilegePin.length) {
            dot.classList.add('filled');
            dot.classList.remove('error');
        } else {
            dot.classList.remove('filled', 'error');
        }
    }
    
    document.getElementById('confirmPrivilegeBtn').disabled = true;
}

function flashPrivilegeError(msg) {
    for (var i = 0; i < 4; i++) {
        document.getElementById('privDot' + i).classList.add('error');
    }
    document.getElementById('privilegePinError').textContent = msg || 'Incorrect PIN. Please try again.';
    setTimeout(function() {
        for (var i = 0; i < 4; i++) {
            document.getElementById('privDot' + i).classList.remove('error', 'filled');
        }
        _privilegePin = '';
        document.getElementById('privilegePinValue').value = '';
        document.getElementById('confirmPrivilegeBtn').disabled = true;
        // Also clear desktop keyboard input if visible
        var kbInput = document.getElementById('privilegeKeyboardInput');
        if (kbInput) kbInput.value = '';
    }, 600);
}

function cancelPrivilegePin() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('privilegePinModal'));
    modal.hide();
    if (_privilegeResolve) {
        _privilegeResolve(null);
        _privilegeResolve = null;
    }
}

// Wire up the "Confirm" button for privilege changes
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmPrivilegeBtn')?.addEventListener('click', function() {
        if (_privilegePin.length === 4 && _privilegeResolve) {
            const pin = _privilegePin;
            const modal = bootstrap.Modal.getInstance(document.getElementById('privilegePinModal'));
            modal.hide();
            _privilegeResolve(pin);
            _privilegeResolve = null;
        }
    });
});

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// âš–ï¸ ACCOUNT LIFECYCLE ACTIONS (approve/reject/cancel)
// Direct account deletion was removed â€” deletion requires a user
// request plus admin approval, then a 60-day grace period.
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
const LIFECYCLE_ACTIONS = {
    approve_activation: {
        title: 'Approve Activation', icon: 'bi-person-check', danger: false,
        desc: 'This self-registered account has verified its email. Approving will activate it and allow sign-in.'
    },
    reject_activation: {
        title: 'Reject Registration', icon: 'bi-person-x', danger: true,
        desc: 'The pending registration will be rejected and the unused account removed.'
    },
    approve_deactivation: {
        title: 'Approve Deactivation', icon: 'bi-pause-circle', danger: false,
        desc: 'The account will be deactivated at the user\'s request. Their transaction history is kept.'
    },
    reject_deactivation: {
        title: 'Reject Deactivation Request', icon: 'bi-check-circle', danger: false,
        desc: 'The request will be rejected and the user will be notified that the account stays active.'
    },
    approve_deletion: {
        title: 'Approve Account Deletion', icon: 'bi-trash3', danger: true,
        desc: 'The account will be deactivated now and permanently purged after a 60-day grace period. All ongoing and closed transactions are preserved.'
    },
    reject_deletion: {
        title: 'Reject Deletion Request', icon: 'bi-check-circle', danger: false,
        desc: 'The deletion request will be rejected and the user will be notified that the account stays active.'
    },
    cancel_deletion: {
        title: 'Cancel Scheduled Deletion', icon: 'bi-arrow-counterclockwise', danger: false,
        desc: 'The scheduled purge will be cancelled and the account reactivated immediately.'
    }
};

function lifecycleAction(userId, action, userName, reason) {
    const cfg = LIFECYCLE_ACTIONS[action];
    if (!cfg) return;

    document.getElementById('lifecycleUserId').value = userId;
    document.getElementById('lifecycleActionName').value = action;
    document.getElementById('lifecycleModalTitle').textContent = cfg.title;
    document.getElementById('lifecycleUserName').textContent = '"' + userName + '"?';
    document.getElementById('lifecycleUserDesc').textContent = cfg.desc;

    const iconWrap = document.getElementById('lifecycleIconWrap');
    iconWrap.innerHTML = '<i class="bi ' + cfg.icon + (cfg.danger ? ' text-danger' : ' text-success') + '" style="font-size: 15px;"></i>';
    iconWrap.style.background = cfg.danger ? 'rgba(220, 53, 69, 0.1)' : 'rgba(25, 135, 84, 0.1)';

    const descWrap = document.getElementById('lifecycleDescWrap');
    descWrap.style.background = cfg.danger ? 'rgba(220, 53, 69, 0.05)' : 'rgba(25, 135, 84, 0.05)';
    descWrap.style.borderColor = cfg.danger ? 'rgba(220, 53, 69, 0.2)' : 'rgba(25, 135, 84, 0.2)';

    const reasonWrap = document.getElementById('lifecycleReasonWrap');
    if (reason && reason.trim()) {
        document.getElementById('lifecycleReasonText').textContent = reason;
        reasonWrap.classList.remove('d-none');
    } else {
        reasonWrap.classList.add('d-none');
    }

    const btn = document.getElementById('confirmLifecycleBtn');
    btn.className = 'btn fw-bold px-4 ' + (cfg.danger ? 'btn-danger' : 'btn-success');
    btn.textContent = cfg.title.startsWith('Approve') || cfg.title.startsWith('Cancel') ? 'Yes, Confirm' : 'Confirm';

    new bootstrap.Modal(document.getElementById('lifecycleModal')).show();
}

async function executeLifecycleAction() {
    const userId = document.getElementById('lifecycleUserId').value;
    const action = document.getElementById('lifecycleActionName').value;
    if (!userId || !action) return;

    const btn = document.getElementById('confirmLifecycleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    try {
        const res = await fetch('/admin/users/' + userId + '/lifecycle-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ action: action })
        });
        const data = await res.json();

        bootstrap.Modal.getInstance(document.getElementById('lifecycleModal'))?.hide();
        showNotify(data.message || (res.ok ? 'Done.' : 'Action failed.'), res.ok ? 'success' : 'error');
        if (res.ok) setTimeout(() => window.location.reload(), 1000);
    } catch (e) {
        showNotify('Network error.', 'error');
    }

    btn.disabled = false;
}

// Wire up the confirm button
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmLifecycleBtn')?.addEventListener('click', executeLifecycleAction);
});

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ðŸ”‘ DESKTOP KEYBOARD INPUT WIRING
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// On desktop, a real input field replaces the numpad buttons.
// This function syncs the keyboard input with the existing PIN logic.
function setupKeyboardPinInput(inputId) {
    var input = document.getElementById(inputId);
    if (!input) return;
    
    var isPrivilege = (inputId === 'privilegeKeyboardInput');
    var confirmBtnId = isPrivilege ? 'confirmPrivilegeBtn' : null;
    var dotsPrefix = isPrivilege ? 'privDot' : null;
    var pinValueId = isPrivilege ? 'privilegePinValue' : null;
    var errorElId = isPrivilege ? 'privilegePinError' : null;
    
    function updatePinState(val) {
        if (!isPrivilege) return;
        _privilegePin = val;
        
        // Update dots
        for (var j = 0; j < 4; j++) {
            var dot = document.getElementById(dotsPrefix + j);
            if (!dot) continue;
            if (j < val.length) {
                dot.classList.add('filled');
                dot.classList.remove('error');
            } else {
                dot.classList.remove('filled', 'error');
            }
        }
        
        document.getElementById(pinValueId).value = val;
        var btn = document.getElementById(confirmBtnId);
        if (btn) btn.disabled = val.length !== 4;
        var errEl = document.getElementById(errorElId);
        if (errEl) errEl.textContent = '';
    }
    
    input.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
        updatePinState(this.value);
    });
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (confirmBtnId) document.getElementById(confirmBtnId)?.click();
        }
        // Let backspace propagate naturally (browser handles it)
    });
    
    input.addEventListener('paste', function(e) {
        var pasted = (e.clipboardData || window.clipboardData).getData('text');
        if (!/^\d+$/.test(pasted)) e.preventDefault();
    });
}

// Wire up both keyboard inputs after the modals are shown (to auto-focus)
document.addEventListener('DOMContentLoaded', function() {
    var privModal = document.getElementById('privilegePinModal');
    if (privModal) {
        privModal.addEventListener('shown.bs.modal', function() {
            var input = document.getElementById('privilegeKeyboardInput');
            if (input && window.getComputedStyle(input).display !== 'none') {
                setTimeout(function() { input.focus(); }, 100);
            }
        });
        setupKeyboardPinInput('privilegeKeyboardInput', privilegePinPad, privilegePinDel, 'confirmPrivilegeBtn');
    }
    
    // Clear keyboard input when the privilege modal is hidden
    var clearInput = function(modalId, inputId, dotPrefix, varName) {
        var el = document.getElementById(modalId);
        if (el) {
            el.addEventListener('hidden.bs.modal', function() {
                var inp = document.getElementById(inputId);
                if (inp) {
                    inp.value = '';
                    inp.blur();
                }
                // Clear dots
                for (var i = 0; i < 4; i++) {
                    var d = document.getElementById(dotPrefix + i);
                    if (d) d.classList.remove('filled', 'error');
                }
                if (varName === '_privilegePin') {
                    _privilegePin = '';
                    document.getElementById('privilegePinValue').value = '';
                    document.getElementById('confirmPrivilegeBtn').disabled = true;
                }
            });
        }
    };
    clearInput('privilegePinModal', 'privilegeKeyboardInput', 'privDot', '_privilegePin');
});

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// UTILITY: escapeHtml
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<script>
// ═══════════════════════════════════════════════════════════════════
// FULL-PAGE ACCOUNT DRAWER
// ═══════════════════════════════════════════════════════════════════
let _drawerUser = null;

function drawerRoleBadge(role) {
    if (role === 'admin') return '<span class="badge bg-icon-purple text-purple px-3 py-2 rounded-pill">Admin</span>';
    if (role === 'custodian') return '<span class="badge bg-icon-green text-green px-3 py-2 rounded-pill">Property Custodian</span>';
    return '<span class="badge bg-icon-blue text-blue px-3 py-2 rounded-pill">Borrower</span>';
}

function drawerStatusBadge(u) {
    if (u.is_scheduled_for_deletion) return '<span class="badge bg-danger text-white px-3 py-2 rounded-pill">Deleting ' + (u.deletion_effective_human || '') + '</span>';
    if (u.deletion_requested_at) return '<span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Deletion Requested</span>';
    if (u.awaiting_activation) return '<span class="badge bg-info text-white px-3 py-2 rounded-pill">Awaiting Approval</span>';
    if (u.deactivation_requested_at) return '<span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Deactivation Requested</span>';
    if (!u.email_verified_at && u.registration_source === 'self_registered') return '<span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Pending OTP</span>';
    if (!u.is_active) return '<span class="badge bg-secondary text-white px-3 py-2 rounded-pill">Deactivated</span>';
    if (!u.email_verified_at) return '<span class="badge bg-icon-yellow text-yellow px-3 py-2 rounded-pill">Pending OTP</span>';
    return '<span class="badge bg-icon-green text-green px-3 py-2 rounded-pill">Active</span>';
}

function jsAttr(text) {
    // Escape for embedding inside a single-quoted JS string in an HTML attribute
    return escapeHtml(String(text || '').replace(/\s+/g, ' ').trim()).replace(/&#0?39;/g, '&#39;');
}

window.openUserDrawer = async function (userId) {
    document.getElementById('userDrawerBackdrop').classList.remove('d-none');
    const drawer = document.getElementById('userDrawer');
    drawer.style.transform = 'translateX(0)';
    document.body.style.overflow = 'hidden';

    ['udUserId','udTeacherId','udRegistered','udVerified','udLastLogin','udLoginCount','udPinStatus','udRegSource','udActiveBorrows','udTxCount'].forEach(function (id) {
        const el = document.getElementById(id); if (el) el.textContent = '…';
    });
    document.getElementById('udName').textContent = 'Loading…';
    document.getElementById('udEmail').textContent = '';
    document.getElementById('udRoleBadge').innerHTML = '';
    document.getElementById('udStatusBadge').innerHTML = '';
    document.getElementById('udActivity').innerHTML = '<span style="color:var(--text-secondary);">Loading…</span>';
    document.getElementById('udLifecycle').innerHTML = '<span style="color:var(--text-secondary);">Loading…</span>';
    const banner = document.getElementById('udBanner');
    banner.classList.add('d-none'); banner.innerHTML = '';

    try {
        const res = await fetch('/admin/users/' + userId, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        const u = await res.json();
        if (!res.ok) throw new Error();
        _drawerUser = u;

        const initial = (u.name || '?').trim().charAt(0).toUpperCase();
        const av = document.getElementById('udAvatar');
        av.textContent = initial;
        if (u.role === 'admin')      { av.style.background = 'rgba(139,92,246,.14)'; av.style.color = '#8b5cf6'; }
        else if (u.role === 'custodian') { av.style.background = 'rgba(25,135,84,.14)'; av.style.color = '#198754'; }
        else                          { av.style.background = 'var(--accent-blue-bg)'; av.style.color = 'var(--accent-blue)'; }

        document.getElementById('udName').textContent = u.name;
        document.getElementById('udEmail').textContent = u.email;
        document.getElementById('udRoleBadge').innerHTML = drawerRoleBadge(u.role);
        document.getElementById('udStatusBadge').innerHTML = drawerStatusBadge(u);

        if (u.is_scheduled_for_deletion) {
            banner.classList.remove('d-none');
            banner.innerHTML = '<i class="bi bi-trash3-fill text-danger me-2"></i><strong>Scheduled for permanent deletion on ' + escapeHtml(u.deletion_effective_human || '') + '</strong> <span class="text-secondary">— transaction history is preserved.</span>';
        } else if (u.deletion_requested_at) {
            banner.classList.remove('d-none');
            banner.innerHTML = '<i class="bi bi-hourglass-split me-2" style="color:#f59e0b;"></i><strong>Deletion request pending review</strong>' + (u.deletion_reason ? ' — “' + escapeHtml(u.deletion_reason) + '”' : '');
        } else if (u.deactivation_requested_at) {
            banner.classList.remove('d-none');
            banner.innerHTML = '<i class="bi bi-hourglass-split me-2" style="color:#f59e0b;"></i><strong>Deactivation request pending review</strong>' + (u.deactivation_reason ? ' — “' + escapeHtml(u.deactivation_reason) + '”' : '');
        } else if (u.awaiting_activation) {
            banner.classList.remove('d-none');
            banner.innerHTML = '<i class="bi bi-person-plus me-2" style="color:#0dcaf0;"></i><strong>Self-registered account awaiting activation approval.</strong>';
        }

        document.getElementById('udUserId').textContent = '#' + u.id;
        document.getElementById('udTeacherId').textContent = u.teacher_id || 'N/A';
        document.getElementById('udRegistered').textContent = u.created_at ? new Date(u.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
        document.getElementById('udVerified').textContent = u.email_verified_at ? 'Yes' : 'No';
        document.getElementById('udLastLogin').textContent = u.last_login_at ? new Date(u.last_login_at).toLocaleString() : 'Never';
        document.getElementById('udLoginCount').textContent = String(u.login_count || 0);
        document.getElementById('udPinStatus').innerHTML = u.pin_setup_completed ? '<span style="color:#198754;">Set up</span>' : '<span style="color:var(--text-secondary);">Not set</span>';
        const pinBtn = document.getElementById('udResetPinBtn');
        const pinHint = document.getElementById('udPinHint');
        if (u.pin_setup_completed) {
            if (pinBtn) { pinBtn.disabled = false; pinBtn.style.opacity = ''; pinBtn.removeAttribute('title'); }
            if (pinHint) pinHint.textContent = 'Clear their PIN if it was forgotten — they create a new one at next sign-in.';
        } else {
            if (pinBtn) { pinBtn.disabled = true; pinBtn.style.opacity = '.5'; pinBtn.title = 'This account has no security PIN yet.'; }
            if (pinHint) pinHint.textContent = 'This account has no security PIN yet.';
        }
        document.getElementById('udRegSource').textContent = u.registration_source === 'self_registered' ? 'Self-registered' : 'Created by admin';
        document.getElementById('udActiveBorrows').textContent = String(u.active_borrows || 0);
        document.getElementById('udTxCount').textContent = String(u.total_transactions || 0);

        const act = document.getElementById('udActivity');
        if (u.recent_activity && u.recent_activity.length > 0) {
            act.innerHTML = u.recent_activity.map(function (a) {
                const cls = (a.status === 'approved' || a.status === 'returned') ? 'bg-success'
                    : ((a.status === 'rejected' || a.status === 'cancelled') ? 'bg-danger' : 'bg-warning text-dark');
                return '<div class="d-flex align-items-center gap-2 py-2" style="border-bottom:1px solid var(--border-color);">'
                    + '<span class="badge ' + cls + '" style="font-size:9px; text-transform:uppercase;">' + escapeHtml(a.status) + '</span>'
                    + '<span style="color:var(--text-primary);">' + escapeHtml(a.item_name) + '</span>'
                    + '<span class="ms-auto text-secondary">' + escapeHtml(a.created_at || '') + '</span></div>';
            }).join('');
        } else {
            act.innerHTML = '<span style="color:var(--text-secondary);">No recent activity.</span>';
        }

        document.getElementById('udNameInput').value = u.name;
        document.getElementById('udEmailInput').value = u.email;
        document.getElementById('udTeacherInput').value = u.teacher_id || '';
        document.getElementById('udRoleSelect').value = u.role;
        document.getElementById('udActiveSwitch').checked = !!u.is_active;
        document.getElementById('udPrivilegePin').value = '';
        document.getElementById('udPinWrap').classList.add('d-none');
        const err = document.getElementById('udSaveError');
        err.classList.add('d-none'); err.textContent = '';

        const nm = jsAttr(u.name);
        const dr = jsAttr(u.deactivation_reason);
        const dl = jsAttr(u.deletion_reason);
        const bOk = 'class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1" style="border-radius:8px;"';
        const bWarn = 'class="btn btn-sm btn-warning fw-semibold d-inline-flex align-items-center gap-1" style="border-radius:8px;"';
        const bDgr = 'class="btn btn-sm btn-danger fw-semibold d-inline-flex align-items-center gap-1" style="border-radius:8px;"';
        const bRej = 'class="btn btn-sm btn-outline-danger fw-semibold d-inline-flex align-items-center gap-1" style="border-radius:8px;"';

        const lc = document.getElementById('udLifecycle');
        lc.style.color = 'var(--text-primary)';
        if (u.awaiting_activation) {
            lc.innerHTML = '<p class="mb-2" style="color:var(--text-secondary);">Self-registered account verified its email and needs approval before it can sign in.</p>'
                + '<button type="button" ' + bOk + " onclick=\"lifecycleAction(" + u.id + ", 'approve_activation', '" + nm + "')\"><i class='bi bi-person-check'></i> Approve activation</button> "
                + "<button type='button' " + bRej + " onclick=\"lifecycleAction(" + u.id + ", 'reject_activation', '" + nm + "')\"><i class='bi bi-person-x'></i> Reject registration</button>";
        } else if (u.deactivation_requested_at) {
            lc.innerHTML = '<p class="mb-1"><strong>Deactivation requested</strong>' + (u.deactivation_requested_human ? ' · ' + escapeHtml(u.deactivation_requested_human) : '') + '</p>'
                + (u.deactivation_reason ? '<p class="mb-2" style="color:var(--text-secondary);">Reason: “' + escapeHtml(u.deactivation_reason) + '”</p>' : '')
                + "<button type='button' " + bWarn + " onclick=\"lifecycleAction(" + u.id + ", 'approve_deactivation', '" + nm + "', '" + dr + "')\"><i class='bi bi-pause-circle'></i> Approve deactivation</button> "
                + "<button type='button' " + bRej + " onclick=\"lifecycleAction(" + u.id + ", 'reject_deactivation', '" + nm + "')\"><i class='bi bi-x-lg'></i> Reject request</button>";
        } else if (u.deletion_requested_at) {
            lc.innerHTML = '<p class="mb-1"><strong>Permanent deletion requested</strong>' + (u.deletion_requested_human ? ' · ' + escapeHtml(u.deletion_requested_human) : '') + '</p>'
                + (u.deletion_reason ? '<p class="mb-2" style="color:var(--text-secondary);">Reason: “' + escapeHtml(u.deletion_reason) + '”</p>' : '')
                + "<button type='button' " + bDgr + " onclick=\"lifecycleAction(" + u.id + ", 'approve_deletion', '" + nm + "', '" + dl + "')\"><i class='bi bi-trash3'></i> Approve deletion (60-day buffer)</button> "
                + "<button type='button' " + bRej + " onclick=\"lifecycleAction(" + u.id + ", 'reject_deletion', '" + nm + "')\"><i class='bi bi-x-lg'></i> Reject request</button>";
        } else if (u.is_scheduled_for_deletion) {
            lc.innerHTML = '<p class="mb-2" style="color:var(--text-secondary);">Inside the grace period. Permanent removal on <strong style="color:var(--text-primary);">' + escapeHtml(u.deletion_effective_human || '') + '</strong>. Transaction history is preserved.</p>'
                + "<button type='button' " + bOk + " onclick=\"lifecycleAction(" + u.id + ", 'cancel_deletion', '" + nm + "')\"><i class='bi bi-arrow-counterclockwise'></i> Cancel deletion & restore account</button>";
        } else {
            lc.innerHTML = '<span style="color:var(--text-secondary);">No pending requests. Users request deactivation or deletion from their own Account Settings; you approve or reject it here or from the list.</span>';
        }
    } catch (e) {
        showToast('Error', 'Failed to load account details.', 'error');
        closeUserDrawer();
    }
};

window.closeUserDrawer = function () {
    document.getElementById('userDrawerBackdrop').classList.add('d-none');
    document.getElementById('userDrawer').style.transform = 'translateX(102%)';
    document.body.style.overflow = '';
    _drawerUser = null;
};

// ── Security tools wiring ──────────────────────────────────────────
window.drawerResetPin = async function () {
    if (!_drawerUser) return;
    const pin = await showPrivilegePinPrompt(
        "Reset " + _drawerUser.name + "'s PIN?",
        'Their current PIN will be cleared. They will create a new one at next sign-in.'
    );
    if (!pin) return;

    try {
        const res = await fetch('/admin/users/' + _drawerUser.id + '/reset-pin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ admin_pin: pin })
        });
        const data = await res.json();
        if (res.ok) {
            showToast('Success', data.message, 'success');
            const id = _drawerUser.id;
            closeUserDrawer();
            setTimeout(function () { openUserDrawer(id); }, 500);
        } else {
            showToast('Error', data.message || 'PIN reset failed.', 'error');
        }
    } catch (e) {
        showToast('Error', 'Network error.', 'error');
    }
};

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') window.closeUserDrawer();
});

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('udSaveBtn')?.addEventListener('click', async function () {
        if (!_drawerUser) return;
        const btn = this;
        const err = document.getElementById('udSaveError');
        err.classList.add('d-none'); err.textContent = '';

        const payload = {
            name: document.getElementById('udNameInput').value.trim(),
            email: document.getElementById('udEmailInput').value.trim(),
            teacher_id: document.getElementById('udTeacherInput').value.trim(),
            role: document.getElementById('udRoleSelect').value,
            is_active: document.getElementById('udActiveSwitch').checked ? 1 : 0,
            _method: 'PUT'
        };
        const pin = document.getElementById('udPrivilegePin').value.trim();
        if (pin) payload.promotion_pin = pin;

        if (!payload.name || !payload.email) {
            err.textContent = 'Name and email are required.';
            err.classList.remove('d-none');
            return;
        }

        const involvesAdmin = (payload.role === 'admin') !== (_drawerUser.role === 'admin');
        if (involvesAdmin && !pin) {
            document.getElementById('udPinWrap').classList.remove('d-none');
            err.textContent = 'Privilege changes involving the Admin role require your PIN.';
            err.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

        try {
            const res = await fetch('/admin/users/' + _drawerUser.id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok) {
                showToast('Success', data.message || 'Account updated.', 'success');
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                err.textContent = data.message || 'Update failed.';
                err.classList.remove('d-none');
                if (data.require_pin) document.getElementById('udPinWrap').classList.remove('d-none');
                btn.disabled = false;
                btn.textContent = 'Save changes';
            }
        } catch (e) {
            err.textContent = 'Network error.';
            err.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = 'Save changes';
        }
    });

    document.getElementById('udResetPwBtn')?.addEventListener('click', function () {
        if (_drawerUser) resetPassword(_drawerUser.id, _drawerUser.name);
    });

    document.getElementById('udResetPwBtn2')?.addEventListener('click', function () {
        if (_drawerUser) resetPassword(_drawerUser.id, _drawerUser.name);
    });

    document.getElementById('udResetPinBtn')?.addEventListener('click', function () {
        window.drawerResetPin();
    });
});
</script>

<script>
// ═══════════════════════════════════════════════════════════════════
// LIVE LIST — search & filters without full-page reloads
// ═══════════════════════════════════════════════════════════════════
let _usersSearchTimer = null;
let _usersAbort = null;

function usersUrlWith(overrides) {
    const params = new URLSearchParams(window.location.search);
    Object.entries(overrides).forEach(([k, v]) => {
        if (v === null || v === undefined || v === '') params.delete(k); else params.set(k, v);
    });
    params.delete('page');
    const qs = params.toString();
    return window.location.pathname + (qs ? '?' + qs : '');
}

async function ajaxLoadUsers(url, push) {
    if (typeof push === 'undefined') push = true;
    if (_usersAbort) _usersAbort.abort();
    _usersAbort = new AbortController();

    const tbody = document.getElementById('usersTbody');
    tbody.style.opacity = '0.45';

    try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(url + sep + '_ajax=1', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            signal: _usersAbort.signal
        });
        if (!res.ok) throw new Error('bad status');
        const data = await res.json();

        tbody.innerHTML = data.rows;
        updateBulkBar();

        const foot = document.getElementById('usersFooter');
        if (foot) foot.classList.toggle('d-none', !data.has_pages);
        const rangeEl = document.getElementById('usersRangeText');
        if (rangeEl) rangeEl.innerHTML = (data.first ?? 0) + '&ndash;' + (data.last ?? 0) + ' of <strong style="color: var(--text-primary);">' + data.total + '</strong>';
        const pageEl = document.getElementById('usersPageText');
        if (pageEl) pageEl.textContent = 'Page ' + data.page + ' of ' + data.pages;

        const prev = document.getElementById('usersPrevLink');
        if (prev) {
            const prevUrl = new URL(url, window.location.origin);
            prevUrl.searchParams.set('page', Math.max(1, data.page - 1));
            prev.href = data.page > 1 ? prevUrl.pathname + '?' + prevUrl.searchParams.toString() : '#';
            prev.classList.toggle('disabled', !(data.page > 1));
            prev.style.pointerEvents = data.page > 1 ? 'auto' : 'none';
            prev.style.color = data.page > 1 ? 'var(--text-secondary)' : 'var(--border-color)';
        }
        const next = document.getElementById('usersNextLink');
        if (next) {
            const nextUrl = new URL(url, window.location.origin);
            nextUrl.searchParams.set('page', data.page + 1);
            const hasMore = data.has_pages && data.page < data.pages;
            next.href = hasMore ? nextUrl.pathname + '?' + nextUrl.searchParams.toString() : '#';
            next.classList.toggle('disabled', !hasMore);
            next.style.pointerEvents = hasMore ? 'auto' : 'none';
            next.style.color = hasMore ? 'var(--text-secondary)' : 'var(--border-color)';
        }

        if (data.lifecycleCounts) {
            document.querySelectorAll('[data-lc-count]').forEach(function (el) {
                el.textContent = data.lifecycleCounts[el.getAttribute('data-lc-count')] ?? 0;
            });
        }

        attachRowNavigation(tbody);
        if (push) history.pushState({}, '', url);
    } catch (err) {
        if (!err || err.name !== 'AbortError') window.location.href = url; // graceful fallback
    } finally {
        tbody.style.opacity = '';
    }
}

function attachRowNavigation(scope) {
    (scope || document).querySelectorAll('#usersTbody tr[data-user-id]').forEach(function (tr) {
        if (tr.dataset.navBound) return;
        tr.dataset.navBound = '1';
        tr.style.cursor = 'pointer';
        tr.addEventListener('click', function (e) {
            if (e.target.closest('input, button, a, select, .dropdown-menu')) return;
            openUserDrawer(tr.getAttribute('data-user-id'));
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('userSearchInput');

    // Live search — updates only the table, never reloads the page
    input?.addEventListener('input', function () {
        clearTimeout(_usersSearchTimer);
        _usersSearchTimer = setTimeout(function () {
            ajaxLoadUsers(usersUrlWith({ search: input.value }));
        }, 350);
    });
    input?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(_usersSearchTimer);
            ajaxLoadUsers(usersUrlWith({ search: input.value }));
        }
        if (e.key === 'Escape') {
            input.value = '';
            ajaxLoadUsers(usersUrlWith({ search: '' }));
        }
    });

    document.getElementById('userToolbarForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        ajaxLoadUsers(usersUrlWith({ search: input ? input.value : '' }));
    });

    window.applyToolbarFilter = function (key, value) {
        const o = {}; o[key] = value;
        ajaxLoadUsers(usersUrlWith(o));
    };

    document.addEventListener('click', function (e) {
        const nav = e.target.closest('[data-users-nav]');
        if (!nav) return;
        e.preventDefault();
        if (nav.hasAttribute('data-remove-param')) {
            const o = {}; o[nav.getAttribute('data-remove-param')] = '';
            ajaxLoadUsers(usersUrlWith(o));
        } else if (nav.hasAttribute('data-clear-filters')) {
            ajaxLoadUsers(usersUrlWith({ search: '', role: '', status: '' }));
        } else if (nav.hasAttribute('data-set-status')) {
            ajaxLoadUsers(usersUrlWith({ status: nav.getAttribute('data-set-status') }));
        } else if (nav.tagName === 'A') {
            ajaxLoadUsers(nav.getAttribute('href'));
        }
    });

    document.getElementById('perPageSelect')?.addEventListener('change', function () {
        ajaxLoadUsers(usersUrlWith({ per_page: this.value }));
    });

    window.addEventListener('popstate', function () {
        if (document.getElementById('usersTbody')) ajaxLoadUsers(window.location.href, false);
    });

    attachRowNavigation(document);
});
</script>

@endsection
