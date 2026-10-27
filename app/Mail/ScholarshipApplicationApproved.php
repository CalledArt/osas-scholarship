<?php

namespace App\Mail;

use App\Models\Scholarship;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells applicants who made the pass list that their application was approved.
 *
 * Every approved applicant of one pass list gets their own message through
 * StudentMailer, but with the same content, so nothing in it names a
 * particular student.
 */
class ScholarshipApplicationApproved extends Mailable
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
            subject: __('Scholarship Application Approved: :title', ['title' => $this->scholarship->title]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.scholarship-applications.approved',
            with: [
                'title' => $this->scholarship->title,
                'applicationsUrl' => route('scholarship-applications.index'),
            ],
        );
    }
}
