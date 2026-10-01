<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateScholarshipRequiredDocumentSampleRequest;
use App\Models\Scholarship;
use App\Models\ScholarshipRequiredDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ScholarshipRequiredDocumentSampleController extends Controller
{
    /**
     * Post or replace the example applicants see for a required document.
     */
    public function update(UpdateScholarshipRequiredDocumentSampleRequest $request, Scholarship $scholarship, ScholarshipRequiredDocument $requiredDocument): RedirectResponse
    {
        abort_unless($requiredDocument->scholarship_id === $scholarship->id, 404);

        $requiredDocument->attachSample($request->file('sample'));

        return redirect()->route('admin.scholarships.edit', $scholarship)
            ->with('status', 'Sample uploaded.');
    }

    /**
     * Take down the example for a required document.
     */
    public function destroy(Scholarship $scholarship, ScholarshipRequiredDocument $requiredDocument): RedirectResponse
    {
        Gate::authorize('update', $scholarship);

        abort_unless($requiredDocument->scholarship_id === $scholarship->id, 404);

        $requiredDocument->removeSample();

        return redirect()->route('admin.scholarships.edit', $scholarship)
            ->with('status', 'Sample removed.');
    }
}
