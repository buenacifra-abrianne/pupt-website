<?php

namespace Tests\Feature;

use App\Http\Middleware\ActiveSecurityTracker;
use App\Http\Middleware\FirewallMiddleware;
use App\Http\Middleware\TrackPublicAnalytics;
use App\Models\Backup;
use App\Services\CmsS3BackupHistory;
use App\Services\DatabaseBackupService;
use App\Support\AboutCmsContent;
use App\Support\CmsSections;
use App\Support\CmsVersionHistory;
use App\Support\HomeCmsContent;
use Aws\Command;
use Aws\Exception\CredentialsException;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CmsBackupFixture;
use Tests\TestCase;

class CmsVersionHistoryTest extends TestCase
{
    private int $baselineBackupId;

    protected function setUp(): void
    {
        parent::setUp();
        // Keep authentication, role checks, terms, sessions and validation active.
        $this->withoutMiddleware([
            FirewallMiddleware::class,
            ActiveSecurityTracker::class,
            TrackPublicAnalytics::class,
        ]);
        (require database_path('migrations/2026_03_06_210500_create_cms_contents_table.php'))->up();
        (require database_path('migrations/2026_10_27_000001_create_cms_content_versions_table.php'))->up();
        (require database_path('migrations/2026_10_28_000001_add_s3_backup_source_to_cms_content_versions.php'))->up();
        (require database_path('migrations/2026_06_06_000300_create_backups_table.php'))->up();
        config(['database_backups.s3_disk' => 's3', 'filesystems.disks.s3.driver' => 's3', 'filesystems.disks.s3.bucket' => 'history-test-bucket']);
        Storage::fake('s3');
        $this->baselineBackupId = $this->backupCurrentDatabase();
        $this->mock(DatabaseBackupService::class)->shouldReceive('createS3Backup')->andReturnUsing(
            fn () => Backup::query()->findOrFail($this->backupCurrentDatabase())
        );
        $this->withSession([
            'user_logged_in' => true, 'user_role' => 'SUPERADMIN', 'user_roles' => ['SUPERADMIN'],
            'user_id' => 12, 'user_first_name' => 'History', 'user_last_name' => 'Editor',
            'terms_accepted' => true,
        ]);
    }

    private function backupCurrentDatabase(): int
    {
        $pages = DB::table('cms_contents')->get()->keyBy('tab_key')->all();

        return (int) CmsBackupFixture::create($pages)->id;
    }

    private function historyUrl(string $tab = 'home', string $suffix = ''): string
    {
        return '/superadmin/content/history/'.$tab.$suffix;
    }

    private function latestVersionId(string $tab = 'home'): int
    {
        return (int) DB::table('cms_content_versions')->where('tab_key', $tab)->max('id');
    }

    public static function otherRoles(): array
    {
        return array_map(fn ($role) => [$role], ['ADMIN', 'FACULTY_PRO', 'REGISTRAR', 'HAP', 'STUDENT_SERVICES', 'RESEARCH_EXTENSION', '']);
    }

    #[DataProvider('otherRoles')]
    public function test_history_and_restore_are_forbidden_for_every_other_role(string $role): void
    {
        $id = $this->baselineBackupId;
        // An extra SUPERADMIN entry must not bypass the primary role check.
        $this->withSession(['user_role' => $role, 'user_roles' => [$role, 'SUPERADMIN']]);
        $this->getJson($this->historyUrl())->assertForbidden();
        $this->getJson($this->historyUrl('home', '/'.$id))->assertForbidden();
        $this->getJson($this->historyUrl('home', '/'.$id.'/preview/overview'))->assertForbidden();
        $this->postJson($this->historyUrl('home', '/'.$id.'/restore'))->assertForbidden();
        $this->assertDatabaseCount('cms_contents', 0);
    }

    public function test_history_requires_a_logged_in_session(): void
    {
        $this->withSession(['user_logged_in' => false]);
        $this->getJson($this->historyUrl())->assertStatus(419);
    }

    public function test_direct_saves_by_admin_and_superadmin_are_recorded_without_exposing_history_to_admin(): void
    {
        foreach (['superadmin', 'admin'] as $role) {
            $this->withSession(['user_role' => strtoupper($role), 'user_roles' => [strtoupper($role)]]);
            $this->postJson('/'.$role.'/content/save', [
                'tab_key' => 'home', 'section_key' => 'description',
                'home' => ['campus_description' => 'Published by '.$role],
            ])->assertOk()->assertJsonPath('ok', true);
            $this->assertDatabaseHas('cms_content_versions', ['source' => 'direct_save', 'actor_role' => strtoupper($role)]);
        }
        $this->assertDatabaseCount('cms_content_versions', 8);
        $this->getJson($this->historyUrl())->assertForbidden();
    }

    public static function tabs(): array
    {
        return array_map(fn ($tab) => [$tab], CmsSections::allTabKeys());
    }

    #[DataProvider('tabs')]
    public function test_restore_preserves_the_complete_page_and_keeps_the_displaced_version(string $tab): void
    {
        $original = CmsVersionHistory::resolved($tab, null);
        // Nested media, documents, arrays and HTML must survive byte-for-byte.
        $original['history_test'] = ['image' => 'media/original.jpg', 'pdf' => 'documents/original.pdf', 'items' => [['text' => '<p>Original & complete</p>'], ['text' => 'Second item']]];
        $encoded = json_encode($original, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        CmsVersionHistory::publish($tab, 'Original page', $encoded, 'direct_save');
        $target = $this->backupCurrentDatabase();
        CmsVersionHistory::publish($tab, 'New page', '{"replacement":true}', 'direct_save');
        $displaced = $this->latestVersionId($tab);
        $review = $this->getJson($this->historyUrl($tab, '/'.$target))->assertOk()->json();
        $this->postJson($this->historyUrl($tab, '/'.$target.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertOk()->assertJsonPath('no_changes', false);
        $this->assertDatabaseHas('cms_contents', ['tab_key' => $tab, 'title' => 'Original page', 'content' => $encoded]);
        $this->assertDatabaseHas('cms_content_versions', ['id' => $displaced, 'title' => 'New page', 'content' => '{"replacement":true}']);
        $this->assertDatabaseHas('cms_content_versions', ['tab_key' => $tab, 'source' => 's3_restore', 'source_backup_id' => $target, 'content' => $encoded]);
        $this->assertGreaterThan($displaced, $this->latestVersionId($tab));
        // Another page is never restored as a side effect.
        $this->assertSame(1, DB::table('cms_contents')->count());
    }

    public function test_restore_rejects_a_stale_review_and_does_not_add_a_version(): void
    {
        $target = $this->backupCurrentDatabase();
        CmsVersionHistory::publish('home', 'First', 'First content', 'direct_save');
        $review = $this->getJson($this->historyUrl('home', '/'.$target))->json();
        CmsVersionHistory::publish('home', 'Concurrent edit', 'Latest content', 'direct_save');
        $count = DB::table('cms_content_versions')->count();
        $this->postJson($this->historyUrl('home', '/'.$target.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertStatus(409);
        $this->assertDatabaseHas('cms_contents', ['title' => 'Concurrent edit']);
        $this->assertDatabaseCount('cms_content_versions', $count);
    }

    public function test_local_backups_and_corrupt_s3_archives_cannot_be_restored(): void
    {
        Storage::fake('database_backups_local');
        $local = CmsBackupFixture::create([], 'database_backups_local');
        $this->getJson($this->historyUrl('home', '/'.$local->id))->assertNotFound();
        $this->postJson($this->historyUrl('home', '/'.$local->id.'/restore'))->assertNotFound();
        $backup = Backup::query()->findOrFail($this->baselineBackupId);
        Storage::disk('s3')->put($backup->file_path, 'damaged archive');
        $this->getJson($this->historyUrl('home', '/'.$backup->id))->assertStatus(422);
        $this->postJson($this->historyUrl('home', '/'.$backup->id.'/restore'))->assertStatus(422);
        $this->assertDatabaseCount('cms_contents', 0);
    }

    public function test_restore_reads_s3_instead_of_the_local_history_copy_and_creates_a_safety_backup(): void
    {
        CmsVersionHistory::publish('home', 'S3 original', 'Original S3 content', 'direct_save');
        $target = $this->backupCurrentDatabase();
        DB::table('cms_content_versions')->where('tab_key', 'home')->update(['content' => 'Untrusted local history copy']);
        CmsVersionHistory::publish('home', 'Current page', 'Current database content', 'direct_save');
        $review = $this->getJson($this->historyUrl('home', '/'.$target))->assertOk()->json();
        $this->assertSame('Original S3 content', $review['version']['content']);
        $response = $this->postJson($this->historyUrl('home', '/'.$target.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertOk();
        $safetyBackup = Backup::query()->findOrFail($response->json('safety_backup_id'));
        $safety = app(CmsS3BackupHistory::class)->read($safetyBackup, 'home');
        $this->assertSame('Current database content', $safety->content);
        $this->assertDatabaseHas('cms_contents', ['tab_key' => 'home', 'content' => 'Original S3 content']);
        $this->assertDatabaseHas('cms_content_versions', [
            'source' => 's3_restore', 'source_backup_id' => $target, 'source_backup_checksum' => $review['backup_checksum'],
        ]);
    }

    public function test_missing_s3_archive_cannot_fall_back_to_an_identical_local_file(): void
    {
        $backup = Backup::query()->findOrFail($this->baselineBackupId);
        Storage::fake('database_backups_local');
        Storage::disk('database_backups_local')->put($backup->file_path, Storage::disk('s3')->get($backup->file_path));
        Storage::disk('s3')->delete($backup->file_path);
        $this->getJson($this->historyUrl('home', '/'.$backup->id))->assertStatus(422);
        $this->postJson($this->historyUrl('home', '/'.$backup->id.'/restore'))->assertStatus(422);
        $this->assertDatabaseCount('cms_contents', 0);
    }

    private function failS3Reads(\Throwable $cause): void
    {
        $driver = \Mockery::mock(FilesystemOperator::class);
        $driver->shouldReceive('readStream')->andThrow(
            UnableToReadFile::fromLocation('backups/database/test.zip', '', $cause)
        );
        $adapter = \Mockery::mock(FilesystemAdapter::class);
        $adapter->shouldReceive('getDriver')->andReturn($driver);
        Storage::shouldReceive('disk')->with('s3')->andReturn($adapter);
    }

    public function test_missing_aws_credentials_show_an_actionable_message_instead_of_the_global_generic_error(): void
    {
        $this->failS3Reads(new CredentialsException('INTERNAL_PROVIDER_DETAIL'));
        $response = $this->getJson($this->historyUrl('home', '/'.$this->baselineBackupId))
            ->assertStatus(503)
            ->assertJsonPath('error_code', 's3_authentication_unavailable');
        $this->assertStringContainsString('Configure its AWS credentials or IAM role', $response->json('message'));
        $response->assertDontSee('INTERNAL_PROVIDER_DETAIL')->assertDontSee('We could not complete that request');
        $this->assertDatabaseCount('cms_contents', 0);
    }

    public static function s3Failures(): array
    {
        return [
            ['AccessDenied', 403, 503, 's3_backup_access_denied'],
            ['ExpiredToken', 403, 503, 's3_backup_access_denied'],
            ['NoSuchKey', 404, 404, 's3_backup_not_found'],
            ['ServiceUnavailable', 503, 503, 's3_backup_unavailable'],
        ];
    }

    #[DataProvider('s3Failures')]
    public function test_s3_access_errors_are_reported_safely(string $awsCode, int $awsStatus, int $status, string $errorCode): void
    {
        $this->failS3Reads(new S3Exception('INTERNAL_SDK_DETAIL', new Command('GetObject'), [
            'code' => $awsCode, 'response' => new Response($awsStatus),
        ]));
        $this->getJson($this->historyUrl('home', '/'.$this->baselineBackupId))
            ->assertStatus($status)->assertJsonPath('error_code', $errorCode)
            ->assertDontSee('INTERNAL_SDK_DETAIL')->assertDontSee('We could not complete that request');
    }

    public function test_archive_replacement_after_review_prevents_restore(): void
    {
        CmsVersionHistory::publish('home', 'Current', 'Current content', 'direct_save');
        $review = $this->getJson($this->historyUrl('home', '/'.$this->baselineBackupId))->json();
        $other = Backup::query()->findOrFail($this->backupCurrentDatabase());
        $target = Backup::query()->findOrFail($this->baselineBackupId);
        Storage::disk('s3')->put($target->file_path, Storage::disk('s3')->get($other->file_path));
        $this->postJson($this->historyUrl('home', '/'.$target->id.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertStatus(409);
        $this->get($this->historyUrl('home', '/'.$target->id.'/preview/overview?backup_checksum='.$review['backup_checksum']))->assertStatus(409);
        $this->assertDatabaseHas('cms_contents', ['title' => 'Current', 'content' => 'Current content']);
    }

    public function test_failed_s3_safety_backup_prevents_any_live_changes(): void
    {
        CmsVersionHistory::publish('home', 'Current', 'Current content', 'direct_save');
        $this->mock(DatabaseBackupService::class)->shouldReceive('createS3Backup')->once()->andThrow(new \RuntimeException('S3 is unavailable'));
        $review = $this->getJson($this->historyUrl('home', '/'.$this->baselineBackupId))->json();
        $count = DB::table('cms_content_versions')->count();
        $this->postJson($this->historyUrl('home', '/'.$this->baselineBackupId.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertStatus(503)->assertJsonPath('error_code', 's3_safety_backup_failed');
        $this->assertDatabaseHas('cms_contents', ['title' => 'Current', 'content' => 'Current content']);
        $this->assertDatabaseCount('cms_content_versions', $count);
    }

    public function test_missing_previous_s3_backup_still_allows_comparison_with_live_content(): void
    {
        $first = Backup::query()->findOrFail($this->baselineBackupId);
        $target = $this->backupCurrentDatabase();
        Storage::disk('s3')->delete($first->file_path);
        $this->getJson($this->historyUrl('home', '/'.$target))->assertOk()
            ->assertJsonPath('has_previous', false)
            ->assertJsonPath('comparison_notice', 'The previous S3 backup is unavailable for comparison. Compare this backup with live content instead.');
    }

    public function test_s3_history_does_not_use_a_disk_configured_for_local_storage(): void
    {
        config(['filesystems.disks.s3.driver' => 'local']);
        $this->getJson($this->historyUrl())->assertStatus(503);
        $this->getJson($this->historyUrl('home', '/'.$this->baselineBackupId))->assertStatus(503);
    }

    public function test_safety_backup_cannot_fall_back_to_local_storage_when_s3_is_not_configured(): void
    {
        config(['filesystems.disks.s3.bucket' => null]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('An S3 database backup is required');
        (new DatabaseBackupService)->createS3Backup(12);
    }

    public function test_archives_created_by_the_database_backup_service_can_be_read_as_s3_restore_sources(): void
    {
        $sqlPath = tempnam(sys_get_temp_dir(), 'cms_archive_test_');
        $zipPath = $sqlPath.'.zip';
        file_put_contents($sqlPath, "CREATE TABLE `cms_contents` (\n `tab_key` varchar(80),\n `title` varchar(255),\n `content` longtext\n) ENGINE=InnoDB;\nINSERT INTO `cms_contents` VALUES ('home','Archived page','Archived CMS data');\n");
        try {
            $writer = new \ReflectionMethod(DatabaseBackupService::class, 'zipDump');
            $writer->invoke(new DatabaseBackupService, $sqlPath, $zipPath, 'roundtrip.zip');
            $backup = Backup::query()->findOrFail($this->baselineBackupId);
            Storage::disk('s3')->put($backup->file_path, file_get_contents($zipPath));
            $source = app(CmsS3BackupHistory::class)->read($backup, 'home');
            $this->assertSame('Archived page', $source->title);
            $this->assertSame('Archived CMS data', $source->content);
        } finally {
            unlink($sqlPath);
            if (is_file($zipPath)) {
                unlink($zipPath);
            }
        }
    }

    public function test_change_comparison_shows_before_and_after_and_preview_uses_selected_content(): void
    {
        $before = HomeCmsContent::defaults();
        $before['campus_description'] = 'Earlier campus description';
        $before['updates']['description'] = 'Earlier campus description';
        CmsVersionHistory::publish('home', 'Home', HomeCmsContent::encode($before), 'direct_save');
        $target = $this->backupCurrentDatabase();
        $after = $before;
        $after['campus_description'] = 'Current campus description';
        $after['updates']['description'] = 'Current campus description';
        $after['campus_image'] = 'assets/static_img/logo.png';
        CmsVersionHistory::publish('home', 'Home', HomeCmsContent::encode($after), 'direct_save');
        $review = $this->getJson($this->historyUrl('home', '/'.$target))->assertOk()->json();
        $field = collect($review['comparisons']['live'])->firstWhere('field', 'Campus description');
        $this->assertSame('Current campus description', $field['before']);
        $this->assertSame('Earlier campus description', $field['after']);
        $this->assertTrue($field['changed']);
        $image = collect($review['comparisons']['live'])->firstWhere('field', 'Campus image');
        $this->assertStringEndsWith('/assets/static_img/logo.png', $image['before_image']);
        $this->assertStringEndsWith('/assets/static_img/pupillar.jpeg', $image['after_image']);
        $this->get($this->historyUrl('home', '/'.$target.'/preview/overview'))
            ->assertOk()->assertSee('Earlier campus description')->assertDontSee('Current campus description');
        $this->get('/home')->assertOk()->assertSee('Current campus description');
        $this->postJson($this->historyUrl('home', '/'.$target.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertOk();
        $this->get('/home')->assertOk()->assertSee('Earlier campus description')->assertDontSee('Current campus description');
    }

    public function test_baseline_can_be_restored_and_identical_restores_do_not_create_duplicate_versions(): void
    {
        $baseline = $this->baselineBackupId;
        CmsVersionHistory::publish('home', 'Home', 'Changed', 'direct_save');
        $review = $this->getJson($this->historyUrl('home', '/'.$baseline))->json();
        $this->postJson($this->historyUrl('home', '/'.$baseline.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertOk();
        $this->assertDatabaseHas('cms_contents', ['tab_key' => 'home', 'title' => null, 'content' => null]);
        $count = DB::table('cms_content_versions')->count();
        $review = $this->getJson($this->historyUrl('home', '/'.$baseline))->json();
        $this->postJson($this->historyUrl('home', '/'.$baseline.'/restore'), [
            'live_checksum' => $review['live_checksum'], 'head_id' => $review['head_id'], 'backup_checksum' => $review['backup_checksum'],
        ])->assertOk()->assertJsonPath('no_changes', true);
        $this->assertDatabaseCount('cms_content_versions', $count);
    }

    public function test_restore_requires_review_tokens_and_missing_migration_blocks_untracked_saves(): void
    {
        $this->postJson($this->historyUrl('home', '/'.$this->latestVersionId().'/restore'))->assertStatus(422);
        Schema::drop('cms_content_versions');
        $this->getJson($this->historyUrl())->assertStatus(503);
        $this->postJson('/superadmin/content/save', [
            'tab_key' => 'home', 'section_key' => 'description',
            'home' => ['campus_description' => 'Should not be published'],
        ])->assertStatus(503);
        $this->assertDatabaseCount('cms_contents', 0);
    }

    public function test_a_failed_history_insert_rolls_back_the_live_page_update(): void
    {
        CmsVersionHistory::publish('home', 'Original', 'Original content', 'direct_save');
        $count = DB::table('cms_content_versions')->count();
        DB::unprepared("CREATE TRIGGER fail_history BEFORE INSERT ON cms_content_versions WHEN NEW.source = 'direct_save' BEGIN SELECT RAISE(ABORT, 'History storage failure'); END");
        try {
            CmsVersionHistory::publish('home', 'Failed update', 'Should never go live', 'direct_save');
            $this->fail('Publishing should fail when its snapshot cannot be stored.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('History storage failure', $exception->getMessage());
        }
        $this->assertDatabaseHas('cms_contents', ['tab_key' => 'home', 'title' => 'Original', 'content' => 'Original content']);
        $this->assertDatabaseCount('cms_content_versions', $count);
    }

    public function test_migration_preserves_existing_content_as_the_initial_snapshot(): void
    {
        Schema::drop('cms_content_versions');
        DB::table('cms_contents')->insert([
            'tab_key' => 'home', 'title' => 'Pre-existing page', 'content' => 'Pre-existing content',
            'updated_by' => 34, 'created_at' => now(), 'updated_at' => now(),
        ]);
        (require database_path('migrations/2026_10_27_000001_create_cms_content_versions_table.php'))->up();
        $this->assertDatabaseHas('cms_content_versions', [
            'tab_key' => 'home', 'title' => 'Pre-existing page', 'content' => 'Pre-existing content', 'actor_id' => 34, 'source' => 'baseline',
        ]);
        $this->assertDatabaseHas('cms_contents', ['tab_key' => 'home', 'content' => 'Pre-existing content']);
    }

    public function test_all_historical_pages_render_without_cms_edit_controls(): void
    {
        $pages = [
            'home' => ['overview'],
            'about' => array_merge(['overview'], AboutCmsContent::sectionSlugs()),
            'academics' => ['overview', 'degree-programs', 'diploma-programs', 'pup-iapply', 'university-calendar'],
            'students' => ['overview', 'admissions', 'downloadable-forms', 'document-requests'],
            'research_extension' => ['overview', 'strategic-development-plan'],
            'events' => ['overview'],
        ];
        foreach ($pages as $tab => $sections) {
            foreach ($sections as $section) {
                $this->get($this->historyUrl($tab, '/'.$this->baselineBackupId.'/preview/'.$section))
                    ->assertOk()->assertSee('<html', false)->assertDontSee('class="cms-preview-chip"', false);
            }
        }
    }

    public function test_history_lists_only_paginated_s3_database_backups(): void
    {
        for ($i = 0; $i < 28; $i++) {
            $this->backupCurrentDatabase();
        }
        Storage::fake('database_backups_local');
        CmsBackupFixture::create([], 'database_backups_local');
        $response = $this->getJson($this->historyUrl())->assertOk()->assertJsonPath('versions.total', 29);
        $this->assertCount(25, $response->json('versions.data'));
        $this->assertSame('s3_backup', $response->json('versions.data.0.source'));
        $this->assertArrayNotHasKey('content', $response->json('versions.data.0'));
        $this->getJson($this->historyUrl('home', '?page=2'))->assertOk()->assertJsonCount(4, 'versions.data');
    }

    public static function approvers(): array
    {
        return [['admin'], ['superadmin']];
    }

    #[DataProvider('approvers')]
    public function test_approved_cms_edits_are_versioned_in_the_approval_transaction(string $role): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('details');
            $table->string('type');
            $table->string('status');
            $table->string('requester_name');
            $table->string('requester_email');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('email');
        });
        $id = DB::table('approval_requests')->insertGetId([
            'title' => 'Approved page', 'details' => json_encode(['tab_key' => 'home', 'title' => 'Approved page', 'content' => 'Approved content', 'previous_content' => '']),
            'type' => 'CMS_HOME_EDIT', 'status' => 'pending', 'requester_name' => 'Staff', 'requester_email' => 'staff@example.test',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withSession(['user_role' => strtoupper($role), 'user_roles' => [strtoupper($role)]]);
        $this->postJson('/'.$role.'/approvals/'.$id.'/approve')->assertOk()->assertJsonPath('ok', true);
        $this->assertDatabaseHas('cms_content_versions', ['source' => 'approval', 'content' => 'Approved content']);
        $this->assertDatabaseHas('approval_requests', ['id' => $id, 'status' => 'approved']);

        // A request submitted against an older page must not overwrite a restore.
        DB::table('approval_requests')->where('id', $id)->update(['status' => 'pending']);
        $versions = DB::table('cms_content_versions')->count();
        $this->postJson('/'.$role.'/approvals/'.$id.'/approve')->assertStatus(409);
        $this->assertDatabaseHas('approval_requests', ['id' => $id, 'status' => 'pending']);
        $this->assertDatabaseCount('cms_content_versions', $versions);
    }
}
