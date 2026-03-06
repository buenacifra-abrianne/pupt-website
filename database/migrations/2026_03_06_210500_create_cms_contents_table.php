<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_contents')) {
            return;
        }

        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->string('tab_key', 80)->unique();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_contents');
    }
};
