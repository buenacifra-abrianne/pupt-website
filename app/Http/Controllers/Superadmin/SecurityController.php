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

        $events = SecurityEvent::latest()->paginate(20);
        $blockedIps = BlockedIp::latest()->get();

        return view('superadmin.security.incidents', compact('events', 'blockedIps'));
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
