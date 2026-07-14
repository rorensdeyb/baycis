@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1 class="fw-bold" style="color: var(--text-primary);">Welcome back, {{ auth()->user()?->name ?? 'Admin' }}</h1>
        <p class="text-secondary">Real-time status of your inventory and recent activities.</p>
    </div>

    <div class="stats-grid mb-4">
        <div class="stat-card shadow-sm">
            <div class="stat-info">
                <span class="stat-label">Total Assets</span>
                <span class="stat-value">{{ number_format($totalItems ?? 0) }}</span>
            </div>
            <div class="stat-icon bg-icon-blue"><i class="bi bi-box-seam text-blue"></i></div>
        </div>
        <div class="stat-card shadow-sm">
            <div class="stat-info">
                <span class="stat-label">Available Items</span>
                <span class="stat-value">{{ number_format($availableItems ?? 0) }}</span>
            </div>
            <div class="stat-icon bg-icon-green"><i class="bi bi-check-circle text-green"></i></div>
        </div>
        <div class="stat-card shadow-sm">
            <div class="stat-info">
                <span class="stat-label">Borrowed Items</span>
                <span class="stat-value">{{ number_format($activeBorrows ?? 0) }}</span> 
            </div>
            <div class="stat-icon bg-icon-yellow"><i class="bi bi-box-arrow-right text-yellow"></i></div>
        </div>
        <div class="stat-card shadow-sm" style="{{ ($pendingReqs ?? 0) > 0 ? 'border: 1px solid var(--accent-red); background: var(--accent-red-bg);' : '' }}">
            <div class="stat-info">
                <span class="stat-label">Pending Requests</span>
                <span class="stat-value">{{ number_format($pendingReqs ?? 0) }}</span>
            </div>
            <div class="stat-icon bg-icon-red"><i class="bi bi-exclamation-triangle text-red"></i></div>
        </div>
    </div>

    <div class="panels-grid">
        <div class="panel-card activity-panel shadow-sm">
            <div class="panel-header mb-3 border-bottom pb-3" style="border-color: var(--border-color) !important;">
                <h2 class="fs-5 m-0 fw-bold">Recent Activity</h2>
                <a href="{{ route('items.history') }}" class="view-all text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-list">
                @forelse($recentActivity ?? [] as $activity)
                    <div class="activity-item d-flex gap-3 mb-3">
                        <div class="dot {{ in_array($activity->status ?? '', ['approved', 'returned']) ? 'dot-green' : (in_array($activity->status ?? '', ['rejected', 'cancelled']) ? 'dot-red' : 'dot-yellow') }} mt-1"></div>
                        
                        <div class="activity-content flex-grow-1">
                            <p class="activity-text fw-bold m-0" style="color: var(--text-primary); font-size: 14px;">
                                {{ ucfirst($activity->status ?? 'Unknown') }} Request: <span class="fw-normal">{{ $activity->item?->name ?? 'Asset' }}</span>
                            </p>
                            <p class="activity-sub m-0 text-secondary" style="font-size: 12px;">by {{ $activity->user?->name ?? 'Unknown User' }}</p>
                        </div>
                        <span class="activity-time text-secondary" style="font-size: 11px;">{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Just now' }}</span>
                    </div>
                @empty
                    <div class="text-center text-muted p-4">
                        <i class="bi bi-inbox fs-2 mb-2 d-block text-secondary"></i>
                        <small>No recent activity logs found.</small>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="panel-card actions-panel shadow-sm">
            <div class="panel-header mb-3 border-bottom pb-3" style="border-color: var(--border-color) !important;">
                <h2 class="fs-5 m-0 fw-bold">Quick Actions</h2>
            </div>
            <div class="actions-list d-flex flex-column gap-2">
                <a href="{{ route('items.index') }}" class="text-decoration-none">
                    <button class="quick-action-btn w-100 d-flex align-items-center gap-3 p-3 bg-transparent" style="border: 1px solid var(--border-color); border-radius: 12px;">
                        <div class="qa-icon bg-icon-blue rounded p-2"><i class="bi bi-box text-blue"></i></div>
                        <span class="qa-text fw-bold text-start flex-grow-1" style="color: var(--text-primary);">Manage Inventory</span>
                        <i class="bi bi-arrow-up-right qa-arrow text-secondary"></i>
                    </button>
                </a>
                <a href="{{ route('admin.requests') }}" class="text-decoration-none">
                    <button class="quick-action-btn w-100 d-flex align-items-center gap-3 p-3 bg-transparent" style="border: 1px solid var(--border-color); border-radius: 12px;">
                        <div class="qa-icon bg-icon-yellow rounded p-2"><i class="bi bi-ui-checks text-yellow"></i></div>
                        <span class="qa-text fw-bold text-start flex-grow-1" style="color: var(--text-primary);">Review Requests</span>
                        <i class="bi bi-arrow-up-right qa-arrow text-secondary"></i>
                    </button>
                </a>
                <a href="#" class="text-decoration-none">
                    <button class="quick-action-btn w-100 d-flex align-items-center gap-3 p-3 bg-transparent" style="border: 1px solid var(--border-color); border-radius: 12px;">
                        <div class="qa-icon bg-icon-purple rounded p-2"><i class="bi bi-graph-up text-purple"></i></div>
                        <span class="qa-text fw-bold text-start flex-grow-1" style="color: var(--text-primary);">View Reports</span>
                        <i class="bi bi-arrow-up-right qa-arrow text-secondary"></i>
                    </button>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection