<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/news&events.css') }}?v={{ filemtime(public_path('assets/css/news&events.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v={{ filemtime(public_path('assets/css/about.css')) }}">
    <link rel="icon" type="image/png" href="../assets/static_img/logo.png" sizes="32x32">
</head>
<body>
<!-- Header -->
<pup-header
  data-home="{{ route('public.home') }}"
  data-about="{{ route('public.about') }}"
  data-academics="{{ route('public.academics') }}"
  data-students="{{ route('public.students') }}"
  data-news-events="{{ route('public.events') }}"
  data-research="{{ route('public.research') }}"
  data-assets="{{ asset('assets') }}"
></pup-header>

    <!-- Main Content -->
    <main class="main-content">
        <section class="hero-shell">
            <section class="carousel-section">
                <div class="carousel full-carousel">
                    <div class="carousel-stage">
                        <div class="carousel-slide active">
                            <div class="carousel-split" aria-hidden="true">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                            </div>

                            <div class="carousel-caption">
                                <h2>NEWS AND EVENTS</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <section class="about-shell">
            <nav class="about-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                @if($selectedSection)
                    <a href="{{ route('public.event') }}">News</a>
                    <span>&gt;</span>
                    <strong>{{ $selectedSection['label'] }}</strong>
                @else
                    <strong>Events</strong>
                @endif
            </nav>


        {{-- ── FEATURED EVENT ── --}}
        <div id="featuredEventMount" class="ne-featured reveal">
            <div class="ne-featured-img">
                <img src="../assets/static_img/pupillar.jpeg" alt="Featured event">
                <span class="ne-featured-badge">Featured Event</span>
            </div>
            <div class="ne-featured-body">
                <span class="ne-tag">Upcoming</span>
                <h2 class="ne-featured-title">Event Title</h2>
                <p class="ne-featured-meta">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <rect x="1" y="3" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.4"/>
                        <path d="M1 7h14" stroke="currentColor" stroke-width="1.4"/>
                        <path d="M5 1v3M11 1v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                    December 23, 2025 &nbsp;|&nbsp; 8:00 A.M. – 10:00 A.M.
                </p>
                <p class="ne-featured-desc">
                    Brief description of the event goes here. Keep it concise and engaging so readers are drawn to learn more about what's happening.
                </p>
                <a href="#" class="ne-btn-gold">Learn More</a>
            </div>
        </div>

    </main>

    {{-- ── EVENTS (ONGOING + UPCOMING) ── --}}
    <main class="main-content ne-events-main">
        <section class="contents-strip reveal">

                <section class>
                    <div class="contents-strip-head">
                        <p class="section-tag">Scheduled Events</p>
                    </div>

            <div class="ne-events-columns">

                {{-- Ongoing --}}
                <div class="ne-events-col">
                    <h3 class="ne-col-title ne-col-ongoing">
                        <span class="ne-pulse"></span> Ongoing
                    </h3>
                    <div class="ne-event-item ne-ongoing">
                        <span class="ne-event-date">Dec 23</span>
                        <p class="ne-event-name">Ongoing Event Title 1</p>
                    </div>
                    <div class="ne-event-item ne-ongoing">
                        <span class="ne-event-date">Dec 24</span>
                        <p class="ne-event-name">Ongoing Event Title 2</p>
                    </div>
                    <div class="ne-event-item ne-ongoing">
                        <span class="ne-event-date">Dec 25</span>
                        <p class="ne-event-name">Ongoing Event Title 3</p>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="ne-events-divider" aria-hidden="true"></div>

                {{-- Upcoming --}}
                <div class="ne-events-col">
                    <h3 class="ne-col-title ne-col-upcoming">Upcoming</h3>
                    <div class="ne-event-item">
                        <span class="ne-event-date">Dec 26</span>
                        <p class="ne-event-name">Upcoming Event Title 1</p>
                    </div>
                    <div class="ne-event-item">
                        <span class="ne-event-date">Dec 28</span>
                        <p class="ne-event-name">Upcoming Event Title 2</p>
                    </div>
                    <div class="ne-event-item">
                        <span class="ne-event-date">Jan 03</span>
                        <p class="ne-event-name">Upcoming Event Title 3</p>
                    </div>
                </div>

            </div>
        </section>
    </main>

    {{-- ── FILTER BAR ── --}}
    <div class="ne-filter-bar reveal">
        <span class="ne-filter-label">Filter By</span>
        <div class="ne-filters" role="group" aria-label="Filter news and events">
            <button class="ne-filter active" data-filter="all">All</button>
            <button class="ne-filter" data-filter="academic">Academic</button>
            <button class="ne-filter" data-filter="events">Events</button>
            <button class="ne-filter" data-filter="research">Research</button>
            <button class="ne-filter" data-filter="student-life">Student Life</button>
        </div>
    </div>

    {{-- ── CARD GRID ── --}}
    <section class="ne-card-grid" aria-label="News and events articles">

        @php
        $cards = [
            ['tag' => 'Student Life', 'filter' => 'student-life', 'date' => 'December 23, 2025', 'title' => 'Article Title One', 'desc' => 'A short engaging summary of the article content goes here to give readers a preview.', 'loc' => 'Main Campus'],
            ['tag' => 'Academic',     'filter' => 'academic',     'date' => 'December 24, 2025', 'title' => 'Article Title Two', 'desc' => 'A short engaging summary of the article content goes here to give readers a preview.', 'loc' => 'College of Engineering'],
            ['tag' => 'Events',       'filter' => 'events',       'date' => 'December 25, 2025', 'title' => 'Article Title Three', 'desc' => 'A short engaging summary of the article content goes here to give readers a preview.', 'loc' => 'Auditorium'],
            ['tag' => 'Research',     'filter' => 'research',     'date' => 'December 26, 2025', 'title' => 'Article Title Four', 'desc' => 'A short engaging summary of the article content goes here to give readers a preview.', 'loc' => 'Research Center'],
            ['tag' => 'Student Life', 'filter' => 'student-life', 'date' => 'December 28, 2025', 'title' => 'Article Title Five', 'desc' => 'A short engaging summary of the article content goes here to give readers a preview.', 'loc' => 'Gymnasium'],
            ['tag' => 'Academic',     'filter' => 'academic',     'date' => 'January 03, 2026',  'title' => 'Article Title Six', 'desc' => 'A short engaging summary of the article content goes here to give readers a preview.', 'loc' => 'Library'],
        ];
        @endphp

        @foreach($cards as $card)
        <article class="ne-card reveal" data-filter="{{ $card['filter'] }}">
            <div class="ne-card-img">
                <img src="../assets/static_img/pupillar.jpeg" alt="{{ $card['title'] }}" loading="lazy">
                <span class="ne-card-tag">{{ $card['tag'] }}</span>
            </div>
            <div class="ne-card-body">
                <p class="ne-card-date">{{ $card['date'] }}</p>
                <h3 class="ne-card-title">{{ $card['title'] }}</h3>
                <p class="ne-card-desc">{{ $card['desc'] }}</p>
                <hr class="ne-card-rule">
                <div class="ne-card-foot">
                    <span class="ne-card-loc">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M8 1a5 5 0 0 1 5 5c0 3.5-5 9-5 9S3 9.5 3 6a5 5 0 0 1 5-5z" stroke="currentColor" stroke-width="1.4"/>
                            <circle cx="8" cy="6" r="1.5" stroke="currentColor" stroke-width="1.4"/>
                        </svg>
                        {{ $card['loc'] }}
                    </span>
                    <a href="#"
                       class="ne-read-more"
                       data-img="../assets/static_img/pupillar.jpeg"
                       data-tag="{{ $card['tag'] }}"
                       data-date="{{ $card['date'] }}"
                       data-title="{{ $card['title'] }}"
                       data-loc="{{ $card['loc'] }}"
                       data-full="Full article details go here. This area supports long-form content and will scroll inside the modal."
                       aria-label="Read more about {{ $card['title'] }}">
                        Read More
                    </a>
                </div>
            </div>
        </article>
        @endforeach

    </section>

    {{-- ── MODAL ── --}}
    <div class="ne-modal-overlay" id="detailsModal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Article details">
        <div class="ne-modal-card">
            <button class="ne-modal-close" type="button" aria-label="Close">&times;</button>
            <div class="ne-modal-body">
                <div class="ne-modal-img-wrap">
                    <img id="modalImg" src="" alt="">
                </div>
                <div class="ne-modal-content">
                    <span class="ne-tag" id="modalTag"></span>
                    <p class="ne-modal-date" id="modalDate"></p>
                    <h3 class="ne-modal-title" id="modalTitle"></h3>
                    <p class="ne-modal-loc" id="modalLocation"></p>
                    <hr class="ne-modal-rule">
                    <p class="ne-modal-text" id="modalText"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
