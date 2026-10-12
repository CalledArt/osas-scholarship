<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Support\PersonName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'first_name', 'middle_name', 'last_name', 'email', 'google_id', 'password', 'role', 'email_verified_at', 'must_change_password', 'date_of_birth', 'student_number', 'course', 'year_level', 'program_years', 'contact_number', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * How many scholarships a student may hold at once, counting applications
     * still waiting for a decision.
     */
    public const SCHOLARSHIP_LIMIT = 2;

    /**
     * The month a school year opens: from it on, dates belong to the school
     * year that starts in that calendar year.
     */
    public const SCHOOL_YEAR_START_MONTH = 8;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'date_of_birth' => 'date',
            'year_level' => 'integer',
            'program_years' => 'integer',
            'must_change_password' => 'boolean',
            'tutorial_completed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    /**
     * The student's name as "first middle last", falling back to the stored
     * full name for records whose parts have not been filled in yet.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => PersonName::assemble($this->first_name, $this->middle_name, $this->last_name)
                ?: (string) $this->name,
        );
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn (): ?int => $this->date_of_birth?->age,
        );
    }

    /**
     * Which school year of study the student is in, counted from the entry
     * year their student number starts with (2022… entered SY 2022–2023).
     *
     * Falls back to the stated year level for numbers without an entry year.
     */
    public function yearOfStudy(): ?int
    {
        $currentSchoolYear = now()->month >= self::SCHOOL_YEAR_START_MONTH ? now()->year : now()->year - 1;

        if (preg_match('/^(20\d{2})\d/', (string) $this->student_number, $matches) === 1 && (int) $matches[1] <= $currentSchoolYear) {
            return $currentSchoolYear - (int) $matches[1] + 1;
        }

        return $this->year_level;
    }

    /**
     * How many school years RA 10931 covers the student for: their program's
     * length plus one, per Section 6(a)(3).
     */
    public function freeTertiaryEducationYears(): ?int
    {
        return $this->program_years === null ? null : $this->program_years + 1;
    }

    /**
     * Whether the student still has free tuition under RA 10931.
     *
     * A student keeps it while either count is unknown, since nothing then
     * shows they have passed the years it covers.
     */
    public function hasFreeTertiaryEducation(): bool
    {
        $yearOfStudy = $this->yearOfStudy();
        $coveredYears = $this->freeTertiaryEducationYears();

        return $yearOfStudy === null || $coveredYears === null || $yearOfStudy <= $coveredYears;
    }

    public function postedScholarships(): HasMany
    {
        return $this->hasMany(Scholarship::class, 'created_by');
    }

    public function scholarshipApplications(): HasMany
    {
        return $this->hasMany(ScholarshipApplication::class);
    }

    /**
     * The student's applications, each with its scholarship's title and status joined in.
     *
     * Joined rather than eager loaded: those two columns are all a list row or
     * the scholarship limit needs, and the join saves a round trip to the database.
     */
    public function scholarshipApplicationsWithScholarship(): HasMany
    {
        return $this->scholarshipApplications()
            ->join('scholarships', 'scholarships.id', '=', 'scholarship_applications.scholarship_id')
            ->select([
                'scholarship_applications.*',
                'scholarships.title as scholarship_title',
                'scholarships.status as scholarship_status',
            ]);
    }

    /**
     * How many more scholarships the student can apply to.
     *
     * Counts the applications the caller already loaded through
     * scholarshipApplicationsWithScholarship(), so a page listing them spends
     * no extra round trip on the limit.
     *
     * @param  Collection<int, ScholarshipApplication>  $applications
     */
    public function remainingScholarshipPlaces(Collection $applications): int
    {
        return max(0, self::SCHOLARSHIP_LIMIT - $applications->filter->holdsScholarshipPlace()->count());
    }
}
