<?php

namespace App\Support;

trait CmsPreviewHandler
{
    protected function editableCmsPreview(): bool
    {
        return true;
    }

    public function preview(string $tabKey, string $sectionKey)
    {
        $roles = session('user_roles', [session('user_role')]);
        $allowedTabs = CmsSections::tabsForRoles($roles);

        if (!in_array($tabKey, $allowedTabs, true)) {
            abort(403, 'Unauthorized access to this CMS tab.');
        }

        $contentsByTab = $this->loadContents([$tabKey]);
        $live = $contentsByTab[$tabKey] ?? ['content' => ''];
        
        $html = '';

        if ($tabKey === 'home') {
            $homeLive = \App\Support\HomeCmsContent::fromStored((string) ($live['content'] ?? ''));
            $html = view('public.home', [
                'homeCms' => $homeLive,
                'news' => method_exists($this, 'loadHomePreviewNews') ? $this->loadHomePreviewNews() : collect(),
                'announcements' => method_exists($this, 'loadHomePreviewAnnouncements') ? $this->loadHomePreviewAnnouncements() : collect(),
                'cmsPreview' => $this->editableCmsPreview(),
            ])->render();
        } elseif ($tabKey === 'academics') {
            $academicsLive = \App\Support\AcademicsCmsContent::fromStored((string) ($live['content'] ?? ''));
            if ($sectionKey === 'overview') {
                $html = view('public.academics', ['academicsCms' => $academicsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'degree-programs') {
                $html = view('public.degreeprograms', ['academicsCms' => $academicsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'diploma-programs') {
                $html = view('public.diplomaprograms', ['academicsCms' => $academicsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'pup-iapply') {
                $html = view('public.pupiapply', ['academicsCms' => $academicsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'university-calendar') {
                $html = view('public.universitycalendar', ['academicsCms' => $academicsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            }
        } elseif ($tabKey === 'students') {
            $studentsLive = \App\Support\StudentsCmsContent::fromStored((string) ($live['content'] ?? ''));
            if ($sectionKey === 'overview') {
                $html = view('public.students', ['studentsCms' => $studentsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'admissions') {
                $html = view('public.student_admissions', ['studentsCms' => $studentsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'downloadable-forms') {
                $html = view('public.student_downloadable_forms', ['studentsCms' => $studentsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'document-requests') {
                $html = view('public.student_document_requests', ['studentsCms' => $studentsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            }
        } elseif ($tabKey === 'research_extension') {
            $researchLive = \App\Support\ResearchCmsContent::fromStored((string) ($live['content'] ?? ''));
            if ($sectionKey === 'overview') {
                $html = view('public.research', ['researchCms' => $researchLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } elseif ($sectionKey === 'strategic-development-plan') {
                $html = view('public.research_sdp', [
                    'researchCms' => $researchLive, 
                    'sdp' => $researchLive['strategic_development_plan'] ?? \App\Support\ResearchCmsContent::defaults()['strategic_development_plan'],
                    'cmsPreview' => $this->editableCmsPreview()
                ])->render();
            }
        } elseif ($tabKey === 'events') {
            $eventsLive = \App\Support\EventsCmsContent::fromStored((string) ($live['content'] ?? ''));
            $html = view('public.events', ['eventsCms' => $eventsLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
        } elseif ($tabKey === 'about') {
            $aboutLive = \App\Support\AboutCmsContent::fromStored((string) ($live['content'] ?? ''));
            if ($sectionKey === 'overview') {
                $html = view('public.about', ['aboutCms' => $aboutLive, 'cmsPreview' => $this->editableCmsPreview()])->render();
            } else {
                $selectedSection = $aboutLive['sections'][$sectionKey] ?? null;
                $html = view('public.about', ['aboutCms' => $aboutLive, 'cmsPreview' => $this->editableCmsPreview(), 'selectedSection' => $selectedSection])->render();
            }
        }

        return response($html)->header('Content-Type', 'text/html');
    }
}
