<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\IncidentResponseService;

class ActiveSecurityTracker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Concatenate full URL and request payload for scanning
        $input = urldecode($request->fullUrl() . ' ' . json_encode($request->all()));
        
        // 1. SQL Injection Patterns
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/waitfor\s+delay/i',
            '/xp_cmdshell/i',
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->recordAndAbort($request, 'SQL_INJECTION', 'Detected SQL Injection attempt');
            }
        }

        // 2. XSS Patterns
        $xssPatterns = [
            '/<script\b[^>]*>/i',
            '/javascript:/i',
            '/onerror\s*=/i',
            '/onload\s*=/i',
            '/eval\s*\(/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->recordAndAbort($request, 'XSS_ATTEMPT', 'Detected Cross-Site Scripting (XSS) attempt');
            }
        }

        // 3. Malicious Payload / Path Traversal
        $maliciousPatterns = [
            '/\.\.\//',
            '/\.\.%2f/i',
            '/etc\/passwd/i',
            '/cmd\.exe/i',
            '/\/bin\/bash/i',
            '/\/bin\/sh/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->recordAndAbort($request, 'MALICIOUS_PAYLOAD', 'Detected Malicious Payload or Directory Traversal');
            }
        }

        return $next($request);
    }

    /**
     * Record the security failure and abort the request.
     */
    private function recordAndAbort(Request $request, string $eventType, string $description)
    {
        // Try to get user email if authenticated, otherwise fallback to 'unknown'
        $email = 'unknown';
        if ($request->user()) {
            $email = $request->user()->email;
        }

        IncidentResponseService::recordFailure($request->ip(), $email, $eventType, $description);
        
        abort(403, 'Forbidden: Suspicious activity detected.');
    }
}
