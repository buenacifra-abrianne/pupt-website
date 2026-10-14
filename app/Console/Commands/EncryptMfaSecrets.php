<?php

namespace App\Console\Commands;

use App\Services\MfaSecretStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class EncryptMfaSecrets extends Command
{
    protected $signature = 'mfa:encrypt-secrets {--check : Verify encryption without changing any secrets}';

    protected $description = 'Encrypt existing MFA secrets or verify that all stored secrets are encrypted and decryptable.';

    public function handle(MfaSecretStorage $storage): int
    {
        if (! Schema::hasColumn('users', 'mfa_secret')) {
            $this->error('The users.mfa_secret column is missing. Run the database migrations first.');

            return self::FAILURE;
        }

        $checkOnly = (bool) $this->option('check');

        try {
            $counts = $storage->processExisting($checkOnly);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($checkOnly) {
            $this->info("{$counts['encrypted']} encrypted MFA secret(s); {$counts['plaintext']} plaintext MFA secret(s).");

            return $counts['plaintext'] === 0 ? self::SUCCESS : self::FAILURE;
        }

        $this->info("Encrypted {$counts['plaintext']} legacy MFA secret(s); {$counts['encrypted']} already encrypted.");

        return self::SUCCESS;
    }
}
