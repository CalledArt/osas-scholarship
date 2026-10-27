<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyApplicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_counts_every_application_of_the_students_still_waiting_beyond_the_first_page(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->count(11)->for($student)->create();
        ScholarshipApplication::factory()->rejected()->for($student)->create();
        ScholarshipApplication::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarship-applications.index'));

        $response->assertViewHas('waitingApplicationCount', 11);
        $response->assertSeeInOrder(['My Applications', 'applications are waiting for a decision.']);
    }

    public function test_banner_says_the_student_is_all_caught_up_once_every_application_is_decided(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)->create();
        ScholarshipApplication::factory()->rejected()->for($student)->create();

        $response = $this->actingAs($student)->get(route('scholarship-applications.index'));

        $response->assertSeeInOrder(['My Applications', "You're all caught up."]);
        $response->assertDontSee('waiting for a decision');
    }

    public function test_banner_says_where_applications_appear_before_the_student_applies(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarship-applications.index'));

        $response->assertSeeInOrder(['Your applications appear here once you apply.', "You haven't applied to any scholarships yet."]);
    }

    public function test_each_card_gives_the_reference_and_date_sent_and_only_a_pending_one_states_its_wait(): void
    {
        $this->travelTo('2026-10-25 10:00');
        $student = User::factory()->create();
        $pending = ScholarshipApplication::factory()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Merit Grant']))
            ->create(['submitted_at' => '2026-10-22 15:00']);
        $approved = ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Held Grant']))
            ->create(['submitted_at' => '2026-10-01 09:00']);

        $response = $this->actingAs($student)->get(route('scholarship-applications.index'));

        $response->assertSeeInOrder([
            'Merit Grant', 'Pending', $pending->reference, 'Oct 22, 2026', '3 days waiting',
            'Held Grant', 'Approved', $approved->reference, 'Oct 1, 2026',
        ]);
        $response->assertDontSee('24 days waiting');
    }
}
