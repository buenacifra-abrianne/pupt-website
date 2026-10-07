"""Read Botpress logs and all extracted knowledge-base passages."""
import os
import re
from datetime import datetime
from pathlib import Path
from urllib.parse import quote
import requests
from dotenv import load_dotenv

load_dotenv(Path(__file__).with_name(".env"))


class BotpressClient:
    def __init__(self):
        self.api_token = os.getenv("BOTPRESS_API_TOKEN") or os.getenv("BOTPRESS_PAT")
        self.bot_id = os.getenv("BOTPRESS_BOT_ID")
        if not self.api_token or not self.bot_id:
            raise ValueError("Set BOTPRESS_API_TOKEN (or BOTPRESS_PAT) and BOTPRESS_BOT_ID in botpress_eval/.env")
        self.base_url = "https://api.botpress.cloud/v1"
        self.headers = {"Authorization": f"Bearer {self.api_token}", "x-bot-id": self.bot_id}

    def _get(self, path, params=None):
        try:
            response = requests.get(self.base_url + path, headers=self.headers, params=params, timeout=60)
        except requests.RequestException:
            raise RuntimeError(f"Botpress request failed for {path}; check connectivity.") from None
        if not response.ok:
            raise RuntimeError(f"Botpress HTTP {response.status_code} for {path}; check permissions/indexing.")
        return response.json()

    def _pages(self, path, key, params=None, limit=None):
        params = dict(params or {})
        result, seen = [], set()
        while True:
            data = self._get(path, params)
            if key not in data:
                raise RuntimeError(f"Botpress response missing {key} for {path}")
            result.extend(data[key])
            if limit is not None and len(result) >= limit:
                return result[:limit]
            token = data.get("meta", {}).get("nextToken")
            if not token:
                return result
            if token in seen:
                raise RuntimeError(f"Botpress repeated pagination token for {path}")
            seen.add(token)
            params["nextToken"] = token

    def fetch_conversations(self, limit=None):
        return self._pages("/chat/conversations", "conversations",
                           {"sortField": "updatedAt", "sortDirection": "desc"}, limit)

    def fetch_messages(self, conversation_id):
        return self._pages("/chat/messages", "messages", {"conversationId": conversation_id})

    def fetch_knowledge_base(self):
        # Bracket syntax verified against the live API. No kbId filter: include every KB.
        files = self._pages("/files", "files", {"tags[source]": "knowledge-base"})
        if not files:
            raise RuntimeError("No knowledge-base files accessible for this bot.")
        passages = []
        for file in files:
            if file.get("status") != "indexing_completed":
                raise RuntimeError(f"Knowledge file {file['id']} is not fully indexed; finish indexing in Botpress.")
            extracted = self._pages("/files/" + quote(file["id"], safe="") + "/passages",
                                    "passages", {"limit": 200})
            contents = [p for p in extracted if p.get("content", "").strip()]
            if not contents:
                raise RuntimeError(f"Knowledge file {file['id']} has no text. Check PDF OCR/indexing in Botpress.")
            for passage in contents:
                passages.append({
                    "id": passage["id"], "file_id": file["id"],
                    "source": file.get("tags", {}).get("title") or file.get("key") or file["id"],
                    "kb_id": file.get("tags", {}).get("kbId", ""),
                    "page": passage.get("meta", {}).get("pageNumber"),
                    "content": passage["content"],
                })
        return passages

    @staticmethod
    def _timestamp(value):
        try:
            date = datetime.fromisoformat(value.replace("Z", "+00:00"))
            return date.timestamp() if date.tzinfo else None
        except (ValueError, TypeError, AttributeError):
            return None

    @staticmethod
    def is_answer(text):
        text = text.strip().lower().replace("’", "'")
        if not text:
            return False
        notices = (
            r"^(?:did (?:i|we) answer your question|was (?:this|that) helpful|is there anything else).*[?]?[\s]*$",
            r"\b(?:i|we)\s+(?:couldn't|could not|can't|cannot)\s+find\s+(?:that|this|the)\s+information",
            r"(?:token|quota|usage|credit|rate|context)\s+(?:limit|budget).{0,50}(?:exceeded|reached|exhausted)",
            r"(?:exceeded|reached|exhausted).{0,50}(?:token|quota|usage|credit|rate|context)\s+(?:limit|budget)",
            r"too many tokens|maximum context length|insufficient (?:tokens|credits|quota)",
            r"\b(?:i|we)\s+(?:couldn't|can't|cannot|could not|can not)\s+(?:help|assist|answer)",
            r"\b(?:unable to|not able to)\s+(?:help|assist|answer)",
            r"hindi (?:ko|kami|ako) (?:alam|makatulong|masagot)",
        )
        return not any(re.search(pattern, text, re.DOTALL) for pattern in notices)

    def get_qa_pairs(self, limit_conversations=100, limit_pairs=None):
        pairs = []
        excluded = 0
        for conv in self.fetch_conversations(limit_conversations):
            messages = sorted(self.fetch_messages(conv["id"]),
                              key=lambda m: self._timestamp(m.get("createdAt")) or 0)
            pending, answers = None, []

            def save_turn():
                nonlocal excluded
                if pending and answers:
                    output = "\n".join(a["payload"]["text"] for a in answers)
                    if not self.is_answer(output):
                        excluded += 1
                        return
                    start = self._timestamp(pending.get("createdAt"))
                    end = self._timestamp(answers[0].get("createdAt"))
                    latency = end - start if start is not None and end is not None and end >= start else None
                    pairs.append({
                        "input": pending["payload"]["text"],
                        "output": output,
                        "conversation_id": conv["id"], "message_id": answers[0]["id"],
                        "created_at": answers[0].get("createdAt", ""), "response_latency": latency,
                    })

            for message in messages:
                direction = message.get("direction")
                text = message.get("payload", {}).get("text")
                if direction == "incoming":
                    save_turn()
                    pending = message if isinstance(text, str) and text.strip() else None
                    answers = []
                elif direction == "outgoing" and pending and isinstance(text, str) and text.strip():
                    answers.append(message)
            save_turn()
            if limit_pairs is not None and len(pairs) >= limit_pairs:
                break
        print(f"Selected {len(pairs)} answered turns; excluded {excluded} limit notices / cannot-answer replies.")
        ordered = sorted(pairs, key=lambda p: self._timestamp(p["created_at"]) or 0, reverse=True)
        return ordered[:limit_pairs] if limit_pairs is not None else ordered
