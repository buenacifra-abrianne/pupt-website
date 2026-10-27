# CMS version history from S3 database backups

Superadmins can open **Version history** on Content Management, News & Announcements, and Campus Memorandum. Each selectable version is an existing database backup registered in `backups` on the configured S3 disk. Preview, comparison, and restore fetch that archive from S3 and read the original records from its MySQL SQL dump. The backup SQL is parsed as data and is never executed.

The feature uses the same configuration as `main` and the deployed backup dashboard: `database_backups.s3_disk` (default `s3`) and `database_backups.s3_path` (default `backups/database`). The existing S3 disk uses the deployment's `AWS_BUCKET`, region, and AWS credential provider configuration. No additional bucket is required. Local backup disks, browser storage, and the database's `cms_content_versions` audit copies are never rollback sources. Missing S3 configuration or unreadable archives fail without falling back to local content.

## Restore behavior

Select a backup to compare its CMS fields with the previous S3 backup or live database content. Image fields have thumbnails; the read-only page preview supports all CMS subpages. Available restore points follow the existing database backup schedule and retention settings. Create a backup from **Dashboard → Database backups** to add a restore point.

**Restore this version** restores the whole selected tab, including its nested sections, ordering, visibility, media paths, and document links. Other tabs and separately managed news, announcements, and downloads are not restored by this page operation. The database backup contains file references; uploaded media must remain available at those paths.

On **News & Announcements**, a restore replaces both the `news` and `announcements` collections together, preserving IDs, statuses, visibility, gallery references, and publication/expiration dates. On **Campus Memorandum**, it replaces `downloadables`, including original file references and filenames. Records created after the selected backup are removed, and records deleted since it was taken are recreated. These operations affect only their named page; the confirmation explains the scope. Current per-user read markers are preserved for remaining memorandum IDs and removed for deleted IDs.

News images and memorandum attachments are retained when replaced or removed once version history is installed, because older S3 database backups may reference them. Before restoration, managed attachment references are checked. A missing attachment blocks the entire restore; database backups cannot recreate file binaries that were already deleted. External links are retained without fetching arbitrary third-party URLs. Backups missing a required table or incompatible with the current schema are rejected rather than interpreted as an empty page.

Before publishing a restore, the server:

1. Downloads and verifies the selected S3 archive again, including its SHA-256 fingerprint from the preview.
2. Checks that the live database page and publication history have not changed since review.
3. Creates a fresh full database backup in S3, preserving the current state as another restore point. Failure prevents restoration.
4. Locks the page, rechecks the live state, and updates the live database and audit history in one transaction. The audit records the source backup ID and archive fingerprint.

Only the primary `SUPERADMIN` role can list, inspect, preview, or restore backups through CMS history. Admin and staff interfaces do not expose this feature. Their normal publishing paths retain transactional database audit snapshots, but those snapshots do not appear as S3 restore points.

ZIP decoding uses disposable server temporary files downloaded from S3. These are deleted when the request finishes and are not a persistent history cache or local backup source. Reads are bounded by `DATABASE_BACKUP_HISTORY_MAX_ARCHIVE_BYTES` (default 256 MiB) and `DATABASE_BACKUP_HISTORY_MAX_SQL_BYTES` (default 512 MiB).

## Installation and checks

Run both migrations when deploying this feature:

```sh
php artisan migrate
```

The migrations create `cms_content_versions` and add its S3 source audit columns. Existing S3 database backups containing the supported `cms_contents` table are available immediately; earlier restore points are not fabricated from local audit copies.

```sh
php artisan test --filter='CmsVersionHistoryTest|CmsBackupSqlReaderTest|PublicationHistoryTest'
node tests/Browser/cms-version-history.cjs
```

Tests use SQLite in memory and an isolated S3 storage fake. They never publish fixture data to the project database or the deployed bucket. Browser screenshots are saved under `storage/app/cms-history-preview`. Testing the real deployed bucket requires that deployment's S3 configuration and access.

## Troubleshooting S3 access

The backup list comes from the database's backup records; a populated list does not confirm that this server can read the corresponding S3 objects. Bucket, region, and public asset URL settings identify the destination but do not authenticate requests to private database backups. The local server needs its own AWS authentication through the SDK's configured credentials, profile, or role. A role assigned to the deployment does not automatically authenticate a local development machine.

CMS history reports missing authentication, denied access, missing objects, and invalid archives separately. These messages do not include raw AWS SDK responses. Credentials stay in the server environment or AWS credential store, outside version control. A failed read or failed safety backup leaves live CMS content unchanged.
