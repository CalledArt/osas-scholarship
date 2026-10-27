<?php

namespace Tests\Feature\Models;

use App\Models\Scholarship;
use App\Models\ScholarshipArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScholarshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_scholarship_gets_a_reference_from_the_year_posted_and_its_id(): void
    {
        $this->travelTo('2026-10-24 09:00:00');

        $scholarship = Scholarship::factory()->create();

        $this->assertSame(sprintf('SCH-2026-%05d', $scholarship->id), $scholarship->fresh()->reference);
    }

    public function test_the_migration_gives_existing_scholarships_and_archives_a_reference(): void
    {
        $migration = require database_path('migrations/2026_10_24_112509_add_reference_to_scholarships_table.php');
        $this->travelTo('2025-08-01 09:00:00');
        $scholarship = Scholarship::factory()->create();
        $archive = ScholarshipArchive::factory()->create(['scholarship_id' => 41, 'posted_at' => '2024-06-15 09:00:00']);
        $migration->down();

        $migration->up();

        $this->assertSame(sprintf('SCH-2025-%05d', $scholarship->id), DB::table('scholarships')->where('id', $scholarship->id)->value('reference'));
        $this->assertSame('SCH-2024-00041', DB::table('scholarship_archives')->where('id', $archive->id)->value('scholarship_reference'));
    }
}
