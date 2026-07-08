<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\HomeCmsContent;
use App\Support\PlainText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $homeCms = HomeCmsContent::defaults();
        $hasNewsHiddenColumn = Schema::hasTable('news') && Schema::hasColumn('news', 'is_hidden_from_public');

        if (Schema::hasTable('cms_contents')) {
            $homeRow = DB::table('cms_contents')->where('tab_key', 'home')->first();
            if ($homeRow) {
                $homeCms = HomeCmsContent::fromStored((string) ($homeRow->content ?? ''));
            }
        }

        // Announcements: ENABLED only (latest first)
        $announcements = collect();
        if (Schema::hasTable('announcements')) {
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
                ->get()
                ->map(function ($announcement) {
                    $announcement->title = PlainText::normalize($announcement->title ?? '');

                    return $announcement;
                });
        }

        // News: APPROVED only
        $news = collect();
        $oneMonthAgo = now()->subMonth();
        if (Schema::hasTable('news')) {
            $news = DB::table('news')
                ->select('news_id','title','content', 'link', 'category','location','image_path','priority','date_published','created_at')
                ->whereRaw("UPPER(TRIM(status)) = 'APPROVED'")
                ->when($hasNewsHiddenColumn, function ($query) {
                    $query->where(function ($inner) {
                        $inner->whereNull('is_hidden_from_public')
                            ->orWhere('is_hidden_from_public', 0);
                    });
                })
                ->where(function($query) use ($oneMonthAgo) {
                    $query->where('date_published', '>=', $oneMonthAgo)
                          ->orWhere(function($q) use ($oneMonthAgo) {
                              $q->whereNull('date_published')->where('created_at', '>=', $oneMonthAgo);
                          });
                })
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
                ->get()
                ->map(function ($item) {
                    $item->title = PlainText::normalize($item->title ?? '');
                    $item->category = PlainText::normalize($item->category ?? '');
                    $item->location = PlainText::normalize($item->location ?? '');

                    return $item;
                });
        }

        return view('public.home', compact('announcements', 'news', 'homeCms'));
    }
}
