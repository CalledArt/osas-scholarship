<?php

namespace Database\Factories;

use App\Models\ScholarshipApplication;
use App\Models\ScholarshipApplicationDocument;
use App\Models\ScholarshipRequiredDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipApplicationDocument>
 */
class ScholarshipApplicationDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scholarship_application_id' => ScholarshipApplication::factory(),
            'scholarship_required_document_id' => ScholarshipRequiredDocument::factory(),
            'disk' => 'supabase',
            'path' => 'scholarship-documents/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
        ];
    }
}
