// Read-only browser checks. New data and action fixtures exist only in the browser DOM.
process.env.PAGES ||= '/superadmin/accounts,/superadmin/security/incidents,/superadmin/approvals/pending,/admin/approvals/pending,/superadmin/dashboard?tab=database-backups,/superadmin/dashboard?tab=analytics,/superadmin/analytics/export/pdf';
process.env.TABLE_CHECKS = '1';
process.env.WIDTHS ||= '320,375,390,480,768,820,1024,1280,1920';
require('./cms-responsive.cjs');
