<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Item;
use App\Observers\ItemObserver;
use App\Models\Transaction;
use App\Observers\TransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register Observers
        Item::observe(ItemObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}