@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $info = is_array($pageData['info'] ?? null) ? $pageData['info'] : [];
    $calendar = is_array($pageData['calendar'] ?? null) ? $pageData['calendar'] : [];
    $heroImage = \App\Support\AcademicsCmsContent::resolveImagePath($hero['image'] ?? '', 'assets/static_img/campus_photo.jpg');
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
    class="iapply-hero{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-hero"
        data-cms-section-label="University Calendar Hero"
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

        <div class="iapply-hero-visual dp-hero-photo-panel">
            <img src="{{ $heroImage }}" alt="{{ $hero['title'] ?? 'University Calendar' }}" class="dp-hero-photo">
        </div>
    </div>
</section>

<div
    class="iapply-schedule-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-info"
        data-cms-section-label="University Calendar Info"
    @endif
>
    <div class="iapply-schedule-inner" @if($cmsPreview) data-cms-boundary @endif>
        <div class="iapply-schedule-head">
            <span class="section-tag">{{ $info['tag'] ?? '' }}</span>
            <h2>{{ $info['title'] ?? '' }}</h2>
        </div>
        <div class="iapply-schedule-grid">
            @foreach(($info['items'] ?? []) as $item)
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
            <div class="uc-calendar-embed">
                <iframe
                    src="{{ \App\Support\DownloadableFile::url($calendar['pdf_url'] ?? null, 'assets/static_img/university_calendar.pdf') }}"
                    title="{{ $calendar['title'] ?? 'PUP University Academic Calendar' }}"
                    class="uc-calendar-iframe"
                    loading="lazy"
                ></iframe>
            </div>
            <p class="uc-calendar-note">{{ $calendar['note'] ?? '' }}</p>
            <div class="uc-calendar-actions reveal delay-200">
                @foreach(($calendar['actions'] ?? []) as $action)
                    @php
                        $style = strtolower(trim((string) ($action['style'] ?? 'primary'))) === 'outline' ? ' uc-btn-outline' : '';
                        $href = \App\Support\DownloadableFile::url($action['href'] ?? null, 'assets/static_img/university_calendar.pdf');
                    @endphp
                    <a
                        href="{{ $href }}"
                        class="apply-now-btn{{ $style }}"
                        @if(!empty($action['download'])) download @endif
                        @unless($cmsPreview || !empty($action['download'])) target="_blank" rel="noopener" @endunless
                    >
                        {{ $action['label'] ?? '' }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
