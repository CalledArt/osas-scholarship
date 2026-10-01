<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScholarshipRequiredDocumentRequest;
use App\Models\Scholarship;
use App\Models\ScholarshipRequiredDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ScholarshipRequiredDocumentController extends Controller
{
    /**
     * Add a required document to the given scholarship.
     */
    public function store(StoreScholarshipRequiredDocumentRequest $request, Scholarship $scholarship): RedirectResponse
    {
        $requiredDocument = $scholarship->requiredDocuments()->make($request->safe()->only(['name', 'description']));

        if ($request->hasFile('sample')) {
            $requiredDocument->attachSample($request->file('sample'));
        } else {
            $requiredDocument->save();
        }

        return redirect()->route('admin.scholarships.show', $scholarship)
            ->with('status', 'Required document added.');
    }

    /**
     * Remove a required document from the given scholarship.
     */
    public function destroy(Scholarship $scholarship, ScholarshipRequiredDocument $requiredDocument): RedirectResponse
    {
        Gate::authorize('update', $scholarship);

        abort_unless($requiredDocument->scholarship_id === $scholarship->id, 404);

        $requiredDocument->delete();
        $requiredDocument->deleteSampleFile();

        return redirect()->route('admin.scholarships.show', $scholarship)
            ->with('status', 'Required document removed.');
    }
}
