<?php

namespace Tests\Feature\Mail;

use App\Mail\ScholarshipApplicationApproved;
use App\Models\Scholarship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipApplicationApprovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_tells_the_applicant_they_passed_and_links_to_their_applications(): void
    {
        $scholarship = Scholarship::factory()->create(['title' => 'STEM Excellence Grant']);

        $mailable = new ScholarshipApplicationApproved($scholarship);

        $mailable->assertHasSubject('Scholarship Application Approved: STEM Excellence Grant');
        $mailable->assertSeeInOrderInText(['Notice of Approval', 'your application for the', 'STEM Excellence Grant', 'has been approved']);
        $mailable->assertSeeInHtml(route('scholarship-applications.index'));
        $mailable->assertSeeInText(route('scholarship-applications.index'));
    }
}
