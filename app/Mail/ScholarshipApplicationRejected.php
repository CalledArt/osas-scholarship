<?php

namespace App\Mail;

use App\Models\Scholarship;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells applicants left off the pass list that their application was not approved.
 *
 * Every rejected applicant of one pass list gets the same copy through
 * BccMailer, so nothing in it is addressed to a particular student.
 */
class ScholarshipApplicationRejected extends Mailable
{
    public function __construct(
        public Scholarship $scholarship,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your application for :title was not approved', ['title' => $this->scholarship->title]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.scholarship-applications.rejected',
            with: [
                'title' => $this->scholarship->title,
                'scholarshipsUrl' => route('scholarships.index'),
            ],
        );
    }
}
