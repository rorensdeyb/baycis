@extends('layouts.admin')

@section('content')
<!-- Barcode Scanner Modal for Mobile -->
<div class="modal fade" id="barcodeScannerModal" tabindex="-1" aria-hidden="true" data-centered>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <div class="icon-circle icon-circle-green">
                        <i class="bi bi-camera text-success"></i>
                    </div>
                    Scan Barcode
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" onclick="stopBarcodeScanner()"></button>
            </div>
            <div class="modal-body px-4 py-4 text-center">
                <p class="text-secondary small mb-3">Position the barcode inside the frame to automatically search the inventory.</p>
                <div style="padding: 16px; border: 2px dashed var(--accent-color); border-radius: 16px; background: var(--bg-main); min-height: 250px;">
                    <div id="barcode-reader"></div>
                    <div id="barcode-manual-input" style="display: none;">
                        <p class="text-secondary small mb-2">Or enter barcode manually:</p>
                        <div class="input-group">
                            <input type="text" id="manualBarcodeInput" class="form-control text-center" placeholder="Enter barcode..." style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color);">
                            <button class="btn btn-primary" onclick="searchBarcode()">Search</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 pt-0 gap-2">
                <button type="button" class="btn w-100 fw-bold py-2" data-bs-dismiss="modal" onclick="stopBarcodeScanner()" style="border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">Close Camera</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Custom Tooltips for Unavailable Items */
    .unavailable-note-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        padding: 6px;
        border-radius: 8px;
        color: var(--text-secondary);
        transition: all 0.2s ease;
    }
    .unavailable-note-btn:hover {
        background: var(--bg-surface-hover);
        color: var(--accent-red);
    }
    .unavailable-tooltip {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        bottom: 130%;
        right: 0;
        background-color: var(--text-primary);
        color: var(--bg-surface);
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1100;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
        font-weight: 600;
        border: 1px solid var(--border-color);
        transform: translateY(4px);
        text-align: left;
    }
    .unavailable-tooltip::after {
        content: "";
        position: absolute;
        top: 100%;
        right: 12px;
        border-width: 6px;
        border-style: solid;
        border-color: var(--text-primary) transparent transparent transparent;
    }
.unavailable-note-btn:hover .unavailable-tooltip {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }
    /* On Good Condition status colors */
    .stat-blue .inv-stat-icon{background:var(--accent-blue-bg);color:var(--accent-blue);}
    .stat-green .inv-stat-icon{background:var(--accent-green-bg);color:var(--accent-green);}
    .stat-yellow .inv-stat-icon{background:var(--accent-yellow-bg);color:var(--accent-yellow);}
    .stat-red .inv-stat-icon{background:var(--accent-red-bg);color:var(--accent-red);}
    .pill-blue{color:var(--accent-blue);border-color:var(--accent-blue);}
    .pill-blue:hover{background:var(--accent-blue-bg);}
    .pill-active-blue{background:var(--accent-blue);color:#fff;border-color:var(--accent-blue);}
    /* Status popover */
    .status-popover{position:fixed;z-index:1050;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:12px;padding:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);display:flex;flex-direction:column;gap:4px;}
    .status-opt{display:flex;align-items:center;gap:8px;width:100%;padding:8px 12px;border:none;border-radius:8px;background:transparent;cursor:pointer;font-size:13px;font-weight:600;transition:all .15s ease;text-align:left;}
    .status-opt:hover{filter:brightness(.92);}
    .status-green{color:var(--accent-green);}.status-green:hover{background:var(--accent-green-bg);}
    .status-blue{color:var(--accent-blue);}.status-blue:hover{background:var(--accent-blue-bg);}
    .status-yellow{color:var(--accent-yellow);}.status-yellow:hover{background:var(--accent-yellow-bg);}
    .status-red{color:var(--accent-red);}.status-red:hover{background:var(--accent-red-bg);}
    .status-orange{color:#f97316;}.status-orange:hover{background:#fff7ed;}
    .status-gray{color:var(--text-secondary);}.status-gray:hover{background:var(--bg-surface-hover);}
    </style>

<div class="dashboard-wrapper" id="inventoryPage">
    <h2 class="fw-bold mb-4" style="color: var(--text-primary);">Inventory</h2>

    {{-- I3.5: MINI-WIDGET STATS BAR --}}
    <div class="inv-stats-bar mb-3" id="invStatsBar">
        <div class="inv-stat-item"><span class="inv-stat-icon"><i class="bi bi-box"></i></span><span class="inv-stat-label">Total</span><span class="inv-stat-value" id="statTotal">--</span></div>
        <div class="inv-stat-item stat-green"><span class="inv-stat-icon"><i class="bi bi-check-circle"></i></span><span class="inv-stat-label">Available</span><span class="inv-stat-value" id="statAvail">--</span></div>
        <div class="inv-stat-item stat-blue"><span class="inv-stat-icon"><i class="bi bi-box-seam"></i></span><span class="inv-stat-label">Good Condition</span><span class="inv-stat-value" id="statGood">--</span></div>
        <div class="inv-stat-item stat-yellow"><span class="inv-stat-icon"><i class="bi bi-clock"></i></span><span class="inv-stat-label">Borrowed</span><span class="inv-stat-value" id="statBorrowed">--</span></div>
        <div class="inv-stat-item stat-red"><span class="inv-stat-icon"><i class="bi bi-exclamation-triangle"></i></span><span class="inv-stat-label">Unavailable</span><span class="inv-stat-value" id="statDamaged">--</span></div>
        <div class="inv-stat-item"><span class="inv-stat-icon"><i class="bi bi-download"></i></span><span class="inv-stat-label">Export</span>
            <a href="{{ route('items.export', request()->all()) }}" class="inv-stat-value" style="font-size:12px;color:var(--accent-blue);text-decoration:underline;cursor:pointer;" title="Export current view as CSV">CSV</a>
        </div>
    </div>

    {{-- CONTROL BAR --}}
    <div class="panel-card d-flex justify-content-between align-items-center mb-4 p-3" style="border-radius: 12px;">
        <form id="inventoryFilterForm" action="/admin/inventory" method="GET" class="d-flex align-items-center gap-3 m-0 flex-wrap">
            <div class="position-relative" style="width: 220px;">
                <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); z-index:2;"></i>
                <input type="search" name="search" id="invSearchInput" class="form-control shadow-none theme-dynamic-input w-100" 
                    style="padding-left: 38px;" 
                    placeholder="Search assets, tags..." 
                    value="{{ request('search') }}">
                @if(request('search'))
                <a href="/admin/inventory" class="position-absolute" style="right:10px;top:50%;transform:translateY(-50%);color:var(--text-secondary);text-decoration:none;" title="Clear search">&times;</a>
                @endif
            </div>
            <button type="button" class="btn btn-outline-primary d-md-none d-flex align-items-center gap-2" onclick="openBarcodeScanner()" title="Scan Barcode" style="border-radius: 8px;">
                <i class="bi bi-camera"></i>
            </button>
            
            {{-- I1.2: QUICK FILTER PILLS (with dropdown positioning) --}}
            <div class="position-relative d-inline-block">
                <div class="filter-pills d-flex align-items-center gap-1 flex-wrap">
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'category' => 'all']) }}" class="pill {{ request('status', 'all') === 'all' && request('category', 'all') === 'all' ? 'pill-active' : '' }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'available']) }}" class="pill pill-green {{ request('status') === 'available' ? 'pill-active-green' : '' }}">Available</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'ongoodcondition']) }}" class="pill pill-blue {{ request('status') === 'ongoodcondition' ? 'pill-active-blue' : '' }}">On Good Condition</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'borrowed']) }}" class="pill pill-yellow {{ request('status') === 'borrowed' ? 'pill-active-yellow' : '' }}">Borrowed</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'damaged']) }}" class="pill pill-red {{ request('status') === 'damaged' ? 'pill-active-red' : '' }}">Unavailable</a>
                    {{-- I1.2: Saved filters button --}}
                    <button type="button" class="pill" onclick="showSavedFilters()" title="Saved filters" style="border-style:dashed;"><i class="bi bi-bookmark"></i></button>
                </div>

                {{-- I1.2: Saved filters dropdown --}}
                <div id="savedFiltersDropdown" class="saved-filters-dropdown" style="display:none;">
                    <div class="saved-filters-header">
                        <span class="fw-bold" style="font-size:12px;">Saved Filters</span>
                        <button onclick="document.getElementById('savedFiltersDropdown').style.display='none'" style="background:none;border:none;color:var(--text-secondary);">&times;</button>
                    </div>
                    <div id="savedFiltersList"></div>
                    <div class="saved-filters-footer">
                        <button type="button" onclick="saveCurrentFilter()" class="btn btn-sm fw-bold w-100" style="background:var(--bg-surface-hover);color:var(--text-primary);border-radius:6px;font-size:11px;">+ Save Current Filter</button>
                    </div>
                </div>
            </div>

            {{-- Theme-aware "Name this filter" overlay modal --}}
            <div id="filterNameOverlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);align-items:center;justify-content:center;">
                <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:16px;padding:28px 32px 24px;width:340px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;animation:modalPop 0.25s ease;">
                    <div style="width:44px;height:44px;background:var(--accent-yellow-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                        <i class="bi bi-bookmark-fill" style="font-size:20px;color:var(--accent-yellow);"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color:var(--text-primary);">Name This Filter</h5>
                    <p class="small mb-3" style="color:var(--text-secondary);">Give your current filter set a memorable name.</p>
                    <input type="text" id="filterNameInput" class="form-control custom-input text-center fw-bold" placeholder="e.g. Borrowed IT Assets" style="background:var(--bg-main);color:var(--text-primary);border:1px solid var(--border-color);border-radius:10px;padding:12px 16px;font-size:14px;outline:none;transition:border-color 0.2s ease;">
                    <div class="d-flex gap-2 mt-3">
                        <button onclick="cancelFilterName()" class="btn flex-fill fw-bold py-2" style="background:var(--bg-surface-hover);color:var(--text-primary);border:1px solid var(--border-color);border-radius:10px;font-size:13px;">Cancel</button>
                        <button onclick="confirmFilterName()" class="btn flex-fill fw-bold py-2" style="background:var(--text-primary);color:var(--bg-surface);border-radius:10px;font-size:13px;">Save Filter</button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes modalPop {
                    0% { transform: scale(0.9); opacity: 0; }
                    100% { transform: scale(1); opacity: 1; }
                }
            </style>

            {{-- Category dropdown chip --}}
            <select name="category" class="form-select shadow-none chip-select" style="width:auto;min-width:140px;background:var(--bg-surface);color:var(--text-primary);border:1px solid var(--border-color);border-radius:8px;font-size:13px;padding:6px 10px;" onchange="this.form.submit()">
                <option value="all" {{ request('category', 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                @if(isset($activeCategories))
                    @foreach($activeCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                @endif
            </select>

            <div class="d-flex align-items-center gap-1 position-relative">
                @if(request('search') || request('status') !== 'all' || request('category') !== 'all')
                <a href="/admin/inventory" class="btn btn-sm shadow-none" style="color:var(--text-secondary);border:1px solid var(--border-color);border-radius:8px;font-size:12px;"><i class="bi bi-x-lg me-1"></i>Clear</a>
                @endif
                {{-- I1.1: Column visibility toggle --}}
                <button type="button" class="btn btn-sm shadow-none" id="columnToggleBtn" onclick="toggleColumnMenu()" title="Toggle columns" style="color:var(--text-secondary);border:1px solid var(--border-color);border-radius:8px;font-size:14px;padding:6px 10px;"><i class="bi bi-layout-three-columns"></i></button>
                <div id="columnToggleMenu" class="column-toggle-menu" style="display:none;">
                    <div class="fw-bold px-2 py-1" style="font-size:11px;color:var(--text-secondary);">Show/Hide Columns</div>
                    <label class="column-opt"><input type="checkbox" data-col="category" checked onchange="toggleCol('category')"> <span>Category</span></label>
                    <label class="column-opt"><input type="checkbox" data-col="location" checked onchange="toggleCol('location')"> <span>Location</span></label>
                    <button onclick="resetColumns()" class="btn btn-sm w-100 mt-1" style="font-size:10px;color:var(--accent-blue);background:none;border:none;">Reset</button>
                </div>
            </div>
        </form>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button type="button" class="btn fw-bold d-flex align-items-center gap-2 px-3 py-2" data-bs-toggle="modal" data-bs-target="#scanModal" style="background-color: var(--bg-surface-hover); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px;">
                <i class="bi bi-upc-scan fs-5"></i> Scan
            </button>
            <a href="/admin/inventory/create" class="btn fw-bold d-flex align-items-center gap-2 px-4 py-2" style="background-color: var(--text-primary); color: var(--bg-surface); border-radius: 8px;">
                <i class="bi bi-plus-lg"></i> Add New
            </a>
            {{-- Keyboard Shortcuts Help Indicator --}}
            <div class="position-relative">
                <button type="button" class="icon-btn" id="shortcutsHelpBtn" onclick="toggleShortcutsHelp()" title="Keyboard shortcuts" style="font-size:16px;width:36px;height:36px;border-radius:50%;border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;background:var(--bg-surface);color:var(--text-secondary);">
                    <i class="bi bi-question-lg"></i>
                </button>
                <div id="shortcutsHelpDropdown" class="shortcuts-help-dropdown" style="display:none;">
                    <div class="shortcuts-help-header">Keyboard Shortcuts</div>
                    <div class="shortcut-row"><kbd>/</kbd><span>Focus search</span></div>
                    <div class="shortcut-row"><kbd>N</kbd><span>New asset</span></div>
                    <div class="shortcut-row"><kbd>B</kbd><span>Toggle bulk select</span></div>
                    <div class="shortcut-row"><kbd>Esc</kbd><span>Clear selection</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- I1.1: FLOATING BULK ACTION BAR (hidden by default) --}}
    <div class="bulk-bar" id="bulkBar" style="display:none;">
        <span class="bulk-count" id="bulkCount">0 selected</span>
        <div class="bulk-actions">
            <button class="bulk-btn" onclick="bulkPrintStudio()" title="Open selected tags in Print Studio (multi-tag sheets)"><i class="bi bi-grid-3x3-gap"></i> Print Studio</button>
            <div class="bulk-dropdown">
                <button class="bulk-btn" id="bulkStatusBtn"><i class="bi bi-arrow-repeat"></i> Change Status</button>
                <div class="bulk-dropdown-menu" id="bulkStatusMenu">
                    <a onclick="bulkSetStatus('available')">Available</a>
                    <a onclick="bulkSetStatus('ongoodcondition')">On Good Condition</a>
                    <a onclick="bulkSetStatus('borrowed')">Borrowed</a>
                    <a onclick="bulkSetStatus('damaged')">Damaged</a>
                    <a onclick="bulkSetStatus('maintenance')">Maintenance</a>
                    <a onclick="bulkSetStatus('disposed')">Disposed</a>
                </div>
            </div>
            <button class="bulk-btn bulk-btn-danger" onclick="bulkClear()"><i class="bi bi-x-lg"></i> Clear</button>
        </div>
    </div>

    {{-- I1.3: MOBILE CARD VIEW (visible <768px, hidden on desktop) --}}
    <div class="d-md-none mb-3" id="mobileCards">
        @forelse($items as $item)
        <div class="inv-mobile-card" data-id="{{ $item->id }}">
            <div class="inv-mobile-card-header">
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" class="bulk-checkbox-mobile" value="{{ $item->id }}">
                    @php
                        $statusColors = [
                            'available'      => ['bg' => 'var(--accent-green-bg)', 'text' => 'var(--accent-green)', 'label' => 'AVAILABLE'],
                            'ongoodcondition' => ['bg' => 'var(--accent-blue-bg)', 'text' => 'var(--accent-blue)', 'label' => 'GOOD CONDITION'],
                            'borrowed'       => ['bg' => 'var(--accent-yellow-bg)', 'text' => 'var(--accent-yellow)', 'label' => 'BORROWED'],
                            'damaged'        => ['bg' => 'var(--accent-red-bg)', 'text' => 'var(--accent-red)', 'label' => 'UNAVAILABLE'],
                            'maintenance'    => ['bg' => 'var(--accent-red-bg)', 'text' => 'var(--accent-red)', 'label' => 'UNAVAILABLE'],
                            'disposed'       => ['bg' => 'var(--bg-surface-hover)', 'text' => 'var(--text-secondary)', 'label' => 'DISPOSED'],
                        ];
                        $sc = $statusColors[strtolower($item->status)] ?? $statusColors['available'];
                    @endphp
                    <span class="badge rounded-pill fw-bold" style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['text'] }};font-size:10px;">{{ $sc['label'] }}</span>
                </div>
                <div class="inv-mobile-tag">
                    <span class="small font-monospace fw-bold">{{ $item->property_tag }}</span>
                    <button class="btn btn-sm shadow-none p-0 ms-1" onclick="copyTag('{{ $item->property_tag }}', this)" title="Copy tag"><i class="bi bi-clipboard" style="font-size:11px;color:var(--text-secondary);"></i></button>
                </div>
            </div>
            <div class="inv-mobile-card-body">
                <div class="inv-mobile-name">{{ $item->name }}</div>
                <div class="inv-mobile-meta">{{ $item->category->name ?? 'N/A' }} &middot; {{ $item->location->name ?? 'N/A' }}</div>
            </div>
            <div class="inv-mobile-card-actions">
                <a href="/admin/inventory/{{ $item->id }}/edit" class="btn btn-sm btn-light fw-bold" style="border:1px solid var(--border-color);background:var(--bg-surface);color:var(--text-primary);border-radius:8px;"><i class="bi bi-pencil me-1"></i>Edit</a>
                <a href="/admin/print-studio?ids={{ $item->id }}" target="_blank" class="btn btn-sm btn-light fw-bold" style="border:1px solid var(--border-color);background:var(--bg-surface);color:var(--text-primary);border-radius:8px;"><i class="bi bi-grid-3x3-gap me-1"></i>Print Studio</a>
                <form action="/admin/inventory/{{ $item->id }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-outline-danger delete-asset-btn" style="border-radius:8px;"><i class="bi bi-trash me-1"></i>Delete</button>
                </form>
            </div>
        </div>
        @empty
        @php
            $hasSearch = request('search');
            $hasCategory = request('category', 'all') !== 'all';
            $hasStatus = request('status', 'all') !== 'all';
            $isFiltered = $hasSearch || $hasCategory || $hasStatus;
        @endphp
        @if($isFiltered)
        <div class="text-center py-5">
            <div style="width:64px;height:64px;background:var(--accent-yellow-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="bi bi-search fs-3" style="color:var(--accent-yellow);"></i>
            </div>
            <h5 class="fw-bold" style="color:var(--text-primary);">No Matches Found</h5>
            <p class="small mb-3" style="color:var(--text-secondary);">Try adjusting your filters or search term.</p>
            <a href="/admin/inventory" class="btn btn-sm fw-bold" style="background:var(--text-primary);color:var(--bg-surface);border-radius:8px;padding:8px 20px;">Clear All Filters</a>
        </div>
        @else
        <div class="text-center py-5">
            <div style="width:72px;height:72px;background:var(--bg-surface-hover);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="bi bi-box-seam fs-2 text-secondary"></i>
            </div>
            <h5 class="fw-bold" style="color:var(--text-primary);">No Assets Yet</h5>
            <p class="small mb-3" style="color:var(--text-secondary);">Get started by adding your first asset.</p>
            <a href="/admin/inventory/create" class="btn fw-bold" style="background:var(--text-primary);color:var(--bg-surface);border-radius:8px;padding:8px 24px;">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Asset
            </a>
        </div>
        @endif
        @endforelse
    </div>

    {{-- DESKTOP TABLE --}}
    <div id="inventory-table" class="panel-card p-0 overflow-hidden shadow-sm d-none d-md-block">
        <table class="admin-table mb-0 inv-table" data-drawer-rows data-drawer-renderer="renderInventoryItemDrawer">
            <thead>
                <tr>
                    <th class="ps-4" style="width:36px;">
                        <input type="checkbox" id="bulkSelectAll" class="form-check-input" style="cursor:pointer;">
                    </th>
                    <th class="ps-2">PROPERTY TAG</th>
                    <th>ITEM NAME</th>
                    <th data-colgroup="category">CATEGORY</th>
                    <th data-colgroup="location">LOCATION</th>
                    <th>STATUS</th>
                    <th class="text-end pe-4">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr class="inv-row" data-id="{{ $item->id }}">
                    <td class="ps-4" style="width:36px;">
                        <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $item->id }}" style="cursor:pointer;">
                    </td>
                    <td class="ps-2 py-3">
                        <div class="d-flex align-items-center gap-2">
                            <div style="height: 35px; width: 140px; overflow: hidden; flex-shrink:0;">
                                <svg class="item-barcode" data-tag="{{ $item->property_tag }}" preserveAspectRatio="none" style="width:100%;height:100%;display:block;"></svg>
                            </div>
                            <div>
                                <span class="small fw-bold d-flex align-items-center gap-1">
                                    {{ $item->property_tag }}
                                    <button class="btn btn-sm shadow-none p-0" onclick="copyTag('{{ $item->property_tag }}', this)" title="Copy tag to clipboard"><i class="bi bi-clipboard" style="font-size:11px;color:var(--text-secondary);"></i></button>
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="fw-bold">{{ $item->name }}</td>
                    <td class="text-secondary" data-colgroup="category">{{ $item->category->name ?? 'N/A' }}</td>
                    <td class="text-secondary" data-colgroup="location">{{ $item->location->name ?? 'N/A' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $statusColors = [
                                    'available'      => ['bg' => 'var(--accent-green-bg)', 'text' => 'var(--accent-green)'],
                                    'ongoodcondition' => ['bg' => 'var(--accent-blue-bg)', 'text' => 'var(--accent-blue)'],
                                    'borrowed'       => ['bg' => 'var(--accent-yellow-bg)', 'text' => 'var(--accent-yellow)'],
                                    'damaged'        => ['bg' => 'var(--accent-red-bg)', 'text' => 'var(--accent-red)'],
                                    'maintenance'    => ['bg' => 'var(--accent-red-bg)', 'text' => 'var(--accent-red)'],
                                    'disposed'       => ['bg' => 'var(--bg-surface-hover)', 'text' => 'var(--text-secondary)'],
                                    'archived'       => ['bg' => 'var(--bg-surface-hover)', 'text' => 'var(--text-secondary)'],
                                ];
                                $color = $statusColors[strtolower($item->status)] ?? $statusColors['available'];
                                $statusText = strtoupper($item->status);
                                if (in_array(strtolower($item->status), ['damaged', 'maintenance'])) {
                                    $statusText = 'UNAVAILABLE';
                                }
                                if (strtolower($item->status) === 'ongoodcondition') {
                                    $statusText = 'GOOD CONDITION';
                                }
                            @endphp
                            <span class="badge rounded-pill py-2 px-3 fw-bold inv-status-badge" 
                                data-id="{{ $item->id }}"
                                data-status="{{ $item->status }}"
                                style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['text'] }}; cursor:pointer; transition: all 0.3s ease;">
                                {{ $statusText }}
                            </span>
                            @if(in_array(strtolower($item->status), ['damaged', 'maintenance', 'ongoodcondition']))
                                @php
                                    $reason = 'Unavailable';
                                    if ($item->status === 'borrowed') $reason = 'Currently Borrowed / Loaned';
                                    elseif ($item->status === 'damaged') $reason = 'Damaged / Broken Parts';
                                    elseif ($item->status === 'maintenance') $reason = 'Needs Maintenance or Repair';
                                    elseif ($item->status === 'ongoodcondition') $reason = 'On Good Condition — Not for Borrowing';
                                @endphp
                                <button type="button" class="unavailable-note-btn p-1" style="position:relative;">
                                    <i class="bi bi-chat-left-text-fill fs-5 text-danger"></i>
                                    <span class="unavailable-tooltip">{{ $reason }}</span>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group gap-2">
                            <a href="/admin/inventory/{{ $item->id }}/edit" class="btn btn-sm border" style="color: var(--text-secondary); border-color: var(--border-color) !important;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="/admin/print-studio?ids={{ $item->id }}" target="_blank" class="btn btn-sm border" style="color: var(--text-secondary); border-color: var(--border-color) !important;" title="Print in Studio">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </a>
                            <form action="/admin/inventory/{{ $item->id }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger delete-asset-btn" style="border-radius: 6px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        @php
                            $hasSearch = request('search');
                            $hasCategory = request('category', 'all') !== 'all';
                            $hasStatus = request('status', 'all') !== 'all';
                            $isFiltered = $hasSearch || $hasCategory || $hasStatus;
                        @endphp
                        {{-- I5.2: CONTEXTUAL EMPTY STATES --}}
                        @if($isFiltered)
                            <div style="width:64px;height:64px;background:var(--accent-yellow-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="bi bi-search fs-3" style="color:var(--accent-yellow);"></i>
                            </div>
                            <h5 class="fw-bold" style="color:var(--text-primary);">No Matches Found</h5>
                            <p class="small mb-3" style="color:var(--text-secondary);max-width:360px;margin:0 auto;">
                                No assets match your current filters.
                                @if($hasSearch) Try adjusting your search term or scanning the barcode instead. @endif
                                @if($hasCategory) Try a different category. @endif
                            </p>
                            <a href="/admin/inventory" class="btn btn-sm fw-bold" style="background:var(--text-primary);color:var(--bg-surface);border-radius:8px;padding:8px 20px;">Clear All Filters</a>
                        @else
                            <div style="width:72px;height:72px;background:var(--bg-surface-hover);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="bi bi-box-seam fs-2 text-secondary"></i>
                            </div>
                            <h5 class="fw-bold" style="color:var(--text-primary);">No Assets Yet</h5>
                            <p class="small mb-3" style="color:var(--text-secondary);">Get started by adding your first asset to the inventory.</p>
                            <a href="/admin/inventory/create" class="btn fw-bold" style="background:var(--text-primary);color:var(--bg-surface);border-radius:8px;padding:8px 24px;">
                                <i class="bi bi-plus-lg me-1"></i> Add Your First Asset
                            </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pagination --}}
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-top: 1px solid var(--border-color);">
            <div class="d-flex align-items-center gap-3">
                <div class="small" style="color: var(--text-secondary);">
                    Showing <strong style="color:var(--text-primary);">{{ $items->firstItem() }}</strong>–<strong style="color:var(--text-primary);">{{ $items->lastItem() }}</strong> of <strong style="color:var(--text-primary);">{{ $items->total() }}</strong> items
                </div>
                {{-- T2: Page Size Selector --}}
                <select class="page-size-select" onchange="var p=new URLSearchParams(window.location.search);p.set('per_page',this.value);window.location.href='/admin/inventory?'+p.toString()">
                    <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5/page</option>
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10/page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25/page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50/page</option>
                </select>
            </div>
            <div class="d-flex align-items-center gap-1">
                {{-- Previous --}}
                @if($items->onFirstPage())
                    <span class="btn btn-sm shadow-none disabled" style="border:1px solid var(--border-color); color:var(--text-secondary); background:transparent; border-radius:8px; padding: 6px 12px;"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $items->previousPageUrl() }}#inventory-table" class="btn btn-sm shadow-none" style="border:1px solid var(--border-color); color:var(--text-primary); background:transparent; border-radius:8px; padding: 6px 12px; transition:0.2s;"><i class="bi bi-chevron-left"></i></a>
                @endif

                {{-- Page Numbers --}}
                @if($items->hasPages())
                @foreach($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="btn btn-sm fw-bold shadow-none" style="background:var(--text-primary); color:var(--bg-surface); border-radius:8px; padding: 6px 12px; border:none;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}#inventory-table" class="btn btn-sm shadow-none" style="border:1px solid var(--border-color); color:var(--text-primary); background:transparent; border-radius:8px; padding: 6px 12px; transition:0.2s;">{{ $page }}</a>
                    @endif
                @endforeach
                @endif

                {{-- Next --}}
                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}#inventory-table" class="btn btn-sm shadow-none" style="border:1px solid var(--border-color); color:var(--text-primary); background:transparent; border-radius:8px; padding: 6px 12px; transition:0.2s;"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="btn btn-sm shadow-none disabled" style="border:1px solid var(--border-color); color:var(--text-secondary); background:transparent; border-radius:8px; padding: 6px 12px;"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Delete PIN Confirmation Modal --}}
<div class="modal fade" id="deletePinModal" tabindex="-1" aria-hidden="true" data-centered>
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background-color: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 20px 50px rgba(0,0,0,.15);">
            <div class="modal-body text-center pt-4 px-4">
                <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-red-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i class="bi bi-shield-lock" style="color:var(--accent-red);font-size:22px;"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color:var(--text-primary);">Confirm Deletion</h6>
                <p class="text-secondary small mb-3">Enter your 4-digit PIN to authorize this action.</p>
                <input type="password" inputmode="numeric" maxlength="4" id="deletePinInput"
                       class="form-control text-center mx-auto" 
                       style="letter-spacing:10px;font-size:24px;font-weight:800;max-width:160px;background:var(--bg-main);color:var(--text-primary);border:2px solid var(--border-color);"
                       placeholder="••••">
                <div class="small mt-2 d-none" id="deletePinError" style="color:var(--accent-red);">Incorrect PIN. Please try again.</div>
                <div class="small mt-1 d-none" id="deletePinAttempts" style="color:var(--text-secondary);"></div>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                <button type="button" class="btn fw-semibold px-3" data-bs-dismiss="modal" style="border-radius:8px;color:var(--text-primary);border:1px solid var(--border-color);background:transparent;">Cancel</button>
                <button type="button" class="btn fw-bold px-4" id="confirmDeletePinBtn" style="border-radius:8px;background:var(--accent-red);color:#fff;" disabled>
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- INVENTORY SCANNER MODAL -->
<div class="modal fade" id="scanModal" tabindex="-1" aria-hidden="true" data-centered>
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background-color: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color);">
            <div class="modal-body text-center p-5">
                <i class="bi bi-upc-scan mb-3 d-block" style="font-size: 4rem; color: var(--text-primary);"></i>
                <h5 class="fw-bold mb-2" style="color: var(--text-primary);">Ready to Scan</h5>
                <p class="text-secondary small mb-4">Aim your scanner at the asset tag.</p>
                
                <form onsubmit="processInventoryScan(event)">
                    <!-- Hidden input captures the hardware scanner -->
                    <input type="text" id="inventoryScanInput" class="form-control text-center shadow-none" placeholder="Awaiting input..." autocomplete="off" style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary);">
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Generate barcodes ──
    if (typeof JsBarcode === "function") {
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || '#000000';
        document.querySelectorAll('.item-barcode').forEach(function(svg) {
            const tag = svg.getAttribute('data-tag');
            if (tag) {
                JsBarcode(svg, tag, {
                    format: "CODE128", lineColor: textColor, width: 1.5, height: 100,
                    displayValue: false, margin: 0, marginTop: 0, marginBottom: 0, marginLeft: 0, marginRight: 0, background: "transparent"
                });
                const w = parseInt(svg.getAttribute('width'));
                const h = parseInt(svg.getAttribute('height'));
                if (!isNaN(w) && !isNaN(h)) {
                    svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
                    svg.removeAttribute('width');
                    svg.removeAttribute('height');
                }
            }
        });
    }

    // ── Load Stats ──
    loadInventoryStats();
    
    // ── I3.5: Load inventory stats via AJAX ──
    function loadInventoryStats() {
        var params = new URLSearchParams(window.location.search);
        fetch('/admin/inventory/stats?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('statTotal').textContent = data.total;
                document.getElementById('statAvail').textContent = data.available;
                document.getElementById('statGood').textContent = data.goodCondition;
                document.getElementById('statBorrowed').textContent = data.borrowed;
                document.getElementById('statDamaged').textContent = data.damaged;
            })
            .catch(function() {});
    }

    // ── Shortcuts help toggle ──
    window.toggleShortcutsHelp = function() {
        var dd = document.getElementById('shortcutsHelpDropdown');
        if (!dd) return;
        dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
    };
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('shortcutsHelpDropdown');
        if (dd && dd.style.display === 'block' && !e.target.closest('#shortcutsHelpBtn') && !e.target.closest('#shortcutsHelpDropdown')) {
            dd.style.display = 'none';
        }
    });

    // ── I3.1: Keyboard Shortcuts ──
    document.addEventListener('keydown', function(e) {
        // Don't trigger shortcuts when typing in inputs/selects/textareas
        var tag = e.target.tagName;
        if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA' || tag === 'BUTTON') return;
        
        switch(e.key) {
            case '/':
                e.preventDefault();
                var si = document.getElementById('invSearchInput');
                if (si) { si.focus(); si.select(); }
                break;
            case 'n':
                e.preventDefault();
                window.location.href = '/admin/inventory/create';
                break;
            case 'b':
                e.preventDefault();
                var selAll = document.getElementById('bulkSelectAll');
                if (selAll) { selAll.click(); }
                break;
            case 'Escape':
                var bulkBar = document.getElementById('bulkBar');
                if (bulkBar && bulkBar.style.display !== 'none') { bulkClear(); }
                break;
        }
    });

    // ── I1.1: Bulk Select Logic ──
    var bulkSelectAll = document.getElementById('bulkSelectAll');
    if (bulkSelectAll) {
        bulkSelectAll.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.bulk-checkbox').forEach(function(cb) { cb.checked = checked; });
            document.querySelectorAll('.bulk-checkbox-mobile').forEach(function(cb) { cb.checked = checked; });
            updateBulkBar();
        });
    }

    function updateBulkBar() {
        var checkboxes = document.querySelectorAll('.bulk-checkbox:checked, .bulk-checkbox-mobile:checked');
        var count = checkboxes.length;
        var bar = document.getElementById('bulkBar');
        var countEl = document.getElementById('bulkCount');
        if (count > 0) {
            bar.style.display = 'flex';
            countEl.textContent = count + ' selected';
        } else {
            bar.style.display = 'none';
        }
    }

    document.querySelectorAll('.bulk-checkbox, .bulk-checkbox-mobile').forEach(function(cb) {
        cb.addEventListener('change', updateBulkBar);
    });

    window.bulkPrintStudio = function() {
        var ids = [];
        document.querySelectorAll('.bulk-checkbox:checked, .bulk-checkbox-mobile:checked').forEach(function(cb) {
            ids.push(cb.value);
        });
        if (ids.length === 0) return;
        if (ids.length > 200) {
            showToast('Too many assets', 'Print Studio supports up to 200 tags per job — showing the first 200.', 'warning');
            ids = ids.slice(0, 200);
        }
        window.open('/admin/print-studio?ids=' + ids.join(','), '_blank');
    };

    window.bulkSetStatus = function(status) {
        var ids = [];
        document.querySelectorAll('.bulk-checkbox:checked, .bulk-checkbox-mobile:checked').forEach(function(cb) {
            ids.push(cb.value);
        });
        if (ids.length === 0) return;

        var btn = document.getElementById('bulkStatusBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
        btn.disabled = true;

        fetch('/admin/inventory/bulk-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: ids.join(','), status: status })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error updating status.');
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Change Status';
                btn.disabled = false;
            }
        })
        .catch(function() {
            alert('Network error.');
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Change Status';
            btn.disabled = false;
        });
    };

    window.bulkClear = function() {
        document.querySelectorAll('.bulk-checkbox, .bulk-checkbox-mobile').forEach(function(cb) { cb.checked = false; });
        if (bulkSelectAll) bulkSelectAll.checked = false;
        updateBulkBar();
    };

    // ── I1.1: Inline status quick-edit popover ──
    document.addEventListener('click', function(e) {
        var badge = e.target.closest('.inv-status-badge');
        if (badge) {
            e.preventDefault();
            e.stopPropagation();
            // Remove any existing popover
            var existing = document.querySelector('.status-popover');
            if (existing) existing.remove();
            
            var id = badge.getAttribute('data-id');
            var currentStatus = badge.getAttribute('data-status');
            var rect = badge.getBoundingClientRect();
            
            var popover = document.createElement('div');
            popover.className = 'status-popover';
            popover.style.left = Math.min(rect.left, window.innerWidth - 170) + 'px';
            popover.style.top = (rect.bottom + 4) + 'px';
            
            var statuses = ['available', 'ongoodcondition', 'borrowed', 'damaged', 'maintenance', 'disposed'];
            var labels = ['Available', 'On Good Condition', 'Borrowed', 'Damaged', 'Maintenance', 'Disposed'];
            var colors = ['status-green', 'status-blue', 'status-yellow', 'status-red', 'status-orange', 'status-gray'];
            
            var html = '';
            for (var i = 0; i < statuses.length; i++) {
                if (statuses[i] !== currentStatus) {
                    html += '<button class="status-opt ' + colors[i] + '" onclick="quickSetStatus(' + id + ', \'' + statuses[i] + '\', this)">' + labels[i] + '</button>';
                }
            }
            popover.innerHTML = html;
            document.body.appendChild(popover);
        } else {
            var popover = document.querySelector('.status-popover');
            if (popover && !e.target.closest('.status-popover')) {
                popover.remove();
            }
        }
    });

    window.quickSetStatus = function(id, status, btn) {
        btn.disabled = true;
        btn.innerHTML = '...';
        fetch('/admin/inventory/bulk-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: id.toString(), status: status })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) window.location.reload();
            else { btn.disabled = false; btn.textContent = 'Error'; }
        })
        .catch(function() { btn.disabled = false; btn.textContent = 'Error'; });
    };

    // ── I1.1: Bulk status dropdown toggle ──
    document.getElementById('bulkStatusBtn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        var menu = document.getElementById('bulkStatusMenu');
        menu.classList.toggle('show');
    });
    document.addEventListener('click', function() {
        var menu = document.getElementById('bulkStatusMenu');
        if (menu) menu.classList.remove('show');
    });

    // ── I5.1: Copy tag to clipboard ──
    window.copyTag = function(tag, btn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tag).then(function() {
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-check-lg';
                    icon.style.color = 'var(--accent-green)';
                    setTimeout(function() {
                        icon.className = 'bi bi-clipboard';
                        icon.style.color = '';
                    }, 1500);
                }
            }).catch(function() {});
        }
    };

    // ── I1.2: Saved Filters ──
    function loadSavedFilters() {
        var list = document.getElementById('savedFiltersList');
        if (!list) return;
        var filters = JSON.parse(localStorage.getItem('inv-saved-filters') || '[]');
        if (filters.length === 0) {
            list.innerHTML = '<div class="p-3 text-center text-secondary" style="font-size:11px;">No saved filters yet.</div>';
            return;
        }
        var html = '';
        filters.forEach(function(f, i) {
            html += '<div class="saved-filter-item" onclick="applyFilter(' + i + ')">'
                 +   '<span>' + f.name + '</span>'
                 +   '<button class="del-filter" onclick="event.stopPropagation();deleteFilter(' + i + ')">&times;</button>'
                 + '</div>';
        });
        list.innerHTML = html;
    }
    window.showSavedFilters = function() {
        var dd = document.getElementById('savedFiltersDropdown');
        var isVisible = dd.style.display === 'block';
        dd.style.display = isVisible ? 'none' : 'block';
        if (!isVisible) loadSavedFilters();
    };
    // ── Theme-aware filter naming modal ──
    window.saveCurrentFilter = function() {
        var overlay = document.getElementById('filterNameOverlay');
        var input = document.getElementById('filterNameInput');
        if (!overlay || !input) return;
        input.value = '';
        input.className = 'form-control custom-input text-center fw-bold';
        input.placeholder = 'e.g. Borrowed IT Assets';
        overlay.style.display = 'flex';
        setTimeout(function() { input.focus(); }, 100);
    };
    window.confirmFilterName = function() {
        var input = document.getElementById('filterNameInput');
        var overlay = document.getElementById('filterNameOverlay');
        if (!input || !overlay) return;
        var name = input.value.trim();
        if (!name) {
            input.className = 'form-control custom-input text-center fw-bold is-invalid';
            return;
        }
        overlay.style.display = 'none';
        var filters = JSON.parse(localStorage.getItem('inv-saved-filters') || '[]');
        filters.push({ name: name, params: { status: '{{ request('status', 'all') }}', category: '{{ request('category', 'all') }}', search: '{{ request('search', '') }}' } });
        localStorage.setItem('inv-saved-filters', JSON.stringify(filters));
        loadSavedFilters();
    };
    window.cancelFilterName = function() {
        var overlay = document.getElementById('filterNameOverlay');
        if (overlay) overlay.style.display = 'none';
    };
    // Close on overlay click (not on modal click)
    document.addEventListener('click', function(e) {
        if (e.target.id === 'filterNameOverlay') {
            document.getElementById('filterNameOverlay').style.display = 'none';
        }
    });
    // Enter key support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && document.getElementById('filterNameOverlay').style.display === 'flex') {
            confirmFilterName();
        }
        if (e.key === 'Escape' && document.getElementById('filterNameOverlay').style.display === 'flex') {
            cancelFilterName();
        }
    });
    window.applyFilter = function(index) {
        var filters = JSON.parse(localStorage.getItem('inv-saved-filters') || '[]');
        var f = filters[index];
        if (!f) return;
        var params = new URLSearchParams();
        if (f.params.status && f.params.status !== 'all') params.set('status', f.params.status);
        if (f.params.category && f.params.category !== 'all') params.set('category', f.params.category);
        if (f.params.search) params.set('search', f.params.search);
        var qs = params.toString();
        window.location.href = '/admin/inventory' + (qs ? '?' + qs : '');
    };
    window.deleteFilter = function(index) {
        var filters = JSON.parse(localStorage.getItem('inv-saved-filters') || '[]');
        filters.splice(index, 1);
        localStorage.setItem('inv-saved-filters', JSON.stringify(filters));
        loadSavedFilters();
    };
    // Close saved filters on outside click
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('savedFiltersDropdown');
        if (dd && dd.style.display === 'block' && !e.target.closest('[onclick="showSavedFilters()"]') && !e.target.closest('#savedFiltersDropdown')) {
            dd.style.display = 'none';
        }
    });

    // ── I1.1: Column visibility toggle ──
    window.toggleColumnMenu = function() {
        var menu = document.getElementById('columnToggleMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    };
    window.toggleCol = function(col) {
        var cells = document.querySelectorAll('[data-colgroup="' + col + '"]');
        var hidden = cells.length > 0 && cells[0].classList.contains('col-hidden');
        cells.forEach(function(c) { c.classList.toggle('col-hidden'); });
        var cols = JSON.parse(localStorage.getItem('inv-columns') || '["category","location"]');
        if (hidden) { cols.push(col); } else { cols = cols.filter(function(c) { return c !== col; }); }
        localStorage.setItem('inv-columns', JSON.stringify(cols));
    };
    window.resetColumns = function() {
        localStorage.removeItem('inv-columns');
        document.querySelectorAll('[data-colgroup]').forEach(function(c) { c.classList.remove('col-hidden'); });
        document.querySelectorAll('.column-opt input').forEach(function(cb) { cb.checked = true; });
        document.getElementById('columnToggleMenu').style.display = 'none';
    };
    // Restore saved column visibility
    (function() {
        var cols = JSON.parse(localStorage.getItem('inv-columns') || '["category","location"]');
        cols.forEach(function(col) {
            document.querySelectorAll('[data-colgroup="' + col + '"]').forEach(function(c) { c.classList.remove('col-hidden'); });
        });
        var hiddenCols = ['category', 'location'].filter(function(c) { return cols.indexOf(c) === -1; });
        hiddenCols.forEach(function(col) {
            document.querySelectorAll('[data-colgroup="' + col + '"]').forEach(function(c) { c.classList.add('col-hidden'); });
            document.querySelector('.column-opt input[data-col="' + col + '"]').checked = false;
        });
    })();
    // Close column menu on outside click
    document.addEventListener('click', function(e) {
        var menu = document.getElementById('columnToggleMenu');
        if (menu && menu.style.display === 'block' && !e.target.closest('#columnToggleBtn') && !e.target.closest('#columnToggleMenu')) {
            menu.style.display = 'none';
        }
    });

    // ── I1.3: Mobile card swipe gestures ──
    (function() {
        var startX, startY, currentCard;
        document.addEventListener('touchstart', function(e) {
            var card = e.target.closest('.inv-mobile-card');
            if (!card) return;
            currentCard = card;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, {passive: true});
        document.addEventListener('touchend', function(e) {
            if (!currentCard || startX === undefined) return;
            var endX = e.changedTouches[0].clientX;
            var endY = e.changedTouches[0].clientY;
            var dx = endX - startX;
            var dy = endY - startY;
            if (Math.abs(dx) > 80 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                if (dx < 0) {
                    // Swipe left → Delete
                    var delBtn = currentCard.querySelector('.delete-asset-btn');
                    if (delBtn) delBtn.click();
                } else {
                    // Swipe right → Edit
                    var editBtn = currentCard.querySelector('a[href*="/edit"]');
                    if (editBtn) window.location.href = editBtn.getAttribute('href');
                }
            }
            currentCard = null;
            startX = undefined;
        }, {passive: true});
    })();

    // ── Delete with PIN Confirmation ──
    var targetDeleteForm = null;
    var deletePinValue = '';
    var deletePinModal = null;

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.delete-asset-btn');
        if (!btn) return;
        e.preventDefault();
        targetDeleteForm = btn.closest('form');
        deletePinValue = '';
        var pinInput = document.getElementById('deletePinInput');
        if (pinInput) { pinInput.value = ''; }
        var errEl = document.getElementById('deletePinError');
        if (errEl) { errEl.classList.add('d-none'); }
        var attEl = document.getElementById('deletePinAttempts');
        if (attEl) { attEl.classList.add('d-none'); }
        var confirmBtn = document.getElementById('confirmDeletePinBtn');
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete'; }
        if (!deletePinModal) { deletePinModal = new bootstrap.Modal(document.getElementById('deletePinModal')); }
        deletePinModal.show();
        setTimeout(function() { if (pinInput) pinInput.focus(); }, 300);
    });

    var deletePinInput = document.getElementById('deletePinInput');
    if (deletePinInput) {
        deletePinInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
            deletePinValue = this.value;
            var confirmBtn = document.getElementById('confirmDeletePinBtn');
            if (confirmBtn) confirmBtn.disabled = deletePinValue.length !== 4;
        });
        deletePinInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && deletePinValue.length === 4) {
                document.getElementById('confirmDeletePinBtn').click();
            }
        });
    }

    var confirmDeletePinBtn = document.getElementById('confirmDeletePinBtn');
    if (confirmDeletePinBtn) {
        confirmDeletePinBtn.addEventListener('click', function() {
            if (!targetDeleteForm || deletePinValue.length !== 4) return;
            var btn = this;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
            btn.disabled = true;
            var errEl = document.getElementById('deletePinError');
            var attEl = document.getElementById('deletePinAttempts');
            errEl.classList.add('d-none');
            attEl.classList.add('d-none');

            fetch('/auth/verify-pin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ pin: deletePinValue })
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(res) {
                if (res.ok && res.data.status === 'success') {
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Confirmed';
                    btn.style.background = 'var(--accent-green)';
                    setTimeout(function() {
                        if (deletePinModal) deletePinModal.hide();
                        targetDeleteForm.submit();
                    }, 500);
                } else {
                    errEl.textContent = res.data.message || 'Incorrect PIN.';
                    errEl.classList.remove('d-none');
                    if (res.data.attempts_remaining !== undefined) {
                        attEl.textContent = res.data.attempts_remaining + ' attempt(s) remaining before lockout.';
                        attEl.classList.remove('d-none');
                    }
                    btn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete';
                    btn.disabled = false;
                    deletePinValue = '';
                    document.getElementById('deletePinInput').value = '';
                    document.getElementById('deletePinInput').focus();
                }
            })
            .catch(function() {
                errEl.textContent = 'Network error. Please try again.';
                errEl.classList.remove('d-none');
                btn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete';
                btn.disabled = false;
            });
        });
    }

    // ── Search ──
    var searchInput = document.getElementById('invSearchInput');
    if (searchInput) {
        if (searchInput.value) {
            searchInput.focus();
            var val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
        var debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                searchInput.closest('form').submit();
            }, 1000);
        });
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(debounceTimer);
                searchInput.closest('form').submit();
            }
        });
    }

    // ── Tooltip toggle for touch ──
    document.querySelectorAll('.unavailable-note-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var tooltip = this.querySelector('.unavailable-tooltip');
            if (tooltip) {
                var isVisible = window.getComputedStyle(tooltip).visibility === 'visible';
                document.querySelectorAll('.unavailable-tooltip').forEach(function(t) {
                    t.style.visibility = 'hidden';
                    t.style.opacity = '0';
                });
                if (!isVisible) {
                    tooltip.style.visibility = 'visible';
                    tooltip.style.opacity = '1';
                }
            }
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.unavailable-tooltip').forEach(function(t) {
            t.style.visibility = 'hidden';
            t.style.opacity = '0';
        });
    });

    // ── Scanner ──
    var scanModal = document.getElementById('scanModal');
    var invScanInput = document.getElementById('inventoryScanInput');
    if (scanModal) {
        scanModal.addEventListener('shown.bs.modal', function() {
            invScanInput.value = '';
            invScanInput.focus();
        });
        invScanInput.addEventListener('blur', function() {
            if (scanModal.classList.contains('show')) invScanInput.focus();
        });
    }
    window.processInventoryScan = function(event) {
        event.preventDefault();
        var scannedTag = invScanInput.value.trim();
        var mainSearch = document.querySelector('input[name="search"]');
        if (mainSearch && scannedTag) {
            mainSearch.value = scannedTag;
            mainSearch.closest('form').submit();
        }
    };
});
</script>
<!-- Barcode Scanner Script for Mobile -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let barcodeScanner = null;
    
    function openBarcodeScanner() {
        const modal = new bootstrap.Modal(document.getElementById('barcodeScannerModal'));
        modal.show();
        
        // Initialize scanner when modal is shown
        document.getElementById('barcodeScannerModal').addEventListener('shown.bs.modal', function () {
            if (!barcodeScanner) {
                barcodeScanner = new Html5Qrcode("barcode-reader");
                barcodeScanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 100 } },
                    onBarcodeScanned,
                    onBarcodeScanFailure
                ).catch(function(err) {
                    // Camera not available, show manual input
                    document.getElementById('barcode-reader').style.display = 'none';
                    document.getElementById('barcode-manual-input').style.display = 'block';
                    document.getElementById('manualBarcodeInput').focus();
                });
            }
        });
    }
    
    function stopBarcodeScanner() {
        if (barcodeScanner) {
            barcodeScanner.stop().then(function() {
                barcodeScanner = null;
            }).catch(function(err) {
                console.log(err);
            });
        }
        document.getElementById('barcode-reader').style.display = 'block';
        document.getElementById('barcode-manual-input').style.display = 'none';
    }
    
    function onBarcodeScanned(decodedText, decodedResult) {
        // Stop scanner
        if (barcodeScanner) {
            barcodeScanner.stop();
            barcodeScanner = null;
        }
        
        // Play success sound
        try {
            let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');
            audio.play();
        } catch(e) {}
        
        // Close modal and redirect with search
        bootstrap.Modal.getInstance(document.getElementById('barcodeScannerModal')).hide();
        window.location.href = '/admin/inventory?search=' + encodeURIComponent(decodedText);
    }
    
    function onBarcodeScanFailure(error) {
        // Ignore - scanner keeps looking
    }
    
    function searchBarcode() {
        const input = document.getElementById('manualBarcodeInput');
        if (input.value.trim()) {
            bootstrap.Modal.getInstance(document.getElementById('barcodeScannerModal')).hide();
            window.location.href = '/admin/inventory?search=' + encodeURIComponent(input.value.trim());
        }
    }
    
    // Allow Enter key on manual input
    document.addEventListener('DOMContentLoaded', function() {
        const manualInput = document.getElementById('manualBarcodeInput');
        if (manualInput) {
            manualInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchBarcode();
                }
            });
        }
    });
</script>

<script>
// ═══════════════════════════════════════════════════════════════════
// INVENTORY ROW DRAWER — property-tag hero + full asset details
// ═══════════════════════════════════════════════════════════════════
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
if (typeof window.escapeHtml !== 'function') {
    window.escapeHtml = function (t) {
        const div = document.createElement('div');
        div.textContent = t;
        return div.innerHTML;
    };
}

window.renderInventoryItemDrawer = function (tr) {
    const id = tr.getAttribute('data-id');
    if (!id) return;

    document.querySelectorAll('.inv-row.drawer-active').forEach(function (r) { r.classList.remove('drawer-active'); });
    tr.classList.add('drawer-active');

    AppDrawer.show({
        overline: 'Asset overview',
        title: 'Loading asset…',
        body: '<div class="text-center py-5"><div class="spinner-border" style="color: var(--accent-color);"></div></div>',
    });

    fetch('/admin/inventory/' + id + '/details', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function (r) { if (!r.ok) throw 0; return r.json(); })
    .then(function (d) {
        const esc = window.escapeHtml || function (t) { return t; };
          const statusMap = {
              available:      ['#ecfdf5', '#10b981'],
              ongoodcondition: ['var(--accent-blue-bg, #eaf2f9)', 'var(--accent-blue, #1b3550)'],
              borrowed:       ['#fffbeb', '#f59e0b'],
              damaged:        ['#fef2f2', '#ef4444'],
              maintenance:    ['#fef2f2', '#ef4444'],
              disposed:       ['#f3f4f6', '#6b7280'],
              archived:       ['#f3f4f6', '#6b7280'],
          };
          const [sbg, sfg] = statusMap[d.status] || statusMap.available;
          const statusText = d.status === 'ongoodcondition' ? 'GOOD CONDITION' : d.status === 'disposed' ? 'DISPOSED' : ['damaged','maintenance'].includes(d.status) ? 'UNAVAILABLE' : d.status.toUpperCase();

        // ── Small builders for the professional overview layout ──
        const secHead = function (icon, text) {
            return '<div class="d-flex align-items-center gap-2 mb-2 mt-1">'
                 + '<i class="bi ' + icon + '" style="font-size:12px;color:var(--accent-color,var(--accent-blue,#1b3550));"></i>'
                 + '<span style="font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-secondary);white-space:nowrap;">' + text + '</span>'
                 + '<span style="flex:1;height:1px;background:var(--border-color);min-width:12px;"></span>'
                 + '</div>';
        };
        const tile = function (icon, label, value, wide) {
            return '<div class="p-3" style="background:var(--bg-main);border:1px solid var(--border-color);border-radius:12px;'
                 + (wide ? 'grid-column:1/-1;' : '') + 'min-width:0;">'
                 + '<div class="d-flex align-items-center gap-1" style="font-size:9.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:5px;"><i class="bi ' + icon + '" style="font-size:11px;"></i>' + label + '</div>'
                 + '<div style="font-size:13.5px;font-weight:600;color:var(--text-primary);line-height:1.35;word-break:break-word;">'
                 + (value ? esc(String(value)) : '<span style="opacity:.4;">—</span>') + '</div>'
                 + '</div>';
        };
        const grid = function (inner) {
            return '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:14px;">' + inner + '</div>';
        };

        const body =
            // ── Property Tag — official print replica on scanner bed ──
            '<div class="mb-4">'
              + secHead('bi-upc-scan', 'Property Tag · Print Replica')
              + '<div style="padding:18px 16px;border-radius:14px;border:1px dashed var(--border-color);'
                   + 'background-image:radial-gradient(var(--border-color) 1px,transparent 1px);background-size:11px 11px;background-position:-6px -6px;">'
                + '<div style="max-width:440px;margin:0 auto;">'
                  + '<div class="tag-container-preview" style="box-shadow:0 14px 30px rgba(16,24,40,.32);">'
                    + '<div class="tag-header" style="background-color:' + esc(d.supplier_color || '#FFFF00') + ';">Supplier: ' + esc(d.supplier || 'Unassigned') + '</div>'
                    + '<div class="tag-body">'
                      + '<div class="left-panel">'
                        + '<img src="{{ asset("/images/deped-logo.png") }}" alt="DepEd Logo" style="width:82%;height:auto;">'
                        + '<div class="barcode-placeholder text-center mt-auto w-100">'
                          + '<svg id="drawerItemBarcode" style="width:100%;height:auto;max-height:38px;object-fit:contain;display:block;"></svg>'
                          + '<small style="font-weight:normal;font-size:9.5px;letter-spacing:.03em;">' + esc(d.property_tag) + '</small>'
                        + '</div>'
                      + '</div>'
                      + '<div class="right-panel">'
                        + '<table class="preview-table">'
                          + '<tr><td>Property Number</td><td><strong>' + esc(d.property_tag) + '</strong></td></tr>'
                          + '<tr><td>Asset Classification</td><td>' + esc(d.category || '--') + '</td></tr>'
                          + '<tr><td>Sub-Category / Tag</td><td>' + esc(d.tag || '--') + '</td></tr>'
                          + '<tr><td>Item/Brand/Model</td><td>' + esc(d.name || '--') + '</td></tr>'
                          + '<tr><td>Serial Number</td><td>' + esc(d.serial_number || 'N/A') + '</td></tr>'
                          + '<tr><td>Acquisition Cost</td><td>' + esc(d.acquisition_cost || '₱0.00') + '</td></tr>'
                          + '<tr><td>Acquisition Date</td><td>' + esc(d.acquisition_date || '--/--/----') + '</td></tr>'
                          + '<tr><td>Accountable Personnel</td><td>' + esc(d.accountable_personnel || 'Unassigned') + '</td></tr>'
                          + '<tr><td>Validation Signature</td><td><br></td></tr>'
                        + '</table>'
                      + '</div>'
                    + '</div>'
                    + '<div class="tag-footer">TAMPERING <span style="border-bottom: 1px solid #f8aba6;">OF</span> THIS PROPERTY TAG IS PUNISHABLE BY LAW</div>'
                  + '</div>'
                + '</div>'
                + '<div class="text-center mt-2" style="font-size:9.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-secondary);">'
                  + '<i class="bi bi-printer me-1"></i>True DepEd label proportions · CODE128 barcode'
                + '</div>'
              + '</div>'
              + '<div class="d-flex justify-content-end mt-2">'
                + '<button type="button" class="btn btn-sm shadow-none d-inline-flex align-items-center gap-1" onclick="copyTag(\'' + d.property_tag + '\', this)" title="Copy tag number" '
                  + 'style="border:1px solid var(--border-color);border-radius:7px;padding:3px 10px;font-size:11px;font-weight:600;color:var(--text-primary);background:transparent;">'
                  + '<i class="bi bi-clipboard"></i>Copy tag number'
                + '</button>'
              + '</div>'
            + '</div>'

            // ── Status strip ──
            + secHead('bi-activity', 'Current Status')
            + '<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap p-3 mb-3" '
                 + 'style="border:1px solid var(--border-color);border-radius:12px;background:var(--bg-main);">'
              + '<div class="d-flex align-items-center gap-2">'
                + '<span class="badge rounded-pill py-2 px-3 fw-bold" id="drawerStatusPill" data-status="' + d.status + '" '
                  + 'style="background-color:' + sbg + ';color:' + sfg + ';border:1px solid ' + sfg + ';font-size:11px;letter-spacing:.05em;">' + statusText + '</span>'
                + '<span style="font-size:11.5px;color:var(--text-secondary);">Condition as of ' + esc(d.updated_human || 'now') + '</span>'
              + '</div>'
              + '<select id="drawerStatusSelect" class="form-select form-select-sm theme-dynamic-input" style="width:auto;min-width:170px;" '
                + 'onchange="drawerSetStatus(' + d.id + ', this.value)">'
                + ['available','ongoodcondition','borrowed','damaged','maintenance','disposed'].map(function (s) {
                    var labels = {available:'Available',ongoodcondition:'On Good Condition',borrowed:'Borrowed',damaged:'Damaged',maintenance:'Maintenance',disposed:'Disposed'};
                    return '<option value="' + s + '"' + (s === d.status ? ' selected' : '') + '>' + (labels[s] || s) + '</option>';
                  }).join('')
              + '</select>'
            + '</div>'

            // ── Holder banner (when borrowed) ──
            + (d.current_holder
                ? '<div class="d-flex align-items-start gap-3 p-3 mb-3" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.35);border-radius:12px;">'
                  + '<span class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;border-radius:50%;background:#fffbeb;border:1px solid rgba(245,158,11,.4);"><i class="bi bi-person-fill" style="color:#b45309;font-size:15px;"></i></span>'
                  + '<div style="min-width:0;">'
                    + '<div style="font-size:9.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#b45309;">Currently issued to</div>'
                    + '<div style="font-size:14px;font-weight:700;color:var(--text-primary);">' + esc(d.current_holder.name) + '</div>'
                    + (d.current_holder.since ? '<div style="font-size:11.5px;color:var(--text-secondary);">In custody since ' + d.current_holder.since + '</div>' : '')
                  + '</div>'
                + '</div>' : '')

            // ── Asset identity ──
            + secHead('bi-box-seam', 'Asset Identity')
            + grid(
                tile('bi-pc-display-horizontal', 'Item / Brand / Model', d.name, true)
              + tile('bi-tags-fill', 'Classification', d.category)
              + tile('bi-bookmark-star-fill', 'Sub-Category / Tag', d.tag)
            )

            // ── Custody & accountability ──
            + secHead('bi-person-badge', 'Custody & Accountability')
            + grid(
                tile('bi-person-check-fill', 'Accountable Personnel', d.accountable_personnel)
              + tile('bi-geo-alt-fill', 'Location',
                    d.location ? d.location + (d.location_code ? ' · ' + d.location_code : '') : null)
              + tile('bi-shop', 'Supplier / Fund Source', d.supplier)
            )

            // ── Acquisition record ──
            + secHead('bi-receipt-cutoff', 'Acquisition Record')
            + grid(
                tile('bi-calendar-event-fill', 'Acquired On', d.acquisition_date)
              + tile('bi-cash-coin', 'Unit Cost', d.acquisition_cost)
              + tile('bi-123', 'Serial Number', d.serial_number)
              + tile('bi-calendar-check', 'Registered On', d.registered_at)
              + tile('bi-hash', 'System ID', '#' + d.id)
            )

            // ── Footer meta ──
            + '<div class="d-flex align-items-center gap-2 pt-1" style="font-size:11px;color:var(--text-secondary);border-top:1px solid var(--border-color);margin-top:2px;padding-top:10px;">'
              + '<i class="bi bi-clock-history"></i>Last updated ' + esc(d.updated_human || '')
            + '</div>';

        // Footer actions → existing pages/flows
        AppDrawer.show({
            overline: 'Asset overview',
            title: d.name,
            body: body,
            footer: [
                { label: '<i class="bi bi-grid-3x3-gap me-1"></i> Print in Studio', class: 'btn btn-light',
                  onClick: function () { window.open('/admin/print-studio?ids=' + d.id, '_blank'); } },
                { label: '<i class="bi bi-pencil-square me-1"></i> Edit', class: 'btn btn-primary',
                  onClick: function () { window.location.href = '/admin/inventory/' + d.id + '/edit'; } },
            ],
        });

        // Barcode inside the property tag replica's left panel (JsBarcode already loaded on this page)
        if (typeof JsBarcode === 'function') {
            try {
                JsBarcode('#drawerItemBarcode', d.property_tag, {
                    format: 'CODE128', lineColor: '#000000',
                    width: 1.1, height: 34, displayValue: false, margin: 0, background: 'transparent'
                });
            } catch (e) {}
        }

        // Row badge live-sync helper for the status select above
        window.__drawerSyncRowStatus = function (itemId, newStatus) {
            const badge = document.querySelector('.inv-status-badge[data-id="' + itemId + '"]');
            if (!badge) return;
            const m = statusMap[newStatus] || statusMap.available;
            const txt = newStatus === 'ongoodcondition' ? 'GOOD CONDITION' : newStatus === 'disposed' ? 'DISPOSED' : ['damaged','maintenance'].includes(newStatus) ? 'UNAVAILABLE' : newStatus.toUpperCase();
            badge.dataset.status = newStatus;
            badge.textContent = txt;
            badge.style.backgroundColor = m[0];
            badge.style.color = m[1];
            badge.style.borderColor = m[1];
        };
    })
    .catch(function () {
        AppDrawer.show({ overline: 'Asset overview', title: 'Could not load asset',
                         body: '<p class="text-secondary text-center py-4">Please refresh and try again.</p>' });
    });
};

async function drawerSetStatus(itemId, status) {
    try {
        const res = await fetch('/admin/inventory/bulk-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ ids: String(itemId), status: status })
        });
        const data = await res.json();
        if (res.ok) {
            showToast('Updated', "Status changed to '" + status + "'.", 'success');
            if (window.__drawerSyncRowStatus) window.__drawerSyncRowStatus(itemId, status);
            const pill = document.getElementById('drawerStatusPill');
            const map = { available:['#ecfdf5','#10b981'], ongoodcondition:['var(--accent-blue-bg, #eaf2f9)','var(--accent-blue, #1b3550)'], borrowed:['#fffbeb','#f59e0b'], damaged:['#fef2f2','#ef4444'], maintenance:['#fef2f2','#ef4444'], disposed:['#f3f4f6','#6b7280'] };
            const c = map[status] || map.available;
            pill.dataset.status = status;
            pill.textContent = status === 'ongoodcondition' ? 'GOOD CONDITION' : status === 'disposed' ? 'DISPOSED' : ['damaged','maintenance'].includes(status) ? 'UNAVAILABLE' : status.toUpperCase();
            pill.style.backgroundColor = c[0]; pill.style.color = c[1]; pill.style.borderColor = c[1];
        } else {
            showToast('Error', data.message || 'Failed to update status.', 'error');
        }
    } catch (e) {
        showToast('Error', 'Network error.', 'error');
    }
}
</script>
@endsection