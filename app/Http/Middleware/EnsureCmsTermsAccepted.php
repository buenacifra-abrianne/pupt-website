<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_logged_in')) {
            return $next($request);
        }

        if ((bool) session('terms_accepted', false)) {
            return $next($request);
        }

        if ($request->routeIs('cms.terms.*') || $request->routeIs('superadmin.logout')) {
            return $next($request);
        }

        if (
            $request->routeIs('admin.dashboard') ||
            $request->routeIs('staff.dashboard') ||
            $request->routeIs('superadmin.dashboard')
        ) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'Terms and Conditions must be accepted before accessing the CMS.',
            ], 423);
        }

        return redirect()->route('cms.terms.blocked');
    }
}
