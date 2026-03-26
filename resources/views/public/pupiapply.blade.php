<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP iApply - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
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
        <nav class="academic-breadcrumb reveal" aria-label="Breadcrumb">
            <a href="{{ route('public.home') }}">Home</a>
            <span>&gt;</span>
            <a href="{{ route('public.academics') }}">Academics</a>
            <span>&gt;</span>
            <strong>PUP iApply</strong>
        </nav>

        {{-- Content here --}}

    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>