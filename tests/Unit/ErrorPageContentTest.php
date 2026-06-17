<?php

namespace Tests\Unit;

use App\Support\ErrorPageContent;
use PHPUnit\Framework\TestCase;

class ErrorPageContentTest extends TestCase
{
    public function test_it_returns_expected_messages_for_common_statuses(): void
    {
        $this->assertSame([
            'headline' => 'Authentication Required',
            'message' => 'You need to sign in to access this page.',
        ], ErrorPageContent::forStatus(401));

        $this->assertSame([
            'headline' => 'Access Restricted',
            'message' => 'You do not have permission to access this page.',
        ], ErrorPageContent::forStatus(403));

        $this->assertSame([
            'headline' => 'Page Not Found',
            'message' => 'Page not found.',
        ], ErrorPageContent::forStatus(404));

        $this->assertSame([
            'headline' => 'Session Expired',
            'message' => 'Your session has expired. Please log in again.',
        ], ErrorPageContent::forStatus(419));

        $this->assertSame([
            'headline' => 'Too Many Requests',
            'message' => 'Too many requests. Please wait a moment and try again.',
        ], ErrorPageContent::forStatus(429));

        $this->assertSame([
            'headline' => 'Something Went Wrong',
            'message' => 'Something went wrong on our side. Please try again later.',
        ], ErrorPageContent::forStatus(500));

        $this->assertSame([
            'headline' => 'Service Unavailable',
            'message' => 'This service is temporarily unavailable. Please try again later.',
        ], ErrorPageContent::forStatus(503));
    }

    public function test_it_falls_back_to_safe_generic_messages(): void
    {
        $this->assertSame([
            'headline' => 'Request Unavailable',
            'message' => 'We could not complete your request. Please try again.',
        ], ErrorPageContent::forStatus(422));

        $this->assertSame([
            'headline' => 'Something Went Wrong',
            'message' => 'Something went wrong on our side. Please try again later.',
        ], ErrorPageContent::forStatus(520));
    }
}
