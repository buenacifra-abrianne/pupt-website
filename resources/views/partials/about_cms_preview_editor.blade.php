@php
    $aboutDefaults = \App\Support\AboutCmsContent::defaults();
    $aboutEditorData = \App\Support\AboutCmsContent::fromInput($aboutEditorData ?? [], null);
    $aboutPreviewData = \App\Support\AboutCmsContent::fromInput($aboutPreviewData ?? $aboutEditorData, null);
    $aboutSections = $aboutEditorData['sections'] ?? [];
    $aboutPreviewSections = $aboutPreviewData['sections'] ?? [];
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

    $aboutPreviewPages = [
        'overview' => view('public.about', [
            'aboutCms' => $aboutPreviewData,
            'cmsPreview' => true,
        ])->render(),
    ];

    foreach ($aboutPreviewSections as $slug => $sectionData) {
        $aboutPreviewPages[$slug] = view('public.about', [
            'aboutCms' => $aboutPreviewData,
            'selectedSection' => $sectionData,
            'cmsPreview' => true,
        ])->render();
    }
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
            <span class="about-cms-side-kicker">About Section</span>
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

                    <div class="form-group">
                        <label>Upload Header Image</label>
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
                        <label>Overview Hero Title</label>
                        <input type="text" name="about[overview][hero_title_default]" maxlength="255" value="{{ $overviewEditor['hero_title_default'] ?? '' }}">
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
                                        <input type="text" name="about[sections][history][timeline][{{ $index }}][title]" maxlength="255" value="{{ $milestone['title'] ?? '' }}">
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
                        <textarea name="about[sections][vision-and-mission][vision]" rows="4">{{ $visionEditor['vision'] ?? '' }}</textarea>
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
            @endphp
            <section class="about-cms-editor-panel" data-about-editor-panel="logo-and-symbols" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="logo-and-symbols">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

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
                        <textarea name="about[sections][logo-and-symbols][lead]" rows="5">{{ $logoEditor['lead'] ?? '' }}</textarea>
                    </div>

                    @foreach($logoEditor['identity_marks'] ?? [] as $index => $identityMark)
                        <article class="about-cms-card-editor">
                            <div class="about-cms-form-grid">
                                <div class="form-group">
                                    <label>Identity Card Title</label>
                                    <input type="text" name="about[sections][logo-and-symbols][identity_marks][{{ $index }}][title]" maxlength="255" value="{{ $identityMark['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Identity Card Body</label>
                                    <textarea name="about[sections][logo-and-symbols][identity_marks][{{ $index }}][body]" rows="4">{{ $identityMark['body'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div class="form-group">
                        <label>Symbolism Points</label>
                        <textarea name="about[sections][logo-and-symbols][symbol_points_text]" rows="6">{{ implode("\n", $logoEditor['symbol_points'] ?? []) }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Logo & Symbols') }}</button>
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
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-card-stack">
                        @foreach($officialsEditor['official_groups'] ?? [] as $index => $officialGroup)
                            @php
                                $officialImageInputId = $idPrefix.'-about-official-image-file-'.$index;
                                $officialImageFieldId = $idPrefix.'-about-official-image-'.$index;
                                $officialImageValue = (string) ($officialGroup['image'] ?? '');
                                $officialImagePreview = \App\Support\AboutCmsContent::resolveImagePath($officialImageValue !== '' ? $officialImageValue : null, 'assets/static_img/temporary_profile.png');
                            @endphp
                            <article class="about-cms-card-editor" data-about-official-editor data-about-official-index="{{ $index }}">
                                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                                    <h4>Official Card {{ $loop->iteration }}</h4>
                                    <span>{{ $officialGroup['name'] ?? ($officialGroup['title'] ?? 'Campus official') }}</span>
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
                                    <div class="form-group">
                                        <label>Role</label>
                                        <div class="about-cms-locked-field" aria-label="Role is locked">
                                            <span class="about-cms-locked-field-icon" aria-hidden="true">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input
                                                type="text"
                                                name="about[sections][campus-officials][official_groups][{{ $index }}][title]"
                                                maxlength="255"
                                                value="{{ $officialGroup['title'] ?? '' }}"
                                                readonly
                                                aria-readonly="true"
                                            >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="about[sections][campus-officials][official_groups][{{ $index }}][name]" maxlength="255" value="{{ $officialGroup['name'] ?? '' }}">
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

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Campus Officials') }}</button>
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

<script type="application/json" data-about-preview-pages>
{!! json_encode($aboutPreviewPages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .about-cms-workspace {
        --about-preview-width: 1520px;
        --about-preview-height: 1800px;
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
        padding: 16px;
    }

    .about-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .about-cms-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(1080px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        margin: 0;
        overflow-x: hidden;
        overflow-y: auto;
        border-radius: 24px;
        background: #fffdfc;
        box-shadow: 0 28px 80px rgba(25, 16, 12, 0.28);
    }

    .about-cms-modal-close {
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

    .about-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .about-cms-modal-panels {
        padding: 22px 24px 24px;
        max-width: 100%;
        overflow-x: hidden;
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

    .about-cms-history-date-fields label {
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

    .about-cms-history-meta-grid > .form-group:not([data-about-history-date-group]) > input {
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

    .about-cms-image-dropzone-shell {
        width: 100%;
        margin: 0 auto;
    }

    .about-cms-image-dropzone {
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

    .about-cms-image-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .about-cms-image-dropzone-preview-column {
        display: flex;
        min-width: 0;
        min-height: 180px;
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
        min-height: 180px;
        object-fit: cover;
        border-radius: 18px;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
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

    .about-cms-image-dropzone-icon {
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
        min-height: 40px;
        padding: 0 18px;
        border-radius: 999px;
        background: #fff8f1;
        color: #1b1714;
        font-size: 0.9rem;
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

    .about-cms-image-dropzone-remove {
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
        .about-cms-image-dropzone-remove {
            top: 12px;
            right: 12px;
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
        display: grid;
        gap: 8px;
    }

    .about-cms-char-counter {
        justify-self: end;
        color: #8a7a73;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
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
        align-items: center !important;
    }

    .about-cms-modal.is-card-focus .about-cms-modal-dialog {
        width: min(860px, calc(100vw - 24px));
        max-width: min(860px, calc(100vw - 24px));
        overflow-x: visible;
        overflow-y: auto;
        border-radius: 28px;
        background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
        box-shadow: 0 30px 70px rgba(45, 8, 5, 0.2);
    }

    .about-cms-modal.is-official-card-focus .about-cms-modal-dialog {
        width: min(720px, calc(100vw - 24px));
        max-width: min(720px, calc(100vw - 24px));
    }

    .about-cms-modal.is-card-focus .about-cms-modal-panels {
        display: grid;
        gap: 16px;
        padding: 20px;
        overflow: visible;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf6 0%, #fffdfc 100%);
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

    .about-cms-editor-panel.is-card-focus .about-cms-card-stack {
        gap: 0;
        overflow: visible;
    }

    .about-cms-editor-panel.is-card-focus [data-about-card-panel-meta],
    .about-cms-editor-panel.is-card-focus [data-about-card-editor-head] {
        display: none;
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active {
        padding: 20px;
        max-width: 100%;
        width: 100%;
        margin: 0 auto;
        overflow: visible;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow:
            0 16px 34px rgba(92, 12, 6, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .about-cms-editor-panel.is-card-focus .about-cms-card-editor.is-active .form-group + .form-group {
        margin-top: 14px;
    }

    .about-cms-modal.is-card-focus .about-cms-modal-footer {
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
        padding-top: 6px;
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
        top: 12px;
        right: 12px;
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

        .about-cms-history-date-fields {
            grid-template-columns: 1fr;
        }

        .about-cms-modal-dialog {
            width: min(100vw - 20px, 1080px);
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

        const ABOUT_PREVIEW_MIN_LOADING_MS = 1500;
        let aboutPreviewFitFrame = null;
        const ABOUT_PREVIEW_STORAGE_KEY = `cms:about-preview-route:${window.location.pathname}`;
        const ABOUT_PREVIEW_LEGACY_STORAGE_KEY = '{{ $idPrefix }}-active-about-preview-page';
        let currentAboutPreviewRoute = 'overview';

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

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--about-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
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

        function loadAboutPreviewPage(routeKey, options = {}) {
            const frame = document.querySelector('[data-about-preview-frame]');
            const payloads = getAboutPreviewPayloads();
            const targetKey = routeKey && payloads[routeKey] ? routeKey : 'overview';
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
            const previewHtml = payloads[targetKey] || payloads.overview || '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>';
            if (typeof window.applyCmsPreviewFrameContent === 'function') {
                window.applyCmsPreviewFrameContent(frame, previewHtml);
            } else {
                frame.srcdoc = previewHtml;
            }

            document.querySelectorAll('[data-about-preview-page]').forEach((btn) => {
                btn.classList.toggle('is-active', btn.getAttribute('data-about-preview-page') === targetKey);
            });
        }

        function resolveAboutEditorRoute(sectionKey, providedRoute = '') {
            const route = String(providedRoute || '').trim();
            if (route !== '') {
                return route;
            }

            if (sectionKey === 'hero' || sectionKey === 'intro' || sectionKey === 'contents') {
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

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');
            modal.classList.remove('is-official-card-focus');

            modal.querySelectorAll('[data-about-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-about-editor-panel') === sectionKey;
                const isContentsCardFocus = sectionKey === 'contents' && String(options.slug || '').trim() !== '';
                const isHistoryCardFocus = sectionKey === 'history' && String(options.historyIndex ?? '').trim() !== '';
                const isStrategicGoalFocus = sectionKey === 'strategic-goals' && String(options.strategicGoalIndex ?? '').trim() !== '';
                const isPlanPriorityFocus = sectionKey === 'strategic-development-plan' && String(options.planPriorityIndex ?? '').trim() !== '';
                const isOfficialCardFocus = sectionKey === 'campus-officials' && String(options.officialIndex ?? '').trim() !== '';
                const isCardFocus = isContentsCardFocus || isHistoryCardFocus || isStrategicGoalFocus || isPlanPriorityFocus || isOfficialCardFocus;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    modal.classList.toggle('is-official-card-focus', sectionKey === 'campus-officials' && isCardFocus);
                    if (title) {
                        title.textContent = label || 'Edit about section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the About page preview.';
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
                    } else if (sectionKey === 'campus-officials') {
                        focusScope = setActiveOfficialEditor(options.officialIndex ?? '', panel) || panel;
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

            modal.hidden = true;
            modal.classList.remove('is-card-focus');
            modal.classList.remove('is-official-card-focus');
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
        }

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-about-edit') {
                openAboutEditor(data.section || '', data.label || 'Edit about section', {
                    route: data.route || '',
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

            if (data.type === 'cms-about-official-card-edit') {
                openAboutEditor('campus-officials', data.label ? `Edit ${data.label}` : 'Edit campus official', {
                    officialIndex: data.index || '',
                    route: data.route || 'campus-officials',
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
        const historyForm = document.querySelector('[data-about-history-form]');
        const historyVersionInput = document.querySelector('[data-about-history-version]');
        const activeHistoryIndexInput = document.querySelector('[data-about-active-history-index]');
        const officialsForm = document.querySelector('[data-about-editor-panel="campus-officials"] form');
        const officialsVersionInput = officialsForm?.querySelector('[data-about-officials-version]') || null;
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

        const setActiveOfficialEditor = (index = '', scope = document) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-official-editor]'));

            if (!editors.length) {
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

            return targetEditor;
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

                    syncPreviewPlaceholderState(false);
                    syncRemoveState();
                };

                input.addEventListener('change', () => {
                    applyFile(input.files && input.files[0] ? input.files[0] : null);
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

                label.addEventListener('drop', (event) => {
                    event.preventDefault();
                    label.classList.remove('dragover');

                    const file = event.dataTransfer?.files?.[0] ?? null;
                    if (!file) {
                        return;
                    }

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                    applyFile(file);
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

        initPlanPrioritiesEditor();
        initStrategicGoalsEditor();

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
        bindAboutContentsDirtyTracking();
        bindAboutHistoryDirtyTracking();
        bindCoreValuesDirtyTracking();
        bindOfficialsDirtyTracking();
        setActiveHistoryEditor();
        window.__aboutCmsPreviewEditorReady = true;
    })();
</script>
