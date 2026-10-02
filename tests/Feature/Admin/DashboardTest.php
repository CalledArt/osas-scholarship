<?php

namespace Tests\Feature\Admin;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_is_forbidden_from_the_admin_dashboard(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_is_told_when_nothing_is_posted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('No scholarships have been posted yet.');
        $response->assertSee(route('admin.scholarships.create'));
    }

    public function test_only_scholarships_awaiting_a_decision_are_listed(): void
    {
        $admin = User::factory()->admin()->create();

        $settled = Scholarship::factory()->create(['title' => 'Settled Grant']);
        ScholarshipApplication::factory()->approved()->for($settled)->create();

        $waiting = Scholarship::factory()->create(['title' => 'Waiting Grant']);
        ScholarshipApplication::factory()->for($waiting)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Waiting Grant');
        $response->assertDontSee('Settled Grant');
        $response->assertSee('View all 2 scholarships');
    }

    public function test_scholarships_still_taking_applications_are_ordered_by_soonest_deadline(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            'Later Grant' => now()->addMonth(),
            'Sooner Grant' => now()->addDays(2),
            'Open Ended Grant' => null,
        ] as $title => $deadline) {
            $scholarship = Scholarship::factory()->create(['title' => $title, 'deadline' => $deadline]);
            ScholarshipApplication::factory()->for($scholarship)->create();
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('takingApplications', function (Collection $takingApplications): bool {
            return $takingApplications->pluck('title')->all() === ['Sooner Grant', 'Later Grant', 'Open Ended Grant'];
        });
        $response->assertViewHas('readyForPassList', fn (Collection $readyForPassList): bool => $readyForPassList->isEmpty());
    }

    public function test_scholarships_students_can_no_longer_apply_to_are_ready_for_a_pass_list(): void
    {
        $admin = User::factory()->admin()->create();

        $closedEarly = Scholarship::factory()->closed()->create(['title' => 'Closed Early Grant', 'deadline' => now()->addMonth()]);
        $pastDeadline = Scholarship::factory()->create(['title' => 'Past Deadline Grant', 'deadline' => now()->subDays(3)]);
        $full = Scholarship::factory()->create(['title' => 'Full Grant', 'slots' => 1, 'deadline' => now()->addMonth()]);
        ScholarshipApplication::factory()->approved()->for($full)->create();
        $open = Scholarship::factory()->create(['title' => 'Open Grant', 'slots' => 5, 'deadline' => now()->addMonth()]);

        foreach ([$closedEarly, $pastDeadline, $full, $open] as $scholarship) {
            ScholarshipApplication::factory()->for($scholarship)->create();
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('readyForPassList', function (Collection $readyForPassList): bool {
            return $readyForPassList->pluck('title')->sort()->values()->all() === ['Closed Early Grant', 'Full Grant', 'Past Deadline Grant'];
        });
        $response->assertViewHas('takingApplications', function (Collection $takingApplications): bool {
            return $takingApplications->pluck('title')->all() === ['Open Grant'];
        });
        $response->assertSeeInOrder(['Ready for a pass list', 'Still taking applications', 'Open Grant']);
    }

    public function test_scholarships_ready_for_a_pass_list_are_ordered_by_longest_wait(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            'Recent Grant' => [now()->subDays(1), now()->subDays(2)],
            'Open Ended Grant' => [null, now()->subDays(20)],
            'Middle Grant' => [now()->subDays(8), now()->subDays(9)],
        ] as $title => [$deadline, $submittedAt]) {
            $scholarship = Scholarship::factory()->closed()->create(['title' => $title, 'deadline' => $deadline]);
            ScholarshipApplication::factory()->for($scholarship)->create(['submitted_at' => $submittedAt]);
            ScholarshipApplication::factory()->for($scholarship)->create(['submitted_at' => now()]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('readyForPassList', function (Collection $readyForPassList): bool {
            return $readyForPassList->pluck('title')->all() === ['Open Ended Grant', 'Middle Grant', 'Recent Grant'];
        });
    }

    public function test_a_full_scholarship_reads_as_full_rather_than_closing_later(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Municipal Grant', 'slots' => 1, 'deadline' => now()->addDays(21)]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeInOrder(['Municipal Grant', 'Full', '1 of 1 slots filled']);
        $response->assertDontSee('Closes in 21 days');
    }

    public function test_a_scholarship_osas_closed_early_reads_as_closed_rather_than_closing_later(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->closed()->create(['title' => 'Closed Early Grant', 'deadline' => now()->addDays(21)]);
        ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeInOrder(['Closed Early Grant', 'Closed by OSAS']);
        $response->assertDontSee('Closes in 21 days');
    }

    public function test_a_deadline_of_today_reads_as_closed_because_applications_already_stopped(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['deadline' => today()]);
        ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSee('Closed today');
        $response->assertDontSee('Closes today');
    }

    public function test_summary_counts_the_pass_lists_due_and_the_scholarships_still_open(): void
    {
        $admin = User::factory()->admin()->create();
        ScholarshipApplication::factory()->for(Scholarship::factory()->closed())->create();
        ScholarshipApplication::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeInOrder(['scholarship is ready for a pass list.', 'more are still taking applications.']);
    }

    public function test_summary_says_no_pass_lists_are_due_while_every_scholarship_is_still_open(): void
    {
        $admin = User::factory()->admin()->create();
        ScholarshipApplication::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSee('No pass lists are due yet.');
        $response->assertSee('scholarship with applicants is still taking applications.');
    }

    public function test_each_scholarship_shows_its_pending_count_and_filled_slots(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 10]);
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        ScholarshipApplication::factory()->rejected()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('takingApplications', function (Collection $takingApplications): bool {
            return (int) $takingApplications->first()->pending_count === 2;
        });
        $response->assertSee('1 of 10 slots filled');
    }

    public function test_deadlines_are_counted_in_whole_calendar_days(): void
    {
        $admin = User::factory()->admin()->create();

        $closing = Scholarship::factory()->create(['deadline' => now()->addDays(2)]);
        ScholarshipApplication::factory()->for($closing)->create();

        $overdue = Scholarship::factory()->create(['deadline' => now()->subDays(20)]);
        ScholarshipApplication::factory()->for($overdue)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        // Counted between day boundaries, so the time of day never rounds this down.
        $response->assertSee('Closes in 2 days');
        $response->assertSee('Closed 20 days ago');
    }

    public function test_admin_is_told_when_nothing_needs_a_decision(): void
    {
        $admin = User::factory()->admin()->create();
        ScholarshipApplication::factory()->approved()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('No applications need a decision.');
    }
}
