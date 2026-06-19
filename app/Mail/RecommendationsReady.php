<?php
namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecommendationsReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public bool $failed = false
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->failed
                ? 'There was an issue with your hardware request'
                : 'Your Hardware Recommendations Are Ready'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recommendations',
        );
    }
}