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
     * How many rows each dashboard list shows before deferring to its full page.
     */
    private const LIST_LIMIT = 5;

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
        $availableScholarships = $remainingPlaces > 0
            ? Scholarship::availableTo($student)->get()->filter->isOpenForApplications()->values()
            : new Collection;

        return view('dashboard', [
            'activeApplications' => $applications->filter->isActive()->values(),
            'applications' => $applications->take(self::LIST_LIMIT),
            'applicationCount' => $applications->count(),
            'pendingCount' => $applications->where('status', ApplicationStatus::Pending)->count(),
            'availableScholarships' => $availableScholarships->take(self::LIST_LIMIT),
            'availableScholarshipCount' => $availableScholarships->count(),
            'remainingPlaces' => $remainingPlaces,
        ]);
    }
}
