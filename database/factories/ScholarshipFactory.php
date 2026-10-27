<?php

namespace Database\Factories;

use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\ScholarshipPresets;
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
            'type' => ScholarshipType::Government,
            'stem_courses_only' => false,
            'slots' => fake()->numberBetween(10, 20),
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

    /**
     * Indicate that only students in STEM courses may apply.
     */
    public function stemCoursesOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'stem_courses_only' => true,
        ]);
    }

    /**
     * Indicate the fund to split among the students who pass, in pesos.
     */
    public function funded(string $totalFunds = '100000.00'): static
    {
        return $this->state(fn (array $attributes) => [
            'total_funds' => $totalFunds,
        ]);
    }

    /**
     * Indicate that the scholarship's grant period has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ScholarshipStatus::Ended,
        ]);
    }

    /**
     * Indicate that OSAS posted the scholarship from the given preset (see ScholarshipPresets).
     */
    public function fromPreset(string $preset): static
    {
        return $this->state(fn (array $attributes) => [
            'preset' => $preset,
            'title' => ScholarshipPresets::ALL[$preset]['title'],
            'stem_courses_only' => ScholarshipPresets::limitsToStemCourses($preset),
        ]);
    }
}
