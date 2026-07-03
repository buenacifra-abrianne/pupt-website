@php
    $researchDefaults = \App\Support\ResearchCmsContent::defaults();
    $researchEditorData = \App\Support\ResearchCmsContent::fromInput($researchEditorData ?? [], null);
    $pageEditor = $researchEditorData['page'] ?? $researchDefaults['page'];
    $cardsEditor = $researchEditorData['cards'] ?? $researchDefaults['cards'];
    $sdpEditor = $researchEditorData['strategic_development_plan'] ?? $researchDefaults['strategic_development_plan'];
    $researchPreviewPages = $researchPreviewPages ?? ['overview' => ($researchPreviewHtml ?? '')];
    $formClass = $researchEditorFormClass ?? 'cms-save-form';
    $submitRoute = $researchEditorSubmitRoute;
    $submitMode = $researchEditorSubmitMode ?? 'save';
    $requestId = (int) ($researchEditorRequestId ?? 0);
    $status = strtolower((string) ($researchEditorStatus ?? ''));
    $idPrefix = trim((string) ($researchEditorIdPrefix ?? 'research-editor'));
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
@endphp

<div class="research-cms-workspace">
    <div class="research-cms-preview-shell">
        <div class="research-cms-preview-nav" role="tablist" aria-label="Research preview sections">
            <button
                type="button"
                class="research-cms-preview-nav-btn is-active"
                data-research-preview-page="overview"
                role="tab"
                aria-selected="true"
            >
                Overview
            </button>
            <button
                type="button"
                class="research-cms-preview-nav-btn"
                data-research-preview-page="strategic-development-plan"
                role="tab"
                aria-selected="false"
            >
                Strategic Development Plan
            </button>
        </div>

        <div class="research-cms-preview-frame-shell">
            <div class="research-cms-preview-stage">
                <div class="research-cms-preview-canvas">
                    <iframe
                        title="Research and Extension page preview"
                        class="research-cms-preview-frame"
                        data-research-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="research-cms-modal" data-research-editor-modal hidden>
    <div class="research-cms-modal-backdrop" data-close-research-editor></div>

    <div class="research-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="research-cms-modal-close" data-close-research-editor aria-label="Close editor">&times;</button>

        <div class="research-cms-modal-header">
            <span class="research-cms-side-kicker">Research &amp; Extension Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit research and extension section</h3>
            <p data-research-editor-description>Select a highlighted section from the preview to edit it.</p>
        </div>

        <div class="research-cms-modal-panels">
            @php
                $researchHeroInputId = $idPrefix.'-research-page-hero-image';
                $researchHeroFieldId = $idPrefix.'-research-page-hero-image-field';
                $researchHeroPreview = \App\Support\ResearchCmsContent::resolveImagePath($pageEditor['hero_image'] ?? null, 'assets/static_img/pupillar.jpeg');
            @endphp
            <section class="research-cms-editor-panel" data-research-editor-panel="page" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="research_extension">
                    <input type="hidden" name="section_key" value="page">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <input type="hidden" id="{{ $researchHeroFieldId }}" name="research[page][hero_image]" value="{{ $pageEditor['hero_image'] ?? '' }}">

                    <div class="form-group">
                        <label>Upload Hero Image</label>
                        <div class="research-cms-image-dropzone-shell">
                            <div class="research-cms-image-dropzone cms-image-dropzone-hero" data-research-dropzone-for="{{ $researchHeroInputId }}" role="button" tabindex="0" aria-label="Upload hero image">
                                <span class="research-cms-image-dropzone-preview-column">
                                    <span class="research-cms-image-dropzone-media">
                                        <img
                                            src="{{ $researchHeroPreview }}"
                                            alt="Research hero image preview"
                                            class="research-cms-image-dropzone-preview"
                                            data-research-preview-for="{{ $researchHeroInputId }}"
                                            data-research-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                        >
                                        <button type="button" class="research-cms-image-dropzone-remove" data-research-clear-image-for="{{ $researchHeroInputId }}" aria-label="Delete image" title="Delete image">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="research-cms-image-dropzone-label">Hero Image</span>
                                </span>
                                <span class="research-cms-image-dropzone-upload">
                                    <span class="research-cms-image-dropzone-icon">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="research-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                    <span class="research-cms-image-dropzone-upload-copy">Your hero image preview updates instantly while you edit this section.</span>
                                    <span class="research-cms-image-dropzone-upload-button">Select image</span>
                                    <span class="research-cms-image-dropzone-file" data-research-file-name-for="{{ $researchHeroInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </span>
                            </div>
                        </div>
                        <input
                            id="{{ $researchHeroInputId }}"
                            class="research-cms-image-dropzone-input"
                            type="file"
                            name="research[page][hero_image_file]"
                            accept="image/*"
                            data-research-image-field-id="{{ $researchHeroFieldId }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>Hero Title</label>
                        <input type="text" name="research[page][title]" maxlength="255" value="{{ $pageEditor['title'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Hero Description</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'research[page][description]',
                            'value' => $pageEditor['description'] ?? '',
                            'placeholder' => 'Write the research and extension page description...',
                        ])
                    </div>

                    <div class="research-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Page Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="research-cms-editor-panel" data-research-editor-panel="cards" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-research-cards-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="research_extension">
                    <input type="hidden" name="section_key" value="cards">
                    <input type="hidden" name="research_cards_version" value="0" data-research-cards-version>
                    <input type="hidden" name="research_active_card_index" value="" data-research-active-card-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="research-cms-card-stack" data-research-card-stack>
                        @foreach($cardsEditor as $index => $card)
                            @php
                                $cardInputId = $idPrefix.'-research-card-image-'.$index;
                                $cardPreview = \App\Support\ResearchCmsContent::resolveImagePath($card['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                            @endphp
                            <article class="research-cms-card-editor" data-research-card-editor data-research-card-index="{{ $index }}">
                                <div class="research-cms-card-editor-head" data-research-card-editor-head>
                                    <div>
                                        <h4>Service {{ $loop->iteration }}</h4>
                                    </div>
                                </div>

                                <input type="hidden" name="research[cards][{{ $index }}][image]" value="{{ $card['image'] ?? '' }}" data-research-image-field>

                                <div class="form-group">
                                    <label>Upload Service Image</label>
                                    <div class="research-cms-image-dropzone-shell">
                                        <div class="research-cms-image-dropzone" data-research-dropzone-for="{{ $cardInputId }}" role="button" tabindex="0" aria-label="Upload service image">
                                            <span class="research-cms-image-dropzone-preview-column">
                                                <span class="research-cms-image-dropzone-media">
                                                    <img
                                                        src="{{ $cardPreview }}"
                                                        alt="{{ ($card['title'] ?? '') !== '' ? $card['title'] : 'Research service preview' }}"
                                                        class="research-cms-image-dropzone-preview"
                                                        data-research-preview-for="{{ $cardInputId }}"
                                                        data-research-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                    >
                                                    <button type="button" class="research-cms-image-dropzone-remove" data-research-clear-image-for="{{ $cardInputId }}" aria-label="Delete image" title="Delete image">
                                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                                <span class="research-cms-image-dropzone-label">Service {{ $index + 1 }}</span>
                                            </span>
                                            <span class="research-cms-image-dropzone-upload">
                                                <span class="research-cms-image-dropzone-icon">
                                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                </span>
                                                <span class="research-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                                <span class="research-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this service.</span>
                                                <span class="research-cms-image-dropzone-upload-button">Select image</span>
                                                <span class="research-cms-image-dropzone-file" data-research-file-name-for="{{ $cardInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                            </span>
                                        </div>
                                    </div>
                                    <input
                                        id="{{ $cardInputId }}"
                                        class="research-cms-image-dropzone-input"
                                        type="file"
                                        name="research[cards][{{ $index }}][image_file]"
                                        accept="image/*"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="research[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => 'research[cards]['.$index.'][description]',
                                        'value' => $card['description'] ?? '',
                                        'placeholder' => 'Write the service description...',
                                    ])
                                </div>

                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="research[cards][{{ $index }}][link]" maxlength="2048" value="{{ $card['link'] ?? '' }}">
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-research-card-template>
                        <article class="research-cms-card-editor" data-research-card-editor data-research-card-index="__INDEX__">
                            <div class="research-cms-card-editor-head" data-research-card-editor-head>
                                <div>
                                    <h4>Service __NUMBER__</h4>
                                </div>
                            </div>

                            <input type="hidden" name="research[cards][__INDEX__][image]" value="" data-research-image-field>

                            <div class="form-group">
                                <label>Upload Service Image</label>
                                <div class="research-cms-image-dropzone-shell">
                                    <div class="research-cms-image-dropzone" data-research-dropzone-for="{{ $idPrefix }}-research-card-image-__INDEX__" role="button" tabindex="0" aria-label="Upload service image">
                                        <span class="research-cms-image-dropzone-preview-column">
                                            <span class="research-cms-image-dropzone-media">
                                                <img
                                                    src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                    alt="Research service preview"
                                                    class="research-cms-image-dropzone-preview"
                                                    data-research-preview-for="{{ $idPrefix }}-research-card-image-__INDEX__"
                                                    data-research-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                >
                                                <button type="button" class="research-cms-image-dropzone-remove" data-research-clear-image-for="{{ $idPrefix }}-research-card-image-__INDEX__" aria-label="Delete image" title="Delete image">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                            <span class="research-cms-image-dropzone-label">Service __INDEX__</span>
                                        </span>
                                        <span class="research-cms-image-dropzone-upload">
                                            <span class="research-cms-image-dropzone-icon">
                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="research-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                            <span class="research-cms-image-dropzone-upload-copy">Your image preview updates instantly while you edit this service.</span>
                                            <span class="research-cms-image-dropzone-upload-button">Select image</span>
                                            <span class="research-cms-image-dropzone-file" data-research-file-name-for="{{ $idPrefix }}-research-card-image-__INDEX__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                        </span>
                                    </div>
                                </div>
                                <input
                                    id="{{ $idPrefix }}-research-card-image-__INDEX__"
                                    class="research-cms-image-dropzone-input"
                                    type="file"
                                    name="research[cards][__INDEX__][image_file]"
                                    accept="image/*"
                                >
                            </div>

                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="research[cards][__INDEX__][title]" maxlength="255" value="">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                @include('partials.rich_text_editor', [
                                    'name' => 'research[cards][__INDEX__][description]',
                                    'value' => '',
                                    'placeholder' => 'Write the service description...',
                                ])
                            </div>

                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="research[cards][__INDEX__][link]" maxlength="2048" value="">
                            </div>
                        </article>
                    </template>

                    <div class="research-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Services') }}
                        </button>
                    </div>
                </form>
            </section>
            <section class="research-cms-editor-panel" data-research-editor-panel="strategic-development-plan-header" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="research_extension">
                    <input type="hidden" name="section_key" value="strategic_development_plan">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Section Label</label>
                        <input type="text" name="research[strategic_development_plan][label]" maxlength="255" value="{{ $sdpEditor['label'] ?? 'Strategic Development Plan' }}">
                    </div>

                    <div class="form-group">
                        <label>Lead Text</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'research[strategic_development_plan][lead]',
                            'value' => $sdpEditor['lead'] ?? '',
                            'placeholder' => 'Write the strategic development plan intro text...',
                        ])
                    </div>

                    <div class="research-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Strategic Development Plan Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="research-cms-editor-panel" data-research-editor-panel="strategic-development-plan" hidden data-research-sdp-panel>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-research-sdp-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="research_extension">
                    <input type="hidden" name="section_key" value="strategic_development_plan">
                    <input type="hidden" name="research_sdp_version" value="0" data-research-sdp-version>
                    <input type="hidden" name="research_active_sdp_index" value="" data-research-active-sdp-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="research-cms-card-stack" data-research-sdp-stack>
                        @foreach($sdpEditor['development_priorities'] ?? [] as $index => $priority)
                            <article class="research-cms-card-editor" data-research-sdp-editor data-research-sdp-index="{{ $index }}">
                                <div class="research-cms-card-editor-head" data-research-sdp-editor-head>
                                    <div>
                                        <h4>Priority {{ $loop->iteration }}</h4>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="research[strategic_development_plan][development_priorities][{{ $index }}][title]" maxlength="255" value="{{ $priority['title'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Body</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => 'research[strategic_development_plan][development_priorities]['.$index.'][body]',
                                        'value' => $priority['body'] ?? '',
                                        'placeholder' => 'Describe this development priority...',
                                    ])
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-research-sdp-template>
                        <article class="research-cms-card-editor" data-research-sdp-editor data-research-sdp-index="__INDEX__">
                            <div class="research-cms-card-editor-head" data-research-sdp-editor-head>
                                <div>
                                    <h4>Priority __NUMBER__</h4>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="research[strategic_development_plan][development_priorities][__INDEX__][title]" maxlength="255" value="">
                            </div>

                            <div class="form-group">
                                <label>Body</label>
                                @include('partials.rich_text_editor', [
                                    'name' => 'research[strategic_development_plan][development_priorities][__INDEX__][body]',
                                    'value' => '',
                                    'placeholder' => 'Describe this development priority...',
                                ])
                            </div>
                        </article>
                    </template>

                    <div class="research-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Development Priorities') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-research-preview-pages-json>
{!! json_encode($researchPreviewPages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .research-cms-workspace {
        --research-preview-width: 1520px;
        --research-preview-height: 1800px;
        --research-preview-scale: 1;
        --research-preview-scaled-width: calc(var(--research-preview-width) * var(--research-preview-scale));
        --research-preview-scaled-height: calc(var(--research-preview-height) * var(--research-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .research-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .research-cms-preview-head {
        display: none;
    }

    .research-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .research-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .research-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--research-preview-scaled-width);
        max-width: 100%;
        height: var(--research-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .research-cms-preview-frame {
        display: block;
        width: var(--research-preview-width);
        min-width: var(--research-preview-width);
        height: var(--research-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--research-preview-scale));
        transform-origin: top left;
    }

    .research-cms-modal[hidden] {
        display: none;
    }

    .research-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .research-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .research-cms-modal-dialog {
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

    .research-cms-modal-close {
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

    .research-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .research-cms-side-kicker,
    .research-cms-eyebrow {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .research-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .research-cms-modal-header p,
    .research-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.6;
    }

    .research-cms-modal-panels {
        padding: 22px 24px 24px;
        background: linear-gradient(180deg, rgba(255, 251, 247, 0.92) 0%, #fffdfc 100%);
    }

    .research-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .research-cms-card-stack {
        display: grid;
        gap: 0;
    }

    .research-cms-card-editor {
        padding: 18px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf6 100%);
        display: none;
    }

    .research-cms-card-editor.is-active {
        display: block;
    }

    .research-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .research-cms-card-editor-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .research-cms-card-editor-head span {
        color: #8a7a73;
        font-size: 0.8rem;
    }

    .research-cms-image-dropzone-shell {
        width: 100%;
        margin: 0 auto;
    }

    .research-cms-image-dropzone {
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

    .research-cms-image-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .research-cms-image-dropzone-preview-column {
        display: flex;
        min-width: 0;
        min-height: 180px;
    }

    .research-cms-image-dropzone-media {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
    }

    .research-cms-image-dropzone-preview {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 180px;
        object-fit: cover;
        border-radius: 18px;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
    }

    .research-cms-image-dropzone-label {
        display: none;
        color: #7f1113;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
    }

    .research-cms-image-dropzone-upload {
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

    .research-cms-image-dropzone-icon {
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

    .research-cms-image-dropzone-upload-title {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .research-cms-image-dropzone-upload-copy {
        display: block;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .research-cms-image-dropzone-upload-button {
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

    .research-cms-image-dropzone-file {
        display: block;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.8rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .research-cms-image-dropzone-input {
        display: none;
    }

    .research-cms-image-dropzone-remove {
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

    .research-cms-image-dropzone-remove:hover {
        background: #7f1113;
        color: #fff8f1;
    }

    @media (max-width: 460px) {
        .research-cms-image-dropzone {
            grid-template-columns: 1fr;
        }

        .research-cms-image-dropzone-upload {
            min-height: 280px;
        }
    }

    @media (max-width: 640px) {
        .research-cms-image-dropzone-remove {
            top: 12px;
            right: 12px;
        }
    }

    .research-cms-image-dropzone-remove[hidden] {
        display: none;
    }

    .research-cms-upload-hint {
        display: block;
        margin-top: 8px;
        color: #7a6a63;
        line-height: 1.5;
    }

    .research-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .research-cms-modal.is-card-focus .research-cms-modal-header {
        display: none;
    }

    .research-cms-modal.is-card-focus {
        align-items: center !important;
    }

    .research-cms-modal.is-card-focus .research-cms-modal-dialog {
        width: min(760px, calc(100vw - 24px));
        max-width: min(760px, calc(100vw - 24px));
        border-radius: 30px;
        background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
        box-shadow: 0 30px 70px rgba(45, 8, 5, 0.2);
    }

    .research-cms-modal.is-card-focus .research-cms-modal-panels {
        padding: 18px;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf6 0%, #fffdfc 100%);
    }

    .research-cms-editor-panel.is-card-focus form {
        max-width: 680px;
        margin: 0 auto;
    }

    .research-cms-editor-panel.is-card-focus .research-cms-card-stack {
        gap: 0;
    }

    .research-cms-editor-panel.is-card-focus .research-cms-card-editor.is-active {
        padding: 22px;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow:
            0 16px 34px rgba(92, 12, 6, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .research-cms-editor-panel.is-card-focus .research-cms-card-editor.is-active .form-group + .form-group {
        margin-top: 14px;
    }

    .research-cms-modal.is-card-focus .research-cms-modal-close {
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(127, 17, 19, 0.08);
        font-size: 1.35rem;
    }

    @media (max-width: 768px) {
        .research-cms-workspace {
            --research-preview-width: 1440px;
            --research-preview-height: 1760px;
            --research-preview-scale: 0.58;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .research-cms-modal-dialog {
            width: calc(100vw - 20px);
            margin: 10px auto;
            max-height: calc(100vh - 20px);
        }

        .research-cms-modal-panels,
        .research-cms-modal-header {
            padding: 18px 16px;
        }

        .research-cms-form-grid {
            grid-template-columns: 1fr;
        }

        .research-cms-card-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<script>
    (() => {
        if (window.__researchCmsPreviewEditorReady) {
            return;
        }

        const RESEARCH_PREVIEW_MIN_LOADING_MS = 1500;
        const previewScript = document.querySelector('[data-research-preview-pages-json]');
        const previewPages = previewScript ? JSON.parse(previewScript.textContent || '{}') : {};
        let currentPreviewPage = 'overview';
        const modal = document.querySelector('[data-research-editor-modal]');
        const modalTitle = modal?.querySelector('#{{ $idPrefix }}-modal-title');
        const modalDescription = modal?.querySelector('[data-research-editor-description]');
        const panels = Array.from(document.querySelectorAll('[data-research-editor-panel]'));
        const frames = Array.from(document.querySelectorAll('[data-research-preview-frame]'));
        const previewNavBtns = Array.from(document.querySelectorAll('[data-research-preview-page]'));

        if (!modal || !frames.length) {
            return;
        }

        const getPreviewHtml = (page) => {
            return typeof previewPages[page] === 'string' ? previewPages[page]
                : (typeof previewPages['overview'] === 'string' ? previewPages['overview'] : '');
        };

        previewNavBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const page = btn.getAttribute('data-research-preview-page') || 'overview';
                if (page === currentPreviewPage) return;
                currentPreviewPage = page;
                previewNavBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                frames.forEach((frame) => loadFrame(frame));
            });
        });

        const closeEditor = () => {
            modal.querySelectorAll('[data-research-card-editor][data-research-unsaved="1"]').forEach((editor) => {
                editor.remove();
            });
            relabelCards();
            setActiveCardEditor();
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

            const target = modal.querySelector(`[data-research-card-editor][data-research-card-index="${cardIndex}"]`);
            if (!target) {
                return;
            }

            target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            target.querySelector('input, textarea')?.focus();
        };

        const openEditor = (sectionKey, label, options = {}) => {
            const isCardFocus = sectionKey === 'cards'
                && options.cardIndex !== null
                && options.cardIndex !== undefined
                && options.cardIndex !== '';

            panels.forEach((panel) => {
                const isActive = panel.getAttribute('data-research-editor-panel') === sectionKey;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);
            });

            modal.classList.toggle('is-card-focus', isCardFocus);

            if (modalTitle) {
                modalTitle.textContent = label || 'Edit research and extension section';
            }

            if (modalDescription) {
                if (sectionKey === 'cards') {
                    modalDescription.textContent = 'Update this service item shown in the services strip.';
                } else if (sectionKey === 'strategic-development-plan-header') {
                    modalDescription.textContent = 'Update the Strategic Development Plan label and intro text.';
                } else if (sectionKey === 'strategic-development-plan') {
                    modalDescription.textContent = 'Edit, add, or remove development priority items.';
                } else {
                    modalDescription.textContent = 'Update the page header section, title, and description.';
                }
            }

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(modal);
            }

            if (sectionKey === 'cards') {
                setActiveCardEditor(options.cardIndex ?? null);
                window.setTimeout(() => focusCardEditor(options.cardIndex ?? null), 40);
            }
        };

        const fitResearchPreview = (frame) => {
            const workspace = frame.closest('.research-cms-workspace');
            const shell = frame.closest('.research-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--research-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--research-preview-scale', `${scale}`);
        };

        const setResearchPreviewLoading = (frame, isLoading) => {
            if (frame.__researchPreviewLoadingTimeout) {
                window.clearTimeout(frame.__researchPreviewLoadingTimeout);
                frame.__researchPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__researchPreviewLoadingSession = (frame.__researchPreviewLoadingSession || 0) + 1;
                frame.__researchPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__researchPreviewLoadingSession || 0,
                },
            }));
        };

        const finishResearchPreviewLoading = (frame) => {
            const activeSession = frame.__researchPreviewLoadingSession || 0;
            const startedAt = frame.__researchPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, RESEARCH_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__researchPreviewLoadingTimeout) {
                window.clearTimeout(frame.__researchPreviewLoadingTimeout);
            }

            frame.__researchPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__researchPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__researchPreviewLoadingTimeout = null;
            }, remaining);
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
                    const styles = window.getComputedStyle(element);
                    const marginBottom = Number.parseFloat(styles.marginBottom) || 0;

                    return Math.max(maxBottom, element.offsetTop + element.offsetHeight + marginBottom);
                }, 0);

                return Math.max(1, Math.ceil(contentBottom));
            } catch (error) {
                console.warn('Unable to size research preview frame.', error);
                return 0;
            }
        };

        const syncResearchPreviewHeight = (frame, nextHeight) => {
            const workspace = frame.closest('.research-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--research-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
            fitResearchPreview(frame);
        };

        const scheduleResearchPreviewSync = (frame) => {
            if (!frame) {
                return;
            }

            if (frame.__researchPreviewSyncFrame !== undefined && frame.__researchPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__researchPreviewSyncFrame);
            }

            frame.__researchPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureFrame(frame);

                if (measuredHeight > 0) {
                    syncResearchPreviewHeight(frame, measuredHeight);
                } else {
                    fitResearchPreview(frame);
                }

                frame.__researchPreviewSyncFrame = null;
            });
        };

        const queueResearchPreviewSettledSync = (frame) => {
            scheduleResearchPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleResearchPreviewSync(frame), delay);
            });
            finishResearchPreviewLoading(frame);
        };

        const bindFrame = (frame) => {
            const doc = frame.contentDocument;
            if (!doc) {
                return;
            }

            if (typeof window.bindCmsPreviewScrollBridge === 'function') {
                window.bindCmsPreviewScrollBridge(frame);
            }

            doc.addEventListener('click', (event) => {
                const addCardTrigger = event.target.closest('[data-research-add-card-trigger]');
                if (addCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const cardIndex = addCard();
                    openEditor('cards', 'Add service', { cardIndex });
                    return;
                }

                const editCardTrigger = event.target.closest('[data-research-card-edit]');
                if (editCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = editCardTrigger.closest('[data-research-card-index]');
                    const cardIndex = card?.getAttribute('data-research-card-index') ?? null;
                    openEditor('cards', 'Edit service', { cardIndex });
                    return;
                }

                const deleteCardTrigger = event.target.closest('[data-research-card-delete]');
                if (deleteCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = deleteCardTrigger.closest('[data-research-card-index]');
                    const cardIndex = card?.getAttribute('data-research-card-index') ?? null;
                    void confirmDeleteCard(cardIndex);
                    return;
                }

                if (event.target.closest('[data-research-card-index]')) {
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
                    const rawCardIndex = sectionTrigger.getAttribute('data-research-card-index');
                    const cardIndex = rawCardIndex === null ? null : Number(rawCardIndex);

                    if (sectionKey === 'cards' && rawCardIndex === null) {
                        return;
                    }

                    openEditor(sectionKey, label, { cardIndex });
                    return;
                }

                const anchor = event.target.closest('a');
                if (anchor) {
                    event.preventDefault();
                }
            });

            const schedule = () => queueResearchPreviewSettledSync(frame);
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

        const loadFrame = (frame) => {
            setResearchPreviewLoading(frame, true);
            const html = getPreviewHtml(currentPreviewPage);
            if (typeof window.applyCmsPreviewFrameContent === 'function') {
                window.applyCmsPreviewFrameContent(frame, html);
            } else {
                frame.srcdoc = html;
            }
        };

        frames.forEach((frame) => {
            frame.addEventListener('load', () => {
                bindFrame(frame);
                queueResearchPreviewSettledSync(frame);
            });

            loadFrame(frame);
        });

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || typeof data.type !== 'string') return;

            if (data.type === 'cms-research-preview-height') {
                const targetFrame = frames.find((frame) => frame.contentWindow === event.source);
                if (targetFrame) {
                    syncResearchPreviewHeight(targetFrame, data.height);
                }
                return;
            }

            if (data.type === 'cms-research-edit') {
                const sectionKey = data.section || '';
                const labelMap = {
                    'page': 'Page Header',
                    'cards': 'Services',
                    'strategic-development-plan-header': 'Strategic Development Plan Header',
                    'strategic-development-plan': 'Development Priorities',
                };
                openEditor(sectionKey, labelMap[sectionKey] || 'Edit section', {});
                return;
            }

            if (data.type === 'cms-research-sdp-priority-add') {
                const index = addSdpPriority();
                openEditor('strategic-development-plan', 'Add Priority', { sdpIndex: index });
                return;
            }

            if (data.type === 'cms-research-sdp-priority-edit') {
                openEditor('strategic-development-plan', 'Edit Priority', { sdpIndex: data.index !== '' ? Number(data.index) : null });
                return;
            }

            if (data.type === 'cms-research-sdp-priority-delete') {
                void confirmDeleteSdpPriority(data.index !== '' ? Number(data.index) : null, data.label || '');
                return;
            }
        });

        document.querySelectorAll('[data-close-research-editor]').forEach((trigger) => {
            trigger.addEventListener('click', closeEditor);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeEditor();
            }
        });

        const cardTemplate = modal.querySelector('[data-research-card-template]');
        const cardStack = modal.querySelector('[data-research-card-stack]');
        const cardsForm = modal.querySelector('[data-research-cards-form]');
        const versionInput = modal.querySelector('[data-research-cards-version]');
        const activeCardIndexInput = modal.querySelector('[data-research-active-card-index]');

        const bumpCardsVersion = () => {
            if (versionInput) {
                versionInput.value = String(Date.now());
            }
        };

        const shouldTrackResearchCardField = (target) => {
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

        const bindResearchCardsDirtyTracking = () => {
            if (!cardsForm || cardsForm.dataset.researchDirtyTrackingBound === '1') {
                return;
            }

            cardsForm.dataset.researchDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackResearchCardField(event.target)) {
                    return;
                }

                bumpCardsVersion();
                relabelCards();
            };

            cardsForm.addEventListener('input', markDirty);
            cardsForm.addEventListener('change', markDirty);
        };

        const relabelCards = () => {
            const editors = Array.from(cardStack?.querySelectorAll('[data-research-card-editor]') ?? []);

            editors.forEach((editor, index) => {
                const displayNumber = index + 1;
                const headTitle = editor.querySelector('[data-research-card-editor-head] h4');
                const dropzoneTitle = editor.querySelector('.research-cms-image-dropzone-label');

                if (headTitle) {
                    headTitle.textContent = `Service ${displayNumber}`;
                }

                if (dropzoneTitle) {
                    dropzoneTitle.textContent = `Service ${displayNumber}`;
                }
            });
        };

        const submitCardsForm = () => {
            if (!cardsForm) {
                return;
            }

            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(cardsForm);
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

        const setActiveCardEditor = (cardIndex = null) => {
            const editors = Array.from(cardStack?.querySelectorAll('[data-research-card-editor]') ?? []);

            if (!editors.length) {
                if (activeCardIndexInput) {
                    activeCardIndexInput.value = '';
                }
                return;
            }

            let targetEditor = null;

            if (cardIndex !== null && cardIndex !== undefined) {
                targetEditor = editors.find((editor) => editor.getAttribute('data-research-card-index') === String(cardIndex)) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                editor.classList.toggle('is-active', editor === targetEditor);
            });

            if (activeCardIndexInput) {
                activeCardIndexInput.value = targetEditor?.getAttribute('data-research-card-index') || '';
            }
        };

        const nextCardIndex = () => {
            const indexes = Array.from(cardStack?.querySelectorAll('[data-research-card-editor]') ?? [])
                .map((editor) => Number(editor.getAttribute('data-research-card-index') || '0'))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const initResearchImageDropzones = (scope = document) => {
            scope.querySelectorAll('.research-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.researchDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-research-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-research-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-research-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-research-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-research-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-research-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-research-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-research-clear-image-for="${input.id}"]`);
                const imageField = input.dataset.researchImageFieldId
                    ? document.getElementById(input.dataset.researchImageFieldId)
                    : (input.closest('[data-research-card-editor]')?.querySelector('[data-research-image-field]') || null);

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.researchDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.researchDefaultSrc || '';

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;
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
                    if (event.target.closest('[data-research-clear-image-for]')) {
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

        const addCard = () => {
            if (!cardTemplate || !cardStack) {
                return null;
            }

            const index = nextCardIndex();
            const fragment = cardTemplate.content.cloneNode(true);

            fragment.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/__INDEX__/g, String(index));
            });

            fragment.querySelectorAll('[data-research-card-index]').forEach((element) => {
                element.setAttribute('data-research-card-index', String(index));
            });

            const editor = fragment.querySelector('[data-research-card-editor]');
            if (editor) {
                editor.setAttribute('data-research-unsaved', '1');
            }

            const dropzoneId = `{{ $idPrefix }}-research-card-image-${index}`;
            const dropzoneInput = fragment.querySelector('.research-cms-image-dropzone-input');
            const dropzoneLabel = fragment.querySelector('.research-cms-image-dropzone');
            const dropzonePreview = fragment.querySelector('[data-research-preview-for]');
            const dropzoneFileName = fragment.querySelector('[data-research-file-name-for]');
            const dropzoneTitle = fragment.querySelector('.research-cms-image-dropzone-label');
            const dropzoneRemove = fragment.querySelector('[data-research-clear-image-for]');

            if (dropzoneInput) {
                dropzoneInput.id = dropzoneId;
            }

            if (dropzoneLabel) {
                dropzoneLabel.setAttribute('data-research-dropzone-for', dropzoneId);
            }

            if (dropzonePreview) {
                dropzonePreview.setAttribute('data-research-preview-for', dropzoneId);
            }

            if (dropzoneFileName) {
                dropzoneFileName.setAttribute('data-research-file-name-for', dropzoneId);
            }

            if (dropzoneRemove) {
                dropzoneRemove.setAttribute('data-research-clear-image-for', dropzoneId);
            }

            if (dropzoneTitle) {
                dropzoneTitle.textContent = `Service ${index + 1}`;
            }

            cardStack.appendChild(fragment);
            initResearchImageDropzones(cardStack);
            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(cardStack);
            }
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor(index);
            focusCardEditor(index);

            return index;
        };

        modal.addEventListener('click', (event) => {
            const addTrigger = event.target.closest('[data-add-research-card]');
            if (addTrigger) {
                event.preventDefault();
                addCard();
                return;
            }
        });

        // --- SDP Priority Management ---
        const sdpTemplate = modal.querySelector('[data-research-sdp-template]');
        const sdpStack = modal.querySelector('[data-research-sdp-stack]');
        const sdpVersionInput = modal.querySelector('[data-research-sdp-version]');
        const activeSdpIndexInput = modal.querySelector('[data-research-active-sdp-index]');

        const bumpSdpVersion = () => {
            if (sdpVersionInput) {
                sdpVersionInput.value = String(Date.now());
            }
        };

        const relabelSdpPriorities = () => {
            const editors = Array.from(sdpStack?.querySelectorAll('[data-research-sdp-editor]') ?? []);
            editors.forEach((editor, index) => {
                const headTitle = editor.querySelector('[data-research-sdp-editor-head] h4');
                if (headTitle) headTitle.textContent = `Priority ${index + 1}`;
            });
        };

        const setActiveSdpEditor = (sdpIndex = null) => {
            const editors = Array.from(sdpStack?.querySelectorAll('[data-research-sdp-editor]') ?? []);
            if (!editors.length) {
                if (activeSdpIndexInput) activeSdpIndexInput.value = '';
                return;
            }
            let targetEditor = null;
            if (sdpIndex !== null && sdpIndex !== undefined) {
                targetEditor = editors.find((e) => e.getAttribute('data-research-sdp-index') === String(sdpIndex)) || null;
            }
            if (!targetEditor) targetEditor = editors[0] || null;
            editors.forEach((e) => e.classList.toggle('is-active', e === targetEditor));
            if (activeSdpIndexInput) {
                activeSdpIndexInput.value = targetEditor?.getAttribute('data-research-sdp-index') || '';
            }
        };

        const nextSdpIndex = () => {
            const indexes = Array.from(sdpStack?.querySelectorAll('[data-research-sdp-editor]') ?? [])
                .map((e) => Number(e.getAttribute('data-research-sdp-index') || '0'))
                .filter((v) => Number.isFinite(v));
            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const addSdpPriority = () => {
            if (!sdpTemplate || !sdpStack) return null;
            const index = nextSdpIndex();
            const fragment = sdpTemplate.content.cloneNode(true);
            fragment.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/__INDEX__/g, String(index));
            });
            fragment.querySelectorAll('[data-research-sdp-index]').forEach((el) => {
                el.setAttribute('data-research-sdp-index', String(index));
            });
            const editor = fragment.querySelector('[data-research-sdp-editor]');
            if (editor) editor.setAttribute('data-research-sdp-unsaved', '1');
            const headTitle = fragment.querySelector('[data-research-sdp-editor-head] h4');
            if (headTitle) headTitle.textContent = `Priority ${index + 1}`;
            sdpStack.appendChild(fragment);
            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(sdpStack);
            }
            bumpSdpVersion();
            relabelSdpPriorities();
            setActiveSdpEditor(index);
            return index;
        };

        const confirmDeleteSdpPriority = async (sdpIndex, label) => {
            if (sdpIndex === null || sdpIndex === undefined) return;
            const displayLabel = label || `Priority ${sdpIndex + 1}`;
            if (!window.confirm(`Delete "${displayLabel}"? This cannot be undone.`)) return;
            const editor = sdpStack?.querySelector(`[data-research-sdp-editor][data-research-sdp-index="${sdpIndex}"]`);
            if (editor) {
                editor.remove();
                bumpSdpVersion();
                relabelSdpPriorities();
                setActiveSdpEditor();
            }
        };

        document.querySelectorAll('.{{ $formClass }}').forEach((form) => {
            form.addEventListener('submit', () => {
                if (typeof window.syncRichTextEditors === 'function') {
                    window.syncRichTextEditors(form);
                }
            });
        });

        window.addEventListener('resize', () => {
            frames.forEach((frame) => scheduleResearchPreviewSync(frame));
        });

        window.addEventListener('cms:tab-activated', (event) => {
            const panel = event.detail?.panel;

            frames.forEach((frame) => {
                if (panel && panel.contains(frame)) {
                    loadFrame(frame);
                    window.setTimeout(() => scheduleResearchPreviewSync(frame), 40);
                    window.setTimeout(() => scheduleResearchPreviewSync(frame), 180);
                }
            });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                frames.forEach((frame) => scheduleResearchPreviewSync(frame));
            });

            document.querySelectorAll('.research-cms-preview-frame-shell').forEach((shell) => {
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
                frames.forEach((frame) => scheduleResearchPreviewSync(frame));
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('pageshow', () => {
            frames.forEach((frame) => scheduleResearchPreviewSync(frame));
        });

        window.addEventListener('load', () => {
            frames.forEach((frame) => scheduleResearchPreviewSync(frame));
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                frames.forEach((frame) => scheduleResearchPreviewSync(frame));
            }
        });

        window.refreshResearchCmsPreview = (scope) => {
            const scopedFrames = scope
                ? Array.from(scope.querySelectorAll('[data-research-preview-frame]'))
                : frames;

            scopedFrames.forEach((frame) => loadFrame(frame));
        };

        relabelCards();
        setActiveCardEditor();
        initResearchImageDropzones(modal);
        bindResearchCardsDirtyTracking();
        relabelSdpPriorities();
        setActiveSdpEditor();
        window.__researchCmsPreviewEditorReady = true;
    })();
</script>

<style>
    .research-cms-preview-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-content: flex-start;
        margin-bottom: 18px;
    }

    .research-cms-preview-nav-btn {
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

    .research-cms-preview-nav-btn:hover,
    .research-cms-preview-nav-btn:focus-visible {
        outline: none;
    }

    .research-cms-preview-nav-btn.is-active {
        background: #800000;
        border-color: #800000;
        color: #fff;
    }


</style>
