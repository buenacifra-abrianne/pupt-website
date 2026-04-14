<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v={{ filemtime(public_path('assets/css/about.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $aboutCms = \App\Support\AboutCmsContent::fromInput($aboutCms ?? [], null);
        $overview = $aboutCms['overview'] ?? [];
        $sections = $sections ?? ($aboutCms['sections'] ?? []);
        $selectedSection = $selectedSection ?? null;
        $heroTitle = $overview['hero_title_default'] ?? 'ABOUT THE CAMPUS';

        if (($selectedSection['slug'] ?? null) === 'history') {
            $heroTitle = $overview['hero_title_history'] ?? 'CAMPUS HISTORY';
        } elseif (($selectedSection['slug'] ?? null) === 'vision-and-mission') {
            $heroTitle = $overview['hero_title_vision'] ?? 'VISION AND MISSION';
        }

        $previewRouteKey = $selectedSection['slug'] ?? 'overview';
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

    <main class="main-content">
        <section
            class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="hero"
                data-cms-section-label="Hero"
            @endif
        >
            @if($cmsPreview)
                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="hero" aria-label="Edit About Hero">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                    </svg>
                </button>
            @endif

            <div data-cms-boundary class="cms-preview-boundary-edge">
            <section class="carousel-section">
                <div class="carousel full-carousel">
                    <div class="carousel-stage">
                        <div class="carousel-slide active">
                            <div class="carousel-split" aria-hidden="true">
                                <img src="{{ asset($overview['hero_image'] ?? 'assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                            </div>

                            <div class="carousel-caption">
                                <h2>{{ $heroTitle }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            </div>
        </section>

        <section class="about-shell page-shell">
            <nav class="about-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                @if($selectedSection)
                    <a
                        href="{{ route('public.about', ['cms_preview' => $cmsPreview ? 1 : null]) }}"
                        @if($cmsPreview)
                            data-about-preview-nav="overview"
                        @endif
                    >About</a>
                    <span>&gt;</span>
                    <strong>{{ $selectedSection['label'] }}</strong>
                @else
                    <strong>About</strong>
                @endif
            </nav>

            @unless($selectedSection)
                <section
                    class="about-intro reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                    @if($cmsPreview)
                        data-cms-section="intro"
                        data-cms-section-label="About Intro"
                    @endif
                >
                    @if($cmsPreview)
                        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="intro" aria-label="Edit About Intro">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                            </svg>
                        </button>
                    @endif

                    <div
                        class="campus-story-card{{ $cmsPreview ? ' cms-preview-boundary-edge' : '' }}"
                        @if($cmsPreview)
                            data-cms-boundary
                        @endif
                    >
                        <div class="campus-story-layout">
                            <div class="campus-story-copy">
                                <p class="campus-story-tag">{{ $overview['story_tag'] ?? 'Campus Story' }}</p>
                                <h2>{{ $overview['story_title'] ?? 'PUP Taguig Campus' }}</h2>
                            </div>

                            <div class="campus-story-visual">
                                <img src="{{ asset($overview['story_image'] ?? 'assets/static_img/about-pup.png') }}" alt="{{ $overview['story_title'] ?? 'PUP Taguig Campus' }}">
                            </div>

                            <div class="campus-story-description">
                                <p>{!! nl2br(e($overview['story_description'] ?? '')) !!}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="contents-strip reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                    @if($cmsPreview)
                        data-cms-section="contents"
                        data-cms-section-label="About Contents"
                    @endif
                >
                    @if($cmsPreview)
                        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="contents" aria-label="Edit About Contents">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                            </svg>
                        </button>
                    @endif

                    <div data-cms-boundary class="cms-preview-boundary-full">
                    <div class="contents-strip-head layout-inset">
                        <p class="section-tag layout-kicker">{{ $overview['contents_tag'] ?? 'Contents' }}</p>
                        <h2>{{ $overview['contents_title'] ?? 'All about the campus' }}</h2>
                    </div>

                    <nav class="contents-cards" aria-label="About page contents">
                        @foreach($sections as $section)
                            <a
                                href="{{ route('public.about.section', ['section' => $section['slug'], 'cms_preview' => $cmsPreview ? 1 : null]) }}"
                                class="contents-card card_without_section"
                                @if($cmsPreview)
                                    data-about-preview-nav="{{ $section['slug'] }}"
                                @endif
                            >
                                <div class="contents-card-inner">
                                    <div class="contents-card-front">
                                        <img src="{{ asset($section['image'] ?? 'assets/static_img/pupillar.jpeg') }}" alt="{{ $section['label'] }}">
                                        <div class="contents-card-copy">
                                            <h3>{{ $section['label'] }}</h3>
                                        </div>
                                    </div>

                                    <div class="contents-card-back">
                                        <div class="contents-card-overlay-copy">
                                            <h3>{{ $section['label'] }}</h3>
                                            <p>{{ $section['summary'] }}</p>
                                        </div>
                                        <span class="contents-card-action">See more</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </nav>
                    </div>
                </section>
            @endunless

            @if($selectedSection)
                @php
                    $selectedSlug = $selectedSection['slug'] ?? '';
                    $detailImage = $overview['section_header_image'] ?? 'assets/static_img/about_header_image.png';
                @endphp
                <section class="about-sections">
                    @if($selectedSlug === 'history')
                        <section
                            class="history-story reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                            @if($cmsPreview)
                                data-cms-section="history"
                                data-cms-section-label="History"
                            @endif
                        >
                            @if($cmsPreview)
                                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="history" aria-label="Edit History">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                    </svg>
                                </button>
                            @endif

                            <div data-cms-boundary class="cms-preview-boundary-full">
                                <div class="history-story-inner">
                                    <div class="history-timeline-container reveal">
                                        <div class="history-timeline-shell">
                                            <div class="history-timeline-head reveal delay-100">
                                                <p class="history-kicker layout-kicker">{{ $selectedSection['page_kicker'] ?? 'Campus Timeline' }}</p>
                                                <h4>{{ $selectedSection['page_title'] ?? '' }}</h4>
                                            </div>

                                            <div class="history-timeline-grid">
                                                @foreach($selectedSection['timeline'] ?? [] as $milestone)
                                                    @php
                                                        $firstParagraph = (string) ($milestone['body'][0] ?? '');
                                                        $remainingParagraphs = array_slice($milestone['body'] ?? [], 1);
                                                        $hasExpandableTimelineCard = count($remainingParagraphs) > 0 || \Illuminate\Support\Str::length($firstParagraph) > 180;
                                                    @endphp
                                                    <article class="history-timeline-row reveal {{ $loop->odd ? 'is-left' : 'is-right' }} {{ $loop->iteration % 2 === 0 ? 'delay-200' : 'delay-100' }}">
                                                        <div class="history-timeline-marker" aria-hidden="true">
                                                            <span class="history-timeline-dot"></span>
                                                        </div>

                                                        <div class="history-timeline-card">
                                                            <span class="history-timeline-period">{{ $milestone['period'] ?? '' }}</span>
                                                            <h5>{{ $milestone['title'] ?? '' }}</h5>
                                                            @if($hasExpandableTimelineCard)
                                                                <p class="history-timeline-preview">{{ $firstParagraph }}</p>
                                                                <div class="history-timeline-more" aria-hidden="true">
                                                                    @foreach($remainingParagraphs as $paragraph)
                                                                        <p>{{ $paragraph }}</p>
                                                                    @endforeach
                                                                </div>
                                                                <button type="button" class="history-timeline-toggle" aria-expanded="false" aria-label="Read more about {{ $milestone['title'] ?? 'this milestone' }}">
                                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                                        <path d="m12 18.8 7.4-7.4-1.4-1.4-5 5v-9h-2v9l-5-5-1.4 1.4L12 18.8Z"></path>
                                                                    </svg>
                                                                </button>
                                                            @else
                                                                <p>{{ $firstParagraph }}</p>
                                                            @endif
                                                        </div>
                                                    </article>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @elseif($selectedSlug === 'vision-and-mission')
                        <section class="history-story history-story--vision reveal">
                            <div class="history-story-inner">
                                <div class="history-timeline-container history-timeline-container--vision reveal">
                                    <div
                                        class="history-page-header history-page-header--vision{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                                        @if($cmsPreview)
                                            data-cms-section="vision-mission-header"
                                            data-cms-section-label="Vision and Mission Header"
                                        @endif
                                    >
                                        @if($cmsPreview)
                                            <button type="button" class="cms-preview-chip" data-cms-edit-trigger="vision-mission-header" aria-label="Edit Vision and Mission Header">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        <div data-cms-boundary class="cms-preview-boundary-full">
                                            <p class="history-page-kicker">{{ $selectedSection['page_kicker'] ?? 'Vision and Mission' }}</p>
                                            <h2>{{ $selectedSection['page_title'] ?? '' }}</h2>
                                        </div>
                                    </div>

                                    <div class="about-detail-body about-detail-body--vision">
                                        <div
                                            class="about-vision-content{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                                            @if($cmsPreview)
                                                data-cms-section="vision-mission-statements"
                                                data-cms-section-label="Vision and Mission Statements"
                                            @endif
                                        >
                                            @if($cmsPreview)
                                                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="vision-mission-statements" aria-label="Edit Vision and Mission Statements">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                                    </svg>
                                                </button>
                                            @endif

                                            <div data-cms-boundary class="cms-preview-boundary-edge">
                                                <div class="about-vision-row about-vision-row--vision">
                                                    <button type="button" class="about-vision-trigger about-vision-trigger--vision" aria-expanded="false" aria-controls="aboutVisionDescription">
                                                        <span class="about-vision-word">Vision</span>
                                                        <span class="about-vision-arrow about-vision-arrow--right" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" focusable="false">
                                                                <path d="M4 12h14"></path>
                                                                <path d="m13 5 7 7-7 7"></path>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                    <div id="aboutVisionDescription" class="about-vision-panel" aria-hidden="true">
                                                        <div class="about-vision-card">
                                                            <p class="about-vision-statement">{{ $selectedSection['vision'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="about-vision-row about-vision-row--mission">
                                                    <div id="aboutMissionDescription" class="about-vision-panel about-vision-panel--mission" aria-hidden="true">
                                                        <div class="about-vision-card about-vision-card--mission">
                                                            <p class="about-vision-statement about-vision-statement--mission">{{ $selectedSection['mission'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="about-vision-trigger about-vision-trigger--mission" aria-expanded="false" aria-controls="aboutMissionDescription">
                                                        <span class="about-vision-arrow about-vision-arrow--left" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" focusable="false">
                                                                <path d="M20 12H6"></path>
                                                                <path d="m11 5-7 7 7 7"></path>
                                                            </svg>
                                                        </span>
                                                        <span class="about-vision-word">Mission</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="about-vision-extension-grid">
                                            <article
                                                class="about-vision-feature about-vision-feature--goals reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                                                @if($cmsPreview)
                                                    data-cms-section="strategic-goals"
                                                    data-cms-section-label="Strategic Goals"
                                                @endif
                                            >
                                                @if($cmsPreview)
                                                    <button type="button" class="cms-preview-chip" data-cms-edit-trigger="strategic-goals" aria-label="Edit Strategic Goals">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                                        </svg>
                                                    </button>
                                                @endif

                                                <div data-cms-boundary class="cms-preview-boundary-edge">
                                                    <div class="about-vision-feature-head">
                                                        <span class="about-vision-feature-kicker">Strategic Goals</span>
                                                        <h3>Three priority pillars that guide how the University teaches, serves, and grows.</h3>
                                                    </div>

                                                    <div class="about-goals-grid">
                                                        @foreach($selectedSection['strategic_goals'] ?? [] as $goalGroup)
                                                            @php($pillarRoman = ['I', 'II', 'III', 'IV', 'V'][$loop->index] ?? (string) $loop->iteration)
                                                            <article class="about-goal-pillar">
                                                                <div class="about-goal-pillar-head">
                                                                    <div class="about-goal-pillar-tag" aria-label="{{ $goalGroup['pillar'] ?? '' }}">
                                                                        <span class="about-goal-pillar-label-group">
                                                                            <span class="about-goal-pillar-label">Pillar {{ $pillarRoman }}</span>
                                                                        </span>
                                                                    </div>
                                                                    <h4>{{ $goalGroup['title'] ?? '' }}</h4>
                                                                </div>
                                                                <ul class="about-goal-list">
                                                                    @foreach($goalGroup['goals'] ?? [] as $goal)
                                                                        <li>
                                                                            <span class="about-goal-code">SG-{{ $goal['number'] ?? $loop->iteration }}</span>
                                                                            <span class="about-goal-text">{{ $goal['text'] ?? '' }}</span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </article>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div>
                                </div>

                                <article
                                    class="about-values-band reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                                    @if($cmsPreview)
                                        data-cms-section="core-values"
                                        data-cms-section-label="Core Values"
                                    @endif
                                >
                                    @if($cmsPreview)
                                        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="core-values" aria-label="Edit Core Values">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <div data-cms-boundary class="cms-preview-boundary-edge">
                                        <div class="about-values-band-head">
                                            <span class="about-values-band-kicker">Core Values</span>
                                            <h3>INSPIRED values that shape the character of the PUP community.</h3>
                                        </div>

                                        <div class="about-values-grid">
                                            @foreach($selectedSection['core_values'] ?? [] as $coreValue)
                                                <article class="about-value-card">
                                                    <span class="about-value-letter">{{ $coreValue['letter'] ?? '' }}</span>
                                                    <div class="about-value-copy">
                                                        <h4>{{ $coreValue['title'] ?? '' }}</h4>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>
                    @else

                        <article
                            class="about-section-card reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                            @if($cmsPreview)
                                data-cms-section="{{ $selectedSlug }}"
                                data-cms-section-label="{{ $selectedSection['label'] }}"
                            @endif
                        >
                            @if($cmsPreview)
                                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="{{ $selectedSlug }}" aria-label="Edit {{ $selectedSection['label'] }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                    </svg>
                                </button>
                            @endif

                            @if($selectedSlug !== 'logo-and-symbols' && $selectedSlug !== 'hymn' && $selectedSlug !== 'maps' && $selectedSlug !== 'campus-officials')
                                <div class="about-detail-heading">
                                    <div class="about-detail-heading-copy">
                                        <h2>{{ $selectedSection['label'] ?? '' }}</h2>
                                    </div>
                                </div>
                            @endif

                                <div class="about-detail-body">
                                    @if($selectedSlug === 'logo-and-symbols')
                                        {{-- ── IDENTITY PAGE HEADER ── --}}
                                        <div class="ls-page-header reveal">
                                            <p class="section-tag">Campus Identity</p>
                                            <h2 class="ls-page-title">The Official Seal &amp; Symbols</h2>
                                            <p class="ls-page-subtitle">Every element of the PUP Taguig seal carries deliberate meaning — each mark a reflection of the university's heritage, values, and ambitions.</p>
                                        </div>
                                        {{-- ── SEAL HERO ── --}}
                                        <div class="ls-seal-hero reveal delay-100">
                                            <div class="ls-seal-hero-glow" aria-hidden="true"></div>
                                            <div class="ls-seal-hero-inner">
                                                <div class="ls-seal-ring" aria-label="PUP Official Seal">
                                                    <div class="ls-seal-ring-shimmer" aria-hidden="true"></div>
                                                    <img
                                                        src="{{ asset('assets/static_img/logo.png') }}"
                                                        alt="PUP Taguig Official Seal"
                                                        class="ls-seal-img"
                                                        onerror="this.style.opacity='0'"
                                                    >
                                                </div>
                                                <div class="ls-seal-hero-copy">
                                                    <p class="ls-seal-eyebrow">Official Seal — PUP Taguig Campus</p>
                                                    <h3 class="ls-seal-headline">Forged in Maroon &amp; Gold</h3>
                                                    <p class="ls-seal-body">The seal of the Polytechnic University of the Philippines is not merely a logo — it is a compact declaration of the institution's covenant with the Filipino people: quality education, rooted in history, oriented toward the future.</p>
                                                    <div class="ls-color-chips">
                                                        <span class="ls-chip ls-chip--maroon">
                                                            <span class="ls-chip-dot"></span>
                                                            Maroon — Courage &amp; Excellence
                                                        </span>
                                                        <span class="ls-chip ls-chip--gold">
                                                            <span class="ls-chip-dot"></span>
                                                            Gold — Wisdom &amp; Achievement
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- ── SYMBOLISM GRID ── --}}
                                        <div class="ls-block-header reveal">
                                            <span class="ls-block-kicker">Symbolism</span>
                                            <h3 class="ls-block-title">What each element represents</h3>
                                        </div>
                                        <div class="ls-meanings-grid">
                                            @foreach($selectedSection['identity_marks'] ?? [] as $identityMark)
                                                <article class="ls-meaning-card reveal {{ $loop->index % 2 === 1 ? 'delay-100' : '' }}">
                                                    <div class="ls-meaning-card-header">
                                                        <span class="ls-meaning-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                        <h4 class="ls-meaning-title">{{ $identityMark['title'] ?? '' }}</h4>
                                                    </div>
                                                    <p class="ls-meaning-body">{{ $identityMark['body'] ?? '' }}</p>
                                                    <div class="ls-meaning-card-accent" aria-hidden="true"></div>
                                                </article>
                                            @endforeach
                                        </div>
                                        {{-- ── QUICK REFERENCE ── --}}
                                        @if(!empty($selectedSection['symbol_points']))
                                        <div class="ls-block-header reveal">
                                            <span class="ls-block-kicker">Quick Reference</span>
                                            <h3 class="ls-block-title">Key facts at a glance</h3>
                                        </div>
                                        <div class="ls-reference-card reveal delay-100">
                                            @foreach($selectedSection['symbol_points'] ?? [] as $index => $point)
                                                <div class="ls-ref-row">
                                                    <span class="ls-ref-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                                    <span class="ls-ref-text">{{ $point }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        {{-- ── OFFICIAL COLORS ── --}}
                                        <div class="ls-block-header reveal">
                                            <span class="ls-block-kicker">Official Colors</span>
                                            <h3 class="ls-block-title">The palette of PUP</h3>
                                        </div>
                                        <div class="ls-colors-duo reveal delay-100">
                                            <div class="ls-color-card ls-color-card--maroon">
                                                <div class="ls-color-card-swatch" aria-hidden="true">
                                                    <div class="ls-color-swatch-pattern"></div>
                                                </div>
                                                <div class="ls-color-card-body">
                                                    <p class="ls-color-name">Maroon</p>
                                                    <p class="ls-color-hex">#7f1113</p>
                                                    <p class="ls-color-pantone">Pantone 202 C</p>
                                                    <p class="ls-color-meaning">Represents the courage, discipline, and relentless pursuit of excellence that define PUP's identity.</p>
                                                </div>
                                            </div>
                                            <div class="ls-color-card ls-color-card--gold">
                                                <div class="ls-color-card-swatch" aria-hidden="true">
                                                    <div class="ls-color-swatch-pattern"></div>
                                                </div>
                                                <div class="ls-color-card-body">
                                                    <p class="ls-color-name">Gold</p>
                                                    <p class="ls-color-hex">#d7aa43</p>
                                                    <p class="ls-color-pantone">Pantone 124 C</p>
                                                    <p class="ls-color-meaning">Symbolizes wisdom, achievement, and the lasting value of education as an investment for the nation.</p>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($selectedSlug === 'hymn')
                                        {{-- ── HYMN PAGE HEADER ── --}}
                                        <div class="hymn-page-header reveal">
                                            <p class="section-tag">Official Hymn</p>
                                            <h2 class="hymn-page-title">Imno ng PUP</h2>
                                            <p class="hymn-page-attribution">Music &amp; Lyrics by S. Calabig, S. Roldan &amp; R. Amaranto</p>
                                        </div>
                                        {{-- ── LYRICS HERO ── --}}
                                        <div class="hymn-hero reveal delay-100">
                                            <div class="hymn-hero-ornament" aria-hidden="true">
                                                <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <circle cx="60" cy="60" r="58" stroke="currentColor" stroke-width="0.75" stroke-dasharray="4 6" opacity="0.4"/>
                                                    <circle cx="60" cy="60" r="44" stroke="currentColor" stroke-width="0.5" opacity="0.25"/>
                                                    <path d="M48 42 C48 36 56 32 60 32 C64 32 72 36 72 42 L72 54 C72 60 66 64 60 64 C54 64 48 60 48 54 Z" stroke="currentColor" stroke-width="1" fill="none" opacity="0.55"/>
                                                    <path d="M52 64 L52 76 M68 64 L68 76" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.4"/>
                                                    <path d="M44 76 L76 76" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.4"/>
                                                    <circle cx="60" cy="48" r="3" fill="currentColor" opacity="0.5"/>
                                                </svg>
                                            </div>
                                            <div class="hymn-hero-copy">
                                                <p class="hymn-title-tag">Sintang Paaralan</p>
                                                <h3 class="hymn-tagline">A Song of Covenant with the Nation</h3>
                                                <p class="hymn-hero-desc">Written as a pledge between the university and the Filipino people — a reminder that education, at its highest, is an act of love for country.</p>
                                                <a href="https://youtu.be/Yib_s5UeGvc?si=CR3qUIEbH25lZxfw"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="hymn-listen-btn"
                                                aria-label="Listen to the PUP Hymn on YouTube">
                                                    <span class="hymn-listen-icon" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M8 5.14v14l11-7-11-7z"/>
                                                        </svg>
                                                    </span>
                                                    Listen to the Hymn
                                                </a>
                                                </div>
                                                </div>
                                        <div class="hymn-lyrics-block reveal">
                                            <div class="hymn-lyrics-header">
                                                <span class="ls-block-kicker">Full Lyrics</span>
                                                <h3 class="ls-block-title">Imno ng PUP</h3>
                                            </div>
                                            <div class="hymn-lyrics-body">
                                                <div class="hymn-stanza">
                                                    <p class="hymn-stanza-label">Verse I</p>
                                                    <p class="hymn-line">Sintang Paaralan</p>
                                                    <p class="hymn-line">Tanglaw ka ng bayan</p>
                                                    <p class="hymn-line">Pandayan ng isip ng kabataan</p>
                                                    <p class="hymn-line">Kami ay dumating nang salat sa yaman</p>
                                                    <p class="hymn-line">Hanap na dunong ay iyong alay</p>
                                                </div>
                                                <div class="hymn-stanza">
                                                    <p class="hymn-stanza-label">Verse II</p>
                                                    <p class="hymn-line">Ang layunin mong makatao</p>
                                                    <p class="hymn-line">Dinarangal ang Pilipino</p>
                                                    <p class="hymn-line">Ang iyong aral, diwa, adhikang taglay</p>
                                                    <p class="hymn-line hymn-line--refrain">PUP, aming gabay</p>
                                                </div>
                                                <div class="hymn-stanza hymn-stanza--chorus">
                                                    <p class="hymn-stanza-label">Chorus</p>
                                                    <p class="hymn-line">Paaralang dakila</p>
                                                    <p class="hymn-line">PUP, pinagpala</p>
                                                    <p class="hymn-line">Gagamitin ang karunungan</p>
                                                    <p class="hymn-line">Mula sa iyo, para sa bayan</p>
                                                    <p class="hymn-line">Ang iyong aral, diwa, adhikang taglay</p>
                                                    <p class="hymn-line hymn-line--refrain">PUP, aming gabay</p>
                                                    <p class="hymn-line">Paaralang dakila</p>
                                                    <p class="hymn-line hymn-line--end">PUP, pinagpala</p>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($selectedSlug === 'maps')

                                    <div class="map-hero-header">
                                    <div>
                                        <p class="map-hero-eyebrow">Location</p>
                                        <h2 class="map-hero-title">PUP Taguig Campus</h2>
                                        <p class="map-hero-address">Gen. Santos Ave., Lower Bicutan, Taguig City, Metro Manila</p>
                                        <div class="map-hero-chips">
                                        <span class="map-chip map-chip--gold">Lower Bicutan, Taguig</span>
                                        <span class="map-chip map-chip--light">Mon–Fri · 7AM–6PM</span>
                                        <span class="map-chip map-chip--light">★ 4.5 · Public University</span>
                                        </div>
                                    </div>
                                    <a href="https://maps.app.goo.gl/azsPnMfHwSTNv4xN7" target="_blank" rel="noopener noreferrer" class="map-hero-cta">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                        Get Directions
                                    </a>
                                    </div>

                                    <div class="map-body">

                                    <div style="display:grid;gap:14px">
                                        <div class="map-iframe-wrap">
                                        <div class="map-iframe-topbar">
                                            <div class="map-iframe-label">
                                            <span class="map-dot"></span>
                                            PUP Taguig Campus
                                            </div>
                                            <div class="map-iframe-tabs">
                                            <button class="map-tab active" onclick="switchMapTab(this,'map')">Map</button>
                                            <button class="map-tab" onclick="switchMapTab(this,'street')">Street View</button>
                                            </div>
                                        </div>
                                        <div class="map-iframe-panel">
                                            <iframe id="pane-map"
                                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3862.5!2d121.0484!3d14.5184!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397cf49a0000001%3A0x0!2sPUP+Taguig+Campus!5e0!3m2!1sen!2sph!4v1700000000000"
                                            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="PUP Taguig Map">
                                            </iframe>
                                            <iframe id="pane-street" class="map-pane-hidden"
                                            src="https://www.google.com/maps/embed?pb=!4v1700000000000!6m8!1m7!1sCAoSLEFGMVFpcE5aM2hUX2tKTjBUNEZfSkZn!2m2!1d14.5184!2d121.0509!3f200!4f0!5f0.7820865974627469"
                                            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="PUP Taguig Street View">
                                            </iframe>
                                        </div>
                                        </div>

                                        <div class="map-dir-footer">
                                        <div class="map-dir-text">
                                            <strong>Open in Google Maps</strong> for real-time directions, live traffic, and transit options from your location.
                                        </div>
                                        <div class="map-dir-btns">
                                            <a href="https://maps.app.goo.gl/azsPnMfHwSTNv4xN7" target="_blank" rel="noopener noreferrer" class="map-dir-btn map-dir-btn--primary">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                                            Open Maps
                                            </a>
                                            <a href="https://maps.app.goo.gl/azsPnMfHwSTNv4xN7" target="_blank" rel="noopener noreferrer" class="map-dir-btn map-dir-btn--gold">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                            Directions
                                            </a>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="map-sidebar">

                                        <div class="map-transport-card">
                                        <div class="map-info-kicker">How to Get Here</div>
                                        <div class="map-transport-list">
                                            <div class="map-transport-row">
                                            <div class="map-t-icon"><svg viewBox="0 0 24 24" fill="#7f1113" width="14" height="14"><path d="M12 1c-4.418 0-8 .895-8 2v16l2-2h12l2 2V3c0-1.105-3.582-2-8-2zm0 2c3.314 0 6 .672 6 1.5S15.314 6 12 6 6 5.328 6 4.5 8.686 3 12 3zm-4 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm8 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg></div>
                                            <div><div class="map-t-name">Jeepney</div><div class="map-t-sub">Bicutan–Taguig route</div></div>
                                            <span class="map-t-badge">Nearest stop</span>
                                            </div>
                                            <div class="map-transport-row">
                                            <div class="map-t-icon"><svg viewBox="0 0 24 24" fill="#7f1113" width="14" height="14"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
                                            <div><div class="map-t-name">UV Express</div><div class="map-t-sub">From BGC / Alabang</div></div>
                                            <span class="map-t-badge">Via SLEX</span>
                                            </div>
                                            <div class="map-transport-row">
                                            <div class="map-t-icon"><svg viewBox="0 0 24 24" fill="#7f1113" width="14" height="14"><path d="M12 22s8-5.33 8-12A8 8 0 0 0 4 10c0 6.67 8 12 8 12z"/><circle cx="12" cy="10" r="3"/></svg></div>
                                            <div><div class="map-t-name">Grab / Taxi</div><div class="map-t-sub">Door-to-door service</div></div>
                                            <span class="map-t-badge">Recommended</span>
                                            </div>
                                            <div class="map-transport-row">
                                            <div class="map-t-icon"><svg viewBox="0 0 24 24" fill="#7f1113" width="14" height="14"><path d="M16 4H8L3 9v6l5 5h8l5-5V9l-5-5zm-4 13a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg></div>
                                            <div><div class="map-t-name">BGC Bus</div><div class="map-t-sub">Taguig route stops nearby</div></div>
                                            <span class="map-t-badge">BGC Hub</span>
                                            </div>
                                        </div>
                                        </div>

                                        <div class="map-tips-card2">
                                        <div class="map-tips-kicker">Visitor Tips</div>
                                        <div class="map-tip-item"><span class="map-tip-bull"></span>Bring a valid ID — security requires gate registration for non-students.</div>
                                        <div class="map-tip-item"><span class="map-tip-bull"></span>Parking is limited; public transport is highly recommended.</div>
                                        <div class="map-tip-item"><span class="map-tip-bull"></span>Registrar and Admin offices open at 8:00 AM sharp on weekdays.</div>
                                        <div class="map-tip-item"><span class="map-tip-bull"></span>Nearest landmark: Lower Bicutan Terminal / SM Bicutan.</div>
                                        </div>

                                    </div>
                                    </div>

                                    <script>
                                    function switchMapTab(btn, pane) {
                                    document.querySelectorAll('.map-tab').forEach(b => b.classList.remove('active'));
                                    btn.classList.add('active');
                                    document.getElementById('pane-map').classList.add('map-pane-hidden');
                                    document.getElementById('pane-street').classList.add('map-pane-hidden');
                                    document.getElementById('pane-' + pane).classList.remove('map-pane-hidden');
                                    }
                                    </script>

                                @elseif($selectedSlug === 'campus-officials')
                                <div class="officials-grid">

                                <div class="official-card official-card--featured">
                                    <div class="official-avatar">
                                    <span class="official-initials-text">MBF</span>
                                    </div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Campus Director</span>
                                    <p class="official-name">Dr. Marissa B. Ferrer</p>
                                    <p class="official-role">Campus Director</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">RVM</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Academic Programs</span>
                                    <p class="official-name">Dr. Rhyan V. Molinar</p>
                                    <p class="official-role">Head of Academic Programs</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">MLZ</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Administration</span>
                                    <p class="official-name">Engr. Michael L. Zarco</p>
                                    <p class="official-role">Administration Officer</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">BIC</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Student Services</span>
                                    <p class="official-name">Asst. Prof. Bernadette I. Canlas</p>
                                    <p class="official-role">Head of Student Services</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">MPG</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Registrar</span>
                                    <p class="official-name">Mr. Mhel P. Garcia</p>
                                    <p class="official-role">Campus Registrar / Head of Registration Office</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">LLM</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Guidance</span>
                                    <p class="official-name">Assoc. Prof. Liwanag L. Maliksi</p>
                                    <p class="official-role">Guidance and Counseling Office</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">GAD</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Accreditation</span>
                                    <p class="official-name">Assoc. Prof. Gina A. Dela Cruz</p>
                                    <p class="official-role">Accreditation Coordinator / Property Custodian</p>
                                    </div>
                                </div>

                                <div class="official-card">
                                    <div class="official-avatar"><span class="official-initials-text">DSS</span></div>
                                    <div class="official-info">
                                    <span class="official-office-badge">Admission</span>
                                    <p class="official-name">Dianne S. Segurora</p>
                                    <p class="official-role">Head of Admission</p>
                                    </div>
                                </div>

                                </div>
                                <p class="about-detail-caption">{{ $selectedSection['officials_note'] ?? '' }}</p>

                                    @elseif($selectedSlug === 'strategic-development-plan')
                                        <div class="about-roadmap-grid">
                                            @foreach($selectedSection['development_priorities'] ?? [] as $priority)
                                                <article class="contents-card contents-card--info">
                                                    <div class="contents-card-inner">
                                                        <div class="contents-card-front contents-card-front--info">
                                                            <div class="contents-card-copy contents-card-copy--info">
                                                                <span class="contents-card-number">Priority {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                                <h3>{{ $priority['title'] ?? '' }}</h3>
                                                            </div>
                                                            <div class="contents-card-body">
                                                                <p>{{ $priority['body'] ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>

                                        <article class="contents-card contents-card--info contents-card--note">
                                            <div class="contents-card-inner">
                                                <div class="contents-card-front contents-card-front--info">
                                                    <div class="contents-card-copy contents-card-copy--info">
                                                        <span class="contents-card-number">Planning Guide</span>
                                                        <h3>Planning principles</h3>
                                                    </div>
                                                    <div class="contents-card-body">
                                                        <ul class="about-detail-list">
                                                            @foreach($selectedSection['plan_principles'] ?? [] as $principle)
                                                                <li>{{ $principle }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>

                                    @endif
                                </div>{{-- end .about-detail-body --}}
                            </div>{{-- end [data-cms-boundary] --}}
                        </article>
                    @endif
                </section>
            @endif
        </section>
    </main>

    @unless($cmsPreview)
        <pup-footer></pup-footer>
    @endunless

    @if($cmsPreview)
        <style>
            html,
            body,
            .main-content {
                overflow-x: hidden;
            }

            html,
            body {
                width: 100%;
                max-width: 100%;
                overflow-x: hidden !important;
            }

            body {
                background: #fff;
                min-height: auto !important;
                display: flex !important;
            }

            .main-content {
                flex: none !important;
                width: 100% !important;
                min-height: 0 !important;
                overflow-x: hidden !important;
            }

            body > .main-content {
                flex: none !important;
                width: 100% !important;
                min-height: 0 !important;
            }

            .hero-shell,
            .about-intro,
            .contents-strip,
            .history-story,
            .about-section-card,
            .history-page-header--vision.cms-preview-editable,
            .about-vision-content.cms-preview-editable,
            .about-vision-feature--goals.cms-preview-editable,
            .about-values-band.cms-preview-editable {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                box-sizing: border-box !important;
            }

            .about-shell.page-shell {
                width: 100% !important;
                max-width: none !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .about-breadcrumb {
                padding-left: var(--about-page-gutter, 24px) !important;
                padding-right: var(--about-page-gutter, 24px) !important;
            }

            .hero-shell {
                width: 100% !important;
                max-width: none !important;
                left: auto !important;
                right: auto !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .about-intro,
            .contents-strip,
            .history-story,
            .about-section-card {
                width: 100% !important;
                max-width: none !important;
                left: auto !important;
                right: auto !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .campus-story-card {
                width: 100% !important;
                max-width: none !important;
            }

            .history-page-header--vision.cms-preview-editable,
            .about-vision-content.cms-preview-editable,
            .about-vision-feature--goals.cms-preview-editable,
            .about-values-band.cms-preview-editable {
                --cms-preview-chip-top-offset: 24px;
                width: 100% !important;
                max-width: 100% !important;
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
                z-index: 1;
                box-sizing: border-box;
                margin: var(--cms-preview-outline-offset);
                overflow: visible !important;
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
                width: calc(100% - (var(--cms-preview-outline-offset) * 2));
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge {
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

            .cms-preview-editable > * {
                position: relative;
                z-index: 1;
            }

            .contents-strip.cms-preview-editable {
                overflow: visible !important;
            }

            .contents-strip.cms-preview-editable .contents-cards {
                grid-auto-flow: row;
                grid-auto-columns: unset;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                overflow: visible;
                padding-bottom: 0;
                scroll-snap-type: none;
                touch-action: auto;
            }

            .contents-strip.cms-preview-editable .contents-card {
                min-width: 0;
            }

            .cms-preview-chip {
                position: absolute;
                top: var(--cms-preview-chip-top-offset);
                right: calc(var(--cms-preview-chip-right-offset) + var(--cms-preview-outline-offset));
                left: auto;
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
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
            }

            .cms-preview-chip:hover {
                background: rgba(152, 25, 28, 0.98);
            }

            .cms-preview-chip svg {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }

            @media (max-width: 768px) {
                .hero-shell,
                .about-intro,
                .contents-strip,
                .history-story,
                .about-section-card,
                .history-page-header--vision.cms-preview-editable,
                .about-vision-content.cms-preview-editable,
                .about-vision-feature--goals.cms-preview-editable,
                .about-values-band.cms-preview-editable {
                    --cms-preview-outline-offset: 8px;
                    --cms-preview-chip-top-offset: 50%;
                    --cms-preview-chip-right-offset: 8px;
                }

                .history-page-header--vision.cms-preview-editable,
                .about-vision-content.cms-preview-editable,
                .about-vision-feature--goals.cms-preview-editable,
                .about-values-band.cms-preview-editable {
                    --cms-preview-chip-top-offset: 20px;
                }

                .cms-preview-chip {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                }

                .cms-preview-chip svg {
                    width: 18px;
                    height: 18px;
                }

                .cms-preview-editable > [data-cms-boundary]::after {
                    border-radius: 16px;
                }

                .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge::after {
                    inset: var(--cms-preview-outline-offset);
                }

                .cms-preview-editable > [data-cms-boundary] {
                    margin: var(--cms-preview-outline-offset);
                }
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const targets = Array.from(document.querySelectorAll('[data-cms-section]'));
                let previewHeightFrame = null;

                const postSection = (section, label) => {
                    window.parent?.postMessage({
                        type: 'cms-about-edit',
                        section: section,
                        label: label || section,
                    }, '*');
                };

                const getElementBottom = (element) => {
                    return element.offsetTop + element.offsetHeight;
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
                    const height = visibleElements.reduce((maxBottom, node) => {
                        return Math.max(maxBottom, getElementBottom(node));
                    }, scope.offsetHeight);

                    window.parent?.postMessage({
                        type: 'cms-about-preview-height',
                        height: Math.max(1, Math.ceil(height)),
                        route: '{{ $previewRouteKey }}',
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

                const bindPreviewImages = () => {
                    document.querySelectorAll('img').forEach((image) => {
                        if (image.dataset.cmsPreviewHeightBound === '1') {
                            return;
                        }

                        image.dataset.cmsPreviewHeightBound = '1';

                        if (image.complete) {
                            return;
                        }

                        const handleImageSettled = () => {
                            scheduleSettledPreviewHeight();
                        };

                        image.addEventListener('load', handleImageSettled, { once: true });
                        image.addEventListener('error', handleImageSettled, { once: true });
                    });
                };

                targets.forEach((target) => {
                    const section = target.getAttribute('data-cms-section') || '';
                    const label = target.getAttribute('data-cms-section-label') || section;
                    const chip = target.querySelector('[data-cms-edit-trigger]');

                    const openEditor = (event) => {
                        if (event.target.closest('[data-about-preview-nav]')) {
                            return;
                        }

                        if (event.target.closest('a[href]') && !event.target.closest('[data-cms-edit-trigger]')) {
                            return;
                        }

                        if (event.target.closest('.history-timeline-toggle')) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();
                        postSection(section, label);
                    };

                    target.addEventListener('mouseenter', () => target.classList.add('is-active'));
                    target.addEventListener('mouseleave', () => target.classList.remove('is-active'));
                    target.addEventListener('click', openEditor);

                    if (chip) {
                        chip.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            postSection(section, label);
                        });
                    }
                });

                document.addEventListener('click', (event) => {
                    const navTrigger = event.target.closest('[data-about-preview-nav]');
                    if (navTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-preview-route',
                            route: navTrigger.getAttribute('data-about-preview-nav') || 'overview',
                        }, '*');
                    }
                });

                if (typeof ResizeObserver !== 'undefined') {
                    const observer = new ResizeObserver(() => {
                        scheduleSettledPreviewHeight();
                    });

                    observer.observe(document.body);

                    const main = document.querySelector('.main-content');
                    if (main) {
                        observer.observe(main);
                    }
                }

                if (typeof MutationObserver !== 'undefined') {
                    const previewMutationObserver = new MutationObserver(() => {
                        bindPreviewImages();
                        scheduleSettledPreviewHeight();
                    });

                    previewMutationObserver.observe(document.body, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['class', 'style', 'src'],
                    });
                }

                bindPreviewImages();

                window.addEventListener('load', scheduleSettledPreviewHeight);
                window.addEventListener('resize', scheduleSettledPreviewHeight);
                window.addEventListener('pageshow', scheduleSettledPreviewHeight);
                window.visualViewport?.addEventListener('resize', scheduleSettledPreviewHeight);
                document.fonts?.ready?.then(scheduleSettledPreviewHeight).catch(() => {});
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        scheduleSettledPreviewHeight();
                    }
                });

                window.setTimeout(scheduleSettledPreviewHeight, 120);
                window.setTimeout(scheduleSettledPreviewHeight, 360);
                scheduleSettledPreviewHeight();
            });
        </script>
    @endif

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
