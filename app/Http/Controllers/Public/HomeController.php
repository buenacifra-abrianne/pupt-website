<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
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

        return view('public.home', compact('announcements', 'news'));
    }
}