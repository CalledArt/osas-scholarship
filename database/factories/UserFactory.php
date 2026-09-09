<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
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
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Student,
            'date_of_birth' => fake()->dateTimeBetween('-25 years', '-18 years'),
            'student_number' => fake()->unique()->numerify('##-#####'),
            'course' => fake()->randomElement(['BS Computer Science', 'BS Information Technology', 'BS Accountancy', 'BS Civil Engineering']),
            'year_level' => fake()->numberBetween(1, 4),
            'contact_number' => fake()->numerify('09#########'),
            'address' => fake()->address(),
        ];
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
        ]);
    }
}
