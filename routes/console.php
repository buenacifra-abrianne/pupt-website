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
