<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\CmsS3BackupHistory;
use App\Services\DatabaseBackupService;
use App\Support\AuditLog;
use App\Support\CmsPreviewHandler;
use App\Support\CmsSections;
use App\Support\CmsVersionHistory;
use App\Support\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class CmsVersionController extends Controller
{
    use CmsPreviewHandler { preview as private renderPreview; }

    private ?object $previewVersion = null;

    public function __construct(private CmsS3BackupHistory $history, private DatabaseBackupService $backupService) {}

    protected function editableCmsPreview(): bool
    {
        return false;
    }

    private function ready(string $tabKey): void
    {
        abort_unless(in_array($tabKey, CmsSections::allTabKeys(), true), 404);
        abort_unless(Schema::hasTable('cms_content_versions'), 503, 'Version history is not ready. Please run migrations first.');
        abort_unless(Schema::hasTable('backups') && Schema::hasColumn('cms_content_versions', 'source_backup_id'), 503, 'S3 backup history is not ready. Please run migrations first.');
    }

    private function version(string $tabKey, int $version): object
    {
        $this->ready($tabKey);
        $backup = $this->history->backups()->findOrFail($version);

        return $this->history->read($backup, $tabKey);
    }

    public function index(Request $request, string $tabKey)
    {
        $this->ready($tabKey);
        $versions = $this->history->backups()->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(25, ['id', 'backup_name', 'created_at']);
        $versions->getCollection()->each(function ($row) {
            $row->setAttribute('actor_name', 'S3 database backup');
            $row->setAttribute('source', 's3_backup');
        });

        return response()->json(['ok' => true, 'versions' => $versions]);
    }

    public function show(string $tabKey, int $version)
    {
        $selected = $this->version($tabKey, $version);
        $live = DB::table('cms_contents')->where('tab_key', $tabKey)->first();
        $backup = $this->history->backups()->findOrFail($version);
        $previousBackup = $this->history->backups()->where(function ($query) use ($backup) {
            $query->where('created_at', '<', $backup->created_at)->orWhere(function ($query) use ($backup) {
                $query->where('created_at', $backup->created_at)->where('id', '<', $backup->id);
            });
        })->orderByDesc('created_at')->orderByDesc('id')->first();
        $previous = null;
        $comparisonNotice = null;
        if ($previousBackup) {
            try {
                $previous = $this->history->read($previousBackup, $tabKey);
            } catch (HttpExceptionInterface $exception) {
                $comparisonNotice = 'The previous S3 backup is unavailable for comparison. Compare this backup with live content instead.';
            }
        }
        $selectedFields = CmsVersionHistory::fields($tabKey, $selected);
        $comparisons = [];
        foreach (['previous' => $previous, 'live' => $live] as $name => $comparison) {
            $otherFields = CmsVersionHistory::fields($tabKey, $comparison);
            $comparisons[$name] = [];
            foreach (array_unique(array_merge(array_keys($otherFields), array_keys($selectedFields))) as $field) {
                $before = $otherFields[$field] ?? null;
                $after = $selectedFields[$field] ?? null;
                $comparisons[$name][] = [
                    'field' => $field, 'before' => $before, 'after' => $after, 'changed' => $before !== $after,
                    'before_image' => $this->imageUrl($field, $before),
                    'after_image' => $this->imageUrl($field, $after),
                ];
            }
        }

        return response()->json([
            'ok' => true, 'version' => $selected, 'comparisons' => $comparisons,
            'has_previous' => $previous !== null,
            'comparison_notice' => $comparisonNotice,
            'backup_checksum' => $selected->backup_checksum,
            'live_checksum' => CmsVersionHistory::checksum($live),
            'head_id' => (int) DB::table('cms_content_versions')->where('tab_key', $tabKey)->max('id'),
            'matches_live' => hash_equals($selected->checksum, CmsVersionHistory::checksum($live)),
        ]);
    }

    public function restore(Request $request, string $tabKey, int $version)
    {
        $selected = $this->version($tabKey, $version);
        $data = $request->validate([
            'live_checksum' => ['required', 'string', 'size:64'],
            'head_id' => ['required', 'integer', 'min:1'],
            'backup_checksum' => ['required', 'string', 'size:64'],
        ]);
        abort_unless(hash_equals($selected->backup_checksum, $data['backup_checksum']), 409, 'The S3 backup changed after it was reviewed. Reopen version history before restoring.');
        $live = DB::table('cms_contents')->where('tab_key', $tabKey)->first();
        abort_unless(hash_equals(CmsVersionHistory::checksum($live), $data['live_checksum'])
            && (int) DB::table('cms_content_versions')->where('tab_key', $tabKey)->max('id') === (int) $data['head_id'], 409, 'The live CMS page changed. Reopen version history and review the latest content before restoring.');
        if (hash_equals(CmsVersionHistory::checksum($live), $selected->checksum)) {
            return response()->json(['ok' => true, 'no_changes' => true, 'message' => 'This S3 backup already matches the live content.']);
        }
        try {
            $safetyBackup = $this->backupService->createS3Backup((int) session('user_id') ?: null);
        } catch (Throwable $exception) {
            report($exception);
            abort(503, 'A safety database backup could not be saved to S3. No content was restored. Please check S3 backup access and try again.');
        }
        $changed = CmsVersionHistory::publish(
            $tabKey, $selected->title, $selected->content, 's3_restore', null,
            $data['live_checksum'], (int) $data['head_id'], null, $version, $selected->backup_checksum
        );
        if ($changed) {
            AuditLog::record('UPDATED', 'CONTENT', 'Restored '.CmsSections::labelForTab($tabKey).' from S3 database backup '.$selected->backup_name.' (#'.$version.'). Safety backup #'.$safetyBackup->id.'.', $version);
        }

        return response()->json(['ok' => true, 'no_changes' => ! $changed, 'safety_backup_id' => $safetyBackup->id, 'message' => 'CMS content restored from the S3 database backup.']);
    }

    public function preview(Request $request, string $tabKey, int $version, string $sectionKey)
    {
        $this->previewVersion = $this->version($tabKey, $version);
        if ($request->filled('backup_checksum')) {
            abort_unless(hash_equals($this->previewVersion->backup_checksum, (string) $request->query('backup_checksum')), 409, 'The S3 backup changed after it was reviewed. Reopen version history.');
        }

        return $this->renderPreview($tabKey, $sectionKey);
    }

    private function loadContents(array $tabs): array
    {
        return [$this->previewVersion->tab_key => ['content' => $this->previewVersion->content]];
    }

    private function imageUrl(string $field, ?string $value): ?string
    {
        if ($value === null || ! preg_match('/(?:image|logo|photo|poster)/i', $field)
            || ! preg_match('/^[^\s<>]+\.(?:png|jpe?g|gif|webp|svg)(?:\?[^\s<>]*)?$/i', $value)) {
            return null;
        }

        return ImageStorage::url($value);
    }
}
