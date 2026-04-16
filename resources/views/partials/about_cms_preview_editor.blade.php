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
            <section class="about-cms-editor-panel" data-about-editor-panel="hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Landing Hero Title</label>
                            <input type="text" name="about[overview][hero_title_default]" maxlength="255" value="{{ $overviewEditor['hero_title_default'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>History Hero Title</label>
                            <input type="text" name="about[overview][hero_title_history]" maxlength="255" value="{{ $overviewEditor['hero_title_history'] ?? '' }}">
                        </div>
                    </div>

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Vision Hero Title</label>
                            <input type="text" name="about[overview][hero_title_vision]" maxlength="255" value="{{ $overviewEditor['hero_title_vision'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Hero Image Path</label>
                            <input type="text" name="about[overview][hero_image]" maxlength="2048" value="{{ $overviewEditor['hero_image'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Section Header Image Path</label>
                        <input type="text" name="about[overview][section_header_image]" maxlength="2048" value="{{ $overviewEditor['section_header_image'] ?? '' }}">
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Hero') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="intro" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="intro">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

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
                        <textarea name="about[overview][story_description]" rows="10">{{ $overviewEditor['story_description'] ?? '' }}</textarea>
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

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Contents Tag</label>
                            <input type="text" name="about[overview][contents_tag]" maxlength="255" value="{{ $overviewEditor['contents_tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Contents Title</label>
                            <input type="text" name="about[overview][contents_title]" maxlength="255" value="{{ $overviewEditor['contents_title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="about-cms-card-stack">
                        @foreach($aboutSections as $slug => $section)
                            @php
                                $sectionImagePreview = \App\Support\NewsImage::url($section['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                            @endphp
                            <article class="about-cms-card-editor" data-about-contents-editor data-about-contents-slug="{{ $slug }}">
                                <div class="about-cms-card-editor-head">
                                    <h4>{{ $section['label'] ?? $slug }}</h4>
                                    <span>{{ $slug }}</span>
                                </div>

                                <input type="hidden" name="about[sections][{{ $slug }}][visible_in_contents]" value="{{ $section['visible_in_contents'] ?? '1' }}" data-about-contents-visible>
                                <input type="hidden" name="about[sections][{{ $slug }}][image]" value="{{ $section['image'] ?? '' }}">

                                <div class="about-cms-form-grid">
                                    <div class="form-group">
                                        <label>Card Title</label>
                                        <input type="text" name="about[sections][{{ $slug }}][label]" maxlength="255" value="{{ $section['label'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Card Summary</label>
                                        <textarea name="about[sections][{{ $slug }}][summary]" rows="4">{{ $section['summary'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Upload Card Image</label>
                                    <div class="about-cms-image-upload-shell">
                                        <img src="{{ $sectionImagePreview }}" alt="{{ $section['label'] ?? $slug }} preview" class="about-cms-image-upload-preview">
                                        <div class="about-cms-image-upload-copy">
                                            <input type="file" name="about[sections][{{ $slug }}][image_file]" accept="image/*">
                                        </div>
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

            @php($historyEditor = $aboutSections['history'] ?? [])
            <section class="about-cms-editor-panel" data-about-editor-panel="history" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="history">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][history][label]" maxlength="255" value="{{ $historyEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][history][summary]" rows="4">{{ $historyEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="about-cms-form-grid">
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
                        @foreach($historyEditor['timeline'] ?? [] as $index => $milestone)
                            <article class="about-cms-card-editor">
                                <div class="about-cms-card-editor-head">
                                    <h4>Milestone {{ $loop->iteration }}</h4>
                                    <span>{{ $milestone['period'] ?? '' }}</span>
                                </div>

                                <div class="about-cms-form-grid">
                                    <div class="form-group">
                                        <label>Period</label>
                                        <input type="text" name="about[sections][history][timeline][{{ $index }}][period]" maxlength="255" value="{{ $milestone['period'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="about[sections][history][timeline][{{ $index }}][title]" maxlength="255" value="{{ $milestone['title'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Body Paragraphs</label>
                                    <textarea name="about[sections][history][timeline][{{ $index }}][body_text]" rows="8">{{ implode("\n\n", $milestone['body'] ?? []) }}</textarea>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('History') }}</button>
                    </div>
                </form>
            </section>

            @php($visionEditor = $aboutSections['vision-and-mission'] ?? [])
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

            <section class="about-cms-editor-panel" data-about-editor-panel="vision-mission-statements" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="vision-mission-statements">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Vision Statement</label>
                        <textarea name="about[sections][vision-and-mission][vision]" rows="4">{{ $visionEditor['vision'] ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Mission Statement</label>
                        <textarea name="about[sections][vision-and-mission][mission]" rows="4">{{ $visionEditor['mission'] ?? '' }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Vision and Mission Statements') }}</button>
                    </div>
                </form>
            </section>

            <section class="about-cms-editor-panel" data-about-editor-panel="strategic-goals" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="strategic-goals">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

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
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

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

            @php($logoEditor = $aboutSections['logo-and-symbols'] ?? [])
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

            @php($hymnEditor = $aboutSections['hymn'] ?? [])
            <section class="about-cms-editor-panel" data-about-editor-panel="hymn" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="hymn">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][hymn][label]" maxlength="255" value="{{ $hymnEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][hymn][summary]" rows="4">{{ $hymnEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lead Paragraph</label>
                        <textarea name="about[sections][hymn][lead]" rows="5">{{ $hymnEditor['lead'] ?? '' }}</textarea>
                    </div>

                    @foreach($hymnEditor['hymn_sections'] ?? [] as $index => $hymnSection)
                        <article class="about-cms-card-editor">
                            <div class="about-cms-form-grid">
                                <div class="form-group">
                                    <label>Hymn Card Title</label>
                                    <input type="text" name="about[sections][hymn][hymn_sections][{{ $index }}][title]" maxlength="255" value="{{ $hymnSection['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Hymn Card Body</label>
                                    <textarea name="about[sections][hymn][hymn_sections][{{ $index }}][body]" rows="4">{{ $hymnSection['body'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div class="form-group">
                        <label>Hymn Notes</label>
                        <textarea name="about[sections][hymn][hymn_notes_text]" rows="5">{{ implode("\n", $hymnEditor['hymn_notes'] ?? []) }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Hymn') }}</button>
                    </div>
                </form>
            </section>

            @php($mapsEditor = $aboutSections['maps'] ?? [])
            <section class="about-cms-editor-panel" data-about-editor-panel="maps" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="maps">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][maps][label]" maxlength="255" value="{{ $mapsEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][maps][summary]" rows="4">{{ $mapsEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Map URL</label>
                            <input type="text" name="about[sections][maps][map_url]" maxlength="2048" value="{{ $mapsEditor['map_url'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Lead Paragraph</label>
                            <textarea name="about[sections][maps][lead]" rows="4">{{ $mapsEditor['lead'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Visit Planning Copy</label>
                        <textarea name="about[sections][maps][visit_planning_text]" rows="4">{{ $mapsEditor['visit_planning_text'] ?? '' }}</textarea>
                    </div>

                    @foreach($mapsEditor['map_cards'] ?? [] as $index => $mapCard)
                        <article class="about-cms-card-editor">
                            <div class="about-cms-form-grid">
                                <div class="form-group">
                                    <label>Map Card Title</label>
                                    <input type="text" name="about[sections][maps][map_cards][{{ $index }}][title]" maxlength="255" value="{{ $mapCard['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Map Card Body</label>
                                    <textarea name="about[sections][maps][map_cards][{{ $index }}][body]" rows="4">{{ $mapCard['body'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div class="form-group">
                        <label>Visitor Notes</label>
                        <textarea name="about[sections][maps][visit_notes_text]" rows="5">{{ implode("\n", $mapsEditor['visit_notes'] ?? []) }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Maps') }}</button>
                    </div>
                </form>
            </section>

            @php($officialsEditor = $aboutSections['campus-officials'] ?? [])
            <section class="about-cms-editor-panel" data-about-editor-panel="campus-officials" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="campus-officials">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][campus-officials][label]" maxlength="255" value="{{ $officialsEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][campus-officials][summary]" rows="4">{{ $officialsEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lead Paragraph</label>
                        <textarea name="about[sections][campus-officials][lead]" rows="5">{{ $officialsEditor['lead'] ?? '' }}</textarea>
                    </div>

                    @foreach($officialsEditor['official_groups'] ?? [] as $index => $officialGroup)
                        <article class="about-cms-card-editor">
                            <div class="about-cms-form-grid">
                                <div class="form-group">
                                    <label>Office Title</label>
                                    <input type="text" name="about[sections][campus-officials][official_groups][{{ $index }}][title]" maxlength="255" value="{{ $officialGroup['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Office Description</label>
                                    <textarea name="about[sections][campus-officials][official_groups][{{ $index }}][body]" rows="4">{{ $officialGroup['body'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div class="form-group">
                        <label>Footer Note</label>
                        <textarea name="about[sections][campus-officials][officials_note]" rows="4">{{ $officialsEditor['officials_note'] ?? '' }}</textarea>
                    </div>

                    <div class="about-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Campus Officials') }}</button>
                    </div>
                </form>
            </section>

            @php($planEditor = $aboutSections['strategic-development-plan'] ?? [])
            <section class="about-cms-editor-panel" data-about-editor-panel="strategic-development-plan" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="about">
                    <input type="hidden" name="section_key" value="strategic-development-plan">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="about-cms-form-grid">
                        <div class="form-group">
                            <label>Section Label</label>
                            <input type="text" name="about[sections][strategic-development-plan][label]" maxlength="255" value="{{ $planEditor['label'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Summary</label>
                            <textarea name="about[sections][strategic-development-plan][summary]" rows="4">{{ $planEditor['summary'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lead Paragraph</label>
                        <textarea name="about[sections][strategic-development-plan][lead]" rows="5">{{ $planEditor['lead'] ?? '' }}</textarea>
                    </div>

                    @foreach($planEditor['development_priorities'] ?? [] as $index => $priority)
                        <article class="about-cms-card-editor">
                            <div class="about-cms-form-grid">
                                <div class="form-group">
                                    <label>Priority Title</label>
                                    <input type="text" name="about[sections][strategic-development-plan][development_priorities][{{ $index }}][title]" maxlength="255" value="{{ $priority['title'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Priority Body</label>
                                    <textarea name="about[sections][strategic-development-plan][development_priorities][{{ $index }}][body]" rows="4">{{ $priority['body'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div class="form-group">
                        <label>Planning Principles</label>
                        <textarea name="about[sections][strategic-development-plan][plan_principles_text]" rows="5">{{ implode("\n", $planEditor['plan_principles'] ?? []) }}</textarea>
                    </div>

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
        margin: 16px auto;
        overflow: auto;
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
    }

    .about-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
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

    .about-cms-image-upload-shell {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
    }

    .about-cms-image-upload-preview {
        width: 100%;
        height: 132px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #efe3dc;
        background: #f7ede8;
    }

    .about-cms-image-upload-copy {
        min-width: 0;
    }

    .about-cms-upload-hint {
        display: block;
        margin-top: 6px;
        color: #8a7a73;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .about-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
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

        .about-cms-image-upload-shell {
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
        let currentAboutPreviewRoute = 'overview';

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

        function openAboutEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-about-editor-modal]');
            if (!modal) {
                return;
            }

            const title = modal.querySelector('#{{ $idPrefix }}-modal-title');
            const description = modal.querySelector('[data-about-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';

            modal.querySelectorAll('[data-about-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-about-editor-panel') === sectionKey;
                panel.hidden = !isActive;

                if (isActive) {
                    if (title) {
                        title.textContent = label || 'Edit about section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the About page preview.';
                    }

                    const focusScope = sectionKey === 'contents'
                        ? (setActiveContentsEditor(options.slug || '') || panel)
                        : panel;
                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea, select');
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
            document.body.style.overflow = '';
        }

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-about-edit') {
                openAboutEditor(data.section || '', data.label || 'Edit about section');
                return;
            }

            if (data.type === 'cms-about-contents-card-edit') {
                openAboutEditor('contents', data.label ? `Edit ${data.label}` : 'Edit about card', {
                    slug: data.slug || '',
                });
                return;
            }

            if (data.type === 'cms-about-contents-card-delete') {
                confirmDeleteContentsCard(data.slug || '', data.label || '');
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

        const bumpContentsVersion = () => {
            if (contentsVersionInput) {
                contentsVersionInput.value = String(Date.now());
            }
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

        loadAboutPreviewPage('overview');
        scheduleFitAboutPreviews();
        window.__aboutCmsPreviewEditorReady = true;
    })();
</script>
