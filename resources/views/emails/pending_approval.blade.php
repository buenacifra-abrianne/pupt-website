@extends('emails.layout')

@section('content')
    <div class="title-wrapper">
        <span class="alert-icon">!</span>
        <div class="title-text">
            ACTION REQUIRED:<br>
            NEW PENDING REQUEST
        </div>
    </div>

    <table class="details-grid">
        <tr>
            <td class="details-cell">
                <div class="label">REQUESTER:</div>
                <div class="value">{{ $requesterName }}</div>
            </td>
            <td class="details-cell">
                <div class="label">REQUEST TYPE:</div>
                <div class="value">{{ $type }}</div>
            </td>
        </tr>
        <tr>
            <td class="details-cell">
                <div class="label">CONTENT TITLE:</div>
                <div class="value">{{ $title }}</div>
            </td>
            <td class="details-cell">
                <div class="label">SUBMITTED AT:</div>
                <div class="value">{{ $date }}</div>
            </td>
        </tr>
    </table>
@endsection
