<?php

namespace Tests\Feature;

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

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
