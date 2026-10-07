# Botpress DeepEval Pipeline

This project contains an automated evaluation pipeline that uses `deepeval` to test a Botpress chatbot. It uses the API Pull Method to fetch real conversation logs from Botpress Cloud and evaluates them against the `AnswerRelevancyMetric` and `FaithfulnessMetric`.

## Setup

1. **Install Dependencies:**
   ```bash
   pip install -r requirements.txt
   ```

2. **Environment Variables:**
   Copy the `.env.example` file to `.env` and fill in your actual credentials.
   ```bash
   cp .env.example .env
   ```
   You will need:
   - `BOTPRESS_API_TOKEN`
   - `BOTPRESS_WORKSPACE_ID`
   - `BOTPRESS_BOT_ID`
   - `OPENAI_API_KEY` (Required for DeepEval's default evaluator model)

3. **Provide Context (Optional but Recommended):**
   In `test_botpress.py`, there is a `mock_knowledge_base_context` variable. Replace the placeholder context with the actual knowledge base or context your RAG/Botpress bot uses to answer questions. This is crucial for accurate `FaithfulnessMetric` evaluation.

## Execution

To run the DeepEval tests against your Botpress bot:

```bash
deepeval test run test_botpress.py
```
