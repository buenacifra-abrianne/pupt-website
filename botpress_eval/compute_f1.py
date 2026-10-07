"""
compute_f1.py
─────────────
Reads evaluation_report.csv and produces f1_metrics.csv
containing standard F1-score, Precision, Recall, and supporting
counts based on the Relevancy Pass/Fail column.

F1 Definition used here (binary single-class):
  TP = PASS count  (bot correctly answered the user's question)
  FN = FAIL count  (bot failed to answer the user's question)
  FP = 0           (we evaluate all fetched logs, no "false alarm" class)
  TN = 0

  Precision = TP / (TP + FP)  →  TP / TP  =  1.0
  Recall    = TP / (TP + FN)
  F1        = 2 × P × R / (P + R)
"""

import os
import csv
import math

INPUT_FILE  = "evaluation_report.csv"
OUTPUT_FILE = "f1_metrics.csv"


def compute_f1():
    if not os.path.exists(INPUT_FILE):
        print(f"[Error] {INPUT_FILE} not found. Run the evaluation first.")
        return

    rows = []
    with open(INPUT_FILE, newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        for row in reader:
            rows.append(row)

    if not rows:
        print(f"[Error] {INPUT_FILE} is empty.")
        return

    # ── Count TP / FN ──────────────────────────────────────────────────────
    tp = sum(1 for r in rows if r.get("Relevancy Pass", "").strip().upper() == "PASS")
    fn = sum(1 for r in rows if r.get("Relevancy Pass", "").strip().upper() == "FAIL")
    fp = 0   # by definition (see module docstring)
    total = len(rows)

    # ── Scores ────────────────────────────────────────────────────────────
    rel_scores = []
    for r in rows:
        try:
            rel_scores.append(float(r.get("Relevancy Score (0-10)", 0)))
        except (ValueError, TypeError):
            pass

    avg_relevancy = round(sum(rel_scores) / len(rel_scores), 2) if rel_scores else 0

    # ── F1 Calculation ────────────────────────────────────────────────────
    precision = tp / (tp + fp) if (tp + fp) > 0 else 0.0
    recall    = tp / (tp + fn) if (tp + fn) > 0 else 0.0
    f1        = (2 * precision * recall / (precision + recall)
                 if (precision + recall) > 0 else 0.0)
    pass_rate = round(tp / total * 100, 1) if total > 0 else 0

    # ── Per-Row Detail ────────────────────────────────────────────────────
    detail_rows = []
    for r in rows:
        p = r.get("Relevancy Pass", "N/A").strip().upper()
        try:
            score = float(r.get("Relevancy Score (0-10)", 0))
        except (ValueError, TypeError):
            score = 0
        detail_rows.append({
            "Index"               : r.get("Index", ""),
            "User Input"          : r.get("User Input", "")[:80],
            "Relevancy Score"     : score,
            "Relevancy Pass"      : p,
            "TP"                  : 1 if p == "PASS" else 0,
            "FN"                  : 1 if p == "FAIL" else 0,
        })

    # ── Write Output CSV ──────────────────────────────────────────────────
    with open(OUTPUT_FILE, "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)

        # Section 1: F1 Summary
        writer.writerow(["=== F1 METRIC SUMMARY ==="])
        writer.writerow(["Metric", "Value"])
        writer.writerow(["Total Test Cases",   total])
        writer.writerow(["True Positives (TP)", tp])
        writer.writerow(["False Negatives (FN)", fn])
        writer.writerow(["False Positives (FP)", fp])
        writer.writerow(["Precision",            round(precision, 4)])
        writer.writerow(["Recall",               round(recall, 4)])
        writer.writerow(["F1 Score",             round(f1, 4)])
        writer.writerow(["Pass Rate (%)",        pass_rate])
        writer.writerow(["Avg Relevancy (0-10)", avg_relevancy])
        writer.writerow([])

        # Section 2: Per-row breakdown
        writer.writerow(["=== PER-CASE BREAKDOWN ==="])
        writer.writerow(["Index", "User Input", "Relevancy Score", "Pass/Fail", "TP", "FN"])
        for d in detail_rows:
            writer.writerow([
                d["Index"],
                d["User Input"],
                d["Relevancy Score"],
                d["Relevancy Pass"],
                d["TP"],
                d["FN"],
            ])

    # ── Print Summary ──────────────────────────────────────────────────────
    print("\n" + "="*55)
    print("  F1 METRIC REPORT")
    print("="*55)
    print(f"  Total Cases            : {total}")
    print(f"  True Positives (PASS)  : {tp}")
    print(f"  False Negatives (FAIL) : {fn}")
    print(f"  False Positives        : {fp}")
    print(f"  {'-'*45}")
    print(f"  Precision   →  {round(precision, 4)}  ({round(precision*100, 1)}%)")
    print(f"  Recall      →  {round(recall, 4)}  ({round(recall*100, 1)}%)")
    print(f"  F1 Score    →  {round(f1, 4)}  ({round(f1*100, 2)}%)")
    print(f"  {'-'*45}")
    print(f"  Pass Rate              : {pass_rate}%")
    print(f"  Avg Relevancy Score    : {avg_relevancy} / 10")
    print("="*55)
    print(f"\n  Saved to: {OUTPUT_FILE}")


if __name__ == "__main__":
    compute_f1()
