<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/students.css') }}?v={{ filemtime(public_path('assets/css/students.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $studentsCms = \App\Support\StudentsCmsContent::fromInput($studentsCms ?? [], null);
        $pageSection = $studentsCms['page'] ?? [];
        $cards = collect($studentsCms['cards'] ?? [])
            ->filter(fn ($card) => is_array($card))
            ->values();
        $defaultCards = collect(\App\Support\StudentsCmsContent::defaults()['cards'] ?? [])
            ->filter(fn ($card) => is_array($card))
            ->values();
        $requiredStudentCards = $defaultCards
            ->filter(fn ($card) => in_array(strtolower(trim((string) ($card['title'] ?? ''))), ['admissions', 'downloadable forms'], true));
        $existingCardTitles = $cards
            ->map(fn ($card) => strtolower(trim((string) ($card['title'] ?? ''))))
            ->all();
        $cards = $cards
            ->concat($requiredStudentCards
                ->reject(fn ($card) => in_array(strtolower(trim((string) ($card['title'] ?? ''))), $existingCardTitles, true))
                ->map(fn ($card) => array_merge($card, ['_required_card' => true])))
            ->values();
        $organizationSections = collect($studentsCms['organization_sections'] ?? [])
            ->filter(fn ($section) => is_array($section))
            ->values();
    @endphp

    @unless($cmsPreview)
        <pup-header
            data-home="{{ route('public.home') }}"
            data-about="{{ route('public.about') }}"
            data-academics="{{ route('public.academics') }}"
            data-students="{{ route('public.students') }}"
            data-news-events="{{ route('public.events') }}"
            data-research="{{ route('public.research') }}"
            data-assets="{{ asset('assets') }}"
        ></pup-header>
    @endunless

    <main class="main-content students-review-page">

        <section
            class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="page"
                data-cms-section-label="Students Page Header"
            @endif
        >
            @if($cmsPreview)
                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="page" aria-label="Edit Students Page Header">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                    </svg>
                </button>
            @endif

            <div data-cms-boundary class="cms-preview-boundary-full">
                <section class="carousel-section">
                    <div class="carousel full-carousel">
                        <div class="carousel-stage">
                            <div class="carousel-slide active">
                                <div class="carousel-split" aria-hidden="true">
                                    <img src="{{ \App\Support\StudentsCmsContent::resolveImagePath($pageSection['hero_image'] ?? null, 'assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                                </div>
                                <div class="carousel-caption">
                                    <h2>{{ strtoupper((string) ($pageSection['title'] ?? 'Students')) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal{{ $cmsPreview ? ' active' : '' }}" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Students</strong>
            </nav>
        </section>

        <section
            class="students-contents-strip reveal{{ $cmsPreview ? ' active cms-preview-editable' : '' }}"
        >
            <div class="students-contents-head layout-inset">
                        <p class="section-tag">{{ trim((string) ($pageSection['contents_tag'] ?? '')) !== '' ? (string) $pageSection['contents_tag'] : 'Contents' }}</p>
                        <h2>{{ trim((string) ($pageSection['contents_title'] ?? '')) !== '' ? (string) $pageSection['contents_title'] : 'Student Services' }}</h2>
                        @if(trim((string) ($pageSection['contents_description'] ?? '')) !== '')
                            <div class="students-contents-description students-rich-copy">{!! \App\Support\RichText::sanitize($pageSection['contents_description']) !!}</div>
                        @endif
                    </div>

            <div>
                <div class="students-contents-inner">
                    <nav class="students-cards" aria-label="Student services">
                        @if($cmsPreview)
                            <article class="students-card students-card-add" data-students-add-card-trigger tabindex="0" role="button" aria-label="Add services">
                                <div class="students-card-inner">
                                    <div class="students-card-front students-card-front-add">
                                        <div class="students-card-add-inner">
                                            <span class="students-card-add-plus" aria-hidden="true">+</span>
                                            <p class="students-card-add-label">Add Services</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endif

                        @forelse($cards as $card)
                            @php
                                $cardLink = trim((string) ($card['link'] ?? ''));
                                $cardTitle = trim((string) ($card['title'] ?? ''));
                                $cardDescription = trim((string) ($card['description'] ?? ''));
                                $cardImage = trim((string) ($card['image'] ?? 'assets/static_img/pupillar.jpeg'));
                                $cardTitleKey = strtolower($cardTitle);
                                $isExternalCardLink = preg_match('/^https?:\/\//i', $cardLink) === 1;
                                $isRequiredDisplayCard = (bool) ($card['_required_card'] ?? false);
                            @endphp

                            @if($cmsPreview)
                                <article
                                    class="students-card"
                                    data-cms-edit-trigger="cards"
                                    data-cms-section-label="Student Cards"
                                    data-students-card-index="{{ $loop->index }}"
                                >
                            @else
                                <a
                                    href="{{ $cardLink !== '' ? $cardLink : '#' }}"
                                    @if($isExternalCardLink)
                                        target="_blank" rel="noopener noreferrer"
                                    @endif
                                    class="students-card"
                                >
                            @endif
                                @if($cmsPreview)
                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                        @unless($isRequiredDisplayCard)
                                            <button type="button" class="cms-preview-card-action" data-students-card-edit title="Edit card" aria-label="Edit {{ $cardTitle !== '' ? $cardTitle : 'student card' }}">
                                                Edit
                                            </button>
                                        @endunless
                                        @unless($isRequiredDisplayCard || in_array($cardTitleKey, ['admissions', 'downloadable forms'], true))
                                            <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-students-card-delete title="Delete card" aria-label="Delete {{ $cardTitle !== '' ? $cardTitle : 'student card' }}">
                                                Delete
                                            </button>
                                        @endunless
                                        @if($cardTitleKey === 'admissions')
                                            <button type="button" class="cms-preview-card-action" data-cms-edit-trigger="admissions_page" data-cms-section-label="Admissions Page" title="Edit admissions page" aria-label="Edit admissions page">
                                                Page
                                            </button>
                                        @elseif($cardTitleKey === 'downloadable forms')
                                            <button type="button" class="cms-preview-card-action" data-cms-edit-trigger="downloadable_forms_page" data-cms-section-label="Downloadable Forms Page" title="Edit downloadable forms page" aria-label="Edit downloadable forms page">
                                                Page
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                <div class="students-card-inner">
                                    <div class="students-card-front">
                                        <img src="{{ \App\Support\StudentsCmsContent::resolveImagePath($cardImage !== '' ? $cardImage : null, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $cardTitle !== '' ? $cardTitle : 'Student card' }}">
                                        <div class="students-card-copy">
                                            <h3>{{ $cardTitle !== '' ? $cardTitle : 'Student Card' }}</h3>
                                        </div>
                                    </div>

                                    @unless($cmsPreview)
                                        <div class="students-card-back">
                                            <div class="students-card-overlay-copy">
                                                <h3>{{ $cardTitle !== '' ? $cardTitle : 'Student Card' }}</h3>
                                                <p>{{ \Illuminate\Support\Str::limit($cardDescription !== '' ? $cardDescription : 'Explore this student service and open the official page for more details.', 120) }}</p>
                                            </div>
                                            <span class="students-card-action">See more</span>
                                        </div>
                                    @endunless
                                </div>
                            @if($cmsPreview)
                                </article>
                            @else
                                </a>
                            @endif
                        @empty
                            <article class="students-card students-card-empty">
                                <div class="students-card-inner">
                                    <div class="students-card-front">
                                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Student placeholder">
                                        <div class="students-card-copy">
                                            <h3>No student cards yet</h3>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforelse
                    </nav>
                </div>
            </div>
        </section>

        {{-- Student Organizations Section --}}
        <section class="students-orgs-section reveal{{ $cmsPreview ? ' active' : '' }}">
            <div class="students-orgs-inner">
                <div class="students-orgs-blurred students-orgs-live">

                @foreach($organizationSections as $sectionIndex => $organizationSection)
                <div class="students-orgs-group">
                    <div>
                        <p class="section-tag">{{ $organizationSection['title'] ?? 'Student Organizations' }}</p>

                        <div class="students-orgs-grid">
                            @foreach(($organizationSection['items'] ?? []) as $orgIndex => $organization)
                                @php
                                    $orgTitle = trim((string) ($organization['title'] ?? ''));
                                    $orgAbbr = trim((string) ($organization['abbr'] ?? ''));
                                    $orgLink = trim((string) ($organization['link'] ?? '#'));
                                    $orgImage = trim((string) ($organization['image'] ?? 'assets/static_img/pupillar.jpeg'));
                                @endphp

                                @if($cmsPreview)
                                    <article
                                        class="students-org-card"
                                        data-cms-edit-trigger="organizations"
                                        data-cms-section-label="{{ $organizationSection['title'] ?? 'Student Organizations' }}"
                                        data-students-org-section-index="{{ $sectionIndex }}"
                                        data-students-org-index="{{ $orgIndex }}"
                                    >
                                @else
                                    <a href="{{ $orgLink !== '' ? $orgLink : '#' }}" target="_blank" rel="noopener noreferrer" class="students-org-card">
                                @endif
                                    @if($cmsPreview)
                                        <div class="cms-preview-card-actions" aria-label="Organization card actions">
                                            <button type="button" class="cms-preview-card-action" data-students-org-edit title="Edit organization" aria-label="Edit {{ $orgTitle !== '' ? $orgTitle : 'organization' }}">
                                                Edit
                                            </button>
                                        </div>
                                    @endif

                                    <div class="students-org-img-wrap">
                                        <img src="{{ \App\Support\StudentsCmsContent::resolveImagePath($orgImage !== '' ? $orgImage : null, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $orgAbbr !== '' ? $orgAbbr : $orgTitle }}">
                                    </div>
                                    <div class="students-org-copy">
                                        <h3>{{ $orgTitle !== '' ? $orgTitle : 'Organization' }}</h3>
                                        <span class="students-org-abbr">{{ $orgAbbr }}</span>
                                    </div>
                                @if($cmsPreview)
                                    </article>
                                @else
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
    </main>

    @unless($cmsPreview)
        <pup-footer></pup-footer>

        <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
        <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    @endunless

    @if($cmsPreview)
        <style>
            html,
            body {
                overflow: hidden !important;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            html::-webkit-scrollbar,
            body::-webkit-scrollbar {
                width: 0 !important;
                height: 0 !important;
                display: none !important;
            }

            .main-content.students-review-page {
                overflow: hidden !important;
            }

            .reveal {
                opacity: 1 !important;
                transform: none !important;
            }

            .hero-shell,
            .students-contents-strip.cms-preview-editable,
            .students-orgs-group.cms-preview-editable {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                width: 100% !important;
                max-width: 100% !important;
                left: auto !important;
                right: auto !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                transform: none !important;
            }

            .cms-preview-editable {
                position: relative;
                cursor: pointer;
                isolation: isolate;
                overflow: visible !important;
            }

            .cms-preview-editable > [data-cms-boundary] {
                position: relative;
                display: block;
                width: auto;
                max-width: none;
                min-width: 0;
                margin: var(--cms-preview-outline-offset);
                box-sizing: border-box;
                overflow: visible !important;
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
                width: calc(100% - (var(--cms-preview-outline-offset) * 2));
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge {
                width: 100%;
                margin: 0;
            }

            .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
                width: 100%;
                margin: 0;
            }

            .cms-preview-editable > [data-cms-boundary]::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 2;
                box-sizing: border-box;
                pointer-events: none;
                border: 2px dashed rgba(242, 201, 76, 0.95);
                border-radius: 24px;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22);
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge::after {
                inset: var(--cms-preview-outline-offset);
            }

            .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full::after {
                inset: var(--cms-preview-outline-offset);
            }

            .cms-preview-editable > * {
                position: relative;
                z-index: 1;
            }

            .cms-preview-chip {
                position: absolute;
                top: var(--cms-preview-chip-top-offset);
                right: calc(var(--cms-preview-chip-right-offset) + var(--cms-preview-outline-offset));
                transform: translateY(-50%);
                z-index: 9;
                border: none;
                border-radius: 12px;
                width: 44px;
                min-width: 44px;
                height: 44px;
                padding: 0;
                background: rgba(127, 17, 19, 0.96);
                color: #fffaf4;
                display: none !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
            }

            .cms-preview-chip svg {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }

            .students-card[data-cms-edit-trigger] {
                position: relative;
                cursor: default;
                isolation: isolate;
            }

            .students-contents-strip.cms-preview-editable {
                overflow: visible !important;
            }

            .students-contents-strip.cms-preview-editable .students-cards {
                grid-auto-flow: row;
                grid-auto-columns: unset;
                grid-template-columns: repeat(auto-fit, minmax(240px, 250px));
                justify-content: start;
                overflow: visible !important;
                padding-bottom: 0;
                scroll-snap-type: none;
                touch-action: auto;
            }

            .students-contents-strip.cms-preview-editable .students-card {
                min-width: 0;
            }

            .students-contents-strip.cms-preview-editable .students-card,
            .students-contents-strip.cms-preview-editable .students-card:hover,
            .students-contents-strip.cms-preview-editable .students-card:focus-visible,
            .students-contents-strip.cms-preview-editable .students-card.active {
                transform: none !important;
                transition: none !important;
                box-shadow: 0 16px 34px rgba(77, 18, 18, 0.12) !important;
            }

            .students-contents-strip.cms-preview-editable .students-card-inner,
            .students-contents-strip.cms-preview-editable .students-card-front,
            .students-contents-strip.cms-preview-editable .students-card-back,
            .students-contents-strip.cms-preview-editable .students-card-front img,
            .students-contents-strip.cms-preview-editable .students-card-copy,
            .students-contents-strip.cms-preview-editable .students-card-overlay-copy,
            .students-contents-strip.cms-preview-editable .students-card-action {
                transition: none !important;
            }

            .students-contents-strip.cms-preview-editable .students-card-back {
                opacity: 0 !important;
                transform: translateY(100%) !important;
                pointer-events: none !important;
            }

            .students-contents-strip.cms-preview-editable .students-card-front img {
                transform: none !important;
                filter: none !important;
            }

            .students-contents-strip.cms-preview-editable .students-card-overlay-copy,
            .students-contents-strip.cms-preview-editable .students-card-action {
                opacity: 0 !important;
                transform: translateY(18px) !important;
            }

            .students-contents-strip.cms-preview-editable .students-card-copy {
                opacity: 1 !important;
                min-height: 74px;
                padding: 10px 16px 12px;
            }

            .students-cards {
                overflow: visible !important;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .students-cards::-webkit-scrollbar {
                width: 0 !important;
                height: 0 !important;
                display: none !important;
            }

            .students-org-card[data-cms-edit-trigger] {
                position: relative;
                cursor: default;
                isolation: isolate;
            }

            .students-card[data-cms-edit-trigger="cards"]::after,
            .students-org-card[data-cms-edit-trigger]::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 10;
                box-sizing: border-box;
                pointer-events: none;
                border: 2px dashed rgba(242, 201, 76, 0.95);
                border-radius: inherit;
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.24),
                    0 0 0 4px rgba(242, 201, 76, 0.12);
            }

            .students-card[data-cms-edit-trigger="cards"]:hover::after,
            .students-card[data-cms-edit-trigger="cards"]:focus-within::after,
            .students-org-card[data-cms-edit-trigger]:hover::after,
            .students-org-card[data-cms-edit-trigger]:focus-within::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .cms-preview-card-actions {
                position: absolute;
                top: 14px;
                right: 14px;
                z-index: 12;
                display: flex;
                gap: 8px;
                opacity: 1;
                transform: none;
                transition: none;
            }

            .cms-preview-card-action {
                border: none;
                border-radius: 12px;
                padding: 0 12px;
                min-width: 64px;
                height: 36px;
                background: rgba(127, 17, 19, 0.92);
                color: #fffaf4;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
                cursor: pointer;
            }

            .cms-preview-card-action-delete {
                background: rgba(80, 10, 12, 0.96);
            }

            .students-card-add {
                cursor: pointer;
                border: 2px dashed rgba(127, 17, 19, 0.2);
                background: linear-gradient(160deg, rgba(255, 255, 255, 0.96) 0%, rgba(250, 243, 236, 0.9) 100%);
            }

            .students-card-front-add {
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
            }

            .students-card-add-inner {
                display: flex;
                min-height: 100%;
                flex: 1;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 14px;
                padding: 36px 24px;
                text-align: center;
                color: var(--maroon);
            }

            .students-card-add-plus {
                color: var(--maroon);
                font-size: clamp(3rem, 6vw, 4.5rem);
                font-weight: 500;
                line-height: 1;
            }

            .students-card-add-label {
                margin: 0;
                color: var(--maroon);
                font-family: "Poppins", sans-serif;
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: .04em;
            }

            @media (max-width: 768px) {
                .hero-shell,
                .students-contents-strip.cms-preview-editable,
                .students-orgs-group.cms-preview-editable {
                    --cms-preview-outline-offset: 8px;
                    --cms-preview-chip-top-offset: 50%;
                    --cms-preview-chip-right-offset: 8px;
                }

                .cms-preview-chip {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                }

                .cms-preview-card-actions {
                    top: 10px;
                    right: 10px;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let previewHeightFrame = null;

                const getElementBottom = (element) => {
                    if (!(element instanceof HTMLElement)) {
                        return 0;
                    }

                    const styles = window.getComputedStyle(element);
                    const marginBottom = Number.parseFloat(styles.marginBottom || '') || 0;

                    return element.offsetTop + element.offsetHeight + marginBottom;
                };

                const isMeasuredElement = (element) => {
                    if (!(element instanceof HTMLElement)) {
                        return false;
                    }

                    const styles = window.getComputedStyle(element);
                    return styles.display !== 'none'
                        && styles.visibility !== 'hidden'
                        && styles.position !== 'fixed';
                };

                const postPreviewHeight = () => {
                    const main = document.querySelector('.main-content');
                    const scope = main instanceof HTMLElement ? main : document.body;
                    const visibleElements = Array.from(scope.children)
                        .filter((node) => isMeasuredElement(node));
                    const childBottom = visibleElements.reduce((maxBottom, node) => {
                        return Math.max(maxBottom, getElementBottom(node));
                    }, 0);
                    const html = document.documentElement;
                    const body = document.body;
                    const height = Math.max(
                        scope.offsetHeight,
                        scope.scrollHeight,
                        scope.clientHeight,
                        body?.offsetHeight || 0,
                        body?.scrollHeight || 0,
                        body?.clientHeight || 0,
                        html?.offsetHeight || 0,
                        html?.scrollHeight || 0,
                        html?.clientHeight || 0,
                        childBottom
                    );

                    window.parent?.postMessage({
                        type: 'cms-students-preview-height',
                        height: Math.max(1, Math.ceil(height)),
                    }, '*');
                };

                const schedulePreviewHeight = () => {
                    if (previewHeightFrame !== null) {
                        window.cancelAnimationFrame(previewHeightFrame);
                    }

                    previewHeightFrame = window.requestAnimationFrame(() => {
                        postPreviewHeight();
                        previewHeightFrame = null;
                    });
                };

                const scheduleSettledPreviewHeight = () => {
                    schedulePreviewHeight();
                    [80, 220, 480, 900].forEach((delay) => {
                        window.setTimeout(schedulePreviewHeight, delay);
                    });
                };

                if (typeof ResizeObserver !== 'undefined') {
                    const observer = new ResizeObserver(() => {
                        schedulePreviewHeight();
                    });

                    if (document.body) {
                        observer.observe(document.body);
                    }

                    if (document.documentElement) {
                        observer.observe(document.documentElement);
                    }
                }

                document.querySelectorAll('img').forEach((image) => {
                    if (image.complete) {
                        return;
                    }

                    image.addEventListener('load', scheduleSettledPreviewHeight, { once: true });
                    image.addEventListener('error', scheduleSettledPreviewHeight, { once: true });
                });

                window.addEventListener('load', scheduleSettledPreviewHeight);
                window.addEventListener('pageshow', scheduleSettledPreviewHeight);
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        scheduleSettledPreviewHeight();
                    }
                });

                scheduleSettledPreviewHeight();
            });
        </script>
    @endif
</body>
</html>
