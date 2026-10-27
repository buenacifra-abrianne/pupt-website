<?php

namespace App\Http\Controllers\Superadmin;

use App\Exceptions\CmsHistoryException;
use App\Http\Controllers\Controller;
use App\Services\CmsS3BackupHistory;
use App\Services\DatabaseBackupService;
use App\Support\AuditLog;
use App\Support\CmsVersionHistory;
use App\Support\PublicationBackupContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class PublicationHistoryController extends Controller
{
    public function __construct(private CmsS3BackupHistory $history, private DatabaseBackupService $backupService) {}

    private function ready(string $scope): void
    {
        $definition = PublicationBackupContent::definition($scope);
        if (! Schema::hasTable('backups') || ! Schema::hasTable('cms_content_versions') || ! Schema::hasColumn('cms_content_versions', 'source_backup_id')) {
            throw new CmsHistoryException(503, 'Version history is not ready. Please run migrations first.', 'history_not_ready');
        }
        foreach ($definition['tables'] as $table => $key) {
            if (! Schema::hasTable($table)) {
                throw new CmsHistoryException(503, 'The database table for this page is not ready.', 'history_not_ready');
            }
        }
    }

    private function version(string $scope, int $version): object
    {
        $this->ready($scope);
        $backup = $this->history->backups()->findOrFail($version);

        return PublicationBackupContent::prepare($scope, $this->history->read($backup, $scope));
    }

    public function index(string $scope)
    {
        $this->ready($scope);
        $versions = $this->history->backups()->orderByDesc('created_at')->orderByDesc('id')->paginate(25, ['id', 'backup_name', 'created_at']);
        $versions->getCollection()->each(function ($backup) {
            $backup->setAttribute('actor_name', 'S3 database backup');
            $backup->setAttribute('source', 's3_backup');
        });

        return response()->json(['ok' => true, 'versions' => $versions]);
    }

    public function show(string $scope, int $version)
    {
        $selected = $this->version($scope, $version);
        $live = PublicationBackupContent::live($scope);
        $backup = $this->history->backups()->findOrFail($version);
        $previousBackup = $this->history->backups()->where(function ($query) use ($backup) {
            $query->where('created_at', '<', $backup->created_at)->orWhere(function ($query) use ($backup) {
                $query->where('created_at', $backup->created_at)->where('id', '<', $backup->id);
            });
        })->orderByDesc('created_at')->orderByDesc('id')->first();
        $previous = null;
        if ($previousBackup) {
            try {
                $previous = $this->version($scope, $previousBackup->id);
            } catch (HttpExceptionInterface $exception) {
                // A missing older archive must not prevent a live-content comparison.
            }
        }
        $fields = PublicationBackupContent::fields($selected);
        $comparisons = [];
        foreach (['previous' => $previous, 'live' => $live] as $name => $other) {
            $beforeFields = PublicationBackupContent::fields($other);
            $comparisons[$name] = [];
            foreach (array_unique(array_merge(array_keys($beforeFields), array_keys($fields))) as $field) {
                $before = $beforeFields[$field] ?? null;
                $after = $fields[$field] ?? null;
                $comparisons[$name][] = ['field' => $field, 'before' => $before, 'after' => $after, 'changed' => $before !== $after];
            }
        }

        return response()->json([
            'ok' => true, 'version' => $selected, 'comparisons' => $comparisons,
            'has_previous' => $previous !== null, 'backup_checksum' => $selected->backup_checksum,
            'live_checksum' => CmsVersionHistory::checksum($live),
            'head_id' => (int) DB::table('cms_content_versions')->where('tab_key', $scope)->max('id'),
            'matches_live' => hash_equals($selected->checksum, CmsVersionHistory::checksum($live)),
        ]);
    }

    public function restore(Request $request, string $scope, int $version)
    {
        $selected = $this->version($scope, $version);
        $data = $request->validate([
            'live_checksum' => ['required', 'string', 'size:64'],
            'backup_checksum' => ['required', 'string', 'size:64'],
            'head_id' => ['required', 'integer', 'min:0'],
        ]);
        $live = PublicationBackupContent::live($scope);
        if (! hash_equals($selected->backup_checksum, $data['backup_checksum']) || ! hash_equals(CmsVersionHistory::checksum($live), $data['live_checksum'])
            || (int) DB::table('cms_content_versions')->where('tab_key', $scope)->max('id') !== (int) $data['head_id']) {
            throw new CmsHistoryException(409, 'This page or its S3 backup changed after review. Reopen version history before restoring.', 'publication_history_conflict');
        }
        if (hash_equals($selected->checksum, CmsVersionHistory::checksum($live))) {
            return response()->json(['ok' => true, 'no_changes' => true]);
        }
        PublicationBackupContent::verifyFiles($selected);
        try {
            $safety = $this->backupService->createS3Backup((int) session('user_id') ?: null);
        } catch (Throwable $exception) {
            report($exception);
            throw new CmsHistoryException(503, 'A safety backup could not be saved to S3. No content was restored.', 's3_safety_backup_failed', $exception);
        }
        try {
            $changed = PublicationBackupContent::restore($scope, $selected, $data['live_checksum'], (int) $data['head_id']);
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw new CmsHistoryException(422, 'These records could not be restored to the current database. No content was changed.', 'publication_restore_failed', $exception);
        }
        AuditLog::record('UPDATED', 'CONTENT', 'Restored '.PublicationBackupContent::definition($scope)['label'].' from S3 backup #'.$version.'. Safety backup #'.$safety->id.'.', $version);

        return response()->json(['ok' => true, 'no_changes' => ! $changed, 'safety_backup_id' => $safety->id]);
    }

    public function preview(Request $request, string $scope, int $version, string $sectionKey)
    {
        $selected = $this->version($scope, $version);
        if ($request->filled('backup_checksum') && ! hash_equals($selected->backup_checksum, (string) $request->query('backup_checksum'))) {
            throw new CmsHistoryException(409, 'This S3 backup changed after review. Reopen version history.', 'publication_history_conflict');
        }

        return response()->view('superadmin.partials.publication-history-preview', [
            'label' => PublicationBackupContent::definition($scope)['label'],
            'tables' => json_decode($selected->content, true, 512, JSON_THROW_ON_ERROR),
        ]);
    }
}
