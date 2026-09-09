<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScholarshipApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_form_is_prefilled_from_the_authenticated_users_own_profile(): void
    {
        $student = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => now()->subYears(20)->format('Y-m-d'),
            'student_number' => '21-00123',
        ]);
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.apply', $scholarship));

        $response->assertOk();
        $response->assertSee('Juan Dela Cruz');
        $response->assertSee('20');
        $response->assertSee('21-00123');
    }

    public function test_student_can_submit_an_application_with_required_documents(): void
    {
        Storage::fake('supabase');
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $requiredDocument = $scholarship->requiredDocuments()->create(['name' => '2x2 Picture']);

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $scholarship), [
            'documents' => [
                $requiredDocument->id => UploadedFile::fake()->image('picture.jpg'),
            ],
        ]);

        $application = ScholarshipApplication::sole();
        $response->assertRedirect(route('scholarship-applications.show', $application));
        $this->assertSame($student->id, $application->user_id);
        $this->assertSame(ApplicationStatus::Pending, $application->status);
        $document = $application->documents()->sole();
        Storage::disk('supabase')->assertExists($document->path);
    }

    public function test_submitted_application_always_belongs_to_the_authenticated_user_regardless_of_request_input(): void
    {
        Storage::fake('supabase');
        $student = User::factory()->create(['name' => 'Real Applicant']);
        $impersonationTarget = User::factory()->create(['name' => 'Victim Student']);
        $scholarship = Scholarship::factory()->create();
        $requiredDocument = $scholarship->requiredDocuments()->create(['name' => '2x2 Picture']);

        $this->actingAs($student)->post(route('scholarships.apply.store', $scholarship), [
            'user_id' => $impersonationTarget->id,
            'name' => 'Victim Student',
            'status' => 'approved',
            'documents' => [
                $requiredDocument->id => UploadedFile::fake()->image('picture.jpg'),
            ],
        ]);

        $application = ScholarshipApplication::sole();
        $this->assertSame($student->id, $application->user_id);
        $this->assertSame(ApplicationStatus::Pending, $application->status);
        $this->assertDatabaseCount('scholarship_applications', 1);
    }

    public function test_application_fails_validation_when_a_required_document_is_missing(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $requiredDocument = $scholarship->requiredDocuments()->create(['name' => '2x2 Picture']);

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $scholarship), [
            'documents' => [],
        ]);

        $response->assertSessionHasErrors(["documents.{$requiredDocument->id}"]);
        $this->assertDatabaseCount('scholarship_applications', 0);
    }

    public function test_application_rejects_a_disallowed_file_type(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $requiredDocument = $scholarship->requiredDocuments()->create(['name' => '2x2 Picture']);

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $scholarship), [
            'documents' => [
                $requiredDocument->id => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
            ],
        ]);

        $response->assertSessionHasErrors(["documents.{$requiredDocument->id}"]);
    }

    public function test_student_cannot_apply_twice_to_the_same_scholarship(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)->for($student)->create();

        $response = $this->actingAs($student)->get(route('scholarships.apply', $scholarship));

        $response->assertForbidden();
    }

    public function test_student_cannot_apply_after_the_deadline_has_passed(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['deadline' => now()->subDay()]);

        $response = $this->actingAs($student)->get(route('scholarships.apply', $scholarship));

        $response->assertForbidden();
    }

    public function test_student_cannot_apply_when_no_slots_remain(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.apply', $scholarship));

        $response->assertForbidden();
    }

    public function test_admin_cannot_apply_to_a_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($admin)->get(route('scholarships.apply', $scholarship));

        $response->assertForbidden();
    }

    public function test_a_students_document_download_is_denied_to_other_students(): void
    {
        Storage::fake('supabase');
        $owner = User::factory()->create();
        $otherStudent = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $requiredDocument = $scholarship->requiredDocuments()->create(['name' => '2x2 Picture']);
        $application = ScholarshipApplication::factory()->for($scholarship)->for($owner)->create();
        $document = $application->documents()->create([
            'scholarship_required_document_id' => $requiredDocument->id,
            'disk' => 'supabase',
            'path' => 'scholarship-applications/1/picture.jpg',
            'original_name' => 'picture.jpg',
        ]);
        Storage::disk('supabase')->put($document->path, 'fake-content');

        $response = $this->actingAs($otherStudent)->get(route('scholarship-applications.documents.show', [$application, $document]));

        $response->assertForbidden();
    }

    public function test_a_students_document_download_is_allowed_for_the_owner_and_admin(): void
    {
        Storage::fake('supabase');
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        $requiredDocument = $scholarship->requiredDocuments()->create(['name' => '2x2 Picture']);
        $application = ScholarshipApplication::factory()->for($scholarship)->for($owner)->create();
        $document = $application->documents()->create([
            'scholarship_required_document_id' => $requiredDocument->id,
            'disk' => 'supabase',
            'path' => 'scholarship-applications/1/picture.jpg',
            'original_name' => 'picture.jpg',
        ]);
        Storage::disk('supabase')->put($document->path, 'fake-content');

        $this->actingAs($owner)
            ->get(route('scholarship-applications.documents.show', [$application, $document]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('scholarship-applications.documents.show', [$application, $document]))
            ->assertOk();
    }
}
