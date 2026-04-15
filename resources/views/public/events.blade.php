<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/news&events.css') }}?v={{ filemtime(public_path('assets/css/news&events.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $eventsCms = \App\Support\EventsCmsContent::fromInput($eventsCms ?? [], null);
        $pageSection = $eventsCms['page'] ?? [];
        $eventCards = collect($eventsCms['cards'] ?? [])
            ->filter(fn ($card) => is_array($card))
            ->map(fn ($card, $index) => array_merge($card, ['source_index' => $index]))
            ->values();
        $sortedCards = $eventCards
            ->sortBy(fn ($card) => ($card['event_date'] ?? '9999-12-31').'|'.($card['start_time'] ?? '99:99').'|'.($card['title'] ?? ''))
            ->values();
        $today = now()->toDateString();
        $isExpiredCard = static function (array $card) use ($today): bool {
            $date = trim((string) ($card['event_date'] ?? ''));
            return $date !== '' && $date < $today;
        };
        $expiredCards = $sortedCards->filter($isExpiredCard)->values();
        $displayCards = $cmsPreview
            ? $sortedCards->reject($isExpiredCard)->values()
            : $sortedCards;
        $featuredCard = $eventCards->first(fn ($card) => !empty($card['featured']));
        $ongoingCards = $sortedCards->filter(fn ($card) => ($card['event_date'] ?? '') === $today)->values();
        $upcomingCards = $sortedCards->filter(fn ($card) => ($card['event_date'] ?? '') > $today)->values();

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

        $summaryFor = static function (array $card): string {
            $summary = trim((string) ($card['summary'] ?? ''));
            if ($summary !== '') {
                return $summary;
            }

            return \Illuminate\Support\Str::limit(strip_tags((string) ($card['content'] ?? '')), 170);
        };

        $summaryHtmlFor = static function (array $card): string {
            $summary = trim((string) ($card['summary'] ?? ''));
            if ($summary !== '') {
                return \App\Support\RichText::sanitize($summary);
            }

            return \App\Support\RichText::sanitize((string) ($card['content'] ?? ''));
        };
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

                <div data-cms-boundary class="cms-preview-boundary-full">
                    <p class="ne-page-kicker layout-kicker">{{ $pageSection['eyebrow'] ?? 'Campus Calendar' }}</p>
                    <h1 class="ne-page-title">{{ $pageSection['title'] ?? 'Events' }}</h1>
                    <div class="ne-page-copy ne-rich-copy">
                        {!! \App\Support\RichText::sanitize($pageSection['description'] ?? '') !!}
                    </div>
                </div>
            </section>

            @if(!$cmsPreview && $featuredCard)
                <section id="featuredEventMount" class="ne-featured reveal" aria-label="Featured event">
                    <div class="ne-featured-img">
                        <img src="{{ \App\Support\EventsCmsContent::resolveImagePath($featuredCard['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}" alt="{{ $featuredCard['title'] ?? 'Featured event' }}">
                        <span class="ne-featured-badge">Featured Event</span>
                    </div>
                    <div class="ne-featured-body">
                        <span class="ne-tag">{{ \App\Support\EventsCmsContent::categoryLabel($featuredCard['category'] ?? 'events') }}</span>
                        <h2 class="ne-featured-title">{{ $featuredCard['title'] ?? '' }}</h2>
                        <p class="ne-featured-meta">{{ $formatDateLine($featuredCard) }}</p>
                        <p class="ne-featured-desc">{{ $summaryFor($featuredCard) }}</p>
                        <a
                            href="#"
                            class="ne-btn-gold"
                            data-ne-modal-trigger
                            data-tag="{{ \App\Support\EventsCmsContent::categoryLabel($featuredCard['category'] ?? 'events') }}"
                            data-date="{{ $formatDateLine($featuredCard) }}"
                            data-title="{{ $featuredCard['title'] ?? '' }}"
                            data-summary="{{ e($summaryFor($featuredCard)) }}"
                            data-location="{{ $featuredCard['location'] ?? '' }}"
                            data-image="{{ \App\Support\EventsCmsContent::resolveImagePath($featuredCard['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}"
                            data-content-html="{{ e(\App\Support\RichText::sanitize($featuredCard['content'] ?? '')) }}"
                        >
                            Learn More
                        </a>
                    </div>
                </section>
            @endif

            <section
                class="ne-events-main reveal"
            >
                <div data-cms-boundary class="cms-preview-boundary-full">
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
                                            <p class="ne-event-name">{{ $card['title'] ?? '' }}</p>
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
                                            <p class="ne-event-name">{{ $card['title'] ?? '' }}</p>
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
                                        <button type="button" class="cms-preview-card-action" data-cms-card-edit title="Edit card" aria-label="Edit {{ $card['title'] ?? 'event card' }}">
                                            Edit
                                        </button>
                                        <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-cms-card-delete title="Delete card" aria-label="Delete {{ $card['title'] ?? 'event card' }}">
                                            Delete
                                        </button>
                                    </div>
                                @endif
                                <div class="ne-card-img">
                                    <img src="{{ \App\Support\EventsCmsContent::resolveImagePath($card['image'] ?? '', 'assets/static_img/pupillar.jpeg') }}" alt="{{ $card['title'] ?? 'Event card' }}" loading="lazy">
                                    <span class="ne-card-tag">{{ \App\Support\EventsCmsContent::categoryLabel($card['category'] ?? 'events') }}</span>
                                </div>
                                <div class="ne-card-body">
                                    <p class="ne-card-date">{{ $formatDate($card['event_date'] ?? null, 'F d, Y') }}</p>
                                    <h3 class="ne-card-title">{{ $card['title'] ?? '' }}</h3>
                                    <div class="ne-card-desc ne-rich-copy">{!! $summaryHtmlFor($card) !!}</div>
                                    <hr class="ne-card-rule">
                                    <div class="ne-card-foot">
                                        <span class="ne-card-loc">{{ $card['location'] ?? 'Location to be announced' }}</span>
                                        <a
                                            href="#"
                                            class="ne-read-more"
                                            data-ne-modal-trigger
                                            data-tag="{{ \App\Support\EventsCmsContent::categoryLabel($card['category'] ?? 'events') }}"
                                            data-date="{{ $formatDateLine($card) }}"
                                            data-title="{{ $card['title'] ?? '' }}"
                                            data-summary="{{ e($summaryFor($card)) }}"
                                            data-location="{{ $card['location'] ?? '' }}"
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
                        <p class="ne-modal-summary" id="modalSummary"></p>
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
        <script src="{{ asset('assets/js/script.js') }}" defer></script>
        <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    @endunless

    @if($cmsPreview)
        <style>
            .ne-page-intro,
            .ne-events-main {
                --cms-preview-outline-offset: 12px;
                --cms-preview-chip-top-offset: 50%;
                --cms-preview-chip-right-offset: 12px;
                position: relative;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .cms-preview-editable {
                cursor: pointer;
                isolation: isolate;
                overflow: visible !important;
            }

            .cms-preview-editable > [data-cms-boundary] {
                position: relative;
                display: block;
                width: calc(100% - (var(--cms-preview-outline-offset) * 2));
                margin: var(--cms-preview-outline-offset);
                box-sizing: border-box;
                overflow: visible !important;
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
                cursor: pointer;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .cms-preview-card-action-delete {
                background: rgba(92, 0, 0, 0.96);
            }

            .ne-card[data-cms-card-index] {
                transition: none;
            }

            .ne-card[data-cms-card-index]:hover {
                filter: none;
                box-shadow: inherit;
                transform: none;
            }

            .ne-card[data-cms-card-index]:hover .cms-preview-card-actions,
            .ne-card[data-cms-card-index]:focus-within .cms-preview-card-actions {
                opacity: 1;
                transform: none;
            }

            @media (max-width: 768px) {
                .ne-page-intro,
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
            }
        </style>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cmsPreview = @json($cmsPreview);
            const revealElements = Array.from(document.querySelectorAll('.reveal'));
            const filters = Array.from(document.querySelectorAll('.ne-filter'));
            const addCardTrigger = document.querySelector('[data-cms-add-card-trigger]');
            const emptyState = document.querySelector('[data-ne-empty-state]');
            const modal = document.getElementById('detailsModal');
            const modalImg = document.getElementById('modalImg');
            const modalTag = document.getElementById('modalTag');
            const modalDate = document.getElementById('modalDate');
            const modalTitle = document.getElementById('modalTitle');
            const modalSummary = document.getElementById('modalSummary');
            const modalLocation = document.getElementById('modalLocation');
            const modalDetailsLabel = document.getElementById('modalDetailsLabel');
            const modalText = document.getElementById('modalText');
            const closeBtn = modal?.querySelector('.ne-modal-close');
            let lastTrigger = null;

            // CMS preview does not load the public reveal script, so force visible content there.
            if (cmsPreview) {
                revealElements.forEach((element) => element.classList.add('active'));
            }

            const getCards = () => Array.from(document.querySelectorAll('[data-events-card]'));

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
                    for (let index = 0; index < 2; index += 1) {
                        const textarea = document.createElement('textarea');
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
                modalImg.alt = trigger.dataset.title || 'Event image';
                modalTag.textContent = trigger.dataset.tag || '';
                modalDate.textContent = trigger.dataset.date || '';
                modalTitle.textContent = trigger.dataset.title || '';
                modalSummary.textContent = decodeHtmlEntities(trigger.dataset.summary || '');
                modalSummary.hidden = modalSummary.textContent.trim() === '';
                modalLocation.textContent = trigger.dataset.location || '';
                modalLocation.hidden = modalLocation.textContent.trim() === '';
                modalText.innerHTML = decodeHtmlEntities(trigger.dataset.contentHtml || '');
                modalDetailsLabel.hidden = modalText.textContent.trim() === '';

                modal.classList.remove('closing');
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            };

            const closeModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.add('closing');

                window.setTimeout(() => {
                    modal.classList.remove('show', 'closing');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('modal-open');
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

            getCards().forEach((card) => {
                if (!card.hasAttribute('data-cms-card-index')) {
                    return;
                }

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

            window.addEventListener('message', (event) => {
                const data = event.data || {};
                if (!data || data.type !== 'cms-events-prune-card') {
                    return;
                }

                const targetIndex = Number(data.cardIndex);
                if (!Number.isFinite(targetIndex)) {
                    return;
                }

                const targetCard = document.querySelector(`[data-events-card][data-cms-card-index="${targetIndex}"]`);
                if (!targetCard) {
                    return;
                }

                targetCard.remove();
                applyFilter('all');
                schedulePreviewHeight();
            });

            addCardTrigger?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                requestAddCard();
            });

            addCardTrigger?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                requestAddCard();
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
            schedulePreviewHeight();
        });
    </script>
</body>
</html>
