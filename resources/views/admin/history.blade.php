@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">Transaction History</h1>
        <p class="form-label text-secondary mb-0">Real-time audit log of all asset distributions, returns, and outstanding liabilities.</p>
    </div>

    {{-- H-UI.1: Timeline / Table View Toggle --}}
    <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
        <button type="button" id="viewTimelineBtn" class="btn btn-sm d-flex align-items-center gap-1 fw-bold px-3" style="border:1px solid var(--border-color);border-radius:8px;background:var(--bg-surface);color:var(--text-secondary);font-size:11px;" onclick="toggleHistoryView('timeline')">
            <i class="bi bi-diagram-3"></i> Timeline
        </button>
        <button type="button" id="viewTableBtn" class="btn btn-sm d-flex align-items-center gap-1 fw-bold px-3" style="border:1px solid var(--text-primary);border-radius:8px;background:var(--text-primary);color:var(--bg-surface);font-size:11px;" onclick="toggleHistoryView('table')">
            <i class="bi bi-table"></i> Table
        </button>
        <span class="text-secondary" style="font-size:14px;line-height:1;">|</span>
        <span class="small fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; white-space:nowrap;">Quick Date:</span>
        @php
            $period = request('period', 'all');
            $view = request('view', 'table');
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['period' => 'all', 'page' => null, 'view' => $view]) }}" class="pill {{ $period === 'all' ? 'pill-active' : '' }}">All Time</a>
        <a href="{{ request()->fullUrlWithQuery(['period' => 'today', 'page' => null, 'view' => $view]) }}" class="pill {{ $period === 'today' ? 'pill-active' : '' }}">Today</a>
        <a href="{{ request()->fullUrlWithQuery(['period' => 'this_week', 'page' => null, 'view' => $view]) }}" class="pill {{ $period === 'this_week' ? 'pill-active' : '' }}">This Week</a>
        <a href="{{ request()->fullUrlWithQuery(['period' => 'this_month', 'page' => null, 'view' => $view]) }}" class="pill {{ $period === 'this_month' ? 'pill-active' : '' }}">This Month</a>
        <a href="{{ request()->fullUrlWithQuery(['period' => 'this_quarter', 'page' => null, 'view' => $view]) }}" class="pill {{ $period === 'this_quarter' ? 'pill-active' : '' }}">This Quarter</a>
    </div>

    <div class="panel-card p-3 mb-4 shadow-sm" style="border-radius: 12px;">
        <form action="{{ route('items.history') }}" method="GET" class="row g-3 m-0">
            <div class="col-12 col-md-8 p-0 pe-md-2 position-relative">
                <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control theme-dynamic-input w-100" placeholder="Search Trans ID, Borrower Name, or Property Tag..." style="padding-left: 42px;">
            </div>
            <div class="col-12 col-md-4 p-0 ps-md-2 d-flex gap-2">
                <select name="status" class="form-select theme-dynamic-input w-100 cursor-pointer" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Borrowed (Active)</option>
                    <option value="return_pending" {{ request('status') == 'return_pending' ? 'selected' : '' }}>Return Pending</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned (Completed)</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <a href="{{ route('items.history') }}" class="btn btn-light d-flex align-items-center justify-content-center" style="border-radius: 8px; border: 1px solid var(--border-color);" title="Reset Filters">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
            <noscript><button type="submit" class="btn btn-primary d-none">Filter</button></noscript>
        </form>
    </div>

    <div id="history-table" class="panel-card p-0 overflow-hidden shadow-sm" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="admin-table mb-0 align-middle">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3" style="width: 10%;">Trans ID</th>
                        <th class="py-3" style="width: 25%;">Asset Details</th>
                        <th class="py-3" style="width: 20%;">Borrower</th>
                        <th class="py-3" style="width: 20%;">Timeline</th>
                        <th class="text-center py-3" style="width: 15%;">Status</th>
                        <th class="text-end pe-4 py-3" style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: 0.2s;">
                        
                        <td class="ps-4 py-3">
                            <span class="badge bg-secondary px-2 py-1 font-monospace" style="letter-spacing: 0.5px;">#REQ-{{ str_pad($log->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>

                        <td class="py-3">
                            <div class="fw-bold" style="color: var(--text-primary);">{{ $log->item->name ?? 'Unknown Item' }}</div>
                            <div class="small text-secondary font-monospace"><i class="bi bi-upc-scan me-1"></i>{{ $log->item->property_tag ?? 'N/A' }}</div>
                        </td>

                        <td class="py-3">
                            <div class="fw-bold" style="color: var(--text-primary);">{{ $log->user->name ?? 'Unknown User' }}</div>
                            <div class="small text-secondary">{{ $log->user->email ?? 'No email provided' }}</div>
                        </td>

                        <td class="py-3">
                            <div class="small mb-1">
                                <span class="text-secondary fw-bold" style="font-size: 10px; text-transform: uppercase;">Requested:</span><br>
                                <span style="color: var(--text-primary);">{{ $log->created_at->format('M d, Y') }}</span>
                            </div>
                            @if(in_array($log->status, ['returned', 'rejected', 'cancelled']))
                            <div class="small">
                                <span class="text-secondary fw-bold" style="font-size: 10px; text-transform: uppercase;">Closed:</span><br>
                                <span style="color: var(--text-primary);">{{ $log->updated_at->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </td>

                        <td class="text-center py-3">
                            @if($log->status === 'pending')
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">PENDING</span>
                            @elseif(in_array($log->status, ['approved', 'active']))
                                <span class="badge bg-success px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">ON LOAN</span>
                            @elseif($log->status === 'return_pending')
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">RETURN PENDING</span>
                            @elseif($log->status === 'returned')
                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">RETURNED</span>
                            @elseif($log->status === 'rejected')
                                <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 0.5px;">REJECTED</span>
                            @elseif($log->status === 'cancelled')
                                <span class="badge bg-secondary px-3 py-2 rounded-pill fw-bold text-decoration-line-through" style="letter-spacing: 0.5px;">CANCELLED</span>
                            @else
                                <span class="badge bg-secondary px-3 py-2 rounded-pill fw-bold text-uppercase" style="letter-spacing: 0.5px;">{{ $log->status }}</span>
                            @endif
                        </td>

                        <td class="text-end pe-4 py-3">
                            <button type="button" class="btn btn-sm btn-light fw-bold px-3 py-2 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#viewModal-{{ $log->id }}" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary);">
                                <i class="bi bi-file-earmark-text"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div style="width: 64px; height: 64px; background: var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                                <i class="bi bi-clock-history fs-3 text-secondary"></i>
                            </div>
                            <h5 class="fw-bold" style="color: var(--text-primary);">No History Found</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">There are no transaction records matching your current filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @foreach($logs as $log)
    <div class="modal fade" id="viewModal-{{ $log->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <div style="width: 36px; height: 36px; background: var(--bg-main); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                            <i class="bi bi-clock-history text-secondary"></i>
                        </div>
                        Transaction History <span class="text-secondary fs-6 ms-2">#REQ-{{ str_pad($log->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </h5>
                    <button type="button" class="btn-close shadow-none" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <div class="row g-4">
                        
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Request Context</h6>
                            <div class="p-3 mb-4" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <span class="text-secondary small">Final Status</span>
                                    @if($log->status === 'returned')
                                        <span class="badge bg-info text-dark">RETURNED</span>
                                    @elseif($log->status === 'rejected')
                                        <span class="badge bg-danger">REJECTED</span>
                                    @elseif(in_array($log->status, ['approved', 'active']))
                                        <span class="badge bg-success">ON LOAN</span>
                                    @else
                                        <span class="badge bg-secondary text-uppercase">{{ $log->status }}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">Original Reason / Purpose</span>
                                    <p class="mb-0" style="line-height: 1.5; font-size: 14px;">{{ $log->purpose ?? 'No purpose provided.' }}</p>
                                </div>
                                @if($log->admin_remarks)
                                <div class="pt-3" style="border-top: 1px dashed var(--border-color);">
                                    <span class="text-secondary small d-block mb-1">Admin Processing Remarks</span>
                                    <p class="mb-0 fw-semibold" style="font-size: 14px;">{{ $log->admin_remarks }}</p>
                                </div>
                                @endif
                            </div>

                            @if($log->status === 'returned' || $log->status === 'return_pending')
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Return Data</h6>
                            <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">Reported Condition</span>
                                    <span class="badge bg-secondary">{{ $log->return_condition ?? 'Not Stated' }}</span>
                                </div>
                                <div>
                                    <span class="text-secondary small d-block mb-1">Borrower Return Remarks</span>
                                    <p class="mb-0" style="line-height: 1.5; font-size: 14px;">{{ $log->return_remarks ?: 'No remarks provided upon return.' }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Asset Information</h6>
                            <div class="p-3 mb-4" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom: 1px dashed var(--border-color);">
                                    <div style="width: 48px; height: 48px; background: var(--bg-surface); border-radius: 8px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-box-seam fs-4 text-secondary"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block" style="font-size: 15px;">{{ $log->item->name ?? 'Unknown Item' }}</span>
                                        <span class="badge bg-secondary" style="font-size: 10px;">{{ $log->item->category->name ?? 'Uncategorized' }}</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">Property Tag</span>
                                    <span class="font-monospace fw-bold">{{ $log->item->property_tag ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 11px; letter-spacing: 1px;">Borrower Information</h6>
                            <div class="p-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">Full Name</span>
                                    <span class="fw-bold" style="font-size: 15px;">{{ $log->user->name ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-secondary small d-block mb-1">Email Address</span>
                                    <span>{{ $log->user->email ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pb-4 px-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold w-100" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Close History</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>

<script>
// H-UI.1: Timeline view toggle — pass 'timeline' or 'table' to force a view, or omit to toggle
function toggleHistoryView(forceView) {
    var currentView = localStorage.getItem('history-view') || 'table';
    var newView = forceView || (currentView === 'timeline' ? 'table' : 'timeline');
    localStorage.setItem('history-view', newView);
    
    var timelineBtn = document.getElementById('viewTimelineBtn');
    var tableBtn = document.getElementById('viewTableBtn');
    var tableSection = document.getElementById('history-table');
    var timelineSection = document.getElementById('timeline-view');
    
    if (newView === 'timeline') {
        if (timelineBtn) { timelineBtn.style.background = 'var(--text-primary)'; timelineBtn.style.color = 'var(--bg-surface)'; timelineBtn.style.borderColor = 'var(--text-primary)'; }
        if (tableBtn) { tableBtn.style.background = 'var(--bg-surface)'; tableBtn.style.color = 'var(--text-secondary)'; tableBtn.style.borderColor = 'var(--border-color)'; }
        
        // Build timeline view from table rows (always rebuild to sync with current data)
        var existingTimeline = document.getElementById('timeline-view');
        if (existingTimeline) existingTimeline.remove();
        
        var wrapper = document.createElement('div');
        wrapper.id = 'timeline-view';
        wrapper.className = 'panel-card p-0 overflow-hidden shadow-sm';
        wrapper.style.cssText = 'border-radius:12px;';
        
        var rows = document.querySelectorAll('#history-table tbody tr');
        var html = '<div class="p-4">';
        if (rows.length === 0 || (rows.length === 1 && rows[0].querySelector('td[colspan]'))) {
            html += '<div class="text-center py-4 text-secondary">No transactions to display in timeline view.</div>';
        } else {
            rows.forEach(function(row, idx) {
                var cells = row.querySelectorAll('td');
                if (cells.length < 6) return;
                var transId = cells[0]?.textContent?.trim() || 'N/A';
                var itemName = cells[1]?.querySelector('.fw-bold')?.textContent?.trim() || 'Unknown';
                var borrower = cells[2]?.querySelector('.fw-bold')?.textContent?.trim() || 'Unknown';
                // FIXED: Status badge is in cells[4], not cells[3] (cells[3] is the Timeline dates column)
                var statusBadgeText = cells[4]?.querySelector('.badge')?.textContent?.trim() || '';
                var statusBadgeHTML = cells[4]?.innerHTML || '';
                
                // Map badge text to step states
                var norm = statusBadgeText.toUpperCase().replace(/[^A-Z]/g, '');
                var isPending = norm === 'PENDING';
                var isReturnPending = norm === 'RETURNPENDING';
                var isOnLoan = norm === 'ONLOAN' || norm === 'ACTIVE' || norm === 'APPROVED';
                var isReturned = norm === 'RETURNED';
                var isRejected = norm === 'REJECTED';
                var isCancelled = norm === 'CANCELLED';
                
                // 🐛 FIX: isReturnPending must make Approved/Issued steps active (item was already
                // approved and issued — only the return verification is pending)
                // isPending only means the request was just created (no approval yet)
                var steps = [
                    { label: 'Requested', done: true, icon: 'bi-file-earmark-text' },
                    { label: 'Approved', active: isOnLoan || isReturned || isReturnPending, rejected: isRejected || isCancelled, icon: 'bi-check-circle' },
                    { label: 'Issued', active: isOnLoan || isReturned || isReturnPending, icon: 'bi-box-arrow-right' },
                    { label: 'Returned', done: isReturned, icon: 'bi-arrow-return-left' }
                ];
                
                html += '<div class="mb-3 p-3" style="background:var(--bg-main);border-radius:12px;border:1px solid var(--border-color);">';
                html += '<div class="d-flex justify-content-between align-items-center mb-3"><div><span class="fw-bold" style="font-size:13px;">' + transId + '</span><span class="text-secondary ms-2" style="font-size:12px;">' + itemName + '</span></div>' + statusBadgeHTML + '</div>';
                html += '<div class="d-flex align-items-center gap-1">';
                steps.forEach(function(step, si) {
                    var dotColor = 'var(--border-color)';
                    if (step.rejected) {
                        dotColor = 'var(--accent-red)';
                    } else if (step.active) {
                        dotColor = 'var(--accent-blue)';
                    } else if (step.done) {
                        dotColor = 'var(--accent-green)';
                    }
                    var iconColor = step.active || step.done ? '#fff' : 'var(--text-secondary)';
                    html += '<div class="d-flex align-items-center" style="flex:1;">';
                    html += '<div style="width:28px;height:28px;border-radius:50%;background:' + dotColor + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi ' + step.icon + '" style="font-size:12px;color:' + iconColor + ';"></i></div>';
                    html += '<div style="height:2px;flex:1;background:' + dotColor + ';margin:0 4px;"></div>';
                    html += '</div>';
                });
                html += '</div><div class="d-flex justify-content-between mt-1"><span style="font-size:9px;color:var(--text-secondary);">' + borrower + '</span></div>';
                html += '</div>';
            });
        }
        html += '</div>';
        wrapper.innerHTML = html;
        if (tableSection) tableSection.parentNode.insertBefore(wrapper, tableSection);
        timelineSection = wrapper;
        
        if (tableSection) tableSection.style.display = 'none';
        if (timelineSection) timelineSection.style.display = 'block';
    } else {
        if (timelineBtn) { timelineBtn.style.background = 'var(--bg-surface)'; timelineBtn.style.color = 'var(--text-secondary)'; timelineBtn.style.borderColor = 'var(--border-color)'; }
        if (tableBtn) { tableBtn.style.background = 'var(--text-primary)'; tableBtn.style.color = 'var(--bg-surface)'; tableBtn.style.borderColor = 'var(--text-primary)'; }
        if (tableSection) tableSection.style.display = '';
        if (timelineSection) timelineSection.style.display = 'none';
    }
    
    // Sync the URL with the current view so Quick Date pills preserve the correct state
    try {
        var url = new URL(window.location);
        url.searchParams.set('view', newView);
        window.history.replaceState({}, '', url);
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="search"]'); 
    if (searchInput) {
        // Keeps focus at the end of the text if it was reloaded
        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = ''; searchInput.value = val;
        }
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { this.closest('form').submit(); }, 1000); 
        });
    }

    // Fix scroll position: append #history-table anchor to all pagination links
    // so clicking next/prev scrolls to the table, not back to the very top.
    document.querySelectorAll('.pagination a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && !href.includes('#')) {
            link.setAttribute('href', href + '#history-table');
        }
    });
    
    // H-UI.1: Restore view from URL parameter or saved preference
    var urlParams = new URLSearchParams(window.location.search);
    var urlView = urlParams.get('view');
    if (urlView === 'timeline') {
        toggleHistoryView('timeline');
    } else if (urlView === 'table') {
        toggleHistoryView('table');
    } else {
        var savedView = localStorage.getItem('history-view');
        if (savedView === 'timeline') {
            toggleHistoryView('timeline');
        }
    }
});
</script>
@endsection