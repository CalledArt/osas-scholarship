<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Http\Requests\Auth\StudentRegistrationRequest;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use App\Support\TestStudents;
use Database\Seeders\TestStudentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;
use Tests\TestCase;

class TestStudentsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_twenty_test_students_whose_details_pass_the_registration_rules(): void
    {
        $this->seed(TestStudentsSeeder::class);

        $students = User::whereIn('email', TestStudents::emails())->get();
        $this->assertCount(20, $students);
        $this->assertCount(20, $students->pluck('student_number')->unique());

        $rules = (new StudentRegistrationRequest)->rules();
        $rules['student_number'] = array_values(array_filter($rules['student_number'], fn ($rule): bool => ! $rule instanceof Unique));

        foreach ($students as $student) {
            $details = [
                ...$student->only(['first_name', 'middle_name', 'last_name', 'student_number', 'course', 'year_level', 'program_years', 'contact_number', 'address']),
                'date_of_birth' => $student->date_of_birth->toDateString(),
            ];

            $this->assertSame([], Validator::make($details, $rules)->errors()->all(), "{$student->email} fails registration.");
        }
    }

    public function test_seeding_again_updates_the_same_accounts(): void
    {
        $this->seed(TestStudentsSeeder::class);
        $this->seed(TestStudentsSeeder::class);

        $this->assertSame(20, User::whereIn('email', TestStudents::emails())->count());
    }

    public function test_db_seed_creates_the_test_students_and_no_made_up_scholarships(): void
    {
        $this->seed();

        $this->assertSame(20, User::whereIn('email', TestStudents::emails())->count());
        $this->assertSame(0, Scholarship::count());
        $this->assertSame(0, ScholarshipApplication::count());
    }

    public function test_the_test_students_run_through_a_pass_list_and_fund_allocation(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $this->seed(TestStudentsSeeder::class);
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['slots' => 15]);
        $applicants = User::whereIn('email', TestStudents::emails())->get()->reject->isOnLeaveOfAbsence();
        foreach ($applicants as $student) {
            ScholarshipApplication::factory()->for($scholarship)->for($student)->create();
        }
        $passList = "student_number\n".$applicants->take(16)->pluck('student_number')->implode("\n")."\n";

        $response = $this->actingAs($admin)->post(route('admin.scholarships.applications.import-decisions', $scholarship), [
            'file' => UploadedFile::fake()->createWithContent('pass-list.csv', $passList),
        ]);

        $response->assertSessionHas('status', 'Emailed the result to 17 applicants.');
        $statuses = $scholarship->applications()->pluck('status')->countBy(fn (ApplicationStatus $status): string => $status->value);
        $this->assertSame(['approved' => 15, 'pending' => 1, 'rejected' => 2], $statuses->sortKeys()->all());

        $this->put(route('admin.fund-allocation.funds.update', $scholarship), ['total_funds' => '150000'])
            ->assertSessionHas('status', 'Total funds saved. Each of the 15 students who passed is allocated ₱10,000.00.');
        $this->get(route('admin.fund-allocation.show', $scholarship))->assertSeeInOrder(['Students who passed', '(15)']);
        $this->get(route('admin.fund-allocation.report', $scholarship))->assertDownload();
    }
}
