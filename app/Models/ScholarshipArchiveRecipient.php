<?php

namespace App\Models;

use Database\Factories\ScholarshipArchiveRecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One student's line on an archived scholarship's fund allocation sheet,
 * copied as it stood when the scholarship was deleted. user_id is cleared if
 * the student's account is later deleted; the copied details remain.
 */
#[Fillable(['user_id', 'application_reference', 'student_number', 'first_name', 'middle_name', 'last_name', 'name', 'course', 'amount'])]
class ScholarshipArchiveRecipient extends Model
{
    /** @use HasFactory<ScholarshipArchiveRecipientFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function archive(): BelongsTo
    {
        return $this->belongsTo(ScholarshipArchive::class, 'scholarship_archive_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
