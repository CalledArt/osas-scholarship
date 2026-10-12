<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use Database\Factories\ScholarshipApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'status', 'submitted_at'])]
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
     * Rejected ones never count, and nothing counts once the scholarship ends.
     */
    public function holdsScholarshipPlace(): bool
    {
        return $this->status !== ApplicationStatus::Rejected
            && $this->scholarshipStatus() !== ScholarshipStatus::Ended;
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
