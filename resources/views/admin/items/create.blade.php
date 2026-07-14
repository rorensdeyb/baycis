@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>Register New Asset</h1>
        <p class="form-label">Fill out the details to auto-generate an official DepEd Property Tag.</p>
    </div>

    <div class="row gx-4">
        
        <div class="col-lg-7 mb-4">
            <div class="panel-card p-4">
                <form id="createAssetForm" action="/admin/inventory" method="POST">
                    @csrf

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Classification & Sourcing</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Asset Category</label>
                            <select id="category_id" name="category_id" class="form-select custom-input" required>
                                <option value="" disabled selected>Select Classification...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" data-ppe="{{ $cat->ppe_sub_major }}" data-gl="{{ $cat->gl_ledger_acct }}">
                                        [{{ $cat->ppe_sub_major }}-{{ $cat->gl_ledger_acct }}] - {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="form-select custom-input" required>
                                <option value="" disabled selected>Select Supplier...</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Asset Details</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: var(--text-secondary);">Item Description</label>
                        <input type="text" id="name_brand_model" name="name_brand_model" class="form-control custom-input" placeholder="e.g. Acer Aspire 5 Laptop" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Serial Number (Optional)</label>
                            <input type="text" id="serial_number" name="serial_number" class="form-control custom-input" placeholder="Enter Serial #">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Accountable Personnel</label>
                            <input type="text" id="accountable_personnel" name="accountable_personnel" class="form-control custom-input" placeholder="Name of accountable person" required>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Acquisition & Location</h5>
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Acquisition Date</label>
                            <input type="date" id="acquisition_date" name="acquisition_date" class="form-control custom-input" 
                                max="{{ \Carbon\Carbon::now()->timezone('Asia/Manila')->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Cost per item (₱)</label>
                            <input type="number" step="0.01" id="acquisition_cost" name="acquisition_cost" class="form-control custom-input" placeholder="0.00" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold" style="color: var(--accent-blue);">Quantity Received</label>
                            <input type="number" id="quantity" name="quantity" class="form-control custom-input fw-bold" value="1" min="1" max="100" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Location</label>
                            <select id="location_id" name="location_id" class="form-select custom-input" required>
                                <option value="" disabled selected>Select Location...</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" data-code="{{ $loc->code }}">{{ $loc->name }}</option>
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
                    <div class="tag-header" id="preview-header">Supplier: Unassigned</div>
                    <div class="tag-body">
                        <div class="left-panel">
                            <img src="{{ asset('/images/deped-logo.png') }}" 
                                alt="DepEd Logo" 
                                style="width: 80%; height: auto; margin-bottom: 10px;">
                            
                            <div class="barcode-placeholder text-center mt-auto">
                                <svg id="preview-barcode" style="width: 100%; height: auto; max-height: 40px; object-fit: contain;"></svg>
                                <br>
                                <small style="font-weight:normal; font-size: 10px;" id="preview-barcode-text">YYYY-XX-XX-XXXX-XX</small>
                            </div>
                        </div>
                        <div class="right-panel">
                            <table class="preview-table">
                                <tr><td>Property Number</td><td><strong id="preview-tag">YYYY-XX-XX-XXXX(X)-XXXX</strong></td></tr>
                                <tr><td>Asset Classification</td><td id="preview-category">--</td></tr>
                                <tr><td>Item/Brand/Model</td><td id="preview-item">--</td></tr>
                                <tr><td>Serial Number</td><td id="preview-serial">N/A</td></tr>
                                <tr><td>Acquisition Cost</td><td id="preview-cost">₱0.00</td></tr>
                                <tr><td>Acquisition Date</td><td id="preview-date">--/--/----</td></tr>
                                <tr><td>Accountable Personnel</td><td id="preview-person">Unassigned</td></tr>
                                <tr><td>Validation Signature</td><td><br></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="tag-footer">TAMPERING <span style="border-bottom: 1px solid #f8aba6;">OF</span> THIS PROPERTY TAG IS PUNISHABLE BY LAW</div>
                </div>

                <div id="action-state-generate">
                    <button type="button" id="generateBtn" class="btn w-100 fw-bold py-2" style="background-color: var(--accent-blue); color: #fff; border-radius: 8px;">
                        <i class="bi bi-gear-fill me-2"></i> Register Asset & Generate Tag
                    </button>
                    <a href="/admin/inventory" id="cancelBtn" class="btn btn-light w-100 mt-2 py-2 border fw-semibold">Cancel</a>
                </div>
                
                <div id="action-state-success" class="d-none">
                    <div class="alert alert-success text-center fw-bold py-2 mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i> Asset Registered!
                    </div>
                    
                    <button type="button" id="printBtn" class="btn w-100 fw-bold py-2 mb-2" style="background-color: var(--text-primary); color: var(--bg-surface); border-radius: 8px;">
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
                    You are currently in the middle of registering a new asset. Leaving now will permanently discard any information entered into the system. Are you sure you want to proceed?
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
/* Exact Replica of the Print Tag, scaled for the sidebar preview */
.tag-container-preview {
    width: 100%;
    background: #ffffff;
    border: 2px solid #000;
    border-collapse: collapse;
    color: #000000;
    font-family: Arial, sans-serif;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.tag-container-preview .tag-header {
    text-align: center; 
    font-weight: bold; 
    padding: 6px; 
    border-bottom: 2px solid #000;
    background-color: #00FF00;
    color: #000;
    font-size: 13px;
    transition: background-color 0.3s ease;
}
.tag-container-preview .tag-body { 
    display: flex; 
    align-items: stretch;
}
.tag-container-preview .left-panel { 
    width: 35%; 
    border-right: 2px solid #000; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: flex-start; 
    padding: 10px 5px;
    overflow: hidden; 
}
.tag-container-preview .right-panel { 
    width: 65%; 
    display: flex;
}
.tag-container-preview .deped-logo { font-size: 22px; font-weight: 900; text-align: center; line-height: 1.1; }
.tag-container-preview .barcode-placeholder { font-weight: bold; margin-top: auto; padding-bottom: 10px; text-align: center; font-size: 12px; }
.tag-container-preview table.preview-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0; 
    height: 100%;
}
.tag-container-preview table.preview-table td { 
    border-bottom: 1px solid #000; 
    padding: 6px 8px; 
    font-size: 11px; 
}
.tag-container-preview table.preview-table td:first-child { 
    border-right: 1px solid #000; 
    width: 40%; 
    font-weight: 500; 
}
.tag-container-preview table.preview-table tr:last-child td { 
    border-bottom: none; 
}
.tag-container-preview .tag-footer { 
    text-align: center; 
    font-size: 10px; 
    padding: 4px; 
    border-top: 2px solid #000; 
}
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
// Allocate memory tracking parameters globally across the session instance window 
if (typeof window.assetFormIsDirty === 'undefined') {
    window.assetFormIsDirty = false;
}

function initializeAssetCreationPage() {
    const form = document.getElementById('createAssetForm');
    const generateBtn = document.getElementById('generateBtn');
    
    if (!form) return; 

    // Inputs
    const inputCat = document.getElementById('category_id');
    const inputLoc = document.getElementById('location_id');
    const inputDate = document.getElementById('acquisition_date');
    const inputName = document.getElementById('name_brand_model');
    const inputPerson = document.getElementById('accountable_personnel');
    const inputSerial = document.getElementById('serial_number');
    const inputCost = document.getElementById('acquisition_cost');
    const inputSupplier = document.getElementById('supplier_id');

    function updatePreview() {
        let ppe = 'XX', gl = 'XX', loc = 'XX', year = 'YYYY';

        if(inputCat && inputCat.selectedIndex > 0) {
            const selectedOption = inputCat.options[inputCat.selectedIndex];
            ppe = selectedOption.dataset.ppe || 'XX';
            gl = selectedOption.dataset.gl || 'XX';
            
            let fullText = selectedOption.text;
            if (fullText.includes('] - ')) {
                document.getElementById('preview-category').innerText = fullText.split('] - ')[1];
            } else {
                document.getElementById('preview-category').innerText = fullText;
            }
        } else if (document.getElementById('preview-category')) {
            document.getElementById('preview-category').innerText = '--';
        }

        if(inputLoc && inputLoc.selectedIndex > 0) {
            loc = inputLoc.options[inputLoc.selectedIndex].dataset.code || 'LOC';
        }

        if(inputSupplier && inputSupplier.selectedIndex > 0) {
            let supName = inputSupplier.options[inputSupplier.selectedIndex].text;
            if (document.getElementById('preview-header')) {
                document.getElementById('preview-header').innerText = `${supName}`;
                
                let color = '#FFFF00'; 
                let upperName = supName.toUpperCase();
                if(upperName.includes('LGU')) color = '#00FF00';
                else if(upperName.includes('MOOE')) color = '#00CCFF';
                else if(upperName.includes('DONATION') || upperName.includes('DONATED')) color = '#FF99CC';
                
                document.getElementById('preview-header').style.backgroundColor = color;
            }
        }

        if(inputDate && inputDate.value) {
            year = new Date(inputDate.value).getFullYear();
            const d = new Date(inputDate.value);
            const formattedDate = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            document.getElementById('preview-date').innerText = formattedDate;
        } else if (document.getElementById('preview-date')) {
            document.getElementById('preview-date').innerText = '--/--/----';
        }

        if (inputCost) {
            let costVal = parseFloat(inputCost.value);
            document.getElementById('preview-cost').innerText = isNaN(costVal) ? '₱0.00' : `₱${costVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }

        let schId = '108200'; 

        let generatedTag = `${year}-${ppe}-${gl}-XXXX(X)-${schId}`;
        
        if (document.getElementById('preview-tag')) document.getElementById('preview-tag').innerText = generatedTag;
        if (document.getElementById('preview-barcode-text')) document.getElementById('preview-barcode-text').innerText = generatedTag;
        
        if (typeof JsBarcode === "function" && document.getElementById('preview-barcode')) {
            JsBarcode("#preview-barcode", generatedTag, {
                format: "CODE128", lineColor: "#000", width: 1, height: 35, displayValue: false, margin: 0, background: "transparent"
            });
        }
        
        if (inputName && document.getElementById('preview-item')) document.getElementById('preview-item').innerText = inputName.value || '--';
        if (inputSerial && document.getElementById('preview-serial')) document.getElementById('preview-serial').innerText = inputSerial.value || 'N/A';
        if (inputPerson && document.getElementById('preview-person')) document.getElementById('preview-person').innerText = inputPerson.value || 'Unassigned';
    }

    if (form) {
        form.oninput = () => { window.assetFormIsDirty = true; updatePreview(); };
        form.onchange = () => { window.assetFormIsDirty = true; updatePreview(); };
    }

    if (typeof JsBarcode === "function" && document.getElementById('preview-barcode')) {
        JsBarcode("#preview-barcode", "YYYY-XX-XX-XXXX-XX", {
            format: "CODE128", lineColor: "#000", width: 1, height: 35, displayValue: false, margin: 0, background: "transparent"
        });
    }

    // AJAX Form Processor
    if (generateBtn) {
        generateBtn.onclick = async function () {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            generateBtn.disabled = true;
            generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
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
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<i class="bi bi-gear-fill me-2"></i> Register Asset & Generate Tag';
                    return;
                }

                const result = await response.json();

        if (result.success) {
                window.assetFormIsDirty = false; // Clean state

                // Show the FIRST generated tag on the preview UI as confirmation
                document.getElementById('preview-tag').innerText = result.first_tag;
                document.getElementById('preview-barcode-text').innerText = result.first_tag;
                
                JsBarcode("#preview-barcode", result.first_tag, {
                    format: "CODE128", lineColor: "#000", width: 1, height: 35, displayValue: false, margin: 0, background: "transparent"
                });
                
                // Route to print ALL generated IDs separated by commas
                if(document.getElementById('printBtn')) {
                    document.getElementById('printBtn').dataset.printUrl = `/admin/inventory/print-batch?ids=${result.item_ids}`;
                    
                    // Dynamically update the print button text to show quantity
                    if (result.quantity > 1) {
                        document.getElementById('printBtn').innerHTML = `<i class="bi bi-printer-fill me-2"></i> Print All ${result.quantity} Tags`;
                    }
                }

                document.getElementById('action-state-generate').classList.add('d-none');
                document.getElementById('action-state-success').classList.remove('d-none');
                
                const inputsList = [inputCat, inputLoc, inputDate, inputName, inputPerson, inputSerial, inputCost, inputSupplier, document.getElementById('quantity')];
                inputsList.forEach(el => { if(el) el.disabled = true; });

                if (typeof showNotify === "function") {
                    try { showNotify(`${result.quantity} Asset(s) registered successfully!`, "success"); } catch (e) {}
                }
            }
            } catch (error) {
                console.error(error);
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="bi bi-gear-fill me-2"></i> Register Asset & Generate Tag';
            }
        };
    }

    if(document.getElementById('printBtn')) {
        document.getElementById('printBtn').onclick = function () {
            const targetUrl = this.dataset.printUrl;
            if (targetUrl) window.open(targetUrl, '_blank');
        };
    }
}

// Mount Layout Routing Initialization Points
initializeAssetCreationPage();
document.addEventListener('DOMContentLoaded', initializeAssetCreationPage);
document.addEventListener('livewire:navigated', initializeAssetCreationPage);
document.addEventListener('turbo:load', initializeAssetCreationPage);


// --- ASYNCHRONOUS DELEGATED MODAL INTERCEPTOR SYSTEM ---
if (!window.hasGlobalAssetGuardRegistered) {
    window.hasGlobalAssetGuardRegistered = true;
    window.pendingNavigationUrl = null;

    // High-priority capture phase listener intercepting navigation departures
    window.addEventListener('click', function (e) {
        // A. Handle Dismiss requests safely if Bootstrap object scope remains encapsulated
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

        // B. Handle structural relocation execution if user confirms they want to discard changes
        if (e.target.id === 'modalConfirmDiscardBtn') {
            window.assetFormIsDirty = false;
            window.location.href = window.pendingNavigationUrl || '/admin/inventory';
            return;
        }

        if (!window.assetFormIsDirty) return;

        const triggerElement = e.target.closest('a') || e.target.closest('[onclick]') || e.target.closest('[wire\\:click]') || e.target.closest('[x-on\\:click]');
        if (!triggerElement) return;

        // Escape conditions: allow prints, final conversions, and modal internal operations
        if (triggerElement.getAttribute('target') === '_blank' || triggerElement.id === 'printBtn' || triggerElement.closest('#action-state-success') || triggerElement.closest('#cancelConfirmModal')) {
            return; 
        }

        // --- THE FIX: VIP PASS FOR UI DROPDOWNS & THEME TOGGLES ---
        // --- THE FIX: VIP PASS FOR UI DROPDOWNS & THEME TOGGLES ---
        const href = triggerElement.getAttribute('href');
        const onclickStr = triggerElement.getAttribute('onclick') || '';

        if (
            triggerElement.hasAttribute('data-bs-toggle') || 
            triggerElement.hasAttribute('data-toggle') ||    
            href === '#' ||                               
            href === 'javascript:void(0)' ||                
            (!href && !onclickStr.includes('location'))      
        ) {
            return; 
        }

        // Halt backend SPA routing engines from making invisible page body changes
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        window.pendingNavigationUrl = triggerElement.getAttribute('href') || '/admin/inventory';

        // Trigger dynamic modal injection onto user display frame
        const elementModal = document.getElementById('cancelConfirmModal');
        if (elementModal) {
            if (typeof bootstrap !== 'undefined') {
                const bsModalInstance = bootstrap.Modal.getOrCreateInstance(elementModal);
                bsModalInstance.show();
            } else {
                // High-fidelity CSS fallback if framework bundles hide bootstrap globally
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

    // Operating Frame Tab crash warning system fallback
    window.addEventListener('beforeunload', function (e) {
        if (window.assetFormIsDirty) {
            e.preventDefault();
            e.returnValue = ''; 
        }
    });

    document.addEventListener('livewire:navigating', function (e) { if (window.assetFormIsDirty && !confirm("Discard unsaved asset records?")) e.preventDefault(); });
    document.addEventListener('turbo:before-visit', function (e) { if (window.assetFormIsDirty && !confirm("Discard unsaved asset records?")) e.preventDefault(); });
}
</script>
@endsection