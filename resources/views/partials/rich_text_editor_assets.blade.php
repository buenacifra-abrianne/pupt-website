<style>
    .rich-editor {
        border: 1px solid #d8d3d1;
        border-radius: 12px;
        background: #fcfbfb;
        overflow: hidden;
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
        padding: 6px 8px;
        font-size: 15px;
        cursor: pointer;
    }

    .rich-editor-btn:hover {
        background: #ece5e2;
        color: #1f2937;
    }

    .rich-editor-btn-icon {
        min-width: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

        function attachEditor(root) {
            if (!root || root.dataset.richEditorReady === 'true') {
                return;
            }

            const input = root.querySelector('.rich-editor-input');
            const surface = root.querySelector('.rich-editor-surface');
            if (!input || !surface) {
                return;
            }

            surface.innerHTML = input.value || '';

            root.querySelectorAll('.rich-editor-btn').forEach((button) => {
                button.addEventListener('click', () => handleCommand(root, button));
            });

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

            root.dataset.richEditorReady = 'true';
            syncEditor(root);
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
        };

        window.syncRichTextEditors = function syncRichTextEditors(scope = document) {
            scope.querySelectorAll('.js-rich-editor').forEach(syncEditor);
        };

        document.addEventListener('DOMContentLoaded', () => window.initializeRichTextEditors());
        window.__cmsRichTextReady = true;
    })();
</script>
