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

        return view('auth.callback', [
            'code' => $code
        ]);
    }

    public function process(Request $request)
{
    $code = $request->input('code');

    if (!$code) {
        dd('FAILED: Authorization code missing.');
    }

    $tokenResponse = Http::withoutVerifying()->asJson()->post(
        rtrim(config('services.idp.base_url'), '/') . '/api/v1/auth/token',
        [
            'client_id' => config('services.idp.client_id'),
            'client_secret' => config('services.idp.client_secret'),
            'code' => $code,
        ]
    );

    if (!$tokenResponse->successful()) {
        dd([
            'FAILED' => 'token exchange',
            'status' => $tokenResponse->status(),
            'body' => $tokenResponse->json(),
            'raw' => $tokenResponse->body(),
        ]);
    }

    $tokenData = $tokenResponse->json();

    $accessToken = $tokenData['access_token'] ?? null;
    $refreshToken = $tokenData['refresh_token'] ?? null;

    if (!$accessToken) {
        dd([
            'FAILED' => 'access token missing',
            'tokenData' => $tokenData,
        ]);
    }

    $meResponse = Http::withoutVerifying()->withToken($accessToken)->get(
        rtrim(config('services.idp.base_url'), '/') . '/api/v1/me'
    );

    if (!$meResponse->successful()) {
        dd([
            'FAILED' => '/me request',
            'status' => $meResponse->status(),
            'body' => $meResponse->json(),
            'raw' => $meResponse->body(),
        ]);
    }

    $userData = $meResponse->json();

    $id = $userData['id'] ?? null;
    $email = $userData['email'] ?? null;
    $firstName = $userData['first_name'] ?? '';
    $middleName = $userData['middle_name'] ?? '';
    $lastName = $userData['last_name'] ?? '';
    $roles = $userData['roles'] ?? [];

    if (!$email) {
        dd([
            'FAILED' => 'email missing from /me',
            'userData' => $userData,
        ]);
    }

    $user = DB::table('users')
        ->where('email', $email)
        ->first();

    dd([
        'SUCCESS_SO_FAR' => true,
        'email_used_for_lookup' => $email,
        'matched_local_user' => $user,
        'user_status' => $user->status ?? null,
        'user_role' => $user->role ?? null,
        'idp_user' => $userData,
    ]);

    if (!$user) {
        return redirect()->route('public.landing')
            ->with('error', 'You do not have CMS access.');
    }

    if (isset($user->status) && strtoupper((string) $user->status) !== 'ACTIVE') {
        return redirect()->route('public.landing')
            ->with('error', 'Your CMS account is not active.');
    }

    session([
        'user_logged_in' => true,
        'user_id' => $user->user_id,
        'user_email' => $email ?? $user->email ?? '',
        'user_first_name' => $firstName,
        'user_middle_name' => $middleName,
        'user_last_name' => $lastName,
        'user_role' => strtoupper($user->role),
        'user_roles' => [strtoupper($user->role)],
        'user_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
        'oneportal_id' => $id,
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'idp_roles' => $roles,
    ]);

    return $this->redirectByRole($user->role);
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