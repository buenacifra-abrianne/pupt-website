@php
    $pageKey = $programPageKey ?? 'degree-programs';
    $pageTitle = $programPageTitle ?? 'Degree Programs';
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $cards = is_array($pageData['cards'] ?? null) ? $pageData['cards'] : [];
    $contact = is_array($pageData['contact'] ?? null) ? $pageData['contact'] : [];
    $cardImageFallback = 'assets/static_img/pupillar.jpeg';
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
    class="iapply-hero dp-program-hero{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="{{ $pageKey }}-hero"
        data-cms-section-label="{{ $pageTitle }} Hero"
    @endif
>
    <div class="iapply-hero-content dp-program-hero-content">
        <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>
        <h1>{{ $hero['title'] ?? '' }}</h1>
        <p class="iapply-hero-sub">{{ $hero['subtitle'] ?? '' }}</p>
        @if(trim((string) ($hero['body'] ?? '')) !== '')
            <div class="iapply-hero-body academic-rich-copy">
                {!! \App\Support\RichText::sanitize($hero['body'] ?? '') !!}
            </div>
        @endif
    </div>
</section>

<section class="contents-strip dp-programs-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}">
    <div class="contents-strip-inner">
        <div class="contents-strip-head reveal">
            <span class="section-tag">{{ $cards['tag'] ?? '' }}</span>
            <h2>{{ $cards['title'] ?? '' }}</h2>
        </div>

<div class="contents-cards dp-program-cards reveal delay-100">
            @if($cmsPreview)
                <article class="contents-card contents-card-add dp-program-card-add" data-cms-add-program-card-trigger data-cms-card-section="{{ $pageKey }}-cards" data-cms-card-label="{{ $pageTitle }} Card" tabindex="0" role="button" aria-label="Add {{ $pageTitle }} card">
                    <div class="dp-program-card-add-inner">
                        <span class="dp-program-card-add-plus" aria-hidden="true">+</span>
                        <p class="dp-program-card-add-label">Add Card</p>
                    </div>
                </article>
            @endif
            @foreach($cardItems as $item)
                @php
                    $itemTitle = $item['title'] ?? '';
                    $itemBody = \Illuminate\Support\Str::limit(\App\Support\RichText::plainText($item['body'] ?? ''), 100, '');
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
                            <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-cms-card-delete title="Delete card" aria-label="Delete {{ $itemTitle !== '' ? $itemTitle : 'program card' }}">Delete</button>
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
