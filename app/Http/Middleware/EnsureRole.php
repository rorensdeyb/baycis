<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * RBAC-2: Restrict route access to the given role(s).
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,custodian')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
