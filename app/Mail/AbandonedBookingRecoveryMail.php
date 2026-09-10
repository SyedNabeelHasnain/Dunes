<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedBookingRecoveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $brandColor = '#F58F43';
    public string $recoveryUrl;
    public string $whatsappUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        
        // If booking already has a direct payment redirect url (Ziina), use it; otherwise link to online checkout
        $this->recoveryUrl = $booking->ziina_redirect_url ?: url("/?ref={$booking->reference}&action=checkout");
        
        $waPhone = '971502456056';
        $waMsg = rawurlencode("Hi Dunes Discovery, I have an incomplete booking #{$booking->reference} for {$booking->tour_name} on {$booking->tour_date}. Can you help me confirm it?");
        $this->whatsappUrl = "https://wa.me/{$waPhone}?text={$waMsg}";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Complete Your Dubai Desert Safari Reservation - Ref: #{$this->booking->reference}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.abandoned-booking',
        );
    }
}