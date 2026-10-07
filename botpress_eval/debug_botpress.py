import os
import requests
from dotenv import load_dotenv
import json

load_dotenv()

api_token = os.getenv("BOTPRESS_API_TOKEN")
workspace_id = os.getenv("BOTPRESS_WORKSPACE_ID")
bot_id = os.getenv("BOTPRESS_BOT_ID")

headers = {
    "Authorization": f"Bearer {api_token}",
    "x-workspace-id": workspace_id,
    "x-bot-id": bot_id,
    "Content-Type": "application/json"
}

url = "https://api.botpress.cloud/v1/chat/conversations"
res = requests.get(url, headers=headers)
convos = res.json().get("conversations", [])

for i, conv in enumerate(convos[:3]):
    conv_id = conv["id"]
    msg_url = "https://api.botpress.cloud/v1/chat/messages"
    msg_res = requests.get(msg_url, headers=headers, params={"conversationId": conv_id})
    print(f"\n--- Convo {i} ({conv_id}) messages raw response ---")
    data = msg_res.json()
    print(json.dumps(data, indent=2))
