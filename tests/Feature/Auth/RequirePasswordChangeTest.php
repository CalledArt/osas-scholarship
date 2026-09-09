<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequirePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_a_pending_password_change_is_redirected_away_from_other_pages(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('password.force-change'));
    }

    public function test_user_with_a_pending_password_change_can_view_the_change_password_page(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user)->get(route('password.force-change'));

        $response->assertOk();
        $response->assertSee('temporary password', false);
    }

    public function test_user_without_a_pending_change_is_redirected_away_from_the_change_password_page(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user)->get(route('password.force-change'));

        $response->assertRedirect('/');
    }

    public function test_user_with_a_pending_change_can_still_submit_a_new_password(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFalse($user->refresh()->must_change_password);
    }

    public function test_user_with_a_pending_change_can_still_log_out(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_without_a_pending_change_is_not_affected(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }
}
