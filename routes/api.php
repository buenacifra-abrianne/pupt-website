<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\BotpressWebhookController;

Route::prefix('announcements')
    ->middleware('idp.apikey')
    ->group(function () {
        Route::get('/list', [AnnouncementApiController::class, 'list']);
        Route::get('/{announcement_id}', [AnnouncementApiController::class, 'show']);
    });

Route::post('/botpress/webhook', [BotpressWebhookController::class, 'handle'])
    ->middleware('botpress.webhook');
Route::post('/debug-height', function(\Illuminate\Http\Request $req) { file_put_contents(public_path('heights.txt'), $req->getContent()); return 'ok'; });
