<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\AnalyticsPdfBuilder;
use App\Services\CloudWatchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly CloudWatchService $cloudWatchService
    ) {
    }

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
        $filename = 'analytics_report_'.now()->format('Ymd_His').'.xlsx';
        $payload = $this->decodeExportPayload($request);
        $report = $this->buildExportReport($request, $payload);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analytics Report');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7F1113'],
            ],
        ];

        $zebraStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8EFE8'],
            ],
        ];
        
        $sheet->setCellValue('A1', 'Metric');
        $sheet->setCellValue('B1', 'Value');
        $sheet->setCellValue('C1', 'Details');
        
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $sheet->freezePane('A2');
        
        $rowNum = 2;
        
        $addSection = function($title, $data) use ($sheet, &$rowNum, $zebraStyle) {
            $sheet->setCellValue('A' . $rowNum, $title);
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
            $rowNum++;
            
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $rowNum, $row[0] ?? '');
                $sheet->setCellValue('B' . $rowNum, $row[1] ?? '');
                $sheet->setCellValue('C' . $rowNum, $row[2] ?? '');
                
                if ($rowNum % 2 === 0) {
                    $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)->applyFromArray($zebraStyle);
                }
                $rowNum++;
            }
            $rowNum++;
        };
        
        $addSection('Report Info', [
            ['Date Range', ($report['start'] !== '' ? $report['start'] : 'All Dates').' to '.($report['end'] !== '' ? $report['end'] : 'All Dates')],
            ['Generated', $report['generatedAt']]
        ]);

        $addSection('Part 1: Monitoring Overview', [
            ['Total Visitors', $report['data']['total_visitors']],
            ['Avg. Session Duration', $report['data']['avg_duration']],
            ['Bounce Rate', $report['data']['bounce_rate']],
            ['Total Uploads', data_get($report, 'uploads.total_uploads', $report['data']['total_uploads'] ?? 0)],
        ]);

        $addSection('Part 2: User Engagement', [
            ['Sessions', $report['data']['sessions']],
            ['Page views', $report['data']['pageviews']],
            ['Pages / Session', $report['data']['pages_per_session']],
        ]);

        $feedback = $report['feedback'];
        $feedbackRows = [];
        for ($index = 1; $index <= 10; $index++) {
            $feedbackRows[] = ['Q'.$index, number_format((float) data_get($feedback, 'question_'.$index.'_avg', 0), 2).' / 4'];
        }
        $feedbackRows[] = ['Total Average', number_format((float) data_get($feedback, 'overall_average', 0), 2).' / 4'];
        $feedbackRows[] = ['Final Result', data_get($feedback, 'final_rating', 'No Data')];
        $feedbackRows[] = ['Outstanding', number_format((int) data_get($feedback, 'outstanding', 0))];
        $feedbackRows[] = ['Very Satisfactory', number_format((int) data_get($feedback, 'very_satisfactory', 0))];
        $feedbackRows[] = ['Satisfactory', number_format((int) data_get($feedback, 'satisfactory', 0))];
        $feedbackRows[] = ['Unsatisfactory', number_format((int) data_get($feedback, 'unsatisfactory', 0))];
        $feedbackRows[] = ['Total Responses', number_format((int) data_get($feedback, 'total_responses', 0))];
        $addSection('Part 3: Feedback Result', $feedbackRows);

        $uploadRolesRows = [];
        $uploadRolesRows[] = ['Uploader Role', 'Uploads', 'Percentage'];
        $uploadRoles = data_get($report, 'uploads.roles', []);
        if ($uploadRoles === []) {
            $uploadRolesRows[] = ['No role upload data found.', '', ''];
        } else {
            foreach ($uploadRoles as $r) {
                $uploadRolesRows[] = [
                    data_get($r, 'role', 'Unknown'),
                    number_format((int) data_get($r, 'count', 0)),
                    number_format((float) data_get($r, 'percentage', 0), 2).'%'
                ];
            }
        }
        $addSection('Part 4: Upload Percentage by Role', $uploadRolesRows);

        $uploadSourcesRows = [];
        $uploadSourcesRows[] = ['Upload Source', 'Uploads'];
        $uploadSources = data_get($report, 'uploads.sources', []);
        if ($uploadSources === []) {
            $uploadSourcesRows[] = ['No uploads found.', ''];
        } else {
            foreach ($uploadSources as $r) {
                $uploadSourcesRows[] = [
                    data_get($r, 'source', 'Uploads'),
                    number_format((int) data_get($r, 'count', 0))
                ];
            }
        }
        $addSection('Part 4: Upload Percentage by Source', $uploadSourcesRows);

        $addSection('Part 5: Announcement Reach', [
            ['Views', number_format((int) data_get($report, 'announcementReach.views', 0))],
            ['Unique Viewers', number_format((int) data_get($report, 'announcementReach.unique_viewers', 0))],
            ['Clicks', number_format((int) data_get($report, 'announcementReach.clicks', 0))],
            ['CTR', number_format((float) data_get($report, 'announcementReach.ctr_pct', 0), 2).'%'],
        ]);

        $serverHealthRows = [
            ['Server Status', data_get($report, 'serverHealth.status', 'Unavailable')],
            ['CPU Usage', data_get($report, 'serverHealth.cpu_usage', '--')],
            ['Memory Usage', data_get($report, 'serverHealth.memory_usage', '--')],
            ['Last Updated', data_get($report, 'serverHealth.last_updated', '--')],
        ];
        if ((string) data_get($report, 'serverHealth.status', 'Unavailable') === 'Unavailable') {
            $serverHealthRows[] = ['Note', data_get($report, 'serverHealth.message', 'Server health data is temporarily unavailable.')];
        }
        $addSection('Part 6: Server Health', $serverHealthRows);

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $payload = $this->decodeExportPayload($request);
        $report = $this->buildExportReport($request, $payload);

        if ($request->boolean('download_pdf')) {
            return $this->downloadPdfReport($report);
        }

        return view('superadmin.analytics.print', [
            'data' => $report['data'],
            'feedback' => $report['feedback'],
            'uploads' => $report['uploads'],
            'serverHealth' => $report['serverHealth'],
            'announcementReach' => $report['announcementReach'],
            'start' => $report['start'],
            'end' => $report['end'],
            'generatedAt' => $report['generatedAt'],
            'downloadPayload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function decodeExportPayload(Request $request): array
    {
        $payload = json_decode((string) $request->input('payload', ''), true);

        return is_array($payload) ? $payload : [];
    }

    private function buildExportReport(Request $request, array $payload): array
    {
        return [
            'data' => [
                'total_visitors' => data_get($payload, 'kpis.total_visitors', $request->input('total_visitors', 0)),
                'avg_duration' => data_get($payload, 'kpis.avg_duration', $request->input('avg_duration', '0m 0s')),
                'bounce_rate' => data_get($payload, 'kpis.bounce_rate', $request->input('bounce_rate', '0%')),
                'total_uploads' => data_get($payload, 'upload_analytics.total_uploads', 0),
                'sessions' => data_get($payload, 'user_engagement.sessions', $request->input('sessions', 0)),
                'pageviews' => data_get($payload, 'user_engagement.pageviews', $request->input('pageviews', 0)),
                'pages_per_session' => data_get($payload, 'user_engagement.pages_per_session', $request->input('pages_per_session', 0)),
            ],
            'feedback' => data_get($payload, 'feedback_results', $this->feedbackDefaults()),
            'uploads' => data_get($payload, 'upload_analytics', $this->uploadDefaults()),
            'serverHealth' => $this->resolveExportServerHealth($payload),
            'announcementReach' => data_get($payload, 'announcement_reach', [
                'views' => 0,
                'unique_viewers' => 0,
                'clicks' => 0,
                'ctr_pct' => 0,
            ]),
            'start' => $request->input('start', ''),
            'end' => $request->input('end', ''),
            'generatedAt' => now()->format('F d, Y h:i A'),
        ];
    }

    private function resolveExportServerHealth(array $payload): array
    {
        $serverHealth = data_get($payload, 'server_health');

        if (! is_array($serverHealth)) {
            $serverHealth = $this->cloudWatchService->getServerHealth();
        }

        return $this->normalizeServerHealth($serverHealth);
    }

    private function normalizeServerHealth(array $serverHealth): array
    {
        $status = trim((string) data_get($serverHealth, 'status', ''));
        $lastUpdated = trim((string) data_get($serverHealth, 'last_updated', ''));
        $message = trim((string) data_get($serverHealth, 'message', ''));

        return [
            'status' => $status !== '' ? $status : 'Unavailable',
            'cpu_usage' => $this->formatServerHealthMetric(data_get($serverHealth, 'cpu_usage')),
            'memory_usage' => $this->formatServerHealthMetric(data_get($serverHealth, 'memory_usage')),
            'last_updated' => $lastUpdated !== '' ? $lastUpdated : '--',
            'message' => $message !== '' ? $message : 'Server health data is temporarily unavailable.',
        ];
    }

    private function formatServerHealthMetric(mixed $value): string
    {
        if (is_numeric($value)) {
            return round((float) $value).'%';
        }

        $text = trim((string) $value);

        if ($text === '') {
            return '--';
        }

        if (str_ends_with($text, '%')) {
            return $text;
        }

        return is_numeric($text) ? round((float) $text).'%' : $text;
    }

    private function downloadPdfReport(array $report)
    {
        $pdf = (new AnalyticsPdfBuilder())->build($report);
        $filename = 'analytics_report_'.now()->format('Ymd_His').'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($pdf),
        ]);
    }

    private function writeCsvMetricSection($output, string $title, array $rows): void
    {
        fputcsv($output, [$title]);
        fputcsv($output, ['Metric', 'Value']);

        foreach ($rows as [$label, $value]) {
            fputcsv($output, [$label, $value]);
        }

        fputcsv($output, []);
    }

    private function buildAnalyticsPayload(Carbon $startAt, Carbon $endAt): array
    {
        $sessionRows = collect();
        $prevSessionRows = collect();
        $diffInDays = max(1, $startAt->diffInDays($endAt));
        $prevStartAt = $startAt->copy()->subDays($diffInDays);
        $prevEndAt = $startAt->copy();

        if ($this->hasAnalyticsSchema()) {
            $sessionRows = DB::table('analytics_sessions')
                ->select('visitor_id', 'pageviews_count', 'started_at', 'last_activity_at')
                ->whereBetween('started_at', [$startAt, $endAt])
                ->get();

            $prevSessionRows = DB::table('analytics_sessions')
                ->select('visitor_id', 'pageviews_count', 'started_at', 'last_activity_at')
                ->whereBetween('started_at', [$prevStartAt, $prevEndAt])
                ->get();
        }

        $calcMetrics = function($rows) {
            $sessions = $rows->count();
            $totalVisitors = $rows->pluck('visitor_id')->filter()->unique()->count();
            $pageviews = (int) $rows->sum(fn ($r) => (int) ($r->pageviews_count ?? 0));
            $totalDur = (int) $rows->sum(fn ($r) => $this->estimateSessionDurationSec($r));
            $avgDur = (int) round($totalDur / max(1, $sessions));
            $bounce = $rows->filter(fn ($r) => (int) ($r->pageviews_count ?? 0) <= 1)->count();
            $bounceRate = round(($bounce / max(1, $sessions)) * 100, 2);
            $pagesPerSession = round($pageviews / max(1, $sessions), 2);
            return [$totalVisitors, $avgDur, $bounceRate, $sessions, $pageviews, $pagesPerSession];
        };

        [$cVisitors, $cAvgDur, $cBounce, $cSessions, $cPageviews, $cPagesPer] = $calcMetrics($sessionRows);
        [$pVisitors, $pAvgDur, $pBounce, $pSessions, $pPageviews, $pPagesPer] = $calcMetrics($prevSessionRows);

        $calcTrend = function($curr, $prev) {
            if ($prev == 0) return $curr > 0 ? 100 : 0;
            return round((($curr - $prev) / $prev) * 100, 1);
        };

        $buckets = [];
        $step = max(1, $diffInDays / 10);
        for ($i = 0; $i < 10; $i++) {
            $bStart = $startAt->copy()->addDays($i * $step);
            $bEnd = $i === 9 ? $endAt->copy() : $startAt->copy()->addDays(($i + 1) * $step);
            $bRows = $sessionRows->filter(function ($r) use ($bStart, $bEnd) {
                $dt = Carbon::parse($r->started_at);
                return $dt >= $bStart && $dt <= $bEnd;
            });
            $buckets[] = $calcMetrics($bRows);
        }

        $buildSvg = function($idx, $maxHeight = 30) use ($buckets) {
            $vals = array_column($buckets, $idx);
            $max = max($vals);
            $min = min($vals);
            $range = $max - $min;
            if ($range == 0) $range = 1;
            $pts = [];
            foreach ($vals as $i => $v) {
                $x = round($i * (100 / 9), 1);
                $y = round($maxHeight - (($v - $min) / $range) * ($maxHeight * 0.7), 1);
                $pts[] = "{$x},{$y}";
            }
            return 'M' . implode(' L', $pts);
        };

        $feedbackResults = $this->resolveFeedbackResults($startAt, $endAt);
        $uploadAnalytics = $this->resolveUploadAnalytics($startAt, $endAt);
        $prevUploadAnalytics = $this->resolveUploadAnalytics($prevStartAt, $prevEndAt);
        $cUploads = $uploadAnalytics['total_uploads'] ?? 0;
        $pUploads = $prevUploadAnalytics['total_uploads'] ?? 0;

        return [
            'kpis' => [
                'total_visitors' => (int) $cVisitors,
                'avg_session_duration_sec' => $cAvgDur,
                'bounce_rate_pct' => $cBounce,
            ],
            'user_engagement' => [
                'sessions' => (int) $cSessions,
                'pageviews' => $cPageviews,
                'pages_per_session' => $cPagesPer,
            ],
            'trends' => [
                'total_visitors' => $calcTrend($cVisitors, $pVisitors),
                'avg_session_duration' => $calcTrend($cAvgDur, $pAvgDur),
                'bounce_rate' => $calcTrend($cBounce, $pBounce),
                'total_uploads' => $calcTrend($cUploads, $pUploads),
                'sessions' => $calcTrend($cSessions, $pSessions),
                'pageviews' => $calcTrend($cPageviews, $pPageviews),
                'pages_per_session' => $calcTrend($cPagesPer, $pPagesPer),
            ],
            'svgs' => [
                'total_visitors' => $buildSvg(0, 30),
                'avg_session_duration' => $buildSvg(1, 30),
                'bounce_rate' => $buildSvg(2, 30),
                'total_uploads' => $buildSvg(0, 30),
                'sessions' => $buildSvg(3, 40),
                'pageviews' => $buildSvg(4, 40),
                'pages_per_session' => $buildSvg(5, 40),
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
