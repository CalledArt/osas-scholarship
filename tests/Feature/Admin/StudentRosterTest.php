<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class StudentRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get(route('admin.roster.index'))->assertRedirect(route('login'));
    }

    public function test_students_cannot_see_the_roster(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.roster.index'))
            ->assertForbidden();
    }

    public function test_admins_see_registered_students_and_no_import(): void
    {
        User::factory()->named('Juan', null, 'Cruz')->create([
            'email' => 'juan@cvsu.edu.ph',
            'student_number' => '21-00123',
            'course' => 'BS Information Technology',
            'year_level' => 3,
        ]);
        User::factory()->admin()->create(['email' => 'osas.staff@cvsu.edu.ph']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index'))
            ->assertOk()
            ->assertSee('Search Student')
            ->assertSee('juan@cvsu.edu.ph')
            ->assertSee('Juan Cruz')
            ->assertSee('21-00123')
            ->assertSee('BS Information Technology · Year 3')
            ->assertDontSee('osas.staff@cvsu.edu.ph')
            ->assertDontSee('Upload roster');
    }

    public function test_the_roster_can_be_searched(): void
    {
        User::factory()->create(['email' => 'juan@cvsu.edu.ph']);
        User::factory()->create(['email' => 'maria@cvsu.edu.ph']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['search' => 'maria']))
            ->assertSee('maria@cvsu.edu.ph')
            ->assertDontSee('juan@cvsu.edu.ph');
    }

    /**
     * @param  list<string>  $expectedNames
     */
    #[TestWith(['name_asc', ['Bea Alba', 'Ana Cruz', 'Zoe Cruz', 'Luis Diaz']])]
    #[TestWith(['name_desc', ['Luis Diaz', 'Zoe Cruz', 'Ana Cruz', 'Bea Alba']])]
    #[TestWith(['student_number_asc', ['Luis Diaz', 'Bea Alba', 'Zoe Cruz', 'Ana Cruz']])]
    #[TestWith(['student_number_desc', ['Ana Cruz', 'Zoe Cruz', 'Bea Alba', 'Luis Diaz']])]
    #[TestWith(['year_level_asc', ['Bea Alba', 'Ana Cruz', 'Luis Diaz', 'Zoe Cruz']])]
    #[TestWith(['year_level_desc', ['Zoe Cruz', 'Ana Cruz', 'Luis Diaz', 'Bea Alba']])]
    #[TestWith(['email desc', ['Bea Alba', 'Ana Cruz', 'Zoe Cruz', 'Luis Diaz']])]
    public function test_students_are_listed_in_the_selected_order(string $sort, array $expectedNames): void
    {
        User::factory()->named('Zoe', null, 'Cruz')->create(['student_number' => '23-00030', 'year_level' => 3]);
        User::factory()->named('Ana', null, 'Cruz')->create(['student_number' => '24-00040', 'year_level' => 2]);
        User::factory()->named('Bea', null, 'Alba')->create(['student_number' => '22-00020', 'year_level' => 1]);
        User::factory()->named('Luis', null, 'Diaz')->create(['student_number' => '21-00010', 'year_level' => 2]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['sort' => $sort]))
            ->assertSeeInOrder($expectedNames);
    }

    public function test_search_year_and_course_filters_work_together(): void
    {
        User::factory()->named('Maria', null, 'Cruz')->create([
            'email' => 'matching@cvsu.edu.ph', 'course' => 'BS Information Technology', 'year_level' => 3,
        ]);
        User::factory()->named('Maria', null, 'Santos')->create([
            'email' => 'other-year@cvsu.edu.ph', 'course' => 'BS Information Technology', 'year_level' => 2,
        ]);
        User::factory()->named('Maria', null, 'Reyes')->create([
            'email' => 'other-course@cvsu.edu.ph', 'course' => 'BS Accountancy', 'year_level' => 3,
        ]);
        User::factory()->named('Juan', null, 'Reyes')->create([
            'email' => 'other-name@cvsu.edu.ph', 'course' => 'BS Information Technology', 'year_level' => 3,
        ]);
        User::factory()->admin()->named('Maria', null, 'Admin')->create([
            'email' => 'matching-staff@cvsu.edu.ph', 'course' => 'BS Information Technology', 'year_level' => 3,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['search' => 'Maria', 'year_level' => 3, 'course' => ' information ']))
            ->assertSee('matching@cvsu.edu.ph')
            ->assertDontSee('other-year@cvsu.edu.ph')
            ->assertDontSee('other-course@cvsu.edu.ph')
            ->assertDontSee('other-name@cvsu.edu.ph')
            ->assertDontSee('matching-staff@cvsu.edu.ph')
            ->assertSee('value="information"', false)
            ->assertSee('value="3" selected', false)
            ->assertSee('student found');
    }

    #[TestWith([1])]
    #[TestWith([6])]
    public function test_year_level_can_be_filtered_without_a_search(int $yearLevel): void
    {
        User::factory()->create(['email' => 'matching-year@cvsu.edu.ph', 'year_level' => $yearLevel]);
        User::factory()->create(['email' => 'other-year@cvsu.edu.ph', 'year_level' => 3]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['year_level' => $yearLevel]))
            ->assertSee('matching-year@cvsu.edu.ph')
            ->assertDontSee('other-year@cvsu.edu.ph');
    }

    #[TestWith(['0'])]
    #[TestWith(['7'])]
    #[TestWith(['first'])]
    public function test_invalid_year_filters_fall_back_to_all_years(string $yearLevel): void
    {
        User::factory()->create(['email' => 'first-year@cvsu.edu.ph', 'year_level' => 1]);
        User::factory()->create(['email' => 'sixth-year@cvsu.edu.ph', 'year_level' => 6]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['year_level' => $yearLevel]))
            ->assertSee('first-year@cvsu.edu.ph')
            ->assertSee('sixth-year@cvsu.edu.ph')
            ->assertSee('value="" selected', false);
    }

    public function test_an_unmatched_filter_shows_no_results_instead_of_no_registrations(): void
    {
        User::factory()->create(['year_level' => 1]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', ['year_level' => 6]))
            ->assertSee('No students match your search or filters.')
            ->assertDontSee('No students have registered yet.')
            ->assertSee('Reset');
    }

    public function test_pagination_preserves_search_filters_and_sort_order(): void
    {
        User::factory()->count(51)->named('Maria', null, 'Cruz')->sequence(
            fn (Sequence $sequence): array => ['student_number' => sprintf('24-%05d', $sequence->index + 1)],
        )->create(['year_level' => 2, 'course' => 'BS Information Technology']);

        $filters = ['search' => 'Maria', 'year_level' => 2, 'course' => 'Information', 'sort' => 'student_number_desc'];
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.roster.index', $filters));

        $response->assertSee('value="student_number_desc" selected', false);
        $response->assertSeeInOrder(['24-00051', '24-00002']);
        $response->assertDontSee('24-00001');
        $response->assertSee(route('admin.roster.index', [...$filters, 'page' => 2]));

        $this->get($response->viewData('students')->nextPageUrl())
            ->assertSee('24-00001')
            ->assertDontSee('24-00002');
    }
}
