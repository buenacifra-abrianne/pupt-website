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

        if (!$accessToken) {
            return redirect()->route('public.landing');
        }

        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get(rtrim(config('services.idp.base_url'), '/') . '/api/v1/me');

        // 🔥 IMPORTANT FIX: treat ANY failure as logout
        if (!$response->ok()) {

            $request->session()->invalidate();
            $request->session()->flush();
            $request->session()->regenerateToken();

            return redirect()->route('public.landing')
                ->with('error', 'Session expired. Please login again.');
        }

        return $next($request);
    }
}