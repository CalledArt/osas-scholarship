<?php

namespace Tests\Feature\Admin;

use App\Models\StudentRosterEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StudentRosterTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $contents): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('roster.csv', $contents);
    }

    private function upload(string $contents): TestResponse
    {
        return $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.roster.import'), ['file' => $this->csv($contents)]);
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get(route('admin.roster.index'))->assertRedirect(route('login'));
    }

    public function test_students_cannot_see_or_change_the_roster(): void
    {
        $student = User::factory()->create();
        $entry = StudentRosterEntry::factory()->create();

        $this->actingAs($student)->get(route('admin.roster.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.roster.import'), ['file' => $this->csv("email\na@cvsu.edu.ph\n")])->assertForbidden();
        $this->actingAs($student)->delete(route('admin.roster.destroy', $entry))->assertForbidden();
        $this->assertDatabaseCount('student_roster_entries', 1);
    }

    public function test_admins_see_the_roster(): void
    {
        StudentRosterEntry::factory()->create(['email' => 'juan@cvsu.edu.ph', 'first_name' => 'Juan', 'last_name' => 'Cruz']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index'))
            ->assertOk()
            ->assertSee('juan@cvsu.edu.ph')
            ->assertSee('Juan Cruz');
    }

    public function test_the_roster_can_be_searched(): void
    {
        StudentRosterEntry::factory()->create(['email' => 'juan@cvsu.edu.ph']);
        StudentRosterEntry::factory()->create(['email' => 'maria@cvsu.edu.ph']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['search' => 'maria']))
            ->assertSee('maria@cvsu.edu.ph')
            ->assertDontSee('juan@cvsu.edu.ph');
    }

    public function test_uploading_a_roster_adds_each_valid_row_without_creating_accounts(): void
    {
        $response = $this->upload(
            "Email,Student Number,First Name,Middle Name,Last Name,Course,Year Level\n"
            ."Juan.Cruz@CVSU.edu.ph,21-00123,Juan,Santos,Cruz,BS Information Technology,3\n"
            ."maria@cvsu.edu.ph,21-00124,Maria,,Reyes,BS Accountancy,2\n"
        );

        $response->assertOk()->assertSee('Added');
        $this->assertDatabaseHas('student_roster_entries', [
            'email' => 'juan.cruz@cvsu.edu.ph',
            'student_number' => '21-00123',
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Cruz',
            'course' => 'BS Information Technology',
            'year_level' => 3,
        ]);
        $this->assertDatabaseHas('student_roster_entries', ['email' => 'maria@cvsu.edu.ph', 'middle_name' => null]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_re_uploading_an_email_updates_its_entry(): void
    {
        StudentRosterEntry::factory()->create(['email' => 'juan@cvsu.edu.ph', 'course' => 'BS Accountancy', 'year_level' => 1]);

        $this->upload("email,course,year_level\njuan@cvsu.edu.ph,BS Computer Science,2\n");

        $entry = StudentRosterEntry::sole();
        $this->assertSame('BS Computer Science', $entry->course);
        $this->assertSame(2, $entry->year_level);
    }

    public function test_a_single_name_column_is_split_into_parts(): void
    {
        $this->upload("name,email\nJuan Santos Cruz,juan@cvsu.edu.ph\n");

        $this->assertDatabaseHas('student_roster_entries', [
            'email' => 'juan@cvsu.edu.ph',
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Cruz',
        ]);
    }

    public function test_invalid_rows_are_skipped_with_a_reason_and_the_rest_still_import(): void
    {
        $response = $this->upload(
            "email,year_level\n"
            ."juan@gmail.com,1\n"
            ."maria@cvsu.edu.ph,9\n"
            .",1\n"
            ."pedro@cvsu.edu.ph,1\n"
            ."pedro@cvsu.edu.ph,2\n"
        );

        $response->assertSee('The email must be a CvSU address (@cvsu.edu.ph).')
            ->assertSee('Duplicate email within this file.');
        $this->assertSame(['pedro@cvsu.edu.ph'], StudentRosterEntry::pluck('email')->all());
        $this->assertSame(1, StudentRosterEntry::sole()->year_level);
    }

    public function test_a_file_without_an_email_column_is_rejected(): void
    {
        $this->upload("name,student_number\nJuan Cruz,21-00123\n")
            ->assertSessionHasErrors(['file' => 'The file needs an "email" column.']);

        $this->assertDatabaseCount('student_roster_entries', 0);
    }

    public function test_an_upload_needs_a_file(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.roster.import'))
            ->assertSessionHasErrors('file');
    }

    public function test_the_template_lists_the_columns_the_importer_reads(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.roster.template'));

        $response->assertOk()->assertDownload('student-roster-template.csv');
        $this->assertStringStartsWith('email,student_number,first_name,middle_name,last_name,course,year_level', $response->getContent());
    }

    public function test_an_admin_can_remove_a_student_from_the_roster(): void
    {
        $entry = StudentRosterEntry::factory()->create(['email' => 'juan@cvsu.edu.ph']);

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.roster.index'))
            ->delete(route('admin.roster.destroy', $entry))
            ->assertRedirect(route('admin.roster.index'))
            ->assertSessionHas('status', 'juan@cvsu.edu.ph was removed from the roster.');

        $this->assertModelMissing($entry);
    }
}
