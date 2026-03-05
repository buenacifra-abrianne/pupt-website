<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function adminApi(Request $request)
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

            'sessions' => data_get($payload, 'user_engagement.sessions', $request->input('sessions', 0)),
            'pageviews' => data_get($payload, 'user_engagement.pageviews', $request->input('pageviews', 0)),
            'pages_per_session' => data_get($payload, 'user_engagement.pages_per_session', $request->input('pages_per_session', 0)),
        ];

        return view('faculty.analytics.print', [
            'data' => $data,
            'start' => $request->input('start', ''),
            'end' => $request->input('end', ''),
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
        if (! $this->hasFeedbackSchema()) {
            return $this->feedbackDefaults();
        }

        $row = DB::table('feedback_submissions')
            ->selectRaw('COUNT(*) as total_responses')
            ->selectRaw('AVG(q1_score) as question_1_avg')
            ->selectRaw('AVG(q2_score) as question_2_avg')
            ->selectRaw('AVG(q3_score) as question_3_avg')
            ->selectRaw('AVG(q4_score) as question_4_avg')
            ->selectRaw('AVG(q5_score) as question_5_avg')
            ->selectRaw('AVG(q6_score) as question_6_avg')
            ->selectRaw('SUM(CASE WHEN overall_score >= 3.5 THEN 1 ELSE 0 END) as outstanding')
            ->selectRaw('SUM(CASE WHEN overall_score >= 2.5 AND overall_score < 3.5 THEN 1 ELSE 0 END) as very_satisfactory')
            ->selectRaw('SUM(CASE WHEN overall_score >= 1.5 AND overall_score < 2.5 THEN 1 ELSE 0 END) as satisfactory')
            ->selectRaw('SUM(CASE WHEN overall_score < 1.5 THEN 1 ELSE 0 END) as unsatisfactory')
            ->whereBetween('created_at', [$startAt, $endAt])
            ->first();

        if (! $row || (int) ($row->total_responses ?? 0) === 0) {
            return $this->feedbackDefaults();
        }

        $q1 = round((float) ($row->question_1_avg ?? 0), 2);
        $q2 = round((float) ($row->question_2_avg ?? 0), 2);
        $q3 = round((float) ($row->question_3_avg ?? 0), 2);
        $q4 = round((float) ($row->question_4_avg ?? 0), 2);
        $q5 = round((float) ($row->question_5_avg ?? 0), 2);
        $q6 = round((float) ($row->question_6_avg ?? 0), 2);

        $overallAverage = round(($q1 + $q2 + $q3 + $q4 + $q5 + $q6) / 6, 2);

        return [
            'total_responses' => (int) ($row->total_responses ?? 0),
            'question_1_avg' => $q1,
            'question_2_avg' => $q2,
            'question_3_avg' => $q3,
            'question_4_avg' => $q4,
            'question_5_avg' => $q5,
            'question_6_avg' => $q6,
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
            && Schema::hasColumns('feedback_submissions', [
                'q1_score',
                'q2_score',
                'q3_score',
                'q4_score',
                'q5_score',
                'q6_score',
                'overall_score',
                'created_at',
            ]);
    }
}
