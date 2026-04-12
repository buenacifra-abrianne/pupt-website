<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckIdpSession
{
    public function handle(Request $request, Closure $next)
    {
        $accessToken = session('access_token');

        // If no token → not logged in
        if (!$accessToken) {
            return redirect()->route('public.landing');
        }

        // Validate token via IDP
        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get(rtrim(config('services.idp.base_url'), '/') . '/api/v1/me');

        // If invalid → force logout locally
        if (!$response->successful()) {
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('public.landing')
                ->with('error', 'Session expired. Please login again.');
        }

        return $next($request);
    }
}