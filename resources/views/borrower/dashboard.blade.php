@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    {{-- Welcome Header --}}
    @php
        $hour = \Carbon\Carbon::now()->format('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $firstName = explode(' ', Auth::user()->name ?? 'User')[0];
    @endphp
    <div class="welcome-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <h1 class="fw-bold m-0 d-flex flex-wrap align-items-baseline gap-1" style="font-size: clamp(20px, 3.5vw, 24px); color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.25;">
                <span class="text-secondary fw-semibold">{{ $greeting }},</span>
                <span>{{ $firstName }}</span>
                <span style="font-size: 0.9em; display: inline-block;">&#128075;</span>
            </h1>
            <p class="text-secondary m-0 mt-1" style="font-size: 13px;">Welcome to your equipment &amp; supplies portal.</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-1 mt-sm-0 flex-wrap">
            <span class="badge" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-secondary); font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 20px;">
                <i class="bi bi-calendar3 me-1 text-primary"></i> {{ \Carbon\Carbon::now()->format('D, M d, Y') }}
            </span>
            <span class="badge" style="background: var(--accent-blue-bg, rgba(59,130,246,0.1)); color: var(--accent-blue); font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 20px;">
                Borrower
            </span>
        </div>
    </div>

    {{-- Quick Action Launchers --}}
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6">
            <a href="{{ route('borrower.requests') }}" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 py-sm-3 text-decoration-none shadow-sm fw-bold" 
               style="border-radius: 12px; background: var(--accent-color); color: var(--btn-text, #ffffff); font-size: 14px; min-height: 46px;">
                <i class="bi bi-box-seam fs-5"></i>
                <span>Borrow Equipment</span>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('borrower.issuance') }}#request-consumable" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 py-sm-3 text-decoration-none shadow-sm fw-bold" 
               style="border-radius: 12px; border: 1px solid var(--border-color); color: var(--text-primary); background: var(--bg-surface); font-size: 14px; min-height: 46px;">
                <i class="bi bi-box2-heart fs-5 text-secondary"></i>
                <span>Request Supplies</span>
            </a>
        </div>
    </div>

    {{-- Urgent Attention Notice: Pending Supply Receipts --}}
    @if($pendingConfirmationsCount > 0)
    <div class="alert mb-4 p-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 shadow-sm" 
         style="background: var(--accent-yellow-bg, rgba(245, 158, 11, 0.1)); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 14px;">
        <div class="d-flex align-items-center gap-3 min-w-0" style="min-width: 0; flex: 1 1 auto;">
            <div style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-bell-fill" style="color: var(--accent-yellow, #d97706); font-size: 18px;"></i>
            </div>
            <div class="min-w-0" style="min-width: 0; flex: 1 1 auto;">
                <span class="fw-bold d-block text-truncate" style="color: var(--text-primary); font-size: 14px;">Supplies Awaiting Confirmation</span>
                <span class="text-secondary small" style="font-size: 12.5px;">You have <strong>{{ $pendingConfirmationsCount }}</strong> consumable supply item(s) issued by the custodian ready for receipt confirmation.</span>
            </div>
        </div>
        <a href="{{ route('borrower.issuance') }}" class="btn btn-sm fw-bold px-3 py-2 flex-shrink-0" 
           style="background: var(--accent-color); color: var(--btn-text, #ffffff); border-radius: 9px; font-size: 12.5px; text-decoration: none;">
            Confirm Receipt &rarr;
        </a>
    </div>
    @endif

    {{-- Urgent Attention Notice: Pending Returns --}}
    @if(($returnPendingCount ?? 0) > 0)
    <div class="activity-card mb-4 p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3" 
         style="background: var(--accent-blue-bg); border: 1px solid var(--line-strong); border-radius: 14px;">
        <div class="d-flex align-items-center gap-3 min-w-0" style="min-width: 0; flex: 1 1 auto;">
            <div style="width: 40px; height: 40px; background: var(--accent-blue-bg); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-arrow-return-left" style="color: var(--accent-color, var(--navy-800)); font-size: 18px;"></i>
            </div>
            <div class="min-w-0" style="min-width: 0; flex: 1 1 auto;">
                <span class="fw-bold d-block text-truncate" style="color: var(--text-primary); font-size: 14px;">Return Verification In Progress</span>
                <span class="text-secondary small" style="font-size: 12.5px;">You submitted <strong>{{ $returnPendingCount }}</strong> item(s) for return. Waiting for custodian inspection and sign-off.</span>
            </div>
        </div>
        <a href="{{ route('borrower.returns') }}" class="btn btn-sm fw-bold px-3 py-2 flex-shrink-0" 
           style="background: var(--accent-color, var(--navy-800)); color: #ffffff; border-radius: 9px; font-size: 12.5px; text-decoration: none;">
            View Returns &rarr;
        </a>
    </div>
    @endif

    {{-- Key Status Metrics (4-Card Responsive Grid) --}}
    <div class="row g-2 g-md-3 mb-4">
        {{-- 1. Active Loans --}}
        <div class="col-6 col-lg-3">
            <a href="{{ route('borrower.returns') }}" class="stat-card-link text-decoration-none d-block h-100 p-3" 
               style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; transition: all 0.2s ease;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div style="width: 38px; height: 38px; background: var(--accent-blue-bg); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-seam" style="color: var(--accent-color, var(--navy-800)); font-size: 17px;"></i>
                    </div>
                    @if($activeBorrowsCount > 0)
                        <span class="badge rounded-pill" style="font-size: 10.5px; background: var(--accent-blue-bg); color: var(--accent-blue); font-weight: 700;">Active</span>
                    @endif
                </div>
                <div class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ $activeBorrowsCount }}</div>
                <div class="text-secondary fw-semibold mt-1 text-truncate" style="font-size: 12px;">Active Loans</div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Equipment currently borrowed</div>
            </a>
        </div>

        {{-- 2. Pending Requests --}}
        <div class="col-6 col-lg-3">
            <a href="{{ route('borrower.history', ['status' => 'pending']) }}" class="stat-card-link text-decoration-none d-block h-100 p-3" 
               style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; transition: all 0.2s ease;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div style="width: 38px; height: 38px; background: var(--accent-yellow-bg, rgba(245, 158, 11, 0.1)); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history" style="color: var(--accent-yellow, #d97706); font-size: 17px;"></i>
                    </div>
                    @if($totalPendingCount > 0)
                        <span class="badge rounded-pill" style="font-size: 10.5px; background: var(--accent-yellow-bg); color: var(--accent-yellow); font-weight: 700;">Reviewing</span>
                    @endif
                </div>
                <div class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ $totalPendingCount }}</div>
                <div class="text-secondary fw-semibold mt-1 text-truncate" style="font-size: 12px;">Pending Requests</div>
                <div class="text-muted text-truncate" style="font-size: 11px;" title="{{ $pendingBorrowsCount }} equipment • {{ $pendingIssuancesCount }} supplies">{{ $pendingBorrowsCount }} equipment &bull; {{ $pendingIssuancesCount }} supplies</div>
            </a>
        </div>

        {{-- 3. Issued Supplies --}}
        <div class="col-6 col-lg-3">
            <a href="{{ route('borrower.issuance') }}" class="stat-card-link text-decoration-none d-block h-100 p-3" 
               style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; transition: all 0.2s ease;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div style="width: 38px; height: 38px; background: var(--accent-green-bg, rgba(22, 163, 74, 0.1)); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-arrow-in-right" style="color: var(--accent-green, #16a34a); font-size: 17px;"></i>
                    </div>
                    @if($pendingConfirmationsCount > 0)
                        <span class="badge rounded-pill" style="font-size: 10.5px; background: var(--accent-green-bg); color: var(--accent-green); font-weight: 700;">Action</span>
                    @endif
                </div>
                <div class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ $pendingConfirmationsCount }}</div>
                <div class="text-secondary fw-semibold mt-1 text-truncate" style="font-size: 12px;">Supplies to Confirm</div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Awaiting your receipt</div>
            </a>
        </div>

        {{-- 4. Completed History --}}
        <div class="col-6 col-lg-3">
            <a href="{{ route('borrower.history') }}" class="stat-card-link text-decoration-none d-block h-100 p-3" 
               style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; transition: all 0.2s ease;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div style="width: 38px; height: 38px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-check2-circle" style="color: var(--text-primary); font-size: 17px;"></i>
                    </div>
                </div>
                <div class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ $totalCompletedCount }}</div>
                <div class="text-secondary fw-semibold mt-1 text-truncate" style="font-size: 12px;">Completed Records</div>
                <div class="text-muted text-truncate" style="font-size: 11px;">Past returns & supplies</div>
            </a>
        </div>
    </div>

    {{-- Currently Held Equipment (Quick Return Access) --}}
    @if($currentLoans->isNotEmpty())
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2 px-1 flex-wrap gap-1">
            <h2 class="fw-bold m-0" style="font-size: 15px; color: var(--text-primary);">Currently In Your Possession</h2>
            <a href="{{ route('borrower.returns') }}" class="text-secondary fw-semibold text-decoration-none small text-nowrap" style="font-size: 12px;">
                Manage Returns &rarr;
            </a>
        </div>
        <div class="row g-2" id="borrowerRecentActivityContainer">
            @foreach($currentLoans as $loan)
            <div class="col-12 col-md-4">
                <div class="p-3 d-flex align-items-center justify-content-between" 
                     style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; min-width: 0; overflow: hidden;">
                    <div class="d-flex align-items-center gap-2 min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                        <div style="width: 36px; height: 36px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-box-seam text-secondary" style="font-size: 15px;"></i>
                        </div>
                        <div class="min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                            <span class="fw-bold text-truncate d-block" style="font-size: 13px; color: var(--text-primary); line-height: 1.2;">{{ $loan->item->name ?? 'Equipment' }}</span>
                            <span class="text-secondary text-truncate d-block" style="font-size: 11px;">Tag: {{ $loan->item->property_tag ?? 'N/A' }}</span>
                        </div>
                    </div>
                    @if($loan->status === 'return_pending')
                        <span class="badge flex-shrink-0 ms-2" style="font-size: 10px; background: var(--accent-yellow-bg); color: var(--accent-yellow); padding: 4px 8px; border-radius: 6px;">Verifying</span>
                    @else
                        <a href="{{ route('borrower.returns') }}" class="btn btn-sm btn-light border fw-semibold px-2 py-1 flex-shrink-0 ms-2" style="font-size: 11.5px; border-radius: 6px;">Return</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Unified Recent Activity Feed --}}
    <div class="activity-card mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px; padding: 18px 20px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div style="min-width: 0; max-width: calc(100% - 100px);">
                <h2 class="fw-bold m-0 text-truncate" style="font-size: 16px; color: var(--text-primary);">Recent Activity</h2>
                <span class="text-secondary small text-truncate d-block" style="font-size: 12px;">Latest borrows, returns, and consumable supply requests</span>
            </div>
            <a href="{{ route('borrower.history') }}" class="text-secondary fw-semibold text-decoration-none small text-nowrap flex-shrink-0 ms-auto" style="font-size: 12.5px;">
                View History &rarr;
            </a>
        </div>
        
        <div class="d-flex flex-column gap-2">
            @forelse($recentActivity as $act)
                <a href="{{ $act->link }}" class="activity-row-link text-decoration-none p-2 px-3 d-flex align-items-center justify-content-between gap-2" 
                   style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; transition: all 0.15s ease; min-width: 0; overflow: hidden;">
                    <div class="d-flex align-items-center gap-2 gap-sm-3 min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                        <div style="width: 36px; height: 36px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($act->type === 'borrow')
                                <i class="bi bi-box-seam text-primary" style="font-size: 15px;"></i>
                            @else
                                <i class="bi bi-box2-heart text-success" style="font-size: 15px;"></i>
                            @endif
                        </div>
                        <div class="min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                            <span class="fw-bold text-truncate d-block" style="font-size: 13.5px; color: var(--text-primary); line-height: 1.2;">{{ $act->title }}</span>
                            <span class="text-secondary text-truncate d-block" style="font-size: 11.5px;">{{ $act->meta }} &bull; {{ $act->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                        @if($act->status === 'pending' || $act->status === 'pending_issue')
                            <span class="status-badge badge-pending">Pending</span>
                        @elseif(in_array($act->status, ['approved', 'active']))
                            <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Active</span>
                        @elseif($act->status === 'issued')
                            <span class="status-badge" style="background: var(--accent-yellow-bg); color: var(--accent-yellow); border: 1px solid rgba(245, 158, 11, 0.3);">Issued</span>
                        @elseif($act->status === 'returned')
                            <span class="status-badge" style="background: var(--bg-surface-hover); color: var(--text-secondary);">Returned</span>
                        @elseif($act->status === 'confirmed')
                            <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Received</span>
                        @elseif($act->status === 'return_pending')
                            <span class="status-badge" style="background: var(--accent-yellow-bg); color: var(--accent-yellow);">Verifying</span>
                        @elseif($act->status === 'rejected')
                            <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                        @elseif($act->status === 'cancelled')
                            <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                        @else
                            <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary);">{{ ucfirst($act->status) }}</span>
                        @endif
                        <i class="bi bi-chevron-right text-secondary d-none d-sm-inline" style="font-size: 12px;"></i>
                    </div>
                </a>
            @empty
                <div class="text-center py-5" style="background: var(--bg-main); border-radius: 12px; border: 1px dashed var(--border-color);">
                    <div style="width: 56px; height: 56px; background: var(--bg-surface); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                        <i class="bi bi-inbox fs-2 text-secondary"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: var(--text-primary); font-size: 15px;">No Activity Recorded</h5>
                    <p class="small text-secondary mb-3" style="font-size: 12.5px; max-width: 280px; margin: 0 auto;">Your equipment loans and supply requests will appear here once you make a request.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('borrower.requests') }}" class="btn fw-bold px-3 py-2" style="background: var(--accent-color); color: var(--btn-text, #ffffff); border-radius: 9px; font-size: 13px;">
                            <i class="bi bi-box-seam me-1"></i> Borrow Equipment
                        </a>
                        <a href="{{ route('borrower.issuance') }}" class="btn btn-light border fw-bold px-3 py-2" style="border-radius: 9px; font-size: 13px; color: var(--text-primary);">
                            <i class="bi bi-box2-heart me-1"></i> Request Supplies
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

</div>

<style>
    .stat-card-link:hover { 
        transform: translateY(-2px); 
        box-shadow: var(--shadow-sm, 0 4px 16px rgba(0,0,0,0.06)); 
        border-color: var(--accent-color) !important;
    }
    .activity-row-link:hover {
        background: var(--bg-surface-hover) !important;
        border-color: var(--accent-color) !important;
        transform: translateX(2px);
    }
    [data-theme="dark"] .stat-card-link:hover { 
        box-shadow: 0 4px 20px rgba(0,0,0,0.4); 
        border-color: var(--accent-color) !important;
    }
    [data-theme="dark"] .activity-row-link:hover {
        border-color: var(--accent-color) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let _lastActiveBorrows = {{ $activeBorrows ?? 0 }};
    let _lastPendingReqs = {{ $pendingCount ?? 0 }};
    let _lastConfirmations = {{ $pendingConfirmations ?? 0 }};
    let _isUpdatingDashboard = false;

    window.addEventListener('baycis:realtime-update', async function(e) {
        const data = e.detail;
        if (!data || !data.counts) return;

        const activeCount = data.counts.active_loans !== undefined ? Number(data.counts.active_loans) : _lastActiveBorrows;
        const pendingReqs = data.counts.pending_borrow_requests !== undefined ? Number(data.counts.pending_borrow_requests) : _lastPendingReqs;
        const confirms = data.counts.pending_confirmations !== undefined ? Number(data.counts.pending_confirmations) : _lastConfirmations;

        // Update stat counters in DOM
        const elActive = document.getElementById('borrowerStatActiveBorrows');
        if (elActive) elActive.textContent = activeCount;

        const elActiveItems = document.getElementById('borrowerStatActiveItems');
        if (elActiveItems) elActiveItems.textContent = activeCount;

        const elPending = document.getElementById('borrowerStatPendingRequests');
        if (elPending) elPending.textContent = pendingReqs;

        const elPendingApp = document.getElementById('borrowerStatPendingApproval');
        if (elPendingApp) elPendingApp.textContent = pendingReqs;

        const elSupplies = document.getElementById('borrowerStatIssuedSupplies');
        if (elSupplies) elSupplies.textContent = confirms;

        const hasChanged = (activeCount !== _lastActiveBorrows) ||
                           (pendingReqs !== _lastPendingReqs) ||
                           (confirms !== _lastConfirmations);

        if (hasChanged && !_isUpdatingDashboard) {
            _isUpdatingDashboard = true;
            try {
                const res = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                if (res.ok) {
                    const text = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');
                    const newSection = doc.getElementById('borrowerRecentActivityContainer');
                    const currentSection = document.getElementById('borrowerRecentActivityContainer');
                    if (newSection && currentSection) {
                        currentSection.innerHTML = newSection.innerHTML;
                    }
                    _lastActiveBorrows = activeCount;
                    _lastPendingReqs = pendingReqs;
                    _lastConfirmations = confirms;
                }
            } catch (err) {
                console.warn('Realtime borrower dashboard refresh error:', err);
            } finally {
                _isUpdatingDashboard = false;
            }
        }
    });
});
</script>
@endsection

