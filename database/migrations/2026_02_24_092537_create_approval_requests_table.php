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
    if (Schema::hasTable('approval_requests')) {
        return;
    }

    Schema::create('approval_requests', function (Blueprint $table) {
        $table->id();

        $table->string('title');
        $table->text('details')->nullable();
        $table->string('type')->nullable();

        $table->string('status')->default('pending'); // pending | approved | rejected

        $table->string('requester_name')->nullable();
        $table->string('requester_email')->nullable();

        $table->unsignedBigInteger('reviewed_by')->nullable();
        $table->timestamp('reviewed_at')->nullable();
        $table->string('rejection_reason')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
