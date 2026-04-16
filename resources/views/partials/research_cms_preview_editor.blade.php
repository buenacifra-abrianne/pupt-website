@php
    $researchDefaults = \App\Support\ResearchCmsContent::defaults();
    $researchEditorData = \App\Support\ResearchCmsContent::fromInput($researchEditorData ?? [], null);
    $pageEditor = $researchEditorData['page'] ?? $researchDefaults['page'];
    $cardsEditor = $researchEditorData['cards'] ?? $researchDefaults['cards'];
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
        <div class="research-cms-preview-head">
            <div>
                <span class="research-cms-eyebrow">Research &amp; Extension CMS</span>
                <h3>Live website preview</h3>
                <p>Click the highlighted sections inside the preview to edit the page or manage cards.</p>
            </div>
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
            <section class="research-cms-editor-panel" data-research-editor-panel="page" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="research_extension">
                    <input type="hidden" name="section_key" value="page">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="research-cms-form-grid">
                        <div class="form-group">
                            <label>Eyebrow</label>
                            <input type="text" name="research[page][eyebrow]" maxlength="120" value="{{ $pageEditor['eyebrow'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Page Title</label>
                            <input type="text" name="research[page][title]" maxlength="255" value="{{ $pageEditor['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="research[page][description]" rows="5">{{ $pageEditor['description'] ?? '' }}</textarea>
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
                            <article class="research-cms-card-editor" data-research-card-editor data-research-card-index="{{ $index }}">
                                <input type="hidden" name="research[cards][{{ $index }}][image]" value="{{ $card['image'] ?? '' }}">

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="research[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="research[cards][{{ $index }}][description]" rows="4">{{ $card['description'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="research[cards][{{ $index }}][link]" maxlength="2048" value="{{ $card['link'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Upload Card Image</label>
                                    <div class="research-cms-upload-preview-shell">
                                        <img
                                            src="{{ \App\Support\NewsImage::url($card['image'] ?? null, 'assets/static_img/pupillar.jpeg') }}"
                                            alt="{{ ($card['title'] ?? '') !== '' ? $card['title'] : 'Research card preview' }}"
                                            class="research-cms-upload-preview-image"
                                        >
                                    </div>
                                    <input type="file" name="research[cards][{{ $index }}][image_file]" accept="image/*">
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-research-card-template>
                        <article class="research-cms-card-editor" data-research-card-editor data-research-card-index="__INDEX__">
                            <input type="hidden" name="research[cards][__INDEX__][image]" value="">

                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="research[cards][__INDEX__][title]" maxlength="255" value="">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="research[cards][__INDEX__][description]" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="research[cards][__INDEX__][link]" maxlength="2048" value="">
                            </div>

                            <div class="form-group">
                                <label>Upload Card Image</label>
                                <div class="research-cms-upload-preview-shell">
                                    <img
                                        src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                        alt="Research card preview"
                                        class="research-cms-upload-preview-image"
                                    >
                                </div>
                                <input type="file" name="research[cards][__INDEX__][image_file]" accept="image/*">
                            </div>
                        </article>
                    </template>

                    <div class="research-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Cards') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-research-preview-json>
{!! json_encode($researchPreviewHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

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
        margin: 16px auto;
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

    .research-cms-card-remove {
        width: 38px;
        height: 38px;
        border: none;
        border-radius: 12px;
        background: #f8ece6;
        color: #7f1113;
        cursor: pointer;
    }

    .research-cms-upload-preview-shell {
        width: min(240px, 100%);
        aspect-ratio: 4 / 3;
        overflow: hidden;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 16px;
        background: #f6ede8;
        margin-bottom: 12px;
    }

    .research-cms-upload-preview-image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
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
        const previewScript = document.querySelector('[data-research-preview-json]');
        const previewHtml = previewScript ? JSON.parse(previewScript.textContent || '""') : '';
        const modal = document.querySelector('[data-research-editor-modal]');
        const modalTitle = modal?.querySelector('#{{ $idPrefix }}-modal-title');
        const modalDescription = modal?.querySelector('[data-research-editor-description]');
        const panels = Array.from(document.querySelectorAll('[data-research-editor-panel]'));
        const frames = Array.from(document.querySelectorAll('[data-research-preview-frame]'));

        if (!modal || !frames.length) {
            return;
        }

        const closeEditor = () => {
            modal.hidden = true;
            panels.forEach((panel) => {
                panel.hidden = true;
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
            panels.forEach((panel) => {
                panel.hidden = panel.getAttribute('data-research-editor-panel') !== sectionKey;
            });

            if (modalTitle) {
                modalTitle.textContent = label || 'Edit research and extension section';
            }

            if (modalDescription) {
                modalDescription.textContent = sectionKey === 'cards'
                    ? 'Manage the public cards shown in the contents strip.'
                    : 'Update the page header content shown above the cards.';
            }

            modal.hidden = false;

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
                    openEditor('cards', 'Add research card');
                    window.setTimeout(() => addCard(), 0);
                    return;
                }

                const editCardTrigger = event.target.closest('[data-research-card-edit]');
                if (editCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = editCardTrigger.closest('[data-research-card-index]');
                    const cardIndex = card?.getAttribute('data-research-card-index') ?? null;
                    openEditor('cards', 'Edit research card', { cardIndex });
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
            if (typeof window.applyCmsPreviewFrameContent === 'function') {
                window.applyCmsPreviewFrameContent(frame, typeof previewHtml === 'string' ? previewHtml : '');
            } else {
                frame.srcdoc = typeof previewHtml === 'string' ? previewHtml : '';
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
            if (!data || data.type !== 'cms-research-preview-height') {
                return;
            }

            const targetFrame = frames.find((frame) => frame.contentWindow === event.source);
            if (!targetFrame) {
                return;
            }

            syncResearchPreviewHeight(targetFrame, data.height);
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

        const relabelCards = () => {
            return;
        };

        const submitCardsForm = () => {
            if (!cardsForm) {
                return;
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

        const deleteCardByIndex = (cardIndex) => {
            if (cardIndex === null || cardIndex === undefined) {
                return false;
            }

            const targetEditor = modal.querySelector(`[data-research-card-editor][data-research-card-index="${cardIndex}"]`);
            if (!targetEditor) {
                return false;
            }

            targetEditor.remove();
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor();

            return true;
        };

        const confirmDeleteCard = async (cardIndex) => {
            const targetEditor = modal.querySelector(`[data-research-card-editor][data-research-card-index="${cardIndex}"]`);
            if (!targetEditor) {
                return;
            }

            const titleInput = targetEditor.querySelector('input[name*="[title]"]');
            const cardTitle = String(titleInput?.value || '').trim();
            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Card',
                    message: cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this research card?',
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(
                    cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this research card?'
                );
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteCardByIndex(cardIndex);
            if (!deleted) {
                return;
            }

            submitCardsForm();
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

        const addCard = () => {
            if (!cardTemplate || !cardStack) {
                return;
            }

            const index = nextCardIndex();
            const fragment = cardTemplate.content.cloneNode(true);

            fragment.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/__INDEX__/g, String(index));
            });

            fragment.querySelectorAll('[data-research-card-index]').forEach((element) => {
                element.setAttribute('data-research-card-index', String(index));
            });

            cardStack.appendChild(fragment);
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor(index);
            focusCardEditor(index);
        };

        modal.addEventListener('click', (event) => {
            const addTrigger = event.target.closest('[data-add-research-card]');
            if (addTrigger) {
                event.preventDefault();
                addCard();
                return;
            }

            const removeTrigger = event.target.closest('[data-remove-research-card]');
            if (removeTrigger) {
                event.preventDefault();
                const editor = removeTrigger.closest('[data-research-card-editor]');
                if (!editor) {
                    return;
                }

                const editors = Array.from(cardStack?.querySelectorAll('[data-research-card-editor]') ?? []);
                const editorIndex = editors.indexOf(editor);
                editor.remove();
                bumpCardsVersion();
                relabelCards();
                const remainingEditors = Array.from(cardStack?.querySelectorAll('[data-research-card-editor]') ?? []);
                const fallbackEditor = remainingEditors[Math.max(0, editorIndex - 1)] || remainingEditors[0] || null;
                setActiveCardEditor(fallbackEditor?.getAttribute('data-research-card-index') ?? null);
            }
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
        window.__researchCmsPreviewEditorReady = true;
    })();
</script>
