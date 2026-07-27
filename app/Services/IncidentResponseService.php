<?php

namespace App\Services;

use App\Models\BlockedIp;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Models\Admin;
use App\Support\AuditLog;
use App\Notifications\SecurityAnomalyNotification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Notification;

class IncidentResponseService
{
    /**
     * Threshold for anomalous behavior before containment triggers.
     */
    const MAX_ATTEMPTS = 3;

    /**
     * Throttle duration in seconds (15 minutes).
     */
    const DECAY_SECONDS = 900;

    /**
     * Record a failed login or unauthorized access attempt.
     */
    public static function recordFailure(string $ip, ?string $email = null, string $eventType = 'FAILED_LOGIN', string $description = '')
    {
        // 1. Log the event
        SecurityEvent::create([
            'ip_address' => $ip,
            'user_email' => $email,
            'event_type' => $eventType,
            'description' => $description,
        ]);

        $key = 'security_failures:' . $ip;

        // 2. Increment rate limiter
        RateLimiter::hit($key, self::DECAY_SECONDS);

        // 3. Check threshold
        if (RateLimiter::attempts($key) >= self::MAX_ATTEMPTS) {
            self::initiateContainment($ip, $email, $eventType, RateLimiter::attempts($key));
            
            // Prevent duplicate notifications while contained by clearing the attempts, 
            // but the IP is already in BlockedIp table so FirewallMiddleware will catch it.
            RateLimiter::clear($key); 
        }
    }

    /**
     * Execute automated temporary containment measures.
     */
    public static function initiateContainment(string $ip, ?string $email, string $eventType, int $failureCount)
    {
        // 1. Temporary IP Blacklist (15 minutes according to standard)
        BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'blocked_until' => now()->addMinutes(15),
                'reason' => "Automated containment: $failureCount failed attempts ($eventType).",
            ]
        );

        // 2. Audit Log
        AuditLog::record('SECURITY', 'CONTAINMENT', "IP $ip has been temporarily blocked for 15 minutes due to $failureCount failed attempts.");

        // 3. Session Invalidation
        // Invalidate "remember me" sessions
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->save();
            }
            $admin = Admin::where('email', $email)->first();
            if ($admin) {
                $admin->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->save();
            }
        }

        // 4. Notify Superadmins
        self::notifySuperadmins($ip, $email, $eventType, $failureCount);
    }

    public static function notifySuperadmins(string $ip, ?string $email, string $eventType, int $failureCount)
    {
        $message = "Detected $failureCount failed attempts from IP $ip. Temporary containment applied. Target: " . ($email ?? 'Unknown');
        
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'title' => 'Security Anomaly Detected',
            'message' => $message,
            'type' => 'DANGER', // Red icon in the CMS
            'channel' => 'SYSTEM',
            'target_role' => 'SUPERADMIN', // Broadcast to all Superadmins
            'target_user_id' => null,
            'created_at' => now(),
        ]);
    }
}
