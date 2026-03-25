<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\HomeCmsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AboutController extends Controller
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

        return view('public.about', compact('homeCms'));
    }
}
