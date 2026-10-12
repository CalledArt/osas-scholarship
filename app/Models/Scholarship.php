<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipAvailability;
use App\Enums\ScholarshipStatus;
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

#[Fillable(['title', 'description', 'slots', 'deadline', 'status'])]
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
     * Uses the `approved_applications_count` eager-loaded via
     * `withCount()` when available, to avoid an extra query per
     * scholarship when rendering a list. Not memoized beyond that,
     * since callers such as the approval flow rely on a fresh count
     * immediately after an application's status changes.
     */
    public function approvedApplicationsCount(): int
    {
        if (array_key_exists('approved_applications_count', $this->attributes)) {
            return (int) $this->attributes['approved_applications_count'];
        }

        return $this->applications()
            ->where('status', ApplicationStatus::Approved)
            ->count();
    }

    public function remainingSlots(): int
    {
        return max(0, $this->slots - $this->approvedApplicationsCount());
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
     * Narrow to the scholarships the student has not applied to that are still
     * open, soonest deadline first.
     *
     * Filled slots are not checked here, so callers filter the results through
     * isOpenForApplications(), which stays the final word on eligibility.
     */
    #[Scope]
    protected function availableTo(Builder $query, User $student): void
    {
        $query->where('status', ScholarshipStatus::Open)
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhere('deadline', '>', now()))
            ->whereDoesntHave('applications', fn (Builder $query) => $query->whereBelongsTo($student))
            ->withCount([
                'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
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
     * cost a query when `approved_applications_count` was not loaded.
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
