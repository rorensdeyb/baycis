@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    {{-- Page Header --}}
    <div class="welcome-header mb-3 mb-md-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h1 style="font-size: clamp(20px, 4vw, 24px); font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em;">
                    Return Assets
                </h1>
                <p class="text-secondary small mb-0 mt-1">
                    Select an active borrowed item below to declare condition and initiate return.
                </p>
            </div>
            @php
                $activeCount = $activeBorrows->where('status', '!=', 'return_pending')->count();
                $pendingCount = $activeBorrows->where('status', 'return_pending')->count();
            @endphp
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($activeCount > 0)
                    <span class="badge" style="background: var(--accent-blue-bg); color: var(--accent-blue); font-size: 11.5px; font-weight: 700; padding: 6px 12px; border-radius: 20px;">
                        <i class="bi bi-box-seam me-1"></i> {{ $activeCount }} Active {{ Str::plural('Loan', $activeCount) }}
                    </span>
                @endif
                @if($pendingCount > 0)
                    <span class="badge" style="background: var(--accent-yellow-bg); color: var(--accent-yellow); font-size: 11.5px; font-weight: 700; padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <i class="bi bi-hourglass-split me-1"></i> {{ $pendingCount }} Verifying
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- System Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-3 shadow-sm" style="border-radius: 12px; background: var(--accent-red-bg); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 14px 16px;">
            <div class="fw-bold mb-1 small"><i class="bi bi-exclamation-triangle-fill me-2"></i>Return could not be submitted:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm" role="alert" style="border-radius: 12px; background: var(--accent-green-bg); border: 1px solid var(--accent-green); color: var(--accent-green); padding: 14px 16px;">
            <div class="d-flex align-items-center gap-2 small fw-semibold">
                <i class="bi bi-check-circle-fill fs-6 flex-shrink-0"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close" style="font-size: 10px;"></button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm" role="alert" style="border-radius: 12px; background: var(--accent-red-bg); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 14px 16px;">
            <div class="d-flex align-items-center gap-2 small fw-semibold">
                <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close" style="font-size: 10px;"></button>
            </div>
        </div>
    @endif

    {{-- Active Borrows List --}}
    <div id="borrowerReturnsContainer" class="activity-list mb-4">
        @forelse($activeBorrows as $borrow)
            <div class="activity-card mb-2 p-3 shadow-sm" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; transition: all 0.2s ease;">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    
                    {{-- Item Summary --}}
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div style="background: var(--bg-main); width: 44px; height: 44px; border-radius: 11px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); flex-shrink: 0;">
                            <i class="bi bi-box-seam" style="font-size: 20px; color: var(--accent-color);"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="fw-bold text-truncate d-block" style="color: var(--text-primary); font-size: 14.5px; line-height: 1.3;">
                                {{ $borrow->item->name ?? 'Unknown Asset' }}
                            </span>
                            <div class="d-flex align-items-center gap-2 flex-wrap text-secondary" style="font-size: 12px; margin-top: 2px;">
                                <span class="font-monospace" style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 1px 6px; border-radius: 4px; font-size: 11px;">
                                    <i class="bi bi-upc-scan me-1"></i>{{ $borrow->item->property_tag ?? 'N/A' }}
                                </span>
                                @if(optional($borrow->item)->category)
                                    <span>&bull; {{ $borrow->item->category->name }}</span>
                                @endif
                                <span class="d-inline d-sm-none text-muted">&bull; {{ $borrow->created_at->format('M d') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action & Status --}}
                    <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-3 flex-shrink-0 pt-2 pt-sm-0 border-top border-top-sm-0" style="border-color: var(--border-color) !important;">
                        <div class="text-sm-end d-none d-sm-block">
                            <span class="d-block text-secondary" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Borrowed On</span>
                            <span class="fw-semibold" style="color: var(--text-primary); font-size: 13px;">{{ $borrow->created_at->format('M d, Y') }}</span>
                        </div>

                        @if($borrow->status === 'return_pending')
                            <div class="py-1 px-3 text-center d-flex align-items-center gap-1" style="background: var(--accent-yellow-bg); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px;">
                                <span class="spinner-grow spinner-grow-sm" style="width: 8px; height: 8px; color: var(--accent-yellow);" role="status"></span>
                                <span style="color: var(--accent-yellow); font-weight: 700; font-size: 12px;">Verifying with Admin</span>
                            </div>
                        @else
                            <button type="button" 
                                    class="btn-primary-action btn-initiate-return d-inline-flex align-items-center justify-content-center gap-1"
                                    data-id="{{ $borrow->id }}" 
                                    data-item="{{ $borrow->item->name ?? 'Asset' }}"
                                    data-tag="{{ $borrow->item->property_tag ?? 'N/A' }}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#returnModal"
                                    style="padding: 7px 14px; font-size: 13px; border-radius: 8px; font-weight: 600; white-space: nowrap; height: 38px; margin: 0;">
                                <i class="bi bi-arrow-return-left"></i> Initiate Return
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5 px-3" style="background: var(--bg-surface); border-radius: 14px; border: 1px dashed var(--border-color);">
                <div style="width: 52px; height: 52px; background: var(--bg-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; border: 1px solid var(--border-color);">
                    <i class="bi bi-shield-check fs-3" style="color: var(--accent-color);"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color: var(--text-primary); font-size: 16px;">All Caught Up!</h5>
                <p class="small text-secondary mb-0">You currently have no active borrowed items to return.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Return Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 18px; border: 1px solid var(--border-color); box-shadow: 0 16px 48px rgba(0,0,0,0.2);">
            
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="returnModalLabel" style="font-size: 17px;">
                    <div style="width: 34px; height: 34px; background: var(--accent-bg); color: var(--accent-color); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="bi bi-arrow-return-left"></i>
                    </div>
                    Submit Asset Return
                </h5>
                <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="returnForm" method="POST" class="m-0">
                @csrf
                <div class="modal-body px-4 py-3">
                    
                    {{-- Selected Item Banner --}}
                    <div class="p-3 mb-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                        <span class="d-block text-secondary mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Returning Asset:</span>
                        <div class="fw-bold fs-6 d-block text-truncate" id="returnItemName" style="color: var(--text-primary);">Item Name</div>
                        <div class="font-monospace text-secondary small mt-1" id="returnItemTag">TAG-12345</div>
                    </div>

                    <p class="text-secondary mb-3" style="font-size: 13px; line-height: 1.45;">
                        Please declare the physical condition of the item before handing it over to the Property Custodian for verification.
                    </p>

                    {{-- Condition Selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-primary);">
                            Current Item Condition <span class="text-danger">*</span>
                        </label>
                        <select name="return_condition" id="returnConditionSelect" class="form-select" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 12px; font-size: 13.5px; box-shadow: none; cursor: pointer;" required>
                            <option value="" disabled selected>Select condition...</option>
                            <option value="Good">Good / Working Perfectly</option>
                            <option value="Damaged">Damaged / Broken Parts</option>
                            <option value="Needs Repair">Needs Maintenance or Repair</option>
                        </select>
                    </div>

                    {{-- Optional Remarks --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-primary);">Remarks / Issues (Optional)</label>
                        <textarea name="return_remarks" id="returnRemarksText" class="form-control" rows="3" placeholder="Describe any issues, scratches, or missing accessories..." style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 12px; font-size: 13px; box-shadow: none;"></textarea>
                    </div>

                </div>
                
                <div class="modal-footer border-0 pb-4 px-4 pt-1 gap-2">
                    <button type="button" class="btn btn-light px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 9px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary); font-size: 13px;">Cancel</button>
                    <button type="submit" id="submitReturnBtn" class="btn-primary-action fw-bold px-4 py-2 flex-grow-1 justify-content-center" style="border-radius: 9px; margin: 0; font-size: 13.5px; height: 40px;">
                        <i class="bi bi-arrow-return-left me-1"></i> Submit Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const returnModalEl = document.getElementById('returnModal');
    const returnForm = document.getElementById('returnForm');
    const submitBtn = document.getElementById('submitReturnBtn');
    const returnActionTemplate = "{{ route('borrower.returns.submit', ':id') }}";

    function populateReturnModal(source) {
        if (!source) return;
        const reqId = source.getAttribute('data-id');
        const itemName = source.getAttribute('data-item') || 'Asset';
        const itemTag = source.getAttribute('data-tag') || 'N/A';

        if (returnForm && reqId) {
            returnForm.action = returnActionTemplate.replace(':id', encodeURIComponent(reqId));
        }
        const nameEl = document.getElementById('returnItemName');
        if (nameEl) nameEl.textContent = itemName;

        const tagEl = document.getElementById('returnItemTag');
        if (tagEl) tagEl.textContent = itemTag;
    }

    // 1. Bootstrap native modal trigger
    if (returnModalEl) {
        returnModalEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button) {
                populateReturnModal(button);
            }
        });

        // Reset on hide
        returnModalEl.addEventListener('hidden.bs.modal', function() {
            if (returnForm) {
                returnForm.reset();
            }
            if (submitBtn) {
                submitBtn.dataset.busy = '';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-arrow-return-left me-1"></i> Submit Return';
            }
        });
    }

    // 2. Click event delegation fallback
    document.addEventListener('click', function(e) {
        const returnBtn = e.target.closest('.btn-initiate-return');
        if (returnBtn) {
            populateReturnModal(returnBtn);
        }
    });

    // 3. Robust Form Submission: do not cancel the form pipeline
    if (returnForm) {
        returnForm.addEventListener('submit', function(e) {
            if (!returnForm.checkValidity()) {
                returnForm.reportValidity();
                e.preventDefault();
                return;
            }

            if (submitBtn) {
                if (submitBtn.dataset.busy === '1') {
                    e.preventDefault();
                    return;
                }
                submitBtn.dataset.busy = '1';
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';

                // CRITICAL: Defer disabling the button so the browser dispatches the submit event
                setTimeout(function() {
                    submitBtn.disabled = true;
                }, 10);
            }
        });
    }

    /* ═══ Real-time Updates for Borrower Returns ═══ */
    let _lastActiveBorrows = {{ $activeBorrows->count() }};
    let _lastReturnUpdated = '';
    let _isUpdatingBorrowerReturns = false;

    window.addEventListener('baycis:realtime-update', async function(e) {
        const data = e.detail;
        if (!data || !data.counts) return;

        const activeCount = Number(data.counts.active_loans ?? _lastActiveBorrows);
        const returnUpdated = String(data.counts.latest_return_updated_at ?? _lastReturnUpdated);

        const hasChanged = (activeCount !== _lastActiveBorrows) ||
                           (returnUpdated && returnUpdated !== _lastReturnUpdated);

        if (hasChanged && !_isUpdatingBorrowerReturns) {
            if (document.querySelector('.modal.show')) return;

            _isUpdatingBorrowerReturns = true;
            try {
                const res = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                if (res.ok) {
                    const text = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');
                    const newSection = doc.getElementById('borrowerReturnsContainer');
                    const currentSection = document.getElementById('borrowerReturnsContainer');
                    if (newSection && currentSection) {
                        currentSection.innerHTML = newSection.innerHTML;
                        currentSection.querySelectorAll('.activity-card').forEach(card => {
                            card.classList.add('row-highlight-new');
                            setTimeout(() => card.classList.remove('row-highlight-new'), 3500);
                        });
                    }
                    _lastActiveBorrows = activeCount;
                    _lastReturnUpdated = returnUpdated;
                }
            } catch (err) {
                console.warn('Realtime borrower returns refresh error:', err);
            } finally {
                _isUpdatingBorrowerReturns = false;
            }
        }
    });
});
</script>
@endsection