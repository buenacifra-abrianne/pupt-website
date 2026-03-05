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
        if (! $this->hasAnalyticsSchema()) {
            return $this->emptyAnalyticsPayload();
        }

        $sessionRows = DB::table('analytics_sessions')
            ->select('visitor_id', 'pageviews_count', 'started_at', 'last_activity_at')
            ->whereBetween('started_at', [$startAt, $endAt])
            ->get();

        $sessions = $sessionRows->count();

        if ($sessions === 0) {
            return $this->emptyAnalyticsPayload();
        }

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
            'announcement_reach' => [
                'views' => 0,
                'unique_viewers' => 0,
                'clicks' => 0,
                'ctr_pct' => 0,
            ],
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
}

