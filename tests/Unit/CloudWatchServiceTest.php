<?php

namespace Tests\Unit;

use App\Services\CloudWatchService;
use Aws\Lightsail\LightsailClient;
use Aws\Result;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CloudWatchServiceTest extends TestCase
{
    public function test_it_fetches_cpu_usage_from_lightsail_metric_data(): void
    {
        config()->set('services.cloudwatch.cpu_namespace', 'AWS/Lightsail');
        config()->set('services.cloudwatch.cpu_metric', 'CPUUtilization');
        config()->set('services.cloudwatch.cpu_dimensions', 'InstanceName=puptweb-server');

        $lightsail = Mockery::mock(LightsailClient::class);
        $lightsail->shouldReceive('getInstanceMetricData')
            ->once()
            ->with(Mockery::on(function (array $payload) {
                return ($payload['instanceName'] ?? null) === 'puptweb-server'
                    && ($payload['metricName'] ?? null) === 'CPUUtilization'
                    && ($payload['period'] ?? null) === 300
                    && ($payload['unit'] ?? null) === 'Percent'
                    && ($payload['statistics'] ?? []) === ['Average'];
            }))
            ->andReturn(new Result([
                'metricData' => [
                    [
                        'average' => 12.5,
                        'timestamp' => now()->subMinutes(10)->getTimestamp(),
                        'unit' => 'Percent',
                    ],
                    [
                        'average' => 36.25,
                        'timestamp' => now()->subMinutes(5)->getTimestamp(),
                        'unit' => 'Percent',
                    ],
                ],
                'metricName' => 'CPUUtilization',
            ]));

        $service = new CloudWatchService();

        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('lightsailClient');
        $property->setAccessible(true);
        $property->setValue($service, $lightsail);

        $method = $reflection->getMethod('fetchCpuUsage');
        $method->setAccessible(true);

        $value = $method->invoke($service, null);

        $this->assertSame(36.25, $value);
    }
}
