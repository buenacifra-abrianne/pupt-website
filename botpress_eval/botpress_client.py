import os
import requests
from dotenv import load_dotenv

load_dotenv()

class BotpressClient:
    def __init__(self):
        self.api_token = os.getenv("BOTPRESS_API_TOKEN")
        self.workspace_id = os.getenv("BOTPRESS_WORKSPACE_ID")
        self.bot_id = os.getenv("BOTPRESS_BOT_ID")
        self.base_url = "https://api.botpress.cloud/v1"
        
        self.headers = {
            "Authorization": f"Bearer {self.api_token}",
            "x-workspace-id": self.workspace_id or "",
            "x-bot-id": self.bot_id or "",
            "Content-Type": "application/json"
        }

    def fetch_conversations(self):
        """Fetches recent conversations for the bot."""
        url = f"{self.base_url}/chat/conversations"
        response = requests.get(url, headers=self.headers)
        response.raise_for_status()
        # Botpress returns a paginated list or similar, we extract 'conversations'
        data = response.json()
        return data.get("conversations", [])

    def fetch_messages(self, conversation_id):
        """Fetches messages for a specific conversation."""
        url = f"{self.base_url}/chat/messages"
        params = {"conversationId": conversation_id}
        response = requests.get(url, headers=self.headers, params=params)
        response.raise_for_status()
        data = response.json()
        return data.get("messages", [])

    def get_qa_pairs(self, limit_conversations=5):
        """
        Fetches conversations, extracts messages, sorts them chronologically, 
        and extracts User -> Bot question-answer pairs.
        """
        conversations = self.fetch_conversations()
        qa_pairs = []

        for conv in conversations[:limit_conversations]:
            conv_id = conv.get("id")
            if not conv_id:
                continue
                
            messages = self.fetch_messages(conv_id)
            
            # Sort messages chronologically (assuming 'createdAt' is an ISO timestamp)
            messages = sorted(messages, key=lambda x: x.get("createdAt", ""))
            
            parsed_messages = []
            for msg in messages:
                payload = msg.get("payload", {})
                text = payload.get("text")
                
                # Botpress Cloud might indicate direction explicitly, 
                # or we can infer it via userId matching the bot_id.
                direction = msg.get("direction")
                user_id = msg.get("userId")
                
                if text:
                    # Role mapping logic
                    if direction == "incoming":
                        role = "user"
                    else:
                        role = "bot"
                    
                    # Try to extract citations if the Botpress Knowledge Agent returned them
                    citations = payload.get("citations", [])
                    if not citations and "metadata" in payload:
                        citations = payload.get("metadata", {}).get("citations", [])
                        
                    parsed_messages.append({
                        "role": role, 
                        "text": text,
                        "citations": citations
                    })

            # Match sequential user -> bot messages to form QA pairs
            for i in range(len(parsed_messages) - 1):
                if parsed_messages[i]["role"] == "user" and parsed_messages[i+1]["role"] == "bot":
                    qa_pairs.append({
                        "input": parsed_messages[i]["text"],
                        "output": parsed_messages[i+1]["text"],
                        "citations": parsed_messages[i+1].get("citations", [])
                    })

        return qa_pairs
