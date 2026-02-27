<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonAdminRoleOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next)
    {
        if (!session('user_logged_in')) {
            return redirect('/faculty/login');
        }

        if (session('user_role') === 'Admin') {
            return redirect('/faculty/dashboard');
        }

        return $next($request);
    }
}
