@php
    $homeDefaults = \App\Support\HomeCmsContent::defaults();
    $homeEditorData = \App\Support\HomeCmsContent::fromInput($homeEditorData ?? [], null);
    $heroEditor = $homeEditorData['hero'] ?? $homeDefaults['hero'];
    $updatesEditor = $homeEditorData['updates'] ?? $homeDefaults['updates'];
    $quickLinksEditor = $homeEditorData['quick_links'] ?? $homeDefaults['quick_links'];
    $feedbackEditor = $homeEditorData['feedback'] ?? $homeDefaults['feedback'];
    $slidesEditor = $homeEditorData['carousel_slides'] ?? $homeDefaults['carousel_slides'];
    $formClass = $homeEditorFormClass ?? 'cms-save-form';
    $submitRoute = $homeEditorSubmitRoute;
    $submitMode = $homeEditorSubmitMode ?? 'save';
    $requestId = (int) ($homeEditorRequestId ?? 0);
    $status = strtolower((string) ($homeEditorStatus ?? ''));
    $idPrefix = trim((string) ($homeEditorIdPrefix ?? 'home-editor'));
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
@endphp

<div class="home-cms-workspace">
    <div class="home-cms-preview-shell">
        <div class="home-cms-preview-head">
            <div>
                <span class="home-cms-eyebrow">Homepage CMS</span>
                <h3>Live website preview</h3>
                <p>Click the highlighted sections inside the preview to edit the contents of the Home page.</p>
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

                    <div class="home-cms-form-grid">
                        <div class="form-group">
                            <label>Crest Heading</label>
                            <textarea name="home[hero][crest_heading]" rows="5">{{ $heroEditor['crest_heading'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Crest Year</label>
                            <input type="text" name="home[hero][crest_year]" maxlength="50" value="{{ $heroEditor['crest_year'] ?? '' }}">
                        </div>
                    </div>

                    <div class="carousel-manager-grid">
                        @for($idx = 0; $idx < 3; $idx++)
                            @php
                                $slide = $slidesEditor[$idx] ?? ['title' => '', 'subtitle' => '', 'image' => ''];
                                $slideInputId = $idPrefix.'-home-slide-'.$idx;
                                $slidePreview = \App\Support\HomeCmsContent::resolveImagePath($slide['image'] ?? '', 'assets/static_img/pupillar.jpeg');
                            @endphp
                            <div class="carousel-manager-item">
                                <input type="hidden" name="home[carousel][{{ $idx }}][image]" value="{{ $slide['image'] }}">
                                <input type="hidden" name="home[carousel][{{ $idx }}][title]" value="{{ $slide['title'] }}">
                                <input type="hidden" name="home[carousel][{{ $idx }}][subtitle]" value="{{ $slide['subtitle'] }}">

                                <label class="home-dropzone slide-dropzone" for="{{ $slideInputId }}">
                                    <img src="{{ $slidePreview }}" alt="Slide {{ $idx + 1 }} preview" class="slide-preview" data-preview-for="{{ $slideInputId }}">
                                    <span class="dropzone-label">Slide {{ $idx + 1 }}</span>
                                    <span class="dropzone-file-name" data-file-name-for="{{ $slideInputId }}">Drop image here or click to replace</span>
                                </label>
                                <input id="{{ $slideInputId }}" class="home-dropzone-input" type="file" name="home[carousel][{{ $idx }}][image_file]" accept="image/*">
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
                        <label>Intro Copy</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'home[updates][description]',
                            'value' => $updatesEditor['description'] ?? '',
                            'placeholder' => 'Write the intro shown above the news and announcement panels...',
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

            <section class="home-cms-editor-panel" data-home-editor-panel="quick_links" hidden>
                <form class="{{ $formClass }} home-section-form" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-home-quick-links-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="home">
                    <input type="hidden" name="section_key" value="quick_links">
                    <input type="hidden" name="home_quick_links_version" value="0" data-home-quick-links-version>
                    <input type="hidden" name="home_active_quick_link_index" value="" data-home-active-quick-link-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="home-cms-form-grid">
                        <div class="form-group">
                            <label>Section Tag</label>
                            <input type="text" name="home[quick_links][tag]" maxlength="80" value="{{ $quickLinksEditor['tag'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Section Title</label>
                            <input type="text" name="home[quick_links][title]" maxlength="255" value="{{ $quickLinksEditor['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="home-cms-card-stack" data-home-quick-link-stack>
                        @foreach(($quickLinksEditor['items'] ?? []) as $index => $item)
                            <article class="home-cms-card-editor" data-home-quick-link-editor data-home-quick-link-index="{{ $index }}">
                                <div class="home-cms-card-editor-head">
                                    <h4>Explore Card {{ $loop->iteration }}</h4>
                                    <span>{{ $item['href'] ?? '' }}</span>
                                </div>

                                <input type="hidden" name="home[quick_links][items][{{ $index }}][href]" value="{{ $item['href'] ?? '' }}">

                                <div class="home-cms-form-grid">
                                    <div class="form-group">
                                        <label>Card Label</label>
                                        <input type="text" name="home[quick_links][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Card Title</label>
                                        <input type="text" name="home[quick_links][items][{{ $index }}][title]" maxlength="255" value="{{ $item['title'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Card Description</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => 'home[quick_links][items]['.$index.'][body]',
                                        'value' => $item['body'] ?? '',
                                        'placeholder' => 'Write the card description shown in the Explore section...',
                                    ])
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="home-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Explore Section') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="home-cms-editor-panel" data-home-editor-panel="feedback" hidden>
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
                        ])
                    </div>

                    <div class="home-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Feedback Banner') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-home-preview-json>
{!! json_encode($homePreviewHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
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
        display: none;
    }

    .home-cms-preview-head h3,
    .home-cms-side-card h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1.1rem;
    }

    .home-cms-preview-head p,
    .home-cms-side-card p {
        margin: 8px 0 0;
        color: #6f625c;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .home-cms-eyebrow,
    .home-cms-side-kicker {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
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
        margin: 16px auto;
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

    .home-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
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
        .home-cms-card-editor-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .home-cms-form-grid {
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
        let homePreviewFitFrame = null;

        function syncEditorsInScope(scope) {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
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

        function loadHomePreview(frame, options = {}) {
            const payloads = document.querySelectorAll('[data-home-preview-json]');
            const frameIndex = Array.from(document.querySelectorAll('[data-home-preview-frame]')).indexOf(frame);
            const payload = payloads[frameIndex] || payloads[0];
            const explicitSessionId = options.sessionId;

            if (!payload || !frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__homePreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            setHomePreviewLoading(frame, true);

            try {
                const previewHtml = JSON.parse(payload.textContent || '""');
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

        function setActiveQuickLinkEditor(targetIndex = null) {
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
                const shouldActivate = normalizedIndex === null ? editor === editors[0] : isMatch;
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

        function deleteQuickLinkByIndex(targetIndex) {
            const editor = quickLinksStack?.querySelector(`[data-home-quick-link-editor][data-home-quick-link-index="${targetIndex}"]`);
            if (!editor) {
                return false;
            }

            editor.remove();
            bumpQuickLinksVersion();
            setActiveQuickLinkEditor();
            return true;
        }

        async function confirmDeleteQuickLink(targetIndex) {
            const editor = quickLinksStack?.querySelector(`[data-home-quick-link-editor][data-home-quick-link-index="${targetIndex}"]`);
            if (!editor) {
                return;
            }

            const titleInput = editor.querySelector('input[name*="[title]"]');
            const cardTitle = String(titleInput?.value || '').trim();
            const message = cardTitle
                ? `Do you want to delete "${cardTitle}"?`
                : 'Do you want to delete this explore card?';

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

            if (deleteQuickLinkByIndex(targetIndex)) {
                submitQuickLinksForm();
            }
        }

        function openHomeEditor(sectionKey, label, options = {}) {
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
                panel.hidden = !isActive;

                if (isActive) {
                    if (title) {
                        title.textContent = label || 'Edit homepage section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the homepage preview.';
                    }

                    const activeQuickLinkEditor = sectionKey === 'quick_links'
                        ? setActiveQuickLinkEditor(options.cardIndex ?? null)
                        : null;

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const focusScope = activeQuickLinkEditor || panel;
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

            if (data.type === 'cms-home-delete-card') {
                confirmDeleteQuickLink(data.cardIndex);
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
            if (event.target.closest('[data-close-home-editor]')) {
                event.preventDefault();
                closeHomeEditor();
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

        document.querySelectorAll('[data-home-preview-frame]').forEach((frame, index) => {
            loadHomePreview(frame);

            frame.addEventListener('load', () => {
                bindHomePreviewDocument(frame);
                queueHomePreviewSettledSync(frame);
                scheduleFitAllHomePreviews();
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

            document.querySelectorAll('[data-home-preview-frame]').forEach((frame) => {
                if (!tabPanel || !tabPanel.contains(frame)) {
                    return;
                }

                loadHomePreview(frame, { sessionId });
                window.setTimeout(() => scheduleFitAllHomePreviews(), 40);
                window.setTimeout(() => scheduleFitAllHomePreviews(), 180);
            });
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
                loadHomePreview(frame);
            });
        };

        scheduleFitAllHomePreviews();
        setActiveQuickLinkEditor();

        window.__homeCmsPreviewEditorReady = true;
    })();
</script>
