@extends('layouts.admin')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header mb-4 no-print">
        <h1>Inventory Analytics & Reports</h1>
        <p class="form-label text-secondary">Audit lifecycle asset states, track structural distributions, and generate official ledger sheets.</p>
    </div>

    <div class="row gx-4 mb-4 no-print">
        <div class="col-md-3 mb-3">
            <div class="panel-card p-3 d-flex align-items-center justify-content-between" style="border-radius: 12px;">
                <div>
                    <span class="small fw-bold text-secondary d-block mb-1">TOTAL REGISTERED ASSETS</span>
                    <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $totalAssets }}</h3>
                </div>
                <i class="bi bi-box-fill fs-2" style="color: var(--accent-blue); opacity: 0.8;"></i>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="panel-card p-3 d-flex align-items-center justify-content-between" style="border-radius: 12px;">
                <div>
                    <span class="small fw-bold text-secondary d-block mb-1">ASSETS ON LOAN</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ $borrowedCount }}</h3>
                </div>
                <i class="bi bi-arrow-left-right fs-2 text-warning" style="opacity: 0.8;"></i>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="panel-card p-3 d-flex align-items-center justify-content-between" style="border-radius: 12px;">
                <div>
                    <span class="small fw-bold text-secondary d-block mb-1">DAMAGED / MAINTENANCE</span>
                    <h3 class="fw-bold mb-0 text-danger">{{ $damagedCount }}</h3>
                </div>
                <i class="bi bi-exclamation-triangle-fill fs-2 text-danger" style="opacity: 0.8;"></i>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="panel-card p-3 d-flex align-items-center justify-content-between" style="border-radius: 12px;">
                <div>
                    <span class="small fw-bold text-secondary d-block mb-1">TOTAL PORTFOLIO VALUATION</span>
                    <h3 class="fw-bold mb-0" style="color: var(--accent-green, #198754);">₱{{ number_format($totalValue, 2) }}</h3>
                </div>
                <i class="bi bi-currency-dollar fs-2" style="color: var(--accent-green, #198754); opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <div class="panel-card p-4 mb-4 no-print" style="border-radius: 12px;">
        <h5 class="fw-bold mb-3" style="color: var(--text-primary);"><i class="bi bi-gear-fill me-2 text-primary"></i> Report Specification Workbench</h5>
        
        <form action="/admin/reports" method="GET" id="reportGenerationForm">
            <input type="hidden" name="generate" value="true">
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-secondary">Target Report Module</label>
                    <select name="report_type" id="report_type" class="form-select theme-dynamic-input" onchange="toggleFilterView()">
                        <option value="summary" {{ request('report_type') === 'summary' ? 'selected' : '' }}>Inventory Summary Ledger</option>
                        <option value="borrowing" {{ request('report_type') === 'borrowing' ? 'selected' : '' }}>Borrower Log Transaction Report</option>
                        <option value="low_stock" {{ request('report_type') === 'low_stock' ? 'selected' : '' }}>Low Stock Alerts Ledger</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3" id="categoryFilterContainer">
                    <label class="form-label fw-bold text-secondary">Asset Category</label>
                    <select name="category_id" class="form-select theme-dynamic-input">
                        <option value="all" {{ request('category_id') === 'all' ? 'selected' : '' }}>All Classifications</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold text-secondary">Status Parameter</label>
                    <select name="status" class="form-select theme-dynamic-input">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All States</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Borrowed / Loaned</option>
                        <option value="damaged" {{ request('status') === 'damaged' ? 'selected' : '' }}>Damaged / Repair</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold text-secondary">Start Boundary Date</label>
                    <input type="date" name="start_date" class="form-control theme-dynamic-input" 
                        value="{{ request('start_date') }}" 
                        max="{{ \Carbon\Carbon::now()->timezone('Asia/Manila')->format('Y-m-d') }}">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold text-secondary">End Boundary Date</label>
                    <input type="date" name="end_date" class="form-control theme-dynamic-input" 
                        value="{{ request('end_date') }}" 
                        max="{{ \Carbon\Carbon::now()->timezone('Asia/Manila')->format('Y-m-d') }}">
                </div>
            </div>

            <div class="text-end mt-2">
                <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">
                    <i class="bi bi-file-earmark-bar-graph-fill me-1"></i> Generate System Report
                </button>
            </div>
        </form>
    </div>

    @if(request()->has('generate'))
    <div class="panel-card p-0 overflow-hidden shadow-sm printable-report-wrapper">
        
        <div class="print-official-document-header d-none w-100 mb-4 text-center">
            <div class="d-flex align-items-center justify-content-center position-relative pb-3" style="border-bottom: 2px solid #000; min-height: 90px;">
                
                <div class="position-absolute start-0" style="top: 50%; transform: translateY(-50%);">
                    <img src="{{ asset('images/deped-logo.png') }}" alt="" style="height: 75px; width: auto; object-fit: contain; border: none; outline: none;">
                </div>
                
                <div class="text-center font-official-serif" style="padding: 0 90px;">
                    <div style="font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: #000;">Republic of the Philippines</div>
                    <div style="font-size: 16px; font-weight: 700; text-transform: uppercase; margin: 1px 0; font-family: 'Times New Roman', Times, serif; color: #000;">Department of Education</div>
                    <div style="font-size: 11px; font-style: italic; color: #000;">Region IV-A CALABARZON</div>
                    <div style="font-size: 11px; font-weight: 500; color: #000;">Schools Division of Laguna</div>
                    <div style="font-size: 13px; font-weight: 700; margin-top: 3px; text-transform: uppercase; font-family: 'Times New Roman', Times, serif; color: #000;">
                        {{ $settings['org_name'] }}
                    </div>
                </div>

                <div class="position-absolute end-0" style="top: 50%; transform: translateY(-50%);">
                    <img src="{{ asset('images/bces-logo.png') }}" alt="" style="height: 75px; width: auto; object-fit: contain; border: none; outline: none;">
                </div>
            </div>
            
            <div class="mt-4 mb-2 text-center">
                <h4 class="fw-bold font-official-serif m-0" style="font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Times New Roman', Times, serif; color: #000;">
                    @if(request('report_type') === 'borrowing')
                        REPORT ON THE TRANSACTIONAL LOAN LOGS & ENTRIES
                    @elseif(request('report_type') === 'low_stock')
                        SEMI-EXPENDABLE PROPERTY ALERT LOG (LOW STOCK)
                    @else
                        REPORT ON THE PHYSICAL COUNT OF INVENTORIES (RPCI)
                    @endif
                </h4>
                <div class="mt-1 fw-bold" style="font-size: 11px; color: #444 !important;">
                    {{ $settings['system_name'] }} — Compiled: {{ now()->format('F d, Y') }}
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4 py-3 no-print" style="border-bottom: 1px solid var(--border-color); background-color: rgba(128,128,128,0.02);">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success py-1 px-2 fw-bold" style="font-size: 11px;">DATA STREAM ACTIVE</span>
                <small class="text-secondary fw-semibold">{{ $reportRecords->count() }} records parsed successfully.</small>
            </div>
            
            <button type="button" onclick="window.print()" class="btn btn-sm btn-light border fw-bold px-3 py-1.5 d-flex align-items-center gap-2" style="border-radius: 6px;">
                <i class="bi bi-printer-fill"></i> Export / Print DepEd PDF
            </button>
        </div>

        @if($reportRecords->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder-x display-4 mb-2 d-block opacity-50"></i>
                No matching transactional asset elements located for designated filter constraints.
            </div>
        @else
            @if($reportType === 'borrowing')
                <table class="admin-table mb-0 deped-structured-grid">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 22%;">PROPERTY NUMBER</th>
                            <th style="width: 25%;">ARTICLE / NOMENCLATURE</th>
                            <th>BORROWER / CUSTODIAN</th>
                            <th class="text-center" style="width: 15%;">DATE BORROWED</th>
                            <th class="text-center" style="width: 15%;">DATE RETURNED</th>
                            <th class="text-center pe-4" style="width: 13%;">LOG STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRecords as $log)
                        <tr>
                            <td class="ps-2 py-3 text-center font-monospace" style="font-size: 12px; font-weight: 600;">{{ $log->item->property_tag ?? 'N/A' }}</td>
                            <td class="fw-bold text-primary-theme">{{ $log->item->name ?? 'Deleted Asset Reference' }}</td>
                            <td class="fw-semibold">{{ $log->user->name ?? 'N/A' }}</td>
                            <td class="text-center" style="font-size: 12px;">{{ $log->created_at ? $log->created_at->format('Y-m-d h:i A') : 'N/A' }}</td>
                            <td class="text-center" style="font-size: 12px;">
                                {{ $log->status === 'returned' && $log->updated_at ? $log->updated_at->format('Y-m-d h:i A') : 'OUTSTANDING' }}
                            </td>
                            <td class="text-center pe-4 fw-bold text-uppercase" style="font-size: 11px;">{{ $log->status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="d-none d-print-table-footer-group">
                        <tr>
                            <td colspan="6" style="height: 1.8in; border: none !important; background: transparent !important;"></td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <table class="admin-table mb-0 deped-structured-grid">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 24%;">PROPERTY NUMBER</th>
                            <th style="width: 28%;">ARTICLE / NOMENCLATURE</th>
                            <th class="text-center" style="width: 14%;">CATEGORY</th>
                            <th class="text-center" style="width: 14%;">LOCATION</th>
                            <th class="text-end" style="width: 12%;">UNIT VALUE</th>
                            <th class="text-center pe-4" style="width: 10%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRecords as $item)
                        <tr>
                            <td class="ps-2 py-3 text-center font-monospace" style="font-size: 11px; font-weight: 600;">{{ $item->property_tag }}</td>
                            <td class="fw-bold text-primary-theme">{{ $item->name }}</td>
                            <td class="text-secondary text-center" style="font-size: 12px;">{{ $item->category->name ?? 'N/A' }}</td>
                            <td class="text-secondary text-center" style="font-size: 12px;">{{ $item->location->name ?? 'N/A' }}</td>
                            <td class="text-end fw-semibold text-nowrap">₱{{ number_format($item->acquisition_cost, 2) }}</td>
                            <td class="text-center pe-4 fw-bold text-uppercase" style="font-size: 11px;">
                                <span class="print-text-dark">{{ $item->status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="d-none d-print-table-footer-group">
                        <tr>
                            <td colspan="6" style="height: 1.8in; border: none !important; background: transparent !important;"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif

        <div class="print-official-signoff-footer d-none">
            
            <table class="w-100 table-borderless signature-matrix-layout" style="font-family: Arial, sans-serif; font-size: 11px; color: #000; margin-bottom: 20px;">
                <tbody>
                    <tr>
                        <td style="width: 33.3%; vertical-align: top;">
                            <div class="fw-bold mb-4" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Prepared By:</div>
                            <div class="mt-4 pt-2 border-bottom-signature text-center fw-bold text-uppercase" style="width: 85%; font-size: 11px;">{{ Auth::user()->name }}</div>
                            <div class="text-center text-secondary small mt-1" style="width: 85%; color: #555 !important;">Property Custodian / Supply Officer</div>
                        </td>
                        <td style="width: 33.3%; vertical-align: top;">
                            <div class="fw-bold mb-4" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Certified Correct By:</div>
                            <div class="mt-4 pt-2 border-bottom-signature text-center fw-bold" style="width: 85%; font-size: 11px;">___________________________</div>
                            <div class="text-center text-secondary small mt-1" style="width: 85%; color: #555 !important;">School Property Inspectorate Chairman</div>
                        </td>
                        <td style="width: 33.3%; vertical-align: top;">
                            <div class="fw-bold mb-4" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Approved By:</div>
                            <div class="mt-4 pt-2 border-bottom-signature text-center fw-bold text-uppercase" style="width: 85%; font-size: 11px;">___________________________</div>
                            <div class="text-center text-secondary small mt-1" style="width: 85%; color: #555 !important;">School Principal / Head Teacher</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="audit-trail-horizon-metadata pt-2 w-100" style="border-top: 1px dashed #000; font-family: Arial, sans-serif; font-size: 10px; color: #444 !important; display: table; table-layout: fixed;">
                <div style="display: table-cell; text-align: left; width: 35%;">
                    Generated on May 16, 2026 via <span class="fw-bold">{{ $settings['system_name'] }}</span>
                </div>
                
                <div class="italic-notice-label" style="display: table-cell; text-align: center; width: 40%; font-style: italic;">
                    This document is system-generated and intended for official inventory monitoring only.
                </div>
                
                <div class="print-page-tracking-aside" style="display: table-cell; text-align: right; width: 25%; font-weight: bold;">
                    <span class="print-page-counter"></span> | ID: RPCI-{{ now()->format('Ymd') }}-{{ str_pad($reportRecords->count(), 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="panel-card p-5 text-center text-muted" style="border-radius: 12px; border: 1px dashed var(--text-secondary);">
        <i class="bi bi-file-earmark-text-fill display-3 d-block mb-3 opacity-25 text-secondary"></i>
        <h5>No Report Generated Yet</h5>
        <p class="small text-secondary max-width-auto">Configure your target specifications inside the workbench engine parameters panel above and execute "Generate System Report" to run high-fidelity audits.</p>
    </div>
    @endif
</div>

<style>
    /* Workbench Screen Styles */
    .theme-dynamic-input { background: var(--bg-main) !important; border: 1px solid var(--border-color) !important; color: var(--text-primary) !important; }
    .theme-dynamic-input::placeholder { color: var(--text-secondary) !important; opacity: 0.7; }
    .theme-dynamic-input:focus { background-color: transparent !important; color: var(--text-primary) !important; box-shadow: none !important; }
    .theme-dynamic-input option { background-color: var(--bg-surface) !important; color: var(--text-primary) !important; }
    
    .text-primary-theme { color: var(--text-primary); }

    /* ==========================================================================
       OFFICIAL GOVERNMENT REGULATORY PRINT SHEETS ENGINE OVERRIDES
       ========================================================================== */
    @media print {
        /* 1. Force the hardware print engine to zero out default margins, stripping browser headers/footers */
        @page {
            size: letter portrait;
            margin: 0 !important; 
        }

        .sidebar, .top-navbar, .no-print, .floating-help, .page-header, .alert, .btn-close {
            display: none !important;
        }
        
        /* 2. Establish clear 0.5-inch safety margin block via padding inside box-sizing bounds */
        body {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0.5in 0.5in 0.5in 0.5in !important; /* Forces layout spacing from edge */
            margin: 0 !important;
            width: 100% !important;
            font-family: Arial, Helvetica, sans-serif !important;
            box-sizing: border-box !important;
        }
        
        html {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .panel-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .text-primary-theme { color: #000000 !important; }
        .print-text-dark { color: #000000 !important; font-weight: bold; }

        .print-official-document-header {
            display: block !important;
        }

        /* 3. FIX: Position footer symmetrically inside the new padding bounds using an explicit width math statement */
        .print-official-signoff-footer {
            display: block !important;
            position: fixed !important;
            bottom: 0.5in !important;  
            left: 0.5in !important;   
            width: calc(100% - 1.0in) !important; /* Compresses box bounds to stay inside the paper margin guidelines */
            background-color: #ffffff !important;
            page-break-inside: avoid !important;
            z-index: 9999;
            box-sizing: border-box !important;
        }

        /* 4. REMOVED MARGIN-BOTTOM SPILLOVER ERRORS: Spacing handled by the tfoot element */
        .deped-structured-grid {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 15px !important;
            margin-bottom: 0 !important; /* Reset margin leak to prevent phantom empty second page */
        }

        .font-official-serif {
            font-family: 'Times New Roman', Times, serif !important;
        }
        
        .deped-structured-grid th {
            background-color: #f5f5f5 !important;
            color: #000000 !important;
            font-weight: bold !important;
            border: 1px solid #000000 !important;
            padding: 6px 8px !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            text-align: center !important;
        }
        
        .deped-structured-grid th:nth-child(2) { text-align: left !important; }
        .deped-structured-grid th.text-end { text-align: right !important; }

        .deped-structured-grid td {
            color: #000000 !important;
            border: 1px solid #000000 !important;
            padding: 6px 8px !important;
            font-size: 11px !important;
            background: transparent !important;
        }

        .deped-structured-grid td.text-end {
            text-align: right !important;
        }

        .border-bottom-signature {
            border-bottom: 1px solid #000000 !important;
        }

        /* 5. Automated Pagination Counters */
        body {
            counter-reset: page;
        }
        .print-page-counter::after {
            counter-increment: page;
            content: "Page " counter(page);
        }
    }
</style>

<script>
function toggleFilterView() {
    const reportType = document.getElementById('report_type').value;
    const catContainer = document.getElementById('categoryFilterContainer');
    
    if (reportType === 'low_stock' || reportType === 'borrowing') {
        catContainer.style.opacity = '0.5';
        catContainer.querySelector('select').disabled = true;
    } else {
        catContainer.style.opacity = '1';
        catContainer.querySelector('select').disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', toggleFilterView);
</script>
@endsection