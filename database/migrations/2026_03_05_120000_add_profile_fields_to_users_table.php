<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasFirstName = Schema::hasColumn('users', 'first_name');
        $hasMiddleName = Schema::hasColumn('users', 'middle_name');
        $hasLastName = Schema::hasColumn('users', 'last_name');
        $hasProfilePicture = Schema::hasColumn('users', 'profile_picture');

        if ($hasFirstName && $hasMiddleName && $hasLastName && $hasProfilePicture) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($hasFirstName, $hasMiddleName, $hasLastName, $hasProfilePicture) {
            if (!$hasFirstName) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!$hasMiddleName) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (!$hasLastName) {
                $table->string('last_name')->nullable()->after('middle_name');
            }
            if (!$hasProfilePicture) {
                $table->string('profile_picture')->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank to avoid dropping pre-existing columns.
    }
};

