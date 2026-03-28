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
        $historyTimeline = [
            [
                'period' => 'JULY 1957 - September 1968',
                'title' => 'School-site reservation established',
                'body' => [
                    'Twenty years ago, upon the request of then Philippine College of Commerce President, Dr. Nemesio Prudente, former President Ferdinand Marcos issued proclamation No. 469, which excluded from the operation of Proclamation No. 423, dated July 12, 1957 a certain portion of land (10 hectares) situated in the Municipality of Taguig for school purposes of the PCC, now Polytechnic University of the Philippines. This proclamation was issued on September 30, 1968.',
                ],
            ],
            [
                'period' => 'September 1972 - June 1975',
                'title' => 'Land dispute and interruption',
                'body' => [
                    'But things didn\'t work right. On September 21, 1972, President Marcos declared Martial Law, and Dr. Prudente had to forcibly vacate the PCC Presidency. Those who succeeded him, however, did nothing about the land. When he resumed his post in 1978, he started reviving PUP\'s right to the land, which, by that time, was already occupied by the Metals Industry Research and Development Center (MIRDC).',
                    'With three congressmen sympathetic to PUP, Prudente wrote President Corazon Aquino about the matter, citing that the 10-hectare land awarded to PCC was reassigned to MIRDC with an assurance from former Sen. Ronaldo Zamora and former MIRDC Director Antonio Arizabal, Jr. that former Pres. Marcos shall issue another Presidential Proclamation excluding 17-hectares from Proclamation No. 423 to be given to MIRDC on June 2, 1975. Unfortunately, the said assurance did not materialize.',
                    'University officials conducted thorough studies and investigation about the matter. MIRDC claimed that it had rights on the land by virtue of an unnumbered Presidential Proclamation dated June 2, 1975. In fact, they had already four laboratory buildings situated at the disputed site since 1975.',
                ],
            ],
            [
                'period' => 'July 1990 - September 1990',
                'title' => 'Claim reasserted and groundbreaking held',
                'body' => [
                    'On July 10, 1990, Dr. Prudente once more wrote Malacañang Palace, asking if Presidential Proclamation No. 469 had been revoked by any another Presidential issuances. Mrs. Aurora Aquino, Director IV, replied that the records available in that office failed to show that Proclamation No. 469 was ever revoked.',
                    'Thus, on September 8, 1990, PUP personnel surreptitiously entered the site premises and started the construction of a building, inspite of a notice nailed to the fence which read: NO TREEPASSING, (BAWAL PUMASOK) MIRDC PROPERTY, All Trespassers will be prosecuted. On Sept. 9, 1990 the Ground-Breaking Ceremony of PUP Taguig was held at 9:00 A.M..with Sec. Guillermo Carague, Sec. Ceferino Follosco, Sen. Edgardo Angara, Gen. Marino Filart, Congressmen Dante Tinga, Rolando Andaya, and Carlos Padilla, Mayors de Guzman, Capco, Bunye, and Ferrer, among others as sponsors. This move of the PUP Administration caused the MIRDC to file a suit against PUP for entering its premises without seeking permit and for illegally constructing two buildings within their area. Consequently, the office of the Solicitor General issued a CEASE AND DESIST ORDER was issued by the Office of the President on Sept. 27, 1990.',
                ],
            ],
            [
                'period' => 'October 1990 - February 1991',
                'title' => 'Certification and legal ruling',
                'body' => [
                    'Another CEASE AND DESIST ORDER on Oct. 1, 1990. PUP officials tried to settle the matter amicably but the MIRDC refused to accept the offer. Instead, a winner-take-all deal was agreed upon.',
                    'On Oct. 19, 1990, the Office of the Press Secretary (National Printing Office) through Mr. Heriberto Bacalla, the Chief of the Official Gazette Publication, certified that the said office had never received nor accepted any unnumbered document about the PUP land from the Office of the Malacanang Records for publications in the Official Gazette. Thus, the alleged unnumbered proclamation entitled, Revoking Proclamation No. 469, dated Sept. 30, 1968, which established the school site reservation of the PUP in the Municipality of Taguig and reserving the land embraced therein for MIRDC, was not received nor published in the Official Gazette. This development opened the case with MIRDC as petitioner and PUP as respondent with a powerhouse legal counsel, which included lawyers Ernesto Fernandez, Boy Acejas III, Orlino, Estelita dela Rosa, Honesto Cueva, Marcelino Bonafe, Valencia and Rodrigo Melchor.',
                    'Finally, on Feb. 4, 1991, then Justice Secretary Franklin Drilon released the ruling regarding the issue, stating that Proclamation No. 469 has not been revoked or superseded by the unpublished and unnumbered Proclamation dated June 2, 1975, and therefore said Proclamation No. 469 remains valid and enforceable until that time.',
                ],
            ],
            [
                'period' => 'April 1992 - June 1992',
                'title' => 'Opening campaign and first classes',
                'body' => [
                    'Following the court ruling, the construction immediately resumed, this time receiving substantial support from Congressman Tiñga, who worked and fought hard for its funding in Congress. That time, a P7 million fund was released for the two-storey building which the school used for its initial operation.',
                    'In April 1992, through the request of Dr. Samuel M. Salvador, Vice President for Branches and Extension Services, PUP acting President Zenaida Olonan, gave the approval to start the campaign for the opening of PUP Taguig. He recommended Dr. Normita A. Villa, Prof. Angelito D. Roldan, Prof. Susan A. Roldan, and Prof. Amelita A. Laurente to do the job.',
                    'On June 15, 1992, amidst the uncertainty of a mounting campus unrest spawned by a university-wide opposition to the administration of then Education Secretary Carinos anointed, Dr. Jaime Gellor, PUP Taguig held its first class session with some 470 enrollees, 15 faculty members, and a lone clerical staff housed in a still to be completed two-storey building standing on a muddy terrain.',
                ],
            ],
        ];
    @endphp

    @php
        $aboutHeroImage = 'assets/static_img/about_header_image.png';
        $aboutSectionHeaderImage = 'assets/static_img/about_header_image.png';
        $aboutHeroTitle = $selectedSection && $selectedSection['slug'] === 'history'
            ? 'CAMPUS HISTORY'
            : ($selectedSection && $selectedSection['slug'] === 'vision-and-mission'
                ? 'VISION AND MISSION'
                : 'ABOUT THE CAMPUS');
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
                                <img src="{{ asset($aboutHeroImage) }}" alt="" class="carousel-half carousel-half-left">
                            </div>

                            <div class="carousel-caption">
                                <h2>{{ $aboutHeroTitle }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <section class="about-shell page-shell">
            <nav class="about-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
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
                        <div class="contents-strip-head layout-inset">
                            <p class="section-tag layout-kicker">Contents</p>
                            <h2>All about the campus</h2>
                        </div>

                        <nav class="contents-cards" aria-label="About page contents">
                        @foreach($sections as $section)
                            <a
                                href="{{ route('public.about.section', $section['slug']) }}"
                                class="contents-card card_without_section"
                            >
                                    <div class="contents-card-inner">
                                        <div class="contents-card-front">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="{{ $section['label'] }}">
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
                </section>
            @endunless

            @if($selectedSection)
                <section class="about-sections">
                    @if($selectedSection['slug'] === 'history')
                        <section class="history-story reveal">
                            <div class="history-story-inner">
                                <div class="history-timeline-container reveal">
                                    <div class="history-timeline-shell">
                                        <div class="history-timeline-head reveal delay-100">
                                            <p class="history-kicker layout-kicker">Campus Timeline</p>
                                            <h4>How PUP Taguig has evolved over the years</h4>
                                        </div>

                                    <div class="history-timeline-grid">
                                        @foreach($historyTimeline as $milestone)
                                                @php
                                                    $firstParagraph = (string) ($milestone['body'][0] ?? '');
                                                    $remainingParagraphs = array_slice($milestone['body'], 1);
                                                    $hasExpandableTimelineCard = count($remainingParagraphs) > 0 || \Illuminate\Support\Str::length($firstParagraph) > 180;
                                                @endphp
                                                <article class="history-timeline-row reveal {{ $loop->odd ? 'is-left' : 'is-right' }} {{ $loop->iteration % 2 === 0 ? 'delay-200' : 'delay-100' }}">
                                                    <div class="history-timeline-marker" aria-hidden="true">
                                                        <span class="history-timeline-dot"></span>
                                                    </div>

                                                    <div class="history-timeline-card">
                                                        <span class="history-timeline-period">{{ $milestone['period'] }}</span>
                                                        <h5>{{ $milestone['title'] }}</h5>
                                                        @if($hasExpandableTimelineCard)
                                                            <p class="history-timeline-preview">{{ $firstParagraph }}</p>
                                                            <div class="history-timeline-more" aria-hidden="true">
                                                                @foreach($remainingParagraphs as $paragraph)
                                                                    <p>{{ $paragraph }}</p>
                                                                @endforeach
                                                            </div>
                                                            <button
                                                                type="button"
                                                                class="history-timeline-toggle"
                                                                aria-expanded="false"
                                                                aria-label="Read more about {{ $milestone['title'] }}"
                                                            >
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
                        </section>
                    @elseif($selectedSection['slug'] === 'vision-and-mission')
                        <section class="history-story history-story--vision reveal">
                            <div class="history-story-inner">
                                <div class="history-timeline-container history-timeline-container--vision reveal">
                                    <div class="history-page-header history-page-header--vision">
                                        <p class="history-page-kicker">Vision and Mission</p>
                                        <h2>Vision, Mission, Core Values, and Strategic Goals of the University</h2>
                                    </div>

                                    <div class="about-detail-body about-detail-body--vision">
                                        <div class="about-vision-content">
                                            <div class="about-vision-row about-vision-row--vision">
                                                <button
                                                    type="button"
                                                    class="about-vision-trigger about-vision-trigger--vision"
                                                    aria-expanded="false"
                                                    aria-controls="aboutVisionDescription"
                                                >
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
                                                <button
                                                    type="button"
                                                    class="about-vision-trigger about-vision-trigger--mission"
                                                    aria-expanded="false"
                                                    aria-controls="aboutMissionDescription"
                                                >
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

                                        <div class="about-vision-extension-grid">
                                            <article class="about-vision-feature about-vision-feature--goals reveal">
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
                                                                    <span class="about-goal-pillar-icon-shell" aria-hidden="true">
                                                                        <svg class="about-goal-pillar-icon" viewBox="0 0 640 640" focusable="false">
                                                                            <path d="M335.9 84.2C326.1 78.6 314 78.6 304.1 84.2L80.1 212.2C67.5 219.4 61.3 234.2 65 248.2C68.7 262.2 81.5 272 96 272L128 272L128 480L128 480L76.8 518.4C68.7 524.4 64 533.9 64 544C64 561.7 78.3 576 96 576L544 576C561.7 576 576 561.7 576 544C576 533.9 571.3 524.4 563.2 518.4L512 480L512 272L544 272C558.5 272 571.2 262.2 574.9 248.2C578.6 234.2 572.4 219.4 559.8 212.2L335.8 84.2zM464 272L464 480L400 480L400 272L464 272zM352 272L352 480L288 480L288 272L352 272zM240 272L240 480L176 480L176 272L240 272zM320 160C337.7 160 352 174.3 352 192C352 209.7 337.7 224 320 224C302.3 224 288 209.7 288 192C288 174.3 302.3 160 320 160z"/>
                                                                        </svg>
                                                                    </span>
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
                                            </article>
                                        </div>

                                    </div>
                                </div>

                                <article class="about-values-band reveal">
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
                                </article>
                            </div>
                        </section>
                    @else
                        <article class="about-section-card reveal {{ $selectedSection['slug'] === 'vision-and-mission' ? 'about-section-card--vision' : '' }}">
                            <div class="about-detail-heading">
                                <div class="about-detail-heading-copy">
                                    <h2>{{ $selectedSection['label'] }}</h2>
                                </div>

                                @if($selectedSection['slug'] === 'maps')
                                    <a href="https://maps.app.goo.gl/RDAwxBvDzyGzUbVN7" target="_blank" rel="noopener noreferrer" class="section-link">Open Map</a>
                                @endif
                            </div>

                            <div class="about-detail-hero">
                                <div class="about-detail-copy">
                                    <span class="about-detail-eyebrow">Campus Section</span>
                                    <p class="about-detail-lead">{{ $selectedSection['lead'] ?? '' }}</p>
                                </div>

                                    <div class="about-detail-visual">
                                        <img
                                            src="{{ asset($aboutSectionHeaderImage) }}"
                                            alt="{{ $selectedSection['label'] }}"
                                        >
                                    </div>
                                </div>

                            <div class="about-detail-body">
                            @if($selectedSection['slug'] === 'logo-and-symbols')
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
                            @elseif($selectedSection['slug'] === 'hymn')
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
                            @elseif($selectedSection['slug'] === 'maps')
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
                                                <p>Open the official map to check live directions, nearby access roads, and updated travel time before heading to campus.</p>
                                                <a href="https://maps.app.goo.gl/RDAwxBvDzyGzUbVN7" target="_blank" rel="noopener noreferrer" class="section-link">Open Map</a>
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
                            @elseif($selectedSection['slug'] === 'campus-officials')
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
                            @elseif($selectedSection['slug'] === 'strategic-development-plan')
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
                        </article>
                    @endif
                </section>
            @endif
        </section>
    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
