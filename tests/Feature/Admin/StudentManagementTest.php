<?php

namespace Tests\Feature\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function studentDetails(): array
    {
        return [
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'student_number' => '202100123',
            'course' => 'BS Information Technology',
            'year_level' => 3,
            'program_years' => 5,
            'student_status' => StudentStatus::Active->value,
        ];
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get(route('admin.students.index'))->assertRedirect(route('login'));
    }

    public function test_students_cannot_edit_student_data(): void
    {
        $student = User::factory()->create(['course' => 'BS Accountancy']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.students.update', $student), $this->studentDetails())
            ->assertForbidden();

        $this->assertSame('BS Accountancy', $student->fresh()->course);
    }

    public function test_admins_can_find_a_student_to_edit(): void
    {
        $juan = User::factory()->named('Juan', null, '<b>Cruz</b>')->create(['email' => 'juan@cvsu.edu.ph', 'course' => 'BS Information Technology', 'year_level' => 3]);
        User::factory()->create(['email' => 'maria@cvsu.edu.ph']);
        User::factory()->admin()->create(['email' => 'osas.juan@cvsu.edu.ph']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.students.index', ['search' => 'juan']))
            ->assertOk()
            ->assertSee('juan@cvsu.edu.ph')
            ->assertSee('&lt;b&gt;Cruz&lt;/b&gt;, Juan', false)
            ->assertDontSee('<b>Cruz</b>', false)
            ->assertSeeInOrder(['BS Information Technology</td>', 'Year 3</td>'], false)
            ->assertSee(route('admin.students.edit', $juan))
            ->assertDontSee('maria@cvsu.edu.ph')
            ->assertDontSee('osas.juan@cvsu.edu.ph');
    }

    public function test_the_edit_form_shows_the_students_current_details(): void
    {
        $student = User::factory()->named('Juan', 'Santos', 'Dela Cruz')->create([
            'email' => 'juan@cvsu.edu.ph',
            'course' => 'BS Information Technology',
            'year_level' => 4,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.students.edit', $student))
            ->assertOk()
            ->assertSee('value="Dela Cruz"', false)
            ->assertSee("x-on:input=\"\$el.value = \$el.value.replace(/[^0-9]/g, '')\"", false)
            ->assertSee('value="BS Information Technology" selected', false)
            ->assertSee('value="4" selected', false)
            ->assertSee('value="juan@cvsu.edu.ph"', false)
            ->assertDontSee('name="email"', false);
    }

    public function test_the_student_list_marks_students_on_leave_of_absence(): void
    {
        User::factory()->onLeaveOfAbsence()->create(['email' => 'juan@cvsu.edu.ph']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.students.index', ['search' => 'juan']))
            ->assertSeeInOrder(['juan@cvsu.edu.ph', 'LOA']);
    }

    public function test_the_edit_form_shows_a_student_on_leave_and_that_their_free_tuition_is_paused(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->onLeaveOfAbsence()->create(['student_number' => '202312345', 'program_years' => 4]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.students.edit', $student))
            ->assertSee('<option value="leave_of_absence" selected>Leave of Absence (LOA)</option>', false)
            ->assertSee('Their free tuition is paused while they are on leave.')
            ->assertDontSee('Their free tuition has ended.');
    }

    public function test_the_edit_form_shows_when_a_students_ra_10931_coverage_has_ended(): void
    {
        $this->travelTo('2026-10-12');
        $student = User::factory()->create(['student_number' => '202112345', 'program_years' => 4]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.students.edit', $student))
            ->assertSee('This student is in year 6 of 5.')
            ->assertSee('Their free tuition has ended.');
    }

    public function test_admins_can_update_a_students_details(): void
    {
        $student = User::factory()->named('Juan', null, 'Cruz')->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.students.edit', $student))
            ->put(route('admin.students.update', $student), $this->studentDetails())
            ->assertRedirect(route('admin.students.edit', $student))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Student details saved.');

        $student->refresh();
        $this->assertSame('Juan Santos Dela Cruz', $student->name);
        $this->assertSame('Dela Cruz', $student->last_name);
        $this->assertSame('202100123', $student->student_number);
        $this->assertSame('BS Information Technology', $student->course);
        $this->assertSame(3, $student->year_level);
        $this->assertSame(5, $student->program_years);
    }

    public function test_admins_can_mark_a_student_on_leave_of_absence(): void
    {
        $student = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [...$this->studentDetails(), 'student_status' => 'leave_of_absence'])
            ->assertRedirect(route('admin.students.edit', $student))
            ->assertSessionHasNoErrors();

        $this->assertSame(StudentStatus::LeaveOfAbsence, $student->fresh()->student_status);
    }

    public function test_putting_a_student_on_leave_voids_their_pending_applications_but_keeps_approved_scholarships(): void
    {
        $student = User::factory()->create();
        $pending = ScholarshipApplication::factory()->for($student)
            ->for(Scholarship::factory()->create(['title' => 'Merit Grant']))
            ->create();
        $pendingOnClosed = ScholarshipApplication::factory()->for($student)
            ->for(Scholarship::factory()->closed()->create(['title' => 'CHED Grant']))
            ->create();
        $approved = ScholarshipApplication::factory()->approved()->for($student)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [...$this->studentDetails(), 'student_status' => 'leave_of_absence'])
            ->assertSessionHas('status', 'Student details saved. Their pending applications to Merit Grant and CHED Grant were voided, freeing their slots for other students. Reopen CHED Grant, or extend the deadline, to take new applicants.');

        $this->assertSame(ApplicationStatus::Voided, $pending->fresh()->status);
        $this->assertSame(ApplicationStatus::Voided, $pendingOnClosed->fresh()->status);
        $this->assertSame(ApplicationStatus::Approved, $approved->fresh()->status);
    }

    public function test_putting_a_student_with_no_pending_application_on_leave_voids_nothing(): void
    {
        $student = User::factory()->create();
        $approved = ScholarshipApplication::factory()->approved()->for($student)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [...$this->studentDetails(), 'student_status' => 'leave_of_absence'])
            ->assertSessionHas('status', 'Student details saved.');

        $this->assertSame(ApplicationStatus::Approved, $approved->fresh()->status);
    }

    public function test_the_slot_a_student_on_leave_gives_up_can_be_taken_by_another_student(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        ScholarshipApplication::factory()->for($student)->for($scholarship)->create();
        $nextStudent = User::factory()->create();
        $this->assertFalse($nextStudent->can('create', [ScholarshipApplication::class, $scholarship]));

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [...$this->studentDetails(), 'student_status' => 'leave_of_absence']);

        $this->assertTrue($nextStudent->can('create', [ScholarshipApplication::class, $scholarship->fresh()]));
    }

    public function test_admins_can_mark_a_returning_student_active(): void
    {
        $student = User::factory()->onLeaveOfAbsence()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), $this->studentDetails())
            ->assertSessionHasNoErrors();

        $this->assertSame(StudentStatus::Active, $student->fresh()->student_status);
    }

    public function test_the_edit_form_does_not_show_personal_details(): void
    {
        $student = User::factory()->create([
            'date_of_birth' => '2003-05-14',
            'contact_number' => '09171234567',
            'address' => '123 Mabini Street, Indang, Cavite',
        ]);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.students.edit', $student));

        $response->assertDontSee('2003-05-14');
        $response->assertDontSee('May 14, 2003');
        $response->assertDontSee('09171234567');
        $response->assertDontSee('123 Mabini Street, Indang, Cavite');
    }

    public function test_personal_details_cannot_be_changed_here(): void
    {
        $student = User::factory()->create([
            'date_of_birth' => '2003-05-14',
            'contact_number' => '09171234567',
            'address' => 'Indang, Cavite',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [
                ...$this->studentDetails(),
                'date_of_birth' => '2001-01-01',
                'contact_number' => '09998887777',
                'address' => 'Somewhere Else',
            ])
            ->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertSame('2003-05-14', $student->date_of_birth->toDateString());
        $this->assertSame('09171234567', $student->contact_number);
        $this->assertSame('Indang, Cavite', $student->address);
    }

    public function test_the_sign_in_email_and_role_cannot_be_changed(): void
    {
        $student = User::factory()->create(['email' => 'juan@cvsu.edu.ph', 'google_id' => 'google-student-123']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [
                ...$this->studentDetails(),
                'email' => 'someone.else@cvsu.edu.ph',
                'google_id' => 'google-other-456',
                'role' => UserRole::Admin->value,
            ])
            ->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertSame('juan@cvsu.edu.ph', $student->email);
        $this->assertSame('google-student-123', $student->google_id);
        $this->assertTrue($student->isStudent());
    }

    public function test_staff_accounts_cannot_be_edited_here(): void
    {
        $staff = User::factory()->admin()->named('Ana', null, 'Reyes')->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.students.edit', $staff))->assertForbidden();
        $this->put(route('admin.students.update', $staff), $this->studentDetails())->assertForbidden();

        $this->assertSame('Ana Reyes', $staff->fresh()->name);
    }

    public function test_required_details_cannot_be_cleared(): void
    {
        $student = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [])
            ->assertSessionHasErrors([
                'first_name' => 'The first name field is required.',
                'last_name', 'student_number', 'course', 'year_level', 'program_years' => 'The program length field is required.',
                'student_status' => 'The student status field is required.',
            ]);

        $this->assertSame($student->name, $student->fresh()->name);
    }

    #[TestWith(['year_level', 0])]
    #[TestWith(['year_level', 7])]
    #[TestWith(['year_level', 'first'])]
    #[TestWith(['program_years', 1])]
    #[TestWith(['program_years', 7])]
    #[TestWith(['course', 'BS Astrology'])]
    #[TestWith(['student_status', 'graduated'])]
    #[TestWith(['student_number', '21-00123'])]
    #[TestWith(['student_number', '20210012'])]
    public function test_invalid_details_are_rejected(string $field, mixed $value): void
    {
        $student = User::factory()->create(['year_level' => 2]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [...$this->studentDetails(), $field => $value])
            ->assertSessionHasErrors($field);

        $this->assertSame(2, $student->fresh()->year_level);
    }

    public function test_a_student_number_already_used_by_another_student_is_rejected(): void
    {
        User::factory()->create(['student_number' => '202100123']);
        $student = User::factory()->create(['student_number' => '202200456']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), $this->studentDetails())
            ->assertSessionHasErrors(['student_number' => 'The student number has already been taken.']);

        $this->assertSame('202200456', $student->fresh()->student_number);
    }

    public function test_a_student_can_keep_their_own_student_number(): void
    {
        $student = User::factory()->create(['student_number' => '202100123']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), $this->studentDetails())
            ->assertSessionHasNoErrors();
    }

    public function test_the_edit_form_keeps_a_course_saved_before_the_program_list_selected(): void
    {
        $student = User::factory()->create(['course' => 'BSIT']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.students.edit', $student))
            ->assertSee('<option value="BSIT" selected>BSIT (not in the program list)</option>', false);
    }

    public function test_a_course_saved_before_the_program_list_can_be_kept(): void
    {
        $student = User::factory()->create(['course' => 'BSIT']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [...$this->studentDetails(), 'course' => 'BSIT'])
            ->assertSessionHasNoErrors();

        $this->assertSame('BSIT', $student->fresh()->course);
    }

    public function test_the_combined_name_must_fit_the_saved_full_name(): void
    {
        $student = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.students.update', $student), [
                ...$this->studentDetails(),
                'first_name' => str_repeat('A', 130),
                'last_name' => str_repeat('B', 130),
            ])
            ->assertSessionHasErrors(['last_name' => 'The full name must not exceed 255 characters.']);
    }
}
