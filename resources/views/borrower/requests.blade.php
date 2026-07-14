@extends('layouts.borrower')

@section('content')
<style>
    .purpose-chip {
        font-size: 13px;
        font-weight: 600;
        padding: 10px 18px;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        background: var(--bg-surface);
        border-radius: 20px;
        transition: all 0.2s ease;
    }
    .purpose-chip:hover {
        background: var(--bg-main);
    }
    .purpose-chip.active {
        background: var(--accent-color) !important;
        color: var(--btn-text) !important;
        border-color: var(--accent-color) !important;
    }

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

<div class="dashboard-wrapper" style="padding-top: 16px;">
    
    <div class="welcome-header mb-4">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary);">Create Request</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 16px;">
            <div class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Submission Failed:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 16px;">
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
        <div class="activity-card text-center shadow-sm" style="max-width: 450px; margin: 40px auto; padding: 40px 24px; border-radius: 20px;">
            <div style="width: 72px; height: 72px; background: var(--bg-main); color: var(--text-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 24px auto;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="fw-bold mb-2">Request Submitted!</h2>
            
            <div style="background: var(--bg-main); padding: 24px; border-radius: 16px; margin: 24px 0;">
                <p class="fw-bold text-xs text-muted mb-3 text-uppercase letter-spacing-1">YOUR REQUEST QR</p>
                <img src="https://quickchart.io/qr?size=180&text={{ urlencode(session('qr_code_success')) }}" alt="QR Code" style="border-radius: 8px; border: 4px solid var(--bg-surface); box-shadow: var(--shadow-sm);">
                <p class="text-sm text-muted mt-3 mb-0" style="max-width: 200px; margin: 0 auto;">Show this QR code to the admin.</p>
            </div>

            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('borrower.dashboard') }}" class="btn" style="border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary); font-weight: 600; border-radius: 12px; padding: 10px 20px;">Return to Home</a>
                <a href="{{ route('borrower.history') }}" class="btn-primary-action text-decoration-none" style="border-radius: 12px; padding: 10px 20px;">View My Borrows</a>
            </div>
        </div>

    @else
    <form action="{{ route('borrower.request.submit') }}" method="POST" id="requestForm" novalidate>
            @csrf
            <input type="hidden" name="qr_code_hash" id="qrCodeHashInput" value="{{ $qrCodeHash }}">
            
            <div id="step-1-section">
                <div class="d-flex justify-content-between align-items-center mb-4 gap-2">
                    <div style="position: relative; width: 100%;">
                        <label for="searchInventoryInput" style="display: none;">Search inventory</label>
                        <i class="bi bi-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="text" id="searchInventoryInput" name="search" placeholder="Search inventory..." style="width: 100%; padding: 12px 16px 12px 42px; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-surface); color: var(--text-primary);">
                    </div>
                </div>

                <div class="activity-list mb-4">
                    @forelse($items ?? [] as $item)
                        @if($item->status !== 'available')
                            <div class="activity-row item-select-card" style="padding: 16px; border: 1.5px solid var(--text-secondary); border-radius: 12px; background: var(--bg-main); display: flex; align-items: center; gap: 16px; opacity: 0.85; cursor: not-allowed; user-select: none;">
                                <input type="radio" disabled style="width: 20px; height: 20px; opacity: 0.5;">
                                
                                <div class="item-icon" style="background: var(--bg-surface); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border: 1px solid var(--border-color);">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                
                                <div class="item-details flex-grow-1">
                                    <span class="item-name fs-6 text-muted" style="text-decoration: line-through;">{{ $item->name }}</span>
                                    <span class="item-meta text-muted">ID: {{ $item->property_tag ?? 'N/A' }} | {{ $item->category->name ?? 'General' }}</span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary px-2.5 py-1.5 rounded-pill" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        @if($item->status === 'borrowed')
                                            Borrowed
                                        @else
                                            Unavailable
                                        @endif
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
                                    <button type="button" class="unavailable-note-btn p-1" style="cursor: pointer; position: relative;">
                                        <i class="bi bi-chat-left-text-fill fs-5 text-danger"></i>
                                        <span class="unavailable-tooltip">
                                            {{ $reason }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <label class="activity-row item-select-card" for="item_radio_{{ $item->id }}" style="cursor: pointer; padding: 16px; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-surface); display: flex; align-items: center; gap: 16px; transition: 0.2s;">
                                
                                <input type="radio" name="item_id" id="item_radio_{{ $item->id }}" value="{{ $item->id }}" required 
                                    {{ old('item_id') == $item->id ? 'checked' : '' }} 
                                    style="width: 20px; height: 20px; accent-color: var(--accent-color);" 
                                    data-name="{{ $item->name }}" 
                                    data-property-tag="{{ $item->property_tag ?? 'N/A' }}"
                                    data-classification="{{ $item->category->name ?? 'Uncategorized' }}"
                                    data-serial="{{ $item->serial_number ?? 'N/A' }}"
                                    data-personnel="{{ $item->accountable_personnel ?? 'Unassigned' }}"
                                    data-date="{{ $item->acquisition_date ? \Carbon\Carbon::parse($item->acquisition_date)->format('M d, Y') : '--/--/----' }}"
                                    data-cost="{{ $item->acquisition_cost ? '₱' . number_format($item->acquisition_cost, 2) : '₱0.00' }}">
                                
                                <div class="item-icon" style="background: var(--bg-main); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                
                                <div class="item-details flex-grow-1">
                                    <span class="item-name fs-6">{{ $item->name }}</span>
                                    <span class="item-meta">ID: {{ $item->property_tag ?? 'N/A' }} | {{ $item->category->name ?? 'General' }}</span>
                                </div>
                            </label>
                        @endif
                    @empty
                        <div class="text-center text-muted py-5" style="background: var(--bg-surface); border-radius: 16px; border: 1px dashed var(--border-color);">
                            <i class="bi bi-inbox fs-1 mb-2 d-block text-secondary"></i>
                            No available items found in inventory.
                        </div>
                    @endforelse
                </div>

                <div class="text-center mt-4">
                    <button type="button" id="btn-continue" class="btn w-100 py-3 fs-6 disabled" style="border-radius: 12px; font-weight: 600; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-secondary);" disabled>Continue</button>
                </div>
            </div>

            <div id="step-2-section" style="display: none; background: var(--bg-surface); border-radius: 20px; border: 1px solid var(--border-color); padding: 24px;">
                
                <h3 class="fw-bold mb-4 fs-5" style="color: var(--text-primary);">Review Selected Items</h3>
                
                <div class="mb-4 p-4" style="background: var(--bg-main); border-radius: 16px; border: 1px solid var(--border-color);">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom: 1px dashed var(--border-color);">
                        <div class="item-icon" style="background: var(--bg-surface); border: 1px solid var(--border-color); width: 54px; height: 54px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-seam text-muted fs-3"></i>
                        </div>
                        <div class="item-details flex-grow-1">
                            <span class="d-block text-muted mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Item / Brand / Model</span>
                            <span class="item-name fw-bold fs-5" id="review-item-name" style="color: var(--text-primary); line-height: 1.2;">Item Name</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <span class="d-block text-muted mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Asset Classification</span>
                            <span class="badge" id="review-classification" style="background: var(--border-color); color: var(--text-secondary); font-weight: 600; font-size: 12px;">--</span>
                        </div>
                    </div>

                    <div class="d-md-none mb-3">
                        <button class="btn btn-sm w-100 fw-bold d-flex align-items-center justify-content-between px-3" type="button" data-bs-toggle="collapse" data-bs-target="#extendedDetails" aria-expanded="false" style="border: 1px dashed var(--border-color); background: var(--bg-surface); color: var(--text-primary); border-radius: 8px; padding-top: 10px; padding-bottom: 10px;" onclick="this.querySelector('i').classList.toggle('bi-chevron-up'); this.querySelector('i').classList.toggle('bi-chevron-down');">
                            <span>View More Details</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>

                    <div class="collapse d-md-block" id="extendedDetails">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Property Number</span>
                                <span class="fw-bold text-break" id="review-property-tag" style="font-size: 13px; color: var(--text-primary); font-family: monospace;">YYYY-XX-XX-XXXX</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Serial Number</span>
                                <span class="fw-bold text-break" id="review-serial" style="font-size: 13px; color: var(--text-primary); font-family: monospace;">N/A</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Accountable Personnel</span>
                                <span class="fw-bold" id="review-personnel" style="font-size: 13px; color: var(--text-primary);">Unassigned</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="d-block text-muted mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Acquisition Date</span>
                                <span class="fw-bold" id="review-date" style="font-size: 13px; color: var(--text-primary);">--/--/----</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold" style="font-size: 14px; color: var(--text-primary);">Reason for Request</label>
                    
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

                    <div class="d-flex flex-wrap gap-2 mb-3" id="purposeChips">
                        @foreach($commonReasons as $reason)
                            <button type="button" class="btn purpose-chip {{ $oldPurpose === $reason ? 'active' : '' }}" data-value="{{ $reason }}">
                                {{ str_replace([' / Lecture', ' / Activity', ' / Presentation', ' / Organization Meeting', ' / Repair'], '', $reason) }}
                            </button>
                        @endforeach
                        <button type="button" class="btn purpose-chip {{ $isOther ? 'active' : '' }}" data-value="Other">Other...</button>
                    </div>

                    <div id="otherPurposeContainer" style="display: {{ $isOther ? 'block' : 'none' }};">
                        <textarea id="purposeOther" name="{{ $isOther ? 'purpose' : '' }}" class="form-control" rows="3" placeholder="Please specify your exact reason..." style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; box-shadow: none;" {{ $isOther ? 'required' : '' }}>{{ $isOther ? $oldPurpose : '' }}</textarea>
                    </div>
                    <div class="lp-err-msg" id="reason-err" style="display: none; color: #ef4444; margin-top: 6px; font-size: 13px; font-weight: 500;">Please select or specify a reason for the request.</div>
                </div>

                <div class="mb-4">
                    <label for="neededUntilInput" class="form-label fw-bold d-flex align-items-center gap-2" style="font-size: 14px; color: var(--text-primary);">
                        <i class="bi bi-calendar"></i> Needed Until
                    </label>
                    <input type="date" id="neededUntilInput" name="needed_until" class="form-control" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: none;">
                    <div class="lp-err-msg" id="date-err" style="display: none; color: #ef4444; margin-top: 6px; font-size: 13px; font-weight: 500;">Please select the date you need this item until.</div>
                </div>

                <div class="mb-5 p-4 text-center" style="background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border-color);">
                    <h6 class="m-0 fw-bold mb-2" style="color: var(--text-primary);">Fast-Track Request</h6>
                    <p class="text-muted" style="font-size: 13px; margin-bottom: 16px;">This unique QR Code is ready to be scanned by an admin.</p>
                <img src="https://quickchart.io/qr?size=140&text={{ urlencode($qrCodeHash) }}" alt="QR Code" style="border-radius: 8px; border: 4px solid var(--bg-surface); box-shadow: var(--shadow-sm);">
                </div>

                <div class="d-flex gap-3">
                    <button type="button" id="btn-back" class="btn w-50" style="border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary); padding: 12px; border-radius: 12px; font-weight: 600;">Back</button>
                    <button type="button" id="btn-submit-pin" class="btn-primary-action w-50 justify-content-center" style="padding: 12px; border-radius: 12px; font-size: 16px; background: var(--accent-color) !important; color: var(--btn-text) !important;">Submit Request</button>
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
                    btnContinue.style.background = 'var(--accent-color)';
                    btnContinue.style.color = 'var(--btn-text)';
                    btnContinue.style.borderColor = 'var(--accent-color)';
                    btnContinue.disabled = false;
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
                
                // Advance Progression Bar
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
                const neededUntilInput = document.getElementById('neededUntilInput');
                
                const reasonErr = document.getElementById('reason-err');
                const dateErr = document.getElementById('date-err');
                
                let isValid = true;
                
                // Reset display
                reasonErr.style.display = 'none';
                dateErr.style.display = 'none';
                if (purposeOther) purposeOther.classList.remove('is-invalid');
                if (neededUntilInput) neededUntilInput.classList.remove('is-invalid');
                
                // Validate Reason
                if (!activeChip) {
                    reasonErr.style.display = 'block';
                    isValid = false;
                } else if (activeChip.dataset.value === 'Other' && (!purposeOther || !purposeOther.value.trim())) {
                    reasonErr.style.display = 'block';
                    if (purposeOther) purposeOther.classList.add('is-invalid');
                    isValid = false;
                }
                
                // Validate Needed Until Date
                if (!neededUntilInput || !neededUntilInput.value) {
                    dateErr.style.display = 'block';
                    if (neededUntilInput) neededUntilInput.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    // Scroll to the first error container
                    const firstErr = (!activeChip || (activeChip.dataset.value === 'Other' && (!purposeOther || !purposeOther.value.trim()))) ? reasonErr : dateErr;
                    firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

        document.getElementById('pinVerifyOverlay').style.display = 'flex';
    }

    function closeVerifyModal() {
        document.getElementById('pinVerifyOverlay').style.display = 'none';
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
        // Clear error message as soon as user starts typing again
        document.getElementById('pin-verify-err').textContent = '';
        _verifyPin += digit;
        updateVerifyDots(_verifyPin);
        if (_verifyPin.length === 4) {
            setTimeout(() => submitWithPin(), 200);
        }
    };

    window.verifyPinDel = function() {
        _verifyPin = _verifyPin.slice(0, -1);
        updateVerifyDots(_verifyPin);
    };

    async function submitWithPin() {
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
                // PIN is correct — close modal and submit the actual form
                clearLockoutTimer();
                document.getElementById('pinVerifyOverlay').style.display = 'none';
                const btn = document.getElementById('btn-submit-pin');
                if (btn) { btn.innerHTML = 'Submitting...'; btn.style.opacity = '0.7'; btn.style.pointerEvents = 'none'; }
                document.getElementById('requestForm').submit();
            } else {
                if (res.status === 423) {
                    // Lockout — extract remaining time from message and start countdown
                    flashVerifyError();
                    _verifyPin = '';
                    updateVerifyDots('');
                    errEl.textContent = '';
                    // Parse minutes:seconds from server message to start client countdown
                    // Server format: "...try again in MM minutes and SS seconds"
                    const matchMin = data.message.match(/(\d+)\s+minute/);
                    const matchSec = data.message.match(/(\d+)\s+second/);
                    const mins = matchMin ? parseInt(matchMin[1]) : 15;
                    const secs = matchSec ? parseInt(matchSec[1]) : 0;
                    const totalMs = (mins * 60 + secs) * 1000;
                    startLockoutCountdown(Date.now() + totalMs);
                } else {
                    // Normal incorrect PIN: shake, show error, clear dots — error stays until user types
                    flashVerifyError();
                    errEl.textContent = data.message || 'Incorrect PIN. Please try again.';
                    // Clear PIN dots so user can re-enter, but keep error message visible
                    setTimeout(() => { _verifyPin = ''; updateVerifyDots(''); }, 900);
                }
            }
        } catch(e) {
            errEl.textContent = 'Network error. Please try again.';
            _verifyPin = '';
            updateVerifyDots('');
        }
    }
</script>

{{-- PIN VERIFY OVERLAY --}}
<div id="pinVerifyOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); align-items:center; justify-content:center; z-index:99999;">
    <div class="pin-card" style="position: relative;">
        <button type="button" onclick="closeVerifyModal()" style="position: absolute; top: 18px; right: 18px; background: none; border: none; font-size: 18px; color: var(--text-secondary, #9ca3af); cursor: pointer; transition: color 0.15s ease; line-height: 1; z-index: 10;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">✕</button>

        {{-- LOCKOUT STATE (hidden by default) --}}
        <div id="pin-lockout-banner" style="display:none;">
            <div class="pin-card-icon" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="28" height="28">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="pin-card-title" style="color: #ef4444;">Temporarily Locked</div>
            <div class="pin-card-sub">Too many incorrect attempts.<br>Borrow request submission is locked.</div>
            <div id="pin-lockout-countdown">15:00</div>
            <p style="font-size: 12px; color: #9ca3af; margin: 0 0 20px;">Unlocks automatically when timer reaches 00:00.</p>
            <button type="button" onclick="closeVerifyModal()" style="background:none;border:none;color:#9ca3af;font-size:13px;width:100%;padding:8px 0;cursor:pointer;">Close</button>
        </div>

        {{-- NORMAL PIN STATE --}}
        <div id="pin-normal-content">
            <div class="pin-card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="28" height="28">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div class="pin-card-title">Confirm PIN</div>
            <div class="pin-card-sub">Enter your 4-digit PIN to submit this request.</div>
            <div class="pin-dots-row">
                <div class="pin-dot" id="vd0"></div>
                <div class="pin-dot" id="vd1"></div>
                <div class="pin-dot" id="vd2"></div>
                <div class="pin-dot" id="vd3"></div>
            </div>
            <div class="pin-numpad" id="pin-verify-numpad">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button type="button" class="pin-key" onclick="verifyPinPad('{{ $k }}')">{{ $k }}</button>
                @endforeach
                <button type="button" class="pin-key pin-key-empty"></button>
                <button type="button" class="pin-key" onclick="verifyPinPad('0')">0</button>
                <button type="button" class="pin-key pin-key-del" onclick="verifyPinDel()">⌫</button>
            </div>
            <div class="pin-error-msg" id="pin-verify-err"></div>
            <button type="button" onclick="closeVerifyModal()" style="background:none;border:none;color:#9ca3af;font-size:13px;width:100%;padding:8px 0;cursor:pointer;margin-top:4px;">Cancel</button>
        </div>

    </div>
</div>

<style>
/* Premium PIN Modal Styles */
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
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    transition: transform 0.3s ease;
}
.pin-card-icon:hover {
    transform: translateY(-4px) rotate(5deg);
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
    border: 2px solid #d1d5db;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pin-dot.filled {
    background: #10b981;
    border-color: #10b981;
    transform: scale(1.3);
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.6);
}
.pin-dot.error {
    background: #ef4444;
    border-color: #ef4444;
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
    background: #f3f4f6;
    border: 1px solid rgba(0, 0, 0, 0.02);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 22px;
    font-weight: 600;
    color: #1f2937;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    -webkit-tap-highlight-color: transparent;
}
.pin-key:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}
.pin-key:active {
    transform: scale(0.92) translateY(0);
    background: #d1d5db;
}
.pin-key.pin-key-del {
    font-size: 18px;
    color: #4b5563;
    background: transparent;
    box-shadow: none;
    border-color: transparent;
}
.pin-key.pin-key-del:hover {
    background: rgba(0,0,0,0.04);
}
.pin-key.pin-key-empty {
    background: transparent;
    box-shadow: none;
    border-color: transparent;
    cursor: default;
    pointer-events: none;
}
.pin-error-msg { font-size: 12px; color: #ef4444; min-height: 18px; margin-top: 6px; }

[data-theme="dark"] .pin-card {
    background: #141c2e;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.5);
}
[data-theme="dark"] .pin-card-title { color: #f9fafb; }
[data-theme="dark"] .pin-card-sub   { color: #9ca3af; }
[data-theme="dark"] .pin-key {
    background: #1f2937;
    color: #f3f4f6;
    border-color: rgba(255, 255, 255, 0.02);
}
[data-theme="dark"] .pin-key:hover {
    background: #374151;
}
[data-theme="dark"] .pin-key:active {
    background: #4b5563;
}
[data-theme="dark"] .pin-dot { border-color: #4b5563; }
[data-theme="dark"] .pin-key.pin-key-del {
    color: #9ca3af;
    background: transparent;
}
[data-theme="dark"] .pin-key.pin-key-del:hover {
    background: rgba(255,255,255,0.04);
}

/* Lockout countdown timer */
#pin-lockout-countdown {
    font-size: 42px;
    font-weight: 800;
    letter-spacing: -1px;
    color: #ef4444;
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
    margin: 8px 0 16px;
    text-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
    animation: pulseRed 1.5s ease-in-out infinite;
}
@keyframes pulseRed {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
[data-theme="dark"] #pin-lockout-countdown {
    color: #f87171;
    text-shadow: 0 0 20px rgba(248, 113, 113, 0.4);
}
</style>
@endsection