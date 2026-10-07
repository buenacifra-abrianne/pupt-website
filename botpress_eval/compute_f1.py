"""Summarize the current KB-grounded run; never invent scores for legacy/error rows."""
import csv
import math
from pathlib import Path

ROOT = Path(__file__).resolve().parent
INPUT_FILE = ROOT / "evaluation_report.csv"
OUTPUT_FILE = ROOT / "f1_metrics.csv"


def numbers(rows, key):
    values = []
    for row in rows:
        try:
            value = float(row[key])
            if math.isfinite(value):
                values.append(value)
        except (ValueError, TypeError, KeyError):
            pass
    return values


def average(rows, key):
    values = numbers(rows, key)
    return round(sum(values) / len(values), 3) if values else "N/A"


def summarize(rows):
    scored = [r for r in rows if r.get("Evaluation Status") == "OK"]
    total, n = len(rows), len(scored)
    counts = {key: sum(int(r[key]) for r in scored) for key in ("TP", "FN", "FP", "TN")}
    tp, fn, fp, tn = (counts[k] for k in ("TP", "FN", "FP", "TN"))
    precision = tp / (tp + fp) if tp + fp else None
    recall = tp / (tp + fn) if tp + fn else None
    f1 = 2 * tp / (2 * tp + fp + fn) if 2 * tp + fp + fn else None
    unsupported = sum(r["Unsupported Answer"] == "YES" for r in scored)
    passed = sum(r["Overall Pass"] == "PASS" for r in scored)
    summary = {
        "Total Cases": total,
        "Scored Cases": n,
        "Evaluation Errors": sum(r.get("Evaluation Status") == "ERROR" for r in rows),
        "Legacy / Unscored Cases": total - n - sum(r.get("Evaluation Status") == "ERROR" for r in rows),
        "Incomplete Cases": sum(r.get("Overall Pass") == "INCOMPLETE" for r in scored),
        "True Positive": tp, "False Negative": fn, "False Positive": fp, "True Negative": tn,
        "Relevancy (avg 0-10)": average(scored, "Relevancy Score (0-10)"),
        "Accuracy (avg 0-10)": average(scored, "Accuracy Score (0-10)"),
        "Faithfulness (avg 0-10)": average(scored, "Faithfulness Score (0-10)"),
        "Correct Answer / Refusal Rate (%)": round((tp + tn) / n * 100, 2) if n else "N/A",
        "Unsupported Answers (refusals)": unsupported,
        "Unsupported Answers (%)": round(unsupported / n * 100, 2) if n else "N/A",
        "Precision": round(precision, 4) if precision is not None else "N/A",
        "Recall": round(recall, 4) if recall is not None else "N/A",
        "F1 Score": round(f1, 4) if f1 is not None else "N/A",
        "Passed Cases": passed,
        "Pass Rate (%)": round(passed / total * 100, 2) if total else "N/A",
        "Avg Relevancy Score": average(scored, "Relevancy Score (0-10)"),
    }
    for key in ("Incomplete Cases", "Correct Answer / Refusal Rate (%)",
                "Unsupported Answers (%)", "Avg Relevancy Score"):
        summary.pop(key, None)
    return summary


def print_report(summary, output_file=None):
    margin = "  "
    width = 68
    sections = [
        ("CASES", ["Total Cases", "Scored Cases", "Evaluation Errors", "Legacy / Unscored Cases"]),
        ("ANSWER COUNTS", ["True Positive", "False Negative", "False Positive", "True Negative"]),
        ("F1 METRICS", ["Precision", "Recall", "F1 Score"]),
        ("QUALITY", ["Relevancy (avg 0-10)", "Accuracy (avg 0-10)", "Faithfulness (avg 0-10)",
                     "Unsupported Answers (refusals)", "Pass Rate (%)"]),
    ]
    print()
    print(margin + "=" * width)
    print(margin + "BOTPRESS EVALUATION REPORT".center(width))
    print(margin + "=" * width)
    for title, keys in sections:
        print()
        print(margin + title)
        print(margin + "-" * width)
        for key in keys:
            value = summary[key]
            if isinstance(value, (int, float)) and not isinstance(value, bool):
                if "(%)" in key:
                    value = f"{value:.2f}%"
                elif key in ("Precision", "Recall", "F1 Score"):
                    value = f"{value:.4f}"
                elif "seconds" in key:
                    value = f"{value:.3f} s"
                elif "avg" in key or key == "Avg Relevancy Score":
                    value = f"{value:.2f} / 10"
            print(f"{margin}  {key:<38} : {str(value):>14}")
    print()
    print(margin + "=" * width)
    if summary["Legacy / Unscored Cases"]:
        print()
        print(margin + "NOTE: Old report rows do not contain the new evaluation metrics.")
        print(margin + "Run python -m pytest test_botpress.py -s, then compute_f1.py.")
    if output_file is not None:
        print()
        print(margin + f"CSV saved to: {output_file}")
    print()


def compute_f1():
    if not INPUT_FILE.exists():
        raise SystemExit("evaluation_report.csv not found. Run the evaluation first.")
    with INPUT_FILE.open(newline="", encoding="utf-8-sig") as file:
        rows = list(csv.DictReader(file))
    if not rows:
        raise SystemExit("evaluation_report.csv has no cases.")
    summary = summarize(rows)
    with OUTPUT_FILE.open("w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["Metric", "Value"])
        writer.writerows(summary.items())
        writer.writerow([])
        writer.writerow(["Per-case results"])
        writer.writerow(["Index", "Status", "TP", "FN", "FP", "TN", "Unsupported Answer",
                         "Relevancy", "Accuracy", "Faithfulness", "Overall Pass"])
        for r in rows:
            writer.writerow([r.get(k, "N/A") for k in (
                "Index", "Evaluation Status", "TP", "FN", "FP", "TN", "Unsupported Answer",
                "Relevancy Score (0-10)", "Accuracy Score (0-10)", "Faithfulness Score (0-10)",
                "Overall Pass")])
    print_report(summary, OUTPUT_FILE)
    return summary


if __name__ == "__main__":
    compute_f1()
