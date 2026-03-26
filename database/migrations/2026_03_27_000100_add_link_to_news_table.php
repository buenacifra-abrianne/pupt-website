<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('news') || Schema::hasColumn('news', 'link')) {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            $table->string('link')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('news') || !Schema::hasColumn('news', 'link')) {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
