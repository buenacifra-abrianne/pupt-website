"""Backfill existing evaluation cases from read-only Botpress conversation logs."""
import csv
from datetime import datetime, timezone
from pathlib import Path
import shutil

from botpress_client import BotpressClient
from latency import LATENCY_COLUMN, summarize_latency

ROOT = Path(__file__).resolve().parent


def timings(messages):
    # Do not guess ordering when any timestamp is invalid.
    if any(BotpressClient._timestamp(m.get("createdAt")) is None for m in messages):
        return {}
    ordered = sorted(messages, key=lambda m: BotpressClient._timestamp(m["createdAt"]))
    pending = None
    result = {}
    for message in ordered:
        direction = message.get("direction")
        text = message.get("payload", {}).get("text")
        if direction == "incoming":
            pending = message if isinstance(text, str) and text.strip() else None
        elif direction == "outgoing" and pending and isinstance(text, str) and text.strip():
            result[message["id"]] = {
                "seconds": BotpressClient._timestamp(message["createdAt"]) - BotpressClient._timestamp(pending["createdAt"]),
                "input": pending["payload"]["text"],
                "created_at": message["createdAt"],
            }
            pending = None
    return result


def main():
    report = ROOT / "evaluation_report.csv"
    with report.open(newline="", encoding="utf-8-sig") as file:
        reader = csv.DictReader(file)
        fields = list(reader.fieldnames or [])
        rows = list(reader)
    if not rows:
        raise SystemExit("No evaluation cases available")
    client = BotpressClient()
    cache = {}
    for row in rows:
        conversation = row["Conversation ID"]
        if conversation not in cache:
            cache[conversation] = timings(client.fetch_messages(conversation))
        match = cache[conversation].get(row["Message ID"])
        valid = match and match["input"] == row["User Input"] and match["created_at"] == row["Created At"]
        row[LATENCY_COLUMN] = round(match["seconds"], 6) if valid else ""
    if LATENCY_COLUMN not in fields:
        fields.append(LATENCY_COLUMN)
    # Preserve existing scores and a full backup; write only after every fetch succeeds.
    archive = ROOT / "report_history" / datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%S%fZ")
    archive.mkdir(parents=True)
    shutil.copy2(report, archive / report.name)
    temporary = report.with_suffix(".csv.tmp")
    with temporary.open("w", newline="", encoding="utf-8") as file:
        writer = csv.DictWriter(file, fieldnames=fields)
        writer.writeheader()
        writer.writerows(rows)
    temporary.replace(report)
    summary = summarize_latency(rows)
    with (ROOT / "latency_metrics.csv").open("w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["Metric", "Value"])
        writer.writerows(summary.items())
    for key, value in summary.items():
        print(f"{key}: {value}")
    print("Population: existing evaluated answered text turns; no additional quality filtering.")
    print("Measure: first outgoing text createdAt minus incoming text createdAt; not browser delivery time.")


if __name__ == "__main__":
    main()
