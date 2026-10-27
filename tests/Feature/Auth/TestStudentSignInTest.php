<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\TestStudents;
use Database\Seeders\TestStudentsSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestStudentSignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_test_student_signs_in_with_the_test_password_on_a_local_machine(): void
    {
        $this->seed(TestStudentsSeeder::class);
        $this->onA('local');

        $response = $this->post('/login', ['email' => 'test.student01@example.com', 'password' => 'michael']);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs(User::where('email', 'test.student01@example.com')->sole());
    }

    public function test_a_test_student_cannot_sign_in_with_a_password_on_the_live_site(): void
    {
        $this->seed(TestStudentsSeeder::class);
        $this->onA('production');

        $response = $this->post('/login', ['email' => 'test.student01@example.com', 'password' => 'michael']);

        $response->assertSessionHasErrors(['email' => 'Students sign in with their CvSU Google account using the Sign in with Google button.']);
        $this->assertGuest();
    }

    public function test_a_real_student_cannot_sign_in_with_a_password_even_on_a_local_machine(): void
    {
        $student = User::factory()->create();
        $this->onA('local');

        $response = $this->post('/login', ['email' => $student->email, 'password' => 'password']);

        $response->assertSessionHasErrors(['email' => 'Students sign in with their CvSU Google account using the Sign in with Google button.']);
        $this->assertGuest();
    }

    public function test_the_sign_in_page_never_lists_the_test_accounts(): void
    {
        $this->onA('local');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('test.student01@example.com')
            ->assertDontSee(TestStudents::PASSWORD);
    }

    /**
     * Run as the given environment. Laravel skips CSRF checks only in the
     * testing environment, so they are switched off here the same way.
     */
    private function onA(string $environment): void
    {
        $this->app['env'] = $environment;
        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}
