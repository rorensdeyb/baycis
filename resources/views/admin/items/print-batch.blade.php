<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Batch Print Property Tags</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            padding: 20px; 
            background: #f0f0f0; 
            gap: 30px; /* Space between tags on the screen */
        }
        
        .tag-container { 
            width: 600px; 
            background: white; 
            border: 2px solid #000; 
            border-collapse: collapse; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            page-break-inside: avoid; /* CRITICAL: Prevents a sticker from being cut in half across two printed pages */
        }
        
        .tag-header { text-align: center; font-weight: bold; padding: 10px; border-bottom: 2px solid #000; color: #000; font-size: 16px; }
        .tag-body { display: flex; align-items: stretch; }
        .left-panel { width: 35%; border-right: 2px solid #000; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 20px 10px; overflow: hidden; }
        .right-panel { width: 65%; display: flex; }
        .barcode-placeholder { margin-top: auto; text-align: center; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin: 0; height: 100%; }
        td { border-bottom: 1px solid #000; padding: 8px 12px; font-size: 14px; color: #000; }
        td:first-child { border-right: 1px solid #000; width: 40%; font-weight: 500; }
        tr:last-child td { border-bottom: none; }
        .tag-footer { text-align: center; font-size: 12px; padding: 8px; border-top: 2px solid #000; color: #000; font-weight: bold; }
        .tag-footer span { border-bottom: 1px solid #f8aba6; }

        @media print {
            body { background: white; padding: 0; gap: 20px; }
            .tag-container { 
                width: 100%; 
                border: 2px solid #000;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    @foreach($items as $item)
    <div class="tag-container">
        <div class="tag-header" style="background-color: 
            @php
                $supName = strtoupper($item->supplier->name ?? '');
                if (str_contains($supName, 'LGU')) echo '#00FF00';
                elseif (str_contains($supName, 'MOOE')) echo '#00CCFF';
                elseif (str_contains($supName, 'DONATION') || str_contains($supName, 'DONATED')) echo '#FF99CC';
                else echo '#FFFF00';
            @endphp;">
            Supplier Color ({{ $item->supplier->name ?? 'N/A' }})
        </div>
        
        <div class="tag-body">
            <div class="left-panel">
                <img src="{{ asset('images/deped-logo.png') }}" alt="DepEd Logo" style="width: 85%; height: auto; margin-bottom: 15px;">
                
                <div class="barcode-placeholder">
                    <img id="print-barcode-{{ $item->id }}" style="width: 100%; height: auto; max-height: 45px; object-fit: contain;"/>
                    <br>
                    <small style="font-weight: normal; font-size: 11px; letter-spacing: 0.5px; margin-top: 4px; display: inline-block;">{{ $item->property_tag }}</small>
                </div>
            </div>

            <div class="right-panel">
                <table>
                    <tr><td>Property Number</td><td><strong>{{ $item->property_tag }}</strong></td></tr>
                    <tr><td>Asset Classification</td><td>{{ $item->category->name ?? 'N/A' }}</td></tr>
                    <tr><td>Item/Brand/Model</td><td>{{ $item->name }}</td></tr> 
                    <tr><td>Serial Number</td><td>{{ $item->serial_number ?? 'N/A' }}</td></tr>
                    <tr><td>Acquisition Cost</td><td>₱{{ number_format($item->acquisition_cost, 2) }}</td></tr>
                    <tr><td>Acquisition Date</td><td>{{ \Carbon\Carbon::parse($item->acquisition_date)->format('M d, Y') }}</td></tr>
                    <tr><td>Accountable Personnel</td><td>{{ $item->accountable_personnel }}</td></tr>
                    <tr><td>Validation Signature</td><td><br></td></tr>
                </table>
            </div>
        </div>
        
        <div class="tag-footer">TAMPERING <span>OF</span> THIS PROPERTY TAG IS PUNISHABLE BY LAW</div>
    </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        window.onload = function() {
            // Loop through every item and generate its specific barcode onto its specific img tag
            @foreach($items as $item)
                JsBarcode("#print-barcode-{{ $item->id }}", "{{ $item->property_tag }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 1.5, 
                    height: 40,
                    displayValue: false,
                    margin: 0,
                    background: "transparent"
                });
            @endforeach

            // Give the browser 500ms to finish rendering all barcodes before popping the print window
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>