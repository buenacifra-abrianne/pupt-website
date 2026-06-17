<!DOCTYPE html>
<html>
<head>
<title>PUP Taguig Website Analytics Report</title>

<style>
body{
    font-family: Arial, sans-serif;
    margin:34px;
    color:#222;
    font-size:13px;
}

.report-actions{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:18px;
}

.report-action{
    padding:8px 14px;
    border:1px solid #7b0000;
    background:#7b0000;
    color:white;
    cursor:pointer;
    font-size:13px;
}

.report-action-secondary{
    background:#fff;
    color:#7b0000;
}

.report-actions-note{
    margin:0;
    color:#555;
    font-size:12px;
}

.header{
    display:flex;
    align-items:center;
    border-bottom:4px solid #7b0000;
    padding-bottom:14px;
    margin-bottom:18px;
}

.logo{
    width:68px;
    margin-right:18px;
}

.title-block h1{
    margin:0;
    color:#7b0000;
    font-size:25px;
}

.title-block p{
    margin:4px 0;
    font-size:13px;
}

.section{
    margin-top:24px;
    page-break-inside:avoid;
}

.section h2{
    color:#7b0000;
    border-bottom:2px solid #ddd;
    padding-bottom:5px;
    margin:0 0 12px;
    font-size:18px;
}

.summary-grid{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:10px;
}

.summary-card{
    border:1px solid #ccc;
    padding:11px;
    background:#fafafa;
}

.summary-label{
    color:#555;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.03em;
}

.summary-value{
    color:#7b0000;
    font-weight:bold;
    font-size:20px;
    margin-top:5px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
}

th,
td{
    border:1px solid #ccc;
    padding:8px;
    text-align:left;
}

th{
    background:#7b0000;
    color:#fff;
}

tr:nth-child(even) td{
    background:#f5f5f5;
}

.muted{
    color:#777;
    font-style:italic;
}

.two-col{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.report-footer{
    margin-top:34px;
    padding-top:0;
    font-size:11px;
    line-height:1.45;
    font-weight:bold;
}

.report-footer p{
    margin:0 0 5px;
}

.report-footer-right{
    color:#000;
    text-align:right;
    margin-bottom:8px;
}

.report-footer-rule{
    border:0;
    border-top:1px solid #7b0000;
    margin:0 0 8px;
}

.report-footer-left{
    color:#7b0000;
    display:inline-block;
    text-align:center;
    width:auto;
    margin-left:0;
    white-space:nowrap;
}

@media print{
    .report-actions{
        display:none;
    }

    body{
        margin:28px;
        padding-bottom:82px;
    }

    .report-footer{
        position:fixed;
        left:28px;
        right:28px;
        bottom:16px;
        margin-top:0;
        background:#fff;
    }
}
</style>
</head>

<body>
<script>
    function printReport() {
        window.print();
    }

    function saveReportAsPdf() {
        window.print();
    }
</script>
@php
    $feedbackRows = [
        ['Q1', data_get($feedback, 'question_1_avg', 0)],
        ['Q2', data_get($feedback, 'question_2_avg', 0)],
        ['Q3', data_get($feedback, 'question_3_avg', 0)],
        ['Q4', data_get($feedback, 'question_4_avg', 0)],
        ['Q5', data_get($feedback, 'question_5_avg', 0)],
        ['Q6', data_get($feedback, 'question_6_avg', 0)],
        ['Q7', data_get($feedback, 'question_7_avg', 0)],
        ['Q8', data_get($feedback, 'question_8_avg', 0)],
        ['Q9', data_get($feedback, 'question_9_avg', 0)],
        ['Q10', data_get($feedback, 'question_10_avg', 0)],
    ];

    $ratingRows = [
        ['Outstanding', data_get($feedback, 'outstanding', 0)],
        ['Very Satisfactory', data_get($feedback, 'very_satisfactory', 0)],
        ['Satisfactory', data_get($feedback, 'satisfactory', 0)],
        ['Unsatisfactory', data_get($feedback, 'unsatisfactory', 0)],
    ];

    $uploadRoles = data_get($uploads, 'roles', []);
    $uploadSources = data_get($uploads, 'sources', []);
    $serverHealthStatus = data_get($serverHealth, 'status', 'Unavailable');
@endphp

<div class="report-actions">
    <button class="report-action" type="button" onclick="printReport()">Print Report</button>
    <button class="report-action report-action-secondary" type="button" onclick="saveReportAsPdf()">Save as PDF</button>
    <p class="report-actions-note">Choose your browser's Save as PDF destination after clicking the PDF button.</p>
</div>

<div class="header">
    <img src="{{ asset('assets/static_img/logo.png') }}" class="logo" alt="PUP Logo">
    <div class="title-block">
        <h1>PUP Taguig Website Analytics Report</h1>
        <p>Polytechnic University of the Philippines - Taguig Campus</p>
        <p><strong>Date Range:</strong> {{ $start ?: 'All Dates' }} to {{ $end ?: 'All Dates' }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>
</div>

<div class="section">
    <h2>Part 1: Monitoring Overview</h2>
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Visitors</div>
            <div class="summary-value">{{ $data['total_visitors'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Avg. Session Duration</div>
            <div class="summary-value">{{ $data['avg_duration'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Bounce Rate</div>
            <div class="summary-value">{{ $data['bounce_rate'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Uploads</div>
            <div class="summary-value">{{ data_get($uploads, 'total_uploads', $data['total_uploads'] ?? 0) }}</div>
        </div>
    </div>
</div>

<div class="section">
    <h2>Part 2: User Engagement</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Sessions</td>
            <td>{{ $data['sessions'] }}</td>
        </tr>
        <tr>
            <td>Page views</td>
            <td>{{ $data['pageviews'] }}</td>
        </tr>
        <tr>
            <td>Pages / Session</td>
            <td>{{ $data['pages_per_session'] }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Part 3: Feedback Result</h2>
    <div class="two-col">
        <table>
            <tr>
                <th>Question</th>
                <th>Average Score</th>
            </tr>
            @foreach ($feedbackRows as [$label, $value])
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format((float) $value, 2) }} / 4</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Total Average</strong></td>
                <td><strong>{{ number_format((float) data_get($feedback, 'overall_average', 0), 2) }} / 4</strong></td>
            </tr>
            <tr>
                <td><strong>Final Result</strong></td>
                <td><strong>{{ data_get($feedback, 'final_rating', 'No Data') }}</strong></td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Rating</th>
                <th>Responses</th>
            </tr>
            @foreach ($ratingRows as [$label, $value])
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format((int) $value) }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Total Responses</strong></td>
                <td><strong>{{ number_format((int) data_get($feedback, 'total_responses', 0)) }}</strong></td>
            </tr>
        </table>
    </div>
</div>

<div class="section">
    <h2>Part 4: Upload Percentage</h2>
    <div class="two-col">
        <table>
            <tr>
                <th>Uploader Role</th>
                <th>Uploads</th>
                <th>Percentage</th>
            </tr>
            @forelse ($uploadRoles as $row)
                <tr>
                    <td>{{ data_get($row, 'role', 'Unknown') }}</td>
                    <td>{{ number_format((int) data_get($row, 'count', 0)) }}</td>
                    <td>{{ number_format((float) data_get($row, 'percentage', 0), 2) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="muted">No role upload data found.</td>
                </tr>
            @endforelse
        </table>

        <table>
            <tr>
                <th>Upload Source</th>
                <th>Uploads</th>
            </tr>
            @forelse ($uploadSources as $row)
                <tr>
                    <td>{{ data_get($row, 'source', 'Uploads') }}</td>
                    <td>{{ number_format((int) data_get($row, 'count', 0)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="muted">No uploads found.</td>
                </tr>
            @endforelse
        </table>
    </div>
</div>

<div class="section">
    <h2>Part 5: Announcement Reach</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Views</td>
            <td>{{ number_format((int) data_get($announcementReach, 'views', 0)) }}</td>
        </tr>
        <tr>
            <td>Unique Viewers</td>
            <td>{{ number_format((int) data_get($announcementReach, 'unique_viewers', 0)) }}</td>
        </tr>
        <tr>
            <td>Clicks</td>
            <td>{{ number_format((int) data_get($announcementReach, 'clicks', 0)) }}</td>
        </tr>
        <tr>
            <td>CTR</td>
            <td>{{ number_format((float) data_get($announcementReach, 'ctr_pct', 0), 2) }}%</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Part 6: Server Health</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Server Status</td>
            <td>{{ $serverHealthStatus }}</td>
        </tr>
        <tr>
            <td>CPU Usage</td>
            <td>{{ data_get($serverHealth, 'cpu_usage', '--') }}</td>
        </tr>
        <tr>
            <td>Memory Usage</td>
            <td>{{ data_get($serverHealth, 'memory_usage', '--') }}</td>
        </tr>
        <tr>
            <td>Last Updated</td>
            <td>{{ data_get($serverHealth, 'last_updated', '--') }}</td>
        </tr>
    </table>
    @if ($serverHealthStatus === 'Unavailable')
        <p class="muted">{{ data_get($serverHealth, 'message', 'Server health data is temporarily unavailable.') }}</p>
    @endif
</div>

<div class="report-footer">
    <div class="report-footer-right">
        <p>This is system-generated, signature is not required.</p>
    </div>
    <hr class="report-footer-rule">
    <div class="report-footer-left">
        <p>This document contains personal-identifiable information that is subject to Data Privacy.</p>
        <p>Please keep this document protected and in a safe place.</p>
    </div>
</div>
</body>
</html>
