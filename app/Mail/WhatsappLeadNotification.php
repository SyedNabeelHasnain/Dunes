<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WhatsappLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $phone;
    public string $tourName;
    public string $pageUrl;
    public string $messageText;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $phone, string $tourName, string $pageUrl, string $messageText)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->tourName = $tourName;
        $this->pageUrl = $pageUrl;
        $this->messageText = $messageText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New WhatsApp Lead: {$this->name} - {$this->tourName}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.whatsapp-lead',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
