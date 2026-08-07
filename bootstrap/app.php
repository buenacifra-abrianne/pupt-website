<?php

use Illuminate\Foundation\Application;
use App\Console\Commands\ScanLinksCommand;
use App\Console\Commands\SyncUrlCommand;
use App\Console\Commands\SyncBotpressCommand;
use App\Console\Commands\NormalizeHtmlEntityText;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use App\Support\ErrorPageContent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        NormalizeHtmlEntityText::class,
        ScanLinksCommand::class,
        SyncBotpressCommand::class,
        SyncUrlCommand::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\FirewallMiddleware::class,
            \App\Http\Middleware\ActiveSecurityTracker::class,
            \App\Http\Middleware\NormalizePlainTextEntities::class,
            \App\Http\Middleware\TrackPublicAnalytics::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\FirewallMiddleware::class,
            \App\Http\Middleware\ActiveSecurityTracker::class,
            \App\Http\Middleware\NormalizePlainTextEntities::class,
        ]);

        $middleware->alias([
            'superadmin.auth' => \App\Http\Middleware\SuperadminAuth::class,
            'superadmin.role' => \App\Http\Middleware\SuperadminRole::class,
            'nonsuperadmin.role' => \App\Http\Middleware\NonSuperadminRoleOnly::class,
            'cms.terms.accepted' => \App\Http\Middleware\EnsureCmsTermsAccepted::class,
            'idp.apikey' => \App\Http\Middleware\VerifyApiKey::class,
            'botpress.webhook' => \App\Http\Middleware\VerifyBotpressWebhook::class,
            'check.idp' => \App\Http\Middleware\CheckIdpSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $expectsJsonResponse = static function (Request $request): bool {
            return $request->expectsJson()
                || $request->ajax()
                || strtolower((string) $request->header('X-Requested-With')) === 'xmlhttprequest'
                || str_contains(strtolower((string) $request->header('Accept')), 'application/json');
        };

        $sessionExpiredResponse = static function () {
            return response()->json([
                'ok' => false,
                'message' => 'Your session has expired! Please log in again.',
                'session_expired' => true,
                'redirect' => route('public.landing'),
            ], 419);
        };

        $friendlyAttribute = static function (string $attribute): string {
            $attribute = trim($attribute);

            $labels = [
                'home.campus_tour.avp_video_file' => 'Campus AVP video',
            ];

            if (isset($labels[$attribute])) {
                return $labels[$attribute];
            }

            $segments = array_values(array_filter(
                preg_split('/[.]+/', $attribute) ?: [],
                static fn ($segment) => $segment !== '' && !ctype_digit((string) $segment)
            ));

            $segment = $segments !== [] ? end($segments) : $attribute;
            $segment = preg_replace('/_file$/', '', (string) $segment) ?? (string) $segment;
            $segment = str_replace(['_', '-'], ' ', $segment);

            return ucfirst(trim($segment)) ?: 'This field';
        };

        $friendlyValidationMessage = static function (string $attribute, string $message) use ($friendlyAttribute): string {
            $label = $friendlyAttribute($attribute);

            if ($message === 'File too large!') {
                return $message;
            }

            if (preg_match('/^The .+ field is required\\.$/i', $message)) {
                return $label.' is required.';
            }

            if (preg_match('/^The .+ field must be a file of type: (.+)\\.$/i', $message, $matches)) {
                $types = str_replace(['video/mp4', 'video/webm', 'video/quicktime'], ['MP4', 'WebM', 'MOV'], $matches[1]);
                return $label.' must be a '.$types.' file.';
            }

            if (preg_match('/^The .+ must not be greater than \\d+ kilobytes\\.$/i', $message)) {
                return $label.' is too large.';
            }

            if (preg_match('/^The .+ field must be an image\\.$/i', $message)) {
                return $label.' must be an image file.';
            }

            if (preg_match('/^The .+ field must be a file\\.$/i', $message)) {
                return 'Please upload a file for '.$label.'.';
            }

            return $message;
        };

        $exceptions->render(function (ValidationException $e, Request $request) use ($expectsJsonResponse, $friendlyValidationMessage) {
            if (!$expectsJsonResponse($request)) {
                return null;
            }

            $errors = [];
            foreach ($e->errors() as $attribute => $messages) {
                foreach ((array) $messages as $message) {
                    $errors[] = $friendlyValidationMessage((string) $attribute, (string) $message);
                }
            }

            $errors = array_values(array_unique(array_filter($errors)));
            $message = $errors !== []
                ? implode("\n", array_slice($errors, 0, 3))
                : 'Please review the highlighted fields and try again.';

            return response()->json([
                'ok' => false,
                'message' => $message,
                'errors' => $errors,
            ], $e->status);
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($expectsJsonResponse, $sessionExpiredResponse) {
            if (!$expectsJsonResponse($request)) {
                return null;
            }

            return $sessionExpiredResponse();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($expectsJsonResponse, $sessionExpiredResponse) {
            if (!$expectsJsonResponse($request)) {
                return null;
            }

            return $sessionExpiredResponse();
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($expectsJsonResponse, $sessionExpiredResponse) {
            if (!$expectsJsonResponse($request)) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status === 419) {
                return $sessionExpiredResponse();
            }

            if ($status === 403) {
                return response()->json([
                    'ok' => false,
                    'message' => 'You no longer have access to this page.',
                ], 403);
            }

            if ($status >= 500) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Something went wrong. Please try again later.',
                ], $status);
            }

            return response()->json([
                'ok' => false,
                'message' => 'We could not complete that request. Please try again.',
            ], max(400, $status));
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($expectsJsonResponse) {
            if ($expectsJsonResponse($request)) {
                return null;
            }

            if ($e instanceof ValidationException || $e instanceof AuthenticationException || $e instanceof HttpResponseException) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status < 400) {
                return null;
            }

            $content = ErrorPageContent::forStatus($status);

            return response()->view('errors.http', [
                'statusCode' => $status,
                'headline' => $content['headline'],
                'message' => $content['message'],
            ], $status);
        });
    })->create();
