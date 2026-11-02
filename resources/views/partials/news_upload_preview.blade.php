<style>
    .news-upload-preview-button {
        display: block;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
    }
    #newsUploadPreviewDialog {
        width: fit-content;
        max-width: 90vw;
        max-height: 90vh;
        margin: auto;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        overflow: visible;
    }
    #newsUploadPreviewDialog::backdrop { background: rgba(0, 0, 0, .7); }
    #newsUploadPreviewDialog form { margin: 0; }
    #newsUploadPreviewDialog button {
        position: fixed;
        top: 16px;
        right: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        padding: 0;
        border: 0;
        background: transparent;
        color: #fff;
        cursor: pointer;
    }
    #newsUploadPreviewDialog img { display: block; width: auto; height: auto; max-width: 90vw; max-height: 90vh; object-fit: contain; }
</style>
<dialog id="newsUploadPreviewDialog" aria-label="Uploaded news image preview">
    <form method="dialog">
        <button type="submit" aria-label="Close preview" autofocus>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg>
        </button>
    </form>
    <img alt="Uploaded news image">
</dialog>
