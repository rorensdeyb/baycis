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

    /* Ensure modal footer buttons are always visible */
    .modal-footer {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .modal-footer .btn {
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    .modal-dialog {
        overflow-y: auto;
    }
    .modal-content {
        overflow-y: visible;
    }
    .modal-body {
        overflow-y: visible;
    }
</style>

<div class="dashboard-wrapper">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">Borrow Requests</h1>
            <p class="form-label text-secondary mb-0">Review, approve, or decline pending asset loan requests.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" data-bs-toggle="modal" data-bs-target="#initiateBorrowModal" class="btn btn-primary fw-bold px-4 py-2 d-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                <i class="bi bi-plus-circle"></i> Initiate Borrow
            </button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#scannerModal" class="btn btn-success fw-bold px-4 py-2 d-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                <i class="bi bi-qr-code-scan"></i> Scan Request QR
            </button>
        </div>
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

    {{-- W1.1: Batch Approve/Reject Floating Bar --}}
    <div class="bulk-bar" id="reqBulkBar" style="display:none;">
        <span class="bulk-count" id="reqBulkCount">0 selected</span>
        <div class="bulk-actions">
            <button class="bulk-btn bulk-btn-success" onclick="bulkApproveRequests()" style="border-color:var(--accent-green);color:var(--accent-green);"><i class="bi bi-check-lg"></i> Approve Selected</button>
            <button class="bulk-btn bulk-btn-danger" onclick="bulkRejectRequests()"><i class="bi bi-x-lg"></i> Reject Selected</button>
            <button class="bulk-btn" onclick="bulkReqClear()"><i class="bi bi-x-lg"></i> Clear</button>
        </div>
    </div>

    <div id="requestsTableContainer">
        @include('admin.partials.borrow-requests-table')
    </div>

    <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <div class="icon-circle icon-circle-green">
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

    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2">
                        <div class="icon-circle icon-circle-green">
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

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                        <div class="icon-circle icon-circle-red">
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



{{-- ══════════════════════════════════════════
     CANCEL BORROW CONFIRMATION MODAL
     ══════════════════════════════════════════ --}}
<div class="modal fade" id="cancelBorrowModal" tabindex="-1" aria-hidden="true" data-centered>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-warning d-flex align-items-center gap-2">
                    <div class="icon-circle icon-circle-yellow">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    Cancel Borrow Request
                </h5>
                <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelBorrowForm" method="POST" class="m-0">
                @csrf
                <div class="modal-body px-4 py-4">
                    <p class="text-secondary mb-4" style="font-size: 15px; line-height: 1.6;">
                        This will cancel the approved borrow request and return the item to <strong>Available</strong> inventory. 
                        The borrower will be notified. This action cannot be undone.
                    </p>
                    <div>
                        <label class="form-label fw-bold small text-uppercase letter-spacing-1">Reason for Cancellation</label>
                        <textarea name="cancel_reason" class="form-control theme-dynamic-input" rows="2" placeholder="e.g., Borrower did not claim within the day." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Keep Request</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4 py-2 flex-grow-1" style="border-radius: 10px;" onclick="if(this.form.checkValidity()){ this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span> Cancelling...'; this.disabled=true; this.form.submit(); }">
                        Yes, Cancel Borrow
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     WALK-IN BORROW INITIATION MODAL (4-Step)
     ══════════════════════════════════════════ --}}
<div class="modal fade" id="initiateBorrowModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-centered>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <div class="icon-circle icon-circle-blue">
                        <i class="bi bi-plus-circle text-primary"></i>
                    </div>
                    <span id="initModalTitle">Initiate Walk-in Borrow</span>
                </h5>
                <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal" onclick="resetInitBorrowModal()"></button>
            </div>

            {{-- Step Indicator --}}
            <div class="px-4 pt-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="init-step-indicator active" data-step="1">
                        <div class="init-step-circle">1</div>
                        <span class="init-step-label">Borrower</span>
                    </div>
                    <div class="init-step-line"></div>
                    <div class="init-step-indicator" data-step="2">
                        <div class="init-step-circle">2</div>
                        <span class="init-step-label">Asset</span>
                    </div>
                    <div class="init-step-line"></div>
                    <div class="init-step-indicator" data-step="3">
                        <div class="init-step-circle">3</div>
                        <span class="init-step-label">Details</span>
                    </div>
                    <div class="init-step-line"></div>
                    <div class="init-step-indicator" data-step="4">
                        <div class="init-step-circle">4</div>
                        <span class="init-step-label">Confirm</span>
                    </div>
                </div>
            </div>

            <div class="modal-body px-4 py-4">
                {{-- Error display --}}
                <div id="initBorrowError" class="alert alert-danger d-none py-2 px-3 mb-3" style="border-radius: 8px; font-size: 13px;"></div>

                {{-- ═══ STEP 1: SELECT BORROWER ═══ --}}
                <div class="init-step-content" id="initStep1">
                    <h6 class="fw-bold mb-3" style="font-size: 14px;">Select Teacher / Borrower</h6>
                    <div class="mb-3 position-relative">
                        <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); z-index: 5;"></i>
                        <input type="text" id="initBorrowerSearch" class="form-control" placeholder="Search by name, email, or Teacher ID..." style="padding-left: 38px; border-radius: 10px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
                    </div>
                    <div id="initBorrowerList" class="d-flex flex-column gap-1" style="max-height: 240px; overflow-y: auto;">
                        <div class="text-center text-secondary py-5">
                            <i class="bi bi-search fs-2 d-block mb-2" style="opacity: 0.3;"></i>
                            <div class="small">Type a name, email, or Teacher ID to find a borrower</div>
                        </div>
                    </div>
                    <input type="hidden" id="initSelectedBorrowerId" value="">
                    <input type="hidden" id="initSelectedBorrowerName" value="">
                </div>

                {{-- ═══ STEP 2: SELECT ASSET ═══ --}}
                <div class="init-step-content d-none" id="initStep2">
                    <h6 class="fw-bold mb-1" style="font-size: 14px;">Select Available Asset</h6>
                    <p class="text-secondary small mb-3">Showing only items with <strong>Available</strong> status.</p>
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-7 position-relative">
                            <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); z-index: 5;"></i>
                            <input type="text" id="initAssetSearch" class="form-control" placeholder="Search by item name or property tag..." style="padding-left: 38px; border-radius: 10px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>
                        <div class="col-12 col-md-5">
                            <select id="initAssetCategory" class="form-select" style="border-radius: 10px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer;">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                    </div>
                    <div id="initAssetList" class="d-flex flex-column gap-1" style="max-height: 240px; overflow-y: auto;">
                        <div class="text-center text-secondary py-5">
                            <i class="bi bi-box fs-2 d-block mb-2" style="opacity: 0.3;"></i>
                            <div class="small">Loading available items...</div>
                        </div>
                    </div>
                    <input type="hidden" id="initSelectedAssetId" value="">
                    <input type="hidden" id="initSelectedAssetName" value="">
                    <input type="hidden" id="initSelectedAssetTag" value="">
                </div>

                {{-- ═══ STEP 3: PURPOSE & REMARKS ═══ --}}
                <div class="init-step-content d-none" id="initStep3">
                    <h6 class="fw-bold mb-3" style="font-size: 14px;">Purpose & Remarks</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px; color: var(--text-secondary);">
                            Reason for Borrowing <span class="text-danger">*</span>
                        </label>
                        @php
                            $commonReasons = [
                                'Classroom Instruction / Lecture',
                                'School Event / Activity',
                                'Student Project / Presentation',
                                'Club / Organization Meeting',
                                'Maintenance / Repair'
                            ];
                        @endphp
                        <input type="hidden" id="initPurposeValue" value="">
                        <div class="d-flex flex-wrap gap-2 mb-3" id="initPurposeChips">
                            @foreach($commonReasons as $reason)
                                <button type="button" class="btn init-purpose-chip" data-value="{{ $reason }}">
                                    {{ str_replace([' / Lecture', ' / Activity', ' / Presentation', ' / Organization Meeting', ' / Repair'], '', $reason) }}
                                </button>
                            @endforeach
                            <button type="button" class="btn init-purpose-chip" data-value="Other">Other...</button>
                        </div>
                        <div id="initOtherPurposeContainer" style="display: none;">
                            <textarea id="initPurposeOther" class="form-control" rows="2" placeholder="Please specify your exact reason..." style="border-radius: 10px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px; color: var(--text-secondary);">
                            Admin Remarks
                        </label>
                        <textarea id="initRemarks" class="form-control" rows="2" placeholder="Optional — internal notes about this walk-in transaction" style="border-radius: 10px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);"></textarea>
                    </div>
                </div>

                {{-- ═══ STEP 4: CONFIRMATION + PIN ═══ --}}
                <div class="init-step-content d-none" id="initStep4">
                    <h6 class="fw-bold mb-3" style="font-size: 14px;">Confirm & Authorize</h6>
                    
                    <div class="p-3 mb-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="icon-circle-lg icon-circle-blue">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                            <div>
                                <span class="text-secondary small d-block">Borrower</span>
                                <span class="fw-bold" id="confirmBorrowerName" style="font-size: 15px;">—</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-lg icon-circle-green">
                                <i class="bi bi-box-seam text-success"></i>
                            </div>
                            <div>
                                <span class="text-secondary small d-block">Asset</span>
                                <span class="fw-bold" id="confirmAssetName" style="font-size: 15px;">—</span>
                                <span class="text-secondary small d-block font-monospace" id="confirmAssetTag"></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 mb-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                        <span class="text-secondary small d-block mb-1">Purpose</span>
                        <p class="mb-0" id="confirmPurpose" style="font-size: 14px;">—</p>
                    </div>

                    <p class="text-center text-secondary mb-2" style="font-size: 12px;">Enter your <strong>4-digit PIN</strong> to authorize this borrow:</p>
                    <div class="d-flex justify-content-center gap-2 mb-2" id="initPinDots">
                        <div class="init-pin-dot" id="initDot0"></div>
                        <div class="init-pin-dot" id="initDot1"></div>
                        <div class="init-pin-dot" id="initDot2"></div>
                        <div class="init-pin-dot" id="initDot3"></div>
                    </div>
                    <div class="text-danger text-center mb-2" id="initPinError" style="font-size: 12px; min-height: 20px;"></div>
                    <input type="hidden" id="initPinValue" value="">

                    {{-- Desktop keyboard input --}}
                    <div class="d-none d-md-block mb-3 text-center">
                        <input type="password" id="initKeyboardPin" class="form-control text-center" maxlength="4" inputmode="numeric" pattern="[0-9]{4}" autocomplete="off" placeholder="&bull;&bull;&bull;&bull;" style="font-size: 24px; letter-spacing: 8px; font-weight: 700; max-width: 160px; margin: 0 auto; border-radius: 10px; background: var(--bg-main); border: 2px solid var(--border-color); color: var(--text-primary); padding: 10px;">
                    </div>

                    {{-- Numpad (mobile) --}}
                    <div class="d-flex d-md-none flex-wrap justify-content-center gap-2" style="max-width: 220px; margin: 0 auto;">
                        @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                            <button type="button" class="btn btn-lg fw-bold init-numpad-key" onclick="initPinPad('{{ $k }}')" style="width: 60px; height: 60px; border-radius: 50%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 20px;">{{ $k }}</button>
                        @endforeach
                        <button type="button" class="btn btn-lg" style="width: 60px; height: 60px; border-radius: 50%; background: transparent; border: none; cursor: default;" disabled></button>
                        <button type="button" class="btn btn-lg fw-bold init-numpad-key" onclick="initPinPad('0')" style="width: 60px; height: 60px; border-radius: 50%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 20px;">0</button>
                        <button type="button" class="btn btn-lg init-numpad-key" onclick="initPinDel()" style="width: 60px; height: 60px; border-radius: 50%; background: transparent; border: none; color: var(--text-secondary); font-size: 18px;">&#9003;</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pb-4 px-4 pt-0 d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-light fw-semibold px-4" id="initBackBtn" onclick="initPrevStep()" style="border-radius: 8px; background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color);">
                    <i class="bi bi-chevron-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary fw-bold px-4" id="initNextBtn" onclick="initNextStep()" style="border-radius: 8px; background-color: var(--accent-blue); border: none;">
                    Continue <i class="bi bi-chevron-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-success fw-bold px-4 d-none" id="initSubmitBtn" onclick="initSubmitBorrow()" style="border-radius: 8px;">
                    <i class="bi bi-check-lg me-1"></i> Confirm & Borrow
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.init-step-indicator { display: flex; align-items: center; gap: 6px; }
.init-step-circle {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    background: var(--border-color);
    color: var(--text-secondary);
    transition: all 0.2s ease;
}
.init-step-indicator.active .init-step-circle {
    background: var(--accent-blue);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
}
.init-step-indicator.completed .init-step-circle {
    background: var(--accent-green);
    color: #fff;
}
.init-step-label { font-size: 11px; font-weight: 600; color: var(--text-secondary); white-space: nowrap; }
.init-step-indicator.active .init-step-label { color: var(--accent-blue); }
.init-step-indicator.completed .init-step-label { color: var(--accent-green); }
.init-step-line { flex: 1; height: 2px; background: var(--border-color); margin: 0 4px; min-width: 16px; }
.init-step-line.completed { background: var(--accent-green); }
.init-pin-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    background: transparent;
    border: 2px solid var(--border-color);
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.init-pin-dot.filled {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
    transform: scale(1.3);
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.6);
}
.init-pin-dot.error {
    background: var(--accent-red);
    border-color: var(--accent-red);
    animation: initPinShake 0.4s ease;
}
.init-numpad-key:active { transform: scale(0.92) !important; background: #d1d5db !important; }
[data-theme="dark"] .init-numpad-key { background: #1f2937 !important; color: #f3f4f6 !important; border-color: rgba(255,255,255,0.08) !important; }
[data-theme="dark"] .init-numpad-key:active { background: #374151 !important; }

@keyframes initPinShake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}
.init-purpose-chip {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: var(--bg-main);
    border: 1.5px solid var(--border-color);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.15s ease;
}
.init-purpose-chip:hover {
    background: rgba(13, 110, 253, 0.08);
    border-color: var(--accent-blue);
}
.init-purpose-chip.active {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
    color: #fff;
}
[data-theme="dark"] .init-purpose-chip { border-color: rgba(255,255,255,0.1); }
[data-theme="dark"] .init-purpose-chip:hover { background: rgba(255,255,255,0.04); }
.init-borrower-item, .init-asset-item {
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1.5px solid transparent;
}
.init-borrower-item:hover, .init-asset-item:hover {
    background: var(--bg-surface);
    border-color: var(--border-color);
}
.init-borrower-item.selected, .init-asset-item.selected {
    background: rgba(13, 110, 253, 0.08);
    border-color: var(--accent-blue);
}
[data-theme="dark"] .init-borrower-item:hover, [data-theme="dark"] .init-asset-item:hover {
    background: rgba(255,255,255,0.03);
}
</style>

</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
// ── Global camera error message helper ──
function showCameraError(message) {
    const reader = document.getElementById('qr-reader');
    if (!reader) return;
    reader.innerHTML = '' +
        '<div style="padding: 32px 16px; text-align: center;">' +
        '   <div style="width: 56px; height: 56px; background: var(--accent-red-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">' +
        '       <i class="bi bi-camera-video-off fs-4" style="color: var(--accent-red);"></i>' +
        '   </div>' +
        '   <p style="font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Camera Unavailable</p>' +
        '   <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px; max-width: 280px; margin-left: auto; margin-right: auto;">' + message + '</p>' +
        '   <button onclick="initScanner()" class="btn btn-sm btn-light fw-bold" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 8px; padding: 6px 14px;">' +
        '       <i class="bi bi-arrow-repeat me-1"></i>Try Again' +
        '   </button>' +
        '</div>';
}

document.addEventListener('DOMContentLoaded', function () {
    
    // ==========================================
    // 1. QR CODE SCANNER LOGIC
    // ==========================================
    const scannerModal = document.getElementById('scannerModal');
    let html5QrcodeScanner;

    // ── Initialise scanner (called on modal open or retry) ──
    window.initScanner = function () {
        const reader = document.getElementById('qr-reader');
        if (!reader) return;

        // Reset the reader area to placeholder while we check permissions
        reader.innerHTML = '<div style="padding: 40px 16px; text-align: center;">' +
            '   <div class="spinner-border text-secondary mb-3" role="status" style="width: 32px; height: 32px;"></div>' +
            '   <p class="small text-secondary">Checking camera permission...</p>' +
            '</div>';

        // First check if camera permission is available
        navigator.permissions.query({ name: 'camera' }).then(function(permissionStatus) {
            if (permissionStatus.state === 'denied') {
                showCameraError('Camera access was blocked in your browser settings. Please go to your site settings and allow camera access, then try again.');
                return;
            }

            // Check if any cameras exist before launching
            Html5Qrcode.getCameras().then(function(devices) {
                if (devices && devices.length > 0) {
                    startScanner();
                } else {
                    showCameraError('No camera found on this device. Connect a webcam or use a mobile device with a camera.');
                }
            }).catch(function(err) {
                console.warn('Camera enumeration failed:', err);
                // Fallback: try to start scanner anyway (some browsers don't support enumerateDevices)
                startScanner();
            });
        }).catch(function() {
            // Permission query API not supported (some browsers) — start scanner directly
            startScanner();
        });
    };

    function startScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(function(){});
        }

        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", 
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false
        );
        
        html5QrcodeScanner.render(onScanSuccess, function(error) {
            // Forward camera errors to the user
            if (error && error.includes && error.includes('NotAllowedError')) {
                showCameraError('Camera permission was denied. Click the camera icon in your browser address bar to allow access, then click "Try Again".');
            }
        });
    }

    if (scannerModal) {
        scannerModal.addEventListener('shown.bs.modal', function () {
            // Small delay to ensure modal transition completes for user gesture
            setTimeout(function() {
                initScanner();
            }, 300);
        });

        scannerModal.addEventListener('hidden.bs.modal', function () {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(function(error) {
                    console.error("Failed to clear scanner. ", error);
                });
                html5QrcodeScanner = null;
            }
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (!html5QrcodeScanner) return;
        
        // Stop the camera immediately
        try { html5QrcodeScanner.clear(); } catch(e) {}

        // Play a subtle success "beep"
        let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');
        audio.play().catch(function(){});

        // Redirect the Admin to the exact request
        window.location.href = '/admin/requests?search=' + encodeURIComponent(decodedText);
    }

    // ==========================================
    // 2. MODAL EVENT DELEGATION
    // ==========================================
    document.addEventListener('click', function(e) {
        
        // Intercept Approve Click
        const approveBtn = e.target.closest('.btn-approve-request');
        if (approveBtn) {
            const reqId = approveBtn.getAttribute('data-id');
            document.getElementById('approveForm').action = '/admin/requests/' + reqId + '/approve';
        }

        // Intercept Reject Click
        const rejectBtn = e.target.closest('.btn-reject-request');
        if (rejectBtn) {
            const reqId = rejectBtn.getAttribute('data-id');
            const rejectForm = document.getElementById('rejectForm');
            rejectForm.action = '/admin/requests/' + reqId + '/reject';
            rejectForm.querySelector('textarea[name="admin_remarks"]').value = '';
        }
    });

    // Fix scroll position: append #requests-table anchor to all pagination links
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#requests-table');
        }
    });
    
    // ── W1.1: Batch Select Logic ──
    var reqSelectAll = document.getElementById('reqSelectAll');
    if (reqSelectAll) {
        reqSelectAll.addEventListener('change', function() {
            document.querySelectorAll('.req-checkbox').forEach(function(cb) { cb.checked = reqSelectAll.checked; });
            updateReqBulkBar();
        });
    }
    document.querySelectorAll('.req-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateReqBulkBar);
    });
    function updateReqBulkBar() {
        var checkboxes = document.querySelectorAll('.req-checkbox:checked');
        var count = checkboxes.length;
        var bar = document.getElementById('reqBulkBar');
        var countEl = document.getElementById('reqBulkCount');
        if (count > 0) {
            bar.style.display = 'flex';
            countEl.textContent = count + ' selected';
        } else {
            bar.style.display = 'none';
        }
    }
    window.bulkApproveRequests = function() {
        var ids = [];
        document.querySelectorAll('.req-checkbox:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        if (!confirm('Approve ' + ids.length + ' pending requests?')) return;
        var btn = document.querySelector('#reqBulkBar .bulk-btn-success');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Approving...';
        btn.disabled = true;
        fetch('/admin/requests/bulk-approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ ids: ids.join(',') })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.success) { window.location.reload(); }
            else { alert('Error approving requests: ' + (data && data.message ? data.message : 'Server error')); btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Approve Selected'; }
        })
        .catch(function() { alert('Network error - could not approve requests. Check server connection.'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Approve Selected'; });
    };
    window.bulkRejectRequests = function() {
        var ids = [];
        document.querySelectorAll('.req-checkbox:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        var reason = prompt('Enter reason for rejection (optional):');
        if (reason === null) return; // cancelled
        if (!confirm('Reject ' + ids.length + ' pending requests?')) return;
        var btn = document.querySelector('#reqBulkBar .bulk-btn-danger');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Rejecting...';
        btn.disabled = true;
        fetch('/admin/requests/bulk-reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ ids: ids.join(','), remarks: reason || '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.success) { window.location.reload(); }
            else { alert('Error rejecting requests: ' + (data && data.message ? data.message : 'Server error')); btn.disabled = false; btn.innerHTML = '<i class="bi bi-x-lg"></i> Reject Selected'; }
        })
        .catch(function() { alert('Network error - could not reject requests. Check server connection.'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-x-lg"></i> Reject Selected'; });
    };
    window.bulkReqClear = function() {
        document.querySelectorAll('.req-checkbox').forEach(function(cb) { cb.checked = false; });
        if (reqSelectAll) reqSelectAll.checked = false;
        updateReqBulkBar();
    };
});

// ═══════════════════════════════════════════════════════════════════
// WALK-IN BORROW INITIATION (4-Step Modal)
// ═══════════════════════════════════════════════════════════════════

var initCurrentStep = 1;
var initBorrowers = [];
var initAssets = [];
var initPin = '';

// Read CSRF token from meta tag (not affected by admin layout IIFE scope)
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// ── Modal Open: Load borrowers & available items ──
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('initiateBorrowModal');
    if (!modal) return;
    
    modal.addEventListener('shown.bs.modal', function() {
        resetInitBorrowModal();
        loadInitBorrowers();
        loadInitAssets();
    });
});

function resetInitBorrowModal() {
    initCurrentStep = 1;
    initPin = '';
    document.getElementById('initSelectedBorrowerId').value = '';
    document.getElementById('initSelectedBorrowerName').value = '';
    document.getElementById('initSelectedAssetId').value = '';
    document.getElementById('initSelectedAssetName').value = '';
    document.getElementById('initSelectedAssetTag').value = '';
    document.getElementById('initPurposeValue').value = '';
    document.getElementById('initPurposeOther').value = '';
    document.getElementById('initOtherPurposeContainer').style.display = 'none';
    document.querySelectorAll('#initPurposeChips .init-purpose-chip').forEach(function(c) { c.classList.remove('active'); });
    document.getElementById('initRemarks').value = '';
    document.getElementById('initBorrowerSearch').value = '';
    document.getElementById('initAssetSearch').value = '';
    document.getElementById('initPinValue').value = '';
    document.getElementById('initPinError').textContent = '';
    document.getElementById('initBorrowError').classList.add('d-none');
    var kb = document.getElementById('initKeyboardPin');
    if (kb) kb.value = '';
    // Reset dots
    for (var i = 0; i < 4; i++) {
        var d = document.getElementById('initDot' + i);
        if (d) d.classList.remove('filled', 'error');
    }
    showInitStep(1);
}

function showInitStep(step) {
    initCurrentStep = step;
    
    // Hide all step contents
    document.getElementById('initStep1').classList.add('d-none');
    document.getElementById('initStep2').classList.add('d-none');
    document.getElementById('initStep3').classList.add('d-none');
    document.getElementById('initStep4').classList.add('d-none');
    document.getElementById('initStep' + step).classList.remove('d-none');
    
    // Update step indicators
    for (var s = 1; s <= 4; s++) {
        var ind = document.querySelector('.init-step-indicator[data-step="' + s + '"]');
        var line = ind ? ind.nextElementSibling : null;
        if (ind) {
            ind.classList.remove('active', 'completed');
            if (s < step) ind.classList.add('completed');
            if (s === step) ind.classList.add('active');
        }
        if (line && line.classList.contains('init-step-line')) {
            line.classList.toggle('completed', s < step);
        }
    }
    
    // Modal title
    var titles = ['', 'Select Borrower', 'Select Asset', 'Purpose & Remarks', 'Confirm & Authorize'];
    document.getElementById('initModalTitle').textContent = titles[step] || 'Initiate Walk-in Borrow';
    
    // Back button
    var backBtn = document.getElementById('initBackBtn');
    if (step === 1) {
        backBtn.disabled = true;
        backBtn.style.opacity = '0.4';
    } else {
        backBtn.disabled = false;
        backBtn.style.opacity = '1';
    }
    
    // Next / Submit buttons
    document.getElementById('initNextBtn').classList.add('d-none');
    document.getElementById('initSubmitBtn').classList.add('d-none');
    if (step < 4) {
        document.getElementById('initNextBtn').classList.remove('d-none');
        updateInitNextBtn();
    } else {
        document.getElementById('initSubmitBtn').classList.remove('d-none');
        // Auto-focus keyboard PIN input
        setTimeout(function() {
            var kb = document.getElementById('initKeyboardPin');
            if (kb && window.getComputedStyle(kb).display !== 'none') kb.focus();
        }, 200);
    }
    
    // Update confirmation display
    if (step === 4) {
        document.getElementById('confirmBorrowerName').textContent = document.getElementById('initSelectedBorrowerName').value || '—';
        document.getElementById('confirmAssetName').textContent = document.getElementById('initSelectedAssetName').value || '—';
        document.getElementById('confirmAssetTag').textContent = document.getElementById('initSelectedAssetTag').value ? 'Tag: ' + document.getElementById('initSelectedAssetTag').value : '';
        document.getElementById('confirmPurpose').textContent = document.getElementById('initPurposeValue').value.trim() || document.getElementById('initPurposeOther').value.trim() || '—';
    }
}

function updateInitNextBtn() {
    var btn = document.getElementById('initNextBtn');
    var disabled = true;
    if (initCurrentStep === 1) disabled = !document.getElementById('initSelectedBorrowerId').value;
    if (initCurrentStep === 2) disabled = !document.getElementById('initSelectedAssetId').value;
    if (initCurrentStep === 3) {
        var pv = document.getElementById('initPurposeValue').value.trim();
        var otherVal = document.getElementById('initPurposeOther').value.trim();
        disabled = !pv && !otherVal;
    }
    btn.disabled = disabled;
    btn.style.opacity = disabled ? '0.5' : '1';
}

function initNextStep() {
    if (initCurrentStep < 4) showInitStep(initCurrentStep + 1);
}

function initPrevStep() {
    if (initCurrentStep > 1) showInitStep(initCurrentStep - 1);
}

// ── Load borrowers from server ──
async function loadInitBorrowers() {
    try {
        var res = await fetch('/api/admin/borrowers', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        var data = await res.json();
        initBorrowers = data.users || [];
        // Don't render — wait for the admin to type
        renderInitBorrowers(document.getElementById('initBorrowerSearch').value);
    } catch (e) {
        var list = document.getElementById('initBorrowerList');
        list.innerHTML = '<div class="text-center text-danger py-3 small">Could not load borrowers. ' +
            '<button class="btn btn-sm btn-link p-0 fw-bold text-decoration-none" onclick="loadInitBorrowers()" style="color: var(--accent-blue);">Retry</button></div>';
    }
}

function renderInitBorrowers(query) {
    var list = document.getElementById('initBorrowerList');
    var q = query.toLowerCase().trim();
    
    // Show placeholder until the admin types something
    if (!q) {
        list.innerHTML = '<div class="text-center text-secondary py-5">' +
            '<i class="bi bi-search fs-2 d-block mb-2" style="opacity: 0.3;"></i>' +
            '<div class="small">Type a name, email, or Teacher ID to find a borrower</div></div>';
        return;
    }
    
    var filtered = initBorrowers.filter(function(b) {
        return (b.name && b.name.toLowerCase().includes(q)) ||
               (b.email && b.email.toLowerCase().includes(q)) ||
               (b.teacher_id && b.teacher_id.toLowerCase().includes(q));
    });
    
    if (filtered.length === 0) {
        list.innerHTML = '<div class="text-center text-secondary py-4 small">No borrowers found matching "' + _escHtml(query) + '".</div>';
        return;
    }
    
    var selectedId = document.getElementById('initSelectedBorrowerId').value;
    var html = '';
    filtered.forEach(function(b) {
        var sel = b.id == selectedId ? 'selected' : '';
        html += '<div class="init-borrower-item ' + sel + '" data-id="' + b.id + '" data-name="' + _escAttr(b.name || 'Unknown') + '" onclick="selectInitBorrower(this)">' +
                '<div class="fw-bold" style="font-size: 14px;">' + _escHtml(b.name || 'Unknown') + '</div>' +
                '<div class="small text-secondary">' + _escHtml(b.email || '') + (b.teacher_id ? ' &middot; ' + _escHtml(b.teacher_id) : '') + '</div>' +
                '</div>';
    });
    list.innerHTML = html;
}

// ── Load available items from server ──
async function loadInitAssets() {
    try {
        var res = await fetch('/api/admin/available-items', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        var data = await res.json();
        initAssets = data.items || [];
        // Populate category filter from loaded assets
        populateInitCategoryFilter();
        renderInitAssets('');
    } catch (e) {
        var list = document.getElementById('initAssetList');
        list.innerHTML = '<div class="text-center text-danger py-3 small">Could not load available items. ' +
            '<button class="btn btn-sm btn-link p-0 fw-bold text-decoration-none" onclick="loadInitAssets()" style="color: var(--accent-blue);">Retry</button></div>';
    }
}

function populateInitCategoryFilter() {
    var sel = document.getElementById('initAssetCategory');
    if (!sel) return;
    // Get unique categories from loaded assets
    var cats = {};
    initAssets.forEach(function(a) {
        if (a.category && a.category.id) {
            cats[a.category.id] = a.category.name;
        }
    });
    var html = '<option value="">All Categories</option>';
    Object.keys(cats).forEach(function(id) {
        html += '<option value="' + id + '">' + _escHtml(cats[id]) + '</option>';
    });
    sel.innerHTML = html;
}

function renderInitAssets(query) {
    var list = document.getElementById('initAssetList');
    var q = (query || '').toLowerCase();
    var catId = document.getElementById('initAssetCategory')?.value || '';
    
    var filtered = initAssets.filter(function(a) {
        // Filter by category
        if (catId && (!a.category || a.category.id != catId)) return false;
        // Filter by search text
        if (q) {
            var nameMatch = a.name && a.name.toLowerCase().includes(q);
            var tagMatch = a.property_tag && a.property_tag.toLowerCase().includes(q);
            if (!nameMatch && !tagMatch) return false;
        }
        return true;
    });
    
    if (filtered.length === 0) {
        list.innerHTML = '<div class="text-center text-secondary py-4 small">' +
            (q || catId ? 'No items match your filters.' : 'No available items found.') +
            '</div>';
        return;
    }
    
    var selectedId = document.getElementById('initSelectedAssetId').value;
    var html = '';
    filtered.forEach(function(a) {
        var sel = a.id == selectedId ? 'selected' : '';
        var catName = (a.category && a.category.name) ? '<span class="badge bg-secondary me-1" style="font-size: 10px;">' + _escHtml(a.category.name) + '</span>' : '';
        html += '<div class="init-asset-item ' + sel + '" data-id="' + a.id + '" data-name="' + _escAttr(a.name || 'Unknown') + '" data-tag="' + _escAttr(a.property_tag || '') + '" onclick="selectInitAsset(this)">' +
                '<div class="fw-bold" style="font-size: 14px;">' + _escHtml(a.name || 'Unknown') + '</div>' +
                '<div class="small">' + catName + '<span class="text-secondary font-monospace">' + _escHtml(a.property_tag || '') + '</span></div>' +
                '</div>';
    });
    list.innerHTML = html;
}

function selectInitBorrower(el) {
    var id = el.getAttribute('data-id');
    var name = el.getAttribute('data-name');
    document.getElementById('initSelectedBorrowerId').value = id;
    document.getElementById('initSelectedBorrowerName').value = name;
    // Re-render to show selection
    renderInitBorrowers(document.getElementById('initBorrowerSearch').value);
    updateInitNextBtn();
}

function selectInitAsset(el) {
    var id = el.getAttribute('data-id');
    var name = el.getAttribute('data-name');
    var tag = el.getAttribute('data-tag');
    document.getElementById('initSelectedAssetId').value = id;
    document.getElementById('initSelectedAssetName').value = name;
    document.getElementById('initSelectedAssetTag').value = tag;
    renderInitAssets(document.getElementById('initAssetSearch').value);
    updateInitNextBtn();
}

// ── Search filters ──
document.addEventListener('DOMContentLoaded', function() {
    var bs = document.getElementById('initBorrowerSearch');
    if (bs) {
        bs.addEventListener('input', function() {
            renderInitBorrowers(this.value);
        });
    }
    var as = document.getElementById('initAssetSearch');
    if (as) {
        as.addEventListener('input', function() {
            renderInitAssets(this.value);
        });
    }
    
    // Step 3: purpose chips enable/disables Next
    initPurposeChipsInit();
    
    // Asset category filter
    var catSel = document.getElementById('initAssetCategory');
    if (catSel) {
        catSel.addEventListener('change', function() {
            renderInitAssets(document.getElementById('initAssetSearch').value);
        });
    }
    
    // Step 4: keyboard PIN input
    var kb = document.getElementById('initKeyboardPin');
    if (kb) {
        kb.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
            initPin = this.value;
            document.getElementById('initPinValue').value = this.value;
            for (var i = 0; i < 4; i++) {
                var d = document.getElementById('initDot' + i);
                if (!d) continue;
                if (i < this.value.length) { d.classList.add('filled'); d.classList.remove('error'); }
                else { d.classList.remove('filled', 'error'); }
            }
        });
        kb.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (initPin.length === 4) initSubmitBorrow();
            }
        });
    }
    
    // ── Cancel Borrow: set form action (event delegation for paginated rows) ──
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-cancel-borrow');
        if (btn) {
            var id = btn.getAttribute('data-id');
            var form = document.getElementById('cancelBorrowForm');
            if (form) {
                form.action = '/admin/requests/' + id + '/cancel';
            }
        }
    });
});

// ── Purpose chips handler ──
function initPurposeChipsInit() {
    var chips = document.querySelectorAll('#initPurposeChips .init-purpose-chip');
    var purposeValue = document.getElementById('initPurposeValue');
    var otherContainer = document.getElementById('initOtherPurposeContainer');
    var otherTextarea = document.getElementById('initPurposeOther');
    if (!chips.length || !purposeValue) return;
    
    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var val = this.getAttribute('data-value');
            if (val === 'Other') {
                purposeValue.value = '';
                otherContainer.style.display = 'block';
                otherTextarea.setAttribute('required', 'true');
                otherTextarea.focus();
            } else {
                purposeValue.value = val;
                otherContainer.style.display = 'none';
                otherTextarea.removeAttribute('required');
                otherTextarea.value = '';
            }
            updateInitNextBtn();
        });
    });
    
    // Also update on other text input
    if (otherTextarea) {
        otherTextarea.addEventListener('input', updateInitNextBtn);
    }
}

// ── Numpad PIN functions ──
function initPinPad(d) {
    if (initPin.length >= 4) return;
    initPin += d;
    document.getElementById('initPinValue').value = initPin;
    for (var i = 0; i < 4; i++) {
        var dot = document.getElementById('initDot' + i);
        if (i < initPin.length) { dot.classList.add('filled'); dot.classList.remove('error'); }
        else { dot.classList.remove('filled', 'error'); }
    }
    document.getElementById('initPinError').textContent = '';
}

function initPinDel() {
    initPin = initPin.slice(0, -1);
    document.getElementById('initPinValue').value = initPin;
    for (var i = 0; i < 4; i++) {
        var dot = document.getElementById('initDot' + i);
        if (i < initPin.length) { dot.classList.add('filled'); dot.classList.remove('error'); }
        else { dot.classList.remove('filled', 'error'); }
    }
}

function flashInitPinError(msg) {
    for (var i = 0; i < 4; i++) {
        var d = document.getElementById('initDot' + i);
        if (d) d.classList.add('error');
    }
    document.getElementById('initPinError').textContent = msg || 'Incorrect PIN. Please try again.';
    setTimeout(function() {
        for (var i = 0; i < 4; i++) {
            var d = document.getElementById('initDot' + i);
            if (d) d.classList.remove('error', 'filled');
        }
        initPin = '';
        document.getElementById('initPinValue').value = '';
        var kb = document.getElementById('initKeyboardPin');
        if (kb) kb.value = '';
    }, 600);
}

// ── Submit walk-in borrow ──
async function initSubmitBorrow() {
    if (initPin.length !== 4) return;
    
    var userId = document.getElementById('initSelectedBorrowerId').value;
    var itemId = document.getElementById('initSelectedAssetId').value;
    var purpose = document.getElementById('initPurposeValue').value.trim() || document.getElementById('initPurposeOther').value.trim();
    var remarks = document.getElementById('initRemarks').value.trim();
    
    if (!userId || !itemId || !purpose) {
        document.getElementById('initBorrowError').textContent = 'Missing required fields. Please go back and complete all steps.';
        document.getElementById('initBorrowError').classList.remove('d-none');
        return;
    }
    
    var btn = document.getElementById('initSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
    
    try {
        var res = await fetch('/admin/requests/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },                body: JSON.stringify({
                user_id: userId,
                item_id: itemId,
                purpose: purpose,
                admin_remarks: remarks || ('Walk-in request initiated by ' + window.adminName || 'Admin'),
                pin: initPin
            })
        });
        
        var data = await res.json();
        
        if (res.ok) {
            // Success!
            var modal = bootstrap.Modal.getInstance(document.getElementById('initiateBorrowModal'));
            modal.hide();
            showToast('Walk-in Borrow Created', data.message || 'The walk-in borrow was created successfully.', 'success');
            setTimeout(function() { window.location.reload(); }, 1200);
        } else {
            // Show error in the alert box
            document.getElementById('initBorrowError').textContent = data.message || 'Failed to create walk-in borrow.';
            document.getElementById('initBorrowError').classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Confirm & Borrow';
        }
    } catch (e) {
        document.getElementById('initBorrowError').textContent = 'Network error. Please try again.';
        document.getElementById('initBorrowError').classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Confirm & Borrow';
    }
}

// ── HTML-safe attribute/value escaping for data attributes ──
function _escAttr(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function _escHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ── Load admin name for default remarks ──
window.adminName = '';
try {
    var meta = document.querySelector('meta[name="user-name"]');
    if (meta) window.adminName = meta.getAttribute('content');
} catch(e) {}

// ── Realtime In-Page Update for Borrow Requests ──
(function() {
    let lastPendingCount = {{ $requests->total() }};
    let lastRequestId = {{ (int) (\App\Models\BorrowRequest::max('id') ?? 0) }};
    let lastUpdatedAt = '{{ (string) (\App\Models\BorrowRequest::max('updated_at') ?? '') }}';
    let isFetching = false;

    window.addEventListener('baycis:realtime-update', function(e) {
        const counts = e.detail?.counts;
        if (!counts) return;

        const currentPending = counts.pending_borrow_requests !== undefined ? Number(counts.pending_borrow_requests) : lastPendingCount;
        const currentMaxId = counts.latest_request_id !== undefined ? Number(counts.latest_request_id) : lastRequestId;
        const currentUpdated = counts.latest_request_updated_at !== undefined ? String(counts.latest_request_updated_at) : lastUpdatedAt;

        const hasChanged = (currentPending !== lastPendingCount) ||
                           (currentMaxId && currentMaxId !== lastRequestId) ||
                           (currentUpdated && currentUpdated !== lastUpdatedAt);

        if (hasChanged && !isFetching) {
            // Avoid reloading while user is interacting with an open modal
            if (document.querySelector('.modal.show')) return;
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && searchInput === document.activeElement && searchInput.value.trim() !== '') return;

            refreshBorrowRequestsTable(currentPending, currentMaxId, currentUpdated);
        }
    });

    async function refreshBorrowRequestsTable(newPending, newMaxId, newUpdated) {
        const container = document.getElementById('requestsTableContainer');
        if (!container || isFetching) return;
        isFetching = true;

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
                // Check if any modal opened while waiting
                if (!document.querySelector('.modal.show')) {
                    container.innerHTML = html;

                    lastPendingCount = newPending;
                    lastRequestId = newMaxId;
                    lastUpdatedAt = newUpdated;

                    // Re-bind pagination anchor links
                    container.querySelectorAll('.pagination a').forEach(function(link) {
                        const href = link.getAttribute('href');
                        if (href && !href.includes('#')) {
                            link.setAttribute('href', href + '#requests-table');
                        }
                    });

                    // Re-bind batch checkbox listeners
                    const newSelectAll = document.getElementById('reqSelectAll');
                    if (newSelectAll) {
                        newSelectAll.addEventListener('change', function() {
                            document.querySelectorAll('.req-checkbox').forEach(function(cb) { cb.checked = newSelectAll.checked; });
                            if (typeof updateReqBulkBar === 'function') updateReqBulkBar();
                        });
                    }
                    container.querySelectorAll('.req-checkbox').forEach(function(cb) {
                        cb.addEventListener('change', function() {
                            if (typeof updateReqBulkBar === 'function') updateReqBulkBar();
                        });
                    });

                    // Highlight top row if pending
                    const firstRow = container.querySelector('tbody tr[data-req-id]');
                    if (firstRow) {
                        firstRow.classList.add('row-highlight-new');
                        setTimeout(() => firstRow.classList.remove('row-highlight-new'), 3500);
                    }
                }
            }
        } catch (err) {
            console.warn('Realtime table refresh error:', err);
        } finally {
            isFetching = false;
        }
    }
})();
</script>
@endsection