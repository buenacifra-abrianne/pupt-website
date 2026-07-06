<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Development Plan - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/research.css') }}?v={{ filemtime(public_path('assets/css/research.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v={{ filemtime(public_path('assets/css/about.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Polytechnic University of the Philippines - Taguig Campus" />
    <meta property="og:description" content="Welcome to the PUP Taguig Campus Website" />
    <meta property="og:image" content="{{ asset('assets/static_img/logo.png') }}" />

</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $researchCms = \App\Support\ResearchCmsContent::fromInput($researchCms ?? [], null);
        $sdp = $sdp ?? ($researchCms['strategic_development_plan'] ?? \App\Support\ResearchCmsContent::defaults()['strategic_development_plan']);
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
        <section class="academic-shell page-shell research-page-shell">
            <nav class="research-breadcrumb layout-breadcrumb reveal{{ $cmsPreview ? ' active' : '' }}" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <a
                    href="{{ route('public.research', $cmsPreview ? ['cms_preview' => 1] : []) }}"
                    @if($cmsPreview) data-research-preview-nav="overview" @endif
                >Research &amp; Extension</a>
                <span>&gt;</span>
                <strong>{{ $sdp['label'] ?? 'Strategic Development Plan' }}</strong>
            </nav>

            {{-- SDP Page Header --}}
            <div
                class="sdp-page-header reveal{{ $cmsPreview ? ' cms-preview-editable active' : '' }}"
                @if($cmsPreview)
                    data-cms-section="strategic-development-plan-header"
                    data-cms-section-label="{{ $sdp['label'] ?? 'Strategic Development Plan' }}"
                @endif
            >
                @if($cmsPreview)
                    <button type="button" class="cms-preview-chip" data-cms-edit-trigger="strategic-development-plan-header" aria-label="Edit {{ $sdp['label'] ?? 'Strategic Development Plan' }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                        </svg>
                    </button>
                @endif

                <div @if($cmsPreview) data-cms-boundary class="cms-preview-boundary-edge" @endif>
                    @php
                        $sdpHeaderDescription = trim((string) (($sdp['lead'] ?? '') !== '' ? ($sdp['lead'] ?? '') : ($sdp['summary'] ?? '')));
                        $sdpHeaderDescriptionHtml = trim($sdpHeaderDescription) !== strip_tags($sdpHeaderDescription)
                            ? \App\Support\RichText::sanitize($sdpHeaderDescription)
                            : nl2br(e($sdpHeaderDescription));
                    @endphp
                    <div class="sdp-page-header-inner">
                        <div class="sdp-page-header-copy">
                            <p class="sdp-page-header-eyebrow">Strategic Planning</p>
                            <h1 class="sdp-page-title">{{ $sdp['label'] ?? 'Strategic Development Plan' }}</h1>
                            <div class="sdp-page-subtitle rich-text-content">{!! $sdpHeaderDescriptionHtml !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Development Priorities --}}
            <div
                class="sdp-priorities-shell reveal{{ $cmsPreview ? ' cms-preview-editable active' : '' }}"
                @if($cmsPreview)
                    data-cms-section="strategic-development-plan"
                    data-cms-section-label="Development Priorities"
                @endif
            >
                @if($cmsPreview)
                    <button type="button" class="cms-preview-chip" data-cms-edit-trigger="strategic-development-plan" aria-label="Edit Development Priorities">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                        </svg>
                    </button>
                @endif

                <div @if($cmsPreview) data-cms-boundary class="cms-preview-boundary-edge" @endif>
                    <div class="sdp-block-header reveal">
                        <span class="ls-block-kicker">Development Priorities</span>
                        <h2 class="ls-block-title">Highlighted focus areas driving the campus forward</h2>
                    </div>

                    <div class="sdp-priorities-grid">
                        @if($cmsPreview)
                            <article
                                class="sdp-priority-card sdp-priority-card--add reveal"
                                data-research-plan-priority-add
                                tabindex="0"
                                role="button"
                                aria-label="Add a new strategic development priority"
                            >
                                <div class="sdp-priority-add-inner">
                                    <span class="sdp-priority-add-plus" aria-hidden="true">+</span>
                                    <p class="sdp-priority-add-label">Add Priority</p>
                                </div>
                            </article>
                        @endif

                        @foreach($sdp['development_priorities'] ?? [] as $priority)
                            @php
                                $num = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT);
                                $priorityBody = (string) ($priority['body'] ?? '');
                                $priorityBodyHtml = trim($priorityBody) !== strip_tags($priorityBody)
                                    ? \App\Support\RichText::sanitize($priorityBody)
                                    : nl2br(e($priorityBody));
                            @endphp
                            <article
                                class="sdp-priority-card reveal {{ $loop->index % 2 === 1 ? 'delay-100' : '' }}{{ $cmsPreview ? ' cms-preview-editable-card' : '' }}"
                                @if($cmsPreview)
                                    data-research-plan-priority-card
                                    data-research-plan-priority-index="{{ $loop->index }}"
                                    data-research-plan-priority-label="{{ $priority['title'] ?? ('Priority Card ' . $loop->iteration) }}"
                                @endif
                            >
                                @if($cmsPreview)
                                    <div class="cms-preview-card-actions" aria-label="Strategic development priority actions">
                                        <button type="button" class="cms-preview-card-action" data-research-plan-priority-edit>Edit</button>
                                        <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-research-plan-priority-delete>Delete</button>
                                    </div>
                                @endif
                                <div class="sdp-priority-card-accent" aria-hidden="true"></div>
                                <div class="sdp-priority-header">
                                    <span class="sdp-priority-index">{{ $num }}</span>
                                    <h3 class="sdp-priority-title">{{ $priority['title'] ?? '' }}</h3>
                                </div>
                                <div class="sdp-priority-body rich-text-content">{!! $priorityBodyHtml !!}</div>
                            </article>
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
            .reveal {
                opacity: 1 !important;
                transform: none !important;
            }

            .sdp-page-header.cms-preview-editable,
            .sdp-priorities-shell.cms-preview-editable {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
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
                box-sizing: border-box;
                overflow: visible !important;
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
                top: var(--cms-preview-chip-top-offset, 50%);
                right: calc(var(--cms-preview-chip-right-offset, 12px) + var(--cms-preview-outline-offset, 12px));
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

            .cms-preview-editable:hover .cms-preview-chip,
            .cms-preview-editable:focus-within .cms-preview-chip {
                display: inline-flex !important;
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

            .sdp-priority-card--add {
                cursor: pointer;
                min-height: 140px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px dashed rgba(127, 17, 19, 0.2);
                background: linear-gradient(160deg, rgba(255,255,255,.96) 0%, rgba(250,243,236,.9) 100%);
                border-radius: 18px;
            }

            .sdp-priority-add-inner {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 24px;
                text-align: center;
            }

            .sdp-priority-add-plus {
                color: #7f1113;
                font-size: clamp(2.5rem, 5vw, 4rem);
                font-weight: 500;
                line-height: 1;
            }

            .sdp-priority-add-label {
                margin: 0;
                color: #7f1113;
                font-family: "Poppins", sans-serif;
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: .04em;
            }

            .sdp-priority-card.cms-preview-editable-card {
                position: relative;
                isolation: isolate;
            }

            .sdp-priority-card.cms-preview-editable-card::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 2;
                box-sizing: border-box;
                pointer-events: none;
                border: 2px dashed rgba(242, 201, 76, 0.95);
                border-radius: inherit;
            }

            @media (max-width: 768px) {
                .cms-preview-chip {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                }

                .cms-preview-chip svg {
                    width: 18px;
                    height: 18px;
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
                // Notify CMS editor of preview height
                let previewHeightFrame = null;

                const schedulePreviewHeight = () => {
                    if (previewHeightFrame !== null) {
                        window.cancelAnimationFrame(previewHeightFrame);
                    }
                    previewHeightFrame = window.requestAnimationFrame(() => {
                        const main = document.querySelector('.main-content');
                        const scope = main instanceof HTMLElement ? main : document.body;
                        const visible = Array.from(scope.children).filter((node) => {
                            if (!(node instanceof HTMLElement)) return false;
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
                    const obs = new ResizeObserver(() => schedulePreviewHeight());
                    obs.observe(document.body);
                    const mc = document.querySelector('.main-content');
                    if (mc) obs.observe(mc);
                }
                if (typeof MutationObserver !== 'undefined') {
                    new MutationObserver(() => schedulePreviewHeight()).observe(document.body, {
                        childList: true, subtree: true, attributes: true,
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

                // Communicate click events to parent CMS editor
                document.addEventListener('click', (event) => {
                    const chip = event.target.closest('[data-cms-edit-trigger]');
                    if (chip) {
                        event.stopPropagation();
                        window.parent?.postMessage({
                            type: 'cms-research-edit',
                            section: chip.getAttribute('data-cms-edit-trigger'),
                        }, '*');
                        return;
                    }

                    const addTrigger = event.target.closest('[data-research-plan-priority-add]');
                    if (addTrigger) {
                        window.parent?.postMessage({ type: 'cms-research-sdp-priority-add' }, '*');
                        return;
                    }

                    const editTrigger = event.target.closest('[data-research-plan-priority-edit]');
                    if (editTrigger) {
                        const card = editTrigger.closest('[data-research-plan-priority-card]');
                        window.parent?.postMessage({
                            type: 'cms-research-sdp-priority-edit',
                            index: card?.getAttribute('data-research-plan-priority-index') || '',
                            label: card?.getAttribute('data-research-plan-priority-label') || '',
                        }, '*');
                        return;
                    }

                    const deleteTrigger = event.target.closest('[data-research-plan-priority-delete]');
                    if (deleteTrigger) {
                        const card = deleteTrigger.closest('[data-research-plan-priority-card]');
                        window.parent?.postMessage({
                            type: 'cms-research-sdp-priority-delete',
                            index: card?.getAttribute('data-research-plan-priority-index') || '',
                            label: card?.getAttribute('data-research-plan-priority-label') || '',
                        }, '*');
                        return;
                    }
                });
            })();
        </script>
    @endif
</body>
</html>
