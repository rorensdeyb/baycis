<div id="requests-table" class="panel-card p-0 overflow-hidden shadow-sm" style="border-radius: 12px;">
    <div class="table-responsive">
        <table class="admin-table mb-0 align-middle">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                    <th class="ps-4 py-3" style="width: 36px;"><input type="checkbox" id="reqSelectAll" class="form-check-input" style="cursor:pointer;"></th>
                    <th class="py-3" style="width: 15%;">Date Requested</th>
                    <th class="py-3" style="width: 20%;">Borrower Info</th>
                    <th class="py-3" style="width: 25%;">Asset Details</th>
                    <th class="py-3" style="width: 20%;">Reason / Purpose</th>
                    <th class="text-center py-3" style="width: 10%;">Status</th>
                    <th class="text-end pe-4 py-3" style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr data-req-id="{{ $req->id }}" style="border-bottom: 1px solid var(--border-color); background-color: {{ $req->status === 'pending' ? 'var(--accent-blue-bg)' : 'transparent' }}; transition: background-color 0.4s ease;">
                    <td class="ps-4 py-3" style="width:36px;">
                        <input type="checkbox" class="form-check-input req-checkbox" value="{{ $req->id }}" style="cursor:pointer;">
                    </td>
                    <td class="py-3">
                        <div class="fw-bold" style="color: var(--text-primary);">{{ $req->created_at->format('M d, Y') }}</div>
                        <div class="small text-secondary"><i class="bi bi-clock me-1"></i>{{ $req->created_at->format('h:i A') }}</div>
                    </td>

                    <td class="py-3">
                        <div class="fw-bold" style="color: var(--text-primary);">{{ $req->user->name ?? 'Unknown User' }}</div>
                        <div class="small text-secondary">{{ $req->user->email ?? 'No email provided' }}</div>
                    </td>

                    <td class="py-3">
                        <div class="fw-bold" style="color: var(--text-primary);">{{ $req->item->name ?? 'Unknown Item' }}</div>
                        <div class="small text-secondary font-monospace"><i class="bi bi-upc-scan me-1"></i>{{ $req->item->property_tag ?? 'N/A' }}</div>
                    </td>

                    <td class="py-3">
                        <div class="small" style="color: var(--text-primary); line-height: 1.4;">{{ Str::limit($req->purpose, 60) }}</div>
                    </td>

                    <td class="text-center py-3">
                        @if($req->status === 'pending')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">PENDING</span>
                        @elseif(in_array($req->status, ['approved', 'active']))
                            <span class="badge bg-success px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">APPROVED</span>
                        @elseif($req->status === 'rejected')
                            <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">REJECTED</span>
                        @elseif($req->status === 'cancelled')
                            <span class="badge bg-secondary px-3 py-2 rounded-pill fw-bold text-decoration-line-through" style="letter-spacing: 0.5px;">CANCELLED</span>
                        @elseif($req->status === 'returned')
                            <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">RETURNED</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 rounded-pill fw-bold text-uppercase" style="letter-spacing: 0.5px;">{{ $req->status }}</span>
                        @endif
                    </td>

                    <td class="text-end pe-4 py-3">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-light fw-bold px-3 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#viewModal-{{ $req->id }}" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary);" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>

                            @if($req->status === 'pending')
                                <button type="button" class="btn btn-sm btn-success fw-bold px-3 d-flex align-items-center justify-content-center btn-approve-request" data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#approveModal" title="Approve Request">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3 d-flex align-items-center justify-content-center btn-reject-request" data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#rejectModal" title="Reject Request">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @elseif($req->status === 'approved' && $req->isWalkin())
                                <button type="button" class="btn btn-sm btn-outline-warning fw-bold px-3 d-flex align-items-center justify-content-center btn-cancel-borrow" data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#cancelBorrowModal" title="Cancel Borrow (No-show)">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        @include('components.empty-state', [
                            'icon' => 'bi-inbox',
                            'title' => 'No Borrow Requests Found',
                            'message' => 'All requests have been reviewed. No pending items match your current filters.',
                            'actionUrl' => '/admin/inventory',
                            'actionLabel' => 'Browse Inventory',
                        ])
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($requests->hasPages())
        <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@foreach($requests as $req)
<div class="modal fade" id="viewModal-{{ $req->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <div style="width: 36px; height: 36px; background: var(--bg-main); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                        <i class="bi bi-file-earmark-text text-secondary"></i>
                    </div>
                    Request Details <span class="text-secondary fs-6 ms-2">#REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</span>
                </h5>
                <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Borrower Information</h6>
                        <div class="p-3 mb-4" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div class="mb-3">
                                <span class="text-secondary small d-block mb-1">Full Name</span>
                                <span class="fw-bold" style="font-size: 15px;">{{ $req->user->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-secondary small d-block mb-1">Email Address</span>
                                <span>{{ $req->user->email ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Request Details</h6>
                        <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Current Status</span>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning text-dark">PENDING</span>
                                @elseif(in_array($req->status, ['approved', 'active']))
                                    <span class="badge bg-success">APPROVED</span>
                                @elseif($req->status === 'rejected')
                                    <span class="badge bg-danger">REJECTED</span>
                                @elseif($req->status === 'cancelled')
                                    <span class="badge bg-secondary">CANCELLED</span>
                                @else
                                    <span class="badge bg-secondary text-uppercase">{{ $req->status }}</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <span class="text-secondary small d-block mb-1">Date Requested</span>
                                <span class="fw-semibold">{{ $req->created_at->format('F d, Y - h:i A') }}</span>
                            </div>
                            <div>
                                <span class="text-secondary small d-block mb-1">Reason / Purpose</span>
                                <p class="mb-0" style="line-height: 1.5; font-size: 14px; word-break: break-word; overflow-wrap: break-word; white-space: pre-wrap;">{{ $req->purpose ?? 'No purpose provided.' }}</p>
                            </div>
                            
                            @if($req->admin_remarks)
                            <div class="mt-3 pt-3" style="border-top: 1px dashed var(--border-color);">
                                <span class="text-secondary small d-block mb-1">Admin Remarks</span>
                                <p class="mb-0 fw-semibold {{ $req->status === 'rejected' ? 'text-danger' : 'text-success' }}" style="font-size: 14px; word-break: break-word; overflow-wrap: break-word; white-space: pre-wrap;">
                                    {{ $req->admin_remarks }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Asset Information</h6>
                        <div class="p-3 mb-4" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom: 1px dashed var(--border-color);">
                                <div style="width: 48px; height: 48px; background: var(--bg-surface); border-radius: 8px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-box-seam fs-4 text-secondary"></i>
                                </div>
                                <div>
                                    <span class="fw-bold d-block" style="font-size: 15px;">{{ $req->item->name ?? 'Unknown Item' }}</span>
                                    <span class="badge bg-secondary" style="font-size: 10px;">{{ $req->item->category->name ?? 'Uncategorized' }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="text-secondary small d-block mb-1">Property Tag</span>
                                <span class="font-monospace fw-bold">{{ $req->item->property_tag ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-secondary small d-block mb-1">Serial Number</span>
                                <span class="font-monospace">{{ $req->item->serial_number ?? 'N/A' }}</span>
                            </div>
                        </div>

                        @if($req->qr_code_hash && $req->status === 'pending')
                        <div class="text-center p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                            <span class="text-secondary small d-block mb-2 fw-bold text-uppercase">Borrower Fast-Track QR</span>
                            <img src="https://quickchart.io/qr?size=140&text={{ urlencode($req->qr_code_hash ?? '') }}" alt="QR Code" class="rounded shadow-sm" style="border: 4px solid var(--bg-surface);">
                            <p class="small text-secondary mt-2 mb-0" style="font-size: 12px;">This code matches the borrower's digital receipt.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-0 pb-4 px-4 pt-0 d-flex gap-2">
                @if($req->status === 'pending')
                <button type="button" class="btn btn-success fw-bold px-4 py-2 flex-grow-1 btn-approve-request" data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#approveModal" style="border-radius: 10px;">
                    <i class="bi bi-check-lg me-1"></i> Approve Request
                </button>
                <button type="button" class="btn btn-outline-danger fw-bold px-4 py-2 flex-grow-1 btn-reject-request" data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#rejectModal" style="border-radius: 10px;">
                    <i class="bi bi-x-lg me-1"></i> Reject
                </button>
                @elseif($req->status === 'approved' && $req->isWalkin())
                <button type="button" class="btn btn-outline-warning fw-bold px-4 py-2 flex-grow-1 btn-cancel-borrow" data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#cancelBorrowModal" style="border-radius: 10px;">
                    <i class="bi bi-x-circle me-1"></i> Cancel Borrow
                </button>
                @endif
                <button type="button" class="btn btn-light px-4 py-2 fw-bold {{ $req->status === 'pending' || $req->status === 'approved' ? '' : 'w-100' }}" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Close Details</button>
            </div>
        </div>
    </div>
</div>
@endforeach
