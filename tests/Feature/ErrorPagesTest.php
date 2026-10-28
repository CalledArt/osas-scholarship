<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_form_sent_after_its_page_expired_offers_the_way_back_to_it(): void
    {
        Route::middleware('web')->post('/_test/expired', fn () => abort(419));
        $student = User::factory()->create();

        $response = $this->actingAs($student)
            ->from(route('scholarships.index'))
            ->post('/_test/expired');

        $response->assertStatus(419);
        $response->assertSee('This form expired');
        $response->assertSee('href="'.route('scholarships.index').'"', false);
    }

    public function test_a_server_failure_explains_itself_without_asking_the_database(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/_test/failure', fn () => throw new RuntimeException('Database unreachable.'));

        DB::enableQueryLog();
        $response = $this->get('/_test/failure');

        $response->assertStatus(500);
        $response->assertSee('Something went wrong on our side');
        $response->assertSee('Go to the start page');
        $response->assertDontSee('Database unreachable.');
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_maintenance_and_rate_limits_have_their_own_pages(): void
    {
        Route::middleware('web')->get('/_test/maintenance', fn () => abort(503));
        Route::middleware('web')->get('/_test/throttled', fn () => abort(429));

        $this->get('/_test/maintenance')->assertStatus(503)->assertSeeInOrder(['Down for a short update', 'Try again', 'Error 503']);
        $this->get('/_test/throttled')->assertStatus(429)->assertSee('Too many tries');
    }
}
