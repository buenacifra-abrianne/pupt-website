<?php

namespace App\Services\Idp;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;
use UnexpectedValueException;

class IdpDirectoryService
{
    private const MAX_PAGES = 200;

    public function getActiveUsersForDropdown(string $accessToken): array
    {
        $baseUrl = rtrim((string) config('services.idp.base_url'), '/');
        $source = hash('sha256', $baseUrl);
        // Old cache entries may contain partial responses. Only use complete new snapshots.
        $cacheKey = 'idp_directory_complete:'.$source;

        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && isset($cached['data'], $cached['cached_at'])) {
                return $this->result($cached['data'], true, $cached['cached_at']);
            }
        } catch (Throwable $exception) {
            Log::warning('IDP directory temporary cache could not be read.', ['exception' => $exception::class]);
        }

        try {
            if ($baseUrl === '') {
                throw new UnexpectedValueException('IDP base URL is not configured.');
            }

            $users = $this->fetchCompleteDirectory($baseUrl, $accessToken);
        } catch (Throwable $exception) {
            Log::warning('IDP directory unavailable; reading the last complete saved directory.', ['exception' => $exception::class]);

            return $this->fallback($source);
        }

        $fetchedAt = now()->toIso8601String();
        try {
            if (! Schema::hasTable('idp_directory_snapshots')) {
                throw new UnexpectedValueException('IDP directory snapshot migration is required.');
            }
            DB::table('idp_directory_snapshots')->upsert([
                [
                    'source' => $source,
                    'base_url' => $baseUrl,
                    'users' => json_encode($users, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'fetched_at' => now(),
                ],
            ], ['source'], ['base_url', 'users', 'fetched_at']);
        } catch (Throwable $exception) {
            // A persistence problem must not discard a usable live response.
            Log::warning('The complete IDP directory could not be saved for outages.', ['exception' => $exception::class]);
        }

        try {
            Cache::put($cacheKey, ['data' => $users, 'cached_at' => $fetchedAt], now()->addMinutes(15));
        } catch (Throwable $exception) {
            Log::warning('IDP directory temporary cache could not be updated.', ['exception' => $exception::class]);
        }

        return $this->result($users, false, $fetchedAt);
    }

    private function fetchCompleteDirectory(string $baseUrl, string $accessToken): array
    {
        $users = [];
        $seen = [];
        $lastPage = null;
        $total = null;
        for ($page = 1; $page <= ($lastPage ?? 1); $page++) {
            $response = Http::withHeaders([
                'Cookie' => 'access_token='.$accessToken,
                'Authorization' => 'Bearer '.$accessToken,
                'Accept' => 'application/json',
            ])->connectTimeout(3)->timeout(10)->get($baseUrl.'/api/v1/admin/users', [
                'page' => $page,
                'limit' => 200,
            ]);

            if (! $response->successful()) {
                throw new UnexpectedValueException('IDP directory request failed.');
            }

            $payload = $response->json();
            if (! is_array($payload) || ($payload['success'] ?? true) === false || ($payload['ok'] ?? true) === false
                || ! isset($payload['users']) || ! is_array($payload['users'])
                || ! array_is_list($payload['users']) || (isset($payload['meta']) && ! is_array($payload['meta']))) {
                throw new UnexpectedValueException('Invalid IDP directory response.');
            }

            $reportedLast = $payload['meta']['last_page'] ?? $payload['last_page'] ?? 1;
            $reportedLast = filter_var($reportedLast, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => self::MAX_PAGES]]);
            $currentPage = $payload['meta']['current_page'] ?? $payload['current_page'] ?? $page;
            if ($reportedLast === false || ($lastPage !== null && $lastPage !== $reportedLast)
                || filter_var($currentPage, FILTER_VALIDATE_INT) !== $page) {
                throw new UnexpectedValueException('Invalid IDP directory pagination.');
            }
            $lastPage = $reportedLast;
            if ($lastPage > 1 && $payload['users'] === []) {
                throw new UnexpectedValueException('IDP directory page is incomplete.');
            }

            $reportedTotal = $payload['meta']['total'] ?? $payload['total'] ?? null;
            if ($reportedTotal !== null) {
                $reportedTotal = filter_var($reportedTotal, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
                if ($reportedTotal === false || ($total !== null && $total !== $reportedTotal)) {
                    throw new UnexpectedValueException('Invalid IDP directory total.');
                }
                $total = $reportedTotal;
            }

            foreach ($payload['users'] as $item) {
                if (! is_array($item)) {
                    throw new UnexpectedValueException('Invalid IDP directory user.');
                }
                $user = ['source' => 'IDP'];
                foreach (['oneportal_id' => 'id', 'first_name' => 'first_name', 'middle_name' => 'middle_name', 'last_name' => 'last_name', 'email' => 'email'] as $field => $apiField) {
                    $value = $item[$apiField] ?? '';
                    if (! is_scalar($value)) {
                        throw new UnexpectedValueException('Invalid IDP directory user field.');
                    }
                    $user[$field] = trim((string) $value);
                }
                $user['email'] = strtolower($user['email']);
                $identity = $user['oneportal_id'] !== '' ? 'id:'.$user['oneportal_id'] : 'email:'.$user['email'];
                if (($user['oneportal_id'] === '' && $user['email'] === '') || isset($seen[$identity])) {
                    throw new UnexpectedValueException('Missing or duplicate IDP directory identity.');
                }
                $seen[$identity] = true;
                $users[] = $user;
            }
        }

        if ($total !== null && count($users) !== $total) {
            throw new UnexpectedValueException('IDP directory is incomplete.');
        }

        return $users;
    }

    private function fallback(string $source): array
    {
        try {
            if (Schema::hasTable('idp_directory_snapshots')) {
                $snapshot = DB::table('idp_directory_snapshots')->where('source', $source)->first();
                if ($snapshot) {
                    $users = json_decode($snapshot->users, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($users) && array_is_list($users)) {
                        return $this->result($users, true, $snapshot->fetched_at, true);
                    }
                }
            }
        } catch (Throwable $exception) {
            Log::warning('The saved IDP directory could not be read.', ['exception' => $exception::class]);
        }

        return $this->result([], false, null, false, true);
    }

    private function result(array $users, bool $cached, ?string $cachedAt, bool $fallback = false, bool $unavailable = false): array
    {
        return [
            'data' => $users,
            'is_cached' => $cached,
            'cached_at' => $cachedAt,
            'is_fallback' => $fallback,
            'unavailable' => $unavailable,
        ];
    }
}
