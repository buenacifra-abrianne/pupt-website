<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function page(Request $request)
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            abort(403, 'Missing user_id in session');
        }

        $q = $request->get('q', '');
        $typeFilter = $request->get('type', 'ALL');
        $statusFilter = $request->get('status', 'ALL');
        $rangeFilter = $request->get('range', '30D');

        // Stats
        $statsRow = DB::table('notifications as n')
            ->leftJoin('notification_reads as nr', function ($join) use ($userId) {
                $join->on('nr.notification_id', '=', 'n.notification_id')
                    ->where('nr.user_id', '=', $userId);
            })
            ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
                $join->on('nd.notification_id', '=', 'n.notification_id')
                    ->where('nd.user_id', '=', $userId);
            })
            ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
            ->whereRaw('UPPER(n.target_role) = ?', ['STAFF'])
            ->where('n.target_user_id', $userId)
            ->whereNull('nd.user_id')
            
            ->selectRaw("SUM(CASE WHEN nr.user_id IS NULL THEN 1 ELSE 0 END) AS unread_count")
            ->selectRaw("COUNT(*) AS total_count")
            ->first();

        $stats = [
            'unread' => (int)($statsRow->unread_count ?? 0),
            'total'  => (int)($statsRow->total_count ?? 0),
        ];

        // Base query
        $base = DB::table('notifications as n')
            ->leftJoin('notification_reads as nr', function ($join) use ($userId) {
                $join->on('nr.notification_id', '=', 'n.notification_id')
                    ->where('nr.user_id', '=', $userId);
            })
            ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
                $join->on('nd.notification_id', '=', 'n.notification_id')
                    ->where('nd.user_id', '=', $userId);
            })
            ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
            ->whereRaw('UPPER(n.target_role) = ?', ['STAFF'])
            ->where('n.target_user_id', $userId)
            ->whereNull('nd.user_id');

        // Type
        if ($typeFilter !== 'ALL' && $typeFilter !== '') {
            $base->whereRaw('UPPER(n.type) = ?', [$typeFilter]);
        }

        // Status
        if ($statusFilter === 'UNREAD') {
            $base->whereNull('nr.user_id');
        } elseif ($statusFilter === 'READ') {
            $base->whereNotNull('nr.user_id');
        }

        // Date range
        if ($rangeFilter === '7D') {
            $base->where('n.created_at', '>=', now()->subDays(7));
        } elseif ($rangeFilter === '30D') {
            $base->where('n.created_at', '>=', now()->subDays(30));
        } elseif ($rangeFilter === '3M') {
            $base->where('n.created_at', '>=', now()->subMonths(3));
        }

        // Search
        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('n.title', 'like', "%{$q}%")
                  ->orWhere('n.message', 'like', "%{$q}%");
            });
        }

        $totalFiltered = (clone $base)->count();

        // IMPORTANT: paginate() so $notifications->links() is valid
        $notifications = $base
            ->select([
                'n.notification_id',
                'n.title',
                'n.message',
                'n.type',
                'n.channel',
                'n.created_at',
                'nr.read_at',
            ])
            ->selectRaw("CASE WHEN nr.user_id IS NULL THEN 0 ELSE 1 END AS is_read")
            ->orderByDesc('n.created_at')
            ->paginate(10)
            ->appends($request->query());

        $iconMap = [
            'DANGER'  => ['danger', 'fa-exclamation-triangle'],
            'WARNING' => ['warning', 'fa-database'],
            'PRIMARY' => ['primary', 'fa-sync'],
            'INFO'    => ['info', 'fa-bullhorn'],
        ];

        return view('staff.notifications', compact(
            'q','typeFilter','statusFilter','rangeFilter',
            'notifications','stats','iconMap', 'totalFiltered'
        ));

    }

    public function markRead(Request $request) {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'error' => 'Missing user_id in session'], 400);
        }

        // ✅ supports both:
        // - mark one: {id: 1}
        // - mark all: {all: 1}
        if ($request->boolean('all')) {
            $ids = DB::table('notifications as n')
            ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
                $join->on('nd.notification_id', '=', 'n.notification_id')
                    ->where('nd.user_id', '=', $userId);
            })
            ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
            ->whereRaw('UPPER(n.target_role) = ?', ['STAFF'])
            ->where('n.target_user_id', $userId)
            ->whereNull('nd.user_id')
            ->pluck('n.notification_id');

            if ($ids->isNotEmpty()) {
                $rows = $ids->map(fn ($nid) => [
                    'notification_id' => $nid,
                    'user_id'        => $userId,
                    'read_at'         => now(),
                ])->all();

                DB::table('notification_reads')->upsert(
                    $rows,
                    ['notification_id', 'user_id'],
                    ['read_at']
                );
            }

            return response()->json(['ok' => true]);
        }

        $request->validate(['id' => ['required', 'integer']]);
        $id = (int) $request->id;

        DB::table('notification_reads')->updateOrInsert(
            ['notification_id' => $id, 'user_id' => $userId],
            ['read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function delete(Request $request) { $userId = (int) session('user_id');
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'error' => 'Missing user_id in session'], 400);
        }

        // ✅ supports both:
        // - delete one: {id: 1}
        // - clear all: {all: 1}
        if ($request->boolean('all')) {
            $ids = DB::table('notifications as n')
            ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
                $join->on('nd.notification_id', '=', 'n.notification_id')
                    ->where('nd.user_id', '=', $userId);
            })
            ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
            ->whereRaw('UPPER(n.target_role) = ?', ['STAFF'])
            ->where('n.target_user_id', $userId)
            ->whereNull('nd.user_id')
            ->pluck('n.notification_id');

            if ($ids->isNotEmpty()) {
                $rows = $ids->map(fn ($nid) => [
                    'notification_id' => $nid,
                    'user_id'        => $userId,
                    'dismissed_at'    => now(),
                ])->all();

                DB::table('notification_dismissed')->upsert(
                    $rows,
                    ['notification_id', 'user_id'],
                    ['dismissed_at']
                );
            }

            return response()->json(['ok' => true]);
        }

        $request->validate(['id' => ['required', 'integer']]);
        $id = (int) $request->id;

        DB::table('notification_dismissed')->updateOrInsert(
            ['notification_id' => $id, 'user_id' => $userId],
            ['dismissed_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    private function pushSystemNotif(string $type, string $title, string $message, ?string $targetRole = null, ?int $targetUserId = null): void
    {
        DB::table('notifications')->insert([
            'title'         => $title,
            'message'       => $message,
            'type'          => strtoupper($type),
            'channel'       => 'SYSTEM',
            'target_role'   => $targetRole,     // ADMIN / STAFF
            'target_user_id'=> $targetUserId,   // only for STAFF
            'created_at'    => now(),
        ]);
    }
}