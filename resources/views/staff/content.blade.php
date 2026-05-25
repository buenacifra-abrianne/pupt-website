<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Content Management - PUP Taguig CMS</title>
    <script>
        window.__cmsEntryLoading = false;
        try {
            window.__cmsEntryLoading = sessionStorage.getItem('cms-content-entry-loading') === '1';
            if (window.__cmsEntryLoading) {
                document.documentElement.classList.add('cms-entry-loading');
            }
        } catch (error) {
            window.__cmsEntryLoading = false;
        }
    </script>
    <style>
        html.cms-entry-loading body {
            overflow: hidden;
        }

        html.cms-entry-loading .main-content {
            opacity: 0;
            visibility: hidden;
        }

        html.cms-entry-loading body > .cms-page-loading-overlay {
            opacity: 1;
        }
    </style>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">
    @include('partials.cms_preview_compat_assets')
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('staff.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.content') }}" class="nav-link active">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.downloadables') }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Downloadables</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.notifications') }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>
        </ul>
    </nav>

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Staff" />

    @include('partials.profile_modal')
    <div class="cms-page-loading-overlay" data-cms-page-loader hidden>
        <div class="cms-page-loading-card" role="status" aria-live="polite">
            <span class="cms-page-loading-spinner" aria-hidden="true"></span>
            <h3>Loading CMS Preview</h3>
            <p>Please wait while the page refreshes.</p>
        </div>
    </div>
    <script>
        if (window.__cmsEntryLoading) {
            document.querySelector('[data-cms-page-loader]')?.removeAttribute('hidden');
        }
    </script>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Content Management</h1>
            <p class="page-subtitle">Role-based content editing. Every change is sent to admin pending approvals.</p>
        </div>
        <nav class="tab-navigation" aria-label="CMS content sections">
            <ul class="tab-navigation-list" role="tablist">
                @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
                    <li class="tab-navigation-item {{ $loop->first ? 'is-active' : '' }}">
                        <button
                            class="cms-tab-btn {{ $loop->first ? 'active' : '' }}"
                            type="button"
                            role="tab"
                            id="cms-tab-trigger-{{ $tabKey }}"
                            data-tab-key="{{ $tabKey }}"
                            aria-controls="cms-tab-{{ $tabKey }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            tabindex="{{ $loop->first ? '0' : '-1' }}"
                            onclick="switchCmsTab('{{ $tabKey }}', this)"
                        >
                            <span>{{ $tabDef['label'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </nav>

        @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
            @php
                $live = $contentsByTab[$tabKey] ?? ['title' => $tabDef['label'].' Content', 'content' => ''];
                $draft = $requestDraftsByTab[$tabKey] ?? null;
                $reviewSections = $reviewSectionsByTab[$tabKey] ?? [];
                $reviewSectionLabels = collect($reviewSections)
                    ->pluck('label')
                    ->filter(fn ($label) => trim((string) $label) !== '')
                    ->values()
                    ->all();
                $status = strtolower((string)($draft['status'] ?? ''));
                $badgeClass = $status === 'pending' ? 'status-pending' : ($status === 'rejected' ? 'status-disabled' : 'status-enabled');
                $prefillTitle = trim((string) ($draft['title'] ?? '')) !== ''
                    ? (string) $draft['title']
                    : (string) ($live['title'] ?? '');
                $prefillContent = trim((string) ($draft['content'] ?? '')) !== ''
                    ? (string) $draft['content']
                    : (string) ($live['content'] ?? '');
                $homeLive = $tabKey === 'home'
                    ? \App\Support\HomeCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $homePrefill = $tabKey === 'home'
                    ? \App\Support\HomeCmsContent::fromStored((string) $prefillContent)
                    : null;
                $aboutLive = $tabKey === 'about'
                    ? \App\Support\AboutCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $aboutPrefill = $tabKey === 'about'
                    ? \App\Support\AboutCmsContent::fromStored((string) $prefillContent)
                    : null;
                $academicsLive = $tabKey === 'academics'
                    ? \App\Support\AcademicsCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $academicsPrefill = $tabKey === 'academics'
                    ? \App\Support\AcademicsCmsContent::fromStored((string) $prefillContent)
                    : null;
                $studentsLive = $tabKey === 'students'
                    ? \App\Support\StudentsCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $studentsPrefill = $tabKey === 'students'
                    ? \App\Support\StudentsCmsContent::fromStored((string) $prefillContent)
                    : null;
                $researchLive = $tabKey === 'research_extension'
                    ? \App\Support\ResearchCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $researchPrefill = $tabKey === 'research_extension'
                    ? \App\Support\ResearchCmsContent::fromStored((string) $prefillContent)
                    : null;
                $eventsLive = $tabKey === 'events'
                    ? \App\Support\EventsCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $eventsPrefill = $tabKey === 'events'
                    ? \App\Support\EventsCmsContent::fromStored((string) $prefillContent)
                    : null;
            @endphp

            <div
                id="cms-tab-{{ $tabKey }}"
                class="tab-content cms-tab-panel {{ $loop->first ? 'active' : '' }}"
                role="tabpanel"
                aria-labelledby="cms-tab-trigger-{{ $tabKey }}"
                @unless($loop->first) hidden @endunless
            >
                @if($status === 'pending')
                    <div class="cms-staff-status-banner cms-staff-status-banner-pending" role="status" aria-live="polite">
                        <div>
                            <span class="cms-staff-status-pill">Pending Approval</span>
                            <h3>{{ $tabDef['label'] }} changes are under review.</h3>
                            <p>The preview below still shows the approved live content. Your updates will appear after admin or superadmin approval.</p>
                            @if(!empty($draft['updated_at']))
                                <p class="cms-staff-status-meta">Submitted {{ \Carbon\Carbon::parse($draft['updated_at'])->format('M d, Y h:i A') }}</p>
                            @endif
                        </div>
                        @if(!empty($reviewSectionLabels))
                            <div class="cms-staff-status-chips" aria-label="Sections under review">
                                @foreach($reviewSectionLabels as $sectionLabel)
                                    <span class="cms-staff-status-chip">{{ $sectionLabel }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif($status === 'rejected' && !empty($draft['rejection_reason']))
                    <div class="cms-staff-status-banner cms-staff-status-banner-rejected" role="status" aria-live="polite">
                        <div>
                            <span class="cms-staff-status-pill cms-staff-status-pill-rejected">Needs Revision</span>
                            <h3>{{ $tabDef['label'] }} request was rejected.</h3>
                            <p>{{ $draft['rejection_reason'] }}</p>
                        </div>
                    </div>
                @endif

                <div class="cms-staff-review-shell">
                    @if($tabKey === 'home')
                        @php
                            $homePreviewHtml = view('public.home', [
                                'homeCms' => $homeLive,
                                'news' => $homePreviewNews ?? collect(),
                                'announcements' => $homePreviewAnnouncements ?? collect(),
                                'cmsPreview' => true,
                            ])->render();
                        @endphp

                        @include('partials.home_cms_preview_editor', [
                            'homePreviewHtml' => $homePreviewHtml,
                            'homeEditorData' => $homePrefill,
                            'homeEditorFormClass' => 'cms-edit-form',
                            'homeEditorSubmitRoute' => route('staff.content.requestEdit'),
                            'homeEditorSubmitMode' => 'request',
                            'homeEditorRequestId' => $draft['id'] ?? null,
                            'homeEditorStatus' => $status,
                            'homeEditorIdPrefix' => 'staff-home',
                        ])
                    @elseif($tabKey === 'about')
                        @include('partials.about_cms_preview_editor', [
                            'aboutPreviewData' => $aboutLive,
                            'aboutEditorData' => $aboutPrefill,
                            'aboutEditorFormClass' => 'cms-edit-form',
                            'aboutEditorSubmitRoute' => route('staff.content.requestEdit'),
                            'aboutEditorSubmitMode' => 'request',
                            'aboutEditorRequestId' => $draft['id'] ?? null,
                            'aboutEditorStatus' => $status,
                            'aboutEditorIdPrefix' => 'staff-about',
                        ])
                    @elseif($tabKey === 'academics')
                        @php
                            $academicsPreviewData = $academicsLive;
                            $academicsPreviewPages = [
                                'overview' => view('public.academics', [
                                    'academicsCms' => $academicsLive,
                                    'cmsPreview' => true,
                                ])->render(),
                                'degree-programs' => view('public.degreeprograms', ['cmsPreview' => true, 'academicsCms' => $academicsPreviewData])->render(),
                                'diploma-programs' => view('public.diplomaprograms', ['cmsPreview' => true, 'academicsCms' => $academicsPreviewData])->render(),
                                'pup-iapply' => view('public.pupiapply', ['cmsPreview' => true, 'academicsCms' => $academicsPreviewData])->render(),
                                'university-calendar' => view('public.universitycalendar', ['cmsPreview' => true, 'academicsCms' => $academicsPreviewData])->render(),
                            ];
                        @endphp

                        @include('partials.academics_cms_preview_editor', [
                            'academicsPreviewPages' => $academicsPreviewPages,
                            'academicsEditorData' => $academicsPrefill,
                            'academicsEditorFormClass' => 'cms-edit-form',
                            'academicsEditorSubmitRoute' => route('staff.content.requestEdit'),
                            'academicsEditorSubmitMode' => 'request',
                            'academicsEditorRequestId' => $draft['id'] ?? null,
                            'academicsEditorStatus' => $status,
                            'academicsEditorIdPrefix' => 'staff-academics',
                        ])
                    @elseif($tabKey === 'students')
                        @php
                            $studentsPreviewPages = [
                                'overview' => view('public.students', [
                                    'studentsCms' => $studentsLive,
                                    'cmsPreview' => true,
                                ])->render(),
                                'admissions' => view('public.student_admissions', [
                                    'studentsCms' => $studentsLive,
                                    'cmsPreview' => true,
                                ])->render(),
                                'downloadable-forms' => view('public.student_downloadable_forms', [
                                    'studentsCms' => $studentsLive,
                                    'cmsPreview' => true,
                                ])->render(),
                            ];
                        @endphp

                        @include('partials.students_cms_preview_editor', [
                            'studentsPreviewPages' => $studentsPreviewPages,
                            'studentsEditorData' => $studentsPrefill,
                            'studentsEditorFormClass' => 'cms-edit-form',
                            'studentsEditorSubmitRoute' => route('staff.content.requestEdit'),
                            'studentsEditorSubmitMode' => 'request',
                            'studentsEditorRequestId' => $draft['id'] ?? null,
                            'studentsEditorStatus' => $status,
                            'studentsEditorIdPrefix' => 'staff-students',
                        ])
                    @elseif($tabKey === 'research_extension')
                        @php
                            $researchPreviewHtml = view('public.research', [
                                'researchCms' => $researchLive,
                                'cmsPreview' => true,
                            ])->render();
                        @endphp

                        @include('partials.research_cms_preview_editor', [
                            'researchPreviewHtml' => $researchPreviewHtml,
                            'researchEditorData' => $researchPrefill,
                            'researchEditorFormClass' => 'cms-edit-form',
                            'researchEditorSubmitRoute' => route('staff.content.requestEdit'),
                            'researchEditorSubmitMode' => 'request',
                            'researchEditorRequestId' => $draft['id'] ?? null,
                            'researchEditorStatus' => $status,
                            'researchEditorIdPrefix' => 'staff-research',
                        ])
                    @elseif($tabKey === 'events')
                        @php
                            $eventsPreviewHtml = view('public.events', [
                                'eventsCms' => $eventsLive,
                                'cmsPreview' => true,
                            ])->render();
                        @endphp

                        @include('partials.events_cms_preview_editor', [
                            'eventsPreviewHtml' => $eventsPreviewHtml,
                            'eventsEditorData' => $eventsPrefill,
                            'eventsEditorFormClass' => 'cms-edit-form',
                            'eventsEditorSubmitRoute' => route('staff.content.requestEdit'),
                            'eventsEditorSubmitMode' => 'request',
                            'eventsEditorRequestId' => $draft['id'] ?? null,
                            'eventsEditorStatus' => $status,
                            'eventsEditorIdPrefix' => 'staff-events',
                        ])
                    @else
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Request Edit - {{ $tabDef['label'] }}</h3>
                                @if($draft)
                                    <span class="status-badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                @else
                                    <span class="status-badge status-enabled">No Draft</span>
                                @endif
                            </div>
                            <div style="padding:14px;">
                                @if($draft && !empty($draft['updated_at']))
                                    <div style="font-size:13px;opacity:.75;margin-bottom:10px;">
                                        Last updated: {{ \Carbon\Carbon::parse($draft['updated_at'])->format('M d, Y h:i A') }}
                                    </div>
                                @endif

                                @if($status === 'rejected' && !empty($draft['rejection_reason']))
                                    <div style="margin-bottom:10px;padding:10px 12px;border-radius:10px;background:#fff3f3;color:#932525;font-size:13px;">
                                        Rejection reason: {{ $draft['rejection_reason'] }}
                                    </div>
                                @endif

                                <form class="cms-edit-form" method="POST" action="{{ route('staff.content.requestEdit') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="tab_key" value="{{ $tabKey }}">
                                    @if($draft && !empty($draft['id']))
                                        <input type="hidden" name="request_id" value="{{ (int) $draft['id'] }}">
                                    @endif

                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title" maxlength="255" value="{{ $prefillTitle }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Content</label>
                                        <textarea name="content" rows="11">{{ $prefillContent }}</textarea>
                                    </div>

                                    <div style="display:flex;justify-content:flex-end;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                            {{ $status === 'pending' ? 'Update Pending Request' : 'Submit for Approval' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </main>

<style>
    :root {
        --cms-tab-bleed: 30px;
    }

    .tab-navigation {
        width: calc(100% + (var(--cms-tab-bleed) * 2));
        margin: 0 calc(var(--cms-tab-bleed) * -1) 26px;
        overflow-x: auto;
        background: #991b21;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 0;
        box-shadow: 0 16px 32px rgba(66, 12, 12, 0.14);
    }

    .tab-navigation-list {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 28px;
        list-style: none;
        width: 100%;
        margin: 0;
        padding: 0 30px;
    }

    .tab-navigation-item {
        position: relative;
        transition: background-color 0.24s ease, box-shadow 0.24s ease;
    }

    .tab-navigation-item:hover,
    .tab-navigation-item.is-active {
        background: #7f1113;
        box-shadow: inset 0 -3px 0 rgba(255, 250, 244, 0.14);
    }

    .cms-page-loading-overlay[hidden] {
        display: none !important;
    }

    .cms-page-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 2500;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(255, 252, 249, 0.42);
        backdrop-filter: blur(4px);
    }

    .cms-page-loading-overlay:not([hidden]) ~ #floatingVoiceBtn {
        opacity: 0;
        visibility: hidden;
        pointer-events: none !important;
    }

    .cms-page-loading-card {
        min-width: min(360px, calc(100vw - 48px));
        max-width: 420px;
        padding: 28px 26px;
        border: 1px solid rgba(128, 0, 0, 0.08);
        border-radius: 22px;
        background: #fffdfb;
        box-shadow: 0 22px 60px rgba(40, 13, 10, 0.18);
        text-align: center;
        color: #5c0000;
    }

    .cms-page-loading-card h3 {
        margin: 0;
        font-size: 1.3rem;
    }

    .cms-page-loading-card p {
        margin: 10px 0 0;
        color: #6f625c;
        line-height: 1.5;
    }

    .cms-staff-review-shell {
        position: relative;
    }

    .cms-staff-status-banner {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin: 0 0 18px;
        padding: 20px 22px;
        border-radius: 22px;
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(180deg, rgba(255, 253, 250, 0.98) 0%, rgba(255, 248, 242, 0.96) 100%);
        box-shadow: 0 16px 36px rgba(46, 15, 11, 0.08);
    }

    .cms-staff-status-banner-pending {
        border-color: rgba(127, 17, 19, 0.14);
    }

    .cms-staff-status-banner-rejected {
        border-color: rgba(148, 37, 37, 0.18);
        background: linear-gradient(180deg, rgba(255, 249, 249, 0.98) 0%, rgba(255, 241, 241, 0.96) 100%);
    }

    .cms-staff-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(128, 0, 0, 0.08);
        color: #7f1113;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .cms-staff-status-pill-rejected {
        background: rgba(148, 37, 37, 0.1);
        color: #932525;
    }

    .cms-staff-status-banner h3 {
        margin: 0;
        font-size: 1.4rem;
        color: #5c0000;
    }

    .cms-staff-status-banner p {
        margin: 12px 0 0;
        color: #6f625c;
        line-height: 1.6;
    }

    .cms-staff-status-meta {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .cms-staff-status-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        max-width: 420px;
    }

    .cms-staff-status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(127, 17, 19, 0.08);
        color: #7f1113;
        font-size: 0.84rem;
        font-weight: 700;
        text-align: center;
    }

    .cms-page-loading-spinner {
        display: inline-block;
        width: 42px;
        height: 42px;
        margin-bottom: 16px;
        border-radius: 999px;
        border: 4px solid rgba(128, 0, 0, 0.14);
        border-top-color: #800000;
        animation: cmsPageLoaderSpin 0.85s linear infinite;
    }

    @keyframes cmsPageLoaderSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .cms-tab-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 58px;
        padding: 16px 30px;
        border: 0;
        background: transparent;
        color: #fff;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.055em;
        text-transform: uppercase;
        white-space: nowrap;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .cms-tab-btn::after {
        content: "";
        position: absolute;
        left: 22px;
        right: 22px;
        bottom: 10px;
        height: 3px;
        border-radius: 999px;
        background: #f3c45a;
        transform: scaleX(0);
        transform-origin: center;
        transition: transform 0.22s ease;
    }

    .tab-navigation-item:hover .cms-tab-btn::after,
    .cms-tab-btn:focus-visible::after,
    .cms-tab-btn.active::after {
        transform: scaleX(1);
    }

    .cms-tab-btn:hover,
    .cms-tab-btn.active {
        color: #fff;
    }

    .cms-tab-btn:focus-visible {
        outline: none;
        box-shadow: inset 0 0 0 2px rgba(243, 196, 90, 0.95);
    }

    .cms-tab-btn.active:focus-visible {
        box-shadow: inset 0 0 0 2px rgba(243, 196, 90, 0.95);
    }

    @media (max-width: 860px) {
        .tab-navigation {
            scrollbar-width: thin;
        }

        .tab-navigation-list {
            justify-content: flex-start;
            width: max-content;
            min-width: 100%;
            padding: 0 14px;
            gap: 8px;
        }

        .cms-tab-btn {
            min-height: 54px;
            padding: 14px 18px;
            font-size: 0.74rem;
        }

        .cms-staff-status-banner {
            padding: 18px;
        }

        .cms-staff-status-chips {
            justify-content: flex-start;
            max-width: none;
        }
    }

    @media (max-width: 480px) {
        :root {
            --cms-tab-bleed: 20px;
        }
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 14px;
        padding: 14px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        color: #fff;
    }

    .stat-icon.maroon { background: #800000; }
    .stat-icon.yellow { background: #d4af37; color: #3b2a00; }

    .stat-label { font-size: 12px; opacity: .8; }
    .stat-value { font-size: 24px; font-weight: 700; line-height: 1.1; }

    @media (max-width: 980px) {
        .cms-tab-panel > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }

    .home-dropzone {
        border: 1px dashed #d4af37;
        border-radius: 10px;
        padding: 12px;
        display: block;
        cursor: pointer;
        background: #fffdf6;
    }

    .dropzone-label {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
        color: #5c0000;
    }

    .dropzone-file-name {
        display: block;
        font-size: 12px;
        color: #666;
        word-break: break-all;
    }

    .home-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .home-dropzone-input {
        display: none;
    }

    .home-cms-section {
        margin-bottom: 18px;
        padding: 12px;
        border: 1px solid #ececec;
        border-radius: 10px;
        background: #fff;
    }

    .home-cms-title {
        margin: 0 0 10px;
        font-size: 14px;
        color: #5c0000;
    }

    .home-section-form + .home-section-form {
        margin-top: 12px;
    }

    .carousel-manager-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .carousel-manager-item {
        min-width: 0;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 10px;
        background: #fff;
    }

    .slide-dropzone {
        min-height: 180px;
        text-align: center;
    }

    .slide-preview {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f1f1f1;
    }

    .slide-meta {
        margin-top: 10px;
    }

    .slide-meta .form-group {
        margin-bottom: 8px;
    }

    .slide-meta textarea {
        resize: vertical;
        min-height: 56px;
    }

    .campus-dropzone {
        text-align: center;
    }

    .campus-preview {
        width: min(100%, 460px);
        height: 220px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f1f1f1;
    }

    @media (max-width: 1024px) {
        .carousel-manager-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let cmsPreviewLoadingSession = 0;
    const CMS_ENTRY_LOADING_KEY = 'cms-content-entry-loading';

    function clearCmsEntryLoadingState() {
        document.documentElement.classList.remove('cms-entry-loading');

        try {
            sessionStorage.removeItem(CMS_ENTRY_LOADING_KEY);
        } catch (error) {
            // Ignore storage access failures and keep the page usable.
        }
    }

    function setFloatingVoiceVisibility(isVisible) {
        const floatingVoiceBtn = document.getElementById('floatingVoiceBtn');
        if (!floatingVoiceBtn) {
            return;
        }

        floatingVoiceBtn.style.opacity = isVisible ? '1' : '0';
        floatingVoiceBtn.style.visibility = isVisible ? 'visible' : 'hidden';
        floatingVoiceBtn.style.pointerEvents = isVisible ? 'auto' : 'none';
    }

    function showCmsPreviewLoading(sessionId) {
        const overlay = document.querySelector('[data-cms-page-loader]');
        const normalizedSessionId = Number(sessionId || (Date.now() + Math.random()));

        cmsPreviewLoadingSession = normalizedSessionId;

        if (overlay) {
            overlay.hidden = false;
        }

        setFloatingVoiceVisibility(false);

        return normalizedSessionId;
    }

    function hideCmsPreviewLoading(sessionId) {
        const overlay = document.querySelector('[data-cms-page-loader]');
        const normalizedSessionId = Number(sessionId || 0);

        if (!overlay) {
            return;
        }

        if (normalizedSessionId && normalizedSessionId !== cmsPreviewLoadingSession) {
            return;
        }

        overlay.hidden = true;
        clearCmsEntryLoadingState();
        setFloatingVoiceVisibility(true);
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) {
            return;
        }

        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.toggle('sidebar-expanded');
            return;
        }

        sidebar.classList.remove('sidebar-expanded');
        sidebar.classList.toggle('collapsed');
    }

    function setCmsTabState(tabKey, btn) {
        document.querySelectorAll('.cms-tab-btn').forEach((el) => {
            const isActive = el === btn;
            el.classList.toggle('active', isActive);
            el.setAttribute('aria-selected', isActive ? 'true' : 'false');
            el.setAttribute('tabindex', isActive ? '0' : '-1');
            el.closest('.tab-navigation-item')?.classList.toggle('is-active', isActive);
        });

        document.querySelectorAll('.cms-tab-panel').forEach((el) => {
            const isActive = el.id === 'cms-tab-' + tabKey;
            el.classList.toggle('active', isActive);
            el.hidden = !isActive;
        });
    }

    function switchCmsTab(tabKey, btn) {
        const sessionId = showCmsPreviewLoading();
        setCmsTabState(tabKey, btn);
        const nextPanel = document.getElementById('cms-tab-' + tabKey);
        localStorage.setItem('activeStaffCmsTab', tabKey);
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });

        window.dispatchEvent(new CustomEvent('cms:tab-activated', {
            detail: {
                tabKey,
                panel: nextPanel || null,
                sessionId,
            },
        }));

        const hasPreview = !!nextPanel?.querySelector('[data-home-preview-frame], [data-about-preview-frame], [data-academics-preview-frame], [data-students-preview-frame], [data-research-preview-frame], [data-events-preview-frame]');
        if (!hasPreview) {
            window.setTimeout(() => hideCmsPreviewLoading(sessionId), 1500);
        }
    }

    function bindCmsTabKeyboardNav() {
        const tabs = Array.from(document.querySelectorAll('.cms-tab-btn'));
        if (!tabs.length) {
            return;
        }

        tabs.forEach((tab, index) => {
            tab.addEventListener('keydown', (event) => {
                let nextIndex = null;

                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                    nextIndex = (index + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    nextIndex = (index - 1 + tabs.length) % tabs.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = tabs.length - 1;
                }

                if (nextIndex === null) {
                    return;
                }

                event.preventDefault();
                const nextTab = tabs[nextIndex];
                nextTab?.focus();
                nextTab?.click();
            });
        });
    }

    function getTrackableFields(form) {
        const ignored = new Set(['_token', 'tab_key', 'section_key', 'request_id']);
        return Array.from(form.elements).filter((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                return false;
            }

            if (!field.name || ignored.has(field.name)) {
                return false;
            }

            const type = (field.type || '').toLowerCase();
            if (type === 'submit' || type === 'button' || type === 'reset') {
                return false;
            }

            return true;
        });
    }

    function fieldValue(field) {
        if (field instanceof HTMLInputElement) {
            const type = (field.type || '').toLowerCase();
            if (type === 'checkbox' || type === 'radio') {
                return field.checked ? '1' : '0';
            }
        }

        return field.value ?? '';
    }

    function syncFormEditors(form) {
        if (typeof window.syncRichTextEditors === 'function') {
            window.syncRichTextEditors(form);
        }

        ensureRichTextChangeMarker(form);
    }

    function ensureRichTextChangeMarker(form) {
        if (!form || !form.querySelector('.js-rich-editor') || form.querySelector('[data-cms-rich-editor-version]')) {
            return;
        }

        const marker = document.createElement('input');
        marker.type = 'hidden';
        marker.name = 'cms_rich_editor_version';
        marker.value = '0';
        marker.setAttribute('data-cms-rich-editor-version', '');
        form.appendChild(marker);
    }

    function normalizeCmsSnapshotString(value) {
        return String(value ?? '').replace(/\r\n?/g, '\n').trim();
    }

    function normalizeCmsHistoryBody(value) {
        return normalizeCmsSnapshotString(value).replace(/\n{3,}/g, '\n\n');
    }

    function deepEqualCmsSnapshot(left, right) {
        return JSON.stringify(left) === JSON.stringify(right);
    }

    function getHistoryInputValue(scope, selector) {
        return normalizeCmsSnapshotString(scope.querySelector(selector)?.value ?? '');
    }

    function getHistoryFormSnapshot(form) {
        syncFormEditors(form);

        if (typeof window.syncAboutHistoryDateFields === 'function') {
            window.syncAboutHistoryDateFields(form);
        }

        const section = {
            label: getHistoryInputValue(form, '[name="about[sections][history][label]"]'),
            summary: getHistoryInputValue(form, '[name="about[sections][history][summary]"]'),
            page_kicker: getHistoryInputValue(form, '[name="about[sections][history][page_kicker]"]'),
            page_title: getHistoryInputValue(form, '[name="about[sections][history][page_title]"]'),
        };

        const timeline = Array.from(form.querySelectorAll('[data-about-history-editor]')).map((editor) => ({
            visible: getHistoryInputValue(editor, '[data-about-history-visible]') || '1',
            period: getHistoryInputValue(editor, '[data-about-history-period]'),
            title: getHistoryInputValue(editor, 'input[name$="[title]"]'),
            body: normalizeCmsHistoryBody(editor.querySelector('.rich-editor-input')?.value ?? ''),
        }));

        return { section, timeline };
    }

    function captureHistoryFormSnapshot(form) {
        form.dataset.initialHistorySnapshot = JSON.stringify(getHistoryFormSnapshot(form));
    }

    function historyFormHasChanges(form) {
        const initial = form.dataset.initialHistorySnapshot || '';

        if (!initial) {
            captureHistoryFormSnapshot(form);
            return false;
        }

        return !deepEqualCmsSnapshot(getHistoryFormSnapshot(form), JSON.parse(initial));
    }

    function captureFormSnapshot(form) {
        syncFormEditors(form);
        if (form.matches('[data-about-history-form]')) {
            captureHistoryFormSnapshot(form);
        }

        getTrackableFields(form).forEach((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return;
            }

            field.dataset.initialValue = fieldValue(field);
        });
    }

    function formHasChanges(form) {
        if (form.matches('[data-about-history-form]')) {
            return historyFormHasChanges(form);
        }

        syncFormEditors(form);
        return getTrackableFields(form).some((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return !!(field.files && field.files.length > 0);
            }

            return (field.dataset.initialValue ?? '') !== fieldValue(field);
        });
    }

    function hasServerTrackedCardVersion(form) {
        return !!form.querySelector(
            '[data-home-quick-links-version], ' +
            '[data-home-feedback-questions-version], ' +
            '[data-cms-rich-editor-version], ' +
            '[data-about-intro-version], ' +
            '[data-about-contents-version], ' +
            '[data-about-history-version], ' +
            '[data-about-officials-version], ' +
            '[data-about-plan-priorities-version], ' +
            '[data-about-strategic-goals-version], ' +
            '[data-about-core-values-version], ' +
            '[data-academics-contents-version], ' +
            '[data-academics-features-version], ' +
            '[data-students-cards-version], ' +
            '[data-research-cards-version], ' +
            '[data-events-cards-version]'
        );
    }

    function getActiveCsrfToken(form) {
        const formToken = form.querySelector('input[name="_token"]')?.value?.trim();
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')?.trim();
        return formToken || metaToken || CSRF || '';
    }

    function resolveAboutPreviewRoute(sectionKey) {
        const normalizedSectionKey = String(sectionKey || '').trim().toLowerCase();

        if (!normalizedSectionKey || normalizedSectionKey === 'hero' || normalizedSectionKey === 'intro' || normalizedSectionKey === 'contents') {
            return 'overview';
        }

        if (
            normalizedSectionKey === 'vision-mission-header'
            || normalizedSectionKey === 'vision-statement'
            || normalizedSectionKey === 'mission-statement'
            || normalizedSectionKey === 'vision-mission-statements'
            || normalizedSectionKey === 'strategic-goals'
            || normalizedSectionKey === 'core-values'
        ) {
            return 'vision-and-mission';
        }

        if (normalizedSectionKey === 'strategic-development-plan-header') {
            return 'strategic-development-plan';
        }

        return normalizedSectionKey;
    }

    function persistCmsPreviewContextBeforeReload(form) {
        const tabKey = String(form.querySelector('input[name="tab_key"]')?.value || '').trim().toLowerCase();
        const sectionKey = String(form.querySelector('input[name="section_key"]')?.value || '').trim();

        if (!tabKey) {
            return;
        }

        localStorage.setItem('activeStaffCmsTab', tabKey);

        if (tabKey === 'about') {
            const routeKey = resolveAboutPreviewRoute(sectionKey);
            const aboutPreviewStorageKey = `cms:about-preview-route:${window.location.pathname}`;
            const aboutPreviewLegacyStorageKey = 'about-editor-active-about-preview-page';

            localStorage.setItem(aboutPreviewStorageKey, routeKey);
            localStorage.setItem(aboutPreviewLegacyStorageKey, routeKey);
            return;
        }

        if (tabKey === 'academics') {
            const normalizedSectionKey = sectionKey.toLowerCase();
            const pageMatch = normalizedSectionKey.match(/^(degree-programs|diploma-programs|pup-iapply|university-calendar)(?:-|$)/);
            const routeKey = pageMatch ? pageMatch[1] : 'overview';
            const academicsPreviewStorageKey = `cms:academics-preview-route:${window.location.pathname}`;
            const academicsPreviewLegacyStorageKey = 'academics-editor-active-academics-preview-page';

            localStorage.setItem(academicsPreviewStorageKey, routeKey);
            localStorage.setItem(academicsPreviewLegacyStorageKey, routeKey);
        }
    }

    async function postForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        syncFormEditors(form);
        const csrfToken = getActiveCsrfToken(form);
        const formData = new FormData(form);

        if (csrfToken) {
            formData.set('_token', csrfToken);
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.message || json.error || raw.slice(0, 180) || ('Request failed (' + res.status + ')'));
            }

            if (typeof window.queueToast === 'function') {
                if (json.no_changes) {
                    window.queueToast(json.message || 'No changes detected.', 'info', 'No Changes');
                } else {
                    window.queueToast(json.message || 'Request submitted for approval.', 'success', 'Success');
                }
            } else if (typeof window.showToast === 'function') {
                if (json.no_changes) {
                    window.showToast(json.message || 'No changes detected.', 'info', 'No Changes');
                } else {
                    window.showToast(json.message || 'Request submitted for approval.', 'success', 'Success');
                }
            }

            if (!json.no_changes) {
                captureFormSnapshot(form);
                persistCmsPreviewContextBeforeReload(form);
                window.location.reload();
            } else if (submitBtn) {
                submitBtn.disabled = false;
            }
        } catch (err) {
            if (typeof window.showToast === 'function') {
                window.showToast(err.message, 'error', 'Request Failed');
            } else {
                alert(err.message);
            }
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    document.querySelectorAll('.cms-edit-form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const allowServerTrackedVersion = !form.matches('[data-about-history-form]') && hasServerTrackedCardVersion(form);
            if (!formHasChanges(form) && !allowServerTrackedVersion) {
                if (typeof window.showToast === 'function') {
                    window.showToast('No changes detected.', 'info', 'No Changes');
                } else {
                    alert('No changes detected.');
                }
                return;
            }
            postForm(form);
        });
    });

    function initHomeDropzones() {
        document.querySelectorAll('.home-dropzone-input').forEach((input) => {
            const label = document.querySelector(`.home-dropzone[for="${input.id}"]`);
            const fileNameEl = document.querySelector(`[data-file-name-for="${input.id}"]`);
            const previewEl = document.querySelector(`[data-preview-for="${input.id}"]`);
            if (!label || !fileNameEl) return;

            const applyFile = (file) => {
                if (!file) return;
                fileNameEl.textContent = `Selected: ${file.name}`;

                if (previewEl) {
                    const url = URL.createObjectURL(file);
                    previewEl.src = url;
                }
            };

            input.addEventListener('change', () => {
                const file = input.files && input.files[0] ? input.files[0] : null;
                applyFile(file);
            });

            label.addEventListener('dragover', (e) => {
                e.preventDefault();
                label.classList.add('dragover');
            });

            label.addEventListener('dragleave', () => {
                label.classList.remove('dragover');
            });

            label.addEventListener('drop', (e) => {
                e.preventDefault();
                label.classList.remove('dragover');

                const files = e.dataTransfer?.files;
                if (!files || files.length === 0) return;

                const file = files[0];
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                applyFile(file);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindCmsTabKeyboardNav();

        if (window.__cmsEntryLoading) {
            const entrySessionId = showCmsPreviewLoading();
            window.setTimeout(() => hideCmsPreviewLoading(entrySessionId), 2500);
        }

        window.addEventListener('cms:preview-loading', (event) => {
            showCmsPreviewLoading(event.detail?.sessionId);
        });

        window.addEventListener('cms:preview-loaded', (event) => {
            hideCmsPreviewLoading(event.detail?.sessionId);
        });

        const saved = localStorage.getItem('activeStaffCmsTab');
        if (saved) {
            const btn = Array.from(document.querySelectorAll('.cms-tab-btn'))
                .find((el) => el.dataset.tabKey === saved);

            if (btn) {
                switchCmsTab(saved, btn);
            }
        }

        initHomeDropzones();
        document.querySelectorAll('.cms-edit-form').forEach((form) => captureFormSnapshot(form));
    });

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

let activeRecognition = null;
let activeEditableField = null;
let activeFloatingButton = null;

function appendSpeechText(currentValue, spokenText) {
    const current = String(currentValue || '').trim();
    const spoken = String(spokenText || '').trim();
    if (!current) return spoken;
    if (!spoken) return current;
    return `${current} ${spoken}`.trim();
}

function stopFloatingVoiceRecognition() {
    if (activeRecognition) {
        activeRecognition.stop();
        activeRecognition = null;
    }

    if (activeFloatingButton) {
        activeFloatingButton.classList.remove('listening');
        activeFloatingButton = null;
    }
}

function setActiveEditableField(el) {
    activeEditableField = el;
}

function getRichTextHiddenFieldFromEditor(target) {
    const form = target.closest('form');
    if (!form) return null;
    return form.querySelector('[name="content"]');
}

function insertSpeechIntoActiveField(text) {
    if (!activeEditableField) {
        alert('Click a field first, then use speech-to-text.');
        return;
    }

    const target = activeEditableField;

    if (target.matches('input[type="text"], textarea')) {
        target.value = appendSpeechText(target.value, text);
        target.dispatchEvent(new Event('input', { bubbles: true }));

        if (target.id === 'globalSearch') {
            runSearch();
        }

        target.focus();
        return;
    }

    const richTextField = getRichTextHiddenFieldFromEditor(target);
    if (richTextField) {
        const nextValue = appendSpeechText(richTextField.value, text);
        setRichTextEditorValue(richTextField, nextValue);
        richTextField.dispatchEvent(new Event('input', { bubbles: true }));
        return;
    }

    alert('Selected field is not supported for speech input.');
}

function startFloatingVoiceRecognition(buttonEl) {
    if (!SpeechRecognition) {
        alert('Voice input is not supported in this browser. Please use Edge or Chrome.');
        return;
    }

    if (activeRecognition && activeFloatingButton === buttonEl) {
        stopFloatingVoiceRecognition();
        return;
    }

    if (activeRecognition) {
        stopFloatingVoiceRecognition();
    }

    const recognition = new SpeechRecognition();
    activeRecognition = recognition;
    activeFloatingButton = buttonEl;

    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = function () {
        buttonEl.classList.add('listening');
        console.log('floating recognition started');
    };

    recognition.onerror = function (event) {
        console.error('Speech recognition error:', event.error);
        stopFloatingVoiceRecognition();
    };

    recognition.onend = function () {
        if (activeRecognition === recognition) {
            stopFloatingVoiceRecognition();
        }
    };

    recognition.onresult = function (event) {
        const transcript = (event.results?.[0]?.[0]?.transcript || '')
            .trim()
            .replace(/[^a-zA-Z0-9\s@_-]/g, '')
            .replace(/\s+/g, ' ');

        console.log('floating transcript:', transcript);

        if (!transcript) return;
        insertSpeechIntoActiveField(transcript);
    };

    try {
        recognition.start();
    } catch (err) {
        console.error('recognition.start failed:', err);
        stopFloatingVoiceRecognition();
    }
}

document.addEventListener('focusin', function (e) {
    const target = e.target;

    if (target.matches('input[type="text"], textarea')) {
        setActiveEditableField(target);
        return;
    }

    if (
        target.closest('.tox, .ck-editor, [contenteditable="true"], .rich-text-editor') ||
        target.isContentEditable
    ) {
        setActiveEditableField(target);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const floatingVoiceBtn = document.getElementById('floatingVoiceBtn');
    if (floatingVoiceBtn) {
        floatingVoiceBtn.addEventListener('click', function () {
            console.log('floating mic clicked');
            console.log('activeEditableField:', activeEditableField);
            startFloatingVoiceRecognition(floatingVoiceBtn);
        });
    }
});
</script>
<button type="button" id="floatingVoiceBtn" class="floating-voice-btn" title="Speech to text">
    <i class="fas fa-microphone"></i>
</button>
</body>
</html>
