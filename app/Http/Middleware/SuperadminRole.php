<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperadminRole
{
    public function handle(Request $request, Closure $next)
    {
        $role = strtoupper(trim((string) session('user_role')));
        $role = preg_replace('/\s+/', '_', $role);

        if (!in_array($role, ['SUPERADMIN'], true)) {
            abort(403, 'Unauthorized (superadmin role required).');
        }

        return $next($request);
    }
}