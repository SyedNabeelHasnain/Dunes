@extends('emails.layout')

@section('title', 'Synthetic E2E Journey Test Alert')

@section('content')
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; padding: 5px 12px; border-radius: 9999px; background-color: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
            Automated E2E Journey Alert
        </span>
        <h1 style="margin: 12px 0 6px; font-size: 20px; font-weight: 800; color: #0f172a;">
            Synthetic Test Run #{{ $run->id }} {{ strtoupper($run->status) }}
        </h1>
        <p style="margin: 0; color: #475569; font-size: 13px;">
            {{ $run->summary }}
        </p>
    </div>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 18px 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; font-size: 12px;">
        <tr style="background-color: #f8fafc;">
            <td style="padding: 10px 14px; font-weight: 700; color: #475569; width: 35%;">Trigger Type</td>
            <td style="padding: 10px 14px; color: #0f172a; font-weight: 600;">{{ ucfirst($run->trigger_type) }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 14px; font-weight: 700; color: #475569; border-top: 1px solid #e2e8f0;">Locale &amp; Device</td>
            <td style="padding: 10px 14px; color: #0f172a; border-top: 1px solid #e2e8f0;">{{ strtoupper($run->locale) }} · {{ ucfirst($run->device_profile) }}</td>
        </tr>
        <tr style="background-color: #f8fafc;">
            <td style="padding: 10px 14px; font-weight: 700; color: #475569; border-top: 1px solid #e2e8f0;">Flows / Steps</td>
            <td style="padding: 10px 14px; color: #0f172a; border-top: 1px solid #e2e8f0;">
                {{ $run->passed_flows }}/{{ $run->total_flows }} flows passed · {{ $run->passed_steps }}/{{ $run->total_steps }} steps passed
            </td>
        </tr>
    </table>

    <h3 style="margin: 22px 0 10px; font-size: 14px; font-weight: 800; color: #991b1b;">Failing Steps</h3>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #fecaca; font-size: 12px;">
        <thead>
            <tr style="background-color: #fef2f2; color: #991b1b; text-align: left;">
                <th style="padding: 8px 10px; border-bottom: 1px solid #fecaca;">Flow</th>
                <th style="padding: 8px 10px; border-bottom: 1px solid #fecaca;">Step</th>
                <th style="padding: 8px 10px; border-bottom: 1px solid #fecaca;">HTTP</th>
                <th style="padding: 8px 10px; border-bottom: 1px solid #fecaca;">Error</th>
            </tr>
        </thead>
        <tbody>
            @foreach($failedSteps as $step)
                <tr>
                    <td style="padding: 8px 10px; border-bottom: 1px solid #fee2e2; font-weight: 700; text-transform: uppercase;">{{ $step->flow }}</td>
                    <td style="padding: 8px 10px; border-bottom: 1px solid #fee2e2;">{{ $step->title }}</td>
                    <td style="padding: 8px 10px; border-bottom: 1px solid #fee2e2;">{{ $step->http_status ?: '—' }}</td>
                    <td style="padding: 8px 10px; border-bottom: 1px solid #fee2e2; color: #7f1d1d;">{{ $step->error_message }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 26px; text-align: center;">
        <a href="{{ route('admin.qa-simulator.show', $run->id) }}" style="display: inline-block; padding: 12px 24px; border-radius: 8px; background-color: #F58F43; color: #ffffff; font-size: 13px; font-weight: 700; text-decoration: none;">
            Inspect Full Run Waterfall in CMS &rarr;
        </a>
    </div>
@endsection
