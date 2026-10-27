<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use Database\Factories\ScholarshipApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'status', 'submitted_at', 'passed_while_holding_active'])]
class ScholarshipApplication extends Model
{
    /** @use HasFactory<ScholarshipApplicationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'submitted_at' => 'datetime',
            'passed_while_holding_active' => 'boolean',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ScholarshipApplicationDocument::class);
    }

    /**
     * Narrow to the applications whose students passed, which make up the
     * scholarship's fund allocation.
     *
     * The column is qualified so the scope still works when users are joined in.
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), ApplicationStatus::Approved);
    }

    /**
     * Order alphabetically by the student's surname, then given names, ignoring
     * case, as OSAS's printed lists run. Students without name parts sort by
     * their stored full name.
     *
     * Joins users, so only the application's own columns are selected.
     */
    #[Scope]
    protected function orderedByStudentName(Builder $query): void
    {
        $query->join('users', 'users.id', '=', 'scholarship_applications.user_id')
            ->select('scholarship_applications.*')
            ->orderByRaw('lower(coalesce(users.last_name, users.name))')
            ->orderByRaw('lower(users.first_name)')
            ->orderByRaw('lower(users.middle_name)')
            ->orderBy('scholarship_applications.id');
    }

    /**
     * The office's handle for this application, as "2026-00029".
     *
     * A records office gives you a number. Without one a student phoning OSAS
     * about their application has nothing to quote, and the id lived only in
     * the URL. Derived from the year it was submitted and the id, so it is
     * stable, needs no column, and sorts with the record.
     */
    protected function reference(): Attribute
    {
        return Attribute::make(
            get: fn (): string => sprintf(
                '%d-%05d',
                ($this->submitted_at ?? $this->created_at)?->year ?? now()->year,
                $this->id,
            ),
        );
    }

    /**
     * Narrow to the applications holding one of their scholarship's slots.
     *
     * Pending applications hold a slot along with approved ones, so a
     * scholarship stops taking applications once enough students have
     * applied to fill it. A rejection gives the slot back, and so does
     * voiding, when OSAS puts the student on leave of absence.
     */
    #[Scope]
    protected function holdingSlot(Builder $query): void
    {
        $query->whereIn('status', [ApplicationStatus::Pending, ApplicationStatus::Approved]);
    }

    /**
     * Whether the student was approved for a scholarship that has not ended.
     */
    public function isActive(): bool
    {
        return $this->status === ApplicationStatus::Approved
            && $this->scholarshipStatus() !== ScholarshipStatus::Ended;
    }

    /**
     * Whether the application counts toward the student's scholarship limit.
     *
     * Pending applications count along with active ones, so approving every
     * application a student has waiting can never put them over the limit.
     * Rejected and voided ones never count, and nothing counts once the
     * scholarship ends.
     */
    public function holdsScholarshipPlace(): bool
    {
        return in_array($this->status, [ApplicationStatus::Pending, ApplicationStatus::Approved], true)
            && $this->scholarshipStatus() !== ScholarshipStatus::Ended;
    }

    /**
     * The preset the scholarship was posted from, read from the
     * `scholarship_preset` column that User::scholarshipApplicationsWithScholarship()
     * joins in when available, like scholarshipStatus().
     */
    public function scholarshipPreset(): ?string
    {
        if (array_key_exists('scholarship_preset', $this->attributes)) {
            return $this->attributes['scholarship_preset'];
        }

        return $this->scholarship->preset;
    }

    /**
     * The scholarship's status, read from the `scholarship_status` column that
     * User::scholarshipApplicationsWithScholarship() joins in when available,
     * so checking a list of applications costs no query per row.
     */
    private function scholarshipStatus(): ScholarshipStatus
    {
        if (array_key_exists('scholarship_status', $this->attributes)) {
            return ScholarshipStatus::from($this->attributes['scholarship_status']);
        }

        return $this->scholarship->status;
    }
}
