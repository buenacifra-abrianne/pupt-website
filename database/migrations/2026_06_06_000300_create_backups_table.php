<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('backups')) {
            return;
        }

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('backup_name')->unique();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('storage_disk', 50);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('created_by');
            $table->index('storage_disk');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
