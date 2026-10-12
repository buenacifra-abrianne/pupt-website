# AI Help Desk response latency

Measured on 12 October 2026 from the original Botpress message logs for the
100 cases in `evaluation_report.csv`. Reply timestamps span 15 September to
6 October 2026 (Asia/Singapore). All 100 cases had valid matched timings;
no missing or invalid latency observations were excluded.

| Measure | Result |
| --- | ---: |
| Valid observations | 100 |
| Median response time | 6.550 seconds |
| 95th percentile response time | 11.439 seconds |
| Proposed real-time threshold | 5.000 seconds |
| Replies within 5 seconds | 17 of 100 (17%) |
| P95 <= 5 seconds | Not met |

## Study text

Response latency was measured using Botpress conversation logs as the elapsed
time between the creation timestamp of an incoming user text message and that
of the first outgoing bot text reply in the same conversation. Across 100
evaluated interactions, the median response time was 6.550 seconds and the
95th percentile response time was 11.439 seconds. The 95th percentile was
calculated using the nearest-rank method, selecting the 95th observation after
sorting the 100 measured latencies in ascending order.

For this study, the proposed operational definition of real-time is a first
logged reply within 5 seconds, with the performance target requiring at least
95% of measured replies to satisfy this threshold (P95 <= 5 seconds). Under
this definition, the observed sample did not meet the real-time target: only
17% of replies were recorded within 5 seconds. The 5-second threshold is a
study-defined target introduced for this analysis, rather than a pre-registered
criterion, a universal standard, or a Botpress performance guarantee.

## Measurement limits and reproducibility

These are server-log measurements of time to first text reply. A first reply
may be an acknowledgement rather than the complete answer. Browser display
time, streaming completion, and network delivery delays were not measured.
Chronological pairing assumes sequential turns; timestamps alone do not
establish causal attribution for overlapping user requests.

The population is the existing answer-quality evaluation sample, which already
excluded certain refusal/limit notices and unanswered questions. This is not a
random sample or a measurement of every help desk request. No additional
filtering by answer-quality score was applied to the latency calculations.

Each original conversation/message ID, question, and reply timestamp was
matched against the logs. Measured latencies are saved alongside the existing
scores in `evaluation_report.csv`; summaries appear in `latency_metrics.csv`
and `f1_metrics.csv`. A backup of the original evaluation report is retained
under the ignored `report_history/` directory. Future evaluations also save
latency. To refresh the measurements for the current report:

```powershell
cd botpress_eval
python compute_latency.py
python compute_f1.py
```

Botpress documents incoming/outgoing message direction and creation timestamps
in its [Runtime API concepts](https://botpress.com/docs/api-reference/runtime-api/concepts/).
