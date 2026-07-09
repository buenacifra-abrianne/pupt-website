<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class TrackCmsChanges
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If it's a mutating request and successful, update the cache
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Usually redirects (3xx) are returned after successful POSTs
            // Successful responses are 2xx
            if ($response->isSuccessful() || $response->isRedirect()) {
                $path = $request->path();
                // Exclude auth-related and profile modifications from triggering a global CMS refresh
                if (!str_contains($path, 'logout') && !str_contains($path, 'profile') && !str_contains($path, 'password') && !str_contains($path, 'login')) {
                    Cache::put('cms_last_updated', time());
                }
            }
        }

        return $response;
    }
}
