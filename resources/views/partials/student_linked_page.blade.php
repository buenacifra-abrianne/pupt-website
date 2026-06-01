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
                    @if($cmsPreview && $qrSectionKey !== '')
                        <article
                            class="student-qr-card student-qr-card-add"
                            data-students-qr-add-card-trigger
                            data-students-qr-section="{{ $qrSectionKey }}"
                            tabindex="0"
                            role="button"
                            aria-label="Add QR card"
                        >
                            <div class="student-qr-card-add-inner">
                                <span class="student-qr-card-add-plus" aria-hidden="true">+</span>
                                <p class="student-qr-card-add-label">Add QR Card</p>
                            </div>
                        </article>
                    @endif

                    @forelse(($qrCodes['items'] ?? []) as $qrCode)
                        @php
                            $qrImage = trim((string) ($qrCode['image'] ?? ''));
                            $qrHowToImage = trim((string) ($qrCode['how_to_image'] ?? ''));
                            $qrHref = trim((string) ($qrCode['href'] ?? ''));
                            $qrIsExternalLink = preg_match('/^https?:\/\//i', $qrHref) === 1;
                            $qrImageUrl = $qrImage !== '' ? \App\Support\StudentsCmsContent::resolveImagePath($qrImage, 'assets/static_img/pupillar.jpeg') : '';
                            $qrHowToImageUrl = $qrHowToImage !== '' ? \App\Support\StudentsCmsContent::resolveImagePath($qrHowToImage, 'assets/static_img/pupillar.jpeg') : '';
                        @endphp
                        <article
                            class="student-qr-card{{ !$cmsPreview && $qrHowToImage !== '' ? ' student-qr-card-live student-qr-card-live-clickable' : '' }}"
                            @if($cmsPreview)
                                data-students-qr-card-index="{{ $loop->index }}"
                                data-students-qr-section="{{ $qrSectionKey }}"
                            @elseif($qrHowToImage !== '')
                                data-student-qr-open
                                data-qr-howto-image="{{ e($qrHowToImageUrl) }}"
                                data-qr-label="{{ e($qrCode['label'] ?? 'QR Code') }}"
                                data-qr-description="{{ e($qrCode['description'] ?? '') }}"
                                data-qr-link="{{ e($qrHref) }}"
                                tabindex="0"
                                role="button"
                            @endif
                        >
                            @if($cmsPreview && $qrSectionKey !== '')
                                <div class="cms-preview-card-actions" aria-label="QR card actions">
                                    <button
                                        type="button"
                                        class="cms-preview-card-action"
                                        data-students-qr-card-edit
                                        aria-label="Edit {{ trim((string) ($qrCode['label'] ?? '')) !== '' ? $qrCode['label'] : 'QR card' }}"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="cms-preview-card-action cms-preview-card-action-delete"
                                        data-students-qr-card-delete
                                        aria-label="Delete {{ trim((string) ($qrCode['label'] ?? '')) !== '' ? $qrCode['label'] : 'QR card' }}"
                                    >
                                        Delete
                                    </button>
                                </div>
                            @endif

                            @if($qrImage !== '')
                                @if(!$cmsPreview && $qrHowToImage === '' && $qrHref !== '')
                                    <a
                                        href="{{ $qrHref }}"
                                        class="student-qr-media-link"
                                        @if($qrIsExternalLink) target="_blank" rel="noopener noreferrer" @endif
                                        aria-label="Open link for {{ $qrCode['label'] ?? 'QR code' }}"
                                    >
                                        <img src="{{ $qrImageUrl }}" alt="{{ $qrCode['label'] ?? 'QR code' }}">
                                    </a>
                                @else
                                    <img src="{{ $qrImageUrl }}" alt="{{ $qrCode['label'] ?? 'QR code' }}">
                                @endif
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
            </div>
        </section>
    @endif

    @if(!$cmsPreview && ($isAdmissionsPage || $isDocumentRequestsPage))
        <div class="student-qr-howto-modal" id="studentQrHowToModal" hidden aria-hidden="true">
            <div class="student-qr-howto-modal-backdrop" data-student-qr-close></div>
            <div class="student-qr-howto-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="studentQrHowToTitle">
                <button type="button" class="student-qr-howto-modal-close" data-student-qr-close aria-label="Close how-to image">&times;</button>
                <div class="student-qr-howto-modal-image-shell">
                    <img src="" alt="" id="studentQrHowToImage">
                </div>
                <div class="student-qr-howto-modal-copy">
                    <h3 id="studentQrHowToTitle">How To</h3>
                    <p id="studentQrHowToDescription"></p>
                    <a href="#" id="studentQrHowToLink" class="student-qr-howto-modal-link" target="_blank" rel="noopener noreferrer" hidden>Open Related Link</a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('studentQrHowToModal');
                if (!modal) {
                    return;
                }

                const image = document.getElementById('studentQrHowToImage');
                const title = document.getElementById('studentQrHowToTitle');
                const description = document.getElementById('studentQrHowToDescription');
                const link = document.getElementById('studentQrHowToLink');

                const closeModal = () => {
                    modal.hidden = true;
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                };

                const openModal = (trigger) => {
                    const howToImage = String(trigger.getAttribute('data-qr-howto-image') || '').trim();
                    if (howToImage === '' || !image || !title || !description || !link) {
                        return;
                    }

                    const label = String(trigger.getAttribute('data-qr-label') || 'QR Code').trim();
                    const copy = String(trigger.getAttribute('data-qr-description') || '').trim();
                    const href = String(trigger.getAttribute('data-qr-link') || '').trim();

                    image.src = howToImage;
                    image.alt = `${label} how-to image`;
                    title.textContent = `${label} How To`;
                    description.textContent = copy !== '' ? copy : 'Follow the instructions shown in this image.';

                    if (href !== '') {
                        link.href = href;
                        link.hidden = false;
                    } else {
                        link.hidden = true;
                        link.removeAttribute('href');
                    }

                    modal.hidden = false;
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                document.querySelectorAll('[data-student-qr-open]').forEach((card) => {
                    const activate = (event) => {
                        event.preventDefault();
                        openModal(card);
                    };

                    card.addEventListener('click', activate);
                    card.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            activate(event);
                        }
                    });
                });

                modal.addEventListener('click', (event) => {
                    if (event.target.closest('[data-student-qr-close]')) {
                        event.preventDefault();
                        closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !modal.hidden) {
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
