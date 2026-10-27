<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_greets_them_by_first_middle_last_name(): void
    {
        $student = User::factory()->named('Maria', 'Santos', 'Reyes')->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('Welcome back, REYES, MARIA SANTOS!');
    }

    public function test_welcome_banner_states_the_applications_waiting_for_a_decision_then_the_scholarships_open_to_the_student(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->for($student)->create();
        ScholarshipApplication::factory()->rejected()->for($student)->create();
        Scholarship::factory()->count(2)->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertViewHas('waitingApplicationCount', 1);
        $response->assertSeeInOrder(['Welcome back,', 'application is waiting for a decision.', 'scholarships are open to you.', 'Where you stand']);
    }

    public function test_welcome_banner_says_the_student_is_all_caught_up_when_nothing_waits_on_them(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)->create();
        Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSeeInOrder(['Welcome back,', "You're all caught up.", 'Where you stand']);
        $response->assertDontSee('is open to you.');
    }

    public function test_sidebar_shows_the_first_middle_last_name(): void
    {
        $student = User::factory()->named('Juan', null, 'Dela Cruz')->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('Dela Cruz, Juan');
    }

    public function test_dashboard_falls_back_to_the_stored_name_when_no_parts_are_set(): void
    {
        $student = User::factory()->create();

        DB::table('users')->where('id', $student->id)->update([
            'name' => 'Unbackfilled Student',
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
        ]);

        $response = $this->actingAs($student->fresh())->get(route('dashboard'));

        $response->assertSee('Welcome back, UNBACKFILLED STUDENT!');
    }

    public function test_student_sees_only_their_own_applications_with_status(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'My Grant']))
            ->create(['submitted_at' => now()->subDays(3)]);
        ScholarshipApplication::factory()
            ->for(Scholarship::factory()->create(['title' => 'Someone Else Grant']))
            ->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('applications', function ($applications): bool {
            return $applications->pluck('scholarship_title')->all() === ['My Grant'];
        });
        $response->assertSeeInOrder(['My applications', 'My Grant', '3 days waiting', 'Pending']);
    }

    public function test_active_scholarships_are_the_students_approvals_on_scholarships_that_have_not_ended(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->closed()->create(['title' => 'Ongoing Grant']))
            ->create();
        ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->ended()->create(['title' => 'Ended Grant']))
            ->create();
        ScholarshipApplication::factory()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Pending Grant']))
            ->create();
        ScholarshipApplication::factory()->approved()
            ->for(Scholarship::factory()->create(['title' => 'Someone Else Grant']))
            ->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertViewHas('activeApplications', function ($activeApplications): bool {
            return $activeApplications->pluck('scholarship_title')->all() === ['Ongoing Grant'];
        });
        $response->assertViewHas('applications', function ($applications): bool {
            return $applications->pluck('scholarship_title')->sort()->values()->all() === ['Ended Grant', 'Pending Grant'];
        });
        $response->assertSeeInOrder(['Where you stand', 'Ongoing Grant', 'Active', 'Available to apply']);
    }

    public function test_every_student_holds_republic_act_10931_without_it_counting_toward_the_limit(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->create(['student_number' => '202212345', 'program_years' => 4]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSeeInOrder(['Where you stand', 'Republic Act No. 10931', 'Free tuition', 'Year 5 of 5', 'In force', 'Available to apply']);
        $response->assertSee(route('scholarships.republic-act-10931'));
        $response->assertViewHas('remainingPlaces', 2);
    }

    public function test_republic_act_10931_leaves_active_scholarships_once_the_student_passes_the_years_it_covers(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->create(['student_number' => '202112345', 'program_years' => 4]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertDontSee('Republic Act No. 10931');
        $response->assertSeeTextInOrder(['Active scholarship', 'None yet.']);
    }

    public function test_available_to_apply_lists_only_scholarships_the_student_can_still_apply_to(): void
    {
        $student = User::factory()->create();

        Scholarship::factory()->create(['title' => 'Eligible Grant', 'slots' => 5]);
        Scholarship::factory()->closed()->create(['title' => 'Closed Grant']);
        Scholarship::factory()->create(['title' => 'Expired Grant', 'deadline' => now()->subDay()]);

        $full = Scholarship::factory()->create(['title' => 'Full Grant', 'slots' => 1]);
        ScholarshipApplication::factory()->approved()->for($full)->create();

        $applied = Scholarship::factory()->create(['title' => 'Applied Grant']);
        ScholarshipApplication::factory()->for($student)->for($applied)->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('availableScholarships', function ($availableScholarships): bool {
            return $availableScholarships->pluck('title')->all() === ['Eligible Grant'];
        });
        $response->assertSeeText('Eligible Grant');
        $response->assertDontSeeText('Slots left');
        $response->assertDontSeeText('You can apply to 1 more scholarship.');
        $response->assertViewHas('remainingPlaces', 1);
        $response->assertViewHas('hasOpenScholarships', true);
    }

    public function test_student_at_the_scholarship_limit_is_offered_nothing_to_apply_to(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->count(2)->for($student)->create();
        Scholarship::factory()->create(['title' => 'Eligible Grant']);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSeeInOrder(['Available to apply', 'You already have 2 applications waiting for a decision. You can apply again once one of them is rejected.']);
        $response->assertDontSee('Eligible Grant');
        $response->assertViewHas('hasOpenScholarships', false);
    }

    public function test_student_holding_an_active_scholarship_is_offered_nothing_to_apply_to(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)->create();
        Scholarship::factory()->create(['title' => 'Eligible Grant']);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('You hold an active scholarship. Apply again once it ends.');
        $response->assertDontSee('Eligible Grant');
        $response->assertViewHas('hasOpenScholarships', false);
    }

    public function test_student_on_leave_of_absence_has_ra_10931_paused_and_is_offered_nothing_to_apply_to(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->onLeaveOfAbsence()->create(['student_number' => '202312345', 'program_years' => 4]);
        Scholarship::factory()->create(['title' => 'Eligible Grant']);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSeeInOrder([
            'Where you stand',
            'Republic Act No. 10931',
            'Free tuition paused during leave of absence (LOA).',
            'Available to apply',
            'On leave of absence (LOA): applying is paused.',
        ]);
        $response->assertDontSee('Year 4 of 5');
        $response->assertDontSee('Eligible Grant');
        $response->assertViewHas('hasOpenScholarships', false);
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_available_to_apply_leaves_out_postings_of_a_preset_the_student_holds(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->for($student)->for(Scholarship::factory()->fromPreset('tdp')->closed())->create();
        Scholarship::factory()->fromPreset('tdp')->create(['title' => 'TDP Second Call']);
        Scholarship::factory()->create(['title' => 'Merit Grant']);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('Merit Grant');
        $response->assertDontSee('TDP Second Call');
    }
}
