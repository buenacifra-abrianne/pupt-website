<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::withoutDoubleEncoding();

        View::composer(['admin.*', 'superadmin.*', 'components.app.sidebar'], function ($view) {
            $pendingApprovalCount = 0;

            if (Schema::hasTable('approval_requests')) {
                $pendingApprovalCount = (int) DB::table('approval_requests')
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])
                    ->count();
            }

            $view->with('pendingApprovalCount', $pendingApprovalCount);
        });

        View::composer(['admin.*', 'superadmin.*', 'staff.*', 'components.app.sidebar'], function ($view) {
            $unreadNotificationCount = 0;
            $userId = (int) session('user_id');
            $role = strtoupper((string) session('role', ''));

            if ($userId > 0 && Schema::hasTable('notifications')) {
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
                    ->whereNull('nd.user_id')
                    ->whereNull('nr.user_id');

                $currentRole = strtoupper(trim((string) (session('user_role') ?? session('role', ''))));

                $userCreatedAt = session('user_created_at');
                if (!$userCreatedAt) {
                    $userCreatedAt = DB::table('users')->where('user_id', $userId)->value('created_at') ?? '2000-01-01 00:00:00';
                    session(['user_created_at' => $userCreatedAt]);
                }

                $base->where(function ($q) use ($userId, $currentRole, $userCreatedAt) {
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

                    $q->orWhere(function ($broadcast) use ($currentRole, $userCreatedAt) {
                        $broadcast->whereNull('n.target_user_id')
                                  ->where('n.created_at', '>=', $userCreatedAt)
                                  ->where(function ($roleCheck) use ($currentRole) {
                                      if ($currentRole === 'SUPERADMIN') {
                                          $roleCheck->whereIn(DB::raw('UPPER(n.target_role)'), ['SUPERADMIN', 'ADMIN']);
                                      } elseif ($currentRole === 'ADMIN') {
                                          $roleCheck->whereRaw('UPPER(n.target_role) = ?', ['ADMIN']);
                                      } else {
                                          $roleCheck->whereRaw('UPPER(n.target_role) = ?', ['STAFF']);
                                      }
                                      $roleCheck->orWhereNull('n.target_role');
                                  });
                    });
                });

                if (!in_array($currentRole, ['SUPERADMIN', 'ADMIN'])) {
                    $base->where(function ($filter) {
                        $filter->whereNotIn(DB::raw('UPPER(n.target_role)'), ['SUPERADMIN', 'ADMIN'])
                               ->orWhereNull('n.target_role');
                    });
                } elseif ($currentRole === 'ADMIN') {
                    $base->where(function ($filter) {
                        $filter->whereRaw('UPPER(n.target_role) != ?', ['SUPERADMIN'])
                               ->orWhereNull('n.target_role');
                    });
                }

                $unreadNotificationCount = (int) $base->count();
            }

            $view->with('unreadNotificationCount', $unreadNotificationCount);
        });
    }
}
