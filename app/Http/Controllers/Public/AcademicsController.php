<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\AcademicsCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

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
        $academicsCms = Cache::remember('public_academics_cms', 300, function () {
            $cms = AcademicsCmsContent::defaults();
            if (Schema::hasTable('cms_contents')) {
                $academicsRow = DB::table('cms_contents')->where('tab_key', 'academics')->first();
                if ($academicsRow) {
                    $cms = AcademicsCmsContent::fromStored((string) ($academicsRow->content ?? ''));
                }
            }
            return $cms;
        });

        return view('public.academics', compact('academicsCms'));
    }
}
