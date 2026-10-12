<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one message to many students without showing them each other's addresses.
 *
 * Mail goes out inside the request (see .ai/rules/mail.md), so one SMTP
 * transaction per student would hold the admin's page for as long as it takes
 * to reach everyone. Students go in Bcc batches instead, and each message is
 * addressed to the sender's own mailbox, so it never arrives with an empty
 * "To" header for spam filters to score. Gmail still counts every Bcc address
 * against the account's daily sending limit.
 */
class BccMailer
{
    private const BATCH_SIZE = 50;

    /**
     * Send the mailable to every address and return how many were reached.
     *
     * Sending stops at the first batch that fails. A wrong App Password, an
     * unreachable server or a spent daily quota fail every batch after it
     * too, and each attempt can take the full SMTP timeout.
     *
     * @param  array<int, string>  $emails
     */
    public function send(array $emails, Mailable $mailable): int
    {
        $reached = 0;

        foreach (array_chunk($emails, self::BATCH_SIZE) as $batch) {
            try {
                // A fresh copy per batch: sending adds the recipients to the mailable itself.
                Mail::to(config('mail.from.address'))->bcc($batch)->send(clone $mailable);
            } catch (Throwable $exception) {
                Log::warning('Could not email a batch of students.', [
                    'mailable' => $mailable::class,
                    'reached' => $reached,
                    'not_reached' => count($emails) - $reached,
                    'exception' => $exception,
                ]);

                break;
            }

            $reached += count($batch);
        }

        return $reached;
    }
}
