@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Return Assets</h1>
            <p class="text-muted m-0">Select an active borrowed item below to initiate a return process.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 16px;">
            <div class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Return Failed:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success mb-4 shadow-sm" style="border-radius: 12px; background: #d1fae5; border: 1px solid #34d399; color: #065f46; padding: 16px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="activity-list mb-4">
        @forelse($activeBorrows as $borrow)
            <div class="activity-card mb-3 p-4 shadow-sm" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px; transition: 0.2s;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="item-icon" style="background: var(--bg-main); width: 54px; height: 54px; border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                            <i class="bi bi-box-seam fs-3 text-secondary"></i>
                        </div>
                        <div class="item-details">
                            <span class="item-name fw-bold fs-5 d-block" style="color: var(--text-primary);">{{ $borrow->item->name ?? 'Unknown Item' }}</span>
                            <span class="item-meta font-monospace text-secondary" style="font-size: 13px;">
                                <i class="bi bi-upc-scan me-1"></i> {{ $borrow->item->property_tag ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                        <div class="text-md-end">
                            <span class="d-block text-secondary" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Borrowed On</span>
                            <span class="fw-bold" style="color: var(--text-primary); font-size: 14px;">{{ $borrow->created_at->format('M d, Y') }}</span>
                        </div>

                        @if($borrow->status === 'return_pending')
                            <div class="p-2 px-3 text-center" style="background: rgba(245, 158, 11, 0.1); border: 1px solid #f59e0b; border-radius: 8px;">
                                <span class="d-block" style="color: #b45309; font-weight: 700; font-size: 12px;"><i class="bi bi-hourglass-split me-1"></i> Return Pending</span>
                                <span class="d-block" style="color: #92400e; font-size: 10px;">Waiting for Admin Check</span>
                            </div>
                        @else
                            <button type="button" class="btn-primary-action btn-initiate-return" 
                                    data-id="{{ $borrow->id }}" 
                                    data-item="{{ $borrow->item->name ?? 'Asset' }}"
                                    data-tag="{{ $borrow->item->property_tag ?? 'N/A' }}"
                                    data-bs-toggle="modal" data-bs-target="#returnModal"
                                    style="white-space: nowrap;">
                                <i class="bi bi-arrow-return-left me-1"></i> Initiate Return
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5" style="background: var(--bg-surface); border-radius: 16px; border: 1px dashed var(--border-color);">
                <div style="width: 64px; height: 64px; background: var(--bg-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                    <i class="bi bi-shield-check fs-2 text-secondary"></i>
                </div>
                <h5 class="fw-bold" style="color: var(--text-primary);">All Caught Up!</h5>
                <p class="small mb-0">You currently have no active borrowed items to return.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <div style="width: 36px; height: 36px; background: var(--accent-bg); color: var(--accent-color); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-return-left"></i>
                    </div>
                    Submit Asset Return
                </h5>
                <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
            </div>

            <form id="returnForm" method="POST" class="m-0">
                @csrf
                <div class="modal-body px-4 py-4">
                    
                    <div class="p-3 mb-4" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                        <span class="d-block text-secondary mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Returning Asset:</span>
                        <span class="fw-bold fs-5 d-block" id="returnItemName" style="color: var(--text-primary);">Item Name</span>
                        <span class="font-monospace text-secondary small" id="returnItemTag">TAG-12345</span>
                    </div>

                    <p class="text-secondary mb-4" style="font-size: 14px; line-height: 1.5;">
                        To finalize your return, please declare the current physical condition of the asset.
                    </p>

                    <div class="mb-4">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Item Condition <span class="text-danger">*</span></label>
                        <select name="return_condition" class="form-select" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none; cursor: pointer;" required>
                            <option value="" disabled selected>Select the condition...</option>
                            <option value="Good">Good / Working Perfectly</option>
                            <option value="Damaged">Damaged / Broken Parts</option>
                            <option value="Needs Repair">Needs Maintenance or Repair</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary);">Remarks / Issues (Optional)</label>
                        <textarea name="return_remarks" class="form-control" rows="3" placeholder="If damaged or missing parts, please describe here..." style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;"></textarea>
                    </div>

                </div>
                
                <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Cancel</button>
                    <button type="submit" class="btn-primary-action fw-bold px-4 py-2 flex-grow-1" style="border-radius: 10px; margin: 0;" onclick="if(this.form.checkValidity()){ this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span> Processing...'; this.style.pointerEvents='none'; this.form.submit(); }">
                        Submit Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Event Delegation for Return Modals
        document.addEventListener('click', function(e) {
            const returnBtn = e.target.closest('.btn-initiate-return');
            if (returnBtn) {
                const reqId = returnBtn.getAttribute('data-id');
                const itemName = returnBtn.getAttribute('data-item');
                const itemTag = returnBtn.getAttribute('data-tag');

                // Dynamically inject data into the modal
                document.getElementById('returnForm').action = `/borrower/returns/${reqId}`;
                document.getElementById('returnItemName').innerText = itemName;
                document.getElementById('returnItemTag').innerText = itemTag;
            }
        });
    });
</script>
@endsection