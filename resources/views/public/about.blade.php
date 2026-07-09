<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v={{ filemtime(public_path('assets/css/about.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/students.css') }}?v={{ filemtime(public_path('assets/css/students.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/card-selector.css') }}?v={{ filemtime(public_path('assets/css/card-selector.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Polytechnic University of the Philippines - Taguig Campus" />
    <meta property="og:description" content="Welcome to the PUP Taguig Campus Website" />
    <meta property="og:image" content="{{ asset('assets/static_img/logo.png') }}" />

</head>
<body @if(!empty($cmsPreview)) data-cms-preview="true" @endif>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $aboutCms = \App\Support\AboutCmsContent::fromInput($aboutCms ?? [], null);
        $overview = $aboutCms['overview'] ?? [];
        $sections = $sections ?? ($aboutCms['sections'] ?? []);
        $contentsSections = array_values(array_filter($sections, static function ($section) {
            return (string) ($section['visible_in_contents'] ?? '1') !== '0';
        }));
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

        @unless($selectedSection)
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
                                <img src="{{ \App\Support\AboutCmsContent::resolveImagePath($overview['hero_image'] ?? null, 'assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
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
        @endunless

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
                                <img src="{{ \App\Support\AboutCmsContent::resolveImagePath($overview['story_image'] ?? null, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $overview['story_title'] ?? 'PUP Taguig Campus' }}">
                            </div>

                            @php
                                $storyDescription = (string) ($overview['story_description'] ?? '');
                                $storyDescriptionHtml = trim($storyDescription) !== strip_tags($storyDescription)
                                    ? \App\Support\RichText::sanitize($storyDescription)
                                    : nl2br(e($storyDescription));
                            @endphp
                            <div class="campus-story-description rich-text-content">
                                {!! $storyDescriptionHtml !!}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="philosophy-intro-text reveal">
                    <div class="philosophy-intro-text-inner philosophy-intro-rich-copy">
                        <p class="section-tag layout-kicker" style="margin-bottom: 24px;">PUP-T Philosophy</p>
                        <p><strong>Quality and relevant education</strong> that responds to the call of present times in building the <strong>foundations of the future.</strong></p>
                        <p>Ranging from high school to doctoral courses, traditional to nontraditional education system, <strong>the University makes it possible</strong> that <strong>deserving individuals can have access</strong> to these academic resources.</p>
                        <p>The University has always been making <strong>initiatives to enrich its academic programs</strong> in various fields of study and <strong>implement an educational strategy</strong> designed to provide our students with highly employable, managerial, and entrepreneurial skills in order to make them exceedingly <strong>creative, productive, competitive, and self-reliant</strong>.</p>
                    </div>
                </section>

                <section class="contents-strip reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}">
                    <div>
                    <div class="contents-strip-head layout-inset">
                        <p class="section-tag layout-kicker">{{ $overview['contents_tag'] ?? 'Contents' }}</p>
                        <h2>{{ $overview['contents_title'] ?? 'All about the campus' }}</h2>
                    </div>

                    <nav class="contents-cards{{ $cmsPreview ? '' : ' alphabetical-card-pages' }}" aria-label="About page contents">
                        @foreach($contentsSections as $section)
                            @if($cmsPreview)
                                <article
                                    class="contents-card card_without_section"
                                    data-cms-edit-trigger="contents"
                                    data-cms-section-label="About Contents"
                                    data-about-contents-card
                                    data-about-contents-slug="{{ $section['slug'] }}"
                                    data-about-contents-label="{{ $section['label'] }}"
                                >
                            @else
                                <a
                                    href="{{ route('public.about.section', ['section' => $section['slug'], 'cms_preview' => $cmsPreview ? 1 : null]) }}"
                                    class="contents-card card_without_section"
                                >
                            @endif
                                @if($cmsPreview)
                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                        <button type="button" class="cms-preview-card-action" data-about-card-edit title="Edit card" aria-label="Edit {{ $section['label'] }}">
                                            Edit
                                        </button>
                                    </div>
                                @endif
                                <div class="contents-card-inner">
                                    <div class="contents-card-front">
                                        <img src="{{ \App\Support\AboutCmsContent::resolveImagePath($section['image'] ?? null, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $section['label'] }}">
                                        <div class="contents-card-copy">
                                            <h3>{{ $section['label'] }}</h3>
                                        </div>
                                    </div>

                                    @unless($cmsPreview)
                                        <div class="contents-card-back">
                                            <div class="contents-card-overlay-copy">
                                                <h3>{{ $section['label'] }}</h3>
                                                <p>{{ \Illuminate\Support\Str::limit((string) ($section['summary'] ?? ''), 120) }}</p>
                                            </div>
                                            <span class="contents-card-action">See more</span>
                                        </div>
                                    @endunless
                                </div>
                            @if($cmsPreview)
                                </article>
                            @else
                                </a>
                            @endif
                        @endforeach
                    </nav>
                    </div>
                </section>
            @endunless

            @if($selectedSection)
                @php
                    $selectedSlug = $selectedSection['slug'] ?? '';
                    $detailImage = \App\Support\AboutCmsContent::resolveImagePath($overview['section_header_image'] ?? null, 'assets/static_img/about_header_image.png');
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
                                                    @continue((string) ($milestone['visible'] ?? '1') === '0')
                                                    @php
                                                        $timelineIndex = $loop->index;
                                                        $timelineBody = implode('', array_map(static function ($paragraph) {
                                                            $text = trim((string) $paragraph);

                                                            if ($text === '') {
                                                                return '';
                                                            }

                                                            return preg_match('/<[^>]+>/', $text)
                                                                ? $text
                                                                : '<p>'.e($text).'</p>';
                                                        }, $milestone['body'] ?? []));
                                                    @endphp
                                                    <article
                                                        class="history-timeline-row reveal {{ $loop->odd ? 'is-left' : 'is-right' }} {{ $loop->iteration % 2 === 0 ? 'delay-200' : 'delay-100' }}"
                                                        @if($cmsPreview)
                                                            data-about-history-card
                                                            data-about-history-index="{{ $timelineIndex }}"
                                                            data-about-history-label="{{ $milestone['title'] ?? 'Milestone' }}"
                                                        @endif
                                                    >
                                                        <div class="history-timeline-marker" aria-hidden="true">
                                                            <span class="history-timeline-dot"></span>
                                                        </div>

                                                        <div class="history-timeline-card">
                                                            @if($cmsPreview)
                                                                <div class="cms-preview-card-actions" aria-label="Timeline actions">
                                                                    <button type="button" class="cms-preview-card-action" data-about-history-edit>Edit</button>
                                                                </div>
                                                            @endif
                                                            <span class="history-timeline-period">{{ $milestone['period'] ?? '' }}</span>
                                                            <h5>{{ $milestone['title'] ?? '' }}</h5>
                                                            @if($cmsPreview)
                                                                <div class="history-timeline-copy rich-text-content">
                                                                    {!! \App\Support\RichText::sanitize($timelineBody) !!}
                                                                </div>
                                                            @else
                                                                <div class="history-timeline-preview rich-text-content">
                                                                    {!! \App\Support\RichText::sanitize($timelineBody) !!}
                                                                </div>
                                                                <div class="history-timeline-more" aria-hidden="true"></div>
                                                                <button type="button" class="history-timeline-toggle" aria-expanded="false" aria-label="Read more about {{ $milestone['title'] ?? 'this milestone' }}">
                                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                                        <path d="m12 18.8 7.4-7.4-1.4-1.4-5 5v-9h-2v9l-5-5-1.4 1.4L12 18.8Z"></path>
                                                                    </svg>
                                                                </button>
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
                        <section class="history-story history-story--vision">
                            <div class="history-story-inner">
                                <div class="history-timeline-container history-timeline-container--vision reveal">
                                    <div class="history-timeline-shell history-timeline-shell--vision">
                                    <div class="about-detail-body about-detail-body--vision">
                                        @if($cmsPreview)
                                            <div class="about-vision-content about-vision-content--cms reveal delay-200">
                                                <div class="about-vision-row about-vision-row--vision is-open">
                                                    <div>
                                                        <div class="about-vision-trigger about-vision-trigger--vision about-vision-trigger--static" aria-hidden="true">
                                                            <span class="about-vision-word">Vision</span>
                                                            <span class="about-vision-arrow about-vision-arrow--right" aria-hidden="true">
                                                                <svg viewBox="0 0 24 24" focusable="false">
                                                                    <path d="M4 12h14"></path>
                                                                    <path d="m13 5 7 7-7 7"></path>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="about-vision-panel" aria-hidden="false">
                                                            <div
                                                                class="cms-preview-editable"
                                                                data-cms-section="vision-statement"
                                                                data-cms-section-label="Vision Statement"
                                                            >
                                                                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="vision-statement" aria-label="Edit Vision Statement">
                                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                                                    </svg>
                                                                </button>
                                                                <div data-cms-boundary class="about-vision-card">
                                                                    <p class="about-vision-statement">{{ $selectedSection['vision'] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="about-vision-row about-vision-row--mission is-open">
                                                    <div>
                                                        <div class="about-vision-panel about-vision-panel--mission" aria-hidden="false">
                                                            <div
                                                                class="cms-preview-editable"
                                                                data-cms-section="mission-statement"
                                                                data-cms-section-label="Mission Statement"
                                                            >
                                                                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="mission-statement" aria-label="Edit Mission Statement">
                                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                                                    </svg>
                                                                </button>
                                                                <div data-cms-boundary class="about-vision-card about-vision-card--mission">
                                                                    <p class="about-vision-statement about-vision-statement--mission">{{ $selectedSection['mission'] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="about-vision-trigger about-vision-trigger--mission about-vision-trigger--static" aria-hidden="true">
                                                            <span class="about-vision-arrow about-vision-arrow--left" aria-hidden="true">
                                                                <svg viewBox="0 0 24 24" focusable="false">
                                                                    <path d="M20 12H6"></path>
                                                                    <path d="m11 5-7 7 7 7"></path>
                                                                </svg>
                                                            </span>
                                                            <span class="about-vision-word">Mission</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="about-vision-content reveal delay-200">
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
                                        @endif

                                        <div class="about-vision-extension-grid">
                                            <article class="about-vision-feature about-vision-feature--goals reveal" data-reveal-threshold="0.28">
                                                <div>
                                                    <div class="about-vision-feature-head">
                                                        <span class="about-vision-feature-kicker">Strategic Goals</span>
                                                        <h3>Three priority pillars that guide how the University teaches, serves, and grows.</h3>
                                                    </div>

                                                    <div class="about-goals-grid">
                                                        @php
                                                            $strategicGoalNumber = 1;
                                                        @endphp
                                                        @foreach($selectedSection['strategic_goals'] ?? [] as $goalGroup)
                                                            <details
                                                                class="about-goal-pillar{{ $cmsPreview ? ' cms-preview-editable-card' : '' }}"
                                                                @if($cmsPreview)
                                                                    data-about-strategic-goal-card
                                                                    data-about-strategic-goal-index="{{ $loop->index }}"
                                                                    data-about-strategic-goal-label="{{ $goalGroup['pillar'] ?? ('Pillar ' . $loop->iteration) }}"
                                                                @endif
                                                            >
                                                                <summary class="about-goal-pillar-summary">
                                                                    <div class="about-goal-pillar-head">
                                                                        <div class="about-goal-pillar-tag" aria-label="{{ $goalGroup['pillar'] ?? '' }}">
                                                                            <span class="about-goal-pillar-icon-shell" aria-hidden="true">
                                                                                @php
                                                                                    $pillarIconSet = [
                                                                                        'assets/static_img/pillar_1_icon.svg',
                                                                                        'assets/static_img/pillar_2_icon.svg',
                                                                                        'assets/static_img/pillar_3_icon.svg',
                                                                                    ];
                                                                                    $pillarIcon = $pillarIconSet[$loop->index % count($pillarIconSet)] ?? $pillarIconSet[0];
                                                                                @endphp
                                                                                <img
                                                                                    src="{{ asset($pillarIcon) }}"
                                                                                    alt=""
                                                                                    class="about-goal-pillar-icon"
                                                                                >
                                                                            </span>
                                                                            <span class="about-goal-pillar-label-group">
                                                                                <span class="about-goal-pillar-label">{{ $goalGroup['pillar'] ?? ('Pillar ' . $loop->iteration) }}</span>
                                                                            </span>
                                                                        </div>
                                                                        <h4>{{ $goalGroup['title'] ?? '' }}</h4>
                                                                    </div>
                                                                    <span class="about-goal-pillar-toggle" aria-hidden="true">
                                                                        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                                                                            <path d="M6 9l6 6 6-6"></path>
                                                                        </svg>
                                                                    </span>
                                                                </summary>
                                                                @if($cmsPreview)
                                                                    <div class="cms-preview-card-actions">
                                                                        <button type="button" class="cms-preview-card-action" data-about-strategic-goal-edit>Edit</button>
                                                                    </div>
                                                                @endif
                                                                <div class="about-goal-pillar-body">
                                                                    <ul class="about-goal-list">
                                                                        @foreach($goalGroup['goals'] ?? [] as $goal)
                                                                            <li>
                                                                                <span class="about-goal-code">SG-{{ $strategicGoalNumber }}</span>
                                                                                <span class="about-goal-text">{{ $goal['text'] ?? '' }}</span>
                                                                            </li>
                                                                            @php
                                                                                $strategicGoalNumber++;
                                                                            @endphp
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </details>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div>
                                    </div>
                                </div>

                                <article
                                    class="about-values-band reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                                    data-reveal-threshold="0.32"
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
                                            <h3>{{ $selectedSection['core_values_heading'] ?? 'INSPIRED values that shape the character of the PUP community.' }}</h3>
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
                            class="{{ $selectedSlug === 'citizens-charter' ? 'students-contents-strip' : 'about-section-card' }} reveal{{ $cmsPreview && $selectedSlug !== 'hymn' && $selectedSlug !== 'maps' && $selectedSlug !== 'logo-and-symbols' ? ' cms-preview-editable' : '' }}"
                            @if($cmsPreview && $selectedSlug !== 'hymn' && $selectedSlug !== 'maps' && $selectedSlug !== 'logo-and-symbols')
                                data-cms-section="{{ $selectedSlug }}"
                                data-cms-section-label="{{ $selectedSection['label'] }}"
                            @endif
                        >
                            @if($cmsPreview && $selectedSlug !== 'hymn' && $selectedSlug !== 'maps' && $selectedSlug !== 'logo-and-symbols')
                                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="{{ $selectedSlug }}" aria-label="Edit {{ $selectedSection['label'] }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                                    </svg>
                                </button>
                            @endif

                             @if($selectedSlug !== 'logo-and-symbols' && $selectedSlug !== 'hymn' && $selectedSlug !== 'maps' && $selectedSlug !== 'campus-officials' && $selectedSlug !== 'citizens-charter')
                                <div class="about-detail-heading">
                                    <div class="about-detail-heading-copy">
                                        <h2>{{ $selectedSection['label'] ?? '' }}</h2>
                                    </div>
                                </div>
                            @endif

                                <div class="about-detail-body">
                                    @if($selectedSlug === 'logo-and-symbols')
                                        @php
                                            $sealCards = array_values($selectedSection['seals'] ?? []);
                                        @endphp

                                        <section class="ls-gallery-shell reveal" data-ls-gallery>
                                            <div class="ls-gallery-head">
                                                <p class="section-tag">Campus Identity</p>
                                                <h2 class="ls-gallery-title">{{ $selectedSection['label'] ?? 'Logo and Symbols' }}</h2>
                                                <p class="ls-gallery-summary">
                                                    {{ $selectedSection['summary'] ?? 'View each campus seal and logo. Click any item to show its meaning and purpose.' }}
                                                </p>
                                            </div>

                                            <div class="ls-gallery-grid" role="tablist" aria-label="Logos and seals">
                                                @if($cmsPreview)
                                                    <button
                                                        type="button"
                                                        class="ls-gallery-card ls-gallery-card--add"
                                                        data-about-seal-card-add
                                                        aria-label="Add seal"
                                                    >
                                                        <span class="ls-gallery-add-plus" aria-hidden="true">+</span>
                                                        <span class="ls-gallery-label">Add Seal</span>
                                                        <span class="ls-gallery-tag">CMS Action</span>
                                                    </button>
                                                @endif
                                                @foreach($sealCards as $seal)
                                                    @php
                                                        $sealId = trim((string) ($seal['id'] ?? ''));
                                                        if ($sealId === '') {
                                                            $sealId = 'seal-'.$loop->iteration;
                                                        }
                                                        $sealLabel = trim((string) ($seal['label'] ?? ''));
                                                        $sealTag = trim((string) ($seal['tag'] ?? ''));
                                                        $sealImage = \App\Support\AboutCmsContent::resolveImagePath($seal['image'] ?? null, 'assets/static_img/logo.png');
                                                        $sealHighlights = array_values(array_filter(array_map(static fn ($item) => trim((string) $item), is_array($seal['highlights'] ?? null) ? $seal['highlights'] : [])));
                                                        $sealInfoTitle = trim((string) data_get($seal, 'information.title', 'Informations about the Seal'));
                                                        $sealInfoDescription = (string) data_get($seal, 'information.description', '');
                                                        $sealReportsTitle = trim((string) data_get($seal, 'reports.title', 'Reports and Records'));
                                                        $sealReportsDescription = (string) data_get($seal, 'reports.description', '');
                                                        $sealInfoHtml = trim($sealInfoDescription) !== strip_tags($sealInfoDescription)
                                                            ? \App\Support\RichText::sanitize($sealInfoDescription)
                                                            : nl2br(e($sealInfoDescription));
                                                        $sealReportsHtml = trim($sealReportsDescription) !== strip_tags($sealReportsDescription)
                                                            ? \App\Support\RichText::sanitize($sealReportsDescription)
                                                            : nl2br(e($sealReportsDescription));
                                                        $sealLinks = array_values(array_filter(is_array($seal['links'] ?? null) ? $seal['links'] : [], static function ($item) {
                                                            if (!is_array($item)) {
                                                                return false;
                                                            }

                                                            return trim((string) ($item['label'] ?? '')) !== '' || trim((string) ($item['url'] ?? '')) !== '';
                                                        }));
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        class="ls-gallery-card{{ $cmsPreview ? ' cms-preview-editable-card' : '' }}"
                                                        data-ls-seal-trigger
                                                        data-seal-id="{{ $sealId }}"
                                                        aria-controls="ls-seal-panel-{{ $sealId }}"
                                                        aria-expanded="false"
                                                        @if($cmsPreview)
                                                            data-about-section-card
                                                            data-about-section-card-section="logo-and-symbols"
                                                            data-about-section-card-index="{{ $loop->index }}"
                                                            data-about-section-card-label="{{ $sealLabel !== '' ? $sealLabel : ('Seal ' . $loop->iteration) }}"
                                                            data-about-section-card-route="logo-and-symbols"
                                                        @endif
                                                    >
                                                    @if($cmsPreview)
                                                        <span class="cms-preview-card-actions" aria-label="Seal actions">
                                                            <span class="cms-preview-card-action" data-about-section-card-edit>Edit</span>
                                                            <span class="cms-preview-card-action cms-preview-card-action-delete" data-about-section-card-delete>Delete</span>
                                                        </span>
                                                    @endif
                                                        <span class="ls-gallery-media">
                                                            <img src="{{ $sealImage }}" alt="{{ $sealLabel !== '' ? $sealLabel : 'Seal' }}">
                                                        </span>
                                                        <span class="ls-gallery-label">{{ $sealLabel !== '' ? $sealLabel : ('Seal ' . $loop->iteration) }}</span>
                                                        <span class="ls-gallery-tag">{{ $sealTag !== '' ? $sealTag : 'Campus Seal' }}</span>
                                                    </button>
                                                @endforeach
                                            </div>

                                            <div class="ls-gallery-panel-shell">
                                                <div class="ls-gallery-empty" data-ls-seal-empty>
                                                    Select a seal above to view details.
                                                </div>

                                                @foreach($sealCards as $seal)
                                                    @php
                                                        $sealId = trim((string) ($seal['id'] ?? ''));
                                                        if ($sealId === '') {
                                                            $sealId = 'seal-'.$loop->iteration;
                                                        }
                                                        $sealLabel = trim((string) ($seal['label'] ?? ''));
                                                        $sealTag = trim((string) ($seal['tag'] ?? ''));
                                                        $sealHighlights = array_values(array_filter(array_map(static fn ($item) => trim((string) $item), is_array($seal['highlights'] ?? null) ? $seal['highlights'] : [])));
                                                        $sealInfoTitle = trim((string) data_get($seal, 'information.title', 'Informations about the Seal'));
                                                        $sealInfoDescription = (string) data_get($seal, 'information.description', '');
                                                        $sealReportsTitle = trim((string) data_get($seal, 'reports.title', 'Reports and Records'));
                                                        $sealReportsDescription = (string) data_get($seal, 'reports.description', '');
                                                        $sealInfoHtml = trim($sealInfoDescription) !== strip_tags($sealInfoDescription)
                                                            ? \App\Support\RichText::sanitize($sealInfoDescription)
                                                            : nl2br(e($sealInfoDescription));
                                                        $sealReportsHtml = trim($sealReportsDescription) !== strip_tags($sealReportsDescription)
                                                            ? \App\Support\RichText::sanitize($sealReportsDescription)
                                                            : nl2br(e($sealReportsDescription));
                                                        $sealLinks = array_values(array_filter(is_array($seal['links'] ?? null) ? $seal['links'] : [], static function ($item) {
                                                            if (!is_array($item)) {
                                                                return false;
                                                            }

                                                            return trim((string) ($item['label'] ?? '')) !== '' || trim((string) ($item['url'] ?? '')) !== '';
                                                        }));
                                                    @endphp
                                                    <article
                                                        id="ls-seal-panel-{{ $sealId }}"
                                                        class="ls-gallery-panel"
                                                        data-ls-seal-panel="{{ $sealId }}"
                                                        hidden
                                                    >
                                                        <div class="ls-gallery-panel-head">
                                                            <span class="ls-gallery-panel-tag">{{ $sealTag !== '' ? $sealTag : 'Campus Seal' }}</span>
                                                            <h3 class="ls-gallery-panel-title">{{ $sealLabel !== '' ? $sealLabel : ('Seal ' . $loop->iteration) }}</h3>
                                                        </div>
                                                        @if(!empty($sealHighlights))
                                                            <h4 class="ls-gallery-block-title">Highlights</h4>
                                                            <ul class="ls-gallery-panel-list">
                                                                @foreach($sealHighlights as $highlight)
                                                                    <li>{{ $highlight }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        <div class="ls-gallery-two-col">
                                                            <article class="ls-gallery-container-card rich-text-content">
                                                                <h4>{{ $sealInfoTitle !== '' ? $sealInfoTitle : 'Informations about the Seal' }}</h4>
                                                                {!! $sealInfoHtml !!}
                                                            </article>
                                                            <article class="ls-gallery-container-card rich-text-content">
                                                                <h4>{{ $sealReportsTitle !== '' ? $sealReportsTitle : 'Reports and Records' }}</h4>
                                                                {!! $sealReportsHtml !!}
                                                            </article>
                                                        </div>
                                                        @if(!empty($sealLinks))
                                                            <div class="ls-gallery-links">
                                                                <h4 class="ls-gallery-block-title">Links</h4>
                                                                <ul>
                                                                    @foreach($sealLinks as $sealLink)
                                                                        @php
                                                                            $linkLabel = trim((string) ($sealLink['label'] ?? ''));
                                                                            $linkUrl = trim((string) ($sealLink['url'] ?? ''));
                                                                        @endphp
                                                                        @continue($linkLabel === '' && $linkUrl === '')
                                                                        <li>
                                                                            @if($linkUrl !== '')
                                                                                <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer">{{ $linkLabel !== '' ? $linkLabel : $linkUrl }}</a>
                                                                            @else
                                                                                <span>{{ $linkLabel }}</span>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </article>
                                                @endforeach
                                            </div>
                                        </section>
                                    @elseif($selectedSlug === 'hymn')
                                        @php
                                            $defaultHymnSection = \App\Support\AboutCmsContent::defaults()['sections']['hymn'] ?? [];
                                            $rawHymnSections = $selectedSection['hymn_sections'] ?? [];
                                            $hasActualLyrics = collect($rawHymnSections)->contains(static function ($hymnSection) {
                                                $body = mb_strtolower(trim((string) ($hymnSection['body'] ?? '')));

                                                return $body !== ''
                                                    && (
                                                        str_contains($body, 'sintang paaralan')
                                                        || str_contains($body, 'paaralang dakila')
                                                        || str_contains($body, "\n")
                                                    );
                                            });
                                            $hymnSectionData = $hasActualLyrics
                                                ? $selectedSection
                                                : array_merge($selectedSection, $defaultHymnSection);
                                        @endphp
                                        <div class="hymn-layout">
                                            <div class="hymn-page-header reveal">
                                                <p class="section-tag">Official Hymn</p>
                                                <h2 class="hymn-page-title">{{ $hymnSectionData['label'] ?? 'Hymn' }}</h2>
                                                <p class="hymn-page-attribution">{{ $hymnSectionData['summary'] ?? '' }}</p>
                                            </div>
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
                                                    <p class="hymn-title-tag">{{ $hymnSectionData['label'] ?? 'Hymn' }}</p>
                                                    <h3 class="hymn-tagline">{{ $hymnSectionData['summary'] ?? 'A Song of Covenant with the Nation' }}</h3>
                                                    <p class="hymn-hero-desc">{{ $hymnSectionData['lead'] ?? '' }}</p>
                                                    <a href="https://youtu.be/Yib_s5UeGvc?si=CR3qUIEbH25lZxfw" target="_blank" rel="noopener noreferrer" class="hymn-listen-btn" aria-label="Listen to the PUP Hymn on YouTube">
                                                        <span class="hymn-listen-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M8 5.14v14l11-7-11-7z"/></svg></span>
                                                        Listen to the Hymn
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="hymn-lyrics-block reveal">
                                                <div class="hymn-lyrics-header">
                                                    <span class="ls-block-kicker">Full Lyrics</span>
                                                    <h3 class="ls-block-title">{{ $hymnSectionData['label'] ?? 'Hymn' }}</h3>
                                                </div>
                                                <div class="hymn-lyrics-body">
                                                    @foreach($hymnSectionData['hymn_sections'] ?? [] as $hymnSection)
                                                        @php
                                                            $hymnLines = preg_split('/\R+/', trim((string) ($hymnSection['body'] ?? ''))) ?: [];
                                                            $hymnLines = array_values(array_filter(array_map(static fn ($line) => trim((string) $line), $hymnLines), static fn ($line) => $line !== ''));
                                                            $isChorus = str_contains(mb_strtolower((string) ($hymnSection['title'] ?? '')), 'chorus');
                                                        @endphp
                                                        <div class="hymn-stanza{{ $isChorus ? ' hymn-stanza--chorus' : '' }}">
                                                            <p class="hymn-stanza-label">{{ $hymnSection['title'] ?? ('Section ' . $loop->iteration) }}</p>
                                                            @foreach($hymnLines as $lineIndex => $hymnLine)
                                                                @php
                                                                    $lineClass = 'hymn-line';
                                                                    if (trim($hymnLine) === 'PUP, aming gabay') {
                                                                        $lineClass .= ' hymn-line--refrain';
                                                                    } elseif (
                                                                        $isChorus
                                                                        && $lineIndex === count($hymnLines) - 1
                                                                        && trim($hymnLine) === 'PUP, pinagpala'
                                                                    ) {
                                                                        $lineClass .= ' hymn-line--end';
                                                                    }
                                                                @endphp
                                                                <p class="{{ $lineClass }}">{{ $hymnLine }}</p>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($selectedSlug === 'maps')

                                    @php
                                        $mapUrl = $selectedSection['map_url'] ?? 'https://maps.app.goo.gl/RDAwxBvDzyGzUbVN7';
                                    @endphp
                                    <div class="map-hero-header">
                                    <div>
                                        <p class="map-hero-eyebrow">Location</p>
                                        <h2 class="map-hero-title">{{ $selectedSection['label'] ?? 'Maps' }}</h2>
                                        <p class="map-hero-address">{{ $selectedSection['summary'] ?? '' }}</p>
                                        <div class="map-hero-chips">
                                        <span class="map-chip map-chip--gold">Lower Bicutan, Taguig</span>
                                        <span class="map-chip map-chip--light">Mon-Fri · 7AM-6PM</span>
                                        <span class="map-chip map-chip--light">Public University</span>
                                        </div>
                                        <p class="map-hero-address">{{ $selectedSection['lead'] ?? '' }}</p>
                                    </div>
                                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="map-hero-cta">
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
                                        </div>
                                        <div class="map-iframe-panel">
                                            <iframe id="pane-street"
                                            src="https://maps.google.com/maps?q=&layer=c&cbll=14.488971,121.0511899&cbp=12,164.16,0,86.36,0&z=18&output=svembed"
                                            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="PUP Taguig Campus View">
                                            </iframe>
                                        </div>
                                        </div>

                                        <div class="map-dir-footer">
                                        <div class="map-dir-text">
                                            <strong>Open in Google Maps</strong> {{ $selectedSection['visit_planning_text'] ?? 'for real-time directions, live traffic, and transit options from your location.' }}
                                        </div>
                                        <div class="map-dir-btns">
                                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="map-dir-btn map-dir-btn--primary">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                                            Open Maps
                                            </a>
                                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="map-dir-btn map-dir-btn--gold">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                            Directions
                                            </a>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="map-sidebar">

                                        <div class="map-transport-card reveal delay-100">
                                        <div class="map-info-kicker">How to Get Here</div>
                                        <div class="map-transport-list">
                                            @foreach($selectedSection['map_cards'] ?? [] as $mapCard)
                                                <article class="map-transport-row">
                                                    <div class="map-t-icon"><svg viewBox="0 0 24 24" fill="#7f1113" width="14" height="14"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.4" fill="#f4e7cf"/></svg></div>
                                                    <div>
                                                        <div class="map-t-name">{{ $mapCard['title'] ?? '' }}</div>
                                                        <div class="map-t-sub">{{ $mapCard['body'] ?? '' }}</div>
                                                    </div>
                                                    <span class="map-t-badge">Stop {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                </article>
                                            @endforeach
                                        </div>
                                        </div>

                                        <div class="map-tips-card2 reveal">
                                        <div class="map-tips-kicker">Visitor Tips</div>
                                        @foreach($selectedSection['visit_notes'] ?? [] as $visitNote)
                                            <div class="map-tip-item"><span class="map-tip-bull"></span>{{ $visitNote }}</div>
                                        @endforeach
                                        </div>

                                    </div>
                                    </div>

                                @elseif($selectedSlug === 'campus-officials')
                                @php
                                    $officialChartImageValue = trim((string) ($selectedSection['organizational_chart_image'] ?? ''));
                                    $officialChartImage = \App\Support\AboutCmsContent::resolveImagePath(
                                        $officialChartImageValue !== '' ? $officialChartImageValue : ($selectedSection['image'] ?? null),
                                        'assets/static_img/about_header_image.png'
                                    );
                                @endphp
                                <div class="ls-page-header reveal">
                                    <p class="section-tag">Campus Leadership</p>
                                    <h2 class="ls-page-title">{{ $selectedSection['label'] ?? 'Campus Officials' }}</h2>
                                    <p class="ls-page-subtitle">{{ $selectedSection['summary'] ?? '' }}</p>
                                </div>
                                <div class="officials-chart-shell reveal">
                                    <button
                                        type="button"
                                        class="officials-chart-trigger{{ $cmsPreview ? ' cms-preview-editable-card' : '' }}"
                                        @if($cmsPreview)
                                            data-about-campus-officials-chart-edit
                                            aria-label="Edit organizational structure image"
                                        @else
                                            data-campus-officials-chart-zoom
                                            aria-label="Zoom organizational structure image"
                                        @endif
                                    >
                                        <img
                                            src="{{ $officialChartImage }}"
                                            alt="{{ $selectedSection['label'] ?? 'Campus Officials' }} organizational structure"
                                            class="officials-chart-image"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <span class="officials-chart-zoom-badge">{{ $cmsPreview ? 'Edit' : 'Zoom' }}</span>
                                    </button>
                                </div>
                                <div class="officials-grid">
                                @if($cmsPreview)
                                    <button
                                        type="button"
                                        class="official-card official-card--add reveal"
                                        data-about-official-card-add
                                        aria-label="Add campus official"
                                    >
                                        <span class="official-card-add-inner">
                                            <span class="official-card-add-plus" aria-hidden="true">+</span>
                                            <span class="official-card-add-label">Add Card</span>
                                        </span>
                                    </button>
                                @endif
                                @foreach($selectedSection['official_groups'] ?? [] as $officialGroup)
                                    @php
                                        $officialImage = trim((string) ($officialGroup['image'] ?? ''));
                                    @endphp
                                    <article class="official-card reveal {{ $loop->index % 2 === 1 ? 'delay-100' : '' }}{{ $cmsPreview ? ' cms-preview-editable-card' : '' }}"
                                        tabindex="0"
                                        role="button"
                                        aria-expanded="false"
                                        aria-label="{{ $officialGroup['title'] ?? 'Campus official' }} profile card"
                                        @if($cmsPreview)
                                            data-about-section-card
                                            data-about-section-card-section="campus-officials"
                                            data-about-section-card-index="{{ $loop->index }}"
                                            data-about-section-card-label="{{ $officialGroup['title'] ?? ('Official Card ' . $loop->iteration) }}"
                                            data-about-section-card-route="campus-officials"
                                        @endif>
                                        @if($cmsPreview)
                                            <div class="cms-preview-card-actions" aria-label="Campus official actions">
                                                <button type="button" class="cms-preview-card-action" data-about-section-card-edit>Edit</button>
                                                <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-about-section-card-delete>Delete</button>
                                            </div>
                                        @endif
                                        <div class="official-card-inner">
                                            <div class="official-face official-face--front">
                                                <div class="official-portrait">
                                                    @if($officialImage !== '')
                                                        <img
                                                            src="{{ \App\Support\AboutCmsContent::resolveImagePath($officialImage, 'assets/static_img/temporary_profile.png') }}"
                                                            alt="{{ $officialGroup['title'] ?? 'Campus official' }}"
                                                            class="official-portrait-img"
                                                        >
                                                    @else
                                                        <img
                                                            src="{{ asset('assets/static_img/temporary_profile.png') }}"
                                                            alt="{{ $officialGroup['title'] ?? 'Campus official' }}"
                                                            class="official-portrait-img official-portrait-img--placeholder"
                                                        >
                                                    @endif
                                                    <div class="official-front-copy">
                                                        <p class="official-front-name">{{ $officialGroup['title'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="official-face official-face--back">
                                                <div class="official-back-layout">
                                                    <div class="official-back-portrait">
                                                        @if($officialImage !== '')
                                                            <img
                                                                src="{{ \App\Support\AboutCmsContent::resolveImagePath($officialImage, 'assets/static_img/temporary_profile.png') }}"
                                                                alt="{{ $officialGroup['title'] ?? 'Campus official' }}"
                                                                class="official-back-img"
                                                            >
                                                        @else
                                                            <img
                                                                src="{{ asset('assets/static_img/temporary_profile.png') }}"
                                                                alt="{{ $officialGroup['title'] ?? 'Campus official' }}"
                                                                class="official-back-img official-back-img--placeholder"
                                                            >
                                                        @endif
                                                    </div>
                                                    <div class="official-info">
                                                        <p class="official-name">{{ $officialGroup['name'] ?? '' }}</p>
                                                        <p class="official-role">{{ $officialGroup['title'] ?? '' }}</p>
                                                        <p class="official-description">{{ $officialGroup['body'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach

                                </div>
                                <p class="about-detail-caption">{{ $selectedSection['officials_note'] ?? '' }}</p>

                                <div class="officials-chart-zoom" id="campusOfficialsChartZoom" aria-hidden="true">
                                    <button type="button" class="officials-chart-zoom-close" data-campus-officials-chart-zoom-close aria-label="Close organizational structure zoom">&times;</button>
                                    <img id="campusOfficialsChartZoomImage" src="" alt="Zoomed organizational structure">
                                </div>

                                <script>
                                (function () {
                                    function initOfficialCards() {
                                        document.querySelectorAll('.official-card:not(.official-card--add)').forEach(function (card) {
                                            if (card.dataset.flipBound === '1') return;
                                            card.dataset.flipBound = '1';
                                            const cardInner = card.querySelector('.official-card-inner');
                                            const frontFace = card.querySelector('.official-face--front');
                                            const backFace = card.querySelector('.official-face--back');
                                            if (!(cardInner instanceof HTMLElement)) return;
                                            if (!(frontFace instanceof HTMLElement) || !(backFace instanceof HTMLElement)) return;

                                            const syncExpandedState = function () {
                                                card.setAttribute('aria-expanded', card.dataset.flipped === '1' ? 'true' : 'false');
                                            };

                                            const showFace = function (face) {
                                                const showBack = face === 'back';
                                                frontFace.style.display = showBack ? 'none' : 'flex';
                                                backFace.style.display = showBack ? 'flex' : 'none';
                                            };

                                            const setFlipState = function (isFlipped) {
                                                card.dataset.flipped = isFlipped ? '1' : '0';
                                                cardInner.style.transform = 'rotateY(0deg)';
                                                showFace(isFlipped ? 'back' : 'front');
                                                syncExpandedState();
                                            };

                                            const playFlip = function (nextFlipped) {
                                                if (card.dataset.flipAnimating === '1') return;

                                                card.dataset.flipAnimating = '1';
                                                cardInner.getAnimations().forEach(function (animation) {
                                                    animation.cancel();
                                                });

                                                const finalizeFlip = function () {
                                                    card.dataset.flipAnimating = '0';
                                                    setFlipState(nextFlipped);
                                                };

                                                if (typeof cardInner.animate === 'function') {
                                                    showFace(card.dataset.flipped === '1' ? 'back' : 'front');
                                                    cardInner.style.transform = 'rotateY(0deg)';

                                                    const firstHalf = cardInner.animate([
                                                        { transform: 'rotateY(0deg) scale(1)', offset: 0 },
                                                        { transform: nextFlipped ? 'rotateY(90deg) scale(1.02)' : 'rotateY(-90deg) scale(1.02)', offset: 1 }
                                                    ], {
                                                        duration: 320,
                                                        easing: 'cubic-bezier(0.55, 0.08, 0.68, 0.53)',
                                                        fill: 'forwards'
                                                    });

                                                    firstHalf.onfinish = function () {
                                                        showFace(nextFlipped ? 'back' : 'front');
                                                        cardInner.style.transform = nextFlipped ? 'rotateY(-90deg)' : 'rotateY(90deg)';

                                                        const secondHalf = cardInner.animate([
                                                            { transform: nextFlipped ? 'rotateY(-90deg) scale(1.02)' : 'rotateY(90deg) scale(1.02)', offset: 0 },
                                                            { transform: 'rotateY(0deg) scale(1)', offset: 1 }
                                                        ], {
                                                            duration: 320,
                                                            easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                                                            fill: 'forwards'
                                                        });

                                                        secondHalf.onfinish = finalizeFlip;
                                                        secondHalf.oncancel = function () {
                                                            card.dataset.flipAnimating = '0';
                                                        };
                                                    };

                                                    firstHalf.oncancel = function () {
                                                        card.dataset.flipAnimating = '0';
                                                    };
                                                    return;
                                                }

                                                finalizeFlip();
                                            };

                                            card.addEventListener('click', function (event) {
                                                if (event.target.closest('.cms-preview-card-actions')) return;
                                                playFlip(card.dataset.flipped !== '1');
                                            });

                                            card.addEventListener('keydown', function (event) {
                                                if (event.key === 'Enter' || event.key === ' ') {
                                                    event.preventDefault();
                                                    playFlip(card.dataset.flipped !== '1');
                                                }
                                                
                                                if (event.key === 'Escape') {
                                                    if (card.dataset.flipAnimating === '1') return;
                                                    setFlipState(false);
                                                }
                                            });

                                            card.dataset.flipAnimating = '0';
                                            setFlipState(false);
                                        });
                                    }

                                    function initOfficialChartZoom() {
                                        const zoom = document.getElementById('campusOfficialsChartZoom');
                                        const zoomImage = document.getElementById('campusOfficialsChartZoomImage');
                                        const zoomClose = zoom?.querySelector('[data-campus-officials-chart-zoom-close]');
                                        const triggers = document.querySelectorAll('[data-campus-officials-chart-zoom]');
                                        let lastTrigger = null;

                                        if (!(zoom instanceof HTMLElement) || !(zoomImage instanceof HTMLImageElement) || !(zoomClose instanceof HTMLElement) || !triggers.length) {
                                            return;
                                        }

                                        if (zoom.parentElement !== document.body) {
                                            document.body.appendChild(zoom);
                                        }

                                        const openZoom = (trigger) => {
                                            const image = trigger.querySelector('img');
                                            if (!(image instanceof HTMLImageElement) || !image.src) {
                                                return;
                                            }

                                            lastTrigger = trigger;
                                            zoomImage.src = image.src;
                                            zoom.classList.add('is-open');
                                            zoom.setAttribute('aria-hidden', 'false');
                                            document.body.classList.add('about-chart-zoom-open');
                                            zoomClose.focus();
                                        };

                                        const closeZoom = () => {
                                            zoom.classList.remove('is-open');
                                            zoom.setAttribute('aria-hidden', 'true');
                                            zoomImage.removeAttribute('src');
                                            document.body.classList.remove('about-chart-zoom-open');
                                            if (lastTrigger instanceof HTMLElement) {
                                                lastTrigger.focus();
                                            }
                                        };

                                        triggers.forEach(function (trigger) {
                                            trigger.addEventListener('click', function () {
                                                openZoom(trigger);
                                            });

                                            trigger.addEventListener('keydown', function (event) {
                                                if (event.key === 'Enter' || event.key === ' ') {
                                                    event.preventDefault();
                                                    openZoom(trigger);
                                                }
                                            });
                                        });

                                        zoomClose.addEventListener('click', closeZoom);
                                        zoom.addEventListener('click', function (event) {
                                            if (event.target === zoom) {
                                                closeZoom();
                                            }
                                        });

                                        document.addEventListener('keydown', function (event) {
                                            if (event.key === 'Escape' && zoom.classList.contains('is-open')) {
                                                closeZoom();
                                            }
                                        });
                                    }

                                    function initOfficialChartEditor() {
                                        @if($cmsPreview)
                                        const trigger = document.querySelector('[data-about-campus-officials-chart-edit]');
                                        if (!(trigger instanceof HTMLElement)) {
                                            return;
                                        }

                                        trigger.addEventListener('click', function (event) {
                                            event.preventDefault();
                                            event.stopPropagation();
                                            window.parent?.postMessage({
                                                type: 'cms-about-official-chart-edit',
                                                route: 'campus-officials',
                                                label: 'Organizational Structure and Image Uploader',
                                            }, '*');
                                        });
                                        @endif
                                    }

                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', function () {
                                            initOfficialCards();
                                            initOfficialChartZoom();
                                            initOfficialChartEditor();
                                        }, { once: true });
                                    } else {
                                        initOfficialCards();
                                        initOfficialChartZoom();
                                        initOfficialChartEditor();
                                    }
                                })();
                                </script>

                                @elseif($selectedSlug === 'citizens-charter')
                                    <div class="students-contents-head layout-inset" style="padding-top: 30px;">
                                        <p class="section-tag">CONTENTS</p>
                                        <h2>{{ $selectedSection['title'] ?? "Citizen's Charter" }}</h2>
                                        @if(!empty($selectedSection['lead']))
                                            <div class="students-contents-description students-rich-copy">
                                                <p>{{ $selectedSection['lead'] }}</p>
                                            </div>
                                        @endif
                                        @if(!empty($selectedSection['body_text']))
                                            <div class="students-contents-description students-rich-copy">
                                                {!! \App\Support\RichText::sanitize($selectedSection['body_text'] ?? '') !!}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="students-contents-inner">
                                        <nav class="students-cards" aria-label="Citizen's Charter Services">
                                        @if($cmsPreview)
                                            <article class="students-card students-card-add" data-about-service-add tabindex="0" role="button" aria-label="Add service">
                                                <div class="students-card-inner">
                                                    <div class="students-card-front students-card-front-add">
                                                        <div class="students-card-add-inner">
                                                            <span class="students-card-add-plus" aria-hidden="true">+</span>
                                                            <p class="students-card-add-label">Add Service</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @endif

                                        @foreach($selectedSection['services'] ?? [] as $index => $service)
                                            @php
                                                $serviceTitle = trim((string) ($service['title'] ?? ''));
                                                $serviceImage = trim((string) ($service['image'] ?? ''));
                                                $serviceLink = trim((string) ($service['link'] ?? ''));
                                            @endphp

                                            @if($cmsPreview)
                                                <article
                                                    class="students-card cms-preview-editable"
                                                    data-cms-section="citizens-charter"
                                                    data-cms-section-label="Citizen's Charter"
                                                    data-cms-editable-type="card"
                                                    data-cms-editable-index="{{ $index }}"
                                                    data-cms-trigger-edit="citizens-charter"
                                                >
                                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                                        <button type="button" class="cms-preview-card-action" data-about-service-edit aria-label="Edit service">Edit</button>
                                                        <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-about-service-delete aria-label="Delete service">Delete</button>
                                                    </div>
                                            @elseif($serviceLink !== '')
                                                <a href="{{ $serviceLink }}" target="_blank" rel="noopener noreferrer" class="students-card">
                                            @else
                                                <article class="students-card">
                                            @endif

                                            <div class="students-card-inner">
                                                <div class="students-card-front">
                                                    <img src="{{ \App\Support\AboutCmsContent::resolveImagePath($serviceImage !== '' ? $serviceImage : null, 'assets/static_img/pupillar.jpeg') }}" alt="Service image">
                                                    <div class="students-card-copy">
                                                        <h3>{{ $serviceTitle !== '' ? $serviceTitle : 'Service' }}</h3>
                                                    </div>
                                                </div>
                                                <div class="students-card-back">
                                                    <div class="students-card-overlay-copy">
                                                        <h3>{{ $serviceTitle !== '' ? $serviceTitle : 'Service' }}</h3>
                                                        <div class="students-card-description">{!! \App\Support\RichText::sanitize($service['description'] ?? '') !!}</div>
                                                    </div>
                                                    <span class="students-card-action">{{ $serviceLink !== '' ? 'Open link' : 'Update soon' }}</span>
                                                </div>
                                            </div>

                                            @if($cmsPreview)
                                                </article>
                                            @elseif($serviceLink !== '')
                                                </a>
                                            @else
                                                </article>
                                            @endif
                                        @endforeach
                                        </nav>
                                    </div>
                                  @endif
                                </div>{{-- end .about-detail-body --}}
                            </div>{{-- end [data-cms-boundary] --}}
                        </article>
                    @endif
                </section>
            @endif
</section>{{-- /.about-shell.page-shell --}}

    </main>

    @unless($cmsPreview)
        <pup-footer></pup-footer>
    @endunless

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const galleries = document.querySelectorAll('[data-ls-gallery]');
            if (!galleries.length) {
                return;
            }

            const isCmsPreview = document.body.hasAttribute('data-cms-preview');

            galleries.forEach((gallery) => {
                const triggers = Array.from(gallery.querySelectorAll('[data-ls-seal-trigger]'));
                const panels = Array.from(gallery.querySelectorAll('[data-ls-seal-panel]'));
                const emptyState = gallery.querySelector('[data-ls-seal-empty]');

                const showPanel = (sealId) => {
                    let hasActive = false;

                    triggers.forEach((trigger) => {
                        const isActive = trigger.getAttribute('data-seal-id') === sealId;
                        trigger.classList.toggle('is-active', isActive);
                        trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                    });

                    panels.forEach((panel) => {
                        const isActive = panel.getAttribute('data-ls-seal-panel') === sealId;
                        panel.hidden = !isActive;
                        panel.classList.toggle('is-active', isActive);
                        hasActive = hasActive || isActive;
                    });

                    if (emptyState) {
                        emptyState.hidden = hasActive;
                    }
                };

                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', (event) => {
                        if (isCmsPreview) {
                            return;
                        }

                        const sealId = trigger.getAttribute('data-seal-id') || '';
                        if (!sealId) {
                            return;
                        }

                        showPanel(sealId);
                        event.preventDefault();
                    });
                });
            });
        });
    </script>

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

            .about-shell > .about-sections {
                padding-top: clamp(10px, 1vw, 16px) !important;
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

            .history-story--vision,
            .history-story--vision .history-story-inner,
            .history-story--vision .history-timeline-container,
            .history-story--vision .history-timeline-shell,
            .history-story--vision .about-detail-body--vision {
                width: 100% !important;
                max-width: 100% !important;
                margin-right: 0 !important;
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }

            .about-sections > .history-story.history-story--vision {
                padding: 0 !important;
                margin: 0 !important;
            }

            .history-story--vision .history-story-inner {
                gap: 24px !important;
            }

            .history-story--vision .history-timeline-container {
                padding: 0 !important;
                overflow: hidden !important;
            }

            .history-story--vision .history-timeline-shell {
                gap: 0 !important;
            }

            .history-story--vision .history-page-header--vision {
                padding: 0 24px !important;
            }

            .history-story--vision .about-detail-body--vision {
                margin-top: 0 !important;
                padding: 0 24px !important;
            }

            .history-story--vision .about-vision-content--cms,
            .history-story--vision .about-vision-extension-grid {
                margin-bottom: 0 !important;
            }

            .about-values-band.cms-preview-editable {
                width: calc(100% + 48px) !important;
                max-width: calc(100% + 48px) !important;
                margin-left: -24px !important;
                margin-right: -24px !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                border-radius: 0 !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                filter: none !important;
                z-index: 2;
            }

            .about-values-band.cms-preview-editable > [data-cms-boundary] {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }

            .about-values-band.cms-preview-editable .about-values-grid {
                width: 100% !important;
                max-width: 100% !important;
            }

            .about-values-band.cms-preview-editable .about-value-card:last-child {
                border-right: none !important;
            }

            .history-story--vision .about-values-band.cms-preview-editable {
                box-shadow: 0 22px 40px rgba(79, 9, 12, 0.18) !important;
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

            .contents-strip.cms-preview-editable .contents-card,
            .contents-strip.cms-preview-editable .contents-card:hover,
            .contents-strip.cms-preview-editable .contents-card:focus-visible,
            .contents-strip.cms-preview-editable .contents-card.active {
                transform: none !important;
                transition: none !important;
                box-shadow: 0 16px 34px rgba(77, 18, 18, 0.12);
            }

            .contents-strip.cms-preview-editable .contents-card-inner,
            .contents-strip.cms-preview-editable .contents-card-front,
            .contents-strip.cms-preview-editable .contents-card-back,
            .contents-strip.cms-preview-editable .contents-card-front img,
            .contents-strip.cms-preview-editable .contents-card-copy,
            .contents-strip.cms-preview-editable .contents-card-overlay-copy,
            .contents-strip.cms-preview-editable .contents-card-action {
                transition: none !important;
            }

            .contents-strip.cms-preview-editable .contents-card-back {
                opacity: 0 !important;
                transform: translateY(100%) !important;
                pointer-events: none !important;
            }

            .contents-strip.cms-preview-editable .contents-card-front img {
                transform: none !important;
                filter: none !important;
            }

            .contents-strip.cms-preview-editable .contents-card-overlay-copy,
            .contents-strip.cms-preview-editable .contents-card-action {
                opacity: 0 !important;
                transform: translateY(18px) !important;
            }

            .contents-strip.cms-preview-editable .contents-card-copy {
                opacity: 1 !important;
            }

            .contents-strip.cms-preview-editable .card_without_section .contents-card-copy {
                min-height: 74px;
                padding: 10px 16px 12px;
            }

            .contents-strip.cms-preview-editable [data-about-contents-card] {
                position: relative;
                cursor: default;
                isolation: isolate;
            }

            .contents-strip.cms-preview-editable [data-about-contents-card]::after {
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

            .contents-strip.cms-preview-editable [data-about-contents-card]:hover,
            .contents-strip.cms-preview-editable [data-about-contents-card]:focus-within {
                transform: none !important;
                filter: none !important;
                box-shadow: 0 16px 34px rgba(77, 18, 18, 0.12) !important;
            }

            .contents-strip.cms-preview-editable [data-about-contents-card]:hover::after,
            .contents-strip.cms-preview-editable [data-about-contents-card]:focus-within::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .contents-strip.cms-preview-editable [data-about-contents-card]:hover .contents-card-back,
            .contents-strip.cms-preview-editable [data-about-contents-card]:focus-within .contents-card-back {
                opacity: 0 !important;
                transform: translateY(100%) !important;
            }

            .contents-strip.cms-preview-editable [data-about-contents-card]:hover .contents-card-overlay-copy,
            .contents-strip.cms-preview-editable [data-about-contents-card]:hover .contents-card-action,
            .contents-strip.cms-preview-editable [data-about-contents-card]:focus-within .contents-card-overlay-copy,
            .contents-strip.cms-preview-editable [data-about-contents-card]:focus-within .contents-card-action {
                opacity: 0 !important;
                transform: translateY(18px) !important;
            }

            .contents-strip.cms-preview-editable [data-about-contents-card]:hover .contents-card-front img,
            .contents-strip.cms-preview-editable [data-about-contents-card]:focus-within .contents-card-front img {
                transform: none !important;
                filter: none !important;
            }

            [data-about-history-card] .history-timeline-card {
                cursor: pointer;
                isolation: isolate;
            }

            [data-about-strategic-goal-card] {
                position: relative;
                cursor: pointer;
                isolation: isolate;
            }

            [data-about-strategic-goal-card]::after {
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

            [data-about-strategic-goal-card]:hover::after,
            [data-about-strategic-goal-card]:focus-within::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            [data-about-history-card] .history-timeline-card::after {
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

            [data-about-history-card]:hover .history-timeline-card::after,
            [data-about-history-card]:focus-within .history-timeline-card::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .official-card.cms-preview-editable-card .official-card-inner::before {
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
                background: none;
                padding: 0;
                -webkit-mask: none;
                mask: none;
            }

            .official-card.cms-preview-editable-card:hover .official-card-inner::before,
            .official-card.cms-preview-editable-card:focus-within .official-card-inner::before {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .official-card--add {
                display: block;
                min-height: 100%;
                padding: 0;
                border: 0;
                cursor: pointer;
                background: transparent;
                transform: none !important;
                box-shadow: none !important;
            }

            .official-card--add:hover {
                transform: none !important;
                box-shadow: none !important;
            }

            .official-card--add .official-card-add-inner {
                display: flex;
                min-height: 360px;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 14px;
                text-align: center;
                padding: 36px 24px;
                width: 100%;
                border-radius: 26px;
                border: 2px dashed rgba(127, 17, 19, 0.2);
                background: linear-gradient(160deg, rgba(255,255,255,.96) 0%, rgba(250,243,236,.9) 100%);
                color: #7f1113;
                box-shadow: none;
                transition: none;
            }

            .official-card--add:hover .official-card-add-inner,
            .official-card--add:focus-visible .official-card-add-inner {
                border-color: rgba(127, 17, 19, 0.2);
                box-shadow: none;
            }

            .official-card-add-plus {
                font-size: clamp(3rem, 6vw, 4.5rem);
                line-height: 1;
                font-weight: 500;
            }

            .official-card-add-label {
                margin: 0;
                font-family: "Poppins", sans-serif;
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: .04em;
            }

            .official-card--add:focus-visible {
                outline: 3px solid rgba(242,201,76,.95);
                outline-offset: 3px;
            }

            .history-story > .cms-preview-chip {
                display: none !important;
            }

            .cms-preview-card-actions {
                position: absolute;
                top: 14px;
                right: 14px;
                z-index: 12;
                display: flex;
                gap: 8px;
            }

            .cms-preview-card-action {
                border: none;
                border-radius: 12px;
                padding: 0 12px;
                min-width: 64px;
                height: 36px;
                background: rgba(127, 17, 19, 0.92);
                color: #fffaf4;
                display: flex !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
                cursor: pointer;
                font-size: 0.78rem;
                font-weight: 700;
            }

            .cms-preview-card-action-delete {
                background: rgba(92, 0, 0, 0.96);
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
                display: none !important;
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
                    const previewRoute = section === 'hero' || section === 'intro' || section === 'contents'
                        ? 'overview'
                        : (section === 'vision-mission-header' || section === 'vision-statement' || section === 'mission-statement' || section === 'vision-mission-statements' || section === 'strategic-goals' || section === 'core-values'
                            ? 'vision-and-mission'
                            : section);
                    window.parent?.postMessage({
                        type: 'cms-about-edit',
                        section: section,
                        label: label || section,
                        route: previewRoute,
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

                    if (section === 'history') {
                        return;
                    }

                    const openEditor = (event) => {
                        if (event.target.closest('[data-about-card-edit], [data-about-card-delete], [data-about-contents-card], [data-about-history-card], [data-about-history-edit], [data-about-section-card], [data-about-section-card-edit], [data-about-section-card-delete], [data-about-strategic-goal-card], [data-about-strategic-goal-edit], [data-about-plan-priority-card], [data-about-plan-priority-edit], [data-about-plan-priority-delete], [data-about-plan-priority-add], [data-about-official-card-add], [data-about-seal-card-add]')) {
                            return;
                        }

                        if (event.target.closest('[data-about-preview-nav]')) {
                            return;
                        }

                        if (event.target.closest('a[href]') && !event.target.closest('[data-cms-edit-trigger]')) {
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
                    const officialChartEditTrigger = event.target.closest('[data-about-campus-officials-chart-edit]');
                    if (officialChartEditTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-official-chart-edit',
                            route: 'campus-officials',
                            label: 'Organizational Structure and Image Uploader',
                        }, '*');
                        return;
                    }

                    const historyEditTrigger = event.target.closest('[data-about-history-edit]');
                    if (historyEditTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = historyEditTrigger.closest('[data-about-history-card]');
                        window.parent?.postMessage({
                            type: 'cms-about-history-card-edit',
                            index: card?.getAttribute('data-about-history-index') || '',
                            label: card?.getAttribute('data-about-history-label') || 'History milestone',
                            route: 'history',
                        }, '*');
                        return;
                    }

                    const historyCard = event.target.closest('[data-about-history-card]');
                    if (historyCard) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-history-card-edit',
                            index: historyCard.getAttribute('data-about-history-index') || '',
                            label: historyCard.getAttribute('data-about-history-label') || 'History milestone',
                            route: 'history',
                        }, '*');
                        return;
                    }

                    const strategicGoalEditTrigger = event.target.closest('[data-about-strategic-goal-edit]');
                    if (strategicGoalEditTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const pillarCard = strategicGoalEditTrigger.closest('[data-about-strategic-goal-card]');
                        window.parent?.postMessage({
                            type: 'cms-about-strategic-goal-edit',
                            index: pillarCard?.getAttribute('data-about-strategic-goal-index') || '',
                            label: pillarCard?.getAttribute('data-about-strategic-goal-label') || 'Strategic goal pillar',
                            route: 'vision-and-mission',
                        }, '*');
                        return;
                    }

                    const strategicGoalCard = event.target.closest('[data-about-strategic-goal-card]');
                    if (strategicGoalCard && !event.target.closest('.about-goal-pillar-summary')) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-strategic-goal-edit',
                            index: strategicGoalCard.getAttribute('data-about-strategic-goal-index') || '',
                            label: strategicGoalCard.getAttribute('data-about-strategic-goal-label') || 'Strategic goal pillar',
                            route: 'vision-and-mission',
                        }, '*');
                        return;
                    }

                    const sectionCardEditTrigger = event.target.closest('[data-about-section-card-edit]');
                    if (sectionCardEditTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = sectionCardEditTrigger.closest('[data-about-section-card]');
                        const route = card?.getAttribute('data-about-section-card-route') || '';
                        const section = card?.getAttribute('data-about-section-card-section') || '';
                        const index = card?.getAttribute('data-about-section-card-index') || '';
                        const label = card?.getAttribute('data-about-section-card-label') || 'About card';

                        if (section === 'campus-officials') {
                            window.parent?.postMessage({
                                type: 'cms-about-official-card-edit',
                                index: index,
                                label: label,
                                route: route || 'campus-officials',
                            }, '*');
                            return;
                        }

                        if (section === 'logo-and-symbols') {
                            window.parent?.postMessage({
                                type: 'cms-about-seal-card-edit',
                                index: index,
                                label: label,
                                route: route || 'logo-and-symbols',
                            }, '*');
                            return;
                        }
                    }

                    const sectionCardDeleteTrigger = event.target.closest('[data-about-section-card-delete]');
                    if (sectionCardDeleteTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = sectionCardDeleteTrigger.closest('[data-about-section-card]');
                        const route = card?.getAttribute('data-about-section-card-route') || '';
                        const section = card?.getAttribute('data-about-section-card-section') || '';
                        const index = card?.getAttribute('data-about-section-card-index') || '';
                        const label = card?.getAttribute('data-about-section-card-label') || 'Campus official';

                        if (section === 'campus-officials') {
                            window.parent?.postMessage({
                                type: 'cms-about-official-card-delete',
                                index: index,
                                label: label,
                                route: route || 'campus-officials',
                            }, '*');
                            return;
                        }

                        if (section === 'logo-and-symbols') {
                            window.parent?.postMessage({
                                type: 'cms-about-seal-card-delete',
                                index: index,
                                label: label,
                                route: route || 'logo-and-symbols',
                            }, '*');
                            return;
                        }
                    }

                    const sectionCard = event.target.closest('[data-about-section-card]');
                    if (sectionCard) {
                        const section = sectionCard.getAttribute('data-about-section-card-section') || '';
                        if (section === 'campus-officials') {
                            event.preventDefault();
                            event.stopPropagation();
                            window.parent?.postMessage({
                                type: 'cms-about-official-card-edit',
                                index: sectionCard.getAttribute('data-about-section-card-index') || '',
                                label: sectionCard.getAttribute('data-about-section-card-label') || 'Campus official',
                                route: sectionCard.getAttribute('data-about-section-card-route') || 'campus-officials',
                            }, '*');
                            return;
                        }

                        if (section === 'logo-and-symbols') {
                            event.preventDefault();
                            event.stopPropagation();
                            window.parent?.postMessage({
                                type: 'cms-about-seal-card-edit',
                                index: sectionCard.getAttribute('data-about-section-card-index') || '',
                                label: sectionCard.getAttribute('data-about-section-card-label') || 'Seal',
                                route: sectionCard.getAttribute('data-about-section-card-route') || 'logo-and-symbols',
                            }, '*');
                            return;
                        }
                    }

                    const addOfficialCardTrigger = event.target.closest('[data-about-official-card-add]');
                    if (addOfficialCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-official-card-add',
                            route: 'campus-officials',
                            label: 'Add campus official',
                        }, '*');
                        return;
                    }

                    const addSealCardTrigger = event.target.closest('[data-about-seal-card-add]');
                    if (addSealCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-seal-card-add',
                            route: 'logo-and-symbols',
                            label: 'Add seal',
                        }, '*');
                        return;
                    }

                    const addPlanPriorityTrigger = event.target.closest('[data-about-plan-priority-add]');
                    if (addPlanPriorityTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-plan-priority-add',
                            route: 'strategic-development-plan',
                            label: 'Add development priority',
                        }, '*');
                        return;
                    }

                    const planPriorityEditTrigger = event.target.closest('[data-about-plan-priority-edit]');
                    if (planPriorityEditTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = planPriorityEditTrigger.closest('[data-about-plan-priority-card]');
                        window.parent?.postMessage({
                            type: 'cms-about-plan-priority-edit',
                            index: card?.getAttribute('data-about-plan-priority-index') || '',
                            label: card?.getAttribute('data-about-plan-priority-label') || 'Development priority',
                            route: card?.getAttribute('data-about-plan-priority-route') || 'strategic-development-plan',
                        }, '*');
                        return;
                    }

                    const planPriorityDeleteTrigger = event.target.closest('[data-about-plan-priority-delete]');
                    if (planPriorityDeleteTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = planPriorityDeleteTrigger.closest('[data-about-plan-priority-card]');
                        window.parent?.postMessage({
                            type: 'cms-about-plan-priority-delete',
                            index: card?.getAttribute('data-about-plan-priority-index') || '',
                            label: card?.getAttribute('data-about-plan-priority-label') || 'Development priority',
                            route: card?.getAttribute('data-about-plan-priority-route') || 'strategic-development-plan',
                        }, '*');
                        return;
                    }

                    const planPriorityCard = event.target.closest('[data-about-plan-priority-card]');
                    if (planPriorityCard) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-plan-priority-edit',
                            index: planPriorityCard.getAttribute('data-about-plan-priority-index') || '',
                            label: planPriorityCard.getAttribute('data-about-plan-priority-label') || 'Development priority',
                            route: planPriorityCard.getAttribute('data-about-plan-priority-route') || 'strategic-development-plan',
                        }, '*');
                        return;
                    }

                    const deleteCardTrigger = event.target.closest('[data-about-card-delete]');
                    if (deleteCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = deleteCardTrigger.closest('[data-about-contents-card]');
                        window.parent?.postMessage({
                            type: 'cms-about-contents-card-delete',
                            slug: card?.getAttribute('data-about-contents-slug') || '',
                            label: card?.getAttribute('data-about-contents-label') || 'About card',
                        }, '*');
                        return;
                    }

                    const addServiceCardTrigger = event.target.closest('[data-about-service-add]');
                    if (addServiceCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-about-service-card-add',
                            route: 'citizens-charter',
                            label: 'Add Citizen\'s Charter service',
                        }, '*');
                        return;
                    }

                    const editServiceCardTrigger = event.target.closest('[data-about-service-edit]');
                    if (editServiceCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = editServiceCardTrigger.closest('.students-card');
                        window.parent?.postMessage({
                            type: 'cms-about-service-card-edit',
                            index: card?.getAttribute('data-cms-editable-index') || '',
                            label: 'Edit Citizen\'s Charter service',
                            route: 'citizens-charter',
                        }, '*');
                        return;
                    }

                    const deleteServiceCardTrigger = event.target.closest('[data-about-service-delete]');
                    if (deleteServiceCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = deleteServiceCardTrigger.closest('.students-card');
                        window.parent?.postMessage({
                            type: 'cms-about-service-card-delete',
                            index: card?.getAttribute('data-cms-editable-index') || '',
                            label: 'Citizen\'s Charter service',
                        }, '*');
                        return;
                    }

                    const editCardTrigger = event.target.closest('[data-about-card-edit]');
                    if (editCardTrigger) {
                        event.preventDefault();
                        event.stopPropagation();
                        const card = editCardTrigger.closest('[data-about-contents-card]');
                        window.parent?.postMessage({
                            type: 'cms-about-contents-card-edit',
                            slug: card?.getAttribute('data-about-contents-slug') || '',
                            label: card?.getAttribute('data-about-contents-label') || 'About card',
                            route: 'overview',
                        }, '*');
                        return;
                    }

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
