<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $pending_requests = \DB::table('approval_requests')
            ->whereRaw("LOWER(status) = 'pending'")
            ->count();

        $approved_requests = \DB::table('approval_requests')
            ->whereRaw("LOWER(status) = 'approved'")
            ->count();

        $rejected_requests = \DB::table('approval_requests')
            ->whereRaw("LOWER(status) = 'rejected'")
            ->count();

        $recentActivities = collect();
        $userEmail = strtolower(trim((string) session('user_email')));
        $userDisplayName = trim((string) session('user_first_name', '').' '.(string) session('user_last_name', ''));
        if ($userDisplayName === '') {
            $userDisplayName = 'Staff';
        }

        if ($userEmail !== '') {
            $recentActivities = DB::table('approval_requests')
                ->select('type', 'title', 'status', 'updated_at', 'created_at')
                ->whereRaw('LOWER(requester_email) = ?', [$userEmail])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(function ($row) use ($userDisplayName) {
                    $type = strtoupper(trim((string) ($row->type ?? '')));
                    $status = strtoupper(trim((string) ($row->status ?? 'PENDING')));

                    return (object) [
                        'action' => $status !== '' ? $status : 'PENDING',
                        'module' => $this->moduleFromRequestType($type),
                        'description' => $this->activityDescription($type, (string) ($row->title ?? '')),
                        'user_name' => $userDisplayName,
                        'created_at' => $row->updated_at ?? $row->created_at ?? now(),
                    ];
                });
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
            ->where(function ($q) use ($userId) {
                $this->applyStaffAudienceScope($q, $userId);
            })
            ->whereNull('nd.user_id')
            ->orderBy('n.created_at', 'desc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'pending_requests',
            'approved_requests',
            'rejected_requests',
            'recentActivities',
            'recent_notifications'
        ));
    }

    private function applyStaffAudienceScope(Builder $query, int $userId): void
    {
        $query->where(function ($staff) use ($userId) {
            $staff->whereRaw('UPPER(n.target_role) = ?', ['STAFF'])
                ->where('n.target_user_id', $userId);
        })->orWhere(function ($legacy) use ($userId) {
            $legacy->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                ->where('n.target_user_id', $userId);
        })->orWhere(function ($direct) use ($userId) {
            $direct->whereNull('n.target_role')
                ->where('n.target_user_id', $userId);
        });
    }

    private function moduleFromRequestType(string $type): string
    {
        if (str_starts_with($type, 'ANNOUNCEMENT_')) {
            return 'ANNOUNCEMENTS';
        }

        if (str_starts_with($type, 'NEWS_')) {
            return 'NEWS';
        }

        if (str_starts_with($type, 'CMS_') && str_ends_with($type, '_EDIT')) {
            return 'CONTENT';
        }

        return 'REQUEST';
    }

    private function activityDescription(string $type, string $title): string
    {
        $title = trim($title);

        $label = match (true) {
            str_starts_with($type, 'ANNOUNCEMENT_CREATE') => 'Create announcement request',
            str_starts_with($type, 'ANNOUNCEMENT_UPDATE') => 'Edit announcement request',
            str_starts_with($type, 'ANNOUNCEMENT_DELETE') => 'Delete announcement request',
            str_starts_with($type, 'ANNOUNCEMENT_ENABLE') => 'Enable announcement request',
            str_starts_with($type, 'ANNOUNCEMENT_DISABLE') => 'Disable announcement request',
            str_starts_with($type, 'NEWS_CREATE') => 'Create news request',
            str_starts_with($type, 'NEWS_UPDATE') => 'Edit news request',
            str_starts_with($type, 'NEWS_DELETE') => 'Delete news request',
            str_starts_with($type, 'CMS_') && str_ends_with($type, '_EDIT') => 'Content edit request',
            default => 'Request update',
        };

        if ($title === '') {
            return $label.'.';
        }

        return $label.' "'.$title.'".';
    }
}
