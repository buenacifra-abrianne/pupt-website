<?php

use App\Models\Backup;
use App\Services\DatabaseBackupService;
use App\Support\CmsVersionHistory;
use App\Support\HomeCmsContent;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\CmsBackupFixture;

// Isolated CLI fixture: no HTTP login bypass and no reads/writes to the project database.
if (PHP_SAPI !== 'cli') {
    exit(1);
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config([
    'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
    'session.driver' => 'array', 'app.debug' => false,
    'database_backups.s3_disk' => 's3', 'filesystems.disks.s3.driver' => 's3', 'filesystems.disks.s3.bucket' => 'history-browser-fixture',
]);
DB::purge('sqlite');
$app->instance('middleware.disable', true);
Carbon::setTestNow('2026-10-27 12:00:00');
$session = app('session')->driver();
$session->start();
$session->put([
    'user_logged_in' => true, 'user_id' => 12, 'user_role' => 'SUPERADMIN', 'user_roles' => ['SUPERADMIN'],
    'user_first_name' => 'History', 'user_last_name' => 'Editor', 'terms_accepted' => true,
]);
(require database_path('migrations/2026_03_06_210500_create_cms_contents_table.php'))->up();
(require database_path('migrations/2026_10_27_000001_create_cms_content_versions_table.php'))->up();
(require database_path('migrations/2026_10_28_000001_add_s3_backup_source_to_cms_content_versions.php'))->up();
(require database_path('migrations/2026_06_06_000300_create_backups_table.php'))->up();
Storage::fake('s3');
CmsBackupFixture::create([]);
Schema::create('approval_requests', function (Blueprint $table) {
    $table->id();
    $table->string('type');
    $table->string('status');
});
Schema::create('users', function (Blueprint $table) {
    $table->id();
});
foreach (['announcements' => 'announcement_id', 'news' => 'news_id'] as $tableName => $primaryKey) {
    Schema::create($tableName, function (Blueprint $table) use ($primaryKey) {
        $table->id($primaryKey);
        foreach (['title', 'content', 'link', 'priority', 'status', 'category', 'location', 'image_path'] as $column) {
            $table->text($column)->nullable();
        }
        $table->timestamp('date_published')->nullable();
        $table->timestamps();
    });
}
$home = HomeCmsContent::defaults();
$home['updates']['description'] = 'Earlier campus description';
CmsVersionHistory::publish('home', 'Home Content', HomeCmsContent::encode($home), 'direct_save', 'updates');
CmsBackupFixture::create(DB::table('cms_contents')->get()->keyBy('tab_key')->all());
$home['updates']['description'] = 'Current campus description';
CmsVersionHistory::publish('home', 'Home Content', HomeCmsContent::encode($home), 'direct_save', 'updates');
CmsBackupFixture::create(DB::table('cms_contents')->get()->keyBy('tab_key')->all());
$app->instance(DatabaseBackupService::class, new class extends DatabaseBackupService
{
    public function createS3Backup(?int $userId = null): Backup
    {
        return CmsBackupFixture::create(DB::table('cms_contents')->get()->keyBy('tab_key')->all());
    }
});
$uri = $argv[1] ?? '/superadmin/content';
if (! str_starts_with($uri, '/superadmin/content')) {
    throw new RuntimeException('Only CMS content fixtures can be rendered.');
}
$method = $argv[2] ?? 'GET';
$body = $argv[3] ?? null;
$request = Request::create('http://localhost'.$uri, $method, [], [], [], [
    'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => str_contains($uri, '/history/') && ! str_contains($uri, '/preview/') ? 'application/json' : 'text/html',
], $body);
$request->setLaravelSession($session);
$app->instance('request', $request);
View::share('errors', new ViewErrorBag);
try {
    $response = app('router')->dispatch($request);
    if ($response->getStatusCode() >= 500) {
        throw new RuntimeException($response->exception?->getMessage() ?? 'CMS fixture returned an error response.');
    }
    echo json_encode(['status' => $response->getStatusCode(), 'body' => $response->getContent()]);
} catch (HttpExceptionInterface $exception) {
    echo json_encode(['status' => $exception->getStatusCode(), 'body' => json_encode(['ok' => false, 'message' => $exception->getMessage()])]);
}
