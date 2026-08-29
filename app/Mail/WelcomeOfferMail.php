<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeOfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public float $discount;
    public string $name;
    public string $bookingUrl;
    public string $brandColor = '#F58F43';

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, float $discount = 25.00, ?string $name = null)
    {
        $this->code = $code;
        $this->discount = $discount;
        $this->name = $name ?: 'Valued Traveler';
        $this->bookingUrl = url('/') . '?promo=' . urlencode($code);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎟️ Your 25% OFF Voucher: {$this->code} - Dunes Discovery Tourism"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-offer',
        );
    }
}
