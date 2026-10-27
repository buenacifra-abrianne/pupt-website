<?php

namespace Tests\Support;

use App\Models\Backup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CmsBackupFixture
{
    public static function create(array $pages, string $disk = 's3', array $extraTables = []): Backup
    {
        $id = (int) Backup::query()->max('id') + 1;
        $name = 'fixture_backup_'.$id.'.zip';
        $path = trim((string) config('database_backups.s3_path', 'backups/database'), '/').'/'.$name;
        $sql = "CREATE TABLE `cms_contents` (\n  `id` bigint unsigned NOT NULL,\n  `tab_key` varchar(80) NOT NULL,\n  `title` varchar(255) DEFAULT NULL,\n  `content` longtext,\n  `updated_by` bigint unsigned DEFAULT NULL\n) ENGINE=InnoDB;\n";
        $tuples = [];
        foreach ($pages as $tab => $page) {
            $row = (object) $page;
            $values = [count($tuples) + 1, $tab, $row->title ?? null, $row->content ?? null, $row->updated_by ?? null];
            $tuples[] = '('.implode(',', array_map(self::literal(...), $values)).')';
        }
        if ($tuples !== []) {
            $sql .= 'INSERT INTO `cms_contents` VALUES '.implode(',', $tuples).";\n";
        }
        foreach ($extraTables as $table => $rows) {
            $columns = Schema::getColumnListing($table);
            $sql .= 'CREATE TABLE `'.$table."` (\n";
            $sql .= implode(",\n", array_map(fn ($column) => '  `'.$column.'` longtext', $columns));
            $sql .= "\n) ENGINE=InnoDB;\n";
            $tuples = [];
            foreach ($rows as $row) {
                $row = (array) $row;
                $tuples[] = '('.implode(',', array_map(fn ($column) => self::literal($row[$column] ?? null), $columns)).')';
            }
            if ($tuples !== []) {
                $sql .= 'INSERT INTO `'.$table.'` VALUES '.implode(',', $tuples).";\n";
            }
        }

        // Fixed ZIP timestamps keep separate browser fixture requests byte-identical.
        $entry = 'database.sql';
        $size = strlen($sql);
        $crc = crc32($sql);
        $local = pack('VvvvvvVVVvv', 0x04034B50, 20, 0, 0, 0, 0x21, $crc, $size, $size, strlen($entry), 0).$entry.$sql;
        $central = pack('VvvvvvvVVVvvvvvVV', 0x02014B50, 20, 20, 0, 0, 0, 0x21, $crc, $size, $size, strlen($entry), 0, 0, 0, 0, 0, 0).$entry;
        $bytes = $local.$central.pack('VvvvvVVv', 0x06054B50, 0, 0, 1, 1, strlen($central), strlen($local), 0);
        Storage::disk($disk)->put($path, $bytes);

        return Backup::query()->create([
            'backup_name' => $name, 'file_path' => $path, 'storage_disk' => $disk,
            'file_size' => strlen($bytes), 'created_by' => 12,
        ]);
    }

    private static function literal(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_int($value)) {
            return (string) $value;
        }

        return "'".str_replace(['\\', "\0", "\n", "\r", "\x1a", "'"], ['\\\\', '\\0', '\\n', '\\r', '\\Z', "\\'"], $value)."'";
    }
}
