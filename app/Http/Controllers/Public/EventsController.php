<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\EventsCmsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class EventsController extends Controller
{
    public function index()
    {
        $content = Cache::remember('public_events_cms', 300, function () {
            if (Schema::hasTable('cms_contents')) {
                $row = DB::table('cms_contents')
                    ->where('tab_key', 'events')
                    ->first();
                return (string) ($row->content ?? '');
            }
            return '';
        });

        return view('public.events', [
            'eventsCms' => EventsCmsContent::fromStored($content),
        ]);
    }
}
