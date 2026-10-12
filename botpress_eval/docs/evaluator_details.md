# Botpress Evaluator: Complete Process and Record Guide

Prepared 7 October 2026 | Asia/Singapore
Implementation and report snapshot from this project

## 1. Purpose, scope, and current result

This evaluator assesses previously recorded PUP Taguig chatbot answers against evidence extracted from local knowledge PDFs. It reads real conversation logs from Botpress, selects eligible question-and-answer turns, retrieves relevant PDF passages, and asks a local Ollama model to judge answer quality. Python validates the model output, assigns pass/fail and answer counts, and writes CSV records. It does not send new questions to the chatbot or measure a new chatbot response.

The current case cap is 100 answered turns, drawn from up to 100 conversations. A case is one user question plus its collected bot response; it is not a whole conversation or one raw message. One conversation can contribute several cases.

The records inspected for this guide contain 100 total cases, 100 scored cases, 0 evaluation errors, and 0 legacy/unscored cases. There are 99 passing cases and 1 failing case. Precision, recall, and F1 are each 0.9900; pass rate is 99.00%. Average relevancy is 9.240/10, accuracy 9.780/10, and faithfulness 9.780/10. These are AI-derived results for the selected population, not independently labeled ground truth.

Answer counts are TP=99, FN=1, FP=1, TN=0. The failed substantive answer can contribute both FP and FN, so these counts total 101 despite there being 100 cases. There are no scored refusals in this snapshot. Common refusal replies are excluded upstream, which limits what this sample says about refusal behavior.

Runtime was sampled separately on one existing case: 18.6 seconds for evaluation. A projection for 100 cases, including the default pauses, is approximately 34.3 minutes plus collection and setup. A planning allowance of 35-45 minutes is an estimate, not a measured full-run duration. The CSVs do not store evaluation timings.

This guide describes the executable Python code. Some older sections of README.md describe superseded behavior, including generated quotes, remote knowledge fetching, and a 384-token output cap. The current live path uses local PDFs, no generated quotes, and a 192-token output cap.

## 2. Files, dependencies, and execution

- test_botpress.py: live pytest collection, shared preflight, Ollama judging, validation, per-case scoring, CSV writing, and model unloading.
- botpress_client.py: authentication, paginated conversation/message reads, turn construction, filtering, sorting, and optional remote knowledge helper.
- local_knowledge.py: local PDF discovery, text extraction, SHA-256 cache signatures, and cached page records.
- knowledge.py: overlapping text chunks and BM25-style lexical evidence ranking.
- compute_f1.py: reads the current case CSV, computes aggregate metrics, writes the summary CSV, and prints a console report.
- tests/test_pipeline.py: mocked offline verification of pairing, pagination, retrieval, validation, archives, filtering, counts, and PDF caching.

Declared evaluator packages are requests, python-dotenv, pytest, and pypdf. Python and Ollama are also required. The configured model must already be installed in Ollama. The project's .venv interpreter contains pypdf; the default system Python used during the preceding check did not. Offline tests passed: 26 tests in 0.56 seconds using the project environment.

Run these commands from the project root in PowerShell, sequentially:

```powershell
Set-Location botpress_eval
../.venv/Scripts/python.exe -m pytest test_botpress.py -s
../.venv/Scripts/python.exe compute_f1.py
```

Run the summary command after evaluation even if pytest reports failed answers. Failures are still recorded. Do not run multiple live evaluator processes or pytest-xdist against the same reports: the writer has no interprocess locking.

Offline verification, from botpress_eval:

```powershell
../.venv/Scripts/python.exe -m pytest tests -q
```

Knowledge PDFs belong in botpress_eval/knowledge_base/. Botpress credentials belong in its ignored .env. No OpenAI or Gemini API key is used by this live evaluation path. Credentials and original conversation text are omitted from this guide.

## 3. Configuration: every active control

botpress_client.py loads botpress_eval/.env on import using python-dotenv. Existing process environment variables take precedence. Several settings are captured when test_botpress.py imports; other settings are read during each evaluation. Start a new process after changing settings.

- BOTPRESS_API_TOKEN: primary token; BOTPRESS_PAT is the fallback. One token and BOTPRESS_BOT_ID are required. Authorization is sent as Bearer token, with x-bot-id identifying the bot.
- MAX_CONVERSATIONS = 100: maximum conversations requested from the recent-conversation listing. Must be positive. Raising the case cap does not raise this separate collection cap.
- MAX_TEST_CASES = 100: maximum eligible turns passed to pytest. Must be positive. This is a ceiling, not a promise that 100 eligible turns exist.
- PASS_THRESHOLD = 7: minimum score for each of the three metrics. Valid range is 0-10; decimals are permitted.
- OLLAMA_MODEL = phi3:mini: local judge model.
- OLLAMA_URL = http://localhost:11434: Ollama base address; trailing slashes are removed. A configured remote address would send the judging inputs to that server.
- OLLAMA_TIMEOUT = 90: requests timeout in seconds for each generation call. This is not a whole-run timer.
- OLLAMA_NUM_CTX = 4096: model context setting, minimum 2048 enforced by this evaluator.
- KB_CONTEXT_CHARS = 2400: maximum retrieved evidence string size, minimum 1800 enforced. This counts characters, not tokens.
- OLLAMA_NUM_THREADS = 2: CPU thread setting passed to Ollama.
- OLLAMA_NUM_PREDICT = 192: maximum model output tokens. Hitting the length limit is an error even if some output exists.
- EVALUATION_ATTEMPTS = 1: generation attempts for invalid generated judgments. Values below 1 are clamped to 1. Network failures do not use this correction loop.
- EVALUATION_PAUSE_SECONDS = 2: pause before every case except the first. Negative values are clamped to zero.

Fixed settings are temperature=0, num_batch=64, stream=false, and keep_alive="1m". Botpress GET requests use a 60-second timeout; the Ollama model-list preflight and unload call each use a 10-second timeout. The live path does not use workspace URL/ID or BOTPRESS_KNOWLEDGE_BASE_ID to select evidence.

The effective settings inspected for this guide match the defaults listed above. Malformed numeric environment strings can raise conversion errors; not every such configuration error becomes a saved case row.

## 4. Process from start to finish

1. Python imports the evaluator and client, loads .env, and resolves configuration.
2. During pytest collection, validate positive conversation/case limits and fetch Botpress conversation logs.
3. Read messages, assemble text question/answer turns, filter unsuitable replies, sort the collected eligible turns, and cap the case list.
4. Create one pytest parameter set per selected case, named pair_0 through pair_N-1.
5. At session fixture setup, validate the score threshold, load PDF text/cache, construct the evidence index, and check Ollama's installed model list.
6. After successful preflight, create a UTC archive directory, move existing evaluation_report.csv and f1_metrics.csv there, and create a fresh evaluation_report.csv header.
7. For each selected turn, pause if appropriate, print progress, retrieve evidence, build the prompt, call Ollama, parse and validate its JSON, and apply a small refusal wording check.
8. Compute metric flags, TP/FN/FP/TN counts, and overall quality status. Append a CSV row before asserting pass/fail in pytest.
9. On a caught evaluation error, append an ERROR row. Timeouts/disconnections stop the run after saving that row; other handled errors ordinarily allow later cases to proceed under normal pytest behavior.
10. At fixture teardown, ask Ollama to unload the model. A requests exception during unloading is ignored.
11. Run compute_f1.py separately. It reads existing case rows, writes aggregate and per-case summary records, and prints the formatted result.

Collection happens before shared preflight. An API collection failure therefore occurs before a new case report is created. A preflight failure preserves existing reports, though loading PDFs can update the PDF cache first.

There is no automatic resume from a partial run, no per-case archive transaction, and no automatic summary regeneration inside pytest. A fresh live run archives the previous files and starts from a new selection. Existing environment overrides and pytest flags can change behavior; for example, -x or --maxfail can stop after an ordinary failed answer.

## 5. Which logs become cases

The client uses https://api.botpress.cloud/v1. It lists /chat/conversations with sortField=updatedAt and sortDirection=desc, following meta.nextToken until the conversation cap or the end. Messages are fetched from /chat/messages with conversationId, with all pages for each visited conversation.

Messages within a conversation are sorted by parsed createdAt. A timestamp must include a timezone to be used; invalid or timezone-free values receive a zero sort fallback. An incoming message begins a pending question only when payload.text is a nonempty string. Subsequent outgoing text messages are combined with newline separators until the next incoming message or the end of the conversation.

The stored question is the incoming text. The stored answer is the combined outgoing text. Message ID and Created At refer to the first collected outgoing message, even when the answer contains several outgoing messages. A pending question without outgoing text produces no pair. Unsupported message directions are ignored; non-text content is not evaluated. A new incoming message flushes the preceding pending turn.

Excluded replies include empty text, certain feedback-only prompts such as "Did I answer your question?", and recognized cannot-find-information, cannot-help, cannot-assist, or cannot-answer phrases. English inability phrases and selected Filipino phrases such as "Hindi ko alam" are recognized. Token/quota/usage/credit/rate/context limit notices, "too many tokens", "maximum context length", and insufficient token/credit/quota notices are also filtered by regular expressions.

Filtering applies to the combined response. A refusal or limit phrase inside otherwise substantive text can therefore exclude the entire turn. Conversely, unrecognized refusal wording may remain. This is pattern filtering, not a complete semantic classification.

After finishing a conversation, the client stops visiting further conversations if it already has at least the case cap. It then sorts collected pairs by first outgoing timestamp, newest first, and truncates to 100. This is a recent eligible sample from visited conversations; it is not a guaranteed global newest-turn sample across all conversations, nor a random sample. The selection progress print occurs before final truncation and can temporarily report more than the cap.

If fewer than 100 eligible turns exist in the visited set, fewer cases run. If none exist, pytest raises a collection usage error. The client computes time from the incoming question to the first outgoing reply when valid timestamps exist, and this response_latency field is now saved as Response Latency (seconds). Median, nearest-rank P95 and the proposed 5-second threshold are summarized independently of quality scores; see response_latency.md.

## 6. PDF evidence and caching

The live evidence documents are loaded from the local folder rather than downloaded from Botpress. A remote fetch_knowledge_base helper remains in botpress_client.py and is covered by tests, but the live session calls load_pdf_knowledge instead.

The inspected documents consist of 6 PDFs, 28 pages with extracted text, and 46 indexed chunks:

- Academic-Programs.pdf
- Campus-Information.pdf
- Enrollment-and-Admission.pdf
- Frequently-Asked-Questions.pdf
- Student-Services.pdf
- Systems-and-Quick-Links.pdf

Discovery uses a nonrecursive *.pdf glob in the knowledge folder, with files sorted by filename. Every PDF is read for a SHA-256 hash. Cache reuse requires version=1, the same filename-to-hash signature mapping, and nonempty cached passages. An unreadable or invalid JSON cache is ignored and rebuilt. If any PDF changes, is added, or is removed, all documents are re-extracted.

pypdf extracts page text. Whitespace-only pages are skipped. A PDF with no readable text causes an explicit OCR-required error; if the file has at least one readable page, its blank/scanned pages are simply absent from the index. OCR is not performed automatically. Corrupt/encrypted PDFs or filesystem failures can also prevent setup.

Each cached page contains id="filename:page:number", file_id=filename, source=filename, kb_id="local-pdfs", a one-based page number, and extracted content. The cache is .pdf_cache.json, written as UTF-8 JSON with version, signatures, and passages. Full PDF text stays in this cache/index; only selected passages are sent to the judge for each case.

PDF content is divided at character offsets 0, 1600, 3200, and so on, each slice up to 1800 characters. Neighboring chunks can overlap by 200 characters. They retain the same parent page identifier; there is no separate saved chunk-offset identifier. Tables, multi-column pages, images, and poor extraction can affect evidence quality.

## 7. How evidence is retrieved

Retrieval is lexical and BM25-style. Text is lowercased and split into Unicode word tokens using the regular expression \w+. There is no embedding model, stemming, semantic synonym expansion, translation, or human answer key in this path.

The query combines question and answer terms. Question token frequencies receive three times their original weight; answer frequencies are then added. Each term's resulting query weight is capped at 6 during scoring. Including answer words can help find cited facts but also makes retrieval dependent on the answer being assessed.

For a term in a chunk, let N be chunk count, df the number of chunks containing the term, tf its frequency in that chunk, L the chunk's token count, and avgL the average token count across the documents. The implementation uses:

IDF = ln(1 + (N - df + 0.5) / (df + 0.5))

Term contribution = min(query_weight, 6) * IDF * tf * 2.5 / [tf + 1.5 * (0.25 + 0.75 * L / avgL)]

Chunk scores are the sum over matching query terms. Every chunk is scored, then score/index tuples are sorted descending. Equal scores favor the larger chunk index. Zero or negative scores are not selected.

Selected text blocks have a source header containing a sequential evidence number, filename, page, and parent passage ID. A block is included only when its entire header and content fit within the character budget, including separators. Oversized blocks are skipped rather than shortened. Ranking continues, so a smaller lower-ranked block may fit. With a 2400-character budget, a long chunk often leaves room for little additional evidence; there is no guaranteed fixed top-k count.

No positive matching chunk produces an empty evidence context. The prompt then explicitly says no relevant evidence was retrieved. This does not automatically make the case an ERROR. The judge still estimates scores and answerability. Missing retrieved evidence does not prove that the answer is absent from all PDFs.

All PDFs are indexed, but the judge sees only the selected subset for that case. Relevance can be affected by common words, answer wording, cross-language queries, and omitted chunks. Saved source references identify what was selected; they do not independently prove that every answer claim was verified.

## 8. What the AI judge receives and returns

The prompt tells the model to evaluate a PUP Taguig chatbot using only supplied KB evidence. It instructs the model to treat the question, answer, and evidence as data, to avoid outside facts, and to return compact JSON without explanations, reference answers, quotations, or generated source IDs. This is a prompt instruction, not a guarantee that every model judgment follows it.

The input JSON contains question, chatbot_response, and knowledge_evidence. The evidence contains the source headers and retrieved text. The model does not receive the entire conversation history, every PDF page, the user's identity, or an independently written ideal answer.

Relevancy measures how directly the reply addresses the question. Accuracy measures correctness and completeness against evidence facts. Faithfulness measures whether the reply's factual claims are supported by evidence. The rubric labels 0-3 poor, 4-6 partial, 7-9 good, and 10 fully correct; it does not define a separate detailed rubric for each individual score.

The answerable flag asks whether the evidence answers the question independently of the reply. The refusal flag asks whether the chatbot substantially declines to answer. A justified refusal for an unanswerable question can receive high scores; refusing an answerable question should receive low accuracy. Both flags are model estimates based on retrieved evidence.

The required output is five fields, for example:

```json
{"relevancy":9,"accuracy":9,"faithfulness":9,
 "answerable":true,"refusal":false}
```

The request is POST /api/generate with stream=false and the JSON schema supplied as format. The schema requires an object, all five fields, numeric scores between 0 and 10, boolean flags, and no additional properties. Temperature is zero. Determinism and correctness are still not guaranteed.

Before generation, the code rejects a prompt longer than OLLAMA_NUM_CTX multiplied by 2 characters. At the default context setting, that is 8192 characters. This is a conservative character heuristic, not an actual tokenizer-based fit check. Larger questions or replies can fail this check even if evidence retrieval succeeded.

## 9. Validation, retries, and refusal handling

After a successful HTTP response, the code reads Ollama's envelope and parses its response field as JSON. A done_reason of "length" raises an error requesting a higher output limit. Empty, truncated, malformed, or non-JSON content cannot become a scored judgment.

Python requires a dictionary; each score must be an int or float, must not be a boolean, and must lie in the inclusive range 0-10. Each flag must have exactly Python bool type. Missing scores or flags fail these checks. The local validator checks the required values but does not independently reject extra keys, although the schema asks the model to omit them.

context and source_count are accepted by validate_judgment but are not used for quotation validation or factual verification. Generated quotations were removed from the current evaluator. Numeric validation establishes output shape, not factual correctness.

With EVALUATION_ATTEMPTS=1, invalid model output becomes a ValueError after one generation. If increased, validation failures caught in the generation parsing block append the previous validation error and a correction instruction to the prompt before a new generation call. Attempts are at least one. HTTP errors, timeouts, and connection errors happen outside that correction block and are not retried by this mechanism. The initial prompt-length check is not rerun after adding correction instructions.

After valid judgment, Python sets refusal=true if the model said true, or if the response is fewer than 200 characters and matches recognized refusal language. This post-check includes cannot-help/assist/answer, unable/not-able phrasing, do-not-know/have-information phrasing, and selected Filipino wording. It can correct a missed refusal but cannot turn a model's refusal=true back to false.

The selection filter and this post-check are separate and have different patterns. Many recognizable refusals are removed during collection, so they never reach the model. Other refusal forms may be scored. Consequently, a scored "Unsupported Answers" count of zero is not evidence that the bot never refused in its raw logs.

## 10. Pass/fail and answer-count rules

Each metric passes when its score is greater than or equal to PASS_THRESHOLD, currently 7. Overall PASS requires all three metrics to pass and must not be an answerable question receiving a refusal. The case is saved as Evaluation Status=OK even if Overall Pass=FAIL: an evaluated poor answer is a successful evaluation of a failing answer.

Let A=answerable, R=refusal, and C=all three scores meet the threshold. The implementation computes:

- TP = 1 when A and not R and C; otherwise 0.
- FN = 1 when A and TP is 0; otherwise 0.
- FP = 1 when not R and TP is 0; otherwise 0.
- TN = 1 when not A and R; otherwise 0.

Examples:

- Answerable, substantive, scores 9/9/9: TP=1; overall PASS.
- Answerable, substantive, scores 9/4/4: FN=1 and FP=1; overall FAIL.
- Answerable, refusal, even scores 9/9/9: FN=1; overall FAIL because the refusal is inappropriate.
- Unanswerable, substantive, scores 2/2/2: FP=1; overall FAIL.
- Unanswerable, refusal, scores 9/9/9: TN=1; overall PASS.
- Unanswerable, refusal, a score below 7: TN=1; overall FAIL. TN depends on flags, while pass/fail also requires quality scores.

The overall-pass code does not explicitly reject an unanswerable substantive reply when all scores are high. Such an inconsistent model judgment would count as FP=1 but could still receive overall PASS. This is an implementation limitation worth checking when reviewing rows.

Unsupported Answer is YES when refusal=true. Here this label means a refusal/cannot-help response, not a hallucination or a count of unsupported factual claims. KB Answerable is the model's answerable flag. Latency is summarized independently using a proposed 5-second real-time threshold (P95 <= 5 seconds); it does not change answer-quality pass/fail. See response_latency.md for the observed results and limitations.

Rows are appended before the pytest assertion. A quality FAIL remains in the CSV and ordinarily allows the next case to run. Pass rate and F1 answer counts therefore answer related but different questions and need not move together.

## 11. Errors and what gets saved

Collection errors: missing Botpress credentials, nonpositive selection caps, failed API requests, non-OK API status, missing expected response arrays, repeated pagination tokens, or no eligible turns can stop pytest collection. RuntimeError/ValueError from collection are converted to pytest usage errors. Unexpected response shapes can raise other exceptions. These happen before new report initialization; no per-case ERROR row is created.

Setup errors: invalid threshold, no local PDFs, no documents, PDF extraction failure, unreadable PDF text, cache write failure, unreachable Ollama model-list endpoint, HTTP preflight error, or absent configured model prevent shared fixture setup. Existing reports are not deliberately archived until those checks pass. Setup errors are not saved as individual evaluated rows.

Handled per-case errors: requests.RequestException, ValueError, KeyError, or TypeError escaping evaluate_pair produce an ERROR row. Examples include the context minima, oversized prompt, invalid JSON, invalid scores/flags, output token limit, and HTTP generation error. Evaluation Status and Overall Pass become ERROR; the row retains the input/output, IDs, model, threshold, and reason. Score, flag, evidence-source, and answer-count columns remain blank. No scores are fabricated.

ValueError reasons include the exception class and detailed validation text. Other caught exception reasons store the class name only, such as ReadTimeout or HTTPError, so the record may not include the HTTP status or network details. A generation timeout or connection failure appends its row and calls pytest.exit with return code 2, preserving earlier rows and stopping repeated waits. Other handled failures call pytest.fail and ordinarily continue.

Failures outside the protected evaluate_pair call are not guaranteed an ERROR row. Examples include invalid pause configuration, missing required pair fields while creating the row, CSV permission/disk errors, unexpected result-processing exceptions, or archive failures. A filesystem failure during the multi-file archive can leave a partially moved archive; the operation is not transactional.

The summary script stops explicitly when evaluation_report.csv is missing or has no rows. Corrupt OK rows with missing or noninteger answer-count fields can also cause summary errors. The unload finalizer ignores requests exceptions, and does not check the returned HTTP status; an unload failure is not recorded in the evaluation CSV.

## 12. How detailed records are stored

Detailed rows go to botpress_eval/evaluation_report.csv using csv.DictWriter, UTF-8 encoding, and newline="". A new report gets a header, and each case opens the file in append mode, writes one row, and closes it. csv handles commas, quotes, and embedded newlines in the question/answer. This is local filesystem storage, not a database insert or Laravel model record.

All 24 columns, in file order:

1. Index: zero-based selected-case number; progress prints use index+1.
2. Conversation ID: original Botpress conversation identifier.
3. Message ID: first outgoing message identifier of the assembled answer.
4. Created At: that outgoing message's original timestamp; not evaluation time.
5. User Input: original incoming text question.
6. Bot Response: assembled outgoing text, including multiple parts if present.
7. Evaluation Status: OK or ERROR.
8. Relevancy Score (0-10): validated numeric judgment.
9. Relevancy Pass: PASS or FAIL.
10. Accuracy Score (0-10): validated numeric judgment.
11. Accuracy Pass: PASS or FAIL.
12. Faithfulness Score (0-10): validated numeric judgment.
13. Faithfulness Pass: PASS or FAIL.
14. Unsupported Answer: YES/NO refusal indicator.
15. KB Answerable: YES/NO model-estimated evidence answerability.
16. Evidence Sources: JSON with a sources array of selected source headers.
17-20. TP, FN, FP, TN: each case's integer answer-count contributions.
21. Overall Pass: PASS, FAIL, or ERROR.
22. Evaluation Reason: empty on OK rows; error reason on handled errors.
23. Evaluator Model: model name used by the run.
24. Pass Threshold: configured threshold used by the run.

Evidence Sources stores filename/page/passage headers, not the full retrieved context or verified quotation text. Raw model JSON, full prompt, token counts, timings, model version digest, PDF signatures per run, and excluded-turn details are not saved in the case CSV. The PDF cache contains text/signatures, but it can be replaced later. Reproducing a historical run exactly therefore requires retaining its source PDFs and environment separately.

## 13. Archives, summary records, and formulas

After successful preflight, both existing CSVs are moved into report_history/<UTC timestamp>/. The format is YYYYMMDDTHHMMSSffffffZ, containing microseconds. For example, 20261007T085004621674Z is an archive identifier in UTC; user-facing dates in this guide use Asia/Singapore. An archive directory is created even when there are no old reports to move. These history files are ignored by Git.

compute_f1.py reads the current detailed CSV with utf-8-sig, classifies Evaluation Status=OK rows as scored, counts ERROR rows separately, and treats other rows as legacy/unscored. It excludes errors and legacy rows from answer counts, score averages, precision, recall, and F1. Average extraction skips missing, invalid, and nonfinite numeric values.

The summary CSV, f1_metrics.csv, is overwritten with Metric,Value pairs, a blank line, a "Per-case results" marker, and a compact per-case table. That table contains Index, Status, TP, FN, FP, TN, Unsupported Answer, Relevancy, Accuracy, Faithfulness, and Overall Pass. The file contains two sections, so it is not one uniform rectangular table throughout.

Summary metrics are Total Cases, Scored Cases, Evaluation Errors, Legacy / Unscored Cases, True Positive, False Negative, False Positive, True Negative, the three average scores, Unsupported Answers (refusals), Precision, Recall, F1 Score, Passed Cases, and Pass Rate (%).

- Precision = TP / (TP + FP).
- Recall = TP / (TP + FN).
- F1 = 2*TP / (2*TP + FP + FN).
- Pass rate = passing scored cases / all report rows * 100.
- Average metric = mean of usable numeric values among OK rows.

Zero denominators yield N/A, not zero. Precision/recall/F1 are rounded to four decimals, score averages to three, and pass rate to two. Errors cannot improve pass rate because they remain in its denominator. A 99/100 result yields 99% pass rate; TP=99, FP=1, FN=1 yields 0.99 for each F1 metric.

The script also prints cases, answer counts, F1 metrics, and quality sections. Passed Cases is saved in the CSV but not separately listed in that printed quality section. Summary is manual: a failed or stopped live run can leave no current summary until compute_f1.py is invoked. No email, spreadsheet, external dashboard, or Laravel database synchronization occurs.

## 14. Runtime, interpretation, and review checklist

For N completed cases, a practical estimate is:

Total time = log collection + PDF/index/model setup + sum of case evaluation times + max(N-1,0)*pause + teardown + summary generation.

The observed one-case sample was 18.6 seconds. For 100 cases, 100*18.6 + 99*2 = 2058 seconds, or 34.3 minutes, before collection/setup/teardown. The earlier console projection printed 34.2 minutes because it used the unrounded sample duration internally. Plan roughly 35-45 minutes on the same setup, with uncertainty from hardware load, model warm-up, prompt size, API delays, and PDF cache state. This is not a confirmed elapsed time for the existing 100-row run.

Useful scenarios with default pauses, excluding overhead:

- 10 cases at 18.6 seconds each: approximately 3.4 minutes.
- 20 cases at 18.6 seconds each: approximately 6.8 minutes.
- 100 cases at 10 seconds each: approximately 20.0 minutes.
- 100 cases at 30 seconds each: approximately 53.3 minutes.
- 100 cases at 60 seconds each: approximately 103.3 minutes.

The 90-second request timeout is a requests network timeout, not a strict maximum full-run duration. Timeout/connection failures stop the run rather than spending that timeout on all remaining cases. Extra validation attempts multiply generation work for affected cases. Serial execution and two-second pauses are intentional current behavior. There is no saved runtime column or elapsed-run metadata.

For review, verify that selected-case counts meet the intended sample size; distinguish scored FAIL from evaluation ERROR; inspect the failed case against its source pages; and check whether answerability/refusal flags agree with quality scores. A high F1 for filtered answered turns does not establish performance on excluded failures, unanswered questions, every chatbot topic, or future conversations.

The evaluator uses current local PDFs to judge historical conversations, so facts may have changed since the original answer. It uses an AI judge, lexical retrieval, and a small evidence window, rather than human-labeled ground truth. Scores can be wrong even when JSON is valid. The current records have no independent factual-verification stage, calibrated uncertainty, or confidence interval.

Code references for this guide: test_botpress.py (configuration, prompt/schema, validation, outcome, pytest collection, fixture, row writer); botpress_client.py (API paging, filtering, pair selection); local_knowledge.py (PDF/cache); knowledge.py (BM25-style retrieval); compute_f1.py (summary). Record snapshot: evaluation_report.csv and f1_metrics.csv inspected on 7 October 2026. No live 100-case run was started to prepare this document.

