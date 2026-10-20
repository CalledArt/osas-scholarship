<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_staff_can_update_their_profile_and_email(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Reyes',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Maria', $user->first_name);
        $this->assertSame('Santos', $user->middle_name);
        $this->assertSame('Reyes', $user->last_name);
        $this->assertSame('Maria Santos Reyes', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_a_student_cannot_change_their_email(): void
    {
        $student = User::factory()->named('Pedro', null, 'Reyes')->create(['email' => 'juan@cvsu.edu.ph']);

        $response = $this
            ->actingAs($student)
            ->patch('/profile', [
                'first_name' => 'Juan',
                'last_name' => 'Cruz',
                'email' => 'classmate@cvsu.edu.ph',
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertSame('juan@cvsu.edu.ph', $student->refresh()->email);
        $this->assertSame('Juan Cruz', $student->name);
        $this->assertNotNull($student->email_verified_at);
    }

    public function test_the_profile_page_offers_students_no_password_or_email_change(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/profile');

        $response->assertOk()
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="current_password"', false);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Reyes',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_middle_name_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Juan',
                'middle_name' => '',
                'last_name' => 'Dela Cruz',
                'email' => $user->email,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('Juan Dela Cruz', $user->refresh()->name);
    }

    public function test_rejects_a_profile_update_with_no_first_or_last_name(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'first_name' => '',
                'middle_name' => 'Santos',
                'last_name' => '',
                'email' => $user->email,
            ]);

        $response->assertSessionHasErrors(['first_name', 'last_name']);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_the_profile_page_shows_a_student_their_personal_details(): void
    {
        $student = User::factory()->create([
            'date_of_birth' => '2003-05-14',
            'contact_number' => '09171234567',
            'address' => 'Indang, Cavite',
        ]);

        $response = $this->actingAs($student)->get('/profile');

        $response->assertSee('value="2003-05-14"', false);
        $response->assertSee('value="09171234567"', false);
        $response->assertSee('value="Indang, Cavite"', false);
    }

    public function test_a_student_can_update_their_own_personal_details(): void
    {
        $student = User::factory()->named('Juan', null, 'Cruz')->create();

        $response = $this->actingAs($student)->patch('/profile', [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'date_of_birth' => '2003-05-14',
            'contact_number' => '09998887777',
            'address' => 'Trece Martires, Cavite',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $student->refresh();
        $this->assertSame('2003-05-14', $student->date_of_birth->toDateString());
        $this->assertSame('09998887777', $student->contact_number);
        $this->assertSame('Trece Martires, Cavite', $student->address);
    }

    public function test_a_student_cannot_clear_their_personal_details(): void
    {
        $student = User::factory()->named('Juan', null, 'Cruz')->create(['contact_number' => '09171234567']);

        $response = $this->actingAs($student)->from('/profile')->patch('/profile', [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'date_of_birth' => '',
            'contact_number' => '',
            'address' => '',
        ]);

        $response->assertSessionHasErrors(['date_of_birth', 'contact_number' => 'The contact number field is required.', 'address']);
        $this->assertSame('09171234567', $student->refresh()->contact_number);
    }
}
