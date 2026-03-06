<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SsoController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(403, 'Missing SSO token.');
        }

        // TODO:
        // Replace this mock payload with real token verification later
        $portalUser = $this->resolvePortalUserFromToken($token);

        if (!$portalUser || empty($portalUser['email'])) {
            abort(403, 'Invalid or expired SSO token.');
        }

        $user = DB::table('users')
            ->where('email', $portalUser['email'])
            ->first();

        if (!$user) {
            abort(403, 'You do not have access to this system.');
        }

        if (!in_array((string) $user->status, ['Active', 'Pending'], true)) {
            abort(403, 'Your account is not active.');
        }

        session([
            'user_id' => $user->user_id ?? $user->id,
            'user_first_name' => $user->first_name,
            'user_role' => $user->role,
            'user_email' => $user->email,
            'sso_logged_in' => true,
        ]);

        DB::table('users')
            ->where(isset($user->user_id) ? 'user_id' : 'id', $user->user_id ?? $user->id)
            ->update([
                'last_login_at' => now(),
            ]);

        return $this->redirectByRole((string) $user->role);
    }

    private function resolvePortalUserFromToken(string $token): ?array
    {
        /**
         * TEMPORARY MOCK ONLY
         * Replace this once your classmates provide:
         * - token type
         * - verification method
         * - payload structure
         */

        return [
            'email' => null,
            'name'  => null,
            'role'  => null,
        ];
    }

    private function redirectByRole(string $role)
    {
        $role = strtoupper(trim($role));

        return match ($role) {
            'GLOBAL_SUPERADMIN', 'SYSTEM_SUPERADMIN' => redirect()->route('superadmin.dashboard'),
            'REGISTRAR' => redirect()->route('admin.dashboard'),
            'HAP' => redirect()->route('admin.dashboard'),
            'STUDENT_SERVICES' => redirect()->route('admin.dashboard'),
            'RESEARCH_EXTENSION' => redirect()->route('admin.dashboard'),
            'FACULTY', 'PUPT:FACULTY', 'pupt:faculty' => redirect()->route('admin.dashboard'),
            default => redirect('/'),
        };
    }
}