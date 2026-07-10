
    (() => {
        if (window.__researchCmsPreviewEditorReady) {
            return;
        }

        const RESEARCH_PREVIEW_MIN_LOADING_MS = 800;
        const previewScript = document.querySelector('[data-research-preview-pages-json]');
        const previewPages = previewScript ? JSON.parse(previewScript.textContent || '{}') : {};
        let currentPreviewPage = 'overview';
        const modal = document.querySelector('[data-research-editor-modal]');
        const modalTitle = modal?.querySelector('#""-modal-title');
        const modalDescription = modal?.querySelector('[data-research-editor-description]');
        const panels = Array.from(document.querySelectorAll('[data-research-editor-panel]'));
        const frames = Array.from(document.querySelectorAll('[data-research-preview-frame]'));
        const previewNavBtns = Array.from(document.querySelectorAll('[data-research-preview-page]'));

        if (!modal || !frames.length) {
            return;
        }

        window.__researchPreviewCache = window.__researchPreviewCache || {};

        const loadFrame = (frame) => {
            const page = currentPreviewPage;
            setResearchPreviewLoading(frame, true);
            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, html);
                } else {
                    frame.srcdoc = html;
                }
            };

            if (window.__researchPreviewCache[page]) {
                applyHtml(window.__researchPreviewCache[page]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/research_extension/${page}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__researchPreviewCache[page] = previewHtml;
                    if (currentPreviewPage === page) {
                        applyHtml(previewHtml);
                    }
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
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
                const editButton = scope.querySelector(`[data-research-edit-image-for="${input.id}"]`)
                    || document.querySelector(`[data-research-edit-image-for="${input.id}"]`);
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

            const dropzoneId = `""-research-card-image-${index}`;
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

        document.querySelectorAll('.""').forEach((form) => {
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
