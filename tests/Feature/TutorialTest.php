<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Tests\TestCase;

class TutorialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The fragment of the tour's config that says whether it starts on its own,
     * encoded exactly as @js writes it into the page.
     */
    private function autostart(bool $value): string
    {
        return Str::between((string) Js::from(['autostart' => $value]), '{', '}');
    }

    public function test_tutorial_starts_on_its_own_until_the_user_completes_it(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('dashboard'))->assertSee($this->autostart(true), false);

        $this->actingAs($student)->post(route('tutorial.complete'))->assertNoContent();

        $this->assertNotNull($student->fresh()->tutorial_completed_at);
        $this->actingAs($student->fresh())->get(route('dashboard'))->assertSee($this->autostart(false), false);
    }

    public function test_tutorial_link_replays_a_completed_tutorial(): void
    {
        $admin = User::factory()->admin()->create(['tutorial_completed_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tutorial' => 1]));

        $response->assertSee($this->autostart(true), false);
    }

    public function test_each_role_gets_its_own_tour(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('Upload the pass list')
            ->assertDontSee('Follow your applications');

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSee('Follow your applications')
            ->assertDontSee('Upload the pass list');
    }

    public function test_rail_offers_the_tutorial_from_any_page(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('profile.edit'));

        $response->assertSee(route('dashboard', ['tutorial' => 1]), false);
        $response->assertSee('Tutorial');
    }

    public function test_guest_cannot_complete_the_tutorial(): void
    {
        $this->post(route('tutorial.complete'))->assertRedirect(route('login'));
    }
}
