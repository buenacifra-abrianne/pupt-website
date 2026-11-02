<?php

namespace Tests\Feature;

use App\Http\Middleware\ActiveSecurityTracker;
use App\Http\Middleware\FirewallMiddleware;
use App\Http\Middleware\TrackPublicAnalytics;
use App\Models\Backup;
use App\Services\CmsS3BackupHistory;
use App\Services\DatabaseBackupService;
use App\Support\DownloadableFile;
use App\Support\NewsImage;
use App\Support\PublicationBackupContent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CmsBackupFixture;
use Tests\TestCase;

class PublicationHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([FirewallMiddleware::class, ActiveSecurityTracker::class, TrackPublicAnalytics::class]);
        foreach ([
            '2026_03_06_210500_create_cms_contents_table.php',
            '2026_10_27_000001_create_cms_content_versions_table.php',
            '2026_10_28_000001_add_s3_backup_source_to_cms_content_versions.php',
            '2026_06_06_000300_create_backups_table.php',
            '2026_06_01_000200_ensure_announcements_and_news_tables.php',
            '2026_06_05_000100_ensure_downloadables_table.php',
            '2026_08_19_232449_add_expiration_date_to_news_table.php',
            '2026_09_02_145644_add_additional_images_to_news_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Schema::table('news', fn (Blueprint $table) => $table->boolean('is_featured')->default(false));
        Schema::create('downloadable_user_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('downloadable_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        config(['filesystems.disks.s3.bucket' => 'history-test', 'database_backups.s3_disk' => 's3']);
        Storage::fake('s3');
        Storage::fake('public');
        Storage::disk('s3')->put('news/old.jpg', 'image');
        Storage::disk('s3')->put('news/gallery.jpg', 'gallery');
        Storage::disk('s3')->put('downloadables/original.pdf', 'pdf');
        $this->withSession(['user_logged_in' => true, 'user_id' => 12, 'user_role' => 'SUPERADMIN', 'user_roles' => ['SUPERADMIN'], 'terms_accepted' => true]);
        DB::table('news')->insert([
            'news_id' => 1, 'title' => 'Original news', 'content' => '<p>Original article</p>', 'status' => 'APPROVED',
            'priority' => 'HIGH', 'image_path' => 'news/old.jpg', 'additional_images' => '["news/gallery.jpg"]',
            'is_hidden_from_public' => 1, 'is_featured' => 1, 'date_published' => '2026-10-01', 'expiration_date' => '2026-12-31', 'created_at' => now(),
        ]);
        DB::table('announcements')->insert(['announcement_id' => 1, 'title' => 'Original announcement', 'content' => 'Original notice', 'status' => 'ENABLED', 'priority' => 'HIGH', 'created_at' => now()]);
        DB::table('downloadables')->insert(['downloadable_id' => 1, 'title' => 'Original memo', 'description' => 'Original memo description', 'file_path' => 'downloadables/original.pdf', 'original_filename' => 'original.pdf', 'created_at' => now(), 'updated_at' => now()]);
        $this->mock(DatabaseBackupService::class)->shouldReceive('createS3Backup')->andReturnUsing(fn () => $this->backup());
    }

    private function backup(array $tables = ['news', 'announcements', 'downloadables']): Backup
    {
        $extra = [];
        foreach ($tables as $table) {
            $extra[$table] = DB::table($table)->get()->all();
        }

        return CmsBackupFixture::create([], 's3', $extra);
    }

    private function url(string $scope, string $suffix = ''): string
    {
        return '/superadmin/publication-history/'.$scope.$suffix;
    }

    private function tokens(array $review): array
    {
        return array_intersect_key($review, array_flip(['head_id', 'backup_checksum', 'live_checksum']));
    }

    public static function roles(): array
    {
        return array_map(fn ($role) => [$role], ['ADMIN', 'FACULTY_PRO', 'REGISTRAR', 'HAP', 'STUDENT_SERVICES', 'RESEARCH_EXTENSION']);
    }

    #[DataProvider('roles')]
    public function test_other_roles_cannot_list_preview_or_restore_either_module(string $role): void
    {
        $id = $this->backup()->id;
        $this->withSession(['user_role' => $role, 'user_roles' => [$role, 'SUPERADMIN']]);
        foreach (['news_announcements', 'memoranda'] as $scope) {
            $this->getJson($this->url($scope))->assertForbidden();
            $this->getJson($this->url($scope, '/'.$id))->assertForbidden();
            $this->getJson($this->url($scope, '/'.$id.'/preview/overview'))->assertForbidden();
            $this->postJson($this->url($scope, '/'.$id.'/restore'))->assertForbidden();
        }
    }

    public function test_news_and_announcements_restore_both_tables_with_media_statuses_and_removed_records(): void
    {
        $backup = $this->backup();
        DB::table('news')->where('news_id', 1)->update(['title' => 'Edited news', 'is_featured' => 0, 'is_hidden_from_public' => 0, 'additional_images' => '[]']);
        DB::table('news')->insert(['news_id' => 2, 'title' => 'New article', 'content' => 'New content']);
        DB::table('announcements')->delete();
        $review = $this->getJson($this->url('news_announcements', '/'.$backup->id))->assertOk()->json();
        $this->assertTrue(collect($review['comparisons']['live'])->contains(fn ($field) => $field['changed'] && str_contains($field['field'], 'Title')));
        $response = $this->postJson($this->url('news_announcements', '/'.$backup->id.'/restore'), $this->tokens($review))->assertOk();
        $this->assertDatabaseHas('news', ['news_id' => 1, 'title' => 'Original news', 'image_path' => 'news/old.jpg', 'additional_images' => '["news/gallery.jpg"]', 'is_featured' => 1, 'is_hidden_from_public' => 1, 'expiration_date' => '2026-12-31']);
        $this->assertDatabaseMissing('news', ['news_id' => 2]);
        $this->assertDatabaseHas('announcements', ['announcement_id' => 1, 'title' => 'Original announcement', 'status' => 'ENABLED']);
        $this->assertDatabaseHas('downloadables', ['downloadable_id' => 1, 'title' => 'Original memo']);
        $this->assertDatabaseHas('cms_content_versions', ['tab_key' => 'news_announcements', 'source_backup_id' => $backup->id, 'source' => 's3_restore']);
        $this->assertNotSame($backup->id, $response->json('safety_backup_id'));
        $this->assertDatabaseCount('cms_contents', 0);
    }

    public function test_memorandum_restore_keeps_original_files_and_cleans_reads_only_for_removed_records(): void
    {
        $backup = $this->backup();
        DB::table('downloadables')->where('downloadable_id', 1)->update(['title' => 'Edited memo']);
        DB::table('downloadables')->insert(['downloadable_id' => 2, 'title' => 'New memo', 'file_path' => 'new.pdf', 'original_filename' => 'new.pdf']);
        foreach ([1, 2] as $id) {
            DB::table('downloadable_user_reads')->insert(['downloadable_id' => $id, 'user_id' => 12]);
        }
        $review = $this->getJson($this->url('memoranda', '/'.$backup->id))->assertOk()->json();
        $this->postJson($this->url('memoranda', '/'.$backup->id.'/restore'), $this->tokens($review))->assertOk();
        $this->assertDatabaseHas('downloadables', ['downloadable_id' => 1, 'title' => 'Original memo', 'file_path' => 'downloadables/original.pdf']);
        $this->assertDatabaseMissing('downloadables', ['downloadable_id' => 2]);
        $this->assertDatabaseHas('downloadable_user_reads', ['downloadable_id' => 1]);
        $this->assertDatabaseMissing('downloadable_user_reads', ['downloadable_id' => 2]);
        $this->assertDatabaseHas('news', ['title' => 'Original news']);
    }

    public function test_missing_attachments_block_restoration_without_changing_the_database(): void
    {
        $backup = $this->backup();
        DB::table('downloadables')->update(['title' => 'Current memo']);
        Storage::disk('s3')->delete('downloadables/original.pdf');
        $review = $this->getJson($this->url('memoranda', '/'.$backup->id))->json();
        $this->postJson($this->url('memoranda', '/'.$backup->id.'/restore'), $this->tokens($review))
            ->assertStatus(422)->assertJsonPath('error_code', 'backup_attachment_missing');
        $this->assertDatabaseHas('downloadables', ['title' => 'Current memo']);
    }

    public function test_archives_missing_a_module_table_are_not_treated_as_empty_records(): void
    {
        $backup = $this->backup(['news']);
        $this->getJson($this->url('news_announcements', '/'.$backup->id))->assertStatus(422);
        $this->assertDatabaseCount('announcements', 1);
    }

    public function test_stale_review_and_replaced_archive_cannot_overwrite_new_content(): void
    {
        $backup = $this->backup();
        $review = $this->getJson($this->url('news_announcements', '/'.$backup->id))->json();
        DB::table('news')->update(['title' => 'Concurrent edit']);
        $this->postJson($this->url('news_announcements', '/'.$backup->id.'/restore'), $this->tokens($review))->assertStatus(409);
        $other = $this->backup();
        $review = $this->getJson($this->url('news_announcements', '/'.$backup->id))->json();
        Storage::disk('s3')->put($backup->file_path, Storage::disk('s3')->get($other->file_path));
        $this->postJson($this->url('news_announcements', '/'.$backup->id.'/restore'), $this->tokens($review))->assertStatus(409);
        $this->assertDatabaseHas('news', ['title' => 'Concurrent edit']);
    }

    public function test_failed_safety_backup_leaves_records_unchanged(): void
    {
        $backup = $this->backup();
        DB::table('downloadables')->update(['title' => 'Current memo']);
        $review = $this->getJson($this->url('memoranda', '/'.$backup->id))->json();
        $this->mock(DatabaseBackupService::class)->shouldReceive('createS3Backup')->once()->andThrow(new \RuntimeException('unavailable'));
        $this->postJson($this->url('memoranda', '/'.$backup->id.'/restore'), $this->tokens($review))->assertStatus(503);
        $this->assertDatabaseHas('downloadables', ['title' => 'Current memo']);
    }

    public function test_failed_database_write_rolls_back_the_whole_restore(): void
    {
        $backup = $this->backup();
        DB::table('downloadables')->update(['title' => 'Current memo']);
        $review = $this->getJson($this->url('memoranda', '/'.$backup->id))->json();
        DB::unprepared("CREATE TRIGGER fail_restore BEFORE UPDATE ON downloadables BEGIN SELECT RAISE(ABORT, 'Storage constraint'); END");
        $this->postJson($this->url('memoranda', '/'.$backup->id.'/restore'), $this->tokens($review))->assertStatus(422);
        $this->assertDatabaseHas('downloadables', ['title' => 'Current memo']);
    }

    public function test_news_and_announcement_changes_roll_back_together_if_either_table_fails(): void
    {
        $backup = $this->backup();
        DB::table('news')->update(['title' => 'Current news']);
        DB::table('announcements')->update(['title' => 'Current announcement']);
        $review = $this->getJson($this->url('news_announcements', '/'.$backup->id))->json();
        DB::unprepared("CREATE TRIGGER fail_announcement_restore BEFORE UPDATE ON announcements BEGIN SELECT RAISE(ABORT, 'Constraint'); END");
        $this->postJson($this->url('news_announcements', '/'.$backup->id.'/restore'), $this->tokens($review))->assertStatus(422);
        $this->assertDatabaseHas('news', ['title' => 'Current news']);
        $this->assertDatabaseHas('announcements', ['title' => 'Current announcement']);
        $this->assertDatabaseMissing('cms_content_versions', ['tab_key' => 'news_announcements']);
    }

    public function test_an_empty_archived_collection_removes_only_that_pages_records(): void
    {
        $savedNews = DB::table('news')->first();
        $savedAnnouncement = DB::table('announcements')->first();
        DB::table('news')->delete();
        DB::table('announcements')->delete();
        $backup = $this->backup();
        DB::table('news')->insert((array) $savedNews);
        DB::table('announcements')->insert((array) $savedAnnouncement);
        $review = $this->getJson($this->url('news_announcements', '/'.$backup->id))->json();
        $this->postJson($this->url('news_announcements', '/'.$backup->id.'/restore'), $this->tokens($review))->assertOk();
        $this->assertDatabaseCount('news', 0);
        $this->assertDatabaseCount('announcements', 0);
        $this->assertDatabaseCount('downloadables', 1);
        Storage::disk('s3')->assertExists('news/old.jpg');
    }

    public function test_older_snapshot_fields_use_database_defaults_instead_of_current_record_values(): void
    {
        $snapshot = app(CmsS3BackupHistory::class)->read($this->backup(), 'news_announcements');
        $tables = json_decode($snapshot->content, true);
        unset($tables['news'][1]['is_featured'], $tables['news'][1]['additional_images'], $tables['news'][1]['expiration_date']);
        $snapshot->content = PublicationBackupContent::encode($tables);
        $prepared = PublicationBackupContent::prepare('news_announcements', $snapshot);
        $news = json_decode($prepared->content, true)['news'][1];
        $this->assertSame('0', $news['is_featured']);
        $this->assertNull($news['additional_images']);
        $this->assertNull($news['expiration_date']);
    }

    public function test_invalid_historical_gallery_is_rejected_with_an_actionable_error(): void
    {
        DB::table('news')->update(['additional_images' => '"invalid gallery"']);
        $backup = $this->backup();
        $this->getJson($this->url('news_announcements', '/'.$backup->id))
            ->assertStatus(422)->assertJsonPath('error_code', 'backup_attachment_invalid');
        $this->assertDatabaseHas('news', ['title' => 'Original news']);
    }

    public function test_previews_are_read_only_and_show_archived_record_content(): void
    {
        $backup = $this->backup();
        DB::table('news')->update(['title' => 'Current news']);
        $this->get($this->url('news_announcements', '/'.$backup->id.'/preview/overview'))
            ->assertOk()->assertSee('Original news')->assertSee('Original announcement')->assertDontSee('Current news');
        $this->get($this->url('memoranda', '/'.$backup->id.'/preview/overview'))
            ->assertOk()->assertSee('Original memo')->assertSee('original.pdf');
    }

    public function test_deleting_managed_files_retains_them_for_historical_database_backups(): void
    {
        NewsImage::delete('news/old.jpg');
        DownloadableFile::delete('downloadables/original.pdf');
        Storage::disk('s3')->assertExists('news/old.jpg');
        Storage::disk('s3')->assertExists('downloadables/original.pdf');
    }

    public function test_identical_restores_do_not_create_a_backup_or_change_records(): void
    {
        $backup = $this->backup();
        $review = $this->getJson($this->url('news_announcements', '/'.$backup->id))->json();
        $this->postJson($this->url('news_announcements', '/'.$backup->id.'/restore'), $this->tokens($review))
            ->assertOk()->assertJsonPath('no_changes', true);
        $this->assertDatabaseCount('backups', 1);
    }

    public function test_history_buttons_are_rendered_on_both_superadmin_pages(): void
    {
        Schema::create('users', fn (Blueprint $table) => $table->id('user_id'));
        View::share('errors', new ViewErrorBag);
        $html = view('superadmin.announcements', [
            'active_news' => collect(), 'expired_news' => collect(),
            'active_announcements' => collect(), 'expired_announcements' => collect(),
            'hasAnnouncementLinkColumn' => true, 'hasNewsLinkColumn' => true,
            'hasNewsFeaturedColumn' => true, 'hasNewsHiddenColumn' => true,
        ])->render();
        $this->assertStringContainsString('id="cms-history-open"', $html);
        $this->assertStringContainsString('data-scope="news_announcements"', $html);
        $html = view('superadmin.downloadables', ['downloadables' => collect()])->render();
        $this->assertStringContainsString('id="cms-history-open"', $html);
        $this->assertStringContainsString('data-scope="memoranda"', $html);
        foreach (['admin/announcements', 'admin/downloadables', 'staff/announcements', 'staff/downloadables'] as $file) {
            $this->assertStringNotContainsString('cms-history-open', file_get_contents(resource_path('views/'.$file.'.blade.php')));
        }
    }
}
