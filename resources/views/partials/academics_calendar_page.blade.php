@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $info = is_array($pageData['info'] ?? null) ? $pageData['info'] : [];
    $calendar = is_array($pageData['calendar'] ?? null) ? $pageData['calendar'] : [];
    $calendarUrl = 'https://www.pup.edu.ph/about/calendar';
    $infoRows = array_values(is_array($info['items'] ?? null) ? $info['items'] : []);
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
    <div data-cms-boundary class="cms-preview-boundary-full">
        <div class="uc-hero-b-top">
            <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>

            <h1>{{ $hero['title'] ?? '' }}</h1>
            <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
            <p class="uc-hero-b-desc">{{ $hero['body'] ?? '' }}</p>
        </div>
    </div>
</section>

@if($infoRows !== [])
    <section
        class="iapply-schedule-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="university-calendar-info"
            data-cms-section-label="University Calendar Info"
        @endif
    >
        <div data-cms-boundary class="cms-preview-boundary-full">
            <div class="iapply-schedule-inner">
                <div class="iapply-schedule-head reveal">
                    <span class="section-tag">{{ $info['tag'] ?? '' }}</span>
                    <h2>{{ $info['title'] ?? '' }}</h2>
                </div>

                <div class="iapply-schedule-grid reveal delay-100">
                    @foreach($infoRows as $row)
                        @php
                            $rowLabel = trim((string) ($row['label'] ?? ''));
                            $rowValue = trim((string) ($row['value'] ?? ''));
                            $rowHref = trim((string) ($row['href'] ?? ''));
                        @endphp
                        <article class="iapply-schedule-box">
                            <span class="iapply-schedule-box-label">{{ $rowLabel !== '' ? $rowLabel : 'Info' }}</span>
                            @if($rowHref !== '')
                                <a
                                    class="iapply-schedule-box-value"
                                    href="{{ $rowHref }}"
                                    @if(!$cmsPreview && preg_match('/^https?:\/\//i', $rowHref) === 1)
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    @endif
                                >
                                    {{ $rowValue !== '' ? $rowValue : $rowHref }}
                                </a>
                            @else
                                <span class="iapply-schedule-box-value">{{ $rowValue !== '' ? $rowValue : 'Not specified' }}</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

<section
    class="contents-strip dp-programs-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-calendar"
        data-cms-section-label="University Calendar Content"
    @endif
>
    <div data-cms-boundary class="cms-preview-boundary-full">
        <div class="contents-strip-inner">
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

                    @if(trim((string) ($calendar['note'] ?? '')) !== '')
                        <div class="uc-calendar-note academic-rich-copy">
                            {!! \App\Support\RichText::sanitize($calendar['note'] ?? '') !!}
                        </div>
                    @endif

                    @if(!empty($calendar['actions'] ?? []))
                        <div class="uc-calendar-actions reveal delay-200">
                            @foreach(($calendar['actions'] ?? []) as $action)
                                @php
                                    $actionLabel = trim((string) ($action['label'] ?? ''));
                                    $actionHref = trim((string) ($action['href'] ?? ''));
                                @endphp
                                @if($actionHref !== '')
                                    <a
                                        href="{{ $actionHref }}"
                                        class="apply-now-btn"
                                        @unless($cmsPreview) target="_blank" rel="noopener" @endunless
                                    >
                                        {{ $actionLabel !== '' ? $actionLabel : 'Open Calendar' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
