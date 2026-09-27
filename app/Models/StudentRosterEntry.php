<?php

namespace App\Models;

use Database\Factories\StudentRosterEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One student OSAS has cleared to use the system.
 *
 * Signing in with Google only succeeds for an email on this list, and the
 * student's account is created from this row the first time they do.
 */
#[Fillable(['email', 'student_number', 'first_name', 'middle_name', 'last_name', 'course', 'year_level'])]
class StudentRosterEntry extends Model
{
    /** @use HasFactory<StudentRosterEntryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year_level' => 'integer',
        ];
    }
}
