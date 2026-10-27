<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipApplicationDocument;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AdminApplicationsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cannot_download_the_applications_report(): void
    {
        $scholarship = Scholarship::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.scholarships.applications.report', $scholarship))
            ->assertForbidden();
    }

    public function test_scholarship_page_offers_the_report_once_someone_has_applied(): void
    {
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.scholarships.show', $scholarship))
            ->assertSee(route('admin.scholarships.applications.report', $scholarship));
    }

    public function test_report_lists_every_applicant_by_surname_with_the_documents_they_submitted(): void
    {
        $this->travelTo('2026-10-20 09:30:00');
        $scholarship = Scholarship::factory()->create(['title' => 'CHED Merit Grant']);
        $picture = ScholarshipRequiredDocument::factory()->for($scholarship)->create(['name' => '2x2 Picture']);
        $registration = ScholarshipRequiredDocument::factory()->for($scholarship)->create(['name' => 'Certificate of Registration']);
        $ana = ScholarshipApplication::factory()->for($scholarship)
            ->for(User::factory()->named('Ana', null, 'Reyes')->state(['contact_number' => '09171234567']))
            ->create();
        ScholarshipApplicationDocument::factory()->for($ana, 'application')->for($picture, 'requiredDocument')->create(['original_name' => 'ana-photo.jpg']);
        ScholarshipApplicationDocument::factory()->for($ana, 'application')->for($registration, 'requiredDocument')->create(['original_name' => 'ana-cor.pdf']);
        $ben = ScholarshipApplication::factory()->for($scholarship)->approved()
            ->for(User::factory()->named('Ben', null, 'Santos'))
            ->create();
        ScholarshipApplicationDocument::factory()->for($ben, 'application')->for($picture, 'requiredDocument')->create(['original_name' => 'ben-photo.jpg']);
        ScholarshipApplication::factory()->for($scholarship)->rejected()->for(User::factory()->named('Carla', null, 'Cruz'))->create();
        ScholarshipApplication::factory()->for($scholarship)->for(User::factory()->named('Dina', null, 'de la Paz'))->create();
        ScholarshipApplication::factory()->for(User::factory()->named('Other', null, 'Applicant'))->create();
        $reportView = null;
        View::composer('admin.scholarships.report', function ($view) use (&$reportView) {
            $reportView = $view;
        });

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.scholarships.applications.report', $scholarship));

        $response->assertDownload('applications-report-ched-merit-grant-2026-10-20.pdf');
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $report = $reportView->render();
        $this->assertMatchesRegularExpression('/CRUZ, CARLA.*DE LA PAZ, DINA.*REYES, ANA.*SANTOS, BEN/s', $report);
        $this->assertMatchesRegularExpression('/CRUZ, CARLA.*Rejected.*✗.*2x2 Picture.*Not submitted/s', $report);
        $this->assertMatchesRegularExpression('/REYES, ANA.*Pending.*✓.*2x2 Picture.*ana-photo\.jpg.*✓.*Certificate of Registration.*ana-cor\.pdf/s', $report);
        $this->assertMatchesRegularExpression('/SANTOS, BEN.*Approved.*✓.*2x2 Picture.*ben-photo\.jpg.*✗.*Certificate of Registration.*Not submitted/s', $report);
        $this->assertStringNotContainsString('Other Applicant', $report);
        $this->assertStringNotContainsString('09171234567', $report);
    }
}
