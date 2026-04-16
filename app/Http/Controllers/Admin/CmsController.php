<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AcademicsCmsContent;
use App\Support\AboutCmsContent;
use App\Support\AuditLog;
use App\Support\CmsSections;
use App\Support\EventsCmsContent;
use App\Support\HomeCmsContent;
use App\Support\ResearchCmsContent;
use App\Support\StudentsCmsContent;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CmsController extends Controller
{
    public function page()
    {
        $roles = session('user_roles', [session('user_role')]);
        $allowedTabs = CmsSections::tabsForRoles($roles);

        if (empty($allowedTabs)) {
            abort(403, 'No CMS tabs are assigned to your role.');
        }

        $tabDefs = CmsSections::tabDefinitions($allowedTabs);
        $contentsByTab = $this->loadContents($allowedTabs);
        $pendingByTab = $this->loadPendingCountsByTab();
        $totalPending = array_sum($pendingByTab);
        $totalLiveContents = $this->countTotalContents($allowedTabs);

        return view('admin.content', [
            'tabDefs' => $tabDefs,
            'allowedTabs' => $allowedTabs,
            'contentsByTab' => $contentsByTab,
            'pendingByTab' => $pendingByTab,
            'totalPending' => $totalPending,
            'totalLiveContents' => $totalLiveContents,
            'homePreviewNews' => $this->loadHomePreviewNews(),
            'homePreviewAnnouncements' => $this->loadHomePreviewAnnouncements(),
        ]);
    }

    public function save(Request $request)
    {
        $roles = session('user_roles', [session('user_role')]);
        $allowedTabs = CmsSections::tabsForRoles($roles);

        $data = $request->validate([
            'tab_key' => ['required', Rule::in($allowedTabs)],
            'section_key' => ['nullable', Rule::in(array_merge([
                'description', 'carousel', 'updates', 'quick_links', 'feedback', 'hero', 'intro', 'contents',
                'vision-mission-header', 'vision-mission-statements', 'strategic-goals', 'core-values', 'features',
                'page', 'cards', 'organizations',
            ], AboutCmsContent::sectionSlugs()))],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'home_quick_links_version' => ['nullable'],
            'home_active_quick_link_index' => ['nullable'],
            'home_feedback_questions_version' => ['nullable'],
            'home_active_feedback_question_index' => ['nullable'],
            'about_contents_version' => ['nullable'],
            'about_active_contents_slug' => ['nullable', 'string'],
            'academics_contents_version' => ['nullable'],
            'academics_active_contents_index' => ['nullable'],
            'academics_features_version' => ['nullable'],
            'academics_active_feature_index' => ['nullable'],
            'students_cards_version' => ['nullable'],
            'students_active_card_index' => ['nullable'],
            'students_active_org_key' => ['nullable', 'string'],
            'research_cards_version' => ['nullable'],
            'research_active_card_index' => ['nullable'],
            'events_cards_version' => ['nullable'],
            'home' => ['nullable', 'array'],
            'about' => ['nullable', 'array'],
            'academics' => ['nullable', 'array'],
            'students' => ['nullable', 'array'],
            'research' => ['nullable', 'array'],
            'events' => ['nullable', 'array'],
            'about.sections' => ['nullable', 'array'],
            'about.sections.*.visible_in_contents' => ['nullable'],
            'about.sections.*.image' => ['nullable', 'string', 'max:2048'],
            'about.sections.*.image_file' => ['nullable', 'image', 'max:5120'],
            'home.campus_description' => ['nullable', 'string'],
            'home.campus_image' => ['nullable', 'string', 'max:2048'],
            'home.campus_image_file' => ['nullable', 'image', 'max:5120'],
            'home.hero' => ['nullable', 'array'],
            'home.hero.crest_heading' => ['nullable', 'string', 'max:255'],
            'home.hero.crest_year' => ['nullable', 'string', 'max:50'],
            'home.updates' => ['nullable', 'array'],
            'home.updates.tag' => ['nullable', 'string', 'max:80'],
            'home.updates.title' => ['nullable', 'string', 'max:255'],
            'home.updates.description' => ['nullable', 'string'],
            'home.quick_links' => ['nullable', 'array'],
            'home.quick_links.tag' => ['nullable', 'string', 'max:80'],
            'home.quick_links.title' => ['nullable', 'string', 'max:255'],
            'home.quick_links.items' => ['nullable', 'array'],
            'home.quick_links.items.*.label' => ['nullable', 'string', 'max:255'],
            'home.quick_links.items.*.title' => ['nullable', 'string', 'max:255'],
            'home.quick_links.items.*.body' => ['nullable', 'string'],
            'home.quick_links.items.*.href' => ['nullable', 'string', 'max:2048'],
            'home.feedback' => ['nullable', 'array'],
            'home.feedback.tag' => ['nullable', 'string', 'max:80'],
            'home.feedback.title' => ['nullable', 'string', 'max:255'],
            'home.feedback.description' => ['nullable', 'string'],
            'home.feedback.button_label' => ['nullable', 'string', 'max:120'],
            'home.feedback.questions' => ['nullable', 'array', 'max:10'],
            'home.feedback.questions.*.question' => ['nullable', 'string'],
            'home.carousel' => ['nullable', 'array'],
            'home.carousel.*.title' => ['nullable', 'string', 'max:255'],
            'home.carousel.*.subtitle' => ['nullable', 'string', 'max:255'],
            'home.carousel.*.image' => ['nullable', 'string', 'max:2048'],
            'home.carousel.*.image_file' => ['nullable', 'image', 'max:5120'],
            'academics.hero' => ['nullable', 'array'],
            'academics.hero.title' => ['nullable', 'string', 'max:255'],
            'academics.hero.image' => ['nullable', 'string', 'max:2048'],
            'academics.contents' => ['nullable', 'array'],
            'academics.contents.tag' => ['nullable', 'string', 'max:80'],
            'academics.contents.items' => ['nullable', 'array'],
            'academics.contents.items.*.label' => ['nullable', 'string', 'max:255'],
            'academics.contents.items.*.summary' => ['nullable', 'string'],
            'academics.contents.items.*.image' => ['nullable', 'string', 'max:2048'],
            'academics.contents.items.*.image_file' => ['nullable', 'image', 'max:5120'],
            'academics.intro' => ['nullable', 'array'],
            'academics.intro.body' => ['nullable', 'string'],
            'academics.features' => ['nullable', 'array'],
            'academics.features.eyebrow' => ['nullable', 'string', 'max:120'],
            'academics.features.items' => ['nullable', 'array'],
            'academics.features.items.*.title' => ['nullable', 'string', 'max:255'],
            'academics.features.items.*.body' => ['nullable', 'string'],
            'research.page' => ['nullable', 'array'],
            'research.page.eyebrow' => ['nullable', 'string', 'max:120'],
            'research.page.title' => ['nullable', 'string', 'max:255'],
            'research.page.description' => ['nullable', 'string'],
            'research.cards' => ['nullable', 'array'],
            'research.cards.*.title' => ['nullable', 'string', 'max:255'],
            'research.cards.*.description' => ['nullable', 'string'],
            'research.cards.*.link' => ['nullable', 'string', 'max:2048'],
            'research.cards.*.image' => ['nullable', 'string', 'max:2048'],
            'research.cards.*.image_file' => ['nullable', 'image', 'max:5120'],
            'students.page' => ['nullable', 'array'],
            'students.page.eyebrow' => ['nullable', 'string', 'max:120'],
            'students.page.title' => ['nullable', 'string', 'max:255'],
            'students.page.description' => ['nullable', 'string'],
            'students.page.hero_image' => ['nullable', 'string', 'max:2048'],
            'students.cards' => ['nullable', 'array'],
            'students.cards.*.title' => ['nullable', 'string', 'max:255'],
            'students.cards.*.description' => ['nullable', 'string'],
            'students.cards.*.link' => ['nullable', 'string', 'max:2048'],
            'students.cards.*.image' => ['nullable', 'string', 'max:2048'],
            'students.cards.*.image_file' => ['nullable', 'image', 'max:5120'],
            'students.organization_sections' => ['nullable', 'array'],
            'students.organization_sections.*.title' => ['nullable', 'string', 'max:255'],
            'students.organization_sections.*.items' => ['nullable', 'array'],
            'students.organization_sections.*.items.*.title' => ['nullable', 'string', 'max:255'],
            'students.organization_sections.*.items.*.abbr' => ['nullable', 'string', 'max:255'],
            'students.organization_sections.*.items.*.link' => ['nullable', 'string', 'max:2048'],
            'students.organization_sections.*.items.*.image' => ['nullable', 'string', 'max:2048'],
            'students.organization_sections.*.items.*.image_file' => ['nullable', 'image', 'max:5120'],
            'events.page' => ['nullable', 'array'],
            'events.page.eyebrow' => ['nullable', 'string', 'max:120'],
            'events.page.title' => ['nullable', 'string', 'max:255'],
            'events.page.description' => ['nullable', 'string'],
            'events.cards' => ['nullable', 'array'],
            'events.cards.*.title' => ['nullable', 'string', 'max:255'],
            'events.cards.*.summary' => ['nullable', 'string'],
            'events.cards.*.content' => ['nullable', 'string'],
            'events.cards.*.image' => ['nullable', 'string', 'max:2048'],
            'events.cards.*.image_file' => ['nullable', 'image', 'max:5120'],
            'events.cards.*.location' => ['nullable', 'string', 'max:255'],
            'events.cards.*.event_date' => ['nullable', 'date_format:Y-m-d'],
            'events.cards.*.start_time' => ['nullable', 'date_format:H:i'],
            'events.cards.*.end_time' => ['nullable', 'date_format:H:i'],
            'events.cards.*.category' => ['nullable', Rule::in(array_keys(EventsCmsContent::categoryOptions()))],
            'events.cards.*.featured' => ['nullable'],
        ]);

        if (!Schema::hasTable('cms_contents')) {
            return response()->json([
                'ok' => false,
                'message' => 'cms_contents table not found. Please run migrations first.',
            ], 422);
        }

        $tabKey = (string) $data['tab_key'];
        $tabLabel = CmsSections::labelForTab($tabKey);
        $sectionKey = in_array($tabKey, ['home', 'about', 'academics', 'students', 'research_extension', 'events'], true)
            ? strtolower(trim((string) ($data['section_key'] ?? '')))
            : '';
        $sectionLabel = match ($tabKey) {
            'home' => $this->homeSectionLabel($sectionKey),
            'about' => $this->aboutSectionLabel($sectionKey),
            'academics' => $this->academicsSectionLabel($sectionKey),
            'students' => $this->studentsSectionLabel($sectionKey),
            'research_extension' => $this->researchSectionLabel($sectionKey),
            'events' => $this->eventsSectionLabel($sectionKey),
            default => '',
        };

        $existing = DB::table('cms_contents')
            ->where('tab_key', $tabKey)
            ->first();

        $currentTitle = trim((string) ($existing->title ?? ''));
        if ($currentTitle === '') {
            $currentTitle = $tabLabel.' Content';
        }

        $currentContent = (string) ($existing->content ?? '');
        $title = trim((string) ($data['title'] ?? ''));
        $content = (string) ($data['content'] ?? '');

        if ($tabKey === 'home') {
            $baseHome = HomeCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseHomeEncoded = HomeCmsContent::encode($baseHome);
            $homeInput = $this->filterHomeInputBySection(
                is_array($data['home'] ?? null) ? $data['home'] : [],
                $sectionKey
            );

            if ($sectionKey === '' || $sectionKey === 'description') {
                $campusImageUpload = $request->file('home.campus_image_file');
                if ($campusImageUpload instanceof UploadedFile) {
                    $homeInput['campus_image'] = $campusImageUpload->store('home/description', 'public');
                }
            }

            if ($sectionKey === '' || $sectionKey === 'carousel') {
                $carouselUploads = $request->file('home.carousel', []);

                if (is_array($carouselUploads)) {
                    foreach ($carouselUploads as $index => $slideUpload) {
                        $upload = is_array($slideUpload) ? ($slideUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $homeInput['carousel'][$index]['image'] = $upload->store('home/carousel', 'public');
                    }
                }
            }

            $content = HomeCmsContent::encode(
                HomeCmsContent::fromInput($homeInput, $baseHomeEncoded)
            );
            $title = $currentTitle;
            $currentContent = $baseHomeEncoded;
        } elseif ($tabKey === 'about') {
            $baseAbout = AboutCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseAboutEncoded = AboutCmsContent::encode($baseAbout);
            $aboutInput = $this->filterAboutInputBySection(
                is_array($data['about'] ?? null) ? $data['about'] : [],
                $sectionKey
            );

            if ($sectionKey === '' || $sectionKey === 'contents') {
                $sectionUploads = $request->file('about.sections', []);

                if (is_array($sectionUploads)) {
                    foreach ($sectionUploads as $slug => $sectionUpload) {
                        $upload = is_array($sectionUpload) ? ($sectionUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $aboutInput['sections'][$slug]['image'] = $upload->store('about/sections', 'public');
                    }
                }
            }

            $content = AboutCmsContent::encode(
                AboutCmsContent::fromInput($aboutInput, $baseAboutEncoded)
            );
            $title = $currentTitle;
            $currentContent = $baseAboutEncoded;
        } elseif ($tabKey === 'academics') {
            $baseAcademics = AcademicsCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseAcademicsEncoded = AcademicsCmsContent::encode($baseAcademics);
            $academicsInput = $this->filterAcademicsInputBySection(
                is_array($data['academics'] ?? null) ? $data['academics'] : [],
                $sectionKey
            );

            $academicsUploads = $request->file('academics.contents.items', []);
            if (is_array($academicsUploads)) {
                foreach ($academicsUploads as $index => $itemUpload) {
                    $upload = is_array($itemUpload) ? ($itemUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $academicsInput['contents']['items'][$index]['image'] = $upload->store('academics/contents', 'public');
                }
            }

            $content = AcademicsCmsContent::encode(
                AcademicsCmsContent::fromInput($academicsInput, $baseAcademicsEncoded)
            );
            $title = $currentTitle;
            $currentContent = $baseAcademicsEncoded;
        } elseif ($tabKey === 'students') {
            $baseStudents = StudentsCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseStudentsEncoded = StudentsCmsContent::encode($baseStudents);
            $studentsInput = $this->filterStudentsInputBySection(
                is_array($data['students'] ?? null) ? $data['students'] : [],
                $sectionKey
            );

            $studentCardUploads = $request->file('students.cards', []);
            if (is_array($studentCardUploads)) {
                foreach ($studentCardUploads as $index => $cardUpload) {
                    $upload = is_array($cardUpload) ? ($cardUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $studentsInput['cards'][$index]['image'] = $upload->store('students/cards', 'public');
                }
            }

            $organizationUploads = $request->file('students.organization_sections', []);
            if (is_array($organizationUploads)) {
                foreach ($organizationUploads as $sectionIndex => $sectionUpload) {
                    $itemUploads = is_array($sectionUpload) ? ($sectionUpload['items'] ?? []) : [];
                    if (!is_array($itemUploads)) {
                        continue;
                    }

                    foreach ($itemUploads as $orgIndex => $itemUpload) {
                        $upload = is_array($itemUpload) ? ($itemUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $studentsInput['organization_sections'][$sectionIndex]['items'][$orgIndex]['image'] = $upload->store('students/organizations', 'public');
                    }
                }
            }

            $content = StudentsCmsContent::encode(
                $sectionKey === 'cards'
                    ? StudentsCmsContent::fromCardsInput($studentsInput['cards'] ?? [], $baseStudentsEncoded)
                    : ($sectionKey === 'organizations'
                        ? StudentsCmsContent::fromOrganizationsInput($studentsInput['organization_sections'] ?? [], $baseStudentsEncoded)
                        : StudentsCmsContent::fromInput($studentsInput, $baseStudentsEncoded))
            );
            $title = $currentTitle;
            $currentContent = $baseStudentsEncoded;
        } elseif ($tabKey === 'research_extension') {
            $baseResearch = ResearchCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseResearchEncoded = ResearchCmsContent::encode($baseResearch);
            $researchInput = $this->filterResearchInputBySection(
                is_array($data['research'] ?? null) ? $data['research'] : [],
                $sectionKey
            );

            $researchCardUploads = $request->file('research.cards', []);
            if (is_array($researchCardUploads)) {
                foreach ($researchCardUploads as $index => $cardUpload) {
                    $upload = is_array($cardUpload) ? ($cardUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $researchInput['cards'][$index]['image'] = $upload->store('research/cards', 'public');
                }
            }

            $content = ResearchCmsContent::encode(
                $sectionKey === 'cards'
                    ? ResearchCmsContent::fromCardsInput($researchInput['cards'] ?? [], $baseResearchEncoded)
                    : ResearchCmsContent::fromInput($researchInput, $baseResearchEncoded)
            );
            $title = $currentTitle;
            $currentContent = $baseResearchEncoded;
        } elseif ($tabKey === 'events') {
            $baseEvents = EventsCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseEventsEncoded = EventsCmsContent::encode($baseEvents);
            $eventsInput = $this->filterEventsInputBySection(
                is_array($data['events'] ?? null) ? $data['events'] : [],
                $sectionKey
            );

            $eventUploads = $request->file('events.cards', []);
            if (is_array($eventUploads)) {
                foreach ($eventUploads as $index => $cardUpload) {
                    $upload = is_array($cardUpload) ? ($cardUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $eventsInput['cards'][$index]['image'] = $upload->store('events/cards', 'public');
                }
            }

            $content = EventsCmsContent::encode(
                $sectionKey === 'cards'
                    ? EventsCmsContent::fromCardsInput($eventsInput['cards'] ?? [], $baseEventsEncoded)
                    : EventsCmsContent::fromInput($eventsInput, $baseEventsEncoded)
            );
            $title = $currentTitle;
            $currentContent = $baseEventsEncoded;
        } elseif ($title === '') {
            $title = $tabLabel.' Content';
        }

        if ($title === $currentTitle && $content === $currentContent) {
            return response()->json([
                'ok' => true,
                'no_changes' => true,
                'message' => 'No changes detected.',
            ]);
        }

        if ($existing) {
            DB::table('cms_contents')
                ->where('tab_key', $tabKey)
                ->update([
                    'title' => $title,
                    'content' => $content,
                    'updated_by' => (int) (session('user_id') ?? 0),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('cms_contents')->insert([
                'tab_key' => $tabKey,
                'title' => $title,
                'content' => $content,
                'updated_by' => (int) (session('user_id') ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $auditMessage = 'Updated '.$tabLabel.' content directly as admin.';
        if (in_array($tabKey, ['home', 'about', 'academics', 'students', 'research_extension', 'events'], true) && $sectionLabel !== '') {
            $auditMessage = 'Updated '.$tabLabel.' content ('.$sectionLabel.') directly as admin.';
        }

        AuditLog::record(
            'UPDATED',
            'CONTENT',
            $auditMessage,
            (int) (session('user_id') ?? 0)
        );

        $successMessage = $tabLabel.' content saved successfully.';
        if (in_array($tabKey, ['home', 'about', 'academics', 'students', 'research_extension', 'events'], true) && $sectionLabel !== '') {
            $successMessage = $tabLabel.' '.$sectionLabel.' saved successfully.';
        }

        return response()->json([
            'ok' => true,
            'message' => $successMessage,
        ]);
    }

    private function homeSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'description' => 'Description',
            'carousel' => 'Hero Carousel',
            'updates' => 'Campus Updates',
            'quick_links' => 'Explore Section',
            'feedback' => 'Feedback Banner',
            default => '',
        };
    }

    private function filterHomeInputBySection(array $homeInput, string $sectionKey): array
    {
        if ($sectionKey === '') {
            return $homeInput;
        }

        $allowed = match ($sectionKey) {
            'description' => ['campus_title', 'campus_description', 'campus_image'],
            'carousel' => ['hero', 'carousel', 'carousel_slides'],
            'updates' => ['updates'],
            'quick_links' => ['quick_links'],
            'feedback' => ['feedback'],
            default => array_keys($homeInput),
        };

        return array_intersect_key($homeInput, array_flip($allowed));
    }

    private function aboutSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'hero' => 'Hero',
            'intro' => 'Intro',
            'contents' => 'Contents',
            'history' => 'History',
            'vision-mission-header' => 'Vision and Mission Header',
            'vision-mission-statements' => 'Vision and Mission Statements',
            'strategic-goals' => 'Strategic Goals',
            'core-values' => 'Core Values',
            'vision-and-mission' => 'Vision and Mission',
            'logo-and-symbols' => 'Logo and Symbols',
            'hymn' => 'Hymn',
            'maps' => 'Maps',
            'campus-officials' => 'Campus Officials',
            'strategic-development-plan' => 'Strategic Development Plan',
            default => '',
        };
    }

    private function academicsSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'hero' => 'Hero',
            'contents' => 'Contents',
            'intro' => 'Intro',
            'features' => 'What We Offer',
            default => '',
        };
    }

    private function studentsSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'page' => 'Page Header',
            'cards' => 'Cards',
            'organizations' => 'Organizations',
            default => '',
        };
    }

    private function researchSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'page' => 'Page Header',
            'cards' => 'Cards',
            default => '',
        };
    }

    private function eventsSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'page' => 'Page Header',
            'cards' => 'Event Listings',
            default => '',
        };
    }

    private function filterAboutInputBySection(array $aboutInput, string $sectionKey): array
    {
        if ($sectionKey === '' || $sectionKey === 'all') {
            return $aboutInput;
        }

        $overview = is_array($aboutInput['overview'] ?? null) ? $aboutInput['overview'] : [];
        $sections = is_array($aboutInput['sections'] ?? null) ? $aboutInput['sections'] : [];
        $visionSection = is_array($sections['vision-and-mission'] ?? null) ? $sections['vision-and-mission'] : [];

        return match ($sectionKey) {
            'hero' => [
                'overview' => array_intersect_key($overview, array_flip(['hero_image', 'hero_title_default', 'hero_title_history', 'hero_title_vision', 'section_header_image'])),
            ],
            'intro' => [
                'overview' => array_intersect_key($overview, array_flip(['story_tag', 'story_title', 'story_description'])),
            ],
            'contents' => [
                'overview' => array_intersect_key($overview, array_flip(['contents_tag', 'contents_title'])),
                'sections' => collect($sections)
                    ->map(fn ($section) => is_array($section)
                        ? array_intersect_key($section, array_flip(['label', 'summary', 'image', 'visible_in_contents']))
                        : [])
                    ->all(),
            ],
            'vision-mission-header' => [
                'sections' => [
                    'vision-and-mission' => array_intersect_key($visionSection, array_flip(['page_kicker', 'page_title'])),
                ],
            ],
            'vision-mission-statements' => [
                'sections' => [
                    'vision-and-mission' => array_intersect_key($visionSection, array_flip(['vision', 'mission'])),
                ],
            ],
            'strategic-goals' => [
                'sections' => [
                    'vision-and-mission' => array_intersect_key($visionSection, array_flip(['strategic_goals'])),
                ],
            ],
            'core-values' => [
                'sections' => [
                    'vision-and-mission' => array_intersect_key($visionSection, array_flip(['core_values'])),
                ],
            ],
            default => [
                'sections' => isset($sections[$sectionKey]) && is_array($sections[$sectionKey])
                    ? [$sectionKey => $sections[$sectionKey]]
                    : [],
            ],
        };
    }

    private function filterAcademicsInputBySection(array $academicsInput, string $sectionKey): array
    {
        if ($sectionKey === '' || $sectionKey === 'all') {
            return $academicsInput;
        }

        return match ($sectionKey) {
            'hero' => [
                'hero' => is_array($academicsInput['hero'] ?? null)
                    ? array_intersect_key($academicsInput['hero'], array_flip(['title', 'image']))
                    : [],
            ],
            'contents' => [
                'contents' => [
                    'tag' => (string) data_get($academicsInput, 'contents.tag', ''),
                    'items' => collect(data_get($academicsInput, 'contents.items', []))
                        ->map(fn ($item) => is_array($item)
                            ? array_intersect_key($item, array_flip(['label', 'summary', 'image']))
                            : [])
                        ->all(),
                ],
            ],
            'intro' => [
                'intro' => is_array($academicsInput['intro'] ?? null)
                    ? array_intersect_key($academicsInput['intro'], array_flip(['body']))
                    : [],
            ],
            'features' => [
                'features' => [
                    'eyebrow' => (string) data_get($academicsInput, 'features.eyebrow', ''),
                    'items' => collect(data_get($academicsInput, 'features.items', []))
                        ->map(fn ($item) => is_array($item)
                            ? array_intersect_key($item, array_flip(['title', 'body']))
                            : [])
                        ->all(),
                ],
            ],
            default => $academicsInput,
        };
    }

    private function filterStudentsInputBySection(array $studentsInput, string $sectionKey): array
    {
        if ($sectionKey === '' || $sectionKey === 'all') {
            return $studentsInput;
        }

        return match ($sectionKey) {
            'page' => [
                'page' => is_array($studentsInput['page'] ?? null)
                    ? array_intersect_key($studentsInput['page'], array_flip(['eyebrow', 'title', 'description', 'hero_image']))
                    : [],
            ],
            'cards' => [
                'cards' => collect(data_get($studentsInput, 'cards', []))
                    ->map(fn ($item) => is_array($item)
                        ? array_intersect_key($item, array_flip(['title', 'description', 'link', 'image']))
                        : [])
                    ->all(),
            ],
            'organizations' => [
                'organization_sections' => collect(data_get($studentsInput, 'organization_sections', []))
                    ->map(function ($section) {
                        if (!is_array($section)) {
                            return [];
                        }

                        return [
                            'title' => (string) ($section['title'] ?? ''),
                            'items' => collect(data_get($section, 'items', []))
                                ->map(fn ($item) => is_array($item)
                                    ? array_intersect_key($item, array_flip(['title', 'abbr', 'link', 'image']))
                                    : [])
                                ->all(),
                        ];
                    })
                    ->all(),
            ],
            default => $studentsInput,
        };
    }

    private function filterResearchInputBySection(array $researchInput, string $sectionKey): array
    {
        if ($sectionKey === '' || $sectionKey === 'all') {
            return $researchInput;
        }

        return match ($sectionKey) {
            'page' => [
                'page' => is_array($researchInput['page'] ?? null)
                    ? array_intersect_key($researchInput['page'], array_flip(['eyebrow', 'title', 'description']))
                    : [],
            ],
            'cards' => [
                'cards' => collect(data_get($researchInput, 'cards', []))
                    ->map(fn ($item) => is_array($item)
                        ? array_intersect_key($item, array_flip(['title', 'description', 'link', 'image']))
                        : [])
                    ->all(),
            ],
            default => $researchInput,
        };
    }

    private function filterEventsInputBySection(array $eventsInput, string $sectionKey): array
    {
        if ($sectionKey === '' || $sectionKey === 'all') {
            return $eventsInput;
        }

        return match ($sectionKey) {
            'page' => [
                'page' => is_array($eventsInput['page'] ?? null)
                    ? array_intersect_key($eventsInput['page'], array_flip(['eyebrow', 'title', 'description']))
                    : [],
            ],
            'cards' => [
                'cards' => collect(data_get($eventsInput, 'cards', []))
                    ->map(fn ($item) => is_array($item)
                        ? array_intersect_key($item, array_flip([
                            'title',
                            'summary',
                            'content',
                            'image',
                            'location',
                            'event_date',
                            'start_time',
                            'end_time',
                            'category',
                            'featured',
                        ]))
                        : [])
                    ->all(),
            ],
            default => $eventsInput,
        };
    }

    private function loadContents(array $tabKeys): array
    {
        $defaults = [];
        foreach ($tabKeys as $tabKey) {
            $defaults[$tabKey] = [
                'title' => CmsSections::labelForTab($tabKey).' Content',
                'content' => '',
            ];
        }

        if (!Schema::hasTable('cms_contents')) {
            return $defaults;
        }

        $rows = DB::table('cms_contents')
            ->whereIn('tab_key', $tabKeys)
            ->get()
            ->keyBy('tab_key');

        foreach ($tabKeys as $tabKey) {
            $row = $rows->get($tabKey);
            if (!$row) {
                continue;
            }

            $defaults[$tabKey] = [
                'title' => trim((string) ($row->title ?? '')) !== ''
                    ? (string) $row->title
                    : CmsSections::labelForTab($tabKey).' Content',
                'content' => (string) ($row->content ?? ''),
            ];
        }

        return $defaults;
    }

    private function loadPendingCountsByTab(): array
    {
        $out = [];
        foreach (CmsSections::allTabKeys() as $tabKey) {
            $out[$tabKey] = 0;
        }

        $types = [];
        foreach (CmsSections::allTabKeys() as $tabKey) {
            $type = CmsSections::requestTypeForTab($tabKey);
            if ($type !== null) {
                $types[] = $type;
            }
        }

        if (empty($types)) {
            return $out;
        }

        $rows = DB::table('approval_requests')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->where('status', 'pending')
            ->whereIn('type', $types)
            ->groupBy('type')
            ->get();

        foreach ($rows as $row) {
            $tabKey = CmsSections::tabForRequestType((string) ($row->type ?? ''));
            if ($tabKey === null) {
                continue;
            }

            $out[$tabKey] = (int) ($row->total ?? 0);
        }

        return $out;
    }

    private function countTotalContents(array $tabKeys): int
    {
        return count($tabKeys);
    }

    private function loadHomePreviewAnnouncements()
    {
        return DB::table('announcements')
            ->select('announcement_id', 'title', 'content', 'link', 'priority', 'date_published', 'created_at')
            ->whereRaw("UPPER(TRIM(status)) = 'ENABLED'")
            ->orderByRaw("
                CASE
                    WHEN UPPER(TRIM(priority)) = 'HIGH' THEN 0
                    WHEN UPPER(TRIM(priority)) = 'MEDIUM' THEN 1
                    WHEN UPPER(TRIM(priority)) = 'LOW' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw("COALESCE(date_published, created_at) DESC")
            ->limit(10)
            ->get();
    }

    private function loadHomePreviewNews()
    {
        return DB::table('news')
            ->select('news_id', 'title', 'content', 'category', 'location', 'image_path', 'priority', 'date_published', 'created_at')
            ->whereRaw("UPPER(TRIM(status)) = 'APPROVED'")
            ->orderByRaw("
                CASE
                    WHEN UPPER(TRIM(priority)) = 'HIGH' THEN 0
                    WHEN UPPER(TRIM(priority)) = 'MEDIUM' THEN 1
                    WHEN UPPER(TRIM(priority)) = 'LOW' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('date_published')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
}
