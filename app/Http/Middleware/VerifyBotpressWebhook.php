<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBotpressWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedSecret = (string) config('services.botpress.webhook_secret');
        $providedSecret = (string) $request->header('X-BP-SECRET');

        if ($expectedSecret === '' || $providedSecret === '' || ! hash_equals($expectedSecret, $providedSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
