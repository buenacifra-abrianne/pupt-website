<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function adminApi(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        // If your analytics tables differ, we return safe zeros for now.
        // We'll align this to your real analytics schema next.
        $payload = [
            'ok' => true,
            'kpis' => [
                'total_visitors' => 0,
                'avg_session_duration_sec' => 0,
                'bounce_rate_pct' => 0,
            ],
            'user_engagement' => [
                'sessions' => 0,
                'pageviews' => 0,
                'pages_per_session' => 0,
                'returning_rate_pct' => 0,
            ],
            'announcement_reach' => [
                'views' => 0,
                'unique_viewers' => 0,
                'clicks' => 0,
                'ctr_pct' => 0,
            ]
        ];

        return response()->json($payload);
    }

    public function exportExcel(Request $request)
{
    $filename="analytics_report_".now()->format('Ymd_His').".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $output=fopen("php://output","w");

    fputcsv($output,['Metric','Value']);

    fputcsv($output,['Total Visitors',$request->total_visitors]);
    fputcsv($output,['Avg Session Duration',$request->avg_duration]);
    fputcsv($output,['Bounce Rate',$request->bounce_rate]);

    fputcsv($output,['Sessions',$request->sessions]);
    fputcsv($output,['Pageviews',$request->pageviews]);
    fputcsv($output,['Pages per Session',$request->pages_per_session]);
    fputcsv($output,['Returning Visitors',$request->returning_rate]);

    fclose($output);
    exit;
}


public function exportPdf(Request $request)
{
    $data = [
        'total_visitors' => $request->total_visitors ?? 0,
        'avg_duration' => $request->avg_duration ?? '0m 0s',
        'bounce_rate' => $request->bounce_rate ?? '0%',

        'sessions' => $request->sessions ?? 0,
        'pageviews' => $request->pageviews ?? 0,
        'pages_per_session' => $request->pages_per_session ?? 0,
        'returning_rate' => $request->returning_rate ?? '0%',
    ];

    return view('faculty.analytics.print', [
        'data' => $data,
        'start' => $request->start ?? '',
        'end' => $request->end ?? ''
    ]);
}

private function buildAnalyticsPayload($start = null, $end = null)
{
    // temporary data so export works even if analytics DB is empty
    return [
        'kpis' => [
            'total_visitors' => 0,
            'avg_session_duration_sec' => 0,
            'bounce_rate_pct' => 0
        ],

        'user_engagement' => [
            'sessions' => 0,
            'pageviews' => 0,
            'pages_per_session' => 0,
            'returning_rate_pct' => 0
        ],

        'announcement_reach' => [
            'views' => 0,
            'unique_viewers' => 0,
            'clicks' => 0,
            'ctr_pct' => 0
        ]
    ];
}
}