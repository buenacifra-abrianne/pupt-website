<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCmsSession
{
    public function handle(Request $request, Closure $next)
    {
        // check if may login session
        if (!session()->has('user_id')) {
            return redirect()->route('public.landing')
                ->with('error', 'Please login first.');
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}