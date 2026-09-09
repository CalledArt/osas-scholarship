<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the register: every scholarship, ordered by how urgently it needs a decision.
     */
    public function index(): View
    {
        $register = $this->register();

        [$needsDecision, $settled] = $register->partition(
            fn (Scholarship $scholarship): bool => $scholarship->pending_count > 0
        );

        return view('admin.dashboard', [
            'register' => $needsDecision->concat($settled)->values(),
            'needsDecisionCount' => $needsDecision->count(),
            'pendingTotal' => $needsDecision->sum('pending_count'),
            'nextToClose' => $needsDecision->first(
                fn (Scholarship $scholarship): bool => $scholarship->deadline !== null
            ),
            'openCount' => $register->filter(
                fn (Scholarship $scholarship): bool => $scholarship->status === ScholarshipStatus::Open
            )->count(),
            'recentApplications' => $this->recentApplications($register),
        ]);
    }

    /**
     * Every scholarship with its decision counts and the wait on its oldest
     * undecided application.
     *
     * Ordered by deadline so that, once the caller splits the collection on
     * `pending_count`, the scholarships needing a decision already run
     * soonest-closing first.
     *
     * @return Collection<int, Scholarship>
     */
    private function register(): Collection
    {
        return Scholarship::query()
            ->withCount([
                'applications as pending_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Pending),
                'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
            ])
            ->addSelect([
                'oldest_pending_at' => ScholarshipApplication::query()
                    ->selectRaw('min(coalesce(submitted_at, created_at))')
                    ->whereColumn('scholarship_id', 'scholarships.id')
                    ->where('status', ApplicationStatus::Pending),
            ])
            ->orderByRaw('deadline asc nulls last')
            ->orderByDesc('pending_count')
            ->orderBy('title')
            ->get();
    }

    /**
     * The latest submissions, resolved in a single round trip.
     *
     * The applicant is joined rather than eager loaded, and the scholarship is
     * taken from the register the caller already holds. Against a remote
     * database each avoided query is worth more than the tidier relation calls.
     *
     * @param  Collection<int, Scholarship>  $register
     * @return Collection<int, ScholarshipApplication>
     */
    private function recentApplications(Collection $register): Collection
    {
        $applications = ScholarshipApplication::query()
            ->join('users', 'users.id', '=', 'scholarship_applications.user_id')
            ->select([
                'scholarship_applications.*',
                'users.name as applicant_name',
                'users.student_number as applicant_student_number',
            ])
            ->orderByRaw('coalesce(scholarship_applications.submitted_at, scholarship_applications.created_at) desc')
            ->take(5)
            ->get();

        $byId = $register->keyBy('id');

        return $applications->each(
            fn (ScholarshipApplication $application) => $application->setRelation(
                'scholarship',
                $byId->get($application->scholarship_id)
            )
        );
    }
}
