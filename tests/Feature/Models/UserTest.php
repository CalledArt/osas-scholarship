<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_joins_the_parts_in_first_middle_last_order(): void
    {
        $user = User::factory()->named('Maria', 'Santos', 'Reyes')->create();

        $this->assertSame('Maria Santos Reyes', $user->full_name);
    }

    public function test_full_name_omits_an_absent_middle_name(): void
    {
        $user = User::factory()->named('Juan', null, 'Dela Cruz')->create();

        $this->assertSame('Juan Dela Cruz', $user->full_name);
    }

    public function test_full_name_falls_back_to_the_stored_name_when_no_parts_are_set(): void
    {
        $user = User::factory()->create();

        // A row written before the name-part columns existed, and never backfilled.
        DB::table('users')->where('id', $user->id)->update([
            'name' => 'Unbackfilled Student',
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
        ]);

        $this->assertSame('Unbackfilled Student', $user->fresh()->full_name);
    }

    /**
     * Student number, year level, program length, today's date, then the
     * expected year of study and whether RA 10931 still covers it.
     *
     * @return array<string, array{string, ?int, int, string, ?int, bool}>
     */
    public static function yearsOfStudy(): array
    {
        return [
            'fifth year of a four-year program' => ['202212345', 1, 4, '2026-10-12', 5, true],
            'sixth year of a four-year program' => ['202112345', 1, 4, '2026-10-12', 6, false],
            'sixth year of a five-year program' => ['202112345', 1, 5, '2026-10-12', 6, true],
            'the day before the school year turns over' => ['202212345', 1, 4, '2027-07-31', 5, true],
            'the day the school year turns over' => ['202212345', 1, 4, '2027-08-01', 6, false],
            'a number without an entry year falls back to year level' => ['21-00123', 6, 4, '2026-10-12', 6, false],
            'an entry year still to come falls back to year level' => ['202712345', 2, 4, '2026-10-12', 2, true],
            'an unknown year of study keeps the coverage' => ['21-00123', null, 4, '2026-10-12', null, true],
        ];
    }

    #[DataProvider('yearsOfStudy')]
    public function test_counts_the_year_of_study_and_whether_ra_10931_still_covers_it(string $studentNumber, ?int $yearLevel, int $programYears, string $today, ?int $expectedYear, bool $expectedCovered): void
    {
        $this->travelTo($today);
        $student = User::factory()->make([
            'student_number' => $studentNumber,
            'year_level' => $yearLevel,
            'program_years' => $programYears,
        ]);

        $yearOfStudy = $student->yearOfStudy();
        $isCovered = $student->hasFreeTertiaryEducation();

        $this->assertSame($expectedYear, $yearOfStudy);
        $this->assertSame($expectedCovered, $isCovered);
    }

    public function test_leave_of_absence_pauses_ra_10931_without_using_up_the_years_it_covers(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->onLeaveOfAbsence()->make(['student_number' => '202312345', 'program_years' => 4]);

        $hasFreeTuition = $student->hasFreeTertiaryEducation();
        $isWithinCoveredYears = $student->isWithinFreeTertiaryEducationYears();

        $this->assertFalse($hasFreeTuition);
        $this->assertTrue($isWithinCoveredYears);
    }
}
