@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="welcome-header align-items-center mb-4">
        <div>
            <p class="text-muted m-0 fs-6 fw-bold">
                {{ \Carbon\Carbon::now()->format('H') < 12 ? 'Good Morning' : (\Carbon\Carbon::now()->format('H') < 18 ? 'Good Afternoon' : 'Good Evening') }},
            </p>
            <h1 class="fw-bold m-0" style="font-size: 28px;">{{ explode(' ', Auth::user()->name ?? 'User')[0] }}</h1>
        </div>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-md-6">
            <a href="{{ route('borrower.requests') }}" class="btn-primary-action w-100 justify-content-center py-3 fs-6 text-decoration-none" style="border-radius: 12px; background: var(--accent-color);">
                <i class="bi bi-box-seam fs-5"></i> Borrow Equipment
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('borrower.requests') }}?type=issuance" class="btn btn-light w-100 justify-content-center py-3 fs-6 d-flex align-items-center gap-2 text-decoration-none" style="border-radius: 12px; border: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary); background: var(--bg-surface);">
                <i class="bi bi-pencil-square fs-5 text-muted"></i> Request Supplies
            </a>
        </div>
    </div>

    <h2 class="fw-bold mb-3" style="font-size: 18px;">Overview</h2>
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="bi bi-box-seam"></i></div>
            <div class="stat-info">
                <span class="stat-label">Active Items</span>
                <span class="stat-value">{{ $activeCount ?? 0 }}</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-yellow"><i class="bi bi-clock-history"></i></div>
            <div class="stat-info">
                <span class="stat-label">Pending Approval</span>
                <span class="stat-value">{{ $pendingCount ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="activity-card">
        <div class="activity-header mb-3">
            <h2 class="fw-bold" style="font-size: 18px;">Recent Activity</h2>
            <a href="{{ route('borrower.history') }}" class="text-muted fw-bold text-decoration-none" style="font-size: 14px;">View all <i class="bi bi-chevron-right"></i></a>
        </div>
        
        <div class="activity-list">
            @forelse($recentActivity as $activity)
                <div class="activity-row">
                    <div class="item-info">
                        <div class="item-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="item-details">
                            <span class="item-name">{{ $activity->item->name ?? 'Unknown Item' }}</span>
                            <span class="item-meta">Requested • {{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    @if($activity->status === 'pending')
                        <span class="status-badge badge-pending">Pending</span>
                    @elseif($activity->status === 'approved' || $activity->status === 'active')
                        <span class="status-badge badge-approved">Active</span>
                    @elseif($activity->status === 'returned')
                        <span class="status-badge" style="background: rgba(107, 114, 128, 0.1); color: #6b7280;">Returned</span>
                    @else
                        <span class="status-badge" style="background: rgba(107, 114, 128, 0.1); color: #6b7280;">{{ ucfirst($activity->status) }}</span>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                    <p class="m-0">You have no recent activity.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection