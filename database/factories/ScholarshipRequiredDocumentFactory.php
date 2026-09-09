<?php

namespace Database\Factories;

use App\Models\Scholarship;
use App\Models\ScholarshipRequiredDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipRequiredDocument>
 */
class ScholarshipRequiredDocumentFactory extends Factory
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
            'name' => fake()->randomElement(['2x2 Picture', 'Certificate of Registration', 'Income Tax Return']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
