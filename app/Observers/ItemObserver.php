<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ItemObserver
{
    // Log when an item is created
    public function created(Item $item)
    {
        if (!Auth::id()) return;
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created',
            'table_name' => 'items',
            'record_id' => $item->id,
            'description' => 'Added new asset: ' . $item->name
        ]);
    }

    // Log when an item is updated
    public function updated(Item $item)
    {
        if (!Auth::id()) return;
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated',
            'table_name' => 'items',
            'record_id' => $item->id,
            'description' => 'Updated asset: ' . $item->name
        ]);
    }

    // Log when an item is deleted
    public function deleted(Item $item)
    {
        if (!Auth::id()) return;
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted',
            'table_name' => 'items',
            'record_id' => $item->id,
            'description' => 'Deleted asset: ' . $item->name
        ]);
    }
}