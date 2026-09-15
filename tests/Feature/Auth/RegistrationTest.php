<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A complete, valid registration payload.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'first_name' => 'Michael',
            'middle_name' => 'Dela',
            'last_name' => 'Valledor',
            'email' => 'main.michael.valledor@cvsu.edu.ph',
            'student_number' => '202200976',
            'course' => 'BS Computer Science',
            'year_level' => '4',
            'contact_number' => '09171234567',
            'address' => 'Indang, Cavite',
            'date_of_birth' => '2003-05-14',
            'password' => 'Password-123',
            'password_confirmation' => 'Password-123',
            ...$overrides,
        ];
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_new_students_can_register_and_are_signed_in(): void
    {
        $response = $this->post(route('register'), $this->payload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::firstWhere('email', 'main.michael.valledor@cvsu.edu.ph');

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame('Michael Dela Valledor', $user->name);
        $this->assertSame('202200976', $user->student_number);
        $this->assertSame('BS Computer Science', $user->course);
        $this->assertSame(4, $user->year_level);
        $this->assertSame('09171234567', $user->contact_number);
        $this->assertSame('Indang, Cavite', $user->address);
        $this->assertSame('2003-05-14', $user->date_of_birth->toDateString());
    }

    public function test_a_registered_student_is_verified_and_not_forced_to_change_password(): void
    {
        $this->post(route('register'), $this->payload());

        $user = User::firstWhere('email', 'main.michael.valledor@cvsu.edu.ph');

        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->must_change_password);
    }

    public function test_a_registered_student_can_reach_the_dashboard(): void
    {
        $this->post(route('register'), $this->payload());

        $this->get(route('dashboard'))->assertStatus(200);
    }

    public function test_registration_is_rejected_for_a_non_school_email(): void
    {
        $response = $this->post(route('register'), $this->payload([
            'email' => 'valledormichael5@gmail.com',
        ]));

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_is_rejected_for_an_email_already_taken(): void
    {
        User::factory()->create(['email' => 'main.michael.valledor@cvsu.edu.ph']);

        $response = $this->post(route('register'), $this->payload());

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_is_rejected_for_a_student_number_already_taken(): void
    {
        User::factory()->create(['student_number' => '202200976']);

        $response = $this->post(route('register'), $this->payload());

        $response->assertSessionHasErrors('student_number');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_a_student_number(): void
    {
        $response = $this->post(route('register'), $this->payload(['student_number' => '']));

        $response->assertSessionHasErrors('student_number');
        $this->assertGuest();
    }

    public function test_registration_requires_a_confirmed_password(): void
    {
        $response = $this->post(route('register'), $this->payload([
            'password_confirmation' => 'something-else',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_an_uppercased_school_email_is_stored_lowercase(): void
    {
        $this->post(route('register'), $this->payload([
            'email' => 'Main.Michael.Valledor@CVSU.EDU.PH',
        ]));

        $this->assertDatabaseHas('users', ['email' => 'main.michael.valledor@cvsu.edu.ph']);
    }

    public function test_optional_details_may_be_left_blank(): void
    {
        $response = $this->post(route('register'), $this->payload([
            'middle_name' => '',
            'course' => '',
            'year_level' => '',
            'contact_number' => '',
            'address' => '',
            'date_of_birth' => '',
        ]));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::firstWhere('email', 'main.michael.valledor@cvsu.edu.ph');

        $this->assertSame('Michael Valledor', $user->name);
        $this->assertNull($user->course);
    }

    public function test_a_signed_in_user_is_redirected_away_from_registration(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('register'));

        $response->assertRedirect();
    }
}
