const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function setup() {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/superadmin/dashboard.blade.php'), 'utf8');
    const functions = source.slice(source.indexOf('function exportPdf() {')).split('</script>')[0];
    let submissions = 0;
    let selected = true;
    let hasContent = true;
    const elements = {
        analyticsPdfExportModal: {
            open: false,
            showModal() { this.open = true; },
            close() { this.open = false; },
        },
        confirmPdfExportButton: { disabled: false },
        pdfExportSelectionHint: { hidden: true },
        analyticsStart: { value: '2026-11-01' },
        analyticsEnd: { value: '2026-11-02' },
        exp_start: {}, exp_end: {}, exp_payload: {},
        exportPdfForm: { submit() { submissions++; } },
    };
    const context = vm.createContext({
        document: {
            getElementById: id => elements[id],
            querySelector: () => selected ? {} : null,
        },
        hasAnalyticsExportContent: () => hasContent,
        buildAnalyticsExportPayload: () => ({ kpis: { total_visitors: '30,336' } }),
        showToast() {},
    });
    vm.runInContext(functions, context);
    return { context, elements, submissions: () => submissions, select: value => { selected = value; }, content: value => { hasContent = value; } };
}

test('opening and cancelling the modal never exports a PDF', () => {
    const state = setup();
    state.context.exportPdf();
    assert.equal(state.elements.analyticsPdfExportModal.open, true);
    assert.equal(state.submissions(), 0);
    state.elements.analyticsPdfExportModal.close();
    state.context.confirmPdfExport();
    assert.equal(state.submissions(), 0);
});

test('confirmation exports current metrics and date range once, then closes the modal', () => {
    const state = setup();
    state.context.exportPdf();
    state.context.confirmPdfExport();
    assert.equal(state.submissions(), 1);
    assert.equal(state.elements.exp_start.value, '2026-11-01');
    assert.equal(state.elements.exp_end.value, '2026-11-02');
    assert.equal(JSON.parse(state.elements.exp_payload.value).kpis.total_visitors, '30,336');
    assert.equal(state.elements.analyticsPdfExportModal.open, false);
    state.context.confirmPdfExport();
    assert.equal(state.submissions(), 1);
});

test('an empty selection disables export and cannot submit', () => {
    const state = setup();
    state.context.exportPdf();
    state.select(false);
    state.context.updatePdfExportSelection();
    assert.equal(state.elements.confirmPdfExportButton.disabled, true);
    assert.equal(state.elements.pdfExportSelectionHint.hidden, false);
    state.context.confirmPdfExport();
    assert.equal(state.submissions(), 0);
    state.select(true);
    state.context.updatePdfExportSelection();
    assert.equal(state.elements.confirmPdfExportButton.disabled, false);
    assert.equal(state.elements.pdfExportSelectionHint.hidden, true);
});

test('empty analytics cannot open the export modal', () => {
    const state = setup();
    state.content(false);
    state.context.exportPdf();
    assert.equal(state.elements.analyticsPdfExportModal.open, false);
    assert.equal(state.submissions(), 0);
});
