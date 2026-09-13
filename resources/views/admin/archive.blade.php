@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>Archived Assets</h1>
        <p class="form-label text-secondary">Items disposed from inventory. Restore them or permanently remove records.</p>
    </div>

    {{-- Stats Bar --}}
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <div class="inv-stat-item"><span class="inv-stat-icon"><i class="bi bi-archive"></i></span><span class="inv-stat-label">Total Archived</span><span class="inv-stat-value" id="statTotalArchived">{{ $archivedItems->total() }}</span></div>
        <div class="inv-stat-item stat-blue"><span class="inv-stat-icon"><i class="bi bi-calendar-month"></i></span><span class="inv-stat-label">This Month</span><span class="inv-stat-value" id="statMonthArchived">--</span></div>
        <div class="inv-stat-item stat-green"><span class="inv-stat-icon"><i class="bi bi-arrow-counterclockwise"></i></span><span class="inv-stat-label">Restored</span><span class="inv-stat-value" id="statRestored">--</span></div>
    </div>

    {{-- Search & Filter --}}
    <div class="panel-card p-3 mb-4" style="border-radius: 12px;">
        <form action="/admin/archive" method="GET" class="row g-3 m-0 align-items-end">
            <div class="col-12 col-md-4 p-0 pe-md-2">
                <label class="form-label fw-bold small" style="color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Search</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="search" name="search" class="form-control shadow-none theme-dynamic-input w-100" 
                           style="padding-left: 40px; border-radius: 10px; border: 1px solid var(--border-color);" 
                           placeholder="Name, tag, serial number..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3 p-0 ps-md-2">
                <label class="form-label fw-bold small" style="color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Category</label>
                <select name="category" class="form-select theme-dynamic-input shadow-none" style="border-radius: 10px; border: 1px solid var(--border-color);">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 p-0 ps-md-2">
                <label class="form-label fw-bold small" style="color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Sort By</label>
                <select name="sort" class="form-select theme-dynamic-input shadow-none" style="border-radius: 10px; border: 1px solid var(--border-color);">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                </select>
            </div>
            <div class="col-12 col-md-2 p-0 ps-md-2 d-flex gap-2">
                <noscript><button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 10px;">Filter</button></noscript>
                @if(request('search') || request('category') || request('sort') !== 'newest')
                <a href="/admin/archive" class="btn btn-light w-100 fw-bold d-flex align-items-center justify-content-center" style="border-radius: 10px; border: 1px solid var(--border-color);"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bulk Action Bar --}}
    <div class="bulk-bar" id="archiveBulkBar" style="display:none;">
        <span class="bulk-count" id="archiveBulkCount">0 selected</span>
        <div class="bulk-actions">
            <button class="bulk-btn" onclick="bulkArchiveRestore()" id="bulkRestoreBtn"><i class="bi bi-arrow-counterclockwise"></i> Restore Selected</button>
            <button class="bulk-btn bulk-btn-danger" onclick="bulkArchiveDestroy()" id="bulkDestroyBtn"><i class="bi bi-trash3-fill"></i> Destroy Selected</button>
            <button class="bulk-btn" onclick="bulkArchiveClear()"><i class="bi bi-x-lg"></i> Clear</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="panel-card p-0 overflow-hidden shadow-sm" style="border-radius: 12px; border: 1px solid var(--border-color);">
        {{-- Desktop Table --}}
        <div class="d-none d-md-block">
            <table class="admin-table mb-0">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th class="ps-4" style="width: 36px;"><input type="checkbox" id="archiveSelectAll" class="form-check-input" style="cursor:pointer;"></th>
                        <th>ASSET</th>
                        <th>CATEGORY</th>
                        <th>LOCATION</th>
                        <th>ARCHIVED</th>
                        <th class="text-end pe-4">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivedItems as $item)
                    <tr style="border-bottom: 1px solid var(--border-color); cursor:pointer;" data-id="{{ $item->id }}" class="archive-row" onclick="openArchiveDrawer({{ $item->id }})">
                        <td class="ps-4 py-3" style="width: 36px;" onclick="event.stopPropagation()">
                            <input type="checkbox" class="form-check-input archive-checkbox" value="{{ $item->id }}" style="cursor:pointer;">
                        </td>
                        <td class="ps-2 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:10px;background:var(--bg-surface-hover);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi bi-archive" style="color:var(--text-secondary);font-size:16px;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:13px;color:var(--text-primary);">{{ $item->name }}</div>
                                    <div class="small" style="color:var(--text-secondary);font-size:11px;">{{ $item->property_tag }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary" style="font-size:13px;">{{ $item->category->name ?? 'N/A' }}</td>
                        <td class="text-secondary" style="font-size:13px;">{{ $item->location->name ?? 'N/A' }}</td>
                        <td>
                            <span style="font-size:12px;color:var(--accent-red);font-weight:600;">
                                <i class="bi bi-clock-history me-1"></i>
                                {{ \Carbon\Carbon::parse($item->deleted_at)->diffForHumans() }}
                            </span>
                        </td>
                        <td class="text-end pe-4" onclick="event.stopPropagation()">
                            <div class="btn-group gap-2">
                                <form action="/admin/inventory/{{ $item->id }}/restore" method="POST" class="d-inline restore-form">
                                    @csrf
                                    <button type="button" class="btn btn-sm fw-bold px-3 py-1 restore-asset-btn shadow-none" style="border-radius: 6px; background:var(--accent-green-bg); color:var(--accent-green); border:1px solid var(--accent-green);">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                </form>
                                <form action="/admin/inventory/{{ $item->id }}/force" method="POST" class="d-inline force-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm fw-bold px-3 py-1 force-delete-btn shadow-none" style="border-radius: 6px; background:var(--accent-red-bg); color:var(--accent-red); border:1px solid var(--accent-red);">
                                        <i class="bi bi-trash3-fill"></i> Destroy
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            @if(request('search') || request('category'))
                            <div style="width:64px;height:64px;background:var(--accent-yellow-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="bi bi-search fs-3" style="color:var(--accent-yellow);"></i>
                            </div>
                            <h5 class="fw-bold" style="color:var(--text-primary);">No Matches Found</h5>
                            <p class="small mb-3" style="color:var(--text-secondary);">No archived items match your filters.</p>
                            <a href="/admin/archive" class="btn btn-sm fw-bold" style="background:var(--text-primary);color:var(--bg-surface);border-radius:8px;padding:8px 20px;">Clear Filters</a>
                            @else
                            <div style="width:64px;height:64px;background:var(--bg-surface-hover);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="bi bi-archive fs-3" style="color:var(--text-secondary);"></i>
                            </div>
                            <h5 class="fw-bold" style="color:var(--text-primary);">Archive is Empty</h5>
                            <p class="small mb-0" style="color:var(--text-secondary);">No assets have been disposed yet.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="d-md-none">
            @forelse($archivedItems as $item)
            <div class="archive-mobile-card" style="padding:16px;border-bottom:1px solid var(--border-color);" data-id="{{ $item->id }}">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="form-check-input archive-checkbox" value="{{ $item->id }}" style="cursor:pointer;">
                        <div style="width:36px;height:36px;border-radius:10px;background:var(--accent-red-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-archive" style="color:var(--accent-red);font-size:16px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:14px;color:var(--text-primary);">{{ $item->name }}</div>
                            <div class="small" style="color:var(--text-secondary);font-size:12px;">{{ $item->property_tag }}</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3" style="padding-left:52px;">
                    <span class="small" style="background:var(--bg-surface-hover);padding:3px 10px;border-radius:6px;color:var(--text-secondary);font-size:11px;">
                        <i class="bi bi-tag me-1"></i>{{ $item->tag->name ?? 'N/A' }}
                    </span>
                    <span class="small" style="background:var(--bg-surface-hover);padding:3px 10px;border-radius:6px;color:var(--text-secondary);font-size:11px;">
                        <i class="bi bi-geo-alt me-1"></i>{{ $item->location->name ?? 'N/A' }}
                    </span>
                    <span class="small" style="background:var(--accent-red-bg);padding:3px 10px;border-radius:6px;color:var(--accent-red);font-size:11px;font-weight:600;">
                        <i class="bi bi-clock-history me-1"></i>{{ \Carbon\Carbon::parse($item->deleted_at)->diffForHumans() }}
                    </span>
                </div>
                <div class="d-flex gap-2" style="padding-left:52px;">
                    <form action="/admin/inventory/{{ $item->id }}/restore" method="POST" class="d-inline restore-form flex-fill">
                        @csrf
                        <button type="button" class="btn btn-sm fw-bold w-100 restore-asset-btn shadow-none" style="border-radius:8px;background:var(--accent-green-bg);color:var(--accent-green);border:1px solid var(--accent-green);">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                        </button>
                    </form>
                    <form action="/admin/inventory/{{ $item->id }}/force" method="POST" class="d-inline force-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm fw-bold px-3 force-delete-btn shadow-none" style="border-radius:8px;background:var(--accent-red-bg);color:var(--accent-red);border:1px solid var(--accent-red);">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div style="width:64px;height:64px;background:var(--bg-surface-hover);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-archive fs-3" style="color:var(--text-secondary);"></i>
                </div>
                <h5 class="fw-bold" style="color:var(--text-primary);">Archive is Empty</h5>
                <p class="small mb-0" style="color:var(--text-secondary);">No assets have been disposed yet.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($archivedItems->hasPages())
        <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
            {{ $archivedItems->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

    {{-- Restore Confirmation Modal --}}
    <div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 20px 50px rgba(0,0,0,.15);">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" style="color:var(--accent-green);">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Restore Asset
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-3">
                    <p style="font-size:15px;color:var(--text-secondary);line-height:1.6;margin:0;">This will move the item back into the active inventory pool with <strong style="color:var(--accent-green);">Available</strong> status.</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                    <button type="button" id="confirmRestoreActionBtn" class="btn fw-bold px-3 py-2" style="border-radius: 8px; background:var(--accent-green);color:#fff;">
                        Yes, Restore
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Force Delete Modal --}}
    <div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 14px; border: 1px solid var(--accent-red); box-shadow: 0 20px 50px rgba(239,68,68,.12);">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Permanently Destroy
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-3">
                    <p style="font-size:15px;color:var(--text-secondary);line-height:1.6;margin:0;">This will <strong style="color:var(--accent-red);">permanently remove</strong> the record from the database. This cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); border: 1px solid var(--border-color);">Cancel</button>
                    <button type="button" id="confirmForceDeleteBtn" class="btn fw-bold px-3 py-2" style="border-radius: 8px; background:var(--accent-red);color:#fff;">
                        Yes, Destroy
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Drawer --}}
</div>

<style>
    .theme-dynamic-input { color: var(--text-primary) !important; }
    .theme-dynamic-input::placeholder { color: var(--text-secondary) !important; opacity: 0.7; }
    .theme-dynamic-input:focus { background-color: transparent !important; color: var(--text-primary) !important; box-shadow: none !important; }
    .archive-row:hover { background: var(--bg-surface-hover) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Live Search
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
            debounceTimer = setTimeout(() => { this.closest('form').submit(); }, 800); 
        });
    }

    // Restore Modal
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

    // Force Delete Modal
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

    // Bulk Selection
    var archiveSelectAll = document.getElementById('archiveSelectAll');
    if (archiveSelectAll) {
        archiveSelectAll.addEventListener('change', function() {
            document.querySelectorAll('.archive-checkbox').forEach(function(cb) { cb.checked = archiveSelectAll.checked; });
            updateArchiveBulkBar();
        });
    }
    document.querySelectorAll('.archive-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateArchiveBulkBar);
    });

    function updateArchiveBulkBar() {
        var count = document.querySelectorAll('.archive-checkbox:checked').length;
        var bar = document.getElementById('archiveBulkBar');
        if (count > 0) {
            bar.style.display = 'flex';
            document.getElementById('archiveBulkCount').textContent = count + ' selected';
        } else {
            bar.style.display = 'none';
        }
    }

    window.bulkArchiveRestore = function() {
        var ids = [];
        document.querySelectorAll('.archive-checkbox:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        if (!confirm('Restore ' + ids.length + ' archived items back to active inventory?')) return;
        var btn = document.getElementById('bulkRestoreBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Restoring...';
        btn.disabled = true;
        fetch('/admin/inventory/bulk-restore', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ ids: ids.join(',') })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) { if (data.success) window.location.reload(); else { alert('Error restoring items.'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Restore Selected'; } })
        .catch(function() { btn.disabled = false; btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Restore Selected'; });
    };

    window.bulkArchiveDestroy = function() {
        var ids = [];
        document.querySelectorAll('.archive-checkbox:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        if (!confirm('PERMANENTLY DESTROY ' + ids.length + ' items? This cannot be undone.')) return;
        var btn = document.getElementById('bulkDestroyBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Destroying...';
        btn.disabled = true;
        fetch('/admin/inventory/bulk-force', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ ids: ids.join(',') })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) { if (data.success) window.location.reload(); else { alert('Error destroying items.'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-trash3-fill"></i> Destroy Selected'; } })
        .catch(function() { btn.disabled = false; btn.innerHTML = '<i class="bi bi-trash3-fill"></i> Destroy Selected'; });
    };

    window.bulkArchiveClear = function() {
        document.querySelectorAll('.archive-checkbox').forEach(function(cb) { cb.checked = false; });
        if (archiveSelectAll) archiveSelectAll.checked = false;
        updateArchiveBulkBar();
    };

    // Shift+click multi-select
    document.querySelectorAll('.archive-checkbox').forEach(function(cb) {
        cb.addEventListener('click', function(e) {
            if (e.shiftKey) {
                var cbs = document.querySelectorAll('.archive-checkbox');
                var start = -1, end = -1;
                cbs.forEach(function(c, i) { if (c === cb) end = i; if (c.checked && start === -1 && c !== cb) start = i; });
                if (start !== -1 && end !== -1) {
                    for (var i = Math.min(start,end); i <= Math.max(start,end); i++) cbs[i].checked = true;
                    updateArchiveBulkBar();
                }
            }
        });
    });

    // Auto-submit on sort/category change
    document.querySelectorAll('select[name="category"], select[name="sort"]').forEach(function(sel) {
        sel.addEventListener('change', function() { this.closest('form').submit(); });
    });

    // Refresh stats
    fetch('/admin/archive/stats').then(function(r) { return r.json(); }).then(function(data) {
        var el;
        if (el = document.getElementById('statMonthArchived')) el.textContent = data.thisMonth;
        if (el = document.getElementById('statRestored')) el.textContent = data.restoredLastMonth;
    }).catch(function() {});
});

// Archive Details Drawer
window.openArchiveDrawer = function(id) {
    AppDrawer.show({
        overline: 'ARCHIVED ASSET',
        title: 'Loading...',
        body: '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>',
    });

    fetch('/admin/inventory/' + id + '/details')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var esc = window.escapeHtml || function(t) { return t; };
            var html = '';
            // Header
            html += '<div class="d-flex align-items-center gap-3 mb-4">';
            html += '<div style="width:48px;height:48px;border-radius:14px;background:var(--accent-red-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-archive" style="color:var(--accent-red);font-size:22px;"></i></div>';
            html += '<div><div class="fw-bold" style="font-size:18px;color:var(--text-primary);">' + esc(d.name) + '</div>';
            html += '<div style="font-size:12px;color:var(--text-secondary);">' + esc(d.property_tag) + '</div></div></div>';
            // Status pill
            html += '<div class="mb-4"><span class="badge rounded-pill px-3 py-2 fw-bold" style="background:var(--bg-surface-hover);color:var(--text-secondary);font-size:11px;border:1px solid var(--border-color);">DISPOSED</span>';
            html += '<span style="font-size:12px;color:var(--text-secondary);margin-left:8px;">Archived ' + (d.updated_human || '') + '</span></div>';
            // Details table
            html += '<div style="background:var(--bg-main);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">';
            var rows = [
                ['Category', d.category],
                ['Tag', d.tag],
                ['Location', d.location],
                ['Location Code', d.location_code],
                ['Supplier', d.supplier],
                ['Serial Number', d.serial_number],
                ['Cost', d.acquisition_cost],
                ['Acquisition Date', d.acquisition_date],
                ['Accountable Personnel', d.accountable_personnel],
                ['Registered', d.registered_at],
            ];
            rows.forEach(function(r) {
                if (r[1]) {
                    html += '<div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom:1px solid var(--border-color);font-size:13px;">';
                    html += '<span style="color:var(--text-secondary);font-weight:500;">' + esc(r[0]) + '</span>';
                    html += '<span style="color:var(--text-primary);font-weight:600;text-align:right;">' + esc(r[1]) + '</span></div>';
                }
            });
            html += '</div>';
            // Actions
            html += '<div class="d-flex gap-2 mt-4">';
            html += '<form action="/admin/inventory/' + d.id + '/restore" method="POST" class="flex-fill restore-form"><input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + '"><button type="button" class="btn w-100 fw-bold restore-asset-btn" style="border-radius:10px;background:var(--accent-green);color:#fff;padding:10px;"><i class="bi bi-arrow-counterclockwise me-2"></i>Restore to Inventory</button></form>';
            html += '<form action="/admin/inventory/' + d.id + '/force" method="POST" class="force-form"><input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + '"><input type="hidden" name="_method" value="DELETE"><button type="button" class="btn fw-bold px-3 force-delete-btn" style="border-radius:10px;background:var(--accent-red-bg);color:var(--accent-red);border:1px solid var(--accent-red);padding:10px 16px;"><i class="bi bi-trash3-fill"></i></button></form>';
            html += '</div>';

            AppDrawer.setBody(html);
            document.getElementById('adTitle').textContent = d.name;
        })
        .catch(function() {
            AppDrawer.setBody('<div class="text-center py-5 text-danger">Failed to load details.</div>');
        });
};
</script>
@endsection
