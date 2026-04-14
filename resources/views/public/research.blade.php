<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research & Extension - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/research.css') }}?v={{ filemtime(public_path('assets/css/research.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    <pup-header
        data-home="{{ route('public.home') }}"
        data-about="{{ route('public.about') }}"
        data-academics="{{ route('public.academics') }}"
        data-students="{{ route('public.students') }}"
        data-news-events="{{ route('public.events') }}"
        data-research="{{ route('public.research') }}"
        data-assets="{{ asset('assets') }}"
    ></pup-header>

    <main class="main-content research-review-page">

        {{-- Hero --}}
        <section class="hero-shell">
            <section class="carousel-section">
                <div class="carousel full-carousel">
                    <div class="carousel-stage">
                        <div class="carousel-slide active">
                            <div class="carousel-split" aria-hidden="true">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                            </div>
                            <div class="carousel-caption">
                                <h2>Research &amp; Extension</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        {{-- Breadcrumb --}}
        <section class="academic-shell page-shell research-page-shell">
            <nav class="research-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Research &amp; Extension</strong>
            </nav>

            <section class="research-page-intro reveal">
                <div class="research-story-card">
                    <div class="research-story-layout">
                        <div class="research-story-copy">
                            <p class="research-story-tag">Research &amp; Extension</p>
                            <h1>Research and Extension</h1>
                        </div>

                        <p class="research-story-description">
                            Discover the campus initiatives, scholarly work, and community-centered extension programs
                            that connect PUP Taguig with industry, partner institutions, and the wider public.
                        </p>
                    </div>
                </div>
            </section>
        </section>

        {{-- Contents Strip --}}
        <section class="students-contents-strip reveal">
            <div class="students-contents-inner">
                <div class="students-contents-head">
                    <p class="section-tag">Contents</p>
                </div>

                <nav class="students-cards" aria-label="Research and Extension services">

                    {{-- Research --}}
                    <a href="#research" class="students-card">
                        <div class="students-card-inner">
                            <div class="students-card-front">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="Research">
                                <div class="students-card-copy">
                                    <h3>Research</h3>
                                </div>
                            </div>
                            <div class="students-card-back">
                                <div class="students-card-overlay-copy">
                                    <h3>Research</h3>
                                    <p>Explore research initiatives, publications, and scholarly works conducted at PUP Taguig Campus.</p>
                                </div>
                                <span class="students-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    {{-- Extension --}}
                    <a href="#extension" class="students-card">
                        <div class="students-card-inner">
                            <div class="students-card-front">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="Extension">
                                <div class="students-card-copy">
                                    <h3>Extension</h3>
                                </div>
                            </div>
                            <div class="students-card-back">
                                <div class="students-card-overlay-copy">
                                    <h3>Extension</h3>
                                    <p>Community outreach and extension programs that connect PUP Taguig with the wider community.</p>
                                </div>
                                <span class="students-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                </nav>
            </div>
        </section>

    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
