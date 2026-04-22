<style>
    :root {
        --cms-modal-viewport-height: 100vh;
    }

    @supports (height: 100dvh) {
        :root {
            --cms-modal-viewport-height: 100dvh;
        }
    }

    html.cms-modal-open,
    body.cms-modal-open {
        overflow: hidden !important;
    }

    [class$="-cms-modal"][hidden] {
        display: none !important;
    }

    [class$="-cms-modal"] {
        position: fixed !important;
        inset: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        min-height: var(--cms-modal-viewport-height);
        padding:
            max(24px, env(safe-area-inset-top))
            max(16px, env(safe-area-inset-right))
            max(24px, env(safe-area-inset-bottom))
            max(16px, env(safe-area-inset-left)) !important;
        box-sizing: border-box;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .profile-edit-modal {
        position: fixed !important;
        inset: 0 !important;
        min-height: var(--cms-modal-viewport-height);
        padding:
            max(22px, env(safe-area-inset-top))
            max(22px, env(safe-area-inset-right))
            max(22px, env(safe-area-inset-bottom))
            max(22px, env(safe-area-inset-left)) !important;
        box-sizing: border-box;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .profile-edit-modal:not(.active) {
        display: none !important;
    }

    .profile-edit-modal.active {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    [class$="-cms-modal-backdrop"] {
        position: fixed !important;
        inset: 0 !important;
        background: rgba(25, 16, 12, 0.54) !important;
    }

    [class$="-cms-modal-dialog"] {
        position: relative;
        inset: auto !important;
        margin: auto !important;
        max-width: min(1080px, calc(100vw - 32px));
        max-height: calc(var(--cms-modal-viewport-height) - 48px);
        isolation: isolate;
        transform: translateZ(0) !important;
        -webkit-transform: translateZ(0) !important;
    }

    .profile-edit-dialog {
        position: relative;
        inset: auto !important;
        margin: auto !important;
        max-width: min(920px, calc(100vw - 32px));
        max-height: calc(var(--cms-modal-viewport-height) - 44px);
        isolation: isolate;
        transform: translateZ(0) !important;
        -webkit-transform: translateZ(0) !important;
    }

    [class$="-cms-preview-frame-shell"] {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-gutter: stable both-edges;
    }

    [class$="-cms-preview-stage"] {
        width: max-content !important;
        min-width: 100% !important;
        justify-content: center !important;
        overflow: visible !important;
    }

    [class$="-cms-preview-canvas"] {
        flex: 0 0 auto !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .cms-page-loading-overlay {
        background: rgba(255, 252, 249, 0.68) !important;
    }

    @supports ((backdrop-filter: blur(6px)) or (-webkit-backdrop-filter: blur(6px))) {
        [class$="-cms-modal-backdrop"] {
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
        }

        .cms-page-loading-overlay {
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }
    }

    @supports not ((backdrop-filter: blur(6px)) or (-webkit-backdrop-filter: blur(6px))) {
        [class$="-cms-modal-backdrop"] {
            background: rgba(25, 16, 12, 0.72) !important;
        }

        .cms-page-loading-overlay {
            background: rgba(255, 252, 249, 0.9) !important;
        }
    }

    @media (max-width: 768px) {
        [class$="-cms-modal"] {
            align-items: flex-start !important;
            padding:
                max(12px, env(safe-area-inset-top))
                max(10px, env(safe-area-inset-right))
                max(12px, env(safe-area-inset-bottom))
                max(10px, env(safe-area-inset-left)) !important;
        }

        [class$="-cms-modal-dialog"] {
            margin: 0 auto !important;
            max-width: calc(100vw - 20px);
            max-height: calc(var(--cms-modal-viewport-height) - 24px);
        }

        .profile-edit-modal {
            padding:
                max(12px, env(safe-area-inset-top))
                max(10px, env(safe-area-inset-right))
                max(12px, env(safe-area-inset-bottom))
                max(10px, env(safe-area-inset-left)) !important;
        }

        .profile-edit-dialog {
            max-width: calc(100vw - 20px);
            max-height: calc(var(--cms-modal-viewport-height) - 24px);
        }
    }

    .cms-image-dropzone-hero {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 14px !important;
        width: 100% !important;
        padding: 16px !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-preview-column"] {
        display: block !important;
        width: 100% !important;
        min-width: 0 !important;
        min-height: 0 !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-media"] {
        position: relative !important;
        display: block !important;
        width: 100% !important;
        min-height: clamp(160px, 20vw, 220px) !important;
        aspect-ratio: 16 / 4 !important;
        overflow: hidden !important;
        border-radius: 20px !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-preview"] {
        position: absolute !important;
        inset: 0 !important;
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        object-fit: cover !important;
        object-position: top center !important;
        border-radius: 20px !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload"] {
        display: grid !important;
        grid-template-columns: auto minmax(0, 1fr) auto !important;
        grid-template-rows: auto auto auto !important;
        align-items: center !important;
        column-gap: 16px !important;
        row-gap: 4px !important;
        width: 100% !important;
        min-height: auto !important;
        padding: 16px 18px !important;
        border-radius: 20px !important;
        text-align: left !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-icon"] {
        grid-column: 1 !important;
        grid-row: 1 / span 3 !important;
        width: 64px !important;
        height: 64px !important;
        margin-bottom: 0 !important;
        font-size: 1.6rem !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload-title"] {
        grid-column: 2 !important;
        grid-row: 1 !important;
        display: block !important;
        font-size: 1rem !important;
        line-height: 1.35 !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload-copy"] {
        grid-column: 2 !important;
        grid-row: 2 !important;
        display: block !important;
        font-size: 0.85rem !important;
        line-height: 1.55 !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload-button"] {
        grid-column: 3 !important;
        grid-row: 1 / span 3 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 46px !important;
        margin-top: 0 !important;
        align-self: center !important;
        white-space: nowrap !important;
    }

    .cms-image-dropzone-hero [class$="-cms-image-dropzone-file"] {
        grid-column: 2 !important;
        grid-row: 3 !important;
        display: block !important;
        font-size: 0.8rem !important;
        line-height: 1.45 !important;
        word-break: break-word !important;
    }

    @media (max-width: 640px) {
        .cms-image-dropzone-hero {
            padding: 14px !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-media"] {
            min-height: 150px !important;
            aspect-ratio: 16 / 5 !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload"] {
            grid-template-columns: auto 1fr !important;
            grid-template-rows: auto auto auto auto !important;
            padding: 14px !important;
            row-gap: 6px !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-icon"] {
            grid-row: 1 / span 2 !important;
            width: 56px !important;
            height: 56px !important;
            font-size: 1.4rem !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload-title"] {
            grid-column: 2 !important;
            grid-row: 1 !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload-copy"] {
            grid-column: 2 !important;
            grid-row: 2 !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-upload-button"] {
            grid-column: 1 / -1 !important;
            grid-row: 3 !important;
            justify-self: start !important;
        }

        .cms-image-dropzone-hero [class$="-cms-image-dropzone-file"] {
            grid-column: 1 / -1 !important;
            grid-row: 4 !important;
        }
    }
</style>

<script>
    (() => {
        if (window.__cmsPreviewCompatReady) {
            return;
        }

        const PREVIEW_FALLBACK_HTML = '<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>';

        function normalizePreviewHtml(html) {
            if (typeof html !== 'string') {
                return PREVIEW_FALLBACK_HTML;
            }

            const trimmed = html.trim();
            return trimmed !== '' ? html : PREVIEW_FALLBACK_HTML;
        }

        function scheduleObjectUrlRevoke(url) {
            if (!url || typeof URL?.revokeObjectURL !== 'function') {
                return;
            }

            window.setTimeout(() => {
                try {
                    URL.revokeObjectURL(url);
                } catch (_) {
                }
            }, 1000);
        }

        function clearFrameObjectUrl(frame) {
            if (!frame || !frame.__cmsPreviewObjectUrl) {
                return;
            }

            const oldUrl = frame.__cmsPreviewObjectUrl;
            frame.__cmsPreviewObjectUrl = null;
            scheduleObjectUrlRevoke(oldUrl);
        }

        function applyCmsPreviewFrameContent(frame, html) {
            if (!(frame instanceof HTMLIFrameElement)) {
                return false;
            }

            const markup = normalizePreviewHtml(html);
            frame.__cmsPreviewMarkup = markup;
            frame.__cmsPreviewHasContent = markup.trim() !== '';

            clearFrameObjectUrl(frame);

            if (typeof Blob === 'function' && typeof URL?.createObjectURL === 'function') {
                try {
                    const objectUrl = URL.createObjectURL(new Blob([markup], { type: 'text/html;charset=utf-8' }));
                    frame.__cmsPreviewObjectUrl = objectUrl;
                    frame.removeAttribute('srcdoc');
                    frame.src = objectUrl;
                    return true;
                } catch (_) {
                }
            }

            try {
                frame.srcdoc = markup;
                return true;
            } catch (_) {
                frame.src = 'about:blank';
                return false;
            }
        }

        function hasCmsPreviewFrameContent(frame) {
            return !!(frame && frame.__cmsPreviewHasContent);
        }

        window.applyCmsPreviewFrameContent = applyCmsPreviewFrameContent;
        window.hasCmsPreviewFrameContent = hasCmsPreviewFrameContent;

        function getViewportHeight() {
            const visualHeight = Number(window.visualViewport?.height || 0);
            const viewportHeight = Number(window.innerHeight || document.documentElement?.clientHeight || 0);

            return Math.max(0, Math.round(visualHeight > 0 ? visualHeight : viewportHeight));
        }

        function syncCmsViewportHeight() {
            const height = getViewportHeight();

            if (height > 0) {
                document.documentElement.style.setProperty('--cms-modal-viewport-height', `${height}px`);
            }
        }

        function getManagedModals() {
            return Array.from(document.querySelectorAll('[class$="-cms-modal"], .profile-edit-modal'));
        }

        function isManagedModalVisible(modal) {
            if (!(modal instanceof HTMLElement)) {
                return false;
            }

            if (modal.matches('.profile-edit-modal')) {
                return modal.classList.contains('active');
            }

            return !modal.hasAttribute('hidden');
        }

        function syncManagedModalState() {
            const anyVisible = getManagedModals().some((modal) => isManagedModalVisible(modal));

            document.documentElement.classList.toggle('cms-modal-open', anyVisible);
            document.body.classList.toggle('cms-modal-open', anyVisible);
        }

        function bindManagedModalState(modal) {
            if (!(modal instanceof HTMLElement) || modal.__cmsCompatObserverBound === true) {
                return;
            }

            const observer = new MutationObserver(() => {
                syncManagedModalState();
            });

            observer.observe(modal, {
                attributes: true,
                attributeFilter: ['hidden', 'class', 'style'],
            });

            modal.__cmsCompatObserverBound = true;
            modal.__cmsCompatObserver = observer;
        }

        function moveManagedModalsToBody() {
            if (!document.body) {
                return;
            }

            getManagedModals().forEach((modal) => {
                bindManagedModalState(modal);

                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            });

            syncManagedModalState();
        }

        function measureCmsPreviewFrameHeight(frame, options = {}) {
            if (!(frame instanceof HTMLIFrameElement)) {
                return 0;
            }

            const doc = frame.contentDocument;
            if (!doc) {
                return 0;
            }

            const html = doc.documentElement;
            const body = doc.body;
            const scopeSelector = typeof options.scopeSelector === 'string' && options.scopeSelector.trim() !== ''
                ? options.scopeSelector
                : '.main-content';
            const scope = doc.querySelector(scopeSelector) || body || html;

            if (!(scope instanceof HTMLElement) || !html || !body) {
                return 0;
            }

            const getElementBottom = (element) => {
                if (!(element instanceof HTMLElement)) {
                    return 0;
                }

                const styles = doc.defaultView?.getComputedStyle(element);
                const marginBottom = Number.parseFloat(styles?.marginBottom || '') || 0;

                return element.offsetTop + element.offsetHeight + marginBottom;
            };

            const isMeasuredElement = (element) => {
                if (!(element instanceof HTMLElement)) {
                    return false;
                }

                const styles = doc.defaultView?.getComputedStyle(element);
                return styles?.display !== 'none'
                    && styles?.visibility !== 'hidden'
                    && styles?.position !== 'fixed';
            };

            const childBottom = Array.from(scope.children)
                .filter((element) => isMeasuredElement(element))
                .reduce((maxBottom, element) => Math.max(maxBottom, getElementBottom(element)), 0);

            return Math.max(
                1,
                body.scrollHeight,
                body.offsetHeight,
                body.clientHeight,
                html.scrollHeight,
                html.offsetHeight,
                html.clientHeight,
                scope.scrollHeight,
                scope.offsetHeight,
                scope.clientHeight,
                childBottom
            );
        }

        function bindCmsPreviewScrollBridge(frame, cleanups = []) {
            if (!(frame instanceof HTMLIFrameElement)) {
                return;
            }

            const doc = frame.contentDocument;
            if (!doc) {
                return;
            }

            const handleWheel = (event) => {
                if (!event || event.defaultPrevented || event.ctrlKey || event.metaKey) {
                    return;
                }

                const deltaX = Number(event.deltaX || 0);
                const deltaY = Number(event.deltaY || 0);

                if (Math.abs(deltaX) < 1 && Math.abs(deltaY) < 1) {
                    return;
                }

                window.scrollBy({
                    left: deltaX,
                    top: deltaY,
                    behavior: 'auto',
                });

                event.preventDefault();
            };

            doc.addEventListener('wheel', handleWheel, {
                passive: false,
                capture: true,
            });

            cleanups.push(() => {
                doc.removeEventListener('wheel', handleWheel, true);
            });
        }

        function initializeCmsCompatLayout() {
            syncCmsViewportHeight();
            moveManagedModalsToBody();
        }

        window.measureCmsPreviewFrameHeight = measureCmsPreviewFrameHeight;
        window.bindCmsPreviewScrollBridge = bindCmsPreviewScrollBridge;

        window.addEventListener('beforeunload', () => {
            document.querySelectorAll('iframe[data-home-preview-frame], iframe[data-about-preview-frame], iframe[data-academics-preview-frame], iframe[data-students-preview-frame], iframe[data-research-preview-frame], iframe[data-events-preview-frame]')
                .forEach((frame) => clearFrameObjectUrl(frame));
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCmsCompatLayout, { once: true });
        } else {
            initializeCmsCompatLayout();
        }

        window.addEventListener('load', initializeCmsCompatLayout);
        window.addEventListener('pageshow', initializeCmsCompatLayout);
        window.addEventListener('resize', syncCmsViewportHeight);
        window.addEventListener('orientationchange', syncCmsViewportHeight);
        window.visualViewport?.addEventListener('resize', syncCmsViewportHeight);
        window.visualViewport?.addEventListener('scroll', syncCmsViewportHeight);

        window.__cmsPreviewCompatReady = true;
    })();
</script>
