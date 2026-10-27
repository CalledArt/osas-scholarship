<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one message to many students, each copy addressed to one student alone.
 *
 * Every student finds their own address in "To", and nobody sees anyone
 * else's. Mail goes out inside the request (see .ai/rules/mail.md), so the
 * admin's page waits for every message; the SMTP connection is kept open
 * between them. Gmail counts every message against the account's daily
 * sending limit.
 */
class StudentMailer
{
    /**
     * Send the mailable to every address and return how many were reached.
     *
     * Sending stops at the first message that fails. A wrong App Password, an
     * unreachable server or a spent daily quota fail every message after it
     * too, and each attempt can take the full SMTP timeout.
     *
     * PHP's time limit (30 seconds in production) restarts before each
     * message, so a long list is held only to the platform's request timeout.
     *
     * @param  array<int, string>  $emails
     */
    public function send(array $emails, Mailable $mailable): int
    {
        $secondsPerMessage = (int) ini_get('max_execution_time');
        $reached = 0;

        foreach ($emails as $email) {
            if ($secondsPerMessage > 0) {
                set_time_limit($secondsPerMessage);
            }

            try {
                // A fresh copy per student: sending adds the recipient to the mailable itself.
                Mail::to($email)->send(clone $mailable);
            } catch (Throwable $exception) {
                Log::warning('Could not email a student.', [
                    'mailable' => $mailable::class,
                    'reached' => $reached,
                    'not_reached' => count($emails) - $reached,
                    'exception' => $exception,
                ]);

                break;
            }

            $reached++;
        }

        return $reached;
    }
}
