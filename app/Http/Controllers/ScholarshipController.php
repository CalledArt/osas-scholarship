<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    /**
     * Display the scholarships the student holds, then the ones they can still apply to.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Scholarship::class);

        $student = $request->user();

        $applications = $student->scholarshipApplicationsWithScholarship()
            ->latest('scholarship_applications.submitted_at')
            ->get();

        $remainingPlaces = $student->remainingScholarshipPlaces($applications);

        // A student at the limit can't apply to anything, so skip the round trip.
        // The document count rides along as a subquery for the cards.
        $availableScholarships = $remainingPlaces > 0
            ? Scholarship::availableTo($student)->withCount('requiredDocuments')->get()->filter->isOpenForApplications()->values()
            : new Collection;

        return view('scholarships.index', [
            'activeApplications' => $applications->filter->isActive()->values(),
            'availableScholarships' => $availableScholarships,
            'remainingPlaces' => $remainingPlaces,
        ]);
    }

    /**
     * Display the given scholarship's details.
     */
    public function show(Request $request, Scholarship $scholarship): View
    {
        Gate::authorize('view', $scholarship);

        $scholarship->load('requiredDocuments');
        $scholarship->loadCount([
            'applications as held_slots_count' => fn (Builder $query) => $query->holdingSlot(),
        ]);

        $applications = $request->user()->scholarshipApplicationsWithScholarship()->get();

        return view('scholarships.show', [
            'scholarship' => $scholarship,
            'application' => $applications->firstWhere('scholarship_id', $scholarship->id),
            'admitsCourse' => $scholarship->admitsCourse($request->user()->course),
            'remainingPlaces' => $request->user()->remainingScholarshipPlaces($applications),
        ]);
    }
}
