<?php

namespace App\Http\Controllers;

use App\Services\IdpHealthChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Show the public index landing page.
     */
    public function index()
    {
        // Cache the IdP health status for 5 minutes (300 seconds)
        $isIdpOnline = Cache::remember('idp_health_status', 300, function () {
            return IdpHealthChecker::check();
        });

        return view('public.index', compact('isIdpOnline'));
    }
}
