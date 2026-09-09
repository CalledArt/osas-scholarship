<?php

namespace Tests\Feature\StudentImport;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminStudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.students.import.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_is_forbidden(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.students.import.create'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_the_import_form(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.import.create'));

        $response->assertOk();
        $response->assertSee('Import Students');
    }

    public function test_admin_can_import_students_and_sees_the_generated_password(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = $this->csvFile("name,email\nJuan Dela Cruz,juan@cvsu.edu.ph\n");

        $response = $this->actingAs($admin)->post(route('admin.students.import.store'), [
            'file' => $csv,
        ]);

        $response->assertOk();
        $response->assertSee('juan@cvsu.edu.ph');
        $response->assertSee('1 account created');
        $response->assertSee('One-time password');
        $this->assertDatabaseHas('users', ['email' => 'juan@cvsu.edu.ph', 'role' => 'student']);
    }

    public function test_skipped_rows_are_listed_separately_with_their_reason(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'taken@cvsu.edu.ph']);
        $csv = $this->csvFile("name,email\nJuan Dela Cruz,juan@cvsu.edu.ph\nMaria Santos,taken@cvsu.edu.ph\n");

        $response = $this->actingAs($admin)->post(route('admin.students.import.store'), [
            'file' => $csv,
        ]);

        $response->assertOk();
        $response->assertSee('1 account created');
        $response->assertSee('1 row skipped');
        $response->assertSee('Why it was skipped');
    }

    public function test_import_form_documents_the_headers_the_importer_actually_accepts(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.import.create'));

        $response->assertOk();
        $response->assertSee('Columns the importer reads');
        // Derived from the importer's own column map, so the page cannot drift from it.
        $response->assertSee('student_no, student_id');
        $response->assertSee('contact, phone');
    }

    public function test_import_requires_a_csv_file(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.students.import.store'), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_admin_can_download_the_csv_template(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.import.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSee('name,email,student_number', false);
    }

    private function csvFile(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'students.csv', 'text/csv', null, true);
    }
}
