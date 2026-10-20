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
     *
     * The scholarship row is locked before the slots are counted again, so
     * students submitting at the same moment take turns and only one of
     * them gets the last slot.
     */
    public function store(StoreScholarshipApplicationRequest $request, Scholarship $scholarship): RedirectResponse
    {
        $application = DB::transaction(function () use ($request, $scholarship) {
            abort_if(Scholarship::query()->lockForUpdate()->findOrFail($scholarship->id)->remainingSlots() === 0, 403);

            $application = $scholarship->applications()->create([
                'user_id' => $request->user()->id,
                'status' => ApplicationStatus::Pending,
                'submitted_at' => now(),
            ]);
            $directory = "scholarship-applications/{$application->id}";

            foreach ($scholarship->requiredDocuments as $requiredDocument) {
                // A file sent straight to storage is moved out of staging; one
                // that came with the form (no JavaScript) is stored as before.
                if ($accepted = $request->acceptedUpload($requiredDocument->id)) {
                    [$upload, $extension] = $accepted;
                    $path = $upload->moveInto($directory, $extension);
                    $originalName = $upload->originalName;
                } else {
                    $file = $request->file("documents.{$requiredDocument->id}");
                    $path = $file->store($directory, 'supabase');
                    $originalName = $file->getClientOriginalName();
                }

                $application->documents()->create([
                    'scholarship_required_document_id' => $requiredDocument->id,
                    'disk' => 'supabase',
                    'path' => $path,
                    'original_name' => $originalName,
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

        // The held-slot count rides along as a subquery, so the page can say
        // whether applications are still open without another round trip.
        $application->load([
            'scholarship' => fn ($query) => $query->withCount([
                'applications as held_slots_count' => fn ($query) => $query->holdingSlot(),
            ]),
            'documents.requiredDocument',
        ]);

        return view('scholarship-applications.show', [
            'application' => $application,
        ]);
    }
}
