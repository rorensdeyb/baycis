@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Transaction History</h1>
            <p class="text-muted m-0">View all your borrows, issuances, and returns.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4 shadow-sm" style="border-radius: 12px; background: #d1fae5; border: 1px solid #34d399; color: #065f46; padding: 16px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 16px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="activity-card" style="padding: 0; overflow: hidden;">
        
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface);">
            <form action="{{ route('borrower.history') }}" method="GET" class="row g-3 m-0">
                <div class="col-12 col-md-8 p-0 pe-md-2" style="position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, Item Name, or Tag..." style="width: 100%; padding: 12px 16px 12px 42px; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-main); color: var(--text-primary);">
                </div>
                <div class="col-12 col-md-4 p-0 ps-md-2">
                    <select name="status" style="width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-main); color: var(--text-primary); cursor: pointer;" onchange="this.form.submit()">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Active / Borrowed</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <noscript><button type="submit" class="btn-primary-action">Filter</button></noscript>
            </form>
        </div>

        <div class="table-responsive d-none d-md-block" style="padding: 0 20px;">
            <table class="table table-borderless align-middle mt-3" style="color: var(--text-primary);">
                <thead style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="pb-3 fw-bold">Trans. ID</th>
                        <th class="pb-3 fw-bold">Item Details</th>
                        <th class="pb-3 fw-bold">Date Requested</th>
                        <th class="pb-3 fw-bold">Status</th>
                        <th class="pb-3 fw-bold text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td class="py-3 fw-bold text-muted">#REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $req->item->name ?? 'Unknown Item' }}</span>
                                    <span class="text-muted" style="font-size: 12px;">Tag: {{ $req->item->property_tag ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-muted" style="font-size: 14px;">
                                {{ $req->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-3">
                                @if($req->status === 'pending')
                                    <span class="status-badge badge-pending">Pending</span>
                                @elseif(in_array($req->status, ['approved', 'active']))
                                    <span class="status-badge badge-approved" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">Active</span>
                                @elseif($req->status === 'returned')
                                    <span class="status-badge" style="background: rgba(107, 114, 128, 0.15); color: #6b7280;">Returned</span>
                                @elseif($req->status === 'rejected')
                                    <span class="status-badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">Rejected</span>
                                @elseif($req->status === 'cancelled')
                                    <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                                @else
                                    <span class="status-badge" style="background: var(--bg-main); color: var(--text-secondary);">{{ ucfirst($req->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-end">
                                <button class="btn btn-light btn-sm fw-bold px-3 py-2" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#detailsModal-{{ $req->id }}">View</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No transaction history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-block d-md-none p-3">
            @forelse($requests as $req)
                <div class="mb-3 p-3" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 1px dashed var(--border-color);">
                        <span class="text-muted fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">#REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</span>
                        
                        @if($req->status === 'pending')
                            <span class="status-badge badge-pending">Pending</span>
                        @elseif(in_array($req->status, ['approved', 'active']))
                            <span class="status-badge badge-approved" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">Active</span>
                        @elseif($req->status === 'returned')
                            <span class="status-badge" style="background: rgba(107, 114, 128, 0.15); color: #6b7280;">Returned</span>
                        @elseif($req->status === 'rejected')
                            <span class="status-badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">Rejected</span>
                        @elseif($req->status === 'cancelled')
                            <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                        @else
                            <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ ucfirst($req->status) }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="item-icon" style="width: 48px; height: 48px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-seam fs-4 text-muted"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold" style="font-size: 15px; color: var(--text-primary);">{{ $req->item->name ?? 'Unknown Item' }}</span>
                            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-calendar-event me-1"></i> {{ $req->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <button class="btn btn-light w-100 fw-bold py-2 mt-1" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 10px; font-size: 14px;" data-bs-toggle="modal" data-bs-target="#detailsModal-{{ $req->id }}">View Details</button>
                </div>
            @empty
                <div class="text-center py-5 text-muted" style="background: var(--bg-surface); border-radius: 16px; border: 1px dashed var(--border-color);">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    No transaction history found.
                </div>
            @endforelse
        </div>

        <div class="p-3" style="background: var(--bg-surface); border-top: 1px solid var(--border-color);">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@foreach($requests as $req)
    <div class="modal fade" id="detailsModal-{{ $req->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 20px; border: 1px solid var(--border-color);">
                
                <div class="modal-header d-flex align-items-center justify-content-between" style="border-bottom: 1px solid var(--border-color); padding: 20px 24px;">
                    <h5 class="modal-title fw-bold" style="font-size: 18px;">Transaction #REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(var(--invert-icon, 0));"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4 p-3" style="background: var(--bg-main); border-radius: 16px; border: 1px solid var(--border-color);">
                        <div class="item-icon" style="width: 54px; height: 54px; min-width: 54px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-seam fs-3 text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold m-0 fs-5 mb-1" style="color: var(--text-primary); line-height: 1.2;">{{ $req->item->name ?? 'Unknown Item' }}</h6>
                            <span class="badge" style="background: var(--border-color); color: var(--text-secondary); font-weight: 600; font-size: 11px;">
                                <i class="bi bi-folder2-open me-1"></i> {{ $req->item->category->name ?? 'General Category' }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 h-100" style="background: var(--bg-main); border-radius: 16px; border: 1px solid var(--border-color);">
                                <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Current Status</span>
                                <div class="mb-3">
                                    @if($req->status === 'pending')
                                        <span class="status-badge badge-pending">Pending Approval</span>
                                    @elseif(in_array($req->status, ['approved', 'active']))
                                        <span class="status-badge badge-approved" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">Active / Borrowed</span>
                                    @elseif($req->status === 'returned')
                                        <span class="status-badge" style="background: rgba(107, 114, 128, 0.15); color: #6b7280;">Returned</span>
                                    @elseif($req->status === 'rejected')
                                        <span class="status-badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">Rejected</span>
                                    @elseif($req->status === 'cancelled')
                                        <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                                    @else
                                        <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ ucfirst($req->status) }}</span>
                                    @endif
                                </div>
                                <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Date Requested</span>
                                <p class="m-0 fw-bold" style="font-size: 14px; color: var(--text-primary);">{{ $req->created_at->format('M d, Y') }}</p>
                                <p class="m-0 text-muted" style="font-size: 12px;">{{ $req->created_at->format('h:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 h-100" style="background: var(--bg-main); border-radius: 16px; border: 1px solid var(--border-color);">
                                <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Property Tag</span>
                                <p class="m-0 mb-3 fw-bold text-break" style="font-size: 13px; color: var(--text-primary); font-family: monospace;">
                                    <i class="bi bi-upc-scan me-1 text-muted"></i> {{ $req->item->property_tag ?? 'N/A' }}
                                </p>
                                <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Serial Number</span>
                                <p class="m-0 fw-bold text-break" style="font-size: 13px; color: var(--text-primary); font-family: monospace;">
                                    <i class="bi bi-hash me-1 text-muted"></i> {{ $req->item->serial_number ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Reason for Request</span>
                        <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                            <p class="m-0 text-break" style="font-size: 14px; line-height: 1.5; color: var(--text-primary);">{{ $req->purpose ?? 'No reason provided.' }}</p>
                        </div>
                    </div>

                    @if($req->admin_remarks)
                        <div class="mb-3 p-3" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; border-radius: 4px;">
                            <span class="d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: #b45309; letter-spacing: 0.5px;">Admin Remarks</span>
                            <p class="m-0" style="color: #92400e; font-size: 14px; line-height: 1.5;">{{ $req->admin_remarks }}</p>
                        </div>
                    @endif

                    @if($req->status === 'pending' && $req->qr_code_hash)
                        <div class="text-center mt-4 p-4" style="background: var(--bg-main); border-radius: 16px; border: 1px solid var(--border-color);">
                            <p class="fw-bold text-xs text-muted mb-3 text-uppercase letter-spacing-1">Your Request QR</p>
                            <img src="https://quickchart.io/qr?size=160&text={{ urlencode($req->qr_code_hash) }}" alt="QR Code" style="border-radius: 12px; border: 4px solid var(--bg-surface); box-shadow: var(--shadow-sm);">
                            <p class="text-sm text-muted mt-3 mb-0">Present this QR code to the admin.</p>
                        </div>
                    @endif
                </div>
                
                <div class="modal-footer d-flex gap-2" style="border-top: 1px solid var(--border-color); padding: 16px 24px;">
                    @if($req->status === 'pending')
                        <form action="{{ route('borrower.request.cancel', $req->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 fw-bold py-2" style="border-radius: 12px; background: #fee2e2; color: #991b1b; border: 1px solid #f87171; font-size: 15px;">Cancel Request</button>
                        </form>
                        <button type="button" class="btn btn-light fw-bold py-2" style="flex: 1; border: 1px solid var(--border-color); background: var(--bg-main); border-radius: 12px; font-size: 15px;" data-bs-dismiss="modal">Close</button>
                    @else
                        <button type="button" class="btn btn-light w-100 fw-bold py-2" style="border: 1px solid var(--border-color); background: var(--bg-main); border-radius: 12px; font-size: 15px;" data-bs-dismiss="modal">Close</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection