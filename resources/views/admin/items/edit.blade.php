@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-3">
        <h1>Edit Asset Details</h1>
        <p class="form-label" style="margin-bottom:0;">Update information for Property Tag: <strong>{{ $item->property_tag }}</strong></p>
    </div>
    <div class="d-flex justify-content-end mb-4">
        <!-- N1.2: Contextual Quick-Jump Menu -->
            <select class="form-select shadow-none" onchange="if(this.value)window.location.href=this.value" style="width:auto;min-width:130px;font-size:12px;padding:6px 10px;background:var(--bg-surface);color:var(--text-primary);border:1px solid var(--border-color);border-radius:8px;flex-shrink:0;">
            <option value="">↗ Jump to...</option>
            <option value="{{ route('items.index') }}?search={{ $item->property_tag }}">View in Inventory</option>
            <option value="/admin/print-studio?ids={{ $item->id }}">Print Tag in Studio</option>
            <option value="{{ route('items.create') }}?duplicate={{ $item->id }}">Duplicate This Asset</option>
            <option value="{{ route('items.history') }}?search={{ $item->property_tag }}">Transaction History</option>
        </select>
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
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">
                                Sub-Category / Tag
                                <span class="text-muted fw-normal" style="font-size: 11px;">(optional)</span>
                            </label>
                            <select id="tag_id" name="tag_id" class="form-select custom-input">
                                <option value="">Select an Asset Category first...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Supplier / Fund Source</label>
                            <select id="supplier_id" name="supplier_id" class="form-select custom-input" required>
                                <option value="" disabled>Select Supplier...</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" data-color="{{ $sup->color ?? '#FFFF00' }}" {{ $item->supplier_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
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
                                <option value="ongoodcondition" {{ $item->status == 'ongoodcondition' ? 'selected' : '' }}>On Good Condition</option>
                                <option value="borrowed" {{ $item->status == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                <option value="damaged" {{ $item->status == 'damaged' ? 'selected' : '' }}>Damaged / Broken Parts</option>
                                <option value="maintenance" {{ $item->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="disposed" {{ $item->status == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary);">Acquisition & Location</h5>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-secondary);">Acquisition Date</label>
                            <input type="date" id="acquisition_date" name="acquisition_date" class="form-control custom-input" value="{{ old('acquisition_date', \Carbon\Carbon::parse($item->acquisition_date)->format('Y-m-d')) }}" max="{{ \Carbon\Carbon::now()->timezone('Asia/Manila')->format('Y-m-d') }}" required>
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
                <h5 class="fw-bold mb-3" style="color: var(--text-primary);">Live Tag Preview
                    <span class="d-flex gap-1" style="float:right;">
                        <button type="button" class="btn btn-sm shadow-none p-1" onclick="zoomPreview(-0.1)" title="Zoom out" style="font-size:12px;color:var(--text-secondary);"><i class="bi bi-dash-circle"></i></button>
                        <span id="zoomLevelDisplay" style="font-size:11px;font-weight:600;color:var(--text-secondary);min-width:28px;text-align:center;display:inline-block;">1.0x</span>
                        <button type="button" class="btn btn-sm shadow-none p-1" onclick="zoomPreview(0.1)" title="Zoom in" style="font-size:12px;color:var(--text-secondary);"><i class="bi bi-plus-circle"></i></button>
                        <span class="border-start mx-1" style="height:18px;border-color:var(--border-color) !important;"></span>
                        <button type="button" class="btn btn-sm shadow-none p-1" onclick="window.copyPreviewTag()" title="Copy tag number" style="font-size:12px;color:var(--text-secondary);"><i class="bi bi-clipboard"></i></button>
                        <button type="button" class="btn btn-sm shadow-none p-1" onclick="window.copyPreviewAsImage()" title="Copy tag as image" style="font-size:14px;color:var(--text-secondary);"><i class="bi bi-image"></i></button>
                    </span>
                </h5>
                <div id="previewZoomContainer" style="overflow:hidden; position:relative; cursor:grab;">
                <div id="previewInner" style="transform-origin:0 0; will-change:transform;">
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
                                <tr><td>Property Number</td><td><strong id="preview-tag">{{ $item->property_tag }}</strong></td></tr>
                                <tr><td>Asset Classification</td><td id="preview-category">--</td></tr>
                        <tr><td>Sub-Category / Tag</td><td id="preview-item-tag">--</td></tr>
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
                </div>
                </div>

                <div id="action-state-edit">
                    <button type="button" id="saveChangesBtn" class="btn w-100 fw-bold py-2" style="background-color: var(--accent-blue); color: #fff; border-radius: 8px;">
                        <i class="bi bi-save-fill me-2"></i> Save Changes
                    </button>
                    <button type="button" onclick="toggleFocusMode()" class="btn w-100 mt-2 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" style="background:transparent;color:var(--text-secondary);border:1px solid var(--border-color);border-radius:8px;font-size:13px;">
                        <i class="bi bi-arrows-expand" id="focusModeIcon"></i> Focus Mode
                    </button>
                    <a href="/admin/inventory" id="cancelBtn" class="btn btn-light w-100 mt-2 py-2 border fw-semibold">Cancel</a>
                    <a href="/admin/inventory/{{ $item->id }}/duplicate" class="btn w-100 mt-2 py-2 fw-semibold" style="background:transparent;color:var(--accent-blue);border:1px solid var(--accent-blue);border-radius:8px;font-size:13px;">
                        <i class="bi bi-copy me-1"></i> Duplicate This Asset
                    </a>
                </div>

                <div id="action-state-success" class="d-none">
                    <div class="alert alert-success text-center fw-bold py-2 mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i> Changes Saved!
                    </div>
                    
                    <button type="button" id="printBtn" data-print-url="/admin/print-studio?ids={{ $item->id }}" class="btn w-100 fw-bold py-2 mb-2" style="background-color: var(--text-primary); color: var(--bg-surface); border-radius: 8px;">
                        <i class="bi bi-grid-3x3-gap me-2"></i> Print Tag in Studio
                    </button>
                    
                    <a href="/admin/inventory" class="btn w-100 fw-bold py-2 border" style="background-color: var(--bg-surface); color: var(--text-primary); border-radius: 8px;">
                        <i class="bi bi-check2-all me-2"></i> Finish & Go to Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelConfirmModal" data-centered data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
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
/* N2.1: Focus Mode CSS */
body.focus-mode .sidebar { display: none !important; }
body.focus-mode .unified-header { display: none !important; }
body.focus-mode .main-content > .p-4.p-md-5 { max-width: 800px; margin: 0 auto; }
body.focus-mode .tag-preview-col, body.focus-mode .col-lg-5 { display: none !important; }
body.focus-mode .col-lg-7 { flex: 0 0 100%; max-width: 100%; }
body.focus-mode .page-header { text-align: center; }
body.focus-mode .breadcrumb { display: none !important; }
body.focus-mode .panel-card { border: none; box-shadow: none; background: transparent; }
body.focus-mode { background: var(--bg-main); transition: all 0.3s ease; }

.tag-container-preview { width: 100%; background: #ffffff; border: 2px solid #000; border-collapse: collapse; color: #000000; font-family: Arial, sans-serif; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
.tag-container-preview .tag-header { text-align: center; font-weight: bold; padding: 6px; border-bottom: 2px solid #000; background-color: #FFFF00; color: #000; font-size: 13px; transition: background-color 0.3s ease; }
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
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.bi-arrow-repeat.spinner { animation: spin 0.8s linear infinite; display: inline-block; }
</style>
<script>
// ══════════════════════════════════════
// ZOOM, PAN, COPY FUNCTIONS (standalone)
// ══════════════════════════════════════
window._previewZoomLevel = 1.0;

// ── Zoom ──
window.zoomPreview = function(delta) {
    window._previewZoomLevel = Math.max(1.0, Math.min(3.0, window._previewZoomLevel + delta));
    var inner = document.getElementById('previewInner');
    var container = document.getElementById('previewZoomContainer');
    if (!inner || !container) return;
    var z = window._previewZoomLevel;
    inner.style.transform = 'scale(' + z + ')';
    inner.style.transformOrigin = '0 0';
    var display = document.getElementById('zoomLevelDisplay');
    if (display) display.textContent = z.toFixed(1) + 'x';
    container.style.cursor = z > 1 ? 'move' : 'grab';
};

// ── Pan ──
window._attachPreviewPan = function() {
    var container = document.getElementById('previewZoomContainer');
    var inner = document.getElementById('previewInner');
    if (!container || !inner) return;
    
    var isDown = false, startX, startY, scrollLeft, scrollTop;
    
    container.addEventListener('mousedown', function(e) {
        if (window._previewZoomLevel <= 1) return;
        isDown = true;
        container.style.cursor = 'grabbing';
        var match = inner.style.transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
        scrollLeft = match ? parseFloat(match[1]) || 0 : 0;
        scrollTop = match ? parseFloat(match[2]) || 0 : 0;
        startX = e.clientX - scrollLeft;
        startY = e.clientY - scrollTop;
    });
    container.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        var x = e.clientX - startX;
        var y = e.clientY - startY;
        inner.style.transform = 'scale(' + window._previewZoomLevel + ') translate(' + x + 'px, ' + y + 'px)';
        inner.style.transformOrigin = '0 0';
    });
    container.addEventListener('mouseup', function() { isDown = false; container.style.cursor = window._previewZoomLevel > 1 ? 'move' : 'grab'; });
    container.addEventListener('mouseleave', function() { isDown = false; container.style.cursor = window._previewZoomLevel > 1 ? 'move' : 'grab'; });
};

function validateAssetDetailsForTag() {
    const required = [
        { id: 'category_id', label: 'Asset Category' },
        { id: 'supplier_id', label: 'Supplier' },
        { id: 'name_brand_model', label: 'Item Description / Name' },
        { id: 'acquisition_date', label: 'Acquisition Date' },
        { id: 'acquisition_cost', label: 'Acquisition Cost' },
        { id: 'location_id', label: 'Location' }
    ];
    for (const field of required) {
        const el = document.getElementById(field.id);
        if (!el || !el.value || el.value.trim() === '') {
            return field;
        }
    }
    return null;
}

// ── Copy property tag (text) ──
window.copyPreviewTag = function() {
    try {
        const missing = validateAssetDetailsForTag();
        if (missing) {
            const msg = 'Please complete ' + missing.label + ' before copying the property tag.';
            if (typeof showToast === 'function') {
                showToast('Incomplete Asset Details', msg, 'warning');
            } else {
                alert(msg);
            }
            const el = document.getElementById(missing.id);
            if (el) {
                el.focus();
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('is-invalid');
                setTimeout(() => { el.classList.remove('is-invalid'); }, 2500);
            }
            return;
        }

        var tagEl = document.getElementById('preview-tag');
        if (!tagEl) { alert('Property tag element not found.'); return; }
        var text = tagEl.textContent || tagEl.innerText;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                flashCopyBtn('copyPreviewTag', 'bi-clipboard', 'bi-check-lg', 'Tag copied!');
            }).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    } catch(e) { console.error('copyPreviewTag error:', e); }
};

function fallbackCopy(text) {
    try {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed'; ta.style.left = '-9999px'; ta.style.top = '0'; ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        flashCopyBtn('copyPreviewTag', 'bi-clipboard', 'bi-check-lg', 'Tag copied!');
    } catch(e) { console.error('fallbackCopy error:', e); }
}

function flashCopyBtn(fnName, restoreClass, flashClass, msg) {
    var icon = document.querySelector('button[onclick="window.' + fnName + '()"] i');
    if (!icon) icon = document.querySelector('button[onclick="' + fnName + '()"] i');
    if (icon) {
        var orig = icon.className;
        icon.className = flashClass;
        icon.style.color = '#10b981';
        setTimeout(function() {
            icon.className = restoreClass;
            icon.style.color = '';
        }, 1500);
    }
    try { if (typeof showToast === 'function') showToast('Copied', msg, 'success'); } catch(ee) {}
}

// ── Copy tag as image ──
window.copyPreviewAsImage = function() {
    try {
        const missing = validateAssetDetailsForTag();
        if (missing) {
            const msg = 'Please complete ' + missing.label + ' before copying the property tag image.';
            if (typeof showToast === 'function') {
                showToast('Incomplete Asset Details', msg, 'warning');
            } else {
                alert(msg);
            }
            const fieldEl = document.getElementById(missing.id);
            if (fieldEl) {
                fieldEl.focus();
                fieldEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                fieldEl.classList.add('is-invalid');
                setTimeout(() => { fieldEl.classList.remove('is-invalid'); }, 2500);
            }
            return;
        }

        var el = document.querySelector('#previewZoomContainer .tag-container-preview') || document.getElementById('previewZoomContainer');
        if (!el) return;
        
        var icon = document.querySelector('button[onclick="window.copyPreviewAsImage()"] i');
        if (!icon) icon = document.querySelector('button[onclick="copyPreviewAsImage()"] i');
        if (icon) icon.className = 'bi bi-arrow-repeat spinner';
        
        if (typeof html2canvas !== 'function') {
            if (icon) { icon.className = 'bi bi-image'; }
            alert('Image library not loaded. Please check your internet connection.');
            return;
        }
        
        html2canvas(el, { backgroundColor: '#ffffff', scale: 2, useCORS: true, logging: false })
        .then(function(canvas) {
            canvas.toBlob(function(blob) {
                if (!blob) { resetImgBtn(icon); return; }
                try {
                    if (navigator.clipboard && navigator.clipboard.write && typeof ClipboardItem !== 'undefined') {
                        navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })])
                        .then(function() {
                            flashCopyBtn('copyPreviewAsImage', 'bi-image', 'bi-check-lg', 'Tag image copied!');
                        })
                        .catch(function() {
                            downloadCanvas(canvas, icon);
                        });
                    } else {
                        downloadCanvas(canvas, icon);
                    }
                } catch(ce) {
                    downloadCanvas(canvas, icon);
                }
            }, 'image/png');
        })
        .catch(function(err) {
            console.error('html2canvas error:', err);
            resetImgBtn(icon);
        });
    } catch(e) { console.error('copyPreviewAsImage error:', e); }
};

function downloadCanvas(canvas, icon) {
    try {
        var link = document.createElement('a');
        link.download = 'property-tag.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        flashCopyBtn('copyPreviewAsImage', 'bi-image', 'bi-check-lg', 'Tag image downloaded!');
    } catch(e) { console.error('downloadCanvas error:', e); resetImgBtn(icon); }
}

function resetImgBtn(icon) {
    if (!icon) return;
    icon.className = 'bi bi-image';
    icon.style.color = '';
}

// ── Focus Mode ──
window.toggleFocusMode = function() {
    document.body.classList.toggle('focus-mode');
    var icon = document.getElementById('focusModeIcon');
    if (icon) {
        var isFocus = document.body.classList.contains('focus-mode');
        icon.className = isFocus ? 'bi bi-arrows-collapse' : 'bi bi-arrows-expand';
        localStorage.setItem('focus-mode', isFocus ? 'on' : 'off');
        if (isFocus) {
            var hint = document.createElement('div');
            hint.style.cssText = 'position:fixed;top:12px;left:50%;transform:translateX(-50%);z-index:99998;background:var(--accent-blue);color:#fff;padding:8px 20px;border-radius:20px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.2);pointer-events:none;transition:opacity 0.3s ease;';
            hint.textContent = 'Focus Mode — click the Focus button or press Esc to exit';
            hint.id = 'focusModeHint';
            document.body.appendChild(hint);
            setTimeout(function() {
                var h = document.getElementById('focusModeHint');
                if (h) { h.style.opacity = '0'; setTimeout(function() { h && h.remove(); }, 300); }
            }, 3000);
        } else {
            var h = document.getElementById('focusModeHint');
            if (h) h.remove();
        }
    }
};

// ── Escape key exits focus mode ──
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.body.classList.contains('focus-mode')) {
        toggleFocusMode();
    }
});

// W1.2: Draft Auto-Save ────────────────
(function() {
    var form = document.getElementById('editAssetForm');
    if (!form) return;
    var draftKey = 'draft-' + window.location.pathname;
    var saveTimer;
    
    function saveDraft() {
        var data = new FormData(form);
        var obj = {};
        data.forEach(function(value, key) { obj[key] = value; });
        localStorage.setItem(draftKey, JSON.stringify(obj));
    }
    
    function checkDraft() {
        var saved = localStorage.getItem(draftKey);
        if (!saved) return;
        try {
            var data = JSON.parse(saved);
            var hasData = Object.values(data).some(function(v) { return v && v.length > 0; });
            if (!hasData) return;
            var banner = document.createElement('div');
            banner.className = 'alert alert-info d-flex align-items-center justify-content-between mb-4';
            banner.style.cssText = 'border-radius:12px;background:var(--accent-blue-bg);border:1px solid var(--accent-blue);color:var(--text-primary);padding:12px 18px;';
            banner.innerHTML = '<span><i class="bi bi-save me-2"></i><strong>Draft found.</strong> You have unsaved data from a previous session.</span>'
                + '<div class="d-flex gap-2">'
                + '<button class="btn btn-sm btn-light fw-bold" onclick="restoreEditDraft()" style="border-radius:8px;">Restore</button>'
                + '<button class="btn btn-sm btn-outline-danger fw-bold" onclick="discardEditDraft()" style="border-radius:8px;">Discard</button>'
                + '</div>';
            form.parentNode.insertBefore(banner, form);
            window._editDraftData = data;
        } catch(e) {}
    }
    
    window.restoreEditDraft = function() {
        if (!window._editDraftData) return;
        Object.keys(window._editDraftData).forEach(function(key) {
            var el = form.querySelector('[name="' + key + '"]');
            if (el) el.value = window._editDraftData[key];
        });
        var banner = form.previousSibling;
        if (banner && banner.classList.contains('alert')) banner.remove();
        if (typeof updatePreview === 'function') updatePreview();
        localStorage.removeItem('draft-' + window.location.pathname);
        window._editDraftData = null;
    };
    window.discardEditDraft = function() {
        localStorage.removeItem('draft-' + window.location.pathname);
        var banner = form.previousSibling;
        if (banner && banner.classList.contains('alert')) banner.remove();
        window._editDraftData = null;
    };
    
    form.addEventListener('input', function() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 10000);
    });
    
    checkDraft();
    
    var observer = new MutationObserver(function() {
        if (document.querySelector('#action-state-success:not(.d-none)')) {
            localStorage.removeItem(draftKey);
            observer.disconnect();
        }
    });
    observer.observe(document.getElementById('action-state-success'), { attributes: true, attributeFilter: ['class'] });
})();

if (typeof window.assetFormIsDirty === 'undefined') { window.assetFormIsDirty = false; }

function initializeAssetEditPage() {
    const form = document.getElementById('editAssetForm');
    const saveBtn = document.getElementById('saveChangesBtn');
    if (!form) return; 

    window.assetFormIsDirty = false;

    const inputCat = document.getElementById('category_id');
    const inputDate = document.getElementById('acquisition_date');
    const inputName = document.getElementById('name_brand_model');
    const inputPerson = document.getElementById('accountable_personnel');
    const inputSerial = document.getElementById('serial_number');
    const inputCost = document.getElementById('acquisition_cost');
    const inputSupplier = document.getElementById('supplier_id');

    function updatePreview() {
        if(inputCat && inputCat.selectedIndex > 0) {
            let fullText = inputCat.options[inputCat.selectedIndex].text;
            document.getElementById('preview-category').innerText = fullText.includes('] - ') ? fullText.split('] - ')[1] : fullText;
        }

        if(inputSupplier && inputSupplier.selectedIndex > 0) {
            let supName = inputSupplier.options[inputSupplier.selectedIndex].text;
            let supColor = inputSupplier.options[inputSupplier.selectedIndex].dataset.color || '#FFFF00';
            let header = document.getElementById('preview-header');
            if (header) {
                header.innerText = `${supName}`;
                header.style.backgroundColor = supColor;
            }
        }

        if(inputDate && inputDate.value) {
            const d = new Date(inputDate.value);
            document.getElementById('preview-date').innerText = !isNaN(d) ? d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '--/--/----';
        }

        if (inputCost) {
            let costVal = parseFloat(inputCost.value);
            document.getElementById('preview-cost').innerText = isNaN(costVal) ? '₱0.00' : `₱${costVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }

        if (inputName && document.getElementById('preview-item')) document.getElementById('preview-item').innerText = inputName.value || '--';
        if (inputSerial && document.getElementById('preview-serial')) document.getElementById('preview-serial').innerText = inputSerial.value || 'N/A';
        if (inputPerson && document.getElementById('preview-person')) document.getElementById('preview-person').innerText = inputPerson.value || 'Unassigned';
    }

    // Pan setup (once only)
    if (window._previewZoomLevel !== undefined && !window._previewPanAttached) {
        window._previewPanAttached = true;
        window._attachPreviewPan();
    }

    if (form) {
        form.addEventListener('input', () => { window.assetFormIsDirty = true; updatePreview(); });
        form.addEventListener('change', () => { window.assetFormIsDirty = true; updatePreview(); });
    }

    if (typeof JsBarcode === "function" && document.getElementById('preview-barcode')) {
        JsBarcode("#preview-barcode", "{{ $item->property_tag }}", {
            format: "CODE128", lineColor: "#000", width: 1, height: 35, displayValue: false, margin: 0, background: "transparent"
        });
        updatePreview();
    }

    if (saveBtn) {
        saveBtn.onclick = async function () {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            
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

                window.assetFormIsDirty = false;

                document.getElementById('action-state-edit').classList.add('d-none');
                document.getElementById('action-state-success').classList.remove('d-none');
                
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
}
    // ── W1.4: Save this item to recently viewed ──
    (function() {
        try {
            var items = JSON.parse(localStorage.getItem('recent-items') || '[]');
            var itemId = '{{ $item->id }}';
            var itemName = '{{ addslashes($item->name) }}';
            var itemTag = '{{ $item->property_tag }}';
            items = items.filter(function(i) { return i.id !== itemId; });
            items.unshift({
                id: itemId,
                name: itemName,
                tag: itemTag,
                time: 'Just now',
                ts: Date.now()
            });
            items = items.slice(0, 10);
            localStorage.setItem('recent-items', JSON.stringify(items));
        } catch(e) {}
    })();
</script>

<script>
// ── Sub-Category / Tag dropdown (dependent on Asset Category) ──
(function () {
    const tagsByCategory = @json($tagsByCategory ?? []);
    const prefillTagId = @json(old('tag_id', $item->tag_id));

    const catSelect = document.getElementById('category_id');
    const tagSelect = document.getElementById('tag_id');
    if (!catSelect || !tagSelect) return;

    function refreshTagOptions() {
        const catId = catSelect.value;
        const list = tagsByCategory[catId] || [];

        tagSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        if (!catId) {
            placeholder.textContent = 'Select an Asset Category first...';
        } else if (list.length === 0) {
            placeholder.textContent = 'No sub-categories defined for this category';
        } else {
            placeholder.textContent = 'None / General';
        }
        tagSelect.appendChild(placeholder);

        list.forEach(function (t) {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            tagSelect.appendChild(opt);
        });

        updateTagPreview();
    }

    function updateTagPreview() {
        const preview = document.getElementById('preview-item-tag');
        if (preview) {
            preview.innerText = tagSelect.selectedIndex > 0
                ? tagSelect.options[tagSelect.selectedIndex].text
                : '--';
        }
    }

    catSelect.addEventListener('change', function () {
        // Changing category invalidates the previous tag unless it belongs to it
        tagSelect.value = '';
        refreshTagOptions();
    });
    tagSelect.addEventListener('change', updateTagPreview);

    refreshTagOptions();
    if (prefillTagId) {
        tagSelect.value = String(prefillTagId);
        updateTagPreview();
    }
})();
</script>
@endsection