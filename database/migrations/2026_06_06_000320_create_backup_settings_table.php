<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('backup_settings')) {
            return;
        }

        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('automatic_backups_enabled')->default(false);
            $table->string('frequency', 20)->default('daily');
            $table->unsignedSmallInteger('retention_count')->default(7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};
