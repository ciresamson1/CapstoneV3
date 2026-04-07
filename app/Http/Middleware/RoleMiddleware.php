<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRole = strtolower((string) auth()->user()->role);
        $allowedRoles = array_map(fn ($role) => strtolower((string) $role), $roles);

        if (!in_array($userRole, $allowedRoles, true)) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized');
            }

            return redirect()->route('dashboard')->with('status', 'You do not have access to the admin dashboard.');
        }

        return $next($request);
    }
}