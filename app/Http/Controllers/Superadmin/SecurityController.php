<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlockedIp;
use App\Models\SecurityEvent;
use App\Support\AccountStatusGuard;
use App\Support\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecurityController extends Controller
{
    public function incidents()
    {
        // Prune expired temporary blocks first
        BlockedIp::where('blacklisted', false)
            ->whereNotNull('blocked_until')
            ->where('blocked_until', '<', now())
            ->delete();

        $events = SecurityEvent::latest()->paginate(10);
        $blockedIps = BlockedIp::latest()->get();
        
        $stats = [
            'login' => SecurityEvent::whereIn('event_type', ['LOGIN_FAILURE', 'BRUTE_FORCE', 'FAILED_LOGIN', 'FAILED_SSO_LOGIN'])->count(),
            'unauth' => SecurityEvent::whereIn('event_type', ['UNAUTHORIZED_ACCESS', 'UNKNOWN_USER', 'INACTIVE_ACCOUNT', 'INVALID_ROLE'])->count(),
            'firewall' => BlockedIp::count(),
            'malicious' => SecurityEvent::where('event_type', 'MALICIOUS_PAYLOAD')->count(),
            'sql_xss' => SecurityEvent::whereIn('event_type', ['SQL_INJECTION', 'XSS_ATTEMPT'])->count(),
            'policy' => SecurityEvent::where('event_type', 'POLICY_VIOLATION')->count(),
        ];

        // Ensure minimum scale of 10 to avoid full size on just 1 event
        $max = max(10, max($stats));

        // Calculate radar chart coordinates (r = 80 max)
        $radar = [];
        $angles = [
            ['x' => 0, 'y' => -1],         // Top (Login)
            ['x' => 0.866, 'y' => -0.5],   // Top-Right (Unauth)
            ['x' => 0.866, 'y' => 0.5],    // Bottom-Right (Firewall)
            ['x' => 0, 'y' => 1],          // Bottom (Malicious)
            ['x' => -0.866, 'y' => 0.5],   // Bottom-Left (SQL/XSS)
            ['x' => -0.866, 'y' => -0.5],  // Top-Left (Policy)
        ];

        $statValues = array_values($stats);
        foreach ($statValues as $index => $val) {
            $r = 80 * ($val / $max);
            $r = max(2, $r); // Minimum radius so points don't stack perfectly at exactly 100,100
            $radar[] = [
                'x' => 100 + ($angles[$index]['x'] * $r),
                'y' => 100 + ($angles[$index]['y'] * $r)
            ];
        }

        $radarPoints = implode(' ', array_map(function($p) { return "{$p['x']},{$p['y']}"; }, $radar));

        return view('superadmin.security.incidents', compact('events', 'blockedIps', 'radar', 'radarPoints'));
    }

    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        BlockedIp::updateOrCreate(
            ['ip_address' => $request->ip_address],
            [
                'blacklisted' => true,
                'blocked_until' => null,
                'reason' => $request->reason ?? 'Manual blacklist by Superadmin',
            ]
        );

        AuditLog::record('SECURITY', 'FIREWALL', "IP {$request->ip_address} has been permanently blacklisted by Superadmin.");

        return back()->with('success', 'IP address permanently blacklisted.');
    }

    public function unblockIp(BlockedIp $blockedIp)
    {
        $ip = $blockedIp->ip_address;
        $blockedIp->delete();

        AuditLog::record('SECURITY', 'FIREWALL', "IP {$ip} has been unblocked by Superadmin.");

        return back()->with('success', 'IP address unblocked.');
    }

    public function suspendAccount(Request $request, int $user)
    {
        return $this->changeAccountStatus($user, 'Suspended');
    }

    public function unsuspendAccount(Request $request, int $user)
    {
        return $this->changeAccountStatus($user, 'Active');
    }

    private function changeAccountStatus(int $userId, string $status)
    {
        if ($response = AccountStatusGuard::denySelfDeactivation($userId, $status)) {
            return $response;
        }

        $key = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $user = DB::table('users')->where($key, $userId)->first();
        abort_unless($user, 404);

        $updates = ['status' => $status];
        if (Schema::hasColumn('users', 'updated_at')) {
            $updates['updated_at'] = now();
        }
        DB::table('users')->where($key, $userId)->update($updates);

        $action = $status === 'Suspended' ? 'suspended' : 'restored (unsuspended)';
        AuditLog::record('SECURITY', 'ACCOUNT', "User account {$user->email} {$action} by Superadmin.", $userId);

        return back()->with('success', $status === 'Suspended'
            ? 'User account suspended successfully.'
            : 'User account restored successfully.');
    }
}
