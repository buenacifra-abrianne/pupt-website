@php
    $aboutDefaults = \App\Support\AboutCmsContent::defaults();
    $aboutEditorData = \App\Support\AboutCmsContent::fromInput($aboutEditorData ?? [], null);
    $aboutPreviewData = \App\Support\AboutCmsContent::fromInput($aboutPreviewData ?? $aboutEditorData, null);
    $aboutSections = $aboutEditorData['sections'] ?? [];
    $aboutPreviewSections = $aboutPreviewData['sections'] ?? [];
    $aboutLockedSectionSlugs = ['hymn', 'maps'];
    $aboutSections = array_diff_key($aboutSections, array_flip($aboutLockedSectionSlugs));
    $aboutPreviewSections = array_diff_key($aboutPreviewSections, array_flip($aboutLockedSectionSlugs));
    $overviewEditor = $aboutEditorData['overview'] ?? ($aboutDefaults['overview'] ?? []);
    $formClass = $aboutEditorFormClass ?? 'cms-save-form';
    $submitRoute = $aboutEditorSubmitRoute;
    $submitMode = $aboutEditorSubmitMode ?? 'save';
    $requestId = (int) ($aboutEditorRequestId ?? 0);
    $status = strtolower((string) ($aboutEditorStatus ?? ''));
    $idPrefix = trim((string) ($aboutEditorIdPrefix ?? 'about-editor'));
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
    $historyMonthValue = static function (string $value): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/([A-Za-z]+)\s+(\d{4})/', $value, $match)) {
            $month = date_parse($match[1])['month'] ?? false;
            if ($month !== false && $month >= 1 && $month <= 12) {
                return sprintf('%04d-%02d', (int) $match[2], (int) $month);
            }
        }

        return '';
    };

@endphp

<div class="about-cms-workspace">
    <div class="about-cms-preview-shell">
        <div class="about-cms-preview-head">
            <div>
                <span class="about-cms-eyebrow">About CMS</span>
                <h3>Live website preview</h3>
                <p>Use the preview tabs to inspect each About page section, then click the highlighted edit chips inside the preview.</p>
            </div>

            <div class="about-cms-preview-nav">
                <button type="button" class="about-cms-preview-nav-btn is-active" data-about-preview-page="overview">Overview</button>
                @foreach($aboutSections as $slug => $section)
                    <button type="button" class="about-cms-preview-nav-btn" data-about-preview-page="{{ $slug }}">{{ $section['label'] ?? $slug }}</button>
                @endforeach
            </div>
        </div>

        <div class="about-cms-preview-frame-shell">
            <div class="about-cms-preview-stage">
                <div class="about-cms-preview-canvas">
                    <iframe
                        title="About page preview"
                        class="about-cms-preview-frame"
                        data-about-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="about-cms-modal" data-about-editor-modal hidden>
    <div class="about-cms-modal-backdrop" data-close-about-editor></div>

    <div class="about-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="about-cms-modal-close" data-close-about-editor aria-label="Close editor">&times;</button>

        <div class="about-cms-modal-header">
            <h3 id="{{ $idPrefix }}-modal-title">Edit about section</h3>
            <p data-about-editor-description>Select a section from the preview to start editing.</p>
        </div>

        <div class="about-cms-modal-panels">
            @php
                $aboutCombinedHeaderImage = trim((string) ($overviewEditor['hero_image'] ?? '')) !== ''
                    ? (string) $overviewEditor['hero_image']
                    : (string) ($overviewEditor['section_header_image'] ?? '');
                $aboutHeroInputId = $idPrefix.'-about-hero-image';
                $aboutHeroFieldId = $idPrefix.'-about-hero-image-field';
                $aboutHeroPreview = \App\Support\AboutCmsContent::resolveImagePath($aboutCombinedHeaderImage !== '' ? $aboutCombinedHeaderImage : null, 'assets/static_img/about_header_image.png');
                $aboutSectionHeaderFieldId = $idPrefix.'-about-section-header-image-field';
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $aboutHeroFieldId }}" name="about[overview][hero_image]" value="{{ $aboutCombinedHeaderImage }}">
                    <input type="hidden" id="{{ $aboutSectionHeaderFieldId }}" name="about[overview][section_header_image]" value="{{ $aboutCombinedHeaderImage }}">

                    <div class="about-cms-card-editor is-active">
                        <div class="form-group">
                            <label>Upload Image</label>
                            <div class="about-cms-image-dropzone-shell">
                                <div class="about-cms-image-dropzone cms-image-dropzone-hero" data-about-dropzone-for="{{ $aboutHeroInputId }}" role="button" tabindex="0" aria-label="Upload hero image">
                                    <span class="about-cms-image-dropzone-preview-column">
                                        <span class="about-cms-image-dropzone-media">
                                            <img
                                                src="{{ $aboutHeroPreview }}"
                                                alt="About hero image preview"
                                                class="about-cms-image-dropzone-preview"
                                                data-about-preview-for="{{ $aboutHeroInputId }}"
                                                data-about-default-src="{{ asset('assets/static_img/about_header_image.png') }}"
                                            >
                                            <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $aboutHeroInputId }}" aria-label="Edit image" title="Edit image">
                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                            </button>
                                                        <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $aboutHeroInputId }}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="about-cms-image-dropzone-label">Header Image</span>
                                    </span>
                                    <span class="about-cms-image-dropzone-upload">
                                        <span class="about-cms-image-dropzone-icon">
                                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="about-cms-image-dropzone-upload-copy">This one image is used for both the main hero and the section header.</span>
                                        <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $aboutHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input
                                id="{{ $aboutHeroInputId }}"
                                class="about-cms-image-dropzone-input"
                                type="file"
                                name="about[overview][hero_image_file]"
                                accept="image/*"
                                data-about-image-field-id="{{ $aboutHeroFieldId }}"
                                data-about-sync-image-field-id="{{ $aboutSectionHeaderFieldId }}"
                            >
                        </div>

                        <div class="form-group" data-about-card-panel-meta>
                            <label>Title</label>
                            <input type="text" name="about[overview][hero_title_default]" maxlength="255" value="{{ $overviewEditor['hero_title_default'] ?? '' }}">
                        </div>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Hero') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="intro" hidden>
                @php
                    $aboutStoryImageInputId = $idPrefix.'-about-story-image';
                    $aboutStoryImageFieldId = $idPrefix.'-about-story-image-field';
                    $aboutStoryImagePreview = \App\Support\AboutCmsContent::resolveImagePath($overviewEditor['story_image'] ?? null, 'assets/static_img/pupillar.jpeg');
                @endphp
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-about-intro-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="intro">
                    <input type="hidden" name="about_intro_version" value="0" data-about-intro-version>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $aboutStoryImageFieldId }}" name="about[overview][story_image]" value="{{ $overviewEditor['story_image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Story Image</label>
                        <div class="about-cms-image-dropzone-shell">
                            <div class="about-cms-image-dropzone" data-about-dropzone-for="{{ $aboutStoryImageInputId }}" role="button" tabindex="0" aria-label="Upload story image">
                                <span class="about-cms-image-dropzone-preview-column">
                                    <span class="about-cms-image-dropzone-media">
                                        <img
                                            src="{{ $aboutStoryImagePreview }}"
                                            alt="About story image preview"
                                            class="about-cms-image-dropzone-preview"
                                            data-about-preview-for="{{ $aboutStoryImageInputId }}"
                                            data-about-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                        >
                                        <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $aboutStoryImageInputId }}" aria-label="Edit image" title="Edit image">
                                            <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                        </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $aboutStoryImageInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="about-cms-image-dropzone-label">Story Image</span>
                                </span>
                                <span class="about-cms-image-dropzone-upload">
                                    <span class="about-cms-image-dropzone-icon">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="about-cms-image-dropzone-upload-copy">This image appears on the right side of the Campus Story section.</span>
                                    <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $aboutStoryImageInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input
                            id="{{ $aboutStoryImageInputId }}"
                            class="about-cms-image-dropzone-input"
                            type="file"
                            name="about[overview][story_image_file]"
                            accept="image/*"
                            data-about-image-field-id="{{ $aboutStoryImageFieldId }}"
                        >
                    </div>

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Story Tag</label>
                            <input type="text" name="about[overview][story_tag]" maxlength="255" value="{{ $overviewEditor['story_tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Story Title</label>
                            <input type="text" name="about[overview][story_title]" maxlength="255" value="{{ $overviewEditor['story_title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Story Description</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'about[overview][story_description]',
                            'value' => $overviewEditor['story_description'] ?? '',
                            'placeholder' => 'Write the campus story description...',
                        ])
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('About Intro') }}</button>
                    </div>
                </form>
            </section>
            <section class="about-cms-editor-panel" data-about-editor-panel="philosophy" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-about-philosophy-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="philosophy">
                    <input type="hidden" name="about_philosophy_version" value="0" data-about-philosophy-version>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif


                    <div class="form-group">
                        <label>Philosophy Content</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'about[overview][philosophy_description]',
                            'value' => $overviewEditor['philosophy_description'] ?? '',
                            'placeholder' => 'Write the campus philosophy content...',
                        ])
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Philosophy') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="contents" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-about-contents-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="contents">
                    <input type="hidden" name="about_contents_version" value="0" data-about-contents-version>
                    <input type="hidden" name="about_active_contents_slug" value="" data-about-active-contents-slug>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" name="about[overview][contents_tag]" value="{{ $overviewEditor['contents_tag'] ?? '' }}">
                    <input type="hidden" name="about[overview][contents_title]" value="{{ $overviewEditor['contents_title'] ?? '' }}">

                    <div class="about-cms-card-stack">
                        @foreach($aboutSections as $slug => $section)
                            @php
                                $sectionImagePreview = \App\Support\AboutCmsContent::resolveImagePath($section['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                $sectionImageInputId = $idPrefix.'-about-card-image-'.$slug;
                            @endphp
                            <article class="about-cms-card-editor" data-about-contents-editor data-about-contents-slug="{{ $slug }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4>{{ $section['label'] ?? $slug }}</h4>
                                    <span>{{ $slug }}</span>
                                </div>

                                <input type="hidden" name="about[sections][{{ $slug }}][visible_in_contents]" value="{{ $section['visible_in_contents'] ?? '1' }}" data-about-contents-visible>
                                <input type="hidden" name="about[sections][{{ $slug }}][image]" value="{{ $section['image'] ?? '' }}" data-about-image-field>

                                <div class="form-group">
                                    <label>Upload Card Image</label>
                                    <div class="about-cms-image-dropzone-shell">
                                        <div class="about-cms-image-dropzone" data-about-dropzone-for="{{ $sectionImageInputId }}" role="button" tabindex="0" aria-label="Upload card image">
                                            <span class="about-cms-image-dropzone-preview-column">
                                                <span class="about-cms-image-dropzone-media">
                                                    <img
                                                        src="{{ $sectionImagePreview }}"
                                                        alt="{{ $section['label'] ?? $slug }} preview"
                                                        class="about-cms-image-dropzone-preview"
                                                        data-about-preview-for="{{ $sectionImageInputId }}"
                                                        data-about-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                    >
                                                    <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $sectionImageInputId }}" aria-label="Edit image" title="Edit image">
                                                        <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $sectionImageInputId }}" aria-label="Delete image" title="Delete image">
                                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                                <span class="about-cms-image-dropzone-label">{{ $section['label'] ?? $slug }}</span>
                                            </span>
                                            <span class="about-cms-image-dropzone-upload">
                                                <span class="about-cms-image-dropzone-icon">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                </span>
                                                <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                <span class="about-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this card.</span>
                                                <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                                <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $sectionImageInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                            </span>
                                        </div>
                                    </div>
                                    <input
                                        id="{{ $sectionImageInputId }}"
                                        class="about-cms-image-dropzone-input"
                                        type="file"
                                        name="about[sections][{{ $slug }}][image_file]"
                                        accept="image/*"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Title</label>
                                    <input
                                        type="text"
                                        name="about[sections][{{ $slug }}][label]"
                                        maxlength="255"
                                        value="{{ $section['label'] ?? '' }}"
                                        readonly
                                        aria-readonly="true"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <div class="about-cms-textarea-field" data-about-char-limit="100">
                                        <textarea
                                            name="about[sections][{{ $slug }}][summary]"
                                            rows="4"
                                            maxlength="100"
                                            data-about-char-input
                                        >{{ $section['summary'] ?? '' }}</textarea>
                                        <div class="about-cms-char-counter" data-about-char-counter aria-live="polite">0/100</div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('About Contents') }}</button>
                    </div>
                </form>
            </section>

            @php
                $historyEditor = $aboutSections['history'] ?? [];
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="history" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-about-history-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="history">
                    <input type="hidden" name="about_history_version" value="0" data-about-history-version>
                    <input type="hidden" name="about_active_history_index" value="" data-about-active-history-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid" data-about-card-panel-meta>
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][history][label]" maxlength="255" value="{{ $historyEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][history][summary]" rows="4">{{ $historyEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="about-cms-form-grid" data-about-card-panel-meta>
                        <div class="form-group">
                            <label>Timeline Kicker</label>
                            <input type="text" name="about[sections][history][page_kicker]" maxlength="255" value="{{ $historyEditor['page_kicker'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Timeline Heading</label>
                            <input type="text" name="about[sections][history][page_title]" maxlength="255" value="{{ $historyEditor['page_title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="about-cms-card-stack">
                        <?php foreach (($historyEditor['timeline'] ?? []) as $index => $milestone): ?>
                            @php
                                $periodText = (string) ($milestone['period'] ?? '');
                                $periodParts = preg_split('/\s*(?:-|–|—|to)\s*/i', $periodText, 2) ?: [];
                                $periodStartMonth = $historyMonthValue((string) ($periodParts[0] ?? $periodText));
                                $periodEndMonth = $historyMonthValue((string) ($periodParts[1] ?? ''));
                                $historyMilestoneNumber = ((int) $index) + 1;
                            @endphp
                            <article
                                class="about-cms-card-editor"
                                data-about-history-editor
                                data-about-history-index="{{ $index }}"
                            >
                                <input type="hidden" name="about[sections][history][timeline][{{ $index }}][visible]" value="{{ $milestone['visible'] ?? '1' }}" data-about-history-visible>

                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4>Milestone {{ $historyMilestoneNumber }}</h4>
                                    <span>{{ $milestone['period'] ?? '' }}</span>
                                </div>

                                <div class="about-cms-form-grid about-cms-history-meta-grid">
                                    <div class="form-group" data-about-history-date-group>
                                        <label>Date <span>(Month Year)</span></label>
                                        <input type="hidden" name="about[sections][history][timeline][{{ $index }}][period]" value="{{ $periodText }}" data-about-history-period>
                                        <div class="about-cms-history-date-fields">
                                            <label>
                                                <span>Start</span>
                                                <input type="month" value="{{ $periodStartMonth }}" data-about-history-date-start>
                                            </label>
                                            <label>
                                                <span>End</span>
                                                <input type="month" value="{{ $periodEndMonth }}" data-about-history-date-end>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <div class="about-cms-history-title-wrap">
                                            <label>
                                                <span>Event / Milestone</span>
                                                <input type="text" name="about[sections][history][timeline][{{ $index }}][title]" maxlength="255" value="{{ $milestone['title'] ?? '' }}">
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    @php
                                        $historyBodyEditorName = 'about[sections][history][timeline]['.$index.'][body_text]';
                                        $historyBodyEditorValue = implode("\n\n", $milestone['body'] ?? []);
                                    @endphp
                                    @include('partials.rich_text_editor', [
                                        'name' => $historyBodyEditorName,
                                        'value' => $historyBodyEditorValue,
                                        'placeholder' => 'Write the milestone description...',
                                    ])
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('History') }}</button>
                    </div>
                </form>
            </section>

            @php
                $visionEditor = $aboutSections['vision-and-mission'] ?? [];
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="vision-mission-header" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="vision-mission-header">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Page Kicker</label>
                            <input type="text" name="about[sections][vision-and-mission][page_kicker]" maxlength="255" value="{{ $visionEditor['page_kicker'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Page Heading</label>
                            <input type="text" name="about[sections][vision-and-mission][page_title]" maxlength="255" value="{{ $visionEditor['page_title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Vision and Mission Header') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="vmgo-download" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="vision-and-mission">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Download Button Title</label>
                            <input type="text" name="about[sections][vision-and-mission][download_title]" maxlength="255" value="{{ $visionEditor['download_title'] ?? 'Download VMGO' }}">
                        </div>
                        <div class="form-group">
                            <label>Download Link</label>
                            <div class="about-link-row">
                                <input type="url" name="about[sections][vision-and-mission][download_link]" value="{{ $visionEditor['download_link'] ?? '' }}" placeholder="https://...">
                                <button type="button" class="about-link-paste" onclick="navigator.clipboard.readText().then(t => this.previousElementSibling.value = t).catch(err => alert('Please allow clipboard access to paste.'))" title="Paste URL">
                                    <i class="fas fa-paste"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Download VMGO Button') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="vision-statement" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="vision-statement">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Vision Statement</label>
                        <textarea name="about[sections][vision-and-mission][vision]" rows="5">{{ $visionEditor['vision'] ?? '' }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Vision Statement') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="mission-statement" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="mission-statement">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Mission Statement</label>
                        <textarea name="about[sections][vision-and-mission][mission]" rows="4">{{ $visionEditor['mission'] ?? '' }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Mission Statement') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="strategic-goals" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-about-strategic-goals-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="strategic-goals">
                    <input type="hidden" name="about_strategic_goals_version" value="0" data-about-strategic-goals-version>
                    <input type="hidden" name="about_active_strategic_goal_index" value="" data-about-active-strategic-goal-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-card-stack" data-about-strategic-goals-groups>
                        @foreach($visionEditor['strategic_goals'] ?? [] as $groupIndex => $goalGroup)
                            <article class="about-cms-card-editor" data-about-strategic-goal-group data-about-strategic-goal-editor data-about-strategic-goal-index="{{ $groupIndex }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4 data-about-strategic-group-heading>{{ $goalGroup['pillar'] ?? 'Pillar' }}</h4>
                                    <span>{{ $goalGroup['title'] ?? '' }}</span>
                                </div>

                                <div class="about-cms-form-grid about-cms-strategic-pillar-grid">
                                    <div class="form-group">
                                        <label>Pillar Label</label>
                                        <input type="text" data-about-strategic-group-pillar maxlength="255" value="{{ $goalGroup['pillar'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Pillar Title</label>
                                        <input type="text" data-about-strategic-group-title maxlength="255" value="{{ $goalGroup['title'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="about-cms-inline-actions">
                                    <button type="button" class="btn btn-outline-secondary" data-about-add-sg>+ Add new SG</button>
                                </div>

                                <div class="about-cms-goal-stack" data-about-strategic-group-goals>
                                @foreach($goalGroup['goals'] ?? [] as $goalIndex => $goal)
                                    <div class="form-group about-cms-goal-item" data-about-strategic-goal-item>
                                        <label data-about-strategic-goal-label>Goal {{ $goal['number'] ?? ($goalIndex + 1) }}</label>
                                        <div class="about-cms-goal-row">
                                            <input type="text" data-about-strategic-goal-text maxlength="4000" value="{{ $goal['text'] ?? '' }}">
                                            <input type="hidden" data-about-strategic-goal-number value="{{ $goal['number'] ?? ($goalIndex + 1) }}">
                                            <button type="button" class="btn btn-outline-danger" data-about-delete-sg>Delete</button>
                                        </div>
                                    </div>
                                @endforeach
                                </div>

                            </article>
                        @endforeach
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Strategic Goals') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="core-values" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="core-values">
                    <input type="hidden" name="about_core_values_version" value="0" data-about-core-values-version>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Core Values Heading</label>
                        <input type="text" name="about[sections][vision-and-mission][core_values_heading]" maxlength="255" value="{{ $visionEditor['core_values_heading'] ?? 'INSPIRED values that shape the character of the PUP community.' }}">
                    </div>

                    <div class="about-cms-card-stack">
                        @foreach($visionEditor['core_values'] ?? [] as $index => $coreValue)
                            <article class="about-cms-card-editor">
                                <div class="about-cms-form-grid about-cms-official-meta-grid">
                                    <div class="form-group">
                                        <label>Core Value Letter</label>
                                        <input type="text" name="about[sections][vision-and-mission][core_values][{{ $index }}][letter]" maxlength="10" value="{{ $coreValue['letter'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Core Value Title</label>
                                        <input type="text" name="about[sections][vision-and-mission][core_values][{{ $index }}][title]" maxlength="255" value="{{ $coreValue['title'] ?? '' }}">
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Core Values') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="pup-quality-policy" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="vision-and-mission">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    @php
                        $qualityPolicyImageInputId = $idPrefix.'-quality-policy-image';
                        $qualityPolicyImageFieldId = $idPrefix.'-quality-policy-image-field';
                        $emptyPlaceholderSrc = asset('assets/static_img/pupillar.jpeg');
                        $hasQualityImage = trim($visionEditor['quality_policy_image'] ?? '') !== '';
                        $qualityPolicyImagePreview = \App\Support\AboutCmsContent::resolveImagePath($visionEditor['quality_policy_image'] ?? null, 'assets/static_img/pupillar.jpeg');
                    @endphp
                    <input type="hidden" id="{{ $qualityPolicyImageFieldId }}" name="about[sections][vision-and-mission][quality_policy_image]" value="{{ $visionEditor['quality_policy_image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Quality Policy Image</label>
                        <div class="about-cms-image-dropzone-shell">
                            <div class="about-cms-image-dropzone" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);" data-about-dropzone-for="{{ $qualityPolicyImageInputId }}" role="button" tabindex="0" aria-label="Upload quality policy image">
                                <span class="about-cms-image-dropzone-preview-column">
                                    <span class="about-cms-image-dropzone-media">
                                        <img
                                            src="{{ $qualityPolicyImagePreview }}"
                                            alt="Quality Policy image preview"
                                            class="about-cms-image-dropzone-preview {{ !$hasQualityImage ? 'about-cms-image-dropzone-preview--profile-placeholder' : '' }}"
                                            data-about-preview-for="{{ $qualityPolicyImageInputId }}"
                                            data-about-default-src="{{ $emptyPlaceholderSrc }}"
                                        >
                                        <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $qualityPolicyImageInputId }}" aria-label="Edit image" title="Edit image">
                                            <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $qualityPolicyImageInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="about-cms-image-dropzone-label">Quality Policy Image</span>
                                </span>
                                <span class="about-cms-image-dropzone-upload">
                                    <span class="about-cms-image-dropzone-icon">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $qualityPolicyImageInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input
                            id="{{ $qualityPolicyImageInputId }}"
                            class="about-cms-image-dropzone-input"
                            type="file"
                            name="about[sections][vision-and-mission][quality_policy_image_file]"
                            accept="image/*"
                            data-about-image-field-id="{{ $qualityPolicyImageFieldId }}"
                        >
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('PUP Quality Policy') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="vision-and-mission" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="vision-and-mission">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][vision-and-mission][label]" maxlength="255" value="{{ $visionEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][vision-and-mission][summary]" rows="4">{{ $visionEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Page Kicker</label>
                            <input type="text" name="about[sections][vision-and-mission][page_kicker]" maxlength="255" value="{{ $visionEditor['page_kicker'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Page Heading</label>
                            <input type="text" name="about[sections][vision-and-mission][page_title]" maxlength="255" value="{{ $visionEditor['page_title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Download Button Title</label>
                            <input type="text" name="about[sections][vision-and-mission][download_title]" maxlength="255" value="{{ $visionEditor['download_title'] ?? 'Download VMGO' }}">
                        </div>
                        <div class="form-group">
                            <label>Download Link</label>
                            <div class="about-cms-icon-input" style="position: relative;">
                                <input type="url" name="about[sections][vision-and-mission][download_link]" value="{{ $visionEditor['download_link'] ?? '' }}" placeholder="https://..." style="padding-right: 30px;">
                                <button type="button" onclick="navigator.clipboard.readText().then(t => this.previousElementSibling.value = t).catch(err => console.error('Failed to paste', err))" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;" title="Paste from clipboard">
                                    <i class="fas fa-paste"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Vision Statement</label>
                        <textarea name="about[sections][vision-and-mission][vision]" rows="3">{{ $visionEditor['vision'] ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Mission Statement</label>
                        <textarea name="about[sections][vision-and-mission][mission]" rows="3">{{ $visionEditor['mission'] ?? '' }}</textarea>
                    </div>

                    <div class="about-cms-card-stack">
                        @foreach($visionEditor['strategic_goals'] ?? [] as $groupIndex => $goalGroup)
                            <article class="about-cms-card-editor">
                                <div class="about-cms-card-editor-head">
                                    <h4>{{ $goalGroup['pillar'] ?? 'Pillar' }}</h4>
                                    <span>{{ $goalGroup['title'] ?? '' }}</span>
                                </div>

                                <div class="form-group">
                                    <label>Pillar Title</label>
                                    <input type="text" name="about[sections][vision-and-mission][strategic_goals][{{ $groupIndex }}][title]" maxlength="255" value="{{ $goalGroup['title'] ?? '' }}">
                                </div>

                                @foreach($goalGroup['goals'] ?? [] as $goalIndex => $goal)
                                    <div class="form-group">
                                        <label>Goal {{ $goal['number'] ?? ($goalIndex + 1) }}</label>
                                        <input type="text" name="about[sections][vision-and-mission][strategic_goals][{{ $groupIndex }}][goals][{{ $goalIndex }}][text]" maxlength="4000" value="{{ $goal['text'] ?? '' }}">
                                        <input type="hidden" name="about[sections][vision-and-mission][strategic_goals][{{ $groupIndex }}][goals][{{ $goalIndex }}][number]" value="{{ $goal['number'] ?? ($goalIndex + 1) }}">
                                        <input type="hidden" name="about[sections][vision-and-mission][strategic_goals][{{ $groupIndex }}][pillar]" value="{{ $goalGroup['pillar'] ?? '' }}">
                                    </div>
                                @endforeach
                            </article>
                        @endforeach
                    </div>

                    <div class="about-cms-card-stack">
                        @foreach($visionEditor['core_values'] ?? [] as $index => $coreValue)
                            <article class="about-cms-card-editor">
                                <div class="about-cms-form-grid">
                                    <div class="form-group">
                                        <label>Core Value Letter</label>
                                        <input type="text" name="about[sections][vision-and-mission][core_values][{{ $index }}][letter]" maxlength="10" value="{{ $coreValue['letter'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Core Value Title</label>
                                        <input type="text" name="about[sections][vision-and-mission][core_values][{{ $index }}][title]" maxlength="255" value="{{ $coreValue['title'] ?? '' }}">
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Vision & Mission') }}</button>
                    </div>
                </form>
            </section>

            @php
                $logoEditor = $aboutSections['logo-and-symbols'] ?? [];
                $logoSeals = is_array($logoEditor['seals'] ?? null) ? array_values($logoEditor['seals']) : [];
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="logo-and-symbols" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-about-seals-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="logo-and-symbols">
                    <input type="hidden" name="about_seals_version" value="0" data-about-seals-version>
                    <input type="hidden" name="about_active_seal_index" value="" data-about-active-seal-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <article class="about-cms-card-editor" data-about-card-panel-meta>
                        <div class="about-cms-card-editor-head">
                            <h4>Section Header</h4>
                            <span>Shared details</span>
                        </div>
                        <div class="about-cms-form-grid">
                            <div class="form-group">
                                <label>Section Label</label>
                                <input type="text" name="about[sections][logo-and-symbols][label]" maxlength="255" value="{{ $logoEditor['label'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Section Summary</label>
                                <textarea name="about[sections][logo-and-symbols][summary]" rows="4">{{ $logoEditor['summary'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Lead Paragraph</label>
                            @include('partials.rich_text_editor', [
                                'name' => 'about[sections][logo-and-symbols][lead]',
                                'value' => $logoEditor['lead'] ?? '',
                                'placeholder' => 'Write the logo and symbols introduction...',
                            ])
                        </div>
                    </article>


                    <div class="about-cms-card-stack" data-about-seals-list>
                        @foreach($logoSeals as $index => $seal)
                            @php
                                $sealInputId = $idPrefix.'-about-seal-image-file-'.$index;
                                $sealImageFieldId = $idPrefix.'-about-seal-image-'.$index;
                                $sealImageValue = (string) ($seal['image'] ?? '');
                                $sealFallbackImage = trim((string) ($seal['image'] ?? '')) !== ''
                                    ? (string) $seal['image']
                                    : '/assets/static_img/logo.png';
                                $sealImagePreview = \App\Support\AboutCmsContent::resolveImagePath($sealImageValue !== '' ? $sealImageValue : null, ltrim($sealFallbackImage, '/'));
                                $sealLinks = is_array($seal['links'] ?? null) ? array_values($seal['links']) : [];
                                $sealHighlights = is_array($seal['highlights'] ?? null) ? $seal['highlights'] : [];
                            @endphp
                            <article class="about-cms-card-editor" data-about-seal-editor data-about-seal-index="{{ $index }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4 data-about-seal-heading>{{ $seal['label'] ?? ('Seal ' . $loop->iteration) }}</h4>
                                    <span data-about-seal-meta>{{ $seal['tag'] ?? ('Seal ' . $loop->iteration) }}</span>
                                </div>
                                <input type="hidden" name="about[sections][logo-and-symbols][seals][{{ $index }}][id]" value="{{ $seal['id'] ?? '' }}" data-about-seal-id>
                                <input type="hidden" id="{{ $sealImageFieldId }}" name="about[sections][logo-and-symbols][seals][{{ $index }}][image]" value="{{ $sealImageValue }}" data-about-image-field data-about-seal-image>

                                <div class="form-group">
                                    <label>Upload Seal Image</label>
                                    <div class="about-cms-image-dropzone-shell">
                                        <input
                                            type="file"
                                            id="{{ $sealInputId }}"
                                            name="about[sections][logo-and-symbols][seals][{{ $index }}][image_file]"
                                            class="about-cms-image-dropzone-input"
                                            accept="image/*"
                                            data-about-image-field-id="{{ $sealImageFieldId }}"
                                        >
                                        <div class="about-cms-image-dropzone about-cms-image-dropzone--square" data-about-dropzone-for="{{ $sealInputId }}" tabindex="0" role="button" aria-label="Upload seal image">
                                            <span class="about-cms-image-dropzone-preview-column">
                                                <span class="about-cms-image-dropzone-media">
                                                    <img
                                                        src="{{ $sealImagePreview }}"
                                                        alt="{{ $seal['label'] ?? 'Seal' }} preview"
                                                        class="about-cms-image-dropzone-preview{{ $sealImageValue === '' ? ' about-cms-image-dropzone-preview--profile-placeholder' : '' }}"
                                                        data-about-preview-for="{{ $sealInputId }}"
                                                        data-about-default-src="{{ \App\Support\AboutCmsContent::resolveImagePath($sealFallbackImage, 'assets/static_img/logo.png') }}"
                                                    >
                                                    <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $sealInputId }}" aria-label="Edit image" title="Edit image" {{ $sealImageValue === '' ? 'hidden' : '' }}>
                                                        <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $sealInputId }}" aria-label="Delete image" title="Delete image" {{ $sealImageValue === '' ? 'hidden' : '' }}>
                                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                            </span>
                                            <span class="about-cms-image-dropzone-upload">
                                                <span class="about-cms-image-dropzone-icon">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                </span>
                                                <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                <span class="about-cms-image-dropzone-upload-copy">Preview updates instantly while you edit this seal.</span>
                                                <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                                <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $sealInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="about-cms-form-grid">
                                    <div class="form-group">
                                        <label>Seal Title</label>
                                        <input type="text" name="about[sections][logo-and-symbols][seals][{{ $index }}][label]" maxlength="255" value="{{ $seal['label'] ?? '' }}" data-about-seal-label>
                                    </div>
                                    <div class="form-group">
                                        <label>Seal Tag</label>
                                        <input type="text" name="about[sections][logo-and-symbols][seals][{{ $index }}][tag]" maxlength="120" value="{{ $seal['tag'] ?? '' }}" data-about-seal-tag>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Highlights</label>
                                    <textarea name="about[sections][logo-and-symbols][seals][{{ $index }}][highlights_text]" rows="5" data-about-seal-highlights>{{ implode("\n", array_map(static fn ($item) => trim((string) $item), $sealHighlights)) }}</textarea>
                                </div>

                                <article class="about-cms-card-editor about-cms-card-editor--sub">
                                    <div class="form-group" data-about-seal-info-desc>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <label style="margin-bottom: 0;">Informations about the Seal Description</label>
                                            <label style="font-weight: normal; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="hidden" name="about[sections][logo-and-symbols][seals][{{ $index }}][information][visible]" value="0">
                                                <input type="checkbox" name="about[sections][logo-and-symbols][seals][{{ $index }}][information][visible]" value="1" {{ (data_get($seal, 'information.visible', '1') == '1') ? 'checked' : '' }}>
                                                Show in public view
                                            </label>
                                        </div>
                                        @include('partials.rich_text_editor', [
                                            'name' => 'about[sections][logo-and-symbols][seals]['.$index.'][information][description]',
                                            'value' => data_get($seal, 'information.description', ''),
                                            'placeholder' => 'Write information about this seal...',
                                        ])
                                    </div>
                                </article>

                                <article class="about-cms-card-editor about-cms-card-editor--sub">
                                    <div class="form-group" data-about-seal-reports-desc>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <label style="margin-bottom: 0;">Reports and Records Description</label>
                                            <label style="font-weight: normal; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="hidden" name="about[sections][logo-and-symbols][seals][{{ $index }}][reports][visible]" value="0">
                                                <input type="checkbox" name="about[sections][logo-and-symbols][seals][{{ $index }}][reports][visible]" value="1" {{ (data_get($seal, 'reports.visible', '1') == '1') ? 'checked' : '' }}>
                                                Show in public view
                                            </label>
                                        </div>
                                        @include('partials.rich_text_editor', [
                                            'name' => 'about[sections][logo-and-symbols][seals]['.$index.'][reports][description]',
                                            'value' => data_get($seal, 'reports.description', ''),
                                            'placeholder' => 'Write reports and records details...',
                                        ])
                                    </div>
                                </article>

                                <article class="about-cms-card-editor about-cms-card-editor--sub">
                                    <div class="about-cms-card-editor-head">
                                        <h4>Links</h4>
                                        <span>Add or remove multiple links</span>
                                    </div>
                                    <div class="about-cms-link-stack" data-about-seal-links-list>
                                        @foreach($sealLinks as $linkIndex => $link)
                                            <div class="about-cms-link-row" data-about-seal-link-item>
                                                <input type="text" name="about[sections][logo-and-symbols][seals][{{ $index }}][links][{{ $linkIndex }}][label]" maxlength="255" value="{{ $link['label'] ?? '' }}" placeholder="Link label" data-about-seal-link-label>
                                                <input type="text" name="about[sections][logo-and-symbols][seals][{{ $index }}][links][{{ $linkIndex }}][url]" maxlength="2048" value="{{ $link['url'] ?? '' }}" placeholder="https://..." data-about-seal-link-url>
                                                <button type="button" class="btn btn-outline-danger" data-about-seal-link-delete>Delete</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="about-cms-inline-actions">
                                        <button type="button" class="btn btn-outline-secondary" data-about-seal-link-add>+ Add Link</button>
                                    </div>
                                </article>
                            </article>
                        @endforeach
                    </div>

                    <template data-about-seal-template>
                        <article class="about-cms-card-editor" data-about-seal-editor data-about-seal-index="__INDEX__">
                            <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                <h4 data-about-seal-heading>New Seal</h4>
                                <span data-about-seal-meta>Seal __NUMBER__</span>
                            </div>
                            <input type="hidden" name="about[sections][logo-and-symbols][seals][__INDEX__][id]" value="" data-about-seal-id>
                            <input type="hidden" id="{{ $idPrefix }}-about-seal-image-__INDEX__" name="about[sections][logo-and-symbols][seals][__INDEX__][image]" value="" data-about-image-field data-about-seal-image>

                            <div class="form-group">
                                <label>Upload Seal Image</label>
                                <div class="about-cms-image-dropzone-shell">
                                    <input
                                        type="file"
                                        id="{{ $idPrefix }}-about-seal-image-file-__INDEX__"
                                        name="about[sections][logo-and-symbols][seals][__INDEX__][image_file]"
                                        class="about-cms-image-dropzone-input"
                                        accept="image/*"
                                        data-about-image-field-id="{{ $idPrefix }}-about-seal-image-__INDEX__"
                                    >
                                    <div class="about-cms-image-dropzone" data-about-dropzone-for="{{ $idPrefix }}-about-seal-image-file-__INDEX__" tabindex="0" role="button" aria-label="Upload seal image">
                                        <span class="about-cms-image-dropzone-preview-column">
                                            <span class="about-cms-image-dropzone-media">
                                                <img
                                                    src="{{ asset('assets/static_img/logo.png') }}"
                                                    alt="Seal preview"
                                                    class="about-cms-image-dropzone-preview about-cms-image-dropzone-preview--profile-placeholder"
                                                    data-about-preview-for="{{ $idPrefix }}-about-seal-image-file-__INDEX__"
                                                    data-about-default-src="{{ asset('assets/static_img/logo.png') }}"
                                                >
                                                <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $idPrefix }}-about-seal-image-file-__INDEX__" aria-label="Edit image" title="Edit image" hidden>
                                                    <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $idPrefix }}-about-seal-image-file-__INDEX__" aria-label="Delete image" title="Delete image" hidden>
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                        </span>
                                        <span class="about-cms-image-dropzone-upload">
                                            <span class="about-cms-image-dropzone-icon">
                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                            <span class="about-cms-image-dropzone-upload-copy">Preview updates instantly while you edit this seal.</span>
                                            <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                            <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $idPrefix }}-about-seal-image-file-__INDEX__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="about-cms-form-grid">
                                <div class="form-group">
                                    <label>Seal Title</label>
                                    <input type="text" name="about[sections][logo-and-symbols][seals][__INDEX__][label]" maxlength="255" value="" data-about-seal-label>
                                </div>
                                <div class="form-group">
                                    <label>Seal Tag</label>
                                    <input type="text" name="about[sections][logo-and-symbols][seals][__INDEX__][tag]" maxlength="120" value="" data-about-seal-tag>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Highlights</label>
                                <textarea name="about[sections][logo-and-symbols][seals][__INDEX__][highlights_text]" rows="5" data-about-seal-highlights></textarea>
                            </div>

                            <article class="about-cms-card-editor about-cms-card-editor--sub">
                                <div class="form-group" data-about-seal-info-desc>
                                    <label>Informations about the Seal Description</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => '',
                                        'value' => '',
                                        'placeholder' => 'Write information about this seal...',
                                    ])
                                </div>
                            </article>

                            <article class="about-cms-card-editor about-cms-card-editor--sub">
                                <div class="form-group" data-about-seal-reports-desc>
                                    <label>Reports and Records Description</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => '',
                                        'value' => '',
                                        'placeholder' => 'Write reports and records details...',
                                    ])
                                </div>
                            </article>

                            <article class="about-cms-card-editor about-cms-card-editor--sub">
                                <div class="about-cms-card-editor-head">
                                    <h4>Links</h4>
                                    <span>Add or remove multiple links</span>
                                </div>
                                <div class="about-cms-link-stack" data-about-seal-links-list></div>
                                <div class="about-cms-inline-actions">
                                    <button type="button" class="btn btn-outline-secondary" data-about-seal-link-add>+ Add Link</button>
                                </div>
                            </article>
                        </article>
                    </template>

                    <template data-about-seal-link-template>
                        <div class="about-cms-link-row" data-about-seal-link-item>
                            <input type="text" maxlength="255" value="" placeholder="Link label" data-about-seal-link-label>
                            <input type="text" maxlength="2048" value="" placeholder="https://..." data-about-seal-link-url>
                            <button type="button" class="btn btn-outline-danger" data-about-seal-link-delete>Delete</button>
                        </div>
                    </template>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Logo & Symbols') }}</button>
                    </div>
                </form>
            </section>

            @php
                $citizensCharterEditor = $aboutSections['citizens-charter'] ?? [];
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="citizens-charter" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="citizens-charter">
                    <input type="hidden" name="about_services_version" value="0" data-about-services-version>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="about[sections][citizens-charter][label]" maxlength="255" value="{{ $citizensCharterEditor['label'] ?? '' }}">
                    </div>



                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="about[sections][citizens-charter][lead]" rows="3" maxlength="4000">{{ $citizensCharterEditor['lead'] ?? '' }}</textarea>
                    </div>





                    <div class="about-cms-card-stack" data-about-services-list>
                        @foreach($citizensCharterEditor['services'] ?? [] as $index => $service)
                            <article class="about-cms-card-editor" data-about-service-editor data-about-service-index="{{ $index }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4 data-about-service-heading>Office {{ $loop->iteration }}</h4>
                                </div>
                                <div class="form-group">
                                    <label>Department (Title)</label>
                                    <input type="text" name="about[sections][citizens-charter][services][{{ $index }}][title]" maxlength="255" value="{{ $service['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="about[sections][citizens-charter][services][{{ $index }}][description]" rows="3" maxlength="5000">{{ $service['description'] ?? '' }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Link</label>
                                    <div class="about-link-row">
                                        <input type="text" name="about[sections][citizens-charter][services][{{ $index }}][link]" maxlength="2048" value="{{ $service['link'] ?? '' }}" placeholder="https://...">
                                        <button type="button" class="about-link-paste" onclick="navigator.clipboard.readText().then(t => this.previousElementSibling.value = t).catch(e => alert('Please allow clipboard access to paste.'))" title="Paste URL">
                                            <i class="fas fa-paste"></i>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-about-service-template>
                        <article class="about-cms-card-editor" data-about-service-editor data-about-service-index="__INDEX__">
                            <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                <h4 data-about-service-heading>Office __NUMBER__</h4>
                            </div>
                            <div class="form-group">
                                <label>Department (Title)</label>
                                <input type="text" name="about[sections][citizens-charter][services][__INDEX__][title]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="about[sections][citizens-charter][services][__INDEX__][description]" rows="3" maxlength="5000"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Link</label>
                                <div class="about-link-row">
                                    <input type="text" name="about[sections][citizens-charter][services][__INDEX__][link]" maxlength="2048" value="" placeholder="https://...">
                                    <button type="button" class="about-link-paste" onclick="navigator.clipboard.readText().then(t => this.previousElementSibling.value = t).catch(e => alert('Please allow clipboard access to paste.'))" title="Paste URL">
                                        <i class="fas fa-paste"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </template>

                    <div style="margin-top: 10px;" data-about-service-add-wrapper>
                        <button type="button" class="btn btn-outline-primary" data-about-service-add>+ Add Office</button>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Citizen\'s Charter') }}</button>
                    </div>
                </form>
            </section>

            @php
                $officialsEditor = $aboutSections['campus-officials'] ?? [];
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="campus-officials" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="campus-officials">
                    <input type="hidden" name="about_officials_version" value="0" data-about-officials-version>
                    <input type="hidden" name="about_active_official_index" value="" data-about-active-official-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    @php
                        $officialChartInputId = $idPrefix.'-about-official-chart-image-file';
                        $officialChartFieldId = $idPrefix.'-about-official-chart-image';
                        $officialChartValue = (string) ($officialsEditor['organizational_chart_image'] ?? '');
                        $officialChartPreview = \App\Support\AboutCmsContent::resolveImagePath(
                            $officialChartValue !== '' ? $officialChartValue : ($officialsEditor['image'] ?? null),
                            'assets/static_img/about_header_image.png'
                        );
                    @endphp

                    <div class="form-group">
                        <div class="about-cms-image-dropzone-shell">
                            <input type="hidden" id="{{ $officialChartFieldId }}" name="about[sections][campus-officials][organizational_chart_image]" value="{{ $officialChartValue }}" data-about-image-field>
                            <input
                                type="file"
                                id="{{ $officialChartInputId }}"
                                name="about[sections][campus-officials][organizational_chart_image_file]"
                                class="about-cms-image-dropzone-input"
                                accept="image/*"
                                data-about-image-field-id="{{ $officialChartFieldId }}"
                            >
                            <div class="about-cms-image-dropzone" data-about-dropzone-for="{{ $officialChartInputId }}" tabindex="0" role="button" aria-label="Upload organizational structure image">
                                <span class="about-cms-image-dropzone-preview-column">
                                    <span class="about-cms-image-dropzone-media">
                                        <img
                                            src="{{ $officialChartPreview }}"
                                            alt="Organizational structure preview"
                                            class="about-cms-image-dropzone-preview"
                                            data-about-preview-for="{{ $officialChartInputId }}"
                                            data-about-default-src="{{ asset('assets/static_img/about_header_image.png') }}"
                                        >
                                        <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $officialChartInputId }}" aria-label="Edit image" title="Edit image" {{ $officialChartValue === '' ? 'hidden' : '' }}>
                                            <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                        </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $officialChartInputId }}" aria-label="Delete image" title="Delete image" {{ $officialChartValue === '' ? 'hidden' : '' }}>
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                </span>
                                <span class="about-cms-image-dropzone-upload">
                                    <span class="about-cms-image-dropzone-icon">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="about-cms-image-dropzone-upload-copy">This image appears above the campus officials cards and opens in a zoom view on the public page.</span>
                                    <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $officialChartInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="about-cms-card-stack" data-about-officials-list>
                        @foreach($officialsEditor['official_groups'] ?? [] as $index => $officialGroup)
                            @php
                                $officialImageInputId = $idPrefix.'-about-official-image-file-'.$index;
                                $officialImageFieldId = $idPrefix.'-about-official-image-'.$index;
                                $officialImageValue = (string) ($officialGroup['image'] ?? '');
                                $officialImagePreview = \App\Support\AboutCmsContent::resolveImagePath($officialImageValue !== '' ? $officialImageValue : null, 'assets/static_img/temporary_profile.png');
                            @endphp
                            <article class="about-cms-card-editor" data-about-official-editor data-about-official-index="{{ $index }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4 data-about-official-heading>Official Card {{ $loop->iteration }}</h4>
                                    <span data-about-official-meta>{{ $officialGroup['name'] ?? ($officialGroup['title'] ?? 'Campus official') }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Upload Profile Image</label>
                                    <div class="about-cms-image-dropzone-shell">
                                        <input type="hidden" id="{{ $officialImageFieldId }}" name="about[sections][campus-officials][official_groups][{{ $index }}][image]" value="{{ $officialImageValue }}" data-about-image-field>
                                        <input
                                            type="file"
                                            id="{{ $officialImageInputId }}"
                                            name="about[sections][campus-officials][official_groups][{{ $index }}][image_file]"
                                            class="about-cms-image-dropzone-input"
                                            accept="image/*"
                                            data-about-image-field-id="{{ $officialImageFieldId }}"
                                        >
                                        <div class="about-cms-image-dropzone" data-about-dropzone-for="{{ $officialImageInputId }}" tabindex="0" role="button" aria-label="Upload official profile image">
                                            <span class="about-cms-image-dropzone-preview-column">
                                                <span class="about-cms-image-dropzone-media">
                                                    <img
                                                        src="{{ $officialImagePreview }}"
                                                        alt="{{ $officialGroup['title'] ?? 'Campus official' }} preview"
                                                        class="about-cms-image-dropzone-preview{{ $officialImageValue === '' ? ' about-cms-image-dropzone-preview--profile-placeholder' : '' }}"
                                                        data-about-preview-for="{{ $officialImageInputId }}"
                                                        data-about-default-src="{{ asset('assets/static_img/temporary_profile.png') }}"
                                                    >
                                                    <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $officialImageInputId }}" aria-label="Edit image" title="Edit image" {{ $officialImageValue === '' ? 'hidden' : '' }}>
                                                        <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $officialImageInputId }}" aria-label="Delete image" title="Delete image" {{ $officialImageValue === '' ? 'hidden' : '' }}>
                                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                            </span>
                                            <span class="about-cms-image-dropzone-upload">
                                                <span class="about-cms-image-dropzone-icon">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                </span>
                                                <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                <span class="about-cms-image-dropzone-upload-copy">Preview updates instantly while you edit this official card.</span>
                                                <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                                <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $officialImageInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="about-cms-official-fields">
                                    <div class="about-cms-official-meta-row">
                                        <div class="about-cms-official-meta-col">
                                            <label>Role</label>
                                            <input
                                                type="text"
                                                name="about[sections][campus-officials][official_groups][{{ $index }}][title]"
                                                maxlength="255"
                                                value="{{ $officialGroup['title'] ?? '' }}"
                                            >
                                        </div>
                                        <div class="about-cms-official-meta-col">
                                            <label>Name</label>
                                            <input type="text" name="about[sections][campus-officials][official_groups][{{ $index }}][name]" maxlength="255" value="{{ $officialGroup['name'] ?? '' }}">
                                        </div>
                                        <div class="about-cms-official-meta-col">
                                            <label>Order</label>
                                            <input
                                                type="number"
                                                name="about[sections][campus-officials][official_groups][{{ $index }}][order]"
                                                min="1"
                                                step="1"
                                                value="{{ $officialGroup['order'] ?? ($loop->iteration) }}"
                                                data-about-official-order
                                            >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <div class="about-cms-textarea-field" data-about-char-limit="220">
                                            <textarea
                                                name="about[sections][campus-officials][official_groups][{{ $index }}][body]"
                                                rows="4"
                                                maxlength="220"
                                                data-about-char-input
                                            >{{ $officialGroup['body'] ?? '' }}</textarea>
                                            <div class="about-cms-char-counter" data-about-char-counter aria-live="polite">0/220</div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-about-official-template>
                        <article class="about-cms-card-editor" data-about-official-editor data-about-official-index="__INDEX__">
                            <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                <h4 data-about-official-heading>Official Card __NUMBER__</h4>
                                <span data-about-official-meta>Campus official</span>
                            </div>
                            <div class="form-group">
                                <label>Upload Profile Image</label>
                                <div class="about-cms-image-dropzone-shell">
                                    <input type="hidden" id="{{ $idPrefix }}-about-official-image-__INDEX__" name="about[sections][campus-officials][official_groups][__INDEX__][image]" value="" data-about-image-field>
                                    <input
                                        type="file"
                                        id="{{ $idPrefix }}-about-official-image-file-__INDEX__"
                                        name="about[sections][campus-officials][official_groups][__INDEX__][image_file]"
                                        class="about-cms-image-dropzone-input"
                                        accept="image/*"
                                        data-about-image-field-id="{{ $idPrefix }}-about-official-image-__INDEX__"
                                    >
                                    <div class="about-cms-image-dropzone" data-about-dropzone-for="{{ $idPrefix }}-about-official-image-file-__INDEX__" tabindex="0" role="button" aria-label="Upload official profile image">
                                        <span class="about-cms-image-dropzone-preview-column">
                                            <span class="about-cms-image-dropzone-media">
                                                <img
                                                    src="{{ asset('assets/static_img/temporary_profile.png') }}"
                                                    alt="Campus official preview"
                                                    class="about-cms-image-dropzone-preview about-cms-image-dropzone-preview--profile-placeholder"
                                                    data-about-preview-for="{{ $idPrefix }}-about-official-image-file-__INDEX__"
                                                    data-about-default-src="{{ asset('assets/static_img/temporary_profile.png') }}"
                                                >
                                                <button type="button" class="about-cms-image-dropzone-edit" data-about-edit-image-for="{{ $idPrefix }}-about-official-image-file-__INDEX__" aria-label="Edit image" title="Edit image" hidden>
                                                    <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                </button>
                                                    <button type="button" class="about-cms-image-dropzone-remove" data-about-clear-image-for="{{ $idPrefix }}-about-official-image-file-__INDEX__" aria-label="Delete image" title="Delete image" hidden>
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                        </span>
                                        <span class="about-cms-image-dropzone-upload">
                                            <span class="about-cms-image-dropzone-icon">
                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="about-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                            <span class="about-cms-image-dropzone-upload-copy">Preview updates instantly while you edit this official card.</span>
                                            <span class="about-cms-image-dropzone-upload-button">Select image</span>
                                            <span class="about-cms-image-dropzone-file" data-about-file-name-for="{{ $idPrefix }}-about-official-image-file-__INDEX__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="about-cms-official-fields">
                                <div class="about-cms-official-meta-row">
                                    <div class="about-cms-official-meta-col">
                                        <label>Role</label>
                                        <input
                                            type="text"
                                            name="about[sections][campus-officials][official_groups][__INDEX__][title]"
                                            maxlength="255"
                                            value=""
                                        >
                                    </div>
                                    <div class="about-cms-official-meta-col">
                                        <label>Name</label>
                                        <input type="text" name="about[sections][campus-officials][official_groups][__INDEX__][name]" maxlength="255" value="">
                                    </div>
                                    <div class="about-cms-official-meta-col">
                                        <label>Order</label>
                                        <input
                                            type="number"
                                            name="about[sections][campus-officials][official_groups][__INDEX__][order]"
                                            min="1"
                                            step="1"
                                            value="__NUMBER__"
                                            data-about-official-order
                                        >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <div class="about-cms-textarea-field" data-about-char-limit="220">
                                        <textarea
                                            name="about[sections][campus-officials][official_groups][__INDEX__][body]"
                                            rows="4"
                                            maxlength="220"
                                            data-about-char-input
                                        ></textarea>
                                        <div class="about-cms-char-counter" data-about-char-counter aria-live="polite">0/220</div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </template>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </section>

            @php
                $planEditor = $aboutSections['strategic-development-plan'] ?? [];
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="strategic-development-plan-header" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="strategic-development-plan">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <article class="about-cms-card-editor">
                        <div class="about-cms-card-editor-head">
                            <h4>Plan Overview</h4>
                            <span>Shared section content</span>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="about[sections][strategic-development-plan][label]" maxlength="255" value="{{ $planEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="hidden" name="about[sections][strategic-development-plan][summary]" value="">
                            @include('partials.rich_text_editor', [
                                'name' => 'about[sections][strategic-development-plan][lead]',
                                'value' => ($planEditor['lead'] ?? '') !== '' ? ($planEditor['lead'] ?? '') : ($planEditor['summary'] ?? ''),
                                'placeholder' => 'Write the strategic development plan description...',
                                'characterLimit' => 220,
                            ])
                        </div>
                    </article>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Strategic Development Plan Header') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="strategic-development-plan" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-about-plan-priorities-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="strategic-development-plan">
                    <input type="hidden" name="about_plan_priorities_version" value="0" data-about-plan-priorities-version>
                    <input type="hidden" name="about_active_plan_priority_index" value="" data-about-active-plan-priority-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-card-stack" data-about-plan-priorities-list>
                        @foreach($planEditor['development_priorities'] ?? [] as $index => $priority)
                            <article class="about-cms-card-editor about-cms-card-editor--plan-priority" data-about-plan-priority-editor data-about-plan-priority-index="{{ $index }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4 data-about-plan-priority-heading>{{ $priority['title'] ?? ('Priority Card ' . $loop->iteration) }}</h4>
                                    <span data-about-plan-priority-meta>Priority {{ $loop->iteration }}</span>
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" data-about-plan-priority-title maxlength="255" value="{{ $priority['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => 'about[sections][strategic-development-plan][development_priorities]['.$index.'][body]',
                                        'value' => $priority['body'] ?? '',
                                        'placeholder' => 'Write the priority description...',
                                        'characterLimit' => 220,
                                    ])
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-about-plan-priority-template>
                        <article class="about-cms-card-editor about-cms-card-editor--plan-priority" data-about-plan-priority-editor data-about-plan-priority-index="">
                            <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                <h4 data-about-plan-priority-heading>New Priority</h4>
                                <span data-about-plan-priority-meta>Priority</span>
                            </div>
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" data-about-plan-priority-title maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                @include('partials.rich_text_editor', [
                                    'name' => '',
                                    'value' => '',
                                    'placeholder' => 'Write the priority description...',
                                    'characterLimit' => 220,
                                ])
                            </div>
                        </article>
                    </template>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Strategic Development Plan') }}</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>



@include('partials.rich_text_editor_assets')

<style>
    .about-cms-workspace {
        --about-preview-width: 1520px;
        --about-preview-height: 1800px;
        --about-preview-vision-height-cap: 1800px;
        --about-preview-min-height: 0px;
        --about-preview-scale: 1;
        --about-preview-scaled-width: calc(var(--about-preview-width) * var(--about-preview-scale));
        --about-preview-scaled-height: calc(var(--about-preview-height) * var(--about-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .about-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .about-cms-preview-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 0 0 12px;
        border-bottom: 0;
    }

    .about-cms-preview-head > div:first-child {
        display: none;
    }

    .about-cms-eyebrow,
    .about-cms-side-kicker {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .about-cms-preview-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-content: flex-start;
    }

    .about-cms-preview-nav-btn {
        border: 1px solid #d7c5bd;
        background: #fff8f5;
        color: #5c0000;
        border-radius: 999px;
        padding: 8px 12px;
        cursor: pointer;
        font: inherit;
        font-size: 0.82rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .about-cms-preview-nav-btn:not(.is-active):hover {
        background: #fff8f5;
        border-color: #f0c85a;
        color: #f0c85a;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(240, 200, 90, 0.15);
    }

    .about-cms-preview-nav-btn.is-active:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(128, 0, 0, 0.25);
    }

    .about-cms-preview-nav-btn.is-active {
        background: #800000;
        border-color: #800000;
        color: #fff;
    }

    .about-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .about-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .about-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--about-preview-scaled-width);
        max-width: 100%;
        height: var(--about-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .about-cms-preview-frame {
        display: block;
        width: var(--about-preview-width);
        min-width: var(--about-preview-width);
        height: var(--about-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--about-preview-scale));
        transform-origin: top left;
    }

    .about-cms-modal[hidden] {
        display: none;
    }

    .about-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .about-cms-modal::-webkit-scrollbar,
    .about-cms-modal-dialog::-webkit-scrollbar,
    .about-cms-modal-panels::-webkit-scrollbar,
    .about-cms-modal *::-webkit-scrollbar {
        display: none;
    }

    .about-cms-modal * {
        scrollbar-width: none;
    }

    .about-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.6);
        backdrop-filter: blur(8px);
    }

    .about-cms-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(1320px, calc(100vw - 24px));
        margin: 0 auto;
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 24px;
        scrollbar-width: none;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow: 0 16px 34px rgba(92, 12, 6, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(127, 17, 19, 0.12);
    }

    .about-cms-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 46px;
        height: 46px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(180deg, #fff7f1 0%, #f7e8dc 100%);
        color: #5c0000;
        font-size: 1.6rem;
        cursor: pointer;
    }

    .about-cms-modal-header {
        flex: 0 0 auto;
        padding: 24px 30px 14px;
        border-bottom: 1px solid #f0e2d9;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 246, 238, 0.75) 100%);
    }

    .about-cms-modal-panels {
        flex: 1 1 auto;
        padding: 24px 30px 30px;
        max-width: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: none;
    }

    .about-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        align-items: start;
    }

    .about-cms-form-grid .form-group {
        display: grid;
        align-content: start;
        gap: 10px;
        min-width: 0;
    }

    .about-cms-form-grid .form-group > label {
        min-height: 22px;
        line-height: 1.2;
    }

    .about-cms-official-fields {
        display: grid;
        gap: 10px;
        width: 100%;
    }

    .about-cms-official-fields > .form-group > input,
    .about-cms-official-fields .about-cms-locked-field {
        min-height: 52px;
    }

    .about-cms-official-meta-row {
        display: grid;
        grid-template-columns: 1fr 1fr 160px;
        gap: 16px;
        align-items: start;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .about-cms-official-meta-col {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin: 0;
        padding: 0;
        min-width: 0;
    }

    .about-cms-official-meta-col > label {
        display: block;
        height: 22px;
        margin: 0;
        line-height: 1.2;
        color: #8a0000;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.01em;
    }

    .about-cms-official-meta-col > input {
        display: block;
        width: 100%;
        height: 52px;
        min-height: 52px;
        margin: 0;
        box-sizing: border-box;
        padding-top: 0;
        padding-bottom: 0;
        padding-left: 14px;
        padding-right: 14px;
        border: 1px solid #d8cbc4;
        border-radius: 8px;
        background: #fffdfc;
        color: #2f2320;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active .about-cms-official-meta-col + .about-cms-official-meta-col {
        margin-top: 0;
    }

    .about-cms-official-meta-col > input:hover,
    .about-cms-official-fields .about-cms-textarea-field textarea:hover {
        border-color: #c9b6ad;
    }

    .about-cms-official-meta-col > input:focus,
    .about-cms-official-fields .about-cms-textarea-field textarea:focus {
        outline: none;
        border-color: #8a0000;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.6),
            0 0 0 3px rgba(138, 0, 0, 0.12);
    }

    .about-cms-official-fields > .form-group > label {
        color: #8a0000;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.01em;
    }

    .about-cms-official-fields .about-cms-textarea-field textarea {
        display: block;
        width: 100%;
        min-height: 112px;
        margin: 0;
        padding: 12px 14px;
        border: 1px solid #d8cbc4;
        border-radius: 8px;
        background: #fffdfc;
        color: #2f2320;
        line-height: 1.55;
        resize: vertical;
        box-sizing: border-box;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
    }

    .about-cms-history-meta-grid {
        grid-template-columns: minmax(0, 1fr) minmax(240px, 0.85fr);
        align-items: start;
        column-gap: 18px;
        row-gap: 16px;
    }

    .about-cms-strategic-pillar-grid {
        align-items: start;
    }

    .about-cms-strategic-pillar-grid .form-group {
        display: grid;
        align-content: start;
        gap: 10px;
        min-width: 0;
    }

    .about-cms-strategic-pillar-grid label {
        min-height: 22px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .about-cms-history-meta-grid .form-group {
        display: grid;
        align-content: start;
        gap: 10px;
        min-width: 0;
    }

    .about-cms-history-meta-grid label {
        min-height: 22px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .about-cms-history-meta-grid label span {
        color: #a67a14;
        font-size: 0.82em;
        font-weight: 700;
    }

    .about-cms-history-meta-grid input {
        width: 100%;
        min-width: 0;
    }

    .about-cms-history-date-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        align-items: start;
        min-width: 0;
    }

    .about-cms-history-date-fields label,
    .about-cms-history-title-wrap label {
        display: grid;
        gap: 6px;
        min-height: 0;
        color: #8a0000;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .about-cms-history-date-fields input[type="month"] {
        min-height: 54px;
        padding-right: 12px;
        cursor: pointer;
    }

    .about-cms-history-meta-grid > .form-group:not([data-about-history-date-group]) {
        padding-top: 0;
    }

    .about-cms-history-meta-grid > .form-group:not([data-about-history-date-group]) input {
        min-height: 54px;
    }

    .about-cms-card-stack {
        display: grid;
        gap: 14px;
    }

    .about-cms-card-editor {
        padding: 16px;
        border: 1px solid #efe3dc;
        border-radius: 16px;
        background: #fff;
        box-sizing: border-box;
    }

    .about-cms-card-editor .form-group {
        margin-bottom: 12px;
    }

    .about-cms-card-editor[data-about-official-editor] {
        width: 100%;
    }

    .about-cms-card-editor[data-about-official-editor] {
        width: min(100%, 620px);
    }

    .about-cms-card-editor--plan-priority {
        width: min(100%, 720px);
        margin: 0 auto;
    }

    .about-cms-card-editor[data-about-seal-editor] {
        width: min(100%, 940px);
        margin: 0 auto;
    }

    .about-cms-card-editor--sub {
        margin-top: 14px;
        border-style: dashed;
        border-color: #e7d9cf;
        background: #fffdfa;
    }

    .about-cms-card-editor[data-about-contents-editor] {
        display: none;
    }

    .about-cms-card-editor[data-about-contents-editor].is-active {
        display: block;
    }

    .about-cms-card-editor.is-active {
        border-color: rgba(127, 17, 19, 0.38);
        box-shadow: 0 0 0 3px rgba(127, 17, 19, 0.08);
    }

    .about-cms-card-editor.is-disabled {
        opacity: 0.58;
    }

    .about-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }

    .about-cms-card-editor-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .about-cms-card-editor-head span {
        color: #8a7a73;
        font-size: 0.8rem;
    }

    .about-cms-inline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .about-cms-add-card-button {
        display: grid;
        place-items: center;
        gap: 10px;
        width: 100%;
        min-height: 176px;
        margin-top: 16px;
        border: 2px dashed rgba(127, 17, 19, 0.22);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255, 252, 249, 0.98) 0%, rgba(250, 243, 238, 0.96) 100%);
        color: #7f1113;
        cursor: pointer;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .about-cms-add-card-button:hover,
    .about-cms-add-card-button:focus-visible {
        border-color: rgba(127, 17, 19, 0.38);
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(79, 9, 12, 0.08);
    }

    .about-cms-add-card-button-plus {
        font-size: 4rem;
        line-height: 1;
        font-weight: 300;
    }

    .about-cms-add-card-button-label {
        font-size: 1.08rem;
        font-weight: 800;
    }

    .about-cms-goal-stack {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .about-cms-goal-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
    }

    .about-cms-link-stack {
        display: grid;
        gap: 12px;
        margin-top: 6px;
    }

    .about-cms-link-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) auto;
        gap: 10px;
        align-items: center;
        padding: 10px;
        border: 1px solid #e9d9cd;
        border-radius: 14px;
        background: linear-gradient(180deg, #fffdfb 0%, #fff6ee 100%);
        box-shadow: 0 8px 18px rgba(92, 12, 6, 0.05);
    }

    .about-cms-link-row input {
        width: 100%;
        min-width: 0;
        height: 46px;
        padding: 0 14px;
        border: 1px solid #d9c7bb;
        border-radius: 10px;
        background: #fff;
        color: #2e2422;
        box-sizing: border-box;
    }

    .about-cms-link-row input:focus {
        outline: none;
        border-color: #8a0000;
        box-shadow: 0 0 0 3px rgba(138, 0, 0, 0.12);
    }

    .about-cms-link-row [data-about-seal-link-delete] {
        height: 46px;
        padding: 0 16px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #8f1a1f 0%, #6d1115 100%);
        color: #fff7f4;
        font-size: 0.84rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        box-shadow: 0 10px 20px rgba(92, 10, 14, 0.22);
    }

    .about-cms-link-row [data-about-seal-link-delete]:hover,
    .about-cms-link-row [data-about-seal-link-delete]:focus-visible {
        transform: translateY(-1px);
        background: linear-gradient(135deg, #9d1f24 0%, #7a1318 100%);
    }

    .about-cms-inline-actions [data-about-seal-link-add] {
        height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        border: 1px solid rgba(127, 17, 19, 0.25);
        background: linear-gradient(180deg, #fff 0%, #fff1e5 100%);
        color: #6a0f13;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.01em;
    }

    .about-cms-inline-actions [data-about-seal-link-add]:hover,
    .about-cms-inline-actions [data-about-seal-link-add]:focus-visible {
        border-color: rgba(127, 17, 19, 0.42);
        background: linear-gradient(180deg, #fff9f2 0%, #ffe8d8 100%);
        transform: translateY(-1px);
    }

    .about-cms-inline-actions [data-about-seal-add-editor] {
        min-height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        border: 1px solid rgba(127, 17, 19, 0.25);
        background: linear-gradient(145deg, #fff 0%, #fff0e4 100%);
        color: #690d12;
        font-weight: 900;
        letter-spacing: 0.01em;
    }

    .about-cms-image-dropzone-shell {
        width: 100%;
        margin: 0 auto;
    }

    .about-cms-image-dropzone {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        gap: 16px;
        width: 100%;
        padding: 14px;
        border: 1px dashed #d4af37;
        border-radius: 24px;
        background: linear-gradient(180deg, #fffdf8 0%, #fff8ee 100%);
        cursor: pointer;
        align-items: stretch;
    }

    .about-cms-image-dropzone--square {
        grid-template-columns: 160px minmax(0, 1fr);
    }

    .about-cms-image-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .about-cms-image-dropzone-preview-column {
        display: flex;
        min-width: 0;
        min-height: 120px;
    }

    .about-cms-image-dropzone-media {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
    }

    .about-cms-image-dropzone-preview {
        width: 100%;
        height: 100%;
        min-height: 120px;
        object-fit: cover;
        border-radius: 18px;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
    }

    .about-cms-image-dropzone--square .about-cms-image-dropzone-preview {
        max-height: 160px;
        object-fit: contain;
    }

    .about-cms-image-dropzone-preview--profile-placeholder {
        object-fit: contain;
        padding: 22px;
        background:
            radial-gradient(circle at center, rgba(255, 255, 255, 0.98) 0%, rgba(255, 250, 245, 0.96) 55%, rgba(247, 232, 220, 0.92) 100%);
        box-shadow:
            0 0 30px rgba(255, 255, 255, 0.85),
            0 0 56px rgba(255, 244, 232, 0.65),
            inset 0 0 0 1px rgba(255, 255, 255, 0.88);
        filter: drop-shadow(0 0 22px rgba(255, 255, 255, 0.95));
    }

    .about-cms-image-dropzone-label {
        display: none;
        color: #7f1113;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
    }

    .about-cms-image-dropzone-upload {
        display: grid;
        justify-items: center;
        align-content: center;
        gap: 8px;
        min-width: 0;
        padding: 14px 16px;
        border-radius: 18px;
        background: radial-gradient(circle at top, rgba(151, 26, 33, 0.98), rgba(96, 12, 18, 0.98));
        color: #f8f4ef;
        text-align: center;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
        min-height: 100%;
    }

    .about-cms-image-dropzone-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        border-radius: 999px;
        background: rgba(73, 8, 13, 0.42);
        color: #f2f0ed;
        font-size: 1.4rem;
    }

    .about-cms-image-dropzone-upload-title {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .about-cms-image-dropzone-upload-copy {
        display: block;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .about-cms-image-dropzone-upload-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 16px;
        border-radius: 999px;
        background: #fff8f1;
        color: #1b1714;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .about-cms-image-dropzone-file {
        display: block;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.8rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .about-cms-image-dropzone-input {
        display: none;
    }

    
    

    

    

    


    .about-cms-image-dropzone-edit {
        position: absolute;
        top: 60px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 40px;
        height: 40px;
        padding: 0;
        border: 1px solid rgba(26, 115, 232, 0.14);
        border-radius: 999px;
        background: rgba(255, 253, 250, 0.94);
        color: #1a73e8;
        font: inherit;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(26, 115, 232, 0.12);
        backdrop-filter: blur(6px);
        z-index: 10;
        transition: opacity 0.15s, background-color 0.15s, color 0.15s;
    }

    

    .about-cms-image-dropzone-edit:hover {
        background: #1a73e8;
        color: #ffffff;
    }

    

    .about-cms-image-dropzone-edit[hidden] {
        display: none !important;
    }

.about-cms-image-dropzone-remove {
        position: absolute;
        top: 12px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
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

    

    .about-cms-image-dropzone-remove:hover {
        background: #7f1113;
        color: #fff8f1;
    }

    @media (max-width: 460px) {
        .about-cms-image-dropzone {
            grid-template-columns: 1fr;
        }

        .about-cms-image-dropzone-upload {
            min-height: 280px;
        }
    }

    
    @media (max-width: 640px) {
        .about-cms-image-dropzone-edit {
            top: 54px;
            right: 12px;
            padding: 0;
            height: 36px;
        }

        
    }

@media (max-width: 640px) {
        
    

    

    

    

.about-cms-image-dropzone-remove {
            top: 12px;
            right: 12px;
            padding: 0;
            height: 36px;
        }

        
    }

    .about-cms-image-dropzone-remove[hidden] {
        display: none;
    }

    .about-cms-upload-hint {
        display: block;
        margin-top: 6px;
        color: #8a7a73;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .about-cms-textarea-field {
        position: relative;
        display: block;
    }

    .about-cms-textarea-field textarea {
        padding-bottom: 24px;
    }

    .about-cms-char-counter {
        position: absolute;
        bottom: 12px;
        right: 12px;
        color: #8a7a73;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
        pointer-events: none;
    }

    .about-cms-char-counter.is-limit {
        color: #b91c1c;
    }

    .about-cms-locked-field {
        position: relative;
        display: block;
        width: 100%;
    }

    .about-cms-locked-field-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        color: #8a7a73;
        pointer-events: none;
        z-index: 1;
    }

    .about-cms-locked-field input[readonly] {
        display: block;
        width: 100%;
        min-height: 52px;
        margin: 0;
        padding: 0 14px 0 40px;
        border-radius: 12px;
        border: 1px solid #e6d7cf;
        background: linear-gradient(180deg, #faf5f2 0%, #f5eeea 100%);
        color: #6a5550;
        line-height: 1.35;
        box-sizing: border-box;
        cursor: not-allowed;
    }

    .about-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .about-cms-modal.is-card-focus .about-cms-modal-header {
        display: none;
    }

    .about-cms-modal.is-card-focus {
        align-items: flex-start !important;
    }

    .about-cms-modal.is-card-focus .about-cms-modal-dialog {
        width: min(640px, calc(100vw - 20px));
        max-width: min(640px, calc(100vw - 20px));
        border-radius: 32px;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: linear-gradient(145deg, #ffffff 0%, #fffbfa 100%);
        box-shadow: 0 40px 100px rgba(45, 8, 5, 0.12), inset 0 0 0 1px rgba(212, 175, 55, 0.12);
    }

    .about-cms-modal.is-official-card-focus .about-cms-modal-dialog {
        width: min(680px, calc(100vw - 20px));
        max-width: min(680px, calc(100vw - 20px));
    }

    .about-cms-modal.is-card-focus[data-about-active-panel="logo-and-symbols"] .about-cms-modal-dialog {
        width: min(1020px, calc(100vw - 20px));
        max-width: min(1020px, calc(100vw - 20px));
    }

    .about-cms-modal.is-chart-focus .about-cms-modal-dialog {
        width: min(1080px, calc(100vw - 20px));
        max-width: min(1080px, calc(100vw - 20px));
    }

    .about-cms-modal.is-card-focus .about-cms-modal-panels {
        display: block;
        padding: 18px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .about-cms-editor-panel.is-card-focus form {
        display: grid;
        gap: 16px;
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
        overflow: visible;
    }

    .about-cms-modal.is-official-card-focus .about-cms-editor-panel.is-card-focus form {
        max-width: 640px;
    }

    .about-cms-modal.is-official-card-focus .about-cms-editor-panel[data-about-editor-panel="campus-officials"] > form > .form-group:first-of-type {
        display: none;
    }

    .about-cms-editor-panel[data-about-editor-panel="logo-and-symbols"].is-card-focus form {
        max-width: 980px;
    }

    /* Service card focus */
    .about-cms-modal.is-service-card-focus .about-cms-editor-panel[data-about-editor-panel="citizens-charter"] > form > .form-group:nth-of-type(1),
    .about-cms-modal.is-service-card-focus .about-cms-editor-panel[data-about-editor-panel="citizens-charter"] > form > .form-group:nth-of-type(2),
    .about-cms-modal.is-service-card-focus .about-cms-editor-panel[data-about-editor-panel="citizens-charter"] [data-about-service-add-wrapper] {
        display: none !important;
    }

    /* Service header focus */
    .about-cms-modal.is-service-header-focus .about-cms-editor-panel[data-about-editor-panel="citizens-charter"] > form > h4,
    .about-cms-modal.is-service-header-focus .about-cms-editor-panel[data-about-editor-panel="citizens-charter"] > form > .about-cms-card-stack,
    .about-cms-modal.is-service-header-focus .about-cms-editor-panel[data-about-editor-panel="citizens-charter"] [data-about-service-add-wrapper] {
        display: none !important;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-stack {
        gap: 0;
        overflow: visible;
    }

    .about-link-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .about-link-row input {
        flex: 1;
    }
    .about-link-paste {
        width: 42px;
        height: 42px;
        border: 1px solid #d7dbe2;
        border-radius: 10px;
        background: #f8fafc;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background-color 0.2s, color 0.2s;
    }
    .about-link-paste:hover {
        background: #eef2f7;
        color: #1f2937;
    }

    .about-cms-editor-panel.is-card-focus [data-about-card-panel-meta],
    .about-cms-editor-panel.is-card-focus [data-about-card-editor-head] {
        display: none;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active {
        position: relative;
        padding: 22px;
        max-width: 100%;
        width: 100%;
        margin: 0 auto;
        overflow: visible;
        border: none;
        background: transparent;
        box-shadow: none;
    }

    .about-cms-editor-panel[data-about-editor-panel="logo-and-symbols"].is-card-focus .about-cms-card-editor.is-active {
        padding: 24px 26px;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active .form-group + .form-group {
        margin-top: 0;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active .about-cms-form-grid > .form-group + .form-group {
        margin-top: 0;
    }

    .about-cms-modal.is-card-focus .about-cms-modal-footer {
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
        padding-top: 6px;
        padding-right: 22px;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active > .about-cms-modal-footer {
        max-width: 100%;
        margin: 18px 0 0;
        padding-top: 0;
        padding-right: 0;
    }

    .about-cms-modal.is-chart-focus .about-cms-modal-header p {
        display: none;
    }

    .about-cms-modal.is-chart-focus .about-cms-card-stack {
        display: none;
    }

    .about-cms-modal.is-chart-focus .about-cms-modal-panels {
        padding: 12px 12px 18px;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-shell {
        max-width: none;
        width: 100%;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
        padding: 12px;
        border-color: rgba(127, 17, 19, 0.28);
        background: linear-gradient(180deg, #fffaf2 0%, #fff4eb 100%);
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-preview-column {
        min-height: 0;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-media {
        height: auto;
        min-height: 0;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-preview {
        height: auto;
        min-height: 0;
        max-height: min(84vh, 1200px);
        object-fit: contain;
        border: 2px solid rgba(127, 17, 19, 0.16);
        box-shadow:
            0 0 0 1px rgba(255, 255, 255, 0.9),
            0 20px 42px rgba(127, 17, 19, 0.16);
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-upload {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        grid-template-areas:
            "icon title button"
            "icon copy button"
            "icon file button";
        align-items: center;
        justify-items: center;
        column-gap: 16px;
        row-gap: 2px;
        width: 100%;
        min-height: 0;
        padding: 14px 16px;
        border: 0;
        border-radius: 14px;
        background: radial-gradient(circle at top, rgba(151, 26, 33, 0.98), rgba(96, 12, 18, 0.98));
        color: #f8f4ef;
        text-align: center;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-icon {
        grid-area: icon;
        width: 44px;
        height: 44px;
        background: rgba(73, 8, 13, 0.42);
        color: #f2f0ed;
        font-size: 1.05rem;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-upload-title {
        grid-area: title;
        color: #fff8f1;
        font-size: 0.92rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-upload-copy {
        grid-area: copy;
        max-width: 640px;
        color: rgba(255, 255, 255, 0.78);
        font-size: 0.78rem;
        line-height: 1.35;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-upload-button {
        grid-area: button;
        justify-self: end;
        min-height: 40px;
        padding: 0 18px;
        background: #fff8f1;
        color: #1b1714;
        box-shadow: none;
    }

    .about-cms-modal.is-chart-focus .about-cms-image-dropzone-file {
        grid-area: file;
        max-width: 640px;
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.74rem;
        line-height: 1.35;
    }

    @media (max-width: 720px) {
        .about-cms-modal.is-chart-focus .about-cms-image-dropzone-upload {
            grid-template-columns: auto minmax(0, 1fr);
            grid-template-areas:
                "icon title"
                "icon copy"
                "icon file"
                "button button";
            justify-items: center;
        }

        .about-cms-modal.is-chart-focus .about-cms-image-dropzone-upload-button {
            justify-self: center;
        }
    }

    .about-cms-modal.is-chart-focus 
    

    

    

    

.about-cms-image-dropzone-remove {
        top: 16px;
        right: 16px;
    }

    .about-cms-modal.is-chart-focus .about-cms-modal-footer {
        width: 100%;
        max-width: 1080px;
        margin: 18px auto 0;
        padding-top: 4px;
    }

    .about-cms-editor-panel[data-about-editor-panel="logo-and-symbols"].is-card-focus .about-cms-modal-footer {
        max-width: 980px;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-history-meta-grid .form-group + .form-group {
        margin-top: 0;
    }

    .about-cms-editor-panel.is-card-focus .rich-editor,
    .about-cms-editor-panel.is-card-focus .rich-editor-toolbar,
    .about-cms-editor-panel.is-card-focus .rich-editor-surface,
    .about-cms-editor-panel.is-card-focus .rich-editor-footer {
        max-width: 100%;
    }

    .about-cms-editor-panel.is-card-focus .rich-editor-toolbar {
        overflow: visible;
        position: relative;
        z-index: 6;
    }

    .about-cms-editor-panel.is-card-focus .rich-editor-color-wrap,
    .about-cms-editor-panel.is-card-focus .rich-editor-fontsize-wrap {
        z-index: 7;
    }

    .about-cms-modal.is-card-focus .about-cms-modal-close {
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(127, 17, 19, 0.08);
        font-size: 1.35rem;
        z-index: 10;
    }

    @media (max-width: 768px) {
        .about-cms-workspace {
            --about-preview-width: 1440px;
            --about-preview-height: 1760px;
            --about-preview-min-height: 0px;
            --about-preview-scale: 0.58;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .about-cms-preview-head,
        .about-cms-card-editor-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .about-cms-form-grid {
            grid-template-columns: 1fr;
        }

        .about-cms-history-meta-grid {
            grid-template-columns: 1fr;
        }

        .about-cms-official-meta-row {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .about-cms-history-date-fields {
            grid-template-columns: 1fr;
        }

        .about-cms-link-row {
            grid-template-columns: 1fr;
        }

        .about-cms-link-row [data-about-seal-link-delete] {
            width: 100%;
        }

        .about-cms-modal-dialog {
            width: min(100vw - 20px, 1320px);
            max-height: calc(100vh - 20px);
            margin: 10px auto;
        }

        .about-cms-modal-header,
        .about-cms-modal-panels {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

<script>
    (() => {
        if (window.__aboutCmsPreviewEditorReady) {
            return;
        }

        const ABOUT_PREVIEW_MIN_LOADING_MS = 800;
        let aboutPreviewFitFrame = null;
        const ABOUT_PREVIEW_STORAGE_KEY = `cms:about-preview-route:${window.location.pathname}`;
        const ABOUT_PREVIEW_LEGACY_STORAGE_KEY = '{{ $idPrefix }}-active-about-preview-page';
        let currentAboutPreviewRoute = 'overview';
        const aboutModalChromePlacements = new WeakMap();

        function rememberAboutModalChromePlacement(element) {}
        function restoreAboutModalChrome(modal) {}
        function placeAboutCardModalChrome(modal, activeCard) {}

        function getStoredAboutPreviewRoute() {
            try {
                return window.localStorage.getItem(ABOUT_PREVIEW_STORAGE_KEY)
                    || window.localStorage.getItem(ABOUT_PREVIEW_LEGACY_STORAGE_KEY)
                    || '';
            } catch (_) {
                return '';
            }
        }

        function storeAboutPreviewRoute(routeKey) {
            try {
                const storedRoute = String(routeKey || 'overview');
                window.localStorage.setItem(ABOUT_PREVIEW_STORAGE_KEY, storedRoute);
                window.localStorage.setItem(ABOUT_PREVIEW_LEGACY_STORAGE_KEY, storedRoute);
            } catch (_) {
                // Ignore storage access failures and keep the in-memory route only.
            }
        }

        function getAboutPreviewPayloads() {
            const el = document.querySelector('[data-about-preview-pages]');
            if (!el) {
                return {};
            }

            try {
                return JSON.parse(el.textContent || '{}');
            } catch (_) {
                return {};
            }
        }

        function fitAboutPreview(frame) {
            const workspace = frame.closest('.about-cms-workspace');
            const shell = frame.closest('.about-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--about-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--about-preview-scale', `${scale}`);
        }

        function setAboutPreviewLoading(frame, isLoading) {
            const canvas = frame?.closest('.about-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            if (frame.__aboutPreviewLoadingTimeout) {
                window.clearTimeout(frame.__aboutPreviewLoadingTimeout);
                frame.__aboutPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__aboutPreviewLoadingSession = (frame.__aboutPreviewLoadingSession || 0) + 1;
                frame.__aboutPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__aboutPreviewLoadingSession || 0,
                },
            }));
        }

        function finishAboutPreviewLoading(frame) {
            const canvas = frame?.closest('.about-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            const activeSession = frame.__aboutPreviewLoadingSession || 0;
            const startedAt = frame.__aboutPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, ABOUT_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__aboutPreviewLoadingTimeout) {
                window.clearTimeout(frame.__aboutPreviewLoadingTimeout);
            }

            frame.__aboutPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__aboutPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__aboutPreviewLoadingTimeout = null;
            }, remaining);
        }

        function getAboutPreviewElementBottom(element) {
            return element.offsetTop + element.offsetHeight;
        }

        function isAboutPreviewMeasuredElement(element) {
            if (!(element instanceof HTMLElement)) {
                return false;
            }

            const styles = window.getComputedStyle(element);
            return styles.display !== 'none'
                && styles.visibility !== 'hidden'
                && styles.position !== 'fixed';
        }

        function measureAboutPreviewHeight(frame) {
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
                .filter((element) => isAboutPreviewMeasuredElement(element));

            const contentBottom = visibleElements.reduce((maxBottom, element) => {
                return Math.max(maxBottom, getAboutPreviewElementBottom(element));
            }, scope.offsetHeight);

            return Math.max(1, Math.ceil(contentBottom));
        }

        function setAboutPreviewHeight(frame, nextHeight) {
            const workspace = frame.closest('.about-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);
            const visionHeightCap = currentAboutPreviewRoute === 'vision-and-mission'
                ? Number.parseFloat(getComputedStyle(workspace || document.documentElement).getPropertyValue('--about-preview-vision-height-cap')) || 0
                : 0;
            const nextViewportHeight = visionHeightCap > 0 ? Math.min(height, visionHeightCap) : height;

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--about-preview-height', `${nextViewportHeight}px`);
            frame.style.height = `${nextViewportHeight}px`;
            fitAboutPreview(frame);
        }

        function scheduleAboutPreviewSync(frame) {
            if (!frame) {
                return;
            }

            if (frame.__aboutPreviewSyncFrame !== undefined && frame.__aboutPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__aboutPreviewSyncFrame);
            }

            frame.__aboutPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureAboutPreviewHeight(frame);

                if (measuredHeight > 0) {
                    setAboutPreviewHeight(frame, measuredHeight);
                } else {
                    fitAboutPreview(frame);
                }

                frame.__aboutPreviewSyncFrame = null;
            });
        }

        function queueAboutPreviewSettledSync(frame) {
            scheduleAboutPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleAboutPreviewSync(frame), delay);
            });
            finishAboutPreviewLoading(frame);
        }

        function bindAboutPreviewDocument(frame) {
            const doc = frame.contentDocument;
            const win = frame.contentWindow;

            if (!doc) {
                return;
            }

            if (typeof frame.__aboutPreviewCleanup === 'function') {
                frame.__aboutPreviewCleanup();
            }

            const cleanups = [];
            const schedule = () => queueAboutPreviewSettledSync(frame);
            const main = doc.querySelector('.main-content');
            const focusVisionCoreValues = () => {
                if (currentAboutPreviewRoute !== 'vision-and-mission') {
                    return;
                }

                const target = doc.querySelector('.about-values-band');
                if (!(target instanceof HTMLElement) || !win || typeof win.scrollTo !== 'function') {
                    return;
                }

                const top = Math.max(0, target.offsetTop - 24);
                win.scrollTo({ top, behavior: 'auto' });
            };

            if (typeof window.bindCmsPreviewScrollBridge === 'function') {
                window.bindCmsPreviewScrollBridge(frame, cleanups);
            }

            const bindPreviewImages = () => {
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
            };

            bindPreviewImages();

            if (typeof ResizeObserver !== 'undefined') {
                const observer = new ResizeObserver(() => {
                    schedule();
                });

                if (doc.documentElement) {
                    observer.observe(doc.documentElement);
                }

                if (doc.body) {
                    observer.observe(doc.body);
                }

                if (main) {
                    observer.observe(main);
                }

                cleanups.push(() => observer.disconnect());
            }

            if (typeof MutationObserver !== 'undefined') {
                const observer = new MutationObserver(() => {
                    bindPreviewImages();
                    schedule();
                });

                observer.observe(doc.body || doc.documentElement, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'style', 'src'],
                });

                cleanups.push(() => observer.disconnect());
            }

            if (doc.fonts?.ready) {
                doc.fonts.ready.then(() => schedule()).catch(() => {});
            }

            if (win) {
                const handleResize = () => schedule();
                win.addEventListener('resize', handleResize);
                cleanups.push(() => win.removeEventListener('resize', handleResize));
            }

            focusVisionCoreValues();
            [120, 320, 720].forEach((delay) => {
                window.setTimeout(focusVisionCoreValues, delay);
            });
            frame.__aboutPreviewCleanup = () => {
                cleanups.forEach((cleanup) => cleanup());
            };
        }

        function scheduleFitAboutPreviews() {
            if (aboutPreviewFitFrame !== null) {
                window.cancelAnimationFrame(aboutPreviewFitFrame);
            }

            aboutPreviewFitFrame = window.requestAnimationFrame(() => {
                const frame = document.querySelector('[data-about-preview-frame]');
                if (frame) {
                    scheduleAboutPreviewSync(frame);
                }

                aboutPreviewFitFrame = null;
            });
        }

        window.__aboutPreviewCache = window.__aboutPreviewCache || {};

        function loadAboutPreviewPage(routeKey, options = {}) {
            const frame = document.querySelector('[data-about-preview-frame]');
            const targetKey = routeKey || 'overview';
            const shouldForceReload = options.forceReload === true;
            const explicitSessionId = options.sessionId;

            if (!frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__aboutPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            if (!shouldForceReload && currentAboutPreviewRoute === targetKey && (typeof window.hasCmsPreviewFrameContent === 'function' ? window.hasCmsPreviewFrameContent(frame) : !!frame.srcdoc)) {
                setAboutPreviewLoading(frame, true);
                queueAboutPreviewSettledSync(frame);
                return;
            }

            currentAboutPreviewRoute = targetKey;
            storeAboutPreviewRoute(targetKey);
            setAboutPreviewLoading(frame, true);

            document.querySelectorAll('[data-about-preview-page]').forEach((btn) => {
                btn.classList.toggle('is-active', btn.getAttribute('data-about-preview-page') === targetKey);
            });

            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, html);
                } else {
                    frame.srcdoc = html;
                }
            };

            if (!shouldForceReload && window.__aboutPreviewCache[targetKey]) {
                applyHtml(window.__aboutPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/about/${targetKey}`;

            fetch(previewUrl)
                .then(async response => {
                    const previewHtml = await response.text();
                    let json = null;
                    try { json = JSON.parse(previewHtml); } catch (_) {}

                    if (!response.ok || (json && json.session_expired)) {
                        const failure = typeof window.cmsResolveRequestError === 'function'
                            ? window.cmsResolveRequestError({ response, json, raw: previewHtml })
                            : null;

                        if (failure?.sessionExpired && typeof window.handleSessionExpired === 'function') {
                            window.handleSessionExpired(failure.redirect);
                            return;
                        }
                    }

                    window.__aboutPreviewCache[targetKey] = previewHtml;
                    if (currentAboutPreviewRoute === targetKey) {
                        applyHtml(previewHtml);
                    }
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
        }

        function resolveAboutEditorRoute(sectionKey, providedRoute = '') {
            const route = String(providedRoute || '').trim();
            if (route !== '') {
                return route;
            }

            if (sectionKey === 'hero' || sectionKey === 'intro' || sectionKey === 'contents' || sectionKey === 'philosophy') {
                return 'overview';
            }

            if (
                sectionKey === 'vision-mission-header'
                || sectionKey === 'vision-statement'
                || sectionKey === 'mission-statement'
                || sectionKey === 'vision-mission-statements'
                || sectionKey === 'strategic-goals'
                || sectionKey === 'core-values'
            ) {
                return 'vision-and-mission';
            }

            if (sectionKey === 'strategic-development-plan-header') {
                return 'strategic-development-plan';
            }

            return String(sectionKey || 'overview');
        }

        function openAboutEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-about-editor-modal]');
            if (!modal) {
                return;
            }

            const targetRoute = resolveAboutEditorRoute(sectionKey, options.route || '');
            currentAboutPreviewRoute = targetRoute;
            storeAboutPreviewRoute(targetRoute);

            const title = modal.querySelector('#{{ $idPrefix }}-modal-title');
            const description = modal.querySelector('[data-about-editor-description]');
            const isChartFocus = sectionKey === 'campus-officials' && options.chartOnly === true;

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');
            restoreAboutModalChrome(modal);
            modal.dataset.aboutActivePanel = sectionKey;
            modal.classList.remove('is-official-card-focus');
            modal.classList.toggle('is-chart-focus', isChartFocus);

            modal.querySelectorAll('[data-about-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-about-editor-panel') === sectionKey;
                const isContentsCardFocus = sectionKey === 'contents' && String(options.slug || '').trim() !== '';
                const isHistoryCardFocus = sectionKey === 'history' && String(options.historyIndex ?? '').trim() !== '';
                const isStrategicGoalFocus = sectionKey === 'strategic-goals' && String(options.strategicGoalIndex ?? '').trim() !== '';
                const isPlanPriorityFocus = sectionKey === 'strategic-development-plan' && String(options.planPriorityIndex ?? '').trim() !== '';
                const isOfficialCardFocus = sectionKey === 'campus-officials' && !isChartFocus && String(options.officialIndex ?? '').trim() !== '';
                const isSealCardFocus = sectionKey === 'logo-and-symbols' && String(options.sealIndex ?? '').trim() !== '';
                const isServiceCardFocus = sectionKey === 'citizens-charter' && String(options.serviceIndex ?? '').trim() !== '';
                const isServiceHeaderFocus = sectionKey === 'citizens-charter' && options.headerFocus === true;
                const isCardFocus = isContentsCardFocus || isHistoryCardFocus || isStrategicGoalFocus || isPlanPriorityFocus || isOfficialCardFocus || isSealCardFocus || isServiceCardFocus;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    modal.classList.toggle('is-official-card-focus', sectionKey === 'campus-officials' && isCardFocus);
                    modal.classList.toggle('is-service-card-focus', isServiceCardFocus);
                    modal.classList.toggle('is-service-header-focus', isServiceHeaderFocus);
                    if (title) {
                        title.textContent = label || 'Edit about section';
                    }

                    if (description) {
                        description.hidden = isChartFocus;
                        description.textContent = isChartFocus ? '' : 'Update this section and save to refresh the About page preview.';
                    }

                    let focusScope = panel;
                    if (sectionKey === 'contents') {
                        focusScope = setActiveContentsEditor(options.slug || '') || panel;
                    } else if (sectionKey === 'history') {
                        focusScope = setActiveHistoryEditor(options.historyIndex ?? '') || panel;
                    } else if (sectionKey === 'strategic-goals') {
                        focusScope = setActiveStrategicGoalEditor(options.strategicGoalIndex ?? '', isCardFocus) || panel;
                    } else if (sectionKey === 'strategic-development-plan') {
                        focusScope = setActivePlanPriorityEditor(options.planPriorityIndex ?? '', isCardFocus) || panel;
                    } else if (sectionKey === 'campus-officials' && !isChartFocus) {
                        focusScope = setActiveOfficialEditor(options.officialIndex ?? '', panel) || panel;
                    } else if (sectionKey === 'logo-and-symbols') {
                        focusScope = setActiveSealEditor(options.sealIndex ?? '', panel, isCardFocus) || panel;
                    } else if (sectionKey === 'citizens-charter') {
                        focusScope = setActiveServiceEditor(options.serviceIndex ?? '', panel) || panel;
                    }

                    if (isCardFocus && focusScope?.classList?.contains('about-cms-card-editor')) {
                        placeAboutCardModalChrome(modal, focusScope);
                    }

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea:not([hidden]), select, .rich-editor-surface');
                    firstField?.focus();
                }
            });
        }

        function closeAboutEditor() {
            const modal = document.querySelector('[data-about-editor-modal]');
            if (!modal) {
                return;
            }

            const activePanel = modal.querySelector('[data-about-editor-panel]:not([hidden])');
            if (activePanel?.getAttribute('data-about-editor-panel') === 'logo-and-symbols') {
                discardPendingSealDrafts();
            }

            modal.hidden = true;
            restoreAboutModalChrome(modal);
            delete modal.dataset.aboutActivePanel;
            modal.classList.remove('is-card-focus');
            modal.classList.remove('is-official-card-focus');
            modal.classList.remove('is-service-card-focus');
            modal.classList.remove('is-service-header-focus');
            modal.classList.remove('is-chart-focus');
            const description = modal.querySelector('[data-about-editor-description]');
            if (description) {
                description.hidden = false;
                description.textContent = 'Select a section from the preview to start editing.';
            }
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
        }

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-about-edit') {
                let section = data.section || '';
                let isHeaderFocus = false;
                if (section === 'citizens-charter-header') {
                    section = 'citizens-charter';
                    isHeaderFocus = true;
                }
                openAboutEditor(section, data.label || 'Edit about section', {
                    route: data.route || '',
                    headerFocus: isHeaderFocus,
                });
                return;
            }

            if (data.type === 'cms-about-service-card-add') {
                const editor = addServiceEditor({
                    title: '',
                    description: '',
                    link: '',
                }, true);
                const nextIndex = editor?.getAttribute('data-about-service-index') || '';
                openAboutEditor('citizens-charter', data.label || 'Add Office', {
                    serviceIndex: nextIndex,
                    route: 'citizens-charter',
                });
                return;
            }

            if (data.type === 'cms-about-service-card-delete') {
                confirmDeleteServiceCard(data.index !== undefined && data.index !== null ? data.index : '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-service-card-edit') {
                openAboutEditor('citizens-charter', data.label ? `Edit ${data.label}` : 'Edit Office', {
                    serviceIndex: data.index !== undefined && data.index !== null ? data.index : '',
                    route: 'citizens-charter',
                });
                return;
            }

            if (data.type === 'cms-about-contents-card-edit') {
                openAboutEditor('contents', data.label ? `Edit ${data.label}` : 'Edit about card', {
                    slug: data.slug || '',
                    route: data.route || 'overview',
                });
                return;
            }

            if (data.type === 'cms-about-contents-card-delete') {
                confirmDeleteContentsCard(data.slug || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-history-card-edit') {
                openAboutEditor('history', data.label ? `Edit ${data.label}` : 'Edit history milestone', {
                    historyIndex: data.index || '',
                    route: data.route || 'history',
                });
                return;
            }

            if (data.type === 'cms-about-strategic-goal-edit') {
                openAboutEditor('strategic-goals', data.label ? `Edit ${data.label}` : 'Edit strategic goal pillar', {
                    strategicGoalIndex: data.index || '',
                    route: data.route || 'vision-and-mission',
                });
                return;
            }

            if (data.type === 'cms-about-plan-priority-edit') {
                openAboutEditor('strategic-development-plan', data.label ? `Edit ${data.label}` : 'Edit development priority', {
                    planPriorityIndex: data.index || '',
                    route: data.route || 'strategic-development-plan',
                });
                return;
            }

            if (data.type === 'cms-about-plan-priority-add') {
                initPlanPrioritiesEditor();
                const editor = addPlanPriorityEditor({
                    title: '',
                    body: '',
                }, true);
                const nextIndex = editor?.getAttribute('data-about-plan-priority-index') || '';
                openAboutEditor('strategic-development-plan', data.label || 'Add development priority', {
                    planPriorityIndex: nextIndex,
                    route: data.route || 'strategic-development-plan',
                });
                return;
            }

            if (data.type === 'cms-about-plan-priority-delete') {
                confirmDeletePlanPriority(data.index || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-official-card-add') {
                const editor = addOfficialEditor({
                    title: '',
                    name: '',
                    body: '',
                    image: '',
                    order: '',
                }, true);
                const nextIndex = editor?.getAttribute('data-about-official-index') || '';
                openAboutEditor('campus-officials', data.label || 'Add campus official', {
                    officialIndex: nextIndex,
                    route: data.route || 'campus-officials',
                });
                return;
            }

            if (data.type === 'cms-about-official-chart-edit') {
                openAboutEditor('campus-officials', 'Organizational Structure and Image Uploader', {
                    route: data.route || 'campus-officials',
                    chartOnly: true,
                });
                return;
            }

            if (data.type === 'cms-about-official-card-delete') {
                confirmDeleteOfficialCard(data.index || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-official-card-edit') {
                openAboutEditor('campus-officials', data.label ? `Edit ${data.label}` : 'Edit campus official', {
                    officialIndex: data.index || '',
                    route: data.route || 'campus-officials',
                });
                return;
            }

            if (data.type === 'cms-about-seal-card-add') {
                initSealsEditor();
                const editor = addSealEditor({
                    id: '',
                    label: '',
                    tag: '',
                    image: '',
                    highlights: [],
                    information: { title: 'Informations about the Seal', description: '' },
                    reports: { title: 'Reports and Records', description: '' },
                    links: [],
                }, true, true);
                const nextIndex = editor?.getAttribute('data-about-seal-index') || '';
                openAboutEditor('logo-and-symbols', data.label || 'Add seal', {
                    sealIndex: nextIndex,
                    route: data.route || 'logo-and-symbols',
                });
                return;
            }

            if (data.type === 'cms-about-seal-card-delete') {
                confirmDeleteSealCard(data.index || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-seal-card-edit') {
                openAboutEditor('logo-and-symbols', data.label ? `Edit ${data.label}` : 'Edit seal', {
                    sealIndex: data.index || '',
                    route: data.route || 'logo-and-symbols',
                });
                return;
            }

            if (data.type === 'cms-about-preview-route') {
                loadAboutPreviewPage(data.route || 'overview');
                return;
            }

            if (data.type === 'cms-about-preview-height') {
                const frame = document.querySelector('[data-about-preview-frame]');
                if (frame && frame.contentWindow === event.source) {
                    setAboutPreviewHeight(frame, data.height);
                    scheduleFitAboutPreviews();
                }
            }
        });

        document.addEventListener('click', (event) => {
            const closeTrigger = event.target.closest('[data-close-about-editor]');
            if (closeTrigger) {
                event.preventDefault();
                closeAboutEditor();
                return;
            }

            const previewBtn = event.target.closest('[data-about-preview-page]');
            if (previewBtn) {
                event.preventDefault();
                loadAboutPreviewPage(previewBtn.getAttribute('data-about-preview-page') || 'overview');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAboutEditor();
            }
        });

        const contentsForm = document.querySelector('[data-about-contents-form]');
        const contentsVersionInput = document.querySelector('[data-about-contents-version]');
        const activeContentsSlugInput = document.querySelector('[data-about-active-contents-slug]');
        const introForm = document.querySelector('[data-about-intro-form]');
        const introVersionInput = document.querySelector('[data-about-intro-version]');
        const philosophyForm = document.querySelector('[data-about-philosophy-form]');
        const philosophyVersionInput = document.querySelector('[data-about-philosophy-version]');
        const historyForm = document.querySelector('[data-about-history-form]');
        const historyVersionInput = document.querySelector('[data-about-history-version]');
        const activeHistoryIndexInput = document.querySelector('[data-about-active-history-index]');
        const officialsForm = document.querySelector('[data-about-editor-panel="campus-officials"] form');
        const officialsList = officialsForm?.querySelector('[data-about-officials-list]') || null;
        const officialsVersionInput = officialsForm?.querySelector('[data-about-officials-version]') || null;
        const activeOfficialIndexInput = officialsForm?.querySelector('[data-about-active-official-index]') || null;
        const officialsTemplate = officialsForm?.querySelector('[data-about-official-template]') || null;
        const sealsForm = document.querySelector('[data-about-editor-panel="logo-and-symbols"] form[data-about-seals-form]');
        const sealsList = sealsForm?.querySelector('[data-about-seals-list]') || null;
        const sealsVersionInput = sealsForm?.querySelector('[data-about-seals-version]') || null;
        const activeSealIndexInput = sealsForm?.querySelector('[data-about-active-seal-index]') || null;
        const sealsTemplate = sealsForm?.querySelector('[data-about-seal-template]') || null;
        const sealLinkTemplate = sealsForm?.querySelector('[data-about-seal-link-template]') || null;
        const pendingSealDraftEditors = new Set();
        const planPrioritiesForm = document.querySelector('[data-about-editor-panel="strategic-development-plan"] form[data-about-plan-priorities-form]');
        const planPrioritiesList = planPrioritiesForm?.querySelector('[data-about-plan-priorities-list]') || null;
        const planPrioritiesVersionInput = planPrioritiesForm?.querySelector('[data-about-plan-priorities-version]') || null;
        const activePlanPriorityIndexInput = planPrioritiesForm?.querySelector('[data-about-active-plan-priority-index]') || null;
        const strategicGoalsForm = document.querySelector('[data-about-strategic-goals-form]');
        const strategicGoalsGroups = strategicGoalsForm?.querySelector('[data-about-strategic-goals-groups]') || null;
        const strategicGoalsVersionInput = strategicGoalsForm?.querySelector('[data-about-strategic-goals-version]') || null;
        const activeStrategicGoalIndexInput = strategicGoalsForm?.querySelector('[data-about-active-strategic-goal-index]') || null;
        const coreValuesForm = document.querySelector('[data-about-editor-panel="core-values"] form');
        const coreValuesVersionInput = coreValuesForm?.querySelector('[data-about-core-values-version]') || null;

        const bumpIntroVersion = () => {
            if (introVersionInput) {
                introVersionInput.value = String(Date.now());
            }
        };

        const bumpPhilosophyVersion = () => {
            if (philosophyVersionInput) {
                philosophyVersionInput.value = String(Date.now());
            }
        };

        const bumpContentsVersion = () => {
            if (contentsVersionInput) {
                contentsVersionInput.value = String(Date.now());
            }
        };

        const bumpStrategicGoalsVersion = () => {
            if (strategicGoalsVersionInput) {
                strategicGoalsVersionInput.value = String(Date.now());
            }
        };

        const bumpCoreValuesVersion = () => {
            if (coreValuesVersionInput) {
                coreValuesVersionInput.value = String(Date.now());
            }
        };

        const bumpOfficialsVersion = () => {
            if (officialsVersionInput) {
                officialsVersionInput.value = String(Date.now());
            }
        };

        const bumpSealsVersion = () => {
            if (sealsVersionInput) {
                sealsVersionInput.value = String(Date.now());
            }
        };

        const bumpPlanPrioritiesVersion = () => {
            if (planPrioritiesVersionInput) {
                planPrioritiesVersionInput.value = String(Date.now());
            }
        };

        const createPlanPriorityEditor = (priority = {}) => {
            const template = document.querySelector('[data-about-plan-priority-template]');
            if (!(template instanceof HTMLTemplateElement)) {
                return null;
            }

            const article = template.content.firstElementChild?.cloneNode(true);
            if (!(article instanceof HTMLElement)) {
                return null;
            }

            article.querySelector('[data-about-plan-priority-title]').value = String(priority.title || '');
            const richInput = article.querySelector('.rich-editor-input');
            if (richInput instanceof HTMLTextAreaElement) {
                richInput.value = String(priority.body || '');
            }
            return article;
        };

        const syncPlanPrioritiesForm = () => {
            if (!planPrioritiesList) {
                return;
            }

            const editors = Array.from(planPrioritiesList.querySelectorAll('[data-about-plan-priority-editor]'));
            editors.forEach((editor, index) => {
                editor.setAttribute('data-about-plan-priority-index', String(index));
                const titleInput = editor.querySelector('[data-about-plan-priority-title]');
                const bodyInput = editor.querySelector('.rich-editor-input');
                const heading = editor.querySelector('[data-about-plan-priority-heading]');
                const meta = editor.querySelector('[data-about-plan-priority-meta]');
                const titleValue = String(titleInput?.value || '').trim();
                const fallbackTitle = `Priority Card ${index + 1}`;

                if (titleInput) {
                    titleInput.name = `about[sections][strategic-development-plan][development_priorities][${index}][title]`;
                }

                if (bodyInput instanceof HTMLTextAreaElement) {
                    bodyInput.name = `about[sections][strategic-development-plan][development_priorities][${index}][body]`;
                }

                if (heading) {
                    heading.textContent = titleValue || fallbackTitle;
                }

                if (meta) {
                    meta.textContent = `Priority ${index + 1}`;
                }
            });
        };

        const setActivePlanPriorityEditor = (index = '', collapse = false) => {
            const editors = Array.from(document.querySelectorAll('[data-about-plan-priority-editor]'));

            if (!editors.length) {
                if (activePlanPriorityIndexInput) {
                    activePlanPriorityIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-plan-priority-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = collapse && targetEditor ? !isActive : false;
            });

            if (activePlanPriorityIndexInput) {
                activePlanPriorityIndexInput.value = targetEditor?.getAttribute('data-about-plan-priority-index') || '';
            }

            return targetEditor;
        };

        const addPlanPriorityEditor = (priority = {}, focus = true) => {
            if (!planPrioritiesList) {
                return null;
            }

            const editor = createPlanPriorityEditor(priority);
            if (!editor) {
                return null;
            }
            planPrioritiesList.appendChild(editor);
            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(editor);
            }
            bumpPlanPrioritiesVersion();
            syncPlanPrioritiesForm();

            const nextIndex = editor.getAttribute('data-about-plan-priority-index') || String(planPrioritiesList.querySelectorAll('[data-about-plan-priority-editor]').length - 1);
            if (focus) {
                setActivePlanPriorityEditor(nextIndex, true);
            }

            return editor;
        };

        const deletePlanPriorityByIndex = (index) => {
            if (!planPrioritiesList) {
                return false;
            }

            const normalizedIndex = String(index ?? '').trim();
            const editors = Array.from(planPrioritiesList.querySelectorAll('[data-about-plan-priority-editor]'));
            const targetEditor = editors.find((editor) => editor.getAttribute('data-about-plan-priority-index') === normalizedIndex) || null;
            if (!targetEditor) {
                return false;
            }

            if (editors.length <= 1) {
                const titleInput = targetEditor.querySelector('[data-about-plan-priority-title]');
                const bodyInput = targetEditor.querySelector('.rich-editor-input');
                const bodySurface = targetEditor.querySelector('.rich-editor-surface');
                if (titleInput) {
                    titleInput.value = '';
                }
                if (bodyInput instanceof HTMLTextAreaElement) {
                    bodyInput.value = '';
                }
                if (bodySurface instanceof HTMLElement) {
                    bodySurface.innerHTML = '';
                }
            } else {
                targetEditor.remove();
            }

            bumpPlanPrioritiesVersion();
            syncPlanPrioritiesForm();
            setActivePlanPriorityEditor('', true);
            return true;
        };

        const initPlanPrioritiesEditor = () => {
            if (!planPrioritiesForm || !planPrioritiesList || planPrioritiesForm.dataset.aboutPlanPrioritiesBound === '1') {
                return;
            }

            planPrioritiesForm.dataset.aboutPlanPrioritiesBound = '1';
            if (!planPrioritiesList.querySelector('[data-about-plan-priority-editor]')) {
                planPrioritiesList.appendChild(createPlanPriorityEditor({
                    title: '',
                    body: '',
                }));
            }

            planPrioritiesForm.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-about-plan-priority-add-editor]');
                if (addButton) {
                    event.preventDefault();
                    const editor = addPlanPriorityEditor({
                        title: '',
                        body: '',
                    }, true);
                    editor?.querySelector('[data-about-plan-priority-title]')?.focus();
                    return;
                }

            });

            planPrioritiesForm.addEventListener('input', (event) => {
                if (event.target.closest('[data-about-plan-priority-editor]')) {
                    bumpPlanPrioritiesVersion();
                    syncPlanPrioritiesForm();
                }
            });

            planPrioritiesForm.addEventListener('submit', () => {
                syncPlanPrioritiesForm();
            }, true);

            syncPlanPrioritiesForm();
            setActivePlanPriorityEditor('', false);
        };

        const createStrategicGoalItem = (goal = {}) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'form-group about-cms-goal-item';
            wrapper.setAttribute('data-about-strategic-goal-item', '');
            wrapper.innerHTML = `
                <label data-about-strategic-goal-label>Goal</label>
                <div class="about-cms-goal-row">
                    <input type="text" data-about-strategic-goal-text maxlength="4000" value="">
                    <input type="hidden" data-about-strategic-goal-number value="">
                    <button type="button" class="btn btn-outline-danger" data-about-delete-sg>Delete</button>
                </div>
            `;
            wrapper.querySelector('[data-about-strategic-goal-text]').value = String(goal.text || '');
            wrapper.querySelector('[data-about-strategic-goal-number]').value = String(goal.number || '');
            return wrapper;
        };

        const createStrategicGoalGroup = (group = {}) => {
            const article = document.createElement('article');
            article.className = 'about-cms-card-editor';
            article.setAttribute('data-about-strategic-goal-group', '');
            article.setAttribute('data-about-strategic-goal-editor', '');
            article.setAttribute('data-about-strategic-goal-index', '');
            article.innerHTML = `
                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                    <h4 data-about-strategic-group-heading>Pillar</h4>
                    <span></span>
                </div>
                <div class="about-cms-form-grid about-cms-strategic-pillar-grid">
                    <div class="form-group">
                        <label>Pillar Label</label>
                        <input type="text" data-about-strategic-group-pillar maxlength="255" value="">
                    </div>
                    <div class="form-group">
                        <label>Pillar Title</label>
                        <input type="text" data-about-strategic-group-title maxlength="255" value="">
                    </div>
                </div>
                <div class="about-cms-inline-actions">
                    <button type="button" class="btn btn-outline-secondary" data-about-add-sg>+ Add new SG</button>
                </div>
                <div class="about-cms-goal-stack" data-about-strategic-group-goals></div>
            `;

            article.querySelector('[data-about-strategic-group-pillar]').value = String(group.pillar || '');
            article.querySelector('[data-about-strategic-group-title]').value = String(group.title || '');

            const goalsHost = article.querySelector('[data-about-strategic-group-goals]');
            const goals = Array.isArray(group.goals) && group.goals.length ? group.goals : [{ number: '1', text: '' }];
            goals.forEach((goal) => goalsHost.appendChild(createStrategicGoalItem(goal)));
            return article;
        };

        const syncStrategicGoalsForm = () => {
            if (!strategicGoalsGroups) {
                return;
            }

            const groups = Array.from(strategicGoalsGroups.querySelectorAll('[data-about-strategic-goal-group]'));
            groups.forEach((group, groupIndex) => {
                group.setAttribute('data-about-strategic-goal-index', String(groupIndex));
                const pillarInput = group.querySelector('[data-about-strategic-group-pillar]');
                const titleInput = group.querySelector('[data-about-strategic-group-title]');
                const heading = group.querySelector('[data-about-strategic-group-heading]');
                const headingMeta = group.querySelector('.about-cms-card-editor-head span');
                const pillarValue = String(pillarInput?.value || '').trim() || `Pillar ${groupIndex + 1}`;
                const titleValue = String(titleInput?.value || '').trim();

                if (pillarInput) {
                    pillarInput.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][pillar]`;
                    if (String(pillarInput.value || '').trim() === '') {
                        pillarInput.value = pillarValue;
                    }
                }

                if (titleInput) {
                    titleInput.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][title]`;
                }

                if (heading) {
                    heading.textContent = pillarValue;
                }

                if (headingMeta) {
                    headingMeta.textContent = titleValue;
                }

                const goals = Array.from(group.querySelectorAll('[data-about-strategic-goal-item]'));
                goals.forEach((goalItem, goalIndex) => {
                    const goalText = goalItem.querySelector('[data-about-strategic-goal-text]');
                    const goalNumber = goalItem.querySelector('[data-about-strategic-goal-number]');
                    const goalLabel = goalItem.querySelector('[data-about-strategic-goal-label]');
                    const numberValue = String(goalIndex + 1);

                    if (goalText) {
                        goalText.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][goals][${goalIndex}][text]`;
                    }

                    if (goalNumber) {
                        goalNumber.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][goals][${goalIndex}][number]`;
                        goalNumber.value = numberValue;
                    }

                    if (goalLabel) {
                        goalLabel.textContent = `Goal ${numberValue}`;
                    }
                });
            });
        };

        const setActiveStrategicGoalEditor = (index = '', collapse = false) => {
            const editors = Array.from(document.querySelectorAll('[data-about-strategic-goal-editor]'));

            if (!editors.length) {
                if (activeStrategicGoalIndexInput) {
                    activeStrategicGoalIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-strategic-goal-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.hidden = collapse && targetEditor ? !isActive : false;
            });

            if (activeStrategicGoalIndexInput) {
                activeStrategicGoalIndexInput.value = targetEditor?.getAttribute('data-about-strategic-goal-index') || '';
            }

            return targetEditor;
        };

        const initStrategicGoalsEditor = () => {
            if (!strategicGoalsForm || !strategicGoalsGroups || strategicGoalsForm.dataset.aboutStrategicGoalsBound === '1') {
                return;
            }

            strategicGoalsForm.dataset.aboutStrategicGoalsBound = '1';
            if (!strategicGoalsGroups.querySelector('[data-about-strategic-goal-group]')) {
                strategicGoalsGroups.appendChild(createStrategicGoalGroup({ pillar: 'Pillar 1', title: '', goals: [{ number: '1', text: '' }] }));
            }

            strategicGoalsForm.addEventListener('click', (event) => {
                const addGoal = event.target.closest('[data-about-add-sg]');
                if (addGoal) {
                    event.preventDefault();
                    const group = addGoal.closest('[data-about-strategic-goal-group]');
                    const host = group?.querySelector('[data-about-strategic-group-goals]');
                    if (host) {
                        host.appendChild(createStrategicGoalItem({}));
                        bumpStrategicGoalsVersion();
                        syncStrategicGoalsForm();
                    }
                    return;
                }

                const deleteGoal = event.target.closest('[data-about-delete-sg]');
                if (deleteGoal) {
                    event.preventDefault();
                    const group = deleteGoal.closest('[data-about-strategic-goal-group]');
                    const goalItems = group ? Array.from(group.querySelectorAll('[data-about-strategic-goal-item]')) : [];
                    if (goalItems.length > 1) {
                        deleteGoal.closest('[data-about-strategic-goal-item]')?.remove();
                        bumpStrategicGoalsVersion();
                        syncStrategicGoalsForm();
                    }
                    return;
                }

            });

            strategicGoalsForm.addEventListener('input', (event) => {
                if (event.target.closest('[data-about-strategic-goal-group]')) {
                    bumpStrategicGoalsVersion();
                    syncStrategicGoalsForm();
                }
            });

            strategicGoalsForm.addEventListener('submit', () => {
                syncStrategicGoalsForm();
            }, true);

            syncStrategicGoalsForm();
            setActiveStrategicGoalEditor('', false);
        };

        const shouldTrackAboutContentsField = (target) => {
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

        const bindAboutContentsDirtyTracking = () => {
            if (!contentsForm || contentsForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            contentsForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackAboutContentsField(event.target)) {
                    return;
                }

                bumpContentsVersion();
            };

            contentsForm.addEventListener('input', markDirty);
            contentsForm.addEventListener('change', markDirty);
        };

        const bindAboutIntroDirtyTracking = () => {
            if (!introForm || introForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            introForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.closest('.rich-editor-surface')) {
                    bumpIntroVersion();
                    return;
                }

                if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
                    const type = (target.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset') {
                        return;
                    }
                }

                bumpIntroVersion();
            };

            introForm.addEventListener('input', markDirty);
            introForm.addEventListener('change', markDirty);
            introForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpIntroVersion, 0);
                }
            });
        };

        const bindPhilosophyDirtyTracking = () => {
            if (!philosophyForm || philosophyForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            philosophyForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.closest('.rich-editor-surface')) {
                    bumpPhilosophyVersion();
                    return;
                }

                if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
                    const type = (target.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset') {
                        return;
                    }
                }

                bumpPhilosophyVersion();
            };

            philosophyForm.addEventListener('input', markDirty);
            philosophyForm.addEventListener('change', markDirty);
            philosophyForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpPhilosophyVersion, 0);
                }
            });
        };

        const bindAboutHistoryDirtyTracking = () => {
            if (!historyForm || historyForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            historyForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
                    const type = (target.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset') {
                        return;
                    }
                }

                bumpHistoryVersion();
            };

            historyForm.addEventListener('input', markDirty);
            historyForm.addEventListener('change', markDirty);
            historyForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpHistoryVersion, 0);
                }
            });
        };

        const bindCoreValuesDirtyTracking = () => {
            if (!coreValuesForm || coreValuesForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            coreValuesForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackAboutContentsField(event.target)) {
                    return;
                }

                bumpCoreValuesVersion();
            };

            coreValuesForm.addEventListener('input', markDirty);
            coreValuesForm.addEventListener('change', markDirty);
        };

        const bindOfficialsDirtyTracking = () => {
            if (!officialsForm || officialsForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            officialsForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackAboutContentsField(event.target)) {
                    return;
                }

                bumpOfficialsVersion();
            };

            officialsForm.addEventListener('input', markDirty);
            officialsForm.addEventListener('change', markDirty);
        };

        const bindSealsDirtyTracking = () => {
            if (!sealsForm || sealsForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            sealsForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.closest('.rich-editor-surface')) {
                    bumpSealsVersion();
                    return;
                }

                if (!shouldTrackAboutContentsField(target)) {
                    return;
                }

                bumpSealsVersion();
            };

            sealsForm.addEventListener('input', markDirty);
            sealsForm.addEventListener('change', markDirty);
            sealsForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpSealsVersion, 0);
                }
            });
        };

        const initOfficialsEditor = () => {
            if (!officialsForm || !officialsList || officialsForm.dataset.aboutOfficialsBound === '1') {
                return;
            }

            officialsForm.dataset.aboutOfficialsBound = '1';

            if (!officialsList.querySelector('[data-about-official-editor]')) {
                addOfficialEditor({
                    title: '',
                    name: '',
                    body: '',
                    image: '',
                    order: '',
                }, false);
            } else {
                relabelOfficialEditors();
                setActiveOfficialEditor('', officialsForm);
            }

            officialsForm.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-about-official-add-editor]');
                if (addButton) {
                    event.preventDefault();
                    addOfficialEditor({
                        title: '',
                        name: '',
                        body: '',
                        image: '',
                        order: '',
                    }, true);
                    return;
                }

                const editorHead = event.target.closest('[data-about-card-editor-head]');
                if (editorHead) {
                    const editor = editorHead.closest('[data-about-official-editor]');
                    const officialIndex = editor?.getAttribute('data-about-official-index') || '';
                    setActiveOfficialEditor(officialIndex, officialsForm);
                }
            });

            officialsForm.addEventListener('input', (event) => {
                const editor = event.target.closest('[data-about-official-editor]');
                if (!editor) {
                    return;
                }

                const editorIndex = Array.from(officialsList.querySelectorAll('[data-about-official-editor]')).indexOf(editor);
                syncOfficialCardMeta(editor, editorIndex >= 0 ? editorIndex : null);
            });
        };

        const formatHistoryMonth = (value) => {
            const match = String(value || '').match(/^(\d{4})-(\d{2})$/);
            if (!match) {
                return '';
            }

            const year = Number(match[1]);
            const monthIndex = Number(match[2]) - 1;
            if (!Number.isFinite(year) || !Number.isFinite(monthIndex) || monthIndex < 0 || monthIndex > 11) {
                return '';
            }

            return new Intl.DateTimeFormat('en', {
                month: 'long',
                year: 'numeric',
                timeZone: 'UTC',
            }).format(new Date(Date.UTC(year, monthIndex, 1)));
        };

        const syncAboutHistoryDateGroup = (group) => {
            const periodInput = group.querySelector('[data-about-history-period]');
            const startInput = group.querySelector('[data-about-history-date-start]');
            const endInput = group.querySelector('[data-about-history-date-end]');

            if (!periodInput || !startInput) {
                return;
            }

            const startLabel = formatHistoryMonth(startInput.value);
            const endLabel = formatHistoryMonth(endInput?.value || '');
            const nextPeriod = startLabel && endLabel
                ? `${startLabel} - ${endLabel}`
                : (startLabel || endLabel);

            if (nextPeriod !== '' && periodInput.value !== nextPeriod) {
                periodInput.value = nextPeriod;
                periodInput.dispatchEvent(new Event('input', {
                    bubbles: true,
                }));
                periodInput.dispatchEvent(new Event('change', {
                    bubbles: true,
                }));
                bumpHistoryVersion();
            }
        };

        const initAboutHistoryDateFields = (scope = document) => {
            scope.querySelectorAll('[data-about-history-date-group]').forEach((group) => {
                if (group.dataset.aboutHistoryDatesBound === '1') {
                    return;
                }

                group.dataset.aboutHistoryDatesBound = '1';
                const sync = () => syncAboutHistoryDateGroup(group);
                group.querySelectorAll('[data-about-history-date-start], [data-about-history-date-end]').forEach((input) => {
                    input.addEventListener('input', sync);
                    input.addEventListener('change', sync);
                });
            });
        };

        window.syncAboutHistoryDateFields = (scope = document) => {
            scope.querySelectorAll('[data-about-history-date-group]').forEach(syncAboutHistoryDateGroup);
        };

        const setActiveContentsEditor = (slug = '') => {
            const editors = Array.from(document.querySelectorAll('[data-about-contents-editor]'));

            if (!editors.length) {
                if (activeContentsSlugInput) {
                    activeContentsSlugInput.value = '';
                }
                return null;
            }

            let targetEditor = null;

            if (slug !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-contents-slug') === slug) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                editor.classList.toggle('is-active', editor === targetEditor);
            });

            if (activeContentsSlugInput) {
                activeContentsSlugInput.value = targetEditor?.getAttribute('data-about-contents-slug') || '';
            }

            return targetEditor;
        };

        const submitContentsForm = () => {
            if (!contentsForm) {
                return;
            }

            if (typeof contentsForm.requestSubmit === 'function') {
                contentsForm.requestSubmit();
                return;
            }

            contentsForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const submitPlanPrioritiesForm = () => {
            if (!planPrioritiesForm) {
                return;
            }

            syncPlanPrioritiesForm();
            if (typeof planPrioritiesForm.requestSubmit === 'function') {
                planPrioritiesForm.requestSubmit();
                return;
            }

            planPrioritiesForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const submitOfficialsForm = () => {
            if (!officialsForm) {
                return;
            }

            if (typeof officialsForm.requestSubmit === 'function') {
                officialsForm.requestSubmit();
                return;
            }

            officialsForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const bumpHistoryVersion = () => {
            if (historyVersionInput) {
                historyVersionInput.value = String(Date.now());
            }
        };

        const setActiveHistoryEditor = (index = '') => {
            const editors = Array.from(document.querySelectorAll('[data-about-history-editor]'));

            if (!editors.length) {
                if (activeHistoryIndexInput) {
                    activeHistoryIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-history-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors.find((editor) => editor.querySelector('[data-about-history-visible]')?.value !== '0') || editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.hidden = targetEditor ? !isActive : false;
            });

            if (activeHistoryIndexInput) {
                activeHistoryIndexInput.value = targetEditor?.getAttribute('data-about-history-index') || '';
            }

            return targetEditor;
        };

        const syncOfficialCardMeta = (editor, displayIndex = null) => {
            if (!editor) {
                return;
            }

            const heading = editor.querySelector('[data-about-official-heading]');
            const meta = editor.querySelector('[data-about-official-meta]');
            const titleInput = editor.querySelector('input[name*="[title]"]');
            const nameInput = editor.querySelector('input[name*="[name]"]');
            const titleValue = String(titleInput?.value || '').trim();
            const nameValue = String(nameInput?.value || '').trim();
            const fallbackMeta = 'Campus official';

            if (heading && Number.isFinite(displayIndex)) {
                heading.textContent = `Official Card ${Number(displayIndex) + 1}`;
            }

            if (meta) {
                meta.textContent = nameValue || titleValue || fallbackMeta;
            }
        };

        const relabelOfficialEditors = () => {
            const editors = Array.from(officialsList?.querySelectorAll('[data-about-official-editor]') || []);
            editors.forEach((editor, index) => {
                syncOfficialCardMeta(editor, index);
            });
        };

        const nextOfficialIndex = () => {
            const indexes = Array.from(officialsList?.querySelectorAll('[data-about-official-editor]') || [])
                .map((editor) => Number(editor.getAttribute('data-about-official-index')))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const createOfficialEditor = (official = {}, index = 0, displayNumber = 1) => {
            if (!(officialsTemplate instanceof HTMLTemplateElement)) {
                return null;
            }

            const markup = officialsTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(displayNumber));

            const shell = document.createElement('div');
            shell.innerHTML = markup.trim();
            const editor = shell.firstElementChild;
            if (!(editor instanceof HTMLElement)) {
                return null;
            }

            const titleInput = editor.querySelector('input[name*="[title]"]');
            const nameInput = editor.querySelector('input[name*="[name]"]');
            const bodyInput = editor.querySelector('textarea[name*="[body]"]');
            const imageInput = editor.querySelector('input[data-about-image-field]');
            const orderInput = editor.querySelector('input[data-about-official-order]');

            if (titleInput instanceof HTMLInputElement) {
                titleInput.value = String(official.title || '');
            }
            if (nameInput instanceof HTMLInputElement) {
                nameInput.value = String(official.name || '');
            }
            if (bodyInput instanceof HTMLTextAreaElement) {
                bodyInput.value = String(official.body || '');
            }
            if (imageInput instanceof HTMLInputElement) {
                imageInput.value = String(official.image || '');
            }
            if (orderInput instanceof HTMLInputElement) {
                orderInput.value = String(official.order || displayNumber);
            }

            return editor;
        };

        const addOfficialEditor = (official = {}, focus = true) => {
            if (!officialsList) {
                return null;
            }

            const index = nextOfficialIndex();
            const displayNumber = officialsList.querySelectorAll('[data-about-official-editor]').length + 1;
            const editor = createOfficialEditor(official, index, displayNumber);
            if (!editor) {
                return null;
            }

            officialsList.appendChild(editor);
            initAboutImageDropzones(editor);
            initAboutCharCounters(editor);
            relabelOfficialEditors();
            bumpOfficialsVersion();
            const activeEditor = setActiveOfficialEditor(index, officialsForm || document);

            if (focus) {
                const firstField = activeEditor?.querySelector('input:not([type="hidden"]), textarea, .rich-editor-surface');
                firstField?.focus();
            }

            return activeEditor || editor;
        };

        const deleteOfficialByIndex = (index) => {
            if (!officialsList) {
                return false;
            }

            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return false;
            }

            const targetEditor = officialsList.querySelector(`[data-about-official-editor][data-about-official-index="${normalizedIndex}"]`);
            if (!targetEditor) {
                return false;
            }

            targetEditor.remove();
            relabelOfficialEditors();
            setActiveOfficialEditor('', officialsForm || document);
            bumpOfficialsVersion();
            return true;
        };

        const setActiveOfficialEditor = (index = '', scope = document) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-official-editor]'));

            if (!editors.length) {
                if (activeOfficialIndexInput) {
                    activeOfficialIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-official-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = targetEditor ? !isActive : false;
            });

            if (activeOfficialIndexInput) {
                activeOfficialIndexInput.value = targetEditor?.getAttribute('data-about-official-index') || '';
            }

            return targetEditor;
        };

        const syncSealEditorMeta = (editor, displayIndex = null) => {
            if (!editor) {
                return;
            }

            const heading = editor.querySelector('[data-about-seal-heading]');
            const meta = editor.querySelector('[data-about-seal-meta]');
            const labelInput = editor.querySelector('[data-about-seal-label]');
            const tagInput = editor.querySelector('[data-about-seal-tag]');
            const sealIdInput = editor.querySelector('[data-about-seal-id]');
            const labelValue = String(labelInput?.value || '').trim();
            const tagValue = String(tagInput?.value || '').trim();
            const fallbackLabel = Number.isFinite(displayIndex) ? `Seal ${Number(displayIndex) + 1}` : 'Seal';

            if (heading) {
                heading.textContent = labelValue || fallbackLabel;
            }

            if (meta) {
                meta.textContent = tagValue || fallbackLabel;
            }

            if (sealIdInput instanceof HTMLInputElement) {
                const current = String(sealIdInput.value || '').trim();
                if (!current) {
                    const raw = (labelValue || fallbackLabel).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    sealIdInput.value = raw || `seal-${Number.isFinite(displayIndex) ? Number(displayIndex) + 1 : 1}`;
                }
            }
        };

        const syncSealLinkNames = (editor) => {
            const sealIndex = editor?.getAttribute('data-about-seal-index') || '';
            if (!editor || sealIndex === '') {
                return;
            }

            const sealIdInput = editor.querySelector('[data-about-seal-id]');
            if (sealIdInput instanceof HTMLInputElement) {
                sealIdInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][id]`;
            }

            const imageInput = editor.querySelector('[data-about-seal-image]');
            if (imageInput instanceof HTMLInputElement) {
                imageInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][image]`;
            }

            const imageFileInput = editor.querySelector('.about-cms-image-dropzone-input');
            if (imageFileInput instanceof HTMLInputElement) {
                imageFileInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][image_file]`;
            }

            const labelInput = editor.querySelector('[data-about-seal-label]');
            if (labelInput instanceof HTMLInputElement) {
                labelInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][label]`;
            }

            const tagInput = editor.querySelector('[data-about-seal-tag]');
            if (tagInput instanceof HTMLInputElement) {
                tagInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][tag]`;
            }

            const highlightsInput = editor.querySelector('[data-about-seal-highlights]');
            if (highlightsInput instanceof HTMLTextAreaElement) {
                highlightsInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][highlights_text]`;
            }

            const infoDescInput = editor.querySelector('[data-about-seal-info-desc] .rich-editor-input');
            if (infoDescInput instanceof HTMLTextAreaElement) {
                infoDescInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][information][description]`;
            }

            const reportsDescInput = editor.querySelector('[data-about-seal-reports-desc] .rich-editor-input');
            if (reportsDescInput instanceof HTMLTextAreaElement) {
                reportsDescInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][reports][description]`;
            }

            const linkItems = Array.from(editor.querySelectorAll('[data-about-seal-link-item]'));
            linkItems.forEach((item, linkIndex) => {
                const labelInput = item.querySelector('[data-about-seal-link-label]');
                const urlInput = item.querySelector('[data-about-seal-link-url]');
                if (labelInput instanceof HTMLInputElement) {
                    labelInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][links][${linkIndex}][label]`;
                }
                if (urlInput instanceof HTMLInputElement) {
                    urlInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][links][${linkIndex}][url]`;
                }
            });
        };

        const relabelSealEditors = () => {
            const editors = Array.from(sealsList?.querySelectorAll('[data-about-seal-editor]') || []);
            editors.forEach((editor, index) => {
                editor.setAttribute('data-about-seal-index', String(index));
                syncSealEditorMeta(editor, index);
                syncSealLinkNames(editor);
            });
        };

        const discardPendingSealDrafts = () => {
            if (!sealsList || pendingSealDraftEditors.size === 0) {
                return;
            }

            let removed = false;
            pendingSealDraftEditors.forEach((editor) => {
                if (editor instanceof HTMLElement && editor.isConnected) {
                    editor.remove();
                    removed = true;
                }
            });
            pendingSealDraftEditors.clear();

            if (!removed) {
                return;
            }

            if (sealsList.querySelector('[data-about-seal-editor]')) {
                relabelSealEditors();
                setActiveSealEditor('', sealsForm || document, false);
                bumpSealsVersion();
                return;
            }

            if (activeSealIndexInput) {
                activeSealIndexInput.value = '';
            }
            bumpSealsVersion();
        };

        const nextSealIndex = () => {
            const indexes = Array.from(sealsList?.querySelectorAll('[data-about-seal-editor]') || [])
                .map((editor) => Number(editor.getAttribute('data-about-seal-index')))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const createSealLinkRow = (link = {}) => {
            if (!(sealLinkTemplate instanceof HTMLTemplateElement)) {
                return null;
            }

            const row = sealLinkTemplate.content.firstElementChild?.cloneNode(true);
            if (!(row instanceof HTMLElement)) {
                return null;
            }

            const labelInput = row.querySelector('[data-about-seal-link-label]');
            const urlInput = row.querySelector('[data-about-seal-link-url]');
            if (labelInput instanceof HTMLInputElement) {
                labelInput.value = String(link.label || '');
            }
            if (urlInput instanceof HTMLInputElement) {
                urlInput.value = String(link.url || '');
            }

            return row;
        };

        const createSealEditor = (seal = {}, index = 0, displayNumber = 1) => {
            if (!(sealsTemplate instanceof HTMLTemplateElement)) {
                return null;
            }

            const markup = sealsTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(displayNumber));
            const shell = document.createElement('div');
            shell.innerHTML = markup.trim();
            const editor = shell.firstElementChild;
            if (!(editor instanceof HTMLElement)) {
                return null;
            }

            const idInput = editor.querySelector('[data-about-seal-id]');
            const labelInput = editor.querySelector('[data-about-seal-label]');
            const tagInput = editor.querySelector('[data-about-seal-tag]');
            const imageInput = editor.querySelector('[data-about-seal-image]');
            const highlightsInput = editor.querySelector('[data-about-seal-highlights]');
            const infoDescInput = editor.querySelector('[data-about-seal-info-desc] .rich-editor-input');
            const reportsDescInput = editor.querySelector('[data-about-seal-reports-desc] .rich-editor-input');

            if (idInput instanceof HTMLInputElement) {
                idInput.value = String(seal.id || '');
            }
            if (labelInput instanceof HTMLInputElement) {
                labelInput.value = String(seal.label || '');
            }
            if (tagInput instanceof HTMLInputElement) {
                tagInput.value = String(seal.tag || '');
            }
            if (imageInput instanceof HTMLInputElement) {
                imageInput.value = String(seal.image || '');
            }
            if (highlightsInput instanceof HTMLTextAreaElement) {
                const highlights = Array.isArray(seal.highlights) ? seal.highlights : [];
                highlightsInput.value = highlights.map((item) => String(item || '').trim()).filter(Boolean).join('\n');
            }
            if (infoDescInput instanceof HTMLTextAreaElement) {
                infoDescInput.value = String(seal?.information?.description || '');
            }
            if (reportsDescInput instanceof HTMLTextAreaElement) {
                reportsDescInput.value = String(seal?.reports?.description || '');
            }

            const linksHost = editor.querySelector('[data-about-seal-links-list]');
            const links = Array.isArray(seal.links) ? seal.links : [];
            if (linksHost) {
                links.forEach((link) => {
                    const row = createSealLinkRow(link);
                    if (row) {
                        linksHost.appendChild(row);
                    }
                });
            }

            return editor;
        };

        const setActiveSealEditor = (index = '', scope = document, collapse = false) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-seal-editor]'));

            if (!editors.length) {
                if (activeSealIndexInput) {
                    activeSealIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-seal-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = collapse && targetEditor ? !isActive : false;
            });

            if (activeSealIndexInput) {
                activeSealIndexInput.value = targetEditor?.getAttribute('data-about-seal-index') || '';
            }

            return targetEditor;
        };

        const addSealEditor = (seal = {}, focus = true, trackAsDraft = false) => {
            if (!sealsList) {
                return null;
            }

            const index = nextSealIndex();
            const displayNumber = sealsList.querySelectorAll('[data-about-seal-editor]').length + 1;
            const editor = createSealEditor(seal, index, displayNumber);
            if (!editor) {
                return null;
            }

            sealsList.appendChild(editor);
            if (trackAsDraft) {
                pendingSealDraftEditors.add(editor);
            }
            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(editor);
            }
            initAboutImageDropzones(editor);
            relabelSealEditors();
            bumpSealsVersion();

            const activeIndex = editor.getAttribute('data-about-seal-index') || String(index);
            const activeEditor = setActiveSealEditor(activeIndex, sealsForm || document, true);
            if (focus) {
                const firstField = activeEditor?.querySelector('input:not([type="hidden"]), textarea, .rich-editor-surface');
                firstField?.focus();
            }

            return activeEditor || editor;
        };

        const deleteSealByIndex = (index) => {
            if (!sealsList) {
                return false;
            }

            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return false;
            }

            const editor = sealsList.querySelector(`[data-about-seal-editor][data-about-seal-index="${normalizedIndex}"]`);
            if (!editor) {
                return false;
            }

            pendingSealDraftEditors.delete(editor);
            editor.remove();
            if (!sealsList.querySelector('[data-about-seal-editor]')) {
                addSealEditor({
                    id: '',
                    label: '',
                    tag: '',
                    image: '',
                    highlights: [],
                    information: { title: 'Informations about the Seal', description: '' },
                    reports: { title: 'Reports and Records', description: '' },
                    links: [],
                }, false);
            } else {
                relabelSealEditors();
                setActiveSealEditor('', sealsForm || document, true);
                bumpSealsVersion();
            }

            return true;
        };

        const submitSealsForm = () => {
            if (!sealsForm) {
                return;
            }

            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(sealsForm);
            }

            if (typeof sealsForm.requestSubmit === 'function') {
                sealsForm.requestSubmit();
                return;
            }

            sealsForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const initSealsEditor = () => {
            if (!sealsForm || !sealsList || sealsForm.dataset.aboutSealsBound === '1') {
                return;
            }

            sealsForm.dataset.aboutSealsBound = '1';
            if (!sealsList.querySelector('[data-about-seal-editor]')) {
                addSealEditor({
                    id: '',
                    label: '',
                    tag: '',
                    image: '',
                    highlights: [],
                    information: { title: 'Informations about the Seal', description: '' },
                    reports: { title: 'Reports and Records', description: '' },
                    links: [],
                }, false);
            } else {
                relabelSealEditors();
                setActiveSealEditor('', sealsForm, false);
            }

            sealsForm.addEventListener('click', (event) => {
                const addSealButton = event.target.closest('[data-about-seal-add-editor]');
                if (addSealButton) {
                    event.preventDefault();
                    addSealEditor({
                        id: '',
                        label: '',
                        tag: '',
                        image: '',
                        highlights: [],
                        information: { title: 'Informations about the Seal', description: '' },
                        reports: { title: 'Reports and Records', description: '' },
                        links: [],
                    }, true, true);
                    return;
                }

                const editorHead = event.target.closest('[data-about-card-editor-head]');
                if (editorHead) {
                    const editor = editorHead.closest('[data-about-seal-editor]');
                    const sealIndex = editor?.getAttribute('data-about-seal-index') || '';
                    setActiveSealEditor(sealIndex, sealsForm, false);
                }

                const addLinkButton = event.target.closest('[data-about-seal-link-add]');
                if (addLinkButton) {
                    event.preventDefault();
                    const editor = addLinkButton.closest('[data-about-seal-editor]');
                    const linksHost = editor?.querySelector('[data-about-seal-links-list]');
                    if (!editor || !linksHost) {
                        return;
                    }

                    const row = createSealLinkRow({});
                    if (row) {
                        linksHost.appendChild(row);
                        syncSealLinkNames(editor);
                        bumpSealsVersion();
                    }
                    return;
                }

                const deleteLinkButton = event.target.closest('[data-about-seal-link-delete]');
                if (deleteLinkButton) {
                    event.preventDefault();
                    const editor = deleteLinkButton.closest('[data-about-seal-editor]');
                    deleteLinkButton.closest('[data-about-seal-link-item]')?.remove();
                    if (editor) {
                        syncSealLinkNames(editor);
                    }
                    bumpSealsVersion();
                }
            });

            sealsForm.addEventListener('input', (event) => {
                const editor = event.target.closest('[data-about-seal-editor]');
                if (!editor) {
                    return;
                }

                syncSealEditorMeta(editor);
                syncSealLinkNames(editor);
                bumpSealsVersion();
            });

            sealsForm.addEventListener('change', (event) => {
                if (event.target.closest('[data-about-seal-editor]')) {
                    bumpSealsVersion();
                }
            });

            sealsForm.addEventListener('submit', () => {
                relabelSealEditors();
                if (typeof window.syncRichTextEditors === 'function') {
                    window.syncRichTextEditors(sealsForm);
                }
                pendingSealDraftEditors.clear();
                bumpSealsVersion();
            }, true);
        };

        const submitHistoryForm = () => {
            if (!historyForm) {
                return;
            }

            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(historyForm);
            }

            if (typeof historyForm.requestSubmit === 'function') {
                historyForm.requestSubmit();
                return;
            }

            historyForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const initAboutImageDropzones = (scope = document) => {
            scope.querySelectorAll('.about-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.aboutDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-about-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-about-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-about-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-about-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-about-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-about-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-about-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-about-clear-image-for="${input.id}"]`);
                const editButton = scope.querySelector(`[data-about-edit-image-for="${input.id}"]`)
                    || document.querySelector(`[data-about-edit-image-for="${input.id}"]`);
                const imageField = input.dataset.aboutImageFieldId
                    ? document.getElementById(input.dataset.aboutImageFieldId)
                    : (input.closest('[data-about-contents-editor]')?.querySelector('[data-about-image-field]') || null);
                const syncImageField = input.dataset.aboutSyncImageFieldId
                    ? document.getElementById(input.dataset.aboutSyncImageFieldId)
                    : null;

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.aboutDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.aboutDefaultSrc || '';
                const syncPreviewPlaceholderState = (isPlaceholder) => {
                    if (!previewEl) {
                        return;
                    }

                    previewEl.classList.toggle('about-cms-image-dropzone-preview--profile-placeholder', isPlaceholder);
                };

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;
                    if (typeof editButton !== 'undefined' && editButton) editButton.hidden = !hasImage;
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
                if (typeof editButton !== 'undefined' && editButton) {
                    editButton.addEventListener('click', async (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        let file = input.files && input.files[0];
                        if (!file && previewEl && previewEl.src && previewEl.src !== defaultSrc) {
                            try {
                                const dbPath = typeof imageField !== 'undefined' && imageField ? imageField.value : null;
                                if (dbPath) {
                                    const res = await fetch(`/cms/proxy-image?path=${encodeURIComponent(dbPath)}`);
                                    if (!res.ok) throw new Error("Proxy fetch failed");
                                    const blob = await res.blob();
                                    const ext = dbPath.split('.').pop().split(/#|\?/)[0] || 'jpg';
                                    file = new File([blob], `image.${ext}`, { type: blob.type });
                                } else {
                                    throw new Error("No db path available");
                                }
                            } catch(err) {
                                console.warn("Proxy failed, using canvas fallback", err);
                                try {
                                    const canvas = document.createElement('canvas');
                                    canvas.width = previewEl.naturalWidth || previewEl.width || 800;
                                    canvas.height = previewEl.naturalHeight || previewEl.height || 600;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(previewEl, 0, 0, canvas.width, canvas.height);
                                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 1.0));
                                    if (blob) file = new File([blob], 'image.jpg', { type: 'image/jpeg' });
                                } catch (canvasErr) {
                                    console.error("Canvas fallback also failed", canvasErr);
                                }
                            }
                        }
                        
                        if (file && window.CmsImageEditor) {
                            const editedFile = await window.CmsImageEditor.editFile(file, {
                                input,
                                previewElement: previewEl,
                            });
                            
                            if (editedFile && editedFile !== file) {
                                window.CmsImageEditor.setInputFile(input, editedFile);
                                if (typeof applyFile === 'function') {
                                    applyFile(editedFile);
                                }
                            }
                        }
                    });
                }


                const applyFile = (file) => {
                    if (!file) {
                        syncRemoveState();
                        return;
                    }

                    fileNameEl.textContent = `Selected: ${file.name}`;

                    if (previewEl) {
                        previewEl.src = URL.createObjectURL(file);
                    }

                    syncPreviewPlaceholderState(false);
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
                    if (event.target.closest('[data-about-clear-image-for]')) {
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
                    if (syncImageField) {
                        syncImageField.value = '';
                    }
                    if (previewEl && defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    syncPreviewPlaceholderState(true);
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncPreviewPlaceholderState((imageField?.value || '').trim() === '');
                syncRemoveState();
            });
        };

        const initAboutCharCounters = (scope = document) => {
            scope.querySelectorAll('[data-about-char-limit]').forEach((field) => {
                if (field.dataset.aboutCharCounterBound === '1') {
                    return;
                }

                const input = field.querySelector('[data-about-char-input]');
                const counter = field.querySelector('[data-about-char-counter]');
                const limit = Number(field.getAttribute('data-about-char-limit') || input?.getAttribute('maxlength') || 0);

                if (!input || !counter || limit <= 0) {
                    return;
                }

                field.dataset.aboutCharCounterBound = '1';
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

        const deleteContentsCardBySlug = (slug) => {
            if (!slug) {
                return false;
            }

            const targetEditor = document.querySelector(`[data-about-contents-editor][data-about-contents-slug="${slug}"]`);
            const visibilityInput = targetEditor?.querySelector('[data-about-contents-visible]');
            if (!targetEditor || !visibilityInput) {
                return false;
            }

            visibilityInput.value = '0';
            targetEditor.classList.add('is-disabled');
            bumpContentsVersion();
            setActiveContentsEditor();

            return true;
        };

        const confirmDeleteContentsCard = async (slug, label) => {
            if (!slug) {
                return;
            }

            let confirmed = false;
            const promptLabel = label || slug;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Card',
                    message: `Do you want to delete "${promptLabel}" from the About contents list?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from the About contents list?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteContentsCardBySlug(slug);
            if (!deleted) {
                return;
            }

            submitContentsForm();
        };

        const confirmDeletePlanPriority = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Priority ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Priority Card',
                    message: `Do you want to delete "${promptLabel}" from the Strategic Development Plan?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from the Strategic Development Plan?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deletePlanPriorityByIndex(normalizedIndex);
            if (!deleted) {
                return;
            }

            if (options.submit !== false) {
                submitPlanPrioritiesForm();
            }
        };

        const confirmDeleteOfficialCard = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Official ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Official Card',
                    message: `Do you want to delete "${promptLabel}" from Campus Officials?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from Campus Officials?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteOfficialByIndex(normalizedIndex);
            if (!deleted) {
                return;
            }

            if (options.submit !== false) {
                submitOfficialsForm();
            }
        };

        const confirmDeleteSealCard = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Seal ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Seal',
                    message: `Do you want to delete "${promptLabel}" from Logo and Symbols?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from Logo and Symbols?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteSealByIndex(normalizedIndex);
            if (!deleted) {
                return;
            }

            if (options.submit !== false) {
                submitSealsForm();
            }
        };

        document.querySelectorAll('form.{{ $formClass }}').forEach((form) => {
            if (form.dataset.aboutRichTextSubmitBound === '1') {
                return;
            }

            form.dataset.aboutRichTextSubmitBound = '1';
            if (form.matches('[data-about-intro-form]')) {
                form.addEventListener('submit', () => {
                    if (typeof window.syncRichTextEditors === 'function') {
                        window.syncRichTextEditors(form);
                    }
                    bumpIntroVersion();
                }, true);
            }

            if (form.matches('[data-about-philosophy-form]')) {
                form.addEventListener('submit', () => {
                    if (typeof window.syncRichTextEditors === 'function') {
                        window.syncRichTextEditors(form);
                    }
                    bumpPhilosophyVersion();
                }, true);
            }

            if (form.matches('[data-about-history-form]')) {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('[data-about-history-date-group]').forEach(syncAboutHistoryDateGroup);
                    if (typeof window.syncRichTextEditors === 'function') {
                        window.syncRichTextEditors(form);
                    }
                    bumpHistoryVersion();
                }, true);
            }

            form.addEventListener('submit', () => {
                if (typeof window.syncRichTextEditors === 'function') {
                    window.syncRichTextEditors(form);
                }
            });
        });

        initOfficialsEditor();
        initSealsEditor();
        initPlanPrioritiesEditor();
        initStrategicGoalsEditor();

        const setActiveServiceEditor = (index = '', scope = document) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-service-editor]'));

            if (!editors.length) {
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-service-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = targetEditor ? !isActive : false;
            });

            return targetEditor;
        };

        const submitCitizensCharterForm = () => {
            const panel = document.querySelector('[data-about-editor-panel="citizens-charter"]');
            const form = panel?.querySelector('form');
            if (form) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        };

        const confirmDeleteServiceCard = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Office ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Office Card',
                    message: `Do you want to delete "${promptLabel}" from Citizen's Charter?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from Citizen's Charter?`);
            }

            if (!confirmed) {
                return;
            }

            const servicesList = document.querySelector('[data-about-services-list]');
            const targetEditor = servicesList?.querySelector(`[data-about-service-editor][data-about-service-index="${normalizedIndex}"]`);
            if (targetEditor) {
                targetEditor.remove();
                relabelServiceEditors();
                bumpServicesVersion();

                if (options.submit !== false) {
                    submitCitizensCharterForm();
                }
            }
        };

        const relabelServiceEditors = () => {
            const servicesList = document.querySelector('[data-about-services-list]');
            if (!servicesList) return;
            const editors = servicesList.querySelectorAll('[data-about-service-editor]');
            editors.forEach((editor, newIndex) => {
                editor.setAttribute('data-about-service-index', newIndex);
                const heading = editor.querySelector('[data-about-service-heading]');
                if (heading) {
                    heading.textContent = `Office ${newIndex + 1}`;
                }
                editor.querySelectorAll('input, textarea').forEach((input) => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[services\]\[\d+\]/, `[services][${newIndex}]`));
                    }
                });
            });
        };

        const bumpServicesVersion = () => {
            const servicesForm = document.querySelector('[data-about-editor-panel="citizens-charter"] form');
            const servicesVersionInput = servicesForm?.querySelector('[data-about-services-version]');
            if (servicesVersionInput) {
                servicesVersionInput.value = String(Date.now());
            }
            const frame = document.querySelector('[data-about-preview-frame]');
            if (frame && typeof queueAboutPreviewSettledSync === 'function') {
                queueAboutPreviewSettledSync(frame);
            }
        };

        const addServiceEditor = (data = {}, focus = false) => {
            const servicesList = document.querySelector('[data-about-services-list]');
            const servicesTemplate = document.querySelector('[data-about-service-template]');
            if (!servicesList || !servicesTemplate) return null;

            const newIndex = servicesList.querySelectorAll('[data-about-service-editor]').length;
            const html = document.createElement('div');
            html.appendChild(servicesTemplate.content.cloneNode(true));
            html.innerHTML = html.innerHTML.replace(/__INDEX__/g, newIndex).replace(/__NUMBER__/g, newIndex + 1);
            const editor = html.firstElementChild;
            servicesList.appendChild(editor);

            if (data.title) {
                const titleInput = editor.querySelector('input[name*="[title]"]');
                if (titleInput) titleInput.value = data.title;
            }
            if (data.description) {
                const descInput = editor.querySelector('textarea[name*="[description]"]');
                if (descInput) descInput.value = data.description;
            }
            if (data.link) {
                const linkInput = editor.querySelector('input[name*="[link]"]');
                if (linkInput) linkInput.value = data.link;
            }

            if (focus) {
                setActiveServiceEditor(newIndex, servicesList.closest('[data-about-editor-panel]'));
            }

            return editor;
        };

        const initServicesEditor = () => {
            const servicesList = document.querySelector('[data-about-services-list]');
            const servicesTemplate = document.querySelector('[data-about-service-template]');
            const addBtn = document.querySelector('[data-about-service-add]');
            if (servicesList && servicesTemplate && addBtn) {
                addBtn.addEventListener('click', () => {
                    addServiceEditor({}, false);
                });
            }
        };
        initServicesEditor();

        const frame = document.querySelector('[data-about-preview-frame]');
        if (frame) {
            frame.addEventListener('load', () => {
                bindAboutPreviewDocument(frame);
                queueAboutPreviewSettledSync(frame);
                scheduleFitAboutPreviews();
            });
        }

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAboutPreviews();
            });

            document.querySelectorAll('.about-cms-preview-frame-shell').forEach((shell) => {
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
                scheduleFitAboutPreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('resize', scheduleFitAboutPreviews);
        window.addEventListener('pageshow', scheduleFitAboutPreviews);
        window.addEventListener('load', scheduleFitAboutPreviews);
        window.addEventListener('cms:tab-activated', (event) => {
            const tabPanel = event.detail?.panel;
            const frame = document.querySelector('[data-about-preview-frame]');
            const sessionId = Number(event.detail?.sessionId || 0) || undefined;

            if (!frame || (tabPanel && !tabPanel.contains(frame))) {
                return;
            }

            loadAboutPreviewPage(currentAboutPreviewRoute || 'overview', { forceReload: true, sessionId });
            window.setTimeout(scheduleFitAboutPreviews, 40);
            window.setTimeout(scheduleFitAboutPreviews, 180);
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAboutPreviews();
            }
        });

        window.refreshAboutCmsPreview = (scope) => {
            const frame = scope
                ? scope.querySelector('[data-about-preview-frame]')
                : document.querySelector('[data-about-preview-frame]');

            if (!frame) {
                return;
            }

            loadAboutPreviewPage(currentAboutPreviewRoute || 'overview', { forceReload: true });
        };

        const initialAboutPreviewRoute = getStoredAboutPreviewRoute();
        loadAboutPreviewPage(initialAboutPreviewRoute || 'overview');
        scheduleFitAboutPreviews();
        initAboutImageDropzones(document);
        initAboutCharCounters(document);
        initAboutHistoryDateFields(document);
        if (typeof window.initializeRichTextEditors === 'function') {
            window.initializeRichTextEditors(document);
        }
        bindAboutIntroDirtyTracking();
        bindPhilosophyDirtyTracking();
        bindAboutContentsDirtyTracking();
        bindAboutHistoryDirtyTracking();
        bindCoreValuesDirtyTracking();
        bindOfficialsDirtyTracking();
        bindSealsDirtyTracking();
        setActiveHistoryEditor();
        window.__aboutCmsPreviewEditorReady = true;
    })();
</script>
<style>
.about-cms-image-dropzone--square .about-cms-image-dropzone-preview-column {
    flex: 0 0 200px !important;
    max-width: 200px !important;
}
.about-cms-image-dropzone--square .about-cms-image-dropzone-media {
    width: 100% !important;
    aspect-ratio: 1 / 1 !important;
    height: auto !important;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent !important;
}
.about-cms-image-dropzone--square .about-cms-image-dropzone-preview {
    object-fit: contain !important;
    max-height: 100%;
}
</style>
