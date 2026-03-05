<?php

// Superadmin
use App\Http\Controllers\Superadmin\AuthController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\AnnouncementController;
use App\Http\Controllers\Superadmin\ApprovalsController;
use App\Http\Controllers\Superadmin\CmsController;
use App\Http\Controllers\Superadmin\NotificationController;
use App\Http\Controllers\Superadmin\AnalyticsController;
use App\Http\Controllers\Superadmin\AccountsController;

// Faculty
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;

// Public
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\AcademicsController;
use App\Http\Controllers\Public\StudentsController;
use App\Http\Controllers\Public\EventsController;
use App\Http\Controllers\Public\ResearchController;
use App\Http\Controllers\Public\FeedbackController;

Route::get('/', function () {
    return view('public.index'); // <-- ito yung index blade mo
})->name('public.landing');

Route::get('/home', [HomeController::class, 'index'])->name('public.home');
Route::get('/about', [AboutController::class, 'index'])->name('public.about');
Route::get('/academics', [AcademicsController::class, 'index'])->name('public.academics');
Route::get('/students', [StudentsController::class, 'index'])->name('public.students');
Route::get('/events', [EventsController::class, 'index'])->name('public.events');
Route::get('/research', [ResearchController::class, 'index'])->name('public.research');
Route::get('/feedback', [FeedbackController::class, 'index'])->name('public.feedback');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('public.feedback.submit');

Route::post('/profile/update', [AuthController::class, 'updateProfile'])
    ->middleware('superadmin.auth')
    ->name('profile.update');

// Staff Login
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['superadmin.auth', 'nonsuperadmin.role'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements');

        Route::delete('/requests/{id}', [AdminAnnouncementController::class, 'deleteRequestOnly'])
            ->name('requests.delete');

        // Requests (Announcements)
        Route::post('/announcements/request-create', [AdminAnnouncementController::class, 'requestCreateAnnouncement'])
            ->name('announcements.requestCreate');
        Route::post('/announcements/request-update', [AdminAnnouncementController::class, 'requestUpdateAnnouncement'])
            ->name('announcements.requestUpdate');
        Route::post('/announcements/request-delete', [AdminAnnouncementController::class, 'requestDeleteAnnouncement'])
            ->name('announcements.requestDelete');

        // Requests (Announcement Toggle)
        Route::post('/announcements/request-enable',  [AdminAnnouncementController::class, 'requestEnableAnnouncement'])
            ->name('announcements.requestEnable');

        Route::post('/announcements/request-disable', [AdminAnnouncementController::class, 'requestDisableAnnouncement'])
            ->name('announcements.requestDisable');

        // Requests (News)
        Route::post('/news/request-create', [AdminAnnouncementController::class, 'requestCreateNews'])
            ->name('news.requestCreate');
        Route::post('/news/request-update', [AdminAnnouncementController::class, 'requestUpdateNews'])
            ->name('news.requestUpdate');
        Route::post('/news/request-delete', [AdminAnnouncementController::class, 'requestDeleteNews'])
            ->name('news.requestDelete');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'page'])
            ->name('notifications');

        Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markRead'])
            ->name('notifications.markRead');

        Route::post('/notifications/delete', [AdminNotificationController::class, 'delete'])
            ->name('notifications.delete');

    });

// Faculty Login
Route::prefix('superadmin')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('superadmin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('superadmin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('superadmin.logout');

    Route::middleware(['superadmin.auth', 'superadmin.role'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('superadmin.dashboard');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('superadmin.announcements');

        Route::get('/content', [CmsController::class, 'page'])->name('superadmin.content');

        Route::get('/notifications', [NotificationController::class, 'page'])->name('superadmin.notifications');

        Route::get('/accounts', [AccountsController::class, 'index'])->name('superadmin.accounts');

        Route::post('/analytics/api', [AnalyticsController::class, 'superadminApi'])->name('superadmin.analytics.superadminApi');

        // Accounts
        Route::post('/accounts', [AccountsController::class, 'store'])
            ->name('superadmin.accounts.store');

        Route::post('/accounts', [AccountsController::class, 'store'])
            ->name('superadmin.accounts.store');

        Route::put('/accounts/{id}', [AccountsController::class, 'update'])
            ->name('superadmin.accounts.update');

        Route::patch('/accounts/{id}/status', [AccountsController::class, 'updateStatus'])
            ->name('superadmin.accounts.status');

        // Notifications

        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])
            ->name('superadmin.notifications.markRead');

        Route::post('/notifications/delete', [NotificationController::class, 'delete'])
            ->name('superadmin.notifications.delete');

        // Announcements

        Route::post('/announcements/save', [AnnouncementController::class, 'save'])
            ->name('superadmin.announcements.save');

        Route::post('/announcements/delete', [AnnouncementController::class, 'delete'])
            ->name('superadmin.announcements.delete');

        Route::post('/announcements/toggle', [AnnouncementController::class, 'toggle'])
            ->name('superadmin.announcements.toggle');

        // News

        Route::post('/news/save', [AnnouncementController::class, 'saveNews'])
            ->name('superadmin.news.save');

        Route::post('/news/delete', [AnnouncementController::class, 'deleteNews'])
            ->name('superadmin.news.delete');

        // Approvals
        
        Route::get('/approvals/pending', [ApprovalsController::class, 'pending'])
            ->name('superadmin.approvals.pending');

        Route::post('/approvals/{id}/approve', [ApprovalsController::class, 'approve'])
            ->name('superadmin.approvals.approve');

        Route::post('/approvals/{id}/reject', [ApprovalsController::class, 'reject'])
            ->name('superadmin.approvals.reject');

        Route::delete('/approvals/{id}', [ApprovalsController::class, 'destroy'])
            ->name('superadmin.approvals.destroy');

        // Analytics

        Route::post('/analytics/export/pdf', [AnalyticsController::class, 'exportPdf'])
            ->name('superadmin.analytics.exportPdf');

        Route::post('/analytics/export/excel', [AnalyticsController::class, 'exportExcel'])
            ->name('superadmin.analytics.exportExcel');
    });
});
