@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper" id="adminDashboard">
    
    {{-- ══════════════════════════════════════════
         PAGE HEADER
         ══════════════════════════════════════════ --}}
    {{-- ══════════════════════════════════════════
         PAGE HEADER WITH LIVE TIME & DATE
         ══════════════════════════════════════════ --}}
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold m-0" style="color: var(--text-primary); font-size: clamp(22px, 3.2vw, 28px); letter-spacing: -0.02em;">Command Center</h1>
            <p class="text-secondary m-0 mt-1" style="font-size: 13.5px;">Real-time inventory status, loan velocity, and property analytics.</p>
        </div>
        {{-- Live Time & Date Widget --}}
        <div class="d-flex align-items-center gap-3 p-2 px-3" 
             style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="d-flex align-items-center justify-content-center" 
                 style="width: 38px; height: 38px; background: var(--accent-blue-bg); border-radius: 10px; color: var(--accent-blue); flex-shrink: 0;">
                <i class="bi bi-clock-history fs-5"></i>
            </div>
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2">
                    <span id="liveClock" class="fw-bold" style="font-size: 14.5px; color: var(--text-primary); font-variant-numeric: tabular-nums; letter-spacing: 0.02em;">
                        {{ \Carbon\Carbon::now()->format('h:i:s A') }}
                    </span>
                    <span class="badge" style="background: var(--accent-green-bg); color: var(--accent-green); font-size: 9.5px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 5px; height: 5px; background: var(--accent-green); border-radius: 50%; display: inline-block;"></span>
                        LIVE
                    </span>
                </div>
                <span id="liveDate" class="text-secondary fw-semibold" style="font-size: 11.5px;">
                    {{ \Carbon\Carbon::now()->format('l, F j, Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         E1: STICKY KPI BAR WITH TREND ARROWS
         ══════════════════════════════════════════ --}}
    <div class="kpi-bar" id="kpiBar">
        <div class="kpi-item">
            <div class="kpi-value">{{ number_format($totalItems ?? 0) }}</div>
            <div class="kpi-label">Total Assets</div>
            <div class="kpi-icon-box bg-icon-blue"><i class="bi bi-box-seam text-blue"></i></div>
        </div>
        <div class="kpi-item">
            @php
                $availPct = ($totalItems > 0) ? round(($availableItems / $totalItems) * 100) : 0;
                $availColor = $availPct >= 75 ? 'var(--accent-green)' : ($availPct >= 40 ? 'var(--accent-yellow)' : 'var(--accent-red)');
            @endphp
            <div class="kpi-value" style="color: {{ $availColor }};">{{ $availPct }}%</div>
            <div class="kpi-label">Available Rate</div>
            <div class="kpi-trend {{ ($availableItems ?? 0) > 0 ? 'trend-up' : 'trend-neutral' }}">
                <i class="bi {{ ($availableItems ?? 0) > 0 ? 'bi-arrow-up' : 'bi-dash' }}"></i>
            </div>
        </div>
        <div class="kpi-item">
            @php
                $trendDir = ($activeBorrows ?? 0) - ($lastWeekActiveBorrows ?? 0);
                $trendClass = $trendDir > 0 ? 'trend-up' : ($trendDir < 0 ? 'trend-down' : 'trend-neutral');
                $trendIcon = $trendDir > 0 ? 'bi-arrow-up' : ($trendDir < 0 ? 'bi-arrow-down' : 'bi-dash');
                $trendPct = ($lastWeekActiveBorrows > 0) ? round(abs($trendDir / $lastWeekActiveBorrows) * 100) : 0;
            @endphp
            <div class="kpi-value" id="kpiActiveBorrowsVal">{{ number_format($activeBorrows ?? 0) }}</div>
            <div class="kpi-label">Active Borrows</div>
            <div class="kpi-trend {{ $trendClass }}">
                <i class="bi {{ $trendIcon }}"></i> {{ $trendPct }}%
            </div>
        </div>
        <div class="kpi-item {{ ($pendingReqs ?? 0) > 0 ? 'kpi-alert' : '' }}" id="kpiPendingReqsCard">
            <div class="kpi-value" id="pendingReqsCount">{{ number_format($pendingReqs ?? 0) }}</div>
            <div class="kpi-label">Pending <span id="kpiPendingDotWrap" class="{{ ($pendingReqs ?? 0) > 0 ? '' : 'd-none' }}"><span class="kpi-pulse-dot"></span></span> Requests</div>
            <div class="kpi-trend {{ ($pendingReqs ?? 0) - ($lastWeekPendingReqs ?? 0) > 0 ? 'trend-up' : 'trend-neutral' }}">
                <i class="bi bi-exclamation-triangle text-red"></i>
            </div>
        </div>
        <div class="kpi-item {{ ($overdueReturns ?? 0) > 0 ? 'kpi-alert' : '' }}">
            <div class="kpi-value" style="color: {{ ($overdueReturns ?? 0) > 0 ? 'var(--accent-red)' : 'var(--text-primary)' }};">{{ number_format($overdueReturns ?? 0) }}</div>
            <div class="kpi-label">Overdue Returns</div>
            <div class="kpi-icon-box bg-icon-red"><i class="bi bi-clock text-red"></i></div>
        </div>
        <div class="kpi-item {{ ($lowStockCount ?? 0) > 0 ? 'kpi-alert' : '' }}">
            <div class="kpi-value" style="color: {{ ($lowStockCount ?? 0) > 0 ? 'var(--accent-yellow)' : 'var(--text-primary)' }};">{{ $lowStockCount ?? 0 }}</div>
            <div class="kpi-label">Low Stock Items</div>
            <div class="kpi-icon-box bg-icon-yellow"><i class="bi bi-boxes text-yellow"></i></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         OPERATIONAL VELOCITY & ANALYTICS STRIP
         ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Checkouts This Month --}}
        <div class="col-6 col-lg-3">
            <div class="panel-card shadow-sm h-100 p-3 d-flex flex-column justify-content-between" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary fw-semibold" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px;">Monthly Checkouts</span>
                    <div style="width: 30px; height: 30px; background: var(--accent-blue-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-repeat text-blue" style="font-size: 14px;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ number_format($monthlyBorrows ?? 0) }}</span>
                    @if(($borrowGrowthPct ?? 0) != 0)
                        <span class="badge" style="background: {{ ($borrowGrowthPct ?? 0) > 0 ? 'var(--accent-green-bg)' : 'var(--accent-red-bg)' }}; color: {{ ($borrowGrowthPct ?? 0) > 0 ? 'var(--accent-green)' : 'var(--accent-red)' }}; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px;">
                            {{ ($borrowGrowthPct ?? 0) > 0 ? '+' : '' }}{{ $borrowGrowthPct }}%
                        </span>
                    @endif
                </div>
                <span class="text-secondary mt-1 text-truncate" style="font-size: 11.5px;">Equipment loans this month</span>
            </div>
        </div>

        {{-- Card 2: Supplies Dispensed --}}
        <div class="col-6 col-lg-3">
            <div class="panel-card shadow-sm h-100 p-3 d-flex flex-column justify-content-between" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary fw-semibold" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px;">Supplies Issued</span>
                    <div style="width: 30px; height: 30px; background: var(--accent-green-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box2-heart text-green" style="font-size: 14px;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ number_format($monthlySupplies ?? 0) }}</span>
                    <span class="text-secondary" style="font-size: 12px;">units</span>
                </div>
                <span class="text-secondary mt-1 text-truncate" style="font-size: 11.5px;">Consumables fulfilled</span>
            </div>
        </div>

        {{-- Card 3: Return Compliance Rate --}}
        <div class="col-6 col-lg-3">
            <div class="panel-card shadow-sm h-100 p-3 d-flex flex-column justify-content-between" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary fw-semibold" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px;">On-Time Return Rate</span>
                    <div style="width: 30px; height: 30px; background: var(--accent-purple-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check text-purple" style="font-size: 14px;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fw-bold" style="font-size: 24px; color: {{ ($returnComplianceRate ?? 100) >= 90 ? 'var(--accent-green)' : (($returnComplianceRate ?? 100) >= 70 ? 'var(--accent-yellow)' : 'var(--accent-red)') }}; line-height: 1.1;">{{ $returnComplianceRate ?? 100 }}%</span>
                </div>
                <span class="text-secondary mt-1 text-truncate" style="font-size: 11.5px;">Fulfillment punctuality</span>
            </div>
        </div>

        {{-- Card 4: Active Borrowers This Month --}}
        <div class="col-6 col-lg-3">
            <div class="panel-card shadow-sm h-100 p-3 d-flex flex-column justify-content-between" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary fw-semibold" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px;">Active Borrowers</span>
                    <div style="width: 30px; height: 30px; background: var(--accent-yellow-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people text-yellow" style="font-size: 14px;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fw-bold" style="font-size: 24px; color: var(--text-primary); line-height: 1.1;">{{ number_format($activeBorrowersCount ?? 0) }}</span>
                    <span class="text-secondary" style="font-size: 12px;">users</span>
                </div>
                <span class="text-secondary mt-1 text-truncate" style="font-size: 11.5px;">Circulating this month</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         E2: SPLIT PANEL — Charts & Priority Feeds
         ══════════════════════════════════════════ --}}
    <div class="panels-grid-alt mb-4">
        
        {{-- LEFT COLUMN: Asset Status & 7-Day Velocity Chart --}}
        <div class="d-flex flex-column gap-4">
            
            {{-- Status Donut Chart (lazy-loaded via AJAX) --}}
            <div class="panel-card chart-panel shadow-sm" id="chartPanel">
                <div class="panel-header">
                    <div>
                        <h2 class="fs-5 m-0 fw-bold">Asset Distribution</h2>
                        <span class="text-secondary" style="font-size: 12px;">Click a segment to filter inventory</span>
                    </div>
                </div>
                <div class="skeleton-loader" id="chartSkeleton">
                    <div class="skeleton-ring"></div>
                    <div class="skeleton-legend">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line"></div>
                    </div>
                </div>
                <div id="chartContainer" style="display: none;">
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <svg id="donutChart" width="160" height="160" viewBox="0 0 160 160">
                            <circle cx="80" cy="80" r="68" fill="none" stroke="var(--border-color)" stroke-width="22"/>
                            <circle id="donutSegment1" cx="80" cy="80" r="68" fill="none" stroke="var(--accent-green)" stroke-width="22" stroke-dasharray="0 427" stroke-dashoffset="0" transform="rotate(-90 80 80)" style="transition: stroke-dasharray 0.8s ease;"/>
                            <circle id="donutSegment2" cx="80" cy="80" r="68" fill="none" stroke="var(--accent-yellow)" stroke-width="22" stroke-dasharray="0 427" stroke-dashoffset="0" transform="rotate(-90 80 80)" style="transition: stroke-dasharray 0.8s ease;"/>
                            <circle id="donutSegment3" cx="80" cy="80" r="68" fill="none" stroke="var(--accent-red)" stroke-width="22" stroke-dasharray="0 427" stroke-dashoffset="0" transform="rotate(-90 80 80)" style="transition: stroke-dasharray 0.8s ease;"/>
                            <circle id="donutSegment4" cx="80" cy="80" r="68" fill="none" stroke="var(--accent-blue)" stroke-width="22" stroke-dasharray="0 427" stroke-dashoffset="0" transform="rotate(-90 80 80)" style="transition: stroke-dasharray 0.8s ease;"/>
                            <text x="80" y="80" text-anchor="middle" dominant-baseline="central" id="donutCenterText" fill="var(--text-primary)" font-size="28" font-weight="700">--%</text>
                        </svg>
                        <div id="chartLegend" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>

            {{-- 7-Day Velocity Chart --}}
            <div class="panel-card shadow-sm">
                <div class="panel-header mb-3">
                    <div>
                        <h2 class="fs-5 m-0 fw-bold">7-Day Loan Velocity</h2>
                        <span class="text-secondary" style="font-size: 12px;">Daily checkout activity trends</span>
                    </div>
                    <span class="badge" style="background: var(--accent-blue-bg); color: var(--accent-blue); font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 8px;">
                        Past Week
                    </span>
                </div>
                <div id="velocityChartBars" class="d-flex align-items-end justify-content-between gap-2 pt-3 pb-2 px-1" style="height: 130px; border-bottom: 1px solid var(--border-color); position: relative;">
                    <div class="text-secondary small w-100 text-center py-4">
                        <i class="bi bi-arrow-repeat spin me-1"></i> Loading loan velocity trends...
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Priority Alert Feed & Top Categories --}}
        <div class="d-flex flex-column gap-4">
            
            {{-- Priority Alert Feed --}}
            <div class="panel-card alert-panel shadow-sm">
                <div class="panel-header">
                    <h2 class="fs-5 m-0 fw-bold"><i class="bi bi-bell-fill me-2 text-red"></i>Priority Alerts</h2>
                </div>
                <div class="alert-feed">
                    @if(($pendingReqs ?? 0) > 0)
                    <a href="{{ route('admin.requests') }}" class="alert-item alert-warning text-decoration-none">
                        <div class="alert-item-icon bg-icon-yellow"><i class="bi bi-ui-checks text-yellow"></i></div>
                        <div class="alert-item-content">
                            <span class="alert-item-title">{{ $pendingReqs }} Pending Request{{ $pendingReqs > 1 ? 's' : '' }} Need Review</span>
                            <span class="alert-item-sub">Awaiting approval action</span>
                        </div>
                        <i class="bi bi-chevron-right text-secondary"></i>
                    </a>
                    @endif
                    @if(($pendingRets ?? 0) > 0)
                    <a href="{{ route('admin.returns') }}" class="alert-item alert-warning text-decoration-none">
                        <div class="alert-item-icon bg-icon-yellow"><i class="bi bi-arrow-return-left text-yellow"></i></div>
                        <div class="alert-item-content">
                            <span class="alert-item-title">{{ $pendingRets }} Return{{ $pendingRets > 1 ? 's' : '' }} Pending Verification</span>
                            <span class="alert-item-sub">Admin check required</span>
                        </div>
                        <i class="bi bi-chevron-right text-secondary"></i>
                    </a>
                    @endif
                    @if(($overdueReturns ?? 0) > 0)
                    <a href="{{ route('admin.returns') }}" class="alert-item alert-danger-item text-decoration-none">
                        <div class="alert-item-icon bg-icon-red"><i class="bi bi-clock text-red"></i></div>
                        <div class="alert-item-content">
                            <span class="alert-item-title">{{ $overdueReturns }} Overdue Return{{ $overdueReturns > 1 ? 's' : '' }}</span>
                            <span class="alert-item-sub">Borrowed over 7 days ago</span>
                        </div>
                        <i class="bi bi-chevron-right text-secondary"></i>
                    </a>
                    @endif
                    @if(($lowStockCount ?? 0) > 0)
                    <a href="{{ route('items.issuance') }}" class="alert-item alert-warning text-decoration-none">
                        <div class="alert-item-icon bg-icon-yellow"><i class="bi bi-boxes text-yellow"></i></div>
                        <div class="alert-item-content">
                            <span class="alert-item-title">{{ $lowStockCount }} Low Stock {{ $lowStockCount > 1 ? 'Items' : 'Item' }}</span>
                            <span class="alert-item-sub">Consumables below reorder level</span>
                        </div>
                        <i class="bi bi-chevron-right text-secondary"></i>
                    </a>
                    @endif
                    @if(($pendingReqs ?? 0) + ($pendingRets ?? 0) + ($overdueReturns ?? 0) + ($lowStockCount ?? 0) == 0)
                    <div class="text-center text-secondary p-4">
                        <i class="bi bi-check2-circle fs-2 d-block mb-2" style="color: var(--accent-green);"></i>
                        <small>All clear — no urgent items require attention.</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Top Inventory Categories --}}
            <div class="panel-card shadow-sm">
                <div class="panel-header mb-3">
                    <div>
                        <h2 class="fs-5 m-0 fw-bold">Inventory Categories</h2>
                        <span class="text-secondary" style="font-size: 12px;">Asset distribution by classification</span>
                    </div>
                    <a href="{{ route('items.index') }}" class="view-all text-decoration-none" style="font-size: 12px;">View All &rarr;</a>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse($topCategories ?? [] as $cat)
                        @php
                            $cName = is_array($cat) ? ($cat['name'] ?? 'Uncategorized') : ($cat->name ?? 'Uncategorized');
                            $cCount = is_array($cat) ? ($cat['count'] ?? 0) : ($cat->count ?? 0);
                            $cPct = is_array($cat) ? ($cat['pct'] ?? 0) : ($cat->pct ?? 0);
                        @endphp
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 12.5px;">
                                <span class="fw-semibold text-truncate" style="color: var(--text-primary); max-width: 65%;">{{ $cName }}</span>
                                <span class="text-secondary fw-bold" style="font-size: 12px;">{{ $cCount }} <span class="fw-normal text-muted">({{ $cPct }}%)</span></span>
                            </div>
                            <div class="progress" style="height: 7px; background-color: var(--bg-surface-hover); border-radius: 10px; overflow: hidden;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ max($cPct, 3) }}%; background-color: var(--accent-blue); border-radius: 10px; transition: width 0.8s ease;" 
                                     aria-valuenow="{{ $cPct }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary small text-center py-3">No category data recorded yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    {{-- ══════════════════════════════════════════
         E3: BOTTOM PANELS — Activity + Leaderboard + Quick Actions
         ══════════════════════════════════════════ --}}
    <div class="panels-grid">
        
        {{-- LEFT: Recent Activity --}}
        <div class="panel-card activity-panel shadow-sm">
            <div class="panel-header">
                <h2 class="fs-5 m-0 fw-bold">Recent Activity</h2>
                <a href="{{ route('items.history') }}" class="view-all text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-list">
                @forelse($recentActivity ?? [] as $activity)
                    <div class="activity-item">
                        <div class="dot {{ in_array($activity->status ?? '', ['approved', 'returned']) ? 'dot-green' : (in_array($activity->status ?? '', ['rejected', 'cancelled']) ? 'dot-red' : 'dot-yellow') }}"></div>
                        <div class="activity-content">
                            <p class="activity-text">
                                {{ ucfirst($activity->status ?? 'Unknown') }} Request: <span class="fw-normal">{{ $activity->item?->name ?? 'Asset' }}</span>
                            </p>
                            <p class="activity-sub">by {{ $activity->user?->name ?? 'Unknown User' }}</p>
                        </div>
                        <span class="activity-time">{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Just now' }}</span>
                    </div>
                @empty
                    <div class="text-center text-muted p-4">
                        <i class="bi bi-inbox fs-2 mb-2 d-block text-secondary"></i>
                        <small>No recent activity logs found.</small>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT TOP: Borrower Leaderboard + Quick Actions --}}
        <div class="d-flex flex-column gap-4">
            {{-- Borrower Leaderboard --}}
            @if(($topBorrowers ?? collect())->count() > 0)
            <div class="panel-card shadow-sm">
                <div class="panel-header">
                    <h2 class="fs-5 m-0 fw-bold"><i class="bi bi-trophy me-2" style="color: var(--accent-yellow);"></i>Top Borrowers</h2>
                    <span class="text-secondary" style="font-size: 11px;">This month</span>
                </div>
                <div class="leaderboard-list">
                    @foreach($topBorrowers as $i => $borrower)
                        <div class="leaderboard-item">
                            <span class="leaderboard-rank {{ $i < 3 ? 'leaderboard-top' : '' }}">{{ $i + 1 }}</span>
                            <div class="leaderboard-name text-truncate">{{ $borrower->user_name }}</div>
                            <span class="leaderboard-count">{{ $borrower->request_count }} <small>req</small></span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- W1.4: Recent Items Widget --}}
            <div class="panel-card shadow-sm" id="recentItemsWidget">
                <div class="panel-header">
                    <h2 class="fs-5 m-0 fw-bold"><i class="bi bi-clock-history me-2" style="color:var(--accent-blue);"></i>Recently Accessed</h2>
                    <span class="text-secondary" style="font-size:11px;">Past 5 items</span>
                </div>
                <div class="recent-items-list" id="recentItemsList">
                    <div class="text-center text-secondary py-3" style="font-size:12px;">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                        <span>Edit an item to see it here.</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="panel-card shadow-sm flex-grow-1">
                <div class="panel-header">
                    <h2 class="fs-5 m-0 fw-bold">Quick Actions</h2>
                </div>
                <div class="actions-list">
                    <a href="{{ route('items.issuance') }}" class="quick-action-btn">
                        <div class="qa-icon bg-icon-green"><i class="bi bi-box-arrow-in-right text-green"></i></div>
                        <span class="qa-text">Issue Item</span>
                        <i class="bi bi-arrow-up-right qa-arrow"></i>
                    </a>
                    <a href="{{ route('admin.requests') }}" class="quick-action-btn">
                        <div class="qa-icon bg-icon-yellow"><i class="bi bi-ui-checks text-yellow"></i></div>
                        <span class="qa-text">Review Requests</span>
                        <i class="bi bi-arrow-up-right qa-arrow"></i>
                    </a>
                    <a href="{{ route('items.index') }}" class="quick-action-btn">
                        <div class="qa-icon bg-icon-blue"><i class="bi bi-box text-blue"></i></div>
                        <span class="qa-text">Manage Inventory</span>
                        <i class="bi bi-arrow-up-right qa-arrow"></i>
                    </a>
                    <a href="{{ route('items.create') }}" class="quick-action-btn">
                        <div class="qa-icon bg-icon-purple"><i class="bi bi-plus-circle text-purple"></i></div>
                        <span class="qa-text">Add New Item</span>
                        <i class="bi bi-arrow-up-right qa-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     E3: FLOATING ACTION BUTTON (FAB)
     ══════════════════════════════════════════ --}}
<div class="fab-container" id="fabContainer">
    <button class="fab-main" id="fabMain" onclick="toggleFab()" aria-label="Quick actions">
        <i class="bi bi-plus-lg" id="fabIcon"></i>
    </button>
    <div class="fab-menu" id="fabMenu">
        <a href="{{ route('items.create') }}" class="fab-item" title="New Item">
            <span class="fab-item-label">New Item</span>
            <div class="fab-item-icon bg-icon-blue"><i class="bi bi-box text-blue"></i></div>
        </a>
        <a href="{{ route('items.issuance') }}" class="fab-item" title="Issue">
            <span class="fab-item-label">Issue</span>
            <div class="fab-item-icon bg-icon-green"><i class="bi bi-box-arrow-in-right text-green"></i></div>
        </a>
        <a href="{{ route('admin.requests') }}" class="fab-item" title="Approve">
            <span class="fab-item-label">Approve</span>
            <div class="fab-item-icon bg-icon-yellow"><i class="bi bi-check-lg text-yellow"></i></div>
        </a>
        <button class="fab-item" title="Refresh" onclick="refreshChart()">
            <span class="fab-item-label">Refresh</span>
            <div class="fab-item-icon bg-icon-purple"><i class="bi bi-arrow-clockwise text-purple"></i></div>
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════
     E2: CHART LOADING + INTERACTIVE JS
     ══════════════════════════════════════════ --}}
<script>
// ── Chart data loading with animations ──
async function loadChartData() {
    try {
        const res = await fetch('{{ route("admin.dashboard.chart") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }
        });
        if (!res.ok) return;
        const data = await res.json();
        
        // Hide skeleton, show chart
        document.getElementById('chartSkeleton').style.display = 'none';
        document.getElementById('chartContainer').style.display = 'block';
        
        const total = data.status_breakdown.available + data.status_breakdown.borrowed + data.status_breakdown.damaged + data.status_breakdown.maintenance;
        const circumference = 2 * Math.PI * 68; // r=68
        
        const segments = [
            { id: 'donutSegment1', value: data.status_breakdown.available, color: 'var(--accent-green)', label: 'Available', filterStatus: 'available', hoverColor: '#059669' },
            { id: 'donutSegment2', value: data.status_breakdown.borrowed, color: 'var(--accent-yellow)', label: 'Borrowed', filterStatus: 'borrowed', hoverColor: '#d97706' },
            { id: 'donutSegment3', value: data.status_breakdown.damaged, color: 'var(--accent-red)', label: 'Damaged', filterStatus: 'damaged', hoverColor: '#dc2626' },
            { id: 'donutSegment4', value: data.status_breakdown.maintenance, color: 'var(--accent-blue)', label: 'Maintenance', filterStatus: 'maintenance', hoverColor: 'var(--navy-700, #24476b)' },
        ];
        
        // ── Strip old listeners from SVG circles before re-binding ──
        document.querySelectorAll('#donutChart circle[id^="donutSegment"]').forEach(function(el) {
            el.replaceWith(el.cloneNode(true));
        });
        
        // ── Step 1: Start all segments at zero (entry animation setup) ──
        segments.forEach(function(seg) {
            const el = document.getElementById(seg.id);
            if (!el) return;
            el.style.strokeDasharray = '0 ' + circumference;
            el.style.strokeDashoffset = '0';
            el.style.transition = 'stroke-dasharray 1s cubic-bezier(0.34, 1.56, 0.64, 1), stroke 0.3s ease, opacity 0.3s ease';
            // Add hover interactivity
            el.style.cursor = seg.value > 0 ? 'pointer' : 'default';
            
            // Click to filter
            (function(s) {
                el.addEventListener('click', function(e) {
                    if (s.value > 0) {
                        window.location.href = '/admin/inventory?status=' + s.filterStatus;
                    }
                });
                // Hover effect: increase stroke width + lighten
                el.addEventListener('mouseenter', function() {
                    if (s.value > 0) {
                        this.style.stroke = s.hoverColor;
                        this.style.opacity = '1';
                        this.style.filter = 'drop-shadow(0 0 6px ' + s.hoverColor + ')';
                        // Highlight the associated legend item
                        var legendItems = document.querySelectorAll('.chart-legend-item');
                        legendItems.forEach(function(li) {
                            var lbl = li.querySelector('.legend-label');
                            if (lbl && lbl.textContent.trim() === s.label) {
                                li.style.transform = 'scale(1.05)';
                                li.style.fontWeight = '700';
                            } else {
                                li.style.opacity = '0.5';
                            }
                        });
                    }
                });
                el.addEventListener('mouseleave', function() {
                    this.style.stroke = s.color;
                    this.style.opacity = '';
                    this.style.filter = '';
                    var legendItems = document.querySelectorAll('.chart-legend-item');
                    legendItems.forEach(function(li) {
                        li.style.transform = '';
                        li.style.fontWeight = '';
                        li.style.opacity = '';
                    });
                });
            })(seg);
        });
        
        // ── Step 2: Animate segments from 0 to final size (staggered) ──
        function animateSegments() {
            let offset = 0;
            segments.forEach(function(seg, idx) {
                const el = document.getElementById(seg.id);
                if (!el) return;
                const length = total > 0 ? (seg.value / total) * circumference : 0;
                
                // Stagger the animation: each segment starts 200ms after the previous
                setTimeout(function() {
                    el.style.strokeDasharray = length + ' ' + (circumference - length);
                    el.style.strokeDashoffset = -offset;
                    offset += length;
                }, idx * 200);
            });
            
            // ── Step 3: Set center text AFTER animation completes ──
            setTimeout(function() {
                var centerEl = document.getElementById('donutCenterText');
                if (centerEl) {
                    // First hide text without transition, then change it, then fade in
                    centerEl.style.transition = 'none';
                    centerEl.style.opacity = '0';
                    centerEl.style.transform = 'scale(0.5)';
                    // Force layout so the above takes effect before we change text
                    void centerEl.offsetHeight;
                    // Now change the text (invisible)
                    centerEl.textContent = data.availability_pct + '%';
                    // Then enable transition and fade in
                    centerEl.style.transition = 'opacity 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    centerEl.style.opacity = '1';
                    centerEl.style.transform = 'scale(1)';
                }
            }, segments.length * 200 + 100);
        }
        
        // ── Trigger animation on next frame ──
        requestAnimationFrame(function() {
            requestAnimationFrame(animateSegments);
        });
        
        // ── Center text (initial, hidden before animation) ──
        var centerEl = document.getElementById('donutCenterText');
        if (centerEl) {
            centerEl.textContent = '0%';
            centerEl.style.opacity = '0.3';
        }
        
        // ── Legend ──
        const legend = document.getElementById('chartLegend');
        legend.innerHTML = '';
        segments.forEach(function(seg) {
            const pct = total > 0 ? Math.round((seg.value / total) * 100) : 0;
            const item = document.createElement('div');
            item.className = 'chart-legend-item';
            item.style.cursor = seg.value > 0 ? 'pointer' : 'default';
            item.style.transition = 'all 0.3s ease';
            item.innerHTML = '<span class="legend-dot" style="background:' + seg.color + ';"></span>'
                + '<span class="legend-label">' + seg.label + '</span>'
                + '<span class="legend-value">' + seg.value + ' (' + pct + '%)</span>';
            
            // Hover effect on legend item → highlight segment
            (function(s) {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(6px)';
                    var segEl = document.getElementById(s.id);
                    if (segEl && s.value > 0) {
                        segEl.style.stroke = s.hoverColor;
                        segEl.style.filter = 'drop-shadow(0 0 6px ' + s.hoverColor + ')';
                    }
                });
                item.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    var segEl = document.getElementById(s.id);
                    if (segEl) {
                        segEl.style.stroke = s.color;
                        segEl.style.filter = '';
                    }
                });
                item.addEventListener('click', function() {
                    if (s.value > 0) {
                        window.location.href = '/admin/inventory?status=' + s.filterStatus;
                    }
                });
            })(seg);
            
            legend.appendChild(item);
        });
        
        // ── Step 4: Render 7-Day Velocity Chart ──
        const velocityContainer = document.getElementById('velocityChartBars');
        if (velocityContainer && data.daily_borrows && data.daily_borrows.length > 0) {
            const counts = data.daily_borrows.map(b => b.count);
            const maxVal = Math.max(...counts, 4);
            velocityContainer.innerHTML = '';
            
            data.daily_borrows.forEach(item => {
                const heightPct = item.count > 0 ? Math.max(Math.round((item.count / maxVal) * 100), 12) : 4;
                const barColor = item.count > 0 ? 'var(--accent-blue)' : 'var(--border-color)';
                const col = document.createElement('div');
                col.className = 'd-flex flex-column align-items-center flex-grow-1';
                col.style.height = '100%';
                col.style.justifyContent = 'flex-end';
                col.style.cursor = 'default';
                col.title = item.date + ' (' + item.day + '): ' + item.count + ' checkout(s)';
                
                col.innerHTML = 
                    '<span style="font-size: 11px; font-weight: 700; color: ' + (item.count > 0 ? 'var(--text-primary)' : 'var(--text-secondary)') + '; margin-bottom: 4px; opacity: ' + (item.count > 0 ? '1' : '0.5') + ';">'
                    + item.count
                    + '</span>'
                    + '<div class="velocity-bar" style="width: 100%; max-width: 28px; background: ' + barColor + '; height: 0%; border-radius: 6px 6px 2px 2px; transition: height 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); min-height: 4px;"></div>'
                    + '<span style="font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-top: 6px;">'
                    + item.day
                    + '</span>';
                velocityContainer.appendChild(col);
                
                // Trigger entry animation
                setTimeout(function() {
                    var bar = col.querySelector('.velocity-bar');
                    if (bar) bar.style.height = heightPct + '%';
                }, 80);
            });
        }
        
    } catch (e) { /* silently ignore */ }
}

// ── Realtime Clock & Date Updater ──
function updateLiveClock() {
    const clockEl = document.getElementById('liveClock');
    const dateEl = document.getElementById('liveDate');
    if (!clockEl || !dateEl) return;
    const now = new Date();
    clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    dateEl.textContent = now.toLocaleDateString([], { weekday: 'long', month: 'short', day: '2-digit', year: 'numeric' });
}
setInterval(updateLiveClock, 1000);
document.addEventListener('DOMContentLoaded', updateLiveClock);

function toggleFab() {
    const menu = document.getElementById('fabMenu');
    const icon = document.getElementById('fabIcon');
    const isOpen = menu.classList.contains('show');
    menu.classList.toggle('show');
    icon.className = isOpen ? 'bi bi-plus-lg' : 'bi bi-x-lg';
}

function refreshChart() {
    const btn = document.querySelector('.fab-item[title="Refresh"]');
    if (btn) btn.style.pointerEvents = 'none';
    document.getElementById('chartSkeleton').style.display = 'flex';
    document.getElementById('chartContainer').style.display = 'none';
    const velocityContainer = document.getElementById('velocityChartBars');
    if (velocityContainer) {
        velocityContainer.innerHTML = '<div class="text-secondary small w-100 text-center py-4"><i class="bi bi-arrow-repeat spin me-1"></i> Loading loan velocity trends...</div>';
    }
    setTimeout(function() {
        loadChartData();
        if (btn) { btn.style.pointerEvents = ''; toggleFab(); }
    }, 400);
}

// ── Load chart on page ready ──
document.addEventListener('DOMContentLoaded', loadChartData);

// ── KPI bar sticky offset ──
document.addEventListener('DOMContentLoaded', function() {
    const kpiBar = document.getElementById('kpiBar');
    if (kpiBar) {
        const headerHeight = 72;
        kpiBar.style.top = headerHeight + 'px';
    }
});

// ── W1.4: Load recent items from localStorage ──
document.addEventListener('DOMContentLoaded', function() {
    function loadRecentItems() {
        var list = document.getElementById('recentItemsList');
        if (!list) return;
        var items = JSON.parse(localStorage.getItem('recent-items') || '[]');
        if (items.length === 0) {
            list.innerHTML = '<div class="text-center text-secondary py-3" style="font-size:12px;"><i class="bi bi-inbox fs-4 d-block mb-1"></i><span>Edit an item to see it here.</span></div>';
            return;
        }
        var html = '';
        var count = Math.min(items.length, 5);
        for (var i = 0; i < count; i++) {
            var item = items[i];
            var safeName = (item.name || 'Unknown').replace(/[&<>"']/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m] || m; });
            var safeTag = (item.tag || '').replace(/[&<>"']/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m] || m; });
            html += '<a href="/admin/inventory/' + item.id + '/edit" class="recent-item" style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;text-decoration:none;color:var(--text-primary);transition:background 0.15s;">'
                 +   '<div style="width:32px;height:32px;background:var(--bg-surface-hover);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-box" style="color:var(--text-secondary);font-size:14px;"></i></div>'
                 +   '<div style="flex:1;min-width:0;">'
                 +     '<div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + safeName + '</div>'
                 +     '<div style="font-size:11px;color:var(--text-secondary);">' + safeTag + ' &middot; ' + item.time + '</div>'
                 +   '</div>'
                 +   '<i class="bi bi-chevron-right" style="color:var(--text-secondary);font-size:12px;"></i>'
                 + '</a>';
        }
        list.innerHTML = html;
    }
    loadRecentItems();
});

// ── Realtime In-Page Update for Dashboard KPIs ──
window.addEventListener('baycis:realtime-update', function(e) {
    const counts = e.detail?.counts;
    if (!counts) return;

    // Update Pending Requests KPI count & pulse dot
    if (counts.pending_borrow_requests !== undefined) {
        const pCount = Number(counts.pending_borrow_requests);
        const pEl = document.getElementById('pendingReqsCount');
        if (pEl) pEl.textContent = pCount;

        const card = document.getElementById('kpiPendingReqsCard');
        if (card) {
            if (pCount > 0) card.classList.add('kpi-alert');
            else card.classList.remove('kpi-alert');
        }

        const dotWrap = document.getElementById('kpiPendingDotWrap');
        if (dotWrap) {
            if (pCount > 0) dotWrap.classList.remove('d-none');
            else dotWrap.classList.add('d-none');
        }
    }

    // Update Active Borrows KPI
    if (counts.active_borrows !== undefined) {
        const abEl = document.getElementById('kpiActiveBorrowsVal');
        if (abEl) abEl.textContent = Number(counts.active_borrows);
    }
});
</script>

<style>
.recent-items-list a:hover { background: var(--bg-surface-hover) !important; }
.velocity-bar:hover { filter: brightness(1.2); }
</style>
@endsection