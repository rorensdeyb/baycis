<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Item;
use App\Observers\ItemObserver;
use App\Models\Transaction;
use App\Observers\TransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Railway serves the app behind HTTPS (dashboard-assigned domain).
        // Local (non-production) environments are unaffected.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register Observers
        Item::observe(ItemObserver::class);
        Transaction::observe(TransactionObserver::class);

        // RBAC-2: Authorization gates
        Gate::define('manage-users', fn ($user) => $user->role === 'admin');
        Gate::define('manage-settings', fn ($user) => $user->role === 'admin');
        Gate::define('view-audit-logs', fn ($user) => $user->role === 'admin');
        Gate::define('force-delete-items', fn ($user) => $user->role === 'admin');
        Gate::define('manage-inventory', fn ($user) => in_array($user->role, ['admin', 'custodian'], true));
    }
}