<?php

namespace App\Mail;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssignmentGraded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Assignment $assignment,
        public readonly AssignmentSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your assignment has been graded — {$this->assignment->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.assignment-graded',
        );
    }
}
