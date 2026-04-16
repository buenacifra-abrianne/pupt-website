<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feedback_submissions')) {
            return;
        }

        Schema::table('feedback_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('feedback_submissions', 'q7_score')) {
                $table->unsignedTinyInteger('q7_score')->nullable()->after('q6_score');
            }

            if (! Schema::hasColumn('feedback_submissions', 'q8_score')) {
                $table->unsignedTinyInteger('q8_score')->nullable()->after('q7_score');
            }

            if (! Schema::hasColumn('feedback_submissions', 'q9_score')) {
                $table->unsignedTinyInteger('q9_score')->nullable()->after('q8_score');
            }

            if (! Schema::hasColumn('feedback_submissions', 'q10_score')) {
                $table->unsignedTinyInteger('q10_score')->nullable()->after('q9_score');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('feedback_submissions')) {
            return;
        }

        Schema::table('feedback_submissions', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['q7_score', 'q8_score', 'q9_score', 'q10_score'] as $column) {
                if (Schema::hasColumn('feedback_submissions', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
