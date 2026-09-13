@extends('layouts.borrower')

@section('content')
<style>
    /* ── Compact Mobile-First Sticky Bottom Bar ──────────────────── */
    .sticky-bottom-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: linear-gradient(to bottom, transparent 0%, var(--bg-main) 30%);
        padding-top: 8px;
        margin-top: 0;
        margin-left: -32px;
        margin-right: -32px;
        padding-left: 32px;
        padding-right: 32px;
        padding-bottom: max(calc(16px + env(safe-area-inset-bottom, 0px)), 16px);
        pointer-events: none;
    }
    .sticky-bar-inner {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 8px 12px;
        display: flex;
        flex-direction: column;
        gap: 0;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05), 0 0 0 1px rgba(0,0,0,0.02);
        pointer-events: auto;
    }
    .sticky-bar-hint {
        font-size: 11px;
        color: var(--text-secondary);
        text-align: center;
        font-weight: 500;
        margin-bottom: 4px;
        letter-spacing: 0.1px;
    }
    .sticky-bar-hint i {
        font-size: 11px;
    }
    .btn-continue-action {
        width: 100%;
        height: 42px;
        padding: 0 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .btn-continue-action.disabled {
        background: var(--bg-surface-hover);
        color: var(--text-secondary);
        border: 1px dashed var(--border-color);
        cursor: default;
        opacity: 0.75;
    }
    .btn-continue-action:not(.disabled) {
        background: var(--accent-color);
        color: var(--btn-text, #ffffff);
        box-shadow: 0 2px 10px rgba(27, 53, 80, 0.2);
    }
    .btn-continue-action:not(.disabled):hover {
        background: var(--accent-blue-hover);
        transform: translateY(-1px);
    }
    .btn-continue-action:not(.disabled):active {
        transform: scale(0.98);
    }
    .btn-continue-action .btn-icon {
        font-size: 15px;
        transition: transform 0.2s ease;
    }
    .btn-continue-action:not(.disabled):hover .btn-icon {
        transform: translateX(3px);
    }

    /* Dark mode refinements for sticky bar */
    [data-theme="dark"] .sticky-bar-inner {
        border-color: var(--border-color);
        box-shadow: 0 -4px 20px rgba(0,0,0,0.35);
    }

    @media (max-width: 768px) {
        .sticky-bottom-bar {
            bottom: 0 !important;
            padding-bottom: calc(64px + env(safe-area-inset-bottom, 0px)) !important;
            padding-top: 6px;
            margin-left: -12px;
            margin-right: -12px;
            padding-left: 12px;
            padding-right: 12px;
        }
        .sticky-bar-hint {
            display: none;
        }
        .sticky-bar-inner {
            padding: 8px 10px !important;
        }
        .btn-continue-action {
            height: 40px !important;
            font-size: 13.5px !important;
        }
        .step2-actions {
            padding-bottom: calc(64px + env(safe-area-inset-bottom, 0px)) !important;
        }
    }

    /* Compact Stepper Wizard */
    .progress-wizard {
        margin: 0 auto 20px auto !important;
        max-width: 480px;
    }
    .progress-line, .progress-line-fill {
        top: 13px !important;
        height: 2px !important;
    }
    .step-circle {
        width: 26px !important;
        height: 26px !important;
        font-size: 11px !important;
        margin-bottom: 6px !important;
        box-shadow: 0 0 0 4px var(--bg-surface) !important;
    }
    .step-label {
        font-size: 11.5px !important;
        font-weight: 600 !important;
    }

    .purpose-chip {
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        background: var(--bg-surface);
        border-radius: 8px;
        transition: all 0.15s ease;
    }
    .purpose-chip:hover {
        background: var(--bg-main);
        color: var(--text-primary);
    }
    .purpose-chip.active {
        background: var(--accent-color) !important;
        color: var(--btn-text, #ffffff) !important;
        border-color: var(--accent-color) !important;
    }

    /* Unavailable Item Pill */
    .unavailable-badge-sm {
        font-size: 10.5px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: var(--bg-surface-hover);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        white-space: nowrap;
    }

    /* Tooltip */
    .unavailable-note-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        padding: 4px;
        border-radius: 6px;
        color: var(--text-secondary);
    }
    .unavailable-note-btn:hover {
        background: var(--bg-surface-hover);
        color: var(--accent-red);
    }
    .unavailable-tooltip {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        bottom: 125%;
        right: 0;
        background-color: var(--navy-900, #14273a);
        color: #ffffff;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        white-space: nowrap;
        z-index: 1100;
        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
        transition: opacity 0.15s ease, visibility 0.15s ease;
        font-weight: 500;
    }
    .unavailable-tooltip::after {
        content: "";
        position: absolute;
        top: 100%;
        right: 8px;
        border-width: 5px;
        border-style: solid;
        border-color: var(--navy-900, #14273a) transparent transparent transparent;
    }
    .unavailable-note-btn:hover .unavailable-tooltip {
        visibility: visible;
        opacity: 1;
    }

    /* Collapsible Unavailable Section */
    .unavailable-details summary::-webkit-details-marker {
        display: none;
    }
    .unavailable-details[open] summary .details-chevron {
        transform: rotate(180deg);
    }
    .details-chevron {
        transition: transform 0.2s ease;
    }
</style>

<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Create Request</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-red-bg); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 16px;">
            <div class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Submission Failed:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: var(--accent-red-bg); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 16px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="progress-wizard" style="margin-bottom: 32px;">
        <div class="progress-line"></div>
        <div class="progress-line-fill" id="progress-fill" style="width: {{ session('qr_code_success') ? '66.66%' : '0%' }};"></div>
        
        <div class="step-item {{ session('qr_code_success') ? 'completed' : 'active' }}" id="indicator-1">
            <div class="step-circle" id="circle-1">
                @if(session('qr_code_success')) <i class="bi bi-check-lg"></i> @else 1 @endif
            </div>
            <div class="step-label" id="label-1">Select Items</div>
        </div>
        
        <div class="step-item {{ session('qr_code_success') ? 'completed' : '' }}" id="indicator-2">
            <div class="step-circle" id="circle-2">
                @if(session('qr_code_success')) <i class="bi bi-check-lg"></i> @else 2 @endif
            </div>
            <div class="step-label" id="label-2">Confirm Request</div>
        </div>
        
        <div class="step-item {{ session('qr_code_success') ? 'completed' : '' }}" id="indicator-3">
            <div class="step-circle" id="circle-3">
                @if(session('qr_code_success')) <i class="bi bi-check-lg"></i> @else 3 @endif
            </div>
            <div class="step-label" id="label-3">Submit</div>
        </div>
    </div>

    @if(session('qr_code_success'))
        <div class="activity-card text-center shadow-sm" style="max-width: 420px; margin: 20px auto; padding: 24px 16px; border-radius: 16px;">
            <div style="width: 52px; height: 52px; background: var(--bg-main); color: var(--text-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 16px auto;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="fw-bold mb-1" style="font-size: 20px;">Request Submitted!</h2>
            
            <div style="background: var(--bg-main); padding: 16px; border-radius: 12px; margin: 16px 0;">
                <p class="fw-bold text-xs text-muted mb-2 text-uppercase letter-spacing-1" style="font-size: 11px;">YOUR REQUEST QR</p>
                <img src="https://quickchart.io/qr?size=140&text={{ urlencode(session('qr_code_success')) }}" alt="QR Code" style="border-radius: 6px; border: 3px solid var(--bg-surface); box-shadow: var(--shadow-sm); width: 140px; height: 140px;">
                <p class="text-sm text-muted mt-2 mb-0" style="max-width: 220px; margin: 0 auto; font-size: 12px;">Show this QR code to the admin.</p>
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('borrower.dashboard') }}" class="btn" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); font-weight: 600; border-radius: 10px; padding: 9px 16px; font-size: 13.5px;">Return Home</a>
                <a href="{{ route('borrower.history') }}" class="btn-primary-action text-decoration-none" style="border-radius: 10px; padding: 9px 16px; font-size: 13.5px;">View Borrows</a>
            </div>
        </div>

    @else
    <form action="{{ route('borrower.request.submit') }}" method="POST" id="requestForm" novalidate>
            @csrf
            <input type="hidden" name="qr_code_hash" id="qrCodeHashInput" value="{{ $qrCodeHash }}">
            
            <div id="step-1-section">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-column flex-sm-row">
                    <div style="position: relative; flex: 1; width: 100%;">
                        <label for="searchInventoryInput" style="display: none;">Search inventory</label>
                        <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 13px;"></i>
                        <input type="text" id="searchInventoryInput" placeholder="Search by name or tag (e.g. Laptop)…" autocomplete="off" style="width: 100%; height: 42px; padding: 0 14px 0 38px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-surface); color: var(--text-primary); font-size: 13.5px; outline: none;">
                    </div>
                    <select id="tagBrowseFilter" aria-label="Filter by sub-category" style="width: 100%; max-width: 180px; height: 42px; padding: 0 12px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-surface); color: var(--text-primary); font-size: 13px; font-weight: 500;">
                        <option value="">All Types</option>
                        @foreach($items->pluck('tag.name')->filter()->unique()->sort() as $tagName)
                            <option value="{{ $tagName }}">{{ $tagName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="activity-list mb-3 d-flex flex-column gap-2" id="availableItemsList">
                    @forelse($items->where('status', 'available') as $item)
                        <label class="item-select-card" for="item_radio_{{ $item->id }}" data-browse-name="{{ strtolower(trim($item->name . ' ' . ($item->property_tag ?? '') . ' ' . ($item->tag->name ?? ''))) }}" data-tag-name="{{ strtolower($item->tag->name ?? '') }}">
                            <input type="radio" name="item_id" id="item_radio_{{ $item->id }}" value="{{ $item->id }}" required 
                                {{ old('item_id') == $item->id ? 'checked' : '' }} 
                                data-name="{{ $item->name }}" 
                                data-property-tag="{{ $item->property_tag ?? 'N/A' }}"
                                data-classification="{{ $item->category->name ?? 'Uncategorized' }}"
                                data-serial="{{ $item->serial_number ?? 'N/A' }}"
                                data-personnel="{{ $item->accountable_personnel ?? 'Unassigned' }}"
                                data-date="{{ $item->acquisition_date ? \Carbon\Carbon::parse($item->acquisition_date)->format('M d, Y') : '--/--/----' }}"
                                data-cost="{{ $item->acquisition_cost ? '₱' . number_format($item->acquisition_cost, 2) : '₱0.00' }}">
                            
                            <div class="item-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            
                            <div class="item-details">
                                <span class="item-name">{{ $item->name }}</span>
                                <span class="item-meta">
                                    @if($item->tag)
                                        <span class="badge" style="font-size: 10px; font-weight: 700; background: var(--accent-blue-bg); color: var(--accent-blue); padding: 2px 6px; border-radius: 4px;">
                                            {{ $item->tag->name }}
                                        </span>
                                    @endif
                                    ID: {{ $item->property_tag ?? 'N/A' }} • {{ $item->category->name ?? 'General' }}
                                </span>
                            </div>

                            <span class="badge ms-auto flex-shrink-0" style="font-size: 10px; font-weight: 700; background: var(--accent-green-bg); color: var(--accent-green); padding: 3px 8px; border-radius: 6px;">
                                Available
                            </span>
                        </label>
                    @empty
                        <div class="text-center text-muted py-4" style="background: var(--bg-surface); border-radius: 14px; border: 1px dashed var(--border-color);">
                            <i class="bi bi-inbox fs-2 mb-2 d-block text-secondary"></i>
                            No available items currently in stock.
                        </div>
                    @endforelse

                    <div id="browseEmptyMsg" style="display: none;" class="text-center text-muted py-4">
                        <i class="bi bi-search fs-2 mb-2 d-block text-secondary"></i>
                        <p class="mb-1 fw-semibold" style="color: var(--text-primary);">No items match your search or filter.</p>
                        <p class="small mb-0">Try a different keyword or clear the category filter.</p>
                    </div>
                </div>

                @php $unavailableItems = $items->where('status', '!=', 'available'); @endphp
                @if($unavailableItems->isNotEmpty())
                    <div id="unavailableSection" class="mb-3">
                        <details class="unavailable-details" style="border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-surface); overflow: hidden;">
                            <summary class="d-flex align-items-center justify-content-between px-3 py-2 text-secondary fw-semibold small" style="cursor: pointer; user-select: none; background: var(--bg-main);">
                                <span><i class="bi bi-slash-circle me-1 text-danger"></i> Unavailable Items (<span id="unavailableCount">{{ $unavailableItems->count() }}</span>)</span>
                                <i class="bi bi-chevron-down details-chevron" style="font-size: 11px;"></i>
                            </summary>
                            <div class="d-flex flex-column gap-2 p-2" id="unavailableList" style="background: var(--bg-main);">
                                @foreach($unavailableItems as $item)
                                    <div class="item-select-card" style="opacity: 0.65; cursor: not-allowed; background: var(--bg-surface);"
                                         data-browse-name="{{ strtolower(trim($item->name . ' ' . ($item->property_tag ?? '') . ' ' . ($item->tag->name ?? ''))) }}" 
                                         data-tag-name="{{ strtolower($item->tag->name ?? '') }}">
                                        <input type="radio" disabled style="opacity: 0.35;">
                                        
                                        <div class="item-icon">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        
                                        <div class="item-details">
                                            <span class="item-name text-muted" style="text-decoration: line-through;">{{ $item->name }}</span>
                                            <span class="item-meta text-muted">ID: {{ $item->property_tag ?? 'N/A' }} • {{ $item->category->name ?? 'General' }}</span>
                                        </div>

                                        <div class="d-flex align-items-center gap-1 ms-auto flex-shrink-0">
                                            <span class="unavailable-badge-sm">
                                                {{ $item->status === 'borrowed' ? 'Borrowed' : 'Unavailable' }}
                                            </span>
                                            
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
                                            <button type="button" class="unavailable-note-btn" title="{{ $reason }}">
                                                <i class="bi bi-info-circle text-secondary" style="font-size: 13px;"></i>
                                                <span class="unavailable-tooltip">{{ $reason }}</span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                @endif

                <div class="sticky-bottom-bar" id="stickyBottomBar">
                    <div class="sticky-bar-inner">
                        <div class="sticky-bar-hint" id="stickyBarHint">
                            <i class="bi bi-arrow-up-circle me-1"></i> Select an item above to continue
                        </div>
                        <button type="button" id="btn-continue" class="btn-continue-action disabled" disabled>
                            <span class="btn-label">Continue</span>
                            <i class="bi bi-arrow-right btn-icon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="step-2-section" style="display: none; background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 16px;">
                
                <h3 class="fw-bold mb-3 fs-6" style="color: var(--text-primary);">Review Selected Item</h3>
                
                <div class="mb-3" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color); padding: 12px 14px;">
                    <div class="d-flex align-items-center gap-2 mb-2 pb-2" style="border-bottom: 1px dashed var(--border-color);">
                        <div class="item-icon" style="background: var(--bg-surface); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-box-seam text-muted" style="font-size: 16px;"></i>
                        </div>
                        <div class="item-details flex-grow-1 min-w-0">
                            <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Item / Brand / Model</span>
                            <span class="item-name fw-bold d-block text-truncate" id="review-item-name" style="color: var(--text-primary); font-size: 14.5px; line-height: 1.2;">Item Name</span>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2px;">Asset Classification</span>
                            <span class="badge" id="review-classification" style="background: var(--border-color); color: var(--text-secondary); font-weight: 600; font-size: 11px; padding: 3px 8px; border-radius: 6px;">--</span>
                        </div>
                    </div>

                    <div class="d-md-none mb-2">
                        <button class="btn btn-sm w-100 fw-bold d-flex align-items-center justify-content-between px-2" type="button" data-bs-toggle="collapse" data-bs-target="#extendedDetails" aria-expanded="false" style="border: 1px dashed var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 6px; padding: 6px 10px; font-size: 12px;" onclick="this.querySelector('i').classList.toggle('bi-chevron-up'); this.querySelector('i').classList.toggle('bi-chevron-down');">
                            <span>View More Details</span>
                            <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                        </button>
                    </div>

                    <div class="collapse d-md-block" id="extendedDetails">
                        <div class="row g-2 mb-1">
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Property Number</span>
                                <span class="fw-bold text-break" id="review-property-tag" style="font-size: 12px; color: var(--text-primary); font-family: monospace;">YYYY-XX-XX-XXXX</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Serial Number</span>
                                <span class="fw-bold text-break" id="review-serial" style="font-size: 12px; color: var(--text-primary); font-family: monospace;">N/A</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Accountable Personnel</span>
                                <span class="fw-bold" id="review-personnel" style="font-size: 12px; color: var(--text-primary);">Unassigned</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Acquisition Date</span>
                                <span class="fw-bold" id="review-date" style="font-size: 12px; color: var(--text-primary);">--/--/----</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: 13px; color: var(--text-primary); margin-bottom: 6px;">Reason for Request</label>
                    
                    @php
                        $commonReasons = [
                            'Classroom Instruction / Lecture',
                            'School Event / Activity',
                            'Student Project / Presentation',
                            'Club / Organization Meeting',
                            'Maintenance / Repair'
                        ];
                        $oldPurpose = old('purpose');
                        $isOther = $oldPurpose && !in_array($oldPurpose, $commonReasons);
                    @endphp

                    <input type="hidden" id="purposeHidden" name="{{ $isOther ? '' : 'purpose' }}" value="{{ $isOther ? 'Other' : $oldPurpose }}">

                    <div class="d-flex flex-wrap gap-2 mb-2" id="purposeChips">
                        @foreach($commonReasons as $reason)
                            <button type="button" class="btn purpose-chip {{ $oldPurpose === $reason ? 'active' : '' }}" data-value="{{ $reason }}">
                                {{ str_replace([' / Lecture', ' / Activity', ' / Presentation', ' / Organization Meeting', ' / Repair'], '', $reason) }}
                            </button>
                        @endforeach
                        <button type="button" class="btn purpose-chip {{ $isOther ? 'active' : '' }}" data-value="Other">Other...</button>
                    </div>

                    <div id="otherPurposeContainer" style="display: {{ $isOther ? 'block' : 'none' }};">
                        <textarea id="purposeOther" name="{{ $isOther ? 'purpose' : '' }}" class="form-control" rows="2" placeholder="Please specify your exact reason..." style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; font-size: 13px; box-shadow: none;" {{ $isOther ? 'required' : '' }}>{{ $isOther ? $oldPurpose : '' }}</textarea>
                    </div>
                    <div class="lp-err-msg" id="reason-err" style="display: none; color: var(--accent-red); margin-top: 4px; font-size: 12px; font-weight: 500;">Please select or specify a reason for the request.</div>
                </div>

                <div class="mb-3">
                    <label for="neededUntilInput" class="form-label fw-bold d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-primary); margin-bottom: 6px;">
                        <i class="bi bi-calendar"></i> Needed Until <span class="text-muted fw-normal" style="font-size: 11px;">(optional)</span>
                    </label>
                    <input type="date" id="neededUntilInput" name="needed_until" class="form-control" min="{{ \Carbon\Carbon::now()->timezone('Asia/Manila')->format('Y-m-d') }}" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; font-size: 13px; box-shadow: none;">
                </div>

                <div class="mb-3 p-3 text-center" style="background: var(--bg-main); border-radius: 10px; border: 1px solid var(--border-color);">
                    <h6 class="m-0 fw-bold mb-1" style="color: var(--text-primary); font-size: 13px;">Fast-Track Request</h6>
                    <p class="text-muted" style="font-size: 11.5px; margin-bottom: 10px;">This unique QR Code is ready to be scanned by an admin.</p>
                    <img src="https://quickchart.io/qr?size=110&text={{ urlencode($qrCodeHash) }}" alt="QR Code" style="border-radius: 6px; border: 3px solid var(--bg-surface); box-shadow: var(--shadow-sm); width: 110px; height: 110px;">
                </div>

                <div class="d-flex gap-2 step2-actions">
                    <button type="button" id="btn-back" class="btn w-50" style="border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary); padding: 9px 16px; height: 42px; border-radius: 10px; font-weight: 600; font-size: 13.5px;">Back</button>
                    <button type="button" id="btn-submit-pin" class="btn-primary-action w-50 justify-content-center" style="padding: 9px 16px; height: 42px; border-radius: 10px; font-size: 13.5px; font-weight: 700; background: var(--accent-color) !important; color: var(--btn-text) !important;">Submit Request</button>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemRadios = document.querySelectorAll('input[name="item_id"]');
        const btnContinue = document.getElementById('btn-continue');
        const btnBack = document.getElementById('btn-back');
        const step1 = document.getElementById('step-1-section');
        const step2 = document.getElementById('step-2-section');
        
        // Progress Bar Elements
        const indicator1 = document.getElementById('indicator-1');
        const indicator2 = document.getElementById('indicator-2');
        const circle1 = document.getElementById('circle-1');
        const label1 = document.getElementById('label-1');
        const label2 = document.getElementById('label-2');
        
        const cards = document.querySelectorAll('.item-select-card');

        document.getElementById('requestForm')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') { e.preventDefault(); }
        });

        if (!btnContinue) return; 

        // ==========================================
        // LIVE BROWSE: search + tag filter (client-side, instant)
        // Users browse by general name / type instead of exact item names.
        // ==========================================
        const searchInput = document.getElementById('searchInventoryInput');
        const tagFilter = document.getElementById('tagBrowseFilter');
        let browseTimer = null;

        function applyBrowseFilters() {
            const q = (searchInput?.value || '').trim().toLowerCase();
            const tag = tagFilter?.value || '';

            let visibleAvailable = 0;
            let visibleUnavailable = 0;

            cards.forEach(function (card) {
                const isAvailable = card.tagName === 'LABEL';
                const hay = card.dataset.browseName || '';
                const matchesSearch = !q || hay.includes(q);
                const matchesTag = !tag || (card.dataset.tagName || '') === tag.toLowerCase();
                const show = matchesSearch && matchesTag;

                card.style.display = show ? '' : 'none';
                if (show) {
                    if (isAvailable) visibleAvailable++;
                    else visibleUnavailable++;
                }
            });

            // Deselect a hidden item so it can't be submitted invisibly
            const checked = document.querySelector('input[name="item_id"]:checked');
            if (checked) {
                const cardEl = checked.closest('.item-select-card');
                if (cardEl && cardEl.style.display === 'none') {
                    checked.checked = false;
                    checked.dispatchEvent(new Event('change'));
                }
            }

            // Update unavailable section
            const unavailSection = document.getElementById('unavailableSection');
            const unavailCount = document.getElementById('unavailableCount');
            if (unavailCount) unavailCount.textContent = visibleUnavailable;
            if (unavailSection) {
                if (q || tag) {
                    unavailSection.style.display = visibleUnavailable > 0 ? '' : 'none';
                    const detailsEl = unavailSection.querySelector('details');
                    if (detailsEl && visibleUnavailable > 0 && visibleAvailable === 0) {
                        detailsEl.open = true;
                    }
                } else {
                    unavailSection.style.display = '';
                }
            }

            // Empty state
            const emptyMsg = document.getElementById('browseEmptyMsg');
            if (emptyMsg) emptyMsg.style.display = (visibleAvailable === 0 && visibleUnavailable === 0) ? 'block' : 'none';
        }

        searchInput?.addEventListener('input', function () {
            clearTimeout(browseTimer);
            browseTimer = setTimeout(applyBrowseFilters, 120);
        });
        tagFilter?.addEventListener('change', applyBrowseFilters);

        // ==========================================
        // DYNAMIC CHIP LOGIC
        // ==========================================
        const purposeHidden = document.getElementById('purposeHidden');
        const purposeOther = document.getElementById('purposeOther');
        const otherContainer = document.getElementById('otherPurposeContainer');
        const chips = document.querySelectorAll('.purpose-chip');

        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                const val = this.dataset.value;

                if (val === 'Other') {
                    otherContainer.style.display = 'block';
                    purposeHidden.removeAttribute('name');
                    purposeOther.setAttribute('name', 'purpose');
                    purposeOther.setAttribute('required', 'true');
                } else {
                    otherContainer.style.display = 'none';
                    purposeHidden.setAttribute('name', 'purpose');
                    purposeHidden.value = val;
                    purposeOther.removeAttribute('name');
                    purposeOther.removeAttribute('required');
                    purposeOther.value = ''; 
                }
            });
        });

        // ==========================================
        // ERROR RECOVERY 
        // ==========================================
        let hasError = false;
        @if($errors->any() || session('error'))
            hasError = true;
        @endif

        if (hasError) {
            step1.style.display = 'none';
            step2.style.display = 'block';
            
            // Advance Progression Bar visually via classes
            document.getElementById('progress-fill').style.width = '33.33%';
            indicator1.classList.remove('active');
            indicator1.classList.add('completed');
            circle1.innerHTML = '<i class="bi bi-check-lg"></i>';
            label1.style.color = 'var(--text-secondary)';
            label1.style.fontWeight = '600';
            
            indicator2.classList.add('active');
            label2.style.color = 'var(--text-primary)';
            label2.style.fontWeight = '700';

            const checkedItem = document.querySelector('input[name="item_id"]:checked');
            if (checkedItem) {
                document.getElementById('review-item-name').innerText = checkedItem.dataset.name;
                document.getElementById('review-property-tag').innerText = checkedItem.dataset.propertyTag;
                document.getElementById('review-classification').innerText = checkedItem.dataset.classification;
                document.getElementById('review-serial').innerText = checkedItem.dataset.serial;
                document.getElementById('review-personnel').innerText = checkedItem.dataset.personnel;
                document.getElementById('review-date').innerText = checkedItem.dataset.date;
            }
        }

        // ==========================================
        // ITEM SELECTION
        // ==========================================
        const stickyBarHint = document.getElementById('stickyBarHint');
        itemRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                cards.forEach(c => {
                    c.style.borderColor = 'var(--border-color)';
                    c.style.backgroundColor = 'var(--bg-surface)';
                });
                if (this.checked) {
                    this.closest('.item-select-card').style.borderColor = 'var(--accent-color)';
                    this.closest('.item-select-card').style.backgroundColor = 'var(--accent-bg)';
                    btnContinue.classList.remove('disabled');
                    btnContinue.disabled = false;
                    if (stickyBarHint) stickyBarHint.style.opacity = '0';
                    // Scroll sticky bar into view so user sees the enabled button
                    setTimeout(function() {
                        document.getElementById('stickyBottomBar')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 150);
                }
            });
        });

        if (!hasError) {
            const checkedOnLoad = document.querySelector('input[name="item_id"]:checked');
            if(checkedOnLoad) {
                checkedOnLoad.dispatchEvent(new Event('change'));
            }
        }

        // ==========================================
        // CONTINUE & BACK NAVIGATION
        // ==========================================
        btnContinue.addEventListener('click', function() {
            const selectedItem = document.querySelector('input[name="item_id"]:checked');
            if (selectedItem) {
                document.getElementById('review-item-name').innerText = selectedItem.dataset.name;
                document.getElementById('review-property-tag').innerText = selectedItem.dataset.propertyTag;
                document.getElementById('review-classification').innerText = selectedItem.dataset.classification;
                document.getElementById('review-serial').innerText = selectedItem.dataset.serial;
                document.getElementById('review-personnel').innerText = selectedItem.dataset.personnel;
                document.getElementById('review-date').innerText = selectedItem.dataset.date;
                
                step1.style.display = 'none';
                step2.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                document.getElementById('progress-fill').style.width = '33.33%';
                indicator1.classList.remove('active');
                indicator1.classList.add('completed');
                circle1.innerHTML = '<i class="bi bi-check-lg"></i>';
                label1.style.color = 'var(--text-secondary)';
                label1.style.fontWeight = '600';
                
                indicator2.classList.add('active');
                label2.style.color = 'var(--text-primary)';
                label2.style.fontWeight = '700';
            }
        });

        btnBack.addEventListener('click', function() {
            step2.style.display = 'none';
            step1.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
            // Restore the sticky bar hint when going back
            if (stickyBarHint) stickyBarHint.style.opacity = '1';
            
            // Revert Progression Bar
            document.getElementById('progress-fill').style.width = '0%';
            indicator2.classList.remove('active');
            label2.style.color = 'var(--text-secondary)';
            label2.style.fontWeight = '600';
            
            indicator1.classList.remove('completed');
            indicator1.classList.add('active');
            circle1.innerHTML = '1';
            label1.style.color = 'var(--text-primary)';
            label1.style.fontWeight = '700';
        });

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

        // ==========================================
        // PIN VERIFICATION — SUBMIT REQUEST
        // ==========================================
        const btnSubmitPin = document.getElementById('btn-submit-pin');
        if (btnSubmitPin) {
            btnSubmitPin.addEventListener('click', function() {
                const activeChip = document.querySelector('.purpose-chip.active');
                const purposeOther = document.getElementById('purposeOther');
                
                const reasonErr = document.getElementById('reason-err');
                
                let isValid = true;
                
                // Reset display
                reasonErr.style.display = 'none';
                if (purposeOther) purposeOther.classList.remove('is-invalid');
                
                // Validate Reason
                if (!activeChip) {
                    reasonErr.style.display = 'block';
                    isValid = false;
                } else if (activeChip.dataset.value === 'Other' && (!purposeOther || !purposeOther.value.trim())) {
                    reasonErr.style.display = 'block';
                    if (purposeOther) purposeOther.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    reasonErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                
                openPinVerifyModal();
            });
        }
    });

    // ──────────────────────────────────────────────────────
    // PIN VERIFY MODAL LOGIC
    // ──────────────────────────────────────────────────────
    let _verifyPin = '';
    let _lockoutTimer = null;
    let _lockoutEnd = null;

    function openPinVerifyModal() {
        const overlay = document.getElementById('pinVerifyOverlay');
        if (overlay.dataset.busy === '1') return; // already submitting — ignore re-taps
        _verifyPin = '';
        updateVerifyDots('');
        document.getElementById('pin-verify-err').textContent = '';

        // If still locked (timer was running or persisted), resume lockout state immediately
        if (_lockoutEnd && _lockoutEnd > Date.now()) {
            showLockoutBanner('15:00'); // will be overwritten by tick immediately
            startLockoutCountdown(_lockoutEnd);
        } else {
            hideLockoutBanner();
        }

        overlay.style.display = 'flex';
    }

    function closeVerifyModal() {
        const overlay = document.getElementById('pinVerifyOverlay');
        if (overlay && overlay.dataset.busy === '1') return; // cannot cancel mid-submission
        overlay.style.display = 'none';
        _verifyPin = '';
        updateVerifyDots('');
        // Stop the tick interval but KEEP _lockoutEnd so re-opening detects the active lock
        if (_lockoutTimer) { clearInterval(_lockoutTimer); _lockoutTimer = null; }
    }

    function updateVerifyDots(val) {
        for (let i = 0; i < 4; i++) {
            const d = document.getElementById('vd' + i);
            if (!d) continue;
            if (i < val.length) { d.classList.add('filled'); d.classList.remove('error'); }
            else { d.classList.remove('filled', 'error'); }
        }
    }

    function flashVerifyError() {
        for (let i = 0; i < 4; i++) {
            const d = document.getElementById('vd' + i);
            if (d) { d.classList.add('error'); setTimeout(() => d.classList.remove('error', 'filled'), 500); }
        }
    }

    function showNumpad(visible) {
        const numpad = document.getElementById('pin-verify-numpad');
        if (numpad) numpad.style.display = visible ? 'grid' : 'none';
    }

    function showLockoutBanner(timeStr) {
        const banner = document.getElementById('pin-lockout-banner');
        const countdown = document.getElementById('pin-lockout-countdown');
        const normalContent = document.getElementById('pin-normal-content');
        if (banner) banner.style.display = 'block';
        if (normalContent) normalContent.style.display = 'none';
        if (countdown) countdown.textContent = timeStr;
    }

    function hideLockoutBanner() {
        const banner = document.getElementById('pin-lockout-banner');
        const normalContent = document.getElementById('pin-normal-content');
        if (banner) banner.style.display = 'none';
        if (normalContent) normalContent.style.display = 'block';
    }

    function startLockoutCountdown(endTimestamp) {
        clearLockoutTimer();
        _lockoutEnd = endTimestamp;

        function tick() {
            const now = Date.now();
            const remaining = Math.max(0, Math.ceil((_lockoutEnd - now) / 1000));
            if (remaining <= 0) {
                clearLockoutTimer();
                _lockoutEnd = null;
                hideLockoutBanner();
                document.getElementById('pin-verify-err').textContent = '';
                updateVerifyDots('');
                _verifyPin = '';
                return;
            }
            const mins = String(Math.floor(remaining / 60)).padStart(2, '0');
            const secs = String(remaining % 60).padStart(2, '0');
            showLockoutBanner(`${mins}:${secs}`);
        }

        tick();
        _lockoutTimer = setInterval(tick, 1000);
    }

    function clearLockoutTimer() {
        if (_lockoutTimer) { clearInterval(_lockoutTimer); _lockoutTimer = null; }
        _lockoutEnd = null;
    }

    window.verifyPinPad = function(digit) {
        if (_verifyPin.length >= 4) return;
        _verifyPin += digit;
        updateVerifyDots(_verifyPin);
        if (_verifyPin.length === 4) {
            setTimeout(() => submitVerifyPin(), 300);
        }
    };

    window.verifyPinDel = function() {
        _verifyPin = _verifyPin.slice(0, -1);
        updateVerifyDots(_verifyPin);
    };

    async function submitVerifyPin() {
        const errEl = document.getElementById('pin-verify-err');
        try {
            const res = await fetch('{{ route("auth.verify-pin") }}', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ pin: _verifyPin })
            });
            const data = await res.json();
            if (res.ok) {
                // ── Lock the overlay in a busy state while the form posts.
                // Closing it early let users re-tap Submit and create duplicates.
                pinOverlayBusy();
                document.getElementById('requestForm').submit();
                // Do NOT close or reset anything — navigation takes over from here.
            } else {
                if (res.status === 423) {
                    // Lockout: parse time from message or use default 15 mins
                    const lockoutDuration = 15 * 60 * 1000; // 15 minutes
                    _lockoutEnd = Date.now() + lockoutDuration;
                    startLockoutCountdown(_lockoutEnd);
                    errEl.textContent = data.message || 'Too many attempts. Locked for 15 minutes.';
                } else {
                    flashVerifyError();
                    errEl.textContent = data.message || 'Incorrect PIN.';
                    _verifyPin = '';
                    setTimeout(() => { updateVerifyDots(''); errEl.textContent = ''; }, 1000);
                }
            }
        } catch (e) {
            errEl.textContent = 'Network error. Please try again.';
            _verifyPin = '';
            updateVerifyDots('');
        }
    }

    function pinOverlayBusy() {
        const overlay = document.getElementById('pinVerifyOverlay');
        if (!overlay) return;
        overlay.dataset.busy = '1';
        const pad = document.getElementById('pin-verify-numpad');
        if (pad) pad.style.display = 'none';
        const dotsRow = overlay.querySelector('.pin-dots-row');
        if (dotsRow) dotsRow.style.opacity = '0.35';
        const title = overlay.querySelector('.pin-card-title');
        if (title) title.textContent = 'Submitting request…';
        const sub = overlay.querySelector('.pin-card-sub');
        if (sub) sub.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Hold on — sending your request to the admin.';
        const cancelBtn = overlay.querySelector('.pin-key[onclick*="closeVerifyModal"]');
        if (cancelBtn) cancelBtn.style.display = 'none';
    }
</script>

<!-- PIN VERIFY OVERLAY (appended to body) -->
<div class="pin-overlay" id="pinVerifyOverlay" style="display: none;">
    <div class="pin-card">
        <!-- Lockout Banner -->
        <div id="pin-lockout-banner" style="display: none; padding: 20px; text-align: center;">
            <div class="pin-card-icon" style="background: linear-gradient(135deg, var(--accent-red), #b91c1c);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
            </div>
            <div class="pin-card-title" style="color: var(--accent-red);">Temporarily Locked</div>
            <div class="pin-card-sub">Too many incorrect PIN attempts. Please try again in</div>
            <div style="font-size: 32px; font-weight: 800; color: var(--text-primary); letter-spacing: 4px; margin: 12px 0;" id="pin-lockout-countdown">15:00</div>
            <button class="pin-key" onclick="closeVerifyModal()" style="width: 100%; background: var(--bg-main); color: var(--text-primary); border-radius: 10px; height: 44px; font-size: 15px; font-weight: 600; margin-top: 12px;">Close</button>
        </div>

        <!-- Normal PIN pad -->
        <div id="pin-normal-content">
            <div class="pin-card-icon" style="background: linear-gradient(135deg, var(--accent-blue), #072d5a);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="pin-card-title">Enter PIN</div>
            <div class="pin-card-sub">Enter your 4-digit security PIN to confirm this request.</div>
            <div class="pin-dots-row">
                <div class="pin-dot" id="vd0"></div>
                <div class="pin-dot" id="vd1"></div>
                <div class="pin-dot" id="vd2"></div>
                <div class="pin-dot" id="vd3"></div>
            </div>
            <div class="pin-numpad" id="pin-verify-numpad">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button class="pin-key" onclick="verifyPinPad('{{ $k }}')">{{ $k }}</button>
                @endforeach
                <button class="pin-key pin-key-empty"></button>
                <button class="pin-key" onclick="verifyPinPad('0')">0</button>
                <button class="pin-key pin-key-del" onclick="verifyPinDel()">⌫</button>
            </div>
            <div class="pin-error-msg" id="pin-verify-err"></div>
            <button class="pin-key" onclick="closeVerifyModal()" style="width: 100%; background: none; color: var(--text-secondary); font-size: 13px; height: auto; padding: 8px 0; border: none;">Cancel</button>
        </div>
    </div>
</div>

<style>
/* PIN Overlay Style (Same as admin layout) */
.pin-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.pin-card {
    background: var(--bg-surface, #ffffff);
    border-radius: 28px;
    padding: 44px 36px 36px;
    width: 350px;
    max-width: 95vw;
    text-align: center;
    box-shadow: 0 24px 70px rgba(20, 28, 46, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255,255,255,0.7);
    animation: pinSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes pinSlideUp {
    from { transform: translateY(40px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.pin-card-icon {
    width: 68px; height: 68px;
    background: linear-gradient(135deg, var(--accent-green), #059669);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    transition: transform 0.3s ease;
}
.pin-card-icon svg { width: 28px; height: 28px; fill: none; stroke: #fff; stroke-width: 2; }
.pin-card-title {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: var(--text-primary, #111827);
    margin-bottom: 8px;
}
.pin-card-sub {
    font-size: 13.5px;
    color: var(--text-secondary, #6b7280);
    line-height: 1.5;
    margin-bottom: 28px;
}
.pin-dots-row {
    display: flex;
    justify-content: center;
    gap: 22px;
    margin: 20px 0 28px;
}
.pin-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    background: transparent;
    border: 2px solid var(--border-color);
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pin-dot.filled {
    background: var(--accent-color);
    border-color: var(--accent-color);
    transform: scale(1.3);
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.6);
}
.pin-dot.error {
    background: var(--accent-red);
    border-color: var(--accent-red);
    box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
    animation: pinShake 0.4s ease;
}
@keyframes pinShake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}
.pin-numpad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px 20px;
    margin: 24px 0 20px;
    justify-items: center;
}
.pin-key {
    background: var(--bg-surface-hover);
    border: 1px solid var(--border-color);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 22px;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    -webkit-tap-highlight-color: transparent;
}
.pin-key:hover {
    background: var(--bg-surface-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}
.pin-key:active {
    transform: scale(0.92) translateY(0);
    background: var(--border-color);
}
.pin-key.pin-key-del {
    font-size: 18px;
    color: var(--text-secondary);
    background: transparent;
    box-shadow: none;
    border-color: transparent;
}
.pin-key.pin-key-empty {
    background: transparent;
    box-shadow: none;
    border-color: transparent;
    cursor: default;
    pointer-events: none;
}
.pin-error-msg { font-size: 12px; color: var(--accent-red); min-height: 18px; margin-top: 6px; }

[data-theme="dark"] .pin-card {
    background: var(--bg-surface);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.5);
}
[data-theme="dark"] .pin-card-title { color: var(--text-primary); }
[data-theme="dark"] .pin-card-sub   { color: var(--text-secondary); }
[data-theme="dark"] .pin-key {
    background: var(--bg-surface-hover);
    color: var(--text-primary);
    border-color: rgba(255, 255, 255, 0.02);
}
[data-theme="dark"] .pin-key:hover { background: var(--border-color); }
[data-theme="dark"] .pin-key:active { background: var(--text-secondary); }
[data-theme="dark"] .pin-dot { border-color: var(--text-secondary); }
[data-theme="dark"] .pin-key.pin-key-del { color: var(--text-secondary); }
</style>
@endsection