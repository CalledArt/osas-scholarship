<?php

namespace Tests\Feature\Support;

use App\Support\StudentMailer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class StudentMailerTest extends TestCase
{
    public function test_it_sends_every_student_their_own_message_addressed_to_them_alone(): void
    {
        Mail::fake();

        $reached = app(StudentMailer::class)->send($this->studentEmails(3), new Mailable);

        $this->assertSame(3, $reached);

        $messages = Mail::sent(Mailable::class);
        $this->assertSame(
            [['student1@cvsu.edu.ph'], ['student2@cvsu.edu.ph'], ['student3@cvsu.edu.ph']],
            $messages->map(fn (Mailable $mail) => array_column($mail->to, 'address'))->all(),
        );
        $this->assertSame([], $messages->flatMap(fn (Mailable $mail) => [...$mail->cc, ...$mail->bcc])->all());
    }

    public function test_it_stops_at_the_first_failed_message_and_counts_only_the_students_reached(): void
    {
        $delivered = Mockery::mock(PendingMail::class);
        $delivered->shouldReceive('send')->twice();
        $refused = Mockery::mock(PendingMail::class);
        $refused->shouldReceive('send')->once()->andThrow(new TransportException('Daily user sending limit exceeded.'));
        Mail::shouldReceive('to')->times(3)->andReturn($delivered, $delivered, $refused);
        Log::shouldReceive('warning')->once()->with('Could not email a student.', Mockery::subset([
            'reached' => 2,
            'not_reached' => 3,
        ]));

        $reached = app(StudentMailer::class)->send($this->studentEmails(5), new Mailable);

        $this->assertSame(2, $reached);
    }

    /**
     * @return array<int, string>
     */
    private function studentEmails(int $count): array
    {
        return array_map(fn (int $number) => "student{$number}@cvsu.edu.ph", range(1, $count));
    }
}
