<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CmsTermsController;

// Superadmin
use App\Http\Controllers\Superadmin\AuthController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\AnnouncementController;
use App\Http\Controllers\Superadmin\ApprovalsController;
use App\Http\Controllers\Superadmin\CmsController;
use App\Http\Controllers\Superadmin\NotificationController;
use App\Http\Controllers\Superadmin\AnalyticsController;
use App\Http\Controllers\Superadmin\AnalyticsServerHealthController;
use App\Http\Controllers\Superadmin\AccountsController;
use App\Http\Controllers\Superadmin\AuditController;
use App\Http\Controllers\Superadmin\DatabaseBackupController;
use App\Http\Controllers\Superadmin\DownloadableController as SuperadminDownloadableController;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ApprovalsController as AdminApprovalsController;
use App\Http\Controllers\Admin\CmsController as AdminCmsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\DownloadableController as AdminDownloadableController;

// Staff
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\AnnouncementController as StaffAnnouncementController;
use App\Http\Controllers\Staff\NotificationController as StaffNotificationController;
use App\Http\Controllers\Staff\CmsController as StaffCmsController;
use App\Http\Controllers\Staff\DownloadableController as StaffDownloadableController;

// Public
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\AcademicsController;
use App\Http\Controllers\Public\StudentsController;
use App\Http\Controllers\Public\EventsController;
use App\Http\Controllers\Public\ResearchController;
use App\Http\Controllers\Public\FeedbackController;
use App\Http\Controllers\Public\PupiapplyController;

// Endpoints
use App\Http\Controllers\SsoController;
use App\Http\Controllers\Auth\OnePortalController;
use App\Http\Controllers\Public\DegreeProgramsController;
use App\Http\Controllers\Public\DiplomaProgramsController;
use App\Http\Controllers\Public\StudentCalendarController;
use App\Http\Controllers\Public\UniversityCalendarController;

Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('public.landing');

Route::get('/home/callback', function () {
    return view('public.home_callback');
})->name('public.home.callback');

Route::get('/home', [HomeController::class, 'index'])->name('public.home');
Route::get('/about', [AboutController::class, 'index'])->name('public.about');
Route::get('/about/{section}', [AboutController::class, 'show'])->name('public.about.section');
Route::get('/academics', [AcademicsController::class, 'index'])->name('public.academics');
Route::get('/academics/pup-iapply', [PupiapplyController::class, 'index'])->name('public.pup-iapply');
Route::get('/academics/degree-programs',   [DegreeProgramsController::class, 'index'])->name('public.degree-programs');
Route::get('/academics/diploma-programs',  [DiplomaProgramsController::class, 'index'])->name('public.diploma-programs');
Route::get('/academics/university-calendar', [UniversityCalendarController::class, 'index'])->name('public.university-calendar');
Route::get('/students', [StudentsController::class, 'index'])->name('public.students');
Route::get('/students/admissions', [StudentsController::class, 'admissions'])->name('public.students.admissions');
Route::get('/students/downloadable-forms', [StudentsController::class, 'downloadableForms'])->name('public.students.downloadable-forms');
Route::get('/students/document-requests', [StudentsController::class, 'documentRequests'])->name('public.students.document-requests');
Route::get('/events', [EventsController::class, 'index'])->name('public.events');
Route::get('/research', [ResearchController::class, 'index'])->name('public.research');
Route::get('/research/strategic-development-plan', [ResearchController::class, 'strategicPlan'])->name('public.research.strategic-development-plan');
Route::get('/feedback', [FeedbackController::class, 'index'])->name('public.feedback');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('public.feedback.submit');

// Endpoints

Route::get('/auth/redirect', [OnePortalController::class, 'redirectToIdp'])->name('oneportal.redirect');
Route::get('/auth/callback', [OnePortalController::class, 'callback'])->name('oneportal.callback');
Route::post('/auth/process', [OnePortalController::class, 'process'])->name('oneportal.process');
Route::post('/auth/logout', [OnePortalController::class, 'logout'])->name('oneportal.logout');

// Logout

Route::post('/logout', [OnePortalController::class, 'logout'])->name('logout');

Route::get('/logout/completed', function () {
    return redirect()->route('public.landing')
        ->with('success', 'You have been logged out.');
})->name('logout.completed');

Route::get('/auth/idp/logout', [OnePortalController::class, 'idpLogout'])
    ->name('idp.logout');

// One Portal Entry Point
Route::get('/sso/login', [App\Http\Controllers\SsoController::class, 'login'])
    ->name('sso.login');

Route::post('/profile/update', [AuthController::class, 'updateProfile'])
    ->middleware('superadmin.auth')
    ->name('profile.update');

Route::post('/profile/password', [AuthController::class, 'updatePassword'])
    ->middleware('superadmin.auth')
    ->name('profile.password.update');

Route::post('/profile/local-password', [AuthController::class, 'updateLocalPassword'])
    ->middleware('superadmin.auth')
    ->name('profile.local-password.update');

Route::middleware('superadmin.auth')->group(function () {
    Route::post('/cms/terms/accept', [CmsTermsController::class, 'accept'])->name('cms.terms.accept');
    Route::get('/cms/terms/blocked', [CmsTermsController::class, 'blocked'])->name('cms.terms.blocked');
});

// Staff Login
Route::prefix('staff')
    ->name('staff.')
    ->middleware(['superadmin.auth', 'check.idp', 'nonsuperadmin.role', 'cms.terms.accepted'])
    ->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        Route::get('/announcements', [StaffAnnouncementController::class, 'index'])->name('announcements');

        Route::get('/content', [StaffCmsController::class, 'index'])->name('content');
        Route::post('/content/request-edit', [StaffCmsController::class, 'requestEdit'])->name('content.requestEdit');

        Route::delete('/requests/{id}', [StaffAnnouncementController::class, 'deleteRequestOnly'])
            ->name('requests.delete');

        Route::get('/requests/{id}/changes', [StaffAnnouncementController::class, 'showRequestChanges'])
            ->name('requests.changes');

        // Requests (Announcements)
        Route::post('/announcements/request-create', [StaffAnnouncementController::class, 'requestCreateAnnouncement'])
            ->name('announcements.requestCreate');
        Route::post('/announcements/request-update', [StaffAnnouncementController::class, 'requestUpdateAnnouncement'])
            ->name('announcements.requestUpdate');
        Route::post('/announcements/request-delete', [StaffAnnouncementController::class, 'requestDeleteAnnouncement'])
            ->name('announcements.requestDelete');

        Route::post('/announcements/request-bulk-delete', [StaffAnnouncementController::class, 'requestBulkDeleteAnnouncements'])
            ->name('announcements.requestBulkDelete');

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

        // Downloadables
        Route::get('/downloadables', [StaffDownloadableController::class, 'index'])->name('downloadables');
        Route::post('/downloadables/mark-read', [StaffDownloadableController::class, 'markAsRead'])->name('downloadables.markRead');
        Route::post('/downloadables/request-create', [StaffDownloadableController::class, 'requestCreate'])->name('downloadables.requestCreate');
        Route::post('/downloadables/request-update', [StaffDownloadableController::class, 'requestUpdate'])->name('downloadables.requestUpdate');
        Route::post('/downloadables/request-delete', [StaffDownloadableController::class, 'requestDelete'])->name('downloadables.requestDelete');
        Route::delete('/downloadables/request/{id}', [StaffDownloadableController::class, 'deleteRequestOnly'])->name('downloadables.request.deleteOnly');

    });

// Admin Login
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['superadmin.auth', 'check.idp', 'nonsuperadmin.role', 'cms.terms.accepted'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements');

        Route::get('/content', [AdminCmsController::class, 'page'])->name('content');
        Route::post('/content/save', [AdminCmsController::class, 'save'])->name('content.save');

        Route::get('/notifications', [AdminNotificationController::class, 'page'])->name('notifications');

        Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markRead'])
            ->name('notifications.markRead');

        Route::post('/notifications/delete', [AdminNotificationController::class, 'delete'])
            ->name('notifications.delete');

        Route::post('/announcements/save', [AdminAnnouncementController::class, 'save'])
            ->name('announcements.save');

        Route::post('/announcements/delete', [AdminAnnouncementController::class, 'delete'])
            ->name('announcements.delete');

        Route::post('/announcements/bulk-delete', [AdminAnnouncementController::class, 'bulkAnnouncements'])
            ->name('announcements.bulk');

        Route::post('/announcements/toggle', [AdminAnnouncementController::class, 'toggle'])
            ->name('announcements.toggle');

        Route::post('/news/save', [AdminAnnouncementController::class, 'saveNews'])
            ->name('news.save');

        Route::post('/news/delete', [AdminAnnouncementController::class, 'deleteNews'])
            ->name('news.delete');

        Route::post('/news/bulk', [AdminAnnouncementController::class, 'bulkNews'])
            ->name('news.bulk');

        Route::get('/approvals/pending', [AdminApprovalsController::class, 'pending'])
            ->name('approvals.pending');

        Route::post('/approvals/{id}/approve', [AdminApprovalsController::class, 'approve'])
            ->name('approvals.approve');

        Route::post('/approvals/{id}/reject', [AdminApprovalsController::class, 'reject'])
            ->name('approvals.reject');

        Route::delete('/approvals/{id}', [AdminApprovalsController::class, 'destroy'])
            ->name('approvals.destroy');

        Route::get('/downloadables', [AdminDownloadableController::class, 'index'])->name('downloadables');
        Route::post('/downloadables/mark-read', [AdminDownloadableController::class, 'markAsRead'])->name('downloadables.markRead');
        Route::post('/downloadables/save', [AdminDownloadableController::class, 'save'])->name('downloadables.save');
        Route::post('/downloadables/delete', [AdminDownloadableController::class, 'delete'])->name('downloadables.delete');
    });

// Superadmin Login
Route::prefix('superadmin')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('superadmin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('superadmin.login.submit');
    
    // MFA Routes
    Route::get('/login/mfa/setup', [\App\Http\Controllers\Superadmin\MfaController::class, 'setup'])->name('superadmin.mfa.setup');
    Route::get('/login/mfa/challenge', [\App\Http\Controllers\Superadmin\MfaController::class, 'challenge'])->name('superadmin.mfa.challenge');
    Route::post('/login/mfa/verify', [\App\Http\Controllers\Superadmin\MfaController::class, 'verify'])->name('superadmin.mfa.verify');

    Route::post('/logout', [AuthController::class, 'logout'])->name('superadmin.logout');

    Route::middleware(['superadmin.auth', 'check.idp', 'superadmin.role', 'cms.terms.accepted'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('superadmin.dashboard');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('superadmin.announcements');

        Route::get('/content', [CmsController::class, 'page'])->name('superadmin.content');
        Route::post('/content/save', [CmsController::class, 'save'])->name('superadmin.content.save');

        Route::get('/notifications', [NotificationController::class, 'page'])->name('superadmin.notifications');

        Route::get('/accounts', [AccountsController::class, 'index'])->name('superadmin.accounts');

        Route::get('/audit', [AuditController::class, 'index'])->name('superadmin.audit');

        Route::get('/database-backups', [DatabaseBackupController::class, 'index'])
            ->name('superadmin.database-backups.index');

        Route::post('/database-backups', [DatabaseBackupController::class, 'store'])
            ->name('superadmin.database-backups.store');

        Route::patch('/database-backups/settings', [DatabaseBackupController::class, 'updateSettings'])
            ->name('superadmin.database-backups.settings');

        Route::get('/database-backups/{backup}/download', [DatabaseBackupController::class, 'download'])
            ->name('superadmin.database-backups.download');

        Route::delete('/database-backups/{backup}', [DatabaseBackupController::class, 'destroy'])
            ->name('superadmin.database-backups.destroy');

        Route::post('/analytics/api', [AnalyticsController::class, 'superadminApi'])->name('superadmin.analytics.superadminApi');

        // Accounts
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

        Route::post('/announcements/bulk-delete', [AnnouncementController::class, 'bulkAnnouncements'])
            ->name('superadmin.announcements.bulk');

        Route::post('/announcements/toggle', [AnnouncementController::class, 'toggle'])
            ->name('superadmin.announcements.toggle');

        // News

        Route::post('/news/save', [AnnouncementController::class, 'saveNews'])
            ->name('superadmin.news.save');

        Route::post('/news/delete', [AnnouncementController::class, 'deleteNews'])
            ->name('superadmin.news.delete');

        Route::post('/news/bulk', [AnnouncementController::class, 'bulkNews'])
            ->name('superadmin.news.bulk');

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

        // Downloadables

         Route::get('/downloadables', [SuperadminDownloadableController::class, 'index'])->name('superadmin.downloadables');
        Route::post('/downloadables/mark-read', [SuperadminDownloadableController::class, 'markAsRead'])->name('superadmin.downloadables.markRead');
        Route::post('/downloadables/save', [SuperadminDownloadableController::class, 'save'])->name('superadmin.downloadables.save');
        Route::post('/downloadables/delete', [SuperadminDownloadableController::class, 'delete'])->name('superadmin.downloadables.delete');
    });


});

Route::middleware(['superadmin.auth', 'check.idp', 'superadmin.role', 'cms.terms.accepted'])
    ->get('/api/analytics/server-health', AnalyticsServerHealthController::class)
    ->name('superadmin.analytics.serverHealth');
