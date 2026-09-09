<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use Database\Factories\ScholarshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            // Not a column: supplied by a subquery select on the admin register.
            'oldest_pending_at' => 'datetime',
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

    public function isOpenForApplications(): bool
    {
        return $this->status === ScholarshipStatus::Open
            && ! $this->hasPassedDeadline()
            && $this->remainingSlots() > 0;
    }
}
