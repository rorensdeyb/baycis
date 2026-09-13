@extends('layouts.admin')

@section('title', 'Issuance')

@section('content')
<div class="mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary); letter-spacing:-.02em;"><i class="bi bi-box-seam me-2" style="color:var(--accent-blue);"></i>Consumable Issuance</h4>
            <p class="text-secondary mb-0" style="font-size:13px;">Monitor stock, fulfill requests, and track every issuance — all in one workspace.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-2" style="background:var(--bg-surface);border:1px solid var(--border-color);color:var(--text-secondary);font-weight:600;">
                <i class="bi bi-shield-check me-1" style="color:#10b981;"></i> Custodian & Admin
            </span>
            <a href="#history-panel" class="btn btn-sm btn-light border fw-semibold" onclick="document.querySelector('[data-bs-target=\'#history-panel\']')?.click()"><i class="bi bi-download me-1"></i> Export History</a>
        </div>
    </div>
    {{-- <div class="d-flex align-items-center gap-2 mt-3 p-2 px-3 rounded-3 flex-wrap" style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);overflow-x:auto;">
        <span class="badge bg-primary rounded-pill">1</span><span class="small fw-semibold">Request</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="badge" style="background:#f59e0b;color:#fff;">2</span><span class="small fw-semibold">Issue & Deduct</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="badge bg-success rounded-pill">3</span><span class="small fw-semibold">Confirm Receipt</span>
        <span class="ms-auto small text-secondary d-none d-md-inline">Stock-aware • Audited • Notified</span>
    </div> --}}
</div>

{{-- ═══ KPI PILL ROW ═══ --}}
<div class="row g-3 mb-4" id="kpiRow">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-pill" data-go-tab="history" style="--kpi:var(--success);--kpi-bg:var(--success-soft);">
            <div class="kpi-icon"><i class="bi bi-send-check"></i></div>
            <div><div class="kpi-val">{{ $issuedThisWeek }}</div><div class="kpi-lbl">Issued This Week</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-pill" data-go-tab="history" style="--kpi:var(--accent-blue);--kpi-bg:var(--accent-blue-bg);">
            <div class="kpi-icon"><i class="bi bi-calendar-month"></i></div>
            <div><div class="kpi-val">{{ $issuedThisMonth }}</div><div class="kpi-lbl">This Month</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-pill" data-go-tab="pending" style="--kpi:var(--warning);--kpi-bg:var(--warning-soft);">
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="kpi-val" id="kpiPendingRequestsVal">{{ $pendingRequests->total() }}</div><div class="kpi-lbl">Pending Requests</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-pill" data-go-tab="pending" style="--kpi:#8b5cf6;--kpi-bg:rgba(139,92,246,0.12);">
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div><div class="kpi-val" id="kpiPendingConfirmVal">{{ $pendingConfirmations->total() }}</div><div class="kpi-lbl">Awaiting Confirm</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        @php $belowMin = $stocks->filter(fn($s) => $s->isLowStock() || $s->isOutOfStock())->count(); @endphp
        <div class="kpi-pill" data-go-tab="stocks" data-toggle-lowstock style="--kpi:var(--danger);--kpi-bg:var(--danger-soft);">
            <div class="kpi-icon"><i class="bi bi-exclamation-octagon"></i></div>
            <div><div class="kpi-val">{{ $belowMin }}</div><div class="kpi-lbl">Below Minimum</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-pill" data-go-tab="stocks" style="--kpi:var(--text-secondary);--kpi-bg:var(--bg-main);">
            <div class="kpi-icon"><i class="bi bi-boxes"></i></div>
            <div><div class="kpi-val">{{ $stocks->count() }}</div><div class="kpi-lbl">Stock Items</div></div>
        </div>
    </div>
</div>

{{-- ═══ BULK ISSUE CART (ISS-3) — Drawer-Based ═══ --}}

{{-- ═══ TABS CARD ═══ --}}
<div class="panel-card p-3 p-md-4">
    <ul class="nav custom-settings-nav mb-0 pb-2 border-bottom flex-nowrap overflow-auto" role="tablist" id="issTabs">
        <li class="nav-item">
            <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#stocks-panel" type="button">
                <i class="bi bi-box-seam me-1"></i>Stocks
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#pending-panel" type="button">
                <i class="bi bi-inbox me-1"></i>Pending
                <span class="badge rounded-pill ms-1" id="issuancePendingTabBadge" style="background:var(--danger,#dc3545);color:#fff;">{{ $pendingRequests->total() + $pendingConfirmations->total() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#history-panel" type="button">
                <i class="bi bi-clock-history me-1"></i>History Workbench
            </button>
        </li>
    </ul>

    <div class="tab-content pt-3">

        {{-- ─── TAB 1 · STOCKS ─── --}}
        <div class="tab-pane fade show active" id="stocks-panel">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-1">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2" onclick="stockForm.open()" style="height:38px;padding:0 16px;border-radius:var(--radius-md,10px);">
                        <i class="bi bi-plus-lg"></i>
                        <span>Add Consumable Stock</span>
                    </button>
                    <button type="button" class="btn btn-iss-emerald fw-bold" id="tbBulkOpen" style="height:38px;">
                        <i class="bi bi-collection me-1"></i>New Bulk Issue
                    </button>
                    <button type="button" class="btn btn-iss-outline fw-semibold" id="tbImportCsv" title="Import from CSV" style="height:38px;">
                        <i class="bi bi-file-earmark-arrow-up text-success me-1"></i>Import CSV
                    </button>
                    <button type="button" class="btn btn-iss-outline fw-semibold" id="btnScanIssue" title="Scan barcode to find stock" style="height:38px;">
                        <i class="bi bi-upc-scan text-primary me-1"></i>Scan
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                    <button type="button" class="chip-btn" id="lowStockChip" title="Toggle below minimum filter">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Below Minimum</span>
                    </button>
                    <div class="search-mini">
                        <i class="bi bi-search"></i>
                        <input type="text" id="stockSearch" placeholder="Filter stocks…" autocomplete="off">
                        <button type="button" class="btn btn-sm p-0 border-0 bg-transparent ms-1 d-none" id="clearStockSearch" title="Clear"><i class="bi bi-x-circle text-secondary"></i></button>
                    </div>
                </div>
            </div>

            <table class="admin-table w-100" id="stocksTable" data-drawer-rows data-drawer-renderer="renderStockDrawer">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Min</th>
                        <th>Last Issued</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $s)
                    <tr data-name="{{ strtolower($s->name) }}"
                        data-low="{{ ($s->isLowStock() || $s->isOutOfStock()) ? 1 : 0 }}"
                        data-id="{{ $s->id }}" data-name-full="{{ $s->name }}" data-unit="{{ $s->unit }}"
                        data-stock="{{ $s->stock_quantity }}" data-min="{{ $s->getMinStockThreshold() }}">
                        <td class="fw-semibold" style="color:var(--text-primary);">{{ $s->name }}</td>
                        <td class="text-center">
                            <span class="stock-figure" data-sid="{{ $s->id }}">{{ $s->stock_quantity }}</span>
                            <button type="button" class="quick-add-btn" title="Quick replenish (+N)"
                                    onclick="event.stopPropagation(); quickReplenish.open(this.closest('tr'));">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </td>
                        <td class="text-center text-secondary">{{ $s->unit }}</td>
                        <td class="text-center text-secondary">{{ $s->getMinStockThreshold() }}</td>
                        <td class="text-secondary small">{{ $s->last_issued_at ? \Carbon\Carbon::parse($s->last_issued_at)->diffForHumans() : '—' }}</td>
                        <td class="text-center">
                            @if($s->isOutOfStock())
                                <span class="badge rounded-pill fw-bold" style="background:var(--danger-soft,#fef2f2);color:var(--danger,#dc2626);border:1px solid rgba(220,38,38,0.2);">Out of Stock</span>
                            @elseif($s->isLowStock())
                                <span class="badge rounded-pill fw-bold" style="background:var(--warning-soft,#fffbeb);color:var(--warning,#f59e0b);border:1px solid rgba(245,158,11,0.2);">Low Stock</span>
                            @else
                                <span class="badge rounded-pill fw-bold" style="background:var(--success-soft,#ecfdf5);color:var(--success,#10b981);border:1px solid rgba(16,185,129,0.2);">In Stock</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <button type="button" class="act-btn act-issue" title="Issue Stock" {{ $s->isOutOfStock() ? 'disabled' : '' }}
                                    onclick="event.stopPropagation(); issueForm.open({{ $s->id }}, '{{ addslashes($s->name) }}', {{ $s->stock_quantity }}, '{{ $s->unit }}');">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </button>
                                <button type="button" class="act-btn act-history" title="Stock History"
                                    onclick="event.stopPropagation(); showStockHistory({{ $s->id }}, '{{ addslashes($s->name) }}');">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                <button type="button" class="act-btn act-edit" title="Edit Item"
                                    onclick="event.stopPropagation(); stockForm.open({{ $s->id }}, true);">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="act-btn act-danger" title="Delete Item"
                                    onclick="event.stopPropagation(); stockDelete.ask({{ $s->id }}, '{{ addslashes($s->name) }}');">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-boxes d-block mb-2" style="font-size:2rem;"></i>No consumable stocks yet — click “Add Consumable Stock”.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ─── TAB 2 · PENDING ─── --}}
        <div class="tab-pane fade" id="pending-panel">
            @include('admin.partials.issuance-pending-panel')
        </div>

        {{-- ─── TAB 3 · HISTORY WORKBENCH ─── --}}
        <div class="tab-pane fade" id="history-panel">
            <form method="GET" id="historyFilters" class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <input type="hidden" name="status" id="fStatus" value="{{ request('status') }}">
                <div class="chip-group" role="group">
                    <a class="chip-link {{ empty(request('status')) ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => null, 'history_page' => null]) }}">All</a>
                    <a class="chip-link {{ request('status') === 'confirmed' ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => 'confirmed', 'history_page' => null]) }}">Confirmed</a>
                    <a class="chip-link {{ request('status') === 'cancelled' ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'history_page' => null]) }}">Cancelled</a>
                </div>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm theme-dynamic-input" style="width:auto;height:38px;border-radius:var(--radius-md,10px);" title="From date">
                <span class="text-secondary small">→</span>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm theme-dynamic-input" style="width:auto;height:38px;border-radius:var(--radius-md,10px);" title="To date">
                <select name="per_page" class="form-select form-select-sm theme-dynamic-input" style="width:auto;height:38px;border-radius:var(--radius-md,10px);" title="Rows per page" onchange="this.form.submit()">
                    @foreach([10,25,50,100] as $pp)
                        <option value="{{ $pp }}" {{ request('per_page', 10) == $pp ? 'selected' : '' }}>{{ $pp }} / page</option>
                    @endforeach
                </select>
                <div class="search-mini ms-auto">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search borrower, item, purpose…" autocomplete="off" id="histSearch">
                </div>
                <a class="tb-like-btn" id="exportCsvBtn" href="#" title="Export current filtered view"><i class="bi bi-filetype-csv"></i> CSV</a>
            </form>

            <table class="admin-table w-100" id="historyTable" data-drawer-rows data-drawer-renderer="renderHistoryDrawer">
                <thead><tr><th>#</th><th>Borrower</th><th>Item</th><th class="text-center">Qty</th><th>Purpose</th><th>Issued By</th><th>Status</th><th>Completed</th></tr></thead>
                <tbody>
                    @forelse($history as $i => $h)
                    @php
                        $hDrawer = [
                            'borrower'  => $h->user?->name ?? 'Unknown',
                            'item'      => $h->item?->name ?? 'Unknown',
                            'qty'       => $h->quantity,
                            'unit'      => $h->item?->unit,
                            'purpose'   => $h->purpose,
                            'issued_by' => $h->issuer?->name ?? '',
                            'status'    => ucfirst($h->status),
                            'when'      => optional($h->updated_at)->format('M d, Y h:i A'),
                        ];
                    @endphp
                    <tr data-history='@json($hDrawer)'>
                        <td class="text-secondary">{{ $history->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $h->user?->name ?? 'Unknown' }}</td>
                        <td>{{ $h->item?->name ?? 'Unknown' }}</td>
                        <td class="text-center fw-bold">{{ $h->quantity }}</td>
                        <td class="text-truncate" style="max-width:200px;" title="{{ $h->purpose }}">{{ Str::limit($h->purpose, 50) }}</td>
                        <td class="text-secondary">{{ $h->issuer?->name ?? '—' }}</td>
                        <td>
                            @if($h->status === 'confirmed')
                                <span class="badge rounded-pill fw-bold" style="background:#ecfdf5;color:#10b981;">Received</span>
                            @else
                                <span class="badge rounded-pill fw-bold" style="background:#fef2f2;color:#dc2626;">Cancelled</span>
                            @endif
                        </td>
                        <td class="text-secondary small">{{ optional($h->updated_at)->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-secondary">No issuance records match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($history->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-secondary small">Showing {{ $history->firstItem() }}–{{ $history->lastItem() }} of {{ $history->total() }}</span>
                    {{ $history->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══ MODALS ═══ --}}

{{-- Create / Edit stock (merged lifecycle modal) --}}
<div class="modal fade" id="stockModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:14px;border:1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="stockModalTitle">Add Consumable Stock</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter:var(--thumb-invert,none);"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <input type="hidden" id="sm_id">
                <label class="form-label fw-bold small text-secondary">ITEM NAME *</label>
                <input type="text" class="form-control mb-3 theme-dynamic-input" id="sm_name" maxlength="255">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-bold small text-secondary">UNIT *</label>
                        <select class="form-select theme-dynamic-input" id="sm_unit">
                            @foreach(['pcs','box','pack','ream','bottle','roll','set'] as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-secondary" id="sm_qtyLabel">QUANTITY *</label>
                        <input type="number" class="form-control theme-dynamic-input" id="sm_qty" min="1" value="1">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small text-secondary">MINIMUM STOCK ALERT LEVEL</label>
                    <input type="number" class="form-control theme-dynamic-input" id="sm_min" min="0" placeholder="e.g., 5">
                    <div class="hint">Alert fires when stock falls to or below this number.</div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small text-secondary">NOTES</label>
                    <input type="text" class="form-control theme-dynamic-input" id="sm_notes" maxlength="500">
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold px-4" id="sm_saveBtn"><i class="bi bi-save me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Quick replenish --}}
<div class="modal fade" id="replenishModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
            <div class="modal-body text-center py-4">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:50%;background:var(--success-soft,#ecfdf5);color:var(--success,#10b981);"><i class="bi bi-plus-circle fs-4"></i></div>
                <h6 class="fw-bold mb-1">Replenish "<span id="rp_name"></span>"</h6>
                <p class="text-secondary small mb-3">Current: <strong id="rp_current"></strong> <span id="rp_unit"></span></p>
                <input type="number" class="form-control text-center fw-bold mb-2 theme-dynamic-input" id="rp_qty" min="1" value="10" style="font-size:18px;border-radius:var(--radius-md,10px);">
                <input type="text" class="form-control form-control-sm theme-dynamic-input" id="rp_notes" placeholder="Note (optional): delivery, PO#, …" maxlength="200" style="border-radius:var(--radius-md,10px);">
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:var(--radius-md,10px);">Cancel</button>
                <button type="button" class="btn btn-iss-emerald fw-bold px-4" id="rp_go" style="border-radius:var(--radius-md,10px);"><i class="bi bi-plus-lg me-1"></i>Add</button>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Fulfill Modal (Issue & Deduct) --}}
<div class="modal fade" id="confirmFulfillModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-lg, 0 10px 30px rgba(0,0,0,0.18));">
            <div class="modal-body text-center pt-4 px-4">
                <div style="width:52px;height:52px;border-radius:50%;background:var(--success-soft, rgba(16,185,129,0.12));color:var(--success,#10b981);display:inline-flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 12px auto;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <h6 class="fw-bold mb-1" style="font-size:16px;">Issue this request and deduct stock now?</h6>
                <p class="text-secondary small mb-3">This action will immediately decrement stock from inventory and notify the borrower.</p>
                <div class="p-3 mb-3 text-start" style="background:var(--bg-main);border-radius:var(--radius-md,10px);border:1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="text-secondary">Borrower:</span>
                        <strong id="cfm_user" class="text-end text-truncate ms-2" style="max-width:220px;"></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="text-secondary">Item:</span>
                        <strong id="cfm_item" class="text-end text-truncate ms-2" style="max-width:220px;"></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-secondary">Quantity to Deduct:</span>
                        <strong id="cfm_qty" class="text-success fw-bold"></strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:var(--radius-md,10px);">Cancel</button>
                <button type="button" class="btn btn-success fw-bold px-4" id="cfm_go" style="border-radius:var(--radius-md,10px);">
                    <i class="bi bi-check-circle-fill me-1"></i>Issue &amp; Deduct
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Cancel-with-reason (centered like logout) --}}
<div class="modal fade" id="cancelReasonModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
            <div class="modal-body text-center pt-4 px-4">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:50%;background:var(--danger-soft, rgba(220,38,38,0.12));color:var(--danger,#dc2626);">
                    <i class="bi bi-x-octagon-fill fs-3"></i>
                </div>
                <h6 class="fw-bold mb-1" id="cr_title">Cancel this issuance?</h6>
                <p class="text-secondary small mb-3" id="cr_label"></p>
                <textarea class="form-control theme-dynamic-input" id="cr_reason" rows="2" maxlength="300"
                          placeholder="Reason (shared with the borrower)…" style="border-radius:var(--radius-md,10px);"></textarea>
                <div class="small text-secondary mt-2" id="cr_note">Issued items will have their stock restored automatically.</div>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:var(--radius-md,10px);">Keep it</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="cr_go" style="border-radius:var(--radius-md,10px);"><i class="bi bi-x-lg me-1"></i>Cancel Issuance</button>
            </div>
        </div>
    </div>
</div>

{{-- Delete stock confirm (centered) --}}
<div class="modal fade" id="deleteStockModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
            <div class="modal-body text-center pt-4 px-4">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:50%;background:var(--danger-soft, rgba(220,38,38,0.12));color:var(--danger,#dc2626);">
                    <i class="bi bi-trash3-fill fs-3"></i>
                </div>
                <h6 class="fw-bold mb-1">Delete "<span id="ds_name"></span>"?</h6>
                <p class="text-secondary small mb-0">Items with issuance history cannot be deleted.</p>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:var(--radius-md,10px);">Keep</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="ds_go" style="border-radius:var(--radius-md,10px);">Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- Import CSV modal --}}
<div class="modal fade" id="importCsvModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up-fill text-success me-2"></i>Import Consumable Stocks from CSV</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter:var(--thumb-invert,none);"></button>
            </div>
            <div class="modal-body px-4 pb-3">
                <p class="text-secondary small mb-3">Upload a CSV file with columns: <strong>Item Name, Quantity, Unit, Min Stock, Reorder Level, Notes</strong>. Optional: Category, Supplier, Location, Cost, Date.</p>
                <form id="importCsvForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control theme-dynamic-input" id="importCsvFile" name="file" accept=".csv,.txt" required style="border-radius:var(--radius-md,10px);">
                        <div class="form-text">Columns: Item Name*, Quantity, Unit, Min Stock, Reorder Level, Notes</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:var(--radius-md,10px);">Cancel</button>
                <button type="button" class="btn btn-success fw-bold px-4" id="importCsvGo" style="border-radius:var(--radius-md,10px);"><i class="bi bi-upload me-1"></i> Import</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="stockHistoryModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i>Stock History — <span id="sh_name"></span></h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter:var(--thumb-invert,none);"></button>
            </div>
            <div class="modal-body px-4 py-4" id="sh_body">
                <div class="text-center py-5"><div class="spinner-border" style="color:var(--accent-color,var(--accent-blue,#1b3550));"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Single issue modal --}}
<div class="modal fade" id="issueConsumableModal" data-centered tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-up-right me-2"></i>Issue Consumable</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter:var(--thumb-invert,none);"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="im_item_id">
                <div class="p-3 mb-3 rounded-3" style="background:var(--bg-main);border:1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong id="im_name" style="color:var(--text-primary);"></strong>
                        <span class="fw-bold" id="im_stock" style="color:#10b981;"></span>
                    </div>
                    <div class="text-secondary small mt-1">Stock deducted immediately · Borrower must confirm receipt.</div>
                </div>
                <label class="form-label fw-bold small text-secondary">QUANTITY *</label>
                <input type="number" class="form-control mb-3 theme-dynamic-input" id="im_qty" min="1" value="1" style="border-radius:var(--radius-md,10px);">
                <label class="form-label fw-bold small text-secondary">BORROWER *</label>
                <div id="singleRecipientPicker"></div>
                <label class="form-label fw-bold small text-secondary mt-3">PURPOSE *</label>
                <div class="purpose-chips" data-target="im_purpose"></div>
                <input type="hidden" id="im_purpose">
                <input type="text" class="form-control form-control-sm mt-2 d-none theme-dynamic-input" id="im_purpose_other" placeholder="Specify purpose…" maxlength="500" style="border-radius:var(--radius-md,10px);">
                <label class="form-label fw-bold small text-secondary mt-3">NOTES</label>
                <input type="text" class="form-control theme-dynamic-input" id="im_notes" maxlength="500" style="border-radius:var(--radius-md,10px);">
            </div>
            <div class="modal-footer border-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:var(--radius-md,10px);">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold px-4" id="im_go" style="border-radius:var(--radius-md,10px);"><i class="bi bi-send-fill me-1"></i>Issue</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Issuance Design System (DM Sans, Deep Navy & Theme Responsive) ── */
.kpi-pill {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg, 14px);
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    height: 100%;
}
.kpi-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 24, 40, 0.08);
    border-color: var(--line-strong);
}
[data-theme="dark"] .kpi-pill:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
}
.kpi-icon {
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: var(--radius-md, 10px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--kpi-bg, var(--bg-main));
    color: var(--kpi, var(--text-primary));
    font-size: 18px;
    transition: transform 0.15s ease;
}
.kpi-pill:hover .kpi-icon {
    transform: scale(1.06);
}
.kpi-val {
    font-size: 20px;
    font-weight: 800;
    line-height: 1.1;
    color: var(--text-primary);
    letter-spacing: -0.02em;
}
.kpi-lbl {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-top: 2px;
}

/* Tab Navigation */
#issTabs {
    border-bottom: 1px solid var(--border-color) !important;
    gap: 6px;
    padding-bottom: 6px;
}
#issTabs .nav-link {
    color: var(--text-secondary);
    border-radius: var(--radius-md, 10px);
    padding: 8px 16px;
    font-weight: 700;
    font-size: 13px;
    border: 1px solid transparent;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    background: transparent;
}
#issTabs .nav-link:hover {
    color: var(--text-primary);
    background: var(--bg-surface-hover);
}
#issTabs .nav-link.active {
    color: var(--btn-text, #ffffff) !important;
    background: var(--accent-blue) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
#issTabs .nav-link.active .badge {
    background: #ffffff !important;
    color: var(--navy-900, #14273a) !important;
}

/* Toolbar Buttons */
.btn-iss-emerald {
    background-color: #10b981 !important;
    border: 1px solid #059669 !important;
    color: #ffffff !important;
    border-radius: var(--radius-md, 10px) !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    padding: 0 16px !important;
    height: 38px !important;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-iss-emerald:hover {
    background-color: #059669 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}
.btn-iss-outline {
    background-color: var(--bg-surface) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--text-primary) !important;
    border-radius: var(--radius-md, 10px) !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    padding: 0 14px !important;
    height: 38px !important;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-iss-outline:hover {
    background-color: var(--bg-surface-hover) !important;
    border-color: var(--line-strong) !important;
    color: var(--text-primary) !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* Filter Chips & Groups */
.chip-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--border-color);
    background: var(--bg-surface);
    color: var(--text-secondary);
    font-size: 12.5px;
    font-weight: 700;
    padding: 0 14px;
    height: 38px;
    border-radius: 99px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.chip-btn:hover {
    background: var(--bg-surface-hover);
    color: var(--text-primary);
    border-color: var(--line-strong);
}
.chip-btn.on {
    background: var(--danger-soft, #fef2f2);
    border-color: var(--danger, #dc2626);
    color: var(--danger, #dc2626);
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.15);
}
.chip-group {
    display: inline-flex;
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 10px);
    overflow: hidden;
    height: 38px;
    align-items: center;
}
.chip-link {
    display: inline-flex;
    align-items: center;
    height: 100%;
    padding: 0 14px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-secondary);
    text-decoration: none;
    border-right: 1px solid var(--border-color);
    transition: background 0.12s ease, color 0.12s ease;
}
.chip-link:last-child {
    border-right: none;
}
.chip-link:hover {
    background: var(--bg-surface-hover);
    color: var(--text-primary);
}
.chip-link.on {
    background: var(--accent-blue);
    color: var(--btn-text, #fff);
}

/* Search Bar */
.search-mini {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.search-mini i.bi-search {
    position: absolute;
    left: 12px;
    color: var(--text-secondary);
    font-size: 13px;
    pointer-events: none;
}
.search-mini input {
    height: 38px;
    padding: 6px 32px 6px 34px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 10px);
    background: var(--search-bg, var(--bg-surface));
    color: var(--text-primary);
    font-size: 13px;
    font-family: inherit;
    width: 220px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, width 0.2s ease;
}
.search-mini input:focus {
    outline: none;
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 3px var(--focus-ring);
    width: 260px;
}
.search-mini #clearStockSearch {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
}

/* Stocks Table & Actions */
.stock-figure {
    font-weight: 800;
    font-size: 14.5px;
    color: var(--text-primary);
    margin-right: 4px;
}
.quick-add-btn {
    border: 1px solid var(--border-color);
    background: var(--bg-surface);
    color: var(--accent-blue);
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    cursor: pointer;
    vertical-align: middle;
    transition: all 0.15s ease;
}
.quick-add-btn:hover {
    background: var(--accent-blue-bg);
    border-color: var(--accent-blue);
    color: var(--accent-blue);
    transform: scale(1.1);
}

.act-btn {
    border: 1px solid var(--border-color);
    background: var(--bg-surface);
    color: var(--text-secondary);
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm, 8px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: var(--shadow-xs, 0 1px 2px rgba(0, 0, 0, 0.03));
}
.act-btn:hover:not(:disabled) {
    background: var(--accent-blue-bg);
    color: var(--accent-blue);
    border-color: var(--accent-blue);
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
.act-btn.act-history:hover:not(:disabled) {
    background: var(--bg-surface-hover);
    color: var(--text-primary);
    border-color: var(--line-strong);
}
.act-btn.act-edit:hover:not(:disabled) {
    background: var(--warning-soft);
    color: var(--warning);
    border-color: var(--warning);
}
.act-btn.act-danger:hover:not(:disabled) {
    background: var(--danger-soft);
    color: var(--danger);
    border-color: var(--danger);
}
.act-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
    background: var(--bg-main) !important;
    border-color: var(--border-color) !important;
    color: var(--text-secondary) !important;
    transform: none !important;
    box-shadow: none !important;
}

.tb-like-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--border-color);
    background: var(--bg-surface);
    color: var(--text-primary);
    font-size: 12.5px;
    font-weight: 700;
    padding: 0 14px;
    height: 38px;
    border-radius: var(--radius-md, 10px);
    text-decoration: none;
    transition: all 0.15s ease;
}
.tb-like-btn:hover {
    background: var(--bg-surface-hover);
    border-color: var(--line-strong);
    color: var(--text-primary);
    transform: translateY(-1px);
}

.purpose-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.purpose-chips .pc {
    border: 1px solid var(--border-color);
    background: var(--bg-main);
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 99px;
    cursor: pointer;
    transition: all 0.12s ease;
}
.purpose-chips .pc:hover {
    border-color: var(--accent-blue);
    color: var(--text-primary);
}
.purpose-chips .pc.on {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
    color: var(--btn-text, #fff);
}

.bp-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 90px 36px;
    gap: 8px;
    align-items: center;
    padding: 10px 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 10px);
    background: var(--bg-main);
    transition: border-color 0.15s ease;
}
.bp-row:hover {
    border-color: var(--accent-blue);
}
.bp-row.over {
    border-color: var(--danger);
    background: var(--danger-soft);
}

.sh-tl {
    position: relative;
    padding-left: 26px;
}
.sh-tl::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: var(--border-color);
}
.sh-item {
    position: relative;
    padding-bottom: 16px;
}
.sh-item::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--bg-surface);
    border: 2px solid var(--text-secondary);
}
.sh-item.in::before { border-color: var(--success); }
.sh-item.out::before { border-color: var(--warning); }
.sh-item.restore::before { border-color: var(--accent-blue); }

.delta-badge {
    font-family: ui-monospace, Menlo, monospace;
    font-weight: 800;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 6px;
}
.delta-badge.plus {
    background: var(--success-soft);
    color: var(--success);
}
.delta-badge.minus {
    background: var(--warning-soft);
    color: var(--warning);
}
.hint {
    font-size: 11px;
    color: var(--text-secondary);
    margin-top: 4px;
}
</style>

<script>
(function () {
'use strict';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const $ = (id) => document.getElementById(id);
const esc = window.escapeHtml || ((t) => { const d=document.createElement('div'); d.textContent=t; return d.innerHTML; });
if (typeof window.showToast !== 'function') {
    window.showToast = function(title, msg, type){ 
        // Fallback toast using native alert + console, will be replaced once admin.js loads
        console.log('['+type+'] '+title+': '+msg);
        const c = document.createElement('div');
        c.style.cssText='position:fixed;top:20px;right:20px;z-index:99999;background:'+(type==='success'?'#10b981':type==='error'?'#ef4444':'var(--accent-color, #1b3550)')+';color:#fff;padding:12px 18px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.15);font-weight:600;font-size:13px;';
        c.textContent = title + ': ' + msg;
        document.body.appendChild(c); setTimeout(()=>c.remove(), 4000);
    };
}
window.__ISS = { CSRF, $, esc };

/* Tabs: hash persistence — bound ONCE (fixes listener accumulation bug) + localStorage fallback for form submits */
function activateHashTab() {
    let h = location.hash.replace('#', '');
    if (!h) {
        const last = localStorage.getItem('issuance_lastTab');
        if (last && ['stocks','pending','history'].includes(last)) h = last;
    }
    const map = { stocks:'#stocks-panel', pending:'#pending-panel', history:'#history-panel' };
    if (map[h]) {
        const btn = document.querySelector('#issTabs [data-bs-target="' + map[h] + '"]');
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    }
}
document.addEventListener('DOMContentLoaded', () => {
    activateHashTab();
    document.querySelectorAll('#issTabs [data-bs-target]').forEach(b =>
        b.addEventListener('shown.bs.tab', () => {
            const tab = b.getAttribute('data-bs-target').replace('-panel','').replace('#','');
            history.replaceState(null, '', '#' + tab);
            localStorage.setItem('issuance_lastTab', tab);
        }));
    // Preserve history tab through GET filter submits
    const histForm = $('historyFilters');
    if (histForm) {
        histForm.addEventListener('submit', () => {
            localStorage.setItem('issuance_lastTab', 'history');
        });
    }
});
window.addEventListener('hashchange', activateHashTab);

/* KPI pills navigate */
document.querySelectorAll('.kpi-pill[data-go-tab]').forEach(p => {
    p.addEventListener('click', () => {
        const t = p.dataset.goTab;
        const btn = document.querySelector('#issTabs [data-bs-target="#' + t + '-panel"]');
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
        if (t === 'stocks' && p.hasAttribute('data-toggle-lowstock')) $('lowStockChip').click();
    });
});

/* Stocks: client filter + low-stock chip */
let lowOnly = false;
function applyStockFilter() {
    const q = ($('stockSearch')?.value || '').trim().toLowerCase();
    document.querySelectorAll('#stocksTable tbody tr[data-id]').forEach(tr => {
        const okQ = !q || tr.dataset.name.includes(q);
        const okL = !lowOnly || tr.dataset.low === '1';
        tr.style.display = (okQ && okL) ? '' : 'none';
    });
    const clearBtn = $('clearStockSearch');
    if (clearBtn) clearBtn.classList.toggle('d-none', !q);
}
$('lowStockChip')?.addEventListener('click', () => {
    lowOnly = !lowOnly;
    $('lowStockChip').classList.toggle('on', lowOnly);
    applyStockFilter();
});
$('stockSearch')?.addEventListener('input', applyStockFilter);
$('clearStockSearch')?.addEventListener('click', () => { $('stockSearch').value=''; applyStockFilter(); $('stockSearch').focus(); });
$('btnScanIssue')?.addEventListener('click', () => {
    $('stockSearch')?.focus();
    if (typeof showToast === 'function') showToast('Scanner', 'Scan a barcode to filter stocks — camera scan coming soon.', 'info');
});

/* Purpose chips binder */
const PURPOSES = ['Classroom Instruction','Office / Admin Use','Event / Program','Repair / Maintenance','Cleaning / Janitorial','Other'];
document.querySelectorAll('.purpose-chips').forEach(box => {
    const target = box.dataset.target;
    PURPOSES.forEach(p => {
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'pc'; b.textContent = p;
        b.addEventListener('click', () => {
            box.querySelectorAll('.pc').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            if (p === 'Other') { $(target).value=''; $(target+'_other')?.classList.remove('d-none'); $(target+'_other')?.focus(); }
            else { $(target).value = p; $(target+'_other')?.classList.add('d-none'); }
        });
        box.appendChild(b);
    });
});
['im_purpose_other','bulkPurpose_other'].forEach(id => {
    const el = $(id);
    if (el) el.addEventListener('input', function () { const target = id.replace('_other',''); const t = $(target); if (t) t.value = this.value; });
});

/* Borrower picker — ONE implementation, mounted twice */
    window.BORROWERS = @json($users->map(fn($u) => ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email]));
    window.mountBorrowerPicker = function(mountId, hiddenId) {
        const wrap = $(mountId);
        if (!wrap) return null;
        wrap.innerHTML =
            '<input class="form-control theme-dynamic-input bp-search" placeholder="Type name…">' +
            '<input type="hidden" id="' + hiddenId + '">' +
            '<div class="bp-list d-none" style="max-height:180px;overflow:auto;border:1px solid var(--border-color);border-radius:10px;margin-top:5px;background:var(--bg-surface);"></div>';
        const input = wrap.querySelector('.bp-search'), list = wrap.querySelector('.bp-list'),
              hidden = wrap.querySelector('input[type=hidden]');
        input.addEventListener('input', function() {
            hidden.value = '';
            const q = input.value.trim().toLowerCase();
            if (!q) { list.classList.add('d-none'); return; }
            list.innerHTML = window.BORROWERS.filter(function(u) { return u.name.toLowerCase().includes(q); }).slice(0,8).map(function(u) {
                return '<div class="bp-opt px-3 py-2" data-id="'+u.id+'" data-name="'+esc(u.name)+'" style="cursor:pointer;border-bottom:1px solid var(--border-color);">'
                    + '<div class="fw-bold" style="font-size:12.5px;">'+esc(u.name)+'</div>'
                    + '<div class="text-secondary" style="font-size:11px;">'+esc(u.email||'')+'</div></div>';
            }).join('')
                || '<div class="px-3 py-2 text-secondary" style="font-size:12px;">No match</div>';
            list.classList.remove('d-none');
            list.querySelectorAll('.bp-opt').forEach(function(o) { o.addEventListener('mousedown', function() {
                hidden.value = o.dataset.id; input.value = o.dataset.name; list.classList.add('d-none');
            }); });
        });
        input.addEventListener('blur', function() { setTimeout(function() { list.classList.add('d-none'); }, 150); });
        return hidden;
    };
    const singleRecipientHidden = window.mountBorrowerPicker('singleRecipientPicker','im_user_id');

/* Single issue modal */
window.issueForm = {
    open(stockId, name, stock, unit) {
        $('im_item_id').value = stockId;
        $('im_name').textContent = name;
        $('im_stock').textContent = stock + ' ' + unit + ' available';
        $('im_qty').max = stock; $('im_qty').value = 1;
        if (singleRecipientHidden) singleRecipientHidden.value = '';
        const s = document.querySelector('#singleRecipientPicker .bp-search'); if (s) s.value='';
        new bootstrap.Modal($('issueConsumableModal')).show();
    },
};
$('im_go').addEventListener('click', async function () {
    const qty = parseInt($('im_qty').value), uid = singleRecipientHidden ? singleRecipientHidden.value : $('im_user_id')?.value, purpose = $('im_purpose').value;
    if (!(qty > 0)) return showToast('Validation','Enter a quantity.','warning');
    if (!uid)       return showToast('Validation','Pick a borrower.','warning');
    if (!purpose)   return showToast('Validation','Pick or type a purpose.','warning');
    this.disabled = true; this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Issuing…';
    try {
        const res = await fetch('/admin/issuance/initiate', {
            method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({ item_id:$('im_item_id').value, user_id:uid, quantity:qty,
                                  purpose:purpose, admin_notes:$('im_notes').value }),
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Failed.');
        bootstrap.Modal.getInstance($('issueConsumableModal')).hide();
        showToast('Issued','Borrower must confirm receipt.','success');
        setTimeout(() => location.reload(), 700);
    } catch (e) {
        showToast('Error', e.message, 'error');
        this.disabled = false; this.innerHTML = '<i class="bi bi-send-fill me-1"></i>Issue';
    }
});

/* Fulfill with dynamic confirmation modal */
let _fulfillTargetId = null;
let _fulfillTriggerBtn = null;
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-fulfill-btn');
    if (!btn) return;
    _fulfillTargetId = btn.dataset.id;
    _fulfillTriggerBtn = btn;
    
    const userEl = $('cfm_user');
    const itemEl = $('cfm_item');
    const qtyEl = $('cfm_qty');
    
    if (userEl) userEl.textContent = btn.dataset.user || 'Unknown Borrower';
    if (itemEl) itemEl.textContent = btn.dataset.item || 'Unknown Item';
    if (qtyEl) qtyEl.textContent = (btn.dataset.qty || '1') + ' ' + (btn.dataset.unit || '');

    const modalEl = $('confirmFulfillModal');
    if (modalEl) {
        new bootstrap.Modal(modalEl).show();
    }
});

$('cfm_go')?.addEventListener('click', async function () {
    if (!_fulfillTargetId) return;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deducting Stock…';
    if (_fulfillTriggerBtn) {
        _fulfillTriggerBtn.disabled = true;
        _fulfillTriggerBtn.innerHTML = 'Processing…';
    }
    try {
        const res = await fetch('/admin/issuance/fulfill/' + _fulfillTargetId, {
            method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF},
        });
        const d = await res.json().catch(()=>({}));
        const modalEl = $('confirmFulfillModal');
        if (modalEl) {
            const inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide();
        }
        if (res.ok) {
            showToast('Fulfilled', 'Stock deducted and request issued.', 'success');
            setTimeout(() => location.reload(), 650);
        } else {
            showToast('Cannot fulfill', d.message || 'Failed.', 'error');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Issue &amp; Deduct';
            if (_fulfillTriggerBtn) {
                _fulfillTriggerBtn.disabled = false;
                _fulfillTriggerBtn.innerHTML = 'Issue &amp; Deduct';
            }
        }
    } catch(e) {
        showToast('Network error', '', 'error');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Issue &amp; Deduct';
        if (_fulfillTriggerBtn) {
            _fulfillTriggerBtn.disabled = false;
            _fulfillTriggerBtn.innerHTML = 'Issue &amp; Deduct';
        }
    }
});

/* Cancel-with-reason / Reject */
let _cancelTarget = null;
let _isRejectMode = false;
window.cancelReason = {
    open(id, label, isReject = false) {
        _cancelTarget = id;
        _isRejectMode = !!isReject;
        const lbl = $('cr_label'); if (lbl) lbl.textContent = label;
        const rsn = $('cr_reason'); if (rsn) rsn.value = '';
        
        const titleEl = $('cr_title');
        const noteEl = $('cr_note');
        const goBtn = $('cr_go');
        
        if (titleEl) titleEl.textContent = _isRejectMode ? 'Reject this request?' : 'Cancel this issuance?';
        if (noteEl) noteEl.textContent = _isRejectMode ? 'The borrower will be notified that this request was rejected.' : 'Issued items will have their stock restored automatically.';
        if (goBtn) goBtn.innerHTML = _isRejectMode ? '<i class="bi bi-x-circle-fill me-1"></i>Reject Request' : '<i class="bi bi-x-lg me-1"></i>Cancel Issuance';

        new bootstrap.Modal($('cancelReasonModal')).show();
    },
};
$('cr_go').addEventListener('click', async function () {
    if (!_cancelTarget) return;
    this.disabled = true;
    this.innerHTML = _isRejectMode ? 'Rejecting…' : 'Cancelling…';
    try {
        const res = await fetch('/admin/issuance/cancel/' + _cancelTarget, {
            method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({ reason:$('cr_reason').value.trim() }),
        });
        const d = await res.json().catch(()=>({}));
        bootstrap.Modal.getInstance($('cancelReasonModal')).hide();
        if (res.ok) {
            showToast(_isRejectMode ? 'Rejected' : 'Cancelled', _isRejectMode ? 'Request rejected and borrower notified.' : 'Stock restored where applicable.', 'success');
            setTimeout(()=>location.reload(), 650);
        }
        else showToast('Error', d.message || 'Failed.', 'error');
    } catch(e){ showToast('Network error','','error'); }
    this.disabled = false;
    this.innerHTML = _isRejectMode ? '<i class="bi bi-x-circle-fill me-1"></i>Reject Request' : '<i class="bi bi-x-lg me-1"></i>Cancel Issuance';
});
})();
</script>

<script>
(function () {
'use strict';
const CSRF = window.__ISS.CSRF;
const $ = window.__ISS.$;
const esc = window.__ISS.esc;

/* ═══ Stock create/edit (merged modal) ═══ */
window.stockForm = {
    open(editId, isEdit) {
        $('sm_id').value = isEdit ? editId : '';
        $('sm_qtyLabel').textContent = isEdit ? 'ADD QUANTITY (optional)' : 'QUANTITY *';
        $('sm_qty').required = !isEdit;
        if (isEdit) {
            const tr = document.querySelector('#stocksTable tr[data-id="' + editId + '"]');
            $('stockModalTitle').textContent = 'Edit Stock Item';
            $('sm_name').value = tr?.dataset.nameFull || '';
            $('sm_unit').value = tr?.dataset.unit || 'pcs';
            $('sm_min').value = tr?.dataset.min || '';
            $('sm_qty').value = ''; $('sm_notes').value = '';
        } else {
            $('stockModalTitle').textContent = 'Add Consumable Stock';
            ['sm_name','sm_min','sm_notes'].forEach(i => $(i).value = '');
            $('sm_qty').value = 1;
        }
        new bootstrap.Modal($('stockModal')).show();
    },
};
$('sm_saveBtn').addEventListener('click', async function () {
    const id = $('sm_id').value;
    const name = $('sm_name').value.trim(), unit = $('sm_unit').value, qty = parseInt($('sm_qty').value) || 0;
    if (!name) return showToast('Validation','Item name is required.','warning');
    if (!id && !(qty > 0)) return showToast('Validation','Quantity must be at least 1.','warning');

    this.disabled = true;
    try {
        let res;
        if (id) {
            res = await fetch('/admin/issuance/stocks/' + id, {
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
                body:JSON.stringify({ _method:'PUT', name:name, unit:unit,
                    min_stock:$('sm_min').value === '' ? null : parseInt($('sm_min').value),
                    notes:$('sm_notes').value,
                    add_quantity: qty > 0 ? qty : null }),
            });
        } else {
            res = await fetch('/admin/issuance/add-stock', {
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
                body:JSON.stringify({ item_name:name, quantity:qty, unit:unit,
                    min_stock:$('sm_min').value === '' ? null : parseInt($('sm_min').value),
                    notes:$('sm_notes').value }),
            });
        }
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Save failed.');
        bootstrap.Modal.getInstance($('stockModal')).hide();
        showToast('Saved', d.message || 'Stock saved.', 'success');
        setTimeout(() => location.reload(), 650);
    } catch (e) { showToast('Error', e.message, 'error'); }
    this.disabled = false;
});

/* ═══ Delete stock ═══ */
window.stockDelete = {
    ask(id, name) { $('ds_name').textContent = name; $('ds_go').dataset.id = id;
                     new bootstrap.Modal($('deleteStockModal')).show(); },
};
$('ds_go').addEventListener('click', async function () {
    this.disabled = true;
    try {
        const res = await fetch('/admin/issuance/stocks/' + this.dataset.id, {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({ _method:'DELETE' }),
        });
        const d = await res.json().catch(()=>({}));
        bootstrap.Modal.getInstance($('deleteStockModal')).hide();
        if (res.ok) { showToast('Deleted','Stock item removed.','success'); setTimeout(()=>location.reload(), 650); }
        else showToast('Cannot delete', d.message || 'Failed.', 'error');
    } catch(e){ showToast('Network error','','error'); }
    this.disabled = false;
});

/* ═══ ISS-2: Quick replenish ═══ */
window.quickReplenish = {
    open(tr) {
        $('rp_name').textContent = tr.dataset.nameFull;
        $('rp_current').textContent = tr.dataset.stock;
        $('rp_unit').textContent = tr.dataset.unit;
        $('rp_go').dataset.id = tr.dataset.id;
        $('rp_qty').value = Math.max(10, Math.ceil((parseInt(tr.dataset.min)||5)*2));
        new bootstrap.Modal($('replenishModal')).show();
        setTimeout(() => $('rp_qty').select(), 250);
    },
};
$('rp_go').addEventListener('click', async function () {
    const qty = parseInt($('rp_qty').value);
    if (!(qty > 0)) return;
    this.disabled = true;
    try {
        const res = await fetch('/admin/issuance/stocks/' + this.dataset.id + '/replenish', {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({ quantity:qty, notes:$('rp_notes').value.trim() }),
        });
        const d = await res.json();
        bootstrap.Modal.getInstance($('replenishModal')).hide();
        if (res.ok) {
            const fig = document.querySelector('.stock-figure[data-sid="' + this.dataset.id + '"]');
            if (fig) { fig.textContent = d.stock_quantity;
                const cell = fig.closest('td');
                cell.style.transition='background .25s'; cell.style.background='#ecfdf5';
                setTimeout(()=>{ cell.style.background=''; }, 900); }
            showToast('Replenished', d.message, 'success');
        } else showToast('Error', d.message || 'Failed.', 'error');
    } catch(e){ showToast('Network error','','error'); }
    this.disabled = false;
});

/* ═══ ISS-3: Bulk issue cart ═══ */
const STOCK_OPTIONS = @json($stocks->map(fn($s)=>['id'=>$s->id,'label'=>$s->name.' ('.$s->stock_quantity.' '.$s->unit.')','stock'=>$s->stock_quantity]));
window.bulk = {
    open() {
        const bodyHTML =
            '<div class="d-flex flex-column gap-3">' +
                '<div>' +
                    '<label class="form-label fw-bold small text-secondary" style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;">RECIPIENT</label>' +
                    '<div id="bulkRecipientPicker" style="position:relative;"></div>' +
                '</div>' +
                '<div>' +
                    '<label class="form-label fw-bold small text-secondary" style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;">PURPOSE</label>' +
                    '<div class="purpose-chips" data-target="bulkPurpose" id="bulkPurposeChips" style="min-height:40px;"></div>' +
                    '<input type="hidden" id="bulkPurpose">' +
                    '<input type="text" class="form-control form-control-sm mt-2 theme-dynamic-input d-none" id="bulkPurpose_other" placeholder="Specify purpose…" maxlength="500">' +
                '</div>' +
                '<div>' +
                    '<label class="form-label fw-bold small text-secondary" style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;">NOTES (optional)</label>' +
                    '<input type="text" class="form-control form-control-sm theme-dynamic-input" id="bulkNotes" placeholder="e.g., For Science Fair kit" maxlength="500">' +
                '</div>' +
                '<div>' +
                    '<div class="d-flex align-items-center justify-content-between mb-2">' +
                        '<label class="form-label fw-bold small text-secondary mb-0" style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;">LINE ITEMS</label>' +
                        '<button type="button" class="btn btn-sm btn-primary fw-bold" id="bulkAddRowBtnDrawer" style="border-radius:8px;padding:5px 14px;font-size:12px;">' +
                            '<i class="bi bi-plus-lg me-1"></i>Add Line' +
                        '</button>' +
                    '</div>' +
                    '<div id="bulkLines" class="d-flex flex-column gap-2"></div>' +
                '</div>' +
                '<div class="d-flex align-items-end gap-3 flex-wrap">' +
                    '<div class="form-check">' +
                        '<input class="form-check-input" type="checkbox" id="bulkPartial">' +
                        '<label class="form-check-label small" for="bulkPartial">Skip out-of-stock lines</label>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn btn-primary fw-bold px-4 mt-2" id="bulkSubmitBtnDrawer" style="border-radius:var(--radius-md,10px);width:100%;height:40px;">' +
                    '<i class="bi bi-send-fill me-1"></i> Issue Kit' +
                '</button>' +
                '<div class="mt-1" id="bulkSummary" style="color:var(--text-secondary);font-size:12px;"></div>' +
                '<div class="mt-1 small fw-semibold d-none" id="bulkWarn" style="color:#dc2626;font-size:12px;"></div>' +
            '</div>' +
            '<style>' +
                '.bp-row{' +
                    'display:grid;grid-template-columns:minmax(0,1fr) 90px 36px;gap:8px;align-items:center;' +
                    'padding:10px 12px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-main);' +
                    'transition:border-color .15s;' +
                '}' +
                '.bp-row:hover{border-color:var(--accent-blue);}' +
                '.bp-row.over{border-color:#fca5a5;background:rgba(239,68,68,.06);}' +
                '.bp-row .bp-item{flex:1;}' +
                '.bp-row .bp-qty{width:80px;}' +
                '.bp-row .bp-rm{background:transparent;border:none;color:var(--text-secondary);cursor:pointer;padding:4px;}' +
                '.bp-row .bp-rm:hover{color:#dc2626;}' +
                '.purpose-chips{display:flex;flex-wrap:wrap;gap:7px;min-height:40px;padding:4px 0;}' +
                '.purpose-chips .pc{border:1px solid var(--border-color);background:var(--bg-main);color:var(--text-secondary);' +
                    'font-size:12px;font-weight:600;padding:6px 14px;border-radius:99px;cursor:pointer;transition:.12s;}' +
                '.purpose-chips .pc:hover{border-color:var(--accent-blue);}' +
                '.purpose-chips .pc.on{background:var(--accent-blue);border-color:var(--accent-blue);color:var(--btn-text,#fff);}' +
                '.bp-search{width:100%;}' +
                '.bp-list{max-height:180px;}' +
                '.bp-opt{cursor:pointer;border-bottom:1px solid var(--border-color);}' +
                '.bp-opt:hover{background:var(--bg-surface-hover,var(--bg-main));}' +
            '</style>';

        const footerHTML = [
            { label: '<i class="bi bi-x-lg me-1"></i> Close', class: 'btn btn-light', onClick: function(d) { d.close(); } }
        ];

        AppDrawer.show({
            overline: 'Bulk Issue',
            title: 'New Bulk Issue — Kit',
            body: bodyHTML,
            footer: footerHTML,
        });

        setTimeout(function() {
            if (!bulk.count()) bulk.addRow();
            if (typeof window.mountBorrowerPicker === 'function') window.mountBorrowerPicker('bulkRecipientPicker', 'bulk_user_id');
            const purposeBox = document.querySelector('.purpose-chips[data-target="bulkPurpose"]');
            if (purposeBox && purposeBox.children.length === 0) {
                const PURPOSES = ['Classroom Instruction','Office / Admin Use','Event / Program','Repair / Maintenance','Cleaning / Janitorial','Other'];
                PURPOSES.forEach(function(p) {
                    const b = document.createElement('button');
                    b.type = 'button'; b.className = 'pc'; b.textContent = p;
                    b.addEventListener('click', function() {
                        purposeBox.querySelectorAll('.pc').forEach(function(x) { x.classList.remove('on'); });
                        b.classList.add('on');
                        if (p === 'Other') {
                            document.getElementById('bulkPurpose').value = '';
                            document.getElementById('bulkPurpose_other')?.classList.remove('d-none');
                            document.getElementById('bulkPurpose_other')?.focus();
                        } else {
                            document.getElementById('bulkPurpose').value = p;
                            document.getElementById('bulkPurpose_other')?.classList.add('d-none');
                        }
                    });
                    purposeBox.appendChild(b);
                });
            }
            const addBtn = document.getElementById('bulkAddRowBtnDrawer');
            if (addBtn) addBtn.addEventListener('click', function() { bulk.addRow(); });
            const submitBtn = document.getElementById('bulkSubmitBtnDrawer');
            if (submitBtn) submitBtn.addEventListener('click', function() { bulk.submit(this); });
        }, 100);
    },
    close() { AppDrawer.close(); },
    count() { return document.querySelectorAll('#bulkLines .bp-row').length; },
    addRow() {
        const row = document.createElement('div');
        row.className = 'bp-row'; row.dataset.itemId = '';
        let opts = '<option value="">Select item…</option>';
        STOCK_OPTIONS.forEach(function(s) { opts += '<option value="'+s.id+'" data-stock="'+s.stock+'">'+esc(s.label)+'</option>'; });
        row.innerHTML =
            '<select class="form-select form-select-sm theme-dynamic-input bp-item">'+opts+'</select>' +
            '<input type="number" class="form-control form-control-sm bp-qty" min="1" value="1">' +
            '<button type="button" class="btn btn-sm btn-light border bp-rm"><i class="bi bi-x-lg"></i></button>';
        const sel = row.querySelector('.bp-item'), q = row.querySelector('.bp-qty');
        sel.addEventListener('change', function() { row.dataset.itemId = sel.value; bulk.validate(); });
        q.addEventListener('input', function() { bulk.validate(); });
        row.querySelector('.bp-rm').addEventListener('click', function() { row.remove(); bulk.validate(); });
        const bulkLines = document.getElementById('bulkLines');
        if (bulkLines) {
            bulkLines.appendChild(row);
            bulk.validate();
        }
    },
    collect() {
        const lines = [];
        document.querySelectorAll('#bulkLines .bp-row').forEach(function(row) {
            const itemId = parseInt(row.dataset.itemId), qty = parseInt(row.querySelector('.bp-qty').value) || 0;
            if (itemId && qty > 0) lines.push({ item_id:itemId, quantity:qty });
        });
        return lines;
    },
    validate() {
        const uid = document.querySelector('#bulkRecipientPicker input[type=hidden]')?.value;
        const purpose = $('bulkPurpose')?.value || '';
        let over = 0, valid = 0;
        document.querySelectorAll('#bulkLines .bp-row').forEach(function(row) {
            row.classList.remove('over');
            const opt = row.querySelector('.bp-item').selectedOptions[0];
            if (!opt || !opt.value) return;
            const max = +opt.dataset.stock;
            const q = parseInt(row.querySelector('.bp-qty').value) || 0;
            if (q > max) { over++; row.classList.add('over'); }
            else if (q > 0) valid++;
        });
        const warn = $('bulkWarn');
        if (warn) warn.classList.toggle('d-none', over === 0);
        if (over) warn.textContent = over + ' line(s) exceed available stock.';
        const summary = $('bulkSummary');
        if (summary) {
            summary.textContent = bulk.count() + ' line(s) · ' + valid + ' ready'
                + (uid ? '' : ' · pick a recipient')
                + (purpose ? '' : ' · pick a purpose');
        }
        return { uid:uid, purpose:purpose, over:over, valid:valid };
    },
    async submit(btn) {
        const st = bulk.validate();
        if (!st.uid) return showToast('Validation','Pick a recipient.','warning');
        if (!st.purpose) return showToast('Validation','Pick or type a purpose.','warning');
        if (!st.valid) return showToast('Validation','Add at least one item line.','warning');
        if (st.over && !$('bulkPartial')?.checked)
            return showToast('Over stock','Reduce quantities or enable "skip out-of-stock lines".','error');

        const lines = [];
        document.querySelectorAll('#bulkLines .bp-row').forEach(function(row) {
            const itemId = parseInt(row.dataset.itemId), q = parseInt(row.querySelector('.bp-qty').value)||0;
            const opt = row.querySelector('.bp-item').selectedOptions[0];
            const max = opt ? +opt.dataset.stock : 0;
            if (itemId && q > 0 && ($('bulkPartial')?.checked ? true : q <= max))
                lines.push({ item_id:itemId, quantity:Math.min(q, max) });
        });

        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Issuing…';
        try {
            const res = await fetch('/admin/issuance/bulk-issue', {
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
                body:JSON.stringify({
                    user_id:st.uid, purpose:$('bulkPurpose')?.value,
                    admin_notes:$('bulkNotes')?.value?.trim(), partial_ok:$('bulkPartial')?.checked,
                    lines:lines,
                }),
            });
            const d = await res.json();
            if (!res.ok) throw new Error(d.message || 'Bulk issue failed.');
            showToast('Kit issued', d.message, 'success');
            setTimeout(function() { AppDrawer.close(); location.reload(); }, 800);
        } catch (e) { showToast('Error', e.message, 'error'); }
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Issue Kit';
    },
};
const bulk = window.bulk;
$('tbBulkOpen')?.addEventListener('click', () => bulk.open());

/* ═══ History workbench ═══ */
let histTimer = null;
$('histSearch')?.addEventListener('input', function(){
    clearTimeout(histTimer);
    histTimer = setTimeout(() => $('historyFilters').submit(), 800);
});
['date_from','date_to'].forEach(id => {
    const el = $(id);
    if (el) el.addEventListener('change', () => $('historyFilters').submit());
});
$('exportCsvBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    const params = new URLSearchParams(new FormData($('historyFilters'))).toString();
    window.location.href = '/admin/issuance/history/export?' + params;
});
/* focus preservation after reload */
const s = $('histSearch');
if (s.value) { s.focus(); s.setSelectionRange(s.value.length, s.value.length); }


/* ═══ Stock history drawer/timeline (fixed payload: movements + delta + balance) ═══ */
window.showStockHistory = function(id, name) {
    $('sh_name').textContent = name;
    $('sh_body').innerHTML = '<div class="text-center py-5"><div class="spinner-border" style="color:var(--accent-blue);"></div></div>';
    new bootstrap.Modal($('stockHistoryModal')).show();

    fetch('/admin/issuance/stock-history/' + id, { headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF} })
        .then(r => r.json())
        .then(d => {
            const fmt = ts => ts ? new Date(ts).toLocaleString('en-PH',{dateStyle:'medium',timeStyle:'short'}) : '';
            let html =
                '<div class="d-flex justify-content-between align-items-center px-3 py-2 mb-3 rounded-3" '
              + 'style="background:var(--bg-main);border:1px solid var(--border-color);">'
              + '<span style="font-size:12.5px;color:var(--text-secondary);">Current balance</span>'
              + '<strong style="font-size:17px;">' + d.stock.current + ' ' + esc(d.stock.unit) + '</strong></div>';

            if (!d.movements.length) {
                html += '<p class="text-secondary text-center py-4">No stock movements recorded yet.</p>';
            } else {
                html += '<div class="sh-tl">';
                d.movements.forEach(m => {
                    const cls = m.type === 'in' ? 'in' : m.type === 'out' ? 'out' : 'restore';
                    const badge = typeof m.delta === 'number' && m.delta !== 0
                        ? '<span class="delta-badge ' + (m.delta > 0 ? 'plus">+' : 'minus">') + m.delta + '</span>' : '';
                    html += '<div class="sh-item ' + cls + '">'
                          +   '<div class="d-flex align-items-center gap-2 flex-wrap">'
                          +     '<strong style="font-size:12.5px;">' + esc(m.action_label) + '</strong>' + badge
                          +     (m.balance_after != null ? '<span class="text-secondary" style="font-size:11px;">bal: ' + m.balance_after + '</span>' : '')
                          +   '</div>'
                          +   '<div style="font-size:12px;color:var(--text-secondary);">' + esc(m.description || '') + '</div>'
                          +   '<div style="font-size:11px;color:var(--text-secondary);">' + fmt(m.timestamp) + ' · ' + esc(m.user_name) + '</div>'
                          + '</div>';
                });
                html += '</div>';
            }
            $('sh_body').innerHTML = html;
        })
        .catch(() => { $('sh_body').innerHTML = '<p class="text-danger text-center py-4">Failed to load history.</p>'; });
};

/* ═══ Drawer renderers ═══ */
window.renderStockDrawer = function(tr) {
    fetch('/admin/issuance/stock-history/' + tr.dataset.id, { headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF} })
        .then(r => r.json())
        .then(d => {
            let rows = '';
            (d.movements || []).slice(0, 6).forEach(m => {
                rows += '<tr><td style="padding:6px 8px;font-size:12px;">' + esc(m.action_label) + '</td>'
                      + '<td style="padding:6px 8px;font-size:12px;color:var(--text-secondary);">' + esc(m.user_name) + '</td></tr>';
            });
            AppDrawer.show({
                overline:'Stock overview',
                title:d.stock.name,
                body:
                    '<div class="d-flex justify-content-between px-3 py-2 mb-3 rounded-3" style="background:var(--bg-main);border:1px solid var(--border-color);">'
                  + '<span style="font-size:12px;color:var(--text-secondary);">Balance / minimum</span>'
                  + '<strong>' + d.stock.current + ' / ' + d.stock.min + ' ' + esc(d.stock.unit) + '</strong></div>'
                  + '<table class="w-100">' + (rows || '<tr><td class="text-secondary" colspan="2">No movements yet.</td></tr>') + '</table>',
                footer:[
                    { label:'<i class="bi bi-plus-lg me-1"></i> Add Stock', class:'btn btn-light',
                      onClick:function(){ AppDrawer.close(); stockForm.open(); } },
                    { label:'<i class="bi bi-box-arrow-up-right me-1"></i> Issue', class:'btn btn-primary',
                      onClick:function(){ AppDrawer.close();
                          const tr2 = document.querySelector('#stocksTable tr[data-id="' + d.stock.id + '"]');
                          if (tr2 && !tr2.querySelector('.act-btn[disabled]'))
                              issueForm.open(d.stock.id, d.stock.name, d.stock.current, d.stock.unit);
                          else showToast('Out of stock','', 'error'); } },
                ],
            });
        });
};
window.renderPendingRequestDrawer = function(tr) {
    const cells = Array.from(tr.children).map(c => c.textContent.trim());
    AppDrawer.show({ overline:'Borrower request', title:(cells[0]||''),
        body:'<dl style="margin:0;display:grid;gap:14px;">'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Item</dt><dd style="margin:0;font-weight:600;">'+esc(cells[1])+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Quantity</dt><dd style="margin:0;font-weight:600;">'+esc(cells[2])+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Purpose</dt><dd style="margin:0;font-weight:600;">'+esc(cells[3])+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Requested</dt><dd style="margin:0;font-weight:600;">'+esc(cells[4])+'</dd></div>'
           + '</dl>' });
};
window.renderHistoryDrawer = function(tr) {
    let d = {};
    try { d = JSON.parse(tr.dataset.history || '{}'); } catch(e){}
    AppDrawer.show({ overline:'Issuance record', title:(d.borrower||''),
        body:'<dl style="margin:0;display:grid;gap:14px;">'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Item</dt><dd style="margin:0;font-weight:600;">'+esc(d.item||'—')+' × '+esc(d.qty||'')+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Purpose</dt><dd style="margin:0;font-weight:600;">'+esc(d.purpose||'—')+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Status</dt><dd style="margin:0;font-weight:600;">'+esc(d.status||'—')+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Issued by</dt><dd style="margin:0;font-weight:600;">'+esc(d.issued_by||'—')+'</dd></div>'
           + '<div><dt style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);">Completed</dt><dd style="margin:0;font-weight:600;">'+esc(d.when||'—')+'</dd></div>'
           + '</dl>' });
};

/* ═══ Import CSV (vanilla JS) ═══ */
$('tbImportCsv')?.addEventListener('click', () => new bootstrap.Modal($('importCsvModal')).show());
$('importCsvGo')?.addEventListener('click', async function () {
    const file = $('importCsvFile')?.files[0];
    if (!file) return showToast('Validation', 'Please select a CSV file.', 'warning');
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing…';
    try {
        const fd = new FormData();
        fd.append('file', file);
        const res = await fetch('/admin/issuance/stocks/import', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: fd,
        });
        const d = await res.json();
        if (res.ok) {
            showToast('Imported', d.message, 'success');
            bootstrap.Modal.getInstance($('importCsvModal'))?.hide();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Import failed', d.message || 'Failed.', 'error');
        }
    } catch (e) {
        showToast('Network error', '', 'error');
    }
    this.disabled = false;
    this.innerHTML = '<i class="bi bi-upload me-1"></i> Import';
});

/* ═══ Real-time Dynamic Updates for Issuance Pending Panel ═══ */
let _lastPendingCount = {{ $pendingRequests->total() }};
let _lastConfirmCount = {{ $pendingConfirmations->total() }};
let _lastIssuanceId = '{{ $pendingRequests->max('id') ?? '' }}';
let _lastIssuanceUpdate = '';
let _isRefreshingPending = false;

window.addEventListener('baycis:realtime-update', async function(e) {
    const data = e.detail;
    if (!data || !data.counts) return;

    const pendingReqs = Number(data.counts.pending_issue_requests !== undefined ? data.counts.pending_issue_requests : (data.counts.pending_consumable_issuances ?? _lastPendingCount));
    const pendingConfirms = Number(data.counts.pending_confirmations ?? _lastConfirmCount);
    const latestIssuanceId = String(data.counts.latest_issuance_id ?? _lastIssuanceId);
    const latestIssuanceUpdate = String(data.counts.latest_issuance_updated_at ?? _lastIssuanceUpdate);

    // Update KPI counters
    const kpiReqs = document.getElementById('kpiPendingRequestsVal');
    if (kpiReqs) kpiReqs.textContent = pendingReqs;
    const kpiConf = document.getElementById('kpiPendingConfirmVal');
    if (kpiConf) kpiConf.textContent = pendingConfirms;

    // Update tab badge
    const tabBadge = document.getElementById('issuancePendingTabBadge');
    if (tabBadge) {
        const total = pendingReqs + pendingConfirms;
        tabBadge.textContent = total;
        if (total > 0) {
            tabBadge.classList.remove('d-none');
        } else {
            tabBadge.classList.add('d-none');
        }
    }

    // Check if pending panel content changed
    const hasChanged = (pendingReqs !== _lastPendingCount) ||
                        (pendingConfirms !== _lastConfirmCount) ||
                        (latestIssuanceId && latestIssuanceId !== _lastIssuanceId) ||
                        (latestIssuanceUpdate && latestIssuanceUpdate !== _lastIssuanceUpdate);

    if (hasChanged && !_isRefreshingPending) {
        // Do not disrupt user if a modal is open
        if (document.querySelector('.modal.show')) return;

        _isRefreshingPending = true;
        try {
            const res = await fetch('/admin/issuance?ajax_pending=1', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            if (res.ok) {
                const html = await res.text();
                const container = document.getElementById('pending-panel');
                if (container) {
                    container.innerHTML = html;
                    container.querySelectorAll('tr[data-issuance-id]').forEach(tr => {
                        tr.classList.add('row-highlight-new');
                        setTimeout(() => tr.classList.remove('row-highlight-new'), 3500);
                    });
                }
                _lastPendingCount = pendingReqs;
                _lastConfirmCount = pendingConfirms;
                _lastIssuanceId = latestIssuanceId;
                _lastIssuanceUpdate = latestIssuanceUpdate;
            }
        } catch (err) {
            console.warn('Realtime issuance pending refresh error:', err);
        } finally {
            _isRefreshingPending = false;
        }
    }
});
})();
</script>
@endsection
