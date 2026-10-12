"""Statistics for measured Botpress first-reply latency, independent of AI scoring."""
import math
import statistics

LATENCY_COLUMN = "Response Latency (seconds)"
REALTIME_THRESHOLD_SECONDS = 5.0


def summarize_latency(rows, threshold=REALTIME_THRESHOLD_SECONDS):
    if not math.isfinite(threshold) or threshold <= 0:
        raise ValueError("Real-time threshold must be finite and positive")
    values = []
    for row in rows:
        try:
            value = float(row.get(LATENCY_COLUMN, ""))
        except (TypeError, ValueError):
            continue
        if math.isfinite(value) and value >= 0:
            values.append(value)
    values.sort()
    # Nearest-rank percentile: the ceil(0.95*n)-th ordered observation.
    p95 = values[math.ceil(0.95 * len(values)) - 1] if values else None
    return {
        "Latency Sample Size": len(values),
        "Latency Missing / Invalid Cases": len(rows) - len(values),
        "Median Response Time (seconds)": round(statistics.median(values), 3) if values else "N/A",
        "95th Percentile Response Time (seconds)": round(p95, 3) if values else "N/A",
        "Real-time Threshold (seconds)": threshold,
        "Responses Within Threshold (%)": round(100 * sum(v <= threshold for v in values) / len(values), 2) if values else "N/A",
        "Real-time Criterion (P95 <= threshold)": ("MET" if p95 <= threshold else "NOT MET") if values else "N/A",
    }
