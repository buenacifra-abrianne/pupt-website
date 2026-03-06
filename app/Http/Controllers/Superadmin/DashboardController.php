<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingApprovals = ApprovalRequest::where('status', 'pending')->count();

        $uptime = $this->getSystemUptime();

        $total_announcements = \DB::table('announcements')->count();

        $recent_announcements = \DB::table('announcements')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivities = collect();
        if (Schema::hasTable('activity_logs')) {
            $recentActivities = DB::table('activity_logs')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

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
            ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
            ->where(function ($scope) use ($userId) {
                $this->applyAdminAudienceScope($scope, $userId);
            })
            ->whereNull('nd.user_id')
            ->orderBy('n.created_at', 'desc')
            ->limit(6)
            ->get();

        return view('superadmin.dashboard', compact(
            'total_announcements',
            'recent_announcements',
            'recent_notifications',
            'pendingApprovals',
            'uptime',
            'recentActivities'
        ));
    }

    private function applyAdminAudienceScope(Builder $query, int $userId): void
    {
        $query->where(function ($adminBroadcast) {
            $adminBroadcast->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                ->whereNull('n.target_user_id');
        })->orWhere(function ($adminDirect) use ($userId) {
            $adminDirect->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                ->where('n.target_user_id', $userId);
        })->orWhere(function ($legacyBroadcast) {
            $legacyBroadcast->whereNull('n.target_role')
                ->whereNull('n.target_user_id');
        })->orWhere(function ($legacyDirect) use ($userId) {
            $legacyDirect->whereNull('n.target_role')
                ->where('n.target_user_id', $userId);
        });
    }

    private function getSystemUptime(): array
    {
        $seconds = $this->getUptimeSeconds();

        return [
            'seconds' => $seconds,
            'human' => $this->humanUptime($seconds),
            'percent' => '100%',
            'ok' => true,
        ];
    }

    private function getUptimeSeconds(): int
    {
        if (is_readable('/proc/uptime')) {
            $raw = trim((string) @file_get_contents('/proc/uptime'));
            if ($raw !== '') {
                $parts = preg_split('/\s+/', $raw);
                $sec = (int) floor((float) ($parts[0] ?? 0));
                if ($sec > 0) {
                    return $sec;
                }
            }
        }

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

        if ($days > 0) {
            return "{$days}d {$hours}h {$mins}m";
        }
        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins}m";
    }
}
