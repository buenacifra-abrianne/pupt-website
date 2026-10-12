# AI Help Desk Knowledge Base and Content Management

PUP Taguig website and Botpress AI Help Desk | Revised 12 October 2026 | Asia/Singapore

## 1. Content scope and distinct evidence stores

The system has three related but distinct content stores: institutional website/CMS content, files uploaded or configured in Botpress, and local PDFs used by the evaluator. The revised description separates them because the evaluator does not fetch the production Botpress knowledge base in its current live scoring path.

Website content is managed through the Laravel CMS. The synchronization pipeline discovers candidate links from that content and other sources, then attempts to upload extracted text. The evaluator instead reads the six PDFs in botpress_eval/knowledge_base. Similar subject matter does not prove that the three stores have identical facts, versions or coverage.

## 2. Local evaluation documents

| Knowledge document | Pages | Subject area |
| --- | --- | --- |
| Academic-Programs.pdf | 4 | Academic programs and related institutional information. |
| Campus-Information.pdf | 6 | Campus information and institutional context. |
| Enrollment-and-Admission.pdf | 4 | Enrollment and admission guidance. |
| Frequently-Asked-Questions.pdf | 6 | Common institutional questions. |
| Student-Services.pdf | 4 | Student-facing services and guidance. |
| Systems-and-Quick-Links.pdf | 4 | Institutional systems and reference links. |
| Total | 28 | Six inspected local documents. |

Page counts describe the source files, not a guarantee that every page yields readable text. The loader processes nonrecursive PDF files in sorted filename order using pypdf. Pages without extracted text are skipped; a file with no readable text raises an OCR-required error. OCR is not performed automatically.

Facts in a current PDF can differ from the facts that were valid when a historical chat occurred. Dates, fees, admission schedules, office contacts and links therefore require version-aware checking before a historical answer is labeled incorrect.

## 3. CMS ownership and publication workflow

The public website includes institutional information pages, news/announcements and downloadable materials. Staff, admin and superadmin functions provide different levels of content control. Access is also constrained by assigned CMS tabs and roles; access is not determined solely by a generic staff/admin label.

Staff contributors submit content for review. Admin approval controllers support review and publication, while the superadmin CMS controller can update authorized sections directly. Audit records capture supported content actions. Direct superadmin updates should therefore be distinguished from staff submissions that pass through approval.

| Activity | Existing mechanism | Documentation implication |
| --- | --- | --- |
| Create or revise institutional content | Role-controlled CMS controllers and validation | Record the responsible section and publishing actor. |
| Review submissions | Approval requests and admin/superadmin approval controllers | Preserve approval outcomes and effective publication dates. |
| Publish official documents | Downloadable management and storage helpers | Review file accessibility and sensitive-data exposure before knowledge use. |
| Track changes | AuditLog and content records | Audit actions do not establish the correctness of the published facts. |
| Refresh chatbot material | Discovery/sync commands and optional queued jobs | Verify file indexing and retrieval after transport succeeds. |

Recommended governance assigns each content category an institutional owner, reviewer, effective date and review date. These fields are a proposed operating record, not a claim that all are currently enforced by database schema. High-change material should be reviewed when the institution publishes an update rather than relying only on a scheduled scan.

## 4. Link discovery and document selection

WebsiteLinkDiscoveryService inspects available CMS fields, templates, internal-page links, sitemap links and configured manual URLs. UrlNormalizer resolves URLs and removes selected tracking parameters. Candidate selection accepts qualifying external URLs and internal document URLs; discovering a web page does not mean every internal page body becomes a knowledge document.

The scan maintains BotpressKnowledgeLink records with URL, activity state, discovery timing and sync status. Newly discovered links become pending, previously inactive links can be reactivated, and undiscovered links can be marked inactive. The implementation avoids bulk inactivation when a scan yields no URLs; an empty result is not treated as reliable proof that all content was removed.

Because discovery includes templates and stored content, source authorization should be reviewed explicitly. Link presence is not proof that a resource is current, official, public or suitable for AI use. Confidential administrative records and unpublished material should not be included simply because a URL is discoverable.

## 5. Fetching, extraction and synchronization

The configured fetch pipeline accepts HTML, plain text, PDF and DOCX content, with limits on redirects, response size, connection duration and extracted-text size. Default limits include a 15-second request timeout, 8-second connection timeout, three redirects, 5,000,000 response bytes and 200,000 extracted-text bytes. These are configuration defaults, not measurements of current production load.

URL validation blocks configured local/metadata hosts and private/reserved addresses unless the configured internal base host is explicitly trusted. The fetcher checks content type and document size. However, it currently disables TLS certificate verification, and redirected URLs are checked after the client follows them. These implementation gaps are described in the privacy/security assessment; the controls must not be presented as complete SSRF or transport-security protection.

TextExtractor converts supported content into text and a title. KnowledgeSyncService computes a SHA-256 hash of extracted text, skips unchanged content already marked synced, and updates tracked file IDs, errors and last-success time. No readable text is recorded as skipped. A failed request/upload is recorded as failed.

The adapter creates a stable file key from a URL hash, uploads a title, source URL and extracted text, and receives a Botpress file ID. Removed/inactive records with file IDs are submitted for deletion; a failed deletion can leave the file ID attached to an inactive record. Administrative inactivity alone therefore does not prove remote content removal.

## 6. Upload success versus searchable knowledge

The adapter marks success after upload, but its knowledge-base linkage step remains unfinished and it does not request indexing in the inspected upsert payload. It also does not verify indexing_completed before marking the local record synced. The configured knowledge-base ID is not used to establish an explicit searchable association in this path.

Botpress's official Files API guide describes indexing as an explicit operation and notes that completion is asynchronous. The repository's upload result should therefore be treated as transport success, pending confirmation that the bot's retrieval configuration can use the uploaded content. [Botpress: Index and search files](https://botpress.com/docs/api-reference/files-api/how-tos/index-and-search-files/).

A suitable acceptance record would capture the file ID, intended knowledge-base association, indexing status, retrieval result, test question and observed answer. This is a recommended verification process; these records were not supplied as measured outcomes. Automatic freshness and complete website-to-bot synchronization cannot presently be claimed.

## 7. Evaluation indexing and retrieval

The local PDF cache uses SHA-256 signatures for every file. Reuse requires the same filename/signature mapping, cache version 1 and nonempty passages. A changed, added or removed PDF causes text extraction for all documents to be rebuilt. Cached page records preserve filename, page number and passage identifier.

KnowledgeIndex splits page text into chunks of up to 1,800 characters at 1,600-character intervals, giving adjacent chunks up to 200 characters of overlap. It uses word tokens and BM25-style lexical ranking. Question-term frequency is weighted three times before response terms are added; term weights are capped during scoring. There is no embedding model, semantic synonym expansion or translation in this evaluator.

Only retrieved chunks that fit the configured evidence-character budget are sent to the judge; the default budget is 2,400 characters. Indexing all PDFs does not mean the judge sees every passage. Including answer terms can bias retrieval toward the reply being evaluated. A missing lexical match is not proof that the institution has no answer.

The CSV stores selected source headers rather than verified quotations, full prompt text or a frozen manifest of the documents for each run. Preserve the original PDFs, hashes, evaluator settings and model version for reproducible historical assessments. The current cache can be overwritten and is not a permanent run archive.

## 8. Scheduling and operational responsibilities

routes/console.php schedules link scanning daily at 01:30 and Botpress synchronization at 02:00, using application-timezone settings. A deployed Laravel scheduler is required. Queue-based jobs define retry/backoff settings, but a sync service failure can return a failed result without throwing an exception; that outcome does not automatically trigger queue retries.

Proposed ownership is: content owners approve facts; CMS reviewers authorize publication; administrators check discovery and sync failures; the Botpress maintainer confirms indexing and retrieval; researchers preserve dated evidence snapshots. Test a changed document and a withdrawn document end to end before claiming automatic update or removal reliability.

## 9. Repository evidence

- CMS: app/Http/Controllers/Staff, Admin and Superadmin CMS, announcement, downloadable and approval controllers; app/Support/CmsSections.php and AuditLog.php.
- Discovery and transport: app/Services/KnowledgeSync/WebsiteLinkDiscoveryService.php, UrlNormalizer.php, SafeHttpFetcher.php, TextExtractor.php, KnowledgeSyncService.php and BotpressKnowledgeAdapter.php.
- Tracking/configuration: app/Models/BotpressKnowledgeLink.php, config/knowledge_sync.php, routes/console.php and app/Jobs/SyncBotpressKnowledgeJob.php.
- Local assessment documents: botpress_eval/knowledge_base; local_knowledge.py; knowledge.py; docs/evaluator_details.md.

This document records the current source-management implementation and its limits. It does not establish that every official source is complete, current or already indexed in the hosted bot.
