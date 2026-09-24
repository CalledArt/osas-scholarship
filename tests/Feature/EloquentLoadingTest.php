<?php

namespace Tests\Feature;

use App\Models\ScholarshipApplication;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the Eloquent loading defaults AppServiceProvider::boot() installs.
 */
class EloquentLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_outside_production_a_missing_eager_load_throws_instead_of_querying_per_row(): void
    {
        ScholarshipApplication::factory()->count(3)->create();

        $applications = ScholarshipApplication::all();

        $this->expectException(LazyLoadingViolationException::class);

        // Deliberately no with('scholarship') on the query above.
        $applications->first()->scholarship;
    }

    public function test_production_resolves_the_same_miss_in_one_query_rather_than_failing(): void
    {
        ScholarshipApplication::factory()->count(3)->create();

        $this->bootAsProduction();

        DB::enableQueryLog();

        foreach (ScholarshipApplication::all() as $application) {
            $this->assertNotNull($application->scholarship->title);
        }

        // One for the applications, one batched load for all three scholarships —
        // not the four a per-row lazy load would cost.
        $this->assertCount(2, DB::getQueryLog());
    }

    public function test_the_two_settings_are_never_enabled_at_the_same_time(): void
    {
        $this->assertFalse(Model::isAutomaticallyEagerLoadingRelationships());
        $this->assertTrue(Model::preventsLazyLoading());

        $this->bootAsProduction();

        $this->assertTrue(Model::isAutomaticallyEagerLoadingRelationships());
        $this->assertFalse(Model::preventsLazyLoading());
    }

    /**
     * Re-run the provider's boot decisions with the application reporting the
     * production environment, which is the only thing they branch on.
     */
    private function bootAsProduction(): void
    {
        $this->app['env'] = 'production';

        (new AppServiceProvider($this->app))->boot();
    }

    protected function tearDown(): void
    {
        DB::disableQueryLog();

        $this->app['env'] = 'testing';
        (new AppServiceProvider($this->app))->boot();

        parent::tearDown();
    }
}
