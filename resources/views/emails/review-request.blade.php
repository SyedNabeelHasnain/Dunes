@extends('emails.layout')

@section('title', 'Rate Your Desert Adventure - Dunes Discovery')

@section('content')
<div style="text-align: center; margin-bottom: 25px;">
    <h2 style="color: #111827; font-size: 22px; font-weight: bold; margin-bottom: 8px;">How was your desert adventure?</h2>
    <p style="color: #6b7280; font-size: 15px; margin-top: 0;">
        Dear {{ $booking->name }}, thank you for choosing Dunes Discovery Tourism for your <strong>{{ $booking->tour_name }}</strong> on {{ $booking->tour_date ? $booking->tour_date->format('M j, Y') : 'your recent tour' }}.
    </p>
</div>

<div style="background-color: #fef8f3; border: 1px solid #fbd9be; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 25px;">
    <p style="color: #1f2937; font-weight: bold; font-size: 16px; margin-bottom: 15px;">Please tap a rating to share your feedback:</p>
    
    <!-- Interactive Rating Stars & Buttons -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('review.rate', ['ref' => $booking->reference, 'score' => 5]) }}" style="display: inline-block; background-color: #F58F43; color: #ffffff; text-decoration: none; padding: 12px 18px; border-radius: 25px; font-weight: bold; font-size: 15px; margin: 4px; box-shadow: 0 2px 4px rgba(245, 143, 67, 0.3);">
            ⭐⭐⭐⭐⭐ Outstanding (5/5)
        </a>
    </div>
    <div>
        <a href="{{ route('review.rate', ['ref' => $booking->reference, 'score' => 4]) }}" style="display: inline-block; background-color: #ffffff; color: #374151; border: 1px solid #d1d5db; text-decoration: none; padding: 8px 14px; border-radius: 20px; font-size: 13px; margin: 3px;">
            ⭐⭐⭐⭐ Great (4/5)
        </a>
        <a href="{{ route('review.rate', ['ref' => $booking->reference, 'score' => 3]) }}" style="display: inline-block; background-color: #ffffff; color: #374151; border: 1px solid #d1d5db; text-decoration: none; padding: 8px 14px; border-radius: 20px; font-size: 13px; margin: 3px;">
            ⭐⭐⭐ Average (3/5)
        </a>
        <a href="{{ route('review.rate', ['ref' => $booking->reference, 'score' => 2]) }}" style="display: inline-block; background-color: #ffffff; color: #374151; border: 1px solid #d1d5db; text-decoration: none; padding: 8px 14px; border-radius: 20px; font-size: 13px; margin: 3px;">
            ⭐⭐ Needs Improvement
        </a>
    </div>
</div>

<p style="color: #6b7280; font-size: 14px; text-align: center;">
    Your feedback directly helps fellow travelers discover authentic Dubai desert experiences. We look forward to welcoming you and your family back soon!
</p>
@endsection