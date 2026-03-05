<?php

// Admin
use App\Http\Controllers\Faculty\AuthController;
use App\Http\Controllers\Faculty\DashboardController;
use App\Http\Controllers\Faculty\AnnouncementController;
use App\Http\Controllers\Faculty\ApprovalsController;
use App\Http\Controllers\Faculty\CmsController;
use App\Http\Controllers\Faculty\NotificationController;
use App\Http\Controllers\Faculty\AnalyticsController;
use App\Http\Controllers\Faculty\AccountsController;

// Faculty
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\AnnouncementController as StaffAnnouncementController;
use App\Http\Controllers\Staff\NotificationController as StaffNotificationController;

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
    ->middleware('faculty.auth')
    ->name('profile.update');

// Staff Login
Route::prefix('staff')
    ->name('staff.')
    ->middleware(['faculty.auth', 'nonadmin.role'])
    ->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        Route::get('/announcements', [StaffAnnouncementController::class, 'index'])->name('announcements');

        Route::delete('/requests/{id}', [StaffAnnouncementController::class, 'deleteRequestOnly'])
            ->name('requests.delete');

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

        Route::get('/accounts', [AccountsController::class, 'index'])->name('faculty.accounts');

        Route::post('/analytics/api', [AnalyticsController::class, 'adminApi'])->name('faculty.analytics.adminApi');

        // Accounts
        Route::post('/accounts', [AccountsController::class, 'store'])
            ->name('faculty.accounts.store');

        // Notifications

        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])
            ->name('faculty.notifications.markRead');

        Route::post('/notifications/delete', [NotificationController::class, 'delete'])
            ->name('faculty.notifications.delete');

        // Announcements

        Route::post('/announcements/save', [AnnouncementController::class, 'save'])
            ->name('faculty.announcements.save');

        Route::post('/announcements/delete', [AnnouncementController::class, 'delete'])
            ->name('faculty.announcements.delete');

        Route::post('/announcements/toggle', [AnnouncementController::class, 'toggle'])
            ->name('faculty.announcements.toggle');

        // News

        Route::post('/news/save', [AnnouncementController::class, 'saveNews'])
            ->name('faculty.news.save');

        Route::post('/news/delete', [AnnouncementController::class, 'deleteNews'])
            ->name('faculty.news.delete');

        // Approvals
        
        Route::get('/approvals/pending', [ApprovalsController::class, 'pending'])
            ->name('faculty.approvals.pending');

        Route::post('/approvals/{id}/approve', [ApprovalsController::class, 'approve'])
            ->name('faculty.approvals.approve');

        Route::post('/approvals/{id}/reject', [ApprovalsController::class, 'reject'])
            ->name('faculty.approvals.reject');

        Route::delete('/approvals/{id}', [ApprovalsController::class, 'destroy'])
            ->name('faculty.approvals.destroy');

        // Analytics

        Route::post('/analytics/export/pdf', [AnalyticsController::class, 'exportPdf'])
            ->name('faculty.analytics.exportPdf');

        Route::post('/analytics/export/excel', [AnalyticsController::class, 'exportExcel'])
            ->name('faculty.analytics.exportExcel');
    });
});
