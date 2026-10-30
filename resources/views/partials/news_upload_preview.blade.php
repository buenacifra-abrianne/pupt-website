<style>
    .news-upload-preview-button {
        display: block;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
    }
    #newsUploadPreviewDialog {
        width: min(900px, 90vw);
        max-height: 90vh;
        margin: auto;
        padding: 16px;
        border: 0;
        border-radius: 12px;
        background: #fffaf6;
    }
    #newsUploadPreviewDialog::backdrop { background: rgba(0, 0, 0, .7); }
    #newsUploadPreviewDialog form { display: flex; justify-content: flex-end; margin-bottom: 12px; }
    #newsUploadPreviewDialog img { display: block; width: 100%; max-height: 75vh; object-fit: contain; }
</style>
<dialog id="newsUploadPreviewDialog" aria-label="Uploaded news image preview">
    <form method="dialog">
        <button type="submit" class="btn btn-secondary" autofocus>Close preview</button>
    </form>
    <img alt="Uploaded news image">
</dialog>
