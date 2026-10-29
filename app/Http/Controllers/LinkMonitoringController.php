<?php

namespace App\Http\Controllers;

use App\Services\LinkMonitoringService;
use App\Support\CmsSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LinkMonitoringController extends Controller
{
    public function __invoke(Request $request, LinkMonitoringService $monitor)
    {
        abort_unless(in_array(CmsSections::normalizeRole((string) session('user_role')), ['SUPERADMIN', 'ADMIN'], true), 403);
        $key = 'link-monitoring:'.hash('sha256', $request->session()->getId());

        if ($request->isMethod('GET')) {
            return response()->json(['scan' => Cache::get($key)]);
        }

        $request->validate(['action' => 'required|in:start,step']);
        $lock = Cache::lock($key.':lock', 60);
        abort_unless($lock->get(), 409, 'A link check is already in progress.');
        try {
            $scan = $request->input('action') === 'start' ? $monitor->start() : Cache::get($key);
            abort_if($scan === null, 410, 'The scan expired. Start a new scan.');
            if ($request->input('action') === 'step') {
                $scan = $monitor->step($scan);
            }
            Cache::put($key, $scan, now()->addHours(2));

            return response()->json(['scan' => $scan]);
        } finally {
            $lock->release();
        }
    }
}
