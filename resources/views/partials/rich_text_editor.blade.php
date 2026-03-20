<div class="rich-editor js-rich-editor" data-placeholder="{{ $placeholder ?? 'Write here...' }}">
    <div class="rich-editor-toolbar" role="toolbar" aria-label="Text formatting">
        <div class="rich-editor-group">
            <button type="button" class="rich-editor-btn" data-command="bold" title="Bold"><strong>B</strong></button>
            <button type="button" class="rich-editor-btn" data-command="italic" title="Italic"><em>I</em></button>
            <button type="button" class="rich-editor-btn" data-command="underline" title="Underline"><u>U</u></button>
            <button type="button" class="rich-editor-btn" data-command="strikeThrough" title="Strike"><s>S</s></button>
        </div>
        <div class="rich-editor-separator" aria-hidden="true"></div>
        <div class="rich-editor-group">
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="justifyLeft" title="Align Left" aria-label="Align Left">
                <i class="fas fa-align-left"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="justifyCenter" title="Align Center" aria-label="Align Center">
                <i class="fas fa-align-center"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="justifyRight" title="Align Right" aria-label="Align Right">
                <i class="fas fa-align-right"></i>
            </button>
        </div>
        <div class="rich-editor-separator" aria-hidden="true"></div>
        <div class="rich-editor-group">
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="insertUnorderedList" title="Bullet List" aria-label="Bullet List">
                <i class="fas fa-list-ul"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="insertOrderedList" title="Numbered List" aria-label="Numbered List">
                <i class="fas fa-list-ol"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="outdent" title="Outdent" aria-label="Outdent">
                <i class="fas fa-outdent"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="indent" title="Indent" aria-label="Indent">
                <i class="fas fa-indent"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="formatBlock" data-value="blockquote" title="Quote" aria-label="Quote">
                <i class="fas fa-quote-right"></i>
            </button>
        </div>
        <div class="rich-editor-separator" aria-hidden="true"></div>
        <div class="rich-editor-group">
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="undo" title="Undo" aria-label="Undo">
                <i class="fas fa-undo"></i>
            </button>
            <button type="button" class="rich-editor-btn rich-editor-btn-icon" data-command="redo" title="Redo" aria-label="Redo">
                <i class="fas fa-redo"></i>
            </button>
        </div>
    </div>

    <div class="rich-editor-surface" contenteditable="true" spellcheck="true" data-placeholder="{{ $placeholder ?? 'Write here...' }}"></div>
    <div class="rich-editor-footer">
        <span class="rich-editor-count">0 characters</span>
    </div>
    <textarea name="{{ $name }}" class="rich-editor-input" hidden>{{ $value ?? '' }}</textarea>
</div>
