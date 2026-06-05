<?php

namespace Tests\Feature;

use App\Services\CloudWatchService;
use Mockery;
use Tests\TestCase;

class AnalyticsServerHealthControllerTest extends TestCase
{
    public function test_guest_requests_are_redirected(): void
    {
        $response = $this->get('/api/analytics/server-health');

        $response->assertRedirect(route('public.landing'));
    }

    public function test_superadmin_can_fetch_server_health_metrics(): void
    {
        $payload = [
            'status' => 'Healthy',
            'cpu_usage' => 32,
            'memory_usage' => 58,
            'last_updated' => '2026-06-05 14:15:00',
        ];

        $mock = Mockery::mock(CloudWatchService::class);
        $mock->shouldReceive('getServerHealth')
            ->once()
            ->andReturn($payload);

        $this->app->instance(CloudWatchService::class, $mock);

        $response = $this
            ->withSession([
                'user_logged_in' => true,
                'user_role' => 'SUPERADMIN',
                'terms_accepted' => true,
            ])
            ->getJson('/api/analytics/server-health');

        $response->assertOk()->assertExactJson($payload);
    }

    public function test_superadmin_receives_graceful_fallback_when_metrics_are_unavailable(): void
    {
        $payload = [
            'status' => 'Unavailable',
            'cpu_usage' => null,
            'memory_usage' => null,
            'last_updated' => null,
            'message' => 'Server health data is temporarily unavailable.',
        ];

        $mock = Mockery::mock(CloudWatchService::class);
        $mock->shouldReceive('getServerHealth')
            ->once()
            ->andReturn($payload);

        $this->app->instance(CloudWatchService::class, $mock);

        $response = $this
            ->withSession([
                'user_logged_in' => true,
                'user_role' => 'SUPERADMIN',
                'terms_accepted' => true,
            ])
            ->getJson('/api/analytics/server-health');

        $response->assertOk()->assertExactJson($payload);
    }
}
