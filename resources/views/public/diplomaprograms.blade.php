<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diploma Programs - PUP Taguig Branch</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $academicsCms = \App\Support\AcademicsCmsContent::fromInput($academicsCms ?? [], null);
        $pageData = $academicsCms['pages']['diploma-programs'] ?? [];
    @endphp

    @unless($cmsPreview)
    <pup-header
        data-home="{{ route('public.home') }}"
        data-about="{{ route('public.about') }}"
        data-academics="{{ route('public.academics') }}"
        data-students="{{ route('public.students') }}"
        data-news-events="{{ route('public.events') }}"
        data-research="{{ route('public.research') }}"
        data-assets="{{ asset('assets') }}"
    ></pup-header>
    @endunless

    <main class="main-content academics-no-bottom-gap">
        @include('partials.academics_program_page', [
            'programPageKey' => 'diploma-programs',
            'programPageTitle' => 'Diploma Programs',
            'pageData' => $pageData,
            'cmsPreview' => $cmsPreview,
        ])
    </main>

    @unless($cmsPreview)
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    @else
    @include('partials.academics_preview_page_assets')
    @endunless
</body>
</html>
