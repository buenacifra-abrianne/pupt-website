
    (() => {
        if (window.__aboutCmsPreviewEditorReady) {
            return;
        }

        const ABOUT_PREVIEW_MIN_LOADING_MS = 800;
        let aboutPreviewFitFrame = null;
        const ABOUT_PREVIEW_STORAGE_KEY = `cms:about-preview-route:${window.location.pathname}`;
        const ABOUT_PREVIEW_LEGACY_STORAGE_KEY = '""-active-about-preview-page';
        let currentAboutPreviewRoute = 'overview';
        const aboutModalChromePlacements = new WeakMap();

        function rememberAboutModalChromePlacement(element) {
            if (!element || aboutModalChromePlacements.has(element)) {
                return;
            }

            aboutModalChromePlacements.set(element, {
                parent: element.parentNode,
                nextSibling: element.nextSibling,
            });
        }

        function restoreAboutModalChrome(modal) {
            if (!modal) {
                return;
            }

            modal.querySelectorAll('[data-about-modal-relocated="true"]').forEach((element) => {
                const placement = aboutModalChromePlacements.get(element);
                if (!placement?.parent) {
                    return;
                }

                if (placement.nextSibling && placement.nextSibling.parentNode === placement.parent) {
                    placement.parent.insertBefore(element, placement.nextSibling);
                } else {
                    placement.parent.appendChild(element);
                }

                element.removeAttribute('data-about-modal-relocated');
            });
        }

        function placeAboutCardModalChrome(modal, activeCard) {
            if (!modal || !activeCard) {
                return;
            }

            const closeButton = modal.querySelector('.about-cms-modal-close');
            const activePanel = activeCard.closest('[data-about-editor-panel]');
            const footer = activePanel?.querySelector('.about-cms-modal-footer');

            if (closeButton && !activeCard.contains(closeButton)) {
                rememberAboutModalChromePlacement(closeButton);
                activeCard.prepend(closeButton);
                closeButton.setAttribute('data-about-modal-relocated', 'true');
            }

            if (footer && !activeCard.contains(footer)) {
                rememberAboutModalChromePlacement(footer);
                activeCard.appendChild(footer);
                footer.setAttribute('data-about-modal-relocated', 'true');
            }
        }

        function getStoredAboutPreviewRoute() {
            try {
                return window.localStorage.getItem(ABOUT_PREVIEW_STORAGE_KEY)
                    || window.localStorage.getItem(ABOUT_PREVIEW_LEGACY_STORAGE_KEY)
                    || '';
            } catch (_) {
                return '';
            }
        }

        function storeAboutPreviewRoute(routeKey) {
            try {
                const storedRoute = String(routeKey || 'overview');
                window.localStorage.setItem(ABOUT_PREVIEW_STORAGE_KEY, storedRoute);
                window.localStorage.setItem(ABOUT_PREVIEW_LEGACY_STORAGE_KEY, storedRoute);
            } catch (_) {
                // Ignore storage access failures and keep the in-memory route only.
            }
        }

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
            const visionHeightCap = currentAboutPreviewRoute === 'vision-and-mission'
                ? Number.parseFloat(getComputedStyle(workspace || document.documentElement).getPropertyValue('--about-preview-vision-height-cap')) || 0
                : 0;
            const nextViewportHeight = visionHeightCap > 0 ? Math.min(height, visionHeightCap) : height;

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--about-preview-height', `${nextViewportHeight}px`);
            frame.style.height = `${nextViewportHeight}px`;
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
            const focusVisionCoreValues = () => {
                if (currentAboutPreviewRoute !== 'vision-and-mission') {
                    return;
                }

                const target = doc.querySelector('.about-values-band');
                if (!(target instanceof HTMLElement) || !win || typeof win.scrollTo !== 'function') {
                    return;
                }

                const top = Math.max(0, target.offsetTop - 24);
                win.scrollTo({ top, behavior: 'auto' });
            };

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

            focusVisionCoreValues();
            [120, 320, 720].forEach((delay) => {
                window.setTimeout(focusVisionCoreValues, delay);
            });
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

        window.__aboutPreviewCache = window.__aboutPreviewCache || {};

        function loadAboutPreviewPage(routeKey, options = {}) {
            const frame = document.querySelector('[data-about-preview-frame]');
            const targetKey = routeKey || 'overview';
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
            storeAboutPreviewRoute(targetKey);
            setAboutPreviewLoading(frame, true);

            document.querySelectorAll('[data-about-preview-page]').forEach((btn) => {
                btn.classList.toggle('is-active', btn.getAttribute('data-about-preview-page') === targetKey);
            });

            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, html);
                } else {
                    frame.srcdoc = html;
                }
            };

            if (!shouldForceReload && window.__aboutPreviewCache[targetKey]) {
                applyHtml(window.__aboutPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/about/${targetKey}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__aboutPreviewCache[targetKey] = previewHtml;
                    if (currentAboutPreviewRoute === targetKey) {
                        applyHtml(previewHtml);
                    }
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
        }

        function resolveAboutEditorRoute(sectionKey, providedRoute = '') {
            const route = String(providedRoute || '').trim();
            if (route !== '') {
                return route;
            }

            if (sectionKey === 'hero' || sectionKey === 'intro' || sectionKey === 'contents' || sectionKey === 'philosophy') {
                return 'overview';
            }

            if (
                sectionKey === 'vision-mission-header'
                || sectionKey === 'vision-statement'
                || sectionKey === 'mission-statement'
                || sectionKey === 'vision-mission-statements'
                || sectionKey === 'strategic-goals'
                || sectionKey === 'core-values'
            ) {
                return 'vision-and-mission';
            }

            if (sectionKey === 'strategic-development-plan-header') {
                return 'strategic-development-plan';
            }

            return String(sectionKey || 'overview');
        }

        function openAboutEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-about-editor-modal]');
            if (!modal) {
                return;
            }

            const targetRoute = resolveAboutEditorRoute(sectionKey, options.route || '');
            currentAboutPreviewRoute = targetRoute;
            storeAboutPreviewRoute(targetRoute);

            const title = modal.querySelector('#""-modal-title');
            const description = modal.querySelector('[data-about-editor-description]');
            const isChartFocus = sectionKey === 'campus-officials' && options.chartOnly === true;

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');
            restoreAboutModalChrome(modal);
            modal.dataset.aboutActivePanel = sectionKey;
            modal.classList.remove('is-official-card-focus');
            modal.classList.toggle('is-chart-focus', isChartFocus);

            modal.querySelectorAll('[data-about-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-about-editor-panel') === sectionKey;
                const isContentsCardFocus = sectionKey === 'contents' && String(options.slug || '').trim() !== '';
                const isHistoryCardFocus = sectionKey === 'history' && String(options.historyIndex ?? '').trim() !== '';
                const isStrategicGoalFocus = sectionKey === 'strategic-goals' && String(options.strategicGoalIndex ?? '').trim() !== '';
                const isPlanPriorityFocus = sectionKey === 'strategic-development-plan' && String(options.planPriorityIndex ?? '').trim() !== '';
                const isOfficialCardFocus = sectionKey === 'campus-officials' && !isChartFocus && String(options.officialIndex ?? '').trim() !== '';
                const isSealCardFocus = sectionKey === 'logo-and-symbols' && String(options.sealIndex ?? '').trim() !== '';
                const isServiceCardFocus = sectionKey === 'citizens-charter' && String(options.serviceIndex ?? '').trim() !== '';
                const isServiceHeaderFocus = sectionKey === 'citizens-charter' && options.headerFocus === true;
                const isCardFocus = isContentsCardFocus || isHistoryCardFocus || isStrategicGoalFocus || isPlanPriorityFocus || isOfficialCardFocus || isSealCardFocus || isServiceCardFocus;
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    modal.classList.toggle('is-official-card-focus', sectionKey === 'campus-officials' && isCardFocus);
                    modal.classList.toggle('is-service-card-focus', isServiceCardFocus);
                    modal.classList.toggle('is-service-header-focus', isServiceHeaderFocus);
                    if (title) {
                        title.textContent = label || 'Edit about section';
                    }

                    if (description) {
                        description.hidden = isChartFocus;
                        description.textContent = isChartFocus ? '' : 'Update this section and save to refresh the About page preview.';
                    }

                    let focusScope = panel;
                    if (sectionKey === 'contents') {
                        focusScope = setActiveContentsEditor(options.slug || '') || panel;
                    } else if (sectionKey === 'history') {
                        focusScope = setActiveHistoryEditor(options.historyIndex ?? '') || panel;
                    } else if (sectionKey === 'strategic-goals') {
                        focusScope = setActiveStrategicGoalEditor(options.strategicGoalIndex ?? '', isCardFocus) || panel;
                    } else if (sectionKey === 'strategic-development-plan') {
                        focusScope = setActivePlanPriorityEditor(options.planPriorityIndex ?? '', isCardFocus) || panel;
                    } else if (sectionKey === 'campus-officials' && !isChartFocus) {
                        focusScope = setActiveOfficialEditor(options.officialIndex ?? '', panel) || panel;
                    } else if (sectionKey === 'logo-and-symbols') {
                        focusScope = setActiveSealEditor(options.sealIndex ?? '', panel, isCardFocus) || panel;
                    } else if (sectionKey === 'citizens-charter') {
                        focusScope = setActiveServiceEditor(options.serviceIndex ?? '', panel) || panel;
                    }

                    if (isCardFocus && focusScope?.classList?.contains('about-cms-card-editor')) {
                        placeAboutCardModalChrome(modal, focusScope);
                    }

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea:not([hidden]), select, .rich-editor-surface');
                    firstField?.focus();
                }
            });
        }

        function closeAboutEditor() {
            const modal = document.querySelector('[data-about-editor-modal]');
            if (!modal) {
                return;
            }

            const activePanel = modal.querySelector('[data-about-editor-panel]:not([hidden])');
            if (activePanel?.getAttribute('data-about-editor-panel') === 'logo-and-symbols') {
                discardPendingSealDrafts();
            }

            modal.hidden = true;
            restoreAboutModalChrome(modal);
            delete modal.dataset.aboutActivePanel;
            modal.classList.remove('is-card-focus');
            modal.classList.remove('is-official-card-focus');
            modal.classList.remove('is-service-card-focus');
            modal.classList.remove('is-service-header-focus');
            modal.classList.remove('is-chart-focus');
            const description = modal.querySelector('[data-about-editor-description]');
            if (description) {
                description.hidden = false;
                description.textContent = 'Select a section from the preview to start editing.';
            }
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
        }

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-about-edit') {
                let section = data.section || '';
                let isHeaderFocus = false;
                if (section === 'citizens-charter-header') {
                    section = 'citizens-charter';
                    isHeaderFocus = true;
                }
                openAboutEditor(section, data.label || 'Edit about section', {
                    route: data.route || '',
                    headerFocus: isHeaderFocus,
                });
                return;
            }

            if (data.type === 'cms-about-service-card-add') {
                const editor = addServiceEditor({
                    title: '',
                    description: '',
                    link: '',
                }, true);
                const nextIndex = editor?.getAttribute('data-about-service-index') || '';
                openAboutEditor('citizens-charter', data.label || 'Add Office', {
                    serviceIndex: nextIndex,
                    route: 'citizens-charter',
                });
                return;
            }

            if (data.type === 'cms-about-service-card-delete') {
                confirmDeleteServiceCard(data.index !== undefined && data.index !== null ? data.index : '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-service-card-edit') {
                openAboutEditor('citizens-charter', data.label ? `Edit ${data.label}` : 'Edit Office', {
                    serviceIndex: data.index !== undefined && data.index !== null ? data.index : '',
                    route: 'citizens-charter',
                });
                return;
            }

            if (data.type === 'cms-about-contents-card-edit') {
                openAboutEditor('contents', data.label ? `Edit ${data.label}` : 'Edit about card', {
                    slug: data.slug || '',
                    route: data.route || 'overview',
                });
                return;
            }

            if (data.type === 'cms-about-contents-card-delete') {
                confirmDeleteContentsCard(data.slug || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-history-card-edit') {
                openAboutEditor('history', data.label ? `Edit ${data.label}` : 'Edit history milestone', {
                    historyIndex: data.index || '',
                    route: data.route || 'history',
                });
                return;
            }

            if (data.type === 'cms-about-strategic-goal-edit') {
                openAboutEditor('strategic-goals', data.label ? `Edit ${data.label}` : 'Edit strategic goal pillar', {
                    strategicGoalIndex: data.index || '',
                    route: data.route || 'vision-and-mission',
                });
                return;
            }

            if (data.type === 'cms-about-plan-priority-edit') {
                openAboutEditor('strategic-development-plan', data.label ? `Edit ${data.label}` : 'Edit development priority', {
                    planPriorityIndex: data.index || '',
                    route: data.route || 'strategic-development-plan',
                });
                return;
            }

            if (data.type === 'cms-about-plan-priority-add') {
                initPlanPrioritiesEditor();
                const editor = addPlanPriorityEditor({
                    title: '',
                    body: '',
                }, true);
                const nextIndex = editor?.getAttribute('data-about-plan-priority-index') || '';
                openAboutEditor('strategic-development-plan', data.label || 'Add development priority', {
                    planPriorityIndex: nextIndex,
                    route: data.route || 'strategic-development-plan',
                });
                return;
            }

            if (data.type === 'cms-about-plan-priority-delete') {
                confirmDeletePlanPriority(data.index || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-official-card-add') {
                const editor = addOfficialEditor({
                    title: '',
                    name: '',
                    body: '',
                    image: '',
                    order: '',
                }, true);
                const nextIndex = editor?.getAttribute('data-about-official-index') || '';
                openAboutEditor('campus-officials', data.label || 'Add campus official', {
                    officialIndex: nextIndex,
                    route: data.route || 'campus-officials',
                });
                return;
            }

            if (data.type === 'cms-about-official-chart-edit') {
                openAboutEditor('campus-officials', 'Organizational Structure and Image Uploader', {
                    route: data.route || 'campus-officials',
                    chartOnly: true,
                });
                return;
            }

            if (data.type === 'cms-about-official-card-delete') {
                confirmDeleteOfficialCard(data.index || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-official-card-edit') {
                openAboutEditor('campus-officials', data.label ? `Edit ${data.label}` : 'Edit campus official', {
                    officialIndex: data.index || '',
                    route: data.route || 'campus-officials',
                });
                return;
            }

            if (data.type === 'cms-about-seal-card-add') {
                initSealsEditor();
                const editor = addSealEditor({
                    id: '',
                    label: '',
                    tag: '',
                    image: '',
                    highlights: [],
                    information: { title: 'Informations about the Seal', description: '' },
                    reports: { title: 'Reports and Records', description: '' },
                    links: [],
                }, true, true);
                const nextIndex = editor?.getAttribute('data-about-seal-index') || '';
                openAboutEditor('logo-and-symbols', data.label || 'Add seal', {
                    sealIndex: nextIndex,
                    route: data.route || 'logo-and-symbols',
                });
                return;
            }

            if (data.type === 'cms-about-seal-card-delete') {
                confirmDeleteSealCard(data.index || '', data.label || '');
                return;
            }

            if (data.type === 'cms-about-seal-card-edit') {
                openAboutEditor('logo-and-symbols', data.label ? `Edit ${data.label}` : 'Edit seal', {
                    sealIndex: data.index || '',
                    route: data.route || 'logo-and-symbols',
                });
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
        const introForm = document.querySelector('[data-about-intro-form]');
        const introVersionInput = document.querySelector('[data-about-intro-version]');
        const philosophyForm = document.querySelector('[data-about-philosophy-form]');
        const philosophyVersionInput = document.querySelector('[data-about-philosophy-version]');
        const historyForm = document.querySelector('[data-about-history-form]');
        const historyVersionInput = document.querySelector('[data-about-history-version]');
        const activeHistoryIndexInput = document.querySelector('[data-about-active-history-index]');
        const officialsForm = document.querySelector('[data-about-editor-panel="campus-officials"] form');
        const officialsList = officialsForm?.querySelector('[data-about-officials-list]') || null;
        const officialsVersionInput = officialsForm?.querySelector('[data-about-officials-version]') || null;
        const activeOfficialIndexInput = officialsForm?.querySelector('[data-about-active-official-index]') || null;
        const officialsTemplate = officialsForm?.querySelector('[data-about-official-template]') || null;
        const sealsForm = document.querySelector('[data-about-editor-panel="logo-and-symbols"] form[data-about-seals-form]');
        const sealsList = sealsForm?.querySelector('[data-about-seals-list]') || null;
        const sealsVersionInput = sealsForm?.querySelector('[data-about-seals-version]') || null;
        const activeSealIndexInput = sealsForm?.querySelector('[data-about-active-seal-index]') || null;
        const sealsTemplate = sealsForm?.querySelector('[data-about-seal-template]') || null;
        const sealLinkTemplate = sealsForm?.querySelector('[data-about-seal-link-template]') || null;
        const pendingSealDraftEditors = new Set();
        const planPrioritiesForm = document.querySelector('[data-about-editor-panel="strategic-development-plan"] form[data-about-plan-priorities-form]');
        const planPrioritiesList = planPrioritiesForm?.querySelector('[data-about-plan-priorities-list]') || null;
        const planPrioritiesVersionInput = planPrioritiesForm?.querySelector('[data-about-plan-priorities-version]') || null;
        const activePlanPriorityIndexInput = planPrioritiesForm?.querySelector('[data-about-active-plan-priority-index]') || null;
        const strategicGoalsForm = document.querySelector('[data-about-strategic-goals-form]');
        const strategicGoalsGroups = strategicGoalsForm?.querySelector('[data-about-strategic-goals-groups]') || null;
        const strategicGoalsVersionInput = strategicGoalsForm?.querySelector('[data-about-strategic-goals-version]') || null;
        const activeStrategicGoalIndexInput = strategicGoalsForm?.querySelector('[data-about-active-strategic-goal-index]') || null;
        const coreValuesForm = document.querySelector('[data-about-editor-panel="core-values"] form');
        const coreValuesVersionInput = coreValuesForm?.querySelector('[data-about-core-values-version]') || null;

        const bumpIntroVersion = () => {
            if (introVersionInput) {
                introVersionInput.value = String(Date.now());
            }
        };

        const bumpPhilosophyVersion = () => {
            if (philosophyVersionInput) {
                philosophyVersionInput.value = String(Date.now());
            }
        };

        const bumpContentsVersion = () => {
            if (contentsVersionInput) {
                contentsVersionInput.value = String(Date.now());
            }
        };

        const bumpStrategicGoalsVersion = () => {
            if (strategicGoalsVersionInput) {
                strategicGoalsVersionInput.value = String(Date.now());
            }
        };

        const bumpCoreValuesVersion = () => {
            if (coreValuesVersionInput) {
                coreValuesVersionInput.value = String(Date.now());
            }
        };

        const bumpOfficialsVersion = () => {
            if (officialsVersionInput) {
                officialsVersionInput.value = String(Date.now());
            }
        };

        const bumpSealsVersion = () => {
            if (sealsVersionInput) {
                sealsVersionInput.value = String(Date.now());
            }
        };

        const bumpPlanPrioritiesVersion = () => {
            if (planPrioritiesVersionInput) {
                planPrioritiesVersionInput.value = String(Date.now());
            }
        };

        const createPlanPriorityEditor = (priority = {}) => {
            const template = document.querySelector('[data-about-plan-priority-template]');
            if (!(template instanceof HTMLTemplateElement)) {
                return null;
            }

            const article = template.content.firstElementChild?.cloneNode(true);
            if (!(article instanceof HTMLElement)) {
                return null;
            }

            article.querySelector('[data-about-plan-priority-title]').value = String(priority.title || '');
            const richInput = article.querySelector('.rich-editor-input');
            if (richInput instanceof HTMLTextAreaElement) {
                richInput.value = String(priority.body || '');
            }
            return article;
        };

        const syncPlanPrioritiesForm = () => {
            if (!planPrioritiesList) {
                return;
            }

            const editors = Array.from(planPrioritiesList.querySelectorAll('[data-about-plan-priority-editor]'));
            editors.forEach((editor, index) => {
                editor.setAttribute('data-about-plan-priority-index', String(index));
                const titleInput = editor.querySelector('[data-about-plan-priority-title]');
                const bodyInput = editor.querySelector('.rich-editor-input');
                const heading = editor.querySelector('[data-about-plan-priority-heading]');
                const meta = editor.querySelector('[data-about-plan-priority-meta]');
                const titleValue = String(titleInput?.value || '').trim();
                const fallbackTitle = `Priority Card ${index + 1}`;

                if (titleInput) {
                    titleInput.name = `about[sections][strategic-development-plan][development_priorities][${index}][title]`;
                }

                if (bodyInput instanceof HTMLTextAreaElement) {
                    bodyInput.name = `about[sections][strategic-development-plan][development_priorities][${index}][body]`;
                }

                if (heading) {
                    heading.textContent = titleValue || fallbackTitle;
                }

                if (meta) {
                    meta.textContent = `Priority ${index + 1}`;
                }
            });
        };

        const setActivePlanPriorityEditor = (index = '', collapse = false) => {
            const editors = Array.from(document.querySelectorAll('[data-about-plan-priority-editor]'));

            if (!editors.length) {
                if (activePlanPriorityIndexInput) {
                    activePlanPriorityIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-plan-priority-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = collapse && targetEditor ? !isActive : false;
            });

            if (activePlanPriorityIndexInput) {
                activePlanPriorityIndexInput.value = targetEditor?.getAttribute('data-about-plan-priority-index') || '';
            }

            return targetEditor;
        };

        const addPlanPriorityEditor = (priority = {}, focus = true) => {
            if (!planPrioritiesList) {
                return null;
            }

            const editor = createPlanPriorityEditor(priority);
            if (!editor) {
                return null;
            }
            planPrioritiesList.appendChild(editor);
            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(editor);
            }
            bumpPlanPrioritiesVersion();
            syncPlanPrioritiesForm();

            const nextIndex = editor.getAttribute('data-about-plan-priority-index') || String(planPrioritiesList.querySelectorAll('[data-about-plan-priority-editor]').length - 1);
            if (focus) {
                setActivePlanPriorityEditor(nextIndex, true);
            }

            return editor;
        };

        const deletePlanPriorityByIndex = (index) => {
            if (!planPrioritiesList) {
                return false;
            }

            const normalizedIndex = String(index ?? '').trim();
            const editors = Array.from(planPrioritiesList.querySelectorAll('[data-about-plan-priority-editor]'));
            const targetEditor = editors.find((editor) => editor.getAttribute('data-about-plan-priority-index') === normalizedIndex) || null;
            if (!targetEditor) {
                return false;
            }

            if (editors.length <= 1) {
                const titleInput = targetEditor.querySelector('[data-about-plan-priority-title]');
                const bodyInput = targetEditor.querySelector('.rich-editor-input');
                const bodySurface = targetEditor.querySelector('.rich-editor-surface');
                if (titleInput) {
                    titleInput.value = '';
                }
                if (bodyInput instanceof HTMLTextAreaElement) {
                    bodyInput.value = '';
                }
                if (bodySurface instanceof HTMLElement) {
                    bodySurface.innerHTML = '';
                }
            } else {
                targetEditor.remove();
            }

            bumpPlanPrioritiesVersion();
            syncPlanPrioritiesForm();
            setActivePlanPriorityEditor('', true);
            return true;
        };

        const initPlanPrioritiesEditor = () => {
            if (!planPrioritiesForm || !planPrioritiesList || planPrioritiesForm.dataset.aboutPlanPrioritiesBound === '1') {
                return;
            }

            planPrioritiesForm.dataset.aboutPlanPrioritiesBound = '1';
            if (!planPrioritiesList.querySelector('[data-about-plan-priority-editor]')) {
                planPrioritiesList.appendChild(createPlanPriorityEditor({
                    title: '',
                    body: '',
                }));
            }

            planPrioritiesForm.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-about-plan-priority-add-editor]');
                if (addButton) {
                    event.preventDefault();
                    const editor = addPlanPriorityEditor({
                        title: '',
                        body: '',
                    }, true);
                    editor?.querySelector('[data-about-plan-priority-title]')?.focus();
                    return;
                }

            });

            planPrioritiesForm.addEventListener('input', (event) => {
                if (event.target.closest('[data-about-plan-priority-editor]')) {
                    bumpPlanPrioritiesVersion();
                    syncPlanPrioritiesForm();
                }
            });

            planPrioritiesForm.addEventListener('submit', () => {
                syncPlanPrioritiesForm();
            }, true);

            syncPlanPrioritiesForm();
            setActivePlanPriorityEditor('', false);
        };

        const createStrategicGoalItem = (goal = {}) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'form-group about-cms-goal-item';
            wrapper.setAttribute('data-about-strategic-goal-item', '');
            wrapper.innerHTML = `
                <label data-about-strategic-goal-label>Goal</label>
                <div class="about-cms-goal-row">
                    <input type="text" data-about-strategic-goal-text maxlength="4000" value="">
                    <input type="hidden" data-about-strategic-goal-number value="">
                    <button type="button" class="btn btn-outline-danger" data-about-delete-sg>Delete</button>
                </div>
            `;
            wrapper.querySelector('[data-about-strategic-goal-text]').value = String(goal.text || '');
            wrapper.querySelector('[data-about-strategic-goal-number]').value = String(goal.number || '');
            return wrapper;
        };

        const createStrategicGoalGroup = (group = {}) => {
            const article = document.createElement('article');
            article.className = 'about-cms-card-editor';
            article.setAttribute('data-about-strategic-goal-group', '');
            article.setAttribute('data-about-strategic-goal-editor', '');
            article.setAttribute('data-about-strategic-goal-index', '');
            article.innerHTML = `
                <div class="about-cms-card-editor-head" data-about-card-editor-head>
                    <h4 data-about-strategic-group-heading>Pillar</h4>
                    <span></span>
                </div>
                <div class="about-cms-form-grid about-cms-strategic-pillar-grid">
                    <div class="form-group">
                        <label>Pillar Label</label>
                        <input type="text" data-about-strategic-group-pillar maxlength="255" value="">
                    </div>
                    <div class="form-group">
                        <label>Pillar Title</label>
                        <input type="text" data-about-strategic-group-title maxlength="255" value="">
                    </div>
                </div>
                <div class="about-cms-inline-actions">
                    <button type="button" class="btn btn-outline-secondary" data-about-add-sg>+ Add new SG</button>
                </div>
                <div class="about-cms-goal-stack" data-about-strategic-group-goals></div>
            `;

            article.querySelector('[data-about-strategic-group-pillar]').value = String(group.pillar || '');
            article.querySelector('[data-about-strategic-group-title]').value = String(group.title || '');

            const goalsHost = article.querySelector('[data-about-strategic-group-goals]');
            const goals = Array.isArray(group.goals) && group.goals.length ? group.goals : [{ number: '1', text: '' }];
            goals.forEach((goal) => goalsHost.appendChild(createStrategicGoalItem(goal)));
            return article;
        };

        const syncStrategicGoalsForm = () => {
            if (!strategicGoalsGroups) {
                return;
            }

            const groups = Array.from(strategicGoalsGroups.querySelectorAll('[data-about-strategic-goal-group]'));
            groups.forEach((group, groupIndex) => {
                group.setAttribute('data-about-strategic-goal-index', String(groupIndex));
                const pillarInput = group.querySelector('[data-about-strategic-group-pillar]');
                const titleInput = group.querySelector('[data-about-strategic-group-title]');
                const heading = group.querySelector('[data-about-strategic-group-heading]');
                const headingMeta = group.querySelector('.about-cms-card-editor-head span');
                const pillarValue = String(pillarInput?.value || '').trim() || `Pillar ${groupIndex + 1}`;
                const titleValue = String(titleInput?.value || '').trim();

                if (pillarInput) {
                    pillarInput.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][pillar]`;
                    if (String(pillarInput.value || '').trim() === '') {
                        pillarInput.value = pillarValue;
                    }
                }

                if (titleInput) {
                    titleInput.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][title]`;
                }

                if (heading) {
                    heading.textContent = pillarValue;
                }

                if (headingMeta) {
                    headingMeta.textContent = titleValue;
                }

                const goals = Array.from(group.querySelectorAll('[data-about-strategic-goal-item]'));
                goals.forEach((goalItem, goalIndex) => {
                    const goalText = goalItem.querySelector('[data-about-strategic-goal-text]');
                    const goalNumber = goalItem.querySelector('[data-about-strategic-goal-number]');
                    const goalLabel = goalItem.querySelector('[data-about-strategic-goal-label]');
                    const numberValue = String(goalIndex + 1);

                    if (goalText) {
                        goalText.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][goals][${goalIndex}][text]`;
                    }

                    if (goalNumber) {
                        goalNumber.name = `about[sections][vision-and-mission][strategic_goals][${groupIndex}][goals][${goalIndex}][number]`;
                        goalNumber.value = numberValue;
                    }

                    if (goalLabel) {
                        goalLabel.textContent = `Goal ${numberValue}`;
                    }
                });
            });
        };

        const setActiveStrategicGoalEditor = (index = '', collapse = false) => {
            const editors = Array.from(document.querySelectorAll('[data-about-strategic-goal-editor]'));

            if (!editors.length) {
                if (activeStrategicGoalIndexInput) {
                    activeStrategicGoalIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-strategic-goal-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.hidden = collapse && targetEditor ? !isActive : false;
            });

            if (activeStrategicGoalIndexInput) {
                activeStrategicGoalIndexInput.value = targetEditor?.getAttribute('data-about-strategic-goal-index') || '';
            }

            return targetEditor;
        };

        const initStrategicGoalsEditor = () => {
            if (!strategicGoalsForm || !strategicGoalsGroups || strategicGoalsForm.dataset.aboutStrategicGoalsBound === '1') {
                return;
            }

            strategicGoalsForm.dataset.aboutStrategicGoalsBound = '1';
            if (!strategicGoalsGroups.querySelector('[data-about-strategic-goal-group]')) {
                strategicGoalsGroups.appendChild(createStrategicGoalGroup({ pillar: 'Pillar 1', title: '', goals: [{ number: '1', text: '' }] }));
            }

            strategicGoalsForm.addEventListener('click', (event) => {
                const addGoal = event.target.closest('[data-about-add-sg]');
                if (addGoal) {
                    event.preventDefault();
                    const group = addGoal.closest('[data-about-strategic-goal-group]');
                    const host = group?.querySelector('[data-about-strategic-group-goals]');
                    if (host) {
                        host.appendChild(createStrategicGoalItem({}));
                        bumpStrategicGoalsVersion();
                        syncStrategicGoalsForm();
                    }
                    return;
                }

                const deleteGoal = event.target.closest('[data-about-delete-sg]');
                if (deleteGoal) {
                    event.preventDefault();
                    const group = deleteGoal.closest('[data-about-strategic-goal-group]');
                    const goalItems = group ? Array.from(group.querySelectorAll('[data-about-strategic-goal-item]')) : [];
                    if (goalItems.length > 1) {
                        deleteGoal.closest('[data-about-strategic-goal-item]')?.remove();
                        bumpStrategicGoalsVersion();
                        syncStrategicGoalsForm();
                    }
                    return;
                }

            });

            strategicGoalsForm.addEventListener('input', (event) => {
                if (event.target.closest('[data-about-strategic-goal-group]')) {
                    bumpStrategicGoalsVersion();
                    syncStrategicGoalsForm();
                }
            });

            strategicGoalsForm.addEventListener('submit', () => {
                syncStrategicGoalsForm();
            }, true);

            syncStrategicGoalsForm();
            setActiveStrategicGoalEditor('', false);
        };

        const shouldTrackAboutContentsField = (target) => {
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

        const bindAboutContentsDirtyTracking = () => {
            if (!contentsForm || contentsForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            contentsForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackAboutContentsField(event.target)) {
                    return;
                }

                bumpContentsVersion();
            };

            contentsForm.addEventListener('input', markDirty);
            contentsForm.addEventListener('change', markDirty);
        };

        const bindAboutIntroDirtyTracking = () => {
            if (!introForm || introForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            introForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.closest('.rich-editor-surface')) {
                    bumpIntroVersion();
                    return;
                }

                if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
                    const type = (target.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset') {
                        return;
                    }
                }

                bumpIntroVersion();
            };

            introForm.addEventListener('input', markDirty);
            introForm.addEventListener('change', markDirty);
            introForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpIntroVersion, 0);
                }
            });
        };

        const bindPhilosophyDirtyTracking = () => {
            if (!philosophyForm || philosophyForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            philosophyForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.closest('.rich-editor-surface')) {
                    bumpPhilosophyVersion();
                    return;
                }

                if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
                    const type = (target.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset') {
                        return;
                    }
                }

                bumpPhilosophyVersion();
            };

            philosophyForm.addEventListener('input', markDirty);
            philosophyForm.addEventListener('change', markDirty);
            philosophyForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpPhilosophyVersion, 0);
                }
            });
        };

        const bindAboutHistoryDirtyTracking = () => {
            if (!historyForm || historyForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            historyForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
                    const type = (target.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset') {
                        return;
                    }
                }

                bumpHistoryVersion();
            };

            historyForm.addEventListener('input', markDirty);
            historyForm.addEventListener('change', markDirty);
            historyForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpHistoryVersion, 0);
                }
            });
        };

        const bindCoreValuesDirtyTracking = () => {
            if (!coreValuesForm || coreValuesForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            coreValuesForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackAboutContentsField(event.target)) {
                    return;
                }

                bumpCoreValuesVersion();
            };

            coreValuesForm.addEventListener('input', markDirty);
            coreValuesForm.addEventListener('change', markDirty);
        };

        const bindOfficialsDirtyTracking = () => {
            if (!officialsForm || officialsForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            officialsForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackAboutContentsField(event.target)) {
                    return;
                }

                bumpOfficialsVersion();
            };

            officialsForm.addEventListener('input', markDirty);
            officialsForm.addEventListener('change', markDirty);
        };

        const bindSealsDirtyTracking = () => {
            if (!sealsForm || sealsForm.dataset.aboutDirtyTrackingBound === '1') {
                return;
            }

            sealsForm.dataset.aboutDirtyTrackingBound = '1';

            const markDirty = (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.closest('.rich-editor-surface')) {
                    bumpSealsVersion();
                    return;
                }

                if (!shouldTrackAboutContentsField(target)) {
                    return;
                }

                bumpSealsVersion();
            };

            sealsForm.addEventListener('input', markDirty);
            sealsForm.addEventListener('change', markDirty);
            sealsForm.addEventListener('click', (event) => {
                if (event.target.closest('.rich-editor-toolbar button')) {
                    window.setTimeout(bumpSealsVersion, 0);
                }
            });
        };

        const initOfficialsEditor = () => {
            if (!officialsForm || !officialsList || officialsForm.dataset.aboutOfficialsBound === '1') {
                return;
            }

            officialsForm.dataset.aboutOfficialsBound = '1';

            if (!officialsList.querySelector('[data-about-official-editor]')) {
                addOfficialEditor({
                    title: '',
                    name: '',
                    body: '',
                    image: '',
                    order: '',
                }, false);
            } else {
                relabelOfficialEditors();
                setActiveOfficialEditor('', officialsForm);
            }

            officialsForm.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-about-official-add-editor]');
                if (addButton) {
                    event.preventDefault();
                    addOfficialEditor({
                        title: '',
                        name: '',
                        body: '',
                        image: '',
                        order: '',
                    }, true);
                    return;
                }

                const editorHead = event.target.closest('[data-about-card-editor-head]');
                if (editorHead) {
                    const editor = editorHead.closest('[data-about-official-editor]');
                    const officialIndex = editor?.getAttribute('data-about-official-index') || '';
                    setActiveOfficialEditor(officialIndex, officialsForm);
                }
            });

            officialsForm.addEventListener('input', (event) => {
                const editor = event.target.closest('[data-about-official-editor]');
                if (!editor) {
                    return;
                }

                const editorIndex = Array.from(officialsList.querySelectorAll('[data-about-official-editor]')).indexOf(editor);
                syncOfficialCardMeta(editor, editorIndex >= 0 ? editorIndex : null);
            });
        };

        const formatHistoryMonth = (value) => {
            const match = String(value || '').match(/^(\d{4})-(\d{2})$/);
            if (!match) {
                return '';
            }

            const year = Number(match[1]);
            const monthIndex = Number(match[2]) - 1;
            if (!Number.isFinite(year) || !Number.isFinite(monthIndex) || monthIndex < 0 || monthIndex > 11) {
                return '';
            }

            return new Intl.DateTimeFormat('en', {
                month: 'long',
                year: 'numeric',
                timeZone: 'UTC',
            }).format(new Date(Date.UTC(year, monthIndex, 1)));
        };

        const syncAboutHistoryDateGroup = (group) => {
            const periodInput = group.querySelector('[data-about-history-period]');
            const startInput = group.querySelector('[data-about-history-date-start]');
            const endInput = group.querySelector('[data-about-history-date-end]');

            if (!periodInput || !startInput) {
                return;
            }

            const startLabel = formatHistoryMonth(startInput.value);
            const endLabel = formatHistoryMonth(endInput?.value || '');
            const nextPeriod = startLabel && endLabel
                ? `${startLabel} - ${endLabel}`
                : (startLabel || endLabel);

            if (nextPeriod !== '' && periodInput.value !== nextPeriod) {
                periodInput.value = nextPeriod;
                periodInput.dispatchEvent(new Event('input', {
                    bubbles: true,
                }));
                periodInput.dispatchEvent(new Event('change', {
                    bubbles: true,
                }));
                bumpHistoryVersion();
            }
        };

        const initAboutHistoryDateFields = (scope = document) => {
            scope.querySelectorAll('[data-about-history-date-group]').forEach((group) => {
                if (group.dataset.aboutHistoryDatesBound === '1') {
                    return;
                }

                group.dataset.aboutHistoryDatesBound = '1';
                const sync = () => syncAboutHistoryDateGroup(group);
                group.querySelectorAll('[data-about-history-date-start], [data-about-history-date-end]').forEach((input) => {
                    input.addEventListener('input', sync);
                    input.addEventListener('change', sync);
                });
            });
        };

        window.syncAboutHistoryDateFields = (scope = document) => {
            scope.querySelectorAll('[data-about-history-date-group]').forEach(syncAboutHistoryDateGroup);
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

        const submitPlanPrioritiesForm = () => {
            if (!planPrioritiesForm) {
                return;
            }

            syncPlanPrioritiesForm();
            if (typeof planPrioritiesForm.requestSubmit === 'function') {
                planPrioritiesForm.requestSubmit();
                return;
            }

            planPrioritiesForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const submitOfficialsForm = () => {
            if (!officialsForm) {
                return;
            }

            if (typeof officialsForm.requestSubmit === 'function') {
                officialsForm.requestSubmit();
                return;
            }

            officialsForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const bumpHistoryVersion = () => {
            if (historyVersionInput) {
                historyVersionInput.value = String(Date.now());
            }
        };

        const setActiveHistoryEditor = (index = '') => {
            const editors = Array.from(document.querySelectorAll('[data-about-history-editor]'));

            if (!editors.length) {
                if (activeHistoryIndexInput) {
                    activeHistoryIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-history-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors.find((editor) => editor.querySelector('[data-about-history-visible]')?.value !== '0') || editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.hidden = targetEditor ? !isActive : false;
            });

            if (activeHistoryIndexInput) {
                activeHistoryIndexInput.value = targetEditor?.getAttribute('data-about-history-index') || '';
            }

            return targetEditor;
        };

        const syncOfficialCardMeta = (editor, displayIndex = null) => {
            if (!editor) {
                return;
            }

            const heading = editor.querySelector('[data-about-official-heading]');
            const meta = editor.querySelector('[data-about-official-meta]');
            const titleInput = editor.querySelector('input[name*="[title]"]');
            const nameInput = editor.querySelector('input[name*="[name]"]');
            const titleValue = String(titleInput?.value || '').trim();
            const nameValue = String(nameInput?.value || '').trim();
            const fallbackMeta = 'Campus official';

            if (heading && Number.isFinite(displayIndex)) {
                heading.textContent = `Official Card ${Number(displayIndex) + 1}`;
            }

            if (meta) {
                meta.textContent = nameValue || titleValue || fallbackMeta;
            }
        };

        const relabelOfficialEditors = () => {
            const editors = Array.from(officialsList?.querySelectorAll('[data-about-official-editor]') || []);
            editors.forEach((editor, index) => {
                syncOfficialCardMeta(editor, index);
            });
        };

        const nextOfficialIndex = () => {
            const indexes = Array.from(officialsList?.querySelectorAll('[data-about-official-editor]') || [])
                .map((editor) => Number(editor.getAttribute('data-about-official-index')))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const createOfficialEditor = (official = {}, index = 0, displayNumber = 1) => {
            if (!(officialsTemplate instanceof HTMLTemplateElement)) {
                return null;
            }

            const markup = officialsTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(displayNumber));

            const shell = document.createElement('div');
            shell.innerHTML = markup.trim();
            const editor = shell.firstElementChild;
            if (!(editor instanceof HTMLElement)) {
                return null;
            }

            const titleInput = editor.querySelector('input[name*="[title]"]');
            const nameInput = editor.querySelector('input[name*="[name]"]');
            const bodyInput = editor.querySelector('textarea[name*="[body]"]');
            const imageInput = editor.querySelector('input[data-about-image-field]');
            const orderInput = editor.querySelector('input[data-about-official-order]');

            if (titleInput instanceof HTMLInputElement) {
                titleInput.value = String(official.title || '');
            }
            if (nameInput instanceof HTMLInputElement) {
                nameInput.value = String(official.name || '');
            }
            if (bodyInput instanceof HTMLTextAreaElement) {
                bodyInput.value = String(official.body || '');
            }
            if (imageInput instanceof HTMLInputElement) {
                imageInput.value = String(official.image || '');
            }
            if (orderInput instanceof HTMLInputElement) {
                orderInput.value = String(official.order || displayNumber);
            }

            return editor;
        };

        const addOfficialEditor = (official = {}, focus = true) => {
            if (!officialsList) {
                return null;
            }

            const index = nextOfficialIndex();
            const displayNumber = officialsList.querySelectorAll('[data-about-official-editor]').length + 1;
            const editor = createOfficialEditor(official, index, displayNumber);
            if (!editor) {
                return null;
            }

            officialsList.appendChild(editor);
            initAboutImageDropzones(editor);
            initAboutCharCounters(editor);
            relabelOfficialEditors();
            bumpOfficialsVersion();
            const activeEditor = setActiveOfficialEditor(index, officialsForm || document);

            if (focus) {
                const firstField = activeEditor?.querySelector('input:not([type="hidden"]), textarea, .rich-editor-surface');
                firstField?.focus();
            }

            return activeEditor || editor;
        };

        const deleteOfficialByIndex = (index) => {
            if (!officialsList) {
                return false;
            }

            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return false;
            }

            const targetEditor = officialsList.querySelector(`[data-about-official-editor][data-about-official-index="${normalizedIndex}"]`);
            if (!targetEditor) {
                return false;
            }

            targetEditor.remove();
            relabelOfficialEditors();
            setActiveOfficialEditor('', officialsForm || document);
            bumpOfficialsVersion();
            return true;
        };

        const setActiveOfficialEditor = (index = '', scope = document) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-official-editor]'));

            if (!editors.length) {
                if (activeOfficialIndexInput) {
                    activeOfficialIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-official-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = targetEditor ? !isActive : false;
            });

            if (activeOfficialIndexInput) {
                activeOfficialIndexInput.value = targetEditor?.getAttribute('data-about-official-index') || '';
            }

            return targetEditor;
        };

        const syncSealEditorMeta = (editor, displayIndex = null) => {
            if (!editor) {
                return;
            }

            const heading = editor.querySelector('[data-about-seal-heading]');
            const meta = editor.querySelector('[data-about-seal-meta]');
            const labelInput = editor.querySelector('[data-about-seal-label]');
            const tagInput = editor.querySelector('[data-about-seal-tag]');
            const sealIdInput = editor.querySelector('[data-about-seal-id]');
            const labelValue = String(labelInput?.value || '').trim();
            const tagValue = String(tagInput?.value || '').trim();
            const fallbackLabel = Number.isFinite(displayIndex) ? `Seal ${Number(displayIndex) + 1}` : 'Seal';

            if (heading) {
                heading.textContent = labelValue || fallbackLabel;
            }

            if (meta) {
                meta.textContent = tagValue || fallbackLabel;
            }

            if (sealIdInput instanceof HTMLInputElement) {
                const current = String(sealIdInput.value || '').trim();
                if (!current) {
                    const raw = (labelValue || fallbackLabel).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    sealIdInput.value = raw || `seal-${Number.isFinite(displayIndex) ? Number(displayIndex) + 1 : 1}`;
                }
            }
        };

        const syncSealLinkNames = (editor) => {
            const sealIndex = editor?.getAttribute('data-about-seal-index') || '';
            if (!editor || sealIndex === '') {
                return;
            }

            const sealIdInput = editor.querySelector('[data-about-seal-id]');
            if (sealIdInput instanceof HTMLInputElement) {
                sealIdInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][id]`;
            }

            const imageInput = editor.querySelector('[data-about-seal-image]');
            if (imageInput instanceof HTMLInputElement) {
                imageInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][image]`;
            }

            const imageFileInput = editor.querySelector('.about-cms-image-dropzone-input');
            if (imageFileInput instanceof HTMLInputElement) {
                imageFileInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][image_file]`;
            }

            const labelInput = editor.querySelector('[data-about-seal-label]');
            if (labelInput instanceof HTMLInputElement) {
                labelInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][label]`;
            }

            const tagInput = editor.querySelector('[data-about-seal-tag]');
            if (tagInput instanceof HTMLInputElement) {
                tagInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][tag]`;
            }

            const highlightsInput = editor.querySelector('[data-about-seal-highlights]');
            if (highlightsInput instanceof HTMLTextAreaElement) {
                highlightsInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][highlights_text]`;
            }

            const infoDescInput = editor.querySelector('[data-about-seal-info-desc] .rich-editor-input');
            if (infoDescInput instanceof HTMLTextAreaElement) {
                infoDescInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][information][description]`;
            }

            const reportsDescInput = editor.querySelector('[data-about-seal-reports-desc] .rich-editor-input');
            if (reportsDescInput instanceof HTMLTextAreaElement) {
                reportsDescInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][reports][description]`;
            }

            const linkItems = Array.from(editor.querySelectorAll('[data-about-seal-link-item]'));
            linkItems.forEach((item, linkIndex) => {
                const labelInput = item.querySelector('[data-about-seal-link-label]');
                const urlInput = item.querySelector('[data-about-seal-link-url]');
                if (labelInput instanceof HTMLInputElement) {
                    labelInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][links][${linkIndex}][label]`;
                }
                if (urlInput instanceof HTMLInputElement) {
                    urlInput.name = `about[sections][logo-and-symbols][seals][${sealIndex}][links][${linkIndex}][url]`;
                }
            });
        };

        const relabelSealEditors = () => {
            const editors = Array.from(sealsList?.querySelectorAll('[data-about-seal-editor]') || []);
            editors.forEach((editor, index) => {
                editor.setAttribute('data-about-seal-index', String(index));
                syncSealEditorMeta(editor, index);
                syncSealLinkNames(editor);
            });
        };

        const discardPendingSealDrafts = () => {
            if (!sealsList || pendingSealDraftEditors.size === 0) {
                return;
            }

            let removed = false;
            pendingSealDraftEditors.forEach((editor) => {
                if (editor instanceof HTMLElement && editor.isConnected) {
                    editor.remove();
                    removed = true;
                }
            });
            pendingSealDraftEditors.clear();

            if (!removed) {
                return;
            }

            if (sealsList.querySelector('[data-about-seal-editor]')) {
                relabelSealEditors();
                setActiveSealEditor('', sealsForm || document, false);
                bumpSealsVersion();
                return;
            }

            if (activeSealIndexInput) {
                activeSealIndexInput.value = '';
            }
            bumpSealsVersion();
        };

        const nextSealIndex = () => {
            const indexes = Array.from(sealsList?.querySelectorAll('[data-about-seal-editor]') || [])
                .map((editor) => Number(editor.getAttribute('data-about-seal-index')))
                .filter((value) => Number.isFinite(value));

            return indexes.length ? Math.max(...indexes) + 1 : 0;
        };

        const createSealLinkRow = (link = {}) => {
            if (!(sealLinkTemplate instanceof HTMLTemplateElement)) {
                return null;
            }

            const row = sealLinkTemplate.content.firstElementChild?.cloneNode(true);
            if (!(row instanceof HTMLElement)) {
                return null;
            }

            const labelInput = row.querySelector('[data-about-seal-link-label]');
            const urlInput = row.querySelector('[data-about-seal-link-url]');
            if (labelInput instanceof HTMLInputElement) {
                labelInput.value = String(link.label || '');
            }
            if (urlInput instanceof HTMLInputElement) {
                urlInput.value = String(link.url || '');
            }

            return row;
        };

        const createSealEditor = (seal = {}, index = 0, displayNumber = 1) => {
            if (!(sealsTemplate instanceof HTMLTemplateElement)) {
                return null;
            }

            const markup = sealsTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(displayNumber));
            const shell = document.createElement('div');
            shell.innerHTML = markup.trim();
            const editor = shell.firstElementChild;
            if (!(editor instanceof HTMLElement)) {
                return null;
            }

            const idInput = editor.querySelector('[data-about-seal-id]');
            const labelInput = editor.querySelector('[data-about-seal-label]');
            const tagInput = editor.querySelector('[data-about-seal-tag]');
            const imageInput = editor.querySelector('[data-about-seal-image]');
            const highlightsInput = editor.querySelector('[data-about-seal-highlights]');
            const infoDescInput = editor.querySelector('[data-about-seal-info-desc] .rich-editor-input');
            const reportsDescInput = editor.querySelector('[data-about-seal-reports-desc] .rich-editor-input');

            if (idInput instanceof HTMLInputElement) {
                idInput.value = String(seal.id || '');
            }
            if (labelInput instanceof HTMLInputElement) {
                labelInput.value = String(seal.label || '');
            }
            if (tagInput instanceof HTMLInputElement) {
                tagInput.value = String(seal.tag || '');
            }
            if (imageInput instanceof HTMLInputElement) {
                imageInput.value = String(seal.image || '');
            }
            if (highlightsInput instanceof HTMLTextAreaElement) {
                const highlights = Array.isArray(seal.highlights) ? seal.highlights : [];
                highlightsInput.value = highlights.map((item) => String(item || '').trim()).filter(Boolean).join('\n');
            }
            if (infoDescInput instanceof HTMLTextAreaElement) {
                infoDescInput.value = String(seal?.information?.description || '');
            }
            if (reportsDescInput instanceof HTMLTextAreaElement) {
                reportsDescInput.value = String(seal?.reports?.description || '');
            }

            const linksHost = editor.querySelector('[data-about-seal-links-list]');
            const links = Array.isArray(seal.links) ? seal.links : [];
            if (linksHost) {
                links.forEach((link) => {
                    const row = createSealLinkRow(link);
                    if (row) {
                        linksHost.appendChild(row);
                    }
                });
            }

            return editor;
        };

        const setActiveSealEditor = (index = '', scope = document, collapse = false) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-seal-editor]'));

            if (!editors.length) {
                if (activeSealIndexInput) {
                    activeSealIndexInput.value = '';
                }
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-seal-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = collapse && targetEditor ? !isActive : false;
            });

            if (activeSealIndexInput) {
                activeSealIndexInput.value = targetEditor?.getAttribute('data-about-seal-index') || '';
            }

            return targetEditor;
        };

        const addSealEditor = (seal = {}, focus = true, trackAsDraft = false) => {
            if (!sealsList) {
                return null;
            }

            const index = nextSealIndex();
            const displayNumber = sealsList.querySelectorAll('[data-about-seal-editor]').length + 1;
            const editor = createSealEditor(seal, index, displayNumber);
            if (!editor) {
                return null;
            }

            sealsList.appendChild(editor);
            if (trackAsDraft) {
                pendingSealDraftEditors.add(editor);
            }
            if (typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(editor);
            }
            initAboutImageDropzones(editor);
            relabelSealEditors();
            bumpSealsVersion();

            const activeIndex = editor.getAttribute('data-about-seal-index') || String(index);
            const activeEditor = setActiveSealEditor(activeIndex, sealsForm || document, true);
            if (focus) {
                const firstField = activeEditor?.querySelector('input:not([type="hidden"]), textarea, .rich-editor-surface');
                firstField?.focus();
            }

            return activeEditor || editor;
        };

        const deleteSealByIndex = (index) => {
            if (!sealsList) {
                return false;
            }

            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return false;
            }

            const editor = sealsList.querySelector(`[data-about-seal-editor][data-about-seal-index="${normalizedIndex}"]`);
            if (!editor) {
                return false;
            }

            pendingSealDraftEditors.delete(editor);
            editor.remove();
            if (!sealsList.querySelector('[data-about-seal-editor]')) {
                addSealEditor({
                    id: '',
                    label: '',
                    tag: '',
                    image: '',
                    highlights: [],
                    information: { title: 'Informations about the Seal', description: '' },
                    reports: { title: 'Reports and Records', description: '' },
                    links: [],
                }, false);
            } else {
                relabelSealEditors();
                setActiveSealEditor('', sealsForm || document, true);
                bumpSealsVersion();
            }

            return true;
        };

        const submitSealsForm = () => {
            if (!sealsForm) {
                return;
            }

            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(sealsForm);
            }

            if (typeof sealsForm.requestSubmit === 'function') {
                sealsForm.requestSubmit();
                return;
            }

            sealsForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const initSealsEditor = () => {
            if (!sealsForm || !sealsList || sealsForm.dataset.aboutSealsBound === '1') {
                return;
            }

            sealsForm.dataset.aboutSealsBound = '1';
            if (!sealsList.querySelector('[data-about-seal-editor]')) {
                addSealEditor({
                    id: '',
                    label: '',
                    tag: '',
                    image: '',
                    highlights: [],
                    information: { title: 'Informations about the Seal', description: '' },
                    reports: { title: 'Reports and Records', description: '' },
                    links: [],
                }, false);
            } else {
                relabelSealEditors();
                setActiveSealEditor('', sealsForm, false);
            }

            sealsForm.addEventListener('click', (event) => {
                const addSealButton = event.target.closest('[data-about-seal-add-editor]');
                if (addSealButton) {
                    event.preventDefault();
                    addSealEditor({
                        id: '',
                        label: '',
                        tag: '',
                        image: '',
                        highlights: [],
                        information: { title: 'Informations about the Seal', description: '' },
                        reports: { title: 'Reports and Records', description: '' },
                        links: [],
                    }, true, true);
                    return;
                }

                const editorHead = event.target.closest('[data-about-card-editor-head]');
                if (editorHead) {
                    const editor = editorHead.closest('[data-about-seal-editor]');
                    const sealIndex = editor?.getAttribute('data-about-seal-index') || '';
                    setActiveSealEditor(sealIndex, sealsForm, false);
                }

                const addLinkButton = event.target.closest('[data-about-seal-link-add]');
                if (addLinkButton) {
                    event.preventDefault();
                    const editor = addLinkButton.closest('[data-about-seal-editor]');
                    const linksHost = editor?.querySelector('[data-about-seal-links-list]');
                    if (!editor || !linksHost) {
                        return;
                    }

                    const row = createSealLinkRow({});
                    if (row) {
                        linksHost.appendChild(row);
                        syncSealLinkNames(editor);
                        bumpSealsVersion();
                    }
                    return;
                }

                const deleteLinkButton = event.target.closest('[data-about-seal-link-delete]');
                if (deleteLinkButton) {
                    event.preventDefault();
                    const editor = deleteLinkButton.closest('[data-about-seal-editor]');
                    deleteLinkButton.closest('[data-about-seal-link-item]')?.remove();
                    if (editor) {
                        syncSealLinkNames(editor);
                    }
                    bumpSealsVersion();
                }
            });

            sealsForm.addEventListener('input', (event) => {
                const editor = event.target.closest('[data-about-seal-editor]');
                if (!editor) {
                    return;
                }

                syncSealEditorMeta(editor);
                syncSealLinkNames(editor);
                bumpSealsVersion();
            });

            sealsForm.addEventListener('change', (event) => {
                if (event.target.closest('[data-about-seal-editor]')) {
                    bumpSealsVersion();
                }
            });

            sealsForm.addEventListener('submit', () => {
                relabelSealEditors();
                if (typeof window.syncRichTextEditors === 'function') {
                    window.syncRichTextEditors(sealsForm);
                }
                pendingSealDraftEditors.clear();
                bumpSealsVersion();
            }, true);
        };

        const submitHistoryForm = () => {
            if (!historyForm) {
                return;
            }

            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(historyForm);
            }

            if (typeof historyForm.requestSubmit === 'function') {
                historyForm.requestSubmit();
                return;
            }

            historyForm.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));
        };

        const initAboutImageDropzones = (scope = document) => {
            scope.querySelectorAll('.about-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.aboutDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-about-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-about-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-about-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-about-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-about-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-about-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-about-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-about-clear-image-for="${input.id}"]`);
                const editButton = scope.querySelector(`[data-about-edit-image-for="${input.id}"]`)
                    || document.querySelector(`[data-about-edit-image-for="${input.id}"]`);
                const imageField = input.dataset.aboutImageFieldId
                    ? document.getElementById(input.dataset.aboutImageFieldId)
                    : (input.closest('[data-about-contents-editor]')?.querySelector('[data-about-image-field]') || null);
                const syncImageField = input.dataset.aboutSyncImageFieldId
                    ? document.getElementById(input.dataset.aboutSyncImageFieldId)
                    : null;

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.aboutDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.aboutDefaultSrc || '';
                const syncPreviewPlaceholderState = (isPlaceholder) => {
                    if (!previewEl) {
                        return;
                    }

                    previewEl.classList.toggle('about-cms-image-dropzone-preview--profile-placeholder', isPlaceholder);
                };

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

                    syncPreviewPlaceholderState(false);
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
                    if (event.target.closest('[data-about-clear-image-for]')) {
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
                    if (syncImageField) {
                        syncImageField.value = '';
                    }
                    if (previewEl && defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    syncPreviewPlaceholderState(true);
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncPreviewPlaceholderState((imageField?.value || '').trim() === '');
                syncRemoveState();
            });
        };

        const initAboutCharCounters = (scope = document) => {
            scope.querySelectorAll('[data-about-char-limit]').forEach((field) => {
                if (field.dataset.aboutCharCounterBound === '1') {
                    return;
                }

                const input = field.querySelector('[data-about-char-input]');
                const counter = field.querySelector('[data-about-char-counter]');
                const limit = Number(field.getAttribute('data-about-char-limit') || input?.getAttribute('maxlength') || 0);

                if (!input || !counter || limit <= 0) {
                    return;
                }

                field.dataset.aboutCharCounterBound = '1';
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

        const confirmDeletePlanPriority = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Priority ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Priority Card',
                    message: `Do you want to delete "${promptLabel}" from the Strategic Development Plan?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from the Strategic Development Plan?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deletePlanPriorityByIndex(normalizedIndex);
            if (!deleted) {
                return;
            }

            if (options.submit !== false) {
                submitPlanPrioritiesForm();
            }
        };

        const confirmDeleteOfficialCard = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Official ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Official Card',
                    message: `Do you want to delete "${promptLabel}" from Campus Officials?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from Campus Officials?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteOfficialByIndex(normalizedIndex);
            if (!deleted) {
                return;
            }

            if (options.submit !== false) {
                submitOfficialsForm();
            }
        };

        const confirmDeleteSealCard = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Seal ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Seal',
                    message: `Do you want to delete "${promptLabel}" from Logo and Symbols?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from Logo and Symbols?`);
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteSealByIndex(normalizedIndex);
            if (!deleted) {
                return;
            }

            if (options.submit !== false) {
                submitSealsForm();
            }
        };

        document.querySelectorAll('form.""').forEach((form) => {
            if (form.dataset.aboutRichTextSubmitBound === '1') {
                return;
            }

            form.dataset.aboutRichTextSubmitBound = '1';
            if (form.matches('[data-about-intro-form]')) {
                form.addEventListener('submit', () => {
                    if (typeof window.syncRichTextEditors === 'function') {
                        window.syncRichTextEditors(form);
                    }
                    bumpIntroVersion();
                }, true);
            }

            if (form.matches('[data-about-philosophy-form]')) {
                form.addEventListener('submit', () => {
                    if (typeof window.syncRichTextEditors === 'function') {
                        window.syncRichTextEditors(form);
                    }
                    bumpPhilosophyVersion();
                }, true);
            }

            if (form.matches('[data-about-history-form]')) {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('[data-about-history-date-group]').forEach(syncAboutHistoryDateGroup);
                    if (typeof window.syncRichTextEditors === 'function') {
                        window.syncRichTextEditors(form);
                    }
                    bumpHistoryVersion();
                }, true);
            }

            form.addEventListener('submit', () => {
                if (typeof window.syncRichTextEditors === 'function') {
                    window.syncRichTextEditors(form);
                }
            });
        });

        initOfficialsEditor();
        initSealsEditor();
        initPlanPrioritiesEditor();
        initStrategicGoalsEditor();

        const setActiveServiceEditor = (index = '', scope = document) => {
            const editors = Array.from(scope.querySelectorAll('[data-about-service-editor]'));

            if (!editors.length) {
                return null;
            }

            const normalizedIndex = String(index ?? '').trim();
            let targetEditor = null;

            if (normalizedIndex !== '') {
                targetEditor = editors.find((editor) => editor.getAttribute('data-about-service-index') === normalizedIndex) || null;
            }

            if (!targetEditor) {
                targetEditor = editors[0] || null;
            }

            editors.forEach((editor) => {
                const isActive = editor === targetEditor;
                editor.classList.toggle('is-active', isActive);
                editor.classList.toggle('is-disabled', targetEditor ? !isActive : false);
                editor.hidden = targetEditor ? !isActive : false;
            });

            return targetEditor;
        };

        const submitCitizensCharterForm = () => {
            const panel = document.querySelector('[data-about-editor-panel="citizens-charter"]');
            const form = panel?.querySelector('form');
            if (form) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        };

        const confirmDeleteServiceCard = async (index, label, options = {}) => {
            const normalizedIndex = String(index ?? '').trim();
            if (normalizedIndex === '') {
                return;
            }

            let confirmed = false;
            const promptLabel = label || `Office ${Number(normalizedIndex) + 1}`;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Office Card',
                    message: `Do you want to delete "${promptLabel}" from Citizen's Charter?`,
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(`Do you want to delete "${promptLabel}" from Citizen's Charter?`);
            }

            if (!confirmed) {
                return;
            }

            const servicesList = document.querySelector('[data-about-services-list]');
            const targetEditor = servicesList?.querySelector(`[data-about-service-editor][data-about-service-index="${normalizedIndex}"]`);
            if (targetEditor) {
                targetEditor.remove();
                relabelServiceEditors();
                bumpServicesVersion();

                if (options.submit !== false) {
                    submitCitizensCharterForm();
                }
            }
        };

        const relabelServiceEditors = () => {
            const servicesList = document.querySelector('[data-about-services-list]');
            if (!servicesList) return;
            const editors = servicesList.querySelectorAll('[data-about-service-editor]');
            editors.forEach((editor, newIndex) => {
                editor.setAttribute('data-about-service-index', newIndex);
                const heading = editor.querySelector('[data-about-service-heading]');
                if (heading) {
                    heading.textContent = `Office ${newIndex + 1}`;
                }
                editor.querySelectorAll('input, textarea').forEach((input) => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[services\]\[\d+\]/, `[services][${newIndex}]`));
                    }
                });
            });
        };

        const bumpServicesVersion = () => {
            const servicesForm = document.querySelector('[data-about-editor-panel="citizens-charter"] form');
            const servicesVersionInput = servicesForm?.querySelector('[data-about-services-version]');
            if (servicesVersionInput) {
                servicesVersionInput.value = String(Date.now());
            }
            const frame = document.querySelector('[data-about-preview-frame]');
            if (frame && typeof queueAboutPreviewSettledSync === 'function') {
                queueAboutPreviewSettledSync(frame);
            }
        };

        const addServiceEditor = (data = {}, focus = false) => {
            const servicesList = document.querySelector('[data-about-services-list]');
            const servicesTemplate = document.querySelector('[data-about-service-template]');
            if (!servicesList || !servicesTemplate) return null;

            const newIndex = servicesList.querySelectorAll('[data-about-service-editor]').length;
            const html = document.createElement('div');
            html.appendChild(servicesTemplate.content.cloneNode(true));
            html.innerHTML = html.innerHTML.replace(/__INDEX__/g, newIndex).replace(/__NUMBER__/g, newIndex + 1);
            const editor = html.firstElementChild;
            servicesList.appendChild(editor);

            if (data.title) {
                const titleInput = editor.querySelector('input[name*="[title]"]');
                if (titleInput) titleInput.value = data.title;
            }
            if (data.description) {
                const descInput = editor.querySelector('textarea[name*="[description]"]');
                if (descInput) descInput.value = data.description;
            }
            if (data.link) {
                const linkInput = editor.querySelector('input[name*="[link]"]');
                if (linkInput) linkInput.value = data.link;
            }

            if (focus) {
                setActiveServiceEditor(newIndex, servicesList.closest('[data-about-editor-panel]'));
            }

            return editor;
        };

        const initServicesEditor = () => {
            const servicesList = document.querySelector('[data-about-services-list]');
            const servicesTemplate = document.querySelector('[data-about-service-template]');
            const addBtn = document.querySelector('[data-about-service-add]');
            if (servicesList && servicesTemplate && addBtn) {
                addBtn.addEventListener('click', () => {
                    addServiceEditor({}, false);
                });
            }
        };
        initServicesEditor();

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

        const initialAboutPreviewRoute = getStoredAboutPreviewRoute();
        loadAboutPreviewPage(initialAboutPreviewRoute || 'overview');
        scheduleFitAboutPreviews();
        initAboutImageDropzones(document);
        initAboutCharCounters(document);
        initAboutHistoryDateFields(document);
        if (typeof window.initializeRichTextEditors === 'function') {
            window.initializeRichTextEditors(document);
        }
        bindAboutIntroDirtyTracking();
        bindPhilosophyDirtyTracking();
        bindAboutContentsDirtyTracking();
        bindAboutHistoryDirtyTracking();
        bindCoreValuesDirtyTracking();
        bindOfficialsDirtyTracking();
        bindSealsDirtyTracking();
        setActiveHistoryEditor();
        window.__aboutCmsPreviewEditorReady = true;
    })();
