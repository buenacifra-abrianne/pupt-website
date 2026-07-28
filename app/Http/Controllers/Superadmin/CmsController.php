<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\AcademicsCmsContent;
use App\Support\AboutCmsContent;
use App\Support\AuditLog;
use App\Support\CmsSections;
use App\Support\CmsUploadLimits;
use App\Support\DownloadableFile;
use App\Support\EventsCmsCardValidation;
use App\Support\EventsCmsContent;
use App\Support\HomeCmsContent;
use App\Support\ImageStorage;
use App\Support\ResearchCmsContent;
use App\Support\StudentsCmsContent;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CmsController extends Controller
{
    use \App\Support\CmsPreviewHandler;

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

        return view('superadmin.content', [
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
                'description', 'carousel', 'updates', 'campus_tour_video', 'campus_tour_facilities', 'quick_links', 'feedback', 'hero', 'intro', 'contents', 'philosophy',
                'vision-mission-header', 'vision-statement', 'mission-statement', 'vision-mission-statements', 'strategic-goals', 'core-values', 'features',
                'page', 'cards_header', 'cards', 'organizations',
                'admissions_page', 'admissions_hero', 'admissions_instructions', 'admissions_contact', 'admissions_contact_offices', 'admissions_contact_persons', 'admissions_links', 'admissions_form_links',
                'document_requests_hero', 'document_requests_qr_codes', 'document_requests_qr_codes_header', 'document_requests_qr_codes_items',
                'downloadable_forms_page', 'downloadable_forms_hero', 'downloadable_forms_links', 'downloadable_forms_items',
                'degree-programs-hero', 'degree-programs-info', 'degree-programs-cards', 'degree-programs-contact',
                'diploma-programs-hero', 'diploma-programs-info', 'diploma-programs-cards', 'diploma-programs-contact',
                'pup-iapply-hero', 'pup-iapply-schedule', 'pup-iapply-guide', 'pup-iapply-reminders',
                'university-calendar-hero', 'university-calendar-info', 'university-calendar-calendar',
                'strategic_development_plan',
            ], AboutCmsContent::sectionSlugs()))],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'home_quick_links_version' => ['nullable'],
            'home_active_quick_link_index' => ['nullable'],
            'home_feedback_questions_version' => ['nullable'],
            'home_active_feedback_question_index' => ['nullable'],
            'about_intro_version' => ['nullable'],
            'about_philosophy_version' => ['nullable'],
            'about_contents_version' => ['nullable'],
            'about_active_contents_slug' => ['nullable', 'string'],
            'about_history_version' => ['nullable'],
            'about_active_history_index' => ['nullable'],
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
            'about.sections.*.label' => ['nullable', 'string', 'max:255'],
            'about.sections.*.summary' => ['nullable', 'string'],
            'about.sections.*.lead' => ['nullable', 'string'],
            'about.sections.*.visible_in_contents' => ['nullable'],
            'about.sections.*.image' => ['nullable', 'string', 'max:2048'],
            'about.sections.*.image_file' => ['nullable', 'image', 'max:5120'],
            'about.sections.*.page_kicker' => ['nullable', 'string', 'max:255'],
            'about.sections.*.page_title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.vision' => ['nullable', 'string'],
            'about.sections.*.mission' => ['nullable', 'string'],
            'about.sections.*.strategic_goals' => ['nullable', 'array'],
            'about.sections.*.strategic_goals.*.pillar' => ['nullable', 'string', 'max:255'],
            'about.sections.*.strategic_goals.*.title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.strategic_goals.*.goals' => ['nullable', 'array'],
            'about.sections.*.strategic_goals.*.goals.*.number' => ['nullable', 'string', 'max:255'],
            'about.sections.*.strategic_goals.*.goals.*.text' => ['nullable', 'string'],
            'about.sections.*.core_values' => ['nullable', 'array'],
            'about.sections.*.core_values.*.letter' => ['nullable', 'string', 'max:10'],
            'about.sections.*.core_values.*.title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.timeline' => ['nullable', 'array'],
            'about.sections.*.timeline.*.visible' => ['nullable'],
            'about.sections.*.timeline.*.period' => ['nullable', 'string', 'max:255'],
            'about.sections.*.timeline.*.title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.timeline.*.body' => ['nullable'],
            'about.sections.*.timeline.*.body_text' => ['nullable', 'string'],
            'about.sections.*.official_groups' => ['nullable', 'array'],
            'about.sections.*.official_groups.*.name' => ['nullable', 'string', 'max:255'],
            'about.sections.*.official_groups.*.title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.official_groups.*.body' => ['nullable', 'string'],
            'about.sections.*.official_groups.*.image' => ['nullable', 'string', 'max:2048'],
            'about.sections.*.official_groups.*.image_file' => ['nullable', 'image', 'max:5120'],
            'about.sections.*.organizational_chart_image' => ['nullable', 'string', 'max:2048'],
            'about.sections.*.organizational_chart_image_file' => ['nullable', 'image', 'max:5120'],
            'about.sections.*.seals' => ['nullable', 'array'],
            'about.sections.*.seals.*.id' => ['nullable', 'string', 'max:120'],
            'about.sections.*.seals.*.label' => ['nullable', 'string', 'max:255'],
            'about.sections.*.seals.*.tag' => ['nullable', 'string', 'max:120'],
            'about.sections.*.seals.*.image' => ['nullable', 'string', 'max:2048'],
            'about.sections.*.seals.*.image_file' => ['nullable', 'image', 'max:5120'],
            'about.sections.*.seals.*.highlights' => ['nullable', 'array'],
            'about.sections.*.seals.*.highlights.*' => ['nullable', 'string', 'max:2048'],
            'about.sections.*.seals.*.information.title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.seals.*.information.description' => ['nullable', 'string'],
            'about.sections.*.seals.*.reports.title' => ['nullable', 'string', 'max:255'],
            'about.sections.*.seals.*.reports.description' => ['nullable', 'string'],
            'about.sections.*.seals.*.links' => ['nullable', 'array'],
            'about.sections.*.seals.*.links.*.label' => ['nullable', 'string', 'max:255'],
            'about.sections.*.seals.*.links.*.url' => ['nullable', 'string', 'max:2048'],
            'about.overview.story_tag' => ['nullable', 'string', 'max:255'],
            'about.overview.story_title' => ['nullable', 'string', 'max:255'],
            'about.overview.story_description' => ['nullable', 'string'],
            'about.overview.story_image' => ['nullable', 'string', 'max:2048'],
            'about.overview.story_image_file' => ['nullable', 'image', 'max:5120'],
            'about.overview.hero_image_file' => ['nullable', 'image', 'max:5120'],
            'about.overview.section_header_image_file' => ['nullable', 'image', 'max:5120'],
            'home.campus_description' => ['nullable', 'string'],
            'home.campus_image' => ['nullable', 'string', 'max:2048'],
            'home.campus_image_file' => ['nullable', 'image', 'max:5120'],
            'home.hero' => ['nullable', 'array'],
            'home.hero.crest_heading' => ['nullable', 'string', 'max:255'],
            'home.hero.crest_year' => ['nullable', 'string', 'max:50'],
            'home.updates' => ['nullable', 'array'],
            'home.updates.tag' => ['nullable', 'string', 'max:80'],
            'home.updates.title' => ['nullable', 'string', 'max:255'],
            'home.updates.description' => ['nullable', 'string', 'max:100'],
            'home.campus_tour' => ['nullable', 'array'],
            'home.campus_tour.avp_video' => ['nullable', 'string', 'max:2048'],
            'home.campus_tour.avp_video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:'.CmsUploadLimits::CAMPUS_AVP_MAX_KB],
            'home.campus_tour.facilities' => ['nullable', 'array', 'max:24'],
            'home.campus_tour.facilities.*.name' => ['nullable', 'string', 'max:255'],
            'home.campus_tour.facilities.*.image' => ['nullable', 'string', 'max:2048'],
            'home.campus_tour.facilities.*.image_file' => ['nullable', 'image', 'max:5120'],
            'home.quick_links' => ['nullable', 'array'],
            'home.quick_links.tag' => ['nullable', 'string', 'max:80'],
            'home.quick_links.title' => ['nullable', 'string', 'max:255'],
            'home.quick_links.description' => ['nullable', 'string'],
            'home.quick_links.items' => ['nullable', 'array'],
            'home.quick_links.items.*.label' => ['nullable', 'string', 'max:255'],
            'home.quick_links.items.*.title' => ['nullable', 'string', 'max:255'],
            'home.quick_links.items.*.body' => ['nullable', 'string'],
            'home.quick_links.items.*.href' => ['nullable', 'string', 'max:2048'],
            'home.feedback' => ['nullable', 'array'],
            'home.feedback.tag' => ['nullable', 'string', 'max:80'],
            'home.feedback.title' => ['nullable', 'string', 'max:255'],
            'home.feedback.description' => ['nullable', 'string', 'max:100'],
            'home.feedback.button_label' => ['nullable', 'string', 'max:120'],
            'home.feedback.questions' => ['nullable', 'array', 'max:10'],
            'home.feedback.questions.*.question' => ['nullable', 'string', 'max:255'],
            'home.carousel' => ['nullable', 'array'],
            'home.carousel.*.title' => ['nullable', 'string', 'max:255'],
            'home.carousel.*.subtitle' => ['nullable', 'string', 'max:255'],
            'home.carousel.*.image' => ['nullable', 'string', 'max:2048'],
            'home.carousel.*.image_file' => ['nullable', 'image', 'max:5120'],
            'academics.hero' => ['nullable', 'array'],
            'academics.hero.title' => ['nullable', 'string', 'max:255'],
            'academics.hero.image' => ['nullable', 'string', 'max:2048'],
            'academics.hero.image_file' => ['nullable', 'image', 'max:5120'],
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
            'academics.features.tag' => ['nullable', 'string', 'max:120'],
            'academics.features.title' => ['nullable', 'string', 'max:255'],
            'academics.features.description' => ['nullable', 'string'],
            'academics.features.eyebrow' => ['nullable', 'string', 'max:120'],
            'academics.features.items' => ['nullable', 'array'],
            'academics.features.items.*.tag' => ['nullable', 'string', 'max:120'],
            'academics.features.items.*.title' => ['nullable', 'string', 'max:255'],
            'academics.features.items.*.description' => ['nullable', 'string'],
            'academics.features.items.*.body' => ['nullable', 'string'],
            'academics.pages' => ['nullable', 'array'],
            'academics.pages.*.hero' => ['nullable', 'array'],
            'academics.pages.*.hero.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.hero.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.hero.subtitle' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.hero.body' => ['nullable', 'string'],
            'academics.pages.*.hero.list_title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.hero.list_items' => ['nullable', 'array'],
            'academics.pages.*.hero.list_items.*' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.hero.image' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.hero.image_file' => ['nullable', 'image', 'max:5120'],
            'academics.pages.*.hero.visual_title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.hero.visual_body' => ['nullable', 'string'],
            'academics.pages.*.hero.cta_label' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.hero.cta_href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.info' => ['nullable', 'array'],
            'academics.pages.*.info.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.info.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.ay_start_date' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.sem1_start' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.sem1_end' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.sem2_start' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.sem2_end' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.summer_start' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.summer_end' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.info.items' => ['nullable', 'array'],
            'academics.pages.*.info.items.*.label' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.info.items.*.value' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.info.items.*.href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.cards' => ['nullable', 'array'],
            'academics.pages.*.cards.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.cards.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.cards.higher_education_pdf_url' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.cards.items' => ['nullable', 'array'],
            'academics.pages.*.cards.items.*.badge' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.cards.items.*.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.cards.items.*.body' => ['nullable', 'string'],
            'academics.pages.*.cards.items.*.dept' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.cards.items.*.accreditation_levels' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.cards.items.*.accrediting_institution' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.cards.items.*.accreditation_validity' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.cards.items.*.accreditation_validity_start' => ['nullable', 'date_format:Y-m-d'],
            'academics.pages.*.cards.items.*.accreditation_validity_end' => ['nullable', 'date_format:Y-m-d'],
            'academics.pages.*.cards.items.*.image' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.cards.items.*.image_file' => ['nullable', 'image', 'max:5120'],
            'academics.pages.*.cards.items.*.href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.cards.items.*.cta' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.contact' => ['nullable', 'array'],
            'academics.pages.*.contact.campus_name' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.contact.campus_sub' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.contact.address' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.contact.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.contact.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.contact.description' => ['nullable', 'string'],
            'academics.pages.*.contact.rows' => ['nullable', 'array'],
            'academics.pages.*.contact.rows.*.label' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.contact.rows.*.value' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.contact.rows.*.href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.contact.rows.*.tone' => ['nullable', 'string', 'max:40'],
            'academics.pages.*.contact.cta_label' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.contact.cta_href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.schedule' => ['nullable', 'array'],
            'academics.pages.*.schedule.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.schedule.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.schedule.items' => ['nullable', 'array', 'max:3'],
            'academics.pages.*.schedule.items.*.label' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.schedule.items.*.value' => ['nullable', 'date_format:Y-m-d'],
            'academics.pages.*.schedule.items.*.href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.guide' => ['nullable', 'array'],
            'academics.pages.*.guide.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.guide.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.guide.description' => ['nullable', 'string'],
            'academics.pages.*.guide.video_url' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.reminders' => ['nullable', 'array'],
            'academics.pages.*.reminders.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.reminders.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.reminders.notice_title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.reminders.notice_items' => ['nullable', 'array'],
            'academics.pages.*.reminders.notice_items.*' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.reminders.body_html' => ['nullable', 'string'],
            'academics.pages.*.reminders.steps' => ['nullable', 'array', 'max:5'],
            'academics.pages.*.reminders.steps.*' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.reminders.checklist_items' => ['nullable', 'array'],
            'academics.pages.*.reminders.checklist_items.*' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.calendar' => ['nullable', 'array'],
            'academics.pages.*.calendar.tag' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.calendar.title' => ['nullable', 'string', 'max:255'],
            'academics.pages.*.calendar.pdf_url' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.calendar.pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'academics.pages.*.calendar.note' => ['nullable', 'string'],
            'academics.pages.*.calendar.actions' => ['nullable', 'array'],
            'academics.pages.*.calendar.actions.*.label' => ['nullable', 'string', 'max:120'],
            'academics.pages.*.calendar.actions.*.href' => ['nullable', 'string', 'max:2048'],
            'academics.pages.*.calendar.actions.*.style' => ['nullable', 'string', 'max:40'],
            'academics.pages.*.calendar.actions.*.download' => ['nullable'],
            'research.page' => ['nullable', 'array'],
            'research.page.eyebrow' => ['nullable', 'string', 'max:120'],
            'research.page.title' => ['nullable', 'string', 'max:255'],
            'research.page.description' => ['nullable', 'string'],
            'research.page.hero_image' => ['nullable', 'string', 'max:2048'],
            'research.page.hero_image_file' => ['nullable', 'image', 'max:5120'],
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
            'students.page.hero_image_file' => ['nullable', 'image', 'max:5120'],
            'students.page.contents_tag' => ['nullable', 'string', 'max:120'],
            'students.page.contents_title' => ['nullable', 'string', 'max:255'],
            'students.page.contents_description' => ['nullable', 'string'],
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
            'students.pages' => ['nullable', 'array'],
            'students.pages.*.hero' => ['nullable', 'array'],
            'students.pages.*.hero.tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.hero.title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.hero.subtitle' => ['nullable', 'string', 'max:255'],
            'students.pages.*.hero.body' => ['nullable', 'string'],
            'students.pages.*.hero.image' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.hero.image_file' => ['nullable', 'image', 'max:5120'],
            'students.pages.*.instructions' => ['nullable', 'array'],
            'students.pages.*.instructions.tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.instructions.title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.instructions.body' => ['nullable', 'string'],
            'students.pages.*.instructions.image' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.instructions.image_file' => ['nullable', 'image', 'max:5120'],
            'students.pages.*.contact' => ['nullable', 'array'],
            'students.pages.*.contact.tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.contact.title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.description' => ['nullable', 'string'],
            'students.pages.*.contact.offices_tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.contact.offices_title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.offices_description' => ['nullable', 'string'],
            'students.pages.*.contact.persons_tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.contact.persons_title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.persons_description' => ['nullable', 'string'],
            'students.pages.*.contact.offices' => ['nullable', 'array'],
            'students.pages.*.contact.offices.*.label' => ['nullable', 'string', 'max:120'],
            'students.pages.*.contact.offices.*.value' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.offices.*.href' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.contact.persons' => ['nullable', 'array'],
            'students.pages.*.contact.persons.*.name' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.persons.*.role' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.persons.*.email' => ['nullable', 'string', 'max:255'],
            'students.pages.*.contact.persons.*.href' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.contact.persons.*.image' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.contact.persons.*.image_file' => ['nullable', 'image', 'max:5120'],
            'students.pages.*.links' => ['nullable', 'array'],
            'students.pages.*.links.tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.links.title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.links.description' => ['nullable', 'string'],
            'students.pages.*.links.items' => ['nullable', 'array'],
            'students.pages.*.links.items.*.label' => ['nullable', 'string', 'max:255'],
            'students.pages.*.links.items.*.href' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.links.items.*.description' => ['nullable', 'string'],
            'students.pages.*.qr_codes' => ['nullable', 'array'],
            'students.pages.*.qr_codes.tag' => ['nullable', 'string', 'max:120'],
            'students.pages.*.qr_codes.title' => ['nullable', 'string', 'max:255'],
            'students.pages.*.qr_codes.items' => ['nullable', 'array'],
            'students.pages.*.qr_codes.items.*.label' => ['nullable', 'string', 'max:255'],
            'students.pages.*.qr_codes.items.*.description' => ['nullable', 'string', 'max:50'],
            'students.pages.*.qr_codes.items.*.href' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.qr_codes.items.*.image' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.qr_codes.items.*.flyer_image' => ['nullable', 'string', 'max:2048'],
            'students.pages.*.qr_codes.items.*.image_file' => ['nullable', 'image', 'max:5120'],
            'students.pages.*.qr_codes.items.*.flyer_image_file' => ['nullable', 'image', 'max:5120'],
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
        ], [
            'home.campus_tour.avp_video_file.max' => CmsUploadLimits::FILE_TOO_LARGE_MESSAGE,
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
                is_array($request->input('home')) ? $request->input('home') : [],
                $sectionKey
            );

            if ($sectionKey === '' || $sectionKey === 'description') {
                $campusImageUpload = $request->file('home.campus_image_file');
                if ($campusImageUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($campusImageUpload, 'home/description');
                    if ($storedPath !== false) {
                        $homeInput['campus_image'] = $storedPath;
                    }
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

                        $storedPath = ImageStorage::store($upload, 'home/carousel');
                        if ($storedPath !== false) {
                            $homeInput['carousel'][$index]['image'] = $storedPath;
                        }
                    }
                }
            }

            if ($sectionKey === '' || $sectionKey === 'campus_tour_video') {
                $campusTourVideoUpload = $request->file('home.campus_tour.avp_video_file');
                if ($campusTourVideoUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($campusTourVideoUpload, 'home/campus-tour/video');
                    if ($storedPath !== false) {
                        $homeInput['campus_tour']['avp_video'] = $storedPath;
                    }
                }
            }

            if ($sectionKey === '' || $sectionKey === 'campus_tour_facilities') {
                $facilityUploads = $request->file('home.campus_tour.facilities', []);
                if (is_array($facilityUploads)) {
                    foreach ($facilityUploads as $index => $facilityUpload) {
                        $upload = is_array($facilityUpload) ? ($facilityUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $storedPath = ImageStorage::store($upload, 'home/campus-tour/facilities');
                        if ($storedPath !== false) {
                            $homeInput['campus_tour']['facilities'][$index]['image'] = $storedPath;
                        }
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
                is_array($request->input('about')) ? $request->input('about') : [],
                $sectionKey
            );

            if ($sectionKey === '' || $sectionKey === 'hero') {
                if ($request->exists('about.overview.hero_image')) {
                    $aboutInput['overview']['hero_image'] = (string) ($request->input('about.overview.hero_image') ?? '');
                }

                if ($request->exists('about.overview.section_header_image')) {
                    $aboutInput['overview']['section_header_image'] = (string) ($request->input('about.overview.section_header_image') ?? '');
                }

                $heroImageUpload = $request->file('about.overview.hero_image_file');
                if ($heroImageUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($heroImageUpload, 'about/hero');
                    if ($storedPath !== false) {
                        $aboutInput['overview']['hero_image'] = $storedPath;
                        $aboutInput['overview']['section_header_image'] = $storedPath;
                    }
                }

                $sectionHeaderUpload = $request->file('about.overview.section_header_image_file');
                if (!($heroImageUpload instanceof UploadedFile) && $sectionHeaderUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($sectionHeaderUpload, 'about/hero');
                    if ($storedPath !== false) {
                        $aboutInput['overview']['hero_image'] = $storedPath;
                        $aboutInput['overview']['section_header_image'] = $storedPath;
                    }
                }
            }

            if ($sectionKey === '' || $sectionKey === 'contents') {
                $sectionUploads = $request->file('about.sections', []);

                if (is_array($sectionUploads)) {
                    foreach ($sectionUploads as $slug => $sectionUpload) {
                        $upload = is_array($sectionUpload) ? ($sectionUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $storedPath = ImageStorage::store($upload, 'about/sections');
                        if ($storedPath !== false) {
                            $aboutInput['sections'][$slug]['image'] = $storedPath;
                        }
                    }
                }
            }

            if ($sectionKey === '' || $sectionKey === 'intro') {
                if ($request->exists('about.overview.story_image')) {
                    $aboutInput['overview']['story_image'] = (string) ($request->input('about.overview.story_image') ?? '');
                }

                $storyImageUpload = $request->file('about.overview.story_image_file');
                if ($storyImageUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($storyImageUpload, 'about/story');
                    if ($storedPath !== false) {
                        $aboutInput['overview']['story_image'] = $storedPath;
                    }
                }
            }

            if ($sectionKey === '' || $sectionKey === 'campus-officials') {
                if ($request->exists('about.sections.campus-officials.organizational_chart_image')) {
                    $aboutInput['sections']['campus-officials']['organizational_chart_image'] = (string) ($request->input('about.sections.campus-officials.organizational_chart_image') ?? '');
                }

                $organizationalChartUpload = $request->file('about.sections.campus-officials.organizational_chart_image_file');
                if ($organizationalChartUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($organizationalChartUpload, 'about/officials/chart');
                    if ($storedPath !== false) {
                        $aboutInput['sections']['campus-officials']['organizational_chart_image'] = $storedPath;
                    }
                }

                $officialUploads = data_get($request->file('about.sections', []), 'campus-officials.official_groups', []);

                if (is_array($officialUploads)) {
                    foreach ($officialUploads as $index => $officialUpload) {
                        $upload = is_array($officialUpload) ? ($officialUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $storedPath = ImageStorage::store($upload, 'about/officials');
                        if ($storedPath !== false) {
                            $aboutInput['sections']['campus-officials']['official_groups'][$index]['image'] = $storedPath;
                        }
                    }
                }
            }

            if ($sectionKey === '' || $sectionKey === 'logo-and-symbols') {
                $sealUploads = data_get($request->file('about.sections', []), 'logo-and-symbols.seals', []);

                if (is_array($sealUploads)) {
                    foreach ($sealUploads as $index => $sealUpload) {
                        $upload = is_array($sealUpload) ? ($sealUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $storedPath = ImageStorage::store($upload, 'about/seals');
                        if ($storedPath !== false) {
                            $aboutInput['sections']['logo-and-symbols']['seals'][$index]['image'] = $storedPath;
                        }
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
                is_array($request->input('academics')) ? $request->input('academics') : [],
                $sectionKey
            );

            if (($sectionKey === '' || $sectionKey === 'hero') && $request->exists('academics.hero.image')) {
                $academicsInput['hero']['image'] = (string) ($request->input('academics.hero.image') ?? '');
            }

            $academicsHeroUpload = $request->file('academics.hero.image_file');
            if (($sectionKey === '' || $sectionKey === 'hero') && $academicsHeroUpload instanceof UploadedFile) {
                $storedPath = ImageStorage::store($academicsHeroUpload, 'academics/hero');
                if ($storedPath !== false) {
                    $academicsInput['hero']['image'] = $storedPath;
                }
            }

            $academicsUploads = $request->file('academics.contents.items', []);
            if (is_array($academicsUploads)) {
                foreach ($academicsUploads as $index => $itemUpload) {
                    $upload = is_array($itemUpload) ? ($itemUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $storedPath = ImageStorage::store($upload, 'academics/contents');
                    if ($storedPath !== false) {
                        $academicsInput['contents']['items'][$index]['image'] = $storedPath;
                    }
                }
            }

            foreach (['degree-programs', 'diploma-programs', 'pup-iapply', 'university-calendar'] as $pageKey) {
                if (($sectionKey === '' || $sectionKey === $pageKey.'-hero') && $request->exists("academics.pages.$pageKey.hero.image")) {
                    $academicsInput['pages'][$pageKey]['hero']['image'] = (string) ($request->input("academics.pages.$pageKey.hero.image") ?? '');
                }

                $pageHeroUpload = $request->file("academics.pages.$pageKey.hero.image_file");
                if (($sectionKey === '' || $sectionKey === $pageKey.'-hero') && $pageHeroUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($pageHeroUpload, 'academics/'.$pageKey.'/hero');
                    if ($storedPath !== false) {
                        $academicsInput['pages'][$pageKey]['hero']['image'] = $storedPath;
                    }
                }

                $pageCardUploads = $request->file("academics.pages.$pageKey.cards.items", []);
                if (($sectionKey === '' || $sectionKey === $pageKey.'-cards') && is_array($pageCardUploads)) {
                    foreach ($pageCardUploads as $index => $itemUpload) {
                        $upload = is_array($itemUpload) ? ($itemUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $storedPath = ImageStorage::store($upload, 'academics/'.$pageKey.'/cards');
                        if ($storedPath !== false) {
                            $academicsInput['pages'][$pageKey]['cards']['items'][$index]['image'] = $storedPath;
                        }
                    }
                }

                $calendarPdfUpload = $request->file("academics.pages.$pageKey.calendar.pdf_file");
                if (($sectionKey === '' || $sectionKey === $pageKey.'-calendar') && $calendarPdfUpload instanceof UploadedFile) {
                    $storedPath = DownloadableFile::store($calendarPdfUpload, 'academics/'.$pageKey.'/calendar');
                    if ($storedPath !== false) {
                        $currentCalendar = data_get($academicsInput, "pages.$pageKey.calendar", []);
                        $previousPdfPath = (string) data_get(
                            $currentCalendar,
                            'pdf_url',
                            data_get($baseAcademics, "pages.$pageKey.calendar.pdf_url", '')
                        );
                        $academicsInput['pages'][$pageKey]['calendar'] = AcademicsCmsContent::syncCalendarPdfReferences(
                            is_array($currentCalendar) ? $currentCalendar : [],
                            $storedPath,
                            $previousPdfPath
                        );
                    }
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
                is_array($request->input('students')) ? $request->input('students') : [],
                $sectionKey
            );

            if (($sectionKey === '' || $sectionKey === 'page') && $request->exists('students.page.hero_image')) {
                $studentsInput['page']['hero_image'] = (string) ($request->input('students.page.hero_image') ?? '');
            }

            $studentsHeroUpload = $request->file('students.page.hero_image_file');
            if (($sectionKey === '' || $sectionKey === 'page') && $studentsHeroUpload instanceof UploadedFile) {
                $storedPath = ImageStorage::store($studentsHeroUpload, 'students/page');
                if ($storedPath !== false) {
                    $studentsInput['page']['hero_image'] = $storedPath;
                }
            }

            $studentCardUploads = $request->file('students.cards', []);
            if (is_array($studentCardUploads)) {
                foreach ($studentCardUploads as $index => $cardUpload) {
                    $upload = is_array($cardUpload) ? ($cardUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $storedPath = ImageStorage::store($upload, 'students/cards');
                    if ($storedPath !== false) {
                        $studentsInput['cards'][$index]['image'] = $storedPath;
                    }
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

                        $storedPath = ImageStorage::store($upload, 'students/organizations');
                        if ($storedPath !== false) {
                            $studentsInput['organization_sections'][$sectionIndex]['items'][$orgIndex]['image'] = $storedPath;
                        }
                    }
                }
            }

            foreach (['admissions', 'document-requests', 'downloadable-forms'] as $pageKey) {
                $pageSectionKeys = $this->studentsSectionKeysForPage($pageKey);
                $isActivePageSection = $sectionKey === '' || in_array($sectionKey, $pageSectionKeys, true);

                if ($isActivePageSection && $request->exists("students.pages.$pageKey.hero.image")) {
                    $studentsInput['pages'][$pageKey]['hero']['image'] = (string) ($request->input("students.pages.$pageKey.hero.image") ?? '');
                }

                $pageHeroUpload = $request->file("students.pages.$pageKey.hero.image_file");
                if ($isActivePageSection && $pageHeroUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($pageHeroUpload, 'students/'.$pageKey.'/hero');
                    if ($storedPath !== false) {
                        $studentsInput['pages'][$pageKey]['hero']['image'] = $storedPath;
                    }
                }

                $instructionsUpload = $request->file("students.pages.$pageKey.instructions.image_file");
                if ($isActivePageSection && $pageKey === 'admissions' && $instructionsUpload instanceof UploadedFile) {
                    $storedPath = ImageStorage::store($instructionsUpload, 'students/'.$pageKey.'/instructions');
                    if ($storedPath !== false) {
                        $studentsInput['pages'][$pageKey]['instructions']['image'] = $storedPath;
                    }
                }

                $contactPersonUploads = $request->file("students.pages.$pageKey.contact.persons", []);
                if ($isActivePageSection && $pageKey === 'admissions' && is_array($contactPersonUploads)) {
                    foreach ($contactPersonUploads as $index => $personUpload) {
                        if (!is_array($personUpload)) {
                            continue;
                        }

                        $upload = $personUpload['image_file'] ?? null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $storedPath = ImageStorage::store($upload, 'students/'.$pageKey.'/contact-persons');
                        if ($storedPath !== false) {
                            $studentsInput['pages'][$pageKey]['contact']['persons'][$index]['image'] = $storedPath;
                        }
                    }
                }

                $qrUploads = $request->file("students.pages.$pageKey.qr_codes.items", []);
                if ($isActivePageSection && is_array($qrUploads)) {
                    foreach ($qrUploads as $index => $itemUpload) {
                        if (!is_array($itemUpload)) {
                            continue;
                        }

                        $upload = $itemUpload['image_file'] ?? null;
                        if ($upload instanceof UploadedFile) {
                            $storedPath = ImageStorage::store($upload, 'students/'.$pageKey.'/qr-codes');
                            if ($storedPath !== false) {
                                $studentsInput['pages'][$pageKey]['qr_codes']['items'][$index]['image'] = $storedPath;
                            }
                        }

                        $flyerUpload = $itemUpload['flyer_image_file'] ?? null;
                        if ($flyerUpload instanceof UploadedFile) {
                            $storedPath = ImageStorage::store($flyerUpload, 'students/'.$pageKey.'/qr-flyers');
                            if ($storedPath !== false) {
                                $studentsInput['pages'][$pageKey]['qr_codes']['items'][$index]['flyer_image'] = $storedPath;
                            }
                        }
                    }
                }
            }

            $this->enforceStudentsSectionRequirements($studentsInput, $sectionKey);

            $content = StudentsCmsContent::encode(
                $sectionKey === 'cards'
                    ? StudentsCmsContent::fromCardsInput($studentsInput, $baseStudentsEncoded)
                    : ($sectionKey === 'organizations'
                        ? StudentsCmsContent::fromOrganizationsInput($studentsInput['organization_sections'] ?? [], $baseStudentsEncoded)
                        : (($studentsPageKey = $this->studentsPageKeyForSection($sectionKey)) !== null
                            ? StudentsCmsContent::fromPageInput($studentsPageKey, data_get($studentsInput, 'pages.'.$studentsPageKey, []), $baseStudentsEncoded)
                            : StudentsCmsContent::fromInput($studentsInput, $baseStudentsEncoded)))
            );
            $title = $currentTitle;
            $currentContent = $baseStudentsEncoded;
        } elseif ($tabKey === 'research_extension') {
            $baseResearch = ResearchCmsContent::fromStored((string) ($existing->content ?? ''));
            $baseResearchEncoded = ResearchCmsContent::encode($baseResearch);
            $researchInput = $this->filterResearchInputBySection(
                is_array($request->input('research')) ? $request->input('research') : [],
                $sectionKey
            );

            if (($sectionKey === '' || $sectionKey === 'page') && $request->exists('research.page.hero_image')) {
                $researchInput['page']['hero_image'] = (string) ($request->input('research.page.hero_image') ?? '');
            }

            $researchHeroUpload = $request->file('research.page.hero_image_file');
            if (($sectionKey === '' || $sectionKey === 'page') && $researchHeroUpload instanceof UploadedFile) {
                $storedPath = ImageStorage::store($researchHeroUpload, 'research/page');
                if ($storedPath !== false) {
                    $researchInput['page']['hero_image'] = $storedPath;
                }
            }

            $researchCardUploads = $request->file('research.cards', []);
            if (is_array($researchCardUploads)) {
                foreach ($researchCardUploads as $index => $cardUpload) {
                    $upload = is_array($cardUpload) ? ($cardUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $storedPath = ImageStorage::store($upload, 'research/cards');
                    if ($storedPath !== false) {
                        $researchInput['cards'][$index]['image'] = $storedPath;
                    }
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
                is_array($request->input('events')) ? $request->input('events') : [],
                $sectionKey
            );

            $eventUploads = $request->file('events.cards', []);
            if (is_array($eventUploads)) {
                foreach ($eventUploads as $index => $cardUpload) {
                    $upload = is_array($cardUpload) ? ($cardUpload['image_file'] ?? null) : null;
                    if (!$upload instanceof UploadedFile) {
                        continue;
                    }

                    $storedPath = ImageStorage::store($upload, 'events/cards');
                    if ($storedPath !== false) {
                        $eventsInput['cards'][$index]['image'] = $storedPath;
                    }
                }
            }

            $eventsErrors = EventsCmsCardValidation::validateCardsSubmission(
                $eventsInput['cards'] ?? [],
                $request->filled('events_active_card_index') ? (int) $request->input('events_active_card_index') : null
            );
            if ($eventsErrors !== []) {
                $firstMessage = collect($eventsErrors)->flatten()->first() ?: 'Please complete the required event fields.';

                return response()->json([
                    'ok' => false,
                    'message' => $firstMessage,
                    'errors' => $eventsErrors,
                ], 422);
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
            if ($this->requestHasUploadedFiles($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'We could not finish uploading that file. Please try again.',
                ], 422);
            }

            if ($this->requestExceededUploadLimit($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => $sectionKey === 'campus_tour_video'
                        ? CmsUploadLimits::FILE_TOO_LARGE_MESSAGE
                        : 'One of the uploaded files is too large. Please choose a smaller file and try again.',
                ], 422);
            }

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

        $auditMessage = 'Updated '.$tabLabel.' content directly as superadmin.';
        if (in_array($tabKey, ['home', 'about', 'academics', 'students', 'research_extension', 'events'], true) && $sectionLabel !== '') {
            $auditMessage = 'Updated '.$tabLabel.' content ('.$sectionLabel.') directly as superadmin.';
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
            'campus_tour_video' => 'Campus Tour AVP',
            'campus_tour_facilities' => 'Campus Tour Facilities',
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
            'campus_tour_video' => ['campus_tour'],
            'campus_tour_facilities' => ['campus_tour'],
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
            'vision-statement' => 'Vision Statement',
            'mission-statement' => 'Mission Statement',
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
            'cards_header' => 'Cards Header',
            'cards' => 'Cards',
            'organizations' => 'Organizations',
            'admissions_page' => 'Admissions Page',
            'admissions_hero' => 'Admissions Header',
            'admissions_instructions' => 'Admissions Instructions',
            'admissions_contact' => 'Admissions Contact',
            'admissions_qr_codes' => 'Admissions QR Codes',
            'admissions_links' => 'Admissions Links',
            'document_requests_hero' => 'Document Requests Header',
            'document_requests_qr_codes' => 'Document Requests QR Codes',
            'downloadable_forms_page' => 'Downloadable Forms Page',
            'downloadable_forms_hero' => 'Downloadables Header',
            'downloadable_forms_links' => 'Downloadables Links',
            default => '',
        };
    }

    private function researchSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'page' => 'Page Header',
            'cards' => 'Contents',
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

    private function requestHasUploadedFiles(Request $request): bool
    {
        return $this->hasUploadedFiles($request->allFiles());
    }

    private function requestExceededUploadLimit(Request $request): bool
    {
        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);
        if ($contentLength <= 0) {
            return false;
        }

        $uploadMax = $this->iniSizeToBytes($this->readIniLimit('upload_max_filesize'));
        $postMax = $this->iniSizeToBytes($this->readIniLimit('post_max_size'));
        $hardLimit = min($uploadMax > 0 ? $uploadMax : PHP_INT_MAX, $postMax > 0 ? $postMax : PHP_INT_MAX);

        return $hardLimit > 0 && $contentLength > $hardLimit && !$this->requestHasUploadedFiles($request);
    }

    private function readIniLimit(string $key): string
    {
        $value = trim((string) ini_get($key));
        return $value !== '' ? $value : 'unknown';
    }

    private function iniSizeToBytes(string $size): int
    {
        $value = trim($size);
        if ($value === '') {
            return 0;
        }

        if (!preg_match('/^(\d+(?:\.\d+)?)\s*([KMG]?)$/i', $value, $matches)) {
            return (int) $value;
        }

        $number = (float) $matches[1];
        $unit = strtoupper($matches[2] ?? '');

        return match ($unit) {
            'G' => (int) round($number * 1024 * 1024 * 1024),
            'M' => (int) round($number * 1024 * 1024),
            'K' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }

    private function hasUploadedFiles(array $files): bool
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                return true;
            }

            if (is_array($file) && $this->hasUploadedFiles($file)) {
                return true;
            }
        }

        return false;
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
                'overview' => array_intersect_key($overview, array_flip(['story_tag', 'story_title', 'story_description', 'story_image'])),
            ],
            'philosophy' => [
                'overview' => array_intersect_key($overview, array_flip(['philosophy_tag', 'philosophy_description'])),
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
            'vision-statement' => [
                'sections' => [
                    'vision-and-mission' => array_intersect_key($visionSection, array_flip(['vision'])),
                ],
            ],
            'mission-statement' => [
                'sections' => [
                    'vision-and-mission' => array_intersect_key($visionSection, array_flip(['mission'])),
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
                    'tag' => (string) data_get($academicsInput, 'features.tag', ''),
                    'eyebrow' => (string) data_get($academicsInput, 'features.eyebrow', ''),
                    'title' => (string) data_get($academicsInput, 'features.title', ''),
                    'description' => (string) data_get($academicsInput, 'features.description', ''),
                    'items' => collect(data_get($academicsInput, 'features.items', []))
                        ->map(fn ($item) => is_array($item)
                            ? array_intersect_key($item, array_flip(['tag', 'title', 'description', 'body']))
                            : [])
                        ->all(),
                ],
            ],
            'degree-programs-hero', 'degree-programs-info', 'degree-programs-cards', 'degree-programs-contact',
            'diploma-programs-hero', 'diploma-programs-info', 'diploma-programs-cards', 'diploma-programs-contact',
            'pup-iapply-hero', 'pup-iapply-schedule', 'pup-iapply-guide', 'pup-iapply-reminders',
            'university-calendar-hero', 'university-calendar-info', 'university-calendar-calendar'
                => $this->filterAcademicsPageSectionInput($academicsInput, $sectionKey),
            default => $academicsInput,
        };
    }

    private function filterAcademicsPageSectionInput(array $academicsInput, string $sectionKey): array
    {
        $matches = [];
        if (!preg_match('/^(degree-programs|diploma-programs|pup-iapply|university-calendar)\-(hero|info|cards|contact|schedule|guide|reminders|calendar)$/', $sectionKey, $matches)) {
            return $academicsInput;
        }

        $pageKey = $matches[1];
        $subSection = $matches[2];
        $pageSection = data_get($academicsInput, 'pages.'.$pageKey.'.'.$subSection, []);

        return [
            'pages' => [
                $pageKey => [
                    $subSection => is_array($pageSection) ? $pageSection : [],
                ],
            ],
        ];
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
            'cards_header' => [
                'page' => is_array($studentsInput['page'] ?? null)
                    ? array_intersect_key($studentsInput['page'], array_flip(['contents_tag', 'contents_title', 'contents_description']))
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
            'admissions_page', 'admissions_hero', 'admissions_instructions', 'admissions_qr_codes', 'admissions_links',
            'document_requests_hero', 'document_requests_qr_codes',
            'downloadable_forms_page', 'downloadable_forms_hero', 'downloadable_forms_links'
                => $this->filterStudentsPageSectionInput($studentsInput, $sectionKey),
            default => $studentsInput,
        };
    }

    private function filterStudentsPageSectionInput(array $studentsInput, string $sectionKey): array
    {
        $pageKey = $this->studentsPageKeyForSection($sectionKey);

        if ($pageKey === null) {
            return $studentsInput;
        }

        $subSection = match ($sectionKey) {
            'admissions_hero', 'downloadable_forms_hero', 'document_requests_hero' => 'hero',
            'admissions_instructions' => 'instructions',
            'admissions_contact' => 'contact',
            'admissions_qr_codes', 'document_requests_qr_codes' => 'qr_codes',
            'admissions_links', 'downloadable_forms_links' => 'links',
            default => null,
        };

        if ($subSection === null) {
            return [
                'pages' => [
                    $pageKey => data_get($studentsInput, 'pages.'.$pageKey, []),
                ],
            ];
        }

        return [
            'pages' => [
                $pageKey => [
                    $subSection => data_get($studentsInput, 'pages.'.$pageKey.'.'.$subSection, []),
                ],
            ],
        ];
    }

    private function studentsPageKeyForSection(string $sectionKey): ?string
    {
        return match ($sectionKey) {
            'admissions_page', 'admissions_hero', 'admissions_instructions', 'admissions_contact', 'admissions_qr_codes', 'admissions_links' => 'admissions',
            'document_requests_hero', 'document_requests_qr_codes' => 'document-requests',
            'downloadable_forms_page', 'downloadable_forms_hero', 'downloadable_forms_links' => 'downloadable-forms',
            default => null,
        };
    }

    private function studentsSectionKeysForPage(string $pageKey): array
    {
        return match ($pageKey) {
            'admissions' => ['admissions_page', 'admissions_hero', 'admissions_instructions', 'admissions_contact', 'admissions_qr_codes', 'admissions_links'],
            'document-requests' => ['document_requests_hero', 'document_requests_qr_codes'],
            'downloadable-forms' => ['downloadable_forms_page', 'downloadable_forms_hero', 'downloadable_forms_links'],
            default => [str_replace('-', '_', $pageKey).'_page'],
        };
    }

    private function enforceStudentsSectionRequirements(array $studentsInput, string $sectionKey): void
    {
        if (in_array($sectionKey, ['admissions_qr_codes', 'document_requests_qr_codes'], true)) {
            $pageKey = $this->studentsPageKeyForSection($sectionKey);
            $items = array_values(is_array(data_get($studentsInput, "pages.$pageKey.qr_codes.items")) ? data_get($studentsInput, "pages.$pageKey.qr_codes.items") : []);

            if ($items === []) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "students.pages.$pageKey.qr_codes.items" => 'Add at least one QR code with an uploaded image.',
                ]);
            }

            foreach ($items as $index => $item) {
                $image = trim((string) (is_array($item) ? ($item['image'] ?? '') : ''));
                if ($image === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "students.pages.$pageKey.qr_codes.items.$index.image" => 'Each QR code entry requires an uploaded image.',
                    ]);
                }
            }
        }

        if (in_array($sectionKey, ['admissions_links', 'downloadable_forms_links'], true)) {
            $pageKey = $this->studentsPageKeyForSection($sectionKey);
            $links = is_array(data_get($studentsInput, "pages.$pageKey.links")) ? data_get($studentsInput, "pages.$pageKey.links") : [];
            $tag = trim((string) ($links['tag'] ?? ''));
            $title = trim((string) ($links['title'] ?? ''));
            $description = trim((string) ($links['description'] ?? ''));

            if ($tag === '' || $title === '' || $description === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "students.pages.$pageKey.links" => 'Links tag, title, and description are required.',
                ]);
            }

            $items = array_values(is_array($links['items'] ?? null) ? $links['items'] : []);
            if ($items === []) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "students.pages.$pageKey.links.items" => 'Add at least one link item.',
                ]);
            }

            foreach ($items as $index => $item) {
                $label = trim((string) (is_array($item) ? ($item['label'] ?? '') : ''));
                $href = trim((string) (is_array($item) ? ($item['href'] ?? '') : ''));
                $itemDescription = trim((string) (is_array($item) ? ($item['description'] ?? '') : ''));

                if ($label === '' || $href === '' || $itemDescription === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "students.pages.$pageKey.links.items.$index" => 'All link item fields (label, URL, and description) are required.',
                    ]);
                }
            }
        }
    }

    private function filterResearchInputBySection(array $researchInput, string $sectionKey): array
    {
        if ($sectionKey === '' || $sectionKey === 'all') {
            return $researchInput;
        }

        return match ($sectionKey) {
                'page' => [
                    'page' => is_array($researchInput['page'] ?? null)
                    ? array_intersect_key($researchInput['page'], array_flip(['eyebrow', 'title', 'description', 'hero_image']))
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
