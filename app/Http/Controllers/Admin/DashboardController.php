<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the scholarships that are waiting on a decision, soonest deadline first.
     *
     * Every scholarship is fetched in the one query so the page can also say how
     * many exist in total without a second round trip for a count.
     */
    public function index(): View
    {
        $scholarships = Scholarship::query()
            ->withCount([
                'applications as pending_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Pending),
                'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
            ])
            ->orderByRaw('deadline asc nulls last')
            ->orderByDesc('pending_count')
            ->orderBy('title')
            ->get();

        $needsDecision = $scholarships
            ->filter(fn (Scholarship $scholarship): bool => $scholarship->pending_count > 0)
            ->values();

        return view('admin.dashboard', [
            'needsDecision' => $needsDecision,
            'pendingTotal' => $needsDecision->sum('pending_count'),
            'scholarshipCount' => $scholarships->count(),
        ]);
    }
}
