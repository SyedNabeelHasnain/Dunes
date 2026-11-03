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

class BookingAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public ?string $pdfBinary = null;

    public ?string $pdfFileName = null;

    public ?string $pdfFilePath = null;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Attach in-memory PDF ticket voucher binary.
     */
    public function withPdfAttachment(?string $binary, ?string $filename = null): self
    {
        $this->pdfBinary = $binary;
        $this->pdfFileName = $filename ?: 'Dunes-Discovery-Ticket-Voucher-' . $this->booking->reference . '.pdf';

        return $this;
    }

    /**
     * Attach PDF ticket voucher from server filesystem path.
     */
    public function withPdfFile(?string $filePath, ?string $filename = null): self
    {
        $this->pdfFilePath = $filePath;
        if ($filename) {
            $this->pdfFileName = $filename;
        }

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

        if (! empty($this->pdfFilePath) && file_exists($this->pdfFilePath)) {
            return [
                Attachment::fromPath($this->pdfFilePath)
                    ->as($this->pdfFileName ?: basename($this->pdfFilePath))
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
