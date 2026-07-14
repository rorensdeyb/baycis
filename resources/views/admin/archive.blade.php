@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>Archived Assets</h1>
        <p class="form-label text-secondary">Review, restore, or permanently destroy previously deleted records.</p>
    </div>

    <div class="panel-card d-flex justify-content-between align-items-center mb-4 p-3" style="border-radius: 12px;">
        <form action="/admin/archive" method="GET" class="d-flex align-items-center m-0">
            <div class="position-relative" style="width: 350px;">
                <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="search" name="search" class="form-control shadow-none theme-dynamic-input w-100" 
                       style="padding-left: 42px;" 
                       placeholder="Search archived assets, tags..." 
                       value="{{ request('search') }}">
            </div>
        </form>
    </div>

    <div class="panel-card p-0 overflow-hidden shadow-sm">
        <table class="admin-table mb-0">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <th class="ps-4">PROPERTY TAG</th>
                    <th>ITEM NAME</th>
                    <th>DATE DELETED</th>
                    <th class="text-end pe-4">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($archivedItems as $item)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td class="ps-4 py-3">
                        <span class="small fw-bold">{{ $item->property_tag }}</span>
                    </td>
                    <td class="fw-bold">{{ $item->name }}</td>
                    <td class="text-danger fw-bold">
                        <i class="bi bi-clock-history me-1"></i> 
                        {{ \Carbon\Carbon::parse($item->deleted_at)->format('M d, Y h:i A') }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group gap-2">
                            <form action="/admin/inventory/{{ $item->id }}/restore" method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn btn-sm btn-success fw-bold px-3 py-1 restore-asset-btn shadow-none" style="border-radius: 6px;">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                            </form>

                            <form action="/admin/inventory/{{ $item->id }}/force" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger fw-bold px-3 py-1 force-delete-btn shadow-none" style="border-radius: 6px;">
                                    <i class="bi bi-trash3-fill"></i> Destroy
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">No archived items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid var(--text-secondary); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Confirm Restoration
                    </h5>
                    <button type="button" class="btn-close" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-3 text-secondary" style="font-size: 15px; line-height: 1.5; color: var(--text-secondary) !important;">
                    Are you sure you want to restore this asset? This will move the item and its historical tracking data back into the active inventory pool.
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel</button>
                    <button type="button" id="confirmRestoreActionBtn" class="btn btn-success fw-bold px-3 py-2" style="border-radius: 8px;">
                        Yes, Restore Asset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid #ff4d4d; box-shadow: 0 10px 30px rgba(255,0,0,0.15);">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-radioactive me-2"></i> PERMANENT DESTRUCTION
                    </h5>
                    <button type="button" class="btn-close" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-3 text-secondary" style="font-size: 15px; line-height: 1.5; color: var(--text-secondary) !important;">
                    You are about to permanently eradicate this asset from the database. <strong style="color: var(--text-primary);">This action bypasses the archive and cannot be undone.</strong> Are you absolutely sure?
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel</button>
                    <button type="button" id="confirmForceDeleteBtn" class="btn btn-danger fw-bold px-3 py-2" style="border-radius: 8px;">
                        Yes, Eradicate Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .theme-dynamic-input { color: var(--text-primary) !important; }
    .theme-dynamic-input::placeholder { color: var(--text-secondary) !important; opacity: 0.7; }
    .theme-dynamic-input:focus { background-color: transparent !important; color: var(--text-primary) !important; box-shadow: none !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Search (Debounced)
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

    // 2. Restore Modal Logic Interception
    let targetRestoreForm = null;
    document.addEventListener('click', function(e) {
        const restoreBtn = e.target.closest('.restore-asset-btn');
        if (restoreBtn) {
            e.preventDefault();
            targetRestoreForm = restoreBtn.closest('form');
            new bootstrap.Modal(document.getElementById('restoreConfirmModal')).show();
        }
    });

    const confirmRestoreBtn = document.getElementById('confirmRestoreActionBtn');
    if (confirmRestoreBtn) {
        confirmRestoreBtn.addEventListener('click', function() {
            if (targetRestoreForm) {
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Restoring...';
                this.disabled = true;
                targetRestoreForm.submit();
            }
        });
    }

    // 3. Force Delete Modal Logic
    let targetForceForm = null;
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.force-delete-btn');
        if (deleteBtn) {
            e.preventDefault();
            targetForceForm = deleteBtn.closest('form');
            new bootstrap.Modal(document.getElementById('forceDeleteModal')).show();
        }
    });

    const confirmForceBtn = document.getElementById('confirmForceDeleteBtn');
    if (confirmForceBtn) {
        confirmForceBtn.addEventListener('click', function() {
            if (targetForceForm) {
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Destroying...';
                this.disabled = true;
                targetForceForm.submit();
            }
        });
    }
});
</script>
@endsection