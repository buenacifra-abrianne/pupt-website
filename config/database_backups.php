<?php

return [
    's3_disk' => env('DATABASE_BACKUP_S3_DISK', 's3'),
    'local_disk' => env('DATABASE_BACKUP_LOCAL_DISK', 'database_backups_local'),
    's3_path' => trim((string) env('DATABASE_BACKUP_S3_PATH', 'backups/database'), '/'),
    'dump_binary' => env('DATABASE_BACKUP_DUMP_BINARY', 'mysqldump'),
    'dump_timeout' => (int) env('DATABASE_BACKUP_DUMP_TIMEOUT', 300),
    'download_url_ttl_minutes' => (int) env('DATABASE_BACKUP_DOWNLOAD_URL_TTL', 5),
    'history_max_archive_bytes' => (int) env('DATABASE_BACKUP_HISTORY_MAX_ARCHIVE_BYTES', 268435456),
    'history_max_sql_bytes' => (int) env('DATABASE_BACKUP_HISTORY_MAX_SQL_BYTES', 536870912),
];
