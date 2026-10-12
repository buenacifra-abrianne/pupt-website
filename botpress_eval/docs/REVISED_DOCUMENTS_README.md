# Revised AI Help Desk documents

Prepared 12 October 2026 from the Laravel implementation, the earlier evaluator
guide/PDF, current evaluation CSVs and the measured Botpress-log latencies.

The four editable Word documents are together in AI_HELP_DESK_WORD_DOCUMENTS/.
They have matching Markdown masters:

- AI_HELP_DESK_ARCHITECTURE_AND_AI_CAPABILITIES
- AI_HELP_DESK_KNOWLEDGE_BASE_AND_CONTENT_MANAGEMENT
- AI_HELP_DESK_EVALUATION
- AI_HELP_DESK_PRIVACY_AND_SECURITY_ASSESSMENT

AI_HELP_DESK_WORD_DOCUMENTS.zip packages that folder and its four DOCX files.
The previous AI_HELP_DESK_REVISED_DOCUMENTS.zip contains the PDF versions. The older
evaluator_details.pdf is retained as the earlier process-guide artifact; use the
new evaluation document for the revised metrics, limitations and study criteria.
Unmeasured fallback, escalation, hallucination, availability and reliability
outcomes are stated explicitly, rather than inferred from answer-quality scores.
The security assessment describes data flows, existing controls and validation limits; it does not change application code.

To rebuild from the project root:

```powershell
.venv/Scripts/python.exe botpress_eval/docs/generate_help_desk_word_documents.py
```

The Word renderer requires python-docx. The DOCX files preserve editable headings,
tables, the architecture flow diagram and linked external references. Markdown
files remain the source masters for rebuilding. The previous PDF renderer is
generate_help_desk_documents.py and requires reportlab.
No raw conversations or credentials are included in the documents or ZIP files.
