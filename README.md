# PUP Taguig Website (pupt-website)

This repository contains the source code for the Polytechnic University of the Philippines Taguig (PUPT) website. The system is built on Laravel and serves as both a public-facing informational portal and a robust internal Content Management System (CMS).

## System Overview

The PUP Taguig Website features a comprehensive Role-Based Access Control (RBAC) architecture that manages content delivery and administrative operations:
- **Public**: Access to home, about, academics, students, events, research pages, and feedback submission.
- **Staff**: Content contributors who draft News, Announcements, and Downloadable forms for review.
- **Admin**: Content managers who review, approve, and publish content submitted by staff.
- **Superadmin**: Full system control including user management, security incident response, database backups, and audit logs.

## Implemented Modules

- **Authentication & SSO**: Secure login via Single Sign-On integration (`OnePortal`) with MFA capabilities.
- **Content Management System (CMS)**: Tiered content creation and approval workflows.
- **Downloadable Forms**: Management of official documents for student access.
- **Audit Logs & Analytics**: Comprehensive action tracking, exportable analytics (PDF/Excel), and server health monitoring.
- **Security**: Automated IP blocking, account suspension, and incident response tracking.
- **Database Backups**: Built-in modules for manual database backup generation and retrieval.

## Technology Stack

- **Framework**: Laravel
- **Frontend**: Blade templates, Vanilla CSS/JS, bundled via Vite.
- **Database**: MySQL / MariaDB (configurable via `.env`).

## Website-to-Botpress Link Discovery Sync (Lightweight)

This project now includes a backend-only service that discovers website links, safely fetches readable content, and syncs it to Botpress.

### What It Scans

- Rendered public pages (including footer/header/navbar links)
- CMS content (`cms_contents`, `announcements`, `news`, `downloadables`)
- Hardcoded frontend templates (`resources/views`, `resources/js`, `public/assets/components`)
- `sitemap.xml`
- Public GET routes
- Manual URLs from env (`KNOWLEDGE_SYNC_MANUAL_URLS`)

### Safety Controls

- Allows only `http` and `https`
- Blocks `file:`, `ftp:`, `javascript:`, `data:`, `mailto:`, `tel:`
- Blocks localhost/private/reserved/link-local/loopback ranges
- Re-validates redirects and final URL
- Enforces timeout, redirect, response-size, and extracted-text limits
- Enforces content-type allowlist:
  - `text/html`
  - `text/plain`
  - `application/pdf`
  - `application/vnd.openxmlformats-officedocument.wordprocessingml.document`

### Extraction

- HTML: removes script/style/noscript and extracts readable text from title/headings/body
- PDF: lightweight text extraction fallback
- DOCX: extracts `word/document.xml` text only

### Commands

- `php artisan scan:links`
- `php artisan sync:botpress`
- `php artisan sync:url {url}`

Queue mode:

- `php artisan scan:links --queue`
- `php artisan sync:botpress --queue`
- `php artisan sync:url {url} --queue`

### Schedule

Configured in `routes/console.php`:

- `scan:links` daily at `01:30`
- `sync:botpress` daily at `02:00`

### Environment Variables

Add to `.env`:

```env
KNOWLEDGE_SYNC_BASE_URL=${APP_URL}
KNOWLEDGE_SYNC_MAX_DEPTH=2
KNOWLEDGE_SYNC_MAX_PAGES=80
KNOWLEDGE_SYNC_MANUAL_URLS=

KNOWLEDGE_SYNC_FETCH_TIMEOUT=15
KNOWLEDGE_SYNC_FETCH_CONNECT_TIMEOUT=8
KNOWLEDGE_SYNC_FETCH_MAX_REDIRECTS=3
KNOWLEDGE_SYNC_FETCH_MAX_BYTES=5000000
KNOWLEDGE_SYNC_MAX_TEXT_BYTES=200000

BOTPRESS_API_BASE_URL=https://api.botpress.cloud
BOTPRESS_PAT=
BOTPRESS_BOT_ID=
BOTPRESS_KNOWLEDGE_BASE_ID=
BOTPRESS_FILE_KEY_PREFIX=knowledge-sync
BOTPRESS_WEBHOOK_SECRET=
BOTPRESS_WEBHOOK_URL=${APP_URL}/api/botpress/webhook
```

### Migration

Run:

```bash
php artisan migrate
```

The service stores minimal metadata in `botpress_knowledge_links`:

- `url`
- `content_hash`
- `sync_status`
- `last_synced_at`

(plus operational fields needed for reliability: active flag, error, file id, timestamps)

### Webhook entrypoint

To keep Botpress from responding until a real chat event occurs, wire your Botpress workflow to call `BOTPRESS_WEBHOOK_URL` from a `Conversation Started` trigger.

Send the same value from `BOTPRESS_WEBHOOK_SECRET` as the `X-BP-SECRET` header so the Laravel webhook can verify the request.

Botpress docs that match this pattern:

- Conversation-start triggers: https://botpress.com/docs/webchat/interact/start-trigger
- Webhook events: https://www.botpress.com/docs/integrations/integration-guides/webhook
- Webchat event listeners: https://botpress.com/docs/webchat/interact/listen-to-events

### Botpress Notes

`App\Services\KnowledgeSync\BotpressKnowledgeAdapter` uses Botpress Files API as the sync transport.

There are explicit `TODO` notes in the adapter where latest Botpress Knowledge Base-specific endpoint contracts must be confirmed and finalized.
