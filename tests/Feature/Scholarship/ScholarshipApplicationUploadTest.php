<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use App\Support\StagedUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScholarshipApplicationUploadTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n1 0 obj\n<< /Type /Catalog >>\nendobj\n";

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(StagedUpload::DISK);
    }

    public function test_upload_link_is_issued_for_a_document_of_a_scholarship_the_student_can_apply_to(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->postJson(route('scholarships.apply.uploads.store', $document->scholarship), [
            'required_document_id' => $document->id,
            'name' => 'certificate-of-registration.pdf',
            'size' => 120_000,
        ]);

        $response->assertOk()->assertJsonStructure(['url', 'headers', 'token']);
        $upload = StagedUpload::fromToken($response->json('token'));
        $this->assertTrue($upload->isFor($student, $document));
        $this->assertStringStartsWith(StagedUpload::PREFIX."/{$student->id}/", $upload->path);
        $this->assertStringEndsWith('.pdf', $upload->path);
        $this->assertSame('certificate-of-registration.pdf', $upload->originalName);
    }

    public function test_upload_link_is_refused_for_a_document_of_another_scholarship(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $otherDocument = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->postJson(route('scholarships.apply.uploads.store', $scholarship), [
            'required_document_id' => $otherDocument->id,
            'name' => 'scan.pdf',
            'size' => 1000,
        ]);

        $response->assertJsonValidationErrors('required_document_id');
    }

    /**
     * @return array<string, array{string, int, string}>
     */
    public static function refusedFiles(): array
    {
        return [
            'over 5 MB' => ['scan.pdf', StagedUpload::MAX_BYTES + 1, 'size'],
            'empty' => ['scan.pdf', 0, 'size'],
            'not a JPG, PNG or PDF' => ['scan.docx', 1000, 'name'],
        ];
    }

    #[DataProvider('refusedFiles')]
    public function test_upload_link_is_refused_for_a_file_the_form_would_not_accept(string $name, int $size, string $field): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->postJson(route('scholarships.apply.uploads.store', $document->scholarship), [
            'required_document_id' => $document->id,
            'name' => $name,
            'size' => $size,
        ]);

        $response->assertJsonValidationErrors($field);
    }

    public function test_upload_link_is_forbidden_once_the_scholarship_stops_taking_applications(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->for(Scholarship::factory()->closed())->create();

        $response = $this->actingAs($student)->postJson(route('scholarships.apply.uploads.store', $document->scholarship), [
            'required_document_id' => $document->id,
            'name' => 'scan.pdf',
            'size' => 1000,
        ]);

        $response->assertForbidden();
    }

    public function test_student_applies_with_documents_sent_straight_to_storage(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();
        $upload = $this->stage($student, $document, 'cor.pdf', self::PDF);

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $document->scholarship), [
            'uploads' => [$document->id => $upload->token()],
            'certification' => '1',
        ]);

        $application = ScholarshipApplication::query()->whereBelongsTo($student)->sole();
        $response->assertRedirect(route('scholarship-applications.show', $application));
        $stored = $application->documents()->sole();
        $this->assertSame('cor.pdf', $stored->original_name);
        $this->assertStringStartsWith("scholarship-applications/{$application->id}/", $stored->path);
        $this->assertStringEndsWith('.pdf', $stored->path);
        Storage::disk(StagedUpload::DISK)->assertExists($stored->path);
        Storage::disk(StagedUpload::DISK)->assertMissing($upload->path);
    }

    public function test_an_upload_made_by_another_student_is_refused(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();
        $othersUpload = $this->stage(User::factory()->create(), $document, 'cor.pdf', self::PDF);

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $document->scholarship), [
            'uploads' => [$document->id => $othersUpload->token()],
        ]);

        $response->assertSessionHasErrors("uploads.{$document->id}");
        $this->assertDatabaseCount('scholarship_applications', 0);
        Storage::disk(StagedUpload::DISK)->assertExists($othersUpload->path);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function refusedStagedContents(): array
    {
        return [
            'not really a JPG, PNG or PDF' => ['This is a plain text file renamed to .pdf'],
            'over 5 MB' => [self::PDF.str_repeat('0', StagedUpload::MAX_BYTES)],
        ];
    }

    #[DataProvider('refusedStagedContents')]
    public function test_a_staged_file_is_checked_before_the_application_accepts_it(string $contents): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();
        $upload = $this->stage($student, $document, 'cor.pdf', $contents);

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $document->scholarship), [
            'uploads' => [$document->id => $upload->token()],
        ]);

        $response->assertSessionHasErrors("uploads.{$document->id}");
        $this->assertDatabaseCount('scholarship_applications', 0);
    }

    public function test_an_upload_older_than_its_token_is_refused(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create(['scholarship_id' => Scholarship::factory()->create(['deadline' => now()->addMonth()])]);
        $upload = $this->stage($student, $document, 'cor.pdf', self::PDF);

        $this->travel(3)->hours();
        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $document->scholarship), [
            'uploads' => [$document->id => $upload->token()],
        ]);

        $response->assertSessionHasErrors("uploads.{$document->id}");
    }

    public function test_without_javascript_the_form_still_accepts_an_ordinary_file_upload(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $this->actingAs($student)->post(route('scholarships.apply.store', $document->scholarship), [
            'documents' => [$document->id => UploadedFile::fake()->create('cor.pdf', 120, 'application/pdf')],
            'certification' => '1',
        ]);

        $stored = ScholarshipApplication::query()->whereBelongsTo($student)->sole()->documents()->sole();
        $this->assertSame('cor.pdf', $stored->original_name);
        Storage::disk(StagedUpload::DISK)->assertExists($stored->path);
    }

    public function test_application_without_a_file_or_an_upload_for_a_document_is_refused(): void
    {
        $student = User::factory()->create();
        $document = ScholarshipRequiredDocument::factory()->create();

        $response = $this->actingAs($student)->post(route('scholarships.apply.store', $document->scholarship), []);

        $response->assertSessionHasErrors(["documents.{$document->id}", "uploads.{$document->id}"]);
        $this->assertDatabaseCount('scholarship_applications', 0);
    }

    public function test_pruning_deletes_staged_uploads_older_than_a_day_and_keeps_everything_else(): void
    {
        $disk = Storage::disk(StagedUpload::DISK);
        $disk->put(StagedUpload::PREFIX.'/1/abandoned.pdf', self::PDF);
        $disk->put(StagedUpload::PREFIX.'/1/recent.pdf', self::PDF);
        $disk->put('scholarship-applications/9/submitted.pdf', self::PDF);
        touch($disk->path(StagedUpload::PREFIX.'/1/abandoned.pdf'), now()->subHours(25)->getTimestamp());
        touch($disk->path('scholarship-applications/9/submitted.pdf'), now()->subHours(25)->getTimestamp());

        $this->artisan('uploads:prune-staged')->assertSuccessful();

        $disk->assertMissing(StagedUpload::PREFIX.'/1/abandoned.pdf');
        $disk->assertExists(StagedUpload::PREFIX.'/1/recent.pdf');
        $disk->assertExists('scholarship-applications/9/submitted.pdf');
    }

    /**
     * Stand in for the browser's PUT: store the file where the upload link points.
     */
    private function stage(User $student, ScholarshipRequiredDocument $document, string $name, string $contents): StagedUpload
    {
        $upload = StagedUpload::begin($student, $document, $name);
        Storage::disk(StagedUpload::DISK)->put($upload->path, $contents);

        return $upload;
    }
}
