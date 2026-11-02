<?php

use App\Support\CmsSections;
use App\Support\CmsVersionHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_content_versions', function (Blueprint $table) {
            $table->id();
            $table->string('tab_key', 80)->index();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->char('checksum', 64);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 80)->nullable();
            $table->string('source', 40);
            $table->string('section_key', 120)->nullable();
            $table->unsignedBigInteger('restored_from_id')->nullable();
            $table->timestamp('created_at');
            $table->index(['tab_key', 'id']);
        });

        if (Schema::hasTable('cms_contents')) {
            foreach (CmsSections::allTabKeys() as $tabKey) {
                $row = DB::table('cms_contents')->where('tab_key', $tabKey)->first();
                CmsVersionHistory::append($tabKey, $row, 'baseline');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_content_versions');
    }
};
