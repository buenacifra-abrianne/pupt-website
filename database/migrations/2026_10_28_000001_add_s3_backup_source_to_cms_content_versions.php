<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_content_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('source_backup_id')->nullable()->index();
            $table->char('source_backup_checksum', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cms_content_versions', function (Blueprint $table) {
            $table->dropIndex(['source_backup_id']);
            $table->dropColumn(['source_backup_id', 'source_backup_checksum']);
        });
    }
};
