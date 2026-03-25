<style>
.rich-editor {
    border: 1px solid #d8d3d1;
    border-radius: 12px;
    background: #fcfbfb;
    overflow: visible;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.rich-editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0;
    padding: 10px 12px;
    border-bottom: 1px solid #e7e1de;
    background: #f7f3f2;
    position: relative;
    z-index: 2;
}

.rich-editor-group {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.rich-editor-separator {
    width: 1px;
    height: 26px;
    background: #ddd3cf;
    margin: 0 10px;
}

.rich-editor-btn {
    border: 0;
    background: transparent;
    color: #4b5563;
    border-radius: 8px;
    width: 36px;
    min-width: 36px;
    height: 36px;
    padding: 0;
    font-size: 15px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}

.rich-editor-btn:hover {
    background: #ece5e2;
    color: #1f2937;
}

.rich-editor-btn-icon {
    width: 36px;
    min-width: 36px;
    height: 36px;
}

.rich-editor-fontsize-wrap,
.rich-editor-color-wrap {
    position: relative;
    display: inline-flex;
}

.rich-editor-fontsize-trigger {
    width: auto;
    min-width: 72px;
    padding: 0 10px;
    gap: 8px;
}

.rich-editor-fontsize-trigger i {
    font-size: 12px;
}

.rich-editor-fontsize-popover,
.rich-editor-color-popover {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    background: #fff;
    border: 1px solid #ddd3cf;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16);
    z-index: 30;
}

.rich-editor-fontsize-popover {
    width: 120px;
    padding: 8px;
}

.rich-editor-color-popover {
    width: 250px;
    padding: 12px;
}

.rich-editor-fontsize-popover[hidden],
.rich-editor-color-popover[hidden] {
    display: none;
}

.rich-editor-fontsize-title,
.rich-editor-color-popover-title {
    font-size: 13px;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 10px;
}

.rich-editor-fontsize-options {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.rich-editor-fontsize-option {
    border: 1px solid #d6cdcd;
    background: #fff;
    color: #4b5563;
    border-radius: 8px;
    height: 34px;
    padding: 0 10px;
    font-size: 14px;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
}

.rich-editor-fontsize-option:hover,
.rich-editor-fontsize-option.is-active {
    background: #f7f3f2;
    color: #1f2937;
}

.rich-editor-color-preview-wrap {
    margin-bottom: 12px;
}

.rich-editor-color-preview-label {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 6px;
}

.rich-editor-color-preview {
    min-height: 52px;
    border: 1px solid #e5dfdc;
    border-radius: 10px;
    background: #faf8f7;
    padding: 12px;
    color: #000;
    font-size: 14px;
    line-height: 1.4;
    word-break: break-word;
}

.rich-editor-color-section {
    margin-bottom: 12px;
}

.rich-editor-color-section-title {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 6px;
}

.rich-editor-color-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 6px;
}

.js-standard-color-grid {
    grid-template-columns: repeat(10, 1fr);
}

.rich-editor-swatch {
    border: 1px solid #d6cdcd;
    border-radius: 6px;
    width: 100%;
    aspect-ratio: 1 / 1;
    cursor: pointer;
    padding: 0;
}

.rich-editor-swatch.is-selected {
    outline: 2px solid #111827;
    outline-offset: 1px;
}

.rich-editor-color-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.rich-editor-color-link {
    border: 1px solid #d6cdcd;
    background: #fff;
    color: #4b5563;
    border-radius: 8px;
    height: 32px;
    padding: 0 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.rich-editor-color-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.rich-editor-color-cancel,
.rich-editor-color-confirm {
    border: 1px solid #d6cdcd;
    border-radius: 8px;
    height: 34px;
    padding: 0 14px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.rich-editor-color-cancel {
    background: #fff;
    color: #4b5563;
}

.rich-editor-color-confirm {
    background: #7c0a02;
    border-color: #7c0a02;
    color: #fff;
}

.rich-editor-native-color-anchor {
    position: absolute;
    right: 12px;
    bottom: 12px;
    width: 36px;
    height: 36px;
    opacity: 0.01;
    pointer-events: none;
    border: 0;
    padding: 0;
    margin: 0;
}

.rich-editor-btn:focus,
.rich-editor-fontsize-option:focus,
.rich-editor-swatch:focus,
.rich-editor-color-link:focus,
.rich-editor-color-cancel:focus,
.rich-editor-color-confirm:focus,
.rich-editor-surface:focus {
    outline: none;
}

input[name="title"] {
    font-weight: 700;
}

.rich-editor-surface {
    min-height: 154px;
    padding: 16px 14px;
    outline: none;
    line-height: 1.55;
    background: #fff;
    font-size: 14px;
}

.rich-editor-surface p,
.rich-editor-surface div {
    margin: 0 0 0.45rem;
}

.rich-editor-surface p:last-child,
.rich-editor-surface div:last-child {
    margin-bottom: 0;
}

.rich-editor-surface ul,
.rich-editor-surface ol {
    margin: 0.35rem 0 0.6rem 0;
    padding-left: 1.65rem;
    list-style-position: outside;
}

.rich-editor-surface li {
    margin: 0.15rem 0;
    padding-left: 0.2rem;
}

.rich-editor-surface li > p,
.rich-editor-surface li > div {
    margin: 0;
}

.rich-editor-surface ul ul,
.rich-editor-surface ul ol,
.rich-editor-surface ol ul,
.rich-editor-surface ol ol {
    margin-top: 0.2rem;
    margin-bottom: 0.2rem;
}

.rich-editor-surface:empty::before {
    content: attr(data-placeholder);
    color: #94a3b8;
}

.rich-editor-surface blockquote {
    margin: 0.6rem 0;
    padding-left: 0.9rem;
    border-left: 3px solid #d0c4bf;
    color: #475569;
}

.rich-editor-footer {
    display: flex;
    justify-content: flex-end;
    padding: 8px 12px;
    border-top: 1px solid #ece5e2;
    background: #f7f3f2;
}

.rich-editor-count {
    font-size: 12px;
    color: #b3afb4;
    font-weight: 600;
}

.announcement-description.rich-text-content,
.announcement-text.rich-text-content,
.news-rich-content {
    white-space: normal;
    line-height: 1.6;
}

.announcement-description.rich-text-content p,
.announcement-text.rich-text-content p,
.news-rich-content p {
    margin: 0 0 0.7rem;
}

.announcement-description.rich-text-content p:last-child,
.announcement-text.rich-text-content p:last-child,
.news-rich-content p:last-child {
    margin-bottom: 0;
}

.announcement-description.rich-text-content ul,
.announcement-description.rich-text-content ol,
.announcement-text.rich-text-content ul,
.announcement-text.rich-text-content ol,
.news-rich-content ul,
.news-rich-content ol {
    padding-left: 1.25rem;
    margin: 0.4rem 0 0.8rem;
}

.rich-editor-surface * {
    line-height: inherit;
}

.rich-editor-surface span[style*="font-size"] {
    line-height: 1;
    vertical-align: baseline;
}
</style>

<script>
(() => {
    if (window.__cmsRichTextReady) {
        return;
    }

    function normalizeEditorHtml(html) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html || '';

        if (!wrapper.textContent.trim()) {
            return '';
        }

        return wrapper.innerHTML.trim();
    }

    function syncEditor(root) {
        const input = root.querySelector('.rich-editor-input');
        const surface = root.querySelector('.rich-editor-surface');
        const counter = root.querySelector('.rich-editor-count');

        if (!input || !surface) {
            return;
        }

        input.value = normalizeEditorHtml(surface.innerHTML);

        if (counter) {
            counter.textContent = `${surface.textContent.trim().length} characters`;
        }
    }

    function handleCommand(root, button) {
        const command = button.dataset.command;
        const value = button.dataset.value || null;
        const surface = root.querySelector('.rich-editor-surface');

        if (!command || !surface) {
            return;
        }

        surface.focus();
        document.execCommand(command, false, value);
        syncEditor(root);
    }

    function getSelectionRangeInside(surface) {
        const selection = window.getSelection();

        if (!selection || selection.rangeCount === 0) {
            return null;
        }

        const range = selection.getRangeAt(0);
        const commonAncestor = range.commonAncestorContainer;
        const container = commonAncestor.nodeType === Node.ELEMENT_NODE
            ? commonAncestor
            : commonAncestor.parentNode;

        if (!surface.contains(container)) {
            return null;
        }

        return range;
    }

    function cloneRange(range) {
        if (!range) {
            return null;
        }

        try {
            return range.cloneRange();
        } catch (error) {
            return null;
        }
    }

    function restoreSelection(savedRange) {
        if (!savedRange) {
            return false;
        }

        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(savedRange);
        return true;
    }

    function applyInlineStyleToSelection(surface, styles, savedRange = null) {
        const range = savedRange || getSelectionRangeInside(surface);

        if (!range || range.collapsed) {
            return;
        }

        const extracted = range.extractContents();

        const temp = document.createElement('div');
        temp.appendChild(extracted);

        temp.querySelectorAll('span').forEach((span) => {
            if (styles.fontSize) {
                span.style.fontSize = '';
                span.style.lineHeight = '';
                span.style.verticalAlign = '';
            }

            if (styles.color) {
                span.style.color = '';
            }

            if (!span.getAttribute('style') || span.getAttribute('style').trim() === '') {
                span.replaceWith(...span.childNodes);
            }
        });

        const wrapper = document.createElement('span');

        Object.entries(styles).forEach(([key, value]) => {
            wrapper.style[key] = value;
        });

        if (styles.fontSize) {
            wrapper.style.lineHeight = '1';
            wrapper.style.verticalAlign = 'baseline';
            wrapper.style.display = 'inline';
        }

        while (temp.firstChild) {
            wrapper.appendChild(temp.firstChild);
        }

        range.insertNode(wrapper);

        const selection = window.getSelection();
        const newRange = document.createRange();
        newRange.selectNodeContents(wrapper);
        selection.removeAllRanges();
        selection.addRange(newRange);
    }

    function applyFontSize(root, size) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface || !size) {
            return;
        }

        surface.focus();
        applyInlineStyleToSelection(surface, { fontSize: size });
        normalizeStyledSpans(root);
        syncEditor(root);
    }

    function applyTextColor(root, color, savedRange = null) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface || !color) {
            return;
        }

        surface.focus();

        if (savedRange) {
            restoreSelection(savedRange);
        }

        applyInlineStyleToSelection(
            surface,
            { color: color },
            savedRange ? getSelectionRangeInside(surface) : null
        );

        normalizeStyledSpans(root);
        syncEditor(root);
    }

    function clearTextColor(root, savedRange = null) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface) {
            return;
        }

        surface.focus();

        if (savedRange) {
            restoreSelection(savedRange);
        }

        applyInlineStyleToSelection(
            surface,
            { color: 'inherit' },
            savedRange ? getSelectionRangeInside(surface) : null
        );

        normalizeStyledSpans(root);
        syncEditor(root);
    }

    function updateColorTriggerAppearance(trigger, color) {
        if (!trigger) {
            return;
        }

        trigger.style.color = color || '#4b5563';
    }

    function selectionInsideList(surface) {
        const selection = window.getSelection();

        if (!selection || selection.rangeCount === 0) {
            return false;
        }

        let node = selection.anchorNode;

        while (node && node !== surface) {
            if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'LI') {
                return true;
            }
            node = node.parentNode;
        }

        return false;
    }

    function insertIndentSpaces() {
        document.execCommand('insertHTML', false, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    }

    function removeLeadingIndent(selection) {
        if (!selection || selection.rangeCount === 0) {
            return false;
        }

        const range = selection.getRangeAt(0);
        let node = range.startContainer;

        if (node.nodeType !== Node.TEXT_NODE) {
            node = node.childNodes[range.startOffset - 1] || node.firstChild;
        }

        if (!node || node.nodeType !== Node.TEXT_NODE) {
            return false;
        }

        const value = node.textContent || '';
        const normalized = value.replace(/\u00a0/g, ' ');
        if (!/ {4,8}$/.test(normalized.slice(0, range.startOffset))) {
            return false;
        }

        const updated = value.slice(0, Math.max(0, range.startOffset - 8)) + value.slice(range.startOffset);
        node.textContent = updated;

        const nextOffset = Math.max(0, range.startOffset - 8);
        const newRange = document.createRange();
        newRange.setStart(node, Math.min(nextOffset, node.textContent.length));
        newRange.collapse(true);
        selection.removeAllRanges();
        selection.addRange(newRange);

        return true;
    }

    function getSelectionText(surface, savedRange = null) {
        if (savedRange) {
            return savedRange.toString().trim();
        }

        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            return '';
        }

        const range = selection.getRangeAt(0);
        const commonAncestor = range.commonAncestorContainer;
        const container = commonAncestor.nodeType === Node.ELEMENT_NODE
            ? commonAncestor
            : commonAncestor.parentNode;

        if (!surface.contains(container)) {
            return '';
        }

        return selection.toString().trim();
    }

    function updateColorPreview(root) {
        const surface = root.querySelector('.rich-editor-surface');
        const preview = root.querySelector('.js-text-color-preview');
        const draftColor = root.__richEditorDraftColor || '#000000';
        const savedRange = root.__richEditorSavedRange || null;
        const currentSize = root.__richEditorCurrentFontSize || '14px';

        if (!surface || !preview) {
            return;
        }

        const selectedText = getSelectionText(surface, savedRange);
        preview.textContent = selectedText || 'Sample text';
        preview.style.color = draftColor === '__inherit__' ? 'inherit' : draftColor;
        preview.style.fontSize = currentSize;
    }

    function markSelectedSwatch(root, color) {
        root.querySelectorAll('.rich-editor-swatch').forEach((swatch) => {
            swatch.classList.toggle('is-selected', swatch.dataset.color === color);
        });
    }

    function addRecentColor(root, color) {
        if (!color || color === '__inherit__') {
            return;
        }

        const current = Array.isArray(root.__richEditorRecentColors) ? root.__richEditorRecentColors : [];
        const updated = [color, ...current.filter((item) => item !== color)].slice(0, 8);
        root.__richEditorRecentColors = updated;

        const section = root.querySelector('.js-recent-colors-section');
        const grid = root.querySelector('.js-recent-color-grid');

        if (!section || !grid) {
            return;
        }

        grid.innerHTML = '';
        updated.forEach((recentColor) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rich-editor-swatch';
            btn.dataset.color = recentColor;
            btn.style.background = recentColor;
            btn.addEventListener('click', () => {
                setDraftColor(root, recentColor);
            });
            grid.appendChild(btn);
        });

        section.hidden = updated.length === 0;
    }

    function setDraftColor(root, color) {
        root.__richEditorDraftColor = color;
        markSelectedSwatch(root, color);
        updateColorPreview(root);
    }

    function setFontSizeState(root, size) {
        root.__richEditorCurrentFontSize = size;
        const label = root.querySelector('.js-font-size-label');

        if (label) {
            label.textContent = size;
        }

        root.querySelectorAll('.js-font-size-option').forEach((option) => {
            option.classList.toggle('is-active', option.dataset.size === size);
        });

        updateColorPreview(root);
    }

    function openFontSizePopover(root) {
        const popover = root.querySelector('.js-font-size-popover');
        if (!popover) {
            return;
        }

        document.querySelectorAll('.js-font-size-popover').forEach((item) => {
            item.hidden = true;
        });
        document.querySelectorAll('.js-text-color-popover').forEach((item) => {
            item.hidden = true;
        });

        popover.hidden = false;
    }

    function closeFontSizePopover(root) {
        const popover = root.querySelector('.js-font-size-popover');
        if (popover) {
            popover.hidden = true;
        }
    }

    function openColorPopover(root) {
        const popover = root.querySelector('.js-text-color-popover');
        const surface = root.querySelector('.rich-editor-surface');

        if (!popover || !surface) {
            return;
        }

        document.querySelectorAll('.js-text-color-popover').forEach((item) => {
            item.hidden = true;
        });
        document.querySelectorAll('.js-font-size-popover').forEach((item) => {
            item.hidden = true;
        });

        root.__richEditorSavedRange = cloneRange(getSelectionRangeInside(surface));
        root.__richEditorDraftColor = root.__richEditorDraftColor || '#000000';

        popover.hidden = false;
        updateColorPreview(root);
    }

    function closeColorPopover(root) {
        const popover = root.querySelector('.js-text-color-popover');
        if (popover) {
            popover.hidden = true;
        }
    }
    function normalizeStyledSpans(root) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface) {
            return;
        }

        surface.querySelectorAll('span').forEach((span) => {
            if (span.style.fontSize) {
                span.style.lineHeight = '1';
                span.style.verticalAlign = 'baseline';
                span.style.display = 'inline';
            }

            const style = span.getAttribute('style');
            if (!style || style.trim() === '') {
                span.replaceWith(...span.childNodes);
            }
        });
    }

    function attachEditor(root) {
        if (!root || root.dataset.richEditorReady === 'true') {
            return;
        }

        const input = root.querySelector('.rich-editor-input');
        const surface = root.querySelector('.rich-editor-surface');
        const fontSizeTrigger = root.querySelector('.js-font-size-trigger');
        const fontSizePopover = root.querySelector('.js-font-size-popover');
        const textColorTrigger = root.querySelector('.js-text-color-trigger');
        const textColorConfirm = root.querySelector('.js-text-color-confirm');
        const textColorCancel = root.querySelector('.js-text-color-cancel');
        const textColorAuto = root.querySelector('.js-text-color-auto');
        const textColorNone = root.querySelector('.js-text-color-none');
        const textColorMore = root.querySelector('.js-text-color-more');
        const textColorNative = root.querySelector('.js-text-color-native');
        const colorPopover = root.querySelector('.js-text-color-popover');

        if (!input || !surface) {
            return;
        }

        root.__richEditorRecentColors = [];
        root.__richEditorDraftColor = '#000000';
        root.__richEditorSavedRange = null;
        root.__richEditorCurrentFontSize = '14px';

        surface.innerHTML = input.value || '';

        root.querySelectorAll('.rich-editor-btn').forEach((button) => {
            if (!button.classList.contains('js-font-size-trigger') && !button.classList.contains('js-text-color-trigger')) {
                button.addEventListener('click', () => handleCommand(root, button));
            }
        });

        if (fontSizeTrigger) {
            fontSizeTrigger.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                if (fontSizePopover && !fontSizePopover.hidden) {
                    closeFontSizePopover(root);
                } else {
                    openFontSizePopover(root);
                }
            });
        }

        root.querySelectorAll('.js-font-size-option').forEach((option) => {
            option.addEventListener('click', () => {
                const size = option.dataset.size;
                setFontSizeState(root, size);
                applyFontSize(root, size);
                closeFontSizePopover(root);
            });
        });

        if (textColorTrigger) {
            textColorTrigger.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                if (colorPopover && !colorPopover.hidden) {
                    closeColorPopover(root);
                } else {
                    openColorPopover(root);
                }
            });
        }

        root.querySelectorAll('.rich-editor-swatch').forEach((swatch) => {
            swatch.addEventListener('click', () => {
                setDraftColor(root, swatch.dataset.color);
            });
        });

        if (textColorAuto) {
            textColorAuto.addEventListener('click', () => {
                setDraftColor(root, '#000000');
            });
        }

        if (textColorNone) {
            textColorNone.addEventListener('click', () => {
                setDraftColor(root, '__inherit__');
            });
        }

        if (textColorMore && textColorNative) {
            textColorMore.addEventListener('click', () => {
                try {
                    if (typeof textColorNative.showPicker === 'function') {
                        textColorNative.showPicker();
                    } else {
                        textColorNative.click();
                    }
                } catch (error) {
                    textColorNative.click();
                }
            });

            textColorNative.addEventListener('input', () => {
                setDraftColor(root, textColorNative.value);
            });

            textColorNative.addEventListener('change', () => {
                setDraftColor(root, textColorNative.value);
            });
        }

        if (textColorConfirm && textColorTrigger) {
            textColorConfirm.addEventListener('click', () => {
                const draftColor = root.__richEditorDraftColor || '#000000';
                const savedRange = root.__richEditorSavedRange || null;

                if (draftColor === '__inherit__') {
                    clearTextColor(root, savedRange);
                    updateColorTriggerAppearance(textColorTrigger, '#4b5563');
                } else {
                    applyTextColor(root, draftColor, savedRange);
                    updateColorTriggerAppearance(textColorTrigger, draftColor);
                    addRecentColor(root, draftColor);
                }

                closeColorPopover(root);
            });
        }

        if (textColorCancel) {
            textColorCancel.addEventListener('click', () => {
                closeColorPopover(root);
            });
        }

        surface.addEventListener('mouseup', () => updateColorPreview(root));
        surface.addEventListener('keyup', () => updateColorPreview(root));
        surface.addEventListener('focus', () => updateColorPreview(root));
        surface.addEventListener('input', () => syncEditor(root));
        surface.addEventListener('blur', () => syncEditor(root));

        surface.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab') {
                return;
            }

            event.preventDefault();

            if (selectionInsideList(surface)) {
                document.execCommand(event.shiftKey ? 'outdent' : 'indent', false, null);
                syncEditor(root);
                return;
            }

            const selection = window.getSelection();

            if (event.shiftKey) {
                removeLeadingIndent(selection);
            } else {
                insertIndentSpaces();
            }

            syncEditor(root);
        });

        surface.addEventListener('paste', (event) => {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text/plain');
            const escaped = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');

            document.execCommand('insertHTML', false, escaped);
            syncEditor(root);
        });

        document.addEventListener('mousedown', (event) => {
            if (!root.contains(event.target)) {
                closeColorPopover(root);
                closeFontSizePopover(root);
                return;
            }

            if (colorPopover && !colorPopover.hidden) {
                const clickedTrigger = textColorTrigger && textColorTrigger.contains(event.target);
                const clickedPopover = colorPopover.contains(event.target);

                if (!clickedTrigger && !clickedPopover) {
                    closeColorPopover(root);
                }
            }

            if (fontSizePopover && !fontSizePopover.hidden) {
                const clickedTrigger = fontSizeTrigger && fontSizeTrigger.contains(event.target);
                const clickedPopover = fontSizePopover.contains(event.target);

                if (!clickedTrigger && !clickedPopover) {
                    closeFontSizePopover(root);
                }
            }
        });

        root.dataset.richEditorReady = 'true';
        setFontSizeState(root, '14px');
        syncEditor(root);
        updateColorPreview(root);
        updateColorTriggerAppearance(textColorTrigger, '#4b5563');
    }

    window.initializeRichTextEditors = function initializeRichTextEditors(scope = document) {
        scope.querySelectorAll('.js-rich-editor').forEach(attachEditor);
    };

    window.setRichTextEditorValue = function setRichTextEditorValue(input, value) {
        const field = typeof input === 'string'
            ? document.querySelector(`textarea[name="${input}"]`)
            : input;

        if (!field) {
            return;
        }

        const root = field.closest('.js-rich-editor');

        if (!root) {
            field.value = value || '';
            return;
        }

        const surface = root.querySelector('.rich-editor-surface');
        field.value = value || '';

        if (surface) {
            surface.innerHTML = value || '';
        }

        syncEditor(root);
        updateColorPreview(root);
    };

    window.syncRichTextEditors = function syncRichTextEditors(scope = document) {
        scope.querySelectorAll('.js-rich-editor').forEach(syncEditor);
    };

    document.addEventListener('DOMContentLoaded', () => window.initializeRichTextEditors());
    window.__cmsRichTextReady = true;
})();
</script>