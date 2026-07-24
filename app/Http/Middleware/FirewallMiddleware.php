<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BlockedIp;
use App\Support\AuditLog;

class FirewallMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $blockedIp = BlockedIp::where('ip_address', $ip)->first();

        if ($blockedIp) {
            if ($blockedIp->blacklisted) {
                AuditLog::record('SECURITY', 'FIREWALL', "Blocked request from permanently blacklisted IP: {$ip}");
                abort(403, 'Your IP address has been permanently blacklisted.');
            }

            if ($blockedIp->blocked_until && $blockedIp->blocked_until->isFuture()) {
                AuditLog::record('SECURITY', 'FIREWALL', "Blocked request from temporarily contained IP: {$ip}");
                abort(403, 'Your IP address is temporarily blocked due to suspicious activity. Please try again later.');
            }
            
            // If the temporary block has expired, we can optionally delete it
            if ($blockedIp->blocked_until && $blockedIp->blocked_until->isPast() && !$blockedIp->blacklisted) {
                $blockedIp->delete();
            }
        }

        return $next($request);
    }
}
