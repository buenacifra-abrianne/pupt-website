<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FacultyAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Your UI uses session('admin_role'), so we check that.
        $role = session('user_role');

        // Adjust the allowed role value to match your system exactly (e.g., 'Admin', 'ADMIN', 'Administrator')
        if (strtoupper((string)$role) !== 'ADMIN') {
            abort(403, 'Admins only.');
        }

        return $next($request);
    }
}