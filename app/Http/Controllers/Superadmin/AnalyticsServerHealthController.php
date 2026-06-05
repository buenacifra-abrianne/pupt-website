<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\CloudWatchService;
use Illuminate\Http\JsonResponse;

class AnalyticsServerHealthController extends Controller
{
    public function __construct(
        private readonly CloudWatchService $cloudWatchService
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json(
            $this->cloudWatchService->getServerHealth()
        );
    }
}
