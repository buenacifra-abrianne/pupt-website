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
        <div class="research-review-content" aria-hidden="true">
            <section class="page-section research-review-stage" aria-hidden="true">
                <div class="research-review-backdrop"></div>
            </section>
        </div>

        <section class="research-review-overlay" aria-modal="true" role="dialog" aria-labelledby="researchReviewTitle" aria-describedby="researchReviewText">
            <div class="research-review-popup reveal active">
                <p class="research-review-kicker">Research & Extension</p>
                <h1 id="researchReviewTitle">Page Under Review</h1>
                <p id="researchReviewText">This page is currently under review. The content is temporarily unavailable while updates are being finalized.</p>
                <a href="{{ route('public.home') }}" class="research-review-button">Back to Home</a>
            </div>
        </section>
    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
