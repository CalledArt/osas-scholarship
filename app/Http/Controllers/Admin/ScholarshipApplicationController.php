<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DecideScholarshipApplicationsFromCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportScholarshipApplicationDecisionsRequest;
use App\Models\Scholarship;
use Illuminate\Http\RedirectResponse;

class ScholarshipApplicationController extends Controller
{
    /**
     * Approve every pending applicant named in the uploaded CSV and reject
     * every other pending applicant for this scholarship.
     *
     * The pass list is the only way OSAS decides applications; there is no
     * per-applicant approve or reject action.
     */
    public function importDecisions(ImportScholarshipApplicationDecisionsRequest $request, Scholarship $scholarship, DecideScholarshipApplicationsFromCsv $decider): RedirectResponse
    {
        $results = $decider->handle($scholarship, $request->file('file'));

        return redirect()->route('admin.scholarships.show', $scholarship)
            ->with('decisionResults', $results);
    }
}
