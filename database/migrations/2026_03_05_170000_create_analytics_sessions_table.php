<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_sessions')) {
            return;
        }

        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_uuid')->unique();
            $table->string('visitor_id', 64)->index();
            $table->timestamp('started_at')->index();
            $table->timestamp('last_activity_at')->index();
            $table->unsignedInteger('pageviews_count')->default(1);
            $table->string('entry_path', 255)->nullable();
            $table->string('last_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_sessions');
    }
};