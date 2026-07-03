<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Flss\FlssClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncFacultyCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faculty:sync-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the active faculty directory from FLSS and store it in the local database cache.';

    /**
     * Execute the console command.
     */
    public function handle(FlssClient $client)
    {
        $this->info('Starting faculty cache sync...');

        if (!$client->configured()) {
            $this->error('FLSS is not configured. Aborting.');
            return Command::FAILURE;
        }

        try {
            $this->info('Fetching faculties from FLSS API...');
            
            // Note: Background job uses the default FlssClient timeout (e.g., 15s)
            $payload = $client->get('/api/v1/faculties');
            $faculties = collect($payload['faculties'] ?? []);

            $activeFaculties = $faculties->filter(function ($faculty) {
                return strtoupper(trim((string) ($faculty['status'] ?? ''))) === 'ACTIVE';
            })->map(function ($faculty) {
                $firstName = (string) ($faculty['first_name'] ?? '');
                $middleName = (string) ($faculty['middle_name'] ?? '');
                $lastName = (string) ($faculty['last_name'] ?? '');
                $suffix = (string) ($faculty['suffix_name'] ?? '');
                $email = strtolower(trim((string) ($faculty['email'] ?? '')));

                $label = trim(implode(' ', array_filter([
                    $firstName,
                    $middleName,
                    $lastName,
                    $suffix,
                ])));

                return [
                    'faculty_id' => (string) ($faculty['faculty_id'] ?? ''),
                    'faculty_code' => (string) ($faculty['faculty_code'] ?? ''),
                    'first_name' => $firstName,
                    'middle_name' => $middleName !== '' ? $middleName : null,
                    'last_name' => $lastName,
                    'suffix' => $suffix !== '' ? $suffix : null,
                    'email' => $email,
                    'status' => 'Active',
                    'label' => $label !== '' ? $label : $email,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->filter(function ($data) {
                return $data['email'] !== '';
            })->values()->all();

            $this->info(sprintf('Fetched %d active faculties. Updating database...', count($activeFaculties)));

            DB::transaction(function () use ($activeFaculties) {
                // Clear the existing cache table (delete avoids implicit commit caused by truncate in MySQL)
                DB::table('faculty_cache')->delete();
                
                // Chunk the inserts to avoid payload too large issues
                $chunks = array_chunk($activeFaculties, 500);
                foreach ($chunks as $chunk) {
                    DB::table('faculty_cache')->insert($chunk);
                }
            });

            $this->info('Successfully synced faculty cache.');
            return Command::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Faculty sync failed: ' . $e->getMessage());
            Log::error('Scheduled faculty sync failed', [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
