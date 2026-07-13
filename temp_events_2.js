
    (() => {
        if (window.__eventsCmsPreviewEditorReady) {
            return;
        }

        const EVENTS_PREVIEW_MIN_LOADING_MS = 800;
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

        window.__eventsPreviewCache = window.__eventsPreviewCache || {};

        function loadEventsPreview(frame, options = {}) {
            const explicitSessionId = options.sessionId;
            const targetKey = 'overview';

            if (!frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__eventsPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            setEventsPreviewLoading(frame, true);

            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, html);
                } else {
                    frame.srcdoc = html;
                }
            };

            if (window.__eventsPreviewCache[targetKey]) {
                applyHtml(window.__eventsPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/events/${targetKey}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__eventsPreviewCache[targetKey] = previewHtml;
                    applyHtml(previewHtml);
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
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
            const stack = cardsPanel?.querySelector('[data-events-card-stack]');
            const activeIndexField = cardsPanel?.querySelector('[data-events-active-card-index]');
            const editors = Array.from(cardsPanel?.querySelectorAll('[data-events-card-editor]') || []);

            if (!editors.length) {
                refreshEventsCardGroups(cardsPanel);
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

            if (stack instanceof HTMLElement) {
                const editorGroup = activeEditor?.closest('[data-events-card-group]')?.getAttribute('data-events-card-group') || '';
                stack.dataset.eventsVisibleGroup = editorGroup !== '' ? editorGroup : 'active';
            }

            if (activeIndexField instanceof HTMLInputElement) {
                activeIndexField.value = activeEditor?.getAttribute('data-events-card-index') || '';
            }

            refreshEventsCardGroups(cardsPanel);
            syncEventsFeaturedToggles(cardsPanel);

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

        function markEventsCardsChanged(form) {
            const marker = form?.querySelector('[data-events-cards-version]');
            if (!marker) {
                return;
            }

            const currentValue = Number(marker.value || '0');
            marker.value = String(Number.isFinite(currentValue) ? currentValue + 1 : 1);
        }

        function shouldTrackEventsCardField(target) {
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

        function bindEventsCardsDirtyTracking(form) {
            if (!form || form.dataset.eventsDirtyTrackingBound === '1') {
                return;
            }

            form.dataset.eventsDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackEventsCardField(event.target)) {
                    return;
                }

                markEventsCardsChanged(form);
            };

            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
        }

        function syncEventsFeaturedToggles(scope) {
            const form = scope?.matches?.('[data-events-cards-form]')
                ? scope
                : scope?.querySelector?.('[data-events-cards-form]') || document.querySelector('[data-events-cards-form]');

            if (!(form instanceof HTMLElement)) {
                return;
            }

            const toggles = Array.from(form.querySelectorAll('.events-cms-feature-toggle'));
            const checkedToggles = toggles.filter((toggle) => toggle.checked);
            const activeToggle = checkedToggles[0] || null;

            checkedToggles.slice(1).forEach((toggle) => {
                toggle.checked = false;
            });

            toggles.forEach((toggle) => {
                const wrapper = toggle.closest('[data-events-feature-check]');
                const note = wrapper?.querySelector('[data-events-feature-note]');
                const shouldLock = activeToggle !== null && activeToggle !== toggle;

                toggle.disabled = shouldLock;
                toggle.setAttribute('aria-disabled', shouldLock ? 'true' : 'false');
                wrapper?.classList.toggle('is-locked', shouldLock);

                if (note instanceof HTMLElement) {
                    note.hidden = !shouldLock;
                }
            });
        }

        function getEventsTodayKey(stack) {
            const value = String(stack?.getAttribute('data-events-today') || '').trim();

            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return value;
            }

            return new Date().toISOString().slice(0, 10);
        }

        function isExpiredEventsDate(value, todayKey) {
            const normalized = String(value || '').trim();

            return /^\d{4}-\d{2}-\d{2}$/.test(normalized) && normalized < todayKey;
        }

        function getEventsCardGroupList(stack, groupKey) {
            return stack?.querySelector(`[data-events-card-group-list="${groupKey}"]`) || null;
        }

        function refreshEventsCardGroups(scope) {
            const stack = scope?.querySelector?.('[data-events-card-stack]') || scope;
            if (!(stack instanceof HTMLElement)) {
                return;
            }

            const hasSelection = Array.from(stack.querySelectorAll('[data-events-card-editor]'))
                .some((editor) => editor.classList.contains('is-selected'));
            const visibleGroup = String(stack.dataset.eventsVisibleGroup || '').trim().toLowerCase();

            ['active', 'expired'].forEach((groupKey) => {
                const group = stack.querySelector(`[data-events-card-group="${groupKey}"]`);
                const list = getEventsCardGroupList(stack, groupKey);
                const editors = Array.from(list?.querySelectorAll('[data-events-card-editor]') || []);
                const visibleEditors = editors.filter((editor) => !editor.hidden);
                const count = stack.querySelector(`[data-events-card-group-count="${groupKey}"]`);
                const empty = stack.querySelector(`[data-events-card-empty="${groupKey}"]`);

                if (count) {
                    count.textContent = String(editors.length);
                }

                if (empty) {
                    empty.hidden = editors.length !== 0;
                }

                if (group) {
                    const hiddenForVisibleGroup = visibleGroup !== '' && visibleGroup !== 'all' && visibleGroup !== groupKey;
                    group.hidden = hiddenForVisibleGroup || (hasSelection ? visibleEditors.length === 0 : false);
                }
            });
        }

        function moveEventsCardEditorToGroup(editor) {
            const stack = editor?.closest('[data-events-card-stack]');
            if (!editor || !stack) {
                return;
            }

            const dateInput = editor.querySelector('input[name*="[event_date]"]');
            const nextGroupKey = isExpiredEventsDate(dateInput?.value, getEventsTodayKey(stack)) ? 'expired' : 'active';
            const targetList = getEventsCardGroupList(stack, nextGroupKey);

            if (!targetList || targetList === editor.parentElement) {
                return;
            }

            targetList.appendChild(editor);
        }

        function bindEventsCardDateInput(editor) {
            const dateInput = editor?.querySelector('input[name*="[event_date]"]');
            if (!dateInput || dateInput.dataset.eventsDateBound === '1') {
                return;
            }

            dateInput.dataset.eventsDateBound = '1';

            const syncGroup = () => {
                moveEventsCardEditorToGroup(editor);
                const stack = editor.closest('[data-events-card-stack]');
                if (editor.classList.contains('is-selected') && stack instanceof HTMLElement) {
                    const editorGroup = editor.closest('[data-events-card-group]')?.getAttribute('data-events-card-group') || '';
                    if (editorGroup !== '') {
                        stack.dataset.eventsVisibleGroup = editorGroup;
                    }
                }
                refreshEventsCardGroups(editor.closest('[data-events-cards-form]'));
            };

            dateInput.addEventListener('change', syncGroup);
            dateInput.addEventListener('input', syncGroup);
        }

        function initEventsImageDropzones(scope = document) {
            scope.querySelectorAll('.events-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.eventsDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-events-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-events-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-events-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-events-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-events-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-events-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-events-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-events-clear-image-for="${input.id}"]`);
                const editButton = scope.querySelector(`[data-events-edit-image-for="${input.id}"]`)
                    || document.querySelector(`[data-events-edit-image-for="${input.id}"]`);
                const imageField = input.closest('[data-events-card-editor]')?.querySelector('[data-events-image-field]') || null;

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.eventsDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.eventsDefaultSrc || '';

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;
                    if (typeof editButton !== 'undefined' && editButton) editButton.hidden = !hasImage;
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
                if (typeof editButton !== 'undefined' && editButton) {
                    editButton.addEventListener('click', async (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        let file = input.files && input.files[0];
                        if (!file && previewEl && previewEl.src && previewEl.src !== defaultSrc) {
                            try {
                                const dbPath = typeof imageField !== 'undefined' && imageField ? imageField.value : null;
                                if (dbPath) {
                                    const res = await fetch(`/cms/proxy-image?path=${encodeURIComponent(dbPath)}`);
                                    if (!res.ok) throw new Error("Proxy fetch failed");
                                    const blob = await res.blob();
                                    const ext = dbPath.split('.').pop().split(/#|\?/)[0] || 'jpg';
                                    file = new File([blob], `image.${ext}`, { type: blob.type });
                                } else {
                                    throw new Error("No db path available");
                                }
                            } catch(err) {
                                console.warn("Proxy failed, using canvas fallback", err);
                                try {
                                    const canvas = document.createElement('canvas');
                                    canvas.width = previewEl.naturalWidth || previewEl.width || 800;
                                    canvas.height = previewEl.naturalHeight || previewEl.height || 600;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(previewEl, 0, 0, canvas.width, canvas.height);
                                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 1.0));
                                    if (blob) file = new File([blob], 'image.jpg', { type: 'image/jpeg' });
                                } catch (canvasErr) {
                                    console.error("Canvas fallback also failed", canvasErr);
                                }
                            }
                        }
                        
                        if (file && window.CmsImageEditor) {
                            const editedFile = await window.CmsImageEditor.editFile(file, {
                                input,
                                previewElement: previewEl,
                            });
                            
                            if (editedFile && editedFile !== file) {
                                window.CmsImageEditor.setInputFile(input, editedFile);
                                if (typeof applyFile === 'function') {
                                    applyFile(editedFile);
                                }
                            }
                        }
                    });
                }


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
                    if (event.target.closest('[data-events-clear-image-for]')) {
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
                refreshEventsCardGroups(stack);
                return true;
            }

            if (wasSelected && options.keepFocus !== false) {
                const fallbackEditor = remainingEditors[0];
                const fallbackIndex = fallbackEditor.getAttribute('data-events-card-index');
                setActiveEventsCardEditor(fallbackIndex);

                const firstField = fallbackEditor.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                firstField?.focus();
            }

            refreshEventsCardGroups(stack);

            return true;
        }

        function openEventsEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-events-editor-modal]');
            if (!modal) {
                return;
            }

            const title = modal.querySelector('#""-modal-title');
            const description = modal.querySelector('[data-events-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            modal.querySelectorAll('[data-events-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-events-editor-panel') === sectionKey;
                const isCardFocus = sectionKey === 'cards';
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    if (title) {
                        title.textContent = label || 'Edit events section';
                    }

                    if (description) {
                        description.textContent = sectionKey === 'cards'
                            ? 'Add, edit, or delete event cards individually and save to refresh the events page preview.'
                            : 'Update this section and save to refresh the events page preview.';
                    }

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const activeCardEditor = sectionKey === 'cards'
                        ? setActiveEventsCardEditor(options.cardIndex ?? null)
                        : null;
                    if (sectionKey === 'cards') {
                        const stack = panel.querySelector('[data-events-card-stack]');
                        if (stack instanceof HTMLElement && (!stack.dataset.eventsVisibleGroup || stack.dataset.eventsVisibleGroup === 'all')) {
                            stack.dataset.eventsVisibleGroup = activeCardEditor
                                ? (activeCardEditor.closest('[data-events-card-group]')?.getAttribute('data-events-card-group') || 'active')
                                : 'active';
                        }
                    }
                    const focusScope = activeCardEditor || panel;
                    if (sectionKey === 'cards') {
                        refreshEventsCardGroups(panel);
                    }
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

            const activeList = getEventsCardGroupList(stack, 'active');
            if (!activeList) {
                return null;
            }

            activeList.insertAdjacentHTML('beforeend', html);

            const newCard = activeList.lastElementChild;
            if (newCard && typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(newCard);
            }

            bindEventsCardDateInput(newCard);
            initEventsImageDropzones(newCard);
            moveEventsCardEditorToGroup(newCard);
            syncEventsFeaturedToggles(form);

            markEventsCardsChanged(form);

            setActiveEventsCardEditor(String(nextIndex));
            refreshEventsCardGroups(form);

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
            modal.classList.remove('is-card-focus');
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
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

            markEventsCardsChanged(form);

            const frame = document.querySelector('[data-events-preview-frame]');
            frame?.contentWindow?.postMessage({
                type: 'cms-events-prune-card',
                cardIndex: Number(cardIndex),
            }, '*');

            return true;
        }

        function deleteEventsCardsByIndexes(cardIndexes, options = {}) {
            const normalizedIndexes = Array.from(new Set(
                (Array.isArray(cardIndexes) ? cardIndexes : [])
                    .map((value) => Number(value))
                    .filter((value) => Number.isFinite(value))
            ));

            if (!normalizedIndexes.length) {
                return 0;
            }

            let deletedCount = 0;

            normalizedIndexes.forEach((cardIndex) => {
                if (deleteEventsCardByIndex(cardIndex, {
                    keepFocus: false,
                    ...options,
                })) {
                    deletedCount += 1;
                }
            });

            return deletedCount;
        }

        function submitEventsCardsForm(form) {
            if (!form) {
                return;
            }

            form.dataset.eventsSkipValidation = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));

            delete form.dataset.eventsSkipValidation;
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

            submitEventsCardsForm(form);
        }

        async function confirmExpiredEventsDelete(cardIndexes) {
            const form = document.querySelector('[data-events-cards-form]');
            if (!form) {
                return;
            }

            const normalizedIndexes = Array.from(new Set(
                (Array.isArray(cardIndexes) ? cardIndexes : [])
                    .map((value) => Number(value))
                    .filter((value) => Number.isFinite(value))
            ));

            if (!normalizedIndexes.length) {
                return;
            }

            const titles = normalizedIndexes
                .map((cardIndex) => {
                    const editor = form.querySelector(`[data-events-card-editor][data-events-card-index="${cardIndex}"]`);
                    const titleInput = editor?.querySelector('input[name*="[title]"]');

                    return String(titleInput?.value || '').trim();
                })
                .filter((value) => value !== '');

            const totalCount = normalizedIndexes.length;
            const message = totalCount === 1
                ? (titles[0]
                    ? `Do you want to remove "${titles[0]}" from expired events?`
                    : 'Do you want to remove this expired event?')
                : `Do you want to remove ${totalCount} selected expired events?`;

            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: totalCount === 1 ? 'Remove Expired Event' : 'Remove Selected Expired Events',
                    message,
                    confirmText: 'Remove',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(message);
            }

            if (!confirmed) {
                return;
            }

            const deletedCount = deleteEventsCardsByIndexes(normalizedIndexes, {
                keepFocus: false,
            });

            if (deletedCount === 0) {
                return;
            }

            submitEventsCardsForm(form);
        }

        window.openEventsCmsSection = openEventsEditor;

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-events-edit') {
                if ((data.section || '') === 'cards') {
                    return;
                }

                openEventsEditor(data.section || 'page', data.label || 'Edit events section');
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

            if (data.type === 'cms-events-delete-expired-cards') {
                confirmExpiredEventsDelete(data.cardIndexes);
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

        function showEventsValidationToast(message) {
            if (typeof window.showToast === 'function') {
                window.showToast(message, 'warning', 'Missing Details');
                return;
            }

            window.alert(message);
        }

        function getEventCardField(editor, fieldName) {
            return editor?.querySelector(`[name*="[${fieldName}]"]`) || null;
        }

        function getEventCardFieldValue(editor, fieldName) {
            const field = getEventCardField(editor, fieldName);
            return String(field?.value || '').trim();
        }

        function validateEventsCardEditor(editor) {
            if (!(editor instanceof HTMLElement)) {
                return true;
            }

            const requiredFields = [
                ['title', 'Event Title'],
                ['category', 'Category'],
                ['event_date', 'Event Date'],
                ['location', 'Location'],
                ['start_time', 'Start Time'],
                ['end_time', 'End Time'],
                ['content', 'Event Details'],
            ];
            const missing = requiredFields.filter(([fieldName]) => getEventCardFieldValue(editor, fieldName) === '');

            if (missing.length === 0) {
                return true;
            }

            const firstMissingField = getEventCardField(editor, missing[0][0]);
            showEventsValidationToast('All fields are required. Please complete: ' + missing.map(([, label]) => label).join(', ') + '.');

            if (firstMissingField) {
                firstMissingField.focus();
            } else {
                editor.querySelector('.rich-editor-surface')?.focus();
            }

            return false;
        }

        function validateEventsCardsForm(form) {
            if (!form?.matches?.('[data-events-cards-form]')) {
                return true;
            }

            if (form.dataset.eventsSkipValidation === '1') {
                delete form.dataset.eventsSkipValidation;
                return true;
            }

            syncEditorsInScope(form);

            const editors = Array.from(form.querySelectorAll('[data-events-card-editor]'));
            const targetEditors = editors.filter((editor) => editor.classList.contains('is-selected'));
            const editorsToValidate = targetEditors.length > 0
                ? targetEditors
                : editors.filter((editor) => editor.hasAttribute('data-events-new-card') && !editor.hidden);

            if (editorsToValidate.length === 0) {
                return true;
            }

            return editorsToValidate.every((editor) => validateEventsCardEditor(editor));
        }

        document.querySelectorAll('.""').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!validateEventsCardsForm(form)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    event.stopPropagation();
                    return;
                }

                syncEditorsInScope(form);
            });
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
        document.querySelectorAll('[data-events-card-editor]').forEach((editor) => {
            bindEventsCardDateInput(editor);
            moveEventsCardEditorToGroup(editor);
        });
        const eventsCardsForm = document.querySelector('[data-events-cards-form]');
        initEventsImageDropzones(eventsCardsForm || document);
        bindEventsCardsDirtyTracking(eventsCardsForm);
        eventsCardsForm?.addEventListener('change', (event) => {
            if (event.target instanceof HTMLInputElement && event.target.classList.contains('events-cms-feature-toggle')) {
                syncEventsFeaturedToggles(eventsCardsForm);
            }
        });
        refreshEventsCardGroups(eventsCardsForm);
        syncEventsFeaturedToggles(eventsCardsForm);
        scheduleFitAllEventsPreviews();
        window.__eventsCmsPreviewEditorReady = true;
    })();
