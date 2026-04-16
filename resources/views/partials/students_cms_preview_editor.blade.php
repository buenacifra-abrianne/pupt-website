@php
    $studentsDefaults = \App\Support\StudentsCmsContent::defaults();
    $studentsEditorData = \App\Support\StudentsCmsContent::fromInput($studentsEditorData ?? [], null);
    $pageEditor = $studentsEditorData['page'] ?? $studentsDefaults['page'];
    $cardsEditor = $studentsEditorData['cards'] ?? $studentsDefaults['cards'];
    $organizationSectionsEditor = $studentsEditorData['organization_sections'] ?? ($studentsDefaults['organization_sections'] ?? []);
    $formClass = $studentsEditorFormClass ?? 'cms-save-form';
    $submitRoute = $studentsEditorSubmitRoute;
    $submitMode = $studentsEditorSubmitMode ?? 'save';
    $requestId = (int) ($studentsEditorRequestId ?? 0);
    $status = strtolower((string) ($studentsEditorStatus ?? ''));
    $idPrefix = trim((string) ($studentsEditorIdPrefix ?? 'students-editor'));
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
@endphp

<div class="students-cms-workspace">
    <div class="students-cms-preview-shell">
        <div class="students-cms-preview-head">
            <div>
                <span class="students-cms-eyebrow">Students CMS</span>
                <h3>Live website preview</h3>
                <p>Click the highlighted sections inside the preview to edit the page or manage student cards.</p>
            </div>
        </div>

        <div class="students-cms-preview-frame-shell">
            <div class="students-cms-preview-stage">
                <div class="students-cms-preview-canvas">
                    <iframe
                        title="Students page preview"
                        class="students-cms-preview-frame"
                        data-students-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="students-cms-modal" data-students-editor-modal hidden>
    <div class="students-cms-modal-backdrop" data-close-students-editor></div>

    <div class="students-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="students-cms-modal-close" data-close-students-editor aria-label="Close editor">&times;</button>

        <div class="students-cms-modal-header">
            <span class="students-cms-side-kicker">Students Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit students section</h3>
            <p data-students-editor-description>Select a highlighted section from the preview to edit it.</p>
        </div>

        <div class="students-cms-modal-panels">
            <section class="students-cms-editor-panel" data-students-editor-panel="page" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="page">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Eyebrow</label>
                            <input type="text" name="students[page][eyebrow]" maxlength="120" value="{{ $pageEditor['eyebrow'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Page Title</label>
                            <input type="text" name="students[page][title]" maxlength="255" value="{{ $pageEditor['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="students[page][description]" rows="5">{{ $pageEditor['description'] ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Hero Image Path</label>
                        <input type="text" name="students[page][hero_image]" maxlength="2048" value="{{ $pageEditor['hero_image'] ?? '' }}">
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Page Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="cards" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-students-cards-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="cards">
                    <input type="hidden" name="students_cards_version" value="0" data-students-cards-version>
                    <input type="hidden" name="students_active_card_index" value="" data-students-active-card-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-card-stack" data-students-card-stack>
                        @foreach($cardsEditor as $index => $card)
                            <article class="students-cms-card-editor" data-students-card-editor data-students-card-index="{{ $index }}">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="students[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="students[cards][{{ $index }}][description]" rows="4">{{ $card['description'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="students[cards][{{ $index }}][link]" maxlength="2048" value="{{ $card['link'] ?? '' }}">
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <template data-students-card-template>
                        <article class="students-cms-card-editor" data-students-card-editor data-students-card-index="__INDEX__">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="students[cards][__INDEX__][title]" maxlength="255" value="">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="students[cards][__INDEX__][description]" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="students[cards][__INDEX__][link]" maxlength="2048" value="">
                            </div>
                        </article>
                    </template>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Cards') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="students-cms-editor-panel" data-students-editor-panel="organizations" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" data-students-organizations-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="students">
                    <input type="hidden" name="section_key" value="organizations">
                    <input type="hidden" name="students_active_org_key" value="" data-students-active-org-key>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="students-cms-card-stack" data-students-org-stack>
                        @foreach($organizationSectionsEditor as $sectionIndex => $organizationSection)
                            @foreach(($organizationSection['items'] ?? []) as $orgIndex => $organization)
                                <article
                                    class="students-cms-card-editor"
                                    data-students-org-editor
                                    data-students-org-key="{{ $sectionIndex }}-{{ $orgIndex }}"
                                >
                                    <div class="students-cms-card-editor-head">
                                        <h4>{{ $organizationSection['title'] ?? 'Organizations' }}</h4>
                                        <span>{{ $organization['abbr'] ?? '' }}</span>
                                    </div>

                                    <input type="hidden" name="students[organization_sections][{{ $sectionIndex }}][title]" value="{{ $organizationSection['title'] ?? '' }}">
                                    <input type="hidden" name="students[organization_sections][{{ $sectionIndex }}][key]" value="{{ $organizationSection['key'] ?? '' }}">

                                    <div class="form-group">
                                        <label>Organization Name</label>
                                        <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][title]" maxlength="255" value="{{ $organization['title'] ?? '' }}">
                                    </div>

                                    <div class="students-cms-form-grid">
                                        <div class="form-group">
                                            <label>Abbreviation / Caption</label>
                                            <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][abbr]" maxlength="255" value="{{ $organization['abbr'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Link</label>
                                            <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][link]" maxlength="2048" value="{{ $organization['link'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Image Path</label>
                                        <input type="text" name="students[organization_sections][{{ $sectionIndex }}][items][{{ $orgIndex }}][image]" maxlength="2048" value="{{ $organization['image'] ?? '' }}">
                                    </div>
                                </article>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="students-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Organizations') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-students-preview-json>
{!! json_encode($studentsPreviewHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

<style>
    .students-cms-workspace {
        --students-preview-width: 1520px;
        --students-preview-height: 1800px;
        --students-preview-scale: 1;
        --students-preview-scaled-width: calc(var(--students-preview-width) * var(--students-preview-scale));
        --students-preview-scaled-height: calc(var(--students-preview-height) * var(--students-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .students-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .students-cms-preview-head {
        display: none;
    }

    .students-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .students-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .students-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--students-preview-scaled-width);
        max-width: 100%;
        height: var(--students-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .students-cms-preview-frame {
        display: block;
        width: var(--students-preview-width);
        min-width: var(--students-preview-width);
        height: var(--students-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--students-preview-scale));
        transform-origin: top left;
    }

    .students-cms-modal[hidden] {
        display: none;
    }

    .students-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
    }

    .students-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .students-cms-modal-dialog {
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

    .students-cms-modal-close {
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

    .students-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .students-cms-side-kicker,
    .students-cms-eyebrow {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .students-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .students-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.6;
    }

    .students-cms-modal-panels {
        padding: 22px 24px 24px;
        background: linear-gradient(180deg, rgba(255, 251, 247, 0.92) 0%, #fffdfc 100%);
    }

    .students-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .students-cms-card-stack {
        display: grid;
        gap: 0;
    }

    .students-cms-card-editor {
        padding: 18px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf6 100%);
        display: none;
    }

    .students-cms-card-editor.is-active {
        display: block;
    }

    .students-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .students-cms-card-editor-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .students-cms-card-editor-head span {
        color: #8a7a73;
        font-size: 0.8rem;
    }

    .students-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .students-cms-workspace {
            --students-preview-width: 1440px;
            --students-preview-height: 1760px;
            --students-preview-scale: 0.58;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .students-cms-modal-dialog {
            width: calc(100vw - 20px);
            margin: 10px auto;
            max-height: calc(100vh - 20px);
        }

        .students-cms-modal-panels,
        .students-cms-modal-header {
            padding: 18px 16px;
        }

        .students-cms-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    (() => {
        if (window.__studentsCmsPreviewEditorReady) {
            return;
        }

        const STUDENTS_PREVIEW_MIN_LOADING_MS = 1500;
        const previewScript = document.querySelector('[data-students-preview-json]');
        const previewHtml = previewScript ? JSON.parse(previewScript.textContent || '""') : '';
        const modal = document.querySelector('[data-students-editor-modal]');
        const modalTitle = modal?.querySelector('#{{ $idPrefix }}-modal-title');
        const modalDescription = modal?.querySelector('[data-students-editor-description]');
        const panels = Array.from(document.querySelectorAll('[data-students-editor-panel]'));
        const frames = Array.from(document.querySelectorAll('[data-students-preview-frame]'));

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

            const target = modal.querySelector(`[data-students-card-editor][data-students-card-index="${cardIndex}"]`);
            if (!target) {
                return;
            }

            target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            target.querySelector('input, textarea')?.focus();
        };

        const focusOrganizationEditor = (orgKey) => {
            if (!orgKey) {
                return;
            }

            const target = modal.querySelector(`[data-students-org-editor][data-students-org-key="${orgKey}"]`);
            if (!target) {
                return;
            }

            target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            target.querySelector('input, textarea')?.focus();
        };

        const openEditor = (sectionKey, label, options = {}) => {
            panels.forEach((panel) => {
                panel.hidden = panel.getAttribute('data-students-editor-panel') !== sectionKey;
            });

            if (modalTitle) {
                modalTitle.textContent = label || 'Edit students section';
            }

            if (modalDescription) {
                modalDescription.textContent = sectionKey === 'cards'
                    ? 'Manage the public cards shown in the student contents strip.'
                    : 'Update the student page hero and introduction content.';
            }

            modal.hidden = false;

            if (sectionKey === 'cards') {
                setActiveCardEditor(options.cardIndex ?? null);
                window.setTimeout(() => focusCardEditor(options.cardIndex ?? null), 40);
            } else if (sectionKey === 'organizations') {
                setActiveOrganizationEditor(options.orgKey ?? '');
                window.setTimeout(() => focusOrganizationEditor(options.orgKey ?? ''), 40);
            }
        };

        const fitStudentsPreview = (frame) => {
            const workspace = frame.closest('.students-cms-workspace');
            const shell = frame.closest('.students-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--students-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--students-preview-scale', `${scale}`);
        };

        const setStudentsPreviewLoading = (frame, isLoading) => {
            if (frame.__studentsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__studentsPreviewLoadingTimeout);
                frame.__studentsPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__studentsPreviewLoadingSession = (frame.__studentsPreviewLoadingSession || 0) + 1;
                frame.__studentsPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__studentsPreviewLoadingSession || 0,
                },
            }));
        };

        const finishStudentsPreviewLoading = (frame) => {
            const activeSession = frame.__studentsPreviewLoadingSession || 0;
            const startedAt = frame.__studentsPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, STUDENTS_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__studentsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__studentsPreviewLoadingTimeout);
            }

            frame.__studentsPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__studentsPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__studentsPreviewLoadingTimeout = null;
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
                    return Math.max(maxBottom, element.offsetTop + element.offsetHeight);
                }, scope.offsetHeight);

                return Math.max(1, Math.ceil(contentBottom));
            } catch (error) {
                console.warn('Unable to size students preview frame.', error);
                return 0;
            }
        };

        const syncStudentsPreviewHeight = (frame, nextHeight) => {
            const workspace = frame.closest('.students-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--students-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
            fitStudentsPreview(frame);
        };

        const scheduleStudentsPreviewSync = (frame) => {
            if (!frame) {
                return;
            }

            if (frame.__studentsPreviewSyncFrame !== undefined && frame.__studentsPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__studentsPreviewSyncFrame);
            }

            frame.__studentsPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureFrame(frame);

                if (measuredHeight > 0) {
                    syncStudentsPreviewHeight(frame, measuredHeight);
                } else {
                    fitStudentsPreview(frame);
                }

                frame.__studentsPreviewSyncFrame = null;
            });
        };

        const queueStudentsPreviewSettledSync = (frame) => {
            scheduleStudentsPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleStudentsPreviewSync(frame), delay);
            });
            finishStudentsPreviewLoading(frame);
        };

        const bindFrame = (frame) => {
            const doc = frame.contentDocument;
            if (!doc) {
                return;
            }

            doc.addEventListener('click', (event) => {
                const addCardTrigger = event.target.closest('[data-students-add-card-trigger]');
                if (addCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    openEditor('cards', 'Add student card');
                    window.setTimeout(() => addCard(), 0);
                    return;
                }

                const editCardTrigger = event.target.closest('[data-students-card-edit]');
                if (editCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = editCardTrigger.closest('[data-students-card-index]');
                    const cardIndex = card?.getAttribute('data-students-card-index') ?? null;
                    openEditor('cards', 'Edit student card', { cardIndex });
                    return;
                }

                const editOrgTrigger = event.target.closest('[data-students-org-edit]');
                if (editOrgTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = editOrgTrigger.closest('[data-students-org-index]');
                    const sectionIndex = card?.getAttribute('data-students-org-section-index') ?? '';
                    const orgIndex = card?.getAttribute('data-students-org-index') ?? '';
                    const orgKey = sectionIndex !== '' && orgIndex !== '' ? `${sectionIndex}-${orgIndex}` : '';
                    openEditor('organizations', 'Edit student organization', { orgKey });
                    return;
                }

                const deleteCardTrigger = event.target.closest('[data-students-card-delete]');
                if (deleteCardTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    const card = deleteCardTrigger.closest('[data-students-card-index]');
                    const cardIndex = card?.getAttribute('data-students-card-index') ?? null;
                    void confirmDeleteCard(cardIndex);
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
                    const rawCardIndex = sectionTrigger.getAttribute('data-students-card-index');
                    const cardIndex = rawCardIndex === null ? null : Number(rawCardIndex);
                    const orgSectionIndex = sectionTrigger.getAttribute('data-students-org-section-index');
                    const orgIndex = sectionTrigger.getAttribute('data-students-org-index');
                    const orgKey = orgSectionIndex !== null && orgIndex !== null ? `${orgSectionIndex}-${orgIndex}` : '';

                    openEditor(sectionKey, label, { cardIndex, orgKey });
                    return;
                }

                const anchor = event.target.closest('a');
                if (anchor) {
                    event.preventDefault();
                }
            });

            const schedule = () => queueStudentsPreviewSettledSync(frame);
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
            setStudentsPreviewLoading(frame, true);
            if (typeof window.applyCmsPreviewFrameContent === 'function') {
                window.applyCmsPreviewFrameContent(frame, typeof previewHtml === 'string' ? previewHtml : '');
            } else {
                frame.srcdoc = typeof previewHtml === 'string' ? previewHtml : '';
            }
        };

        frames.forEach((frame) => {
            frame.addEventListener('load', () => {
                bindFrame(frame);
                queueStudentsPreviewSettledSync(frame);
            });

            loadFrame(frame);
        });

        document.querySelectorAll('[data-close-students-editor]').forEach((trigger) => {
            trigger.addEventListener('click', closeEditor);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeEditor();
            }
        });

        const cardTemplate = modal.querySelector('[data-students-card-template]');
        const cardStack = modal.querySelector('[data-students-card-stack]');
        const cardsForm = modal.querySelector('[data-students-cards-form]');
        const versionInput = modal.querySelector('[data-students-cards-version]');
        const activeCardIndexInput = modal.querySelector('[data-students-active-card-index]');
        const orgStack = modal.querySelector('[data-students-org-stack]');
        const activeOrgKeyInput = modal.querySelector('[data-students-active-org-key]');

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

            const targetEditor = modal.querySelector(`[data-students-card-editor][data-students-card-index="${cardIndex}"]`);
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
            const targetEditor = modal.querySelector(`[data-students-card-editor][data-students-card-index="${cardIndex}"]`);
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
                        : 'Do you want to delete this student card?',
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(
                    cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this student card?'
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
            const editors = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? []);

            if (!editors.length) {
                if (activeCardIndexInput) {
                    activeCardIndexInput.value = '';
                }
                return;
            }

            let targetEditor = null;

            if (cardIndex !== null && cardIndex !== undefined) {
                targetEditor = editors.find((editor) => editor.getAttribute('data-students-card-index') === String(cardIndex)) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                editor.classList.toggle('is-active', editor === targetEditor);
            });

            if (activeCardIndexInput) {
                activeCardIndexInput.value = targetEditor?.getAttribute('data-students-card-index') || '';
            }
        };

        const setActiveOrganizationEditor = (orgKey = '') => {
            const editors = Array.from(orgStack?.querySelectorAll('[data-students-org-editor]') ?? []);

            if (!editors.length) {
                if (activeOrgKeyInput) {
                    activeOrgKeyInput.value = '';
                }
                return;
            }

            let targetEditor = null;

            if (orgKey !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-students-org-key') === orgKey) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                editor.classList.toggle('is-active', editor === targetEditor);
            });

            if (activeOrgKeyInput) {
                activeOrgKeyInput.value = targetEditor?.getAttribute('data-students-org-key') || '';
            }
        };

        const nextCardIndex = () => {
            const indexes = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? [])
                .map((editor) => Number(editor.getAttribute('data-students-card-index') || '0'))
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

            fragment.querySelectorAll('[data-students-card-index]').forEach((element) => {
                element.setAttribute('data-students-card-index', String(index));
            });

            cardStack.appendChild(fragment);
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor(index);
            focusCardEditor(index);
        };

        modal.addEventListener('click', (event) => {
            const addTrigger = event.target.closest('[data-add-students-card]');
            if (addTrigger) {
                event.preventDefault();
                addCard();
                return;
            }

            const removeTrigger = event.target.closest('[data-remove-students-card]');
            if (removeTrigger) {
                event.preventDefault();
                const editor = removeTrigger.closest('[data-students-card-editor]');
                if (!editor) {
                    return;
                }

                const editors = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? []);
                const editorIndex = editors.indexOf(editor);
                editor.remove();
                bumpCardsVersion();
                relabelCards();
                const remainingEditors = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? []);
                const fallbackEditor = remainingEditors[Math.max(0, editorIndex - 1)] || remainingEditors[0] || null;
                setActiveCardEditor(fallbackEditor?.getAttribute('data-students-card-index') ?? null);
            }
        });

        window.addEventListener('resize', () => {
            frames.forEach((frame) => scheduleStudentsPreviewSync(frame));
        });

        window.addEventListener('cms:tab-activated', (event) => {
            const panel = event.detail?.panel;

            frames.forEach((frame) => {
                if (panel && panel.contains(frame)) {
                    loadFrame(frame);
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 40);
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 180);
                }
            });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                frames.forEach((frame) => scheduleStudentsPreviewSync(frame));
            });

            document.querySelectorAll('.students-cms-preview-frame-shell').forEach((shell) => {
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
                frames.forEach((frame) => scheduleStudentsPreviewSync(frame));
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('pageshow', () => {
            frames.forEach((frame) => scheduleStudentsPreviewSync(frame));
        });

        window.addEventListener('load', () => {
            frames.forEach((frame) => scheduleStudentsPreviewSync(frame));
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                frames.forEach((frame) => scheduleStudentsPreviewSync(frame));
            }
        });

        window.refreshStudentsCmsPreview = (scope) => {
            const scopedFrames = scope
                ? Array.from(scope.querySelectorAll('[data-students-preview-frame]'))
                : frames;

            scopedFrames.forEach((frame) => loadFrame(frame));
        };

        relabelCards();
        setActiveCardEditor();
        setActiveOrganizationEditor('');
        window.__studentsCmsPreviewEditorReady = true;
    })();
</script>
