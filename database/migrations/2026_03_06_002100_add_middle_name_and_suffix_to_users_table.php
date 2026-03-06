<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $hasMiddleName = Schema::hasColumn('users', 'middle_name');
        $hasSuffix = Schema::hasColumn('users', 'suffix');

        if ($hasMiddleName && $hasSuffix) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($hasMiddleName, $hasSuffix) {
            if (!$hasMiddleName) {
                $table->string('middle_name')->nullable();
            }

            if (!$hasSuffix) {
                $table->string('suffix', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank to avoid dropping columns in existing environments.
    }
};
