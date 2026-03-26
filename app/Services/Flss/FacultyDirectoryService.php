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
            return [];
        }

        try {
            $payload = $this->client->get('/api/v1/faculties');
            $faculties = collect($payload['faculties'] ?? []);

            return $faculties
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
                        'email' => $email,
                        'status' => 'Active',
                        'label' => $label !== '' ? $label : $email,
                    ];
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::error('FLSS faculty fetch failed', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}