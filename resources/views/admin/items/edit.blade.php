@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>Edit Asset Details</h1>
        <p class="form-label">Update information for Property Tag: <strong class="text-white">{{ $item->property_tag }}</strong></p>
    </div>

    <div class="row gx-4">
        
        <div class="col-lg-7 mb-4">
            <div class="panel-card p-4">
                <form id="editAssetForm" action="/admin/inventory/{{ $item->id }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Classification & Sourcing</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Asset Category</label>
                            <select id="category_id" name="category_id" class="form-select custom-input" required>
                                <option value="" disabled>Select Classification...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" data-ppe="{{ $cat->ppe_sub_major }}" data-gl="{{ $cat->gl_ledger_acct }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>
                                        [{{ $cat->ppe_sub_major }}-{{ $cat->gl_ledger_acct }}] - {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Supplier / Fund Source</label>
                            <select id="supplier_id" name="supplier_id" class="form-select custom-input" required>
                                <option value="" disabled>Select Supplier...</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ $item->supplier_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Asset Details</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: var(--text-secondary);">Item Description</label>
                        <input type="text" id="name_brand_model" name="name_brand_model" class="form-control custom-input" value="{{ old('name_brand_model', $item->name) }}" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Serial Number</label>
                            <input type="text" id="serial_number" name="serial_number" class="form-control custom-input" value="{{ old('serial_number', $item->serial_number) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Accountable Personnel</label>
                            <input type="text" id="accountable_personnel" name="accountable_personnel" class="form-control custom-input" value="{{ old('accountable_personnel', $item->accountable_personnel) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Current Status</label>
                            <select id="status" name="status" class="form-select custom-input" required>
                                <option value="available" {{ $item->status == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="borrowed" {{ $item->status == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                <option value="maintenance" {{ $item->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="disposed" {{ $item->status == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Acquisition & Location</h5>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Acquisition Date</label>
                            <input type="date" id="acquisition_date" name="acquisition_date" class="form-control custom-input" value="{{ old('acquisition_date', \Carbon\Carbon::parse($item->acquisition_date)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Acquisition Cost (₱)</label>
                            <input type="number" step="0.01" id="acquisition_cost" name="acquisition_cost" class="form-control custom-input" value="{{ old('acquisition_cost', $item->acquisition_cost) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Location</label>
                            <select id="location_id" name="location_id" class="form-select custom-input" required>
                                <option value="" disabled>Select Location...</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" data-code="{{ $loc->code }}" {{ $item->location_id == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4 sticky-top" style="top: 24px;">
                <h5 class="fw-bold mb-3" style="color: var(--text-primary);">Live Tag Preview</h5>
                
                <div class="tag-container-preview mb-4">
                    <div class="tag-header" id="preview-header">Supplier: Loading...</div>
                    <div class="tag-body">
                        <div class="left-panel">
                            <img src="{{ asset('/images/deped-logo.png') }}" alt="DepEd Logo" style="width: 80%; height: auto; margin-bottom: 10px;">
                            <div class="barcode-placeholder text-center mt-auto">
                                <svg id="preview-barcode" style="width: 100%; height: auto; max-height: 40px; object-fit: contain;"></svg>
                                <br>
                                <small style="font-weight:normal; font-size: 10px;" id="preview-barcode-text">{{ $item->property_tag }}</small>
                            </div>
                        </div>
                        <div class="right-panel">
                            <table class="preview-table">
                                <tr><td>Property Number</td><td><strong>{{ $item->property_tag }}</strong></td></tr>
                                <tr><td>Asset Classification</td><td id="preview-category">--</td></tr>
                                <tr><td>Item/Brand/Model</td><td id="preview-item">{{ $item->name }}</td></tr>
                                <tr><td>Serial Number</td><td id="preview-serial">{{ $item->serial_number ?? 'N/A' }}</td></tr>
                                <tr><td>Acquisition Cost</td><td id="preview-cost">₱{{ number_format($item->acquisition_cost, 2) }}</td></tr>
                                <tr><td>Acquisition Date</td><td id="preview-date">{{ \Carbon\Carbon::parse($item->acquisition_date)->format('M d, Y') }}</td></tr>
                                <tr><td>Accountable Personnel</td><td id="preview-person">{{ $item->accountable_personnel }}</td></tr>
                                <tr><td>Validation Signature</td><td><br></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="tag-footer">TAMPERING <span style="border-bottom: 1px solid #f8aba6;">OF</span> THIS PROPERTY TAG IS PUNISHABLE BY LAW</div>
                </div>

                <div id="action-state-edit">
                    <button type="button" id="saveChangesBtn" class="btn w-100 fw-bold py-2" style="background-color: var(--accent-blue); color: #fff; border-radius: 8px;">
                        <i class="bi bi-save-fill me-2"></i> Save Changes
                    </button>
                    <a href="/admin/inventory" id="cancelBtn" class="btn btn-light w-100 mt-2 py-2 border fw-semibold">Cancel</a>
                </div>

                <div id="action-state-success" class="d-none">
                    <div class="alert alert-success text-center fw-bold py-2 mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i> Changes Saved!
                    </div>
                    
                    <button type="button" id="printBtn" data-print-url="/admin/inventory/{{ $item->id }}/print-tag" class="btn w-100 fw-bold py-2 mb-2" style="background-color: var(--text-primary); color: var(--bg-surface); border-radius: 8px;">
                        <i class="bi bi-printer-fill me-2"></i> Print Property Tag
                    </button>
                    
                    <a href="/admin/inventory" class="btn w-100 fw-bold py-2 border" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 8px;">
                        <i class="bi bi-check2-all me-2"></i> Finish & Go to Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-danger" id="cancelConfirmModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Unsaved Modifications!
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-3 text-muted" style="font-size: 15px; line-height: 1.5;">
                    You are currently in the middle of editing an asset. Leaving now will permanently discard any changes you've made. Are you sure you want to proceed?
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-light border fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 14px;">
                        No, Stay & Edit
                    </button>
                    <button type="button" id="modalConfirmDiscardBtn" class="btn btn-danger fw-bold px-3 py-2" style="border-radius: 8px; font-size: 14px;">
                        Yes, Discard Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Same CSS as create.blade.php for the live preview */
.tag-container-preview { width: 100%; background: #ffffff; border: 2px solid #000; border-collapse: collapse; color: #000000; font-family: Arial, sans-serif; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
.tag-container-preview .tag-header { text-align: center; font-weight: bold; padding: 6px; border-bottom: 2px solid #000; background-color: #00FF00; color: #000; font-size: 13px; transition: background-color 0.3s ease; }
.tag-container-preview .tag-body { display: flex; align-items: stretch; }
.tag-container-preview .left-panel { width: 35%; border-right: 2px solid #000; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 10px 5px; overflow: hidden; }
.tag-container-preview .right-panel { width: 65%; display: flex; }
.tag-container-preview .deped-logo { font-size: 22px; font-weight: 900; text-align: center; line-height: 1.1; }
.tag-container-preview .barcode-placeholder { font-weight: bold; margin-top: auto; padding-bottom: 10px; text-align: center; font-size: 12px; }
.tag-container-preview table.preview-table { width: 100%; border-collapse: collapse; margin: 0; height: 100%; }
.tag-container-preview table.preview-table td { border-bottom: 1px solid #000; padding: 6px 8px; font-size: 11px; }
.tag-container-preview table.preview-table td:first-child { border-right: 1px solid #000; width: 40%; font-weight: 500; }
.tag-container-preview table.preview-table tr:last-child td { border-bottom: none; }
.tag-container-preview .tag-footer { text-align: center; font-size: 10px; padding: 4px; border-top: 2px solid #000; }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
if (typeof window.assetFormIsDirty === 'undefined') { window.assetFormIsDirty = false; }

function initializeAssetEditPage() {
    const form = document.getElementById('editAssetForm');
    const saveBtn = document.getElementById('saveChangesBtn');
    if (!form) return; 

    window.assetFormIsDirty = false; // Reset on load

    const inputCat = document.getElementById('category_id');
    const inputDate = document.getElementById('acquisition_date');
    const inputName = document.getElementById('name_brand_model');
    const inputPerson = document.getElementById('accountable_personnel');
    const inputSerial = document.getElementById('serial_number');
    const inputCost = document.getElementById('acquisition_cost');
    const inputSupplier = document.getElementById('supplier_id');

    function updatePreview() {
        // Update Category Text
        if(inputCat && inputCat.selectedIndex > 0) {
            let fullText = inputCat.options[inputCat.selectedIndex].text;
            document.getElementById('preview-category').innerText = fullText.includes('] - ') ? fullText.split('] - ')[1] : fullText;
        }

        // Update Supplier Colors dynamically
        if(inputSupplier && inputSupplier.selectedIndex > 0) {
            let supName = inputSupplier.options[inputSupplier.selectedIndex].text;
            let header = document.getElementById('preview-header');
            if (header) {
                header.innerText = `${supName}`;
                let color = '#FFFF00'; 
                let upperName = supName.toUpperCase();
                if(upperName.includes('LGU')) color = '#00FF00';
                else if(upperName.includes('MOOE')) color = '#00CCFF';
                else if(upperName.includes('DONATION') || upperName.includes('DONATED')) color = '#FF99CC';
                header.style.backgroundColor = color;
            }
        }

        // Update Dates & Costs
        if(inputDate && inputDate.value) {
            const d = new Date(inputDate.value);
            document.getElementById('preview-date').innerText = !isNaN(d) ? d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '--/--/----';
        }

        if (inputCost) {
            let costVal = parseFloat(inputCost.value);
            document.getElementById('preview-cost').innerText = isNaN(costVal) ? '₱0.00' : `₱${costVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }

        // Update Text Fields
        if (inputName && document.getElementById('preview-item')) document.getElementById('preview-item').innerText = inputName.value || '--';
        if (inputSerial && document.getElementById('preview-serial')) document.getElementById('preview-serial').innerText = inputSerial.value || 'N/A';
        if (inputPerson && document.getElementById('preview-person')) document.getElementById('preview-person').innerText = inputPerson.value || 'Unassigned';
    }

    // Bind listeners to mark form as dirty and update preview live
    if (form) {
        form.addEventListener('input', () => { window.assetFormIsDirty = true; updatePreview(); });
        form.addEventListener('change', () => { window.assetFormIsDirty = true; updatePreview(); });
    }

    // Initialize Barcode and Preview Values on Page Load
    if (typeof JsBarcode === "function" && document.getElementById('preview-barcode')) {
        JsBarcode("#preview-barcode", "{{ $item->property_tag }}", {
            format: "CODE128", lineColor: "#000", width: 1, height: 35, displayValue: false, margin: 0, background: "transparent"
        });
        updatePreview(); // Fires once to align colors and text with existing DB data
    }
    // AJAX Form Processor (Matches Create Page Workflow)
    if (saveBtn) {
        saveBtn.onclick = async function () {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            
            // FormData automatically grabs the @method('PUT') hidden field you have in your form!
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    alert("Server Error: " + (errorData.message || "Unknown error"));
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-save-fill me-2"></i> Save Changes';
                    return;
                }

                // If the server returns a successful response (either a redirect or a JSON success message)
                window.assetFormIsDirty = false; // Disable validation guards safely

                // Swap the UI Panels
                document.getElementById('action-state-edit').classList.add('d-none');
                document.getElementById('action-state-success').classList.remove('d-none');
                
                // Lock the form inputs so they can't be changed after saving
                const inputsList = [
                    inputCat, inputDate, inputName, inputPerson, 
                    inputSerial, inputCost, inputSupplier, 
                    document.getElementById('status'), 
                    document.getElementById('location_id')
                ];
                inputsList.forEach(el => { if(el) el.disabled = true; });

                if (typeof showNotify === "function") {
                    try { showNotify("Asset updated successfully!", "success"); } catch (e) {}
                }
            } catch (error) {
                console.error(error);
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save-fill me-2"></i> Save Changes';
            }
        };
    }

    // Attach Print Button click logic
    if(document.getElementById('printBtn')) {
        document.getElementById('printBtn').onclick = function () {
            const targetUrl = this.dataset.printUrl;
            if (targetUrl) window.open(targetUrl, '_blank');
        };
    }
}

// Lifecycle Init Hooks
initializeAssetEditPage();
document.addEventListener('DOMContentLoaded', initializeAssetEditPage);
document.addEventListener('livewire:navigated', initializeAssetEditPage);
document.addEventListener('turbo:load', initializeAssetEditPage);


// --- ASYNCHRONOUS DELEGATED MODAL INTERCEPTOR SYSTEM (Identical to Create View) ---
if (!window.hasGlobalAssetGuardRegistered) {
    window.hasGlobalAssetGuardRegistered = true;
    window.pendingNavigationUrl = null;

    window.addEventListener('click', function (e) {
        if (e.target.closest('[data-bs-dismiss="modal"]') || e.target.classList.contains('btn-close')) {
            const elementModal = document.getElementById('cancelConfirmModal');
            if (elementModal && typeof bootstrap === 'undefined') {
                elementModal.classList.remove('show');
                elementModal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.getElementById('manual-modal-backdrop');
                if (backdrop) backdrop.remove();
                return;
            }
        }

        if (e.target.id === 'modalConfirmDiscardBtn') {
            window.assetFormIsDirty = false;
            window.location.href = window.pendingNavigationUrl || '/admin/inventory';
            return;
        }

        if (!window.assetFormIsDirty) return;

        const triggerElement = e.target.closest('a') || e.target.closest('[onclick]') || e.target.closest('[wire\\:click]') || e.target.closest('[x-on\\:click]');
        if (!triggerElement) return;

        if (triggerElement.getAttribute('target') === '_blank' || triggerElement.id === 'printBtn' || triggerElement.closest('#cancelConfirmModal')) return; 

        // THE VIP PASS: Ignores theme switchers and notification dropdowns
        const href = triggerElement.getAttribute('href');
        const onclickStr = triggerElement.getAttribute('onclick') || '';
        if (
            triggerElement.hasAttribute('data-bs-toggle') || 
            triggerElement.hasAttribute('data-toggle') ||    
            href === '#' || href === 'javascript:void(0)' ||                
            (!href && !onclickStr.includes('location'))      
        ) { return; }

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        window.pendingNavigationUrl = triggerElement.getAttribute('href') || '/admin/inventory';

        const elementModal = document.getElementById('cancelConfirmModal');
        if (elementModal) {
            if (typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(elementModal).show();
            } else {
                elementModal.classList.add('show');
                elementModal.style.display = 'block';
                document.body.classList.add('modal-open');
                if (!document.getElementById('manual-modal-backdrop')) {
                    let backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'manual-modal-backdrop';
                    document.body.appendChild(backdrop);
                }
            }
        }
    }, true); 

    window.addEventListener('beforeunload', function (e) {
        if (window.assetFormIsDirty) { e.preventDefault(); e.returnValue = ''; }
    });
    document.addEventListener('livewire:navigating', function (e) { if (window.assetFormIsDirty && !confirm("Discard unsaved asset records?")) e.preventDefault(); });
    document.addEventListener('turbo:before-visit', function (e) { if (window.assetFormIsDirty && !confirm("Discard unsaved asset records?")) e.preventDefault(); });
}
</script>
@endsection