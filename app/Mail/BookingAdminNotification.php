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

class BookingAdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public ?string $pdfBinary = null;

    public ?string $pdfFileName = null;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Attach in-memory PDF ticket voucher.
     */
    public function withPdfAttachment(?string $binary, ?string $filename = null): self
    {
        $this->pdfBinary = $binary;
        $this->pdfFileName = $filename ?: 'Dunes-Discovery-Ticket-Voucher-' . $this->booking->reference . '.pdf';

        return $this;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Booking - Ref: {$this->booking->reference}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-admin',
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
