<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\AuditLog;

class PrintStudioController extends Controller
{
    /**
     * PRINT-1: Print Studio workspace.
     * Accepts ?ids=1,2,3 (from inventory bulk-select). Layout math is client-side.
     */
    public function index(Request $request)
    {
        $idsString = (string) $request->query('ids', '');
        $ids = collect(explode(',', $idsString))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->values();

        $truncated = false;
        if ($ids->count() > 200) {
            $ids = $ids->take(200);
            $truncated = true;
        }

        $items = $ids->isNotEmpty()
            ? Item::with(['category:id,name', 'tag:id,name', 'supplier:id,name,color'])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn ($i) => $ids->search($i->id))
                ->values()
            : collect();

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'print_studio',
            'table_name'  => 'items',
            'record_id'   => $items->isNotEmpty() ? $items->first()->id : 0,
            'description' => 'Opened Print Studio with ' . $items->count() . ' asset(s) queued for sheet printing'
                . ($truncated ? ' (selection capped at 200)' : ''),
        ]);

        return view('admin.items.print-studio', [
            'items'       => $items,
            'truncated'   => $truncated,
            'schoolName'  => config('app.name', 'BayCIS'),
            'assetsJson'  => $items->map(fn ($i) => [
                'id'       => $i->id,
                'tag'      => $i->property_tag,
                'name'     => $i->name,
                'category' => $i->category?->name,
                'supplier' => $i->supplier?->name,
                'color'    => $i->supplier?->color ?? '#FFFF00',
            ])->values(),
            // Original tag design (mm). All studio sizes are ratios of this.
            'baseW'       => 150,
            'baseH'       => 90,
        ]);
    }

    /**
     * PRINT-6: Quick asset search for the embedded picker.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $items = Item::with('category:id,name')
            ->where(function ($w) use ($q) {
                $w->whereLike('property_tag', "%{$q}%")
                  ->orWhereLike('name', "%{$q}%");
            })
            ->orderBy('property_tag')
            ->limit(10)
            ->get(['id', 'property_tag', 'name']);

        return response()->json(
            $items->map(fn ($i) => [
                'id'           => $i->id,
                'property_tag' => $i->property_tag,
                'name'         => $i->name,
                'category'     => $i->category?->name,
            ])
        );
    }

    /**
     * Excel import: resolve a list of property-tag strings to asset IDs.
     */
    public function resolveTags(Request $request)
    {
        $data = $request->validate([
            'tags'   => 'required|array|max:500',
            'tags.*' => 'string|max:150',
        ]);

        $wanted = collect($data['tags'])
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique()
            ->values();

        $items = Item::whereIn('property_tag', $wanted)->get(['id', 'property_tag']);
        $idByTag = $items->pluck('id', 'property_tag');

        $matched = [];
        $missing = [];
        foreach ($wanted as $tag) {
            if (isset($idByTag[$tag])) {
                $matched[] = ['id' => (int) $idByTag[$tag], 'tag' => $tag];
            } else {
                $missing[] = $tag;
            }
        }

        return response()->json([
            'matched' => $matched,
            'missing' => $missing,
        ]);
    }
}
