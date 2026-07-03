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

        View::composer(['admin.*', 'superadmin.*'], function ($view) {
            $pendingApprovalCount = 0;

            if (Schema::hasTable('approval_requests')) {
                $pendingApprovalCount = (int) DB::table('approval_requests')
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])
                    ->count();
            }

            $view->with('pendingApprovalCount', $pendingApprovalCount);
        });

        View::composer(['admin.*', 'superadmin.*', 'staff.*'], function ($view) {
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

                $base->where(function ($scope) use ($userId, $role) {
                    if ($role === 'SUPERADMIN') {
                        $scope->where(function ($q) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['SUPERADMIN'])
                              ->whereNull('n.target_user_id');
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['SUPERADMIN'])
                              ->where('n.target_user_id', $userId);
                        })->orWhere(function ($q) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                              ->whereNull('n.target_user_id');
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                              ->where('n.target_user_id', $userId);
                        })->orWhere(function ($q) {
                            $q->whereNull('n.target_role')
                              ->whereNull('n.target_user_id');
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereNull('n.target_role')
                              ->where('n.target_user_id', $userId);
                        });
                    } elseif ($role === 'ADMIN') {
                        $scope->where(function ($q) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                              ->whereNull('n.target_user_id');
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                              ->where('n.target_user_id', $userId);
                        })->orWhere(function ($q) {
                            $q->whereNull('n.target_role')
                              ->whereNull('n.target_user_id');
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereNull('n.target_role')
                              ->where('n.target_user_id', $userId);
                        });
                    } else { // STAFF
                        $scope->where(function ($q) use ($userId) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['STAFF'])
                              ->where('n.target_user_id', $userId);
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereRaw('UPPER(n.target_role) = ?', ['ADMIN'])
                              ->where('n.target_user_id', $userId);
                        })->orWhere(function ($q) use ($userId) {
                            $q->whereNull('n.target_role')
                              ->where('n.target_user_id', $userId);
                        });
                    }
                });

                $unreadNotificationCount = (int) $base->count();
            }

            $view->with('unreadNotificationCount', $unreadNotificationCount);
        });
    }
}
