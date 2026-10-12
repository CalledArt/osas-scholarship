<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TutorialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every distinct place a form on the page can post to, sorted.
     *
     * @return list<string>
     */
    private function formActions(TestResponse $response): array
    {
        preg_match_all('/<form\b[^>]*\baction="([^"]*)"/', $response->getContent(), $matches);
        $actions = array_values(array_unique(array_map('html_entity_decode', $matches[1])));
        sort($actions);

        return $actions;
    }

    public function test_a_newcomers_dashboard_opens_the_practice_tour_first(): void
    {
        $this->actingAs(User::factory()->newcomer()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('tutorial'));

        $this->actingAs(User::factory()->admin()->newcomer()->create())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('tutorial'));
    }

    public function test_the_dashboard_opens_normally_once_the_tour_has_been_seen(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_completing_the_tour_records_it_and_returns_to_the_dashboard(): void
    {
        $student = User::factory()->newcomer()->create();
        $admin = User::factory()->admin()->newcomer()->create();

        $this->actingAs($student)->post(route('tutorial.complete'))->assertRedirect(route('dashboard'));
        $this->actingAs($admin)->post(route('tutorial.complete'))->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($student->fresh()->tutorial_completed_at);
        $this->assertNotNull($admin->fresh()->tutorial_completed_at);
    }

    public function test_replaying_the_tour_keeps_the_first_completion(): void
    {
        $student = User::factory()->create(['tutorial_completed_at' => '2026-09-01 08:00:00']);

        $this->actingAs($student)->post(route('tutorial.complete'));

        $this->assertSame('2026-09-01 08:00:00', $student->fresh()->tutorial_completed_at->toDateTimeString());
    }

    public function test_each_role_practices_its_own_flow(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('tutorial'))
            ->assertSee('Upload the pass list')
            ->assertDontSee('Follow your application');

        $this->actingAs(User::factory()->create())
            ->get(route('tutorial'))
            ->assertSee('Follow your application')
            ->assertDontSee('Upload the pass list');
    }

    public function test_the_student_tour_fills_the_practice_form_from_their_own_account(): void
    {
        $student = User::factory()->create([
            'student_number' => '22-04567',
            'course' => 'BS Agriculture',
            'contact_number' => '09171234567',
        ]);

        $response = $this->actingAs($student)->get(route('tutorial'));

        $response->assertSee('22-04567');
        $response->assertSee('BS Agriculture');
        $response->assertSee('09171234567');
        $response->assertSeeInOrder(['First Name', 'Middle Name', 'Last Name']);
    }

    public function test_every_menu_item_the_tour_points_at_is_in_the_menu(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->create()] as $user) {
            $page = $this->actingAs($user)->get(route('tutorial'))->getContent();
            $config = Str::between($page, 'x-data="tutorial(', ')"');
            preg_match_all('/nav-[a-z]+/', $config, $matches);

            $this->assertNotEmpty($matches[0]);

            foreach (array_unique($matches[0]) as $menuItem) {
                $this->assertStringContainsString('data-tour="'.$menuItem.'"', $page, "The tour points at {$menuItem}, which the menu lacks.");
            }
        }
    }

    public function test_the_practice_tour_can_only_mark_itself_seen(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->create()] as $user) {
            $response = $this->actingAs($user)->get(route('tutorial'));

            $this->assertSame([route('logout'), route('tutorial.complete')], $this->formActions($response));
        }
    }

    public function test_rail_offers_the_tutorial_from_any_page(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('profile.edit'));

        $response->assertSee(route('tutorial'));
    }

    public function test_guest_cannot_open_or_complete_the_tutorial(): void
    {
        $this->get(route('tutorial'))->assertRedirect(route('login'));
        $this->post(route('tutorial.complete'))->assertRedirect(route('login'));
    }
}
