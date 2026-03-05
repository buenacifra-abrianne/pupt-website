<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackPublicAnalytics
{
    private const VISITOR_COOKIE = 'pup_visitor_id';
    private const SESSION_KEY = 'analytics_session_uuid';
    private const LAST_ACTIVITY_KEY = 'analytics_last_activity_at';
    private const SESSION_TIMEOUT_MINUTES = 30;

    private static ?bool $hasAnalyticsSchema = null;

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $this->shouldTrack($request, $response) || ! $this->hasAnalyticsSchema()) {
            return $response;
        }

        try {
            $now = now();
            $visitorId = $request->cookie(self::VISITOR_COOKIE);
            $mustSetVisitorCookie = false;

            if (! is_string($visitorId) || trim($visitorId) === '') {
                $visitorId = (string) Str::uuid();
                $mustSetVisitorCookie = true;
            }

            $sessionUuid = (string) $request->session()->get(self::SESSION_KEY, '');
            $lastActivityRaw = $request->session()->get(self::LAST_ACTIVITY_KEY);

            $startNewSession = $sessionUuid === '';
            if (! $startNewSession && $lastActivityRaw) {
                try {
                    $lastActivity = Carbon::parse($lastActivityRaw);
                    $startNewSession = $lastActivity->diffInMinutes($now) >= self::SESSION_TIMEOUT_MINUTES;
                } catch (\Throwable $e) {
                    $startNewSession = true;
                }
            }

            if ($startNewSession) {
                $sessionUuid = (string) Str::uuid();
                DB::table('analytics_sessions')->insert([
                    'session_uuid' => $sessionUuid,
                    'visitor_id' => $visitorId,
                    'started_at' => $now,
                    'last_activity_at' => $now,
                    'pageviews_count' => 1,
                    'entry_path' => '/'.$request->path(),
                    'last_path' => '/'.$request->path(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $updated = DB::table('analytics_sessions')
                    ->where('session_uuid', $sessionUuid)
                    ->update([
                        'last_activity_at' => $now,
                        'last_path' => '/'.$request->path(),
                        'pageviews_count' => DB::raw('pageviews_count + 1'),
                        'updated_at' => $now,
                    ]);

                if (! $updated) {
                    DB::table('analytics_sessions')->insert([
                        'session_uuid' => $sessionUuid,
                        'visitor_id' => $visitorId,
                        'started_at' => $now,
                        'last_activity_at' => $now,
                        'pageviews_count' => 1,
                        'entry_path' => '/'.$request->path(),
                        'last_path' => '/'.$request->path(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $request->session()->put(self::SESSION_KEY, $sessionUuid);
            $request->session()->put(self::LAST_ACTIVITY_KEY, $now->toDateTimeString());

            if ($mustSetVisitorCookie) {
                $response->headers->setCookie(
                    Cookie::make(
                        self::VISITOR_COOKIE,
                        $visitorId,
                        60 * 24 * 365 * 2,
                        '/',
                        config('session.domain'),
                        $request->isSecure(),
                        false,
                        false,
                        config('session.same_site', 'lax')
                    )
                );
            }
        } catch (\Throwable $e) {
            report($e); // Fail-safe: analytics collection should never break page rendering.
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        $routeName = (string) optional($request->route())->getName();

        return str_starts_with($routeName, 'public.') || $routeName === 'public.landing';
    }

    private function hasAnalyticsSchema(): bool
    {
        if (self::$hasAnalyticsSchema !== null) {
            return self::$hasAnalyticsSchema;
        }

        self::$hasAnalyticsSchema =
            Schema::hasTable('analytics_sessions')
            && Schema::hasColumns('analytics_sessions', [
                'session_uuid',
                'visitor_id',
                'started_at',
                'last_activity_at',
                'pageviews_count',
            ]);

        return self::$hasAnalyticsSchema;
    }
}
