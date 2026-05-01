@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $guide = is_array($pageData['guide'] ?? null) ? $pageData['guide'] : [];
    $reminders = is_array($pageData['reminders'] ?? null) ? $pageData['reminders'] : [];
    $applyHref = trim((string) ($hero['cta_href'] ?? ''));
    $applyHref = $applyHref !== '' && $applyHref !== '#' ? $applyHref : 'https://iapply.pup.edu.ph/signin';
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
</section>

<div class="iapply-sections-wrap">
    <section
        class="iapply-guide-section reveal delay-100{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="pup-iapply-guide"
            data-cms-section-label="PUP iApply Guide"
        @endif
    >
        <div class="iapply-guide-grid" @if($cmsPreview) data-cms-boundary @endif>
            <div class="iapply-video-wrap">
                <iframe
                    src="{{ $guide['video_url'] ?? '' }}"
                    title="{{ $guide['title'] ?? 'CAEPUP Step-by-step Application Guide' }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            </div>

            <div class="iapply-guide-copy">
                <span class="section-tag">{{ $guide['tag'] ?? '' }}</span>
                <h2>{{ $guide['title'] ?? '' }}</h2>
                <p>{{ $guide['description'] ?? '' }}</p>
                <a href="#iapply-requirements" class="iapply-text-link">Check the files you need before applying</a>
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
        <div @if($cmsPreview) data-cms-boundary @endif>
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
    </section>
</div>
