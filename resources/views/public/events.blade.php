<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/news&events.css') }}?v={{ filemtime(public_path('assets/css/news&events.css')) }}">
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
        $eventsCms = \App\Support\EventsCmsContent::fromInput($eventsCms ?? [], null);
        $pageSection = $eventsCms['page'] ?? [];
        $eventCards = collect($eventsCms['cards'] ?? [])
            ->filter(fn ($card) => is_array($card))
            ->map(fn ($card, $index) => array_merge($card, ['source_index' => $index]))
            ->values();
        $today = now()->toDateString();
        $cardCollections = \App\Support\EventsCmsContent::displayCollections($eventCards, $today);
        $displayCards = collect($cardCollections['active'] ?? []);
        $expiredCards = collect($cardCollections['expired'] ?? []);
        $featuredCard = $cardCollections['featured'] ?? null;
        $ongoingCards = collect($cardCollections['ongoing'] ?? []);
        $upcomingCards = collect($cardCollections['upcoming'] ?? []);

        $formatDate = static function (?string $date, string $format = 'F d, Y'): string {
            $value = trim((string) $date);
            if ($value === '') {
                return '';
            }

            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format($format);
            } catch (\Throwable $e) {
                return '';
            }
        };

        $formatTime = static function (?string $time): string {
            $value = trim((string) $time);
            if ($value === '') {
                return '';
            }

            try {
                return str_replace(['AM', 'PM'], ['A.M.', 'P.M.'], \Carbon\Carbon::createFromFormat('H:i', $value)->format('g:i A'));
            } catch (\Throwable $e) {
                return '';
            }
        };

        $formatDateLine = static function (array $card) use ($formatDate, $formatTime): string {
            $date = $formatDate($card['event_date'] ?? null);
            $start = $formatTime($card['start_time'] ?? null);
            $end = $formatTime($card['end_time'] ?? null);
            $time = trim($start.($end !== '' ? ' - '.$end : ''));

            if ($date === '') {
                return $time;
            }

            return $time !== '' ? $date.' | '.$time : $date;
        };

        $formatChipDate = static function (?string $date): string {
            $value = trim((string) $date);
            if ($value === '') {
                return 'TBA';
            }

            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('M d');
            } catch (\Throwable $e) {
                return 'TBA';
            }
        };

        $summaryHtmlFor = static function (array $card): string {
            $summary = trim((string) ($card['summary'] ?? ''));
            if ($summary !== '') {
                return \App\Support\RichText::sanitize($summary);
            }

            return \App\Support\RichText::sanitize((string) ($card['content'] ?? ''));
        };

        $plainText = static fn (mixed $value): string => \App\Support\PlainText::normalize((string) $value);
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
        <section class="about-shell page-shell ne-page-shell">
            <nav class="ne-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Events</strong>
            </nav>

            <section
                class="ne-page-intro reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                @if($cmsPreview)
                    data-cms-section="page"
                    data-cms-section-label="Events Page Header"
                @endif
            >
                @if($cmsPreview)
                    <button type="button" class="cms-preview-chip" data-cms-edit-trigger="page" aria-label="Edit Events Page Header">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                        </svg>
                    </button>
                @endif

                <div data-cms-boundary class="cms-preview-boundary-edge">
                    <h1 class="ne-page-title">{{ $plainText($pageSection['title'] ?? 'Events') }}</h1>
                    <div class="ne-page-copy ne-rich-copy">
                        {!! \App\Support\RichText::sanitize($pageSection['description'] ?? '') !!}
                    </div>
                </div>
            </section>

            @if($featuredCard)
                <section
                    id="featuredEventMount"
                    class="ne-featured reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
                    aria-label="Featured event"
                    @if($cmsPreview)
                        data-cms-featured-card-index="{{ $featuredCard['source_index'] ?? 0 }}"
                    @endif
                >
                    @if($cmsPreview)
                        <button
                            type="button"
                            class="cms-preview-chip"
                            data-cms-featured-edit-trigger
                            aria-label="Edit Featured Event"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
                            </svg>
                        </button>
                    @endif

                    <div data-cms-boundary class="cms-preview-boundary-full">
                        <div class="ne-featured-img">
                            <img src="{{ \App\Support\EventsCmsContent::resolveImagePath($featuredCard['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}" alt="{{ $plainText($featuredCard['title'] ?? 'Featured event') }}">
                            <span class="ne-featured-badge">Featured Event</span>
                        </div>
                        <div class="ne-featured-body">
                            <span class="ne-tag">{{ \App\Support\EventsCmsContent::categoryLabel($featuredCard['category'] ?? 'events') }}</span>
                            <h2 class="ne-featured-title">{{ $plainText($featuredCard['title'] ?? '') }}</h2>
                            <p class="ne-featured-meta">{{ $formatDateLine($featuredCard) }}</p>
                            <div class="ne-featured-desc ne-rich-copy">{!! $summaryHtmlFor($featuredCard) !!}</div>
                            <a
                                href="#"
                                class="ne-btn-gold"
                                data-ne-modal-trigger
                                data-tag="{{ \App\Support\EventsCmsContent::categoryLabel($featuredCard['category'] ?? 'events') }}"
                                data-date="{{ $formatDateLine($featuredCard) }}"
                                data-title="{{ $plainText($featuredCard['title'] ?? '') }}"
                                data-summary-html="{{ e($summaryHtmlFor($featuredCard)) }}"
                                data-location="{{ $plainText($featuredCard['location'] ?? '') }}"
                                data-image="{{ \App\Support\EventsCmsContent::resolveImagePath($featuredCard['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}"
                                data-content-html="{{ e(\App\Support\RichText::sanitize($featuredCard['content'] ?? '')) }}"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>
                </section>
            @endif

            <section class="ne-events-main reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}">
                <div>
                    @unless($cmsPreview)
                        <section class="ne-events-section">
                            <div class="ne-events-header">
                                <p class="ne-section-label">Scheduled Events</p>
                            </div>

                            <div class="ne-events-columns">
                                <div class="ne-events-col">
                                    <h3 class="ne-col-title ne-col-ongoing"><span class="ne-pulse"></span> Ongoing</h3>
                                    @forelse($ongoingCards as $card)
                                        <div class="ne-event-item ne-ongoing">
                                            <span class="ne-event-date">{{ $formatChipDate($card['event_date'] ?? null) }}</span>
                                            <p class="ne-event-name">{{ $plainText($card['title'] ?? '') }}</p>
                                        </div>
                                    @empty
                                        <div class="ne-event-item ne-ongoing">
                                            <span class="ne-event-date">TBA</span>
                                            <p class="ne-event-name">No ongoing events right now.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="ne-events-divider" aria-hidden="true"></div>

                                <div class="ne-events-col">
                                    <h3 class="ne-col-title ne-col-upcoming">Upcoming</h3>
                                    @forelse($upcomingCards as $card)
                                        <div class="ne-event-item">
                                            <span class="ne-event-date">{{ $formatChipDate($card['event_date'] ?? null) }}</span>
                                            <p class="ne-event-name">{{ $plainText($card['title'] ?? '') }}</p>
                                        </div>
                                    @empty
                                        <div class="ne-event-item">
                                            <span class="ne-event-date">TBA</span>
                                            <p class="ne-event-name">No upcoming events yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </section>
                    @endunless

                    <div class="ne-filter-bar">
                        <span class="ne-filter-label">Filter By</span>
                        <div class="ne-filters" role="group" aria-label="Filter events">
                            <button class="ne-filter active" type="button" data-filter="all">All</button>
                            <button class="ne-filter" type="button" data-filter="academic">Academic</button>
                            <button class="ne-filter" type="button" data-filter="events">Events</button>
                            <button class="ne-filter" type="button" data-filter="research">Research</button>
                            <button class="ne-filter" type="button" data-filter="student-life">Student Life</button>
                        </div>
                    </div>

                    <section class="ne-card-grid" aria-label="Event cards" data-ne-card-grid>
                        @if($cmsPreview)
                            <article class="ne-card ne-card-add" data-cms-add-card-trigger tabindex="0" role="button" aria-label="Add a new event card">
                                <div class="ne-card-add-inner">
                                    <span class="ne-card-add-plus" aria-hidden="true">+</span>
                                    <p class="ne-card-add-label">Add Event</p>
                                </div>
                            </article>
                        @endif

                        @foreach($displayCards as $card)
                            <article
                                class="ne-card"
                                data-events-card
                                data-filter="{{ $card['category'] ?? 'events' }}"
                                @if($cmsPreview)
                                    data-cms-card-index="{{ $card['source_index'] ?? 0 }}"
                                @endif
                            >
                                @if($cmsPreview)
                                    <div class="cms-preview-card-actions" aria-label="Card actions">
                                        <button type="button" class="cms-preview-card-action" data-cms-card-edit title="Edit card" aria-label="Edit {{ $plainText($card['title'] ?? 'event card') }}">
                                            Edit
                                        </button>
                                        <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-cms-card-delete title="Delete card" aria-label="Delete {{ $plainText($card['title'] ?? 'event card') }}">
                                            Delete
                                        </button>
                                    </div>
                                @endif
                                <div class="ne-card-img">
                                    <img src="{{ \App\Support\EventsCmsContent::resolveImagePath($card['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}" alt="{{ $plainText($card['title'] ?? 'Event card') }}" loading="lazy">
                                    <span class="ne-card-tag">{{ \App\Support\EventsCmsContent::categoryLabel($card['category'] ?? 'events') }}</span>
                                </div>
                                <div class="ne-card-body">
                                    <p class="ne-card-date">{{ $formatDate($card['event_date'] ?? null, 'F d, Y') }}</p>
                                    <h3 class="ne-card-title">{{ $plainText($card['title'] ?? '') }}</h3>
                                    <div class="ne-card-desc ne-rich-copy">{!! $summaryHtmlFor($card) !!}</div>
                                    <hr class="ne-card-rule">
                                    <div class="ne-card-foot">
                                        <span class="ne-card-loc">{{ $plainText($card['location'] ?? 'Location to be announced') }}</span>
                                        <a
                                            href="#"
                                            class="ne-read-more"
                                            data-ne-modal-trigger
                                            data-tag="{{ \App\Support\EventsCmsContent::categoryLabel($card['category'] ?? 'events') }}"
                                            data-date="{{ $formatDateLine($card) }}"
                                            data-title="{{ $plainText($card['title'] ?? '') }}"
                                            data-summary-html="{{ e($summaryHtmlFor($card)) }}"
                                            data-location="{{ $plainText($card['location'] ?? '') }}"
                                            data-image="{{ \App\Support\EventsCmsContent::resolveImagePath($card['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}"
                                            data-content-html="{{ e(\App\Support\RichText::sanitize($card['content'] ?? '')) }}"
                                        >
                                            Read More
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <div class="ne-empty-state" data-ne-empty-state @if($displayCards->isNotEmpty()) hidden @endif>
                        No events available yet.
                    </div>

                    @if($cmsPreview)
                        <section class="ne-expired-preview-section" aria-label="Expired events preview">
                            <div class="ne-expired-preview-header">
                                <div>
                                    <h3 class="ne-expired-preview-title">Expired Events</h3>
                                    <p class="ne-expired-preview-copy">These are finished events from the public page. You can view and remove them here in the CMS.</p>
                                </div>
                                <button
                                    type="button"
                                    class="ne-expired-remove-selected"
                                    data-ne-expired-remove-selected
                                    @if($expiredCards->isEmpty()) hidden @endif
                                    disabled
                                >
                                    Remove Selected
                                </button>
                            </div>

                            <section class="ne-card-grid ne-card-grid-expired" aria-label="Expired event cards">
                                @foreach($expiredCards as $card)
                                    <article
                                        class="ne-card ne-card-expired"
                                        data-cms-card-index="{{ $card['source_index'] ?? 0 }}"
                                        data-ne-expired-card
                                        tabindex="0"
                                        aria-label="Select {{ $plainText($card['title'] ?? 'expired event') }}"
                                    >
                                        <button
                                            type="button"
                                            class="ne-expired-select-toggle"
                                            data-ne-expired-select
                                            aria-pressed="false"
                                            aria-label="Select {{ $plainText($card['title'] ?? 'expired event') }}"
                                        >
                                            Select
                                        </button>

                                        <div class="cms-preview-card-actions" aria-label="Expired card actions">
                                            <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-cms-card-delete title="Remove card" aria-label="Remove {{ $plainText($card['title'] ?? 'expired event card') }}">
                                                Remove
                                            </button>
                                        </div>

                                        <div class="ne-card-img">
                                            <img src="{{ \App\Support\EventsCmsContent::resolveImagePath($card['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}" alt="{{ $plainText($card['title'] ?? 'Expired event card') }}" loading="lazy">
                                            <span class="ne-card-tag">Expired</span>
                                        </div>
                                        <div class="ne-card-body">
                                            <p class="ne-card-date">Expired on {{ $formatDate($card['event_date'] ?? null, 'F d, Y') }}</p>
                                            <h3 class="ne-card-title">{{ $plainText($card['title'] ?? '') }}</h3>
                                            <div class="ne-card-desc ne-rich-copy">{!! $summaryHtmlFor($card) !!}</div>
                                            <hr class="ne-card-rule">
                                            <div class="ne-card-foot">
                                                <span class="ne-card-loc">{{ $plainText($card['location'] ?? 'Location to be announced') }}</span>
                                                <a
                                                    href="#"
                                                    class="ne-read-more"
                                                    data-ne-modal-trigger
                                                    data-tag="Expired Event"
                                                    data-date="{{ $formatDateLine($card) }}"
                                                    data-title="{{ $plainText($card['title'] ?? '') }}"
                                                    data-summary-html="{{ e($summaryHtmlFor($card)) }}"
                                                    data-location="{{ $plainText($card['location'] ?? '') }}"
                                                    data-image="{{ \App\Support\EventsCmsContent::resolveImagePath($card['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}"
                                                    data-content-html="{{ e(\App\Support\RichText::sanitize($card['content'] ?? '')) }}"
                                                >
                                                    View Event
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </section>

                            <div class="ne-empty-state ne-expired-preview-empty" data-ne-expired-empty-state @if($expiredCards->isNotEmpty()) hidden @endif>
                                No expired events to review.
                            </div>
                        </section>
                    @endif
                </div>
            </section>
        </section>

        <div class="ne-modal-overlay" id="detailsModal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Event details">
            <div class="ne-modal-card">
                <button class="ne-modal-close" type="button" aria-label="Close">&times;</button>
                <div class="ne-modal-body">
                    <div class="ne-modal-img-wrap">
                        <img id="modalImg" src="" alt="">
                    </div>
                    <div class="ne-modal-content">
                        <span class="ne-tag" id="modalTag"></span>
                        <p class="ne-modal-date" id="modalDate"></p>
                        <h3 class="ne-modal-title" id="modalTitle"></h3>
                        <p class="ne-modal-loc" id="modalLocation"></p>
                        <hr class="ne-modal-rule">
                        <p class="ne-modal-details-label" id="modalDetailsLabel">Details</p>
                        <div class="ne-modal-text" id="modalText"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @unless($cmsPreview)
        <pup-footer></pup-footer>
        <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
        <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    @endunless

    @if($cmsPreview)
        <style>
            .ne-page-intro,
            .ne-featured,
            .ne-events-main {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                position: relative;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .ne-page-shell.page-shell {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .ne-page-intro.cms-preview-editable {
                margin-left: 0 !important;
                margin-right: 0 !important;
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

            .ne-page-intro.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge {
                width: 100%;
                margin: 0;
                padding: 32px 34px;
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

            .ne-page-intro.cms-preview-editable .ne-page-copy {
                max-width: none;
                width: 100%;
            }

            .ne-page-intro.cms-preview-editable {
                padding: 0;
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
                display: none !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
            }

            .cms-preview-chip svg {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }

            .ne-card {
                position: relative;
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

            .ne-card[data-cms-card-index] {
                position: relative;
                isolation: isolate;
                transition: none;
            }

            .ne-card[data-cms-card-index]::after {
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

            .ne-card[data-cms-card-index]:hover {
                filter: none;
                box-shadow: inherit;
                transform: none;
            }

            .ne-card[data-cms-card-index]:hover::after,
            .ne-card[data-cms-card-index]:focus-within::after {
                border-color: rgba(255, 220, 92, 1);
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.32),
                    0 0 0 5px rgba(242, 201, 76, 0.2);
            }

            .ne-expired-preview-section {
                margin: 0 var(--ne-page-gutter) 52px;
                padding: 26px 0 0;
                border-top: 1px solid rgba(74, 74, 74, 0.16);
            }

            .ne-expired-preview-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 22px;
            }

            .ne-expired-preview-title {
                margin: 6px 0 8px;
                color: #4f4f4f;
                font-family: "Poppins", sans-serif;
                font-size: clamp(1.3rem, 2.2vw, 1.7rem);
                font-weight: 800;
                line-height: 1.1;
            }

            .ne-expired-preview-copy {
                max-width: 760px;
                margin: 0;
                color: #767676;
                font-size: 0.95rem;
                line-height: 1.7;
            }

            .ne-expired-remove-selected {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 164px;
                min-height: 46px;
                padding: 0 18px;
                border-radius: 999px;
                border: 1px solid rgba(82, 82, 82, 0.18);
                background: linear-gradient(160deg, #6a6a6a 0%, #464646 100%);
                color: #faf8f6;
                font-size: 0.85rem;
                font-weight: 800;
                letter-spacing: 0.02em;
                box-shadow: 0 12px 22px rgba(72, 72, 72, 0.16);
                cursor: pointer;
            }

            .ne-expired-remove-selected[disabled] {
                opacity: 0.55;
                cursor: not-allowed;
                box-shadow: none;
            }

            .ne-card-grid-expired {
                padding-left: 0;
                padding-right: 0;
                padding-bottom: 0;
            }

            .ne-card-expired {
                background: linear-gradient(165deg, #f2f2f2 0%, #dcdcdc 100%);
                border-color: rgba(84, 84, 84, 0.16);
                box-shadow: 0 12px 28px rgba(80, 80, 80, 0.12);
                cursor: pointer;
            }

            .ne-card-expired.is-selected {
                outline: 3px solid rgba(127, 17, 19, 0.92);
                outline-offset: 0;
                box-shadow: 0 0 0 1px rgba(127, 17, 19, 0.28), 0 18px 32px rgba(70, 70, 70, 0.18);
            }

            .ne-card-expired:focus-visible {
                outline: 3px solid rgba(127, 17, 19, 0.42);
                outline-offset: 0;
            }

            .ne-expired-select-toggle {
                position: absolute;
                top: 14px;
                left: 14px;
                z-index: 13;
                min-width: 74px;
                height: 34px;
                padding: 0 12px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 999px;
                background: rgba(52, 52, 52, 0.78);
                color: #f7f7f7;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.03em;
                cursor: pointer;
            }

            .ne-card-expired.is-selected .ne-expired-select-toggle {
                background: linear-gradient(160deg, #565656 0%, #3a3a3a 100%);
                border-color: rgba(255, 255, 255, 0.32);
            }

            .ne-card-expired .ne-card-img {
                background: linear-gradient(160deg, #d0d0d0 0%, #b5b5b5 100%);
            }

            .ne-card-expired .ne-card-img img {
                filter: grayscale(1) saturate(0.2) brightness(0.94);
            }

            .ne-card-expired .ne-card-tag {
                background: rgba(58, 58, 58, 0.78);
                border-color: rgba(255, 255, 255, 0.18);
                color: #f8f8f8;
            }

            .ne-card-expired .ne-card-date,
            .ne-card-expired .ne-card-loc,
            .ne-card-expired .ne-card-desc,
            .ne-card-expired .ne-read-more {
                color: #5f5f5f;
            }

            .ne-card-expired .ne-card-title {
                color: #303030;
            }

            .ne-card-expired .ne-card-rule {
                border-top-color: rgba(78, 78, 78, 0.12);
            }

            .ne-card-expired:hover,
            .ne-card-expired:focus-within {
                transform: none !important;
                box-shadow: 0 12px 28px rgba(80, 80, 80, 0.12) !important;
            }

            .ne-card-expired:hover .ne-card-img img,
            .ne-card-expired:focus-within .ne-card-img img {
                transform: none !important;
                filter: grayscale(1) saturate(0.2) brightness(0.94) !important;
            }

            .ne-expired-preview-empty {
                margin: 20px 0 0;
                background: linear-gradient(160deg, #f5f5f5 0%, #e3e3e3 100%);
                border-color: rgba(84, 84, 84, 0.14);
                color: #646464;
                box-shadow: none;
            }

            @media (max-width: 768px) {
                .ne-page-intro,
                .ne-featured,
                .ne-events-main {
                    --cms-preview-outline-offset: 8px;
                    --cms-preview-chip-right-offset: 8px;
                }

                .cms-preview-chip {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                }

                .cms-preview-card-actions {
                    opacity: 1;
                    transform: none;
                }

                .ne-expired-preview-header {
                    flex-direction: column;
                }
            }
        </style>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cmsPreview = @json($cmsPreview);
            const revealElements = Array.from(document.querySelectorAll('.reveal'));
            const filters = Array.from(document.querySelectorAll('.ne-filter'));
            const addCardTrigger = document.querySelector('[data-cms-add-card-trigger]');
            const featuredSection = document.querySelector('[data-cms-featured-card-index]');
            const featuredEditTrigger = document.querySelector('[data-cms-featured-edit-trigger]');
            const emptyState = document.querySelector('[data-ne-empty-state]');
            const expiredRemoveSelectedButton = document.querySelector('[data-ne-expired-remove-selected]');
            const expiredEmptyState = document.querySelector('[data-ne-expired-empty-state]');
            const modal = document.getElementById('detailsModal');
            const modalImg = document.getElementById('modalImg');
            const modalTag = document.getElementById('modalTag');
            const modalDate = document.getElementById('modalDate');
            const modalTitle = document.getElementById('modalTitle');
            const modalLocation = document.getElementById('modalLocation');
            const modalDetailsLabel = document.getElementById('modalDetailsLabel');
            const modalText = document.getElementById('modalText');
            const closeBtn = modal?.querySelector('.ne-modal-close');
            let lastTrigger = null;
            const selectedExpiredCards = new Set();
            let lockedScrollY = 0;

            // CMS preview does not load the public reveal script, so force visible content there.
            if (cmsPreview) {
                revealElements.forEach((element) => element.classList.add('active'));
            }

            const getCards = () => Array.from(document.querySelectorAll('[data-events-card]'));
            const getCmsCards = () => Array.from(document.querySelectorAll('.ne-card[data-cms-card-index]'));
            const getExpiredCards = () => Array.from(document.querySelectorAll('.ne-card-expired[data-cms-card-index]'));

            const syncExpiredSelectionState = () => {
                const expiredCards = getExpiredCards();

                expiredCards.forEach((card) => {
                    const cardIndex = String(card.getAttribute('data-cms-card-index') || '');
                    const isSelected = selectedExpiredCards.has(cardIndex);
                    const toggle = card.querySelector('[data-ne-expired-select]');

                    card.classList.toggle('is-selected', isSelected);

                    if (toggle) {
                        toggle.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                        toggle.textContent = isSelected ? 'Selected' : 'Select';
                    }
                });

                if (expiredRemoveSelectedButton) {
                    const selectedCount = selectedExpiredCards.size;
                    expiredRemoveSelectedButton.disabled = selectedCount === 0;
                    expiredRemoveSelectedButton.textContent = selectedCount > 0
                        ? `Remove Selected (${selectedCount})`
                        : 'Remove Selected';
                }
            };

            const syncExpiredPreviewState = () => {
                const remainingExpiredCards = getExpiredCards();
                const remainingIndexes = new Set(
                    remainingExpiredCards
                        .map((card) => String(card.getAttribute('data-cms-card-index') || ''))
                        .filter((value) => value !== '')
                );

                Array.from(selectedExpiredCards).forEach((cardIndex) => {
                    if (!remainingIndexes.has(cardIndex)) {
                        selectedExpiredCards.delete(cardIndex);
                    }
                });

                if (expiredRemoveSelectedButton) {
                    expiredRemoveSelectedButton.hidden = remainingExpiredCards.length === 0;
                }

                if (!expiredEmptyState) {
                    syncExpiredSelectionState();
                    return;
                }

                expiredEmptyState.hidden = remainingExpiredCards.length !== 0;
                syncExpiredSelectionState();
            };

            const applyFilter = (filterKey) => {
                let visibleCount = 0;

                getCards().forEach((card) => {
                    const matches = filterKey === 'all' || card.dataset.filter === filterKey;
                    card.hidden = !matches;
                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.hidden = visibleCount !== 0;
                    emptyState.textContent = filterKey === 'all'
                        ? 'No events available yet.'
                        : 'No events match this filter yet.';
                }
            };

            filters.forEach((button) => {
                button.addEventListener('click', () => {
                    filters.forEach((item) => item.classList.toggle('active', item === button));
                    applyFilter(button.dataset.filter || 'all');
                });
            });

            const openModal = (trigger) => {
                if (!modal || !trigger) {
                    return;
                }

                const decodeHtmlEntities = (value) => {
                    let current = String(value || '');
                    const textarea = document.createElement('textarea');

                    for (let index = 0; index < 5; index += 1) {
                        textarea.innerHTML = current;
                        const decoded = textarea.value;
                        if (decoded === current) {
                            break;
                        }
                        current = decoded;
                    }

                    return current;
                };
                lastTrigger = trigger;
                modalImg.src = trigger.dataset.image || '';
                modalImg.alt = decodeHtmlEntities(trigger.dataset.title || 'Event image');
                modalTag.textContent = decodeHtmlEntities(trigger.dataset.tag || '');
                modalDate.textContent = decodeHtmlEntities(trigger.dataset.date || '');
                modalTitle.textContent = decodeHtmlEntities(trigger.dataset.title || '');
                const summaryHtml = decodeHtmlEntities(trigger.dataset.summaryHtml || '');
                const contentHtml = decodeHtmlEntities(trigger.dataset.contentHtml || '');
                const detailsHtml = contentHtml.trim() !== '' ? contentHtml : summaryHtml;

                modalLocation.textContent = decodeHtmlEntities(trigger.dataset.location || '');
                modalLocation.hidden = modalLocation.textContent.trim() === '';
                modalText.innerHTML = detailsHtml;
                modalDetailsLabel.hidden = modalText.textContent.trim() === '';

                lockedScrollY = window.scrollY || window.pageYOffset || 0;
                document.documentElement.classList.add('modal-open');
                document.body.classList.add('modal-open');
                document.body.style.top = `-${lockedScrollY}px`;

                modal.classList.remove('closing');
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
            };

            const closeModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.add('closing');

                window.setTimeout(() => {
                    modal.classList.remove('show', 'closing');
                    modal.setAttribute('aria-hidden', 'true');
                    document.documentElement.classList.remove('modal-open');
                    document.body.classList.remove('modal-open');
                    document.body.style.top = '';
                    window.scrollTo(0, lockedScrollY);
                    lastTrigger?.focus();
                    lastTrigger = null;
                }, 350);
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-ne-modal-trigger]');
                if (trigger) {
                    event.preventDefault();
                    openModal(trigger);
                    return;
                }

                if (event.target === modal || event.target.closest('.ne-modal-close')) {
                    event.preventDefault();
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal?.classList.contains('show')) {
                    closeModal();
                }
            });

            closeBtn?.addEventListener('click', closeModal);
            applyFilter('all');

            if (!cmsPreview) {
                return;
            }

            let previewHeightFrame = null;
            const sectionTargets = Array.from(document.querySelectorAll('[data-cms-section]'));
            const postSection = (section, label) => {
                window.parent?.postMessage({
                    type: 'cms-events-edit',
                    section,
                    label: label || section,
                }, '*');
            };

            const requestAddCard = () => {
                window.parent?.postMessage({
                    type: 'cms-events-add-card',
                    section: 'cards',
                    label: 'Add Event Card',
                }, '*');
            };

            const requestEditCard = (cardIndex) => {
                window.parent?.postMessage({
                    type: 'cms-events-edit-card',
                    section: 'cards',
                    label: 'Edit Event Card',
                    cardIndex: Number(cardIndex),
                }, '*');
            };

            const requestDeleteCard = (cardIndex) => {
                window.parent?.postMessage({
                    type: 'cms-events-delete-card',
                    section: 'cards',
                    label: 'Delete Event Card',
                    cardIndex: Number(cardIndex),
                }, '*');
            };

            const requestDeleteExpiredCards = (cardIndexes) => {
                const normalizedIndexes = Array.from(new Set(
                    (Array.isArray(cardIndexes) ? cardIndexes : [])
                        .map((value) => Number(value))
                        .filter((value) => Number.isFinite(value))
                ));

                if (!normalizedIndexes.length) {
                    return;
                }

                window.parent?.postMessage({
                    type: 'cms-events-delete-expired-cards',
                    section: 'cards',
                    label: 'Remove Expired Events',
                    cardIndexes: normalizedIndexes,
                }, '*');
            };

            sectionTargets.forEach((target) => {
                const section = target.getAttribute('data-cms-section') || '';
                const label = target.getAttribute('data-cms-section-label') || section;
                const chip = target.querySelector('[data-cms-edit-trigger]');

                if (section === 'cards') {
                    return;
                }

                const openSectionEditor = (event) => {
                    if (event.target.closest('[data-cms-card-index], [data-ne-modal-trigger], .ne-filter, [data-ne-expired-select], [data-ne-expired-remove-selected]')) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    postSection(section, label);
                };

                target.addEventListener('mouseenter', () => target.classList.add('is-active'));
                target.addEventListener('mouseleave', () => target.classList.remove('is-active'));
                target.addEventListener('click', openSectionEditor);

                chip?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    postSection(section, label);
                });
            });

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
                        return styles.display !== 'none' && styles.visibility !== 'hidden' && styles.position !== 'fixed';
                    });

                    const contentBottom = visible.reduce((maxBottom, node) => {
                        return Math.max(maxBottom, node.offsetTop + node.offsetHeight);
                    }, 0);
                    const height = Math.max(
                        contentBottom,
                        scope.scrollHeight,
                        document.documentElement?.scrollHeight || 0,
                        document.body?.scrollHeight || 0
                    );

                    window.parent?.postMessage({
                        type: 'cms-events-preview-height',
                        height: Math.max(1, Math.ceil(height)),
                    }, '*');

                    previewHeightFrame = null;
                });
            };

            getCmsCards().forEach((card) => {
                const editButton = card.querySelector('[data-cms-card-edit]');
                const deleteButton = card.querySelector('[data-cms-card-delete]');

                const openCardEditor = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    requestEditCard(card.getAttribute('data-cms-card-index'));
                };

                const deleteCard = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    requestDeleteCard(card.getAttribute('data-cms-card-index'));
                };

                editButton?.addEventListener('click', openCardEditor);
                deleteButton?.addEventListener('click', deleteCard);
            });

            getExpiredCards().forEach((card) => {
                const cardIndex = String(card.getAttribute('data-cms-card-index') || '');
                const selectToggle = card.querySelector('[data-ne-expired-select]');
                const toggleBlockedSelector = 'a, button, input, select, textarea, label, [data-ne-modal-trigger], [data-cms-card-delete], [data-ne-expired-select], .cms-preview-card-actions';

                const toggleSelection = () => {
                    if (cardIndex === '') {
                        return;
                    }

                    if (selectedExpiredCards.has(cardIndex)) {
                        selectedExpiredCards.delete(cardIndex);
                    } else {
                        selectedExpiredCards.add(cardIndex);
                    }

                    syncExpiredSelectionState();
                };

                selectToggle?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    toggleSelection();
                });

                card.addEventListener('click', (event) => {
                    if (event.target.closest(toggleBlockedSelector)) {
                        return;
                    }

                    toggleSelection();
                });

                card.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    if (event.target.closest(toggleBlockedSelector) && event.target !== card) {
                        return;
                    }

                    event.preventDefault();
                    toggleSelection();
                });

            });

            window.addEventListener('message', (event) => {
                const data = event.data || {};
                if (!data || !data.type) {
                    return;
                }

                if (data.type === 'cms-events-prune-card') {
                    const targetIndex = Number(data.cardIndex);
                    if (!Number.isFinite(targetIndex)) {
                        return;
                    }

                    const targetCard = document.querySelector(`.ne-card[data-cms-card-index="${targetIndex}"]`);
                    if (!targetCard) {
                        return;
                    }

                    targetCard.remove();
                    applyFilter('all');
                    syncExpiredPreviewState();
                    schedulePreviewHeight();
                    return;
                }

                if (data.type === 'cms-events-prune-cards') {
                    const targetIndexes = Array.from(new Set(
                        (Array.isArray(data.cardIndexes) ? data.cardIndexes : [])
                            .map((value) => Number(value))
                            .filter((value) => Number.isFinite(value))
                    ));

                    if (!targetIndexes.length) {
                        return;
                    }

                    targetIndexes.forEach((cardIndex) => {
                        document.querySelector(`.ne-card[data-cms-card-index="${cardIndex}"]`)?.remove();
                    });

                    applyFilter('all');
                    syncExpiredPreviewState();
                    schedulePreviewHeight();
                }
            });

            addCardTrigger?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                requestAddCard();
            });

            featuredEditTrigger?.addEventListener('click', (event) => {
                const featuredCardIndex = Number(featuredSection?.getAttribute('data-cms-featured-card-index'));
                if (!Number.isFinite(featuredCardIndex)) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                requestEditCard(featuredCardIndex);
            });

            addCardTrigger?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                requestAddCard();
            });

            expiredRemoveSelectedButton?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                requestDeleteExpiredCards(Array.from(selectedExpiredCards));
            });

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
            syncExpiredPreviewState();
            schedulePreviewHeight();
        });
    </script>
<script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
</body>
</html>
