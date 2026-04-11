<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Support\AuditLog;

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
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.landing')
            ->with('no_role_error', 'You have no role in this system. Please check with the superadmin. 
            If you want to switch accounts, please log out from the Identity Provider first.')
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');
    }

    // if user exists but oneportal_id is still empty, update it
    if ($id && empty($user->oneportal_id)) {
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'oneportal_id' => $id,
                'updated_at' => now(),
            ]);

        $user = DB::table('users')
            ->where('user_id', $user->user_id)
            ->first();
    }

    // if account is inactive, deny access
    if (isset($user->status) && strtoupper((string) $user->status) !== 'ACTIVE') {
        return redirect()->route('public.landing')
            ->with('error', 'Your CMS account is not active.');
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
    ];

    if ($finalRole === '' || !in_array($finalRole, $allowedRoles, true)) {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.landing')
            ->with('no_role_error', 'You have no role in this system. Please check with the superadmin.')
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');
    }

    DB::table('users')
    ->where('user_id', $user->user_id)
    ->update([
        'last_login_at' => now(),
        'updated_at' => now(),
    ]);

    // create local session only after role is valid
    session([
        'user_logged_in' => true,
        'user_id' => $user->user_id,
        'user_email' => $email ?? $user->email ?? '',
        'user_first_name' => $firstName,
        'user_middle_name' => $middleName,
        'user_last_name' => $lastName,
        'user_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
        'user_role' => $finalRole,
        'role' => $finalRole,
        'user_roles' => [$finalRole],
        'oneportal_id' => $id,
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'idp_roles' => $idpRoles,
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
    $userId = (int) session('user_id', 0);
    $userName = (string) session('user_name', 'Unknown');

    AuditLog::record(
        'LOGOUT',
        'AUTHENTICATION',
        'User initiated logout.',
        $userId,
        [
            'user_id' => $userId,
            'user_name' => $userName,
            'ip_address' => $request->ip(),
        ]
    );

    $baseUrl = rtrim((string) config('services.idp.base_url'), '/');
    $clientId = (string) config('services.idp.client_id');

    // clear local CMS session first
    $request->session()->flush();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($baseUrl === '' || $clientId === '') {
        return redirect()->route('public.landing')
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');
    }

    return response()->view('auth.idp-logout', [
        'idpLogoutUrl' => $baseUrl . '/api/v1/auth/logout',
        'clientId' => $clientId,
        'afterLogoutUrl' => route('public.landing'),
    ])->withoutCookie('access_token')
      ->withoutCookie('refresh_token');
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
            $role === 'DIRECTOR_OFFICE'
        ) {
            return redirect('/staff/dashboard');
        }

        return redirect('/');
    }
}