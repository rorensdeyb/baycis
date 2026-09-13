<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Tag - {{ $item->property_tag }}</title>
    @php
        $size = request('size', 'medium');
        $customW = request('custom_w', '');
        $customH = request('custom_h', '');
        $sizeConfig = [
            'small' => ['tagW' => '420px', 'hPad' => '14px', 'vPad' => '8px', 'mainFont' => 12, 'cellFont' => 10, 'footerFont' => 9, 'hdFont' => 13, 'barW' => 1, 'barH' => 28, 'logoW' => '75%'],
            'medium' => ['tagW' => '600px', 'hPad' => '20px', 'vPad' => '10px', 'mainFont' => 14, 'cellFont' => 12, 'footerFont' => 10, 'hdFont' => 16, 'barW' => 1.5, 'barH' => 40, 'logoW' => '85%'],
            'large' => ['tagW' => '780px', 'hPad' => '26px', 'vPad' => '14px', 'mainFont' => 16, 'cellFont' => 14, 'footerFont' => 12, 'hdFont' => 19, 'barW' => 2, 'barH' => 50, 'logoW' => '90%'],
            '2x3' => ['tagW' => '3in', 'hPad' => '10px', 'vPad' => '6px', 'mainFont' => 10, 'cellFont' => 8, 'footerFont' => 7, 'hdFont' => 11, 'barW' => 0.8, 'barH' => 22, 'logoW' => '65%'],
            '2.5x5' => ['tagW' => '5in', 'hPad' => '12px', 'vPad' => '7px', 'mainFont' => 11, 'cellFont' => 9, 'footerFont' => 8, 'hdFont' => 12, 'barW' => 1, 'barH' => 26, 'logoW' => '70%'],
            'a6' => ['tagW' => '148mm', 'hPad' => '16px', 'vPad' => '9px', 'mainFont' => 13, 'cellFont' => 11, 'footerFont' => 9, 'hdFont' => 14, 'barW' => 1.2, 'barH' => 32, 'logoW' => '80%'],
        ];
        if ($size === 'custom' && $customW && $customH) {
            $tagW = intval($customW);
            $ratio = $tagW / 600;
            $cfg = [
                'tagW' => $tagW . 'px',
                'hPad' => round(20 * $ratio) . 'px',
                'vPad' => round(10 * $ratio) . 'px',
                'mainFont' => max(8, round(14 * $ratio)),
                'cellFont' => max(7, round(12 * $ratio)),
                'footerFont' => max(6, round(10 * $ratio)),
                'hdFont' => max(9, round(16 * $ratio)),
                'barW' => max(0.5, round(1.5 * $ratio, 1)),
                'barH' => max(16, round(40 * $ratio)),
                'logoW' => min(95, round(85 * $ratio)) . '%',
            ];
        } else {
            $cfg = $sizeConfig[$size] ?? $sizeConfig['medium'];
        }
    @endphp
    <style>
        body { 
            font-family: Arial, sans-serif; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            padding: 40px; 
            background: #f0f0f0; 
        }
        .size-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding: 12px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-size: 14px;
        }
        .size-bar .label { font-weight: 600; color: #333; }
        .size-btn {
            padding: 6px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }
        .size-btn:hover { background: #f3f4f6; border-color: #9ca3af; }
        .size-btn.active { background: #111827; color: #fff; border-color: #111827; }
        .size-btn.print-now {
            background: #10b981;
            color: #fff;
            border-color: #10b981;
            margin-left: auto;
        }
        .size-btn.print-now:hover { background: #059669; }
        
        .tag-container { 
            width: {{ $cfg['tagW'] }}; 
            background: white; 
            border: 2px solid #000; 
            border-collapse: collapse; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .tag-header {
            text-align: center; 
            font-weight: bold; 
            padding: {{ $cfg['vPad'] }}; 
            border-bottom: 2px solid #000;
            color: #000;
            font-size: {{ $cfg['hdFont'] }}px;
            background-color: {{ $item->supplier->color ?? '#FFFF00' }};
        }
        
        .tag-body { 
            display: flex; 
            align-items: stretch; 
        }
        
        .left-panel { 
            width: 35%; 
            border-right: 2px solid #000; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: flex-start; 
            padding: {{ $cfg['hPad'] }} {{ $cfg['vPad'] }};
            overflow: hidden;
        }
        
        .right-panel { 
            width: 65%; 
            display: flex;
        }
        
        .barcode-placeholder { 
            margin-top: auto; 
            text-align: center;
            width: 100%;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 0;
            height: 100%;
        }
        
        td { 
            border-bottom: 1px solid #000; 
            padding: {{ $cfg['vPad'] }} {{ $cfg['hPad'] }}; 
            font-size: {{ $cfg['cellFont'] }}px; 
            color: #000;
        }
        
        td:first-child { 
            border-right: 1px solid #000; 
            width: 40%; 
            font-weight: 500; 
        }
        
        tr:last-child td { 
            border-bottom: none; 
        }
        
        .tag-footer { 
            text-align: center; 
            font-size: {{ $cfg['footerFont'] }}px; 
            padding: {{ $cfg['vPad'] }}; 
            border-top: 2px solid #000; 
            color: #000;
            font-weight: bold;
        }
        
        .tag-footer span { 
            border-bottom: 1px solid #f8aba6; 
        }

        /* Formerly inline on markup (now in shared tag-card partial) */
        .left-panel img { width: {{ $cfg['logoW'] }}; height: auto; margin-bottom: 15px; }
        .barcode-placeholder img { width: 100%; height: auto; max-height: {{ $cfg['barH'] + 5 }}px; object-fit: contain; display: inline-block; }
        .barcode-placeholder small { font-weight: normal; font-size: {{ $cfg['cellFont'] - 1 }}px; letter-spacing: 0.5px; margin-top: 4px; display: inline-block; }
        .right-panel td strong { font-size: {{ $cfg['mainFont'] }}px; }

        @media print {
            body { 
                background: white; 
                padding: 0; 
            }
            .size-bar { display: none; }
            /* Keep the EXACT selected size on paper — never stretch to page width.
               96 CSS px = 1 printed inch, and in/mm map to real dimensions. */
            .tag-container { 
                width: {{ $cfg['tagW'] }}; 
                max-width: none !important;
                box-shadow: none;
                margin: 0 auto;
                border: 2px solid #000;
                page-break-inside: avoid; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
        }
        @page {
            margin: 8mm;
        }
    </style>
</head>
<body>

    <div class="size-bar" style="flex-wrap:wrap;">
        <span class="label">Tag Size:</span>
        <a href="?size=2x3" class="size-btn {{ $size === '2x3' ? 'active' : '' }}">2×3"</a>
        <a href="?size=small" class="size-btn {{ $size === 'small' ? 'active' : '' }}">Small</a>
        <a href="?size=medium" class="size-btn {{ $size === 'medium' ? 'active' : '' }}">Medium</a>
        <a href="?size=large" class="size-btn {{ $size === 'large' ? 'active' : '' }}">Large</a>
        <a href="?size=2.5x5" class="size-btn {{ $size === '2.5x5' ? 'active' : '' }}">2.5×5"</a>
        <a href="?size=a6" class="size-btn {{ $size === 'a6' ? 'active' : '' }}">A6</a>
        
        <span class="text-secondary mx-1" style="font-size:12px;">|</span>
        
        <form method="GET" style="display:flex;align-items:center;gap:6px;margin:0;" id="customSizeForm">
            <span style="font-size:11px;font-weight:600;color:#6b7280;">Custom:</span>
            <input type="number" name="custom_w" value="{{ $customW ?: '' }}" placeholder="W" style="width:55px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;" min="200" max="1200">
            <span style="font-size:11px;color:#6b7280;">×</span>
            <input type="number" name="custom_h" value="{{ $customH ?: '' }}" placeholder="H" style="width:55px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;" min="200" max="1200">
            <span style="font-size:10px;color:#9ca3af;">px</span>
            <input type="hidden" name="size" value="custom">
            <button type="submit" class="size-btn" style="font-size:10px;padding:4px 10px;">Apply</button>
        </form>
        
        <button class="size-btn print-now" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @include('admin.items.partials.tag-card', ['item' => $item])

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.querySelectorAll('.js-barcode').forEach(function (el) {
            JsBarcode(el, el.dataset.tag, {
                format: "CODE128",
                lineColor: "#000",
                width: {{ $cfg['barW'] }}, 
                height: {{ $cfg['barH'] }},
                displayValue: false,
                margin: 0,
                background: "transparent"
            });
        });
    </script>
</body>
</html>