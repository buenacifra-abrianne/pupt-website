<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\AuditLog;
use App\Support\CmsSections;

class SsoController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login: missing token.');
            abort(403, 'Missing SSO token.');
        }

        $portalUser = $this->resolvePortalUserFromToken($token);

        if (!$portalUser || empty($portalUser['email'])) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login: invalid or expired token.');
            abort(403, 'Invalid or expired SSO token.');
        }

        $user = DB::table('users')
            ->where('email', $portalUser['email'])
            ->first();

        if (!$user) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login: unknown user '.$portalUser['email']);

            return redirect()->route('public.landing')
                ->with('no_role_error', 'You have no role in this system. Please check with the superadmin.');
        }

        if (!in_array((string) $user->status, ['Active', 'Suspended'], true)) {
            AuditLog::record('SECURITY', 'SECURITY', 'Blocked SSO login for inactive account: '.$user->email);
            abort(403, 'Your account is inactive/suspended.');
        }

        $finalRole = strtoupper(trim((string) ($user->role ?? '')));

        $allowedRoles = [
            'GLOBAL_SUPERADMIN',
            'SYSTEM_SUPERADMIN',
            'SUPERADMIN',
            'ADMIN',
            'REGISTRAR',
            'HAP',
            'STUDENT_SERVICES',
            'RESEARCH_EXTENSION',
            'FACULTY',
        ];

        if ($finalRole === '' || !in_array($finalRole, $allowedRoles, true)) {
            $request->session()->forget([
                'user_logged_in',
                'user_id',
                'user_first_name',
                'user_role',
                'user_roles',
                'user_email',
                'sso_logged_in',
                'terms_accepted',
            ]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditLog::record(
                'SECURITY',
                'SECURITY',
                'Blocked SSO login: no valid role for '.$user->email
            );

            return redirect()->route('public.landing')
                ->with('no_role_error', 'You have no role in this system. Please check with the superadmin.');
        }

        session([
            'user_logged_in' => true,
            'user_id' => $user->user_id ?? $user->id,
            'user_first_name' => $user->first_name,
            'user_role' => $finalRole,
            'user_roles' => [$finalRole],
            'user_email' => $user->email,
            'sso_logged_in' => true,
            'terms_accepted' => false,
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

        return $this->redirectByRole($finalRole);
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
        $role = CmsSections::normalizeRole($role);

        if (in_array($role, ['SUPERADMIN'], true)) {
            return redirect()->route('superadmin.dashboard');
        }

        if (!empty(CmsSections::tabsForRole($role))) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('public.landing')
            ->with('no_role_error', 'You have no role in this system. Please check with the superadmin.');
    }
}
