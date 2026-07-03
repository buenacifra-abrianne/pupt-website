<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdpHealthChecker
{
    /**
     * Check if the IdP is online.
     * Returns true if 2xx or 3xx status, false otherwise.
     */
    public static function check(): bool
    {
        $idpUrl = config('services.idp.base_url');

        if (empty($idpUrl)) {
            return false;
        }

        try {
            // Fast HEAD request with 2 second timeout
            $response = Http::timeout(2)
                ->connectTimeout(2)
                ->head($idpUrl);

            return $response->successful() || $response->redirect();
        } catch (\Exception $e) {
            // Log the failure just in case, but keep it quiet for users
            Log::warning('IdP Health Check Failed: ' . $e->getMessage());
            return false;
        }
    }
}
