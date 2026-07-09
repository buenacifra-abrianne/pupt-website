<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
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

        $typeFilter = strtoupper(trim($request->query('type', 'ALL')));
        $statusFilter = strtoupper(trim($request->query('status', 'ALL')));
        $rangeFilter = strtoupper(trim($request->query('range', '7D')));
        $q = trim((string) $request->query('q', ''));

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
            ->where(function ($scope) use ($userId) {
                $this->applyAdminAudienceScope($scope, $userId);
            })
            ->whereNull('nd.user_id')
            ->selectRaw('SUM(CASE WHEN nr.user_id IS NULL THEN 1 ELSE 0 END) AS unread_count')
            ->selectRaw('COUNT(*) AS total_count')
            ->first();

        $stats = [
            'unread' => (int) ($statsRow->unread_count ?? 0),
            'total' => (int) ($statsRow->total_count ?? 0),
        ];

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
            ->where(function ($scope) use ($userId) {
                $this->applyAdminAudienceScope($scope, $userId);
            })
            ->whereNull('nd.user_id');

        if ($typeFilter !== 'ALL' && $typeFilter !== '') {
            $base->whereRaw('UPPER(n.type) = ?', [$typeFilter]);
        }

        if ($statusFilter === 'UNREAD') {
            $base->whereNull('nr.user_id');
        } elseif ($statusFilter === 'READ') {
            $base->whereNotNull('nr.user_id');
        }

        if ($rangeFilter === '7D') {
            $base->where('n.created_at', '>=', now()->subDays(7));
        } elseif ($rangeFilter === '30D') {
            $base->where('n.created_at', '>=', now()->subDays(30));
        } elseif ($rangeFilter === '3M') {
            $base->where('n.created_at', '>=', now()->subMonths(3));
        }

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('n.title', 'like', "%{$q}%")
                    ->orWhere('n.message', 'like', "%{$q}%");
            });
        }

        $totalFiltered = (clone $base)->count();

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
            ->selectRaw('CASE WHEN nr.user_id IS NULL THEN 0 ELSE 1 END AS is_read')
            ->orderByDesc('n.created_at')
            ->paginate(10)
            ->appends($request->query());

        $iconMap = [
            'DANGER' => ['danger', 'fa-exclamation-triangle'],
            'WARNING' => ['warning', 'fa-database'],
            'PRIMARY' => ['primary', 'fa-sync'],
            'INFO' => ['info', 'fa-bullhorn'],
        ];

        return view('admin.notifications', compact(
            'stats',
            'totalFiltered',
            'notifications',
            'typeFilter',
            'statusFilter',
            'rangeFilter',
            'q',
            'iconMap'
        ));
    }

    public function markRead(Request $request)
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'error' => 'Missing user_id in session'], 400);
        }

        if ($request->boolean('all')) {
            $ids = DB::table('notifications as n')
                ->leftJoin('notification_reads as nr', function ($join) use ($userId) {
                    $join->on('nr.notification_id', '=', 'n.notification_id')
                        ->where('nr.user_id', '=', $userId);
                })
                ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
                    $join->on('nd.notification_id', '=', 'n.notification_id')
                        ->where('nd.user_id', '=', $userId);
                })
                ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
                ->where(function ($scope) use ($userId) {
                    $this->applyAdminAudienceScope($scope, $userId);
                })
                ->whereNull('nd.user_id')
                ->whereNull('nr.user_id')
                ->pluck('n.notification_id');

            if ($ids->isEmpty()) {
                return response()->json([
                    'ok' => true,
                    'changed' => false,
                    'message' => 'No unread notifications found.',
                ]);
            }

            $rows = $ids->map(fn ($nid) => [
                'notification_id' => $nid,
                'user_id' => $userId,
                'read_at' => now(),
            ])->all();

            DB::table('notification_reads')->upsert(
                $rows,
                ['notification_id', 'user_id'],
                ['read_at']
            );

            return response()->json([
                'ok' => true,
                'changed' => true,
                'count' => count($rows),
                'message' => 'All notifications marked as read.',
            ]);
        }

        $request->validate(['id' => ['required', 'integer']]);
        $id = (int) $request->id;

        $canAccess = DB::table('notifications as n')
            ->where('n.notification_id', $id)
            ->where(function ($scope) use ($userId) {
                $this->applyAdminAudienceScope($scope, $userId);
            })
            ->exists();

        if (!$canAccess) {
            return response()->json(['ok' => false, 'error' => 'Unauthorized or notification not found'], 403);
        }

        $alreadyRead = DB::table('notification_reads')
            ->where('notification_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyRead) {
            return response()->json([
                'ok' => true,
                'changed' => false,
                'message' => 'Notification is already marked as read.',
            ]);
        }

        DB::table('notification_reads')->updateOrInsert(
            ['notification_id' => $id, 'user_id' => $userId],
            ['read_at' => now()]
        );

        return response()->json([
            'ok' => true,
            'changed' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    public function delete(Request $request)
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'error' => 'Missing user_id in session'], 400);
        }

        if ($request->boolean('all')) {
            $ids = DB::table('notifications as n')
                ->leftJoin('notification_dismissed as nd', function ($join) use ($userId) {
                    $join->on('nd.notification_id', '=', 'n.notification_id')
                        ->where('nd.user_id', '=', $userId);
                })
                ->whereRaw('UPPER(n.channel) = ?', ['SYSTEM'])
                ->where(function ($scope) use ($userId) {
                    $this->applyAdminAudienceScope($scope, $userId);
                })
                ->whereNull('nd.user_id')
                ->pluck('n.notification_id');

            if ($ids->isEmpty()) {
                return response()->json([
                    'ok' => true,
                    'changed' => false,
                    'message' => 'No notifications to clear.',
                ]);
            }

            $rows = $ids->map(fn ($nid) => [
                'notification_id' => $nid,
                'user_id' => $userId,
                'dismissed_at' => now(),
            ])->all();

            DB::table('notification_dismissed')->upsert(
                $rows,
                ['notification_id', 'user_id'],
                ['dismissed_at']
            );

            return response()->json([
                'ok' => true,
                'changed' => true,
                'count' => count($rows),
                'message' => 'All notifications cleared successfully.',
            ]);
        }

        $request->validate(['id' => ['required', 'integer']]);
        $id = (int) $request->id;

        $canAccess = DB::table('notifications as n')
            ->where('n.notification_id', $id)
            ->where(function ($scope) use ($userId) {
                $this->applyAdminAudienceScope($scope, $userId);
            })
            ->exists();

        if (!$canAccess) {
            return response()->json(['ok' => false, 'error' => 'Unauthorized or notification not found'], 403);
        }

        $alreadyDismissed = DB::table('notification_dismissed')
            ->where('notification_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyDismissed) {
            return response()->json([
                'ok' => true,
                'changed' => false,
                'message' => 'Notification is already cleared.',
            ]);
        }

        DB::table('notification_dismissed')->updateOrInsert(
            ['notification_id' => $id, 'user_id' => $userId],
            ['dismissed_at' => now()]
        );

        return response()->json([
            'ok' => true,
            'changed' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }

    private function pushSystemNotif(
        string $type,
        string $title,
        string $message,
        ?string $targetRole,
        ?int $targetUserId = null
    ): void {
        \DB::table('notifications')->insert([
            'title' => $title,
            'message' => $message,
            'type' => strtoupper($type),
            'channel' => 'SYSTEM',
            'target_role' => $targetRole,
            'target_user_id' => $targetUserId,
            'created_at' => now(),
        ]);
    }

    private function applyAdminAudienceScope(Builder $query, int $userId): void
    {
        $currentRole = strtoupper(trim((string) session('user_role')));

        $query->where(function ($q) use ($userId, $currentRole) {
            // Strictly bound to the user directly or via historical interaction
            $q->where('n.target_user_id', $userId)
              ->orWhereExists(function ($sub) use ($userId) {
                  $sub->select(DB::raw(1))
                      ->from('notification_reads as nr2')
                      ->whereColumn('nr2.notification_id', 'n.notification_id')
                      ->where('nr2.user_id', $userId);
              })
              ->orWhereExists(function ($sub) use ($userId) {
                  $sub->select(DB::raw(1))
                      ->from('notification_dismissed as nd2')
                      ->whereColumn('nd2.notification_id', 'n.notification_id')
                      ->where('nd2.user_id', $userId);
              });

            // Or it's a broadcast to their current role
            $q->orWhere(function ($broadcast) use ($currentRole) {
                $broadcast->whereNull('n.target_user_id');
                if ($currentRole === 'SUPERADMIN') {
                    $broadcast->whereIn(DB::raw('UPPER(n.target_role)'), ['SUPERADMIN', 'ADMIN']);
                } elseif ($currentRole === 'ADMIN') {
                    $broadcast->whereRaw('UPPER(n.target_role) = ?', ['ADMIN']);
                } elseif ($currentRole === 'STAFF') {
                    $broadcast->whereRaw('UPPER(n.target_role) = ?', ['STAFF']);
                }
                $broadcast->orWhereNull('n.target_role'); // Legacy broadcasts
            });
        });

        // Conditional Notification Rendering: Hide sensitive historical notifications if demoted
        if ($currentRole === 'STAFF') {
            $query->where(function ($filter) {
                $filter->whereNotIn(DB::raw('UPPER(n.target_role)'), ['SUPERADMIN', 'ADMIN'])
                       ->orWhereNull('n.target_role');
            });
        } elseif ($currentRole === 'ADMIN') {
            $query->where(function ($filter) {
                $filter->whereRaw('UPPER(n.target_role) != ?', ['SUPERADMIN'])
                       ->orWhereNull('n.target_role');
            });
        }
    }
}
