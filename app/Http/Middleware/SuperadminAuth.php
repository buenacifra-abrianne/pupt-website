<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperadminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->get('user_logged_in')) {
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

        return $next($request);
    }
}
