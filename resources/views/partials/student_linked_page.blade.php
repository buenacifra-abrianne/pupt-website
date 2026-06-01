@php
    $pageKey = (string) ($pageKey ?? '');
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $links = is_array($pageData['links'] ?? null) ? $pageData['links'] : [];
    $instructions = is_array($pageData['instructions'] ?? null) ? $pageData['instructions'] : [];
    $qrCodes = is_array($pageData['qr_codes'] ?? null) ? $pageData['qr_codes'] : [];
    $heroImage = \App\Support\StudentsCmsContent::resolveImagePath($hero['image'] ?? null, 'assets/static_img/about_header_image.png');
    $headerText = $pageKey === 'admissions'
        ? 'ADMISSIONS'
        : ($pageKey === 'downloadable-forms'
            ? 'DOWNLOADABLES'
            : ($pageKey === 'document-requests' ? 'DOCUMENT REQUESTS' : strtoupper((string) ($hero['title'] ?? 'STUDENTS'))));
    $heroSectionKey = $pageKey === 'admissions'
        ? 'admissions_hero'
        : ($pageKey === 'downloadable-forms'
            ? 'downloadable_forms_hero'
            : ($pageKey === 'document-requests' ? 'document_requests_hero' : ''));
    $instructionsSectionKey = $pageKey === 'admissions' ? 'admissions_instructions' : '';
    $qrSectionKey = $pageKey === 'admissions'
        ? 'admissions_qr_codes'
        : ($pageKey === 'document-requests' ? 'document_requests_qr_codes' : '');
    $linksSectionKey = $pageKey === 'admissions'
        ? 'admissions_links'
        : ($pageKey === 'downloadable-forms'
            ? 'downloadable_forms_links'
            : '');
    $isAdmissionsPage = $pageKey === 'admissions';
    $isDownloadablesPage = $pageKey === 'downloadable-forms';
    $isDocumentRequestsPage = $pageKey === 'document-requests';
@endphp

<section
    class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview && $heroSectionKey !== '')
        data-cms-section="{{ $heroSectionKey }}"
        data-cms-section-label="{{ $isAdmissionsPage ? 'Admissions Header' : ($isDownloadablesPage ? 'Downloadables Header' : 'Document Requests Header') }}"
    @endif
>
    @if($cmsPreview && $heroSectionKey !== '')
        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="{{ $heroSectionKey }}" aria-label="Edit header">
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
                            <img src="{{ $heroImage }}" alt="" class="carousel-half carousel-half-left">
                        </div>
                        <div class="carousel-caption">
                            <h2>{{ $headerText }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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

<div
    class="student-page-body reveal{{ $cmsPreview ? ' active' : '' }}"
>
    @if(!in_array($pageKey, ['admissions', 'downloadable-forms', 'document-requests'], true))
        <section
            class="student-page-intro{{ $cmsPreview && $heroSectionKey !== '' ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview && $heroSectionKey !== '')
                data-cms-section="{{ $heroSectionKey }}"
                data-cms-section-label="{{ $isAdmissionsPage ? 'Admissions Header' : ($isDownloadablesPage ? 'Downloadables Header' : 'Document Requests Header') }}"
            @endif
        >
            <div data-cms-boundary class="cms-preview-boundary-full">
                <p class="section-tag">{{ $hero['tag'] ?? '' }}</p>
                <h2>{{ $hero['subtitle'] ?? ($hero['title'] ?? '') }}</h2>
                <p>{{ $hero['body'] ?? '' }}</p>
            </div>
        </section>
    @endif

    @if($isAdmissionsPage)
        <section
            class="student-page-section{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview && $instructionsSectionKey !== '')
                data-cms-section="{{ $instructionsSectionKey }}"
                data-cms-section-label="Admissions Instructions"
            @endif
        >
            <div data-cms-boundary class="cms-preview-boundary-full">
                <div class="student-page-section-head">
                    <p class="section-tag">{{ $instructions['tag'] ?? '' }}</p>
                    <h2>{{ $instructions['title'] ?? '' }}</h2>
                </div>
                <div class="students-rich-copy student-page-rich">
                    {!! \App\Support\RichText::sanitize($instructions['body'] ?? '') !!}
                </div>
            </div>
        </section>
    @endif

    @if($isAdmissionsPage || $isDocumentRequestsPage)
        <section
            class="student-page-section{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview && $qrSectionKey !== '')
                data-cms-section="{{ $qrSectionKey }}"
                data-cms-section-label="{{ $isAdmissionsPage ? 'Admissions QR Codes' : 'Document Requests QR Codes' }}"
            @endif
        >
            <div data-cms-boundary class="cms-preview-boundary-full">
                <div class="student-page-section-head">
                    <p class="section-tag">{{ $qrCodes['tag'] ?? '' }}</p>
                    <h2>{{ $qrCodes['title'] ?? '' }}</h2>
                </div>
                <div class="student-qr-grid">
                    @forelse(($qrCodes['items'] ?? []) as $qrCode)
                        @php
                            $qrTitle = trim((string) ($qrCode['label'] ?? 'QR Code'));
                            $qrDescription = trim((string) ($qrCode['description'] ?? ''));
                            $qrImage = trim((string) ($qrCode['image'] ?? ''));
                            $qrHref = trim((string) ($qrCode['href'] ?? ''));
                            $qrDetailImage = trim((string) ($qrCode['detail_image'] ?? ''));
                            $qrResolvedImage = $qrImage !== ''
                                ? \App\Support\StudentsCmsContent::resolveImagePath($qrImage, 'assets/static_img/pupillar.jpeg')
                                : asset('assets/static_img/pupillar.jpeg');
                            $qrResolvedDetailImage = \App\Support\StudentsCmsContent::resolveImagePath(
                                $qrDetailImage !== '' ? $qrDetailImage : $qrImage,
                                'assets/static_img/pupillar.jpeg'
                            );
                        @endphp
                        <article
                            class="student-qr-card"
                            data-student-qr-trigger
                            data-qr-title="{{ e($qrTitle !== '' ? $qrTitle : 'QR Code') }}"
                            data-qr-description="{{ e($qrDescription) }}"
                            data-qr-link="{{ e($qrHref) }}"
                            data-qr-image="{{ e($qrResolvedImage) }}"
                            data-qr-detail-image="{{ e($qrResolvedDetailImage) }}"
                            tabindex="0"
                            role="button"
                        >
                            @if($qrImage !== '')
                                <img src="{{ $qrResolvedImage }}" alt="{{ $qrTitle !== '' ? $qrTitle : 'QR code' }}">
                            @else
                                <div class="student-qr-placeholder">QR</div>
                            @endif
                            <div>
                                <h3>{{ $qrTitle !== '' ? $qrTitle : 'QR Code' }}</h3>
                                <p>{{ $qrDescription }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="student-page-empty">No QR codes have been added yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <div class="student-qr-modal" data-student-qr-modal hidden>
            <div class="student-qr-modal-backdrop" data-student-qr-close></div>
            <div class="student-qr-modal-panel" role="dialog" aria-modal="true" aria-label="QR details">
                <button type="button" class="student-qr-modal-close" data-student-qr-close aria-label="Close">&times;</button>

                <div class="student-qr-modal-grid">
                    <div class="student-qr-modal-left">
                        <a href="#" class="student-qr-modal-link" data-student-qr-modal-link>
                            <img src="" alt="QR code" data-student-qr-modal-image>
                        </a>
                    </div>

                    <div class="student-qr-modal-right">
                        <h3 data-student-qr-modal-title>QR Code</h3>
                        <p data-student-qr-modal-description></p>
                        <img src="" alt="" class="student-qr-modal-detail-image" data-student-qr-modal-detail-image hidden>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const isPreviewMode = @json($cmsPreview);
                if (isPreviewMode) {
                    return;
                }

                const modal = document.querySelector('[data-student-qr-modal]');
                const modalLink = modal?.querySelector('[data-student-qr-modal-link]');
                const modalImage = modal?.querySelector('[data-student-qr-modal-image]');
                const modalTitle = modal?.querySelector('[data-student-qr-modal-title]');
                const modalDescription = modal?.querySelector('[data-student-qr-modal-description]');
                const modalDetailImage = modal?.querySelector('[data-student-qr-modal-detail-image]');
                const triggers = Array.from(document.querySelectorAll('[data-student-qr-trigger]'));

                if (!modal || !modalLink || !modalImage || !modalTitle || !modalDescription || !modalDetailImage || !triggers.length) {
                    return;
                }

                const closeModal = () => {
                    modal.hidden = true;
                    document.body.classList.remove('student-qr-modal-open');
                };

                const openModal = (trigger) => {
                    const title = trigger.getAttribute('data-qr-title') || 'QR Code';
                    const description = trigger.getAttribute('data-qr-description') || '';
                    const href = trigger.getAttribute('data-qr-link') || '';
                    const imageSrc = trigger.getAttribute('data-qr-image') || '';
                    const detailImageSrc = trigger.getAttribute('data-qr-detail-image') || '';
                    const isExternalLink = /^https?:\/\//i.test(href);

                    modalTitle.textContent = title;
                    modalDescription.textContent = description;
                    modalImage.src = imageSrc;
                    modalImage.alt = title;

                    modalLink.href = href !== '' ? href : '#';
                    if (href !== '') {
                        modalLink.removeAttribute('aria-disabled');
                        modalLink.classList.remove('is-disabled');
                        if (isExternalLink) {
                            modalLink.setAttribute('target', '_blank');
                            modalLink.setAttribute('rel', 'noopener noreferrer');
                        } else {
                            modalLink.removeAttribute('target');
                            modalLink.removeAttribute('rel');
                        }
                    } else {
                        modalLink.removeAttribute('target');
                        modalLink.removeAttribute('rel');
                        modalLink.setAttribute('aria-disabled', 'true');
                        modalLink.classList.add('is-disabled');
                    }

                    if (detailImageSrc !== '') {
                        modalDetailImage.src = detailImageSrc;
                        modalDetailImage.alt = `${title} detail image`;
                        modalDetailImage.hidden = false;
                    } else {
                        modalDetailImage.hidden = true;
                        modalDetailImage.removeAttribute('src');
                    }

                    modal.hidden = false;
                    document.body.classList.add('student-qr-modal-open');
                };

                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', () => openModal(trigger));
                    trigger.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            openModal(trigger);
                        }
                    });
                });

                modal.querySelectorAll('[data-student-qr-close]').forEach((closeTrigger) => {
                    closeTrigger.addEventListener('click', closeModal);
                });

                document.addEventListener('keydown', (event) => {
                    if (!modal.hidden && event.key === 'Escape') {
                        closeModal();
                    }
                });
            });
        </script>
    @endif

    @if($isAdmissionsPage || $isDownloadablesPage)
        <section
            class="student-page-section{{ $cmsPreview && $linksSectionKey !== '' ? ' cms-preview-editable' : '' }}"
            @if($cmsPreview && $linksSectionKey !== '')
                data-cms-section="{{ $linksSectionKey }}"
                data-cms-section-label="{{ $isAdmissionsPage ? 'Admissions Links' : 'Downloadables Links' }}"
            @endif
        >
            <div data-cms-boundary class="cms-preview-boundary-full">
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
            </div>
        </section>
    @endif
</div>
