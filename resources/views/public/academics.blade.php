<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="../assets/styles/layout.css">
    <link rel="stylesheet" href="../assets/css/academics.css">
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
        <button class="message-button" title="Chat with AI Assistant">💬</button>

        <div class="page-content">
            <section class="about-pup">
                <div class="about-content">
                <h1>OVERVIEW</h1>

                <div id="academics_overview" class="content-block">
                    <p>Quality and relevant education that responds to the call of the present times in building the foundations of the future.</p>
                    <p>Ranging from high school to doctoral courses, traditional to nontraditional education system, the University makes it possible that deserving individuals can have access to these academic resources.</p>
                    <p>The University has always been making initiatives to enrich its academic programs in various fields of study and implement an educational strategy designed to provide our students with highly employable, managerial, and entrepreneurial skills in order to make them exceedingly creative, productive, competitive, and self-reliant.</p>
                </div>
            </section>
            <br>
            <section class="about-pup reveal">
                <div class="about-content">
                <h1>QUALITY</h1>

                <div id="academics_quality" class="content-block">
                    <p>Being one of the reputable universities in the country, we always make it to a point that the education given to our students meet the standards of quality and excellence.</p>
                </div>
            </section>
            <br>
            <section class="about-pup reveal">
                <div class="about-content">
                <h1>RELEVANT</h1>

                <div id="academics_relevant" class="content-block">
                    <p>Being one of the reputable universities in the country, we always make it to a point that the education given to our students meet the standards of quality and excellence.</p>
                </div>
            </section>
            <br>
            <section class="about-pup reveal">
                <div class="about-content">
                <h1>FLEXIBLE</h1>

                <div id="academics_flexible" class="content-block">
                    <p>Be it learning in a classroom, at home, or through the Internet, the University offer programs that can adapt to a student's living condition -- specially the working class. Our Open University and distance learning method goes beyond the physical restrictions of a campus.</p>
                </div>
            </section>
            <br>
            <section class="about-pup reveal">
                <div class="about-content">
                <h1>ACCREDITED</h1>

                <div id="academics_accredited" class="content-block">
                    <p>Most of our academic courses are accredited by the Accrediting Agency of Chartered Colleges and Universities in the Philippines (AACCUP).</p>
                </div>
            </section>
            <br>
            <section class="about-pup reveal">
                <div class="about-content">
                <h1>UNIVERSAL ACCESS TO QUALITY TERTIARY EDUCATION ACT</h1>

                <div id="academics_uate_act" class="content-block">
                    <p>Most of our academic courses are accredited by the Accrediting Agency of Chartered Colleges and Universities in the Philippines (AACCUP).</p>
                </div>
            </section>
        </div>

        <aside class="sidebar-column">
            <section class="contents-sidebar">
                <h2 class="contents-title">CONTENTS</h2>
                <nav class="contents-nav ">
                    <a href="#profile" class="contents-link">Academic Overview
                        <span class="popout" id="academics_content_overview">
                            Overview of the university academics.
                        </span>
                    </a>
                    <a href="#vision" class="contents-link">Academic Programs
                        <span class="popout" id="academics_content_programs">
                            Overview of the university academic programs.
                        </span>
                    </a>
                </nav>
            </section>
            <br>
            <section class="contents-sidebar">
                <h2 class="contents-title">LIBRARY AND<br>INFORMATION SERVICES</h2>
                <nav class="contents-nav">
                    <a href="#profile" class="contents-link">Ninoy Aquino Library and Learning Resources Center
                        <span class="popout" id="academics_library">
                            Overview of the university library and learning resources center.
                        </span>
                    </a>
                </nav>
            </section>
            <br>
            <section class="contents-sidebar">
                <h2 class="contents-title">QUALITY ASSURANCE</h2>
                <nav class="contents-nav">
                    <a href="#profile" class="contents-link">Quality Assurance Center
                        <span class="popout" id="academics_quality_assurance">
                            Overview of the university quality assurance center.
                        </span>
                    </a>
                </nav>
            </section>
            <br>    
            <section class="contents-sidebar">
                <h2 class="contents-title">ACADEMIC DEVELOPMENT</h2>
                <nav class="contents-nav">
                    <a href="#profile" class="contents-link">University Teaching and Learning Development Office
                        <span class="popout" id="academics_development">
                            Overview of the university teaching and learning development.
                        </span>
                    </a>
                </nav>
            </section>
        </aside>

    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
