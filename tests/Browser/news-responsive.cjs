// CLI fixture rendering and browser-only edits; no news or announcements are saved.
process.env.PAGES ||= '/superadmin/announcements,/admin/announcements,/staff/announcements';
process.env.NEWS_CHECKS = '1';
process.env.WIDTHS ||= '320,375,390,480,600,767,768,820,1024,1025,1280,1920';
require('./cms-responsive.cjs');
