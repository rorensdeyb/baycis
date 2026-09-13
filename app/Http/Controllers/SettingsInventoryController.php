<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\AssetTag;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;

class SettingsInventoryController extends Controller
{
    // ==========================================
    // CATEGORIES CRUD
    // ==========================================
    public function categories()
    {
        $categories = Cache::remember('categories.withCount.api', 86400, fn() =>
            Category::withCount('items')->orderBy('name')->get()
        );
        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'ppe_sub_major' => $request->ppe_sub_major ?? '',
            'gl_ledger_acct' => $request->gl_ledger_acct ?? '',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create',
            'table_name' => 'categories',
            'record_id' => $category->id,
            'description' => 'Created asset category: ' . $category->name,
        ]);

        // Cache invalidation for settings page + API endpoints
        Cache::forget('categories.withCount');
        Cache::forget('categories.withCount.api');
        Cache::forget('categories.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }
        return redirect()->back()->with('success', 'Category "'.$category->name.'" created!');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
        ]);

        $oldName = $category->name;
        $category->update([
            'name' => $request->name,
            'ppe_sub_major' => $request->ppe_sub_major ?? '',
            'gl_ledger_acct' => $request->gl_ledger_acct ?? '',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update',
            'table_name' => 'categories',
            'record_id' => $category->id,
            'description' => 'Updated asset category: ' . $oldName . ' → ' . $category->name,
        ]);

        Cache::forget('categories.withCount');
        Cache::forget('categories.withCount.api');
        Cache::forget('categories.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }
        return redirect()->back()->with('success', 'Category updated!');
    }

    public function destroyCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;

        // Check if category has items
        if ($category->items()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete category with existing assets.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete "'.$name.'" — it has '.$category->items()->count().(' asset(s) attached.'));
        }

        // App-level cascade: remove this category's tags and unlink them from items
        $tagIds = \App\Models\AssetTag::where('category_id', $id)->pluck('id');
        if ($tagIds->isNotEmpty()) {
            \DB::table('items')->whereIn('tag_id', $tagIds)->update(['tag_id' => null]);
            \App\Models\AssetTag::whereIn('id', $tagIds)->delete();
        }

        $category->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete',
            'table_name' => 'categories',
            'record_id' => $id,
            'description' => 'Deleted asset category: ' . $name,
        ]);

        Cache::forget('categories.withCount');
        Cache::forget('categories.withCount.api');
        Cache::forget('categories.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Category "'.$name.'" deleted!');
    }

    // ==========================================
    // SUPPLIERS CRUD
    // ==========================================
    public function suppliers()
    {
        $suppliers = Cache::remember('suppliers.withCount.api', 86400, fn() =>
            Supplier::withCount('items')->orderBy('name')->get()
        );
        return response()->json($suppliers);
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'color' => 'nullable|string|max:7',
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'color' => $request->color ?? '#FFFF00',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create',
            'table_name' => 'suppliers',
            'record_id' => $supplier->id,
            'description' => 'Created supplier: ' . $supplier->name,
        ]);

        Cache::forget('suppliers.withCount');
        Cache::forget('suppliers.withCount.api');
        Cache::forget('suppliers.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }
        return redirect()->back()->with('success', 'Supplier "'.$supplier->name.'" created!');
    }

    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,'.$id,
            'color' => 'nullable|string|max:7',
        ]);

        $oldName = $supplier->name;
        $supplier->update([
            'name' => $request->name,
            'color' => $request->color ?? '#FFFF00',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update',
            'table_name' => 'suppliers',
            'record_id' => $supplier->id,
            'description' => 'Updated supplier: ' . $oldName . ' → ' . $supplier->name,
        ]);

        Cache::forget('suppliers.withCount');
        Cache::forget('suppliers.withCount.api');
        Cache::forget('suppliers.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }
        return redirect()->back()->with('success', 'Supplier updated!');
    }

    public function destroySupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $name = $supplier->name;

        if ($supplier->items()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete supplier with existing assets.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete "'.$name.'" — it has '.$supplier->items()->count().(' asset(s) attached.'));
        }

        $supplier->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete',
            'table_name' => 'suppliers',
            'record_id' => $id,
            'description' => 'Deleted supplier: ' . $name,
        ]);

        Cache::forget('suppliers.withCount');
        Cache::forget('suppliers.withCount.api');
        Cache::forget('suppliers.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Supplier "'.$name.'" deleted!');
    }

    // ==========================================
    // LOCATIONS CRUD
    // ==========================================
    public function locations()
    {
        $locations = Cache::remember('locations.withCount.api', 86400, fn() =>
            Location::withCount('items')->orderBy('name')->get()
        );
        return response()->json($locations);
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'code' => 'nullable|string|max:50',
        ]);

        $location = Location::create([
            'name' => $request->name,
            'code' => $request->code ?? '',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create',
            'table_name' => 'locations',
            'record_id' => $location->id,
            'description' => 'Created location: ' . $location->name . ($location->code ? ' ('.$location->code.')' : ''),
        ]);

        Cache::forget('locations.withCount');
        Cache::forget('locations.withCount.api');
        Cache::forget('locations.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'location' => $location]);
        }
        return redirect()->back()->with('success', 'Location "'.$location->name.'" created!');
    }

    public function updateLocation(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,'.$id,
            'code' => 'nullable|string|max:50',
        ]);

        $oldName = $location->name;
        $location->update([
            'name' => $request->name,
            'code' => $request->code ?? '',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update',
            'table_name' => 'locations',
            'record_id' => $location->id,
            'description' => 'Updated location: ' . $oldName . ' → ' . $location->name,
        ]);

        Cache::forget('locations.withCount');
        Cache::forget('locations.withCount.api');
        Cache::forget('locations.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'location' => $location]);
        }
        return redirect()->back()->with('success', 'Location updated!');
    }

    public function destroyLocation(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $name = $location->name;

        if ($location->items()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete location with existing assets.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete "'.$name.'" — it has '.$location->items()->count().(' asset(s) attached.'));
        }

        $location->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete',
            'table_name' => 'locations',
            'record_id' => $id,
            'description' => 'Deleted location: ' . $name,
        ]);

        Cache::forget('locations.withCount');
        Cache::forget('locations.withCount.api');
        Cache::forget('locations.withCount.v2');

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Location "'.$name.'" deleted!');
    }

    // ==========================================
    // ASSET TAGS (SUB-CATEGORIES) CRUD
    // ==========================================
    public function storeTag(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:100',
        ]);

        $exists = AssetTag::where('category_id', $request->category_id)
            ->where('name', $request->name)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'That tag already exists for the selected category.');
        }

        $tag = AssetTag::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Create',
            'table_name'  => 'asset_tags',
            'record_id'   => $tag->id,
            'description' => "Created asset tag \"{$tag->name}\" under category {$tag->category->name}",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'tag' => $tag]);
        }
        return redirect()->back()->with('success', "Tag \"{$tag->name}\" added!");
    }

    public function updateTag(Request $request, $id)
    {
        $tag = AssetTag::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:100',
        ]);

        $duplicate = AssetTag::where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();
        if ($duplicate) {
            return redirect()->back()->with('error', 'That tag already exists for the selected category.');
        }

        $oldName = $tag->name;
        $tag->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Update',
            'table_name'  => 'asset_tags',
            'record_id'   => $tag->id,
            'description' => "Renamed asset tag \"{$oldName}\" to \"{$tag->name}\"",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'tag' => $tag]);
        }
        return redirect()->back()->with('success', 'Tag updated!');
    }

    public function destroyTag(Request $request, $id)
    {
        $tag = AssetTag::findOrFail($id);
        $name = $tag->name;
        $inUse = $tag->items()->count();

        // Unlink items first (no DB FK — app-level nullOnDelete equivalent)
        \DB::table('items')->where('tag_id', $tag->id)->update(['tag_id' => null]);
        $tag->delete();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Delete',
            'table_name'  => 'asset_tags',
            'record_id'   => $id,
            'description' => "Deleted asset tag \"{$name}\"" . ($inUse > 0 ? " (was assigned to {$inUse} item(s))" : ''),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', "Tag \"{$name}\" deleted!");
    }
}
