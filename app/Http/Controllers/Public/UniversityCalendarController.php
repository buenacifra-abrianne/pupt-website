<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\AcademicsCmsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UniversityCalendarController extends Controller
{
    public function index()
    {
        $academicsCms = AcademicsCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $academicsRow = DB::table('cms_contents')->where('tab_key', 'academics')->first();
            if ($academicsRow) {
                $academicsCms = AcademicsCmsContent::fromStored((string) ($academicsRow->content ?? ''));
            }
        }

        return view('public.universitycalendar', compact('academicsCms'));
    }
}
