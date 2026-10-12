<?php

namespace Tests\Feature\Support;

use App\Support\BccMailer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class BccMailerTest extends TestCase
{
    public function test_it_sends_every_address_in_bcc_batches_of_fifty_addressed_to_the_sender(): void
    {
        Mail::fake();
        config(['mail.from.address' => 'osas.scholarships@gmail.com']);
        $emails = $this->studentEmails(51);

        $reached = app(BccMailer::class)->send($emails, new Mailable);

        $this->assertSame(51, $reached);

        $batches = Mail::sent(Mailable::class);
        $this->assertSame([50, 1], $batches->map(fn (Mailable $mail) => count($mail->bcc))->all());
        $this->assertSame($emails, $batches->flatMap(fn (Mailable $mail) => array_column($mail->bcc, 'address'))->all());
        $this->assertTrue($batches->every(fn (Mailable $mail) => $mail->hasTo('osas.scholarships@gmail.com')));
    }

    public function test_it_stops_at_the_first_failed_batch_and_counts_only_the_students_reached(): void
    {
        $delivered = Mockery::mock(PendingMail::class);
        $delivered->shouldReceive('bcc->send')->once();
        Mail::shouldReceive('to')->twice()->andReturnUsing(
            fn () => $delivered,
            fn () => throw new TransportException('Daily user sending limit exceeded.'),
        );
        Log::shouldReceive('warning')->once()->with('Could not email a batch of students.', Mockery::subset([
            'reached' => 50,
            'not_reached' => 51,
        ]));

        $reached = app(BccMailer::class)->send($this->studentEmails(101), new Mailable);

        $this->assertSame(50, $reached);
    }

    /**
     * @return array<int, string>
     */
    private function studentEmails(int $count): array
    {
        return array_map(fn (int $number) => "student{$number}@cvsu.edu.ph", range(1, $count));
    }
}
