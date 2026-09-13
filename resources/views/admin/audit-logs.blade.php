@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);"><i class="bi bi-journal-text me-2"></i>Audit Logs</h1>
            <p class="form-label text-secondary mb-0">Review detailed system activity, changes, and administrative actions.</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary fw-semibold px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 8px;" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="panel-card p-3 mb-4 shadow-sm" style="border-radius: 12px;">
        <form action="{{ route('admin.audit-logs') }}" method="GET" class="row g-3 m-0">
            <div class="col-12 col-md-4 p-0 pe-md-2 position-relative">
                <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); z-index: 5;"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control w-100" placeholder="Search actions, descriptions, or user names..." style="padding-left: 38px; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
            </div>
            <div class="col-6 col-md-2 p-0 ps-md-2">
                <select name="action" class="form-select w-100" style="border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer;" onchange="this.form.submit()">
                    <option value="all" {{ request('action') === 'all' ? 'selected' : '' }}>All Actions</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ request('action') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 p-0 ps-md-2">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control w-100" placeholder="From date" style="border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
            </div>
            <div class="col-6 col-md-2 p-0 ps-md-2">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control w-100" placeholder="To date" style="border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
            </div>
            <div class="col-6 col-md-2 p-0 ps-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-semibold px-3 flex-grow-1" style="border-radius: 8px;"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->anyFilled(['search', 'action', 'from', 'to']))
                    <a href="{{ route('admin.audit-logs') }}" class="btn btn-outline-secondary fw-semibold px-3" style="border-radius: 8px;"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- Stats Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="panel-card p-3 text-center" style="border-radius: 10px;">
                <div class="fw-bold fs-4" style="color: var(--text-primary);">{{ $logs->total() }}</div>
                <div class="small text-secondary">Total Entries</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="panel-card p-3 text-center" style="border-radius: 10px;">
                <div class="fw-bold fs-4" style="color: var(--accent-blue);">{{ $actionTypes->count() }}</div>
                <div class="small text-secondary">Action Types</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="panel-card p-3 text-center" style="border-radius: 10px;">
                <div class="fw-bold fs-4" style="color: var(--accent-green);">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</div>
                <div class="small text-secondary">Showing</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="panel-card p-3 text-center d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px;">
                <select name="per_page" class="form-select form-select-sm" style="width: auto; border-radius: 6px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer;" onchange="var p=new URLSearchParams(location.search);p.set('per_page',this.value);location.href='{{ route('admin.audit-logs') }}?'+p.toString()">
                    <option value="10" {{ (request('per_page', 25) == 10) ? 'selected' : '' }}>10</option>
                    <option value="25" {{ (request('per_page', 25) == 25) ? 'selected' : '' }}>25</option>
                    <option value="50" {{ (request('per_page', 25) == 50) ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (request('per_page', 25) == 100) ? 'selected' : '' }}>100</option>
                </select>
                <span class="small text-secondary">per page</span>
            </div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="panel-card p-0 overflow-hidden shadow-sm" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="admin-table mb-0 align-middle">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3" style="width: 60px;">ID</th>
                        <th class="py-3" style="width: 160px;">Timestamp</th>
                        <th class="py-3" style="width: 180px;">User</th>
                        <th class="py-3" style="width: 130px;">Action</th>
                        <th class="py-3">Description</th>
                        <th class="py-3 text-center" style="width: 100px;">Target</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: 0.15s;">
                        <td class="ps-4 py-3">
                            <span class="font-monospace small text-secondary">#{{ $log->id }}</span>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold" style="color: var(--text-primary); font-size: 13px;">
                                {{ $log->created_at->format('M d, Y') }}
                            </div>
                            <div class="small text-secondary" style="font-size: 11px;">
                                <i class="bi bi-clock me-1"></i>{{ $log->created_at->format('h:i A') }}
                            </div>
                        </td>
                        <td class="py-3">
                            @if($log->user)
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--bg-main); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-person" style="font-size: 12px; color: var(--text-secondary);"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="color: var(--text-primary); font-size: 13px;">
                                            {{ $log->user->name }}
                                        </div>
                                        <div class="small text-secondary" style="font-size: 10px;">
                                            {{ $log->user->role === 'admin' ? 'Administrator' : 'Borrower' }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">System / Unknown</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @php
                                $actionColors = [
                                    'Created' => 'success',
                                    'Updated' => 'info',
                                    'Deleted' => 'danger',
                                    'Approved' => 'success',
                                    'Rejected' => 'danger',
                                    'Cancelled' => 'warning',
                                    'Failed' => 'danger',
                                    'Password' => 'warning',
                                    'Settings' => 'secondary',
                                    'Restored' => 'info',
                                    'Promoted' => 'success',
                                    'Demoted' => 'warning',
                                    'Promotion' => 'warning',
                                    'Demotion' => 'warning',
                                    'Verified' => 'success',
                                    'Locked' => 'danger',
                                    'Borrow' => 'primary',
                                    'Return' => 'info',
                                    'Initiated' => 'primary',
                                    'PIN' => 'secondary',
                                ];
                                $badgeColor = 'secondary';
                                foreach ($actionColors as $key => $color) {
                                    if (str_contains($log->action, $key)) {
                                        $badgeColor = $color;
                                        break;
                                    }
                                }
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} px-3 py-2 rounded-pill fw-semibold" style="font-size: 11px; letter-spacing: 0.3px;">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3">
                            <div style="color: var(--text-primary); font-size: 13px; line-height: 1.5; max-width: 400px;">
                                {{ $log->description }}
                            </div>
                        </td>
                        <td class="py-3 text-center">
                            @if($log->table_name && $log->record_id)
                                <span class="small text-secondary font-monospace" style="font-size: 11px;">
                                    {{ $log->table_name }}<br>
                                    <span class="fw-semibold">#{{ $log->record_id }}</span>
                                </span>
                            @elseif($log->table_name)
                                <span class="small text-secondary" style="font-size: 11px;">{{ $log->table_name }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-journal-text d-block mb-3" style="font-size: 3rem; opacity: 0.2; color: var(--text-primary);"></i>
                            <h6 class="fw-bold mb-1" style="color: var(--text-primary);">No Audit Logs Found</h6>
                            <p class="text-secondary small mb-0">
                                @if(request()->anyFilled(['search', 'action', 'from', 'to']))
                                    No logs match your current filters.
                                @else
                                    The system hasn't recorded any activity yet.
                                @endif
                            </p>
                            @if(request()->anyFilled(['search', 'action', 'from', 'to']))
                                <a href="{{ route('admin.audit-logs') }}" class="btn btn-sm btn-outline-secondary fw-semibold mt-3" style="border-radius: 8px;">
                                    <i class="bi bi-x-lg me-1"></i> Clear Filters
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color: var(--border-color) !important;">
                <div class="small text-secondary">
                    Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
                </div>
                <div>
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* Dark mode table hover */
[data-theme="dark"] .admin-table tbody tr:hover {
    background: rgba(255,255,255,0.02) !important;
}
.admin-table tbody tr:hover {
    background: rgba(0,0,0,0.015) !important;
}
/* Badge color overrides for dark mode — now palette-aware via CSS vars */
[data-theme="dark"] .bg-success { background: var(--accent-green) !important; }
[data-theme="dark"] .bg-danger  { background: var(--accent-red) !important; }
[data-theme="dark"] .bg-warning { background: var(--accent-yellow) !important; color: #fff !important; }
[data-theme="dark"] .bg-info    { background: var(--accent-blue) !important; }
[data-theme="dark"] .bg-secondary { background: var(--text-secondary) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit on date change
    document.querySelectorAll('input[type="date"]').forEach(function(input) {
        input.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});
</script>
@endsection
