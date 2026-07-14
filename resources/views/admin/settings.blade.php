@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4">
        <h1>System Settings</h1>
        <p class="form-label text-secondary">Configure system behavior, inventory rules, and administrative preferences.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2 fw-semibold" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row gx-4">
        
        <div class="col-lg-3 mb-4">
            <div class="panel-card p-3 sticky-top" style="top: 24px; border-radius: 12px;">
                <div class="nav flex-column nav-pills custom-settings-nav" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    
                    <div class="nav-category mt-2 mb-2 px-3 fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Preferences</div>
                    <button class="nav-link active d-flex align-items-center gap-2 mb-1" id="v-pills-general-tab" data-bs-toggle="pill" data-bs-target="#v-pills-general" type="button" role="tab">
                        <i class="bi bi-sliders"></i> General
                    </button>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-inventory-tab" data-bs-toggle="pill" data-bs-target="#v-pills-inventory" type="button" role="tab">
                        <i class="bi bi-upc-scan"></i> Inventory Rules
                    </button>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-appearance-tab" data-bs-toggle="pill" data-bs-target="#v-pills-appearance" type="button" role="tab">
                        <i class="bi bi-palette"></i> Appearance
                    </button>

                    <div class="nav-category mt-4 mb-2 px-3 fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Security & Data</div>
                    <button class="nav-link d-flex align-items-center gap-2 mb-1" id="v-pills-backup-tab" data-bs-toggle="pill" data-bs-target="#v-pills-backup" type="button" role="tab">
                        <i class="bi bi-database-down"></i> Backup & Restore
                    </button>
                    <a href="/admin/users" class="nav-link d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-people"></i> User Management
                    </a>
                    <a href="/admin/logs" class="nav-link d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-journal-text"></i> Audit Logs
                    </a>

                    <div class="nav-category mt-4 mb-2 px-3 fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Organizations</div>
                    <a href="/admin/departments" class="nav-link d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-building"></i> Departments
                    </a>
                    <a href="/admin/suppliers" class="nav-link d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-truck"></i> Suppliers
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                
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

                <div class="tab-pane fade" id="v-pills-appearance" role="tabpanel" tabindex="0">
                    <div class="panel-card p-4 mb-4" style="border-radius: 12px;">
                        <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--text-primary);">
                            <i class="bi bi-palette me-2 text-primary"></i> Appearance Customization
                        </h5>
                        
                        <p class="text-secondary mb-4">Note: The system theme (Dark/Light mode) can be toggled instantly using the sun/moon icon in the top navigation bar. These settings control structural layouts.</p>

                        <form action="{{ route('admin.settings.appearance') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary mb-3">Inventory Table Density</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="density" id="densityCozy" value="densityCozy" {{ ($settings['density'] ?? 'densityCozy') === 'densityCozy' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="densityCozy" style="color: var(--text-primary);">Cozy (Spacious rows)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="density" id="densityCompact" value="densityCompact" {{ ($settings['density'] ?? 'densityCozy') === 'densityCompact' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="densityCompact" style="color: var(--text-primary);">Compact (More data on screen)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">Save Appearance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" id="restoreWarningModal" tabindex="-1" aria-hidden="true">
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
/* Settings Navigation Pill Styles (Dynamic Theme Proof) */
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

/* Fixes the text input colors */
.theme-dynamic-input {
    background: transparent !important;
    border: 1px solid var(--text-secondary) !important;
    color: var(--text-primary) !important;
}

.theme-dynamic-input:focus {
    box-shadow: none !important;
    border-color: var(--text-primary) !important;
}

/* ==========================================================================
   THEME EXTRACTION OVERRIDE: SELECTION FIELD READABILITY FIX
   ========================================================================== */
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Intercept Database Overwrite Restoration Actions
    const triggerRestoreBtn = document.getElementById('triggerRestoreBtn');
    const confirmSystemRestoreBtn = document.getElementById('confirmSystemRestoreBtn');
    const systemRestoreForm = document.getElementById('systemRestoreForm');
    const restorePasswordInput = document.getElementById('restorePasswordInput');
    const hiddenRestorePassword = document.getElementById('hiddenRestorePassword');

    if (triggerRestoreBtn) {
        triggerRestoreBtn.addEventListener('click', function(e) {
            const fileInput = systemRestoreForm.querySelector('input[type="file"]');
            if (!fileInput.value) {
                fileInput.reportValidity(); // Alerts browser to prompt native required flags
                return;
            }
            
            // Clear any password typed previously to ensure a fresh session challenge
            if(restorePasswordInput) restorePasswordInput.value = '';
            
            // Present the high-priority safety verification warning modal
            new bootstrap.Modal(document.getElementById('restoreWarningModal')).show();
        });
    }

    if (confirmSystemRestoreBtn) {
        confirmSystemRestoreBtn.addEventListener('click', function() {
            // 1. Validation check ensuring password field is not empty
            if (!restorePasswordInput.value) {
                restorePasswordInput.reportValidity();
                return;
            }

            // 2. Map password value token to form container hidden variable proxy
            hiddenRestorePassword.value = restorePasswordInput.value;

            // 3. Trigger operational state loading indicators
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Authenticating & Restoring...';
            this.disabled = true;
            
            // 4. Submit form payload
            systemRestoreForm.submit();
        });
    }
});
</script>
@endsection