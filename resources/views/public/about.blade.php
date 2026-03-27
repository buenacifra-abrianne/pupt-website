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
        $homeCms = \App\Support\HomeCmsContent::fromInput($homeCms ?? [], null);
        $campusStoryDescription = <<<'TEXT'
The Polytechnic University of the Philippines (PUP) is a government educational institution governed by Republic Act Number 8292 known as the Higher Education Modernization Act of 1997, and its Implementing Rules and Regulations contained in the Commission on Higher Education Memorandum Circular No. 4, series 1997. PUP is one of the country's highly competent educational institutions. The PUP Community is composed of the Board of Regents, University Officials, Administrative and Academic Personnel, Students, various Organizations, and the Alumni.

Governance of PUP is vested upon the Board of Regents, which exercises policy-making functions to carry out the mission and programs of the University by virtue of RA 8292 granted by the Commission on Higher Education. The University is administered by an appointed President by virtue of RA 8292 and is assisted by an Executive Vice President and the Vice Presidents for Academic Affairs, Student Services, Administration, Research, Extension and Development, and Finance.
TEXT;
        $historyMovedParagraphs = [
            'Government and University officials envisioned PUP Taguig to become the main source of commercial and industrial managers and employers that will fill in the job vacancies in the area, particularly now that the region is fast becoming an industrial zone that can employ thousands of workers.',
            'Twenty years ago, upon the request of then Philippine College of Commerce President, Dr. Nemesio Prudente, former President Ferdinand Marcos issued proclamation No. 469, which excluded from the operation of Proclamation No. 423, dated July 12, 1957 a certain portion of land (10 hectares) situated in the Municipality of Taguig for school purposes of the PCC, now Polytechnic University of the Philippines. This proclamation was issued on September 30, 1968.',
        ];
    @endphp

    <pup-header
        data-home="{{ route('public.home') }}"
        data-about="{{ route('public.about') }}"
        data-academics="{{ route('public.academics') }}"
        data-students="{{ route('public.students') }}"
        data-news-events="{{ route('public.events') }}"
        data-research="{{ route('public.research') }}"
        data-assets="{{ asset('assets') }}"
    ></pup-header>

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
                                <h2>ABOUT THE CAMPUS</h2>
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
                    <a href="{{ route('public.about') }}">About</a>
                    <span>&gt;</span>
                    <strong>{{ $selectedSection['label'] }}</strong>
                @else
                    <strong>About</strong>
                @endif
            </nav>

            @unless($selectedSection)
                <section class="about-intro reveal">
                    <div class="campus-story-card">
                        <div class="campus-story-layout">
                            <div class="campus-story-copy">
                                <p class="campus-story-tag">Campus Story</p>
                                <h2>{{ $homeCms['campus_title'] ?? 'PUP Taguig Campus' }}</h2>
                            </div>

                            <div class="campus-story-visual">
                                <img src="{{ asset('assets/static_img/about-pup.png') }}" alt="PUP Taguig Campus">
                            </div>

                            <div class="campus-story-description">
                                <p>{!! nl2br(e($campusStoryDescription)) !!}</p>
                            </div>
                        </div>
                    </div>
                </section>
            @endunless

            @unless($selectedSection)
                <section class="contents-strip reveal">
                    <div class="contents-strip-head">
                        <p class="section-tag">Contents</p>
                        <h2>All about the campus</h2>
                    </div>

                    <nav class="contents-cards" aria-label="About page contents">
                        @foreach($sections as $section)
                            <a
                                href="{{ route('public.about.section', $section['slug']) }}"
                                class="contents-card"
                            >
                                <div class="contents-card-inner">
                                    <div class="contents-card-front">
                                        <img src="{{ asset('assets/static_img/' . ($section['image'] ?? 'pupillar.jpeg')) }}" alt="{{ $section['label'] }}">
                                        <div class="contents-card-copy">
                                            <span class="contents-card-number">Section {{ $section['number'] }}</span>
                                            <h3>{{ $section['label'] }}</h3>
                                        </div>
                                    </div>

                                    <div class="contents-card-back">
                                        <div class="contents-card-overlay-copy">
                                            <span class="contents-card-number">Section {{ $section['number'] }}</span>
                                            <h3>{{ $section['label'] }}</h3>
                                            <p>{{ $section['summary'] }}</p>
                                        </div>
                                        <span class="contents-card-action">See more</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </nav>
                </section>
            @endunless

            @if($selectedSection)
                <section class="about-sections">
                    <article class="about-section-card reveal">
                        <div class="section-heading-row">
                            <div>
                                <p class="section-tag">Section {{ $selectedSection['number'] }}</p>
                                <h2>{{ $selectedSection['label'] }}</h2>
                            </div>

                            @if($selectedSection['slug'] === 'maps')
                                <a href="https://maps.app.goo.gl/RDAwxBvDzyGzUbVN7" target="_blank" rel="noopener noreferrer" class="section-link">Open Map</a>
                            @endif
                        </div>

                        @if($selectedSection['slug'] === 'history')
                            <div class="section-copy section-copy-intro">
                                <p>This institution started as the Manila Business School (MBS), founded on October 19, 1904, as part of the city school system to respond to the demand for trained government personnel and private-sector workers.</p>
                                <p>It later evolved into the Philippine School of Commerce, then the Philippine College of Commerce, before becoming the Polytechnic University of the Philippines through Presidential Decree No. 1341 on April 1, 1978.</p>
                                @foreach($historyMovedParagraphs as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>

                            <button class="read-more-btn" type="button">Read More</button>
                            <div class="read-more-content">
                                <div id="about_readMore" class="section-copy">
                                    <p>PUP is a public, non-sectarian, non-profit institution of higher learning primarily tasked with harnessing the tremendous human resources potential of the nation by improving the physical, intellectual and material well-being of the individual through higher occupational, technical and professional instruction and training in the applied arts and sciences related to the fields of commerce, business administration, and technology.</p>
                                    <p>The University promotes applied research, advanced studies and progressive leadership in the stated fields. We also offer ladder-type higher vocational, distance learning (open university system), technical and professional programs in the area of business and distributive arts, education and the social sciences related to the fields of commerce, business administration and other polytechnic areas.</p>
                                    <p>Majority of the students belong to the economically challenged level of society. It is the University's commitment to give qualified and talented students access to quality and responsive education to aid them in the achievement of their dreams and improve their lives.</p>
                                </div>
                                <div class="history-gallery">
                                    <img id="about_readMore_img1" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP campus view">
                                    <img id="about_readMore_img2" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP students and campus">
                                </div>
                                <div class="history-feature">
                                    <img id="about_readMore_img3" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP Taguig Campus building">
                                </div>
                            </div>
                        @elseif($selectedSection['slug'] === 'vision-and-mission')
                            <div id="visionMission" class="section-copy">
                                Overview of the university vision and mission.
                            </div>
                        @elseif($selectedSection['slug'] === 'logo-and-symbols')
                            <div id="logoSymbols" class="section-copy">
                                Overview of the university logo and symbols.
                            </div>
                        @elseif($selectedSection['slug'] === 'hymn')
                            <div id="hymn" class="section-copy">
                                Overview of the university hymn.
                            </div>
                        @elseif($selectedSection['slug'] === 'maps')
                            <div id="maps" class="section-copy">
                                Overview of the university location.
                            </div>
                        @elseif($selectedSection['slug'] === 'campus-officials')
                            <div id="campusOfficials" class="section-copy">
                                Overview of the university campus officials.
                            </div>
                        @elseif($selectedSection['slug'] === 'strategic-development-plan')
                            <div id="strategicPlan" class="section-copy">
                                Overview of the university strategic development plan.
                            </div>
                        @elseif($selectedSection['slug'] === 'university-calendar')
                            <div id="universityCalendar" class="section-copy">
                                Overview of the university calendar.
                            </div>
                        @endif
                    </article>
                </section>
            @endif
        </section>
    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
