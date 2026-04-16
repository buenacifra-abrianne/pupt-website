<style>
    [class$="-cms-modal"][hidden] {
        display: none !important;
    }

    [class$="-cms-modal"] {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow-y: auto !important;
        min-height: 100vh;
        padding: 24px 16px !important;
        box-sizing: border-box;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    [class$="-cms-modal-backdrop"] {
        position: fixed !important;
        inset: 0 !important;
        background: rgba(25, 16, 12, 0.54) !important;
    }

    [class$="-cms-modal-dialog"] {
        position: relative;
        margin: auto !important;
        max-width: min(1080px, calc(100vw - 32px));
        max-height: calc(100vh - 48px);
        isolation: isolate;
        transform: translateZ(0) !important;
        -webkit-transform: translateZ(0) !important;
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
            padding: 12px 10px !important;
        }

        [class$="-cms-modal-dialog"] {
            margin: 0 auto !important;
            max-width: calc(100vw - 20px);
            max-height: calc(100vh - 24px);
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

        window.addEventListener('beforeunload', () => {
            document.querySelectorAll('iframe[data-home-preview-frame], iframe[data-about-preview-frame], iframe[data-academics-preview-frame], iframe[data-students-preview-frame], iframe[data-research-preview-frame], iframe[data-events-preview-frame]')
                .forEach((frame) => clearFrameObjectUrl(frame));
        });

        window.__cmsPreviewCompatReady = true;
    })();
</script>
