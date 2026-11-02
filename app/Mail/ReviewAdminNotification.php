<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Review $review;

    public ?Booking $booking;

    public string $type; // 'review' or 'feedback'

    /**
     * Create a new message instance.
     */
    public function __construct(Review $review, ?Booking $booking = null, string $type = 'review')
    {
        $this->review = $review;
        $this->booking = $booking;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $refStr = $this->booking ? " [Ref: #{$this->booking->reference}]" : '';

        if ($this->type === 'feedback') {
            return new Envelope(
                subject: "Direct Guest Service Feedback{$refStr} - {$this->review->reviewer_name}"
            );
        }

        $stars = str_repeat('⭐', max(1, (int) round($this->review->rating)));

        return new Envelope(
            subject: "New Customer Review ({$this->review->rating}/5 {$stars}){$refStr} - {$this->review->reviewer_name}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.review-admin',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
