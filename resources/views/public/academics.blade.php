<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
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

    <main class="main-content">

        {{-- ===== CAROUSEL ===== --}}
        <section class="hero-shell">
            <section class="carousel-section">
                <div class="carousel full-carousel">
                    <div class="carousel-stage">
                        <div class="carousel-slide active">
                            <div class="carousel-split" aria-hidden="true">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                            </div>
                            <div class="carousel-caption">
                                <h2>ACADEMICS</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

                {{-- ===== MAIN ACADEMIC SHELL (breadcrumb, contents strip, sections) ===== --}}
        <section class="academic-shell">
            <nav class="academic-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                @if($selectedSection)
                    <a href="{{ route('public.academics') }}">Academics</a>
                    <span>&gt;</span>
                    <strong>{{ $selectedSection['label'] }}</strong>
                @else
                    <strong>Academics</strong>
                @endif
            </nav>

            <section class="contents-strip reveal">
                <div class="contents-strip-head">
                    <p class="section-tag">Contents</p>
                </div>

                <nav class="contents-cards" aria-label="Academic page contents">
                    <a
                        href="{{ route('public.academics', ['section' => 'degree-programs']) }}"
                        class="contents-card{{ $selectedSection && $selectedSection['slug'] === 'degree-programs' ? ' active' : '' }}"
                    >
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/' . ($sections[0]['image'] ?? 'pupillar.jpeg')) }}" alt="Degree Programs">
                                <div class="contents-card-copy">
                                    <span class="contents-card-number">Section 1</span>
                                    <h3>Degree Programs</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <span class="contents-card-number">Section 1</span>
                                    <h3>Degree Programs</h3>
                                    <p>Discover a wide range of undergraduate majors and minors designed to prepare you for professional success.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('public.academics', ['section' => 'diploma-programs']) }}"
                        class="contents-card{{ $selectedSection && $selectedSection['slug'] === 'diploma-programs' ? ' active' : '' }}"
                    >
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/' . ($sections[1]['image'] ?? 'pupillar.jpeg')) }}" alt="Diploma Programs">
                                <div class="contents-card-copy">
                                    <span class="contents-card-number">Section 2</span>
                                    <h3>Diploma Programs</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <span class="contents-card-number">Section 2</span>
                                    <h3>Diploma Programs</h3>
                                    <p>Gain practical skills and specialized knowledge through diploma courses tailored for career readiness.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('public.academics', ['section' => 'graduate-programs']) }}"
                        class="contents-card{{ $selectedSection && $selectedSection['slug'] === 'graduate-programs' ? ' active' : '' }}"
                    >
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/' . ($sections[2]['image'] ?? 'pupillar.jpeg')) }}" alt="Graduate Programs">
                                <div class="contents-card-copy">
                                    <span class="contents-card-number">Section 3</span>
                                    <h3>Graduate Programs</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <span class="contents-card-number">Section 3</span>
                                    <h3>Graduate Programs</h3>
                                    <p>Advance your expertise with master's and doctoral programs that foster research, leadership, and innovation.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('public.academics', ['section' => 'pup-iapply']) }}"
                        class="contents-card{{ $selectedSection && $selectedSection['slug'] === 'pup-iapply' ? ' active' : '' }}"
                    >
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/' . ($sections[3]['image'] ?? 'pupillar.jpeg')) }}" alt="PUP iApply">
                                <div class="contents-card-copy">
                                    <span class="contents-card-number">Section 4</span>
                                    <h3>PUP iApply</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <span class="contents-card-number">Section 4</span>
                                    <h3>PUP iApply</h3>
                                    <p>Easily access the university's online application portal to start your academic journey.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('public.academics', ['section' => 'university-calendar']) }}"
                        class="contents-card{{ $selectedSection && $selectedSection['slug'] === 'university-calendar' ? ' active' : '' }}"
                    >
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/' . ($sections[4]['image'] ?? 'pupillar.jpeg')) }}" alt="University Calendar">
                                <div class="contents-card-copy">
                                    <span class="contents-card-number">Section 5</span>
                                    <h3>University Calendar</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <span class="contents-card-number">Section 5</span>
                                    <h3>University Calendar</h3>
                                    <p>Stay updated with important academic schedules, events, and deadlines throughout the school year.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>
                </nav>
            </section>

            @if($selectedSection)
                <section class="academic-sections">
                    <article class="academic-section-card reveal">
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
                                <div id="academic_readMore" class="section-copy">
                                    <p>PUP is a public, non-sectarian, non-profit institution of higher learning primarily tasked with harnessing the tremendous human resources potential of the nation by improving the physical, intellectual and material well-being of the individual through higher occupational, technical and professional instruction and training in the applied arts and sciences related to the fields of commerce, business administration, and technology.</p>
                                    <p>The University promotes applied research, advanced studies and progressive leadership in the stated fields. We also offer ladder-type higher vocational, distance learning (open university system), technical and professional programs in the area of business and distributive arts, education and the social sciences related to the fields of commerce, business administration and other polytechnic areas.</p>
                                    <p>Majority of the students belong to the economically challenged level of society. It is the University's commitment to give qualified and talented students access to quality and responsive education to aid them in the achievement of their dreams and improve their lives.</p>
                                </div>
                                <div class="history-gallery">
                                    <img id="academic_readMore_img1" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP campus view">
                                    <img id="academic_readMore_img2" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP students and campus">
                                </div>
                                <div class="history-feature">
                                    <img id="academic_readMore_img3" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP Taguig Campus building">
                                </div>
                            </div>
                        @elseif($selectedSection['slug'] === 'degree-programs')
                            <div id="degreePrograms" class="section-copy">
                                Overview of degree programs offered by the university.
                            </div>
                        @elseif($selectedSection['slug'] === 'diploma-programs')
                            <div id="diplomaPrograms" class="section-copy">
                                Overview of diploma programs offered by the university.
                            </div>
                        @elseif($selectedSection['slug'] === 'graduate-programs')
                            <div id="graduatePrograms" class="section-copy">
                                Overview of graduate programs offered by the university.
                            </div>
                        @elseif($selectedSection['slug'] === 'pup-iapply')
                            <div id="pupIApply" class="section-copy">
                                Overview of the PUP iApply online application portal.
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

        <section class="academic-intro-text reveal">
            <div class="academic-intro-text-inner">
                <p><strong>Quality and relevant education</strong> that responds to the call of present times in building the <strong>foundations of the future.</strong></p>
                <p>Ranging from high school to doctoral courses, traditional to nontraditional education system, <strong>the University makes it possible</strong> that <strong>deserving individuals can have access</strong> to these academic resources.</p>
                <p>The University has always been making <strong>initiatives to enrich its academic programs</strong> in various fields of study and <strong>implement an educational strategy</strong> designed to provide our students with highly employable, managerial, and entrepreneurial skills in order to make them exceedingly <strong>creative, productive, competitive, and self-reliant</strong>.</p>
            </div>
        </section>

        <section class="academic-features reveal">
            <div class="academic-features-inner">
                <p class="academic-features-eyebrow">What we offer</p>
                <div class="academic-features-grid">
                    <div class="academic-feature-card">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">
                            <span class="academic-feature-dot"></span>QUALITY
                        </h3>
                        <hr class="academic-feature-divider">
                        <p>Being one of the reputable universities in the country, we always make it to a point that the education given to our students meets the standards of quality and excellence.</p>
                    </div>
                    <div class="academic-feature-card">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">
                            <span class="academic-feature-dot"></span>RELEVANT
                        </h3>
                        <hr class="academic-feature-divider">
                        <p>The University, through its various programs, equips its students with learning and skills that are significant and responsive — enabling students to be competitive and very resourceful.</p>
                    </div>
                    <div class="academic-feature-card">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">
                            <span class="academic-feature-dot"></span>FLEXIBLE
                        </h3>
                        <hr class="academic-feature-divider">
                        <p>Programs that adapt to a student's living condition — especially for the working class. Our Open University and distance learning method goes beyond the physical restrictions of a campus.</p>
                    </div>
                    <div class="academic-feature-card">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">
                            <span class="academic-feature-dot"></span>ACCREDITED
                        </h3>
                        <hr class="academic-feature-divider">
                        <p>Most of our academic courses are accredited by the Accrediting Agency of Chartered Colleges and Universities in the Philippines (AACCUP).</p>
                    </div>
                    <div class="academic-feature-card academic-feature-card--wide">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">
                            <span class="academic-feature-dot"></span>AFFORDABLE
                        </h3>
                        <hr class="academic-feature-divider">
                        <p>Practicality without sacrificing quality in education. Having the lowest tuition and fees among universities in the Philippines, one can enroll for less than ₱500 per semester in an undergraduate program.</p>
                    </div>
                </div>
            </div>
        </section>


    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>