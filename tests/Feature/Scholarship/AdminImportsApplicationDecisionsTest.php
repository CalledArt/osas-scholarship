<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_upload_pass_list_form_is_visible_when_applicants_are_pending(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();
        ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.show', $scholarship));

        $response->assertOk();
        $response->assertSee('Apply Decisions');
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
