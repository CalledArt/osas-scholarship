<?php

namespace Database\Factories;

use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scholarship>
 */
class ScholarshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->admin(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'slots' => fake()->numberBetween(1, 20),
            'deadline' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'status' => ScholarshipStatus::Open,
        ];
    }

    /**
     * Indicate that the scholarship is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ScholarshipStatus::Closed,
        ]);
    }
}
