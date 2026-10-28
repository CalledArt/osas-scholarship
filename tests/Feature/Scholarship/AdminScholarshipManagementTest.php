<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipApplicationDocument;
use App\Models\ScholarshipArchive;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use App\Notifications\ScholarshipPosted;
use Database\Seeders\TestStudentsSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
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

    public function test_each_scholarship_card_gives_slots_left_applicants_and_closing_date(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Grant', 'slots' => 5, 'deadline' => '2026-11-30']);
        ScholarshipApplication::factory()->count(2)->for($scholarship)->create();
        ScholarshipApplication::factory()->rejected()->for($scholarship)->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.index'));

        $response->assertSeeInOrder([
            'Manage Scholarships', 'scholarship posted.', 'Post Scholarship',
            'Merit Grant', 'Open', 'Slots left', '3 of 5', 'Applicants', '3', 'Closes', 'Nov 30, 2026',
            route('admin.scholarships.edit', $scholarship),
        ]);
    }

    public function test_a_scholarship_left_open_past_its_deadline_says_so_on_its_card_and_page(): void
    {
        $this->travelTo('2026-10-25 10:00');
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Grant', 'deadline' => '2026-10-20']);

        $this->actingAs($admin)->get(route('admin.scholarships.index'))
            ->assertSeeInOrder(['Merit Grant', 'Open', 'Past deadline', 'Oct 20, 2026'])
            ->assertDontSee('Closes');
        $this->actingAs($admin)->get(route('admin.scholarships.show', $scholarship))
            ->assertSee('Past deadline: Oct 20, 2026');
    }

    public function test_scholarships_banner_says_none_are_posted_before_the_first(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.scholarships.index'));

        $response->assertSeeInOrder(['Manage Scholarships', 'No scholarships have been posted yet.', 'Post Scholarship']);
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

    public function test_posting_a_scholarship_notifies_every_student_in_their_bell_and_sends_no_email(): void
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
        $response->assertSessionHas('status', 'Scholarship posted and 2 students notified.');

        foreach ($students as $student) {
            $notification = $student->notifications()->sole();
            $this->assertSame(ScholarshipPosted::class, $notification->type);
            $this->assertSame(['scholarship_id' => $scholarship->id, 'title' => 'STEM Excellence Grant'], $notification->data);
            $this->assertNull($notification->read_at);
        }
        $this->assertSame(0, $admin->notifications()->count());
        Mail::assertNothingSent();
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

    public function test_scholarship_forms_no_longer_set_the_total_funds(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->funded('90000.00')->create();

        $this->actingAs($admin)->get(route('admin.scholarships.edit', $scholarship))->assertDontSee('name="total_funds"', false);
        $this->actingAs($admin)->get(route('admin.scholarships.create'))->assertDontSee('name="total_funds"', false);
        $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
            'title' => $scholarship->title,
            'type' => 'government',
            'description' => $scholarship->description,
            'slots' => $scholarship->slots,
            'total_funds' => '150000.50',
            'status' => 'open',
        ])->assertSessionHasNoErrors();

        $this->assertSame('90000.00', $scholarship->fresh()->total_funds);
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

    public function test_deleting_a_scholarship_with_a_completed_fund_allocation_archives_it_with_each_recipients_share(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->ended()->funded('100000.01')->create(['title' => 'CHED Merit Grant', 'slots' => 5]);
        foreach ([['Juan', 'Santos', '202212345'], ['Ana', 'Cruz', '202254321']] as [$firstName, $lastName, $studentNumber]) {
            ScholarshipApplication::factory()->approved()->for($scholarship)
                ->for(User::factory()->named($firstName, null, $lastName)->state(['student_number' => $studentNumber, 'course' => 'Bachelor of Science in Biology']))
                ->create();
        }
        ScholarshipApplication::factory()->for($scholarship)->create();
        ScholarshipApplication::factory()->rejected()->for($scholarship)->create();

        $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

        $this->assertModelMissing($scholarship);
        $archive = ScholarshipArchive::sole();
        $this->assertSame($scholarship->reference, $archive->scholarship_reference);
        $this->assertSame([$scholarship->id, 'CHED Merit Grant', ScholarshipStatus::Ended, '100000.01', '50000.00', 2, $admin->id, $scholarship->created_by], [
            $archive->scholarship_id, $archive->title, $archive->status, $archive->total_funds, $archive->share_amount, $archive->recipient_count, $archive->deleted_by, $archive->posted_by,
        ]);
        $this->assertSame([
            ['Ana Cruz', '202254321', 'Bachelor of Science in Biology', '50000.00'],
            ['Juan Santos', '202212345', 'Bachelor of Science in Biology', '50000.00'],
        ], $archive->recipients()->orderBy('id')->get()->map(fn ($recipient): array => [
            $recipient->name, $recipient->student_number, $recipient->course, $recipient->amount,
        ])->all());
    }

    public function test_deleting_a_scholarship_with_no_fund_total_keeps_no_archive(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();

        $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

        $this->assertModelMissing($scholarship);
        $this->assertDatabaseCount('scholarship_archives', 0);
    }

    public function test_deleting_a_funded_scholarship_nobody_passed_keeps_no_archive(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->funded()->create();
        ScholarshipApplication::factory()->for($scholarship)->create();

        $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

        $this->assertModelMissing($scholarship);
        $this->assertDatabaseCount('scholarship_archives', 0);
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

    #[TestWith(['dost', true])]
    #[TestWith(['tdp', false])]
    #[TestWith([null, false])]
    public function test_only_the_dost_preset_limits_a_posted_scholarship_to_stem_courses(?string $preset, bool $stemCoursesOnly): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'preset' => $preset,
            'title' => 'Science Grant',
            'type' => 'government',
            'stem_courses_only' => '1',
            'description' => 'For science majors.',
            'slots' => 5,
        ])->assertSessionHasNoErrors();

        $this->assertSame($stemCoursesOnly, Scholarship::sole()->stem_courses_only);
    }

    public function test_posting_a_scholarship_rejects_an_unknown_preset(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'preset' => 'stem',
            'title' => 'Science Grant',
            'type' => 'government',
            'description' => 'For science majors.',
            'slots' => 5,
        ]);

        $response->assertSessionHasErrors(['preset' => 'The selected preset is invalid.']);
        $this->assertDatabaseCount('scholarships', 0);
    }

    public function test_post_form_shows_the_stem_course_limit_only_for_the_dost_preset(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.scholarships.create'));

        $response->assertSee('name="preset"', false);
        $response->assertSee('x-show="presets[preset]?.stem_courses_only"', false);
        $response->assertDontSee('name="stem_courses_only"', false);
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

    public function test_posting_a_stem_only_scholarship_notifies_only_students_in_stem_courses(): void
    {
        $admin = User::factory()->admin()->create();
        $stemStudent = User::factory()->create(['course' => 'BS Computer Science']);
        $otherStudent = User::factory()->create(['course' => 'BS Accountancy']);

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'preset' => 'dost',
            'title' => 'DOST Scholarship',
            'type' => 'government',
            'description' => 'For S&T priority courses.',
            'slots' => 5,
        ]);

        $response->assertSessionHas('status', 'Scholarship posted and 1 student notified.');
        $this->assertSame(1, $stemStudent->notifications()->count());
        $this->assertSame(0, $otherStudent->notifications()->count());
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_editing_a_scholarship_never_changes_its_course_limit(bool $stemCoursesOnly): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['stem_courses_only' => $stemCoursesOnly]);

        $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
            'title' => $scholarship->title,
            'type' => 'government',
            'stem_courses_only' => $stemCoursesOnly ? '0' : '1',
            'description' => $scholarship->description,
            'slots' => $scholarship->slots,
            'status' => 'open',
        ])->assertSessionHasNoErrors();

        $this->assertSame($stemCoursesOnly, $scholarship->fresh()->stem_courses_only);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_edit_form_shows_the_course_limit_only_for_a_dost_scholarship_and_never_as_a_checkbox(bool $stemCoursesOnly): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['stem_courses_only' => $stemCoursesOnly]);

        $response = $this->actingAs($admin)->get(route('admin.scholarships.edit', $scholarship));

        $response->assertOk();
        $stemCoursesOnly
            ? $response->assertSee('Posted as the DOST Scholarship, so only students in a STEM course can apply.')
            : $response->assertDontSee('STEM courses only');
        $response->assertDontSee('name="stem_courses_only"', false);
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

    public function test_the_applicant_list_says_why_an_application_was_voided(): void
    {
        $application = ScholarshipApplication::factory()->create(['status' => ApplicationStatus::Voided]);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.scholarships.show', $application->scholarship));

        $response->assertSeeInOrder(['Voided', 'Voided when the student went on leave of absence (LOA); the slot was freed.']);
    }

    public function test_posting_from_a_preset_records_the_preset(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.scholarships.store'), $this->tdpPosting());

        $this->assertSame('tdp', Scholarship::sole()->preset);
    }

    public function test_a_preset_cannot_be_posted_while_a_posting_of_it_is_open(): void
    {
        $admin = User::factory()->admin()->create();
        $open = Scholarship::factory()->fromPreset('tdp')->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), $this->tdpPosting());

        $response->assertSessionHasErrors(['preset' => 'A Tulong Dunong Program (TDP) posting is already open for applications. Close it before posting another one.']);
        $this->assertSame([$open->id], Scholarship::pluck('id')->all());
    }

    public function test_a_preset_can_be_posted_again_once_its_posting_is_closed(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->fromPreset('tdp')->closed()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), $this->tdpPosting());

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, Scholarship::where('preset', 'tdp')->count());
    }

    public function test_two_posts_of_one_preset_sent_at_the_same_moment_land_only_once(): void
    {
        $admin = User::factory()->admin()->create();
        // Stands in for the other post: it lands just after this one passed
        // validation. Its title differs so the title check can't catch it
        // first, leaving the lock as the only thing that can.
        $otherPostLanded = false;
        DB::listen(function (QueryExecuted $query) use (&$otherPostLanded): void {
            if (! $otherPostLanded && str_contains($query->sql, '"preset" = ?')) {
                $otherPostLanded = true;
                Scholarship::factory()->fromPreset('tdp')->create(['title' => 'TDP posted at the same moment']);
            }
        });

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), $this->tdpPosting());

        $response->assertSessionHasErrors(['preset' => 'A Tulong Dunong Program (TDP) posting is already open for applications. Close it before posting another one.']);
        $this->assertSame(1, Scholarship::count());
    }

    public function test_the_post_form_marks_a_preset_that_is_already_open(): void
    {
        Scholarship::factory()->fromPreset('tdp')->create();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.scholarships.create'));

        $response->assertSee('<option value="tdp" disabled>Tulong Dunong Program (TDP) (already open)</option>', false);
        $response->assertSee('<option value="tes" >Tertiary Education Subsidy (TES)</option>', false);
    }

    public function test_a_posting_cannot_be_reopened_while_another_of_its_preset_is_open(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->fromPreset('tdp')->create();
        $closed = Scholarship::factory()->fromPreset('tdp')->closed()->create();

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $closed), [...$this->tdpPosting(), 'status' => 'open']);

        $response->assertSessionHasErrors(['status' => 'Another Tulong Dunong Program (TDP) posting is already open for applications. Close it before reopening this one.']);
        $this->assertSame(ScholarshipStatus::Closed, $closed->fresh()->status);
    }

    public function test_an_open_posting_stays_editable_beside_an_older_open_duplicate_of_its_preset(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->fromPreset('tdp')->create();
        $duplicate = Scholarship::factory()->fromPreset('tdp')->create();

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $duplicate), [...$this->tdpPosting(), 'title' => 'TDP 2026', 'status' => 'open']);

        $response->assertSessionHasNoErrors();
        $this->assertSame('TDP 2026', $duplicate->fresh()->title);
    }

    public function test_a_scholarship_cannot_be_posted_while_one_with_the_same_title_is_open(): void
    {
        $admin = User::factory()->admin()->create();
        $open = Scholarship::factory()->create(['title' => 'CHED Merit Grant']);

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [...$this->tdpPosting(), 'preset' => null, 'title' => 'ched   MERIT grant']);

        $response->assertSessionHasErrors(['title' => 'A scholarship titled “ched MERIT grant” is still open for applications. Close it before posting it again.']);
        $this->assertSame([$open->id], Scholarship::pluck('id')->all());
    }

    public function test_a_title_can_be_posted_again_once_its_posting_is_closed(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->closed()->create(['title' => 'CHED Merit Grant']);

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [...$this->tdpPosting(), 'preset' => null, 'title' => 'CHED Merit Grant']);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, Scholarship::where('title', 'CHED Merit Grant')->count());
    }

    public function test_a_posting_cannot_be_renamed_to_the_title_of_another_open_posting(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->create(['title' => 'CHED Merit Grant']);
        $other = Scholarship::factory()->create(['title' => 'Other Grant']);

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $other), [...$this->tdpPosting(), 'title' => 'CHED Merit Grant', 'status' => 'open']);

        $response->assertSessionHasErrors(['title' => 'Another scholarship titled “CHED Merit Grant” is still open for applications. Close it first, or give this one a different title.']);
        $this->assertSame('Other Grant', $other->fresh()->title);
    }

    public function test_a_posting_cannot_be_reopened_while_another_with_its_title_is_open(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->create(['title' => 'CHED Merit Grant']);
        $closed = Scholarship::factory()->closed()->create(['title' => 'CHED Merit Grant']);

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $closed), [...$this->tdpPosting(), 'title' => 'CHED Merit Grant', 'status' => 'open']);

        $response->assertSessionHasErrors('title');
        $this->assertSame(ScholarshipStatus::Closed, $closed->fresh()->status);
    }

    public function test_an_open_posting_stays_editable_beside_an_older_open_posting_with_its_title(): void
    {
        $admin = User::factory()->admin()->create();
        Scholarship::factory()->create(['title' => 'CHED Merit Grant']);
        $duplicate = Scholarship::factory()->create(['title' => 'CHED Merit Grant']);

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $duplicate), [...$this->tdpPosting(), 'title' => 'CHED Merit Grant', 'slots' => 9, 'status' => 'open']);

        $response->assertSessionHasNoErrors();
        $this->assertSame(9, $duplicate->fresh()->slots);
    }

    public function test_on_a_local_machine_the_test_students_apply_to_a_posted_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seed(TestStudentsSeeder::class);
        $this->app['env'] = 'local';
        $this->withoutMiddleware(PreventRequestForgery::class);

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [...$this->tdpPosting(), 'slots' => 30]);

        $response->assertSessionHas('status', 'Scholarship posted and 20 students notified. 18 test students applied (local machine only).');
        $this->assertSame(18, Scholarship::sole()->applications()->count());
    }

    public function test_the_test_students_never_apply_on_the_live_site(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seed(TestStudentsSeeder::class);

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [...$this->tdpPosting(), 'slots' => 30]);

        $response->assertSessionHas('status', 'Scholarship posted and 20 students notified.');
        $this->assertSame(0, Scholarship::sole()->applications()->count());
    }

    /**
     * A Post Scholarship form filled in from the TDP preset.
     *
     * @return array<string, mixed>
     */
    private function tdpPosting(): array
    {
        return [
            'preset' => 'tdp',
            'title' => 'Tulong Dunong Program (TDP)',
            'type' => 'government',
            'description' => 'Financial assistance for qualified students.',
            'slots' => 5,
        ];
    }
}
