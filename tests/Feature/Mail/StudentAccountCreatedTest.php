<?php

namespace Tests\Feature\Mail;

use App\Mail\StudentAccountCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccountCreatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_gives_the_student_the_credentials_and_a_way_in(): void
    {
        $student = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@cvsu.edu.ph',
        ]);

        $mailable = new StudentAccountCreated($student, 'Temporary-Password-1');

        $mailable->assertHasSubject('Your '.config('app.name').' account is ready');
        $mailable->assertSeeInHtml('Juan Dela Cruz');
        $mailable->assertSeeInHtml('juan@cvsu.edu.ph');
        $mailable->assertSeeInHtml('Temporary-Password-1');
        $mailable->assertSeeInHtml(route('login'));
    }

    /**
     * The password is the whole point of the message, so it has to survive
     * into the plain-text part that clients without HTML fall back to.
     */
    public function test_the_plain_text_part_carries_the_password_too(): void
    {
        $student = User::factory()->create(['email' => 'juan@cvsu.edu.ph']);

        $mailable = new StudentAccountCreated($student, 'Temporary-Password-1');

        $mailable->assertSeeInText('Temporary-Password-1');
        $mailable->assertSeeInText('juan@cvsu.edu.ph');
    }

    public function test_it_tells_the_student_the_password_must_be_replaced(): void
    {
        $student = User::factory()->create();

        $mailable = new StudentAccountCreated($student, 'Temporary-Password-1');

        $mailable->assertSeeInHtml('This password is temporary.');
    }
}
