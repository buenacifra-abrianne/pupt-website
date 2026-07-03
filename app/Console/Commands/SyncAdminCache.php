<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Ocms\OcmsClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAdminCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sync-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the active admin directory from OCMS and store it in the local database cache.';

    /**
     * Execute the console command.
     */
    public function handle(OcmsClient $client)
    {
        $this->info('Starting admin cache sync...');

        if (!$client->configured()) {
            $this->error('OCMS is not configured. Aborting.');
            return Command::FAILURE;
        }

        try {
            $this->info('Fetching admins from OCMS API...');
            
            // Note: Background job uses the default OcmsClient timeout (e.g., 15s)
            $payload = $client->get('/external/admins/options');
            
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

            $activeAdmins = $admins->map(function ($admin) {
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
                    'admin_id' => (string) ($admin['id'] ?? $admin['admin_id'] ?? ''),
                    'office' => (string) ($admin['office'] ?? $admin['offices'] ?? ''),
                    'first_name' => $firstName,
                    'middle_name' => $middleName !== '' ? $middleName : null,
                    'last_name' => $lastName,
                    'suffix' => $suffix !== '' ? $suffix : null,
                    'email' => $email,
                    'status' => in_array($status, ['ACTIVE', 'INACTIVE', 'SUSPENDED'], true) ? ucfirst(strtolower($status)) : 'Active',
                    'label' => $label !== '' ? $label : $email,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->filter(function ($data) {
                return $data['email'] !== '';
            })->values()->all();

            $this->info(sprintf('Fetched %d admins. Updating database...', count($activeAdmins)));

            DB::transaction(function () use ($activeAdmins) {
                // Clear the existing cache table (delete avoids implicit commit caused by truncate in MySQL)
                DB::table('admin_cache')->delete();
                
                // Chunk the inserts to avoid payload too large issues
                $chunks = array_chunk($activeAdmins, 500);
                foreach ($chunks as $chunk) {
                    DB::table('admin_cache')->insert($chunk);
                }
            });

            $this->info('Successfully synced admin cache.');
            return Command::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Admin sync failed: ' . $e->getMessage());
            Log::error('Scheduled admin sync failed', [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
