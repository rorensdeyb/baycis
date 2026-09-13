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

        {{-- R-UI.2: One-Click Report Templates (inside workbench) --}}
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap p-3" style="background:var(--bg-main);border-radius:10px;border:1px solid var(--border-color);">
            <span class="small fw-bold text-secondary" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Quick Templates:</span>
            <button type="button" class="pill" onclick="applyReportTemplate('endOfMonth')" style="font-size:11px;"><i class="bi bi-calendar-check me-1"></i> End-of-Month</button>
            <button type="button" class="pill" onclick="applyReportTemplate('borrowerActivity')" style="font-size:11px;"><i class="bi bi-people me-1"></i> Borrower Activity</button>
            <button type="button" class="pill" onclick="applyReportTemplate('lowStock')" style="font-size:11px;"><i class="bi bi-exclamation-triangle me-1"></i> Low Stock</button>
            <button type="button" class="pill" onclick="applyReportTemplate('damagedAssets')" style="font-size:11px;"><i class="bi bi-tools me-1"></i> Damaged Assets</button>
        </div>
        
        <form action="/admin/reports" method="GET" id="reportGenerationForm">
            <input type="hidden" name="generate" value="true">
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-secondary">Target Report Module</label>
<select name="report_type" id="report_type" class="form-select theme-dynamic-input" onchange="toggleFilterView()">
                <option value="summary" {{ request('report_type') === 'summary' ? 'selected' : '' }}>Inventory Summary Ledger</option>
                <option value="borrowing" {{ request('report_type') === 'borrowing' ? 'selected' : '' }}>Borrower Log Transaction Report</option>
                <option value="low_stock" {{ request('report_type') === 'low_stock' ? 'selected' : '' }}>Low Stock Alerts Ledger</option>
                <option value="consumable_summary" {{ request('report_type') === 'consumable_summary' ? 'selected' : '' }}>Consumable Summary Ledger</option>
                <option value="consumable_low_stock" {{ request('report_type') === 'consumable_low_stock' ? 'selected' : '' }}>Consumable Low Stock Ledger</option>
                <option value="consumable_issuance" {{ request('report_type') === 'consumable_issuance' ? 'selected' : '' }}>Consumable Issuance Log</option>
                <option value="consumable_trend" {{ request('report_type') === 'consumable_trend' ? 'selected' : '' }}>Consumable Trend Report</option>
            </select>
                </div>
                
                <div class="col-md-3 mb-3" id="categoryFilterContainer">
                    <label class="form-label fw-bold text-secondary">Asset Category</label>
                    <select name="category_id" class="form-select theme-dynamic-input">
                        <option value="all" {{ request('category_id') === 'all' ? 'selected' : '' }}>All Classifications</option>
                        @foreach($categories as $cat)
                            @php if (!($cat instanceof \App\Models\Category)) continue; @endphp
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold text-secondary">Status Parameter</label>
                    <select name="status" class="form-select theme-dynamic-input">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All States</option>
                        @foreach($itemStatusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
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
            @elseif($reportType === 'consumable_summary')
                {{-- ISS-6: Consumable Summary Ledger --}}
                <table class="admin-table mb-0 deped-structured-grid">
                    <thead>
                        <tr>
                            <th class="text-center ps-3" style="width: 10%;">#</th>
                            <th style="width: 24%;">ITEM NAME</th>
                            <th class="text-center" style="width: 12%;">CATEGORY</th>
                            <th class="text-center" style="width: 10%;">UNIT</th>
                            <th class="text-center" style="width: 12%;">CURRENT STOCK</th>
                            <th class="text-center" style="width: 12%;">MINIMUM LEVEL</th>
                            <th class="text-center" style="width: 12%;">REORDER LEVEL</th>
                            <th class="text-center" style="width: 12%;">STATUS</th>
                            <th class="text-center" style="width: 12%;">NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRecords as $i => $row)
                        <tr>
                            <td class="text-center ps-3" style="font-size: 11px; font-weight: 600;">{{ $loop->iteration }}</td>
                            <td class="fw-bold" style="font-size: 12px; color: var(--text-primary);">{{ $row['item_name'] }}</td>
                            <td class="text-center">{{ $row['category'] }}</td>
                            <td class="text-center" style="font-size: 12px;">{{ $row['unit'] }}</td>
                            <td class="text-center fw-bold" style="font-size: 12px;">{{ $row['current_stock'] }}</td>
                            <td class="text-center" style="font-size: 12px;">{{ $row['min_stock'] }}</td>
                            <td class="text-center" style="font-size: 12px;">{{ $row['reorder_level'] }}</td>
                            <td class="text-center pe-4 fw-bold">
                                @if($row['status'] === 'OUT OF STOCK')
                                    <span class="text-danger">OUT OF STOCK</span>
                                @elseif($row['status'] === 'LOW STOCK')
                                    <span class="text-warning">LOW STOCK</span>
                                @else
                                    <span class="text-success">IN STOCK</span>
                                @endif
                            </td>
                            <td class="text-center pe-4" style="font-size: 11px; color: var(--text-secondary);">{{ $row['notes'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="d-none d-print-table-footer-group">
                        <tr>
                            <td colspan="9" style="height: 1.8in; border: none !important; background: transparent !important;"></td>
                        </tr>
                    </tfoot>
                </table>
            @elseif($reportType === 'consumable_low_stock')
                {{-- I-LSA.1: Low stock report shows ConsumableStock data --}}
                <table class="admin-table mb-0 deped-structured-grid">
                    <thead>
                        <tr>
                            <th class="text-center ps-3" style="width: 20%;">ITEM NAME</th>
                            <th class="text-center" style="width: 12%;">UNIT</th>
                            <th class="text-center" style="width: 14%;">CURRENT STOCK</th>
                            <th class="text-center" style="width: 14%;">MINIMUM LEVEL</th>
                            <th class="text-center" style="width: 14%;">REORDER LEVEL</th>
                            <th class="text-center pe-4" style="width: 14%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRecords as $stock)
                        <tr>
                            <td class="ps-3 fw-bold" style="color: var(--text-primary); font-size: 13px;">{{ $stock->name }}</td>
                            <td class="text-center text-secondary" style="font-size: 12px;">{{ $stock->unit ?? 'pc(s)' }}</td>
                            <td class="text-center fw-bold" style="font-size: 13px; color: {{ $stock->isOutOfStock() ? 'var(--accent-red)' : ($stock->isLowStock() ? 'var(--accent-yellow)' : 'var(--accent-green)') }};">{{ $stock->stock_quantity }}</td>
                            <td class="text-center text-secondary" style="font-size: 12px;">{{ $stock->min_stock ?? 'N/A' }}</td>
                            <td class="text-center text-secondary" style="font-size: 12px;">{{ $stock->reorder_level ?? 'N/A' }}</td>
                            <td class="text-center pe-4 fw-bold text-uppercase" style="font-size: 11px;">
                                @if($stock->isOutOfStock())
                                    <span class="text-danger">OUT OF STOCK</span>
                                @elseif($stock->isLowStock())
                                    <span class="text-warning">LOW STOCK</span>
                                @else
                                    <span class="text-success">IN STOCK</span>
                                @endif
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
            @elseif($reportType === 'consumable_issuance')
                {{-- ISS-6: Consumable Issuance Log --}}
                <table class="admin-table mb-0 deped-structured-grid">
                    <thead>
                        <tr>
                            <th class="text-center ps-3" style="width: 5%;">#</th>
                            <th style="width: 18%;">PROPERTY TAG</th>
                            <th style="width: 20%;">ITEM / BRAND / MODEL</th>
                            <th class="text-center" style="width: 8%;">UNIT</th>
                            <th class="text-center" style="width: 8%;">QTY</th>
                            <th style="width: 18%;">PURPOSE</th>
                            <th class="text-center" style="width: 10%;">STATUS</th>
                            <th style="width: 14%;">BORROWER</th>
                            <th class="text-center" style="width: 12%;">ISSUED BY</th>
                            <th class="text-center" style="width: 12%;">ISSUED AT</th>
                            <th class="text-center" style="width: 12%;">CONFIRMED AT</th>
                            <th class="text-center pe-4" style="width: 10%;">ADMIN NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRecords as $i => $row)
                        <tr>
                            <td class="text-center ps-3" style="font-size: 11px; font-weight: 600;">{{ $loop->iteration }}</td>
                            <td class="font-monospace fw-bold" style="font-size: 11px;">{{ $row['property_tag'] }}</td>
                            <td>{{ $row['item_name'] }}</td>
                            <td class="text-center">{{ $row['unit'] }}</td>
                            <td class="text-center fw-bold">{{ $row['quantity'] }}</td>
                            <td style="font-size: 12px;">{{ Str::limit($row['purpose'], 40) }}</td>
                            <td class="text-center">
                                @if($row['status'] === 'Confirmed')
                                    <span class="badge rounded-pill bg-success">{{ $row['status'] }}</span>
                                @elseif($row['status'] === 'Cancelled')
                                    <span class="badge rounded-pill bg-secondary">{{ $row['status'] }}</span>
                                @elseif($row['status'] === 'Issued')
                                    <span class="badge bg-warning text-dark">{{ $row['status'] }}</span>
                                @else
                                    <span class="badge bg-info">{{ $row['status'] }}</span>
                                @endif
                            </td>
                            <td style="font-size: 11px;">{{ $row['borrower'] }}</td>
                            <td class="text-center" style="font-size: 11px;">{{ $row['issued_by'] }}</td>
                            <td class="text-center" style="font-size: 11px;">{{ $row['issued_at'] }}</td>
                            <td class="text-center" style="font-size: 11px;">{{ $row['confirmed_at'] ?? '—' }}</td>
                            <td class="text-center pe-4" style="font-size: 10px;">{{ Str::limit($row['admin_notes'], 30) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="d-none d-print-table-footer-group">
                        <tr>
                            <td colspan="13" style="height: 1.8in; border: none !important; background: transparent !important;"></td>
                        </tr>
                    </tfoot>
                </table>
            @elseif($reportType === 'consumable_trend')
                {{-- ISS-6: Consumable Trend Report --}}
                <div class="mb-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);"><i class="bi bi-graph-up me-2"></i> 30-Day Consumption Trend</h6>
                    <div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;background:#fff;">
                        <img src="{{ $trendUrl }}" alt="30-day consumption trend" class="w-100" style="height:190px;object-fit:contain;" loading="lazy">
                    </div>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2">Top Consumed Items (30 days)</h6>
                        <div class="d-flex flex-column gap-2">
                            @forelse($topItems as $i => $item)
                            <div class="d-flex align-items-center justify-content-between gap-2 p-2" style="background:var(--bg-main);border:1px solid var(--border-color);border-radius:8px;">
                                <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:22px;height:22px;border-radius:7px;font-size:11px;
                                    background:{{ $loop->iteration === 1 ? '#fef3c7' : ($loop->iteration === 2 ? '#f1f5f9' : ($loop->iteration === 3 ? '#fde8d7' : 'var(--bg-surface)')) }};
                                    color:{{ $loop->iteration === 1 ? '#b45309' : ($loop->iteration === 2 ? '#475569' : ($loop->iteration === 3 ? '#c2410c' : '#94a3b8')) }};
                                    border:1px solid var(--border-color);width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;">{{ $loop->iteration }}</span>
                                <span class="text-truncate" style="font-size:12.5px;font-weight:600;color:var(--text-primary);">{{ $item->item->name ?? 'Unknown' }}</span>
                                <span class="badge rounded-pill fw-bold flex-shrink-0" style="background:rgba(245,158,11,.12);color:#f59e0b;">{{ $item->total }}</span>
                            </div>
                            @empty
                            <div class="text-center py-4 text-secondary">No consumable issuances in this period.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
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

        /* 2. Hide ALL layout elements globally */
        .sidebar,
        .top-navbar,
        .no-print,
        .floating-help,
        .page-header,
        .alert,
        .btn-close,
        .sidebar-overlay,
        .admin-body,
        .dashboard-wrapper > *:not(.panel-card),
        header,
        aside.sidebar,
        .modal,
        .modal-backdrop,
        .toast-container,
        #appDrawer,
        #appDrawerBackdrop,
        .print-official-document-header + * {
            display: none !important;
        }

        /* 3. Ensure body takes full page with no margins */
        body {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            font-family: Arial, Helvetica, sans-serif !important;
            box-sizing: border-box !important;
            overflow: visible !important;
        }

        html {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* 4. Reset layout structure for print */
        .admin-body,
        .dashboard-wrapper {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .panel-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
        }

        .text-primary-theme { color: #000000 !important; }
        .print-text-dark { color: #000000 !important; font-weight: bold; }

        .print-official-document-header {
            display: block !important;
        }

        /* 5. FIX: Position footer symmetrically inside the new padding bounds */
        .print-official-signoff-footer {
            display: block !important;
            position: fixed !important;
            bottom: 0.5in !important;
            left: 0.5in !important;
            width: calc(100% - 1.0in) !important;
            background-color: #ffffff !important;
            page-break-inside: avoid !important;
            z-index: 9999;
            box-sizing: border-box !important;
        }

        /* 6. DEPED structured grid print styles */
        .deped-structured-grid {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 15px !important;
            margin-bottom: 0 !important;
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

        /* 7. Ensure the printable report wrapper takes full page */
        .printable-report-wrapper {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* 8. Hide the data stream header bar in print */
        .no-print {
            display: none !important;
        }

        /* 9. Automated Pagination Counters */
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

// R-UI.2: One-Click Report Templates
function applyReportTemplate(name) {
    const templates = {
        'endOfMonth': { report_type: 'summary', category_id: 'all', status: 'all', start_date: '', end_date: '' },
        'borrowerActivity': { report_type: 'borrowing', category_id: 'all', status: 'all', start_date: '', end_date: '' },
        'lowStock': { report_type: 'low_stock', category_id: 'all', status: 'all', start_date: '', end_date: '' },
        'damagedAssets': { report_type: 'summary', category_id: 'all', status: 'damaged', start_date: '', end_date: '' }
    };
    const t = templates[name];
    if (!t) return;
    
    // Set date defaults for endOfMonth
    if (name === 'endOfMonth') {
        var now = new Date();
        t.start_date = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        t.end_date = now.toISOString().split('T')[0];
    }
    // Set date defaults for borrowerActivity (this quarter)
    if (name === 'borrowerActivity') {
        var now = new Date();
        var qStart = Math.floor(now.getMonth() / 3) * 3;
        t.start_date = new Date(now.getFullYear(), qStart, 1).toISOString().split('T')[0];
        t.end_date = now.toISOString().split('T')[0];
    }
    
    Object.entries(t).forEach(function(_ref) {
        var key = _ref[0], val = _ref[1];
        var el = document.querySelector('[name="' + key + '"]');
        if (el) el.value = val;
    });
    
    toggleFilterView();
    
    // Show toast and auto-submit
    showToast('Template Applied', 'Filter settings configured for selected report template', 'info');
    
    // Save to report history (R-UI.3)
    saveReportToHistory(name, t);
}

// R-UI.3: Report History & Favorites
function saveReportToHistory(name, config) {
    var label = name.replace(/([A-Z])/g, ' $1').replace(/^./, function(s) { return s.toUpperCase(); });
    var entry = {
        label: label,
        config: config,
        timestamp: Date.now()
    };
    var history = JSON.parse(localStorage.getItem('report-history') || '[]');
    history.unshift(entry);
    if (history.length > 20) history = history.slice(0, 20);
    localStorage.setItem('report-history', JSON.stringify(history));
    renderReportHistory();
}

function renderReportHistory() {
    var history = JSON.parse(localStorage.getItem('report-history') || '[]');
    var container = document.getElementById('reportHistoryContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'reportHistoryContainer';
        container.className = 'panel-card p-3 mb-4 no-print';
        container.style.cssText = 'border-radius:12px;';
        
        // Insert after workbench
        var workbench = document.querySelector('.panel-card:has([name="report_type"])') || 
                        document.querySelector('[id="reportGenerationForm"]')?.closest('.panel-card');
        if (workbench) {
            workbench.parentNode.insertBefore(container, workbench.nextSibling);
        } else {
            document.querySelector('.dashboard-wrapper').appendChild(container);
        }
    }
    
    if (history.length === 0) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';
    
    var html = '<h6 class="fw-bold mb-3" style="color:var(--text-primary);font-size:14px;"><i class="bi bi-clock-history me-2 text-secondary"></i>Recent Reports</h6>';
    html += '<div class="d-flex flex-wrap gap-2">';
    history.forEach(function(entry, idx) {
        html += '<button type="button" class="btn btn-sm d-flex align-items-center gap-1" style="border:1px solid var(--border-color);border-radius:8px;background:var(--bg-surface);color:var(--text-primary);font-size:11px;padding:6px 12px;" onclick="restoreReport(' + idx + ')">';
        html += '<i class="bi bi-file-earmark-text text-secondary"></i> ' + entry.label;
        html += '<span class="ms-1 text-secondary" style="font-size:9px;">' + new Date(entry.timestamp).toLocaleDateString() + '</span>';
        html += '<button class="btn btn-sm p-0 ms-1" onclick="event.stopPropagation();deleteReport(' + idx + ')" style="color:var(--accent-red);">&times;</button>';
        html += '</button>';
    });
    html += '</div>';
    html += '<div class="mt-2"><button class="btn btn-sm" onclick="localStorage.removeItem(\'report-history\');renderReportHistory();" style="font-size:10px;color:var(--text-secondary);border:none;background:none;">Clear history</button></div>';
    container.innerHTML = html;
}

function restoreReport(idx) {
    var history = JSON.parse(localStorage.getItem('report-history') || '[]');
    var entry = history[idx];
    if (!entry || !entry.config) return;
    Object.entries(entry.config).forEach(function(_ref) {
        var key = _ref[0], val = _ref[1];
        var el = document.querySelector('[name="' + key + '"]');
        if (el) el.value = val;
    });
    toggleFilterView();
    showToast('Report Restored', 'Filter settings restored from history', 'success');
}

function deleteReport(idx) {
    var history = JSON.parse(localStorage.getItem('report-history') || '[]');
    history.splice(idx, 1);
    localStorage.setItem('report-history', JSON.stringify(history));
    renderReportHistory();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleFilterView();
    renderReportHistory();
});
</script>
@endsection
