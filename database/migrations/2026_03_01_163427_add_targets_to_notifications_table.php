<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('target_role', 20)->nullable()->index(); // 'ADMIN' or 'STAFF'
            $table->unsignedBigInteger('target_user_id')->nullable()->index(); // for STAFF-only
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['target_role', 'target_user_id']);
        });
    }
};
