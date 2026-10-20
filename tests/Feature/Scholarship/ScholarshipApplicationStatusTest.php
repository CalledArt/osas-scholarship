<?php

namespace Tests\Feature\Scholarship;

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

    public function test_approved_application_tells_the_student_it_counts_toward_their_limit(): void
    {
        $application = ScholarshipApplication::factory()->approved()->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You were approved');
        $response->assertSee('This scholarship counts as one of the 2 you can hold until OSAS ends it.');
    }

    public function test_approved_application_on_an_ended_scholarship_no_longer_counts_toward_the_limit(): void
    {
        $application = ScholarshipApplication::factory()->approved()
            ->for(Scholarship::factory()->ended())
            ->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You were approved');
        $response->assertSee('The grant period has since ended, so this scholarship no longer counts toward the 2 you can hold.');
    }

    public function test_rejected_application_tells_the_student_it_no_longer_counts_toward_their_limit(): void
    {
        $application = ScholarshipApplication::factory()->rejected()->create();

        $response = $this->actingAs($application->user)->get(route('scholarship-applications.show', $application));

        $response->assertSee('You were not approved');
        $response->assertSee('This application no longer counts toward the 2 scholarships you can hold.');
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
}
