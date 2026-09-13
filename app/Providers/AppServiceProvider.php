<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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

        // Case-insensitive LIKE for every search box in the system.
        // Postgres LIKE is case-sensitive (unlike MySQL), so all user
        // searches go through these macros: ILIKE on pgsql, LIKE elsewhere.
        // Usage: ->whereLike('name', "%{$term}%")->orWhereLike('email', ...)
        $likeOperator = fn () => DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        Builder::macro('whereLike', function (string $column, string $value) use ($likeOperator) {
            /** @var Builder $this */
            return $this->where($column, $likeOperator(), $value);
        });
        Builder::macro('orWhereLike', function (string $column, string $value) use ($likeOperator) {
            /** @var Builder $this */
            return $this->orWhere($column, $likeOperator(), $value);
        });

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