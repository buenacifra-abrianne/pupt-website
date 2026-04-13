<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\EventsCmsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventsController extends Controller
{
    public function index()
    {
        $content = '';

        if (Schema::hasTable('cms_contents')) {
            $row = DB::table('cms_contents')
                ->where('tab_key', 'events')
                ->first();

            $content = (string) ($row->content ?? '');
        }

        return view('public.events', [
            'eventsCms' => EventsCmsContent::fromStored($content),
        ]);
    }
}
