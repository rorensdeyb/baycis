@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>Asset Issuance</h1>
        <p class="form-label text-secondary">Manage long-term accountability assignments via Property Acknowledgment Receipts (PAR) and Inventory Custodian Slips (ICS).</p>
    </div>

    <div class="panel-card d-flex justify-content-between align-items-center mb-4 p-3" style="border-radius: 12px;">
        <form action="/admin/issuance" method="GET" class="d-flex align-items-center m-0">
            <div class="position-relative" style="width: 350px;">
                <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="search" name="search" class="form-control shadow-none theme-dynamic-input w-100" 
                       style="padding-left: 42px;" 
                       placeholder="Search custodian, item, tag..." 
                       value="{{ request('search') }}">
            </div>
        </form>

        <button class="btn btn-primary fw-bold d-flex align-items-center gap-2 px-4 py-2" data-bs-toggle="modal" data-bs-target="#issueAssetModal" style="border-radius: 8px;">
            <i class="bi bi-person-check-fill"></i> Issue New Asset
        </button>
    </div>

    <div class="panel-card p-0 overflow-hidden shadow-sm">
        <table class="admin-table mb-0">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <th class="ps-4">PROPERTY TAG</th>
                    <th>ASSET DESCRIPTION</th>
                    <th>ACCOUNTABLE OFFICER</th>
                    <th>DATE ISSUED</th>
                    <th>DOCUMENT TYPE</th>
                    <th class="text-end pe-4">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issuances as $issue)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td class="ps-4 py-3">
                        <span class="small fw-bold">{{ $issue->item->property_tag ?? 'N/A' }}</span>
                    </td>
                    <td class="fw-bold">{{ $issue->item->name ?? 'Unknown Asset' }}</td>
                    <td class="fw-semibold text-primary-theme">{{ $issue->user->name ?? 'N/A' }}</td>
                    <td class="text-secondary">
                        <i class="bi bi-calendar-check me-1"></i>
                        {{ \Carbon\Carbon::parse($issue->created_at)->format('M d, Y') }}
                    </td>
                    <td>
                        @if(($issue->item->acquisition_cost ?? 0) >= 50000)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold rounded-pill px-3">PAR</span>
                        @else
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary fw-bold rounded-pill px-3">ICS</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm border shadow-none" style="color: var(--text-secondary); border-color: var(--border-color) !important;" title="View Document">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="color: var(--text-primary);">
                        <i class="bi bi-inboxes display-4 d-block mb-3 opacity-25" style="color: var(--text-secondary); opacity: 0.4;"></i>
                        No active asset issuances found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .theme-dynamic-input { background: var(--bg-main) !important; border: 1px solid var(--border-color) !important; color: var(--text-primary) !important; }
    .theme-dynamic-input::placeholder { color: var(--text-secondary) !important; opacity: 0.7; }
    .theme-dynamic-input:focus { background-color: transparent !important; color: var(--text-primary) !important; box-shadow: none !important; }
    .text-primary-theme { color: var(--text-primary); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Live Debounced Search
    const searchInput = document.querySelector('input[name="search"]'); 
    if (searchInput) {
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
});
</script>
@endsection