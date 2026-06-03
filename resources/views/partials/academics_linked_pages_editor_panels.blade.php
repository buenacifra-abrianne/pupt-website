@php
    $pagesEditor = $academicsEditorData['pages'] ?? ($academicsDefaults['pages'] ?? []);
    $programPageConfigs = [
        'degree-programs' => ['label' => 'Degree Programs', 'fallback' => 'assets/static_img/pupillar.jpeg'],
        'diploma-programs' => ['label' => 'Diploma Programs', 'fallback' => 'assets/static_img/pupillar.jpeg'],
    ];
@endphp

@foreach($programPageConfigs as $pageKey => $pageConfig)
    @php
        $pageLabel = $pageConfig['label'];
        $pageFallback = $pageConfig['fallback'];
        $programsOfferedFieldLabel = $pageKey === 'diploma-programs'
            ? 'Diploma Programs Offered'
            : 'Undergraduate Programs Offered';
        $pageData = $pagesEditor[$pageKey] ?? [];
        $heroData = $pageData['hero'] ?? [];
        $cardsData = $pageData['cards'] ?? [];
        $contactData = $pageData['contact'] ?? [];
        $cardsSectionKey = $pageKey.'-cards';
    @endphp

    <section class="academics-cms-editor-panel" data-academics-editor-panel="{{ $pageKey }}-hero" hidden>
        <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tab_key" value="academics">
            <input type="hidden" name="section_key" value="{{ $pageKey }}-hero">
            @if($requestId > 0)
                <input type="hidden" name="request_id" value="{{ $requestId }}">
            @endif
            <div class="academics-cms-form-grid">
                <div class="form-group">
                    <label>Section Tag</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][hero][tag]" maxlength="120" value="{{ $heroData['tag'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Hero Title</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][hero][title]" maxlength="255" value="{{ $heroData['title'] ?? '' }}">
                </div>
            </div>

            <div class="form-group">
                <label>Subtitle</label>
                <input type="text" name="academics[pages][{{ $pageKey }}][hero][subtitle]" maxlength="255" value="{{ $heroData['subtitle'] ?? '' }}">
            </div>

            <div class="form-group">
                <label>Hero Description</label>
                @include('partials.rich_text_editor', [
                    'name' => 'academics[pages]['.$pageKey.'][hero][body]',
                    'value' => $heroData['body'] ?? '',
                    'placeholder' => 'Write the hero description...',
                    'characterLimit' => 500,
                    'counterMode' => 'limit',
                ])
            </div>

            <div class="academics-cms-modal-footer">
                <button type="submit" class="btn btn-primary">{{ $submitLabel($pageLabel.' Hero') }}</button>
            </div>
        </form>
    </section>

    <section class="academics-cms-editor-panel" data-academics-editor-panel="{{ $cardsSectionKey }}" hidden>
        <div data-academics-page-card-section-shell="{{ $cardsSectionKey }}">
            <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                @csrf
                <input type="hidden" name="tab_key" value="academics">
                <input type="hidden" name="section_key" value="{{ $cardsSectionKey }}">
                @if($requestId > 0)
                    <input type="hidden" name="request_id" value="{{ $requestId }}">
                @endif

                <div class="academics-cms-form-grid" data-academics-card-panel-meta>
                    <div class="form-group">
                        <label>Section Tag</label>
                        <input type="text" name="academics[pages][{{ $pageKey }}][cards][tag]" maxlength="120" value="{{ $cardsData['tag'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Section Title</label>
                        <input type="text" name="academics[pages][{{ $pageKey }}][cards][title]" maxlength="255" value="{{ $cardsData['title'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Higher Education Accreditation PDF URL</label>
                        <input type="text" name="academics[pages][{{ $pageKey }}][cards][higher_education_pdf_url]" maxlength="2048" value="{{ $cardsData['higher_education_pdf_url'] ?? '' }}">
                    </div>
                </div>

                @foreach(($cardsData['items'] ?? []) as $index => $item)
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][badge]" value="{{ $item['badge'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][title]" value="{{ $item['title'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][body]" value="{{ $item['body'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][dept]" value="{{ $item['dept'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_levels]" value="{{ $item['accreditation_levels'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accrediting_institution]" value="{{ $item['accrediting_institution'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_validity]" value="{{ $item['accreditation_validity'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_validity_start]" value="{{ $item['accreditation_validity_start'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_validity_end]" value="{{ $item['accreditation_validity_end'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][href]" value="{{ $item['href'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][cta]" value="{{ $item['cta'] ?? '' }}">
                @endforeach

                <div class="academics-cms-modal-footer">
                    <button type="submit" class="btn btn-primary">{{ $submitLabel($pageLabel.' Cards') }}</button>
                </div>
            </form>
        </div>

        <div data-academics-page-card-item-shell="{{ $cardsSectionKey }}">
            <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-academics-card-form="{{ $cardsSectionKey }}" data-academics-program-card-form data-academics-page-key="{{ $pageKey }}" data-academics-page-label="{{ $pageLabel }}" data-academics-page-fallback="{{ asset($pageFallback) }}">
                @csrf
                <input type="hidden" name="tab_key" value="academics">
                <input type="hidden" name="section_key" value="{{ $cardsSectionKey }}">
                <input type="hidden" name="{{ str_replace('-', '_', $cardsSectionKey) }}_version" value="0" data-academics-card-version>
                <input type="hidden" name="{{ str_replace('-', '_', $cardsSectionKey) }}_active_index" value="" data-academics-card-active-index>
                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][tag]" value="{{ $cardsData['tag'] ?? '' }}">
                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][title]" value="{{ $cardsData['title'] ?? '' }}">
                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][higher_education_pdf_url]" value="{{ $cardsData['higher_education_pdf_url'] ?? '' }}">
                @if($requestId > 0)
                    <input type="hidden" name="request_id" value="{{ $requestId }}">
                @endif

                <div class="academics-cms-card-stack" data-academics-card-stack="{{ $cardsSectionKey }}">
                    @foreach(($cardsData['items'] ?? []) as $index => $item)
                        @php
                            $cardInputId = $idPrefix.'-'.$cardsSectionKey.'-'.$index.'-image';
                            $cardPreview = \App\Support\NewsImage::url($item['image'] ?? null, $pageFallback);
                            $programCardBody = $item['body'] ?? '';
                            $accreditationOptions = ['I', 'II', 'III', 'IV'];
                            $currentAccreditationLevel = trim((string) ($item['accreditation_levels'] ?? ''));
                        @endphp
                        <article class="academics-cms-card-editor" data-academics-page-card-editor="{{ $cardsSectionKey }}" data-academics-page-card-index="{{ $index }}" data-accreditation-validity-range>
                            <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                                <h4>{{ $pageLabel }} Card {{ $loop->iteration }}</h4>
                                <span>{{ $item['href'] ?? '' }}</span>
                            </div>

                            <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}" data-academics-image-field>

                            <div class="form-group">
                                <label>Upload Card Image</label>
                                <div class="academics-cms-image-dropzone-shell">
                                    <div class="academics-cms-image-dropzone" data-academics-dropzone-for="{{ $cardInputId }}" role="button" tabindex="0" aria-label="Upload card image">
                                        <span class="academics-cms-image-dropzone-preview-column">
                                            <span class="academics-cms-image-dropzone-media">
                                                <img
                                                    src="{{ $cardPreview }}"
                                                    alt="{{ $item['title'] ?? ($pageLabel.' card preview') }}"
                                                    class="academics-cms-image-dropzone-preview"
                                                    data-academics-preview-for="{{ $cardInputId }}"
                                                    data-academics-default-src="{{ asset($pageFallback) }}"
                                                >
                                                <button type="button" class="academics-cms-image-dropzone-remove" data-academics-clear-image-for="{{ $cardInputId }}" aria-label="Delete image" title="Delete image">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                            <span class="academics-cms-image-dropzone-label">Card {{ $index + 1 }}</span>
                                        </span>
                                        <span class="academics-cms-image-dropzone-upload">
                                            <span class="academics-cms-image-dropzone-icon">
                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="academics-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                            <span class="academics-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this card.</span>
                                            <span class="academics-cms-image-dropzone-upload-button">Select image</span>
                                            <span class="academics-cms-image-dropzone-file" data-academics-file-name-for="{{ $cardInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                        </span>
                                    </div>
                                </div>
                                <input id="{{ $cardInputId }}" class="academics-cms-image-dropzone-input" type="file" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][image_file]" accept="image/*">
                            </div>

                            <div class="academics-cms-form-grid">
                                <div class="form-group">
                                    <label>Acronym</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][badge]" maxlength="120" value="{{ $item['badge'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>{{ $programsOfferedFieldLabel }}</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][title]" maxlength="255" value="{{ $item['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][dept]" maxlength="255" value="{{ $item['dept'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Accreditation Level</label>
                                    <select name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_levels]">
                                        <option value="">Select accreditation level</option>
                                        @foreach($accreditationOptions as $option)
                                            <option value="{{ $option }}" @selected($currentAccreditationLevel === $option)>{{ $option }}</option>
                                        @endforeach
                                        @if($currentAccreditationLevel !== '' && !in_array($currentAccreditationLevel, $accreditationOptions, true))
                                            <option value="{{ $currentAccreditationLevel }}" selected>{{ $currentAccreditationLevel }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Accrediting Institution</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accrediting_institution]" maxlength="255" value="{{ $item['accrediting_institution'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Validity Start Date</label>
                                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_validity]" value="{{ $item['accreditation_validity'] ?? '' }}" data-accreditation-validity-value>
                                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_validity_start]" value="{{ $item['accreditation_validity_start'] ?? '' }}" data-accreditation-validity-start-value>
                                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][accreditation_validity_end]" value="{{ $item['accreditation_validity_end'] ?? '' }}" data-accreditation-validity-end-value>
                                    <input type="text" value="{{ $item['accreditation_validity_start'] ?? '' }}" placeholder="Select start date" autocomplete="off" data-accreditation-validity-start>
                                </div>
                                <div class="form-group">
                                    <label>Validity End Date</label>
                                    <input type="text" value="{{ $item['accreditation_validity_end'] ?? '' }}" placeholder="Select end date" autocomplete="off" data-accreditation-validity-end>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Card Description</label>
                                @include('partials.rich_text_editor', [
                                    'name' => 'academics[pages]['.$pageKey.'][cards][items]['.$index.'][body]',
                                    'value' => $programCardBody,
                                    'placeholder' => 'Write the program description...',
                                    'characterLimit' => 1500,
                                    'counterMode' => 'limit',
                                ])
                            </div>
                        </article>
                    @endforeach
                </div>

                <template data-academics-program-card-template="{{ $cardsSectionKey }}">
                    <article class="academics-cms-card-editor" data-academics-page-card-editor="{{ $cardsSectionKey }}" data-academics-page-card-index="__INDEX__" data-accreditation-validity-range>
                        <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                            <h4>{{ $pageLabel }} Card</h4>
                            <span>New card</span>
                        </div>

                        <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][image]" value="" data-academics-image-field>

                        <div class="form-group">
                            <label>Upload Card Image</label>
                            <div class="academics-cms-image-dropzone-shell">
                                <div class="academics-cms-image-dropzone" data-academics-dropzone-for="__DROPZONE_ID__" role="button" tabindex="0" aria-label="Upload card image">
                                    <span class="academics-cms-image-dropzone-preview-column">
                                        <span class="academics-cms-image-dropzone-media">
                                            <img
                                                src="{{ asset($pageFallback) }}"
                                                alt="{{ $pageLabel }} card preview"
                                                class="academics-cms-image-dropzone-preview"
                                                data-academics-preview-for="__DROPZONE_ID__"
                                                data-academics-default-src="{{ asset($pageFallback) }}"
                                            >
                                            <button type="button" class="academics-cms-image-dropzone-remove" data-academics-clear-image-for="__DROPZONE_ID__" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="academics-cms-image-dropzone-label">Card</span>
                                    </span>
                                    <span class="academics-cms-image-dropzone-upload">
                                        <span class="academics-cms-image-dropzone-icon">
                                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="academics-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="academics-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this card.</span>
                                        <span class="academics-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="academics-cms-image-dropzone-file" data-academics-file-name-for="__DROPZONE_ID__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="__DROPZONE_ID__" class="academics-cms-image-dropzone-input" type="file" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][image_file]" accept="image/*">
                        </div>

                        <div class="academics-cms-form-grid">
                            <div class="form-group">
                                <label>Acronym</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][badge]" maxlength="120" value="">
                            </div>
                            <div class="form-group">
                                <label>{{ $programsOfferedFieldLabel }}</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][title]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][dept]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][href]" maxlength="2048" value="#">
                            </div>
                            <div class="form-group">
                                <label>Accreditation Level</label>
                                <select name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][accreditation_levels]">
                                    <option value="" selected>Select accreditation level</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Accrediting Institution</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][accrediting_institution]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Validity Start Date</label>
                                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][accreditation_validity]" value="" data-accreditation-validity-value>
                                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][accreditation_validity_start]" value="" data-accreditation-validity-start-value>
                                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][__INDEX__][accreditation_validity_end]" value="" data-accreditation-validity-end-value>
                                <input type="text" value="" placeholder="Select start date" autocomplete="off" data-accreditation-validity-start>
                            </div>
                            <div class="form-group">
                                <label>Validity End Date</label>
                                <input type="text" value="" placeholder="Select end date" autocomplete="off" data-accreditation-validity-end>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Card Description</label>
                            @include('partials.rich_text_editor', [
                                'name' => 'academics[pages]['.$pageKey.'][cards][items][__INDEX__][body]',
                                'value' => '',
                                'placeholder' => 'Write the program description...',
                                'characterLimit' => 1500,
                                'counterMode' => 'limit',
                            ])
                        </div>
                    </article>
                </template>

                <div class="academics-cms-modal-footer">
                    <button type="submit" class="btn btn-primary">{{ $submitLabel($pageLabel.' Card') }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class="academics-cms-editor-panel" data-academics-editor-panel="{{ $pageKey }}-contact" hidden>
        <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
            @csrf
            <input type="hidden" name="tab_key" value="academics">
            <input type="hidden" name="section_key" value="{{ $pageKey }}-contact">
            @if($requestId > 0)
                <input type="hidden" name="request_id" value="{{ $requestId }}">
            @endif

            <div class="academics-cms-form-grid">
                <div class="form-group">
                    <label>Campus Name</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][contact][campus_name]" maxlength="255" value="{{ $contactData['campus_name'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Campus Subtitle</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][contact][campus_sub]" maxlength="255" value="{{ $contactData['campus_sub'] ?? '' }}">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="academics[pages][{{ $pageKey }}][contact][address]" maxlength="2048" value="{{ $contactData['address'] ?? '' }}">
            </div>

            <div class="academics-cms-form-grid">
                <div class="form-group">
                    <label>Section Tag</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][contact][tag]" maxlength="120" value="{{ $contactData['tag'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Section Title</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][contact][title]" maxlength="255" value="{{ $contactData['title'] ?? '' }}">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                    <textarea name="academics[pages][{{ $pageKey }}][contact][description]" rows="4" maxlength="255" data-academics-char-input>{{ $contactData['description'] ?? '' }}</textarea>
                    <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
                </div>
            </div>

            <div class="academics-cms-card-stack">
                @foreach(($contactData['rows'] ?? []) as $index => $row)
                    <article class="academics-cms-card-editor">
                        <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                            <h4>Contact Row {{ $loop->iteration }}</h4>
                        </div>
                        <div class="academics-cms-form-grid">
                            <div class="form-group">
                                <label>Label</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][contact][rows][{{ $index }}][label]" maxlength="120" value="{{ $row['label'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Value</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][contact][rows][{{ $index }}][value]" maxlength="255" value="{{ $row['value'] ?? '' }}">
                            </div>
                        </div>
                        <div class="academics-cms-form-grid">
                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][contact][rows][{{ $index }}][href]" maxlength="2048" value="{{ $row['href'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Tone</label>
                                <select name="academics[pages][{{ $pageKey }}][contact][rows][{{ $index }}][tone]">
                                    <option value="maroon" @selected(($row['tone'] ?? 'maroon') === 'maroon')>Maroon</option>
                                    <option value="gold" @selected(($row['tone'] ?? '') === 'gold')>Gold</option>
                                </select>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="academics-cms-form-grid">
                <div class="form-group">
                    <label>CTA Label</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][contact][cta_label]" maxlength="120" value="{{ $contactData['cta_label'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>CTA Link</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][contact][cta_href]" maxlength="2048" value="{{ $contactData['cta_href'] ?? '' }}">
                </div>
            </div>

            <div class="academics-cms-modal-footer">
                <button type="submit" class="btn btn-primary">{{ $submitLabel($pageLabel.' Contact') }}</button>
            </div>
        </form>
    </section>
@endforeach

@php
    $iapplyData = $pagesEditor['pup-iapply'] ?? [];
    $iapplyHero = $iapplyData['hero'] ?? [];
    $iapplySchedule = $iapplyData['schedule'] ?? [];
    $iapplyGuide = $iapplyData['guide'] ?? [];
    $iapplyReminders = $iapplyData['reminders'] ?? [];
    $calendarData = $pagesEditor['university-calendar'] ?? [];
    $calendarHero = $calendarData['hero'] ?? [];
    $calendarInfo = $calendarData['info'] ?? [];
    $calendarSection = $calendarData['calendar'] ?? [];
    $calendarHeroInputId = $idPrefix.'-university-calendar-hero-image';
    $calendarHeroFieldId = $idPrefix.'-university-calendar-hero-image-field';
    $calendarHeroPreview = \App\Support\NewsImage::url($calendarHero['image'] ?? null, 'assets/static_img/campus_photo.jpg');
@endphp

<section class="academics-cms-editor-panel" data-academics-editor-panel="pup-iapply-hero" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="pup-iapply-hero">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][pup-iapply][hero][tag]" maxlength="120" value="{{ $iapplyHero['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Hero Title</label>
                <input type="text" name="academics[pages][pup-iapply][hero][title]" maxlength="255" value="{{ $iapplyHero['title'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>Subtitle</label>
            <input type="text" name="academics[pages][pup-iapply][hero][subtitle]" maxlength="255" value="{{ $iapplyHero['subtitle'] ?? '' }}">
        </div>

        <div class="form-group">
            <label>Hero Description</label>
            <div class="academics-cms-textarea-field" data-academics-char-limit="1000">
                <textarea name="academics[pages][pup-iapply][hero][body]" rows="5" maxlength="1000" data-academics-char-input>{{ $iapplyHero['body'] ?? '' }}</textarea>
                <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/1000</div>
            </div>
        </div>

        <div class="form-group">
            <label>List Title</label>
            <input type="text" name="academics[pages][pup-iapply][hero][list_title]" maxlength="255" value="{{ $iapplyHero['list_title'] ?? '' }}">
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($iapplyHero['list_items'] ?? []) as $index => $item)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>Benefit {{ $loop->iteration }}</h4>
                    </div>
                    <div class="form-group">
                        <label>Benefit Text</label>
                        <input type="text" name="academics[pages][pup-iapply][hero][list_items][{{ $index }}]" maxlength="255" value="{{ $item }}">
                    </div>
                </article>
            @endforeach
        </div>

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Visual Title</label>
                <input type="text" name="academics[pages][pup-iapply][hero][visual_title]" maxlength="255" value="{{ $iapplyHero['visual_title'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>CTA Label</label>
                <input type="text" name="academics[pages][pup-iapply][hero][cta_label]" maxlength="120" value="{{ $iapplyHero['cta_label'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>Visual Description</label>
            <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                <textarea name="academics[pages][pup-iapply][hero][visual_body]" rows="4" maxlength="255" data-academics-char-input>{{ $iapplyHero['visual_body'] ?? '' }}</textarea>
                <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
            </div>
        </div>

        <div class="form-group">
            <label>CTA Link</label>
            <input type="text" name="academics[pages][pup-iapply][hero][cta_href]" maxlength="2048" value="{{ $iapplyHero['cta_href'] ?? '' }}">
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('PUP iApply Hero') }}</button>
        </div>
    </form>
</section>

<x-calendar-assets />
@once
    <script>
        (() => {
            if (window.__academicsAccreditationCalendarInit) {
                return;
            }

            window.__academicsAccreditationCalendarInit = true;

            const displayFormatter = new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            });

            const toIsoDate = (value) => {
                const raw = String(value || '').trim();
                if (raw === '') {
                    return '';
                }

                if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                    return raw;
                }

                const parsed = new Date(raw);
                if (Number.isNaN(parsed.getTime())) {
                    return '';
                }

                const year = parsed.getFullYear();
                const month = String(parsed.getMonth() + 1).padStart(2, '0');
                const day = String(parsed.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            const formatDisplayDate = (value) => {
                const iso = toIsoDate(value);
                if (iso === '') {
                    return '';
                }

                const [year, month, day] = iso.split('-').map(Number);
                return displayFormatter.format(new Date(year, month - 1, day));
            };

            const parseRange = (value) => {
                const raw = String(value || '').trim();
                if (raw === '') {
                    return { start: '', end: '' };
                }

                const match = raw.match(/^(.*?)(?:\s+-\s+|\s+to\s+)(.*?)$/i);
                if (!match) {
                    return { start: toIsoDate(raw), end: '' };
                }

                return {
                    start: toIsoDate(match[1]),
                    end: toIsoDate(match[2]),
                };
            };

            const buildRangeLabel = (startValue, endValue) => {
                const start = formatDisplayDate(startValue);
                const end = formatDisplayDate(endValue);

                if (start !== '' && end !== '') {
                    return `${start} - ${end}`;
                }

                return start || end || '';
            };

            const syncRangeInputs = (container) => {
                const hiddenValue = container.querySelector('[data-accreditation-validity-value]');
                const hiddenStart = container.querySelector('[data-accreditation-validity-start-value]');
                const hiddenEnd = container.querySelector('[data-accreditation-validity-end-value]');
                const visibleStart = container.querySelector('[data-accreditation-validity-start]');
                const visibleEnd = container.querySelector('[data-accreditation-validity-end]');

                if (!hiddenValue || !hiddenStart || !hiddenEnd || !visibleStart || !visibleEnd) {
                    return;
                }

                const start = toIsoDate(visibleStart.value);
                let end = toIsoDate(visibleEnd.value);

                if (start !== '' && end !== '' && end < start) {
                    end = '';
                    visibleEnd.value = '';
                    visibleEnd._flatpickr?.clear();
                }

                hiddenStart.value = start;
                hiddenEnd.value = end;
                hiddenValue.value = buildRangeLabel(start, end);

                if (visibleEnd._flatpickr) {
                    visibleEnd._flatpickr.set('minDate', start || null);
                }
            };

            const initRangeContainer = (container) => {
                if (!(container instanceof HTMLElement) || container.dataset.accreditationCalendarReady === '1') {
                    return;
                }

                const hiddenValue = container.querySelector('[data-accreditation-validity-value]');
                const hiddenStart = container.querySelector('[data-accreditation-validity-start-value]');
                const hiddenEnd = container.querySelector('[data-accreditation-validity-end-value]');
                const visibleStart = container.querySelector('[data-accreditation-validity-start]');
                const visibleEnd = container.querySelector('[data-accreditation-validity-end]');

                if (!hiddenValue || !hiddenStart || !hiddenEnd || !visibleStart || !visibleEnd) {
                    return;
                }

                const parsedRange = parseRange(hiddenValue.value);
                visibleStart.value = toIsoDate(hiddenStart.value) || parsedRange.start;
                visibleEnd.value = toIsoDate(hiddenEnd.value) || parsedRange.end;

                if (typeof window.CmsCalendar?.init === 'function') {
                    window.CmsCalendar.init(visibleStart, {
                        onChange: () => syncRangeInputs(container),
                    });

                    window.CmsCalendar.init(visibleEnd, {
                        minDate: visibleStart.value || null,
                        onChange: () => syncRangeInputs(container),
                    });
                }

                visibleStart.addEventListener('change', () => syncRangeInputs(container));
                visibleEnd.addEventListener('change', () => syncRangeInputs(container));
                container.dataset.accreditationCalendarReady = '1';

                syncRangeInputs(container);
            };

            const initAllRangeContainers = (scope = document) => {
                scope.querySelectorAll('[data-accreditation-validity-range]').forEach((container) => {
                    initRangeContainer(container);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => initAllRangeContainers());
            } else {
                initAllRangeContainers();
            }

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (!(node instanceof HTMLElement)) {
                            return;
                        }

                        if (node.matches('[data-accreditation-validity-range]')) {
                            initRangeContainer(node);
                            return;
                        }

                        initAllRangeContainers(node);
                    });
                });
            });

            observer.observe(document.documentElement, {
                childList: true,
                subtree: true,
            });
        })();
    </script>
@endonce

<section class="academics-cms-editor-panel" data-academics-editor-panel="pup-iapply-schedule" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="pup-iapply-schedule">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][pup-iapply][schedule][tag]" maxlength="120" value="{{ $iapplySchedule['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="academics[pages][pup-iapply][schedule][title]" maxlength="255" value="{{ $iapplySchedule['title'] ?? '' }}">
            </div>
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($iapplySchedule['items'] ?? []) as $index => $item)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>Schedule Item {{ $loop->iteration }}</h4>
                    </div>
                    <div class="academics-cms-form-grid">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="academics[pages][pup-iapply][schedule][items][{{ $index }}][label]" maxlength="120" value="{{ $item['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Value</label>
                            <input type="text" name="academics[pages][pup-iapply][schedule][items][{{ $index }}][value]" maxlength="2048" value="{{ $item['value'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Optional Link</label>
                        <input type="text" name="academics[pages][pup-iapply][schedule][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                    </div>
                </article>
            @endforeach
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('PUP iApply Schedule') }}</button>
        </div>
    </form>
</section>

<section class="academics-cms-editor-panel" data-academics-editor-panel="pup-iapply-guide" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="pup-iapply-guide">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][pup-iapply][guide][tag]" maxlength="120" value="{{ $iapplyGuide['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="academics[pages][pup-iapply][guide][title]" maxlength="255" value="{{ $iapplyGuide['title'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                <textarea name="academics[pages][pup-iapply][guide][description]" rows="4" maxlength="255" data-academics-char-input>{{ $iapplyGuide['description'] ?? '' }}</textarea>
                <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
            </div>
        </div>

        <div class="form-group">
            <label>Video URL</label>
            <input type="text" name="academics[pages][pup-iapply][guide][video_url]" maxlength="2048" value="{{ $iapplyGuide['video_url'] ?? '' }}">
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('PUP iApply Guide') }}</button>
        </div>
    </form>
</section>

<section class="academics-cms-editor-panel" data-academics-editor-panel="pup-iapply-reminders" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="pup-iapply-reminders">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][pup-iapply][reminders][tag]" maxlength="120" value="{{ $iapplyReminders['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="academics[pages][pup-iapply][reminders][title]" maxlength="255" value="{{ $iapplyReminders['title'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>Notice Title</label>
            <input type="text" name="academics[pages][pup-iapply][reminders][notice_title]" maxlength="255" value="{{ $iapplyReminders['notice_title'] ?? '' }}">
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($iapplyReminders['notice_items'] ?? []) as $index => $item)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>Reminder {{ $loop->iteration }}</h4>
                    </div>
                    <div class="form-group">
                        <label>Reminder Text</label>
                        <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                            <textarea name="academics[pages][pup-iapply][reminders][notice_items][{{ $index }}]" rows="3" maxlength="255" data-academics-char-input>{{ $item }}</textarea>
                            <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="form-group">
            <label>Body Copy</label>
            @include('partials.rich_text_editor', [
                'name' => 'academics[pages][pup-iapply][reminders][body_html]',
                'value' => $iapplyReminders['body_html'] ?? '',
                'placeholder' => 'Write the reminder body copy...',
            ])
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($iapplyReminders['checklist_items'] ?? []) as $index => $item)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>Checklist Item {{ $loop->iteration }}</h4>
                    </div>
                    <div class="form-group">
                        <label>Checklist Text</label>
                        <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                            <textarea name="academics[pages][pup-iapply][reminders][checklist_items][{{ $index }}]" rows="3" maxlength="255" data-academics-char-input>{{ $item }}</textarea>
                            <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('PUP iApply Reminders') }}</button>
        </div>
    </form>
</section>

<section class="academics-cms-editor-panel" data-academics-editor-panel="university-calendar-hero" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="university-calendar-hero">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <input type="hidden" id="{{ $calendarHeroFieldId }}" name="academics[pages][university-calendar][hero][image]" value="{{ $calendarHero['image'] ?? '' }}">

        <div class="form-group">
            <label>Upload Hero Image</label>
            <div class="academics-cms-image-dropzone-shell">
                <div class="academics-cms-image-dropzone cms-image-dropzone-hero" data-academics-dropzone-for="{{ $calendarHeroInputId }}" role="button" tabindex="0" aria-label="Upload hero image">
                    <span class="academics-cms-image-dropzone-preview-column">
                        <span class="academics-cms-image-dropzone-media">
                            <img
                                src="{{ $calendarHeroPreview }}"
                                alt="University Calendar hero preview"
                                class="academics-cms-image-dropzone-preview"
                                data-academics-preview-for="{{ $calendarHeroInputId }}"
                                data-academics-default-src="{{ asset('assets/static_img/campus_photo.jpg') }}"
                            >
                            <button type="button" class="academics-cms-image-dropzone-remove" data-academics-clear-image-for="{{ $calendarHeroInputId }}" aria-label="Delete image" title="Delete image">
                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span class="academics-cms-image-dropzone-label">University Calendar</span>
                    </span>
                    <span class="academics-cms-image-dropzone-upload">
                        <span class="academics-cms-image-dropzone-icon">
                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                        </span>
                        <span class="academics-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                        <span class="academics-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this section.</span>
                        <span class="academics-cms-image-dropzone-upload-button">Select image</span>
                        <span class="academics-cms-image-dropzone-file" data-academics-file-name-for="{{ $calendarHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                    </span>
                </div>
            </div>
            <input
                id="{{ $calendarHeroInputId }}"
                class="academics-cms-image-dropzone-input"
                type="file"
                name="academics[pages][university-calendar][hero][image_file]"
                accept="image/*"
                data-academics-image-field-id="{{ $calendarHeroFieldId }}"
            >
        </div>

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][university-calendar][hero][tag]" maxlength="120" value="{{ $calendarHero['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Hero Title</label>
                <input type="text" name="academics[pages][university-calendar][hero][title]" maxlength="255" value="{{ $calendarHero['title'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>Subtitle</label>
            <input type="text" name="academics[pages][university-calendar][hero][subtitle]" maxlength="255" value="{{ $calendarHero['subtitle'] ?? '' }}">
        </div>

        <div class="form-group">
            <label>Hero Description</label>
            <div class="academics-cms-textarea-field" data-academics-char-limit="500">
                <textarea name="academics[pages][university-calendar][hero][body]" rows="4" maxlength="500" data-academics-char-input>{{ $calendarHero['body'] ?? '' }}</textarea>
                <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/500</div>
            </div>
        </div>

        <div class="form-group">
            <label>List Title</label>
            <input type="text" name="academics[pages][university-calendar][hero][list_title]" maxlength="255" value="{{ $calendarHero['list_title'] ?? '' }}">
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($calendarHero['list_items'] ?? []) as $index => $item)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>List Item {{ $loop->iteration }}</h4>
                    </div>
                    <div class="form-group">
                        <label>Item Text</label>
                        <input type="text" name="academics[pages][university-calendar][hero][list_items][{{ $index }}]" maxlength="255" value="{{ $item }}">
                    </div>
                </article>
            @endforeach
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('University Calendar Hero') }}</button>
        </div>
    </form>
</section>

<section class="academics-cms-editor-panel" data-academics-editor-panel="university-calendar-info" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="university-calendar-info">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][university-calendar][info][tag]" maxlength="120" value="{{ $calendarInfo['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="academics[pages][university-calendar][info][title]" maxlength="255" value="{{ $calendarInfo['title'] ?? '' }}">
            </div>
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($calendarInfo['items'] ?? []) as $index => $item)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>Info Item {{ $loop->iteration }}</h4>
                    </div>
                    <div class="academics-cms-form-grid">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="academics[pages][university-calendar][info][items][{{ $index }}][label]" maxlength="120" value="{{ $item['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Value</label>
                            <input type="text" name="academics[pages][university-calendar][info][items][{{ $index }}][value]" maxlength="2048" value="{{ $item['value'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Optional Link</label>
                        <input type="text" name="academics[pages][university-calendar][info][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                    </div>
                </article>
            @endforeach
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('University Calendar Info') }}</button>
        </div>
    </form>
</section>

<section class="academics-cms-editor-panel" data-academics-editor-panel="university-calendar-calendar" hidden>
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tab_key" value="academics">
        <input type="hidden" name="section_key" value="university-calendar-calendar">
        @if($requestId > 0)
            <input type="hidden" name="request_id" value="{{ $requestId }}">
        @endif

        <div class="academics-cms-form-grid">
            <div class="form-group">
                <label>Section Tag</label>
                <input type="text" name="academics[pages][university-calendar][calendar][tag]" maxlength="120" value="{{ $calendarSection['tag'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="academics[pages][university-calendar][calendar][title]" maxlength="255" value="{{ $calendarSection['title'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>PDF URL or Path</label>
            <input type="text" name="academics[pages][university-calendar][calendar][pdf_url]" maxlength="2048" value="{{ $calendarSection['pdf_url'] ?? '' }}">
        </div>

        <div class="form-group">
            <label>Upload Calendar PDF</label>
            @php
                $calendarPdfPreview = \App\Support\DownloadableFile::url($calendarSection['pdf_url'] ?? null, 'assets/static_img/university_calendar.pdf');
            @endphp
            @if($calendarPdfPreview)
                <p><a href="{{ $calendarPdfPreview }}" target="_blank" rel="noopener">View current calendar PDF</a></p>
            @endif
            <input type="file" name="academics[pages][university-calendar][calendar][pdf_file]" accept="application/pdf,.pdf">
        </div>

        <div class="form-group">
            <label>Calendar Note</label>
            <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                <textarea name="academics[pages][university-calendar][calendar][note]" rows="4" maxlength="255" data-academics-char-input>{{ $calendarSection['note'] ?? '' }}</textarea>
                <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
            </div>
        </div>

        <div class="academics-cms-card-stack">
            @foreach(($calendarSection['actions'] ?? []) as $index => $action)
                <article class="academics-cms-card-editor">
                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                        <h4>Action {{ $loop->iteration }}</h4>
                    </div>
                    <div class="academics-cms-form-grid">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="academics[pages][university-calendar][calendar][actions][{{ $index }}][label]" maxlength="120" value="{{ $action['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Link</label>
                            <input type="text" name="academics[pages][university-calendar][calendar][actions][{{ $index }}][href]" maxlength="2048" value="{{ $action['href'] ?? '' }}">
                        </div>
                    </div>
                    <div class="academics-cms-form-grid">
                        <div class="form-group">
                            <label>Style</label>
                            <select name="academics[pages][university-calendar][calendar][actions][{{ $index }}][style]">
                                <option value="primary" @selected(($action['style'] ?? 'primary') === 'primary')>Primary</option>
                                <option value="outline" @selected(($action['style'] ?? '') === 'outline')>Outline</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="academics-cms-checkbox-label">
                                <input type="hidden" name="academics[pages][university-calendar][calendar][actions][{{ $index }}][download]" value="0">
                                <input type="checkbox" name="academics[pages][university-calendar][calendar][actions][{{ $index }}][download]" value="1" @checked(!empty($action['download']))>
                                Download file
                            </label>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="academics-cms-modal-footer">
            <button type="submit" class="btn btn-primary">{{ $submitLabel('University Calendar') }}</button>
        </div>
    </form>
</section>
