@php
    $homeDefaults = \App\Support\HomeCmsContent::defaults();
    $homeEditorData = \App\Support\HomeCmsContent::fromInput($homeEditorData ?? [], null);
    $updatesEditor = $homeEditorData['updates'] ?? $homeDefaults['updates'];
    $campusTourEditor = $homeEditorData['campus_tour'] ?? $homeDefaults['campus_tour'];
    $campusTourFacilitiesEditor = array_values(is_array($campusTourEditor['facilities'] ?? null) ? $campusTourEditor['facilities'] : []);
    $quickLinksEditor = $homeEditorData['quick_links'] ?? $homeDefaults['quick_links'];
    $feedbackEditor = $homeEditorData['feedback'] ?? $homeDefaults['feedback'];
    $feedbackQuestionsEditor = $feedbackEditor['questions'] ?? ($homeDefaults['feedback']['questions'] ?? []);
    $slidesEditor = $homeEditorData['carousel_slides'] ?? $homeDefaults['carousel_slides'];
    $carouselPageLabels = [
        'About',
        'Academics',
        'Students',
        'Events',
        'Research & Extension',
    ];
    $formClass = $homeEditorFormClass ?? 'cms-save-form';
    $submitRoute = $homeEditorSubmitRoute;
    $submitMode = $homeEditorSubmitMode ?? 'save';
    $requestId = (int) ($homeEditorRequestId ?? 0);
    $status = strtolower((string) ($homeEditorStatus ?? ''));
    $idPrefix = trim((string) ($homeEditorIdPrefix ?? 'home-editor'));
    $campusTourVideoId = $idPrefix.'-home-campus-tour-video';
    $campusTourVideoPath = (string) ($campusTourEditor['avp_video'] ?? '');
    $campusTourVideoUrl = $campusTourVideoPath !== ''
        ? \App\Support\ImageStorage::url($campusTourVideoPath, null)
        : null;
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
    $homePreviewPages = [
        'overview' => $homePreviewHtml,
        'feedback_form' => view('public.feedback', [
            'cmsPreview' => true,
            'homeFeedbackPreview' => $feedbackEditor,
        ])->render(),
    ];
@endphp

<div class="home-cms-workspace">
    <div class="home-cms-preview-shell">
        <div class="home-cms-preview-head">
            <div class="home-cms-preview-nav" aria-label="Home preview pages">
                <button type="button" class="home-cms-preview-nav-btn is-active" data-home-preview-page="overview">Overview</button>
                <button type="button" class="home-cms-preview-nav-btn" data-home-preview-page="feedback_form">Feedback Form</button>
            </div>
        </div>

        <div class="home-cms-preview-frame-shell">
            <div class="home-cms-preview-stage">
                <div class="home-cms-preview-canvas">
                    <iframe
                        title="Homepage preview"
                        class="home-cms-preview-frame"
                        data-home-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="home-cms-modal" data-home-editor-modal hidden>
    <div class="home-cms-modal-backdrop" data-close-home-editor></div>

    <div class="home-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="home-cms-modal-close" data-close-home-editor aria-label="Close editor">&times;</button>

        <div class="home-cms-modal-header">
            <span class="home-cms-side-kicker">Homepage Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit homepage section</h3>
            <p data-home-editor-description>Select a section from the preview to start editing.</p>
        </div>

        <div class="home-cms-modal-panels">
            <section class="home-cms-editor-panel" data-home-editor-panel="carousel" hidden>
                <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="home">
                    <input type="hidden" name="section_key" value="carousel">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="carousel-manager-grid">
                        @for($idx = 0; $idx < 5; $idx++)
                            @php
                                $slide = $slidesEditor[$idx] ?? ['title' => '', 'subtitle' => '', 'image' => ''];
                                $slideInputId = $idPrefix.'-home-slide-'.$idx;
                                $defaultSlideImage = $homeDefaults['carousel_slides'][$idx]['image'] ?? 'assets/static_img/pupillar.jpeg';
                                $slidePreview = \App\Support\HomeCmsContent::resolveImagePath($slide['image'] ?? '', $defaultSlideImage);
                                $pageLabel = $carouselPageLabels[$idx] ?? 'Page';
                            @endphp
                            <div class="carousel-manager-item">
                                <input type="hidden" name="home[carousel][{{ $idx }}][image]" value="{{ $slide['image'] }}" data-home-carousel-image-field>

                                <label class="home-dropzone slide-dropzone" for="{{ $slideInputId }}" data-home-carousel-dropzone-for="{{ $slideInputId }}">
                                    <span class="home-cms-carousel-media">
                                        <img
                                            src="{{ $slidePreview }}"
                                            alt="Slide {{ $idx + 1 }} preview"
                                            class="slide-preview"
                                            data-home-carousel-preview-for="{{ $slideInputId }}"
                                            data-home-carousel-default-src="{{ asset($defaultSlideImage) }}"
                                        >
                                        <button
                                            type="button"
                                            class="home-cms-carousel-remove"
                                            data-home-carousel-clear-for="{{ $slideInputId }}"
                                            aria-label="Delete slide {{ $idx + 1 }} image"
                                            title="Delete image"
                                        >
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="dropzone-label">Slide {{ $idx + 1 }}: {{ $pageLabel }}</span>
                                    <span class="dropzone-file-name" data-home-carousel-file-name-for="{{ $slideInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </label>
                                <input
                                    id="{{ $slideInputId }}"
                                    class="home-dropzone-input"
                                    type="file"
                                    name="home[carousel][{{ $idx }}][image_file]"
                                    accept="image/*"
                                >
                                <div class="form-group">
                                    <label for="{{ $slideInputId }}-title">{{ $pageLabel }} title</label>
                                    <input
                                        id="{{ $slideInputId }}-title"
                                        type="text"
                                        name="home[carousel][{{ $idx }}][title]"
                                        maxlength="255"
                                        value="{{ $slide['title'] }}"
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="{{ $slideInputId }}-subtitle">{{ $pageLabel }} subtitle</label>
                                    <input
                                        id="{{ $slideInputId }}-subtitle"
                                        type="text"
                                        name="home[carousel][{{ $idx }}][subtitle]"
                                        maxlength="255"
                                        value="{{ $slide['subtitle'] }}"
                                    >
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="home-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Hero Carousel') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="home-cms-editor-panel" data-home-editor-panel="updates" hidden>
                <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="home">
                    <input type="hidden" name="section_key" value="updates">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="home-cms-form-grid">
                        <div class="form-group">
                            <label>Section Tag</label>
                            <input type="text" name="home[updates][tag]" maxlength="80" value="{{ $updatesEditor['tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Title</label>
                            <input type="text" name="home[updates][title]" maxlength="255" value="{{ $updatesEditor['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'home[updates][description]',
                            'value' => $updatesEditor['description'] ?? '',
                            'placeholder' => 'Write the description for the updates section...',
                            'characterLimit' => 100,
                            'counterMode' => 'limit',
                        ])
                    </div>

                    <div class="home-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Campus Updates') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="home-cms-editor-panel" data-home-editor-panel="campus_tour_video" hidden>
                <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="home">
                    <input type="hidden" name="section_key" value="campus_tour_video">
                    <input type="hidden" name="home[campus_tour][avp_video]" value="{{ $campusTourEditor['avp_video'] ?? '' }}" data-home-campus-tour-video-field>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="home-cms-campus-tour-video-shell">
                        <label class="home-dropzone home-campus-tour-video-dropzone" for="{{ $campusTourVideoId }}">
                            <span class="dropzone-label">University AVP Video</span>
                            <span class="dropzone-file-name" data-home-campus-tour-video-name data-empty-text="Upload MP4, WebM, or MOV video">Upload MP4, WebM, or MOV video</span>
                            <span class="home-cms-campus-tour-video-preview-wrap">
                                <video
                                    class="home-cms-campus-tour-video-preview"
                                    data-home-campus-tour-video-preview
                                    controls
                                    playsinline
                                    preload="metadata"
                                    @if($campusTourVideoUrl) src="{{ $campusTourVideoUrl }}" @endif
                                    @if(!$campusTourVideoUrl) hidden @endif
                                ></video>
                                <span class="home-cms-campus-tour-video-empty" data-home-campus-tour-video-empty @if($campusTourVideoUrl) hidden @endif>No AVP video uploaded yet.</span>
                                <button
                                    type="button"
                                    class="home-cms-carousel-remove"
                                    data-home-campus-tour-video-clear
                                    aria-label="Delete AVP video"
                                    title="Delete video"
                                    @if(!$campusTourVideoUrl) hidden @endif
                                >
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </span>
                        </label>
                        <input
                            id="{{ $campusTourVideoId }}"
                            class="home-dropzone-input"
                            type="file"
                            name="home[campus_tour][avp_video_file]"
                            accept="video/mp4,video/webm,video/quicktime"
                            data-home-campus-tour-video-input
                        >
                    </div>

                    <div class="home-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Campus Tour AVP') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="home-cms-editor-panel" data-home-editor-panel="campus_tour_facilities" hidden>
                <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab_key" value="home">
                    <input type="hidden" name="section_key" value="campus_tour_facilities">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="home-cms-section-toolbar">
                        <div>
                            <h4 class="home-cms-section-toolbar-title">Facility Cards</h4>
                            <p class="home-cms-section-toolbar-copy">Add, edit, or delete cards. Update each card name and image.</p>
                        </div>
                        <button type="button" class="home-cms-inline-btn" data-home-campus-tour-facility-add>Add Facility Card</button>
                    </div>

                    <div class="campus-tour-manager-grid" data-home-campus-tour-facility-stack>
                        @foreach($campusTourFacilitiesEditor as $index => $facility)
                            @php
                                $facilityInputId = $idPrefix.'-home-campus-tour-facility-'.$index;
                                $facilityImage = (string) ($facility['image'] ?? '');
                                $facilityName = (string) ($facility['name'] ?? '');
                                $facilityPreview = \App\Support\HomeCmsContent::resolveImagePath($facilityImage, 'assets/static_img/pupillar.jpeg');
                            @endphp
                            <article class="campus-tour-manager-item" data-home-campus-tour-facility-card data-home-campus-tour-facility-index="{{ $index }}">
                                <div class="campus-tour-manager-item-head">
                                    <h4 data-home-campus-tour-facility-title>Facility Card {{ $loop->iteration }}</h4>
                                    <button type="button" class="home-cms-inline-btn danger" data-home-campus-tour-facility-delete>Delete</button>
                                </div>

                                <div class="form-group">
                                    <label>Card Name</label>
                                    <input type="text" name="home[campus_tour][facilities][{{ $index }}][name]" maxlength="255" value="{{ $facilityName }}" placeholder="Enter facility name">
                                </div>

                                <input type="hidden" name="home[campus_tour][facilities][{{ $index }}][image]" value="{{ $facilityImage }}" data-home-campus-tour-facility-image-field>

                                <label class="home-dropzone campus-tour-dropzone" for="{{ $facilityInputId }}" data-home-campus-tour-facility-dropzone-for="{{ $facilityInputId }}">
                                    <span class="home-cms-carousel-media">
                                        <img
                                            src="{{ $facilityPreview }}"
                                            alt="Facility {{ $index + 1 }} preview"
                                            class="slide-preview"
                                            data-home-campus-tour-facility-preview-for="{{ $facilityInputId }}"
                                            data-home-campus-tour-facility-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                        >
                                        <button
                                            type="button"
                                            class="home-cms-carousel-remove"
                                            data-home-campus-tour-facility-clear-for="{{ $facilityInputId }}"
                                            aria-label="Delete facility image {{ $index + 1 }}"
                                            title="Delete image"
                                        >
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                    <span class="dropzone-label">Facility {{ $index + 1 }}</span>
                                    <span class="dropzone-file-name" data-home-campus-tour-facility-file-name-for="{{ $facilityInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                </label>
                                <input
                                    id="{{ $facilityInputId }}"
                                    class="home-dropzone-input"
                                    type="file"
                                    name="home[campus_tour][facilities][{{ $index }}][image_file]"
                                    accept="image/*"
                                    data-home-campus-tour-facility-input
                                >
                            </article>
                        @endforeach
                    </div>

                    <template data-home-campus-tour-facility-template>
                        <article class="campus-tour-manager-item" data-home-campus-tour-facility-card data-home-campus-tour-facility-index="__INDEX__">
                            <div class="campus-tour-manager-item-head">
                                <h4 data-home-campus-tour-facility-title>Facility Card __NUMBER__</h4>
                                <button type="button" class="home-cms-inline-btn danger" data-home-campus-tour-facility-delete>Delete</button>
                            </div>

                            <div class="form-group">
                                <label>Card Name</label>
                                <input type="text" name="home[campus_tour][facilities][__INDEX__][name]" maxlength="255" value="" placeholder="Enter facility name">
                            </div>

                            <input type="hidden" name="home[campus_tour][facilities][__INDEX__][image]" value="" data-home-campus-tour-facility-image-field>

                            <label class="home-dropzone campus-tour-dropzone" for="__INPUT_ID__" data-home-campus-tour-facility-dropzone-for="__INPUT_ID__">
                                <span class="home-cms-carousel-media">
                                    <img
                                        src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                        alt="Facility __NUMBER__ preview"
                                        class="slide-preview"
                                        data-home-campus-tour-facility-preview-for="__INPUT_ID__"
                                        data-home-campus-tour-facility-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                    >
                                    <button
                                        type="button"
                                        class="home-cms-carousel-remove"
                                        data-home-campus-tour-facility-clear-for="__INPUT_ID__"
                                        aria-label="Delete facility image __NUMBER__"
                                        title="Delete image"
                                        hidden
                                    >
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </span>
                                <span class="dropzone-label">Facility __NUMBER__</span>
                                <span class="dropzone-file-name" data-home-campus-tour-facility-file-name-for="__INPUT_ID__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                            </label>
                            <input
                                id="__INPUT_ID__"
                                class="home-dropzone-input"
                                type="file"
                                name="home[campus_tour][facilities][__INDEX__][image_file]"
                                accept="image/*"
                                data-home-campus-tour-facility-input
                            >
                        </article>
                    </template>

                    <div class="home-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Campus Tour Facilities') }}
                        </button>
                    </div>
                </form>
            </section>

            

            <section class="home-cms-editor-panel" data-home-editor-panel="feedback" hidden>
                <div data-home-feedback-section-shell>
                    <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tab_key" value="home">
                        <input type="hidden" name="section_key" value="feedback">
                        @if($requestId > 0)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        <div class="home-cms-form-grid">
                            <div class="form-group">
                                <label>Section Tag</label>
                                <input type="text" name="home[feedback][tag]" maxlength="80" value="{{ $feedbackEditor['tag'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Button Label</label>
                                <input type="text" name="home[feedback][button_label]" maxlength="120" value="{{ $feedbackEditor['button_label'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Banner Title</label>
                            <input type="text" name="home[feedback][title]" maxlength="255" value="{{ $feedbackEditor['title'] ?? '' }}">
                        </div>

                        <div class="form-group">
                            <label>Banner Description</label>
                            @include('partials.rich_text_editor', [
                                'name' => 'home[feedback][description]',
                                'value' => $feedbackEditor['description'] ?? '',
                                'placeholder' => 'Write the supporting copy shown in the feedback banner...',
                                'characterLimit' => 100,
                                'counterMode' => 'limit',
                            ])
                        </div>

                        @foreach($feedbackQuestionsEditor as $index => $item)
                            <input type="hidden" name="home[feedback][questions][{{ $index }}][question]" value="{{ $item['question'] ?? '' }}">
                        @endforeach

                        <div class="home-cms-modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                                {{ $submitLabel('Feedback Form') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div data-home-feedback-question-shell>
                    <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-home-feedback-form>
                        @csrf
                        <input type="hidden" name="tab_key" value="home">
                        <input type="hidden" name="section_key" value="feedback">
                        <input type="hidden" name="home_feedback_questions_version" value="0" data-home-feedback-questions-version>
                        <input type="hidden" name="home_active_feedback_question_index" value="" data-home-active-feedback-question-index>
                        <input type="hidden" name="home[feedback][tag]" value="{{ $feedbackEditor['tag'] ?? '' }}">
                        <input type="hidden" name="home[feedback][button_label]" value="{{ $feedbackEditor['button_label'] ?? '' }}">
                        <input type="hidden" name="home[feedback][title]" value="{{ $feedbackEditor['title'] ?? '' }}">
                        <input type="hidden" name="home[feedback][description]" value="{{ $feedbackEditor['description'] ?? '' }}">
                        @if($requestId > 0)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        <div class="home-cms-card-stack" data-home-feedback-question-stack>
                            @foreach($feedbackQuestionsEditor as $index => $item)
                                <article class="home-cms-card-editor" data-home-feedback-question-editor data-home-feedback-question-index="{{ $index }}">
                                    <div class="home-cms-card-editor-head">
                                        <h4>Question {{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</h4>
                                        <span>Ratings: 4 / 3 / 2 / 1</span>
                                    </div>

                                    <div class="form-group">
                                        <label>Question</label>
                                        <textarea name="home[feedback][questions][{{ $index }}][question]" rows="5">{{ $item['question'] ?? '' }}</textarea>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <template data-home-feedback-question-template>
                            <article class="home-cms-card-editor" data-home-feedback-question-editor data-home-feedback-question-index="__INDEX__">
                                <div class="home-cms-card-editor-head">
                                    <h4>Question __NUMBER__</h4>
                                    <span>Ratings: 4 / 3 / 2 / 1</span>
                                </div>

                                <div class="form-group">
                                    <label>Question</label>
                                    <textarea name="home[feedback][questions][__INDEX__][question]" rows="5"></textarea>
                                </div>
                            </article>
                        </template>

                        <div class="home-cms-modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                                {{ $submitLabel('Feedback Question') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-home-preview-json>
{!! json_encode($homePreviewPages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .home-cms-workspace {
        --home-preview-width: 1520px;
        --home-preview-height: 1800px;
        --home-preview-min-height: 0px;
        --home-preview-scale: 1;
        --home-preview-scaled-width: calc(var(--home-preview-width) * var(--home-preview-scale));
        --home-preview-scaled-height: calc(var(--home-preview-height) * var(--home-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .home-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .home-cms-preview-head {
        margin-bottom: 18px;
    }

    .home-cms-preview-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-content: flex-start;
    }

    .home-cms-preview-nav-btn {
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

    .home-cms-preview-nav-btn.is-active {
        background: #800000;
        border-color: #800000;
        color: #fff;
    }

    .home-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .home-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .home-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--home-preview-scaled-width);
        max-width: 100%;
        height: var(--home-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .home-cms-preview-frame {
        display: block;
        width: var(--home-preview-width);
        min-width: var(--home-preview-width);
        height: var(--home-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--home-preview-scale));
        transform-origin: top left;
    }

    .home-cms-modal[hidden] {
        display: none;
    }

    .home-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .home-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .home-cms-modal-dialog {
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

    .home-cms-modal-close {
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

    .home-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .home-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .home-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.55;
    }

    .home-cms-modal-panels {
        padding: 22px 24px 24px;
    }

    .home-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .home-cms-form-grid > * {
        min-width: 0;
    }

    .home-cms-card-stack {
        display: grid;
        gap: 14px;
    }

    .home-cms-card-editor {
        padding: 16px;
        border: 1px solid #efe3dc;
        border-radius: 16px;
        background: #fff;
        display: none;
    }

    .home-cms-card-editor.is-active {
        display: block;
    }

    .home-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }

    .home-cms-card-editor-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .home-cms-card-editor-head span {
        color: #8a7a73;
        font-size: 0.8rem;
    }

    .home-cms-section-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 22px;
        margin-bottom: 16px;
        padding: 16px 18px;
        border: 1px solid #efe3dc;
        border-radius: 16px;
        background: linear-gradient(180deg, #fffaf7 0%, #fff 100%);
    }

    .home-cms-section-toolbar-title {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .home-cms-section-toolbar-copy {
        margin: 6px 0 0;
        color: #7d6d65;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .home-cms-carousel-meta-grid {
        margin-top: 18px;
        align-items: start;
    }

    .home-dropzone {
        border: 1px dashed #d4af37;
        border-radius: 18px;
        padding: 14px;
        display: block;
        cursor: pointer;
        background: linear-gradient(180deg, #fffdf8 0%, #fff8ee 100%);
    }

    .home-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .home-dropzone-input {
        display: none;
    }

    .carousel-manager-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .campus-tour-manager-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        align-items: start;
    }

    .carousel-manager-item {
        min-width: 0;
        padding: 12px;
        border: 1px solid #efe3dc;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(92, 12, 6, 0.04);
    }

    .campus-tour-manager-item {
        min-width: 0;
        padding: 12px;
        border: 1px solid #efe3dc;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(92, 12, 6, 0.04);
    }

    .campus-tour-manager-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .campus-tour-manager-item-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 0.95rem;
    }

    .home-cms-inline-btn {
        border: 1px solid #d4af37;
        background: #fff8e7;
        color: #5c0000;
        border-radius: 999px;
        padding: 7px 12px;
        font: inherit;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
    }

    .home-cms-inline-btn.danger {
        border-color: rgba(127, 17, 19, 0.3);
        background: rgba(127, 17, 19, 0.08);
        color: #7f1113;
    }

    .slide-dropzone {
        min-height: 0;
        margin-bottom: 14px;
        text-align: center;
    }

    .carousel-manager-item .form-group {
        margin-bottom: 12px;
    }

    .carousel-manager-item .form-group:last-child {
        margin-bottom: 0;
    }

    .campus-tour-dropzone {
        min-height: 0;
        text-align: center;
    }

    .home-cms-campus-tour-video-shell {
        display: block;
    }

    .home-campus-tour-video-dropzone {
        text-align: center;
    }

    .home-cms-campus-tour-video-preview-wrap {
        position: relative;
        display: block;
        margin-top: 10px;
        border-radius: 14px;
        overflow: hidden;
        background: #f1e7dd;
        min-height: 220px;
    }

    .home-cms-campus-tour-video-preview {
        width: 100%;
        height: 320px;
        display: block;
        object-fit: cover;
        background: #120304;
    }

    .home-cms-campus-tour-video-empty {
        min-height: 220px;
        display: grid;
        place-items: center;
        color: #6f625c;
        font-weight: 600;
        padding: 18px;
    }

    .home-cms-carousel-media {
        position: relative;
        display: block;
        margin-bottom: 12px;
    }

    .slide-preview {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 14px;
        display: block;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
    }

    .home-cms-carousel-remove {
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

    .home-cms-carousel-remove:hover {
        background: #7f1113;
        color: #fff8f1;
    }

    .home-cms-carousel-remove[hidden] {
        display: none;
    }

    .dropzone-label {
        display: block;
        font-weight: 700;
        margin-bottom: 6px;
        color: #5c0000;
        font-size: 1.02rem;
    }

    .dropzone-file-name {
        display: block;
        font-size: 0.88rem;
        color: #6f625c;
        line-height: 1.5;
        word-break: break-word;
    }

    .home-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    [data-home-quick-links-card-shell] {
        display: none;
    }

    [data-home-feedback-question-shell] {
        display: none;
    }

    .home-cms-editor-panel.is-card-focus [data-home-quick-links-section-shell] {
        display: none;
    }

    .home-cms-editor-panel.is-card-focus [data-home-quick-links-card-shell] {
        display: block;
    }

    .home-cms-editor-panel.is-card-focus [data-home-feedback-section-shell] {
        display: none;
    }

    .home-cms-editor-panel.is-card-focus [data-home-feedback-question-shell] {
        display: block;
    }

    .home-cms-modal.is-card-focus .home-cms-modal-header {
        display: none;
    }

    .home-cms-modal.is-card-focus .home-cms-modal-dialog {
        width: min(620px, calc(100vw - 24px));
        max-width: min(620px, calc(100vw - 24px));
        border-radius: 30px;
        background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
        box-shadow: 0 30px 70px rgba(45, 8, 5, 0.2);
    }

    .home-cms-modal.is-card-focus .home-cms-modal-panels {
        padding: 18px;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf6 0%, #fffdfc 100%);
    }

    .home-cms-editor-panel.is-card-focus form {
        max-width: 540px;
        margin: 0 auto;
    }

    .home-cms-editor-panel.is-card-focus .home-cms-form-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 8px;
    }

    .home-cms-editor-panel.is-card-focus .home-cms-card-stack {
        gap: 0;
    }

    .home-cms-editor-panel.is-card-focus [data-home-card-panel-meta],
    .home-cms-editor-panel.is-card-focus [data-home-card-editor-head] {
        display: none;
    }

    .home-cms-editor-panel.is-card-focus .home-cms-card-editor.is-active {
        padding: 22px;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow:
            0 16px 34px rgba(92, 12, 6, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .home-cms-editor-panel.is-card-focus .home-cms-card-editor.is-active .form-group + .form-group {
        margin-top: 10px;
    }

    .home-cms-modal.is-card-focus .home-cms-modal-close {
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(127, 17, 19, 0.08);
        font-size: 1.35rem;
    }

    @media (max-width: 768px) {
        .home-cms-workspace {
            --home-preview-width: 1440px;
            --home-preview-height: 1760px;
            --home-preview-min-height: 0px;
            --home-preview-scale: 0.58;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .home-cms-preview-head,
        .home-cms-card-editor-head,
        .home-cms-section-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .home-cms-form-grid {
            grid-template-columns: 1fr;
        }

        .carousel-manager-grid {
            grid-template-columns: 1fr;
        }

        .campus-tour-manager-grid {
            grid-template-columns: 1fr;
        }

        .home-cms-modal-dialog {
            width: min(100vw - 20px, 1080px);
            max-height: calc(100vh - 20px);
            margin: 10px auto;
        }

        .home-cms-modal-header,
        .home-cms-modal-panels {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

<script>
    (() => {
        if (window.__homeCmsPreviewEditorReady) {
            return;
        }

        const HOME_PREVIEW_MIN_LOADING_MS = 1500;
        const HOME_PREVIEW_ROUTE_STORAGE_KEY = `cms:home-preview-route:${window.location.pathname}`;
        let homePreviewFitFrame = null;
        let currentHomePreviewRoute = readStoredHomePreviewRoute();

        function readStoredHomePreviewRoute() {
            try {
                return window.localStorage.getItem(HOME_PREVIEW_ROUTE_STORAGE_KEY) || 'overview';
            } catch (_) {
                return 'overview';
            }
        }

        function persistHomePreviewRoute(routeKey) {
            try {
                window.localStorage.setItem(HOME_PREVIEW_ROUTE_STORAGE_KEY, String(routeKey || 'overview'));
            } catch (_) {
                // Ignore storage access failures and keep the in-memory state.
            }
        }

        function syncEditorsInScope(scope) {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
        }

        function initHomeCarouselDropzones(scope = document) {
            scope.querySelectorAll('.home-dropzone-input').forEach((input) => {
                if (input.dataset.homeCarouselDropzoneBound === '1') {
                    return;
                }

                const dropzone = scope.querySelector(`[data-home-carousel-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-home-carousel-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-home-carousel-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-home-carousel-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-home-carousel-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-home-carousel-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-home-carousel-clear-for="${input.id}"]`)
                    || document.querySelector(`[data-home-carousel-clear-for="${input.id}"]`);
                const imageField = input.closest('.carousel-manager-item')?.querySelector('[data-home-carousel-image-field]') || null;

                if (!dropzone || !fileNameEl || !previewEl) {
                    return;
                }

                input.dataset.homeCarouselDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl.dataset.homeCarouselDefaultSrc || '';

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
                    previewEl.src = URL.createObjectURL(file);
                    syncRemoveState();
                };

                input.addEventListener('change', () => {
                    applyFile(input.files && input.files[0] ? input.files[0] : null);
                });

                dropzone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    dropzone.classList.add('dragover');
                });

                dropzone.addEventListener('dragleave', () => {
                    dropzone.classList.remove('dragover');
                });

                dropzone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('dragover');

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
                    if (defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncRemoveState();
            });
        }

        function initHomeCampusTourFacilityDropzones(scope = document) {
            scope.querySelectorAll('[data-home-campus-tour-facility-input]').forEach((input) => {
                if (input.dataset.homeCampusTourFacilityBound === '1') {
                    return;
                }

                const dropzone = scope.querySelector(`[data-home-campus-tour-facility-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-home-campus-tour-facility-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-home-campus-tour-facility-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-home-campus-tour-facility-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-home-campus-tour-facility-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-home-campus-tour-facility-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-home-campus-tour-facility-clear-for="${input.id}"]`)
                    || document.querySelector(`[data-home-campus-tour-facility-clear-for="${input.id}"]`);
                const imageField = input.closest('.campus-tour-manager-item')?.querySelector('[data-home-campus-tour-facility-image-field]') || null;

                if (!dropzone || !fileNameEl || !previewEl) {
                    return;
                }

                input.dataset.homeCampusTourFacilityBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl.dataset.homeCampusTourFacilityDefaultSrc || '';

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
                    previewEl.src = URL.createObjectURL(file);
                    syncRemoveState();
                };

                input.addEventListener('change', () => {
                    applyFile(input.files && input.files[0] ? input.files[0] : null);
                });

                dropzone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    dropzone.classList.add('dragover');
                });

                dropzone.addEventListener('dragleave', () => {
                    dropzone.classList.remove('dragover');
                });

                dropzone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('dragover');

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
                    if (defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncRemoveState();
            });
        }

        function initHomeCampusTourVideoInput(scope = document) {
            const input = scope.querySelector('[data-home-campus-tour-video-input]')
                || document.querySelector('[data-home-campus-tour-video-input]');
            const preview = scope.querySelector('[data-home-campus-tour-video-preview]')
                || document.querySelector('[data-home-campus-tour-video-preview]');
            const clearButton = scope.querySelector('[data-home-campus-tour-video-clear]')
                || document.querySelector('[data-home-campus-tour-video-clear]');
            const fileName = scope.querySelector('[data-home-campus-tour-video-name]')
                || document.querySelector('[data-home-campus-tour-video-name]');
            const empty = scope.querySelector('[data-home-campus-tour-video-empty]')
                || document.querySelector('[data-home-campus-tour-video-empty]');
            const field = scope.querySelector('[data-home-campus-tour-video-field]')
                || document.querySelector('[data-home-campus-tour-video-field]');

            if (!input || !preview || !fileName || input.dataset.homeCampusTourVideoBound === '1') {
                return;
            }

            input.dataset.homeCampusTourVideoBound = '1';
            const emptyText = fileName.dataset.emptyText || 'Upload MP4, WebM, or MOV video';

            const updateState = () => {
                const hasStored = Boolean((field?.value || '').trim() !== '');
                const hasSelected = Boolean(input.files && input.files[0]);
                const hasMedia = hasStored || hasSelected;
                clearButton && (clearButton.hidden = !hasMedia);
                if (!hasMedia) {
                    preview.hidden = true;
                    if (empty) {
                        empty.hidden = false;
                    }
                }
            };

            input.addEventListener('change', () => {
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) {
                    updateState();
                    return;
                }

                fileName.textContent = `Selected: ${file.name}`;
                preview.src = URL.createObjectURL(file);
                preview.hidden = false;
                if (empty) {
                    empty.hidden = true;
                }
                updateState();
            });

            clearButton?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                input.value = '';
                preview.removeAttribute('src');
                preview.load();
                if (field) {
                    field.value = '';
                }
                fileName.textContent = emptyText;
                updateState();
            });

            updateState();
        }

        const homeEditorIdPrefix = @json($idPrefix);
        const campusTourFacilitiesForm = document.querySelector('[data-home-editor-panel="campus_tour_facilities"] form');
        const campusTourFacilityStack = campusTourFacilitiesForm?.querySelector('[data-home-campus-tour-facility-stack]');
        const campusTourFacilityTemplate = campusTourFacilitiesForm?.querySelector('[data-home-campus-tour-facility-template]');

        function getCampusTourFacilityCards() {
            return Array.from(campusTourFacilityStack?.querySelectorAll('[data-home-campus-tour-facility-card]') ?? []);
        }

        function relabelCampusTourFacilityCards() {
            const cards = getCampusTourFacilityCards();
            cards.forEach((card, index) => {
                const title = card.querySelector('[data-home-campus-tour-facility-title]');
                if (title) {
                    title.textContent = `Facility Card ${String(index + 1).padStart(2, '0')}`;
                }
            });
        }

        function nextCampusTourFacilityIndex() {
            const indexes = getCampusTourFacilityCards()
                .map((card) => Number(card.getAttribute('data-home-campus-tour-facility-index') || '0'))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        }

        function addCampusTourFacilityCard() {
            if (!campusTourFacilityStack || !campusTourFacilityTemplate) {
                return;
            }

            const nextIndex = nextCampusTourFacilityIndex();
            const nextNumber = getCampusTourFacilityCards().length + 1;
            const inputId = `${homeEditorIdPrefix}-home-campus-tour-facility-${nextIndex}`;
            const markup = campusTourFacilityTemplate.innerHTML
                .replaceAll('__INDEX__', String(nextIndex))
                .replaceAll('__NUMBER__', String(nextNumber))
                .replaceAll('__INPUT_ID__', inputId);

            campusTourFacilityStack.insertAdjacentHTML('beforeend', markup);
            const appendedCard = campusTourFacilityStack.lastElementChild;
            if (appendedCard) {
                initHomeCampusTourFacilityDropzones(appendedCard);
                const firstField = appendedCard.querySelector('input[type="text"]');
                firstField?.focus();
            }

            relabelCampusTourFacilityCards();
        }

        function deleteCampusTourFacilityCard(trigger) {
            const card = trigger?.closest('[data-home-campus-tour-facility-card]');
            if (!card) {
                return;
            }

            card.remove();
            relabelCampusTourFacilityCards();
        }

        function readCssNumber(element, variableName, fallback) {
            const raw = window.getComputedStyle(element).getPropertyValue(variableName).trim();
            const parsed = Number.parseFloat(raw);

            return Number.isFinite(parsed) ? parsed : fallback;
        }

        function fitHomePreview(frame) {
            const workspace = frame.closest('.home-cms-workspace');
            const shell = frame.closest('.home-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--home-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--home-preview-scale', `${scale}`);
        }

        function setHomePreviewLoading(frame, isLoading) {
            const canvas = frame?.closest('.home-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            if (frame.__homePreviewLoadingTimeout) {
                window.clearTimeout(frame.__homePreviewLoadingTimeout);
                frame.__homePreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__homePreviewLoadingSession = (frame.__homePreviewLoadingSession || 0) + 1;
                frame.__homePreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__homePreviewLoadingSession || 0,
                },
            }));
        }

        function finishHomePreviewLoading(frame) {
            const canvas = frame?.closest('.home-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            const activeSession = frame.__homePreviewLoadingSession || 0;
            const startedAt = frame.__homePreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, HOME_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__homePreviewLoadingTimeout) {
                window.clearTimeout(frame.__homePreviewLoadingTimeout);
            }

            frame.__homePreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__homePreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__homePreviewLoadingTimeout = null;
            }, remaining);
        }

        function scheduleHomePreviewSync(frame) {
            if (!frame) {
                return;
            }

            if (frame.__homePreviewSyncFrame !== undefined && frame.__homePreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__homePreviewSyncFrame);
            }

            frame.__homePreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureHomePreviewHeight(frame);

                if (measuredHeight > 0) {
                    syncHomePreviewHeight(frame, measuredHeight);
                } else {
                    fitHomePreview(frame);
                }

                frame.__homePreviewSyncFrame = null;
            });
        }

        function queueHomePreviewSettledSync(frame) {
            scheduleHomePreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleHomePreviewSync(frame), delay);
            });
            finishHomePreviewLoading(frame);
        }

        function bindHomePreviewDocument(frame) {
            const doc = frame.contentDocument;
            const win = frame.contentWindow;

            if (!doc) {
                return;
            }

            if (typeof frame.__homePreviewCleanup === 'function') {
                frame.__homePreviewCleanup();
            }

            const cleanups = [];
            const schedule = () => queueHomePreviewSettledSync(frame);
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

            frame.__homePreviewCleanup = () => {
                cleanups.forEach((cleanup) => cleanup());
            };
        }

    function getHomePreviewElementBottom(element) {
        return element.offsetTop + element.offsetHeight;
    }

    function isHomePreviewMeasuredElement(element) {
        if (!(element instanceof HTMLElement)) {
            return false;
        }

        const styles = window.getComputedStyle(element);
        return styles.display !== 'none'
            && styles.visibility !== 'hidden'
            && styles.position !== 'fixed';
    }

    function measureHomePreviewHeight(frame) {
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
            .filter((element) => isHomePreviewMeasuredElement(element));

        const contentBottom = visibleElements.reduce((maxBottom, element) => {
            return Math.max(maxBottom, getHomePreviewElementBottom(element));
        }, scope.offsetHeight);

        return Math.max(1, Math.ceil(contentBottom));
    }

    function syncHomePreviewHeight(frame, nextHeight) {
        const workspace = frame.closest('.home-cms-workspace');
        const height = Math.max(1, Number(nextHeight) || 0);

        if (!workspace || !height) {
            return;
        }

        workspace.style.setProperty('--home-preview-height', `${height}px`);
        frame.style.height = `${height}px`;
        fitHomePreview(frame);
    }

        function fitAllHomePreviews() {
            document.querySelectorAll('[data-home-preview-frame]').forEach((frame) => {
                scheduleHomePreviewSync(frame);
            });
        }

        function normalizeHomePreviewRoute(routeKey) {
            const payload = document.querySelector('[data-home-preview-json]');
            const fallbackKey = 'overview';
            const nextKey = String(routeKey || fallbackKey);

            if (!payload) {
                return nextKey;
            }

            try {
                const parsedPayload = JSON.parse(payload.textContent || '{}');

                if (parsedPayload && typeof parsedPayload === 'object' && !Array.isArray(parsedPayload)) {
                    return Object.prototype.hasOwnProperty.call(parsedPayload, nextKey)
                        ? nextKey
                        : fallbackKey;
                }
            } catch (_) {
                return fallbackKey;
            }

            return fallbackKey;
        }

        function setActiveHomePreviewPage(routeKey) {
            document.querySelectorAll('[data-home-preview-page]').forEach((button) => {
                button.classList.toggle('is-active', button.getAttribute('data-home-preview-page') === routeKey);
            });
        }

        function loadHomePreview(frame, options = {}) {
            const payloads = document.querySelectorAll('[data-home-preview-json]');
            const frameIndex = Array.from(document.querySelectorAll('[data-home-preview-frame]')).indexOf(frame);
            const payload = payloads[frameIndex] || payloads[0];
            const explicitSessionId = options.sessionId;
            const routeKey = String(options.routeKey || currentHomePreviewRoute || 'overview');

            if (!payload || !frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__homePreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            setHomePreviewLoading(frame, true);

            try {
                const parsedPayload = JSON.parse(payload.textContent || '{}');
                const previewHtml = typeof parsedPayload === 'string'
                    ? parsedPayload
                    : (parsedPayload[routeKey] || parsedPayload.overview || '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
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

        function loadHomePreviewPage(routeKey, options = {}) {
            const targetKey = normalizeHomePreviewRoute(routeKey);
            currentHomePreviewRoute = targetKey;
            persistHomePreviewRoute(targetKey);
            setActiveHomePreviewPage(targetKey);

            document.querySelectorAll('[data-home-preview-frame]').forEach((frame) => {
                loadHomePreview(frame, {
                    routeKey: targetKey,
                    sessionId: options.sessionId,
                });
            });
        }

        function scheduleFitAllHomePreviews() {
            if (homePreviewFitFrame !== null) {
                window.cancelAnimationFrame(homePreviewFitFrame);
            }

            homePreviewFitFrame = window.requestAnimationFrame(() => {
                fitAllHomePreviews();
                window.setTimeout(fitAllHomePreviews, 140);
                homePreviewFitFrame = null;
            });
        }

        const quickLinksForm = document.querySelector('[data-home-quick-links-form]');
        const quickLinksStack = quickLinksForm?.querySelector('[data-home-quick-link-stack]');
        const quickLinksVersionInput = quickLinksForm?.querySelector('[data-home-quick-links-version]');
        const activeQuickLinkIndexInput = quickLinksForm?.querySelector('[data-home-active-quick-link-index]');

        function bumpQuickLinksVersion() {
            if (quickLinksVersionInput) {
                quickLinksVersionInput.value = String(Date.now());
            }
        }

        function submitQuickLinksForm() {
            if (!quickLinksForm) {
                return;
            }

            syncEditorsInScope(quickLinksForm);

            if (typeof quickLinksForm.requestSubmit === 'function') {
                quickLinksForm.requestSubmit();
                return;
            }

            quickLinksForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        }

        function setActiveQuickLinkEditor(targetIndex = null, activateFirstWhenEmpty = true) {
            const editors = Array.from(quickLinksStack?.querySelectorAll('[data-home-quick-link-editor]') ?? []);

            if (!editors.length) {
                if (activeQuickLinkIndexInput) {
                    activeQuickLinkIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = targetIndex === null || targetIndex === undefined || targetIndex === ''
                ? null
                : String(targetIndex);
            let activeEditor = null;

            editors.forEach((editor) => {
                const isMatch = normalizedIndex !== null && editor.getAttribute('data-home-quick-link-index') === normalizedIndex;
                const shouldActivate = normalizedIndex === null
                    ? (activateFirstWhenEmpty && editor === editors[0])
                    : isMatch;
                editor.classList.toggle('is-active', shouldActivate);

                if (shouldActivate) {
                    activeEditor = editor;
                }
            });

            if (activeQuickLinkIndexInput) {
                activeQuickLinkIndexInput.value = activeEditor?.getAttribute('data-home-quick-link-index') || '';
            }

            return activeEditor;
        }

        const feedbackForm = document.querySelector('[data-home-feedback-form]');
        const feedbackQuestionStack = feedbackForm?.querySelector('[data-home-feedback-question-stack]');
        const feedbackQuestionsVersionInput = feedbackForm?.querySelector('[data-home-feedback-questions-version]');
        const activeFeedbackQuestionIndexInput = feedbackForm?.querySelector('[data-home-active-feedback-question-index]');
        const feedbackQuestionTemplate = feedbackForm?.querySelector('[data-home-feedback-question-template]');

        function bumpFeedbackQuestionsVersion() {
            if (feedbackQuestionsVersionInput) {
                feedbackQuestionsVersionInput.value = String(Date.now());
            }
        }

        function submitFeedbackForm() {
            if (!feedbackForm) {
                return;
            }

            syncEditorsInScope(feedbackForm);

            if (typeof feedbackForm.requestSubmit === 'function') {
                feedbackForm.requestSubmit();
                return;
            }

            feedbackForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        }

        function relabelFeedbackQuestions() {
            const editors = Array.from(feedbackQuestionStack?.querySelectorAll('[data-home-feedback-question-editor]') ?? []);

            editors.forEach((editor, index) => {
                const title = editor.querySelector('.home-cms-card-editor-head h4');
                if (title) {
                    title.textContent = `Question ${String(index + 1).padStart(2, '0')}`;
                }
            });
        }

        function setActiveFeedbackQuestionEditor(targetIndex = null, activateFirstWhenEmpty = true) {
            const editors = Array.from(feedbackQuestionStack?.querySelectorAll('[data-home-feedback-question-editor]') ?? []);

            if (!editors.length) {
                if (activeFeedbackQuestionIndexInput) {
                    activeFeedbackQuestionIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = targetIndex === null || targetIndex === undefined || targetIndex === ''
                ? null
                : String(targetIndex);
            let activeEditor = null;

            editors.forEach((editor) => {
                const isMatch = normalizedIndex !== null && editor.getAttribute('data-home-feedback-question-index') === normalizedIndex;
                const shouldActivate = normalizedIndex === null
                    ? (activateFirstWhenEmpty && editor === editors[0])
                    : isMatch;
                editor.classList.toggle('is-active', shouldActivate);

                if (shouldActivate) {
                    activeEditor = editor;
                }
            });

            if (activeFeedbackQuestionIndexInput) {
                activeFeedbackQuestionIndexInput.value = activeEditor?.getAttribute('data-home-feedback-question-index') || '';
            }

            return activeEditor;
        }

        function nextFeedbackQuestionIndex() {
            const indexes = Array.from(feedbackQuestionStack?.querySelectorAll('[data-home-feedback-question-editor]') ?? [])
                .map((editor) => Number(editor.getAttribute('data-home-feedback-question-index') || '0'))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        }

        function notifyFeedbackQuestionLimit() {
            const message = 'Question limit reached. You can add up to 10 feedback questions.';

            if (typeof window.notify === 'function') {
                window.notify(message, 'warning');
                return;
            }

            window.alert(message);
        }

        function getFeedbackQuestionCount() {
            return feedbackQuestionStack?.querySelectorAll('[data-home-feedback-question-editor]').length ?? 0;
        }

        function canAddFeedbackQuestion() {
            return getFeedbackQuestionCount() < 10;
        }

        function addFeedbackQuestion() {
            if (!feedbackQuestionStack || !feedbackQuestionTemplate) {
                return null;
            }

            const currentCount = getFeedbackQuestionCount();
            if (currentCount >= 10) {
                notifyFeedbackQuestionLimit();
                return null;
            }

            const index = nextFeedbackQuestionIndex();
            const number = currentCount + 1;
            const markup = feedbackQuestionTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(number).padStart(2, '0'));

            feedbackQuestionStack.insertAdjacentHTML('beforeend', markup);
            bumpFeedbackQuestionsVersion();
            relabelFeedbackQuestions();
            const activeEditor = setActiveFeedbackQuestionEditor(index);
            const questionField = activeEditor?.querySelector('textarea[name*="[question]"]');
            if (questionField) {
                questionField.value = '';
            }

            return activeEditor;
        }

        function deleteFeedbackQuestionByIndex(targetIndex) {
            const editor = feedbackQuestionStack?.querySelector(`[data-home-feedback-question-editor][data-home-feedback-question-index="${targetIndex}"]`);
            if (!editor) {
                return false;
            }

            editor.remove();
            bumpFeedbackQuestionsVersion();
            relabelFeedbackQuestions();
            setActiveFeedbackQuestionEditor();
            return true;
        }

        async function confirmDeleteFeedbackQuestion(targetIndex) {
            const editor = feedbackQuestionStack?.querySelector(`[data-home-feedback-question-editor][data-home-feedback-question-index="${targetIndex}"]`);
            if (!editor) {
                return;
            }

            const questionInput = editor.querySelector('textarea[name*="[question]"]');
            const questionText = String(questionInput?.value || '').trim();
            const message = questionText
                ? `Do you want to delete "${questionText}"?`
                : 'Do you want to delete this feedback question?';

            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Question',
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

            if (deleteFeedbackQuestionByIndex(targetIndex)) {
                submitFeedbackForm();
            }
        }

        function openHomeEditor(sectionKey, label, options = {}) {
            if (sectionKey === 'feedback' && options.addFeedbackQuestion === true && !canAddFeedbackQuestion()) {
                notifyFeedbackQuestionLimit();
                return;
            }

            const modal = document.querySelector('[data-home-editor-modal]');
            if (!modal) {
                return;
            }

            const title = modal.querySelector('#{{ $idPrefix }}-modal-title');
            const description = modal.querySelector('[data-home-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';

            modal.querySelectorAll('[data-home-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-home-editor-panel') === sectionKey;
                const isQuickLinkCardFocus = sectionKey === 'quick_links'
                    && options.cardIndex !== null
                    && options.cardIndex !== undefined
                    && options.cardIndex !== '';
                const isFeedbackQuestionFocus = sectionKey === 'feedback'
                    && (
                        options.addFeedbackQuestion === true
                        || (options.feedbackQuestionIndex !== null
                            && options.feedbackQuestionIndex !== undefined
                            && options.feedbackQuestionIndex !== '')
                    );
                const isCardFocus = isQuickLinkCardFocus || isFeedbackQuestionFocus;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    if (title) {
                        title.textContent = label || 'Edit homepage section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the homepage preview.';
                    }

                    const activeQuickLinkEditor = sectionKey === 'quick_links'
                        ? setActiveQuickLinkEditor(options.cardIndex ?? null, isCardFocus)
                        : null;
                    const activeFeedbackQuestionEditor = sectionKey === 'feedback'
                        ? (
                            options.addFeedbackQuestion === true
                                ? addFeedbackQuestion()
                                : setActiveFeedbackQuestionEditor(options.feedbackQuestionIndex ?? null, isFeedbackQuestionFocus)
                        )
                        : null;

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const focusScope = activeQuickLinkEditor || activeFeedbackQuestionEditor || panel;
                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                    firstField?.focus();
                }
            });
        }

        function closeHomeEditor() {
            const modal = document.querySelector('[data-home-editor-modal]');
            if (!modal) {
                return;
            }

            modal.hidden = true;
            modal.classList.remove('is-card-focus');
            document.body.style.overflow = '';
        }

        window.openHomeCmsSection = openHomeEditor;

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-home-edit') {
                openHomeEditor(data.section || '', data.label || 'Edit homepage section');
                return;
            }

            if (data.type === 'cms-home-edit-card') {
                openHomeEditor('quick_links', data.label || 'Edit explore card', {
                    cardIndex: data.cardIndex,
                });
                return;
            }

            if (data.type === 'cms-home-feedback-question-edit') {
                openHomeEditor('feedback', data.label || 'Edit feedback question', {
                    feedbackQuestionIndex: data.questionIndex,
                });
                return;
            }

            if (data.type === 'cms-home-feedback-question-delete') {
                confirmDeleteFeedbackQuestion(data.questionIndex);
                return;
            }

            if (data.type === 'cms-home-feedback-question-add') {
                if (!canAddFeedbackQuestion()) {
                    notifyFeedbackQuestionLimit();
                    return;
                }

                openHomeEditor('feedback', data.label || 'Add feedback question', {
                    addFeedbackQuestion: true,
                });
                return;
            }

            if (data.type === 'cms-home-preview-height') {
                const targetFrame = Array.from(document.querySelectorAll('[data-home-preview-frame]'))
                    .find((frame) => frame.contentWindow === event.source);

                if (!targetFrame) {
                    return;
                }

                syncHomePreviewHeight(targetFrame, data.height);
                return;
            }

        });

        document.addEventListener('click', (event) => {
            const addFacilityButton = event.target.closest('[data-home-campus-tour-facility-add]');
            if (addFacilityButton) {
                event.preventDefault();
                addCampusTourFacilityCard();
                return;
            }

            const deleteFacilityButton = event.target.closest('[data-home-campus-tour-facility-delete]');
            if (deleteFacilityButton) {
                event.preventDefault();
                deleteCampusTourFacilityCard(deleteFacilityButton);
                return;
            }

            if (event.target.closest('[data-close-home-editor]')) {
                event.preventDefault();
                closeHomeEditor();
                return;
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeHomeEditor();
            }
        });

        document.querySelectorAll('.{{ $formClass }}').forEach((form) => {
            form.addEventListener('submit', () => syncEditorsInScope(form));
        });

        initHomeCarouselDropzones(document);
        initHomeCampusTourFacilityDropzones(document);
        initHomeCampusTourVideoInput(document);
        relabelCampusTourFacilityCards();

        currentHomePreviewRoute = normalizeHomePreviewRoute(currentHomePreviewRoute);
        persistHomePreviewRoute(currentHomePreviewRoute);

        document.querySelectorAll('[data-home-preview-frame]').forEach((frame, index) => {
            loadHomePreview(frame, {
                routeKey: currentHomePreviewRoute,
            });

            frame.addEventListener('load', () => {
                bindHomePreviewDocument(frame);
                queueHomePreviewSettledSync(frame);
                scheduleFitAllHomePreviews();
            });
        });

        document.querySelectorAll('[data-home-preview-page]').forEach((button) => {
            button.addEventListener('click', () => {
                loadHomePreviewPage(button.getAttribute('data-home-preview-page') || 'overview');
            });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAllHomePreviews();
            });

            document.querySelectorAll('.home-cms-preview-frame-shell').forEach((shell) => {
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
                scheduleFitAllHomePreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('resize', scheduleFitAllHomePreviews);
        window.addEventListener('pageshow', scheduleFitAllHomePreviews);
        window.addEventListener('load', scheduleFitAllHomePreviews);
        window.addEventListener('cms:tab-activated', (event) => {
            const tabPanel = event.detail?.panel;
            const sessionId = Number(event.detail?.sessionId || 0) || undefined;

            const targetFrame = Array.from(document.querySelectorAll('[data-home-preview-frame]'))
                .find((frame) => !tabPanel || tabPanel.contains(frame));

            if (!targetFrame) {
                return;
            }

            loadHomePreviewPage(currentHomePreviewRoute || 'overview', { sessionId });
            window.setTimeout(() => scheduleFitAllHomePreviews(), 40);
            window.setTimeout(() => scheduleFitAllHomePreviews(), 180);
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAllHomePreviews();
            }
        });

        window.refreshHomeCmsPreview = (scope) => {
            const frames = scope
                ? Array.from(scope.querySelectorAll('[data-home-preview-frame]'))
                : Array.from(document.querySelectorAll('[data-home-preview-frame]'));

            frames.forEach((frame) => {
                loadHomePreview(frame, {
                    routeKey: currentHomePreviewRoute,
                });
            });
        };

        scheduleFitAllHomePreviews();
        setActiveQuickLinkEditor();
        relabelFeedbackQuestions();
        setActiveFeedbackQuestionEditor(null, false);
        setActiveHomePreviewPage(currentHomePreviewRoute);

        window.__homeCmsPreviewEditorReady = true;
    })();
</script>
