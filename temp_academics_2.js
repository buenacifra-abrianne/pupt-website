
    (() => {
        if (window.__academicsCmsPreviewEditorReady) {
            if (typeof window.__rebindAcademicsCmsPreviewEditor === 'function') {
                window.__rebindAcademicsCmsPreviewEditor();
            }
            return;
        }

        const ACADEMICS_PREVIEW_MIN_LOADING_MS = 800;
        let academicsPreviewFitFrame = null;
        const ACADEMICS_PREVIEW_STORAGE_KEY = `cms:academics-preview-route:${window.location.pathname}`;
        const ACADEMICS_PREVIEW_LEGACY_STORAGE_KEY = '""-active-academics-preview-page';
        let currentAcademicsPreviewRoute = 'overview';

        function getStoredAcademicsPreviewRoute() {
            try {
                return window.localStorage.getItem(ACADEMICS_PREVIEW_STORAGE_KEY)
                    || window.localStorage.getItem(ACADEMICS_PREVIEW_LEGACY_STORAGE_KEY)
                    || '';
            } catch (_) {
                return '';
            }
        }

        function storeAcademicsPreviewRoute(routeKey) {
            try {
                const storedRoute = String(routeKey || 'overview');
                window.localStorage.setItem(ACADEMICS_PREVIEW_STORAGE_KEY, storedRoute);
                window.localStorage.setItem(ACADEMICS_PREVIEW_LEGACY_STORAGE_KEY, storedRoute);
            } catch (_) {
                // Ignore storage failures and keep the route in memory for this session.
            }
        }

        function getAcademicsPreviewPayloads() {
            const el = document.querySelector('[data-academics-preview-pages]');
            if (!el) {
                return {};
            }

            try {
                return JSON.parse(el.textContent || '{}');
            } catch (_) {
                return {};
            }
        }

        function syncAcademicsPreviewNav(routeKey) {
            document.querySelectorAll('[data-academics-preview-page]').forEach((button) => {
                const isActive = (button.getAttribute('data-academics-preview-page') || '') === routeKey;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

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
            const doc = frame.contentDocument;

            if (!doc) {
                return 0;
            }

            const main = doc.querySelector('.main-content');
            const scope = main instanceof HTMLElement ? main : doc.body;

            if (!(scope instanceof HTMLElement)) {
                return 0;
            }

            if (currentAcademicsPreviewRoute === 'university-calendar') {
                const calendarCard = doc.querySelector('.uc-calendar-official-card');
                const calendarSection = calendarCard?.closest('.contents-strip');
                const heroSection = doc.querySelector('.uc-hero-b');
                const breadcrumbShell = doc.querySelector('.academic-shell');
                const candidates = [breadcrumbShell, heroSection, calendarSection, calendarCard]
                    .filter((element) => element instanceof HTMLElement && isAcademicsPreviewMeasuredElement(element));
                const routeBottom = candidates.reduce((maxBottom, element) => {
                    return Math.max(maxBottom, getAcademicsPreviewElementBottom(element));
                }, 0);

                if (routeBottom > 0) {
                    return Math.max(1, Math.ceil(routeBottom));
                }
            }

            const visibleElements = Array.from(scope.children)
                .filter((element) => isAcademicsPreviewMeasuredElement(element));

            const contentBottom = visibleElements.reduce((maxBottom, element) => {
                return Math.max(maxBottom, getAcademicsPreviewElementBottom(element));
            }, 0);

            const fallbackHeight = Math.max(scope.scrollHeight || 0, scope.offsetHeight || 0);

            return Math.max(1, Math.ceil(contentBottom || fallbackHeight));
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

        window.__academicsPreviewCache = window.__academicsPreviewCache || {};

        function loadAcademicsPreview(frame, options = {}) {
            const targetKey = options.routeKey || currentAcademicsPreviewRoute || 'overview';
            const shouldForceReload = options.forceReload === true;
            const explicitSessionId = options.sessionId;

            if (!frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__academicsPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            if (!shouldForceReload && currentAcademicsPreviewRoute === targetKey && (typeof window.hasCmsPreviewFrameContent === 'function' ? window.hasCmsPreviewFrameContent(frame) : !!frame.srcdoc)) {
                storeAcademicsPreviewRoute(targetKey);
                setAcademicsPreviewLoading(frame, true);
                queueAcademicsPreviewSettledSync(frame);
                return;
            }

            currentAcademicsPreviewRoute = targetKey;
            storeAcademicsPreviewRoute(targetKey);
            syncAcademicsPreviewNav(targetKey);
            setAcademicsPreviewLoading(frame, true);

            const applyHtml = (html) => {
                try {
                    if (typeof window.applyCmsPreviewFrameContent === 'function') {
                        window.applyCmsPreviewFrameContent(frame, html);
                    } else {
                        frame.srcdoc = html;
                    }
                } catch (_) {
                    if (typeof window.applyCmsPreviewFrameContent === 'function') {
                        window.applyCmsPreviewFrameContent(frame, '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                    } else {
                        frame.srcdoc = '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>';
                    }
                }
            };

            if (!shouldForceReload && window.__academicsPreviewCache[targetKey]) {
                applyHtml(window.__academicsPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/academics/${targetKey}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__academicsPreviewCache[targetKey] = previewHtml;
                    if (currentAcademicsPreviewRoute === targetKey) {
                        applyHtml(previewHtml);
                    }
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
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

        const getCardEditorCollections = () => {
            const collections = {};

            document.querySelectorAll('[data-academics-card-form]').forEach((form) => {
                const sectionKey = form.getAttribute('data-academics-card-form') || '';
                if (!sectionKey) {
                    return;
                }

                collections[sectionKey] = {
                    form,
                    stack: form.querySelector(`[data-academics-card-stack="${sectionKey}"]`),
                    versionInput: form.querySelector('[data-academics-card-version]'),
                    hiddenInput: form.querySelector('[data-academics-card-active-index]'),
                    selector: `[data-academics-page-card-editor="${sectionKey}"]`,
                    indexAttribute: 'data-academics-page-card-index',
                };
            });

            return collections;
        };

        const cardEditorCollections = getCardEditorCollections();
        const academicsPreviewImageState = new Map();

        function getAcademicsPreviewFrames() {
            return Array.from(document.querySelectorAll('[data-academics-preview-frame]'));
        }

        function getAcademicsPreviewImageKey(sectionKey, cardIndex) {
            return `${String(sectionKey ?? '').trim()}:${String(cardIndex ?? '').trim()}`;
        }

        function replayAcademicsPreviewImages(frame) {
            const targetWindow = frame?.contentWindow;
            if (!targetWindow) {
                return;
            }

            academicsPreviewImageState.forEach((state) => {
                targetWindow.postMessage({
                    type: 'cms-academics-preview-image',
                    section: state.sectionKey,
                    cardIndex: state.cardIndex,
                    src: state.src,
                    defaultSrc: state.defaultSrc,
                }, '*');
            });
        }

        function syncAcademicsPreviewImage(sectionKey, cardIndex, src, defaultSrc = '') {
            const normalizedSection = String(sectionKey || '').trim();
            const normalizedIndex = String(cardIndex ?? '').trim();
            const canTrack = normalizedSection === 'hero' || normalizedIndex !== '';

            if (!normalizedSection || !canTrack) {
                return;
            }

            const nextSrc = String(src || '').trim();
            const nextDefaultSrc = String(defaultSrc || '').trim();
            const key = getAcademicsPreviewImageKey(normalizedSection, normalizedIndex);

            if (nextSrc && nextSrc !== nextDefaultSrc) {
                academicsPreviewImageState.set(key, {
                    sectionKey: normalizedSection,
                    cardIndex: normalizedIndex,
                    src: nextSrc,
                    defaultSrc: nextDefaultSrc,
                });
            } else {
                academicsPreviewImageState.delete(key);
            }

            getAcademicsPreviewFrames().forEach((frame) => {
                const targetWindow = frame.contentWindow;
                if (!targetWindow) {
                    return;
                }

                targetWindow.postMessage({
                    type: 'cms-academics-preview-image',
                    section: normalizedSection,
                    cardIndex: normalizedIndex,
                    src: nextSrc,
                    defaultSrc: nextDefaultSrc,
                }, '*');
            });
        }

        function bumpEditorVersion(input) {
            if (input) {
                input.value = String(Date.now());
            }
        }

        function shouldTrackAcademicsField(target) {
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

        function bindAcademicsDirtyTracking(form, versionInput, boundKey) {
            const boundAttribute = `data-${String(boundKey || 'academics-dirty-tracking-bound')
                .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
                .replace(/[^a-zA-Z0-9_-]+/g, '-')
                .toLowerCase()}`;

            if (!form || form.getAttribute(boundAttribute) === '1') {
                return;
            }

            form.setAttribute(boundAttribute, '1');

            const markDirty = (event) => {
                if (!shouldTrackAcademicsField(event.target)) {
                    return;
                }

                bumpEditorVersion(versionInput);
            };

            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
        }

        const academicsDropzoneDelegationRoots = new WeakSet();

        function findAcademicsDropzoneElement(scope, attribute, inputId) {
            if (!scope || !inputId) {
                return null;
            }

            return Array.from(scope.querySelectorAll(`[${attribute}]`))
                .find((element) => element.getAttribute(attribute) === inputId) || null;
        }

        function resolveAcademicsDropzone(input) {
            if (!(input instanceof HTMLInputElement) || !input.classList.contains('academics-cms-image-dropzone-input')) {
                return null;
            }

            const inputId = input.id || '';
            const dropzone = input.closest('.academics-cms-image-dropzone')
                || findAcademicsDropzoneElement(input.closest('[data-academics-editor-modal]') || document, 'data-academics-dropzone-for', inputId);
            const scope = input.closest('[data-academics-editor-panel]')
                || input.closest('[data-academics-editor-modal]')
                || document;
            const previewEl = findAcademicsDropzoneElement(dropzone, 'data-academics-preview-for', inputId)
                || findAcademicsDropzoneElement(scope, 'data-academics-preview-for', inputId)
                || findAcademicsDropzoneElement(document, 'data-academics-preview-for', inputId);
            const fileNameEl = findAcademicsDropzoneElement(dropzone, 'data-academics-file-name-for', inputId)
                || findAcademicsDropzoneElement(scope, 'data-academics-file-name-for', inputId)
                || findAcademicsDropzoneElement(document, 'data-academics-file-name-for', inputId);
            const removeButton = findAcademicsDropzoneElement(dropzone, 'data-academics-clear-image-for', inputId)
                || findAcademicsDropzoneElement(scope, 'data-academics-clear-image-for', inputId)
                || findAcademicsDropzoneElement(document, 'data-academics-clear-image-for', inputId);
            const editButton = findAcademicsDropzoneElement(dropzone, 'data-academics-edit-image-for', inputId)
                || findAcademicsDropzoneElement(scope, 'data-academics-edit-image-for', inputId)
                || findAcademicsDropzoneElement(document, 'data-academics-edit-image-for', inputId);
            const imageField = input.dataset.academicsImageFieldId
                ? document.getElementById(input.dataset.academicsImageFieldId)
                : (
                    input.closest('[data-academics-page-card-editor]')?.querySelector('[data-academics-image-field]')
                    || input.closest('[data-academics-contents-editor]')?.querySelector('[data-academics-image-field]')
                    || null
                );
            const previewSection = input.closest('[data-academics-page-card-editor]')?.getAttribute('data-academics-page-card-editor')
                || input.closest('[data-academics-editor-panel]')?.getAttribute('data-academics-editor-panel')
                || '';
            const previewCardIndex = input.closest('[data-academics-page-card-index]')?.getAttribute('data-academics-page-card-index') || '';

            return {
                input,
                dropzone,
                fileNameEl,
                previewEl,
                removeButton,
                editButton,
                imageField,
                previewSection,
                previewCardIndex,
                defaultSrc: previewEl?.dataset.academicsDefaultSrc || '',
                emptyText: fileNameEl?.dataset.emptyText || 'Drop image here or click to replace',
            };
        }

        function revokeAcademicsPreviewUrl(input) {
            if (input.__academicsPreviewObjectUrl && typeof URL?.revokeObjectURL === 'function') {
                URL.revokeObjectURL(input.__academicsPreviewObjectUrl);
                input.__academicsPreviewObjectUrl = '';
            }
        }

        function syncAcademicsDropzoneRemoveState(parts) {
            if (!parts?.removeButton) {
                return;
            }

            const hasImage = Boolean((parts.imageField?.value || '').trim() !== '' || (parts.input.files && parts.input.files[0]));
            parts.removeButton.hidden = !hasImage;
            if (parts.editButton) {
                parts.editButton.hidden = !hasImage;
            }
        }

        async function prepareAcademicsImageFile(input, file) {
            if (!file || !window.CmsImageEditor) {
                return file;
            }

            const parts = resolveAcademicsDropzone(input);
            const editedFile = await window.CmsImageEditor.editFile(file, {
                input,
                previewElement: parts?.previewEl || null,
            });

            if (editedFile && editedFile !== file) {
                window.CmsImageEditor.setInputFile(input, editedFile);
            }

            return editedFile;
        }

        function applyAcademicsDropzoneFile(input, file) {
            const parts = resolveAcademicsDropzone(input);
            if (!parts) {
                return;
            }

            revokeAcademicsPreviewUrl(input);

            if (!file) {
                syncAcademicsDropzoneRemoveState(parts);
                return;
            }

            if (parts.fileNameEl) {
                parts.fileNameEl.textContent = `Selected: ${file.name}`;
            }

            let nextSrc = '';
            if (parts.previewEl && typeof URL?.createObjectURL === 'function') {
                input.__academicsPreviewObjectUrl = URL.createObjectURL(file);
                nextSrc = input.__academicsPreviewObjectUrl;
                if (!parts.previewEl.dataset.academicsSavedSrc) {
                    parts.previewEl.dataset.academicsSavedSrc = parts.previewEl.getAttribute('src') || '';
                }
                parts.previewEl.src = nextSrc;
            }

            syncAcademicsDropzoneRemoveState(parts);
        }

        function clearAcademicsDropzoneInput(input) {
            const parts = resolveAcademicsDropzone(input);
            if (!parts) {
                return;
            }

            revokeAcademicsPreviewUrl(input);
            input.value = '';

            if (parts.imageField) {
                if (!parts.imageField.dataset.academicsSavedValue) {
                    parts.imageField.dataset.academicsSavedValue = parts.imageField.value || '';
                }
                parts.imageField.value = '';
            }

            if (parts.previewEl && parts.defaultSrc) {
                if (!parts.previewEl.dataset.academicsSavedSrc) {
                    parts.previewEl.dataset.academicsSavedSrc = parts.previewEl.getAttribute('src') || '';
                }
                parts.previewEl.src = parts.defaultSrc;
            }

            if (parts.fileNameEl) {
                parts.fileNameEl.textContent = parts.emptyText;
            }

            syncAcademicsDropzoneRemoveState(parts);
        }

        function resetAcademicsDropzonePreview(input) {
            const parts = resolveAcademicsDropzone(input);
            if (!parts) {
                return;
            }

            revokeAcademicsPreviewUrl(input);
            input.value = '';

            if (parts.imageField?.dataset.academicsSavedValue !== undefined) {
                parts.imageField.value = parts.imageField.dataset.academicsSavedValue;
                delete parts.imageField.dataset.academicsSavedValue;
            }

            if (parts.previewEl) {
                const savedSrc = parts.previewEl.dataset.academicsSavedSrc || parts.previewEl.getAttribute('src') || parts.defaultSrc;
                if (savedSrc) {
                    parts.previewEl.src = savedSrc;
                }
                delete parts.previewEl.dataset.academicsSavedSrc;
            }

            if (parts.fileNameEl) {
                parts.fileNameEl.textContent = parts.emptyText;
            }

            syncAcademicsDropzoneRemoveState(parts);
        }

        function resetAcademicsModalDropzonePreviews(modal) {
            modal?.querySelectorAll('.academics-cms-image-dropzone-input').forEach((input) => {
                resetAcademicsDropzonePreview(input);
            });
        }

        function inputForAcademicsDropzone(dropzone) {
            const inputId = dropzone?.getAttribute('data-academics-dropzone-for') || '';
            return Array.from((dropzone || document).querySelectorAll('.academics-cms-image-dropzone-input'))
                .find((input) => input.id === inputId)
                || document.getElementById(inputId)
                || null;
        }

        function inputForAcademicsClearButton(button) {
            const inputId = button?.getAttribute('data-academics-clear-image-for') || '';
            return document.getElementById(inputId);
        }

        function bindAcademicsDropzoneDelegates(root) {
            if (!root || academicsDropzoneDelegationRoots.has(root)) {
                return;
            }

            academicsDropzoneDelegationRoots.add(root);

            root.addEventListener('change', async (event) => {
                const input = event.target;
                if (!(input instanceof HTMLInputElement) || !input.classList.contains('academics-cms-image-dropzone-input')) {
                    return;
                }

                const file = await prepareAcademicsImageFile(input, input.files && input.files[0] ? input.files[0] : null);
                if (!file) {
                    input.value = '';
                }
                applyAcademicsDropzoneFile(input, file);
            });

            root.addEventListener('click', async (event) => {
                const clearButton = event.target.closest('[data-academics-clear-image-for]');
                if (clearButton) {
                    const input = inputForAcademicsClearButton(clearButton);
                    if (!input) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    clearAcademicsDropzoneInput(input);
                    return;
                }

                const editButton = event.target.closest('[data-academics-edit-image-for]');
                if (editButton) {
                    event.preventDefault();
                    event.stopPropagation();

                    const inputId = editButton.getAttribute('data-academics-edit-image-for');
                    const input = document.getElementById(inputId);
                    if (!input) return;

                    const parts = resolveAcademicsDropzone(input);
                    let file = input.files && input.files[0];
                    if (!file && parts.previewEl && parts.previewEl.src && parts.previewEl.src !== parts.defaultSrc) {
                        try {
                            const dbPath = parts.imageField ? parts.imageField.value : null;
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
                                canvas.width = parts.previewEl.naturalWidth || parts.previewEl.width || 800;
                                canvas.height = parts.previewEl.naturalHeight || parts.previewEl.height || 600;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(parts.previewEl, 0, 0, canvas.width, canvas.height);
                                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 1.0));
                                if (blob) file = new File([blob], 'image.jpg', { type: 'image/jpeg' });
                            } catch (canvasErr) {
                                console.error("Canvas fallback also failed", canvasErr);
                            }
                        }
                    }

                    (async () => {
                        const editedFile = await prepareAcademicsImageFile(input, file);
                        if (editedFile && editedFile !== file) {
                            applyAcademicsDropzoneFile(input, editedFile);
                        }
                    })();
                    return;
                }

                const dropzone = event.target.closest('[data-academics-dropzone-for]');
                if (!dropzone || event.target.closest('.academics-cms-image-dropzone-input')) {
                    return;
                }

                const input = inputForAcademicsDropzone(dropzone);
                if (input) {
                    input.click();
                }
            });

            root.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                const dropzone = event.target.closest('[data-academics-dropzone-for]');
                if (!dropzone) {
                    return;
                }

                const input = inputForAcademicsDropzone(dropzone);
                if (!input) {
                    return;
                }

                event.preventDefault();
                input.click();
            });

            root.addEventListener('dragover', (event) => {
                const dropzone = event.target.closest('[data-academics-dropzone-for]');
                if (!dropzone) {
                    return;
                }

                event.preventDefault();
                dropzone.classList.add('dragover');
            });

            root.addEventListener('dragleave', (event) => {
                const dropzone = event.target.closest('[data-academics-dropzone-for]');
                if (dropzone) {
                    dropzone.classList.remove('dragover');
                }
            });

            root.addEventListener('drop', async (event) => {
                const dropzone = event.target.closest('[data-academics-dropzone-for]');
                if (!dropzone) {
                    return;
                }

                event.preventDefault();
                dropzone.classList.remove('dragover');

                const file = event.dataTransfer?.files?.[0] ?? null;
                const input = inputForAcademicsDropzone(dropzone);
                if (!file || !input) {
                    return;
                }

                const editedFile = await prepareAcademicsImageFile(input, file);
                if (!editedFile) {
                    input.value = '';
                    applyAcademicsDropzoneFile(input, null);
                    return;
                }

                window.CmsImageEditor?.setInputFile(input, editedFile);
                applyAcademicsDropzoneFile(input, editedFile);
            });
        }

        function initAcademicsImageDropzones(scope = document) {
            bindAcademicsDropzoneDelegates(document);

            scope.querySelectorAll('.academics-cms-image-dropzone-input').forEach((input) => {
                syncAcademicsDropzoneRemoveState(resolveAcademicsDropzone(input));
            });
        }

        function initAcademicsCharCounters(scope = document) {
            scope.querySelectorAll('[data-academics-char-limit]').forEach((field) => {
                if (field.dataset.academicsCharCounterBound === '1') {
                    return;
                }

                const input = field.querySelector('[data-academics-char-input]');
                const counter = field.querySelector('[data-academics-char-counter]');
                const limit = Number(field.getAttribute('data-academics-char-limit') || input?.getAttribute('maxlength') || 0);

                if (!input || !counter || limit <= 0) {
                    return;
                }

                field.dataset.academicsCharCounterBound = '1';
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
        }

        window.__rebindAcademicsCmsPreviewEditor = () => {
            initAcademicsImageDropzones(document);
            initAcademicsCharCounters(document);
        };

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

        function nextAcademicsCardIndex(collection) {
            const indexes = Array.from(collection?.stack?.querySelectorAll(collection.selector) ?? [])
                .map((editor) => Number(editor.getAttribute(collection.indexAttribute) || '0'))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        }

        function replaceAcademicsTemplateTokens(root, tokens) {
            const elements = [root, ...Array.from(root.querySelectorAll('*'))];

            elements.forEach((element) => {
                for (const attribute of Array.from(element.attributes || [])) {
                    let nextValue = attribute.value;

                    Object.entries(tokens).forEach(([token, value]) => {
                        nextValue = nextValue.replaceAll(token, value);
                    });

                    if (nextValue !== attribute.value) {
                        element.setAttribute(attribute.name, nextValue);
                    }
                }

                if (element.childNodes.length === 1 && element.firstChild?.nodeType === Node.TEXT_NODE) {
                    let nextText = element.textContent || '';

                    Object.entries(tokens).forEach(([token, value]) => {
                        nextText = nextText.replaceAll(token, value);
                    });

                    if (nextText !== element.textContent) {
                        element.textContent = nextText;
                    }
                }
            });
        }

        function relabelAcademicsCardEditors(collection) {
            Array.from(collection?.stack?.querySelectorAll(collection.selector) ?? []).forEach((editor, index) => {
                const displayNumber = index + 1;
                const headTitle = editor.querySelector('[data-academics-card-editor-head] h4');
                const headSubtitle = editor.querySelector('[data-academics-card-editor-head] span');
                const titleInput = editor.querySelector('input[name*="[title]"]');
                const dropzoneTitle = editor.querySelector('.academics-cms-image-dropzone-label');
                const formLabel = collection.form?.getAttribute('data-academics-page-label') || 'Program';

                if (headTitle) {
                    headTitle.textContent = `${formLabel} Card ${displayNumber}`;
                }

                if (headSubtitle) {
                    headSubtitle.textContent = String(titleInput?.value || '').trim() || 'New card';
                }

                if (dropzoneTitle) {
                    dropzoneTitle.textContent = `Card ${displayNumber}`;
                }
            });
        }

        function addAcademicsProgramCard(sectionKey) {
            const collection = cardEditorCollections[sectionKey];
            if (!collection) {
                return null;
            }

            const template = collection.form.querySelector(`[data-academics-program-card-template="${sectionKey}"]`);
            if (!template || !collection.stack) {
                return null;
            }

            const index = nextAcademicsCardIndex(collection);
            const dropzoneId = `""-${sectionKey}-${index}-image`;
            const fragment = template.content.cloneNode(true);
            const editor = fragment.querySelector(collection.selector);

            if (!editor) {
                return null;
            }

            replaceAcademicsTemplateTokens(editor, {
                '__INDEX__': String(index),
                '__DROPZONE_ID__': dropzoneId,
            });

            collection.stack.appendChild(fragment);
            initAcademicsImageDropzones(collection.stack);

            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(collection.stack);
            }

            bumpEditorVersion(collection.versionInput);
            relabelAcademicsCardEditors(collection);
            setActiveEditor(collection.stack, collection.selector, collection.indexAttribute, collection.hiddenInput, index);
            editor.scrollIntoView({ block: 'nearest' });
            editor.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface')?.focus();

            return editor;
        }

        async function confirmDeleteAcademicsCard(type, targetIndex) {
            const collection = cardEditorCollections[type];
            if (!collection) {
                return;
            }

            const { stack, selector, indexAttribute, versionInput, hiddenInput, form } = collection;
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

            const title = modal.querySelector('#""-modal-title');
            const description = modal.querySelector('[data-academics-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            modal.querySelectorAll('[data-academics-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-academics-editor-panel') === sectionKey;
                const hasCardTarget = options.cardIndex !== null && options.cardIndex !== undefined && options.cardIndex !== '';
                const cardCollection = cardEditorCollections[sectionKey] || null;
                const isScheduleSection = sectionKey === 'pup-iapply-schedule';
                const isTitleFocus = Boolean(isActive && isScheduleSection && !hasCardTarget);
                const isCardFocus = Boolean(cardCollection && hasCardTarget);
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);
                panel.classList.toggle('is-title-focus', isTitleFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    modal.classList.toggle('is-title-focus', isTitleFocus);
                    if (title) {
                        title.textContent = label || 'Edit academics section';
                    }

                    if (description) {
                        description.textContent = 'Update this section and save to refresh the Academics page preview.';
                    }

                    let activeCardEditor = null;
                    if (cardCollection && !isTitleFocus) {
                        activeCardEditor = setActiveEditor(
                            cardCollection.stack,
                            cardCollection.selector,
                            cardCollection.indexAttribute,
                            cardCollection.hiddenInput,
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

            resetAcademicsModalDropzonePreviews(modal);
            modal.hidden = true;
            modal.classList.remove('is-card-focus');
            modal.classList.remove('is-title-focus');
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
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

            if (data.type === 'cms-academics-add-card') {
                const section = data.section || '';
                openAcademicsEditor(section, data.label || 'Add academics card', {
                    cardIndex: '__new__',
                });
                window.setTimeout(() => addAcademicsProgramCard(section), 0);
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

                const measuredHeight = measureAcademicsPreviewHeight(targetFrame);
                syncAcademicsPreviewHeight(targetFrame, measuredHeight > 0 ? measuredHeight : data.height);
                return;
            }

            if (data.type === 'cms-academics-preview-route') {
                const frame = document.querySelector('[data-academics-preview-frame]');
                if (!frame) {
                    return;
                }

                loadAcademicsPreview(frame, {
                    routeKey: data.route || 'overview',
                });
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-close-academics-editor]')) {
                event.preventDefault();
                closeAcademicsEditor();
                return;
            }

            const previewButton = event.target.closest('[data-academics-preview-page]');
            if (previewButton) {
                event.preventDefault();
                const frame = document.querySelector('[data-academics-preview-frame]');
                if (!frame) {
                    return;
                }

                loadAcademicsPreview(frame, {
                    routeKey: previewButton.getAttribute('data-academics-preview-page') || 'overview',
                });
            }

            const addProgramCardButton = event.target.closest('[data-add-academics-program-card]');
            if (addProgramCardButton) {
                event.preventDefault();
                addAcademicsProgramCard(addProgramCardButton.getAttribute('data-add-academics-program-card') || '');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAcademicsEditor();
            }
        });

        document.querySelectorAll('.""').forEach((form) => {
            form.addEventListener('submit', () => syncEditorsInScope(form));
        });

        document.querySelectorAll('[data-academics-preview-frame]').forEach((frame) => {
            loadAcademicsPreview(frame, {
                routeKey: getStoredAcademicsPreviewRoute() || 'overview',
            });

            frame.addEventListener('load', () => {
                bindAcademicsPreviewDocument(frame);
                replayAcademicsPreviewImages(frame);
                queueAcademicsPreviewSettledSync(frame);
                scheduleFitAllAcademicsPreviews();
                window.setTimeout(() => scheduleAcademicsPreviewSync(frame), 120);
                window.setTimeout(() => scheduleAcademicsPreviewSync(frame), 360);
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

                loadAcademicsPreview(frame, {
                    sessionId,
                    routeKey: currentAcademicsPreviewRoute,
                    forceReload: true,
                });
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
                loadAcademicsPreview(frame, {
                    routeKey: currentAcademicsPreviewRoute,
                    forceReload: true,
                });
            });
        };

        scheduleFitAllAcademicsPreviews();
        syncAcademicsPreviewNav(currentAcademicsPreviewRoute);
        Object.values(cardEditorCollections).forEach((collection) => {
            setActiveEditor(collection.stack, collection.selector, collection.indexAttribute, collection.hiddenInput);
            relabelAcademicsCardEditors(collection);
            bindAcademicsDirtyTracking(
                collection.form,
                collection.versionInput,
                `academicsCardDirtyTrackingBound${collection.form.getAttribute('data-academics-card-form') || ''}`
            );
        });
        window.__rebindAcademicsCmsPreviewEditor();
        window.__academicsCmsPreviewEditorReady = true;
    })();
