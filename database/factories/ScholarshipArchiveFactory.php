<?php

namespace Database\Factories;

use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use App\Models\ScholarshipArchive;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipArchive>
 */
class ScholarshipArchiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scholarship_id' => $scholarshipId = fake()->unique()->numberBetween(1, 99999),
            'scholarship_reference' => sprintf('SCH-2026-%05d', $scholarshipId),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => ScholarshipType::Government,
            'stem_courses_only' => false,
            'slots' => 10,
            'deadline' => fake()->dateTimeBetween('-3 months', '-1 week'),
            'status' => ScholarshipStatus::Ended,
            'total_funds' => '100000.00',
            'share_amount' => '10000.00',
            'recipient_count' => 10,
            'posted_by' => User::factory()->admin(),
            'posted_at' => fake()->dateTimeBetween('-6 months', '-3 months'),
            'deleted_by' => User::factory()->admin(),
        ];
    }
}
