<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $phone;
    public string $subjectText;
    public string $messageText;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $email, string $phone, string $subjectText, string $messageText)
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->subjectText = $subjectText;
        $this->messageText = $messageText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Contact Message: {$this->subjectText}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-admin',
            with: [
                'subject' => $this->subjectText
            ]
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
