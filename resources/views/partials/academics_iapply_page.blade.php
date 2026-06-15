@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $schedule = is_array($pageData['schedule'] ?? null) ? $pageData['schedule'] : [];
    $guide = is_array($pageData['guide'] ?? null) ? $pageData['guide'] : [];
    $reminders = is_array($pageData['reminders'] ?? null) ? $pageData['reminders'] : [];
    $applyHref = trim((string) ($hero['cta_href'] ?? ''));
    $applyHref = $applyHref !== '' && $applyHref !== '#' ? $applyHref : 'https://iapply.pup.edu.ph/signin';
    $guideVideoUrl = trim((string) ($guide['video_url'] ?? ''));
    $guideWatchUrl = $guideVideoUrl;

    if (preg_match('~youtube\.com/embed/([^?&/]+)~i', $guideVideoUrl, $matches)) {
        $guideWatchUrl = 'https://www.youtube.com/watch?v='.$matches[1];
    }
@endphp

<div class="academic-shell page-shell">
    <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <span>&gt;</span>
        <a href="{{ route('public.academics') }}" @if($cmsPreview) data-academics-preview-nav="overview" @endif>Academics</a>
        <span>&gt;</span>
        <strong>PUP iApply</strong>
    </nav>
</div>

<section
    class="iapply-hero reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="pup-iapply-hero"
        data-cms-section-label="PUP iApply Hero"
    @endif
>
    <div data-cms-boundary class="cms-preview-boundary-full">
        <div class="iapply-hero-content">
            <div class="iapply-hero-copy">
                <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>
                <h1>{{ $hero['title'] ?? '' }}</h1>
                <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
                <p class="iapply-hero-lede">{{ $hero['body'] ?? '' }}</p>

                <div class="iapply-hero-actions">
                    <a href="{{ $applyHref }}" class="apply-now-btn" @unless($cmsPreview) target="_blank" rel="noopener" @endunless>
                        {{ $hero['cta_label'] ?? 'Apply Now' }}
                    </a>
                    <a href="#iapply-requirements" class="iapply-secondary-btn">View Requirements</a>
                </div>
            </div>

            <div class="iapply-hero-panel">
                <div class="iapply-hero-panel-head">
                    <span class="iapply-logo-icon">i</span>
                    <h3>{{ $hero['visual_title'] ?? '' }}</h3>
                </div>
                <p>{{ $hero['visual_body'] ?? '' }}</p>

                <div class="iapply-hero-desc">
                    <p>{{ $hero['list_title'] ?? '' }}</p>
                    <ul>
                        @foreach(($hero['list_items'] ?? []) as $item)
                            @if(trim((string) $item) !== '')
                                <li>{{ $item }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

@if(is_array($schedule['items'] ?? null) && array_values($schedule['items']) !== [])
    <section
        class="iapply-schedule-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="pup-iapply-schedule"
            data-cms-section-label="PUP iApply Schedule"
        @endif
    >
        <div data-cms-boundary class="cms-preview-boundary-full">
            <div class="iapply-schedule-inner">
                <div class="iapply-schedule-head reveal">
                    <span class="section-tag">{{ $schedule['tag'] ?? '' }}</span>
                    <h2>{{ $schedule['title'] ?? '' }}</h2>
                </div>

                <div class="iapply-schedule-grid reveal delay-100">
                    @foreach(($schedule['items'] ?? []) as $item)
                        @php
                            $itemLabel = trim((string) ($item['label'] ?? ''));
                            $itemValue = trim((string) ($item['value'] ?? ''));
                            $itemHref = trim((string) ($item['href'] ?? ''));
                        @endphp
                        <article class="iapply-schedule-box">
                            <span class="iapply-schedule-box-label">{{ $itemLabel !== '' ? $itemLabel : 'Schedule' }}</span>
                            @if($itemHref !== '')
                                <a
                                    class="iapply-schedule-box-value"
                                    href="{{ $itemHref }}"
                                    @if(!$cmsPreview && preg_match('/^https?:\/\//i', $itemHref) === 1)
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    @endif
                                >
                                    {{ $itemValue !== '' ? $itemValue : $itemHref }}
                                </a>
                            @else
                                <span class="iapply-schedule-box-value">{{ $itemValue !== '' ? $itemValue : 'Not specified' }}</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

<div class="iapply-sections-wrap">
    <section
        class="iapply-guide-section reveal delay-100{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="pup-iapply-guide"
            data-cms-section-label="PUP iApply Guide"
        @endif
    >
        <div data-cms-boundary class="cms-preview-boundary-full">
            <div class="iapply-guide-grid">
                <div class="iapply-video-wrap">
                @if($cmsPreview)
                    <div class="iapply-video-preview">
                        <span class="iapply-video-preview-icon">!</span>
                        <div>
                            <p class="iapply-video-preview-label">Video Preview</p>
                            <h3>{{ $guide['title'] ?? 'CAEPUP Step-by-step Application Guide' }}</h3>
                            <p>YouTube blocks this player inside the CMS iframe preview. The video embed remains active on the public page.</p>
                            @if($guideWatchUrl !== '')
                                <span>{{ $guideWatchUrl }}</span>
                            @endif
                        </div>
                    </div>
                @else
                    <iframe
                        src="{{ $guideVideoUrl }}"
                        title="{{ $guide['title'] ?? 'CAEPUP Step-by-step Application Guide' }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                @endif
                </div>

                <div class="iapply-guide-copy">
                    <span class="section-tag">{{ $guide['tag'] ?? '' }}</span>
                    <h2>{{ $guide['title'] ?? '' }}</h2>
                    <p>{{ $guide['description'] ?? '' }}</p>
                    <a href="#iapply-requirements" class="iapply-text-link">Check the files you need before applying</a>
                </div>
            </div>
        </div>
    </section>

    <section
        id="iapply-requirements"
        class="iapply-requirements-section reveal delay-200{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="pup-iapply-reminders"
            data-cms-section-label="PUP iApply Reminders"
        @endif
    >
        <div data-cms-boundary class="cms-preview-boundary-full">
            <div>
                <div class="iapply-section-heading">
                    <span class="section-tag">{{ $reminders['tag'] ?? '' }}</span>
                    <h2>{{ $reminders['title'] ?? '' }}</h2>
                </div>

                <div class="iapply-notice">
                    <p class="iapply-notice-title">{{ $reminders['notice_title'] ?? '' }}</p>
                    <ul>
                        @foreach(($reminders['notice_items'] ?? []) as $item)
                            @if(trim((string) $item) !== '')
                                <li>{{ $item }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div class="academic-rich-copy">
                    {!! \App\Support\RichText::sanitize($reminders['body_html'] ?? '') !!}
                </div>

                <ol class="iapply-checklist">
                    @foreach(($reminders['checklist_items'] ?? []) as $item)
                        @if(trim((string) $item) !== '')
                            <li>{{ $item }}</li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
</div>
