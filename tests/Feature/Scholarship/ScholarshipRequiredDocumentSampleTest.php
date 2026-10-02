<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScholarshipRequiredDocumentSampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_required_document_with_a_sample(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.required-documents.store', $scholarship), [
            'name' => 'PSA Birth Certificate',
            'sample' => UploadedFile::fake()->create('psa-sample.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $document = $scholarship->requiredDocuments()->sole();
        $this->assertSame('PSA Birth Certificate', $document->name);
        $this->assertSame('psa-sample.pdf', $document->sample_original_name);
        Storage::disk('supabase')->assertExists($document->sample_path);
    }

    public function test_adding_a_required_document_rejects_a_sample_over_5mb(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.required-documents.store', $scholarship), [
            'name' => 'PSA Birth Certificate',
            'sample' => UploadedFile::fake()->create('psa-sample.pdf', 6000, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors(['sample' => 'The sample field must not be greater than 5120 kilobytes.']);
        $this->assertDatabaseCount('scholarship_required_documents', 0);
    }

    public function test_admin_can_post_a_scholarship_with_a_sample_for_one_of_its_required_documents(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
            'required_documents' => [
                ['name' => '2x2 Picture', 'sample' => UploadedFile::fake()->create('picture-sample.jpg', 100, 'image/jpeg')],
                ['name' => 'Certificate of Registration'],
            ],
        ]);

        $scholarship = Scholarship::sole();
        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $picture = $scholarship->requiredDocuments()->where('name', '2x2 Picture')->sole();
        $this->assertSame('picture-sample.jpg', $picture->sample_original_name);
        Storage::disk('supabase')->assertExists($picture->sample_path);
        $registration = $scholarship->requiredDocuments()->where('name', 'Certificate of Registration')->sole();
        $this->assertFalse($registration->hasSample());
    }

    public function test_posting_a_scholarship_rejects_a_sample_that_is_not_an_image_or_pdf(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
            'required_documents' => [
                ['name' => 'Letter of Intent', 'sample' => UploadedFile::fake()->create('letter.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
            ],
        ]);

        $response->assertSessionHasErrors(['required_documents.0.sample' => 'The sample field must be a file of type: jpg, jpeg, png, pdf.']);
        $this->assertDatabaseCount('scholarships', 0);
    }

    public function test_admin_can_upload_a_sample_to_an_existing_required_document(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.scholarships.required-documents.sample.update', [$document->scholarship_id, $document]), [
            'sample' => UploadedFile::fake()->create('itr-sample.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.scholarships.edit', $document->scholarship_id));
        $document->refresh();
        $this->assertSame('itr-sample.pdf', $document->sample_original_name);
        Storage::disk('supabase')->assertExists($document->sample_path);
    }

    public function test_replacing_a_sample_deletes_the_previous_file(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();
        $previousPath = $document->sample_path;
        Storage::disk('supabase')->put($previousPath, 'old-sample');

        $this->actingAs($admin)->put(route('admin.scholarships.required-documents.sample.update', [$document->scholarship_id, $document]), [
            'sample' => UploadedFile::fake()->create('new-sample.pdf', 100, 'application/pdf'),
        ]);

        $document->refresh();
        $this->assertSame('new-sample.pdf', $document->sample_original_name);
        Storage::disk('supabase')->assertExists($document->sample_path);
        Storage::disk('supabase')->assertMissing($previousPath);
    }

    public function test_uploading_a_sample_requires_a_file(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.scholarships.required-documents.sample.update', [$document->scholarship_id, $document]), []);

        $response->assertSessionHasErrors(['sample' => 'The sample field is required.']);
        $this->assertFalse($document->fresh()->hasSample());
    }

    public function test_uploading_a_sample_through_another_scholarship_returns_404(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->create();
        $otherScholarship = Scholarship::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.scholarships.required-documents.sample.update', [$otherScholarship, $document]), [
            'sample' => UploadedFile::fake()->create('itr-sample.pdf', 100, 'application/pdf'),
        ]);

        $response->assertNotFound();
        $this->assertFalse($document->fresh()->hasSample());
    }

    public function test_student_cannot_upload_a_sample(): void
    {
        Storage::fake('supabase');
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->put(route('admin.scholarships.required-documents.sample.update', [$document->scholarship_id, $document]), [
            'sample' => UploadedFile::fake()->create('itr-sample.pdf', 100, 'application/pdf'),
        ]);

        $response->assertForbidden();
        $this->assertFalse($document->fresh()->hasSample());
    }

    public function test_admin_can_remove_a_sample_and_its_file(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();
        $samplePath = $document->sample_path;
        Storage::disk('supabase')->put($samplePath, 'sample');

        $response = $this->actingAs($admin)->delete(route('admin.scholarships.required-documents.sample.destroy', [$document->scholarship_id, $document]));

        $response->assertRedirect(route('admin.scholarships.edit', $document->scholarship_id));
        $document->refresh();
        $this->assertNull($document->sample_path);
        $this->assertNull($document->sample_original_name);
        Storage::disk('supabase')->assertMissing($samplePath);
    }

    public function test_removing_a_required_document_deletes_its_sample_file(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();
        Storage::disk('supabase')->put($document->sample_path, 'sample');

        $this->actingAs($admin)->delete(route('admin.scholarships.required-documents.destroy', [$document->scholarship_id, $document]));

        $this->assertModelMissing($document);
        Storage::disk('supabase')->assertMissing($document->sample_path);
    }

    public function test_student_can_view_a_posted_sample_inline(): void
    {
        Storage::fake('supabase');
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();
        Storage::disk('supabase')->put($document->sample_path, 'sample-content');

        $response = $this->actingAs($student)->get(route('scholarships.required-documents.sample', [$document->scholarship_id, $document]));

        $response->assertStreamedContent('sample-content');
        $response->assertHeader('content-disposition', 'inline; filename=sample.pdf');
    }

    public function test_student_can_download_a_copy_of_a_posted_sample(): void
    {
        Storage::fake('supabase');
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();
        Storage::disk('supabase')->put($document->sample_path, 'sample-content');

        $response = $this->actingAs($student)->get(route('scholarships.required-documents.sample.download', [$document->scholarship_id, $document]));

        $response->assertDownload('sample.pdf');
        $response->assertStreamedContent('sample-content');
    }

    public function test_downloading_a_sample_returns_404_when_none_is_posted(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.required-documents.sample.download', [$document->scholarship_id, $document]));

        $response->assertNotFound();
    }

    public function test_viewing_a_sample_redirects_guests_to_login(): void
    {
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();

        $response = $this->get(route('scholarships.required-documents.sample', [$document->scholarship_id, $document]));

        $response->assertRedirect(route('login'));
    }

    public function test_viewing_a_sample_returns_404_when_none_is_posted(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.required-documents.sample', [$document->scholarship_id, $document]));

        $response->assertNotFound();
    }

    public function test_viewing_a_sample_through_another_scholarship_returns_404(): void
    {
        Storage::fake('supabase');
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();
        Storage::disk('supabase')->put($document->sample_path, 'sample-content');
        $otherScholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.required-documents.sample', [$otherScholarship, $document]));

        $response->assertNotFound();
    }

    public function test_edit_page_offers_to_view_replace_or_remove_a_posted_sample(): void
    {
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.edit', $document->scholarship_id));

        $response->assertSee(route('scholarships.required-documents.sample', [$document->scholarship_id, $document]));
        $response->assertSee('Replace sample');
        $response->assertSee('Remove sample');
        $response->assertDontSee('Upload sample');
    }

    public function test_edit_page_offers_to_upload_a_sample_when_none_is_posted(): void
    {
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.edit', $document->scholarship_id));

        $response->assertSee('Upload sample');
        $response->assertSee(route('admin.scholarships.required-documents.sample.update', [$document->scholarship_id, $document]));
        $response->assertDontSee('Remove sample');
        $response->assertDontSee(route('scholarships.required-documents.sample', [$document->scholarship_id, $document]));
    }

    public function test_scholarship_page_previews_only_the_documents_that_have_a_sample(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $withSample = ScholarshipRequiredDocument::factory()->for($scholarship)->withSample()->create();
        $withoutSample = ScholarshipRequiredDocument::factory()->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee(route('scholarships.required-documents.sample', [$scholarship, $withSample]));
        $response->assertSee(route('scholarships.required-documents.sample.download', [$scholarship, $withSample]));
        $response->assertDontSee(route('scholarships.required-documents.sample', [$scholarship, $withoutSample]));
    }

    public function test_preview_shows_a_picture_sample_as_an_image_and_a_pdf_sample_in_a_viewer(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $picture = ScholarshipRequiredDocument::factory()->for($scholarship)->withSample()->create(['sample_path' => 'scholarship-samples/1/picture.jpg']);
        $pdf = ScholarshipRequiredDocument::factory()->for($scholarship)->withSample()->create(['sample_path' => 'scholarship-samples/1/certificate.pdf']);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('<img src="'.route('scholarships.required-documents.sample', [$scholarship, $picture]).'"', false);
        $response->assertSee('<object data="'.route('scholarships.required-documents.sample', [$scholarship, $pdf]), false);
    }

    public function test_application_form_previews_the_sample_next_to_its_upload_field(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->for($scholarship)->withSample()->create(['sample_path' => 'scholarship-samples/1/picture.jpg']);

        $response = $this->actingAs($student)->get(route('scholarships.apply', $scholarship));

        $response->assertSeeInOrder([
            'name="documents['.$document->id.']"',
            '<img src="'.route('scholarships.required-documents.sample', [$scholarship, $document]).'"',
        ], false);
    }

    public function test_admin_scholarship_page_previews_a_posted_sample(): void
    {
        $admin = User::factory()->admin()->create();
        $document = ScholarshipRequiredDocument::factory()->withSample()->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.show', $document->scholarship_id));

        $response->assertSee('<object data="'.route('scholarships.required-documents.sample', [$document->scholarship_id, $document]), false);
    }
}
