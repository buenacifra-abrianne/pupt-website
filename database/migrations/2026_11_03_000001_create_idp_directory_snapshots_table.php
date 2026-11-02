<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idp_directory_snapshots', function (Blueprint $table) {
            $table->string('source', 64)->primary();
            $table->text('base_url');
            $table->longText('users');
            $table->timestamp('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idp_directory_snapshots');
    }
};
