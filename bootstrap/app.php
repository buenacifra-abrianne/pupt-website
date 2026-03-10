<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TrackPublicAnalytics::class,
        ]);

        $middleware->alias([
            'superadmin.auth' => \App\Http\Middleware\SuperadminAuth::class,
            'superadmin.role' => \App\Http\Middleware\SuperadminRole::class,
            'nonsuperadmin.role' => \App\Http\Middleware\NonSuperadminRoleOnly::class,
            'cms.terms.accepted' => \App\Http\Middleware\EnsureCmsTermsAccepted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
