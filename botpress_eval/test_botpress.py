import os
import re
import csv
import pytest
import requests
from botpress_client import BotpressClient
from dotenv import load_dotenv

load_dotenv()

try:
    import gspread
    from oauth2client.service_account import ServiceAccountCredentials
    GSPREAD_AVAILABLE = True
except ImportError:
    GSPREAD_AVAILABLE = False

# ─────────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────────
MAX_CONVERSATIONS = 100    # Fetch from this many conversations
MAX_TEST_CASES   = 100    # Evaluate at most this many Q&A pairs
OLLAMA_MODEL     = "phi3:mini"   # Change to "llama3" if you have enough RAM
OLLAMA_URL       = "http://localhost:11434/api/generate"
PASS_THRESHOLD   = 4    # Score >= 7 out of 10 = Pass

# ─────────────────────────────────────────────
# DIRECT OLLAMA EVALUATOR (no DeepEval async)
# ─────────────────────────────────────────────
def _ask_ollama(prompt: str, timeout: int = 120) -> str:
    """Send a single prompt to Ollama and return the response text."""
    payload = {
        "model": OLLAMA_MODEL,
        "prompt": prompt,
        "stream": False,
        "options": {"temperature": 0}
    }
    resp = requests.post(OLLAMA_URL, json=payload, timeout=timeout)
    resp.raise_for_status()
    return resp.json().get("response", "").strip()


def evaluate_relevancy(user_input: str, bot_response: str) -> tuple[float, bool]:
    """
    Ask Ollama to rate how relevant the bot's answer is to the user's question.
    Returns (score_0_to_10, pass_bool).
    """
    prompt = f"""You are a strict AI evaluator assessing a university chatbot.

CONTEXT: This is an official chatbot for PUP Taguig (Polytechnic University of the Philippines - Taguig Campus),
a Philippine government university. The chatbot ONLY operates in English and Filipino.
It should NOT respond in other languages (e.g. Chinese Mandarin, Japanese, Korean, etc.).
If a user asks it to switch to another language, the CORRECT behavior is to politely decline or redirect.

USER QUESTION: {user_input}

CHATBOT RESPONSE: {bot_response}

Rate how appropriate and helpful the chatbot response is given the context above, on a scale of 1 to 10.
- 10 = Perfectly appropriate response for a PUP Taguig university chatbot
- 7-9 = Mostly appropriate, minor issues
- 4-6 = Partially appropriate
- 1-3 = Completely inappropriate or wrong response

Reply with ONLY a single integer number between 1 and 10. Do not explain."""

    raw = _ask_ollama(prompt)
    # Extract the first integer found in the response
    match = re.search(r'\b([1-9]|10)\b', raw)
    score = int(match.group()) if match else 5
    return score, score >= PASS_THRESHOLD


def evaluate_faithfulness(user_input: str, bot_response: str, knowledge_base: str) -> tuple[float, bool]:
    """
    Ask Ollama to rate how faithful (accurate) the bot's answer is against the knowledge base.
    Returns (score_0_to_10, pass_bool).
    """
    prompt = f"""You are a strict AI fact-checker. Your job is to check if a chatbot response is accurate based on a knowledge base.

USER QUESTION: {user_input}

CHATBOT RESPONSE: {bot_response}

KNOWLEDGE BASE (source of truth):
{knowledge_base[:3000]}

Rate how faithful and factually accurate the chatbot response is to the knowledge base on a scale of 1 to 10.
- 10 = Every claim in the response is supported by the knowledge base
- 7-9 = Most claims are supported, minor gaps
- 4-6 = Some claims are unsupported or partially wrong
- 1-3 = The response contains hallucinations or contradicts the knowledge base

Reply with ONLY a single integer number between 1 and 10. Do not explain."""

    raw = _ask_ollama(prompt)
    match = re.search(r'\b([1-9]|10)\b', raw)
    score = int(match.group()) if match else 5
    return score, score >= PASS_THRESHOLD


# ─────────────────────────────────────────────
# KNOWLEDGE BASE (optional — for faithfulness)
# ─────────────────────────────────────────────
def _load_knowledge_base() -> str:
    """Load knowledge_base.txt if it exists, otherwise return empty string."""
    kb_path = os.path.join(os.path.dirname(__file__), "knowledge_base.txt")
    if os.path.exists(kb_path):
        with open(kb_path, "r", encoding="utf-8") as f:
            return f.read()
    return ""

KNOWLEDGE_BASE = _load_knowledge_base()


# ─────────────────────────────────────────────
# FETCH Q&A PAIRS
# ─────────────────────────────────────────────
def load_qa_pairs():
    """Fetch Q&A pairs from Botpress and cap at MAX_TEST_CASES."""
    client = BotpressClient()
    all_pairs = client.get_qa_pairs(limit_conversations=MAX_CONVERSATIONS)
    return all_pairs[:MAX_TEST_CASES]


# ─────────────────────────────────────────────
# PARAMETRIZED TEST
# ─────────────────────────────────────────────
def pytest_generate_tests(metafunc):
    """Dynamically parametrize test_botpress_logs with real Botpress Q&A pairs."""
    if "qa_pair" in metafunc.fixturenames and "pair_index" in metafunc.fixturenames:
        if not os.getenv("BOTPRESS_API_TOKEN"):
            metafunc.parametrize("qa_pair,pair_index", [(None, 0)])
        else:
            pairs = load_qa_pairs()
            if not pairs:
                metafunc.parametrize("qa_pair,pair_index", [(None, 0)])
            else:
                metafunc.parametrize(
                    "qa_pair,pair_index",
                    [(p, i) for i, p in enumerate(pairs)],
                    ids=[f"pair_{i}" for i in range(len(pairs))]
                )


# ─────────────────────────────────────────────
# CSV + GOOGLE SHEETS REPORT
# ─────────────────────────────────────────────
REPORT_FILE = "evaluation_report.csv"
HEADERS = [
    "Index", "User Input", "Bot Response",
    "Relevancy Score (0-10)", "Relevancy Pass",
    "Faithfulness Score (0-10)", "Faithfulness Pass"
]

def _append_to_report(index, user_input, bot_response,
                      relevancy_score, relevancy_pass,
                      faithfulness_score=None, faithfulness_pass=None):
    """Append a single result row to the CSV report and Google Sheets."""
    row_data = [
        index,
        user_input[:120],
        bot_response[:120],
        relevancy_score,
        "PASS" if relevancy_pass else "FAIL",
        faithfulness_score if faithfulness_score is not None else "N/A",
        ("PASS" if faithfulness_pass else "FAIL") if faithfulness_pass is not None else "N/A"
    ]

    # Write to Local CSV
    file_exists = os.path.isfile(REPORT_FILE)
    with open(REPORT_FILE, "a", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        if not file_exists:
            writer.writerow(HEADERS)
        writer.writerow(row_data)
    print(f"\n[CSV] Row {index} saved to {REPORT_FILE}")

    # Write to Google Sheets (if credentials.json exists)
    if GSPREAD_AVAILABLE and os.path.exists("credentials.json"):
        try:
            scope = ["https://spreadsheets.google.com/feeds", "https://www.googleapis.com/auth/drive"]
            creds = ServiceAccountCredentials.from_json_keyfile_name("credentials.json", scope)
            client = gspread.authorize(creds)
            sheet = client.open("Botpress Evaluation").sheet1
            if not sheet.get_all_values():
                sheet.append_row(HEADERS)
            sheet.append_row(row_data)
            print(f"[Sheets] Row {index} pushed to Google Sheets!")
        except Exception as e:
            print(f"[Warning] Google Sheets push failed: {e}")


# ─────────────────────────────────────────────
# MAIN TEST FUNCTION
# ─────────────────────────────────────────────
def test_botpress_logs(qa_pair, pair_index):
    # Skip if preconditions are missing
    if not os.getenv("BOTPRESS_API_TOKEN"):
        pytest.skip("BOTPRESS_API_TOKEN is missing in .env")
    if qa_pair is None:
        pytest.skip("No Q&A pairs found in Botpress conversations.")

    # Check Ollama is reachable
    try:
        requests.get("http://localhost:11434/api/tags", timeout=3).raise_for_status()
    except Exception:
        pytest.skip("Ollama is not running. Start it with: ollama serve")

    user_input   = qa_pair["input"]
    bot_response = qa_pair["output"]

    print(f"\n[Test {pair_index}] Q: {user_input[:60]}...")

    # --- Evaluate Relevancy (always runs) ---
    rel_score, rel_pass = evaluate_relevancy(user_input, bot_response)
    print(f"[Test {pair_index}] Relevancy: {rel_score}/10 ({'PASS' if rel_pass else 'FAIL'})")

    # --- Evaluate Faithfulness (only if knowledge_base.txt exists) ---
    faith_score, faith_pass = None, None
    if KNOWLEDGE_BASE:
        faith_score, faith_pass = evaluate_faithfulness(user_input, bot_response, KNOWLEDGE_BASE)
        print(f"[Test {pair_index}] Faithfulness: {faith_score}/10 ({'PASS' if faith_pass else 'FAIL'})")
    else:
        print(f"[Test {pair_index}] Faithfulness: SKIPPED (no knowledge_base.txt found)")

    # Save to CSV + Google Sheets
    _append_to_report(
        index=pair_index,
        user_input=user_input,
        bot_response=bot_response,
        relevancy_score=rel_score,
        relevancy_pass=rel_pass,
        faithfulness_score=faith_score,
        faithfulness_pass=faith_pass,
    )

    # Fail the test if relevancy score is below threshold
    assert rel_pass, (
        f"Relevancy score {rel_score}/10 is below threshold {PASS_THRESHOLD}/10\n"
        f"Q: {user_input}\nA: {bot_response[:200]}"
    )
