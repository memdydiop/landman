<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryClientConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SIBEA-CI — Votre demande #'.$this->inquiry->id.' bien reçue',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inquiry-client',
            with: ['inquiry' => $this->inquiry->loadMissing(['program', 'plot'])],
        );
    }
}
