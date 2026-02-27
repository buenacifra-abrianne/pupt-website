<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Notify
{
    public static function system(string $title, string $message, string $type = 'INFO'): void
    {
        $allowed = ['INFO','WARNING','DANGER','PRIMARY'];
        $type = strtoupper($type);
        if (!in_array($type, $allowed, true)) $type = 'INFO';

        DB::table('notifications')->insert([
            'channel'    => 'SYSTEM',
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'created_at' => now()
        ]);
    }
}