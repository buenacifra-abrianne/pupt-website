@php
    $studentsDefaults = \App\Support\StudentsCmsContent::defaults();
    $studentsEditorData = \App\Support\StudentsCmsContent::fromInput($studentsEditorData ?? [], null);
    $pageEditor = $studentsEditorData['page'] ?? $studentsDefaults['page'];
    $cardsEditor = $studentsEditorData['cards'] ?? $studentsDefaults['cards'];
    $requiredCardsEditor = collect($studentsDefaults['cards'] ?? [])
        ->filter(fn ($card) => is_array($card) && in_array(strtolower(trim((string) ($card['title'] ?? ''))), ['admissions', 'downloadable forms', 'document requests'], true));
    $existingCardsEditorTitles = collect($cardsEditor)
        ->filter(fn ($card) => is_array($card))
        ->map(fn ($card) => strtolower(trim((string) ($card['title'] ?? ''))))
        ->all();
    $cardsEditor = collect($cardsEditor)
        ->filter(fn ($card) => is_array($card))
        ->concat($requiredCardsEditor->reject(fn ($card) => in_array(strtolower(trim((string) ($card['title'] ?? ''))), $existingCardsEditorTitles, true)))
        ->values()
        ->all();
    $pagesEditor = $studentsEditorData['pages'] ?? ($studentsDefaults['pages'] ?? []);
    $organizationSectionsEditor = $studentsEditorData['organization_sections'] ?? ($studentsDefaults['organization_sections'] ?? []);
    $formClass = $studentsEditorFormClass ?? 'cms-save-form';
    $submitRoute = $studentsEditorSubmitRoute;
    $submitMode = $studentsEditorSubmitMode ?? 'save';
    $requestId = (int) ($studentsEditorRequestId ?? 0);
    $status = strtolower((string) ($studentsEditorStatus ?? ''));
    $idPrefix = trim((string) ($studentsEditorIdPrefix ?? 'students-editor'));
    $studentsPreviewNav = [
        'overview' => 'Overview',
        'admissions' => 'Admissions',
        'downloadable-forms' => 'Downloadable Forms',
        'document-requests' => 'Document Requests',
    ];
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
@endphp

<div class="students-cms-workspace">
    <div class="students-cms-preview-shell">
        <div class="students-cms-preview-nav" role="tablist" aria-label="Students preview sections">
            @foreach($studentsPreviewNav as $routeKey => $routeLabel)
                <button
                    type="button"
                    class="students-cms-preview-nav-btn{{ $routeKey === 'overview' ? ' is-active' : '' }}"
                    data-students-preview-page="{{ $routeKey }}"
                    role="tab"
                    aria-selected="{{ $routeKey === 'overview' ? 'true' : 'false' }}"
                >
                    {{ $routeLabel }}
                </button>
            @endforeach
        </div>

        <div class="students-cms-preview-frame-shell">
            <div class="students-cms-preview-stage">
                <div class="students-cms-preview-canvas">
                    <iframe
                        title="Students page preview"
                        class="students-cms-preview-frame"
                        data-students-preview-frame
                        scrolling="no"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="students-cms-modal" data-students-editor-modal hidden>
    <div class="students-cms-modal-backdrop" data-close-students-editor></div>

    <div class="students-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="students-cms-modal-close" data-close-students-editor aria-label="Close editor">&times;</button>

        <div class="students-cms-modal-header">
            <span class="students-cms-side-kicker">Students Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit students section</h3>
            <p data-students-editor-description>Select a highlighted section from the preview to edit it.</p>
        </div>

        <div class="students-cms-modal-panels">
            @php
                $studentsHeroInputId = $idPrefix.'-students-page-hero-image';
                $studentsHeroFieldId = $idPrefix.'-students-page-hero-image-field';
                $studentsHeroPreview = \App\Support\StudentsCmsContent::resolveImagePath($pageEditor['hero_image'] ?? null, 'assets/static_img/about_header_image.png');
            @endphp
            <section class="students-cms-editor-panel" data-students-editor-panel="page" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="page">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $studentsHeroFieldId }}" name="students[page][hero_image]" value="{{ $pageEditor['hero_image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Hero Image</label>
                        <div class="students-cms-image-dropzone-shell">
                            <div class="students-cms-image-dropzone cms-image-dropzone-hero" data-students-dropzone-for="{{ $studentsHeroInputId }}" role="button" tabindex="0" aria-label="Upload hero image">
                                <span class="students-cms-image-dropzone-preview-column">
                                    <span class="students-cms-image-dropzone-media">
                                        <img
                                            src="{{ $studentsHeroPreview }}"
                                            alt="Students hero image preview"
                                            class="students-cms-image-dropzone-preview"
                                            data-students-preview-for="{{ $studentsHeroInputId }}"
                                            data-students-default-src="{{ asset('assets/static_img/about_header_image.png') }}"
                                        >
                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $studentsHeroInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="students-cms-image-dropzone-label">Hero Image</span>
                                </span>
                                <span class="students-cms-image-dropzone-upload">
                                    <span class="students-cms-image-dropzone-icon">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="students-cms-image-dropzone-upload-copy">Your hero image preview updates instantly while you edit this section.</span>
                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $studentsHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input
                            id="{{ $studentsHeroInputId }}"
                            class="students-cms-image-dropzone-input"
                            type="file"
                            name="students[page][hero_image_file]"
                            accept="image/*"
                            data-students-image-field-id="{{ $studentsHeroFieldId }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>Hero Title</label>
                        <input type="text" name="students[page][title]" maxlength="255" value="{{ $pageEditor['title'] ?? '' }}">
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Page Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="cards_header" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="cards_header">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Section Tag</label>
                            <input type="text" name="students[page][contents_tag]" maxlength="120" value="{{ $pageEditor['contents_tag'] ?? 'Contents' }}">
                        </div>

                        <div class="form-group">
                            <label>Header Title</label>
                            <input type="text" name="students[page][contents_title]" maxlength="255" value="{{ $pageEditor['contents_title'] ?? ($pageEditor['eyebrow'] ?? 'Student Services') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Header Description</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'students[page][contents_description]',
                            'value' => $pageEditor['contents_description'] ?? '',
                            'placeholder' => 'Write the supporting copy shown above the student cards...',
                            'characterLimit' => 100,
                            'counterMode' => 'limit',
                        ])
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Cards Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="cards" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-cards-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="cards">
                    <input type="hidden" name="students_cards_version" value="0" data-students-cards-version>
                    <input type="hidden" name="students_active_card_index" value="" data-students-active-card-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-card-stack" data-students-card-stack>
                        @foreach($cardsEditor as $index => $card)
                            @php
                                $cardInputId = $idPrefix.'-students-card-image-'.$index;
                                $cardPreview = \App\Support\StudentsCmsContent::resolveImagePath($card['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                $cardTitleKey = strtolower(trim((string) ($card['title'] ?? '')));
                                $isProtectedStudentCard = in_array($cardTitleKey, ['admissions', 'downloadable forms', 'document requests'], true);
                            @endphp
                            <article class="students-cms-card-editor" data-students-card-editor data-students-card-index="{{ $index }}">
                                <div class="students-cms-card-editor-head" data-students-card-editor-head>
                                    <div>
                                        <h4>Service Card {{ $loop->iteration }}</h4>
                                        <span>{{ $card['title'] ?? '' }}</span>
                                    </div>
                                    @unless($isProtectedStudentCard)
                                        <button type="button" class="btn students-cms-delete-card" data-remove-students-card>
                                            Delete Service
                                        </button>
                                    @endunless
                                </div>

                                <input type="hidden" name="students[cards][{{ $index }}][image]" value="{{ $card['image'] ?? '' }}" data-students-image-field>

                                <div class="form-group">
                                    <label>Upload Card Image</label>
                                    <div class="students-cms-image-dropzone-shell">
                                        <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $cardInputId }}" role="button" tabindex="0" aria-label="Upload card image">
                                            <span class="students-cms-image-dropzone-preview-column">
                                                <span class="students-cms-image-dropzone-media">
                                                    <img
                                                        src="{{ $cardPreview }}"
                                                        alt="{{ ($card['title'] ?? '') !== '' ? $card['title'] : 'Student card preview' }}"
                                                        class="students-cms-image-dropzone-preview"
                                                        data-students-preview-for="{{ $cardInputId }}"
                                                        data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                    >
                                                    <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $cardInputId }}" aria-label="Delete image" title="Delete image">
                                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                                <span class="students-cms-image-dropzone-label">Card {{ $index + 1 }}</span>
                                            </span>
                                            <span class="students-cms-image-dropzone-upload">
                                                <span class="students-cms-image-dropzone-icon">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                <span class="students-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this card.</span>
                                                <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $cardInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                            </span>
                                        </div>
                                    </div>
                                    <input
                                        id="{{ $cardInputId }}"
                                        class="students-cms-image-dropzone-input"
                                        type="file"
                                        name="students[cards][{{ $index }}][image_file]"
                                        accept="image/*"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="students[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <div class="students-cms-textarea-field" data-students-char-limit="255">
                                        <textarea
                                            name="students[cards][{{ $index }}][description]"
                                            rows="4"
                                            maxlength="255"
                                            data-students-char-input
                                        >{{ $card['description'] ?? '' }}</textarea>
                                        <div class="students-cms-char-counter" data-students-char-counter aria-live="polite">0/255</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="students[cards][{{ $index }}][link]" maxlength="2048" value="{{ $card['link'] ?? '' }}">
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-students-card-template>
                        <article class="students-cms-card-editor" data-students-card-editor data-students-card-index="__INDEX__">
                            <div class="students-cms-card-editor-head" data-students-card-editor-head>
                                <div>
                                    <h4>Service Card __NUMBER__</h4>
                                    <span></span>
                                </div>
                                <button type="button" class="btn students-cms-delete-card" data-remove-students-card>
                                    Delete Service
                                </button>
                            </div>

                            <input type="hidden" name="students[cards][__INDEX__][image]" value="" data-students-image-field>

                            <div class="form-group">
                                <label>Upload Card Image</label>
                                <div class="students-cms-image-dropzone-shell">
                                    <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $idPrefix }}-students-card-image-__INDEX__" role="button" tabindex="0" aria-label="Upload card image">
                                        <span class="students-cms-image-dropzone-preview-column">
                                            <span class="students-cms-image-dropzone-media">
                                                <img
                                                    src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                    alt="Student card preview"
                                                    class="students-cms-image-dropzone-preview"
                                                    data-students-preview-for="{{ $idPrefix }}-students-card-image-__INDEX__"
                                                    data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                >
                                                <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $idPrefix }}-students-card-image-__INDEX__" aria-label="Delete image" title="Delete image">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                            <span class="students-cms-image-dropzone-label">Card __INDEX__</span>
                                        </span>
                                        <span class="students-cms-image-dropzone-upload">
                                            <span class="students-cms-image-dropzone-icon">
                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                            <span class="students-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this card.</span>
                                            <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                            <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $idPrefix }}-students-card-image-__INDEX__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                        </span>
                                    </div>
                                </div>
                                <input
                                    id="{{ $idPrefix }}-students-card-image-__INDEX__"
                                    class="students-cms-image-dropzone-input"
                                    type="file"
                                    name="students[cards][__INDEX__][image_file]"
                                    accept="image/*"
                                >
                            </div>

                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="students[cards][__INDEX__][title]" maxlength="255" value="">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <div class="students-cms-textarea-field" data-students-char-limit="255">
                                    <textarea
                                        name="students[cards][__INDEX__][description]"
                                        rows="4"
                                        maxlength="255"
                                        data-students-char-input
                                    ></textarea>
                                    <div class="students-cms-char-counter" data-students-char-counter aria-live="polite">0/255</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="students[cards][__INDEX__][link]" maxlength="2048" value="">
                            </div>
                        </article>
                    </template>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Cards') }}
                        </button>
                    </div>
                </form>
            </section>

            @php
                $admissionsPage = is_array($pagesEditor['admissions'] ?? null) ? $pagesEditor['admissions'] : ($studentsDefaults['pages']['admissions'] ?? []);
                $admissionsHero = is_array($admissionsPage['hero'] ?? null) ? $admissionsPage['hero'] : [];
                $admissionsInstructions = is_array($admissionsPage['instructions'] ?? null) ? $admissionsPage['instructions'] : [];
                $admissionsContact = is_array($admissionsPage['contact'] ?? null) ? $admissionsPage['contact'] : [];
                $admissionsContactOffices = is_array($admissionsContact['offices'] ?? null) ? $admissionsContact['offices'] : [];
                $admissionsContactPersons = is_array($admissionsContact['persons'] ?? null) ? $admissionsContact['persons'] : [];
                $admissionsLinks = is_array($admissionsPage['links'] ?? null) ? $admissionsPage['links'] : [];
                $admissionsQrCodes = is_array($admissionsPage['qr_codes'] ?? null) ? $admissionsPage['qr_codes'] : [];
                $admissionsHeroInputId = $idPrefix.'-students-admissions-hero-image';
                $admissionsHeroFieldId = $idPrefix.'-students-admissions-hero-image-field';
                $admissionsHeroPreview = \App\Support\StudentsCmsContent::resolveImagePath($admissionsHero['image'] ?? null, 'assets/static_img/about_header_image.png');
            @endphp
            <section class="students-cms-editor-panel" data-students-editor-panel="admissions_hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="admissions_hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $admissionsHeroFieldId }}" name="students[pages][admissions][hero][image]" value="{{ $admissionsHero['image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Hero Image</label>
                        <div class="students-cms-image-dropzone-shell">
                            <div class="students-cms-image-dropzone cms-image-dropzone-hero" data-students-dropzone-for="{{ $admissionsHeroInputId }}" role="button" tabindex="0" aria-label="Upload admissions hero image">
                                <span class="students-cms-image-dropzone-preview-column">
                                    <span class="students-cms-image-dropzone-media">
                                        <img src="{{ $admissionsHeroPreview }}" alt="Admissions hero image preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $admissionsHeroInputId }}" data-students-default-src="{{ asset('assets/static_img/about_header_image.png') }}">
                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $admissionsHeroInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="students-cms-image-dropzone-label">Hero Image</span>
                                </span>
                                <span class="students-cms-image-dropzone-upload">
                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="students-cms-image-dropzone-upload-copy">Use this image at the top of the admissions page.</span>
                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $admissionsHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input id="{{ $admissionsHeroInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][hero][image_file]" accept="image/*" data-students-image-field-id="{{ $admissionsHeroFieldId }}">
                    </div>

                    <div class="form-group">
                        <label>Hero Title</label>
                        <input type="text" name="students[pages][admissions][hero][title]" maxlength="255" value="{{ $admissionsHero['title'] ?? '' }}">
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Admissions Header') }}
                        </button>
                    </div>
                </form>
            </section>

            @php
                $admissionsInstructionsImageInputId = $idPrefix.'-students-admissions-instructions-image';
                $admissionsInstructionsImageFieldId = $idPrefix.'-students-admissions-instructions-image-field';
                $admissionsInstructionsImagePreview = \App\Support\StudentsCmsContent::resolveImagePath($admissionsInstructions['image'] ?? null, 'assets/static_img/pupillar.jpeg');
            @endphp
            <section class="students-cms-editor-panel" data-students-editor-panel="admissions_instructions" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="admissions_instructions">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Instructions Tag</label>
                            <input type="text" name="students[pages][admissions][instructions][tag]" maxlength="120" value="{{ $admissionsInstructions['tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Instructions Title</label>
                            <input type="text" name="students[pages][admissions][instructions][title]" maxlength="255" value="{{ $admissionsInstructions['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>How to Apply Text</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'students[pages][admissions][instructions][body]',
                            'value' => $admissionsInstructions['body'] ?? '',
                            'placeholder' => 'Write the admissions steps and requirements...',
                            'characterLimit' => 10000,
                            'counterMode' => 'limit',
                        ])
                    </div>

                    <input type="hidden" id="{{ $admissionsInstructionsImageFieldId }}" name="students[pages][admissions][instructions][image]" value="{{ $admissionsInstructions['image'] ?? '' }}" data-students-image-field>
                    <div class="form-group">
                        <label>Upload Step by Step Process Image</label>
                        <div class="students-cms-image-dropzone-shell">
                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $admissionsInstructionsImageInputId }}" role="button" tabindex="0" aria-label="Upload admissions step by step process image">
                                <span class="students-cms-image-dropzone-preview-column">
                                    <span class="students-cms-image-dropzone-media">
                                        <img src="{{ $admissionsInstructionsImagePreview }}" alt="Step by step process preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $admissionsInstructionsImageInputId }}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $admissionsInstructionsImageInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                </span>
                                <span class="students-cms-image-dropzone-upload">
                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="students-cms-image-dropzone-upload-copy">Upload the application guide step by step process image.</span>
                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $admissionsInstructionsImageInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input id="{{ $admissionsInstructionsImageInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][instructions][image_file]" accept="image/*" data-students-image-field-id="{{ $admissionsInstructionsImageFieldId }}">
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Admissions Instructions') }}
                        </button>
                    </div>
                </form>
            </section>

            @php
                $admissionsContactOfficeCount = count($admissionsContactOffices);
                $admissionsContactPersonCount = count($admissionsContactPersons);
            @endphp
            <section class="students-cms-editor-panel" data-students-editor-panel="admissions_contact" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="admissions_contact">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Contact Tag</label>
                            <input type="text" name="students[pages][admissions][contact][tag]" maxlength="120" value="{{ $admissionsContact['tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Contact Title</label>
                            <input type="text" name="students[pages][admissions][contact][title]" maxlength="255" value="{{ $admissionsContact['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contact Description</label>
                        <textarea name="students[pages][admissions][contact][description]" rows="2">{{ $admissionsContact['description'] ?? '' }}</textarea>
                    </div>

                    <div class="students-cms-repeatable" data-students-repeatable="admissions-contact-offices">
                        <div class="students-cms-repeatable-head">
                            <h4>Contact Us Offices</h4>
                            <button type="button" class="btn btn-primary" data-students-add-repeatable="admissions-contact-offices">Add Office</button>
                        </div>
                        <div data-students-repeatable-list="admissions-contact-offices">
                            @foreach($admissionsContactOffices as $index => $item)
                                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Office Name</label>
                                            <input type="text" name="students[pages][admissions][contact][offices][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="text" name="students[pages][admissions][contact][offices][{{ $index }}][value]" maxlength="255" value="{{ $item['value'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Link</label>
                                        <input type="text" name="students[pages][admissions][contact][offices][{{ $index }}][href]" maxlength="255" value="{{ $item['href'] ?? '' }}">
                                    </div>
                                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Office</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="students-cms-repeatable" data-students-repeatable="admissions-contact-persons">
                        <div class="students-cms-repeatable-head">
                            <h4>Contact Persons</h4>
                            <button type="button" class="btn btn-primary" data-students-add-repeatable="admissions-contact-persons">Add Person</button>
                        </div>
                        <div data-students-repeatable-list="admissions-contact-persons">
                            @foreach($admissionsContactPersons as $index => $item)
                                @php
                                    $personInputId = $idPrefix.'-students-admissions-contact-person-'.$index;
                                    $personFieldId = $idPrefix.'-students-admissions-contact-person-field-'.$index;
                                    $personPreview = \App\Support\StudentsCmsContent::resolveImagePath($item['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                @endphp
                                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                                    <input type="hidden" id="{{ $personFieldId }}" name="students[pages][admissions][contact][persons][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}" data-students-image-field>
                                    <div class="form-group">
                                        <label>Upload Profile Photo</label>
                                        <div class="students-cms-image-dropzone-shell">
                                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $personInputId }}" role="button" tabindex="0" aria-label="Upload contact person profile photo">
                                                <span class="students-cms-image-dropzone-preview-column">
                                                    <span class="students-cms-image-dropzone-media">
                                                        <img src="{{ $personPreview }}" alt="Contact person photo preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $personInputId }}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $personInputId }}" aria-label="Delete image" title="Delete image">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                        </button>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-label">Profile Photo</span>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload">
                                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                    <span class="students-cms-image-dropzone-upload-copy">Upload the contact person's profile photo.</span>
                                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $personInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                </span>
                                            </div>
                                        </div>
                                        <input id="{{ $personInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][contact][persons][{{ $index }}][image_file]" accept="image/*" data-students-image-field-id="{{ $personFieldId }}">
                                    </div>
                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="students[pages][admissions][contact][persons][{{ $index }}][name]" maxlength="255" value="{{ $item['name'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Role</label>
                                            <input type="text" name="students[pages][admissions][contact][persons][{{ $index }}][role]" maxlength="255" value="{{ $item['role'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="students[pages][admissions][contact][persons][{{ $index }}][email]" maxlength="255" value="{{ $item['email'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Email Link</label>
                                        <input type="text" name="students[pages][admissions][contact][persons][{{ $index }}][href]" maxlength="255" value="{{ $item['href'] ?? '' }}">
                                    </div>
                                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Person</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Admissions Contact') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="admissions_qr_codes" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="admissions_qr_codes">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>QR Section Tag</label>
                            <input type="text" name="students[pages][admissions][qr_codes][tag]" maxlength="120" value="{{ $admissionsQrCodes['tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>QR Section Title</label>
                            <input type="text" name="students[pages][admissions][qr_codes][title]" maxlength="255" value="{{ $admissionsQrCodes['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="students-cms-repeatable" data-students-repeatable="admissions-qr">
                        <div class="students-cms-repeatable-head">
                            <h4>QR Codes</h4>
                            <button type="button" class="btn btn-primary" data-students-add-repeatable="admissions-qr">Add QR Code</button>
                        </div>
                        <div data-students-repeatable-list="admissions-qr">
                            @foreach(($admissionsQrCodes['items'] ?? []) as $index => $item)
                                @php
                                    $qrInputId = $idPrefix.'-students-admissions-qr-'.$index;
                                    $qrFieldId = $idPrefix.'-students-admissions-qr-field-'.$index;
                                    $qrPreview = \App\Support\StudentsCmsContent::resolveImagePath($item['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                    $qrHasImage = trim((string) ($item['image'] ?? '')) !== '';
                                    $qrFlyerInputId = $idPrefix.'-students-admissions-qr-flyer-'.$index;
                                    $qrFlyerFieldId = $idPrefix.'-students-admissions-qr-flyer-field-'.$index;
                                    $qrFlyerPreview = \App\Support\StudentsCmsContent::resolveImagePath($item['flyer_image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                @endphp
                                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                                    <input type="hidden" id="{{ $qrFieldId }}" name="students[pages][admissions][qr_codes][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}" data-students-image-field>
                                    <div class="form-group">
                                        <label>Upload QR Code Image</label>
                                        <div class="students-cms-image-dropzone-shell">
                                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $qrInputId }}" role="button" tabindex="0" aria-label="Upload QR code image">
                                                <span class="students-cms-image-dropzone-preview-column">
                                                    <span class="students-cms-image-dropzone-media">
                                                        <img src="{{ $qrPreview }}" alt="QR code preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $qrInputId }}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $qrInputId }}" aria-label="Delete image" title="Delete image">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                        </button>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-label">QR Code</span>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload">
                                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                    <span class="students-cms-image-dropzone-upload-copy">Upload a QR code image for applicants.</span>
                                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $qrInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                </span>
                                            </div>
                                        </div>
                                        <input id="{{ $qrInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][qr_codes][items][{{ $index }}][image_file]" accept="image/*" data-students-image-field-id="{{ $qrFieldId }}" data-students-require-file-on-empty="1" @if(!$qrHasImage) required @endif>
                                    </div>
                                    <input type="hidden" id="{{ $qrFlyerFieldId }}" name="students[pages][admissions][qr_codes][items][{{ $index }}][flyer_image]" value="{{ $item['flyer_image'] ?? '' }}" data-students-image-field>
                                    <div class="form-group">
                                        <label>Upload Step by Step Process Image</label>
                                        <div class="students-cms-image-dropzone-shell">
                                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $qrFlyerInputId }}" role="button" tabindex="0" aria-label="Upload flyer or step by step image">
                                                <span class="students-cms-image-dropzone-preview-column">
                                                    <span class="students-cms-image-dropzone-media">
                                                        <img src="{{ $qrFlyerPreview }}" alt="Flyer preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $qrFlyerInputId }}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $qrFlyerInputId }}" aria-label="Delete image" title="Delete image">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                        </button>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload">
                                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                    <span class="students-cms-image-dropzone-upload-copy">Upload the companion step by step process image.</span>
                                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $qrFlyerInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                </span>
                                            </div>
                                        </div>
                                        <input id="{{ $qrFlyerInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][qr_codes][items][{{ $index }}][flyer_image_file]" accept="image/*" data-students-image-field-id="{{ $qrFlyerFieldId }}">
                                    </div>
                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="students[pages][admissions][qr_codes][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <input type="text" name="students[pages][admissions][qr_codes][items][{{ $index }}][description]" maxlength="50" value="{{ $item['description'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Link</label>
                                            <input type="text" name="students[pages][admissions][qr_codes][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                                        </div>
                                    </div>
                                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove QR Code</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Admissions QR Codes') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="admissions_links" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="admissions_links">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Links Tag</label>
                            <input type="text" name="students[pages][admissions][links][tag]" maxlength="120" value="{{ $admissionsLinks['tag'] ?? '' }}" required>
                        </div>
                        <div class="form-group">
                            <label>Links Title</label>
                            <input type="text" name="students[pages][admissions][links][title]" maxlength="255" value="{{ $admissionsLinks['title'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Links Description</label>
                        <textarea name="students[pages][admissions][links][description]" rows="2" required>{{ $admissionsLinks['description'] ?? '' }}</textarea>
                    </div>

                    <div class="students-cms-repeatable" data-students-repeatable="admissions-links">
                        <div class="students-cms-repeatable-head">
                            <h4>Editable Links</h4>
                            <button type="button" class="btn btn-primary" data-students-add-repeatable="admissions-links">Add Link</button>
                        </div>
                        <div data-students-repeatable-list="admissions-links">
                            @foreach(($admissionsLinks['items'] ?? []) as $index => $item)
                                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="students[pages][admissions][links][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>URL</label>
                                            <input type="text" name="students[pages][admissions][links][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="students[pages][admissions][links][items][{{ $index }}][description]" rows="2" required>{{ $item['description'] ?? '' }}</textarea>
                                    </div>
                                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Link</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Admissions Links') }}
                        </button>
                    </div>
                </form>
            </section>

            @php
                $documentRequestsPage = is_array($pagesEditor['document-requests'] ?? null) ? $pagesEditor['document-requests'] : ($studentsDefaults['pages']['document-requests'] ?? []);
                $documentRequestsHero = is_array($documentRequestsPage['hero'] ?? null) ? $documentRequestsPage['hero'] : [];
                $documentRequestsQrCodes = is_array($documentRequestsPage['qr_codes'] ?? null) ? $documentRequestsPage['qr_codes'] : [];
                $documentRequestsHeroInputId = $idPrefix.'-students-document-requests-hero-image';
                $documentRequestsHeroFieldId = $idPrefix.'-students-document-requests-hero-image-field';
                $documentRequestsHeroPreview = \App\Support\StudentsCmsContent::resolveImagePath($documentRequestsHero['image'] ?? null, 'assets/static_img/about_header_image.png');
            @endphp
            <section class="students-cms-editor-panel" data-students-editor-panel="document_requests_hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="document_requests_hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $documentRequestsHeroFieldId }}" name="students[pages][document-requests][hero][image]" value="{{ $documentRequestsHero['image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Hero Image</label>
                        <div class="students-cms-image-dropzone-shell">
                            <div class="students-cms-image-dropzone cms-image-dropzone-hero" data-students-dropzone-for="{{ $documentRequestsHeroInputId }}" role="button" tabindex="0" aria-label="Upload document requests hero image">
                                <span class="students-cms-image-dropzone-preview-column">
                                    <span class="students-cms-image-dropzone-media">
                                        <img src="{{ $documentRequestsHeroPreview }}" alt="Document requests hero image preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $documentRequestsHeroInputId }}" data-students-default-src="{{ asset('assets/static_img/about_header_image.png') }}">
                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $documentRequestsHeroInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="students-cms-image-dropzone-label">Hero Image</span>
                                </span>
                                <span class="students-cms-image-dropzone-upload">
                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="students-cms-image-dropzone-upload-copy">Use this image at the top of the document requests page.</span>
                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $documentRequestsHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input id="{{ $documentRequestsHeroInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][hero][image_file]" accept="image/*" data-students-image-field-id="{{ $documentRequestsHeroFieldId }}">
                    </div>

                    <div class="form-group">
                        <label>Hero Title</label>
                        <input type="text" name="students[pages][document-requests][hero][title]" maxlength="255" value="{{ $documentRequestsHero['title'] ?? '' }}">
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Document Requests Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="document_requests_qr_codes" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="document_requests_qr_codes">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>QR Section Tag</label>
                            <input type="text" name="students[pages][document-requests][qr_codes][tag]" maxlength="120" value="{{ $documentRequestsQrCodes['tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>QR Section Title</label>
                            <input type="text" name="students[pages][document-requests][qr_codes][title]" maxlength="255" value="{{ $documentRequestsQrCodes['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="students-cms-repeatable" data-students-repeatable="document-requests-qr">
                        <div class="students-cms-repeatable-head">
                            <h4>QR Codes</h4>
                            <button type="button" class="btn btn-primary" data-students-add-repeatable="document-requests-qr">Add QR Code</button>
                        </div>
                        <div data-students-repeatable-list="document-requests-qr">
                            @foreach(($documentRequestsQrCodes['items'] ?? []) as $index => $item)
                                @php
                                    $qrInputId = $idPrefix.'-students-document-requests-qr-'.$index;
                                    $qrFieldId = $idPrefix.'-students-document-requests-qr-field-'.$index;
                                    $qrPreview = \App\Support\StudentsCmsContent::resolveImagePath($item['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                    $qrHasImage = trim((string) ($item['image'] ?? '')) !== '';
                                    $qrFlyerInputId = $idPrefix.'-students-document-requests-qr-flyer-'.$index;
                                    $qrFlyerFieldId = $idPrefix.'-students-document-requests-qr-flyer-field-'.$index;
                                    $qrFlyerPreview = \App\Support\StudentsCmsContent::resolveImagePath($item['flyer_image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                @endphp
                                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                                    <input type="hidden" id="{{ $qrFieldId }}" name="students[pages][document-requests][qr_codes][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}" data-students-image-field>
                                    <div class="form-group">
                                        <label>Upload QR Code Image</label>
                                        <div class="students-cms-image-dropzone-shell">
                                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $qrInputId }}" role="button" tabindex="0" aria-label="Upload QR code image">
                                                <span class="students-cms-image-dropzone-preview-column">
                                                    <span class="students-cms-image-dropzone-media">
                                                        <img src="{{ $qrPreview }}" alt="QR code preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $qrInputId }}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $qrInputId }}" aria-label="Delete image" title="Delete image">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                        </button>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-label">QR Code</span>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload">
                                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                    <span class="students-cms-image-dropzone-upload-copy">Upload a QR code image for document requests.</span>
                                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $qrInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                </span>
                                            </div>
                                        </div>
                                        <input id="{{ $qrInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][qr_codes][items][{{ $index }}][image_file]" accept="image/*" data-students-image-field-id="{{ $qrFieldId }}" data-students-require-file-on-empty="1" @if(!$qrHasImage) required @endif>
                                    </div>
                                    <input type="hidden" id="{{ $qrFlyerFieldId }}" name="students[pages][document-requests][qr_codes][items][{{ $index }}][flyer_image]" value="{{ $item['flyer_image'] ?? '' }}" data-students-image-field>
                                    <div class="form-group">
                                        <label>Upload Step by Step Process Image</label>
                                        <div class="students-cms-image-dropzone-shell">
                                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $qrFlyerInputId }}" role="button" tabindex="0" aria-label="Upload flyer or step by step image">
                                                <span class="students-cms-image-dropzone-preview-column">
                                                    <span class="students-cms-image-dropzone-media">
                                                        <img src="{{ $qrFlyerPreview }}" alt="Flyer preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $qrFlyerInputId }}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $qrFlyerInputId }}" aria-label="Delete image" title="Delete image">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                        </button>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload">
                                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                    <span class="students-cms-image-dropzone-upload-copy">Upload the companion step by step process image.</span>
                                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $qrFlyerInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                </span>
                                            </div>
                                        </div>
                                        <input id="{{ $qrFlyerInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][qr_codes][items][{{ $index }}][flyer_image_file]" accept="image/*" data-students-image-field-id="{{ $qrFlyerFieldId }}">
                                    </div>
                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="students[pages][document-requests][qr_codes][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <input type="text" name="students[pages][document-requests][qr_codes][items][{{ $index }}][description]" maxlength="50" value="{{ $item['description'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Link</label>
                                            <input type="text" name="students[pages][document-requests][qr_codes][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                                        </div>
                                    </div>
                                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove QR Code</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Document Requests QR Codes') }}
                        </button>
                    </div>
                </form>
            </section>

            @php
                $formsPage = is_array($pagesEditor['downloadable-forms'] ?? null) ? $pagesEditor['downloadable-forms'] : ($studentsDefaults['pages']['downloadable-forms'] ?? []);
                $formsHero = is_array($formsPage['hero'] ?? null) ? $formsPage['hero'] : [];
                $formsLinks = is_array($formsPage['links'] ?? null) ? $formsPage['links'] : [];
                $formsHeroInputId = $idPrefix.'-students-forms-hero-image';
                $formsHeroFieldId = $idPrefix.'-students-forms-hero-image-field';
                $formsHeroPreview = \App\Support\StudentsCmsContent::resolveImagePath($formsHero['image'] ?? null, 'assets/static_img/about_header_image.png');
                $formsHeroTitleValue = trim((string) ($formsHero['title'] ?? ''));
                if ($formsHeroTitleValue === '' || strcasecmp($formsHeroTitleValue, 'Downloadable Forms') === 0) {
                    $formsHeroTitleValue = 'Downloadables';
                }
            @endphp
            <section class="students-cms-editor-panel" data-students-editor-panel="downloadable_forms_hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="downloadable_forms_hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $formsHeroFieldId }}" name="students[pages][downloadable-forms][hero][image]" value="{{ $formsHero['image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Hero Image</label>
                        <div class="students-cms-image-dropzone-shell">
                            <div class="students-cms-image-dropzone cms-image-dropzone-hero" data-students-dropzone-for="{{ $formsHeroInputId }}" role="button" tabindex="0" aria-label="Upload downloadable forms hero image">
                                <span class="students-cms-image-dropzone-preview-column">
                                    <span class="students-cms-image-dropzone-media">
                                        <img src="{{ $formsHeroPreview }}" alt="Downloadable forms hero image preview" class="students-cms-image-dropzone-preview" data-students-preview-for="{{ $formsHeroInputId }}" data-students-default-src="{{ asset('assets/static_img/about_header_image.png') }}">
                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $formsHeroInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="students-cms-image-dropzone-label">Hero Image</span>
                                </span>
                                <span class="students-cms-image-dropzone-upload">
                                    <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="students-cms-image-dropzone-upload-copy">Use this image at the top of the downloadable forms page.</span>
                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $formsHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input id="{{ $formsHeroInputId }}" class="students-cms-image-dropzone-input" type="file" name="students[pages][downloadable-forms][hero][image_file]" accept="image/*" data-students-image-field-id="{{ $formsHeroFieldId }}">
                    </div>

                    <div class="form-group">
                        <label>Hero Title</label>
                        <input type="text" name="students[pages][downloadable-forms][hero][title]" maxlength="255" value="{{ $formsHeroTitleValue }}">
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Downloadables Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="downloadable_forms_links" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-students-linked-page-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="downloadable_forms_links">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Links Tag</label>
                            <input type="text" name="students[pages][downloadable-forms][links][tag]" maxlength="120" value="{{ $formsLinks['tag'] ?? '' }}" required>
                        </div>
                        <div class="form-group">
                            <label>Links Title</label>
                            <input type="text" name="students[pages][downloadable-forms][links][title]" maxlength="255" value="{{ $formsLinks['title'] ?? '' }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Links Description</label>
                        <textarea name="students[pages][downloadable-forms][links][description]" rows="2" required>{{ $formsLinks['description'] ?? '' }}</textarea>
                    </div>

                    <div class="students-cms-repeatable" data-students-repeatable="forms-links">
                        <div class="students-cms-repeatable-head">
                            <h4>Downloadable Form Links</h4>
                            <button type="button" class="btn btn-primary" data-students-add-repeatable="forms-links">Add Form Link</button>
                        </div>
                        <div data-students-repeatable-list="forms-links">
                            @foreach(($formsLinks['items'] ?? []) as $index => $item)
                                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Form Name</label>
                                            <input type="text" name="students[pages][downloadable-forms][links][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>URL</label>
                                            <input type="text" name="students[pages][downloadable-forms][links][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="students[pages][downloadable-forms][links][items][{{ $index }}][description]" rows="2" required>{{ $item['description'] ?? '' }}</textarea>
                                    </div>
                                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Form Link</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Downloadables Links') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="organizations" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-students-organizations-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="organizations">
                    <input type="hidden" name="students_active_org_key" value="" data-students-active-org-key>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-card-stack" data-students-org-stack>
                        @foreach($organizationSectionsEditor as $sectionIndex => $organizationSection)
                            @foreach(($organizationSection['items'] ?? []) as $orgIndex => $organization)
                                @php
                                    $orgInputId = $idPrefix.'-students-org-image-'.$sectionIndex.'-'.$orgIndex;
                                    $orgFieldId = $idPrefix.'-students-org-image-field-'.$sectionIndex.'-'.$orgIndex;
                                    $orgPreview = \App\Support\StudentsCmsContent::resolveImagePath($organization['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                @endphp
                                <article
                                    class="students-cms-card-editor"
                                    data-students-org-editor
                                    data-students-org-key="{{ $sectionIndex }}-{{ $orgIndex }}"
                                >
                                <div class="students-cms-card-editor-head" data-students-org-editor-head>
                                        <h4>{{ $organizationSection['title'] ?? 'Organizations' }}</h4>
                                        <span>{{ $organization['abbr'] ?? '' }}</span>
                                    </div>

                                    <input type="hidden" name="students[organization_sections][{{ $sectionIndex }}][title]" value="{{ $organizationSection['title'] ?? '' }}">
                                    <input type="hidden" name="students[organization_sections][{{ $sectionIndex }}][key]" value="{{ $organizationSection['key'] ?? '' }}">
                                    <input
                                        type="hidden"
                                        id="{{ $orgFieldId }}"
                                        name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][image]"
                                        value="{{ $organization['image'] ?? '' }}"
                                        data-students-image-field
                                    >

                                    <div class="form-group">
                                        <label>Upload Organization Image</label>
                                        <div class="students-cms-image-dropzone-shell">
                                            <div class="students-cms-image-dropzone" data-students-dropzone-for="{{ $orgInputId }}" role="button" tabindex="0" aria-label="Upload organization image">
                                                <span class="students-cms-image-dropzone-preview-column">
                                                    <span class="students-cms-image-dropzone-media">
                                                        <img
                                                            src="{{ $orgPreview }}"
                                                            alt="{{ ($organization['title'] ?? '') !== '' ? $organization['title'] : 'Organization image preview' }}"
                                                            class="students-cms-image-dropzone-preview"
                                                            data-students-preview-for="{{ $orgInputId }}"
                                                            data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                        >
                                                        <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="{{ $orgInputId }}" aria-label="Delete image" title="Delete image">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                        </button>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-label">Organization Image</span>
                                                </span>
                                                <span class="students-cms-image-dropzone-upload">
                                                    <span class="students-cms-image-dropzone-icon">
                                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                    </span>
                                                    <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                    <span class="students-cms-image-dropzone-upload-copy">Your organization image preview updates instantly while you edit this entry.</span>
                                                    <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                                    <span class="students-cms-image-dropzone-file" data-students-file-name-for="{{ $orgInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                </span>
                                            </div>
                                        </div>
                                        <input
                                            id="{{ $orgInputId }}"
                                            class="students-cms-image-dropzone-input"
                                            type="file"
                                            name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][image_file]"
                                            accept="image/*"
                                            data-students-image-field-id="{{ $orgFieldId }}"
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label>Organization Name</label>
                                        <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][title]" maxlength="255" value="{{ $organization['title'] ?? '' }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Abbreviation / Caption</label>
                                        <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][abbr]" maxlength="255" value="{{ $organization['abbr'] ?? '' }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Link</label>
                                        <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][link]" maxlength="2048" value="{{ $organization['link'] ?? '' }}">
                                    </div>
                                </article>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Organizations') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-students-preview-pages>
{!! json_encode(($studentsPreviewPages ?? ['overview' => ($studentsPreviewHtml ?? '')]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .students-cms-workspace {
        --students-preview-width: 1520px;
        --students-preview-height: 1800px;
        --students-preview-scale: 1;
        --students-preview-scaled-width: calc(var(--students-preview-width) * var(--students-preview-scale));
        --students-preview-scaled-height: calc(var(--students-preview-height) * var(--students-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .students-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .students-cms-preview-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-content: flex-start;
        margin-bottom: 18px;
    }

    .students-cms-preview-nav-btn {
        border: 1px solid #d7c5bd;
        background: #fff8f5;
        color: #5c0000;
        border-radius: 999px;
        padding: 8px 12px;
        cursor: pointer;
        font: inherit;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .students-cms-preview-nav-btn:hover,
    .students-cms-preview-nav-btn:focus-visible {
        outline: none;
    }

    .students-cms-preview-nav-btn.is-active {
        background: #800000;
        border-color: #800000;
        color: #fff;
    }

    .students-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .students-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .students-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--students-preview-scaled-width);
        max-width: 100%;
        height: var(--students-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .students-cms-preview-frame {
        display: block;
        width: var(--students-preview-width);
        min-width: var(--students-preview-width);
        height: var(--students-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        overflow: hidden;
        transform: scale(var(--students-preview-scale));
        transform-origin: top left;
    }

    .students-cms-modal[hidden] {
        display: none;
    }

    .students-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .students-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .students-cms-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(1080px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        margin: 0;
        overflow: auto;
        border-radius: 24px;
        background: #fffdfc;
        box-shadow: 0 28px 80px rgba(25, 16, 12, 0.28);
    }

    .students-cms-modal-close {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 46px;
        height: 46px;
        border: none;
        border-radius: 14px;
        background: #f7ede8;
        color: #5c0000;
        font-size: 1.6rem;
        cursor: pointer;
    }

    .students-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .students-cms-side-kicker,
    .students-cms-eyebrow {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .students-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .students-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.6;
    }

    .students-cms-modal-panels {
        padding: 22px 24px 24px;
        background: linear-gradient(180deg, rgba(255, 251, 247, 0.92) 0%, #fffdfc 100%);
    }

    .students-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .students-cms-card-stack {
        display: grid;
        gap: 0;
    }

    .students-cms-card-editor {
        padding: 18px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf6 100%);
        display: none;
    }

    .students-cms-card-editor.is-active {
        display: block;
    }

    .students-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .students-cms-card-editor-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .students-cms-card-editor-head span {
        color: #8a7a73;
        font-size: 0.8rem;
    }

    .students-cms-section-divider {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin: 24px 0 16px;
        padding: 14px 16px;
        border-top: 1px solid rgba(127, 17, 19, 0.14);
        border-bottom: 1px solid rgba(127, 17, 19, 0.08);
        background: linear-gradient(90deg, rgba(127, 17, 19, 0.07), rgba(255, 250, 246, 0.78));
        border-radius: 16px;
    }

    .students-cms-section-divider:first-of-type {
        margin-top: 0;
    }

    .students-cms-section-divider span {
        flex: 0 0 auto;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #7f1113;
        color: #fffaf4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .students-cms-section-divider h4 {
        margin: 0;
        color: #5c0000;
        font-size: 0.98rem;
    }

    .students-cms-section-divider p {
        margin: 4px 0 0;
        color: #7b6b63;
        font-size: 0.84rem;
        line-height: 1.5;
    }

    .students-cms-repeatable {
        display: grid;
        gap: 14px;
        margin: 18px 0;
    }

    .students-cms-repeatable-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .students-cms-repeatable-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .students-cms-repeatable [data-students-repeatable-list] {
        display: grid;
        gap: 14px;
    }

    .students-cms-repeatable-item {
        display: grid;
        gap: 12px;
        padding: 16px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.74);
    }

    .students-cms-delete-card {
        border: none;
        border-radius: 12px;
        padding: 0 14px;
        min-width: 128px;
        height: 38px;
        background: rgba(80, 10, 12, 0.96);
        color: #fffaf4;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 10px 18px rgba(32, 8, 8, 0.16);
    }

    .students-cms-image-dropzone-shell {
        width: 100%;
        margin: 0 auto;
    }

    .students-cms-image-dropzone {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
        width: 100%;
        padding: 14px;
        border: 1px dashed #d4af37;
        border-radius: 24px;
        background: linear-gradient(180deg, #fffdf8 0%, #fff8ee 100%);
        cursor: pointer;
        align-items: stretch;
    }

    .students-cms-image-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .students-cms-image-dropzone-preview-column {
        display: flex;
        min-width: 0;
        min-height: 180px;
    }

    .students-cms-image-dropzone-media {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
    }

    .students-cms-image-dropzone-preview {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 180px;
        object-fit: cover;
        border-radius: 18px;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
    }

    .students-cms-image-dropzone-label {
        display: none;
        color: #7f1113;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
    }

    .students-cms-image-dropzone-upload {
        display: grid;
        justify-items: center;
        align-content: center;
        gap: 12px;
        min-width: 0;
        padding: 20px 18px;
        border-radius: 18px;
        background: radial-gradient(circle at top, rgba(151, 26, 33, 0.98), rgba(96, 12, 18, 0.98));
        color: #f8f4ef;
        text-align: center;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
        min-height: 100%;
    }

    .students-cms-image-dropzone-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 82px;
        height: 82px;
        border-radius: 999px;
        background: rgba(73, 8, 13, 0.42);
        color: #f2f0ed;
        font-size: 2rem;
    }

    .students-cms-image-dropzone-upload-title {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .students-cms-image-dropzone-upload-copy {
        display: block;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .students-cms-image-dropzone-upload-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 18px;
        border-radius: 999px;
        background: #fff8f1;
        color: #1b1714;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .students-cms-image-dropzone-file {
        display: block;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.8rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .students-cms-image-dropzone-input {
        display: none;
    }

    .students-cms-image-dropzone-remove {
        position: absolute;
        top: 12px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        padding: 0;
        border: 1px solid rgba(127, 17, 19, 0.14);
        border-radius: 999px;
        background: rgba(255, 253, 250, 0.94);
        color: #7f1113;
        font: inherit;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(92, 12, 6, 0.12);
        backdrop-filter: blur(6px);
    }

    .students-cms-image-dropzone-remove:hover {
        background: #7f1113;
        color: #fff8f1;
    }

    @media (max-width: 460px) {
        .students-cms-image-dropzone {
            grid-template-columns: 1fr;
        }

        .students-cms-image-dropzone-upload {
            min-height: 280px;
        }
    }

    @media (max-width: 640px) {
        .students-cms-image-dropzone-remove {
            top: 12px;
            right: 12px;
        }
    }

    .students-cms-image-dropzone-remove[hidden] {
        display: none;
    }

    .students-cms-upload-hint {
        display: block;
        margin-top: 8px;
        color: #7a6a63;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .students-cms-textarea-field {
        display: grid;
        gap: 8px;
    }

    .students-cms-char-counter {
        justify-self: end;
        color: #8a7a73;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
    }

    .students-cms-char-counter.is-limit {
        color: #b91c1c;
    }

    .students-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .students-cms-modal.is-card-focus .students-cms-modal-header {
        display: none;
    }

    .students-cms-modal.is-card-focus {
        align-items: center !important;
    }

    .students-cms-modal.is-card-focus .students-cms-modal-dialog {
        width: min(760px, calc(100vw - 24px));
        max-width: min(760px, calc(100vw - 24px));
        border-radius: 30px;
        background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
        box-shadow: 0 30px 70px rgba(45, 8, 5, 0.2);
    }

    .students-cms-modal.is-card-focus .students-cms-modal-panels {
        padding: 18px;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf6 0%, #fffdfc 100%);
    }

    .students-cms-editor-panel.is-card-focus form {
        max-width: 680px;
        margin: 0 auto;
    }

    .students-cms-editor-panel.is-card-focus .students-cms-card-stack {
        gap: 0;
    }

    .students-cms-editor-panel.is-card-focus [data-students-org-editor-head] {
        display: none;
    }

    .students-cms-editor-panel.is-card-focus .students-cms-card-editor.is-active {
        padding: 22px;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow:
            0 16px 34px rgba(92, 12, 6, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .students-cms-editor-panel.is-card-focus .students-cms-card-editor.is-active .form-group + .form-group {
        margin-top: 14px;
    }

    .students-cms-modal.is-card-focus .students-cms-modal-close {
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(127, 17, 19, 0.08);
        font-size: 1.35rem;
    }

    @media (max-width: 768px) {
        .students-cms-workspace {
            --students-preview-width: 1440px;
            --students-preview-height: 1760px;
            --students-preview-scale: 0.58;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .students-cms-modal-dialog {
            width: calc(100vw - 20px);
            margin: 10px auto;
            max-height: calc(100vh - 20px);
        }

        .students-cms-modal-panels,
        .students-cms-modal-header {
            padding: 18px 16px;
        }

        .students-cms-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    (() => {
        if (window.__studentsCmsPreviewEditorReady) {
            return;
        }

        const STUDENTS_PREVIEW_MIN_LOADING_MS = 800;
        const STUDENTS_PREVIEW_STORAGE_KEY = `cms:students-preview-route:${window.location.pathname}`;
        const STUDENTS_PREVIEW_LEGACY_STORAGE_KEY = '{{ $idPrefix }}-active-students-preview-page';
        let currentStudentsPreviewRoute = 'overview';
        const modal = document.querySelector('[data-students-editor-modal]');
        const modalTitle = modal?.querySelector('#{{ $idPrefix }}-modal-title');
        const modalDescription = modal?.querySelector('[data-students-editor-description]');
        const panels = Array.from(document.querySelectorAll('[data-students-editor-panel]'));
        const frames = Array.from(document.querySelectorAll('[data-students-preview-frame]'));

        if (!modal || !frames.length) {
            return;
        }

        const getStudentsPreviewPayloads = () => {
            const previewScript = document.querySelector('[data-students-preview-pages]');
            if (!previewScript) {
                return {};
            }

            try {
                return JSON.parse(previewScript.textContent || '{}');
            } catch (_) {
                return {};
            }
        };

        const getStoredStudentsPreviewRoute = () => {
            try {
                return window.localStorage.getItem(STUDENTS_PREVIEW_STORAGE_KEY)
                    || window.localStorage.getItem(STUDENTS_PREVIEW_LEGACY_STORAGE_KEY)
                    || '';
            } catch (_) {
                return '';
            }
        };

        const storeStudentsPreviewRoute = (routeKey) => {
            try {
                const storedRoute = String(routeKey || 'overview');
                window.localStorage.setItem(STUDENTS_PREVIEW_STORAGE_KEY, storedRoute);
                window.localStorage.setItem(STUDENTS_PREVIEW_LEGACY_STORAGE_KEY, storedRoute);
            } catch (_) {
                // Ignore storage failures and keep the route in memory for this session.
            }
        };

        const syncStudentsPreviewNav = (routeKey) => {
            document.querySelectorAll('[data-students-preview-page]').forEach((button) => {
                const isActive = (button.getAttribute('data-students-preview-page') || '') === routeKey;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        const syncEditorsInScope = (scope) => {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
        };

        const closeEditor = () => {
            modal.hidden = true;
            modal.classList.remove('is-card-focus');
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
            panels.forEach((panel) => {
                panel.hidden = true;
                panel.classList.remove('is-card-focus');
            });
        };

        const focusCardEditor = (cardIndex) => {
            if (cardIndex === null || cardIndex === undefined) {
                return;
            }

            const target = modal.querySelector(`[data-students-card-editor][data-students-card-index="${cardIndex}"]`);
            if (!target) {
                return;
            }

            target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            target.querySelector('input, textarea')?.focus();
        };

        const focusOrganizationEditor = (orgKey) => {
            if (!orgKey) {
                return;
            }

            const target = modal.querySelector(`[data-students-org-editor][data-students-org-key="${orgKey}"]`);
            if (!target) {
                return;
            }

            target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            target.querySelector('input, textarea')?.focus();
        };

        const openEditor = (sectionKey, label, options = {}) => {
            const isCardFocus = (
                sectionKey === 'cards'
                && options.cardIndex !== null
                && options.cardIndex !== undefined
                && options.cardIndex !== ''
            ) || (
                sectionKey === 'organizations'
                && String(options.orgKey || '').trim() !== ''
            );

            panels.forEach((panel) => {
                const isActive = panel.getAttribute('data-students-editor-panel') === sectionKey;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);
            });

            modal.classList.toggle('is-card-focus', isCardFocus);

            if (modalTitle) {
                modalTitle.textContent = label || 'Edit students section';
            }

            if (modalDescription) {
                const sectionDescriptions = {
                    cards: 'Manage the public cards shown in the student contents strip.',
                    cards_header: 'Update the heading, title, and supporting copy above the student cards.',
                    admissions_hero: 'Update the admissions subpage header and intro copy.',
                    admissions_instructions: 'Update the admissions application instructions section.',
                    admissions_contact: 'Manage the admissions contact offices and contact person profiles.',
                    admissions_qr_codes: 'Manage the admissions QR codes section.',
                    admissions_links: 'Manage the admissions links section.',
                    document_requests_hero: 'Update the document requests subpage header and intro copy.',
                    document_requests_qr_codes: 'Manage the document requests QR codes section.',
                    downloadable_forms_hero: 'Update the downloadables subpage header and intro copy.',
                    downloadable_forms_links: 'Manage the downloadables links section.',
                };
                modalDescription.textContent = sectionDescriptions[sectionKey] || 'Update the selected student section.';
            }

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            const activePanel = panels.find((panel) => panel.getAttribute('data-students-editor-panel') === sectionKey) || null;
            if (activePanel && typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(activePanel);
            }

            if (sectionKey === 'cards') {
                setActiveCardEditor(options.cardIndex ?? null);
                window.setTimeout(() => focusCardEditor(options.cardIndex ?? null), 40);
            } else if (sectionKey === 'organizations') {
                setActiveOrganizationEditor(options.orgKey ?? '');
                window.setTimeout(() => focusOrganizationEditor(options.orgKey ?? ''), 40);
            } else if (activePanel) {
                const firstField = activePanel.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                window.setTimeout(() => firstField?.focus(), 40);
            }
        };

        const fitStudentsPreview = (frame) => {
            const workspace = frame.closest('.students-cms-workspace');
            const shell = frame.closest('.students-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--students-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--students-preview-scale', `${scale}`);
        };

        const fitAllStudentsPreviews = () => {
            frames.forEach((frame) => {
                scheduleStudentsPreviewSync(frame);
            });
        };

        const scheduleFitAllStudentsPreviews = () => {
            fitAllStudentsPreviews();
            window.setTimeout(fitAllStudentsPreviews, 140);
        };

        const setStudentsPreviewLoading = (frame, isLoading) => {
            if (frame.__studentsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__studentsPreviewLoadingTimeout);
                frame.__studentsPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__studentsPreviewLoadingSession = (frame.__studentsPreviewLoadingSession || 0) + 1;
                frame.__studentsPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__studentsPreviewLoadingSession || 0,
                },
            }));
        };

        const finishStudentsPreviewLoading = (frame) => {
            const activeSession = frame.__studentsPreviewLoadingSession || 0;
            const startedAt = frame.__studentsPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, STUDENTS_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__studentsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__studentsPreviewLoadingTimeout);
            }

            frame.__studentsPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__studentsPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__studentsPreviewLoadingTimeout = null;
            }, remaining);
        };

        const getStudentsPreviewElementBottom = (element) => {
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

        const measureFrame = (frame) => {
            try {
                if (typeof window.measureCmsPreviewFrameHeight === 'function') {
                    const measuredHeight = window.measureCmsPreviewFrameHeight(frame, {
                        scopeSelector: '.main-content',
                    });

                    if (measuredHeight > 0) {
                        return measuredHeight;
                    }
                }

                const doc = frame.contentDocument;
                if (!doc) {
                    return 0;
                }

                const main = doc.querySelector('.main-content');
                const scope = main instanceof HTMLElement ? main : doc.body;

                if (!(scope instanceof HTMLElement)) {
                    return 0;
                }

                const visibleElements = Array.from(scope.children)
                    .filter((element) => isMeasuredElement(element));

                const contentBottom = visibleElements.reduce((maxBottom, element) => {
                    return Math.max(maxBottom, getStudentsPreviewElementBottom(element));
                }, scope.offsetHeight);

                return Math.max(1, Math.ceil(contentBottom));
            } catch (error) {
                console.warn('Unable to size students preview frame.', error);
                return 0;
            }
        };

        const syncStudentsPreviewHeight = (frame, nextHeight) => {
            const workspace = frame.closest('.students-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--students-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
            fitStudentsPreview(frame);
        };

        const scheduleStudentsPreviewSync = (frame) => {
            if (!frame) {
                return;
            }

            if (frame.__studentsPreviewSyncFrame !== undefined && frame.__studentsPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__studentsPreviewSyncFrame);
            }

            frame.__studentsPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureFrame(frame);

                if (measuredHeight > 0) {
                    syncStudentsPreviewHeight(frame, measuredHeight);
                } else {
                    fitStudentsPreview(frame);
                }

                frame.__studentsPreviewSyncFrame = null;
            });
        };

        const queueStudentsPreviewSettledSync = (frame) => {
            scheduleStudentsPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleStudentsPreviewSync(frame), delay);
            });
            finishStudentsPreviewLoading(frame);
        };

        const bindFrame = (frame) => {
            const doc = frame.contentDocument;
            if (!doc) {
                return;
            }

            if (doc.documentElement) {
                doc.documentElement.style.overflow = 'hidden';
            }

            if (doc.body) {
                doc.body.style.overflow = 'hidden';
            }

            if (typeof window.bindCmsPreviewScrollBridge === 'function') {
                window.bindCmsPreviewScrollBridge(frame);
            }

            doc.addEventListener('click', (event) => {
                const addCardTrigger = event.target.closest('[data-students-add-card-trigger]');
                if (addCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    openEditor('cards', 'Add student card');
                    window.setTimeout(() => addCard(), 0);
                    return;
                }

                const editCardTrigger = event.target.closest('[data-students-card-edit]');
                if (editCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = editCardTrigger.closest('[data-students-card-index]');
                    const cardIndex = card?.getAttribute('data-students-card-index') ?? null;
                    openEditor('cards', 'Edit student card', { cardIndex });
                    return;
                }

                const editOrgTrigger = event.target.closest('[data-students-org-edit]');
                if (editOrgTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = editOrgTrigger.closest('[data-students-org-index]');
                    const sectionIndex = card?.getAttribute('data-students-org-section-index') ?? '';
                    const orgIndex = card?.getAttribute('data-students-org-index') ?? '';
                    const orgKey = sectionIndex !== '' && orgIndex !== '' ? `${sectionIndex}-${orgIndex}` : '';
                    openEditor('organizations', 'Edit student organization', { orgKey });
                    return;
                }

                const deleteCardTrigger = event.target.closest('[data-students-card-delete]');
                if (deleteCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = deleteCardTrigger.closest('[data-students-card-index]');
                    const cardIndex = card?.getAttribute('data-students-card-index') ?? null;
                    void confirmDeleteCard(cardIndex);
                    return;
                }

                if (event.target.closest('[data-students-card-index], [data-students-org-index]')) {
                    return;
                }

                const sectionTrigger = event.target.closest('[data-cms-edit-trigger], [data-cms-section]');
                if (sectionTrigger) {
                    event.preventDefault();
                    event.stopPropagation();

                    const sectionKey = sectionTrigger.getAttribute('data-cms-edit-trigger')
                        || sectionTrigger.getAttribute('data-cms-section')
                        || '';
                    const label = sectionTrigger.getAttribute('data-cms-section-label') || 'Edit section';
                    const rawCardIndex = sectionTrigger.getAttribute('data-students-card-index');
                    const cardIndex = rawCardIndex === null ? null : Number(rawCardIndex);
                    const orgSectionIndex = sectionTrigger.getAttribute('data-students-org-section-index');
                    const orgIndex = sectionTrigger.getAttribute('data-students-org-index');
                    const orgKey = orgSectionIndex !== null && orgIndex !== null ? `${orgSectionIndex}-${orgIndex}` : '';

                    openEditor(sectionKey, label, { cardIndex, orgKey });
                    return;
                }

                const anchor = event.target.closest('a');
                if (anchor) {
                    event.preventDefault();
                }
            });

            const schedule = () => queueStudentsPreviewSettledSync(frame);
            const observer = typeof ResizeObserver !== 'undefined'
                ? new ResizeObserver(() => schedule())
                : null;

            doc.querySelectorAll('img').forEach((image) => {
                if (image.dataset.cmsPreviewHeightBound === '1') {
                    return;
                }

                image.dataset.cmsPreviewHeightBound = '1';

                if (image.complete) {
                    return;
                }

                const handleImageSettled = () => schedule();
                image.addEventListener('load', handleImageSettled, { once: true });
                image.addEventListener('error', handleImageSettled, { once: true });
            });

            if (observer && doc.body) {
                observer.observe(doc.body);
            }

            if (observer && doc.documentElement) {
                observer.observe(doc.documentElement);
            }

            schedule();
        };

        window.__studentsPreviewCache = window.__studentsPreviewCache || {};

        const loadFrame = (frame, options = {}) => {
            const targetKey = options.routeKey || currentStudentsPreviewRoute || 'overview';
            const shouldForceReload = options.forceReload === true;

            if (!frame) {
                return;
            }

            if (!shouldForceReload && currentStudentsPreviewRoute === targetKey && (typeof window.hasCmsPreviewFrameContent === 'function' ? window.hasCmsPreviewFrameContent(frame) : !!frame.srcdoc)) {
                storeStudentsPreviewRoute(targetKey);
                syncStudentsPreviewNav(targetKey);
                setStudentsPreviewLoading(frame, true);
                queueStudentsPreviewSettledSync(frame);
                return;
            }

            currentStudentsPreviewRoute = targetKey;
            storeStudentsPreviewRoute(targetKey);
            syncStudentsPreviewNav(targetKey);
            setStudentsPreviewLoading(frame, true);

            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, typeof html === 'string' ? html : '');
                } else {
                    frame.srcdoc = typeof html === 'string' ? html : '';
                }
            };

            if (!shouldForceReload && window.__studentsPreviewCache[targetKey]) {
                applyHtml(window.__studentsPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/students/${targetKey}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__studentsPreviewCache[targetKey] = previewHtml;
                    if (currentStudentsPreviewRoute === targetKey) {
                        applyHtml(previewHtml);
                    }
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
        };

        frames.forEach((frame) => {
            frame.addEventListener('load', () => {
                bindFrame(frame);
                queueStudentsPreviewSettledSync(frame);
            });

            loadFrame(frame, {
                routeKey: getStoredStudentsPreviewRoute() || 'overview',
            });
        });

        document.querySelectorAll('[data-students-preview-page]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const routeKey = button.getAttribute('data-students-preview-page') || 'overview';
                const frame = document.querySelector('[data-students-preview-frame]');
                if (!frame) {
                    return;
                }

                loadFrame(frame, { routeKey });
            });
        });

        document.querySelectorAll('[data-close-students-editor]').forEach((trigger) => {
            trigger.addEventListener('click', closeEditor);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeEditor();
            }
        });

        const cardTemplate = modal.querySelector('[data-students-card-template]');
        const cardStack = modal.querySelector('[data-students-card-stack]');
        const cardsForm = modal.querySelector('[data-students-cards-form]');
        const versionInput = modal.querySelector('[data-students-cards-version]');
        const activeCardIndexInput = modal.querySelector('[data-students-active-card-index]');
        const orgStack = modal.querySelector('[data-students-org-stack]');
        const activeOrgKeyInput = modal.querySelector('[data-students-active-org-key]');

        const bumpCardsVersion = () => {
            if (versionInput) {
                versionInput.value = String(Date.now());
            }
        };

        const shouldTrackStudentsCardField = (target) => {
            if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
                return false;
            }

            const type = (target.type || '').toLowerCase();
            return type !== 'file'
                && type !== 'hidden'
                && type !== 'submit'
                && type !== 'button'
                && type !== 'reset';
        };

        const bindStudentsCardsDirtyTracking = () => {
            if (!cardsForm || cardsForm.dataset.studentsDirtyTrackingBound === '1') {
                return;
            }

            cardsForm.dataset.studentsDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackStudentsCardField(event.target)) {
                    return;
                }

                bumpCardsVersion();
                relabelCards();
            };

            cardsForm.addEventListener('input', markDirty);
            cardsForm.addEventListener('change', markDirty);
        };

        const relabelCards = () => {
            const editors = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? []);

            editors.forEach((editor, index) => {
                const displayNumber = index + 1;
                const headTitle = editor.querySelector('[data-students-card-editor-head] h4');
                const headSubtitle = editor.querySelector('[data-students-card-editor-head] span');
                const titleInput = editor.querySelector('input[name*="[title]"]');
                const dropzoneTitle = editor.querySelector('.students-cms-image-dropzone-label');

                if (headTitle) {
                    headTitle.textContent = `Service Card ${displayNumber}`;
                }

                if (headSubtitle) {
                    headSubtitle.textContent = String(titleInput?.value || '').trim();
                }

                if (dropzoneTitle) {
                    dropzoneTitle.textContent = `Card ${displayNumber}`;
                }
            });
        };

        const submitCardsForm = () => {
            if (!cardsForm) {
                return;
            }

            if (typeof cardsForm.requestSubmit === 'function') {
                cardsForm.requestSubmit();
                return;
            }

            cardsForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const deleteCardByIndex = (cardIndex) => {
            if (cardIndex === null || cardIndex === undefined) {
                return false;
            }

            const targetEditor = modal.querySelector(`[data-students-card-editor][data-students-card-index="${cardIndex}"]`);
            if (!targetEditor) {
                return false;
            }

            targetEditor.remove();
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor();

            return true;
        };

        const confirmDeleteCard = async (cardIndex) => {
            const targetEditor = modal.querySelector(`[data-students-card-editor][data-students-card-index="${cardIndex}"]`);
            if (!targetEditor) {
                return;
            }

            const titleInput = targetEditor.querySelector('input[name*="[title]"]');
            const cardTitle = String(titleInput?.value || '').trim();
            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Card',
                    message: cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this student card?',
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(
                    cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this student card?'
                );
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteCardByIndex(cardIndex);
            if (!deleted) {
                return;
            }

            submitCardsForm();
        };

        const setActiveCardEditor = (cardIndex = null) => {
            const editors = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? []);

            if (!editors.length) {
                if (activeCardIndexInput) {
                    activeCardIndexInput.value = '';
                }
                return;
            }

            let targetEditor = null;

            if (cardIndex !== null && cardIndex !== undefined) {
                targetEditor = editors.find((editor) => editor.getAttribute('data-students-card-index') === String(cardIndex)) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                editor.classList.toggle('is-active', editor === targetEditor);
            });

            if (activeCardIndexInput) {
                activeCardIndexInput.value = targetEditor?.getAttribute('data-students-card-index') || '';
            }
        };

        const setActiveOrganizationEditor = (orgKey = '') => {
            const editors = Array.from(orgStack?.querySelectorAll('[data-students-org-editor]') ?? []);

            if (!editors.length) {
                if (activeOrgKeyInput) {
                    activeOrgKeyInput.value = '';
                }
                return;
            }

            let targetEditor = null;

            if (orgKey !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-students-org-key') === orgKey) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                editor.classList.toggle('is-active', editor === targetEditor);
            });

            if (activeOrgKeyInput) {
                activeOrgKeyInput.value = targetEditor?.getAttribute('data-students-org-key') || '';
            }
        };

        const nextCardIndex = () => {
            const indexes = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? [])
                .map((editor) => Number(editor.getAttribute('data-students-card-index') || '0'))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const initStudentsImageDropzones = (scope = document) => {
            scope.querySelectorAll('.students-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.studentsDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-students-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-students-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-students-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-students-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-students-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-students-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-students-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-students-clear-image-for="${input.id}"]`);
                const imageField = input.dataset.studentsImageFieldId
                    ? document.getElementById(input.dataset.studentsImageFieldId)
                    : (input.closest('[data-students-card-editor]')?.querySelector('[data-students-image-field]') || null);

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.studentsDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.studentsDefaultSrc || '';

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;

                    if (input.dataset.studentsRequireFileOnEmpty === '1') {
                        input.required = !hasImage;
                    }
                };

                const prepareImageFile = async (file) => {
                    if (!file || !window.CmsImageEditor) {
                        return file;
                    }

                    const editedFile = await window.CmsImageEditor.editFile(file, {
                        input,
                        previewElement: previewEl,
                    });

                    if (editedFile && editedFile !== file) {
                        window.CmsImageEditor.setInputFile(input, editedFile);
                    }

                    return editedFile;
                };

                const applyFile = (file) => {
                    if (!file) {
                        syncRemoveState();
                        return;
                    }

                    fileNameEl.textContent = `Selected: ${file.name}`;

                    if (previewEl) {
                        previewEl.src = URL.createObjectURL(file);
                    }

                    syncRemoveState();
                };

                input.addEventListener('change', async () => {
                    const file = await prepareImageFile(input.files && input.files[0] ? input.files[0] : null);
                    if (!file) {
                        input.value = '';
                    }
                    applyFile(file);
                });

                label.addEventListener('click', (event) => {
                    if (event.target.closest('[data-students-clear-image-for]')) {
                        return;
                    }

                    input.click();
                });

                label.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    input.click();
                });

                label.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    label.classList.add('dragover');
                });

                label.addEventListener('dragleave', () => {
                    label.classList.remove('dragover');
                });

                label.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    label.classList.remove('dragover');

                    const file = event.dataTransfer?.files?.[0] ?? null;
                    if (!file) {
                        return;
                    }

                    const editedFile = await prepareImageFile(file);
                    if (!editedFile) {
                        input.value = '';
                        applyFile(null);
                        return;
                    }

                    window.CmsImageEditor?.setInputFile(input, editedFile);
                    applyFile(editedFile);
                });

                removeButton?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    input.value = '';
                    if (imageField) {
                        imageField.value = '';
                    }
                    if (previewEl && defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncRemoveState();
            });
        };

        const initStudentsCharCounters = (scope = document) => {
            scope.querySelectorAll('[data-students-char-limit]').forEach((field) => {
                if (field.dataset.studentsCharCounterBound === '1') {
                    return;
                }

                const input = field.querySelector('[data-students-char-input]');
                const counter = field.querySelector('[data-students-char-counter]');
                const limit = Number(field.getAttribute('data-students-char-limit') || input?.getAttribute('maxlength') || 0);

                if (!input || !counter || limit <= 0) {
                    return;
                }

                field.dataset.studentsCharCounterBound = '1';
                input.setAttribute('maxlength', String(limit));

                const syncCounter = () => {
                    const chars = Array.from(input.value || '');
                    if (chars.length > limit) {
                        input.value = chars.slice(0, limit).join('');
                    }

                    const count = Array.from(input.value || '').length;
                    counter.textContent = `${count}/${limit}`;
                    counter.classList.toggle('is-limit', count >= limit);
                };

                input.addEventListener('input', syncCounter);
                syncCounter();
            });
        };

        const addCard = () => {
            if (!cardTemplate || !cardStack) {
                return;
            }

            const index = nextCardIndex();
            const fragment = cardTemplate.content.cloneNode(true);

            fragment.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/__INDEX__/g, String(index));
            });

            fragment.querySelectorAll('[data-students-card-index]').forEach((element) => {
                element.setAttribute('data-students-card-index', String(index));
            });

            const dropzoneId = `{{ $idPrefix }}-students-card-image-${index}`;
            const dropzoneInput = fragment.querySelector('.students-cms-image-dropzone-input');
            const dropzoneLabel = fragment.querySelector('.students-cms-image-dropzone');
            const dropzonePreview = fragment.querySelector('[data-students-preview-for]');
            const dropzoneFileName = fragment.querySelector('[data-students-file-name-for]');
            const dropzoneTitle = fragment.querySelector('.students-cms-image-dropzone-label');
            const dropzoneRemove = fragment.querySelector('[data-students-clear-image-for]');

            if (dropzoneInput) {
                dropzoneInput.id = dropzoneId;
            }

            if (dropzoneLabel) {
                dropzoneLabel.setAttribute('data-students-dropzone-for', dropzoneId);
            }

            if (dropzonePreview) {
                dropzonePreview.setAttribute('data-students-preview-for', dropzoneId);
            }

            if (dropzoneFileName) {
                dropzoneFileName.setAttribute('data-students-file-name-for', dropzoneId);
            }

            if (dropzoneRemove) {
                dropzoneRemove.setAttribute('data-students-clear-image-for', dropzoneId);
            }

            if (dropzoneTitle) {
                dropzoneTitle.textContent = `Card ${index + 1}`;
            }

            cardStack.appendChild(fragment);
            initStudentsImageDropzones(cardStack);
            initStudentsCharCounters(cardStack);
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor(index);
            focusCardEditor(index);
        };

        const nextRepeatableIndex = (list) => {
            const indexes = Array.from(list?.querySelectorAll('[name]') ?? [])
                .map((field) => {
                    const match = String(field.name || '').match(/\[items\]\[(\d+)\]/);
                    return match ? Number(match[1]) : -1;
                })
                .filter((value) => Number.isFinite(value) && value >= 0);

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const repeatableTemplates = {
            'admissions-links': (index) => `
                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="students[pages][admissions][links][items][${index}][label]" maxlength="255" value="" required>
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <input type="text" name="students[pages][admissions][links][items][${index}][href]" maxlength="2048" value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="students[pages][admissions][links][items][${index}][description]" rows="2" required></textarea>
                    </div>
                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Link</button>
                </div>
            `,
            'admissions-contact-offices': (index) => `
                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Office Name</label>
                            <input type="text" name="students[pages][admissions][contact][offices][${index}][label]" maxlength="255" value="">
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="students[pages][admissions][contact][offices][${index}][value]" maxlength="255" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone Link</label>
                        <input type="text" name="students[pages][admissions][contact][offices][${index}][href]" maxlength="255" value="">
                    </div>
                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Office</button>
                </div>
            `,
            'admissions-contact-persons': (index) => {
                const inputId = `{{ $idPrefix }}-students-admissions-contact-person-${index}`;
                const fieldId = `{{ $idPrefix }}-students-admissions-contact-person-field-${index}`;

                return `
                    <div class="students-cms-repeatable-item" data-students-repeatable-item>
                        <input type="hidden" id="${fieldId}" name="students[pages][admissions][contact][persons][${index}][image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload Profile Photo</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${inputId}" role="button" tabindex="0" aria-label="Upload contact person profile photo">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Contact person photo preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${inputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${inputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">Profile Photo</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload the contact person's profile photo.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${inputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${inputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][contact][persons][${index}][image_file]" accept="image/*" data-students-image-field-id="${fieldId}">
                        </div>
                        <div class="students-cms-form-grid">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="students[pages][admissions][contact][persons][${index}][name]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" name="students[pages][admissions][contact][persons][${index}][role]" maxlength="255" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="students[pages][admissions][contact][persons][${index}][email]" maxlength="255" value="">
                        </div>
                        <div class="form-group">
                            <label>Email Link</label>
                            <input type="text" name="students[pages][admissions][contact][persons][${index}][href]" maxlength="255" value="">
                        </div>
                        <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Person</button>
                    </div>
                `;
            },
            'forms-links': (index) => `
                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Form Name</label>
                            <input type="text" name="students[pages][downloadable-forms][links][items][${index}][label]" maxlength="255" value="" required>
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <input type="text" name="students[pages][downloadable-forms][links][items][${index}][href]" maxlength="2048" value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="students[pages][downloadable-forms][links][items][${index}][description]" rows="2" required></textarea>
                    </div>
                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Form Link</button>
                </div>
            `,
            'admissions-qr': (index) => {
                const inputId = `{{ $idPrefix }}-students-admissions-qr-${index}`;
                const fieldId = `{{ $idPrefix }}-students-admissions-qr-field-${index}`;
                const flyerInputId = `{{ $idPrefix }}-students-admissions-qr-flyer-${index}`;
                const flyerFieldId = `{{ $idPrefix }}-students-admissions-qr-flyer-field-${index}`;

                return `
                    <div class="students-cms-repeatable-item" data-students-repeatable-item>
                        <input type="hidden" id="${fieldId}" name="students[pages][admissions][qr_codes][items][${index}][image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload QR Code Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${inputId}" role="button" tabindex="0" aria-label="Upload QR code image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="QR code preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${inputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${inputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">QR Code</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload a QR code image for applicants.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${inputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${inputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][qr_codes][items][${index}][image_file]" accept="image/*" data-students-image-field-id="${fieldId}" data-students-require-file-on-empty="1" required>
                        </div>
                        <input type="hidden" id="${flyerFieldId}" name="students[pages][admissions][qr_codes][items][${index}][flyer_image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload Step by Step Process Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${flyerInputId}" role="button" tabindex="0" aria-label="Upload flyer or step by step image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Flyer preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${flyerInputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${flyerInputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload the companion step by step process image.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${flyerInputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${flyerInputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][qr_codes][items][${index}][flyer_image_file]" accept="image/*" data-students-image-field-id="${flyerFieldId}">
                        </div>
                        <div class="students-cms-form-grid">
                            <div class="form-group">
                                <label>Label</label>
                                <input type="text" name="students[pages][admissions][qr_codes][items][${index}][label]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="students[pages][admissions][qr_codes][items][${index}][description]" maxlength="50" value="">
                            </div>
                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="students[pages][admissions][qr_codes][items][${index}][href]" maxlength="2048" value="">
                            </div>
                        </div>
                        <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove QR Code</button>
                    </div>
                `;
            },
            'document-requests-qr': (index) => {
                const inputId = `{{ $idPrefix }}-students-document-requests-qr-${index}`;
                const fieldId = `{{ $idPrefix }}-students-document-requests-qr-field-${index}`;
                const flyerInputId = `{{ $idPrefix }}-students-document-requests-qr-flyer-${index}`;
                const flyerFieldId = `{{ $idPrefix }}-students-document-requests-qr-flyer-field-${index}`;

                return `
                    <div class="students-cms-repeatable-item" data-students-repeatable-item>
                        <input type="hidden" id="${fieldId}" name="students[pages][document-requests][qr_codes][items][${index}][image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload QR Code Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${inputId}" role="button" tabindex="0" aria-label="Upload QR code image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="QR code preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${inputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${inputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">QR Code</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload a QR code image for document requests.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${inputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${inputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][qr_codes][items][${index}][image_file]" accept="image/*" data-students-image-field-id="${fieldId}" data-students-require-file-on-empty="1" required>
                        </div>
                        <input type="hidden" id="${flyerFieldId}" name="students[pages][document-requests][qr_codes][items][${index}][flyer_image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload Step by Step Process Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${flyerInputId}" role="button" tabindex="0" aria-label="Upload flyer or step by step image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Flyer preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${flyerInputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${flyerInputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload the companion step by step process image.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${flyerInputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${flyerInputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][qr_codes][items][${index}][flyer_image_file]" accept="image/*" data-students-image-field-id="${flyerFieldId}">
                        </div>
                        <div class="students-cms-form-grid">
                            <div class="form-group">
                                <label>Label</label>
                                <input type="text" name="students[pages][document-requests][qr_codes][items][${index}][label]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="students[pages][document-requests][qr_codes][items][${index}][description]" maxlength="50" value="">
                            </div>
                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="students[pages][document-requests][qr_codes][items][${index}][href]" maxlength="2048" value="">
                            </div>
                        </div>
                        <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove QR Code</button>
                    </div>
                `;
            },
        };

        modal.addEventListener('click', (event) => {
            const addTrigger = event.target.closest('[data-add-students-card]');
            if (addTrigger) {
                event.preventDefault();
                addCard();
                return;
            }

            const removeTrigger = event.target.closest('[data-remove-students-card]');
            if (removeTrigger) {
                event.preventDefault();
                const editor = removeTrigger.closest('[data-students-card-editor]');
                if (!editor) {
                    return;
                }

                const cardIndex = editor.getAttribute('data-students-card-index');
                void confirmDeleteCard(cardIndex === null ? null : Number(cardIndex));
            }

            const addRepeatableTrigger = event.target.closest('[data-students-add-repeatable]');
            if (addRepeatableTrigger) {
                event.preventDefault();
                const key = addRepeatableTrigger.getAttribute('data-students-add-repeatable') || '';
                const list = modal.querySelector(`[data-students-repeatable-list="${key}"]`);
                const template = repeatableTemplates[key];

                if (!list || typeof template !== 'function') {
                    return;
                }

                const index = nextRepeatableIndex(list);
                list.insertAdjacentHTML('beforeend', template(index));
                initStudentsImageDropzones(list);
                const latest = list.lastElementChild;
                latest?.querySelector('input:not([type="hidden"]), textarea')?.focus();
                return;
            }

            const removeRepeatableTrigger = event.target.closest('[data-students-remove-repeatable]');
            if (removeRepeatableTrigger) {
                event.preventDefault();
                removeRepeatableTrigger.closest('[data-students-repeatable-item]')?.remove();
            }
        });

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || data.type !== 'cms-students-preview-height') {
                return;
            }

            const targetFrame = Array.from(document.querySelectorAll('[data-students-preview-frame]'))
                .find((frame) => frame.contentWindow === event.source);

            if (!targetFrame) {
                return;
            }

            syncStudentsPreviewHeight(targetFrame, data.height);
        });

        window.addEventListener('resize', () => {
            scheduleFitAllStudentsPreviews();
        });

        window.addEventListener('cms:tab-activated', (event) => {
            const panel = event.detail?.panel;

            frames.forEach((frame) => {
                if (panel && panel.contains(frame)) {
                    loadFrame(frame, {
                        routeKey: currentStudentsPreviewRoute,
                        forceReload: true,
                    });
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 40);
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 180);
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 320);
                }
            });

            scheduleFitAllStudentsPreviews();
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAllStudentsPreviews();
            });

            document.querySelectorAll('.students-cms-preview-frame-shell').forEach((shell) => {
                previewResizeObserver.observe(shell);
            });

            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                previewResizeObserver.observe(mainContent);
            }
        }

        const sidebar = document.getElementById('sidebar');
        if (sidebar && typeof MutationObserver !== 'undefined') {
            const sidebarObserver = new MutationObserver(() => {
                scheduleFitAllStudentsPreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('pageshow', () => {
            scheduleFitAllStudentsPreviews();
        });

        window.addEventListener('load', () => {
            scheduleFitAllStudentsPreviews();
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAllStudentsPreviews();
            }
        });

        window.refreshStudentsCmsPreview = (scope) => {
            const scopedFrames = scope
                ? Array.from(scope.querySelectorAll('[data-students-preview-frame]'))
                : frames;

            scopedFrames.forEach((frame) => loadFrame(frame, {
                routeKey: currentStudentsPreviewRoute,
                forceReload: true,
            }));
        };

        relabelCards();
        setActiveCardEditor();
        setActiveOrganizationEditor('');
        syncEditorsInScope(modal);
        initStudentsImageDropzones(modal);
        initStudentsCharCounters(modal);
        bindStudentsCardsDirtyTracking();
        syncStudentsPreviewNav(currentStudentsPreviewRoute);
        scheduleFitAllStudentsPreviews();
        window.__studentsCmsPreviewEditorReady = true;
    })();
</script>
