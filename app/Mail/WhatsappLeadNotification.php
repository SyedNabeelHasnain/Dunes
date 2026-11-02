<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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

    public ?string $trafficChannel;

    public ?string $location;

    public ?string $deviceType;

    public ?string $refCode;

    public ?int $inquiryId;

    public ?string $directChatUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $name,
        string $phone,
        string $tourName,
        string $pageUrl,
        string $messageText,
        ?string $trafficChannel = null,
        ?string $location = null,
        ?string $deviceType = null,
        ?string $refCode = null,
        ?int $inquiryId = null
    ) {
        $this->name = $name;
        $this->phone = $phone;
        $this->tourName = $tourName;
        $this->pageUrl = $pageUrl;
        $this->messageText = $messageText;
        $this->trafficChannel = $trafficChannel;
        $this->location = $location;
        $this->deviceType = $deviceType;
        $this->refCode = $refCode;
        $this->inquiryId = $inquiryId;

        $clean = preg_replace('/[^0-9]/', '', $phone);
        $this->directChatUrl = ! empty($clean) && strlen($clean) >= 7 ? "https://wa.me/{$clean}" : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $ref = $this->refCode ? "[#{$this->refCode}] " : '';
        $channel = $this->trafficChannel ? " ({$this->trafficChannel})" : '';

        return new Envelope(
            subject: "New WhatsApp Lead {$ref}{$this->name} - {$this->tourName}{$channel}"
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
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
