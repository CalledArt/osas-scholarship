<?php

namespace Tests\Feature\Scholarship;

use App\Actions\DecideScholarshipApplicationsFromCsv;
use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DecideScholarshipApplicationsFromCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_approves_matched_names_and_rejects_the_rest(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $passed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();
        $failed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Maria Santos']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ApplicationStatus::Approved, $passed->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $failed->fresh()->status);
        $this->assertCount(1, $results['approved']);
        $this->assertCount(1, $results['rejected']);
        $this->assertSame('Cruz, Juan Dela', $results['approved'][0]['name']);
    }

    public function test_matching_ignores_case_and_extra_whitespace(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\n  juan   DELA cruz  \n"));

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
    }

    public function test_accepts_a_headerless_list_of_names(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("Juan Dela Cruz\n"));

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
    }

    public function test_leaves_already_decided_applications_untouched(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $alreadyRejected = ScholarshipApplication::factory()->rejected()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ApplicationStatus::Rejected, $alreadyRejected->fresh()->status);
        $this->assertCount(0, $results['approved']);
    }

    public function test_flags_a_name_shared_by_two_pending_applicants_as_ambiguous(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $first = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();
        $second = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ApplicationStatus::Pending, $first->fresh()->status);
        $this->assertSame(ApplicationStatus::Pending, $second->fresh()->status);
        $this->assertCount(1, $results['ambiguous']);
        $this->assertSame(2, $results['ambiguous'][0]['count']);
    }

    public function test_reports_names_that_do_not_match_any_pending_applicant(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\nNonexistent Student\n"));

        $this->assertSame([['name' => 'Nonexistent Student', 'student_number' => null]], $results['unmatched']);
    }

    public function test_matches_by_student_number_when_provided(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz', 'student_number' => '21-00123']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle(
            $scholarship,
            $this->csvFile("student_number\n21-00123\n")
        );

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
        $this->assertCount(1, $results['approved']);
    }

    public function test_student_number_resolves_a_name_that_would_otherwise_be_ambiguous(): void
    {
        // Both applicants share a name, which would be ambiguous under
        // name-only matching. Since each has their own student number,
        // the CSV's number confirms exactly who passed and who didn't —
        // no guessing required, so this must not be reported as ambiguous.
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $passed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz', 'student_number' => '21-00123']))
            ->create();
        $notPassed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz', 'student_number' => '21-00456']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle(
            $scholarship,
            $this->csvFile("name,student_number\nJuan Dela Cruz,21-00123\n")
        );

        $this->assertSame(ApplicationStatus::Approved, $passed->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $notPassed->fresh()->status);
        $this->assertCount(0, $results['ambiguous']);
    }

    public function test_falls_back_to_name_for_a_row_without_a_student_number(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz', 'student_number' => null]))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle(
            $scholarship,
            $this->csvFile("name,student_number\nJuan Dela Cruz,\n")
        );

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
        $this->assertCount(1, $results['approved']);
    }

    public function test_a_wrong_student_number_is_not_approved_by_a_matching_name(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz', 'student_number' => '202100123']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle(
            $scholarship,
            $this->csvFile("name,student_number\nJuan Dela Cruz,202199999\n")
        );

        $this->assertSame(ApplicationStatus::Rejected, $application->fresh()->status);
        $this->assertSame([], $results['approved']);
        $this->assertSame([['name' => 'Juan Dela Cruz', 'student_number' => '202199999']], $results['unmatched']);
    }

    public function test_reports_an_unmatched_student_number(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 5]);

        $results = (new DecideScholarshipApplicationsFromCsv)->handle(
            $scholarship,
            $this->csvFile("student_number\n99-99999\n")
        );

        $this->assertSame([['name' => null, 'student_number' => '99-99999']], $results['unmatched']);
    }

    public function test_does_not_approve_beyond_remaining_slots(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        $pending = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ApplicationStatus::Pending, $pending->fresh()->status);
        $this->assertCount(1, $results['skipped_capacity']);
    }

    public function test_closes_the_scholarship_once_slots_are_filled(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ScholarshipStatus::Closed, $scholarship->fresh()->status);
    }

    public function test_leaves_an_ended_scholarship_ended_once_slots_are_filled(): void
    {
        $scholarship = Scholarship::factory()->ended()->create(['slots' => 1]);
        ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();

        (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ScholarshipStatus::Ended, $scholarship->fresh()->status);
    }

    public function test_rejects_a_passed_student_who_already_holds_an_active_scholarship(): void
    {
        $student = User::factory()->create(['name' => 'Juan Dela Cruz']);
        ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->closed()->create(['title' => 'CHED Merit Grant']))
            ->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        $application = ScholarshipApplication::factory()->for($scholarship)->for($student)->create();
        $next = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Maria Santos']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\nMaria Santos\n"));

        $this->assertSame(ApplicationStatus::Rejected, $application->fresh()->status);
        $this->assertTrue($application->fresh()->passed_while_holding_active);
        $this->assertSame([['name' => 'Cruz, Juan Dela', 'email' => $student->email, 'active_scholarship' => 'CHED Merit Grant']], $results['already_active']);
        $this->assertSame([], $results['rejected']);
        $this->assertSame(ApplicationStatus::Approved, $next->fresh()->status);
    }

    public function test_voids_a_pending_applicant_on_leave_of_absence_instead_of_approving_them(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        $application = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->onLeaveOfAbsence()->create(['name' => 'Juan Dela Cruz']))
            ->create();
        $next = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Maria Santos']))
            ->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\nMaria Santos\n"));

        $this->assertSame(ApplicationStatus::Voided, $application->fresh()->status);
        $this->assertSame(ApplicationStatus::Approved, $next->fresh()->status);
        $this->assertSame([['name' => 'Juan Dela Cruz', 'student_number' => null]], $results['unmatched']);
    }

    public function test_approves_a_student_whose_earlier_scholarship_has_ended(): void
    {
        $student = User::factory()->create(['name' => 'Juan Dela Cruz']);
        ScholarshipApplication::factory()->approved()->for($student)->for(Scholarship::factory()->ended())->create();
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)->for($student)->create();

        $results = (new DecideScholarshipApplicationsFromCsv)->handle($scholarship, $this->csvFile("name\nJuan Dela Cruz\n"));

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
        $this->assertSame([], $results['already_active']);
    }

    private function csvFile(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'decisions.csv', 'text/csv', null, true);
    }
}
