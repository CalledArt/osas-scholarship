<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_staff_can_authenticate_with_their_password(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_a_student_cannot_sign_in_with_a_password_even_a_correct_one(): void
    {
        $student = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Students sign in with their CvSU Google account using the Sign in with Google button.']);
    }

    public function test_a_wrong_password_for_a_student_gets_the_generic_failure(): void
    {
        $student = User::factory()->create();

        $this->post('/login', ['email' => $student->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_an_already_authenticated_admin_visiting_login_is_sent_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/login');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_an_already_authenticated_student_visiting_login_is_sent_to_the_student_dashboard(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get('/login');

        $response->assertRedirect(route('dashboard'));
    }
}
