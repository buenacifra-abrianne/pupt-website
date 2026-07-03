<?php

namespace App\Services\Ocms;

use Illuminate\Support\Facades\Log;

class OcmsAdminDirectoryService
{
    public function __construct(
        protected OcmsClient $client
    ) {}

    public function getActiveAdminsForDropdown(): array
    {
        if (!$this->client->configured()) {
            Log::warning('OCMS is not configured yet: missing base URL or API key.');
            return ['data' => [], 'is_cached' => false, 'cached_at' => null];
        }

        try {
            // Apply 3 second timeout for web requests
            $this->client->setTimeout(3);
            
            $payload = $this->client->get('/external/admins/options');

            $rawAdmins = $payload['data']
                ?? $payload['admins']
                ?? $payload['options']
                ?? $payload['results']
                ?? $payload['items']
                ?? $payload;

            if (isset($rawAdmins['id']) || isset($rawAdmins['admin_id'])) {
                $rawAdmins = [$rawAdmins];
            }

            $admins = collect(is_array($rawAdmins) ? $rawAdmins : []);

            $data = $admins
                ->map(function ($admin) {
                    $firstName = (string) ($admin['first_name'] ?? '');
                    $middleName = (string) ($admin['middle_name'] ?? '');
                    $lastName = (string) ($admin['last_name'] ?? '');
                    $suffix = (string) ($admin['suffix_name'] ?? '');
                    $email = strtolower(trim((string) ($admin['email'] ?? $admin['email_address'] ?? '')));
                    $status = strtoupper(trim((string) ($admin['status'] ?? '')));

                    $label = trim(implode(' ', array_filter([
                        $firstName,
                        $middleName,
                        $lastName,
                        $suffix,
                    ])));

                    return [
                        'id' => 'ocms:' . (string) ($admin['id'] ?? $admin['admin_id'] ?? ''),
                        'admin_id' => (string) ($admin['id'] ?? $admin['admin_id'] ?? ''),
                        'office' => (string) ($admin['office'] ?? $admin['offices'] ?? ''),
                        'first_name' => $firstName,
                        'middle_name' => $middleName !== '' ? $middleName : null,
                        'last_name' => $lastName,
                        'suffix' => $suffix !== '' ? $suffix : null,
                        'email' => $email,
                        'status' => in_array($status, ['ACTIVE', 'INACTIVE', 'SUSPENDED'], true) ? ucfirst(strtolower($status)) : 'Active',
                        'label' => $label !== '' ? $label : $email,
                        'source' => 'OCMS',
                    ];
                })
                ->filter(function ($admin) {
                    return trim((string) ($admin['email'] ?? '')) !== '';
                })
                ->sortBy([
                    ['first_name', 'asc'],
                    ['last_name', 'asc'],
                    ['middle_name', 'asc'],
                ])
                ->values()
                ->all();
                
            return ['data' => $data, 'is_cached' => false, 'cached_at' => null];
                
        } catch (\Throwable $e) {
            Log::error('OCMS admin fetch failed, falling back to local cache', [
                'message' => $e->getMessage(),
            ]);

            return $this->getFallbackFromCache();
        }
    }
    
    protected function getFallbackFromCache(): array
    {
        try {
            $cached = \Illuminate\Support\Facades\DB::table('admin_cache')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('middle_name')
                ->get();
                
            if ($cached->isEmpty()) {
                return ['data' => [], 'is_cached' => true, 'cached_at' => null];
            }
            
            $data = $cached->map(function ($admin) {
                return [
                    'id' => 'ocms:' . $admin->admin_id,
                    'admin_id' => $admin->admin_id,
                    'office' => $admin->office,
                    'first_name' => $admin->first_name,
                    'middle_name' => $admin->middle_name,
                    'last_name' => $admin->last_name,
                    'suffix' => $admin->suffix,
                    'email' => strtolower(trim((string)$admin->email)),
                    'status' => $admin->status,
                    'label' => $admin->label,
                    'source' => 'OCMS (Cached)',
                ];
            })->all();
            
            $firstRecord = $cached->first();
            $cachedAt = $firstRecord->updated_at ?? null;
            
            return ['data' => $data, 'is_cached' => true, 'cached_at' => $cachedAt];
        } catch (\Throwable $e) {
            Log::error('Failed to read from admin_cache', [
                'message' => $e->getMessage(),
            ]);
            return ['data' => [], 'is_cached' => true, 'cached_at' => null];
        }
    }
}