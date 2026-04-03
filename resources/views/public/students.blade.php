<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/students.css') }}?v={{ filemtime(public_path('assets/css/students.css')) }}">
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
                <a href="https://pupsinta.freshservice.com/" class="ords-button">Go to PUP ORDS</a>
            </div>

            <div class="ords-footer reveal">
                <p>If you have questions, visit <a href="https://pupsinta.freshservice.com/">https://pupsinta.freshservice.com/</a></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>