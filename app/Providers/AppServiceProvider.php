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
    }
}
