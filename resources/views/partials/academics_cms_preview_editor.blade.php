@php
    $academicsDefaults = \App\Support\AcademicsCmsContent::defaults();
    $academicsEditorData = \App\Support\AcademicsCmsContent::fromInput($academicsEditorData ?? [], null);
    $heroEditor = $academicsEditorData['hero'] ?? $academicsDefaults['hero'];
    $contentsEditor = $academicsEditorData['contents'] ?? $academicsDefaults['contents'];
    $introEditor = $academicsEditorData['intro'] ?? $academicsDefaults['intro'];
    $featuresEditor = $academicsEditorData['features'] ?? $academicsDefaults['features'];
    $formClass = $academicsEditorFormClass ?? 'cms-save-form';
    $submitRoute = $academicsEditorSubmitRoute;
    $submitMode = $academicsEditorSubmitMode ?? 'save';
    $requestId = (int) ($academicsEditorRequestId ?? 0);
    $status = strtolower((string) ($academicsEditorStatus ?? ''));
    $idPrefix = trim((string) ($academicsEditorIdPrefix ?? 'academics-editor'));
    $academicsPreviewNav = [
        'overview' => 'Overview',
        'degree-programs' => 'Degree Programs',
        'diploma-programs' => 'Diploma Programs',
        'pup-iapply' => 'PUP iApply',
        'university-calendar' => 'University Calendar',
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

<div class="academics-cms-workspace">
    <div class="academics-cms-preview-shell">
        <div class="academics-cms-preview-nav" role="tablist" aria-label="Academics preview sections">
            @foreach($academicsPreviewNav as $routeKey => $routeLabel)
                <button
                    type="button"
                    class="academics-cms-preview-nav-btn{{ $routeKey === 'overview' ? ' is-active' : '' }}"
                    data-academics-preview-page="{{ $routeKey }}"
                    role="tab"
                    aria-selected="{{ $routeKey === 'overview' ? 'true' : 'false' }}"
                >
                    {{ $routeLabel }}
                </button>
            @endforeach
        </div>
        <div class="academics-cms-preview-frame-shell">
            <div class="academics-cms-preview-stage">
                <div class="academics-cms-preview-canvas">
                    <iframe
                        title="Academics page preview"
                        class="academics-cms-preview-frame"
                        data-academics-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="academics-cms-modal" data-academics-editor-modal hidden>
    <div class="academics-cms-modal-backdrop" data-close-academics-editor></div>

    <div class="academics-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="academics-cms-modal-close" data-close-academics-editor aria-label="Close editor">&times;</button>

        <div class="academics-cms-modal-header">
            <span class="academics-cms-side-kicker">Academics Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit academics section</h3>
            <p data-academics-editor-description>Select a highlighted section from the preview to edit it.</p>
        </div>

        <div class="academics-cms-modal-panels">
            @php
                $academicsHeroInputId = $idPrefix.'-academics-hero-image';
                $academicsHeroFieldId = $idPrefix.'-academics-hero-image-field';
                $academicsHeroPreview = \App\Support\NewsImage::url($heroEditor['image'] ?? null, 'assets/static_img/about_header_image.png');
            @endphp
            <section class="academics-cms-editor-panel" data-academics-editor-panel="hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="academics">
                    <input type="hidden" name="section_key" value="hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $academicsHeroFieldId }}" name="academics[hero][image]" value="{{ $heroEditor['image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Hero Image</label>
                        <div class="academics-cms-image-dropzone-shell">
                            <div class="academics-cms-image-dropzone cms-image-dropzone-hero" data-academics-dropzone-for="{{ $academicsHeroInputId }}" role="button" tabindex="0" aria-label="Upload hero image">
                                <span class="academics-cms-image-dropzone-preview-column">
                                    <span class="academics-cms-image-dropzone-media">
                                        <img
                                            src="{{ $academicsHeroPreview }}"
                                            alt="Academics hero image preview"
                                            class="academics-cms-image-dropzone-preview"
                                            data-academics-preview-for="{{ $academicsHeroInputId }}"
                                            data-academics-default-src="{{ asset('assets/static_img/about_header_image.png') }}"
                                        >
                                        <button type="button" class="academics-cms-image-dropzone-remove" data-academics-clear-image-for="{{ $academicsHeroInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="academics-cms-image-dropzone-label">Hero Image</span>
                                </span>
                                <span class="academics-cms-image-dropzone-upload">
                                    <span class="academics-cms-image-dropzone-icon">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="academics-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="academics-cms-image-dropzone-upload-copy">Your hero image preview updates instantly while you edit this section.</span>
                                    <span class="academics-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="academics-cms-image-dropzone-file" data-academics-file-name-for="{{ $academicsHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    <input
                                        id="{{ $academicsHeroInputId }}"
                                        class="academics-cms-image-dropzone-input"
                                        type="file"
                                        name="academics[hero][image_file]"
                                        accept="image/*"
                                        data-academics-image-field-id="{{ $academicsHeroFieldId }}"
                                    >
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Hero Title</label>
                        <input type="text" name="academics[hero][title]" maxlength="255" value="{{ $heroEditor['title'] ?? '' }}">
                    </div>

                    <div class="academics-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Academics Hero') }}</button>
                    </div>
                </form>
            </section>

            <section class="academics-cms-editor-panel" data-academics-editor-panel="contents" hidden>
                <div data-academics-contents-section-shell>
                    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                        @csrf
                        <input type="hidden" name="tab_key" value="academics">
                        <input type="hidden" name="section_key" value="contents">
                        @if($requestId > 0)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        <div class="form-group" data-academics-card-panel-meta>
                            <label>Section Tag</label>
                            <input type="text" name="academics[contents][tag]" maxlength="80" value="{{ $contentsEditor['tag'] ?? '' }}">
                        </div>

                        @foreach(($contentsEditor['items'] ?? []) as $index => $item)
                            <input type="hidden" name="academics[contents][items][{{ $index }}][route]" value="{{ $item['route'] ?? '' }}">
                            <input type="hidden" name="academics[contents][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}">
                            <input type="hidden" name="academics[contents][items][{{ $index }}][label]" value="{{ $item['label'] ?? '' }}">
                            <input type="hidden" name="academics[contents][items][{{ $index }}][summary]" value="{{ $item['summary'] ?? '' }}">
                        @endforeach

                        <div class="academics-cms-modal-footer">
                            <button type="submit" class="btn btn-primary">{{ $submitLabel('Academics Contents') }}</button>
                        </div>
                    </form>
                </div>

                <div data-academics-contents-card-shell>
                    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-academics-contents-form data-academics-card-form="contents">
                        @csrf
                        <input type="hidden" name="tab_key" value="academics">
                        <input type="hidden" name="section_key" value="contents">
                        <input type="hidden" name="academics_contents_version" value="0" data-academics-contents-version data-academics-card-version>
                        <input type="hidden" name="academics_active_contents_index" value="" data-academics-active-contents-index data-academics-card-active-index>
                        <input type="hidden" name="academics[contents][tag]" value="{{ $contentsEditor['tag'] ?? '' }}">
                        @if($requestId > 0)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        <div class="academics-cms-card-stack" data-academics-contents-stack data-academics-card-stack="contents">
                            @foreach(($contentsEditor['items'] ?? []) as $index => $item)
                                @php
                                    $itemInputId = $idPrefix.'-academics-card-image-'.$index;
                                    $itemPreview = \App\Support\NewsImage::url($item['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                @endphp
                                <article class="academics-cms-card-editor" data-academics-contents-editor data-academics-contents-index="{{ $index }}" data-academics-page-card-editor="contents" data-academics-page-card-index="{{ $index }}">
                                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                                        <h4>Contents Card {{ $loop->iteration }}</h4>
                                        <span>{{ $item['route'] ?? '' }}</span>
                                    </div>

                                    <input type="hidden" name="academics[contents][items][{{ $index }}][route]" value="{{ $item['route'] ?? '' }}">
                                    <input type="hidden" name="academics[contents][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}" data-academics-image-field>

                                    <div class="form-group">
                                        <label>Upload Card Image</label>
                                        <div class="academics-cms-image-dropzone-shell">
                                            <div class="academics-cms-image-dropzone" data-academics-dropzone-for="{{ $itemInputId }}" role="button" tabindex="0" aria-label="Upload card image">
                                                <span class="academics-cms-image-dropzone-preview-column">
                                                    <span class="academics-cms-image-dropzone-media">
                                                        <img
                                                            src="{{ $itemPreview }}"
                                                            alt="{{ ($item['label'] ?? '') !== '' ? $item['label'] : 'Contents card preview' }}"
                                                            class="academics-cms-image-dropzone-preview"
                                                            data-academics-preview-for="{{ $itemInputId }}"
                                                            data-academics-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                        >
                                                        <button type="button" class="academics-cms-image-dropzone-remove" data-academics-clear-image-for="{{ $itemInputId }}" aria-label="Delete image" title="Delete image">
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
                                                    <span class="academics-cms-image-dropzone-file" data-academics-file-name-for="{{ $itemInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                    <input
                                                        id="{{ $itemInputId }}"
                                                        class="academics-cms-image-dropzone-input"
                                                        type="file"
                                                        name="academics[contents][items][{{ $index }}][image_file]"
                                                        accept="image/*"
                                                    >
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="academics[contents][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <div class="academics-cms-textarea-field" data-academics-char-limit="100">
                                            <textarea
                                                name="academics[contents][items][{{ $index }}][summary]"
                                                rows="4"
                                                maxlength="100"
                                                data-academics-char-input
                                            >{{ $item['summary'] ?? '' }}</textarea>
                                            <div class="academics-cms-char-counter" data-academics-char-counter aria-live="polite">0/100</div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="academics-cms-modal-footer">
                            <button type="submit" class="btn btn-primary">{{ $submitLabel('Academics Content Card') }}</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="academics-cms-editor-panel" data-academics-editor-panel="intro" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="academics">
                    <input type="hidden" name="section_key" value="intro">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Intro Copy</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'academics[intro][body]',
                            'value' => $introEditor['body'] ?? '',
                            'placeholder' => 'Write the main academics introduction...',
                        ])
                    </div>

                    <div class="academics-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Academics Intro') }}</button>
                    </div>
                </form>
            </section>

            <section class="academics-cms-editor-panel" data-academics-editor-panel="features" hidden>
                <div data-academics-features-section-shell>
                    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                        @csrf
                        <input type="hidden" name="tab_key" value="academics">
                        <input type="hidden" name="section_key" value="features">
                        @if($requestId > 0)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        <div class="academics-cms-form-grid" data-academics-card-panel-meta>
                            <div class="form-group">
                                <label>Section Tag</label>
                                <input type="text" name="academics[features][tag]" maxlength="120" value="{{ $featuresEditor['tag'] ?? ($featuresEditor['eyebrow'] ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>Section Title</label>
                                <input type="text" name="academics[features][title]" maxlength="255" value="{{ $featuresEditor['title'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            @include('partials.rich_text_editor', [
                                'name' => 'academics[features][description]',
                                'value' => $featuresEditor['description'] ?? '',
                                'placeholder' => 'Write the description for the What We Offer section...',
                                'characterLimit' => 100,
                                'counterMode' => 'limit',
                            ])
                        </div>

                        @foreach(($featuresEditor['items'] ?? []) as $index => $item)
                            <input type="hidden" name="academics[features][items][{{ $index }}][tag]" value="{{ $item['tag'] ?? ($item['title'] ?? '') }}">
                            <input type="hidden" name="academics[features][items][{{ $index }}][title]" value="{{ $item['title'] ?? '' }}">
                            <input type="hidden" name="academics[features][items][{{ $index }}][description]" value="{{ $item['description'] ?? ($item['body'] ?? '') }}">
                            <input type="hidden" name="academics[features][items][{{ $index }}][wide]" value="{{ !empty($item['wide']) ? '1' : '0' }}">
                        @endforeach

                        <div class="academics-cms-modal-footer">
                            <button type="submit" class="btn btn-primary">{{ $submitLabel('What We Offer') }}</button>
                        </div>
                    </form>
                </div>

                <div data-academics-features-card-shell>
                    <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-academics-features-form data-academics-card-form="features">
                        @csrf
                        <input type="hidden" name="tab_key" value="academics">
                        <input type="hidden" name="section_key" value="features">
                        <input type="hidden" name="academics_features_version" value="0" data-academics-features-version data-academics-card-version>
                        <input type="hidden" name="academics_active_feature_index" value="" data-academics-active-feature-index data-academics-card-active-index>
                        <input type="hidden" name="academics[features][tag]" value="{{ $featuresEditor['tag'] ?? ($featuresEditor['eyebrow'] ?? '') }}">
                        <input type="hidden" name="academics[features][title]" value="{{ $featuresEditor['title'] ?? '' }}">
                        <input type="hidden" name="academics[features][description]" value="{{ $featuresEditor['description'] ?? '' }}">
                        @if($requestId > 0)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        <div class="academics-cms-card-stack" data-academics-features-stack data-academics-card-stack="features">
                            @foreach(($featuresEditor['items'] ?? []) as $index => $item)
                                <article class="academics-cms-card-editor" data-academics-feature-editor data-academics-feature-index="{{ $index }}" data-academics-page-card-editor="features" data-academics-page-card-index="{{ $index }}">
                                    <div class="academics-cms-card-editor-head" data-academics-card-editor-head>
                                        <h4>Feature Card {{ $loop->iteration }}</h4>
                                        <span>{{ !empty($item['wide']) ? 'Wide card' : 'Standard card' }}</span>
                                    </div>

                                    <input type="hidden" name="academics[features][items][{{ $index }}][wide]" value="{{ !empty($item['wide']) ? '1' : '0' }}">

                                    <div class="form-group">
                                        <label>Content Tag</label>
                                        <input type="text" name="academics[features][items][{{ $index }}][tag]" maxlength="120" value="{{ $item['tag'] ?? ($item['title'] ?? '') }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="academics[features][items][{{ $index }}][title]" maxlength="255" value="{{ $item['title'] ?? '' }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        @include('partials.rich_text_editor', [
                                            'name' => 'academics[features][items]['.$index.'][description]',
                                            'value' => $item['description'] ?? ($item['body'] ?? ''),
                                            'placeholder' => 'Write the supporting copy for this offer card...',
                                            'characterLimit' => 100,
                                            'counterMode' => 'limit',
                                        ])
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="academics-cms-modal-footer">
                            <button type="submit" class="btn btn-primary">{{ $submitLabel('What We Offer Card') }}</button>
                        </div>
                    </form>
                </div>
            </section>

            @include('partials.academics_linked_pages_editor_panels')
        </div>
    </div>
</div>

<script type="application/json" data-academics-preview-pages>
{!! json_encode(($academicsPreviewPages ?? ['overview' => ($academicsPreviewHtml ?? '')]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .academics-cms-workspace {
        --academics-preview-width: 1520px;
        --academics-preview-height: 1800px;
        --academics-preview-scale: 1;
        --academics-preview-scaled-width: calc(var(--academics-preview-width) * var(--academics-preview-scale));
        --academics-preview-scaled-height: calc(var(--academics-preview-height) * var(--academics-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .academics-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .academics-cms-preview-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-content: flex-start;
        margin-bottom: 18px;
    }

    .academics-cms-preview-nav-btn {
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

    .academics-cms-preview-nav-btn:hover,
    .academics-cms-preview-nav-btn:focus-visible {
        outline: none;
    }

    .academics-cms-preview-nav-btn.is-active {
        background: #800000;
        border-color: #800000;
        color: #fff;
    }

    .academics-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .academics-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .academics-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--academics-preview-scaled-width);
        max-width: 100%;
        height: var(--academics-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .academics-cms-preview-frame {
        display: block;
        width: var(--academics-preview-width);
        min-width: var(--academics-preview-width);
        height: var(--academics-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--academics-preview-scale));
        transform-origin: top left;
    }

    .academics-cms-modal[hidden] {
        display: none;
    }

    .academics-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .academics-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .academics-cms-modal-dialog {
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

    .academics-cms-modal-close {
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

    .academics-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .academics-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .academics-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.55;
    }

    .academics-cms-side-kicker {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .academics-cms-modal-panels {
        padding: 22px 24px 24px;
    }

    .academics-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .academics-cms-card-stack {
        display: grid;
        gap: 14px;
    }

    .academics-cms-card-editor {
        padding: 16px;
        border: 1px solid #efe3dc;
        border-radius: 16px;
        background: #fff;
        display: none;
    }

    .academics-cms-card-editor.is-active {
        display: block;
    }

    .academics-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }

    .academics-cms-card-editor-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .academics-cms-card-editor-head span {
        color: #8a7a73;
        font-size: 0.8rem;
    }

    .academics-cms-image-dropzone-shell {
        width: 100%;
        margin: 0 auto;
    }

    .academics-cms-image-dropzone {
        position: relative;
        isolation: isolate;
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

    .academics-cms-image-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .academics-cms-image-dropzone-preview-column {
        position: relative;
        z-index: 1;
        display: flex;
        min-width: 0;
        min-height: 180px;
    }

    .academics-cms-image-dropzone-media {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
    }

    .academics-cms-image-dropzone-preview {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 180px;
        object-fit: cover;
        border-radius: 18px;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
    }

    .academics-cms-image-dropzone-label {
        display: none;
        color: #7f1113;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
    }

    .academics-cms-image-dropzone-upload {
        position: relative;
        z-index: 2;
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

    .academics-cms-image-dropzone-icon {
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

    .academics-cms-image-dropzone-upload-title {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .academics-cms-image-dropzone-upload-copy {
        display: block;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .academics-cms-image-dropzone-upload-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 18px;
        margin: 0;
        border-radius: 999px;
        background: #fff8f1;
        color: #1b1714;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        text-decoration: none;
        white-space: nowrap;
        align-self: center;
        width: auto;
        max-width: 100%;
        cursor: pointer;
    }

    .academics-cms-image-dropzone-file {
        display: block;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.8rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .academics-cms-image-dropzone-input {
        display: none;
    }

    .academics-cms-image-dropzone-remove {
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

    .academics-cms-image-dropzone-remove:hover {
        background: #7f1113;
        color: #fff8f1;
    }

    @media (max-width: 460px) {
        .academics-cms-image-dropzone {
            grid-template-columns: 1fr;
        }

        .academics-cms-image-dropzone-upload {
            min-height: 280px;
        }
    }

    @media (max-width: 640px) {
        .academics-cms-image-dropzone-remove {
            top: 12px;
            right: 12px;
        }
    }

    .academics-cms-image-dropzone-remove[hidden] {
        display: none;
    }

    .academics-cms-textarea-field {
        display: grid;
        gap: 8px;
    }

    .academics-cms-char-counter {
        justify-self: end;
        color: #8a7a73;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
    }

    .academics-cms-char-counter.is-limit {
        color: #b91c1c;
    }

    .academics-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    [data-academics-contents-card-shell],
    [data-academics-features-card-shell],
    [data-academics-page-card-item-shell] {
        display: none;
    }

    .academics-cms-editor-panel.is-card-focus [data-academics-contents-section-shell],
    .academics-cms-editor-panel.is-card-focus [data-academics-features-section-shell],
    .academics-cms-editor-panel.is-card-focus [data-academics-page-card-section-shell] {
        display: none;
    }

    .academics-cms-editor-panel.is-card-focus [data-academics-contents-card-shell],
    .academics-cms-editor-panel.is-card-focus [data-academics-features-card-shell],
    .academics-cms-editor-panel.is-card-focus [data-academics-page-card-item-shell] {
        display: block;
    }

    .academics-cms-modal.is-card-focus .academics-cms-modal-header {
        display: none;
    }

    .academics-cms-modal.is-card-focus {
        align-items: center !important;
    }

    .academics-cms-modal.is-card-focus .academics-cms-modal-dialog {
        width: min(760px, calc(100vw - 24px));
        max-width: min(760px, calc(100vw - 24px));
        border-radius: 30px;
        background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
        box-shadow: 0 30px 70px rgba(45, 8, 5, 0.2);
    }

    .academics-cms-modal.is-card-focus .academics-cms-modal-panels {
        padding: 18px;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf6 0%, #fffdfc 100%);
    }

    .academics-cms-editor-panel.is-card-focus form {
        max-width: 680px;
        margin: 0 auto;
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-stack {
        gap: 0;
    }

    .academics-cms-editor-panel.is-title-focus [data-academics-card-stack="pup-iapply-schedule"] {
        display: none;
    }

    .academics-cms-editor-panel.is-card-focus [data-academics-card-panel-meta],
    .academics-cms-editor-panel.is-card-focus [data-academics-card-editor-head] {
        display: none;
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-editor.is-active {
        padding: 22px;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow:
            0 16px 34px rgba(92, 12, 6, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-editor.is-active .form-group + .form-group {
        margin-top: 14px;
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-editor.is-active .academics-cms-form-grid {
        column-gap: 16px;
        row-gap: 10px;
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-editor.is-active .academics-cms-form-grid .form-group {
        margin-bottom: 0;
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-editor.is-active .academics-cms-form-grid .form-group + .form-group {
        margin-top: 0;
    }

    .academics-cms-editor-panel.is-card-focus .academics-cms-card-editor.is-active .academics-cms-form-grid + .form-group {
        margin-top: 16px;
    }

    .academics-cms-modal.is-card-focus .academics-cms-modal-close {
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(127, 17, 19, 0.08);
        font-size: 1.35rem;
    }

    @media (max-width: 768px) {
        .academics-cms-workspace {
            --academics-preview-width: 1440px;
            --academics-preview-height: 1760px;
            --academics-preview-scale: 0.58;
        }

        .academics-cms-form-grid {
            grid-template-columns: 1fr;
        }

        .academics-cms-card-editor-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .academics-cms-modal-dialog {
            width: min(100vw - 20px, 1080px);
            max-height: calc(100vh - 20px);
            margin: 10px auto;
        }

        .academics-cms-modal-header,
        .academics-cms-modal-panels {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

<script>
    (() => {
        if (window.__academicsCmsPreviewEditorReady) {
            if (typeof window.__rebindAcademicsCmsPreviewEditor === 'function') {
                window.__rebindAcademicsCmsPreviewEditor();
            }
            return;
        }

        const ACADEMICS_PREVIEW_MIN_LOADING_MS = 1500;
        let academicsPreviewFitFrame = null;
        const ACADEMICS_PREVIEW_STORAGE_KEY = `cms:academics-preview-route:${window.location.pathname}`;
        const ACADEMICS_PREVIEW_LEGACY_STORAGE_KEY = '{{ $idPrefix }}-active-academics-preview-page';
        let currentAcademicsPreviewRoute = 'overview';

        function getStoredAcademicsPreviewRoute() {
            try {
                return window.localStorage.getItem(ACADEMICS_PREVIEW_STORAGE_KEY)
                    || window.localStorage.getItem(ACADEMICS_PREVIEW_LEGACY_STORAGE_KEY)
                    || '';
            } catch (_) {
                return '';
            }
        }

        function storeAcademicsPreviewRoute(routeKey) {
            try {
                const storedRoute = String(routeKey || 'overview');
                window.localStorage.setItem(ACADEMICS_PREVIEW_STORAGE_KEY, storedRoute);
                window.localStorage.setItem(ACADEMICS_PREVIEW_LEGACY_STORAGE_KEY, storedRoute);
            } catch (_) {
                // Ignore storage failures and keep the route in memory for this session.
            }
        }

        function getAcademicsPreviewPayloads() {
            const el = document.querySelector('[data-academics-preview-pages]');
            if (!el) {
                return {};
            }

            try {
                return JSON.parse(el.textContent || '{}');
            } catch (_) {
                return {};
            }
        }

        function syncAcademicsPreviewNav(routeKey) {
            document.querySelectorAll('[data-academics-preview-page]').forEach((button) => {
                const isActive = (button.getAttribute('data-academics-preview-page') || '') === routeKey;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

        function syncEditorsInScope(scope) {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
        }

        function fitAcademicsPreview(frame) {
            const workspace = frame.closest('.academics-cms-workspace');
            const shell = frame.closest('.academics-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--academics-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--academics-preview-scale', `${scale}`);
        }

        function setAcademicsPreviewLoading(frame, isLoading) {
            const canvas = frame?.closest('.academics-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            if (frame.__academicsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__academicsPreviewLoadingTimeout);
                frame.__academicsPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__academicsPreviewLoadingSession = (frame.__academicsPreviewLoadingSession || 0) + 1;
                frame.__academicsPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__academicsPreviewLoadingSession || 0,
                },
            }));
        }

        function finishAcademicsPreviewLoading(frame) {
            const canvas = frame?.closest('.academics-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            const activeSession = frame.__academicsPreviewLoadingSession || 0;
            const startedAt = frame.__academicsPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, ACADEMICS_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__academicsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__academicsPreviewLoadingTimeout);
            }

            frame.__academicsPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__academicsPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__academicsPreviewLoadingTimeout = null;
            }, remaining);
        }

        function getAcademicsPreviewElementBottom(element) {
            return element.offsetTop + element.offsetHeight;
        }

        function isAcademicsPreviewMeasuredElement(element) {
            if (!(element instanceof HTMLElement)) {
                return false;
            }

            const styles = window.getComputedStyle(element);
            return styles.display !== 'none'
                && styles.visibility !== 'hidden'
                && styles.position !== 'fixed';
        }

        function measureAcademicsPreviewHeight(frame) {
            const doc = frame.contentDocument;

            if (!doc) {
                return 0;
            }

            const main = doc.querySelector('.main-content');
            const scope = main instanceof HTMLElement ? main : doc.body;

            if (!(scope instanceof HTMLElement)) {
                return 0;
            }

            if (currentAcademicsPreviewRoute === 'university-calendar') {
                const calendarCard = doc.querySelector('.uc-calendar-official-card');
                const calendarSection = calendarCard?.closest('.contents-strip');
                const heroSection = doc.querySelector('.uc-hero-b');
                const breadcrumbShell = doc.querySelector('.academic-shell');
                const candidates = [breadcrumbShell, heroSection, calendarSection, calendarCard]
                    .filter((element) => element instanceof HTMLElement && isAcademicsPreviewMeasuredElement(element));
                const routeBottom = candidates.reduce((maxBottom, element) => {
                    return Math.max(maxBottom, getAcademicsPreviewElementBottom(element));
                }, 0);

                if (routeBottom > 0) {
                    return Math.max(1, Math.ceil(routeBottom));
                }
            }

            const visibleElements = Array.from(scope.children)
                .filter((element) => isAcademicsPreviewMeasuredElement(element));

            const contentBottom = visibleElements.reduce((maxBottom, element) => {
                return Math.max(maxBottom, getAcademicsPreviewElementBottom(element));
            }, 0);

            const fallbackHeight = Math.max(scope.scrollHeight || 0, scope.offsetHeight || 0);

            return Math.max(1, Math.ceil(contentBottom || fallbackHeight));
        }

        function syncAcademicsPreviewHeight(frame, nextHeight) {
            const workspace = frame.closest('.academics-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--academics-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
            fitAcademicsPreview(frame);
        }

        function scheduleAcademicsPreviewSync(frame) {
            if (!frame) {
                return;
            }

            if (frame.__academicsPreviewSyncFrame !== undefined && frame.__academicsPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__academicsPreviewSyncFrame);
            }

            frame.__academicsPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureAcademicsPreviewHeight(frame);

                if (measuredHeight > 0) {
                    syncAcademicsPreviewHeight(frame, measuredHeight);
                } else {
                    fitAcademicsPreview(frame);
                }

                frame.__academicsPreviewSyncFrame = null;
            });
        }

        function queueAcademicsPreviewSettledSync(frame) {
            scheduleAcademicsPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleAcademicsPreviewSync(frame), delay);
            });
            finishAcademicsPreviewLoading(frame);
        }

        function bindAcademicsPreviewDocument(frame) {
            const doc = frame.contentDocument;
            const win = frame.contentWindow;

            if (!doc) {
                return;
            }

            if (typeof frame.__academicsPreviewCleanup === 'function') {
                frame.__academicsPreviewCleanup();
            }

            const cleanups = [];
            const schedule = () => queueAcademicsPreviewSettledSync(frame);
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

            frame.__academicsPreviewCleanup = () => {
                cleanups.forEach((cleanup) => cleanup());
            };
        }

        function fitAllAcademicsPreviews() {
            document.querySelectorAll('[data-academics-preview-frame]').forEach((frame) => {
                scheduleAcademicsPreviewSync(frame);
            });
        }

        function loadAcademicsPreview(frame, options = {}) {
            const payloads = getAcademicsPreviewPayloads();
            const targetKey = options.routeKey && payloads[options.routeKey]
                ? options.routeKey
                : currentAcademicsPreviewRoute;
            const shouldForceReload = options.forceReload === true;
            const explicitSessionId = options.sessionId;

            if (!frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__academicsPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            if (!shouldForceReload && currentAcademicsPreviewRoute === targetKey && (typeof window.hasCmsPreviewFrameContent === 'function' ? window.hasCmsPreviewFrameContent(frame) : !!frame.srcdoc)) {
                storeAcademicsPreviewRoute(targetKey);
                setAcademicsPreviewLoading(frame, true);
                queueAcademicsPreviewSettledSync(frame);
                return;
            }

            currentAcademicsPreviewRoute = targetKey;
            storeAcademicsPreviewRoute(targetKey);
            syncAcademicsPreviewNav(targetKey);
            setAcademicsPreviewLoading(frame, true);
            const previewHtml = payloads[targetKey] || payloads.overview || '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>';

            try {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, previewHtml);
                } else {
                    frame.srcdoc = previewHtml;
                }
            } catch (_) {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                } else {
                    frame.srcdoc = '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>';
                }
            }
        }

        function scheduleFitAllAcademicsPreviews() {
            if (academicsPreviewFitFrame !== null) {
                window.cancelAnimationFrame(academicsPreviewFitFrame);
            }

            academicsPreviewFitFrame = window.requestAnimationFrame(() => {
                fitAllAcademicsPreviews();
                window.setTimeout(fitAllAcademicsPreviews, 140);
                academicsPreviewFitFrame = null;
            });
        }

        const getCardEditorCollections = () => {
            const collections = {};

            document.querySelectorAll('[data-academics-card-form]').forEach((form) => {
                const sectionKey = form.getAttribute('data-academics-card-form') || '';
                if (!sectionKey) {
                    return;
                }

                collections[sectionKey] = {
                    form,
                    stack: form.querySelector(`[data-academics-card-stack="${sectionKey}"]`),
                    versionInput: form.querySelector('[data-academics-card-version]'),
                    hiddenInput: form.querySelector('[data-academics-card-active-index]'),
                    selector: `[data-academics-page-card-editor="${sectionKey}"]`,
                    indexAttribute: 'data-academics-page-card-index',
                };
            });

            return collections;
        };

        const cardEditorCollections = getCardEditorCollections();
        const academicsPreviewImageState = new Map();

        function getAcademicsPreviewFrames() {
            return Array.from(document.querySelectorAll('[data-academics-preview-frame]'));
        }

        function getAcademicsPreviewImageKey(sectionKey, cardIndex) {
            return `${String(sectionKey ?? '').trim()}:${String(cardIndex ?? '').trim()}`;
        }

        function replayAcademicsPreviewImages(frame) {
            const targetWindow = frame?.contentWindow;
            if (!targetWindow) {
                return;
            }

            academicsPreviewImageState.forEach((state) => {
                targetWindow.postMessage({
                    type: 'cms-academics-preview-image',
                    section: state.sectionKey,
                    cardIndex: state.cardIndex,
                    src: state.src,
                    defaultSrc: state.defaultSrc,
                }, '*');
            });
        }

        function syncAcademicsPreviewImage(sectionKey, cardIndex, src, defaultSrc = '') {
            const normalizedSection = String(sectionKey || '').trim();
            const normalizedIndex = String(cardIndex ?? '').trim();
            const canTrack = normalizedSection === 'hero'
                || normalizedSection.endsWith('-hero')
                || normalizedIndex !== '';

            if (!normalizedSection || !canTrack) {
                return;
            }

            const nextSrc = String(src || '').trim();
            const nextDefaultSrc = String(defaultSrc || '').trim();
            const key = getAcademicsPreviewImageKey(normalizedSection, normalizedIndex);

            if (nextSrc && nextSrc !== nextDefaultSrc) {
                academicsPreviewImageState.set(key, {
                    sectionKey: normalizedSection,
                    cardIndex: normalizedIndex,
                    src: nextSrc,
                    defaultSrc: nextDefaultSrc,
                });
            } else {
                academicsPreviewImageState.delete(key);
            }

            getAcademicsPreviewFrames().forEach((frame) => {
                const targetWindow = frame.contentWindow;
                if (!targetWindow) {
                    return;
                }

                targetWindow.postMessage({
                    type: 'cms-academics-preview-image',
                    section: normalizedSection,
                    cardIndex: normalizedIndex,
                    src: nextSrc,
                    defaultSrc: nextDefaultSrc,
                }, '*');
            });
        }

        function bumpEditorVersion(input) {
            if (input) {
                input.value = String(Date.now());
            }
        }

        function shouldTrackAcademicsField(target) {
            if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
                return false;
            }

            const type = (target.type || '').toLowerCase();
            return type !== 'file'
                && type !== 'hidden'
                && type !== 'submit'
                && type !== 'button'
                && type !== 'reset';
        }

        function bindAcademicsDirtyTracking(form, versionInput, boundKey) {
            if (!form || form.dataset[boundKey] === '1') {
                return;
            }

            form.dataset[boundKey] = '1';

            const markDirty = (event) => {
                if (!shouldTrackAcademicsField(event.target)) {
                    return;
                }

                bumpEditorVersion(versionInput);
            };

            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
        }

        function initAcademicsImageDropzones(scope = document) {
            scope.querySelectorAll('.academics-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.academicsDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-academics-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-academics-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-academics-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-academics-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-academics-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-academics-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-academics-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-academics-clear-image-for="${input.id}"]`);
                const previewSection = input.closest('[data-academics-page-card-editor]')?.getAttribute('data-academics-page-card-editor')
                    || input.closest('[data-academics-editor-panel]')?.getAttribute('data-academics-editor-panel')
                    || '';
                const previewCardIndex = input.closest('[data-academics-page-card-index]')?.getAttribute('data-academics-page-card-index') || '';
                const imageField = input.dataset.academicsImageFieldId
                    ? document.getElementById(input.dataset.academicsImageFieldId)
                    : (
                        input.closest('[data-academics-page-card-editor]')?.querySelector('[data-academics-image-field]')
                        || input.closest('[data-academics-contents-editor]')?.querySelector('[data-academics-image-field]')
                        || null
                    );

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.academicsDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.academicsDefaultSrc || '';

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;
                };

                const applyPreviewSource = (src) => {
                    const nextSrc = String(src || '').trim();

                    if (previewEl && nextSrc !== '') {
                        previewEl.src = nextSrc;
                    }

                    syncAcademicsPreviewImage(previewSection, previewCardIndex, nextSrc || previewEl?.src || '', defaultSrc);
                    syncRemoveState();
                };

                const applyFile = (file) => {
                    if (input.__academicsPreviewObjectUrl && typeof URL?.revokeObjectURL === 'function') {
                        URL.revokeObjectURL(input.__academicsPreviewObjectUrl);
                        input.__academicsPreviewObjectUrl = '';
                    }

                    if (!file) {
                        syncRemoveState();
                        return;
                    }

                    fileNameEl.textContent = `Selected: ${file.name}`;

                    if (typeof FileReader === 'function') {
                        const reader = new FileReader();
                        reader.addEventListener('load', () => {
                            applyPreviewSource(typeof reader.result === 'string' ? reader.result : '');
                        });
                        reader.addEventListener('error', () => {
                            if (typeof URL?.createObjectURL === 'function') {
                                input.__academicsPreviewObjectUrl = URL.createObjectURL(file);
                                applyPreviewSource(input.__academicsPreviewObjectUrl);
                                return;
                            }

                            syncRemoveState();
                        });
                        reader.readAsDataURL(file);
                        return;
                    }

                    if (typeof URL?.createObjectURL === 'function') {
                        input.__academicsPreviewObjectUrl = URL.createObjectURL(file);
                        applyPreviewSource(input.__academicsPreviewObjectUrl);
                        return;
                    }

                    syncRemoveState();
                };

                input.addEventListener('change', () => {
                    applyFile(input.files && input.files[0] ? input.files[0] : null);
                });

                label.addEventListener('click', (event) => {
                    if (event.target.closest('[data-academics-clear-image-for]')) {
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
                    if (input.__academicsPreviewObjectUrl && typeof URL?.revokeObjectURL === 'function') {
                        URL.revokeObjectURL(input.__academicsPreviewObjectUrl);
                        input.__academicsPreviewObjectUrl = '';
                    }
                    input.value = '';
                    if (imageField) {
                        imageField.value = '';
                    }
                    if (previewEl && defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    syncAcademicsPreviewImage(previewSection, previewCardIndex, defaultSrc, defaultSrc);
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncRemoveState();
            });
        }

        function initAcademicsCharCounters(scope = document) {
            scope.querySelectorAll('[data-academics-char-limit]').forEach((field) => {
                if (field.dataset.academicsCharCounterBound === '1') {
                    return;
                }

                const input = field.querySelector('[data-academics-char-input]');
                const counter = field.querySelector('[data-academics-char-counter]');
                const limit = Number(field.getAttribute('data-academics-char-limit') || input?.getAttribute('maxlength') || 0);

                if (!input || !counter || limit <= 0) {
                    return;
                }

                field.dataset.academicsCharCounterBound = '1';
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
        }

        window.__rebindAcademicsCmsPreviewEditor = () => {
            initAcademicsImageDropzones(document);
            initAcademicsCharCounters(document);
        };

        function submitEditorForm(form) {
            if (!form) {
                return;
            }

            syncEditorsInScope(form);

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        }

        function setActiveEditor(stack, selector, indexAttribute, hiddenInput, targetIndex = null) {
            const editors = Array.from(stack?.querySelectorAll(selector) ?? []);

            if (!editors.length) {
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
                return null;
            }

            const normalizedIndex = targetIndex === null || targetIndex === undefined || targetIndex === ''
                ? null
                : String(targetIndex);
            let activeEditor = null;

            editors.forEach((editor) => {
                const isMatch = normalizedIndex !== null && editor.getAttribute(indexAttribute) === normalizedIndex;
                const shouldActivate = normalizedIndex === null ? editor === editors[0] : isMatch;
                editor.classList.toggle('is-active', shouldActivate);

                if (shouldActivate) {
                    activeEditor = editor;
                }
            });

            if (hiddenInput) {
                hiddenInput.value = activeEditor?.getAttribute(indexAttribute) || '';
            }

            return activeEditor;
        }

        function deleteEditorByIndex(stack, selector, indexAttribute, versionInput, hiddenInput, targetIndex) {
            const editor = stack?.querySelector(`${selector}[${indexAttribute}="${targetIndex}"]`);
            if (!editor) {
                return false;
            }

            editor.remove();
            bumpEditorVersion(versionInput);
            setActiveEditor(stack, selector, indexAttribute, hiddenInput);
            return true;
        }

        function nextAcademicsCardIndex(collection) {
            const indexes = Array.from(collection?.stack?.querySelectorAll(collection.selector) ?? [])
                .map((editor) => Number(editor.getAttribute(collection.indexAttribute) || '0'))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        }

        function replaceAcademicsTemplateTokens(root, tokens) {
            const elements = [root, ...Array.from(root.querySelectorAll('*'))];

            elements.forEach((element) => {
                for (const attribute of Array.from(element.attributes || [])) {
                    let nextValue = attribute.value;

                    Object.entries(tokens).forEach(([token, value]) => {
                        nextValue = nextValue.replaceAll(token, value);
                    });

                    if (nextValue !== attribute.value) {
                        element.setAttribute(attribute.name, nextValue);
                    }
                }

                if (element.childNodes.length === 1 && element.firstChild?.nodeType === Node.TEXT_NODE) {
                    let nextText = element.textContent || '';

                    Object.entries(tokens).forEach(([token, value]) => {
                        nextText = nextText.replaceAll(token, value);
                    });

                    if (nextText !== element.textContent) {
                        element.textContent = nextText;
                    }
                }
            });
        }

        function relabelAcademicsCardEditors(collection) {
            Array.from(collection?.stack?.querySelectorAll(collection.selector) ?? []).forEach((editor, index) => {
                const displayNumber = index + 1;
                const headTitle = editor.querySelector('[data-academics-card-editor-head] h4');
                const headSubtitle = editor.querySelector('[data-academics-card-editor-head] span');
                const titleInput = editor.querySelector('input[name*="[title]"]');
                const dropzoneTitle = editor.querySelector('.academics-cms-image-dropzone-label');
                const formLabel = collection.form?.getAttribute('data-academics-page-label') || 'Program';

                if (headTitle) {
                    headTitle.textContent = `${formLabel} Card ${displayNumber}`;
                }

                if (headSubtitle) {
                    headSubtitle.textContent = String(titleInput?.value || '').trim() || 'New card';
                }

                if (dropzoneTitle) {
                    dropzoneTitle.textContent = `Card ${displayNumber}`;
                }
            });
        }

        function addAcademicsProgramCard(sectionKey) {
            const collection = cardEditorCollections[sectionKey];
            if (!collection) {
                return null;
            }

            const template = collection.form.querySelector(`[data-academics-program-card-template="${sectionKey}"]`);
            if (!template || !collection.stack) {
                return null;
            }

            const index = nextAcademicsCardIndex(collection);
            const dropzoneId = `{{ $idPrefix }}-${sectionKey}-${index}-image`;
            const fragment = template.content.cloneNode(true);
            const editor = fragment.querySelector(collection.selector);

            if (!editor) {
                return null;
            }

            replaceAcademicsTemplateTokens(editor, {
                '__INDEX__': String(index),
                '__DROPZONE_ID__': dropzoneId,
            });

            collection.stack.appendChild(fragment);
            initAcademicsImageDropzones(collection.stack);

            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(collection.stack);
            }

            bumpEditorVersion(collection.versionInput);
            relabelAcademicsCardEditors(collection);
            setActiveEditor(collection.stack, collection.selector, collection.indexAttribute, collection.hiddenInput, index);
            editor.scrollIntoView({ block: 'nearest' });
            editor.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface')?.focus();

            return editor;
        }

        async function confirmDeleteAcademicsCard(type, targetIndex) {
            const collection = cardEditorCollections[type];
            if (!collection) {
                return;
            }

            const { stack, selector, indexAttribute, versionInput, hiddenInput, form } = collection;
            const editor = stack?.querySelector(`${selector}[${indexAttribute}="${targetIndex}"]`);

            if (!editor) {
                return;
            }

            const titleInput = editor.querySelector('input[name*="[label]"], input[name*="[title]"]');
            const cardTitle = String(titleInput?.value || '').trim();
            const message = cardTitle
                ? `Do you want to delete "${cardTitle}"?`
                : 'Do you want to delete this card?';

            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Card',
                    message,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(message);
            }

            if (!confirmed) {
                return;
            }

            if (deleteEditorByIndex(stack, selector, indexAttribute, versionInput, hiddenInput, targetIndex)) {
                submitEditorForm(form);
            }
        }

        function openAcademicsEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-academics-editor-modal]');
            if (!modal) {
                return;
            }

            const title = modal.querySelector('#{{ $idPrefix }}-modal-title');
            const description = modal.querySelector('[data-academics-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            modal.querySelectorAll('[data-academics-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-academics-editor-panel') === sectionKey;
                const hasCardTarget = options.cardIndex !== null && options.cardIndex !== undefined && options.cardIndex !== '';
                const cardCollection = cardEditorCollections[sectionKey] || null;
                const isScheduleSection = sectionKey === 'pup-iapply-schedule';
                const isTitleFocus = Boolean(isActive && isScheduleSection && !hasCardTarget);
                const isCardFocus = Boolean(cardCollection && hasCardTarget);
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);
                panel.classList.toggle('is-title-focus', isTitleFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    modal.classList.toggle('is-title-focus', isTitleFocus);
                    if (title) {
                        title.textContent = label || 'Edit academics section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the Academics page preview.';
                    }

                    let activeCardEditor = null;
                    if (cardCollection && !isTitleFocus) {
                        activeCardEditor = setActiveEditor(
                            cardCollection.stack,
                            cardCollection.selector,
                            cardCollection.indexAttribute,
                            cardCollection.hiddenInput,
                            options.cardIndex ?? null
                        );
                    }

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const focusScope = activeCardEditor || panel;
                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                    firstField?.focus();
                }
            });
        }

        function closeAcademicsEditor() {
            const modal = document.querySelector('[data-academics-editor-modal]');
            if (!modal) {
                return;
            }

            modal.hidden = true;
            modal.classList.remove('is-card-focus');
            modal.classList.remove('is-title-focus');
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
        }

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-academics-edit') {
                openAcademicsEditor(data.section || '', data.label || 'Edit academics section');
                return;
            }

            if (data.type === 'cms-academics-edit-card') {
                openAcademicsEditor(data.section || '', data.label || 'Edit academics card', {
                    cardIndex: data.cardIndex,
                });
                return;
            }

            if (data.type === 'cms-academics-add-card') {
                const section = data.section || '';
                openAcademicsEditor(section, data.label || 'Add academics card', {
                    cardIndex: '__new__',
                });
                window.setTimeout(() => addAcademicsProgramCard(section), 0);
                return;
            }

            if (data.type === 'cms-academics-delete-card') {
                confirmDeleteAcademicsCard(data.section || '', data.cardIndex);
                return;
            }

            if (data.type === 'cms-academics-preview-height') {
                const targetFrame = Array.from(document.querySelectorAll('[data-academics-preview-frame]'))
                    .find((frame) => frame.contentWindow === event.source);

                if (!targetFrame) {
                    return;
                }

                const measuredHeight = measureAcademicsPreviewHeight(targetFrame);
                syncAcademicsPreviewHeight(targetFrame, measuredHeight > 0 ? measuredHeight : data.height);
                return;
            }

            if (data.type === 'cms-academics-preview-route') {
                const frame = document.querySelector('[data-academics-preview-frame]');
                if (!frame) {
                    return;
                }

                loadAcademicsPreview(frame, {
                    routeKey: data.route || 'overview',
                });
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-close-academics-editor]')) {
                event.preventDefault();
                closeAcademicsEditor();
                return;
            }

            const previewButton = event.target.closest('[data-academics-preview-page]');
            if (previewButton) {
                event.preventDefault();
                const frame = document.querySelector('[data-academics-preview-frame]');
                if (!frame) {
                    return;
                }

                loadAcademicsPreview(frame, {
                    routeKey: previewButton.getAttribute('data-academics-preview-page') || 'overview',
                });
            }

            const addProgramCardButton = event.target.closest('[data-add-academics-program-card]');
            if (addProgramCardButton) {
                event.preventDefault();
                addAcademicsProgramCard(addProgramCardButton.getAttribute('data-add-academics-program-card') || '');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAcademicsEditor();
            }
        });

        document.querySelectorAll('.{{ $formClass }}').forEach((form) => {
            form.addEventListener('submit', () => syncEditorsInScope(form));
        });

        document.querySelectorAll('[data-academics-preview-frame]').forEach((frame) => {
            loadAcademicsPreview(frame, {
                routeKey: getStoredAcademicsPreviewRoute() || 'overview',
            });

            frame.addEventListener('load', () => {
                bindAcademicsPreviewDocument(frame);
                replayAcademicsPreviewImages(frame);
                queueAcademicsPreviewSettledSync(frame);
                scheduleFitAllAcademicsPreviews();
                window.setTimeout(() => scheduleAcademicsPreviewSync(frame), 120);
                window.setTimeout(() => scheduleAcademicsPreviewSync(frame), 360);
            });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAllAcademicsPreviews();
            });

            document.querySelectorAll('.academics-cms-preview-frame-shell').forEach((shell) => {
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
                scheduleFitAllAcademicsPreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('resize', scheduleFitAllAcademicsPreviews);
        window.addEventListener('pageshow', scheduleFitAllAcademicsPreviews);
        window.addEventListener('load', scheduleFitAllAcademicsPreviews);
        window.addEventListener('cms:tab-activated', (event) => {
            const tabPanel = event.detail?.panel;
            const sessionId = Number(event.detail?.sessionId || 0) || undefined;

            document.querySelectorAll('[data-academics-preview-frame]').forEach((frame) => {
                if (!tabPanel || !tabPanel.contains(frame)) {
                    return;
                }

                loadAcademicsPreview(frame, {
                    sessionId,
                    routeKey: currentAcademicsPreviewRoute,
                    forceReload: true,
                });
                window.setTimeout(() => scheduleFitAllAcademicsPreviews(), 40);
                window.setTimeout(() => scheduleFitAllAcademicsPreviews(), 180);
            });
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAllAcademicsPreviews();
            }
        });

        window.refreshAcademicsCmsPreview = (scope) => {
            const frames = scope
                ? Array.from(scope.querySelectorAll('[data-academics-preview-frame]'))
                : Array.from(document.querySelectorAll('[data-academics-preview-frame]'));

            frames.forEach((frame) => {
                loadAcademicsPreview(frame, {
                    routeKey: currentAcademicsPreviewRoute,
                    forceReload: true,
                });
            });
        };

        scheduleFitAllAcademicsPreviews();
        syncAcademicsPreviewNav(currentAcademicsPreviewRoute);
        Object.values(cardEditorCollections).forEach((collection) => {
            setActiveEditor(collection.stack, collection.selector, collection.indexAttribute, collection.hiddenInput);
            relabelAcademicsCardEditors(collection);
            bindAcademicsDirtyTracking(
                collection.form,
                collection.versionInput,
                `academicsCardDirtyTrackingBound${collection.form.getAttribute('data-academics-card-form') || ''}`
            );
        });
        window.__rebindAcademicsCmsPreviewEditor();
        window.__academicsCmsPreviewEditorReady = true;
    })();
</script>
