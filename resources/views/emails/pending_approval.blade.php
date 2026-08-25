@extends('emails.layout')

@section('content')
    <div class="highlight-box">
        <h2 style="font-size: 32px;">New Pending Request</h2>
    </div>
    <div class="text-content">
        <ul>
            <li><strong>Requester:</strong> {{ $requesterName }}</li>
            <li><strong>Request Type:</strong> {{ $type }}</li>
            <li><strong>Title:</strong> {{ $title }}</li>
            <li><strong>Submitted At:</strong> {{ $date }}</li>
            <li><strong>Status:</strong> <span style='color:orange;'>Pending</span></li>
        </ul>
        <p>Please log in to dashboard to review.</p>
    </div>
@endsection
