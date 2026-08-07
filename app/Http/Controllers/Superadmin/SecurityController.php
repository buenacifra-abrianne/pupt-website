<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlockedIp;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Support\AuditLog;

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
            'login' => SecurityEvent::whereIn('event_type', ['LOGIN_FAILURE', 'BRUTE_FORCE'])->count(),
            'unauth' => SecurityEvent::where('event_type', 'UNAUTHORIZED_ACCESS')->count(),
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

    public function suspendAccount(Request $request, User $user)
    {
        $user->update(['status' => 'Suspended']);

        AuditLog::record('SECURITY', 'ACCOUNT', "User account {$user->email} suspended by Superadmin.");

        return back()->with('success', 'User account suspended successfully.');
    }

    public function unsuspendAccount(Request $request, User $user)
    {
        $user->update(['status' => 'Active']);

        AuditLog::record('SECURITY', 'ACCOUNT', "User account {$user->email} restored (unsuspended) by Superadmin.");

        return back()->with('success', 'User account restored successfully.');
    }
}
