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
            if ($request->expectsJson()
                || $request->ajax()
                || strtolower((string) $request->header('X-Requested-With')) === 'xmlhttprequest'
                || str_contains(strtolower((string) $request->header('Accept')), 'application/json')) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Your session has expired! Please log in again.',
                    'session_expired' => true,
                    'redirect' => route('public.landing'),
                ], 419);
            }

            return redirect()->route('public.landing');
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
