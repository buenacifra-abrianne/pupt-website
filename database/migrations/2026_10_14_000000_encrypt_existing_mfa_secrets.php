<?php

use App\Services\MfaSecretStorage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Encrypted payloads can exceed the original VARCHAR(255) capacity.
        Schema::table('users', function (Blueprint $table) {
            $table->text('mfa_secret')->nullable()->change();
        });

        // Idempotent backfill: retain the same TOTP key and skip ciphertext.
        // A failed run can safely resume without encrypting a value twice.
        app(MfaSecretStorage::class)->processExisting();
    }

    public function down(): void
    {
        // Keep encrypted secrets and the TEXT column on rollback. Decrypting
        // into the database or shrinking the column would expose or lose keys.
    }
};
