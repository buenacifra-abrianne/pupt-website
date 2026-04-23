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

<section
    class="contents-strip dp-programs-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="{{ $pageKey }}-cards"
        data-cms-section-label="{{ $pageTitle }} Cards"
    @endif
>
    <div class="contents-strip-inner">
        <div class="contents-strip-head reveal" @if($cmsPreview) data-cms-boundary @endif>
            <span class="section-tag">{{ $cards['tag'] ?? '' }}</span>
            <h2>{{ $cards['title'] ?? '' }}</h2>
        </div>

        <div class="contents-cards reveal delay-100">
            @foreach($cardItems as $item)
                @php
                    $itemTitle = $item['title'] ?? '';
                    $itemBody = $item['body'] ?? '';
                    $itemHref = trim((string) ($item['href'] ?? ''));
                    $itemImage = \App\Support\AcademicsCmsContent::resolveImagePath($item['image'] ?? '', $cardImageFallback);
                    $itemCta = trim((string) ($item['cta'] ?? '')) !== '' ? $item['cta'] : 'View Program';
                @endphp
                @if($cmsPreview)
                    <article
                        class="contents-card card_without_section"
                        data-cms-card-index="{{ $loop->index }}"
                        data-cms-card-section="{{ $pageKey }}-cards"
                        data-cms-card-label="{{ $pageTitle }} Card"
                    >
                        <div class="cms-preview-card-actions" aria-label="Card actions">
                            <button type="button" class="cms-preview-card-action" data-cms-card-edit>Edit</button>
                        </div>
                @else
                    <a href="{{ $itemHref !== '' ? $itemHref : '#' }}" class="contents-card card_without_section" tabindex="0">
                @endif
                    <div class="contents-card-inner">
                        <div class="contents-card-front">
                            <img src="{{ $itemImage }}" alt="{{ $itemTitle }}" loading="lazy">
                            <div class="contents-card-copy">
                                <h3>{{ $itemTitle }}</h3>
                            </div>
                        </div>
                        <div class="contents-card-back">
                            <div class="contents-card-overlay-copy">
                                <h3>{{ $itemTitle }}</h3>
                                <p>{{ $itemBody }}</p>
                            </div>
                            <span class="contents-card-action">{{ $itemCta }}</span>
                        </div>
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

<div
    class="dp-contact-wrap{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="{{ $pageKey }}-contact"
        data-cms-section-label="{{ $pageTitle }} Contact"
    @endif
>
    <div class="contact-info-card reveal" @if($cmsPreview) data-cms-boundary @endif>
        <div class="dp-contact-photo-panel">
            <div class="dp-contact-logo-wrap">
                <img
                    src="{{ asset('assets/static_img/logo.png') }}"
                    alt="{{ $contact['campus_name'] ?? 'PUP Taguig Branch' }}"
                    class="dp-contact-logo-img"
                    onerror="this.style.display='none'"
                >
            </div>
            <div class="dp-contact-branch-info">
                <p class="dp-contact-branch-name">{{ $contact['campus_name'] ?? '' }}</p>
                <p class="dp-contact-branch-sub">{{ $contact['campus_sub'] ?? '' }}</p>
            </div>
            <div class="dp-contact-divider"></div>
            <p class="dp-contact-address">{{ $contact['address'] ?? '' }}</p>
        </div>

        <div class="dp-contact-details-panel">
            <div class="dp-contact-intro">
                <span class="section-tag">{{ $contact['tag'] ?? '' }}</span>
                <h2 class="dp-contact-heading">{{ $contact['title'] ?? '' }}</h2>
                <p class="dp-contact-subtext">{{ $contact['description'] ?? '' }}</p>
            </div>

            <div class="dp-contact-rows">
                @foreach($contactRows as $row)
                    @php
                        $tone = strtolower(trim((string) ($row['tone'] ?? 'maroon'))) === 'gold' ? 'gold' : 'maroon';
                    @endphp
                    <div class="dp-contact-row dp-contact-row--{{ $tone }}">
                        <div class="dp-contact-icon dp-contact-icon--{{ $tone }}">
                            @if($tone === 'gold')
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2d1606" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fffaf4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.64 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.13 1 .37 1.98.71 2.93a2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l.96-.95a2 2 0 0 1 2.11-.45c.95.34 1.93.58 2.93.71A2 2 0 0 1 22 16.92z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <span class="dp-contact-row-label">{{ $row['label'] ?? '' }}</span>
                            <a href="{{ $row['href'] ?? '#' }}" class="dp-contact-row-value">{{ $row['value'] ?? '' }}</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <a href="{{ $contact['cta_href'] ?? '#' }}" class="apply-now-btn dp-contact-cta">
                {{ $contact['cta_label'] ?? 'Send Us a Message' }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>
