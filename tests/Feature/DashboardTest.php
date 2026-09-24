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

        $response->assertSee('Welcome, Maria Santos Reyes!');
    }

    public function test_sidebar_shows_the_first_middle_last_name(): void
    {
        $student = User::factory()->named('Juan', null, 'Dela Cruz')->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('Juan Dela Cruz');
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

        $response->assertSee('Welcome, Unbackfilled Student!');
    }

    public function test_student_sees_only_their_own_applications_with_status(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'My Grant']))
            ->create();
        ScholarshipApplication::factory()
            ->for(Scholarship::factory()->create(['title' => 'Someone Else Grant']))
            ->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Pending');
        $response->assertViewHas('applications', function ($applications): bool {
            return $applications->pluck('scholarship_title')->all() === ['My Grant'];
        });
        $response->assertViewHas('pendingCount', 1);
    }

    public function test_open_for_you_lists_only_scholarships_the_student_can_still_apply_to(): void
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
        $response->assertViewHas('openScholarships', function ($openScholarships): bool {
            return $openScholarships->pluck('title')->all() === ['Eligible Grant'];
        });
        $response->assertSee('5 slots left');
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
