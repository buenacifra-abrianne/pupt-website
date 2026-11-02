<link rel="stylesheet" href="{{ asset('assets/css/link-monitoring.css') }}?v={{ filemtime(public_path('assets/css/link-monitoring.css')) }}">
<section class="link-monitor" data-endpoint="{{ route('cms.link-monitoring') }}" aria-labelledby="linkMonitorTitle">
    <header class="link-monitor-header">
        <div>
            <h2 id="linkMonitorTitle"><i class="fas fa-link" aria-hidden="true"></i> Link Monitoring</h2>
            <p>Monitor page and external links in published CMS content, News, Announcements, and Campus Memorandum.</p>
        </div>
        <button type="button" class="btn btn-primary" id="linkMonitorScan"><i class="fas fa-rotate" aria-hidden="true"></i> Scan all links</button>
    </header>

    <div class="link-monitor-summary">
        <div><span>Links found</span><strong id="linkMonitorTotal">0</strong></div>
        <div><span>Working</span><strong id="linkMonitorWorking">0</strong></div>
        <div><span>Not Working</span><strong id="linkMonitorBroken">0</strong></div>
        <div><span>Not checked</span><strong id="linkMonitorUnchecked">0</strong></div>
    </div>

    <div class="link-monitor-progress" role="status" aria-live="polite" aria-atomic="true">
        <i class="fas fa-circle-notch" id="linkMonitorSpinner" aria-hidden="true" hidden></i>
        <span id="linkMonitorProgress">Ready to scan. Results appear as links are checked.</span>
    </div>
    <progress id="linkMonitorBar" value="0" max="1" aria-label="Links checked" hidden></progress>
    <p id="linkMonitorError" class="link-monitor-error" role="alert" hidden></p>

    <div class="link-monitor-filters">
        <label for="linkMonitorPage">Page
            <select id="linkMonitorPage"><option value="">All pages</option></select>
        </label>
        <label for="linkMonitorStatus">Status
            <select id="linkMonitorStatus">
                <option value="">All statuses</option>
                <option value="working">Working</option>
                <option value="not_working">Not Working</option>
                <option value="pending">Checking / Waiting</option>
                <option value="not_checked">Not checked</option>
            </select>
        </label>
    </div>

    <div class="link-monitor-table-wrap" tabindex="0" role="region" aria-label="Link results">
        <table class="link-monitor-table">
            <caption class="link-monitor-sr-only">Discovered links, their source pages and availability</caption>
            <thead><tr><th scope="col">Link</th><th scope="col">Found on page</th><th scope="col">Status</th><th scope="col">Result</th></tr></thead>
            <tbody id="linkMonitorRows"><tr><td colspan="4" class="link-monitor-empty">Start a scan to discover links across the website.</td></tr></tbody>
        </table>
    </div>
    <div class="link-monitor-pagination">
        <span id="linkMonitorCount">0 links</span>
        <div>
            <button type="button" class="btn btn-outline" id="linkMonitorPrevious" disabled aria-label="Previous results">Previous</button>
            <button type="button" class="btn btn-outline" id="linkMonitorNext" disabled aria-label="Next results">Next</button>
        </div>
    </div>
    <p class="link-monitor-note">Includes page links, external destinations, and linked document uploads. Images, scripts, stylesheets, fonts, and other page resources are excluded. A scan continues while this tab is open; reopen it to resume. Results are kept for two hours. Some external sites may block automated requests.</p>
</section>
<script src="{{ asset('assets/js/link-monitoring.js') }}?v={{ filemtime(public_path('assets/js/link-monitoring.js')) }}" defer></script>
