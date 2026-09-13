@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">
                <i class="bi bi-boxes me-2"></i> Consumable Items
            </h1>
            <p class="text-muted m-0">Request consumable items, confirm receipts, and view your issuance history.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-green-bg); border: 1px solid var(--accent-green); color: var(--accent-green); padding: 16px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-red-bg); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 16px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div id="borrowerIssuancePendingContainer">
    {{-- ══════════════════════════════════════════
         SECTION 1: PENDING CONFIRMATIONS
         ══════════════════════════════════════════ --}}
    @if($pendingConfirmations->count() > 0)
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: var(--accent-blue-bg); border-radius: 8px;">
                <i class="bi bi-bell text-primary"></i>
            </div>
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">Pending Confirmation</h5>
            <span class="badge bg-primary rounded-pill">{{ $pendingConfirmations->count() }}</span>
        </div>

        @foreach($pendingConfirmations as $iss)
        <div class="activity-card mb-3 p-3 p-sm-4 shadow-sm" style="background: var(--bg-surface); border: 2px solid var(--accent-color); border-radius: 16px;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3 min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                    <div class="d-flex align-items-center justify-content-center" style="background: var(--accent-blue-bg); width: 54px; height: 54px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-box-seam fs-3 text-primary"></i>
                    </div>
                    <div class="min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                        <span class="fw-bold fs-5 d-block text-truncate" style="color: var(--text-primary);">{{ $iss->item->name ?? 'Unknown Item' }}</span>
                        <div class="d-flex align-items-center gap-2 mt-1 min-w-0 flex-wrap">
                            <span class="fw-bold text-primary flex-shrink-0">{{ $iss->quantity }}</span>
                            <span class="text-secondary small flex-shrink-0">{{ $iss->item->unit ?? '' }}</span>
                            <span class="text-secondary mx-1 flex-shrink-0">•</span>
                            <span class="text-secondary small text-truncate">{{ \Illuminate\Support\Str::limit($iss->purpose, 40) }}</span>
                        </div>
                        @if($iss->admin_notes)
                            <div class="small text-secondary mt-1 text-truncate"><i class="bi bi-chat me-1"></i> {{ $iss->admin_notes }}</div>
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-column align-items-md-end gap-2 flex-shrink-0">
                    <span class="d-block text-secondary text-nowrap" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Issued {{ $iss->issued_at ? $iss->issued_at->diffForHumans() : '' }}</span>
                    <form action="{{ route('borrower.issuance.confirm', $iss->id) }}" method="POST" data-lock-form>
                        @csrf
                        @php $confirmMsg = 'Confirm receipt of ' . $iss->quantity . ' ' . ($iss->item->unit ?? 'unit') . '(s) of ' . ($iss->item->name ?? 'this item') . '?'; @endphp
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2 d-flex align-items-center gap-2 text-nowrap" style="border-radius: 10px;"
                                onclick="return lockSubmit(this, 'Confirming…', {{ json_encode($confirmMsg) }})">
                            <i class="bi bi-check2-all"></i> Confirm Receipt
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         SECTION 2: MY REQUESTS
         ══════════════════════════════════════════ --}}
    @if($myRequests->count() > 0)
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: var(--accent-yellow-bg); border-radius: 8px;">
                <i class="bi bi-clock text-warning"></i>
            </div>
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">My Pending Requests</h5>
            <span class="badge bg-warning text-dark rounded-pill">{{ $myRequests->count() }}</span>
        </div>

        @foreach($myRequests as $req)
        <div class="activity-card mb-3 p-3" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3 min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                    <div style="width: 40px; height: 40px; background: var(--bg-main); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi bi-box-seam text-secondary"></i>
                    </div>
                    <div class="min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                        <span class="fw-bold d-block text-truncate" style="color: var(--text-primary);">{{ $req->item->name ?? 'Unknown' }}</span>
                        <span class="small text-secondary text-truncate d-block">{{ $req->quantity }} {{ $req->item->unit ?? '' }} • {{ \Illuminate\Support\Str::limit($req->purpose, 30) }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                    <span class="badge bg-warning text-dark px-2 px-sm-3 py-2 rounded-pill fw-bold text-nowrap">
                        <i class="bi bi-hourglass me-1"></i> Pending
                    </span>
                    <form action="{{ route('borrower.issuance.cancel', $req->id) }}" method="POST" class="m-0" data-lock-form>
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" style="border-radius: 8px;"
                                onclick="return lockSubmit(this, 'Cancelling…', 'Cancel this request?')" title="Cancel request">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    </div>

    {{-- ══════════════════════════════════════════
         SECTION 3: REQUEST FORM (wizard-lite)
         ══════════════════════════════════════════ --}}
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: var(--accent-green-bg); border-radius: 8px;">
                <i class="bi bi-plus-circle text-success"></i>
            </div>
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">Request Consumable Item</h5>
        </div>

        <div class="panel-card p-3 p-sm-4 shadow-sm" id="request-consumable" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px; scroll-margin-top: 80px;">

            {{-- Favorites: request-again shortcuts --}}
            @if($favorites->count() > 0)
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="text-secondary small fw-bold"><i class="bi bi-star-fill text-warning me-1"></i> Request again:</span>
                @foreach($favorites as $f)
                    <button type="button" class="fav-chip {{ $loop->first ? 'on' : '' }}"
                            data-id="{{ $f->item_id }}" data-stock="{{ $f->item?->stock_quantity ?? 0 }}" data-unit="{{ $f->item?->unit ?? 'unit' }}">
                        {{ $f->item?->name ?? 'Item' }} <span class="opacity-75">×{{ $f->times }}</span>
                    </button>
                @endforeach
            </div>
            @endif

            <div class="wizard-steps-bar d-flex align-items-center gap-1 gap-sm-2 mb-3 small text-secondary flex-nowrap overflow-x-auto py-2 px-2 px-sm-3"
                 style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                <span class="d-inline-flex align-items-center gap-1 flex-shrink-0 text-nowrap">
                    <span class="badge bg-primary rounded-pill">1</span>
                    <span><span class="d-none d-sm-inline">Select </span>Item</span>
                </span>
                <i class="bi bi-chevron-right text-muted flex-shrink-0 opacity-50" style="font-size: 10px;"></i>
                <span class="d-inline-flex align-items-center gap-1 flex-shrink-0 text-nowrap">
                    <span class="badge bg-primary rounded-pill">2</span>
                    <span>Quantity</span>
                </span>
                <i class="bi bi-chevron-right text-muted flex-shrink-0 opacity-50" style="font-size: 10px;"></i>
                <span class="d-inline-flex align-items-center gap-1 flex-shrink-0 text-nowrap">
                    <span class="badge bg-primary rounded-pill">3</span>
                    <span>Purpose</span>
                </span>
                <i class="bi bi-chevron-right text-muted flex-shrink-0 opacity-50" style="font-size: 10px;"></i>
                <span class="d-inline-flex align-items-center gap-1 flex-shrink-0 text-nowrap">
                    <span class="badge bg-success rounded-pill">4</span>
                    <span>Confirm<span class="d-none d-sm-inline"> with PIN</span></span>
                </span>
            </div>
            <form id="consumableForm">
                <label class="form-label fw-bold text-secondary d-flex align-items-center gap-2"><span class="badge bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px;height:22px;">1</span> Select Item <span class="text-danger">*</span></label>
                @if($availableItems->isEmpty())
                    <div class="text-center py-4 mb-1" style="border:1px dashed var(--border-color);border-radius:12px;background:var(--bg-main);">
                        <i class="bi bi-inbox text-secondary fs-4 d-block mb-1"></i>
                        <span class="small text-secondary">No consumables available right now — please check back later.</span>
                    </div>
                @endif
                <div class="row g-2 mb-1" id="itemCards">
                    @foreach($availableItems as $item)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="item-card d-block h-100 {{ $loop->first ? 'on' : '' }}" data-id="{{ $item->id }}"
                               data-stock="{{ $item->stock_quantity }}" data-unit="{{ $item->unit ?? 'unit' }}">
                            <input type="radio" name="item_id" value="{{ $item->id }}" class="visually-hidden" required
                                   {{ $loop->first ? 'checked' : '' }}>
                            <div class="ic-head">
                                <span class="ic-name">{{ $item->name }}</span>
                                <span class="badge rounded-pill ic-badge {{ $item->stock_quantity <= ($item->getMinStockThreshold()) ? 'low' : '' }}">
                                    {{ $item->stock_quantity }} {{ $item->unit }}
                                </span>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold text-secondary d-flex align-items-center gap-2"><span class="badge bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px;height:22px;">2</span> Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="consumableQty" class="form-control theme-dynamic-input" min="1" value="1" required>
                    <div class="small text-danger mt-1 d-none" id="qtyStockWarning"></div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold text-secondary d-flex align-items-center gap-2"><span class="badge bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px;height:22px;">3</span> Purpose <span class="text-danger">*</span></label>
                    <div class="purpose-chips" data-target="req_purpose"></div>
                    <input type="hidden" name="purpose" id="req_purpose" required>
                    <input type="text" class="form-control form-control-sm mt-2 d-none theme-dynamic-input" id="req_purpose_other"
                           placeholder="Specify purpose…" maxlength="500">
                </div>
                <div class="mt-4">
                    <button type="submit" id="issuanceSubmitBtn" class="btn btn-success fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px;">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- PIN confirmation modal --}}
    <div class="modal fade" id="pinConfirmModal" data-centered tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="background:var(--bg-surface);color:var(--text-primary);border-radius:16px;border:1px solid var(--border-color);">
                <div class="modal-body text-center pt-4 px-4">
                    <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:50%;background:var(--accent-blue-bg);color:var(--accent-blue);">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Enter your 4-digit PIN</h6>
                    <p class="text-secondary small mb-3">to confirm this consumable request</p>
                    <input type="password" inputmode="numeric" maxlength="4" id="pinInput"
                           class="form-control text-center theme-dynamic-input"
                           style="letter-spacing:10px;font-size:22px;font-weight:800;" placeholder="••••">
                    <div class="small text-danger mt-2 d-none" id="pinError">Incorrect PIN.</div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                    <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success fw-bold px-4" id="pinGo"><i class="bi bi-check-lg me-1"></i> Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SECTION 4: HISTORY
         ══════════════════════════════════════════ --}}
    <div>
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width: 36px; height: 36px; background: var(--bg-main); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                <i class="bi bi-clock-history text-secondary"></i>
            </div>
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">Issuance History</h5>
        </div>

        @php $history = $history ?? collect(); @endphp
        @forelse($history as $iss)
        <div class="activity-card mb-3 p-3 p-sm-4" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3 min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                    <div style="width: 40px; height: 40px; background: var(--bg-main); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi bi-box-seam text-secondary"></i>
                    </div>
                    <div class="min-w-0" style="min-width: 0; overflow: hidden; flex: 1 1 auto;">
                        <span class="fw-bold d-block text-truncate" style="color: var(--text-primary);">{{ $iss->item->name ?? 'Unknown' }}</span>
                        <span class="small text-secondary text-truncate d-block">{{ $iss->quantity }} {{ $iss->item->unit ?? '' }} • {{ \Illuminate\Support\Str::limit($iss->purpose, 35) }}</span>
                    </div>
                </div>
                <div class="text-end flex-shrink-0 ms-2">
                    @if($iss->status === 'confirmed')
                        <span class="badge bg-success px-2 px-sm-3 py-2 rounded-pill fw-bold text-nowrap">
                            <i class="bi bi-check2-all me-1"></i> Received
                        </span>
                    @else
                        <span class="badge bg-secondary px-2 px-sm-3 py-2 rounded-pill fw-bold text-nowrap">
                            <i class="bi bi-x me-1"></i> Cancelled
                        </span>
                    @endif
                    <div class="small text-secondary mt-1 text-nowrap">{{ $iss->updated_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5" style="background: var(--bg-surface); border-radius: 16px; border: 1px dashed var(--border-color);">
            <div style="width: 64px; height: 64px; background: var(--bg-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <i class="bi bi-inbox fs-2 text-secondary"></i>
            </div>
            <h5 class="fw-bold" style="color: var(--text-primary);">No History Yet</h5>
            <p class="small text-muted mb-0">Your consumable issuance history will appear here.</p>
        </div>
        @endforelse

        @if(method_exists($history, 'hasPages') && $history->hasPages())
            <div class="mt-4">{{ $history->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>

<style>
    .theme-dynamic-input { background: var(--bg-main) !important; border: 1px solid var(--border-color) !important; color: var(--text-primary) !important; }
    .theme-dynamic-input:focus { background-color: transparent !important; box-shadow: none !important; }
    .theme-dynamic-input option { background-color: var(--bg-surface) !important; color: var(--text-primary) !important; }
    .item-card{border:1.5px solid var(--border-color);background:var(--bg-surface);border-radius:12px;
        padding:11px 13px;cursor:pointer;transition:.14s;}
    .item-card:hover{border-color:var(--line-strong);background:var(--accent-blue-bg);}
    .item-card.on{border-color:var(--accent-color);background:var(--accent-blue-bg);box-shadow:0 0 0 2px var(--focus-ring);}
    .ic-head{display:flex;align-items:center;justify-content:space-between;gap:8px;min-width:0;}
    .ic-name{font-weight:700;font-size:12.5px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .ic-badge{font-size:10.5px;background:var(--bg-main);color:var(--text-secondary);flex-shrink:0;}
    .ic-badge.low{background:#fef2f2;color:#dc2626;}
    .fav-chip{border:1px solid var(--border-color);background:var(--bg-main);color:var(--text-primary);
        font-size:12px;font-weight:600;padding:5px 13px;border-radius:99px;cursor:pointer;transition:.12s;}
    .fav-chip:hover,.fav-chip.on{border-color:#f59e0b;background:rgba(245,158,11,.08);}
    .purpose-chips{display:flex;flex-wrap:wrap;gap:7px;}
    .purpose-chips .pc{border:1px solid var(--border-color);background:var(--bg-main);color:var(--text-secondary);
        font-size:12px;font-weight:600;padding:5px 13px;border-radius:99px;cursor:pointer;transition:.12s;}
    .purpose-chips .pc.on{background:#10b981;border-color:#10b981;color:#fff;}
    .wizard-steps-bar{scrollbar-width:none !important;-ms-overflow-style:none !important;}
    .wizard-steps-bar::-webkit-scrollbar{display:none !important;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const form = document.getElementById('consumableForm');
    const qty = document.getElementById('consumableQty');
    const warn = document.getElementById('qtyStockWarning');
    const purposeHidden = document.getElementById('req_purpose');

    /* ── Item card selection (replaces <select>) ── */
    function selectedCard() {
        const checked = document.querySelector('#itemCards input[type=radio]:checked');
        return checked ? checked.closest('.item-card') : document.querySelector('#itemCards .item-card');
    }
    document.querySelectorAll('.item-card input[type=radio]').forEach(radio =>
        radio.addEventListener('change', () => {
            document.querySelectorAll('.item-card').forEach(c => c.classList.remove('on'));
            radio.closest('.item-card')?.classList.add('on');
            applyStockLimit();
        }));

    /* ── Favorites chips pre-select ── */
    document.querySelectorAll('.fav-chip').forEach(chip =>
        chip.addEventListener('click', () => {
            const radio = document.querySelector('#itemCards input[type=radio][value="' + chip.dataset.id + '"]');
            if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
            document.querySelectorAll('.fav-chip').forEach(c => c.classList.remove('on'));
            chip.classList.add('on');
        }));

    /* ── Purpose chips ── */
    const PURPOSES = ['Classroom Instruction','Office / Admin Use','Event / Program','Repair / Maintenance','Cleaning / Janitorial','Other'];
    const pbox = document.querySelector('.purpose-chips');
    PURPOSES.forEach(p => {
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'pc'; b.textContent = p;
        b.addEventListener('click', () => {
            pbox.querySelectorAll('.pc').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            if (p === 'Other') { purposeHidden.value=''; document.getElementById('req_purpose_other')?.classList.remove('d-none');
                                 document.getElementById('req_purpose_other')?.focus(); }
            else { purposeHidden.value = p; document.getElementById('req_purpose_other')?.classList.add('d-none'); }
        });
        pbox.appendChild(b);
    });
    document.getElementById('req_purpose_other')?.addEventListener('input', function(){ purposeHidden.value = this.value; });

    /* ── Stock-aware quantity ── */
    function stockOf() {
        const card = selectedCard();
        return { stock: parseInt(card?.dataset.stock || '0'), unit: card?.dataset.unit || 'unit' };
    }
    function applyStockLimit() {
        const s = stockOf();
        if (!qty || !s.stock) return;
        qty.max = s.stock;
        if (parseInt(qty.value||'1') > s.stock) {
            qty.value = s.stock;
            warn.textContent = 'Only ' + s.stock + ' ' + s.unit + '(s) in stock — adjusted quantity.';
            warn.classList.remove('d-none');
            setTimeout(() => warn.classList.add('d-none'), 4000);
        }
    }
    qty.addEventListener('input', () => {
        const s = stockOf();
        if (s.stock > 0 && parseInt(qty.value) > s.stock) {
            warn.textContent = 'Only ' + s.stock + ' in stock.'; warn.classList.remove('d-none');
        } else warn.classList.add('d-none');
    });
    applyStockLimit();

    /* ── Submit → PIN gate → fetch request ── */
    let pendingSubmit = null;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const s = stockOf();
        if (!(parseInt(qty.value) > 0)) return showToast ? showToast('Validation','Enter a quantity.','warning') : alert('Enter a quantity.');
        if (s.stock && parseInt(qty.value) > s.stock)
            return showToast ? showToast('Over stock','Only '+s.stock+' '+s.unit+'(s) available.','error') : alert('Insufficient stock.');
        if (!purposeHidden.value)
            return showToast ? showToast('Validation','Pick or type a purpose.','warning') : alert('Purpose required.');
        pendingSubmit = {
            item_id: selectedCard()?.dataset.id,
            quantity: parseInt(qty.value),
            purpose: purposeHidden.value,
        };
        const modal = new bootstrap.Modal(document.getElementById('pinConfirmModal'));
        modal.show();
        setTimeout(() => document.getElementById('pinInput').focus(), 250);
    });

    async function doSubmit(pin) {
        const btn = document.getElementById('issuanceSubmitBtn');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        try {
            // 1) verify PIN
            const vp = await fetch('/auth/verify-pin', {
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
                body: JSON.stringify({ pin: pin }),
            });
            if (!vp.ok) {
                document.getElementById('pinError').classList.remove('d-none');
                btn.disabled = false; btn.innerHTML = '<i class="bi bi-send"></i> Submit Request';
                return false;
            }
            bootstrap.Modal.getInstance(document.getElementById('pinConfirmModal'))?.hide();

            // 2) submit the actual request
            const res = await fetch('{{ route("borrower.issuance.request") }}', {
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
                body: JSON.stringify(pendingSubmit),
            });
            const d = await res.json().catch(()=>({}));
            if (res.ok) { showToast ? showToast('Submitted', d.message || 'Request submitted.', 'success')
                                    : alert(d.message); setTimeout(()=>location.reload(), 900); return true; }
            showToast ? showToast('Error', d.message || 'Failed.', 'error') : alert(d.message || 'Failed.');
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-send"></i> Submit Request';
            return false;
        } catch (e) {
            alert('Network error.');
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-send"></i> Submit Request';
            return false;
        }
    }

    document.getElementById('pinGo').addEventListener('click', () => {
        const pin = document.getElementById('pinInput').value.trim();
        if (pin.length !== 4) { document.getElementById('pinError').classList.remove('d-none'); return; }
        doSubmit(pin);
    });
    document.getElementById('pinInput').addEventListener('keydown', function(e){
        this.value = this.value.replace(/\D/g,'');
        if (e.key === 'Enter' && this.value.length === 4) document.getElementById('pinGo').click();
    });

    /* ═══ Real-time Updates for Borrower Issuance Status ═══ */
    let _lastBorrowerConfirms = {{ $pendingConfirmations->count() }};
    let _lastBorrowerReqs = {{ $myRequests->count() }};
    let _isUpdatingBorrowerPending = false;

    window.addEventListener('baycis:realtime-update', async function(e) {
        const data = e.detail;
        if (!data || !data.counts) return;

        const confirms = Number(data.counts.pending_confirmations ?? _lastBorrowerConfirms);
        const reqs = Number(data.counts.pending_consumable_issuances ?? _lastBorrowerReqs);

        if ((confirms !== _lastBorrowerConfirms || reqs !== _lastBorrowerReqs) && !_isUpdatingBorrowerPending) {
            if (document.querySelector('.modal.show')) return;

            _isUpdatingBorrowerPending = true;
            try {
                const res = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                if (res.ok) {
                    const text = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');
                    const newSection = doc.getElementById('borrowerIssuancePendingContainer');
                    const currentSection = document.getElementById('borrowerIssuancePendingContainer');
                    if (newSection && currentSection) {
                        currentSection.innerHTML = newSection.innerHTML;
                        currentSection.querySelectorAll('.activity-card').forEach(card => {
                            card.classList.add('row-highlight-new');
                            setTimeout(() => card.classList.remove('row-highlight-new'), 3500);
                        });
                    }
                    _lastBorrowerConfirms = confirms;
                    _lastBorrowerReqs = reqs;
                }
            } catch (err) {
                console.warn('Realtime borrower issuance refresh error:', err);
            } finally {
                _isUpdatingBorrowerPending = false;
            }
        }
    });

    // ── CONFIRM RECEIPT MODAL ──────────
    const crmModalEl = document.getElementById('confirmReceiptModal');

    function populateConfirmModal(btn) {
        if (!btn) return;
        const formAction = btn.getAttribute('data-action') || btn.dataset.action;
        const itemName = btn.getAttribute('data-item') || btn.dataset.item || 'Unknown Item';
        const qty = btn.getAttribute('data-qty') || btn.dataset.qty || '1';
        const unit = btn.getAttribute('data-unit') || btn.dataset.unit || 'unit';
        const issued = btn.getAttribute('data-issued') || btn.dataset.issued || '';

        const form = document.getElementById('confirmReceiptForm');
        if (form && formAction) form.action = formAction;

        const qEl = document.getElementById('crm_question');
        if (qEl) qEl.textContent = 'Confirm receipt of ' + qty + ' ' + unit + '(s) of ' + itemName + '?';

        const itemEl = document.getElementById('crm_item');
        if (itemEl) itemEl.textContent = itemName;

        const qtyEl = document.getElementById('crm_qty');
        if (qtyEl) qtyEl.textContent = qty + ' ' + unit + '(s)';

        const issuedEl = document.getElementById('crm_issued');
        if (issuedEl) issuedEl.textContent = issued ? 'Issued ' + issued : 'Recently';
    }

    if (crmModalEl) {
        crmModalEl.addEventListener('show.bs.modal', function (event) {
            if (event.relatedTarget) {
                populateConfirmModal(event.relatedTarget);
            }
        });
    }

    document.querySelectorAll('.js-confirm-receipt-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            populateConfirmModal(this);
            if (crmModalEl && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(crmModalEl);
                modal?.show();
            }
        });
    });

    document.getElementById('confirmReceiptForm')?.addEventListener('submit', function () {
        const submitBtn = document.getElementById('crm_submit_btn');
        if (submitBtn) {
            setTimeout(() => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Confirming…';
            }, 10);
        }
    });
});
</script>
@endsection