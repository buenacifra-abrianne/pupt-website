<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
{
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
    ->whereNull('nd.user_id') // hide dismissed for this user
    ->orderBy('n.created_at', 'desc')
    ->limit(6)
    ->get();

    return view('faculty.dashboard', compact(
        'total_announcements',
        'recent_announcements',
        'recent_notifications',
        'recentActivities'
    ));
}
}