@php
    $pagesEditor = $academicsEditorData['pages'] ?? ($academicsDefaults['pages'] ?? []);
    $programPageConfigs = [
        'degree-programs' => ['label' => 'Degree Programs', 'fallback' => 'assets/static_img/pupillar.jpeg'],
        'diploma-programs' => ['label' => 'Diploma Programs', 'fallback' => 'assets/static_img/pupillar.jpeg'],
        'graduate-programs' => ['label' => 'Graduate Programs', 'fallback' => 'assets/static_img/pupillar.jpeg'],
    ];
@endphp

@foreach($programPageConfigs as $pageKey => $pageConfig)
    @php
        $pageLabel = $pageConfig['label'];
        $pageFallback = $pageConfig['fallback'];
        $pageData = $pagesEditor[$pageKey] ?? [];
        $heroData = $pageData['hero'] ?? [];
        $infoData = $pageData['info'] ?? [];
        $cardsData = $pageData['cards'] ?? [];
        $contactData = $pageData['contact'] ?? [];
        $heroInputId = $idPrefix.'-'.$pageKey.'-hero-image';
        $heroFieldId = $idPrefix.'-'.$pageKey.'-hero-image-field';
        $heroPreview = \App\Support\NewsImage::url($heroData['image'] ?? null, $pageFallback);
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

            <input type="hidden" id="{{ $heroFieldId }}" name="academics[pages][{{ $pageKey }}][hero][image]" value="{{ $heroData['image'] ?? '' }}">

            <div class="form-group">
                <label>Upload Hero Image</label>
                <div class="academics-cms-image-dropzone-shell">
                    <div class="academics-cms-image-dropzone cms-image-dropzone-hero" data-academics-dropzone-for="{{ $heroInputId }}" role="button" tabindex="0" aria-label="Upload hero image">
                        <span class="academics-cms-image-dropzone-preview-column">
                            <span class="academics-cms-image-dropzone-media">
                                <img
                                    src="{{ $heroPreview }}"
                                    alt="{{ $pageLabel }} hero preview"
                                    class="academics-cms-image-dropzone-preview"
                                    data-academics-preview-for="{{ $heroInputId }}"
                                    data-academics-default-src="{{ asset($pageFallback) }}"
                                >
                                <button type="button" class="academics-cms-image-dropzone-remove" data-academics-clear-image-for="{{ $heroInputId }}" aria-label="Delete image" title="Delete image">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </span>
                            <span class="academics-cms-image-dropzone-label">{{ $pageLabel }}</span>
                        </span>
                        <span class="academics-cms-image-dropzone-upload">
                            <span class="academics-cms-image-dropzone-icon">
                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                            </span>
                            <span class="academics-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                            <span class="academics-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this section.</span>
                            <span class="academics-cms-image-dropzone-upload-button">Select image</span>
                            <span class="academics-cms-image-dropzone-file" data-academics-file-name-for="{{ $heroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                        </span>
                    </div>
                </div>
                <input
                    id="{{ $heroInputId }}"
                    class="academics-cms-image-dropzone-input"
                    type="file"
                    name="academics[pages][{{ $pageKey }}][hero][image_file]"
                    accept="image/*"
                    data-academics-image-field-id="{{ $heroFieldId }}"
                >
            </div>

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
                <div class="academics-cms-textarea-field" data-academics-char-limit="500">
                    <textarea name="academics[pages][{{ $pageKey }}][hero][body]" rows="4" maxlength="500" data-academics-char-input>{{ $heroData['body'] ?? '' }}</textarea>
                    <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/500</div>
                </div>
            </div>

            <div class="form-group">
                <label>List Title</label>
                <input type="text" name="academics[pages][{{ $pageKey }}][hero][list_title]" maxlength="255" value="{{ $heroData['list_title'] ?? '' }}">
            </div>

            <div class="academics-cms-card-stack">
                @foreach(($heroData['list_items'] ?? []) as $index => $item)
                    <article class="academics-cms-card-editor">
                        <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                            <h4>List Item {{ $loop->iteration }}</h4>
                        </div>
                        <div class="form-group">
                            <label>Item Text</label>
                            <input type="text" name="academics[pages][{{ $pageKey }}][hero][list_items][{{ $index }}]" maxlength="255" value="{{ $item }}">
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="academics-cms-modal-footer">
                <button type="submit" class="btn btn-primary">{{ $submitLabel($pageLabel.' Hero') }}</button>
            </div>
        </form>
    </section>

    <section class="academics-cms-editor-panel" data-academics-editor-panel="{{ $pageKey }}-info" hidden>
        <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
            @csrf
            <input type="hidden" name="tab_key" value="academics">
            <input type="hidden" name="section_key" value="{{ $pageKey }}-info">
            @if($requestId > 0)
                <input type="hidden" name="request_id" value="{{ $requestId }}">
            @endif

            <div class="academics-cms-form-grid">
                <div class="form-group">
                    <label>Section Tag</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][info][tag]" maxlength="120" value="{{ $infoData['tag'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Section Title</label>
                    <input type="text" name="academics[pages][{{ $pageKey }}][info][title]" maxlength="255" value="{{ $infoData['title'] ?? '' }}">
                </div>
            </div>

            <div class="academics-cms-card-stack">
                @foreach(($infoData['items'] ?? []) as $index => $item)
                    <article class="academics-cms-card-editor">
                        <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                            <h4>Info Item {{ $loop->iteration }}</h4>
                        </div>
                        <div class="academics-cms-form-grid">
                            <div class="form-group">
                                <label>Label</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][info][items][{{ $index }}][label]" maxlength="120" value="{{ $item['label'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Value</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][info][items][{{ $index }}][value]" maxlength="2048" value="{{ $item['value'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Optional Link</label>
                            <input type="text" name="academics[pages][{{ $pageKey }}][info][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="academics-cms-modal-footer">
                <button type="submit" class="btn btn-primary">{{ $submitLabel($pageLabel.' Info') }}</button>
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
                </div>

                @foreach(($cardsData['items'] ?? []) as $index => $item)
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][title]" value="{{ $item['title'] ?? '' }}">
                    <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][body]" value="{{ $item['body'] ?? '' }}">
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
            <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-academics-card-form="{{ $cardsSectionKey }}">
                @csrf
                <input type="hidden" name="tab_key" value="academics">
                <input type="hidden" name="section_key" value="{{ $cardsSectionKey }}">
                <input type="hidden" name="{{ str_replace('-', '_', $cardsSectionKey) }}_version" value="0" data-academics-card-version>
                <input type="hidden" name="{{ str_replace('-', '_', $cardsSectionKey) }}_active_index" value="" data-academics-card-active-index>
                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][tag]" value="{{ $cardsData['tag'] ?? '' }}">
                <input type="hidden" name="academics[pages][{{ $pageKey }}][cards][title]" value="{{ $cardsData['title'] ?? '' }}">
                @if($requestId > 0)
                    <input type="hidden" name="request_id" value="{{ $requestId }}">
                @endif

                <div class="academics-cms-card-stack" data-academics-card-stack="{{ $cardsSectionKey }}">
                    @foreach(($cardsData['items'] ?? []) as $index => $item)
                        @php
                            $cardInputId = $idPrefix.'-'.$cardsSectionKey.'-'.$index.'-image';
                            $cardPreview = \App\Support\NewsImage::url($item['image'] ?? null, $pageFallback);
                        @endphp
                        <article class="academics-cms-card-editor" data-academics-page-card-editor="{{ $cardsSectionKey }}" data-academics-page-card-index="{{ $index }}">
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

                            <div class="form-group">
                                <label>Card Title</label>
                                <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][title]" maxlength="255" value="{{ $item['title'] ?? '' }}">
                            </div>

                            <div class="form-group">
                                <label>Card Description</label>
                                <div class="academics-cms-textarea-field" data-academics-char-limit="255">
                                    <textarea name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][body]" rows="5" maxlength="255" data-academics-char-input>{{ $item['body'] ?? '' }}</textarea>
                                    <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/255</div>
                                </div>
                            </div>

                            <div class="academics-cms-form-grid">
                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][href]" maxlength="2048" value="{{ $item['href'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>CTA Label</label>
                                    <input type="text" name="academics[pages][{{ $pageKey }}][cards][items][{{ $index }}][cta]" maxlength="120" value="{{ $item['cta'] ?? '' }}">
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

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
    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
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
