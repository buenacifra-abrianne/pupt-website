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
            return [];
        }

        try {
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

            return $admins
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
        } catch (\Throwable $e) {
            Log::error('OCMS admin fetch failed', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}