<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreScholarshipApplicationRequest;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
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
        $student = auth()->user();

        // Each row carries `waiting_total`, the student's whole count of
        // applications still waiting for a decision, as a subquery, so the
        // banner costs no extra round trip.
        $applications = $student->scholarshipApplications()
            ->select('scholarship_applications.*')
            ->selectSub($student->scholarshipApplications()->where('status', ApplicationStatus::Pending)->toBase()->selectRaw('count(*)'), 'waiting_total')
            ->with('scholarship')
            ->latest('submitted_at')
            ->paginate(10);

        return view('scholarship-applications.index', [
            'applications' => $applications,
            'waitingApplicationCount' => (int) ($applications->first()?->waiting_total ?? 0),
        ]);
    }

    /**
     * Show the application form, pre-filled from the authenticated user's own profile.
     */
    public function create(Scholarship $scholarship): View
    {
        Gate::authorize('create', [ScholarshipApplication::class, $scholarship]);

        // The held-slot count rides along as a subquery, so the form can state
        // the deadline and the remaining capacity that decide whether starting
        // is worth it, without another round trip.
        $scholarship->load('requiredDocuments')
            ->loadCount([
                'applications as held_slots_count' => fn ($query) => $query->holdingSlot(),
            ]);

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
            // One student's applications are taken one at a time. Two sent at
            // once (two tabs, two postings of one preset) both passed the
            // policy before this transaction, so it is checked again under
            // the lock on the student's row.
            User::query()->lockForUpdate()->findOrFail($request->user()->id);
            abort_unless($request->user()->can('create', [ScholarshipApplication::class, $scholarship]), 403);
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

        // The confirmation quotes the reference and what was received, because
        // this is the receipt for the highest-stakes action in the product and
        // the student needs something they can quote back to the office. The
        // document count comes from the already-loaded relation, not a query.
        $received = $scholarship->requiredDocuments->count();

        return redirect()->route('scholarship-applications.show', $application)
            ->with('status', __('Application :reference received.', ['reference' => $application->reference]).' '
                .trans_choice(
                    'OSAS has your :count document.|OSAS has your :count documents.',
                    $received,
                    ['count' => $received],
                ));
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

        // What the student can still do from here. A rejected application ends a
        // path, and the page should be able to say where the student goes next
        // rather than stopping at the limit arithmetic. One joined query, which
        // production answers in a few ms. The policy above already guarantees
        // the application is this student's own.
        $applications = auth()->user()->scholarshipApplicationsWithScholarship()->get();

        return view('scholarship-applications.show', [
            'application' => $application,
            'remainingPlaces' => auth()->user()->remainingScholarshipPlaces($applications),
        ]);
    }
}
