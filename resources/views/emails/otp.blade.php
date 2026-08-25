@extends('emails.layout')

@section('title', 'Your Verification Code')

@section('content')
    <h2 style="text-align: center;">Email Verification Code</h2>
    
    <p>Please use the verification code below to verify your email address:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <div style="display: inline-block; padding: 15px 30px; border: 2px dashed #F58F43; background-color: #fffaf5; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #333333; border-radius: 8px;">
            {{ $otp }}
        </div>
    </div>
    
    <p style="text-align: center; color: #e74c3c;"><strong>Note:</strong> This code will expire in 5 minutes.</p>
    
    <p>If you did not request this verification code, please ignore this email.</p>
@endsection
