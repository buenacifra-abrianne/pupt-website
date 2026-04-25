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

.rich-editor-btn.is-active {
    background: #7c0a02;
    color: #fff;
    box-shadow: inset 0 0 0 1px rgba(124, 10, 2, 0.24);
}

.rich-editor-btn.is-active strong,
.rich-editor-btn.is-active em,
.rich-editor-btn.is-active u,
.rich-editor-btn.is-active s,
.rich-editor-btn.is-active i {
    color: inherit;
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
    width: 248px;
    padding: 12px;
}

.rich-editor-fontsize-popover[hidden],
.rich-editor-color-popover[hidden] {
    display: none;
}

.rich-editor-fontsize-title {
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

.rich-editor-color-section {
    margin-bottom: 12px;
}

.rich-editor-color-section:last-child {
    margin-bottom: 0;
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
    transition: transform 0.14s ease, box-shadow 0.14s ease, border-color 0.14s ease;
}

.rich-editor-swatch:hover {
    transform: translateY(-1px);
    border-color: #b9a8a2;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
}

.rich-editor-swatch.is-selected {
    outline: 2px solid #111827;
    outline-offset: 1px;
}

.rich-editor-btn:focus,
.rich-editor-fontsize-option:focus,
.rich-editor-swatch:focus,
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
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
    word-wrap: break-word;
}

.rich-editor-surface p,
.rich-editor-surface div {
    margin: 0 0 0.45rem;
    white-space: inherit;
    overflow-wrap: inherit;
    word-break: inherit;
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

.rich-editor-count.is-limit {
    color: #b12a2a;
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

.rich-text-content strong:not([style*="color"]),
.rich-text-content b:not([style*="color"]),
.rich-text-content em:not([style*="color"]),
.rich-text-content i:not([style*="color"]),
.rich-text-content u:not([style*="color"]),
.rich-text-content s:not([style*="color"]),
.rich-text-content a:not([style*="color"]) {
    color: inherit;
}

.rich-editor-surface * {
    line-height: inherit;
    max-width: 100%;
    overflow-wrap: inherit;
    word-break: inherit;
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

        const walker = document.createTreeWalker(wrapper, NodeFilter.SHOW_TEXT);
        const textNodes = [];

        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        textNodes.forEach((node) => {
            node.textContent = (node.textContent || '').replace(/\u200B/g, '');
        });

        if (!wrapper.textContent.trim()) {
            return '';
        }

        return wrapper.innerHTML.trim();
    }

    function syncEditor(root) {
        const input = root.querySelector('.rich-editor-input');
        const surface = root.querySelector('.rich-editor-surface');
        const counter = root.querySelector('.rich-editor-count');
        const characterLimit = Number(root.dataset.characterLimit || 0);
        const counterMode = String(root.dataset.counterMode || '');

        if (!input || !surface) {
            return;
        }

        if (characterLimit > 0) {
            enforceCharacterLimit(surface, characterLimit);
        }

        input.value = normalizeEditorHtml(surface.innerHTML);

        if (counter) {
            const textLength = Array.from(surface.textContent || '').length;

            if (characterLimit > 0 || counterMode === 'limit') {
                const safeLimit = characterLimit > 0 ? characterLimit : textLength;
                counter.textContent = `${Math.min(textLength, safeLimit)}/${safeLimit}`;
                counter.classList.toggle('is-limit', safeLimit > 0 && textLength >= safeLimit);
            } else {
                counter.textContent = `${textLength} characters`;
                counter.classList.remove('is-limit');
            }
        }
    }

    function syncEditorAndNotify(root) {
        const input = root.querySelector('.rich-editor-input');
        const previousValue = input ? input.value : '';

        syncEditor(root);

        if (input && input.value !== previousValue) {
            input.dispatchEvent(new Event('input', {
                bubbles: true,
            }));
            input.dispatchEvent(new Event('change', {
                bubbles: true,
            }));
        }
    }

    function enforceCharacterLimit(surface, limit) {
        if (!surface || !Number.isFinite(limit) || limit <= 0) {
            return;
        }

        let used = 0;
        const walker = document.createTreeWalker(surface, NodeFilter.SHOW_TEXT);
        const textNodes = [];

        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        textNodes.forEach((node) => {
            const value = node.textContent || '';
            const chars = Array.from(value);

            if (used >= limit) {
                node.textContent = '';
                return;
            }

            const nextCount = used + chars.length;
            if (nextCount <= limit) {
                used = nextCount;
                return;
            }

            const remaining = Math.max(0, limit - used);
            node.textContent = chars.slice(0, remaining).join('');
            used = limit;
        });

        surface.querySelectorAll('*').forEach((element) => {
            if (!element.textContent?.trim() && !element.querySelector('img, br')) {
                if (element === surface) {
                    return;
                }

                if (!element.children.length) {
                    element.remove();
                }
            }
        });
    }

    function closestWithin(node, selector, boundary) {
        let current = node && node.nodeType === Node.ELEMENT_NODE ? node : node?.parentElement;

        while (current && current !== boundary) {
            if (current.matches?.(selector)) {
                return current;
            }

            current = current.parentElement;
        }

        return null;
    }

    function hasBlockChild(element) {
        return Array.from(element.children || []).some((child) => {
            const display = window.getComputedStyle(child).display;
            return display === 'block'
                || display === 'list-item'
                || display === 'table'
                || display === 'grid'
                || display === 'flex';
        });
    }

    function unwrapBlockquote(blockquote) {
        const parent = blockquote.parentNode;
        if (!parent) {
            return;
        }

        if (!hasBlockChild(blockquote)) {
            const paragraph = document.createElement('p');
            paragraph.innerHTML = blockquote.innerHTML;
            parent.replaceChild(paragraph, blockquote);
            return;
        }

        while (blockquote.firstChild) {
            parent.insertBefore(blockquote.firstChild, blockquote);
        }

        parent.removeChild(blockquote);
    }

    function toggleBlockquote(root, surface) {
        restoreSelection(root.__richEditorSavedRange || null);

        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            surface.focus();
            document.execCommand('formatBlock', false, '<blockquote>');
            return;
        }

        const range = selection.getRangeAt(0);
        const commonAncestor = range.commonAncestorContainer;
        const container = commonAncestor.nodeType === Node.ELEMENT_NODE
            ? commonAncestor
            : commonAncestor.parentElement;

        const existingBlockquote = closestWithin(container, 'blockquote', surface);
        if (existingBlockquote) {
            unwrapBlockquote(existingBlockquote);
            return;
        }

        document.execCommand('formatBlock', false, '<blockquote>');

        if (!surface.querySelector('blockquote')) {
            document.execCommand('formatBlock', false, 'blockquote');
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
        restoreSelection(root.__richEditorSavedRange || null);
        if (command === 'formatBlock' && value === 'blockquote') {
            toggleBlockquote(root, surface);
        } else {
            document.execCommand(command, false, value);
        }
        saveSelection(root);
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

    function saveSelection(root) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface) {
            root.__richEditorSavedRange = null;
            return null;
        }

        const range = cloneRange(getSelectionRangeInside(surface));
        root.__richEditorSavedRange = range;

        return range;
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

    function applyInlineStyleAtCaret(surface, styles, savedRange = null) {
        const range = savedRange || getSelectionRangeInside(surface);

        if (!range || !range.collapsed) {
            return false;
        }

        const wrapper = document.createElement('span');

        Object.entries(styles).forEach(([key, value]) => {
            wrapper.style[key] = value;
        });

        if (styles.fontSize) {
            wrapper.style.lineHeight = '1';
            wrapper.style.verticalAlign = 'baseline';
            wrapper.style.display = 'inline';
        }

        const textNode = document.createTextNode('\u200B');
        wrapper.appendChild(textNode);
        range.insertNode(wrapper);

        const selection = window.getSelection();
        const caretRange = document.createRange();
        caretRange.setStart(textNode, textNode.textContent.length);
        caretRange.collapse(true);
        selection.removeAllRanges();
        selection.addRange(caretRange);

        return true;
    }

    function applyFontSize(root, size) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface || !size) {
            return;
        }

        surface.focus();
        restoreSelection(root.__richEditorSavedRange || null);
        applyInlineStyleToSelection(surface, { fontSize: size });
        saveSelection(root);
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

        const activeRange = getSelectionRangeInside(surface);

        if (activeRange && !activeRange.collapsed) {
            document.execCommand('styleWithCSS', false, true);
            document.execCommand('foreColor', false, color);
        } else if (!applyInlineStyleAtCaret(surface, { color: color }, activeRange)) {
            applyInlineStyleToSelection(surface, { color: color }, activeRange);
        }

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

    function markSelectedSwatch(root, color) {
        root.querySelectorAll('.rich-editor-swatch').forEach((swatch) => {
            swatch.classList.toggle('is-selected', swatch.dataset.color === color);
        });
    }

    function rgbToHex(color) {
        if (!color || typeof color !== 'string') {
            return null;
        }

        const match = color.replace(/\s+/g, '').match(/^rgba?\((\d+),(\d+),(\d+)/i);
        if (!match) {
            return color.startsWith('#') ? color.toUpperCase() : null;
        }

        const toHex = (value) => Number.parseInt(value, 10).toString(16).padStart(2, '0');
        return `#${toHex(match[1])}${toHex(match[2])}${toHex(match[3])}`.toUpperCase();
    }

    function resolveColorValue(color) {
        if (!color) {
            return null;
        }

        const probe = document.createElement('span');
        probe.style.color = color;
        probe.style.display = 'none';
        document.body.appendChild(probe);
        const resolved = window.getComputedStyle(probe).color;
        probe.remove();

        return rgbToHex(resolved);
    }

    function getSelectionElement(surface) {
        const range = getSelectionRangeInside(surface);

        if (!range) {
            return document.activeElement === surface ? surface : null;
        }

        let node = range.startContainer;
        if (node.nodeType === Node.TEXT_NODE) {
            node = node.parentNode;
        }

        return node && surface.contains(node) ? node : surface;
    }

    function updateTextColorState(root) {
        const surface = root.querySelector('.rich-editor-surface');
        const trigger = root.querySelector('.js-text-color-trigger');

        if (!surface || !trigger) {
            return;
        }

        const element = getSelectionElement(surface);
        if (!element) {
            markSelectedSwatch(root, null);
            updateColorTriggerAppearance(trigger, '#4b5563');
            return;
        }

        const computedColor = rgbToHex(window.getComputedStyle(element).color);
        const matchedSwatch = computedColor
            ? root.querySelector(`.rich-editor-swatch[data-color="${computedColor}"]`)
            : null;

        markSelectedSwatch(root, matchedSwatch ? computedColor : null);
        updateColorTriggerAppearance(trigger, computedColor || '#4b5563');
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
    }

    function normalizeFontSizeValue(size) {
        const parsed = Number.parseFloat(String(size || '').replace('px', '').trim());

        if (!Number.isFinite(parsed) || parsed <= 0) {
            return '14px';
        }

        return `${Math.round(parsed)}px`;
    }

    function findEditorContentElement(surface) {
        if (!surface) {
            return null;
        }

        const selectionElement = getSelectionElement(surface);
        if (selectionElement && selectionElement !== surface) {
            return selectionElement;
        }

        const walker = document.createTreeWalker(surface, NodeFilter.SHOW_TEXT);

        while (walker.nextNode()) {
            const textNode = walker.currentNode;
            if ((textNode.textContent || '').trim() !== '') {
                return textNode.parentNode instanceof HTMLElement ? textNode.parentNode : surface;
            }
        }

        return surface.firstElementChild instanceof HTMLElement ? surface.firstElementChild : surface;
    }

    function updateFontSizeState(root) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface) {
            return;
        }

        const sourceElement = findEditorContentElement(surface);
        const computedSize = sourceElement
            ? window.getComputedStyle(sourceElement).fontSize
            : '14px';

        setFontSizeState(root, normalizeFontSizeValue(computedSize));
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
        updateTextColorState(root);
        popover.hidden = false;
    }

    function closeColorPopover(root) {
        const popover = root.querySelector('.js-text-color-popover');
        if (popover) {
            popover.hidden = true;
        }
    }

    function safeQueryCommandState(command) {
        try {
            return !!document.queryCommandState(command);
        } catch (_) {
            return false;
        }
    }

    function updateToolbarState(root) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface) {
            return;
        }

        const isInsideEditor = !!getSelectionRangeInside(surface) || document.activeElement === surface;

        root.querySelectorAll('.rich-editor-btn[data-active-command]').forEach((button) => {
            let isActive = false;

            if (isInsideEditor) {
                isActive = safeQueryCommandState(button.dataset.activeCommand);
            }

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        root.querySelectorAll('.rich-editor-btn[data-command="formatBlock"][data-value="blockquote"]').forEach((button) => {
            const range = getSelectionRangeInside(surface);
            const node = range?.commonAncestorContainer || (document.activeElement === surface ? surface : null);
            const isActive = !!node && !!closestWithin(node, 'blockquote', surface);

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }
    function normalizeStyledSpans(root) {
        const surface = root.querySelector('.rich-editor-surface');

        if (!surface) {
            return;
        }

        surface.querySelectorAll('font').forEach((font) => {
            const replacement = document.createElement('span');
            const color = font.getAttribute('color') || font.style.color;

            if (color) {
                replacement.style.color = color;
            }

            while (font.firstChild) {
                replacement.appendChild(font.firstChild);
            }

            font.replaceWith(replacement);
        });

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
        const colorPopover = root.querySelector('.js-text-color-popover');

        if (!input || !surface) {
            return;
        }

        root.__richEditorSavedRange = null;
        root.__richEditorCurrentFontSize = '14px';

        surface.innerHTML = input.value || '';

        root.querySelectorAll('.rich-editor-btn').forEach((button) => {
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
                saveSelection(root);
            });

            if (!button.classList.contains('js-font-size-trigger') && !button.classList.contains('js-text-color-trigger')) {
                button.addEventListener('click', () => {
                    handleCommand(root, button);
                    syncEditorAndNotify(root);
                    updateToolbarState(root);
                    updateTextColorState(root);
                });
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
            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                saveSelection(root);
            });

            option.addEventListener('click', () => {
                const size = option.dataset.size;
                setFontSizeState(root, size);
                applyFontSize(root, size);
                syncEditorAndNotify(root);
                closeFontSizePopover(root);
                updateTextColorState(root);
            });
        });

        if (textColorTrigger) {
            textColorTrigger.addEventListener('mousedown', (event) => {
                event.preventDefault();
                saveSelection(root);
            });

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
            swatch.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            swatch.addEventListener('click', () => {
                const selectedColor = resolveColorValue(swatch.dataset.color);
                const savedRange = root.__richEditorSavedRange || null;

                if (!selectedColor) {
                    return;
                }

                applyTextColor(root, selectedColor, savedRange);
                syncEditorAndNotify(root);
                markSelectedSwatch(root, selectedColor);
                updateColorTriggerAppearance(textColorTrigger, selectedColor);
                closeColorPopover(root);
                updateToolbarState(root);
            });
        });

        surface.addEventListener('click', () => {
            saveSelection(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
        });
        surface.addEventListener('mouseup', () => {
            saveSelection(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
        });
        surface.addEventListener('keyup', () => {
            saveSelection(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
        });
        surface.addEventListener('focus', () => {
            saveSelection(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
        });
        surface.addEventListener('input', () => {
            saveSelection(root);
            syncEditor(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
        });
        surface.addEventListener('blur', () => {
            syncEditor(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
        });

        surface.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab') {
                return;
            }

            event.preventDefault();

            if (selectionInsideList(surface)) {
                document.execCommand(event.shiftKey ? 'outdent' : 'indent', false, null);
                syncEditor(root);
                updateFontSizeState(root);
                updateToolbarState(root);
                updateTextColorState(root);
                return;
            }

            const selection = window.getSelection();

            if (event.shiftKey) {
                removeLeadingIndent(selection);
            } else {
                insertIndentSpaces();
            }

            syncEditor(root);
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
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
            updateFontSizeState(root);
            updateToolbarState(root);
            updateTextColorState(root);
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
        updateFontSizeState(root);
        syncEditor(root);
        updateToolbarState(root);
        updateTextColorState(root);
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
        updateFontSizeState(root);
        updateToolbarState(root);
        updateTextColorState(root);
    };

    window.syncRichTextEditors = function syncRichTextEditors(scope = document) {
        scope.querySelectorAll('.js-rich-editor').forEach(syncEditor);
    };

    document.addEventListener('DOMContentLoaded', () => window.initializeRichTextEditors());
    window.__cmsRichTextReady = true;
})();
</script>
