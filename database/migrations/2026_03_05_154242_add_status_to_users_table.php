<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('status', 20)->default('Active')->after('role'); // Active/Inactive/Pending/Suspended
        $table->timestamp('last_login_at')->nullable()->after('status'); // optional
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['status', 'last_login_at']);
    });
}
};
