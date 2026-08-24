@extends('emails.layout')

@section('content')
    <div class="highlight-box">
        <p>Hello <strong>{{ $requesterName }}</strong>,</p>
        <p>Your request has been</p>
        <h2 style="font-size: 36px; color: {{ strtolower($status) === 'approved' ? '#28a745' : '#dc3545' }};">{{ ucfirst(strtolower($status)) }}</h2>
    </div>
    <div class="text-content">
        <ul>
            <li><strong>Request Type:</strong> {{ $type }}</li>
            <li><strong>Title:</strong> {{ $title }}</li>
            <li><strong>Reviewed At:</strong> {{ $date }}</li>
            @if($reason)
                <li><strong>Reason:</strong> {{ $reason }}</li>
            @endif
        </ul>
    </div>
@endsection
