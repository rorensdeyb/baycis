@extends('layouts.admin')

@section('content')

<style>
    /* Force the scanner wrapper to match the theme */
    #qr-reader {
        border: none !important;
        background: var(--bg-main);
        color: var(--text-primary);
        border-radius: 12px;
        overflow: hidden;
        width: 100%;
    }
    
    /* Round the actual camera video feed */
    #qr-reader video {
        border-radius: 12px !important;
        object-fit: cover;
    }
    
    /* Theme the Camera Selection Dropdown */
    #qr-reader select {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-color) !important;
        padding: 8px 12px;
        border-radius: 8px;
        margin-bottom: 12px;
        width: 100%;
        cursor: pointer;
    }

    /* Theme the Start/Stop Buttons */
    #qr-reader button {
        background-color: var(--accent-color) !important;
        color: var(--btn-text) !important;
        border: none !important;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        margin: 4px;
    }
    #qr-reader button:hover {
        opacity: 0.85;
    }

    /* Hide the library watermark and extra links */
    #qr-reader a { display: none !important; }
    #qr-reader__dashboard_section_swaplink { display: none !important; }
</style>

<div class="dashboard-wrapper">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">Borrow Requests</h1>
            <p class="form-label text-secondary mb-0">Review, approve, or decline pending asset loan requests.</p>
        </div>
        <button type="button" data-bs-toggle="modal" data-bs-target="#scannerModal" class="btn btn-success fw-bold px-4 py-2 d-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
            <i class="bi bi-qr-code-scan"></i> Scan Request QR
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="ms-2">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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

    <div class="panel-card p-3 mb-4 shadow-sm" style="border-radius: 12px;">
        <form action="{{ route('admin.requests') }}" method="GET" class="row g-3 m-0">
            <div class="col-12 col-md-8 p-0 pe-md-2 position-relative">
                <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control theme-dynamic-input w-100" placeholder="Search Trans ID, Borrower Name, or Property Tag..." style="padding-left: 42px;">
            </div>
            <div class="col-12 col-md-4 p-0 ps-md-2">
                <select name="status" class="form-select theme-dynamic-input w-100 cursor-pointer" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approvals</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved / Borrowed</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <noscript><button type="submit" class="btn btn-primary d-none">Filter</button></noscript>
        </form>
    </div>

    <div id="requests-table" class="panel-card p-0 overflow-hidden shadow-sm" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="admin-table mb-0 align-middle">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3" style="width: 15%;">Date Requested</th>
                        <th class="py-3" style="width: 20%;">Borrower Info</th>
                        <th class="py-3" style="width: 25%;">Asset Details</th>
                        <th class="py-3" style="width: 20%;">Reason / Purpose</th>
                        <th class="text-center py-3" style="width: 10%;">Status</th>
                        <th class="text-end pe-4 py-3" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr style="border-bottom: 1px solid var(--border-color); background-color: {{ $req->status === 'pending' ? 'var(--accent-blue-bg)' : 'transparent' }}; transition: 0.2s;">
                        
                        <td class="ps-4 py-3">
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
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div style="width: 64px; height: 64px; background: var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                                <i class="bi bi-inbox fs-3 text-secondary"></i>
                            </div>
                            <h5 class="fw-bold" style="color: var(--text-primary);">No Borrow Requests Found</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">There are currently no active requests matching your criteria.</p>
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

    <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; background: rgba(25, 135, 84, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-qr-code-scan text-success"></i>
                        </div>
                        Scan Borrower QR
                    </h5>
                    <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body px-4 py-4 text-center">
                    <p class="text-secondary small mb-3">Position the borrower's Fast-Track QR code inside the frame to instantly locate their request.</p>
                    
                    <div style="padding: 16px; border: 2px dashed var(--accent-color); border-radius: 16px; background: var(--bg-main);">
                        <div id="qr-reader"></div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                    <button type="button" class="btn w-100 fw-bold py-2" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Close Camera</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; background: rgba(25, 135, 84, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        Approve Request
                    </h5>
                    <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" method="POST" class="m-0">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <p class="text-secondary mb-4" style="font-size: 15px; line-height: 1.6;">
                            Are you sure you want to approve this transaction? The asset will be officially locked to this borrower and marked as <strong>"Borrowed"</strong> in the inventory.
                        </p>
                        <div>
                            <label class="form-label fw-bold small text-uppercase letter-spacing-1">Admin Remarks (Optional)</label>
                            <input type="text" name="admin_remarks" class="form-control theme-dynamic-input" placeholder="e.g., Approved. Please handle with care.">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold px-4 py-2 flex-grow-1" style="border-radius: 10px;" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span> Processing...'; this.disabled=true; this.form.submit();">
                            Yes, Approve Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; background: rgba(220, 53, 69, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-x-lg"></i>
                        </div>
                        Reject Request
                    </h5>
                    <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST" class="m-0">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <p class="text-secondary mb-4" style="font-size: 15px; line-height: 1.6;">
                            Are you sure you want to decline this request? The asset will instantly be returned to the <strong>"Available"</strong> inventory pool.
                        </p>
                        <div>
                            <label class="form-label fw-bold small text-danger text-uppercase letter-spacing-1">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="admin_remarks" class="form-control theme-dynamic-input" rows="3" placeholder="Please explicitly state why this request is being denied..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Cancel</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4 py-2 flex-grow-1" style="border-radius: 10px;" onclick="if(this.form.checkValidity()){ this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span> Rejecting...'; this.disabled=true; this.form.submit(); }">
                            Yes, Decline Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
                                    <p class="mb-0" style="line-height: 1.5; font-size: 14px;">{{ $req->purpose ?? 'No purpose provided.' }}</p>
                                </div>
                                
                                @if($req->admin_remarks)
                                <div class="mt-3 pt-3" style="border-top: 1px dashed var(--border-color);">
                                    <span class="text-secondary small d-block mb-1">Admin Remarks</span>
                                    <p class="mb-0 fw-semibold {{ $req->status === 'rejected' ? 'text-danger' : 'text-success' }}" style="font-size: 14px;">
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
                
                <div class="modal-footer border-0 pb-4 px-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold w-100" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Close Details</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // ==========================================
    // 1. QR CODE SCANNER LOGIC
    // ==========================================
    const scannerModal = document.getElementById('scannerModal');
    let html5QrcodeScanner;

    if (scannerModal) {
        scannerModal.addEventListener('shown.bs.modal', function () {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", 
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false
            );
            
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });

        scannerModal.addEventListener('hidden.bs.modal', function () {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error("Failed to clear scanner. ", error);
                });
            }
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Stop the camera immediately
        html5QrcodeScanner.clear();

        // Play a subtle success "beep"
        let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');
        audio.play().catch(e => console.log('Audio blocked by browser'));

        // Redirect the Admin to the exact request
        window.location.href = `/admin/requests?search=${encodeURIComponent(decodedText)}`;
    }

    function onScanFailure(error) {
        // Ignore constant failures while it looks for a clear code
    }

    // ==========================================
    // 2. MODAL EVENT DELEGATION
    // ==========================================
    document.addEventListener('click', function(e) {
        
        // Intercept Approve Click
        const approveBtn = e.target.closest('.btn-approve-request');
        if (approveBtn) {
            const reqId = approveBtn.getAttribute('data-id');
            document.getElementById('approveForm').action = `/admin/requests/${reqId}/approve`;
        }

        // Intercept Reject Click
        const rejectBtn = e.target.closest('.btn-reject-request');
        if (rejectBtn) {
            const reqId = rejectBtn.getAttribute('data-id');
            const rejectForm = document.getElementById('rejectForm');
            rejectForm.action = `/admin/requests/${reqId}/reject`;
            rejectForm.querySelector('textarea[name="admin_remarks"]').value = '';
        }
    });

    // Fix scroll position: append #requests-table anchor to all pagination links
    // so clicking next/prev scrolls to the table, not back to the very top.
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#requests-table');
        }
    });
});
</script>
@endsection