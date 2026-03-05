<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonSuperadminRoleOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next)
    {
        if (!session('user_logged_in')) {
            return redirect('/superadmin/login');
        }

        $role = strtoupper(trim((string) session('user_role')));
        if (!in_array($role, ['SYSTEM_SUPERADMIN', 'GLOBAL_SUPERADMIN'])) {
            return redirect('/superadmin/dashboard');
        }

        return $next($request);
    }
}
