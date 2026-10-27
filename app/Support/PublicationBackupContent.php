<?php

namespace App\Support;

use App\Exceptions\CmsHistoryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PublicationBackupContent
{
    public static function definitions(): array
    {
        return [
            'news_announcements' => [
                'label' => 'News & Announcements',
                'tables' => ['news' => 'news_id', 'announcements' => 'announcement_id'],
                'notice' => 'Restores all news and announcements from the selected S3 backup, including deleted records, statuses, images, and publication dates. Records created after that backup will be removed. Content Management and Campus Memorandum are unaffected.',
            ],
            'memoranda' => [
                'label' => 'Campus Memorandum',
                'tables' => ['downloadables' => 'downloadable_id'],
                'notice' => 'Restores all campus memoranda from the selected S3 backup, including deleted records and their original file references. Memoranda created after that backup will be removed. Content Management, news, and announcements are unaffected.',
            ],
        ];
    }

    public static function supports(string $scope): bool
    {
        return isset(self::definitions()[$scope]);
    }

    public static function definition(string $scope): array
    {
        abort_unless(self::supports($scope), 404);

        return self::definitions()[$scope];
    }

    public static function encode(array $tables): string
    {
        ksort($tables);
        foreach ($tables as &$rows) {
            ksort($rows, SORT_NUMERIC);
            foreach ($rows as &$row) {
                $row = (array) $row;
                ksort($row);
                foreach ($row as &$value) {
                    $value = $value === null ? null : (is_bool($value) ? (string) (int) $value : (string) $value);
                }
                unset($value);
            }
            unset($row);
        }
        unset($rows);

        return json_encode($tables, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function live(string $scope, bool $lock = false): object
    {
        $definition = self::definition($scope);
        $tables = [];
        foreach ($definition['tables'] as $table => $key) {
            $query = DB::table($table)->orderBy($key);
            if ($lock) {
                $query->lockForUpdate();
            }
            $tables[$table] = $query->get()->keyBy($key)->all();
        }

        return (object) ['title' => $definition['label'], 'content' => self::encode($tables)];
    }

    /** Project older schemas onto today's columns without carrying over current record values. */
    public static function prepare(string $scope, object $snapshot): object
    {
        $tables = json_decode($snapshot->content, true, 512, JSON_THROW_ON_ERROR);
        foreach (self::definition($scope)['tables'] as $table => $key) {
            $columns = collect(Schema::getColumns($table))->keyBy('name');
            if (! array_key_exists($table, $tables)) {
                throw new CmsHistoryException(422, 'This backup does not contain all records required for this page.', 'backup_table_missing');
            }
            foreach ($tables[$table] as $id => &$row) {
                if (! ctype_digit((string) $id) || (int) $id < 1 || (string) ($row[$key] ?? '') !== (string) $id || array_diff(array_keys($row), $columns->keys()->all()) !== []) {
                    throw new CmsHistoryException(422, 'The backup contains records that are incompatible with the current database schema.', 'backup_schema_incompatible');
                }
                foreach ($columns as $name => $column) {
                    if (array_key_exists($name, $row)) {
                        continue;
                    }
                    $default = $column['default'];
                    if ($default !== null && preg_match('/^[\'\"]?(-?\d+)[\'\"]?$/D', (string) $default, $match)) {
                        $row[$name] = $match[1];
                    } elseif ($column['nullable']) {
                        $row[$name] = null;
                    } else {
                        throw new CmsHistoryException(422, 'The backup is missing a required database field. No content was restored.', 'backup_schema_incompatible');
                    }
                }
                if ($table === 'news') {
                    self::newsImages($row);
                }
            }
            unset($row);
        }
        $snapshot = clone $snapshot;
        $snapshot->content = self::encode($tables);
        $snapshot->checksum = CmsVersionHistory::checksum($snapshot);

        return $snapshot;
    }

    public static function fields(?object $snapshot): array
    {
        $fields = [];
        $tables = $snapshot ? json_decode($snapshot->content, true, 512, JSON_THROW_ON_ERROR) : [];
        foreach ($tables as $table => $rows) {
            $label = match ($table) {
                'news' => 'News', 'announcements' => 'Announcements', 'downloadables' => 'Memoranda'
            };
            foreach ($rows as $id => $row) {
                $prefix = $label.' / #'.$id.' / ';
                foreach ($row as $column => $value) {
                    $fields[$prefix.ucfirst(str_replace('_', ' ', $column))] = $value;
                }
            }
        }

        return $fields;
    }

    public static function verifyFiles(object $snapshot): void
    {
        $tables = json_decode($snapshot->content, true, 512, JSON_THROW_ON_ERROR);
        $paths = [];
        foreach ($tables['news'] ?? [] as $row) {
            $paths = array_merge($paths, self::newsImages($row));
        }
        foreach ($tables['downloadables'] ?? [] as $row) {
            $paths[] = $row['file_path'] ?? '';
        }
        foreach (array_unique(array_filter($paths)) as $path) {
            $baseUrl = rtrim((string) config('filesystems.disks.s3.url'), '/');
            if ($baseUrl !== '' && str_starts_with($path, $baseUrl.'/')) {
                $path = substr($path, strlen($baseUrl) + 1);
            } elseif (preg_match('/^(?:https?:)?\/\//i', $path)) {
                continue; // External links are retained as links; do not fetch arbitrary URLs.
            }
            $path = NewsImage::normalizeStoredPath($path);
            if (str_contains($path, '..')) {
                throw new CmsHistoryException(422, 'This backup contains an invalid attachment path.', 'backup_attachment_invalid');
            }
            if (str_starts_with($path, 'assets/') && is_file(public_path($path))) {
                continue;
            }
            try {
                if (Storage::disk('s3')->getDriver()->fileExists($path) || Storage::disk('public')->exists($path)) {
                    continue;
                }
            } catch (Throwable $exception) {
                throw new CmsHistoryException(503, 'The backup attachments could not be checked. Verify S3 access before restoring.', 'backup_attachment_unavailable', $exception);
            }
            throw new CmsHistoryException(422, 'An image or memorandum file referenced by this backup is missing. The database backup cannot recreate deleted files. No content was restored.', 'backup_attachment_missing');
        }
    }

    public static function newsImages(array $row): array
    {
        try {
            $additional = json_decode($row['additional_images'] ?? '[]', true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new CmsHistoryException(422, 'The backup contains an invalid news image gallery.', 'backup_attachment_invalid', $exception);
        }
        if (! is_array($additional)) {
            throw new CmsHistoryException(422, 'The backup contains an invalid news image gallery.', 'backup_attachment_invalid');
        }
        $images = [$row['image_path'] ?? ''];
        foreach ($additional as $image) {
            $value = is_array($image) ? ($image['path'] ?? $image['image_path'] ?? $image['url'] ?? null) : $image;
            if (! is_string($value)) {
                throw new CmsHistoryException(422, 'The backup contains an invalid news image reference.', 'backup_attachment_invalid');
            }
            $images[] = $value;
        }

        return array_values(array_filter($images));
    }

    public static function restore(string $scope, object $snapshot, string $expectedChecksum, int $expectedHead): bool
    {
        return DB::transaction(function () use ($scope, $snapshot, $expectedChecksum, $expectedHead) {
            $live = self::live($scope, true);
            $head = DB::table('cms_content_versions')->where('tab_key', $scope)->orderByDesc('id')->lockForUpdate()->first();
            if (! hash_equals($expectedChecksum, CmsVersionHistory::checksum($live)) || (int) ($head->id ?? 0) !== $expectedHead) {
                throw new CmsHistoryException(409, 'This page changed after review. Reopen version history before restoring.', 'publication_history_conflict');
            }
            if (hash_equals(CmsVersionHistory::checksum($live), $snapshot->checksum)) {
                return false;
            }
            $tables = json_decode($snapshot->content, true, 512, JSON_THROW_ON_ERROR);
            foreach (self::definition($scope)['tables'] as $table => $key) {
                $ids = array_keys($tables[$table]);
                $removed = DB::table($table)->when($ids !== [], fn ($query) => $query->whereNotIn($key, $ids))->pluck($key)->all();
                if ($table === 'downloadables' && $removed !== [] && Schema::hasTable('downloadable_user_reads')) {
                    DB::table('downloadable_user_reads')->whereIn('downloadable_id', $removed)->delete();
                }
                if ($removed !== []) {
                    DB::table($table)->whereIn($key, $removed)->delete();
                }
                foreach ($tables[$table] as $id => $row) {
                    DB::table($table)->updateOrInsert([$key => $id], $row);
                }
            }
            CmsVersionHistory::append($scope, $snapshot, 's3_restore', null, null, (int) $snapshot->id, $snapshot->backup_checksum);

            return true;
        }, 3);
    }
}
