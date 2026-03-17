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
            return redirect()->route('public.landing');
        }

        return $next($request);
    }
}