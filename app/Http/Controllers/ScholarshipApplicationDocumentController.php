<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipApplication;
use App\Models\ScholarshipApplicationDocument;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScholarshipApplicationDocumentController extends Controller
{
    /**
     * Download an uploaded application document.
     */
    public function show(ScholarshipApplication $application, ScholarshipApplicationDocument $document): StreamedResponse
    {
        abort_unless($document->scholarship_application_id === $application->id, 404);

        Gate::authorize('view', $document);

        return $document->download();
    }
}
