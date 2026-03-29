<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnnouncementApiController;

Route::prefix('announcements')->group(function () {
    Route::get('/list', [AnnouncementApiController::class, 'list']);
    Route::get('/{announcement_id}', [AnnouncementApiController::class, 'show']);
});