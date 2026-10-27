<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScholarshipBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('scholarships.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_is_forbidden_from_the_student_scholarships_list(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('scholarships.index'));

        $response->assertForbidden();
    }

    public function test_student_can_view_the_scholarships_list(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Scholarship']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertOk();
        $response->assertSee('Merit Scholarship');
    }

    public function test_student_can_view_a_scholarships_details(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertOk();
        $response->assertSee($scholarship->title);
        $response->assertSee(route('scholarships.apply', $scholarship));
    }

    public function test_scholarship_details_give_the_date_applications_close(): void
    {
        $this->travelTo('2026-10-16');
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['deadline' => '2026-11-08']);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('Applications close on November 8, 2026.');
    }

    public function test_scholarship_details_link_a_student_who_applied_to_their_application_instead_of_offering_to_apply(): void
    {
        $student = User::factory()->create();
        $application = ScholarshipApplication::factory()->for($student)->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $application->scholarship));

        $response->assertSee('You applied.');
        $response->assertSee(route('scholarship-applications.show', $application));
        $response->assertDontSee(route('scholarships.apply', $application->scholarship));
    }

    public function test_full_scholarship_details_say_it_is_full_and_offer_nothing_to_apply_to(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSeeInOrder(['Full', 'This scholarship is no longer accepting applications.']);
        $response->assertDontSee('slots left');
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    public function test_scholarships_page_lists_active_scholarships_above_the_ones_available_to_apply(): void
    {
        $student = User::factory()->create();
        Scholarship::factory()->create(['title' => 'Open Grant']);
        Scholarship::factory()->closed()->create(['title' => 'Closed Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSeeInOrder(['Where you stand', 'Republic Act No. 10931', 'Available to apply', 'Open Grant']);
        $response->assertDontSee('Closed Grant');
        $response->assertViewHas('hasOpenScholarships', true);
    }

    public function test_scholarships_banner_counts_the_scholarships_open_to_the_student(): void
    {
        $student = User::factory()->create();
        Scholarship::factory()->count(2)->create();
        Scholarship::factory()->closed()->create();

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSeeInOrder(['scholarships are open to you.', 'Where you stand']);
    }

    public function test_a_student_holding_an_active_scholarship_is_offered_nothing_else_to_apply_to(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Held Grant']))
            ->create();
        Scholarship::factory()->create(['title' => 'Open Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSeeInOrder(['Nothing is open for you to apply to right now.', 'Where you stand', 'Republic Act No. 10931', 'Held Grant', 'Available to apply', 'You hold an active scholarship. Apply again once it ends.']);
        $response->assertDontSee('Open Grant');
        $response->assertViewHas('hasOpenScholarships', false);
    }

    public function test_scholarship_details_are_marked_restricted_while_the_student_holds_an_active_scholarship(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSeeInOrder(['Merit Grant', 'Restricted', 'You currently hold an active scholarship. As a student may hold only one active scholarship at a time, applications to other scholarships are restricted until the grant period of your current scholarship has ended.']);
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    public function test_scholarship_details_are_marked_restricted_while_the_student_is_on_leave_of_absence(): void
    {
        $student = User::factory()->onLeaveOfAbsence()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSeeInOrder(['Merit Grant', 'Restricted', 'You are on leave of absence (LOA), so you can&#039;t apply to scholarships.'], false);
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    public function test_the_active_scholarship_restriction_is_explained_before_the_stem_course_limit(): void
    {
        $student = User::factory()->create(['course' => 'BS Accountancy']);
        ScholarshipApplication::factory()->approved()->for($student)->create();
        $scholarship = Scholarship::factory()->stemCoursesOnly()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('Restricted');
        $response->assertSee('You currently hold an active scholarship.');
        $response->assertDontSee('Only students in a STEM course can apply');
    }

    public function test_the_scholarship_a_student_holds_is_not_marked_restricted(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('View your application');
        $response->assertDontSee('Restricted');
    }

    public function test_scholarships_page_boxes_give_the_deadline_and_documents_but_no_slot_count(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Grant', 'slots' => 5, 'deadline' => now()->addMonth()->setDate(2026, 11, 8)]);
        ScholarshipRequiredDocument::factory()->count(2)->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        // Every box sets the same facts in the same cells, so two boxes compare at
        // a glance: when it closes, then how many documents it wants beside the
        // Apply cue. Slots left are not shown (decided 2026-10-24).
        $response->assertSeeTextInOrder(['Merit Grant', 'Closes', 'Nov 8, 2026', '2 documents', 'Apply']);
        $response->assertDontSeeText('Slots left');
    }

    public function test_scholarship_details_explain_the_limit_instead_of_offering_to_apply_when_the_student_is_at_it(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->count(2)->for($student)->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('You already have 2 applications waiting for a decision.');
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function coursesAndWhetherStemOnlyIsListed(): array
    {
        return [
            'STEM course' => ['BS Civil Engineering', true],
            'non-STEM course' => ['BS Accountancy', false],
        ];
    }

    #[DataProvider('coursesAndWhetherStemOnlyIsListed')]
    public function test_a_stem_only_scholarship_is_listed_only_for_students_in_a_stem_course(string $course, bool $listed): void
    {
        $student = User::factory()->create(['course' => $course]);
        Scholarship::factory()->stemCoursesOnly()->create(['title' => 'DOST Scholarship']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertViewHas('availableScholarships', fn ($scholarships): bool => $scholarships->contains('title', 'DOST Scholarship') === $listed);
    }

    public function test_a_stem_only_scholarship_tells_a_student_outside_stem_why_they_cannot_apply(): void
    {
        $student = User::factory()->create(['course' => 'BS Accountancy']);
        $scholarship = Scholarship::factory()->stemCoursesOnly()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('Only students in a STEM course can apply, and your registered course, BS Accountancy, is not one of them.');
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    public function test_scholarship_details_name_its_type(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['type' => 'academic']);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('Academic scholarship');
    }

    public function test_a_scholarship_closed_by_osas_states_the_reason_and_never_a_future_deadline(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->closed()->create([
            'title' => 'Tulong Dunong',
            'slots' => 12,
            'deadline' => now()->addDays(10),
        ]);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('Closed by OSAS');
        $response->assertDontSee('Closes in 10 days');
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    public function test_an_open_scholarship_counts_its_deadline_in_whole_calendar_days(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['deadline' => now()->addDays(2)]);

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        // Counted between day boundaries, so the time of day never rounds this down.
        $response->assertSee('Closes in 2 days');
    }

    public function test_a_full_scholarship_states_full_rather_than_its_still_future_deadline(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1, 'deadline' => now()->addDays(30)]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('Full');
        $response->assertDontSee('Closes in 30 days');
    }

    public function test_a_posting_of_a_preset_the_student_holds_is_not_offered_to_apply_to(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->for($student)->for(Scholarship::factory()->fromPreset('tdp')->closed())->create();
        Scholarship::factory()->fromPreset('tdp')->create(['title' => 'TDP Second Call']);
        Scholarship::factory()->create(['title' => 'Merit Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSee('Merit Grant');
        $response->assertDontSee('TDP Second Call');
    }

    public function test_scholarship_details_explain_that_the_student_holds_another_posting_of_its_preset(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->for($student)->for(Scholarship::factory()->fromPreset('tdp')->closed())->create();
        $scholarship = Scholarship::factory()->fromPreset('tdp')->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSeeInOrder(['Restricted', 'You already applied to another Tulong Dunong Program (TDP) posting. A student may hold only one at a time, so you can apply here only if that application is rejected or its grant period ends.']);
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }
}
