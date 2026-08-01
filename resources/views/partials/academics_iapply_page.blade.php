@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $schedule = is_array($pageData['schedule'] ?? null) ? $pageData['schedule'] : [];
    $guide = is_array($pageData['guide'] ?? null) ? $pageData['guide'] : [];
    $reminders = is_array($pageData['reminders'] ?? null) ? $pageData['reminders'] : [];
    $iapplySteps = array_values(
        is_array($reminders['steps'] ?? null)
            ? $reminders['steps']
            : (is_array($reminders['checklist_items'] ?? null) ? $reminders['checklist_items'] : [])
    );
    $scheduleItems = array_slice(
        array_values(is_array($schedule['items'] ?? null) ? $schedule['items'] : []),
        0,
        3
    );
    $scheduleItemLabels = [
        'Online Application',
        'Last Day of Online Application',
        'PUPCET results',
    ];
    $formatScheduleDateDisplay = static function (mixed $value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}\b/i', $value, $matches)) {
            $timestamp = strtotime($matches[0]);
        } else {
            $timestamp = strtotime($value);
        }

        return $timestamp ? date('F j, Y', $timestamp) : $value;
    };
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
    <div data-cms-boundary class="cms-preview-boundary-full" style="width: 100%;">
        <div class="iapply-hero-content iapply-hero-content-spread">
            <div class="iapply-hero-copy">
                <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>
                <h1>{{ $hero['title'] ?? '' }}</h1>
                <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
                <p class="iapply-hero-lede">{{ $hero['body'] ?? '' }}</p>
            </div>
            
            <div class="iapply-hero-actions" style="justify-content: flex-end; align-self: end; padding-bottom: 20px;">
                <a href="{{ $applyHref }}" class="apply-now-btn" @unless($cmsPreview) target="_blank" rel="noopener" @endunless>
                    {{ str_replace(' ↗', '', $hero['cta_label'] ?? 'Apply Now') }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                </a>
                <a href="#iapply-requirements" class="iapply-secondary-btn">View Requirements</a>
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

                <div class="uc-schedule-container reveal delay-100">
                    @foreach($scheduleItems as $index => $item)
                        @php
                            $itemLabel = $scheduleItemLabels[$index] ?? trim((string) ($item['label'] ?? ''));
                            $itemValue = trim((string) ($item['value'] ?? ''));
                            $itemHref = trim((string) ($item['href'] ?? ''));
                            $itemDisplayValue = $formatScheduleDateDisplay($itemValue);
                        @endphp
                        <article
                            class="uc-schedule-cell{{ $itemHref !== '' && $index === 2 ? ' iapply-schedule-box--with-action' : '' }}"
                        >
                            <div class="iapply-schedule-box-content">
                                <span class="iapply-schedule-box-label">{{ $itemLabel !== '' ? $itemLabel : 'Schedule' }}</span>
                                <span class="iapply-schedule-box-value">{{ $itemDisplayValue !== '' ? $itemDisplayValue : 'Not specified' }}</span>
                            </div>
                            @if($itemHref !== '' && $index === 2)
                                <div class="iapply-schedule-box-action">
                                    <a
                                        class="iapply-schedule-results-btn"
                                        href="{{ $itemHref }}"
                                        @if(!$cmsPreview && preg_match('/^https?:\/\//i', $itemHref) === 1)
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        @endif
                                    >
                                        View Results
                                    </a>
                                </div>
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
                    <span class="section-tag">{{ $reminders['tag'] ?? 'BEFORE YOU APPLY' }}</span>
                    <h2>{{ $reminders['title'] ?? '' }}</h2>
                </div>

                <div class="iapply-notice">
                    <p class="iapply-notice-title">REMINDERS</p>
                    <div class="academic-rich-copy">
                        {!! \App\Support\RichText::sanitize($reminders['reminders_html'] ?? '') !!}
                    </div>
                </div>

                <p class="mt-4" style="font-size: 0.92rem; font-weight: 600; color: var(--maroon);">Step by Step Process</p>
                <div class="academic-rich-copy mt-2">
                    {!! \App\Support\RichText::sanitize($reminders['steps_html'] ?? '') !!}
                </div>
            </div>
        </div>
    </section>
</div>
