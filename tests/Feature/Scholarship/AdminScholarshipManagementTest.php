<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ScholarshipType;
use App\Mail\ScholarshipPosted;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipApplicationDocument;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AdminScholarshipManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_viewing_admin_scholarships(): void
    {
        $response = $this->get(route('admin.scholarships.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_is_forbidden_from_viewing_admin_scholarships(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.scholarships.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_scholarship_with_required_documents(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'type' => 'government',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
            'deadline' => now()->addMonth()->format('Y-m-d'),
            'required_documents' => [
                ['name' => '2x2 Picture', 'description' => 'White background'],
                ['name' => 'Certificate of Registration'],
            ],
        ]);

        $scholarship = Scholarship::sole();
        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $this->assertSame($admin->id, $scholarship->created_by);
        $this->assertSame(2, $scholarship->requiredDocuments()->count());
        $this->assertTrue($scholarship->requiredDocuments->pluck('name')->contains('2x2 Picture'));
    }

    public function test_posting_a_scholarship_emails_every_student_but_not_staff(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $students = User::factory()->count(2)->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'type' => 'government',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
        ]);

        $scholarship = Scholarship::sole();
        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $response->assertSessionHas('status', 'Scholarship posted and emailed to 2 students.');
        Mail::assertSent(ScholarshipPosted::class, fn (ScholarshipPosted $mail) => $mail->scholarship->is($scholarship)
            && $mail->hasBcc($students[0]->email)
            && $mail->hasBcc($students[1]->email)
            && ! $mail->hasBcc($admin->email));
    }

    public function test_a_scholarship_stays_posted_when_the_announcement_email_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new TransportException('Connection refused.'));
        $admin = User::factory()->admin()->create();
        User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'type' => 'government',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
        ]);

        $scholarship = Scholarship::sole();
        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $response->assertSessionHas('status', 'Scholarship posted.');
        $response->assertSessionHas('error', 'The announcement email reached only 0 of 1 students. Check the mail settings and the application log.');
    }

    public function test_student_cannot_create_scholarship(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'type' => 'government',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('scholarships', 0);
    }

    public function test_creating_scholarship_requires_title_description_type_and_slots(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), []);

        $response->assertSessionHasErrors(['title', 'description', 'type', 'slots']);
    }

    public function test_admin_can_update_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['title' => 'Old Title']);

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
            'title' => 'New Title',
            'type' => 'government',
            'description' => $scholarship->description,
            'slots' => $scholarship->slots,
            'status' => 'closed',
        ]);

        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $this->assertSame('New Title', $scholarship->fresh()->title);
        $this->assertSame('closed', $scholarship->fresh()->status->value);
    }

    public function test_admin_can_mark_a_scholarship_as_ended(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->closed()->create();

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
            'title' => $scholarship->title,
            'type' => 'government',
            'description' => $scholarship->description,
            'slots' => $scholarship->slots,
            'status' => 'ended',
        ]);

        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $this->assertSame('ended', $scholarship->fresh()->status->value);
    }

    public function test_admin_can_delete_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();

        $response = $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

        $response->assertRedirect(route('admin.scholarships.index'));
        $this->assertModelMissing($scholarship);
    }

    public function test_deleting_a_scholarship_removes_its_applicants_uploads_and_samples_from_storage(): void
    {
        Storage::fake('supabase');
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        $requiredDocument = ScholarshipRequiredDocument::factory()->for($scholarship)->withSample()->create();
        $upload = ScholarshipApplicationDocument::factory()
            ->for(ScholarshipApplication::factory()->for($scholarship), 'application')
            ->for($requiredDocument, 'requiredDocument')
            ->create();
        $otherScholarshipsUpload = ScholarshipApplicationDocument::factory()->create();
        Storage::disk('supabase')->put($requiredDocument->sample_path, 'sample');
        Storage::disk('supabase')->put($upload->path, 'upload');
        Storage::disk('supabase')->put($otherScholarshipsUpload->path, 'other upload');

        $response = $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

        $response->assertRedirect(route('admin.scholarships.index'));
        $this->assertModelMissing($scholarship);
        $this->assertModelMissing($upload);
        Storage::disk('supabase')->assertMissing($upload->path);
        Storage::disk('supabase')->assertMissing($requiredDocument->sample_path);
        Storage::disk('supabase')->assertExists($otherScholarshipsUpload->path);
    }

    public function test_student_cannot_delete_a_scholarship(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->delete(route('admin.scholarships.destroy', $scholarship));

        $response->assertForbidden();
        $this->assertModelExists($scholarship);
    }

    public function test_delete_confirmation_states_how_many_applications_will_be_lost(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.edit', $scholarship));

        $response->assertSee(route('admin.scholarships.destroy', $scholarship));
        $response->assertSee('This also deletes 2 student applications and every document uploaded with them.');
    }

    public function test_delete_confirmation_says_when_no_one_has_applied(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.edit', $scholarship));

        $response->assertSee('No students have applied yet.');
    }

    public function test_admin_can_add_and_remove_a_required_document(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();

        $this->actingAs($admin)->post(route('admin.scholarships.required-documents.store', $scholarship), [
            'name' => 'Income Tax Return',
        ])->assertRedirect(route('admin.scholarships.show', $scholarship));

        $document = ScholarshipRequiredDocument::sole();
        $this->assertSame('Income Tax Return', $document->name);

        $this->actingAs($admin)
            ->delete(route('admin.scholarships.required-documents.destroy', [$scholarship, $document]))
            ->assertRedirect(route('admin.scholarships.show', $scholarship));

        $this->assertModelMissing($document);
    }

    public function test_post_form_offers_the_three_presets(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.create'));

        $response->assertSeeInOrder(['Start from a preset', 'Tulong Dunong Program (TDP)', 'Tertiary Education Subsidy (TES)', 'DOST Scholarship']);
    }

    public function test_admin_can_post_an_academic_scholarship_limited_to_stem_courses(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'Dean\'s Science Grant',
            'type' => 'academic',
            'stem_courses_only' => '1',
            'description' => 'For science majors.',
            'slots' => 5,
        ]);

        $scholarship = Scholarship::sole();
        $this->assertSame(ScholarshipType::Academic, $scholarship->type);
        $this->assertTrue($scholarship->stem_courses_only);
    }

    public function test_posting_a_scholarship_rejects_an_unknown_type(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'Mystery Grant',
            'type' => 'private',
            'description' => 'Unknown sponsor.',
            'slots' => 5,
        ]);

        $response->assertSessionHasErrors(['type' => 'The selected type is invalid.']);
        $this->assertDatabaseCount('scholarships', 0);
    }

    public function test_posting_a_stem_only_scholarship_emails_only_students_in_stem_courses(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $stemStudent = User::factory()->create(['course' => 'BS Computer Science']);
        $otherStudent = User::factory()->create(['course' => 'BS Accountancy']);

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'DOST Scholarship',
            'type' => 'government',
            'stem_courses_only' => '1',
            'description' => 'For S&T priority courses.',
            'slots' => 5,
        ]);

        $response->assertSessionHas('status', 'Scholarship posted and emailed to 1 student.');
        Mail::assertSent(ScholarshipPosted::class, fn (ScholarshipPosted $mail) => $mail->hasBcc($stemStudent->email)
            && ! $mail->hasBcc($otherStudent->email));
    }

    public function test_unticking_stem_courses_only_opens_the_scholarship_to_every_course(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->stemCoursesOnly()->create();

        $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
            'title' => $scholarship->title,
            'type' => 'government',
            'description' => $scholarship->description,
            'slots' => $scholarship->slots,
            'status' => 'open',
        ]);

        $this->assertFalse($scholarship->fresh()->stem_courses_only);
    }

    public function test_the_applicant_list_does_not_show_personal_details(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create([
            'date_of_birth' => now()->subYears(20)->format('Y-m-d'),
            'contact_number' => '09171234567',
            'address' => '123 Mabini Street, Indang, Cavite',
        ]);
        $application = ScholarshipApplication::factory()->for($student)->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.show', $application->scholarship));

        $response->assertSee($student->email);
        $response->assertDontSee('years old');
        $response->assertDontSee('09171234567');
        $response->assertDontSee('123 Mabini Street, Indang, Cavite');
    }
}
