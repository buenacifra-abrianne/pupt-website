<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/students.css') }}?v={{ filemtime(public_path('assets/css/students.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $studentsCms = \App\Support\StudentsCmsContent::fromInput($studentsCms ?? [], null);
        $pageSection = $studentsCms['page'] ?? [];
        $cards = collect($studentsCms['cards'] ?? [])
            ->filter(fn ($card) => is_array($card))
            ->values();
        $organizationSections = collect($studentsCms['organization_sections'] ?? [])
            ->filter(fn ($section) => is_array($section))
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

    <main class="main-content students-review-page">

        <section
            class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="page"
                data-cms-section-label="Students Page Header"
            @endif
        >
            @if($cmsPreview)
                <button type="button" class="cms-preview-chip" data-cms-edit-trigger="page" aria-label="Edit Students Page Header">
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
                                    <img src="{{ asset($pageSection['hero_image'] ?? 'assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                                </div>
                                <div class="carousel-caption">
                                    <h2>{{ strtoupper((string) ($pageSection['title'] ?? 'Students')) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal{{ $cmsPreview ? ' active' : '' }}" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Students</strong>
            </nav>
        </section>

        <section
            class="students-contents-strip reveal{{ $cmsPreview ? ' active cms-preview-editable' : '' }}"
            @if($cmsPreview)
                data-cms-section="cards"
                data-cms-section-label="Student Cards"
            @endif
        >
            <div data-cms-boundary class="cms-preview-boundary-edge">
                <div class="students-contents-inner">
                    <div class="students-contents-head">
                        <p class="section-tag">Contents</p>
                    </div>

                    <nav class="students-cards" aria-label="Student services">
                        @if($cmsPreview)
                            <article class="students-card students-card-add" data-students-add-card-trigger tabindex="0" role="button" aria-label="Add a new student card">
                                <div class="students-card-inner">
                                    <div class="students-card-front students-card-front-add">
                                        <div class="students-card-add-inner">
                                            <span class="students-card-add-plus" aria-hidden="true">+</span>
                                            <p class="students-card-add-label">Add Card</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endif

                        @forelse($cards as $card)
                            @php
                                $cardLink = trim((string) ($card['link'] ?? ''));
                                $cardTitle = trim((string) ($card['title'] ?? ''));
                            @endphp

                            @if($cmsPreview)
                                <article
                                    class="students-card"
                                    data-cms-edit-trigger="cards"
                                    data-cms-section-label="Student Cards"
                                    data-students-card-index="{{ $loop->index }}"
                                >
                            @else
                                <a
                                    href="{{ $cardLink !== '' ? $cardLink : '#' }}"
                                    @if($cardLink !== '' && $cardLink !== '#')
                                        target="_blank" rel="noopener noreferrer"
                                    @endif
                                    class="students-card"
                                >
                            @endif
                                @if($cmsPreview)
                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                        <button type="button" class="cms-preview-card-action" data-students-card-edit title="Edit card" aria-label="Edit {{ $cardTitle !== '' ? $cardTitle : 'student card' }}">
                                            Edit
                                        </button>
                                        <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-students-card-delete title="Delete card" aria-label="Delete {{ $cardTitle !== '' ? $cardTitle : 'student card' }}">
                                            Delete
                                        </button>
                                    </div>
                                @endif

                                <div class="students-card-inner">
                                    <div class="students-card-front">
                                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="{{ $cardTitle !== '' ? $cardTitle : 'Student card' }}">
                                        <div class="students-card-copy">
                                            <h3>{{ $cardTitle !== '' ? $cardTitle : 'Student Card' }}</h3>
                                        </div>
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
                                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Student placeholder">
                                        <div class="students-card-copy">
                                            <h3>No student cards yet</h3>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforelse
                    </nav>
                </div>
            </div>
        </section>

        {{-- Student Organizations Section --}}
        <section class="students-orgs-section reveal{{ $cmsPreview ? ' active' : '' }}">
            <div class="students-orgs-inner">
                <div class="students-orgs-blurred students-orgs-live">

                @foreach($organizationSections as $sectionIndex => $organizationSection)
                <div
                    class="students-orgs-group{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                    @if($cmsPreview)
                        data-cms-section="organizations"
                        data-cms-section-label="{{ $organizationSection['title'] ?? 'Student Organizations' }}"
                    @endif
                >
                    @if($cmsPreview)
                        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="organizations" aria-label="Edit {{ $organizationSection['title'] ?? 'Student Organizations' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                            </svg>
                        </button>
                    @endif

                    <div data-cms-boundary class="cms-preview-boundary-edge">
                        <p class="section-tag">{{ $organizationSection['title'] ?? 'Student Organizations' }}</p>

                        <div class="students-orgs-grid">
                            @foreach(($organizationSection['items'] ?? []) as $orgIndex => $organization)
                                @php
                                    $orgTitle = trim((string) ($organization['title'] ?? ''));
                                    $orgAbbr = trim((string) ($organization['abbr'] ?? ''));
                                    $orgLink = trim((string) ($organization['link'] ?? '#'));
                                    $orgImage = trim((string) ($organization['image'] ?? 'assets/static_img/pupillar.jpeg'));
                                @endphp

                                @if($cmsPreview)
                                    <article
                                        class="students-org-card"
                                        data-cms-edit-trigger="organizations"
                                        data-cms-section-label="{{ $organizationSection['title'] ?? 'Student Organizations' }}"
                                        data-students-org-section-index="{{ $sectionIndex }}"
                                        data-students-org-index="{{ $orgIndex }}"
                                    >
                                @else
                                    <a href="{{ $orgLink !== '' ? $orgLink : '#' }}" target="_blank" rel="noopener noreferrer" class="students-org-card">
                                @endif
                                    @if($cmsPreview)
                                        <div class="cms-preview-card-actions" aria-label="Organization card actions">
                                            <button type="button" class="cms-preview-card-action" data-students-org-edit title="Edit organization" aria-label="Edit {{ $orgTitle !== '' ? $orgTitle : 'organization' }}">
                                                Edit
                                            </button>
                                        </div>
                                    @endif

                                    <div class="students-org-img-wrap">
                                        <img src="{{ asset($orgImage !== '' ? $orgImage : 'assets/static_img/pupillar.jpeg') }}" alt="{{ $orgAbbr !== '' ? $orgAbbr : $orgTitle }}">
                                    </div>
                                    <div class="students-org-copy">
                                        <h3>{{ $orgTitle !== '' ? $orgTitle : 'Organization' }}</h3>
                                        <span class="students-org-abbr">{{ $orgAbbr }}</span>
                                    </div>
                                @if($cmsPreview)
                                    </article>
                                @else
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
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
            .students-contents-strip.cms-preview-editable,
            .students-orgs-group.cms-preview-editable {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                width: 100% !important;
                max-width: 100% !important;
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
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
            }

            .cms-preview-chip svg {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }

            .students-card[data-cms-edit-trigger] {
                cursor: pointer;
            }

            .students-org-card[data-cms-edit-trigger] {
                position: relative;
                cursor: pointer;
            }

            .cms-preview-card-actions {
                position: absolute;
                top: 14px;
                right: 14px;
                z-index: 12;
                display: flex;
                gap: 8px;
                opacity: 0;
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
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
            }

            .cms-preview-card-action-delete {
                background: rgba(80, 10, 12, 0.96);
            }

            .students-card[data-cms-edit-trigger]:hover .cms-preview-card-actions,
            .students-card[data-cms-edit-trigger]:focus-within .cms-preview-card-actions,
            .students-org-card[data-cms-edit-trigger]:hover .cms-preview-card-actions,
            .students-org-card[data-cms-edit-trigger]:focus-within .cms-preview-card-actions {
                opacity: 1;
                transform: none;
            }

            .students-card-add {
                cursor: pointer;
                border-style: dashed;
                border-width: 2px;
                border-color: rgba(127, 17, 19, 0.22);
            }

            .students-card-front-add {
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at 50% 35%, rgba(215, 170, 67, 0.18) 0%, transparent 36%),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 239, 232, 0.94) 100%);
            }

            .students-card-add-inner {
                display: grid;
                place-items: center;
                gap: 12px;
                text-align: center;
                color: var(--maroon);
            }

            .students-card-add-plus {
                width: 62px;
                height: 62px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, rgba(127, 17, 19, 0.96) 0%, rgba(79, 9, 12, 0.99) 100%);
                color: #fffaf4;
                font-size: 1.9rem;
                line-height: 1;
                box-shadow: 0 16px 28px rgba(79, 9, 12, 0.2);
            }

            .students-card-add-label {
                margin: 0;
                font-size: 0.88rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            @media (max-width: 768px) {
                .hero-shell,
                .students-contents-strip.cms-preview-editable,
                .students-orgs-group.cms-preview-editable {
                    --cms-preview-outline-offset: 8px;
                    --cms-preview-chip-top-offset: 50%;
                    --cms-preview-chip-right-offset: 8px;
                }

                .cms-preview-chip {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                }

                .cms-preview-card-actions {
                    top: 10px;
                    right: 10px;
                }
            }
        </style>
    @endif
</body>
</html>
