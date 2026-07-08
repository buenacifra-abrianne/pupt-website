<?php

namespace App\Support;

class AcademicsCmsContent
{
    private const DEFAULTS = [
        'hero' => [
            'image' => 'assets/static_img/about_header_image.png',
            'title' => 'ACADEMICS',
        ],
        'contents' => [
            'tag' => 'Contents',
            'items' => [
                [
                    'label' => 'Degree Programs',
                    'summary' => 'Discover a wide range of undergraduate majors and minors designed to prepare you for professional success.',
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.degree-programs',
                ],
                [
                    'label' => 'Diploma Programs',
                    'summary' => 'Gain practical skills and specialized knowledge through diploma courses tailored for career readiness.',
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.diploma-programs',
                ],
                [
                    'label' => 'PUP iApply',
                    'summary' => "Easily access the university's online application portal to start your academic journey.",
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.pup-iapply',
                ],
                [
                    'label' => 'University Calendar',
                    'summary' => 'Stay updated with important academic schedules, events, and deadlines throughout the school year.',
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.university-calendar',
                ],
            ],
        ],
        'intro' => [
            'body' => '<p><strong>Quality and relevant education</strong> that responds to the call of present times in building the <strong>foundations of the future.</strong></p><p>Ranging from high school to doctoral courses, traditional to nontraditional education system, <strong>the University makes it possible</strong> that <strong>deserving individuals can have access</strong> to these academic resources.</p><p>The University has always been making <strong>initiatives to enrich its academic programs</strong> in various fields of study and <strong>implement an educational strategy</strong> designed to provide our students with highly employable, managerial, and entrepreneurial skills in order to make them exceedingly <strong>creative, productive, competitive, and self-reliant</strong>.</p>',
        ],
        'pages' => [
            'degree-programs' => [
                'hero' => [
                    'tag' => 'PUP Taguig Branch',
                    'title' => 'Degree Programs',
                    'subtitle' => 'Academic Year 2024-2025',
                    'body' => 'Pursue excellence through our CHED-accredited degree programs designed to prepare you for professional success. PUP Taguig Branch offers quality education rooted in science, technology, and professional practice.',
                    'list_title' => 'Offered Colleges',
                    'list_items' => [
                        'College of Engineering',
                        'College of Accountancy and Finance',
                        'College of Business Administration',
                        'College of Science',
                        'College of Education',
                        'College of Office Administration and Business Education',
                        'College of Social Sciences and Development',
                    ],
                    'image' => 'assets/static_img/pupillar.jpeg',
                ],
                'info' => [
                    'tag' => 'Quick Info',
                    'title' => 'Program Admission at a Glance',
                    'items' => [
                        ['label' => 'Program Type', 'value' => 'CHED-Accredited BS & AB Degrees', 'href' => ''],
                        ['label' => 'Mode of Study', 'value' => 'Face-to-face / Blended Learning', 'href' => ''],
                        ['label' => 'Location', 'value' => 'PUP Taguig Branch, Gen. Santos Ave., Taguig City', 'href' => ''],
                    ],
                ],
                'cards' => [
                    'tag' => 'Academic Offerings',
                    'title' => "Bachelor's Degree Programs",
                    'higher_education_pdf_url' => 'storage/downloadables/tZMWTIG2M7fQw7T03gKtb0c0c14rlBaoPU0kjaI1.pdf',
                    'items' => [
                        ['title' => 'BS Electronics Engineering', 'badge' => 'BSECE', 'dept' => 'College of Engineering', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'A rigorous program covering circuits, communications, and embedded systems. Graduates are equipped to sit for the ECE Licensure Examination.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsece', 'cta' => 'View Program'],
                        ['title' => 'BS Mechanical Engineering', 'badge' => 'BSME', 'dept' => 'College of Engineering', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Covers thermodynamics, fluid mechanics, and machine design. Prepares students for the Mechanical Engineering Licensure Exam.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsme', 'cta' => 'View Program'],
                        ['title' => 'BS Accountancy', 'badge' => 'BSA', 'dept' => 'College of Accountancy and Finance', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'A comprehensive program in financial reporting, auditing, and taxation. Aligned with the CPA Licensure Examination competencies.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsa', 'cta' => 'View Program'],
                        ['title' => 'BS Business Administration', 'badge' => 'BSBA', 'dept' => 'College of Business Administration', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Offered with majors in Human Resource Development Management and Marketing Management.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsba', 'cta' => 'View Program'],
                        ['title' => 'BS Applied Mathematics', 'badge' => 'BSAM', 'dept' => 'College of Science', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Develops strong analytical and problem-solving skills for careers in data science, finance, and research.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsam', 'cta' => 'View Program'],
                        ['title' => 'BS Information Technology', 'badge' => 'BSIT', 'dept' => 'College of Science', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Covers software development, networking, and database systems. Prepares students for the IT Licensure Examination.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsit', 'cta' => 'View Program'],
                        ['title' => 'BS Entrepreneurship', 'badge' => 'BSENTREP', 'dept' => 'College of Business Administration', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Equips students with the mindset and skills to launch and manage successful ventures in the Philippine and global market.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsentrep', 'cta' => 'View Program'],
                        ['title' => 'Bachelor in Secondary Education', 'badge' => 'BSED', 'dept' => 'College of Education', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Offered with majors in English and Mathematics. Aligned with the LET competency standards.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsed', 'cta' => 'View Program'],
                        ['title' => 'BS Office Administration', 'badge' => 'BSOA', 'dept' => 'College of Office Administration and Business Education', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Trains students in records management, office systems, and administrative operations for both public and private sectors.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bsoa', 'cta' => 'View Program'],
                        ['title' => 'BS Psychology', 'badge' => 'BSPSY', 'dept' => 'College of Social Sciences and Development', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Studies human behavior, mental processes, and psychological assessment. Prepares students for careers in counseling, HR, and research.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#bspsy', 'cta' => 'View Program'],
                    ],
                ],
            ],
            'diploma-programs' => [
                'hero' => [
                    'tag' => 'PUP Taguig Branch',
                    'title' => 'Diploma Programs',
                    'subtitle' => 'Academic Year 2024-2025',
                    'body' => 'Gain practical, career-ready skills through our diploma programs designed for students who seek focused, industry-relevant training. PUP Taguig Branch offers CHED-recognized diploma courses that open pathways to employment and further study.',
                    'list_title' => 'Offered Departments',
                    'list_items' => [
                        'Department of Information and Communications Technology',
                        'Department of Office Administration',
                    ],
                    'image' => 'assets/static_img/pupillar.jpeg',
                ],
                'info' => [
                    'tag' => 'Quick Info',
                    'title' => 'Program Admission at a Glance',
                    'items' => [
                        ['label' => 'Program Type', 'value' => 'CHED-Recognized Diploma Courses', 'href' => ''],
                        ['label' => 'Mode of Study', 'value' => 'Face-to-face / Blended Learning', 'href' => ''],
                        ['label' => 'Location', 'value' => 'PUP Taguig Branch, Gen. Santos Ave., Taguig City', 'href' => ''],
                    ],
                ],
                'cards' => [
                    'tag' => 'Academic Offerings',
                    'title' => 'Diploma Programs',
                    'higher_education_pdf_url' => 'storage/downloadables/tZMWTIG2M7fQw7T03gKtb0c0c14rlBaoPU0kjaI1.pdf',
                    'items' => [
                        ['title' => 'Diploma in Information Communication Technology', 'badge' => 'DICT', 'dept' => 'Dept. of Information & Communications Technology', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'A focused program covering computer systems, networking, and digital communications. Prepares graduates for technical roles in the ICT industry.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#dict', 'cta' => 'View Program'],
                        ['title' => 'Diploma in Office Management Technology', 'badge' => 'DOMT', 'dept' => 'Dept. of Office Administration', 'accreditation_levels' => '', 'accrediting_institution' => '', 'accreditation_validity' => '', 'accreditation_validity_start' => '', 'accreditation_validity_end' => '', 'body' => 'Covers office procedures, records management, and business communications. Equips students for administrative and clerical careers in various industries.', 'image' => 'assets/static_img/pupillar.jpeg', 'href' => '#domt', 'cta' => 'View Program'],
                    ],
                ],
            ],
            'pup-iapply' => [
                'hero' => [
                    'tag' => 'Admissions',
                    'title' => 'PUP iApply',
                    'subtitle' => 'CAEPUP - College Admission Evaluation of PUP',
                    'body' => "PUP iApply (formerly PUPCET iApply), a Web-based Registration System, streamlines the University's ability to develop, deploy, and operate a massive admission process in a more efficient method, lower its costs of operation, and deliver a more efficient and reliable ICT-enabled system that effectively works for the community.",
                    'list_title' => 'System Benefits',
                    'list_items' => [
                        'Apply anytime at their convenience;',
                        'Save on cumulative expenses;',
                        'Save on time and energy; and',
                        'Verify status of application anytime.',
                    ],
                    'image' => '',
                    'visual_title' => 'Ready to Apply?',
                    'visual_body' => 'Enable applicants to register for University college admission evaluation and entrance exams online.',
                    'cta_label' => 'Apply Now ↗',
                    'cta_href' => 'https://iapply.pup.edu.ph/signin',
                ],
                'schedule' => [
                    'tag' => 'Schedule & Key Dates',
                    'title' => 'Branch Campus - Taguig City',
                    'items' => [
                        ['label' => 'Online Application', 'value' => '2022-01-27', 'href' => ''],
                        ['label' => 'Last Day of Issuance', 'value' => '2022-06-15', 'href' => ''],
                        ['label' => 'Evaluation Result', 'value' => '2022-06-15', 'href' => ''],
                    ],
                ],
                'guide' => [
                    'tag' => 'How to Apply',
                    'title' => 'Step-by-step CAEPUP Application Guide',
                    'description' => 'Online application for the College Admission Evaluation of PUP #CAEPUP for the First Semester, Academic Year 2022-2023.',
                    'video_url' => 'https://www.youtube.com/embed/A7Ed_9_nB50',
                ],
                'reminders' => [
                    'tag' => 'Before You Apply',
                    'title' => 'Important - Please Read Carefully',
                    'notice_title' => 'Reminders',
                    'notice_items' => [
                        'Once your online application is finalized, no more editing of application.',
                        'Multiple accounts and multiple applications are grounds for disqualification.',
                        'Wrong entry of information and grades are grounds for disqualification.',
                    ],
                    'body_html' => '<p><strong>Note:</strong> For general admission requirements, please read the Specific Academic Program Criteria.</p><p>Before you apply online, please make sure that you have the following files on your device or USB drive <em>(each file size must not be more than 300 kilobytes / KB)</em>:</p>',
                    'steps' => [
                        "Applicant's photo (JPEG file - read photo guidelines)",
                        'Grades in English, Math, Science and General Weighted Average (GWA) in Grade 10; and Grades in all subjects in Grade 11 and GWA in Grade 11.',
                        'Scanned Grade 10 Report Card (JPEG file)',
                        'Scanned Grade 11 Report Card (JPEG file)',
                        'Report Cards must clearly show your complete name, LRN, grades in English, Math, Science and GWA.',
                    ],
                    'checklist_items' => [
                        "Applicant's photo (JPEG file - read photo guidelines)",
                        'Grades in English, Math, Science and General Weighted Average (GWA) in Grade 10; and Grades in all subjects in Grade 11 and GWA in Grade 11.',
                        'Scanned Grade 10 Report Card (JPEG file)',
                        'Scanned Grade 11 Report Card (JPEG file)',
                        'Report Cards must clearly show your complete name, LRN, grades in English, Math, Science and GWA.',
                    ],
                ],
            ],
            'university-calendar' => [
                'hero' => [
                    'tag' => 'PUP Taguig Branch',
                    'title' => 'University Calendar',
                    'subtitle' => 'Academic Year 2024-2025',
                    'body' => "Stay on top of your academic journey with PUP Taguig's official university calendar. Find important dates including enrollment periods, class schedules, holidays, examinations, and university-wide events.",
                    'list_title' => 'Key Dates Include',
                    'list_items' => [
                        'Enrollment & Registration periods',
                        'Start and end of classes',
                        'Midterm & Final examination schedules',
                        'Regular & special holidays',
                        'University events & activities',
                    ],
                    'image' => 'assets/static_img/campus_photo.jpg',
                ],
                'info' => [
                    'tag' => '',
                    'title' => 'Academic Year 2024-2025',
                    'items' => [
                        ['label' => '1st Semester', 'value' => 'August - December 2024', 'href' => ''],
                        ['label' => '2nd Semester', 'value' => 'January - May 2025', 'href' => ''],
                        ['label' => 'Summer', 'value' => 'June - July 2025', 'href' => ''],
                        ['label' => 'Issued By', 'value' => 'Office of the University Registrar', 'href' => ''],
                        ['label' => 'Official Source', 'value' => 'pup.edu.ph →', 'href' => 'https://www.pup.edu.ph'],
                    ],
                ],
                'calendar' => [
                    'tag' => 'Official Calendar',
                    'title' => 'University Academic Calendar',
                    'pdf_url' => 'https://www.pup.edu.ph/about/calendar',
                    'note' => 'If the embedded calendar does not load on your browser or device, open the official PUP calendar in a new tab below.',
                    'actions' => [
                        ['label' => 'Open Official Calendar', 'href' => 'https://www.pup.edu.ph/about/calendar', 'style' => 'primary', 'download' => false],
                    ],
                ],
            ],
        ],
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
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

        if (isset($decoded['academics']) && is_array($decoded['academics'])) {
            $decoded = $decoded['academics'];
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

    public static function syncCalendarPdfReferences(array $calendar, string $nextPath, ?string $previousPath = null): array
    {
        $resolvedPath = trim($nextPath);

        if ($resolvedPath === '') {
            return $calendar;
        }

        $currentPdfPath = trim((string) ($calendar['pdf_url'] ?? ''));
        $replaceablePaths = array_values(array_unique(array_filter([
            trim((string) $previousPath),
            $currentPdfPath,
            'assets/static_img/university_calendar.pdf',
        ], static fn ($value) => is_string($value) && trim($value) !== '')));

        $calendar['pdf_url'] = $resolvedPath;
        $actions = is_array($calendar['actions'] ?? null) ? array_values($calendar['actions']) : [];

        foreach ($actions as $index => $action) {
            if (!is_array($action)) {
                continue;
            }

            $href = trim((string) ($action['href'] ?? ''));
            if ($href === '' || in_array($href, $replaceablePaths, true)) {
                $actions[$index]['href'] = $resolvedPath;
            }
        }

        $calendar['actions'] = $actions;

        return $calendar;
    }

    private static function normalize(array $source, array $base): array
    {
        $defaults = self::defaults();

        return [
            'hero' => self::normalizeHero(
                is_array($source['hero'] ?? null) ? $source['hero'] : [],
                is_array($base['hero'] ?? null) ? $base['hero'] : $defaults['hero'],
                $defaults['hero']
            ),
            'contents' => self::normalizeContents(
                is_array($source['contents'] ?? null) ? $source['contents'] : [],
                is_array($base['contents'] ?? null) ? $base['contents'] : $defaults['contents'],
                $defaults['contents']
            ),
            'intro' => self::normalizeIntro(
                is_array($source['intro'] ?? null) ? $source['intro'] : [],
                is_array($base['intro'] ?? null) ? $base['intro'] : $defaults['intro'],
                $defaults['intro']
            ),

            'pages' => self::normalizePages(
                is_array($source['pages'] ?? null) ? $source['pages'] : [],
                is_array($base['pages'] ?? null) ? $base['pages'] : $defaults['pages'],
                $defaults['pages']
            ),
        ];
    }

    private static function normalizeHero(array $source, array $base, array $defaults): array
    {
        return [
            'image' => self::pickOptionalString($source, $base, $defaults, 'image', 2048),
            'title' => self::pickString($source, $base, $defaults, 'title'),
        ];
    }

    private static function normalizeContents(array $source, array $base, array $defaults): array
    {
        return [
            'tag' => self::pickString($source, $base, $defaults, 'tag', 80),
            'items' => self::normalizeContentsItems(
                $source['items'] ?? [],
                $base['items'] ?? $defaults['items'],
                $defaults['items']
            ),
        ];
    }

    private static function normalizeIntro(array $source, array $base, array $defaults): array
    {
        return [
            'body' => self::pickString($source, $base, $defaults, 'body', 20000),
        ];
    }


    private static function normalizeContentsItems(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values(is_array($base) ? $base : []);
        $defaultItems = array_values(is_array($defaults) ? $defaults : []);
        $itemsSource = $sourceItems !== [] ? $sourceItems : $baseItems;
        $items = [];

        foreach ($itemsSource as $index => $source) {
            if (!is_array($source)) {
                continue;
            }

            $defaultItem = is_array($defaultItems[$index] ?? null)
                ? $defaultItems[$index]
                : ['label' => '', 'summary' => '', 'image' => '', 'route' => 'public.academics'];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

            $normalized = [
                'label' => self::pickString($source, $baseItem, $defaultItem, 'label'),
                'summary' => self::pickString($source, $baseItem, $defaultItem, 'summary', 4000),
                'image' => self::pickOptionalString($source, $baseItem, $defaultItem, 'image', 2048),
                'route' => self::pickString($source, $baseItem, $defaultItem, 'route', 255),
            ];

            if ($normalized['route'] === 'public.graduate-programs') {
                continue;
            }

            if (
                trim($normalized['label']) === ''
                && trim($normalized['summary']) === ''
                && trim($normalized['image']) === ''
            ) {
                continue;
            }

            $items[] = $normalized;

            if (count($items) >= 24) {
                break;
            }
        }

        return $items;
    }


    private static function normalizePages(array $source, array $base, array $defaults): array
    {
        $pages = [];

        foreach ($defaults as $pageKey => $pageDefaults) {
            $sourcePage = is_array($source[$pageKey] ?? null) ? $source[$pageKey] : [];
            $basePage = is_array($base[$pageKey] ?? null) ? $base[$pageKey] : $pageDefaults;
            $pages[$pageKey] = self::mergePageTree($sourcePage, $basePage, $pageDefaults);

            if ($pageKey === 'pup-iapply') {
                $pages[$pageKey]['reminders'] = self::normalizePupIApplyReminders(
                    is_array($sourcePage['reminders'] ?? null) ? $sourcePage['reminders'] : [],
                    is_array($basePage['reminders'] ?? null) ? $basePage['reminders'] : (is_array($pageDefaults['reminders'] ?? null) ? $pageDefaults['reminders'] : []),
                    is_array($pageDefaults['reminders'] ?? null) ? $pageDefaults['reminders'] : []
                );
            }
        }

        return $pages;
    }

    private static function normalizePupIApplyReminders(array $source, array $base, array $defaults): array
    {
        $noticeDefaults = is_array($defaults['notice_items'] ?? null) ? $defaults['notice_items'] : [];
        $noticeBase = is_array($base['notice_items'] ?? null) ? $base['notice_items'] : $noticeDefaults;
        $noticeSource = is_array($source['notice_items'] ?? null) ? $source['notice_items'] : [];

        $stepDefaults = is_array($defaults['steps'] ?? null)
            ? $defaults['steps']
            : (is_array($defaults['checklist_items'] ?? null) ? $defaults['checklist_items'] : []);
        $stepBase = is_array($base['steps'] ?? null)
            ? $base['steps']
            : (is_array($base['checklist_items'] ?? null) ? $base['checklist_items'] : $stepDefaults);
        $stepSource = array_key_exists('steps', $source)
            ? (is_array($source['steps'] ?? null) ? $source['steps'] : [])
            : (is_array($source['checklist_items'] ?? null) ? $source['checklist_items'] : []);

        $steps = self::normalizeFixedStringList($stepSource, $stepBase, $stepDefaults, 5, 2048);

        return [
            'tag' => self::pickString($source, $base, $defaults, 'tag', 120),
            'title' => self::pickString($source, $base, $defaults, 'title'),
            'notice_title' => self::pickString($source, $base, $defaults, 'notice_title'),
            'notice_items' => self::normalizeFixedStringList($noticeSource, $noticeBase, $noticeDefaults, 3, 2048),
            'body_html' => self::pickString($source, $base, $defaults, 'body_html', 20000),
            'steps' => $steps,
            'checklist_items' => $steps,
        ];
    }

    private static function normalizeFixedStringList(mixed $source, mixed $base, mixed $defaults, int $count, int $maxLen): array
    {
        $sourceItems = array_values(is_array($source) ? $source : []);
        $baseItems = array_values(is_array($base) ? $base : []);
        $defaultItems = array_values(is_array($defaults) ? $defaults : []);
        $items = [];

        for ($index = 0; $index < $count; $index++) {
            $fallback = (string) ($defaultItems[$index] ?? '');
            $candidate = $sourceItems[$index] ?? null;

            if ($candidate === null || (is_string($candidate) && trim($candidate) === '')) {
                $candidate = $baseItems[$index] ?? null;
            }

            if ($candidate === null || (is_string($candidate) && trim($candidate) === '')) {
                $candidate = $fallback;
            }

            $items[] = self::sanitizeString((string) $candidate, $maxLen, $fallback);
        }

        return $items;
    }

    private static function mergePageTree(mixed $source, mixed $base, mixed $defaults): mixed
    {
        if (!is_array($defaults)) {
            if (is_bool($defaults)) {
                if ($source !== null && $source !== '') {
                    return filter_var($source, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $defaults;
                }

                return is_bool($base) ? $base : $defaults;
            }

            $candidate = $source;
            if ($candidate === null || (is_string($candidate) && trim($candidate) === '')) {
                $candidate = $base;
            }
            if ($candidate === null || (is_string($candidate) && trim($candidate) === '')) {
                $candidate = $defaults;
            }

            return is_string($candidate) ? trim($candidate) : (string) $candidate;
        }

        $sourceArray = is_array($source) ? $source : [];
        $baseArray = is_array($base) ? $base : [];

        if (array_is_list($defaults)) {
            $itemsSource = $sourceArray !== [] ? $sourceArray : ($baseArray !== [] ? $baseArray : $defaults);
            $items = [];

            foreach (array_values($itemsSource) as $index => $item) {
                $defaultItem = $defaults[$index] ?? ($defaults[0] ?? []);
                $baseItem = $baseArray[$index] ?? $defaultItem;
                $items[] = self::mergePageTree($item, $baseItem, $defaultItem);
            }

            return $items;
        }

        $merged = [];
        foreach ($defaults as $key => $defaultValue) {
            $merged[$key] = self::mergePageTree(
                $sourceArray[$key] ?? null,
                $baseArray[$key] ?? null,
                $defaultValue
            );
        }

        return $merged;
    }

    private static function pickString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        $value = $source[$key] ?? ($base[$key] ?? ($defaults[$key] ?? ''));

        return self::sanitizeString((string) $value, $maxLen, (string) ($defaults[$key] ?? ''));
    }

    private static function pickOptionalString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        if (array_key_exists($key, $source)) {
            return self::sanitizeOptionalString((string) $source[$key], $maxLen);
        }

        return self::pickString($source, $base, $defaults, $key, $maxLen);
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
}
