<?php

namespace App\Http\Middleware;

use App\Support\CmsSections;
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
            return redirect('public.landing');
        }

        $role = CmsSections::normalizeRole((string) session('user_role'));
        $roles = session('user_roles', [session('user_role')]);

        if (in_array($role, ['SUPERADMIN'], true)) {
            return redirect('/superadmin/dashboard');
        }

        if (empty(CmsSections::tabsForRoles($roles))) {
    abort(403, 'Unauthorized (staff role required).');
}

        return $next($request);
    }
}
