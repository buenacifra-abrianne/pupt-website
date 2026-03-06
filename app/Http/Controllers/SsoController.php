<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\AuditLog;

class SsoController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login: missing token.');
            abort(403, 'Missing SSO token.');
        }

        // TODO:
        // Replace this mock payload with real token verification later
        $portalUser = $this->resolvePortalUserFromToken($token);

        if (!$portalUser || empty($portalUser['email'])) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login: invalid or expired token.');
            abort(403, 'Invalid or expired SSO token.');
        }

        $user = DB::table('users')
            ->where('email', $portalUser['email'])
            ->first();

        if (!$user) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login: unknown user '.$portalUser['email']);
            abort(403, 'You do not have access to this system.');
        }

        if (!in_array((string) $user->status, ['Active', 'Suspended'], true)) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login for inactive account: '.$user->email);
            abort(403, 'Your account is inactive/suspended.');
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

        AuditLog::record(
            'LOGIN',
            'AUTHENTICATION',
            'Successful SSO login for '.$user->email,
            (int) ($user->user_id ?? $user->id ?? 0),
            [
                'user_id' => (int) ($user->user_id ?? $user->id ?? 0),
                'user_name' => trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? '')),
            ]
        );

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
