<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\AssetTag;
use App\Models\BorrowRequest;
use App\Models\ConsumableIssuance;
use App\Models\ConsumableStock;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemController extends Controller
{
    /* ═══════════════════════════════════════════
       1. MASTER LIST (READ)
       ═══════════════════════════════════════════ */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'location'])->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('property_tag', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = min(50, max(5, (int)($request->per_page ?? 5)));
        $items = $query->paginate($perPage)->appends($request->all());

        $activeCategoryIds = Item::pluck('category_id')->unique();
        $activeCategories = Category::whereIn('id', $activeCategoryIds)->get();

        return view('admin.items.index', compact('items', 'activeCategories'));
    }

    public function export(Request $request)
    {
        $query = Item::with(['category', 'location', 'tag', 'supplier'])->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('property_tag', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $filename = 'inventory-items-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $statusLabels = [
            'available'       => 'Available',
            'ongoodcondition' => 'Good Condition',
            'borrowed'        => 'Borrowed',
            'damaged'         => 'Damaged',
            'maintenance'     => 'Maintenance',
            'disposed'        => 'Disposed',
        ];

        return response()->stream(function () use ($query, $statusLabels) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                '#',
                'Property Tag',
                'Item Name',
                'Category',
                'Asset Tag',
                'Location',
                'Supplier',
                'Status',
                'Accountable Personnel',
                'Serial Number',
                'Acquisition Date',
                'Acquisition Cost',
                'Registered At',
            ]);

            $n = 0;
            $query->chunk(200, function ($items) use (&$n, $out, $statusLabels) {
                foreach ($items as $item) {
                    $statusKey = strtolower((string)$item->status);
                    $status = $statusLabels[$statusKey] ?? ucfirst($item->status ?? '');

                    fputcsv($out, [
                        ++$n,
                        $item->property_tag ?? 'N/A',
                        $item->name ?? 'N/A',
                        $item->category?->name ?? 'N/A',
                        $item->tag?->name ?? 'N/A',
                        $item->location?->name ?? 'N/A',
                        $item->supplier?->name ?? 'N/A',
                        $status,
                        $item->accountable_personnel ?? 'N/A',
                        $item->serial_number ?? 'N/A',
                        $item->acquisition_date ? Carbon::parse($item->acquisition_date)->format('Y-m-d') : 'N/A',
                        $item->acquisition_cost !== null ? number_format((float)$item->acquisition_cost, 2, '.', '') : '0.00',
                        $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'N/A',
                    ]);
                }
            });

            fclose($out);
        }, 200, $headers);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $tagsByCategory = AssetTag::orderBy('name')
            ->get(['id', 'category_id', 'name'])
            ->groupBy('category_id');

        return view('admin.items.create', compact('categories', 'locations', 'suppliers', 'tagsByCategory'));
    }

    public function store(Request $request)
    {
$request->validate([
            'category_id'           => 'required|exists:categories,id',
            'tag_id'                => 'nullable|exists:asset_tags,id',
            'supplier_id'           => 'required|integer',
            'location_id'           => 'required|exists:locations,id',
            'name_brand_model'      => 'required|string|max:255',
            'accountable_personnel' => 'required|string|max:255',
            'acquisition_date'      => 'required|date',
            'acquisition_cost'      => 'required|numeric',
            'quantity'              => 'required|integer|min:1|max:100',
            'status'                => 'required|string|in:available,ongoodcondition,borrowed,damaged,maintenance,disposed',
        ]);

        $category = DB::table('categories')->where('id', $request->category_id)->first();
        $year = date('Y', strtotime($request->acquisition_date));
        $ppe = $category->ppe_sub_major ?? '00';
        $gl = $category->gl_ledger_acct ?? '00';
        $schId = '108200';
        $quantity = $request->quantity;

        $existingBatchItem = DB::table('items')
            ->where('category_id', $request->category_id)
            ->where('supplier_id', $request->supplier_id)
            ->where('acquisition_date', $request->acquisition_date)
            ->where('property_tag', 'LIKE', "{$year}-{$ppe}-{$gl}-%")
            ->first();

        $sppe = '';
        $currentMaxX = 0;

        if ($existingBatchItem) {
            $segments = explode('-', $existingBatchItem->property_tag);
            $sppe = explode('(', $segments[3])[0];

            $batchTags = DB::table('items')
                ->where('property_tag', 'LIKE', "{$year}-{$ppe}-{$gl}-{$sppe}(%")
                ->pluck('property_tag');

            foreach($batchTags as $tag) {
                preg_match('/\((\d+)\)/', $tag, $matches);
                if(isset($matches[1]) && intval($matches[1]) > $currentMaxX) {
                    $currentMaxX = intval($matches[1]);
                }
            }
        } else {
            $allItems = DB::table('items')
                ->where('property_tag', 'LIKE', "{$year}-{$ppe}-{$gl}-%")
                ->pluck('property_tag');
            $maxSppe = 0;
            foreach ($allItems as $tag) {
                $segments = explode('-', $tag);
                if (isset($segments[3])) {
                    $currentSppe = intval(explode('(', $segments[3])[0]);
                    if ($currentSppe > $maxSppe) $maxSppe = $currentSppe;
                }
            }
            $sppe = str_pad($maxSppe + 1, 4, '0', STR_PAD_LEFT);
        }

        $savedItemIds = [];
        $firstTag = "";
        $lastTag = "";

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= $quantity; $i++) {
                $nextX = $currentMaxX + $i;
                $officialPropertyTag = "{$year}-{$ppe}-{$gl}-{$sppe}({$nextX})-{$schId}";

                if ($i === 1) $firstTag = $officialPropertyTag;
                $lastTag = $officialPropertyTag;

                $item = new Item();
                $item->property_tag = $officialPropertyTag;
                $item->name = $request->name_brand_model;
                $item->category_id = $request->category_id;
                $item->tag_id = $request->tag_id;
                $item->supplier_id = $request->supplier_id;
                $item->location_id = $request->location_id;
                $item->accountable_personnel = $request->accountable_personnel;
                $item->acquisition_date = $request->acquisition_date;
                $item->acquisition_cost = $request->acquisition_cost;
                $item->serial_number = $request->serial_number;
                $item->status = $request->status ?? 'available';
                $item->save();

                $savedItemIds[] = $item->id;
            }
            DB::commit();

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Batch Created',
                'table_name'  => 'items',
                'record_id'   => $savedItemIds[0],
                'description' => 'Added ' . $quantity . ' assets: ' . $request->name_brand_model . ' (tags: ' . $firstTag . ' to ' . $lastTag . ')'
            ]);

            return response()->json([
                'success' => true,
                'item_ids' => implode(',', $savedItemIds),
                'first_tag' => $firstTag,
                'quantity' => $quantity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * JSON payload powering the Inventory row drawer (property tag, relations,
     * current holder when borrowed, etc.).
     */
    public function details($id)
    {
        $item = Item::with([
            'category:id,name',
            'tag:id,name',
            'location:id,name,code',
            'supplier:id,name,color',
        ])->findOrFail($id);

        $holder = null;
        if (strtolower($item->status) === 'borrowed') {
            $active = BorrowRequest::with('user:id,name')
                ->where('item_id', $item->id)
                ->whereIn('status', ['approved', 'active'])
                ->latest()
                ->first();
            if ($active && $active->user) {
                $holder = [
                    'name'  => $active->user->name,
                    'since' => $active->created_at?->format('M d, Y'),
                ];
            }
        }

        return response()->json([
            'id'                    => $item->id,
            'property_tag'          => $item->property_tag,
            'name'                  => $item->name,
            'status'                => strtolower($item->status),
            'category'              => $item->category?->name,
            'tag'                   => $item->tag?->name,
            'location'              => $item->location?->name,
            'location_code'         => $item->location?->code,
            'supplier'              => $item->supplier?->name,
            'supplier_color'        => $item->supplier?->color,
            'serial_number'         => $item->serial_number,
            'accountable_personnel' => $item->accountable_personnel,
            'acquisition_date'      => $item->acquisition_date
                ? Carbon::parse($item->acquisition_date)->format('M d, Y') : null,
            'acquisition_cost'      => $item->acquisition_cost !== null
                ? '₱' . number_format($item->acquisition_cost, 2) : null,
            'registered_at'         => $item->created_at?->format('M d, Y'),
            'updated_human'         => $item->updated_at?->diffForHumans(),
            'current_holder'        => $holder,
        ]);
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $tagsByCategory = AssetTag::orderBy('name')
            ->get(['id', 'category_id', 'name'])
            ->groupBy('category_id');

        return view('admin.items.edit', compact('item', 'categories', 'suppliers', 'locations', 'tagsByCategory'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'name_brand_model'      => 'required|string|max:255',
            'category_id'           => 'required|integer',
            'tag_id'                => 'nullable|exists:asset_tags,id',
            'supplier_id'           => 'required|integer',
            'location_id'           => 'required|integer',
            'accountable_personnel' => 'required|string|max:255',
            'acquisition_date'      => 'required|date',
            'acquisition_cost'      => 'required|numeric',
            'serial_number'         => 'nullable|string|max:255',
            'status'                => 'required|string|in:available,ongoodcondition,borrowed,damaged,maintenance,disposed'
        ]);

        $old = $item->name;
        $item->update([
            'name'            => $request->name_brand_model,
            'category_id'     => $request->category_id,
            'tag_id'          => $request->tag_id,
            'supplier_id'     => $request->supplier_id,
            'location_id'     => $request->location_id,
            'accountable_personnel' => $request->accountable_personnel,
            'acquisition_date'    => $request->acquisition_date,
            'acquisition_cost'    => $request->acquisition_cost,
            'serial_number'   => $request->serial_number,
            'status'          => $request->status,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(), 'action' => 'Asset Updated', 'table_name' => 'items',
            'record_id' => $item->id,
            'description' => "Renamed/edited asset: \"{$old}\" → \"{$item->name}\""
        ]);

        if ($request->status === 'disposed') {
            $item->delete();
            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Asset Disposed',
                'table_name'  => 'items',
                'record_id'   => $item->id,
                'description' => "Asset \"{$item->name}\" was disposed and moved to archive.",
            ]);
            return redirect('/admin/inventory')->with('success', 'Asset disposed and moved to archive.');
        }

        return redirect('/admin/inventory')->with('success', 'Asset updated successfully!');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $name = $item->name;
        $item->delete();
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Asset Deleted',
            'table_name'  => 'items',
            'record_id'   => $item->id,
            'description' => "Asset \"{$name}\" deleted from inventory.",
        ]);
        return redirect('/admin/inventory')->with('success', 'Asset removed from inventory successfully.');
    }

    public function archive(Request $request)
    {
        $query = Item::onlyTrashed();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('property_tag', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category !== '') {
            $query->where('category_id', $request->category);
        }

        $sort = $request->get('sort', 'newest');
        $query->with(['category:id,name', 'tag:id,name', 'location:id,name']);
        match ($sort) {
            'oldest'   => $query->orderBy('deleted_at', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default    => $query->orderBy('deleted_at', 'desc'),
        };

        $archivedItems = $query->paginate($request->get('per_page', 10));
        $categories = Category::orderBy('name')->get();

        return view('admin.archive', compact('archivedItems', 'categories'));
    }

    public function archiveStats()
    {
        $total = Item::onlyTrashed()->count();
        $thisMonth = Item::onlyTrashed()->whereMonth('deleted_at', now()->month)
            ->whereYear('deleted_at', now()->year)->count();
        $restoredLastMonth = Item::withoutGlobalScopes()
            ->whereNotNull('deleted_at')
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->count();
        $destroyed = 0;

        return response()->json([
            'total'            => $total,
            'thisMonth'        => $thisMonth,
            'restoredLastMonth' => $restoredLastMonth,
            'destroyed'        => $destroyed,
        ]);
    }

    public function restore($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->status = 'available';
        $item->save();
        $item->restore();
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Asset Restored',
            'table_name'  => 'items',
            'record_id'   => $item->id,
            'description' => "Asset \"{$item->name}\" restored to active inventory.",
        ]);
        return redirect()->route('items.archive')->with('success', 'Asset successfully restored to active inventory.');
    }

    public function bulkRestore(Request $request)
    {
        $request->validate(['ids' => 'required|string']);
        $ids = array_map('intval', explode(',', $request->ids));
        Item::withTrashed()->whereIn('id', $ids)->each(function ($item) {
            $item->status = 'available';
            $item->save();
            $item->restore();
        });

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Bulk Restore',
            'table_name'  => 'items',
            'record_id'   => $ids[0],
            'description' => 'Restored ' . count($ids) . ' item(s) to active inventory.',
        ]);

        return response()->json(['success' => true]);
    }

    public function forceDelete($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $name = $item->name;
        $item->forceDelete();
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Asset Permanently Deleted',
            'table_name'  => 'items',
            'record_id'   => $id,
            'description' => "Asset \"{$name}\" permanently deleted from system.",
        ]);
        return redirect()->route('items.archive')->with('success', 'Asset permanently deleted.');
    }

    public function bulkForceDelete(Request $request)
    {
        $request->validate(['ids' => 'required|string']);
        $ids = array_map('intval', explode(',', $request->ids));
        Item::withTrashed()->whereIn('id', $ids)->each(function ($item) {
            $item->forceDelete();
        });

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Bulk Force Delete',
            'table_name'  => 'items',
            'record_id'   => $ids[0],
            'description' => 'Permanently deleted ' . count($ids) . ' item(s) from system.',
        ]);

        return response()->json(['success' => true]);
    }

    public function availableItems()
    {
        $items = Item::where('status', 'available')
            ->with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'property_tag', 'category_id']);

        return response()->json(['items' => $items]);
    }

    public function transactionHistory(Request $request)
    {
        $query = BorrowRequest::with(['user', 'item.category']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('id', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$searchTerm}%"))
                  ->orWhereHas('item', fn($i) => $i->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('property_tag', 'LIKE', "%{$searchTerm}%"));
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('updated_at', 'desc')->paginate(5);
        return view('admin.history', compact('logs'));
    }

    public function reports(Request $request)
    {
        $settingsPath = storage_path('app/settings.json');
        $defaultSettings = [
            'system_name' => 'BayCIS Inventory Management System',
            'org_name'    => 'Bay Central Elementary School',
        ];

        if (file_exists($settingsPath)) {
            $savedSettings = json_decode(file_get_contents($settingsPath), true);
            $settings = array_merge($defaultSettings, $savedSettings);
        } else {
            $settings = $defaultSettings;
        }

        $totalAssets = Cache::remember('reports.total_assets', 300, fn() => Item::count());
        $borrowedCount = Cache::remember('reports.borrowed_count', 300, fn() => Item::where('status', 'borrowed')->count());
        $damagedCount = Cache::remember('reports.damaged_count', 300, fn() => Item::where('status', 'damaged')->count());
        $totalValue = Cache::remember('reports.total_value', 300, fn() => Item::sum('acquisition_cost'));

        $categories = Category::orderBy('name')->get();

        $reportType = $request->query('report_type', 'summary');
        $categoryId = $request->query('category_id', 'all');
        $status = $request->query('status', 'all');
        $itemStatusOptions = Item::STATUS_OPTIONS;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $reportRecords = collect();
        $trendUrl = null;
        $topItems = null;

        if ($request->has('generate')) {
            if ($reportType === 'borrowing') {
                $query = BorrowRequest::with(['item', 'user']);

                if ($startDate) $query->whereDate('created_at', '>=', $startDate);
                if ($endDate) $query->whereDate('created_at', '<=', $endDate);
                if ($status !== 'all') {
                    if ($status === 'borrowed') {
                        $query->whereIn('status', ['approved', 'active', 'return_pending']);
                    } else {
                        $query->where('status', $status);
                    }
                }
                $reportRecords = $query->orderBy('created_at', 'desc')->get();
            } elseif ($reportType === 'low_stock') {
                $allStocks = ConsumableStock::all();
                $reportRecords = $allStocks->filter(fn($s) => $s->isLowStock() || $s->isOutOfStock())->values();
            } elseif ($reportType === 'consumable_low_stock') {
                // Consumable Low Stock Ledger — all consumable stocks with status
                $reportRecords = ConsumableStock::orderBy('name')->get();
            } elseif ($reportType === 'consumable_summary') {
                $stocks = ConsumableStock::with('category:id,name')
                    ->orderBy('name')
                    ->get();

                $reportRecords = $stocks->map(function ($stock) {
                    return [
                        'item_name' => $stock->name,
                        'category' => $stock->category?->name ?? 'N/A',
                        'unit' => $stock->unit,
                        'current_stock' => $stock->stock_quantity,
                        'min_stock' => $stock->getMinStockThreshold(),
                        'reorder_level' => $stock->reorder_level,
                        'status' => $stock->isOutOfStock() ? 'OUT OF STOCK' :
                                  ($stock->isLowStock() ? 'LOW STOCK' : 'IN STOCK'),
                        'notes' => $stock->notes ?? 'N/A',
                    ];
                });
            } elseif ($reportType === 'consumable_issuance') {
                $query = ConsumableIssuance::with(['user:id,name,email', 'item:id,name,unit', 'issuer:id,name']);

                if ($startDate) $query->whereDate('issued_at', '>=', $startDate);
                if ($endDate) $query->whereDate('issued_at', '<=', $endDate);
                if ($request->filled('status') && $request->status !== 'all') {
                    $query->where('status', $request->status);
                }

                $reportRecords = $query->latest('issued_at')->get()->map(function ($issuance) {
                    return [
                        'id' => $issuance->id,
                        'property_tag' => $issuance->item->property_tag ?? 'N/A',
                        'item_name' => $issuance->item->name ?? 'N/A',
                        'unit' => $issuance->item->unit ?? 'N/A',
                        'quantity' => $issuance->quantity,
                        'purpose' => $issuance->purpose,
                        'status' => ucfirst($issuance->status),
                        'initiated_by' => ucfirst($issuance->initiated_by),
                        'borrower' => $issuance->user->name ?? 'N/A',
                        'borrower_email' => $issuance->user->email ?? 'N/A',
                        'issued_by' => $issuance->issuer->name ?? 'System',
                        'issued_at' => $issuance->issued_at?->format('M d, Y H:i'),
                        'confirmed_at' => $issuance->confirmed_at?->format('M d, Y H:i'),
                        'admin_notes' => $issuance->admin_notes ?? '—',
                    ];
                });
            } elseif ($reportType === 'consumable_trend') {
                $startDate = $startDate ?? now()->subDays(30)->format('Y-m-d');
                $endDate = $endDate ?? now()->format('Y-m-d');

                $trendData = ConsumableIssuance::selectRaw('DATE(issued_at) as day, SUM(quantity) as total')
                    ->whereIn('status', ['issued', 'confirmed'])
                    ->whereBetween('issued_at', [$startDate, $endDate])
                    ->groupBy(DB::raw('DATE(issued_at)'))
                    ->pluck('total', 'day');

                $labels = [];
                $data = [];
                $now = now();
                for ($i = 29; $i >= 0; $i--) {
                    $day = $now->copy()->subDays($i)->format('Y-m-d');
                    $labels[] = $now->copy()->subDays($i)->format('M j');
                    $data[] = (int) ($trendData[$day] ?? 0);
                }

                $topItems = ConsumableIssuance::selectRaw('item_id, SUM(quantity) as total')
                    ->whereIn('status', ['issued', 'confirmed'])
                    ->whereBetween('issued_at', [$startDate, $endDate])
                    ->groupBy('item_id')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->with('item:id,name')
                    ->get();

                $trendUrl = 'https://quickchart.io/chart?w=640&h=190&v=3&bkg=%23ffffff&c=' . urlencode(json_encode([
                    'type' => 'line',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [[
                            'label' => 'Units Issued',
                            'data' => $data,
                            'borderColor' => '#3b82f6',
                            'backgroundColor' => 'rgba(59,130,246,0.07)',
                            'fill' => true,
                            'tension' => 0.4,
                            'pointRadius' => 0,
                            'pointHoverRadius' => 4,
                            'borderWidth' => 2.5,
                        ]],
                    ],
                    'options' => [
                        'plugins' => ['legend' => false],
                        'scales' => [
                            'y' => ['beginAtZero' => true, 'grace' => '15%',
                                    'grid' => ['color' => '#eef2f7'],
                                    'ticks' => ['precision' => 0, 'font' => ['size' => 10], 'color' => '#94a3b8']],
                            'x' => ['grid' => ['display' => false],
                                    'ticks' => ['font' => ['size' => 10], 'maxTicksLimit' => 8, 'color' => '#94a3b8']],
                        ],
                    ],
                ]));

                $reportRecords = collect();
            } else {
                // Disposed assets are soft-deleted when moved to the archive,
                // so include archived records only for that status filter.
                $query = $status === 'disposed'
                    ? Item::onlyTrashed()
                    : Item::query();

                $query->with(['category', 'location']);
                if ($categoryId !== 'all') $query->where('category_id', $categoryId);
                if ($status !== 'all') $query->where('status', $status);
                if ($startDate) $query->whereDate('acquisition_date', '>=', $startDate);
                if ($endDate) $query->whereDate('acquisition_date', '<=', $endDate);
                $reportRecords = $query->get();
            }
        }

        return view('admin.reports', compact(
            'totalAssets', 'borrowedCount', 'damagedCount', 'totalValue',
            'categories', 'reportRecords', 'reportType', 'settings', 'trendUrl', 'topItems',
            'itemStatusOptions'
        ));
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|string',
            'status' => 'required|string|in:available,ongoodcondition,borrowed,damaged,maintenance,disposed',
        ]);

        $ids = array_map('intval', explode(',', $request->ids));
        Item::whereIn('id', $ids)->update(['status' => $request->status]);

        if ($request->status === 'disposed') {
            Item::whereIn('id', $ids)->get()->each(function ($item) {
                $item->delete();
            });
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => $request->status === 'disposed' ? 'Bulk Dispose' : 'Bulk Status Change',
            'table_name'  => 'items',
            'record_id'   => $ids[0],
            'description' => $request->status === 'disposed'
                ? 'Disposed and archived ' . count($ids) . ' item(s).'
                : 'Changed status of ' . count($ids) . ' item(s) to "' . $request->status . '"',
        ]);

        return response()->json(['success' => true]);
    }

    public function stats(Request $request)
    {
        $total    = Cache::remember('inventory.total', 300, fn() => Item::count());
        $available = Cache::remember('inventory.available', 300, fn() => Item::where('status', 'available')->count());
        $goodCond = Cache::remember('inventory.goodcondition', 300, fn() => Item::where('status', 'ongoodcondition')->count());
        $borrowed = Cache::remember('inventory.borrowed', 300, fn() => Item::where('status', 'borrowed')->count());
        $damaged  = Cache::remember('inventory.damaged', 300, fn() => Item::whereIn('status', ['damaged', 'maintenance'])->count());

        return response()->json([
            'total'      => $total,
            'available'  => $available,
            'goodCondition' => $goodCond,
            'borrowed'   => $borrowed,
            'damaged'    => $damaged,
        ]);
    }
}
