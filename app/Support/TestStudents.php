<?php

namespace App\Support;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\User;

/**
 * The twenty hardcoded test student accounts.
 *
 * DatabaseSeeder creates them (TestStudentsSeeder). On a local machine they
 * sign in on the ordinary password form with PASSWORD (LoginRequest), since
 * real students sign in only with CvSU Google, and they apply to every
 * scholarship OSAS posts that they may apply to (App\Actions\ApplyTestStudents).
 * Neither happens on the live site, where they exist only if someone seeds
 * that database.
 *
 * The details are fixed, so a pass list written against them keeps working,
 * and every account passes the registration rules (9-digit student number,
 * 11-digit contact number, a listed course). Between them they cover the
 * cases worth trying: STEM and non-STEM courses, every year level, 4-, 5-
 * and 6-year programs, no middle name, a lower-case surname ("de la Paz")
 * and an ñ, two students on leave of absence, and one past the years
 * Republic Act 10931 covers. Emails are @example.com, a domain reserved for
 * examples, so nothing sent to them reaches a real inbox; student numbers
 * count from 90001 within their entry year so they stand out.
 */
class TestStudents
{
    /**
     * Every test account's password.
     */
    public const PASSWORD = 'michael';

    /**
     * First, middle and last name, course, year level, program length, the
     * school year they entered (the first four digits of the student number),
     * and whether OSAS has them on leave of absence.
     *
     * @var list<array{string, ?string, string, string, int, int, int, bool}>
     */
    private const ACCOUNTS = [
        ['Juan', 'Santos', 'Dela Cruz', 'BS Information Technology', 3, 4, 2024, false],
        ['Maria Clara', 'Reyes', 'Santos', 'BS Accountancy', 2, 4, 2025, false],
        ['Jose', 'Mercado', 'Rizal', 'BS Computer Science', 4, 4, 2023, false],
        ['Andrea', null, 'Bautista', 'BS Biology', 1, 4, 2026, false],
        ['Mark Anthony', 'Villanueva', 'Garcia', 'BS Civil Engineering', 5, 5, 2022, false],
        ['Kristine', 'Lopez', 'de la Paz', 'BS Psychology', 3, 4, 2024, false],
        ['Paolo', 'Ramos', 'Mendoza', 'BS Criminology', 2, 4, 2025, false],
        ['Angelica', 'Torres', 'Aquino', 'Bachelor of Secondary Education', 4, 4, 2023, false],
        ['Rafael', 'Domingo', 'Castillo', 'BS Electrical Engineering', 3, 5, 2024, false],
        ['Jasmine', null, 'Navarro', 'BS Nursing', 2, 4, 2025, false],
        ['Carlo', 'Ignacio', 'Peñaflor', 'BS Agriculture', 1, 4, 2026, false],
        ['Bea', 'Salazar', 'Fernandez', 'BS Hospitality Management', 3, 4, 2024, false],
        ['Miguel', 'Corpuz', 'Pascual', 'BS Information Technology', 4, 4, 2021, false],
        ['Princess Joy', 'Manalo', 'Soriano', 'BS Business Management', 2, 4, 2025, false],
        ['Gabriel', 'Estrada', 'Valdez', 'BS Applied Mathematics', 3, 4, 2024, false],
        ['Nicole', 'Dizon', 'Robles', 'BS Tourism Management', 1, 4, 2026, false],
        ['Joshua', 'Panganiban', 'Lim', 'BS Computer Engineering', 2, 5, 2025, true],
        ['Erika', 'Gonzales', 'Tan', 'BS Midwifery', 3, 4, 2024, true],
        ['Christian Dave', 'Morales', 'Ocampo', 'BS Industrial Technology major in Automotive Technology', 2, 4, 2025, false],
        ['Samantha', 'Ilagan', 'Cruz', 'Doctor of Veterinary Medicine', 4, 6, 2023, false],
    ];

    private const TOWNS = [
        'Indang', 'Trece Martires City', 'General Trias City', 'Dasmariñas City', 'Imus City',
        'Tagaytay City', 'Naic', 'Silang', 'Amadeo', 'Alfonso',
    ];

    /**
     * Each account's user attributes, keyed by its email.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function accounts(): array
    {
        $accounts = [];

        foreach (self::ACCOUNTS as $index => [$firstName, $middleName, $lastName, $course, $yearLevel, $programYears, $entryYear, $onLeave]) {
            $number = $index + 1;

            $accounts[self::email($number)] = [
                'name' => PersonName::assemble($firstName, $middleName, $lastName),
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'role' => UserRole::Student,
                'email_verified_at' => now(),
                'student_number' => sprintf('%d%05d', $entryYear, 90000 + $number),
                'course' => $course,
                'year_level' => $yearLevel,
                'program_years' => $programYears,
                'student_status' => $onLeave ? StudentStatus::LeaveOfAbsence : StudentStatus::Active,
                'date_of_birth' => sprintf('%d-%02d-%02d', $entryYear - 18, $number % 12 + 1, $number % 28 + 1),
                'contact_number' => sprintf('0917%07d', 9000000 + $number),
                'address' => self::TOWNS[$index % count(self::TOWNS)].', Cavite',
            ];
        }

        return $accounts;
    }

    /**
     * The test accounts' emails, in order.
     *
     * @return list<string>
     */
    public static function emails(): array
    {
        return array_keys(self::accounts());
    }

    /**
     * Whether the user may sign in with a password although a student: only
     * a test account, and only on a local machine.
     */
    public static function maySignInWithPassword(User $user): bool
    {
        return app()->isLocal() && in_array($user->email, self::emails(), true);
    }

    private static function email(int $number): string
    {
        return sprintf('test.student%02d@example.com', $number);
    }
}
