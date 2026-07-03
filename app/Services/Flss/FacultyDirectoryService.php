<?php

namespace App\Services\Flss;

use Illuminate\Support\Facades\Log;

class FacultyDirectoryService
{
    public function __construct(
        protected FlssClient $client
    ) {}

    public function getActiveFacultyForDropdown(): array
    {
        if (!$this->client->configured()) {
            Log::warning('FLSS is not configured yet: missing base URL or API key.');
            return ['data' => [], 'is_cached' => false, 'cached_at' => null];
        }

        try {
            // Apply 3 second timeout for web requests
            $this->client->setTimeout(3);
            
            $payload = $this->client->get('/api/v1/faculties');
            $faculties = collect($payload['faculties'] ?? []);

            $data = $faculties
                ->filter(function ($faculty) {
                    return strtoupper(trim((string) ($faculty['status'] ?? ''))) === 'ACTIVE';
                })
                ->map(function ($faculty) {
                    $firstName = (string) ($faculty['first_name'] ?? '');
                    $middleName = (string) ($faculty['middle_name'] ?? '');
                    $lastName = (string) ($faculty['last_name'] ?? '');
                    $suffix = (string) ($faculty['suffix_name'] ?? '');
                    $email = (string) ($faculty['email'] ?? '');

                    $label = trim(implode(' ', array_filter([
                        $firstName,
                        $middleName,
                        $lastName,
                        $suffix,
                    ])));

                    return [
                        'id' => (string) ($faculty['faculty_id'] ?? ''),
                        'faculty_code' => (string) ($faculty['faculty_code'] ?? ''),
                        'first_name' => $firstName,
                        'middle_name' => $middleName !== '' ? $middleName : null,
                        'last_name' => $lastName,
                        'suffix' => $suffix !== '' ? $suffix : null,
                        'email' => strtolower(trim($email)),
                        'status' => 'Active',
                        'label' => $label !== '' ? $label : $email,
                        'source' => 'FLSS',
                    ];
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
            Log::error('FLSS faculty fetch failed, falling back to local cache', [
                'message' => $e->getMessage(),
            ]);

            return $this->getFallbackFromCache();
        }
    }
    
    protected function getFallbackFromCache(): array
    {
        try {
            $cached = \Illuminate\Support\Facades\DB::table('faculty_cache')
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('middle_name')
                ->get();
                
            if ($cached->isEmpty()) {
                return ['data' => [], 'is_cached' => true, 'cached_at' => null];
            }
            
            $data = $cached->map(function ($faculty) {
                return [
                    'id' => $faculty->faculty_id,
                    'faculty_code' => $faculty->faculty_code,
                    'first_name' => $faculty->first_name,
                    'middle_name' => $faculty->middle_name,
                    'last_name' => $faculty->last_name,
                    'suffix' => $faculty->suffix,
                    'email' => strtolower(trim((string)$faculty->email)),
                    'status' => $faculty->status,
                    'label' => $faculty->label,
                    'source' => 'FLSS (Cached)',
                ];
            })->all();
            
            $firstRecord = $cached->first();
            $cachedAt = $firstRecord->updated_at ?? null;
            
            return ['data' => $data, 'is_cached' => true, 'cached_at' => $cachedAt];
        } catch (\Throwable $e) {
            Log::error('Failed to read from faculty_cache', [
                'message' => $e->getMessage(),
            ]);
            return ['data' => [], 'is_cached' => true, 'cached_at' => null];
        }
    }
}