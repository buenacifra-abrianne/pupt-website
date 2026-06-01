<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feedback_submissions')) {
            Schema::create('feedback_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('q1_score')->nullable();
                $table->unsignedTinyInteger('q2_score')->nullable();
                $table->unsignedTinyInteger('q3_score')->nullable();
                $table->unsignedTinyInteger('q4_score')->nullable();
                $table->unsignedTinyInteger('q5_score')->nullable();
                $table->unsignedTinyInteger('q6_score')->nullable();
                $table->unsignedTinyInteger('q7_score')->nullable();
                $table->unsignedTinyInteger('q8_score')->nullable();
                $table->unsignedTinyInteger('q9_score')->nullable();
                $table->unsignedTinyInteger('q10_score')->nullable();
                $table->decimal('overall_score', 4, 2)->nullable();
                $table->string('overall_rating', 40)->nullable();
                $table->timestamps();

                $table->index('created_at');
                $table->index('overall_rating');
            });

            return;
        }

        Schema::table('feedback_submissions', function (Blueprint $table) {
            foreach (range(1, 10) as $questionNumber) {
                $column = 'q'.$questionNumber.'_score';

                if (! Schema::hasColumn('feedback_submissions', $column)) {
                    $table->unsignedTinyInteger($column)->nullable();
                }
            }

            if (! Schema::hasColumn('feedback_submissions', 'overall_score')) {
                $table->decimal('overall_score', 4, 2)->nullable();
            }

            if (! Schema::hasColumn('feedback_submissions', 'overall_rating')) {
                $table->string('overall_rating', 40)->nullable();
            }

            if (! Schema::hasColumn('feedback_submissions', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('feedback_submissions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep existing feedback submissions intact on rollback.
    }
};
