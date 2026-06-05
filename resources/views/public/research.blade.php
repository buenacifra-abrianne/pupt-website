<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research & Extension - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/research.css') }}?v={{ filemtime(public_path('assets/css/research.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/card-selector.css') }}?v={{ filemtime(public_path('assets/css/card-selector.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $researchCms = \App\Support\ResearchCmsContent::fromInput($researchCms ?? [], null);
        $pageSection = $researchCms['page'] ?? [];
        $cards = collect($researchCms['cards'] ?? [])
            ->filter(fn ($card) => is_array($card))
            ->values();
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

    <main class="main-content research-review-page">
        <section
            class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="page"
                data-cms-section-label="Research Page Header"
            @endif
        >
            @if($cmsPreview)
                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="page" aria-label="Edit Research Page Header">
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
                                    <img src="{{ \App\Support\ResearchCmsContent::resolveImagePath($pageSection['hero_image'] ?? null, 'assets/static_img/pupillar.jpeg') }}" alt="" class="carousel-half carousel-half-left">
                                </div>
                                <div class="carousel-caption">
                                    <h2>{{ $pageSection['title'] ?? 'Research and Extension' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section class="academic-shell page-shell research-page-shell">
            <nav class="research-breadcrumb layout-breadcrumb reveal{{ $cmsPreview ? ' active' : '' }}" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Research &amp; Extension</strong>
            </nav>

            <section
                class="research-page-intro reveal{{ $cmsPreview ? ' active cms-preview-editable' : '' }}"
                @if($cmsPreview)
                    data-cms-section="page"
                    data-cms-section-label="Research Page Header"
                @endif
            >
                @if($cmsPreview)
                    <button type="button" class="cms-preview-chip" data-cms-edit-trigger="page" aria-label="Edit Research Intro">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                        </svg>
                    </button>
                @endif

                <div class="research-story-card cms-preview-boundary-edge" data-cms-boundary>
                    <div class="research-story-layout">
                        <div class="research-story-copy">
                            <p class="research-story-tag">{{ $pageSection['eyebrow'] ?? 'Research & Extension' }}</p>
                            <h1>{{ $pageSection['title'] ?? 'Research and Extension' }}</h1>
                        </div>

                        <div class="research-story-description">
                            {!! \App\Support\RichText::sanitize($pageSection['description'] ?? '') !!}
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <section class="students-contents-strip reveal{{ $cmsPreview ? ' active cms-preview-editable' : '' }}">
            <div>
                <div class="students-contents-inner">
                    <div class="students-contents-head">
                        <p class="section-tag">Contents</p>
                    </div>

                    <nav class="students-cards{{ $cmsPreview ? '' : ' alphabetical-card-pages' }}" aria-label="Research and Extension contents">
                        @if($cmsPreview)
                            <article class="students-card students-card-add" data-research-add-card-trigger tabindex="0" role="button" aria-label="Add content">
                                <div class="students-card-inner">
                                    <div class="students-card-front students-card-front-add">
                                        <div class="students-card-add-inner">
                                            <span class="students-card-add-plus" aria-hidden="true">+</span>
                                            <p class="students-card-add-label">Add Content</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endif

                        @forelse($cards as $card)
                            @php
                                $cardLink = trim((string) ($card['link'] ?? ''));
                                $cardTitle = trim((string) ($card['title'] ?? ''));
                                $cardImage = trim((string) ($card['image'] ?? 'assets/static_img/pupillar.jpeg'));
                            @endphp

                            @if($cmsPreview)
                                <article
                                    class="students-card"
                                    data-cms-edit-trigger="cards"
                                    data-cms-section-label="Research Contents"
                                    data-research-card-index="{{ $loop->index }}"
                                >
                            @else
                                <a
                                    href="{{ $cardLink !== '' ? $cardLink : '#' }}"
                                    class="students-card"
                                >
                            @endif
                                @if($cmsPreview)
                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                        <button type="button" class="cms-preview-card-action" data-research-card-edit title="Edit content" aria-label="Edit {{ $cardTitle !== '' ? $cardTitle : 'research content' }}">
                                            Edit
                                        </button>
                                        <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-research-card-delete title="Delete content" aria-label="Delete {{ $cardTitle !== '' ? $cardTitle : 'research content' }}">
                                            Delete
                                        </button>
                                    </div>
                                @endif
                                <div class="students-card-inner">
                                    <div class="students-card-front">
                                        <img src="{{ \App\Support\ResearchCmsContent::resolveImagePath($cardImage !== '' ? $cardImage : null, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $cardTitle !== '' ? $cardTitle : 'Research content' }}">
                                        <div class="students-card-copy">
                                            <h3>{{ $cardTitle !== '' ? $cardTitle : 'Content' }}</h3>
                                        </div>
                                    </div>
                                    <div class="students-card-back">
                                        <div class="students-card-overlay-copy">
                                            <h3>{{ $cardTitle !== '' ? $cardTitle : 'Content' }}</h3>
                                            <div class="students-card-description">{!! \App\Support\RichText::sanitize($card['description'] ?? '') !!}</div>
                                        </div>
                                        <span class="students-card-action">{{ $cardLink !== '' ? 'Open link' : 'Update soon' }}</span>
                                    </div>
                                </div>
                            @if($cmsPreview)
                                </article>
                            @else
                                </a>
                            @endif
                        @empty
                            <article class="students-card students-card-empty">
                                <div class="students-card-inner">
                                    <div class="students-card-front">
                                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Research placeholder">
                                        <div class="students-card-copy">
                                            <h3>No content yet</h3>
                                        </div>
                                    </div>
                                    <div class="students-card-back">
                                        <div class="students-card-overlay-copy">
                                            <h3>No content yet</h3>
                                            <p>Add content from CMS to show research and extension links here.</p>
                                        </div>
                                        <span class="students-card-action">Add content</span>
                                    </div>
                                </div>
                            </article>
                        @endforelse
                    </nav>
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
            .reveal {
                opacity: 1 !important;
                transform: none !important;
            }

            .hero-shell,
            .research-page-intro.cms-preview-editable,
            .students-contents-strip.cms-preview-editable {
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

            .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
                width: 100%;
                margin: 0;
            }

            .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full::after {
                inset: var(--cms-preview-outline-offset);
            }

            .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge {
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

            .students-card[data-cms-edit-trigger] {
                position: relative;
                cursor: default;
                isolation: isolate;
            }

            .students-card[data-cms-edit-trigger="cards"]::after {
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

            .students-card[data-cms-edit-trigger="cards"]:hover::after,
            .students-card[data-cms-edit-trigger="cards"]:focus-within::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .cms-preview-card-actions {
                position: absolute;
                top: 14px;
                right: 14px;
                z-index: 12;
                display: flex;
                gap: 8px;
                opacity: 1;
                transform: none;
                transition: none;
            }

            .cms-preview-card-action {
                border: none;
                border-radius: 12px;
                padding: 0 12px;
                min-width: 64px;
                height: 36px;
                background: rgba(127, 17, 19, 0.92);
                color: #fffaf4;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
                cursor: pointer;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .cms-preview-card-action-delete {
                background: rgba(92, 0, 0, 0.96);
            }

            .students-card-add {
                cursor: pointer;
                min-height: 100%;
                justify-content: center;
                align-items: center;
                border: 2px dashed rgba(127, 17, 19, 0.2);
                background: linear-gradient(160deg, rgba(255,255,255,.96) 0%, rgba(250,243,236,.9) 100%);
            }

            .students-card-add .students-card-inner {
                height: 100%;
            }

            .students-card-add .students-card-back {
                display: none;
            }

            .students-card-front-add {
                display: flex;
                min-height: 100%;
                flex: 1;
                align-items: center;
                justify-content: center;
                background: transparent;
            }

            .students-card-add-inner {
                display: flex;
                min-height: 100%;
                flex: 1;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 14px;
                text-align: center;
                padding: 36px 24px;
            }

            .students-card-add-plus {
                color: #7f1113;
                font-size: clamp(3rem, 6vw, 4.5rem);
                font-weight: 500;
                line-height: 1;
            }

            .students-card-add-label {
                margin: 0;
                color: #7f1113;
                font-family: "Poppins", sans-serif;
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: .04em;
            }

            .students-card[data-cms-edit-trigger] {
                transition: filter .18s ease, box-shadow .18s ease, transform .18s ease;
            }

            .students-card[data-cms-edit-trigger]:hover {
                filter: none;
                box-shadow: none;
                transform: none;
            }

            .students-card[data-cms-edit-trigger]:hover .cms-preview-card-actions,
            .students-card[data-cms-edit-trigger]:focus-within .cms-preview-card-actions {
                opacity: 1;
                transform: none;
            }

            .students-card-add:hover {
                transform: none;
                box-shadow: none;
                border-color: rgba(127, 17, 19, 0.2);
                filter: none;
            }

            .students-card,
            .students-card-inner,
            .students-card-front,
            .students-card-back,
            .students-card-front img,
            .students-card-overlay-copy,
            .students-card-action {
                transition: none !important;
                animation: none !important;
            }

            .students-card:hover .students-card-back,
            .students-card:focus-visible .students-card-back {
                opacity: 0;
                transform: translateY(100%);
            }

            .students-card:hover .students-card-overlay-copy,
            .students-card:hover .students-card-action,
            .students-card:focus-visible .students-card-overlay-copy,
            .students-card:focus-visible .students-card-action {
                opacity: 0;
                transform: translateY(18px);
            }

            .students-card:hover .students-card-front img,
            .students-card:focus-visible .students-card-front img {
                transform: none;
                filter: none;
            }

            .students-card-add:focus-visible {
                outline: 3px solid rgba(242,201,76,.95);
                outline-offset: 3px;
            }

            @media (max-width: 768px) {
                .hero-shell,
                .research-page-intro.cms-preview-editable,
                .students-contents-strip.cms-preview-editable {
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
                    opacity: 1;
                    transform: none;
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
            (() => {
                document.querySelectorAll('a').forEach((anchor) => {
                    anchor.addEventListener('click', (event) => {
                        event.preventDefault();
                    });
                });
            })();
        </script>

        <script>
            (() => {
                let previewHeightFrame = null;

                const schedulePreviewHeight = () => {
                    if (previewHeightFrame !== null) {
                        window.cancelAnimationFrame(previewHeightFrame);
                    }

                    previewHeightFrame = window.requestAnimationFrame(() => {
                        const main = document.querySelector('.main-content');
                        const scope = main instanceof HTMLElement ? main : document.body;
                        const visible = Array.from(scope.children).filter((node) => {
                            if (!(node instanceof HTMLElement)) {
                                return false;
                            }

                            const styles = window.getComputedStyle(node);
                            return styles.display !== 'none'
                                && styles.visibility !== 'hidden'
                                && styles.position !== 'fixed';
                        });

                        const contentBottom = visible.reduce((maxBottom, node) => {
                            const styles = window.getComputedStyle(node);
                            const marginBottom = Number.parseFloat(styles.marginBottom) || 0;

                            return Math.max(maxBottom, node.offsetTop + node.offsetHeight + marginBottom);
                        }, 0);

                        window.parent?.postMessage({
                            type: 'cms-research-preview-height',
                            height: Math.max(1, Math.ceil(contentBottom)),
                        }, '*');

                        previewHeightFrame = null;
                    });
                };

                if (typeof ResizeObserver !== 'undefined') {
                    const observer = new ResizeObserver(() => schedulePreviewHeight());
                    observer.observe(document.body);
                    document.querySelector('.main-content') && observer.observe(document.querySelector('.main-content'));
                }

                if (typeof MutationObserver !== 'undefined') {
                    const observer = new MutationObserver(() => schedulePreviewHeight());
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['class', 'style', 'src'],
                    });
                }

                if (document.fonts?.ready) {
                    document.fonts.ready.then(() => schedulePreviewHeight()).catch(() => {});
                }

                window.addEventListener('load', schedulePreviewHeight);
                window.addEventListener('resize', schedulePreviewHeight);
                window.addEventListener('pageshow', schedulePreviewHeight);
                schedulePreviewHeight();
            })();
        </script>
    @endif
</body>
</html>
