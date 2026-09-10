<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cvsu.edu.ph'],
            [
                'name' => 'OSAS Administrator',
                'first_name' => 'OSAS',
                'last_name' => 'Administrator',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@cvsu.edu.ph'],
            [
                'name' => 'Juan Dela Cruz',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'password' => 'password',
                'role' => UserRole::Student,
                'email_verified_at' => now(),
                'date_of_birth' => '2003-05-14',
                'student_number' => '21-00123',
                'course' => 'BS Information Technology',
                'year_level' => 3,
                'contact_number' => '09171234567',
                'address' => 'Indang, Cavite',
            ]
        );
    }
}
