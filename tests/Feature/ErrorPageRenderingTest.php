<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPageRenderingTest extends TestCase
{
    public function test_known_http_errors_use_the_standardized_error_page(): void
    {
        Route::get('/_test/error-page/404', static function () {
            abort(404);
        });

        $response = $this->get('/_test/error-page/404');

        $response->assertStatus(404);
        $response->assertSee('404', false);
        $response->assertSee('Page not found.');
        $response->assertSee('Go Back to Homepage');
        $response->assertSee(route('public.landing'));
    }

    public function test_session_expired_errors_use_the_friendly_message(): void
    {
        Route::get('/_test/error-page/419', static function () {
            abort(419);
        });

        $response = $this->get('/_test/error-page/419');

        $response->assertStatus(419);
        $response->assertSee('419', false);
        $response->assertSee('Your session has expired. Please log in again.');
        $response->assertSee('Go Back to Homepage');
    }

    public function test_unexpected_exceptions_hide_raw_exception_messages(): void
    {
        Route::get('/_test/error-page/500', static function () {
            throw new Exception('Top secret exception message');
        });

        $response = $this->get('/_test/error-page/500');

        $response->assertStatus(500);
        $response->assertSee('500', false);
        $response->assertSee('Something went wrong on our side. Please try again later.');
        $response->assertSee('Go Back to Homepage');
        $response->assertDontSee('Top secret exception message');
    }
}
