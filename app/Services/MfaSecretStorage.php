<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MfaSecretStorage
{
    public function isPlaintext(string $value): bool
    {
        // Only generated Base32 secrets qualify as legacy plaintext. Never
        // treat damaged ciphertext or a value encrypted with another key as one.
        return preg_match('/\A[A-Z2-7]{16,}\z/', $value) === 1;
    }

    public function encrypt(string $secret): string
    {
        if (! $this->isPlaintext($secret)) {
            throw new RuntimeException('Invalid MFA secret format.');
        }

        return Crypt::encryptString($secret);
    }

    public function decrypt(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        if ($this->isPlaintext($stored)) {
            return $stored;
        }

        $secret = Crypt::decryptString($stored);

        if (! $this->isPlaintext($secret)) {
            throw new DecryptException('Invalid encrypted MFA secret.');
        }

        return $secret;
    }

    public function protect(?string $stored): ?string
    {
        $secret = $this->decrypt($stored);

        if ($secret === null) {
            return $stored;
        }

        return $this->isPlaintext($stored) ? $this->encrypt($secret) : $stored;
    }

    public function protectUser(int|string $userId, string $idColumn): ?string
    {
        return DB::transaction(function () use ($userId, $idColumn): ?string {
            $user = DB::table('users')->where($idColumn, $userId)->lockForUpdate()->first();

            if (! $user) {
                return null;
            }

            $protected = $this->protect($user->mfa_secret);

            if ($protected !== $user->mfa_secret) {
                DB::table('users')->where($idColumn, $userId)->update(['mfa_secret' => $protected]);
            }

            return $protected;
        });
    }

    /** @return array{plaintext: int, encrypted: int} */
    public function processExisting(bool $checkOnly = false): array
    {
        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $counts = ['plaintext' => 0, 'encrypted' => 0];

        DB::table('users')->select([$idColumn, 'mfa_secret'])
            ->whereNotNull('mfa_secret')->where('mfa_secret', '<>', '')
            ->chunkById(100, function ($users) use ($idColumn, $checkOnly, &$counts): void {
                foreach ($users as $user) {
                    try {
                        // Validate encrypted entries as well as legacy entries.
                        $this->decrypt($user->mfa_secret);

                        if ($this->isPlaintext($user->mfa_secret)) {
                            $counts['plaintext']++;

                            if (! $checkOnly) {
                                $this->protectUser($user->{$idColumn}, $idColumn);
                            }
                        } else {
                            $counts['encrypted']++;
                        }
                    } catch (DecryptException $exception) {
                        throw new RuntimeException(
                            "Unable to decrypt MFA secret for user {$user->{$idColumn}}. Check the encryption key or stored data.",
                            previous: $exception,
                        );
                    }
                }
            }, $idColumn);

        return $counts;
    }
}
