<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\TestStudents;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Creates the twenty hardcoded test student accounts (App\Support\TestStudents).
 *
 * Called by DatabaseSeeder. Each gets TestStudents::PASSWORD, which works only
 * on a local machine. Seeding again updates the same accounts instead
 * of adding more. Only the accounts are seeded: no scholarships or
 * applications, so a database starts with nothing made up beyond them.
 */
class TestStudentsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (TestStudents::accounts() as $email => $attributes) {
            User::updateOrCreate(['email' => $email], [...$attributes, 'password' => TestStudents::PASSWORD]);
        }
    }
}
