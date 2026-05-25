<?php

namespace App\Support;

class AboutCmsContent
{
    private const DEFAULT_CARD_IMAGE = '/assets/static_img/pupillar.jpeg';
    private const LEGACY_CARD_IMAGES = [
        '/assets/static_img/logo.png',
        '/assets/static_img/about-pup.png',
    ];

    private const DEFAULTS = [
        'overview' => [
            'hero_image' => '/assets/static_img/about_header_image.png',
            'hero_title_default' => 'ABOUT THE CAMPUS',
            'hero_title_history' => 'CAMPUS HISTORY',
            'hero_title_vision' => 'VISION AND MISSION',
            'story_tag' => 'Campus Story',
            'story_title' => 'PUP Taguig Campus',
            'story_image' => '/assets/static_img/about-pup.png',
            'story_description' => "The Polytechnic University of the Philippines (PUP) is a government educational institution governed by Republic Act Number 8292 known as the Higher Education Modernization Act of 1997, and its Implementing Rules and Regulations contained in the Commission on Higher Education Memorandum Circular No. 4, series 1997. PUP is one of the country's highly competent educational institutions. The PUP Community is composed of the Board of Regents, University Officials, Administrative and Academic Personnel, Students, various Organizations, and the Alumni.\n\nGovernance of PUP is vested upon the Board of Regents, which exercises policy-making functions to carry out the mission and programs of the University by virtue of RA 8292 granted by the Commission on Higher Education. The University is administered by an appointed President by virtue of RA 8292 and is assisted by an Executive Vice President and the Vice Presidents for Academic Affairs, Student Services, Administration, Research, Extension and Development, and Finance.",
            'contents_tag' => 'Contents',
            'contents_title' => 'All about the campus',
            'section_header_image' => '/assets/static_img/about_header_image.png',
        ],
        'sections' => [
            'history' => [
                'slug' => 'history',
                'number' => '01',
                'label' => 'History',
                'visible_in_contents' => '1',
                'summary' => 'Discover how the institution grew into today\'s PUP community.',
                'image' => self::DEFAULT_CARD_IMAGE,
                'page_kicker' => 'Campus Timeline',
                'page_title' => 'How PUP Taguig has evolved over the years',
                'timeline' => [
                    [
                        'period' => 'JULY 1957 - September 1968',
                        'title' => 'School-site reservation established',
                        'body' => [
                            'Twenty years ago, upon the request of then Philippine College of Commerce President, Dr. Nemesio Prudente, former President Ferdinand Marcos issued proclamation No. 469, which excluded from the operation of Proclamation No. 423, dated July 12, 1957 a certain portion of land (10 hectares) situated in the Municipality of Taguig for school purposes of the PCC, now Polytechnic University of the Philippines. This proclamation was issued on September 30, 1968.',
                        ],
                    ],
                    [
                        'period' => 'September 1972 - June 1975',
                        'title' => 'Land dispute and interruption',
                        'body' => [
                            'But things didn\'t work right. On September 21, 1972, President Marcos declared Martial Law, and Dr. Prudente had to forcibly vacate the PCC Presidency. Those who succeeded him, however, did nothing about the land. When he resumed his post in 1978, he started reviving PUP\'s right to the land, which, by that time, was already occupied by the Metals Industry Research and Development Center (MIRDC).',
                            'With three congressmen sympathetic to PUP, Prudente wrote President Corazon Aquino about the matter, citing that the 10-hectare land awarded to PCC was reassigned to MIRDC with an assurance from former Sen. Ronaldo Zamora and former MIRDC Director Antonio Arizabal, Jr. that former Pres. Marcos shall issue another Presidential Proclamation excluding 17-hectares from Proclamation No. 423 to be given to MIRDC on June 2, 1975. Unfortunately, the said assurance did not materialize.',
                            'University officials conducted thorough studies and investigation about the matter. MIRDC claimed that it had rights on the land by virtue of an unnumbered Presidential Proclamation dated June 2, 1975. In fact, they had already four laboratory buildings situated at the disputed site since 1975.',
                        ],
                    ],
                    [
                        'period' => 'July 1990 - September 1990',
                        'title' => 'Claim reasserted and groundbreaking held',
                        'body' => [
                            'On July 10, 1990, Dr. Prudente once more wrote Malacanang Palace, asking if Presidential Proclamation No. 469 had been revoked by any another Presidential issuances. Mrs. Aurora Aquino, Director IV, replied that the records available in that office failed to show that Proclamation No. 469 was ever revoked.',
                            'Thus, on September 8, 1990, PUP personnel surreptitiously entered the site premises and started the construction of a building, inspite of a notice nailed to the fence which read: NO TREEPASSING, (BAWAL PUMASOK) MIRDC PROPERTY, All Trespassers will be prosecuted. On Sept. 9, 1990 the Ground-Breaking Ceremony of PUP Taguig was held at 9:00 A.M..with Sec. Guillermo Carague, Sec. Ceferino Follosco, Sen. Edgardo Angara, Gen. Marino Filart, Congressmen Dante Tinga, Rolando Andaya, and Carlos Padilla, Mayors de Guzman, Capco, Bunye, and Ferrer, among others as sponsors. This move of the PUP Administration caused the MIRDC to file a suit against PUP for entering its premises without seeking permit and for illegally constructing two buildings within their area. Consequently, the office of the Solicitor General issued a CEASE AND DESIST ORDER was issued by the Office of the President on Sept. 27, 1990.',
                        ],
                    ],
                    [
                        'period' => 'October 1990 - February 1991',
                        'title' => 'Certification and legal ruling',
                        'body' => [
                            'Another CEASE AND DESIST ORDER on Oct. 1, 1990. PUP officials tried to settle the matter amicably but the MIRDC refused to accept the offer. Instead, a winner-take-all deal was agreed upon.',
                            'On Oct. 19, 1990, the Office of the Press Secretary (National Printing Office) through Mr. Heriberto Bacalla, the Chief of the Official Gazette Publication, certified that the said office had never received nor accepted any unnumbered document about the PUP land from the Office of the Malacanang Records for publications in the Official Gazette. Thus, the alleged unnumbered proclamation entitled, Revoking Proclamation No. 469, dated Sept. 30, 1968, which established the school site reservation of the PUP in the Municipality of Taguig and reserving the land embraced therein for MIRDC, was not received nor published in the Official Gazette. This development opened the case with MIRDC as petitioner and PUP as respondent with a powerhouse legal counsel, which included lawyers Ernesto Fernandez, Boy Acejas III, Orlino, Estelita dela Rosa, Honesto Cueva, Marcelino Bonafe, Valencia and Rodrigo Melchor.',
                            'Finally, on Feb. 4, 1991, then Justice Secretary Franklin Drilon released the ruling regarding the issue, stating that Proclamation No. 469 has not been revoked or superseded by the unpublished and unnumbered Proclamation dated June 2, 1975, and therefore said Proclamation No. 469 remains valid and enforceable until that time.',
                        ],
                    ],
                    [
                        'period' => 'April 1992 - June 1992',
                        'title' => 'Opening campaign and first classes',
                        'body' => [
                            'Following the court ruling, the construction immediately resumed, this time receiving substantial support from Congressman Tinga, who worked and fought hard for its funding in Congress. That time, a P7 million fund was released for the two-storey building which the school used for its initial operation.',
                            'In April 1992, through the request of Dr. Samuel M. Salvador, Vice President for Branches and Extension Services, PUP acting President Zenaida Olonan, gave the approval to start the campaign for the opening of PUP Taguig. He recommended Dr. Normita A. Villa, Prof. Angelito D. Roldan, Prof. Susan A. Roldan, and Prof. Amelita A. Laurente to do the job.',
                            'On June 15, 1992, amidst the uncertainty of a mounting campus unrest spawned by a university-wide opposition to the administration of then Education Secretary Carinos anointed, Dr. Jaime Gellor, PUP Taguig held its first class session with some 470 enrollees, 15 faculty members, and a lone clerical staff housed in a still to be completed two-storey building standing on a muddy terrain.',
                        ],
                    ],
                ],
            ],
            'vision-and-mission' => [
                'slug' => 'vision-and-mission',
                'number' => '02',
                'label' => 'Vision and Mission',
                'visible_in_contents' => '1',
                'summary' => 'Read the guiding ideals that shape learning, service, and growth on campus.',
                'image' => self::DEFAULT_CARD_IMAGE,
                'page_kicker' => 'Vision and Mission',
                'page_title' => 'Vision, Mission, Core Values, and Strategic Goals of the University',
                'vision' => 'A LEADING COMPREHENSIVE POLYTECHNIC UNIVERSITY IN ASIA',
                'mission' => 'ADVANCE AN INCLUSIVE, EQUITABLE, AND GLOBALLY RELEVANT POLYTECHNIC EDUCATION TOWARDS NATIONAL DEVELOPMENT',
                'strategic_goals' => [
                    [
                        'pillar' => 'Pillar 1',
                        'title' => 'Teaching and Learning',
                        'goals' => [
                            ['number' => '1', 'text' => 'Innovative Curricula and Instruction'],
                            ['number' => '2', 'text' => 'Empowered, Expert, and Productive Faculty Members'],
                            ['number' => '3', 'text' => 'Holistic Student Development'],
                        ],
                    ],
                    [
                        'pillar' => 'Pillar 2',
                        'title' => 'Research and Extension',
                        'goals' => [
                            ['number' => '4', 'text' => 'Intensified Research Innovation, Dissemination and Utilization'],
                            ['number' => '5', 'text' => 'Strengthened Sustainable and Impactful Extension Program'],
                            ['number' => '6', 'text' => 'Expanded Research and Extension Networks with Local, National, and International Partners'],
                        ],
                    ],
                    [
                        'pillar' => 'Pillar 3',
                        'title' => 'Internal Governance',
                        'goals' => [
                            ['number' => '7', 'text' => 'Transformational University Leadership'],
                            ['number' => '8', 'text' => 'Judicious and Ethical Stewardship of Physical and Financial Resources'],
                            ['number' => '9', 'text' => 'Effective and Efficient Human Resource Management'],
                            ['number' => '10', 'text' => 'Excellent Citizen/Client Satisfaction'],
                            ['number' => '11', 'text' => 'Smart Campuses'],
                        ],
                    ],
                ],
                'core_values' => [
                    ['letter' => 'I', 'title' => 'Integrity and Accountability'],
                    ['letter' => 'N', 'title' => 'Nationalism'],
                    ['letter' => 'S', 'title' => 'Sense of Service'],
                    ['letter' => 'P', 'title' => 'Passion for Learning and Innovation'],
                    ['letter' => 'I', 'title' => 'Inclusivity'],
                    ['letter' => 'R', 'title' => 'Respect for Human Rights and the Environment'],
                    ['letter' => 'E', 'title' => 'Excellence'],
                    ['letter' => 'D', 'title' => 'Democracy'],
                ],
            ],
            'logo-and-symbols' => [
                'slug' => 'logo-and-symbols',
                'number' => '03',
                'label' => 'Logo and Symbols',
                'visible_in_contents' => '1',
                'summary' => 'Learn the meaning behind the seal, colors, and symbolic elements of the University.',
                'image' => self::DEFAULT_CARD_IMAGE,
                'lead' => 'Each element of the University logo represents a core ideal: truth, wisdom, excellence, purity, and the highest form of quality embodied in education.',
                'identity_marks' => [
                    [
                        'title' => 'The Star',
                        'body' => 'The star stands for the perfection of the human person as well as the search for truth.',
                    ],
                    [
                        'title' => 'Five Concentric Circles',
                        'body' => 'The five concentric circles depict infinite wisdom and, together with the five-pointed star, stand for quintessence.',
                    ],
                    [
                        'title' => 'Laurel Arcs',
                        'body' => 'The two arcs of laurel symbolize excellence and quality of education as demonstrated by the rich achievements of the University in over a century of its existence.',
                    ],
                    [
                        'title' => 'University Colors',
                        'body' => 'Golden yellow and dark maroon reflect the traditional colors of the University, while white symbolizes purity.',
                    ],
                ],
                'symbol_points' => [
                    'The five-pointed star and the five concentric circles both stand for quintessence, meaning the highest form of quality or the most perfect example of creation.',
                    'The star is golden yellow because it is a star\'s natural color and because it is one of the traditional colors of the University.',
                    'Dark maroon serves as the logo background and completes the traditional University color pairing with golden yellow.',
                    'The five concentric circles are white because white symbolizes purity.',
                ],
            ],
            'hymn' => [
                'slug' => 'hymn',
                'number' => '04',
                'label' => 'Hymn',
                'visible_in_contents' => '1',
                'summary' => 'Explore what the campus hymn represents in ceremonies and student life.',
                'image' => self::DEFAULT_CARD_IMAGE,
                'lead' => 'The campus hymn serves as a ceremonial expression of identity, unity, and commitment to the ideals of the University.',
                'hymn_sections' => [
                    [
                        'title' => 'Verse I',
                        'body' => "Sintang Paaralan\nTanglaw ka ng bayan\nPandayan ng isip ng kabataan\nKami ay dumating nang salat sa yaman\nHanap na dunong ay iyong alay",
                    ],
                    [
                        'title' => 'Verse II',
                        'body' => "Ang layunin mong makatao\nDinarangal ang Pilipino\nAng iyong aral, diwa, adhikang taglay\nPUP, aming gabay",
                    ],
                    [
                        'title' => 'Chorus',
                        'body' => "Paaralang dakila\nPUP, pinagpala\nGagamitin ang karunungan\nMula sa iyo, para sa bayan\nAng iyong aral, diwa, adhikang taglay\nPUP, aming gabay\nPaaralang dakila\nPUP, pinagpala",
                    ],
                ],
            ],
            'maps' => [
                'slug' => 'maps',
                'number' => '05',
                'label' => 'Maps',
                'visible_in_contents' => '1',
                'summary' => 'Locate the campus quickly and prepare for visits or transactions.',
                'image' => self::DEFAULT_CARD_IMAGE,
                'lead' => 'Use the campus map before travelling so you can plan your route, confirm your destination office, and arrive with enough time for your transaction.',
                'map_url' => 'https://maps.app.goo.gl/RDAwxBvDzyGzUbVN7',
                'visit_planning_text' => 'Open the official map to check live directions, nearby access roads, and updated travel time before heading to campus.',
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
                'visible_in_contents' => '1',
                'summary' => 'See the key leadership and service offices that guide campus operations.',
                'image' => self::DEFAULT_CARD_IMAGE,
                'lead' => 'Campus leadership is organized through academic, student, administrative, and service offices that support daily operations and long-term development.',
                'official_groups' => [
                    [
                        'name' => '',
                        'title' => 'Campus Director',
                        'body' => 'Provides overall direction, institutional coordination, and external linkages for the campus.',
                        'order' => 1,
                    ],
                    [
                        'name' => '',
                        'title' => 'Academic Affairs',
                        'body' => 'Oversees instruction, faculty coordination, curriculum delivery, and academic standards.',
                        'order' => 2,
                    ],
                    [
                        'name' => '',
                        'title' => 'Student Services',
                        'body' => 'Supports student welfare, guidance, discipline, co-curricular programs, and campus life.',
                        'order' => 3,
                    ],
                    [
                        'name' => '',
                        'title' => 'Administration and Finance',
                        'body' => 'Handles facilities, records support, procurement, budgeting, and operational resources.',
                        'order' => 4,
                    ],
                    [
                        'name' => '',
                        'title' => 'Research and Extension',
                        'body' => 'Leads research activity, partnerships, and outreach initiatives that connect campus work with communities.',
                        'order' => 5,
                    ],
                    [
                        'name' => '',
                        'title' => 'Registrar and Frontline Offices',
                        'body' => 'Assists with academic records, enrolment-related requests, and student-facing transactions.',
                        'order' => 6,
                    ],
                ],
                'officials_note' => 'For the latest officeholders and contact details, refer to current campus announcements and office directories.',
            ],
            'strategic-development-plan' => [
                'slug' => 'strategic-development-plan',
                'number' => '07',
                'label' => 'Strategic Development Plan',
                'visible_in_contents' => '1',
                'summary' => 'Review the long-term priorities that shape campus growth and improvement.',
                'image' => self::DEFAULT_CARD_IMAGE,
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
        ],
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function sectionSlugs(): array
    {
        return array_keys(self::DEFAULTS['sections']);
    }

    public static function fromStored(?string $raw): array
    {
        $content = trim((string) $raw);

        if ($content === '') {
            return self::defaults();
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return self::defaults();
        }

        if (isset($decoded['about']) && is_array($decoded['about'])) {
            $decoded = $decoded['about'];
        }

        return self::normalize($decoded, self::defaults());
    }

    public static function fromInput(mixed $input, ?string $fallbackStored = null): array
    {
        $base = self::fromStored($fallbackStored);
        $source = is_array($input) ? $input : [];

        return self::normalize($source, $base);
    }

    public static function encode(array $data): string
    {
        return (string) json_encode(
            self::normalize($data, self::defaults()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public static function resolveImagePath(?string $path, string $fallbackPath): string
    {
        return (string) (ImageStorage::url($path, $fallbackPath) ?? asset(ltrim($fallbackPath, '/')));
    }

    private static function normalize(array $source, array $base): array
    {
        $defaults = self::defaults();
        $overviewSource = is_array($source['overview'] ?? null) ? $source['overview'] : [];
        $overviewBase = is_array($base['overview'] ?? null) ? $base['overview'] : $defaults['overview'];
        $sectionsSource = is_array($source['sections'] ?? null) ? $source['sections'] : [];
        $sectionsBase = is_array($base['sections'] ?? null) ? $base['sections'] : $defaults['sections'];
        $sections = [];

        foreach ($defaults['sections'] as $slug => $defaultSection) {
            $sectionSource = is_array($sectionsSource[$slug] ?? null) ? $sectionsSource[$slug] : [];
            $sectionBase = is_array($sectionsBase[$slug] ?? null) ? $sectionsBase[$slug] : $defaultSection;
            $sections[$slug] = self::normalizeSection($slug, $sectionSource, $sectionBase, $defaultSection);
        }

        return [
            'overview' => self::normalizeOverview($overviewSource, $overviewBase, $defaults['overview']),
            'sections' => $sections,
        ];
    }

    private static function normalizeOverview(array $source, array $base, array $defaults): array
    {
        return [
            'hero_image' => self::pickOptionalString($source, $base, $defaults, 'hero_image', 2048),
            'hero_title_default' => self::pickString($source, $base, $defaults, 'hero_title_default'),
            'hero_title_history' => self::pickString($source, $base, $defaults, 'hero_title_history'),
            'hero_title_vision' => self::pickString($source, $base, $defaults, 'hero_title_vision'),
            'story_tag' => self::pickString($source, $base, $defaults, 'story_tag'),
            'story_title' => self::pickString($source, $base, $defaults, 'story_title'),
            'story_image' => self::pickOptionalString($source, $base, $defaults, 'story_image', 2048),
            'story_description' => self::pickString($source, $base, $defaults, 'story_description', 12000),
            'contents_tag' => self::pickString($source, $base, $defaults, 'contents_tag'),
            'contents_title' => self::pickString($source, $base, $defaults, 'contents_title'),
            'section_header_image' => self::pickOptionalString($source, $base, $defaults, 'section_header_image', 2048),
        ];
    }

    private static function normalizeSection(string $slug, array $source, array $base, array $defaults): array
    {
        $section = [
            'slug' => $defaults['slug'],
            'number' => self::pickString($source, $base, $defaults, 'number'),
            'label' => self::pickString($source, $base, $defaults, 'label'),
            'visible_in_contents' => self::pickFlag($source, $base, $defaults, 'visible_in_contents'),
            'summary' => self::pickString($source, $base, $defaults, 'summary', 2000),
            'image' => self::normalizeCardImage(
                self::pickOptionalString($source, $base, $defaults, 'image', 2048)
            ),
        ];

        return match ($slug) {
            'history' => array_merge($section, [
                'page_kicker' => self::pickString($source, $base, $defaults, 'page_kicker'),
                'page_title' => self::pickString($source, $base, $defaults, 'page_title'),
                'timeline' => self::normalizeTimeline(
                    $source['timeline'] ?? [],
                    $base['timeline'] ?? $defaults['timeline'],
                    $defaults['timeline']
                ),
            ]),
            'vision-and-mission' => array_merge($section, [
                'page_kicker' => self::pickString($source, $base, $defaults, 'page_kicker'),
                'page_title' => self::pickString($source, $base, $defaults, 'page_title'),
                'vision' => self::pickString($source, $base, $defaults, 'vision', 4000),
                'mission' => self::pickString($source, $base, $defaults, 'mission', 4000),
                'strategic_goals' => self::normalizeStrategicGoals(
                    $source['strategic_goals'] ?? [],
                    $base['strategic_goals'] ?? $defaults['strategic_goals'],
                    $defaults['strategic_goals']
                ),
                'core_values' => self::normalizeCoreValues(
                    $source['core_values'] ?? [],
                    $base['core_values'] ?? $defaults['core_values'],
                    $defaults['core_values']
                ),
            ]),
            'logo-and-symbols' => array_merge($section, [
                'lead' => self::pickString($source, $base, $defaults, 'lead', 4000),
                'identity_marks' => self::normalizeTitleBodyCards(
                    $source['identity_marks'] ?? [],
                    $base['identity_marks'] ?? $defaults['identity_marks'],
                    $defaults['identity_marks']
                ),
                'symbol_points' => self::normalizeTextList(
                    $source['symbol_points'] ?? ($source['symbol_points_text'] ?? []),
                    $base['symbol_points'] ?? $defaults['symbol_points'],
                    $defaults['symbol_points']
                ),
            ]),
            'hymn' => array_merge($section, [
                'lead' => self::pickString($source, $base, $defaults, 'lead', 4000),
                'hymn_sections' => self::normalizeTitleBodyCards(
                    $source['hymn_sections'] ?? [],
                    $base['hymn_sections'] ?? $defaults['hymn_sections'],
                    $defaults['hymn_sections']
                ),
            ]),
            'maps' => array_merge($section, [
                'lead' => self::pickString($source, $base, $defaults, 'lead', 4000),
                'map_url' => self::pickString($source, $base, $defaults, 'map_url', 2048),
                'visit_planning_text' => self::pickString($source, $base, $defaults, 'visit_planning_text', 4000),
                'map_cards' => self::normalizeTitleBodyCards(
                    $source['map_cards'] ?? [],
                    $base['map_cards'] ?? $defaults['map_cards'],
                    $defaults['map_cards']
                ),
                'visit_notes' => self::normalizeTextList(
                    $source['visit_notes'] ?? ($source['visit_notes_text'] ?? []),
                    $base['visit_notes'] ?? $defaults['visit_notes'],
                    $defaults['visit_notes']
                ),
            ]),
            'campus-officials' => array_merge($section, [
                'lead' => self::pickString($source, $base, $defaults, 'lead', 4000),
                'official_groups' => self::normalizeOfficialGroups(
                    $source['official_groups'] ?? [],
                    $base['official_groups'] ?? $defaults['official_groups'],
                    $defaults['official_groups']
                ),
                'officials_note' => self::pickString($source, $base, $defaults, 'officials_note', 4000),
            ]),
            'strategic-development-plan' => array_merge($section, [
                'lead' => self::pickString($source, $base, $defaults, 'lead', 4000),
                'development_priorities' => self::normalizeDynamicTitleBodyCards(
                    $source['development_priorities'] ?? [],
                    $base['development_priorities'] ?? $defaults['development_priorities'],
                    $defaults['development_priorities']
                ),
                'plan_principles' => self::normalizeTextList(
                    $source['plan_principles'] ?? ($source['plan_principles_text'] ?? []),
                    $base['plan_principles'] ?? $defaults['plan_principles'],
                    $defaults['plan_principles']
                ),
            ]),
            default => $section,
        };
    }

    private static function normalizeTimeline(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values($base);
        $items = [];

        foreach ($defaults as $index => $defaultItem) {
            $source = is_array($sourceItems[$index] ?? null) ? $sourceItems[$index] : [];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;
            $rawBody = $source['body'] ?? ($source['body_text'] ?? ($baseItem['body'] ?? []));

            $items[] = [
                'visible' => self::pickFlag($source, $baseItem, $defaultItem + ['visible' => '1'], 'visible'),
                'period' => self::pickString($source, $baseItem, $defaultItem, 'period'),
                'title' => self::pickString($source, $baseItem, $defaultItem, 'title'),
                'body' => self::normalizeParagraphs($rawBody, $baseItem['body'] ?? $defaultItem['body'], $defaultItem['body']),
            ];
        }

        return $items;
    }

    private static function normalizeStrategicGoals(mixed $input, array $base, array $defaults): array
    {
        $sourceGroups = is_array($input) ? array_values($input) : [];
        $baseGroups = array_values($base);
        $effectiveGroups = !empty($sourceGroups)
            ? $sourceGroups
            : (!empty($baseGroups) ? $baseGroups : $defaults);

        $groups = [];

        foreach ($effectiveGroups as $index => $groupCandidate) {
            $source = is_array($sourceGroups[$index] ?? null)
                ? $sourceGroups[$index]
                : (is_array($groupCandidate) ? $groupCandidate : []);
            $defaultGroup = is_array($defaults[$index] ?? null)
                ? $defaults[$index]
                : ['pillar' => 'Pillar '.($index + 1), 'title' => '', 'goals' => []];
            $baseGroup = is_array($baseGroups[$index] ?? null) ? $baseGroups[$index] : $defaultGroup;

            $sourceGoals = is_array($source['goals'] ?? null) ? array_values($source['goals']) : [];
            $baseGoals = array_values(is_array($baseGroup['goals'] ?? null) ? $baseGroup['goals'] : []);
            $defaultGoals = array_values(is_array($defaultGroup['goals'] ?? null) ? $defaultGroup['goals'] : []);
            $effectiveGoals = !empty($sourceGoals)
                ? $sourceGoals
                : (!empty($baseGoals) ? $baseGoals : $defaultGoals);
            $goals = [];

            foreach ($effectiveGoals as $goalIndex => $goalCandidate) {
                $goalSource = is_array($sourceGoals[$goalIndex] ?? null)
                    ? $sourceGoals[$goalIndex]
                    : (is_array($goalCandidate) ? $goalCandidate : []);
                $defaultGoal = is_array($defaultGoals[$goalIndex] ?? null)
                    ? $defaultGoals[$goalIndex]
                    : ['number' => (string) ($goalIndex + 1), 'text' => ''];
                $goalBase = is_array($baseGoals[$goalIndex] ?? null) ? $baseGoals[$goalIndex] : $defaultGoal;
                $goalNumber = trim(self::pickString($goalSource, $goalBase, $defaultGoal, 'number'));

                $goals[] = [
                    'number' => $goalNumber !== '' ? $goalNumber : (string) ($goalIndex + 1),
                    'text' => self::pickString($goalSource, $goalBase, $defaultGoal, 'text', 4000),
                ];
            }

            if (empty($goals)) {
                $goals[] = [
                    'number' => '1',
                    'text' => '',
                ];
            }

            $pillar = trim(self::pickString($source, $baseGroup, $defaultGroup, 'pillar'));

            $groups[] = [
                'pillar' => $pillar !== '' ? $pillar : 'Pillar '.($index + 1),
                'title' => self::pickString($source, $baseGroup, $defaultGroup, 'title'),
                'goals' => $goals,
            ];
        }

        return empty($groups) ? $defaults : $groups;
    }

    private static function normalizeCoreValues(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values($base);
        $items = [];

        foreach ($defaults as $index => $defaultItem) {
            $source = is_array($sourceItems[$index] ?? null) ? $sourceItems[$index] : [];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

            $items[] = [
                'letter' => self::pickString($source, $baseItem, $defaultItem, 'letter'),
                'title' => self::pickString($source, $baseItem, $defaultItem, 'title'),
            ];
        }

        return $items;
    }

    private static function normalizeTitleBodyCards(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values($base);
        $items = [];

        $defaultCount = count($defaults);
        $itemCount = max($defaultCount, count($sourceItems), count($baseItems));

        for ($index = 0; $index < $itemCount; $index++) {
            $defaultItem = is_array($defaults[$index] ?? null)
                ? $defaults[$index]
                : ['title' => '', 'body' => '', 'image' => ''];
            $source = is_array($sourceItems[$index] ?? null) ? $sourceItems[$index] : [];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

            $item = [
                'title' => self::pickString($source, $baseItem, $defaultItem, 'title'),
                'body' => self::pickString($source, $baseItem, $defaultItem, 'body', 6000),
                'image' => self::pickString($source, $baseItem, $defaultItem + ['image' => ''], 'image', 2048),
            ];

            if (
                $index >= $defaultCount
                && trim((string) ($item['title'] ?? '')) === ''
                && trim((string) ($item['body'] ?? '')) === ''
                && trim((string) ($item['image'] ?? '')) === ''
            ) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    private static function normalizeDynamicTitleBodyCards(mixed $input, array $base, array $defaults): array
    {
        if (!is_array($input)) {
            return self::normalizeTitleBodyCards($input, $base, $defaults);
        }

        $sourceItems = array_values($input);
        $items = [];

        foreach ($sourceItems as $index => $source) {
            if (!is_array($source)) {
                continue;
            }

            $item = [
                'title' => self::sanitizeString((string) ($source['title'] ?? ''), 255, ''),
                'body' => self::sanitizeString((string) ($source['body'] ?? ''), 6000, ''),
                'image' => self::sanitizeString((string) ($source['image'] ?? ''), 2048, ''),
            ];

            if (
                trim((string) ($item['title'] ?? '')) === ''
                && trim((string) ($item['body'] ?? '')) === ''
                && trim((string) ($item['image'] ?? '')) === ''
            ) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    private static function normalizeOfficialGroups(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values($base);
        $defaultItems = array_values($defaults);
        $items = [];

        if (!empty($sourceItems)) {
            foreach ($sourceItems as $index => $source) {
                if (!is_array($source)) {
                    continue;
                }

                $defaultItem = is_array($defaultItems[$index] ?? null)
                    ? $defaultItems[$index]
                    : ['name' => '', 'title' => '', 'body' => '', 'image' => '', 'order' => $index + 1];
                $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

                $item = [
                    'name' => self::pickString($source, $baseItem, $defaultItem + ['name' => ''], 'name'),
                    'title' => self::pickString($source, $baseItem, $defaultItem + ['title' => ''], 'title'),
                    'body' => self::pickString($source, $baseItem, $defaultItem + ['body' => ''], 'body', 6000),
                    'image' => self::pickString($source, $baseItem, $defaultItem + ['image' => ''], 'image', 2048),
                    'order' => self::normalizePositiveInt($source['order'] ?? ($baseItem['order'] ?? ($index + 1)), $index + 1),
                    '__position' => $index,
                ];

                if (
                    trim((string) $item['name']) === ''
                    && trim((string) $item['title']) === ''
                    && trim((string) $item['body']) === ''
                    && trim((string) $item['image']) === ''
                ) {
                    continue;
                }

                $items[] = $item;
            }
        }

        if (empty($items)) {
            foreach ($defaultItems as $index => $defaultItem) {
                $source = is_array($sourceItems[$index] ?? null) ? $sourceItems[$index] : [];
                $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

                $items[] = [
                    'name' => self::pickString($source, $baseItem, $defaultItem + ['name' => ''], 'name'),
                    'title' => self::pickString($source, $baseItem, $defaultItem + ['title' => ''], 'title'),
                    'body' => self::pickString($source, $baseItem, $defaultItem + ['body' => ''], 'body', 6000),
                    'image' => self::pickString($source, $baseItem, $defaultItem + ['image' => ''], 'image', 2048),
                    'order' => self::normalizePositiveInt($source['order'] ?? ($baseItem['order'] ?? ($index + 1)), $index + 1),
                    '__position' => $index,
                ];
            }
        }

        usort($items, static function (array $left, array $right): int {
            $orderCompare = ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0));

            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return ((int) ($left['__position'] ?? 0)) <=> ((int) ($right['__position'] ?? 0));
        });

        $items = array_map(static function (array $item): array {
            unset($item['__position']);

            return $item;
        }, $items);

        return $items;
    }

    private static function normalizePositiveInt(mixed $value, int $fallback): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) || is_float($value)) {
            $normalized = (int) trim((string) $value);
            if ($normalized > 0) {
                return $normalized;
            }
        }

        return max(1, $fallback);
    }

    private static function normalizeParagraphs(mixed $input, array $base, array $defaults): array
    {
        $items = [];

        if (is_string($input)) {
            $parts = preg_split("/\R{2,}/", trim($input)) ?: [];
            foreach ($parts as $part) {
                $text = trim((string) $part);
                if ($text !== '') {
                    $items[] = self::sanitizeString($text, 8000, '');
                }
            }
        } elseif (is_array($input)) {
            foreach ($input as $part) {
                $text = trim((string) $part);
                if ($text !== '') {
                    $items[] = self::sanitizeString($text, 8000, '');
                }
            }
        }

        if ($items !== []) {
            return $items;
        }

        return self::normalizeTextList($base, $defaults, $defaults);
    }

    private static function normalizeTextList(mixed $input, array $base, array $defaults): array
    {
        $items = [];

        if (is_string($input)) {
            $parts = preg_split("/\R+/", trim($input)) ?: [];
            foreach ($parts as $part) {
                $text = trim((string) $part);
                if ($text !== '') {
                    $items[] = self::sanitizeString($text, 4000, '');
                }
            }
        } elseif (is_array($input)) {
            foreach ($input as $part) {
                $text = trim((string) $part);
                if ($text !== '') {
                    $items[] = self::sanitizeString($text, 4000, '');
                }
            }
        }

        if ($items !== []) {
            return $items;
        }

        $fallback = [];
        foreach ($base as $part) {
            $text = trim((string) $part);
            if ($text !== '') {
                $fallback[] = self::sanitizeString($text, 4000, '');
            }
        }

        if ($fallback !== []) {
            return $fallback;
        }

        $defaultItems = [];
        foreach ($defaults as $part) {
            $text = trim((string) $part);
            if ($text !== '') {
                $defaultItems[] = self::sanitizeString($text, 4000, '');
            }
        }

        return $defaultItems;
    }

    private static function pickString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        if (array_key_exists($key, $source)) {
            return self::sanitizeOptionalString((string) $source[$key], $maxLen);
        }

        $value = $base[$key] ?? ($defaults[$key] ?? '');

        return self::sanitizeString((string) $value, $maxLen, (string) ($defaults[$key] ?? ''));
    }

    private static function pickOptionalString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        if (array_key_exists($key, $source)) {
            return self::sanitizeOptionalString((string) $source[$key], $maxLen);
        }

        return self::pickString($source, $base, $defaults, $key, $maxLen);
    }

    private static function pickFlag(array $source, array $base, array $defaults, string $key): string
    {
        $value = $source[$key] ?? ($base[$key] ?? ($defaults[$key] ?? '1'));
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['0', 'false', 'off', 'no'], true) ? '0' : '1';
    }

    private static function sanitizeString(string $value, int $maxLen, string $fallback): string
    {
        $text = trim(HtmlEntities::decode($value));

        if ($text === '') {
            $text = trim(HtmlEntities::decode($fallback));
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }

    private static function sanitizeOptionalString(string $value, int $maxLen): string
    {
        $text = trim(HtmlEntities::decode($value));

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }

    private static function normalizeCardImage(string $path): string
    {
        $normalized = trim($path);

        if (in_array($normalized, self::LEGACY_CARD_IMAGES, true)) {
            return self::DEFAULT_CARD_IMAGE;
        }

        return $normalized;
    }
}
