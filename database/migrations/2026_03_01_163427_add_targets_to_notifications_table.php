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
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $hasTargetRole = Schema::hasColumn('notifications', 'target_role');
        $hasTargetUserId = Schema::hasColumn('notifications', 'target_user_id');

        if ($hasTargetRole && $hasTargetUserId) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) use ($hasTargetRole, $hasTargetUserId) {
            if (!$hasTargetRole) {
                $table->string('target_role', 20)->nullable()->index(); // 'ADMIN' or 'STAFF'
            }

            if (!$hasTargetUserId) {
                $table->unsignedBigInteger('target_user_id')->nullable()->index(); // for STAFF-only
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $hasTargetRole = Schema::hasColumn('notifications', 'target_role');
        $hasTargetUserId = Schema::hasColumn('notifications', 'target_user_id');

        if (!$hasTargetRole && !$hasTargetUserId) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) use ($hasTargetRole, $hasTargetUserId) {
            if ($hasTargetRole) {
                $table->dropColumn('target_role');
            }

            if ($hasTargetUserId) {
                $table->dropColumn('target_user_id');
            }
        });
    }
};
