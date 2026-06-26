@once
<style>
    .cms-image-editor[hidden] {
        display: none !important;
    }

    .cms-image-editor {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(20, 12, 10, 0.64);
        box-sizing: border-box;
    }

    .cms-image-editor-dialog {
        width: min(880px, calc(100vw - 32px));
        max-height: calc(100vh - 48px);
        display: grid;
        grid-template-rows: auto minmax(220px, 1fr) auto;
        gap: 16px;
        padding: 18px;
        border-radius: 8px;
        background: #fffaf6;
        box-shadow: 0 24px 70px rgba(25, 16, 12, 0.28);
        box-sizing: border-box;
        overflow: hidden;
    }

    .cms-image-editor-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .cms-image-editor-title {
        margin: 0;
        color: #3a1712;
        font-size: 18px;
        line-height: 1.2;
        font-weight: 800;
    }

    .cms-image-editor-subtitle {
        margin: 4px 0 0;
        color: #70534b;
        font-size: 13px;
        line-height: 1.4;
    }

    .cms-image-editor-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 8px;
        background: #f2e6df;
        color: #4b2119;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
    }

    .cms-image-editor-stage {
        position: relative;
        min-height: 320px;
        border-radius: 8px;
        background: #1f1a18;
        overflow: hidden;
        touch-action: none;
        cursor: grab;
    }

    .cms-image-editor-stage.is-dragging {
        cursor: grabbing;
    }

    .cms-image-editor-canvas {
        width: 100%;
        height: 100%;
        display: block;
    }

    .cms-image-editor-controls {
        display: grid;
        grid-template-columns: minmax(160px, 1fr) auto;
        gap: 14px;
        align-items: center;
    }

    .cms-image-editor-zoom {
        display: grid;
        grid-template-columns: auto minmax(120px, 1fr) auto;
        gap: 10px;
        align-items: center;
        color: #4b2119;
        font-size: 13px;
        font-weight: 700;
    }

    .cms-image-editor-zoom input {
        width: 100%;
        accent-color: #8f1d18;
    }

    .cms-image-editor-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .cms-image-editor-btn {
        min-height: 38px;
        border: 1px solid #d8c3b8;
        border-radius: 8px;
        padding: 8px 14px;
        background: #fff;
        color: #4b2119;
        font-weight: 800;
        cursor: pointer;
    }

    .cms-image-editor-btn:hover,
    .cms-image-editor-btn:focus-visible {
        border-color: #8f1d18;
        outline: none;
    }

    .cms-image-editor-btn-primary {
        border-color: #8f1d18;
        background: #8f1d18;
        color: #fff;
    }

    @media (max-width: 680px) {
        .cms-image-editor {
            padding: 12px;
        }

        .cms-image-editor-dialog {
            width: calc(100vw - 24px);
            max-height: calc(100vh - 24px);
            padding: 14px;
        }

        .cms-image-editor-stage {
            min-height: 260px;
        }

        .cms-image-editor-controls {
            grid-template-columns: 1fr;
        }

        .cms-image-editor-actions {
            justify-content: stretch;
        }

        .cms-image-editor-btn {
            flex: 1 1 130px;
        }
    }
</style>

<script>
    (function () {
        if (window.CmsImageEditor) {
            return;
        }

        const editor = {
            active: null,
            modal: null,
            canvas: null,
            stage: null,
            zoomInput: null,
            resolve: null,
            state: null,
        };

        function createModal() {
            if (editor.modal) {
                return editor.modal;
            }

            const modal = document.createElement('div');
            modal.className = 'cms-image-editor';
            modal.hidden = true;
            modal.innerHTML = `
                <div class="cms-image-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="cmsImageEditorTitle">
                    <div class="cms-image-editor-head">
                        <div>
                            <h2 class="cms-image-editor-title" id="cmsImageEditorTitle">Adjust image</h2>
                            <p class="cms-image-editor-subtitle">Drag the image, adjust zoom, then choose original or edited.</p>
                        </div>
                        <button type="button" class="cms-image-editor-close" data-cms-image-editor-cancel aria-label="Close">&times;</button>
                    </div>
                    <div class="cms-image-editor-stage" data-cms-image-editor-stage>
                        <canvas class="cms-image-editor-canvas" data-cms-image-editor-canvas></canvas>
                    </div>
                    <div class="cms-image-editor-controls">
                        <label class="cms-image-editor-zoom">
                            <span>Zoom</span>
                            <input type="range" min="1" max="3" step="0.01" value="1" data-cms-image-editor-zoom>
                            <span data-cms-image-editor-zoom-value>100%</span>
                        </label>
                        <div class="cms-image-editor-actions">
                            <button type="button" class="cms-image-editor-btn" data-cms-image-editor-reset>Reset</button>
                            <button type="button" class="cms-image-editor-btn" data-cms-image-editor-original>Use original</button>
                            <button type="button" class="cms-image-editor-btn cms-image-editor-btn-primary" data-cms-image-editor-apply>Use edited</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            editor.modal = modal;
            editor.canvas = modal.querySelector('[data-cms-image-editor-canvas]');
            editor.stage = modal.querySelector('[data-cms-image-editor-stage]');
            editor.zoomInput = modal.querySelector('[data-cms-image-editor-zoom]');
            editor.zoomValue = modal.querySelector('[data-cms-image-editor-zoom-value]');

            modal.querySelector('[data-cms-image-editor-cancel]').addEventListener('click', () => finish(null));
            modal.querySelector('[data-cms-image-editor-original]').addEventListener('click', () => finish(editor.state?.file || null));
            modal.querySelector('[data-cms-image-editor-reset]').addEventListener('click', resetState);
            modal.querySelector('[data-cms-image-editor-apply]').addEventListener('click', applyEditedImage);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    finish(null);
                }
            });
            editor.zoomInput.addEventListener('input', () => {
                if (!editor.state) {
                    return;
                }

                editor.state.zoom = Number(editor.zoomInput.value || '1');
                clampOffset();
                render();
            });

            bindDrag();
            window.addEventListener('resize', () => {
                if (editor.state) {
                    render();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (!editor.state || event.key !== 'Escape') {
                    return;
                }

                event.preventDefault();
                finish(null);
            });

            return modal;
        }

        function bindDrag() {
            let pointerId = null;
            let startX = 0;
            let startY = 0;
            let startOffsetX = 0;
            let startOffsetY = 0;

            editor.stage.addEventListener('pointerdown', (event) => {
                if (!editor.state) {
                    return;
                }

                pointerId = event.pointerId;
                startX = event.clientX;
                startY = event.clientY;
                startOffsetX = editor.state.offsetX;
                startOffsetY = editor.state.offsetY;
                editor.stage.classList.add('is-dragging');
                editor.stage.setPointerCapture(pointerId);
            });

            editor.stage.addEventListener('pointermove', (event) => {
                if (!editor.state || pointerId !== event.pointerId) {
                    return;
                }

                editor.state.offsetX = startOffsetX + event.clientX - startX;
                editor.state.offsetY = startOffsetY + event.clientY - startY;
                clampOffset();
                render();
            });

            const endDrag = (event) => {
                if (pointerId !== event.pointerId) {
                    return;
                }

                pointerId = null;
                editor.stage.classList.remove('is-dragging');
            };

            editor.stage.addEventListener('pointerup', endDrag);
            editor.stage.addEventListener('pointercancel', endDrag);
        }

        function getAspectRatio(options) {
            const preview = options?.previewElement;
            const rect = preview?.getBoundingClientRect?.();
            const width = Number(rect?.width || preview?.naturalWidth || 0);
            const height = Number(rect?.height || preview?.naturalHeight || 0);

            if (width > 0 && height > 0) {
                return Math.min(4, Math.max(0.45, width / height));
            }

            return 16 / 9;
        }

        function loadImage(file) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const image = new Image();
                image.onload = () => resolve({ image, url });
                image.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error('Image could not be loaded.'));
                };
                image.src = url;
            });
        }

        function resetState() {
            if (!editor.state) {
                return;
            }

            editor.state.zoom = 1;
            editor.state.offsetX = 0;
            editor.state.offsetY = 0;
            editor.zoomInput.value = '1';
            render();
        }

        function getGeometry() {
            const rect = editor.stage.getBoundingClientRect();
            const width = Math.max(1, Math.round(rect.width));
            const height = Math.max(1, Math.round(rect.height));
            const aspect = editor.state?.aspect || (16 / 9);
            let cropWidth = width * 0.78;
            let cropHeight = cropWidth / aspect;

            if (cropHeight > height * 0.78) {
                cropHeight = height * 0.78;
                cropWidth = cropHeight * aspect;
            }

            return {
                width,
                height,
                cropX: (width - cropWidth) / 2,
                cropY: (height - cropHeight) / 2,
                cropWidth,
                cropHeight,
            };
        }

        function getDrawRect(geometry) {
            const image = editor.state.image;
            const baseScale = Math.max(
                geometry.cropWidth / image.naturalWidth,
                geometry.cropHeight / image.naturalHeight
            );
            const scale = baseScale * editor.state.zoom;
            const drawWidth = image.naturalWidth * scale;
            const drawHeight = image.naturalHeight * scale;
            const centerX = geometry.width / 2 + editor.state.offsetX;
            const centerY = geometry.height / 2 + editor.state.offsetY;

            return {
                x: centerX - drawWidth / 2,
                y: centerY - drawHeight / 2,
                width: drawWidth,
                height: drawHeight,
            };
        }

        function clampOffset() {
            if (!editor.state) {
                return;
            }

            const geometry = getGeometry();
            const draw = getDrawRect(geometry);
            const cropLeft = geometry.cropX;
            const cropRight = geometry.cropX + geometry.cropWidth;
            const cropTop = geometry.cropY;
            const cropBottom = geometry.cropY + geometry.cropHeight;

            if (draw.x > cropLeft) {
                editor.state.offsetX -= draw.x - cropLeft;
            }
            if (draw.x + draw.width < cropRight) {
                editor.state.offsetX += cropRight - (draw.x + draw.width);
            }
            if (draw.y > cropTop) {
                editor.state.offsetY -= draw.y - cropTop;
            }
            if (draw.y + draw.height < cropBottom) {
                editor.state.offsetY += cropBottom - (draw.y + draw.height);
            }
        }

        function render() {
            if (!editor.state) {
                return;
            }

            const geometry = getGeometry();
            const ratio = window.devicePixelRatio || 1;
            editor.canvas.width = Math.round(geometry.width * ratio);
            editor.canvas.height = Math.round(geometry.height * ratio);
            editor.canvas.style.width = `${geometry.width}px`;
            editor.canvas.style.height = `${geometry.height}px`;

            const ctx = editor.canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.clearRect(0, 0, geometry.width, geometry.height);

            const draw = getDrawRect(geometry);
            ctx.drawImage(editor.state.image, draw.x, draw.y, draw.width, draw.height);

            ctx.save();
            ctx.fillStyle = 'rgba(0, 0, 0, 0.48)';
            ctx.fillRect(0, 0, geometry.width, geometry.cropY);
            ctx.fillRect(0, geometry.cropY + geometry.cropHeight, geometry.width, geometry.height);
            ctx.fillRect(0, geometry.cropY, geometry.cropX, geometry.cropHeight);
            ctx.fillRect(geometry.cropX + geometry.cropWidth, geometry.cropY, geometry.width, geometry.cropHeight);
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.strokeRect(geometry.cropX, geometry.cropY, geometry.cropWidth, geometry.cropHeight);
            ctx.restore();

            if (editor.zoomValue) {
                editor.zoomValue.textContent = `${Math.round(editor.state.zoom * 100)}%`;
            }
        }

        function canvasToBlob(canvas, type, quality) {
            return new Promise((resolve) => canvas.toBlob(resolve, type, quality));
        }

        async function applyEditedImage() {
            if (!editor.state) {
                finish(null);
                return;
            }

            const geometry = getGeometry();
            const draw = getDrawRect(geometry);
            const image = editor.state.image;
            const sx = Math.max(0, (geometry.cropX - draw.x) / draw.width * image.naturalWidth);
            const sy = Math.max(0, (geometry.cropY - draw.y) / draw.height * image.naturalHeight);
            const sw = Math.min(image.naturalWidth - sx, geometry.cropWidth / draw.width * image.naturalWidth);
            const sh = Math.min(image.naturalHeight - sy, geometry.cropHeight / draw.height * image.naturalHeight);
            const outputWidth = Math.max(1, Math.min(1800, Math.round(sw)));
            const outputHeight = Math.max(1, Math.round(outputWidth / (sw / sh)));
            const output = document.createElement('canvas');
            output.width = outputWidth;
            output.height = outputHeight;
            output.getContext('2d').drawImage(image, sx, sy, sw, sh, 0, 0, outputWidth, outputHeight);

            const originalType = (editor.state.file.type || '').toLowerCase();
            const type = ['image/jpeg', 'image/png', 'image/webp'].includes(originalType) ? originalType : 'image/jpeg';
            const blob = await canvasToBlob(output, type, type === 'image/jpeg' ? 0.92 : undefined);

            if (!blob) {
                finish(editor.state.file);
                return;
            }

            const extension = type === 'image/png' ? 'png' : (type === 'image/webp' ? 'webp' : 'jpg');
            const baseName = editor.state.file.name.replace(/\.[^.]+$/, '') || 'image';
            finish(new File([blob], `${baseName}-edited.${extension}`, {
                type,
                lastModified: Date.now(),
            }));
        }

        function finish(file) {
            const resolve = editor.resolve;
            const state = editor.state;
            editor.modal.hidden = true;
            document.documentElement.classList.remove('cms-modal-open');
            document.body.classList.remove('cms-modal-open');
            editor.resolve = null;
            editor.state = null;

            if (state?.url) {
                URL.revokeObjectURL(state.url);
            }

            if (typeof resolve === 'function') {
                resolve(file);
            }
        }

        async function editFile(file, options = {}) {
            if (!(file instanceof File) || !file.type.startsWith('image/')) {
                return file || null;
            }

            if (file.type.toLowerCase() === 'image/svg+xml') {
                return file;
            }

            createModal();

            try {
                const loaded = await loadImage(file);
                editor.state = {
                    file,
                    image: loaded.image,
                    url: loaded.url,
                    aspect: getAspectRatio(options),
                    zoom: 1,
                    offsetX: 0,
                    offsetY: 0,
                };
            } catch (error) {
                return file;
            }

            editor.zoomInput.value = '1';
            editor.modal.hidden = false;
            document.documentElement.classList.add('cms-modal-open');
            document.body.classList.add('cms-modal-open');
            render();

            return new Promise((resolve) => {
                editor.resolve = resolve;
            });
        }

        function setInputFile(input, file) {
            if (!(input instanceof HTMLInputElement) || !(file instanceof File) || typeof DataTransfer !== 'function') {
                return false;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            return true;
        }

        window.CmsImageEditor = {
            editFile,
            setInputFile,
        };
    })();
</script>
@endonce
