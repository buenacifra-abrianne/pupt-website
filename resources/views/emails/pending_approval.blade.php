@extends('emails.layout')

@section('content')
<!-- The White Card -->
<table cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e0e0e0;">
    <tr>
        <td style="padding: 30px;">
            <!-- Title -->
            <div style="text-align: center; font-size: 19px; font-weight: 800; color: #111111; margin-bottom: 25px; line-height: 1.4;">
                <span style="display: inline-block; width: 28px; height: 28px; background-color: #f59e0b; color: #ffffff; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; font-size: 18px; margin-right: 5px; vertical-align: middle;">!</span> 
                <span style="vertical-align: middle;">ACTION REQUIRED:</span><br>
                <span style="vertical-align: middle;">NEW PENDING REQUEST</span>
            </div>

            <!-- Grid Table -->
            <table width="100%" cellpadding="15" cellspacing="0" border="0" style="border-top: 1px solid #eeeeee;">
                <tr>
                    <td width="50%" valign="top" style="border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; padding: 15px 15px 15px 0;">
                        <div style="font-size: 12px; font-weight: 700; color: #666666; margin-bottom: 5px;">REQUESTER:</div>
                        <div style="font-size: 15px; color: #222222; word-break: break-word;">{{ $requesterName }}</div>
                    </td>
                    <td width="50%" valign="top" style="border-bottom: 1px solid #eeeeee; padding: 15px 0 15px 15px;">
                        <div style="font-size: 12px; font-weight: 700; color: #666666; margin-bottom: 5px;">REQUEST TYPE:</div>
                        <div style="font-size: 15px; color: #222222; word-break: break-word;">{{ $type }}</div>
                    </td>
                </tr>
                <tr>
                    <td width="50%" valign="top" style="border-right: 1px solid #eeeeee; padding: 15px 15px 0 0;">
                        <div style="font-size: 12px; font-weight: 700; color: #666666; margin-bottom: 5px;">CONTENT TITLE:</div>
                        <div style="font-size: 15px; color: #222222; word-break: break-word;">{{ $title }}</div>
                    </td>
                    <td width="50%" valign="top" style="padding: 15px 0 0 15px;">
                        <div style="font-size: 12px; font-weight: 700; color: #666666; margin-bottom: 5px;">SUBMITTED AT:</div>
                        <div style="font-size: 15px; color: #222222; word-break: break-word;">{{ $date }}</div>
                    </td>
                </tr>
            </table>

            <!-- Button -->
            <div style="text-align: center; margin-top: 35px;">
                <a href="{{ route('superadmin.approvals.pending') }}" style="background-color: #800000; color: #ffffff; font-weight: bold; font-size: 14px; text-decoration: none; padding: 14px 28px; border-radius: 6px; display: inline-block;">
                    REVIEW IN DASHBOARD
                </a>
            </div>
        </td>
    </tr>
</table>
@endsection
