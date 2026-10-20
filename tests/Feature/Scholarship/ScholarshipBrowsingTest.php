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

        $response->assertSeeInOrder(['Full', '0 of 1 slots left', 'This scholarship is no longer accepting applications.']);
        $response->assertDontSee(route('scholarships.apply', $scholarship));
    }

    public function test_scholarships_page_lists_active_scholarships_above_the_ones_available_to_apply(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->approved()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Held Grant']))
            ->create();
        Scholarship::factory()->create(['title' => 'Open Grant']);
        Scholarship::factory()->closed()->create(['title' => 'Closed Grant']);

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSeeInOrder(['Active scholarships', 'Republic Act No. 10931', 'Held Grant', 'Available to apply', 'Open Grant']);
        $response->assertDontSee('Closed Grant');
    }

    public function test_scholarships_page_cards_give_slots_left_documents_and_deadline(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['title' => 'Merit Grant', 'slots' => 5, 'deadline' => now()->addMonth()->setDate(2026, 11, 8)]);
        ScholarshipRequiredDocument::factory()->count(2)->for($scholarship)->create();

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertSeeInOrder(['Merit Grant', 'Slots left', '5', 'of 5', 'Documents', '2', 'Deadline', 'Nov 8']);
    }

    public function test_scholarship_details_explain_the_limit_instead_of_offering_to_apply_when_the_student_is_at_it(): void
    {
        $student = User::factory()->create();
        ScholarshipApplication::factory()->count(2)->for($student)->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($student)->get(route('scholarships.show', $scholarship));

        $response->assertSee('You already hold 2 scholarships');
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
}
