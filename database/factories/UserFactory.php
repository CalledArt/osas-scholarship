<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\PersonName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // Derived from `name` so that overriding the full name alone, which
            // most tests in this suite do, still yields consistent parts.
            'first_name' => fn (array $attributes): ?string => PersonName::split($attributes['name'])['first_name'],
            'middle_name' => fn (array $attributes): ?string => PersonName::split($attributes['name'])['middle_name'],
            'last_name' => fn (array $attributes): ?string => PersonName::split($attributes['name'])['last_name'],
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Student,
            'date_of_birth' => fake()->dateTimeBetween('-25 years', '-18 years'),
            'student_number' => fake()->unique()->numerify('##-#####'),
            'course' => fake()->randomElement(['BS Computer Science', 'BS Information Technology', 'BS Accountancy', 'BS Civil Engineering']),
            'year_level' => fake()->numberBetween(1, 4),
            'program_years' => 4,
            'contact_number' => fake()->numerify('09#########'),
            'address' => fake()->address(),
            // Most tests act as someone who has used the system before; see newcomer().
            'tutorial_completed_at' => now(),
        ];
    }

    /**
     * Indicate that the user has not seen the practice tour yet, as on their first sign-in.
     */
    public function newcomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'tutorial_completed_at' => null,
        ]);
    }

    /**
     * Give the user an exact set of name parts, with the full name to match.
     *
     * Use this rather than overriding the parts individually, which would leave
     * the derived `name` disagreeing with them.
     */
    public function named(string $firstName, ?string $middleName, string $lastName): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => PersonName::assemble($firstName, $middleName, $lastName),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
        ]);
    }

    /**
     * Indicate that the student is on leave of absence.
     */
    public function onLeaveOfAbsence(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_status' => StudentStatus::LeaveOfAbsence,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an OSAS administrator.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
            'student_number' => null,
            'course' => null,
            'year_level' => null,
            'program_years' => null,
        ]);
    }
}
