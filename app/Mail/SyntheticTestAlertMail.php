<?php

namespace App\Mail;

use App\Models\SyntheticTestRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SyntheticTestAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SyntheticTestRun $run,
        public Collection $failedSteps,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[QA Alert] Synthetic Visitor Journey Test #{$this->run->id} ".strtoupper($this->run->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.synthetic-test-alert',
        );
    }
}
