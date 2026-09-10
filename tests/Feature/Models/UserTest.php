<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_joins_the_parts_in_first_middle_last_order(): void
    {
        $user = User::factory()->named('Maria', 'Santos', 'Reyes')->create();

        $this->assertSame('Maria Santos Reyes', $user->full_name);
    }

    public function test_full_name_omits_an_absent_middle_name(): void
    {
        $user = User::factory()->named('Juan', null, 'Dela Cruz')->create();

        $this->assertSame('Juan Dela Cruz', $user->full_name);
    }

    public function test_full_name_falls_back_to_the_stored_name_when_no_parts_are_set(): void
    {
        $user = User::factory()->create();

        // A row written before the name-part columns existed, and never backfilled.
        DB::table('users')->where('id', $user->id)->update([
            'name' => 'Unbackfilled Student',
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
        ]);

        $this->assertSame('Unbackfilled Student', $user->fresh()->full_name);
    }
}
