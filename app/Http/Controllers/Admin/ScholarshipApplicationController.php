<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DecideScholarshipApplicationsFromCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportScholarshipApplicationDecisionsRequest;
use App\Mail\ScholarshipApplicationApproved;
use App\Mail\ScholarshipApplicationRejected;
use App\Models\Scholarship;
use App\Support\BccMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ScholarshipApplicationController extends Controller
{
    /**
     * Approve every pending applicant named in the uploaded CSV and reject
     * every other pending applicant for this scholarship.
     *
     * The pass list is the only way OSAS decides applications; there is no
     * per-applicant approve or reject action.
     *
     * Each decided applicant is emailed whether they passed. Applicants the
     * pass list left pending (ambiguous names, no slots left) are not.
     */
    public function importDecisions(ImportScholarshipApplicationDecisionsRequest $request, Scholarship $scholarship, DecideScholarshipApplicationsFromCsv $decider, BccMailer $mailer): RedirectResponse
    {
        $results = $decider->handle($scholarship, $request->file('file'));

        $decidedCount = count($results['approved']) + count($results['rejected']);
        $emailed = $mailer->send(array_column($results['approved'], 'email'), new ScholarshipApplicationApproved($scholarship))
            + $mailer->send(array_column($results['rejected'], 'email'), new ScholarshipApplicationRejected($scholarship));

        $redirect = redirect()->route('admin.scholarships.show', $scholarship)
            ->with('decisionResults', $results);

        if ($emailed < $decidedCount) {
            return $redirect->with('error', "The result email reached only {$emailed} of {$decidedCount} applicants. Check the mail settings and the application log.");
        }

        if ($decidedCount > 0) {
            return $redirect->with('status', "Emailed the result to {$decidedCount} ".Str::plural('applicant', $decidedCount).'.');
        }

        return $redirect;
    }
}
