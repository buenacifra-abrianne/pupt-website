<link rel="stylesheet" href="{{ asset('assets/css/shared/cms-version-history.css') }}?v={{ filemtime(public_path('assets/css/shared/cms-version-history.css')) }}">
<dialog id="cms-version-history" class="cms-history-dialog"
    aria-labelledby="cms-history-heading"
    data-base-url="{{ url(isset($historyScope) ? '/superadmin/publication-history' : '/superadmin/content/history') }}"
    @isset($historyScope)
        data-scope="{{ $historyScope }}"
        data-label="{{ $historyLabel }}"
    @endisset>
    <header class="cms-history-header">
        <div>
            <p class="cms-history-eyebrow">SUPERADMIN WORKSPACE</p>
            <h2 id="cms-history-heading">Version history</h2>
            <p id="cms-history-subtitle">Review database backups stored in S3 and restore earlier CMS content.</p>
        </div>
        <button type="button" class="cms-history-button" id="cms-history-close" aria-label="Close version history">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg><span>Close</span>
        </button>
    </header>
    <div class="cms-history-layout">
        <section class="cms-history-workspace" aria-label="Selected backup">
            <div class="cms-history-selection">
                <div><h3 id="cms-history-selected">Select a version</h3><p id="cms-history-author"></p></div>
                <button type="button" id="cms-history-restore" class="cms-history-button cms-history-primary" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 11a9 9 0 1 1 2.6 7M3 4v7h7"/></svg> Restore this version
                </button>
            </div>
            <p class="cms-history-notice">{{ isset($historyScope) ? \App\Support\PublicationBackupContent::definition($historyScope)['notice'] : 'Restores the entire selected CMS page, including its sections, images, and document links. Other CMS pages and separately managed news, announcements, and downloads are unchanged.' }}</p>
            <div id="cms-history-message" class="cms-history-message" role="status" aria-live="polite"></div>
            <div class="cms-history-controls">
                <label>View
                    <select id="cms-history-view"><option value="changes">Changes</option><option value="preview">Page preview</option></select>
                </label>
                <label id="cms-history-compare-label">Compare with
                    <select id="cms-history-compare"><option value="previous">Previous S3 backup</option><option value="live">Live content</option></select>
                </label>
                <label id="cms-history-page-label" hidden>Preview page<select id="cms-history-page"></select></label>
                <label id="cms-history-filter-label" class="cms-history-checkbox"><input type="checkbox" id="cms-history-changed" checked> Changes only</label>
            </div>
            <div id="cms-history-diff" class="cms-history-diff"></div>
            <iframe id="cms-history-preview" title="Read-only historical CMS page preview" sandbox="allow-scripts" hidden></iframe>
        </section>
        <aside class="cms-history-sidebar" aria-label="S3 database backups">
            <div class="cms-history-sidebar-heading"><h3>S3 database backups</h3><span id="cms-history-total"></span></div>
            <p class="cms-history-sidebar-note">Versions come from database backup archives stored in S3. Create backups from the dashboard to add restore points.</p>
            <div id="cms-history-list" aria-busy="false"></div>
            <button type="button" id="cms-history-more" class="cms-history-button" hidden>Load older versions</button>
        </aside>
    </div>
    <section id="cms-history-confirm" class="cms-history-confirm" aria-label="Confirm version restore" hidden>
        <h3>Restore this page?</h3>
        <p id="cms-history-confirm-text"></p>
        <p>A fresh database backup will be saved to S3 before restoring. Unsaved edits in the editor will be discarded.</p>
        <div><button type="button" class="cms-history-button" id="cms-history-cancel">Cancel</button><button type="button" class="cms-history-button cms-history-primary" id="cms-history-confirm-restore">Confirm restore</button></div>
    </section>
</dialog>
<script type="application/json" id="cms-history-pages">{!! json_encode([
    'home' => ['overview' => 'Home'],
    'about' => array_merge(['overview' => 'About overview'], array_combine(\App\Support\AboutCmsContent::sectionSlugs(), array_map(fn ($slug) => ucwords(str_replace('-', ' ', $slug)), \App\Support\AboutCmsContent::sectionSlugs()))),
    'academics' => ['overview' => 'Academics overview', 'degree-programs' => 'Degree programs', 'diploma-programs' => 'Diploma programs', 'pup-iapply' => 'PUP iApply', 'university-calendar' => 'University calendar'],
    'students' => ['overview' => 'Students overview', 'admissions' => 'Admissions', 'downloadable-forms' => 'Downloadable forms', 'document-requests' => 'Document requests'],
    'research_extension' => ['overview' => 'Research overview', 'strategic-development-plan' => 'Strategic development plan'],
    'events' => ['overview' => 'Events'],
    'news_announcements' => ['overview' => 'News & Announcements'],
    'memoranda' => ['overview' => 'Campus Memorandum'],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('assets/js/cms-version-history.js') }}?v={{ filemtime(public_path('assets/js/cms-version-history.js')) }}" defer></script>
