<?php
namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/*
* This Mailable is used to notify the user that their hardware recommendations are ready, or if there was an issue with their request. 
It takes a FormSubmission object and an optional boolean indicating if the request failed. 
The email subject and content are determined based on the status of the request.
*/
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