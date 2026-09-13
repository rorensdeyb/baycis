@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">Return Assets</h1>
            <p class="form-label text-secondary mb-0">Verify asset conditions and restore items to the available inventory pool.</p>
        </div>
        <button type="button" class="btn fw-bold d-flex align-items-center gap-2 px-4 py-2" data-bs-toggle="modal" data-bs-target="#returnScanModal" style="background-color: var(--text-primary); color: var(--bg-surface); border-radius: 8px;">
            <i class="bi bi-upc-scan fs-5"></i> Scan to Return
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div class="ms-2">{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="ms-2">{{ session('error') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Search & Filter Bar for Return Assets --}}
    <div class="panel-card p-3 mb-4 shadow-sm" style="border-radius: 12px;">
        <form action="{{ route('admin.returns') }}" method="GET" class="row g-3 m-0">
            <div class="col-12 col-md-8 p-0 pe-md-2 position-relative">
                <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control theme-dynamic-input w-100" placeholder="Search by asset name, property tag, or borrower..." style="padding-left: 42px;">
            </div>
            <div class="col-12 col-md-4 p-0 ps-md-2 d-flex gap-2">
                <select name="condition" class="form-select theme-dynamic-input w-100 cursor-pointer" onchange="this.form.submit()">
                    <option value="all" {{ request('condition') == 'all' ? 'selected' : '' }}>All Conditions</option>
                    <option value="Good" {{ request('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                    <option value="Damaged" {{ request('condition') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="Needs Repair" {{ request('condition') == 'Needs Repair' ? 'selected' : '' }}>Needs Repair</option>
                </select>
            </div>
            <noscript><button type="submit" class="btn btn-primary d-none">Filter</button></noscript>
        </form>
    </div>

    <div id="returnsTableContainer">
        @include('admin.partials.returns-table')
    </div>

    <!-- ==========================================
         CONFIRM RETURN MODAL
         ========================================== -->
    <div class="modal fade" id="confirmReturnModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; background: rgba(25, 135, 84, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-arrow-in-left"></i>
                        </div>
                        Verify Asset Return
                    </h5>
                    <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                <form id="confirmReturnForm" method="POST" class="m-0">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <p class="text-secondary mb-4" style="font-size: 15px; line-height: 1.6;">
                            Confirming this return will finalize the transaction and return the asset to the <strong>"Available"</strong> inventory pool.
                        </p>
                        
                        <div>
                            <label class="form-label fw-bold small text-uppercase letter-spacing-1">Final Asset Condition <span class="text-danger">*</span></label>
                            <select name="final_condition" id="modalConditionSelect" class="form-select theme-dynamic-input cursor-pointer" required>
                                <option value="Good">Good / Working Perfectly</option>
                                <option value="Damaged">Damaged / Broken Parts</option>
                                <option value="Needs Repair">Needs Maintenance or Repair</option>
                            </select>
                            <div class="form-text mt-2" style="font-size: 12px; color: var(--text-secondary);">
                                <i class="bi bi-info-circle me-1"></i> Pre-filled with the borrower's declaration. You may override this if necessary.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Cancel</button>
                        <button type="submit" id="adminConfirmReturnSubmitBtn" class="btn btn-success fw-bold px-4 py-2 flex-grow-1" style="border-radius: 10px;">
                            <i class="bi bi-box-arrow-in-left me-1"></i> Confirm Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- RETURN SCANNER MODAL -->
<div class="modal fade" id="returnScanModal" tabindex="-1" aria-hidden="true" data-centered>
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background-color: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-body text-center p-5">
                <i class="bi bi-qr-code-scan mb-3 d-block" style="font-size: 4rem; color: var(--text-primary);"></i>
                <h5 class="fw-bold mb-2" style="color: var(--text-primary);">Return Asset</h5>
                <p class="text-secondary small mb-4">Scan the tag to instantly verify return.</p>
                <form onsubmit="processReturnScan(event)">
                    <input type="text" id="returnScanInput" class="form-control text-center shadow-none" placeholder="Awaiting input..." autocomplete="off" style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const adminReturnActionTemplate = "{{ route('admin.requests.return', ':id') }}";
    const confirmReturnForm = document.getElementById('confirmReturnForm');
    const confirmReturnModalEl = document.getElementById('confirmReturnModal');
    const adminSubmitBtn = document.getElementById('adminConfirmReturnSubmitBtn');

    function populateAdminReturnModal(btn) {
        if (!btn) return;
        const reqId = btn.getAttribute('data-id');
        const reportedCondition = btn.getAttribute('data-condition');

        if (confirmReturnForm && reqId) {
            confirmReturnForm.action = adminReturnActionTemplate.replace(':id', encodeURIComponent(reqId));
        }
        const selectDropdown = document.getElementById('modalConditionSelect');
        if (selectDropdown && reportedCondition) {
            selectDropdown.value = reportedCondition;
        }
    }

    if (confirmReturnModalEl) {
        confirmReturnModalEl.addEventListener('show.bs.modal', function(e) {
            if (e.relatedTarget) {
                populateAdminReturnModal(e.relatedTarget);
            }
        });

        confirmReturnModalEl.addEventListener('hidden.bs.modal', function() {
            if (adminSubmitBtn) {
                adminSubmitBtn.dataset.busy = '';
                adminSubmitBtn.disabled = false;
                adminSubmitBtn.innerHTML = '<i class="bi bi-box-arrow-in-left me-1"></i> Confirm Return';
            }
        });
    }

    // Dynamic Event Delegation for Return Modals
    document.addEventListener('click', function(e) {
        const verifyBtn = e.target.closest('.btn-verify-return');
        if (verifyBtn) {
            populateAdminReturnModal(verifyBtn);
        }
    });

    // Form submission with deferred button disable
    if (confirmReturnForm) {
        confirmReturnForm.addEventListener('submit', function(e) {
            if (!confirmReturnForm.checkValidity()) {
                confirmReturnForm.reportValidity();
                e.preventDefault();
                return;
            }

            if (adminSubmitBtn) {
                if (adminSubmitBtn.dataset.busy === '1') {
                    e.preventDefault();
                    return;
                }
                adminSubmitBtn.dataset.busy = '1';
                adminSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

                setTimeout(function() {
                    adminSubmitBtn.disabled = true;
                }, 10);
            }
        });
    }

    // Return Barcode Scanner Logic
    const returnScanModalEl = document.getElementById('returnScanModal');
    const returnScanInput = document.getElementById('returnScanInput');
    
    if(returnScanModalEl) {
        returnScanModalEl.addEventListener('shown.bs.modal', () => {
            returnScanInput.value = '';
            returnScanInput.focus();
        });
        returnScanInput.addEventListener('blur', () => {
            if(returnScanModalEl.classList.contains('show')) returnScanInput.focus();
        });
    }

    window.processReturnScan = function(event) {
        event.preventDefault();
        const scannedTag = returnScanInput.value.trim().toLowerCase();
        let found = false;

        // Loop through the table rows to find the matching tag
        document.querySelectorAll('tbody tr').forEach(row => {
            const tagElement = row.querySelector('.font-monospace');
            if (tagElement && tagElement.textContent.trim().toLowerCase().includes(scannedTag)) {
                // Asset found! Close scanner and click the verify button
                const verifyBtn = row.querySelector('.btn-verify-return');
                if (verifyBtn) {
                    bootstrap.Modal.getInstance(returnScanModalEl).hide();
                    verifyBtn.click(); // Magically opens the verification modal!
                    found = true;
                }
            }
        });

        if (!found) {
            alert('Tag "' + scannedTag + '" is not currently pending return on this page.');
            returnScanInput.value = '';
            returnScanInput.focus();
        }
    };

    // Fix scroll position: append #returns-table anchor to all pagination links
    // so clicking next/prev scrolls to the table, not back to the very top.
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#returns-table');
        }
    });

    // Remarks "Show more" toggle — uses event delegation to avoid inline-onclick fragility
    document.querySelectorAll('.remarks-expand-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const fullText = this.getAttribute('data-full-text');
            const remarkDiv = this.previousElementSibling;
            if (remarkDiv) {
                remarkDiv.textContent = fullText;
            }
            this.style.display = 'none';
        });
    });

    // ── Realtime In-Page Update for Return Assets ──
    let lastPendingReturns = {{ $pendingReturns->total() }};
    let lastReturnId = {{ (int) (\App\Models\BorrowRequest::where('status', 'return_pending')->max('id') ?? 0) }};
    let lastReturnUpdated = '{{ (string) (\App\Models\BorrowRequest::whereIn('status', ['return_pending', 'returned'])->max('updated_at') ?? '') }}';
    let isFetchingReturns = false;

    window.addEventListener('baycis:realtime-update', function(e) {
        const counts = e.detail?.counts;
        if (!counts) return;

        const currentPending = counts.pending_returns !== undefined ? Number(counts.pending_returns) : lastPendingReturns;
        const currentReturnId = counts.latest_return_id !== undefined ? Number(counts.latest_return_id) : lastReturnId;
        const currentReturnUpdated = counts.latest_return_updated_at !== undefined ? String(counts.latest_return_updated_at) : lastReturnUpdated;

        const hasChanged = (currentPending !== lastPendingReturns) ||
                           (currentReturnId && currentReturnId !== lastReturnId) ||
                           (currentReturnUpdated && currentReturnUpdated !== lastReturnUpdated);

        if (hasChanged && !isFetchingReturns) {
            if (document.querySelector('.modal.show')) return;
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && searchInput === document.activeElement && searchInput.value.trim() !== '') return;

            refreshReturnsTable(currentPending, currentReturnId, currentReturnUpdated);
        }
    });

    async function refreshReturnsTable(newPending, newReturnId, newReturnUpdated) {
        const container = document.getElementById('returnsTableContainer');
        if (!container || isFetchingReturns) return;
        isFetchingReturns = true;

        try {
            const url = new URL(window.location.href);
            url.searchParams.set('ajax_table', '1');

            const res = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (res.ok) {
                const html = await res.text();
                if (!document.querySelector('.modal.show')) {
                    container.innerHTML = html;

                    lastPendingReturns = newPending;
                    lastReturnId = newReturnId;
                    lastReturnUpdated = newReturnUpdated;

                    // Re-bind pagination anchor links
                    container.querySelectorAll('.pagination a').forEach(function(link) {
                        const href = link.getAttribute('href');
                        if (href && !href.includes('#')) {
                            link.setAttribute('href', href + '#returns-table');
                        }
                    });

                    // Re-bind remarks expand buttons
                    container.querySelectorAll('.remarks-expand-btn').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            const fullText = this.getAttribute('data-full-text');
                            const remarkDiv = this.previousElementSibling;
                            if (remarkDiv) remarkDiv.textContent = fullText;
                            this.style.display = 'none';
                        });
                    });

                    // Highlight top row if new
                    const firstRow = container.querySelector('tbody tr[data-return-id]');
                    if (firstRow) {
                        firstRow.classList.add('row-highlight-new');
                        setTimeout(() => firstRow.classList.remove('row-highlight-new'), 3500);
                    }
                }
            }
        } catch (err) {
            console.warn('Realtime returns table refresh error:', err);
        } finally {
            isFetchingReturns = false;
        }
    }
});
</script>
@endsection