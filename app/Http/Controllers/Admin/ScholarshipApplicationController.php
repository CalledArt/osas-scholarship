<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DecideScholarshipApplicationsFromCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportScholarshipApplicationDecisionsRequest;
use App\Mail\ScholarshipApplicationApproved;
use App\Mail\ScholarshipApplicationRejected;
use App\Models\Scholarship;
use App\Support\StudentMailer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
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
     * Each decided applicant is emailed whether they passed. Those on the list
     * who already hold an active scholarship get the rejection that explains
     * it. Applicants the pass list left pending (ambiguous names, no slots
     * left) are not emailed.
     */
    public function importDecisions(ImportScholarshipApplicationDecisionsRequest $request, Scholarship $scholarship, DecideScholarshipApplicationsFromCsv $decider, StudentMailer $mailer): RedirectResponse
    {
        $results = $decider->handle($scholarship, $request->file('file'));

        $decidedCount = count($results['approved']) + count($results['rejected']) + count($results['already_active']);
        $emailed = $mailer->send(array_column($results['approved'], 'email'), new ScholarshipApplicationApproved($scholarship))
            + $mailer->send(array_column($results['rejected'], 'email'), new ScholarshipApplicationRejected($scholarship))
            + $mailer->send(array_column($results['already_active'], 'email'), new ScholarshipApplicationRejected($scholarship, passedWhileHoldingActive: true));

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

    /**
     * Download every application to the scholarship as a PDF for printing,
     * by surname, with the required documents each applicant submitted.
     */
    public function report(Request $request, Scholarship $scholarship): Response
    {
        Gate::authorize('view', $scholarship);

        $scholarship->load('requiredDocuments');
        $applications = $scholarship->applications()
            ->orderedByStudentName()
            ->with(['user', 'documents'])
            ->get();
        $generatedAt = now(config('app.display_timezone'));

        $filename = 'applications-report-'.Str::slug(Str::limit($scholarship->title, 60, '')).'-'.$generatedAt->format('Y-m-d').'.pdf';

        return Pdf::loadView('admin.scholarships.report', [
            'scholarship' => $scholarship,
            'applications' => $applications,
            'preparedBy' => $request->user(),
            'generatedAt' => $generatedAt,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('enable_font_subsetting', true)
            ->download($filename);
    }
}
