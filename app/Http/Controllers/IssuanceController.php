<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ConsumableIssuance;
use App\Models\ConsumableStock;
use App\Models\User;
use App\Services\IssuanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IssuanceController extends Controller
{
    /* ═══════════════════════════════════════════
       Page: Issuance workspace (KPIs + 3 tabs)
    ═══════════════════════════════════════════ */

    public function index(Request $request)
    {
        $now = now();

        /* ── KPI pills ── */
        $issuedThisWeek = ConsumableIssuance::whereIn('status', ['issued', 'confirmed'])
            ->whereBetween('issued_at', [$now->copy()->startOfWeek(), $now])->count();
        $issuedThisMonth = ConsumableIssuance::whereIn('status', ['issued', 'confirmed'])
            ->whereBetween('issued_at', [$now->copy()->startOfMonth(), $now])->count();

        /* ── Tab datasets ── */
        $stocks = ConsumableStock::orderBy('name')->get();
        $lastIssued = ConsumableIssuance::selectRaw('item_id, MAX(issued_at) as last_issued')
            ->groupBy('item_id')->pluck('last_issued', 'item_id');
        $stocks->each(fn ($s) => $s->last_issued_at = $lastIssued[$s->id] ?? null);

        $pendingRequests = ConsumableIssuance::with(['user:id,name,email', 'item:id,name,unit'])
            ->where('status', 'pending_issue')
            ->where('initiated_by', 'borrower')
            ->orderBy('created_at')
            ->paginate(10, ['*'], 'requests_page')
            ->withQueryString();

        $pendingConfirmations = ConsumableIssuance::with(['user:id,name,email', 'item:id,name,unit', 'issuer:id,name'])
            ->where('status', 'issued')
            ->orderBy('issued_at', 'desc')
            ->paginate(10, ['*'], 'confirm_page')
            ->withQueryString();

        $historyQuery = ConsumableIssuance::with(['user:id,name,email', 'item:id,name,unit', 'issuer:id,name'])
            ->whereIn('status', ['confirmed', 'cancelled']);

        // ISS-5: status chips + date range + search
        if ($request->filled('status') && in_array($request->status, ['confirmed', 'cancelled'])) {
            $historyQuery->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $historyQuery->whereDate('updated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $historyQuery->whereDate('updated_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $historyQuery->where(function ($q) use ($s) {
                $q->whereHas('user', fn ($u) => $u->whereLike('name', "%{$s}%"))
                  ->orWhereHas('item', fn ($i) => $i->whereLike('name', "%{$s}%"))
                  ->orWhereLike('purpose', "%{$s}%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 10;
        $history = $historyQuery->latest('updated_at')->paginate($perPage, ['*'], 'history_page')->withQueryString();

        $users = User::where('role', 'borrower')->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'email', 'teacher_id']);

        if ($request->ajax() || $request->has('ajax_pending')) {
            return view('admin.partials.issuance-pending-panel', compact('pendingRequests', 'pendingConfirmations'))->render();
        }

        return view('admin.issuance', compact(
            'stocks', 'pendingRequests', 'pendingConfirmations', 'history', 'users',
            'issuedThisWeek', 'issuedThisMonth'
        ));
    }

    /* ═══════════════════════════════════════════
       Stocks: create / update / delete / replenish / search
    ═══════════════════════════════════════════ */

    public function addStock(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity'  => 'required|integer|min:1',
            'unit'      => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'notes'     => 'nullable|string|max:500',
        ]);

        // ISS-1: case-insensitive smart match on name+unit
        $stock = ConsumableStock::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->item_name))])
            ->whereRaw('LOWER(unit) = ?', [mb_strtolower(trim($request->unit))])
            ->first();

        $created = false;
        if ($stock) {
            $stock->increment('stock_quantity', $request->quantity);
            if ($request->filled('min_stock')) $stock->update(['min_stock' => (int) $request->min_stock]);
        } else {
            $stock = ConsumableStock::create([
                'name'           => trim($request->item_name),
                'unit'           => trim($request->unit),
                'stock_quantity' => $request->quantity,
                'reorder_level'  => 5,
                'min_stock'      => $request->filled('min_stock') ? (int) $request->min_stock : null,
                'notes'          => $request->notes,
            ]);
            $created = true;
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => $created ? 'New Consumable Stock Added' : 'Stock Increased',
            'table_name'  => 'consumable_stocks',
            'record_id'   => $stock->id,
            'description' => ($created ? "Created stock item: {$stock->name} ({$stock->stock_quantity} {$stock->unit})"
                                      : "+{$request->quantity} {$stock->unit} → \"{$stock->name}\" (now {$stock->stock_quantity})"),
        ]);

        self::alertLowStock($stock);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'created' => $created,
                'stock'   => ['id' => $stock->id, 'name' => $stock->name, 'stock_quantity' => $stock->stock_quantity,
                              'unit' => $stock->unit, 'min_stock' => $stock->min_stock],
            ]);
        }
        return redirect()->back()->with('success', $created ? "Stock item created!" : "Stock updated!");
    }

    public function updateStock(Request $request, $id)
    {
        $stock = ConsumableStock::findOrFail($id);
        $request->validate([
            'name'         => 'required|string|max:255',
            'unit'         => 'required|string|max:50',
            'min_stock'    => 'nullable|integer|min:0',
            'notes'        => 'nullable|string|max:500',
            'add_quantity' => 'nullable|integer|min:1|max:100000',
        ]);

        $old = $stock->name;
        $stock->update([
            'name'      => trim($request->name),
            'unit'      => trim($request->unit),
            'min_stock' => $request->filled('min_stock') ? (int) $request->min_stock : null,
            'notes'     => $request->notes,
        ]);

        if ($request->filled('add_quantity')) {
            $stock->increment('stock_quantity', (int) $request->add_quantity);
            AuditLog::create([
                'user_id' => Auth::id(), 'action' => 'Stock Increased (Edit)', 'table_name' => 'consumable_stocks',
                'record_id' => $stock->id,
                'description' => "+{$request->add_quantity} {$stock->unit} → \"{$stock->name}\" (now {$stock->stock_quantity}) via edit",
            ]);
            self::alertLowStockClear($stock);
        }

        AuditLog::create([
            'user_id' => Auth::id(), 'action' => 'Stock Item Updated', 'table_name' => 'consumable_stocks',
            'record_id' => $stock->id,
            'description' => "Renamed/edited stock: \"{$old}\" → \"{$stock->name}\" (min: {$stock->getMinStockThreshold()} {$stock->unit})",
        ]);

        return response()->json(['success' => true, 'stock' => $stock->only(['id','name','unit','min_stock','notes','stock_quantity'])]);
    }

    public function deleteStock(Request $request, $id)
    {
        $stock = ConsumableStock::findOrFail($id);

        // ISS-10 guard: never orphan issuance history
        $issuanceCount = ConsumableIssuance::where('item_id', $stock->id)->count();
        if ($issuanceCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "\"{$stock->name}\" has {$issuanceCount} issuance record(s). Archiving is not available yet — remove its history first or keep the item.",
            ], 422);
        }

        AuditLog::create([
            'user_id' => Auth::id(), 'action' => 'Consumable Stock Deleted', 'table_name' => 'consumable_stocks',
            'record_id' => $stock->id, 'description' => "Deleted stock item: {$stock->name}",
        ]);
        $stock->delete();

        return response()->json(['success' => true]);
    }

    public function replenish(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100000',
            'notes'    => 'nullable|string|max:500',
        ]);

        $stock = ConsumableStock::findOrFail($id);
        $stock->increment('stock_quantity', $request->quantity);

        AuditLog::create([
            'user_id' => Auth::id(), 'action' => 'Stock Increased', 'table_name' => 'consumable_stocks',
            'record_id' => $stock->id,
            'description' => "+{$request->quantity} {$stock->unit} → \"{$stock->name}\" (now {$stock->stock_quantity})"
                          . ($request->notes ? ' — ' . $request->notes : ''),
        ]);

        self::alertLowStockClear($stock);

        return response()->json([
            'success' => true,
            'message' => "+{$request->quantity} {$stock->unit} added to \"{$stock->name}\".",
            'stock_quantity' => $stock->stock_quantity,
            'low' => $stock->isLowStock() || $stock->isOutOfStock(),
        ]);
    }

    /** ISS-2: searchable stocks endpoint (quick-add pickers + scanner prefill). */
    public function searchStocks(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = ConsumableStock::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->whereLike('name', "%{$q}%");
            })
            ->orderBy('name')->limit(8);

        return response()->json($query->get(['id', 'name', 'unit', 'stock_quantity', 'min_stock'])
            ->map(fn ($s) => [
                'id' => $s->id, 'name' => $s->name, 'unit' => $s->unit,
                'stock' => $s->stock_quantity, 'low' => $s->isLowStock() || $s->isOutOfStock(),
            ]));
    }

    /* ═══════════════════════════════════════════
       Mutations — all delegated to IssuanceService
       (single source of truth; zero duplicated math)
    ═══════════════════════════════════════════ */

    public function adminInitiateIssue(Request $request)
    {
        $request->validate([
            'item_id'     => 'required|exists:consumable_stocks,id',
            'user_id'     => 'required|exists:users,id',
            'quantity'    => 'required|integer|min:1',
            'purpose'     => 'required|string|max:500',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        try {
            $issuance = IssuanceService::issueDirect(
                (int) $request->item_id, (int) $request->user_id, (int) $request->quantity,
                $request->purpose, Auth::id(), $request->admin_notes
            );
        } catch (\DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $issuance->id]);
        }
        return redirect()->back()->with('success', 'Item issued! Borrower must confirm receipt.');
    }

    /** ISS-3: multi-line kit issue — atomic per ISS spec. */
    public function bulkIssue(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'purpose'    => 'required|string|max:500',
            'admin_notes'=> 'nullable|string|max:500',
            'partial_ok' => 'boolean',
            'lines'      => 'required|array|min:1|max:25',
            'lines.*.item_id' => 'required|integer|distinct|exists:consumable_stocks,id',
            'lines.*.quantity'=> 'required|integer|min:1',
        ]);

        $groupId = (string) \Illuminate\Support\Str::uuid();
        $partialOk = $request->boolean('partial_ok');

        // Pre-check everything inside ONE transaction when partial_ok is false
        if (!$partialOk) {
            try {
                $issuedIds = DB::transaction(function () use ($request, $groupId) {
                    $ids = [];
                    foreach ($request->lines as $line) {
                        // lock first to validate atomically
                        $stock = ConsumableStock::where('id', $line['item_id'])->lockForUpdate()->firstOrFail();
                        if ($stock->stock_quantity < $line['quantity']) {
                            throw new \DomainException("Insufficient stock for \"{$stock->name}\" — {$stock->stock_quantity} {$stock->unit}(s) left.");
                        }
                    }
                    // all lines validated → now deduct & create
                    foreach ($request->lines as $line) {
                        $stock = ConsumableStock::where('id', $line['item_id'])->lockForUpdate()->firstOrFail();
                        $stock->decrement('stock_quantity', $line['quantity']);
                        $iss = ConsumableIssuance::create([
                            'user_id' => $request->user_id, 'item_id' => $line['item_id'],
                            'quantity' => $line['quantity'], 'purpose' => $request->purpose,
                            'status' => 'issued', 'initiated_by' => 'admin',
                            'issued_by' => Auth::id(), 'issued_at' => now(),
                            'admin_notes' => $request->admin_notes, 'group_id' => $groupId,
                        ]);
                        IssuanceService::audit('Consumable Issued (Admin) Kit', $iss, Auth::id(),
                            "Kit line: {$line['quantity']} × {$stock->name} [group {$groupId}]");
                        $ids[] = $iss->id;
                    }
                    return $ids;
                });

                // Notify once after commit
                $first = ConsumableIssuance::find($issuedIds[0]);
                if ($first) {
                    IssuanceService::notifyRecipient($first, 'Consumable Kit Issued',
                        count($issuedIds) . " items issued to you (" . count($request->lines) . " lines).");
                    IssuanceService::notifyStaffExcept($first, 'Kit Issued',
                        count($issuedIds) . " lines issued by staff.", Auth::id());
                }
                foreach (ConsumableIssuance::whereIn('id', $issuedIds)->with('item')->get() as $iss) {
                    IssuanceService::checkLowStock($iss->item);
                }

                return response()->json([
                    'success' => true, 'group_id' => $groupId,
                    'issued_count' => count($issuedIds), 'skipped' => [],
                    'message' => count($issuedIds) . ' line(s) issued.',
                ]);
            } catch (\DomainException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage() . ' — nothing was issued.', 'skipped' => []], 422);
            }
        }

        // partial_ok = true → best-effort per line (each own transaction via service)
        $issuedIds = []; $skipped = [];
        foreach ($request->lines as $index => $line) {
            try {
                $issuance = IssuanceService::issueDirect(
                    (int) $line['item_id'], (int) $request->user_id, (int) $line['quantity'],
                    $request->purpose, Auth::id(), $request->admin_notes, $groupId
                );
                $issuedIds[] = $issuance->id;
            } catch (\DomainException $e) {
                $skipped[] = ['line' => $index + 1, 'reason' => $e->getMessage()];
            }
        }
        if (empty($issuedIds)) {
            return response()->json(['success' => false, 'message' => 'No lines could be issued.', 'skipped' => $skipped], 422);
        }
        return response()->json([
            'success' => true, 'group_id' => $groupId,
            'issued_count' => count($issuedIds), 'skipped' => $skipped,
            'message' => count($issuedIds) . ' line(s) issued' . (count($skipped) ? " · " . count($skipped) . " skipped" : '') . '.',
        ]);
    }

    public function adminFulfillRequest(Request $request, $id)
    {
        $issuance = ConsumableIssuance::with('item')->findOrFail($id);

        if ($issuance->status !== 'pending_issue') {
            $msg = 'Only pending requests can be fulfilled.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        try {
            IssuanceService::fulfill($issuance, Auth::id());
        } catch (\DomainException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : redirect()->back()->with('error', $e->getMessage());
        }

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : redirect()->back()->with('success', 'Request fulfilled!');
    }

    public function adminCancelIssuance(Request $request, $id)
    {
        $issuance = ConsumableIssuance::with('item')->findOrFail($id);
        $reason = trim((string) ($request->input('reason') ?? ''));

        try {
            IssuanceService::cancel($issuance, Auth::id(), $reason);
        } catch (\DomainException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : redirect()->back()->with('error', $e->getMessage());
        }

        // Notify the affected borrower with the reason (fixes the silent-cancel gap)
        IssuanceService::notifyRecipient(
            $issuance, 'Issuance Cancelled',
            "Your request for {$issuance->quantity} × {$issuance->item->name} was cancelled by staff."
            . ($reason !== '' ? ' Reason: ' . $reason : '')
        );
        IssuanceService::notifyStaffExcept($issuance, 'Issuance Cancelled',
            "{$issuance->quantity} × {$issuance->item->name} cancelled by staff.", Auth::id());

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : redirect()->back()->with('success', 'Issuance cancelled.');
    }

    /* ═══════════════════════════════════════════
       History & monitoring
    ═══════════════════════════════════════════ */

    public function stockHistory($id)
    {
        $stock = ConsumableStock::withTrashed()->findOrFail($id);

        $movements = collect();

        foreach (AuditLog::where('table_name', 'consumable_stocks')
            ->where('record_id', $stock->id)
            ->with('user:id,name')
            ->get() as $log) {

            $delta = 0;
            $type = 'info';
            if (str_contains($log->action, 'Increased') || str_contains($log->action, 'Added')) { $type = 'in'; }
            elseif (str_contains($log->action, 'Issued') && str_contains($log->action, '(Admin)')) { $type = 'out'; }
            elseif (str_contains($log->action, 'Fulfilled')) { $type = 'out'; }
            elseif (str_contains($log->action, 'Cancelled')) { $type = 'restore'; }

            $movements->push([
                'type'        => $type,
                'action_label'=> $log->action,
                'description' => $log->description,
                'user_name'   => $log->user?->name ?? 'System',
                'timestamp'   => $log->created_at->toIso8601String(),
            ]);
        }

        // Chronological order so the running balance walks forward correctly
        $movements = $movements->sortBy('timestamp')->values();

        // ISS-4: compute signed delta + resulting balance per movement
        $balance = $stock->stock_quantity;
        $netSince = $movements->reduce(function ($carry, $m) {
            preg_match('/([+-]\d+)/', $m['description'], $mm);
            return $carry - (isset($mm[1]) ? (int) $mm[1] : 0);
        }, 0);
        // Walk from the earliest known balance backwards-derived starting point:
        $startBalance = $balance - $netSince;
        foreach ($movements as $m) {
            preg_match('/([+-]\d+)/', $m['description'], $mm);
            $m['delta'] = isset($mm[1]) ? (int) $mm[1] : 0;
            $startBalance += $m['delta'];
            $m['balance_after'] = max(0, $startBalance);
        }
        $movements = $movements->reverse()->values(); // newest-first display

        return response()->json([
            'stock' => ['id' => $stock->id, 'name' => $stock->name, 'unit' => $stock->unit,
                        'current' => $stock->stock_quantity, 'min' => $stock->getMinStockThreshold()],
            'movements' => $movements,
        ]);
    }

    public function exportHistory(Request $request)
    {
        $query = ConsumableIssuance::with(['user:id,name,email', 'item:id,name,unit', 'issuer:id,name'])
            ->whereIn('status', ['confirmed', 'cancelled']);

        if ($request->filled('status') && in_array($request->status, ['confirmed', 'cancelled'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) $query->whereDate('updated_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('updated_at', '<=', $request->date_to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('user', fn ($u) => $u->whereLike('name', "%{$s}%"))
                  ->orWhereHas('item', fn ($i) => $i->whereLike('name', "%{$s}%"))
                  ->orWhereLike('purpose', "%{$s}%");
            });
        }

        $filename = 'issuance-history-' . now()->format('Ymd-His') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];

        return response()->stream(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Borrower', 'Email', 'Item', 'Quantity', 'Unit', 'Purpose', 'Status',
                           'Initiated By', 'Issued By', 'Requested At', 'Issued At', 'Confirmed At', 'Last Updated']);
            $n = 0;
            $query->chunk(200, function ($rows) use (&$n, $out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        ++$n, $r->user?->name ?? 'Unknown', $r->user?->email ?? '',
                        $r->item?->name ?? 'Unknown', $r->quantity, $r->item?->unit ?? '',
                        $r->purpose, ucfirst($r->status), ucfirst($r->initiated_by),
                        $r->issuer?->name ?? '', $r->created_at?->format('Y-m-d H:i'),
                        $r->issued_at?->format('Y-m-d H:i'), $r->confirmed_at?->format('Y-m-d H:i'),
                        $r->updated_at?->format('Y-m-d H:i'),
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }

    /* ── Low-stock alert helpers (deduped per stock/day) ── */

    private static function alertLowStock(ConsumableStock $stock): void
    {
        if (!($stock->isLowStock() || $stock->isOutOfStock())) return;

        $key = 'lowstock.alert.' . $stock->id . '.' . now()->format('Ymd');
        if (Cache::has($key)) return;
        Cache::put($key, true, now()->endOfDay());

        $state = $stock->isOutOfStock() ? 'OUT OF STOCK' : 'below minimum';
        foreach (User::inventoryStaff() as $u) {
            try {
                \App\Models\Notification::create([
                    'user_id' => $u->id, 'type' => 'alert',
                    'title'   => '⚠ Low Stock Alert',
                    'message' => "\"{$stock->name}\" is {$state} — {$stock->stock_quantity} {$stock->unit}(s) remaining "
                               . "(minimum: {$stock->getMinStockThreshold()}).",
                ]);
            } catch (\Throwable $e) {}
        }
    }

    private static function alertLowStockClear(ConsumableStock $stock): void
    {
        Cache::forget('lowstock.alert.' . $stock->id . '.' . now()->format('Ymd'));
    }

    /**
     * ISS-9: CSV Import for Consumable Stocks
     */
    public function importStocks(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $csvData = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", $csvData));
        
        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'CSV file is empty.',
            ], 400);
        }

        // Detect header row
        $header = array_map('strtolower', array_map('trim', array_shift($rows)));
        $tagCol = array_search('property_tag', $header) ?? array_search('property tag', $header) ?? 0;
        $nameCol = array_search('name', $header) ?? array_search('item_name', $header) ?? 1;
        $categoryCol = array_search('category', $header) ?? array_search('category_id', $header);
        $locationCol = array_search('location', $header) ?? array_search('location_id', $header);
        $supplierCol = array_search('supplier', $header) ?? array_search('supplier_id', $header);
        $quantityCol = array_search('quantity', $header);
        $unitCol = array_search('unit', $header);
        $costCol = array_search('cost', $header) ?? array_search('acquisition_cost', $header);
        $dateCol = array_search('date', $header) ?? array_search('acquisition_date', $header);
        $serialCol = array_search('serial', $header) ?? array_search('serial_number', $header);
        $personnelCol = array_search('personnel', $header) ?? array_search('accountable_personnel', $header);
        $minStockCol = array_search('min_stock', $header) ?? array_search('min_stock_level', $header);
        $reorderCol = array_search('reorder_level', $header) ?? array_search('reorder_level', $header);
        $notesCol = array_search('notes', $header);

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if (empty($row[$nameCol] ?? '')) {
                $errors[] = "Row " . ($index + 2) . ": Missing item name";
                continue;
            }

            $name = trim($row[$nameCol] ?? '');
            $unit = trim($row[$unitCol] ?? 'pcs');
            $quantity = (int)($row[$quantityCol] ?? 0);
            $minStock = $minStockCol !== null && isset($row[$minStockCol]) ? (int)trim($row[$minStockCol]) : null;
            $reorderLevel = $reorderCol !== null && isset($row[$reorderCol]) ? (int)trim($row[$reorderCol]) : 5;
            $notes = $notesCol !== null ? trim($row[$notesCol] ?? '') : null;
            $itemName = trim($row[$nameCol] ?? '');

            // Find existing stock by name (case-insensitive) and unit
            $stock = ConsumableStock::whereRaw('LOWER(name) = ?', [mb_strtolower($itemName)])
                ->where('unit', $unit)
                ->first();

            try {
                if ($stock) {
                    $stock->increment('stock_quantity', max(1, (int)$row[$quantityCol] ?? 1));
                    if ($request->input('update_existing') === 'true') {
                        $stock->update(array_filter([
                            'min_stock' => $minStock,
                            'reorder_level' => $reorderLevel,
                            'notes' => $notes,
                        ]));
                    }
                    $updated++;
                } else {
                    ConsumableStock::create([
                        'name' => $itemName,
                        'unit' => $unit,
                        'stock_quantity' => max(1, $quantity),
                        'min_stock' => $minStock,
                        'reorder_level' => $reorderLevel,
                        'notes' => $notes,
                    ]);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "Import complete: {$created} created, {$updated} updated.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
