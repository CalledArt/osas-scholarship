<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * How many rows each dashboard list shows before deferring to its full page.
     */
    private const LIST_LIMIT = 5;

    /**
     * Display where the student's applications stand and what they can still apply to.
     */
    public function index(Request $request): View
    {
        $student = $request->user();

        // Joined rather than eager loaded: the title is all the row needs, and it
        // saves a round trip to the database.
        $applications = $student->scholarshipApplications()
            ->join('scholarships', 'scholarships.id', '=', 'scholarship_applications.scholarship_id')
            ->select(['scholarship_applications.*', 'scholarships.title as scholarship_title'])
            ->latest('scholarship_applications.submitted_at')
            ->get();

        // The SQL narrows the candidates; isOpenForApplications() stays the
        // final word on eligibility, since it also accounts for filled slots.
        $openScholarships = Scholarship::query()
            ->where('status', ScholarshipStatus::Open)
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhere('deadline', '>', now()))
            ->whereDoesntHave('applications', fn (Builder $query) => $query->whereBelongsTo($student))
            ->withCount([
                'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
            ])
            ->orderByRaw('deadline asc nulls last')
            ->orderBy('title')
            ->get()
            ->filter(fn (Scholarship $scholarship): bool => $scholarship->isOpenForApplications())
            ->values();

        return view('dashboard', [
            'applications' => $applications->take(self::LIST_LIMIT),
            'applicationCount' => $applications->count(),
            'pendingCount' => $applications->where('status', ApplicationStatus::Pending)->count(),
            'openScholarships' => $openScholarships->take(self::LIST_LIMIT),
            'openScholarshipCount' => $openScholarships->count(),
        ]);
    }
}
