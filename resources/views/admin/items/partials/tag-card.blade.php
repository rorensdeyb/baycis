{{-- ══════════════════════════════════════════════════════════════════
     Property Tag Card — SINGLE SOURCE OF TRUTH for tag markup.
     Consumed by: print-tag.blade.php, print-batch.blade.php,
                  print-studio.blade.php (inside <template> clones).

     Params:
       $item         Item model (category, supplier eager-loaded)
       $headerText   optional — overrides supplier name in the header
       $headerColor  optional — overrides supplier color

     Sizing is NOT inline here. Each consuming view styles the
     .tag-container / .left-panel / .right-panel / .barcode-placeholder /
     .tag-footer classes in its own <style> block (screen + @media print).
     Field rows carry data-field hooks so Print Studio can toggle them.
═══════════════════════════════════════════════════════════════════ --}}
@php
    $tcSupplier = $item->supplier ?? null;
    $tcHeaderColor = $headerColor ?? ($tcSupplier->color ?? '#FFFF00');
    $tcHeaderText = $headerText ?? ($tcSupplier->name ?? 'N/A');
@endphp
<div class="tag-container js-tag" data-item-id="{{ $item->id }}" data-property-tag="{{ $item->property_tag }}">
    <div class="tag-header" style="background-color: {{ $tcHeaderColor }};">{{ $tcHeaderText }}</div>

    <div class="tag-body">
        <div class="left-panel">
            <img src="{{ asset('images/deped-logo.png') }}" alt="DepEd Logo" class="js-logo">

            <div class="barcode-placeholder">
                <img class="js-barcode" data-tag="{{ $item->property_tag }}" alt="Barcode" />
                <br>
                <small class="js-barcode-text">{{ $item->property_tag }}</small>
            </div>
        </div>

        <div class="right-panel">
            <table>
                <tr><td>Property Number</td><td><strong class="js-main">{{ $item->property_tag }}</strong></td></tr>
                <tr data-field="category"><td>Asset Classification</td><td>{{ $item->category->name ?? 'N/A' }}</td></tr>
                <tr data-field="item"><td>Item/Brand/Model</td><td>{{ $item->name }}</td></tr>
                <tr data-field="serial"><td>Serial Number</td><td>{{ $item->serial_number ?? 'N/A' }}</td></tr>
                <tr data-field="cost"><td>Acquisition Cost</td><td>₱{{ number_format($item->acquisition_cost, 2) }}</td></tr>
                <tr data-field="date"><td>Acquisition Date</td><td>{{ \Carbon\Carbon::parse($item->acquisition_date)->format('M d, Y') }}</td></tr>
                <tr data-field="personnel"><td>Accountable Personnel</td><td>{{ $item->accountable_personnel }}</td></tr>
                <tr data-field="signature"><td>Validation Signature</td><td><br></td></tr>
            </table>
        </div>
    </div>

    <div class="tag-footer">TAMPERING <span>OF</span> THIS PROPERTY TAG IS PUNISHABLE BY LAW</div>
</div>
