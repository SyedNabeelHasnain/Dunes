<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $type;

    public Booking $booking;

    public string $brandColor = '#F58F43';

    public ?string $pdfBinary = null;

    public ?string $pdfFileName = null;

    /**
     * Create a new message instance.
     */
    public function __construct(string $type, Booking $booking)
    {
        $this->type = $type;
        $this->booking = $booking;
    }

    /**
     * Attach in-memory PDF ticket voucher.
     */
    public function withPdfAttachment(?string $binary, ?string $filename = null): self
    {
        $this->pdfBinary = $binary;
        $this->pdfFileName = $filename ?: 'Dunes-Discovery-Ticket-Voucher-' . $this->booking->reference . '.pdf';

        if (! empty($binary)) {
            $this->attachData($binary, $this->pdfFileName, [
                'mime' => 'application/pdf',
            ]);
        }

        return $this;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'booking_cash' => "Booking Received - Ref: {$this->booking->reference}",
            'booking_advance' => "Advance Payment Received - Ref: {$this->booking->reference}",
            'booking_full' => "Payment Successful - Ref: {$this->booking->reference}",
            'booking_confirmed' => "Booking Confirmed - Ref: {$this->booking->reference}",
            'booking_cancelled' => "Booking Cancelled - Ref: {$this->booking->reference}",
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'Booking Update'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! empty($this->pdfBinary)) {
            return [
                Attachment::fromData(fn () => $this->pdfBinary, $this->pdfFileName ?: 'Dunes-Discovery-Ticket-Voucher.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
