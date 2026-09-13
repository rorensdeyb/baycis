@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>System Settings</h1>
        <p class="form-label text-secondary">Configure system behavior, inventory rules, organizational data, and administrative preferences.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row gx-4">
        <!-- ==========================================
             VERTICAL NAVIGATION TABS
             ========================================== -->
        <div class="col-lg-3 mb-4">
            <div class="panel-card p-3 sticky-top" style="top: 24px; border-radius: 12px;">
                <div class="nav flex-column nav-pills custom-settings-nav" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                    {{-- System Preferences --}}
                    <div class="nav-category mt-4 mb-2 px-3 fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">System Preferences</div>

                    <button class="nav-link active d-flex align-items-center gap-2 mb-1" id="v-pills-general-tab" data-bs-toggle="pill" data-bs-target="#v-pills-general" type="button" role="tab">
                        <i class="bi bi-sliders"></i> General
                    </button>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-inventory-tab" data-bs-toggle="pill" data-bs-target="#v-pills-inventory" type="button" role="tab">
                        <i class="bi bi-upc-scan"></i> Inventory Rules
                    </button>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-appearance-tab" data-bs-toggle="pill" data-bs-target="#v-pills-appearance" type="button" role="tab">
                        <i class="bi bi-palette"></i> Appearance
                    </button>

                    {{-- Inventory Reference --}}
                    <div class="nav-category mt-4 mb-2 px-3 fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Inventory Reference</div>

                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-departments-tab" data-bs-toggle="pill" data-bs-target="#v-pills-departments" type="button" role="tab">
                        <i class="bi bi-building"></i> Departments
                    </button>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-suppliers-tab" data-bs-toggle="pill" data-bs-target="#v-pills-suppliers" type="button" role="tab">
                        <i class="bi bi-truck"></i> Suppliers
                    </button>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-categories-tab" data-bs-toggle="pill" data-bs-target="#v-pills-categories" type="button" role="tab">
                        <i class="bi bi-tags"></i> Asset Categories
                    </button>

                    {{-- Security & Data --}}
                    <div class="nav-category mt-4 mb-2 px-3 fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Security & Data</div>

                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-backup-tab" data-bs-toggle="pill" data-bs-target="#v-pills-backup" type="button" role="tab">
                        <i class="bi bi-database-down"></i> Backup & Restore
                    </button>
                    <a href="{{ route('admin.audit-logs') }}" class="nav-link d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-journal-text"></i> Audit Logs
                    </a>
                </div>
            </div>
        </div>

        <!-- ==========================================
             TAB CONTENT PANES
             ========================================== -->
        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">

                {{-- TAB PANE: General --}}
                <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                            <i class="bi bi-sliders me-2 text-primary"></i> General Settings
                        </h5>

                        <form action="/admin/settings/general" method="POST">
                            @csrf
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">System Name</label>
                                    <input type="text" name="system_name" class="form-control theme-dynamic-input" value="{{ $settings['system_name'] }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Organization / School</label>
                                    <input type="text" name="org_name" class="form-control theme-dynamic-input" value="{{ $settings['org_name'] }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Timezone</label>
                                    <select name="timezone" class="form-select theme-dynamic-input">
                                        <option value="Asia/Manila" {{ $settings['timezone'] === 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (PHT)</option>
                                        <option value="UTC" {{ $settings['timezone'] === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Date Format</label>
                                    <select name="date_format" class="form-select theme-dynamic-input">
                                        <option value="Y-m-d" {{ $settings['date_format'] === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (e.g. 2026-05-16)</option>
                                        <option value="M d, Y" {{ $settings['date_format'] === 'M d, Y' ? 'selected' : '' }}>MMM DD, YYYY (e.g. May 16, 2026)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">Save General Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- TAB PANE: Inventory Rules --}}
                <div class="tab-pane fade" id="v-pills-inventory" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                            <i class="bi bi-upc-scan me-2 text-primary"></i> Inventory Configuration
                        </h5>

                        <form action="/admin/settings/inventory" method="POST">
                            @csrf

                            <div class="mb-4 p-3 rounded" style="border: 1px solid var(--text-secondary);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: var(--text-primary);">Auto-Generate Property Tags</h6>
                                        <small class="text-secondary">Automatically assign sequential tags during asset registration.</small>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="auto_tags" {{ $settings['auto_tags'] === 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Property Tag Format</label>
                                    <input type="text" class="form-control theme-dynamic-input fw-bold" value="YYYY-PPE-GL-XXXX(X)-SCHID" disabled>
                                    <small class="text-muted mt-1 d-block">Locked to official DepEd standard format.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Low Stock Alert Threshold</label>
                                    <div class="input-group">
                                        <input type="number" name="low_stock_threshold" class="form-control theme-dynamic-input" value="{{ $settings['low_stock_threshold'] }}">
                                        <span class="input-group-text theme-dynamic-addon border-start-0">Items</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">Save Inventory Rules</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- TAB PANE: Departments --}}
                <div class="tab-pane fade" id="v-pills-departments" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">
                                <i class="bi bi-building me-2 text-primary"></i> Departments
                            </h5>
                            <button type="button" class="btn btn-primary fw-bold d-flex align-items-center gap-1 px-3 py-2" style="border-radius: 8px;" onclick="openLocationModal()">
                                <i class="bi bi-plus-lg"></i> Add Department
                            </button>
                        </div>
                        <p class="text-secondary small mb-4">Manage organizational departments and their assigned asset location codes.</p>

                        <div class="table-responsive">
                            <table class="admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>NAME</th>
                                        <th>CODE</th>
                                        <th>LINKED ASSETS</th>
                                        <th class="text-end">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $dept)
                                    @php if (!($dept instanceof \App\Models\Location)) continue; @endphp
                                    <tr>
                                        <td class="fw-bold" style="color: var(--text-primary);">{{ $dept->name }}</td>
                                        <td><code style="background: var(--bg-main); padding: 3px 10px; border-radius: 6px; color: var(--text-primary);">{{ $dept->code ?: 'â€”' }}</code></td>
                                        <td><span class="badge rounded-pill" style="background: var(--accent-blue-bg); color: var(--accent-blue); font-weight: 600;">{{ $dept->items_count ?? 0 }}</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1 py-1 px-2" onclick="editLocation({{ $dept->id }},'{{ addslashes($dept->name) }}','{{ addslashes($dept->code ?? '') }}')" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDelete('location',{{ $dept->id }},'{{ addslashes($dept->name) }}')" title="Delete"><i class="bi bi-trash3"></i></button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-building text-secondary d-block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-secondary mb-1">No departments yet.</p>
                                            <button class="btn btn-sm btn-primary fw-semibold mt-1" onclick="openLocationModal()">Add the first department</button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE: Suppliers --}}
                <div class="tab-pane fade" id="v-pills-suppliers" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">
                                <i class="bi bi-truck me-2 text-primary"></i> Suppliers
                            </h5>
                            <button type="button" class="btn btn-primary fw-bold d-flex align-items-center gap-1 px-3 py-2" style="border-radius: 8px;" onclick="openSupplierModal()">
                                <i class="bi bi-plus-lg"></i> Add Supplier
                            </button>
                        </div>
                        <p class="text-secondary small mb-4">Manage vendors and suppliers used for inventory procurement tracking.</p>

                        <div class="table-responsive">
                            <table class="admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>TAG COLOR</th>
                                        <th>NAME</th>
                                        <th>LINKED ASSETS</th>
                                        <th class="text-end">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($suppliers as $supplier)
                                    @php if (!($supplier instanceof \App\Models\Supplier)) continue; @endphp
                                    <tr>
                                        <td><span style="display:inline-block;width:24px;height:24px;border-radius:6px;background:{{ $supplier->color ?? '#FFFF00' }};border:1px solid rgba(0,0,0,0.1);vertical-align:middle;"></span></td>
                                        <td class="fw-bold" style="color: var(--text-primary);">{{ $supplier->name }}</td>
                                        <td><span class="badge rounded-pill" style="background: rgba(16,185,129,0.1); color: #10b981; font-weight: 600;">{{ $supplier->items_count ?? 0 }}</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1 py-1 px-2" onclick="editSupplier({{ $supplier->id }},'{{ addslashes($supplier->name) }}','{{ $supplier->color ?? '#FFFF00' }}')" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDelete('supplier',{{ $supplier->id }},'{{ addslashes($supplier->name) }}')" title="Delete"><i class="bi bi-trash3"></i></button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-truck text-secondary d-block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-secondary mb-1">No suppliers yet.</p>
                                            <button class="btn btn-sm btn-primary fw-semibold mt-1" onclick="openSupplierModal()">Add the first supplier</button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE: Asset Categories --}}
                <style>
                    .cat-action-btn {
                        width: 30px; height: 30px;
                        display: inline-flex; align-items: center; justify-content: center;
                        padding: 0 !important;
                        border-radius: 8px;
                        background: var(--bg-surface);
                        border: 1px solid var(--border-color);
                        font-size: 13px;
                        transition: color .15s ease, border-color .15s ease, background .15s ease;
                    }
                    .cat-action-btn:hover { background: var(--bg-main); color: var(--accent-blue); border-color: var(--accent-blue); }
                    .cat-action-btn.cat-action-danger:hover { color: #dc3545; border-color: #dc3545; background: rgba(220,53,69,.05); }
                    .cat-action-btn.cat-action-danger { color: var(--text-secondary); }
                </style>
                <div class="tab-pane fade" id="v-pills-categories" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">
                                <i class="bi bi-tags me-2 text-primary"></i> Asset Categories
                            </h5>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.settings.inventory-reference') }}"
                                   class="btn fw-semibold d-inline-flex align-items-center gap-2 px-3 py-2"
                                   style="border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); background: var(--bg-surface);">
                                    <i class="bi bi-bookmark-star" style="color: var(--accent-blue);"></i> Sub-Categories / Tags
                                </a>
                                <button type="button" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-1 px-3 py-2" style="border-radius: 8px;" onclick="openCategoryModal()">
                                    <i class="bi bi-plus-lg"></i> Add Category
                                </button>
                            </div>
                        </div>
                        <p class="text-secondary small mb-3">Classify assets by DepEd PPE classification. Sub-categories are managed on the Inventory Reference page.</p>

                        <div class="position-relative mb-3" style="max-width: 320px;">
                            <i class="bi bi-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-secondary); font-size:13px;"></i>
                            <input type="text" id="categoryFilterInput" class="form-control form-control-sm" placeholder="Filter categoriesâ€¦" autocomplete="off"
                                   style="padding-left:34px; border-radius:10px; background:var(--bg-main); border-color:var(--border-color); color:var(--text-primary);">
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table mb-0" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>CATEGORY</th>
                                        <th>PPE / GL CODES</th>
                                        <th>SUB-CATEGORIES</th>
                                        <th>ASSETS</th>
                                        <th class="text-end pe-4" style="width:96px;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $category)
                                    @php
                                        if (!($category instanceof \App\Models\Category)) continue;
                                        $catTags = ($tags[$category->id] ?? collect())->sortBy('name');
                                        $tagPreview = $catTags->take(3);
                                        $refUrl = route('admin.settings.inventory-reference') . '#cat-' . $category->id;
                                    @endphp
                                    <tr data-filter="{{ strtolower($category->name) }} {{ strtolower($catTags->pluck('name')->implode(' ')) }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                                     style="width:30px;height:30px;border-radius:8px;background:var(--accent-blue-bg);color:var(--accent-blue);">
                                                    <i class="bi bi-folder2" style="font-size:13px;"></i>
                                                </div>
                                                <span class="fw-bold" style="color: var(--text-primary);">{{ $category->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <span class="badge rounded-pill font-monospace" title="PPE Sub-Major" style="background: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color); font-weight:600;">PPE&nbsp;{{ $category->ppe_sub_major ?: 'â€”' }}</span>
                                                <span class="badge rounded-pill font-monospace" title="GL Ledger Account" style="background: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color); font-weight:600;">GL&nbsp;{{ $category->gl_ledger_acct ?: 'â€”' }}</span>
                                            </div>
                                        </td>
                                        <td style="max-width:280px;">
                                            @if($catTags->count() > 0)
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    @foreach($tagPreview as $t)
                                                        <span class="badge rounded-pill" style="font-weight:600; background: rgba(59,130,246,0.08); color: var(--accent-blue); border:1px solid rgba(59,130,246,0.25);">{{ $t->name }}</span>
                                                    @endforeach
                                                    @if($catTags->count() > 3)
                                                        <a href="{{ $refUrl }}" class="badge rounded-pill text-decoration-none" style="background: var(--bg-main); color: var(--text-secondary); border:1px dashed var(--border-color);">+{{ $catTags->count() - 3 }} more</a>
                                                    @endif
                                                </div>
                                            @else
                                                <a href="{{ $refUrl }}" class="small text-decoration-none" style="color: var(--text-secondary);">Add tags <i class="bi bi-arrow-right"></i></a>
                                            @endif
                                        </td>
                                        <td><span class="badge rounded-pill" style="background: rgba(245,158,11,0.1); color: #f59e0b; font-weight: 600;">{{ $category->items_count ?? 0 }}</span></td>
                                        <td class="text-end pe-4 align-middle">
                                            <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                                <button type="button" class="btn btn-sm cat-action-btn text-secondary"
                                                        onclick="editCategory({{ $category->id }},'{{ addslashes($category->name) }}','{{ addslashes($category->ppe_sub_major ?? '') }}','{{ addslashes($category->gl_ledger_acct ?? '') }}')"
                                                        title="Edit category" aria-label="Edit {{ $category->name }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm cat-action-btn cat-action-danger"
                                                        onclick="confirmDelete('category',{{ $category->id }},'{{ addslashes($category->name) }}')"
                                                        title="Delete category" aria-label="Delete {{ $category->name }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-tags text-secondary d-block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-secondary mb-1">No asset categories yet.</p>
                                            <button class="btn btn-sm btn-primary fw-semibold mt-1" onclick="openCategoryModal()">Add the first category</button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- â”€â”€ SUB-CATEGORIES / TAGS â†’ dedicated page â”€â”€ --}}
                        <div class="mt-4 pt-4 d-flex flex-wrap justify-content-between align-items-center gap-3" style="border-top: 1px solid var(--border-color);">
                            <div>
                                <h6 class="fw-bold mb-1" style="color: var(--text-primary);">
                                    <i class="bi bi-bookmark-star me-2 text-primary"></i> Sub-Categories / Tags
                                </h6>
                                <p class="text-secondary small mb-0">
                                    Add, rename or remove sub-categories on every Asset Category from the Inventory Reference page.
                                </p>
                            </div>
                            <a href="{{ route('admin.settings.inventory-reference') }}"
                               class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2 px-4 py-2 flex-shrink-0"
                               style="border-radius: 8px;">
                                <i class="bi bi-box-arrow-up-right"></i> Open Inventory Reference
                            </a>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const input = document.getElementById('categoryFilterInput');
                        input?.addEventListener('input', function () {
                            const q = this.value.trim().toLowerCase();
                            document.querySelectorAll('#categoriesTable tbody tr[data-filter]').forEach(function (tr) {
                                tr.style.display = tr.dataset.filter.includes(q) ? '' : 'none';
                            });
                        });
                    });
                </script>

                {{-- TAB PANE: Backup & Restore --}}
                <div class="tab-pane fade" id="v-pills-backup" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                            <i class="bi bi-database-down me-2 text-primary"></i> Backup & Restore
                        </h5>

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold" role="alert" style="border-radius: 10px;">
                                <i class="bi bi-exclamation-octagon-fill"></i>
                                <div>{{ session('error') }}</div>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Safety Warning:</strong> Restoring a previous backup will permanently overwrite all current system data, including recently added inventory items and transactions. Always download a fresh backup file before performing a restoration.
                            </div>
                        </div>

                        <div class="row gap-4 px-2">
                            <div class="col p-4 rounded text-center d-flex flex-column justify-content-between" style="border: 1px dashed var(--text-secondary); background: transparent;">
                                <div>
                                    <i class="bi bi-cloud-arrow-down text-success mb-3 d-block" style="font-size: 3rem;"></i>
                                    <h6 class="fw-bold" style="color: var(--text-primary);">Download Backup</h6>
                                    <p class="text-secondary small">Generate an instant, structural .sql dump copy of your current system database.</p>
                                </div>
                                <a href="{{ route('admin.backup.download') }}" class="btn btn-success fw-bold w-100 py-2 mt-3">
                                    <i class="bi bi-download me-1"></i> Generate Backup
                                </a>
                            </div>

                            <div class="col p-4 rounded text-center d-flex flex-column justify-content-between" style="border: 1px dashed var(--text-secondary); background: transparent;">
                                <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" id="systemRestoreForm">
                                    @csrf
                                    <input type="hidden" name="password" id="hiddenRestorePassword">

                                    <div>
                                        <i class="bi bi-cloud-arrow-up text-danger mb-3 d-block" style="font-size: 3rem;"></i>
                                        <h6 class="fw-bold" style="color: var(--text-primary);">Restore System</h6>
                                        <p class="text-secondary small">Upload a previously generated .sql file to roll back database rows.</p>
                                    </div>

                                    <div class="mb-3 text-start mt-2">
                                        <input type="file" name="backup_file" class="form-control theme-dynamic-input form-control-sm" accept=".sql" required>
                                    </div>

                                    <button type="button" class="btn btn-outline-danger fw-bold w-100 py-2 border-2" id="triggerRestoreBtn">
                                        <i class="bi bi-shield-exclamation me-1"></i> Upload Backup File
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE: Appearance --}}
                <div class="tab-pane fade" id="v-pills-appearance" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                            <i class="bi bi-palette me-2 text-primary"></i> Appearance Customization
                        </h5>

                        {{-- Theme Mode Toggle --}}
                        <div class="mb-4 p-4 rounded" style="border: 1px solid var(--border-color); background: var(--bg-main);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1 fs-5 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                                        <i class="bi {{ session('theme') === 'dark' ? 'bi-moon-stars' : 'bi-sun' }}" id="settingsThemeIcon"></i>
                                        Theme Mode
                                    </h6>
                                    <p class="text-secondary small mb-0">Switch between Light and Dark mode. You can also toggle this from the header icon.</p>
                                </div>
                                <div class="form-check form-switch fs-3">
                                    <input class="form-check-input" type="checkbox" id="settingsDarkToggle" role="switch"
                                           onchange="toggleTheme()"
                                           {{ session('theme') === 'dark' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        {{-- Color Palette Picker --}}
                        <div class="mb-4 p-4 rounded" style="border: 1px solid var(--border-color); background: var(--bg-main);">
                            <h6 class="fw-bold mb-1 fs-5 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                                <i class="bi bi-palette"></i>
                                Color Palette
                            </h6>
                            <p class="text-secondary small mb-3">Choose an accent color palette that complements the system theme. Changes apply instantly across all pages.</p>
                            <div class="d-flex flex-wrap" style="gap: 8px;" id="settingsPaletteOptions">
                                <button class="palette-option settings-palette-opt" data-palette="default" onclick="setPalette('default')" style="flex: 1; min-width: 160px; max-width: 200px;">
                                    <span class="palette-swatch" style="background: linear-gradient(135deg, #1b3550, #14273a);"></span>
                                    <span class="fw-semibold" style="font-size: 13px;">Default (Navy)</span>
                                    <span class="ms-auto small" id="sttPalCheck-default" style="color: var(--accent-blue);">&#10003;</span>
                                </button>
                                <button class="palette-option settings-palette-opt" data-palette="ocean" onclick="setPalette('ocean')" style="flex: 1; min-width: 160px; max-width: 200px;">
                                    <span class="palette-swatch" style="background: linear-gradient(135deg, #0891b2, #164e63);"></span>
                                    <span class="fw-semibold" style="font-size: 13px;">Ocean</span>
                                    <span class="ms-auto small" id="sttPalCheck-ocean" style="color: var(--accent-blue); display: none;">&#10003;</span>
                                </button>
                                <button class="palette-option settings-palette-opt" data-palette="emerald" onclick="setPalette('emerald')" style="flex: 1; min-width: 160px; max-width: 200px;">
                                    <span class="palette-swatch" style="background: linear-gradient(135deg, #059669, #064e3b);"></span>
                                    <span class="fw-semibold" style="font-size: 13px;">Emerald</span>
                                    <span class="ms-auto small" id="sttPalCheck-emerald" style="color: var(--accent-blue); display: none;">&#10003;</span>
                                </button>
                                <button class="palette-option settings-palette-opt" data-palette="sunset" onclick="setPalette('sunset')" style="flex: 1; min-width: 160px; max-width: 200px;">
                                    <span class="palette-swatch" style="background: linear-gradient(135deg, #ea580c, #431407);"></span>
                                    <span class="fw-semibold" style="font-size: 13px;">Sunset</span>
                                    <span class="ms-auto small" id="sttPalCheck-sunset" style="color: var(--accent-blue); display: none;">&#10003;</span>
                                </button>
                                <button class="palette-option settings-palette-opt" data-palette="lavender" onclick="setPalette('lavender')" style="flex: 1; min-width: 160px; max-width: 200px;">
                                    <span class="palette-swatch" style="background: linear-gradient(135deg, #7c3aed, #3b0764);"></span>
                                    <span class="fw-semibold" style="font-size: 13px;">Lavender</span>
                                    <span class="ms-auto small" id="sttPalCheck-lavender" style="color: var(--accent-blue); display: none;">&#10003;</span>
                                </button>
                                <button class="palette-option settings-palette-opt" data-palette="rose" onclick="setPalette('rose')" style="flex: 1; min-width: 160px; max-width: 200px;">
                                    <span class="palette-swatch" style="background: linear-gradient(135deg, #e11d48, #4c0519);"></span>
                                    <span class="fw-semibold" style="font-size: 13px;">Rose</span>
                                    <span class="ms-auto small" id="sttPalCheck-rose" style="color: var(--accent-blue); display: none;">&#10003;</span>
                                </button>
                            </div>
                        </div>

                        {{-- Table Density --}}
                        <form action="{{ route('admin.settings.appearance') }}" method="POST">
                            @csrf
                            <div class="mb-4 p-4 rounded" style="border: 1px solid var(--border-color); background: var(--bg-main);">
                                <h6 class="fw-bold mb-1 fs-5 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                                    <i class="bi bi-layout-three-columns"></i>
                                    Table Density
                                </h6>
                                <p class="text-secondary small mb-3">Control how much data fits on screen in inventory and issuance tables.</p>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="density" id="densityCozy" value="densityCozy" {{ ($settings['density'] ?? 'densityCozy') === 'densityCozy' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="densityCozy" style="color: var(--text-primary);">Cozy <span class="text-secondary fw-normal">(Spacious rows)</span></label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="density" id="densityCompact" value="densityCompact" {{ ($settings['density'] ?? 'densityCozy') === 'densityCompact' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="densityCompact" style="color: var(--text-primary);">Compact <span class="text-secondary fw-normal">(More data on screen)</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">
                                    <i class="bi bi-check-lg me-1"></i> Save Appearance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- Category CRUD Modal --}}
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid var(--border-color);">
                <form id="categoryForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="categoryMethod" value="POST">
                    <input type="hidden" name="category_id" id="categoryId">
                    <div class="modal-header border-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" id="categoryModalTitle" style="color: var(--text-primary);">
                            <i class="bi bi-tags me-2 text-primary"></i> <span id="categoryModalLabel">Add Asset Category</span>
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter: var(--thumb-invert, none);"></button>
                    </div>
                    <div class="modal-body px-4 pb-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="categoryName" class="form-control theme-dynamic-input" placeholder="e.g. IT Equipment" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">PPE Sub-Major</label>
                            <input type="text" name="ppe_sub_major" id="categoryPpe" class="form-control theme-dynamic-input" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">GL Ledger Account</label>
                            <input type="text" name="gl_ledger_acct" id="categoryGl" class="form-control theme-dynamic-input" placeholder="Optional">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 8px;">
                            <i class="bi bi-check-lg"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Supplier CRUD Modal --}}
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid var(--border-color);">
                <form id="supplierForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="supplierMethod" value="POST">
                    <input type="hidden" name="supplier_id" id="supplierId">
                    <div class="modal-header border-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: var(--text-primary);">
                            <i class="bi bi-truck me-2 text-primary"></i> <span id="supplierModalLabel">Add Supplier</span>
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter: var(--thumb-invert, none);"></button>
                    </div>
                    <div class="modal-body px-4 pb-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="supplierName" class="form-control theme-dynamic-input" placeholder="e.g. ACME Corp" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Tag Header Color</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" name="color" id="supplierColor" class="form-control form-control-color p-1" style="width: 48px; height: 42px; border-radius: 8px; cursor: pointer;" value="#FFFF00">
                                <span id="supplierColorHex" class="fw-bold" style="font-size: 13px; color: var(--text-primary);">#FFFF00</span>
                                <span class="text-secondary small">This color appears on the property tag header.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 8px;">
                            <i class="bi bi-check-lg"></i> Save Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Department (Location) CRUD Modal --}}
    <div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid var(--border-color);">
                <form id="locationForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="locationMethod" value="POST">
                    <input type="hidden" name="location_id" id="locationId">
                    <div class="modal-header border-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: var(--text-primary);">
                            <i class="bi bi-building me-2 text-primary"></i> <span id="locationModalLabel">Add Department</span>
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="filter: var(--thumb-invert, none);"></button>
                    </div>
                    <div class="modal-body px-4 pb-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="locationName" class="form-control theme-dynamic-input" placeholder="e.g. Science Department" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Department Code</label>
                            <input type="text" name="code" id="locationCode" class="form-control theme-dynamic-input" placeholder="e.g. SCI-DEPT">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 8px;">
                            <i class="bi bi-check-lg"></i> Save Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" data-centered tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid #dc3545;">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger mb-3 d-block" style="font-size: 2.5rem;"></i>
                    <h6 class="fw-bold mb-2" style="color: var(--text-primary);" id="deleteConfirmTitle">Delete this item?</h6>
                    <p class="text-secondary small mb-0" id="deleteConfirmDesc">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2 justify-content-center">
                    <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel</button>
                    <button type="button" id="confirmDeleteGoBtn" class="btn btn-danger fw-bold px-3 py-2" style="border-radius: 8px;">
                        <i class="bi bi-trash3"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Backup Warning Modal --}}
    <div class="modal fade" id="restoreWarningModal" tabindex="-1" aria-hidden="true" data-centered>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 12px; border: 1px solid #ffc107; box-shadow: 0 10px 30px rgba(255,193,7,0.15);">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> DESTRUCTIVE RESTORATION LOCK
                    </h5>
                    <button type="button" class="btn-close" style="filter: var(--thumb-invert, none);" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-3">
                    <p class="text-secondary mb-3" style="font-size: 15px; line-height: 1.5; color: var(--text-secondary) !important;">
                        You are about to wipe the active inventory tables and override all system entities with data from the backup file. <strong class="text-danger">Any transactions recorded after this backup was taken will be lost forever.</strong> Are you absolutely sure?
                    </p>
                    <div class="text-start mt-4 pt-3 border-top" style="border-color: rgba(128,128,128,0.15) !important;">
                        <label class="form-label fw-bold text-secondary" style="font-size: 12px; letter-spacing: 0.5px;">CONFIRM ADMIN PASSWORD</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 shadow-none theme-dynamic-addon" style="background: transparent;">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" id="restorePasswordInput" class="form-control shadow-none theme-dynamic-input"
                                style="border-left: none;" placeholder="Enter your account password" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn border-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px; color: var(--text-primary); background: transparent;">Cancel Operation</button>
                    <button type="button" id="confirmSystemRestoreBtn" class="btn btn-warning fw-bold px-3 py-2 text-dark" style="border-radius: 8px;">
                        Yes, Overwrite Live Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-settings-nav .nav-link {
    color: var(--text-secondary);
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.custom-settings-nav .nav-link:hover {
    background-color: rgba(128, 128, 128, 0.1);
    color: var(--text-primary);
}
.custom-settings-nav .nav-link.active {
    background-color: var(--text-primary) !important;
    color: var(--bg-surface) !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.theme-dynamic-input {
    background: transparent !important;
    border: 1px solid var(--text-secondary) !important;
    color: var(--text-primary) !important;
}
.theme-dynamic-input:focus {
    box-shadow: none !important;
    border-color: var(--text-primary) !important;
}
.theme-dynamic-input option {
    background-color: var(--bg-surface) !important;
    color: var(--text-primary) !important;
}
.theme-dynamic-addon {
    background: transparent !important;
    border: 1px solid var(--text-secondary) !important;
    color: var(--text-secondary) !important;
}
</style>

<style>
.settings-palette-opt {
    flex: 1;
    min-width: 160px;
    max-width: 200px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1.5px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    background: var(--bg-surface);
    color: var(--text-primary);
    font-family: inherit;
    font-size: inherit;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 10px;
}
.settings-palette-opt:hover {
    border-color: var(--border-color);
}
.settings-palette-opt:focus-visible {
    outline: 2px solid var(--accent-blue);
    outline-offset: 2px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // â”€â”€ Auto-navigate to Appearance tab if #appearance is in URL â”€â”€
    if (window.location.hash === '#appearance') {
        var appearanceTab = document.getElementById('v-pills-appearance-tab');
        if (appearanceTab) {
            bootstrap.Tab.getOrCreateInstance(appearanceTab).show();
        }
    }

    // â”€â”€ Sync settings dark mode toggle with saved theme â”€â”€
    var savedTheme = localStorage.getItem('theme') || 'light';
    var settingsToggle = document.getElementById('settingsDarkToggle');
    if (settingsToggle) {
        settingsToggle.checked = savedTheme === 'dark';
    }
    // Sync settings theme icon
    var settingsIcon = document.getElementById('settingsThemeIcon');
    if (settingsIcon) {
        settingsIcon.className = savedTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
    }

    // â”€â”€ Sync settings palette checkmarks with saved palette â”€â”€
    var savedPalette = localStorage.getItem('palette') || 'default';
    var activeSttCheck = document.getElementById('sttPalCheck-' + savedPalette);
    if (activeSttCheck) activeSttCheck.style.display = 'inline';
    // Backup restore actions
    const triggerRestoreBtn = document.getElementById('triggerRestoreBtn');
    const confirmSystemRestoreBtn = document.getElementById('confirmSystemRestoreBtn');
    const systemRestoreForm = document.getElementById('systemRestoreForm');
    const restorePasswordInput = document.getElementById('restorePasswordInput');
    const hiddenRestorePassword = document.getElementById('hiddenRestorePassword');

    if (triggerRestoreBtn) {
        triggerRestoreBtn.addEventListener('click', function(e) {
            const fileInput = systemRestoreForm.querySelector('input[type="file"]');
            if (!fileInput.value) {
                fileInput.reportValidity();
                return;
            }
            if(restorePasswordInput) restorePasswordInput.value = '';
            new bootstrap.Modal(document.getElementById('restoreWarningModal')).show();
        });
    }

    if (confirmSystemRestoreBtn) {
        confirmSystemRestoreBtn.addEventListener('click', function() {
            if (!restorePasswordInput.value) {
                restorePasswordInput.reportValidity();
                return;
            }
            hiddenRestorePassword.value = restorePasswordInput.value;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Authenticating & Restoring...';
            this.disabled = true;
            systemRestoreForm.submit();
        });
    }

    // â”€â”€ Supplier color picker sync â”€â”€
    var supplierColorInput = document.getElementById('supplierColor');
    var supplierColorHex = document.getElementById('supplierColorHex');
    if (supplierColorInput && supplierColorHex) {
        supplierColorInput.addEventListener('input', function() {
            supplierColorHex.textContent = this.value;
        });
    }
});

// â”€â”€ CATEGORY MODAL â”€â”€
window.openCategoryModal = function() {
    document.getElementById('categoryForm').action = '/admin/settings/categories';
    document.getElementById('categoryMethod').value = 'POST';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryPpe').value = '';
    document.getElementById('categoryGl').value = '';
    document.getElementById('categoryModalLabel').textContent = 'Add Asset Category';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
};

window.editCategory = function(id, name, ppe, gl) {
    document.getElementById('categoryForm').action = '/admin/settings/categories/' + id;
    document.getElementById('categoryMethod').value = 'PUT';
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryPpe').value = ppe;
    document.getElementById('categoryGl').value = gl;
    document.getElementById('categoryModalLabel').textContent = 'Edit Asset Category';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
};

// â”€â”€ SUPPLIER MODAL â”€â”€
window.openSupplierModal = function() {
    document.getElementById('supplierForm').action = '/admin/settings/suppliers';
    document.getElementById('supplierMethod').value = 'POST';
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierName').value = '';
    var colorInput = document.getElementById('supplierColor');
    var colorHex = document.getElementById('supplierColorHex');
    if (colorInput) { colorInput.value = '#FFFF00'; }
    if (colorHex) { colorHex.textContent = '#FFFF00'; }
    document.getElementById('supplierModalLabel').textContent = 'Add Supplier';
    new bootstrap.Modal(document.getElementById('supplierModal')).show();
};

window.editSupplier = function(id, name, color) {
    document.getElementById('supplierForm').action = '/admin/settings/suppliers/' + id;
    document.getElementById('supplierMethod').value = 'PUT';
    document.getElementById('supplierId').value = id;
    document.getElementById('supplierName').value = name;
    var colorInput = document.getElementById('supplierColor');
    var colorHex = document.getElementById('supplierColorHex');
    if (colorInput) { colorInput.value = color || '#FFFF00'; }
    if (colorHex) { colorHex.textContent = color || '#FFFF00'; }
    document.getElementById('supplierModalLabel').textContent = 'Edit Supplier';
    new bootstrap.Modal(document.getElementById('supplierModal')).show();
};

// â”€â”€ DEPARTMENT (LOCATION) MODAL â”€â”€
window.openLocationModal = function() {
    document.getElementById('locationForm').action = '/admin/settings/locations';
    document.getElementById('locationMethod').value = 'POST';
    document.getElementById('locationId').value = '';
    document.getElementById('locationName').value = '';
    document.getElementById('locationCode').value = '';
    document.getElementById('locationModalLabel').textContent = 'Add Department';
    new bootstrap.Modal(document.getElementById('locationModal')).show();
};

window.editLocation = function(id, name, code) {
    document.getElementById('locationForm').action = '/admin/settings/locations/' + id;
    document.getElementById('locationMethod').value = 'PUT';
    document.getElementById('locationId').value = id;
    document.getElementById('locationName').value = name;
    document.getElementById('locationCode').value = code;
    document.getElementById('locationModalLabel').textContent = 'Edit Department';
    new bootstrap.Modal(document.getElementById('locationModal')).show();
};

// â”€â”€ DELETE CONFIRMATION â”€â”€
window.confirmDelete = function(type, id, name) {
    window._pendingDelete = { type: type, id: id };
    document.getElementById('deleteConfirmTitle').textContent = 'Delete "' + name + '"?';
    document.getElementById('deleteConfirmDesc').textContent = 'This action cannot be undone. Assets linked to this ' + type + ' will not be affected.';
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
};

document.addEventListener('DOMContentLoaded', function () {
    const goBtn = document.getElementById('confirmDeleteGoBtn');
    if (!goBtn) return;
    goBtn.addEventListener('click', async function () {
        const pending = window._pendingDelete;
        if (!pending) return;
        /* irregular plural: category → categories */
        const SEGMENTS = { category: 'categories' };
        const segment = SEGMENTS[pending.type] || (pending.type + 's');
        goBtn.disabled = true;
        goBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting…';
        try {
            const res = await fetch('/admin/settings/' + segment + '/' + pending.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ _method: 'DELETE' }),
            });
            if (res.ok) { window.location.reload(); return; }
            const d = await res.json().catch(() => ({}));
            alert(d.message || ('Delete failed (' + res.status + ').'));
        } catch (err) {
            alert('Network error — please try again.');
        } finally {
            goBtn.disabled = false;
            goBtn.innerHTML = '<i class="bi bi-trash3"></i> Delete';
            const modalEl = document.getElementById('deleteConfirmModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
    });
});

window.renameTag = async function (id, categoryId, currentName) {
    const name = window.prompt("Rename tag:", currentName);
    if (!name || !name.trim() || name.trim() === currentName) return;
    try {
        const res = await fetch('/admin/settings/tags/' + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ category_id: categoryId, name: name.trim(), _method: 'PUT' })
        });
        if (res.ok) { window.location.reload(); }
        else { const d = await res.json().catch(() => ({})); alert(d.message || "Rename failed."); }
    } catch (e) { alert("Network error."); }
};
</script>
@endsection
