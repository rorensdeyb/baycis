@extends('layouts.admin')

@section('content')
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
</style>

<div class="dashboard-wrapper">
    <h2 class="fw-bold mb-4" style="color: var(--text-primary);">Inventory</h2>
    <div class="panel-card d-flex justify-content-between align-items-center mb-4 p-3" style="border-radius: 12px;">
        <form action="/admin/inventory" method="GET" class="d-flex align-items-center gap-3 m-0">  
            <div class="position-relative" style="width: 280px;">
                <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="search" name="search" class="form-control shadow-none theme-dynamic-input w-100" 
                    style="padding-left: 42px;" 
                    placeholder="Search assets, tags..." 
                    value="{{ request('search') }}">
            </div>
            {{-- Style for Search Bar --}}
            <style>
                .theme-dynamic-input {
                    color: var(--text-primary) !important;
                }
            
                .theme-dynamic-input::placeholder {
                    color: var(--text-secondary) !important;
                    opacity: 0.7;
                }
                .theme-dynamic-input:focus {
                    background-color: transparent !important;
                    color: var(--text-primary) !important;
                }
                .theme-dynamic-icon {
                    color: var(--text-secondary) !important;
                }
            </style>
            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 shadow-none" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background: transparent; border: 1px solid var(--text-secondary); color: var(--text-primary);">
                    <i class="bi bi-filter"></i> Filter
                </button>
                <div class="dropdown-menu p-3 shadow" style="width: 250px; border-radius: 12px; background-color: var(--bg-surface); border: 1px solid var(--text-secondary);">
                    
                    <h6 class="dropdown-header px-0 fw-bold mb-2" style="color: var(--text-primary);">Filter by Category</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="category" value="all" id="catAll" 
                            {{ request('category', 'all') === 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="catAll" style="color: var(--text-primary);">All Classifications</label>
                    </div>
                    
                    @if(isset($activeCategories))
                        @foreach($activeCategories as $cat)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="category" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" 
                                {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label" for="cat_{{ $cat->id }}" style="color: var(--text-primary);">{{ $cat->name }}</label>
                        </div>
                        @endforeach
                    @endif
                    
                    <hr class="dropdown-divider my-3" style="border-color: var(--text-secondary);">

                    <h6 class="dropdown-header px-0 fw-bold mb-2" style="color: var(--text-primary);">Filter by Status</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="status" value="all" id="statAll" 
                            {{ request('status', 'all') === 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="statAll" style="color: var(--text-primary);">All States</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="status" value="available" id="statAvail" 
                            {{ request('status') === 'available' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fw-bold" for="statAvail" style="color: var(--accent-green);">Available</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="status" value="borrowed" id="statBorr" 
                            {{ request('status') === 'borrowed' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fw-bold" for="statBorr" style="color: var(--accent-yellow);">Borrowed</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="status" value="issued" id="statIssued" 
                            {{ request('status') === 'issued' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fw-bold" for="statIssued" style="color: var(--accent-blue);">Issued</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="status" value="damaged" id="statDam" 
                            {{ request('status') === 'damaged' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fw-bold" for="statDam" style="color: var(--accent-red);">Damaged / Repair</label>
                    </div>
                    
                    <hr class="dropdown-divider my-3" style="border-color: var(--text-secondary);">
                    <a href="/admin/inventory" class="btn btn-sm w-100 fw-semibold" style="border: 1px solid var(--text-secondary); color: var(--text-primary);">Clear Filters</a>
                </div>
            </div>
        </form>

        <a href="/admin/inventory/create" class="btn fw-bold d-flex align-items-center gap-2 px-4 py-2" style="background-color: var(--text-primary); color: var(--bg-surface); border-radius: 8px; transition: opacity 0.2s;">
            <i class="bi bi-plus-lg"></i> Add New Item
        </a>
    </div>
    <div id="inventory-table" class="panel-card p-0 overflow-hidden shadow-sm">
        <table class="admin-table mb-0">
            <thead>
                
                
                    <th class="ps-4">PROPERTY TAG</th>
                    <th>ITEM NAME</th>
                    <th>CATEGORY</th>
                    <th>LOCATION</th>
                    <th>STATUS</th>
                    <th class="text-end pe-4">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td class="ps-4 py-3">
                        <div class="mb-1" style="height: 35px; width: 100%; max-width: 220px; overflow: hidden;">
                            <svg class="item-barcode" data-tag="{{ $item->property_tag }}" preserveAspectRatio="none" style="width: 100% !important; height: 100% !important; display: block;"></svg>
                        </div>
                        <span class="small fw-bold">{{ $item->property_tag }}</span>
                    </td>
                    <td class="fw-bold">{{ $item->name }}</td>
                    <td class="text-secondary">{{ $item->category->name ?? 'N/A' }}</td>
                    <td class="text-secondary">{{ $item->location->name ?? 'N/A' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $statusColors = [
                                    'available'   => ['bg' => 'var(--accent-green-bg)', 'text' => 'var(--accent-green)'],
                                    'borrowed'    => ['bg' => 'var(--accent-yellow-bg)', 'text' => 'var(--accent-yellow)'],
                                    'damaged'     => ['bg' => 'var(--accent-red-bg)', 'text' => 'var(--accent-red)'],
                                    'maintenance' => ['bg' => 'var(--accent-red-bg)', 'text' => 'var(--accent-red)'],
                                    'archived'    => ['bg' => 'var(--bg-surface-hover)', 'text' => 'var(--text-secondary)'],
                                ];
                                $color = $statusColors[strtolower($item->status)] ?? $statusColors['available'];
                                
                                $statusText = strtoupper($item->status);
                                if (in_array(strtolower($item->status), ['damaged', 'maintenance'])) {
                                    $statusText = 'UNAVAILABLE';
                                }
                            @endphp

                            <span class="badge rounded-pill py-2 px-3 fw-bold" 
                                style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['text'] }};">
                                {{ $statusText }}
                            </span>

                            @if($item->status !== 'available')
                                @php
                                    $reason = 'Unavailable';
                                    if ($item->status === 'borrowed') {
                                        $reason = 'Currently Borrowed / Loaned';
                                    } elseif ($item->status === 'damaged') {
                                        $reason = 'Damaged / Broken Parts';
                                    } elseif ($item->status === 'maintenance') {
                                        $reason = 'Needs Maintenance or Repair';
                                    }
                                @endphp
                                <button type="button" class="unavailable-note-btn p-1" style="cursor: pointer; position: relative;">
                                    <i class="bi bi-chat-left-text-fill fs-5 text-danger"></i>
                                    <span class="unavailable-tooltip">
                                        {{ $reason }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group gap-2">
                            <a href="/admin/inventory/{{ $item->id }}/edit" class="btn btn-sm border" style="color: var(--text-secondary); border-color: var(--border-color) !important;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="/admin/inventory/{{ $item->id }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger delete-asset-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No items registered in the inventory.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #1e1e1e; color: #fff; border-radius: 12px; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    <div class="modal-header border-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-danger" id="deleteConfirmModalLabel">
                            <i class="bi bi-exclamation-octagon-fill me-2"></i> Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="form-label px-4 pb-3" style="font-size: 15px; line-height: 1.5;">
                        Are you sure you want to permanently delete this asset? This action cannot be undone and will permanently remove the item and its tracking history from the inventory system.
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn btn-dark border-secondary fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 14px;">
                            Cancel
                        </button>
                        <button type="button" id="confirmDeleteActionBtn" class="btn btn-danger fw-bold px-3 py-2" style="border-radius: 8px; font-size: 14px;">
                            Yes, Delete Asset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if($items->hasPages())
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-top: 1px solid var(--border-color);">
            <div class="small" style="color: var(--text-secondary);">
                Showing <strong style="color:var(--text-primary);">{{ $items->firstItem() }}</strong>–<strong style="color:var(--text-primary);">{{ $items->lastItem() }}</strong> of <strong style="color:var(--text-primary);">{{ $items->total() }}</strong> items
            </div>
            <div class="d-flex align-items-center gap-1">
                {{-- Previous --}}
                @if($items->onFirstPage())
                    <span class="btn btn-sm shadow-none disabled" style="border:1px solid var(--border-color); color:var(--text-secondary); background:transparent; border-radius:8px; padding: 6px 12px;"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $items->previousPageUrl() }}#inventory-table" class="btn btn-sm shadow-none" style="border:1px solid var(--border-color); color:var(--text-primary); background:transparent; border-radius:8px; padding: 6px 12px; transition:0.2s;"><i class="bi bi-chevron-left"></i></a>
                @endif

                {{-- Page Numbers --}}
                @foreach($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="btn btn-sm fw-bold shadow-none" style="background:var(--text-primary); color:var(--bg-surface); border-radius:8px; padding: 6px 12px; border:none;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}#inventory-table" class="btn btn-sm shadow-none" style="border:1px solid var(--border-color); color:var(--text-primary); background:transparent; border-radius:8px; padding: 6px 12px; transition:0.2s;">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}#inventory-table" class="btn btn-sm shadow-none" style="border:1px solid var(--border-color); color:var(--text-primary); background:transparent; border-radius:8px; padding: 6px 12px; transition:0.2s;"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="btn btn-sm shadow-none disabled" style="border:1px solid var(--border-color); color:var(--text-secondary); background:transparent; border-radius:8px; padding: 6px 12px;"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Generate barcodes for items
    if (typeof JsBarcode === "function") {
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || '#000000';
        document.querySelectorAll('.item-barcode').forEach(svg => {
            const tag = svg.getAttribute('data-tag');
            if (tag) {
                // Use a large internal height (100) so bars fill the entire SVG height.
                // With preserveAspectRatio="none" and the container at 35px, this stretches
                // the bars edge-to-edge with zero white padding on any side.
                JsBarcode(svg, tag, {
                    format: "CODE128",
                    lineColor: textColor,
                    width: 1.5,
                    height: 100,
                    displayValue: false,
                    margin: 0,
                    marginTop: 0,
                    marginBottom: 0,
                    marginLeft: 0,
                    marginRight: 0,
                    background: "transparent"
                });
                const w = parseInt(svg.getAttribute('width'));
                const h = parseInt(svg.getAttribute('height'));
                if (!isNaN(w) && !isNaN(h)) {
                    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
                    svg.removeAttribute('width');
                    svg.removeAttribute('height');
                }
            }
        });
    }
    
    // ==========================================
    // 1. LIVE SEARCH (AUTO-SUBMIT & REFOCUS)
    // ==========================================
    // Grab the search input (adjust the selector if your input uses a different name/id)
    const searchInput = document.querySelector('input[name="search"]') || document.querySelector('input[type="search"]'); 
    
    if (searchInput) {
        // 1. Instantly refocus the input and put the cursor at the end after page reload
        if (searchInput.value) {
            searchInput.focus();
            // This little trick forces the cursor to the very end of the text
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        // 2. Debounced Submit Engine
        let debounceTimer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            
            // Increased to 1000ms (1 full second) so it waits for you to actually finish typing
            debounceTimer = setTimeout(() => {
                // Submit the form only if the value has actually changed
                this.closest('form').submit();
            }, 1000); 
        });
        
        // 3. Optional: Let the user hit "Enter" instantly without waiting for the timer
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(debounceTimer);
                this.closest('form').submit();
            }
        });
    }

    // ==========================================
    // 2. DELETE CONFIRMATION MODAL
    // ==========================================
    let targetDeleteForm = null;

    // Listen for clicks on any "Delete" button in the table
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-asset-btn');
        
        if (deleteBtn) {
            e.preventDefault();
            
            // Find the specific form associated with this button
            targetDeleteForm = deleteBtn.closest('form');
            
            // Trigger the custom Bootstrap modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        }
    });

    // Execute the deletion when the red modal button is clicked
    const confirmBtn = document.getElementById('confirmDeleteActionBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (targetDeleteForm) {
                // Add a spinner to the button so the user knows it's processing
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Deleting...';
                this.disabled = true;
                
                // Submit the specific item's form to the backend
                targetDeleteForm.submit();
            }
        });
    }

    // Toggle tooltips on click/tap for touch screens
    document.querySelectorAll('.unavailable-note-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const tooltip = this.querySelector('.unavailable-tooltip');
            if (tooltip) {
                const isVisible = window.getComputedStyle(tooltip).visibility === 'visible';
                // Hide other open tooltips
                document.querySelectorAll('.unavailable-tooltip').forEach(t => {
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
    
    // Hide tooltips when tapping outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.unavailable-tooltip').forEach(t => {
            t.style.visibility = 'hidden';
            t.style.opacity = '0';
        });
    });
    // Fix scroll position for inventory pagination (links are built in Blade with #inventory-table anchor directly)
});
</script>
@endsection