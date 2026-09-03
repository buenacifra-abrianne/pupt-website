<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/card-selector.css') }}?v={{ filemtime(public_path('assets/css/card-selector.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Polytechnic University of the Philippines - Taguig Campus" />
    <meta property="og:description" content="Welcome to the PUP Taguig Campus Website" />
    <meta property="og:image" content="{{ asset('assets/static_img/logo.png') }}" />

</head>
<body>
  <script>
      (function() {
          try {
              var isDark = localStorage.getItem('pup-dark-mode') === 'true';
              if (isDark) {
                  document.body.classList.add('pup-dark-mode');
              }
          } catch (e) {}
      })();
  </script>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $academicsCms = \App\Support\AcademicsCmsContent::fromInput($academicsCms ?? [], null);
        $heroSection = $academicsCms['hero'] ?? [];
        $contentsSection = $academicsCms['contents'] ?? [];

        $featuresSection = $academicsCms['features'] ?? [];
        $contentsItems = $contentsSection['items'] ?? [];
        $featureItems = $featuresSection['items'] ?? [];

    @endphp

    @unless($cmsPreview)
        <pup-header
            data-home="{{ route('public.home') }}"
            data-about="{{ route('public.about') }}"
            data-academics="{{ route('public.academics') }}"
            data-students="{{ route('public.students') }}"
            data-news-events="{{ route('public.events') }}"
            data-research="{{ route('public.research') }}"
            data-assets="{{ asset('assets') }}"
        ></pup-header>
    @endunless

    <main class="main-content">
        <section
            class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="hero"
                data-cms-section-label="Academics Hero"
            @endif
        >
            @if($cmsPreview)
                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="hero" aria-label="Edit Academics Hero">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                    </svg>
                </button>
            @endif

            <div data-cms-boundary class="cms-preview-boundary-full">
                <section class="carousel-section">
                    <div class="carousel full-carousel">
                        <div class="carousel-stage">
                            <div class="carousel-slide active">
                                <div class="carousel-split" aria-hidden="true">
                                    <img
                                        src="{{ \App\Support\AcademicsCmsContent::resolveImagePath($heroSection['image'] ?? '', 'assets/static_img/about_header_image.png') }}"
                                        alt=""
                                        class="carousel-half carousel-half-left"
                                        data-academics-hero-image
                                        data-academics-default-src="{{ \App\Support\AcademicsCmsContent::resolveImagePath($heroSection['image'] ?? '', 'assets/static_img/about_header_image.png') }}"
                                    >
                                </div>
                                <div class="carousel-caption">
                                    <h2>{{ $heroSection['title'] ?? 'ACADEMICS' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Academics</strong>
            </nav>
        </section>

        <section class="contents-strip reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}">
            <div class="contents-strip-head layout-inset">
                <p class="section-tag">{{ $contentsSection['tag'] ?? 'Contents' }}</p>
            </div>

            <div class="contents-strip-inner">
                    <nav class="contents-cards show-card-action" aria-label="Academic page contents">
                        @foreach($contentsItems as $item)
                            @if($cmsPreview)
                                <article
                                    class="contents-card card_without_section"
                                    data-academics-contents-card
                                    data-academics-contents-index="{{ $loop->index }}"
                                >
                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                        <button type="button" class="cms-preview-card-action" data-academics-card-edit>Edit</button>
                                    </div>
                            @else
                                <a href="{{ route($item['route'] ?? 'public.academics') }}" class="contents-card card_without_section">
                            @endif
                                <div class="contents-card-inner">
                                    <div class="contents-card-front">
                                        <img
                                            src="{{ \App\Support\AcademicsCmsContent::resolveImagePath($item['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}"
                                            alt="{{ $item['label'] ?? 'Academics section' }}"
                                            data-academics-contents-card-image
                                            data-academics-default-src="{{ \App\Support\AcademicsCmsContent::resolveImagePath($item['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}"
                                        >
                                        <div class="contents-card-copy">
                                            <h3>{{ $item['label'] ?? '' }}</h3>
                                        </div>
                                    </div>
                                    <div class="contents-card-back">
                                        <div class="contents-card-overlay-copy">
                                            <h3>{{ $item['label'] ?? '' }}</h3>
                                            <p>{{ \Illuminate\Support\Str::limit((string) ($item['summary'] ?? ''), 120) }}</p>
                                        </div>
                                        <span class="contents-card-action">See more</span>
                                    </div>
                                </div>
                            @if($cmsPreview)
                                </article>
                            @else
                                </a>
                            @endif
                        @endforeach
                    </nav>
            </div>
        </section>




        <section
            class="academic-features reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="features"
                data-cms-section-label="Academics Features"
            @endif
        >
            <div data-cms-boundary class="cms-preview-boundary-full">
                <div class="academic-features-header layout-inset">
                    <p class="academic-features-eyebrow layout-kicker">{{ $featuresSection['tag'] ?? ($featuresSection['eyebrow'] ?? 'What we offer') }}</p>
                    @if(trim((string) ($featuresSection['title'] ?? '')) !== '')
                        <h2 class="academic-features-heading">{{ $featuresSection['title'] }}</h2>
                    @endif
                    @if(trim((string) ($featuresSection['description'] ?? '')) !== '')
                        <div class="academic-feature-copy academic-rich-copy academic-features-description">
                            {!! \App\Support\RichText::sanitize($featuresSection['description'] ?? '') !!}
                        </div>
                    @endif
                </div>

                <div class="academic-features-inner layout-inset">
                        <div class="academic-features-grid">
                            @foreach($featureItems as $item)
                                <div
                                    class="academic-feature-card{{ !empty($item['wide']) ? ' academic-feature-card--wide' : '' }}"
                                    @if($cmsPreview)
                                        data-academics-feature-card
                                        data-academics-feature-index="{{ $loop->index }}"
                                    @endif
                                >
                                    @if($cmsPreview)
                                        <div class="cms-preview-card-actions" aria-label="Card actions">
                                            <button type="button" class="cms-preview-card-action" data-academics-feature-edit>Edit</button>
                                        </div>
                                    @endif
                                    <div class="academic-feature-card-accent"></div>
                                    <p class="academic-feature-tag">{{ $item['tag'] ?? ($item['title'] ?? '') }}</p>
                                    @if(trim((string) ($item['title'] ?? '')) !== '')
                                        <h3 class="academic-feature-title">{{ $item['title'] }}</h3>
                                    @endif
                                    <div class="academic-feature-copy academic-rich-copy">
                                        {!! \App\Support\RichText::sanitize($item['description'] ?? ($item['body'] ?? '')) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                </div>
            </div>
        </section>
    </main>

    @unless($cmsPreview)
        <pup-footer></pup-footer>

        <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
        <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    @endunless

    @if($cmsPreview)
        <style>
            html,
            body {
                height: auto !important;
                min-height: 0 !important;
            }

            body {
                display: block !important;
            }

            body > .main-content {
                flex: none !important;
                min-height: 0 !important;
            }

            .reveal,
            .reveal.active {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
                will-change: auto !important;
            }

            .academic-intro-rich-copy > :first-child,
            .academic-feature-copy > :first-child {
                margin-top: 0;
            }

            .academic-intro-rich-copy > :last-child,
            .academic-feature-copy > :last-child {
                margin-bottom: 0;
            }

            .academic-intro-rich-copy p,
            .academic-feature-copy p {
                margin-bottom: 1em;
            }

            .academic-feature-copy strong {
                font-weight: 700;
            }

            .hero-shell,
            .contents-strip,
            .academic-intro-text,
            .academic-features {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                left: auto !important;
                right: auto !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                transform: none !important;
            }

            .cms-preview-editable {
                position: relative;
                cursor: pointer;
                isolation: isolate;
                overflow: visible !important;
            }

            .cms-preview-editable > [data-cms-boundary] {
                position: relative;
                display: block;
                width: auto;
                max-width: none;
                min-width: 0;
                margin: var(--cms-preview-outline-offset);
                box-sizing: border-box;
                overflow: visible !important;
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
                width: calc(100% - (var(--cms-preview-outline-offset) * 2));
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge {
                width: 100%;
                margin: 0;
            }

            .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
                width: 100%;
                margin: 0;
            }

            .cms-preview-editable > [data-cms-boundary]::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 2;
                box-sizing: border-box;
                pointer-events: none;
                border: 2px dashed rgba(242, 201, 76, 0.95);
                border-radius: 24px;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22);
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge::after {
                inset: var(--cms-preview-outline-offset);
            }

            .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full::after {
                inset: var(--cms-preview-outline-offset);
            }

            .cms-preview-editable > * {
                position: relative;
                z-index: 1;
            }

            .cms-preview-chip {
                position: absolute;
                top: var(--cms-preview-chip-top-offset);
                right: calc(var(--cms-preview-chip-right-offset) + var(--cms-preview-outline-offset));
                left: auto;
                transform: translateY(-50%);
                z-index: 9;
                border: none;
                border-radius: 12px;
                width: 44px;
                min-width: 44px;
                height: 44px;
                padding: 0;
                background: rgba(127, 17, 19, 0.96);
                color: #fffaf4;
                display: none !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
            }

            .cms-preview-chip:hover {
                background: rgba(152, 25, 28, 0.98);
            }

            .cms-preview-chip svg {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }

            .cms-preview-card-actions {
                position: absolute;
                top: 14px;
                right: 14px;
                z-index: 12;
                display: flex;
                gap: 8px;
            }

            .cms-preview-card-action {
                border: none;
                border-radius: 12px;
                padding: 0 12px;
                min-width: 64px;
                height: 36px;
                background: rgba(127, 17, 19, 0.92);
                color: #fffaf4;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
                cursor: pointer;
                font-size: 0.78rem;
                font-weight: 700;
            }

            .cms-preview-card-action-delete {
                background: rgba(92, 0, 0, 0.96);
            }

            .contents-card[data-academics-contents-card],
            .academic-feature-card[data-academics-feature-card] {
                position: relative;
                cursor: pointer;
                isolation: isolate;
            }

            .contents-card[data-academics-contents-card]::after,
            .academic-feature-card[data-academics-feature-card]::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 10;
                box-sizing: border-box;
                pointer-events: none;
                border: 2px dashed rgba(242, 201, 76, 0.95);
                border-radius: inherit;
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.24),
                    0 0 0 4px rgba(242, 201, 76, 0.12);
            }

            .contents-card[data-academics-contents-card]:hover::after,
            .contents-card[data-academics-contents-card]:focus-within::after,
            .academic-feature-card[data-academics-feature-card]:hover::after,
            .academic-feature-card[data-academics-feature-card]:focus-within::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .contents-card[data-academics-contents-card] *,
            .academic-feature-card[data-academics-feature-card] * {
                transition: none !important;
                animation: none !important;
            }

            .contents-strip.cms-preview-editable {
                overflow: visible !important;
            }

            .contents-strip.cms-preview-editable .contents-cards {
                grid-auto-flow: row;
                grid-auto-columns: unset;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                overflow: visible !important;
                padding-bottom: 0;
                scroll-snap-type: none;
                touch-action: auto;
            }

            .contents-strip.cms-preview-editable .contents-card {
                min-width: 0;
            }

            .contents-strip.cms-preview-editable .contents-card,
            .contents-strip.cms-preview-editable .contents-card:hover,
            .contents-strip.cms-preview-editable .contents-card:focus-visible,
            .contents-strip.cms-preview-editable .contents-card.active {
                transform: none !important;
                transition: none !important;
                box-shadow: 0 16px 34px rgba(77, 18, 18, 0.12) !important;
            }

            .contents-strip.cms-preview-editable .contents-card-inner,
            .contents-strip.cms-preview-editable .contents-card-front,
            .contents-strip.cms-preview-editable .contents-card-back,
            .contents-strip.cms-preview-editable .contents-card-front img,
            .contents-strip.cms-preview-editable .contents-card-copy,
            .contents-strip.cms-preview-editable .contents-card-overlay-copy,
            .contents-strip.cms-preview-editable .contents-card-action {
                transition: none !important;
            }

            .contents-strip.cms-preview-editable .contents-card-back {
                opacity: 0 !important;
                transform: translateY(100%) !important;
                pointer-events: none !important;
            }

            .contents-strip.cms-preview-editable .contents-card-front img {
                transform: none !important;
                filter: none !important;
            }

            .contents-strip.cms-preview-editable .contents-card-overlay-copy,
            .contents-strip.cms-preview-editable .contents-card-action {
                opacity: 0 !important;
                transform: translateY(18px) !important;
            }

            .contents-strip.cms-preview-editable .contents-card-copy {
                opacity: 1 !important;
            }

            .contents-strip.cms-preview-editable .card_without_section .contents-card-copy {
                min-height: 74px;
                padding: 10px 16px 12px;
            }

            .contents-card[data-academics-contents-card]:hover,
            .contents-card[data-academics-contents-card]:focus-within,
            .academic-feature-card[data-academics-feature-card]:hover,
            .academic-feature-card[data-academics-feature-card]:focus-within {
                transform: none !important;
                filter: none !important;
                box-shadow: inherit !important;
            }

            .contents-card[data-academics-contents-card]:hover .contents-card-back,
            .contents-card[data-academics-contents-card]:focus-within .contents-card-back {
                opacity: 0;
                transform: translateY(100%);
            }

            .contents-card[data-academics-contents-card]:hover .contents-card-overlay-copy,
            .contents-card[data-academics-contents-card]:hover .contents-card-action,
            .contents-card[data-academics-contents-card]:focus-within .contents-card-overlay-copy,
            .contents-card[data-academics-contents-card]:focus-within .contents-card-action {
                opacity: 0;
                transform: translateY(18px);
            }

            .contents-card[data-academics-contents-card]:hover .contents-card-front img,
            .contents-card[data-academics-contents-card]:focus-within .contents-card-front img {
                transform: none;
            }

            @media (max-width: 768px) {
                .hero-shell,
                .contents-strip,
                .academic-intro-text,
                .academic-features {
                    --cms-preview-outline-offset: 8px;
                    --cms-preview-chip-right-offset: 8px;
                }

                .cms-preview-chip {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                }

                .cms-preview-chip svg {
                    width: 18px;
                    height: 18px;
                }

                .cms-preview-card-actions {
                    top: 10px;
                    right: 10px;
                }

                .cms-preview-editable > [data-cms-boundary]::after {
                    border-radius: 16px;
                }

                .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge::after {
                    inset: var(--cms-preview-outline-offset);
                }
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const targets = Array.from(document.querySelectorAll('[data-cms-section]'));
                let previewHeightFrame = null;

                const postSection = (section, label) => {
                    window.parent?.postMessage({
                        type: 'cms-academics-edit',
                        section: section,
                        label: label || section,
                    }, '*');
                };

                const getElementBottom = (element) => {
                    return element.offsetTop + element.offsetHeight;
                };

                const isMeasuredElement = (element) => {
                    if (!(element instanceof HTMLElement)) {
                        return false;
                    }

                    const styles = window.getComputedStyle(element);
                    return styles.display !== 'none'
                        && styles.visibility !== 'hidden'
                        && styles.position !== 'fixed';
                };

                const postPreviewHeight = () => {
                    const main = document.querySelector('.main-content');
                    const scope = main instanceof HTMLElement ? main : document.body;
                    const visibleElements = Array.from(scope.children)
                        .filter((node) => isMeasuredElement(node));
                    const childBottom = visibleElements.reduce((maxBottom, node) => {
                        return Math.max(maxBottom, getElementBottom(node));
                    }, 0);
                    const height = childBottom || scope.scrollHeight || scope.offsetHeight || 1;

                    window.parent?.postMessage({
                        type: 'cms-academics-preview-height',
                        height: Math.max(1, Math.ceil(height)),
                    }, '*');
                };

                const updateHeroImage = (src, defaultSrc = '') => {
                    const image = document.querySelector('[data-academics-hero-image]');
                    if (!(image instanceof HTMLImageElement)) {
                        return;
                    }

                    const nextSrc = String(src || '').trim() || String(defaultSrc || image.dataset.academicsDefaultSrc || image.getAttribute('src') || '').trim();
                    if (!nextSrc) {
                        return;
                    }

                    if (defaultSrc) {
                        image.dataset.academicsDefaultSrc = defaultSrc;
                    }

                    image.src = nextSrc;
                    scheduleSettledPreviewHeight();
                };

                const updateContentsCardImage = (cardIndex, src, defaultSrc = '') => {
                    const card = document.querySelector(`[data-academics-contents-card][data-academics-contents-index="${cardIndex}"]`);
                    if (!card) {
                        return;
                    }

                    const image = card.querySelector('[data-academics-contents-card-image]') || card.querySelector('.contents-card-front img');
                    if (!(image instanceof HTMLImageElement)) {
                        return;
                    }

                    const nextSrc = String(src || '').trim() || String(defaultSrc || image.dataset.academicsDefaultSrc || image.getAttribute('src') || '').trim();
                    if (!nextSrc) {
                        return;
                    }

                    if (defaultSrc) {
                        image.dataset.academicsDefaultSrc = defaultSrc;
                    }

                    image.src = nextSrc;
                    scheduleSettledPreviewHeight();
                };

                const schedulePreviewHeight = () => {
                    if (previewHeightFrame !== null) {
                        window.cancelAnimationFrame(previewHeightFrame);
                    }

                    previewHeightFrame = window.requestAnimationFrame(() => {
                        postPreviewHeight();
                        previewHeightFrame = null;
                    });
                };

                const scheduleSettledPreviewHeight = () => {
                    schedulePreviewHeight();
                    [80, 220, 480, 900].forEach((delay) => {
                        window.setTimeout(schedulePreviewHeight, delay);
                    });
                };

                const bindPreviewImages = () => {
                    document.querySelectorAll('img').forEach((image) => {
                        if (image.dataset.cmsPreviewHeightBound === '1') {
                            return;
                        }

                        image.dataset.cmsPreviewHeightBound = '1';

                        if (image.complete) {
                            return;
                        }

                        const handleImageSettled = () => {
                            scheduleSettledPreviewHeight();
                        };

                        image.addEventListener('load', handleImageSettled, { once: true });
                        image.addEventListener('error', handleImageSettled, { once: true });
                    });
                };

                window.addEventListener('message', (event) => {
                    const data = event.data || {};
                    if (data.type !== 'cms-academics-preview-image') {
                        return;
                    }

                    if ((data.section || '') === 'hero') {
                        updateHeroImage(data.src, data.defaultSrc);
                        return;
                    }

                    if ((data.section || '') === 'contents') {
                        updateContentsCardImage(data.cardIndex, data.src, data.defaultSrc);
                    }
                });

                targets.forEach((target) => {
                    const section = target.getAttribute('data-cms-section') || '';
                    const label = target.getAttribute('data-cms-section-label') || section;
                    const chip = target.querySelector('[data-cms-edit-trigger]');
                    const boundary = target.querySelector('[data-cms-boundary]');

                    const openEditor = (event) => {
                        if (event.target.closest('[data-academics-contents-card], [data-academics-feature-card]')) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();
                        postSection(section, label);
                    };

                    target.addEventListener('mouseenter', () => target.classList.add('is-active'));
                    target.addEventListener('mouseleave', () => target.classList.remove('is-active'));

                    if (boundary) {
                        boundary.addEventListener('click', openEditor);
                    }

                    if (chip) {
                        chip.addEventListener('click', openEditor);
                    }
                });

                document.querySelectorAll('[data-academics-contents-card]').forEach((card) => {
                    const cardIndex = Number(card.getAttribute('data-academics-contents-index'));
                    const postCard = () => {
                        window.parent?.postMessage({
                            type: 'cms-academics-edit-card',
                            section: 'contents',
                            label: 'Edit Contents Card',
                            cardIndex,
                        }, '*');
                    };

                    card.querySelector('[data-academics-card-edit]')?.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        postCard();
                    });
                });

                document.querySelectorAll('[data-academics-feature-card]').forEach((card) => {
                    const cardIndex = Number(card.getAttribute('data-academics-feature-index'));
                    const postCard = () => {
                        window.parent?.postMessage({
                            type: 'cms-academics-edit-card',
                            section: 'features',
                            label: 'Edit Feature Card',
                            cardIndex,
                        }, '*');
                    };

                    card.querySelector('[data-academics-feature-edit]')?.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        postCard();
                    });
                });

                if (typeof ResizeObserver !== 'undefined') {
                    const previewHeightObserver = new ResizeObserver(() => {
                        scheduleSettledPreviewHeight();
                    });

                    previewHeightObserver.observe(document.body);

                    const main = document.querySelector('.main-content');
                    if (main) {
                        previewHeightObserver.observe(main);
                    }
                }

                if (typeof MutationObserver !== 'undefined') {
                    const previewMutationObserver = new MutationObserver(() => {
                        bindPreviewImages();
                        scheduleSettledPreviewHeight();
                    });

                    previewMutationObserver.observe(document.body, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['class', 'style', 'src'],
                    });
                }

                if (document.fonts?.ready) {
                    document.fonts.ready.then(() => scheduleSettledPreviewHeight()).catch(() => {});
                }

                window.addEventListener('load', scheduleSettledPreviewHeight);
                window.addEventListener('resize', scheduleSettledPreviewHeight);
                window.addEventListener('pageshow', scheduleSettledPreviewHeight);

                bindPreviewImages();
                scheduleSettledPreviewHeight();
            });
        </script>
    @endif
<script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
</body>
</html>
