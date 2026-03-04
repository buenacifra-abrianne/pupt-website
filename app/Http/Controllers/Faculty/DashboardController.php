<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
{
    $pendingApprovals = ApprovalRequest::where('status', 'pending')->count();

    $uptime = $this->getSystemUptime(); // returns ['seconds'=>..,'human'=>..,'percent'=>..,'ok'=>..]

    // Total announcements
    $total_announcements = \DB::table('announcements')->count();

    // Recent announcements
    $recent_announcements = \DB::table('announcements')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    // No activity_logs table in your DB → set empty for now
    $recentActivities = DB::table('activity_logs')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

    // Recent notifications (latest 5)
    $userId = (int) session('user_id');

    $recent_notifications = DB::table('notifications as n')
    ->leftJoin('notification_reads as nr', function ($join) use ($userId) {
        $join->on('nr.notification_id', '=', 'n.notification_id')
            ->where('nr.user_id', '=', $userId);
    })
    ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
        $join->on('nd.notification_id', '=', 'n.notification_id')
            ->where('nd.user_id', '=', $userId);
    })
    ->select(
        'n.notification_id',
        'n.channel',
        'n.title',
        'n.message',
        'n.type',
        'n.created_at',
        DB::raw('CASE WHEN nr.user_id IS NULL THEN 0 ELSE 1 END AS is_read'),
        'nr.read_at'
    )
    ->where('n.channel', 'SYSTEM')
    ->where('n.target_role', 'ADMIN')
    ->whereNull('nd.user_id') // hide dismissed for this user
    ->orderBy('n.created_at', 'desc')
    ->limit(6)
    ->get();

    return view('faculty.dashboard', compact(
        'total_announcements',
        'recent_announcements',
        'recent_notifications',
        'pendingApprovals',
        'uptime',
        'recentActivities'
    ));
}

private function getSystemUptime(): array
{
    $seconds = $this->getUptimeSeconds();

    return [
        'seconds' => $seconds,
        'human'   => $this->humanUptime($seconds),
        // If you don’t have downtime tracking, assume 100% within the observed period.
        // (You can replace this later with real monitoring data.)
        'percent' => '100%',
        'ok'      => true,
    ];
}

private function getUptimeSeconds(): int
{
    // ✅ Best on Linux servers
    if (is_readable('/proc/uptime')) {
        $raw = trim((string) @file_get_contents('/proc/uptime'));
        if ($raw !== '') {
            $parts = preg_split('/\s+/', $raw);
            $sec = (int) floor((float) ($parts[0] ?? 0));
            if ($sec > 0) return $sec;
        }
    }

    // ✅ Fallback (works anywhere): app uptime since first request after cache clear
    $boot = Cache::rememberForever('app_boot_at', fn () => now()->toDateTimeString());
    try {
        return now()->diffInSeconds(\Carbon\Carbon::parse($boot));
    } catch (\Throwable $e) {
        return 0;
    }
}

private function humanUptime(int $seconds): string
{
    $seconds = max(0, $seconds);

    $days = intdiv($seconds, 86400);
    $seconds %= 86400;

    $hours = intdiv($seconds, 3600);
    $seconds %= 3600;

    $mins = intdiv($seconds, 60);

    if ($days > 0) return "{$days}d {$hours}h {$mins}m";
    if ($hours > 0) return "{$hours}h {$mins}m";
    return "{$mins}m";
}
}