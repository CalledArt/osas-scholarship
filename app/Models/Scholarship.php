<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipAvailability;
use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use App\Support\CvsuPrograms;
use Database\Factories\ScholarshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description', 'type', 'stem_courses_only', 'slots', 'deadline', 'status'])]
class Scholarship extends Model
{
    /** @use HasFactory<ScholarshipFactory> */
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
            'status' => ScholarshipStatus::class,
            'type' => ScholarshipType::class,
            'stem_courses_only' => 'boolean',
        ];
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(ScholarshipRequiredDocument::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ScholarshipApplication::class);
    }

    /**
     * The number of approved applications against this scholarship.
     *
     * Always counted fresh, since the approval flow relies on it
     * immediately after an application's status changes.
     */
    public function approvedApplicationsCount(): int
    {
        return $this->applications()
            ->where('status', ApplicationStatus::Approved)
            ->count();
    }

    /**
     * The number of applications holding one of this scholarship's slots.
     *
     * Uses the `held_slots_count` eager-loaded via `withCount()` when
     * available, to avoid an extra query per scholarship when rendering
     * a list.
     */
    public function heldSlotsCount(): int
    {
        if (array_key_exists('held_slots_count', $this->attributes)) {
            return (int) $this->attributes['held_slots_count'];
        }

        return $this->applications()->holdingSlot()->count();
    }

    /**
     * Slots taken by applicants, capped at the slot count for a scholarship
     * that had more applicants than slots before OSAS lowered them.
     */
    public function filledSlots(): int
    {
        return min($this->slots, $this->heldSlotsCount());
    }

    public function remainingSlots(): int
    {
        return $this->slots - $this->filledSlots();
    }

    public function hasPassedDeadline(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }

    /**
     * Whole calendar days until the deadline; negative once it has passed.
     *
     * Counted between day boundaries rather than instants, so a deadline two
     * dates away always reads as two days regardless of the time of day.
     */
    public function daysUntilDeadline(): ?int
    {
        if ($this->deadline === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->deadline->startOfDay());
    }

    /**
     * Whether a student in the given course may apply: any course, unless OSAS
     * limited the scholarship to STEM courses.
     */
    public function admitsCourse(?string $course): bool
    {
        return ! $this->stem_courses_only || CvsuPrograms::isStem($course);
    }

    /**
     * Narrow to the scholarships the student has not applied to that are still
     * open to them, soonest deadline first.
     *
     * Filled slots are not checked here, so callers filter the results through
     * isOpenForApplications(), which stays the final word on eligibility.
     */
    #[Scope]
    protected function availableTo(Builder $query, User $student): void
    {
        $query->where('status', ScholarshipStatus::Open)
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhere('deadline', '>', now()))
            ->unless(CvsuPrograms::isStem($student->course), fn (Builder $query) => $query->where('stem_courses_only', false))
            ->whereDoesntHave('applications', fn (Builder $query) => $query->whereBelongsTo($student))
            ->withCount([
                'applications as held_slots_count' => fn (Builder $query) => $query->holdingSlot(),
            ])
            ->orderByRaw('deadline asc nulls last')
            ->orderBy('title');
    }

    public function isOpenForApplications(): bool
    {
        return $this->availability() === ScholarshipAvailability::Open;
    }

    /**
     * Whether students can apply and, if not, the first reason they can't.
     *
     * The slot count is checked last because it is the only check that may
     * cost a query when `held_slots_count` was not loaded.
     */
    public function availability(): ScholarshipAvailability
    {
        return match (true) {
            $this->status !== ScholarshipStatus::Open => ScholarshipAvailability::ClosedByOsas,
            $this->hasPassedDeadline() => ScholarshipAvailability::PastDeadline,
            $this->remainingSlots() === 0 => ScholarshipAvailability::Full,
            default => ScholarshipAvailability::Open,
        };
    }

    /**
     * Delete the scholarship along with every file stored for it.
     *
     * Foreign keys cascade the delete to its required documents, applications
     * and their document rows, but not to storage. Applicants' uploads and
     * OSAS's samples are looked up first and removed once the rows are gone,
     * so students' personal documents are not left behind in the bucket.
     */
    public function deleteWithStoredFiles(): void
    {
        $applicantUploads = ScholarshipApplicationDocument::query()
            ->whereIn('scholarship_application_id', $this->applications()->select('id'))
            ->get(['disk', 'path']);
        $samples = $this->requiredDocuments()->whereNotNull('sample_path')->get();

        $this->delete();

        $applicantUploads->groupBy('disk')->each(
            fn (Collection $uploads, string $disk) => Storage::disk($disk)->delete($uploads->pluck('path')->all())
        );
        $samples->each->deleteSampleFile();
    }
}
