<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function superadminApi(Request $request)
    {
        try {
            [$startAt, $endAt] = $this->resolveDateRange($request);

            $payload = [
                'ok' => true,
                'range' => [
                    'start' => $startAt->toDateString(),
                    'end' => $endAt->toDateString(),
                ],
            ] + $this->buildAnalyticsPayload($startAt, $endAt);

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Unable to load analytics data.',
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $filename = 'analytics_report_'.now()->format('Ymd_His').'.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['Metric', 'Value']);

        fputcsv($output, ['Total Visitors', $request->input('total_visitors', 0)]);
        fputcsv($output, ['Avg Session Duration', $request->input('avg_duration', '0m 0s')]);
        fputcsv($output, ['Bounce Rate', $request->input('bounce_rate', '0%')]);

        fputcsv($output, ['Sessions', $request->input('sessions', 0)]);
        fputcsv($output, ['Page views', $request->input('pageviews', 0)]);
        fputcsv($output, ['Pages per Session', $request->input('pages_per_session', 0)]);

        fclose($output);
        exit;
    }

    public function exportPdf(Request $request)
    {
        $payload = json_decode((string) $request->input('payload', ''), true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $data = [
            'total_visitors' => data_get($payload, 'kpis.total_visitors', $request->input('total_visitors', 0)),
            'avg_duration' => data_get($payload, 'kpis.avg_duration', $request->input('avg_duration', '0m 0s')),
            'bounce_rate' => data_get($payload, 'kpis.bounce_rate', $request->input('bounce_rate', '0%')),
            'total_uploads' => data_get($payload, 'upload_analytics.total_uploads', 0),

            'sessions' => data_get($payload, 'user_engagement.sessions', $request->input('sessions', 0)),
            'pageviews' => data_get($payload, 'user_engagement.pageviews', $request->input('pageviews', 0)),
            'pages_per_session' => data_get($payload, 'user_engagement.pages_per_session', $request->input('pages_per_session', 0)),
        ];

        return view('superadmin.analytics.print', [
            'data' => $data,
            'feedback' => data_get($payload, 'feedback_results', $this->feedbackDefaults()),
            'uploads' => data_get($payload, 'upload_analytics', $this->uploadDefaults()),
            'announcementReach' => data_get($payload, 'announcement_reach', [
                'views' => 0,
                'unique_viewers' => 0,
                'clicks' => 0,
                'ctr_pct' => 0,
            ]),
            'start' => $request->input('start', ''),
            'end' => $request->input('end', ''),
            'generatedAt' => now()->format('F d, Y h:i A'),
        ]);
    }

    private function buildAnalyticsPayload(Carbon $startAt, Carbon $endAt): array
    {
        $sessionRows = collect();

        if ($this->hasAnalyticsSchema()) {
            $sessionRows = DB::table('analytics_sessions')
                ->select('visitor_id', 'pageviews_count', 'started_at', 'last_activity_at')
                ->whereBetween('started_at', [$startAt, $endAt])
                ->get();
        }

        $sessions = $sessionRows->count();

        $totalVisitors = $sessionRows
            ->pluck('visitor_id')
            ->filter()
            ->unique()
            ->count();

        $pageviews = (int) $sessionRows->sum(function ($row) {
            return (int) ($row->pageviews_count ?? 0);
        });

        $totalDurationSec = (int) $sessionRows->sum(function ($row) {
            return $this->estimateSessionDurationSec($row);
        });

        $avgSessionDurationSec = (int) round($totalDurationSec / max(1, $sessions));

        $bounceSessions = $sessionRows->filter(function ($row) {
            return (int) ($row->pageviews_count ?? 0) <= 1;
        })->count();

        $bounceRatePct = round(($bounceSessions / max(1, $sessions)) * 100, 2);
        $pagesPerSession = round($pageviews / max(1, $sessions), 2);
        $feedbackResults = $this->resolveFeedbackResults($startAt, $endAt);
        $uploadAnalytics = $this->resolveUploadAnalytics($startAt, $endAt);

        return [
            'kpis' => [
                'total_visitors' => (int) $totalVisitors,
                'avg_session_duration_sec' => $avgSessionDurationSec,
                'bounce_rate_pct' => $bounceRatePct,
            ],
            'user_engagement' => [
                'sessions' => (int) $sessions,
                'pageviews' => $pageviews,
                'pages_per_session' => $pagesPerSession,
            ],
            'feedback_results' => $feedbackResults,
            'upload_analytics' => $uploadAnalytics,
            'announcement_reach' => [
                'views' => 0,
                'unique_viewers' => 0,
                'clicks' => 0,
                'ctr_pct' => 0,
            ],
        ];
    }

    private function estimateSessionDurationSec(object $row): int
    {
        try {
            $started = Carbon::parse($row->started_at);
            $ended = $row->last_activity_at ? Carbon::parse($row->last_activity_at) : $started;
            $observed = max(0, $ended->diffInSeconds($started));

            if ($observed > 0) {
                return $observed;
            }

            // Fallback for single-hit / same-second sessions so Avg Session Duration is not always 0.
            $pageviews = max(1, (int) ($row->pageviews_count ?? 1));

            return $pageviews * 30;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function emptyAnalyticsPayload(): array
    {
        return [
            'kpis' => [
                'total_visitors' => 0,
                'avg_session_duration_sec' => 0,
                'bounce_rate_pct' => 0,
            ],
            'user_engagement' => [
                'sessions' => 0,
                'pageviews' => 0,
                'pages_per_session' => 0,
            ],
            'feedback_results' => $this->feedbackDefaults(),
            'upload_analytics' => $this->uploadDefaults(),
            'announcement_reach' => [
                'views' => 0,
                'unique_viewers' => 0,
                'clicks' => 0,
                'ctr_pct' => 0,
            ],
        ];
    }

    private function resolveFeedbackResults(Carbon $startAt, Carbon $endAt): array
    {
        $scoreColumns = $this->feedbackScoreColumns();

        if ($scoreColumns === []) {
            return $this->feedbackDefaults();
        }

        $query = DB::table('feedback_submissions');

        if (Schema::hasColumn('feedback_submissions', 'created_at')) {
            $query->whereBetween('created_at', [$startAt, $endAt]);
        }

        $selects = ['COUNT(*) as total_responses'];
        foreach ($scoreColumns as $index => $column) {
            $questionNumber = $index + 1;
            $selects[] = sprintf('AVG(%s) as question_%d_avg', $column, $questionNumber);
        }

        $overallScoreExpr = $this->feedbackOverallScoreExpression($scoreColumns);

        $selects[] = sprintf('AVG(%s) as overall_average', $overallScoreExpr);
        $selects[] = sprintf('SUM(CASE WHEN %s >= 3.5 THEN 1 ELSE 0 END) as outstanding', $overallScoreExpr);
        $selects[] = sprintf('SUM(CASE WHEN %s >= 2.5 AND %s < 3.5 THEN 1 ELSE 0 END) as very_satisfactory', $overallScoreExpr, $overallScoreExpr);
        $selects[] = sprintf('SUM(CASE WHEN %s >= 1.5 AND %s < 2.5 THEN 1 ELSE 0 END) as satisfactory', $overallScoreExpr, $overallScoreExpr);
        $selects[] = sprintf('SUM(CASE WHEN %s < 1.5 THEN 1 ELSE 0 END) as unsatisfactory', $overallScoreExpr);

        $row = $query->selectRaw(implode(', ', $selects))->first();

        if (! $row || (int) ($row->total_responses ?? 0) === 0) {
            return $this->feedbackDefaults();
        }

        $questionAverages = [];
        foreach (range(1, 10) as $questionNumber) {
            $questionAverages[] = $row->{'question_'.$questionNumber.'_avg'} ?? null;
        }
        $roundedQuestionAverages = collect($questionAverages)
            ->map(fn ($value) => round((float) ($value ?? 0), 2))
            ->values()
            ->all();
        $overallAverage = round((float) ($row->overall_average ?? 0), 2);

        return [
            'total_responses' => (int) ($row->total_responses ?? 0),
            'question_1_avg' => $roundedQuestionAverages[0] ?? 0,
            'question_2_avg' => $roundedQuestionAverages[1] ?? 0,
            'question_3_avg' => $roundedQuestionAverages[2] ?? 0,
            'question_4_avg' => $roundedQuestionAverages[3] ?? 0,
            'question_5_avg' => $roundedQuestionAverages[4] ?? 0,
            'question_6_avg' => $roundedQuestionAverages[5] ?? 0,
            'question_7_avg' => $roundedQuestionAverages[6] ?? 0,
            'question_8_avg' => $roundedQuestionAverages[7] ?? 0,
            'question_9_avg' => $roundedQuestionAverages[8] ?? 0,
            'question_10_avg' => $roundedQuestionAverages[9] ?? 0,
            'overall_average' => $overallAverage,
            'final_rating' => $this->feedbackLabelFromScore($overallAverage),
            'outstanding' => (int) ($row->outstanding ?? 0),
            'very_satisfactory' => (int) ($row->very_satisfactory ?? 0),
            'satisfactory' => (int) ($row->satisfactory ?? 0),
            'unsatisfactory' => (int) ($row->unsatisfactory ?? 0),
        ];
    }

    private function feedbackDefaults(): array
    {
        return [
            'total_responses' => 0,
            'question_1_avg' => 0,
            'question_2_avg' => 0,
            'question_3_avg' => 0,
            'question_4_avg' => 0,
            'question_5_avg' => 0,
            'question_6_avg' => 0,
            'question_7_avg' => 0,
            'question_8_avg' => 0,
            'question_9_avg' => 0,
            'question_10_avg' => 0,
            'overall_average' => 0,
            'final_rating' => 'No Data',
            'outstanding' => 0,
            'very_satisfactory' => 0,
            'satisfactory' => 0,
            'unsatisfactory' => 0,
        ];
    }

    private function feedbackLabelFromScore(float $score): string
    {
        if ($score >= 3.5) {
            return 'Outstanding';
        }

        if ($score >= 2.5) {
            return 'Very Satisfactory';
        }

        if ($score >= 1.5) {
            return 'Satisfactory';
        }

        return 'Unsatisfactory';
    }

    private function resolveUploadAnalytics(Carbon $startAt, Carbon $endAt): array
    {
        $roleCounts = [];
        $sourceCounts = [
            'CMS Images' => 0,
            'News Images' => 0,
            'Announcement Images' => 0,
            'Downloadable Files' => 0,
        ];

        $this->collectUploadedPathRows(
            $roleCounts,
            $sourceCounts,
            'news',
            'image_path',
            'created_by',
            'created_at',
            'News Images',
            $startAt,
            $endAt
        );

        $this->collectUploadedPathRows(
            $roleCounts,
            $sourceCounts,
            'announcements',
            'image_path',
            'created_by',
            'created_at',
            'Announcement Images',
            $startAt,
            $endAt
        );

        $this->collectUploadedPathRows(
            $roleCounts,
            $sourceCounts,
            'downloadables',
            'file_path',
            'created_by',
            'created_at',
            'Downloadable Files',
            $startAt,
            $endAt
        );

        if (Schema::hasTable('cms_contents') && Schema::hasColumns('cms_contents', ['content', 'updated_by', 'updated_at'])) {
            $rows = DB::table('cms_contents')
                ->select('content', 'updated_by', 'updated_at')
                ->whereBetween('updated_at', [$startAt, $endAt])
                ->get();

            $roleLabels = $this->roleLabelsForUsers($rows->pluck('updated_by')->filter()->all());

            foreach ($rows as $row) {
                $count = $this->countUploadsInCmsContent((string) ($row->content ?? ''));
                if ($count <= 0) {
                    continue;
                }

                $label = $roleLabels[(int) $row->updated_by] ?? 'Unknown';
                $roleCounts[$label] = ($roleCounts[$label] ?? 0) + $count;
                $sourceCounts['CMS Images'] += $count;
            }
        }

        $total = (int) array_sum($roleCounts);
        $roles = collect($roleCounts)
            ->map(function ($count, $role) use ($total) {
                $count = (int) $count;

                return [
                    'role' => (string) $role,
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        $sources = collect($sourceCounts)
            ->map(fn ($count, $source) => [
                'source' => (string) $source,
                'count' => (int) $count,
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values()
            ->all();

        return [
            'total_uploads' => $total,
            'roles' => $roles,
            'sources' => $sources,
        ];
    }

    private function collectUploadedPathRows(
        array &$roleCounts,
        array &$sourceCounts,
        string $table,
        string $pathColumn,
        string $userColumn,
        string $dateColumn,
        string $sourceLabel,
        Carbon $startAt,
        Carbon $endAt
    ): void {
        if (! Schema::hasTable($table) || ! Schema::hasColumns($table, [$pathColumn, $userColumn, $dateColumn])) {
            return;
        }

        $rows = DB::table($table)
            ->select($userColumn, $dateColumn)
            ->whereNotNull($pathColumn)
            ->where($pathColumn, '<>', '')
            ->whereBetween($dateColumn, [$startAt, $endAt])
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $roleLabels = $this->roleLabelsForUsers($rows->pluck($userColumn)->filter()->all());

        foreach ($rows as $row) {
            $userId = (int) ($row->{$userColumn} ?? 0);
            $label = $roleLabels[$userId] ?? 'Unknown';
            $roleCounts[$label] = ($roleCounts[$label] ?? 0) + 1;
            $sourceCounts[$sourceLabel] = ($sourceCounts[$sourceLabel] ?? 0) + 1;
        }
    }

    private function roleLabelsForUsers(array $userIds): array
    {
        $userIds = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($userIds) || ! Schema::hasTable('users')) {
            return [];
        }

        $userPk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $query = DB::table('users')
            ->whereIn('users.'.$userPk, $userIds)
            ->select('users.'.$userPk.' as user_key');

        if (Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
            $query->leftJoin('roles', 'users.role_id', '=', 'roles.id');
            if (Schema::hasColumn('roles', 'name')) {
                $query->addSelect('roles.name as role_name');
            }
            if (Schema::hasColumn('roles', 'code')) {
                $query->addSelect('roles.code as role_code');
            }
        }

        if (Schema::hasColumn('users', 'role')) {
            $query->addSelect('users.role as user_role');
        }

        return $query->get()
            ->mapWithKeys(function ($row) {
                $label = (string) ($row->role_name ?? $row->role_code ?? $row->user_role ?? 'Unknown');
                $label = trim($label) !== '' ? $label : 'Unknown';

                return [(int) $row->user_key => $label];
            })
            ->all();
    }

    private function countUploadsInCmsContent(string $content): int
    {
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->countUploadPathsInValue($decoded);
        }

        preg_match_all('/(?:storage|uploads|cms|home|about|academics|students|research|events)\/[^"\']+\.(?:jpe?g|png|gif|webp|svg|pdf|docx?|xlsx?|pptx?)/i', $content, $matches);

        return count(array_unique($matches[0] ?? []));
    }

    private function countUploadPathsInValue(mixed $value): int
    {
        if (is_array($value)) {
            $count = 0;
            foreach ($value as $key => $child) {
                if (is_string($child) && $this->looksLikeUploadPath($child, (string) $key)) {
                    $count++;
                    continue;
                }

                $count += $this->countUploadPathsInValue($child);
            }

            return $count;
        }

        return 0;
    }

    private function looksLikeUploadPath(string $value, string $key = ''): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        $hasUploadKey = preg_match('/(^|_)(image|file|path|photo|poster|flyer|logo|icon|qr)(_|$)/i', $key) === 1;
        $hasAssetExtension = preg_match('/\.(jpe?g|png|gif|webp|svg|pdf|docx?|xlsx?|pptx?)($|\?)/i', $value) === 1;
        $isUploadLocation = preg_match('/^(storage\/|uploads\/|home\/|about\/|academics\/|students\/|research\/|events\/)/i', $value) === 1;

        return $hasAssetExtension && ($hasUploadKey || $isUploadLocation);
    }

    private function uploadDefaults(): array
    {
        return [
            'total_uploads' => 0,
            'roles' => [],
            'sources' => [],
        ];
    }

    private function resolveDateRange(Request $request): array
    {
        $startInput = trim((string) $request->input('start', ''));
        $endInput = trim((string) $request->input('end', ''));

        if ($startInput !== '' && $endInput !== '') {
            $startAt = Carbon::parse($startInput)->startOfDay();
            $endAt = Carbon::parse($endInput)->endOfDay();
        } else {
            $endAt = now()->endOfDay();
            $startAt = now()->subDays(30)->startOfDay();
        }

        if ($startAt->gt($endAt)) {
            [$startAt, $endAt] = [$endAt->copy()->startOfDay(), $startAt->copy()->endOfDay()];
        }

        return [$startAt, $endAt];
    }

    private function hasAnalyticsSchema(): bool
    {
        return Schema::hasTable('analytics_sessions')
            && Schema::hasColumns('analytics_sessions', [
                'visitor_id',
                'pageviews_count',
                'started_at',
                'last_activity_at',
            ]);
    }

    private function hasFeedbackSchema(): bool
    {
        return Schema::hasTable('feedback_submissions')
            && $this->feedbackScoreColumns() !== [];
    }

    /**
     * @return array<int, string>
     */
    private function feedbackScoreColumns(): array
    {
        $columns = [];

        foreach (range(1, 10) as $questionNumber) {
            $column = 'q'.$questionNumber.'_score';

            if (Schema::hasColumn('feedback_submissions', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Build a row-level average expression from the available feedback score columns.
     */
    private function feedbackOverallScoreExpression(array $scoreColumns): string
    {
        $scoreSumExpr = collect($scoreColumns)
            ->map(fn (string $column) => 'COALESCE('.$column.', 0)')
            ->implode(' + ');

        $scoreCountExpr = collect($scoreColumns)
            ->map(fn (string $column) => 'CASE WHEN '.$column.' IS NULL THEN 0 ELSE 1 END')
            ->implode(' + ');

        return 'CASE WHEN ('.$scoreCountExpr.') > 0 THEN ('.$scoreSumExpr.' / ('.$scoreCountExpr.')) ELSE 0 END';
    }
}
