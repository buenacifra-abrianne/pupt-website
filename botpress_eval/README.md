# Current evaluator: compact local-PDF scoring

The evaluator searches cached local PDFs, then asks Ollama for only three numeric
scores (relevancy, accuracy, faithfulness) and two flags (answerable, refusal).
Generated quotations are no longer required or validated. Retrieved source/page
references are saved by Python. This removes quote-format errors, but scores and
F1 are AI estimates, not independently verified ground truth. Invalid JSON,
timeouts and incorrect chatbot answers still produce explicit errors/failures.
The default output cap is 192 tokens. Set OLLAMA_NUM_PREDICT to override it.
Feedback prompts and cannot-find-information replies are excluded before selecting
up to 20 answered turns. Filtering changes the evaluated population.

```powershell
python -m pytest test_botpress.py -s
python compute_f1.py
```

## Earlier implementation notes (superseded where different)

# Local PDF evaluation

Place PDFs in `knowledge_base/`. The evaluator reads their text locally, caches
extraction in ignored `.pdf_cache.json`, and rebuilds the cache when a PDF changes.
Knowledge files are no longer fetched from Botpress. Botpress credentials are still
required to fetch conversation logs. Scanned PDFs need OCR first.

Run from this folder:

```powershell
python -m pip install -r requirements.txt
python -m pytest test_botpress.py -s
python compute_f1.py
```

# Previous metric definitions and settings

The evaluator fetches current text conversation logs from Botpress and loads
all PDFs from local `knowledge_base/`. Extracted page text is cached and retrieved
locally. Knowledge Files API downloads are no longer used. Missing PDFs or PDFs
without extracted text fail explicitly rather than silently skipping faithfulness.

## Run

From this folder:

```powershell
python -m pip install -r requirements.txt
ollama pull phi3:mini
ollama serve
```

Leave Ollama running and use another terminal:

```powershell
python -m pytest test_botpress.py -s
python compute_f1.py
```

Run the summary command even when pytest reports failed chatbot answers. Failed answers
are still recorded. Every new evaluation archives both old CSVs under
`report_history/<UTC timestamp>/` and starts a fresh report after KB/Ollama preflight
succeeds. Existing reports are not changed when preflight fails. Do not use pytest-xdist
or multiple concurrent live runs: the CSV writer supports a single process.

Store credentials only in ignored `.env` in this folder:

```dotenv
BOTPRESS_API_TOKEN=your_personal_access_token
BOTPRESS_BOT_ID=your_bot_id
OLLAMA_MODEL=phi3:mini
PASS_THRESHOLD=7
MAX_CONVERSATIONS=100
MAX_TEST_CASES=20
```

`BOTPRESS_PAT` is accepted as an alternative token name. Workspace ID/URL is not needed.
`BOTPRESS_KNOWLEDGE_BASE_ID` is unused; local PDFs define the evidence corpus. Optional settings: `OLLAMA_URL` (base URL),
`OLLAMA_TIMEOUT=90`, `OLLAMA_NUM_CTX=4096`, `KB_CONTEXT_CHARS=2400`.
No OpenAI/Gemini key or local knowledge_base.txt is required. Results stay local;
the evaluator does not automatically send conversation logs to Google Sheets.

## Metrics

All three scores are 0–10 and use the retrieved Botpress evidence:

| Metric | Definition |
| --- | --- |
| Relevancy | How directly the response addresses the question |
| Accuracy | Correctness/completeness against KB facts |
| Faithfulness | Whether the response's factual claims are supported by evidence |
| Unsupported Answers | Refusal/cannot-help responses, including “I couldn't help you with that”; this is your requested definition, distinct from hallucinations |

A correct refusal for a question not answerable from evidence can pass. A refusal for
an answerable question fails. Overall PASS requires all three score thresholds and an appropriate answer/refusal.
Response latency is not evaluated.

The CSV preserves full questions/responses, quoted evidence,
source/page/passage references, model and threshold for review. Model output is validated;
bad JSON or invented evidence quotes are recorded as ERROR.

## Precision / Recall / F1

These measure successful grounded answer delivery, using AI-estimated answerability
and correctness, not the old assumption that false positives are always zero:

- TP: answerable question received a substantive answer passing all three score thresholds.
- FN: answerable question did not receive a correct grounded answer (wrong answer or refusal).
- FP: a substantive answer was given but was incorrect/ungrounded or the question was not answerable.
- TN: an unanswerable question received a refusal.

An incorrect substantive answer to an answerable question contributes **both FP and FN**:
one spurious answer and one missed correct answer. These are answer retrieval counts,
not mutually exclusive binary classification buckets, so they need not sum to total cases.
Precision = TP/(TP+FP), Recall = TP/(TP+FN), F1 = 2TP/(2TP+FP+FN).
Undefined denominators are N/A. Errors/legacy rows are excluded from scored averages
and F1; the report lists them separately. Overall pass rate divides passing cases by
all report rows, so errors cannot improve it.

## Evidence limits

Every local PDF page is extracted and indexed using BM25. Each evaluation
receives the highest matching chunks within KB_CONTEXT_CHARS rather than only the first
3,000 characters of a PDF. This lexical retrieval can miss synonyms or cross-language
matches; “not answerable” means not established by retrieved evidence. It is not proof
that an answer is absent from the entire KB. Larger KB_CONTEXT_CHARS requires a larger
Ollama context. Review scores/source quotes, especially with small models such as phi3:mini;
these are AI estimates, not human-labeled ground truth. Current KB facts may differ from
facts when an old conversation occurred.

Table-only KB sources not represented by indexed Files API passages are outside this
file-source evaluator; export them into an indexed document to include them. Scanned PDFs
need OCR before local text extraction can evaluate them.

Official API references:
- [List knowledge files](https://botpress.com/docs/api-reference/files-api/openapi/listFiles/)
- [KB file tags](https://botpress.com/docs/api-reference/files-api/how-tos/creating-files/)
- [Extracted passages](https://botpress.com/docs/api-reference/files-api/openapi/listFilePassages/)

## Offline verification

```powershell
python -m pytest tests -q
```

These tests use synthetic documents/messages and mocked services. They never fetch
real conversations, overwrite live reports, or call Ollama.

## Laptop resource settings

Defaults use phi3:mini, a 4096-token context, 2400 evidence characters, two CPU
threads, a 64-token processing batch and at most 384 output tokens. There is a
two-second pause between cases. The model unloads after the run; idle retention
is one minute. Smaller evidence windows can reduce coverage and score reliability.
Existing environment overrides still take precedence. Optional overrides:
OLLAMA_NUM_THREADS, OLLAMA_NUM_PREDICT and EVALUATION_PAUSE_SECONDS.

For an 8 GB laptop, start the Ollama server with one loaded model and one
parallel request (quit any existing Ollama server first):

```powershell
$env:OLLAMA_MAX_LOADED_MODELS="1"
$env:OLLAMA_NUM_PARALLEL="1"
ollama serve
```
## Fast evaluation

Defaults use one generation attempt and a 90-second request timeout. A timeout
or disconnection saves the error row and stops the run to avoid repeated waits.
Case-start messages show progress. All KB file passages are still fetched; the
smaller evidence selection can reduce score reliability. Environment overrides
take precedence; clear earlier overrides to use these defaults.

## Compact F1 report

The model now returns only three scores, answerable/refusal flags and at most one
160-character verified evidence quote. It no longer generates reference answers
or explanations. Source metadata and TP/FP/FN/TN counts are added by Python.
The summary prioritizes Precision, Recall and F1, with case/error counts and a
short quality section. Duplicate averages/rates and full retrieved text are omitted.
F1 definitions and error exclusions are unchanged. Historical generated CSVs remain
as they were; rerun evaluation to create the compact report.
