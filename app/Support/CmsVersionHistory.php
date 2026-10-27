<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CmsVersionHistory
{
    public static function checksum(?object $row): string
    {
        return hash('sha256', json_encode([
            $row->title ?? null, $row->content ?? null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public static function append(string $tabKey, ?object $row, string $source, ?string $sectionKey = null, ?int $restoredFrom = null, ?int $sourceBackupId = null, ?string $sourceBackupChecksum = null): int
    {
        $baseline = $source === 'baseline';

        $attributes = [
            'tab_key' => $tabKey,
            'title' => $row->title ?? null,
            'content' => $row->content ?? null,
            'checksum' => self::checksum($row),
            'actor_id' => $baseline ? ($row->updated_by ?? null) : (session('user_id') ?: null),
            'actor_name' => $baseline ? 'Existing content' : (trim(session('user_first_name', '').' '.session('user_last_name', '')) ?: session('user_email', 'CMS editor')),
            'actor_role' => $baseline ? null : session('user_role'),
            'source' => $source,
            'section_key' => $sectionKey ?: null,
            'restored_from_id' => $restoredFrom,
            'created_at' => now(),
        ];
        if ($sourceBackupId !== null) {
            $attributes['source_backup_id'] = $sourceBackupId;
            $attributes['source_backup_checksum'] = $sourceBackupChecksum;
        }

        return DB::table('cms_content_versions')->insertGetId($attributes);
    }

    /** All publishing paths use the same transaction and page lock. */
    public static function publish(string $tabKey, ?string $title, ?string $content, string $source, ?string $sectionKey = null, ?string $expectedChecksum = null, ?int $expectedHead = null, ?int $restoredFrom = null, ?int $sourceBackupId = null, ?string $sourceBackupChecksum = null): bool
    {
        abort_unless(Schema::hasTable('cms_content_versions'), 503, 'Version history is not ready. Please run the CMS version history migration before saving content.');

        return DB::transaction(function () use ($tabKey, $title, $content, $source, $sectionKey, $expectedChecksum, $expectedHead, $restoredFrom, $sourceBackupId, $sourceBackupChecksum) {
            // The unique tab key also serializes the first publication of a page.
            DB::table('cms_contents')->insertOrIgnore([
                'tab_key' => $tabKey, 'title' => null, 'content' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $live = DB::table('cms_contents')->where('tab_key', $tabKey)->lockForUpdate()->first();
            $head = DB::table('cms_content_versions')->where('tab_key', $tabKey)->orderByDesc('id')->lockForUpdate()->first();

            abort_if($expectedChecksum !== null && ! hash_equals($expectedChecksum, self::checksum($live)), 409, 'This page has changed. Reload the CMS and review the latest content before trying again.');
            abort_if($expectedHead !== null && $expectedHead !== (int) ($head->id ?? 0), 409, 'A newer version was published. Reopen version history and review it before restoring.');

            // Preserve changes made before history was installed, or outside the CMS.
            if (! $head || ! hash_equals($head->checksum, self::checksum($live))) {
                self::append($tabKey, $live, 'baseline');
            }

            if ($live->title === $title && $live->content === $content) {
                return false;
            }

            DB::table('cms_contents')->where('tab_key', $tabKey)->update([
                'title' => $title, 'content' => $content,
                'updated_by' => session('user_id') ?: null, 'updated_at' => now(),
            ]);
            self::append($tabKey, (object) ['title' => $title, 'content' => $content], $source, $sectionKey, $restoredFrom, $sourceBackupId, $sourceBackupChecksum);

            return true;
        }, 3);
    }

    public static function resolved(string $tabKey, ?string $content): array
    {
        return match ($tabKey) {
            'home' => HomeCmsContent::fromStored((string) $content),
            'about' => AboutCmsContent::fromStored((string) $content),
            'academics' => AcademicsCmsContent::fromStored((string) $content),
            'students' => StudentsCmsContent::fromStored((string) $content),
            'research_extension' => ResearchCmsContent::fromStored((string) $content),
            'events' => EventsCmsContent::fromStored((string) $content),
        };
    }

    public static function fields(string $tabKey, ?object $row): array
    {
        $fields = ['Title' => $row->title ?? CmsSections::labelForTab($tabKey).' Content'];
        self::flatten(self::resolved($tabKey, $row->content ?? null), '', $fields);

        return $fields;
    }

    private static function flatten(array $values, string $prefix, array &$fields): void
    {
        foreach ($values as $key => $value) {
            $label = is_int($key) ? 'Item '.($key + 1) : ucfirst(str_replace(['_', '-'], ' ', $key));
            $path = $prefix === '' ? $label : $prefix.' / '.$label;
            if (is_array($value) && $value !== []) {
                self::flatten($value, $path, $fields);
            } else {
                $fields[$path] = is_bool($value) ? ($value ? 'Yes' : 'No') : (is_array($value) ? '[]' : (string) $value);
            }
        }
    }
}
