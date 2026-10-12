<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ApplicationStatus;
use App\Mail\ScholarshipApplicationApproved;
use App\Mail\ScholarshipApplicationRejected;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AdminImportsApplicationDecisionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_pass_list_and_sees_results(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['slots' => 5]);
        $passed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();
        $failed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Maria Santos']))
            ->create();

        $response = $this->actingAs($admin)->post(
            route('admin.scholarships.applications.import-decisions', $scholarship),
            ['file' => $this->csvFile("name\nJuan Dela Cruz\n")]
        );

        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $this->assertSame(ApplicationStatus::Approved, $passed->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $failed->fresh()->status);

        $this->followRedirects($response)->assertSee('1 approved, 1 rejected.');
    }

    public function test_applicants_are_emailed_whether_they_passed_or_failed(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['slots' => 5]);
        $passed = User::factory()->create(['name' => 'Juan Dela Cruz']);
        $failed = User::factory()->create(['name' => 'Maria Santos']);
        ScholarshipApplication::factory()->for($scholarship)->for($passed)->create();
        ScholarshipApplication::factory()->for($scholarship)->for($failed)->create();

        $response = $this->actingAs($admin)->post(
            route('admin.scholarships.applications.import-decisions', $scholarship),
            ['file' => $this->csvFile("name\nJuan Dela Cruz\n")]
        );

        $response->assertSessionHas('status', 'Emailed the result to 2 applicants.');
        Mail::assertSent(ScholarshipApplicationApproved::class, fn (ScholarshipApplicationApproved $mail) => $mail->hasBcc($passed->email)
            && ! $mail->hasBcc($failed->email));
        Mail::assertSent(ScholarshipApplicationRejected::class, fn (ScholarshipApplicationRejected $mail) => $mail->hasBcc($failed->email)
            && ! $mail->hasBcc($passed->email));
    }

    public function test_applicants_the_pass_list_leaves_pending_are_not_emailed(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['slots' => 5]);
        foreach (User::factory()->count(2)->create(['name' => 'Ana Cruz']) as $namesake) {
            ScholarshipApplication::factory()->for($scholarship)->for($namesake)->create();
        }

        $this->actingAs($admin)->post(
            route('admin.scholarships.applications.import-decisions', $scholarship),
            ['file' => $this->csvFile("name\nAna Cruz\n")]
        );

        Mail::assertNothingSent();
    }

    public function test_decisions_are_kept_when_the_result_email_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new TransportException('Connection refused.'));
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['slots' => 5]);
        $passed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Juan Dela Cruz']))
            ->create();
        $failed = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->create(['name' => 'Maria Santos']))
            ->create();

        $response = $this->actingAs($admin)->post(
            route('admin.scholarships.applications.import-decisions', $scholarship),
            ['file' => $this->csvFile("name\nJuan Dela Cruz\n")]
        );

        $response->assertSessionHas('error', 'The result email reached only 0 of 2 applicants. Check the mail settings and the application log.');
        $this->assertSame(ApplicationStatus::Approved, $passed->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $failed->fresh()->status);
    }

    public function test_upload_pass_list_section_is_visible_even_with_no_pending_applicants(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.show', $scholarship));

        $response->assertOk();
        $response->assertSee('Upload Pass List');
        $response->assertSee('There are no pending applicants to decide on right now.');
        $response->assertDontSee('Apply Decisions');
    }

    public function test_the_pass_list_is_the_only_decision_offered_for_pending_applicants(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();
        ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.show', $scholarship));

        $response->assertOk();
        $response->assertSee('Apply Decisions');
        $response->assertDontSee('Approve');
        $response->assertDontSee('Reject');
    }

    public function test_an_application_cannot_be_decided_one_at_a_time(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->patch(
            "/admin/scholarships/{$scholarship->id}/applications/{$application->id}",
            ['status' => 'approved']
        );

        $response->assertNotFound();
        $this->assertSame(ApplicationStatus::Pending, $application->fresh()->status);
    }

    public function test_student_is_forbidden_from_uploading_a_pass_list(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->post(
            route('admin.scholarships.applications.import-decisions', $scholarship),
            ['file' => $this->csvFile("name\nJuan Dela Cruz\n")]
        );

        $response->assertForbidden();
    }

    public function test_upload_requires_a_file(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.scholarships.applications.import-decisions', $scholarship),
            []
        );

        $response->assertSessionHasErrors('file');
    }

    private function csvFile(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'decisions.csv', 'text/csv', null, true);
    }
}
