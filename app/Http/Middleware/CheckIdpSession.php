<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckIdpSession
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('IDP MIDDLEWARE HIT');

        $accessToken = session('access_token');

        \Log::info('🔑 TOKEN FROM SESSION', [
            'token' => $accessToken,
        ]);

        if (!$accessToken) {
            \Log::warning('NO TOKEN FOUND');
            return $next($request);
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($accessToken)
                ->get(rtrim(config('services.idp.base_url'), '/') . '/api/v1/me');

            \Log::info('ME RESPONSE', [
                'status' => $response->status(),
            ]);

            if ($response->status() !== 200) {

                \Log::warning('TOKEN INVALID → LOGGING OUT');

                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('public.landing')
                    ->with('error', 'Session expired.');
            }

        } catch (\Exception $e) {

            \Log::error('ME REQUEST FAILED', [
                'error' => $e->getMessage()
            ]);

            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('public.landing');
        }

        return $next($request);
    }
}