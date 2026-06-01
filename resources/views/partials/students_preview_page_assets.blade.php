<style>
    html,
    body {
        overflow: hidden !important;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
    }

    .main-content.students-review-page {
        overflow: hidden !important;
    }

    .reveal {
        opacity: 1 !important;
        transform: none !important;
    }

    .hero-shell,
    .student-page-intro.cms-preview-editable,
    .student-page-section.cms-preview-editable {
        --cms-preview-outline-offset: 12px;
        --cms-preview-chip-top-offset: 50%;
        --cms-preview-chip-right-offset: 12px;
        width: 100% !important;
        max-width: 100% !important;
        left: auto !important;
        right: auto !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        transform: none !important;
    }

    .cms-preview-editable {
        position: relative;
        cursor: pointer;
        isolation: isolate;
        overflow: visible !important;
    }

    .cms-preview-editable > [data-cms-boundary] {
        position: relative;
        display: block;
        width: auto;
        max-width: none;
        min-width: 0;
        margin: var(--cms-preview-outline-offset);
        box-sizing: border-box;
        overflow: visible !important;
    }

    .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
        width: calc(100% - (var(--cms-preview-outline-offset) * 2));
    }

    .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
        width: 100%;
        margin: 0;
    }

    .cms-preview-editable > [data-cms-boundary]::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        box-sizing: border-box;
        pointer-events: none;
        border: 2px dashed rgba(242, 201, 76, 0.95);
        border-radius: 24px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22);
    }

    .hero-shell.cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full::after {
        inset: var(--cms-preview-outline-offset);
    }

    .cms-preview-editable > * {
        position: relative;
        z-index: 1;
    }

    .cms-preview-chip {
        position: absolute;
        top: var(--cms-preview-chip-top-offset);
        right: calc(var(--cms-preview-chip-right-offset) + var(--cms-preview-outline-offset));
        transform: translateY(-50%);
        z-index: 9;
        border: none;
        border-radius: 12px;
        width: 44px;
        min-width: 44px;
        height: 44px;
        padding: 0;
        background: rgba(127, 17, 19, 0.96);
        color: #fffaf4;
        display: none !important;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
    }

    .cms-preview-chip svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }

    @media (max-width: 768px) {
        .hero-shell,
        .student-page-intro.cms-preview-editable,
        .student-page-section.cms-preview-editable {
            --cms-preview-outline-offset: 8px;
            --cms-preview-chip-top-offset: 50%;
            --cms-preview-chip-right-offset: 8px;
        }

        .cms-preview-chip {
            width: 40px;
            min-width: 40px;
            height: 40px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let previewHeightFrame = null;

        const getElementBottom = (element) => {
            if (!(element instanceof HTMLElement)) {
                return 0;
            }

            const styles = window.getComputedStyle(element);
            const marginBottom = Number.parseFloat(styles.marginBottom || '') || 0;

            return element.offsetTop + element.offsetHeight + marginBottom;
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

        const postPreviewHeight = () => {
            const main = document.querySelector('.main-content');
            const scope = main instanceof HTMLElement ? main : document.body;
            const visibleElements = Array.from(scope.children)
                .filter((node) => isMeasuredElement(node));
            const childBottom = visibleElements.reduce((maxBottom, node) => {
                return Math.max(maxBottom, getElementBottom(node));
            }, 0);
            const html = document.documentElement;
            const body = document.body;
            const height = Math.max(
                scope.offsetHeight,
                scope.scrollHeight,
                scope.clientHeight,
                body?.offsetHeight || 0,
                body?.scrollHeight || 0,
                body?.clientHeight || 0,
                html?.offsetHeight || 0,
                html?.scrollHeight || 0,
                html?.clientHeight || 0,
                childBottom
            );

            window.parent?.postMessage({
                type: 'cms-students-preview-height',
                height: Math.max(1, Math.ceil(height)),
            }, '*');
        };

        const schedulePreviewHeight = () => {
            if (previewHeightFrame !== null) {
                window.cancelAnimationFrame(previewHeightFrame);
            }

            previewHeightFrame = window.requestAnimationFrame(() => {
                postPreviewHeight();
                previewHeightFrame = null;
            });
        };

        const scheduleSettledPreviewHeight = () => {
            schedulePreviewHeight();
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(schedulePreviewHeight, delay);
            });
        };

        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(() => {
                schedulePreviewHeight();
            });

            if (document.body) {
                observer.observe(document.body);
            }

            if (document.documentElement) {
                observer.observe(document.documentElement);
            }
        }

        document.querySelectorAll('img').forEach((image) => {
            if (image.complete) {
                return;
            }

            image.addEventListener('load', scheduleSettledPreviewHeight, { once: true });
            image.addEventListener('error', scheduleSettledPreviewHeight, { once: true });
        });

        window.addEventListener('load', scheduleSettledPreviewHeight);
        window.addEventListener('pageshow', scheduleSettledPreviewHeight);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleSettledPreviewHeight();
            }
        });

        scheduleSettledPreviewHeight();
    });
</script>
