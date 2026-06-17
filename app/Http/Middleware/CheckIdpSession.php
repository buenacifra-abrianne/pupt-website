<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class CheckIdpSession
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('IDP MIDDLEWARE HIT');

        if (!session('user_logged_in')) {
            return $next($request);
        }

        $accessToken = (string) ($request->cookie('access_token') ?: session('access_token') ?: '');
        $isIdpBackedSession = !empty(session('oneportal_id'))
            || !empty(session('access_token'))
            || !empty($request->cookie('access_token'));

        \Log::info('TOKEN CHECK', [
            'has_session_token' => !empty(session('access_token')),
            'has_cookie_token' => !empty($request->cookie('access_token')),
            'has_oneportal_id' => !empty(session('oneportal_id')),
            'is_idp_backed_session' => $isIdpBackedSession,
        ]);

        \Log::info('TOKEN SOURCE DEBUG', [
            'session_token_prefix' => session('access_token') ? substr(session('access_token'), 0, 20) : null,
            'cookie_token_prefix' => $request->cookie('access_token') ? substr($request->cookie('access_token'), 0, 20) : null,
        ]);

        \Log::info('FULL TOKEN', [
            'token' => $request->cookie('access_token'),
        ]);

        if (!$isIdpBackedSession) {
            return $next($request);
        }

        if ($accessToken === '') {
            \Log::warning('NO TOKEN FOUND FOR IDP-BACKED SESSION');
            return $this->forceLogout($request);
        }

        try {
            $http = Http::withToken($accessToken);

            if (app()->environment(['local', 'testing'])) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get(
                rtrim(config('services.idp.base_url'), '/') . '/api/v1/me'
            );

            \Log::info('ME RESPONSE', [
                'status' => $response->status(),
            ]);

            if ($response->status() !== 200) {
                \Log::warning('TOKEN INVALID -> LOGGING OUT');
                return $this->forceLogout($request);
            }
        } catch (\Throwable $e) {
            \Log::error('ME REQUEST FAILED', [
                'error' => $e->getMessage(),
            ]);

            return $this->forceLogout($request);
        }

        return $next($request);
    }

    protected function forceLogout(Request $request): Response
    {
        Auth::logout();

        $request->session()->forget([
            'access_token',
            'refresh_token',
            'oneportal_id',
            'user_logged_in',
            'user_id',
            'user_email',
            'user_first_name',
            'user_middle_name',
            'user_last_name',
            'user_name',
            'user_role',
            'role',
            'user_roles',
            'user_profile_picture',
            'terms_accepted',
        ]);
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->header('X-CMS-IDP-CHECK') === '1') {
            return response()->json([
                'ok' => false,
                'message' => 'Your session has expired! Please log in again.',
                'session_expired' => true,
                'logout' => true,
                'redirect' => route('public.landing'),
            ], 419);
        }

        $response = redirect()->route('public.landing')
            ->with('error', 'Your session has expired! Please log in again.');

        $cookieNames = [
            'access_token',
            'refresh_token',
            'jwt_token',
            config('session.cookie'),
            'XSRF-TOKEN',
        ];

        foreach ($cookieNames as $cookieName) {
            $response->withCookie(cookie()->forget($cookieName, '/'));
        }

        return $response;
    }
}
