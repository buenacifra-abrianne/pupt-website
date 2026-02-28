<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $email = (string) session('user_email');

        $pending_requests = \DB::table('approval_requests')
            ->whereRaw("LOWER(status) = 'pending'")
            ->count();

        $approved_requests = \DB::table('approval_requests')
            ->whereRaw("LOWER(status) = 'approved'")
            ->count();

        $rejected_requests = \DB::table('approval_requests')
            ->whereRaw("LOWER(status) = 'rejected'")
            ->count();

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
        ->whereNull('nd.user_id') // hide dismissed for this user
        ->orderBy('n.created_at', 'desc')
        ->limit(6)
        ->get();

        return view('staff.dashboard', compact(
            'pending_requests',
            'approved_requests',
            'rejected_requests',
            'recentActivities',
            'recent_notifications'
        ));
    }
}