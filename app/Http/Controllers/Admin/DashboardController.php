<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * How many of the newest postings the dashboard lists. Posting another
     * pushes the oldest of them off the table; it is not deleted, and the
     * Scholarships page still lists it.
     */
    private const RECENT_POSTINGS_LIMIT = 2;

    /**
     * Display the dashboard: the banner's summary, the figures and the newest postings.
     *
     * The summary counts scholarships with applicants waiting, split by
     * whether a pass list can settle them yet: a pass list rejects every
     * pending applicant it doesn't name, so a scholarship is ready for one
     * only once students can no longer apply. The page no longer lists them
     * (decided 2026-10-24), but the split keeps its order (longest wait, then
     * soonest deadline) so a list can come back without reworking it.
     *
     * Every scholarship is fetched in the one query so the summary, the
     * figures and the postings table cost no second round trip. Each row
     * also carries the active and on-leave student counts as subqueries;
     * only when nothing is posted, so there is no row to read them from,
     * are students counted on their own.
     */
    public function index(): View
    {
        $scholarships = Scholarship::query()
            ->withCount([
                'applications as pending_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Pending),
                'applications as held_slots_count' => fn (Builder $query) => $query->holdingSlot(),
            ])
            ->addSelect([
                'oldest_pending_at' => ScholarshipApplication::query()
                    ->selectRaw('min(coalesce(submitted_at, created_at))')
                    ->whereColumn('scholarship_id', 'scholarships.id')
                    ->where('status', ApplicationStatus::Pending),
                'active_student_count' => $this->students(StudentStatus::Active)->selectRaw('count(*)'),
                'on_leave_student_count' => $this->students(StudentStatus::LeaveOfAbsence)->selectRaw('count(*)'),
            ])
            ->withCasts(['oldest_pending_at' => 'datetime'])
            ->orderByRaw('deadline asc nulls last')
            ->orderByDesc('pending_count')
            ->orderBy('title')
            ->get();

        [$takingApplications, $readyForPassList] = $scholarships
            ->filter(fn (Scholarship $scholarship): bool => $scholarship->pending_count > 0)
            ->partition(fn (Scholarship $scholarship): bool => $scholarship->isOpenForApplications());

        $openCount = $scholarships->filter(fn (Scholarship $scholarship): bool => $scholarship->isOpenForApplications())->count();
        $endedCount = $scholarships->where('status', ScholarshipStatus::Ended)->count();

        $firstScholarship = $scholarships->first();
        $activeStudentCount = (int) ($firstScholarship?->active_student_count ?? $this->students(StudentStatus::Active)->count());
        $onLeaveStudentCount = (int) ($firstScholarship?->on_leave_student_count ?? $this->students(StudentStatus::LeaveOfAbsence)->count());

        return view('admin.dashboard', [
            'readyForPassList' => $readyForPassList->sortBy('oldest_pending_at')->values(),
            'takingApplications' => $takingApplications->values(),
            'postings' => $scholarships->sortBy([['created_at', 'desc'], ['id', 'desc']])->take(self::RECENT_POSTINGS_LIMIT)->values(),
            'scholarshipCount' => $scholarships->count(),
            'openCount' => $openCount,
            'closedCount' => $scholarships->count() - $openCount - $endedCount,
            'endedCount' => $endedCount,
            'activeStudentCount' => $activeStudentCount,
            'onLeaveStudentCount' => $onLeaveStudentCount,
            'registeredStudentCount' => $activeStudentCount + $onLeaveStudentCount,
        ]);
    }

    /**
     * Registered students with the given status. Staff accounts carry a
     * student status too, so the role is checked as well.
     *
     * @return Builder<User>
     */
    private function students(StudentStatus $status): Builder
    {
        return User::query()->where('role', UserRole::Student)->where('student_status', $status);
    }
}
