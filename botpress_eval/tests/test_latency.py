import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from compute_latency import timings
from latency import LATENCY_COLUMN, summarize_latency


def test_median_nearest_rank_and_missing_values():
    rows = [{LATENCY_COLUMN: value} for value in [*range(1, 21), "", -1, "nan", "inf"]]
    result = summarize_latency(rows)
    assert result["Latency Sample Size"] == 20
    assert result["Latency Missing / Invalid Cases"] == 4
    assert result["Median Response Time (seconds)"] == 10.5
    assert result["95th Percentile Response Time (seconds)"] == 19
    assert result["Responses Within Threshold (%)"] == 25
    assert result["Real-time Criterion (P95 <= threshold)"] == "NOT MET"


def test_threshold_uses_unrounded_values_and_empty_is_not_a_pass():
    assert summarize_latency([{LATENCY_COLUMN: 5}])["Real-time Criterion (P95 <= threshold)"] == "MET"
    assert summarize_latency([{LATENCY_COLUMN: 5.0001}])["Real-time Criterion (P95 <= threshold)"] == "NOT MET"
    assert summarize_latency([])["Real-time Criterion (P95 <= threshold)"] == "N/A"


def test_matches_first_reply_in_unsorted_logs_and_does_not_reuse_question():
    def message(id, direction, second, text):
        return {"id": id, "direction": direction, "createdAt": f"2026-10-06T00:00:{second:02d}Z", "payload": {"text": text}}
    messages = [message("b2", "outgoing", 4, "more"), message("b", "outgoing", 2, "Cannot help"),
                message("u", "incoming", 0, "question"), message("u2", "incoming", 5, "unanswered")]
    result = timings(messages)
    assert list(result) == ["b"]
    assert result["b"]["seconds"] == 2
    assert result["b"]["input"] == "question"
    messages[0]["createdAt"] = "invalid"
    assert timings(messages) == {}
