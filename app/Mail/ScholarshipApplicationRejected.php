<?php

namespace App\Mail;

use App\Models\Scholarship;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells applicants left off the pass list that their application was not approved.
 *
 * Every rejected applicant of one pass list gets their own message through
 * StudentMailer, but with the same content, so nothing in it names a
 * particular student. The
 * applicants who were on the list but already hold an active scholarship get
 * their own copy, with `passedWhileHoldingActive` set, saying so.
 */
class ScholarshipApplicationRejected extends Mailable
{
    public function __construct(
        public Scholarship $scholarship,
        public bool $passedWhileHoldingActive = false,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Scholarship Application Result: :title', ['title' => $this->scholarship->title]),
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
                'passedWhileHoldingActive' => $this->passedWhileHoldingActive,
                'scholarshipsUrl' => route('scholarships.index'),
            ],
        );
    }
}
