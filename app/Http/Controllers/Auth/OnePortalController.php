<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Support\AuditLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OnePortalController extends Controller
{
    
    public function redirectToIdp(Request $request)
    {

        \Log::info('redirectToIdp session check', [
            'user_id' => session('user_id'),
            'role' => session('role'),
            'all' => session()->all(),
        ]);

        $role = strtoupper((string) session('role', ''));

        $allowedRoles = [
            'SUPERADMIN',
            'ADMIN',
            'REGISTRAR',
            'HAP',
            'STUDENT_SERVICES',
            'RESEARCH_EXTENSION',
            'FACULTY',
            'FACULTY_PRO',
            'DENTAL',
            'GUIDANCE',
            'CLINIC',
            'ACCREDITATION',
            'ADMISSIONS',
            'LIBRARY',
            'OJT',
            'CWTS',
            'DIRECTOR_OFFICE',
            'ADMINISTRATION',
        ];

        if (session()->has('user_id') && in_array($role, $allowedRoles, true)) {
            return $this->redirectByRole($role);
        }

        $clientId = config('services.idp.client_id');
        $authorizeUrl = rtrim(config('services.idp.base_url'), '/') . '/api/v1/auth/authorize?client_id=' . urlencode($clientId);

        return redirect()->away($authorizeUrl);
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('public.landing')
                ->with('error', 'IDP login failed: ' . $request->get('error'));
        }

        $code = $request->query('code');

        if (!$code) {
            return redirect()->route('public.landing')
                ->with('error', 'Authorization code missing.');
        }

        //balik sa own callback URL kasama authorization code
        return view('auth.callback', [
            'code' => $code
        ]);
    }

    public function process(Request $request)
{
    // get authorization code
    $code = $request->input('code');

    if (!$code) {
        return redirect()->route('public.landing')
            ->with('error', 'Authorization code missing.');
    }

    // exchange auth code for access token
    $tokenResponse = Http::withoutVerifying()->asJson()->post(
        rtrim(config('services.idp.base_url'), '/') . '/api/v1/auth/token',
        [
            'client_id' => config('services.idp.client_id'),
            'client_secret' => config('services.idp.client_secret'),
            'code' => $code,
        ]
    );

    if (!$tokenResponse->successful()) {
        $errorBody = $tokenResponse->json();
        $errorMessage = is_array($errorBody) && isset($errorBody['error'])
            ? $errorBody['error']
            : 'Token exchange failed.';

        return redirect()->route('public.landing')
            ->with('error', $errorMessage);
    }

    // get access and refresh token from response
    $tokenData = $tokenResponse->json();

    $accessToken = $tokenData['access_token'] ?? null;
    $refreshToken = $tokenData['refresh_token'] ?? null;

    if (!$accessToken) {
        return redirect()->route('public.landing')
            ->with('error', 'Access token missing.');
    }

    // call /me endpoint using access token
    $meResponse = Http::withoutVerifying()->withToken($accessToken)->get(
        rtrim(config('services.idp.base_url'), '/') . '/api/v1/me'
    );

    if (!$meResponse->successful()) {
        $errorBody = $meResponse->json();
        $errorMessage = is_array($errorBody) && isset($errorBody['error'])
            ? $errorBody['error']
            : 'Unable to fetch user information.';

        return redirect()->route('public.landing')
            ->with('error', $errorMessage);
    }

    // get user data from /me response
    $userData = $meResponse->json();

    $id = $userData['id'] ?? null;
    $email = $userData['email'] ?? null;
    $firstName = $userData['first_name'] ?? '';
    $middleName = $userData['middle_name'] ?? '';
    $lastName = $userData['last_name'] ?? '';

    if (!$email) {
        return redirect()->route('public.landing')
            ->with('error', 'Email not found from IDP.');
    }

    // check local user by email
    $user = DB::table('users')
    ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
    ->select(
        'users.*',
        'roles.code as role_code',
        'roles.name as role_name',
        'roles.level as role_level'
    )
    ->where('users.email', $email)
    ->first();

    // if user does not exist locally, deny access
    if (!$user) {
        \App\Services\IncidentResponseService::recordFailure($request->ip(), $email, 'UNKNOWN_USER', 'IDP SSO Login failed: User not found locally');
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.landing')
            ->with('no_role_error', 'You don’t have a role assigned in this system. Please visit the PUP-T Website instead.')
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');
    }

    // sync IDP identity data to local DB
    $updates = [];

    if ((string) ($user->first_name ?? '') !== trim((string) $firstName)) {
        $updates['first_name'] = trim((string) $firstName);
    }

    if ((string) ($user->middle_name ?? '') !== trim((string) $middleName)) {
        $updates['middle_name'] = trim((string) $middleName);
    }

    if ((string) ($user->last_name ?? '') !== trim((string) $lastName)) {
        $updates['last_name'] = trim((string) $lastName);
    }

    if ((string) ($user->email ?? '') !== trim((string) $email)) {
        $updates['email'] = trim((string) $email);
    }

    $fullName = trim(implode(' ', array_filter([
        $firstName,
        $lastName,
    ], fn ($part) => trim((string) $part) !== '')));

    if ((string) ($user->name ?? '') !== $fullName) {
        $updates['name'] = $fullName;
    }

    if ($id && empty($user->oneportal_id)) {
        $updates['oneportal_id'] = $id;
    }

    if (!empty($updates)) {
        $updates['updated_at'] = now();

        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update($updates);

        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select(
                'users.*',
                'roles.code as role_code',
                'roles.name as role_name',
                'roles.level as role_level'
            )
            ->where('users.user_id', $user->user_id)
            ->first();
    }

    // if account is inactive, deny access
    if (isset($user->status) && strtoupper((string) $user->status) !== 'ACTIVE') {
        \App\Services\IncidentResponseService::recordFailure($request->ip(), $email, 'INACTIVE_ACCOUNT', 'IDP SSO Login failed: Account is inactive');
        return redirect()->route('public.landing')
            ->with('error', 'Your CMS account is not active. Please contact Superadmin.');
    }

    // local DB role
    $finalRole = strtoupper(trim((string) ($user->role_code ?? '')));

    $allowedRoles = [
        'SUPERADMIN',
        'ADMIN',
        'REGISTRAR',
        'HAP',
        'STUDENT_SERVICES',
        'RESEARCH_EXTENSION',
        'FACULTY',
        'FACULTY_PRO',
        'DENTAL',
        'GUIDANCE',
        'CLINIC',
        'ACCREDITATION',
        'ADMISSIONS',
        'LIBRARY',
        'OJT',
        'CWTS',
        'DIRECTOR_OFFICE',
        'ADMINISTRATION',
    ];

    if ($finalRole === '' || !in_array($finalRole, $allowedRoles, true)) {
        \App\Services\IncidentResponseService::recordFailure($request->ip(), $email, 'INVALID_ROLE', 'IDP SSO Login failed: User has no valid role');
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.landing')
            ->with('no_role_error', 'You don’t have a role assigned in this system. Please visit the PUP-T Website instead.')
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');
    }

    DB::table('users')
    ->where('user_id', $user->user_id)
    ->update([
        'last_login_at' => now(),
        'updated_at' => now(),
    ]);

    $request->session()->regenerate();

    // create local session only after role is valid
    session([
        'user_logged_in' => true,
        'user_id' => $user->user_id,
        'user_email' => $email ?? $user->email ?? '',
        'user_first_name' => $firstName,
        'user_middle_name' => $middleName,
        'user_last_name' => $lastName,
        'user_name' => $fullName,
        'user_role' => $finalRole,
        'role' => $finalRole,
        'user_roles' => [$finalRole],
        'oneportal_id' => $id,
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
    ]);

    AuditLog::record(
        'LOGIN',
        'AUTHENTICATION',
        'User logged in successfully: ' . (string) ($email ?? $user->email ?? ''),
        (int) ($user->user_id ?? $user->id ?? 0),
        [
            'user_id' => (int) ($user->user_id ?? $user->id ?? 0),
            'user_name' => trim((string) $firstName . ' ' . (string) $lastName),
            'ip_address' => $request->ip(),
        ]
    );

    return $this->redirectByRole($finalRole)
        ->cookie('access_token', $accessToken, 60, '/', null, $request->isSecure(), true, false, 'Lax')
        ->cookie('refresh_token', $refreshToken ?? '', 60, '/', null, $request->isSecure(), true, false, 'Lax');
}

    public function logout(Request $request)
{
    $accessToken = (string) (session('access_token') ?: $request->cookie('access_token') ?: '');
    $refreshToken = (string) (session('refresh_token') ?: $request->cookie('refresh_token') ?: '');
    $logoutMode = strtolower(trim((string) config('services.idp.logout_mode', 'post')));

    AuditLog::record(
        'LOGOUT',
        'AUTHENTICATION',
        'User logged out.',
        (int) session('user_id', 0)
    );

    $isIdpOnline = \Illuminate\Support\Facades\Cache::remember('idp_health_status', 300, function () {
        return \App\Services\IdpHealthChecker::check();
    });

    if (!$isIdpOnline) {
        return $this->buildLoggedOutRedirect($request, 'You have been logged out locally (Central Login system is currently offline).');
    }

    if (in_array($logoutMode, ['get', 'front_channel', 'idp_get'], true)) {
        return $this->handleIdpGetLogout($request, $accessToken);
    }

    $idpRedirectUrl = $this->revokeIdpTokens($request, $accessToken, $refreshToken);

    if ($idpRedirectUrl) {
        $response = $request->expectsJson()
            ? response()->json(['redirect' => $idpRedirectUrl])
            : redirect()->away($idpRedirectUrl);

        return $this->clearLocalSession($request, $response);
    }

    return $this->buildLoggedOutRedirect($request, 'You have been logged out.');
}

    public function idpLogout(Request $request): Response
    {
        $accessToken = (string) (session('access_token') ?: $request->cookie('access_token') ?: '');
        $refreshToken = (string) (session('refresh_token') ?: $request->cookie('refresh_token') ?: '');
        $redirectTo = $this->sanitizeLogoutRedirectTarget((string) $request->query('redirect_to', ''));
        $this->revokeIdpTokens($request, $accessToken, $refreshToken);
        $response = $redirectTo !== ''
            ? redirect($redirectTo)->with('success', 'You have been logged out.')
            : redirect()->route('logout.completed');

        return $this->clearLocalSession($request, $response);
    }

    private function redirectByRole($role)
    {
        $role = strtoupper((string) $role);

        if ($role === 'SUPERADMIN') {
            return redirect('/superadmin/dashboard');
        }

        if ($role === 'ADMIN') {
            return redirect('/admin/dashboard');
        }

        if (
            $role === 'REGISTRAR' ||
            $role === 'HAP' ||
            $role === 'STUDENT_SERVICES' ||
            $role === 'RESEARCH_EXTENSION' ||
            $role === 'FACULTY' ||
            $role === 'FACULTY_PRO' ||
            $role === 'DENTAL' ||
            $role === 'GUIDANCE' ||
            $role === 'CLINIC' ||
            $role === 'ACCREDITATION' ||
            $role === 'ADMISSIONS' ||
            $role === 'LIBRARY' ||
            $role === 'OJT' ||
            $role === 'CWTS' ||
            $role === 'DIRECTOR_OFFICE' ||
            $role === 'ADMINISTRATION'
        ) {
            return redirect('/staff/dashboard');
        }

        return redirect('/');
    }

    private function buildLoggedOutRedirect(Request $request, ?string $message = null): Response
    {
        if ($request->expectsJson()) {
            $response = response()->json(['redirect' => route('public.landing')]);
            return $this->clearLocalSession($request, $response);
        }

        $response = redirect()->route('public.landing');

        if ($message !== null && $message !== '') {
            $response->with('success', $message);
        }

        return $this->clearLocalSession($request, $response);
    }

    private function clearLocalSession(Request $request, Response $response): Response
    {
        Auth::logout();

        $request->session()->forget([
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
            'oneportal_id',
            'access_token',
            'refresh_token',
            'terms_accepted',
        ]);
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->attachCookieExpiryHeaders($response);
    }

    private function revokeIdpTokens(Request $request, ?string $accessToken = null, ?string $refreshToken = null): ?string
    {
        $baseUrl = rtrim((string) config('services.idp.base_url'), '/');
        $configuredLogoutUrl = (string) config('services.idp.logout_url');
        $clientId = (string) config('services.idp.client_id');
        $logoutUrl = $baseUrl !== '' ? $baseUrl . '/api/v1/auth/logout' : $configuredLogoutUrl;

        if ($logoutUrl === '' || $clientId === '') {
            \Log::warning('IDP logout skipped due to missing configuration.', [
                'has_logout_url' => $logoutUrl !== '',
                'has_client_id' => $clientId !== '',
            ]);

            return null;
        }

        if (!$accessToken) {
            \Log::warning('IDP logout skipped because no access token was available for bearer auth.');

            return null;
        }

        $payload = [
            'client_id' => $clientId,
        ];

        try {
            $http = Http::asJson()
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->withOptions([
                    'allow_redirects' => false,
                ])
                ->withToken($accessToken);

            if (app()->environment(['local', 'testing'])) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($logoutUrl . '?client_id=' . urlencode($clientId), $payload);

            if ($response->successful() || $response->status() === 302) {
                $location = (string) $response->header('Location', '');

                \Log::info('IDP tokens revoked successfully.', [
                    'status' => $response->status(),
                    'location' => $location,
                ]);

                return $location !== '' ? $location : null;
            }

            if ($response->status() === 401) {
                \Log::info('IDP logout returned 401; token already invalid.', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            \Log::warning('IDP token revocation failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('IDP token revocation request failed.', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function handleIdpGetLogout(Request $request, ?string $accessToken = null): Response
    {
        $logoutUrl = $this->buildFrontChannelIdpLogoutUrl($accessToken);

        if ($logoutUrl === null) {
            return $this->buildLoggedOutRedirect($request, 'You have been logged out.');
        }

        $response = $request->expectsJson()
            ? response()->json(['redirect' => $logoutUrl])
            : redirect()->away($logoutUrl);

        return $this->clearLocalSession($request, $response);
    }

    private function buildFrontChannelIdpLogoutUrl(?string $accessToken = null): ?string
    {
        $logoutUrl = trim((string) config('services.idp.logout_url_get', ''));
        $clientId = trim((string) config('services.idp.client_id', ''));

        if ($logoutUrl === '' || $clientId === '') {
            return null;
        }

        $userId = $this->fetchIdpUserIdForLogout($accessToken);

        $query = [
            'client_id' => $clientId,
            'user_id' => $userId !== '' ? $userId : null,
        ];

        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');

        return rtrim($logoutUrl, '?') . '?' . http_build_query($query);
    }

    private function fetchIdpUserIdForLogout(?string $accessToken = null): string
    {
        if ($accessToken) {
            try {
                $http = Http::withToken($accessToken);

                if (app()->environment(['local', 'testing'])) {
                    $http = $http->withoutVerifying();
                }

                $response = $http->get(
                    rtrim((string) config('services.idp.base_url'), '/') . '/api/v1/me'
                );

                if ($response->successful()) {
                    $userId = trim((string) ($response->json('id') ?? ''));

                    if ($userId !== '') {
                        return $userId;
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Unable to resolve IDP user id for GET logout.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return trim((string) session('oneportal_id', ''));
    }

    private function sanitizeLogoutRedirectTarget(string $target): string
    {
        $target = trim($target);

        if ($target === '') {
            return '';
        }

        if (str_starts_with($target, '/')) {
            return $target;
        }

        $targetParts = parse_url($target);
        $appUrlParts = parse_url((string) config('app.url'));

        if (
            !is_array($targetParts) ||
            !is_array($appUrlParts) ||
            empty($targetParts['host']) ||
            empty($appUrlParts['host'])
        ) {
            return '';
        }

        if (!hash_equals((string) $appUrlParts['host'], (string) $targetParts['host'])) {
            return '';
        }

        $path = (string) ($targetParts['path'] ?? '/');
        $query = isset($targetParts['query']) ? '?' . $targetParts['query'] : '';

        return $path . $query;
    }

    private function attachCookieExpiryHeaders(Response $response): Response
    {
        $cookieNames = [
            'access_token',
            'refresh_token',
            'jwt_token',
            config('session.cookie'),
            'XSRF-TOKEN',
        ];

        $paths = ['/', ''];
        $domains = array_values(array_unique(array_filter([
            null,
            config('session.domain'),
            parse_url((string) config('app.url'), PHP_URL_HOST),
            $this->normalizeCookieDomain((string) config('session.domain')),
            $this->normalizeCookieDomain((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
        ], fn ($value) => $value !== '')));

        foreach ($cookieNames as $cookieName) {
            foreach ($paths as $path) {
                $response->withCookie(cookie()->forget($cookieName, $path ?: '/'));

                foreach ($domains as $domain) {
                    $response->withCookie(cookie()->forget($cookieName, $path ?: '/', $domain));
                }
            }
        }

        return $response;
    }

    private function normalizeCookieDomain(string $domain): string
    {
        $trimmed = trim($domain);

        if ($trimmed === '') {
            return '';
        }

        return ltrim($trimmed, '.');
    }
}
