<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id('announcement_id');
                $table->string('title');
                $table->text('content');
                $table->string('link')->nullable();
                $table->string('priority', 50)->nullable();
                $table->string('status', 20)->nullable();
                $table->date('date_published')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('status');
                $table->index('date_published');
            });
        } else {
            Schema::table('announcements', function (Blueprint $table) {
                if (! Schema::hasColumn('announcements', 'link')) {
                    $table->string('link')->nullable();
                }
            });
        }

        if (! Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->id('news_id');
                $table->string('title');
                $table->text('content');
                $table->string('category', 100)->nullable();
                $table->string('location', 150)->nullable();
                $table->string('image_path')->nullable();
                $table->date('date_published')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->string('priority', 50)->nullable();
                $table->string('status', 50)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->boolean('is_hidden_from_public')->nullable();
                $table->string('link')->nullable();

                $table->index('status');
                $table->index('date_published');
            });
        } else {
            Schema::table('news', function (Blueprint $table) {
                if (! Schema::hasColumn('news', 'link')) {
                    $table->string('link')->nullable();
                }

                if (! Schema::hasColumn('news', 'is_hidden_from_public')) {
                    $table->boolean('is_hidden_from_public')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Keep existing content intact on rollback.
    }
};
