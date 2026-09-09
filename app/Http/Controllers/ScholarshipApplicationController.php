<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreScholarshipApplicationRequest;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ScholarshipApplicationController extends Controller
{
    /**
     * Display the authenticated student's own applications.
     */
    public function index(): View
    {
        $applications = auth()->user()->scholarshipApplications()
            ->with('scholarship')
            ->latest('submitted_at')
            ->paginate(10);

        return view('scholarship-applications.index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show the application form, pre-filled from the authenticated user's own profile.
     */
    public function create(Scholarship $scholarship): View
    {
        Gate::authorize('create', [ScholarshipApplication::class, $scholarship]);

        $scholarship->load('requiredDocuments');

        return view('scholarship-applications.create', [
            'scholarship' => $scholarship,
            'applicant' => auth()->user(),
        ]);
    }

    /**
     * Submit the application for the given scholarship.
     *
     * The applicant's identity (name, age, course, etc.) is never accepted
     * from the request; it is always read from the authenticated user so it
     * cannot be spoofed. Only document uploads come from the request body.
     */
    public function store(StoreScholarshipApplicationRequest $request, Scholarship $scholarship): RedirectResponse
    {
        $application = DB::transaction(function () use ($request, $scholarship) {
            $application = $scholarship->applications()->create([
                'user_id' => $request->user()->id,
                'status' => ApplicationStatus::Pending,
                'submitted_at' => now(),
            ]);

            foreach ($scholarship->requiredDocuments as $requiredDocument) {
                $file = $request->file("documents.{$requiredDocument->id}");

                $path = $file->store("scholarship-applications/{$application->id}", 'supabase');

                $application->documents()->create([
                    'scholarship_required_document_id' => $requiredDocument->id,
                    'disk' => 'supabase',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }

            return $application;
        });

        return redirect()->route('scholarship-applications.show', $application)
            ->with('status', 'Application submitted.');
    }

    /**
     * Display the authenticated student's own application.
     */
    public function show(ScholarshipApplication $application): View
    {
        Gate::authorize('view', $application);

        $application->load(['scholarship.requiredDocuments', 'documents.requiredDocument']);

        return view('scholarship-applications.show', [
            'application' => $application,
        ]);
    }
}
