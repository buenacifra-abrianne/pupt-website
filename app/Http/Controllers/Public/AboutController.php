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
        return $this->renderPage();
    }

    public function show(string $section)
    {
        return $this->renderPage($section);
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

        $sections = $this->sections();
        $selectedSection = null;

        if ($section !== null) {
            abort_unless(isset($sections[$section]), 404);
            $selectedSection = $sections[$section];
        }

        return view('public.about', compact('homeCms', 'sections', 'selectedSection'));
    }

    private function sections(): array
    {
        return [
            'history' => [
                'slug' => 'history',
                'number' => '01',
                'label' => 'History',
                'summary' => 'Discover how the institution grew into today\'s PUP community.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'about_readMore',
            ],
            'vision-and-mission' => [
                'slug' => 'vision-and-mission',
                'number' => '02',
                'label' => 'Vision and Mission',
                'summary' => 'Read the principles that shape the campus direction.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'visionMission',
            ],
            'logo-and-symbols' => [
                'slug' => 'logo-and-symbols',
                'number' => '03',
                'label' => 'Logo and Symbols',
                'summary' => 'Understand the visual identity and what it represents.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'logoSymbols',
            ],
            'hymn' => [
                'slug' => 'hymn',
                'number' => '04',
                'label' => 'Hymn',
                'summary' => 'See the campus hymn and its meaning to the community.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'hymn',
            ],
            'maps' => [
                'slug' => 'maps',
                'number' => '05',
                'label' => 'Maps',
                'summary' => 'Locate the campus and open the full map quickly.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'maps',
            ],
            'campus-officials' => [
                'slug' => 'campus-officials',
                'number' => '06',
                'label' => 'Campus Officials',
                'summary' => 'View the people guiding the campus and its services.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'campusOfficials',
            ],
            'strategic-development-plan' => [
                'slug' => 'strategic-development-plan',
                'number' => '07',
                'label' => 'Strategic Development Plan',
                'summary' => 'Review the long-term plans and priorities of the campus.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'strategicPlan',
            ],
            'university-calendar' => [
                'slug' => 'university-calendar',
                'number' => '08',
                'label' => 'University Calendar',
                'summary' => 'Check the academic and institutional schedule overview.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'universityCalendar',
            ],
        ];
    }
}
