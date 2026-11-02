// Run: node tests/Browser/audit-responsive.cjs (Chrome and the local Laravel database required).
// Uses the CLI-only CMS fixture renderer; all interaction fixtures live in the browser.
process.env.PAGES = '/superadmin/audit';
process.env.AUDIT_CHECKS = '1';
process.env.WIDTHS ||= '320,375,390,480,768,820,1024,1280,1920';
require('./cms-responsive.cjs');
