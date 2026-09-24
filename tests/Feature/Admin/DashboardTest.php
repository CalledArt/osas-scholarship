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
        $response->assertViewHas('needsDecision', function (Collection $needsDecision): bool {
            return $needsDecision->pluck('title')->all() === ['Sooner Grant', 'Later Grant', 'Open Ended Grant'];
        });
    }

    public function test_each_scholarship_shows_its_pending_count_and_filled_slots(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 10]);
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        ScholarshipApplication::factory()->rejected()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('needsDecision', function (Collection $needsDecision): bool {
            return (int) $needsDecision->first()->pending_count === 2;
        });
        $response->assertSee('1 of 10 slots filled');
        $response->assertViewHas('pendingTotal', 2);
        $response->assertSee('applications need your decision.');
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
