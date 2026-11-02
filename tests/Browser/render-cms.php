<?php

// CLI-only renderer for browser layout checks. It never adds a web login route.
if (PHP_SAPI !== 'cli') {
    exit(1);
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'app.debug' => false, 'debugbar.enabled' => false]);
$app->instance('middleware.disable', true);
$uri = $argv[1] ?? '/superadmin/dashboard';
$role = explode('/', trim($uri, '/'))[0];
if (!in_array($role, ['superadmin', 'admin', 'staff'], true)) {
    throw new RuntimeException('Only CMS pages can be rendered.');
}
$request = Illuminate\Http\Request::create('http://localhost'.$uri);
if (parse_url($uri, PHP_URL_PATH) === '/superadmin/analytics/export/pdf') {
    $request = Illuminate\Http\Request::create('http://localhost'.$uri, 'POST', ['payload' => json_encode([
        'kpis' => ['total_visitors' => 1234],
        'server_health' => ['status' => 'Online', 'cpu_usage' => '12%', 'memory_usage' => '25%'],
        'upload_analytics' => ['total_uploads' => 12, 'roles' => [['role' => 'Research and Extension', 'count' => 12, 'percentage' => 100]], 'sources' => [['source' => 'Campus announcements', 'count' => 12]]],
    ])]);
}
$app->instance('request', $request);
$session = app('session')->driver();
$session->start();
$session->put([
    'user_logged_in' => true,
    'user_id' => 2147483647,
    'user_role' => $role === 'staff' ? 'FACULTY_PRO' : strtoupper($role),
    'user_roles' => $role === 'staff' ? ['FACULTY_PRO', 'REGISTRAR', 'HAP', 'STUDENT_SERVICES', 'RESEARCH_EXTENSION'] : [strtoupper($role)],
    'user_first_name' => 'Responsive',
    'user_last_name' => 'Preview',
    'user_email' => 'responsive@example.test',
    'terms_accepted' => true,
]);
$request->setLaravelSession($session);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());
Illuminate\Support\Facades\View::composer('components.app.sidebar', function ($view) {
    $view->with(['pendingApprovalCount' => 7, 'unreadNotificationCount' => 29]);
});
Illuminate\Support\Facades\DB::beginTransaction();
try {
    $response = app('router')->dispatch($request);
    if ($response->getStatusCode() !== 200) {
        throw new RuntimeException('CMS render returned '.$response->getStatusCode().' for '.$uri.': '.($response->exception?->getMessage() ?? 'Unknown error'));
    }
    echo $response->getContent();
} finally {
    Illuminate\Support\Facades\DB::rollBack();
}
