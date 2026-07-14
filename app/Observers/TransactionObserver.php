<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    public function updated(Transaction $transaction) 
    {
        // Only log if the status changed
        if ($transaction->wasChanged('status')) {
            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Status Change',
                'table_name' => 'transactions',
                'record_id' => $transaction->id,
                'description' => 'Transaction #' . $transaction->id . ' changed to ' . $transaction->status
            ]);
        }
    }
}