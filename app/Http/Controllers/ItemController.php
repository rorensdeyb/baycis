<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Supplier;


class ItemController extends Controller
{
    // ==========================================
    // 1. MASTER LIST (READ)
    // ==========================================
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Item::with(['category', 'location'])->latest();

        // 1. Live Text Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('property_tag', 'like', "%{$searchTerm}%");
            });
        }

        // 2. DYNAMIC CATEGORY FILTER
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }

        // 3. STATUS FILTER
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(5)->appends($request->all());

        // BULLETPROOF CATEGORY FETCH: Gets unique category IDs currently in use by items
        $activeCategoryIds = \App\Models\Item::pluck('category_id')->unique();
        $activeCategories = \App\Models\Category::whereIn('id', $activeCategoryIds)->get();

        // FIX: Restored your original view path (admin.items.index)
        return view('admin.items.index', compact('items', 'activeCategories'));
    }

    
    // ==========================================
    // 2. ADD NEW ITEM (CREATE)
    // ==========================================
    public function create()
    {
        $categories = Category::all();
        $locations = Location::all();
        $suppliers = Supplier::all();

        return view('admin.items.create', compact('categories', 'locations', 'suppliers'));
    }

    // ==========================================
    // 2. STORE NEW ITEM (CREATE)
    // ==========================================
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'category_id'           => 'required|exists:categories,id',
            'supplier_id'           => 'required|integer',
            'location_id'           => 'required|exists:locations,id',
            'name_brand_model'      => 'required|string|max:255',
            'accountable_personnel' => 'required|string|max:255',
            'acquisition_date'      => 'required|date',
            'acquisition_cost'      => 'required|numeric',
            'quantity'              => 'required|integer|min:1|max:100' // New Validation
        ]);

        $category = \DB::table('categories')->where('id', $request->category_id)->first();
        $year = date('Y', strtotime($request->acquisition_date));
        $ppe = $category->ppe_sub_major ?? '00';
        $gl = $category->gl_ledger_acct ?? '00';
        $schId = '108200'; // Official DepEd School ID
        $quantity = $request->quantity;

        // 1. Detect if a batch already exists for this exact Category + Supplier + Date
        $existingBatchItem = \DB::table('items')
            ->where('category_id', $request->category_id)
            ->where('supplier_id', $request->supplier_id)
            ->where('acquisition_date', $request->acquisition_date)
            ->where('property_tag', 'LIKE', "{$year}-{$ppe}-{$gl}-%")
            ->first();

        $sppe = '';
        $currentMaxX = 0;

        if ($existingBatchItem) {
            // EXTRACT EXISTING BATCH: Get "0001" from "2026-05-03-0001(5)-108200"
            $segments = explode('-', $existingBatchItem->property_tag);
            $sppe = explode('(', $segments[3])[0]; 

            // Find the highest (X) currently in this specific batch
            $batchTags = \DB::table('items')
                ->where('property_tag', 'LIKE', "{$year}-{$ppe}-{$gl}-{$sppe}(%")->pluck('property_tag');
                
            foreach($batchTags as $tag) {
                preg_match('/\((\d+)\)/', $tag, $matches);
                if(isset($matches[1]) && intval($matches[1]) > $currentMaxX) {
                    $currentMaxX = intval($matches[1]);
                }
            }
        } else {
            // BRAND NEW BATCH: Find the highest SPPE overall and add 1
            $allItems = \DB::table('items')->where('property_tag', 'LIKE', "{$year}-{$ppe}-{$gl}-%")->pluck('property_tag');
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

        // 2. Loop and Save all items in the batch
        $savedItemIds = [];
        $firstTag = "";

        // Use a database transaction so if one fails, they all cleanly cancel
        \DB::beginTransaction();
        try {
            for ($i = 1; $i <= $quantity; $i++) {
                $nextX = $currentMaxX + $i;
                $officialPropertyTag = "{$year}-{$ppe}-{$gl}-{$sppe}({$nextX})-{$schId}";
                
                if ($i === 1) $firstTag = $officialPropertyTag;

                $item = new \App\Models\Item();
                $item->property_tag = $officialPropertyTag;
                $item->name = $request->name_brand_model;
                $item->category_id = $request->category_id;
                $item->supplier_id = $request->supplier_id;
                $item->location_id = $request->location_id;
                $item->accountable_personnel = $request->accountable_personnel;
                $item->acquisition_date = $request->acquisition_date;
                $item->acquisition_cost = $request->acquisition_cost;
                $item->serial_number = $request->serial_number;
                $item->status = 'available';
                $item->save();

                $savedItemIds[] = $item->id;
            }
            \DB::commit();

            return response()->json([
                'success' => true,
                'item_ids' => implode(',', $savedItemIds), // Returns "15,16,17"
                'first_tag' => $firstTag,
                'quantity' => $quantity
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        $item = Item::create($request->all());

        \App\Models\AuditLog::create([
            'user_id'     => \Illuminate\Support\Facades\Auth::id(),
            'action'      => 'Created',
            'table_name'  => 'items',
            'record_id'   => $item->id,
            'description' => 'Added new asset: ' . $item->name
        ]);

        return redirect()->route('items.index')->with('success', 'Item added!');
    }
    // ==========================================
    // 3. EDIT ITEM (UPDATE)
    // ==========================================
    // Show the Edit Form
    public function edit($id)
    {
        // Find the item or throw a 404 if it doesn't exist
        $item = \App\Models\Item::findOrFail($id);
        
        // Fetch dropdown data so the admin can change classifications/locations
        $categories = \DB::table('categories')->get();
        $suppliers = \DB::table('suppliers')->get(); // Assuming you have this table
        $locations = \DB::table('locations')->get(); // Assuming you have this table

        // Send it all to the edit view
        return view('admin.items.edit', compact('item', 'categories', 'suppliers', 'locations'));
    }

    // Handle the Save Action
    public function update(Request $request, $id)
    {
        // 1. Find the exact asset
        $item = \App\Models\Item::findOrFail($id);

        // 2. Validate the incoming form data
        $request->validate([
            'name_brand_model'      => 'required|string|max:255',
            'category_id'           => 'required|integer',
            'supplier_id'           => 'required|integer',
            'location_id'           => 'required|integer',
            'accountable_personnel' => 'required|string|max:255',
            'acquisition_date'      => 'required|date',
            'acquisition_cost'      => 'required|numeric',
            'serial_number'         => 'nullable|string|max:255',
            'status'                => 'required|string'
        ]);

        // 3. Explicitly map the form inputs to your database columns.
        // NOTE: We map the form's 'name_brand_model' to the database's 'name' column!
        $item->name = $request->name_brand_model; 
        $item->category_id = $request->category_id;
        $item->supplier_id = $request->supplier_id;
        $item->location_id = $request->location_id;
        $item->accountable_personnel = $request->accountable_personnel;
        $item->acquisition_date = $request->acquisition_date;
        $item->acquisition_cost = $request->acquisition_cost;
        $item->serial_number = $request->serial_number;
        $item->status = $request->status;

        // 4. Save safely to the database
        $item->save();

        // 5. Redirect back to the master list
        return redirect('/admin/inventory')->with('success', 'Asset updated successfully!');
    }
    // ==========================================
    // PRINT PROPERTY TAG
    // ==========================================
    public function printTag($id)
    {
        $item = \App\Models\Item::with(['category', 'supplier', 'location'])->findOrFail($id);
        return view('admin.items.print-tag', compact('item'));
    }
    public function printBatch(\Illuminate\Http\Request $request)
    {
        // Get the 'ids' parameter from the URL, default to empty string
        $idsString = $request->query('ids', '');
        
        // Convert the string "15,16,17" into an array [15, 16, 17]
        $ids = array_filter(explode(',', $idsString));

        if (empty($ids)) {
            abort(404, 'No items selected for printing.');
        }

        // Fetch all items that match these IDs
        $items = \App\Models\Item::with(['category', 'supplier'])->whereIn('id', $ids)->get();

        if ($items->isEmpty()) {
            abort(404, 'No items found.');
        }

        return view('admin.items.print-batch', compact('items'));
    }
    // ==========================================
    //  DELETE ITEM (DESTROY)
    // ==========================================
    public function destroy($id)
    {
        $item = \App\Models\Item::findOrFail($id);
        $item->delete();
        return redirect('/admin/inventory')->with('success', 'Asset removed from inventory successfully.');
    }
    // 1. View the Archive Page
    public function archive(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Item::onlyTrashed();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('property_tag', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%");
            });
        }

    $archivedItems = $query->orderBy('deleted_at', 'desc')->get();
    return view('admin.archive', compact('archivedItems'));
    }

    // 1. Update your Restore Method
    public function restore($id)
    {
        $item = \App\Models\Item::withTrashed()->findOrFail($id);
        $item->restore();

        // FIX: Replaced hardcoded text strings with the safe named route reference
        return redirect()->route('items.archive')->with('success', 'Asset successfully restored to active inventory.');
    }

    // 2. Update your Force Delete Method (Just to be safe!)
    public function forceDelete($id)
    {
        $item = \App\Models\Item::withTrashed()->findOrFail($id);
        $item->forceDelete();

        // FIX: Replaced hardcoded text strings with the safe named route reference
        return redirect()->route('items.archive')->with('success', 'Asset permanently deleted.');
    }

    // ==========================================
    //  TRANSACTION HISTORY (READ)
    // ==========================================
    // ==========================================
    // ADMIN: TRANSACTION HISTORY DASHBOARD
    // ==========================================
    public function transactionHistory(Request $request)
    {
        // 1. Base query using the new BorrowRequest model
        $query = \App\Models\BorrowRequest::with(['user', 'item.category']);

        // 2. Handle Search Engine (Matches Trans ID, Borrower Name, or Item Tag)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('id', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('user', function($u) use ($searchTerm) {
                      $u->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('item', function($i) use ($searchTerm) {
                      $i->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('property_tag', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        // 3. Handle Status Filtering
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // 4. Fetch the logs (Newest updates first)
        $logs = $query->orderBy('updated_at', 'desc')->paginate(5);

        return view('admin.history', compact('logs'));
    }

    public function reports(Request $request)
    {
        // ==========================================
        // NEW: LOAD FLAT-FILE SETTINGS FOR DEPED HEADER
        // ==========================================
        $settingsPath = storage_path('app/settings.json');
        $defaultSettings = [
            'system_name' => 'BayCIS Inventory Management System',
            'org_name'    => 'Department of Education (DepEd)'
        ];

        if (file_exists($settingsPath)) {
            $savedSettings = json_decode(file_get_contents($settingsPath), true);
            $settings = array_merge($defaultSettings, $savedSettings);
        } else {
            $settings = $defaultSettings;
        }

        // 1. Core Analytics Cards Calculations
        $totalAssets = Item::count();
        $borrowedCount = Item::where('status', 'borrowed')->count();
        $damagedCount = Item::where('status', 'damaged')->count();
        $totalValue = Item::sum('acquisition_cost');

        // 2. Fetch standard filter boundaries
        $categories = Category::all();

        // 3. Process Live Report Generation Filters if submitted
        $reportType = $request->query('report_type', 'summary');
        $categoryId = $request->query('category_id', 'all');
        $status = $request->query('status', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $reportRecords = collect();

        if ($request->has('generate')) {
            if ($reportType === 'borrowing') {
                $query = \App\Models\BorrowRequest::with(['item', 'user']);
                
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
            } else {
                $query = \App\Models\Item::with(['category', 'location']);
                if ($categoryId !== 'all') $query->where('category_id', $categoryId);
                if ($status !== 'all') $query->where('status', $status);
                if ($startDate) $query->whereDate('acquisition_date', '>=', $startDate);
                if ($endDate) $query->whereDate('acquisition_date', '<=', $endDate);
                $reportRecords = $query->get();
            }
        }

        return view('admin.reports', compact(
            'totalAssets', 'borrowedCount', 'damagedCount', 'totalValue', 
            'categories', 'reportRecords', 'reportType', 'settings'
        ));
    }

    public function issuance(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\BorrowRequest::with(['item', 'user'])->whereIn('status', ['approved', 'active']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('item', function($itemQuery) use ($search) {
                    $itemQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('property_tag', 'LIKE', "%{$search}%");
                });
            });
        }

        $issuances = $query->orderBy('created_at', 'desc')->get();

        $availableItems = \App\Models\Item::where('status', 'available')->get();

        return view('admin.issuance', compact('issuances', 'availableItems'));
    }

}