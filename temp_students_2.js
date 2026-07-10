
    (() => {
        if (window.__studentsCmsPreviewEditorReady) {
            return;
        }

        const STUDENTS_PREVIEW_MIN_LOADING_MS = 800;
        const STUDENTS_PREVIEW_STORAGE_KEY = `cms:students-preview-route:${window.location.pathname}`;
        const STUDENTS_PREVIEW_LEGACY_STORAGE_KEY = '""-active-students-preview-page';
        let currentStudentsPreviewRoute = 'overview';
        const modal = document.querySelector('[data-students-editor-modal]');
        const modalTitle = modal?.querySelector('#""-modal-title');
        const modalDescription = modal?.querySelector('[data-students-editor-description]');
        const panels = Array.from(document.querySelectorAll('[data-students-editor-panel]'));
        const frames = Array.from(document.querySelectorAll('[data-students-preview-frame]'));

        if (!modal || !frames.length) {
            return;
        }

        const getStudentsPreviewPayloads = () => {
            const previewScript = document.querySelector('[data-students-preview-pages]');
            if (!previewScript) {
                return {};
            }

            try {
                return JSON.parse(previewScript.textContent || '{}');
            } catch (_) {
                return {};
            }
        };

        const getStoredStudentsPreviewRoute = () => {
            try {
                return window.localStorage.getItem(STUDENTS_PREVIEW_STORAGE_KEY)
                    || window.localStorage.getItem(STUDENTS_PREVIEW_LEGACY_STORAGE_KEY)
                    || '';
            } catch (_) {
                return '';
            }
        };

        const storeStudentsPreviewRoute = (routeKey) => {
            try {
                const storedRoute = String(routeKey || 'overview');
                window.localStorage.setItem(STUDENTS_PREVIEW_STORAGE_KEY, storedRoute);
                window.localStorage.setItem(STUDENTS_PREVIEW_LEGACY_STORAGE_KEY, storedRoute);
            } catch (_) {
                // Ignore storage failures and keep the route in memory for this session.
            }
        };

        const syncStudentsPreviewNav = (routeKey) => {
            document.querySelectorAll('[data-students-preview-page]').forEach((button) => {
                const isActive = (button.getAttribute('data-students-preview-page') || '') === routeKey;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        const syncEditorsInScope = (scope) => {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
        };

        const closeEditor = () => {
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
            const isCardFocus = (
                sectionKey === 'cards'
                && options.cardIndex !== null
                && options.cardIndex !== undefined
                && options.cardIndex !== ''
            ) || (
                sectionKey === 'organizations'
                && String(options.orgKey || '').trim() !== ''
            );

            panels.forEach((panel) => {
                const isActive = panel.getAttribute('data-students-editor-panel') === sectionKey;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);
            });

            modal.classList.toggle('is-card-focus', isCardFocus);

            if (modalTitle) {
                modalTitle.textContent = label || 'Edit students section';
            }

            if (modalDescription) {
                const sectionDescriptions = {
                    cards: 'Manage the public cards shown in the student contents strip.',
                    cards_header: 'Update the heading, title, and supporting copy above the student cards.',
                    admissions_hero: 'Update the admissions subpage header and intro copy.',
                    admissions_instructions: 'Update the admissions instructions section.',
                    admissions_contact_offices: 'Manage the admissions contact offices.',
                    admissions_contact_persons: 'Manage the admissions contact person profiles.',
                    admissions_links: 'Update the heading and title above the admissions links.',
                    admissions_form_links: 'Manage the application and form links.',
                    document_requests_hero: 'Update the document requests subpage header and intro copy.',
                    document_requests_qr_codes_header: 'Update the heading and title above the document requests QR codes.',
                    document_requests_qr_codes_items: 'Manage the document requests QR codes section.',
                    downloadable_forms_hero: 'Update the downloadables subpage header and intro copy.',
                    downloadable_forms_links: 'Manage the downloadables links section.',
                    downloadable_forms_items: 'Manage the downloadable form links.',
                };
                modalDescription.textContent = sectionDescriptions[sectionKey] || 'Update the selected student section.';
            }

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            const activePanel = panels.find((panel) => panel.getAttribute('data-students-editor-panel') === sectionKey) || null;
            if (activePanel && typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(activePanel);
            }

            if (sectionKey === 'cards') {
                setActiveCardEditor(options.cardIndex ?? null);
                window.setTimeout(() => focusCardEditor(options.cardIndex ?? null), 40);
            } else if (sectionKey === 'organizations') {
                setActiveOrganizationEditor(options.orgKey ?? '');
                window.setTimeout(() => focusOrganizationEditor(options.orgKey ?? ''), 40);
            } else if (activePanel) {
                const firstField = activePanel.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                window.setTimeout(() => firstField?.focus(), 40);
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

        const fitAllStudentsPreviews = () => {
            frames.forEach((frame) => {
                scheduleStudentsPreviewSync(frame);
            });
        };

        const scheduleFitAllStudentsPreviews = () => {
            fitAllStudentsPreviews();
            window.setTimeout(fitAllStudentsPreviews, 140);
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

        const getStudentsPreviewElementBottom = (element) => {
            return element.offsetTop + element.offsetHeight;
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
                    .filter((element) => isMeasuredElement(element));

                const contentBottom = visibleElements.reduce((maxBottom, element) => {
                    return Math.max(maxBottom, getStudentsPreviewElementBottom(element));
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

            if (doc.documentElement) {
                doc.documentElement.style.overflow = 'hidden';
            }

            if (doc.body) {
                doc.body.style.overflow = 'hidden';
            }

            if (typeof window.bindCmsPreviewScrollBridge === 'function') {
                window.bindCmsPreviewScrollBridge(frame);
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

                if (event.target.closest('[data-students-card-index], [data-students-org-index]')) {
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

        window.__studentsPreviewCache = window.__studentsPreviewCache || {};

        const loadFrame = (frame, options = {}) => {
            const targetKey = options.routeKey || currentStudentsPreviewRoute || 'overview';
            const shouldForceReload = options.forceReload === true;

            if (!frame) {
                return;
            }

            if (!shouldForceReload && currentStudentsPreviewRoute === targetKey && (typeof window.hasCmsPreviewFrameContent === 'function' ? window.hasCmsPreviewFrameContent(frame) : !!frame.srcdoc)) {
                storeStudentsPreviewRoute(targetKey);
                syncStudentsPreviewNav(targetKey);
                setStudentsPreviewLoading(frame, true);
                queueStudentsPreviewSettledSync(frame);
                return;
            }

            currentStudentsPreviewRoute = targetKey;
            storeStudentsPreviewRoute(targetKey);
            syncStudentsPreviewNav(targetKey);
            setStudentsPreviewLoading(frame, true);

            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, typeof html === 'string' ? html : '');
                } else {
                    frame.srcdoc = typeof html === 'string' ? html : '';
                }
            };

            if (!shouldForceReload && window.__studentsPreviewCache[targetKey]) {
                applyHtml(window.__studentsPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/students/${targetKey}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__studentsPreviewCache[targetKey] = previewHtml;
                    if (currentStudentsPreviewRoute === targetKey) {
                        applyHtml(previewHtml);
                    }
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
        };

        frames.forEach((frame) => {
            frame.addEventListener('load', () => {
                bindFrame(frame);
                queueStudentsPreviewSettledSync(frame);
            });

            loadFrame(frame, {
                routeKey: getStoredStudentsPreviewRoute() || 'overview',
            });
        });

        document.querySelectorAll('[data-students-preview-page]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const routeKey = button.getAttribute('data-students-preview-page') || 'overview';
                const frame = document.querySelector('[data-students-preview-frame]');
                if (!frame) {
                    return;
                }

                loadFrame(frame, { routeKey });
            });
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

        const shouldTrackStudentsCardField = (target) => {
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

        const bindStudentsCardsDirtyTracking = () => {
            if (!cardsForm || cardsForm.dataset.studentsDirtyTrackingBound === '1') {
                return;
            }

            cardsForm.dataset.studentsDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackStudentsCardField(event.target)) {
                    return;
                }

                bumpCardsVersion();
                relabelCards();
            };

            cardsForm.addEventListener('input', markDirty);
            cardsForm.addEventListener('change', markDirty);
        };

        const relabelCards = () => {
            const editors = Array.from(cardStack?.querySelectorAll('[data-students-card-editor]') ?? []);

            editors.forEach((editor, index) => {
                const displayNumber = index + 1;
                const headTitle = editor.querySelector('[data-students-card-editor-head] h4');
                const headSubtitle = editor.querySelector('[data-students-card-editor-head] span');
                const titleInput = editor.querySelector('input[name*="[title]"]');
                const dropzoneTitle = editor.querySelector('.students-cms-image-dropzone-label');

                if (headTitle) {
                    headTitle.textContent = `Service Card ${displayNumber}`;
                }

                if (headSubtitle) {
                    headSubtitle.textContent = String(titleInput?.value || '').trim();
                }

                if (dropzoneTitle) {
                    dropzoneTitle.textContent = `Card ${displayNumber}`;
                }
            });
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

        const initStudentsImageDropzones = (scope = document) => {
            scope.querySelectorAll('.students-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.studentsDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-students-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-students-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-students-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-students-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-students-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-students-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-students-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-students-clear-image-for="${input.id}"]`);
                const editButton = scope.querySelector(`[data-students-edit-image-for="${input.id}"]`)
                    || document.querySelector(`[data-students-edit-image-for="${input.id}"]`);
                const imageField = input.dataset.studentsImageFieldId
                    ? document.getElementById(input.dataset.studentsImageFieldId)
                    : (input.closest('[data-students-card-editor]')?.querySelector('[data-students-image-field]') || null);

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.studentsDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.studentsDefaultSrc || '';

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;
                    if (typeof editButton !== 'undefined' && editButton) editButton.hidden = !hasImage;

                    if (input.dataset.studentsRequireFileOnEmpty === '1') {
                        input.required = !hasImage;
                    }
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
                    if (event.target.closest('[data-students-clear-image-for]')) {
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

        const initStudentsCharCounters = (scope = document) => {
            scope.querySelectorAll('[data-students-char-limit]').forEach((field) => {
                if (field.dataset.studentsCharCounterBound === '1') {
                    return;
                }

                const input = field.querySelector('[data-students-char-input]');
                const counter = field.querySelector('[data-students-char-counter]');
                const limit = Number(field.getAttribute('data-students-char-limit') || input?.getAttribute('maxlength') || 0);

                if (!input || !counter || limit <= 0) {
                    return;
                }

                field.dataset.studentsCharCounterBound = '1';
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

            const dropzoneId = `""-students-card-image-${index}`;
            const dropzoneInput = fragment.querySelector('.students-cms-image-dropzone-input');
            const dropzoneLabel = fragment.querySelector('.students-cms-image-dropzone');
            const dropzonePreview = fragment.querySelector('[data-students-preview-for]');
            const dropzoneFileName = fragment.querySelector('[data-students-file-name-for]');
            const dropzoneTitle = fragment.querySelector('.students-cms-image-dropzone-label');
            const dropzoneRemove = fragment.querySelector('[data-students-clear-image-for]');

            if (dropzoneInput) {
                dropzoneInput.id = dropzoneId;
            }

            if (dropzoneLabel) {
                dropzoneLabel.setAttribute('data-students-dropzone-for', dropzoneId);
            }

            if (dropzonePreview) {
                dropzonePreview.setAttribute('data-students-preview-for', dropzoneId);
            }

            if (dropzoneFileName) {
                dropzoneFileName.setAttribute('data-students-file-name-for', dropzoneId);
            }

            if (dropzoneRemove) {
                dropzoneRemove.setAttribute('data-students-clear-image-for', dropzoneId);
            }

            if (dropzoneTitle) {
                dropzoneTitle.textContent = `Card ${index + 1}`;
            }

            cardStack.appendChild(fragment);
            initStudentsImageDropzones(cardStack);
            initStudentsCharCounters(cardStack);
            bumpCardsVersion();
            relabelCards();
            setActiveCardEditor(index);
            focusCardEditor(index);
        };

        const nextRepeatableIndex = (list) => {
            const indexes = Array.from(list?.querySelectorAll('[name]') ?? [])
                .map((field) => {
                    const match = String(field.name || '').match(/\[items\]\[(\d+)\]/);
                    return match ? Number(match[1]) : -1;
                })
                .filter((value) => Number.isFinite(value) && value >= 0);

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const repeatableTemplates = {
            'admissions-links': (index) => `
                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="students[pages][admissions][links][items][${index}][label]" maxlength="255" value="" required>
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <div class="students-link-row">
                                <input type="text" class="form-control" name="students[pages][admissions][links][items][${index}][href]" maxlength="2048" value="" required>
                                <button type="button" class="students-link-paste" onclick="navigator.clipboard.readText().then(t => this.previousElementSibling.value = t).catch(e => alert('Please allow clipboard access to paste.'))" title="Paste URL">
                                    <i class="fas fa-paste"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="students[pages][admissions][links][items][${index}][description]" rows="2" required></textarea>
                    </div>
                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Link</button>
                </div>
            `,
            'admissions-contact-offices': (index) => `
                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Office Name</label>
                            <input type="text" name="students[pages][admissions][contact][offices][${index}][label]" maxlength="255" value="">
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="students[pages][admissions][contact][offices][${index}][value]" maxlength="255" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone Link</label>
                        <input type="text" name="students[pages][admissions][contact][offices][${index}][href]" maxlength="255" value="">
                    </div>
                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Office</button>
                </div>
            `,
            'admissions-contact-persons': (index) => {
                const inputId = `""-students-admissions-contact-person-${index}`;
                const fieldId = `""-students-admissions-contact-person-field-${index}`;

                return `
                    <div class="students-cms-repeatable-item" data-students-repeatable-item>
                        <input type="hidden" id="${fieldId}" name="students[pages][admissions][contact][persons][${index}][image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload Profile Photo</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${inputId}" role="button" tabindex="0" aria-label="Upload contact person profile photo">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Contact person photo preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${inputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-edit" data-students-edit-image-for="${inputId}" aria-label="Edit image" title="Edit image">
                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                            </button>
                                                    <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${inputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">Profile Photo</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload the contact person's profile photo.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${inputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${inputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][contact][persons][${index}][image_file]" accept="image/*" data-students-image-field-id="${fieldId}">
                        </div>
                        <div class="students-cms-form-grid">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="students[pages][admissions][contact][persons][${index}][name]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" name="students[pages][admissions][contact][persons][${index}][role]" maxlength="255" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="students[pages][admissions][contact][persons][${index}][email]" maxlength="255" value="">
                        </div>
                        <div class="form-group">
                            <label>Email Link</label>
                            <input type="text" name="students[pages][admissions][contact][persons][${index}][href]" maxlength="255" value="">
                        </div>
                        <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Person</button>
                    </div>
                `;
            },
            'forms-links': (index) => `
                <div class="students-cms-repeatable-item" data-students-repeatable-item>
                    <div class="students-cms-form-grid">
                        <div class="form-group">
                            <label>Form Name</label>
                            <input type="text" name="students[pages][downloadable-forms][links][items][${index}][label]" maxlength="255" value="" required>
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <input type="text" name="students[pages][downloadable-forms][links][items][${index}][href]" maxlength="2048" value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="students[pages][downloadable-forms][links][items][${index}][description]" rows="2" required></textarea>
                    </div>
                    <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove Form Link</button>
                </div>
            `,
            'admissions-qr': (index) => {
                const inputId = `""-students-admissions-qr-${index}`;
                const fieldId = `""-students-admissions-qr-field-${index}`;
                const flyerInputId = `""-students-admissions-qr-flyer-${index}`;
                const flyerFieldId = `""-students-admissions-qr-flyer-field-${index}`;

                return `
                    <div class="students-cms-repeatable-item" data-students-repeatable-item>
                        <input type="hidden" id="${fieldId}" name="students[pages][admissions][qr_codes][items][${index}][image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload QR Code Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${inputId}" role="button" tabindex="0" aria-label="Upload QR code image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="QR code preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${inputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-edit" data-students-edit-image-for="${inputId}" aria-label="Edit image" title="Edit image">
                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                            </button>
                                                    <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${inputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">QR Code</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload a QR code image for applicants.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${inputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${inputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][qr_codes][items][${index}][image_file]" accept="image/*" data-students-image-field-id="${fieldId}" data-students-require-file-on-empty="1" required>
                        </div>
                        <input type="hidden" id="${flyerFieldId}" name="students[pages][admissions][qr_codes][items][${index}][flyer_image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload Step by Step Process Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${flyerInputId}" role="button" tabindex="0" aria-label="Upload flyer or step by step image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Flyer preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${flyerInputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-edit" data-students-edit-image-for="${flyerInputId}" aria-label="Edit image" title="Edit image">
                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                            </button>
                                                    <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${flyerInputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload the companion step by step process image.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${flyerInputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${flyerInputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][admissions][qr_codes][items][${index}][flyer_image_file]" accept="image/*" data-students-image-field-id="${flyerFieldId}">
                        </div>
                        <div class="students-cms-form-grid">
                            <div class="form-group">
                                <label>Label</label>
                                <input type="text" name="students[pages][admissions][qr_codes][items][${index}][label]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="students[pages][admissions][qr_codes][items][${index}][description]" maxlength="50" value="">
                            </div>
                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="students[pages][admissions][qr_codes][items][${index}][href]" maxlength="2048" value="">
                            </div>
                        </div>
                        <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove QR Code</button>
                    </div>
                `;
            },
            'document-requests-qr': (index) => {
                const inputId = `""-students-document-requests-qr-${index}`;
                const fieldId = `""-students-document-requests-qr-field-${index}`;
                const flyerInputId = `""-students-document-requests-qr-flyer-${index}`;
                const flyerFieldId = `""-students-document-requests-qr-flyer-field-${index}`;

                return `
                    <div class="students-cms-repeatable-item" data-students-repeatable-item>
                        <input type="hidden" id="${fieldId}" name="students[pages][document-requests][qr_codes][items][${index}][image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload QR Code Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${inputId}" role="button" tabindex="0" aria-label="Upload QR code image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="QR code preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${inputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-edit" data-students-edit-image-for="${inputId}" aria-label="Edit image" title="Edit image">
                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                            </button>
                                                    <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${inputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">QR Code</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload a QR code image for document requests.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${inputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${inputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][qr_codes][items][${index}][image_file]" accept="image/*" data-students-image-field-id="${fieldId}" data-students-require-file-on-empty="1" required>
                        </div>
                        <input type="hidden" id="${flyerFieldId}" name="students[pages][document-requests][qr_codes][items][${index}][flyer_image]" value="" data-students-image-field>
                        <div class="form-group">
                            <label>Upload Step by Step Process Image</label>
                            <div class="students-cms-image-dropzone-shell">
                                <div class="students-cms-image-dropzone" data-students-dropzone-for="${flyerInputId}" role="button" tabindex="0" aria-label="Upload flyer or step by step image">
                                    <span class="students-cms-image-dropzone-preview-column">
                                        <span class="students-cms-image-dropzone-media">
                                            <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Flyer preview" class="students-cms-image-dropzone-preview" data-students-preview-for="${flyerInputId}" data-students-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
                                            <button type="button" class="students-cms-image-dropzone-edit" data-students-edit-image-for="${flyerInputId}" aria-label="Edit image" title="Edit image">
                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                            </button>
                                                    <button type="button" class="students-cms-image-dropzone-remove" data-students-clear-image-for="${flyerInputId}" aria-label="Delete image" title="Delete image">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <span class="students-cms-image-dropzone-label">Step by Step process</span>
                                    </span>
                                    <span class="students-cms-image-dropzone-upload">
                                        <span class="students-cms-image-dropzone-icon"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                        <span class="students-cms-image-dropzone-upload-title">Drag and drop image files to upload</span>
                                        <span class="students-cms-image-dropzone-upload-copy">Upload the companion step by step process image.</span>
                                        <span class="students-cms-image-dropzone-upload-button">Select image</span>
                                        <span class="students-cms-image-dropzone-file" data-students-file-name-for="${flyerInputId}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                    </span>
                                </div>
                            </div>
                            <input id="${flyerInputId}" class="students-cms-image-dropzone-input" type="file" name="students[pages][document-requests][qr_codes][items][${index}][flyer_image_file]" accept="image/*" data-students-image-field-id="${flyerFieldId}">
                        </div>
                        <div class="students-cms-form-grid">
                            <div class="form-group">
                                <label>Label</label>
                                <input type="text" name="students[pages][document-requests][qr_codes][items][${index}][label]" maxlength="255" value="">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="students[pages][document-requests][qr_codes][items][${index}][description]" maxlength="50" value="">
                            </div>
                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" name="students[pages][document-requests][qr_codes][items][${index}][href]" maxlength="2048" value="">
                            </div>
                        </div>
                        <button type="button" class="btn students-cms-delete-card" data-students-remove-repeatable>Remove QR Code</button>
                    </div>
                `;
            },
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

                const cardIndex = editor.getAttribute('data-students-card-index');
                void confirmDeleteCard(cardIndex === null ? null : Number(cardIndex));
            }

            const addRepeatableTrigger = event.target.closest('[data-students-add-repeatable]');
            if (addRepeatableTrigger) {
                event.preventDefault();
                const key = addRepeatableTrigger.getAttribute('data-students-add-repeatable') || '';
                const list = modal.querySelector(`[data-students-repeatable-list="${key}"]`);
                const template = repeatableTemplates[key];

                if (!list || typeof template !== 'function') {
                    return;
                }

                const index = nextRepeatableIndex(list);
                list.insertAdjacentHTML('beforeend', template(index));
                initStudentsImageDropzones(list);
                const latest = list.lastElementChild;
                latest?.querySelector('input:not([type="hidden"]), textarea')?.focus();
                return;
            }

            const removeRepeatableTrigger = event.target.closest('[data-students-remove-repeatable]');
            if (removeRepeatableTrigger) {
                event.preventDefault();
                removeRepeatableTrigger.closest('[data-students-repeatable-item]')?.remove();
            }
        });

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || data.type !== 'cms-students-preview-height') {
                return;
            }

            const targetFrame = Array.from(document.querySelectorAll('[data-students-preview-frame]'))
                .find((frame) => frame.contentWindow === event.source);

            if (!targetFrame) {
                return;
            }

            syncStudentsPreviewHeight(targetFrame, data.height);
        });

        window.addEventListener('resize', () => {
            scheduleFitAllStudentsPreviews();
        });

        window.addEventListener('cms:tab-activated', (event) => {
            const panel = event.detail?.panel;

            frames.forEach((frame) => {
                if (panel && panel.contains(frame)) {
                    loadFrame(frame, {
                        routeKey: currentStudentsPreviewRoute,
                        forceReload: true,
                    });
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 40);
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 180);
                    window.setTimeout(() => scheduleStudentsPreviewSync(frame), 320);
                }
            });

            scheduleFitAllStudentsPreviews();
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAllStudentsPreviews();
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
                scheduleFitAllStudentsPreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('pageshow', () => {
            scheduleFitAllStudentsPreviews();
        });

        window.addEventListener('load', () => {
            scheduleFitAllStudentsPreviews();
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAllStudentsPreviews();
            }
        });

        window.refreshStudentsCmsPreview = (scope) => {
            const scopedFrames = scope
                ? Array.from(scope.querySelectorAll('[data-students-preview-frame]'))
                : frames;

            scopedFrames.forEach((frame) => loadFrame(frame, {
                routeKey: currentStudentsPreviewRoute,
                forceReload: true,
            }));
        };

        relabelCards();
        setActiveCardEditor();
        setActiveOrganizationEditor('');
        syncEditorsInScope(modal);
        initStudentsImageDropzones(modal);
        initStudentsCharCounters(modal);
        bindStudentsCardsDirtyTracking();
        syncStudentsPreviewNav(currentStudentsPreviewRoute);
        scheduleFitAllStudentsPreviews();
        window.__studentsCmsPreviewEditorReady = true;
    })();
