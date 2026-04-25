@php
    $pageKey = $programPageKey ?? 'degree-programs';
    $pageTitle = $programPageTitle ?? 'Degree Programs';
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $info = is_array($pageData['info'] ?? null) ? $pageData['info'] : [];
    $cards = is_array($pageData['cards'] ?? null) ? $pageData['cards'] : [];
    $contact = is_array($pageData['contact'] ?? null) ? $pageData['contact'] : [];
    $heroImageFallback = 'assets/static_img/pupillar.jpeg';
    $cardImageFallback = 'assets/static_img/pupillar.jpeg';
    $heroImage = \App\Support\AcademicsCmsContent::resolveImagePath($hero['image'] ?? '', $heroImageFallback);
    $infoItems = array_values(is_array($info['items'] ?? null) ? $info['items'] : []);
    $cardItems = array_values(is_array($cards['items'] ?? null) ? $cards['items'] : []);
    $contactRows = array_values(is_array($contact['rows'] ?? null) ? $contact['rows'] : []);
@endphp

<div class="academic-shell page-shell">
    <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <span>&gt;</span>
        <a href="{{ route('public.academics') }}" @if($cmsPreview) data-academics-preview-nav="overview" @endif>Academics</a>
        <span>&gt;</span>
        <strong>{{ $pageTitle }}</strong>
    </nav>
</div>

<section
    class="iapply-hero{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="{{ $pageKey }}-hero"
        data-cms-section-label="{{ $pageTitle }} Hero"
    @endif
>
    <div class="iapply-hero-content" @if($cmsPreview) data-cms-boundary @endif>
        <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>
        <h1>{{ $hero['title'] ?? '' }}</h1>
        <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
        <p>{{ $hero['body'] ?? '' }}</p>

        @if(trim((string) ($hero['list_title'] ?? '')) !== '' || !empty($hero['list_items']))
            <div class="iapply-hero-desc">
                @if(trim((string) ($hero['list_title'] ?? '')) !== '')
                    <p>{{ $hero['list_title'] }}</p>
                @endif
                @if(!empty($hero['list_items']))
                    <ul>
                        @foreach(($hero['list_items'] ?? []) as $item)
                            @if(trim((string) $item) !== '')
                                <li>{{ $item }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="iapply-hero-visual dp-hero-photo-panel">
            <img src="{{ $heroImage }}" alt="{{ $hero['title'] ?? $pageTitle }}" class="dp-hero-photo">
        </div>
    </div>
</section>

<div
    class="iapply-schedule-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="{{ $pageKey }}-info"
        data-cms-section-label="{{ $pageTitle }} Quick Info"
    @endif
>
    <div class="iapply-schedule-inner" @if($cmsPreview) data-cms-boundary @endif>
        <div class="iapply-schedule-head">
            <span class="section-tag">{{ $info['tag'] ?? '' }}</span>
            <h2>{{ $info['title'] ?? '' }}</h2>
        </div>
        <div class="iapply-schedule-grid">
            @foreach($infoItems as $item)
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

<section class="contents-strip dp-programs-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}">
    <div class="contents-strip-inner">
        <div class="contents-strip-head reveal">
            <span class="section-tag">{{ $cards['tag'] ?? '' }}</span>
            <h2>{{ $cards['title'] ?? '' }}</h2>
        </div>

<div class="contents-cards dp-program-cards reveal delay-100">
            @foreach($cardItems as $item)
                @php
                    $itemTitle = $item['title'] ?? '';
                    $itemBody = $item['body'] ?? '';
                    $itemHref = trim((string) ($item['href'] ?? ''));
                    $itemBadge = trim((string) ($item['badge'] ?? ''));
                    $itemDept = trim((string) ($item['dept'] ?? ''));
                @endphp
                @if($cmsPreview)
                    <article
                        class="contents-card"
                        data-cms-card-index="{{ $loop->index }}"
                        data-cms-card-section="{{ $pageKey }}-cards"
                        data-cms-card-label="{{ $pageTitle }} Card"
                    >
                        <div class="cms-preview-card-actions" aria-label="Card actions">
                            <button type="button" class="cms-preview-card-action" data-cms-card-edit>Edit</button>
                        </div>
                @else
                    <a href="{{ $itemHref !== '' ? $itemHref : '#' }}" class="contents-card" tabindex="0">
                @endif
                    <div class="dp-diploma-card-body">
                        @if($itemBadge !== '')
                            <span class="dp-diploma-badge">{{ $itemBadge }}</span>
                        @endif
                        <h3 class="dp-diploma-title">{{ $itemTitle }}</h3>
                        @if($itemBody !== '')
                            <p class="dp-diploma-desc">{{ $itemBody }}</p>
                        @endif
                        @if($itemDept !== '')
                            <span class="dp-diploma-dept">{{ $itemDept }}</span>
                        @endif
                    </div>
                @if($cmsPreview)
                    </article>
                @else
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>
