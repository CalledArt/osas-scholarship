<?php

namespace Tests\Feature\Admin;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use SimpleXMLElement;
use Tests\TestCase;
use ZipArchive;

class FundAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cannot_open_fund_allocation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.fund-allocation.index'))
            ->assertForbidden();
    }

    public function test_fund_allocation_lists_only_scholarships_students_have_passed_with_the_equal_share(): void
    {
        $passed = Scholarship::factory()->funded('90000.00')->create(['title' => 'CHED Merit Grant']);
        ScholarshipApplication::factory()->count(3)->for($passed)->approved()->create();
        ScholarshipApplication::factory()->for($passed)->rejected()->create();
        $undecided = Scholarship::factory()->funded()->create(['title' => 'Undecided Grant']);
        ScholarshipApplication::factory()->for($undecided)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.index'))
            ->assertSeeInOrder([route('admin.fund-allocation.show', $passed), 'CHED Merit Grant', 'Allocated', '₱90,000.00', '₱30,000.00', '3 students passed'])
            ->assertDontSee('Undecided Grant');
    }

    public function test_banner_counts_every_passed_scholarship_still_without_funds_beyond_the_first_page(): void
    {
        $unfunded = Scholarship::factory()->create(['created_at' => now()->subDay()]);
        ScholarshipApplication::factory()->for($unfunded)->approved()->create();
        foreach (Scholarship::factory()->funded()->count(12)->create() as $funded) {
            ScholarshipApplication::factory()->for($funded)->approved()->create();
        }
        ScholarshipApplication::factory()->for(Scholarship::factory())->create();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.fund-allocation.index'));

        $response->assertViewHas('unfundedCount', 1);
        $response->assertSeeInOrder(['Fund Allocation', 'scholarship still needs its total funds.']);
    }

    public function test_banner_says_every_fund_is_set_once_none_is_missing(): void
    {
        $scholarship = Scholarship::factory()->funded()->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()->create();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.fund-allocation.index'));

        $response->assertSeeInOrder(['Fund Allocation', "You're all caught up. Every fund is set."]);
    }

    public function test_fund_allocation_flags_a_scholarship_whose_fund_is_not_set(): void
    {
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.index'))
            ->assertSeeInOrder(['Fund not set', 'Not set', 'No share until the fund is set']);
    }

    public function test_allocation_list_shows_only_the_students_who_passed_and_their_share(): void
    {
        $scholarship = Scholarship::factory()->funded('100000.00')->create();
        foreach (['Ana', 'Ben', 'Carla'] as $firstName) {
            ScholarshipApplication::factory()->for($scholarship)->approved()
                ->for(User::factory()->named($firstName, null, 'Reyes'))
                ->create();
        }
        ScholarshipApplication::factory()->for($scholarship)->for(User::factory()->named('Pending', null, 'Applicant'))->create();
        ScholarshipApplication::factory()->for($scholarship)->rejected()->for(User::factory()->named('Rejected', null, 'Applicant'))->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.show', $scholarship))
            ->assertSeeInOrder(['Reyes, Ana', '₱33,333.33', 'Reyes, Ben', '₱33,333.33', 'Reyes, Carla', '₱33,333.33'])
            ->assertDontSee('Pending Applicant')
            ->assertDontSee('Rejected Applicant');
    }

    public function test_allocation_list_states_the_centavos_the_equal_split_leaves_in_the_fund(): void
    {
        $scholarship = Scholarship::factory()->funded('100000.00')->create();
        ScholarshipApplication::factory()->count(3)->for($scholarship)->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.show', $scholarship))
            ->assertSeeInOrder(['Allocated', '₱99,999.99', '₱0.01 left over stays in the fund']);
    }

    public function test_allocation_list_asks_for_the_fund_before_showing_shares(): void
    {
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.show', $scholarship))
            ->assertSee('Set total funds')
            ->assertSeeInOrder(['Students who passed', 'No share until the fund is set']);
    }

    public function test_allocation_list_divides_the_total_funds_by_the_students_who_passed_as_osas_types(): void
    {
        $scholarship = Scholarship::factory()->funded('100000.00')->create();
        ScholarshipApplication::factory()->count(3)->for($scholarship)->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.show', $scholarship))
            ->assertSee('action="'.route('admin.fund-allocation.funds.update', $scholarship).'"', false)
            ->assertSee('x-model="total"', false)
            ->assertSeeInOrder(['Total funds (₱)', '÷ 3 students who passed =', '₱33,333.33', 'per student', 'Update total funds']);
    }

    public function test_admin_sets_the_total_funds_on_the_allocation_list_and_is_told_each_students_allocation(): void
    {
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->count(3)->for($scholarship)->approved()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.fund-allocation.funds.update', $scholarship), ['total_funds' => '100000']);

        $response->assertRedirect(route('admin.fund-allocation.show', $scholarship));
        $response->assertSessionHas('status', 'Total funds saved. Each of the 3 students who passed is allocated ₱33,333.33.');
        $this->assertSame('100000.00', $scholarship->fresh()->total_funds);
    }

    #[TestWith(['', 'The total funds field is required.'])]
    #[TestWith(['0.50', 'The total funds field must be at least 1.'])]
    #[TestWith(['1000.005', 'The total funds field must have 0-2 decimal places.'])]
    public function test_total_funds_must_be_at_least_one_peso_in_whole_centavos(string $totalFunds, string $message): void
    {
        $scholarship = Scholarship::factory()->funded('90000.00')->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.fund-allocation.funds.update', $scholarship), ['total_funds' => $totalFunds]);

        $response->assertSessionHasErrors(['total_funds' => $message]);
        $this->assertSame('90000.00', $scholarship->fresh()->total_funds);
    }

    public function test_total_funds_must_give_every_student_who_passed_at_least_a_centavo(): void
    {
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->count(101)->for($scholarship)->approved()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.fund-allocation.funds.update', $scholarship), ['total_funds' => '1.00']);

        $response->assertSessionHasErrors(['total_funds' => 'The total funds must give each of the 101 students who passed at least ₱0.01.']);
        $this->assertNull($scholarship->fresh()->total_funds);
    }

    public function test_allocation_list_without_passed_students_explains_instead_of_dividing_by_zero(): void
    {
        $scholarship = Scholarship::factory()->funded('90000.00')->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.show', $scholarship))
            ->assertOk()
            ->assertSee('No students have passed yet. Each one&#039;s allocation appears here once a pass list approves them.', false)
            ->assertDontSee('students who passed =');
    }

    public function test_allocation_list_names_missing_student_details_for_screen_readers(): void
    {
        $scholarship = Scholarship::factory()->funded('90000.00')->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()
            ->for(User::factory()->state(['student_number' => null, 'course' => null, 'year_level' => null]))
            ->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.show', $scholarship))
            ->assertSeeInOrder(['No student number', 'No course recorded', 'No year level recorded']);
    }

    public function test_students_cannot_set_the_total_funds(): void
    {
        $scholarship = Scholarship::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.fund-allocation.funds.update', $scholarship), ['total_funds' => '100000'])
            ->assertForbidden();
        $this->assertNull($scholarship->fresh()->total_funds);
    }

    public function test_report_downloads_the_allocation_as_a_landscape_excel_sheet_for_students_to_sign(): void
    {
        $this->travelTo('2026-10-20 09:30:00');
        $scholarship = Scholarship::factory()->funded('90000.00')->create(['title' => 'CHED Merit Grant']);
        ScholarshipApplication::factory()->for($scholarship)->approved()
            ->for(User::factory()->named('Ben', null, 'Santos')->state(['student_number' => '22-00002', 'course' => 'BS Industrial Technology major in Automotive Technology']))
            ->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()
            ->for(User::factory()->named('Ana', 'Cruz', 'Reyes')->state(['student_number' => '22-00001', 'course' => 'BS Computer Science', 'contact_number' => '09171234567']))
            ->create();
        ScholarshipApplication::factory()->for($scholarship)->for(User::factory()->named('Pending', null, 'Applicant'))->create();

        $response = $this->actingAs(User::factory()->admin()->named('Olivia', null, 'Staff')->create())
            ->get(route('admin.fund-allocation.report', $scholarship));

        $response->assertDownload('fund-allocation-ched-merit-grant-2026-10-20.xlsx');
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $sheet = $this->worksheet($response->streamedContent());
        $rows = $this->rows($sheet);
        $this->assertSame(['A' => 'CHED Merit Grant'], array_filter($rows[4]));
        $this->assertArrayNotHasKey(5, $rows);
        $this->assertSame(['A' => 'SEQ No.', 'B' => 'Student Number', 'C' => 'Last Name', 'D' => 'First Name', 'E' => 'Middle Name', 'F' => 'Course', 'G' => 'Amount Received', 'H' => 'Signature'], $rows[6]);
        $this->assertSame(['A' => '1', 'B' => '22-00001', 'C' => 'REYES', 'D' => 'ANA', 'E' => 'CRUZ', 'F' => 'BSCS', 'G' => '45000.00', 'H' => ''], $rows[7]);
        $this->assertSame(['A' => '2', 'B' => '22-00002', 'C' => 'SANTOS', 'D' => 'BEN', 'E' => '', 'F' => 'BSIndT-AT', 'G' => '45000.00', 'H' => ''], $rows[8]);
        $this->assertSame(['A' => 'Total', 'G' => '90000.00'], array_filter($rows[9]));
        $this->assertSame('SUM(G7:G8)', (string) $sheet->xpath('//x:c[@r="G9"]/x:f')[0]);
        $this->assertSame('STAFF, OLIVIA', $rows[13]['B']);
        $this->assertStringNotContainsString('Pending', $sheet->asXML());
        $this->assertStringNotContainsString('09171234567', $sheet->asXML());

        $this->assertSame('landscape', (string) $sheet->pageSetup['orientation']);
        $this->assertSame('1', (string) $sheet->pageSetup['fitToWidth']);
    }

    public function test_report_puts_the_cvsu_logo_in_the_top_left_corner(): void
    {
        $scholarship = Scholarship::factory()->funded()->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.report', $scholarship));

        $workbook = $response->streamedContent();
        $this->assertCount(1, $this->worksheet($workbook)->xpath('//x:drawing'));
        $drawing = new SimpleXMLElement($this->workbookFile($workbook, 'xl/drawings/drawing1.xml'));
        $drawing->registerXPathNamespace('xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing');
        $this->assertSame(['0', '0'], [(string) $drawing->xpath('//xdr:from/xdr:col')[0], (string) $drawing->xpath('//xdr:from/xdr:row')[0]]);
        $this->assertSame(file_get_contents(resource_path('images/cvsu-logo.png')), $this->workbookFile($workbook, 'xl/media/image1.png'));
    }

    public function test_report_leaves_the_amounts_blank_until_the_fund_is_set(): void
    {
        $scholarship = Scholarship::factory()->create();
        ScholarshipApplication::factory()->for($scholarship)->approved()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.fund-allocation.report', $scholarship));

        $rows = $this->rows($this->worksheet($response->streamedContent()));
        $this->assertSame('', $rows[7]['G']);
        $this->assertSame('', $rows[8]['G']);
    }

    /**
     * The workbook's one worksheet, with the spreadsheet namespace registered as "x" for XPath.
     */
    private function worksheet(string $xlsx): SimpleXMLElement
    {
        $sheet = new SimpleXMLElement($this->workbookFile($xlsx, 'xl/worksheets/sheet1.xml'));
        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        return $sheet;
    }

    /**
     * One file from inside the .xlsx package, by its path there.
     */
    private function workbookFile(string $xlsx, string $name): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $xlsx);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $contents = $zip->getFromName($name);
        $zip->close();
        unlink($path);

        $this->assertIsString($contents, "The workbook has no {$name}.");

        return $contents;
    }

    /**
     * Each row's cell values by column letter, keyed by row number; empty cells read as ''.
     *
     * @return array<int, array<string, string>>
     */
    private function rows(SimpleXMLElement $sheet): array
    {
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            foreach ($row->c as $cell) {
                $column = preg_replace('/\d+/', '', (string) $cell['r']);
                $rows[(int) $row['r']][$column] = isset($cell->is) ? (string) $cell->is->t : (string) $cell->v;
            }
        }

        return $rows;
    }
}
