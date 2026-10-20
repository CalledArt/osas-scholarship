<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the scholarships with applicants waiting, split by whether a pass list can settle them yet.
     *
     * A pass list rejects every pending applicant it doesn't name, so a scholarship
     * is ready for one only once students can no longer apply. Those come first,
     * longest wait first; the ones still open follow, soonest deadline first.
     *
     * Every scholarship is fetched in the one query so the page can also say how
     * many exist in total without a second round trip for a count.
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
            ])
            ->withCasts(['oldest_pending_at' => 'datetime'])
            ->orderByRaw('deadline asc nulls last')
            ->orderByDesc('pending_count')
            ->orderBy('title')
            ->get();

        [$takingApplications, $readyForPassList] = $scholarships
            ->filter(fn (Scholarship $scholarship): bool => $scholarship->pending_count > 0)
            ->partition(fn (Scholarship $scholarship): bool => $scholarship->isOpenForApplications());

        return view('admin.dashboard', [
            'readyForPassList' => $readyForPassList->sortBy('oldest_pending_at')->values(),
            'takingApplications' => $takingApplications->values(),
            'scholarshipCount' => $scholarships->count(),
        ]);
    }
}
