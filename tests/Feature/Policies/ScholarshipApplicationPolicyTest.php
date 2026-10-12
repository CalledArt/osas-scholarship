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
}
