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

    public function test_admin_sees_an_empty_register_when_nothing_is_posted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('No scholarships have been posted yet.');
        $response->assertSee('No applications have been submitted yet.');
    }

    public function test_scholarships_awaiting_a_decision_come_before_settled_ones(): void
    {
        $admin = User::factory()->admin()->create();

        // Closes sooner, but every application is already decided.
        $settled = Scholarship::factory()->create(['title' => 'Settled Grant', 'deadline' => now()->addDay()]);
        ScholarshipApplication::factory()->approved()->for($settled)->create();

        $waiting = Scholarship::factory()->create(['title' => 'Waiting Grant', 'deadline' => now()->addMonth()]);
        ScholarshipApplication::factory()->for($waiting)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('needsDecisionCount', 1);
        $response->assertViewHas('register', function (Collection $register) use ($waiting, $settled): bool {
            return $register->first()->is($waiting) && $register->last()->is($settled);
        });
    }

    public function test_scholarships_awaiting_a_decision_are_ordered_by_soonest_deadline(): void
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

        $response->assertOk();
        $response->assertViewHas('register', function (Collection $register): bool {
            return $register->pluck('title')->all() === ['Sooner Grant', 'Later Grant', 'Open Ended Grant'];
        });
    }

    public function test_register_counts_pending_and_awarded_applications_per_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 10]);
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        ScholarshipApplication::factory()->rejected()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('register', function (Collection $register): bool {
            $row = $register->first();

            return (int) $row->pending_count === 2 && (int) $row->approved_applications_count === 1;
        });
        $response->assertViewHas('pendingTotal', 2);
    }

    public function test_register_reports_the_wait_on_the_oldest_undecided_application(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)->create(['submitted_at' => now()->subDays(9)]);
        ScholarshipApplication::factory()->for($scholarship)->create(['submitted_at' => now()->subDay()]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('9 days');
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
        $response->assertSee('in 2 days');
        $response->assertSee('20 days ago');
    }

    public function test_standing_line_summarises_what_is_waiting(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Tulong Dunong Grant', 'deadline' => now()->addDays(3)]);
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('applications are waiting on you across');
        $response->assertSee('Tulong Dunong Grant');
        $response->assertViewHas('nextToClose', fn (?Scholarship $next): bool => $next?->is($scholarship) ?? false);
    }

    public function test_dashboard_lists_recent_submissions(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Juan Dela Cruz');
        $response->assertSee($scholarship->title);
    }
}
