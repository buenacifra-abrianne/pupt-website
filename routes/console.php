<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('scan:links')
    ->dailyAt('01:30')
    ->withoutOverlapping();

Schedule::command('sync:botpress')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('database-backups:run-scheduled')
    ->dailyAt('02:30')
    ->withoutOverlapping();

Schedule::command('faculty:sync-cache')
    ->everySixHours()
    ->withoutOverlapping();

Schedule::command('admin:sync-cache')
    ->everySixHours()
    ->withoutOverlapping();

Schedule::call(function () {
    \App\Models\BlockedIp::where('blacklisted', false)
        ->whereNotNull('blocked_until')
        ->where('blocked_until', '<', now())
        ->delete();
})->everyFifteenMinutes();
