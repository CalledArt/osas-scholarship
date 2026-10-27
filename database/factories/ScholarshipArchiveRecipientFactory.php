<?php

namespace Database\Factories;

use App\Models\ScholarshipArchive;
use App\Models\ScholarshipArchiveRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipArchiveRecipient>
 */
class ScholarshipArchiveRecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'scholarship_archive_id' => ScholarshipArchive::factory(),
            'user_id' => null,
            'application_reference' => sprintf('2026-%05d', fake()->unique()->numberBetween(1, 99999)),
            'student_number' => (string) fake()->unique()->numberBetween(202100000, 202699999),
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'name' => "{$firstName} {$lastName}",
            'course' => 'Bachelor of Science in Information Technology',
            'amount' => '10000.00',
        ];
    }
}
