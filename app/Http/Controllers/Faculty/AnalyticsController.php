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
}