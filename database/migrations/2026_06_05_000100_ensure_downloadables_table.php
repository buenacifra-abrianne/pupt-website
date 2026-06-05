<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('downloadables')) {
            Schema::create('downloadables', function (Blueprint $table) {
                $table->id('downloadable_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category', 100)->nullable();
                $table->string('file_path');
                $table->string('original_filename');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('created_at');
                $table->index('created_by');
            });

            return;
        }

        $columns = [
            'title' => fn (Blueprint $table) => $table->string('title')->nullable(),
            'description' => fn (Blueprint $table) => $table->text('description')->nullable(),
            'category' => fn (Blueprint $table) => $table->string('category', 100)->nullable(),
            'file_path' => fn (Blueprint $table) => $table->string('file_path')->nullable(),
            'original_filename' => fn (Blueprint $table) => $table->string('original_filename')->nullable(),
            'created_by' => fn (Blueprint $table) => $table->unsignedBigInteger('created_by')->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $column => $addColumn) {
            if (! Schema::hasColumn('downloadables', $column)) {
                Schema::table('downloadables', $addColumn);
            }
        }
    }

    public function down(): void
    {
        // Keep existing downloadable records and files intact on rollback.
    }
};
