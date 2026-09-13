<div id="pending-panel-content">
    <h6 class="fw-bold mt-1 mb-2" style="color:var(--text-primary);"><i class="bi bi-inbox me-1"></i>Borrower Requests</h6>
    <table class="admin-table w-100 mb-4">
        <thead><tr><th>Borrower</th><th>Item</th><th class="text-center">Qty</th><th>Purpose</th><th>Requested</th><th class="text-end pe-3">Actions</th></tr></thead>
        <tbody>
            @forelse($pendingRequests as $r)
            <tr data-issuance-id="{{ $r->id }}">
                <td class="fw-semibold">{{ $r->user?->name ?? 'Unknown' }}</td>
                <td>{{ $r->item?->name ?? 'Unknown' }}</td>
                <td class="text-center fw-bold">{{ $r->quantity }}</td>
                <td class="text-truncate" style="max-width:220px;" title="{{ $r->purpose }}">{{ Str::limit($r->purpose, 60) }}</td>
                <td class="text-secondary small">{{ $r->created_at->diffForHumans() }}</td>
                <td class="text-end pe-3">
                    <div class="d-inline-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-success btn-sm fw-bold px-3 js-fulfill-btn"
                                data-id="{{ $r->id }}" style="border-radius:var(--radius-md,10px);"><i class="bi bi-box-arrow-up-right me-1"></i>Issue &amp; Deduct</button>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-semibold px-3"
                                onclick="cancelReason.open({{ $r->id }}, '{{ addslashes(($r->user?->name ?? 'user').' — '.($r->item?->name ?? '')) }}')" style="border-radius:var(--radius-md,10px);"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-secondary">No pending requests 🎉</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($pendingRequests->hasPages()){{ $pendingRequests->links('pagination::bootstrap-5') }}@endif

    <h6 class="fw-bold mt-4 mb-2" style="color:var(--text-primary);"><i class="bi bi-hourglass me-1"></i>Awaiting Borrower Confirmation</h6>
    <table class="admin-table w-100">
        <thead><tr><th>Borrower</th><th>Item</th><th class="text-center">Qty</th><th>Issued By</th><th>Issued</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead>
        <tbody>
            @forelse($pendingConfirmations as $c)
            <tr>
                <td class="fw-semibold">{{ $c->user?->name ?? 'Unknown' }}</td>
                <td>{{ $c->item?->name ?? 'Unknown' }}@if($c->group_id)<span class="badge rounded-pill ms-1" style="background:rgba(59,130,246,.1);color:var(--accent-blue);font-size:9.5px;">KIT</span>@endif</td>
                <td class="text-center fw-bold">{{ $c->quantity }}</td>
                <td class="text-secondary">{{ $c->issuer?->name ?? 'Staff' }}</td>
                <td class="text-secondary small">{{ $c->issued_at?->diffForHumans() }}</td>
                <td><span class="badge rounded-pill fw-bold" style="background:rgba(139,92,246,.12);color:#8b5cf6;">Pending Confirmation</span></td>
                <td class="text-end pe-3">
                    <button type="button" class="btn btn-outline-danger btn-sm fw-semibold px-3"
                            onclick="cancelReason.open({{ $c->id }}, '{{ addslashes(($c->user?->name ?? 'user').' — '.($c->item?->name ?? '')) }}')" style="border-radius:var(--radius-md,10px);"><i class="bi bi-arrow-counterclockwise me-1"></i>Cancel &amp; Restore</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-secondary">Nothing awaiting confirmation.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($pendingConfirmations->hasPages()){{ $pendingConfirmations->links('pagination::bootstrap-5') }}@endif
</div>
