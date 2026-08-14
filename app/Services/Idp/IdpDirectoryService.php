<?php

namespace App\Services\Idp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IdpDirectoryService
{
    /**
     * Fetch active users from the IdP directory for the dropdown.
     * It handles pagination to fetch all active users.
     */
    public function getActiveUsersForDropdown(string $accessToken): array
    {
        $cacheKey = 'idp_directory_users';

        if (Cache::has($cacheKey)) {
            return [
                'data'      => Cache::get($cacheKey),
                'is_cached' => true,
                'cached_at' => Cache::get($cacheKey . '_timestamp'),
            ];
        }

        $users = [];
        $page = 1;
        $limit = 10;
        $baseUrl = rtrim(env('IDP_BASE_URL', ''), '/');

        if (empty($baseUrl)) {
            Log::error('IdpDirectoryService: IDP_BASE_URL is not configured.');
            return [
                'data' => [],
                'is_cached' => false,
                'cached_at' => null,
            ];
        }

        try {
            Log::info('IdpDirectoryService: Fetching users with access_token: ' . ($accessToken ? 'PRESENT' : 'EMPTY'));
            do {
                $response = Http::withHeaders([
                        'Cookie' => 'access_token=' . $accessToken,
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ])
                    ->timeout(10)
                    ->get($baseUrl . '/api/v1/admin/users', [
                        'page' => $page,
                        'limit' => $limit,
                    ]);

                if (!$response->successful()) {
                    Log::error('IdpDirectoryService failed to fetch users. Status: ' . $response->status() . ' Body: ' . $response->body());
                    break;
                }

                $data = $response->json();
                Log::info('IdpDirectoryService Response: ' . json_encode($data));
                
                $items = $data['users'] ?? [];
                
                foreach ($items as $item) {
                    // Display everything EXCEPT Applicant, Guest, Student, Alumni
                    $excludedTypes = ['applicant', 'guest', 'student', 'alumni'];
                    $accountType = strtolower($item['account_type'] ?? '');
                    
                    if (in_array($accountType, $excludedTypes, true)) {
                        continue;
                    }

                    $users[] = [
                        'oneportal_id' => $item['id'] ?? null,
                        'first_name'   => $item['first_name'] ?? '',
                        'middle_name'  => $item['middle_name'] ?? '',
                        'last_name'    => $item['last_name'] ?? '',
                        'email'        => $item['email'] ?? '',
                        'source'       => 'IDP',
                    ];
                }

                $lastPage = $data['meta']['last_page'] ?? $data['last_page'] ?? 1;
                $page++;

            } while ($page <= $lastPage && $page <= 100); // Failsafe limit of 100 pages

        } catch (\Exception $e) {
            Log::error('IdpDirectoryService exception: ' . $e->getMessage());
        }

        if (!empty($users)) {
            Cache::put($cacheKey, $users, now()->addMinutes(15));
            Cache::put($cacheKey . '_timestamp', now()->toIso8601String(), now()->addMinutes(15));
        }

        return [
            'data'      => $users,
            'is_cached' => false,
            'cached_at' => null,
        ];
    }
}
