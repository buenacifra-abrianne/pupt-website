<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feedback_submissions')) {
            return;
        }

        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('q1_score');
            $table->unsignedTinyInteger('q2_score');
            $table->unsignedTinyInteger('q3_score');
            $table->unsignedTinyInteger('q4_score');
            $table->unsignedTinyInteger('q5_score');
            $table->unsignedTinyInteger('q6_score');
            $table->decimal('overall_score', 4, 2);
            $table->string('overall_rating', 40);
            $table->timestamps();

            $table->index('created_at');
            $table->index('overall_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_submissions');
    }
};

