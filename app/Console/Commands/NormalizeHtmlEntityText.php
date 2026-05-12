<?php

namespace App\Console\Commands;

use App\Services\HtmlEntityNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeHtmlEntityText extends Command
{
    protected $signature = 'content:normalize-html-entities {--dry-run : Show affected rows without updating them}';

    protected $description = 'Decode encoded HTML entities in plain-text content fields such as titles and locations.';

    /**
     * @var array<string, array<int, string>>
     */
    private array $tables = [
        'announcements' => ['title', 'link'],
        'news' => ['title', 'category', 'location', 'link'],
        'approval_requests' => ['title', 'details'],
        'cms_contents' => ['title', 'content'],
        'downloadables' => ['title', 'category', 'description', 'original_filename'],
        'notifications' => ['title', 'message'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;

        foreach ($this->tables as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $availableColumns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($table, $column)
            ));

            if ($availableColumns === []) {
                continue;
            }

            DB::table($table)
                ->select(array_merge(['*'], []))
                ->chunkById(100, function ($rows) use ($table, $availableColumns, $dryRun, &$changed): void {
                    $primaryKey = $this->primaryKeyFor($table);

                    foreach ($rows as $row) {
                        $updates = [];

                        foreach ($availableColumns as $column) {
                            $current = $row->{$column} ?? null;
                            if (!is_string($current) || !str_contains($current, '&')) {
                                continue;
                            }

                            $normalized = $column === 'details'
                                ? $this->normalizeJsonDetails($current)
                                : HtmlEntityNormalizer::plain($current);

                            if ($normalized !== $current) {
                                $updates[$column] = $normalized;
                            }
                        }

                        if ($updates === []) {
                            continue;
                        }

                        $changed++;
                        $id = $row->{$primaryKey};

                        if ($dryRun) {
                            $this->line("{$table}#{$id}: ".implode(', ', array_keys($updates)));
                            continue;
                        }

                        DB::table($table)->where($primaryKey, $id)->update($updates);
                    }
                }, $this->primaryKeyFor($table));
        }

        $message = $dryRun
            ? "{$changed} row(s) would be normalized."
            : "{$changed} row(s) normalized.";

        $this->info($message);

        return self::SUCCESS;
    }

    private function primaryKeyFor(string $table): string
    {
        return match ($table) {
            'announcements' => 'announcement_id',
            'news' => 'news_id',
            'downloadables' => 'downloadable_id',
            'notifications' => 'notification_id',
            default => 'id',
        };
    }

    private function normalizeJsonDetails(string $json): string
    {
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            return HtmlEntityNormalizer::plain($json);
        }

        foreach (['title', 'category', 'location', 'description', 'original_filename'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                $payload[$key] = HtmlEntityNormalizer::plain($payload[$key]);
            }
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $json;
    }
}
