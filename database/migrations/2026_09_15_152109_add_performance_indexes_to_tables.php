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
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'is_featured') && !collect(DB::select("SHOW INDEXES FROM news"))->pluck('Key_name')->contains('news_is_featured_index')) {
                $table->index('is_featured');
            }
            if (Schema::hasColumn('news', 'is_hidden_from_public') && !collect(DB::select("SHOW INDEXES FROM news"))->pluck('Key_name')->contains('news_is_hidden_from_public_index')) {
                $table->index('is_hidden_from_public');
            }
            if (!collect(DB::select("SHOW INDEXES FROM news"))->pluck('Key_name')->contains('news_created_at_index')) {
                $table->index('created_at');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            if (!collect(DB::select("SHOW INDEXES FROM announcements"))->pluck('Key_name')->contains('announcements_status_index')) {
                $table->index('status');
            }
            if (!collect(DB::select("SHOW INDEXES FROM announcements"))->pluck('Key_name')->contains('announcements_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'is_featured')) {
                $table->dropIndex(['is_featured']);
            }
            if (Schema::hasColumn('news', 'is_hidden_from_public')) {
                $table->dropIndex(['is_hidden_from_public']);
            }
            $table->dropIndex(['created_at']);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
