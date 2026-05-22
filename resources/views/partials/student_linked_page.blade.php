@php
    $pageKey = (string) ($pageKey ?? '');
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $links = is_array($pageData['links'] ?? null) ? $pageData['links'] : [];
    $instructions = is_array($pageData['instructions'] ?? null) ? $pageData['instructions'] : [];
    $qrCodes = is_array($pageData['qr_codes'] ?? null) ? $pageData['qr_codes'] : [];
    $heroImage = \App\Support\StudentsCmsContent::resolveImagePath($hero['image'] ?? null, 'assets/static_img/about_header_image.png');
@endphp

<section class="student-page-hero">
    <img src="{{ $heroImage }}" alt="" class="student-page-hero-image">
    <div class="student-page-hero-overlay"></div>
    <div class="student-page-hero-copy">
        <p>{{ $hero['tag'] ?? '' }}</p>
        <h1>{{ $hero['title'] ?? '' }}</h1>
        @if(trim((string) ($hero['subtitle'] ?? '')) !== '')
            <span>{{ $hero['subtitle'] }}</span>
        @endif
    </div>
</section>

<div class="academic-shell page-shell">
    <nav class="academic-breadcrumb layout-breadcrumb reveal{{ $cmsPreview ? ' active' : '' }}" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <span>&gt;</span>
        <a href="{{ route('public.students') }}">Students</a>
        <span>&gt;</span>
        <strong>{{ $hero['title'] ?? 'Students' }}</strong>
    </nav>
</div>

<div class="student-page-body reveal{{ $cmsPreview ? ' active' : '' }}">
    <section class="student-page-intro">
        <p class="section-tag">{{ $hero['tag'] ?? '' }}</p>
        <h2>{{ $hero['subtitle'] ?? ($hero['title'] ?? '') }}</h2>
        <p>{{ $hero['body'] ?? '' }}</p>
    </section>

    @if($pageKey === 'admissions')
        <section class="student-page-section">
            <div class="student-page-section-head">
                <p class="section-tag">{{ $instructions['tag'] ?? '' }}</p>
                <h2>{{ $instructions['title'] ?? '' }}</h2>
            </div>
            <div class="students-rich-copy student-page-rich">
                {!! \App\Support\RichText::sanitize($instructions['body'] ?? '') !!}
            </div>
        </section>

        <section class="student-page-section">
            <div class="student-page-section-head">
                <p class="section-tag">{{ $qrCodes['tag'] ?? '' }}</p>
                <h2>{{ $qrCodes['title'] ?? '' }}</h2>
            </div>
            <div class="student-qr-grid">
                @forelse(($qrCodes['items'] ?? []) as $qrCode)
                    @php
                        $qrImage = trim((string) ($qrCode['image'] ?? ''));
                    @endphp
                    <article class="student-qr-card">
                        @if($qrImage !== '')
                            <img src="{{ \App\Support\StudentsCmsContent::resolveImagePath($qrImage, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $qrCode['label'] ?? 'QR code' }}">
                        @else
                            <div class="student-qr-placeholder">QR</div>
                        @endif
                        <div>
                            <h3>{{ $qrCode['label'] ?? 'QR Code' }}</h3>
                            <p>{{ $qrCode['description'] ?? '' }}</p>
                        </div>
                    </article>
                @empty
                    <p class="student-page-empty">No QR codes have been added yet.</p>
                @endforelse
            </div>
        </section>
    @endif

    <section class="student-page-section">
        <div class="student-page-section-head">
            <p class="section-tag">{{ $links['tag'] ?? '' }}</p>
            <h2>{{ $links['title'] ?? '' }}</h2>
            @if(trim((string) ($links['description'] ?? '')) !== '')
                <p>{{ $links['description'] }}</p>
            @endif
        </div>

        <div class="student-link-list">
            @forelse(($links['items'] ?? []) as $link)
                @php
                    $href = trim((string) ($link['href'] ?? ''));
                    $isExternal = preg_match('/^https?:\/\//i', $href) === 1;
                @endphp
                <a
                    href="{{ $href !== '' ? $href : '#' }}"
                    class="student-link-row"
                    @if($isExternal && !$cmsPreview) target="_blank" rel="noopener noreferrer" @endif
                >
                    <span>
                        <strong>{{ $link['label'] ?? 'Link' }}</strong>
                        @if(trim((string) ($link['description'] ?? '')) !== '')
                            <small>{{ $link['description'] }}</small>
                        @endif
                    </span>
                    <em>Open</em>
                </a>
            @empty
                <p class="student-page-empty">No links have been added yet.</p>
            @endforelse
        </div>
    </section>
</div>
