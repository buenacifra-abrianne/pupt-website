@php
    $eventsDefaults = \App\Support\EventsCmsContent::defaults();
    $eventsEditorData = \App\Support\EventsCmsContent::fromInput($eventsEditorData ?? [], null);
    $pageEditor = $eventsEditorData['page'] ?? $eventsDefaults['page'];
    $cardsEditor = $eventsEditorData['cards'] ?? $eventsDefaults['cards'];
    $categoryOptions = \App\Support\EventsCmsContent::categoryOptions();
    $formClass = $eventsEditorFormClass ?? 'cms-save-form';
    $submitRoute = $eventsEditorSubmitRoute;
    $submitMode = $eventsEditorSubmitMode ?? 'save';
    $requestId = (int) ($eventsEditorRequestId ?? 0);
    $status = strtolower((string) ($eventsEditorStatus ?? ''));
    $idPrefix = trim((string) ($eventsEditorIdPrefix ?? 'events-editor'));
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
@endphp

<div class="events-cms-workspace">
    <div class="events-cms-preview-shell">
        <div class="events-cms-preview-head">
            <div>
                <span class="events-cms-eyebrow">Events CMS</span>
                <h3>Live website preview</h3>
                <p>Click the highlighted sections inside the preview to edit the contents of the Events page.</p>
            </div>
        </div>

        <div class="events-cms-preview-frame-shell">
            <div class="events-cms-preview-stage">
                <div class="events-cms-preview-canvas">
                    <iframe
                        title="Events page preview"
                        class="events-cms-preview-frame"
                        data-events-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="events-cms-modal" data-events-editor-modal hidden>
    <div class="events-cms-modal-backdrop" data-close-events-editor></div>

    <div class="events-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="events-cms-modal-close" data-close-events-editor aria-label="Close editor">&times;</button>

        <div class="events-cms-modal-header">
            <span class="events-cms-side-kicker">Events Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit events section</h3>
            <p data-events-editor-description>Select a highlighted section from the preview to edit it.</p>
        </div>

        <div class="events-cms-modal-panels">
            <section class="events-cms-editor-panel" data-events-editor-panel="page" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="events">
                    <input type="hidden" name="section_key" value="page">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="events-cms-form-grid">
                        <div class="form-group">
                            <label>Eyebrow</label>
                            <input type="text" name="events[page][eyebrow]" maxlength="120" value="{{ $pageEditor['eyebrow'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Page Title</label>
                            <input type="text" name="events[page][title]" maxlength="255" value="{{ $pageEditor['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'events[page][description]',
                            'value' => $pageEditor['description'] ?? '',
                            'placeholder' => 'Write the intro text shown above the events listing...',
                        ])
                    </div>

                    <div class="events-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Page Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="events-cms-editor-panel" data-events-editor-panel="cards" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-events-cards-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="events">
                    <input type="hidden" name="section_key" value="cards">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="events-cms-card-stack" data-events-card-stack>
                        @foreach($cardsEditor as $index => $card)
                            <article class="events-cms-card-editor" data-events-card-editor data-events-card-index="{{ $index }}">
                                <div class="events-cms-form-grid">
                                    <div class="form-group">
                                        <label>Event Title</label>
                                        <input type="text" name="events[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="events[cards][{{ $index }}][category]">
                                            @foreach($categoryOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(($card['category'] ?? 'events') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Event Date</label>
                                        <input type="date" name="events[cards][{{ $index }}][event_date]" value="{{ $card['event_date'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input type="text" name="events[cards][{{ $index }}][location]" maxlength="255" value="{{ $card['location'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" name="events[cards][{{ $index }}][start_time]" value="{{ $card['start_time'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" name="events[cards][{{ $index }}][end_time]" value="{{ $card['end_time'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Link</label>
                                        <input type="text" name="events[cards][{{ $index }}][image]" maxlength="2048" value="{{ $card['image'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Upload Card Image</label>
                                        <input type="file" name="events[cards][{{ $index }}][image_file]" accept="image/*">
                                    </div>
                                </div>

                                <label class="events-cms-feature-check">
                                    <input type="checkbox" name="events[cards][{{ $index }}][featured]" value="1" @checked(!empty($card['featured']))>
                                    <span class="events-cms-feature-copy">
                                        <strong>Featured Event</strong>
                                        <small>Pin this card to the highlighted event section.</small>
                                    </span>
                                </label>

                                <div class="form-group">
                                    <label>Card Summary</label>
                                    <textarea name="events[cards][{{ $index }}][summary]" rows="4">{{ $card['summary'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Modal Details</label>
                                    @include('partials.rich_text_editor', [
                                        'name' => 'events[cards]['.$index.'][content]',
                                        'value' => $card['content'] ?? '',
                                        'placeholder' => 'Write the full event details shown in the public modal...',
                                    ])
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-events-card-template>
                        <article class="events-cms-card-editor" data-events-card-editor data-events-card-index="__INDEX__" data-events-new-card="1">
                            <div class="events-cms-form-grid">
                                <div class="form-group">
                                    <label>Event Title</label>
                                    <input type="text" name="events[cards][__INDEX__][title]" maxlength="255" value="">
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="events[cards][__INDEX__][category]">
                                        @foreach($categoryOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($value === 'events')>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Event Date</label>
                                    <input type="date" name="events[cards][__INDEX__][event_date]" value="">
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="events[cards][__INDEX__][location]" maxlength="255" value="">
                                </div>
                                <div class="form-group">
                                    <label>Start Time</label>
                                    <input type="time" name="events[cards][__INDEX__][start_time]" value="">
                                </div>
                                <div class="form-group">
                                    <label>End Time</label>
                                    <input type="time" name="events[cards][__INDEX__][end_time]" value="">
                                </div>
                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="events[cards][__INDEX__][image]" maxlength="2048" value="">
                                </div>
                                <div class="form-group">
                                    <label>Upload Card Image</label>
                                    <input type="file" name="events[cards][__INDEX__][image_file]" accept="image/*">
                                </div>
                            </div>

                            <label class="events-cms-feature-check">
                                <input type="checkbox" name="events[cards][__INDEX__][featured]" value="1">
                                <span class="events-cms-feature-copy">
                                    <strong>Featured Event</strong>
                                    <small>Pin this card to the highlighted event section.</small>
                                </span>
                            </label>

                            <div class="form-group">
                                <label>Card Summary</label>
                                <textarea name="events[cards][__INDEX__][summary]" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Modal Details</label>
                                @include('partials.rich_text_editor', [
                                    'name' => 'events[cards][__INDEX__][content]',
                                    'value' => '',
                                    'placeholder' => 'Write the full event details shown in the public modal...',
                                ])
                            </div>
                        </article>
                    </template>

                    <div class="events-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Event Listings') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-events-preview-json>
{!! json_encode($eventsPreviewHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .events-cms-workspace {
        --events-preview-width: 1520px;
        --events-preview-height: 1800px;
        --events-preview-scale: 1;
        --events-preview-scaled-width: calc(var(--events-preview-width) * var(--events-preview-scale));
        --events-preview-scaled-height: calc(var(--events-preview-height) * var(--events-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .events-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .events-cms-preview-head {
        display: none;
    }

    .events-cms-preview-head h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.1rem;
    }

    .events-cms-preview-head p {
        margin: 8px 0 0;
        color: #6f625c;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .events-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .events-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .events-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--events-preview-scaled-width);
        max-width: 100%;
        height: var(--events-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .events-cms-preview-frame {
        display: block;
        width: var(--events-preview-width);
        min-width: var(--events-preview-width);
        height: var(--events-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--events-preview-scale));
        transform-origin: top left;
    }

    .events-cms-modal[hidden] {
        display: none;
    }

    .events-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
    }

    .events-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .events-cms-modal-dialog {
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

    .events-cms-modal-close {
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

    .events-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .events-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .events-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.55;
    }

    .events-cms-eyebrow,
    .events-cms-side-kicker {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .events-cms-modal-panels {
        padding: 22px 24px 24px;
        background: linear-gradient(180deg, rgba(255, 251, 247, 0.92) 0%, #fffdfc 100%);
    }

    .events-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .events-cms-card-stack {
        display: grid;
        gap: 16px;
    }

    .events-cms-card-editor {
        padding: 18px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf6 100%);
        box-shadow: 0 8px 22px rgba(92, 12, 6, 0.05);
    }

    .events-cms-card-editor[hidden] {
        display: none !important;
    }

    .events-cms-card-editor.is-selected {
        border-color: rgba(127, 17, 19, 0.28);
        box-shadow: 0 18px 34px rgba(92, 12, 6, 0.1);
    }

    .events-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 10px;
    }

    .events-cms-delete-card {
        min-height: 38px;
        padding: 0 14px;
        border-color: rgba(127, 17, 19, 0.18);
        color: #7f1113;
        background: rgba(255, 250, 244, 0.92);
        box-shadow: none;
    }

    .events-cms-delete-card:hover {
        border-color: #7f1113;
        background: #7f1113;
        color: #fffaf4;
    }

    .events-cms-feature-check {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 16px 0 10px;
        padding: 14px 16px;
        border: 1px solid rgba(127, 17, 19, 0.1);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(127, 17, 19, 0.04) 0%, rgba(242, 201, 76, 0.08) 100%);
        color: #5c0000;
    }

    .events-cms-feature-check input {
        width: 20px;
        height: 20px;
        accent-color: #800000;
        flex-shrink: 0;
    }

    .events-cms-feature-copy {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .events-cms-feature-copy strong {
        color: #5c0000;
        font-size: 0.94rem;
        line-height: 1.2;
    }

    .events-cms-feature-copy small {
        color: #7c6660;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .events-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .events-cms-workspace {
            --events-preview-width: 1440px;
            --events-preview-height: 1760px;
            --events-preview-scale: 0.58;
        }

        .events-cms-form-grid {
            grid-template-columns: 1fr;
        }

        .events-cms-preview-head,
        .events-cms-card-editor-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .events-cms-modal-dialog {
            width: min(100vw - 20px, 1080px);
            max-height: calc(100vh - 20px);
            margin: 10px auto;
        }

        .events-cms-modal-header,
        .events-cms-modal-panels {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

<script>
    (() => {
        if (window.__eventsCmsPreviewEditorReady) {
            return;
        }

        const EVENTS_PREVIEW_MIN_LOADING_MS = 1500;
        let eventsPreviewFitFrame = null;

        function syncEditorsInScope(scope) {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
        }

        function fitEventsPreview(frame) {
            const workspace = frame.closest('.events-cms-workspace');
            const shell = frame.closest('.events-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--events-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--events-preview-scale', `${scale}`);
        }

        function setEventsPreviewLoading(frame, isLoading) {
            const canvas = frame?.closest('.events-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            if (frame.__eventsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__eventsPreviewLoadingTimeout);
                frame.__eventsPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__eventsPreviewLoadingSession = (frame.__eventsPreviewLoadingSession || 0) + 1;
                frame.__eventsPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__eventsPreviewLoadingSession || 0,
                },
            }));
        }

        function finishEventsPreviewLoading(frame) {
            const canvas = frame?.closest('.events-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            const activeSession = frame.__eventsPreviewLoadingSession || 0;
            const startedAt = frame.__eventsPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, EVENTS_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__eventsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__eventsPreviewLoadingTimeout);
            }

            frame.__eventsPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__eventsPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__eventsPreviewLoadingTimeout = null;
            }, remaining);
        }

        function getEventsPreviewElementBottom(element) {
            return element.offsetTop + element.offsetHeight;
        }

        function isEventsPreviewMeasuredElement(element) {
            if (!(element instanceof HTMLElement)) {
                return false;
            }

            const styles = window.getComputedStyle(element);
            return styles.display !== 'none'
                && styles.visibility !== 'hidden'
                && styles.position !== 'fixed';
        }

        function measureEventsPreviewHeight(frame) {
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
                .filter((element) => isEventsPreviewMeasuredElement(element));

            const contentBottom = visibleElements.reduce((maxBottom, element) => {
                return Math.max(maxBottom, getEventsPreviewElementBottom(element));
            }, 0);

            return Math.max(
                1,
                Math.ceil(contentBottom),
                Math.ceil(scope.scrollHeight || 0),
                Math.ceil(doc.documentElement?.scrollHeight || 0),
                Math.ceil(doc.body?.scrollHeight || 0)
            );
        }

        function syncEventsPreviewHeight(frame, nextHeight) {
            const workspace = frame.closest('.events-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--events-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
            fitEventsPreview(frame);
        }

        function scheduleEventsPreviewSync(frame) {
            if (!frame) {
                return;
            }

            if (frame.__eventsPreviewSyncFrame !== undefined && frame.__eventsPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__eventsPreviewSyncFrame);
            }

            frame.__eventsPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureEventsPreviewHeight(frame);

                if (measuredHeight > 0) {
                    syncEventsPreviewHeight(frame, measuredHeight);
                } else {
                    fitEventsPreview(frame);
                }

                frame.__eventsPreviewSyncFrame = null;
            });
        }

        function queueEventsPreviewSettledSync(frame) {
            scheduleEventsPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleEventsPreviewSync(frame), delay);
            });
            finishEventsPreviewLoading(frame);
        }

        function bindEventsPreviewDocument(frame) {
            const doc = frame.contentDocument;
            const win = frame.contentWindow;

            if (!doc) {
                return;
            }

            if (typeof frame.__eventsPreviewCleanup === 'function') {
                frame.__eventsPreviewCleanup();
            }

            const cleanups = [];
            const schedule = () => queueEventsPreviewSettledSync(frame);
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
                const observer = new ResizeObserver(() => schedule());
                if (doc.documentElement) observer.observe(doc.documentElement);
                if (doc.body) observer.observe(doc.body);
                if (main) observer.observe(main);
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

            frame.__eventsPreviewCleanup = () => {
                cleanups.forEach((cleanup) => cleanup());
            };
        }

        function fitAllEventsPreviews() {
            document.querySelectorAll('[data-events-preview-frame]').forEach((frame) => {
                scheduleEventsPreviewSync(frame);
            });
        }

        function loadEventsPreview(frame, options = {}) {
            const payloads = document.querySelectorAll('[data-events-preview-json]');
            const frameIndex = Array.from(document.querySelectorAll('[data-events-preview-frame]')).indexOf(frame);
            const payload = payloads[frameIndex] || payloads[0];
            const explicitSessionId = options.sessionId;

            if (!payload || !frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__eventsPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            setEventsPreviewLoading(frame, true);

            try {
                frame.srcdoc = JSON.parse(payload.textContent || '""');
            } catch (_) {
                frame.srcdoc = '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>';
            }
        }

        function scheduleFitAllEventsPreviews() {
            if (eventsPreviewFitFrame !== null) {
                window.cancelAnimationFrame(eventsPreviewFitFrame);
            }

            eventsPreviewFitFrame = window.requestAnimationFrame(() => {
                fitAllEventsPreviews();
                window.setTimeout(fitAllEventsPreviews, 140);
                eventsPreviewFitFrame = null;
            });
        }

        function setActiveEventsCardEditor(targetIndex = null) {
            const cardsPanel = document.querySelector('[data-events-editor-panel="cards"]');
            const editors = Array.from(cardsPanel?.querySelectorAll('[data-events-card-editor]') || []);

            if (!editors.length) {
                return null;
            }

            const normalizedIndex = targetIndex === null || targetIndex === undefined || targetIndex === ''
                ? null
                : String(targetIndex);

            let activeEditor = null;

            editors.forEach((editor) => {
                const isMatch = normalizedIndex !== null && editor.getAttribute('data-events-card-index') === normalizedIndex;
                editor.hidden = normalizedIndex !== null && !isMatch;
                editor.classList.toggle('is-selected', isMatch);

                if (isMatch) {
                    activeEditor = editor;
                }
            });

            return activeEditor;
        }

        function getNextEventsCardIndex(stack) {
            const indexes = Array.from(stack.querySelectorAll('[data-events-card-editor]'))
                .map((editor) => Number(editor.getAttribute('data-events-card-index')))
                .filter((value) => Number.isFinite(value));

            if (!indexes.length) {
                return 0;
            }

            return Math.max(...indexes) + 1;
        }

        function deleteEventsCardEditor(editor, options = {}) {
            const stack = editor?.closest('[data-events-card-stack]');

            if (!editor || !stack) {
                return false;
            }

            const wasSelected = editor.classList.contains('is-selected');
            editor.remove();

            const remainingEditors = Array.from(stack.querySelectorAll('[data-events-card-editor]'));

            if (!remainingEditors.length) {
                return true;
            }

            if (wasSelected && options.keepFocus !== false) {
                const fallbackEditor = remainingEditors[0];
                const fallbackIndex = fallbackEditor.getAttribute('data-events-card-index');
                setActiveEventsCardEditor(fallbackIndex);

                const firstField = fallbackEditor.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                firstField?.focus();
            }

            return true;
        }

        function openEventsEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-events-editor-modal]');
            if (!modal) {
                return;
            }

            const title = modal.querySelector('#{{ $idPrefix }}-modal-title');
            const description = modal.querySelector('[data-events-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';

            modal.querySelectorAll('[data-events-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-events-editor-panel') === sectionKey;
                panel.hidden = !isActive;

                if (isActive) {
                    if (title) {
                        title.textContent = label || 'Edit events section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the events page preview.';
                    }

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const activeCardEditor = sectionKey === 'cards'
                        ? setActiveEventsCardEditor(options.cardIndex ?? null)
                        : null;
                    const focusScope = activeCardEditor || panel;
                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                    firstField?.focus();
                }
            });
        }

        function addEventsCard(options = {}) {
            const cardsPanel = document.querySelector('[data-events-editor-panel="cards"]');
            const form = cardsPanel?.querySelector('[data-events-cards-form]');
            const stack = form?.querySelector('[data-events-card-stack]');
            const template = form?.querySelector('[data-events-card-template]');

            if (!stack || !template) {
                return null;
            }

            const existingNewCard = stack.querySelector('[data-events-card-editor][data-events-new-card="1"]');
            if (existingNewCard) {
                setActiveEventsCardEditor(existingNewCard.getAttribute('data-events-card-index'));

                if (options.focus !== false) {
                    const existingField = existingNewCard.querySelector('input[type="text"], input[type="date"], textarea, select');
                    existingField?.focus();
                }

                return existingNewCard;
            }

            const nextIndex = getNextEventsCardIndex(stack);
            const html = template.innerHTML
                .replaceAll('__INDEX__', String(nextIndex))
                .replaceAll('__CARD_NUMBER__', String(stack.querySelectorAll('[data-events-card-editor]').length + 1));

            stack.insertAdjacentHTML('beforeend', html);

            const newCard = stack.lastElementChild;
            if (newCard && typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(newCard);
            }

            setActiveEventsCardEditor(String(nextIndex));

            if (options.focus !== false) {
                const firstField = newCard?.querySelector('input[type="text"], input[type="date"], textarea, select');
                firstField?.focus();
            }

            return newCard;
        }

        function closeEventsEditor() {
            const modal = document.querySelector('[data-events-editor-modal]');
            if (!modal) {
                return;
            }

            modal.hidden = true;
            document.body.style.overflow = '';
        }

        function bindAddEventsCard() {
            document.querySelectorAll('[data-add-events-card]').forEach((button) => {
                if (button.dataset.eventsCardBound === '1') {
                    return;
                }

                button.dataset.eventsCardBound = '1';

                button.addEventListener('click', () => {
                    addEventsCard();
                });
            });
        }

        function deleteEventsCard(trigger) {
            const editor = trigger.closest('[data-events-card-editor]');
            if (!editor) {
                return;
            }

            confirmEventsCardDelete(editor.getAttribute('data-events-card-index'));
        }

        function deleteEventsCardByIndex(cardIndex, options = {}) {
            const form = document.querySelector('[data-events-cards-form]');
            const editor = form?.querySelector(`[data-events-card-editor][data-events-card-index="${cardIndex}"]`);
            if (!editor) {
                return false;
            }

            const deleted = deleteEventsCardEditor(editor, options);
            if (!deleted) {
                return false;
            }

            const frame = document.querySelector('[data-events-preview-frame]');
            frame?.contentWindow?.postMessage({
                type: 'cms-events-prune-card',
                cardIndex: Number(cardIndex),
            }, '*');

            return true;
        }

        async function confirmEventsCardDelete(cardIndex) {
            const form = document.querySelector('[data-events-cards-form]');
            const editor = form?.querySelector(`[data-events-card-editor][data-events-card-index="${cardIndex}"]`);
            if (!editor) {
                return;
            }

            const titleInput = editor.querySelector('input[name*="[title]"]');
            const cardTitle = String(titleInput?.value || '').trim();
            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Event',
                    message: cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this event card?',
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(
                    cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this event card?'
                );
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteEventsCardByIndex(cardIndex);
            if (!deleted) {
                return;
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Event deleted successfully.', 'success', 'Event');
            }
        }

        window.openEventsCmsSection = openEventsEditor;

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-events-add-card') {
                openEventsEditor('cards', data.label || 'Add event card');
                window.setTimeout(() => addEventsCard(), 0);
                return;
            }

            if (data.type === 'cms-events-edit-card') {
                openEventsEditor('cards', data.label || 'Edit event card', {
                    cardIndex: data.cardIndex,
                });
                return;
            }

            if (data.type === 'cms-events-delete-card') {
                confirmEventsCardDelete(data.cardIndex);
                return;
            }

            if (data.type === 'cms-events-preview-height') {
                const targetFrame = Array.from(document.querySelectorAll('[data-events-preview-frame]'))
                    .find((frame) => frame.contentWindow === event.source);

                if (!targetFrame) {
                    return;
                }

                syncEventsPreviewHeight(targetFrame, data.height);
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-close-events-editor]')) {
                event.preventDefault();
                closeEventsEditor();
                return;
            }

            const deleteTrigger = event.target.closest('[data-delete-events-card]');
            if (deleteTrigger) {
                event.preventDefault();
                deleteEventsCard(deleteTrigger);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeEventsEditor();
            }
        });

        document.querySelectorAll('.{{ $formClass }}').forEach((form) => {
            form.addEventListener('submit', () => syncEditorsInScope(form));
        });

        document.querySelectorAll('[data-events-preview-frame]').forEach((frame) => {
            loadEventsPreview(frame);

            frame.addEventListener('load', () => {
                bindEventsPreviewDocument(frame);
                queueEventsPreviewSettledSync(frame);
                scheduleFitAllEventsPreviews();
            });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAllEventsPreviews();
            });

            document.querySelectorAll('.events-cms-preview-frame-shell').forEach((shell) => {
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
                scheduleFitAllEventsPreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('resize', scheduleFitAllEventsPreviews);
        window.addEventListener('pageshow', scheduleFitAllEventsPreviews);
        window.addEventListener('load', scheduleFitAllEventsPreviews);
        window.addEventListener('cms:tab-activated', (event) => {
            const tabPanel = event.detail?.panel;
            const sessionId = Number(event.detail?.sessionId || 0) || undefined;

            document.querySelectorAll('[data-events-preview-frame]').forEach((frame) => {
                if (!tabPanel || !tabPanel.contains(frame)) {
                    return;
                }

                loadEventsPreview(frame, { sessionId });
                window.setTimeout(() => scheduleFitAllEventsPreviews(), 40);
                window.setTimeout(() => scheduleFitAllEventsPreviews(), 180);
            });
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAllEventsPreviews();
            }
        });

        window.refreshEventsCmsPreview = (scope) => {
            const frames = scope
                ? Array.from(scope.querySelectorAll('[data-events-preview-frame]'))
                : Array.from(document.querySelectorAll('[data-events-preview-frame]'));

            frames.forEach((frame) => {
                loadEventsPreview(frame);
            });
        };

        bindAddEventsCard();
        scheduleFitAllEventsPreviews();
        window.__eventsCmsPreviewEditorReady = true;
    })();
</script>
