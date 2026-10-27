<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BuildApplicationsReport;
use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApplicationsReportBuildController extends Controller
{
    /**
     * Begin building the applications report with every applicant's documents.
     *
     * The browser then calls step() until the report is ready, showing the
     * progress each call returns.
     */
    public function store(Request $request, Scholarship $scholarship, BuildApplicationsReport $builder): JsonResponse
    {
        Gate::authorize('view', $scholarship);

        $build = $builder->start($scholarship, $request->user());

        return response()->json([
            ...$builder->progress($build),
            'stepUrl' => route('admin.scholarships.applications.report-builds.step', [$scholarship, $build['id']]),
        ], 201);
    }

    /**
     * Do the next stretch of the build and report how far along it is.
     *
     * A build belongs to the admin who started it; any other id is unknown.
     */
    public function step(Request $request, Scholarship $scholarship, string $build, BuildApplicationsReport $builder): JsonResponse
    {
        Gate::authorize('view', $scholarship);

        $state = $builder->find($build);

        abort_unless(
            $state !== null && $state['scholarship_id'] === $scholarship->id && $state['user_id'] === $request->user()->id,
            404,
        );

        return response()->json($builder->progress($builder->step($state)));
    }
}
