<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feedback_submissions')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE feedback_submissions MODIFY q1_score TINYINT UNSIGNED NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q2_score TINYINT UNSIGNED NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q3_score TINYINT UNSIGNED NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q4_score TINYINT UNSIGNED NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q5_score TINYINT UNSIGNED NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q6_score TINYINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('feedback_submissions')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('UPDATE feedback_submissions SET q1_score = COALESCE(q1_score, 1), q2_score = COALESCE(q2_score, 1), q3_score = COALESCE(q3_score, 1), q4_score = COALESCE(q4_score, 1), q5_score = COALESCE(q5_score, 1), q6_score = COALESCE(q6_score, 1)');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q1_score TINYINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q2_score TINYINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q3_score TINYINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q4_score TINYINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q5_score TINYINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE feedback_submissions MODIFY q6_score TINYINT UNSIGNED NOT NULL');
    }
};
