<?php

namespace Tests\Feature\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScholarshipApplicationPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The student's existing applications, as application and scholarship
     * status pairs, and whether they may still apply to another scholarship.
     *
     * @return array<string, array{array<int, array{ApplicationStatus, ScholarshipStatus}>, bool}>
     */
    public static function existingApplications(): array
    {
        return [
            'no applications' => [[], true],
            'one pending' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open]], true],
            'two pending' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Pending, ScholarshipStatus::Open]], false],
            'one pending and one approved' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Approved, ScholarshipStatus::Open]], false],
            'approved on a closed scholarship still counts' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Approved, ScholarshipStatus::Closed]], false],
            'pending on a closed scholarship still counts' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Pending, ScholarshipStatus::Closed]], false],
            'rejected does not count' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Rejected, ScholarshipStatus::Open]], true],
            'approved on an ended scholarship does not count' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Approved, ScholarshipStatus::Ended]], true],
            'pending on an ended scholarship does not count' => [[[ApplicationStatus::Pending, ScholarshipStatus::Open], [ApplicationStatus::Pending, ScholarshipStatus::Ended]], true],
        ];
    }

    /**
     * @param  array<int, array{ApplicationStatus, ScholarshipStatus}>  $applications
     */
    #[DataProvider('existingApplications')]
    public function test_student_can_apply_only_while_under_the_scholarship_limit(array $applications, bool $canApply): void
    {
        $student = User::factory()->create();
        foreach ($applications as [$applicationStatus, $scholarshipStatus]) {
            ScholarshipApplication::factory()->for($student)
                ->for(Scholarship::factory()->state(['status' => $scholarshipStatus]))
                ->state(['status' => $applicationStatus])
                ->create();
        }
        $scholarship = Scholarship::factory()->create();

        $allowed = $student->can('create', [ScholarshipApplication::class, $scholarship]);

        $this->assertSame($canApply, $allowed);
    }

    /**
     * Other students' applications to a two-slot scholarship, and whether one
     * more student may still apply to it.
     *
     * @return array<string, array{array<int, ApplicationStatus>, bool}>
     */
    public static function otherApplicantsForTwoSlots(): array
    {
        return [
            'one slot left' => [[ApplicationStatus::Pending], true],
            'filled by pending applicants' => [[ApplicationStatus::Pending, ApplicationStatus::Pending], false],
            'filled by approved applicants' => [[ApplicationStatus::Approved, ApplicationStatus::Approved], false],
            'rejected applicants give their slots back' => [[ApplicationStatus::Pending, ApplicationStatus::Rejected, ApplicationStatus::Rejected], true],
        ];
    }

    /**
     * @param  array<int, ApplicationStatus>  $statuses
     */
    #[DataProvider('otherApplicantsForTwoSlots')]
    public function test_student_can_apply_only_while_other_applicants_leave_a_slot(array $statuses, bool $canApply): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 2]);
        foreach ($statuses as $status) {
            ScholarshipApplication::factory()->for($scholarship)->state(['status' => $status])->create();
        }

        $allowed = $student->can('create', [ScholarshipApplication::class, $scholarship]);

        $this->assertSame($canApply, $allowed);
    }

    /**
     * A student's course, whether the scholarship takes STEM courses only,
     * and whether the student may apply.
     *
     * @return array<string, array{string, bool, bool}>
     */
    public static function coursesAndCourseLimits(): array
    {
        return [
            'STEM course, STEM courses only' => ['BS Computer Science', true, true],
            'non-STEM course, STEM courses only' => ['BS Accountancy', true, false],
            'course saved before the program list, STEM courses only' => ['Bachelor of Science In Information Technology', true, false],
            'non-STEM course, open to every course' => ['BS Accountancy', false, true],
        ];
    }

    #[DataProvider('coursesAndCourseLimits')]
    public function test_a_stem_only_scholarship_admits_only_students_in_a_stem_course(string $course, bool $stemCoursesOnly, bool $canApply): void
    {
        $student = User::factory()->create(['course' => $course]);
        $scholarship = Scholarship::factory()->create(['stem_courses_only' => $stemCoursesOnly]);

        $allowed = $student->can('create', [ScholarshipApplication::class, $scholarship]);

        $this->assertSame($canApply, $allowed);
    }
}
