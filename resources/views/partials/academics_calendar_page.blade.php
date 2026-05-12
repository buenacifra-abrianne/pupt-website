@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $calendar = is_array($pageData['calendar'] ?? null) ? $pageData['calendar'] : [];
    $calendarUrl = 'https://www.pup.edu.ph/about/calendar';
@endphp

<div class="academic-shell page-shell">
    <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <span>&gt;</span>
        <a href="{{ route('public.academics') }}" @if($cmsPreview) data-academics-preview-nav="overview" @endif>Academics</a>
        <span>&gt;</span>
        <strong>University Calendar</strong>
    </nav>
</div>

<section
    class="iapply-hero uc-hero-b{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-hero"
        data-cms-section-label="University Calendar Hero"
    @endif
>
    <div class="uc-hero-b-top" @if($cmsPreview) data-cms-boundary @endif>
        <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>

        <h1>{{ $hero['title'] ?? '' }}</h1>
        <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
        <p class="uc-hero-b-desc">{{ $hero['body'] ?? '' }}</p>
    </div>
</section>

<section
    class="contents-strip dp-programs-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-calendar"
        data-cms-section-label="University Calendar Content"
    @endif
>
    <div class="contents-strip-inner" @if($cmsPreview) data-cms-boundary @endif>
        <div class="contents-strip-head reveal">
            <span class="section-tag">{{ $calendar['tag'] ?? '' }}</span>
            <h2>{{ $calendar['title'] ?? '' }}</h2>
        </div>

        <div class="uc-calendar-frame reveal delay-100">
            <div class="uc-calendar-official-card">
                <div class="uc-calendar-official-head">
                    <img
                        src="{{ asset('assets/static_img/logo.png') }}"
                        alt="PUP Seal"
                        class="uc-calendar-official-seal"
                    >
                    <div>
                        <p>Official Source</p>
                        <h3>Polytechnic University of the Philippines Calendar</h3>
                    </div>
                </div>

                <div class="uc-calendar-source-row">
                    <span>www.pup.edu.ph/about/calendar</span>
                </div>

                <div class="uc-calendar-actions reveal delay-200">
                    <a
                        href="{{ $calendarUrl }}"
                        class="apply-now-btn"
                        @unless($cmsPreview) target="_blank" rel="noopener" @endunless
                    >
                        View Official Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
