<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScholarshipApplicationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_application_tells_the_student_it_is_their_one_active_scholarship(): void
    {
        $application = ScholarshipApplication::factory()->approved()->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You were approved');
        $response->assertSee('This is your active scholarship: a student can hold only one at a time, so you cannot apply to others until OSAS ends its grant period.');
    }

    public function test_approved_application_on_an_ended_scholarship_no_longer_counts_toward_the_limit(): void
    {
        $application = ScholarshipApplication::factory()->approved()
            ->for(Scholarship::factory()->ended())
            ->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You were approved');
        $response->assertSee('The grant period has since ended, so it is no longer your active scholarship and you can apply to others again.');
    }

    public function test_rejected_application_tells_the_student_it_no_longer_counts_toward_their_limit(): void
    {
        $application = ScholarshipApplication::factory()->rejected()->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You were not approved');
        $response->assertSee('Your name was not on the pass list. This application no longer counts toward the 2 applications you can have.');
        $response->assertDontSee('Waiting for a decision');
    }

    public function test_rejection_for_already_holding_an_active_scholarship_tells_the_student_they_passed(): void
    {
        $application = ScholarshipApplication::factory()->rejected()->create(['passed_while_holding_active' => true]);

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('Your name was on the pass list, but you already held an active scholarship, and a student can hold only one at a time.');
        $response->assertDontSee('Your name was not on the pass list.');
    }

    public function test_voided_application_tells_the_student_it_gave_up_its_slot_for_their_leave(): void
    {
        $application = ScholarshipApplication::factory()->create(['status' => ApplicationStatus::Voided]);

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('Your application left review when you went on leave of absence (LOA).');
        $response->assertSee('OSAS put you on leave of absence (LOA) before this application was decided, so it gave up its slot for another student.');
        $response->assertSee('You can still apply to other scholarships.');
        $response->assertDontSee('Waiting for a decision');
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function availabilityWhilePending(): array
    {
        return [
            'open with a deadline' => [['deadline' => '2026-11-08'], 'Applications close on November 8, 2026.'],
            'open without a deadline' => [['deadline' => null], 'Applications are still open.'],
            'past its deadline' => [['deadline' => '2026-10-01'], 'Applications closed on October 1, 2026.'],
            'closed by OSAS' => [['status' => ScholarshipStatus::Closed, 'deadline' => '2026-11-08'], 'OSAS has closed applications.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $scholarshipAttributes
     */
    #[DataProvider('availabilityWhilePending')]
    public function test_pending_application_says_whether_applications_are_still_open(array $scholarshipAttributes, string $availability): void
    {
        $this->travelTo('2026-10-16');
        $application = ScholarshipApplication::factory()
            ->for(Scholarship::factory()->state($scholarshipAttributes))
            ->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('Waiting for a decision');
        $response->assertSee($availability);
    }

    public function test_pending_application_on_a_full_scholarship_says_every_slot_is_filled(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 1, 'deadline' => now()->addWeek()]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('Every slot has been filled.');
        $response->assertDontSee('Applications close on');
    }

    public function test_application_page_confirms_a_submission_just_made(): void
    {
        $application = ScholarshipApplication::factory()->create();

        $response = $this->actingAs($application->user)
            ->withSession(['status' => 'Application submitted.'])
            ->get(route('scholarship-applications.show', $application));

        $response->assertSee('Application submitted.');
    }

    public function test_another_student_is_forbidden_from_viewing_the_application(): void
    {
        $application = ScholarshipApplication::factory()->create();

        $response = $this->actingAs(User::factory()->create())->get(route('scholarship-applications.show', $application));

        $response->assertForbidden();
    }

    public function test_an_application_to_a_scholarship_needing_no_documents_says_so_instead_of_counting_none(): void
    {
        $application = ScholarshipApplication::factory()->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You applied. No documents were required.');
        $response->assertDontSee('You sent 0 documents.');
    }
}
