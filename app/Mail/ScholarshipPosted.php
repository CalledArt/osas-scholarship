<?php

namespace App\Mail;

use App\Models\Scholarship;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/**
 * Tells students a new scholarship is open for applications.
 *
 * One copy goes to every student at once through BccMailer, so nothing in it
 * is addressed to a particular student.
 */
class ScholarshipPosted extends Mailable
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
            subject: __('New scholarship: :title', ['title' => $this->scholarship->title]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.scholarships.posted',
            with: [
                'title' => $this->scholarship->title,
                'summary' => Str::of($this->scholarship->description)->squish()->limit(300)->value(),
                'slots' => $this->scholarship->slots,
                'deadline' => $this->scholarship->deadline?->format('F j, Y'),
                'scholarshipUrl' => route('scholarships.show', $this->scholarship),
            ],
        );
    }
}
