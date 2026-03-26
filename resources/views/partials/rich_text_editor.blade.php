<div class="rich-editor js-rich-editor" data-placeholder="{{ $placeholder ?? 'Write here...' }}">
    <div class="rich-editor-toolbar" role="toolbar" aria-label="Text formatting">
        <div class="rich-editor-group">
            <button type="button" class="rich-editor-btn" data-command="bold" data-active-command="bold" title="Bold"><strong>B</strong></button>
            <button type="button" class="rich-editor-btn" data-command="italic" data-active-command="italic" title="Italic"><em>I</em></button>
            <button type="button" class="rich-editor-btn" data-command="underline" data-active-command="underline" title="Underline"><u>U</u></button>
            <button type="button" class="rich-editor-btn" data-command="strikeThrough" data-active-command="strikeThrough" title="Strike"><s>S</s></button>
        </div>

        <div class="rich-editor-separator" aria-hidden="true"></div>

        <div class="rich-editor-group">
            <div class="rich-editor-fontsize-wrap">
                <button
                    type="button"
                    class="rich-editor-btn rich-editor-fontsize-trigger js-font-size-trigger"
                    title="Font Size"
                    aria-label="Font Size"
                >
                    <span class="js-font-size-label">14px</span>
                    <i class="fas fa-chevron-down"></i>
                </button>

                <div class="rich-editor-fontsize-popover js-font-size-popover" hidden>
                    <div class="rich-editor-fontsize-title">Font Size</div>

                    <div class="rich-editor-fontsize-options">
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="12px">12px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option is-active" data-size="14px">14px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="16px">16px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="18px">18px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="20px">20px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="24px">24px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="28px">28px</button>
                        <button type="button" class="rich-editor-fontsize-option js-font-size-option" data-size="32px">32px</button>
                    </div>
                </div>
            </div>

            <div class="rich-editor-color-wrap">
                <button
                    type="button"
                    class="rich-editor-btn rich-editor-btn-icon js-text-color-trigger"
                    title="Text Color"
                    aria-label="Text Color"
                >
                    <i class="fas fa-palette"></i>
                </button>

                <div class="rich-editor-color-popover js-text-color-popover" hidden>
                    <div class="rich-editor-color-section">
                        <div class="rich-editor-color-section-title">Theme Colors</div>
                        <div class="rich-editor-color-grid js-theme-color-grid">
                            <button type="button" class="rich-editor-swatch" data-color="#000000" style="background:#000000;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#44546A" style="background:#44546A;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#5B9BD5" style="background:#5B9BD5;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#70AD47" style="background:#70AD47;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#FFC000" style="background:#FFC000;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#ED7D31" style="background:#ED7D31;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#C00000" style="background:#C00000;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#7030A0" style="background:#7030A0;"></button>

                            <button type="button" class="rich-editor-swatch" data-color="#F2F2F2" style="background:#F2F2F2;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#D9E2F3" style="background:#D9E2F3;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#DDEBF7" style="background:#DDEBF7;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#E2F0D9" style="background:#E2F0D9;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#FFF2CC" style="background:#FFF2CC;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#FCE4D6" style="background:#FCE4D6;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#F4CCCC" style="background:#F4CCCC;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#EADCF4" style="background:#EADCF4;"></button>
                        </div>
                    </div>

                    <div class="rich-editor-color-section">
                        <div class="rich-editor-color-section-title">Standard Colors</div>
                        <div class="rich-editor-color-grid js-standard-color-grid">
                            <button type="button" class="rich-editor-swatch" data-color="#C00000" style="background:#C00000;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#FF0000" style="background:#FF0000;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#FFC000" style="background:#FFC000;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#FFFF00" style="background:#FFFF00;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#92D050" style="background:#92D050;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#00B050" style="background:#00B050;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#00B0F0" style="background:#00B0F0;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#0070C0" style="background:#0070C0;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#002060" style="background:#002060;"></button>
                            <button type="button" class="rich-editor-swatch" data-color="#7030A0" style="background:#7030A0;"></button>
                        </div>
                    </div>
                </div>
            </div>
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
