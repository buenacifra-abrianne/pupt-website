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
            <section class="academics-cms-editor-panel" data-academics-editor-panel="hero" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="academics">
                    <input type="hidden" name="section_key" value="hero">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="academics-cms-form-grid">
                        <div class="form-group">
                            <label>Hero Title</label>
                            <input type="text" name="academics[hero][title]" maxlength="255" value="{{ $heroEditor['title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Hero Image Path</label>
                            <input type="text" name="academics[hero][image]" maxlength="2048" value="{{ $heroEditor['image'] ?? '' }}">
                        </div>
                    </div>

                    <div class="academics-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Academics Hero') }}</button>
                    </div>
                </form>
            </section>

            <section class="academics-cms-editor-panel" data-academics-editor-panel="contents" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-academics-contents-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="academics">
                    <input type="hidden" name="section_key" value="contents">
                    <input type="hidden" name="academics_contents_version" value="0" data-academics-contents-version>
                    <input type="hidden" name="academics_active_contents_index" value="" data-academics-active-contents-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Section Tag</label>
                        <input type="text" name="academics[contents][tag]" maxlength="80" value="{{ $contentsEditor['tag'] ?? '' }}">
                    </div>

                    <div class="academics-cms-card-stack" data-academics-contents-stack>
                        @foreach(($contentsEditor['items'] ?? []) as $index => $item)
                            <article class="academics-cms-card-editor" data-academics-contents-editor data-academics-contents-index="{{ $index }}">
                                <div class="academics-cms-card-editor-head">
                                    <h4>Contents Card {{ $loop->iteration }}</h4>
                                    <span>{{ $item['route'] ?? '' }}</span>
                                </div>

                                <input type="hidden" name="academics[contents][items][{{ $index }}][route]" value="{{ $item['route'] ?? '' }}">
                                <input type="hidden" name="academics[contents][items][{{ $index }}][image]" value="{{ $item['image'] ?? '' }}">

                                <div class="academics-cms-form-grid">
                                    <div class="form-group">
                                        <label>Card Label</label>
                                        <input type="text" name="academics[contents][items][{{ $index }}][label]" maxlength="255" value="{{ $item['label'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Upload Card Image</label>
                                        <input type="file" name="academics[contents][items][{{ $index }}][image_file]" accept="image/*">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Card Summary</label>
                                    <textarea name="academics[contents][items][{{ $index }}][summary]" rows="4">{{ $item['summary'] ?? '' }}</textarea>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="academics-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('Academics Contents') }}</button>
                    </div>
                </form>
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
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-academics-features-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="academics">
                    <input type="hidden" name="section_key" value="features">
                    <input type="hidden" name="academics_features_version" value="0" data-academics-features-version>
                    <input type="hidden" name="academics_active_feature_index" value="" data-academics-active-feature-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="form-group">
                        <label>Section Eyebrow</label>
                        <input type="text" name="academics[features][eyebrow]" maxlength="120" value="{{ $featuresEditor['eyebrow'] ?? '' }}">
                    </div>

                    <div class="academics-cms-card-stack" data-academics-features-stack>
                        @foreach(($featuresEditor['items'] ?? []) as $index => $item)
                            <article class="academics-cms-card-editor" data-academics-feature-editor data-academics-feature-index="{{ $index }}">
                                <div class="academics-cms-card-editor-head">
                                    <h4>Feature Card {{ $loop->iteration }}</h4>
                                    <span>{{ !empty($item['wide']) ? 'Wide card' : 'Standard card' }}</span>
                                </div>

                                <input type="hidden" name="academics[features][items][{{ $index }}][wide]" value="{{ !empty($item['wide']) ? '1' : '0' }}">

                                <div class="form-group">
                                    <label>Card Title</label>
                                    <input type="text" name="academics[features][items][{{ $index }}][title]" maxlength="255" value="{{ $item['title'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Card Description</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => 'academics[features][items]['.$index.'][body]',
                                        'value' => $item['body'] ?? '',
                                        'placeholder' => 'Write the supporting copy for this offer card...',
                                    ])
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="academics-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel('What We Offer') }}</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-academics-preview-json>
{!! json_encode($academicsPreviewHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
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
        margin: 16px auto;
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

    .academics-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
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
            return;
        }

        const ACADEMICS_PREVIEW_MIN_LOADING_MS = 1500;
        let academicsPreviewFitFrame = null;

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
                .filter((element) => isAcademicsPreviewMeasuredElement(element));

            const contentBottom = visibleElements.reduce((maxBottom, element) => {
                return Math.max(maxBottom, getAcademicsPreviewElementBottom(element));
            }, scope.offsetHeight);

            return Math.max(1, Math.ceil(contentBottom));
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
            const payloads = document.querySelectorAll('[data-academics-preview-json]');
            const frameIndex = Array.from(document.querySelectorAll('[data-academics-preview-frame]')).indexOf(frame);
            const payload = payloads[frameIndex] || payloads[0];
            const explicitSessionId = options.sessionId;

            if (!payload || !frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__academicsPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            setAcademicsPreviewLoading(frame, true);

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

        const contentsForm = document.querySelector('[data-academics-contents-form]');
        const contentsStack = contentsForm?.querySelector('[data-academics-contents-stack]');
        const contentsVersionInput = contentsForm?.querySelector('[data-academics-contents-version]');
        const activeContentsIndexInput = contentsForm?.querySelector('[data-academics-active-contents-index]');
        const featuresForm = document.querySelector('[data-academics-features-form]');
        const featuresStack = featuresForm?.querySelector('[data-academics-features-stack]');
        const featuresVersionInput = featuresForm?.querySelector('[data-academics-features-version]');
        const activeFeatureIndexInput = featuresForm?.querySelector('[data-academics-active-feature-index]');

        function bumpEditorVersion(input) {
            if (input) {
                input.value = String(Date.now());
            }
        }

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

        async function confirmDeleteAcademicsCard(type, targetIndex) {
            const isContents = type === 'contents';
            const stack = isContents ? contentsStack : featuresStack;
            const selector = isContents ? '[data-academics-contents-editor]' : '[data-academics-feature-editor]';
            const indexAttribute = isContents ? 'data-academics-contents-index' : 'data-academics-feature-index';
            const versionInput = isContents ? contentsVersionInput : featuresVersionInput;
            const hiddenInput = isContents ? activeContentsIndexInput : activeFeatureIndexInput;
            const form = isContents ? contentsForm : featuresForm;
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

            modal.querySelectorAll('[data-academics-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-academics-editor-panel') === sectionKey;
                panel.hidden = !isActive;

                if (isActive) {
                    if (title) {
                        title.textContent = label || 'Edit academics section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the Academics page preview.';
                    }

                    let activeCardEditor = null;
                    if (sectionKey === 'contents') {
                        activeCardEditor = setActiveEditor(
                            contentsStack,
                            '[data-academics-contents-editor]',
                            'data-academics-contents-index',
                            activeContentsIndexInput,
                            options.cardIndex ?? null
                        );
                    } else if (sectionKey === 'features') {
                        activeCardEditor = setActiveEditor(
                            featuresStack,
                            '[data-academics-feature-editor]',
                            'data-academics-feature-index',
                            activeFeatureIndexInput,
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
            document.body.style.overflow = '';
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

                syncAcademicsPreviewHeight(targetFrame, data.height);
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-close-academics-editor]')) {
                event.preventDefault();
                closeAcademicsEditor();
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
            loadAcademicsPreview(frame);

            frame.addEventListener('load', () => {
                bindAcademicsPreviewDocument(frame);
                queueAcademicsPreviewSettledSync(frame);
                scheduleFitAllAcademicsPreviews();
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

                loadAcademicsPreview(frame, { sessionId });
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
                loadAcademicsPreview(frame);
            });
        };

        scheduleFitAllAcademicsPreviews();
        setActiveEditor(contentsStack, '[data-academics-contents-editor]', 'data-academics-contents-index', activeContentsIndexInput);
        setActiveEditor(featuresStack, '[data-academics-feature-editor]', 'data-academics-feature-index', activeFeatureIndexInput);
        window.__academicsCmsPreviewEditorReady = true;
    })();
</script>
