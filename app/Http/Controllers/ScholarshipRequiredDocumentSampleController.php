<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\ScholarshipRequiredDocument;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScholarshipRequiredDocumentSampleController extends Controller
{
    /**
     * Stream the example OSAS posted for a required document, for previewing in the page.
     */
    public function show(Scholarship $scholarship, ScholarshipRequiredDocument $requiredDocument): StreamedResponse
    {
        $this->authorizeSample($scholarship, $requiredDocument);

        return $requiredDocument->sampleResponse();
    }

    /**
     * Send the example as a file for the applicant to keep.
     */
    public function download(Scholarship $scholarship, ScholarshipRequiredDocument $requiredDocument): StreamedResponse
    {
        $this->authorizeSample($scholarship, $requiredDocument);

        return $requiredDocument->sampleResponse('attachment');
    }

    /**
     * Only a posted sample, reached through its own scholarship and still in
     * storage, can be served.
     */
    private function authorizeSample(Scholarship $scholarship, ScholarshipRequiredDocument $requiredDocument): void
    {
        abort_unless($requiredDocument->scholarship_id === $scholarship->id && $requiredDocument->hasSample(), 404);

        Gate::authorize('view', $scholarship);

        abort_unless($requiredDocument->sampleIsStored(), 404);
    }
}
