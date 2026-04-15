<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckIdpSession
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ ONLY trust Laravel session
        if (!session('user_logged_in')) {
            return redirect()->route('public.landing');
        }

        return $next($request);
    }
}