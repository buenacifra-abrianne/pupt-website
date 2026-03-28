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
                'summary' => 'Read the guiding ideals that shape learning, service, and growth on campus.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'visionMission',
                'lead' => 'The campus stands for purposeful learning, public service, and academic excellence guided by the University\'s shared ideals.',
                'vision' => 'A LEADING COMPREHENSIVE POLYTECHNIC UNIVERSITY IN ASIA',
                'mission' => 'ADVANCE AN INCLUSIVE, EQUITABLE, AND GLOBALLY RELEVANT POLYTECHNIC EDUCATION TOWARDS NATIONAL DEVELOPMENT',
                'strategic_goals' => [
                    [
                        'pillar' => 'Pillar 1',
                        'title' => 'Teaching and Learning',
                        'goals' => [
                            [
                                'number' => 1,
                                'text' => 'Innovative Curricula and Instruction',
                            ],
                            [
                                'number' => 2,
                                'text' => 'Empowered, Expert, and Productive Faculty Members',
                            ],
                            [
                                'number' => 3,
                                'text' => 'Holistic Student Development',
                            ],
                        ],
                    ],
                    [
                        'pillar' => 'Pillar 2',
                        'title' => 'Research and Extension',
                        'goals' => [
                            [
                                'number' => 4,
                                'text' => 'Intensified Research Innovation, Dissemination and Utilization',
                            ],
                            [
                                'number' => 5,
                                'text' => 'Strengthened Sustainable and Impactful Extension Program',
                            ],
                            [
                                'number' => 6,
                                'text' => 'Expanded Research and Extension Networks with Local, National, and International Partners',
                            ],
                        ],
                    ],
                    [
                        'pillar' => 'Pillar 3',
                        'title' => 'Internal Governance',
                        'goals' => [
                            [
                                'number' => 7,
                                'text' => 'Transformational University Leadership',
                            ],
                            [
                                'number' => 8,
                                'text' => 'Judicious and Ethical Stewardship of Physical and Financial Resources',
                            ],
                            [
                                'number' => 9,
                                'text' => 'Effective and Efficient Human Resource Management',
                            ],
                            [
                                'number' => 10,
                                'text' => 'Excellent Citizen/Client Satisfaction',
                            ],
                            [
                                'number' => 11,
                                'text' => 'Smart Campuses',
                            ],
                        ],
                    ],
                ],
                'core_values' => [
                    [
                        'letter' => 'I',
                        'title' => 'Integrity and Accountability',
                        'description' => 'We uphold honesty, accountability, and ethical public service in every action.',
                    ],
                    [
                        'letter' => 'N',
                        'title' => 'Nationalism',
                        'description' => 'We use education to contribute to the welfare and progress of the nation.',
                    ],
                    [
                        'letter' => 'S',
                        'title' => 'Sense of Service',
                        'description' => 'We nurture purpose, respect, and compassion in the life of the community.',
                    ],
                    [
                        'letter' => 'P',
                        'title' => 'Passion for Learning and Innovation',
                        'description' => 'We pursue disciplined, competent, and excellent work in every responsibility.',
                    ],
                    [
                        'letter' => 'I',
                        'title' => 'Inclusivity',
                        'description' => 'We build collaboration, mutual respect, and solidarity across the campus.',
                    ],
                    [
                        'letter' => 'R',
                        'title' => 'Respect for Human Rights and the Environment',
                        'description' => 'We listen, adapt, and serve the evolving needs of students and society.',
                    ],
                    [
                        'letter' => 'E',
                        'title' => 'Excellence',
                        'description' => 'We aim for high standards in learning, leadership, and institutional impact.',
                    ],
                    [
                        'letter' => 'D',
                        'title' => 'Democracy',
                        'description' => 'We value participation, fairness, and shared responsibility in the community.',
                    ],
                ],
            ],
            'logo-and-symbols' => [
                'slug' => 'logo-and-symbols',
                'number' => '03',
                'label' => 'Logo and Symbols',
                'summary' => 'Understand the campus identity marks and what they communicate.',
                'image' => 'logo.png',
                'content_id' => 'logoSymbols',
                'lead' => 'The campus identity reflects scholarship, public service, discipline, and institutional pride across official communications and ceremonies.',
                'identity_marks' => [
                    [
                        'title' => 'University Seal',
                        'body' => 'The seal serves as the most formal visual mark of the institution and is used to represent authority, continuity, and academic heritage.',
                    ],
                    [
                        'title' => 'Campus Branding',
                        'body' => 'Official campus materials use consistent colors, typography, and seal placement to preserve recognition and trust.',
                    ],
                ],
                'symbol_points' => [
                    'Maroon signals strength, courage, and commitment to service.',
                    'Gold highlights achievement, honor, and the pursuit of excellence.',
                    'The official mark represents both academic rigor and the responsibility of serving the public.',
                    'Campus symbols are used with respect in publications, ceremonies, and student activities.',
                ],
            ],
            'hymn' => [
                'slug' => 'hymn',
                'number' => '04',
                'label' => 'Hymn',
                'summary' => 'Explore what the campus hymn represents in ceremonies and student life.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'hymn',
                'lead' => 'The campus hymn serves as a ceremonial expression of identity, unity, and commitment to the ideals of the University.',
                'hymn_sections' => [
                    [
                        'title' => 'What It Expresses',
                        'body' => 'The hymn celebrates pride in learning, loyalty to the institution, and the call to use one\'s education in service of the nation.',
                    ],
                    [
                        'title' => 'When It Is Performed',
                        'body' => 'It is commonly sung during campus ceremonies, academic gatherings, recognition programs, and other official events.',
                    ],
                    [
                        'title' => 'Shared Practice',
                        'body' => 'Students, employees, alumni, and guests are encouraged to observe the hymn with respect and attention whenever it is rendered.',
                    ],
                ],
                'hymn_notes' => [
                    'The hymn reinforces a sense of belonging across different generations of the PUP community.',
                    'Its message is closely linked with discipline, honor, perseverance, and public service.',
                    'Official event programs may provide the approved performance guide when needed.',
                ],
            ],
            'maps' => [
                'slug' => 'maps',
                'number' => '05',
                'label' => 'Maps',
                'summary' => 'Locate the campus quickly and prepare for visits or transactions.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'maps',
                'lead' => 'Use the campus map before travelling so you can plan your route, confirm your destination office, and arrive with enough time for your transaction.',
                'map_cards' => [
                    [
                        'title' => 'Campus Location',
                        'body' => 'The map link opens the official campus pin for route guidance, nearby roads, and travel-time estimates.',
                    ],
                    [
                        'title' => 'Before You Visit',
                        'body' => 'Coordinate with the office you are visiting, prepare a valid ID, and monitor announcements for schedule changes.',
                    ],
                ],
                'visit_notes' => [
                    'Allow extra travel time during enrollment, examinations, and university-wide events.',
                    'Use the map link for the most accurate live directions and traffic-aware route suggestions.',
                    'Bring the documents required by the office you intend to visit to avoid delays.',
                ],
            ],
            'campus-officials' => [
                'slug' => 'campus-officials',
                'number' => '06',
                'label' => 'Campus Officials',
                'summary' => 'See the key leadership and service offices that guide campus operations.',
                'image' => 'pupillar.jpeg',
                'content_id' => 'campusOfficials',
                'lead' => 'Campus leadership is organized through academic, student, administrative, and service offices that support daily operations and long-term development.',
                'official_groups' => [
                    [
                        'title' => 'Campus Director',
                        'body' => 'Provides overall direction, institutional coordination, and external linkages for the campus.',
                    ],
                    [
                        'title' => 'Academic Affairs',
                        'body' => 'Oversees instruction, faculty coordination, curriculum delivery, and academic standards.',
                    ],
                    [
                        'title' => 'Student Services',
                        'body' => 'Supports student welfare, guidance, discipline, co-curricular programs, and campus life.',
                    ],
                    [
                        'title' => 'Administration and Finance',
                        'body' => 'Handles facilities, records support, procurement, budgeting, and operational resources.',
                    ],
                    [
                        'title' => 'Research and Extension',
                        'body' => 'Leads research activity, partnerships, and outreach initiatives that connect campus work with communities.',
                    ],
                    [
                        'title' => 'Registrar and Frontline Offices',
                        'body' => 'Assists with academic records, enrolment-related requests, and student-facing transactions.',
                    ],
                ],
                'officials_note' => 'For the latest officeholders and contact details, refer to current campus announcements and office directories.',
            ],
            'strategic-development-plan' => [
                'slug' => 'strategic-development-plan',
                'number' => '07',
                'label' => 'Strategic Development Plan',
                'summary' => 'Review the long-term priorities that shape campus growth and improvement.',
                'image' => 'about-pup.png',
                'content_id' => 'strategicPlan',
                'lead' => 'The campus strategic development plan aligns academic priorities, student support, facilities, and partnerships toward sustainable institutional growth.',
                'development_priorities' => [
                    [
                        'title' => 'Instructional Excellence',
                        'body' => 'Continue improving program delivery, learning outcomes, and faculty support systems.',
                    ],
                    [
                        'title' => 'Student Success',
                        'body' => 'Expand services that improve access, retention, wellbeing, and holistic student development.',
                    ],
                    [
                        'title' => 'Infrastructure and Digital Readiness',
                        'body' => 'Upgrade classrooms, laboratories, connectivity, and campus systems that support learning and operations.',
                    ],
                    [
                        'title' => 'Research and Community Engagement',
                        'body' => 'Strengthen initiatives that connect scholarship with community needs and industry collaboration.',
                    ],
                    [
                        'title' => 'Good Governance',
                        'body' => 'Promote data-informed planning, transparent processes, and continuous quality improvement.',
                    ],
                ],
                'plan_principles' => [
                    'Set measurable targets for instruction, services, and campus operations.',
                    'Use feedback and evidence to improve policies, programs, and resource allocation.',
                    'Build partnerships that extend learning opportunities and community impact.',
                ],
            ],
        ];
    }
}
