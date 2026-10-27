<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * How many applications the dashboard lists before deferring to their full page.
     */
    private const LIST_LIMIT = 5;

    /**
     * How many open scholarships the dashboard shows before deferring to the
     * Scholarships page: an even number, so the two-up grid closes square.
     */
    private const AVAILABLE_LIMIT = 4;

    /**
     * Display the scholarships the student holds, what they can still apply to,
     * and where their applications stand.
     */
    public function index(Request $request): View
    {
        $student = $request->user();

        $applications = $student->scholarshipApplicationsWithScholarship()
            ->latest('scholarship_applications.submitted_at')
            ->get();

        $remainingPlaces = $student->remainingScholarshipPlaces($applications);

        // A student at the limit can't apply to anything, so skip the round trip.
        // The document count rides along as a subquery for the boxes.
        $availableScholarships = $remainingPlaces > 0
            ? Scholarship::availableTo($student)->withCount('requiredDocuments')->get()
                ->filter->isOpenForApplications()
                ->reject(fn (Scholarship $scholarship): bool => $student->holdsPlaceFromPreset($applications, $scholarship->preset))
                ->values()
            : new Collection;

        $activeLimitReached = $student->hasReachedActiveScholarshipLimit($applications);
        $onLeave = $student->isOnLeaveOfAbsence();

        return view('dashboard', [
            'activeApplications' => $applications->filter->isActive()->values(),
            // An active scholarship already has its own box in the Mine column.
            'applications' => $applications->reject->isActive()->take(self::LIST_LIMIT)->values(),
            'applicationCount' => $applications->count(),
            'waitingApplicationCount' => $applications->where('status', ApplicationStatus::Pending)->count(),
            'availableScholarships' => $availableScholarships->take(self::AVAILABLE_LIMIT),
            'availableScholarshipCount' => $availableScholarships->count(),
            'remainingPlaces' => $remainingPlaces,
            'activeLimitReached' => $activeLimitReached,
            'onLeave' => $onLeave,
            // Only open boxes earn the Open pane its column; otherwise the Mine boxes take the width.
            'hasOpenScholarships' => ! $onLeave && ! $activeLimitReached && $availableScholarships->isNotEmpty(),
            'notifications' => $student->bellNotifications(),
        ]);
    }
}
