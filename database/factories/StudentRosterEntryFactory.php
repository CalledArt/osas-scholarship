<?php

namespace Database\Factories;

use App\Models\StudentRosterEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentRosterEntry>
 */
class StudentRosterEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->userName().'@cvsu.edu.ph',
            'student_number' => fake()->unique()->numerify('##-#####'),
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'last_name' => fake()->lastName(),
            'course' => fake()->randomElement(['BS Computer Science', 'BS Information Technology', 'BS Accountancy', 'BS Civil Engineering']),
            'year_level' => fake()->numberBetween(1, 4),
        ];
    }
}
