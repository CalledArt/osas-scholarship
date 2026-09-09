<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
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
}
