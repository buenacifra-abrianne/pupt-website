<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\StudentsCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        return view('public.students', [
            'studentsCms' => $this->loadStudentsCms(),
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }

    public function admissions(Request $request)
    {
        return view('public.student_admissions', [
            'studentsCms' => $this->loadStudentsCms(),
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }

    public function downloadableForms(Request $request)
    {
        return view('public.student_downloadable_forms', [
            'studentsCms' => $this->loadStudentsCms(),
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }

    public function documentRequests(Request $request)
    {
        return view('public.student_document_requests', [
            'studentsCms' => $this->loadStudentsCms(),
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }

    private function loadStudentsCms(): array
    {
        $studentsCms = StudentsCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $studentsRow = DB::table('cms_contents')
                ->where('tab_key', 'students')
                ->first();

            if ($studentsRow) {
                $studentsCms = StudentsCmsContent::fromStored((string) ($studentsRow->content ?? ''));
            }
        }

        return $studentsCms;
    }
}
