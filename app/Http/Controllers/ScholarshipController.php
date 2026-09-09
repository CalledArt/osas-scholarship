<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    /**
     * Display the scholarships available to students.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Scholarship::class);

        $scholarships = Scholarship::withCount([
            'applications',
            'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
        ])
            ->orderByRaw("status = 'open' desc")
            ->latest()
            ->paginate(10);

        return view('scholarships.index', [
            'scholarships' => $scholarships,
        ]);
    }

    /**
     * Display the given scholarship's details.
     */
    public function show(Scholarship $scholarship): View
    {
        Gate::authorize('view', $scholarship);

        $scholarship->load('requiredDocuments');
        $scholarship->loadCount([
            'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
        ]);

        $application = $scholarship->applications()
            ->where('user_id', auth()->id())
            ->first();

        return view('scholarships.show', [
            'scholarship' => $scholarship,
            'application' => $application,
        ]);
    }
}
