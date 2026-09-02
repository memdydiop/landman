<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaign extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $subjectLine, public string $bodyMd) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter-campaign',
            with: ['bodyMd' => $this->bodyMd],
        );
    }
}
