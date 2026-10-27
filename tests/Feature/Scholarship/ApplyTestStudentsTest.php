<?php

namespace Tests\Feature\Scholarship;

use App\Actions\ApplyTestStudents;
use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Database\Seeders\TestStudentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyTestStudentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestStudentsSeeder::class);
    }

    public function test_every_test_student_not_on_leave_applies(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 30]);

        $applied = app(ApplyTestStudents::class)->handle($scholarship);

        $this->assertSame(18, $applied);
        $this->assertSame([ApplicationStatus::Pending], $scholarship->applications()->pluck('status')->unique()->values()->all());
        $this->assertNotContains('test.student17@example.com', $this->applicantEmails($scholarship));
        $this->assertNotContains('test.student18@example.com', $this->applicantEmails($scholarship));
    }

    public function test_applications_stop_once_the_slots_are_taken(): void
    {
        $scholarship = Scholarship::factory()->create(['slots' => 3]);

        $applied = app(ApplyTestStudents::class)->handle($scholarship);

        $this->assertSame(3, $applied);
        $this->assertSame(['test.student01@example.com', 'test.student02@example.com', 'test.student03@example.com'], $this->applicantEmails($scholarship));
    }

    public function test_only_test_students_in_a_stem_course_apply_to_a_stem_only_scholarship(): void
    {
        $scholarship = Scholarship::factory()->fromPreset('dost')->create(['slots' => 30]);

        app(ApplyTestStudents::class)->handle($scholarship);

        $this->assertSame([
            'test.student01@example.com', 'test.student03@example.com', 'test.student04@example.com', 'test.student05@example.com',
            'test.student06@example.com', 'test.student09@example.com', 'test.student11@example.com', 'test.student13@example.com',
            'test.student15@example.com', 'test.student20@example.com',
        ], $this->applicantEmails($scholarship));
    }

    public function test_a_test_student_at_the_scholarship_limit_does_not_apply(): void
    {
        $atLimit = User::where('email', 'test.student01@example.com')->sole();
        ScholarshipApplication::factory()->count(2)->for($atLimit)->create();
        $scholarship = Scholarship::factory()->create(['slots' => 30]);

        app(ApplyTestStudents::class)->handle($scholarship);

        $this->assertNotContains('test.student01@example.com', $this->applicantEmails($scholarship));
        $this->assertCount(17, $this->applicantEmails($scholarship));
    }

    /**
     * @return list<string>
     */
    private function applicantEmails(Scholarship $scholarship): array
    {
        return $scholarship->applications()->with('user')->get()->pluck('user.email')->sort()->values()->all();
    }
}
