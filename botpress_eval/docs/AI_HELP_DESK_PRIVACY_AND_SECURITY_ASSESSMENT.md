# AI Help Desk Privacy and Security Assessment

PUP Taguig website and Botpress AI Help Desk | Revised 12 October 2026 | Asia/Singapore

## 1. Assessment scope and method

This assessment examines code-level data flows and controls in the Laravel website, embedded Botpress chat, knowledge-sync pipeline and local evaluator. It is based on repository inspection and current documentation. It is not a penetration test, deployed-configuration audit, certification or legal compliance determination. A source-code control can exist without being enabled correctly in production.

The existing institutional privacy links and CMS notice are recorded as interface features. Their wording alone does not establish lawful processing, adequate consent, vendor contractual protections or compliance with a particular statute. No production secrets, raw user conversations or user identifiers are reproduced in this document.

## 2. Data inventory and processing boundaries

| Data category | Processing/storage observed | Main consideration |
| --- | --- | --- |
| Public chat messages | Visitor browser and Botpress-hosted iframe; conversation logs read by evaluator | Free text can include identifying or sensitive information even when the service asks general questions. |
| Evaluator records | Local CSVs and ignored report archives | Full questions/replies and conversation/message IDs persist after analysis. |
| Retrieved evidence | Local PDFs, extracted-text cache, selected passages sent to Ollama | Limit documents to approved information and preserve access controls. |
| CMS account/session information | Laravel session/authentication and identity-provider integration | Protect tokens, role assignments, MFA secrets and account metadata. |
| Public analytics | Visitor UUID cookie, session UUID, paths, timestamps and view counts | Persistent tracking needs clear purpose and retention decisions. |
| Security/audit events | IP address, email/actor metadata, action and incident records | Operational logs may identify users and require restricted access. |
| Webhook metadata | Event type, conversation ID, message ID and request IP in logs | Shared-secret authentication does not remove the need for replay/idempotency controls. |
| Database backups | ZIP archives stored through configured local/S3 storage | Backups can reproduce personal and security-sensitive database data. |

The embedded chat communicates with a third-party hosted service. CMS terms apply to CMS access and do not demonstrate that public chat users received a chat-specific notice. Botpress retention, provider subprocessing, hosting region, deletion behavior and production model configuration were not supplied and remain verification requirements.

The evaluator reads logs using environment-loaded Botpress credentials. Its default Ollama URL is local; configuring a remote URL would send the question, response and selected evidence to that server. The current CSV and cache files have no application-level redaction or encryption step in the inspected writer. Operating-system/disk protection and administrative access were not audited.

## 3. Existing authentication and access controls

The website defines session authentication, role middleware and assigned-section CMS access. Protected groups combine authentication with role checks, identity-provider checks and CMS-terms acceptance. Authorization should be assessed per action/route, rather than assuming every controller inherits a universal role guarantee.

Superadmin MFA uses time-based one-time passwords and backup codes. Backup codes are hashed and consumed after successful use. The inspected setup writes mfa_secret directly to the database and reads it directly for verification; this path does not apply field encryption to the TOTP secret. Session data includes temporary setup material until completion.

Session defaults include an HttpOnly session cookie and SameSite=lax, while secure-cookie behavior is environment-dependent and session-payload encryption defaults to false. These defaults do not prove the active deployed settings. Review HTTPS, proxy handling, session regeneration/expiry and token storage as part of deployment validation.

The Botpress webhook compares the configured secret with the X-BP-SECRET header using hash_equals and rejects empty or mismatching secrets. This is a shared-secret check, not a signed timestamped payload. There is no explicit replay or duplicate-event guard in the inspected handler. Future ticket creation or staff notification would need idempotency to prevent duplicate actions.

## 4. Input protection and incident handling

Laravel's web middleware provides the framework CSRF protection path, while controller validation and rich-text sanitization constrain supported submissions. RichText uses allowed tags/attributes and removes disallowed content. These implementation mechanisms require verification on the actual routes that receive content.

FirewallMiddleware blocks stored contained/blacklisted IP addresses. ActiveSecurityTracker scans URLs and request values for selected SQL injection, script/XSS and path-traversal patterns. Pattern matching is a supplementary heuristic: it can miss malicious variants and can block legitimate text. It should not be described as complete prevention of these attack classes or as a replacement for query parameterization, output encoding and authorization.

IncidentResponseService records security failures and uses a three-failure threshold within a 900-second limiter window to initiate a 15-minute temporary IP block. It records containment and notifies superadmins. Resetting remember tokens does not prove that all active database sessions or external identity-provider tokens are invalidated. Shared institutional network addresses can also cause unrelated users to be blocked together.

## 5. Retention, backup and deletion assessment

The system has configurable database-backup storage and retention processing. Backup archives are generated as ZIP files; the inspected service does not establish archive encryption. Storage-provider encryption, private bucket policies, administrative download authorization and restore procedures must be reviewed in the deployed configuration rather than assumed from the existence of a backup module.

The current evaluator has no automatic redaction or expiry for report CSVs and archives. Conversation deletion in Botpress, deletion of local CSVs, purge of backups and deletion of logs are separate operations. Establish retention periods for each category, assign an owner and record how deletion propagates across copies. A withdrawn knowledge-link record does not prove that its remote file or archived evidence was deleted successfully.

The CMS-terms acceptance controller saves an accepted flag and timestamp in the session. This is not demonstrated as a durable, versioned consent register, and it does not cover public-chat processing by itself. Preserve the applicable notice version and review process without mischaracterizing session acceptance as universal consent.

## 6. AI-specific privacy and security limits

Free-text prompts can contain personal information or instructions designed to alter model behavior. The evaluator prompt tells its judge to treat question/answer/evidence as data, but this is not a guarantee of prompt-injection resistance. The production Botpress system prompt, tool permissions and knowledge-source trust rules were not exported, so production defenses cannot be assessed completely.

Restrict the help desk to approved informational sources, avoid requesting passwords or sensitive student records, and direct account-specific matters through authenticated institutional channels. These are proposed operating controls; their enforcement by the hosted bot has not been demonstrated. Grounded-answer scores do not establish privacy protection or resistance to malicious prompts.

The iframe permits microphone use and clipboard writing. Review whether those permissions are needed for the enabled experience and whether the hosted page uses them. An allow attribute alone does not demonstrate that recordings are collected, and the document makes no such claim.

## 7. Validation plan and current conclusion

The code provides role checks, MFA, webhook-secret verification, input validation, sanitization, incident recording and IP containment. These controls require validation against the deployed configuration before the study can claim fully verified security.

Recommended validation should use a controlled test environment: check denied role access and CMS-section boundaries; confirm CSRF behavior on applicable routes; verify webhook rejection, replay behavior and payload limits; test outbound redirect/private-address rejection before contact; inspect logs for secret values using redacted results; verify secure cookies and encrypted sensitive fields; and perform a backup restore with access/deletion checks. Do not use production personal conversations as test payloads.

The existing evaluator's 29 passing offline tests concern evaluation/statistical behavior, not this security assessment. No penetration-test result, regulatory certification, deletion exercise, vendor retention review or production security sign-off is asserted. Validation results and deployment evidence should be retained before stronger privacy/security claims are added to the study.

## 8. Repository evidence

- Auth/session: routes/web.php; config/session.php; CheckIdpSession.php; SuperadminAuth.php; SuperadminRole.php; NonSuperadminRoleOnly.php; MfaController.php.
- Webhook/debug route: routes/api.php; VerifyBotpressWebhook.php; BotpressWebhookController.php.
- Fetch controls: UrlSafetyValidator.php, SafeHttpFetcher.php and BotpressKnowledgeAdapter.php under app/Services/KnowledgeSync.
- Data/diagnostics: TrackPublicAnalytics.php, ActiveSecurityTracker.php, app/Support/RichText.php, AuditLog.php and app/Services/IncidentResponseService.php.
- Notices/widget: CmsTermsController.php, resources/views/components/app/terms-modal.blade.php, landing-footer.blade.php and public/assets/js/widget-dock.js.
- Backup/evaluator: DatabaseBackupService.php, config/database_backups.php, botpress_eval/test_botpress.py, compute_latency.py, local_knowledge.py and .gitignore.
