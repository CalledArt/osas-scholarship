<?php

namespace Tests\Feature\StudentImport;

use App\Actions\ImportStudentsFromCsv;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImportStudentsFromCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_student_account_for_each_valid_row(): void
    {
        $csv = "name,email,student_number,course,year_level\n"
            ."Juan Dela Cruz,juan@cvsu.edu.ph,21-00123,BS Information Technology,3\n"
            ."Maria Santos,maria@cvsu.edu.ph,21-00456,BS Accountancy,2\n";

        $results = (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $this->assertCount(2, $results);
        $this->assertSame('created', $results[0]['status']);
        $this->assertSame('created', $results[1]['status']);
        $this->assertDatabaseCount('users', 2);

        $juan = User::where('email', 'juan@cvsu.edu.ph')->sole();
        $this->assertSame(UserRole::Student, $juan->role);
        $this->assertSame('21-00123', $juan->student_number);
        $this->assertSame(3, $juan->year_level);
        $this->assertNotNull($juan->email_verified_at);
        $this->assertTrue($juan->must_change_password);
    }

    public function test_generated_password_actually_authenticates(): void
    {
        $csv = "name,email\nJuan Dela Cruz,juan@cvsu.edu.ph\n";

        $results = (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $this->assertTrue(Auth::attempt([
            'email' => 'juan@cvsu.edu.ph',
            'password' => $results[0]['password'],
        ]));
    }

    public function test_skips_a_row_missing_a_required_field(): void
    {
        $csv = "name,email\nJuan Dela Cruz,\n";

        $results = (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $this->assertSame('skipped', $results[0]['status']);
        $this->assertNotNull($results[0]['error']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_skips_a_row_whose_email_already_exists(): void
    {
        User::factory()->create(['email' => 'juan@cvsu.edu.ph']);

        $csv = "name,email\nJuan Dela Cruz,juan@cvsu.edu.ph\n";

        $results = (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $this->assertSame('skipped', $results[0]['status']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_skips_a_duplicate_email_within_the_same_file(): void
    {
        $csv = "name,email\n"
            ."Juan Dela Cruz,juan@cvsu.edu.ph\n"
            ."Juan Duplicate,juan@cvsu.edu.ph\n";

        $results = (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $this->assertSame('created', $results[0]['status']);
        $this->assertSame('skipped', $results[1]['status']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_understands_alternate_column_header_names(): void
    {
        $csv = "Full Name,Email Address,Student No,Program\nJuan Dela Cruz,juan@cvsu.edu.ph,21-00123,BSIT\n";

        (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $juan = User::where('email', 'juan@cvsu.edu.ph')->sole();
        $this->assertSame('21-00123', $juan->student_number);
        $this->assertSame('BSIT', $juan->course);
    }

    public function test_strips_a_utf8_bom_from_the_header_row(): void
    {
        $csv = "\xEF\xBB\xBFname,email\nJuan Dela Cruz,juan@cvsu.edu.ph\n";

        $results = (new ImportStudentsFromCsv)->handle($this->csvFile($csv));

        $this->assertSame('created', $results[0]['status']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_rejects_a_file_with_too_many_rows(): void
    {
        $csv = "name,email\n";

        for ($i = 0; $i < 2001; $i++) {
            $csv .= "Student {$i},student{$i}@cvsu.edu.ph\n";
        }

        $this->expectException(ValidationException::class);

        (new ImportStudentsFromCsv)->handle($this->csvFile($csv));
    }

    private function csvFile(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'students.csv', 'text/csv', null, true);
    }
}
