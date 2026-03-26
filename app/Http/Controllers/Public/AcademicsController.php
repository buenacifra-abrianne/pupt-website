<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\HomeCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicsController extends Controller
{
    public function index(Request $request)
    {
        // Section is now an optional query parameter: /academics?section=degree-programs
        return $this->renderPage($request->query('section'));
    }

    private function renderPage(?string $section = null)
    {
        $homeCms = HomeCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $homeRow = DB::table('cms_contents')->where('tab_key', 'home')->first();
            if ($homeRow) {
                $homeCms = HomeCmsContent::fromStored((string) ($homeRow->content ?? ''));
            }
        }

        $sectionsMap = $this->sections();
        $sections    = array_values($sectionsMap); // numeric index for Blade

        $selectedSection = null;

        if ($section !== null) {
            abort_unless(isset($sectionsMap[$section]), 404);
            $selectedSection = $sectionsMap[$section];
        }

        $campusStoryDescription = $homeCms['campus_description'] ?? '';
        $historyMovedParagraphs = [];

        return view('public.academics', compact(
            'homeCms',
            'sections',
            'selectedSection',
            'campusStoryDescription',
            'historyMovedParagraphs',
        ));
    }

    private function sections(): array
    {
        return [
            'degree-programs' => [
                'slug'       => 'degree-programs',
                'number'     => '01',
                'label'      => 'Degree Programs',
                'summary'    => 'Discover a wide range of undergraduate majors and minors designed to prepare you for professional success.',
                'image'      => 'pupillar.jpeg',
                'content_id' => 'degreePrograms',
            ],
            'diploma-programs' => [
                'slug'       => 'diploma-programs',
                'number'     => '02',
                'label'      => 'Diploma Programs',
                'summary'    => 'Gain practical skills and specialized knowledge through diploma courses tailored for career readiness.',
                'image'      => 'pupillar.jpeg',
                'content_id' => 'diplomaPrograms',
            ],
            'graduate-programs' => [
                'slug'       => 'graduate-programs',
                'number'     => '03',
                'label'      => 'Graduate Programs',
                'summary'    => "Advance your expertise with master's and doctoral programs that foster research, leadership, and innovation.",
                'image'      => 'pupillar.jpeg',
                'content_id' => 'graduatePrograms',
            ],
            'pup-iapply' => [
                'slug'       => 'pup-iapply',
                'number'     => '04',
                'label'      => 'PUP iApply',
                'summary'    => "Easily access the university's online application portal to start your academic journey.",
                'image'      => 'pupillar.jpeg',
                'content_id' => 'pupIApply',
            ],
            'university-calendar' => [
                'slug'       => 'university-calendar',
                'number'     => '05',
                'label'      => 'University Calendar',
                'summary'    => 'Stay updated with important academic schedules, events, and deadlines throughout the school year.',
                'image'      => 'pupillar.jpeg',
                'content_id' => 'universityCalendar',
            ],
        ];
    }
}