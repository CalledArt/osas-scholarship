<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('scholarships.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_is_forbidden_from_the_student_scholarships_list(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('scholarships.index'));

        $response->assertForbidden();
    }

    public function test_student_can_view_the_scholarships_list(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Scholarship']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertOk();
        $response->assertSee('Merit Scholarship');
    }

    public function test_student_can_view_a_scholarships_details(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertOk();
        $response->assertSee($scholarship->title);
        $response->assertSee(route('scholarships.apply', $scholarship));
    }

    public function test_scholarships_page_lists_active_scholarships_above_the_ones_available_to_apply(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Held Grant']))
            ->create();
        Scholarship::factory()->create(['title' => 'Open Grant']);
        Scholarship::factory()->closed()->create(['title' => 'Closed Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSeeInOrder(['Active scholarships', 'Republic Act No. 10931', 'Held Grant', 'Available to apply', 'Open Grant']);
        $response->assertDontSee('Closed Grant');
    }

    public function test_scholarship_details_explain_the_limit_instead_of_offering_to_apply_when_the_student_is_at_it(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->count(2)->for($student)->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('You already hold 2 scholarships');
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }
}
