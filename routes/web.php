<?php

use App\Http\Controllers\Faculty\AuthController;
use App\Http\Controllers\Faculty\DashboardController;
use App\Http\Controllers\Faculty\AnnouncementController;
use App\Http\Controllers\Faculty\ApprovalsController;
use App\Http\Controllers\Faculty\CmsController;
use App\Http\Controllers\Faculty\NotificationController;
use App\Http\Controllers\Faculty\AnalyticsController;

use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\AnnouncementController as StaffAnnouncementController;
use App\Http\Controllers\Staff\NotificationController as StaffNotificationController;

Route::get('/', function () {
    return view('welcome');
});

// Staff Login
Route::prefix('staff')
    ->name('staff.')
    ->middleware(['faculty.auth', 'nonadmin.role'])
    ->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        Route::get('/announcements', [StaffAnnouncementController::class, 'index'])->name('announcements');

        // Requests (Announcements)
        Route::post('/announcements/request-create', [StaffAnnouncementController::class, 'requestCreateAnnouncement'])
            ->name('announcements.requestCreate');
        Route::post('/announcements/request-update', [StaffAnnouncementController::class, 'requestUpdateAnnouncement'])
            ->name('announcements.requestUpdate');
        Route::post('/announcements/request-delete', [StaffAnnouncementController::class, 'requestDeleteAnnouncement'])
            ->name('announcements.requestDelete');

        // Requests (Announcement Toggle)
        Route::post('/announcements/request-enable',  [StaffAnnouncementController::class, 'requestEnableAnnouncement'])
            ->name('announcements.requestEnable');

        Route::post('/announcements/request-disable', [StaffAnnouncementController::class, 'requestDisableAnnouncement'])
            ->name('announcements.requestDisable');

        // Requests (News)
        Route::post('/news/request-create', [StaffAnnouncementController::class, 'requestCreateNews'])
            ->name('news.requestCreate');
        Route::post('/news/request-update', [StaffAnnouncementController::class, 'requestUpdateNews'])
            ->name('news.requestUpdate');
        Route::post('/news/request-delete', [StaffAnnouncementController::class, 'requestDeleteNews'])
            ->name('news.requestDelete');

        // Notifications
        Route::get('/notifications', [StaffNotificationController::class, 'page'])
            ->name('notifications');

        Route::post('/notifications/mark-read', [StaffNotificationController::class, 'markRead'])
            ->name('notifications.markRead');

        Route::post('/notifications/delete', [StaffNotificationController::class, 'delete'])
            ->name('notifications.delete');

    });

// Faculty Login
Route::prefix('faculty')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('faculty.login');
    Route::post('/login', [AuthController::class, 'login'])->name('faculty.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('faculty.logout');

    Route::middleware(['faculty.auth', 'admin.role'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('faculty.dashboard');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('faculty.announcements');

        Route::get('/content', [CmsController::class, 'page'])->name('faculty.content');

        Route::get('/notifications', [NotificationController::class, 'page'])->name('faculty.notifications');

        Route::post('/analytics/api', [AnalyticsController::class, 'adminApi'])->name('faculty.analytics.adminApi');

        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])
            ->name('faculty.notifications.markRead');

        Route::post('/notifications/delete', [NotificationController::class, 'delete'])
            ->name('faculty.notifications.delete');

        Route::post('/announcements/save', [AnnouncementController::class, 'save'])
            ->name('faculty.announcements.save');

        Route::post('/announcements/delete', [AnnouncementController::class, 'delete'])
            ->name('faculty.announcements.delete');

        Route::post('/announcements/toggle', [AnnouncementController::class, 'toggle'])
            ->name('faculty.announcements.toggle');

        Route::post('/news/save', [AnnouncementController::class, 'saveNews'])
            ->name('faculty.news.save');

        Route::post('/news/delete', [AnnouncementController::class, 'deleteNews'])
            ->name('faculty.news.delete');

        Route::get('/approvals/pending', [ApprovalsController::class, 'pending'])
            ->name('faculty.approvals.pending');

        Route::post('/approvals/{approval}/approve', [ApprovalsController::class, 'approve'])
            ->name('faculty.approvals.approve');

        Route::post('/approvals/{approval}/reject', [ApprovalsController::class, 'reject'])
            ->name('faculty.approvals.reject');
    });
});