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
                @php
                    $instructionsImage = trim((string) ($instructions['image'] ?? ''));
                @endphp
                @if($instructionsImage !== '')
                    <figure class="student-application-guide-image">
                        <img src="{{ \App\Support\StudentsCmsContent::resolveImagePath($instructionsImage, 'assets/static_img/pupillar.jpeg') }}" alt="{{ $instructions['title'] ?? 'Application Guide' }} step by step process">
                    </figure>
                @endif
                @if(isset($instructions['links']) && is_array($instructions['links']) && count($instructions['links']) > 0)
                    <div class="student-admissions-instructions-links" style="margin-top: 1.5rem;">
                        <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 12px;">
                            @foreach($instructions['links'] as $link)
                                <li>
                                    <a href="{{ $link['href'] ?? '#' }}" target="_blank" rel="noopener noreferrer" style="color: #7b1113; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; background-color: #fdfdfd; border: 1px solid #eee; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; transition: background-color 0.2s, box-shadow 0.2s;" onmouseover="this.style.backgroundColor='#f9f9f9'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)'" onmouseout="this.style.backgroundColor='#fdfdfd'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'">
                                        <i class="fas fa-external-link-alt" style="font-size: 14px; opacity: 0.8;"></i> 
                                        <span>{{ $link['label'] ?? 'Link' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>
                @php
                    $admissionsContact = is_array($pageData['contact'] ?? null) ? $pageData['contact'] : [];
                    $admissionsContactOffices = is_array($admissionsContact['offices'] ?? null) ? $admissionsContact['offices'] : [];
                    $admissionsContactPersons = is_array($admissionsContact['persons'] ?? null) ? $admissionsContact['persons'] : [];
                @endphp
                @if($admissionsContactOffices !== [] || $admissionsContactPersons !== [])
                    <div class="student-admissions-contact">
                        <div class="student-admissions-contact-grid">
                            @if($admissionsContactOffices !== [])
                                <div
                                    class="student-admissions-contact-panel student-admissions-contact-panel--offices{{ $cmsPreview && $pageKey === 'admissions' ? ' cms-preview-editable' : '' }}"
                                    @if($cmsPreview && $pageKey === 'admissions')
                                        data-cms-section="admissions_contact_offices"
                                        data-cms-section-label="Admissions Contact Offices"
                                    @endif
                                >
                                    <div data-cms-boundary class="cms-preview-boundary-full">
                                        <div class="student-admissions-contact-panel-head">
                                            <span class="student-admissions-contact-panel-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M6.5 10.2c1.2 2.5 3.5 4.8 6 6l1.9-1.9c.2-.2.5-.3.7-.2 1.1.4 2.2.7 3.4.8.4 0 .7.3.7.7v3.1c0 .4-.3.8-.8.8C10.9 19.5 4.5 13.1 3.2 3.8c0-.4.3-.8.8-.8h3.1c.4 0 .7.3.7.7.1 1.2.4 2.3.8 3.4.1.2 0 .5-.2.7L6.5 10.2Z" fill="currentColor"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <p>{{ $admissionsContact['offices_tag'] ?? ($admissionsContact['tag'] ?? 'Contact Us') }}</p>
                                                <h4>{{ $admissionsContact['offices_title'] ?? ($admissionsContact['title'] ?? 'Admissions contact details') }}</h4>
                                            </div>
                                        </div>
                                        @if(trim((string) ($admissionsContact['offices_description'] ?? ($admissionsContact['description'] ?? ''))) !== '')
                                            <p class="student-admissions-contact-summary">{{ $admissionsContact['offices_description'] ?? ($admissionsContact['description'] ?? '') }}</p>
                                        @endif
                                        <div class="student-admissions-contact-items">
                                            @foreach($admissionsContactOffices as $office)
                                                @php
                                                    $officeLabel = trim((string) ($office['label'] ?? ''));
                                                    $officeValue = trim((string) ($office['value'] ?? ''));
                                                    $officeHref = trim((string) ($office['href'] ?? ''));
                                                @endphp
                                                @if($officeHref !== '')
                                                    <a href="{{ $officeHref }}" class="student-admissions-contact-item" aria-label="{{ $officeLabel }} {{ $officeValue }}">
                                                        <span class="student-admissions-contact-item-icon" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" fill="none">
                                                                <path d="M6.5 10.2c1.2 2.5 3.5 4.8 6 6l1.9-1.9c.2-.2.5-.3.7-.2 1.1.4 2.2.7 3.4.8.4 0 .7.3.7.7v3.1c0 .4-.3.8-.8.8C10.9 19.5 4.5 13.1 3.2 3.8c0-.4.3-.8.8-.8h3.1c.4 0 .7.3.7.7.1 1.2.4 2.3.8 3.4.1.2 0 .5-.2.7L6.5 10.2Z" fill="currentColor"/>
                                                            </svg>
                                                        </span>
                                                        <div class="student-admissions-contact-item-copy">
                                                            <strong>{{ $officeLabel }}</strong>
                                                            <span>{{ $officeValue }}</span>
                                                        </div>
                                                        <span class="student-admissions-contact-arrow" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" fill="none">
                                                                <path d="m9 5 7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="student-admissions-contact-item">
                                                        <span class="student-admissions-contact-item-icon" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" fill="none">
                                                                <path d="M6.5 10.2c1.2 2.5 3.5 4.8 6 6l1.9-1.9c.2-.2.5-.3.7-.2 1.1.4 2.2.7 3.4.8.4 0 .7.3.7.7v3.1c0 .4-.3.8-.8.8C10.9 19.5 4.5 13.1 3.2 3.8c0-.4.3-.8.8-.8h3.1c.4 0 .7.3.7.7.1 1.2.4 2.3.8 3.4.1.2 0 .5-.2.7L6.5 10.2Z" fill="currentColor"/>
                                                            </svg>
                                                        </span>
                                                        <div class="student-admissions-contact-item-copy">
                                                            <strong>{{ $officeLabel }}</strong>
                                                            <span>{{ $officeValue }}</span>
                                                        </div>
                                                        <span class="student-admissions-contact-arrow" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" fill="none">
                                                                <path d="m9 5 7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($admissionsContactPersons !== [])
                                <div
                                    class="student-admissions-contact-panel student-admissions-contact-panel--people{{ $cmsPreview && $pageKey === 'admissions' ? ' cms-preview-editable' : '' }}"
                                    @if($cmsPreview && $pageKey === 'admissions')
                                        data-cms-section="admissions_contact_persons"
                                        data-cms-section-label="Admissions Contact Persons"
                                    @endif
                                >
                                    <div data-cms-boundary class="cms-preview-boundary-full">
                                        <div class="student-admissions-contact-panel-head">
                                            <span class="student-admissions-contact-panel-icon student-admissions-contact-panel-icon--person" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 12.2a4.1 4.1 0 1 0-4.1-4.1A4.1 4.1 0 0 0 12 12.2Zm0 1.8c-4.1 0-7.6 2.5-8.4 6.1a1 1 0 0 0 1 .9h14.8a1 1 0 0 0 1-.9c-.8-3.6-4.3-6.1-8.4-6.1Z" fill="currentColor"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <p>{{ $admissionsContact['persons_tag'] ?? ($admissionsContact['tag'] ?? 'Contact Person') }}</p>
                                                <h4>{{ $admissionsContact['persons_title'] ?? ($admissionsContact['title'] ?? 'Admissions contact details') }}</h4>
                                            </div>
                                        </div>
                                        @if(trim((string) ($admissionsContact['persons_description'] ?? ($admissionsContact['description'] ?? ''))) !== '')
                                            <p class="student-admissions-contact-summary">{{ $admissionsContact['persons_description'] ?? ($admissionsContact['description'] ?? '') }}</p>
                                        @endif
                                        <div class="student-admissions-person-grid">
                                            @foreach($admissionsContactPersons as $person)
                                                @php
                                                    $personName = trim((string) ($person['name'] ?? ''));
                                                    $personRole = trim((string) ($person['role'] ?? ''));
                                                    $personEmail = trim((string) ($person['email'] ?? ''));
                                                    $personHref = trim((string) ($person['href'] ?? ''));
                                                    $personImage = trim((string) ($person['image'] ?? ''));
                                                    $personImageSrc = $personImage !== '' ? \App\Support\StudentsCmsContent::resolveImagePath($personImage, 'assets/static_img/temporary_profile.png') : '';
                                                    $personModalImageSrc = \App\Support\StudentsCmsContent::resolveImagePath($personImage !== '' ? $personImage : null, 'assets/static_img/temporary_profile.png');
                                                    $personModalEmailHref = $personHref !== '' ? $personHref : ($personEmail !== '' ? 'mailto:'.$personEmail : '');
                                                @endphp
                                                <button
                                                    type="button"
                                                    class="student-admissions-person{{ !$cmsPreview ? ' student-admissions-person-trigger' : '' }}"
                                                    @if(!$cmsPreview)
                                                        data-student-admissions-person-trigger
                                                        data-person-name="{{ e($personName) }}"
                                                        data-person-role="{{ e($personRole) }}"
                                                        data-person-email="{{ e($personEmail) }}"
                                                        data-person-href="{{ e($personModalEmailHref) }}"
                                                        data-person-image="{{ e($personModalImageSrc) }}"
                                                    @endif
                                                    aria-label="Open profile for {{ e($personName !== '' ? $personName : 'contact person') }}"
                                                >
                                                    <span class="student-admissions-person-avatar" aria-hidden="true">
                                                        @if($personImageSrc !== '')
                                                            <img src="{{ $personImageSrc }}" alt="">
                                                        @else
                                                            <svg viewBox="0 0 24 24" fill="none">
                                                                <path d="M12 12.2a4.1 4.1 0 1 0-4.1-4.1A4.1 4.1 0 0 0 12 12.2Zm0 1.8c-4.1 0-7.6 2.5-8.4 6.1a1 1 0 0 0 1 .9h14.8a1 1 0 0 0 1-.9c-.8-3.6-4.3-6.1-8.4-6.1Z" fill="currentColor"/>
                                                            </svg>
                                                        @endif
                                                    </span>
                                                    <div class="student-admissions-person-copy">
                                                        <strong>{{ $personName }}</strong>
                                                        <span>{{ $personRole }}</span>
                                                    </div>
                                                    <div class="student-admissions-person-divider" aria-hidden="true"></div>
                                                    <span class="student-admissions-person-email">{{ $personEmail }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($isAdmissionsPage && $admissionsContactPersons !== [])
        <div class="student-admissions-person-modal" id="studentAdmissionsPersonModal" aria-hidden="true">
            <div class="student-admissions-person-modal-backdrop" data-student-admissions-person-close></div>
            <div class="student-admissions-person-modal-panel" role="dialog" aria-modal="true" aria-labelledby="studentAdmissionsPersonModalName">
                <button type="button" class="student-admissions-person-modal-close" data-student-admissions-person-close aria-label="Close contact profile">&times;</button>
                <div class="student-admissions-person-modal-media">
                    <img id="studentAdmissionsPersonModalImage" src="" alt="">
                </div>
                <div class="student-admissions-person-modal-copy">
                    <p class="student-admissions-person-modal-kicker">Contact Person</p>
                    <h2 id="studentAdmissionsPersonModalName"></h2>
                    <p class="student-admissions-person-modal-role" id="studentAdmissionsPersonModalRole"></p>
                    <a id="studentAdmissionsPersonModalEmail" href="#" rel="noopener noreferrer" hidden></a>
                </div>
            </div>
        </div>
    @endif

    @if($isDocumentRequestsPage)
        <section
            class="student-page-section"
        >
            <div
                class="{{ $cmsPreview ? 'cms-preview-editable' : '' }}"
                @if($cmsPreview && $qrSectionKey !== '')
                    data-cms-section="{{ $qrSectionKey }}_header"
                    data-cms-section-label="Document Requests QR Codes Header"
                @endif
            >
                <div data-cms-boundary class="cms-preview-boundary-full">
                    <div class="student-page-section-head">
                        <p class="section-tag">{{ $qrCodes['tag'] ?? '' }}</p>
                        <h2>{{ $qrCodes['title'] ?? '' }}</h2>
                    </div>
                </div>
            </div>

            <div
                class="{{ $cmsPreview ? 'cms-preview-editable' : '' }}"
                style="margin-top: 30px;"
                @if($cmsPreview && $qrSectionKey !== '')
                    data-cms-section="{{ $qrSectionKey }}_items"
                    data-cms-section-label="Document Requests QR Codes"
                @endif
            >
                <div data-cms-boundary class="cms-preview-boundary-full">
                    <div class="student-qr-grid">
                    @forelse(($qrCodes['items'] ?? []) as $qrCode)
                        @php
                            $qrImage = trim((string) ($qrCode['image'] ?? ''));
                            $qrImageSrc = $qrImage !== '' ? \App\Support\StudentsCmsContent::resolveImagePath($qrImage, 'assets/static_img/pupillar.jpeg') : '';
                            $qrFlyerImage = trim((string) ($qrCode['flyer_image'] ?? ''));
                            $qrFlyerImageSrc = $qrFlyerImage !== '' ? \App\Support\StudentsCmsContent::resolveImagePath($qrFlyerImage, 'assets/static_img/pupillar.jpeg') : '';
                            $qrHref = trim((string) ($qrCode['href'] ?? ''));
                            $qrLabel = trim((string) ($qrCode['label'] ?? 'QR Code'));
                            $qrDescription = trim((string) ($qrCode['description'] ?? ''));
                        @endphp
                        <button
                            type="button"
                            class="student-qr-card student-qr-trigger"
                            data-qr-title="{{ $qrLabel }}"
                            data-qr-description="{{ $qrDescription }}"
                            data-qr-image="{{ $qrImageSrc }}"
                            data-qr-flyer-image="{{ $qrFlyerImageSrc }}"
                            data-qr-link="{{ $qrHref }}"
                            aria-label="Open QR details for {{ $qrLabel }}"
                        >
                            @if($qrImage !== '')
                                <img src="{{ $qrImageSrc }}" alt="{{ $qrLabel }}">
                            @else
                                <div class="student-qr-placeholder">QR</div>
                            @endif
                            <div>
                                <h3>{{ $qrLabel }}</h3>
                                <p>{{ $qrDescription }}</p>
                            </div>
                        </button>
                    @empty
                        <p class="student-page-empty">No QR codes have been added yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

        <div class="student-qr-modal" id="studentQrModal" aria-hidden="true">
            <div class="student-qr-modal-backdrop" data-student-qr-close></div>
            <div class="student-qr-modal-panel" role="dialog" aria-modal="true" aria-labelledby="studentQrModalTitle">
                <div class="student-qr-modal-head">
                    <div>
                        <h2 id="studentQrModalTitle">QR Code</h2>
                        <p id="studentQrModalDescription"></p>
                    </div>
                    <button type="button" class="student-qr-modal-close" data-student-qr-close aria-label="Close QR details">&times;</button>
                </div>
                <div class="student-qr-modal-gallery">
                    <figure class="student-qr-modal-frame">
                        <figcaption>QR Code</figcaption>
                        <a href="#" class="student-qr-modal-image-link" id="studentQrModalImageLink" target="_blank" rel="noopener noreferrer" aria-label="Open QR link">
                            <img id="studentQrModalImage" src="" alt="QR code">
                        </a>
                        <div class="student-qr-modal-empty" id="studentQrModalImageEmpty">No QR image uploaded.</div>
                    </figure>
                    <figure class="student-qr-modal-frame">
                        <figcaption>Step by Step process</figcaption>
                        <button type="button" class="student-qr-modal-zoom-trigger" id="studentQrModalZoomTrigger" aria-label="Zoom step by step image">
                            <img id="studentQrModalFlyerImage" src="" alt="Flyer or step by step guide">
                            <span>Zoom</span>
                        </button>
                        <div class="student-qr-modal-empty" id="studentQrModalFlyerEmpty">No flyer image uploaded.</div>
                    </figure>
                </div>
            </div>
            <div class="student-qr-zoom" id="studentQrZoom" aria-hidden="true">
                <button type="button" class="student-qr-zoom-close" id="studentQrZoomClose" aria-label="Close zoomed image">&times;</button>
                <img id="studentQrZoomImage" src="" alt="Zoomed step by step guide">
            </div>
        </div>
    @endif

    @if($isAdmissionsPage && $admissionsContactPersons !== [])
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('studentAdmissionsPersonModal');
                const triggers = document.querySelectorAll('[data-student-admissions-person-trigger]');

                if (!modal || !triggers.length) {
                    return;
                }

                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }

                const name = document.getElementById('studentAdmissionsPersonModalName');
                const role = document.getElementById('studentAdmissionsPersonModalRole');
                const email = document.getElementById('studentAdmissionsPersonModalEmail');
                const image = document.getElementById('studentAdmissionsPersonModalImage');
                let lastTrigger = null;

                const closeModal = () => {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('student-admissions-person-modal-open');
                    if (lastTrigger instanceof HTMLElement) {
                        lastTrigger.focus();
                    }
                    lastTrigger = null;
                };

                const openModal = (trigger) => {
                    lastTrigger = trigger;

                    const personName = trigger.dataset.personName || 'Contact Person';
                    const personRole = trigger.dataset.personRole || '';
                    const personEmail = trigger.dataset.personEmail || '';
                    const personHref = trigger.dataset.personHref || '';
                    const personImage = trigger.dataset.personImage || '';

                    if (name) {
                        name.textContent = personName;
                    }

                    if (role) {
                        role.textContent = personRole;
                        role.hidden = personRole.trim() === '';
                    }

                    if (image) {
                        image.src = personImage || '';
                        image.alt = personName ? `${personName} profile photo` : 'Contact person profile photo';
                    }

                    if (email) {
                        if (personEmail.trim() !== '' && personHref.trim() !== '') {
                            email.href = personHref;
                            email.textContent = personEmail;
                            email.hidden = false;
                        } else {
                            email.removeAttribute('href');
                            email.textContent = '';
                            email.hidden = true;
                        }
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('student-admissions-person-modal-open');
                    modal.querySelector('.student-admissions-person-modal-close')?.focus();
                };

                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', () => openModal(trigger));
                });

                modal.querySelectorAll('[data-student-admissions-person-close]').forEach((closeTrigger) => {
                    closeTrigger.addEventListener('click', closeModal);
                });

                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            });
        </script>
    @endif

    @if($isAdmissionsPage || $isDownloadablesPage)
        @php
            $linksItemsSectionKey = $isAdmissionsPage ? 'admissions_form_links' : ($isDownloadablesPage ? 'downloadable_forms_items' : '');
        @endphp
        <section class="student-page-section">
            <div
                class="{{ $cmsPreview && $linksSectionKey !== '' ? 'cms-preview-editable' : '' }}"
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
                </div>
            </div>

            <div
                class="{{ $cmsPreview && $linksItemsSectionKey !== '' ? 'cms-preview-editable' : '' }}"
                @if($cmsPreview && $linksItemsSectionKey !== '')
                    data-cms-section="{{ $linksItemsSectionKey }}"
                    data-cms-section-label="{{ $isAdmissionsPage ? 'Application & Form Links' : 'Downloadable Form Links' }}"
                @endif
            >
                <div data-cms-boundary class="cms-preview-boundary-full">
                    @if($isAdmissionsPage)
                        <div class="student-page-filter" style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                            <style>
                                .admissions-filter-wrapper {
                                    position: relative;
                                    width: max-content;
                                    min-width: 150px;
                                    font-family: inherit;
                                }
                                .admissions-dropdown-selected {
                                    background-color: #7b1113; /* PUP Maroon */
                                    color: white;
                                    padding: 8px 16px;
                                    border-radius: 6px;
                                    cursor: pointer;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    font-weight: 600;
                                    font-size: 13px;
                                    white-space: nowrap;
                                    gap: 10px;
                                    transition: background-color 0.2s ease;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                    user-select: none;
                                }
                                .admissions-dropdown-selected:hover {
                                    background-color: #5c0d0e;
                                }
                                .admissions-dropdown-selected::after {
                                    content: '▼';
                                    font-size: 10px;
                                    margin-left: 5px;
                                    transition: transform 0.2s ease;
                                }
                                .admissions-filter-wrapper.open .admissions-dropdown-selected::after {
                                    transform: rotate(180deg);
                                }
                                .admissions-dropdown-options {
                                    display: none;
                                    position: absolute;
                                    top: 100%;
                                    right: 0;
                                    min-width: 100%;
                                    width: max-content;
                                    background-color: white;
                                    border-radius: 6px;
                                    margin-top: 5px;
                                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                    overflow: hidden;
                                    z-index: 100;
                                    border: 1px solid #eee;
                                }
                                .admissions-filter-wrapper.open .admissions-dropdown-options {
                                    display: block;
                                    animation: fadeInDown 0.2s ease forwards;
                                }
                                .admissions-dropdown-option {
                                    padding: 8px 16px;
                                    cursor: pointer;
                                    font-size: 13px;
                                    color: #333;
                                    transition: background-color 0.2s ease, color 0.2s ease;
                                    white-space: nowrap;
                                }
                                .admissions-dropdown-option:hover {
                                    background-color: #f5f5f5;
                                    color: #7b1113;
                                }
                                .admissions-dropdown-option.active {
                                    background-color: #fdf5f5;
                                    color: #7b1113;
                                    font-weight: 600;
                                    border-left: 3px solid #7b1113;
                                }
                                @keyframes fadeInDown {
                                    from { opacity: 0; transform: translateY(-10px); }
                                    to { opacity: 1; transform: translateY(0); }
                                }
                            </style>
                            <div class="admissions-filter-wrapper" id="admissionsFilterWrapper">
                                <div class="admissions-dropdown-selected" id="admissionsCategorySelected">All Categories</div>
                                <div class="admissions-dropdown-options">
                                    <div class="admissions-dropdown-option active" data-value="All">All Categories</div>
                                    <div class="admissions-dropdown-option" data-value="Applicants">Applicants</div>
                                    <div class="admissions-dropdown-option" data-value="Returning students">Returning students</div>
                                    <div class="admissions-dropdown-option" data-value="Shiftee">Shiftee</div>
                                    <div class="admissions-dropdown-option" data-value="Transferee">Transferee</div>
                                </div>
                                <input type="hidden" id="admissionsCategoryFilter" value="All">
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const filterInput = document.getElementById('admissionsCategoryFilter');
                                const filterWrapper = document.getElementById('admissionsFilterWrapper');
                                const filterSelected = document.getElementById('admissionsCategorySelected');
                                const filterOptions = document.querySelectorAll('.admissions-dropdown-option');
                                
                                if (!filterInput || !filterWrapper) return;

                                filterSelected.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    filterWrapper.classList.toggle('open');
                                });

                                document.addEventListener('click', function(e) {
                                    if (!filterWrapper.contains(e.target)) {
                                        filterWrapper.classList.remove('open');
                                    }
                                });

                                filterOptions.forEach(option => {
                                    option.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        
                                        filterOptions.forEach(opt => opt.classList.remove('active'));
                                        this.classList.add('active');
                                        
                                        const selectedValue = this.getAttribute('data-value');
                                        const selectedText = this.textContent;
                                        
                                        filterSelected.textContent = selectedText;
                                        filterInput.value = selectedValue;
                                        
                                        filterWrapper.classList.remove('open');
                                        
                                        filterLinks(selectedValue);
                                    });
                                });

                                function filterLinks(selectedCategory) {
                                    const linkCards = document.querySelectorAll('.student-link-row');
                                    let visibleCount = 0;

                                    linkCards.forEach(card => {
                                        if (selectedCategory === 'All' || (card.dataset.category && card.dataset.category.toLowerCase() === selectedCategory.toLowerCase())) {
                                            card.style.display = '';
                                            visibleCount++;
                                        } else {
                                            card.style.display = 'none';
                                        }
                                    });

                                    let emptyState = document.getElementById('admissions-empty-state');
                                    if (visibleCount === 0) {
                                        if (!emptyState) {
                                            const emptyHtml = '<p class="student-page-empty" id="admissions-empty-state">No links found for this category.</p>';
                                            document.querySelector('.student-link-list').insertAdjacentHTML('beforeend', emptyHtml);
                                        } else {
                                            emptyState.style.display = '';
                                        }
                                    } else {
                                        if (emptyState) {
                                            emptyState.style.display = 'none';
                                        }
                                    }
                                }
                            });
                        </script>
                    @endif

                    <div class="student-link-list" style="min-height: 250px;">
                        @forelse(($links['items'] ?? []) as $link)
                            @php
                                $href = trim((string) ($link['href'] ?? ''));
                                $isExternal = preg_match('/^https?:\/\//i', $href) === 1;
                                $category = $link['category'] ?? '';
                            @endphp
                            <a
                                href="{{ $href !== '' ? $href : '#' }}"
                                class="student-link-row"
                                data-category="{{ e($category) }}"
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
            </div>
        </section>
    @endif
</div>
