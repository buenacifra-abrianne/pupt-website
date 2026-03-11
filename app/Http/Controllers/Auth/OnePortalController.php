<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OnePortalController extends Controller
{
    public function redirectToIdp(Request $request)
    {
        if (session()->has('user_id')) {
            return $this->redirectByRole(session('role'));
        }

        return redirect()->away(rtrim(config('services.idp.base_url'), '/'));
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
            return redirect()->route('public.landing')
                ->with('error', 'Authorization code missing.');
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
            $errorBody = $tokenResponse->json();
            $errorMessage = is_array($errorBody) && isset($errorBody['error'])
                ? $errorBody['error']
                : 'Token exchange failed.';

            return redirect()->route('public.landing')
                ->with('error', $errorMessage);
        }

        $tokenData = $tokenResponse->json();

        $accessToken = $tokenData['access_token'] ?? null;
        $refreshToken = $tokenData['refresh_token'] ?? null;

        if (!$accessToken) {
            return redirect()->route('public.landing')
                ->with('error', 'Access token missing.');
        }

        $meResponse = Http::withToken($accessToken)->get(
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

        $userData = $meResponse->json();

        $id = $userData['id'] ?? null;
        $email = $userData['email'] ?? null;
        $firstName = $userData['first_name'] ?? '';
        $middleName = $userData['middle_name'] ?? '';
        $lastName = $userData['last_name'] ?? '';
        $roles = $userData['roles'] ?? [];

        if (!$id && !$email) {
            return redirect()->route('public.landing')
                ->with('error', 'User identity missing from IDP response.');
        }

        // Priority 1: oneportal_id match
        $user = null;

        if ($id) {
            $user = DB::table('users')
                ->where('oneportal_id', $id)
                ->first();
        }

        // Fallback: email match
        if (!$user && $email) {
            $user = DB::table('users')
                ->where('email', $email)
                ->first();
        }

        if (!$user) {
            return redirect()->route('public.landing')
                ->with('error', 'You do not have CMS access.');
        }

        if (isset($user->status) && strtoupper((string) $user->status) !== 'ACTIVE') {
            return redirect()->route('public.landing')
                ->with('error', 'Your CMS account is not active.');
        }

        // Optional sync of oneportal_id if local record was matched by email
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

        session([
            'user_id' => $user->user_id,
            'email' => $email ?? $user->email,
            'name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
            'role' => $user->role,
            'oneportal_id' => $id,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'idp_roles' => $roles,
        ]);

        return $this->redirectByRole($user->role);
    }

    public function logout(Request $request)
    {
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