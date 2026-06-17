<style>
    html,
    body {
        height: auto !important;
        min-height: 0 !important;
    }

    body {
        display: block !important;
    }

    body > .main-content,
    .main-content {
        flex: none !important;
        min-height: 0 !important;
        height: auto !important;
        padding-bottom: 0 !important;
    }

    .main-content > :last-child {
        margin-bottom: 0 !important;
    }

    .reveal,
    .reveal.active {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
        will-change: auto !important;
    }

    .cms-preview-editable {
        --cms-preview-outline-offset: 12px;
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

    .cms-preview-editable > * {
        position: relative;
        z-index: 1;
    }

    .cms-preview-card-actions {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 12;
        display: flex;
        gap: 8px;
    }

    .cms-preview-card-action {
        border: none;
        border-radius: 12px;
        padding: 0 12px;
        min-width: 64px;
        height: 36px;
        background: rgba(127, 17, 19, 0.92);
        color: #fffaf4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
        cursor: pointer;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .cms-preview-card-action-delete {
        background: rgba(92, 0, 0, 0.96);
    }

    .cms-preview-static-shell,
    .cms-preview-editable,
    .contents-card[data-cms-card-index],
    .contents-card[data-cms-card-index] *,
    .iapply-section-card.cms-preview-editable,
    .iapply-section-card.cms-preview-editable * {
        animation: none !important;
        transition: none !important;
    }

    .contents-card[data-cms-card-index]:hover,
    .contents-card[data-cms-card-index]:focus-within {
        transform: none !important;
        filter: none !important;
        box-shadow: inherit !important;
    }

    .contents-card[data-cms-card-index] {
        position: relative;
        isolation: isolate;
        cursor: pointer;
    }

    .iapply-section-card.cms-preview-editable {
        position: relative;
        isolation: isolate;
    }

    .contents-card[data-cms-card-index]::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 10;
        box-sizing: border-box;
        pointer-events: none;
        border: 2px dashed rgba(242, 201, 76, 0.95);
        border-radius: inherit;
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.24),
            0 0 0 4px rgba(242, 201, 76, 0.12);
    }

    .iapply-section-card.cms-preview-editable::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 10;
        box-sizing: border-box;
        pointer-events: none;
        border: 2px dashed rgba(242, 201, 76, 0.95);
        border-radius: inherit;
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.24),
            0 0 0 4px rgba(242, 201, 76, 0.12);
    }

    .contents-card[data-cms-card-index]:hover::after,
    .contents-card[data-cms-card-index]:focus-within::after,
    .iapply-section-card.cms-preview-editable:hover::after,
    .iapply-section-card.cms-preview-editable:focus-within::after {
        border-color: rgba(255, 220, 92, 1);
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.32),
            0 0 0 5px rgba(242, 201, 76, 0.2);
    }

    .contents-card[data-cms-card-index]:hover .contents-card-back,
    .contents-card[data-cms-card-index]:focus-within .contents-card-back {
        opacity: 0 !important;
        transform: translateY(100%) !important;
    }

    .contents-card[data-cms-card-index]:hover .contents-card-overlay-copy,
    .contents-card[data-cms-card-index]:hover .contents-card-action,
    .contents-card[data-cms-card-index]:focus-within .contents-card-overlay-copy,
    .contents-card[data-cms-card-index]:focus-within .contents-card-action {
        opacity: 0 !important;
        transform: translateY(18px) !important;
    }

    .contents-card[data-cms-card-index]:hover .contents-card-front img,
    .contents-card[data-cms-card-index]:focus-within .contents-card-front img {
        transform: none !important;
        filter: none !important;
    }

    .apply-now-btn,
    .uc-calendar-actions a {
        cursor: default !important;
    }

    @media (max-width: 768px) {
        .cms-preview-editable {
            --cms-preview-outline-offset: 8px;
        }

        .cms-preview-editable > [data-cms-boundary]::after {
            border-radius: 16px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let previewHeightFrame = null;

        const getElementBottom = (element) => element.offsetTop + element.offsetHeight;

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
                type: 'cms-academics-preview-height',
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

        const postSection = (section, label) => {
            window.parent?.postMessage({
                type: 'cms-academics-edit',
                section,
                label: label || section,
            }, '*');
        };

        const postCardEdit = (section, label, cardIndex) => {
            window.parent?.postMessage({
                type: 'cms-academics-edit-card',
                section,
                label: label || section,
                cardIndex,
            }, '*');
        };

        const postCardAdd = (section, label) => {
            window.parent?.postMessage({
                type: 'cms-academics-add-card',
                section,
                label: label || section,
            }, '*');
        };

        const postCardDelete = (section, cardIndex) => {
            window.parent?.postMessage({
                type: 'cms-academics-delete-card',
                section,
                cardIndex,
            }, '*');
        };

        document.querySelectorAll('[data-academics-preview-nav]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                window.parent?.postMessage({
                    type: 'cms-academics-preview-route',
                    route: trigger.getAttribute('data-academics-preview-nav') || 'overview',
                }, '*');
            });
        });

        document.querySelectorAll('[data-cms-section]').forEach((target) => {
            const section = target.getAttribute('data-cms-section') || '';
            const label = target.getAttribute('data-cms-section-label') || section;

            target.addEventListener('click', (event) => {
                if (event.target.closest('[data-cms-card-edit]')) {
                    return;
                }

                const boundary = event.target.closest('[data-cms-boundary]');
                if (!boundary || !target.contains(boundary)) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                postSection(section, label);
            });
        });

        document.querySelectorAll('[data-cms-card-edit]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const card = button.closest('[data-cms-card-index]');
                if (!card) {
                    return;
                }

                postCardEdit(
                    card.getAttribute('data-cms-card-section') || '',
                    card.getAttribute('data-cms-card-label') || 'Edit academics card',
                    card.getAttribute('data-cms-card-index') || ''
                );
            });
        });

        document.querySelectorAll('[data-academics-schedule-card]').forEach((card) => {
            const cardIndex = Number(card.getAttribute('data-academics-schedule-card-index'));
            const label = card.getAttribute('data-academics-schedule-card-label') || 'Edit Schedule Item';

            const postScheduleCard = () => {
                postCardEdit('pup-iapply-schedule', label, cardIndex);
            };

            card.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                postScheduleCard();
            });

            card.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                postScheduleCard();
            });
        });

        document.querySelectorAll('[data-cms-card-delete]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const card = button.closest('[data-cms-card-index]');
                if (!card) {
                    return;
                }

                postCardDelete(
                    card.getAttribute('data-cms-card-section') || '',
                    card.getAttribute('data-cms-card-index') || ''
                );
            });
        });

        document.querySelectorAll('[data-cms-add-program-card-trigger]').forEach((trigger) => {
            const sendAddRequest = (event) => {
                event.preventDefault();
                event.stopPropagation();
                postCardAdd(
                    trigger.getAttribute('data-cms-card-section') || '',
                    trigger.getAttribute('data-cms-card-label') || 'Add academics card'
                );
            };

            trigger.addEventListener('click', sendAddRequest);
            trigger.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                sendAddRequest(event);
            });
        });

        document.querySelectorAll('a:not([data-academics-preview-nav])').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
            });
        });

        document.querySelectorAll('img, iframe').forEach((asset) => {
            if (asset.complete) {
                return;
            }

            asset.addEventListener('load', scheduleSettledPreviewHeight, { once: true });
            asset.addEventListener('error', scheduleSettledPreviewHeight, { once: true });
        });

        window.addEventListener('load', scheduleSettledPreviewHeight);
        window.addEventListener('resize', scheduleSettledPreviewHeight);
        scheduleSettledPreviewHeight();
    });
</script>
