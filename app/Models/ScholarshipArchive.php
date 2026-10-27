<?php

namespace App\Models;

use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use App\Support\Peso;
use Database\Factories\ScholarshipArchiveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A deleted scholarship whose fund allocation was completed, kept with every
 * recipient's line from its fund allocation sheet (decided 2026-10-24).
 *
 * Kept in the database only; no page reads it yet. It is written as the
 * scholarship is deleted, so created_at is when that happened.
 */
#[Fillable(['scholarship_id', 'scholarship_reference', 'title', 'description', 'type', 'preset', 'stem_courses_only', 'slots', 'deadline', 'status', 'total_funds', 'share_amount', 'recipient_count', 'posted_by', 'posted_at', 'deleted_by'])]
class ScholarshipArchive extends Model
{
    /** @use HasFactory<ScholarshipArchiveFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'posted_at' => 'datetime',
            'status' => ScholarshipStatus::class,
            'type' => ScholarshipType::class,
            'stem_courses_only' => 'boolean',
            'total_funds' => 'decimal:2',
            'share_amount' => 'decimal:2',
        ];
    }

    /**
     * Archive the scholarship's fund allocation if it was completed, returning
     * the archive, or null when there was none to keep.
     *
     * Completed means OSAS entered a fund total and at least one student
     * passed, so a fund allocation sheet could be issued. The recipients are
     * read, ordered and shared out exactly as that sheet does it. Call this
     * in the same transaction as the delete, so neither lands without the
     * other.
     */
    public static function recordFor(Scholarship $scholarship, ?User $deletedBy): ?self
    {
        $passedApplications = $scholarship->applications()
            ->approved()
            ->orderedByStudentName()
            ->with('user')
            ->get();
        $share = $scholarship->fundShareInCentavos($passedApplications->count());

        if ($share === null) {
            return null;
        }

        $archive = self::create([
            'scholarship_id' => $scholarship->id,
            'scholarship_reference' => $scholarship->reference,
            'title' => $scholarship->title,
            'description' => $scholarship->description,
            'type' => $scholarship->type,
            'preset' => $scholarship->preset,
            'stem_courses_only' => $scholarship->stem_courses_only,
            'slots' => $scholarship->slots,
            'deadline' => $scholarship->deadline,
            'status' => $scholarship->status,
            'total_funds' => $scholarship->total_funds,
            'share_amount' => Peso::fromCentavos($share),
            'recipient_count' => $passedApplications->count(),
            'posted_by' => $scholarship->created_by,
            'posted_at' => $scholarship->created_at,
            'deleted_by' => $deletedBy?->id,
        ]);

        $archive->recipients()->createMany($passedApplications->map(fn (ScholarshipApplication $application): array => [
            'user_id' => $application->user_id,
            'application_reference' => $application->reference,
            'student_number' => $application->user->student_number,
            'first_name' => $application->user->first_name,
            'middle_name' => $application->user->middle_name,
            'last_name' => $application->user->last_name,
            'name' => $application->user->full_name,
            'course' => $application->user->course,
            'amount' => Peso::fromCentavos($share),
        ])->all());

        return $archive;
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ScholarshipArchiveRecipient::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
