<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperadminOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Adjust the allowed role value to match your system exactly (e.g., 'Superadmin', 'SUPERADMIN', 'SuperAdministrator')
        $role = strtoupper(trim((string) session('user_role')));
        if (!in_array($role, ['SUPERADMIN'])) {
            abort(403);
        }

        return $next($request);
    }
}