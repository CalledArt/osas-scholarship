<?php

namespace Tests\Feature\Mail;

use App\Mail\ScholarshipPosted;
use App\Models\Scholarship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipPostedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_names_the_scholarship_its_terms_and_where_to_apply(): void
    {
        $scholarship = Scholarship::factory()->create([
            'title' => 'STEM Excellence Grant',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
            'deadline' => '2026-11-15',
        ]);

        $mailable = new ScholarshipPosted($scholarship);

        $mailable->assertHasSubject('New scholarship: STEM Excellence Grant');
        $mailable->assertSeeInHtml('For outstanding STEM students.');
        $mailable->assertSeeInHtml('Slots:</strong> 5', false);
        $mailable->assertSeeInHtml('November 15, 2026');
        $mailable->assertSeeInHtml(route('scholarships.show', $scholarship));
        $mailable->assertSeeInText(route('scholarships.show', $scholarship));
    }

    public function test_it_leaves_out_the_deadline_when_the_scholarship_has_none(): void
    {
        $scholarship = Scholarship::factory()->create(['deadline' => null]);

        $mailable = new ScholarshipPosted($scholarship);

        $mailable->assertDontSeeInHtml('Deadline');
    }
}
