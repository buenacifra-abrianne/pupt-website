<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('botpress_knowledge_links')) {
            return;
        }

        if (Schema::hasColumn('botpress_knowledge_links', 'url')) {
            DB::statement('ALTER TABLE botpress_knowledge_links MODIFY url VARCHAR(512) NOT NULL');
        }

        $indexExists = collect(DB::select("SHOW INDEX FROM botpress_knowledge_links WHERE Key_name = 'botpress_knowledge_links_url_unique'"))->isNotEmpty();

        if (!$indexExists) {
            Schema::table('botpress_knowledge_links', function (Blueprint $table) {
                $table->unique('url', 'botpress_knowledge_links_url_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('botpress_knowledge_links')) {
            return;
        }

        $indexExists = collect(DB::select("SHOW INDEX FROM botpress_knowledge_links WHERE Key_name = 'botpress_knowledge_links_url_unique'"))->isNotEmpty();

        if ($indexExists) {
            Schema::table('botpress_knowledge_links', function (Blueprint $table) {
                $table->dropUnique('botpress_knowledge_links_url_unique');
            });
        }
    }
};
