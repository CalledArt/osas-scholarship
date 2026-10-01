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

    /**
     * Indicate that OSAS has posted a sample of the document for applicants.
     */
    public function withSample(): static
    {
        return $this->state(fn (array $attributes): array => [
            'sample_disk' => 'supabase',
            'sample_path' => 'scholarship-samples/'.fake()->uuid().'.pdf',
            'sample_original_name' => 'sample.pdf',
        ]);
    }
}
