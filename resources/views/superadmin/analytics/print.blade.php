<!DOCTYPE html>
<html>
<head>
<title>PUP Taguig Website Analytics Report</title>

<style>

body{
    font-family: Arial, sans-serif;
    margin:40px;
    color:#222;
}

.header{
    display:flex;
    align-items:center;
    border-bottom:4px solid #7b0000;
    padding-bottom:15px;
    margin-bottom:30px;
}

.logo{
    width:70px;
    margin-right:20px;
}

.title-block h1{
    margin:0;
    color:#7b0000;
    font-size:28px;
}

.title-block p{
    margin:4px 0;
    font-size:14px;
}

.section{
    margin-top:35px;
}

.section h2{
    color:#7b0000;
    border-bottom:2px solid #ddd;
    padding-bottom:5px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

table td{
    border:1px solid #ccc;
    padding:10px;
}

table tr:nth-child(even){
    background:#f5f5f5;
}

.print-btn{
    margin-bottom:25px;
    padding:8px 14px;
    border:none;
    background:#7b0000;
    color:white;
    cursor:pointer;
}

.print-btn:hover{
    background:#5a0000;
}

.report-footer{
    margin-top:40px;
    padding-top:14px;
    border-top:1px solid #bbb;
    font-size:12px;
    line-height:1.45;
    color:#333;
}

.report-footer p{
    margin:0 0 6px 0;
}

/* hide button in pdf */
@media print{
    .print-btn{
        display:none;
    }

    body{
        padding-bottom:90px;
    }

    .report-footer{
        position:fixed;
        left:40px;
        right:40px;
        bottom:20px;
        margin-top:0;
        background:#fff;
    }
}

</style>
</head>

<body>

<button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

<div class="header">

<img src="{{ asset('assets/static_img/logo.png') }}" class="logo">

<div class="title-block">
<h1>PUP Taguig Website Analytics Report</h1>
<p>Polytechnic University of the Philippines – Taguig Campus</p>
<p><strong>Date Range:</strong> {{ $start }} to {{ $end }}</p>
</div>

</div>


<div class="section">
<h2>KPIs</h2>

<table>
<tr>
<td>Total Visitors</td>
<td>{{ $data['total_visitors'] }}</td>
</tr>

<tr>
<td>Avg Session Duration</td>
<td>{{ $data['avg_duration'] }}</td>
</tr>

<tr>
<td>Bounce Rate</td>
<td>{{ $data['bounce_rate'] }}</td>
</tr>
</table>

</div>


<div class="section">
<h2>User Engagement</h2>

<table>

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


<div class="report-footer">
    <p>This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and in a safe place.</p>
    <p>This is system-generated, signature is not required.</p>
</div>


</body>
</html>
