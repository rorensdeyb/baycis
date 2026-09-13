@extends('layouts.borrower')

@section('content')
<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Transaction History</h1>
            <p class="text-muted m-0">View all your historical asset borrows, equipment loans, and consumable issuances.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-green-bg); border: 1px solid var(--accent-green); color: var(--accent-green); padding: 16px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-red-bg); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 16px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Segmented Tab Switcher --}}
    <div class="d-flex gap-2 mb-4 p-1" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; width: fit-content; max-width: 100%; overflow-x: auto;">
        <a href="{{ route('borrower.history', ['tab' => 'borrows']) }}" 
           class="btn btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold"
           style="border-radius: 10px; text-decoration: none; transition: all 0.2s ease; {{ $tab === 'borrows' ? 'background: var(--accent-blue); color: #fff; box-shadow: var(--shadow-sm);' : 'background: transparent; color: var(--text-secondary);' }}">
            <i class="bi bi-box-seam"></i>
            <span>Asset Borrows</span>
            <span class="badge rounded-pill" style="font-size: 11px; {{ $tab === 'borrows' ? 'background: rgba(255,255,255,0.25); color: #fff;' : 'background: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color);' }}">{{ $borrowsCount }}</span>
        </a>
        <a href="{{ route('borrower.history', ['tab' => 'issuances']) }}" 
           class="btn btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold"
           style="border-radius: 10px; text-decoration: none; transition: all 0.2s ease; {{ $tab === 'issuances' ? 'background: var(--accent-blue); color: #fff; box-shadow: var(--shadow-sm);' : 'background: transparent; color: var(--text-secondary);' }}">
            <i class="bi bi-clipboard-check"></i>
            <span>Consumable Issuances</span>
            <span class="badge rounded-pill" style="font-size: 11px; {{ $tab === 'issuances' ? 'background: rgba(255,255,255,0.25); color: #fff;' : 'background: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color);' }}">{{ $issuancesCount }}</span>
        </a>
    </div>

    <div class="activity-card" style="padding: 0; overflow: hidden; border-radius: 16px; border: 1px solid var(--border-color); background: var(--bg-surface);">
        
        {{-- Search & Filter Controls --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface);">
            <form action="{{ route('borrower.history') }}" method="GET" class="row g-2 align-items-center m-0">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-12 col-md-7 p-0 pe-md-2" style="position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="{{ $tab === 'borrows' ? 'Search by ID, Item Name, or Tag...' : 'Search by ID, Item Name, or Purpose...' }}" 
                           style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-primary); font-size: 14px;">
                </div>
                <div class="col-8 col-md-3 p-0 pe-2 ps-md-0">
                    <select name="status" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-primary); font-size: 14px; cursor: pointer;" onchange="this.form.submit()">
                        @if($tab === 'borrows')
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Active / Borrowed</option>
                            <option value="returned" {{ $status == 'returned' ? 'selected' : '' }}>Returned</option>
                            <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        @else
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending_issue" {{ $status == 'pending_issue' ? 'selected' : '' }}>Pending Admin Release</option>
                            <option value="issued" {{ $status == 'issued' ? 'selected' : '' }}>Awaiting Confirmation</option>
                            <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Received & Confirmed</option>
                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        @endif
                    </select>
                </div>
                <div class="col-4 col-md-2 p-0">
                    <button type="submit" class="btn w-100 fw-bold" style="padding: 10px 14px; border-radius: 10px; background: var(--accent-blue); color: #fff; font-size: 14px; border: none;">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        @if($tab === 'borrows')
            {{-- TAB 1: ASSET BORROWS --}}
            {{-- Desktop Table View --}}
            <div class="table-responsive d-none d-md-block" style="padding: 0 20px;">
                <table class="table table-borderless align-middle mt-3" style="color: var(--text-primary);">
                    <thead style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
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
                                <td class="py-3 fw-bold text-muted" style="font-size: 13px;">#REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold" style="color: var(--text-primary);">{{ $req->item->name ?? 'Unknown Item' }}</span>
                                        <span class="text-muted" style="font-size: 12px;">Tag: {{ $req->item->property_tag ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-muted" style="font-size: 13px;">
                                    {{ $req->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="py-3">
                                    @if($req->status === 'pending')
                                        <span class="status-badge badge-pending">Pending</span>
                                    @elseif(in_array($req->status, ['approved', 'active']))
                                        <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Active</span>
                                    @elseif($req->status === 'returned')
                                        <span class="status-badge" style="background: var(--bg-surface-hover); color: var(--text-secondary);">Returned</span>
                                    @elseif($req->status === 'rejected')
                                        <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                                    @elseif($req->status === 'cancelled')
                                        <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                                    @else
                                        <span class="status-badge" style="background: var(--bg-main); color: var(--text-secondary);">{{ ucfirst($req->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-end">
                                    <button class="btn btn-light btn-sm fw-bold px-3 py-2" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 8px; font-size: 13px;" data-bs-toggle="modal" data-bs-target="#borrowDetailsModal-{{ $req->id }}">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div style="width: 64px; height: 64px; background: var(--accent-blue-bg, rgba(59, 130, 246, 0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                                        <i class="bi bi-box-seam fs-2" style="color: var(--accent-blue);"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2" style="color: var(--text-primary);">No Borrow Records Found</h5>
                                    <p class="small mb-4" style="color: var(--text-secondary); max-width: 340px; margin: 0 auto;">No asset borrow records match your current filter criteria.</p>
                                    <a href="{{ route('borrower.requests') }}" class="btn fw-bold px-4 py-2" style="background: var(--accent-color, var(--accent-blue)); color: #fff; border-radius: 10px; text-decoration: none;">
                                        <i class="bi bi-box-seam me-1"></i> Make a Request
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Compact Card View --}}
            <div class="d-block d-md-none p-3">
                @forelse($requests as $req)
                    <div class="mb-3 p-3" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed var(--border-color);">
                            <span class="text-muted fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">#REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</span>
                            
                            @if($req->status === 'pending')
                                <span class="status-badge badge-pending">Pending</span>
                            @elseif(in_array($req->status, ['approved', 'active']))
                                <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Active</span>
                            @elseif($req->status === 'returned')
                                <span class="status-badge" style="background: var(--bg-surface-hover); color: var(--text-secondary);">Returned</span>
                            @elseif($req->status === 'rejected')
                                <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                            @elseif($req->status === 'cancelled')
                                <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                            @else
                                <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ ucfirst($req->status) }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width: 44px; height: 44px; min-width: 44px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-box-seam fs-4 text-muted"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold" style="font-size: 14px; color: var(--text-primary); line-height: 1.2;">{{ $req->item->name ?? 'Unknown Item' }}</span>
                                <span class="text-muted mt-1" style="font-size: 12px;"><i class="bi bi-calendar-event me-1"></i> {{ $req->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <button class="btn btn-light w-100 fw-bold py-2" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 10px; font-size: 13px;" data-bs-toggle="modal" data-bs-target="#borrowDetailsModal-{{ $req->id }}">View Details</button>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted" style="background: var(--bg-surface); border-radius: 14px; border: 1px dashed var(--border-color);">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        No borrow history found.
                    </div>
                @endforelse
            </div>

            <div class="p-3" style="background: var(--bg-surface); border-top: 1px solid var(--border-color);">
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>

        @else
            {{-- TAB 2: CONSUMABLE ISSUANCES --}}
            {{-- Desktop Table View --}}
            <div class="table-responsive d-none d-md-block" style="padding: 0 20px;">
                <table class="table table-borderless align-middle mt-3" style="color: var(--text-primary);">
                    <thead style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th class="pb-3 fw-bold">Issuance ID</th>
                            <th class="pb-3 fw-bold">Item & Quantity</th>
                            <th class="pb-3 fw-bold">Issued By</th>
                            <th class="pb-3 fw-bold">Date</th>
                            <th class="pb-3 fw-bold">Status</th>
                            <th class="pb-3 fw-bold text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuances as $iss)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="py-3 fw-bold text-muted" style="font-size: 13px;">#ISS-{{ str_pad($iss->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold" style="color: var(--text-primary);">{{ $iss->item->name ?? 'Unknown Item' }}</span>
                                        <span class="text-muted" style="font-size: 12px;">Qty: <strong style="color: var(--accent-blue);">{{ $iss->quantity }}</strong> {{ $iss->item->unit ?? 'unit(s)' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-muted" style="font-size: 13px;">
                                    {{ $iss->issuer->name ?? 'Inventory Staff' }}
                                </td>
                                <td class="py-3 text-muted" style="font-size: 13px;">
                                    {{ $iss->issued_at ? $iss->issued_at->format('M d, Y h:i A') : $iss->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="py-3">
                                    @if($iss->status === 'pending_issue')
                                        <span class="status-badge badge-pending">Pending Release</span>
                                    @elseif($iss->status === 'issued')
                                        <span class="status-badge" style="background: var(--accent-yellow-bg); color: var(--accent-yellow); border: 1px solid rgba(245, 158, 11, 0.3); font-weight: 600;">Awaiting Receipt</span>
                                    @elseif($iss->status === 'confirmed')
                                        <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Received</span>
                                    @elseif($iss->status === 'cancelled')
                                        <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                                    @elseif($iss->status === 'rejected')
                                        <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                                    @else
                                        <span class="status-badge" style="background: var(--bg-main); color: var(--text-secondary);">{{ ucfirst($iss->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-end">
                                    <button class="btn btn-light btn-sm fw-bold px-3 py-2" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 8px; font-size: 13px;" data-bs-toggle="modal" data-bs-target="#issuanceDetailsModal-{{ $iss->id }}">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div style="width: 64px; height: 64px; background: var(--accent-yellow-bg, rgba(245, 158, 11, 0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                                        <i class="bi bi-clipboard-check fs-2" style="color: var(--accent-yellow);"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2" style="color: var(--text-primary);">No Consumable Issuances Found</h5>
                                    <p class="small mb-4" style="color: var(--text-secondary); max-width: 340px; margin: 0 auto;">No consumable supply records match your current filter criteria.</p>
                                    <a href="{{ route('borrower.issuance') }}" class="btn fw-bold px-4 py-2" style="background: var(--accent-color, var(--accent-blue)); color: #fff; border-radius: 10px; text-decoration: none;">
                                        <i class="bi bi-plus-circle me-1"></i> Request Supplies
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Compact Card View --}}
            <div class="d-block d-md-none p-3">
                @forelse($issuances as $iss)
                    <div class="mb-3 p-3" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed var(--border-color);">
                            <span class="text-muted fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">#ISS-{{ str_pad($iss->id, 4, '0', STR_PAD_LEFT) }}</span>
                            
                            @if($iss->status === 'pending_issue')
                                <span class="status-badge badge-pending">Pending Release</span>
                            @elseif($iss->status === 'issued')
                                <span class="status-badge" style="background: var(--accent-yellow-bg); color: var(--accent-yellow); border: 1px solid rgba(245, 158, 11, 0.3); font-weight: 600;">Awaiting Receipt</span>
                            @elseif($iss->status === 'confirmed')
                                <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Received</span>
                            @elseif($iss->status === 'cancelled')
                                <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                            @elseif($iss->status === 'rejected')
                                <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                            @else
                                <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ ucfirst($iss->status) }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width: 44px; height: 44px; min-width: 44px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-box2-heart fs-4 text-muted"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold" style="font-size: 14px; color: var(--text-primary); line-height: 1.2;">{{ $iss->item->name ?? 'Unknown Item' }}</span>
                                <span class="text-muted mt-1" style="font-size: 12px;"><i class="bi bi-layers me-1"></i> Qty: <strong>{{ $iss->quantity }}</strong> {{ $iss->item->unit ?? 'unit(s)' }} &bull; {{ $iss->issued_at ? $iss->issued_at->format('M d, Y') : $iss->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <button class="btn btn-light w-100 fw-bold py-2" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 10px; font-size: 13px;" data-bs-toggle="modal" data-bs-target="#issuanceDetailsModal-{{ $iss->id }}">View Details</button>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted" style="background: var(--bg-surface); border-radius: 14px; border: 1px dashed var(--border-color);">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        No consumable issuance history found.
                    </div>
                @endforelse
            </div>

            <div class="p-3" style="background: var(--bg-surface); border-top: 1px solid var(--border-color);">
                {{ $issuances->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

{{-- MODALS FOR ASSET BORROWS --}}
@if($tab === 'borrows')
    @foreach($requests as $req)
        <div class="modal fade" id="borrowDetailsModal-{{ $req->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 18px; border: 1px solid var(--border-color);">
                    
                    <div class="modal-header d-flex align-items-center justify-content-between" style="border-bottom: 1px solid var(--border-color); padding: 18px 22px;">
                        <h5 class="modal-title fw-bold" style="font-size: 17px;">Transaction #REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</h5>
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(var(--invert-icon, 0));"></button>
                    </div>
                    
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-start gap-3 mb-4 p-3" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
                            <div style="width: 48px; height: 48px; min-width: 48px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
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
                                <div class="p-3 h-100" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
                                    <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Current Status</span>
                                    <div class="mb-3">
                                        @if($req->status === 'pending')
                                            <span class="status-badge badge-pending">Pending Approval</span>
                                        @elseif(in_array($req->status, ['approved', 'active']))
                                            <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Active / Borrowed</span>
                                        @elseif($req->status === 'returned')
                                            <span class="status-badge" style="background: var(--bg-surface-hover); color: var(--text-secondary);">Returned</span>
                                        @elseif($req->status === 'rejected')
                                            <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                                        @elseif($req->status === 'cancelled')
                                            <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                                        @else
                                            <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ ucfirst($req->status) }}</span>
                                        @endif
                                    </div>
                                    <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Date Requested</span>
                                    <p class="m-0 fw-bold" style="font-size: 13px; color: var(--text-primary);">{{ $req->created_at->format('M d, Y') }}</p>
                                    <p class="m-0 text-muted" style="font-size: 11px;">{{ $req->created_at->format('h:i A') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="p-3 h-100" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
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

                        <div class="mb-3">
                            <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Reason / Purpose</span>
                            <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                <p class="m-0 text-break" style="font-size: 13px; line-height: 1.5; color: var(--text-primary);">{{ $req->purpose ?? 'No reason provided.' }}</p>
                            </div>
                        </div>

                        @if($req->admin_remarks)
                            <div class="mb-3 p-3" style="background: var(--accent-yellow-bg); border-left: 4px solid var(--accent-yellow); border-radius: 6px;">
                                <span class="d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: var(--accent-yellow); letter-spacing: 0.5px;">Admin Remarks</span>
                                <p class="m-0" style="color: var(--text-primary); font-size: 13px; line-height: 1.5;">{{ $req->admin_remarks }}</p>
                            </div>
                        @endif

                        @if($req->status === 'pending' && $req->qr_code_hash)
                            <div class="text-center mt-4 p-4" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
                                <p class="fw-bold text-xs text-muted mb-3 text-uppercase letter-spacing-1">Borrow Claim QR Code</p>
                                <img src="https://quickchart.io/qr?size=160&text={{ urlencode($req->qr_code_hash) }}" alt="QR Code" style="border-radius: 10px; border: 4px solid var(--bg-surface); box-shadow: var(--shadow-sm);">
                                <p class="text-sm text-muted mt-3 mb-0">Show this QR code to the property custodian.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="modal-footer d-flex gap-2" style="border-top: 1px solid var(--border-color); padding: 14px 22px;">
                        @if($req->status === 'pending')
                            <form action="{{ route('borrower.request.cancel', $req->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to cancel this borrow request?');">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100 fw-bold py-2" style="border-radius: 10px; background: var(--accent-red-bg); color: var(--accent-red); border: 1px solid var(--accent-red); font-size: 14px;">Cancel Request</button>
                            </form>
                            <button type="button" class="btn btn-light fw-bold py-2" style="flex: 1; border: 1px solid var(--border-color); background: var(--bg-main); border-radius: 10px; font-size: 14px; color: var(--text-primary);" data-bs-dismiss="modal">Close</button>
                        @else
                            <button type="button" class="btn btn-light w-100 fw-bold py-2" style="border: 1px solid var(--border-color); background: var(--bg-main); border-radius: 10px; font-size: 14px; color: var(--text-primary);" data-bs-dismiss="modal">Close</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    {{-- MODALS FOR CONSUMABLE ISSUANCES --}}
    @foreach($issuances as $iss)
        <div class="modal fade" id="issuanceDetailsModal-{{ $iss->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content" style="background: var(--bg-surface); color: var(--text-primary); border-radius: 18px; border: 1px solid var(--border-color);">
                    
                    <div class="modal-header d-flex align-items-center justify-content-between" style="border-bottom: 1px solid var(--border-color); padding: 18px 22px;">
                        <h5 class="modal-title fw-bold" style="font-size: 17px;">Issuance #ISS-{{ str_pad($iss->id, 4, '0', STR_PAD_LEFT) }}</h5>
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(var(--invert-icon, 0));"></button>
                    </div>
                    
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-start gap-3 mb-4 p-3" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
                            <div style="width: 48px; height: 48px; min-width: 48px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-box2-heart fs-3 text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold m-0 fs-5 mb-1" style="color: var(--text-primary); line-height: 1.2;">{{ $iss->item->name ?? 'Unknown Consumable' }}</h6>
                                <span class="badge" style="background: var(--accent-blue-bg, rgba(59, 130, 246, 0.1)); color: var(--accent-blue); font-weight: 700; font-size: 12px;">
                                    {{ $iss->quantity }} {{ $iss->item->unit ?? 'unit(s)' }}
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="p-3 h-100" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
                                    <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Issuance Status</span>
                                    <div class="mb-3">
                                        @if($iss->status === 'pending_issue')
                                            <span class="status-badge badge-pending">Pending Admin Release</span>
                                        @elseif($iss->status === 'issued')
                                            <span class="status-badge" style="background: var(--accent-yellow-bg); color: var(--accent-yellow); border: 1px solid rgba(245, 158, 11, 0.3); font-weight: 600;">Awaiting Your Receipt</span>
                                        @elseif($iss->status === 'confirmed')
                                            <span class="status-badge badge-approved" style="background: var(--accent-green-bg); color: var(--accent-green);">Received & Confirmed</span>
                                        @elseif($iss->status === 'cancelled')
                                            <span class="status-badge" style="background: var(--bg-surface); color: #9ca3af; text-decoration: line-through; border: 1px solid var(--border-color);">Cancelled</span>
                                        @elseif($iss->status === 'rejected')
                                            <span class="status-badge" style="background: var(--accent-red-bg); color: var(--accent-red);">Rejected</span>
                                        @else
                                            <span class="status-badge" style="background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ ucfirst($iss->status) }}</span>
                                        @endif
                                    </div>
                                    <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Date Requested</span>
                                    <p class="m-0 fw-bold" style="font-size: 13px; color: var(--text-primary);">{{ $iss->created_at->format('M d, Y') }}</p>
                                    <p class="m-0 text-muted" style="font-size: 11px;">{{ $iss->created_at->format('h:i A') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="p-3 h-100" style="background: var(--bg-main); border-radius: 14px; border: 1px solid var(--border-color);">
                                    <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Issued By</span>
                                    <p class="m-0 mb-3 fw-bold text-break" style="font-size: 13px; color: var(--text-primary);">
                                        <i class="bi bi-person-badge me-1 text-muted"></i> {{ $iss->issuer->name ?? 'Inventory Staff' }}
                                    </p>
                                    <span class="text-muted d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Date Confirmed</span>
                                    <p class="m-0 fw-bold text-break" style="font-size: 13px; color: var(--text-primary);">
                                        <i class="bi bi-calendar-check me-1 text-muted"></i> {{ $iss->confirmed_at ? $iss->confirmed_at->format('M d, Y h:i A') : 'Pending' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Purpose</span>
                            <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                <p class="m-0 text-break" style="font-size: 13px; line-height: 1.5; color: var(--text-primary);">{{ $iss->purpose ?? 'No purpose specified.' }}</p>
                            </div>
                        </div>

                        @if($iss->borrower_notes)
                            <div class="mb-3">
                                <span class="text-muted d-block mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Your Notes</span>
                                <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                    <p class="m-0 text-break" style="font-size: 13px; line-height: 1.5; color: var(--text-primary);">{{ $iss->borrower_notes }}</p>
                                </div>
                            </div>
                        @endif

                        @if($iss->admin_notes)
                            <div class="mb-3 p-3" style="background: var(--accent-yellow-bg); border-left: 4px solid var(--accent-yellow); border-radius: 6px;">
                                <span class="d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: var(--accent-yellow); letter-spacing: 0.5px;">Custodian Notes</span>
                                <p class="m-0" style="color: var(--text-primary); font-size: 13px; line-height: 1.5;">{{ $iss->admin_notes }}</p>
                            </div>
                        @endif

                        @if($iss->status === 'issued')
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-0" style="border-radius: 12px;">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div style="font-size: 13px;">
                                    This item has been issued to you. Please confirm receipt on the Issuance tab once received.
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="modal-footer d-flex gap-2" style="border-top: 1px solid var(--border-color); padding: 14px 22px;">
                        @if($iss->status === 'issued')
                            <a href="{{ route('borrower.issuance') }}" class="btn btn-primary fw-bold py-2" style="flex: 1; border-radius: 10px; background: var(--accent-blue); color: #fff; font-size: 14px; text-decoration: none; text-align: center;">Go to Issuance & Confirm</a>
                            <button type="button" class="btn btn-light fw-bold py-2" style="flex: 1; border: 1px solid var(--border-color); background: var(--bg-main); border-radius: 10px; font-size: 14px; color: var(--text-primary);" data-bs-dismiss="modal">Close</button>
                        @else
                            <button type="button" class="btn btn-light w-100 fw-bold py-2" style="border: 1px solid var(--border-color); background: var(--bg-main); border-radius: 10px; font-size: 14px; color: var(--text-primary);" data-bs-dismiss="modal">Close</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Live search: filter as you type (case-insensitive server-side via whereLike)
    var searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        if (searchInput.value) {
            searchInput.focus();
            var val = searchInput.value;
            searchInput.value = ''; searchInput.value = val;
        }
        var debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                searchInput.closest('form').submit();
            }, 800);
        });
    }
});
</script>

@endsection
