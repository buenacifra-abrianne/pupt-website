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

                            <div data-cms-boundary class="cms-preview-boundary-full">
                                <div class="about-detail-heading">
                                    <div class="about-detail-heading-copy">
                                        <h2>{{ $selectedSection['label'] ?? '' }}</h2>
                                    </div>

                                    @if($selectedSlug === 'maps')
                                        <a href="{{ $selectedSection['map_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="section-link">Open Map</a>
                                    @endif
                                </div>

                                <div class="about-detail-hero">
                                    <div class="about-detail-copy">
                                        <span class="about-detail-eyebrow">Campus Section</span>
                                        <p class="about-detail-lead">{{ $selectedSection['lead'] ?? '' }}</p>
                                    </div>

                                    <div class="about-detail-visual">
                                        <img src="{{ asset($detailImage) }}" alt="{{ $selectedSection['label'] ?? 'About Section' }}">
                                    </div>
                                </div>

                                <div class="about-detail-body">
                                    @if($selectedSlug === 'logo-and-symbols')
                                    <div class="about-identity-shell">
                                        <article class="contents-card contents-card--info contents-card--identity">
                                            <div class="contents-card-inner">
                                                <div class="contents-card-front contents-card-front--info">
                                                    <div class="contents-card-copy contents-card-copy--info">
                                                        <span class="contents-card-number">Identity Mark</span>
                                                        <h3>Official Seal</h3>
                                                    </div>
                                                    <div class="contents-card-body contents-card-body--identity">
                                                        <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP logo">
                                                        <div class="about-identity-badges">
                                                            <span>Official Seal</span>
                                                            <span>Maroon and Gold</span>
                                                            <span>Campus Identity</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>

                                        <div class="about-detail-card-grid">
                                            @foreach($selectedSection['identity_marks'] ?? [] as $identityMark)
                                                <article class="contents-card contents-card--info">
                                                    <div class="contents-card-inner">
                                                        <div class="contents-card-front contents-card-front--info">
                                                            <div class="contents-card-copy contents-card-copy--info">
                                                                <span class="contents-card-number">Meaning {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                                <h3>{{ $identityMark['title'] ?? '' }}</h3>
                                                            </div>
                                                            <div class="contents-card-body">
                                                                <p>{{ $identityMark['body'] ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>

                                    <article class="contents-card contents-card--info contents-card--note">
                                        <div class="contents-card-inner">
                                            <div class="contents-card-front contents-card-front--info">
                                                <div class="contents-card-copy contents-card-copy--info">
                                                    <span class="contents-card-number">Quick View</span>
                                                    <h3>Symbolism at a glance</h3>
                                                </div>
                                                <div class="contents-card-body">
                                                    <ul class="about-detail-list">
                                                        @foreach($selectedSection['symbol_points'] ?? [] as $point)
                                                            <li>{{ $point }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @elseif($selectedSlug === 'hymn')
                                    <div class="about-detail-card-grid">
                                        @foreach($selectedSection['hymn_sections'] ?? [] as $hymnSection)
                                            <article class="contents-card contents-card--info">
                                                <div class="contents-card-inner">
                                                    <div class="contents-card-front contents-card-front--info">
                                                        <div class="contents-card-copy contents-card-copy--info">
                                                            <span class="contents-card-number">Hymn Note {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                            <h3>{{ $hymnSection['title'] ?? '' }}</h3>
                                                        </div>
                                                        <div class="contents-card-body">
                                                            <p>{{ $hymnSection['body'] ?? '' }}</p>
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
                                                    <span class="contents-card-number">Shared Meaning</span>
                                                    <h3>Why it matters</h3>
                                                </div>
                                                <div class="contents-card-body">
                                                    <ul class="about-detail-list">
                                                        @foreach($selectedSection['hymn_notes'] ?? [] as $note)
                                                            <li>{{ $note }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @elseif($selectedSlug === 'maps')
                                    <div class="about-detail-card-grid">
                                        @foreach($selectedSection['map_cards'] ?? [] as $mapCard)
                                            <article class="contents-card contents-card--info">
                                                <div class="contents-card-inner">
                                                    <div class="contents-card-front contents-card-front--info">
                                                        <div class="contents-card-copy contents-card-copy--info">
                                                            <span class="contents-card-number">Map Guide {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                            <h3>{{ $mapCard['title'] ?? '' }}</h3>
                                                        </div>
                                                        <div class="contents-card-body">
                                                            <p>{{ $mapCard['body'] ?? '' }}</p>
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
                                                    <span class="contents-card-number">Visit Planning</span>
                                                    <h3>Plan your campus visit</h3>
                                                </div>
                                                <div class="contents-card-body contents-card-body--split">
                                                    <p>{{ $selectedSection['visit_planning_text'] ?? '' }}</p>
                                                    <a href="{{ $selectedSection['map_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="section-link">Open Map</a>
                                                </div>
                                            </div>
                                        </div>
                                    </article>

                                    <article class="contents-card contents-card--info contents-card--note">
                                        <div class="contents-card-inner">
                                            <div class="contents-card-front contents-card-front--info">
                                                <div class="contents-card-copy contents-card-copy--info">
                                                    <span class="contents-card-number">Visitor Tips</span>
                                                    <h3>Visitor reminders</h3>
                                                </div>
                                                <div class="contents-card-body">
                                                    <ul class="about-detail-list">
                                                        @foreach($selectedSection['visit_notes'] ?? [] as $note)
                                                            <li>{{ $note }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @elseif($selectedSlug === 'campus-officials')
                                    <div class="about-detail-card-grid about-detail-card-grid--officials">
                                        @foreach($selectedSection['official_groups'] ?? [] as $officialGroup)
                                            <article class="contents-card contents-card--info">
                                                <div class="contents-card-inner">
                                                    <div class="contents-card-front contents-card-front--info">
                                                        <div class="contents-card-copy contents-card-copy--info">
                                                            <span class="contents-card-number">Office {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                            <h3>{{ $officialGroup['title'] ?? '' }}</h3>
                                                        </div>
                                                        <div class="contents-card-body">
                                                            <p>{{ $officialGroup['body'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
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
                                </div>
                            </div>
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

            body {
                background: #fff;
                min-height: auto !important;
                display: flex !important;
            }

            .main-content {
                flex: none !important;
                min-height: 0 !important;
            }

            body > .main-content {
                flex: none !important;
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
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .about-breadcrumb {
                padding-left: var(--about-page-gutter, 24px) !important;
                padding-right: var(--about-page-gutter, 24px) !important;
            }

            .hero-shell {
                width: 100vw !important;
                max-width: none !important;
                left: 50% !important;
                right: 50% !important;
                margin-left: -50vw !important;
                margin-right: -50vw !important;
            }

            .about-intro,
            .contents-strip,
            .history-story,
            .about-section-card {
                width: 100vw !important;
                max-width: none !important;
                left: auto !important;
                right: auto !important;
                margin-left: calc(50% - 50vw) !important;
                margin-right: calc(50% - 50vw) !important;
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

                const postPreviewHeight = () => {
                    const docEl = document.documentElement;
                    const body = document.body;
                    const main = document.querySelector('.main-content');
                    const visibleChildren = main
                        ? Array.from(main.children).filter((node) => {
                            if (!(node instanceof HTMLElement)) {
                                return false;
                            }

                            const styles = window.getComputedStyle(node);
                            return styles.display !== 'none' && styles.visibility !== 'hidden';
                        })
                        : [];
                    const contentBottom = visibleChildren.reduce((maxBottom, node) => {
                        const bottom = node.offsetTop + node.offsetHeight;
                        return Math.max(maxBottom, bottom);
                    }, 0);
                    const mainBottom = main ? main.offsetTop + main.offsetHeight : 0;
                    const naturalHeight = Math.max(contentBottom, mainBottom);
                    const height = Math.max(
                        naturalHeight > 0 ? Math.ceil(naturalHeight) : 0,
                        docEl.scrollHeight,
                        body.scrollHeight,
                        docEl.offsetHeight,
                        body.offsetHeight
                    );

                    window.parent?.postMessage({
                        type: 'cms-about-preview-height',
                        height,
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
                        schedulePreviewHeight();
                    });

                    observer.observe(document.body);

                    const main = document.querySelector('.main-content');
                    if (main) {
                        observer.observe(main);
                    }
                }

                window.addEventListener('load', schedulePreviewHeight);
                window.addEventListener('resize', schedulePreviewHeight);
                window.addEventListener('pageshow', schedulePreviewHeight);
                document.fonts?.ready?.then(schedulePreviewHeight).catch(() => {});
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        schedulePreviewHeight();
                    }
                });

                window.setTimeout(schedulePreviewHeight, 120);
                window.setTimeout(schedulePreviewHeight, 360);
                schedulePreviewHeight();
            });
        </script>
    @endif

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
