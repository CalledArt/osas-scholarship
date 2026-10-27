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

    public function test_admin_is_welcomed_by_name_above_the_dashboard_status(): void
    {
        $admin = User::factory()->admin()->named('Maria', null, 'Santos')->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeInOrder(['Welcome back, SANTOS, MARIA!', 'No scholarships have been posted yet.', 'Post scholarship']);
    }

    public function test_stat_boxes_count_scholarships_by_whether_students_can_apply_and_students_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->create();
        Scholarship::factory()->closed()->create();
        Scholarship::factory()->create(['deadline' => now()->subDay()]);
        Scholarship::factory()->ended()->create();
        User::factory()->count(2)->create();
        User::factory()->onLeaveOfAbsence()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeTextInOrder([
            'Total scholarships', '4', '1 open', '2 closed', '1 ended',
            'Students with Active status', '2', 'of 3 registered students',
            'Students on leave of absence', '1', 'Free tuition and applying paused',
        ]);
    }

    public function test_student_counts_show_before_any_scholarship_is_posted(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create();
        User::factory()->onLeaveOfAbsence()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeTextInOrder(['Students with Active status', '1', 'of 2 registered students', 'Students on leave of absence', '1']);
    }

    public function test_admin_is_told_when_nothing_is_posted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('No scholarships have been posted yet.');
        $response->assertSee(route('admin.scholarships.create'));
        $response->assertSeeInOrder(['Recent scholarship postings', 'Scholarships you post will be listed here.']);
    }

    public function test_only_scholarships_awaiting_a_decision_are_queued(): void
    {
        $admin = User::factory()->admin()->create();

        $settled = Scholarship::factory()->create(['title' => 'Settled Grant']);
        ScholarshipApplication::factory()->approved()->for($settled)->create();

        $waiting = Scholarship::factory()->create(['title' => 'Waiting Grant']);
        ScholarshipApplication::factory()->for($waiting)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('takingApplications', fn (Collection $queue): bool => $queue->pluck('title')->all() === ['Waiting Grant']);
        $response->assertViewHas('readyForPassList', fn (Collection $queue): bool => $queue->isEmpty());
    }

    public function test_postings_table_lists_the_newest_first_with_whether_students_can_apply(): void
    {
        $admin = User::factory()->admin()->create();
        $this->travelTo('2026-10-10');
        Scholarship::factory()->closed()->create(['title' => 'Older Grant']);
        $this->travelTo('2026-10-20');
        $newest = Scholarship::factory()->create(['title' => 'Newest Grant', 'deadline' => '2026-11-30']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeTextInOrder([
            'Recent scholarship postings',
            'Newest Grant', 'Government', 'Nov 30, 2026', 'Open',
            'Older Grant', 'Closed',
        ]);
        $response->assertSee(route('admin.scholarships.show', $newest));
        $response->assertDontSee(route('admin.scholarships.edit', $newest));
    }

    public function test_postings_table_marks_a_posting_whose_grant_period_ended(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->ended()->create(['title' => 'Finished Grant']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSeeTextInOrder(['Recent scholarship postings', 'Finished Grant', 'Ended']);
    }

    public function test_a_third_posting_pushes_the_oldest_off_the_table_without_deleting_it(): void
    {
        $admin = User::factory()->admin()->create();
        $postings = collect(range(1, 3))->map(function (int $day): Scholarship {
            $this->travelTo("2026-10-0{$day} 09:00:00");

            return Scholarship::factory()->create(['title' => "Grant posted on day {$day}"]);
        });

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('postings', fn (Collection $table): bool => $table->pluck('title')->all() === [
            'Grant posted on day 3', 'Grant posted on day 2',
        ]);
        $response->assertDontSeeText('Grant posted on day 1');
        $this->assertModelExists($postings->first());
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
        $response->assertDontSee('Ready for a pass list');
        $response->assertDontSee('Still taking applications');
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

    public function test_only_pending_applications_count_as_waiting_on_a_pass_list(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->closed()->create(['slots' => 10]);
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        ScholarshipApplication::factory()->rejected()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHas('readyForPassList', function (Collection $readyForPassList): bool {
            return (int) $readyForPassList->first()->pending_count === 2;
        });
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
