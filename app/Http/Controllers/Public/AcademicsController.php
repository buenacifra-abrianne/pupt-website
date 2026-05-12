<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\AcademicsCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicsController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderPage();
    }

    public function degreePrograms()
    {
        return view('public.academics.degree-programs');
    }

    public function diplomaPrograms()
    {
        return view('public.academics.diplomaprograms');
    }

    public function pupIApply()
    {
        return view('public.academics.pupiapply');
    }

    public function universityCalendar()
    {
        return view('public.academics.universitycalendar');
    }

    private function renderPage()
    {
        $academicsCms = AcademicsCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $academicsRow = DB::table('cms_contents')->where('tab_key', 'academics')->first();
            if ($academicsRow) {
                $academicsCms = AcademicsCmsContent::fromStored((string) ($academicsRow->content ?? ''));
            }
        }

        return view('public.academics', compact('academicsCms'));
    }
}
