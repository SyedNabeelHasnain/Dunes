<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $resetUrl)
    {
        $this->user = $user;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@dunesdiscoverytourism.com', 'Dunes Discovery Tourism'),
            subject: 'Reset Your Password - Dunes Discovery Tourism'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #ffffff;'>
                    <h2 style='color: #00476d; margin-top: 0;'>Reset Your Password</h2>
                    <p style='color: #333333; font-size: 15px;'>Hello <strong>".htmlspecialchars($this->user->name ?? 'Admin')."</strong>,</p>
                    <p style='color: #555555; font-size: 14px; line-height: 1.6;'>You are receiving this email because we received a password reset request for your account.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$this->resetUrl}' style='background-color: #f69044; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>Reset Password</a>
                    </div>
                    <p style='color: #777777; font-size: 13px;'>This password reset link will expire in 60 minutes.</p>
                    <p style='color: #999999; font-size: 12px; border-top: 1px solid #eeeeee; padding-top: 15px;'>If you did not request a password reset, no further action is required.</p>
                </div>
            "
        );
    }
}
