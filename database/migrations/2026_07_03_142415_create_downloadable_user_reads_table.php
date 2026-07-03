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
        Schema::create('downloadable_user_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('downloadable_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('downloadable_id')->references('downloadable_id')->on('downloadables')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unique(['downloadable_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloadable_user_reads');
    }
};
