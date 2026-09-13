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
                <tr data-return-id="{{ $req->id }}" style="border-bottom: 1px solid var(--border-color); transition: 0.2s;">
                    
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
                        <div class="small" style="color: var(--text-secondary); line-height: 1.4; word-break: break-word;">
                            {{ Str::limit($req->return_remarks ?? 'No remarks provided.', 10) }}
                        </div>
                        @if(mb_strlen($req->return_remarks ?? '') > 10)
                            <button type="button" class="btn btn-sm p-0 mt-1 remarks-expand-btn" style="font-size: 11px; color: var(--accent-blue); background: none; border: none;" data-full-text="{{ $req->return_remarks ?? '' }}">Show more</button>
                        @endif
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
