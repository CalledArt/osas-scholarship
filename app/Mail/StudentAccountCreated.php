<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Hands a newly imported student the credentials their account was created with.
 *
 * Deliberately not a ShouldQueue mailable. Nothing runs a queue worker in this
 * deployment, so a queued message would sit in the jobs table unsent, and the
 * one-time password would sit there in plaintext with it. Sending inline also
 * lets the importer report per row whether the student was actually reached.
 */
class StudentAccountCreated extends Mailable
{
    public function __construct(
        public User $user,
        public string $password,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your :app account is ready', ['app' => config('app.name')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.students.account-created',
            with: [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->password,
                'loginUrl' => route('login'),
            ],
        );
    }
}
