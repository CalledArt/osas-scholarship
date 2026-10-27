<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ApplicationStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Support\PersonName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'first_name', 'middle_name', 'last_name', 'email', 'google_id', 'password', 'role', 'email_verified_at', 'must_change_password', 'date_of_birth', 'student_number', 'course', 'year_level', 'program_years', 'student_status', 'contact_number', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * How many scholarships a student may have at once, counting applications
     * still waiting for a decision.
     */
    public const SCHOLARSHIP_LIMIT = 2;

    /**
     * How many approved scholarships a student may hold at once. Free tuition
     * under RA 10931 is not a scholarship record, so it never counts.
     */
    public const ACTIVE_SCHOLARSHIP_LIMIT = 1;

    /**
     * The month a school year opens: from it on, dates belong to the school
     * year that starts in that calendar year.
     */
    public const SCHOOL_YEAR_START_MONTH = 8;

    /**
     * How many hours a notification stays in the student's bell once read.
     * After that the bell leaves it out, and the next posting deletes it.
     */
    public const READ_NOTIFICATION_HOURS = 24;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'student_status' => StudentStatus::Active->value,
    ];

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
            'student_status' => StudentStatus::class,
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

    public function isOnLeaveOfAbsence(): bool
    {
        return $this->student_status === StudentStatus::LeaveOfAbsence;
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

    /**
     * The student's name as "last, first middle" for alphabetical lists, with
     * the same fallback to the stored full name as full_name.
     */
    protected function surnameFirstName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => PersonName::surnameFirst($this->first_name, $this->middle_name, $this->last_name)
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
     * Whether the student has free tuition under RA 10931 right now: within
     * the years it covers, and not on leave of absence, which pauses it.
     */
    public function hasFreeTertiaryEducation(): bool
    {
        return ! $this->isOnLeaveOfAbsence() && $this->isWithinFreeTertiaryEducationYears();
    }

    /**
     * Whether the student has not yet used up the school years RA 10931 covers.
     *
     * A student is within them while either count is unknown, since nothing
     * then shows they have passed the years it covers.
     */
    public function isWithinFreeTertiaryEducationYears(): bool
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
     * The student's applications, each with its scholarship's title, status and preset joined in.
     *
     * Joined rather than eager loaded: those columns are all a list row, the
     * scholarship limit or the one-per-preset rule needs, and the join saves
     * a round trip to the database.
     */
    public function scholarshipApplicationsWithScholarship(): HasMany
    {
        return $this->scholarshipApplications()
            ->join('scholarships', 'scholarships.id', '=', 'scholarship_applications.scholarship_id')
            ->select([
                'scholarship_applications.*',
                'scholarships.title as scholarship_title',
                'scholarships.status as scholarship_status',
                'scholarships.preset as scholarship_preset',
            ]);
    }

    /**
     * The student's ten latest notifications for the bell, newest first. A
     * read notification is left out once READ_NOTIFICATION_HOURS have passed
     * since it was read.
     *
     * Each row carries `unread_total`, the student's whole unread count, as a
     * subquery, so the bell and its badge cost one query. A student with no
     * notifications at all has an unread count of 0.
     *
     * @return EloquentCollection<int, DatabaseNotification>
     */
    public function bellNotifications(): EloquentCollection
    {
        return $this->notifications()
            ->select('notifications.*')
            ->selectSub($this->unreadNotifications()->reorder()->toBase()->selectRaw('count(*)'), 'unread_total')
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('read_at')
                ->orWhere('read_at', '>', now()->subHours(self::READ_NOTIFICATION_HOURS)))
            ->limit(10)
            ->get();
    }

    /**
     * Void the student's applications still waiting for a decision, as OSAS
     * does when it puts them on leave of absence, so other students can take
     * the slots they held. Scholarships already approved stay with the student.
     *
     * @return Collection<int, Scholarship> the scholarships whose slots were freed
     */
    public function voidPendingApplications(): Collection
    {
        $applications = $this->scholarshipApplications()
            ->where('status', ApplicationStatus::Pending)
            ->with('scholarship')
            ->get();

        if ($applications->isNotEmpty()) {
            ScholarshipApplication::query()->whereKey($applications->modelKeys())->update(['status' => ApplicationStatus::Voided]);
        }

        return $applications->pluck('scholarship');
    }

    /**
     * How many more scholarships the student can apply to.
     *
     * None while they are on leave of absence, or while they hold an active
     * scholarship, since a second approval could not be taken; otherwise what is left of the limit after pending
     * and active applications. Counts the applications the caller already
     * loaded through scholarshipApplicationsWithScholarship(), so a page
     * listing them spends no extra round trip on the limit.
     *
     * @param  Collection<int, ScholarshipApplication>  $applications
     */
    public function remainingScholarshipPlaces(Collection $applications): int
    {
        if ($this->isOnLeaveOfAbsence() || $this->hasReachedActiveScholarshipLimit($applications)) {
            return 0;
        }

        return max(0, self::SCHOLARSHIP_LIMIT - $applications->filter->holdsScholarshipPlace()->count());
    }

    /**
     * Whether the student may apply to the scholarship, judged from the
     * applications the caller already loaded (through
     * scholarshipApplicationsWithScholarship(), or with their scholarships
     * eager loaded), so it costs no query of its own.
     *
     * Once to each scholarship, only if it admits their course, not while
     * they hold a place on another posting of its preset, and only while
     * under the scholarship limit. Whether the scholarship itself is still
     * open for applications is the caller's to check.
     *
     * @param  Collection<int, ScholarshipApplication>  $applications
     */
    public function mayApplyTo(Scholarship $scholarship, Collection $applications): bool
    {
        return $scholarship->admitsCourse($this->course)
            && ! $applications->contains('scholarship_id', $scholarship->id)
            && ! $this->holdsPlaceFromPreset($applications, $scholarship->preset)
            && $this->remainingScholarshipPlaces($applications) > 0;
    }

    /**
     * Whether the student already holds a place on a scholarship posted from
     * the given preset: a pending or approved application to it, before its
     * grant period ends. A student may hold one posting of each preset, so
     * they cannot apply to another; a rejected application does not count.
     *
     * @param  Collection<int, ScholarshipApplication>  $applications
     */
    public function holdsPlaceFromPreset(Collection $applications, ?string $preset): bool
    {
        return $preset !== null && $applications->contains(
            fn (ScholarshipApplication $application): bool => $application->scholarshipPreset() === $preset && $application->holdsScholarshipPlace(),
        );
    }

    /**
     * Whether the student already holds as many active scholarships as they may.
     *
     * @param  Collection<int, ScholarshipApplication>  $applications
     */
    public function hasReachedActiveScholarshipLimit(Collection $applications): bool
    {
        return $applications->filter->isActive()->count() >= self::ACTIVE_SCHOLARSHIP_LIMIT;
    }
}
