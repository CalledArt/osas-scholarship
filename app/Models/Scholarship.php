<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipAvailability;
use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use App\Support\CvsuPrograms;
use App\Support\Peso;
use Database\Factories\ScholarshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['title', 'description', 'type', 'preset', 'stem_courses_only', 'slots', 'total_funds', 'deadline', 'status'])]
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
            'total_funds' => 'decimal:2',
        ];
    }

    /**
     * Give each new posting its reference number: "SCH-", the year it was
     * posted, and its id to five digits (SCH-2026-00012). The id exists only
     * once the row is inserted, so the number is written straight after.
     */
    protected static function booted(): void
    {
        static::created(function (Scholarship $scholarship): void {
            $scholarship->forceFill([
                'reference' => sprintf('SCH-%d-%05d', ($scholarship->created_at ?? now())->year, $scholarship->id),
            ])->saveQuietly();
        });
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

    /**
     * The fund OSAS set for the scholarship, in centavos; null until it is set.
     */
    public function totalFundsInCentavos(): ?int
    {
        return $this->total_funds === null ? null : Peso::toCentavos($this->total_funds);
    }

    /**
     * Each passed student's equal share of the fund, in centavos.
     *
     * Rounded down to the centavo so the shares never add up to more than
     * the fund; the centavos left over stay in it. Null until OSAS sets the
     * fund or anyone passes.
     */
    public function fundShareInCentavos(int $passedCount): ?int
    {
        $totalFunds = $this->totalFundsInCentavos();

        if ($totalFunds === null || $passedCount === 0) {
            return null;
        }

        return intdiv($totalFunds, $passedCount);
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
     * Whether a posting OSAS still has open for applications already carries
     * the given title, ignoring capitals and extra spaces. Like a preset, a
     * title may be posted again once its earlier posting is closed or ended.
     *
     * Titles are compared in PHP, since collapsing inner spaces has no
     * portable SQL; only open postings are fetched, and only their titles.
     */
    public static function hasOpenPostingTitled(string $title, ?self $except = null): bool
    {
        $normalizedTitle = self::normalizedTitle($title);

        return self::query()
            ->where('status', ScholarshipStatus::Open)
            ->when($except !== null, fn (Builder $query) => $query->whereKeyNot($except->getKey()))
            ->pluck('title')
            ->contains(fn (string $openTitle): bool => self::normalizedTitle($openTitle) === $normalizedTitle);
    }

    /**
     * A title as duplicate checks compare it: lower case, with runs of
     * spaces collapsed and the ends trimmed.
     */
    public static function normalizedTitle(string $title): string
    {
        return Str::lower(Str::squish($title));
    }

    /**
     * Narrow to the postings of the given preset that OSAS still has open for
     * applications. Only one may be open at a time; a posting the slots
     * filled or the deadline passed still counts until OSAS closes it.
     */
    #[Scope]
    protected function openFromPreset(Builder $query, string $preset): void
    {
        $query->where('preset', $preset)->where('status', ScholarshipStatus::Open);
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
     *
     * A completed fund allocation is archived first (ScholarshipArchive), in
     * one transaction with the delete. The files are removed only after it
     * commits, since a rollback cannot bring them back.
     */
    public function deleteWithStoredFiles(?User $deletedBy = null): void
    {
        $applicantUploads = ScholarshipApplicationDocument::query()
            ->whereIn('scholarship_application_id', $this->applications()->select('id'))
            ->get(['disk', 'path']);
        $samples = $this->requiredDocuments()->whereNotNull('sample_path')->get();
        $reportDirectory = $this->reportDirectory();

        DB::transaction(function () use ($deletedBy): void {
            ScholarshipArchive::recordFor($this, $deletedBy);

            $this->delete();
        });

        $applicantUploads->groupBy('disk')->each(
            fn (Collection $uploads, string $disk) => Storage::disk($disk)->delete($uploads->pluck('path')->all())
        );
        $samples->each->deleteSampleFile();
        // Built reports carry copies of every applicant's documents.
        Storage::disk('supabase')->deleteDirectory($reportDirectory);
    }

    /**
     * Where the applications reports built for this scholarship are stored.
     */
    public function reportDirectory(): string
    {
        return "scholarship-reports/{$this->id}";
    }
}
