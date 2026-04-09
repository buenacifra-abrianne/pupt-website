<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/students.css') }}?v={{ filemtime(public_path('assets/css/students.css')) }}">
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

    <main class="main-content students-review-page">
        <div class="students-review-content" aria-hidden="true">
            <section class="hero-shell">
                <section class="carousel-section">
                    <div class="carousel full-carousel">
                        <div class="carousel-stage">
                            <div class="carousel-slide active">
                                <div class="carousel-split" aria-hidden="true">
                                    <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                                </div>
                                <div class="carousel-caption">
                                    <h2>STUDENTS</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <section class="academic-shell page-shell">
                <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                    <a href="{{ route('public.home') }}">Home</a>
                    <span>&gt;</span>
                    <strong>Students</strong>
                </nav>
            </section>

            <div class="ords-container">
                <div class="ords-card reveal">
                    <h1>PUP ONLINE DOCUMENT REQUEST SYSTEM</h1>
                    <a href="https://pupsinta.freshservice.com/" class="ords-button" tabindex="-1" aria-disabled="true">Go to PUP ORDS</a>
                </div>

                <div class="ords-footer reveal">
                    <p>If you have questions, visit <a href="https://pupsinta.freshservice.com/" tabindex="-1" aria-disabled="true">https://pupsinta.freshservice.com/</a></p>
                </div>
            </div>
        </div>

        <section class="students-review-overlay" aria-modal="true" role="dialog" aria-labelledby="studentsReviewTitle" aria-describedby="studentsReviewText">
            <div class="students-review-popup reveal active">
                <p class="students-review-kicker">Students Page</p>
                <h1 id="studentsReviewTitle">Page Under Review</h1>
                <p id="studentsReviewText">This page is currently under review. The content is temporarily unavailable while updates are being finalized.</p>
                <a href="{{ route('public.home') }}" class="students-review-button">Back to Home</a>
            </div>
        </section>
    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
