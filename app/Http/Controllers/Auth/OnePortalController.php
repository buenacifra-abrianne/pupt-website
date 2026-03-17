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
        if (session()->has('user_id')) {
            return $this->redirectByRole(session('role'));
        }

        $clientId = config('services.idp.client_id');
        // IDP Authorize Endpoint
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
    $roles = $userData['roles'] ?? [];

    if (!$email) {
        return redirect()->route('public.landing')
            ->with('error', 'Email not found from IDP.');
    }

    // get role from IDP if available, otherwise fallback to FACULTY
    $allowedRoles = [
        'SUPERADMIN',
        'ADMIN',
        'REGISTRAR',
        'HAP',
        'STUDENT_SERVICES',
        'RESEARCH_EXTENSION',
        'FACULTY',
    ];

    $idpRoles = array_map('strtoupper', $roles);

    $idpRole = null;
    foreach ($idpRoles as $role) {
        // if roles are namespaced like PUPTWEB:REGISTRAR
        if (str_starts_with($role, 'PUPTWEB:')) {
            $role = str_replace('PUPTWEB:', '', $role);
        }

        if (in_array($role, $allowedRoles, true)) {
            $idpRole = $role;
            break;
        }
    }

    // fallback role if none returned by IDP
    $finalRole = $idpRole ?: 'FACULTY';

    // check local user by email
    $user = DB::table('users')
        ->where('email', $email)
        ->first();

    // if user does not exist locally, auto-create local user
    if (!$user) {
        DB::table('users')->insert([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
            'email' => $email,
            'role' => strtolower($finalRole),
            'status' => 'Active',
            'oneportal_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = DB::table('users')
            ->where('email', $email)
            ->first();
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

    // create local session
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
        'user_roles' => $idpRoles ?: [$finalRole],
        'oneportal_id' => $id,
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'idp_roles' => $idpRoles,
    ]);

    // record login in audit trail
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

    return $this->redirectByRole($finalRole);
}

    public function logout(Request $request)
    {
        AuditLog::record(
            'LOGOUT',
            'AUTHENTICATION',
            'User logged out.',
            (int) session('user_id', 0),
            [
                'user_id' => (int) session('user_id', 0),
                'user_name' => (string) session('user_name', 'Unknown'),
                'ip_address' => $request->ip(),
            ]
        );

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.landing');
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
            $role === 'FACULTY'
        ) {
            return redirect('/staff/dashboard');
        }

        return redirect('/');
    }
}