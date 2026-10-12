# AI Help Desk Architecture and AI Capabilities

PUP Taguig website and Botpress AI Help Desk | Revised 12 October 2026 | Asia/Singapore

## 1. Purpose and evidence basis

The AI Help Desk provides a conversational entry point to institutional information through the PUP Taguig website. The website is a Laravel application with public information pages and a role-controlled content management system (CMS). Botpress hosts the embedded conversation experience. A separate Python evaluator assesses historical replies against local institutional PDFs using an Ollama model.

This document revises the architecture description using the repository implementation, the earlier evaluator guide, current CSV reports and the system owner's production clarifications. The owner confirms that production knowledge indexing is verified and that Botpress provides the production retrieval-augmented generation (RAG) and language-model configuration. Production details supplied by the owner are distinguished from the local evaluator and Laravel source-code implementation. The exact deployment model identifier and detailed Botpress Studio configuration were not supplied with this revision.

## 2. Architecture and system boundaries

| Layer | Implemented component | Responsibility |
| --- | --- | --- |
| Public interface | Blade pages and widget-dock.js | Present institutional content and open the AI chat interface on demand. |
| Conversation service | Botpress-hosted shareable webchat | Receive user messages and deliver bot replies through the embedded interface. |
| Website application | Laravel 12 and PHP 8.2 or later dependency constraints | Serve pages, authenticate CMS users, manage approvals, feedback, audit records and administration. |
| Institutional content | Database records, downloadable files and external links | Supply website content and candidate knowledge sources. |
| Knowledge transport | Laravel discovery/upload pipeline and deployed Botpress knowledge indexing | Discover document links, extract text, track changes, upload files and make approved content retrievable; production indexing is confirmed by the system owner. |
| Event receiver | POST /api/botpress/webhook | Check a shared secret and log event/message/conversation identifiers. |
| Offline assessment | Python, local PDFs, BM25-style retrieval and Ollama | Estimate answer relevance, accuracy, faithfulness and evidence answerability. |
| Operations | Scheduler, backup services and CloudWatchService | Schedule maintenance and expose configured server-resource metrics. |

The browser loads Laravel pages, then creates the Botpress iframe when the visitor opens AI chat. The iframe sends chat traffic to the hosted Botpress service. No Laravel controller in the inspected path generates the assistant's live natural-language reply. The webhook is an inbound event logging endpoint, not the response-generation engine.

The content path is separate: authorized CMS actions maintain website records; discovery identifies candidate URLs; the sync service fetches and extracts documents; the adapter uploads text to Botpress; deployed Botpress indexing makes the configured knowledge content retrievable. The architecture diagram's Indexing verified label refers to the production status confirmed by the system owner. The evaluation path reads historical Botpress logs, reconstructs eligible turns, retrieves local PDF evidence, asks phi3:mini through Ollama for judgments, and writes CSVs. Botpress's configured production language model generates user-facing replies; phi3:mini is used only as the evaluator.

## 3. Visitor interaction and interface behavior

The widget uses a Botpress shareable webchat URL and creates its iframe only when needed. It supplies an AI Assistant title, a close/back control, and size adjustments for the chat panel. Opening chat is a user interaction; the code does not demonstrate a separate, informed chatbot privacy-consent process.

The chatbot supports text interaction. Voice processing and image understanding are not implemented. The iframe's existing allow attribute permits clipboard writing and microphone use, and its referrer policy is strict-origin-when-cross-origin. These browser permission declarations do not change the chatbot's implemented text-only capability.

Because the chat is a third-party iframe, first-load time, CDN reachability, the visitor's network and hosted chat availability can affect the experience. Server-log response latency excludes the delay before the visitor submits a message and the delay until the browser displays the reply.

## 4. AI capabilities and supported scope

The evaluated conversations demonstrate text question answering about institutional information. The local evidence collection covers academic programs, campus information, enrollment and admission, frequently asked questions, student services, and institutional systems or quick links. These categories establish the assessment documents; they are not a verified inventory of the production Botpress knowledge base.

The service scope is informational assistance: explaining published guidance and directing visitors to official sources or offices. As clarified by the system owner, the chatbot does not retrieve personal student information when answering questions and does not perform individual student transactions. It uses institutional knowledge content rather than authenticated student records. The conversation ID identifies the chat session; it is not a student-record lookup key.

Student-record access and conversation storage are separate concerns. The inspected Botpress logs contain message text and metadata, the Laravel webhook logs event/conversation/message identifiers and request IP, and the local evaluator saves questions, replies and message IDs alongside conversation IDs. Consequently, the system cannot be described as storing only a conversation ID or as guaranteed safe solely because it does not retrieve student records. Users can also voluntarily enter personal information in free-text messages.

| Capability or claim | Current configuration and scope |
| --- | --- |
| Embedded text conversation | Implemented website widget and recorded Botpress replies. |
| Institutional question answering | Observed in the 100-case evaluation sample. |
| Production retrieval-augmented generation configuration | Botpress retrieves configured indexed institutional knowledge for response generation; production indexing is owner-confirmed. |
| Production LLM and evaluation model | The production LLM is configured in Botpress. phi3:mini through Ollama judges saved replies only; the exact production model identifier was not supplied. |
| Multilingual support | Not implemented in the current release. The project serves PUPT users in the Philippines; an international multilingual rollout is outside its current scope. |
| Voice or image understanding | Not implemented; the chatbot accepts text questions for institutional assistance. |
| Personal student transactions and record retrieval | Not implemented. The chatbot does not retrieve personal student records; conversation logs and evaluation records are retained separately. |

### 4.1. Production retrieval-augmented generation configuration

The owner-confirmed production configuration uses Botpress for both knowledge retrieval and answer generation. Approved institutional content is indexed in the configured Botpress knowledge base. For an incoming text question, Botpress retrieves relevant knowledge content and supplies that context to its configured production language model to generate a reply. The hosted Botpress webchat then delivers the response to the visitor.

| RAG stage | Production configuration |
| --- | --- |
| Knowledge source | Institutional content assigned to the deployed Botpress knowledge base. |
| Indexing | Verified in production, as confirmed by the system owner. |
| Retrieval | Relevant indexed knowledge is retrieved through the configured Botpress knowledge workflow. |
| Generation | The language model selected in Botpress generates the user-facing answer using retrieved context. |
| Delivery | Botpress-hosted webchat embedded in the Laravel website. |
| Subsequent evaluation | Historical replies are assessed separately by phi3:mini using local PDF evidence; the judge does not generate production replies. |

The exact production model name/version, retrieval settings, embedding configuration, source inventory and system prompt were not provided. This description records the confirmed configuration at the component level without assigning unspecified model or retrieval parameters. The local evaluator's BM25 settings and phi3:mini model must not be presented as production RAG settings.

## 5. Evaluator architecture and AI judgment

BotpressClient reads paginated conversations and messages with a bearer token and bot identifier. It sorts messages chronologically, pairs incoming text with subsequent outgoing text, and combines multiple outgoing text parts before the next incoming turn. It excludes recognized feedback prompts, quota/limit notices and some cannot-answer replies before choosing up to 100 cases from up to 100 conversations.

The evaluator extracts text from all six local PDFs, creates overlapping text chunks, and uses BM25-style lexical ranking. Its query uses both the question and response, with greater weight on question terms. The local judge receives the question, assembled bot response and selected evidence, rather than all documents or an independently authored ideal answer.

The judge is phi3:mini in the current saved run. It returns relevancy, accuracy and faithfulness scores from 0 to 10, plus answerable and refusal flags. Python validates those values and applies a score threshold of 7. This is automated evaluation using an AI judge; numeric/schema validation does not independently verify facts.

The current sample contains 100 scored turns from 16 conversations, 99 quality passes and one failure. These cases are clustered within conversations and selected after filtering, so they do not establish universal capability or all-user accuracy. Detailed interpretation is provided in AI_HELP_DESK_EVALUATION.

## 6. Integration limits and deployment dependencies

Production knowledge indexing is verified according to the system owner's clarification. The inspected Laravel adapter handles file creation/upload and still contains an unfinished explicit knowledge-base association/indexing step. The confirmed deployed Botpress configuration and the source adapter are therefore separate evidence: production indexing confirmation does not establish that this adapter alone completes automatic indexing for every future update. New or changed content should be checked for retrieval in the deployed configuration.

The webhook checks X-BP-SECRET, logs event metadata and returns an empty success response. Its role in this architecture is event receipt and logging.

The Laravel scheduler defines link scanning at 01:30, Botpress synchronization at 02:00 and database backup processing at 02:30. These times follow the deployed application's timezone, which must be checked before treating them as local institutional times. Schedule definitions also require an operating scheduler; queued jobs require a functioning worker when used.

The repository includes an optional AWS keep-alive example that fetches a shareable webchat page. It does not prove deployment, prevent every outage, guarantee a warm production model, or demonstrate successful question answering. Current widget behavior loads chat on user interaction; the example README describes the keep-alive stack as optional for legacy always-on behavior.

## 7. Repository evidence and document relationships

- Website and framework: README.md, composer.json, bootstrap/app.php, routes/web.php.
- Interface: public/assets/js/widget-dock.js.
- Knowledge pipeline: app/Services/KnowledgeSync/KnowledgeSyncService.php and BotpressKnowledgeAdapter.php; config/knowledge_sync.php; routes/console.php.
- Webhook: routes/api.php, VerifyBotpressWebhook.php and BotpressWebhookController.php.
- Evaluator: botpress_eval/botpress_client.py, test_botpress.py, local_knowledge.py, knowledge.py and docs/evaluator_details.md; current evaluation_report.csv and f1_metrics.csv.
- Operations: app/Services/CloudWatchService.php and aws/botpress-keepalive/README.md.
- Production configuration and capability scope: system-owner clarifications supplied for this revision, including verified indexing, Botpress production RAG/LLM, no multilingual rollout, no voice/image understanding and no personal student-record retrieval.

The companion knowledge-management document describes content governance; the evaluation document reports metrics and unmeasured dimensions; the privacy/security assessment records code-level controls and gaps. This architecture document does not certify deployment status or regulatory compliance.
