@extends('emails.layout')

@section('title', ($type === 'feedback' ? 'Direct Guest Feedback' : 'New Customer Review Alert') . ' - Dunes Discovery Tourism')

@section('content')
    <div style="border-bottom: 2px solid #eab308; padding-bottom: 12px; margin-bottom: 18px;">
        @if($type === 'feedback')
            <h2 style="color: #0f172a; margin: 0 0 6px 0; font-size: 20px;">💬 Direct Guest Service Feedback</h2>
            <span style="display: inline-block; background-color: #f0fdf4; color: #166534; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px;">
                Direct Post-Tour Submission
            </span>
        @else
            <h2 style="color: #0f172a; margin: 0 0 6px 0; font-size: 20px;">⭐ New Customer Review Submitted</h2>
            <span style="display: inline-block; background-color: #fef9c3; color: #854d0e; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px;">
                Rating: {{ $review->rating }} / 5.0 {{ str_repeat('★', (int) round($review->rating)) }}
            </span>
            <span style="display: inline-block; background-color: #f1f5f9; color: #475569; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px; margin-left: 4px;">
                Status: {{ ucfirst($review->status) }}
            </span>
        @endif
    </div>
    
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px;">
        <tbody>
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 35%; font-weight: bold;">Reviewer Name:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold;">{{ $review->reviewer_name }}</td>
            </tr>
            @if($booking)
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Booking Reference:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold;">
                    #{{ $booking->reference }} ({{ $booking->tour_name }})
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Guest Contact:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">
                    {{ $booking->email }} &bull; {{ $booking->phone }}
                </td>
            </tr>
            @endif
            <tr>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;">Review Title:</td>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold;">{{ $review->review_title }}</td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin: 20px 0;">
        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 6px;">
            Guest Review Text:
        </div>
        <div style="background-color: #f8fafc; border-left: 4px solid #eab308; padding: 12px 14px; font-size: 13px; color: #1e293b; line-height: 1.6;">
            "{{ $review->review_text }}"
        </div>
    </div>

    @if(!empty($review->photos) && is_array($review->photos) && count($review->photos) > 0)
    <div style="margin: 20px 0;">
        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 6px;">
            Attached Customer Photos ({{ count($review->photos) }}):
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @foreach($review->photos as $photo)
                <img src="{{ url($photo) }}" alt="Customer Safari Photo" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
            @endforeach
        </div>
    </div>
    @endif

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ url('/admin/reviews') }}" style="background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 11px 24px; border-radius: 50px; font-weight: bold; display: inline-block; font-size: 13px;">
            Moderate Reviews in Admin CMS
        </a>
    </div>
@endsection
