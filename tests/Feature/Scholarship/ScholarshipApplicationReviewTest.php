<?php

namespace Tests\Feature\Scholarship;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipApplicationReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_application(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->patch(
            route('admin.scholarships.applications.update', [$scholarship, $application]),
            ['status' => 'approved']
        );

        $response->assertRedirect();
        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
    }

    public function test_admin_can_reject_a_pending_application(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 5]);
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->patch(
            route('admin.scholarships.applications.update', [$scholarship, $application]),
            ['status' => 'rejected']
        );

        $response->assertRedirect();
        $this->assertSame(ApplicationStatus::Rejected, $application->fresh()->status);
    }

    public function test_student_is_forbidden_from_reviewing_applications(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($student)->patch(
            route('admin.scholarships.applications.update', [$scholarship, $application]),
            ['status' => 'approved']
        );

        $response->assertForbidden();
        $this->assertSame(ApplicationStatus::Pending, $application->fresh()->status);
    }

    public function test_admin_cannot_approve_an_application_when_no_slots_remain(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        ScholarshipApplication::factory()->approved()->for($scholarship)->create();
        $pendingApplication = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->patch(
            route('admin.scholarships.applications.update', [$scholarship, $pendingApplication]),
            ['status' => 'approved']
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::Pending, $pendingApplication->fresh()->status);
    }

    public function test_scholarship_closes_automatically_once_the_last_slot_is_approved(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create(['slots' => 1]);
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $this->actingAs($admin)->patch(
            route('admin.scholarships.applications.update', [$scholarship, $application]),
            ['status' => 'approved']
        );

        $this->assertSame(ScholarshipStatus::Closed, $scholarship->fresh()->status);
    }

    public function test_status_update_rejects_an_invalid_status_value(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        $application = ScholarshipApplication::factory()->for($scholarship)->create();

        $response = $this->actingAs($admin)->patch(
            route('admin.scholarships.applications.update', [$scholarship, $application]),
            ['status' => 'pending']
        );

        $response->assertSessionHasErrors('status');
    }
}
