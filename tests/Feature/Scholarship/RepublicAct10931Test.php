<?php

namespace Tests\Feature\Scholarship;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepublicAct10931Test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_read_the_full_text_of_the_act(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.republic-act-10931'));

        $response->assertSee('Universal Access to Quality Tertiary Education Act');
        $response->assertSee('in any SUC and LUC shall be exempt from paying tuition and other school fees for units enrolled in');
        $response->assertSeeInOrder(['Section 1.', 'Section 11.', 'Section 21.']);
    }

    public function test_a_student_past_the_years_it_covers_is_told_their_free_tuition_has_ended(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->create(['student_number' => '202112345', 'program_years' => 4]);

        $response = $this->actingAs($student)->get(route('scholarships.republic-act-10931'));

        $response->assertSee('Your free tuition under this Act has ended. You are in year 6 of study, past the 5 years it covers for your 4-year program');
    }

    public function test_admin_is_forbidden_from_the_student_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('scholarships.republic-act-10931'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('scholarships.republic-act-10931'));

        $response->assertRedirect(route('login'));
    }
}
