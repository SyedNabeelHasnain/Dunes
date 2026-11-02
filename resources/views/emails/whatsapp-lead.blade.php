@extends('emails.layout')

@section('title', 'New WhatsApp Lead Alert - Dunes Discovery Tourism')

@section('content')
    <div style="border-bottom: 2px solid #22c55e; padding-bottom: 12px; margin-bottom: 18px;">
        <h2 style="color: #0f172a; margin: 0 0 6px 0; font-size: 20px;">⚡ New WhatsApp Lead Alert</h2>
        <span style="display: inline-block; background-color: #dcfce7; color: #15803d; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px;">
            Action Required: Direct Chat Initiated
        </span>
        @if(!empty($refCode))
            <span style="display: inline-block; background-color: #f1f5f9; color: #475569; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px; margin-left: 4px;">
                Ref: #{{ $refCode }}
            </span>
        @endif
    </div>
    
    <p style="color: #334155; font-size: 13px; line-height: 1.5;">
        A prospective traveler has initiated an instant WhatsApp inquiry from the Dunes Discovery Tourism portal.
    </p>
    
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px;">
        <tbody>
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 35%; font-weight: bold;">Lead Name:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold;">{{ $name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Phone / WhatsApp:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">
                    <a href="tel:{{ $phone }}" style="color: #2563eb; font-weight: bold; text-decoration: none;">{{ $phone }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Tour of Interest:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold;">{{ $tourName }}</td>
            </tr>
            @if(!empty($trafficChannel))
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Acquisition Channel:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #059669; font-weight: bold;">{{ $trafficChannel }}</td>
            </tr>
            @endif
            @if(!empty($location))
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Visitor Location:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ $location }}</td>
            </tr>
            @endif
            @if(!empty($deviceType))
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Device:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ ucfirst($deviceType) }}</td>
            </tr>
            @endif
            @if(!empty($pageUrl))
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Source Page:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">
                    <a href="{{ $pageUrl }}" target="_blank" style="color: #64748b; text-decoration: underline; font-size: 11px; word-break: break-all;">{{ $pageUrl }}</a>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
    
    <div style="margin: 20px 0;">
        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 6px;">
            Pre-composed Customer Message:
        </div>
        <div style="background-color: #f8fafc; border-left: 4px solid #22c55e; padding: 12px 14px; font-size: 13px; color: #1e293b; font-style: italic;">
            "{{ $messageText }}"
        </div>
    </div>

    @if(!empty($directChatUrl))
    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ $directChatUrl }}" target="_blank" style="background-color: #22c55e; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 50px; font-weight: bold; display: inline-block; font-size: 14px; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.35);">
            💬 Open Direct WhatsApp Chat With Guest
        </a>
    </div>
    @endif

    @if(!empty($inquiryId))
    <div style="text-align: center; margin-top: 10px;">
        <a href="{{ url('/admin/whatsapp-leads') }}" style="color: #64748b; font-size: 11px; text-decoration: underline;">
            View Lead in Admin CMS WhatsApp Hub
        </a>
    </div>
    @endif
@endsection
