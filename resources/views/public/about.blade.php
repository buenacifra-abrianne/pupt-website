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
        $campusImage = \App\Support\HomeCmsContent::resolveImagePath(
            $homeCms['campus_image'] ?? '',
            'assets/static_img/pupillar.jpeg'
        );
    @endphp

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
        <section class="about-pup">
            <div class="about-content">
            <h1>About PUP</h1>

            <section class="campus-story-block">
                <div class="campus-story-copy-block">
                    <p class="campus-story-tag">Campus Story</p>
                    <h2>{{ $homeCms['campus_title'] ?? 'PUP Taguig Campus' }}</h2>
                    <p>{!! nl2br(e((string) ($homeCms['campus_description'] ?? ''))) !!}</p>
                </div>

                <div class="campus-story-image-block">
                    <img src="{{ $campusImage }}" alt="PUP Taguig Campus Building">
                </div>
            </section>

            <div id="vision" class="content-block">
                <div id="aboutPUP">
                    <p>The Polytechnic University of the Philippines (PUP) is a government educational institution governed by Republic Act Number 8292 known as the Higher Education Modernization Act of 1997, and its Implementing Rules and Regulations contained in the Commission on Higher Education Memorandum Circular No. 4, series 1997. PUP is one of the country's highly competent educational institutions. The PUP Community is composed of the Board of Regents, University Officials, Administrative and Academic Personnel, Students, various Organizations, and the Alumni.</p>
                    <p>Governance of PUP is vested upon the Board of Regents, which exercises policy-making functions to carry out the mission and programs of the University by virtue of RA 8292 granted by the Commission on Higher Education. The University is administered by an appointed President by virtue of RA 8292 and is assisted by an Executive Vice President and the Vice Presidents for Academic Affairs, Student Services, Administration, Research, Extension and Development, and Finance.</p>
                </div>
            </div>
        

            <div class="carousel">
                <div class="carousel-slide fade">
                    <img id="about_welcomeTaguig_img" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Announcement 1">
                    <div class="carousel-caption">
                        <h2>Welcome to PUP Taguig Campus</h2>
                        <h7 id="about_welcomeTaguig">Excellence in Technical Education</h7>
                    </div>
                </div>
                <div class="carousel-slide fade">
                    <img id="about_academicExcellence_img" src="{{ asset('assets/static_img/graduates.jpg') }}" alt="Announcement 2">
                    <div class="carousel-caption">
                        <h2>Academic Excellence</h2>
                        <h7 id="about_academicExcellence">Preparing Leaders for Tomorrow</h7>
                    </div>
                </div>
                <div class="carousel-slide fade">
                    <img id="about_studentLife_img" src="{{ asset('assets/static_img/studentbody.jpg') }}" alt="Announcement 3">
                    <div class="carousel-caption">
                        <h2>Student Life</h2>
                        <h7 id="about_studentLife">Building Community and Character</h7>
                    </div>
                </div>

                <!-- Carousel Controls -->
                <a class="carousel-prev" onclick="changeSlide(-1)">&#10094;</a>
                <a class="carousel-next" onclick="changeSlide(1)">&#10095;</a>

                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <span class="indicator" onclick="goToSlide(1)"></span>
                    <span class="indicator" onclick="goToSlide(2)"></span>
                    <span class="indicator" onclick="goToSlide(3)"></span>
                </div>
            </div>
        </section>

        <aside class="contents-sidebar">
            <h2 class="contents-title">CONTENTS</h2>
            <nav class="contents-nav reveal">
                <a class="contents-link">Vision and Mission
                    <span class="popout" id = "visionMission">
                        Overview of the university vision and mission.
                    </span>
                </a>
                <a class="contents-link">Logo and Symbols
                    <span class="popout" id = "logoSymbols">
                        Overview of the university logo and symbols.
                    </span>
                </a>
                <a class="contents-link">Hymn
                    <span class="popout" id = "hymn">
                        Overview of the university hymn.
                    </span>
                </a>
                <a href="https://maps.app.goo.gl/RDAwxBvDzyGzUbVN7" class="contents-link">Maps
                    <span class="popout" id = "maps">
                        Overview of the university location.
                    </span>
                </a>
                <a class="contents-link">Campus Officials
                    <span class="popout" id = "campusOfficials">
                        Overview of the university campus officials.
                    </span>
                </a>
                <a class="contents-link">Strategic Development Plan
                    <span class="popout" id = "strategicPlan">
                        Overview of the university strategic development plan.
                    </span>
                </a>
                <a class="contents-link">University Calendar
                    <span class="popout" id = "universityCalendar">
                        Overview of the university calendar.
                    </span>
                </a>
            </nav>
        </aside>

    </main>

    <main class="main-content">
        
            <section class="about-pup read-more-container reveal">
                <div class="about-content reveal">
                    <div id="vision" class="content-block">
                        <button class="read-more-btn">Read More</button>
                            <div class="read-more-content reveal">
                                <div id = "about_readMore">
                                    <p>This institution started as the Manila Business School (MBS), founded in October 19, 1904 as part of the city school system under the superintendence of G.A. O'Reilley, which responds to the demand for training personnel for government service and the felt need to provide skills essential for private employment. In 1908, it was renamed as Philippine School of Commerce (PSC) and merged with the Philippine Normal School (PNS) in 1933 to 1946. By virtue of Republic Act 778, the PSC was again changed to Philippine College of Commerce (PCC) in 1952. Subsequently, the Philippine College of Commerce (PCC) was converted into a chartered state university, now known as the Polytechnic University of the Philippines by virtue of Presidential Decree Number 1341 issued by the President of the Philippines on April 1, 1978.</p>
                                    <p>PUP is a public, non-sectarian, non-profit institution of higher learning primarily tasked with harnessing the tremendous human resources potential of the nation by improving the physical, intellectual and material well-being of the individual through higher occupational, technical and professional instruction and training in the applied arts and sciences related to the fields of commerce, business administration, and technology.</p>
                                    <p>The University promotes applied research, advanced studies and progressive leadership in the stated fields. We also offer ladder-type higher vocational, distance learning (open university system), technical and professional programs in the area of business and distributive arts, education and the social sciences related to the fields of commerce, business administration and other polytechnic areas. Furthermore, the University takes steps to enrich the academic program in other fields of study and adopts a polytechnic program of education designed to provide the individual with employable skills and managerial know-how in order to make them creative, productive and self-reliant.</p>
                                    <p>PUP operates year-round with two semesters and a summer. Summer sessions depend on the course and on the campus.  The University employs 2,042 full-time and part-time faculty members with a few of the full-time faculty holding administrative positions. There are 1,381 regular and casual administrative employees who provide support services to the University population. The faculty spends two-thirds of their time in teaching and one-third in research and extension activities.</p>
                                    <p>One of the major functions of the University is research, a key component of scholarship and teaching. During the years under review, PUP received and allotted government and private funding to research.</p>
                                    <p>With more than twenty campuses serving more than 97,000 students, the Polytechnic University of the Philippines is the largest university in terms of student population. The main campus is named after a national hero, Apolinario Mabini, and is located in Sta. Mesa, Manila - in the middle of a busy metropolitan. But despite of this, the environment within its perimeter is a place conducive of learning.</p>
                                    <div class="image-container">
                                        <img id = "about_readMore_img1" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Image 1">
                                        <img id = "about_readMore_img2" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Image 2">
                                    </div>
                                    <div id="vision" class="content-block">
                                    <br><p>Majority of the students belong to the economically challenged level of society. It is the University's commitment to give qualified and talented students access to quality and responsive education to aid them in the achievement of their dreams and improve their lives. Being a well-educated and skilled individual, they will not only become job seekers but job creators as well, a force of knowledge workers and entrepreneurs.</p>
                                    <p>Iskolar ng Bayan (Scholars of the Nation) is what we call our students because the Philippine Government and other non-government institutions subsidize their tuition and other fees. More than a hundred of the student population are foreigners from China, Singapore, Indonesia, Cambodia, Myanmar, Tanzania, Nigeria, and Ghana. They are enrolled in business, language, statistics, communication, and education courses in the undergraduate and graduate levels. Students from Korea regularly visit PUP in summer to take up Intensive English courses.</p>
                                    <p>The Polytechnic University of the Philippines takes pride in its capability to accommodate the students because it:</p>
                                    <p>• has 25 campuses to make education accessible to everyone;</p>
                                    <p>• offers a wide range of courses: doctorate, master's, and bachelor's degrees as well as technology courses available through traditional and open, flexible or distance learning;</p>
                                    <p>• pioneered the ladderized system and the accreditation and equivalency system through the Expanded Tertiary Education Equivalency and Accreditation Program (ETEEAP) and the Nontraditional System Program (NTSP);</p>
                                    <p>• maintains an average size of 45-50 students per class;</p>
                                    <p>• offers an extensive selection of educational choices through more than 60 undergraduate and graduate programs;</p>
                                    <p>• schedules weekend and evening classes;</p>
                                    <p>• brings the resources and programs of PUP not only to full-time students but also to part-time and adult learners; and</p>
                                    <p>• provides a long list of extension services for the community and the country.</p>
                                    <p>The commitment of its leaders, faculty, staff, students, alumni and friends has formed the cornerstone of this University that has exceeded expectations with every generation of the graduates it has produced since its establishment.</p>
                                    <p>Today, PUP is relishing its successes and its students are enjoying unprecedented academic opportunities, an enhanced campus environment, upgraded colleges, state-of-the-art technology, and nationally and internationally recognized programs.</p>
                                    <div class="big-image">
                                        <img id = "about_readMore_img3" src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Image 1">
                                    </div>
                                    <div id="vision" class="content-block">
                                        <br><p>PUP celebrated 120 years last October 2024. It has gone far from what it was more than a century ago. This is mainly due to the support given by the government and the PUP Community and its benefactors. With the combined effort, PUP will continue to be a partner in nation-building and in poverty alleviation for the marginalized sector of society with quality, responsive, and relevant education as a tool.</p>                    </div>
                                    </div>
                                </div>    
                            </div>
                    </div>
                </div>
            </section>
    </main>
    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>

</body>
</html>
