@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $schedule = is_array($pageData['schedule'] ?? null) ? $pageData['schedule'] : [];
    $guide = is_array($pageData['guide'] ?? null) ? $pageData['guide'] : [];
    $reminders = is_array($pageData['reminders'] ?? null) ? $pageData['reminders'] : [];
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
    <div class="iapply-hero-content" @if($cmsPreview) data-cms-boundary @endif>
        <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>
        <h1>{{ $hero['title'] ?? '' }}</h1>
        <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
        <p>{{ $hero['body'] ?? '' }}</p>

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

        <div class="iapply-hero-visual">
            <div class="iapply-hero-visual-inner">
                <div class="iapply-logo-icon">i</div>
                <h3>{{ $hero['visual_title'] ?? '' }}</h3>
                <p>{{ $hero['visual_body'] ?? '' }}</p>
                <a href="{{ $hero['cta_href'] ?? '#' }}" class="apply-now-btn" @unless($cmsPreview) target="_blank" rel="noopener" @endunless>
                    {{ $hero['cta_label'] ?? 'Apply Now' }}
                </a>
            </div>
        </div>
    </div>
</section>

<div
    class="iapply-schedule-strip reveal delay-100{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="pup-iapply-schedule"
        data-cms-section-label="PUP iApply Schedule"
    @endif
>
    <div class="iapply-schedule-inner" @if($cmsPreview) data-cms-boundary @endif>
        <div class="iapply-schedule-head">
            <span class="section-tag">{{ $schedule['tag'] ?? '' }}</span>
            <h2>{{ $schedule['title'] ?? '' }}</h2>
        </div>

        <div class="iapply-schedule-grid">
            @foreach(($schedule['items'] ?? []) as $item)
                @php
                    $href = trim((string) ($item['href'] ?? ''));
                    $value = trim((string) ($item['value'] ?? ''));
                @endphp
                @if(trim((string) ($item['label'] ?? '')) !== '' || $value !== '')
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">{{ $item['label'] ?? '' }}</span>
                        <span class="iapply-schedule-box-value">
                            @if($href !== '')
                                <a href="{{ $href }}" @unless($cmsPreview) target="_blank" rel="noopener" @endunless>{{ $value !== '' ? $value : $href }}</a>
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<div class="iapply-sections-wrap">
    <div
        class="iapply-section-card reveal delay-100{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="pup-iapply-guide"
            data-cms-section-label="PUP iApply Guide"
        @endif
    >
        <div @if($cmsPreview) data-cms-boundary @endif>
            <span class="section-tag">{{ $guide['tag'] ?? '' }}</span>
            <h2>{{ $guide['title'] ?? '' }}</h2>
            <p>{{ $guide['description'] ?? '' }}</p>

            <div class="iapply-video-wrap">
                <iframe
                    src="{{ $guide['video_url'] ?? '' }}"
                    title="{{ $guide['title'] ?? 'CAEPUP Step-by-step Application Guide' }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            </div>
        </div>
    </div>

    <div
        class="iapply-section-card reveal delay-200{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="pup-iapply-reminders"
            data-cms-section-label="PUP iApply Reminders"
        @endif
    >
        <div @if($cmsPreview) data-cms-boundary @endif>
            <span class="section-tag">{{ $reminders['tag'] ?? '' }}</span>
            <h2>{{ $reminders['title'] ?? '' }}</h2>

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
</div>
