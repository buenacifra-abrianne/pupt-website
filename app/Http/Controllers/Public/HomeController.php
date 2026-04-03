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
            ->select('announcement_id','title','content','link','priority','date_published','created_at')
            ->whereRaw("UPPER(TRIM(status)) = 'ENABLED'")
            ->orderByRaw("
                CASE 
                    WHEN UPPER(TRIM(priority)) = 'HIGH' THEN 0
                    WHEN UPPER(TRIM(priority)) = 'MEDIUM' THEN 1
                    WHEN UPPER(TRIM(priority)) = 'LOW' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw("COALESCE(date_published, created_at) DESC")
            ->limit(10)
            ->get();

        // News: APPROVED only
        $news = DB::table('news')
            ->select('news_id','title','content', 'link', 'category','location','image_path','priority','date_published','created_at')
            ->whereRaw("UPPER(TRIM(status)) = 'APPROVED'")
            ->orderByRaw("
                CASE 
                    WHEN UPPER(TRIM(priority)) = 'HIGH' THEN 0
                    WHEN UPPER(TRIM(priority)) = 'MEDIUM' THEN 1
                    WHEN UPPER(TRIM(priority)) = 'LOW' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('date_published')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('public.home', compact('announcements', 'news', 'homeCms'));
    }
}
