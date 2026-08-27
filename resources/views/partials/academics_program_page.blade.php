@php
    $pageKey = $programPageKey ?? 'degree-programs';
    $pageTitle = $programPageTitle ?? 'Degree Programs';
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $info = is_array($pageData['info'] ?? null) ? $pageData['info'] : [];
    $cards = is_array($pageData['cards'] ?? null) ? $pageData['cards'] : [];
    $programsOfferedLabel = $pageKey === 'diploma-programs' ? 'Diploma Programs Offered' : 'Undergraduate Programs Offered';
    $cardItems = array_values(is_array($cards['items'] ?? null) ? $cards['items'] : []);
    $infoRows = array_values(is_array($info['items'] ?? null) ? $info['items'] : []);
    $modalId = 'dp-program-modal-'.\Illuminate\Support\Str::slug($pageKey);

    $accreditationRank = static function (mixed $rawLevel): int {
        $normalized = strtoupper(trim((string) $rawLevel));
        if ($normalized === '') {
            return 0;
        }

        foreach (['IV' => 4, 'III' => 3, 'II' => 2, 'I' => 1] as $roman => $score) {
            if (preg_match('/\b'.preg_quote($roman, '/').'\b/', $normalized)) {
                return $score;
            }
        }

        return 0;
    };

    if (!$cmsPreview) {
        usort($cardItems, static function (array $left, array $right) use ($accreditationRank): int {
            $leftRank = $accreditationRank($left['accreditation_levels'] ?? '');
            $rightRank = $accreditationRank($right['accreditation_levels'] ?? '');

            if ($leftRank !== $rightRank) {
                return $rightRank <=> $leftRank;
            }

            $leftTitle = trim((string) ($left['title'] ?? ''));
            $rightTitle = trim((string) ($right['title'] ?? ''));

            return strcasecmp($leftTitle, $rightTitle);
        });
    }
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
    <div data-cms-boundary class="cms-preview-boundary-full">
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
                    $itemBodyPreview = \Illuminate\Support\Str::limit(\App\Support\RichText::plainText($item['body'] ?? ''), 120, '...');
                    $itemBodyHtml = \App\Support\RichText::sanitize($item['body'] ?? '');
                    $itemBadge = trim((string) ($item['badge'] ?? ''));
                    $itemDept = trim((string) ($item['dept'] ?? ''));
                    $itemAccreditationLevels = trim((string) ($item['accreditation_levels'] ?? ''));
                    $mappedAccreditationLevel = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4'][$itemAccreditationLevels] ?? $itemAccreditationLevels;
                    $itemAccreditingInstitution = trim((string) ($item['accrediting_institution'] ?? ''));
                    $itemAccreditationValidity = trim((string) ($item['accreditation_validity'] ?? ''));
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
                    <article
                        class="contents-card dp-program-card-trigger"
                        tabindex="0"
                        role="button"
                        aria-haspopup="dialog"
                        aria-controls="{{ $modalId }}"
                        aria-label="View details for {{ $itemTitle !== '' ? $itemTitle : 'this program' }}"
                        data-program-modal-trigger="{{ $modalId }}"
                        data-program-title="{{ $itemTitle }}"
                        data-program-badge="{{ $itemBadge }}"
                        data-program-accreditation="{{ $mappedAccreditationLevel }}"
                        data-program-accrediting-institution="{{ $itemAccreditingInstitution }}"
                        data-program-accreditation-validity="{{ $itemAccreditationValidity }}"
                    >
                @endif
                    <div class="dp-diploma-card-body">
                        @if($itemBadge !== '')
                            <span class="dp-diploma-badge">{{ $itemBadge }}</span>
                        @endif
                        <h3 class="dp-diploma-title">{{ $itemTitle }}</h3>
                        @if($itemBodyPreview !== '')
                            <p class="dp-diploma-desc">{{ $itemBodyPreview }}</p>
                        @endif
                        @if($itemAccreditationLevels !== '')
                            <span class="dp-diploma-accreditation">Accreditation Level: {{ $mappedAccreditationLevel }}</span>
                        @endif
                        @unless($cmsPreview)
                            <div class="dp-program-modal-payload" hidden data-program-modal-body>
                                {!! $itemBodyHtml !!}
                            </div>
                        @endunless
                        @if($itemDept !== '')
                            <span class="dp-diploma-dept">{{ $itemDept }}</span>
                        @endif
                    </div>
                @if($cmsPreview)
                    </article>
                @else
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>

@unless($cmsPreview)
    <div class="dp-program-modal" id="{{ $modalId }}" hidden>
        <div class="dp-program-modal-backdrop" data-program-modal-close></div>
        <div class="dp-program-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-dialog-title">
            <button type="button" class="dp-program-modal-close" data-program-modal-close aria-label="Close program details">
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="dp-program-modal-copy">
                <div class="dp-program-modal-head">
                    <h3 id="{{ $modalId }}-dialog-title" class="dp-program-modal-heading">Program Details</h3>
                    <p class="dp-program-modal-subhead">Academic profile and accreditation snapshot</p>
                </div>

                <div class="dp-program-modal-fields-grid">
                    <div class="dp-program-modal-field">
                        <p class="dp-program-modal-field-label">{{ $programsOfferedLabel }}</p>
                        <p class="dp-program-modal-field-value" data-program-modal-name></p>
                    </div>

                    <div class="dp-program-modal-field">
                        <p class="dp-program-modal-field-label">Program Abbreviation</p>
                        <p class="dp-program-modal-field-value" data-program-modal-badge></p>
                    </div>

                    <div class="dp-program-modal-field">
                        <p class="dp-program-modal-field-label">Accreditation Level</p>
                        <p class="dp-program-modal-field-value" data-program-modal-accreditation></p>
                    </div>

                    <div class="dp-program-modal-field">
                        <p class="dp-program-modal-field-label">Validity of Accreditation (start date - end date)</p>
                        <p class="dp-program-modal-field-value" data-program-modal-accreditation-validity></p>
                    </div>

                    <div class="dp-program-modal-field">
                        <p class="dp-program-modal-field-label">Accrediting Institution</p>
                        <p class="dp-program-modal-field-value" data-program-modal-accrediting-institution></p>
                    </div>

                    <div class="dp-program-modal-field dp-program-modal-field--span-2">
                        <p class="dp-program-modal-field-label">Description</p>
                        <div class="dp-program-modal-body academic-rich-copy" data-program-modal-body-target></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById(@json($modalId));
            if (!modal) {
                return;
            }

            const closeButtons = modal.querySelectorAll('[data-program-modal-close]');
            const nameEl = modal.querySelector('[data-program-modal-name]');
            const badgeEl = modal.querySelector('[data-program-modal-badge]');
            const accreditationEl = modal.querySelector('[data-program-modal-accreditation]');
            const accreditingInstitutionEl = modal.querySelector('[data-program-modal-accrediting-institution]');
            const accreditationValidityEl = modal.querySelector('[data-program-modal-accreditation-validity]');
            const bodyEl = modal.querySelector('[data-program-modal-body-target]');
            let lastTrigger = null;

            const setFieldValue = (element, value) => {
                if (!element) {
                    return;
                }

                const text = value !== '' ? value : 'Not specified';
                element.textContent = text;
                element.classList.toggle('is-empty', text === 'Not specified');
            };

            const closeModal = () => {
                if (modal.hidden) {
                    return;
                }

                modal.hidden = true;
                document.body.classList.remove('dp-modal-open');
                if (lastTrigger instanceof HTMLElement) {
                    lastTrigger.focus();
                }
            };

            const openModal = (trigger) => {
                lastTrigger = trigger;
                const payload = trigger.querySelector('[data-program-modal-body]');
                const title = (trigger.dataset.programTitle || '').trim();
                const badge = (trigger.dataset.programBadge || '').trim();
                const accreditation = (trigger.dataset.programAccreditation || '').trim();
                const accreditingInstitution = (trigger.dataset.programAccreditingInstitution || '').trim();
                const accreditationValidity = (trigger.dataset.programAccreditationValidity || '').trim();

                setFieldValue(nameEl, title);
                setFieldValue(badgeEl, badge);
                setFieldValue(accreditationEl, accreditation);
                setFieldValue(accreditingInstitutionEl, accreditingInstitution);
                setFieldValue(accreditationValidityEl, accreditationValidity);

                if (bodyEl) {
                    const html = payload ? payload.innerHTML.trim() : '';
                    bodyEl.innerHTML = html !== '' ? html : '<p>No additional program details available.</p>';
                }

                modal.hidden = false;
                document.body.classList.add('dp-modal-open');
                modal.querySelector('.dp-program-modal-close')?.focus();
            };

            document.querySelectorAll(`[data-program-modal-trigger="{{ $modalId }}"]`).forEach((trigger) => {
                trigger.addEventListener('click', () => openModal(trigger));
                trigger.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    openModal(trigger);
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (!event.target.closest('.dp-program-modal-dialog')) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
@endunless

<section class="dp-accreditation-footer-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}">
    <div class="dp-accreditation-footer-inner">
        <h3>Looking for official accreditation records?</h3>
        <p>Access the Higher Education accreditation PDF for full details and validity periods.</p>
        <div class="dp-accreditation-glow-actions">
            <a
                href="https://drive.google.com/file/d/1I1fTVNwsYkeWfzz8bMqIVRI5vLI5EMob/view"
                class="dp-accreditation-glow-btn"
                target="_blank"
                rel="noopener"
            >
                HIGHER EDUCATION
            </a>
        </div>
    </div>
</section>
