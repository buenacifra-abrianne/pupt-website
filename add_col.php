<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('news', function (Blueprint $table) {
    if (!Schema::hasColumn('news', 'additional_images')) {
        $table->json('additional_images')->nullable()->after('image_path');
    }
});
echo "Done\n";