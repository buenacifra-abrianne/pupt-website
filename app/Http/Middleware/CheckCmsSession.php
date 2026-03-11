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

        return $next($request);
    }
}