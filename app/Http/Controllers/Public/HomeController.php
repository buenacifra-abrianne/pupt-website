<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\HomeCmsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $homeCms = HomeCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $homeRow = DB::table('cms_contents')->where('tab_key', 'home')->first();
            if ($homeRow) {
                $homeCms = HomeCmsContent::fromStored((string) ($homeRow->content ?? ''));
            }
        }

        // Announcements: ENABLED only (latest first)
        $announcements = DB::table('announcements')
            ->select('announcement_id','title','content','date_published','created_at')
            ->whereRaw("UPPER(TRIM(status)) = 'ENABLED'")
            ->orderByDesc('date_published')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // News: APPROVED only (latest first)
        $news = DB::table('news')
            ->select('news_id','title','content','category','location','image_path','date_published','created_at')
            ->whereRaw("UPPER(TRIM(status)) = 'APPROVED'")
            ->orderByDesc('date_published')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('public.home', compact('announcements', 'news', 'homeCms'));
    }
}
