<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Print Studio — Property Tags</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --ink:#182230; --sub:#66707f; --line:#e3e8ee; --card:#fff;
            --accent:#1b3550; --accent-dark:#14273a; --green:#177a50; --amber:#b45309;
            --canvas:#f6f8fa;
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'DM Sans','Segoe UI',Arial,sans-serif; background:var(--canvas); color:var(--ink); }

        .topbar { position:sticky; top:0; z-index:60; display:flex; align-items:center; gap:14px;
                  padding:10px 18px; background:#ffffffee; backdrop-filter:blur(6px);
                  border-bottom:1px solid var(--line); }
        .topbar .brand { font-weight:800; font-size:15px; }
        .topbar .brand i { color:var(--accent); margin-right:6px; }
        .topbar a.back { font-size:12.5px; color:var(--sub); text-decoration:none; }
        .topbar a.back:hover { color:var(--ink); }
        .chip { font-size:11.5px; font-weight:700; padding:5px 12px; border-radius:99px;
                background:#eaf2f9; color:var(--accent); border:1px solid var(--line); white-space:nowrap; }
        .spacer { flex:1; }
        .zoomctl { display:flex; align-items:center; gap:7px; font-size:11.5px; color:var(--sub); }
        .btn { border:none; cursor:pointer; font-weight:700; border-radius:9px; font-size:13px;
               padding:9px 18px; transition:.15s; display:inline-flex; align-items:center; gap:7px; }
        .btn-green { background:var(--green); color:#fff; } .btn-green:hover { background:#059669; }
        .btn-blue { background:var(--accent); color:#fff; } .btn-blue:hover { background:var(--accent-dark); }
        .btn-gray { background:#fff; color:var(--ink); border:1px solid var(--line); }
        .btn-red-t { background:transparent; color:#dc2626; }

        .wrap { display:flex; align-items:flex-start; }
        .panel { width:330px; flex-shrink:0; position:sticky; top:103px; max-height:calc(100vh - 103px);
                 overflow-y:auto; padding:14px; background:#f8fafc; border-right:1px solid var(--line); }
        .canvas { flex:1; min-width:0; padding:34px 30px 90px; overflow:auto; }

        /* ── Tools toolbar (attached flush under the header) ── */
        .toolbar { position:sticky; top:57px; z-index:45; display:flex; align-items:center; gap:4px;
                   flex-wrap:wrap; padding:7px 16px; background:#ffffffee;
                   backdrop-filter:blur(6px); border-bottom:1px solid var(--line);
                   box-shadow:0 3px 14px rgba(16,24,40,.07); }
        .tb-btn { border:none; background:transparent; color:#374151; font-size:12px; font-weight:700;
                  padding:7px 11px; border-radius:8px; cursor:pointer; display:inline-flex;
                  align-items:center; gap:6px; white-space:nowrap; }
        .tb-btn:hover, .tb-btn.on { background:#eaf2f9; color:var(--accent); }
        .tb-btn.danger:hover { background:#fef2f2; color:#dc2626; }
        .tb-btn:disabled { opacity:.45; cursor:not-allowed; }
        .tb-sep { width:1px; height:22px; background:var(--line); margin:0 5px; flex-shrink:0; }
        .tb-dd { position:relative; }
        .tb-menu { position:absolute; top:calc(100% + 6px); left:0; min-width:200px; background:#fff;
                   border:1px solid var(--line); border-radius:10px; box-shadow:0 12px 32px rgba(16,24,40,.18);
                   padding:5px; display:none; z-index:70; }
        .tb-dd.open .tb-menu { display:block; }
        .tb-dd.right .tb-menu { left:auto; right:0; }
        .tb-menu button { display:flex; align-items:center; gap:9px; width:100%; text-align:left; border:none;
                          background:transparent; padding:8px 10px; font-size:12.5px; font-weight:600;
                          color:#374151; border-radius:7px; cursor:pointer; }
        .tb-menu button:hover { background:#eaf2f9; color:var(--accent); }
        .tb-menu button i { width:16px; text-align:center; color:var(--sub); }
        .tb-menu button:hover i { color:var(--accent); }
        .tb-menu .divider { height:1px; background:var(--line); margin:5px 4px; }
        .rotate-90 { transform:rotate(90deg); }

        details.card { background:var(--card); border:1px solid var(--line); border-radius:12px;
                       margin-bottom:10px; overflow:hidden; }
        details.card summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:9px;
                               padding:11px 14px; font-size:12px; font-weight:800; letter-spacing:.07em;
                               text-transform:uppercase; color:#374151; user-select:none; }
        details.card summary::-webkit-details-marker { display:none; }
        details.card summary i.chev { margin-left:auto; transition:.2s; color:var(--sub); font-size:11px; }
        details.card[open] summary i.chev { transform:rotate(90deg); }
        details.card .card-body { padding:4px 14px 14px; border-top:1px solid var(--line); }
        .fld { margin-top:11px; }
        .fld > label { display:block; font-size:10.5px; font-weight:700; letter-spacing:.05em;
                       text-transform:uppercase; color:var(--sub); margin-bottom:5px; }
        select, input[type=text], input[type=number] { width:100%; padding:7px 9px; font-size:13px;
               border:1px solid var(--line); border-radius:8px; background:#fff; color:var(--ink); outline:none; }
        select:focus, input:focus { border-color:var(--accent); }
        .row2 { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .row3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
        .seg { display:grid; grid-template-columns:1fr 1fr; background:#f1f5f9; border:1px solid var(--line);
               border-radius:9px; overflow:hidden; }
        .seg button { border:none; background:transparent; padding:7px; font-size:12px; font-weight:700;
                      cursor:pointer; color:var(--sub); }
        .seg button.on { background:var(--ink); color:#fff; }
        .chk { display:flex; align-items:center; gap:8px; font-size:13px; padding:4px 0; cursor:pointer; }
        .chk input { width:15px; height:15px; accent-color:var(--accent); }
        input[type=range] { width:100%; accent-color:var(--accent); }
        .hint { font-size:11px; color:var(--sub); margin-top:4px; line-height:1.45; }
        .frow { display:flex; align-items:center; gap:8px; margin-top:6px; }
        .frow-l { width:104px; flex-shrink:0; font-size:11.5px; color:#374151; }
        .frow input[type=range] { flex:1; min-width:0; }
        .frow-v { width:40px; text-align:right; font-size:11px; font-weight:700; color:var(--accent-dark);
                  font-family:ui-monospace,Menlo,monospace; }
        .btn-xs { padding:5px 10px; font-size:11.5px; border-radius:7px; }
        #scopeActions { display:flex; }
        #scopeActions[style*="none"] { display:none !important; }
        .ratio-chips { display:flex; gap:6px; flex-wrap:wrap; margin-top:7px; }
        .ratio-chips button { border:1px solid var(--line); background:#fff; border-radius:99px;
                              font-size:11.5px; font-weight:700; padding:4px 12px; cursor:pointer; color:#374151; }
        .ratio-chips button.on { background:var(--ink); color:#fff; border-color:var(--ink); }
        #ratioDims { font-size:12px; font-weight:800; color:var(--accent-dark); margin-top:7px;
                     font-family:ui-monospace,Menlo,Consolas,monospace; }

        .queue { max-height:190px; overflow-y:auto; border:1px solid var(--line); border-radius:9px; }
        .qrow { display:flex; align-items:center; gap:8px; padding:6px 9px; border-bottom:1px solid #f1f5f9; }
        .qrow:last-child { border-bottom:none; }
        .qrow .qn { flex:1; min-width:0; }
        .qrow .qt { font-family:ui-monospace,Menlo,Consolas,monospace; font-size:11px; font-weight:700; }
        .qrow .qm { font-size:10.5px; color:var(--sub); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .qrow input { width:52px; padding:3px 6px; font-size:12px; text-align:center; }
        .qrow .place-btn { border:1px solid var(--line); background:#eaf2f9; color:var(--accent-dark); font-size:10.5px;
                           font-weight:700; border-radius:7px; padding:3px 8px; cursor:pointer; white-space:nowrap; }
        .qrow .rm { border:none; background:transparent; color:#cbd5e1; cursor:pointer; font-size:13px; }
        .qrow .rm:hover { color:#dc2626; }
        .addbox { position:relative; }
        .addresults { position:absolute; z-index:30; left:0; right:0; top:calc(100% + 4px); background:#fff;
                      border:1px solid var(--line); border-radius:10px; box-shadow:0 10px 26px rgba(16,24,40,.16);
                      overflow:hidden; display:none; }
        .addres { padding:8px 11px; cursor:pointer; font-size:12.5px; border-bottom:1px solid #f1f5f9; }
        .addres:last-child { border-bottom:none; }
        .addres:hover { background:#eaf2f9; }
        .addres b { font-family:ui-monospace,Menlo,monospace; font-size:11.5px; }
        .banner { display:none; font-size:12px; font-weight:600; padding:9px 12px; border-radius:9px; margin-top:10px; }
        .banner.warn { display:block; background:#fffbeb; color:#b45309; border:1px solid rgba(245,158,11,.4); }
        .banner.info { display:block; background:#eaf2f9; color:var(--accent-dark); border:1px solid var(--line); }

        /* ── Sheet stack & rulers ── */
        #sheetStack { display:flex; flex-direction:column; align-items:center; gap:34px; }
        .sheetZone { position:relative; }
        .rulerWrap { position:relative; display:inline-block; }
        .ruler-x, .ruler-y { position:absolute; pointer-events:none; font-size:8.5px; color:#94a3b8; user-select:none; z-index:3; }
        .ruler-x { top:-16px; left:0; right:0; height:14px; border-bottom:1px solid #cbd5e1; }
        .ruler-y { top:0; bottom:0; left:-18px; width:16px; border-right:1px solid #cbd5e1; }
        .ruler-x span { position:absolute; top:0; transform:translateX(-50%); }
        .ruler-x span::before { content:''; position:absolute; left:50%; top:9px; height:5px; border-left:1px solid #cbd5e1; }
        .ruler-y span { position:absolute; left:0; writing-mode:vertical-rl; font-size:8px; }
        .ruler-y span::before { content:''; position:absolute; top:0; left:10px; width:5px; border-top:1px solid #cbd5e1; }
        .sheetLabel { text-align:center; font-size:11px; font-weight:700; letter-spacing:.06em;
                      text-transform:uppercase; color:var(--sub); margin-top:9px; }
        .studio-sheet { position:relative; background:#fff; box-shadow:0 8px 26px rgba(16,24,40,.16); overflow:hidden; }
        .studio-sheet::before { content:''; position:absolute; inset:0; pointer-events:none; z-index:0;
            background-image:
                repeating-linear-gradient(to right, rgba(27,53,80,.12) 0 1px, transparent 1px 50mm),
                repeating-linear-gradient(to bottom, rgba(27,53,80,.12) 0 1px, transparent 1px 50mm),
                repeating-linear-gradient(to right, rgba(27,53,80,.05) 0 1px, transparent 1px 10mm),
                repeating-linear-gradient(to bottom, rgba(27,53,80,.05) 0 1px, transparent 1px 10mm); }
        .addPageRow { text-align:center; }
        .addPageBtn { border:1px dashed #94a3b8; background:transparent; color:var(--sub); font-weight:700;
                      font-size:12.5px; border-radius:10px; padding:10px 26px; cursor:pointer; }
        .addPageBtn:hover { color:var(--accent-dark); border-color:var(--accent); background:#eaf2f9; }

        /* ── Placed tag widget ── */
        .placed { position:absolute; z-index:10; cursor:grab; }
        .placed.dragging { cursor:grabbing; z-index:80; }
        .placed .tag-container { width:100%; height:100%; background:#fff; border:1px solid #000;
                display:flex; flex-direction:column; font-family:Arial,sans-serif; color:#000;
                pointer-events:none; box-shadow:0 3px 10px rgba(16,24,40,.18); }
        .placed.sel .tag-container { outline:2px solid var(--accent); outline-offset:2px; }
        .placed .rz { position:absolute; right:-7px; bottom:-7px; width:14px; height:14px; z-index:20;
                background:#fff; border:2px solid var(--accent); border-radius:4px; cursor:nwse-resize; display:none; }
        .placed.sel .rz, .placed:hover .rz { display:block; }
        .placed .wx { position:absolute; top:-9px; right:-9px; width:20px; height:20px; z-index:21;
                background:#dc2626; color:#fff; border:none; border-radius:50%; cursor:pointer;
                display:none; align-items:center; justify-content:center; font-size:11px; line-height:1; }
        .placed.sel .wx { display:flex; }
        .dimBadge { position:fixed; z-index:300; background:var(--ink); color:#fff; font-size:10.5px;
                    font-weight:700; font-family:ui-monospace,Menlo,monospace; padding:3px 8px;
                    border-radius:6px; pointer-events:none; white-space:nowrap; display:none; }

        .studio-cell .tt { width:100%; height:100%; }
        .placed .tag-header { text-align:center; font-weight:bold; padding:calc(4px*var(--m));
                border-bottom:1px solid #000; font-size:calc(12px*var(--m)*var(--fk-hdr,1)); line-height:1.2;
                white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
                -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .placed .tag-body { flex:1; display:flex; align-items:stretch; min-height:0; }
        .placed .left-panel { width:35%; border-right:1px solid #000; display:flex; flex-direction:column;
                align-items:center; justify-content:space-between; gap:3px;
                padding:calc(4px*var(--m)) calc(3px*var(--m)); overflow:hidden; }
        .placed .left-panel img.js-logo { width:74%; height:auto; flex-shrink:0; max-height:46%; object-fit:contain; }
        .placed .barcode-placeholder { width:100%; text-align:center; margin-top:auto; }
        .placed .barcode-placeholder img { max-width:100%; max-height:calc(26px*var(--m)); object-fit:contain; display:block; margin:0 auto; }
        .placed .barcode-placeholder small { font-weight:normal; font-size:calc(7px*var(--m)*var(--fk-bctext,1)); letter-spacing:.04em; display:inline-block; }
        .placed .right-panel { width:65%; display:flex; }
        .placed .right-panel table { width:100%; height:100%; border-collapse:collapse; table-layout:fixed; }
        .placed .right-panel td { border-bottom:1px solid #000; padding:calc(2px*var(--m)) calc(4px*var(--m));
                font-size:calc(8.5px*var(--m)*var(--fk-cells,1)); line-height:1.25; vertical-align:middle;
                overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .placed .right-panel td:first-child { border-right:1px solid #000; width:42%; font-weight:600; }
        .placed .right-panel tr:last-child td { border-bottom:none; }
        .placed .right-panel td strong { font-size:calc(9.5px*var(--m)*var(--fk-pnum,1)); letter-spacing:.02em; }
        .placed .tag-footer { text-align:center; font-weight:bold; font-size:calc(7px*var(--m)*var(--fk-footer,1));
                padding:calc(2.5px*var(--m)); border-top:1px solid #000; white-space:nowrap; overflow:hidden; }
        .placed .tag-footer span { border-bottom:1px solid #f8aba6; }

        .empty-state { text-align:center; padding:110px 30px; color:var(--sub); }
        .empty-state i { font-size:46px; color:#c3cad4; }
        .empty-state h3 { margin:14px 0 6px; font-size:17px; color:var(--ink); }
        .empty-state p { font-size:13px; max-width:420px; margin:0 auto; line-height:1.55; }

        .modal-bd { position:fixed; inset:0; z-index:200; background:rgba(16,24,40,.48);
                    display:none; align-items:center; justify-content:center; }
        .modal-bd.open { display:flex; }
        .modal-cd { width:min(480px, calc(100vw - 40px)); background:#fff; border-radius:16px;
                    box-shadow:0 24px 60px rgba(16,24,40,.3); animation:pop .18s ease-out; }
        @keyframes pop { from { transform:scale(.94); opacity:0; } }
        .modal-hd { padding:18px 22px 4px; font-size:16px; font-weight:800; }
        .modal-bd .modal-body { padding:10px 22px 6px; font-size:13px; color:#374151; line-height:1.6; }
        .modal-body ul { margin:8px 0; padding-left:18px; }
        .modal-body li { margin:5px 0; }
        .modal-ft { display:flex; justify-content:flex-end; gap:9px; padding:14px 22px 20px; }

        @media print {
            body { background:#fff; }
            .topbar, .panel, .toolbar, .no-print { display:none !important; }
            .wrap { display:block; }
            .canvas { padding:0; overflow:visible; }
            #sheetStack { display:block; zoom:1 !important; }
            .sheetZone { page-break-after:always; break-after:page; }
            .sheetZone:last-child { page-break-after:auto; break-after:auto; }
            .studio-sheet { box-shadow:none; }
            .studio-sheet::before { content:none !important; }
            .placed { cursor:default; }
            .placed .tag-container { box-shadow:none; }
            body.print-guides .placed::after { content:''; position:absolute; inset:-1px;
                border:1px dashed #999; z-index:30; }
            .rz, .wx, .dimBadge { display:none !important; }
            .placed.sel .tag-container { outline:none; }
            * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }

        /* Export mode — rules travel ON the sheet because html2canvas
           clones only the target node (ancestor body classes are lost). */
        .studio-sheet.exporting::before { content:none !important; }
        .studio-sheet.exporting { box-shadow:none !important; }
        .studio-sheet.exporting .rz,
        .studio-sheet.exporting .wx { display:none !important; }
        .studio-sheet.exporting .placed.sel .tag-container { outline:none !important; }
        body.exporting .toolbar, body.exporting .panel, body.exporting .topbar,
        body.exporting .no-print, body.exporting .dimBadge, body.exporting .sheetLabel { display:none !important; }
        body.exporting #sheetStack { zoom:1 !important; }
    </style>
    <style id="pageRule">@page { size: A4 portrait; margin: 0; }</style>
</head>
<body>

<div class="topbar no-print">
    <span class="brand"><i class="bi bi-grid-3x3-gap-fill"></i>Print Studio</span>
    <a class="back" href="/admin/inventory"><i class="bi bi-arrow-left"></i> Inventory</a>
    <span class="chip" id="sumChip">—</span>
    <div class="spacer"></div>
    <div class="zoomctl">
        <i class="bi bi-zoom-out"></i>
        <input type="range" id="zoomCtl" min="35" max="130" step="5" style="width:120px;">
        <span id="zoomVal">65%</span>
    </div>
    <button class="btn btn-green" id="printBtn"><i class="bi bi-printer-fill"></i> Print</button>
</div>

{{-- Tools toolbar — attached flush under the header, screen only --}}
<div class="toolbar no-print" id="toolbar">
    <span class="tb-title">Tools</span>
    <button class="tb-btn" id="tbArrange" title="Re-pack every tag into a clean grid"><i class="bi bi-magic"></i> Auto-arrange</button>
    <button class="tb-btn" id="tbAddPage" title="Append an empty sheet"><i class="bi bi-file-plus"></i> Blank page</button>
    <button class="tb-btn" id="tbDuplicate" title="Duplicate selected tag beside it (Ctrl+D)"><i class="bi bi-copy"></i> Duplicate</button>
    <div class="tb-sep"></div>
    <div class="tb-dd" id="ddAlign">
        <button class="tb-btn" data-dd="ddAlign"><i class="bi bi-align-start"></i> Align <i class="bi bi-chevron-down" style="font-size:10px;"></i></button>
        <div class="tb-menu">
            <button data-align="left"><i class="bi bi-align-start"></i> Left edge</button>
            <button data-align="centerx"><i class="bi bi-align-center"></i> Center horizontally</button>
            <button data-align="right"><i class="bi bi-align-end rotate-90"></i> Right edge</button>
            <div class="divider"></div>
            <button data-align="top"><i class="bi bi-align-top"></i> Top edge</button>
            <button data-align="centery"><i class="bi bi-align-center rotate-90"></i> Center vertically</button>
            <button data-align="bottom"><i class="bi bi-align-bottom"></i> Bottom edge</button>
        </div>
    </div>
    <button class="tb-btn" id="tbCenter" title="Center selected tag on the page"><i class="bi bi-crosshair"></i> Center page</button>
    <div class="tb-sep"></div>
    <button class="tb-btn" id="tbFront" title="Bring selected to front"><i class="bi bi-box-arrow-up-right"></i></button>
    <button class="tb-btn" id="tbBack" title="Send selected behind others"><i class="bi bi-box-arrow-in-down-left"></i></button>
    <button class="tb-btn danger" id="tbDelete" title="Remove selected tag from sheet (Del)"><i class="bi bi-trash3"></i></button>
    <div class="tb-sep"></div>
    <button class="tb-btn" id="tbSnap" title="Toggle 1mm snapping (hold Alt while dragging to bypass)"><i class="bi bi-grid-1x2"></i> Snap</button>
    <div class="spacer"></div>
    <button class="tb-btn" id="tbImportExcel" title="Import tags from an Excel file"><i class="bi bi-file-earmark-arrow-up text-success"></i> Import Excel</button>
    <input type="file" id="importFileInput" accept=".xlsx,.xlsm" style="display:none;">
    <div class="tb-dd right" id="ddExport">
        <button class="tb-btn" data-dd="ddExport"><i class="bi bi-download"></i> Export <i class="bi bi-chevron-down" style="font-size:10px;"></i></button>
        <div class="tb-menu">
            <button data-export="pdf"><i class="bi bi-file-earmark-pdf text-danger"></i> PDF — all pages</button>
            <button data-export="png-page"><i class="bi bi-file-earmark-image text-primary"></i> PNG — current page</button>
            <button data-export="png-all"><i class="bi bi-images text-primary"></i> PNG — all pages</button>
            <div class="divider"></div>
                    <button data-export="json"><i class="bi bi-filetype-json text-secondary"></i> Layout file (.json)</button>
                    <div class="divider"></div>
                    <button data-export="xlsx"><i class="bi bi-file-earmark-excel text-success"></i> Excel — tag list (.xlsx)</button>
        </div>
    </div>
</div>

<div class="wrap">
    {{-- ══════════ CONTROL PANEL ══════════ --}}
    <aside class="panel no-print">

        <details class="card" open>
            <summary><i class="bi bi-collection"></i> Assets <i class="bi bi-chevron-right chev"></i></summary>
            <div class="card-body">
                <div class="fld addbox">
                    <label>Quick add</label>
                    <input type="text" id="quickAdd" placeholder="Search name or property tag…">
                    <div class="addresults" id="addResults"></div>
                </div>
                <div class="fld">
                    <label>Queue &amp; copies</label>
                    <div class="queue" id="assetList"></div>
                    <div style="display:flex;gap:8px;margin-top:8px;">
                        <select id="sortSel" data-k="sort" style="flex:1;">
                            <option value="queue">Keep queue order</option>
                            <option value="tag">Property tag A→Z</option>
                            <option value="name">Item name A→Z</option>
                            <option value="category">Category</option>
                        </select>
                        <button class="btn btn-red-t" id="clearAssets" title="Remove all"><i class="bi bi-trash3"></i></button>
                    </div>
                    <div class="hint"><i class="bi bi-info-circle"></i> <b>Place</b> adds a tag onto the sheet at the current ratio · copies multiply a tag.</div>
                </div>
                <div class="banner info" id="truncBanner">@if($truncated)Selection exceeded 200 assets — showing the first 200.@endif</div>
                <div class="banner" id="dupBanner"><i class="bi bi-exclamation-triangle"></i> Duplicate property tags detected.</div>
            </div>
        </details>

        <details class="card" open>
            <summary><i class="bi bi-aspect-ratio"></i> Tag Size — Ratio <i class="bi bi-chevron-right chev"></i></summary>
            <div class="card-body">
                <div class="fld">
                    <label>Scale vs original — <span id="ratioVal">50%</span></label>
                    <input type="range" id="ratioRange" data-k="ratio" min="20" max="200" step="5">
                    <div class="ratio-chips">
                        <button data-ratio="40">40%</button>
                        <button data-ratio="50">50%</button>
                        <button data-ratio="75">75%</button>
                        <button data-ratio="100">100%</button>
                        <button data-ratio="150">150%</button>
                    </div>
                    <div id="ratioDims">—</div>
                    <div class="hint">Original design: <b>{{ $baseW }}×{{ $baseH }}mm</b>. Contents shrink/grow with the tag — nothing is ever cut off.</div>
                </div>
                <div class="fld">
                    <label>Margins &amp; gaps (mm)</label>
                    <div class="row3">
                        <input type="number" id="marginIn" data-k="margin" min="0" max="25" step="0.5" title="Page margin">
                        <input type="number" id="gapXIn" data-k="gapX" min="0" max="20" step="0.5" title="Gap X">
                        <input type="number" id="gapYIn" data-k="gapY" min="0" max="20" step="0.5" title="Gap Y">
                    </div>
                    <div class="hint">margin · gap X · gap Y</div>
                </div>
                <button class="btn btn-gray" id="autoArrangeBtn" style="width:100%;margin-top:12px;">
                    <i class="bi bi-magic"></i> Auto-arrange all tags
                </button>
                <div class="hint"><i class="bi bi-hand-index"></i> Tags are interactive: <b>drag</b> to move · <b>drag corner dot</b> to resize · press <b>Delete</b> to remove selected. Hold <b>Alt</b> while dragging to disable snapping.</div>
            </div>
        </details>

        <details class="card">
            <summary><i class="bi bi-file-earmark-text"></i> Paper &amp; Page <i class="bi bi-chevron-right chev"></i></summary>
            <div class="card-body">
                <div class="fld">
                    <label>Paper size</label>
                    <select id="paperSel" data-k="paper">
                        <option value="a4">A4 · 210×297mm</option>
                        <option value="letter">Letter · 8.5×11"</option>
                        <option value="legal">Legal · 8.5×14"</option>
                        <option value="a5">A5</option>
                        <option value="a6">A6</option>
                        <option value="custom">Custom…</option>
                    </select>
                </div>
                <div class="fld" id="grp-customPaper" style="display:none;">
                    <label>Custom paper (mm)</label>
                    <div class="row2">
                        <input type="number" id="paperW" data-k="customW" min="40" max="600">
                        <input type="number" id="paperH" data-k="customH" min="40" max="900">
                    </div>
                </div>
                <div class="fld">
                    <label>Orientation</label>
                    <div class="seg">
                        <button data-orient="p" id="orP"><i class="bi bi-file-portrait"></i> Portrait</button>
                        <button data-orient="l" id="orL"><i class="bi bi-file-landscape"></i> Landscape</button>
                    </div>
                </div>
            </div>
        </details>

        <details class="card">
            <summary><i class="bi bi-sliders"></i> Tag Content <i class="bi bi-chevron-right chev"></i></summary>
            <div class="card-body">
                <div id="scopeBox" style="border:1px solid var(--line);border-radius:9px;padding:9px 11px;margin-top:10px;background:#f8fafc;">
                    <div id="scopeText" style="font-size:12px;font-weight:700;color:#374151;line-height:1.4;">
                        <i class="bi bi-globe2" style="color:var(--sub);margin-right:5px;"></i>Global defaults — changes affect every tag.
                    </div>
                    <div id="scopeActions" style="display:none;gap:6px;margin-top:8px;flex-wrap:wrap;">
                        <button class="btn btn-gray btn-xs" id="resetTagBtn" title="Clear this tag's overrides"><i class="bi bi-arrow-counterclockwise"></i> Reset tag</button>
                        <button class="btn btn-blue btn-xs" id="applyAllBtn" title="Make this tag's settings global and clear overrides"><i class="bi bi-files"></i> Apply to all</button>
                        <button class="btn btn-red-t btn-xs" id="clearSelBtn"><i class="bi bi-x-lg"></i> Done</button>
                    </div>
                </div>

                <div class="chk"><input type="checkbox" id="f_serial" data-f="serial" checked><label for="f_serial">Serial Number row</label></div>
                <div class="chk"><input type="checkbox" id="f_cost" data-f="cost" checked><label for="f_cost">Acquisition Cost row</label></div>
                <div class="chk"><input type="checkbox" id="f_date" data-f="date" checked><label for="f_date">Acquisition Date row</label></div>
                <div class="chk"><input type="checkbox" id="f_personnel" data-f="personnel" checked><label for="f_personnel">Accountable Personnel row</label></div>
                <div class="chk"><input type="checkbox" id="f_signature" data-f="signature"><label for="f_signature">Validation Signature row</label></div>
                <div class="chk"><input type="checkbox" id="f_logo" data-f="logo" checked><label for="f_logo">DepEd logo</label></div>

                <div class="fld">
                    <label>Header text</label>
                    <input type="text" id="headerTpl" data-k="headerText" placeholder="{supplier}">
                    <div class="hint">Use <b>{supplier}</b> to inject each asset's fund source.</div>
                </div>
                <div class="chk"><input type="checkbox" id="hdrOverride" data-k="headerOverride"><label for="hdrOverride">Override header color</label></div>
                <div class="fld" id="grp-hdrColor" style="display:none;">
                    <input type="color" id="hdrColor" data-k="headerColorHex" value="#ffff00" style="width:100%;height:34px;border:1px solid var(--line);border-radius:8px;background:#fff;padding:3px;">
                </div>

                <div class="fld">
                    <label>Content boost — <span id="fsVal">100%</span></label>
                    <input type="range" id="fontScale" data-k="fontScale" min="70" max="130" step="5">
                    <div class="hint">Fine-tune content size on top of the ratio.</div>
                </div>

                <div class="fld">
                    <label>Font sizes (per element)</label>
                    @foreach([['header','Header text'],['pnum','Property number'],['cells','Table rows'],['bctext','Barcode label'],['footer','Footer line']] as [$fk, $fl])
                    <div class="frow">
                        <span class="frow-l">{{ $fl }}</span>
                        <input type="range" min="60" max="180" step="5" value="100" data-fk="{{ $fk }}">
                        <span class="frow-v" id="fv_{{ $fk }}">100%</span>
                    </div>
                    @endforeach
                    <div class="hint">Each element scales independently, on top of the tag ratio.</div>
                </div>
                <div class="fld">
                    <label>Barcode width</label>
                    <select id="barDensity" data-k="barDensity">
                        <option value="auto">Auto (fit tag width)</option>
                        <option value="0.6">Fine · 0.6</option>
                        <option value="0.8">Small · 0.8</option>
                        <option value="1">Normal · 1.0</option>
                        <option value="1.5">Bold · 1.5</option>
                        <option value="2">Heavy · 2.0</option>
                    </select>
                </div>
            </div>
        </details>

        <details class="card">
            <summary><i class="bi bi-tools"></i> Advanced <i class="bi bi-chevron-right chev"></i></summary>
            <div class="card-body">
                <div class="fld">
                    <label>Offset before first tag (slots)</label>
                    <input type="number" id="skipFirst" data-k="skipFirst" min="0" max="400" value="0">
                    <div class="hint">For partially-used sticker sheets — auto-arrange leaves this many tag-widths empty at the start.</div>
                </div>
                <div class="chk"><input type="checkbox" id="snapChk" data-k="snap" checked><label for="snapChk">Snap to grid (1mm)</label></div>
                <div class="chk"><input type="checkbox" id="printGuides" data-k="printGuides"><label for="printGuides">Print cut guides</label></div>
            </div>
        </details>

        <details class="card">
            <summary><i class="bi bi-bookmark-star"></i> Layout Presets <i class="bi bi-chevron-right chev"></i></summary>
            <div class="card-body">
                <div class="fld">
                    <label>Saved layouts</label>
                    <select id="loadPresetSel"><option value="">— Load a saved layout —</option></select>
                </div>
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <input type="text" id="presetName" placeholder="Layout name…" style="flex:1;">
                    <button class="btn btn-blue" id="savePresetBtn" title="Save current settings"><i class="bi bi-save"></i></button>
                    <button class="btn btn-red-t" id="delPresetBtn" title="Delete selected"><i class="bi bi-trash3"></i></button>
                </div>
                <button class="btn btn-gray" id="resetBtn" style="width:100%;margin-top:10px;"><i class="bi bi-arrow-counterclockwise"></i> Reset everything to defaults</button>
            </div>
        </details>
    </aside>

    {{-- ══════════ CANVAS ══════════ --}}
    <main class="canvas" id="canvas">
        <div id="sheetStack"></div>
        <div class="addPageRow no-print" id="addPageRow">
            <button class="addPageBtn" id="addPageBtn"><i class="bi bi-plus-lg"></i> Add blank page</button>
        </div>
        <div class="empty-state" id="emptyState" style="display:none;">
            <i class="bi bi-sticky"></i>
            <h3>No tags on the sheet</h3>
            <p>Select assets in <b>Inventory</b> and open Print Studio, use <b>Quick add</b>, or press <b>Place</b> next to a queued asset. Then hit <b>Auto-arrange</b> or drag tags anywhere you like.</p>
        </div>
    </main>
</div>

{{-- Per-item markup templates (shared partial = single source of truth) --}}
@foreach($items as $item)
<template class="tag-template" data-id="{{ $item->id }}">
    @include('admin.items.partials.tag-card', ['item' => $item])
</template>
@endforeach

{{-- Print-readiness checklist --}}
<div class="modal-bd" id="checklistBd">
    <div class="modal-cd">
        <div class="modal-hd"><i class="bi bi-printer" style="color:var(--green);margin-right:8px;"></i>Before you print</div>
        <div class="modal-body">
            For the printed output to match this preview exactly:
            <ul>
                <li><b>Paper size</b> — set to match your selection (default A4).</li>
                <li><b>Scale</b> — must be <b>100 %</b> / “Default”, never “Fit to page”.</li>
                <li><b>Margins</b> — set to <b>None</b>; sheet margins are built in.</li>
                <li><b>Background graphics</b> — enabled ✔ (keeps yellow headers).</li>
            </ul>
        </div>
        <div class="modal-ft">
            <button class="btn btn-gray" id="clCancel">Cancel</button>
            <label class="chk" style="margin-right:auto;"><input type="checkbox" id="clDontShow"> Don't show again</label>
            <button class="btn btn-green" id="clGo"><i class="bi bi-printer-fill"></i> Open print dialog</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
(function () {
'use strict';

/* ══════════ Data ══════════ */
const ASSETS   = @json($assetsJson);
const META     = Object.fromEntries(ASSETS.map(a => [String(a.id), a]));
const BASE_W   = {{ $baseW }};
const BASE_H   = {{ $baseH }};
const MM_PX    = 96 / 25.4;

const PAPERS = { a4:[210,297], letter:[215.9,279.4], legal:[215.9,355.6], a5:[148,210], a6:[105,148] };

const DEF = {
    ratio:50, paper:'a4', customW:210, customH:297, orient:'p', margin:8, gapX:3, gapY:3,
    fields:{ category:true, item:true, serial:true, cost:true, date:true, personnel:true, signature:false, logo:true },
    fonts:{ header:100, pnum:100, cells:100, bctext:100, footer:100 },
    headerOverride:false, headerColorHex:'#ffff00', headerText:'{supplier}',
    fontScale:100, barDensity:'auto', skipFirst:0, snap:true, printGuides:false,
    sort:'queue', copies:{}, zoom:65, manualPages:0,
};
const LS_LAST = 'baycis.printStudio.last';
const LS_PRESETS = 'baycis.printStudio.presets';
const PENDING_COPIES_KEY = 'baycis.printStudio.pendingCopies';

let saved = {};
try { saved = JSON.parse(localStorage.getItem(LS_LAST) || '{}'); } catch (e) {}
let S = Object.assign({}, DEF, saved.s || {});
S.fields = Object.assign({}, DEF.fields, S.fields || {});
S.fonts  = Object.assign({}, DEF.fonts,  S.fonts  || {});
S.copies = S.copies || {};
if (![20,40,50,75,100,125,150,175,200].includes(+S.ratio)) S.ratio = 50;

/* copies: last session + pending Excel-import overlay */
try {
    const pend = JSON.parse(localStorage.getItem(PENDING_COPIES_KEY) || 'null');
    if (pend && typeof pend === 'object') S.copies = Object.assign({}, S.copies || {}, pend);
    localStorage.removeItem(PENDING_COPIES_KEY);
} catch (e) {}
S.copies = Object.assign({}, saved.c || {}, S.copies || {});

/* stable placement UIDs (declared before restoration loop — TDZ-safe) */
let UID_SEQ = 1;
function nextUid() { return 'u' + (UID_SEQ++); }

/* placements: [{uid, aid, page, x, y, w, h, o?}] — h derived from w; o = per-tag overrides */
let PLACED = Array.isArray(saved.p)
    ? saved.p.filter(p => META[String(p.aid)])
    : [];
if (PLACED.length) {
    const validIds = new Set(sequenceIds());
    PLACED = PLACED.filter(p => validIds.has(String(p.aid)));
}
if (PLACED.length) {
    const validIds = new Set(sequenceIds());
    PLACED = PLACED.filter(p => validIds.has(String(p.aid)));
}
/* stable uids for restored layouts */
PLACED.forEach(p => {
    if (!p.uid) p.uid = 'u' + (UID_SEQ++);
    else { const n = parseInt(String(p.uid).slice(1)); if (!isNaN(n) && n >= UID_SEQ) UID_SEQ = n + 1; }
});

function ratioW()      { return +(BASE_W * S.ratio / 100).toFixed(2); }
function ratioH(w)     { return +((w || ratioW()) * BASE_H / BASE_W).toFixed(2); }
function paperGeom() {
    const [w0, h0] = S.paper === 'custom' ? [+S.customW, +S.customH] : PAPERS[S.paper];
    const pw = S.orient === 'l' ? h0 : w0;
    const ph = S.orient === 'l' ? w0 : h0;
    return { pw, ph };
}

/* ══════════ Queue helpers ══════════ */
const $ = (id) => document.getElementById(id);
const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));

function sequenceIds() {
    let ids = ASSETS.map(a => String(a.id));
    const byId = (k) => META[k];
    if (S.sort === 'tag')  ids.sort((a,b)=> byId(a).tag.localeCompare(byId(b).tag));
    if (S.sort === 'name') ids.sort((a,b)=> byId(a).name.localeCompare(byId(b).name));
    if (S.sort === 'category') ids.sort((a,b)=> (byId(a).category||'').localeCompare(byId(b).category||''));
    return ids;
}
function sequenceEntries() {
    const out = [];
    sequenceIds().forEach(id => {
        const n = clamp(parseInt(S.copies[id]) || 1, 1, 99);
        for (let i = 0; i < n; i++) out.push(id);
    });
    return out;
}

/* ══════════ Placement engine ══════════ */
let lastSig = null;
function layoutSig() {
    const g = paperGeom();
    return [sequenceEntries().join(','), S.ratio, g.pw, g.ph, S.margin, S.gapX, S.gapY].join('|');
}
function autoArrange() {
    const g = paperGeom();
    const w = ratioW(), h = ratioH();

    const skip = clamp(parseInt(S.skipFirst) || 0, 0, 400);
    /* leading offset measured in tag-widths */
    const leadX = S.margin + (skip * (w + S.gapX));
    let x = leadX > g.pw - S.margin ? S.margin : leadX;
    let y = S.margin;
    let page = 0;
    const out = [];

    sequenceEntries().forEach(aid => {
        if (x + w > g.pw - S.margin + 0.01) { x = S.margin; y += h + S.gapY; }
        if (y + h > g.ph - S.margin + 0.01) { y = S.margin; page++; }
        out.push({ uid:nextUid(), aid:+aid, page, x:+x.toFixed(2), y:+y.toFixed(2), w:+w.toFixed(2), h:ratioH(w) });
        x += w + S.gapX;
    });

    PLACED = out;
    S.manualPages = 0;
    lastSig = layoutSig();
}

function ensureLayout() {
    const seq = sequenceEntries();
    if (!seq.length) { PLACED = []; lastSig = null; return; }
    const sig = layoutSig();
    /* Re-arrange ONLY when the queue/geometry actually changed —
       manual drags, resizes and deletions are preserved. */
    if (!PLACED.length || lastSig !== sig) autoArrange();
    else lastSig = sig;
}

function clampPlacementsToPaper() {
    const g = paperGeom();
    PLACED.forEach(p => {
        p.w = clamp(+p.w || ratioW(), 30, g.pw - 2 * S.margin);
        p.h = ratioH(p.w);
        p.x = clamp(+p.x || S.margin, S.margin, Math.max(S.margin, g.pw - S.margin - p.w));
        p.y = clamp(+p.y || S.margin, S.margin, Math.max(S.margin, g.ph - S.margin - p.h));
        p.page = clamp(+p.page || 0, 0, 500);
    });
}

/* ══════════ Rendering ══════════ */
const stack = $('sheetStack');
let SEL_UID = null;

function templateFor(aid) {
    const t = document.querySelector('template.tag-template[data-id="' + aid + '"]');
    return t ? t.content.firstElementChild.cloneNode(true) : null;
}

/* ══════════ Edit target: selected tag overrides, or global defaults ══════════ */
const CONTENT_KEYS = ['headerOverride','headerColorHex','headerText','barDensity','fontScale'];

function targetPlacement() { return SEL_UID ? PLACED.find(x => x.uid === SEL_UID) || null : null; }

function effFor(p) {
    const o = (p && p.o) || {};
    return {
        fields:         Object.assign({}, S.fields, o.fields || {}),
        fonts:          Object.assign({}, S.fonts,  o.fonts  || {}),
        fontScale:      o.fontScale     !== undefined ? o.fontScale     : S.fontScale,
        headerOverride: o.headerOverride !== undefined ? o.headerOverride : S.headerOverride,
        headerColorHex: o.headerColorHex !== undefined ? o.headerColorHex : S.headerColorHex,
        headerText:     o.headerText     !== undefined ? o.headerText     : S.headerText,
        barDensity:     o.barDensity     !== undefined ? o.barDensity     : S.barDensity,
    };
}
function tgt() {
    const p = targetPlacement();
    return p ? { scope:'tag', p, e: effFor(p) }
             : { scope:'global', e: effFor(null) };
}
function setField(k, v) {
    const t = tgt();
    if (t.scope === 'tag') { const o = t.p.o || (t.p.o = {}); (o.fields || (o.fields = {}))[k] = v; }
    else S.fields[k] = v;
}
function setFont(k, v) {
    const t = tgt();
    if (t.scope === 'tag') { const o = t.p.o || (t.p.o = {}); (o.fonts || (o.fonts = {}))[k] = v; }
    else S.fonts[k] = v;
}
function setKey(k, v) {
    const t = tgt();
    if (t.scope === 'tag') { const o = t.p.o || (t.p.o = {}); o[k] = v; }
    else S[k] = v;
}

function applyContent(tagEl, w, h, e) {
    const meta = META[tagEl.dataset.itemId] || {};
    tagEl.querySelectorAll('tr[data-field]').forEach(tr => {
        tr.style.display = e.fields[tr.dataset.field] ? '' : 'none';
    });
    tagEl.querySelector('.js-logo').style.display = e.fields.logo ? '' : 'none';

    const hdr = tagEl.querySelector('.tag-header');
    if (e.headerOverride) {
        hdr.style.backgroundColor = e.headerColorHex;
        hdr.textContent = (e.headerText || '').replace(/\{supplier\}/g, meta.supplier || 'N/A') || 'N/A';
    }

    /* contents scale exactly with the chosen size → never cropped */
    const m = (h / BASE_H) * ((e.fontScale ?? 100) / 100);
    tagEl.style.setProperty('--m', m.toFixed(4));
    tagEl.dataset.m = m.toFixed(4);

    /* per-element font configuration */
    const F = e.fonts;
    tagEl.style.setProperty('--fk-hdr',    ((F.header  ?? 100) / 100).toFixed(3));
    tagEl.style.setProperty('--fk-pnum',   ((F.pnum    ?? 100) / 100).toFixed(3));
    tagEl.style.setProperty('--fk-cells',  ((F.cells   ?? 100) / 100).toFixed(3));
    tagEl.style.setProperty('--fk-bctext', ((F.bctext  ?? 100) / 100).toFixed(3));
    tagEl.style.setProperty('--fk-footer', ((F.footer  ?? 100) / 100).toFixed(3));
}

function renderBarcodeFor(tagEl, w, h, e) {
    const img = tagEl.querySelector('.js-barcode');
    if (!img) return;
    const m = parseFloat(tagEl.dataset.m) || 1;

    const panelPx = Math.max(30, w * MM_PX * 0.35 - 9 * m);
    const len = String(img.dataset.tag || '').length || 18;
    const modules = 11 * (len + 2) + 15;

    const fitW = panelPx / modules;
    let barW = e.barDensity === 'auto' ? fitW : Math.min(parseFloat(e.barDensity), fitW * 1.6);
    barW = clamp(+barW.toFixed(2), 0.35, 3);
    const barH = Math.round(clamp(h * MM_PX * 0.20, 10, 120));

    try {
        JsBarcode(img, img.dataset.tag, {
            format:'CODE128', lineColor:'#000',
            width:barW, height:barH,
            displayValue:false, margin:0, background:'transparent',
        });
    } catch (e) {}
}

function render() {
    document.getElementById('pageRule').textContent =
        '@page { size:' + paperGeom().pw + 'mm ' + paperGeom().ph + 'mm; margin:0; }';
    document.body.classList.toggle('print-guides', !!S.printGuides);

    ensureLayout();
    clampPlacementsToPaper();

    /* Duplicate check — on the distinct queue, NOT on copies
       (multiple copies of one asset are intentional, not duplicates) */
    const queueTags = ASSETS.map(a => a.tag);
    const hasDup = new Set(queueTags).size !== queueTags.length;
    $('dupBanner').classList.toggle('warn', hasDup);

    const g = paperGeom();
    const pagesNeeded = PLACED.reduce((m, p) => Math.max(m, p.page + 1), 0);
    const pageCount = Math.max(1, pagesNeeded, +S.manualPages || 0);

    stack.innerHTML = '';

    for (let pg = 0; pg < pageCount; pg++) {
        const zone = document.createElement('div');
        zone.className = 'sheetZone';
        const wrap = document.createElement('div');
        wrap.className = 'rulerWrap';

        const sheet = document.createElement('div');
        sheet.className = 'studio-sheet';
        sheet.dataset.page = pg;
        sheet.style.width = g.pw + 'mm';
        sheet.style.height = g.ph + 'mm';

        PLACED.filter(p => +p.page === pg).forEach((p, idx) => {
            const uid = p.uid;
            const cell = document.createElement('div');
            cell.className = 'placed' + (uid === SEL_UID ? ' sel' : '');
            cell.dataset.uid = uid;
            cell.style.left = p.x + 'mm';
            cell.style.top = p.y + 'mm';
            cell.style.width = p.w + 'mm';
            cell.style.height = p.h + 'mm';

            const tagEl = templateFor(p.aid);
            if (tagEl) {
                const eff = effFor(p);
                applyContent(tagEl, p.w, p.h, eff);
                cell.appendChild(tagEl);
                renderBarcodeFor(tagEl, p.w, p.h, eff);
            } else {
                cell.innerHTML = '<div style="padding:8px;font-size:9px;color:#94a3b8;">Asset #' + p.aid + ' missing</div>';
            }

            const rz = document.createElement('div'); rz.className = 'rz'; rz.title = 'Drag to resize';
            const wx = document.createElement('button'); wx.className = 'wx'; wx.innerHTML = '<i class="bi bi-x-lg"></i>'; wx.title = 'Remove from sheet';
            cell.appendChild(rz); cell.appendChild(wx);

            bindWidget(cell, p, g);
            sheet.appendChild(cell);
        });

        const rx = document.createElement('div'); rx.className = 'ruler-x no-print';
        const ry = document.createElement('div'); ry.className = 'ruler-y no-print';
        for (let mm = 10; mm <= g.pw; mm += 10) {
            const s = document.createElement('span');
            s.style.left = mm + 'mm';
            if (mm % 50 === 0) s.textContent = mm;
            rx.appendChild(s);
        }
        for (let mm = 10; mm <= g.ph; mm += 10) {
            const s = document.createElement('span');
            s.style.top = mm + 'mm';
            if (mm % 50 === 0) s.textContent = mm;
            ry.appendChild(s);
        }
        wrap.appendChild(rx); wrap.appendChild(ry);
        wrap.appendChild(sheet);
        zone.appendChild(wrap);

        const label = document.createElement('div');
        label.className = 'sheetLabel no-print';
        const onPage = PLACED.filter(p => +p.page === pg).length;
        label.textContent = 'Page ' + (pg + 1) + ' · ' + g.pw + '×' + g.ph + 'mm · ' + onPage + ' tag(s)';
        zone.appendChild(label);

        stack.appendChild(zone);
    }

    if (pageCount === 0 || (!PLACED.length && !S.manualPages)) $('emptyState').style.display = '';
    else $('emptyState').style.display = 'none';

    const totalTags = PLACED.length;
    $('sumChip').textContent = totalTags + ' tag' + (totalTags === 1 ? '' : 's')
        + ' · ' + pageCount + ' page' + (pageCount === 1 ? '' : 's')
        + ' · ' + S.ratio + '% (' + ratioW() + '×' + ratioH() + 'mm)';
    $('ratioDims').textContent = '⇒ ' + ratioW() + ' × ' + ratioH() + ' mm per tag';
    saveLast();
}

/* ══════════ Interaction: drag / resize / select ══════════ */
let dragCtx = null;
let dimBadge = null;

function showBadge(sheetZone, xPx, yPx, text) {
    if (!dimBadge) { dimBadge = document.createElement('div'); dimBadge.className = 'dimBadge no-print'; document.body.appendChild(dimBadge); }
    dimBadge.textContent = text;
    dimBadge.style.display = 'block';
    dimBadge.style.left = (xPx + 10) + 'px';
    dimBadge.style.top = (yPx - 26) + 'px';
}
function hideBadge() { if (dimBadge) dimBadge.style.display = 'none'; }

function deselect() {
    stack.querySelectorAll('.placed.sel').forEach(el => el.classList.remove('sel'));
    if (SEL_UID) { SEL_UID = null; syncUI(); }
}

function bindWidget(cell, p, g) {
    cell.addEventListener('pointerdown', function (e) {
        if (e.target.closest('.wx')) { removePlacement(cell); e.stopPropagation(); return; }
        e.preventDefault();

        stack.querySelectorAll('.placed.sel').forEach(el => el.classList.remove('sel'));
        cell.classList.add('sel');
        const selChanged = SEL_UID !== p.uid;
        SEL_UID = p.uid;
        if (selChanged) syncUI();

        const sheet = cell.closest('.studio-sheet');
        const rect = sheet.getBoundingClientRect();
        const pxPerMm = rect.width / g.pw;

        dragCtx = {
            mode: e.target.closest('.rz') ? 'resize' : 'move',
            cell, p, g,
            e: effFor(p),
            pxPerMm,
            sx: e.clientX, sy: e.clientY,
            ox: p.x, oy: p.y, ow: p.w,
        };
        cell.classList.add('dragging');
        cell.setPointerCapture(e.pointerId);

        showBadge(cell.closest('.sheetZone'), e.clientX, e.clientY,
            dragCtx.mode === 'resize'
                ? Math.round(p.w) + '×' + Math.round(ratioH(p.w)) + 'mm'
                : Math.round(p.x) + ', ' + Math.round(p.y) + 'mm');
    });

    cell.addEventListener('pointermove', function (e) {
        if (!dragCtx || dragCtx.cell !== cell) return;
        const dx = (e.clientX - dragCtx.sx) / dragCtx.pxPerMm;
        const dy = (e.clientY - dragCtx.sy) / dragCtx.pxPerMm;
        const snap = (!e.altKey && S.snap) ? (v => Math.round(v)) : (v => Math.round(v * 10) / 10);
        const g = dragCtx.g, p = dragCtx.p;

        if (dragCtx.mode === 'move') {
            p.x = clamp(snap(dragCtx.ox + dx), S.margin, Math.max(S.margin, g.pw - S.margin - p.w));
            p.y = clamp(snap(dragCtx.oy + dy), S.margin, Math.max(S.margin, g.ph - S.margin - p.h));
            cell.style.left = p.x + 'mm';
            cell.style.top = p.y + 'mm';
            showBadge(cell.closest('.sheetZone'), e.clientX, e.clientY, Math.round(p.x) + ', ' + Math.round(p.y) + 'mm');
        } else {
            p.w = clamp(snap(dragCtx.ow + dx), 30, g.pw - 2 * S.margin);
            p.h = ratioH(p.w);
            cell.style.width = p.w + 'mm';
            cell.style.height = p.h + 'mm';
            const tagEl = cell.querySelector('.js-tag');
            applyContent(tagEl, p.w, p.h);
            showBadge(cell.closest('.sheetZone'), e.clientX, e.clientY,
                Math.round(p.w) + '×' + Math.round(p.h) + 'mm · ' + Math.round(p.w / BASE_W * 100) + '%');
        }
    });

    const endDrag = function (e) {
        if (!dragCtx || dragCtx.cell !== cell) return;
        cell.classList.remove('dragging');
        hideBadge();
        if (dragCtx.mode === 'resize') {
            const tagEl = cell.querySelector('.js-tag');
            renderBarcodeFor(tagEl, dragCtx.p.w, dragCtx.p.h, dragCtx.e);
        }
        dragCtx = null;
        saveLast();
    };
    cell.addEventListener('pointerup', endDrag);
    cell.addEventListener('pointercancel', endDrag);
}

function removePlacement(cell) {
    const sheet = cell.closest('.studio-sheet');
    const page = +sheet.dataset.page;
    /* identify index within that page's children order */
    const cells = Array.from(sheet.querySelectorAll(':scope > .placed'));
    const idx = cells.indexOf(cell);
    const onPage = PLACED.map((p, i) => ({ p, i })).filter(o => +o.p.page === page);
    if (onPage[idx]) {
        if (onPage[idx].p.uid === SEL_UID) SEL_UID = null;
        PLACED.splice(onPage[idx].i, 1);
    }
    cell.remove();
    syncUI();
    render();
}

document.addEventListener('keydown', (e) => {
    if ((e.key === 'Delete' || e.key === 'Backspace') && SEL_UID) {
        const active = document.activeElement;
        if (active && ['INPUT','SELECT','TEXTAREA'].includes(active.tagName)) return;
        const cell = stack.querySelector('.placed[data-uid="' + SEL_UID + '"]');
        if (cell) removePlacement(cell);
    }
});

$('canvas').addEventListener('pointerdown', (e) => {
    /* never let a toolbar press wipe the current selection */
    if (e.target.closest('.toolbar')) return;
    if (!e.target.closest('.placed')) deselect();
});

/* ══════════ Scope actions ══════════ */
$('resetTagBtn').addEventListener('click', () => {
    const p = targetPlacement();
    if (!p) return;
    delete p.o;
    syncUI(); scheduleRender(); saveLast();
});
$('applyAllBtn').addEventListener('click', () => {
    const t = tgt();
    if (t.scope !== 'tag') return;
    /* promote this tag's effective settings to global, then clear all overrides */
    S.fields          = Object.assign({}, t.e.fields);
    S.fonts           = Object.assign({}, t.e.fonts);
    S.headerOverride  = t.e.headerOverride;
    S.headerColorHex  = t.e.headerColorHex;
    S.headerText      = t.e.headerText;
    S.barDensity      = t.e.barDensity;
    PLACED.forEach(x => { delete x.o; });
    SEL_UID = null;
    syncUI(); scheduleRender(); saveLast();
});
$('clearSelBtn').addEventListener('click', deselect);

/* ══════════ Queue UI ══════════ */
function renderQueue() {
    const list = $('assetList');
    list.innerHTML = '';
    sequenceIds().forEach(id => {
        const a = META[id];
        const row = document.createElement('div');
        row.className = 'qrow';
        row.innerHTML =
            '<div class="qn"><div class="qt">' + a.tag + '</div><div class="qm">' +
            a.name.replace(/</g,'&lt;') + '</div></div>' +
            '<input type="number" min="1" max="99" value="' + (parseInt(S.copies[id]) || 1) + '" data-copy-id="' + id + '" title="Copies">' +
            '<button class="place-btn" data-place-id="' + id + '" title="Add to sheet at current ratio"><i class="bi bi-plus-square"></i></button>' +
            '<button class="rm" data-rm-id="' + id + '" title="Remove"><i class="bi bi-x-lg"></i></button>';
        list.appendChild(row);
    });
    if (!ASSETS.length) list.innerHTML = '<div style="padding:14px;text-align:center;font-size:12px;color:var(--sub);">Queue is empty — use Quick add or the Inventory bulk-select.</div>';

    list.querySelectorAll('input[data-copy-id]').forEach(inp =>
        inp.addEventListener('change', () => {
            S.copies[inp.dataset.copyId] = clamp(parseInt(inp.value) || 1, 1, 99);
            inp.value = S.copies[inp.dataset.copyId];
            PLACED = [];
            scheduleRender();
        }));
    list.querySelectorAll('.place-btn').forEach(btn =>
        btn.addEventListener('click', () => placeOne(btn.dataset.placeId)));
    list.querySelectorAll('button[data-rm-id]').forEach(btn =>
        btn.addEventListener('click', () => {
            const remaining = ASSETS.filter(a => String(a.id) !== btn.dataset.rmId).map(a => a.id);
            location.href = '/admin/print-studio' + (remaining.length ? '?ids=' + remaining.join(',') : '');
        }));
}

function placeOne(aid) {
    const g = paperGeom();
    const w = ratioW(), h = ratioH();
    const pageCount = PLACED.reduce((m, p) => Math.max(m, p.page + 1),
        Math.max(1, +S.manualPages || 0));
    for (let pg = 0; pg < pageCount + 1; pg++) {
        for (let y = S.margin; y <= g.ph - S.margin - h + 0.01; y += S.gapY + h) {
            for (let x = S.margin; x <= g.pw - S.margin - w + 0.01; x += S.gapX + w) {
                const hit = PLACED.some(p => +p.page === pg &&
                    x < p.x + p.w + S.gapX && x + w + S.gapX > p.x &&
                    y < p.y + p.h + S.gapY && y + h + S.gapY > p.y);
                if (!hit) {
                    PLACED.push({ aid:+aid, page:pg, x:+x.toFixed(2), y:+y.toFixed(2), w, h });
                    scheduleRender();
                    return;
                }
            }
        }
    }
    /* no room anywhere → new page */
    PLACED.push({ uid:nextUid(), aid:+aid, page:pageCount, x:S.margin, y:S.margin, w, h });
    scheduleRender();
}

/* ══════════ Controls wiring ══════════ */
let renderQueued = false;
function scheduleRender() {
    if (renderQueued) return;
    renderQueued = true;
    requestAnimationFrame(() => { renderQueued = false; render(); });
}

function syncUI() {
    const t = tgt();
    const e = t.e;

    /* scope indicator */
    const st = $('scopeText'), sa = $('scopeActions');
    if (t.scope === 'tag') {
        const meta = META[String(t.p.aid)] || {};
        st.innerHTML = '<i class="bi bi-pin-angle" style="color:var(--accent);margin-right:5px;"></i>' +
            'Editing <b>' + (meta.tag || '#' + t.p.aid) + '</b> — options apply to this tag only.';
        sa.style.display = 'flex';
    } else {
        st.innerHTML = '<i class="bi bi-globe2" style="color:var(--sub);margin-right:5px;"></i>Global defaults — changes affect every tag.';
        sa.style.display = 'none';
    }

    document.querySelectorAll('[data-k]').forEach(el => {
        const k = el.dataset.k;
        let v;
        if (CONTENT_KEYS.includes(k)) v = e[k];
        else v = S[k];
        if (el.type === 'checkbox') el.checked = !!v;
        else el.value = v ?? '';
    });
    $('orP').classList.toggle('on', S.orient === 'p');
    $('orL').classList.toggle('on', S.orient === 'l');
    $('grp-customPaper').style.display = S.paper === 'custom' ? '' : 'none';
    $('grp-hdrColor').style.display = e.headerOverride ? '' : 'none';
    $('fsVal').textContent = e.fontScale + '%';
    $('ratioVal').textContent = S.ratio + '%';
    $('ratioRange').value = S.ratio;
    document.querySelectorAll('.ratio-chips button').forEach(b =>
        b.classList.toggle('on', +b.dataset.ratio === +S.ratio));
    $('zoomCtl').value = S.zoom;
    $('zoomVal').textContent = S.zoom + '%';
    $('sortSel').value = ['queue','tag','name','category'].includes(S.sort) ? S.sort : 'queue';
    ['f_serial','f_cost','f_date','f_personnel','f_signature','f_logo'].forEach(id => {
        $(id).checked = !!e.fields[$(id).dataset.f];
    });
    document.querySelectorAll('[data-fk]').forEach(el => {
        const v = +e.fonts[el.dataset.fk];
        if (!isNaN(v)) el.value = v;
        const out = $('fv_' + el.dataset.fk);
        if (out) out.textContent = el.value + '%';
    });
    $('ratioDims').textContent = '⇒ ' + ratioW() + ' × ' + ratioH() + ' mm per tag';
}

document.querySelectorAll('[data-k]').forEach(el => {
    el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', () => {
        const k = el.dataset.k;
        const val = el.type === 'checkbox' ? el.checked
                  : (el.type === 'number' || el.type === 'range') ? (parseFloat(el.value) || 0)
                  : el.value;
        if (CONTENT_KEYS.includes(k)) {
            setKey(k, val);          /* routes to selected tag or global */
            if (k === 'headerOverride') syncUI();   /* reveal/hide the color picker */
            scheduleRender();
        } else {
            S[k] = val;
            if (k === 'ratio') { PLACED = []; $('ratioVal').textContent = S.ratio + '%'; }
            if (['margin','gapX','gapY','sort'].includes(k)) PLACED = [];
            if (k === 'paper') syncUI();
            scheduleRender();
        }
    });
});
document.querySelectorAll('.ratio-chips button').forEach(b =>
    b.addEventListener('click', () => { S.ratio = +b.dataset.ratio; PLACED = []; syncUI(); scheduleRender(); }));

$('orP').addEventListener('click', () => { S.orient = 'p'; PLACED = []; syncUI(); scheduleRender(); });
$('orL').addEventListener('click', () => { S.orient = 'l'; PLACED = []; syncUI(); scheduleRender(); });

['f_serial','f_cost','f_date','f_personnel','f_signature','f_logo'].forEach(id =>
    $(id).addEventListener('change', () => { setField($(id).dataset.f, $(id).checked); scheduleRender(); }));

document.querySelectorAll('[data-fk]').forEach(el =>
    el.addEventListener('input', () => {
        const k = el.dataset.fk;
        const v = parseInt(el.value) || 100;
        setFont(k, v);
        const out = $('fv_' + k);
        if (out) out.textContent = el.value + '%';
        scheduleRender();
    }));

function doAutoArrange() { autoArrange(); scheduleRender(); }
$('autoArrangeBtn').addEventListener('click', doAutoArrange);

$('addPageBtn').addEventListener('click', () => {
    S.manualPages = Math.max(+S.manualPages || 0,
        PLACED.reduce((m, p) => Math.max(m, p.page + 1), 0)) + 1;
    scheduleRender();
});

$('zoomCtl').addEventListener('input', () => {
    S.zoom = parseInt($('zoomCtl').value);
    $('zoomVal').textContent = S.zoom + '%';
    stack.style.zoom = S.zoom / 100;
    saveLast();
});

/* ══════════ Presets persistence ══════════ */
function allPresets() { try { return JSON.parse(localStorage.getItem(LS_PRESETS) || '{}'); } catch (e) { return {}; } }
function refreshPresetList() {
    const sel = $('loadPresetSel');
    sel.innerHTML = '<option value="">— Load a saved layout —</option>' +
        Object.keys(allPresets()).map(n => '<option value="' + n.replace(/"/g,'&quot;') + '">' + n.replace(/</g,'&lt;') + '</option>').join('');
}
$('savePresetBtn').addEventListener('click', () => {
    const name = ($('presetName').value || '').trim();
    if (!name) { $('presetName').focus(); return; }
    const p = allPresets(); p[name] = snapshot();
    localStorage.setItem(LS_PRESETS, JSON.stringify(p));
    refreshPresetList();
});
$('loadPresetSel').addEventListener('change', () => {
    const p = allPresets()[$('loadPresetSel').value];
    if (!p) return;
    S = Object.assign({}, DEF, p, {
        fields: Object.assign({}, DEF.fields, p.fields || {}),
        fonts:  Object.assign({}, DEF.fonts,  p.fonts  || {}),
    });
    PLACED = [];
    syncUI(); renderQueue(); scheduleRender();
});
$('delPresetBtn').addEventListener('click', () => {
    const v = $('loadPresetSel').value;
    if (!v) return;
    const p = allPresets(); delete p[v];
    localStorage.setItem(LS_PRESETS, JSON.stringify(p));
    refreshPresetList();
});
$('resetBtn').addEventListener('click', () => {
    S = JSON.parse(JSON.stringify(DEF));
    PLACED = [];
    syncUI(); render(); stack.style.zoom = S.zoom / 100;
});

/* ══════════ Quick-add picker ══════════ */
let addTimer = null;
$('quickAdd').addEventListener('input', () => {
    clearTimeout(addTimer);
    const q = $('quickAdd').value.trim();
    if (q.length < 2) { $('addResults').style.display = 'none'; return; }
    addTimer = setTimeout(() => {
        fetch('/admin/print-studio/search?q=' + encodeURIComponent(q), {
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        })
        .then(r => r.json())
        .then(list => {
            const box = $('addResults');
            if (!list.length) { box.style.display = 'none'; return; }
            box.innerHTML = list.map(it =>
                '<div class="addres" data-id="' + it.id + '"><b>' + it.property_tag + '</b> · ' +
                it.name.replace(/</g,'&lt;') + '</div>').join('');
            box.style.display = 'block';
            box.querySelectorAll('.addres').forEach(r => r.addEventListener('mousedown', () => {
                const current = ASSETS.map(a => a.id);
                if (!current.includes(parseInt(r.dataset.id))) current.push(parseInt(r.dataset.id));
                location.href = '/admin/print-studio?ids=' + current.join(',');
            }));
        }).catch(() => {});
    }, 250);
});
document.addEventListener('click', (e) => {
    if (!e.target.closest('.addbox')) $('addResults').style.display = 'none';
});
$('clearAssets').addEventListener('click', () => { location.href = '/admin/print-studio'; });

/* ══════════ Print + readiness checklist ══════════ */
$('printBtn').addEventListener('click', () => {
    if (!PLACED.length) return;
    if (sessionStorage.getItem('ps-checklist-ok')) window.print();
    else $('checklistBd').classList.add('open');
});
$('clCancel').addEventListener('click', () => $('checklistBd').classList.remove('open'));
$('clGo').addEventListener('click', () => {
    if ($('clDontShow').checked) sessionStorage.setItem('ps-checklist-ok', '1');
    $('checklistBd').classList.remove('open');
    setTimeout(window.print, 60);
});
$('checklistBd').addEventListener('click', (e) => {
    if (e.target === $('checklistBd')) $('checklistBd').classList.remove('open');
});

/* ══════════ Toolbar: dropdowns & tools ══════════ */
document.querySelectorAll('[data-dd]').forEach(btn =>
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const dd = document.getElementById(btn.dataset.dd);
        document.querySelectorAll('.tb-dd.open').forEach(o => { if (o !== dd) o.classList.remove('open'); });
        dd.classList.toggle('open');
    }));
document.addEventListener('click', () =>
    document.querySelectorAll('.tb-dd.open').forEach(o => o.classList.remove('open')));

function selectedPlacement() {
    return PLACED.find(x => x.uid === SEL_UID) || null;
}
function flashNoSelection() {
    const chip = $('sumChip'), old = chip.textContent;
    chip.textContent = '⚠ Select a tag first';
    setTimeout(() => { chip.textContent = old; }, 1400);
}

$('tbArrange').addEventListener('click', () => { autoArrange(); scheduleRender(); });

$('tbAddPage').addEventListener('click', () => $('addPageBtn').click());

$('tbDuplicate').addEventListener('click', duplicateSelected);
$('tbDelete').addEventListener('click', () => {
    const cell = SEL_UID && stack.querySelector('.placed[data-uid="' + SEL_UID + '"]');
    if (cell) removePlacement(cell);
});

$('tbCenter').addEventListener('click', () => alignSelected('centerpage'));
document.querySelectorAll('#ddAlign [data-align]').forEach(b =>
    b.addEventListener('click', () => alignSelected(b.dataset.align)));

function alignSelected(mode) {
    const p = selectedPlacement();
    if (!p) { flashNoSelection(); return; }
    const g = paperGeom();
    if (mode.includes('x') || mode === 'left' || mode === 'right' || mode === 'centerx') {
        if (mode === 'left')      p.x = S.margin;
        if (mode === 'right')     p.x = g.pw - S.margin - p.w;
        if (mode === 'centerx')   p.x = (g.pw - p.w) / 2;
    }
    if (mode.includes('y') || mode === 'top' || mode === 'bottom' || mode === 'centery') {
        if (mode === 'top')       p.y = S.margin;
        if (mode === 'bottom')    p.y = g.ph - S.margin - p.h;
        if (mode === 'centery')   p.y = (g.ph - p.h) / 2;
    }
    if (mode === 'centerpage') {
        p.x = (g.pw - p.w) / 2;
        p.y = (g.ph - p.h) / 2;
    }
    p.x = Math.max(S.margin, p.x);
    p.y = Math.max(S.margin, p.y);
    syncPlacementStyle(p);
    saveLast();
}

function flashNoSelection() {
    const chip = $('sumChip'), old = chip.textContent;
    chip.textContent = '⚠ Select a tag first';
    setTimeout(() => { chip.textContent = old; }, 1400);
}

function syncPlacementStyle(p) {
    const cell = stack.querySelector('.placed[data-uid="' + p.uid + '"]');
    if (!cell) return;
    cell.style.left = p.x + 'mm';
    cell.style.top = p.y + 'mm';
    cell.style.width = p.w + 'mm';
    cell.style.height = p.h + 'mm';
}

function duplicateSelected() {
    const p = selectedPlacement();
    if (!p) { flashNoSelection(); return; }
    const copy = Object.assign({}, p, { uid: nextUid(), o: p.o ? JSON.parse(JSON.stringify(p.o)) : undefined });
    /* place beside the original, wrapping to next row/page when out of room */
    const g = paperGeom();
    copy.x = p.x + p.w + S.gapX;
    if (copy.x + copy.w > g.pw - S.margin) { copy.x = S.margin; copy.y = p.y + p.h + S.gapY; }
    if (copy.y + copy.h > g.ph - S.margin) { copy.page = p.page + 1; copy.y = S.margin; S.manualPages = Math.max(+S.manualPages||0, copy.page+1); }
    PLACED.push(copy);
    SEL_UID = copy.uid;
    scheduleRender();
}

$('tbFront').addEventListener('click', () => zorder(1));
$('tbBack').addEventListener('click', () => zorder(-1));
function zorder(dir) {
    const p = selectedPlacement();
    if (!p) { flashNoSelection(); return; }
    const idx = PLACED.indexOf(p);
    PLACED.splice(idx, 1);
    if (dir > 0) {
        let insertAt = PLACED.length;
        for (let i = PLACED.length - 1; i >= 0; i--) {
            if (+PLACED[i].page === +p.page) { insertAt = i + 1; break; }
            if (+PLACED[i].page < +p.page) { insertAt = i + 1; break; }
        }
        PLACED.splice(insertAt, 0, p);
    } else {
        let insertAt = 0;
        for (let i = 0; i < PLACED.length; i++) {
            if (+PLACED[i].page === +p.page) { insertAt = i; break; }
        }
        PLACED.splice(insertAt, 0, p);
    }
    scheduleRender(); saveLast();
}

$('tbSnap').addEventListener('click', () => {
    S.snap = !S.snap;
    $('tbSnap').classList.toggle('on', !!S.snap);
    $('snapChk').checked = !!S.snap;
    saveLast();
});

/* Keyboard: Ctrl+D duplicate, arrows nudge */
document.addEventListener('keydown', (e) => {
    const active = document.activeElement;
    if (active && ['INPUT','SELECT','TEXTAREA'].includes(active.tagName)) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
        e.preventDefault(); duplicateSelected(); return;
    }
    const p = selectedPlacement();
    if (!p) return;
    const step = e.shiftKey ? 10 : 1;
    const map = { ArrowLeft:[-step,0], ArrowRight:[step,0], ArrowUp:[0,-step], ArrowDown:[0,step] };
    if (!map[e.key]) return;
    e.preventDefault();
    const g = paperGeom();
    p.x = clamp(p.x + map[e.key][0], S.margin, g.pw - S.margin - p.w);
    p.y = clamp(p.y + map[e.key][1], S.margin, g.ph - S.margin - p.h);
    syncPlacementStyle(p); saveLast();
});

/* ══════════ Export engine (PNG / PDF / JSON) ══════════ */
async function captureSheet(sheet, pw, ph, scale) {
    sheet.classList.add('exporting');
    try {
        return await html2canvas(sheet, {
            scale: scale || 3,                       /* ~288 DPI */
            backgroundColor: '#ffffff',
            logging: false,
            useCORS: true,
        });
    } finally {
        sheet.classList.remove('exporting');
    }
}

async function exportPDF() {
    const sheets = Array.from(stack.querySelectorAll('.studio-sheet'));
    if (!sheets.length) return;
    const g = paperGeom();
    const prevZoom = stack.style.zoom;
    deselect();
    document.body.classList.add('exporting');
    stack.style.zoom = 1;

    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ unit:'mm', format:[g.pw, g.ph], orientation: g.pw > g.ph ? 'landscape' : 'portrait' });
        for (let i = 0; i < sheets.length; i++) {
            if (i > 0) pdf.addPage([g.pw, g.ph], g.pw > g.ph ? 'landscape' : 'portrait');
            const canvas = await captureSheet(sheets[i], g.pw, g.ph);
            pdf.addImage(canvas.toDataURL('image/jpeg', 0.92), 'JPEG', 0, 0, g.pw, g.ph);
        }
        pdf.save('baycis-property-tags.pdf');
    } catch (e) {
        alert('PDF export failed: ' + e.message);
    } finally {
        document.body.classList.remove('exporting');
        stack.style.zoom = prevZoom;
    }
}

async function exportPNG(allPages) {
    const sheets = Array.from(stack.querySelectorAll('.studio-sheet'));
    if (!sheets.length) return;
    const targets = allPages ? sheets : [sheets[+ (SEL_UID ? (PLACED.find(p=>p.uid===SEL_UID)||{}).page : 0) || 0]].filter(Boolean);
    const prevZoom = stack.style.zoom;
    deselect();
    document.body.classList.add('exporting');
    stack.style.zoom = 1;

    try {
        for (let i = 0; i < targets.length; i++) {
            const canvas = await captureSheet(targets[i]);
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = 'baycis-tag-sheet' + (allPages ? '-' + (i+1) : '') + '.png';
            a.click();
            await new Promise(r => setTimeout(r, 250));   // let the browser breathe between downloads
        }
    } catch (e) {
        alert('PNG export failed: ' + e.message);
    } finally {
        document.body.classList.remove('exporting');
        stack.style.zoom = prevZoom;
    }
}

function exportJSON() {
    const payload = {
        exported: new Date().toISOString(),
        settings: snapshot(),
        placements: PLACED,
    };
    const blob = new Blob([JSON.stringify(payload, null, 2)], { type:'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'baycis-print-layout.json';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 4000);
}

const EXCEL_MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

async function exportXLSX() {
    if (!PLACED.length) { alert('Nothing to export — the sheet has no tags.'); return; }

    const g = paperGeom();
    const ordered = PLACED.slice().sort((a, b) =>
        (+a.page - +b.page) || (+a.y - +b.y) || (+a.x - +b.x));

    const wb = new ExcelJS.Workbook();
    wb.creator = 'BayCIS Print Studio';

    /* ── Sheet 1: Property Tags ── */
    const ws = wb.addWorksheet('Property Tags');
    ws.columns = [
        { header: '#',                  key: 'n',      width: 5  },
        { header: 'Property Tag',       key: 'tag',    width: 24 },
        { header: 'Item/Brand/Model',   key: 'name',   width: 34 },
        { header: 'Category',           key: 'cat',    width: 20 },
        { header: 'Supplier/Fund',      key: 'sup',    width: 20 },
        { header: 'Page',               key: 'page',   width: 7  },
        { header: 'X (mm)',             key: 'x',      width: 9  },
        { header: 'Y (mm)',             key: 'y',      width: 9  },
        { header: 'Width (mm)',         key: 'w',      width: 11 },
        { header: 'Height (mm)',        key: 'h',      width: 12 },
        { header: 'Ratio (%)',          key: 'ratio',  width: 10 },
    ];
    ordered.forEach((p, i) => {
        const meta = META[String(p.aid)] || {};
        ws.addRow({
            n: i + 1, tag: meta.tag ?? '', name: meta.name ?? '',
            cat: meta.category ?? '', sup: meta.supplier ?? '',
            page: +p.page + 1, x: +p.x, y: +p.y, w: +p.w, h: +p.h,
            ratio: Math.round(p.w / BASE_W * 100),
        });
    });
    ws.getRow(1).font = { bold: true };
    ws.views = [{ state: 'frozen', ySplit: 1 }];

    /* ── Sheet 2: Layout Settings ── */
    const cfg = wb.addWorksheet('Layout Settings');
    cfg.columns = [{ header: 'Setting', key: 'k', width: 20 }, { header: 'Value', key: 'v', width: 30 }];
    [
        ['Paper size',     S.paper === 'custom' ? (S.customW + '×' + S.customH + 'mm') : String(S.paper).toUpperCase()],
        ['Orientation',    S.orient === 'l' ? 'Landscape' : 'Portrait'],
        ['Page (mm)',      g.pw + ' × ' + g.ph],
        ['Margin (mm)',    S.margin],
        ['Gap X / Y (mm)', S.gapX + ' / ' + S.gapY],
        ['Tag ratio',      S.ratio + '%'],
        ['Base tag (mm)',  BASE_W + ' × ' + BASE_H],
        ['Content boost',  S.fontScale + '%'],
        ['Barcode width',  S.barDensity],
        ['Total tags',     PLACED.length],
        ['Pages',          PLACED.reduce((m, p) => Math.max(m, +p.page + 1), 0)],
        ['Exported at',    new Date().toLocaleString()],
    ].forEach(([k, v]) => cfg.addRow({ k, v }));
    cfg.getRow(1).font = { bold: true };

    /* ── Sheet 3: Tag Previews (embedded images) ── */
    const PREVIEW_CAP = 60;
    const previews = ordered.slice(0, PREVIEW_CAP);
    if (previews.length) {
        const pv = wb.addWorksheet('Tag Previews');
        pv.getColumn(1).width = 4;
        const COLS = 4, CELL_W = 170, CELL_H = 110;
        [2, 3, 4].forEach(c => pv.getColumn(c).width = 26);
        pv.getRow(1).font = { bold: true };

        let idx = 0;
        for (const p of previews) {
            const cellEl = stack.querySelector('.placed[data-uid="' + p.uid + '"] .js-tag');
            if (!cellEl) continue;
            try {
                const canvas = await html2canvas(cellEl, { scale: 2, backgroundColor: '#ffffff', logging: false, useCORS: true });
                const imgId = wb.addImage({
                    base64: canvas.toDataURL('image/png').split(',')[1],
                    extension: 'png',
                });
                const col = (idx % COLS);
                const row = Math.floor(idx / COLS);
                const labelRow = row * 8 + 1;
                if (col === 0) {
                    const r = pv.getRow(labelRow);
                    r.getCell(1).value = 'Preview';
                    for (let c = 0; c < COLS; c++) {
                        const tagMeta = META[String(previews[row * COLS + c]?.aid)];
                        if (tagMeta) pv.getCell(labelRow, 2 + c * 1).value = '';
                    }
                }
                pv.addImage(imgId, {
                    tl: { col: 1 + col * 1, row: labelRow },
                    ext: { width: CELL_W, height: CELL_H },
                });
                idx++;
            } catch (e) {}
        }
    }

    const d = new Date();
    const stamp = d.getFullYear() + String(d.getMonth()+1).padStart(2,'0') + String(d.getDate()).padStart(2,'0')
                + '-' + String(d.getHours()).padStart(2,'0') + String(d.getMinutes()).padStart(2,'0');
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: EXCEL_MIME });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'baycis-tags-' + stamp + '.xlsx';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 4000);

    if (ordered.length > PREVIEW_CAP) {
        alert('Excel saved. Preview images were capped at the first ' + PREVIEW_CAP + ' tags (data rows include all ' + ordered.length + ').');
    }
}

/* ══════════ Excel import (.xlsx → queue) ══════════ */
function handleImportFile(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = async function (e) {
        let entries = [];
        try {
            const wb = new ExcelJS.Workbook();
            await wb.xlsx.load(e.target.result);
            const ws = wb.worksheets[0];
            if (!ws) throw new Error('empty workbook');

            /* locate columns from header row (fall back: column 1 = tags) */
            let tagCol = null, copyCol = null;
            const head = ws.getRow(1).values || [];
            head.forEach((v, i) => {
                const h = String(v ?? '').toLowerCase();
                if (/prop.*tag|property/.test(h)) tagCol = i;
                if (/cop(y|ies)/.test(h)) copyCol = i;
            });
            if (tagCol === null && typeof head[1] === 'string' && head[1] !== '') tagCol = 1;

            ws.eachRow((row, num) => {
                if (num === 1 && tagCol !== null) return;      // header consumed
                const vals = row.values || [];
                const rawTag = String(vals[tagCol ?? 1] ?? '').trim();
                if (!rawTag) return;
                const cRaw = copyCol ? parseInt(vals[copyCol]) : NaN;
                entries.push({ tag: rawTag, copies: isNaN(cRaw) ? 1 : clamp(cRaw, 1, 99) });
            });
        } catch (err) {
            alert('Could not read that file as an Excel workbook. Please use .xlsx.');
            return;
        }

        /* dedupe by tag (keep max copies), cap at 200 */
        const byTag = {};
        entries.forEach(en => {
            byTag[en.tag] = Object.assign({ tag: en.tag }, byTag[en.tag] || {}, { copies: Math.max(en.copies, (byTag[en.tag] || {}).copies || 1) });
        });
        let wanted = Object.values(byTag);
        if (wanted.length > 200) {
            wanted = wanted.slice(0, 200);
            alert('More than 200 unique tags listed — importing the first 200.');
        }
        if (!wanted.length) { alert('No property tags found in the file.'); return; }

        try {
            const res = await fetch('/admin/print-studio/resolve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ tags: wanted.map(w => w.tag) }),
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            if (!data.matched.length) {
                alert('None of the ' + wanted.length + ' tags matched assets in the system.');
                return;
            }
            let msg = 'Imported ' + data.matched.length + ' tag(s).';
            if (data.missing.length) {
                msg += '\n\nNot found (' + data.missing.length + '):\n' + data.missing.slice(0, 8).join(', ')
                     + (data.missing.length > 8 ? ' …' : '');
            }
            alert(msg);

            /* stash copies so they survive the reload */
            const pend = {};
            data.matched.forEach(m => {
                const src = wanted.find(w => w.tag === m.tag);
                if (src && src.copies > 1) pend[m.id] = src.copies;
            });
            if (Object.keys(pend).length) localStorage.setItem(PENDING_COPIES_KEY, JSON.stringify(pend));

            const merged = ASSETS.map(a => a.id);
            data.matched.forEach(m => { if (!merged.includes(m.id)) merged.push(m.id); });
            location.href = '/admin/print-studio?ids=' + merged.join(',');
        } catch (err) {
            alert('Import failed: ' + err.message);
        }
    };
    reader.readAsArrayBuffer(file);
}

$('tbImportExcel').addEventListener('click', () => $('importFileInput').click());
$('importFileInput').addEventListener('change', function () {
    if (this.files && this.files[0]) handleImportFile(this.files[0]);
    this.value = '';
});

document.querySelectorAll('#ddExport [data-export]').forEach(b =>
    b.addEventListener('click', async () => {
        const kind = b.dataset.export;
        const labels = {
            pdf:      '<i class="bi bi-file-earmark-pdf text-danger"></i> PDF — all pages',
            'png-page': '<i class="bi bi-file-earmark-image text-primary"></i> PNG — current page',
            'png-all':  '<i class="bi bi-images text-primary"></i> PNG — all pages',
            json:     '<i class="bi bi-filetype-json text-secondary"></i> Layout file (.json)',
            xlsx:     '<i class="bi bi-file-earmark-excel text-success"></i> Excel — tag list (.xlsx)',
        };
        if (kind !== 'xlsx' && kind !== 'json') b.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Working…';
        try {
            if (kind === 'pdf') await exportPDF();
            if (kind === 'png-page') await exportPNG(false);
            if (kind === 'png-all') await exportPNG(true);
            if (kind === 'json') exportJSON();
            if (kind === 'xlsx') exportXLSX();
        } finally {
            b.innerHTML = labels[kind] || kind;
        }
    }));

/* ══════════ Boot ══════════ */
function snapshot() {
    const c = Object.assign({}, S);
    delete c.zoom; delete c.copies; delete c.sort; delete c.manualPages;
    return c;
}
function snapshot() {
    const c = Object.assign({}, S);
    delete c.zoom; delete c.copies; delete c.sort; delete c.manualPages;
    return c;
}
function saveLast() {
    localStorage.setItem(LS_LAST, JSON.stringify({ s: snapshot(), p: PLACED, c: S.copies }));
}

syncUI();
renderQueue();
stack.style.zoom = S.zoom / 100;
render();
})();
</script>
</body>
</html>
