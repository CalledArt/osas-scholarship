<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipApplication>
 */
class ScholarshipApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scholarship_id' => Scholarship::factory(),
            'user_id' => User::factory(),
            'status' => ApplicationStatus::Pending,
            'submitted_at' => now(),
        ];
    }

    /**
     * Indicate that the application has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::Approved,
        ]);
    }

    /**
     * Indicate that the application has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::Rejected,
        ]);
    }
}
