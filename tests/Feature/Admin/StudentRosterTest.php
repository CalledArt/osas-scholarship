<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
