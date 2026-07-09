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

        $response = $next($request);

        // Add Cache-Control headers to prevent Back button caching for all authenticated routes
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
