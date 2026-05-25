<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

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

### Botpress Notes

`App\Services\KnowledgeSync\BotpressKnowledgeAdapter` uses Botpress Files API as the sync transport.

There are explicit `TODO` notes in the adapter where latest Botpress Knowledge Base-specific endpoint contracts must be confirmed and finalized.
