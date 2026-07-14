@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">Return Assets</h1>
        <p class="form-label text-secondary mb-0">Verify asset conditions and restore items to the available inventory pool.</p>
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

    <div id="returns-table" class="panel-card p-0 overflow-hidden shadow-sm" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="admin-table mb-0 align-middle">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3" style="width: 25%;">Asset Details</th>
                        <th class="py-3" style="width: 20%;">Borrower</th>
                        <th class="py-3" style="width: 20%;">Reported Condition</th>
                        <th class="py-3" style="width: 20%;">Borrower Remarks</th>
                        <th class="text-end pe-4 py-3" style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReturns as $req)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: 0.2s;">
                        
                        <!-- Asset Details -->
                        <td class="ps-4 py-3">
                            <div class="fw-bold" style="color: var(--text-primary);">{{ $req->item->name ?? 'Unknown Item' }}</div>
                            <div class="small text-secondary font-monospace"><i class="bi bi-upc-scan me-1"></i>{{ $req->item->property_tag ?? 'N/A' }}</div>
                        </td>

                        <!-- Borrower Info -->
                        <td class="py-3">
                            <div class="fw-bold" style="color: var(--text-primary);">{{ $req->user->name ?? 'Unknown User' }}</div>
                            <div class="small text-secondary">Returned: {{ $req->updated_at->format('M d, Y') }}</div>
                        </td>

                        <!-- Reported Condition -->
                        <td class="py-3">
                            @if($req->return_condition === 'Good')
                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Good</span>
                            @elseif($req->return_condition === 'Damaged')
                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Damaged</span>
                            @elseif($req->return_condition === 'Needs Repair')
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-tools me-1"></i> Needs Repair</span>
                            @else
                                <span class="badge bg-secondary px-3 py-2 rounded-pill">Not Reported</span>
                            @endif
                        </td>

                        <!-- Remarks -->
                        <td class="py-3">
                            <div class="small" style="color: var(--text-secondary); line-height: 1.4;">
                                {{ $req->return_remarks ?: 'No remarks provided.' }}
                            </div>
                        </td>

                        <!-- Action Button -->
                        <td class="text-end pe-4 py-3">
                            <button type="button" class="btn btn-sm btn-success fw-bold px-3 d-flex align-items-center gap-2 ms-auto btn-verify-return" 
                                data-id="{{ $req->id }}" 
                                data-condition="{{ $req->return_condition }}"
                                data-bs-toggle="modal" data-bs-target="#confirmReturnModal" style="border-radius: 8px;">
                                <i class="bi bi-box-arrow-in-left"></i> Verify Return
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div style="width: 64px; height: 64px; background: var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                                <i class="bi bi-shield-check fs-3 text-secondary"></i>
                            </div>
                            <h5 class="fw-bold" style="color: var(--text-primary);">All Caught Up!</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">There are currently no assets waiting to be returned.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pendingReturns->hasPages())
            <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
                {{ $pendingReturns->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- ==========================================
         CONFIRM RETURN MODAL
         ========================================== -->
    <div class="modal fade" id="confirmReturnModal" tabindex="-1" aria-hidden="true">
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
                        <button type="submit" class="btn btn-success fw-bold px-4 py-2 flex-grow-1" style="border-radius: 10px;" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span> Processing...'; this.disabled=true; this.form.submit();">
                            Confirm Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dynamic Event Delegation for Return Modals
    document.addEventListener('click', function(e) {
        const verifyBtn = e.target.closest('.btn-verify-return');
        if (verifyBtn) {
            const reqId = verifyBtn.getAttribute('data-id');
            const reportedCondition = verifyBtn.getAttribute('data-condition');
            
            // Set the form action URL dynamically
            document.getElementById('confirmReturnForm').action = `/admin/requests/${reqId}/return`;
            
            // Pre-select the dropdown to match the borrower's report!
            const selectDropdown = document.getElementById('modalConditionSelect');
            if(reportedCondition) {
                selectDropdown.value = reportedCondition;
            }
        }
    });

    // Fix scroll position: append #returns-table anchor to all pagination links
    // so clicking next/prev scrolls to the table, not back to the very top.
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#returns-table');
        }
    });
});
</script>
@endsection