<dialog id="analyticsPdfExportModal" class="analytics-pdf-export-dialog" aria-labelledby="pdfExportTitle" aria-describedby="pdfExportDescription">
    <div class="analytics-pdf-export-header">
        <h2 id="pdfExportTitle">Export PDF</h2>
        <button type="button" class="analytics-pdf-export-close" onclick="document.getElementById('analyticsPdfExportModal').close()" aria-label="Close PDF export">&times;</button>
    </div>
    <p id="pdfExportDescription">Choose which sections to include in your report.</p>
    <fieldset class="analytics-pdf-export-sections">
        <legend>Report sections</legend>
        @foreach(\App\Support\AnalyticsPdfBuilder::SECTIONS as $key => $label)
            <label>
                <input type="checkbox" name="sections[]" value="{{ $key }}" form="exportPdfForm" checked onchange="updatePdfExportSelection()">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </fieldset>
    <p id="pdfExportSelectionHint" role="status" hidden>Select at least one section to export.</p>
    <div class="analytics-pdf-export-footer">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('analyticsPdfExportModal').close()">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmPdfExportButton" onclick="confirmPdfExport()">Export PDF</button>
    </div>
</dialog>

<style>
    .analytics-pdf-export-dialog {
        margin: auto;
        width: min(480px, calc(100% - 32px));
        max-height: calc(100dvh - 32px);
        overflow-y: auto;
        padding: 24px;
        border: 1px solid #e8d8d8;
        border-radius: 16px;
        background: #fffaf6;
        color: #333;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        font-family: inherit;
    }
    .analytics-pdf-export-dialog::backdrop { background: rgba(0, 0, 0, 0.45); }
    .analytics-pdf-export-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
    .analytics-pdf-export-header h2 { margin: 0; color: #800000; font-size: 22px; }
    .analytics-pdf-export-close { border: 0; background: transparent; color: #800000; font-size: 28px; cursor: pointer; padding: 0 6px; }
    .analytics-pdf-export-sections { display: grid; gap: 12px; border: 0; padding: 0; margin: 20px 0; }
    .analytics-pdf-export-sections legend { font-weight: 600; margin-bottom: 12px; }
    .analytics-pdf-export-sections label { display: flex; align-items: center; gap: 12px; cursor: pointer; }
    .analytics-pdf-export-sections input { width: 18px; height: 18px; flex: 0 0 auto; accent-color: #800000; }
    .analytics-pdf-export-footer { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .analytics-pdf-export-footer button:disabled { opacity: 0.5; cursor: not-allowed; }
    #pdfExportSelectionHint { color: #800000; }
</style>
