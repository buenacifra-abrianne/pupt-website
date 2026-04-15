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
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/pupt_cms_logo.png') }}" alt="PUPT CMS Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.announcements') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.content') ?? '#' }}" class="nav-link active">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.downloadables') ?? '#' }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Downloadables</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.notifications') ?? '#' }}" class="nav-link">
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
            <p class="page-subtitle">Global content editor for admin. Staff edits are reviewed in Pending Approvals.</p>
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
                            @php $pendingForTab = (int)($pendingByTab[$tabKey] ?? 0); @endphp
                            @if($pendingForTab > 0)
                                <span class="tab-badge">{{ $pendingForTab }}</span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        </nav>

        @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
            @php
                $live = $contentsByTab[$tabKey] ?? ['title' => $tabDef['label'].' Content', 'content' => ''];
                $homeLive = $tabKey === 'home'
                    ? \App\Support\HomeCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $aboutLive = $tabKey === 'about'
                    ? \App\Support\AboutCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $academicsLive = $tabKey === 'academics'
                    ? \App\Support\AcademicsCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $researchLive = $tabKey === 'research_extension'
                    ? \App\Support\ResearchCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $eventsLive = $tabKey === 'events'
                    ? \App\Support\EventsCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
            @endphp

            <div
                id="cms-tab-{{ $tabKey }}"
                class="tab-content cms-tab-panel {{ $loop->first ? 'active' : '' }}"
                role="tabpanel"
                aria-labelledby="cms-tab-trigger-{{ $tabKey }}"
                @unless($loop->first) hidden @endunless
            >
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
                        'homeEditorData' => $homeLive,
                        'homeEditorFormClass' => 'cms-save-form',
                        'homeEditorSubmitRoute' => route('admin.content.save'),
                        'homeEditorSubmitMode' => 'save',
                        'homeEditorIdPrefix' => 'admin-home',
                    ])
                @elseif($tabKey === 'about')
                    @include('partials.about_cms_preview_editor', [
                        'aboutEditorData' => $aboutLive,
                        'aboutEditorFormClass' => 'cms-save-form',
                        'aboutEditorSubmitRoute' => route('admin.content.save'),
                        'aboutEditorSubmitMode' => 'save',
                        'aboutEditorIdPrefix' => 'admin-about',
                    ])
                @elseif($tabKey === 'academics')
                    @php
                        $academicsPreviewHtml = view('public.academics', [
                            'academicsCms' => $academicsLive,
                            'cmsPreview' => true,
                        ])->render();
                    @endphp

                    @include('partials.academics_cms_preview_editor', [
                        'academicsPreviewHtml' => $academicsPreviewHtml,
                        'academicsEditorData' => $academicsLive,
                        'academicsEditorFormClass' => 'cms-save-form',
                        'academicsEditorSubmitRoute' => route('admin.content.save'),
                        'academicsEditorSubmitMode' => 'save',
                        'academicsEditorIdPrefix' => 'admin-academics',
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
                        'researchEditorData' => $researchLive,
                        'researchEditorFormClass' => 'cms-save-form',
                        'researchEditorSubmitRoute' => route('admin.content.save'),
                        'researchEditorSubmitMode' => 'save',
                        'researchEditorIdPrefix' => 'admin-research',
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
                        'eventsEditorData' => $eventsLive,
                        'eventsEditorFormClass' => 'cms-save-form',
                        'eventsEditorSubmitRoute' => route('admin.content.save'),
                        'eventsEditorSubmitMode' => 'save',
                        'eventsEditorIdPrefix' => 'admin-events',
                    ])
                @else
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Manage {{ $tabDef['label'] }} Content</h3>
                            <span class="status-badge status-enabled">Live Update</span>
                        </div>

                        <div style="padding:14px;">
                            <form class="cms-save-form" method="POST" action="{{ route('admin.content.save') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tab_key" value="{{ $tabKey }}">

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" maxlength="255" value="{{ $live['title'] }}">
                                </div>

                                <div class="form-group">
                                    <label>Content</label>
                                    <textarea name="content" rows="13">{{ $live['content'] }}</textarea>
                                </div>

                                <div style="display:flex;justify-content:flex-end;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Live Content
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
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
    .stat-change { font-size: 12px; opacity: .85; margin-top: 4px; }

    .tab-badge {
        min-width: 1.45rem;
        height: 1.45rem;
        margin-left: 10px;
        display: inline-grid;
        place-items: center;
        padding: 0 6px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.14);
        color: #fff3d5;
        font-size: 0.7rem;
        font-weight: 700;
        line-height: 1;
    }

    .tab-navigation-item.is-active .tab-badge,
    .tab-navigation-item:hover .tab-badge {
        border-color: rgba(243, 196, 90, 0.32);
        background: rgba(243, 196, 90, 0.16);
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
    }

    @media (max-width: 480px) {
        :root {
            --cms-tab-bleed: 20px;
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
        localStorage.setItem('activeAdminCmsTab', tabKey);
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });

        window.dispatchEvent(new CustomEvent('cms:tab-activated', {
            detail: {
                tabKey,
                panel: nextPanel || null,
                sessionId,
            },
        }));

        const hasPreview = !!nextPanel?.querySelector('[data-home-preview-frame], [data-about-preview-frame], [data-academics-preview-frame], [data-events-preview-frame]');
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
    }

    function captureFormSnapshot(form) {
        syncFormEditors(form);
        getTrackableFields(form).forEach((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return;
            }

            field.dataset.initialValue = fieldValue(field);
        });
    }

    function formHasChanges(form) {
        syncFormEditors(form);
        return getTrackableFields(form).some((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return !!(field.files && field.files.length > 0);
            }

            return (field.dataset.initialValue ?? '') !== fieldValue(field);
        });
    }

    async function submitSave(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        syncFormEditors(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form)
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.message || json.error || raw.slice(0, 180) || ('Request failed (' + res.status + ')'));
            }

            if (typeof window.showToast === 'function') {
                if (json.no_changes) {
                    window.showToast(json.message || 'No changes detected.', 'info', 'No Changes');
                } else {
                    window.showToast(json.message || 'Content saved.', 'success', 'Success');
                }
            }

            if (!json.no_changes) {
                window.location.reload();
            }
        } catch (err) {
            if (typeof window.showToast === 'function') {
                window.showToast(err.message, 'error', 'Request Failed');
            } else {
                alert(err.message);
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    document.querySelectorAll('.cms-save-form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!formHasChanges(form)) {
                if (typeof window.showToast === 'function') {
                    window.showToast('No changes detected.', 'info', 'No Changes');
                } else {
                    alert('No changes detected.');
                }
                return;
            }
            submitSave(form);
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

        const saved = localStorage.getItem('activeAdminCmsTab');
        if (saved) {
            const btn = Array.from(document.querySelectorAll('.cms-tab-btn'))
                .find((el) => el.dataset.tabKey === saved);

            if (btn) {
                switchCmsTab(saved, btn);
            }
        }

        initHomeDropzones();
        document.querySelectorAll('.cms-save-form').forEach((form) => captureFormSnapshot(form));
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
<x-app.legal-footer />
<button type="button" id="floatingVoiceBtn" class="floating-voice-btn" title="Speech to text">
    <i class="fas fa-microphone"></i>
</button>
</body>
</html>
