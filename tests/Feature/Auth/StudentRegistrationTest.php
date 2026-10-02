<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function googleRegistration(): array
    {
        return ['google_registration' => [
            'identity' => ['sub' => 'google-student-123', 'email' => 'juan@cvsu.edu.ph', 'name' => 'Juan Dela Cruz'],
            'expires_at' => now()->addMinutes(15)->getTimestamp(),
        ]];
    }

    /** @return array<string, mixed> */
    private function studentDetails(): array
    {
        return [
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'student_number' => '21-00123',
            'course' => 'BS Information Technology',
            'year_level' => 3,
            'date_of_birth' => '2003-05-14',
            'contact_number' => '09171234567',
            'address' => 'Indang, Cavite',
        ];
    }

    public function test_registration_requires_google_verification_for_both_display_and_submission(): void
    {
        $this->get('/register')->assertRedirect(route('login'));
        $this->post('/register', $this->studentDetails())->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_verified_students_see_the_form_with_a_locked_google_email_and_no_password(): void
    {
        $response = $this->withSession($this->googleRegistration())->get('/register');

        $response->assertOk()->assertSee('Complete your registration')
            ->assertSee('juan@cvsu.edu.ph')
            ->assertSee('name="student_number"', false)
            ->assertSee('name="course"', false)
            ->assertSee('name="year_level"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="password"', false);
    }

    public function test_completing_registration_saves_the_details_and_signs_in_only_as_the_verified_student(): void
    {
        $response = $this->withSession($this->googleRegistration())->post('/register', [
            ...$this->studentDetails(),
            'email' => 'someone.else@cvsu.edu.ph',
            'google_id' => 'another-google-account',
            'role' => 'admin',
            'password' => 'injected-password',
            'email_verified_at' => null,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'))
            ->assertSessionMissing('google_registration');
        $student = User::sole();
        $this->assertAuthenticatedAs($student);
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertSame('Juan Santos Dela Cruz', $student->name);
        $this->assertSame('juan@cvsu.edu.ph', $student->email);
        $this->assertSame('google-student-123', $student->google_id);
        $this->assertNull($student->password);
        $this->assertNotNull($student->email_verified_at);
        $this->assertFalse($student->must_change_password);
        $this->assertDatabaseHas('users', Arr::except($this->studentDetails(), 'date_of_birth'));
        $this->assertSame('2003-05-14', $student->date_of_birth->toDateString());
        $this->get(route('dashboard'))->assertRedirect(route('tutorial'));
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_registration_preserves_the_intended_student_destination(): void
    {
        $this->withSession([
            ...$this->googleRegistration(),
            'url.intended' => route('scholarships.index'),
        ])->post('/register', $this->studentDetails())
            ->assertRedirect(route('scholarships.index'));
    }

    public function test_expired_verification_cannot_display_or_submit_registration(): void
    {
        $this->freezeTime();
        $this->withSession($this->googleRegistration());
        $this->travel(15)->minutes();

        $this->get('/register')->assertRedirect(route('login'))
            ->assertSessionHasErrors('google')->assertSessionMissing('google_registration');
        $this->post('/register', $this->studentDetails())->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_requires_all_student_details_except_middle_name(): void
    {
        $this->withSession($this->googleRegistration())->from('/register')->post('/register', [])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['first_name', 'last_name', 'student_number', 'course', 'year_level', 'date_of_birth', 'contact_number', 'address']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    #[TestWith(['year_level', 0])]
    #[TestWith(['year_level', 7])]
    #[TestWith(['year_level', 'first'])]
    #[TestWith(['date_of_birth', 'not-a-date'])]
    #[TestWith(['date_of_birth', '2999-01-01'])]
    public function test_invalid_student_details_are_rejected(string $field, mixed $value): void
    {
        $this->withSession($this->googleRegistration())->from('/register')->post('/register', [
            ...$this->studentDetails(),
            $field => $value,
        ])->assertRedirect('/register')->assertSessionHasErrors($field);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_duplicate_student_number_is_rejected_without_losing_google_verification(): void
    {
        $existing = User::factory()->create(['student_number' => '21-00123']);

        $this->withSession($this->googleRegistration())->from('/register')->post('/register', $this->studentDetails())
            ->assertRedirect('/register')->assertSessionHasErrors('student_number')
            ->assertSessionHas('google_registration.identity.email', 'juan@cvsu.edu.ph');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($existing->email, User::sole()->email);
    }

    public function test_the_combined_name_must_fit_the_saved_full_name(): void
    {
        $this->withSession($this->googleRegistration())->from('/register')->post('/register', [
            ...$this->studentDetails(),
            'first_name' => str_repeat('A', 130),
            'last_name' => str_repeat('B', 130),
        ])->assertRedirect('/register')
            ->assertSessionHasErrors(['last_name' => 'Your full name must not exceed 255 characters.']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_middle_name_is_optional(): void
    {
        $this->withSession($this->googleRegistration())->post('/register', [
            ...$this->studentDetails(),
            'middle_name' => '',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $this->assertNull(User::sole()->middle_name);
        $this->assertSame('Juan Dela Cruz', User::sole()->name);
    }

    #[TestWith(['email', 'juan@cvsu.edu.ph'])]
    #[TestWith(['google_id', 'google-student-123'])]
    public function test_an_account_created_while_the_form_is_open_is_not_overwritten(string $field, string $value): void
    {
        $existing = User::factory()->admin()->create([$field => $value]);

        $this->withSession($this->googleRegistration())->from('/register')->post('/register', $this->studentDetails())
            ->assertRedirect('/register')->assertSessionHasErrors('registration');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($existing->name, User::sole()->name);
        $this->assertSame(UserRole::Admin, User::sole()->role);
    }

    public function test_registration_cannot_be_replayed_after_logout(): void
    {
        $this->withSession($this->googleRegistration())->post('/register', $this->studentDetails())
            ->assertRedirect(route('dashboard'));
        $this->post(route('logout'));

        $this->post('/register', $this->studentDetails())->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
