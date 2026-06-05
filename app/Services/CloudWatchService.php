<?php

namespace App\Services;

use Aws\CloudWatch\CloudWatchClient;
use Aws\Lightsail\LightsailClient;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudWatchService
{
    private const CACHE_TTL_SECONDS = 60;

    private ?CloudWatchClient $client = null;

    private ?LightsailClient $lightsailClient = null;

    public function getServerHealth(): array
    {
        return Cache::remember(
            $this->cacheKey(),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            fn () => $this->fetchServerHealth()
        );
    }

    private function fetchServerHealth(): array
    {
        $fetchedAt = now();
        $instanceId = $this->resolveInstanceId();

        try {
            $cpuUsage = $this->fetchCpuUsage($instanceId);

            $memoryUsage = $this->fetchMemoryUsage($instanceId);

            if ($cpuUsage === null || $memoryUsage === null) {
                Log::warning('CloudWatch server health metrics were unavailable.', [
                    'instance_id' => $instanceId,
                    'cpu_usage' => $cpuUsage,
                    'memory_usage' => $memoryUsage,
                ]);

                return $this->fallbackPayload('Server health data is temporarily unavailable.');
            }

            $cpuUsage = $this->formatUsage($cpuUsage);
            $memoryUsage = $this->formatUsage($memoryUsage);

            return [
                'status' => $this->calculateStatus($cpuUsage, $memoryUsage),
                'cpu_usage' => $cpuUsage,
                'memory_usage' => $memoryUsage,
                'last_updated' => $fetchedAt->format('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            Log::error('Unable to retrieve CloudWatch server health metrics.', [
                'instance_id' => $instanceId,
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackPayload('Server health data is temporarily unavailable.');
        }
    }

    private function fetchCpuUsage(?string $instanceId): ?float
    {
        $namespace = (string) config('services.cloudwatch.cpu_namespace', 'AWS/EC2');
        $metricName = (string) config('services.cloudwatch.cpu_metric', 'CPUUtilization');
        $configuredDimensions = $this->parseConfiguredDimensions(
            config('services.cloudwatch.cpu_dimensions')
        );

        if ($this->isLightsailNamespace($namespace)) {
            return $this->fetchLightsailCpuUsage($metricName, $configuredDimensions);
        }

        if ($configuredDimensions !== []) {
            return $this->fetchLatestMetricValue($namespace, $metricName, $configuredDimensions);
        }

        if ($instanceId === null) {
            Log::warning('CloudWatch CPU metric fetch skipped because no CPU dimensions were configured and the instance ID could not be resolved.');

            return null;
        }

        return $this->fetchLatestMetricValue($namespace, $metricName, [
            ['Name' => 'InstanceId', 'Value' => $instanceId],
        ]);
    }

    private function fetchLightsailCpuUsage(string $metricName, array $configuredDimensions): ?float
    {
        $instanceName = $this->findDimensionValue($configuredDimensions, 'InstanceName');

        if ($instanceName === null) {
            Log::warning('Lightsail CPU metric fetch skipped because AWS_CLOUDWATCH_CPU_DIMENSIONS does not include InstanceName.');

            return null;
        }

        $endTime = now();
        $startTime = now()->subMinutes(15);

        try {
            $result = $this->lightsailClient()->getInstanceMetricData([
                'instanceName' => $instanceName,
                'metricName' => $metricName,
                'period' => 300,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'unit' => 'Percent',
                'statistics' => ['Average'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Lightsail CPU metric request failed.', [
                'instance_name' => $instanceName,
                'metric' => $metricName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $datapoints = collect($result->get('metricData') ?? []);

        if ($datapoints->isEmpty()) {
            return null;
        }

        $latest = $datapoints
            ->sortByDesc(fn (array $datapoint) => $this->timestampToUnix($datapoint['timestamp'] ?? null))
            ->first();

        $value = $latest['average'] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    private function fetchMemoryUsage(?string $instanceId): ?float
    {
        $namespace = (string) config('services.cloudwatch.cwagent_namespace', 'CWAgent');
        $metricName = (string) config('services.cloudwatch.memory_metric', 'mem_used_percent');
        $configuredDimensions = $this->parseConfiguredDimensions(
            config('services.cloudwatch.memory_dimensions')
        );

        if ($configuredDimensions !== []) {
            return $this->fetchLatestMetricValue($namespace, $metricName, $configuredDimensions);
        }

        if ($instanceId === null) {
            Log::warning('CloudWatch memory metric fetch skipped because no memory dimensions were configured and the instance ID could not be resolved.');

            return null;
        }

        $dimensionSets = $this->discoverMemoryMetricDimensions($namespace, $metricName, $instanceId);

        foreach ($dimensionSets as $dimensions) {
            $value = $this->fetchLatestMetricValue($namespace, $metricName, $dimensions);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function discoverMemoryMetricDimensions(string $namespace, string $metricName, string $instanceId): array
    {
        $metrics = [];
        $nextToken = null;

        do {
            $result = $this->client()->listMetrics(array_filter([
                'Namespace' => $namespace,
                'MetricName' => $metricName,
                'Dimensions' => [
                    ['Name' => 'InstanceId', 'Value' => $instanceId],
                ],
                'NextToken' => $nextToken,
            ], fn ($value) => $value !== null));

            $metrics = array_merge($metrics, $result->get('Metrics') ?? []);
            $nextToken = $result->get('NextToken');
        } while ($nextToken);

        return collect($metrics)
            ->map(fn (array $metric) => $metric['Dimensions'] ?? [])
            ->filter(fn (array $dimensions) => $dimensions !== [])
            ->sortByDesc(fn (array $dimensions) => count($dimensions))
            ->unique(fn (array $dimensions) => json_encode($dimensions))
            ->values()
            ->all();
    }

    private function fetchLatestMetricValue(string $namespace, string $metricName, array $dimensions): ?float
    {
        $period = max(60, (int) config('services.cloudwatch.period', 300));
        $lookbackMinutes = max(5, (int) config('services.cloudwatch.lookback_minutes', 10));
        $endTime = now();
        $startTime = now()->subMinutes($lookbackMinutes);

        try {
            $result = $this->client()->getMetricStatistics([
                'Namespace' => $namespace,
                'MetricName' => $metricName,
                'Dimensions' => $dimensions,
                'StartTime' => $startTime,
                'EndTime' => $endTime,
                'Period' => $period,
                'Statistics' => ['Average'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('CloudWatch metric request failed.', [
                'namespace' => $namespace,
                'metric' => $metricName,
                'dimensions' => $dimensions,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $datapoints = collect($result->get('Datapoints') ?? []);

        if ($datapoints->isEmpty()) {
            return null;
        }

        $latest = $datapoints
            ->sortByDesc(fn (array $datapoint) => $this->timestampToUnix($datapoint['Timestamp'] ?? null))
            ->first();

        $value = $latest['Average'] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    private function calculateStatus(int $cpuUsage, int $memoryUsage): string
    {
        if ($cpuUsage >= 90 || $memoryUsage >= 90) {
            return 'Critical';
        }

        if ($cpuUsage >= 70 || $memoryUsage >= 80) {
            return 'Warning';
        }

        return 'Healthy';
    }

    private function fallbackPayload(string $message): array
    {
        return [
            'status' => 'Unavailable',
            'cpu_usage' => null,
            'memory_usage' => null,
            'last_updated' => null,
            'message' => $message,
        ];
    }

    private function formatUsage(float $value): int
    {
        return (int) round(max(0, min(100, $value)));
    }

    private function cacheKey(): string
    {
        $instanceId = $this->resolveInstanceId();
        if ($instanceId !== null) {
            return 'analytics.server_health.'.$instanceId;
        }

        return 'analytics.server_health.default';
    }

    private function resolveInstanceId(): ?string
    {
        static $resolved = false;
        static $instanceId = null;

        if ($resolved) {
            return $instanceId;
        }

        $resolved = true;

        $configured = trim((string) config('services.cloudwatch.instance_id', ''));
        if ($configured !== '') {
            $instanceId = $configured;

            return $instanceId;
        }

        try {
            $tokenResponse = Http::connectTimeout(1)
                ->timeout(2)
                ->withHeaders([
                    'X-aws-ec2-metadata-token-ttl-seconds' => '21600',
                ])
                ->put('http://169.254.169.254/latest/api/token');

            $token = $tokenResponse->successful()
                ? trim((string) $tokenResponse->body())
                : '';

            $request = Http::connectTimeout(1)->timeout(2);
            if ($token !== '') {
                $request = $request->withHeaders([
                    'X-aws-ec2-metadata-token' => $token,
                ]);
            }

            $response = $request->get('http://169.254.169.254/latest/meta-data/instance-id');

            if ($response->successful()) {
                $candidate = trim((string) $response->body());
                if ($candidate !== '') {
                    $instanceId = $candidate;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Unable to resolve EC2 instance ID from instance metadata.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $instanceId;
    }

    private function parseConfiguredDimensions(mixed $rawDimensions): array
    {
        if (is_array($rawDimensions)) {
            return $this->normalizeDimensions($rawDimensions);
        }

        $value = trim((string) $rawDimensions);
        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->normalizeDimensions($decoded);
        }

        $dimensions = [];
        foreach (explode(',', $value) as $pair) {
            [$name, $dimensionValue] = array_pad(explode('=', $pair, 2), 2, null);
            $name = trim((string) $name);
            $dimensionValue = trim((string) $dimensionValue);

            if ($name === '' || $dimensionValue === '') {
                continue;
            }

            $dimensions[] = [
                'Name' => $name,
                'Value' => $dimensionValue,
            ];
        }

        return $dimensions;
    }

    private function normalizeDimensions(array $dimensions): array
    {
        return collect($dimensions)
            ->map(function (mixed $dimension) {
                if (! is_array($dimension)) {
                    return null;
                }

                $name = trim((string) ($dimension['Name'] ?? $dimension['name'] ?? ''));
                $value = trim((string) ($dimension['Value'] ?? $dimension['value'] ?? ''));

                if ($name === '' || $value === '') {
                    return null;
                }

                return [
                    'Name' => $name,
                    'Value' => $value,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function findDimensionValue(array $dimensions, string $name): ?string
    {
        foreach ($dimensions as $dimension) {
            $dimensionName = trim((string) ($dimension['Name'] ?? $dimension['name'] ?? ''));
            $dimensionValue = trim((string) ($dimension['Value'] ?? $dimension['value'] ?? ''));

            if ($dimensionValue === '') {
                continue;
            }

            if (strcasecmp($dimensionName, $name) === 0) {
                return $dimensionValue;
            }
        }

        return null;
    }

    private function isLightsailNamespace(string $namespace): bool
    {
        return strcasecmp(trim($namespace), 'AWS/Lightsail') === 0;
    }

    private function timestampToUnix(mixed $timestamp): int
    {
        if ($timestamp instanceof CarbonInterface) {
            return $timestamp->getTimestamp();
        }

        if ($timestamp instanceof \DateTimeInterface) {
            return $timestamp->getTimestamp();
        }

        if (is_numeric($timestamp)) {
            return (int) $timestamp;
        }

        try {
            return $timestamp ? Carbon::parse($timestamp)->getTimestamp() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function client(): CloudWatchClient
    {
        if ($this->client instanceof CloudWatchClient) {
            return $this->client;
        }

        $this->client = new CloudWatchClient([
            'version' => (string) config('services.cloudwatch.version', 'latest'),
            'region' => (string) config(
                'services.cloudwatch.region',
                config('services.ses.region', env('AWS_DEFAULT_REGION', 'us-east-1'))
            ),
        ]);

        return $this->client;
    }

    private function lightsailClient(): LightsailClient
    {
        if ($this->lightsailClient instanceof LightsailClient) {
            return $this->lightsailClient;
        }

        $this->lightsailClient = new LightsailClient([
            'version' => (string) config('services.cloudwatch.version', 'latest'),
            'region' => (string) config(
                'services.cloudwatch.region',
                config('services.ses.region', env('AWS_DEFAULT_REGION', 'us-east-1'))
            ),
        ]);

        return $this->lightsailClient;
    }
}
