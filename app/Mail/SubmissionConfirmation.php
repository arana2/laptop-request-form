<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/*
* This Mailable is used to confirm to the user that their hardware request has been received.
*/
class SubmissionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FormSubmission $submission)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your hardware request — ' . $this->submission->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission-confirmation',
        );
    }
}