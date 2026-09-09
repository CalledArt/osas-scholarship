<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DecideScholarshipApplicationsFromCsv;
use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportScholarshipApplicationDecisionsRequest;
use App\Http\Requests\Admin\UpdateScholarshipApplicationStatusRequest;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use Illuminate\Http\RedirectResponse;

class ScholarshipApplicationController extends Controller
{
    /**
     * Approve or reject a student's scholarship application.
     */
    public function update(UpdateScholarshipApplicationStatusRequest $request, Scholarship $scholarship, ScholarshipApplication $application): RedirectResponse
    {
        abort_unless($application->scholarship_id === $scholarship->id, 404);

        $status = ApplicationStatus::from($request->validated('status'));

        if ($status === ApplicationStatus::Approved && $scholarship->remainingSlots() < 1) {
            return back()->with('error', 'No remaining slots are available for this scholarship.');
        }

        $application->update(['status' => $status]);

        if ($status === ApplicationStatus::Approved && $scholarship->remainingSlots() < 1) {
            $scholarship->update(['status' => ScholarshipStatus::Closed]);
        }

        return back()->with('status', "Application {$status->label()}.");
    }

    /**
     * Approve every pending applicant named in the uploaded CSV and reject
     * every other pending applicant for this scholarship.
     */
    public function importDecisions(ImportScholarshipApplicationDecisionsRequest $request, Scholarship $scholarship, DecideScholarshipApplicationsFromCsv $decider): RedirectResponse
    {
        $results = $decider->handle($scholarship, $request->file('file'));

        return redirect()->route('admin.scholarships.show', $scholarship)
            ->with('decisionResults', $results);
    }
}
