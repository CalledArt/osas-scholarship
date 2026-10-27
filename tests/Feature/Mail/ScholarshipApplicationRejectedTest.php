<?php

namespace Tests\Feature\Mail;

use App\Mail\ScholarshipApplicationRejected;
use App\Models\Scholarship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipApplicationRejectedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_tells_the_applicant_they_were_not_approved_and_points_to_other_scholarships(): void
    {
        $scholarship = Scholarship::factory()->create(['title' => 'STEM Excellence Grant']);

        $mailable = new ScholarshipApplicationRejected($scholarship);

        $mailable->assertHasSubject('Scholarship Application Result: STEM Excellence Grant');
        $mailable->assertSeeInOrderInText(['STEM Excellence Grant', 'your application has not been approved']);
        $mailable->assertSeeInHtml(route('scholarships.index'));
        $mailable->assertSeeInText(route('scholarships.index'));
    }

    public function test_it_tells_a_student_who_passed_while_holding_an_active_scholarship_why_they_were_not_approved(): void
    {
        $scholarship = Scholarship::factory()->create(['title' => 'STEM Excellence Grant']);

        $mailable = new ScholarshipApplicationRejected($scholarship, passedWhileHoldingActive: true);

        $mailable->assertSeeInOrderInText(['Your name was included in the final list', 'a student may hold only one active scholarship at a time']);
        $mailable->assertDontSeeInText('your name was not included');
    }
}
