<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activity_logs', 'approval_request_id')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                // Keep the audit record even if approval history is later deleted.
                $table->unsignedBigInteger('approval_request_id')->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropUnique(['approval_request_id']);
            $table->dropColumn('approval_request_id');
        });
    }
};
